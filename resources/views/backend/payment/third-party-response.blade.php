@extends('adminlte::master')

@section('adminlte_css_pre')
    <link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
@stop

@section('adminlte_css')
    @stack('css')
    @yield('css')
@stop

@section('classes_body', 'login-page')

@section('body')
    <div class="login-box" style="width: 700px; margin-top: 20px;">
        <div class="login-logo">
            <img src="https://greenland.ga/logo/logo2.png" alt="BeeBus" height="130" width="300" style="opacity: .8">
        </div>

        @if($success)
            <!-- Success Message -->
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-check-circle"></i> Pago Exitoso
                    </h3>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <i class="fas fa-check-circle text-success" style="font-size: 72px;"></i>
                        <h3 class="mt-3">{{ $message }}</h3>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-user"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Estudiante</span>
                                    <span class="info-box-number" style="font-size: 1rem;">{{ $data['student_name'] ?? 'N/A' }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box bg-success">
                                <span class="info-box-icon"><i class="fas fa-coins"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Créditos Agregados</span>
                                    <span class="info-box-number">{{ $data['formatted_amount'] ?? '₡0' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="info-box bg-info">
                                <span class="info-box-icon"><i class="fas fa-wallet"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Saldo Anterior</span>
                                    <span class="info-box-number">₡{{ number_format($data['previous_balance'] ?? 0, 0) }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="info-box bg-primary">
                                <span class="info-box-icon"><i class="fas fa-money-bill-wave"></i></span>
                                <div class="info-box-content">
                                    <span class="info-box-text">Nuevo Saldo</span>
                                    <span class="info-box-number">{{ $data['formatted_new_balance'] ?? '₡0' }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Transaction Details -->
                    <div class="card card-outline card-secondary mt-3">
                        <div class="card-header">
                            <h3 class="card-title">Detalles de la Transacción</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-sm">
                                <tr>
                                    <th style="width: 40%">Código de Autorización:</th>
                                    <td>{{ $data['authorization_code'] ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Número de Operación:</th>
                                    <td>{{ $data['operation_number'] ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Referencia de Pago:</th>
                                    <td>{{ $data['payment_reference'] ?? 'N/A' }}</td>
                                </tr>
                                @if(isset($data['brand']))
                                <tr>
                                    <th>Marca de Tarjeta:</th>
                                    <td>{{ $data['brand'] }}</td>
                                </tr>
                                @endif
                                @if(isset($data['bin']))
                                <tr>
                                    <th>BIN:</th>
                                    <td>{{ $data['bin'] }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    </div>

                    <div class="alert alert-info mt-3">
                        <i class="fas fa-info-circle"></i>
                        <strong>Importante:</strong> Por favor guarde este comprobante para futuras consultas.
                        Los créditos han sido agregados exitosamente a la cuenta del estudiante.
                    </div>

                    <div class="text-center mt-4">
                        <a href="{{ route('third-party.search') }}" class="btn btn-success btn-lg">
                            <i class="fas fa-redo"></i> Realizar Otra Recarga
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-secondary btn-lg">
                            <i class="fas fa-home"></i> Ir al Login
                        </a>
                    </div>
                </div>
            </div>
        @else
            <!-- Error Message -->
            <div class="card card-danger">
                <div class="card-header">
                    <h3 class="card-title mb-0">
                        <i class="fas fa-exclamation-triangle"></i> Error en el Pago
                    </h3>
                </div>
                <div class="card-body">
                    <div class="text-center mb-4">
                        <i class="fas fa-times-circle text-danger" style="font-size: 72px;"></i>
                        <h3 class="mt-3">{{ $message }}</h3>
                    </div>

                    @if(!empty($data))
                        <div class="alert alert-warning">
                            <h5><i class="icon fas fa-exclamation-triangle"></i> Detalles del Error:</h5>
                            <table class="table table-sm table-bordered">
                                @if(isset($data['error_code']))
                                <tr>
                                    <th style="width: 30%">Código de Error:</th>
                                    <td>{{ $data['error_code'] }}</td>
                                </tr>
                                @endif
                                @if(isset($data['error_message']))
                                <tr>
                                    <th>Mensaje:</th>
                                    <td>{{ $data['error_message'] }}</td>
                                </tr>
                                @endif
                                @if(isset($data['authorization_result']))
                                <tr>
                                    <th>Resultado:</th>
                                    <td>{{ $data['authorization_result'] }}</td>
                                </tr>
                                @endif
                                @if(isset($data['operation_number']))
                                <tr>
                                    <th>Número de Operación:</th>
                                    <td>{{ $data['operation_number'] }}</td>
                                </tr>
                                @endif
                            </table>
                        </div>
                    @endif

                    <div class="callout callout-info">
                        <h5>¿Qué puedes hacer?</h5>
                        <ul>
                            <li>Verificar que los datos de tu tarjeta sean correctos</li>
                            <li>Asegurarte de tener fondos suficientes</li>
                            <li>Intentar con otra tarjeta</li>
                            <li>Contactar a tu banco si el problema persiste</li>
                        </ul>
                    </div>

                    <div class="text-center mt-4">
                        <a href="{{ route('third-party.search') }}" class="btn btn-warning btn-lg">
                            <i class="fas fa-redo"></i> Intentar Nuevamente
                        </a>
                        <a href="{{ route('login') }}" class="btn btn-secondary btn-lg">
                            <i class="fas fa-home"></i> Ir al Login
                        </a>
                    </div>
                </div>
            </div>
        @endif
    </div>
@stop

@section('adminlte_js')
    <script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script>

    <style>
        .card {
            box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
            border-radius: 10px;
        }

        .info-box {
            min-height: 90px;
            border-radius: 10px;
        }

        .info-box-icon {
            border-radius: 10px 0 0 10px;
        }

        .callout {
            border-radius: 5px;
            border-left: 5px solid #17a2b8;
            padding: 15px;
            background-color: #d1ecf1;
        }

        .callout-info h5 {
            color: #0c5460;
        }

        .btn-lg {
            padding: 10px 30px;
            font-size: 1.1rem;
        }
    </style>
@stop
