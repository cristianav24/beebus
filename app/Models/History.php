<?php

namespace App\Models;

use App\Models\Base\History as BaseHistory;

class History extends BaseHistory
{
	protected $fillable = [
		'user_id',
		'name',
		'first_name',
		'last_name',
		'second_last_name',
		'tipoBeca',
		'colegio',
		'colegio_id',
		'beca_id',
		'ruta_id',
		'tarifa_id',
		'seccion',
		'email',
		'cedula',
		'cuantoRestar',
		'creditos',
		'chancesParaMarcar',
		'status',
		'contrato_subido',
		'contrato_url',
		'contrato_fecha_subida',
		'contrato_subido_por',
		'paradero_id',
	];

	protected $casts = [
		'contrato_fecha_subida' => 'datetime',
		'contrato_subido' => 'boolean',
	];

	/**
	 * Get the full name attribute
	 */
	public function getFullNameAttribute()
	{
		return trim($this->first_name . ' ' . $this->last_name . ' ' . $this->second_last_name) ?: $this->name;
	}

	// Relaciones
	public function user()
	{
		return $this->belongsTo(User::class, 'user_id');
	}

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

	public function paradero()
	{
		return $this->belongsTo(Paradero::class, 'paradero_id');
	}

	public function creditTransactions()
	{
		return $this->hasMany(CreditTransaction::class, 'history_id');
	}
}
