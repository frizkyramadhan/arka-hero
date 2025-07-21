<?php

namespace App\Contracts;

use App\Models\DocumentApproval;
use App\Models\User;
use Illuminate\Support\Collection;

/**
 * Interface for approval services.
 *
 * This interface defines the contract for approval engine services.
 */
interface ApprovalInterface
{
    /**
     * Submit a document for approval.
     *
     * @param string $documentType The type of document
     * @param string $documentId The document ID
     * @param int $submittedBy The user ID who submitted the document
     * @return DocumentApproval The created document approval instance
     * @throws \Exception If submission fails
     */
    public function submitForApproval(string $documentType, string $documentId, int $submittedBy): DocumentApproval;

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
    public function processApproval(int $documentApprovalId, int $approverId, string $action, ?string $comments = null, array $additionalData = []): bool;

    /**
     * Get the next approvers for a document approval.
     *
     * @param int $documentApprovalId The document approval ID
     * @return Collection The collection of approvers
     */
    public function getNextApprovers(int $documentApprovalId): Collection;

    /**
     * Check if a user can approve a specific document.
     *
     * @param int $documentApprovalId The document approval ID
     * @param User $user The user to check
     * @return bool True if the user can approve
     */
    public function canUserApprove(int $documentApprovalId, User $user): bool;

    /**
     * Escalate an approval that has been pending too long.
     *
     * @param int $documentApprovalId The document approval ID
     * @return bool True if escalation was successful
     */
    public function escalateApproval(int $documentApprovalId): bool;

    /**
     * Cancel an approval process.
     *
     * @param int $documentApprovalId The document approval ID
     * @param int $cancelledBy The user ID who cancelled the approval
     * @return bool True if cancellation was successful
     */
    public function cancelApproval(int $documentApprovalId, int $cancelledBy): bool;

    /**
     * Forward an approval to another user.
     *
     * @param int $documentApprovalId The document approval ID
     * @param int $approverId The current approver ID
     * @param int $forwardedTo The user ID to forward to
     * @param string|null $comments Optional comments
     * @return bool True if forwarding was successful
     */
    public function forwardApproval(int $documentApprovalId, int $approverId, int $forwardedTo, ?string $comments = null): bool;

    /**
     * Delegate an approval to another user.
     *
     * @param int $documentApprovalId The document approval ID
     * @param int $approverId The current approver ID
     * @param int $delegatedTo The user ID to delegate to
     * @param string|null $comments Optional comments
     * @return bool True if delegation was successful
     */
    public function delegateApproval(int $documentApprovalId, int $approverId, int $delegatedTo, ?string $comments = null): bool;

    /**
     * Get the approval history for a document.
     *
     * @param int $documentApprovalId The document approval ID
     * @return Collection The approval actions
     */
    public function getApprovalHistory(int $documentApprovalId): Collection;

    /**
     * Get pending approvals for a user.
     *
     * @param User $user The user
     * @param array $filters Optional filters
     * @return Collection The pending approvals
     */
    public function getPendingApprovalsForUser(User $user, array $filters = []): Collection;

    /**
     * Get approval statistics for a user.
     *
     * @param User $user The user
     * @param array $filters Optional filters
     * @return array The statistics
     */
    public function getApprovalStatisticsForUser(User $user, array $filters = []): array;

    /**
     * Check if an approval is overdue and needs escalation.
     *
     * @param int $documentApprovalId The document approval ID
     * @return bool True if the approval is overdue
     */
    public function isApprovalOverdue(int $documentApprovalId): bool;

    /**
     * Get overdue approvals.
     *
     * @return Collection The overdue approvals
     */
    public function getOverdueApprovals(): Collection;

    /**
     * Process automatic approvals based on conditions.
     *
     * @param int $documentApprovalId The document approval ID
     * @return bool True if automatic approval was processed
     */
    public function processAutomaticApproval(int $documentApprovalId): bool;

    /**
     * Validate approval action.
     *
     * @param int $documentApprovalId The document approval ID
     * @param int $approverId The approver ID
     * @param string $action The action type
     * @return bool True if the action is valid
     */
    public function validateApprovalAction(int $documentApprovalId, int $approverId, string $action): bool;

    /**
     * Get approval flow for a document type.
     *
     * @param string $documentType The document type
     * @return \App\Models\ApprovalFlow|null The approval flow
     */
    public function getApprovalFlowForDocumentType(string $documentType);

    /**
     * Create approval flow.
     *
     * @param array $flowData The flow data
     * @return \App\Models\ApprovalFlow The created flow
     */
    public function createApprovalFlow(array $flowData);

    /**
     * Update approval flow.
     *
     * @param int $flowId The flow ID
     * @param array $flowData The flow data
     * @return \App\Models\ApprovalFlow The updated flow
     */
    public function updateApprovalFlow(int $flowId, array $flowData);

    /**
     * Delete approval flow.
     *
     * @param int $flowId The flow ID
     * @return bool True if deletion was successful
     */
    public function deleteApprovalFlow(int $flowId): bool;
}
