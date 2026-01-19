<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;

class LeaveRequest extends Model
{
    use HasFactory, Uuids;

    protected $fillable = [
        'employee_id',
        'administration_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'back_to_work_date',
        'reason',
        'lsl_taken_days',
        'lsl_cashout_days',
        'total_days',
        'status',
        'leave_period',
        'requested_at',
        'requested_by',
        'supporting_document',
        'auto_conversion_at',
        'approved_at',
        'closed_at',
        'closed_by',
        'batch_id',
        'is_batch_request',
        'bulk_notes',
        'manual_approvers'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'back_to_work_date' => 'date',
        'requested_at' => 'datetime',
        'auto_conversion_at' => 'datetime',
        'approved_at' => 'datetime',
        'closed_at' => 'datetime',
        'is_batch_request' => 'boolean',
        'manual_approvers' => 'array'
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function administration()
    {
        return $this->belongsTo(Administration::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function rosterAdjustments()
    {
        return $this->hasMany(RosterAdjustment::class);
    }

    public function approvalPlans()
    {
        return $this->hasMany(ApprovalPlan::class, 'document_id')
            ->where('document_type', 'leave_request');
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function closedBy()
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function cancellations()
    {
        return $this->hasMany(LeaveRequestCancellation::class);
    }

    // Business Logic Methods
    public function calculateTotalDays()
    {
        if ($this->start_date && $this->end_date) {
            $this->total_days = $this->start_date->diffInDays($this->end_date) + 1;
            return $this->total_days;
        }
        return 0;
    }

    public function isPending()
    {
        return $this->status === 'pending';
    }

    public function isApproved()
    {
        return $this->status === 'approved';
    }

    public function isRejected()
    {
        return $this->status === 'rejected';
    }

    public function isCancelled()
    {
        return $this->status === 'cancelled';
    }

    public function isClosed()
    {
        return $this->status === 'closed';
    }

    /**
     * Close this leave request
     * Can be used by HR to mark leave request as closed/completed
     */
    public function close($closedBy)
    {
        $this->status = 'closed';
        $this->closed_at = now();
        $this->closed_by = $closedBy;
        $this->save();
    }

    /**
     * Request cancellation for this leave request
     * Employee can request to cancel part or all of their approved leave
     */
    public function requestCancellation($daysToCancel, $reason, $requestedBy)
    {
        // Validate that cancellation request is valid
        if ($daysToCancel > $this->total_days) {
            throw new \InvalidArgumentException('Days to cancel cannot exceed total leave days');
        }

        if ($this->status !== 'approved') {
            throw new \InvalidArgumentException('Only approved leave requests can be cancelled');
        }

        // Check if there's already a pending cancellation request
        $existingCancellation = $this->cancellations()
            ->where('status', 'pending')
            ->first();

        if ($existingCancellation) {
            throw new \InvalidArgumentException('There is already a pending cancellation request for this leave');
        }

        // Create cancellation request
        return LeaveRequestCancellation::create([
            'leave_request_id' => $this->id,
            'days_to_cancel' => $daysToCancel,
            'reason' => $reason,
            'status' => 'pending',
            'requested_by' => $requestedBy,
            'requested_at' => now()
        ]);
    }

    /**
     * Check if this leave request can be closed
     */
    public function canBeClosed()
    {
        return $this->status === 'approved' && $this->end_date <= now()->addDay();
    }

    /**
     * Check if this leave request can be cancelled
     * Can be cancelled if:
     * 1. Status is approved
     * 2. Not already closed
     * 3. No pending cancellation request exists
     * 4. Can cancel partial days (even if leave has started)
     */
    public function canBeCancelled()
    {
        return $this->status === 'approved' &&
            $this->status !== 'closed' &&
            !$this->cancellations()->where('status', 'pending')->exists();
    }

    /**
     * Set auto conversion date for paid leave requests without supporting document
     */
    public function setAutoConversionDate()
    {
        if ($this->leaveType && $this->leaveType->category === 'paid' && !$this->supporting_document) {
            $this->auto_conversion_at = $this->created_at->addDays(12);
            $this->save();
        }
    }

    /**
     * Clear auto conversion date when supporting document is uploaded
     */
    public function clearAutoConversionDate()
    {
        if ($this->supporting_document && $this->auto_conversion_at) {
            $this->auto_conversion_at = null;
            $this->save();
        }
    }

    /**
     * Get total cancelled days from approved cancellations
     */
    public function getTotalCancelledDays()
    {
        return $this->cancellations()
            ->where('status', 'approved')
            ->sum('days_to_cancel');
    }

    /**
     * Get effective days (total_days - cancelled_days)
     */
    public function getEffectiveDays()
    {
        return $this->total_days - $this->getTotalCancelledDays();
    }

    /**
     * Check if this leave request is eligible for auto conversion
     */
    public function isEligibleForAutoConversion()
    {
        return $this->auto_conversion_at &&
            $this->auto_conversion_at <= now() &&
            !$this->supporting_document &&
            $this->leaveType &&
            $this->leaveType->category === 'paid';
    }

    /**
     * Update auto conversion date when leave type changes
     */
    public function updateAutoConversionDate($newLeaveTypeId)
    {
        $newLeaveType = LeaveType::find($newLeaveTypeId);

        if ($newLeaveType && $newLeaveType->category === 'paid' && !$this->supporting_document) {
            // Set new auto conversion date
            $this->auto_conversion_at = $this->created_at->addDays(12);
        } else {
            // Clear auto conversion date for unpaid leave or if document exists
            $this->auto_conversion_at = null;
        }

        $this->save();
    }

    /**
     * Check if this leave request is LSL flexible
     */
    public function isLSLFlexible()
    {
        return $this->leaveType &&
            ($this->leaveType->category === 'lsl' ||
                str_contains(strtolower($this->leaveType->name), 'lsl'));
    }

    /**
     * Get LSL leave days (for LSL flexible requests)
     */
    public function getLSLLeaveDays()
    {
        if ($this->isLSLFlexible()) {
            return $this->lsl_taken_days ?? 0;
        }
        return $this->total_days;
    }

    /**
     * Get LSL cashout days (for LSL flexible requests)
     */
    public function getLSLCashoutDays()
    {
        if ($this->isLSLFlexible()) {
            return $this->lsl_cashout_days ?? 0;
        }
        return 0;
    }

    /**
     * Get total LSL days (leave + cashout)
     */
    public function getLSLTotalDays()
    {
        if ($this->isLSLFlexible()) {
            return ($this->lsl_taken_days ?? 0) + ($this->lsl_cashout_days ?? 0);
        }
        return $this->total_days;
    }

    // Get manual approvers as User collection
    public function getManualApprovers()
    {
        if (empty($this->manual_approvers)) {
            return collect();
        }

        return User::whereIn('id', $this->manual_approvers)->get();
    }
}
