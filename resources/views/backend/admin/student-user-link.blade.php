@extends('adminlte::page')

@section('title', 'Vincular Estudiantes con Usuarios')

@section('content_header')
    <h1>Vincular Estudiantes con Usuarios</h1>
@stop

@section('content')
    @include('layouts.flash-message')

    <!-- Estadísticas -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $totalWithoutUser }}</h3>
                    <p>Estudiantes SIN Usuario</p>
                </div>
                <div class="icon">
                    <i class="fa fa-user-times"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ $totalWithUser }}</h3>
                    <p>Estudiantes CON Usuario</p>
                </div>
                <div class="icon">
                    <i class="fa fa-user-check"></i>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ $totalWithoutUser + $totalWithUser }}</h3>
                    <p>Total Estudiantes Activos</p>
                </div>
                <div class="icon">
                    <i class="fa fa-users"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Botón de vinculación automática -->
    <div class="card mb-4">
        <div class="card-header bg-warning">
            <h3 class="card-title"><i class="fa fa-magic"></i> Vinculación Automática</h3>
        </div>
        <div class="card-body">
            <p>Esta opción intentará vincular automáticamente los estudiantes con usuarios existentes que tengan el mismo email o cédula.</p>
            <button class="btn btn-warning" onclick="autoLinkAll()">
                <i class="fa fa-magic"></i> Vincular Automáticamente Todos
            </button>
        </div>
    </div>

    <!-- Tabla de estudiantes sin usuario -->
    <div class="card">
        <div class="card-header">
            <h3 class="card-title"><i class="fa fa-list"></i> Estudiantes sin Usuario Vinculado</h3>
        </div>
        <div class="card-body">
            {!! $html->table(['class' => 'table table-bordered table-hover', 'id' => 'students-table']) !!}
        </div>
    </div>

    <!-- Modal para buscar usuario -->
    <div class="modal fade" id="searchUserModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Buscar Usuario para Vincular</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="modal_history_id">

                    <div class="form-group">
                        <label>Buscar usuario:</label>
                        <input type="text" id="search_user_input" class="form-control" placeholder="Buscar por nombre, email o cédula...">
                    </div>

                    <div id="suggested_users" class="mb-3">
                        <!-- Usuarios sugeridos aparecerán aquí -->
                    </div>

                    <div id="search_results">
                        <!-- Resultados de búsqueda aparecerán aquí -->
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
<link rel="stylesheet" type="text/css" href="{{ asset('vendor/datatables/css/dataTables.bootstrap4.min.css') }}">
@stop

@section('js')
<script src="{{ asset('vendor/datatables/js/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('vendor/datatables/js/dataTables.bootstrap4.min.js') }}"></script>
{!! $html->scripts() !!}

<script>
    var currentHistoryId = null;

    function searchUser(historyId, email, cedula) {
        currentHistoryId = historyId;
        $('#modal_history_id').val(historyId);
        $('#search_user_input').val('');
        $('#search_results').html('');

        // Buscar usuarios sugeridos por email/cédula
        $.ajax({
            url: '{{ route("admin.student-user-link.search") }}',
            method: 'GET',
            data: { email: email, cedula: cedula },
            success: function(response) {
                var html = '';
                if (response.users.length > 0) {
                    html = '<h6>Usuarios sugeridos (coinciden email/cédula):</h6>';
                    html += '<div class="list-group">';
                    response.users.forEach(function(user) {
                        html += '<a href="#" class="list-group-item list-group-item-action" onclick="linkUser(' + user.id + ')">';
                        html += '<strong>' + user.name + '</strong><br>';
                        html += '<small>Email: ' + user.email + ' | Cédula: ' + (user.cedula || 'N/A') + '</small>';
                        html += '</a>';
                    });
                    html += '</div>';
                } else {
                    html = '<div class="alert alert-info">No se encontraron usuarios con ese email o cédula. Puedes buscar manualmente o crear uno nuevo.</div>';
                }
                $('#suggested_users').html(html);
            }
        });

        $('#searchUserModal').modal('show');
    }

    // Búsqueda manual
    $('#search_user_input').on('keyup', function() {
        var search = $(this).val();
        if (search.length < 2) {
            $('#search_results').html('');
            return;
        }

        $.ajax({
            url: '{{ route("admin.student-user-link.search") }}',
            method: 'GET',
            data: { search: search },
            success: function(response) {
                var html = '';
                if (response.users.length > 0) {
                    html = '<h6>Resultados de búsqueda:</h6>';
                    html += '<div class="list-group">';
                    response.users.forEach(function(user) {
                        html += '<a href="#" class="list-group-item list-group-item-action" onclick="linkUser(' + user.id + ')">';
                        html += '<strong>' + user.name + '</strong><br>';
                        html += '<small>Email: ' + user.email + ' | Cédula: ' + (user.cedula || 'N/A') + '</small>';
                        html += '</a>';
                    });
                    html += '</div>';
                } else {
                    html = '<div class="alert alert-warning">No se encontraron usuarios.</div>';
                }
                $('#search_results').html(html);
            }
        });
    });

    function linkUser(userId) {
        if (!confirm('¿Vincular este usuario al estudiante?')) return;

        $.ajax({
            url: '{{ route("admin.student-user-link.link") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                history_id: currentHistoryId,
                user_id: userId
            },
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    $('#searchUserModal').modal('hide');
                    $('#students-table').DataTable().ajax.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Error al vincular usuario');
            }
        });
    }

    function createUser(historyId) {
        if (!confirm('¿Crear un nuevo usuario para este estudiante?\n\nSe creará con:\n- Email: cédula@beebus.com (o ID único si no tiene cédula)\n- Contraseña: cédula (o ID único)')) return;

        $.ajax({
            url: '{{ route("admin.student-user-link.create") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                history_id: historyId
            },
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    $('#students-table').DataTable().ajax.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Error al crear usuario');
            }
        });
    }

    function autoLinkAll() {
        if (!confirm('¿Vincular automáticamente todos los estudiantes con usuarios existentes que coincidan por email o cédula?')) return;

        $.ajax({
            url: '{{ route("admin.student-user-link.auto") }}',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}'
            },
            success: function(response) {
                if (response.success) {
                    alert(response.message);
                    location.reload();
                } else {
                    alert('Error: ' + response.message);
                }
            },
            error: function() {
                alert('Error al vincular automáticamente');
            }
        });
    }
</script>
@stop
