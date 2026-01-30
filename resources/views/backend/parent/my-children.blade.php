@extends('adminlte::page')

@section('title', 'Mis Hijos - ' . Config::get('adminlte.title'))

@section('content_header')
<div class="d-flex justify-content-between align-items-center flex-wrap">
    <h1>
        <i class="fas fa-child"></i> Mis Hijos
    </h1>
    <a href="{{ route('parent.dashboard') }}" class="btn btn-secondary btn-sm">
        <i class="fas fa-arrow-left"></i> Volver al Dashboard
    </a>
</div>
@stop

@section('content')
@include('layouts.flash-message')

@if($children->isEmpty())
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="fas fa-users text-muted" style="font-size: 4rem;"></i>
            <h4 class="mt-3 text-muted">No tienes hijos asignados</h4>
            <p class="text-muted">Solicita la asignacion de tus hijos desde el Dashboard.</p>
            <a href="{{ route('parent.dashboard') }}" class="btn btn-primary">
                <i class="fas fa-search"></i> Ir al Dashboard
            </a>
        </div>
    </div>
@else
    @foreach($children as $student)
    <div class="card card-primary card-outline mb-4 child-card">
        <div class="card-header">
            <h3 class="card-title">
                <i class="fas fa-user-graduate"></i> {{ $student->name }}
            </h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                {{-- Perfil del Estudiante --}}
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="card card-outline card-info h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="fas fa-id-card"></i> Perfil</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm table-borderless profile-table">
                                <tr>
                                    <th>Nombre:</th>
                                    <td>{{ $student->name }}</td>
                                </tr>
                                <tr>
                                    <th>Cedula:</th>
                                    <td>{{ $student->cedula }}</td>
                                </tr>
                                <tr>
                                    <th>Colegio:</th>
                                    <td>{{ $student->colegio ? $student->colegio : 'No asignado' }}</td>
                                </tr>
                                <tr>
                                    <th>Seccion:</th>
                                    <td>{{ $student->seccion ?: 'No asignada' }}</td>
                                </tr>
                                <tr>
                                    <th>Beca:</th>
                                    <td>{{ $student->beca ? $student->beca->nombre_beca : ($student->tipoBeca ?: 'Sin beca') }}</td>
                                </tr>
                                <tr>
                                    <th>Ruta:</th>
                                    <td>{{ $student->ruta ? $student->ruta->name : 'No asignada' }}</td>
                                </tr>
                            </table>
                            <hr>
                            <div class="row text-center">
                                <div class="col-6">
                                    <h5 class="text-success mb-0">₡{{ number_format($student->creditos, 0, ',', '.') }}</h5>
                                    <small class="text-muted">Creditos</small>
                                </div>
                                <div class="col-6">
                                    <h5 class="text-primary mb-0">{{ $student->chancesParaMarcar }}</h5>
                                    <small class="text-muted">Chances</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Codigo QR --}}
                <div class="col-lg-4 col-md-6 mb-3">
                    <div class="card card-outline card-success h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="fas fa-qrcode"></i> Codigo QR</h5>
                        </div>
                        <div class="card-body text-center">
                            <div class="qr-container mb-3">
                                <div id="qr-code-{{ $student->id }}" class="qr-code-box"></div>
                            </div>
                            <p class="text-muted small">Codigo QR del estudiante para el acceso al transporte.</p>
                            <button class="btn btn-success btn-block download-qr-btn"
                                    data-student-id="{{ $student->id }}"
                                    data-student-name="{{ $student->name }}">
                                <i class="fas fa-download"></i> Descargar QR
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Contratos --}}
                <div class="col-lg-4 col-md-12 mb-3">
                    <div class="card card-outline card-warning h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><i class="fas fa-file-contract"></i> Contrato</h5>
                        </div>
                        <div class="card-body">
                            {{-- Estado del contrato --}}
                            <div class="mb-3">
                                <strong>Estado:</strong>
                                @if($student->contrato_subido)
                                    <span class="badge badge-success"><i class="fas fa-check"></i> Subido</span>
                                    @if($student->contrato_fecha_subida)
                                        <br><small class="text-muted">Fecha: {{ \Carbon\Carbon::parse($student->contrato_fecha_subida)->format('d/m/Y H:i') }}</small>
                                    @endif
                                @else
                                    <span class="badge badge-warning"><i class="fas fa-clock"></i> Pendiente</span>
                                @endif
                            </div>

                            {{-- Descargar plantilla --}}
                            <a href="{{ route('parent.download-contract-template') }}" class="btn btn-info btn-block btn-sm mb-2">
                                <i class="fas fa-file-pdf"></i> Descargar Plantilla de Contrato
                            </a>

                            {{-- Subir contrato --}}
                            <form class="upload-contract-form" data-student-id="{{ $student->id }}">
                                @csrf
                                <div class="form-group mb-2">
                                    <label class="small"><strong>Subir contrato firmado (PDF):</strong></label>
                                    <div class="custom-file">
                                        <input type="file"
                                               class="custom-file-input contract-file"
                                               id="contract-file-{{ $student->id }}"
                                               accept=".pdf"
                                               data-student-id="{{ $student->id }}">
                                        <label class="custom-file-label" for="contract-file-{{ $student->id }}" data-browse="Elegir">
                                            Seleccionar archivo...
                                        </label>
                                    </div>
                                    <small class="form-text text-muted">Solo archivos PDF, maximo 5MB</small>
                                </div>
                                <button type="submit" class="btn btn-warning btn-block btn-sm upload-btn" disabled>
                                    <i class="fas fa-upload"></i> Subir Contrato
                                </button>
                            </form>

                            {{-- Descargar contrato subido --}}
                            @if($student->contrato_subido)
                                <a href="{{ route('parent.student.download-contract', $student->id) }}"
                                   class="btn btn-success btn-block btn-sm mt-2">
                                    <i class="fas fa-download"></i> Descargar Contrato Subido
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endforeach
@endif

