<?php

namespace App\Http\Controllers\Backend\Attendance;

use App\Http\Controllers\Controller;
use App\Models\History;
use Illuminate\Http\Request;
use Yajra\Datatables\Datatables;
use App\Models\Attendance;
use App\Models\Colegio;
use App\Models\Beca;
use App\Models\Setting;
use Auth;
use Config;

class AttendanceController extends Controller
{
    /**tipoBeca
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
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @throws \Exception
     */
    public function index(Datatables $datatables, Request $request)
    {
        $columns = [
            'student_name' => ['title' => 'Estudiante', 'name' => 'histories.name'],
            'date' => ['title' => 'Fecha', 'name' => 'attendances.date'],
            'colegio_nombre' => ['title' => 'Colegio', 'name' => 'colegios.nombre'],
            'in_time' => ['title' => 'Entrada', 'name' => 'attendances.in_time'],
            'out_time' => ['title' => 'Salida', 'name' => 'attendances.out_time'],
            'cuantoRestar' => ['title' => 'Debitado', 'name' => 'attendances.cuantoRestar'],
            'ruta_nombre' => ['title' => 'Ruta', 'name' => 'settings.key_app'],
            'in_location' => ['title' => 'Ubicación', 'orderable' => false, 'searchable' => false]
        ];

        $from = $request->dateFrom;
        $to = $request->dateTo;

        if ($datatables->getRequest()->ajax()) {
            // Query con JOINs en lugar de eager loading para mejor rendimiento
            // Nota: worker_id es la FK que conecta con histories
            $query = Attendance::leftJoin('histories', 'attendances.worker_id', '=', 'histories.id')
                ->leftJoin('colegios', 'attendances.colegio_id', '=', 'colegios.id')
                ->leftJoin('settings', 'attendances.ruta_id', '=', 'settings.id')
                ->select(
                    'attendances.*',
                    'histories.name as student_name',
                    'colegios.nombre as colegio_nombre',
                    'settings.key_app as ruta_nombre'
                );

            if ($from && $to) {
                $query->whereBetween('attendances.date', [$from, $to]);
            }

            // worker
            if (Auth::user()->hasRole('staff') || Auth::user()->hasRole('admin')) {
                $getUserInfo = History::where('name', Auth::User()->name)->first();
                if ($getUserInfo) {
                    $query->where('attendances.worker_id', $getUserInfo->id);
                } else {
                    $query->where('attendances.worker_id', 0);
                }
            }

            return $datatables->of($query)
                ->editColumn('student_name', function ($data) {
                    return $data->student_name ?: '-';
                })
                ->editColumn('colegio_nombre', function ($data) {
                    return $data->colegio_nombre ?: ($data->colegio ?: '-');
                })
                ->editColumn('ruta_nombre', function ($data) {
                    return $data->ruta_nombre ?: ($data->rutaBus ?: '-');
                })
                ->editColumn('cuantoRestar', function ($data) {
                    if ($data->cuantoRestar > 0) {
                        return '<span class="badge badge-warning">₡' . number_format($data->cuantoRestar, 0, ',', '.') . '</span>';
                    }
                    return '<span class="badge badge-secondary">₡0</span>';
                })
                ->addColumn('in_location', function ($data) {
                    if (!$data->in_location) {
                        return '-';
                    }
                    $str = $data->in_location;
                    $desde = "*"; $hasta = "*";
                    $sub = substr($str, strpos($str,$desde)+strlen($desde),strlen($str));
                    $locationSoloLoDeAsterisk = substr($sub,0,strpos($sub,$hasta));
                    $lnkAGMaps = "https://www.google.com.uy/maps/search/".$locationSoloLoDeAsterisk."/";
                    return '<a href="'.$lnkAGMaps.'" target="_blank" class="btn btn-sm btn-success"><i class="fa fa-map-marker"></i></a>';
                })
                ->rawColumns(['in_location', 'cuantoRestar'])
                ->toJson();
        }

        $columnsArrExPr = [0,1,2,3,4,5,6];
        $html = $datatables->getHtmlBuilder()
            ->columns($columns)
            ->minifiedAjax('', $this->scriptMinifiedJs())
            ->parameters([
                'order' => [[1,'desc']],
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
                    'info' => 'Mostrando _START_ a _END_ de _TOTAL_ asistencias',
                    'infoEmpty' => 'Mostrando 0 a 0 de 0 asistencias',
                    'infoFiltered' => '(filtrado de _MAX_ totales)',
                    'zeroRecords' => 'No se encontraron asistencias',
                    'emptyTable' => 'No hay asistencias registradas',
                    'paginate' => [
                        'first' => 'Primero',
                        'previous' => 'Anterior',
                        'next' => 'Siguiente',
                        'last' => 'Último'
                    ]
                ]
            ]);

        return view('backend.attendances.index', compact('html'));
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
     * Get script for the date range.
     *
     * @return string
     */
    public function scriptMinifiedJs()
    {
        // Script to minified the ajax
        return <<<CDATA
            var formData = $("#date_filter").find("input").serializeArray();
            $.each(formData, function(i, obj){
                data[obj.name] = obj.value;
            });
CDATA;
    }
}
