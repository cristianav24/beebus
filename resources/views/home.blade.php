{{-- resources/views/admin/dashboard.blade.php --}}

@extends('adminlte::page')

@section('title', 'Dashboard  | ' . Config::get('adminlte.title'))

@section('content_header')
    <h2>Dashboard</h2>
    
    
    </head>
  <script
			  src="https://code.jquery.com/jquery-3.4.1.min.js"
			  integrity="sha256-CSXorXvZcTkaix6Yvo6HppcZGetbYMGWSFlBw8HfCJo="
			  crossorigin="anonymous"></script>
<body>
    <h5>Hora actual <strong></strong>
<h7 id="HoraActual"> </h7>
</body>


     <!--<h2>Hola envianos el <strong>Nombre, Número de cédula y Ruta</strong> de tu hijo o hija para activar los creditos en su cuenta!</h2>
     <a href="#"onClick="window.open('https://wa.link/3sgt7h')" class="btn btn-danger btn-labeled btn-labeled-left mr-2"> <b><i class="icon-stack-star mr-2"></i></b>Enviar datos</a>-->
    
@stop

@section('content')
<meta http-equiv="refresh" content="60" >

<meta charset="UTF-8">

    <div class="card">
        <div class="card-body">
            @if (session('status'))
                <div class="alert alert-success" role="alert">
                    {{ session('status') }}
                </div>
            @endif

            Hola, Bienvenidos!!!
            
            <?php 
            $hostVar = env('DB_HOST'); 
            $userVar = env('DB_USERNAME');
            $passVar = env('DB_PASSWORD');
            $dbNameVar = env('DB_DATABASE');
            ?>
            
                <br /> <br />  
            <div>
                <form action="" method="get">  
                        <b>Ingrese cédula para buscar asistencias y saber creditos</b> <input type="text" name="c" />
                        <br />  
                    <input type="submit" value="Buscar" class="btn btn-success btn-sm" />  
                </form>
            </div> <br> 
            
                <?php
					$host = $hostVar;
					$user = $userVar;
					$password = $passVar; 
					$dbname = $dbNameVar; 
					$con = mysqli_connect($host, $user, $password,$dbname);
					if (!$con) {
					die("Error de conexión... " . mysqli_connect_error());
					}
				?>
				
				<?php 
				    $cedula = Request::query('c', false);
				?>
            
            <div class="card shadow-sm border-primary" style="max-width: 400px; margin-top: 20px; background-color: orange;">
    <div class="card-body text-center">
        <?php
            $cedula = Request::query('c', false);
            $dataUser = mysqli_query($con, "
                SELECT histories.cedula, histories.name, histories.creditos
                FROM histories
                WHERE histories.cedula = '$cedula'");
            $numRows = mysqli_num_rows($dataUser);
            
            if ($numRows == 1) {
                while ($rowDataUser = mysqli_fetch_array($dataUser)) {
        ?>
        <h5 class="card-title text-primary">Usuario: <strong><?php echo $rowDataUser['name']; ?></strong></h5>
        <p class="card-text">Cédula: <strong><?php echo $rowDataUser['cedula']; ?></strong></p>
        <p class="card-text">Créditos disponibles: 
            <span class="badge badge-success" style="font-size: 1.2rem; padding: 10px;">
                <?php echo $rowDataUser['creditos']; ?>
            </span>
        </p>
        <?php
                }
            }
        ?>
    </div>
</div>
        </div>
    </div>
    
    <div class="row">
        <div class="col-lg-12 col-12">
            
				<!-- Listado de Tecnicos -->
				<div class="table-responsive">  
					<table id="grillaAsistencias" class="table table-striped table-bordered dt-responsive nowrap">
						<thead>
							<tr>
							    <th><strong>NOMBRE</strong></th>
							    <th><strong>Cédula</strong></th>
								<th><strong>Fecha</strong></th>
								<th><strong>Marca</strong></th>
								<th><strong>Creditos Debitados</strong></th>
								<th><strong>BUS-Ruta</strong></th>
								<th><strong>Ubicación</strong></th>
							</tr>
						</thead>
						<tbody>
							<?php
								$lstEquipos = mysqli_query($con,"
								SELECT attendances.*, histories.cedula, histories.name
								FROM attendances INNER JOIN histories 
								ON attendances.worker_id = histories.id
								AND histories.cedula<>''
								WHERE histories.cedula = '$cedula' ORDER BY date");
								while ($rowData = mysqli_fetch_array($lstEquipos)) {
							?>
							<tr>
								<td><?php echo $rowData['name']; ?> </td>
								<td><?php echo $rowData['cedula']; ?> </td>
								<td><?php echo $rowData['date']; ?></td>
								<td><?php echo $rowData['in_time']; ?></td>
								<td><?php echo $rowData['cuantoRestar']; ?></td>
								<td><?php echo $rowData['rutaBus']; ?></td>
								
								<?php
								$str = $rowData['in_location'];
                                $desde = "*"; $hasta = "*";
                                $sub = substr($str, strpos($str,$desde)+strlen($desde),strlen($str));
                                $locationSoloLoDeAsterisk = substr($sub,0,strpos($sub,$hasta));
                                $lnkAGMaps = "https://www.google.com.uy/maps/search/".$locationSoloLoDeAsterisk."/";
								
								?>
								
								
								<td><a href="<?php echo $lnkAGMaps; ?>" target="_blank"><button class="btn btn-sm btn-success">Ver Mapa</button></a></td>
							</tr>
							<?php
								}
							?>
						</tbody>
					</table>	
				</div>
            
        </div>
    </div>

    <div class="row"> </br> </br> </div>

    <div class="row">
        @if(Auth::user()->hasRole('administrator'))
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $userCount }}</h3>

                    <p>Total Usuarios</p>
                </div>
                <div class="icon">
                    <i class="fa fa-user-plus"></i>
                </div>
                <a href="{{ route('users') }}" class="small-box-footer">Mas info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        @endif
        
        @if(Auth::user()->hasRole('administrator'))
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-olive">
                <div class="inner">
                    <h3>{{ $qrCodeCount }}</h3>

                    <p>Usuarios con accesos activos</p>
                </div>
                <div class="icon">
                    <i class="fa fa-qrcode"></i>
                </div>
                <a href="{{ route('histories') }}" class="small-box-footer">Mas Info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        @endif


        @if(Auth::user()->hasRole('administrator'))
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>178</h3>

                    <p>Rutas Activas</p>
                </div>
                <div class="icon">
                    <i class="fa fa-bus"></i>
                </div>
                <a href="{{ route('attendances') }}" class="small-box-footer">Mas info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        @endif

        @if(Auth::user()->hasRole('administrator'))
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-primary">
                <div class="inner">
                    <h3>{{ $attendaceToday }}</h3>

                    <p>Total Asistencia hoy</p>
                </div>
                <div class="icon">
                    <i class="fa fa-database"></i>
                </div>
                <a href="{{ route('attendances') }}" class="small-box-footer">Mas info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        @endif

        @if(Auth::user()->hasRole('administrator') || Auth::user()->hasRole('admin'))
        <div class="col-lg-3 col-6">
            <!-- small box -->
            <div class="small-box bg-gray">
                <div class="inner">
                    <h3>{{ $qrCodeCount }}</h3>

                    <p>Total QR</p>
                </div>
                <div class="icon">
                    <i class="fa fa-qrcode"></i>
                </div>
                <a href="{{ route('histories') }}" class="small-box-footer">Mas info <i class="fas fa-arrow-circle-right"></i></a>
            </div>
        </div>
        @endif
    </div>
    
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/r/dt/jq-2.1.4,jszip-2.5.0,pdfmake-0.1.18,dt-1.10.9,af-2.0.0,b-1.0.3,b-colvis-1.0.3,b-html5-1.0.3,b-print-1.0.3,se-1.0.1/datatables.min.css"/>
	<script type="text/javascript" src="https://cdn.datatables.net/r/dt/jq-2.1.4,jszip-2.5.0,pdfmake-0.1.18,dt-1.10.9,af-2.0.0,b-1.0.3,b-colvis-1.0.3,b-html5-1.0.3,b-print-1.0.3,se-1.0.1/datatables.min.js"></script>
	
	<script>
	$(document).ready(function() {
		$('#grillaAsistencias').DataTable( {
			"pageLength": 20,
			"language": {
				"url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
			},
			dom: 'Bfrtip',
			buttons: [
				{
				extend: 'copy',
				text: 'Copiar',
				exportOptions: {
					columns: ':visible'
				}
				},
				{
					extend: 'csv',
					text: 'CSV',
					exportOptions: {
						columns: ':visible'
					}
				},
				{
					extend: 'excel',
					text: 'Excel',
					exportOptions: {
						columns: ':visible'
					}
				},
				// {
					// extend: 'pdf',
					// text: 'PDF',
					// exportOptions: {
						// columns: ':visible'
					// }
				// },
				{
					extend: 'print',
					text: 'Imprimir',
					exportOptions: {
						columns: ':visible'
					}
				},
				{
					extend: 'colvis',
					text: 'Visibilidad de Columnas',
					exportOptions: {
						columns: ':visible'
					}
				},
			]
		});
	});
	
	// JavaScript code Reloj
showTime();
function showTime(){
myDate = new Date();
hours = myDate.getHours();
minutes = myDate.getMinutes();
seconds = myDate.getSeconds();
if (hours < 10) hours = 0 + hours;

if (minutes < 10) minutes = "0" + minutes;

if (seconds < 10) seconds = "0" + seconds;

$("#HoraActual").text(hours+ ":" +minutes+ ":" +seconds);
setTimeout("showTime()", 1000);
}
	</script>
    
@stop

@section('css')
@stop

@section('js')
@stop
