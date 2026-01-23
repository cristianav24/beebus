{{-- resources/views/admin/dashboard.blade.php --}}

@extends('adminlte::page')

@section('title', 'Marcas  | ' . Config::get('adminlte.title'))

@section('content_header')
    <h1>Marcas</h1>
@stop

@section('content')
    {{--Show message if any--}}
    @include('layouts.flash-message')

    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Listado de Asistencias</h3>
        </div>
    <div class="card-body">
     
      <?php 
    $hostVar = env('DB_HOST'); 
    $userVar = env('DB_USERNAME');
    $passVar = env('DB_PASSWORD');
    $dbNameVar = env('DB_DATABASE');
    ?>
        
    <?php
    $db=new PDO("mysql:dbname=$dbNameVar;host=$hostVar;","$userVar","$passVar"); 
    
    $row=$db->prepare ("SELECT attendances.rutaBus, COUNT(*) as cantidad 
    FROM attendances WHERE attendances.rutaBus <> ''
    GROUP BY attendances.rutaBus");
    
    $row->execute(); 
    $data = array();
    foreach($row as $rec)  
    { 
        $data[] = array(
          'label'  => $rec["rutaBus"],
          'value'  => $rec["cantidad"]
         );
    } 
    
   $data = json_encode($data);
    
    { ?>
    
    <div id="asistenciasbeebus" style="height: 225px;"></div>
        
        <script> type="text/javascript">
            new Morris.Donut({
            element: 'asistenciasbeebus',
            data: <?php echo $data; ?>
            });
        </script>
    
    <?php } ?>

        <div class="card-body">
            <!-- Filtering -->
            <div id="date_filter" class="form-inline">
                <div class="form-group mb-2">
                    <label for="from"></label>
                    <div class="input-group">
                        <input type="text" name="dateFrom" class="form-control" id="min" placeholder="From Date" autocomplete="off">
                        <div class="input-group-append" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </div>
                <div class="form-group mx-sm-3 mb-2">
                    <label for="to"></label>
                    <div class="input-group">
                        <input type="text" name="dateTo" class="form-control" id="max" placeholder="To Date" autocomplete="off">
                        <div class="input-group-append" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <hr>
            <!-- Filtering -->

            <div class="table-responsive">
                {!! $html->table(['class' => 'table table-hover']) !!}
            </div>
        </div>
    </div>
@stop

@section('css')
    <link href="{{ asset('vendor/datatables/css/dataTables.bootstrap4.css') }}" rel="stylesheet">
    <link href="{{ asset('vendor/datatables-plugins/buttons/css/buttons.bootstrap4.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap-datepicker/css/bootstrap-datepicker.css') }}">
    <script src="//ajax.googleapis.com/ajax/libs/jquery/1.9.0/jquery.min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/raphael/2.1.0/raphael-min.js"></script>
    <script src="//cdnjs.cloudflare.com/ajax/libs/morris.js/0.5.1/morris.min.js"></script>
@stop

@section('js')
    <!--Data tables-->
    <script src="{{ asset('vendor/datatables/buttons.server-side.js') }}"></script>
    <script src="{{ asset('vendor/datatables-plugins/jszip/jszip.js') }}"></script>
    <script src="{{ asset('vendor/datatables-plugins/pdfmake/pdfmake.js') }}"></script>
    <script src="{{ asset('vendor/datatables-plugins/pdfmake/vfs_fonts.js') }}"></script>
    {{--Button--}}
    <script src="{{ asset('vendor/datatables-plugins/buttons/js/dataTables.buttons.js') }}"></script>
    <script src="{{ asset('vendor/datatables-plugins/buttons/js/buttons.flash.min.js') }}"></script>
    <script src="{{ asset('vendor/datatables-plugins/buttons/js/buttons.colVis.js') }}"></script>
    <script src="{{ asset('vendor/datatables-plugins/buttons/js/buttons.html5.js') }}"></script>
    <script src="{{ asset('vendor/datatables-plugins/buttons/js/buttons.print.js') }}"></script>
    {!! $html->scripts() !!}
    {{--Datepicker--}}
    <script src="{{ asset('vendor/bootstrap-datepicker/js/bootstrap-datepicker.min.js') }}"></script>
    <script src="{{ asset('js/main_index.js'). '?v=' . rand(99999,999999) }}"></script>
    <script>
        $(document).ready(function () {
            $('#min, #max').change(function () {
                window.LaravelDataTables["dataTableBuilder"].draw();
            });

            $('#min').datepicker({
                format: 'yyyy-mm-dd',
                todayHighlight: 'TRUE',
                autoclose: true,
                changeMonth: true,
                changeYear: true,
                onSelect: function () {
                    window.LaravelDataTables["dataTableBuilder"].draw();
                },
            });

            $("#max").datepicker({
                format: 'yyyy-mm-dd',
                todayHighlight: 'TRUE',
                autoclose: true,
                changeMonth: true,
                changeYear: true,
                onSelect: function () {
                    window.LaravelDataTables["dataTableBuilder"].draw();
                },
            });
        });
    </script>
@stop
