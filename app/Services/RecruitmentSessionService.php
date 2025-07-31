<?php

namespace App\Services;

use App\Models\RecruitmentSession;
use App\Models\RecruitmentRequest;
use App\Models\RecruitmentCandidate;
use App\Models\RecruitmentAssessment;
use App\Models\User;
use App\Services\RecruitmentLetterNumberService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class RecruitmentSessionService
{
    protected $workflowService;
    protected $notificationService;
    protected $letterNumberService;

    public function __construct(
        RecruitmentWorkflowService $workflowService,
        RecruitmentNotificationService $notificationService,
        RecruitmentLetterNumberService $letterNumberService
    ) {
        $this->workflowService = $workflowService;
        $this->notificationService = $notificationService;
        $this->letterNumberService = $letterNumberService;
    }

    /**
     * Create a new recruitment session
     *
     * @param string $fptkId
     * @param string $candidateId
     * @param array $data
     * @return RecruitmentSession|null
     */
    public function createSession(string $fptkId, string $candidateId, array $data = []): ?RecruitmentSession
    {
        DB::beginTransaction();

        try {
            // Validate FPTK can receive applications
            $fptk = RecruitmentRequest::find($fptkId);
            if (!$fptk || !$fptk->canReceiveApplications()) {
                Log::warning("FPTK cannot receive applications", ['fptk_id' => $fptkId]);
                return null;
            }

            // Validate candidate availability
            $candidate = RecruitmentCandidate::find($candidateId);
            if (!$candidate || !$candidate->canApplyToFptk($fptkId)) {
                Log::warning("Candidate cannot apply to FPTK", [
                    'candidate_id' => $candidateId,
                    'fptk_id' => $fptkId
                ]);
                return null;
            }

            // Create session
            $session = RecruitmentSession::create([
                'session_number' => RecruitmentSession::generateSessionNumber(),
                'fptk_id' => $fptkId,
                'candidate_id' => $candidateId,
                'applied_date' => now()->toDateString(),
                'source' => $data['source'] ?? 'website',
                'current_stage' => 'cv_review',
                'stage_status' => 'pending',
                'stage_started_at' => now(),
                'overall_progress' => 10, // CV Review = 10%
                'status' => 'in_process',
                'responsible_person_id' => $data['responsible_person_id'] ?? null,
            ]);

            // Update candidate global status
            $candidate->update(['global_status' => 'in_process']);

            // Create initial CV Review assessment
            $this->createInitialAssessment($session);

            // Send notifications
            $this->notificationService->sendSessionCreatedNotification($session);

            DB::commit();

            Log::info("Recruitment session created successfully", [
                'session_id' => $session->id,
                'session_number' => $session->session_number
            ]);

            return $session;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to create recruitment session", [
                'error' => $e->getMessage(),
                'fptk_id' => $fptkId,
                'candidate_id' => $candidateId
            ]);
            return null;
        }
    }

    /**
     * Ensure FPTK has letter number when session is created
     *
     * @param RecruitmentRequest $fptk
     * @return void
     */
    protected function ensureFPTKHasLetterNumber(RecruitmentRequest $fptk): void
    {
        if (!$fptk->hasLetterNumber()) {
            try {
                $this->letterNumberService->assignLetterNumberToFPTK($fptk);
                Log::info('Auto-assigned letter number to FPTK during session creation', [
                    'fptk_id' => $fptk->id,
                    'letter_number' => $fptk->fresh()->letter_number,
                ]);
            } catch (Exception $e) {
                Log::error('Failed to auto-assign letter number to FPTK', [
                    'fptk_id' => $fptk->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    /**
     * Get FPTK letter number information for session
     *
     * @param RecruitmentSession $session
     * @return array
     */
    public function getFPTKLetterNumberInfo(RecruitmentSession $session): array
    {
        return $session->fptk->getLetterNumberInfo();
    }

    /**
     * Get FPTK letter number statistics
     *
     * @param int|null $year
     * @return array
     */
    public function getFPTKLetterNumberStats($year = null): array
    {
        return $this->letterNumberService->getFPTKLetterNumberStats($year);
    }

    /**
     * Advance session to next stage
     *
     * @param RecruitmentSession $session
     * @param array $data
     * @return bool
     */
    public function advanceToNextStage(RecruitmentSession $session, array $data = []): bool
    {
        if (!$session->canAdvanceToNextStage()) {
            Log::warning("Session cannot advance to next stage", [
                'session_id' => $session->id,
                'current_stage' => $session->current_stage,
                'stage_status' => $session->stage_status
            ]);
            return false;
        }

        DB::beginTransaction();

        try {
            // Get next stage
            $nextStage = $session->getNextStageAttribute();
            if (!$nextStage) {
                DB::rollBack();
                return false;
            }

            // Record current stage completion
            $session->recordStageCompletion();

            // Update session to next stage
            $session->update([
                'current_stage' => $nextStage,
                'stage_status' => 'pending',
                'stage_started_at' => now(),
                'stage_completed_at' => null,
                'overall_progress' => RecruitmentSession::STAGE_PROGRESS[$nextStage],
                'responsible_person_id' => $data['responsible_person_id'] ?? null,
            ]);

            // Create assessment for next stage
            if (in_array($nextStage, ['psikotes', 'tes_teori', 'interview_hr', 'interview_user', 'mcu'])) {
                $this->createAssessmentForStage($session, $nextStage, $data);
            }

            // Handle stage-specific logic
            $this->workflowService->handleStageAdvancement($session, $nextStage, $data);

            // Send notifications
            $this->notificationService->sendStageAdvancementNotification($session, $nextStage);

            DB::commit();

            Log::info("Session advanced to next stage", [
                'session_id' => $session->id,
                'from_stage' => $session->getOriginal('current_stage'),
                'to_stage' => $nextStage
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to advance session to next stage", [
                'error' => $e->getMessage(),
                'session_id' => $session->id
            ]);
            return false;
        }
    }

    /**
     * Reject session with reason
     *
     * @param RecruitmentSession $session
     * @param string $reason
     * @param int|null $decisionBy
     * @return bool
     */
    public function rejectSession(RecruitmentSession $session, string $reason, int $decisionBy = null): bool
    {
        DB::beginTransaction();

        try {
            // Update session status
            $session->update([
                'stage_status' => 'failed',
                'stage_completed_at' => now(),
                'status' => 'rejected',
                'final_decision_date' => now(),
                'final_decision_by' => $decisionBy ?? auth()->id(),
                'final_decision_notes' => $reason,
            ]);

            // Record stage completion
            $session->recordStageCompletion();

            // Update candidate global status
            $session->candidate->updateGlobalStatus();

            // Send rejection notification
            $this->notificationService->sendRejectionNotification($session, $reason);

            DB::commit();

            Log::info("Session rejected", [
                'session_id' => $session->id,
                'reason' => $reason,
                'decision_by' => $decisionBy
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to reject session", [
                'error' => $e->getMessage(),
                'session_id' => $session->id
            ]);
            return false;
        }
    }

    /**
     * Complete session (hire candidate)
     *
     * @param RecruitmentSession $session
     * @param array $data
     * @return bool
     */
    public function completeSession(RecruitmentSession $session, array $data = []): bool
    {
        DB::beginTransaction();

        try {
            // Update session to hired
            $session->update([
                'current_stage' => 'onboarding',
                'stage_status' => 'completed',
                'stage_completed_at' => now(),
                'overall_progress' => 100,
                'status' => 'hired',
                'final_decision_date' => now(),
                'final_decision_by' => $data['decision_by'] ?? auth()->id(),
                'final_decision_notes' => $data['notes'] ?? null,
            ]);

            // Record stage completion
            $session->recordStageCompletion();

            // Update FPTK positions filled
            $session->fptk->incrementPositionsFilled();

            // Update candidate global status
            $session->candidate->update(['global_status' => 'hired']);

            // Send hire notification
            $this->notificationService->sendHireNotification($session);

            DB::commit();

            Log::info("Session completed - candidate hired", [
                'session_id' => $session->id,
                'candidate_id' => $session->candidate_id,
                'fptk_id' => $session->fptk_id
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to complete session", [
                'error' => $e->getMessage(),
                'session_id' => $session->id
            ]);
            return false;
        }
    }

    /**
     * Update session progress
     *
     * @param string $sessionId
     * @param array $data
     * @return bool
     */
    public function updateSessionProgress(string $sessionId, array $data = []): bool
    {
        $session = RecruitmentSession::find($sessionId);
        if (!$session) {
            return false;
        }

        try {
            $updateData = [];

            if (isset($data['stage_status'])) {
                $updateData['stage_status'] = $data['stage_status'];
            }

            if (isset($data['next_action'])) {
                $updateData['next_action'] = $data['next_action'];
            }

            if (isset($data['responsible_person_id'])) {
                $updateData['responsible_person_id'] = $data['responsible_person_id'];
            }

            // Update stage start/completion times
            if (isset($data['stage_status'])) {
                if ($data['stage_status'] === 'in_progress' && $session->stage_status === 'pending') {
                    $updateData['stage_started_at'] = now();
                } elseif ($data['stage_status'] === 'completed' && $session->stage_status === 'in_progress') {
                    $updateData['stage_completed_at'] = now();
                }
            }

            $session->update($updateData);

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to update session progress", [
                'error' => $e->getMessage(),
                'session_id' => $sessionId
            ]);
            return false;
        }
    }

    /**
     * Get session timeline with duration tracking
     *
     * @param string $sessionId
     * @return array
     */
    public function getSessionTimeline(string $sessionId): array
    {
        $session = RecruitmentSession::find($sessionId);
        if (!$session) {
            return [];
        }

        $timeline = [];
        $stageDurations = $session->stage_durations ?? [];

        foreach (RecruitmentSession::STAGES as $stage) {
            $stageData = [
                'stage' => $stage,
                'name' => $this->getStageDisplayName($stage),
                'progress' => RecruitmentSession::STAGE_PROGRESS[$stage],
                'target_hours' => RecruitmentSession::STAGE_DURATION_TARGETS[$stage],
                'status' => 'pending',
                'started_at' => null,
                'completed_at' => null,
                'duration_hours' => 0,
                'is_current' => $session->current_stage === $stage,
                'is_overdue' => false,
            ];

            if (isset($stageDurations[$stage])) {
                $stageData['status'] = 'completed';
                $stageData['started_at'] = $stageDurations[$stage]['started'];
                $stageData['completed_at'] = $stageDurations[$stage]['completed'];
                $stageData['duration_hours'] = $stageDurations[$stage]['duration_hours'];
                $stageData['is_overdue'] = $stageDurations[$stage]['duration_hours'] > RecruitmentSession::STAGE_DURATION_TARGETS[$stage];
            } elseif ($session->current_stage === $stage) {
                $stageData['status'] = $session->stage_status;
                $stageData['started_at'] = $session->stage_started_at;
                $stageData['duration_hours'] = $session->getCurrentStageDurationAttribute();
                $stageData['is_overdue'] = $session->getIsOverdueAttribute();
            }

            $timeline[] = $stageData;
        }

        return $timeline;
    }

    /**
     * Get progress percentage for session
     *
     * @param RecruitmentSession $session
     * @return float
     */
    public function getProgressPercentage(RecruitmentSession $session): float
    {
        return $session->overall_progress;
    }

    /**
     * Create initial CV Review assessment
     *
     * @param RecruitmentSession $session
     * @return void
     */
    protected function createInitialAssessment(RecruitmentSession $session): void
    {
        RecruitmentAssessment::create([
            'session_id' => $session->id,
            'assessment_type' => 'cv_review',
            'status' => 'scheduled',
            'max_score' => 100,
            'passing_score' => 70,
        ]);
    }

    /**
     * Create assessment for specific stage
     *
     * @param RecruitmentSession $session
     * @param string $stage
     * @param array $data
     * @return void
     */
    protected function createAssessmentForStage(RecruitmentSession $session, string $stage, array $data = []): void
    {
        $assessmentData = [
            'session_id' => $session->id,
            'assessment_type' => $stage,
            'status' => 'scheduled',
            'max_score' => 100,
            'passing_score' => $this->getPassingScoreForStage($stage),
        ];

        if (isset($data['scheduled_date'])) {
            $assessmentData['scheduled_date'] = $data['scheduled_date'];
        }

        if (isset($data['assessor_ids'])) {
            $assessmentData['assessor_ids'] = $data['assessor_ids'];
        }

        RecruitmentAssessment::create($assessmentData);
    }

    /**
     * Get passing score for stage
     *
     * @param string $stage
     * @return int
     */
    protected function getPassingScoreForStage(string $stage): int
    {
        $passingScores = [
            'cv_review' => 70,
            'psikotes' => 60,
            'tes_teori' => 75,
            'interview_hr' => 70,
            'interview_user' => 75,
            'mcu' => 80,
        ];

        return $passingScores[$stage] ?? 70;
    }

    /**
     * Get stage display name
     *
     * @param string $stage
     * @return string
     */
    protected function getStageDisplayName(string $stage): string
    {
        $names = [
            'cv_review' => 'CV Review',
            'psikotes' => 'Psikotes',
            'tes_teori' => 'Tes Teori',
            'interview_hr' => 'Interview HR',
            'interview_user' => 'Interview User',
            'offering' => 'Offering',
            'mcu' => 'MCU',
            'hire' => 'Hire',
            'onboarding' => 'Onboarding',
        ];

        return $names[$stage] ?? ucfirst(str_replace('_', ' ', $stage));
    }
}
