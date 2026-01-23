@extends('adminlte::page')

@section('title', 'Becas | ' . Config::get('adminlte.title'))

@section('content_header')
    <h2>Gestión de Becas</h2>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h3 class="card-title">Lista de Becas</h3>
                <a href="{{ route('becas.add') }}" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nueva Beca
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
                            <th>Monto Créditos</th>
                            <th>Fecha Inicio</th>
                            <th>Fecha Fin</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($becas as $beca)
                            <tr>
                                <td>{{ $beca->id }}</td>
                                <td>{{ $beca->nombre_beca }}</td>
                                <td>
                                    <span class="badge badge-info">{{ $beca->monto_creditos }}</span>
                                </td>
                                <td>{{ $beca->fecha_inicio ? $beca->fecha_inicio->format('d/m/Y') : '-' }}</td>
                                <td>{{ $beca->fecha_fin ? $beca->fecha_fin->format('d/m/Y') : '-' }}</td>
                                <td>
                                    @switch($beca->estado)
                                        @case('activa')
                                            <span class="badge badge-success">Activa</span>
                                            @break
                                        @case('inactiva')
                                            <span class="badge badge-danger">Inactiva</span>
                                            @break
                                        @case('suspendida')
                                            <span class="badge badge-warning">Suspendida</span>
                                            @break
                                    @endswitch
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        <a href="{{ route('becas.edit', $beca->id) }}" class="btn btn-warning btn-sm">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('becas.delete', $beca->id) }}" class="btn btn-danger btn-sm"
                                           onclick="return confirm('¿Está seguro de eliminar esta beca?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center">No hay becas registradas</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-center">
                {{ $becas->links() }}
            </div>
        </div>
    </div>
@stop