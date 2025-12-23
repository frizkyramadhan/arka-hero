<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;

class RecruitmentSession extends Model
{
    use Uuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'session_number',
        'fptk_id',
        'mpp_detail_id',
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
        'status',
        'final_status',
        'final_decision_date',
        'final_decision_by',
        'final_decision_notes',
        'stage_durations',
        'created_by',
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
        'interview',
        'offering',
        'mcu',
        'hire'
    ];

    public const STAGE_STATUSES = ['pending', 'in_progress', 'completed', 'failed', 'skipped'];
    public const statusES = ['in_process', 'hired', 'rejected', 'withdrawn', 'cancelled'];

    // Stage progress percentages
    public const STAGE_PROGRESS = [
        'cv_review' => 14.3,
        'psikotes' => 28.6,
        'tes_teori' => 42.9,
        'interview' => 57.1,
        'offering' => 71.4,
        'mcu' => 85.7,
        'hire' => 100,
    ];

    /**
     * Get adjusted stage progress based on position requirements and employment type
     */
    public function getAdjustedStageProgress(): array
    {
        // For magang and harian: only MCU and Hiring stages
        if ($this->shouldSkipStagesForEmploymentType()) {
            // Total stages: 2 (mcu, hire)
            return [
                'mcu' => 50.0,          // 1/2 * 100
                'hire' => 100,          // 2/2 * 100
            ];
        }

        if ($this->shouldSkipTheoryTest()) {
            // Adjust progress for non-mechanic positions (skip tes_teori)
            // Total stages: 6 (cv_review, psikotes, interview, offering, mcu, hire)
            return [
                'cv_review' => 16.7,    // 1/6 * 100
                'psikotes' => 33.3,     // 2/6 * 100
                'interview' => 50.0,    // 3/6 * 100
                'offering' => 66.7,     // 4/6 * 100
                'mcu' => 83.3,          // 5/6 * 100
                'hire' => 100,          // 6/6 * 100
            ];
        }

        // Total stages: 7 (cv_review, psikotes, tes_teori, interview, offering, mcu, hire)
        return [
            'cv_review' => 14.3,    // 1/7 * 100
            'psikotes' => 28.6,     // 2/7 * 100
            'tes_teori' => 42.9,    // 3/7 * 100
            'interview' => 57.1,    // 4/7 * 100
            'offering' => 71.4,     // 5/7 * 100
            'mcu' => 85.7,          // 6/7 * 100
            'hire' => 100,          // 7/7 * 100
        ];
    }

    // Duration targets per stage (in hours)
    public const STAGE_DURATION_TARGETS = [
        'cv_review' => 48,      // 1-2 days
        'psikotes' => 96,       // 3-5 days
        'tes_teori' => 60,      // 2-3 days
        'interview' => 320,     // 8-15 days (combined HR + User)
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

    public function mppDetail()
    {
        return $this->belongsTo(ManPowerPlanDetail::class, 'mpp_detail_id');
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

    // New separate table relationships
    public function cvReview()
    {
        return $this->hasOne(RecruitmentCvReview::class, 'session_id');
    }

    public function psikotes()
    {
        return $this->hasOne(RecruitmentPsikotes::class, 'session_id');
    }

    public function tesTeori()
    {
        return $this->hasOne(RecruitmentTesTeori::class, 'session_id');
    }

    public function interviews()
    {
        return $this->hasMany(RecruitmentInterview::class, 'session_id');
    }

    public function interviewHr()
    {
        return $this->hasOne(RecruitmentInterview::class, 'session_id')->where('type', 'hr');
    }

    public function interviewUser()
    {
        return $this->hasOne(RecruitmentInterview::class, 'session_id')->where('type', 'user');
    }

    public function interviewTrainer()
    {
        return $this->hasOne(RecruitmentInterview::class, 'session_id')->where('type', 'trainer');
    }

    public function offering()
    {
        return $this->hasOne(RecruitmentOffering::class, 'session_id');
    }

    /**
     * Get the latest offering for this session
     * This method is used by the workflow service for compatibility
     */
    public function getLatestOffer()
    {
        return $this->offering;
    }

    public function mcu()
    {
        return $this->hasOne(RecruitmentMcu::class, 'session_id');
    }

    public function hiring()
    {
        return $this->hasOne(RecruitmentHiring::class, 'session_id');
    }

    public function onboarding()
    {
        return $this->hasOne(RecruitmentOnboarding::class, 'session_id');
    }

    public function documents()
    {
        return $this->hasMany(RecruitmentDocument::class, 'session_id');
    }

    // Get current stage assessment
    public function getCurrentStageAssessment()
    {
        switch ($this->current_stage) {
            case 'cv_review':
                return $this->cvReview;
            case 'psikotes':
                return $this->psikotes;
            case 'tes_teori':
                return $this->tesTeori;
            case 'interview':
                // Return interview summary instead of collection
                return $this->getInterviewSummary();
            case 'offering':
                return $this->offering;
            case 'mcu':
                return $this->mcu;
            case 'hire':
                return $this->hiring;
            case 'onboarding':
                return $this->onboarding;
            default:
                return null;
        }
    }

    // Get assessment by stage
    public function getAssessmentByStage($stage)
    {
        // Ensure relationships are loaded before accessing
        switch ($stage) {
            case 'cv_review':
                if (!$this->relationLoaded('cvReview')) {
                    $this->load('cvReview');
                }
                return $this->cvReview;
            case 'psikotes':
                if (!$this->relationLoaded('psikotes')) {
                    $this->load('psikotes');
                }
                return $this->psikotes;
            case 'tes_teori':
                if (!$this->relationLoaded('tesTeori')) {
                    $this->load('tesTeori');
                }
                return $this->tesTeori;
            case 'interview':
                // Return interview summary instead of collection
                if (!$this->relationLoaded('interviews')) {
                    $this->load('interviews');
                }
                return $this->getInterviewSummary();
            case 'interview_hr':
                if (!$this->relationLoaded('interviews')) {
                    $this->load('interviews');
                }
                return $this->interviewHr;
            case 'interview_user':
                if (!$this->relationLoaded('interviews')) {
                    $this->load('interviews');
                }
                return $this->interviewUser;
            case 'interview_trainer':
                if (!$this->relationLoaded('interviews')) {
                    $this->load('interviews');
                }
                return $this->interviewTrainer;
            case 'offering':
                if (!$this->relationLoaded('offering')) {
                    $this->load('offering');
                }
                return $this->offering;
            case 'mcu':
                if (!$this->relationLoaded('mcu')) {
                    $this->load('mcu');
                }
                return $this->mcu;
            case 'hire':
                if (!$this->relationLoaded('hiring')) {
                    $this->load('hiring');
                }
                return $this->hiring;
            case 'onboarding':
                if (!$this->relationLoaded('onboarding')) {
                    $this->load('onboarding');
                }
                return $this->onboarding;
            default:
                return null;
        }
    }

    // Check if stage is completed
    public function isStageCompleted($stage)
    {
        // For magang and harian: only check MCU and Hire stages
        if ($this->shouldSkipStagesForEmploymentType()) {
            if (!in_array($stage, ['mcu', 'hire'])) {
                return true; // Consider other stages as completed for magang/harian
            }
        }

        $assessment = $this->getAssessmentByStage($stage);
        if (!$assessment) {
            return false;
        }

        // Ensure assessment has required data
        if (is_object($assessment) && !isset($assessment->decision) && !isset($assessment->result) && !isset($assessment->id)) {
            return false;
        }

        // Handle tes_teori stage for non-mechanic positions
        if ($stage === 'tes_teori' && $this->shouldSkipTheoryTest()) {
            return true; // Consider as completed for non-mechanic positions
        }

        switch ($stage) {
            case 'cv_review':
                return $assessment->decision === 'recommended';
            case 'psikotes':
                return $assessment->result === 'pass';
            case 'tes_teori':
                return $assessment->result === 'pass';
            case 'interview':
                // Ensure interviews relationship is loaded
                if (!$this->relationLoaded('interviews')) {
                    $this->load('interviews');
                }

                // Interview stage is completed when all required interviews are completed and recommended
                // Use collection filter instead of query to use loaded relationships
                $hrInterview = $this->interviews->where('type', 'hr')->first();
                $userInterview = $this->interviews->where('type', 'user')->first();
                $trainerInterview = $this->interviews->where('type', 'trainer')->first();

                // Check HR and User interviews (always required)
                if (
                    !$hrInterview || !$userInterview ||
                    $hrInterview->result !== 'recommended' ||
                    $userInterview->result !== 'recommended'
                ) {
                    return false;
                }

                // Check trainer interview only if theory test is required
                if (!$this->shouldSkipTheoryTest()) {
                    if (!$trainerInterview || $trainerInterview->result !== 'recommended') {
                        return false;
                    }
                }

                return true;
            case 'interview_hr':
            case 'interview_user':
            case 'interview_trainer':
                return $assessment->result === 'recommended';
            case 'offering':
                return $assessment->result === 'accepted';
            case 'mcu':
                return $assessment->result === 'fit';
            case 'hire':
                return $assessment->id !== null; // If record exists, it's completed
            case 'onboarding':
                return $assessment->id !== null; // If record exists, it's completed
            default:
                return false;
        }
    }

    // Get all assessments as array
    public function getAllAssessments()
    {
        return [
            'cv_review' => $this->cvReview,
            'psikotes' => $this->psikotes,
            'tes_teori' => $this->tesTeori,
            'interview' => $this->getInterviewSummary(), // Return interview summary instead of collection
            'offering' => $this->offering,
            'mcu' => $this->mcu,
            'hire' => $this->hiring,
        ];
    }

    // Get completed assessments count
    public function getCompletedAssessmentsCount()
    {
        $completed = 0;
        $stages = ['cv_review', 'psikotes', 'tes_teori', 'interview', 'offering', 'mcu', 'hire', 'onboarding'];

        foreach ($stages as $stage) {
            if ($this->isStageCompleted($stage)) {
                $completed++;
            }
        }

        return $completed;
    }

    /**
     * Get interview status for timeline coloring
     */
    public function getInterviewStatus()
    {
        $hrInterview = $this->interviews()->where('type', 'hr')->first();
        $userInterview = $this->interviews()->where('type', 'user')->first();

        if ($hrInterview && $userInterview) {
            // Both interviews completed
            if ($hrInterview->result === 'recommended' && $userInterview->result === 'recommended') {
                return 'success'; // Both passed
            } elseif ($hrInterview->result === 'not_recommended' || $userInterview->result === 'not_recommended') {
                return 'danger'; // One or both failed
            } else {
                return 'warning'; // Both completed but not both passed
            }
        } elseif ($hrInterview || $userInterview) {
            // One interview completed
            $completedInterview = $hrInterview ?? $userInterview;
            if ($completedInterview->result === 'not_recommended') {
                return 'danger'; // Completed interview failed
            } else {
                return 'warning'; // One completed, one pending
            }
        } else {
            // No interviews completed
            return 'secondary';
        }
    }

    /**
     * Check if interview type is already completed
     */
    public function isInterviewTypeCompleted($type)
    {
        return $this->interviews()->where('type', $type)->exists();
    }





    /**
     * Get interview summary for display
     */
    public function getInterviewSummary()
    {
        $summary = [
            'hr' => [
                'completed' => false,
                'result' => null,
                'reviewed_at' => null,
                'reviewer' => null,
                'status' => 'Pending'
            ],
            'user' => [
                'completed' => false,
                'result' => null,
                'reviewed_at' => null,
                'reviewer' => null,
                'status' => 'Pending'
            ]
        ];

        // Add trainer interview only if theory test is required
        if (!$this->shouldSkipTheoryTest()) {
            $summary['trainer'] = [
                'completed' => false,
                'result' => null,
                'reviewed_at' => null,
                'reviewer' => null,
                'status' => 'Pending'
            ];
        }

        // Get individual interviews by type
        $hrInterview = $this->interviews()->where('type', 'hr')->first();
        $userInterview = $this->interviews()->where('type', 'user')->first();
        $trainerInterview = null;

        // Get trainer interview only if theory test is required
        if (!$this->shouldSkipTheoryTest()) {
            $trainerInterview = $this->interviews()->where('type', 'trainer')->first();
        }

        if ($hrInterview) {
            $summary['hr'] = [
                'completed' => true,
                'result' => $hrInterview->result,
                'reviewed_at' => $hrInterview->reviewed_at,
                'reviewer' => $hrInterview->reviewer->name ?? 'N/A',
                'status' => ucfirst($hrInterview->result),
                'notes' => $hrInterview->notes
            ];
        }

        if ($userInterview) {
            $summary['user'] = [
                'completed' => true,
                'result' => $userInterview->result,
                'reviewed_at' => $userInterview->reviewed_at,
                'reviewer' => $userInterview->reviewer->name ?? 'N/A',
                'status' => ucfirst($userInterview->result),
                'notes' => $userInterview->notes
            ];
        }

        // Add trainer interview data if exists
        if ($trainerInterview) {
            $summary['trainer'] = [
                'completed' => true,
                'result' => $trainerInterview->result,
                'reviewed_at' => $trainerInterview->reviewed_at,
                'reviewer' => $trainerInterview->reviewer->name ?? 'N/A',
                'status' => ucfirst($trainerInterview->result),
                'notes' => $trainerInterview->notes
            ];
        }

        return $summary;
    }

    /**
     * Check if all required interviews are completed
     */
    public function areAllInterviewsCompleted()
    {
        $requiredCount = $this->shouldSkipTheoryTest() ? 2 : 3; // 2 if no theory test, 3 if theory test required
        return $this->interviews()->count() === $requiredCount;
    }

    /**
     * Check if all interviews passed (recommended)
     */
    public function didAllInterviewsPass()
    {
        $requiredCount = $this->shouldSkipTheoryTest() ? 2 : 3; // 2 if no theory test, 3 if theory test required
        return $this->interviews()->where('result', 'recommended')->count() === $requiredCount;
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', 'in_process');
    }

    public function scopeHired($query)
    {
        return $query->where('status', 'hired');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
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
    public function getInterviewHrAttribute()
    {
        return $this->interviewHr()->first();
    }

    public function getInterviewUserAttribute()
    {
        return $this->interviewUser()->first();
    }

    public function getInterviewTrainerAttribute()
    {
        return $this->interviewTrainer()->first();
    }

    public function getIsActiveAttribute()
    {
        return $this->status === 'in_process';
    }

    public function getIsCompletedAttribute()
    {
        return in_array($this->status, ['hired', 'rejected', 'withdrawn', 'cancelled']);
    }

    /**
     * Get recruitment source type (FPTK or MPP)
     */
    public function getRecruitmentSourceTypeAttribute()
    {
        if ($this->fptk_id) {
            return 'FPTK';
        } elseif ($this->mpp_detail_id) {
            return 'MPP';
        }
        return 'Unknown';
    }

    /**
     * Get recruitment source object (FPTK or MPP Detail)
     */
    public function getRecruitmentSourceAttribute()
    {
        if ($this->fptk_id) {
            return $this->fptk;
        } elseif ($this->mpp_detail_id) {
            return $this->mppDetail;
        }
        return null;
    }

    /**
     * Get recruitment source display name
     */
    public function getRecruitmentSourceNameAttribute()
    {
        if ($this->fptk_id) {
            $fptk = $this->fptk;
            return $fptk->request_number . ' - ' . $fptk->position->position_name;
        } elseif ($this->mpp_detail_id) {
            $mppDetail = $this->mppDetail;
            return $mppDetail->mpp->mpp_number . ' - ' . ($mppDetail->position->position_name ?? 'N/A');
        }
        return 'N/A';
    }

    public function getNextStageAttribute()
    {
        // For magang and harian: simplified stage order (only MCU and Hiring)
        if ($this->shouldSkipStagesForEmploymentType()) {
            $stageOrder = [
                'mcu' => 'hire',
                'hire' => 'onboarding',
                'onboarding' => null
            ];
            return $stageOrder[$this->current_stage] ?? null;
        }

        // Define stage order with conditional tes_teori for regular employment types
        $stageOrder = [
            'cv_review' => 'psikotes',
            'psikotes' => $this->shouldSkipTheoryTest() ? 'interview' : 'tes_teori',
            'tes_teori' => 'interview',
            'interview' => 'offering',
            'offering' => 'mcu',
            'mcu' => 'hire',
            'hire' => 'onboarding',
            'onboarding' => null
        ];

        return $stageOrder[$this->current_stage] ?? null;
    }

    /**
     * Check if this session should skip theory test
     *
     * @return bool
     */
    public function shouldSkipTheoryTest(): bool
    {
        // For MPP sessions, check requires_theory_test from MPP detail
        if ($this->mpp_detail_id) {
            // Ensure relationship is loaded
            if (!$this->relationLoaded('mppDetail')) {
                $this->load('mppDetail');
            }
            if ($this->mppDetail) {
                return !$this->mppDetail->requires_theory_test;
            }
            // Default to skip if MPP detail not found
            return true;
        }

        // For FPTK sessions
        if ($this->fptk_id) {
            // Ensure relationship is loaded
            if (!$this->relationLoaded('fptk')) {
                $this->load('fptk');
            }
            if ($this->fptk) {
                return !$this->fptk->requires_theory_test;
            }
        }

        // Default to skip if no source found
        return true;
    }

    /**
     * Check if this session should skip stages for employment type (magang/harian)
     *
     * @return bool
     */
    public function shouldSkipStagesForEmploymentType(): bool
    {
        // For MPP sessions, never skip stages (default to full recruitment flow)
        if ($this->mpp_detail_id) {
            return false;
        }

        // Check if fptk exists before accessing employment_type
        if (!$this->fptk_id || !$this->fptk) {
            return false;
        }

        return in_array($this->fptk->employment_type, ['magang', 'harian']);
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

        if ($this->status !== 'in_process') {
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
            'overall_progress' => $this->calculateActualProgress(),
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
            'overall_progress' => $this->calculateActualProgress(),
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
            'status' => 'rejected',
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
            'status' => 'hired',
            'final_decision_date' => now(),
            'final_decision_by' => $decisionBy,
            'final_decision_notes' => $notes,
            'overall_progress' => 100,
        ]);

        // Update FPTK positions filled if session is from FPTK
        if ($this->fptk_id) {
            $this->fptk->incrementPositionsFilled();
        }

        // Update MPP Detail existing quantity if session is from MPP
        if ($this->mpp_detail_id && $this->mppDetail) {
            // Load hiring relationship if not already loaded
            if (!$this->relationLoaded('hiring')) {
                $this->load('hiring');
            }

            // Get agreement_type from hiring record for tie-breaker
            $agreementType = null;
            if ($this->hiring && $this->hiring->agreement_type) {
                $agreementType = $this->hiring->agreement_type;
            }

            // Auto-increment based on MPP Detail needs (which one is still needed)
            // This will check diff_s and diff_ns to determine staff or non-staff
            // Agreement type is only used as tie-breaker if both have same diff
            $this->mppDetail->autoIncrementExistingQuantity($agreementType);

            // Check fulfillment
            $this->mppDetail->checkFulfillment();
        }

        // Update candidate global status
        $this->candidate->update(['global_status' => 'hired']);

        return true;
    }

    public function reject($decisionBy, $reason)
    {
        $this->update([
            'status' => 'rejected',
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
            'status' => 'withdrawn',
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
            'status' => 'cancelled',
            'final_decision_date' => now(),
            'final_decision_notes' => $reason,
        ]);

        // Update candidate global status
        $this->candidate->updateGlobalStatus();

        return true;
    }

    public function recordStageCompletion()
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
     * Get progress percentage for the session
     * Always calculate fresh to ensure accuracy, especially for MPP sessions
     *
     * @return float
     */
    public function getProgressPercentage(): float
    {
        // Always calculate fresh progress to ensure accuracy
        // This is especially important for MPP sessions where theory test requirement may affect progress
        return $this->calculateActualProgress();
    }

    /**
     * Calculate actual progress based on completed stages
     * Progress should only increase when stages are actually completed
     *
     * @return float
     */
    public function calculateActualProgress(): float
    {
        $adjustedProgress = $this->getAdjustedStageProgress();

        // For magang and harian: simplified progress calculation
        if ($this->shouldSkipStagesForEmploymentType()) {
            // Check Hire first (highest priority)
            if ($this->isStageCompleted('hire')) {
                return 100.0; // Hire completed = 100%
            }
            // Check MCU
            if ($this->isStageCompleted('mcu')) {
                return 50.0; // MCU completed = 50%
            }
            // No stages completed
            return 0.0;
        }

        // Regular employment types: use adjusted progress
        // Get stages based on employment type
        if ($this->shouldSkipTheoryTest()) {
            $stages = ['cv_review', 'psikotes', 'interview', 'offering', 'mcu', 'hire'];
        } else {
            $stages = ['cv_review', 'psikotes', 'tes_teori', 'interview', 'offering', 'mcu', 'hire'];
        }

        // Find the last completed stage and return its progress
        $lastCompletedStage = null;
        foreach ($stages as $stage) {
            if ($this->isStageCompleted($stage)) {
                $lastCompletedStage = $stage;
            }
        }

        // If no stages completed, return 0
        if (!$lastCompletedStage) {
            return 0.0;
        }

        // Return progress for the last completed stage
        return $adjustedProgress[$lastCompletedStage] ?? 0.0;
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
