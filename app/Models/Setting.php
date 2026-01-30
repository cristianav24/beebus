<?php

namespace App\Models;

use App\Models\Base\Setting as BaseSetting;

class Setting extends BaseSetting
{
	protected $fillable = [
		'start_time',
		'out_time',
		'key_app',
        'timezone',
        'status',
        'colegio_id'
    ];

    public function colegio()
    {
        return $this->belongsTo(Colegio::class, 'colegio_id');
    }

    public function paraderos()
    {
        return $this->hasMany(Paradero::class, 'ruta_id');
    }
}
