@extends('adminlte::page')

@section('title', 'Estudiantes - ' . $colegio->nombre . ' | ' . Config::get('adminlte.title'))

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1>
        <i class="fas fa-users"></i> Estudiantes de {{ $colegio->nombre }}
    </h1>
    <a href="{{ route('colegios.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Volver a Colegios
    </a>
</div>
@stop

@section('content')
<!-- Información del Colegio -->
<div class="card card-info">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-school"></i> Información del Colegio
        </h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-3">
                <strong>Nombre:</strong><br>
                <span class="text-muted">{{ $colegio->nombre }}</span>
            </div>
            <div class="col-md-3">
                <strong>Código:</strong><br>
                <span class="text-muted">{{ $colegio->codigo_institucional ?? 'No asignado' }}</span>
            </div>
            <div class="col-md-3">
                <strong>Teléfono:</strong><br>
                <span class="text-muted">{{ $colegio->telefono ?? 'No asignado' }}</span>
            </div>
            <div class="col-md-3">
                <strong>Estado:</strong><br>
                <span class="badge badge-{{ $colegio->estado == 'activo' ? 'success' : 'danger' }}">
                    {{ ucfirst($colegio->estado) }}
                </span>
            </div>
        </div>
    </div>
</div>

<!-- Estadísticas -->
<div class="row">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $stats['total'] }}</h3>
                <p>Total Estudiantes</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $stats['active'] }}</h3>
                <p>Estudiantes Activos</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-check"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $stats['inactive'] }}</h3>
                <p>Estudiantes Inactivos</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-times"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ number_format($stats['total_credits'], 0) }}</h3>
                <p>Total Créditos</p>
            </div>
            <div class="icon">
                <i class="fas fa-coins"></i>
            </div>
        </div>
    </div>
</div>

<!-- Filtro de Búsqueda -->
<div class="card card-secondary">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-search"></i> Buscar Estudiantes
        </h3>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('colegios.students', $colegio->id) }}">
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <input type="text" 
                               name="search" 
                               id="search" 
                               class="form-control" 
                               placeholder="Buscar por nombre, cédula o sección..." 
                               value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                        <a href="{{ route('colegios.students', $colegio->id) }}" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Limpiar
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Lista de Estudiantes -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-list"></i> Lista de Estudiantes
            @if(request('search'))
                <small class="text-muted">({{ $students->total() }} resultados para "{{ request('search') }}")</small>
            @endif
        </h3>
    </div>
    <div class="card-body">
        @if($students->count() > 0)
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Nombre</th>
                        <th>Cédula</th>
                        <th>Sección</th>
                        <th>Beca</th>
                        <th>Ruta</th>
                        <th>Codigo QR</th>
                        <th>Créditos</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($students as $student)
                    <tr>
                        <td>{{ $student->name }}</td>
                        <td>{{ $student->cedula ?? '-' }}</td>
                        <td>{{ $student->seccion ?? '-' }}</td>
                        <td>
                            @if($student->beca)
                            <span class="badge badge-success">{{ $student->beca->nombre_beca }}</span>
                            @elseif($student->tipoBeca)
                            <span class="badge badge-info">{{ $student->tipoBeca }}</span>
                            @else
                            <span class="badge badge-secondary">Sin beca</span>
                            @endif
                        </td>
                        <td>
                            @if($student->ruta)
                            <span class="badge badge-primary">{{ $student->ruta->key_app }}</span>
                            @elseif($student->nombreRuta)
                            <span class="badge badge-info">{{ $student->nombreRuta }}</span>
                            @else
                            <span class="badge badge-secondary">Sin ruta</span>
                            @endif
                        </td>
                        <td class="qr-cell" data-id='{{ $student->codigo_qr }}'>
                        </td>
                        <td>
                            <span class="badge badge-{{ $student->creditos > 0 ? 'success' : 'warning' }}">
                                {{ number_format($student->creditos, 0) }}
                            </span>
                        </td>
                        <td>
                            <span class="badge badge-{{ $student->status == 1 ? 'success' : 'danger' }}">
                                {{ $student->status == 1 ? 'Activo' : 'Inactivo' }}
                            </span>
                        </td>
                        <td>
                            <div class="btn-group" role="group">
                                <a href="{{ route('transactions.index', ['search' => $student->name]) }}" class="btn btn-info btn-sm" title="Ver Transacciones de Crédito">
                                    <i class="fas fa-credit-card"></i>
                                </a>
                                <a href="{{ route('histories.edit', $student->id) }}" class="btn btn-primary btn-sm" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                @if(auth()->user()->role == 1 || auth()->user()->role == 2)
                                @if($student->status == 1)
                                <form method="POST" action="{{ route('histories.setInactive') }}" style="display: inline;">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $student->id }}">
                                    <button type="submit" class="btn btn-warning btn-sm" title="Desactivar"
                                        onclick="return confirm('¿Está seguro de desactivar este estudiante?')">
                                        <i class="fas fa-user-times"></i>
                                    </button>
                                </form>
                                @else
                                <form method="POST" action="{{ route('histories.setActive') }}" style="display: inline;">
                                    @csrf
                                    <input type="hidden" name="id" value="{{ $student->id }}">
                                    <button type="submit" class="btn btn-success btn-sm" title="Activar">
                                        <i class="fas fa-user-check"></i>
                                    </button>
                                </form>
                                @endif
                                @endif
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center">
            {{ $students->links() }}
        </div>
        @else
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <strong>No hay estudiantes registrados</strong> para este colegio.
        </div>
        @endif
    </div>
