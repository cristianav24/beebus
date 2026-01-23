@extends('adminlte::page')

@section('title', 'Dashboard Padre - ' . Config::get('adminlte.title'))

@section('content_header')
<h1>Dashboard de Padre</h1>
@stop

@section('content')
@include('layouts.flash-message')

<!-- Statistics Cards -->
<div class="row">
    <div class="col-lg-3 col-6" id="stats-students">
        <div class="small-box bg-info">
            <div class="inner">
                <h3>{{ $statistics['total_students'] }}</h3>
                <p>Hijos Registrados</p>
            </div>
            <div class="icon">
                <i class="fas fa-users"></i>
            </div>
        </div>
    </div>
    
    <div class="col-lg-3 col-6" id="stats-credits">
        <div class="small-box bg-success">
            <div class="inner">
                <h3>₡{{ number_format($statistics['total_credits'], 0, ',', '.') }}</h3>
                <p>Créditos Totales</p>
            </div>
            <div class="icon">
                <i class="fas fa-coins"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6" id="stats-recharges">
        <div class="small-box bg-warning">
            <div class="inner">
                <h3>{{ $statistics['total_recharges'] }}</h3>
                <p>Recargas Realizadas</p>
            </div>
            <div class="icon">
                <i class="fas fa-credit-card"></i>
            </div>
        </div>
    </div>

    <div class="col-lg-3 col-6" id="stats-pending">
        <div class="small-box bg-danger">
            <div class="inner">
                <h3>{{ $statistics['pending_requests'] }}</h3>
                <p>Solicitudes Pendientes</p>
            </div>
            <div class="icon">
                <i class="fas fa-clock"></i>
            </div>
        </div>
    </div>
</div>

@if($approvedRelationships->isEmpty() && $pendingRelationships->isEmpty())
<!-- Welcome Message for New Parents -->
<div class="row" id="welcome-section">
    <div class="col-12">
        <div class="card card-primary card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-hand-wave text-primary"></i> ¡Bienvenido al Sistema de Padres!
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-8">
                        <h4>¡Hola! Parece que es tu primera vez aquí.</h4>
                        <p class="lead">Para comenzar a ver la información de tus hijos, necesitas asociar sus cuentas con la tuya.</p>
                        <p>El proceso es simple:</p>
                        <ol>
                            <li><strong>Busca a tus hijos:</strong> Usa el nombre o cédula para encontrarlos en el sistema</li>
                            <li><strong>Selecciona:</strong> Marca todos los estudiantes que son tus hijos</li>
                            <li><strong>Solicita:</strong> Envía la solicitud al administrador</li>
                            <li><strong>Espera aprobación:</strong> Un administrador verificará y aprobará la relación</li>
                        </ol>
                        <div class="mt-4">
                            <button class="btn btn-primary btn-lg" id="start-assignment">
                                <i class="fas fa-search"></i> Buscar y Asignar Mis Hijos
                            </button>
                            <button class="btn btn-info btn-lg ml-2" id="take-tour">
                                <i class="fas fa-question-circle"></i> Tour del Sistema
                            </button>
                        </div>
                    </div>
                    <div class="col-md-4 text-center">
                        <i class="fas fa-family text-muted" style="font-size: 8rem;"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@if($pendingRelationships->isNotEmpty())
<!-- Pending Requests -->
<div class="row" id="pending-section">
    <div class="col-12">
        <div class="card card-warning card-outline">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-clock"></i> Solicitudes Pendientes de Aprobación
                </h3>
            </div>
            <div class="card-body">
                <p class="text-muted">Las siguientes solicitudes están esperando aprobación del administrador:</p>
                <div class="row">
                    @foreach($pendingRelationships as $pending)
                    <div class="col-md-6 col-lg-4">
                        <div class="card card-outline card-warning">
                            <div class="card-body">
                                <h5 class="card-title">{{ $pending->student->name }}</h5>
                                <p class="card-text">
                                    <small class="text-muted">Cédula: {{ $pending->student->cedula }}</small><br>
                                    <small class="text-muted">Solicitado: {{ $pending->requested_at ? $pending->requested_at->format('d/m/Y H:i') : 'Fecha no disponible' }}</small>
                                </p>
                                <span class="badge badge-warning">Pendiente</span>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif

