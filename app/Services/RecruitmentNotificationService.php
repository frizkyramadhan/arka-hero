<?php

namespace App\Services;

use App\Models\RecruitmentSession;
use App\Models\RecruitmentAssessment;
use App\Models\RecruitmentOffering;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use App\Notifications\RecruitmentNotification;

class RecruitmentNotificationService
{
    /**
     * Send session created notification
     *
     * @param RecruitmentSession $session
     * @return bool
     */
    public function sendSessionCreatedNotification(RecruitmentSession $session): bool
    {
        try {
            // Notify candidate
            $this->notifyCandidate($session, 'session_created', [
                'session_number' => $session->session_number,
                'fptk_title' => $session->fptk->position->name ?? 'N/A',
                'next_stage' => 'CV Review',
            ]);

            // Notify HR staff
            $this->notifyHRStaff($session, 'new_application', [
                'candidate_name' => $session->candidate->fullname,
                'position' => $session->fptk->position->name ?? 'N/A',
                'session_number' => $session->session_number,
            ]);

            // Notify responsible person if assigned
            if ($session->responsible_person_id) {
                $this->notifyUser($session->responsible_person_id, 'session_assigned', [
                    'candidate_name' => $session->candidate->fullname,
                    'session_number' => $session->session_number,
                ]);
            }

            Log::info("Session created notifications sent", [
                'session_id' => $session->id
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send session created notification", [
                'error' => $e->getMessage(),
                'session_id' => $session->id
            ]);
            return false;
        }
    }

    /**
     * Send assessment scheduled notification
     *
     * @param RecruitmentAssessment $assessment
     * @return bool
     */
    public function sendAssessmentScheduledNotification(RecruitmentAssessment $assessment): bool
    {
        try {
            $session = $assessment->session;

            // Notify candidate
            $this->notifyCandidate($session, 'assessment_scheduled', [
                'assessment_type' => $this->getAssessmentTypeName($assessment->assessment_type),
                'scheduled_date' => $assessment->scheduled_date,
                'scheduled_time' => $assessment->scheduled_time,
                'location' => $assessment->location,
                'meeting_link' => $assessment->meeting_link,
            ]);

            // Notify assessors
            $assessorIds = $assessment->assessor_ids ?? [];
            foreach ($assessorIds as $assessorId) {
                $this->notifyUser($assessorId, 'assessment_assigned', [
                    'candidate_name' => $session->candidate->fullname,
                    'assessment_type' => $this->getAssessmentTypeName($assessment->assessment_type),
                    'scheduled_date' => $assessment->scheduled_date,
                    'scheduled_time' => $assessment->scheduled_time,
                ]);
            }

            Log::info("Assessment scheduled notifications sent", [
                'assessment_id' => $assessment->id,
                'session_id' => $session->id
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send assessment scheduled notification", [
                'error' => $e->getMessage(),
                'assessment_id' => $assessment->id
            ]);
            return false;
        }
    }

    /**
     * Send offering notification
     *
     * @param RecruitmentOffering $offering
     * @return bool
     */
    public function sendOfferingNotification(RecruitmentOffering $offering): bool
    {
        try {
            $session = $offering->session;

            // Notify candidate
            $this->notifyCandidate($session, 'offering_sent', [
                'offering_number' => $offering->offering_letter_number,
                'position' => $session->fptk->position->name ?? 'N/A',
                'result' => $offering->result,
            ]);

            // Notify HR staff
            $this->notifyHRStaff($session, 'offering_sent', [
                'candidate_name' => $session->candidate->fullname,
                'offering_number' => $offering->offering_letter_number,
                'position' => $session->fptk->position->name ?? 'N/A',
                'result' => $offering->result,
            ]);

            Log::info("Offering notifications sent", [
                'offering_id' => $offering->id,
                'session_id' => $session->id
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send offering notification", [
                'error' => $e->getMessage(),
                'offering_id' => $offering->id
            ]);
            return false;
        }
    }

