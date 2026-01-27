@extends('adminlte::page')

@section('title', 'Tarifas | ' . Config::get('adminlte.title'))

@section('content_header')
    <h2>Gestión de Tarifas</h2>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">Lista de Tarifas</h3>
                <a href="{{ route('tarifas.add') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nueva Tarifa
                </a>
            </div>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            @endif

            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Monto</th>
                            <th>Descripción</th>
                            <th>Estado</th>
                            <th>Estudiantes</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tarifas as $tarifa)
                            <tr>
                                <td>{{ $tarifa->id }}</td>
                                <td>{{ $tarifa->nombre }}</td>
                                <td><strong>₡{{ number_format($tarifa->monto, 0, ',', '.') }}</strong></td>
                                <td>{{ Str::limit($tarifa->descripcion, 50) ?? '-' }}</td>
                                <td>
                                    <span class="badge badge-{{ $tarifa->estado == 'activa' ? 'success' : 'danger' }}">
                                        {{ ucfirst($tarifa->estado) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge badge-info">
                                        {{ $tarifa->histories()->count() }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('tarifas.students', $tarifa->id) }}" class="btn btn-info btn-sm" title="Ver Estudiantes">
                                            <i class="fas fa-users"></i>
                                        </a>
                                        <a href="{{ route('tarifas.edit', $tarifa->id) }}" class="btn btn-warning btn-sm" title="Editar">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('tarifas.delete', $tarifa->id) }}" class="btn btn-danger btn-sm"
                                           onclick="return confirm('¿Está seguro de eliminar esta tarifa?')" title="Eliminar">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No hay tarifas registradas</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center">
                {{ $tarifas->links() }}
            </div>
        </div>
    </div>
@stop
