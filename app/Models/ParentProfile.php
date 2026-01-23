<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ParentProfile extends Model
{
    protected $table = 'parents';

    protected $fillable = [
        'user_id',
        'telefono',
        'direccion',
        'provincia',
        'canton',
        'distrito',
        'cedula',
        'ocupacion',
        'correo_secundario'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relación con User
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Método para verificar si el perfil está completo
    public function isProfileComplete()
    {
        return !empty($this->telefono) && 
               !empty($this->cedula) && 
               !empty($this->direccion) && 
               !empty($this->provincia) && 
               !empty($this->canton);
    }

    // Método para obtener el nombre completo desde User
    public function getFullNameAttribute()
    {
        return $this->user ? $this->user->name : '';
    }

    // Método para obtener el email desde User
    public function getEmailAttribute()
    {
        return $this->user ? $this->user->email : '';
    }

    // Método estático para encontrar o crear un perfil de padre
    public static function findOrCreateForUser($userId)
    {
        return static::firstOrCreate(['user_id' => $userId]);
    }

    // Validar formato de cédula costarricense
    public function isValidCedulaCR($cedula)
    {
        // Remover guiones y espacios
        $cedula = preg_replace('/[^0-9]/', '', $cedula);
        
        // Debe tener exactamente 9 dígitos
        if (strlen($cedula) !== 9) {
            return false;
        }
        
        // Los primeros dos dígitos deben estar entre 01-31 (provincia y cantón)
        $provincia = (int)substr($cedula, 0, 1);
        if ($provincia < 1 || $provincia > 9) {
            return false;
        }
        
        return true;
    }

    // Formatear cédula
    public function getFormattedCedulaAttribute()
    {
        if (!$this->cedula) return '';
        
        $cedula = preg_replace('/[^0-9]/', '', $this->cedula);
        if (strlen($cedula) === 9) {
            return substr($cedula, 0, 1) . '-' . 
                   substr($cedula, 1, 4) . '-' . 
                   substr($cedula, 5, 4);
        }
        
        return $this->cedula;
    }

    // Formatear teléfono
    public function getFormattedTelefonoAttribute()
    {
        if (!$this->telefono) return '';
        
        $telefono = preg_replace('/[^0-9]/', '', $this->telefono);
        if (strlen($telefono) === 8) {
            return substr($telefono, 0, 4) . '-' . substr($telefono, 4, 4);
        }
        
        return $this->telefono;
    }
}