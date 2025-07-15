<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;

class RecruitmentSession extends Model
{
    use Uuids;

    protected $fillable = [
        'session_number',
        'fptk_id',
        'candidate_id',
        'applied_date',
        'source',
        'current_stage',
        'stage_status',
        'stage_started_at',
        'stage_completed_at',
        'overall_progress',
        'next_action',
        'responsible_person_id',
        'final_status',
        'final_decision_date',
        'final_decision_by',
        'final_decision_notes',
        'stage_durations',
    ];

    protected $casts = [
        'applied_date' => 'date',
        'stage_started_at' => 'datetime',
        'stage_completed_at' => 'datetime',
        'overall_progress' => 'decimal:2',
        'final_decision_date' => 'datetime',
        'stage_durations' => 'array',
    ];

    protected $dates = [
        'applied_date',
        'stage_started_at',
        'stage_completed_at',
        'final_decision_date',
        'created_at',
        'updated_at',
    ];

    // Enums for validation
    public const STAGES = [
        'cv_review',
        'psikotes',
        'tes_teori',
        'interview_hr',
        'interview_user',
        'offering',
        'mcu',
        'hire',
        'onboarding'
    ];

    public const STAGE_STATUSES = ['pending', 'in_progress', 'completed', 'failed', 'skipped'];
    public const FINAL_STATUSES = ['in_process', 'hired', 'rejected', 'withdrawn', 'cancelled'];

    // Stage progress percentages
    public const STAGE_PROGRESS = [
        'cv_review' => 10,
        'psikotes' => 20,
        'tes_teori' => 30,
        'interview_hr' => 45,
        'interview_user' => 60,
        'offering' => 75,
        'mcu' => 85,
        'hire' => 95,
        'onboarding' => 100,
    ];

    // Duration targets per stage (in hours)
    public const STAGE_DURATION_TARGETS = [
        'cv_review' => 48,      // 1-2 days
        'psikotes' => 96,       // 3-5 days
        'tes_teori' => 60,      // 2-3 days
        'interview_hr' => 120,  // 3-7 days
        'interview_user' => 200, // 5-10 days
        'offering' => 120,      // 3-7 days
        'mcu' => 96,           // 2-5 days
        'hire' => 48,          // 1-2 days
        'onboarding' => 120,   // 5-7 days
    ];

    /**
     * Relationships
     */
    public function fptk()
    {
        return $this->belongsTo(RecruitmentRequest::class, 'fptk_id');
    }

    public function candidate()
    {
        return $this->belongsTo(RecruitmentCandidate::class, 'candidate_id');
    }

    public function responsiblePerson()
    {
        return $this->belongsTo(User::class, 'responsible_person_id');
    }

    public function finalDecisionBy()
    {
        return $this->belongsTo(User::class, 'final_decision_by');
    }

    public function assessments()
    {
        return $this->hasMany(RecruitmentAssessment::class, 'session_id');
    }

    public function offers()
    {
        return $this->hasMany(RecruitmentOffer::class, 'session_id');
    }

    public function documents()
    {
        return $this->hasMany(RecruitmentDocument::class, 'session_id');
    }

    // Get assessment by type
    public function getAssessment($type)
    {
        return $this->assessments()->where('assessment_type', $type)->first();
    }

    // Get current stage assessment
    public function getCurrentStageAssessment()
    {
        return $this->getAssessment($this->current_stage);
    }

    // Get latest offer
    public function getLatestOffer()
    {
        return $this->offers()->orderBy('created_at', 'desc')->first();
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('final_status', 'in_process');
    }

    public function scopeHired($query)
    {
        return $query->where('final_status', 'hired');
    }

    public function scopeRejected($query)
    {
        return $query->where('final_status', 'rejected');
    }

    public function scopeByStage($query, $stage)
    {
        return $query->where('current_stage', $stage);
    }

    public function scopeByFptk($query, $fptkId)
    {
        return $query->where('fptk_id', $fptkId);
    }

    public function scopeByCandidate($query, $candidateId)
    {
        return $query->where('candidate_id', $candidateId);
    }

    public function scopeOverdue($query)
    {
        return $query->where('stage_status', 'in_progress')
            ->where('stage_started_at', '<', now()->subHours(48));
    }

    /**
     * Accessors & Mutators
     */
    public function getIsActiveAttribute()
    {
        return $this->final_status === 'in_process';
    }

    public function getIsCompletedAttribute()
    {
        return in_array($this->final_status, ['hired', 'rejected', 'withdrawn', 'cancelled']);
    }

    public function getNextStageAttribute()
    {
        $stages = self::STAGES;
        $currentIndex = array_search($this->current_stage, $stages);

        if ($currentIndex === false || $currentIndex === count($stages) - 1) {
            return null;
        }

        return $stages[$currentIndex + 1];
    }

    public function getCurrentStageDurationAttribute()
    {
        if (!$this->stage_started_at) {
            return 0;
        }

        $endTime = $this->stage_completed_at ?? now();
        return $this->stage_started_at->diffInHours($endTime);
    }

    public function getTotalDurationAttribute()
    {
        return $this->created_at->diffInHours(now());
    }

