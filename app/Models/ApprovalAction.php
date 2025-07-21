<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalAction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'document_approval_id',
        'approval_stage_id',
        'approver_id',
        'action',
        'comments',
        'action_date',
        'forwarded_to',
        'delegated_to',
        'is_automatic',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'action_date' => 'datetime',
        'is_automatic' => 'boolean',
        'metadata' => 'array',
    ];

    /**
     * Get the document approval for this action.
     */
    public function documentApproval(): BelongsTo
    {
        return $this->belongsTo(DocumentApproval::class);
    }

    /**
     * Get the approval stage for this action.
     */
    public function approvalStage(): BelongsTo
    {
        return $this->belongsTo(ApprovalStage::class);
    }

    /**
     * Get the user who performed this action.
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approver_id');
    }

    /**
     * Get the user this action was forwarded to.
     */
    public function forwardedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'forwarded_to');
    }

    /**
     * Get the user this action was delegated to.
     */
    public function delegatedTo(): BelongsTo
    {
        return $this->belongsTo(User::class, 'delegated_to');
    }

    /**
     * Check if this action is an approval.
     */
    public function isApproved(): bool
    {
        return $this->action === 'approved';
    }

    /**
     * Check if this action is a rejection.
     */
    public function isRejected(): bool
    {
        return $this->action === 'rejected';
    }

    /**
     * Check if this action is a forward.
     */
    public function isForwarded(): bool
    {
        return $this->action === 'forwarded';
    }

    /**
     * Check if this action is a delegation.
     */
    public function isDelegated(): bool
    {
        return $this->action === 'delegated';
    }

    /**
     * Check if this action was automatic.
     */
    public function isAutomatic(): bool
    {
        return $this->is_automatic;
    }

    /**
     * Check if this action was manual.
     */
    public function isManual(): bool
    {
        return !$this->is_automatic;
    }

    /**
     * Get the action description for display.
     */
    public function getActionDescription(): string
    {
        $descriptions = [
            'approved' => 'Approved',
            'rejected' => 'Rejected',
            'forwarded' => 'Forwarded',
            'delegated' => 'Delegated',
        ];

        return $descriptions[$this->action] ?? ucfirst($this->action);
    }

    /**
     * Get the target user for forward/delegate actions.
     */
    public function getTargetUser()
    {
        if ($this->isForwarded()) {
            return $this->forwardedTo;
        }

        if ($this->isDelegated()) {
            return $this->delegatedTo;
        }

        return null;
    }

    /**
     * Scope a query to only include approval actions.
     */
    public function scopeApproved($query)
    {
        return $query->where('action', 'approved');
    }

    /**
     * Scope a query to only include rejection actions.
     */
    public function scopeRejected($query)
    {
        return $query->where('action', 'rejected');
    }

    /**
     * Scope a query to only include forward actions.
     */
    public function scopeForwarded($query)
    {
        return $query->where('action', 'forwarded');
    }

    /**
     * Scope a query to only include delegation actions.
     */
    public function scopeDelegated($query)
    {
        return $query->where('action', 'delegated');
    }

    /**
     * Scope a query to only include automatic actions.
     */
    public function scopeAutomatic($query)
    {
        return $query->where('is_automatic', true);
    }

    /**
     * Scope a query to only include manual actions.
     */
    public function scopeManual($query)
    {
        return $query->where('is_automatic', false);
    }
}
