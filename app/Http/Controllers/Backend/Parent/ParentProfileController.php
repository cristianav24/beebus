<?php

namespace App\Http\Controllers\Backend\Parent;

use App\Http\Controllers\Controller;
use App\Models\ParentProfile;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Auth;
use Config;

class ParentProfileController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show parent profile form
     */
    public function index()
    {
        $user = Auth::user();
        
        // Obtener o crear perfil de padre
        $parent = ParentProfile::findOrCreateForUser($user->id);
        
        // Lista de provincias de Costa Rica
        $provincias = [
            'San José',
            'Alajuela', 
            'Cartago',
            'Heredia',
            'Guanacaste',
            'Puntarenas',
            'Limón'
        ];

        return view('backend.parent.profile', compact('user', 'parent', 'provincias'));
    }

    /**
     * Update parent profile
     */
    public function update(Request $request)
    {
        $user = Auth::user();
        
        // Validación
        $validator = $this->validator($request->all());
        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            // Actualizar información básica del usuario
            $user->update([
                'name' => $request->name,
                'email' => $request->email
            ]);

            // Obtener o crear perfil de padre
            $parent = ParentProfile::findOrCreateForUser($user->id);

            // Actualizar información extendida del padre
            $parent->update([
                'telefono' => $request->telefono,
                'direccion' => $request->direccion,
                'provincia' => $request->provincia,
                'canton' => $request->canton,
                'distrito' => $request->distrito,
                'cedula' => $request->cedula,
                'ocupacion' => $request->ocupacion,
                'correo_secundario' => $request->correo_secundario
            ]);

            return redirect()->route('parent.profile')
                ->with('success', 'Perfil actualizado correctamente.');

        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'Error al actualizar el perfil: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Validator for parent profile data
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            // Información básica del usuario
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . Auth::id(),
            
            // Información extendida del padre
            'telefono' => 'required|string|max:20|regex:/^[0-9\-\s\+\(\)]+$/',
            'direccion' => 'required|string|max:255',
            'provincia' => 'required|string|max:100',
            'canton' => 'required|string|max:100',
            'distrito' => 'nullable|string|max:100',
            'cedula' => 'required|string|max:20|regex:/^[0-9\-]+$/',
            'ocupacion' => 'nullable|string|max:100',
            'correo_secundario' => 'nullable|string|email|max:150'
        ], [
            // Mensajes personalizados
            'name.required' => 'El nombre completo es obligatorio.',
            'email.required' => 'El correo electrónico es obligatorio.',
            'email.email' => 'El formato del correo electrónico no es válido.',
            'email.unique' => 'Este correo electrónico ya está en uso.',
            'telefono.required' => 'El número de teléfono es obligatorio.',
            'telefono.regex' => 'El formato del teléfono no es válido.',
            'direccion.required' => 'La dirección es obligatoria.',
            'provincia.required' => 'La provincia es obligatoria.',
            'canton.required' => 'El cantón es obligatorio.',
            'cedula.required' => 'La cédula de identidad es obligatoria.',
            'cedula.regex' => 'El formato de la cédula no es válido.',
            'correo_secundario.email' => 'El formato del correo secundario no es válido.'
        ]);
    }

    /**
     * Check if parent profile is complete
     */
    public function checkProfileComplete()
    {
        $user = Auth::user();
        $parent = ParentProfile::where('user_id', $user->id)->first();
        
        $isComplete = $parent && $parent->isProfileComplete();
        
        return response()->json([
            'complete' => $isComplete,
            'message' => $isComplete ? 
                'Perfil completo' : 
                'Debes completar tu perfil antes de continuar'
        ]);
    }

    /**
     * Show profile completion reminder
     */
    public function showCompletionReminder()
    {
        return view('backend.parent.profile-reminder');
    }

    /**
     * Show parent profile in read-only mode
     */
    public function viewOnly()
    {
        $user = Auth::user();
        
        // Obtener perfil de padre
        $parent = ParentProfile::where('user_id', $user->id)->first();
        
        // Lista de provincias de Costa Rica
        $provincias = [
            'San José',
            'Alajuela', 
            'Cartago',
            'Heredia',
            'Guanacaste',
            'Puntarenas',
            'Limón'
        ];

        return view('backend.parent.profile-view', compact('user', 'parent', 'provincias'));
    }
}