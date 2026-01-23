<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Colegio extends Model
{

    protected $table = 'colegios';

    protected $fillable = [
        'nombre',
        'direccion',
        'telefono',
        'email',
        'codigo_institucional',
        'estado'
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function histories()
    {
        return $this->hasMany(History::class, 'colegio_id');
    }

    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }
}
