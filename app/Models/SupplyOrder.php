<?php

namespace App\Models;

use App\Contracts\NotifiableDocument;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplyOrder extends Model implements NotifiableDocument
{
    use HasUuids;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_CLOSED = 'closed';

    public const NUMBER_PREFIX = 'ORD';

    protected $fillable = [
        'order_number',
        'order_sequence',
        'project_id',
        'administration_id',
        'department_id',
        'requested_by',
        'order_date',
        'manual_approvers',
        'status',
        'submitted_at',
        'approved_at',
        'rejected_at',
        'cancelled_at',
        'closed_at',
        'rejection_reason',
        'notes',
    ];

    protected $casts = [
        'manual_approvers' => 'array',
        'order_date' => 'date',
        'submitted_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'closed_at' => 'datetime',
        'order_sequence' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function administration(): BelongsTo
    {
        return $this->belongsTo(Administration::class);
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
        return $this->hasMany(SupplyOrderItem::class, 'supply_order_id');
    }

    public function stockIns(): HasMany
    {
        return $this->hasMany(SupplyStockIn::class, 'supply_order_id');
    }

    public function approvalPlans(): HasMany
    {
        return $this->hasMany(ApprovalPlan::class, 'document_id')
            ->where('document_type', 'supply_order');
    }

    public static function allocateNumber(int $projectId, string $projectCode): array
    {
        $seq = (int) static::query()
            ->where('project_id', $projectId)
            ->lockForUpdate()
            ->max('order_sequence') + 1;

        return [
            'order_sequence' => $seq,
            'order_number' => self::formatNumber($projectCode, $seq),
        ];
    }

    public static function previewNumber(int $projectId, string $projectCode): string
    {
        $seq = (int) static::query()
            ->where('project_id', $projectId)
            ->max('order_sequence') + 1;

        return self::formatNumber($projectCode, $seq);
    }

    protected static function formatNumber(string $projectCode, int $sequence): string
    {
        return sprintf(
            '%s-%s-%s',
            self::NUMBER_PREFIX,
            $projectCode,
            str_pad((string) $sequence, 4, '0', STR_PAD_LEFT)
        );
    }

    public function isEditable(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function canSubmitForApproval(): bool
    {
        return $this->isEditable() && $this->items()->exists();
    }

    public function canCancel(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_SUBMITTED], true);
    }

    public function canClose(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function canReceive(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function canBeEditedBy(?User $user): bool
    {
        if (! $user || ! $this->isEditable()) {
            return false;
        }

        if ($user->can('supplies.orders.edit')) {
            return true;
        }

        return $user->can('personal.supplies.orders.edit-own')
            && (int) $this->requested_by === (int) $user->id;
    }

    public function canBeViewedBy(?User $user): bool
    {
        if (! $user) {
            return false;
        }

        if ($user->can('supplies.orders.show')) {
            return true;
        }

        return $user->can('personal.supplies.orders.view-own')
            && (int) $this->requested_by === (int) $user->id;
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_APPROVED => 'success',
            self::STATUS_SUBMITTED => 'info',
            self::STATUS_REJECTED, self::STATUS_CANCELLED => 'danger',
            self::STATUS_CLOSED => 'secondary',
            default => 'warning',
        };
    }

    public function statusLabel(): string
    {
        return ucfirst($this->status);
    }

    public function notificationDocumentType(): string
    {
        return 'supply_order';
    }

    public function notificationDocumentLabel(): string
    {
        return config('document_notifications.labels.supply_order', 'Supply Order');
    }

    public function notificationReference(): string
    {
        return $this->order_number ?: ('ORD-'.$this->getKey());
    }

    public function notificationTitle(): string
    {
        return $this->notificationReference();
    }

    public function loadNotificationRelations(): self
    {
        return $this->loadMissing(['project', 'department', 'requestedBy', 'items.item']);
    }

    public function notificationSummary(): array
    {
        $this->loadNotificationRelations();

        $lines = $this->items->map(function (SupplyOrderItem $line) {
            $name = $line->item->name ?? 'Item';
            $code = $line->item->code ?? '';

            return trim($code.' '.$name).' × '.$line->quantity_ordered;
        })->implode('; ');

        $summary = [
            'Order No' => $this->notificationReference(),
            'Project' => trim(($this->project->project_code ?? '—').' — '.($this->project->project_name ?? '')),
            'Date' => $this->order_date?->format('d/m/Y') ?: '—',
            'Department' => $this->department->department_name ?? '—',
            'Requester' => $this->requestedBy?->name ?: '—',
            'Items' => $lines !== '' ? $lines : '—',
        ];

        if (filled($this->notes)) {
            $summary['Notes'] = $this->notes;
        }

        return $summary;
    }

    public function notificationRequester(): ?User
    {
        return $this->requestedBy ?? User::find($this->requested_by);
    }

    public function notificationActionUrl(): string
    {
        return route('supplies.orders.show', $this->getKey());
    }
}
