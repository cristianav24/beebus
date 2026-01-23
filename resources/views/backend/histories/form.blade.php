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
                <strong class="field-title">Nombre</strong>
            </div>
            <div class="col-sm-10 col-content">
                {{ Form::text('name', $data->name, array('class' => 'form-control', 'required')) }}
                <small class="form-text text-muted">
                    <i class="fa fa-question-circle" aria-hidden="true"></i> Nombre del Estudiante o Usuario.
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
                {{ Form::text('cedula', $data->cedula, array('class' => 'form-control', 'required')) }}
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
                {{ Form::email('email', $data->email, array('class' => 'form-control', 'required')) }}
                <small class="form-text text-muted">
                    <i class="fa fa-question-circle" aria-hidden="true"></i>
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
                        data-charge="{{ $ruta->charge_per_day }}"
                        data-ruta="{{ $ruta->key_app }}"
                        {{ (isset($data->ruta_id) && $data->ruta_id == $ruta->id) ? 'selected' : '' }}>
                        {{ $ruta->key_app }} - ₡{{ $ruta->charge_per_day }} - {{ $ruta->start_time ?? '' }} a {{ $ruta->out_time ?? '' }}
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
                <strong class="field-title">¿Cuanto Restar por Asistencia?</strong>
            </div>
            <div class="col-sm-10 col-content">
                {{ Form::text('cuantoRestar', $data->cuantoRestar, array('class' => 'form-control', 'required', 'id' => 'cuantoRestar')) }}
                <small class="form-text text-muted">
                    <i class="fa fa-info-circle text-warning" aria-hidden="true"></i> <strong>Se cobra por asistencia:</strong> Este valor se aplica solo cuando el estudiante <strong>NO tiene beca</strong>. Se establece automáticamente según la ruta seleccionada.
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
        const cuantoRestarField = document.querySelector('input[name="cuantoRestar"]');

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

            // Mostrar mensaje informativo sobre el cobro por asistencia
            updateCuantoRestarMessage(true);
        } else {
            tipoBecaHidden.value = '';
            descripcionDiv.innerHTML = '';
            creditosDiv.innerHTML = '';
            
            // Mostrar mensaje informativo sobre el cobro por asistencia
            updateCuantoRestarMessage(false);
        }
    }

    function updateCuantoRestarMessage(hasBeca) {
        const cuantoRestarHelp = document.querySelector('input[name="cuantoRestar"]').parentNode.querySelector('.form-text');
        
        if (hasBeca) {
            // Si tiene beca, mostrar que NO se cobrará por asistencia
            cuantoRestarHelp.innerHTML = '<i class="fa fa-info-circle text-success" aria-hidden="true"></i> <strong>Con beca:</strong> Este estudiante <strong>NO pagará</strong> por asistencia. El valor se mantiene para referencia.';
        } else {
            // Si no tiene beca, mostrar el mensaje original
            cuantoRestarHelp.innerHTML = '<i class="fa fa-info-circle text-warning" aria-hidden="true"></i> <strong>Se cobra por asistencia:</strong> Este valor se aplica solo cuando el estudiante <strong>NO tiene beca</strong>. Se establece automáticamente según la ruta seleccionada.';
        }
    }

    function updateRutaInfo() {
        const select = document.getElementById('ruta_id');
        const cuantoRestarField = document.querySelector('input[name="cuantoRestar"]');
        
        // Obtener el valor seleccionado usando Select2 API
        const selectedValue = $('#ruta_id').val();
        
        if (selectedValue) {
            // Buscar la opción seleccionada para obtener el data-charge
            const selectedOption = select.querySelector('option[value="' + selectedValue + '"]');
            
            if (selectedOption) {
                const charge = selectedOption.dataset.charge;
                
                // Establecer el valor del campo cuantoRestar y hacerlo readonly
                cuantoRestarField.value = charge || 0;
                cuantoRestarField.readOnly = true;
                cuantoRestarField.style.backgroundColor = '#e9ecef';
                cuantoRestarField.style.cursor = 'not-allowed';
                cuantoRestarField.title = 'Este valor se establece automáticamente según la ruta seleccionada';
            }
        } else {
            // Si no hay ruta seleccionada, habilitar el campo para edición
            cuantoRestarField.readOnly = false;
            cuantoRestarField.style.backgroundColor = '';
            cuantoRestarField.style.cursor = '';
            cuantoRestarField.title = '';
        }
    }

    // Ejecutar al cargar la página si hay un colegio o beca seleccionado
    document.addEventListener('DOMContentLoaded', function() {
        updateColegioInfo();
        updateBecaInfo();
        
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
        
        // Agregar event listener para cambios en la ruta (Select2)
        $('#ruta_id').on('change', function() {
            updateRutaInfo();
        });
        
        // Ejecutar updateRutaInfo al cargar si hay una ruta preseleccionada
        updateRutaInfo();
    });
</script>

<script src="{{ asset('js/backend/histories/form.js'). '?v=' . rand(99999,999999) }}"></script>
@stop