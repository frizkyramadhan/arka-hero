<?php

namespace App\Listeners;

use App\Notifications\DocumentApprovalNotification;
use App\Services\DocumentAuditLogger;
use Illuminate\Notifications\Events\NotificationFailed;
use Illuminate\Support\Facades\Log;

class LogDocumentNotificationFailed
{
    public function __construct(
        protected DocumentAuditLogger $auditLogger
    ) {}

    public function handle(NotificationFailed $event): void
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
            $error = is_array($event->data) && isset($event->data['message'])
                ? (string) $event->data['message']
                : 'Notification failed';

            $this->auditLogger->logEmail(
                $document,
                'email_failed',
                "Email failed for {$email} ({$notification->event})",
                [
                    'notification_event' => $notification->event,
                    'recipient_user_id' => $userId,
                    'recipient_email' => $email,
                    'subject' => $subjectHint,
                    'error' => $error,
                    'approval_plan_id' => $notification->plan?->id,
                    'channel' => $event->channel,
                ]
            );
        } catch (\Throwable $e) {
            Log::warning('LogDocumentNotificationFailed failed', ['error' => $e->getMessage()]);
        }
    }
}
