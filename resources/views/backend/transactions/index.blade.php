@extends('adminlte::page')

@section('title', 'Transacciones de Crédito | ' . Config::get('adminlte.title'))

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1>
        <i class="fas fa-credit-card"></i> Transacciones de Crédito
    </h1>
    <div class="btn-group">
        <a href="{{ route('transactions.export', request()->query()) }}" class="btn btn-success">
            <i class="fas fa-download"></i> Exportar CSV
        </a>
    </div>
</div>
@stop

@section('content')
<!-- Estadísticas -->
<div class="row mb-4">
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ number_format($stats['total_transactions']) }}</h3>
                <p>Total Transacciones</p>
            </div>
            <div class="icon">
                <i class="fas fa-list"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ number_format($stats['pending_verifications']) }}</h3>
                <p>Pendientes Verificación</p>
            </div>
            <div class="icon">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>₡{{ number_format($stats['today_amount']) }}</h3>
                <p>Total Hoy ({{ $stats['today_transactions'] }} trans.)</p>
            </div>
            <div class="icon">
                <i class="fas fa-calendar-day"></i>
            </div>
        </div>
    </div>
    <div class="col-lg-3 col-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>₡{{ number_format($stats['month_amount']) }}</h3>
                <p>Total Mes ({{ $stats['month_transactions'] }} trans.)</p>
            </div>
            <div class="icon">
                <i class="fas fa-calendar-alt"></i>
            </div>
        </div>
    </div>
</div>

<!-- Resumen Recargas vs Consumos -->
<div class="row mb-4">
    <div class="col-md-6">
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-plus-circle"></i> Total Recargas
                </h3>
            </div>
            <div class="card-body">
                <h2 class="text-success">₡{{ number_format($stats['total_recharges']) }}</h2>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card card-danger">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-minus-circle"></i> Total Consumos
                </h3>
            </div>
            <div class="card-body">
                <h2 class="text-danger">₡{{ number_format($stats['total_consumptions']) }}</h2>
            </div>
        </div>
    </div>
</div>

<!-- Filtros -->
<div class="card card-secondary collapsed-card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-filter"></i> Filtros
        </h3>
        <div class="card-tools">
            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                <i class="fas fa-plus"></i>
            </button>
        </div>
    </div>
    <div class="card-body" style="display: none;">
        <form method="GET" action="{{ route('transactions.index') }}">
            <div class="row">
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="type">Tipo</label>
                        <select name="type" id="type" class="form-control">
                            <option value="">Todos</option>
                            <option value="recarga" {{ request('type') == 'recarga' ? 'selected' : '' }}>Recarga</option>
                            <option value="consumo" {{ request('type') == 'consumo' ? 'selected' : '' }}>Consumo</option>
                            <option value="chance_debt" {{ request('type') == 'chance_debt' ? 'selected' : '' }}>Deuda por Oportunidad</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="verification_status">Estado</label>
                        <select name="verification_status" id="verification_status" class="form-control">
                            <option value="">Todos</option>
                            <option value="pending" {{ request('verification_status') == 'pending' ? 'selected' : '' }}>Pendiente</option>
                            <option value="verified" {{ request('verification_status') == 'verified' ? 'selected' : '' }}>Verificado</option>
                            <option value="rejected" {{ request('verification_status') == 'rejected' ? 'selected' : '' }}>Rechazado</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="payment_method">Método Pago</label>
                        <select name="payment_method" id="payment_method" class="form-control">
                            <option value="">Todos</option>
                            <option value="stripe" {{ request('payment_method') == 'stripe' ? 'selected' : '' }}>Stripe</option>
                            <option value="bank_transfer" {{ request('payment_method') == 'bank_transfer' ? 'selected' : '' }}>Transferencia</option>
                            <option value="cash" {{ request('payment_method') == 'cash' ? 'selected' : '' }}>Efectivo</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="date_from">Fecha Desde</label>
                        <input type="date" name="date_from" id="date_from" class="form-control" value="{{ request('date_from') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="date_to">Fecha Hasta</label>
                        <input type="date" name="date_to" id="date_to" class="form-control" value="{{ request('date_to') }}">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label for="search">Buscar Estudiante</label>
                        <input type="text" name="search" id="search" class="form-control" placeholder="Nombre o cédula" value="{{ request('search') }}">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                    <a href="{{ route('transactions.index') }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Limpiar
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tabla de Transacciones -->
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-list"></i> Lista de Transacciones ({{ $transactions->total() }} encontradas)
        </h3>
    </div>
    <div class="card-body">
        @if($transactions->count() > 0)
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Estudiante</th>
                        <th>Tipo</th>
                        <th>Monto</th>
                        <th>Método</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($transactions as $transaction)
                    <tr>
                        <td>{{ $transaction->id }}</td>
                        <td>
                            @if($transaction->history)
                                <strong>{{ $transaction->history->name }}</strong><br>
                                <small class="text-muted">{{ $transaction->history->cedula ?? 'Sin cédula' }}</small>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>
                            @if($transaction->type == 'recarga')
                                <span class="badge badge-success">
                                    <i class="fas fa-plus"></i> Recarga
                                </span>
                            @elseif($transaction->type == 'consumo')
                                <span class="badge badge-danger">
                                    <i class="fas fa-minus"></i> Consumo
                                </span>
                            @elseif($transaction->type == 'chance_debt')
                                <span class="badge badge-warning">
                                    <i class="fas fa-exclamation"></i> Deuda
                                </span>
                            @endif
                        </td>
                        <td>
                            <strong>₡{{ number_format($transaction->amount) }}</strong><br>
                            <small class="text-muted">
                                Antes: ₡{{ number_format($transaction->balance_before) }}<br>
                                Después: ₡{{ number_format($transaction->balance_after) }}
                            </small>
                        </td>
                        <td>
                            @if($transaction->payment_method)
                                <span class="badge badge-info">{{ ucfirst($transaction->payment_method) }}</span>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </td>
                        <td>
                            @if($transaction->verification_status == 'pending')
                                <span class="badge badge-warning">
                                    <i class="fas fa-clock"></i> Pendiente
                                </span>
                            @elseif($transaction->verification_status == 'verified')
                                <span class="badge badge-success">
                                    <i class="fas fa-check"></i> Verificado
                                </span>
                            @elseif($transaction->verification_status == 'rejected')
                                <span class="badge badge-danger">
                                    <i class="fas fa-times"></i> Rechazado
                                </span>
                            @else
                                <span class="badge badge-secondary">Sin estado</span>
                            @endif
                        </td>
                        <td>
                            <small>{{ $transaction->created_at->format('d/m/Y H:i') }}</small>
                        </td>
                        <td>
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('transactions.show', $transaction->id) }}" class="btn btn-info btn-sm" title="Ver Detalles">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if($transaction->verification_status == 'pending')
                                <button type="button" class="btn btn-success btn-sm" title="Verificar" onclick="verifyTransaction({{ $transaction->id }})">
                                    <i class="fas fa-check"></i>
                                </button>
                                <button type="button" class="btn btn-danger btn-sm" title="Rechazar" onclick="rejectTransaction({{ $transaction->id }})">
                                    <i class="fas fa-times"></i>
                                </button>
                                @endif
                            </div>
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
        @else
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i>
            <strong>No se encontraron transacciones</strong> que coincidan con los filtros aplicados.
        </div>
        @endif
    </div>
