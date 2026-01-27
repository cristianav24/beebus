@extends('adminlte::page')

@section('title', 'Estudiantes - ' . $tarifa->nombre . ' | ' . Config::get('adminlte.title'))

@section('content_header')
    <h2>Estudiantes con Tarifa: {{ $tarifa->nombre }}</h2>
@stop

@section('content')
    <div class="row">
        <div class="col-md-3">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Información de Tarifa</h3>
                </div>
                <div class="card-body">
                    <p><strong>Nombre:</strong> {{ $tarifa->nombre }}</p>
                    <p><strong>Monto:</strong> <span class="text-success">₡{{ number_format($tarifa->monto, 0, ',', '.') }}</span></p>
                    <p><strong>Estado:</strong>
                        <span class="badge badge-{{ $tarifa->estado == 'activa' ? 'success' : 'danger' }}">
                            {{ ucfirst($tarifa->estado) }}
                        </span>
                    </p>
                    @if($tarifa->descripcion)
                        <p><strong>Descripción:</strong><br>{{ $tarifa->descripcion }}</p>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header bg-info">
                    <h3 class="card-title">Estadísticas</h3>
                </div>
                <div class="card-body">
                    <p><i class="fas fa-users"></i> Total: <strong>{{ $stats['total'] }}</strong></p>
                    <p><i class="fas fa-check text-success"></i> Activos: <strong>{{ $stats['active'] }}</strong></p>
                    <p><i class="fas fa-ban text-danger"></i> Inactivos: <strong>{{ $stats['inactive'] }}</strong></p>
                </div>
            </div>

            <a href="{{ route('tarifas.index') }}" class="btn btn-secondary btn-block">
                <i class="fas fa-arrow-left"></i> Volver a Tarifas
            </a>
        </div>

        <div class="col-md-9">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">Lista de Estudiantes</h3>
                        <form action="" method="GET" class="form-inline">
                            <input type="text" name="search" class="form-control form-control-sm mr-2"
                                   placeholder="Buscar estudiante..." value="{{ request('search') }}">
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                        </form>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead>
                                <tr>
                                    <th>Nombre</th>
                                    <th>Cédula</th>
                                    <th>Colegio</th>
                                    <th>Ruta</th>
                                    <th>Créditos</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($students as $student)
                                    <tr>
                                        <td>{{ $student->name }}</td>
                                        <td>{{ $student->cedula }}</td>
                                        <td>{{ $student->colegio->nombre ?? $student->colegio ?? '-' }}</td>
                                        <td>{{ $student->ruta->key_app ?? '-' }}</td>
                                        <td>
                                            <span class="badge badge-{{ $student->creditos >= 0 ? 'success' : 'danger' }}">
                                                ₡{{ number_format($student->creditos, 0, ',', '.') }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge badge-{{ $student->status == 1 ? 'success' : 'danger' }}">
                                                {{ $student->status == 1 ? 'Activo' : 'Inactivo' }}
                                            </span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center">No hay estudiantes con esta tarifa</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex justify-content-center">
                        {{ $students->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
