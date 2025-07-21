<?php

namespace App\Exceptions\Approval;

use Exception;

/**
 * Base exception class for approval system.
 */
class ApprovalException extends Exception
{
    /**
     * The document type that caused the exception.
     *
     * @var string|null
     */
    protected $documentType;

    /**
     * The document ID that caused the exception.
     *
     * @var string|null
     */
    protected $documentId;

    /**
     * The user ID involved in the exception.
     *
     * @var int|null
     */
    protected $userId;

    /**
     * Create a new approval exception.
     *
     * @param string $message
     * @param int $code
     * @param Exception|null $previous
     * @param string|null $documentType
     * @param string|null $documentId
     * @param int|null $userId
     */
    public function __construct(
        string $message = '',
        int $code = 0,
        Exception $previous = null,
        ?string $documentType = null,
        ?string $documentId = null,
        ?int $userId = null
    ) {
        parent::__construct($message, $code, $previous);

        $this->documentType = $documentType;
        $this->documentId = $documentId;
        $this->userId = $userId;
    }

    /**
     * Get the document type.
     *
     * @return string|null
     */
    public function getDocumentType(): ?string
    {
        return $this->documentType;
    }

    /**
     * Get the document ID.
     *
     * @return string|null
     */
    public function getDocumentId(): ?string
    {
        return $this->documentId;
    }

    /**
     * Get the user ID.
     *
     * @return int|null
     */
    public function getUserId(): ?int
    {
        return $this->userId;
    }

    /**
     * Get the exception context for logging.
     *
     * @return array
     */
    public function getContext(): array
    {
        return [
            'document_type' => $this->documentType,
            'document_id' => $this->documentId,
            'user_id' => $this->userId,
            'message' => $this->getMessage(),
            'code' => $this->getCode(),
        ];
    }
}
