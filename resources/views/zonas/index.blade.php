@extends('adminlte::page')

@section('title', 'Zonas | ' . Config::get('adminlte.title'))

@section('content_header')
    <h2>Zonas</h2>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">Lista de Zonas</h3>
                <a href="{{ route('zonas.add') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nueva Zona
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
                            <th>Colegios</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($zonas as $zona)
                            <tr>
                                <td>{{ $zona->id }}</td>
                                <td>{{ $zona->nombre }}</td>
                                <td>{{ $zona->colegios_count }}</td>
                                <td>
                                    <span class="badge badge-{{ $zona->estado == 'activo' ? 'success' : 'danger' }}">
                                        {{ ucfirst($zona->estado) }}
                                    </span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('zonas.edit', $zona->id) }}" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('zonas.delete', $zona->id) }}" class="btn btn-danger btn-sm"
                                           onclick="return confirm('¿Está seguro de eliminar esta zona?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center">No hay zonas registradas</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center">
                {{ $zonas->links() }}
            </div>
        </div>
    </div>
@stop
