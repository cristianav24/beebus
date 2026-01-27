@extends('adminlte::page')

@section('title', 'Editar Tarifa | ' . Config::get('adminlte.title'))

@section('content_header')
    <h2>Editar Tarifa</h2>
@stop

@section('content')
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Información de la Tarifa</h3>
        </div>
        <form action="{{ route('tarifas.update') }}" method="POST">
            @csrf
            <input type="hidden" name="id" value="{{ $tarifa->id }}">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nombre">Nombre <span class="text-danger">*</span></label>
                            <input type="text" class="form-control @error('nombre') is-invalid @enderror"
                                   id="nombre" name="nombre" value="{{ old('nombre', $tarifa->nombre) }}" required>
                            @error('nombre')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="monto">Monto (₡) <span class="text-danger">*</span></label>
                            <input type="number" class="form-control @error('monto') is-invalid @enderror"
                                   id="monto" name="monto" value="{{ old('monto', $tarifa->monto) }}" min="0" required>
                            @error('monto')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="form-text text-muted">Monto que se cobrará por cada asistencia</small>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="estado">Estado <span class="text-danger">*</span></label>
                            <select class="form-control @error('estado') is-invalid @enderror" id="estado" name="estado" required>
                                <option value="activa" {{ old('estado', $tarifa->estado) == 'activa' ? 'selected' : '' }}>Activa</option>
                                <option value="inactiva" {{ old('estado', $tarifa->estado) == 'inactiva' ? 'selected' : '' }}>Inactiva</option>
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
                              id="descripcion" name="descripcion" rows="3">{{ old('descripcion', $tarifa->descripcion) }}</textarea>
                    @error('descripcion')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="alert alert-info">
                    <i class="fas fa-info-circle"></i>
                    <strong>Estudiantes usando esta tarifa:</strong> {{ $tarifa->histories()->count() }}
                </div>
            </div>
            <div class="card-footer">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Actualizar
                </button>
                <a href="{{ route('tarifas.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Cancelar
                </a>
            </div>
        </form>
    </div>
@stop
