<?php

namespace App\Exceptions\Approval;

/**
 * Exception thrown when an approval action is not allowed.
 */
class ApprovalActionNotAllowedException extends ApprovalException
{
    /**
     * The action that was not allowed.
     *
     * @var string
     */
    protected $action;

    /**
     * Create a new approval action not allowed exception.
     *
     * @param string $action
     * @param int $userId
     * @param string $documentType
     * @param string $documentId
     * @param string $message
     * @param int $code
     * @param \Exception|null $previous
     */
    public function __construct(
        string $action,
        int $userId,
        string $documentType,
        string $documentId,
        string $message = 'Approval action not allowed',
        int $code = 0,
        \Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous, $documentType, $documentId, $userId);

        $this->action = $action;
    }

    /**
     * Get the action that was not allowed.
     *
     * @return string
     */
    public function getAction(): string
    {
        return $this->action;
    }

    /**
     * Get the exception context for logging.
     *
     * @return array
     */
    public function getContext(): array
    {
        return array_merge(parent::getContext(), [
            'action' => $this->action,
        ]);
    }
}
