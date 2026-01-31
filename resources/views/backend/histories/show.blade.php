@extends('adminlte::page')

@section('title', $student->name . ' | ' . Config::get('adminlte.title'))

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1><i class="fas fa-user-graduate"></i> {{ $student->name }}</h1>
    <div>
        <a href="{{ route('histories.edit', $student->id) }}" class="btn btn-primary btn-sm">
            <i class="fas fa-edit"></i> Editar
        </a>
        <a href="{{ route('histories') }}" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>
</div>
@stop

@section('content')
@include('layouts.flash-message')

<div class="row">
    {{-- Info del Alumno --}}
    <div class="col-lg-4 col-md-6">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-id-card"></i> Datos Personales</h3>
            </div>
            <div class="card-body p-0">
                <table class="table table-striped mb-0">
                    <tr>
                        <th style="width:40%">Nombre</th>
                        <td>{{ $student->name }}</td>
                    </tr>
                    <tr>
                        <th>Cedula</th>
                        <td>{{ $student->cedula }}</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>{{ $student->email ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>Colegio</th>
                        <td>{{ $student->colegio_id && $student->getRelationValue('colegio') ? $student->getRelationValue('colegio')->nombre : ($student->getAttributeValue('colegio') ?: '-') }}</td>
                    </tr>
                    <tr>
                        <th>Seccion</th>
                        <td>{{ $student->seccion ?: '-' }}</td>
                    </tr>
                    <tr>
                        <th>Beca</th>
                        <td>{{ $student->beca ? $student->beca->nombre_beca : ($student->tipoBeca ?: 'Sin beca') }}</td>
                    </tr>
                    <tr>
                        <th>Ruta</th>
                        <td>{{ $student->ruta ? $student->ruta->key_app : '-' }}</td>
                    </tr>
                    <tr>
                        <th>Tarifa</th>
                        <td>
                            @if($student->tarifa)
                                {{ $student->tarifa->nombre }} - <strong>₡{{ number_format($student->tarifa->monto, 0, ',', '.') }}</strong>
                            @elseif($student->paradero && $student->paradero->monto > 0)
                                <strong>₡{{ number_format($student->paradero->monto, 0, ',', '.') }}</strong>
                                <small class="text-muted">(tarifa del paradero)</small>
                            @else
                                <span class="text-danger">Sin tarifa</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Estado</th>
                        <td>
                            @if($student->status == 1)
                                <span class="badge badge-success">Activo</span>
                            @elseif($student->status == 2)
                                <span class="badge badge-warning">Pendiente</span>
                            @else
                                <span class="badge badge-danger">Inactivo</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <th>Registro</th>
                        <td>{{ $student->created_at ? $student->created_at->format('d/m/Y H:i') : '-' }}</td>
                    </tr>
                </table>
            </div>
        </div>

        {{-- Contrato --}}
        <div class="card card-warning card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-file-contract"></i> Contrato</h3>
            </div>
            <div class="card-body">
                @if($student->contrato_subido && $student->contrato_url)
                    <span class="badge badge-success mb-2"><i class="fas fa-check"></i> Subido</span>
                    @if($student->contrato_fecha_subida)
                        <br><small class="text-muted">Fecha: {{ \Carbon\Carbon::parse($student->contrato_fecha_subida)->format('d/m/Y H:i') }}</small>
                    @endif
                    <div class="mt-2">
                        <a href="{{ route('histories.download-contract', $student->id) }}" class="btn btn-sm btn-primary" target="_blank">
                            <i class="fas fa-download"></i> Descargar
                        </a>
                        <a href="{{ asset($student->contrato_url) }}" class="btn btn-sm btn-info" target="_blank">
                            <i class="fas fa-eye"></i> Ver
                        </a>
                    </div>
                @else
                    <span class="badge badge-warning"><i class="fas fa-clock"></i> Pendiente</span>
                    <p class="text-muted small mt-1 mb-0">Aun no se ha subido el contrato.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Creditos, QR y Usuario --}}
    <div class="col-lg-4 col-md-6">
        {{-- Creditos --}}
        <div class="row">
            <div class="col-6">
                <div class="info-box">
                    <span class="info-box-icon bg-success"><i class="fas fa-coins"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Creditos</span>
                        <span class="info-box-number">₡{{ number_format($student->creditos, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="info-box">
                    <span class="info-box-icon bg-primary"><i class="fas fa-ticket-alt"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Chances</span>
                        <span class="info-box-number">{{ $student->chancesParaMarcar }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Estadisticas --}}
        <div class="row">
            <div class="col-6">
                <div class="info-box">
                    <span class="info-box-icon bg-info"><i class="fas fa-arrow-up"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Recargas</span>
                        <span class="info-box-number">{{ $stats['total_recargas'] }}</span>
                        <small class="text-muted">₡{{ number_format($stats['monto_recargas'], 0, ',', '.') }}</small>
                    </div>
                </div>
            </div>
            <div class="col-6">
                <div class="info-box">
                    <span class="info-box-icon bg-warning"><i class="fas fa-arrow-down"></i></span>
                    <div class="info-box-content">
                        <span class="info-box-text">Consumos</span>
                        <span class="info-box-number">{{ $stats['total_consumos'] }}</span>
                        <small class="text-muted">₡{{ number_format(abs($stats['monto_consumos']), 0, ',', '.') }}</small>
                    </div>
                </div>
            </div>
        </div>

        {{-- QR --}}
        <div class="card card-success card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-qrcode"></i> Codigo QR</h3>
            </div>
            <div class="card-body text-center">
                <div id="qr-code-container" class="mb-2"></div>
                <button class="btn btn-success btn-sm" id="download-qr">
                    <i class="fas fa-download"></i> Descargar QR
                </button>
            </div>
        </div>

        {{-- Usuario vinculado --}}
        <div class="card card-info card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-user"></i> Usuario Vinculado</h3>
            </div>
            <div class="card-body">
                @if($user)
                    <table class="table table-sm table-borderless mb-0">
                        <tr>
                            <th style="width:35%">Nombre</th>
                            <td>{{ $user->name }}</td>
                        </tr>
                        <tr>
                            <th>Email</th>
                            <td>{{ $user->email }}</td>
                        </tr>
                        <tr>
                            <th>Rol</th>
                            <td>
                                @if($user->roles->isNotEmpty())
                                    @foreach($user->roles as $role)
                                        <span class="badge badge-info">{{ $role->display_name ?? $role->name }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted">Sin rol</span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <th>Creado</th>
                            <td>{{ $user->created_at ? $user->created_at->format('d/m/Y H:i') : '-' }}</td>
                        </tr>
                    </table>
                @else
                    <p class="text-muted mb-0"><i class="fas fa-unlink"></i> Sin usuario vinculado.</p>
                @endif
            </div>
        </div>
    </div>

    {{-- Transacciones --}}
    <div class="col-lg-4 col-md-12">
        <div class="card card-outline card-dark">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-exchange-alt"></i> Ultimas Transacciones</h3>
                <div class="card-tools">
                    <a href="{{ route('transactions.index', ['search' => $student->name]) }}" class="btn btn-tool" title="Ver todas">
                        <i class="fas fa-external-link-alt"></i>
                    </a>
                </div>
            </div>
            <div class="card-body p-0">
                @if($transactions->isEmpty())
                    <div class="text-center py-4 text-muted">
                        <i class="fas fa-receipt" style="font-size: 2rem;"></i>
                        <p class="mt-2">Sin transacciones</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>Tipo</th>
                                    <th>Monto</th>
                                    <th>Saldo</th>
                                    <th>Fecha</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($transactions as $tx)
                                <tr>
                                    <td>
                                        @if($tx->type == 'recarga' || $tx->type == 'recarga_payme')
                                            <span class="badge badge-success"><i class="fas fa-arrow-up"></i> {{ ucfirst($tx->type) }}</span>
                                        @elseif($tx->type == 'consumo')
                                            <span class="badge badge-warning"><i class="fas fa-arrow-down"></i> Consumo</span>
                                        @else
                                            <span class="badge badge-secondary">{{ ucfirst($tx->type) }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <strong class="{{ $tx->amount >= 0 ? 'text-success' : 'text-danger' }}">
                                            {{ $tx->amount >= 0 ? '+' : '' }}₡{{ number_format($tx->amount, 0, ',', '.') }}
                                        </strong>
                                    </td>
                                    <td>₡{{ number_format($tx->balance_after, 0, ',', '.') }}</td>
                                    <td><small>{{ $tx->created_at->format('d/m/Y H:i') }}</small></td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
            @if($transactions->isNotEmpty())
            <div class="card-footer text-center">
                <a href="{{ route('transactions.index', ['search' => $student->name]) }}" class="text-sm">
                    Ver todas las transacciones <i class="fas fa-arrow-right"></i>
                </a>
            </div>
            @endif
        </div>
    </div>
</div>

@stop

@section('css')
<style>
    .info-box {
        min-height: 80px;
    }
    .info-box-icon {
        width: 60px;
    }
    .info-box-content {
        padding: 8px 10px;
    }
    #qr-code-container {
        display: inline-block;
    }
    #qr-code-container img,
    #qr-code-container canvas {
        max-width: 180px;
    }
</style>
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var container = document.getElementById('qr-code-container');
    if (container) {
        new QRCode(container, {
            text: '{{ $qrData }}',
            width: 180,
            height: 180,
            colorDark: '#000000',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.M
        });
    }

    document.getElementById('download-qr').addEventListener('click', function() {
        var canvas = container.querySelector('canvas');
        var img = container.querySelector('img');
        var link = document.createElement('a');
        link.download = 'QR_{{ preg_replace("/[^a-zA-Z0-9]/", "_", $student->name) }}.png';
        if (canvas) {
            link.href = canvas.toDataURL('image/png');
        } else if (img) {
            link.href = img.src;
        }
        link.click();
    });
});
</script>
@stop
