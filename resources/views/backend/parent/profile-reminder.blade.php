@extends('adminlte::page')

@section('title', 'Completar Perfil - ' . Config::get('adminlte.title'))

@section('content_header')
<h1>Completar Perfil</h1>
@stop

@section('content')

<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card card-warning card-outline">
            <div class="card-header text-center">
                <h3 class="card-title">
                    <i class="fas fa-exclamation-triangle fa-2x text-warning mb-3"></i>
                </h3>
            </div>
            <div class="card-body text-center">
                <h4 class="text-warning">¡Perfil Incompleto!</h4>
                
                <p class="lead">
                    Para poder asignar a tus hijos y acceder a todas las funcionalidades del sistema, 
                    necesitas completar tu información de perfil.
                </p>
                
                <div class="alert alert-info text-left">
                    <h5><i class="fas fa-list-ul"></i> Información Requerida:</h5>
                    <ul class="mb-0">
                        <li><strong>Información básica:</strong> Nombre, email, cédula, teléfono</li>
                        <li><strong>Ubicación:</strong> Dirección, provincia, cantón</li>
                        <li><strong>Información adicional:</strong> Ocupación (opcional), correo secundario (opcional)</li>
                    </ul>
                </div>

                <div class="mt-4">
                    <a href="{{ route('parent.profile') }}" class="btn btn-primary btn-lg">
                        <i class="fas fa-user-edit"></i> Completar Mi Perfil
                    </a>
                </div>
                
                <div class="mt-3">
                    <small class="text-muted">
                        Este proceso solo toma unos minutos y es necesario por motivos de seguridad.
                    </small>
                </div>
            </div>
        </div>
        
        <!-- Why this is required -->
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-question-circle"></i> ¿Por qué necesitamos esta información?
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h5><i class="fas fa-shield-alt text-primary"></i> Seguridad</h5>
                        <p>
                            Verificamos la identidad de los padres para proteger la información 
                            de los estudiantes y garantizar que solo personas autorizadas 
                            accedan a los datos.
                        </p>
                    </div>
                    <div class="col-md-6">
                        <h5><i class="fas fa-phone text-success"></i> Comunicación</h5>
                        <p>
                            Necesitamos formas de contactarte en caso de emergencias, 
                            cambios importantes en el sistema o notificaciones 
                            sobre tus hijos.
                        </p>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <h5><i class="fas fa-map-marker-alt text-info"></i> Ubicación</h5>
                        <p>
                            La información de ubicación nos ayuda a brindar un 
                            mejor servicio y es importante para casos de emergencia.
                        </p>
                    </div>
                    <div class="col-md-6">
                        <h5><i class="fas fa-check-double text-warning"></i> Verificación</h5>
                        <p>
                            Los administradores revisan esta información para 
                            confirmar que eres el padre o tutor legal antes 
                            de aprobar el acceso.
                        </p>
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
    
    .fa-2x {
        font-size: 2em;
    }
    
    .text-warning {
        color: #ffc107 !important;
    }
    
    .alert-info {
        background-color: #d1ecf1;
        border-color: #bee5eb;
        color: #0c5460;
    }
</style>
@stop