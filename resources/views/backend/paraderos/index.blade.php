@extends('adminlte::page')

@section('title', 'Paraderos | ' . Config::get('adminlte.title'))

@section('content_header')
    <h2><i class="fas fa-map-marker-alt"></i> Gestion de Paraderos</h2>
@stop

@section('content')
    @include('layouts.flash-message')

    <!-- Estadisticas -->
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $stats['total'] }}</h3>
                    <p>Total Paraderos</p>
                </div>
                <div class="icon"><i class="fas fa-map-marker-alt"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $stats['activos'] }}</h3>
                    <p>Activos</p>
                </div>
                <div class="icon"><i class="fas fa-check"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $stats['inactivos'] }}</h3>
                    <p>Inactivos</p>
                </div>
                <div class="icon"><i class="fas fa-pause"></i></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="small-box bg-purple">
                <div class="inner">
                    <h3>{{ $stats['becas_empresariales'] }}</h3>
                    <p>Becas Empresariales</p>
                </div>
                <div class="icon"><i class="fas fa-building"></i></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center flex-wrap">
                <h3 class="card-title">Lista de Paraderos</h3>
                <div>
                    <a href="{{ route('paraderos.add') }}" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Nuevo Paradero
                    </a>
                    <button type="button" class="btn btn-secondary" data-toggle="modal" data-target="#modalDuplicar">
                        <i class="fas fa-copy"></i> Duplicar Paraderos
                    </button>
                </div>
            </div>
        </div>

        <div class="card-body">
            <!-- Filtros -->
            <form method="GET" action="{{ route('paraderos.index') }}" class="mb-4">
                <div class="row">
                    <div class="col-md-2">
                        <label>Zona</label>
                        <select name="zona_id" id="filter_zona" class="form-control form-control-sm">
                            <option value="">-- Todas --</option>
                            @foreach($zonas as $zona)
                                <option value="{{ $zona->id }}" {{ request('zona_id') == $zona->id ? 'selected' : '' }}>
                                    {{ $zona->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label>Colegio</label>
                        <select name="colegio_id" id="filter_colegio" class="form-control form-control-sm">
                            <option value="">-- Todos --</option>
                            @foreach($colegios as $colegio)
                                <option value="{{ $colegio->id }}" {{ request('colegio_id') == $colegio->id ? 'selected' : '' }}>
                                    {{ $colegio->nombre }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>Ruta</label>
                        <select name="ruta_id" id="filter_ruta" class="form-control form-control-sm">
                            <option value="">-- Todas --</option>
                            @foreach($rutas as $ruta)
                                <option value="{{ $ruta->id }}" {{ request('ruta_id') == $ruta->id ? 'selected' : '' }}>
                                    {{ $ruta->key_app }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>Estado</label>
                        <select name="estado" class="form-control form-control-sm">
                            <option value="">-- Todos --</option>
                            <option value="activo" {{ request('estado') == 'activo' ? 'selected' : '' }}>Activo</option>
                            <option value="inactivo" {{ request('estado') == 'inactivo' ? 'selected' : '' }}>Inactivo</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>Buscar</label>
                        <input type="text" name="search" class="form-control form-control-sm" placeholder="Nombre..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-1 d-flex align-items-end">
                        <button type="submit" class="btn btn-info btn-sm mr-1"><i class="fas fa-search"></i></button>
                        <a href="{{ route('paraderos.index') }}" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i></a>
                    </div>
                </div>
            </form>

            <!-- Tabla -->
            <div class="table-responsive">
                <table class="table table-striped table-bordered table-sm">
                    <thead class="thead-dark">
                        <tr>
                            <th width="50">Orden</th>
                            <th>Nombre Paradero</th>
                            <th>Hora</th>
                            <th>Monto</th>
                            <th>Ruta</th>
                            <th>Colegio</th>
                            <th>Zona</th>
                            <th width="80">Beca Emp.</th>
                            <th width="80">Estado</th>
                            <th width="120">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($paraderos as $paradero)
                            <tr>
                                <td class="text-center">
                                    <span class="badge badge-secondary">{{ $paradero->orden }}</span>
                                </td>
                                <td>
                                    <i class="fas fa-map-pin text-danger"></i>
                                    {{ $paradero->nombre }}
                                </td>
                                <td>{{ $paradero->hora ?? '-' }}</td>
                                <td>
                                    @if($paradero->monto == 0)
                                        <span class="badge badge-success">Gratis</span>
                                    @else
                                        <strong class="text-primary">{{ number_format($paradero->monto, 0, ',', '.') }}</strong>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-info">{{ $paradero->ruta->key_app ?? 'N/A' }}</span>
                                </td>
                                <td>{{ $paradero->ruta->colegio->nombre ?? 'N/A' }}</td>
                                <td>{{ $paradero->ruta->colegio->zona->nombre ?? 'N/A' }}</td>
                                <td class="text-center">
                                    @if($paradero->es_beca_empresarial)
                                        <span class="badge badge-purple"><i class="fas fa-building"></i> Si</span>
                                    @else
                                        <span class="text-muted">No</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-{{ $paradero->estado == 'activo' ? 'success' : 'danger' }}">
                                        {{ ucfirst($paradero->estado) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('paraderos.edit', $paradero->id) }}" class="btn btn-warning btn-xs" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('paraderos.delete', $paradero->id) }}" class="btn btn-danger btn-xs"
                                           onclick="return confirm('¿Eliminar este paradero?')" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center text-muted py-4">
                                    <i class="fas fa-map-marker-alt fa-3x mb-3 d-block"></i>
                                    No hay paraderos registrados con los filtros seleccionados
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center mt-3">
                <div class="text-muted">
                    Mostrando {{ $paraderos->firstItem() ?? 0 }} - {{ $paraderos->lastItem() ?? 0 }} de {{ $paraderos->total() }} paraderos
                </div>
                {{ $paraderos->links() }}
            </div>
        </div>
    </div>

    <!-- Modal Duplicar Paraderos -->
    <div class="modal fade" id="modalDuplicar" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('paraderos.duplicar') }}">
                    @csrf
                    <div class="modal-header bg-secondary">
                        <h5 class="modal-title"><i class="fas fa-copy"></i> Duplicar Paraderos</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <p class="text-muted">Copiar todos los paraderos de una ruta a otra.</p>
                        <div class="form-group">
                            <label>Ruta Origen <span class="text-danger">*</span></label>
                            <select name="ruta_origen_id" class="form-control" required>
                                <option value="">-- Seleccionar --</option>
                                @foreach($rutas as $ruta)
                                    <option value="{{ $ruta->id }}">{{ $ruta->key_app }} ({{ $ruta->paraderos->count() }} paraderos)</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Ruta Destino <span class="text-danger">*</span></label>
                            <select name="ruta_destino_id" class="form-control" required>
                                <option value="">-- Seleccionar --</option>
                                @foreach($rutas as $ruta)
                                    <option value="{{ $ruta->id }}">{{ $ruta->key_app }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-copy"></i> Duplicar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@stop

@section('css')
<style>
    .badge-purple { background-color: #6f42c1; color: white; }
    .bg-purple { background-color: #6f42c1 !important; }
    .bg-purple .icon { color: rgba(255,255,255,0.2); }
    .btn-xs { padding: 0.15rem 0.4rem; font-size: 0.75rem; }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // Filtro cascada para zona -> colegio -> ruta
    $('#filter_zona').change(function() {
        var zonaId = $(this).val();
        if (zonaId) {
            $.get('/api/zonas/' + zonaId + '/colegios', function(data) {
                var select = $('#filter_colegio');
                select.html('<option value="">-- Todos --</option>');
                data.forEach(function(item) {
                    select.append('<option value="' + item.id + '">' + item.nombre + '</option>');
                });
            });
        }
    });

    $('#filter_colegio').change(function() {
        var colegioId = $(this).val();
        if (colegioId) {
            $.get('/api/colegios/' + colegioId + '/rutas', function(data) {
                var select = $('#filter_ruta');
                select.html('<option value="">-- Todas --</option>');
                data.forEach(function(item) {
                    select.append('<option value="' + item.id + '">' + item.key_app + '</option>');
                });
            });
        }
    });
});
</script>
@stop
