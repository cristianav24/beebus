<?php

namespace App\Http\Controllers\Backend\Users;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Utils\Activity\SaveActivityLogController;
use App\Models\History;
use App\Models\ParentProfile;
use App\Models\Role;
use App\Models\User;
use Auth;
use Config;
use File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Yajra\Datatables\Datatables;

class UsersController extends Controller
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
            'id' => ['title' => 'N°.', 'orderable' => false, 'searchable' => false, 'render' => function () {
                return 'function(data,type,fullData,meta){return meta.settings._iDisplayStart+meta.row+1;}';
            }],
            'image' => ['title' => 'Imagen', 'orderable' => false, 'searchable' => false],
            'full_name' => ['title' => 'Nombre', 'name' => 'name'],
            'cedula' => ['title' => 'Cédula', 'name' => 'cedula'],
            'email',
            'role_name' => ['title' => 'Role', 'name' => 'roles.display_name'],
            'created_at' => ['title' => 'Creado el'],
            'action' => ['orderable' => false, 'searchable' => false],
        ];

        if ($datatables->getRequest()->ajax()) {
            // Usar Query Builder en lugar de all() para server-side processing real
            $query = User::select('users.*', 'roles.display_name as role_name')
                ->leftJoin('roles', 'users.role', '=', 'roles.id');

            return $datatables->of($query)
                ->addColumn('image', function (User $data) {
                    $getAssetFolder = asset('uploads/' . $data->image);
                    return '<img src="' . $getAssetFolder . '" width="30px" class="img-circle elevation-2">';
                })
                ->addColumn('full_name', function (User $data) {
                    return $data->full_name ?: $data->name;
                })
                ->addColumn('action', function (User $data) {
                    $routeEdit = route($this->getRoute() . '.edit', $data->id);
                    $routeDelete = route($this->getRoute() . '.delete', $data->id);

                    if (Auth::user()->hasRole('administrator')) {
                        $button = '<a href="' . $routeEdit . '" title="Editar"><button class="btn btn-sm btn-primary"><i class="fa fa-edit"></i></button></a> ';
                        $button .= '<a href="' . $routeDelete . '" class="delete-button" title="Eliminar"><button class="btn btn-sm btn-danger"><i class="fa fa-trash"></i></button></a>';
                    } else {
                        $button = '<button class="btn btn-sm btn-primary disabled"><i class="fa fa-edit"></i></button> ';
                        $button .= '<button class="btn btn-sm btn-danger disabled"><i class="fa fa-trash"></i></button>';
                    }
                    return $button;
                })
                ->filterColumn('full_name', function($query, $keyword) {
                    $query->where(function($q) use ($keyword) {
                        $q->where('users.name', 'like', "%{$keyword}%")
                          ->orWhere('users.first_name', 'like', "%{$keyword}%")
                          ->orWhere('users.last_name', 'like', "%{$keyword}%");
                    });
                })
                ->editColumn('created_at', function (User $data) {
                    return $data->created_at ? $data->created_at->format('d/m/Y H:i') : '';
                })
                ->rawColumns(['action', 'image'])
                ->toJson();
        }

        $html = $datatables->getHtmlBuilder()
            ->columns($columns)
            ->parameters([
                'responsive' => true,
                'autoWidth' => false,
                'processing' => true,
                'serverSide' => true,
                'order' => [[0, 'desc']],
                'lengthMenu' => [
                    [10, 25, 50, 100],
                    ['10', '25', '50', '100'],
                ],
                'dom' => 'Bfrtip',
                'buttons' => ['pageLength', 'csv', 'excel', 'pdf', 'print'],
                'language' => [
                    'processing' => 'Cargando...',
                    'search' => 'Buscar:',
                    'lengthMenu' => 'Mostrar _MENU_ registros',
                    'info' => 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                    'infoEmpty' => 'Mostrando 0 a 0 de 0 registros',
                    'infoFiltered' => '(filtrado de _MAX_ registros totales)',
                    'zeroRecords' => 'No se encontraron registros',
                    'emptyTable' => 'No hay datos disponibles',
                    'paginate' => [
                        'first' => 'Primero',
                        'previous' => 'Anterior',
                        'next' => 'Siguiente',
                        'last' => 'Último'
                    ]
                ]
            ]);

        return view('backend.users.index', compact('html'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function add()
    {

        $data = new User();
        $data->form_action = $this->getRoute() . '.create';
        // Add page type here to indicate that the form.blade.php is in 'add' mode
        $data->page_type = 'add';
        $data->button_text = 'Add';

        if (Auth::user()->hasRole('administrator')) {
            return view('backend.users.form', [
                'data' => $data,
                'role' => Role::orderBy('id')->pluck('display_name', 'id'),
            ]);
        }

        return view('backend.users.form', [
            'data' => $data,
            'role' => Role::whereNotIn('id', [1, 2])->orderBy('id')->pluck('display_name', 'id'),
        ]);
    }

    /**
     * Get named route depends on which user is logged in
     *
     * @return String
     */
    private function getRoute()
    {
        return 'users';
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

        $this->validator($new, 'create')->validate();
        try {
            $new['password'] = bcrypt($new['password']);
            $createNew = User::create($new);
            if ($createNew) {
                // Attach role
                $createNew->roles()->attach($new['role']);

                // upload image
                if ($request->hasFile('image')) {
                    $file = $request->file('image');
                    ${'image'} = $createNew->id . "_image." . $file->getClientOriginalExtension();
                    $file->move(Config::get('const.UPLOAD_PATH'), ${'image'});
                    $createNew->{'image'} = ${'image'};
                } else {
                    $createNew->{'image'} = 'default-user.png';
                }

                // Si es estudiante (role=3), crear History con status=2 (pendiente)
                if ($new['role'] == 3) {
                    $historyData = [
                        'user_id' => $createNew->id,
                        'name' => $new['name'],
                        'first_name' => $new['first_name'],
                        'last_name' => $new['last_name'],
                        'second_last_name' => $new['second_last_name'] ?? null,
                        'cedula' => $new['cedula'] ?? '',
                        'email' => $new['email'],
                        'status' => 2,
                        'creditos' => 0,
                        'cuantoRestar' => 0,
                        'chancesParaMarcar' => 0,
                    ];
                    History::create($historyData);

                    $controller = new SaveActivityLogController();
                    $controller->saveLog($historyData, "Create new student (pending)");
                }

                // Si es conductor (role=2), crear History con status=1
                if ($new['role'] == 2) {
                    $new['status'] = 1;
                    History::create($new);

                    $controller = new SaveActivityLogController();
                    $controller->saveLog($new, "Create new history QR");
                }

                // Si es padre (role=4), crear ParentProfile
                if ($new['role'] == 4) {
                    ParentProfile::create([
                        'user_id' => $createNew->id,
                        'cedula' => $new['cedula'] ?? '',
                    ]);

                    $controller = new SaveActivityLogController();
                    $controller->saveLog(['user_id' => $createNew->id, 'cedula' => $new['cedula'] ?? ''], "Create new parent profile");
                }

                $createNew->save();

                $controller = new SaveActivityLogController();
                $controller->saveLog($new, "Create new user");

                return redirect()->route($this->getRoute())->with('success', Config::get('const.SUCCESS_CREATE_MESSAGE'));
            }

            return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_CREATE_MESSAGE'));
        } catch (\Exception $e) {
            \Log::error('Error creating user: ' . $e->getMessage());
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
            'cedula' => 'required|string|max:50',
            'email' => $type == 'create' ? 'email|required|string|max:255|unique:users' : 'required|string|max:255|unique:users,email,' . $data['id'],
            'password' => $type == 'create' ? 'required|string|min:6|max:255' : '',
            'name' => $type == 'create' ? 'required|string|max:255|unique:histories,name' : (($data['old_role'] == 1 || $data['old_role'] == 4) ? 'required|string|max:255' : 'required|string|max:255|unique:histories,name,' . ($data['qr_id'] ?? 0)),
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
        $data = User::find($id);
        $data->form_action = $this->getRoute() . '.update';
        // Add page type here to indicate that the form.blade.php is in 'edit' mode
        $data->page_type = 'edit';
        $data->button_text = 'Edit';

        // Get history id by user_id
        $getHistory = History::where('user_id', $data->id)->first();
        $data->qr_id = $getHistory ? $getHistory->id : 0;

        if (Auth::user()->hasRole('administrator')) {
            return view('backend.users.form', [
                'data' => $data,
                'role' => Role::orderBy('id')->pluck('display_name', 'id'),
            ]);
        }

        return view('backend.users.form', [
            'data' => $data,
            'role' => Role::whereNotIn('id', [1, 2])->orderBy('id')->pluck('display_name', 'id'),
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

        try {
            $currentData = User::find($request->get('id'));
            $new['old_role'] = $currentData->role;

            if ($currentData) {
                $this->validator($new, 'update')->validate();

                if (!$new['password']) {
                    $new['password'] = $currentData['password'];
                } else {
                    $new['password'] = bcrypt($new['password']);
                }

                if ($currentData->role != $new['role']) {
                    $currentData->roles()->sync($new['role']);
                }

                // check delete flag: [name ex: image_delete]
                if ($request->get('image_delete') != null) {
                    $new['image'] = null; // filename for db

                    if ($currentData->{'image'} != 'default-user.png') {
                        @unlink(Config::get('const.UPLOAD_PATH') . $currentData['image']);
                    }
                }

                // if new image is being uploaded
                // upload image
                if ($request->hasFile('image')) {
                    $file = $request->file('image');
                    // image file name example: [id]_image.jpg
                    ${'image'} = $currentData->id . "_image." . $file->getClientOriginalExtension();
                    $new['image'] = ${'image'};
                    // save image to the path
                    $file->move(Config::get('const.UPLOAD_PATH'), ${'image'});
                } else {
                    $new['image'] = 'default-user.png';
                }

                // Si el usuario tiene un history relacionado, sincronizar datos
                $relatedHistory = History::where('user_id', $currentData->id)->first();
                if ($relatedHistory) {
                    $relatedHistory->name = $new['name'];
                    $relatedHistory->first_name = $new['first_name'];
                    $relatedHistory->last_name = $new['last_name'];
                    $relatedHistory->second_last_name = $new['second_last_name'] ?? null;
                    $relatedHistory->cedula = $new['cedula'];
                    $relatedHistory->email = $new['email'];
                    $relatedHistory->save();

                    // Save log
                    $controller = new SaveActivityLogController();
                    $controller->saveLog($new, "Update history QR (sync from user)");
                }

                // Update
                $currentData->update($new);

                // Save log
                $controller = new SaveActivityLogController();
                $controller->saveLog($new, "Update user");

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
            if (Auth::user()->id != $id) {

                // delete
                $user = User::find($id);
                $user->detachRole($id);

                // Delete the image
                if ($user->{'image'} != 'default-user.png') {
                    @unlink(Config::get('const.UPLOAD_PATH') . $user['image']);
                }

                // Delete the data DB
                $user->delete();

                // Save log
                $controller = new SaveActivityLogController();
                $controller->saveLog($user->toArray(), "Delete user");

                //delete success
                return redirect()->route($this->getRoute())->with('success', Config::get('const.SUCCESS_DELETE_MESSAGE'));
            }
            // delete failed
            return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_DELETE_SELF_MESSAGE'));
        } catch (Exception $e) {
            // delete failed
            return redirect()->route($this->getRoute())->with('error', Config::get('const.FAILED_DELETE_MESSAGE'));
        } catch (\Illuminate\Database\QueryException $e) {
            return redirect()->route($this->getRoute())->with('error', Config::get('const.ERROR_FOREIGN_KEY'));
        }
    }
}
