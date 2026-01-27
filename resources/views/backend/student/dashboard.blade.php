@extends('adminlte::page')

@section('title', 'Mi Dashboard - Estudiante')

@section('content_header')
    <h1>Mi Dashboard</h1>
@stop

@section('content')
    @include('layouts.flash-message')

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
                    <!-- Botón para descargar el contrato en blanco -->
                    <div class="mb-3">
                        <a href="{{ route('student.download-contract-template') }}" class="btn btn-primary btn-block">
                            <i class="fa fa-file-download"></i> Descargar Contrato en Blanco
                        </a>
                        <small class="text-muted d-block mt-2">
                            <i class="fa fa-info-circle"></i> Descarga, imprime, firma y sube el contrato escaneado
                        </small>
                    </div>

                    <hr>

                    @if($student->contrato_subido)
                        <div class="alert alert-success">
                            <i class="fa fa-check-circle"></i> Contrato subido exitosamente
                            <br>
                            <small>Fecha: {{ $student->contrato_fecha_subida ? $student->contrato_fecha_subida->format('d/m/Y H:i') : '' }}</small>
                        </div>
                        <a href="{{ route('student.download-contract') }}" class="btn btn-info btn-block mb-2">
                            <i class="fa fa-download"></i> Descargar Mi Contrato
                        </a>
                        <p class="text-muted text-center"><small>¿Necesitas subir uno nuevo?</small></p>
                    @else
                        <div class="alert alert-warning">
                            <i class="fa fa-exclamation-triangle"></i> Aún no has subido tu contrato firmado
                        </div>
                    @endif

                    <form id="upload-contract-form" action="{{ route('student.upload-contract') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="form-group">
                            <label for="contract_file">Subir Contrato Firmado (PDF)</label>
                            <input type="file" name="contract_file" id="contract_file" class="form-control-file" accept=".pdf" required>
                            <small class="form-text text-muted">Tamaño máximo: 5MB</small>
                        </div>
                        <button type="submit" class="btn btn-warning btn-block">
                            <i class="fa fa-upload"></i> {{ $student->contrato_subido ? 'Actualizar' : 'Subir' }} Contrato Firmado
                        </button>
                    </form>
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
