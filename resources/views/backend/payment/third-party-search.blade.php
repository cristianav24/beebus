@extends('adminlte::master')

@section('adminlte_css_pre')
    <link rel="stylesheet" href="{{ asset('vendor/icheck-bootstrap/icheck-bootstrap.min.css') }}">
@stop

@section('classes_body', 'login-page')

@section('body')
    <div class="login-box third-party-box">
        <div class="login-logo">
            <img src="https://greenland.ga/logo/logo2.png" alt="BeeBus" class="logo-img" style="opacity: .8">
        </div>
        <div class="card">
            <div class="card-header bg-primary">
                <h3 class="card-title mb-0">
                    <i class="fas fa-search mr-2"></i>
                    Recargar Créditos a un Estudiante
                </h3>
            </div>
            <div class="card-body">
                <p class="text-muted mb-4">
                    Busque al estudiante por su número de cédula o DNI para realizar una recarga de créditos.
                </p>

                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show">
                        <button type="button" class="close" data-dismiss="alert">&times;</button>
                        {{ session('error') }}
                    </div>
                @endif

                <form id="searchForm">
                    <div class="form-group">
                        <label for="cedula">Cédula o DNI del Estudiante</label>
                        <div class="input-group">
                            <input type="text"
                                   class="form-control"
                                   id="cedula"
                                   name="cedula"
                                   placeholder="Ej: 123456789"
                                   required
                                   autofocus>
                            <div class="input-group-append">
                                <div class="input-group-text">
                                    <span class="fas fa-id-card"></span>
                                </div>
                            </div>
                        </div>
                        <small class="form-text text-muted">
                            Ingrese el número de cédula o DNI completo del estudiante
                        </small>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block" id="searchBtn">
                        <i class="fas fa-search mr-2"></i>
                        Buscar Estudiante
                    </button>
                </form>

                <div id="studentResult" class="mt-4" style="display: none;">
                    <div class="alert alert-success">
                        <h5><i class="icon fas fa-check"></i> ¡Estudiante Encontrado!</h5>
                        <hr>
                        <div class="row">
                            <div class="col-md-6">
                                <strong>Nombre:</strong><br>
                                <span id="studentName"></span>
                            </div>
                            <div class="col-md-6">
                                <strong>Cédula:</strong><br>
                                <span id="studentCedula"></span>
                            </div>
                        </div>
                        <div class="row mt-2">
                            <div class="col-md-6">
                                <strong>Colegio:</strong><br>
                                <span id="studentColegio"></span>
                            </div>
                            <div class="col-md-6">
                                <strong>Créditos Actuales:</strong><br>
                                <span id="studentCredits" class="badge badge-info"></span>
                            </div>
                        </div>
                    </div>

                    <a href="#" id="continueBtn" class="btn btn-success btn-block btn-lg">
                        <i class="fas fa-credit-card mr-2"></i>
                        Continuar con la Recarga
                    </a>
                </div>

                <div id="errorResult" class="mt-4" style="display: none;">
                    <div class="alert alert-danger">
                        <h5><i class="icon fas fa-ban"></i> Error</h5>
                        <span id="errorMessage"></span>
                    </div>
                </div>

                <div class="mt-3 text-center">
                    <a href="{{ route('login') }}" class="btn btn-link">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Volver al Login
                    </a>
                </div>
            </div>
        </div>
    </div>
@stop

@section('adminlte_css')
    @stack('css')
    @yield('css')
    <style>
        .third-party-box {
            width: 100%;
            max-width: 500px;
            padding: 0 15px;
            margin: 20px auto;
        }

        .logo-img {
            max-width: 100%;
            height: auto;
            max-height: 130px;
        }

        .login-logo {
            text-align: center;
            margin-bottom: 20px;
        }

        .card {
            box-shadow: 0 0 1px rgba(0,0,0,.125), 0 1px 3px rgba(0,0,0,.2);
            border-radius: 10px;
        }

        #studentResult .row {
            margin: 0 -5px;
        }

        #studentResult .row > div {
            padding: 0 5px;
        }

        @media (max-width: 576px) {
            .third-party-box {
                padding: 0 10px;
                margin-top: 10px;
            }

            .logo-img {
                max-height: 100px;
                width: auto;
            }

            .card-header h3.card-title {
                font-size: 1rem;
            }

            .card-body {
                padding: 1rem;
            }

            #studentResult .col-md-6 {
                margin-bottom: 10px;
            }

            #studentResult .alert h5 {
                font-size: 1rem;
            }

            .btn-block {
                padding: 0.75rem 1rem;
            }

            .btn-lg {
                font-size: 1rem;
                padding: 0.75rem 1.5rem;
            }
        }
    </style>
@stop

@section('adminlte_js')
    <script>
        $(document).ready(function() {
            $('#searchForm').on('submit', function(e) {
                e.preventDefault();

                var cedula = $('#cedula').val();
                var $btn = $('#searchBtn');
                var originalText = $btn.html();

                // Hide previous results
                $('#studentResult').hide();
                $('#errorResult').hide();

                // Show loading state
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-2"></i>Buscando...');

                $.ajax({
                    url: '{{ route('third-party.search-student') }}',
                    method: 'POST',
                    data: {
                        cedula: cedula,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            // Show student data
                            $('#studentName').text(response.data.name);
                            $('#studentCedula').text(response.data.cedula);
                            $('#studentColegio').text(response.data.colegio || 'No especificado');
                            $('#studentCredits').text('₡' + response.data.creditos.toLocaleString());
                            $('#studentResult').fadeIn();

                            // Update continue button link
                            $('#continueBtn').attr('href', '{{ route('third-party.recharge') }}');
                        }
                    },
                    error: function(xhr) {
                        var message = 'No se encontró ningún estudiante con la cédula proporcionada.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        $('#errorMessage').text(message);
                        $('#errorResult').fadeIn();
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html(originalText);
                    }
                });
            });
        });
    </script>
@stop
