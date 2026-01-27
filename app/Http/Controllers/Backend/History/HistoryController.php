<?php

namespace App\Http\Controllers\Backend\History;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Utils\Activity\SaveActivityLogController;
use Yajra\Datatables\Datatables;
use App\Models\History;
use App\Models\Colegio;
use App\Models\Beca;
use App\Models\Setting;
use App\Models\Tarifa;
use App\Models\CreditTransaction;
use Auth;
use Config;
use Crypt;
use Exception;

class HistoryController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     * More info DataTables : https://yajrabox.com/docs/laravel-datatables/master
     *
     * @param Datatables $datatables
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function index(Datatables $datatables)
    {
        $columns = [
            'id' => ['title' => 'No.', 'orderable' => false, 'searchable' => false, 'render' => function () {
                return 'function(data,type,fullData,meta){return meta.settings._iDisplayStart+meta.row+1;}';
            }],
            'full_name' => ['title' => 'Nombre', 'name' => 'histories.name'],
            'cedula' => ['title' => 'Cédula', 'name' => 'histories.cedula'],
            'colegio_nombre' => ['title' => 'Colegio', 'name' => 'colegios.nombre'],
            'ruta_nombre' => ['title' => 'Ruta', 'name' => 'settings.key_app'],
            'tarifa_info' => ['title' => 'Tarifa', 'name' => 'tarifas.monto', 'orderable' => true],
            'creditos' => ['title' => 'Créditos', 'name' => 'histories.creditos'],
            'chancesParaMarcar' => ['title' => 'Chances', 'name' => 'histories.chancesParaMarcar'],
            'contrato_status' => ['title' => 'Contrato', 'orderable' => false, 'searchable' => false],
            'download_qr_code' => ['title' => 'QR', 'orderable' => false, 'searchable' => false],
            'action' => ['orderable' => false, 'searchable' => false]
        ];

        if ($datatables->getRequest()->ajax()) {
            // Query Builder sin ->get() para server-side processing real
            $query = History::where('histories.status', 1)
                ->leftJoin('colegios', 'histories.colegio_id', '=', 'colegios.id')
                ->leftJoin('becas', 'histories.beca_id', '=', 'becas.id')
                ->leftJoin('settings', 'histories.ruta_id', '=', 'settings.id')
                ->leftJoin('tarifas', 'histories.tarifa_id', '=', 'tarifas.id')
                ->select(
                    'histories.*',
                    'colegios.nombre as colegio_nombre',
                    'becas.nombre_beca as beca_nombre',
                    'settings.key_app as ruta_nombre',
                    'tarifas.nombre as tarifa_nombre',
                    'tarifas.monto as tarifa_monto'
                );

            return $datatables->of($query)
                ->addColumn('full_name', function (History $data) {
                    return $data->full_name ?: $data->name;
                })
                ->addColumn('action', function (History $data) {
                    $routeEdit = route($this->getRoute() . ".edit", $data->id);
                    $routeDelete = route($this->getRoute() . ".delete", $data->id);
                    $routeTransactions = route('transactions.index', ['search' => $data->name]);

                    $button = '<div class="btn-group">';
                    $button .= '<a href="' . $routeTransactions . '" title="Transacciones"><button class="btn btn-sm btn-info"><i class="fa fa-credit-card"></i></button></a> ';
                    $button .= '<a href="' . $routeEdit . '" title="Editar"><button class="btn btn-sm btn-primary"><i class="fa fa-edit"></i></button></a> ';
                    if (Auth::user()->hasRole('administrator')) {
                        $button .= '<a href="' . $routeDelete . '" class="delete-button" title="Eliminar"><button class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button></a> ';
                        $button .= '<button class="btn btn-sm btn-warning" onclick="confirmInactive(' . $data->id . ')" title="Inactivar"><i class="fa fa-ban"></i></button>';
                    }
                    $button .= '</div>';
                    return $button;
                })
                ->addColumn('download_qr_code', function (History $data) {
                    $encrypted = Crypt::encryptString($data->id);
                    return '<button class="btn btn-sm btn-danger btn-qr-download" data-qr="' . $encrypted . '" data-id="' . $data->id . '" title="Descargar QR">
                                <i class="fa fa-qrcode"></i>
                            </button>';
                })
                ->editColumn('colegio_nombre', function (History $data) {
                    return $data->colegio_nombre ?? ($data->colegio ?? '-');
                })
                ->editColumn('ruta_nombre', function (History $data) {
                    return $data->ruta_nombre ?? '-';
                })
                ->addColumn('tarifa_info', function (History $data) {
                    if ($data->tarifa_id && $data->tarifa_monto !== null) {
                        return '<span class="badge badge-success">₡' . number_format($data->tarifa_monto, 0, ',', '.') . '</span>';
                    }
                    return '<span class="badge badge-danger">Sin tarifa</span>';
                })
                ->editColumn('creditos', function (History $data) {
                    $class = $data->creditos >= 0 ? 'success' : 'danger';
                    return '<span class="badge badge-' . $class . '">₡' . number_format($data->creditos, 0, ',', '.') . '</span>';
                })
                ->addColumn('contrato_status', function (History $data) {
                    if ($data->contrato_subido) {
                        $downloadUrl = route('histories.download-contract', $data->id);
                        return '<a href="' . $downloadUrl . '" class="btn btn-sm btn-success" title="Descargar contrato" target="_blank">
                                    <i class="fa fa-file-pdf"></i> Ver
                                </a>';
                    }
                    return '<span class="badge badge-warning"><i class="fa fa-times"></i> Pendiente</span>';
                })
                ->filterColumn('full_name', function($query, $keyword) {
                    $query->where(function($q) use ($keyword) {
                        $q->where('histories.name', 'like', "%{$keyword}%")
                          ->orWhere('histories.first_name', 'like', "%{$keyword}%")
                          ->orWhere('histories.last_name', 'like', "%{$keyword}%");
                    });
                })
                ->rawColumns(['action', 'tarifa_info', 'creditos', 'contrato_status', 'download_qr_code'])
                ->toJson();
        }

        $columnsArrExPr = [1, 2, 3, 4, 5, 6, 7, 8];
        $html = $datatables->getHtmlBuilder()
            ->columns($columns)
            ->parameters([
                'order' => [[1, 'asc']],
                'responsive' => true,
                'autoWidth' => false,
                'processing' => true,
                'serverSide' => true,
                'lengthMenu' => [
                    [10, 25, 50, 100],
                    ['10', '25', '50', '100']
                ],
                'dom' => 'Bfrtip',
                'buttons' => $this->buttonDatatables($columnsArrExPr),
                'language' => [
                    'processing' => 'Cargando...',
                    'search' => 'Buscar:',
                    'lengthMenu' => 'Mostrar _MENU_ registros',
                    'info' => 'Mostrando _START_ a _END_ de _TOTAL_ estudiantes',
                    'infoEmpty' => 'Mostrando 0 a 0 de 0 estudiantes',
                    'infoFiltered' => '(filtrado de _MAX_ totales)',
                    'zeroRecords' => 'No se encontraron estudiantes',
                    'emptyTable' => 'No hay estudiantes registrados',
                    'paginate' => [
                        'first' => 'Primero',
                        'previous' => 'Anterior',
                        'next' => 'Siguiente',
                        'last' => 'Último'
                    ]
                ]
            ]);

        return view('backend.histories.index', compact('html'));
    }

    public function inactive(Datatables $datatables)
    {
        $columns = [
            'id' => ['title' => 'No.', 'orderable' => false, 'searchable' => false, 'render' => function () {
                return 'function(data,type,fullData,meta){return meta.settings._iDisplayStart+meta.row+1;}';
            }],
            'full_name' => ['title' => 'Nombre', 'name' => 'histories.name'],
            'cedula' => ['title' => 'Cédula', 'name' => 'histories.cedula'],
            'colegio_nombre' => ['title' => 'Colegio', 'name' => 'colegios.nombre'],
            'creditos' => ['title' => 'Créditos', 'name' => 'histories.creditos'],
            'download_qr_code' => ['title' => 'QR', 'orderable' => false, 'searchable' => false],
            'action' => ['orderable' => false, 'searchable' => false]
        ];

        if ($datatables->getRequest()->ajax()) {
            // Query Builder sin ->get() para server-side processing
            $query = History::where('histories.status', 0)
                ->leftJoin('colegios', 'histories.colegio_id', '=', 'colegios.id')
                ->select('histories.*', 'colegios.nombre as colegio_nombre');

            return $datatables->of($query)
                ->addColumn('full_name', function (History $data) {
                    return $data->full_name ?: $data->name;
                })
                ->addColumn('action', function (History $data) {
                    $routeEdit = route($this->getRoute() . ".edit", $data->id);
                    $routeDelete = route($this->getRoute() . ".delete", $data->id);
                    $routeTransactions = route('transactions.index', ['search' => $data->name]);

                    $button = '<div class="btn-group">';
                    $button .= '<a href="' . $routeTransactions . '" title="Transacciones"><button class="btn btn-sm btn-info"><i class="fa fa-credit-card"></i></button></a> ';
                    $button .= '<a href="' . $routeEdit . '" title="Editar"><button class="btn btn-sm btn-primary"><i class="fa fa-edit"></i></button></a> ';
                    if (Auth::user()->hasRole('administrator')) {
                        $button .= '<a href="' . $routeDelete . '" class="delete-button" title="Eliminar"><button class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button></a> ';
                        $button .= '<button class="btn btn-sm btn-success" onclick="confirmActive(' . $data->id . ')" title="Activar"><i class="fa fa-check"></i></button>';
                    }
                    $button .= '</div>';
                    return $button;
                })
                ->addColumn('download_qr_code', function (History $data) {
                    $encrypted = Crypt::encryptString($data->id);
                    return '<button class="btn btn-sm btn-danger btn-qr-download" data-qr="' . $encrypted . '" data-id="' . $data->id . '" title="Descargar QR">
                                <i class="fa fa-qrcode"></i>
                            </button>';
                })
                ->editColumn('colegio_nombre', function (History $data) {
                    return $data->colegio_nombre ?? ($data->colegio ?? '-');
                })
                ->editColumn('creditos', function (History $data) {
                    $class = $data->creditos >= 0 ? 'success' : 'danger';
                    return '<span class="badge badge-' . $class . '">₡' . number_format($data->creditos, 0, ',', '.') . '</span>';
                })
                ->filterColumn('full_name', function($query, $keyword) {
                    $query->where(function($q) use ($keyword) {
                        $q->where('histories.name', 'like', "%{$keyword}%")
                          ->orWhere('histories.first_name', 'like', "%{$keyword}%")
                          ->orWhere('histories.last_name', 'like', "%{$keyword}%");
                    });
                })
                ->rawColumns(['action', 'creditos', 'download_qr_code'])
                ->toJson();
        }

        $columnsArrExPr = [1, 2, 3, 4];
        $html = $datatables->getHtmlBuilder()
            ->columns($columns)
            ->parameters([
                'order' => [[1, 'asc']],
                'responsive' => true,
                'autoWidth' => false,
                'processing' => true,
                'serverSide' => true,
                'lengthMenu' => [
                    [10, 25, 50, 100],
                    ['10', '25', '50', '100']
                ],
                'dom' => 'Bfrtip',
                'buttons' => $this->buttonDatatables($columnsArrExPr),
                'language' => [
                    'processing' => 'Cargando...',
                    'search' => 'Buscar:',
                    'lengthMenu' => 'Mostrar _MENU_ registros',
                    'info' => 'Mostrando _START_ a _END_ de _TOTAL_ inactivos',
                    'infoEmpty' => 'Mostrando 0 a 0 de 0 inactivos',
                    'infoFiltered' => '(filtrado de _MAX_ totales)',
                    'zeroRecords' => 'No se encontraron estudiantes inactivos',
                    'emptyTable' => 'No hay estudiantes inactivos',
                    'paginate' => [
                        'first' => 'Primero',
                        'previous' => 'Anterior',
                        'next' => 'Siguiente',
                        'last' => 'Último'
                    ]
                ]
            ]);

        return view('backend.histories.history-inactives', compact('html'));
    }

    /**
     * Fungtion show button for export or print.
     *
     * @param $columnsArrExPr
     * @return array[]
     */
    public function buttonDatatables($columnsArrExPr)
    {
        return [
            [
                'pageLength'
            ],
            [
                'extend' => 'csvHtml5',
                'exportOptions' => [
                    'columns' => $columnsArrExPr
                ]
            ],
            [
                'extend' => 'pdfHtml5',
                'exportOptions' => [
                    'columns' => $columnsArrExPr
                ]
            ],
            [
                'extend' => 'excelHtml5',
                'exportOptions' => [
                    'columns' => $columnsArrExPr
                ]
            ],
            [
                'extend' => 'print',
                'exportOptions' => [
                    'columns' => $columnsArrExPr
                ]
            ],
        ];
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function add()
    {

        $data = new History();
        $data->form_action = $this->getRoute() . '.create';
        // Add page type here to indicate that the form.blade.php is in 'add' mode
        $data->page_type = 'add';
        $data->button_text = 'Add';

        $colegios = Colegio::where('estado', 'activo')->orderBy('nombre')->get();
        $becas = Beca::where('estado', 'activa')->orderBy('nombre_beca')->get();
        $rutas = Setting::where('status', 'activo')->orderBy('key_app')->get();
        $tarifas = Tarifa::where('estado', 'activa')->orderBy('monto')->get();

        return view('backend.histories.form', [
            'data' => $data,
            'colegios' => $colegios,
            'becas' => $becas,
            'rutas' => $rutas,
            'tarifas' => $tarifas,
        ]);
    }

    /**
     * Get named route depends on which user is logged in
     *
     * @return String
     */
    private function getRoute()
    {
        return 'histories';
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $new = $request->all();

        // Construir el nombre completo a partir de los campos individuales
        $new['name'] = trim($new['first_name'] . ' ' . $new['last_name'] . ' ' . ($new['second_last_name'] ?? ''));

        // Asegurarse de que los IDs están establecidos
        if (!isset($new['colegio_id']) || empty($new['colegio_id'])) {
            $new['colegio_id'] = null;
        }
        if (!isset($new['beca_id']) || empty($new['beca_id'])) {
            $new['beca_id'] = null;
        }
        if (!isset($new['ruta_id']) || empty($new['ruta_id'])) {
            $new['ruta_id'] = null;
        }
        if (!isset($new['tarifa_id']) || empty($new['tarifa_id'])) {
            $new['tarifa_id'] = null;
        }

        $this->validator($new, 'create')->validate();
        try {
            // Crear el usuario automáticamente
            $cedula = $new['cedula'];
            // Usar el email del formulario (puede ser personalizado por el usuario)
            $emailUsuario = $new['email'];

            // Verificar si el email ya existe
            $existingUser = User::where('email', $emailUsuario)->first();
            $userId = null;

            if (!$existingUser) {
                $newUser = User::create([
                    'name' => $new['name'],
                    'first_name' => $new['first_name'],
                    'last_name' => $new['last_name'],
                    'second_last_name' => $new['second_last_name'] ?? null,
                    'cedula' => $cedula,
                    'email' => $emailUsuario,
                    'password' => bcrypt($cedula),
                    'role' => 3, // Estudiante
                    'image' => 'default-user.png'
                ]);

                // Attach role estudiante
                $newUser->roles()->attach(3);
                $userId = $newUser->id;

                // Log de creación de usuario
                $controller = new SaveActivityLogController();
                $controller->saveLog(['email' => $emailUsuario, 'name' => $new['name']], "Create user for student (auto)");
            } else {
                $userId = $existingUser->id;
            }

            // Asignar user_id al history
            $new['user_id'] = $userId;
            $new['status'] = 1; // Activo

            $createNew = History::create($new);
            if ($createNew) {

                $createNew->save();

                // Create credit transaction for initial credits (recarga)
                if (isset($new['creditos']) && $new['creditos'] > 0) {
                    CreditTransaction::create([
                        'history_id' => $createNew->id,
                        'type' => 'recarga',
                        'amount' => $new['creditos'],
                        'balance_before' => 0,
                        'balance_after' => $new['creditos'],
                        'description' => 'Créditos iniciales al crear el estudiante',
                        'ruta_id' => $new['ruta_id'] ?? null,
                        'processed_by' => Auth::user()->name ?? 'system',
                        'verification_status' => 'verified'
                    ]);
                }

                // Save log
                $controller = new SaveActivityLogController();
                $controller->saveLog($new, "Create new history QR");

                // Create is successful, back to list
                $successMsg = Config::get('const.SUCCESS_CREATE_MESSAGE');
                if ($userId && !$existingUser) {
                    $successMsg .= ' Usuario creado: ' . $emailUsuario . ' (contraseña: cédula)';
                }
                return redirect()->route($this->getRoute())->with('success', $successMsg);
            }

            // Create is failed
            return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_CREATE_MESSAGE'));
        } catch (Exception $e) {
            // Create is failed
            \Log::error('Error creating history: ' . $e->getMessage());
            return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_CREATE_MESSAGE') . ' - ' . $e->getMessage());
        }
    }

    /**
     * Validator data.
     *
     * @param array $data
     * @param $type
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data, $type)
    {
        return Validator::make($data, [
            'first_name' => 'required|string|max:100',
            'last_name' => 'required|string|max:100',
            'second_last_name' => 'nullable|string|max:100',
            'name' => $type == 'create' ? 'required|string|max:255|unique:histories,name' : 'required|string|max:255|unique:histories,name,' . $data['id'],
            'colegio_id' => 'nullable|integer|exists:colegios,id',
            'beca_id' => 'nullable|integer|exists:becas,id',
            'ruta_id' => 'nullable|integer|exists:settings,id',
            'tarifa_id' => 'required|integer|exists:tarifas,id',
            'email' => 'required|email|max:255',
            'cedula' => 'required|string|max:50',
            'creditos' => 'required|integer|min:0',
            'chancesParaMarcar' => 'required|integer|min:0',
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $data = History::find($id);
        $data->form_action = $this->getRoute() . '.update';
        // Add page type here to indicate that the form.blade.php is in 'edit' mode
        $data->page_type = 'edit';
        $data->button_text = 'Edit';

        $colegios = Colegio::where('estado', 'activo')->orderBy('nombre')->get();
        $becas = Beca::where('estado', 'activa')->orderBy('nombre_beca')->get();
        $rutas = Setting::where('status', 'activo')->orderBy('key_app')->get();
        $tarifas = Tarifa::where('estado', 'activa')->orderBy('monto')->get();

        return view('backend.histories.form', [
            'data' => $data,
            'colegios' => $colegios,
            'becas' => $becas,
            'rutas' => $rutas,
            'tarifas' => $tarifas,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $new = $request->all();

        // Construir el nombre completo a partir de los campos individuales
        $new['name'] = trim($new['first_name'] . ' ' . $new['last_name'] . ' ' . ($new['second_last_name'] ?? ''));

        // Asegurarse de que los IDs están establecidos
        if (!isset($new['colegio_id']) || empty($new['colegio_id'])) {
            $new['colegio_id'] = null;
        }
        if (!isset($new['beca_id']) || empty($new['beca_id'])) {
            $new['beca_id'] = null;
        }
        if (!isset($new['ruta_id']) || empty($new['ruta_id'])) {
            $new['ruta_id'] = null;
        }
        if (!isset($new['tarifa_id']) || empty($new['tarifa_id'])) {
            $new['tarifa_id'] = null;
        }

        try {
            $currentData = History::find($request->get('id'));
            if ($currentData) {
                $this->validator($new, 'update')->validate();

                // Check if credits changed to create transaction
                $oldCredits = $currentData->creditos;
                $newCredits = $new['creditos'];
                $creditDifference = $newCredits - $oldCredits;

                // Si el history tiene user_id, actualizar también el usuario relacionado
                if ($currentData->user_id) {
                    $relatedUser = User::find($currentData->user_id);
                    if ($relatedUser) {
                        $relatedUser->name = $new['name'];
                        $relatedUser->first_name = $new['first_name'];
                        $relatedUser->last_name = $new['last_name'];
                        $relatedUser->second_last_name = $new['second_last_name'] ?? null;
                        $relatedUser->cedula = $new['cedula'];
                        $relatedUser->email = $new['email'];
                        $relatedUser->save();
                    }
                }

                // Update
                $currentData->update($new);

                // Create credit transaction if credits changed
                if ($creditDifference != 0) {
                    $transactionType = $creditDifference > 0 ? 'recarga' : 'consumo';
                    $description = $creditDifference > 0 ?
                        'Recarga de créditos (edición manual)' :
                        'Consumo de créditos (edición manual)';

                    CreditTransaction::create([
                        'history_id' => $currentData->id,
                        'type' => $transactionType,
                        'amount' => $creditDifference,
                        'balance_before' => $oldCredits,
                        'balance_after' => $newCredits,
                        'description' => $description,
                        'ruta_id' => $new['ruta_id'] ?? null,
                        'processed_by' => Auth::user()->name ?? 'system',
                        'verification_status' => 'verified'
                    ]);
                }

                // Save log
                $controller = new SaveActivityLogController();
                $controller->saveLog($new, "Update history QR");

                return redirect()->route($this->getRoute())->with('success', Config::get('const.SUCCESS_UPDATE_MESSAGE'));
            }

            // If update is failed
            return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_UPDATE_MESSAGE'));
        } catch (Exception $e) {
            // If update is failed
            return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_CREATE_MESSAGE'));
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function delete($id)
    {
        try {
            // Delete
            $new = History::find($id);
            $new->delete();

            // Save log
            $controller = new SaveActivityLogController();
            $controller->saveLog($new->toArray(), "Delete history");

            //delete success
            return redirect()->route($this->getRoute())->with('success', Config::get('const.SUCCESS_DELETE_MESSAGE'));
        } catch (Exception $e) {
            // delete failed
            return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_DELETE_MESSAGE'));
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route($this->getRoute())->with('error', Config::get('const.ERROR_FOREIGN_KEY'));
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function import()
    {

        $data = new History();
        $data->form_action = $this->getRoute() . '.importData';
        // Add page type here to indicate that the form.blade.php is in 'add' mode
        $data->page_type = 'add';
        $data->button_text = 'Import';

        return view('backend.histories.import', [
            'data' => $data,
        ]);
    }

    /**
     * Upload and import data from csv file.
     *
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function importData(Request $request)
    {
        $errorMessage = '';
        $errorMessageQr = '';
        $errorArr = array();
        $errorArrQr = array();

        // If file extension is 'csv'
        if ($request->hasFile('import')) {

            $file = $request->file('import');

            // File Details
            $extension = $file->getClientOriginalExtension();

            // If file extension is 'csv'
            if ($extension == 'csv') {

                $fp = fopen($file, 'rb');

                $header = fgetcsv($fp, 0, ',');
                $countheader = count($header);

                // Check is csv file is correct format
                if ($countheader < 7 && in_array('email', $header, true) && in_array('first_name', $header, true) && in_array('last_name', $header, true) && in_array('role', $header, true) && in_array('password', $header, true) && in_array('can_login', $header, true)) {
                    // Loop the row data csv
                    while (($csvData = fgetcsv($fp)) !== FALSE) {
                        $csvData = array_map('utf8_encode', $csvData);

                        // Row column length
                        $dataLen = count($csvData);

                        // Skip row if length != 6
                        if (!($dataLen == 6)) {
                            continue;
                        }

                        // Assign value to variables
                        $email = trim($csvData[0]);
                        $first_name = trim($csvData[1]);
                        $last_name = trim($csvData[2]);
                        $canLogin = trim($csvData[5]);
                        $name = $first_name . ' ' . $last_name;
                        $role = trim($csvData[3]);

                        // Insert data to users table
                        if ($canLogin == 'yes') {

                            // Check if any duplicate email
                            if ($this->checkDuplicate($email, 'email')) {
                                $errorArr[] = $email;
                                $str = implode(", ", $errorArr);
                                $errorMessage = '-Some data email already exists ( ' . $str . ' )';
                                continue;
                            }

                            $password = trim($csvData[4]);
                            $hashed = bcrypt($password);

                            $data = array(
                                'email' => $email,
                                'name' => $name,
                                'role' => $role,
                                'password' => $hashed,
                                'image' => 'default-user.png',
                            );

                            // create the user
                            $createNew = User::create($data);

                            // Attach role
                            $createNew->roles()->attach($role);

                            // Save user
                            $createNew->save();
                        }

                        // If Administrator will not import their QR Code
                        if ($role != 1) {
                            // Check if any duplicate name of QR code
                            if ($this->checkDuplicate($name, 'name')) {
                                $errorArrQr[] = $name;
                                $strQr = implode(", ", $errorArrQr);
                                $errorMessageQr = '-Some data name already exists ( ' . $strQr . ' )';
                                continue;
                            }

                            // Insert data to QR code
                            $dataName = array(
                                'name' => $name,
                            );

                            History::create($dataName);
                        }
                    }

                    if ($errorMessage == '' && $errorMessageQr == '') {
                        return redirect()->route($this->getRoute())->with('success', 'Imported was success!');
                    }
                    return redirect()->route($this->getRoute())->with('warning', 'Imported was success! <br><b>Note: We do not import this data data because</b><br>' . $errorMessage . '<br>' . $errorMessageQr);
                }
                return redirect()->route($this->getRoute())->with('error', 'Import failed! You are using the wrong CSV format. Please use the CSV template to import your data.');
            }
            return redirect()->route($this->getRoute())->with('error', 'Please choose file with .CSV extension.');
        }

        return redirect()->route($this->getRoute())->with('error', 'Please select CSV file.');
    }

    /**
     * Function check email is exist or not.
     *
     * @param $data
     * @param $typeCheck
     * @return bool
     */
    public function checkDuplicate($data, $typeCheck)
    {
        if ($typeCheck == 'email') {
            $isExists = User::where('email', $data)->first();
        }

        if ($typeCheck == 'name') {
            $isExists = History::where('name', $data)->first();
        }

        if ($isExists) {
            return true;
        }

        return false;
    }

    public function setInactive(Request $request)
    {
        $itemId = $request->input('id');

        $history = History::find($itemId);

        if ($history) {
            $history->status = 0;
            $history->save();

            return response()->json(['success' => true, 'message' => 'Item set as inactive successfully.']);
        }

        return response()->json(['success' => false, 'message' => 'Item not found.']);
    }

    public function setActive(Request $request)
    {
        $itemId = $request->input('id');

        $history = History::find($itemId);

        if ($history) {
            $history->status = 1;
            $history->save();

            return response()->json(['success' => true, 'message' => 'Item set as inactive successfully.']);
        }

        return response()->json(['success' => false, 'message' => 'Item not found.']);
    }

    /**
     * Download student contract
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function downloadContract($id)
    {
        $history = History::find($id);

        if (!$history) {
            return redirect()->route('histories')->with('error', 'Estudiante no encontrado.');
        }

        if (!$history->contrato_url) {
            return redirect()->route('histories')->with('error', 'Este estudiante no tiene contrato subido.');
        }

        $filePath = public_path($history->contrato_url);
        if (!file_exists($filePath)) {
            return redirect()->route('histories')->with('error', 'El archivo del contrato no existe.');
        }

        return response()->download($filePath, 'Contrato_' . $history->name . '.pdf');
    }
}
