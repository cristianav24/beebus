@extends('adminlte::page')

@section('title', 'Mi Dashboard - Estudiante')

@section('content_header')
    <h1>Mi Dashboard</h1>
@stop

@section('content')
    @include('layouts.flash-message')

    @if($isPending)
    <div class="alert alert-warning alert-dismissible">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <h5><i class="icon fas fa-exclamation-triangle"></i> Cuenta pendiente de verificacion</h5>
        Tu cuenta aun no ha sido verificada por el administrador. Hasta que sea activada, <strong>no podras utilizar el servicio de transporte</strong> ya que tu codigo QR no esta habilitado.
        <br><br>
        <i class="fas fa-file-contract"></i> <strong>Importante:</strong> Para completar la verificacion es necesario que descargues, firmes y subas tu contrato. Sin el contrato firmado, tu cuenta no podra ser activada.
        <br>Si ya subiste tu contrato y paso tiempo sin ser activada, comunicate con la administracion de BeeBus.
    </div>
    @endif

    <div class="row">
        <!-- Tarjeta de Perfil -->
        <div class="col-md-8">
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fa fa-user"></i> Mi Información</h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <p><strong>Nombre Completo:</strong> {{ $student->full_name }}</p>
                            <p><strong>Cédula:</strong> {{ $student->cedula }}</p>
                            <p><strong>Email:</strong> {{ $student->email }}</p>
                            <p><strong>Colegio:</strong>
                                @if($student->colegio_id && $student->colegio)
                                    {{ $student->colegio }}
                                @elseif($student->colegio)
                                    {{ $student->colegio }}
                                @else
                                    Sin asignar
                                @endif
                            </p>
                        </div>
                        <div class="col-md-6">
                            <p><strong>Sección:</strong> {{ $student->seccion ?? '-' }}</p>
                            <p><strong>Ruta:</strong>
                                @if($student->ruta_id && $student->ruta)
                                    {{ $student->ruta->key_app }}
                                @elseif($student->rutaBus)
                                    {{ $student->rutaBus }}
                                @else
                                    Sin asignar
                                @endif
                            </p>
                            <p><strong>Créditos Disponibles:</strong>
                                <span class="badge badge-{{ $student->creditos >= 0 ? 'success' : 'danger' }} badge-lg">
                                    ₡{{ number_format($student->creditos, 0, ',', '.') }}
                                </span>
                            </p>
                            <p><strong>Tarifa:</strong>
                                @if($student->tarifa_id && $student->tarifa)
                                    {{ $student->tarifa->nombre }} - ₡{{ number_format($student->tarifa->monto, 0, ',', '.') }}
                                @elseif($student->paradero_id && $student->paradero && $student->paradero->monto > 0)
                                    ₡{{ number_format($student->paradero->monto, 0, ',', '.') }}
                                    <small class="text-muted">(tarifa del paradero)</small>
                                @else
                                    Sin tarifa asignada
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estadísticas -->
            <div class="row">
                <div class="col-md-4">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ $statistics['total_recharges'] }}</h3>
                            <p>Recargas</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-plus-circle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ $statistics['total_consumptions'] }}</h3>
                            <p>Consumos</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-minus-circle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ $statistics['total_attendances'] }}</h3>
                            <p>Asistencias</p>
                        </div>
                        <div class="icon">
                            <i class="fa fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjeta de QR y Contrato -->
        <div class="col-md-4">
            <!-- QR Code -->
            <div class="card card-success">
                <div class="card-header">
                    <h3 class="card-title"><i class="fa fa-qrcode"></i> Mi Código QR</h3>
                </div>
                <div class="card-body text-center">
                    <div id="qr-container" style="padding: 20px;">
                        <canvas id="qr-canvas"></canvas>
                    </div>
                    <button class="btn btn-success btn-block" id="download-qr">
                        <i class="fa fa-download"></i> Descargar QR
                    </button>
                </div>
            </div>

            <!-- Contrato -->
            <div class="card card-warning">
                <div class="card-header">
                    <h3 class="card-title"><i class="fa fa-file-pdf"></i> Contrato Bee Bus 2026</h3>
                </div>
                <div class="card-body">
                    @if($student->contrato_subido)
                        <div class="alert alert-success">
                            <i class="fa fa-check-circle"></i> <strong>Contrato firmado</strong>
                            <br>
                            <small>Fecha: {{ $student->contrato_fecha_subida ? $student->contrato_fecha_subida->format('d/m/Y H:i') : '' }}</small>
                        </div>
                        <a href="{{ route('student.download-contract') }}" class="btn btn-info btn-block mb-2">
                            <i class="fa fa-download"></i> Descargar Mi Contrato
                        </a>
                    @else
                        <div class="alert alert-warning">
                            <i class="fa fa-exclamation-triangle"></i> <strong>Contrato pendiente</strong>
                            <br>
                            <small>Debes firmar el contrato para activar tu cuenta</small>
                        </div>

                        <!-- OPCION 1: Firma Digital (Recomendado) -->
                        <div class="mb-3">
                            <a href="{{ route('student.sign-contract') }}" class="btn btn-success btn-block btn-lg">
                                <i class="fa fa-pen"></i> Firmar Digitalmente
                            </a>
                            <small class="text-muted d-block mt-2 text-center">
                                <i class="fa fa-star text-warning"></i> <strong>Recomendado:</strong> Firma desde tu dispositivo
                            </small>
                        </div>

                        <hr>
                        <p class="text-center text-muted"><small>O si prefieres el metodo tradicional:</small></p>

                        <!-- OPCION 2: Descargar, imprimir, firmar y subir -->
                        <div class="mb-3">
                            <a href="{{ route('student.download-contract-template') }}" class="btn btn-outline-primary btn-block btn-sm">
                                <i class="fa fa-file-download"></i> Descargar Contrato en Blanco
                            </a>
                        </div>

                        <form id="upload-contract-form" action="{{ route('student.upload-contract') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="form-group">
                                <label for="contract_file"><small>Subir Contrato Escaneado (PDF)</small></label>
                                <input type="file" name="contract_file" id="contract_file" class="form-control-file" accept=".pdf" required>
                                <small class="form-text text-muted">Tamaño maximo: 5MB</small>
                            </div>
                            <button type="submit" class="btn btn-outline-warning btn-block btn-sm">
                                <i class="fa fa-upload"></i> Subir Contrato Escaneado
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Últimas Transacciones -->
    @if($recentTransactions->count() > 0)
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fa fa-history"></i> Últimas Transacciones</h3>
        </div>
        <div class="card-body">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Monto</th>
                        <th>Saldo Anterior</th>
                        <th>Saldo Final</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($recentTransactions as $transaction)
                    <tr>
                        <td>{{ $transaction->created_at->format('d/m/Y H:i') }}</td>
                        <td>
                            <span class="badge badge-{{ $transaction->type == 'recarga' ? 'success' : 'warning' }}">
                                {{ ucfirst($transaction->type) }}
                            </span>
                        </td>
                        <td>
                            <span class="text-{{ $transaction->type == 'recarga' ? 'success' : 'danger' }}">
                                {{ $transaction->type == 'recarga' ? '+' : '-' }}₡{{ number_format($transaction->amount, 0, ',', '.') }}
                            </span>
                        </td>
                        <td>₡{{ number_format($transaction->balance_before, 0, ',', '.') }}</td>
                        <td>₡{{ number_format($transaction->balance_after, 0, ',', '.') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
@stop

@section('css')
    <style>
        .badge-lg {
            font-size: 1.2em;
            padding: 8px 12px;
        }
        #qr-canvas {
            max-width: 100%;
            height: auto;
        }
    </style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/qrcode-generator@1.4.4/qrcode.min.js"></script>
<script>
    $(document).ready(function() {
        // Generar QR
        var qrData = '{{ $qrData }}';
        var qr = qrcode(0, 'M');
        qr.addData(qrData);
        qr.make();

        // Crear canvas y dibujar QR
        var canvas = document.getElementById('qr-canvas');
        var size = 250;
        canvas.width = size;
        canvas.height = size;
        var ctx = canvas.getContext('2d');

        var cellSize = size / qr.getModuleCount();
        for (var row = 0; row < qr.getModuleCount(); row++) {
            for (var col = 0; col < qr.getModuleCount(); col++) {
                ctx.fillStyle = qr.isDark(row, col) ? '#000000' : '#ffffff';
                ctx.fillRect(col * cellSize, row * cellSize, cellSize, cellSize);
            }
        }

        // Descargar QR
        $('#download-qr').click(function() {
            var link = document.createElement('a');
            link.href = canvas.toDataURL('image/png');
            link.download = 'mi_qr_beebus.png';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    });
</script>
@stop
