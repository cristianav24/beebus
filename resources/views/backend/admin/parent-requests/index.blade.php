@extends('adminlte::page')

@section('title', 'Solicitudes de Padres - ' . Config::get('adminlte.title'))

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1>
        <i class="fas fa-users"></i> Gestión de Solicitudes de Padres
    </h1>
</div>
@stop

@section('content')
@include('layouts.flash-message')

<!-- Statistics Cards -->
<div class="row mb-4">
    @php
        $pendingCount = \App\Models\ParentChildRelationship::where('status', 'pending')->count();
        $approvedCount = \App\Models\ParentChildRelationship::where('status', 'approved')->count();
        $rejectedCount = \App\Models\ParentChildRelationship::where('status', 'rejected')->count();
        $totalCount = $pendingCount + $approvedCount + $rejectedCount;
    @endphp
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $pendingCount }}</h3>
                <p>Solicitudes Pendientes</p>
            </div>
            <div class="icon">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $approvedCount }}</h3>
                <p>Solicitudes Aprobadas</p>
            </div>
            <div class="icon">
                <i class="fas fa-check-circle"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $rejectedCount }}</h3>
                <p>Solicitudes Rechazadas</p>
            </div>
            <div class="icon">
                <i class="fas fa-times-circle"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $totalCount }}</h3>
                <p>Total Solicitudes</p>
            </div>
            <div class="icon">
                <i class="fas fa-list"></i>
            </div>
        </div>
    </div>
</div>

<!-- Main Table Card -->
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-table"></i> Lista de Solicitudes de Padres
        </h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            {!! $html->table(['class' => 'table table-striped table-bordered table-hover']) !!}
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal fade" id="approveModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title">
                    <i class="fas fa-check"></i> Aprobar Solicitud
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="approveForm">
                    @csrf
                    <input type="hidden" id="approve_request_id" name="request_id">
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>¿Está seguro que desea aprobar esta solicitud?</strong>
                        <br>El padre podrá ver información del estudiante una vez aprobada.
                    </div>
                    
                    <div class="form-group">
                        <label for="approve_notes">Notas (Opcional):</label>
                        <textarea class="form-control" id="approve_notes" name="notes" rows="3" 
                                  placeholder="Agregue cualquier comentario o nota sobre esta aprobación..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="confirmApprove">
                    <i class="fas fa-check"></i> Aprobar Solicitud
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title">
                    <i class="fas fa-times"></i> Rechazar Solicitud
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="rejectForm">
                    @csrf
                    <input type="hidden" id="reject_request_id" name="request_id">
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>¿Está seguro que desea rechazar esta solicitud?</strong>
                        <br>Debe proporcionar una razón para el rechazo.
                    </div>
                    
                    <div class="form-group">
                        <label for="reject_reason">Razón del Rechazo <span class="text-danger">*</span>:</label>
                        <select class="form-control" id="reject_reason" name="reason" required>
                            <option value="">Seleccione una razón</option>
                            <option value="documentos_incorrectos">Documentos incorrectos o incompletos</option>
                            <option value="identidad_no_verificada">No se pudo verificar la identidad</option>
                            <option value="no_es_tutor">No es el tutor legal del estudiante</option>
                            <option value="estudiante_no_encontrado">Estudiante no encontrado en sistema</option>
                            <option value="datos_inconsistentes">Datos inconsistentes</option>
                            <option value="otra">Otra razón</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="reject_notes">Notas Adicionales:</label>
                        <textarea class="form-control" id="reject_notes" name="notes" rows="3" 
                                  placeholder="Proporcione detalles adicionales sobre el rechazo..."></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmReject">
                    <i class="fas fa-times"></i> Rechazar Solicitud
                </button>
            </div>
        </div>
    </div>
</div>

@stop

@section('css')
<style>
    .small-box {
        border-radius: 0.25rem;
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
        margin-bottom: 20px;
    }
    
    .table th {
        background-color: #f8f9fa;
        border-color: #dee2e6;
    }
    
    .badge {
        font-size: 0.75em;
        padding: 0.375rem 0.75rem;
    }
    
    .btn-group .btn {
        margin-right: 2px;
    }
    
    .btn-group .btn:last-child {
        margin-right: 0;
    }
    
    .modal-header.bg-success,
    .modal-header.bg-danger {
        color: white;
    }
    
    .modal-header .close {
        color: white;
        opacity: 0.8;
    }
    
    .modal-header .close:hover {
        opacity: 1;
    }
</style>
@stop

@section('js')
{!! $html->scripts() !!}
<script>
$(document).ready(function() {
    // Handle approve button click
    $(document).on('click', '.approve-btn', function() {
        const requestId = $(this).data('id');
        $('#approve_request_id').val(requestId);
        $('#approveModal').modal('show');
    });
    
    // Handle reject button click
    $(document).on('click', '.reject-btn', function() {
        const requestId = $(this).data('id');
        $('#reject_request_id').val(requestId);
        $('#rejectModal').modal('show');
    });
    
    // Confirm approve
    $('#confirmApprove').click(function() {
        const requestId = $('#approve_request_id').val();
        const notes = $('#approve_notes').val();
        
        $.ajax({
            url: '/admin/parent-requests/' + requestId + '/approve',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                notes: notes
            },
            success: function(response) {
                $('#approveModal').modal('hide');
                if (response.success) {
                    alert('Éxito: ' + response.message);
                    window.LaravelDataTables["dataTableBuilder"].draw();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr) {
                $('#approveModal').modal('hide');
                alert('Error al procesar la solicitud');
            }
        });
    });
    
    // Confirm reject
    $('#confirmReject').click(function() {
        const requestId = $('#reject_request_id').val();
        const reason = $('#reject_reason').val();
        const notes = $('#reject_notes').val();
        
        if (!reason) {
            alert('Debe seleccionar una razón para el rechazo');
            return;
        }
        
        $.ajax({
            url: '/admin/parent-requests/' + requestId + '/reject',
            method: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                reason: reason,
                notes: notes
            },
            success: function(response) {
                $('#rejectModal').modal('hide');
                if (response.success) {
                    alert('Éxito: ' + response.message);
                    window.LaravelDataTables["dataTableBuilder"].draw();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr) {
                $('#rejectModal').modal('hide');
                alert('Error al procesar la solicitud');
            }
        });
    });
    
    // Clear modals on hide
    $('#approveModal').on('hidden.bs.modal', function() {
        $('#approveForm')[0].reset();
    });
    
    $('#rejectModal').on('hidden.bs.modal', function() {
        $('#rejectForm')[0].reset();
    });
});
</script>
@stop