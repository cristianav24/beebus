@extends('adminlte::page')
<!-- page title -->
@section('title', 'Create and Update Histories Qr ' . Config::get('adminlte.title'))

@section('content_header')
<h1>Alumnos</h1>
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

        {{-- ========== ZONA (primer nivel cascada) ========== --}}
        <div class="form-group row">
            <div class="col-sm-2 col-form-label">
                <strong class="field-title">Zona</strong>
            </div>
            <div class="col-sm-10 col-content">
                <select class="form-control" id="zona_id" name="zona_id">
                    <option value="">Seleccione una zona</option>
                    @foreach($zonas as $zona)
                    <option value="{{ $zona->id }}"
                        {{ ($currentZonaId && $currentZonaId == $zona->id) ? 'selected' : '' }}>
                        {{ $zona->nombre }}
                    </option>
                    @endforeach
                </select>
                <small class="form-text text-muted">
                    <i class="fa fa-question-circle" aria-hidden="true"></i> Seleccione la zona geográfica
                </small>
            </div>
        </div>

        <br>

        {{-- ========== COLEGIO (depende de zona) ========== --}}
        <div class="form-group row">
            <div class="col-sm-2 col-form-label">
                <strong class="field-title">Colegio</strong>
            </div>
            <div class="col-sm-10 col-content">
                <select class="form-control" id="colegio_id" name="colegio_id">
                    <option value="">-- Primero seleccione una zona --</option>
                </select>
                {{ Form::hidden('colegio', $data->getAttributeValue('colegio'), array('id' => 'colegio_hidden')) }}
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

        {{-- ========== RUTA (depende de colegio) ========== --}}
        <div class="form-group row">
            <div class="col-sm-2 col-form-label">
                <strong class="field-title">Ruta</strong>
            </div>
            <div class="col-sm-10 col-content">
                <select class="form-control" id="ruta_id" name="ruta_id">
                    <option value="">-- Primero seleccione un colegio --</option>
                </select>
                <small class="form-text text-muted">
                    <i class="fa fa-question-circle" aria-hidden="true"></i> Seleccione la ruta asignada al estudiante
                </small>
            </div>
        </div>

        <br>

        {{-- ========== PARADERO (depende de ruta) ========== --}}
        <div class="form-group row">
            <div class="col-sm-2 col-form-label">
                <strong class="field-title">Paradero</strong>
            </div>
            <div class="col-sm-10 col-content">
                <select class="form-control" id="paradero_id" name="paradero_id">
                    <option value="">-- Primero seleccione una ruta --</option>
                </select>
                <small class="form-text text-muted">
                    <i class="fa fa-info-circle text-info" aria-hidden="true"></i> <strong>Tarifa automatica:</strong> El monto se asigna segun el paradero seleccionado.
                </small>
                <div id="paradero-monto-info" style="font-size: 14px; color: #28a745; margin-top: 5px; font-weight: bold;"></div>
            </div>
        </div>

        <br>

        {{-- ========== TARIFA LEGACY (oculta, se mantiene por compatibilidad) ========== --}}
        <div class="form-group row">
            <div class="col-sm-2 col-form-label">
                <strong class="field-title">Tarifa (legacy)</strong>
            </div>
            <div class="col-sm-10 col-content">
                <select class="form-control" id="tarifa_id" name="tarifa_id" data-placeholder="Seleccione una tarifa...">
                    <option value="">Sin tarifa (usa paradero)</option>
                    @foreach($tarifas as $tarifa)
                    <option value="{{ $tarifa->id }}"
                        data-monto="{{ $tarifa->monto }}"
                        {{ (isset($data->tarifa_id) && $data->tarifa_id == $tarifa->id) ? 'selected' : '' }}>
                        {{ $tarifa->nombre }} - ₡{{ number_format($tarifa->monto, 0, ',', '.') }}
                    </option>
                    @endforeach
                </select>
                <small class="form-text text-muted">
                    <i class="fa fa-info-circle text-warning" aria-hidden="true"></i> Solo usar si el estudiante NO tiene paradero asignado (legacy).
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
                <strong class="field-title">Estado</strong>
            </div>
            <div class="col-sm-10 col-content">
                <select class="form-control" id="status" name="status">
                    <option value="1" {{ $data->status == 1 ? 'selected' : '' }}>Activo</option>
                    <option value="0" {{ $data->status == 0 ? 'selected' : '' }}>Inactivo</option>
                    <option value="2" {{ $data->status == 2 ? 'selected' : '' }}>Pendiente</option>
                </select>
                <small class="form-text text-muted">
                    <i class="fa fa-info-circle text-info" aria-hidden="true"></i>
                    <strong>Pendiente:</strong> estudiante nuevo sin datos completos.
                    <strong>Activo:</strong> estudiante con datos completos y operativo.
                    <strong>Inactivo:</strong> estudiante deshabilitado.
                </small>
            </div>
        </div>

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

    // URLs para AJAX cascade
    var urlColegiosByZona = "{{ url('/histories/zonas') }}";
    var urlRutasByColegio = "{{ url('/histories/colegios') }}";
    var urlParaderosByRuta = "{{ url('/histories/rutas') }}";

    // Valores actuales (para edit mode)
    var currentColegioId = "{{ $data->colegio_id ?? '' }}";
    var currentRutaId = "{{ $data->ruta_id ?? '' }}";
    var currentParaderoId = "{{ $data->paradero_id ?? '' }}";
