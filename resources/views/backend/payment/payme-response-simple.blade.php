<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Resultado del Pago - BeeBus</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #ffc107 0%, #212529 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }

        .result-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.5);
            max-width: 800px;
            width: 100%;
            padding: 40px;
            border: 3px solid #ffc107;
        }

        .success-icon {
            font-size: 80px;
            color: #ffc107;
            animation: scaleIn 0.5s ease-in-out;
        }

        .error-icon {
            font-size: 80px;
            color: #dc3545;
            animation: scaleIn 0.5s ease-in-out;
        }

        @keyframes scaleIn {
            0% {
                transform: scale(0);
            }

            50% {
                transform: scale(1.2);
            }

            100% {
                transform: scale(1);
            }
        }

        .result-title {
            font-size: 28px;
            font-weight: bold;
            margin: 20px 0;
            color: #212529;
        }

        .result-title.text-success {
            color: #ffc107 !important;
        }

        .info-box {
            background: #fff8e1;
            border-radius: 10px;
            padding: 15px;
            margin: 10px 0;
            border: 2px solid #ffc107;
        }

        .info-label {
            font-weight: bold;
            color: #212529;
        }

        .info-value {
            font-size: 18px;
            color: #212529;
        }

        .info-value.text-success {
            color: #ffc107 !important;
            font-weight: bold;
        }

        .info-value.text-primary {
            color: #212529 !important;
        }

        .btn-custom {
            padding: 12px 30px;
            font-size: 16px;
            border-radius: 25px;
            margin: 10px 5px;
        }

        .btn-primary {
            background-color: #ffc107;
            border-color: #ffc107;
            color: #212529;
            font-weight: bold;
        }

        .btn-primary:hover {
            background-color: #e0a800;
            border-color: #d39e00;
            color: #212529;
        }

        .btn-secondary {
            background-color: #212529;
            border-color: #212529;
            color: #ffc107;
        }

        .btn-secondary:hover {
            background-color: #000;
            border-color: #000;
            color: #ffc107;
        }

        table.table-bordered {
            border-color: #ffc107;
        }

        table.table-bordered th {
            background-color: #ffc107;
            color: #212529;
            font-weight: bold;
        }

        table.table-bordered td,
        table.table-bordered th {
            border-color: #ffc107;
        }

        hr {
            border-color: #ffc107;
        }

        h5 {
            color: #212529;
            font-weight: bold;
        }

        .text-muted {
            color: #212529 !important;
        }

        .fa-lock {
            color: #ffc107;
        }
    </style>
</head>

<body>
    <div class="result-container">
        @if($success)
        <!-- Success Result -->
        <div class="text-center">
            <i class="fas fa-check-circle success-icon"></i>
            <h1 class="result-title text-success">¡Pago Exitoso!</h1>
            <p class="lead">{{ $message }}</p>
        </div>

        <hr class="my-4">

        <div class="row">
            <div class="col-md-6">
                <div class="info-box">
                    <div class="info-label">Estudiante</div>
                    <div class="info-value">{{ $data['student_name'] ?? 'N/A' }}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-box">
                    <div class="info-label">Créditos Agregados</div>
                    <div class="info-value text-success">{{ $data['formatted_amount'] ?? '₡0' }}</div>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="info-box">
                    <div class="info-label">Saldo Anterior</div>
                    <div class="info-value">₡{{ number_format($data['previous_balance'] ?? 0, 0) }}</div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="info-box">
                    <div class="info-label">Nuevo Saldo</div>
                    <div class="info-value text-primary"><strong>{{ $data['formatted_new_balance'] ?? '₡0' }}</strong></div>
                </div>
            </div>
        </div>

        <!-- Transaction Details -->
        <div class="mt-4">
            <h5><i class="fas fa-file-invoice"></i> Detalles de la Transacción</h5>
            <table class="table table-bordered">
                <tr>
                    <th style="width: 40%">Código de Autorización</th>
                    <td>{{ $data['authorization_code'] ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Número de Operación</th>
                    <td>{{ $data['operation_number'] ?? 'N/A' }}</td>
                </tr>
                <tr>
                    <th>Referencia de Pago</th>
                    <td>{{ $data['payment_reference'] ?? 'N/A' }}</td>
                </tr>
                @if(isset($data['brand']))
                <tr>
                    <th>Marca de Tarjeta</th>
                    <td>{{ $data['brand'] }}</td>
                </tr>
                @endif
                @if(isset($data['bin']))
                <tr>
                    <th>BIN</th>
                    <td>{{ $data['bin'] }}</td>
                </tr>
                @endif
            </table>
        </div>

        <div class="text-center mt-4">
            <a href="{{ route('login') }}" class="btn btn-primary btn-custom">
                <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
            </a>
            <button onclick="window.close()" class="btn btn-secondary btn-custom">
                <i class="fas fa-times"></i> Cerrar Ventana
            </button>
        </div>

        @else
        <!-- Error Result -->
        <div class="text-center">
            @if(isset($is_cancellation) && $is_cancellation)
            <i class="fas fa-ban error-icon" style="color: #ff9800;"></i>
            <h1 class="result-title" style="color: #ff9800;">Operación Cancelada</h1>
            @else
            <i class="fas fa-times-circle error-icon"></i>
            <h1 class="result-title text-danger">Error en el Pago</h1>
            @endif
            <p class="lead">{{ $message }}</p>
        </div>

        @if(!empty($data))
        <div class="alert {{ (isset($is_cancellation) && $is_cancellation) ? 'alert-info' : 'alert-warning' }} mt-4">
            <h5>
                <i class="fas {{ (isset($is_cancellation) && $is_cancellation) ? 'fa-info-circle' : 'fa-exclamation-triangle' }}"></i>
                Detalles {{ (isset($is_cancellation) && $is_cancellation) ? 'de la Operación' : 'del Error' }}
            </h5>
            <table class="table table-sm mb-0">
                @if(isset($data['error_code']))
                <tr>
                    <th style="width: 30%">Código:</th>
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

        @if(isset($is_cancellation) && $is_cancellation)
        <div class="alert alert-info mt-3">
            <h5><i class="fas fa-info-circle"></i> ¿Deseas reintentar?</h5>
            <p class="mb-0">No se realizó ningún cargo. Puedes cerrar esta ventana y volver a intentar el pago si lo deseas.</p>
        </div>
        @else
        <div class="alert alert-info mt-3">
            <h5><i class="fas fa-info-circle"></i> ¿Qué puedes hacer?</h5>
            <ul class="mb-0">
                <li>Verificar que los datos de tu tarjeta sean correctos</li>
                <li>Asegurarte de tener fondos suficientes</li>
                <li>Intentar con otra tarjeta</li>
                <li>Contactar a tu banco si el problema persiste</li>
            </ul>
        </div>
        @endif

        <div class="text-center mt-4">
            <a href="{{ route('third-party.search') }}" class="btn btn-primary btn-custom">
                <i class="fas fa-redo"></i> Intentar Nuevamente
            </a>
            <button onclick="window.close()" class="btn btn-secondary btn-custom">
                <i class="fas fa-times"></i> Cerrar Ventana
            </button>
        </div>
        @endif

        <div class="text-center mt-4">
            <small class="text-muted">
                <i class="fas fa-lock"></i> Transacción procesada de forma segura por PayMe/Alignet
            </small>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>