@extends('adminlte::page')

@section('title', 'Mi Perfil - ' . Config::get('adminlte.title'))

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1>Mi Perfil</h1>
    <a href="{{ route('parent.dashboard') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Volver al Dashboard
    </a>
</div>
@stop

@section('content')
@include('layouts.flash-message')

<!-- Profile Completion Status -->
<div class="row mb-4">
    <div class="col-12">
        @if($parent->isProfileComplete())
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> 
                <strong>¡Perfil Completo!</strong> Tu perfil está completo y puedes acceder a todas las funcionalidades.
            </div>
        @else
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> 
                <strong>Perfil Incompleto</strong> Por favor, completa toda la información requerida para poder asignar a tus hijos.
            </div>
        @endif
    </div>
</div>

<form method="POST" action="{{ route('parent.profile.update') }}">
    @csrf
    
    <div class="row">
        <!-- Información Básica -->
        <div class="col-md-6">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user"></i> Información Básica
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
                               value="{{ old('name', $user->name) }}" 
                               required>
                        @error('name')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Email Principal -->
                    <div class="form-group">
                        <label for="email">Correo Electrónico <span class="text-danger">*</span></label>
                        <input type="email" 
                               class="form-control @error('email') is-invalid @enderror" 
                               id="email" 
                               name="email" 
                               value="{{ old('email', $user->email) }}" 
                               required>
                        @error('email')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Cédula -->
                    <div class="form-group">
                        <label for="cedula">Cédula de Identidad <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control @error('cedula') is-invalid @enderror" 
                               id="cedula" 
                               name="cedula" 
                               value="{{ old('cedula', $parent->cedula) }}" 
                               placeholder="1-2345-6789"
                               required>
                        <small class="form-text text-muted">
                            <i class="fas fa-info-circle"></i> Formato: sin espacios ni guiones
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
                               value="{{ old('telefono', $parent->telefono) }}" 
                               placeholder="8888-1234"
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
                               value="{{ old('ocupacion', $parent->ocupacion) }}" 
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
                               value="{{ old('correo_secundario', $parent->correo_secundario) }}" 
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

        <!-- Información de Ubicación -->
        <div class="col-md-6">
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-map-marker-alt"></i> Información de Ubicación
                    </h3>
                </div>
                <div class="card-body">
                    <!-- Dirección -->
                    <div class="form-group">
                        <label for="direccion">Dirección Exacta <span class="text-danger">*</span></label>
                        <textarea class="form-control @error('direccion') is-invalid @enderror" 
                                  id="direccion" 
                                  name="direccion" 
                                  rows="3" 
                                  required 
                                  placeholder="Ej: Del parque central 200m norte, casa color azul">{{ old('direccion', $parent->direccion) }}</textarea>
                        @error('direccion')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

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
                                        {{ old('provincia', $parent->provincia) == $provincia ? 'selected' : '' }}>
                                    {{ $provincia }}
                                </option>
                            @endforeach
                        </select>
                        @error('provincia')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Cantón -->
                    <div class="form-group">
                        <label for="canton">Cantón <span class="text-danger">*</span></label>
                        <input type="text" 
                               class="form-control @error('canton') is-invalid @enderror" 
                               id="canton" 
                               name="canton" 
                               value="{{ old('canton', $parent->canton) }}" 
                               placeholder="Ej: San José, Cartago, Alajuela"
                               required>
                        @error('canton')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Distrito -->
                    <div class="form-group">
                        <label for="distrito">Distrito</label>
                        <input type="text" 
                               class="form-control @error('distrito') is-invalid @enderror" 
                               id="distrito" 
                               name="distrito" 
                               value="{{ old('distrito', $parent->distrito) }}" 
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

    <!-- Información Importante -->
    <div class="row">
        <div class="col-12">
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-info-circle"></i> Información Importante
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5><i class="fas fa-shield-alt text-primary"></i> Privacidad y Seguridad</h5>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-check text-success"></i> Tu información personal está protegida</li>
                                <li><i class="fas fa-check text-success"></i> Solo tú y los administradores pueden verla</li>
                                <li><i class="fas fa-check text-success"></i> Usamos encriptación para proteger tus datos</li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h5><i class="fas fa-users text-primary"></i> Proceso de Verificación</h5>
                            <ul class="list-unstyled">
                                <li><i class="fas fa-info text-info"></i> Completar el perfil es requerido</li>
                                <li><i class="fas fa-info text-info"></i> Los administradores verificarán la información</li>
                                <li><i class="fas fa-info text-info"></i> Una vez aprobado, podrás ver información de tus hijos</li>
                            </ul>
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
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-save"></i> Guardar Información del Perfil
                    </button>
                    <br>
                    <small class="text-muted mt-2">
                        Los campos marcados con <span class="text-danger">*</span> son obligatorios
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
    
    .alert {
        border-left: 5px solid;
    }
    
    .alert-success {
        border-left-color: #28a745;
    }
    
    .alert-warning {
        border-left-color: #ffc107;
    }
    
    .form-control:focus {
        border-color: #007bff;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
    }
    
    .required-fields {
        background-color: #f8f9fa;
        border-radius: .25rem;
        padding: 1rem;
        margin-bottom: 1rem;
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