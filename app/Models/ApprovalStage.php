<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalStage extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'approval_flow_id',
        'stage_name',
        'stage_order',
        'stage_type',
        'is_mandatory',
        'auto_approve_conditions',
        'escalation_hours',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_mandatory' => 'boolean',
        'auto_approve_conditions' => 'array',
        'escalation_hours' => 'integer',
    ];

    /**
     * Get the approval flow that owns this stage.
     */
    public function approvalFlow(): BelongsTo
    {
        return $this->belongsTo(ApprovalFlow::class);
    }

    /**
     * Get the approvers for this stage.
     */
    public function approvers(): HasMany
    {
        return $this->hasMany(ApprovalStageApprover::class);
    }

    /**
     * Get the document approvals currently at this stage.
     */
    public function documentApprovals(): HasMany
    {
        return $this->hasMany(DocumentApproval::class, 'current_stage_id');
    }

    /**
     * Get the approval actions for this stage.
     */
    public function approvalActions(): HasMany
    {
        return $this->hasMany(ApprovalAction::class);
    }

    /**
     * Alias for approvalActions for compatibility with views.
     */
    public function actions(): HasMany
    {
        return $this->approvalActions();
    }

    /**
     * Get the primary approvers (non-backup) for this stage.
     */
    public function primaryApprovers()
    {
        return $this->approvers()->where('is_backup', false);
    }

    /**
     * Get the backup approvers for this stage.
     */
    public function backupApprovers()
    {
        return $this->approvers()->where('is_backup', true);
    }

    /**
     * Get the next stage in the flow.
     */
    public function getNextStage()
    {
        return $this->approvalFlow->stages()
            ->where('stage_order', '>', $this->stage_order)
            ->orderBy('stage_order')
            ->first();
    }

    /**
     * Get the previous stage in the flow.
     */
    public function getPreviousStage()
    {
        return $this->approvalFlow->stages()
            ->where('stage_order', '<', $this->stage_order)
            ->orderBy('stage_order', 'desc')
            ->first();
    }

    /**
     * Check if this is the first stage in the flow.
     */
    public function isFirstStage(): bool
    {
        return $this->stage_order === 1;
    }

    /**
     * Check if this is the last stage in the flow.
     */
    public function isLastStage(): bool
    {
        $lastStage = $this->approvalFlow->getLastStage();
        return $lastStage && $this->id === $lastStage->id;
    }

    /**
     * Check if this stage is sequential.
     */
    public function isSequential(): bool
    {
        return $this->stage_type === 'sequential';
    }

    /**
     * Check if this stage is parallel.
     */
    public function isParallel(): bool
    {
        return $this->stage_type === 'parallel';
    }

    /**
     * Get all approvers for this stage (primary and backup).
     */
    public function getAllApprovers()
    {
        return $this->approvers()->orderBy('is_backup')->orderBy('id');
    }

    /**
     * Check if auto-approval conditions are met.
     */
    public function shouldAutoApprove($documentData = []): bool
    {
        if (!$this->auto_approve_conditions) {
            return false;
        }

        // Implementation for auto-approval logic based on conditions
        // This would be implemented based on specific business rules
        return false;
    }
}
