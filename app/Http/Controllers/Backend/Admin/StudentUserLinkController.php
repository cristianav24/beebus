<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Models\History;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use Auth;

class StudentUserLinkController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Mostrar estudiantes sin usuario relacionado
     */
    public function index(Datatables $datatables)
    {
        $columns = [
            'id' => ['title' => 'ID'],
            'name' => ['title' => 'Nombre'],
            'cedula' => ['title' => 'Cédula'],
            'email' => ['title' => 'Email'],
            'user_status' => ['title' => 'Estado Usuario', 'orderable' => false, 'searchable' => false],
            'action' => ['title' => 'Acciones', 'orderable' => false, 'searchable' => false]
        ];

        if ($datatables->getRequest()->ajax()) {
            $query = History::where('status', 1)
                ->whereNull('user_id')
                ->select('histories.*');

            return $datatables->of($query)
                ->addColumn('user_status', function (History $data) {
                    return '<span class="badge badge-danger"><i class="fa fa-times"></i> Sin usuario</span>';
                })
                ->addColumn('action', function (History $data) {
                    $buttons = '<div class="btn-group">';

                    // Botón para buscar usuario existente
                    $buttons .= '<button class="btn btn-sm btn-info" onclick="searchUser(' . $data->id . ', \'' . addslashes($data->email) . '\', \'' . addslashes($data->cedula) . '\')" title="Buscar Usuario">
                                    <i class="fa fa-search"></i> Buscar
                                </button> ';

                    // Botón para crear usuario automáticamente
                    $buttons .= '<button class="btn btn-sm btn-success" onclick="createUser(' . $data->id . ')" title="Crear Usuario">
                                    <i class="fa fa-user-plus"></i> Crear
                                </button>';

                    $buttons .= '</div>';
                    return $buttons;
                })
                ->rawColumns(['user_status', 'action'])
                ->toJson();
        }

        // Contar estadísticas
        $totalWithoutUser = History::where('status', 1)->whereNull('user_id')->count();
        $totalWithUser = History::where('status', 1)->whereNotNull('user_id')->count();

        $html = $datatables->getHtmlBuilder()
            ->columns($columns)
            ->parameters([
                'order' => [[0, 'asc']],
                'responsive' => true,
                'autoWidth' => false,
                'processing' => true,
                'serverSide' => true,
                'lengthMenu' => [
                    [10, 25, 50, 100],
                    ['10', '25', '50', '100']
                ],
                'language' => [
                    'processing' => 'Cargando...',
                    'search' => 'Buscar:',
                    'lengthMenu' => 'Mostrar _MENU_ registros',
                    'info' => 'Mostrando _START_ a _END_ de _TOTAL_ estudiantes sin usuario',
                    'infoEmpty' => 'No hay estudiantes sin usuario',
                    'zeroRecords' => 'No se encontraron estudiantes',
                    'emptyTable' => 'Todos los estudiantes tienen usuario asignado',
                    'paginate' => [
                        'first' => 'Primero',
                        'previous' => 'Anterior',
                        'next' => 'Siguiente',
                        'last' => 'Último'
                    ]
                ]
            ]);

        return view('backend.admin.student-user-link', compact('html', 'totalWithoutUser', 'totalWithUser'));
    }

    /**
     * Buscar usuarios que coincidan con email o cédula
     */
    public function searchUsers(Request $request)
    {
        $email = $request->get('email');
        $cedula = $request->get('cedula');
        $search = $request->get('search');

        // Solo buscar usuarios con role 3 (estudiantes)
        $query = User::where('role', 3);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('cedula', 'like', "%{$search}%");
            });
        } else {
            $query->where(function($q) use ($email, $cedula) {
                if ($email) {
                    $q->orWhere('email', $email);
                }
                if ($cedula) {
                    $q->orWhere('cedula', $cedula);
                }
            });
        }

        $users = $query->limit(10)->get()->map(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'cedula' => $user->cedula,
                'role' => $user->role
            ];
        });

        return response()->json(['users' => $users]);
    }

    /**
     * Asignar un usuario existente a un estudiante
     */
    public function linkUser(Request $request)
    {
        $request->validate([
            'history_id' => 'required|exists:histories,id',
            'user_id' => 'required|exists:users,id'
        ]);

        $history = History::find($request->history_id);
        $user = User::find($request->user_id);

        // Verificar que el usuario no esté asignado a otro estudiante
        $existingHistory = History::where('user_id', $user->id)->first();
        if ($existingHistory) {
            return response()->json([
                'success' => false,
                'message' => 'Este usuario ya está asignado al estudiante: ' . $existingHistory->name
            ]);
        }

        $history->user_id = $user->id;
        $history->save();

        // Sincronizar datos
        $user->cedula = $history->cedula;
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Usuario vinculado correctamente'
        ]);
    }

    /**
     * Crear un usuario nuevo para el estudiante
     */
    public function createUserForStudent(Request $request)
    {
        $request->validate([
            'history_id' => 'required|exists:histories,id'
        ]);

        $history = History::find($request->history_id);

        // Verificar si ya tiene usuario
        if ($history->user_id) {
            return response()->json([
                'success' => false,
                'message' => 'Este estudiante ya tiene un usuario asignado'
            ]);
        }

        // Generar email si no tiene
        $email = $history->email;
        $cedula = $history->cedula;

        // Si no hay cédula, generar un identificador único
        if (empty($cedula)) {
            $cedula = 'EST' . str_pad($history->id, 6, '0', STR_PAD_LEFT); // Ej: EST000123
        }

        // Si no hay email o el email es inválido (solo @beebus.com)
        if (empty($email) || $email === '@beebus.com') {
            $email = strtolower($cedula) . '@beebus.com';
        }

        // Verificar si el email ya existe
        $existingUser = User::where('email', $email)->first();
        if ($existingUser) {
            // Si el usuario existe y es role 3, vincularlo
            if ($existingUser->role == 3) {
                $history->user_id = $existingUser->id;
                $history->save();

                return response()->json([
                    'success' => true,
                    'message' => 'Se encontró un usuario existente con ese email y se vinculó automáticamente',
                    'user_id' => $existingUser->id
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe un usuario con ese email pero no es estudiante (role 3)'
                ]);
            }
        }

        // Generar contraseña (usar cédula original si existe, sino el identificador generado)
        $password = $history->cedula ?: $cedula;

        // Crear nuevo usuario
        $user = User::create([
            'name' => $history->name,
            'first_name' => $history->first_name,
            'last_name' => $history->last_name,
            'second_last_name' => $history->second_last_name,
            'cedula' => $history->cedula ?: $cedula, // Guardar cédula original o generada
            'email' => $email,
            'password' => bcrypt($password),
            'role' => 3, // Estudiante
            'image' => 'default-user.png'
        ]);

        // Asignar rol
        $user->roles()->attach(3);

        // Vincular al history
        $history->user_id = $user->id;
        $history->save();

        // Actualizar el history con la cédula generada si no tenía
        if (empty($history->cedula)) {
            $history->cedula = $cedula;
            $history->save();
        }

        return response()->json([
            'success' => true,
            'message' => 'Usuario creado y vinculado correctamente. Email: ' . $email . ', Contraseña: ' . $password,
            'user_id' => $user->id
        ]);
    }

    /**
     * Vincular automáticamente todos los estudiantes por email/cédula
     */
    public function autoLinkAll()
    {
        $linked = 0;
        $created = 0;

        // Obtener estudiantes sin usuario
        $histories = History::where('status', 1)
            ->whereNull('user_id')
            ->get();

        foreach ($histories as $history) {
            // Intentar buscar por email (solo usuarios con role 3 - estudiantes)
            $user = null;
            if ($history->email) {
                $user = User::where('email', $history->email)->where('role', 3)->first();
            }

            // Si no encontró, buscar por cédula (solo usuarios con role 3)
            if (!$user && $history->cedula) {
                $user = User::where('cedula', $history->cedula)->where('role', 3)->first();
            }

            if ($user) {
                // Verificar que no esté asignado a otro
                $otherHistory = History::where('user_id', $user->id)->first();
                if (!$otherHistory) {
                    $history->user_id = $user->id;
                    $history->save();
                    $linked++;
                }
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Se vincularon automáticamente $linked estudiantes con usuarios existentes.",
            'linked' => $linked
        ]);
    }
}
