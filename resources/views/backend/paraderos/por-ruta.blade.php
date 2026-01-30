@extends('adminlte::page')

@section('title', 'Paraderos de ' . $ruta->key_app . ' | ' . Config::get('adminlte.title'))

@section('content_header')
    <h2>
        <i class="fas fa-route"></i> Paraderos de la Ruta: <strong class="text-primary">{{ $ruta->key_app }}</strong>
    </h2>
@stop

@section('content')
    @include('layouts.flash-message')

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-info">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title">
                            <i class="fas fa-map-marker-alt"></i>
                            {{ $ruta->paraderos->count() }} Paraderos
                        </h3>
                        <a href="{{ route('paraderos.add', ['ruta_id' => $ruta->id]) }}" class="btn btn-light btn-sm">
                            <i class="fas fa-plus"></i> Agregar Paradero
                        </a>
                    </div>
                </div>
                <div class="card-body p-0">
                    @if($ruta->paraderos->count() > 0)
                        <ul class="list-group list-group-flush" id="sortable-paraderos">
                            @foreach($ruta->paraderos as $index => $paradero)
                                <li class="list-group-item d-flex justify-content-between align-items-center" data-id="{{ $paradero->id }}">
                                    <div class="d-flex align-items-center">
                                        <span class="badge badge-secondary mr-3" style="min-width: 30px;">{{ $paradero->orden }}</span>
                                        <i class="fas fa-map-pin text-danger mr-2"></i>
                                        <div>
                                            <strong>{{ $paradero->nombre }}</strong>
                                            @if($paradero->hora)
                                                <small class="text-muted ml-2">
                                                    <i class="far fa-clock"></i> {{ $paradero->hora }}
                                                </small>
                                            @endif
                                            @if($paradero->es_beca_empresarial)
                                                <span class="badge badge-purple ml-2"><i class="fas fa-building"></i></span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center">
                                        @if($paradero->monto == 0)
                                            <span class="badge badge-success mr-3">Gratis</span>
                                        @else
                                            <span class="badge badge-primary mr-3">₡{{ number_format($paradero->monto, 0, ',', '.') }}</span>
                                        @endif
                                        <span class="badge badge-{{ $paradero->estado == 'activo' ? 'success' : 'danger' }} mr-2">
                                            {{ ucfirst($paradero->estado) }}
                                        </span>
                                        <div class="btn-group">
                                            <a href="{{ route('paraderos.edit', $paradero->id) }}" class="btn btn-warning btn-xs">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="{{ route('paraderos.delete', $paradero->id) }}" class="btn btn-danger btn-xs"
                                               onclick="return confirm('¿Eliminar este paradero?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <div class="text-center py-5 text-muted">
                            <i class="fas fa-map-marker-alt fa-4x mb-3"></i>
                            <h5>No hay paraderos en esta ruta</h5>
                            <p>Agrega el primer paradero para comenzar</p>
                            <a href="{{ route('paraderos.add', ['ruta_id' => $ruta->id]) }}" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Agregar Paradero
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <div class="text-center">
                <a href="{{ route('paraderos.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver a Lista General
                </a>
            </div>
        </div>

        <!-- Panel Informacion de la Ruta -->
        <div class="col-md-4">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle"></i> Informacion de la Ruta</h3>
                </div>
                <div class="card-body">
                    <table class="table table-sm">
                        <tr>
                            <th>Codigo:</th>
                            <td>{{ $ruta->key_app }}</td>
                        </tr>
                        <tr>
                            <th>Colegio:</th>
                            <td>{{ $ruta->colegio->nombre ?? 'Sin asignar' }}</td>
                        </tr>
                        <tr>
                            <th>Zona:</th>
                            <td>{{ $ruta->colegio->zona->nombre ?? 'Sin asignar' }}</td>
                        </tr>
                        <tr>
                            <th>Horario Entrada:</th>
                            <td>{{ $ruta->start_time ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Horario Salida:</th>
                            <td>{{ $ruta->out_time ?? '-' }}</td>
                        </tr>
                        <tr>
                            <th>Estado:</th>
                            <td>
                                <span class="badge badge-{{ $ruta->status == 'activo' ? 'success' : 'danger' }}">
                                    {{ ucfirst($ruta->status) }}
                                </span>
                            </td>
                        </tr>
                    </table>
                </div>
            </div>

            @if($ruta->paraderos->count() > 0)
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-pie"></i> Resumen de Tarifas</h3>
                </div>
                <div class="card-body">
                    @php
                        $montos = $ruta->paraderos->pluck('monto')->unique()->sort();
                    @endphp
                    <p class="mb-2"><strong>Montos en esta ruta:</strong></p>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach($montos as $monto)
                            @if($monto == 0)
                                <span class="badge badge-success m-1">Gratis</span>
                            @else
                                <span class="badge badge-primary m-1">₡{{ number_format($monto, 0, ',', '.') }}</span>
                            @endif
                        @endforeach
                    </div>
                    <hr>
                    <small class="text-muted">
                        <i class="fas fa-info-circle"></i>
                        Total de paradas: {{ $ruta->paraderos->where('estado', 'activo')->count() }} activas
                    </small>
                </div>
            </div>
            @endif
        </div>
    </div>
@stop

@section('css')
<style>
    .badge-purple { background-color: #6f42c1; color: white; }
    .btn-xs { padding: 0.15rem 0.4rem; font-size: 0.75rem; }
    .list-group-item { cursor: move; }
    .list-group-item:hover { background-color: #f8f9fa; }
    .gap-2 { gap: 0.5rem; }
</style>
@stop
