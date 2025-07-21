<?php

namespace App\Services;

use App\Models\DocumentApproval;
use App\Models\ApprovalAction;
use App\Models\ApprovalFlow;
use App\Models\ApprovalStage;
use App\Models\ApprovalStageApprover;
use App\Models\User;
use App\Contracts\ApprovalInterface;
use App\Exceptions\Approval\ApprovalFlowNotFoundException;
use App\Exceptions\Approval\DocumentApprovalNotFoundException;
use App\Exceptions\Approval\ApprovalActionNotAllowedException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;

/**
 * Core approval engine service.
 *
 * This service handles all approval-related operations including submission,
 * processing, escalation, and cancellation of approvals.
 */
class ApprovalEngineService implements ApprovalInterface
{
    protected ApprovalFlowService $flowService;
    protected ApprovalNotificationService $notificationService;

    public function __construct(
        ApprovalFlowService $flowService,
        ApprovalNotificationService $notificationService
    ) {
        $this->flowService = $flowService;
        $this->notificationService = $notificationService;
    }

    /**
     * Submit a document for approval.
     *
     * @param string $documentType The type of document
     * @param string $documentId The document ID
     * @param int $submittedBy The user ID who submitted the document
     * @return DocumentApproval The created document approval instance
     * @throws \Exception If submission fails
     */
    public function submitForApproval(string $documentType, string $documentId, int $submittedBy): DocumentApproval
    {
        try {
            DB::beginTransaction();

            // Get the approval flow for this document type
            $flow = $this->flowService->getFlowByDocumentType($documentType);
            if (!$flow) {
                throw new ApprovalFlowNotFoundException($documentType, 'No active approval flow found for document type');
            }

            // Check if document is already submitted for approval
            $existingApproval = DocumentApproval::where('document_type', $documentType)
                ->where('document_id', $documentId)
                ->where('overall_status', 'pending')
                ->first();

            if ($existingApproval) {
                throw new \Exception('Document is already submitted for approval');
            }

            // Get the first stage of the flow
            $firstStage = $flow->getFirstStage();
            if (!$firstStage) {
                throw new \Exception('Approval flow has no stages');
            }

            // Create the document approval
            $documentApproval = DocumentApproval::create([
                'document_type' => $documentType,
                'document_id' => $documentId,
                'approval_flow_id' => $flow->id,
                'current_stage_id' => $firstStage->id,
                'overall_status' => 'pending',
                'submitted_by' => $submittedBy,
                'submitted_at' => now(),
                'metadata' => $this->getDocumentMetadata($documentType, $documentId),
            ]);

            // Send notifications to approvers
            $this->notificationService->notifyPendingApproval($documentApproval);

            DB::commit();

            Log::info('Document submitted for approval', [
                'document_type' => $documentType,
                'document_id' => $documentId,
                'approval_id' => $documentApproval->id,
                'submitted_by' => $submittedBy,
            ]);

            return $documentApproval;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to submit document for approval', [
                'document_type' => $documentType,
                'document_id' => $documentId,
                'submitted_by' => $submittedBy,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Process an approval action.
     *
     * @param int $documentApprovalId The document approval ID
     * @param int $approverId The user ID who performed the action
     * @param string $action The action type (approved, rejected, forwarded, delegated)
     * @param string|null $comments Optional comments
     * @param array $additionalData Additional data for the action
     * @return bool True if the action was processed successfully
     * @throws \Exception If processing fails
     */
    public function processApproval(int $documentApprovalId, int $approverId, string $action, ?string $comments = null, array $additionalData = []): bool
    {
        $documentApproval = DocumentApproval::with(['approvalFlow', 'currentStage'])->find($documentApprovalId);
        if (!$documentApproval) {
            throw new DocumentApprovalNotFoundException('document_approval', 'Document approval not found');
        }

        // Validate the action
        if (!$this->validateApprovalAction($documentApprovalId, $approverId, $action)) {
            throw new ApprovalActionNotAllowedException($action, $approverId, $documentApproval->document_type, $documentApproval->document_id);
        }

        try {
            DB::beginTransaction();

            // Create the approval action
            $approvalAction = ApprovalAction::create([
                'document_approval_id' => $documentApprovalId,
                'approval_stage_id' => $documentApproval->current_stage_id,
                'approver_id' => $approverId,
                'action' => $action,
                'comments' => $comments,
                'action_date' => now(),
                'forwarded_to' => $additionalData['forwarded_to'] ?? null,
                'delegated_to' => $additionalData['delegated_to'] ?? null,
                'is_automatic' => $additionalData['is_automatic'] ?? false,
                'metadata' => $additionalData,
            ]);

            // Process the action based on type
            switch ($action) {
                case 'approved':
                    $this->processApprovalAction($documentApproval, $approvalAction);
                    break;
                case 'rejected':
                    $this->processRejectionAction($documentApproval, $approvalAction);
                    break;
                case 'forwarded':
                    $this->processForwardAction($documentApproval, $approvalAction);
                    break;
                case 'delegated':
                    $this->processDelegationAction($documentApproval, $approvalAction);
                    break;
            }

            DB::commit();

            Log::info('Approval action processed', [
                'approval_id' => $documentApprovalId,
                'action' => $action,
                'approver_id' => $approverId,
                'stage_id' => $documentApproval->current_stage_id,
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to process approval action', [
                'approval_id' => $documentApprovalId,
                'action' => $action,
                'approver_id' => $approverId,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Get the next approvers for a document approval.
     *
     * @param int $documentApprovalId The document approval ID
     * @return Collection The collection of approvers
     */
    public function getNextApprovers(int $documentApprovalId): Collection
    {
        $documentApproval = DocumentApproval::with(['currentStage.approvers'])->find($documentApprovalId);
        if (!$documentApproval || !$documentApproval->currentStage) {
            return collect();
        }

        $approvers = collect();
        foreach ($documentApproval->currentStage->approvers as $stageApprover) {
            $approverUsers = $stageApprover->getApproverUsers();
            $approvers = $approvers->merge($approverUsers);
        }

        return $approvers->unique('id');
    }

    /**
     * Check if a user can approve a specific document.
     *
     * @param int $documentApprovalId The document approval ID
     * @param User $user The user to check
     * @return bool True if the user can approve
     */
    public function canUserApprove(int $documentApprovalId, User $user): bool
    {
        $documentApproval = DocumentApproval::with(['currentStage.approvers'])->find($documentApprovalId);
        if (!$documentApproval || !$documentApproval->currentStage) {
            return false;
        }

        foreach ($documentApproval->currentStage->approvers as $approver) {
            if ($approver->canUserApprove($user)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Escalate an approval that has been pending too long.
     *
     * @param int $documentApprovalId The document approval ID
     * @return bool True if escalation was successful
     */
    public function escalateApproval(int $documentApprovalId): bool
    {
        $documentApproval = DocumentApproval::with(['currentStage'])->find($documentApprovalId);
        if (!$documentApproval || !$documentApproval->currentStage) {
            return false;
        }

        try {
            DB::beginTransaction();

            // Check if escalation is needed
            $escalationHours = $documentApproval->currentStage->escalation_hours;
            $submittedAt = $documentApproval->submitted_at;
            $hoursSinceSubmission = now()->diffInHours($submittedAt);

            if ($hoursSinceSubmission < $escalationHours) {
                return false; // Not ready for escalation
            }

            // Get backup approvers
            $backupApprovers = $documentApproval->currentStage->backupApprovers;
            if ($backupApprovers->isEmpty()) {
                // No backup approvers, escalate to next level
                $nextStage = $documentApproval->getNextStage();
                if ($nextStage) {
                    $documentApproval->update(['current_stage_id' => $nextStage->id]);
                    $this->notificationService->notifyPendingApproval($documentApproval);
                }
            } else {
                // Notify backup approvers
                foreach ($backupApprovers as $backupApprover) {
                    $this->notificationService->sendEscalationNotification($documentApproval, $backupApprover);
                }
            }

            DB::commit();

            Log::info('Approval escalated', [
                'approval_id' => $documentApprovalId,
                'stage_id' => $documentApproval->current_stage_id,
                'hours_since_submission' => $hoursSinceSubmission,
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to escalate approval', [
                'approval_id' => $documentApprovalId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Cancel an approval process.
     *
     * @param int $documentApprovalId The document approval ID
     * @param int $cancelledBy The user ID who cancelled the approval
     * @return bool True if cancellation was successful
     */
    public function cancelApproval(int $documentApprovalId, int $cancelledBy): bool
    {
        $documentApproval = DocumentApproval::find($documentApprovalId);
        if (!$documentApproval) {
            throw new DocumentApprovalNotFoundException('document_approval', 'Document approval not found');
        }

        try {
            DB::beginTransaction();

            // Update the approval status
            $documentApproval->update([
                'overall_status' => 'cancelled',
                'completed_at' => now(),
            ]);

            // Create cancellation action
            ApprovalAction::create([
                'document_approval_id' => $documentApprovalId,
                'approval_stage_id' => $documentApproval->current_stage_id,
                'approver_id' => $cancelledBy,
                'action' => 'cancelled',
                'action_date' => now(),
                'is_automatic' => false,
            ]);

            // Notify relevant parties
            $this->notificationService->notifyApprovalCancelled($documentApproval);

            DB::commit();

            Log::info('Approval cancelled', [
                'approval_id' => $documentApprovalId,
                'cancelled_by' => $cancelledBy,
            ]);

            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to cancel approval', [
                'approval_id' => $documentApprovalId,
                'cancelled_by' => $cancelledBy,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Forward an approval to another user.
     *
     * @param int $documentApprovalId The document approval ID
     * @param int $approverId The current approver ID
     * @param int $forwardedTo The user ID to forward to
     * @param string|null $comments Optional comments
     * @return bool True if forwarding was successful
     */
    public function forwardApproval(int $documentApprovalId, int $approverId, int $forwardedTo, ?string $comments = null): bool
    {
        return $this->processApproval($documentApprovalId, $approverId, 'forwarded', $comments, [
            'forwarded_to' => $forwardedTo,
        ]);
    }

    /**
     * Delegate an approval to another user.
     *
     * @param int $documentApprovalId The document approval ID
     * @param int $approverId The current approver ID
     * @param int $delegatedTo The user ID to delegate to
     * @param string|null $comments Optional comments
     * @return bool True if delegation was successful
     */
    public function delegateApproval(int $documentApprovalId, int $approverId, int $delegatedTo, ?string $comments = null): bool
    {
        return $this->processApproval($documentApprovalId, $approverId, 'delegated', $comments, [
            'delegated_to' => $delegatedTo,
        ]);
    }

    /**
     * Get the approval history for a document.
     *
     * @param int $documentApprovalId The document approval ID
     * @return Collection The approval actions
     */
    public function getApprovalHistory(int $documentApprovalId): Collection
    {
        return ApprovalAction::where('document_approval_id', $documentApprovalId)
            ->with(['approver', 'approvalStage'])
            ->orderBy('action_date')
            ->get();
    }

    /**
     * Get pending approvals for a user.
     *
     * @param User $user The user
     * @param array $filters Optional filters
     * @return Collection The pending approvals
     */
    public function getPendingApprovalsForUser(User $user, array $filters = []): Collection
    {
        $query = DocumentApproval::with(['approvalFlow', 'currentStage', 'submittedBy'])
            ->pending();

        // Filter by document type
        if (isset($filters['document_type'])) {
            $query->forDocumentType($filters['document_type']);
        }

        // Filter by date range
        if (isset($filters['date_from'])) {
            $query->where('submitted_at', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('submitted_at', '<=', $filters['date_to']);
        }

        // Get approvals where user can approve
        $approvals = $query->get();
        return $approvals->filter(function ($approval) use ($user) {
            return $this->canUserApprove($approval->id, $user);
        });
    }

    /**
     * Get approval statistics for a user.
     *
     * @param User $user The user
     * @param array $filters Optional filters
     * @return array The statistics
     */
    public function getApprovalStatisticsForUser(User $user, array $filters = []): array
    {
        $query = ApprovalAction::where('approver_id', $user->id);

        // Apply filters
        if (isset($filters['date_from'])) {
            $query->where('action_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('action_date', '<=', $filters['date_to']);
        }

        $totalActions = $query->count();
        $approvedActions = $query->where('action', 'approved')->count();
        $rejectedActions = $query->where('action', 'rejected')->count();
        $forwardedActions = $query->where('action', 'forwarded')->count();
        $delegatedActions = $query->where('action', 'delegated')->count();

        return [
            'total_actions' => $totalActions,
            'approved_actions' => $approvedActions,
            'rejected_actions' => $rejectedActions,
            'forwarded_actions' => $forwardedActions,
            'delegated_actions' => $delegatedActions,
            'approval_rate' => $totalActions > 0 ? round(($approvedActions / $totalActions) * 100, 2) : 0,
        ];
    }

    /**
     * Check if an approval is overdue and needs escalation.
     *
     * @param int $documentApprovalId The document approval ID
     * @return bool True if the approval is overdue
     */
    public function isApprovalOverdue(int $documentApprovalId): bool
    {
        $documentApproval = DocumentApproval::with(['currentStage'])->find($documentApprovalId);
        if (!$documentApproval || !$documentApproval->currentStage) {
            return false;
        }

        $escalationHours = $documentApproval->currentStage->escalation_hours;
        $hoursSinceSubmission = now()->diffInHours($documentApproval->submitted_at);

        return $hoursSinceSubmission >= $escalationHours;
    }

    /**
     * Get overdue approvals.
     *
     * @return Collection The overdue approvals
     */
    public function getOverdueApprovals(): Collection
    {
        return DocumentApproval::with(['currentStage', 'approvalFlow'])
            ->pending()
            ->get()
            ->filter(function ($approval) {
                return $this->isApprovalOverdue($approval->id);
            });
    }

    /**
     * Process automatic approvals based on conditions.
     *
     * @param int $documentApprovalId The document approval ID
     * @return bool True if automatic approval was processed
     */
    public function processAutomaticApproval(int $documentApprovalId): bool
    {
        $documentApproval = DocumentApproval::with(['currentStage'])->find($documentApprovalId);
        if (!$documentApproval || !$documentApproval->currentStage) {
            return false;
        }

        // Check if auto-approval conditions are met
        if ($documentApproval->currentStage->shouldAutoApprove($documentApproval->metadata)) {
            return $this->processApproval($documentApprovalId, 1, 'approved', 'Automatic approval', [
                'is_automatic' => true,
            ]);
        }

        return false;
    }

    /**
     * Validate approval action.
     *
     * @param int $documentApprovalId The document approval ID
     * @param int $approverId The approver ID
     * @param string $action The action type
     * @return bool True if the action is valid
     */
    public function validateApprovalAction(int $documentApprovalId, int $approverId, string $action): bool
    {
        $documentApproval = DocumentApproval::with(['currentStage.approvers'])->find($documentApprovalId);
        if (!$documentApproval || !$documentApproval->currentStage) {
            return false;
        }

        // Check if approval is still pending
        if ($documentApproval->overall_status !== 'pending') {
            return false;
        }

        // Check if user can approve this stage
        $user = User::find($approverId);
        if (!$user) {
            return false;
        }

        foreach ($documentApproval->currentStage->approvers as $approver) {
            if ($approver->canUserApprove($user)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get approval flow for a document type.
     *
     * @param string $documentType The document type
     * @return ApprovalFlow|null The approval flow
     */
    public function getApprovalFlowForDocumentType(string $documentType)
    {
        return $this->flowService->getFlowByDocumentType($documentType);
    }

    /**
     * Create approval flow.
     *
     * @param array $flowData The flow data
     * @return ApprovalFlow The created flow
     */
    public function createApprovalFlow(array $flowData)
    {
        return $this->flowService->createFlow($flowData);
    }

    /**
     * Update approval flow.
     *
     * @param int $flowId The flow ID
     * @param array $flowData The flow data
     * @return ApprovalFlow The updated flow
     */
    public function updateApprovalFlow(int $flowId, array $flowData)
    {
        return $this->flowService->updateFlow($flowId, $flowData);
    }

    /**
     * Delete approval flow.
     *
     * @param int $flowId The flow ID
     * @return bool True if deletion was successful
     */
    public function deleteApprovalFlow(int $flowId): bool
    {
        return $this->flowService->deleteFlow($flowId);
    }

    /**
     * Process approval action (approve).
     *
     * @param DocumentApproval $documentApproval
     * @param ApprovalAction $approvalAction
     */
    private function processApprovalAction(DocumentApproval $documentApproval, ApprovalAction $approvalAction): void
    {
        $currentStage = $documentApproval->currentStage;
        $nextStage = $documentApproval->getNextStage();

        if ($nextStage) {
            // Move to next stage
            $documentApproval->update(['current_stage_id' => $nextStage->id]);
            $this->notificationService->notifyPendingApproval($documentApproval);
        } else {
            // Approval completed
            $documentApproval->update([
                'overall_status' => 'approved',
                'completed_at' => now(),
            ]);
            $this->notificationService->notifyApprovalComplete($documentApproval);
        }
    }

    /**
     * Process rejection action.
     *
     * @param DocumentApproval $documentApproval
     * @param ApprovalAction $approvalAction
     */
    private function processRejectionAction(DocumentApproval $documentApproval, ApprovalAction $approvalAction): void
    {
        $documentApproval->update([
            'overall_status' => 'rejected',
            'completed_at' => now(),
        ]);
        $this->notificationService->notifyApprovalRejected($documentApproval);
    }

    /**
     * Process forward action.
     *
     * @param DocumentApproval $documentApproval
     * @param ApprovalAction $approvalAction
     */
    private function processForwardAction(DocumentApproval $documentApproval, ApprovalAction $approvalAction): void
    {
        // Forward action doesn't change the stage, just records the action
        // The forwarded user will be notified separately
        if ($approvalAction->forwarded_to) {
            $this->notificationService->notifyForwardedApproval($documentApproval, $approvalAction->forwarded_to);
        }
    }

    /**
     * Process delegation action.
     *
     * @param DocumentApproval $documentApproval
     * @param ApprovalAction $approvalAction
     */
    private function processDelegationAction(DocumentApproval $documentApproval, ApprovalAction $approvalAction): void
    {
        // Delegation action doesn't change the stage, just records the action
        // The delegated user will be notified separately
        if ($approvalAction->delegated_to) {
            $this->notificationService->notifyDelegatedApproval($documentApproval, $approvalAction->delegated_to);
        }
    }

    /**
     * Get document metadata.
     *
     * @param string $documentType
     * @param string $documentId
     * @return array
     */
    private function getDocumentMetadata(string $documentType, string $documentId): array
    {
        // This method should be implemented based on the specific document types
        // For now, return basic metadata
        return [
            'document_type' => $documentType,
            'document_id' => $documentId,
            'submitted_at' => now()->toISOString(),
        ];
    }

    /**
     * Get approval status for a document.
     *
     * @param int $documentApprovalId The document approval ID
     * @return array The approval status
     */
    public function getApprovalStatus(int $documentApprovalId): array
    {
        $documentApproval = DocumentApproval::with(['currentStage', 'flow'])->find($documentApprovalId);
        if (!$documentApproval) {
            return [];
        }

        return [
            'overall_status' => $documentApproval->overall_status,
            'current_stage' => $documentApproval->currentStage?->stage_name,
            'flow_name' => $documentApproval->flow?->name,
            'submitted_at' => $documentApproval->submitted_at,
            'completed_at' => $documentApproval->completed_at,
            'progress' => $this->getApprovalProgress($documentApprovalId),
        ];
    }

    /**
     * Get approval progress for a document.
     *
     * @param int $documentApprovalId The document approval ID
     * @return array The approval progress
     */
    public function getApprovalProgress(int $documentApprovalId): array
    {
        $documentApproval = DocumentApproval::with(['flow.stages', 'actions'])->find($documentApprovalId);
        if (!$documentApproval) {
            return [];
        }

        $totalStages = $documentApproval->flow->stages->count();
        $completedStages = $documentApproval->actions->where('action', 'approved')->count();
        $percentage = $totalStages > 0 ? round(($completedStages / $totalStages) * 100, 2) : 0;

        return [
            'completed_stages' => $completedStages,
            'total_stages' => $totalStages,
            'percentage' => $percentage,
            'current_stage' => $documentApproval->currentStage?->stage_name,
        ];
    }

    /**
     * Bulk process multiple approvals.
     *
     * @param array $actions Array of approval actions
     * @param int $approverId The approver ID
     * @return array The results
     */
    public function bulkProcessApprovals(array $actions, int $approverId): array
    {
        $successCount = 0;
        $errorCount = 0;
        $results = [];

        foreach ($actions as $action) {
            try {
                $result = $this->processApproval(
                    $action['approval_id'],
                    $approverId,
                    $action['action'],
                    $action['comments'] ?? null
                );

                if ($result) {
                    $successCount++;
                    $results[] = [
                        'approval_id' => $action['approval_id'],
                        'status' => 'success',
                    ];
                } else {
                    $errorCount++;
                    $results[] = [
                        'approval_id' => $action['approval_id'],
                        'status' => 'error',
                        'message' => 'Processing failed',
                    ];
                }
            } catch (\Exception $e) {
                $errorCount++;
                $results[] = [
                    'approval_id' => $action['approval_id'],
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ];
            }
        }

        return [
            'success_count' => $successCount,
            'error_count' => $errorCount,
            'results' => $results,
        ];
    }
}
