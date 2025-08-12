<?php

namespace App\Services;

use App\Models\RecruitmentSession;
use App\Models\RecruitmentAssessment;
use App\Models\RecruitmentOffering;
use Illuminate\Support\Facades\Log;

class RecruitmentWorkflowService
{
    /**
     * Process stage completion
     *
     * @param string $sessionId
     * @param array $results
     * @return bool
     */
    public function processStageCompletion(string $sessionId, array $results): bool
    {
        $session = RecruitmentSession::find($sessionId);
        if (!$session) {
            return false;
        }

        try {
            // Update assessment results
            $assessment = $session->getCurrentStageAssessment();
            if ($assessment) {
                $assessment->update([
                    'status' => 'completed',
                    'overall_score' => $results['score'] ?? null,
                    'assessment_data' => $results['data'] ?? null,
                    'assessor_notes' => $results['notes'] ?? null,
                    'recommendation' => $results['recommendation'] ?? null,
                    'completed_at' => now(),
                ]);
            }

            // Update session stage status
            $session->update(['stage_status' => 'completed']);

            // Check if stage passed
            if ($this->isStagePassedByScore($assessment, $results)) {
                Log::info("Stage completed successfully", [
                    'session_id' => $sessionId,
                    'stage' => $session->current_stage,
                    'score' => $results['score'] ?? null
                ]);
                return true;
            } else {
                Log::warning("Stage failed - score below passing threshold", [
                    'session_id' => $sessionId,
                    'stage' => $session->current_stage,
                    'score' => $results['score'] ?? null
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error("Failed to process stage completion", [
                'error' => $e->getMessage(),
                'session_id' => $sessionId
            ]);
            return false;
        }
    }

    /**
     * Schedule next assessment
     *
     * @param string $sessionId
     * @param string $type
     * @param array $data
     * @return bool
     */
    public function scheduleNextAssessment(string $sessionId, string $type, array $data = []): bool
    {
        $session = RecruitmentSession::find($sessionId);
        if (!$session) {
            return false;
        }

        try {
            $assessment = $session->getAssessment($type);
            if (!$assessment) {
                return false;
            }

            $assessment->update([
                'scheduled_date' => $data['scheduled_date'] ?? null,
                'scheduled_time' => $data['scheduled_time'] ?? null,
                'location' => $data['location'] ?? null,
                'meeting_link' => $data['meeting_link'] ?? null,
                'assessor_ids' => $data['assessor_ids'] ?? null,
                'duration_minutes' => $data['duration_minutes'] ?? null,
                'status' => 'scheduled',
            ]);

            Log::info("Assessment scheduled", [
                'session_id' => $sessionId,
                'assessment_type' => $type,
                'scheduled_date' => $data['scheduled_date'] ?? null
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to schedule assessment", [
                'error' => $e->getMessage(),
                'session_id' => $sessionId,
                'assessment_type' => $type
            ]);
            return false;
        }
    }

    /**
     * Handle stage failure
     *
     * @param string $sessionId
     * @param string $reason
     * @return bool
     */
    public function handleStageFailure(string $sessionId, string $reason): bool
    {
        $session = RecruitmentSession::find($sessionId);
        if (!$session) {
            return false;
        }

        try {
            // Update current stage assessment
            $assessment = $session->getCurrentStageAssessment();
            if ($assessment) {
                $assessment->update([
                    'status' => 'failed',
                    'assessor_notes' => $reason,
                    'completed_at' => now(),
                ]);
            }

            // Update session status
            $session->update([
                'stage_status' => 'failed',
                'status' => 'rejected',
                'final_decision_date' => now(),
                'final_decision_notes' => $reason,
            ]);

            Log::info("Stage failure handled", [
                'session_id' => $sessionId,
                'stage' => $session->current_stage,
                'reason' => $reason
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to handle stage failure", [
                'error' => $e->getMessage(),
                'session_id' => $sessionId
            ]);
            return false;
        }
    }

    /**
     * Validate stage transition
     *
     * @param string $currentStage
     * @param string $nextStage
     * @return bool
     */
    public function validateStageTransition(string $currentStage, string $nextStage): bool
    {
        $stages = RecruitmentSession::STAGES;
        $currentIndex = array_search($currentStage, $stages);
        $nextIndex = array_search($nextStage, $stages);

        if ($currentIndex === false || $nextIndex === false) {
            return false;
        }

        // Next stage must be immediately after current stage
        return $nextIndex === $currentIndex + 1;
    }

    /**
     * Check prerequisites for stage
     *
     * @param string $sessionId
     * @param string $stage
     * @return bool
     */
    public function checkPrerequisites(string $sessionId, string $stage): bool
    {
        $session = RecruitmentSession::find($sessionId);
        if (!$session) {
            return false;
        }

        $prerequisites = $this->getStagePrerequisites($stage);

        foreach ($prerequisites as $prerequisite) {
            if (!$this->isPrerequisiteMet($session, $prerequisite)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Handle stage advancement
     *
     * @param RecruitmentSession $session
     * @param string $nextStage
     * @param array $data
     * @return bool
     */
    public function handleStageAdvancement(RecruitmentSession $session, string $nextStage, array $data = []): bool
    {
        try {
            // Handle stage-specific logic
            switch ($nextStage) {
                case 'offering':
                    return $this->handleOfferingStage($session, $data);

                case 'mcu':
                    return $this->handleMCUStage($session, $data);

                case 'hire':
                    return $this->handleHireStage($session, $data);

                case 'onboarding':
                    return $this->handleOnboardingStage($session, $data);

                default:
                    return true;
            }
        } catch (\Exception $e) {
            Log::error("Failed to handle stage advancement", [
                'error' => $e->getMessage(),
                'session_id' => $session->id,
                'next_stage' => $nextStage
            ]);
            return false;
        }
    }

    /**
     * Check if stage is passed by score
     *
     * @param RecruitmentAssessment|null $assessment
     * @param array $results
     * @return bool
     */
    protected function isStagePassedByScore($assessment, array $results): bool
    {
        if (!$assessment || !isset($results['score'])) {
            return true; // No score requirement
        }

        $score = $results['score'];
        $passingScore = $assessment->passing_score;

        return $score >= $passingScore;
    }

    /**
     * Get stage prerequisites
     *
     * @param string $stage
     * @return array
     */
    protected function getStagePrerequisites(string $stage): array
    {
        $prerequisites = [
            'cv_review' => [],
            'psikotes' => ['cv_review'],
            'tes_teori' => ['cv_review', 'psikotes'],
            'interview_hr' => ['cv_review', 'psikotes', 'tes_teori'],
            'interview_user' => ['cv_review', 'psikotes', 'tes_teori', 'interview_hr'],
            'offering' => ['cv_review', 'psikotes', 'tes_teori', 'interview_hr', 'interview_user'],
            'mcu' => ['offering_accepted'],
            'hire' => ['mcu'],
            'onboarding' => ['hire'],
        ];

        return $prerequisites[$stage] ?? [];
    }

    /**
     * Check if prerequisite is met
     *
     * @param RecruitmentSession $session
     * @param string $prerequisite
     * @return bool
     */
    protected function isPrerequisiteMet(RecruitmentSession $session, string $prerequisite): bool
    {
        if ($prerequisite === 'offering_accepted') {
            $offer = $session->getLatestOffer();
            return $offer && $offer->status === 'accepted';
        }

        // Check if assessment stage is completed
        $assessment = $session->getAssessment($prerequisite);
        return $assessment && $assessment->status === 'completed';
    }

    /**
     * Handle offering stage
     *
     * @param RecruitmentSession $session
     * @param array $data
     * @return bool
     */
    protected function handleOfferingStage(RecruitmentSession $session, array $data): bool
    {
        // Check if offering already exists
        $existingOffering = $session->offering;
        if (!$existingOffering) {
            // Create default offering record - will be updated later
            \App\Models\RecruitmentOffering::create([
                'session_id' => $session->id,
                'offering_letter_number' => null, // Will be set when offering is processed
                'result' => 'pending',
                'notes' => null,
                'reviewed_by' => auth()->id(),
                'reviewed_at' => now(),
            ]);
        }

        return true;
    }

    /**
     * Handle MCU stage
     *
     * @param RecruitmentSession $session
     * @param array $data
     * @return bool
     */
    protected function handleMCUStage(RecruitmentSession $session, array $data): bool
    {
        // MCU should only start after offering is accepted
        $offering = $session->offering;
        if (!$offering || $offering->result !== 'accepted') {
            Log::warning("MCU stage requires accepted offering", [
                'session_id' => $session->id,
                'offering_result' => $offering ? $offering->result : 'no_offering'
            ]);
            return false;
        }

        return true;
    }

    /**
     * Handle hire stage
     *
     * @param RecruitmentSession $session
     * @param array $data
     * @return bool
     */
    protected function handleHireStage(RecruitmentSession $session, array $data): bool
    {
        // Hire should only happen after MCU is completed
        $mcuAssessment = $session->getAssessment('mcu');
        if (!$mcuAssessment || $mcuAssessment->status !== 'completed') {
            Log::warning("Hire stage requires completed MCU", [
                'session_id' => $session->id,
                'mcu_status' => $mcuAssessment ? $mcuAssessment->status : 'no_mcu'
            ]);
            return false;
        }

        // Check if MCU result is fit for work
        $mcuResult = $mcuAssessment->assessment_data['overall_health'] ?? null;
        if ($mcuResult !== 'fit') {
            Log::warning("Hire stage requires fit MCU result", [
                'session_id' => $session->id,
                'mcu_result' => $mcuResult
            ]);
            return false;
        }

        return true;
    }

    /**
     * Handle onboarding stage
     *
     * @param RecruitmentSession $session
     * @param array $data
     * @return bool
     */
    protected function handleOnboardingStage(RecruitmentSession $session, array $data): bool
    {
        // Onboarding starts after hire is completed
        // Create onboarding tasks, assign buddy, etc.

        Log::info("Onboarding stage initiated", [
            'session_id' => $session->id,
            'candidate_id' => $session->candidate_id
        ]);

        return true;
    }

    /**
     * Get business rules for stage
     *
     * @param string $stage
     * @return array
     */
    public function getStageBusinessRules(string $stage): array
    {
        $rules = [
            'cv_review' => [
                'required_fields' => ['education_match', 'experience_match', 'skills_match'],
                'passing_score' => 70,
                'auto_advance' => true,
            ],
            'psikotes' => [
                'required_fields' => ['personality_score', 'iq_score', 'eq_score'],
                'passing_score' => 60,
                'auto_advance' => false,
            ],
            'tes_teori' => [
                'required_fields' => ['technical_score', 'general_score'],
                'passing_score' => 75,
                'auto_advance' => false,
            ],
            'interview_hr' => [
                'required_fields' => ['communication', 'attitude', 'cultural_fit'],
                'passing_score' => 70,
                'auto_advance' => false,
            ],
            'interview_user' => [
                'required_fields' => ['technical_skill', 'experience', 'problem_solving'],
                'passing_score' => 75,
                'auto_advance' => false,
            ],
            'offering' => [
                'required_fields' => ['basic_salary', 'start_date', 'offer_valid_until'],
                'auto_advance' => false,
            ],
            'mcu' => [
                'required_fields' => ['overall_health'],
                'passing_score' => 80,
                'auto_advance' => true,
            ],
        ];

        return $rules[$stage] ?? [];
    }
}
