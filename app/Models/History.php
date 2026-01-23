<?php

namespace App\Models;

use App\Models\Base\History as BaseHistory;

class History extends BaseHistory
{
	protected $fillable = [
		'name',
		'tipoBeca',
		'colegio',
		'colegio_id',
		'beca_id',
		'ruta_id',
		'seccion',
		'email',
		'cedula',
		'cuantoRestar',
		'creditos',
		'chancesParaMarcar',
		'status'
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

	public function creditTransactions()
	{
		return $this->hasMany(CreditTransaction::class, 'history_id');
	}
}
