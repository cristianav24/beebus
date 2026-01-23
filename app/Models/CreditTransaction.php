<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CreditTransaction extends Model
{
    protected $table = 'credit_transactions';

    protected $fillable = [
        'history_id',
        'type',
        'amount',
        'balance_before',
        'balance_after',
        'description',
        'attendance_id',
        'ruta_id',
        'processed_by',
        'receipt_file',
        'payment_method',
        'payment_reference',
        'payment_date',
        'verification_status',
        'verified_by',
        'verified_at',
        'admin_notes',
        'stripe_payment_intent_id',
        'processed_at',
        // PayMe fields
        'payme_operation_number',
        'payme_authorization_code',
        'payme_authorization_result',
        'payme_error_code',
        'payme_error_message'
    ];

    protected $casts = [
        'payment_date' => 'date',
        'verified_at' => 'datetime',
        'processed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relaciones
    public function history()
    {
        return $this->belongsTo(History::class, 'history_id');
    }

    public function attendance()
    {
        return $this->belongsTo(Attendance::class, 'attendance_id');
    }

    // Scopes
    public function scopeRecargas($query)
    {
        return $query->where('type', 'recarga');
    }

    public function scopeConsumos($query)
    {
        return $query->where('type', 'consumo');
    }

    public function scopeChanceDebts($query)
    {
        return $query->where('type', 'chance_debt');
    }

    public function scopePending($query)
    {
        return $query->where('verification_status', 'pending');
    }

    public function scopeVerified($query)
    {
        return $query->where('verification_status', 'verified');
    }
}