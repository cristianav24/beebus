<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Beca extends Model
{

    protected $table = 'becas';

    protected $fillable = [
        'nombre_beca',
        'descripcion',
        'monto_creditos',
        'fecha_inicio',
        'fecha_fin',
        'estado'
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin' => 'date',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function histories()
    {
        return $this->hasMany(History::class, 'beca_id');
    }

    public function scopeActivas($query)
    {
        return $query->where('estado', 'activa');
    }
}
