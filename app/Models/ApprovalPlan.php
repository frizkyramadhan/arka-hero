<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class ApprovalPlan extends Model
{
    use HasFactory;

    // protected $guarded = [];

    protected $fillable = [
        'document_id',
        'document_type',
        'approver_id',
        'status',
        'remarks',
        'is_open',
        'is_read',
        'approval_order'
    ];

    protected $casts = [
        'is_open' => 'boolean',
        'is_read' => 'boolean',
        'approval_order' => 'integer'
    ];

    public function approver()
    {
        return $this->belongsTo(User::class, 'approver_id')->withDefault([
            'name' => 'Not Available',
        ]);
    }

    // Relationship ke ApprovalStage untuk mendapatkan order dan sequential info
    public function approvalStage()
    {
        return $this->belongsTo(\App\Models\ApprovalStage::class, 'approver_id', 'approver_id')
            ->where('document_type', $this->document_type);
    }

    public function officialtravel()
    {
        return $this->belongsTo(Officialtravel::class, 'document_id', 'id');
    }

    public function leaveRequest()
    {
        return $this->belongsTo(LeaveRequest::class, 'document_id', 'id');
    }

    public function recruitmentRequest()
    {
        return $this->belongsTo(RecruitmentRequest::class, 'document_id', 'id');
    }

    /**
     * Check if this approval can be processed (hybrid sequential validation)
     *
     * This method uses a hybrid approach that:
     * - Supports parallel approvals (multiple approvers with same approval_order)
     * - Requires ALL approvals in previous order groups to be completed
     * - Works with missing, skipped, or non-sequential approval orders
     *
     * Examples:
     * - Sequential: 1 → 2 → 3
     * - Parallel: 1,1 → 3 (both order 1 must approve before order 3)
     * - Missing start: 2 → 3 (order 2 becomes first order)
     * - Skipped: 1 → 3 → 5 (no order 2 or 4)
     * - Mixed: 1,1 → 2,2 → 3 (multiple parallel groups)
     *
     * @return bool True if this approval can be processed, false otherwise
     */
    public function canBeProcessed()
    {
        // If approval_order is null or empty, allow processing (fallback for legacy data)
        if (empty($this->approval_order)) {
            return true;
        }

        // Get all approval plans for this document, ordered by approval_order
        $allApprovals = static::where('document_id', $this->document_id)
            ->where('document_type', $this->document_type)
            ->orderBy('approval_order')
            ->get();

        // If no approvals found, allow processing (shouldn't happen but safe fallback)
        if ($allApprovals->isEmpty()) {
            return true;
        }

        // Group approvals by approval_order
        $orderGroups = $allApprovals->groupBy('approval_order');

        // Get current approval order
        $currentOrder = $this->approval_order;

        // If this is the first order group, allow processing
        $firstOrder = $orderGroups->keys()->first();
        if ($currentOrder == $firstOrder) {
            return true;
        }

        // Check if ALL previous order groups are fully approved
        foreach ($orderGroups as $order => $approvals) {
            // Skip current and future orders
            if ($order >= $currentOrder) {
                break;
            }

            // Check if ALL approvals in this order group are approved
            $allApproved = $approvals->every(function ($approval) {
                return $approval->status == 1; // Status 1 = Approved
            });

            // If any previous order group is not fully approved, cannot process
            if (!$allApproved) {
                return false;
            }
        }

        // All previous order groups are fully approved
        return true;
    }

    // Get next approval in sequence
    public function getNextApproval()
    {
        return static::where('document_id', $this->document_id)
            ->where('document_type', $this->document_type)
            ->where('approval_order', $this->approval_order + 1)
            ->first();
    }

    // Check if this is the last approval in sequence
    public function isLastApproval()
    {
        $maxOrder = static::where('document_id', $this->document_id)
            ->where('document_type', $this->document_type)
            ->max('approval_order');

        return $this->approval_order >= $maxOrder;
    }
}
