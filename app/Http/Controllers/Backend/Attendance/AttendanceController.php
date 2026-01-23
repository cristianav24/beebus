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
            'name' => ['name' => 'history.name'],
            'date' => ['title' => 'Fecha'],
            'beca_nombre' => ['title' => 'Beca'],
            'colegio_nombre' => ['title' => 'Colegio'],
            'in_time' => ['title' => 'Marca'],
            'out_time' => ['title' => 'Marca 2'],
            'cuantoRestar' => ['title' => 'Debitado'],
            'ruta_nombre' => ['title' => 'Bus-Ruta'],
            'in_location' => ['title' => 'Ubicacion']
        ];

        $from = date($request->dateFrom);
        $to = date($request->dateTo);

        if ($datatables->getRequest()->ajax()) {
            $query = Attendance::with(['history', 'colegio', 'beca', 'ruta'])
                ->select('attendances.*');

            if ($from && $to) {
                $query = $query->whereBetween('attendances.date', [$from, $to]);
            }

            // worker
            if (Auth::user()->hasRole('staff') || Auth::user()->hasRole('admin')) {
                $getUserInfo = History::where('name', Auth::User()->name)->first();
                if ($getUserInfo) {
                    // There is any worker
                    $query = $query->where('worker_id', $getUserInfo->id);
                } else {
                    // If there is no data attendance for this worker we add 0 id, mean data not found
                    $query = $query->where('worker_id', 0);
                }
            }

            return $datatables->of($query)
                ->addColumn('name', function (Attendance $data) {
                    return $data->history->name;
                })
                ->addColumn('beca_nombre', function (Attendance $data) {
                    if ($data->beca && !empty($data->beca->nombre_beca)) {
                        return $data->beca->nombre_beca;
                    }
                    return $data->tipoBeca ?: 'Sin beca';
                })
                ->addColumn('colegio_nombre', function (Attendance $data) {
                    if ($data->colegio && !empty($data->colegio->nombre)) {
                        return $data->colegio->nombre;
                    }
                    return $data->colegio ?: 'Sin colegio';
                })
                ->addColumn('ruta_nombre', function (Attendance $data) {
                    if ($data->ruta && !empty($data->ruta->key_app)) {
                        return $data->ruta->key_app;
                    }
                    return $data->rutaBus ?: 'Sin ruta';
                })
                ->addColumn('in_location', function (Attendance $data){
                    $str = $data->in_location;
                    $desde = "*"; $hasta = "*";
                    $sub = substr($str, strpos($str,$desde)+strlen($desde),strlen($str));
                    $locationSoloLoDeAsterisk = substr($sub,0,strpos($sub,$hasta));
                    $lnkAGMaps = "https://www.google.com.uy/maps/search/".$locationSoloLoDeAsterisk."/";
                    //return '<a href="'.$lnkAGMaps.'" target="_blank"><button class="btn btn-sm btn-success">'.$data->in_location.'</button></a>';
                    return '<a href="'.$lnkAGMaps.'" target="_blank"><button class="btn btn-sm btn-success">Mapa</button></a>';
                })
                ->rawColumns(['name', 'in_location'])
                ->toJson();
        }

        $columnsArrExPr = [0,1,2,3,4,5,7];
        $html = $datatables->getHtmlBuilder()
            ->columns($columns)
            ->minifiedAjax('', $this->scriptMinifiedJs())
            ->parameters([
                'order' => [[1,'desc'], [2,'desc']],
                'responsive' => true,
                'autoWidth' => false,
                'lengthMenu' => [
                    [ 10, 25, 50, -1 ],
                    [ '10 rows', '25 rows', '50 rows', 'Show all' ]
                ],
                'dom' => 'Bfrtip',
                'buttons' => $this->buttonDatatables($columnsArrExPr),
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
