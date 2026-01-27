@extends('adminlte::page')
<!-- page title -->
@section('title', 'Create and Update Histories Qr ' . Config::get('adminlte.title'))

@section('content_header')
<h1>Accesos QR</h1>
@stop

@section('content')
{{--Show message if any--}}
@include('layouts.flash-message')

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Añadir o Actualizar</h3>
    </div>

    {{ Form::open(array('url' => route($data->form_action), 'method' => 'POST','autocomplete' => 'off', 'files' => true)) }}
    {{ Form::hidden('id', $data->id, array('id' => 'id')) }}

    <div class="card-body">

        <div class="form-group row">
            <div class="col-sm-2 col-form-label">
                <strong class="field-title">Nombre(s)</strong>
            </div>
            <div class="col-sm-10 col-content">
                {{ Form::text('first_name', $data->first_name, array('class' => 'form-control', 'required', 'placeholder' => 'Ej: Juan Pablo')) }}
                <small class="form-text text-muted">
                    <i class="fa fa-question-circle" aria-hidden="true"></i> Nombre o nombres del estudiante.
                </small>
            </div>
        </div>

        <br>

        <div class="form-group row">
            <div class="col-sm-2 col-form-label">
                <strong class="field-title">Primer Apellido</strong>
            </div>
            <div class="col-sm-10 col-content">
                {{ Form::text('last_name', $data->last_name, array('class' => 'form-control', 'required', 'placeholder' => 'Ej: Mora')) }}
                <small class="form-text text-muted">
                    <i class="fa fa-question-circle" aria-hidden="true"></i> Primer apellido.
                </small>
            </div>
        </div>

        <br>

        <div class="form-group row">
            <div class="col-sm-2 col-form-label">
                <strong class="field-title">Segundo Apellido</strong>
            </div>
            <div class="col-sm-10 col-content">
                {{ Form::text('second_last_name', $data->second_last_name, array('class' => 'form-control', 'placeholder' => 'Ej: Pérez (opcional)')) }}
                <small class="form-text text-muted">
                    <i class="fa fa-question-circle" aria-hidden="true"></i> Segundo apellido (opcional).
                </small>
            </div>
        </div>

        <br>

        <div class="form-group row">
            <div class="col-sm-2 col-form-label">
                <strong class="field-title">Colegio</strong>
            </div>
            <div class="col-sm-10 col-content">
                <select class="form-control" id="colegio_id" name="colegio_id" onchange="updateColegioInfo()">
                    <option value="">Seleccione un colegio</option>
                    @foreach($colegios as $colegio)
                    <option value="{{ $colegio->id }}"
                        data-nombre="{{ $colegio->nombre }}"
                        data-direccion="{{ $colegio->direccion }}"
                        {{ (isset($data->colegio_id) && $data->colegio_id == $colegio->id) ? 'selected' : '' }}>
                        {{ $colegio->nombre }}
                    </option>
                    @endforeach
                </select>
                {{ Form::hidden('colegio', $data->colegio, array('id' => 'colegio_hidden')) }}
                <small class="form-text text-muted" id="colegio-info">
                    <i class="fa fa-question-circle" aria-hidden="true"></i> Seleccione el centro educativo
                    <div id="colegio-direccion" style="font-size: 12px; color: #6c757d; margin-top: 5px;"></div>
                </small>
            </div>
        </div>

        <br>

        <div class="form-group row">
            <div class="col-sm-2 col-form-label">
                <strong class="field-title">Seccion</strong>
            </div>
            <div class="col-sm-10 col-content">
                {{ Form::text('seccion', $data->seccion, array('class' => 'form-control', '')) }}
                <small class="form-text text-muted">
                    <i class="fa fa-question-circle" aria-hidden="true"></i> Seccion del Estudiante
                </small>
            </div>
        </div>

        <br>

        <div class="form-group row">
            <div class="col-sm-2 col-form-label">
                <strong class="field-title">Cédula</strong>
            </div>
            <div class="col-sm-10 col-content">
                {{ Form::text('cedula', $data->cedula, array('class' => 'form-control', 'required', 'id' => 'cedula')) }}
                <small class="form-text text-muted">
                    <i class="fa fa-question-circle" aria-hidden="true"></i> Sin puntos ni guiones
                </small>
            </div>
        </div>

        <br>

        <div class="form-group row">
            <div class="col-sm-2 col-form-label">
                <strong class="field-title">Email</strong>
            </div>
            <div class="col-sm-10 col-content">
                {{ Form::email('email', $data->email, array('class' => 'form-control', 'required', 'id' => 'email')) }}
                <small class="form-text text-muted">
                    <i class="fa fa-question-circle" aria-hidden="true"></i> Se genera automáticamente con la cédula, puede cambiarlo si desea
                </small>
            </div>
        </div>

        <br>

        <div class="form-group row">
            <div class="col-sm-2 col-form-label">
                <strong class="field-title">Créditos Actuales</strong>
            </div>
            <div class="col-sm-10 col-content">
                {{ Form::text('creditos', $data->creditos, array('class' => 'form-control', 'required')) }}
                <small class="form-text text-muted">
                    <i class="fa fa-question-circle" aria-hidden="true"></i> Créditos actuales
                </small>
            </div>
        </div>


        <br>

        <div class="form-group row">
            <div class="col-sm-2 col-form-label">
                <strong class="field-title">Tipo de Beca</strong>
            </div>
            <div class="col-sm-10 col-content">
                <select class="form-control" id="beca_id" name="beca_id" onchange="updateBecaInfo()">
                    <option value="">Seleccione una beca</option>
                    @foreach($becas as $beca)
                    <option value="{{ $beca->id }}"
                        data-nombre="{{ $beca->nombre_beca }}"
                        data-descripcion="{{ $beca->descripcion }}"
                        data-creditos="{{ $beca->monto_creditos }}"
                        {{ (isset($data->beca_id) && $data->beca_id == $beca->id) ? 'selected' : '' }}>
                        {{ $beca->nombre_beca }}
                    </option>
                    @endforeach
                </select>
                {{ Form::hidden('tipoBeca', $data->tipoBeca, array('id' => 'tipoBeca_hidden')) }}
                <small class="form-text text-muted" id="beca-info">
                    <i class="fa fa-info-circle text-info" aria-hidden="true"></i> <strong>Importante:</strong> Solo seleccionar si el estudiante tiene beca. Si paga completo, dejar vacío.
                    <div id="beca-descripcion" style="font-size: 12px; color: #6c757d; margin-top: 5px;"></div>
                    <div id="beca-creditos" style="font-size: 12px; color: #28a745; margin-top: 3px; font-weight: bold;"></div>
                </small>
            </div>
        </div>

        <br>

        <div class="form-group row">
            <div class="col-sm-2 col-form-label">
                <strong class="field-title">Ruta</strong>
            </div>
            <div class="col-sm-10 col-content">
                <select class="form-control select2" id="ruta_id" name="ruta_id" data-placeholder="Buscar ruta...">
                    <option value="">Seleccione una ruta</option>
                    @foreach($rutas as $ruta)
                    <option value="{{ $ruta->id }}"
                        data-ruta="{{ $ruta->key_app }}"
                        {{ (isset($data->ruta_id) && $data->ruta_id == $ruta->id) ? 'selected' : '' }}>
                        {{ $ruta->key_app }} - {{ $ruta->start_time ?? '' }} a {{ $ruta->out_time ?? '' }}
                    </option>
                    @endforeach
                </select>
                <small class="form-text text-muted">
                    <i class="fa fa-question-circle" aria-hidden="true"></i> Seleccione la ruta asignada al estudiante
                </small>
            </div>
        </div>

        <br>

        <div class="form-group row">
            <div class="col-sm-2 col-form-label">
                <strong class="field-title">Tarifa</strong>
            </div>
            <div class="col-sm-10 col-content">
                <select class="form-control select2" id="tarifa_id" name="tarifa_id" required data-placeholder="Seleccione una tarifa...">
                    <option value="">Seleccione una tarifa</option>
                    @foreach($tarifas as $tarifa)
                    <option value="{{ $tarifa->id }}"
                        data-monto="{{ $tarifa->monto }}"
                        {{ (isset($data->tarifa_id) && $data->tarifa_id == $tarifa->id) ? 'selected' : '' }}>
                        {{ $tarifa->nombre }} - ₡{{ number_format($tarifa->monto, 0, ',', '.') }}
                    </option>
                    @endforeach
                </select>
                <small class="form-text text-muted">
                    <i class="fa fa-info-circle text-info" aria-hidden="true"></i> <strong>Tarifa por asistencia:</strong> Este monto se cobrará cada vez que el estudiante marque asistencia.
                </small>
            </div>
        </div>

        <br>

        <div class="form-group row">
            <div class="col-sm-2 col-form-label">
                <strong class="field-title">¿Chances para marcar sin creditos?</strong>
            </div>
            <div class="col-sm-10 col-content">
                {{ Form::text('chancesParaMarcar', $data->chancesParaMarcar, array('class' => 'form-control', 'required')) }}
                <small class="form-text text-muted">
                </small>
            </div>
        </div>

        @if($data->page_type == 'edit')
        <br>
        <div class="form-group row">
            <div class="col-sm-2 col-form-label">
                <strong class="field-title">Contrato</strong>
            </div>
            <div class="col-sm-10 col-content">
                @if($data->contrato_subido && $data->contrato_url)
                    <div class="alert alert-success">
                        <i class="fa fa-check-circle"></i> <strong>Contrato subido</strong>
                        <br>
                        <small>
                            Fecha de subida: {{ $data->contrato_fecha_subida ? $data->contrato_fecha_subida->format('d/m/Y H:i') : 'No registrada' }}
                        </small>
                        <br><br>
                        <a href="{{ route('histories.download-contract', $data->id) }}" class="btn btn-sm btn-primary" target="_blank">
                            <i class="fa fa-download"></i> Descargar Contrato
                        </a>
                        <a href="{{ asset($data->contrato_url) }}" class="btn btn-sm btn-info" target="_blank">
                            <i class="fa fa-eye"></i> Ver Contrato
                        </a>
                    </div>
                @else
                    <div class="alert alert-warning">
                        <i class="fa fa-exclamation-triangle"></i> <strong>Sin contrato</strong>
                        <br>
                        <small>El estudiante o su padre/madre aún no ha subido el contrato firmado.</small>
                    </div>
                @endif
            </div>
        </div>
        @endif

    </div>

    <div class="card-footer">
        <div id="form-button">
            <div class="col-sm-12 text-center top20">
                <button type="submit" name="submit" id="btn-admin-member-submit"
                    class="btn btn-primary">{{ $data->button_text }}</button>
            </div>
        </div>
    </div>
    {{ Form::close() }}
