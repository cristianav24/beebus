@extends('adminlte::page')

@section('title', 'Editar Beca | ' . Config::get('adminlte.title'))

@section('content_header')
    <h2>Editar Beca</h2>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Información de la Beca</h3>
        </div>
        <form action="{{ route('becas.update') }}" method="POST">
            @csrf
            <input type="hidden" name="id" value="{{ $beca->id }}">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nombre_beca">Nombre de la Beca <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nombre_beca') is-invalid @enderror" 
                                   id="nombre_beca" name="nombre_beca" value="{{ old('nombre_beca', $beca->nombre_beca) }}" required>
                            @error('nombre_beca')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="monto_creditos">Monto en Créditos <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('monto_creditos') is-invalid @enderror" 
                                   id="monto_creditos" name="monto_creditos" value="{{ old('monto_creditos', $beca->monto_creditos) }}" 
                                   min="0" required>
                            @error('monto_creditos')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="fecha_inicio">Fecha de Inicio</label>
                            <input type="date" class="form-control @error('fecha_inicio') is-invalid @enderror" 
                                   id="fecha_inicio" name="fecha_inicio" 
                                   value="{{ old('fecha_inicio', $beca->fecha_inicio ? $beca->fecha_inicio->format('Y-m-d') : '') }}">
                            @error('fecha_inicio')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="fecha_fin">Fecha de Fin</label>
                            <input type="date" class="form-control @error('fecha_fin') is-invalid @enderror" 
                                   id="fecha_fin" name="fecha_fin" 
                                   value="{{ old('fecha_fin', $beca->fecha_fin ? $beca->fecha_fin->format('Y-m-d') : '') }}">
                            @error('fecha_fin')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="estado">Estado <span class="text-danger">*</span></label>
                            <select class="form-control @error('estado') is-invalid @enderror" id="estado" name="estado" required>
                                <option value="">Seleccione un estado</option>
                                <option value="activa" {{ old('estado', $beca->estado) == 'activa' ? 'selected' : '' }}>Activa</option>
                                <option value="inactiva" {{ old('estado', $beca->estado) == 'inactiva' ? 'selected' : '' }}>Inactiva</option>
                                <option value="suspendida" {{ old('estado', $beca->estado) == 'suspendida' ? 'selected' : '' }}>Suspendida</option>
                            </select>
                            @error('estado')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <textarea class="form-control @error('descripcion') is-invalid @enderror" 
                              id="descripcion" name="descripcion" rows="4">{{ old('descripcion', $beca->descripcion) }}</textarea>
                    @error('descripcion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Actualizar
                </button>
                <a href="{{ route('becas.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
@stop