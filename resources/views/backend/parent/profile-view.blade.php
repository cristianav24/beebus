@extends('adminlte::page')

@section('title', 'Mi Perfil - ' . Config::get('adminlte.title'))

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1>Mi Perfil</h1>
    <div>
        <a href="{{ route('parent.profile') }}" class="btn btn-primary">
            <i class="fas fa-edit"></i> Editar Perfil
        </a>
        <a href="{{ route('parent.dashboard') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al Dashboard
        </a>
    </div>
</div>
@stop

@section('content')

<!-- Profile Completion Status -->
<div class="row mb-4">
    <div class="col-12">
        @if($parent && $parent->isProfileComplete())
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> 
                <strong>¡Perfil Completo!</strong> Tu perfil está completo y tienes acceso a todas las funcionalidades.
            </div>
        @else
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i> 
                <strong>Perfil Incompleto</strong> Por favor, completa toda la información requerida para poder asignar a tus hijos.
                <a href="{{ route('parent.profile') }}" class="btn btn-sm btn-warning ml-2">
                    <i class="fas fa-edit"></i> Completar Ahora
                </a>
            </div>
        @endif
    </div>
</div>

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
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Nombre Completo:</strong></td>
                        <td>{{ $user->name ?: 'No registrado' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Correo Electrónico:</strong></td>
                        <td>{{ $user->email ?: 'No registrado' }}</td>
                    </tr>
                    @if($parent)
                        <tr>
                            <td><strong>Cédula de Identidad:</strong></td>
                            <td>
                                @if($parent->cedula)
                                    <span class="badge badge-info">{{ $parent->formatted_cedula }}</span>
                                @else
                                    <span class="text-muted">No registrada</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Número de Teléfono:</strong></td>
                            <td>
                                @if($parent->telefono)
                                    <span class="badge badge-success">{{ $parent->formatted_telefono }}</span>
                                @else
                                    <span class="text-muted">No registrado</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Ocupación:</strong></td>
                            <td>{{ $parent->ocupacion ?: 'No especificada' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Correo Electrónico Secundario:</strong></td>
                            <td>{{ $parent->correo_secundario ?: 'No registrado' }}</td>
                        </tr>
                    @else
                        <tr>
                            <td colspan="2">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i>
                                    <strong>Información Adicional No Disponible</strong><br>
                                    Completa tu perfil para ver más información.
                                </div>
                            </td>
                        </tr>
                    @endif
                </table>
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
                @if($parent)
                    <table class="table table-borderless">
                        <tr>
                            <td><strong>Dirección Exacta:</strong></td>
                            <td>
                                @if($parent->direccion)
                                    <div class="bg-light p-2 rounded">
                                        {{ $parent->direccion }}
                                    </div>
                                @else
                                    <span class="text-muted">No registrada</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Provincia:</strong></td>
                            <td>
                                @if($parent->provincia)
                                    <span class="badge badge-primary">{{ $parent->provincia }}</span>
                                @else
                                    <span class="text-muted">No registrada</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Cantón:</strong></td>
                            <td>
                                @if($parent->canton)
                                    <span class="badge badge-secondary">{{ $parent->canton }}</span>
                                @else
                                    <span class="text-muted">No registrado</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td><strong>Distrito:</strong></td>
                            <td>{{ $parent->distrito ?: 'No especificado' }}</td>
                        </tr>
                        <tr>
                            <td><strong>Ubicación Completa:</strong></td>
                            <td>
                                @if($parent->provincia || $parent->canton)
                                    <div class="text-info">
                                        <i class="fas fa-map-pin"></i>
                                        {{ $parent->provincia }}{{ $parent->canton ? ', ' . $parent->canton : '' }}{{ $parent->distrito ? ', ' . $parent->distrito : '' }}
                                    </div>
                                @else
                                    <span class="text-muted">Ubicación no completada</span>
                                @endif
                            </td>
                        </tr>
                    </table>
                @else
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Información de Ubicación No Disponible</strong><br>
                        Debes completar tu perfil para registrar tu ubicación.
                        <br><br>
                        <a href="{{ route('parent.profile') }}" class="btn btn-warning btn-sm">
                            <i class="fas fa-plus"></i> Completar Perfil
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Información de Usuario y Seguridad -->
<div class="row">
    <div class="col-md-6">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user-shield"></i> Información de la Cuenta
                </h3>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Usuario de Acceso:</strong></td>
                        <td>{{ $user->email }}</td>
                    </tr>
                    <tr>
                        <td><strong>Nombre de Usuario:</strong></td>
                        <td>{{ $user->name }}</td>
                    </tr>
                    <tr>
                        <td><strong>Tipo de Usuario:</strong></td>
                        <td>
                            <span class="badge badge-info">
                                <i class="fas fa-users"></i> Padre/Tutor
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Estado del Perfil:</strong></td>
                        <td>
                            @if($parent && $parent->isProfileComplete())
                                <span class="badge badge-success">
                                    <i class="fas fa-check-circle"></i> Completo
                                </span>
                            @else
                                <span class="badge badge-warning">
                                    <i class="fas fa-exclamation-triangle"></i> Incompleto
                                </span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Imagen de Perfil:</strong></td>
                        <td>
                            @if($user->image && $user->image !== 'default-user.png')
                                <img src="{{ asset('storage/uploads/' . $user->image) }}" 
                                     class="img-thumbnail" style="max-width: 50px; max-height: 50px;">
                                <span class="ml-2 text-success">Personalizada</span>
                            @else
                                <span class="badge badge-secondary">Imagen por defecto</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Fecha de Registro:</strong></td>
                        <td>{{ $user->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @if($parent)
                    <tr>
                        <td><strong>Última Actualización del Perfil:</strong></td>
                        <td>{{ $parent->updated_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    @endif
                </table>
                
                <hr>
                
                <h5><i class="fas fa-key text-primary"></i> Seguridad de la Cuenta</h5>
                <ul class="list-unstyled">
                    <li><i class="fas fa-shield-alt text-success"></i> Contraseña protegida</li>
                    <li><i class="fas fa-lock text-success"></i> Acceso mediante email y contraseña</li>
                    <li><i class="fas fa-user-check text-success"></i> Verificación de identidad requerida</li>
                </ul>
                
                <div class="mt-3">
                    <a href="{{ route('profile.details') }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-key"></i> Cambiar Contraseña
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="col-md-6">
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-cogs"></i> Panel de Control
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5><i class="fas fa-user-edit text-primary"></i> Gestión de Perfil</h5>
                        <div class="btn-group-vertical d-block" role="group">
                            <a href="{{ route('parent.profile') }}" class="btn btn-outline-primary btn-sm mb-2">
                                <i class="fas fa-edit"></i> Editar Información Personal
                            </a>
                            <a href="{{ route('profile.details') }}" class="btn btn-outline-info btn-sm mb-2">
                                <i class="fas fa-user-cog"></i> Configurar Cuenta de Usuario
                            </a>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <h5><i class="fas fa-tasks text-success"></i> Funciones</h5>
                        <div class="btn-group-vertical d-block" role="group">
                            <a href="{{ route('parent.dashboard') }}" class="btn btn-outline-secondary btn-sm mb-2">
                                <i class="fas fa-tachometer-alt"></i> Mi Dashboard
                            </a>
                            @if($parent && $parent->isProfileComplete())
                            <a href="{{ route('parent.assign-children') }}" class="btn btn-outline-success btn-sm mb-2">
                                <i class="fas fa-user-plus"></i> Asignar Hijos
                            </a>
                            @else
                            <button class="btn btn-outline-secondary btn-sm mb-2" disabled title="Completa tu perfil primero">
                                <i class="fas fa-lock"></i> Asignar Hijos
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
                
                <hr>
                
                <h5><i class="fas fa-info-circle text-info"></i> Información Importante</h5>
                <div class="alert alert-light">
                    <ul class="mb-0">
                        <li><strong>Información Personal:</strong> Se actualiza en "Editar Información Personal"</li>
                        <li><strong>Cuenta de Usuario:</strong> Email, contraseña e imagen se cambian en "Configurar Cuenta"</li>
                        <li><strong>Seguridad:</strong> Mantén tu contraseña segura y actualizada</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Información del Sistema -->
<div class="row">
    <div class="col-12">
        <div class="card card-secondary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle"></i> Información del Sistema
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <h5><i class="fas fa-database text-primary"></i> Estado de Datos</h5>
                        <table class="table table-borderless table-sm">
                            <tr>
                                <td><strong>Perfil de Padre:</strong></td>
                                <td>
                                    @if($parent)
                                        <span class="badge badge-success">Creado</span>
                                    @else
                                        <span class="badge badge-warning">Pendiente</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Campos Completados:</strong></td>
                                <td>
                                    @if($parent)
                                        @php
                                            $totalFields = 7; // name, email, telefono, cedula, direccion, provincia, canton
                                            $completedFields = 2; // name, email always present
                                            if($parent->telefono) $completedFields++;
                                            if($parent->cedula) $completedFields++;
                                            if($parent->direccion) $completedFields++;
                                            if($parent->provincia) $completedFields++;
                                            if($parent->canton) $completedFields++;
                                            $percentage = round(($completedFields / $totalFields) * 100);
                                        @endphp
                                        <div class="progress" style="height: 20px;">
                                            <div class="progress-bar {{ $percentage == 100 ? 'bg-success' : 'bg-warning' }}" 
                                                 role="progressbar" style="width: {{ $percentage }}%">
                                                {{ $completedFields }}/{{ $totalFields }} ({{ $percentage }}%)
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted">No disponible</span>
                                    @endif
                                </td>
                            </tr>
                        </table>
                    </div>
                    
                    <div class="col-md-4">
                        <h5><i class="fas fa-shield-alt text-success"></i> Privacidad y Seguridad</h5>
                        <ul class="list-unstyled">
                            <li><i class="fas fa-check text-success"></i> Información personal protegida</li>
                            <li><i class="fas fa-check text-success"></i> Acceso seguro con contraseña</li>
                            <li><i class="fas fa-check text-success"></i> Datos encriptados</li>
                            <li><i class="fas fa-check text-success"></i> Solo tú y administradores pueden ver tu información</li>
                        </ul>
                    </div>
                    
                    <div class="col-md-4">
                        <h5><i class="fas fa-cogs text-warning"></i> Acciones Disponibles</h5>
                        <div class="btn-group-vertical" role="group">
                            <a href="{{ route('parent.profile') }}" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-edit"></i> Editar Mi Perfil
                            </a>
                            <a href="{{ route('profile.details') }}" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-user-cog"></i> Configuración de Cuenta
                            </a>
                            <a href="{{ route('parent.dashboard') }}" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-tachometer-alt"></i> Ir al Dashboard
                            </a>
                            @if($parent && $parent->isProfileComplete())
                            <a href="{{ route('parent.assign-children') }}" class="btn btn-outline-success btn-sm">
                                <i class="fas fa-user-plus"></i> Asignar Hijos
                            </a>
                            @else
                            <button class="btn btn-outline-secondary btn-sm" disabled>
                                <i class="fas fa-lock"></i> Asignar Hijos (Perfil Incompleto)
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@stop

@section('css')
<style>
    .card {
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    }
    
    .table-borderless td {
        padding: 0.5rem 0.75rem;
        border: none;
        vertical-align: top;
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
    
    .alert-info {
        border-left-color: #17a2b8;
    }
    
    .badge {
        font-size: 0.75em;
        padding: 0.375rem 0.75rem;
    }
    
    .bg-light {
        background-color: #f8f9fa !important;
    }
    
    .btn-group-vertical .btn {
        margin-bottom: 5px;
    }
    
    .text-info {
        color: #17a2b8 !important;
    }
</style>
@stop

@section('js')
<script>
$(document).ready(function() {
    // No JavaScript needed for read-only view
    console.log('Profile view loaded successfully');
});
</script>
@stop