    /**
     * Send stage advancement notification
     *
     * @param RecruitmentSession $session
     * @param string $nextStage
     * @return bool
     */
    public function sendStageAdvancementNotification(RecruitmentSession $session, string $nextStage): bool
    {
        try {
            // Notify candidate
            $this->notifyCandidate($session, 'stage_advancement', [
                'previous_stage' => $this->getStageDisplayName($session->getOriginal('current_stage')),
                'next_stage' => $this->getStageDisplayName($nextStage),
                'progress' => $session->overall_progress,
            ]);

            // Notify responsible person for next stage
            if ($session->responsible_person_id) {
                $this->notifyUser($session->responsible_person_id, 'stage_ready', [
                    'candidate_name' => $session->candidate->fullname,
                    'stage' => $this->getStageDisplayName($nextStage),
                    'session_number' => $session->session_number,
                ]);
            }

            Log::info("Stage advancement notifications sent", [
                'session_id' => $session->id,
                'next_stage' => $nextStage
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send stage advancement notification", [
                'error' => $e->getMessage(),
                'session_id' => $session->id,
                'next_stage' => $nextStage
            ]);
            return false;
        }
    }

    /**
     * Send rejection notification
     *
     * @param RecruitmentSession $session
     * @param string $reason
     * @return bool
     */
    public function sendRejectionNotification(RecruitmentSession $session, string $reason): bool
    {
        try {
            // Notify candidate
            $this->notifyCandidate($session, 'application_rejected', [
                'position' => $session->fptk->position->name ?? 'N/A',
                'stage' => $this->getStageDisplayName($session->current_stage),
                'reason' => $reason,
            ]);

            // Notify HR staff
            $this->notifyHRStaff($session, 'candidate_rejected', [
                'candidate_name' => $session->candidate->fullname,
                'position' => $session->fptk->position->name ?? 'N/A',
                'stage' => $this->getStageDisplayName($session->current_stage),
                'reason' => $reason,
            ]);

            Log::info("Rejection notifications sent", [
                'session_id' => $session->id,
                'reason' => $reason
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send rejection notification", [
                'error' => $e->getMessage(),
                'session_id' => $session->id
            ]);
            return false;
        }
    }

    /**
     * Send hire notification
     *
     * @param RecruitmentSession $session
     * @return bool
     */
    public function sendHireNotification(RecruitmentSession $session): bool
    {
        try {
            // Notify candidate
            $this->notifyCandidate($session, 'congratulations_hired', [
                'position' => $session->fptk->position->name ?? 'N/A',
                'department' => $session->fptk->department->name ?? 'N/A',
                'start_date' => $session->offers()->latest()->first()->start_date ?? null,
            ]);

            // Notify HR staff
            $this->notifyHRStaff($session, 'candidate_hired', [
                'candidate_name' => $session->candidate->fullname,
                'position' => $session->fptk->position->name ?? 'N/A',
                'department' => $session->fptk->department->name ?? 'N/A',
            ]);

            // Notify department head
            $this->notifyDepartmentHead($session, 'new_hire', [
                'candidate_name' => $session->candidate->fullname,
                'position' => $session->fptk->position->name ?? 'N/A',
                'start_date' => $session->offers()->latest()->first()->start_date ?? null,
            ]);

            Log::info("Hire notifications sent", [
                'session_id' => $session->id,
                'candidate_name' => $session->candidate->fullname
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send hire notification", [
                'error' => $e->getMessage(),
                'session_id' => $session->id
            ]);
            return false;
        }
    }

    /**
     * Send reminder notifications
     *
     * @return bool
     */
    public function sendReminderNotifications(): bool
    {
        try {
            // Send overdue stage reminders
            $this->sendOverdueStageReminders();

            // Send upcoming assessment reminders
            $this->sendUpcomingAssessmentReminders();

            // Send expiring offer reminders
            $this->sendExpiringOfferingReminders();

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send reminder notifications", [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Notify candidate
     *
     * @param RecruitmentSession $session
     * @param string $type
     * @param array $data
     * @return bool
     */
    protected function notifyCandidate(RecruitmentSession $session, string $type, array $data): bool
    {
        try {
            $candidate = $session->candidate;

            // Send email notification
            $this->sendEmail($candidate->email, $type, array_merge($data, [
                'candidate_name' => $candidate->fullname,
                'session_number' => $session->session_number,
            ]));

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to notify candidate", [
                'error' => $e->getMessage(),
                'candidate_id' => $session->candidate_id,
                'type' => $type
            ]);
            return false;
        }
    }

