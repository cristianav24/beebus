@extends('adminlte::page')

@section('title', 'Crear Nuevo Padre - ' . Config::get('adminlte.title'))

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1>
        <i class="fas fa-user-plus"></i> Crear Nuevo Padre
    </h1>
    <a href="{{ route('admin.parents.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Volver a Lista
    </a>
</div>
@stop

@section('content')
@include('layouts.flash-message')

<form method="POST" action="{{ route('admin.parents.store') }}">
    @csrf
    
    <div class="row">
        <!-- Información de la Cuenta -->
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user-cog"></i> Información de la Cuenta
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Nombre Completo -->
                    <div class="form-group">
                        <label for="name">Nombre Completo <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control @error('name') is-invalid @enderror" 
                               id="name" 
                               name="name" 
                               value="{{ old('name') }}" 
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="form-group">
                        <label for="email">Correo Electrónico <span class="text-danger">*</span></label>
                        <input type="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               id="email" 
                               name="email" 
                               value="{{ old('email') }}" 
                               required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="form-group">
                        <label for="password">Contraseña <span class="text-danger">*</span></label>
                        <input type="password" 
                               class="form-control @error('password') is-invalid @enderror" 
                               id="password" 
                               name="password" 
                               required>
                        <small class="form-text text-muted">Mínimo 6 caracteres</small>
                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div class="form-group">
                        <label for="password_confirmation">Confirmar Contraseña <span class="text-danger">*</span></label>
                        <input type="password" 
                               class="form-control" 
                               id="password_confirmation" 
                               name="password_confirmation" 
                               required>
                        <small class="form-text text-muted">Debe coincidir con la contraseña anterior</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Información Personal -->
        <div class="col-md-6">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-address-card"></i> Información Personal
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Cédula -->
                    <div class="form-group">
                        <label for="cedula">Cédula de Identidad <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control @error('cedula') is-invalid @enderror" 
                               id="cedula" 
                               name="cedula" 
                               value="{{ old('cedula') }}" 
                               placeholder="123456789"
                               required>
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i> Solo números, sin espacios ni guiones
                        </small>
                        @error('cedula')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Teléfono -->
                    <div class="form-group">
                        <label for="telefono">Número de Teléfono <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control @error('telefono') is-invalid @enderror" 
                               id="telefono" 
                               name="telefono" 
                               value="{{ old('telefono') }}" 
                               placeholder="88881234"
                               required>
                        @error('telefono')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Ocupación -->
                    <div class="form-group">
                        <label for="ocupacion">Ocupación</label>
                        <input type="text" 
                               class="form-control @error('ocupacion') is-invalid @enderror" 
                               id="ocupacion" 
                               name="ocupacion" 
                               value="{{ old('ocupacion') }}" 
                               placeholder="Ej: Ingeniero, Docente, Comerciante">
                        @error('ocupacion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Correo Secundario -->
                    <div class="form-group">
                        <label for="correo_secundario">Correo Electrónico Secundario</label>
                        <input type="email" 
                               class="form-control @error('correo_secundario') is-invalid @enderror" 
                               id="correo_secundario" 
                               name="correo_secundario" 
                               value="{{ old('correo_secundario') }}" 
                               placeholder="correo.trabajo@empresa.com">
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i> Opcional. Útil para notificaciones adicionales.
                        </small>
                        @error('correo_secundario')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Información de Ubicación -->
        <div class="col-md-12">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-map-marker-alt"></i> Información de Ubicación
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-12">
                            <!-- Dirección -->
                            <div class="form-group">
                                <label for="direccion">Dirección Exacta <span class="text-danger">*</span></label>
                                <textarea class="form-control @error('direccion') is-invalid @enderror" 
                                          id="direccion" 
                                          name="direccion" 
                                          rows="3" 
                                          required 
                                          placeholder="Ej: Del parque central 200m norte, casa color azul">{{ old('direccion') }}</textarea>
                                @error('direccion')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <!-- Provincia -->
                            <div class="form-group">
                                <label for="provincia">Provincia <span class="text-danger">*</span></label>
                                <select class="form-control @error('provincia') is-invalid @enderror" 
                                        id="provincia" 
                                        name="provincia" 
                                        required>
                                    <option value="">Seleccione una provincia</option>
                                    @foreach($provincias as $provincia)
                                        <option value="{{ $provincia }}" 
                                                {{ old('provincia') == $provincia ? 'selected' : '' }}>
                                            {{ $provincia }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('provincia')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <!-- Cantón -->
                            <div class="form-group">
                                <label for="canton">Cantón <span class="text-danger">*</span></label>
                                <input type="text" 
                                       class="form-control @error('canton') is-invalid @enderror" 
                                       id="canton" 
                                       name="canton" 
                                       value="{{ old('canton') }}" 
                                       placeholder="Ej: San José, Cartago, Alajuela"
                                       required>
                                @error('canton')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="col-md-4">
                            <!-- Distrito -->
                            <div class="form-group">
                                <label for="distrito">Distrito</label>
                                <input type="text" 
                                       class="form-control @error('distrito') is-invalid @enderror" 
                                       id="distrito" 
                                       name="distrito" 
                                       value="{{ old('distrito') }}" 
                                       placeholder="Ej: Carmen, San Rafael">
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i> Opcional, pero recomendado.
                                </small>
                                @error('distrito')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Submit Button -->
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body text-center">
                    <button type="submit" class="btn btn-success btn-lg">
                        <i class="fas fa-user-plus"></i> Crear Padre
                    </button>
                    <a href="{{ route('admin.parents.index') }}" class="btn btn-secondary btn-lg ml-2">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <br><br>
                    <small class="text-muted">
                        Los campos marcados con <span class="text-danger">*</span> son obligatorios<br>
                        El padre podrá cambiar su contraseña después del primer ingreso
                    </small>
                </div>
            </div>
        </div>
    </div>
</form>

@stop

@section('css')
<style>
    .card {
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    }
    
    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
    }
    
    .invalid-feedback {
        display: block;
    }
</style>
@stop

@section('js')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Format cedula input
    const cedulaInput = document.getElementById('cedula');
    if (cedulaInput) {
        cedulaInput.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, ''); // Remove non-digits
            if (value.length <= 9) {
                this.value = value;
            }
        });
    }

    // Format telefono input
    const telefonoInput = document.getElementById('telefono');
    if (telefonoInput) {
        telefonoInput.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, ''); // Remove non-digits
            if (value.length <= 8) {
                this.value = value;
            }
        });
    }

    // Password confirmation validation
    const passwordInput = document.getElementById('password');
    const confirmPasswordInput = document.getElementById('password_confirmation');
    
    function validatePasswordMatch() {
        if (confirmPasswordInput.value !== passwordInput.value) {
            confirmPasswordInput.setCustomValidity('Las contraseñas no coinciden');
        } else {
            confirmPasswordInput.setCustomValidity('');
        }
    }

    passwordInput.addEventListener('input', validatePasswordMatch);
    confirmPasswordInput.addEventListener('input', validatePasswordMatch);

    // Show confirmation before leaving page with unsaved changes
    let formChanged = false;
    const form = document.querySelector('form');
    const inputs = form.querySelectorAll('input, select, textarea');
    
    inputs.forEach(input => {
        input.addEventListener('change', () => {
            formChanged = true;
        });
    });

    window.addEventListener('beforeunload', function(e) {
        if (formChanged) {
            e.preventDefault();
            e.returnValue = '';
        }
    });

    form.addEventListener('submit', function() {
        formChanged = false;
    });
});
</script>
@stop