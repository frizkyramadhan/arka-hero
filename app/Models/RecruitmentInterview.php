<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecruitmentInterview extends Model
{
    protected $fillable = [
        'session_id',
        'type',
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

    // Type options
    public const TYPES = [
        'hr',
        'user'
    ];

    // Result options
    public const RESULTS = [
        'recommended',
        'not_recommended'
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
    public function scopeHr($query)
    {
        return $query->where('type', 'hr');
    }

    public function scopeUser($query)
    {
        return $query->where('type', 'user');
    }

    public function scopeRecommended($query)
    {
        return $query->where('result', 'recommended');
    }

    public function scopeNotRecommended($query)
    {
        return $query->where('result', 'not_recommended');
    }

    /**
     * Accessors & Mutators
     */
    public function getIsRecommendedAttribute()
    {
        return $this->result === 'recommended';
    }

    public function getResultLabelAttribute()
    {
        return $this->result === 'recommended' ? 'Recommended' : 'Not Recommended';
    }

    public function getTypeLabelAttribute()
    {
        return $this->type === 'hr' ? 'HR Interview' : 'User Interview';
    }
}
