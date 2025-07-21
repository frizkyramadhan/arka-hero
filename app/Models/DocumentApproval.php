<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DocumentApproval extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'document_type',
        'document_id',
        'approval_flow_id',
        'current_stage_id',
        'overall_status',
        'submitted_by',
        'submitted_at',
        'completed_at',
        'metadata',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'submitted_at' => 'datetime',
        'completed_at' => 'datetime',
        'metadata' => 'array',
    ];

    /**
     * Get the approval flow for this document approval.
     */
    public function approvalFlow(): BelongsTo
    {
        return $this->belongsTo(ApprovalFlow::class);
    }

    /**
     * Get the approval flow for this document approval (alias).
     */
    public function flow(): BelongsTo
    {
        return $this->belongsTo(ApprovalFlow::class, 'approval_flow_id');
    }

    /**
     * Get the current stage of this approval.
     */
    public function currentStage(): BelongsTo
    {
        return $this->belongsTo(ApprovalStage::class, 'current_stage_id');
    }

    /**
     * Get the user who submitted this approval.
     */
    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    /**
     * Get the approval actions for this document approval.
     */
    public function approvalActions(): HasMany
    {
        return $this->hasMany(ApprovalAction::class);
    }

    /**
     * Get the approval actions for this document approval (alias).
     */
    public function actions(): HasMany
    {
        return $this->hasMany(ApprovalAction::class);
    }

    /**
     * Get the notifications for this document approval.
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(ApprovalNotification::class);
    }

    /**
     * Get the actual document model.
     */
    public function getDocument()
    {
        $modelClass = $this->getDocumentModelClass();
        if (!$modelClass) {
            return null;
        }

        return $modelClass::find($this->document_id);
    }

    /**
     * Get the document model class based on document type.
     */
    public function getDocumentModelClass()
    {
        $modelMap = [
            'officialtravel' => Officialtravel::class,
            'recruitment_request' => RecruitmentRequest::class,
            'employee_registration' => EmployeeRegistration::class,
        ];

        return $modelMap[$this->document_type] ?? null;
    }

    /**
     * Check if this approval is pending.
     */
    public function isPending(): bool
    {
        return $this->overall_status === 'pending';
    }

    /**
     * Check if this approval is approved.
     */
    public function isApproved(): bool
    {
        return $this->overall_status === 'approved';
    }

    /**
     * Check if this approval is rejected.
     */
    public function isRejected(): bool
    {
        return $this->overall_status === 'rejected';
    }

    /**
     * Check if this approval is cancelled.
     */
    public function isCancelled(): bool
    {
        return $this->overall_status === 'cancelled';
    }

    /**
     * Check if this approval is completed (approved or rejected).
     */
    public function isCompleted(): bool
    {
        return in_array($this->overall_status, ['approved', 'rejected']);
    }

    /**
     * Get the next stage for this approval.
     */
    public function getNextStage()
    {
        if (!$this->currentStage) {
            return $this->approvalFlow->getFirstStage();
        }

        return $this->currentStage->getNextStage();
    }

    /**
     * Get the previous stage for this approval.
     */
    public function getPreviousStage()
    {
        if (!$this->currentStage) {
            return null;
        }

        return $this->currentStage->getPreviousStage();
    }

    /**
     * Get the current approvers for this approval.
     */
    public function getCurrentApprovers()
    {
        if (!$this->currentStage) {
            return collect();
        }

        return $this->currentStage->getAllApprovers();
    }

    /**
     * Get the approval history for this document.
     */
    public function getApprovalHistory()
    {
        return $this->approvalActions()->with(['approver', 'approvalStage'])->orderBy('action_date');
    }

    /**
     * Get the latest approval action.
     */
    public function getLatestAction()
    {
        return $this->approvalActions()->latest('action_date')->first();
    }

    /**
     * Check if a specific user can approve this document.
     */
    public function canUserApprove(User $user): bool
    {
        if (!$this->currentStage) {
            return false;
        }

        foreach ($this->currentStage->approvers as $approver) {
            if ($approver->canUserApprove($user)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Scope a query to only include pending approvals.
     */
    public function scopePending($query)
    {
        return $query->where('overall_status', 'pending');
    }

    /**
     * Scope a query to only include approved documents.
     */
    public function scopeApproved($query)
    {
        return $query->where('overall_status', 'approved');
    }

    /**
     * Scope a query to only include rejected documents.
     */
    public function scopeRejected($query)
    {
        return $query->where('overall_status', 'rejected');
    }

    /**
     * Scope a query to only include completed approvals.
     */
    public function scopeCompleted($query)
    {
        return $query->whereIn('overall_status', ['approved', 'rejected']);
    }

    /**
     * Scope a query to only include approvals for a specific document type.
     */
    public function scopeForDocumentType($query, string $documentType)
    {
        return $query->where('document_type', $documentType);
    }
}