    /**
     * Notify HR staff
     *
     * @param RecruitmentSession $session
     * @param string $type
     * @param array $data
     * @return bool
     */
    protected function notifyHRStaff(RecruitmentSession $session, string $type, array $data): bool
    {
        try {
            // Get HR staff users
            $hrUsers = User::role(['HR Manager', 'HR Staff'])->get();

            foreach ($hrUsers as $user) {
                $this->notifyUser($user->id, $type, $data);
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to notify HR staff", [
                'error' => $e->getMessage(),
                'session_id' => $session->id,
                'type' => $type
            ]);
            return false;
        }
    }

    /**
     * Notify department head
     *
     * @param RecruitmentSession $session
     * @param string $type
     * @param array $data
     * @return bool
     */
    protected function notifyDepartmentHead(RecruitmentSession $session, string $type, array $data): bool
    {
        try {
            // Get department head for the department
            $departmentId = $session->fptk->department_id;

            // TODO: Implement logic to find department head
            // For now, notify all department heads
            $departmentHeads = User::role('Department Head')->get();

            foreach ($departmentHeads as $user) {
                $this->notifyUser($user->id, $type, $data);
            }

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to notify department head", [
                'error' => $e->getMessage(),
                'session_id' => $session->id,
                'type' => $type
            ]);
            return false;
        }
    }

    /**
     * Notify specific user
     *
     * @param int $userId
     * @param string $type
     * @param array $data
     * @return bool
     */
    protected function notifyUser(int $userId, string $type, array $data): bool
    {
        try {
            $user = User::find($userId);
            if (!$user) {
                return false;
            }

            // Send email notification
            $this->sendEmail($user->email, $type, array_merge($data, [
                'user_name' => $user->name,
            ]));

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to notify user", [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'type' => $type
            ]);
            return false;
        }
    }

    /**
     * Send email notification
     *
     * @param string $email
     * @param string $type
     * @param array $data
     * @return bool
     */
    protected function sendEmail(string $email, string $type, array $data): bool
    {
        try {
            // TODO: Implement actual email sending using Mail facade
            // For now, just log the email
            Log::info("Email notification sent", [
                'email' => $email,
                'type' => $type,
                'data' => $data
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error("Failed to send email", [
                'error' => $e->getMessage(),
                'email' => $email,
                'type' => $type
            ]);
            return false;
        }
    }

    /**
     * Send overdue stage reminders
     *
     * @return void
     */
    protected function sendOverdueStageReminders(): void
    {
        $overdueSessions = RecruitmentSession::overdue()->get();

        foreach ($overdueSessions as $session) {
            $this->notifyUser($session->responsible_person_id, 'stage_overdue', [
                'candidate_name' => $session->candidate->fullname,
                'stage' => $this->getStageDisplayName($session->current_stage),
                'session_number' => $session->session_number,
                'overdue_hours' => $session->getCurrentStageDurationAttribute(),
            ]);
        }
    }

    /**
     * Send upcoming assessment reminders
     *
     * @return void
     */
    protected function sendUpcomingAssessmentReminders(): void
    {
        $upcomingAssessments = RecruitmentAssessment::upcoming()
            ->where('scheduled_date', '>=', now())
            ->where('scheduled_date', '<=', now()->addDay())
            ->get();

        foreach ($upcomingAssessments as $assessment) {
            $this->sendAssessmentScheduledNotification($assessment);
        }
    }

    /**
     * Send expiring offering reminders
     *
     * @return void
     */
    protected function sendExpiringOfferingReminders(): void
    {
        // For now, we don't have expiry dates for offerings
        // This method can be implemented later if needed
        Log::info("Expiring offering reminders not implemented yet");
    }

    /**
     * Get assessment type display name
     *
     * @param string $type
     * @return string
     */
    protected function getAssessmentTypeName(string $type): string
    {
        $names = [
            'cv_review' => 'CV Review',
            'psikotes' => 'Psikotes',
            'tes_teori' => 'Tes Teori',
            'interview_hr' => 'Interview HR',
            'interview_user' => 'Interview User',
            'mcu' => 'Medical Check Up',
        ];

        return $names[$type] ?? ucfirst(str_replace('_', ' ', $type));
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
