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
            'name',
            'cedula',
            'colegio_nombre' => ['title' => 'Colegio'],
            'seccion',
            'beca_nombre' => ['title' => 'Tipo de Beca'],
            'ruta_nombre' => ['title' => 'Ruta'],
            'email' => ['title' => 'Email'],
            'creditos'  => ['title' => 'Creditos'],
            'cobro_info'  => ['title' => 'Información de Cobro'],
            'chancesParaMarcar'  => ['title' => 'Chances Rest.'],
            'download_qr_code',
            'created_at' => ['title' => 'Fecha Creación'],
            'updated_at' => ['title' => 'Última Actualización'],
            'action' => ['orderable' => false, 'searchable' => false]
        ];

        if ($datatables->getRequest()->ajax()) {
            return $datatables->of(History::where('histories.status', 1)
                ->leftJoin('colegios', 'histories.colegio_id', '=', 'colegios.id')
                ->leftJoin('becas', 'histories.beca_id', '=', 'becas.id')
                ->leftJoin('settings', 'histories.ruta_id', '=', 'settings.id')
                ->select(
                    'histories.*',
                    'colegios.nombre as colegio_nombre',
                    'becas.nombre_beca as beca_nombre',
                    'becas.monto_creditos as beca_monto_creditos',
                    'settings.key_app as ruta_nombre'
                )
                ->get())
                ->addColumn('action', function (History $data) {
                    $routeEdit = route($this->getRoute() . ".edit", $data->id);
                    $routeDelete = route($this->getRoute() . ".delete", $data->id);
                    $routeTransactions = route('transactions.index', ['search' => $data->name]);

                    $button = '<div class="row"><div class="col-sm-12">';
                    $button .= '<div class="col-sm-3"><a href="' . $routeTransactions . '" title="Ver Transacciones de Crédito"><button class="btn btn-info"><i class="fa fa-credit-card"></i></button></a></div> ';
                    $button .= '<div class="col-sm-3"><a href="' . $routeEdit . '" title="Editar"><button class="btn btn-primary"><i class="fa fa-edit"></i></button></a></div> ';
                    if (Auth::user()->hasRole('administrator')) { // Check the role
                        $button .= '<div class="col-sm-3"><a href="' . $routeDelete . '" class="delete-button" title="Eliminar"><button class="btn btn-danger"><i class="fa fa-trash"></i></button></a></div>';
                        $button .= '<div class="col-sm-3">
                            <button class="btn btn-warning" onclick="confirmInactive(' . $data->id . ')" title="Inactivar">
                                <i class="fa fa-ban"></i>
                            </button>
                        </div>';
                    } else {
                        $button .= '<div class="col-sm-3"><a href="#" title="Sin permisos"><button class="btn btn-danger disabled"><i class="fa fa-trash"></i></button></a></div>';
                        $button .= '<div class="col-sm-3"><button class="btn btn-warning disabled" title="Sin permisos"><i class="fa fa-ban"></i></button></div>';
                    }
                    $button .= '</div></div>';
                    return $button;
                })
                ->addColumn('download_qr_code', function (History $data) {
                    return Crypt::encryptString($data->id);
                })
                ->editColumn('colegio_nombre', function (History $data) {
                    return $data->colegio_nombre ?? ($data->colegio ?? 'Sin asignar');
                })
                ->editColumn('beca_nombre', function (History $data) {
                    return $data->beca_nombre ?? ($data->tipoBeca ?? 'Sin beca');
                })
                ->editColumn('ruta_nombre', function (History $data) {
                    return $data->ruta_nombre ?? 'Sin ruta';
                })
                ->addColumn('cobro_info', function (History $data) {
                    // Si tiene beca, mostrar monto_creditos de la beca
                    if ($data->beca_id && $data->beca_monto_creditos !== null) {
                        return '<span class="badge badge-success">
                                    <i class="fa fa-graduation-cap"></i> 
                                    Beca: ₡' . number_format($data->beca_monto_creditos, 0, ',', '.') . '
                                </span>';
                    } else {
                        // Si no tiene beca, mostrar cuantoRestar
                        return '<span class="badge badge-warning">
                                    <i class="fa fa-money"></i> 
                                    Por asistencia: ₡' . number_format($data->cuantoRestar, 0, ',', '.') . '
                                </span>';
                    }
                })
                ->editColumn('created_at', function (History $data) {
                    return $data->created_at ? $data->created_at->format('d/m/Y H:i') : '';
                })
                ->editColumn('updated_at', function (History $data) {
                    return $data->updated_at ? $data->updated_at->format('d/m/Y H:i') : '';
                })
                ->rawColumns(['action', 'cobro_info'])
                ->toJson();
        }

        $columnsArrExPr = [1, 2, 3, 4, 5];
        $html = $datatables->getHtmlBuilder()
            ->columns($columns)
            ->parameters([
                'order' => [[1, 'desc']],
                'responsive' => true,
                'autoWidth' => false,
                'lengthMenu' => [
                    [10, 25, 50, -1],
                    ['10 rows', '25 rows', '50 rows', 'Show all']
                ],
                'dom' => 'Bfrtip',
                'buttons' => $this->buttonDatatables($columnsArrExPr),
            ]);

        return view('backend.histories.index', compact('html'));
    }

    public function inactive(Datatables $datatables)
    {
        $columns = [
            'id' => ['title' => 'No.', 'orderable' => false, 'searchable' => false, 'render' => function () {
                return 'function(data,type,fullData,meta){return meta.settings._iDisplayStart+meta.row+1;}';
            }],
            'name',
            'cedula',
            'colegio',
            'seccion',
            'tipoBeca' => ['title' => 'Tipo de Beca'],
            'email' => ['title' => 'Email'],
            'creditos'  => ['title' => 'Creditos'],
            'cuantoRestar'  => ['title' => 'Cuanto resta?'],
            'chancesParaMarcar'  => ['title' => 'Chances Rest.'],
            'download_qr_code',
            'created_at',
            'updated_at',
            'action' => ['orderable' => false, 'searchable' => false]
        ];

        if ($datatables->getRequest()->ajax()) {
            return $datatables->of(History::where('status', 0)->get())
                ->addColumn('action', function (History $data) {
                    $routeEdit = route($this->getRoute() . ".edit", $data->id);
                    $routeDelete = route($this->getRoute() . ".delete", $data->id);
                    $routeTransactions = route('transactions.index', ['search' => $data->name]);

                    $button = '<div class="row"><div class="col-sm-12">';
                    $button .= '<div class="col-sm-3"><a href="' . $routeTransactions . '" title="Ver Transacciones de Crédito"><button class="btn btn-info"><i class="fa fa-credit-card"></i></button></a></div> ';
                    $button .= '<div class="col-sm-3"><a href="' . $routeEdit . '" title="Editar"><button class="btn btn-primary"><i class="fa fa-edit"></i></button></a></div> ';
                    if (Auth::user()->hasRole('administrator')) { // Check the role
                        $button .= '<div class="col-sm-3"><a href="' . $routeDelete . '" class="delete-button" title="Eliminar"><button class="btn btn-danger"><i class="fa fa-trash"></i></button></a></div>';
                        $button .= '<div class="col-sm-3">
                            <button class="btn btn-success" onclick="confirmActive(' . $data->id . ')" title="Activar">
                                <i class="fa fa-check"></i>
                            </button>
                        </div>';
                    } else {
                        $button .= '<div class="col-sm-3"><a href="#" title="Sin permisos"><button class="btn btn-danger disabled"><i class="fa fa-trash"></i></button></a></div>';
                        $button .= '<div class="col-sm-3"><button class="btn btn-success disabled" title="Sin permisos"><i class="fa fa-check"></i></button></div>';
                    }
                    $button .= '</div></div>';
                    return $button;
                })
                ->addColumn('download_qr_code', function (History $data) {
                    return Crypt::encryptString($data->id);
                })
                ->toJson();
        }

        $columnsArrExPr = [1, 2, 3, 4, 5];
        $html = $datatables->getHtmlBuilder()
            ->columns($columns)
            ->parameters([
                'order' => [[1, 'desc']],
                'responsive' => true,
                'autoWidth' => false,
                'lengthMenu' => [
                    [10, 25, 50, -1],
                    ['10 rows', '25 rows', '50 rows', 'Show all']
                ],
                'dom' => 'Bfrtip',
                'buttons' => $this->buttonDatatables($columnsArrExPr),
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

        return view('backend.histories.form', [
            'data' => $data,
            'colegios' => $colegios,
            'becas' => $becas,
            'rutas' => $rutas,
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

        $this->validator($new, 'create')->validate();
        try {
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
                return redirect()->route($this->getRoute())->with('success', Config::get('const.SUCCESS_CREATE_MESSAGE'));
            }

            // Create is failed
            return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_CREATE_MESSAGE'));
        } catch (Exception $e) {
            // Create is failed
            return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_CREATE_MESSAGE'));
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
        // Determine if password validation is required depending on the calling
        return Validator::make($data, [
            'name' => $type == 'create' ? 'required|string|max:255|unique:histories,name' : 'required|string|max:255|unique:histories,name,' . $data['id'],
            'colegio_id' => 'nullable|integer|exists:colegios,id',
            'beca_id' => 'nullable|integer|exists:becas,id',
            'ruta_id' => 'nullable|integer|exists:settings,id',
            'email' => 'required|email|max:255',
            'cedula' => 'required|string|max:50',
            'creditos' => 'required|integer|min:0',
            'cuantoRestar' => 'required|integer|min:0',
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

        return view('backend.histories.form', [
            'data' => $data,
            'colegios' => $colegios,
            'becas' => $becas,
            'rutas' => $rutas,
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

        try {
            $currentData = History::find($request->get('id'));
            if ($currentData) {
                $this->validator($new, 'update')->validate();

                // Check if credits changed to create transaction
                $oldCredits = $currentData->creditos;
                $newCredits = $new['creditos'];
                $creditDifference = $newCredits - $oldCredits;

                // If user change name will change name also on user DB
                $changeName = User::where('name', $currentData->name)->first();
                if ($changeName) {
                    $changeName->name = $new['name'];
                    $changeName->cedula = $new['cedula'];
                    $changeName->colegio = $new['colegio'];
                    $changeName->seccion = $new['seccion'];
                    $changeName->tipoBeca = $new['tipoBeca'];
                    $changeName->email = $new['email'];
                    $changeName->cuantoRestar = $new['cuantoRestar'];
                    $changeName->creditos = $new['creditos'];
                    $changeName->chancesParaMarcar = $new['chancesParaMarcar'];
                    $changeName->save();
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
}
