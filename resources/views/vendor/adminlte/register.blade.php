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
            <img src="https://greenland.ga/logo/logo2.png" alt="BeeBus" class="", height="130" width="300" style="opacity: .8">
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
    @stack('js')
    @yield('js')
@stop
