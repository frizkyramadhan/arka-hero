<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecruitmentOffering extends Model
{
    protected $fillable = [
        'session_id',
        'offering_letter_number',
        'result',
        'notes',
        'reviewed_by',
        'reviewed_at'
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    protected $dates = [
        'reviewed_at',
        'created_at',
        'updated_at'
    ];

    // Result options
    public const RESULTS = [
        'accepted',
        'rejected',
        'negotiating'
    ];

    /**
     * Relationships
     */
    public function session()
    {
        return $this->belongsTo(RecruitmentSession::class, 'session_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Scopes
     */
    public function scopeAccepted($query)
    {
        return $query->where('result', 'accepted');
    }

    public function scopeRejected($query)
    {
        return $query->where('result', 'rejected');
    }

    public function scopeNegotiating($query)
    {
        return $query->where('result', 'negotiating');
    }

    /**
     * Accessors & Mutators
     */
    public function getIsAcceptedAttribute()
    {
        return $this->result === 'accepted';
    }

    public function getIsRejectedAttribute()
    {
        return $this->result === 'rejected';
    }

    public function getIsNegotiatingAttribute()
    {
        return $this->result === 'negotiating';
    }

    public function getResultLabelAttribute()
    {
        return ucfirst($this->result);
    }
}
