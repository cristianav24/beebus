<?php

namespace App\Http\Controllers\Backend\Admin;

use App\Http\Controllers\Controller;
use App\Models\ParentChildRelationship;
use App\Models\User;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use Auth;
use Config;

class ParentRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Display parent-child relationship requests
     */
    public function index(Datatables $datatables)
    {
        $columns = [
            'id' => ['title' => 'No.', 'orderable' => false, 'searchable' => false, 'render' => function () {
                return 'function(data,type,fullData,meta){return meta.settings._iDisplayStart+meta.row+1;}';
            }],
            'parent_name' => ['title' => 'Padre/Tutor'],
            'parent_email' => ['title' => 'Email del Padre'],
            'parent_cedula' => ['title' => 'Cédula del Padre'],
            'student_name' => ['title' => 'Estudiante'],
            'student_cedula' => ['title' => 'Cédula Estudiante'],
            'student_colegio' => ['title' => 'Colegio'],
            'status' => ['title' => 'Estado'],
            'requested_at' => ['title' => 'Fecha Solicitud'],
            'action' => ['orderable' => false, 'searchable' => false, 'title' => 'Acciones']
        ];

        if ($datatables->getRequest()->ajax()) {
            return $datatables->of(
                ParentChildRelationship::with([
                    'parent',
                    'student.colegio',
                    'student.beca',
                    'reviewer'
                ])
                    ->select('parent_child_relationships.*')
                    ->get()
            )
                ->addColumn('parent_name', function (ParentChildRelationship $data) {
                    return $data->parent ? $data->parent->name : 'N/A';
                })
                ->addColumn('parent_email', function (ParentChildRelationship $data) {
                    return $data->parent ? $data->parent->email : 'N/A';
                })
                ->addColumn('parent_cedula', function (ParentChildRelationship $data) {
                    if (!$data->parent) return 'N/A';
                    $parentProfile = \App\Models\ParentProfile::where('user_id', $data->parent_user_id)->first();
                    return $parentProfile && $parentProfile->cedula ? $parentProfile->formatted_cedula : 'No registrada';
                })
                ->addColumn('student_name', function (ParentChildRelationship $data) {
                    return $data->student ? $data->student->name : 'N/A';
                })
                ->addColumn('student_cedula', function (ParentChildRelationship $data) {
                    return $data->student ? $data->student->cedula : 'N/A';
                })
                ->addColumn('student_colegio', function (ParentChildRelationship $data) {
                    if (!$data->student) return 'N/A';
                    return $data->student->colegio ? $data->student->colegio : ($data->student->colegio ?: 'No asignado');
                })
                ->editColumn('status', function (ParentChildRelationship $data) {
                    switch ($data->status) {
                        case 'pending':
                            return '<span class="badge badge-warning"><i class="fas fa-clock"></i> Pendiente</span>';
                        case 'approved':
                            return '<span class="badge badge-success"><i class="fas fa-check"></i> Aprobado</span>';
                        case 'rejected':
                            return '<span class="badge badge-danger"><i class="fas fa-times"></i> Rechazado</span>';
                        default:
                            return '<span class="badge badge-secondary">Desconocido</span>';
                    }
                })
                ->editColumn('requested_at', function (ParentChildRelationship $data) {
                    return $data->requested_at ? $data->requested_at->format('d/m/Y H:i') : '';
                })
                ->addColumn('action', function (ParentChildRelationship $data) {
                    $buttons = '<div class="btn-group" role="group">';

                    // View button
                    $buttons .= '<a href="' . route('admin.parent-requests.show', $data->id) . '" class="btn btn-sm btn-info" title="Ver Detalles">
                    <i class="fas fa-eye"></i>
                </a>';

                    if ($data->status === 'pending') {
                        // Approve button
                        $buttons .= '<button type="button" class="btn btn-sm btn-success approve-btn" 
                        data-id="' . $data->id . '" title="Aprobar">
                        <i class="fas fa-check"></i>
                    </button>';

                        // Reject button
                        $buttons .= '<button type="button" class="btn btn-sm btn-danger reject-btn" 
                        data-id="' . $data->id . '" title="Rechazar">
                        <i class="fas fa-times"></i>
                    </button>';
                    }

                    $buttons .= '</div>';
                    return $buttons;
                })
                ->rawColumns(['status', 'action'])
                ->toJson();
        }

        $html = $datatables->getHtmlBuilder()
            ->columns($columns)
            ->parameters([
                'order' => [[8, 'desc']], // Order by requested_at desc
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
                        'exportOptions' => ['columns' => [1, 2, 3, 4, 5, 6, 7, 8]]
                    ],
                    [
                        'extend' => 'excelHtml5',
                        'exportOptions' => ['columns' => [1, 2, 3, 4, 5, 6, 7, 8]]
                    ]
                ],
            ]);

        return view('backend.admin.parent-requests.index', compact('html'));
    }

    /**
     * Show specific request details
     */
    public function show($id)
    {
        $request = ParentChildRelationship::with([
            'parent',
            'student.colegio',
            'student.beca',
            'student.ruta',
            'reviewer'
        ])->findOrFail($id);

        return view('backend.admin.parent-requests.show', compact('request'));
    }

    /**
     * Approve a parent-child relationship request
     */
    public function approve(Request $request, $id)
    {
        $request->validate([
            'notes' => 'nullable|string|max:1000'
        ]);

        $relationship = ParentChildRelationship::findOrFail($id);

        if ($relationship->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Esta solicitud ya ha sido procesada.'
            ]);
        }

        $success = $relationship->approve(Auth::id(), $request->notes);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Solicitud aprobada exitosamente.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Error al procesar la solicitud.'
        ]);
    }

    /**
     * Reject a parent-child relationship request
     */
    public function reject(Request $request, $id)
    {
        $request->validate([
            'reason' => 'required|string|max:500',
            'notes' => 'nullable|string|max:1000'
        ]);

        $relationship = ParentChildRelationship::findOrFail($id);

        if ($relationship->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Esta solicitud ya ha sido procesada.'
            ]);
        }

        $success = $relationship->reject(Auth::id(), $request->reason, $request->notes);

        if ($success) {
            return response()->json([
                'success' => true,
                'message' => 'Solicitud rechazada exitosamente.'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Error al procesar la solicitud.'
        ]);
    }
}