@stop

@section('css')
<style>
    .child-card {
        border-radius: 10px;
        box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
    }

    .qr-code-box {
        display: inline-block;
    }

    .qr-code-box img {
        max-width: 200px;
        width: 100%;
        height: auto;
    }

    .qr-code-box canvas {
        max-width: 200px;
        width: 100%;
        height: auto;
    }

    .qr-container {
        display: flex;
        justify-content: center;
        align-items: center;
        min-height: 200px;
    }

    .profile-table th {
        width: 35%;
        font-weight: 600;
        color: #555;
        padding: 0.3rem 0.5rem;
        white-space: nowrap;
    }

    .profile-table td {
        padding: 0.3rem 0.5rem;
    }

    .card.h-100 {
        min-height: 100%;
    }

    .custom-file-label {
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* Responsive */
    @media (max-width: 767px) {
        .content-header h1 {
            font-size: 1.3rem;
        }

        .content-header .btn-sm {
            font-size: 0.8rem;
            padding: 0.3rem 0.6rem;
        }

        .child-card .card-header .card-title {
            font-size: 1rem;
        }

        .profile-table th,
        .profile-table td {
            font-size: 0.85rem;
            padding: 0.25rem 0.4rem;
        }

        .qr-code-box img,
        .qr-code-box canvas {
            max-width: 180px;
        }

        .qr-container {
            min-height: 180px;
        }
    }

    @media (max-width: 575px) {
        .d-flex.justify-content-between {
            flex-direction: column;
            gap: 10px;
        }

        .content-header .btn-sm {
            align-self: flex-start;
        }
    }
</style>
@stop

@section('js')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.23.0/dist/sweetalert2.all.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Generar QR codes para cada estudiante
    @foreach($children as $student)
    (function() {
        var container = document.getElementById('qr-code-{{ $student->id }}');
        if (container) {
            new QRCode(container, {
                text: '{{ $student->qr_data }}',
                width: 200,
                height: 200,
                colorDark: '#000000',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.M
            });
        }
    })();
    @endforeach

    // Descargar QR
    document.querySelectorAll('.download-qr-btn').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var studentId = this.dataset.studentId;
            var studentName = this.dataset.studentName;
            var container = document.getElementById('qr-code-' + studentId);

            if (container) {
                // qrcodejs genera un canvas y un img dentro del contenedor
                var img = container.querySelector('img');
                var canvas = container.querySelector('canvas');
                var link = document.createElement('a');
                link.download = 'QR_' + studentName.replace(/\s+/g, '_') + '.png';

                if (canvas) {
                    link.href = canvas.toDataURL('image/png');
                } else if (img) {
                    link.href = img.src;
                }
                link.click();
            }
        });
    });

    // Mostrar nombre de archivo seleccionado
    document.querySelectorAll('.contract-file').forEach(function(input) {
        input.addEventListener('change', function() {
            var fileName = this.files[0] ? this.files[0].name : 'Seleccionar archivo...';
            this.nextElementSibling.textContent = fileName;
            var form = this.closest('.upload-contract-form');
            var uploadBtn = form.querySelector('.upload-btn');
            uploadBtn.disabled = !this.files[0];
        });
    });

    // Subir contrato
    document.querySelectorAll('.upload-contract-form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            var studentId = this.dataset.studentId;
            var fileInput = this.querySelector('.contract-file');
            var uploadBtn = this.querySelector('.upload-btn');

            if (!fileInput.files[0]) {
                Swal.fire('Error', 'Selecciona un archivo PDF primero.', 'error');
                return;
            }

            var file = fileInput.files[0];
            if (file.size > 5 * 1024 * 1024) {
                Swal.fire('Error', 'El archivo no puede superar los 5MB.', 'error');
                return;
            }

            if (file.type !== 'application/pdf') {
                Swal.fire('Error', 'Solo se permiten archivos PDF.', 'error');
                return;
            }

            uploadBtn.disabled = true;
            uploadBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Subiendo...';

            var formData = new FormData();
            formData.append('contract_file', file);
            formData.append('_token', '{{ csrf_token() }}');

            fetch('/parent/student/' + studentId + '/upload-contract', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Contrato subido',
                        text: data.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(function() {
                        location.reload();
                    });
                } else {
                    Swal.fire('Error', data.message || 'Error al subir el contrato.', 'error');
                    uploadBtn.disabled = false;
                    uploadBtn.innerHTML = '<i class="fas fa-upload"></i> Subir Contrato';
                }
            })
            .catch(function(error) {
                console.error('Error:', error);
                Swal.fire('Error', 'Error al subir el contrato. Intenta nuevamente.', 'error');
                uploadBtn.disabled = false;
                uploadBtn.innerHTML = '<i class="fas fa-upload"></i> Subir Contrato';
            });
        });
    });
});
</script>
@stop
