<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;

/**
 * Service class for approval audit logging
 */
class ApprovalAuditService
{
    /**
     * Log approval flow creation
     *
     * @param array $flowData The flow data
     * @param User $user The user who created the flow
     * @return void
     */
    public function logApprovalFlowCreation(array $flowData, User $user): void
    {
        Log::channel('approval_audit')->info('Approval flow created', [
            'action' => 'flow_created',
            'user_id' => $user->id,
            'user_name' => $user->name,
            'flow_data' => $flowData,
            'timestamp' => now(),
        ]);
    }

    /**
     * Log approval flow modification
     *
     * @param int $flowId The flow ID
     * @param array $oldData The old flow data
     * @param array $newData The new flow data
     * @param User $user The user who modified the flow
     * @return void
     */
    public function logApprovalFlowModification(int $flowId, array $oldData, array $newData, User $user): void
    {
        Log::channel('approval_audit')->info('Approval flow modified', [
            'action' => 'flow_modified',
            'flow_id' => $flowId,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'old_data' => $oldData,
            'new_data' => $newData,
            'changes' => $this->getChanges($oldData, $newData),
            'timestamp' => now(),
        ]);
    }

    /**
     * Log approval flow deletion
     *
     * @param int $flowId The flow ID
     * @param array $flowData The flow data that was deleted
     * @param User $user The user who deleted the flow
     * @return void
     */
    public function logApprovalFlowDeletion(int $flowId, array $flowData, User $user): void
    {
        Log::channel('approval_audit')->info('Approval flow deleted', [
            'action' => 'flow_deleted',
            'flow_id' => $flowId,
            'user_id' => $user->id,
            'user_name' => $user->name,
            'flow_data' => $flowData,
            'timestamp' => now(),
        ]);
    }

    /**
     * Log approval action
     *
     * @param int $documentApprovalId The document approval ID
     * @param int $approverId The approver ID
     * @param string $action The action taken
     * @param array $metadata Additional metadata
     * @return void
     */
    public function logApprovalAction(int $documentApprovalId, int $approverId, string $action, array $metadata = []): void
    {
        Log::channel('approval_audit')->info('Approval action taken', [
            'action' => 'approval_action',
            'document_approval_id' => $documentApprovalId,
            'approver_id' => $approverId,
            'approval_action' => $action,
            'metadata' => $metadata,
            'timestamp' => now(),
        ]);
    }

    /**
     * Log approval escalation
     *
     * @param int $documentApprovalId The document approval ID
     * @param int $stageId The stage ID
     * @param array $metadata Additional metadata
     * @return void
     */
    public function logApprovalEscalation(int $documentApprovalId, int $stageId, array $metadata = []): void
    {
        Log::channel('approval_audit')->info('Approval escalated', [
            'action' => 'approval_escalated',
            'document_approval_id' => $documentApprovalId,
            'stage_id' => $stageId,
            'metadata' => $metadata,
            'timestamp' => now(),
        ]);
    }

    /**
     * Log approval delegation
     *
     * @param int $documentApprovalId The document approval ID
     * @param int $fromUserId The user who delegated
     * @param int $toUserId The user who received the delegation
     * @param array $metadata Additional metadata
     * @return void
     */
    public function logApprovalDelegation(int $documentApprovalId, int $fromUserId, int $toUserId, array $metadata = []): void
    {
        Log::channel('approval_audit')->info('Approval delegated', [
            'action' => 'approval_delegated',
            'document_approval_id' => $documentApprovalId,
            'from_user_id' => $fromUserId,
            'to_user_id' => $toUserId,
            'metadata' => $metadata,
            'timestamp' => now(),
        ]);
    }

    /**
     * Get changes between old and new data
     *
     * @param array $oldData The old data
     * @param array $newData The new data
     * @return array The changes
     */
    private function getChanges(array $oldData, array $newData): array
    {
        $changes = [];

        foreach ($newData as $key => $value) {
            if (!isset($oldData[$key]) || $oldData[$key] !== $value) {
                $changes[$key] = [
                    'old' => $oldData[$key] ?? null,
                    'new' => $value,
                ];
            }
        }

        return $changes;
    }
}