</div>

<!-- Modal para Verificar Transacción -->
<div class="modal fade" id="verifyModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Verificar Transacción</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="verifyForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="form-group">
                        <label for="admin_notes_verify">Notas del Administrador (Opcional)</label>
                        <textarea name="admin_notes" id="admin_notes_verify" class="form-control" rows="3" placeholder="Añadir notas sobre la verificación..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success">Verificar Transacción</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal para Rechazar Transacción -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Rechazar Transacción</h4>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <form id="rejectForm" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="form-group">
                        <label for="admin_notes_reject">Motivo del Rechazo *</label>
                        <textarea name="admin_notes" id="admin_notes_reject" class="form-control" rows="3" placeholder="Especificar el motivo del rechazo..." required></textarea>
                    </div>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Atención:</strong> Si esta transacción ya aplicó créditos al estudiante, se revertirán automáticamente.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Rechazar Transacción</button>
                </div>
            </form>
        </div>
    </div>
</div>
@stop

@section('css')
<style>
    .small-box {
        border-radius: 10px;
    }
    
    .card {
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        border-radius: 10px;
    }
    
    .table th {
        background-color: #f8f9fa;
        border-top: none;
    }
    
    .badge {
        font-size: 0.875em;
    }
    
    .btn-group .btn {
        margin-right: 2px;
    }
</style>
@stop

@section('js')
<script>
    function verifyTransaction(id) {
        $('#verifyForm').attr('action', '{{ route("transactions.verify", ":id") }}'.replace(':id', id));
        $('#verifyModal').modal('show');
    }
    
    function rejectTransaction(id) {
        $('#rejectForm').attr('action', '{{ route("transactions.reject", ":id") }}'.replace(':id', id));
        $('#rejectModal').modal('show');
    }
    
    $(document).ready(function() {
        // Initialize tooltips
        $('[title]').tooltip();
        
        // Auto-expand filters if any filter is applied
        @if(request()->hasAny(['type', 'verification_status', 'payment_method', 'date_from', 'date_to', 'search']))
            $('.collapsed-card .card-body').show();
            $('.collapsed-card .btn-tool i').removeClass('fa-plus').addClass('fa-minus');
            $('.collapsed-card').removeClass('collapsed-card');
        @endif
    });
</script>
@stop