@if($approvedRelationships->isNotEmpty())
<!-- Approved Students -->
<div class="row" id="students-section">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h3 class="card-title">
                    <i class="fas fa-graduation-cap"></i> Mis Hijos
                </h3>
                <button class="btn btn-primary" id="add-more-children">
                    <i class="fas fa-plus"></i> Agregar Más Hijos
                </button>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach($approvedRelationships as $relationship)
                    @php $student = $relationship->student; @endphp
                    <div class="col-md-6 col-lg-4">
                        <div class="card card-primary card-outline student-card" data-student-id="{{ $student->id }}">
                            <div class="card-header">
                                <h5 class="card-title mb-0">{{ $student->name }}</h5>
                            </div>
                            <div class="card-body">
                                <p><strong>Cédula:</strong> {{ $student->cedula }}</p>
                                <p><strong>Colegio:</strong> {{ $student->colegio ? $student->colegio : ($student->colegio ?: 'No asignado') }}</p>
                                <p><strong>Sección:</strong> {{ $student->seccion ?: 'No asignada' }}</p>
                                <p><strong>Beca:</strong> {{ $student->beca ? $student->beca->nombre_beca : ($student->tipoBeca ?: 'Sin beca') }}</p>
                                
                                <hr>
                                
                                <div class="row text-center">
                                    <div class="col-6">
                                        <div class="description-block border-right">
                                            <h5 class="description-header text-success">₡{{ number_format($student->creditos, 0, ',', '.') }}</h5>
                                            <span class="description-text">CRÉDITOS</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <div class="description-block">
                                            <h5 class="description-header text-primary">{{ $student->chancesParaMarcar }}</h5>
                                            <span class="description-text">CHANCES</span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="mt-3">
                                    <a href="{{ route('parent.student-transactions', $student->id) }}" class="btn btn-primary btn-sm btn-block">
                                        <i class="fas fa-history"></i> Ver Historial de Transacciones
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</div>
@endif

<!-- Assignment Modal -->
<div class="modal fade" id="assignmentModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Buscar y Asignar Hijos</h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="assignment-modal-body">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

@stop

@section('css')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.css"/>
<!-- Toastr CSS -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11.23.0/dist/sweetalert2.min.css">

<style>
    .student-card {
        transition: transform 0.2s;
    }
    .student-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .description-block {
        margin-bottom: 10px;
    }
    
    .student-search-card {
        transition: all 0.2s;
        cursor: pointer;
    }

    .student-search-card:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .border-success {
        border-color: #28a745 !important;
        border-width: 2px !important;
    }
</style>
@stop

