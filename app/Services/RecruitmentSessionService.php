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
     * @return array
     */
    public function advanceToNextStage(RecruitmentSession $session, array $data = []): array
    {
        // Handle assessment data for current stage before advancing
        if (isset($data['assessment_data']) && !empty($data['assessment_data'])) {
            $assessmentResult = $this->processAssessmentData($session, $data['assessment_data']);
            if (!$assessmentResult['success']) {
                return $assessmentResult;
            }

            // If session was ended (rejected), return the result
            if (isset($assessmentResult['session_ended']) && $assessmentResult['session_ended']) {
                return $assessmentResult;
            }

            // If stage was completed by assessment processing, refresh session
            $session->refresh();
        }

        // Check if current stage has required assessments that need to be completed
        $requiresAssessment = in_array($session->current_stage, ['psikotes', 'tes_teori', 'interview_hr', 'interview_user', 'mcu']);

        if ($requiresAssessment) {
            $assessment = $session->assessments()
                ->where('assessment_type', $session->current_stage)
                ->where('status', 'completed')
                ->first();

            if (!$assessment) {
                $stageNames = [
                    'psikotes' => 'Psikotes',
                    'tes_teori' => 'Tes Teori',
                    'interview_hr' => 'Interview HR',
                    'interview_user' => 'Interview User',
                    'mcu' => 'MCU'
                ];

                $stageName = $stageNames[$session->current_stage] ?? $session->current_stage;

                return [
                    'success' => false,
                    'message' => "Assessment for '{$stageName}' stage must be completed before advancing to the next stage."
                ];
            }
        }

        // First, complete the current stage if it's not already completed
        if ($session->stage_status !== 'completed') {
            if ($session->stage_status === 'pending') {
                // Start the stage first
                $session->update(['stage_status' => 'in_progress']);
            }

            // Complete the current stage
            $session->update([
                'stage_status' => 'completed',
                'stage_completed_at' => now()
            ]);

            Log::info("Current stage marked as completed before advancement", [
                'session_id' => $session->id,
                'stage' => $session->current_stage
            ]);
        }

        // Refresh session data after updates
        $session->refresh();

        // Now check if we can advance with detailed error messages
        if (!$session->canAdvanceToNextStage()) {
            $stageNames = [
                'cv_review' => 'CV Review',
                'psikotes' => 'Psikotes',
                'tes_teori' => 'Tes Teori',
                'interview_hr' => 'Interview HR',
                'interview_user' => 'Interview User',
                'offering' => 'Offering',
                'mcu' => 'MCU',
                'hire' => 'Hire',
                'onboarding' => 'Onboarding'
            ];

            $currentStageName = $stageNames[$session->current_stage] ?? $session->current_stage;
            $errorMessage = '';

            if ($session->status !== 'in_process') {
                $errorMessage = "Session cannot be advanced because it is not in process (current status: {$session->status}).";
            } elseif ($session->stage_status !== 'completed') {
                $errorMessage = "Current stage '{$currentStageName}' must be completed before advancing to the next stage (current status: {$session->stage_status}).";
            } elseif (!$session->getNextStageAttribute()) {
                $errorMessage = "Current stage '{$currentStageName}' is the final stage. Cannot advance further.";
            } else {
                $errorMessage = "Session cannot advance from '{$currentStageName}' stage. Please check stage requirements.";
            }

            Log::warning("Session cannot advance to next stage", [
                'session_id' => $session->id,
                'current_stage' => $session->current_stage,
                'stage_status' => $session->stage_status,
                'session_status' => $session->status,
                'error_message' => $errorMessage
            ]);

            return [
                'success' => false,
                'message' => $errorMessage
            ];
        }

        // If we have assessment data and stage is completed, just return success
        if (isset($data['assessment_data']) && !empty($data['assessment_data']) && $session->stage_status === 'completed') {
            return [
                'success' => true,
                'message' => 'Stage completed successfully.'
            ];
        }

        DB::beginTransaction();

        try {
            // Get next stage
            $nextStage = $session->getNextStageAttribute();
            if (!$nextStage) {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'No next stage available. This session may already be at the final stage.'
                ];
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

            return [
                'success' => true,
                'message' => 'Session successfully advanced to the next stage.'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Failed to advance session to next stage", [
                'error' => $e->getMessage(),
                'session_id' => $session->id
            ]);
            return [
                'success' => false,
                'message' => 'An error occurred while advancing the session: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Process assessment data for current stage
     *
     * @param RecruitmentSession $session
     * @param array $assessmentData
     * @return array
     */
    protected function processAssessmentData(RecruitmentSession $session, array $assessmentData): array
    {
        try {
            $currentStage = $session->current_stage;

            switch ($currentStage) {
                case 'cv_review':
                    return $this->processCVReviewAssessment($session, $assessmentData);

                case 'psikotes':
                    return $this->processPsikotesAssessment($session, $assessmentData);

                case 'tes_teori':
                    return $this->processTesTeoriAssessment($session, $assessmentData);

                case 'interview_hr':
                    return $this->processInterviewHrAssessment($session, $assessmentData);

                case 'interview_user':
                    return $this->processInterviewUserAssessment($session, $assessmentData);

                case 'mcu':
                    return $this->processMcuAssessment($session, $assessmentData);

                case 'offering':
                    return $this->processOfferingAssessment($session, $assessmentData);

                case 'hire':
                    return $this->processHireAssessment($session, $assessmentData);

                case 'onboarding':
                    return $this->processOnboardingAssessment($session, $assessmentData);

                default:
                    // For other stages, just log the data
                    Log::info("Assessment data received for stage", [
                        'session_id' => $session->id,
                        'stage' => $currentStage,
                        'data' => $assessmentData
                    ]);
                    return ['success' => true];
            }
        } catch (\Exception $e) {
            Log::error("Error processing assessment data", [
                'session_id' => $session->id,
                'stage' => $session->current_stage,
                'error' => $e->getMessage()
            ]);
            return [
                'success' => false,
                'message' => 'Error processing assessment data: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Process CV Review assessment
     *
     * @param RecruitmentSession $session
     * @param array $assessmentData
     * @return array
     */
    protected function processCVReviewAssessment(RecruitmentSession $session, array $assessmentData): array
    {
        $decision = $assessmentData['decision'] ?? null;

        if (!$decision || !in_array($decision, ['pass', 'fail'])) {
            return [
                'success' => false,
                'message' => 'Invalid CV review decision. Must be either "pass" or "fail".'
            ];
        }

        // Find or create CV review assessment
        $assessment = $session->assessments()
            ->where('assessment_type', 'cv_review')
            ->first();

        if (!$assessment) {
            $assessment = RecruitmentAssessment::create([
                'session_id' => $session->id,
                'assessment_type' => 'cv_review',
                'status' => 'completed',
                'max_score' => 100,
                'passing_score' => 70,
                'overall_score' => $decision === 'pass' ? 100 : 0,
                'assessment_data' => $assessmentData,
                'assessor_notes' => $assessmentData['notes'] ?? null,
                'completed_at' => now(),
            ]);
        } else {
            $assessment->update([
                'status' => 'completed',
                'overall_score' => $decision === 'pass' ? 100 : 0,
                'assessment_data' => $assessmentData,
                'assessor_notes' => $assessmentData['notes'] ?? null,
                'completed_at' => now(),
            ]);
        }

        // If CV review fails, reject the session
        if ($decision === 'fail') {
            $session->update([
                'stage_status' => 'failed',
                'stage_completed_at' => now(),
                'status' => 'rejected',
                'final_decision_date' => now(),
                'final_decision_by' => auth()->id(),
                'final_decision_notes' => 'CV Review: Failed - ' . ($assessmentData['notes'] ?? 'No specific reason provided'),
            ]);

            // Update candidate global status
            $session->candidate->updateGlobalStatus();

            return [
                'success' => true,
                'message' => 'CV Review completed. Candidate rejected due to failed CV review.',
                'session_ended' => true
            ];
        }

        // If CV review passes, complete the stage and advance to next stage
        $session->update([
            'stage_status' => 'completed',
            'stage_completed_at' => now(),
        ]);

        // Automatically advance to next stage (psikotes)
        $nextStage = $this->getNextStage($session->current_stage);
        if ($nextStage) {
            $session->update([
                'current_stage' => $nextStage,
                'stage_status' => 'pending',
                'stage_started_at' => now(),
                'overall_progress' => $this->getProgressForStage($nextStage),
            ]);

            return [
                'success' => true,
                'message' => 'CV Review completed successfully. Candidate passed CV review and advanced to Psikotes stage.',
                'auto_advanced' => true,
                'next_stage' => $nextStage
            ];
        }

        return [
            'success' => true,
            'message' => 'CV Review completed successfully. Candidate passed CV review.'
        ];
    }

    /**
     * Process Psikotes assessment
     *
     * @param RecruitmentSession $session
     * @param array $assessmentData
     * @return array
     */
    protected function processPsikotesAssessment(RecruitmentSession $session, array $assessmentData): array
    {
        $onlineScore = $assessmentData['online_score'] ?? null;
        $offlineScore = $assessmentData['offline_score'] ?? null;
        $overallResult = $assessmentData['overall_result'] ?? null;
        $resultDetails = $assessmentData['result_details'] ?? '';

        // Validate that at least one score is provided
        if (is_null($onlineScore) && is_null($offlineScore)) {
            return [
                'success' => false,
                'message' => 'At least one score (online or offline) must be provided.'
            ];
        }

        // Calculate overall result if not provided
        if (!$overallResult) {
            $overallResult = 'pass';

            if (!is_null($onlineScore) && $onlineScore < 40) {
                $overallResult = 'fail';
            }

            if (!is_null($offlineScore) && $offlineScore < 8) {
                $overallResult = 'fail';
            }
        }

        // Find or create psikotes assessment
        $assessment = $session->assessments()
            ->where('assessment_type', 'psikotes')
            ->first();

        if (!$assessment) {
            $assessment = RecruitmentAssessment::create([
                'session_id' => $session->id,
                'assessment_type' => 'psikotes',
                'status' => 'completed',
                'max_score' => 100,
                'passing_score' => 60,
                'overall_score' => $overallResult === 'pass' ? 100 : 0,
                'assessment_data' => $assessmentData,
                'assessor_notes' => $assessmentData['notes'] ?? $resultDetails,
                'completed_at' => now(),
            ]);
        } else {
            $assessment->update([
                'status' => 'completed',
                'overall_score' => $overallResult === 'pass' ? 100 : 0,
                'assessment_data' => $assessmentData,
                'assessor_notes' => $assessmentData['notes'] ?? $resultDetails,
                'completed_at' => now(),
            ]);
        }

        // If psikotes fails, reject the session
        if ($overallResult === 'fail') {
            $session->update([
                'stage_status' => 'failed',
                'stage_completed_at' => now(),
                'status' => 'rejected',
                'final_decision_date' => now(),
                'final_decision_by' => auth()->id(),
                'final_decision_notes' => 'Psikotes: Failed - ' . ($assessmentData['notes'] ?? $resultDetails),
            ]);

            // Update candidate global status
            $session->candidate->updateGlobalStatus();

            return [
                'success' => true,
                'message' => 'Psikotes assessment completed. Candidate rejected due to failed psikotes.',
                'session_ended' => true
            ];
        }

        // If psikotes passes, complete the stage and advance to next stage
        $session->update([
            'stage_status' => 'completed',
            'stage_completed_at' => now(),
        ]);

        // Automatically advance to next stage (tes teori)
        $nextStage = $this->getNextStage($session->current_stage);
        if ($nextStage) {
            $session->update([
                'current_stage' => $nextStage,
                'stage_status' => 'pending',
                'stage_started_at' => now(),
                'overall_progress' => $this->getProgressForStage($nextStage),
            ]);

            return [
                'success' => true,
                'message' => 'Psikotes assessment completed successfully. Candidate passed psikotes and advanced to Tes Teori stage.',
                'auto_advanced' => true,
                'next_stage' => $nextStage
            ];
        }

        return [
            'success' => true,
            'message' => 'Psikotes assessment completed successfully. Candidate passed psikotes.'
        ];
    }

    /**
     * Process Tes Teori assessment
     *
     * @param RecruitmentSession $session
     * @param array $assessmentData
     * @return array
     */
    protected function processTesTeoriAssessment(RecruitmentSession $session, array $assessmentData): array
    {
        $score = $assessmentData['score'] ?? null;
        $result = $assessmentData['result'] ?? null;

        if ($score === null || $result === null) {
            return [
                'success' => false,
                'message' => 'Invalid tes teori assessment data. Score and result are required.'
            ];
        }

        // Find or create tes teori assessment
        $assessment = $session->assessments()
            ->where('assessment_type', 'tes_teori')
            ->first();

        if (!$assessment) {
            $assessment = RecruitmentAssessment::create([
                'session_id' => $session->id,
                'assessment_type' => 'tes_teori',
                'status' => 'completed',
                'max_score' => 100,
                'passing_score' => 75,
                'overall_score' => $score,
                'assessment_data' => $assessmentData,
                'assessor_notes' => $assessmentData['notes'] ?? null,
                'completed_at' => now(),
            ]);
        } else {
            $assessment->update([
                'status' => 'completed',
                'overall_score' => $score,
                'assessment_data' => $assessmentData,
                'assessor_notes' => $assessmentData['notes'] ?? null,
                'completed_at' => now(),
            ]);
        }

        // If tes teori fails, reject the session
        if ($result === 'fail') {
            $session->update([
                'stage_status' => 'failed',
                'stage_completed_at' => now(),
                'status' => 'rejected',
                'final_decision_date' => now(),
                'final_decision_by' => auth()->id(),
                'final_decision_notes' => 'Tes Teori: Failed - Score: ' . $score . ' (Required: ≥75)',
            ]);

            // Update candidate global status
            $session->candidate->updateGlobalStatus();

            return [
                'success' => true,
                'message' => 'Tes Teori completed. Candidate rejected due to failed tes teori.',
                'session_ended' => true
            ];
        }

        // Mark stage as completed and advance to next stage
        $session->update([
            'stage_status' => 'completed',
            'stage_completed_at' => now(),
        ]);

        // Automatically advance to next stage (interview HR)
        $nextStage = $this->getNextStage($session->current_stage);
        if ($nextStage) {
            $session->update([
                'current_stage' => $nextStage,
                'stage_status' => 'pending',
                'stage_started_at' => now(),
                'overall_progress' => $this->getProgressForStage($nextStage),
            ]);

            return [
                'success' => true,
                'message' => 'Tes Teori assessment completed successfully. Candidate passed tes teori and advanced to Interview HR stage.',
                'auto_advanced' => true,
                'next_stage' => $nextStage
            ];
        }

        return [
            'success' => true,
            'message' => 'Tes Teori assessment completed successfully. Candidate passed tes teori.'
        ];
    }

    /**
     * Process Interview HR assessment
     *
     * @param RecruitmentSession $session
     * @param array $assessmentData
     * @return array
     */
    protected function processInterviewHrAssessment(RecruitmentSession $session, array $assessmentData): array
    {
        $overall = $assessmentData['overall'] ?? null;
        $result = $assessmentData['result'] ?? null;

        if ($overall === null || $result === null) {
            return [
                'success' => false,
                'message' => 'Invalid interview HR assessment data. Overall score and result are required.'
            ];
        }

        // Find or create interview HR assessment
        $assessment = $session->assessments()
            ->where('assessment_type', 'interview_hr')
            ->first();

        if (!$assessment) {
            $assessment = RecruitmentAssessment::create([
                'session_id' => $session->id,
                'assessment_type' => 'interview_hr',
                'status' => 'completed',
                'max_score' => 100,
                'passing_score' => 70,
                'overall_score' => $overall,
                'assessment_data' => $assessmentData,
                'assessor_notes' => $assessmentData['notes'] ?? null,
                'completed_at' => now(),
            ]);
        } else {
            $assessment->update([
                'status' => 'completed',
                'overall_score' => $overall,
                'assessment_data' => $assessmentData,
                'assessor_notes' => $assessmentData['notes'] ?? null,
                'completed_at' => now(),
            ]);
        }

        // If interview HR fails, reject the session
        if ($result === 'fail') {
            $session->update([
                'stage_status' => 'failed',
                'stage_completed_at' => now(),
                'status' => 'rejected',
                'final_decision_date' => now(),
                'final_decision_by' => auth()->id(),
                'final_decision_notes' => 'Interview HR: Failed - Score: ' . $overall . ' (Required: ≥70)',
            ]);

            // Update candidate global status
            $session->candidate->updateGlobalStatus();

            return [
                'success' => true,
                'message' => 'Interview HR completed. Candidate rejected due to failed interview HR.',
                'session_ended' => true
            ];
        }

        // Mark stage as completed and advance to next stage
        $session->update([
            'stage_status' => 'completed',
            'stage_completed_at' => now(),
        ]);

        // Automatically advance to next stage (interview user)
        $nextStage = $this->getNextStage($session->current_stage);
        if ($nextStage) {
            $session->update([
                'current_stage' => $nextStage,
                'stage_status' => 'pending',
                'stage_started_at' => now(),
                'overall_progress' => $this->getProgressForStage($nextStage),
            ]);

            return [
                'success' => true,
                'message' => 'Interview HR assessment completed successfully. Candidate passed interview HR and advanced to Interview User stage.',
                'auto_advanced' => true,
                'next_stage' => $nextStage
            ];
        }

        return [
            'success' => true,
            'message' => 'Interview HR assessment completed successfully. Candidate passed interview HR.'
        ];
    }

    /**
     * Process Interview User assessment
     *
     * @param RecruitmentSession $session
     * @param array $assessmentData
     * @return array
     */
    protected function processInterviewUserAssessment(RecruitmentSession $session, array $assessmentData): array
    {
        $overall = $assessmentData['overall'] ?? null;
        $result = $assessmentData['result'] ?? null;

        if ($overall === null || $result === null) {
            return [
                'success' => false,
                'message' => 'Invalid interview user assessment data. Overall score and result are required.'
            ];
        }

        // Find or create interview user assessment
        $assessment = $session->assessments()
            ->where('assessment_type', 'interview_user')
            ->first();

        if (!$assessment) {
            $assessment = RecruitmentAssessment::create([
                'session_id' => $session->id,
                'assessment_type' => 'interview_user',
                'status' => 'completed',
                'max_score' => 100,
                'passing_score' => 75,
                'overall_score' => $overall,
                'assessment_data' => $assessmentData,
                'assessor_notes' => $assessmentData['notes'] ?? null,
                'completed_at' => now(),
            ]);
        } else {
            $assessment->update([
                'status' => 'completed',
                'overall_score' => $overall,
                'assessment_data' => $assessmentData,
                'assessor_notes' => $assessmentData['notes'] ?? null,
                'completed_at' => now(),
            ]);
        }

        // If interview user fails, reject the session
        if ($result === 'fail') {
            $session->update([
                'stage_status' => 'failed',
                'stage_completed_at' => now(),
                'status' => 'rejected',
                'final_decision_date' => now(),
                'final_decision_by' => auth()->id(),
                'final_decision_notes' => 'Interview User: Failed - Score: ' . $overall . ' (Required: ≥75)',
            ]);

            // Update candidate global status
            $session->candidate->updateGlobalStatus();

            return [
                'success' => true,
                'message' => 'Interview User completed. Candidate rejected due to failed interview user.',
                'session_ended' => true
            ];
        }

        // Mark stage as completed and advance to next stage
        $session->update([
            'stage_status' => 'completed',
            'stage_completed_at' => now(),
        ]);

        // Automatically advance to next stage (offering)
        $nextStage = $this->getNextStage($session->current_stage);
        if ($nextStage) {
            $session->update([
                'current_stage' => $nextStage,
                'stage_status' => 'pending',
                'stage_started_at' => now(),
                'overall_progress' => $this->getProgressForStage($nextStage),
            ]);

            return [
                'success' => true,
                'message' => 'Interview User assessment completed successfully. Candidate passed interview user and advanced to Offering stage.',
                'auto_advanced' => true,
                'next_stage' => $nextStage
            ];
        }

        return [
            'success' => true,
            'message' => 'Interview User assessment completed successfully. Candidate passed interview user.'
        ];
    }

    /**
     * Process MCU assessment
     *
     * @param RecruitmentSession $session
     * @param array $assessmentData
     * @return array
     */
    protected function processMcuAssessment(RecruitmentSession $session, array $assessmentData): array
    {
        $overallHealth = $assessmentData['overall_health'] ?? null;
        $result = $assessmentData['result'] ?? null;

        if ($overallHealth === null || $result === null) {
            return [
                'success' => false,
                'message' => 'Invalid MCU assessment data. Overall health condition and result are required.'
            ];
        }

        // Find or create MCU assessment
        $assessment = $session->assessments()
            ->where('assessment_type', 'mcu')
            ->first();

        if (!$assessment) {
            $assessment = RecruitmentAssessment::create([
                'session_id' => $session->id,
                'assessment_type' => 'mcu',
                'status' => 'completed',
                'max_score' => 100,
                'passing_score' => 70,
                'overall_score' => $result === 'pass' ? 100 : ($result === 'conditional' ? 50 : 0),
                'assessment_data' => $assessmentData,
                'assessor_notes' => $assessmentData['notes'] ?? null,
                'completed_at' => now(),
            ]);
        } else {
            $assessment->update([
                'status' => 'completed',
                'overall_score' => $result === 'pass' ? 100 : ($result === 'conditional' ? 50 : 0),
                'assessment_data' => $assessmentData,
                'assessor_notes' => $assessmentData['notes'] ?? null,
                'completed_at' => now(),
            ]);
        }

        // If MCU fails, reject the session
        if ($result === 'fail') {
            $session->update([
                'stage_status' => 'failed',
                'stage_completed_at' => now(),
                'status' => 'rejected',
                'final_decision_date' => now(),
                'final_decision_by' => auth()->id(),
                'final_decision_notes' => 'MCU: Failed - Health condition: ' . $overallHealth,
            ]);

            // Update candidate global status
            $session->candidate->updateGlobalStatus();

            return [
                'success' => true,
                'message' => 'MCU completed. Candidate rejected due to failed medical check up.',
                'session_ended' => true
            ];
        }

        // Mark stage as completed and advance to next stage
        $session->update([
            'stage_status' => 'completed',
            'stage_completed_at' => now(),
        ]);

        // Automatically advance to next stage (hire)
        $nextStage = $this->getNextStage($session->current_stage);
        if ($nextStage) {
            $session->update([
                'current_stage' => $nextStage,
                'stage_status' => 'pending',
                'stage_started_at' => now(),
                'overall_progress' => $this->getProgressForStage($nextStage),
            ]);

            return [
                'success' => true,
                'message' => 'MCU assessment completed successfully. Candidate passed medical check up and advanced to Hire stage.',
                'auto_advanced' => true,
                'next_stage' => $nextStage
            ];
        }

        return [
            'success' => true,
            'message' => 'MCU assessment completed successfully. Candidate passed medical check up.'
        ];
    }

    /**
     * Process Offering assessment
     *
     * @param RecruitmentSession $session
     * @param array $assessmentData
     * @return array
     */
    protected function processOfferingAssessment(RecruitmentSession $session, array $assessmentData): array
    {
        $status = $assessmentData['status'] ?? null;

        if (!$status) {
            return [
                'success' => false,
                'message' => 'Invalid offering assessment data. Status is required.'
            ];
        }

        // Find or create offering assessment
        $assessment = $session->assessments()
            ->where('assessment_type', 'offering')
            ->first();

        if (!$assessment) {
            $assessment = RecruitmentAssessment::create([
                'session_id' => $session->id,
                'assessment_type' => 'offering',
                'status' => 'completed',
                'max_score' => 100,
                'passing_score' => 70,
                'overall_score' => $status === 'accepted' ? 100 : ($status === 'negotiating' ? 50 : 0),
                'assessment_data' => $assessmentData,
                'assessor_notes' => $assessmentData['notes'] ?? null,
                'completed_at' => now(),
            ]);
        } else {
            $assessment->update([
                'status' => 'completed',
                'overall_score' => $status === 'accepted' ? 100 : ($status === 'negotiating' ? 50 : 0),
                'assessment_data' => $assessmentData,
                'assessor_notes' => $assessmentData['notes'] ?? null,
                'completed_at' => now(),
            ]);
        }

        // If offering is rejected, reject the session
        if ($status === 'rejected') {
            $session->update([
                'stage_status' => 'failed',
                'stage_completed_at' => now(),
                'status' => 'rejected',
                'final_decision_date' => now(),
                'final_decision_by' => auth()->id(),
                'final_decision_notes' => 'Offering: Rejected by candidate',
            ]);

            // Update candidate global status
            $session->candidate->updateGlobalStatus();

            return [
                'success' => true,
                'message' => 'Offering completed. Candidate rejected the offer.',
                'session_ended' => true
            ];
        }

        // Mark stage as completed and advance to next stage
        $session->update([
            'stage_status' => 'completed',
            'stage_completed_at' => now(),
        ]);

        // Automatically advance to next stage (mcu)
        $nextStage = $this->getNextStage($session->current_stage);
        if ($nextStage) {
            $session->update([
                'current_stage' => $nextStage,
                'stage_status' => 'pending',
                'stage_started_at' => now(),
                'overall_progress' => $this->getProgressForStage($nextStage),
            ]);

            return [
                'success' => true,
                'message' => 'Offering assessment completed successfully. Candidate accepted offer and advanced to MCU stage.',
                'auto_advanced' => true,
                'next_stage' => $nextStage
            ];
        }

        return [
            'success' => true,
            'message' => 'Offering assessment completed successfully.'
        ];
    }

    /**
     * Process Hire assessment
     *
     * @param RecruitmentSession $session
     * @param array $assessmentData
     * @return array
     */
    protected function processHireAssessment(RecruitmentSession $session, array $assessmentData): array
    {
        $hireDate = $assessmentData['hire_date'] ?? null;

        if (!$hireDate) {
            return [
                'success' => false,
                'message' => 'Invalid hire assessment data. Hire date is required.'
            ];
        }

        // Find or create hire assessment
        $assessment = $session->assessments()
            ->where('assessment_type', 'hire')
            ->first();

        if (!$assessment) {
            $assessment = RecruitmentAssessment::create([
                'session_id' => $session->id,
                'assessment_type' => 'hire',
                'status' => 'completed',
                'max_score' => 100,
                'passing_score' => 70,
                'overall_score' => 100,
                'assessment_data' => $assessmentData,
                'assessor_notes' => $assessmentData['notes'] ?? null,
                'completed_at' => now(),
            ]);
        } else {
            $assessment->update([
                'status' => 'completed',
                'overall_score' => 100,
                'assessment_data' => $assessmentData,
                'assessor_notes' => $assessmentData['notes'] ?? null,
                'completed_at' => now(),
            ]);
        }

        // Mark stage as completed and advance to next stage
        $session->update([
            'stage_status' => 'completed',
            'stage_completed_at' => now(),
        ]);

        // Automatically advance to next stage (onboarding)
        $nextStage = $this->getNextStage($session->current_stage);
        if ($nextStage) {
            $session->update([
                'current_stage' => $nextStage,
                'stage_status' => 'pending',
                'stage_started_at' => now(),
                'overall_progress' => $this->getProgressForStage($nextStage),
            ]);

            return [
                'success' => true,
                'message' => 'Hire assessment completed successfully. Candidate hired and advanced to Onboarding stage.',
                'auto_advanced' => true,
                'next_stage' => $nextStage
            ];
        }

        return [
            'success' => true,
            'message' => 'Hire assessment completed successfully.'
        ];
    }

    /**
     * Process Onboarding assessment
     *
     * @param RecruitmentSession $session
     * @param array $assessmentData
     * @return array
     */
    protected function processOnboardingAssessment(RecruitmentSession $session, array $assessmentData): array
    {
        $status = $assessmentData['status'] ?? null;

        if (!$status) {
            return [
                'success' => false,
                'message' => 'Invalid onboarding assessment data. Status is required.'
            ];
        }

        // Find or create onboarding assessment
        $assessment = $session->assessments()
            ->where('assessment_type', 'onboarding')
            ->first();

        if (!$assessment) {
            $assessment = RecruitmentAssessment::create([
                'session_id' => $session->id,
                'assessment_type' => 'onboarding',
                'status' => 'completed',
                'max_score' => 100,
                'passing_score' => 70,
                'overall_score' => $status === 'completed' ? 100 : ($status === 'in_progress' ? 50 : 0),
                'assessment_data' => $assessmentData,
                'assessor_notes' => $assessmentData['notes'] ?? null,
                'completed_at' => now(),
            ]);
        } else {
            $assessment->update([
                'status' => 'completed',
                'overall_score' => $status === 'completed' ? 100 : ($status === 'in_progress' ? 50 : 0),
                'assessment_data' => $assessmentData,
                'assessor_notes' => $assessmentData['notes'] ?? null,
                'completed_at' => now(),
            ]);
        }

        // Mark stage as completed and complete the session
        $session->update([
            'stage_status' => 'completed',
            'stage_completed_at' => now(),
            'status' => 'hired',
            'final_decision_date' => now(),
            'final_decision_by' => auth()->id(),
            'final_decision_notes' => 'Onboarding completed successfully',
            'overall_progress' => 100.0,
        ]);

        return [
            'success' => true,
            'message' => 'Onboarding assessment completed successfully. Recruitment session completed and candidate is fully hired.',
            'session_completed' => true
        ];
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

    /**
     * Get next stage in the recruitment workflow
     *
     * @param string $currentStage
     * @return string|null
     */
    protected function getNextStage(string $currentStage): ?string
    {
        $stageOrder = [
            'cv_review' => 'psikotes',
            'psikotes' => 'tes_teori',
            'tes_teori' => 'interview_hr',
            'interview_hr' => 'interview_user',
            'interview_user' => 'offering',
            'offering' => 'mcu',
            'mcu' => 'hire',
            'hire' => 'onboarding',
            'onboarding' => null // Final stage
        ];

        return $stageOrder[$currentStage] ?? null;
    }

    /**
     * Get progress percentage for a specific stage
     *
     * @param string $stage
     * @return float
     */
    protected function getProgressForStage(string $stage): float
    {
        $stageProgress = [
            'cv_review' => 10.0,
            'psikotes' => 20.0,
            'tes_teori' => 30.0,
            'interview_hr' => 40.0,
            'interview_user' => 50.0,
            'offering' => 60.0,
            'mcu' => 70.0,
            'hire' => 80.0,
            'onboarding' => 90.0
        ];

        return $stageProgress[$stage] ?? 0.0;
    }
}