</div>

<!-- Quick Actions Card -->
<div class="card card-secondary">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-tools"></i> Acciones Rápidas
        </h3>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-4">
                <a href="{{ route('histories.add') }}?colegio_id={{ $colegio->id }}" class="btn btn-success btn-block">
                    <i class="fas fa-user-plus"></i> Agregar Estudiante
                </a>
            </div>
            <div class="col-md-4">
                <a href="{{ route('colegios.edit', $colegio->id) }}" class="btn btn-warning btn-block">
                    <i class="fas fa-edit"></i> Editar Colegio
                </a>
            </div>
            <div class="col-md-4">
                <a href="{{ route('histories') }}?search={{ $colegio->nombre }}" class="btn btn-info btn-block">
                    <i class="fas fa-search"></i> Ver en Historias
                </a>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .small-box {
        border-radius: 10px;
    }

    .card {
        box-shadow: 0 0 1px rgba(0, 0, 0, .125), 0 1px 3px rgba(0, 0, 0, .2);
        border-radius: 10px;
    }

    .table th {
        background-color: #f8f9fa;
        border-top: none;
    }

    .badge {
        font-size: 0.875em;
    }

    .btn-group .btn {
        margin-right: 2px;
    }

    .alert-info {
        background-color: #d1ecf1;
        border-color: #bee5eb;
        color: #0c5460;
    }
</style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        // Initialize tooltips
        $('[title]').tooltip();

        // Confirm before status change
        $('form[action*="setInactive"], form[action*="setActive"]').on('submit', function(e) {
            const action = $(this).attr('action').includes('setInactive') ? 'desactivar' : 'activar';
            if (!confirm(`¿Está seguro de ${action} este estudiante?`)) {
                e.preventDefault();
            }
        });

        $('.qr-cell').each(function() {
            var id = $(this).data('id');
            var qr = qrcode(0, 'L');
            qr.addData(id);
            qr.make();
            
            // Create canvas to convert to PNG
            var canvas = document.createElement('canvas');
            var size = qr.getModuleCount();
            var cellSize = 4;
            canvas.width = canvas.height = size * cellSize;
            var ctx = canvas.getContext('2d');
            
            // Draw QR code on canvas
            for (var row = 0; row < size; row++) {
                for (var col = 0; col < size; col++) {
                    ctx.fillStyle = qr.isDark(row, col) ? '#000000' : '#ffffff';
                    ctx.fillRect(col * cellSize, row * cellSize, cellSize, cellSize);
                }
            }
            
            // Convert to PNG data URL
            var qrImage = canvas.toDataURL('image/png');
            var buttonHtml = '<a href="' + qrImage + '" download="qr_code_' + id + '.png"><button class="btn btn-danger">Descargar QR</button></a>';
            $(this).html(buttonHtml);
        });
    });
</script>
@stop