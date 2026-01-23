@extends('adminlte::page')

@section('title', 'Transacciones de ' . $student->name . ' - ' . Config::get('adminlte.title'))

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1>Transacciones de {{ $student->name }}</h1>
    <a href="{{ route('parent.dashboard') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Volver al Dashboard
    </a>
</div>
@stop

@section('content')
@include('layouts.flash-message')

<!-- Student Info Card -->
<div class="row mb-4">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user-graduate"></i> Información del Estudiante
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Nombre:</strong></td>
                                <td>{{ $student->name }}</td>
                            </tr>
                            <tr>
                                <td><strong>Cédula:</strong></td>
                                <td>{{ $student->cedula }}</td>
                            </tr>
                            <tr>
                                <td><strong>Colegio:</strong></td>
                                <td>{{ $student->colegio ? $student->colegio : ($student->colegio ?: 'No asignado') }}</td>
                            </tr>
                            <tr>
                                <td><strong>Sección:</strong></td>
                                <td>{{ $student->seccion ?: 'No asignada' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-borderless">
                            <tr>
                                <td><strong>Tipo de Beca:</strong></td>
                                <td>
                                    @if($student->beca)
                                    <span class="badge badge-success">{{ $student->beca->nombre_beca }}</span>
                                    @else
                                    <span class="badge badge-warning">{{ $student->tipoBeca ?: 'Sin beca' }}</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <td><strong>Créditos Actuales:</strong></td>
                                <td><span class="badge badge-lg badge-success">₡{{ number_format($student->creditos, 0, ',', '.') }}</span></td>
                            </tr>
                            <tr>
                                <td><strong>Chances Restantes:</strong></td>
                                <td><span class="badge badge-lg badge-info">{{ $student->chancesParaMarcar }}</span></td>
                            </tr>
                            <tr>
                                <td><strong>Ruta:</strong></td>
                                <td>{{ $student->ruta ? $student->ruta->key_app : 'No asignada' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Transactions Statistics -->
<div class="row mb-4">
    <div class="col-md-4">
        <div class="info-box">
            <span class="info-box-icon bg-success">
                <i class="fas fa-arrow-up"></i>
            </span>
            <div class="info-box-content">
                <span class="info-box-text">Total Recargas</span>
                <span class="info-box-number">
                    {{ $transactions->where('type', 'recarga')->count() }}
                </span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box">
            <span class="info-box-icon bg-warning">
                <i class="fas fa-arrow-down"></i>
            </span>
            <div class="info-box-content">
                <span class="info-box-text">Total Consumos</span>
                <span class="info-box-number">
                    {{ $transactions->where('type', 'consumo')->count() }}
                </span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box">
            <span class="info-box-icon bg-info">
                <i class="fas fa-exchange-alt"></i>
            </span>
            <div class="info-box-content">
                <span class="info-box-text">Total Transacciones</span>
                <span class="info-box-number">{{ $transactions->total() }}</span>
            </div>
        </div>
    </div>
</div>

<!-- Transactions History -->
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-history"></i> Historial de Transacciones
                </h3>
            </div>
            <div class="card-body">
                @if($transactions->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped table-hover">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Monto</th>
                                <th>Saldo Anterior</th>
                                <th>Saldo Posterior</th>
                                <th>Descripción</th>
                                <th>Procesado Por</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($transactions as $transaction)
                            <tr>
                                <td>
                                    <span class="d-block">{{ $transaction->created_at->format('d/m/Y') }}</span>
                                    <small class="text-muted">{{ $transaction->created_at->format('H:i:s') }}</small>
                                </td>
                                <td>
                                    @if($transaction->type === 'recarga')
                                    <span class="badge badge-success">
                                        <i class="fas fa-arrow-up"></i> Recarga
                                    </span>
                                    @elseif($transaction->type === 'consumo')
                                    <span class="badge badge-warning">
                                        <i class="fas fa-arrow-down"></i> Consumo
                                    </span>
                                    @elseif($transaction->type === 'chance_debt')
                                    <span class="badge badge-danger">
                                        <i class="fas fa-exclamation-triangle"></i> Deuda
                                    </span>
                                    @endif
                                </td>
                                <td>
                                    @if($transaction->amount > 0)
                                    <span class="text-success font-weight-bold">
                                        +₡{{ number_format($transaction->amount, 0, ',', '.') }}
                                    </span>
                                    @else
                                    <span class="text-danger font-weight-bold">
                                        ₡{{ number_format($transaction->amount, 0, ',', '.') }}
                                    </span>
                                    @endif
                                </td>
                                <td>₡{{ number_format($transaction->balance_before, 0, ',', '.') }}</td>
                                <td>
                                    <strong class="{{ $transaction->balance_after >= 0 ? 'text-success' : 'text-danger' }}">
                                        ₡{{ number_format($transaction->balance_after, 0, ',', '.') }}
                                    </strong>
                                </td>
                                <td>
                                    <span class="d-block">{{ $transaction->description }}</span>
                                    @if($transaction->payment_method)
                                    <small class="text-muted">
                                        <i class="fas fa-credit-card"></i> {{ ucfirst($transaction->payment_method) }}
                                        @if($transaction->payment_reference)
                                        - Ref: {{ $transaction->payment_reference }}
                                        @endif
                                    </small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge badge-secondary">{{ $transaction->processed_by }}</span>
                                    @if($transaction->verification_status && $transaction->type === 'recarga')
                                    <br>
                                    <small class="text-muted">
                                        Estado:
                                        @if($transaction->verification_status === 'verified')
                                        <span class="badge badge-success">Verificado</span>
                                        @elseif($transaction->verification_status === 'pending')
                                        <span class="badge badge-warning">Pendiente</span>
                                        @else
                                        <span class="badge badge-danger">Rechazado</span>
                                        @endif
                                    </small>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div class="d-flex justify-content-center mt-4">
                    {{ $transactions->links() }}
                </div>
                @else
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                    <h4 class="text-muted">No hay transacciones registradas</h4>
                    <p class="text-muted">Este estudiante aún no tiene historial de transacciones.</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

@stop

@section('css')
<style>
    .info-box {
        box-shadow: 0 0 1px rgba(0, 0, 0, .125), 0 1px 3px rgba(0, 0, 0, .2);
        border-radius: .25rem;
        background-color: #fff;
        display: flex;
        margin-bottom: 1rem;
        min-height: 80px;
        padding: 0;
        position: relative;
        width: 100%;
    }

    .info-box .info-box-icon {
        border-radius: .25rem 0 0 .25rem;
        align-items: center;
        display: flex;
        font-size: 1.875rem;
        justify-content: center;
        text-align: center;
        width: 90px;
    }

    .info-box .info-box-content {
        display: flex;
        flex-direction: column;
        justify-content: center;
        line-height: 1.8;
        margin-left: 90px;
        padding: 5px 10px;
    }

    .badge-lg {
        font-size: 1rem;
        padding: .5rem .75rem;
    }

    .table th {
        border-top: none;
        font-weight: 600;
    }
</style>
@stop

@section('js')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Add hover effects to transaction rows
        const rows = document.querySelectorAll('tbody tr');
        rows.forEach(row => {
            row.addEventListener('mouseenter', function() {
                this.style.backgroundColor = '#f8f9fa';
            });
            row.addEventListener('mouseleave', function() {
                this.style.backgroundColor = '';
            });
        });
    });
</script>
@stop