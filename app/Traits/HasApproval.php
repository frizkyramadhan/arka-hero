<?php

namespace App\Traits;

use App\Models\DocumentApproval;
use App\Models\ApprovalFlow;
use App\Services\ApprovalEngineService;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Log;

trait HasApproval
{
    /**
     * Get the approval relationship
     */
    public function approval(): HasOne
    {
        return $this->hasOne(DocumentApproval::class, 'document_id', 'id')
            ->where('document_type', $this->getApprovalDocumentType());
    }

    /**
     * Submit document for approval
     */
    public function submitForApproval(): bool
    {
        try {
            $approvalEngine = app(ApprovalEngineService::class);

            return $approvalEngine->submitForApproval(
                $this->getApprovalDocumentType(),
                $this->getApprovalDocumentId(),
                auth()->id() ?? $this->created_by
            );
        } catch (\Exception $e) {
            Log::error('Failed to submit document for approval', [
                'document_type' => $this->getApprovalDocumentType(),
                'document_id' => $this->getApprovalDocumentId(),
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Check if approval is pending
     */
    public function isApprovalPending(): bool
    {
        return $this->approval?->overall_status === 'pending';
    }

    /**
     * Check if document is approved
     */
    public function isApproved(): bool
    {
        return $this->approval?->overall_status === 'approved';
    }

    /**
     * Check if document is rejected
     */
    public function isRejected(): bool
    {
        return $this->approval?->overall_status === 'rejected';
    }

    /**
     * Check if document is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->approval?->overall_status === 'cancelled';
    }

    /**
     * Get approval status
     */
    public function getApprovalStatus(): string
    {
        return $this->approval?->overall_status ?? 'not_submitted';
    }

    /**
     * Get current approval stage
     */
    public function getCurrentApprovalStage()
    {
        return $this->approval?->currentStage;
    }

    /**
     * Get approval flow
     */
    public function getApprovalFlow()
    {
        if ($this->approval_flow_id) {
            return ApprovalFlow::find($this->approval_flow_id);
        }

        return ApprovalFlow::where('document_type', $this->getApprovalDocumentType())
            ->where('is_active', true)
            ->first();
    }

    /**
     * Assign approval flow to document
     */
    public function assignApprovalFlow($flowId): bool
    {
        try {
            $flow = ApprovalFlow::find($flowId);

            if (!$flow || $flow->document_type !== $this->getApprovalDocumentType()) {
                return false;
            }

            $this->update(['approval_flow_id' => $flowId]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to assign approval flow', [
                'document_id' => $this->id,
                'flow_id' => $flowId,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Get approval progress
     */
    public function getApprovalProgress(): array
    {
        if (!$this->approval) {
            return [
                'percentage' => 0,
                'current_stage' => null,
                'total_stages' => 0,
                'completed_stages' => 0
            ];
        }

        $flow = $this->approval->approvalFlow;
        $stages = $flow->stages ?? collect();
        $currentStage = $this->approval->currentStage;

        $totalStages = $stages->count();
        $completedStages = $stages->where('stage_order', '<', $currentStage?->stage_order ?? 0)->count();

        $percentage = $totalStages > 0 ? round(($completedStages / $totalStages) * 100) : 0;

        return [
            'percentage' => $percentage,
            'current_stage' => $currentStage,
            'total_stages' => $totalStages,
            'completed_stages' => $completedStages
        ];
    }

    /**
     * Get approval timeline
     */
    public function getApprovalTimeline(): array
    {
        if (!$this->approval) {
            return [];
        }

        $timeline = [];

        // Add submission
        $timeline[] = [
            'action' => 'submitted',
            'date' => $this->approval->submitted_at,
            'user' => $this->approval->submittedBy,
            'description' => 'Document submitted for approval'
        ];

        // Add approval actions
        foreach ($this->approval->actions as $action) {
            $timeline[] = [
                'action' => $action->action,
                'date' => $action->action_date,
                'user' => $action->approver,
                'description' => $this->getActionDescription($action),
                'comments' => $action->comments
            ];
        }

        // Add completion if approved/rejected
        if ($this->approval->completed_at) {
            $timeline[] = [
                'action' => $this->approval->overall_status,
                'date' => $this->approval->completed_at,
                'user' => null,
                'description' => 'Approval process completed'
            ];
        }

        return $timeline;
    }

    /**
     * Get action description
     */
    private function getActionDescription($action): string
    {
        switch ($action->action) {
            case 'approved':
                return 'Document approved';
            case 'rejected':
                return 'Document rejected';
            case 'forwarded':
                return 'Approval forwarded to ' . ($action->forwardedTo->name ?? 'another user');
            case 'delegated':
                return 'Approval delegated to ' . ($action->delegatedTo->name ?? 'another user');
            case 'request_info':
                return 'Additional information requested';
            case 'escalated':
                return 'Approval escalated';
            default:
                return ucfirst($action->action);
        }
    }

    /**
     * Check if user can approve this document
     */
    public function canUserApprove($userId): bool
    {
        if (!$this->approval || !$this->approval->currentStage) {
            return false;
        }

        $currentStage = $this->approval->currentStage;

        return $currentStage->approvers()
            ->where(function ($query) use ($userId) {
                $query->where('approver_type', 'user')->where('approver_id', $userId)
                    ->orWhere('approver_type', 'role')->whereIn('approver_id', function ($q) use ($userId) {
                        $q->select('role_id')->from('model_has_roles')->where('model_id', $userId);
                    })
                    ->orWhere('approver_type', 'department')->where('approver_id', function ($q) use ($userId) {
                        $q->select('department_id')->from('users')->where('id', $userId);
                    });
            })
            ->exists();
    }

    /**
     * Get next approvers
     */
    public function getNextApprovers(): array
    {
        if (!$this->approval || !$this->approval->currentStage) {
            return [];
        }

        $approvers = [];
        $currentStage = $this->approval->currentStage;

        foreach ($currentStage->approvers as $approver) {
            $approvers[] = [
                'id' => $approver->id,
                'type' => $approver->approver_type,
                'name' => $this->getApproverName($approver),
                'is_backup' => $approver->is_backup
            ];
        }

        return $approvers;
    }

    /**
     * Get approver name
     */
    private function getApproverName($approver): string
    {
        switch ($approver->approver_type) {
            case 'user':
                return $approver->user->name ?? 'Unknown User';
            case 'role':
                return $approver->role->name ?? 'Unknown Role';
            case 'department':
                return $approver->department->name ?? 'Unknown Department';
            default:
                return 'Unknown';
        }
    }

    /**
     * Cancel approval
     */
    public function cancelApproval($userId, $reason = null): bool
    {
        try {
            $approvalEngine = app(ApprovalEngineService::class);

            return $approvalEngine->cancelApproval($this->approval->id, $userId);
        } catch (\Exception $e) {
            Log::error('Failed to cancel approval', [
                'document_id' => $this->id,
                'user_id' => $userId,
                'error' => $e->getMessage()
            ]);

            return false;
        }
    }

    /**
     * Check if approval is overdue
     */
    public function isApprovalOverdue(): bool
    {
        if (!$this->approval || !$this->approval->currentStage) {
            return false;
        }

        $currentStage = $this->approval->currentStage;
        $escalationHours = $currentStage->escalation_hours ?? 72;

        // Check if current stage has been pending longer than escalation hours
        $lastAction = $this->approval->actions()->latest('action_date')->first();
        $startTime = $lastAction ? $lastAction->action_date : $this->approval->submitted_at;

        return $startTime->diffInHours(now()) > $escalationHours;
    }

    /**
     * Get approval statistics
     */
    public function getApprovalStatistics(): array
    {
        if (!$this->approval) {
            return [
                'total_actions' => 0,
                'approved_actions' => 0,
                'rejected_actions' => 0,
                'forwarded_actions' => 0,
                'days_pending' => 0,
                'avg_response_time' => 0,
                'approval_rate' => 0
            ];
        }

        $actions = $this->approval->actions;
        $totalActions = $actions->count();
        $approvedActions = $actions->where('action', 'approved')->count();
        $rejectedActions = $actions->where('action', 'rejected')->count();
        $forwardedActions = $actions->where('action', 'forwarded')->count();

        $daysPending = $this->approval->submitted_at->diffInDays(now());
        $avgResponseTime = $actions->avg('response_time_hours') ?? 0;

        return [
            'total_actions' => $totalActions,
            'approved_actions' => $approvedActions,
            'rejected_actions' => $rejectedActions,
            'forwarded_actions' => $forwardedActions,
            'days_pending' => $daysPending,
            'avg_response_time' => round($avgResponseTime, 2),
            'approval_rate' => $totalActions > 0 ? round(($approvedActions / $totalActions) * 100, 2) : 0
        ];
    }
}
