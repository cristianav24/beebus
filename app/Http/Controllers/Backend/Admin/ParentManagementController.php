<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ParentProfile;
use App\Models\ParentChildRelationship;
use App\Models\History;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Yajra\Datatables\Datatables;
use Auth;
use DB;

class ParentManagementController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display list of parents
     */
    public function index(Datatables $datatables)
    {
        $columns = [
            'id' => ['title' => 'No.', 'orderable' => false, 'searchable' => false, 'render' => function () {
                return 'function(data,type,fullData,meta){return meta.settings._iDisplayStart+meta.row+1;}';
            }],
            'name' => ['title' => 'Nombre Completo'],
            'email' => ['title' => 'Email'],
            'cedula' => ['title' => 'Cédula'],
            'telefono' => ['title' => 'Teléfono'],
            'profile_status' => ['title' => 'Estado del Perfil'],
            'children_count' => ['title' => 'Hijos Asignados'],
            'created_at' => ['title' => 'Fecha Registro'],
            'action' => ['orderable' => false, 'searchable' => false, 'title' => 'Acciones']
        ];

        if ($datatables->getRequest()->ajax()) {
            return $datatables->of(
                User::with('parentProfile')
                    ->where('role', '4')
                    ->select('users.*')
                    ->get()
            )
                ->addColumn('cedula', function (User $data) {
                    if ($data->parentProfile && $data->parentProfile->cedula) {
                        return $data->parentProfile->formatted_cedula;
                    }
                    return '<span class="text-muted">No registrada</span>';
                })
                ->addColumn('telefono', function (User $data) {
                    if ($data->parentProfile && $data->parentProfile->telefono) {
                        return $data->parentProfile->formatted_telefono;
                    }
                    return '<span class="text-muted">No registrado</span>';
                })
                ->addColumn('profile_status', function (User $data) {
                    if ($data->parentProfile && $data->parentProfile->isProfileComplete()) {
                        return '<span class="badge badge-success"><i class="fas fa-check-circle"></i> Completo</span>';
                    }
                    return '<span class="badge badge-warning"><i class="fas fa-exclamation-triangle"></i> Incompleto</span>';
                })
                ->addColumn('children_count', function (User $data) {
                    $count = ParentChildRelationship::where('parent_user_id', $data->id)
                        ->where('status', 'approved')
                        ->count();

                    if ($count > 0) {
                        return '<span class="badge badge-info">' . $count . ' hijo(s)</span>';
                    }
                    return '<span class="badge badge-secondary">0 hijos</span>';
                })
                ->editColumn('created_at', function (User $data) {
                    return $data->created_at->format('d/m/Y H:i');
                })
                ->addColumn('action', function (User $data) {
                    $buttons = '<div class="btn-group" role="group">';

                    // View button
                    $buttons .= '<a href="' . route('admin.parents.show', $data->id) . '" class="btn btn-sm btn-info" title="Ver Detalles">
                    <i class="fas fa-eye"></i>
                </a>';

                    // Edit button
                    $buttons .= '<a href="' . route('admin.parents.edit', $data->id) . '" class="btn btn-sm btn-primary" title="Editar">
                    <i class="fas fa-edit"></i>
                </a>';

                    // Assign children button
                    $buttons .= '<button type="button" class="btn btn-sm btn-success assign-children-btn" 
                    data-id="' . $data->id . '" data-name="' . $data->name . '" title="Asignar Hijos">
                    <i class="fas fa-user-plus"></i>
                </button>';

                    // Delete button
                    $buttons .= '<button type="button" class="btn btn-sm btn-danger delete-btn" 
                    data-id="' . $data->id . '" data-name="' . $data->name . '" title="Eliminar">
                    <i class="fas fa-trash"></i>
                </button>';

                    $buttons .= '</div>';
                    return $buttons;
                })
                ->rawColumns(['cedula', 'telefono', 'profile_status', 'children_count', 'action'])
                ->toJson();
        }

        $html = $datatables->getHtmlBuilder()
            ->columns($columns)
            ->parameters([
                'order' => [[7, 'desc']], // Order by created_at desc
                'responsive' => true,
                'autoWidth' => false,
                'lengthMenu' => [
                    [10, 25, 50, -1],
                    ['10 rows', '25 rows', '50 rows', 'Show all']
                ],
                'dom' => 'Bfrtip',
                'buttons' => [
                    ['pageLength'],
                    [
                        'extend' => 'csvHtml5',
                        'exportOptions' => ['columns' => [1, 2, 3, 4, 5, 6, 7]]
                    ],
                    [
                        'extend' => 'excelHtml5',
                        'exportOptions' => ['columns' => [1, 2, 3, 4, 5, 6, 7]]
                    ]
                ],
            ]);

        return view('backend.admin.parents.index', compact('html'));
    }

    /**
     * Show create parent form
     */
    public function create()
    {
        // Lista de provincias de Costa Rica
        $provincias = [
            'San José',
            'Alajuela',
            'Cartago',
            'Heredia',
            'Guanacaste',
            'Puntarenas',
            'Limón'
        ];

        return view('backend.admin.parents.create', compact('provincias'));
    }

    /**
     * Store new parent
     */
    public function store(Request $request)
    {
        $validator = $this->validator($request->all(), 'create');
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            DB::beginTransaction();

            // Create user
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => '4', // Guest role
                'image' => 'default-user.png'
            ]);

            // Create parent profile
            if ($user) {
                ParentProfile::create([
                    'user_id' => $user->id,
                    'telefono' => $request->telefono,
                    'direccion' => $request->direccion,
                    'provincia' => $request->provincia,
                    'canton' => $request->canton,
                    'distrito' => $request->distrito,
                    'cedula' => $request->cedula,
                    'ocupacion' => $request->ocupacion,
                    'correo_secundario' => $request->correo_secundario
                ]);
            }

            DB::commit();

            return redirect()->route('admin.parents.index')
                ->with('success', 'Padre creado exitosamente.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Error al crear el padre: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show specific parent details
     */
    public function show($id)
    {
        $parent = User::with('parentProfile')->where('role', '4')->findOrFail($id);

        // Get relationships
        $relationships = ParentChildRelationship::with(['student.colegio', 'student.beca', 'student.ruta'])
            ->where('parent_user_id', $id)
            ->orderBy('status', 'asc')
            ->orderBy('requested_at', 'desc')
            ->get();

        return view('backend.admin.parents.show', compact('parent', 'relationships'));
    }

    /**
     * Show edit parent form
     */
    public function edit($id)
    {
        $user = User::with('parentProfile')->where('role', '4')->findOrFail($id);
        $parent = $user->parentProfile;

        // Lista de provincias de Costa Rica
        $provincias = [
            'San José',
            'Alajuela',
            'Cartago',
            'Heredia',
            'Guanacaste',
            'Puntarenas',
            'Limón'
        ];

        return view('backend.admin.parents.edit', compact('user', 'parent', 'provincias'));
    }

    /**
     * Update parent
     */
    public function update(Request $request, $id)
    {
        $validator = $this->validator($request->all(), 'update', $id);
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $user = User::where('role', '4')->findOrFail($id);

            // Update user basic info
            $userData = [
                'name' => $request->name,
                'email' => $request->email
            ];

            if ($request->password) {
                $userData['password'] = Hash::make($request->password);
            }

            $user->update($userData);

            // Update or create parent profile
            $parentData = [
                'telefono' => $request->telefono,
                'direccion' => $request->direccion,
                'provincia' => $request->provincia,
                'canton' => $request->canton,
                'distrito' => $request->distrito,
                'cedula' => $request->cedula,
                'ocupacion' => $request->ocupacion,
                'correo_secundario' => $request->correo_secundario
            ];

            ParentProfile::updateOrCreate(
                ['user_id' => $user->id],
                $parentData
            );

            return redirect()->route('admin.parents.index')
                ->with('success', 'Padre actualizado exitosamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al actualizar el padre: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Assign children to parent
     */
    public function assignChildren(Request $request)
    {
        $request->validate([
            'parent_id' => 'required|exists:users,id',
            'student_ids' => 'required|array|min:1',
            'student_ids.*' => 'required|integer|exists:histories,id'
        ]);

        try {
            $created = 0;
            $existing = 0;

            foreach ($request->student_ids as $studentId) {
                $existingRelation = ParentChildRelationship::where('parent_user_id', $request->parent_id)
                    ->where('student_id', $studentId)
                    ->first();

                if (!$existingRelation) {
                    ParentChildRelationship::create([
                        'parent_user_id' => $request->parent_id,
                        'student_id' => $studentId,
                        'status' => 'approved', // Admin assigns directly
                        'requested_at' => now(),
                        'reviewed_at' => now(),
                        'reviewed_by' => Auth::id(),
                        'admin_notes' => 'Asignado directamente por administrador'
                    ]);
                    $created++;
                } else {
                    $existing++;
                }
            }

            $message = "Se asignaron $created estudiante(s) exitosamente.";
            if ($existing > 0) {
                $message .= " $existing relación(es) ya existían.";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
                'created' => $created,
                'existing' => $existing
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al asignar estudiantes: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Search students for assignment
     */
    public function searchStudents(Request $request)
    {
        $searchTerm = $request->get('search');

        if (empty($searchTerm) || strlen($searchTerm) < 2) {
            return response()->json(['students' => []]);
        }

        $students = History::where('status', 1)
            ->where(function ($query) use ($searchTerm) {
                $query->where('name', 'LIKE', '%' . $searchTerm . '%')
                    ->orWhere('cedula', 'LIKE', '%' . $searchTerm . '%');
            })
            ->with(['colegio', 'beca'])
            ->limit(15)
            ->get()
            ->map(function ($student) {
                return [
                    'id' => $student->id,
                    'name' => $student->name,
                    'cedula' => $student->cedula,
                    'colegio' => $student->colegio ? $student->colegio : ($student->colegio ?: 'No asignado'),
                    'seccion' => $student->seccion,
                    'beca' => $student->beca ? $student->beca->nombre_beca : ($student->tipoBeca ?: 'Sin beca'),
                    'creditos' => $student->creditos
                ];
            });

        return response()->json(['students' => $students]);
    }

    /**
     * Remove parent-child relationship
     */
    public function removeRelationship(Request $request)
    {
        $request->validate([
            'relationship_id' => 'required|exists:parent_child_relationships,id'
        ]);

        try {
            $relationship = ParentChildRelationship::findOrFail($request->relationship_id);
            
            $relationship->delete();

            return response()->json([
                'success' => true,
                'message' => 'Relación eliminada exitosamente. El padre ya no tiene acceso a la información del estudiante.'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la relación: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Delete parent
     */
    public function destroy($id)
    {
        try {
            $user = User::where('role', '4')->findOrFail($id);

            // Check if parent has assigned children
            $hasChildren = ParentChildRelationship::where('parent_user_id', $id)->exists();

            if ($hasChildren) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar el padre porque tiene hijos asignados. Primero debe desasignar todos los hijos.'
                ]);
            }

            // Delete parent profile first
            ParentProfile::where('user_id', $id)->delete();

            // Delete user
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'Padre eliminado exitosamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el padre: ' . $e->getMessage()
            ]);
        }
    }

    /**
     * Validator for parent data
     */
    protected function validator(array $data, $type, $id = null)
    {
        $rules = [
            'name' => 'required|string|max:255',
            'telefono' => 'required|string|max:20|regex:/^[0-9\-\s\+\(\)]+$/',
            'direccion' => 'required|string|max:255',
            'provincia' => 'required|string|max:100',
            'canton' => 'required|string|max:100',
            'distrito' => 'nullable|string|max:100',
            'cedula' => 'required|string|max:20|regex:/^[0-9\-]+$/',
            'ocupacion' => 'nullable|string|max:100',
            'correo_secundario' => 'nullable|string|email|max:150'
        ];

        if ($type === 'create') {
            $rules['email'] = 'required|string|email|max:255|unique:users';
            $rules['password'] = 'required|string|min:6|max:255';
        } else {
            $rules['email'] = 'required|string|email|max:255|unique:users,email,' . $id;
            $rules['password'] = 'nullable|string|min:6|max:255';
        }

        return Validator::make($data, $rules, [
            'name.required' => 'El nombre completo es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El formato del correo electrónico no es válido.',
            'email.unique' => 'Este correo electrónico ya está en uso.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 6 caracteres.',
            'telefono.required' => 'El número de teléfono es obligatorio.',
            'telefono.regex' => 'El formato del teléfono no es válido.',
            'direccion.required' => 'La dirección es obligatoria.',
            'provincia.required' => 'La provincia es obligatoria.',
            'canton.required' => 'El cantón es obligatorio.',
            'cedula.required' => 'La cédula de identidad es obligatoria.',
            'cedula.regex' => 'El formato de la cédula no es válido.',
            'correo_secundario.email' => 'El formato del correo secundario no es válido.'
        ]);
    }
}
