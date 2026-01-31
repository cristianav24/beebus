@extends('adminlte::master')

@section('adminlte_css')
@stack('css')
@yield('css')
@stop

@section('classes_body', 'register-page')

@php( $login_url = View::getSection('login_url') ?? config('adminlte.login_url', 'login') )
@php( $register_url = View::getSection('register_url') ?? config('adminlte.register_url', 'register') )
@php( $dashboard_url = View::getSection('dashboard_url') ?? config('adminlte.dashboard_url', 'home') )

@if (config('adminlte.use_route_url', false))
@php( $login_url = $login_url ? route($login_url) : '' )
@php( $register_url = $register_url ? route($register_url) : '' )
@php( $dashboard_url = $dashboard_url ? route($dashboard_url) : '' )
@else
@php( $login_url = $login_url ? url($login_url) : '' )
@php( $register_url = $register_url ? url($register_url) : '' )
@php( $dashboard_url = $dashboard_url ? url($dashboard_url) : '' )
@endif

@section('body')
<div class="register-box">
    <div class="register-logo">
        <img src="https://greenland.ga/logo/logo2.png" alt="BeeBus" class="" , height="130" width="300" style="opacity: .8">
    </div>
    <div class="card">
        <div class="card-body register-card-body">
            <p class="login-box-msg">{{ __('adminlte::adminlte.register_message') }}</p>
            <form action="{{ $register_url }}" method="post">
                {{ csrf_field() }}

                <div class="input-group mb-3">
                    <input type="text" name="first_name" class="form-control {{ $errors->has('first_name') ? 'is-invalid' : '' }}"
                        value="{{ old('first_name') }}" placeholder="Nombre(s)" autofocus required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-user"></span>
                        </div>
                    </div>
                    @if ($errors->has('first_name'))
                    <div class="invalid-feedback">
                        <strong>{{ $errors->first('first_name') }}</strong>
                    </div>
                    @endif
                </div>

                <div class="input-group mb-3">
                    <input type="text" name="last_name" class="form-control {{ $errors->has('last_name') ? 'is-invalid' : '' }}"
                        value="{{ old('last_name') }}" placeholder="Primer Apellido" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-user"></span>
                        </div>
                    </div>
                    @if ($errors->has('last_name'))
                    <div class="invalid-feedback">
                        <strong>{{ $errors->first('last_name') }}</strong>
                    </div>
                    @endif
                </div>

                <div class="input-group mb-3">
                    <input type="text" name="second_last_name" class="form-control {{ $errors->has('second_last_name') ? 'is-invalid' : '' }}"
                        value="{{ old('second_last_name') }}" placeholder="Segundo Apellido (opcional)">
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-user"></span>
                        </div>
                    </div>
                    @if ($errors->has('second_last_name'))
                    <div class="invalid-feedback">
                        <strong>{{ $errors->first('second_last_name') }}</strong>
                    </div>
                    @endif
                </div>

                <div class="input-group mb-3">
                    <input type="text" name="cedula" class="form-control {{ $errors->has('cedula') ? 'is-invalid' : '' }}"
                        value="{{ old('cedula') }}" placeholder="Cédula" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-id-card"></span>
                        </div>
                    </div>
                    @if ($errors->has('cedula'))
                    <div class="invalid-feedback">
                        <strong>{{ $errors->first('cedula') }}</strong>
                    </div>
                    @endif
                </div>

                <div class="input-group mb-3">
                    <input type="email" name="email" class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                        value="{{ old('email') }}" placeholder="{{ __('adminlte::adminlte.email') }}" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-envelope"></span>
                        </div>
                    </div>
                    @if ($errors->has('email'))
                    <div class="invalid-feedback">
                        <strong>{{ $errors->first('email') }}</strong>
                    </div>
                    @endif
                </div>

                <div class="input-group mb-3">
                    <input type="password" name="password" class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                        placeholder="{{ __('adminlte::adminlte.password') }}" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                    @if ($errors->has('password'))
                    <div class="invalid-feedback">
                        <strong>{{ $errors->first('password') }}</strong>
                    </div>
                    @endif
                </div>

                <div class="input-group mb-3">
                    <input type="password" name="password_confirmation" class="form-control {{ $errors->has('password_confirmation') ? 'is-invalid' : '' }}"
                        placeholder="{{ __('adminlte::adminlte.retype_password') }}" required>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-lock"></span>
                        </div>
                    </div>
                    @if ($errors->has('password_confirmation'))
                    <div class="invalid-feedback">
                        <strong>{{ $errors->first('password_confirmation') }}</strong>
                    </div>
                    @endif
                </div>

                <p class="login-box-msg mb-2">Seleccione su Role</p>
                <div class="input-group mb-3">
                    <select name="role" id="role" class="form-control {{ $errors->has('role') ? 'is-invalid' : '' }}" required>
                        <option value="3" {{ old('role') == 3 ? 'selected' : '' }}>Estudiante</option>
                        <option value="4" {{ old('role') == 4 ? 'selected' : '' }}>Padre</option>
                    </select>
                    <div class="input-group-append">
                        <div class="input-group-text">
                            <span class="fas fa-users"></span>
                        </div>
                    </div>
                    @if ($errors->has('role'))
                    <div class="invalid-feedback">
                        <strong>{{ $errors->first('role') }}</strong>
                    </div>
                    @endif
                </div>

                {{-- Campos extra para estudiantes --}}
                <div id="student-fields" style="{{ old('role', 3) == 3 ? '' : 'display:none;' }}">
                    <p class="login-box-msg mb-2">Datos del Estudiante</p>

                    {{-- ZONA --}}
                    <div class="input-group mb-3">
                        <select name="zona_id" id="zona_id" class="form-control">
                            <option value="">Seleccione su Zona</option>
                            @foreach($zonas as $zona)
                            <option value="{{ $zona->id }}">{{ $zona->nombre }}</option>
                            @endforeach
                        </select>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-map-marker-alt"></span>
                            </div>
                        </div>
                    </div>

                    {{-- COLEGIO --}}
                    <div class="input-group mb-3">
                        <select name="colegio_id" id="colegio_id" class="form-control" disabled>
                            <option value="">-- Primero seleccione una zona --</option>
                        </select>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-school"></span>
                            </div>
                        </div>
                    </div>

                    {{-- RUTA --}}
                    <div class="input-group mb-3">
                        <select name="ruta_id" id="ruta_id" class="form-control" disabled>
                            <option value="">-- Primero seleccione un colegio --</option>
                        </select>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-bus"></span>
                            </div>
                        </div>
                    </div>

                    {{-- PARADERO --}}
                    <div class="input-group mb-3">
                        <select name="paradero_id" id="paradero_id" class="form-control" disabled>
                            <option value="">-- Primero seleccione una ruta --</option>
                        </select>
                        <div class="input-group-append">
                            <div class="input-group-text">
                                <span class="fas fa-map-pin"></span>
                            </div>
                        </div>
                    </div>
                    <div id="paradero-monto-info" style="text-align:center; margin-bottom: 10px; font-weight: bold; display:none"></div>
                </div>

                <button type="submit" class="btn btn-primary btn-block btn-flat">
                    {{ __('adminlte::adminlte.register') }}
                </button>
            </form>
            <p class="mt-2 mb-1">
                <a href="{{ $login_url }}">
                    {{ __('adminlte::adminlte.i_already_have_a_membership') }}
                </a>
            </p>
        </div>
    </div>