</script>

<script>
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

            tipoBecaHidden.value = nombre;

            if (descripcion) {
                descripcionDiv.innerHTML = '<strong>Descripción:</strong> ' + descripcion;
            } else {
                descripcionDiv.innerHTML = '<strong>Descripción:</strong> No registrada';
            }

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

    // ========== CASCADA AJAX ==========

    function loadColegiosByZona(zonaId, selectedColegioId) {
        var colegioSelect = document.getElementById('colegio_id');
        var rutaSelect = document.getElementById('ruta_id');
        var paraderoSelect = document.getElementById('paradero_id');

        // Limpiar dependientes
        colegioSelect.innerHTML = '<option value="">Cargando...</option>';
        rutaSelect.innerHTML = '<option value="">-- Primero seleccione un colegio --</option>';
        paraderoSelect.innerHTML = '<option value="">-- Primero seleccione una ruta --</option>';
        document.getElementById('paradero-monto-info').innerHTML = '';
        document.getElementById('colegio-direccion').innerHTML = '';

        if (!zonaId) {
            colegioSelect.innerHTML = '<option value="">-- Primero seleccione una zona --</option>';
            return;
        }

        fetch(urlColegiosByZona + '/' + zonaId + '/colegios')
            .then(function(response) { return response.json(); })
            .then(function(data) {
                colegioSelect.innerHTML = '<option value="">Seleccione un colegio</option>';
                data.forEach(function(colegio) {
                    var option = document.createElement('option');
                    option.value = colegio.id;
                    option.textContent = colegio.nombre;
                    option.dataset.nombre = colegio.nombre;
                    option.dataset.direccion = colegio.direccion || '';
                    if (selectedColegioId && colegio.id == selectedColegioId) {
                        option.selected = true;
                    }
                    colegioSelect.appendChild(option);
                });
                // Si hay colegio preseleccionado, disparar carga de rutas
                if (selectedColegioId) {
                    updateColegioInfoFromSelect();
                    loadRutasByColegio(selectedColegioId, currentRutaId);
                }
            })
            .catch(function(err) {
                colegioSelect.innerHTML = '<option value="">Error al cargar colegios</option>';
                console.error('Error cargando colegios:', err);
            });
    }

    function loadRutasByColegio(colegioId, selectedRutaId) {
        var rutaSelect = document.getElementById('ruta_id');
        var paraderoSelect = document.getElementById('paradero_id');

        rutaSelect.innerHTML = '<option value="">Cargando...</option>';
        paraderoSelect.innerHTML = '<option value="">-- Primero seleccione una ruta --</option>';
        document.getElementById('paradero-monto-info').innerHTML = '';

        if (!colegioId) {
            rutaSelect.innerHTML = '<option value="">-- Primero seleccione un colegio --</option>';
            return;
        }

        fetch(urlRutasByColegio + '/' + colegioId + '/rutas')
            .then(function(response) { return response.json(); })
            .then(function(data) {
                rutaSelect.innerHTML = '<option value="">Seleccione una ruta</option>';
                data.forEach(function(ruta) {
                    var option = document.createElement('option');
                    option.value = ruta.id;
                    var label = ruta.key_app;
                    if (ruta.start_time) label += ' - ' + ruta.start_time;
                    if (ruta.out_time) label += ' a ' + ruta.out_time;
                    option.textContent = label;
                    if (selectedRutaId && ruta.id == selectedRutaId) {
                        option.selected = true;
                    }
                    rutaSelect.appendChild(option);
                });
                // Si hay ruta preseleccionada, disparar carga de paraderos
                if (selectedRutaId) {
                    loadParaderosByRuta(selectedRutaId, currentParaderoId);
                }
            })
            .catch(function(err) {
                rutaSelect.innerHTML = '<option value="">Error al cargar rutas</option>';
                console.error('Error cargando rutas:', err);
            });
    }

    function loadParaderosByRuta(rutaId, selectedParaderoId) {
        var paraderoSelect = document.getElementById('paradero_id');
        var montoInfo = document.getElementById('paradero-monto-info');

        paraderoSelect.innerHTML = '<option value="">Cargando...</option>';
        montoInfo.innerHTML = '';

        if (!rutaId) {
            paraderoSelect.innerHTML = '<option value="">-- Primero seleccione una ruta --</option>';
            return;
        }

        fetch(urlParaderosByRuta + '/' + rutaId + '/paraderos')
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.length === 0) {
                    paraderoSelect.innerHTML = '<option value="">No hay paraderos para esta ruta</option>';
                    return;
                }

                // Si solo hay 1 paradero, auto-seleccionarlo
                if (data.length === 1) {
                    var p = data[0];
                    var label = p.nombre;
                    if (p.hora) label += ' (' + p.hora + ')';
                    if (p.es_beca_empresarial) {
                        label += ' - BECA EMPRESARIAL';
                    } else {
                        label += ' - ₡' + Number(p.monto).toLocaleString('es-CR');
                    }
                    paraderoSelect.innerHTML = '';
                    var option = document.createElement('option');
                    option.value = p.id;
                    option.textContent = label;
                    option.dataset.monto = p.monto;
                    option.dataset.esBeca = p.es_beca_empresarial;
                    option.selected = true;
                    paraderoSelect.appendChild(option);
                    updateParaderoMontoInfo();
                    return;
                }

                // Varios paraderos: mostrar lista para elegir
                paraderoSelect.innerHTML = '<option value="">Seleccione un paradero</option>';
                data.forEach(function(paradero) {
                    var option = document.createElement('option');
                    option.value = paradero.id;
                    var label = paradero.nombre;
                    if (paradero.hora) label += ' (' + paradero.hora + ')';
                    if (paradero.es_beca_empresarial) {
                        label += ' - BECA EMPRESARIAL';
                    } else {
                        label += ' - ₡' + Number(paradero.monto).toLocaleString('es-CR');
                    }
                    option.textContent = label;
                    option.dataset.monto = paradero.monto;
                    option.dataset.esBeca = paradero.es_beca_empresarial;
                    if (selectedParaderoId && paradero.id == selectedParaderoId) {
                        option.selected = true;
                    }
                    paraderoSelect.appendChild(option);
                });
                // Si hay paradero preseleccionado, mostrar monto
                if (selectedParaderoId) {
                    updateParaderoMontoInfo();
                }
            })
            .catch(function(err) {
                paraderoSelect.innerHTML = '<option value="">Error al cargar paraderos</option>';
                console.error('Error cargando paraderos:', err);
            });
    }

    function updateColegioInfoFromSelect() {
        var select = document.getElementById('colegio_id');
        var selectedOption = select.options[select.selectedIndex];
        var direccionDiv = document.getElementById('colegio-direccion');
        var colegioHidden = document.getElementById('colegio_hidden');

        if (selectedOption && selectedOption.value) {
            var nombre = selectedOption.dataset.nombre || selectedOption.textContent.trim();
            var direccion = selectedOption.dataset.direccion;
            colegioHidden.value = nombre;
            if (direccion) {
                direccionDiv.innerHTML = '<strong>Direccion:</strong> ' + direccion;
            } else {
                direccionDiv.innerHTML = '';
            }
        } else {
            colegioHidden.value = '';
            direccionDiv.innerHTML = '';
        }
    }

    function updateParaderoMontoInfo() {
        var select = document.getElementById('paradero_id');
        var selectedOption = select.options[select.selectedIndex];
        var montoInfo = document.getElementById('paradero-monto-info');

        if (selectedOption && selectedOption.value) {
            var monto = selectedOption.dataset.monto;
            var esBeca = selectedOption.dataset.esBeca;
            if (esBeca == '1' || esBeca == 'true') {
                montoInfo.innerHTML = '<span class="badge badge-info" style="font-size: 16px;">BECA EMPRESARIAL - Sin costo</span>';
            } else {
                montoInfo.innerHTML = '<span class="badge badge-success" style="font-size: 16px;">Monto por asistencia: ₡' + Number(monto).toLocaleString('es-CR') + '</span>';
            }
        } else {
            montoInfo.innerHTML = '';
        }
    }

    // ========== EVENT LISTENERS ==========

    document.addEventListener('DOMContentLoaded', function() {
        updateBecaInfo();

        // Auto-generar email basado en cedula
        var cedulaInput = document.getElementById('cedula');
        var emailInput = document.getElementById('email');
        var emailModifiedManually = false;
        var isNewRecord = {{ $data->id ? 'false' : 'true' }};

        emailInput.addEventListener('input', function() {
            var cedula = cedulaInput.value.trim();
            var expectedEmail = cedula + '@beebus.com';
            if (emailInput.value !== expectedEmail) {
                emailModifiedManually = true;
            }
        });

        cedulaInput.addEventListener('input', function() {
            var cedula = this.value.trim();
            if (cedula && !emailModifiedManually) {
                emailInput.value = cedula + '@beebus.com';
            }
        });

        if (isNewRecord && !emailInput.value && cedulaInput.value) {
            emailInput.value = cedulaInput.value.trim() + '@beebus.com';
        }

        // ========== CASCADE EVENTS ==========

        // Zona change -> cargar colegios
        document.getElementById('zona_id').addEventListener('change', function() {
            loadColegiosByZona(this.value, null);
        });

        // Colegio change -> actualizar info + cargar rutas
        document.getElementById('colegio_id').addEventListener('change', function() {
            updateColegioInfoFromSelect();
            loadRutasByColegio(this.value, null);
        });

        // Ruta change -> cargar paraderos
        document.getElementById('ruta_id').addEventListener('change', function() {
            loadParaderosByRuta(this.value, null);
        });

        // Paradero change -> mostrar monto
        document.getElementById('paradero_id').addEventListener('change', function() {
            updateParaderoMontoInfo();
        });

        // ========== INIT: cargar datos en edit mode ==========
        @if($data->page_type == 'edit')
            @if($currentZonaId)
                // Tiene colegio con zona: cargar cascada completa zona->colegio->ruta->paradero
                loadColegiosByZona("{{ $currentZonaId }}", currentColegioId);
            @elseif(isset($data->ruta_id) && $data->ruta_id)
                // Legacy: tiene ruta pero no zona/colegio - cargar paraderos directamente si existen
                loadParaderosByRuta(currentRutaId, currentParaderoId);
            @endif
        @endif
    });
</script>

<script src="{{ asset('js/backend/histories/form.js'). '?v=' . rand(99999,999999) }}"></script>
@stop
