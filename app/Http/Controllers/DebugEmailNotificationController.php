<?php

namespace App\Http\Controllers;

use App\Contracts\NotifiableDocument;
use App\Models\ApprovalPlan;
use App\Models\User;
use App\Notifications\DocumentApprovalNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;

class DebugEmailNotificationController extends Controller
{
    public function __construct()
    {
        $this->middleware('role:administrator');
    }

    public function index()
    {
        $title = 'Debug Email Notifications';
        $subtitle = 'Preview document approval emails';
        $documentTypes = config('document_notifications.labels', []);
        $events = [
            DocumentApprovalNotification::EVENT_APPROVAL_REQUESTED => 'Approval requested (to approver)',
            DocumentApprovalNotification::EVENT_APPROVAL_NEEDED => 'Next approver needed',
            DocumentApprovalNotification::EVENT_APPROVAL_REMINDER => 'Approval reminder (overdue)',
            DocumentApprovalNotification::EVENT_DOCUMENT_APPROVED => 'Document approved (to requester)',
            DocumentApprovalNotification::EVENT_DOCUMENT_REJECTED => 'Document rejected (to requester)',
        ];
        $mailFrom = config('document_notifications.from');
        $notificationsEnabled = (bool) config('document_notifications.enabled', true);
        $latestDocuments = $this->latestDocumentsPreview();
        $litmusResults = session('litmus_results');

        return view('debug.email-notifications', compact(
            'title',
            'subtitle',
            'documentTypes',
            'events',
            'mailFrom',
            'notificationsEnabled',
            'latestDocuments',
            'litmusResults'
        ));
    }

