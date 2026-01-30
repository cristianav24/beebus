<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Zona extends Model
{
    protected $table = 'zonas';

    protected $fillable = [
        'nombre',
        'estado',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function colegios()
    {
        return $this->hasMany(Colegio::class, 'zona_id');
    }

    public function scopeActivas($query)
    {
        return $query->where('estado', 'activo');
    }
}
