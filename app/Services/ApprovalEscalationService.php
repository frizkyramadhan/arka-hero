<?php

namespace App\Services;

use App\Models\ApprovalAction;
use App\Models\DocumentApproval;
use App\Models\ApprovalStage;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ApprovalEscalationService
{
    /**
     * Check and escalate overdue approvals
     */
    public function checkAndEscalateOverdueApprovals(): array
    {
        $escalatedCount = 0;
        $escalationErrors = [];

        // Get all pending approvals
        $pendingApprovals = DocumentApproval::where('overall_status', 'pending')
            ->with(['approvalFlow.stages', 'currentStage'])
            ->get();

        foreach ($pendingApprovals as $approval) {
            if ($this->shouldEscalate($approval)) {
                try {
                    if ($this->escalateApproval($approval)) {
                        $escalatedCount++;
                    }
                } catch (\Exception $e) {
                    $escalationErrors[] = [
                        'approval_id' => $approval->id,
                        'error' => $e->getMessage()
                    ];
                }
            }
        }

        return [
            'escalated_count' => $escalatedCount,
            'errors' => $escalationErrors
        ];
    }

    /**
     * Check if approval should be escalated
     */
    public function shouldEscalate(DocumentApproval $approval): bool
    {
        if (!$approval->currentStage) {
            return false;
        }

        $escalationHours = $approval->currentStage->escalation_hours ?? 72;
        $lastAction = $approval->actions()->latest('action_date')->first();

        if (!$lastAction) {
            // No actions yet, check from submission time
            return $approval->submitted_at->diffInHours(now()) > $escalationHours;
        }

        // Check from last action time
        return $lastAction->action_date->diffInHours(now()) > $escalationHours;
    }

    /**
     * Escalate approval to next level or backup approvers
     */
    public function escalateApproval(DocumentApproval $approval): bool
    {
        try {
            DB::beginTransaction();

            // Create escalation action
            $escalationAction = ApprovalAction::create([
                'document_approval_id' => $approval->id,
                'approval_stage_id' => $approval->current_stage_id,
                'approver_id' => auth()->id() ?? 1, // System or admin user
                'action' => 'escalated',
                'comments' => 'Automatic escalation due to overdue approval',
                'action_date' => now(),
                'is_automatic' => true,
                'metadata' => [
                    'escalation_reason' => 'overdue',
                    'escalation_hours' => $approval->currentStage->escalation_hours ?? 72,
                    'escalated_at' => now()->toISOString()
                ]
            ]);

            // Get backup approvers for current stage
            $backupApprovers = $approval->currentStage->approvers()
                ->where('is_backup', true)
                ->get();

            if ($backupApprovers->count() > 0) {
                // Notify backup approvers
                foreach ($backupApprovers as $backupApprover) {
                    $this->notifyEscalation($approval, $backupApprover);
                }
            } else {
                // No backup approvers, escalate to next stage if available
                $nextStage = $this->getNextStage($approval);
                if ($nextStage) {
                    $approval->update(['current_stage_id' => $nextStage->id]);
                    $this->notifyNextStageApprovers($approval, $nextStage);
                }
            }

            // Update approval metadata
            $metadata = $approval->metadata ?? [];
            $metadata['escalated'] = true;
            $metadata['escalated_at'] = now()->toISOString();
            $metadata['escalation_count'] = ($metadata['escalation_count'] ?? 0) + 1;

            $approval->update(['metadata' => $metadata]);

            DB::commit();

            Log::info('Approval escalated successfully', [
                'approval_id' => $approval->id,
                'stage_id' => $approval->current_stage_id,
                'escalated_at' => now()
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to escalate approval', [
                'approval_id' => $approval->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get next stage for escalation
     */
    private function getNextStage(DocumentApproval $approval): ?ApprovalStage
    {
        if (!$approval->currentStage) {
            return null;
        }

        return $approval->approvalFlow->stages()
            ->where('stage_order', '>', $approval->currentStage->stage_order)
            ->orderBy('stage_order', 'asc')
            ->first();
    }

    /**
     * Notify escalation to backup approvers
     */
    private function notifyEscalation(DocumentApproval $approval, $backupApprover): void
    {
        // Create notification for backup approver
        \App\Models\ApprovalNotification::create([
            'document_approval_id' => $approval->id,
            'recipient_id' => $backupApprover->approver_id,
            'notification_type' => 'escalation',
            'sent_at' => now()
        ]);

        Log::info('Escalation notification sent', [
            'approval_id' => $approval->id,
            'backup_approver_id' => $backupApprover->approver_id
        ]);
    }

    /**
     * Notify next stage approvers
     */
    private function notifyNextStageApprovers(DocumentApproval $approval, ApprovalStage $nextStage): void
    {
        $approvers = $nextStage->approvers;

        foreach ($approvers as $approver) {
            \App\Models\ApprovalNotification::create([
                'document_approval_id' => $approval->id,
                'recipient_id' => $approver->approver_id,
                'notification_type' => 'escalation_next_stage',
                'sent_at' => now()
            ]);
        }

        Log::info('Next stage escalation notifications sent', [
            'approval_id' => $approval->id,
            'next_stage_id' => $nextStage->id,
            'approvers_count' => $approvers->count()
        ]);
    }

    /**
     * Get escalation history for an approval
     */
    public function getEscalationHistory(DocumentApproval $approval): array
    {
        return ApprovalAction::where('document_approval_id', $approval->id)
            ->where('action', 'escalated')
            ->with(['approver', 'approvalStage'])
            ->orderBy('action_date', 'desc')
            ->get()
            ->map(function ($action) {
                return [
                    'id' => $action->id,
                    'escalated_by' => $action->approver->name,
                    'stage_name' => $action->approvalStage->stage_name,
                    'escalated_at' => $action->action_date,
                    'reason' => $action->comments,
                    'is_automatic' => $action->is_automatic,
                    'metadata' => $action->metadata
                ];
            })
            ->toArray();
    }

    /**
     * Get escalation statistics
     */
    public function getEscalationStats(): array
    {
        $totalEscalations = ApprovalAction::where('action', 'escalated')->count();
        $automaticEscalations = ApprovalAction::where('action', 'escalated')
            ->where('is_automatic', true)
            ->count();
        $manualEscalations = $totalEscalations - $automaticEscalations;

        // Get escalation by stage
        $escalationsByStage = ApprovalAction::where('action', 'escalated')
            ->with('approvalStage')
            ->get()
            ->groupBy('approval_stage_id')
            ->map(function ($escalations, $stageId) {
                $stage = $escalations->first()->approvalStage;
                return [
                    'stage_name' => $stage->stage_name,
                    'count' => $escalations->count(),
                    'automatic' => $escalations->where('is_automatic', true)->count(),
                    'manual' => $escalations->where('is_automatic', false)->count()
                ];
            })
            ->values()
            ->toArray();

        return [
            'total_escalations' => $totalEscalations,
            'automatic_escalations' => $automaticEscalations,
            'manual_escalations' => $manualEscalations,
            'escalations_by_stage' => $escalationsByStage
        ];
    }

    /**
     * Manually escalate approval
     */
    public function manualEscalate(DocumentApproval $approval, int $escalatedBy, string $reason = null): bool
    {
        try {
            DB::beginTransaction();

            // Create manual escalation action
            $escalationAction = ApprovalAction::create([
                'document_approval_id' => $approval->id,
                'approval_stage_id' => $approval->current_stage_id,
                'approver_id' => $escalatedBy,
                'action' => 'escalated',
                'comments' => $reason ?? 'Manual escalation',
                'action_date' => now(),
                'is_automatic' => false,
                'metadata' => [
                    'escalation_reason' => 'manual',
                    'escalated_by' => $escalatedBy,
                    'escalated_at' => now()->toISOString()
                ]
            ]);

            // Get backup approvers and notify them
            $backupApprovers = $approval->currentStage->approvers()
                ->where('is_backup', true)
                ->get();

            foreach ($backupApprovers as $backupApprover) {
                $this->notifyEscalation($approval, $backupApprover);
            }

            DB::commit();

            Log::info('Manual escalation completed', [
                'approval_id' => $approval->id,
                'escalated_by' => $escalatedBy,
                'reason' => $reason
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Failed to manually escalate approval', [
                'approval_id' => $approval->id,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get overdue approvals that need escalation
     */
    public function getOverdueApprovals(): array
    {
        $pendingApprovals = DocumentApproval::where('overall_status', 'pending')
            ->with(['approvalFlow.stages', 'currentStage'])
            ->get();

        $overdueApprovals = [];

        foreach ($pendingApprovals as $approval) {
            if ($this->shouldEscalate($approval)) {
                $overdueApprovals[] = [
                    'id' => $approval->id,
                    'document_type' => $approval->document_type,
                    'document_id' => $approval->document_id,
                    'current_stage' => $approval->currentStage->stage_name ?? 'Unknown',
                    'submitted_at' => $approval->submitted_at,
                    'escalation_hours' => $approval->currentStage->escalation_hours ?? 72,
                    'overdue_hours' => $this->getOverdueHours($approval)
                ];
            }
        }

        return $overdueApprovals;
    }

    /**
     * Get overdue hours for an approval
     */
    private function getOverdueHours(DocumentApproval $approval): int
    {
        $lastAction = $approval->actions()->latest('action_date')->first();

        if (!$lastAction) {
            return $approval->submitted_at->diffInHours(now());
        }

        return $lastAction->action_date->diffInHours(now());
    }
}
