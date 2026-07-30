<?php

namespace App\Services;

use App\Contracts\NotifiableDocument;
use App\Models\ApprovalPlan;
use App\Models\DocumentNotificationSend;
use App\Models\User;
use App\Notifications\DocumentApprovalNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
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

            $this->sendToApproverPlans(
                $this->currentPendingApprovalPlans($documentType, $documentId),
                $document,
                DocumentApprovalNotification::EVENT_APPROVAL_REQUESTED,
                null
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

            $this->sendToApproverPlans(
                $this->currentPendingApprovalPlans($plan->document_type, $plan->document_id),
                $document,
                DocumentApprovalNotification::EVENT_APPROVAL_NEEDED,
                $plan->remarks
            );
        } catch (\Throwable $e) {
            Log::error('DocumentNotificationService::notifyAfterStep failed', [
                'approval_plan_id' => $plan->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Send overdue reminders for pending open approval plans.
     *
     * @return array{reminded: int, skipped: int}
     */
    public function remindPendingApprovals(): array
    {
        $reminded = 0;
        $skipped = 0;

        if (! config('document_notifications.enabled', true)
            || ! config('document_notifications.reminder_enabled', true)) {
            return compact('reminded', 'skipped');
        }

        $days = max(1, (int) config('document_notifications.reminder_days', 3));
        $cutoff = now()->subDays($days);

        $plans = ApprovalPlan::query()
            ->where('is_open', true)
            ->where('status', 0)
            ->where('created_at', '<=', $cutoff)
            ->orderBy('id')
            ->get()
            ->filter(fn (ApprovalPlan $plan) => $plan->canBeProcessed())
            ->values();

        foreach ($plans as $plan) {
            $document = $this->resolveDocument($plan->document_type, $plan->document_id);
            if (! $document instanceof NotifiableDocument) {
                $skipped++;

                continue;
            }

            $user = User::find($plan->approver_id);
            if (! $user) {
                $skipped++;

                continue;
            }

            $email = trim((string) $user->email);
            if ($email === '') {
                $skipped++;

                continue;
            }

            if (! $this->claimSendSlot(
                $document,
                DocumentApprovalNotification::EVENT_APPROVAL_REMINDER,
                $user->id,
                $plan,
                now()->toDateString()
            )) {
                $skipped++;

                continue;
            }

            $notification = new DocumentApprovalNotification(
                $document,
                DocumentApprovalNotification::EVENT_APPROVAL_REMINDER,
                $plan,
                null,
                null,
                true // already claimed slot above
            );
            $actionUrl = $notification->resolveActionUrl();
            $notification->actionUrl = $actionUrl;

            try {
                Notification::send($user, $notification);
                $this->auditLogger->logEmail(
                    $document,
                    'email_queued',
                    "Email queued for {$email} (".DocumentApprovalNotification::EVENT_APPROVAL_REMINDER.')',
                    [
                        'notification_event' => DocumentApprovalNotification::EVENT_APPROVAL_REMINDER,
                        'recipient_user_id' => $user->id,
                        'recipient_email' => $email,
                        'action_url' => $actionUrl,
                        'approval_plan_id' => $plan->id,
                        'dedupe_day' => now()->toDateString(),
                    ]
                );
                $reminded++;
            } catch (\Throwable $e) {
                $skipped++;
                Log::error('Reminder notification failed', [
                    'approval_plan_id' => $plan->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return compact('reminded', 'skipped');
    }

    /**
     * @return \Illuminate\Support\Collection<int, ApprovalPlan>
     */
    protected function currentPendingApprovalPlans(string $documentType, string|int $documentId)
    {
        return ApprovalPlan::query()
            ->where('document_type', $documentType)
            ->where('document_id', $documentId)
            ->where('is_open', true)
            ->where('status', 0)
            ->orderBy('approval_order')
            ->get()
            ->filter(fn (ApprovalPlan $plan) => $plan->canBeProcessed())
            ->values();
    }

    protected function isDocumentFullyApproved(Model $document, string $documentType): bool
    {
        $status = (string) ($document->status ?? '');

        return in_array($status, ['approved'], true);
    }

    protected function notifyRequester(
        Model&NotifiableDocument $document,
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

        $this->sendToUsers(collect([$requester]), $document, $event, null, $remarks);
    }

    /**
     * Send each approver a notification tied to their approval_plans row.
     *
     * @param  \Illuminate\Support\Collection<int, ApprovalPlan>  $plans
     */
    protected function sendToApproverPlans(
        $plans,
        Model&NotifiableDocument $document,
        string $event,
        ?string $remarks = null
    ): void {
        $seenUsers = [];

        foreach ($plans as $plan) {
            $userId = (int) $plan->approver_id;
            if ($userId <= 0 || isset($seenUsers[$userId])) {
                continue;
            }

            $seenUsers[$userId] = true;
            $user = User::find($userId);
            if (! $user) {
                continue;
            }

            $this->sendToUsers(collect([$user]), $document, $event, $plan, $remarks);
        }
    }

    /**
     * @param  \Illuminate\Support\Collection<int, User>  $users
     */
    public function sendToUsers(
        $users,
        Model&NotifiableDocument $document,
        string $event,
        ?ApprovalPlan $plan = null,
        ?string $remarks = null,
        bool $bypassIdempotency = false,
        ?string $dedupeDay = null
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

            if (! $bypassIdempotency && ! $this->claimSendSlot($document, $event, $user->id, $plan, $dedupeDay)) {
                $this->auditLogger->logEmail(
                    $document,
                    'email_skipped',
                    "Skipped duplicate email for {$email} ({$event})",
                    [
                        'notification_event' => $event,
                        'recipient_user_id' => $user->id,
                        'recipient_email' => $email,
                        'reason' => 'duplicate',
                        'approval_plan_id' => $plan?->id,
                        'dedupe_day' => $dedupeDay ?? '',
                    ]
                );

                continue;
            }

            $notification = new DocumentApprovalNotification(
                $document,
                $event,
                $plan,
                $remarks,
                null,
                $bypassIdempotency
            );
            $actionUrl = $notification->resolveActionUrl();
            $notification->actionUrl = $actionUrl;

            $subjectHint = "[{$event}] {$document->notificationDocumentLabel()} {$document->notificationReference()}";

            try {
                Notification::send($user, $notification);

                $this->auditLogger->logEmail(
                    $document,
                    'email_queued',
                    "Email queued for {$email} ({$event})",
                    [
                        'notification_event' => $event,
                        'recipient_user_id' => $user->id,
                        'recipient_email' => $email,
                        'subject' => $subjectHint,
                        'action_url' => $actionUrl,
                        'approval_plan_id' => $plan?->id,
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
                        'approval_plan_id' => $plan?->id,
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

    protected function claimSendSlot(
        Model&NotifiableDocument $document,
        string $event,
        int $userId,
        ?ApprovalPlan $plan,
        ?string $dedupeDay
    ): bool {
        try {
            DocumentNotificationSend::query()->create([
                'document_type' => $document->notificationDocumentType(),
                'document_id' => (string) $document->getKey(),
                'event' => $event,
                'recipient_user_id' => $userId,
                'approval_plan_id' => (int) ($plan?->id ?? 0),
                'dedupe_day' => $dedupeDay ?? '',
            ]);

            return true;
        } catch (QueryException $e) {
            // Unique constraint violation = already sent/queued
            return false;
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
