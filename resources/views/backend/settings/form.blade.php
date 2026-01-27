@extends('adminlte::page')
<!-- page title -->
@section('title', 'Ajustes | ' . Config::get('adminlte.title'))

@section('content_header')
<h1>Ajustes</h1>
@stop

@section('content')
{{--Show message if any--}}
@include('layouts.flash-message')

<div class="card">
    <div class="card-header" style="display: flex; justify-content: space-around; width: 100%;">
        <h3 class="card-title" style="margin: 0;">Rutas BeeBus</h3>
        <?php if (!$key): ?>
            <button class="btn btn-success" style="margin: 0;" data-toggle="modal" data-target="#exampleModal">Agregar nueva Ruta</button>
        <?php endif; ?>
    </div>

    @if($key)
    {{ Form::open(array('url' => route($data->form_action), 'method' => 'POST','autocomplete' => 'off', 'files' => true)) }}
    {{ Form::hidden('id', $data->id, array('id' => 'user_id')) }}
    @endif

    <?php
    $hostVar = env('DB_HOST');
    $userVar = env('DB_USERNAME');
    $passVar = env('DB_PASSWORD');
    $dbNameVar = env('DB_DATABASE');
    ?>

    <?php $key = Request::query('key', false);    ?>

    <?php
    $host = $hostVar;
    $user = $userVar;
    $password = $passVar;
    $dbname = $dbNameVar;
    $con = mysqli_connect($host, $user, $password, $dbname);
    if (!$con) {
        die("Error de conexión... " . mysqli_connect_error());
    }
    ?>

    <?php
    $dataSettings = mysqli_query($con, "
			SELECT settings.* FROM settings
			WHERE settings.key_app = '$key'");
    $numRows = mysqli_num_rows($dataSettings);
    if ($numRows == 1) {
        while ($rowDataSettings = mysqli_fetch_array($dataSettings)) {
    ?>

            <div class="card-body">
                <div class="row">
                    <div class="col-md-7">
                        <div class="form-group row">
                            <div class="col-sm-2 col-form-label">
                                <strong class="field-title">Hora Inicio</strong>
                            </div>
                            <div class="col-sm-10 col-content">
                                {{ Form::text('start_time', $rowDataSettings['start_time'], array('class' => 'form-control', 'required', 'id' => 'start_time')) }}
                                <p class="form-text text-muted">Complete con la hora de inicio del servicio</p>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-sm-2 col-form-label">
                                <strong class="field-title">Hora salida</strong>
                            </div>
                            <div class="col-sm-10 col-content">
                                {{ Form::text('out_time', $rowDataSettings['out_time'], array('class' => 'form-control', 'required', 'id' => 'out_time')) }}
                                <p class="form-text text-muted">Complete con la hora de finalizacion del servicio</p>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-sm-2 col-form-label">
                                <strong class="field-title">Url</strong>
                            </div>
                            <div class="col-sm-10 col-content">
                                {{ Form::text('url', url('/'), array('class' => 'form-control', 'disabled', 'id' => 'url')) }}
                                <p class="form-text text-muted">Tu URL actual. No puede cambiar esta URL</p>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-sm-2 col-form-label">
                                <strong class="field-title">Key App</strong>
                            </div>
                            <div class="col-sm-10 col-content">
                                {{ Form::text('key_app', $rowDataSettings['key_app'], array('class' => 'form-control', 'required', 'id' => 'key', 'readonly')) }}
                                <p class="form-text text-muted">La clave de la aplicación se utiliza para la comunicación con la aplicación. Puede cambiar la clave haciendo clic en el botón "Generar nueva clave" no olvide guardarla</p>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-sm-2 col-form-label">
                                <strong class="field-title">Timezone</strong>
                            </div>
                            <div class="col-sm-10 col-content">
                                {{ Form::select('timezone', $timezone, $rowDataSettings['timezone'], array('id' => 'timezone', 'class' => 'form-control select2')) }}
                                <p class="form-text text-muted">Zona horaria de nuestra region</p>
                            </div>
                        </div>

                        <div class="form-group row">
                            <div class="col-sm-2 col-form-label">
                                <strong class="field-title">Estado</strong>
                            </div>
                            <div class="col-sm-10 col-content">
                                <select class="form-control" id="status" name="status">
                                    <option value="activo" {{ (isset($rowDataSettings['status']) && $rowDataSettings['status'] == 'activo') ? 'selected' : '' }}>Activo</option>
                                    <option value="inactivo" {{ (isset($rowDataSettings['status']) && $rowDataSettings['status'] == 'inactivo') ? 'selected' : '' }}>Inactivo</option>
                                </select>
                                <p class="form-text text-muted">Estado actual de la ruta</p>
                            </div>
                        </div>

                    </div>
                    <div class="col-md-5">
                        <?php $qrParaGenerar = "{'url':'" . url('/') . "', 'key':'" . $rowDataSettings['key_app'] . "'}"; ?>
                        <div id="qrCodeContainer" style="text-align: center;"></div>
                        <p class="text-center"><b>Codigo QR</b></p>
                        <p class="text-center form-text text-muted">Este código QR se usa por primera vez al abrir la aplicación. <br>Escanea este QR y esto se hace solo una vez.</p>
                        <p class="text-center">
                        <div id="qrCodeContainer" style="text-align: center;">
                            <button onclick="descargar()" id="downloadBtn" type="button" class="btn btn-danger">Descargar Ruta</button>
                        </div>
                        </p>
                    </div>
                </div>
            </div>

            <div class="card-footer">
                <div id="form-button">
                    <div class="col-sm-12 text-center top20">
                        <button type="submit" name="submit" id="btn-admin-member-submit" class="btn btn-primary">
                            Actualizar Ruta
                        </button>
                        <!--<button type="button" id="generate-key" class="btn btn-warning">
                        Generar nueva Key
                    </button>-->
                        <a href="{{ route('settings') }}" class="btn btn-secondary">
                            Volver a Lista
                        </a>
                    </div>
                </div>
            </div>
            @if($key)
            {{ Form::close() }}
            @endif

        <?php
        }
    } else {
        ?>
        <div class="card-body">
            <div class="row text-center">
                <h5> Selecciona la <b>Ruta</b> para Obtener la Información: </h5>
                <div class="row">
                    <?php
                    $todasLasSettings = mysqli_query($con, " SELECT * FROM settings");
                    $numRowsSe = mysqli_num_rows($todasLasSettings);
                    if ($numRowsSe > 0) {
                        while ($rowTodasLasSettings = mysqli_fetch_array($todasLasSettings)) {
                    ?>
                            <div class="col-md-4">
                                <a class="btn btn-info btn-lg btn-block mb-3" href="?key=<?php echo $rowTodasLasSettings['key_app'] ?>" role="button"><?php echo $rowTodasLasSettings['key_app'] ?></a>
                            </div>
                    <?php
                        }
                    }
                    ?>
                </div>
            </div>
        </div>
    <?php
    }
    ?>

    <!--
        <div class="card-body">
            <div class="row">
                <div class="col-md-7">
                    <div class="form-group row">
                        <div class="col-sm-2 col-form-label">
                            <strong class="field-title">Hora Inicio</strong>
                        </div>
                        <div class="col-sm-10 col-content">
                            {{ Form::text('start_time', $data->start_time, array('class' => 'form-control', 'required', 'id' => 'start_time')) }}
                            <p class="form-text text-muted">Complete con la hora de inicio del servicio</p>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-sm-2 col-form-label">
                            <strong class="field-title">Hora salida</strong>
                        </div>
                        <div class="col-sm-10 col-content">
                            {{ Form::text('out_time', $data->out_time, array('class' => 'form-control', 'required', 'id' => 'out_time')) }}
                            <p class="form-text text-muted">Complete con la hora de finalizacion del servicio</p>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-sm-2 col-form-label">
                            <strong class="field-title">Url</strong>
                        </div>
                        <div class="col-sm-10 col-content">
                            {{ Form::text('url', url('/'), array('class' => 'form-control', 'disabled', 'id' => 'url')) }}
                            <p class="form-text text-muted">Tu URL actual. No puede cambiar esta URL</p>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-sm-2 col-form-label">
                            <strong class="field-title">Key App</strong>
                        </div>
                        <div class="col-sm-10 col-content">
                            {{ Form::text('key_app', $data->key_app, array('class' => 'form-control', 'required', 'id' => 'key', 'readonly')) }}
                            <p class="form-text text-muted">La clave de la aplicación se utiliza para la comunicación con la aplicación. Puede cambiar la clave haciendo clic en el botón "Generar nueva clave" no olvide guardarla</p>
                        </div>
                    </div>

                    <div class="form-group row">
                        <div class="col-sm-2 col-form-label">
                            <strong class="field-title">Timezone</strong>
                        </div>
                        <div class="col-sm-10 col-content">
                            {{ Form::select('timezone', $timezone, $data->timezone, array('id' => 'timezone', 'class' => 'form-control select2')) }}
                            <p class="form-text text-muted">Zona horaria de nuestra region</p>
                        </div>
                    </div>

                </div>
                <div class="col-md-5">
                    <img class="img-responsive img-thumbnail" src="https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl={{ $data->qr }}&choe=UTF-8" style="margin: 0 auto;display: block;">
                    <p class="text-center"><b>Codigo QR</b></p>
                    <p class="text-center form-text text-muted">Este código QR se usa por primera vez al abrir la aplicación. <br>Escanea este QR y esto se hace solo una vez.</p>
                    <p class="text-center"><a href="https://chart.googleapis.com/chart?chs=400x400&cht=qr&chl={{ $data->qr }}&choe=UTF-8" target="_blank"><button type="button" class="btn btn-success">Descargar</button></a></p>
                </div>
            </div>
        </div>

        <div class="card-footer">
            <div id="form-button">
                <div class="col-sm-12 text-center top20">
                    <button type="submit" name="submit" id="btn-admin-member-submit"
                            class="btn btn-primary">{{ $data->button_text }}</button>

                    <button type="button" id="generate-key" class="btn btn-primary">Generar nueva Key</button>
                </div>
            </div>
        </div>
         -->
</div>

<!-- /.card -->
</div>
<!-- /.row -->
<!-- /.content -->
<div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Datos de la Ruta</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <form action="{{ route('settings.store') }}" method="POST">
                            @csrf
                            <div class="form-group">
                                <label for="start_time">Hora inicial</label>
                                <input type="time" class="form-control" id="start_time" name="start_time">
                            </div>
                            <div class="form-group">
                                <label for="out_time">Hora de cierre</label>
                                <input type="time" class="form-control" id="out_time" name="out_time">
                            </div>
                            <div class="form-group">
                                <label for="key_app">Nombre de Ruta</label>
                                <input type="text" class="form-control" id="key_app" name="key_app">
                            </div>
                            <div class="form-group">
                                <label for="timezone">Pais</label>
                                <input type="text" class="form-control" id="timezone" name="timezone" value="America/Costa_Rica">
                            </div>
                            <div class="form-group">
                                <label for="status">Estado</label>
                                <select class="form-control" id="status" name="status">
                                    <option value="activo" selected>Activo</option>
                                    <option value="inactivo">Inactivo</option>
                                </select>
                            </div>
                            <button type="submit" class="btn btn-primary">Crear Ruta</button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
@stop

@section('js')
<script src="{{ asset('js/backend/settings/form.js'). '?v=' . rand(99999,999999) }}"></script>
<script>
    var qrParaGenerar = "<?php echo isset($qrParaGenerar) ? $qrParaGenerar : '' ?>"
    qrParaGenerar = qrParaGenerar.substring(0, qrParaGenerar.length - 0);
    console.log(qrParaGenerar)
    if (qrParaGenerar) {
        var qr = qrcode(0, 'L');
        qr.addData(qrParaGenerar);
        qr.make();
        var qrImage = document.createElement("img");
        qrImage.src = qr.createDataURL(4);
        var qrContainer = document.getElementById("qrCodeContainer");
        qrContainer.appendChild(qrImage);

        function descargar() {
            var qr = qrcode(0, 'L');
            qr.addData(qrParaGenerar);
            qr.make();
            var qrUrl = qr.createDataURL(5);
            var downloadLink = document.createElement("a");
            downloadLink.href = qrUrl;
            downloadLink.download = "qr_code.png";
            downloadLink.click();
        }
    }
</script>
@stop