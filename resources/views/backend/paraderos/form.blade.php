@extends('adminlte::page')

@section('title', (isset($paradero) ? 'Editar' : 'Nuevo') . ' Paradero | ' . Config::get('adminlte.title'))

@section('content_header')
    <h2>
        <i class="fas fa-map-marker-alt"></i>
        {{ isset($paradero) ? 'Editar Paradero' : 'Nuevo Paradero' }}
    </h2>
@stop

@section('content')
    <div class="row">
        <div class="col-md-8">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        {{ isset($paradero) ? 'Modificar datos del paradero' : 'Ingrese los datos del nuevo paradero' }}
                    </h3>
                </div>

                <form method="POST" action="{{ isset($paradero) ? route('paraderos.update') : route('paraderos.create') }}">
                    @csrf
                    @if(isset($paradero))
                        <input type="hidden" name="id" value="{{ $paradero->id }}">
                    @endif

                    <div class="card-body">
                        @if ($errors->any())
                            <div class="alert alert-danger">
                                <ul class="mb-0">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif

                        <!-- Seleccion de Ruta (Cascada: Zona -> Colegio -> Ruta) -->
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="zona_id">Zona</label>
                                    <select name="zona_id" id="zona_id" class="form-control">
                                        <option value="">-- Seleccionar Zona --</option>
                                        @foreach($zonas as $zona)
                                            @php
                                                $selectedZona = false;
                                                if (isset($paradero) && $paradero->ruta && $paradero->ruta->colegio) {
                                                    $selectedZona = $paradero->ruta->colegio->zona_id == $zona->id;
                                                }
                                            @endphp
                                            <option value="{{ $zona->id }}" {{ $selectedZona ? 'selected' : '' }}>
                                                {{ $zona->nombre }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="colegio_id">Colegio</label>
                                    <select name="colegio_id" id="colegio_id" class="form-control">
                                        <option value="">-- Primero seleccione zona --</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="ruta_id">Ruta <span class="text-danger">*</span></label>
                                    <select name="ruta_id" id="ruta_id" class="form-control @error('ruta_id') is-invalid @enderror" required>
                                        <option value="">-- Primero seleccione colegio --</option>
                                        @if(isset($selectedRutaId))
                                            @foreach($rutas as $ruta)
                                                @if($ruta->id == $selectedRutaId)
                                                    <option value="{{ $ruta->id }}" selected>{{ $ruta->key_app }}</option>
                                                @endif
                                            @endforeach
                                        @endif
                                    </select>
                                    @error('ruta_id')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <hr>

                        <!-- Datos del Paradero -->
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="nombre">Nombre del Paradero <span class="text-danger">*</span></label>
                                    <input type="text" name="nombre" id="nombre"
                                           class="form-control @error('nombre') is-invalid @enderror"
                                           value="{{ old('nombre', $paradero->nombre ?? '') }}"
                                           placeholder="Ej: Parada Central, Frente al Banco, etc."
                                           required>
                                    @error('nombre')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="hora">Hora de Paso</label>
                                    <input type="text" name="hora" id="hora"
                                           class="form-control @error('hora') is-invalid @enderror"
                                           value="{{ old('hora', $paradero->hora ?? '') }}"
                                           placeholder="Ej: 6:00 AM">
                                    @error('hora')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">Hora aproximada de paso del bus</small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="monto">Monto (Colones) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">₡</span>
                                        </div>
                                        <input type="number" name="monto" id="monto"
                                               class="form-control @error('monto') is-invalid @enderror"
                                               value="{{ old('monto', $paradero->monto ?? 0) }}"
                                               min="0" step="50" required>
                                    </div>
                                    @error('monto')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">0 = Gratis (beca empresarial)</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="orden">Orden en la Ruta</label>
                                    <input type="number" name="orden" id="orden"
                                           class="form-control @error('orden') is-invalid @enderror"
                                           value="{{ old('orden', $paradero->orden ?? '') }}"
                                           min="0" placeholder="Auto">
                                    @error('orden')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">Dejar vacio para agregar al final</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="estado">Estado <span class="text-danger">*</span></label>
                                    <select name="estado" id="estado" class="form-control @error('estado') is-invalid @enderror" required>
                                        <option value="activo" {{ old('estado', $paradero->estado ?? 'activo') == 'activo' ? 'selected' : '' }}>
                                            Activo
                                        </option>
                                        <option value="inactivo" {{ old('estado', $paradero->estado ?? '') == 'inactivo' ? 'selected' : '' }}>
                                            Inactivo
                                        </option>
                                    </select>
                                    @error('estado')
                                        <span class="invalid-feedback">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <div class="custom-control custom-switch">
                                        <input type="checkbox" name="es_beca_empresarial" id="es_beca_empresarial"
                                               class="custom-control-input" value="1"
                                               {{ old('es_beca_empresarial', $paradero->es_beca_empresarial ?? false) ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="es_beca_empresarial">
                                            <i class="fas fa-building text-purple"></i> Es Beca Empresarial
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">
                                        Marcar si este paradero corresponde a una empresa que paga el transporte de sus empleados
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card-footer">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> {{ isset($paradero) ? 'Actualizar' : 'Guardar' }}
                        </button>
                        <a href="{{ route('paraderos.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Volver
                        </a>
                    </div>
                </form>
            </div>
        </div>

        <!-- Panel lateral de ayuda -->
        <div class="col-md-4">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle"></i> Informacion</h3>
                </div>
                <div class="card-body">
                    <h6><i class="fas fa-map-marker-alt text-danger"></i> ¿Que es un Paradero?</h6>
                    <p class="text-muted small">
                        Un paradero es una parada especifica dentro de una ruta de bus.
                        Cada paradero tiene un monto que se cobra al estudiante cuando aborda en esa ubicacion.
                    </p>

                    <h6><i class="fas fa-sort-numeric-down text-info"></i> Orden</h6>
                    <p class="text-muted small">
                        El orden determina la secuencia de las paradas en la ruta.
                        Si no especifica un orden, el paradero se agregara al final.
                    </p>

                    <h6><i class="fas fa-building text-purple"></i> Beca Empresarial</h6>
                    <p class="text-muted small">
                        Algunos paraderos corresponden a empresas que tienen convenios con BeeBus.
                        Los empleados de estas empresas pueden tener tarifas especiales o gratuitas.
                    </p>

                    @if(isset($paradero) && $paradero->ruta)
                        <hr>
                        <h6><i class="fas fa-route text-success"></i> Ruta Actual</h6>
                        <p>
                            <strong>{{ $paradero->ruta->key_app }}</strong><br>
                            <small class="text-muted">
                                {{ $paradero->ruta->colegio->nombre ?? 'Sin colegio' }}<br>
                                {{ $paradero->ruta->colegio->zona->nombre ?? 'Sin zona' }}
                            </small>
                        </p>
                    @endif
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .text-purple { color: #6f42c1; }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    var currentColegioId = {{ isset($paradero) && $paradero->ruta ? $paradero->ruta->colegio_id : 'null' }};
    var currentRutaId = {{ isset($paradero) ? $paradero->ruta_id : (isset($selectedRutaId) ? $selectedRutaId : 'null') }};

    // Si hay zona seleccionada, cargar colegios
    var zonaId = $('#zona_id').val();
    if (zonaId) {
        loadColegios(zonaId, currentColegioId);
    }

    // Zona change -> load colegios
    $('#zona_id').change(function() {
        var zonaId = $(this).val();
        if (zonaId) {
            loadColegios(zonaId, null);
        } else {
            $('#colegio_id').html('<option value="">-- Primero seleccione zona --</option>');
            $('#ruta_id').html('<option value="">-- Primero seleccione colegio --</option>');
        }
    });

    // Colegio change -> load rutas
    $('#colegio_id').change(function() {
        var colegioId = $(this).val();
        if (colegioId) {
            loadRutas(colegioId, null);
        } else {
            $('#ruta_id').html('<option value="">-- Primero seleccione colegio --</option>');
        }
    });

    function loadColegios(zonaId, selectedId) {
        $.get('/api/zonas/' + zonaId + '/colegios', function(data) {
            var select = $('#colegio_id');
            select.html('<option value="">-- Seleccionar Colegio --</option>');
            data.forEach(function(item) {
                var selected = selectedId == item.id ? 'selected' : '';
                select.append('<option value="' + item.id + '" ' + selected + '>' + item.nombre + '</option>');
            });

            // Si hay colegio preseleccionado, cargar rutas
            if (selectedId) {
                loadRutas(selectedId, currentRutaId);
            }
        });
    }

    function loadRutas(colegioId, selectedId) {
        $.get('/api/colegios/' + colegioId + '/rutas', function(data) {
            var select = $('#ruta_id');
            select.html('<option value="">-- Seleccionar Ruta --</option>');
            data.forEach(function(item) {
                var selected = selectedId == item.id ? 'selected' : '';
                select.append('<option value="' + item.id + '" ' + selected + '>' + item.key_app + ' (' + item.start_time + ' - ' + item.out_time + ')</option>');
            });
        });
    }

    // Si monto es 0, sugerir marcar beca empresarial
    $('#monto').change(function() {
        if ($(this).val() == 0) {
            if (!$('#es_beca_empresarial').is(':checked')) {
                if (confirm('El monto es 0. ¿Desea marcar este paradero como Beca Empresarial?')) {
                    $('#es_beca_empresarial').prop('checked', true);
                }
            }
        }
    });
});
</script>
@stop
