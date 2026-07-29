<?php

namespace App\Services;

use App\Contracts\NotifiableDocument;
use App\Models\ApprovalPlan;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class DocumentAuditLogger
{
    public const LOG_APPROVAL = 'document_approval';

    public const LOG_EMAIL = 'document_email';

    public function logApproval(
        Model $document,
        string $event,
        string $description,
        array $properties = []
    ): void {
        try {
            $base = $this->baseProperties($document);
            $logger = activity(self::LOG_APPROVAL)
                ->event($event)
                ->withProperties(array_merge($base, $properties));

            $causer = Auth::user();
            if ($causer instanceof User) {
                $logger->causedBy($causer);
            }

            $logger->performedOn($document)->log($description);
        } catch (\Throwable $e) {
            Log::warning('DocumentAuditLogger::logApproval failed', [
                'event' => $event,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function logEmail(
        Model $document,
        string $event,
        string $description,
        array $properties = []
    ): void {
        try {
            $base = $this->baseProperties($document);
            $logger = activity(self::LOG_EMAIL)
                ->event($event)
                ->withProperties(array_merge($base, $properties));

            $causer = Auth::user();
            if ($causer instanceof User) {
                $logger->causedBy($causer);
            }

            $logger->performedOn($document)->log($description);
        } catch (\Throwable $e) {
            Log::warning('DocumentAuditLogger::logEmail failed', [
                'event' => $event,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function logSubmitted(Model $document, string $documentType, int $approverCount): void
    {
        $ref = $document instanceof NotifiableDocument
            ? $document->notificationReference()
            : (string) $document->getKey();

        $this->logApproval(
            $document,
            'submitted',
            "Document {$documentType} {$ref} submitted for approval ({$approverCount} approver(s))",
            [
                'document_type' => $documentType,
                'approver_count' => $approverCount,
            ]
        );
    }

    public function logStep(ApprovalPlan $plan, Model $document, int $status): void
    {
        $event = $status === 1 ? 'step_approved' : 'step_rejected';
        $label = $status === 1 ? 'approved' : 'rejected';
        $ref = $document instanceof NotifiableDocument
            ? $document->notificationReference()
            : (string) $document->getKey();

        $this->logApproval(
            $document,
            $event,
            "Approval step {$label} for {$plan->document_type} {$ref} (order {$plan->approval_order})",
            [
                'document_type' => $plan->document_type,
                'approval_plan_id' => $plan->id,
                'approver_id' => $plan->approver_id,
                'approval_order' => $plan->approval_order,
                'remarks' => $plan->remarks,
                'status' => $status,
            ]
        );
    }

    public function logDocumentFinal(Model $document, string $documentType, string $finalStatus): void
    {
        $event = $finalStatus === 'approved' ? 'document_approved' : 'document_rejected';
        $ref = $document instanceof NotifiableDocument
            ? $document->notificationReference()
            : (string) $document->getKey();

        $this->logApproval(
            $document,
            $event,
            "Document {$documentType} {$ref} {$finalStatus}",
            [
                'document_type' => $documentType,
                'final_status' => $finalStatus,
            ]
        );
    }

    /**
     * @return array<string, mixed>
     */
    protected function baseProperties(Model $document): array
    {
        $props = [
            'document_id' => $document->getKey(),
        ];

        if ($document instanceof NotifiableDocument) {
            $props['document_type'] = $document->notificationDocumentType();
            $props['reference'] = $document->notificationReference();
            $props['document_label'] = $document->notificationDocumentLabel();
        }

        return $props;
    }
}