    public function getIsOverdueAttribute()
    {
        if ($this->stage_status !== 'in_progress') {
            return false;
        }

        $targetDuration = self::STAGE_DURATION_TARGETS[$this->current_stage] ?? 48;
        return $this->getCurrentStageDurationAttribute() > $targetDuration;
    }

    public function getStageDurationSummaryAttribute()
    {
        $durations = $this->stage_durations ?? [];
        $summary = [];

        foreach (self::STAGES as $stage) {
            if (isset($durations[$stage])) {
                $summary[$stage] = [
                    'duration_hours' => $durations[$stage]['duration_hours'],
                    'target_hours' => self::STAGE_DURATION_TARGETS[$stage],
                    'is_overdue' => $durations[$stage]['duration_hours'] > self::STAGE_DURATION_TARGETS[$stage],
                ];
            }
        }

        return $summary;
    }

    /**
     * Business Logic Methods
     */
    public function canAdvanceToNextStage()
    {
        if ($this->stage_status !== 'completed') {
            return false;
        }

        if ($this->final_status !== 'in_process') {
            return false;
        }

        return $this->getNextStageAttribute() !== null;
    }

    public function advanceToNextStage()
    {
        if (!$this->canAdvanceToNextStage()) {
            return false;
        }

        $nextStage = $this->getNextStageAttribute();
        if (!$nextStage) {
            return false;
        }

        // Record current stage completion
        $this->recordStageCompletion();

        // Move to next stage
        $this->update([
            'current_stage' => $nextStage,
            'stage_status' => 'pending',
            'stage_started_at' => now(),
            'stage_completed_at' => null,
            'overall_progress' => self::STAGE_PROGRESS[$nextStage],
        ]);

        return true;
    }

    public function startStage()
    {
        if ($this->stage_status !== 'pending') {
            return false;
        }

        $this->update([
            'stage_status' => 'in_progress',
            'stage_started_at' => now(),
        ]);

        return true;
    }

    public function completeStage($notes = null)
    {
        if ($this->stage_status !== 'in_progress') {
            return false;
        }

        $this->update([
            'stage_status' => 'completed',
            'stage_completed_at' => now(),
        ]);

        // Record duration
        $this->recordStageCompletion();

        return true;
    }

    public function failStage($reason = null)
    {
        $this->update([
            'stage_status' => 'failed',
            'stage_completed_at' => now(),
            'final_status' => 'rejected',
            'final_decision_date' => now(),
            'final_decision_notes' => $reason,
        ]);

        $this->recordStageCompletion();

        return true;
    }

    public function skipStage($reason = null)
    {
        $this->update([
            'stage_status' => 'skipped',
            'stage_completed_at' => now(),
        ]);

        $this->recordStageCompletion();

        return true;
    }

    public function hire($decisionBy, $notes = null)
    {
        $this->update([
            'final_status' => 'hired',
            'final_decision_date' => now(),
            'final_decision_by' => $decisionBy,
            'final_decision_notes' => $notes,
            'overall_progress' => 100,
        ]);

        // Update FPTK positions filled
        $this->fptk->incrementPositionsFilled();

        // Update candidate global status
        $this->candidate->update(['global_status' => 'hired']);

        return true;
    }

    public function reject($decisionBy, $reason)
    {
        $this->update([
            'final_status' => 'rejected',
            'final_decision_date' => now(),
            'final_decision_by' => $decisionBy,
            'final_decision_notes' => $reason,
        ]);

        // Update candidate global status
        $this->candidate->updateGlobalStatus();

        return true;
    }

    public function withdraw($reason = null)
    {
        $this->update([
            'final_status' => 'withdrawn',
            'final_decision_date' => now(),
            'final_decision_notes' => $reason,
        ]);

        // Update candidate global status
        $this->candidate->updateGlobalStatus();

        return true;
    }

    public function cancel($reason = null)
    {
        $this->update([
            'final_status' => 'cancelled',
            'final_decision_date' => now(),
            'final_decision_notes' => $reason,
        ]);

        // Update candidate global status
        $this->candidate->updateGlobalStatus();

        return true;
    }

    protected function recordStageCompletion()
    {
        if (!$this->stage_started_at) {
            return;
        }

        $duration = $this->getCurrentStageDurationAttribute();
        $durations = $this->stage_durations ?? [];

        $durations[$this->current_stage] = [
            'started' => $this->stage_started_at->toISOString(),
            'completed' => now()->toISOString(),
            'duration_hours' => $duration,
        ];

        $this->update(['stage_durations' => $durations]);
    }

    /**
     * Generate unique session number
     */
    public static function generateSessionNumber()
    {
        $year = date('Y');
        $month = date('m');

        $lastNumber = static::whereRaw('YEAR(created_at) = ? AND MONTH(created_at) = ?', [$year, $month])
            ->orderBy('created_at', 'desc')
            ->first();

        $sequence = 1;
        if ($lastNumber && preg_match('/RSN\/\d+\/\d+\/(\d+)$/', $lastNumber->session_number, $matches)) {
            $sequence = intval($matches[1]) + 1;
        }

        return sprintf('RSN/%d/%02d/%04d', $year, $month, $sequence);
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->session_number)) {
                $model->session_number = static::generateSessionNumber();
            }
        });
    }
}
