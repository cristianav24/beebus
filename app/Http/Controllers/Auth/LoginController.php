<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\History;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    /**
     * Ejecutado despues de autenticar exitosamente.
     * Bloquea el login si el estudiante esta inactivo (status 0).
     */
    protected function authenticated(Request $request, $user)
    {
        // Solo verificar estudiantes (role 3)
        if ($user->role == 3) {
            $student = History::where('user_id', $user->id)->first();

            if ($student && $student->status == 0) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return redirect()->route('login')
                    ->withErrors(['email' => 'Tu cuenta de estudiante se encuentra inactiva. Contacta al administrador.']);
            }
        }
    }
}
