<?php

namespace App\Services;

use App\Contracts\NotifiableDocument;
use App\Models\ApprovalPlan;
use App\Models\User;
use App\Notifications\DocumentApprovalNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class DocumentNotificationService
{
    public function __construct(
        protected DocumentAuditLogger $auditLogger
    ) {}

    /**
     * After approval plans are created on submit.
     */
    public function onDocumentSubmitted(string $documentType, string|int $documentId, int $approverCount): void
    {
        try {
            $document = $this->resolveDocument($documentType, $documentId);
            if (! $document) {
                return;
            }

            if ($approverCount > 0) {
                $this->auditLogger->logSubmitted($document, $documentType, $approverCount);
            }

            $this->notifyApproversOnSubmit($documentType, $documentId);
        } catch (\Throwable $e) {
            Log::error('DocumentNotificationService::onDocumentSubmitted failed', [
                'document_type' => $documentType,
                'document_id' => $documentId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * After an approval step is decided (approve/reject).
     */
    public function afterApprovalDecision(ApprovalPlan $plan): void
    {
        try {
            $plan->refresh();
            $document = $this->resolveDocument($plan->document_type, $plan->document_id);
            if (! $document) {
                return;
            }

            $this->auditLogger->logStep($plan, $document, (int) $plan->status);

            $document->refresh();
            $status = (string) ($document->status ?? '');
            if (in_array($status, ['approved', 'rejected'], true)) {
                $this->auditLogger->logDocumentFinal($document, $plan->document_type, $status);
            }

            $this->notifyAfterStep($plan);
        } catch (\Throwable $e) {
            Log::error('DocumentNotificationService::afterApprovalDecision failed', [
                'approval_plan_id' => $plan->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function notifyApproversOnSubmit(string $documentType, string|int $documentId): void
    {
        try {
            $document = $this->resolveDocument($documentType, $documentId);
            if (! $document instanceof NotifiableDocument) {
                return;
            }

            if (! config('document_notifications.enabled', true)) {
                $this->auditLogger->logEmail(
                    $document,
                    'email_skipped',
                    'Document notifications disabled',
                    [
                        'notification_event' => DocumentApprovalNotification::EVENT_APPROVAL_REQUESTED,
                        'reason' => 'feature_flag_disabled',
                    ]
                );

                return;
            }

            $recipients = $this->currentPendingApprovers($documentType, $documentId);
            $this->sendToUsers(
                $recipients,
                $document,
                DocumentApprovalNotification::EVENT_APPROVAL_REQUESTED,
                null,
                null,
                true
            );
        } catch (\Throwable $e) {
            Log::error('DocumentNotificationService::notifyApproversOnSubmit failed', [
                'document_type' => $documentType,
                'document_id' => $documentId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    public function notifyAfterStep(ApprovalPlan $plan): void
    {
        try {
            $document = $this->resolveDocument($plan->document_type, $plan->document_id);
            if (! $document instanceof NotifiableDocument) {
                return;
            }

            $status = (int) $plan->status;

            if ($status === 2) {
                $this->notifyRequester(
                    $document,
                    DocumentApprovalNotification::EVENT_DOCUMENT_REJECTED,
                    $plan->remarks
                );

                return;
            }

            if ($status !== 1) {
                return;
            }

            if ($this->isDocumentFullyApproved($document, $plan->document_type)) {
                $this->notifyRequester(
                    $document,
                    DocumentApprovalNotification::EVENT_DOCUMENT_APPROVED,
                    $plan->remarks
                );

                return;
            }

            if (! config('document_notifications.enabled', true)) {
                $this->auditLogger->logEmail(
                    $document,
                    'email_skipped',
                    'Document notifications disabled',
                    [
                        'notification_event' => DocumentApprovalNotification::EVENT_APPROVAL_NEEDED,
                        'reason' => 'feature_flag_disabled',
                    ]
                );

                return;
            }

            $nextApprovers = $this->currentPendingApprovers($plan->document_type, $plan->document_id);
            $this->sendToUsers(
                $nextApprovers,
                $document,
                DocumentApprovalNotification::EVENT_APPROVAL_NEEDED,
                null,
                $plan->remarks,
                true
            );
        } catch (\Throwable $e) {
            Log::error('DocumentNotificationService::notifyAfterStep failed', [
                'approval_plan_id' => $plan->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * @return \Illuminate\Support\Collection<int, User>
     */
    protected function currentPendingApprovers(string $documentType, string|int $documentId)
    {
        $plans = ApprovalPlan::query()
            ->where('document_type', $documentType)
            ->where('document_id', $documentId)
            ->where('is_open', true)
            ->where('status', 0)
            ->orderBy('approval_order')
            ->get();

        $userIds = $plans
            ->filter(fn (ApprovalPlan $plan) => $plan->canBeProcessed())
            ->pluck('approver_id')
            ->unique()
            ->filter()
            ->values();

        return User::whereIn('id', $userIds)->get();
    }

    protected function isDocumentFullyApproved(Model $document, string $documentType): bool
    {
        $status = (string) ($document->status ?? '');

        return in_array($status, ['approved'], true);
    }

    protected function notifyRequester(
        NotifiableDocument $document,
        string $event,
        ?string $remarks = null
    ): void {
        if (! config('document_notifications.enabled', true)) {
            $this->auditLogger->logEmail(
                $document,
                'email_skipped',
                'Document notifications disabled',
                [
                    'notification_event' => $event,
                    'reason' => 'feature_flag_disabled',
                ]
            );

            return;
        }

        $requester = $document->notificationRequester();
        if (! $requester) {
            $this->auditLogger->logEmail(
                $document,
                'email_skipped',
                'No requester found for document notification',
                [
                    'notification_event' => $event,
                    'reason' => 'no_requester',
                ]
            );

            return;
        }

        $this->sendToUsers(collect([$requester]), $document, $event, null, $remarks, false);
    }

    /**
     * @param  \Illuminate\Support\Collection<int, User>  $users
     */
    protected function sendToUsers(
        $users,
        NotifiableDocument $document,
        string $event,
        ?ApprovalPlan $plan = null,
        ?string $remarks = null,
        bool $useApprovalInboxUrl = false
    ): void {
        $seen = [];

        foreach ($users as $user) {
            if (! $user instanceof User) {
                continue;
            }

            if (isset($seen[$user->id])) {
                continue;
            }
            $seen[$user->id] = true;

            $email = trim((string) $user->email);
            if ($email === '') {
                $this->auditLogger->logEmail(
                    $document,
                    'email_skipped',
                    "Skipped email for user #{$user->id} (empty email)",
                    [
                        'notification_event' => $event,
                        'recipient_user_id' => $user->id,
                        'reason' => 'empty_email',
                    ]
                );

                continue;
            }

            $actionUrl = $useApprovalInboxUrl
                ? route('approval.requests.index')
                : $document->notificationActionUrl();

            $subjectHint = "[{$event}] {$document->notificationDocumentLabel()} {$document->notificationReference()}";

            try {
                Notification::send(
                    $user,
                    new DocumentApprovalNotification($document, $event, $plan, $remarks, $actionUrl)
                );

                $this->auditLogger->logEmail(
                    $document,
                    'email_sent',
                    "Email sent to {$email} ({$event})",
                    [
                        'notification_event' => $event,
                        'recipient_user_id' => $user->id,
                        'recipient_email' => $email,
                        'subject' => $subjectHint,
                        'action_url' => $actionUrl,
                    ]
                );
            } catch (\Throwable $e) {
                $this->auditLogger->logEmail(
                    $document,
                    'email_failed',
                    "Email failed for {$email} ({$event})",
                    [
                        'notification_event' => $event,
                        'recipient_user_id' => $user->id,
                        'recipient_email' => $email,
                        'subject' => $subjectHint,
                        'error' => $e->getMessage(),
                    ]
                );

                Log::error('Document notification email failed', [
                    'user_id' => $user->id,
                    'email' => $email,
                    'event' => $event,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }

    public function resolveDocument(string $documentType, string|int $documentId): ?Model
    {
        $class = config("document_notifications.document_types.{$documentType}");
        if (! $class || ! class_exists($class)) {
            Log::warning('Unknown document type for notification', ['document_type' => $documentType]);

            return null;
        }

        return $class::find($documentId);
    }
}
