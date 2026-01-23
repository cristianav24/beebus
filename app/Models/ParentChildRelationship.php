<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Exception;

class ParentChildRelationship extends Model
{
    protected $table = 'parent_child_relationships';

    protected $fillable = [
        'parent_user_id',
        'student_id',
        'status',
        'requested_at',
        'reviewed_by',
        'reviewed_at',
        'rejection_reason',
        'notes'
    ];

    protected $dates = [
        'requested_at',
        'reviewed_at',
        'created_at',
        'updated_at',
    ];
    
    protected $casts = [
        'requested_at' => 'datetime',
        'reviewed_at' => 'datetime',
    ];

    // Relaciones
    public function parent()
    {
        return $this->belongsTo(User::class, 'parent_user_id');
    }

    public function student()
    {
        return $this->belongsTo(History::class, 'student_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeApproved($query)
    {
        return $query->where('status', 'approved');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeForParent($query, $parentUserId)
    {
        return $query->where('parent_user_id', $parentUserId);
    }

    // Métodos de utilidad
    public function getRequestedAtFormatted()
    {
        try {
            return $this->requested_at ? $this->requested_at->format('d/m/Y H:i') : 'Fecha no disponible';
        } catch (Exception $e) {
            return 'Fecha no disponible';
        }
    }
    
    public function getRequestedAtHuman()
    {
        try {
            return $this->requested_at ? $this->requested_at->diffForHumans() : 'Fecha no disponible';
        } catch (Exception $e) {
            return 'Fecha no disponible';
        }
    }
    
    public function approve($reviewerId, $notes = null)
    {
        $this->status = 'approved';
        $this->reviewed_by = $reviewerId;
        $this->reviewed_at = now();
        $this->notes = $notes;
        return $this->save();
    }

    public function reject($reviewerId, $reason, $notes = null)
    {
        $this->status = 'rejected';
        $this->reviewed_by = $reviewerId;
        $this->reviewed_at = now();
        $this->rejection_reason = $reason;
        $this->notes = $notes;
        return $this->save();
    }
}