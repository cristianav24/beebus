@extends('adminlte::page')

@section('title', 'Colegios | ' . Config::get('adminlte.title'))

@section('content_header')
    <h2>Gestión de Colegios</h2>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">Lista de Colegios</h3>
                <a href="{{ route('colegios.add') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nuevo Colegio
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

            <div class="table-responsive">
                <table class="table table-striped table-bordered">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Código</th>
                            <th>Teléfono</th>
                            <th>Email</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($colegios as $colegio)
                            <tr>
                                <td>{{ $colegio->id }}</td>
                                <td>{{ $colegio->nombre }}</td>
                                <td>{{ $colegio->codigo_institucional ?? '-' }}</td>
                                <td>{{ $colegio->telefono ?? '-' }}</td>
                                <td>{{ $colegio->email ?? '-' }}</td>
                                <td>
                                    <span class="badge badge-{{ $colegio->estado == 'activo' ? 'success' : 'danger' }}">
                                        {{ ucfirst($colegio->estado) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('colegios.students', $colegio->id) }}" class="btn btn-info btn-sm" title="Ver Estudiantes">
                                            <i class="fas fa-users"></i>
                                        </a>
                                        <a href="{{ route('colegios.edit', $colegio->id) }}" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('colegios.delete', $colegio->id) }}" class="btn btn-danger btn-sm"
                                           onclick="return confirm('¿Está seguro de eliminar este colegio?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No hay colegios registrados</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center">
                {{ $colegios->links() }}
            </div>
        </div>
    </div>
@stop