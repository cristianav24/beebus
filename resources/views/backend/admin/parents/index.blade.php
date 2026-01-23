@extends('adminlte::page')

@section('title', 'Gestión de Padres - ' . Config::get('adminlte.title'))

@section('content_header')
<div class="d-flex justify-content-between align-items-center">
    <h1>
        <i class="fas fa-users"></i> Gestión de Padres
    </h1>
    <a href="{{ route('admin.parents.create') }}" class="btn btn-success">
        <i class="fas fa-plus"></i> Crear Nuevo Padre
    </a>
</div>
@stop

@section('content')
@include('layouts.flash-message')

<!-- Statistics Cards -->
<div class="row mb-4">
    @php
        $totalParents = \App\Models\User::where('role', '4')->count();
        $completeProfiles = \App\Models\User::where('role', '4')
            ->whereHas('parentProfile', function($query) {
                $query->whereNotNull('telefono')
                      ->whereNotNull('cedula')
                      ->whereNotNull('direccion')
                      ->whereNotNull('provincia')
                      ->whereNotNull('canton');
            })->count();
        $incompleteProfiles = $totalParents - $completeProfiles;
        $withChildren = \App\Models\ParentChildRelationship::where('status', 'approved')
            ->distinct('parent_user_id')->count('parent_user_id');
    @endphp
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $totalParents }}</h3>
                <p>Total de Padres</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>{{ $completeProfiles }}</h3>
                <p>Perfiles Completos</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-check"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $incompleteProfiles }}</h3>
                <p>Perfiles Incompletos</p>
            </div>
            <div class="icon">
                <i class="fas fa-user-edit"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h3>{{ $withChildren }}</h3>
                <p>Padres con Hijos Asignados</p>
            </div>
            <div class="icon">
                <i class="fas fa-child"></i>
            </div>
        </div>
    </div>
</div>

<!-- Main Table Card -->
<div class="card card-primary">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-table"></i> Lista de Padres
        </h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            {!! $html->table(['class' => 'table table-striped table-bordered table-hover']) !!}
        </div>
    </div>
</div>

<!-- Assign Children Modal -->
<div class="modal fade" id="assignChildrenModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success">
                <h5 class="modal-title">
                    <i class="fas fa-user-plus"></i> Asignar Hijos a <span id="parent-name"></span>
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="assignChildrenForm">
                    @csrf
                    <input type="hidden" id="parent_id" name="parent_id">
                    
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
                        <input type="hidden" name="student_ids" id="student_ids">
                    </div>
                </form>
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

<!-- Delete Modal -->
<div class="modal fade" id="deleteModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h5 class="modal-title">
                    <i class="fas fa-trash"></i> Eliminar Padre
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>¿Está seguro que desea eliminar al padre <span id="delete-parent-name"></span>?</strong>
                    <br><br>
                    Esta acción no se puede deshacer. El padre no podrá acceder más al sistema.
                </div>
                <input type="hidden" id="delete_parent_id">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-danger" id="confirmDelete">
                    <i class="fas fa-trash"></i> Eliminar Padre
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
        justify-content: between;
        align-items: center;
    }
    
    .remove-student {
        color: #dc3545;
        cursor: pointer;
        margin-left: auto;
    }
</style>
@stop

@section('js')
{!! $html->scripts() !!}
<script>
let selectedStudents = [];

$(document).ready(function() {
    // Handle assign children button click
    $(document).on('click', '.assign-children-btn', function() {
        const parentId = $(this).data('id');
        const parentName = $(this).data('name');
        
        $('#parent_id').val(parentId);
        $('#parent-name').text(parentName);
        $('#assignChildrenModal').modal('show');
        
        // Reset form
        selectedStudents = [];
        updateSelectedStudentsList();
        $('#student_search').val('');
        $('#students_results').hide();
    });
    
    // Handle delete button click
    $(document).on('click', '.delete-btn', function() {
        const parentId = $(this).data('id');
        const parentName = $(this).data('name');
        
        $('#delete_parent_id').val(parentId);
        $('#delete-parent-name').text(parentName);
        $('#deleteModal').modal('show');
    });
    
    // Student search functionality
    $('#student_search').on('input', function() {
        const searchTerm = $(this).val();
        
        if (searchTerm.length >= 2) {
            searchStudents(searchTerm);
        } else {
            $('#students_results').hide();
        }
    });
    
    // Confirm assign children
    $('#confirmAssign').click(function() {
        const parentId = $('#parent_id').val();
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
                    window.LaravelDataTables["dataTableBuilder"].draw();
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
    
    // Confirm delete parent
    $('#confirmDelete').click(function() {
        const parentId = $('#delete_parent_id').val();
        
        $.ajax({
            url: '/admin/parents/' + parentId,
            method: 'DELETE',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                $('#deleteModal').modal('hide');
                if (response.success) {
                    alert('Éxito: ' + response.message);
                    window.LaravelDataTables["dataTableBuilder"].draw();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function(xhr) {
                $('#deleteModal').modal('hide');
                alert('Error al eliminar el padre');
            }
        });
    });
    
    // Clear modals on hide
    $('#assignChildrenModal').on('hidden.bs.modal', function() {
        selectedStudents = [];
        updateSelectedStudentsList();
        $('#student_search').val('');
        $('#students_results').hide();
    });
});

function searchStudents(searchTerm) {
    $.ajax({
        url: '{{ route("admin.parents.search-students") }}',
        method: 'GET',
        data: { search: searchTerm },
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
        // Remove student
        selectedStudents.splice(existingIndex, 1);
    } else {
        // Add student
        selectedStudents.push({ id, name, cedula, colegio });
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
    
    // Update hidden input
    const studentIds = selectedStudents.map(student => student.id);
    $('#student_ids').val(JSON.stringify(studentIds));
}

function removeSelectedStudent(studentId) {
    const index = selectedStudents.findIndex(student => student.id === studentId);
    if (index > -1) {
        selectedStudents.splice(index, 1);
        updateSelectedStudentsList();
        
        // Update visual state in search results
        $(`.student-item[onclick*="${studentId}"]`).removeClass('selected');
    }
}
</script>
@stop