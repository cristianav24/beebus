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
	
	/*protected $fillable = [
		'worker_id',
		'date',
		'in_time',
		'out_time',
		'work_hour',
		'over_time',
		'late_time',
		'early_out_time',
		'in_location',
		'out_location'
	];*/

    protected $dates = [
        'date'
    ];

    protected $casts = [
        'date'  => 'date:Y-m-d',
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
}