@section('js')
<script src="https://cdn.jsdelivr.net/npm/driver.js@latest/dist/driver.js.iife.js"></script>
<!-- Toastr JS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.23.0/dist/sweetalert2.all.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    
    // Initialize Driver.js for onboarding
    const driver = window.driver.js.driver;
    const driverJS = driver({
        showProgress: true,
        allowClose: true,
        animate: true,
        overlayClickNext: false,
        stagePadding: 4,
        stageRadius: 10,
        steps: [
            {
                element: '#stats-students',
                popover: {
                    title: 'Hijos Registrados',
                    description: 'Aquí puedes ver cuántos hijos tienes registrados en el sistema.',
                    side: 'bottom',
                    align: 'start'
                }
            },
            {
                element: '#stats-credits',
                popover: {
                    title: 'Créditos Totales',
                    description: 'La suma total de créditos de todos tus hijos.',
                    side: 'bottom',
                    align: 'start'
                }
            },
            {
                element: '#stats-recharges',
                popover: {
                    title: 'Recargas Realizadas',
                    description: 'Número total de recargas de créditos realizadas.',
                    side: 'bottom',
                    align: 'start'
                }
            },
            @if($approvedRelationships->isNotEmpty())
            {
                element: '#students-section',
                popover: {
                    title: 'Información de tus Hijos',
                    description: 'Aquí puedes ver la información detallada de cada uno de tus hijos registrados.',
                    side: 'top',
                    align: 'start'
                }
            },
            {
                element: '.student-card:first',
                popover: {
                    title: 'Tarjeta de Estudiante',
                    description: 'Cada tarjeta muestra información importante: créditos actuales, colegio, y botón para ver transacciones.',
                    side: 'top',
                    align: 'start'
                }
            }
            @endif
        ]
    });

    // Event handlers
    document.getElementById('take-tour')?.addEventListener('click', function() {
        driverJS.drive();
    });

    document.getElementById('start-assignment')?.addEventListener('click', function() {
        loadAssignmentModal();
    });

    document.getElementById('add-more-children')?.addEventListener('click', function() {
        loadAssignmentModal();
    });

    function loadAssignmentModal() {
        // Load assignment form content directly
        const modalContent = `
            <div class="assign-children-form">
                <div class="row">
                    <div class="col-12">
                        <div class="form-group">
                            <label for="student-search">Buscar Estudiantes</label>
                            <div class="input-group">
                                <input type="text" id="student-search" class="form-control" 
                                       placeholder="Busca por nombre o cédula del estudiante..." 
                                       autocomplete="off">
                                <div class="input-group-append">
                                    <span class="input-group-text">
                                        <i class="fas fa-search"></i>
                                    </span>
                                </div>
                            </div>
                            <small class="form-text text-muted">
                                Escribe al menos 3 caracteres para buscar
                            </small>
                        </div>
                    </div>
                </div>

                <!-- Search Results -->
                <div id="search-results" class="mt-3" style="display: none;">
                    <h5>Resultados de búsqueda:</h5>
                    <div id="students-list" class="row">
                        <!-- Students will be loaded here -->
                    </div>
                </div>

                <!-- Selected Students -->
                <div id="selected-students" class="mt-4" style="display: none;">
                    <h5>Estudiantes Seleccionados:</h5>
                    <div id="selected-list" class="row">
                        <!-- Selected students will appear here -->
                    </div>
                    
                    <div class="mt-3">
                        <button type="button" id="submit-requests" class="btn btn-success btn-lg">
                            <i class="fas fa-paper-plane"></i> Enviar Solicitudes de Aprobación
                        </button>
                        <button type="button" id="clear-selection" class="btn btn-secondary ml-2">
                            <i class="fas fa-times"></i> Limpiar Selección
                        </button>
                    </div>
                </div>

                <!-- Loading indicator -->
                <div id="loading" class="text-center mt-3" style="display: none;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Buscando...</span>
                    </div>
                    <p class="mt-2">Buscando estudiantes...</p>
                </div>

                <!-- No results message -->
                <div id="no-results" class="alert alert-info mt-3" style="display: none;">
                    <i class="fas fa-info-circle"></i> No se encontraron estudiantes con ese criterio de búsqueda.
                </div>
            </div>
        `;
        
        document.getElementById('assignment-modal-body').innerHTML = modalContent;
        $('#assignmentModal').modal('show');
        initializeAssignmentForm();
    }

    function initializeAssignmentForm() {
        let selectedStudents = [];
        let searchTimeout;

        const searchInput = document.getElementById('student-search');
        const searchResults = document.getElementById('search-results');
        const studentsList = document.getElementById('students-list');
        const selectedSection = document.getElementById('selected-students');
        const selectedList = document.getElementById('selected-list');
        const loading = document.getElementById('loading');
        const noResults = document.getElementById('no-results');

        // Search functionality
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.trim();
            
            clearTimeout(searchTimeout);
            
            if (searchTerm.length < 3) {
                hideAllSections();
                return;
            }

            searchTimeout = setTimeout(function() {
                searchStudents(searchTerm);
            }, 500);
        });

        function searchStudents(searchTerm) {
            showLoading();
            
            fetch('{{ route("parent.search-students") }}?search=' + encodeURIComponent(searchTerm))
                .then(response => response.json())
                .then(data => {
                    hideLoading();
                    displaySearchResults(data.students);
                })
                .catch(error => {
                    hideLoading();
                    console.error('Error:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al buscar estudiantes. Por favor, intenta nuevamente.'
                    });
                });
        }

        function displaySearchResults(students) {
            studentsList.innerHTML = '';
            
            if (students.length === 0) {
                showNoResults();
                return;
            }

            hideNoResults();
            showSearchResults();

            students.forEach(student => {
                const isSelected = selectedStudents.some(s => s.id === student.id);
                const studentCard = createStudentCard(student, isSelected);
                studentsList.appendChild(studentCard);
            });
        }

        function createStudentCard(student, isSelected = false) {
            const div = document.createElement('div');
            div.className = 'col-md-6 col-lg-4 mb-3';
            
            div.innerHTML = `
                <div class="card ${isSelected ? 'border-success' : 'border-light'} student-search-card" data-student-id="${student.id}">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-start">
                            <div class="flex-grow-1">
                                <h6 class="card-title">${student.name}</h6>
                                <p class="card-text small">
                                    <strong>Cédula:</strong> ${student.cedula}<br>
                                    <strong>Colegio:</strong> ${student.colegio || 'No asignado'}<br>
                                    <strong>Sección:</strong> ${student.seccion || 'No asignada'}<br>
                                    <strong>Beca:</strong> ${student.beca}<br>
                                    <strong>Créditos:</strong> ₡${new Intl.NumberFormat().format(student.creditos)}
                                </p>
                            </div>
                            <div>
                                <button type="button" class="btn btn-sm ${isSelected ? 'btn-success' : 'btn-outline-primary'} select-student" 
                                        data-student='${JSON.stringify(student)}' ${isSelected ? 'disabled' : ''}>
                                    <i class="fas ${isSelected ? 'fa-check' : 'fa-plus'}"></i>
                                    ${isSelected ? 'Seleccionado' : 'Seleccionar'}
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;

            // Add event listener for selection
            const button = div.querySelector('.select-student');
            button.addEventListener('click', function() {
                selectStudent(student, button);
            });

            return div;
        }

        function selectStudent(student, button) {
            selectedStudents.push(student);
            
            button.innerHTML = '<i class="fas fa-check"></i> Seleccionado';
            button.className = 'btn btn-sm btn-success select-student';
            button.disabled = true;
            
            button.closest('.card').classList.add('border-success');
            
            updateSelectedStudentsDisplay();
        }

        function updateSelectedStudentsDisplay() {
            if (selectedStudents.length === 0) {
                selectedSection.style.display = 'none';
                return;
            }

            selectedSection.style.display = 'block';
            selectedList.innerHTML = '';

            selectedStudents.forEach((student, index) => {
                const div = document.createElement('div');
                div.className = 'col-md-6 col-lg-4 mb-2';
                
                div.innerHTML = `
                    <div class="card border-success">
                        <div class="card-body py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <strong>${student.name}</strong><br>
                                    <small class="text-muted">Cédula: ${student.cedula}</small>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-danger remove-student" data-index="${index}">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>
                    </div>
                `;

                const removeButton = div.querySelector('.remove-student');
                removeButton.addEventListener('click', function() {
                    removeStudent(index);
                });

                selectedList.appendChild(div);
            });
        }

        function removeStudent(index) {
            const removedStudent = selectedStudents.splice(index, 1)[0];
            updateSelectedStudentsDisplay();
            
            // Update the search results if visible
            const studentCard = document.querySelector(`[data-student-id="${removedStudent.id}"]`);
            if (studentCard) {
                const button = studentCard.querySelector('.select-student');
                button.innerHTML = '<i class="fas fa-plus"></i> Seleccionar';
                button.className = 'btn btn-sm btn-outline-primary select-student';
                button.disabled = false;
                studentCard.classList.remove('border-success');
            }
        }

        // Submit requests
        document.getElementById('submit-requests').addEventListener('click', function() {
            if (selectedStudents.length === 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Selección requerida',
                    text: 'Por favor, selecciona al menos un estudiante.'
                });
                return;
            }

            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';

            const studentIds = selectedStudents.map(s => s.id);
            
            fetch('{{ route("parent.request-relationship") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    student_ids: studentIds
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        toast: true,
                        position: 'top-end',
                        icon: 'success',
                        title: data.message,
                        showConfirmButton: false,
                        timer: 10000,
                        timerProgressBar: true,
                        didClose: () => {
                        // Se ejecuta cuando desaparece el toast
                        $('#assignmentModal').modal('hide');
                        location.reload(); 
                        }
                    });
                    $('#assignmentModal').modal('hide');
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al enviar las solicitudes. Por favor, intenta nuevamente.'
                    });
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al enviar las solicitudes. Por favor, intenta nuevamente.'
                });
            })
            .finally(() => {
                this.disabled = false;
                this.innerHTML = '<i class="fas fa-paper-plane"></i> Enviar Solicitudes de Aprobación';
            });
        });

        // Clear selection
        document.getElementById('clear-selection').addEventListener('click', function() {
            selectedStudents = [];
            updateSelectedStudentsDisplay();
            
            // Reset all select buttons in search results
            document.querySelectorAll('.select-student').forEach(button => {
                button.innerHTML = '<i class="fas fa-plus"></i> Seleccionar';
                button.className = 'btn btn-sm btn-outline-primary select-student';
                button.disabled = false;
                button.closest('.card').classList.remove('border-success');
            });
        });

        function showLoading() {
            loading.style.display = 'block';
            hideSearchResults();
            hideNoResults();
        }

        function hideLoading() {
            loading.style.display = 'none';
        }

        function showSearchResults() {
            searchResults.style.display = 'block';
        }

        function hideSearchResults() {
            searchResults.style.display = 'none';
        }

        function showNoResults() {
            noResults.style.display = 'block';
        }

        function hideNoResults() {
            noResults.style.display = 'none';
        }

        function hideAllSections() {
            hideLoading();
            hideSearchResults();
            hideNoResults();
        }
    }

    @if($approvedRelationships->isEmpty() && $pendingRelationships->isEmpty())
    // Auto-start tour for new users
    setTimeout(function() {
        Swal.fire({
            title: '¡Bienvenido!',
            text: '¿Te gustaría hacer un recorrido rápido por el sistema?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Sí, empezar tour',
            cancelButtonText: 'No, gracias'
        }).then((result) => {
            if (result.isConfirmed) {
                driverJS.drive();
            }
        });
    }, 2000);
    @endif
});
</script>
@stop