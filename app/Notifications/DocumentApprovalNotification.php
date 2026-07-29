<?php

namespace App\Notifications;

use App\Contracts\NotifiableDocument;
use App\Models\ApprovalPlan;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class DocumentApprovalNotification extends Notification
{
    use Queueable;
    use SerializesModels;

    public const EVENT_APPROVAL_REQUESTED = 'approval_requested';

    public const EVENT_APPROVAL_NEEDED = 'approval_needed';

    public const EVENT_DOCUMENT_APPROVED = 'document_approved';

    public const EVENT_DOCUMENT_REJECTED = 'document_rejected';

    public function __construct(
        public NotifiableDocument $document,
        public string $event,
        public ?ApprovalPlan $plan = null,
        public ?string $remarks = null,
        public ?string $actionUrl = null,
    ) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $appName = config('app.name', 'ARKA HERO');
        $label = $this->document->notificationDocumentLabel();
        $reference = $this->document->notificationReference();
        $title = $this->document->notificationTitle();
        $url = $this->actionUrl ?: $this->document->notificationActionUrl();
        $recipientName = $notifiable->name ?? ($notifiable->email ?? 'User');

        $subject = match ($this->event) {
            self::EVENT_APPROVAL_REQUESTED, self::EVENT_APPROVAL_NEEDED => "[{$appName}] {$label} {$reference} needs your approval",
            self::EVENT_DOCUMENT_APPROVED => "[{$appName}] {$label} {$reference} has been approved",
            self::EVENT_DOCUMENT_REJECTED => "[{$appName}] {$label} {$reference} has been rejected",
            default => "[{$appName}] {$label} {$reference} update",
        };

        $headline = match ($this->event) {
            self::EVENT_APPROVAL_REQUESTED, self::EVENT_APPROVAL_NEEDED => 'Approval Required',
            self::EVENT_DOCUMENT_APPROVED => 'Document Approved',
            self::EVENT_DOCUMENT_REJECTED => 'Document Rejected',
            default => 'Document Update',
        };

        $intro = match ($this->event) {
            self::EVENT_APPROVAL_REQUESTED => "A new {$label} has been submitted and needs your approval.",
            self::EVENT_APPROVAL_NEEDED => "The previous approval step is complete. {$label} {$reference} now needs your approval.",
            self::EVENT_DOCUMENT_APPROVED => "Your {$label} {$reference} has been fully approved.",
            self::EVENT_DOCUMENT_REJECTED => "Your {$label} {$reference} has been rejected.",
            default => "There is an update for {$label} {$reference}.",
        };

        $cta = match ($this->event) {
            self::EVENT_APPROVAL_REQUESTED, self::EVENT_APPROVAL_NEEDED => 'Review & Approve',
            default => 'View Document',
        };

        return (new MailMessage)
            ->subject($subject)
            ->from(
                config('document_notifications.from.address'),
                config('document_notifications.from.name')
            )
            ->view('emails.documents.approval', [
                'appName' => $appName,
                'headline' => $headline,
                'intro' => $intro,
                'cta' => $cta,
                'actionUrl' => $url,
                'recipientName' => $recipientName,
                'event' => $this->event,
                'documentLabel' => $label,
                'reference' => $reference,
                'title' => $title,
                'summary' => $this->document->notificationSummary(),
                'remarks' => $this->remarks,
                'approvalOrder' => $this->plan?->approval_order,
            ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'event' => $this->event,
            'document_type' => $this->document->notificationDocumentType(),
            'document_id' => method_exists($this->document, 'getKey') ? $this->document->getKey() : null,
            'reference' => $this->document->notificationReference(),
        ];
    }
}
