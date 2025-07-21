<?php

namespace App\Exceptions\Approval;

/**
 * Exception thrown when a document approval is not found.
 */
class DocumentApprovalNotFoundException extends ApprovalException
{
    /**
     * Create a new document approval not found exception.
     *
     * @param string $documentType
     * @param string $documentId
     * @param string $message
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct(
        string $documentType,
        string $documentId,
        string $message = 'Document approval not found',
        int $code = 0,
        \Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous, $documentType, $documentId);
    }
}
