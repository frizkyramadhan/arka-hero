<?php

namespace App\Models;

use App\Contracts\NotifiableDocument;
use App\Traits\HasLetterNumber;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RoomConsumptionRequest extends Model implements NotifiableDocument
{
    use HasLetterNumber;
    use HasUuids;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_COMPLETED = 'completed';

    public const CONSUMPTION_TYPES = [
        'coffee_break_morning' => 'Coffee Break Pagi',
        'coffee_break_afternoon' => 'Coffee Break Sore',
        'lunch' => 'Lunch',
        'dinner' => 'Dinner',
    ];

    protected $fillable = [
        'letter_number_id',
        'letter_number',
        'request_number',
        'meeting_room_id',
        'project_id',
        'department_id',
        'requested_by',
        'meeting_title',
        'start_date',
        'end_date',
        'start_time',
        'end_time',
        'attendees_count',
        'facilities',
        'need_zoom',
        'manual_approvers',
        'status',
        'submitted_by_user',
        'submitted_at',
        'approved_at',
        'rejected_at',
        'cancelled_at',
        'completed_at',
        'rejection_reason',
        'notes',
        'it_wo_id',
        'it_wo_number',
        'zoom_meeting_id',
        'zoom_topic',
        'zoom_join_url',
        'zoom_passcode',
        'zoom_sync_status',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'completed_at' => 'datetime',
        'manual_approvers' => 'array',
        'need_zoom' => 'boolean',
        'submitted_by_user' => 'boolean',
        'attendees_count' => 'integer',
    ];

    protected function getDocumentType(): string
    {
        return 'room_consumption_request';
    }

    /**
     * Format Reg. No: 0001/HCS-000H/RCR/I/2026 (roman month/year from start_date).
     */
    public static function formatRequestNumber(string $letterNumberString, string $projectCode, $startDate): string
    {
        $numericPart = $letterNumberString;
        if (str_starts_with(strtoupper($letterNumberString), 'RCR')) {
            $numericPart = substr($letterNumberString, 3);
        }
        $numericPart = str_pad((int) $numericPart, 4, '0', STR_PAD_LEFT);

        $date = $startDate instanceof Carbon
            ? $startDate
            : Carbon::parse($startDate);

        $romanMonth = self::monthToRoman((int) $date->format('n'));

        return sprintf(
            '%s/HCS-%s/RCR/%s/%s',
            $numericPart,
            $projectCode,
            $romanMonth,
            $date->format('Y')
        );
    }

    /**
     * Display start–end date range (single day collapses to one date).
     */
    public function formattedMeetingDateRange(bool $withWeekday = true): string
    {
        if (! $this->start_date) {
            return '—';
        }

        $start = $withWeekday
            ? format_date_with_weekday($this->start_date)
            : $this->start_date->format('d/m/Y');

        if (! $this->end_date || $this->start_date->equalTo($this->end_date)) {
            return $start;
        }

        $end = $withWeekday
            ? format_date_with_weekday($this->end_date)
            : $this->end_date->format('d/m/Y');

        return $start.' – '.$end;
    }

    /**
     * Compact HTML for tables: one line if same day, otherwise start / end on two lines.
     */
    public function formattedMeetingDateRangeHtml(): string
    {
        if (! $this->start_date) {
            return '—';
        }

        $formatDay = static function ($date): string {
            return $date->copy()
                ->locale(app()->getLocale())
                ->translatedFormat('l, d M Y');
        };

        $start = e($formatDay($this->start_date));

        if (! $this->end_date || $this->start_date->equalTo($this->end_date)) {
            return '<span class="text-nowrap">'.$start.'</span>';
        }

        $end = e($formatDay($this->end_date));

        return '<span class="d-inline-block text-left" style="line-height:1.35">'
            .$start
            .'<br><span class="text-muted">– '.$end.'</span>'
            .'</span>';
    }

    public static function monthToRoman(int $month): string
    {
        $map = [
            1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV', 5 => 'V', 6 => 'VI',
            7 => 'VII', 8 => 'VIII', 9 => 'IX', 10 => 'X', 11 => 'XI', 12 => 'XII',
        ];

        return $map[$month] ?? (string) $month;
    }

    /**
     * User submission from My Room & Consumption awaiting HR letter number (REQxxxxx placeholder).
     */
    public function isPendingHr(): bool
    {
        return (bool) $this->submitted_by_user
            && empty($this->letter_number_id)
            && $this->status === self::STATUS_DRAFT;
    }

    public function usesTemporaryRegNumber(): bool
    {
        return is_string($this->request_number)
            && preg_match('/^REQ\d+$/', $this->request_number) === 1;
    }

    public function meetingRoom(): BelongsTo
    {
        return $this->belongsTo(MeetingRoom::class, 'meeting_room_id');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function requestedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function items(): HasMany
    {
        $order = array_keys(self::CONSUMPTION_TYPES);
        $placeholders = implode(',', array_fill(0, count($order), '?'));

        return $this->hasMany(RoomConsumptionItem::class, 'request_id')
            ->orderByRaw("FIELD(consumption_type, {$placeholders})", $order);
    }

    public function approvalPlans(): HasMany
    {
        return $this->hasMany(ApprovalPlan::class, 'document_id')
            ->where('document_type', 'room_consumption_request');
    }

    public function isEditable(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function isDeletable(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function canSubmitForApproval(): bool
    {
        return $this->isEditable();
    }

    public function canCancel(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_SUBMITTED, self::STATUS_APPROVED], true);
    }

    public function canBeEditedBy(?User $user): bool
    {
        if (! $user || ! $this->isEditable()) {
            return false;
        }

        if ($user->can('room-consumption-requests.edit')) {
            return true;
        }

        return $user->can('personal.room-consumption.edit-own')
            && (int) $this->requested_by === (int) $user->id
            && $this->isPendingHr();
    }

    public function canBeDeletedBy(?User $user): bool
    {
        if (! $user || ! $this->isDeletable()) {
            return false;
        }

        if ($user->can('room-consumption-requests.delete')) {
            return true;
        }

        return $user->can('personal.room-consumption.cancel-own')
            && (int) $this->requested_by === (int) $user->id
            && $this->isPendingHr();
    }

    public function canRequestZoomItWo(): bool
    {
        if (! $this->need_zoom) {
            return false;
        }

        if (! in_array($this->status, [self::STATUS_SUBMITTED, self::STATUS_APPROVED], true)) {
            return false;
        }

        if (in_array($this->zoom_sync_status, ['completed', 'done', 'synced'], true)) {
            return false;
        }

        return empty($this->it_wo_id) || in_array($this->zoom_sync_status, ['failed', 'error'], true);
    }

    public function canSyncZoomItWo(): bool
    {
        return $this->need_zoom && ! empty($this->it_wo_id);
    }

    public function hasZoomItWoDebugState(): bool
    {
        if (! $this->need_zoom) {
            return false;
        }

        return ! empty($this->it_wo_id)
            || ! empty($this->it_wo_number)
            || ! empty($this->zoom_meeting_id)
            || ! empty($this->zoom_topic)
            || ! empty($this->zoom_join_url)
            || ! empty($this->zoom_passcode)
            || in_array($this->zoom_sync_status, ['open', 'processing', 'completed', 'done', 'synced', 'failed', 'error'], true);
    }

    public function notificationDocumentType(): string
    {
        return 'room_consumption_request';
    }

    public function notificationDocumentLabel(): string
    {
        return config('document_notifications.labels.room_consumption_request', 'Room & Consumption Request');
    }

    public function notificationReference(): string
    {
        return $this->letter_number
            ?: ($this->request_number ?: ('RCR-'.$this->getKey()));
    }

    public function notificationTitle(): string
    {
        return (string) ($this->meeting_title ?: $this->notificationReference());
    }

    public function notificationSummary(): array
    {
        return [
            'Meeting' => $this->meeting_title,
            'Room' => $this->meetingRoom->name ?? '—',
            'Start' => optional($this->start_date)->format('d M Y').' '.$this->start_time,
            'End' => optional($this->end_date)->format('d M Y').' '.$this->end_time,
            'Attendees' => (string) $this->attendees_count,
            'Status' => $this->status,
        ];
    }

    public function notificationRequester(): ?User
    {
        return $this->requestedBy ?? User::find($this->requested_by);
    }

    public function notificationActionUrl(): string
    {
        return route('room-consumption-requests.show', $this->getKey());
    }
}
