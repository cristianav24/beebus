<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Paradero extends Model
{
    protected $table = 'paraderos';

    protected $fillable = [
        'ruta_id',
        'nombre',
        'hora',
        'monto',
        'es_beca_empresarial',
        'orden',
        'estado',
    ];

    protected $casts = [
        'monto' => 'integer',
        'es_beca_empresarial' => 'boolean',
        'orden' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function ruta()
    {
        return $this->belongsTo(Setting::class, 'ruta_id');
    }

    public function scopeActivos($query)
    {
        return $query->where('estado', 'activo');
    }
}
