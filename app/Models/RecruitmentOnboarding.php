<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecruitmentOnboarding extends Model
{
    protected $table = 'recruitment_onboarding';

    protected $fillable = [
        'session_id',
        'onboarding_date',
        'notes',
        'reviewed_by',
        'reviewed_at'
    ];

    protected $casts = [
        'onboarding_date' => 'date',
        'reviewed_at' => 'datetime',
    ];

    protected $dates = [
        'onboarding_date',
        'reviewed_at',
        'created_at',
        'updated_at'
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
    public function scopeByDate($query, $date)
    {
        return $query->where('onboarding_date', $date);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('onboarding_date', '>=', now()->toDateString());
    }

    public function scopePast($query)
    {
        return $query->where('onboarding_date', '<', now()->toDateString());
    }

    /**
     * Accessors & Mutators
     */
    public function getOnboardingDateFormattedAttribute()
    {
        return $this->onboarding_date ? $this->onboarding_date->format('d/m/Y') : '-';
    }

    public function getIsUpcomingAttribute()
    {
        return $this->onboarding_date && $this->onboarding_date->isFuture();
    }

    public function getIsPastAttribute()
    {
        return $this->onboarding_date && $this->onboarding_date->isPast();
    }
}
