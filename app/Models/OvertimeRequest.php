<?php

namespace App\Models;

use App\Contracts\NotifiableDocument;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OvertimeRequest extends Model implements NotifiableDocument
{
    use HasUuids;

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->register_number)) {
                $model->register_number = static::generateRegisterNumber();
            }
        });
    }

    /**
     * Next number for current year, format: YYOT-xxxxx.
     */
    public static function generateRegisterNumber(): string
    {
        $year = date('y');
        $last = static::query()
            ->where('register_number', 'like', "{$year}OT-%")
            ->orderBy('register_number', 'desc')
            ->first();

        if ($last && preg_match('/\d+$/', (string) $last->register_number, $matches)) {
            $next = (int) $matches[0] + 1;
        } else {
            $next = 1;
        }

        return sprintf('%sOT-%05d', $year, $next);
    }

    public const STATUS_DRAFT = 'draft';

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_FINISHED = 'finished';

    protected $fillable = [
        'register_number',
        'project_id',
        'overtime_date',
        'status',
        'requested_by',
        'requested_at',
        'approved_at',
        'rejected_at',
        'finished_at',
        'finished_by',
        'finished_remarks',
        'manual_approvers',
        'remarks',
    ];

    protected $casts = [
        'overtime_date' => 'date',
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'finished_at' => 'datetime',
        'manual_approvers' => 'array',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function finishedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'finished_by');
    }

    public function details(): HasMany
    {
        return $this->hasMany(OvertimeRequestDetail::class)->orderBy('sort_order');
    }

    public function canBeMarkedFinishedByHr(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isFinished(): bool
    {
        return $this->status === self::STATUS_FINISHED;
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

    public function canBeEditedBy(User $user): bool
    {
        if (! $this->isEditable()) {
            return false;
        }

        if ($user->can('overtime-requests.edit')) {
            return true;
        }

        return $user->can('personal.overtime.edit-own')
            && (int) $this->requested_by === (int) $user->id;
    }

    public function canBeDeletedBy(User $user): bool
    {
        if (! $this->isDeletable()) {
            return false;
        }

        if ($user->can('overtime-requests.delete')) {
            return true;
        }

        return $user->can('personal.overtime.cancel-own')
            && (int) $this->requested_by === (int) $user->id;
    }

    public function approvalPlans(): HasMany
    {
        return $this->hasMany(ApprovalPlan::class, 'document_id')
            ->where('document_type', 'overtime_request');
    }

    public function notificationDocumentType(): string
    {
        return 'overtime_request';
    }

    public function notificationDocumentLabel(): string
    {
        return config('document_notifications.labels.overtime_request', 'Overtime Request');
    }

    public function notificationReference(): string
    {
        return $this->register_number ?: ('OT-'.$this->getKey());
    }

    public function notificationTitle(): string
    {
        $this->loadNotificationRelations();

        $project = $this->project
            ? trim(($this->project->project_code ? $this->project->project_code.' — ' : '').($this->project->project_name ?? ''))
            : null;

        return $project !== null && $project !== ''
            ? $project
            : ('Overtime '.$this->notificationReference());
    }

    /**
     * Eager-load relations used by approval-request show and email content.
     */
    public function loadNotificationRelations(): self
    {
        return $this->loadMissing([
            'project',
            'requestedBy',
            'details.administration.employee',
            'details.administration.position',
        ]);
    }

    /**
     * Summary aligned with approval-requests/show Overtime Information + Employee Details.
     *
     * @return array<string, string|null>
     */
    public function notificationSummary(): array
    {
        $this->loadNotificationRelations();

        $project = $this->project
            ? trim(($this->project->project_code ? $this->project->project_code.' — ' : '').($this->project->project_name ?? ''))
            : '—';

        $employees = $this->details
            ->map(function ($line, $index) {
                $name = $line->administration?->employee?->fullname ?? '—';
                $nik = $line->administration?->nik ?? '—';
                $timeIn = $line->time_in ? \Carbon\Carbon::parse($line->time_in)->format('H:i') : '—';
                $timeOut = $line->time_out ? \Carbon\Carbon::parse($line->time_out)->format('H:i') : '—';
                $desc = $line->work_description ?: '—';

                return sprintf(
                    '%d. %s (%s) %s–%s · %s',
                    $index + 1,
                    $name,
                    $nik,
                    $timeIn,
                    $timeOut,
                    $desc
                );
            })
            ->implode('; ');

        $summary = [
            'Register Number' => $this->notificationReference(),
            'Project' => $project !== '' ? $project : '—',
            'Overtime Date' => $this->overtime_date
                ? format_date_with_weekday($this->overtime_date)
                : '—',
            'Created By' => $this->requestedBy?->name ?: '—',
            'Created At' => $this->created_at
                ? format_datetime_with_weekday($this->created_at)
                : '—',
            'Remarks' => $this->remarks ?: '—',
            'Employees' => $employees !== '' ? $employees : 'No employee details',
        ];

        return $summary;
    }

    public function notificationRequester(): ?User
    {
        return $this->requestedBy ?? User::find($this->requested_by);
    }

    public function notificationActionUrl(): string
    {
        return route('overtime.requests.show', $this->getKey());
    }
}
