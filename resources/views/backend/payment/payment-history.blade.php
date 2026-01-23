@extends('adminlte::page')

@section('title', 'Historial de Pagos - ' . Config::get('adminlte.title'))

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1>
        <i class="fas fa-history"></i> Historial de Pagos con Tarjeta
    </h1>
    <div>
        <a href="{{ route('parent.recharge-credits') }}" class="btn btn-success">
            <i class="fas fa-credit-card"></i> Nueva Recarga
        </a>
        <a href="{{ route('parent.dashboard') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver al Dashboard
        </a>
    </div>
</div>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-list"></i> Transacciones con Tarjeta
        </h3>
    </div>
    <div class="card-body">
        @if($transactions->count() > 0)
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Estudiante</th>
                        <th>Monto</th>
                        <th>Saldo Anterior</th>
                        <th>Saldo Nuevo</th>
                        <th>Estado</th>
                        <th>ID Pago</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $transaction)
                    <tr>
                        <td>
                            <small class="text-muted">
                                {{ $transaction->created_at->format('d/m/Y') }}<br>
                                {{ $transaction->created_at->format('H:i') }}
                            </small>
                        </td>
                        <td>
                            <strong>{{ $transaction->history->name }}</strong>
                            @if($transaction->history->colegio)
                            <br><small class="text-muted">{{ $transaction->history->colegio->nombre ?? $transaction->history->colegio }}</small>
                            @endif
                        </td>
                        <td>
                            <span class="badge badge-success badge-lg">
                                +₡{{ number_format($transaction->amount, 0) }}
                            </span>
                        </td>
                        <td>₡{{ number_format($transaction->balance_before, 0) }}</td>
                        <td>
                            <strong class="text-success">₡{{ number_format($transaction->balance_after, 0) }}</strong>
                        </td>
                        <td>
                            <span class="badge badge-success">
                                <i class="fas fa-check-circle"></i> Exitoso
                            </span>
                        </td>
                        <td>
                            <small class="font-monospace">
                                {{ substr($transaction->stripe_payment_intent_id, -8) }}
                            </small>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="d-flex justify-content-center">
            {{ $transactions->links() }}
        </div>

        <!-- Summary Card -->
        <div class="card card-info mt-4">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-chart-pie"></i> Resumen de Pagos
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="text-center">
                            <h4 class="text-primary">{{ $transactions->total() }}</h4>
                            <p class="text-muted">Total Transacciones</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <h4 class="text-success">₡{{ number_format($transactions->sum('amount'), 0) }}</h4>
                            <p class="text-muted">Total Recargado</p>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="text-center">
                            <h4 class="text-info">₡{{ $transactions->count() > 0 ? number_format($transactions->sum('amount') / $transactions->count(), 0) : 0 }}</h4>
                            <p class="text-muted">Promedio por Recarga</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="alert alert-info text-center">
            <i class="fas fa-info-circle fa-3x mb-3"></i>
            <h4>No hay transacciones registradas</h4>
            <p class="mb-4">Aún no has realizado ninguna recarga con tarjeta.</p>
            <a href="{{ route('parent.recharge-credits') }}" class="btn btn-success">
                <i class="fas fa-credit-card"></i> Realizar Primera Recarga
            </a>
        </div>
        @endif
    </div>
</div>

<!-- Transaction Details Modal -->
<div class="modal fade" id="transactionModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info">
                <h4 class="modal-title">
                    <i class="fas fa-receipt"></i> Detalles de la Transacción
                </h4>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="transaction-details">
                <!-- Transaction details will be loaded here -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>
@stop

@section('css')
<!-- Toastr CSS -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.css">

<style>
    .card {
        box-shadow: 0 0 1px rgba(0, 0, 0, .125), 0 1px 3px rgba(0, 0, 0, .2);
        border-radius: 10px;
    }

    .badge-lg {
        font-size: 0.95em;
        padding: 0.5em 0.75em;
    }

    .font-monospace {
        font-family: 'Courier New', Courier, monospace;
    }

    .table th {
        background-color: #f8f9fa;
        border-top: none;
    }

    .alert-info {
        background-color: #d1ecf1;
        border-color: #bee5eb;
        color: #0c5460;
    }
</style>
@stop

@section('js')
<!-- Toastr JS -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<script>
    $(document).ready(function() {
        // Initialize tooltips
        $('[title]').tooltip();

        // Handle transaction row clicks for details
        $('tbody tr').click(function() {
            // You can add modal functionality here if needed
            // For now, just highlight the row
            $('tbody tr').removeClass('table-active');
            $(this).addClass('table-active');
        });
    });
</script>
@stop