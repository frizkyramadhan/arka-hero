<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;

class RecruitmentAssessment extends Model
{
    use Uuids;

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
        'scheduled_time' => 'time',
        'assessor_ids' => 'array',
        'duration_minutes' => 'integer',
        'overall_score' => 'decimal:2',
        'max_score' => 'decimal:2',
        'passing_score' => 'decimal:2',
        'assessment_data' => 'array',
        'result_documents' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    protected $dates = [
        'scheduled_date',
        'started_at',
        'completed_at',
        'created_at',
        'updated_at',
    ];

    // Assessment types
    public const ASSESSMENT_TYPES = [
        'cv_review',
        'psikotes',
        'tes_teori',
        'interview_hr',
        'interview_user',
        'mcu'
    ];

    // Assessment statuses
    public const STATUSES = [
        'scheduled',
        'in_progress',
        'completed',
        'failed',
        'cancelled',
        'no_show'
    ];

    // Recommendations
    public const RECOMMENDATIONS = [
        'strongly_recommend',
        'recommend',
        'neutral',
        'not_recommend',
        'medical_unfit'
    ];

    /**
     * Relationships
     */
    public function session()
    {
        return $this->belongsTo(RecruitmentSession::class, 'session_id');
    }

    public function assessors()
    {
        $assessorIds = $this->assessor_ids ?? [];
        return User::whereIn('id', $assessorIds)->get();
    }

    public function documents()
    {
        return $this->hasMany(RecruitmentDocument::class, 'related_assessment_id');
    }

    /**
     * Scopes
     */
    public function scopeByType($query, $type)
    {
        return $query->where('assessment_type', $type);
    }

    public function scopeScheduled($query)
    {
        return $query->where('status', 'scheduled');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeFailed($query)
    {
        return $query->where('status', 'failed');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('scheduled_date', now()->toDateString());
    }

    public function scopeUpcoming($query)
    {
        return $query->where('scheduled_date', '>', now())
            ->where('status', 'scheduled');
    }

    public function scopeOverdue($query)
    {
        return $query->where('scheduled_date', '<', now())
            ->where('status', 'scheduled');
    }

    /**
     * Accessors & Mutators
     */
    public function getIsPendingAttribute()
    {
        return $this->status === 'scheduled';
    }

    public function getIsCompletedAttribute()
    {
        return $this->status === 'completed';
    }

    public function getIsFailedAttribute()
    {
        return $this->status === 'failed';
    }

    public function getIsPassedAttribute()
    {
        if (!$this->overall_score || !$this->passing_score) {
            return null;
        }
        return $this->overall_score >= $this->passing_score;
    }

    public function getScorePercentageAttribute()
    {
        if (!$this->overall_score || !$this->max_score) {
            return 0;
        }
        return round(($this->overall_score / $this->max_score) * 100, 2);
    }

    public function getAssessmentDataFormattedAttribute()
    {
        $data = $this->assessment_data ?? [];

        switch ($this->assessment_type) {
            case 'cv_review':
                return [
                    'Education Match' => $data['education_match'] ?? 0,
                    'Experience Match' => $data['experience_match'] ?? 0,
                    'Skills Match' => $data['skills_match'] ?? 0,
                ];

            case 'psikotes':
                return [
                    'Personality Score' => $data['personality_score'] ?? 0,
                    'IQ Score' => $data['iq_score'] ?? 0,
                    'EQ Score' => $data['eq_score'] ?? 0,
                ];

            case 'tes_teori':
                return [
                    'Technical Score' => $data['technical_score'] ?? 0,
                    'General Score' => $data['general_score'] ?? 0,
                ];

            case 'interview_hr':
                return [
                    'Communication' => $data['communication'] ?? 0,
                    'Attitude' => $data['attitude'] ?? 0,
                    'Cultural Fit' => $data['cultural_fit'] ?? 0,
                ];

            case 'interview_user':
                return [
                    'Technical Skill' => $data['technical_skill'] ?? 0,
                    'Experience' => $data['experience'] ?? 0,
                    'Problem Solving' => $data['problem_solving'] ?? 0,
                ];

            case 'mcu':
                return [
                    'Blood Pressure' => $data['blood_pressure'] ?? 'N/A',
                    'Heart Rate' => $data['heart_rate'] ?? 'N/A',
                    'Overall Health' => $data['overall_health'] ?? 'N/A',
                ];

            default:
                return $data;
        }
    }

    public function getDurationHumanAttribute()
    {
        if (!$this->duration_minutes) {
            return 'N/A';
        }

        $hours = floor($this->duration_minutes / 60);
        $minutes = $this->duration_minutes % 60;

        if ($hours > 0) {
            return sprintf('%d jam %d menit', $hours, $minutes);
        }

        return sprintf('%d menit', $minutes);
    }

    /**
     * Business Logic Methods
     */
    public function start()
    {
        if ($this->status !== 'scheduled') {
            return false;
        }

        $this->update([
            'status' => 'in_progress',
            'started_at' => now(),
        ]);

        return true;
    }

    public function complete($score = null, $data = null, $notes = null)
    {
        if ($this->status !== 'in_progress') {
            return false;
        }

        $updateData = [
            'status' => 'completed',
            'completed_at' => now(),
        ];

        if ($score !== null) {
            $updateData['overall_score'] = $score;
        }

        if ($data !== null) {
            $updateData['assessment_data'] = $data;
        }

        if ($notes !== null) {
            $updateData['assessor_notes'] = $notes;
        }

        $this->update($updateData);

        return true;
    }

    public function fail($reason = null)
    {
        $this->update([
            'status' => 'failed',
            'completed_at' => now(),
            'assessor_notes' => $reason,
        ]);

        return true;
    }

    public function cancel($reason = null)
    {
        $this->update([
            'status' => 'cancelled',
            'assessor_notes' => $reason,
        ]);

        return true;
    }

    public function reschedule($date, $time = null)
    {
        $this->update([
            'scheduled_date' => $date,
            'scheduled_time' => $time,
            'status' => 'scheduled',
        ]);

        return true;
    }

    public function addAssessor($userId)
    {
        $assessorIds = $this->assessor_ids ?? [];

        if (!in_array($userId, $assessorIds)) {
            $assessorIds[] = $userId;
            $this->update(['assessor_ids' => $assessorIds]);
        }
    }

    public function removeAssessor($userId)
    {
        $assessorIds = $this->assessor_ids ?? [];

        if (($key = array_search($userId, $assessorIds)) !== false) {
            unset($assessorIds[$key]);
            $this->update(['assessor_ids' => array_values($assessorIds)]);
        }
    }

    public function hasAssessor($userId)
    {
        $assessorIds = $this->assessor_ids ?? [];
        return in_array($userId, $assessorIds);
    }
}