</div><!-- /.register-box -->
@stop

@section('adminlte_js')
<script src="{{ asset('vendor/adminlte/dist/js/adminlte.min.js') }}"></script>
<script>
    var apiBase = "{{ url('/api') }}";

    // Role toggle
    document.getElementById('role').addEventListener('change', function() {
        document.getElementById('student-fields').style.display = this.value == '3' ? '' : 'none';
    });

    // ========== CASCADA ==========

    document.getElementById('zona_id').addEventListener('change', function() {
        var zonaId = this.value;
        var colegioSel = document.getElementById('colegio_id');
        var rutaSel = document.getElementById('ruta_id');
        var paraderoSel = document.getElementById('paradero_id');
        var montoInfo = document.getElementById('paradero-monto-info');

        // Reset dependientes
        colegioSel.innerHTML = '<option value="">Cargando...</option>';
        colegioSel.disabled = true;
        rutaSel.innerHTML = '<option value="">-- Primero seleccione un colegio --</option>';
        rutaSel.disabled = true;
        paraderoSel.innerHTML = '<option value="">-- Primero seleccione una ruta --</option>';
        paraderoSel.disabled = true;
        montoInfo.innerHTML = '';

        if (!zonaId) {
            colegioSel.innerHTML = '<option value="">-- Primero seleccione una zona --</option>';
            return;
        }

        fetch(apiBase + '/zonas/' + zonaId + '/colegios')
            .then(function(r) {
                return r.json();
            })
            .then(function(data) {
                colegioSel.innerHTML = '<option value="">Seleccione su Colegio</option>';
                data.forEach(function(c) {
                    var opt = document.createElement('option');
                    opt.value = c.id;
                    opt.textContent = c.nombre;
                    colegioSel.appendChild(opt);
                });
                colegioSel.disabled = false;
            });
    });

    document.getElementById('colegio_id').addEventListener('change', function() {
        var colegioId = this.value;
        var rutaSel = document.getElementById('ruta_id');
        var paraderoSel = document.getElementById('paradero_id');
        var montoInfo = document.getElementById('paradero-monto-info');

        rutaSel.innerHTML = '<option value="">Cargando...</option>';
        rutaSel.disabled = true;
        paraderoSel.innerHTML = '<option value="">-- Primero seleccione una ruta --</option>';
        paraderoSel.disabled = true;
        montoInfo.innerHTML = '';

        if (!colegioId) {
            rutaSel.innerHTML = '<option value="">-- Primero seleccione un colegio --</option>';
            return;
        }

        fetch(apiBase + '/colegios/' + colegioId + '/rutas')
            .then(function(r) {
                return r.json();
            })
            .then(function(data) {
                rutaSel.innerHTML = '<option value="">Seleccione su Ruta</option>';
                data.forEach(function(ruta) {
                    var opt = document.createElement('option');
                    opt.value = ruta.id;
                    var label = ruta.key_app;
                    opt.textContent = label;
                    rutaSel.appendChild(opt);
                });
                rutaSel.disabled = false;
            });
    });

    document.getElementById('ruta_id').addEventListener('change', function() {
        var rutaId = this.value;
        var paraderoSel = document.getElementById('paradero_id');
        var montoInfo = document.getElementById('paradero-monto-info');

        paraderoSel.innerHTML = '<option value="">Cargando...</option>';
        paraderoSel.disabled = true;
        montoInfo.innerHTML = '';

        if (!rutaId) {
            paraderoSel.innerHTML = '<option value="">-- Primero seleccione una ruta --</option>';
            return;
        }

        fetch(apiBase + '/rutas/' + rutaId + '/paraderos')
            .then(function(r) {
                return r.json();
            })
            .then(function(data) {
                if (data.length === 0) {
                    paraderoSel.innerHTML = '<option value="">No hay paraderos para esta ruta</option>';
                    return;
                }

                // Solo 1 paradero: auto-seleccionar
                if (data.length === 1) {
                    var p = data[0];
                    var label = p.nombre;
                    paraderoSel.innerHTML = '';
                    var opt = document.createElement('option');
                    opt.value = p.id;
                    opt.textContent = label;
                    opt.dataset.monto = p.monto;
                    opt.dataset.esBeca = p.es_beca_empresarial;
                    opt.selected = true;
                    paraderoSel.appendChild(opt);
                    paraderoSel.disabled = false;
                    showMontoInfo(p);
                    return;
                }

                // Varios paraderos: mostrar lista
                paraderoSel.innerHTML = '<option value="">Seleccione su Paradero</option>';
                data.forEach(function(p) {
                    var opt = document.createElement('option');
                    opt.value = p.id;
                    var label = p.nombre;
                    if (p.hora) label += ' (' + p.hora + ')';
                    opt.textContent = label;
                    opt.dataset.monto = p.monto;
                    opt.dataset.esBeca = p.es_beca_empresarial;
                    paraderoSel.appendChild(opt);
                });
                paraderoSel.disabled = false;
            });
    });

    document.getElementById('paradero_id').addEventListener('change', function() {
        var sel = this.options[this.selectedIndex];
        var montoInfo = document.getElementById('paradero-monto-info');
        if (sel && sel.value) {
            showMontoInfo({
                monto: sel.dataset.monto,
                es_beca_empresarial: sel.dataset.esBeca
            });
        } else {
            montoInfo.innerHTML = '';
        }
    });

    function showMontoInfo(p) {
        var montoInfo = document.getElementById('paradero-monto-info');
        if (p.es_beca_empresarial == '1' || p.es_beca_empresarial === true) {
            montoInfo.innerHTML = '<span style="color:#17a2b8;">BECA EMPRESARIAL - Sin costo</span>';
        } else {
            montoInfo.innerHTML = '<span style="color:#28a745;">Monto: ₡' + Number(p.monto).toLocaleString('es-CR') + '</span>';
        }
    }
</script>
@stack('js')
@yield('js')
@stop