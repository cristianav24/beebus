@extends('adminlte::page')

@section('title', 'Detalles de Solicitud - ' . Config::get('adminlte.title'))

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1>
        <i class="fas fa-eye"></i> Detalles de Solicitud de Padre
    </h1>
    <a href="{{ route('admin.parent-requests') }}" class="btn btn-secondary">
        <i class="fas fa-arrow-left"></i> Volver a Lista
    </a>
</div>
@stop

@section('content')
@include('layouts.flash-message')

<div class="row">
    <!-- Request Status Card -->
    <div class="col-md-12">
        <div class="card card-outline 
            @if($request->status === 'pending') card-warning
            @elseif($request->status === 'approved') card-success  
            @elseif($request->status === 'rejected') card-danger
            @endif">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-info-circle"></i> Estado de la Solicitud
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-3">
                        <strong>Estado Actual:</strong><br>
                        @if($request->status === 'pending')
                        <span class="badge badge-warning badge-lg">
                            <i class="fas fa-clock"></i> Pendiente
                        </span>
                        @elseif($request->status === 'approved')
                        <span class="badge badge-success badge-lg">
                            <i class="fas fa-check"></i> Aprobado
                        </span>
                        @elseif($request->status === 'rejected')
                        <span class="badge badge-danger badge-lg">
                            <i class="fas fa-times"></i> Rechazado
                        </span>
                        @endif
                    </div>
                    <div class="col-md-3">
                        <strong>Fecha de Solicitud:</strong><br>
                        {{ $request->requested_at ? $request->requested_at->format('d/m/Y H:i') : 'N/A' }}
                    </div>
                    @if($request->reviewed_at)
                    <div class="col-md-3">
                        <strong>Fecha de Revisión:</strong><br>
                        {{ $request->reviewed_at->format('d/m/Y H:i') }}
                    </div>
                    @endif
                    @if($request->reviewer)
                    <div class="col-md-3">
                        <strong>Revisado por:</strong><br>
                        {{ $request->reviewer->name }}
                    </div>
                    @endif
                </div>

                @if($request->status === 'pending')
                <div class="mt-3">
                    <button type="button" class="btn btn-success" onclick="showApproveModal({{ $request->id }})">
                        <i class="fas fa-check"></i> Aprobar Solicitud
                    </button>
                    <button type="button" class="btn btn-danger" onclick="showRejectModal({{ $request->id }})">
                        <i class="fas fa-times"></i> Rechazar Solicitud
                    </button>
                </div>
                @endif

                @if($request->rejection_reason)
                <div class="mt-3">
                    <div class="alert alert-danger">
                        <h5><i class="fas fa-exclamation-triangle"></i> Razón del Rechazo:</h5>
                        <p><strong>{{ ucfirst(str_replace('_', ' ', $request->rejection_reason)) }}</strong></p>
                        @if($request->admin_notes)
                        <p><strong>Notas del Administrador:</strong> {{ $request->admin_notes }}</p>
                        @endif
                    </div>
                </div>
                @endif

                @if($request->status === 'approved' && $request->admin_notes)
                <div class="mt-3">
                    <div class="alert alert-success">
                        <h5><i class="fas fa-sticky-note"></i> Notas del Administrador:</h5>
                        <p>{{ $request->admin_notes }}</p>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Parent Information -->
    <div class="col-md-6">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user"></i> Información del Padre/Tutor
                </h3>
            </div>
            <div class="card-body">
                @if($request->parent)
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Nombre Completo:</strong></td>
                        <td>{{ $request->parent->name }}</td>
                    </tr>
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td>{{ $request->parent->email }}</td>
                    </tr>
                    <tr>
                        <td><strong>Rol:</strong></td>
                        <td>
                            <span class="badge badge-info">
                                @switch($request->parent->role)
                                @case('4')
                                <i class="fas fa-users"></i> Padre/Tutor
                                @break
                                @default
                                Desconocido
                                @endswitch
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Fecha de Registro:</strong></td>
                        <td>{{ $request->parent->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                </table>

                @php
                $parentProfile = \App\Models\ParentProfile::where('user_id', $request->parent_user_id)->first();
                @endphp

                @if($parentProfile)
                <hr>
                <h5><i class="fas fa-address-card"></i> Información de Perfil:</h5>
                <table class="table table-borderless">
                    @if($parentProfile->cedula)
                    <tr>
                        <td><strong>Cédula:</strong></td>
                        <td>{{ $parentProfile->formatted_cedula }}</td>
                    </tr>
                    @endif
                    @if($parentProfile->telefono)
                    <tr>
                        <td><strong>Teléfono:</strong></td>
                        <td>{{ $parentProfile->formatted_telefono }}</td>
                    </tr>
                    @endif
                    @if($parentProfile->direccion)
                    <tr>
                        <td><strong>Dirección:</strong></td>
                        <td>{{ $parentProfile->direccion }}</td>
                    </tr>
                    @endif
                    @if($parentProfile->provincia)
                    <tr>
                        <td><strong>Ubicación:</strong></td>
                        <td>{{ $parentProfile->provincia }}{{ $parentProfile->canton ? ', ' . $parentProfile->canton : '' }}{{ $parentProfile->distrito ? ', ' . $parentProfile->distrito : '' }}</td>
                    </tr>
                    @endif
                    @if($parentProfile->ocupacion)
                    <tr>
                        <td><strong>Ocupación:</strong></td>
                        <td>{{ $parentProfile->ocupacion }}</td>
                    </tr>
                    @endif
                    @if($parentProfile->correo_secundario)
                    <tr>
                        <td><strong>Email Secundario:</strong></td>
                        <td>{{ $parentProfile->correo_secundario }}</td>
                    </tr>
                    @endif
                </table>

                <div class="mt-2">
                    @if($parentProfile->isProfileComplete())
                    <span class="badge badge-success">
                        <i class="fas fa-check-circle"></i> Perfil Completo
                    </span>
                    @else
                    <span class="badge badge-warning">
                        <i class="fas fa-exclamation-triangle"></i> Perfil Incompleto
                    </span>
                    @endif
                </div>
                @else
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Perfil No Encontrado:</strong>
                    El padre no ha completado su información de perfil.
                </div>
                @endif
                @else
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Error:</strong> No se pudo cargar la información del padre.
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Student Information -->
    <div class="col-md-6">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-graduation-cap"></i> Información del Estudiante
                </h3>
            </div>
            <div class="card-body">
                @if($request->student)
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Nombre:</strong></td>
                        <td>{{ $request->student->name }}</td>
                    </tr>
                    <tr>
                        <td><strong>Cédula:</strong></td>
                        <td>{{ $request->student->cedula ?: 'No registrada' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Colegio:</strong></td>
                        <td>
                            @if($request->student->colegio)
                            <span class="badge badge-primary">{{ $request->student->colegio }}</span>
                            @else
                            {{ $request->student->colegio ?: 'No asignado' }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Sección:</strong></td>
                        <td>{{ $request->student->seccion ?: 'No asignada' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Beca:</strong></td>
                        <td>
                            @if($request->student->beca)
                            <span class="badge badge-success">{{ $request->student->beca->nombre_beca }}</span>
                            @else
                            {{ $request->student->tipoBeca ?: 'Sin beca' }}
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Créditos Actuales:</strong></td>
                        <td>
                            <span class="badge badge-info">
                                <i class="fas fa-coins"></i> {{ number_format($request->student->creditos, 0) }} créditos
                            </span>
                        </td>
                    </tr>
                    @if($request->student->ruta)
                    <tr>
                        <td><strong>Ruta de Bus:</strong></td>
                        <td>
                            <span class="badge badge-secondary">{{ $request->student->ruta->nombre }}</span>
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <td><strong>Estado:</strong></td>
                        <td>
                            @if($request->student->status == 1)
                            <span class="badge badge-success"><i class="fas fa-check"></i> Activo</span>
                            @else
                            <span class="badge badge-secondary"><i class="fas fa-pause"></i> Inactivo</span>
                            @endif
                        </td>
                    </tr>
                </table>
                @else
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Error:</strong> No se pudo cargar la información del estudiante.
                </div>
                @endif
            </div>
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
    .badge-lg {
        font-size: 0.875em;
        padding: 0.5rem 0.75rem;
    }

    .table-borderless td {
        padding: 0.5rem 0.75rem;
        border: none;
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
<script>
    function showApproveModal(requestId) {
        $('#approve_request_id').val(requestId);
        $('#approveModal').modal('show');
    }

    function showRejectModal(requestId) {
        $('#reject_request_id').val(requestId);
        $('#rejectModal').modal('show');
    }

    $(document).ready(function() {
        // Confirm approve
        $('#confirmApprove').click(function() {
            const requestId = $('#approve_request_id').val();
            const notes = $('#approve_notes').val();

            $.ajax({
                url: "{{ url('/admin/parent-requests') }}/" + requestId + "/approve",
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    notes: notes
                },
                success: function(response) {
                    $('#approveModal').modal('hide');
                    if (response.success) {
                        alert('Éxito: ' + response.message);
                        location.reload(); // Reload to show updated status
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
                        location.reload(); // Reload to show updated status
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