<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecruitmentCvReview extends Model
{
    protected $fillable = [
        'session_id',
        'decision',
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

    // Decision options
    public const DECISIONS = [
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
    public function scopeRecommended($query)
    {
        return $query->where('decision', 'recommended');
    }

    public function scopeNotRecommended($query)
    {
        return $query->where('decision', 'not_recommended');
    }

    /**
     * Accessors & Mutators
     */
    public function getIsRecommendedAttribute()
    {
        return $this->decision === 'recommended';
    }

    public function getDecisionLabelAttribute()
    {
        return $this->decision === 'recommended' ? 'Recommended' : 'Not Recommended';
    }
}
