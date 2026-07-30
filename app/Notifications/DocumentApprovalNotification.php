<?php

namespace App\Notifications;

use App\Contracts\NotifiableDocument;
use App\Models\ApprovalPlan;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Queue\SerializesModels;

class DocumentApprovalNotification extends Notification implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public const EVENT_APPROVAL_REQUESTED = 'approval_requested';

    public const EVENT_APPROVAL_NEEDED = 'approval_needed';

    public const EVENT_APPROVAL_REMINDER = 'approval_reminder';

    public const EVENT_DOCUMENT_APPROVED = 'document_approved';

    public const EVENT_DOCUMENT_REJECTED = 'document_rejected';

    /** @var int */
    public $tries = 3;

    public function __construct(
        public NotifiableDocument $document,
        public string $event,
        public ?ApprovalPlan $plan = null,
        public ?string $remarks = null,
        public ?string $actionUrl = null,
        public bool $bypassIdempotency = false,
    ) {
        $this->afterCommit = true;
    }

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $viewData = $this->mailViewData($notifiable, true);
        $logoPath = self::logoFilesystemPath();

        $mail = (new MailMessage)
            ->subject($viewData['subject'])
            ->from(
                config('document_notifications.from.address'),
                config('document_notifications.from.name')
            )
            ->view('emails.documents.approval', $viewData)
            ->text('emails.documents.approval-text', $viewData);

        if ($this->shouldAttachCc()) {
            foreach ($this->ccAddresses() as $cc) {
                $mail->cc($cc);
            }
        }

        $fromAddress = (string) config('document_notifications.from.address');
        $mail->withSymfonyMessage(function ($message) use ($logoPath, $fromAddress) {
            if ($logoPath !== null) {
                $message->embedFromPath($logoPath, 'arka-logo', 'image/jpeg');
            }

            $headers = $message->getHeaders();
            $headers->addTextHeader('X-Auto-Response-Suppress', 'OOF, DR, RN, NRN, AutoReply');
            $headers->addTextHeader('Auto-Submitted', 'auto-generated');
            if ($fromAddress !== '' && filter_var($fromAddress, FILTER_VALIDATE_EMAIL)) {
                $headers->addTextHeader(
                    'List-Unsubscribe',
                    '<mailto:'.$fromAddress.'?subject=unsubscribe-document-notifications>'
                );
            }
        });

        return $mail;
    }

    /**
     * Build the same data for delivered email and browser preview.
     *
     * @param  bool  $embedLogo  true = CID for SMTP (Thunderbird-safe); false = absolute URL for browser preview
     * @return array<string, mixed>
     */
    public function mailViewData(object $notifiable, bool $embedLogo = false): array
    {
        $appName = config('app.name', 'ARKA HERO');
        $label = $this->document->notificationDocumentLabel();
        $reference = $this->document->notificationReference();
        $title = $this->document->notificationTitle();
        $url = $this->resolveActionUrl();
        $recipientName = self::resolveRecipientDisplayName($notifiable);
        $logoUrl = $embedLogo && self::logoFilesystemPath() !== null
            ? 'cid:arka-logo'
            : self::logoUrl();

        $subject = match ($this->event) {
            self::EVENT_APPROVAL_REQUESTED, self::EVENT_APPROVAL_NEEDED => "[{$appName}] {$label} {$reference} needs your approval",
            self::EVENT_APPROVAL_REMINDER => "[{$appName}] Reminder: {$label} {$reference} still needs your approval",
            self::EVENT_DOCUMENT_APPROVED => "[{$appName}] {$label} {$reference} has been approved",
            self::EVENT_DOCUMENT_REJECTED => "[{$appName}] {$label} {$reference} has been rejected",
            default => "[{$appName}] {$label} {$reference} update",
        };

        $headline = match ($this->event) {
            self::EVENT_APPROVAL_REQUESTED, self::EVENT_APPROVAL_NEEDED => 'Approval Required',
            self::EVENT_APPROVAL_REMINDER => 'Approval Reminder',
            self::EVENT_DOCUMENT_APPROVED => 'Document Approved',
            self::EVENT_DOCUMENT_REJECTED => 'Document Rejected',
            default => 'Document Update',
        };

        $intro = match ($this->event) {
            self::EVENT_APPROVAL_REQUESTED => "A new {$label} has been submitted and needs your approval.",
            self::EVENT_APPROVAL_NEEDED => "The previous approval step is complete. {$label} {$reference} now needs your approval.",
            self::EVENT_APPROVAL_REMINDER => "{$label} {$reference} is still pending your approval. Please review it at your earliest convenience.",
            self::EVENT_DOCUMENT_APPROVED => "Your {$label} {$reference} has been fully approved.",
            self::EVENT_DOCUMENT_REJECTED => "Your {$label} {$reference} has been rejected.",
            default => "There is an update for {$label} {$reference}.",
        };

        $cta = match ($this->event) {
            self::EVENT_DOCUMENT_APPROVED, self::EVENT_DOCUMENT_REJECTED => 'View Document',
            default => 'View Approval Requests',
        };

        return [
            'subject' => $subject,
            'appName' => $appName,
            'headline' => $headline,
            'intro' => $intro,
            'cta' => $cta,
            'actionUrl' => $url,
            'logoUrl' => $logoUrl,
            'recipientName' => $recipientName,
            'event' => $this->event,
            'documentType' => $this->document->notificationDocumentType(),
            'documentLabel' => $label,
            'reference' => $reference,
            'title' => $title,
            'titleLabel' => match ($this->document->notificationDocumentType()) {
                'officialtravel', 'flight_request' => 'Purpose',
                'flight_request_issuance' => 'Business Partner',
                'overtime_request' => 'Project',
                'leave_request' => 'Leave Type',
                'recruitment_request' => 'Position',
                'room_consumption_request' => 'Meeting',
                default => 'Title',
            },
            'summary' => $this->document->notificationSummary(),
            'remarks' => $this->remarks,
            'approvalOrder' => $this->plan?->approval_order,
            'notifiableDocument' => $this->document instanceof Model ? $this->document : null,
        ];
    }

    public function resolveActionUrl(): string
    {
        if ($this->actionUrl) {
            return self::productionActionUrl($this->actionUrl);
        }

        if (in_array($this->event, [
            self::EVENT_DOCUMENT_APPROVED,
            self::EVENT_DOCUMENT_REJECTED,
        ], true)) {
            return self::productionActionUrl($this->document->notificationActionUrl());
        }

        return self::productionActionUrl(self::approvalRequestsUrl());
    }

    /**
     * Approval inbox URL used by approver / reminder CTAs.
     */
    public static function approvalRequestsUrl(): string
    {
        return route('approval.requests.index');
    }

    public static function logoUrl(): string
    {
        $base = rtrim((string) config('document_notifications.base_url'), '/');
        $path = (string) config('document_notifications.logo_path', '/images/logo_2.jpg');
        if ($path === '') {
            return '';
        }

        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return $base.'/'.ltrim($path, '/');
    }

    /**
     * Absolute filesystem path for CID embedding, or null if missing.
     */
    public static function logoFilesystemPath(): ?string
    {
        $path = (string) config('document_notifications.logo_path', '/images/logo_2.jpg');
        if ($path === '' || str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            $path = '/images/logo_2.jpg';
        }

        $full = public_path(ltrim($path, '/'));

        return is_file($full) ? $full : null;
    }

    /**
     * Resolve the display name strictly from users.name.
     */
    public static function resolveRecipientDisplayName(object $notifiable): string
    {
        if ($notifiable instanceof User) {
            $name = trim((string) ($notifiable->name ?? ''));
            if ($name !== '') {
                return $name;
            }

            return trim((string) ($notifiable->email ?? '')) ?: 'User';
        }

        $email = self::resolveNotifiableEmail($notifiable);
        if ($email !== null) {
            $user = User::query()->where('email', $email)->first();
            if ($user) {
                return self::resolveRecipientDisplayName($user);
            }
        }

        return $email ?: 'User';
    }

    public static function resolveNotifiableEmail(object $notifiable): ?string
    {
        if (isset($notifiable->email) && is_string($notifiable->email) && $notifiable->email !== '') {
            return strtolower(trim($notifiable->email));
        }

        if (! method_exists($notifiable, 'routeNotificationFor')) {
            return null;
        }

        $route = $notifiable->routeNotificationFor('mail');
        if (is_string($route) && $route !== '') {
            return strtolower(trim($route));
        }

        if (is_array($route) && $route !== []) {
            $key = array_key_first($route);
            if (is_string($key) && filter_var($key, FILTER_VALIDATE_EMAIL)) {
                return strtolower(trim($key));
            }

            $first = reset($route);
            if (is_string($first) && filter_var($first, FILTER_VALIDATE_EMAIL)) {
                return strtolower(trim($first));
            }
        }

        return null;
    }

    /**
     * Rebase a generated application URL onto the email CTA host.
     */
    public static function productionActionUrl(string $url): string
    {
        $baseUrl = rtrim((string) config('document_notifications.base_url'), '/');
        if ($baseUrl === '') {
            return $url;
        }

        $parts = parse_url($url);
        if ($parts === false) {
            return $url;
        }

        $path = $parts['path'] ?? '/';
        $appPath = parse_url((string) config('app.url'), PHP_URL_PATH);
        $appPath = is_string($appPath) ? rtrim($appPath, '/') : '';

        if ($appPath !== '' && ($path === $appPath || str_starts_with($path, $appPath.'/'))) {
            $path = substr($path, strlen($appPath)) ?: '/';
        }

        $rebased = $baseUrl.'/'.ltrim($path, '/');
        if (isset($parts['query'])) {
            $rebased .= '?'.$parts['query'];
        }
        if (isset($parts['fragment'])) {
            $rebased .= '#'.$parts['fragment'];
        }

        return $rebased;
    }

    protected function shouldAttachCc(): bool
    {
        return in_array($this->event, [
            self::EVENT_DOCUMENT_APPROVED,
            self::EVENT_DOCUMENT_REJECTED,
        ], true);
    }

    /**
     * @return array<int, string>
     */
    protected function ccAddresses(): array
    {
        $type = $this->document->notificationDocumentType();
        $list = config("document_notifications.cc.{$type}", []);
        if (! is_array($list)) {
            return [];
        }

        return array_values(array_filter(array_map(
            fn ($email) => is_string($email) ? strtolower(trim($email)) : '',
            $list
        ), fn ($email) => $email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)));
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'event' => $this->event,
            'document_type' => $this->document->notificationDocumentType(),
            'document_id' => $this->document instanceof Model ? $this->document->getKey() : null,
            'reference' => $this->document->notificationReference(),
            'approval_plan_id' => $this->plan?->id,
        ];
    }
}
