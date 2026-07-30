<?php

namespace App\Listeners;

use App\Notifications\DocumentApprovalNotification;
use App\Services\DocumentAuditLogger;
use Illuminate\Notifications\Events\NotificationSent;
use Illuminate\Support\Facades\Log;

class LogDocumentNotificationSent
{
    public function __construct(
        protected DocumentAuditLogger $auditLogger
    ) {}

    public function handle(NotificationSent $event): void
    {
        $notification = $event->notification;
        if (! $notification instanceof DocumentApprovalNotification) {
            return;
        }

        try {
            $document = $notification->document;
            if (! $document instanceof \Illuminate\Database\Eloquent\Model) {
                return;
            }

            $email = DocumentApprovalNotification::resolveNotifiableEmail($event->notifiable) ?? '';
            $userId = $event->notifiable->id ?? null;
            $subjectHint = "[{$notification->event}] {$document->notificationDocumentLabel()} {$document->notificationReference()}";

            $this->auditLogger->logEmail(
                $document,
                'email_sent',
                "Email sent to {$email} ({$notification->event})",
                [
                    'notification_event' => $notification->event,
                    'recipient_user_id' => $userId,
                    'recipient_email' => $email,
                    'subject' => $subjectHint,
                    'action_url' => $notification->actionUrl,
                    'approval_plan_id' => $notification->plan?->id,
                    'channel' => $event->channel,
                ]
            );
        } catch (\Throwable $e) {
            Log::warning('LogDocumentNotificationSent failed', ['error' => $e->getMessage()]);
        }
    }
}
