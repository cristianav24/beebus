<?php

namespace App\Models;

use App\Models\Base\Attendance as BaseAttendance;

class Attendance extends BaseAttendance
{
	protected $fillable = [
		'worker_id',
		'date',
		'rutaBus',
		'tipoBeca',
		'colegio',
		'beca_id',
		'colegio_id',
		'ruta_id',
		'tarifa_id',
		'uso_chance',
		'chances_restantes',
		'in_time',
		'cuantoRestar',
		'out_time',
		'work_hour',
		'over_time',
		'late_time',
		'early_out_time',
		'in_location',
		'out_location'
	];

    protected $dates = [
        'date'
    ];

    protected $casts = [
        'date'  => 'date:Y-m-d',
        'uso_chance' => 'boolean',
    ];

    // Relaciones
    public function colegio()
    {
        return $this->belongsTo(Colegio::class, 'colegio_id');
    }

    public function beca()
    {
        return $this->belongsTo(Beca::class, 'beca_id');
    }

    public function ruta()
    {
        return $this->belongsTo(Setting::class, 'ruta_id');
    }

    public function tarifa()
    {
        return $this->belongsTo(Tarifa::class, 'tarifa_id');
    }
}
