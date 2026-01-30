<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use App\Models\User;
use App\Models\History;
use App\Models\ParentProfile;
use App\Models\Colegio;
use App\Models\Setting;
use App\Models\Zona;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    public function showRegistrationForm()
    {
        $zonas = Zona::where('estado', 'activo')->orderBy('nombre')->get();

        return view('auth.register', compact('zonas'));
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'first_name' => ['required', 'string', 'max:100'],
            'last_name' => ['required', 'string', 'max:100'],
            'second_last_name' => ['nullable', 'string', 'max:100'],
            'cedula' => ['required', 'string', 'max:50'],
            'role' => ['required'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'colegio_id' => ['nullable', 'integer', 'exists:colegios,id'],
            'ruta_id' => ['nullable', 'integer', 'exists:settings,id'],
            'paradero_id' => ['nullable', 'integer', 'exists:paraderos,id'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        // Construir el nombre completo
        $fullName = trim($data['first_name'] . ' ' . $data['last_name'] . ' ' . ($data['second_last_name'] ?? ''));

        $createNew = User::create([
            'name' => $fullName,
            'first_name' => $data['first_name'],
            'last_name' => $data['last_name'],
            'second_last_name' => $data['second_last_name'] ?? null,
            'cedula' => $data['cedula'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'role' => $data['role'],
            'image' => 'default-user.png'
        ]);

        $createNew->roles()->attach($data['role']);

        // Si es estudiante (role=3), crear History con status=2 (pendiente)
        if ($data['role'] == 3) {
            $historyData = [
                'user_id' => $createNew->id,
                'name' => $fullName,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'second_last_name' => $data['second_last_name'] ?? null,
                'cedula' => $data['cedula'],
                'email' => $data['email'],
                'status' => 2, // Pendiente
                'creditos' => 0,
                'cuantoRestar' => 0,
                'chancesParaMarcar' => 0,
            ];

            if (!empty($data['colegio_id'])) {
                $historyData['colegio_id'] = $data['colegio_id'];
                $colegio = Colegio::find($data['colegio_id']);
                if ($colegio) {
                    $historyData['colegio'] = $colegio->nombre;
                }
            }

            if (!empty($data['ruta_id'])) {
                $historyData['ruta_id'] = $data['ruta_id'];
            }

            if (!empty($data['paradero_id'])) {
                $historyData['paradero_id'] = $data['paradero_id'];
            }

            History::create($historyData);
        }

        // Si es padre (role=4), crear ParentProfile con cedula
        if ($data['role'] == 4) {
            ParentProfile::create([
                'user_id' => $createNew->id,
                'cedula' => $data['cedula'],
            ]);
        }

        return $createNew;
    }
}