<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RecruitmentAssessment extends Model
{
    use HasFactory;

    protected $fillable = [
        'session_id',
        'assessment_type',
        'scheduled_date',
        'scheduled_time',
        'location',
        'meeting_link',
        'assessor_ids',
        'duration_minutes',
        'status',
        'overall_score',
        'max_score',
        'passing_score',
        'assessment_data',
        'recommendation',
        'assessor_notes',
        'candidate_feedback',
        'result_documents',
        'started_at',
        'completed_at',
    ];

    protected $casts = [
        'scheduled_date' => 'datetime',
        'scheduled_time' => 'datetime',
        'assessor_ids' => 'array',
        'assessment_data' => 'array',
        'result_documents' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'overall_score' => 'decimal:2',
        'max_score' => 'decimal:2',
        'passing_score' => 'decimal:2',
    ];

    /**
     * Get the session that owns this assessment
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(RecruitmentSession::class, 'session_id');
    }

    /**
     * Scope for upcoming assessments
     */
    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_date', '>=', now())
                    ->where('status', 'scheduled');
    }

    /**
     * Get assessment type display name
     */
    public function getAssessmentTypeNameAttribute(): string
    {
        return match($this->assessment_type) {
            'cv_review' => 'CV Review',
            'psikotes' => 'Psikotes',
            'tes_teori' => 'Tes Teori',
            'interview_hr' => 'Interview HR',
            'interview_user' => 'Interview User',
            'mcu' => 'MCU',
            default => ucfirst(str_replace('_', ' ', $this->assessment_type))
        };
    }

    /**
     * Check if assessment is overdue
     */
    public function getIsOverdueAttribute(): bool
    {
        if ($this->status !== 'scheduled') {
            return false;
        }

        return $this->scheduled_date && $this->scheduled_date->isPast();
    }

    /**
     * Get days until assessment
     */
    public function getDaysUntilAttribute(): int
    {
        if (!$this->scheduled_date) {
            return 0;
        }

        return now()->diffInDays($this->scheduled_date, false);
    }
}
