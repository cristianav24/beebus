<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tarifa extends Model
{
    protected $table = 'tarifas';

    protected $fillable = [
        'nombre',
        'monto',
        'descripcion',
        'estado'
    ];

    protected $casts = [
        'monto' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function histories()
    {
        return $this->hasMany(History::class, 'tarifa_id');
    }

    public function attendances()
    {
        return $this->hasMany(Attendance::class, 'tarifa_id');
    }

    public function scopeActivas($query)
    {
        return $query->where('estado', 'activa');
    }
}
