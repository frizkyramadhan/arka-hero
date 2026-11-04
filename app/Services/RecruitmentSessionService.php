<?php

namespace App\Services;

use App\Models\RecruitmentSession;
use App\Models\RecruitmentRequest;
use App\Models\RecruitmentCandidate;
use App\Models\RecruitmentCvReview;
use App\Models\RecruitmentPsikotes;
use App\Models\RecruitmentTesTeori;
use App\Models\RecruitmentInterview;
use App\Models\RecruitmentOffering;
use App\Models\RecruitmentMcu;
use App\Models\RecruitmentHiring;
use App\Models\RecruitmentDocument;

use App\Models\User;
use App\Services\RecruitmentLetterNumberService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
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

            $isSimplified = $fptk->usesSimplifiedRecruitmentFlow();

            // Create session
            $session = RecruitmentSession::create([
                'session_number' => RecruitmentSession::generateSessionNumber(),
                'fptk_id' => $fptkId,
                'candidate_id' => $candidateId,
                'applied_date' => now()->toDateString(),
                'source' => $data['source'] ?? 'website',
                'current_stage' => $isSimplified ? 'mcu' : 'cv_review',
                'stage_status' => 'pending',
                'stage_started_at' => now(),
                'overall_progress' => $isSimplified ? 0 : 14.3,
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
        if ($session->isParentOnHold()) {
            return [
                'success' => false,
                'message' => $session->parentHoldBlockMessage(),
            ];
        }

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
            $assessment = $session->getAssessmentByStage($session->current_stage);

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
                'overall_progress' => $session->calculateActualProgress(),
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
    public function processCVReviewAssessment(RecruitmentSession $session, array $assessmentData): array
    {
        $decision = $assessmentData['decision'] ?? null;

        if (!$decision || !in_array($decision, ['recommended', 'not_recommended'])) {
            return [
                'success' => false,
                'message' => 'Invalid CV review decision. Must be either "recommended" or "not_recommended".'
            ];
        }

        // Create or update CV review assessment
        $cvReview = $session->cvReview;
        if (!$cvReview) {
            $cvReview = RecruitmentCvReview::create([
                'session_id' => $session->id,
                'decision' => $decision,
                'notes' => $assessmentData['notes'] ?? null,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => $assessmentData['reviewed_at'] ?? now(),
            ]);
        } else {
            $cvReview->update([
                'decision' => $decision,
                'notes' => $assessmentData['notes'] ?? null,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => $assessmentData['reviewed_at'] ?? now(),
            ]);
        }

        // If CV review fails, reject the session
        if ($decision === 'not_recommended') {
            $session->update([
                'stage_status' => 'failed',
                'stage_completed_at' => now(),
                'status' => 'rejected',
                'final_decision_date' => now(),
                'final_decision_by' => auth()->id(),
                'final_decision_notes' => 'CV Review: Not Recommended - ' . ($assessmentData['notes'] ?? 'No specific reason provided'),
            ]);

            // Update candidate global status
            $session->candidate->updateGlobalStatus();

            return [
                'success' => true,
                'message' => 'CV Review completed. Candidate rejected due to not recommended CV review.',
                'session_ended' => true
            ];
        }

        // If CV review passes, complete the stage and advance to next stage
        $session->update([
            'stage_status' => 'completed',
            'stage_completed_at' => now(),
            'overall_progress' => $session->calculateActualProgress(),
        ]);

        // Automatically advance to next stage (psikotes)
        $nextStage = $session->getNextStageAttribute();
        if ($nextStage) {
            $session->update([
                'current_stage' => $nextStage,
                'stage_status' => 'pending',
                'stage_started_at' => now(),
                'overall_progress' => $session->calculateActualProgress(),
            ]);

            return [
                'success' => true,
                'message' => 'CV Review completed successfully. Candidate recommended and advanced to Psikotes stage.',
                'auto_advanced' => true,
                'next_stage' => $nextStage
            ];
        }

        return [
            'success' => true,
            'message' => 'CV Review completed successfully. Candidate recommended.'
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

        // Create or update psikotes assessment
        $psikotes = $session->psikotes;
        if (!$psikotes) {
            $psikotes = RecruitmentPsikotes::create([
                'session_id' => $session->id,
                'online_score' => $onlineScore,
                'offline_score' => $offlineScore,
                'result' => $overallResult,
                'notes' => $assessmentData['notes'] ?? $resultDetails,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => $assessmentData['reviewed_at'] ?? now(),
            ]);
        } else {
            $psikotes->update([
                'online_score' => $onlineScore,
                'offline_score' => $offlineScore,
                'result' => $overallResult,
                'notes' => $assessmentData['notes'] ?? $resultDetails,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => $assessmentData['reviewed_at'] ?? now(),
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
            'overall_progress' => $session->calculateActualProgress(),
        ]);

        // Automatically advance to next stage (tes teori)
        $nextStage = $session->getNextStageAttribute();
        if ($nextStage) {
            $session->update([
                'current_stage' => $nextStage,
                'stage_status' => 'pending',
                'stage_started_at' => now(),
                'overall_progress' => $session->calculateActualProgress(),
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

        // Create or update tes teori assessment
        $tesTeori = $session->tesTeori;
        if (!$tesTeori) {
            $tesTeori = RecruitmentTesTeori::create([
                'session_id' => $session->id,
                'score' => $score,
                'result' => $result,
                'notes' => $assessmentData['notes'] ?? null,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => $assessmentData['reviewed_at'] ?? now(),
            ]);
        } else {
            $tesTeori->update([
                'score' => $score,
                'result' => $result,
                'notes' => $assessmentData['notes'] ?? null,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => $assessmentData['reviewed_at'] ?? now(),
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
            'overall_progress' => $session->calculateActualProgress(),
        ]);

        // Automatically advance to next stage (interview HR)
        $nextStage = $session->getNextStageAttribute();
        if ($nextStage) {
            $session->update([
                'current_stage' => $nextStage,
                'stage_status' => 'pending',
                'stage_started_at' => now(),
                'overall_progress' => $session->calculateActualProgress(),
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

        // Create or update interview HR assessment
        $interviewHr = $session->interviewHr;
        if (!$interviewHr) {
            $interviewHr = RecruitmentInterview::create([
                'session_id' => $session->id,
                'type' => 'hr',
                'result' => $result === 'pass' ? 'recommended' : 'not_recommended',
                'notes' => $assessmentData['notes'] ?? null,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => $assessmentData['reviewed_at'] ?? now(),
            ]);
        } else {
            $interviewHr->update([
                'result' => $result === 'pass' ? 'recommended' : 'not_recommended',
                'notes' => $assessmentData['notes'] ?? null,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => $assessmentData['reviewed_at'] ?? now(),
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
            'overall_progress' => $session->calculateActualProgress(),
        ]);

        // Automatically advance to next stage (interview user)
        $nextStage = $session->getNextStageAttribute();
        if ($nextStage) {
            $session->update([
                'current_stage' => $nextStage,
                'stage_status' => 'pending',
                'stage_started_at' => now(),
                'overall_progress' => $session->calculateActualProgress(),
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

        // Create or update interview user assessment
        $interviewUser = $session->interviewUser;
        if (!$interviewUser) {
            $interviewUser = RecruitmentInterview::create([
                'session_id' => $session->id,
                'type' => 'user',
                'result' => $result === 'pass' ? 'recommended' : 'not_recommended',
                'notes' => $assessmentData['notes'] ?? null,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => $assessmentData['reviewed_at'] ?? now(),
            ]);
        } else {
            $interviewUser->update([
                'result' => $result === 'pass' ? 'recommended' : 'not_recommended',
                'notes' => $assessmentData['notes'] ?? null,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => $assessmentData['reviewed_at'] ?? now(),
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
            'overall_progress' => $session->calculateActualProgress(),
        ]);

        // Automatically advance to next stage (offering)
        $nextStage = $session->getNextStageAttribute();
        if ($nextStage) {
            $session->update([
                'current_stage' => $nextStage,
                'stage_status' => 'pending',
                'stage_started_at' => now(),
                'overall_progress' => $session->calculateActualProgress(),
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

        // Create or update MCU assessment
        $mcu = $session->mcu;
        if (!$mcu) {
            $mcu = RecruitmentMcu::create([
                'session_id' => $session->id,
                'result' => $result,
                'notes' => $assessmentData['notes'] ?? null,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => $assessmentData['reviewed_at'] ?? now(),
            ]);
        } else {
            $mcu->update([
                'result' => $result,
                'notes' => $assessmentData['notes'] ?? null,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => $assessmentData['reviewed_at'] ?? now(),
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
            'overall_progress' => $session->calculateActualProgress(),
        ]);

        // Automatically advance to next stage (hire)
        $nextStage = $session->getNextStageAttribute();
        if ($nextStage) {
            $session->update([
                'current_stage' => $nextStage,
                'stage_status' => 'pending',
                'stage_started_at' => now(),
                'overall_progress' => $session->calculateActualProgress(),
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

        // Create or update offering assessment
        $offering = $session->offering;
        if (!$offering) {
            $offering = RecruitmentOffering::create([
                'session_id' => $session->id,
                'offering_letter_number' => $assessmentData['offering_letter_number'] ?? null,
                'result' => $status,
                'notes' => $assessmentData['notes'] ?? null,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => $assessmentData['reviewed_at'] ?? now(),
            ]);
        } else {
            $offering->update([
                'offering_letter_number' => $assessmentData['offering_letter_number'] ?? null,
                'result' => $status,
                'notes' => $assessmentData['notes'] ?? null,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => $assessmentData['reviewed_at'] ?? now(),
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
            'overall_progress' => $session->calculateActualProgress(),
        ]);

        // Automatically advance to next stage (mcu)
        $nextStage = $session->getNextStageAttribute();
        if ($nextStage) {
            $session->update([
                'current_stage' => $nextStage,
                'stage_status' => 'pending',
                'stage_started_at' => now(),
                'overall_progress' => $session->calculateActualProgress(),
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

        // Get agreement type from FPTK or MPP Detail
        $agreementType = null;
        if ($session->fptk_id && $session->fptk) {
            // For FPTK: get from employment_type
            $agreementType = \App\Models\RecruitmentHiring::getAgreementTypeFromEmploymentType($session->fptk->employment_type);
        } elseif ($session->mpp_detail_id && $session->mppDetail) {
            // For MPP: get from agreement_type in MPP Detail
            $agreementType = $session->mppDetail->agreement_type ?? 'pkwt';
        }

        // Fallback to pkwt if still null
        if (!$agreementType) {
            $agreementType = 'pkwt';
        }

        // Create or update hire assessment
        $hiring = $session->hiring;
        if (!$hiring) {
            $hiring = RecruitmentHiring::create([
                'session_id' => $session->id,
                'agreement_type' => $agreementType, // Auto-set from FPTK employment_type
                'letter_number' => $assessmentData['letter_number'] ?? null,
                'notes' => $assessmentData['notes'] ?? null,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => $assessmentData['reviewed_at'] ?? now(),
            ]);
        } else {
            $hiring->update([
                'agreement_type' => $agreementType, // Auto-set from FPTK employment_type
                'letter_number' => $assessmentData['letter_number'] ?? null,
                'notes' => $assessmentData['notes'] ?? null,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => $assessmentData['reviewed_at'] ?? now(),
            ]);
        }

        // Mark stage as completed and advance to next stage
        $session->update([
            'stage_status' => 'completed',
            'stage_completed_at' => now(),
            'overall_progress' => $session->calculateActualProgress(),
        ]);

        // Mark stage as completed and complete the session
        $session->update([
            'status' => 'hired',
            'final_decision_date' => now(),
            'final_decision_by' => auth()->id(),
            'final_decision_notes' => 'Hire completed successfully',
            'overall_progress' => 100.0,
        ]);

        return [
            'success' => true,
            'message' => 'Hire assessment completed successfully. Candidate hired and recruitment session completed.',
            'session_completed' => true
        ];

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
                'current_stage' => 'hire',
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
        $session = RecruitmentSession::with([
            'cvReview',
            'psikotes',
            'tesTeori',
            'interviews',
            'offering',
            'mcu',
            'hiring',

        ])->find($sessionId);

        if (!$session) {
            return [];
        }

        $timeline = [];
        $stageDurations = $session->stage_durations ?? [];

        // Get stages based on employment type
        $stages = $session->shouldSkipStagesForEmploymentType()
            ? ['mcu', 'hire']
            : RecruitmentSession::STAGES;

        foreach ($stages as $stage) {
            $stageData = [
                'stage' => $stage,
                'name' => $this->getStageDisplayName($stage),
                'progress' => $session->getAdjustedStageProgress()[$stage] ?? 0,
                'target_hours' => RecruitmentSession::STAGE_DURATION_TARGETS[$stage],
                'status' => 'pending',
                'started_at' => null,
                'completed_at' => null,
                'duration_hours' => 0,
                'is_current' => $session->current_stage === $stage,
                'is_overdue' => false,
            ];

            // Check if stage is completed using new model relationships
            if ($session->isStageCompleted($stage)) {
                $stageData['status'] = 'completed';
                if (isset($stageDurations[$stage])) {
                    $stageData['started_at'] = $stageDurations[$stage]['started'];
                    $stageData['completed_at'] = $stageDurations[$stage]['completed'];
                    $stageData['duration_hours'] = $stageDurations[$stage]['duration_hours'];
                    $stageData['is_overdue'] = $stageDurations[$stage]['duration_hours'] > RecruitmentSession::STAGE_DURATION_TARGETS[$stage];
                }
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
     * Get progress percentage for session - Dynamic calculation
     * Formula: (completed_stages / total_valid_stages) * 100
     *
     * @param RecruitmentSession $session
     * @return float
     */
    public function getProgressPercentage(RecruitmentSession $session): float
    {
        // Get valid stages for this session type
        $validStages = $this->getValidStagesForSession($session);

        if (empty($validStages)) {
            return 0;
        }

        $totalStages = count($validStages);
        $completedStages = 0;

        // Count how many stages are actually completed (have valid assessments)
        foreach ($validStages as $stage) {
            if ($session->isStageCompleted($stage)) {
                $completedStages++;
            }
        }

        // Calculate progress as percentage of completed stages vs total stages
        if ($totalStages === 0) {
            return 0;
        }

        $progress = ($completedStages / $totalStages) * 100;

        return round($progress, 1); // Return with 1 decimal place for better precision
    }

    /**
     * Get valid stages for a session based on its type (FPTK/MMP and employment type)
     */
    public function getValidStagesForSession($session): array
    {
        // For magang and harian: only MCU and Hire stages
        if ($session->fptk_id && $session->fptk && $session->fptk->usesSimplifiedRecruitmentFlow()) {
            return ['mcu', 'hire'];
        }

        // Standard stages for regular employment types
        $stages = ['cv_review', 'psikotes', 'tes_teori', 'interview', 'offering', 'mcu', 'hire'];

        // Remove tes_teori if should be skipped
        if ($session->shouldSkipTheoryTest()) {
            $stages = array_diff($stages, ['tes_teori']);
        }

        return array_values($stages);
    }

    /**
     * Continue a candidate from a previous FPTK session onto a new approved FPTK.
     * Mode "copy" clones completed stage data that is valid on the target; "fresh" starts from the beginning.
     * Source session history is kept; in_process sources are cancelled with a continue note.
     *
     * @param  string  $mode  copy|fresh
     * @return array{session: RecruitmentSession, warning: string|null}
     *
     * @throws Exception
     */
    public function reuseOnFptk(
        RecruitmentCandidate $candidate,
        RecruitmentRequest $targetFptk,
        RecruitmentSession $sourceSession,
        string $mode = 'fresh'
    ): array {
        $mode = in_array($mode, ['copy', 'fresh'], true) ? $mode : 'fresh';

        if (in_array($candidate->global_status, ['hired', 'blacklisted'], true)) {
            throw new Exception('Candidate cannot be reused due to status: '.$candidate->global_status);
        }

        if ($sourceSession->candidate_id !== $candidate->id) {
            throw new Exception('Source session does not belong to this candidate.');
        }

        if ($sourceSession->status === 'hired') {
            throw new Exception('Cannot reuse a hired session.');
        }

        if (! $targetFptk->canReceiveApplications()) {
            throw new Exception($targetFptk->isOnHold()
                ? 'FPTK sedang On Hold. Tidak dapat menerima aplikasi hingga Unhold.'
                : 'Target FPTK cannot receive applications.');
        }

        if ($sourceSession->fptk_id && $sourceSession->fptk_id === $targetFptk->id) {
            throw new Exception('Target FPTK must be different from the source FPTK.');
        }

        $existing = RecruitmentSession::where('fptk_id', $targetFptk->id)
            ->where('candidate_id', $candidate->id)
            ->exists();
        if ($existing) {
            throw new Exception('Candidate already has a session on the target FPTK.');
        }

        $warning = null;

        DB::beginTransaction();
        try {
            $isSimplified = $targetFptk->usesSimplifiedRecruitmentFlow();
            $fptkLabel = $targetFptk->request_number ?: $targetFptk->id;
            $continueNote = 'Forwarded to FPTK '.$fptkLabel;

            $session = RecruitmentSession::create([
                'session_number' => RecruitmentSession::generateSessionNumber(),
                'fptk_id' => $targetFptk->id,
                'candidate_id' => $candidate->id,
                'source_session_id' => $sourceSession->id,
                'applied_date' => now()->toDateString(),
                'source' => $sourceSession->source ?: 'website',
                'current_stage' => $isSimplified ? 'mcu' : 'cv_review',
                'stage_status' => 'pending',
                'stage_started_at' => now(),
                'overall_progress' => $isSimplified ? 0 : 14.3,
                'status' => 'in_process',
                'responsible_person_id' => $sourceSession->responsible_person_id,
                'created_by' => auth()->id(),
            ]);

            if ($mode === 'copy') {
                $warning = $this->copyProcessFromSession($sourceSession, $session);
                $session->refresh();
                $session->load(['fptk', 'cvReview', 'psikotes', 'tesTeori', 'interviews', 'offering', 'mcu', 'hiring']);
                $this->remapSessionWorkflowAfterCopy($session);
            }

            $this->closeSourceSessionAfterReuse($sourceSession, $continueNote);

            $candidate->update(['global_status' => 'in_process']);

            DB::commit();

            $session->load(['fptk', 'candidate', 'sourceSession.fptk']);

            Log::info('Recruitment session reused on new FPTK', [
                'session_id' => $session->id,
                'source_session_id' => $sourceSession->id,
                'mode' => $mode,
                'fptk_id' => $targetFptk->id,
            ]);

            return ['session' => $session, 'warning' => $warning];
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to reuse session on FPTK', [
                'error' => $e->getMessage(),
                'candidate_id' => $candidate->id,
                'fptk_id' => $targetFptk->id,
                'source_session_id' => $sourceSession->id,
            ]);
            throw $e;
        }
    }

    /**
     * Clone stage rows and documents from source onto target; only stages valid on target are kept.
     */
    protected function copyProcessFromSession(RecruitmentSession $source, RecruitmentSession $target): ?string
    {
        $source->loadMissing([
            'fptk', 'cvReview', 'psikotes', 'tesTeori', 'interviews', 'offering', 'mcu', 'hiring', 'documents',
        ]);
        $target->loadMissing('fptk');

        $validTargetStages = $this->getValidStagesForSession($target);
        $copied = [];
        $skipped = [];

        $stageCopyMap = [
            'cv_review' => fn () => $source->cvReview,
            'psikotes' => fn () => $source->psikotes,
            'tes_teori' => fn () => $source->tesTeori,
            'offering' => fn () => $source->offering,
            'mcu' => fn () => $source->mcu,
        ];

        foreach (['cv_review', 'psikotes', 'tes_teori', 'interview', 'offering', 'mcu', 'hire'] as $stage) {
            if (! in_array($stage, $validTargetStages, true)) {
                if ($this->sourceHasStageData($source, $stage)) {
                    $skipped[] = $stage;
                }
                continue;
            }

            if ($stage === 'interview') {
                if ($source->interviews->isEmpty()) {
                    continue;
                }
                foreach ($source->interviews as $interview) {
                    if ($target->shouldSkipTheoryTest() && $interview->type === 'trainer') {
                        $skipped[] = 'interview_trainer';
                        continue;
                    }
                    RecruitmentInterview::create([
                        'session_id' => $target->id,
                        'type' => $interview->type,
                        'result' => $interview->result,
                        'notes' => $interview->notes,
                        'reviewed_by' => $interview->reviewed_by,
                        'reviewed_at' => $interview->reviewed_at,
                    ]);
                }
                $copied[] = 'interview';
                continue;
            }

            if ($stage === 'hire') {
                // Never copy employee registration linkage; leave hire pending on target.
                if ($source->hiring) {
                    $skipped[] = 'hire';
                }
                continue;
            }

            $row = $stageCopyMap[$stage]() ?? null;
            if (! $row) {
                continue;
            }

            $attrs = collect($row->getAttributes())
                ->except(['id', 'session_id', 'created_at', 'updated_at'])
                ->all();
            $attrs['session_id'] = $target->id;

            match ($stage) {
                'cv_review' => RecruitmentCvReview::create($attrs),
                'psikotes' => RecruitmentPsikotes::create($attrs),
                'tes_teori' => RecruitmentTesTeori::create($attrs),
                'offering' => RecruitmentOffering::create($attrs),
                'mcu' => RecruitmentMcu::create($attrs),
                default => null,
            };
            $copied[] = $stage;
        }

        $this->cloneSessionDocuments($source, $target);

        if (empty($copied) && ! empty($skipped)) {
            return 'No overlapping stages to copy for the target FPTK flow; session started fresh with source link only.';
        }

        if (! empty($skipped)) {
            return 'Some stages were skipped (not valid on target FPTK): '.implode(', ', array_unique($skipped)).'.';
        }

        return null;
    }

    protected function sourceHasStageData(RecruitmentSession $source, string $stage): bool
    {
        return match ($stage) {
            'cv_review' => (bool) $source->cvReview,
            'psikotes' => (bool) $source->psikotes,
            'tes_teori' => (bool) $source->tesTeori,
            'interview' => $source->interviews->isNotEmpty(),
            'offering' => (bool) $source->offering,
            'mcu' => (bool) $source->mcu,
            'hire' => (bool) $source->hiring,
            default => false,
        };
    }

    protected function cloneSessionDocuments(RecruitmentSession $source, RecruitmentSession $target): void
    {
        foreach ($source->documents as $doc) {
            $newPath = $doc->file_path;
            if ($doc->file_path && Storage::disk('local')->exists($doc->file_path)) {
                $ext = pathinfo($doc->file_path, PATHINFO_EXTENSION);
                $newStored = Str::uuid()->toString().($ext ? '.'.$ext : '');
                $dir = trim(dirname($doc->file_path), '.');
                $newPath = ($dir && $dir !== '' ? $dir.'/' : 'recruitment/documents/').$newStored;
                Storage::disk('local')->copy($doc->file_path, $newPath);
            }

            RecruitmentDocument::create([
                'session_id' => $target->id,
                'document_type' => $doc->document_type,
                'original_filename' => $doc->original_filename,
                'stored_filename' => basename($newPath),
                'file_path' => $newPath,
                'file_size' => $doc->file_size,
                'mime_type' => $doc->mime_type,
                'uploaded_by' => $doc->uploaded_by,
                'is_verified' => $doc->is_verified,
                'stage_type' => $doc->stage_type ?? null,
            ]);
        }
    }

    /**
     * After copying assessments, set current_stage / stage_status / overall_progress from target flow rules.
     */
    protected function remapSessionWorkflowAfterCopy(RecruitmentSession $session): void
    {
        $session->refresh();
        $session->load(['fptk', 'cvReview', 'psikotes', 'tesTeori', 'interviews', 'offering', 'mcu', 'hiring', 'sourceSession']);

        $stages = $this->getValidStagesForSession($session);
        $nextPending = $stages[0] ?? ($session->shouldSkipStagesForEmploymentType() ? 'mcu' : 'cv_review');

        foreach ($stages as $stage) {
            if ($session->isStageCompleted($stage)) {
                continue;
            }
            $nextPending = $stage;
            break;
        }

        $progress = $session->calculateActualProgress();
        $durations = $this->filterStageDurationsForTarget($session, $stages);

        $session->update([
            'current_stage' => $nextPending,
            'stage_status' => 'pending',
            'stage_started_at' => now(),
            'stage_completed_at' => null,
            'status' => 'in_process',
            'overall_progress' => $progress,
            'stage_durations' => $durations,
        ]);
    }

    protected function filterStageDurationsForTarget(RecruitmentSession $session, array $validStages): ?array
    {
        $source = $session->relationLoaded('sourceSession')
            ? $session->sourceSession
            : RecruitmentSession::find($session->source_session_id);
        $sourceDurations = $source?->stage_durations ?? [];

        if (! is_array($sourceDurations) || empty($sourceDurations)) {
            return null;
        }

        $filtered = array_intersect_key($sourceDurations, array_flip($validStages));

        return $filtered ?: null;
    }

    protected function closeSourceSessionAfterReuse(RecruitmentSession $source, string $continueNote): void
    {
        // Always mark source as Forwarded (not cancelled) so history stays distinct from FPTK-close cancels.
        if (in_array($source->status, ['in_process', 'cancelled', 'rejected', 'withdrawn'], true)) {
            $existing = trim((string) $source->final_decision_notes);
            $notes = $existing === '' || $source->status === 'in_process'
                ? $continueNote
                : (str_contains($existing, $continueNote) ? $existing : $existing.' | '.$continueNote);

            $source->forward($notes);

            return;
        }

        $existing = trim((string) $source->final_decision_notes);
        $notes = $existing === ''
            ? $continueNote
            : (str_contains($existing, $continueNote) ? $existing : $existing.' | '.$continueNote);

        $source->update([
            'status' => 'forwarded',
            'final_decision_notes' => $notes,
            'final_decision_date' => $source->final_decision_date ?: now(),
        ]);
    }

    /**
     * Create initial CV Review assessment
     *
     * @param RecruitmentSession $session
     * @return void
     */
    protected function createInitialAssessment(RecruitmentSession $session): void
    {
        // No need to create initial assessment for new structure
        // Assessments will be created when each stage is processed
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
        // No need to create assessment for new structure
        // Assessments will be created when each stage is processed
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
            'interview' => 'Interview',
            'offering' => 'Offering',
            'mcu' => 'MCU',
            'hire' => 'Hire',
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
        // This method should use the model's getNextStageAttribute() instead
        // For backward compatibility, we'll keep this but it should be deprecated
        $stageOrder = [
            'cv_review' => 'psikotes',
            'psikotes' => 'tes_teori',
            'tes_teori' => 'interview', // Changed to unified interview stage
            'interview' => 'offering',
            'offering' => 'mcu',
            'mcu' => 'hire',
            'hire' => null // Final stage
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
        // Normalize to model-level single source of truth
        // This method should not be used anymore - use getAdjustedStageProgress() instead
        return 0.0;
    }
}
