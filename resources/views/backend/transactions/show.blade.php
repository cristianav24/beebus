@extends('adminlte::page')

@section('title', 'Detalle de Transacción #' . $transaction->id . ' | ' . Config::get('adminlte.title'))

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1>
        <i class="fas fa-credit-card"></i> Detalle de Transacción #{{ $transaction->id }}
    </h1>
    <a href="{{ route('transactions.index') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Volver a Transacciones
    </a>
</div>
@stop

@section('content')
<div class="row">
    <!-- Información Principal -->
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle"></i> Información de la Transacción
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <table class="table table-striped">
                            <tr>
                                <th width="40%">ID Transacción:</th>
                                <td>{{ $transaction->id }}</td>
                            </tr>
                            <tr>
                                <th>Tipo:</th>
                                <td>
                                    @if($transaction->type == 'recarga')
                                        <span class="badge badge-success badge-lg">
                                            <i class="fas fa-plus"></i> Recarga de Créditos
                                        </span>
                                    @elseif($transaction->type == 'consumo')
                                        <span class="badge badge-danger badge-lg">
                                            <i class="fas fa-minus"></i> Consumo de Créditos
                                        </span>
                                    @elseif($transaction->type == 'chance_debt')
                                        <span class="badge badge-warning badge-lg">
                                            <i class="fas fa-exclamation"></i> Deuda por Oportunidad
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Monto:</th>
                                <td>
                                    <h4 class="text-{{ $transaction->type == 'recarga' ? 'success' : 'danger' }}">
                                        ₡{{ number_format($transaction->amount) }}
                                    </h4>
                                </td>
                            </tr>
                            <tr>
                                <th>Saldo Anterior:</th>
                                <td>₡{{ number_format($transaction->balance_before) }}</td>
                            </tr>
                            <tr>
                                <th>Saldo Posterior:</th>
                                <td>₡{{ number_format($transaction->balance_after) }}</td>
                            </tr>
                            <tr>
                                <th>Descripción:</th>
                                <td>{{ $transaction->description ?? 'Sin descripción' }}</td>
                            </tr>
                        </table>
                    </div>
                    <div class="col-md-6">
                        <table class="table table-striped">
                            <tr>
                                <th width="40%">Estado de Verificación:</th>
                                <td>
                                    @if($transaction->verification_status == 'pending')
                                        <span class="badge badge-warning badge-lg">
                                            <i class="fas fa-clock"></i> Pendiente
                                        </span>
                                    @elseif($transaction->verification_status == 'verified')
                                        <span class="badge badge-success badge-lg">
                                            <i class="fas fa-check"></i> Verificado
                                        </span>
                                    @elseif($transaction->verification_status == 'rejected')
                                        <span class="badge badge-danger badge-lg">
                                            <i class="fas fa-times"></i> Rechazado
                                        </span>
                                    @else
                                        <span class="badge badge-secondary badge-lg">Sin estado</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Método de Pago:</th>
                                <td>
                                    @if($transaction->payment_method)
                                        <span class="badge badge-info">{{ ucfirst($transaction->payment_method) }}</span>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th>Referencia de Pago:</th>
                                <td>{{ $transaction->payment_reference ?? 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Fecha de Pago:</th>
                                <td>{{ $transaction->payment_date ? $transaction->payment_date->format('d/m/Y') : 'N/A' }}</td>
                            </tr>
                            <tr>
                                <th>Fecha de Creación:</th>
                                <td>{{ $transaction->created_at->format('d/m/Y H:i:s') }}</td>
                            </tr>
                            <tr>
                                <th>Fecha de Proceso:</th>
                                <td>{{ $transaction->processed_at ? $transaction->processed_at->format('d/m/Y H:i:s') : 'N/A' }}</td>
                            </tr>
                        </table>
                    </div>
                </div>

                @if($transaction->admin_notes)
                <div class="alert alert-info mt-3">
                    <h5><i class="fas fa-sticky-note"></i> Notas del Administrador:</h5>
                    <p class="mb-0">{{ $transaction->admin_notes }}</p>
                    @if($transaction->verified_at)
                        <small class="text-muted">
                            Fecha: {{ $transaction->verified_at->format('d/m/Y H:i:s') }}
                        </small>
                    @endif
                </div>
                @endif
            </div>
        </div>

        @if($transaction->stripe_payment_intent_id)
        <!-- Información de Stripe -->
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fab fa-stripe-s"></i> Información de Stripe
                </h3>
            </div>
            <div class="card-body">
                <table class="table table-striped">
                    <tr>
                        <th width="30%">Payment Intent ID:</th>
                        <td>
                            <code>{{ $transaction->stripe_payment_intent_id }}</code>
                            <a href="https://dashboard.stripe.com/payments/{{ $transaction->stripe_payment_intent_id }}" 
                               target="_blank" class="btn btn-link btn-sm">
                                <i class="fas fa-external-link-alt"></i> Ver en Stripe
                            </a>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
        @endif

        @if($transaction->receipt_file)
        <!-- Comprobante de Pago -->
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-receipt"></i> Comprobante de Pago
                </h3>
            </div>
            <div class="card-body">
                <p>
                    <a href="{{ Storage::url($transaction->receipt_file) }}" 
                       target="_blank" class="btn btn-primary">
                        <i class="fas fa-download"></i> Descargar Comprobante
                    </a>
                </p>
            </div>
        </div>
        @endif
    </div>

    <!-- Panel Lateral -->
    <div class="col-md-4">
        <!-- Información del Estudiante -->
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user"></i> Información del Estudiante
                </h3>
            </div>
            <div class="card-body">
                @if($transaction->history)
                    <table class="table table-sm">
                        <tr>
                            <th>Nombre:</th>
                            <td>{{ $transaction->history->name }}</td>
                        </tr>
                        <tr>
                            <th>Cédula:</th>
                            <td>{{ $transaction->history->cedula ?? 'Sin cédula' }}</td>
                        </tr>
                        <tr>
                            <th>Sección:</th>
                            <td>{{ $transaction->history->seccion ?? 'Sin sección' }}</td>
                        </tr>
                        <tr>
                            <th>Créditos Actuales:</th>
                            <td>
                                <span class="badge badge-{{ $transaction->history->creditos > 0 ? 'success' : 'warning' }} badge-lg">
                                    ₡{{ number_format($transaction->history->creditos) }}
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th>Estado:</th>
                            <td>
                                <span class="badge badge-{{ $transaction->history->status == 1 ? 'success' : 'danger' }}">
                                    {{ $transaction->history->status == 1 ? 'Activo' : 'Inactivo' }}
                                </span>
                            </td>
                        </tr>
                    </table>
                    <div class="text-center mt-3">
                        <a href="{{ route('histories.edit', $transaction->history->id) }}" class="btn btn-primary btn-sm">
                            <i class="fas fa-edit"></i> Ver Estudiante
                        </a>
                    </div>
                @else
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        No se encontró información del estudiante asociado.
                    </div>
                @endif
            </div>
        </div>

        <!-- Acciones -->
        @if($transaction->verification_status == 'pending')
        <div class="card card-warning">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-tools"></i> Acciones Administrativas
                </h3>
            </div>
            <div class="card-body">
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-success btn-block" onclick="verifyTransaction({{ $transaction->id }})">
                        <i class="fas fa-check"></i> Verificar Transacción
                    </button>
                    <button type="button" class="btn btn-danger btn-block" onclick="rejectTransaction({{ $transaction->id }})">
                        <i class="fas fa-times"></i> Rechazar Transacción
                    </button>
                </div>
            </div>
        </div>
        @elseif($transaction->verified_by)
        <!-- Información de Verificación -->
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user-check"></i> Información de Verificación
                </h3>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th>Verificado por:</th>
                        <td>Admin ID: {{ $transaction->verified_by }}</td>
                    </tr>
                    <tr>
                        <th>Fecha:</th>
                        <td>{{ $transaction->verified_at ? $transaction->verified_at->format('d/m/Y H:i:s') : 'N/A' }}</td>
                    </tr>
                </table>
            </div>
        </div>
        @endif

        <!-- Información de Asistencia (si aplica) -->
        @if($transaction->attendance)
        <div class="card card-secondary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-calendar-check"></i> Asistencia Relacionada
                </h3>
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <tr>
                        <th>ID Asistencia:</th>
                        <td>{{ $transaction->attendance->id }}</td>
                    </tr>
                    <tr>
                        <th>Fecha:</th>
                        <td>{{ $transaction->attendance->created_at->format('d/m/Y H:i:s') }}</td>
                    </tr>
                </table>
            </div>
        </div>
        @endif
    </div>
</div>

<!-- Modales (reutilizar los mismos del index) -->
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
    .card {
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        border-radius: 10px;
    }
    
    .table th {
        background-color: #f8f9fa;
        border-top: none;
    }
    
    .badge-lg {
        font-size: 1em;
        padding: 0.5em 0.75em;
    }
    
    code {
        background-color: #f8f9fa;
        padding: 0.25em 0.5em;
        border-radius: 0.25rem;
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
    });
</script>
@stop