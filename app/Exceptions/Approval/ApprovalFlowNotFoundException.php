<?php

namespace App\Exceptions\Approval;

/**
 * Exception thrown when an approval flow is not found.
 */
class ApprovalFlowNotFoundException extends ApprovalException
{
    /**
     * Create a new approval flow not found exception.
     *
     * @param string $documentType
     * @param string $message
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct(
        string $documentType,
        string $message = 'Approval flow not found',
        int $code = 0,
        \Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous, $documentType);
    }
}