</div>

<!-- /.card -->
</div>
<!-- /.row -->
<!-- /.content -->
@stop

@section('css')
<!-- Select2 CSS -->
<link rel="stylesheet" href="{{ asset('vendor/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('vendor/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@stop

@section('js')
<!-- Select2 JS -->
<script src="{{ asset('vendor/select2/js/select2.full.min.js') }}"></script>

<script>
    var typePage = "{{ $data->page_type }}";
</script>

<script>
    function updateColegioInfo() {
        const select = document.getElementById('colegio_id');
        const selectedOption = select.options[select.selectedIndex];
        const direccionDiv = document.getElementById('colegio-direccion');
        const colegioHidden = document.getElementById('colegio_hidden');

        if (selectedOption.value) {
            const nombre = selectedOption.dataset.nombre;
            const direccion = selectedOption.dataset.direccion;

            // Actualizar el campo hidden con el nombre del colegio
            colegioHidden.value = nombre;

            // Mostrar la dirección debajo
            if (direccion) {
                direccionDiv.innerHTML = '<strong>Dirección:</strong> ' + direccion;
            } else {
                direccionDiv.innerHTML = '<strong>Dirección:</strong> No registrada';
            }
        } else {
            colegioHidden.value = '';
            direccionDiv.innerHTML = '';
        }
    }

    function updateBecaInfo() {
        const select = document.getElementById('beca_id');
        const selectedOption = select.options[select.selectedIndex];
        const descripcionDiv = document.getElementById('beca-descripcion');
        const creditosDiv = document.getElementById('beca-creditos');
        const tipoBecaHidden = document.getElementById('tipoBeca_hidden');

        if (selectedOption.value) {
            const nombre = selectedOption.dataset.nombre;
            const descripcion = selectedOption.dataset.descripcion;
            const creditos = selectedOption.dataset.creditos;

            // Actualizar el campo hidden con el nombre de la beca
            tipoBecaHidden.value = nombre;

            // Mostrar la descripción debajo
            if (descripcion) {
                descripcionDiv.innerHTML = '<strong>Descripción:</strong> ' + descripcion;
            } else {
                descripcionDiv.innerHTML = '<strong>Descripción:</strong> No registrada';
            }

            // Mostrar los créditos
            if (creditos) {
                creditosDiv.innerHTML = '<strong>Monto Créditos:</strong> ' + creditos;
            } else {
                creditosDiv.innerHTML = '<strong>Monto Créditos:</strong> 0';
            }
        } else {
            tipoBecaHidden.value = '';
            descripcionDiv.innerHTML = '';
            creditosDiv.innerHTML = '';
        }
    }

    // Ejecutar al cargar la página si hay un colegio o beca seleccionado
    document.addEventListener('DOMContentLoaded', function() {
        updateColegioInfo();
        updateBecaInfo();

        // Auto-generar email basado en cédula
        var cedulaInput = document.getElementById('cedula');
        var emailInput = document.getElementById('email');
        var emailModifiedManually = false;
        var isNewRecord = {{ $data->id ? 'false' : 'true' }};

        // Detectar si el usuario modifica manualmente el email
        emailInput.addEventListener('input', function() {
            var cedula = cedulaInput.value.trim();
            var expectedEmail = cedula + '@beebus.com';
            // Si el email es diferente al generado automático, marcar como modificado
            if (emailInput.value !== expectedEmail) {
                emailModifiedManually = true;
            }
        });

        // Al cambiar la cédula, actualizar el email si no fue modificado manualmente
        cedulaInput.addEventListener('input', function() {
            var cedula = this.value.trim();
            if (cedula && !emailModifiedManually) {
                emailInput.value = cedula + '@beebus.com';
            }
        });

        // Si es nuevo registro y no hay email, generar uno basado en cédula
        if (isNewRecord && !emailInput.value && cedulaInput.value) {
            emailInput.value = cedulaInput.value.trim() + '@beebus.com';
        }

        // Inicializar Select2 para el dropdown de rutas
        $('#ruta_id').select2({
            placeholder: 'Buscar ruta...',
            allowClear: true,
            width: '100%',
            language: {
                noResults: function() {
                    return "No se encontraron rutas";
                },
                searching: function() {
                    return "Buscando...";
                }
            }
        });

        // Inicializar Select2 para el dropdown de tarifas
        $('#tarifa_id').select2({
            placeholder: 'Seleccione una tarifa...',
            allowClear: false,
            width: '100%',
            language: {
                noResults: function() {
                    return "No se encontraron tarifas";
                },
                searching: function() {
                    return "Buscando...";
                }
            }
        });
    });
</script>

<script src="{{ asset('js/backend/histories/form.js'). '?v=' . rand(99999,999999) }}"></script>
@stop