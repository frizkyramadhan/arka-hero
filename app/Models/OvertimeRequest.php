<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class OvertimeRequest extends Model
{
    use HasUuids;
    public const STATUS_DRAFT = 'draft';

    public const STATUS_PENDING = 'pending';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_FINISHED = 'finished';

    protected $fillable = [
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
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_REJECTED], true);
    }

    public function isDeletable(): bool
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
}
