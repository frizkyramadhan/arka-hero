<?php

namespace App\Contracts;

/**
 * Interface for documents that can be approved through the dynamic approval system.
 *
 * Any model that implements this interface can be integrated with the approval system.
 */
interface ApprovableDocument
{
    /**
     * Get the document type identifier for the approval system.
     *
     * @return string The document type (e.g., 'officialtravel', 'recruitment_request', 'employee_registration')
     */
    public function getApprovalDocumentType(): string;

    /**
     * Get the document ID for the approval system.
     *
     * @return string The document ID (can be UUID or regular ID)
     */
    public function getApprovalDocumentId(): string;

    /**
     * Get additional metadata for the approval process.
     *
     * @return array Additional data that might be needed for approval decisions
     */
    public function getApprovalMetadata(): array;

    /**
     * Check if this document can be submitted for approval.
     *
     * @return bool True if the document can be approved, false otherwise
     */
    public function canBeApproved(): bool;

    /**
     * Get the title or name of the document for display purposes.
     *
     * @return string The document title/name
     */
    public function getApprovalDocumentTitle(): string;

    /**
     * Get the user who created/submitted this document.
     *
     * @return \App\Models\User|null The user who created the document
     */
    public function getApprovalDocumentCreator();

    /**
     * Get the current status of the document.
     *
     * @return string The current status (e.g., 'draft', 'submitted', 'approved', 'rejected')
     */
    public function getApprovalDocumentStatus(): string;

    /**
     * Check if the document is in a state that allows approval submission.
     *
     * @return bool True if the document can be submitted for approval
     */
    public function canBeSubmittedForApproval(): bool;

    /**
     * Check if the document is currently pending approval.
     *
     * @return bool True if the document is pending approval
     */
    public function isPendingApproval(): bool;

    /**
     * Check if the document has been approved.
     *
     * @return bool True if the document has been approved
     */
    public function isApproved(): bool;

    /**
     * Check if the document has been rejected.
     *
     * @return bool True if the document has been rejected
     */
    public function isRejected(): bool;

    /**
     * Get the approval flow ID associated with this document type.
     *
     * @return int|null The approval flow ID
     */
    public function getApprovalFlowId(): ?int;

    /**
     * Set the approval flow ID for this document.
     *
     * @param int $flowId The approval flow ID
     * @return void
     */
    public function setApprovalFlowId(int $flowId): void;

    /**
     * Callback method called when the approval process is completed successfully.
     *
     * @return void
     */
    public function onApprovalCompleted(): void;

    /**
     * Callback method called when the approval process is rejected.
     *
     * @return void
     */
    public function onApprovalRejected(): void;

    /**
     * Callback method called when the approval process is cancelled.
     *
     * @return void
     */
    public function onApprovalCancelled(): void;

    /**
     * Get the URL for viewing this document in the approval interface.
     *
     * @return string The URL for viewing the document
     */
    public function getApprovalViewUrl(): string;

    /**
     * Get the URL for editing this document.
     *
     * @return string The URL for editing the document
     */
    public function getApprovalEditUrl(): string;

    /**
     * Get additional validation rules for approval submission.
     *
     * @return array Validation rules
     */
    public function getApprovalValidationRules(): array;

    /**
     * Get custom approval messages or notes.
     *
     * @return array Custom messages for the approval process
     */
    public function getApprovalMessages(): array;
}
