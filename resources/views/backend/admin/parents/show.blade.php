@extends('adminlte::page')

@section('title', 'Detalles del Padre - ' . Config::get('adminlte.title'))

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1>
        <i class="fas fa-user"></i> Detalles del Padre: {{ $parent->name }}
    </h1>
    <div>
        <a href="{{ route('admin.parents.edit', $parent->id) }}" class="btn btn-primary">
            <i class="fas fa-edit"></i> Editar
        </a>
        <a href="{{ route('admin.parents.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Volver a Lista
        </a>
    </div>
</div>
@stop

@section('content')
@include('layouts.flash-message')

<div class="row">
    <!-- Parent Information -->
    <div class="col-md-6">
        <div class="card card-primary">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-user"></i> Información del Usuario
                </h3>
            </div>
            <div class="card-body">
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Nombre Completo:</strong></td>
                        <td>{{ $parent->name }}</td>
                    </tr>
                    <tr>
                        <td><strong>Email:</strong></td>
                        <td>{{ $parent->email }}</td>
                    </tr>
                    <tr>
                        <td><strong>Rol:</strong></td>
                        <td>
                            <span class="badge badge-info">
                                <i class="fas fa-users"></i> Padre/Tutor
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Imagen de Perfil:</strong></td>
                        <td>
                            @if($parent->image && $parent->image !== 'default-user.png')
                            <img src="{{ asset('storage/uploads/' . $parent->image) }}"
                                class="img-thumbnail" style="max-width: 60px; max-height: 60px;">
                            <span class="ml-2 text-success">Personalizada</span>
                            @else
                            <span class="badge badge-secondary">Imagen por defecto</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Fecha de Registro:</strong></td>
                        <td>{{ $parent->created_at->format('d/m/Y H:i') }}</td>
                    </tr>
                    <tr>
                        <td><strong>Última Actualización:</strong></td>
                        <td>{{ $parent->updated_at->format('d/m/Y H:i') }}</td>
                    </tr>
                </table>
            </div>
        </div>
    </div>

    <!-- Parent Profile Information -->
    <div class="col-md-6">
        <div class="card card-success">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-address-card"></i> Información del Perfil
                </h3>
            </div>
            <div class="card-body">
                @if($parent->parentProfile)
                <table class="table table-borderless">
                    <tr>
                        <td><strong>Cédula:</strong></td>
                        <td>
                            @if($parent->parentProfile->cedula)
                            <span class="badge badge-info">{{ $parent->parentProfile->formatted_cedula }}</span>
                            @else
                            <span class="text-muted">No registrada</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Teléfono:</strong></td>
                        <td>
                            @if($parent->parentProfile->telefono)
                            <span class="badge badge-success">{{ $parent->parentProfile->formatted_telefono }}</span>
                            @else
                            <span class="text-muted">No registrado</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Dirección:</strong></td>
                        <td>
                            @if($parent->parentProfile->direccion)
                            <div class="bg-light p-2 rounded">
                                {{ $parent->parentProfile->direccion }}
                            </div>
                            @else
                            <span class="text-muted">No registrada</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Provincia:</strong></td>
                        <td>
                            @if($parent->parentProfile->provincia)
                            <span class="badge badge-primary">{{ $parent->parentProfile->provincia }}</span>
                            @else
                            <span class="text-muted">No registrada</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Cantón:</strong></td>
                        <td>
                            @if($parent->parentProfile->canton)
                            <span class="badge badge-secondary">{{ $parent->parentProfile->canton }}</span>
                            @else
                            <span class="text-muted">No registrado</span>
                            @endif
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Distrito:</strong></td>
                        <td>{{ $parent->parentProfile->distrito ?: 'No especificado' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Ocupación:</strong></td>
                        <td>{{ $parent->parentProfile->ocupacion ?: 'No especificada' }}</td>
                    </tr>
                    <tr>
                        <td><strong>Correo Secundario:</strong></td>
                        <td>{{ $parent->parentProfile->correo_secundario ?: 'No registrado' }}</td>
                    </tr>
                </table>

                <div class="mt-3">
                    <strong>Estado del Perfil:</strong>
                    @if($parent->parentProfile->isProfileComplete())
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
                    <strong>Perfil No Encontrado</strong><br>
                    Este padre no ha completado su información de perfil.
                    <br><br>
                    <a href="{{ route('admin.parents.edit', $parent->id) }}" class="btn btn-warning btn-sm">
                        <i class="fas fa-plus"></i> Agregar Información de Perfil
                    </a>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Parent-Child Relationships -->
<div class="row">
    <div class="col-12">
        <div class="card card-info">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-child"></i> Hijos Asignados
                    <span class="badge badge-light ml-2">{{ $relationships->where('status', 'approved')->count() }} aprobados</span>
                </h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-success btn-sm" onclick="showAssignModal()">
                        <i class="fas fa-user-plus"></i> Asignar Más Hijos
                    </button>
                </div>
            </div>
            <div class="card-body">
                @if($relationships->count() > 0)
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Estudiante</th>
                                <th>Cédula</th>
                                <th>Colegio</th>
                                <th>Sección</th>
                                <th>Beca</th>
                                <th>Créditos</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($relationships as $relationship)
                            <tr>
                                <td>
                                    <strong>{{ $relationship->student ? $relationship->student->name : 'N/A' }}</strong>
                                </td>
                                <td>{{ $relationship->student ? $relationship->student->cedula : 'N/A' }}</td>
                                <td>
                                    @if($relationship->student && $relationship->student->colegio)
                                    <span class="badge badge-primary">{{ $relationship->student->colegio }}</span>
                                    @else
                                    {{ $relationship->student ? ($relationship->student->colegio ?: 'No asignado') : 'N/A' }}
                                    @endif
                                </td>
                                <td>{{ $relationship->student ? $relationship->student->seccion : 'N/A' }}</td>
                                <td>
                                    @if($relationship->student && $relationship->student->beca)
                                    <span class="badge badge-success">{{ $relationship->student->beca->nombre_beca }}</span>
                                    @else
                                    {{ $relationship->student ? ($relationship->student->tipoBeca ?: 'Sin beca') : 'N/A' }}
                                    @endif
                                </td>
                                <td>
                                    @if($relationship->student)
                                    <span class="badge badge-info">{{ number_format($relationship->student->creditos, 0) }} créditos</span>
                                    @else
                                    N/A
                                    @endif
                                </td>
                                <td>
                                    @switch($relationship->status)
                                    @case('approved')
                                    <span class="badge badge-success"><i class="fas fa-check"></i> Aprobado</span>
                                    @break
                                    @case('pending')
                                    <span class="badge badge-warning"><i class="fas fa-clock"></i> Pendiente</span>
                                    @break
                                    @case('rejected')
                                    <span class="badge badge-danger"><i class="fas fa-times"></i> Rechazado</span>
                                    @break
                                    @endswitch
                                </td>
                                <td>{{ $relationship->requested_at ? $relationship->requested_at->format('d/m/Y') : 'N/A' }}</td>
                                <td>
                                    @if($relationship->status === 'approved')
                                    <div class="btn-group" role="group">
                                        @if($relationship->student)
                                        <a href="{{ route('transactions.index', ['search' => $relationship->student->name]) }}" 
                                           class="btn btn-sm btn-info" title="Ver Transacciones de Crédito">
                                            <i class="fas fa-credit-card"></i>
                                        </a>
                                        <a href="{{ route('histories.edit', $relationship->student->id) }}" 
                                           class="btn btn-sm btn-primary" title="Ver Perfil del Estudiante">
                                            <i class="fas fa-user"></i>
                                        </a>
                                        @endif
                                        <button type="button" class="btn btn-sm btn-danger"
                                            onclick="removeRelationship({{ $relationship->id }})" title="Desasignar Hijo">
                                            <i class="fas fa-unlink"></i>
                                        </button>
                                    </div>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="alert alert-info text-center">
                    <i class="fas fa-info-circle"></i>
                    <strong>No hay hijos asignados</strong><br>
                    Este padre no tiene hijos asignados actualmente.
                    <br><br>
                    <button type="button" class="btn btn-success" onclick="showAssignModal()">
                        <i class="fas fa-user-plus"></i> Asignar Hijos
                    </button>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Assign Children Modal -->
<div class="modal fade" id="assignChildrenModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus"></i> Asignar Hijos a {{ $parent->name }}
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="student_search">Buscar Estudiantes:</label>
                    <input type="text" class="form-control" id="student_search"
                        placeholder="Escribe el nombre o cédula del estudiante...">
                    <small class="form-text text-muted">Mínimo 2 caracteres para buscar</small>
                </div>

                <div id="students_results" style="display: none;">
                    <h6>Resultados de búsqueda:</h6>
                    <div id="students_list"></div>
                </div>

                <div id="selected_students_section" style="display: none;">
                    <h6>Estudiantes seleccionados:</h6>
                    <div id="selected_students_list"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" id="confirmAssign" disabled>
                    <i class="fas fa-user-plus"></i> Asignar Estudiantes
                </button>
            </div>
        </div>
    </div>
</div>

@stop

@section('css')
<style>
    .table-borderless td {
        padding: 0.5rem 0.75rem;
        border: none;
        vertical-align: top;
    }

    .badge {
        font-size: 0.75em;
        padding: 0.375rem 0.75rem;
    }

    .bg-light {
        background-color: #f8f9fa !important;
    }

    .student-item {
        border: 1px solid #ddd;
        border-radius: 5px;
        padding: 10px;
        margin-bottom: 10px;
        cursor: pointer;
        transition: all 0.3s;
    }

    .student-item:hover {
        background-color: #f8f9fa;
        border-color: #007bff;
    }

    .student-item.selected {
        background-color: #e7f3ff;
        border-color: #007bff;
    }

    .selected-student-item {
        border: 1px solid #28a745;
        border-radius: 5px;
        padding: 8px;
        margin-bottom: 5px;
        background-color: #f8fff8;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .remove-student {
        color: #dc3545;
        cursor: pointer;
    }
</style>
@stop

@section('js')
<script>
let selectedStudents = [];

$(document).ready(function() {
    // Define functions in document ready to ensure they're available
});

function showAssignModal() {
        $('#assignChildrenModal').modal('show');
        selectedStudents = [];
        updateSelectedStudentsList();
        $('#student_search').val('');
        $('#students_results').hide();
    }

// Student search functionality
$('#student_search').on('input', function() {
    const searchTerm = $(this).val();

    if (searchTerm.length >= 2) {
        searchStudents(searchTerm);
    } else {
        $('#students_results').hide();
    }
});

    function searchStudents(searchTerm) {
        $.ajax({
            url: '{{ route("admin.parents.search-students") }}',
            method: 'GET',
            data: {
                search: searchTerm
            },
            success: function(response) {
                displayStudentsResults(response.students);
            }
        });
    }

    function displayStudentsResults(students) {
        const studentsList = $('#students_list');
        studentsList.empty();

        if (students.length === 0) {
            studentsList.html('<p class="text-muted">No se encontraron estudiantes</p>');
        } else {
            students.forEach(function(student) {
                const isSelected = selectedStudents.find(s => s.id === student.id);
                const studentHtml = `
                <div class="student-item ${isSelected ? 'selected' : ''}" onclick="toggleStudent(${student.id}, '${student.name}', '${student.cedula}', '${student.colegio}')">
                    <div class="row">
                        <div class="col-md-6">
                            <strong>${student.name}</strong><br>
                            <small>Cédula: ${student.cedula || 'No registrada'}</small>
                        </div>
                        <div class="col-md-6">
                            <span class="badge badge-primary">${student.colegio}</span><br>
                            <small>Sección: ${student.seccion || 'N/A'}</small><br>
                            <small>Beca: ${student.beca}</small><br>
                            <small>Créditos: ${student.creditos}</small>
                        </div>
                    </div>
                </div>
            `;
                studentsList.append(studentHtml);
            });
        }

        $('#students_results').show();
    }

    function toggleStudent(id, name, cedula, colegio) {
        const existingIndex = selectedStudents.findIndex(student => student.id === id);

        if (existingIndex > -1) {
            selectedStudents.splice(existingIndex, 1);
        } else {
            selectedStudents.push({
                id,
                name,
                cedula,
                colegio
            });
        }

        updateSelectedStudentsList();

        // Update visual state
        $(`.student-item`).removeClass('selected');
        selectedStudents.forEach(student => {
            $(`.student-item[onclick*="${student.id}"]`).addClass('selected');
        });
    }

    function updateSelectedStudentsList() {
        const selectedList = $('#selected_students_list');
        selectedList.empty();

        if (selectedStudents.length === 0) {
            $('#selected_students_section').hide();
            $('#confirmAssign').prop('disabled', true);
        } else {
            selectedStudents.forEach(function(student) {
                const studentHtml = `
                <div class="selected-student-item">
                    <div>
                        <strong>${student.name}</strong> - ${student.colegio}
                        <br><small>Cédula: ${student.cedula || 'No registrada'}</small>
                    </div>
                    <div class="remove-student" onclick="removeSelectedStudent(${student.id})">
                        <i class="fas fa-times"></i>
                    </div>
                </div>
            `;
                selectedList.append(studentHtml);
            });

            $('#selected_students_section').show();
            $('#confirmAssign').prop('disabled', false);
        }
    }

    function removeSelectedStudent(studentId) {
        const index = selectedStudents.findIndex(student => student.id === studentId);
        if (index > -1) {
            selectedStudents.splice(index, 1);
            updateSelectedStudentsList();
            $(`.student-item[onclick*="${studentId}"]`).removeClass('selected');
        }
    }

// Confirm assign children
$('#confirmAssign').click(function() {
    const parentId = {{ $parent->id }};
    const studentIds = selectedStudents.map(student => student.id);

    if (studentIds.length === 0) {
        alert('Debe seleccionar al menos un estudiante');
        return;
    }

    $.ajax({
        url: '{{ route("admin.parents.assign-children") }}',
        method: 'POST',
        data: {
            _token: $('meta[name="csrf-token"]').attr('content'),
            parent_id: parentId,
            student_ids: studentIds
        },
        success: function(response) {
            $('#assignChildrenModal').modal('hide');
            if (response.success) {
                alert('Éxito: ' + response.message);
                location.reload();
            } else {
                alert('Error: ' + response.message);
            }
        },
        error: function(xhr) {
            $('#assignChildrenModal').modal('hide');
            alert('Error al asignar estudiantes');
        }
    });
});

function removeRelationship(relationshipId) {
    if (confirm('¿Está seguro que desea desasignar este hijo?\n\nEsta acción eliminará la relación y el padre ya no podrá ver la información del estudiante.')) {
        $.ajax({
            url: '{{ route("admin.parents.remove-relationship") }}',
            method: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                relationship_id: relationshipId
            },
            success: function(response) {
                if (response.success) {
                    alert('Éxito: ' + response.message);
                    location.reload(); // Reload to update the relationships table
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr) {
                alert('Error al eliminar la relación');
                console.error(xhr.responseText);
            }
        });
    }
}
</script>
@stop