    public function send(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'emails' => 'required|string',
            'document_type' => 'required|string|in:'.implode(',', array_keys(config('document_notifications.document_types', []))),
            'event' => 'required|string|in:'.implode(',', [
                DocumentApprovalNotification::EVENT_APPROVAL_REQUESTED,
                DocumentApprovalNotification::EVENT_APPROVAL_NEEDED,
                DocumentApprovalNotification::EVENT_APPROVAL_REMINDER,
                DocumentApprovalNotification::EVENT_DOCUMENT_APPROVED,
                DocumentApprovalNotification::EVENT_DOCUMENT_REJECTED,
            ]),
            'remarks' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        $emails = $this->parseEmails($request->input('emails'));
        if (empty($emails)) {
            return redirect()->back()
                ->with('toast_error', 'Enter at least one valid email address.')
                ->withInput();
        }

        $documentType = $request->input('document_type');
        $event = $request->input('event');
        $remarks = $request->input('remarks') ?: null;
        $label = config("document_notifications.labels.{$documentType}", $documentType);

        $document = $this->resolveLatestDocument($documentType);
        if (! $document instanceof NotifiableDocument) {
            return redirect()->back()
                ->with('toast_error', "No {$label} found in database. Create one first.")
                ->withInput($request->only('emails', 'event', 'remarks'));
        }

        /** @var NotifiableDocument $document */
        $notification = $this->makeNotification($document, $documentType, $event, $remarks);

        $sent = [];
        $failed = [];
        $reference = $document->notificationReference();

        foreach ($emails as $email) {
            try {
                $recipientName = User::query()
                    ->where('email', $email)
                    ->value('name') ?: $email;

                Notification::route('mail', [$email => $recipientName])
                    ->notifyNow($notification);

                $sent[] = $email;
            } catch (\Throwable $e) {
                $failed[$email] = $e->getMessage();
                Log::error('Debug email notification failed', [
                    'email' => $email,
                    'document_type' => $documentType,
                    'document_id' => method_exists($document, 'getKey') ? $document->getKey() : null,
                    'event' => $event,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $context = "{$label} {$reference} ({$event})";

        if (! empty($sent) && empty($failed)) {
            return redirect()->back()
                ->with('toast_success', "Sent {$context} to: ".implode(', ', $sent))
                ->withInput($request->only('emails', 'event', 'remarks'));
        }

        if (! empty($sent) && ! empty($failed)) {
            $failMsg = collect($failed)->map(fn ($msg, $email) => "{$email}: {$msg}")->implode('; ');

            return redirect()->back()
                ->with('toast_warning', "Partial send for {$context}. OK: ".implode(', ', $sent).'. Failed: '.$failMsg)
                ->withInput($request->only('emails', 'event', 'remarks'));
        }

        $failMsg = collect($failed)->map(fn ($msg, $email) => "{$email}: {$msg}")->implode('; ');

        return redirect()->back()
            ->with('toast_error', "Failed to send {$context}: ".$failMsg)
            ->withInput($request->only('emails', 'event', 'remarks'));
    }

    /**
     * Render the actual mail Blade in a browser without sending anything.
     */
    public function preview(Request $request)
    {
        $validated = $request->validate([
            'document_type' => 'required|string|in:'.implode(',', array_keys(config('document_notifications.document_types', []))),
            'event' => 'required|string|in:'.implode(',', [
                DocumentApprovalNotification::EVENT_APPROVAL_REQUESTED,
                DocumentApprovalNotification::EVENT_APPROVAL_NEEDED,
                DocumentApprovalNotification::EVENT_APPROVAL_REMINDER,
                DocumentApprovalNotification::EVENT_DOCUMENT_APPROVED,
                DocumentApprovalNotification::EVENT_DOCUMENT_REJECTED,
            ]),
            'remarks' => 'nullable|string|max:500',
        ]);

        $document = $this->resolveLatestDocument($validated['document_type']);
        abort_unless($document instanceof NotifiableDocument, 404, 'No document found for this notification type.');

        /** @var NotifiableDocument $notifiableDocument */
        $notifiableDocument = $document;
        $notification = $this->makeNotification(
            $notifiableDocument,
            $validated['document_type'],
            $validated['event'],
            $validated['remarks'] ?? null
        );

        $previewRecipient = $notification->plan?->approver
            ?? $notifiableDocument->notificationRequester()
            ?? (object) ['email' => 'preview@local.test'];

        return response()
            ->view('emails.documents.approval', $notification->mailViewData($previewRecipient))
            ->header('Cache-Control', 'no-store, private');
    }

    /**
     * @return array<string, array{id: mixed, reference: string, title: string, status: string|null, created_at: string|null}|null>
     */
    protected function latestDocumentsPreview(): array
    {
        $preview = [];

        foreach (array_keys(config('document_notifications.document_types', [])) as $type) {
            $document = $this->resolveLatestDocument($type);
            if (! $document instanceof NotifiableDocument) {
                $preview[$type] = null;

                continue;
            }

            $preview[$type] = [
                'id' => method_exists($document, 'getKey') ? $document->getKey() : null,
                'reference' => $document->notificationReference(),
                'title' => $document->notificationTitle(),
                'status' => $document->status ?? null,
                'created_at' => isset($document->created_at)
                    ? optional($document->created_at)->format('d M Y H:i')
                    : null,
            ];
        }

        return $preview;
    }

    protected function resolveLatestDocument(string $documentType): ?Model
    {
        $class = config("document_notifications.document_types.{$documentType}");
        if (! $class || ! class_exists($class)) {
            return null;
        }

        $with = $this->eagerLoadsFor($documentType);

        return $class::query()
            ->when(! empty($with), fn ($q) => $q->with($with))
            ->latest('created_at')
            ->first();
    }

    protected function makeNotification(
        NotifiableDocument $document,
        string $documentType,
        string $event,
        ?string $remarks
    ): DocumentApprovalNotification {
        $plan = null;
        $documentId = $document instanceof Model ? $document->getKey() : null;

        if (in_array($event, [
            DocumentApprovalNotification::EVENT_APPROVAL_REQUESTED,
            DocumentApprovalNotification::EVENT_APPROVAL_NEEDED,
            DocumentApprovalNotification::EVENT_APPROVAL_REMINDER,
        ], true)) {
            $plan = $documentId !== null
                ? $this->resolveRelatedApprovalPlan($documentType, $documentId)
                : null;
        }

        $notification = new DocumentApprovalNotification(
            $document,
            $event,
            $plan,
            $remarks,
            null,
            true
        );
        $notification->actionUrl = $notification->resolveActionUrl();

        return $notification;
    }

    /**
     * Render-only assertions for all document types × events (no SMTP).
     */
    public function litmus()
    {
        $baseHost = parse_url((string) config('document_notifications.base_url'), PHP_URL_HOST);
        $events = [
            DocumentApprovalNotification::EVENT_APPROVAL_REQUESTED,
            DocumentApprovalNotification::EVENT_APPROVAL_NEEDED,
            DocumentApprovalNotification::EVENT_APPROVAL_REMINDER,
            DocumentApprovalNotification::EVENT_DOCUMENT_APPROVED,
            DocumentApprovalNotification::EVENT_DOCUMENT_REJECTED,
        ];

        $results = [];
        $probeUser = User::query()->whereNotNull('name')->where('name', '!=', '')->first()
            ?? (object) ['name' => 'Litmus Probe', 'email' => 'litmus@local.test'];

        foreach (array_keys(config('document_notifications.document_types', [])) as $documentType) {
            $document = $this->resolveLatestDocument($documentType);
            if (! $document instanceof NotifiableDocument) {
                $results[] = [
                    'document_type' => $documentType,
                    'event' => '—',
                    'pass' => false,
                    'checks' => ['document' => 'FAIL: no document in DB'],
                ];

                continue;
            }

            foreach ($events as $event) {
                $checks = [];
                try {
                    $notification = $this->makeNotification($document, $documentType, $event, 'Litmus remarks');
                    $data = $notification->mailViewData($probeUser);
                    $html = view('emails.documents.approval', $data)->render();
                    $text = view('emails.documents.approval-text', $data)->render();

                    $ctaHost = parse_url((string) $data['actionUrl'], PHP_URL_HOST);
                    $checks['cta_host'] = ($ctaHost === $baseHost) ? 'OK' : "FAIL: {$ctaHost}";

                    $path = parse_url((string) $data['actionUrl'], PHP_URL_PATH) ?: '';
                    if (in_array($event, [
                        DocumentApprovalNotification::EVENT_DOCUMENT_APPROVED,
                        DocumentApprovalNotification::EVENT_DOCUMENT_REJECTED,
                    ], true)) {
                        $checks['cta_path'] = (! str_ends_with(rtrim($path, '/'), '/approval/requests'))
                            ? 'OK'
                            : 'FAIL: expected document URL';
                        $checks['cta_label'] = ($data['cta'] === 'View Document') ? 'OK' : 'FAIL';
                    } else {
                        $checks['cta_path'] = str_contains($path, '/approval/requests') ? 'OK' : "FAIL: {$path}";
                        $checks['cta_label'] = ($data['cta'] === 'View Approval Requests') ? 'OK' : 'FAIL';
                    }

                    $expectedName = DocumentApprovalNotification::resolveRecipientDisplayName($probeUser);
                    $checks['dear_name'] = str_contains($html, 'Dear '.$expectedName)
                        ? 'OK'
                        : 'FAIL: Dear '.$expectedName;
                    $checks['reference'] = str_contains($html, $document->notificationReference()) ? 'OK' : 'FAIL';
                    $checks['plain_text'] = trim($text) !== '' ? 'OK' : 'FAIL: empty';
                    $checks['logo'] = (! empty($data['logoUrl']) && str_contains($html, $data['logoUrl'])) ? 'OK' : 'FAIL';
                } catch (\Throwable $e) {
                    $checks['exception'] = 'FAIL: '.$e->getMessage();
                }

                $pass = collect($checks)->every(fn ($v) => str_starts_with((string) $v, 'OK'));
                $results[] = [
                    'document_type' => $documentType,
                    'event' => $event,
                    'pass' => $pass,
                    'checks' => $checks,
                ];
            }
        }

        return redirect()
            ->route('debug.email-notifications.index')
            ->with('litmus_results', $results)
            ->with('toast_success', 'Litmus completed: '.collect($results)->where('pass', true)->count().'/'.count($results).' passed.');
    }

    /**
     * Prefer the approval step that can currently be processed.
     */
    protected function resolveRelatedApprovalPlan(string $documentType, string|int $documentId): ?ApprovalPlan
    {
        $pending = ApprovalPlan::query()
            ->where('document_type', $documentType)
            ->where('document_id', $documentId)
            ->where('is_open', true)
            ->where('status', 0)
            ->orderBy('approval_order')
            ->get()
            ->first(fn (ApprovalPlan $plan) => $plan->canBeProcessed());

        return $pending ?: ApprovalPlan::query()
            ->where('document_type', $documentType)
            ->where('document_id', $documentId)
            ->latest('id')
            ->first();
    }

    /**
     * @return array<int, string>
     */
    protected function eagerLoadsFor(string $documentType): array
    {
        return match ($documentType) {
            'leave_request' => [
                'employee.administrations.position.department',
                'employee.administrations.project',
                'administration.position.department',
                'administration.project',
                'leaveType',
                'requestedBy',
            ],
            'officialtravel' => [
                'stops',
                'traveler.employee',
                'traveler.position.department',
                'traveler.project',
                'transportation',
                'accommodation',
                'details.follower.employee',
                'details.follower.position',
            ],
            'recruitment_request' => [
                'department',
                'project',
                'position',
                'level',
                'createdBy',
            ],
            'flight_request' => [
                'employee',
                'administration.position.department',
                'administration.project',
                'details',
                'leaveRequest.employee',
                'leaveRequest.administration',
                'officialTravel.traveler.employee',
                'officialTravel.stops',
                'officialTravel.details.follower.employee',
                'officialTravel.details.follower.position.department',
                'officialTravel.details.follower.project',
                'followers.employee',
                'followers.administration.position.department',
                'followers.administration.project',
            ],
            'flight_request_issuance' => [
                'businessPartner',
                'issuedBy',
                'issuanceDetails.employee',
            ],
            'overtime_request' => [
                'project',
                'requestedBy',
                'details.administration.employee',
                'details.administration.position',
            ],
            'room_consumption_request' => [
                'project',
                'meetingRoom',
                'department',
                'requestedBy',
                'items',
            ],
            'supply_order' => [
                'project',
                'department',
                'requestedBy',
                'items.item',
            ],
            default => [],
        };
    }

    /**
     * @return array<int, string>
     */
    protected function parseEmails(string $raw): array
    {
        $parts = preg_split('/[\s,;]+/', $raw) ?: [];
        $emails = [];

        foreach ($parts as $part) {
            $email = strtolower(trim($part));
            if ($email !== '' && filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $emails[$email] = $email;
            }
        }

        return array_values($emails);
    }
}
