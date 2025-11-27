<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;

class LeaveRequestCancellation extends Model
{
    use HasFactory, Uuids;

    protected $fillable = [
        'leave_request_id',
        'days_to_cancel',
        'reason',
        'status',
        'requested_by',
        'requested_at',
        'confirmed_by',
        'confirmed_at',
        'confirmation_notes'
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'confirmed_at' => 'datetime'
    ];

    // Relationships
    public function leaveRequest()
    {
        return $this->belongsTo(LeaveRequest::class);
    }

    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function confirmedBy()
    {
        return $this->belongsTo(User::class, 'confirmed_by');
    }

    // Business Logic Methods
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

    /**
     * Approve cancellation request
     */
    public function approve($confirmedBy, $notes = null)
    {
        if (!$this->isPending()) {
            throw new \InvalidArgumentException('Only pending cancellation requests can be approved.');
        }

        $this->status = 'approved';
        $this->confirmed_by = $confirmedBy;
        $this->confirmed_at = now();
        $this->confirmation_notes = $notes;
        $this->save();

        // Handle partial or full cancellation
        $leaveRequest = $this->leaveRequest;

        if ($this->days_to_cancel == $leaveRequest->total_days) {
            // Full cancellation - mark leave request as cancelled
            $leaveRequest->status = 'cancelled';
            $leaveRequest->save();
        }
        // Note: We don't modify total_days for partial cancellation to preserve history

        // Update leave entitlements (return cancelled days to entitlement)
        $this->updateLeaveEntitlements();
    }

    /**
     * Reject cancellation request
     */
    public function reject($confirmedBy, $notes = null)
    {
        $this->status = 'rejected';
        $this->confirmed_by = $confirmedBy;
        $this->confirmed_at = now();
        $this->confirmation_notes = $notes;
        $this->save();
    }

    /**
     * Update leave entitlements after cancellation approval
     */
    private function updateLeaveEntitlements()
    {
        $leaveRequest = $this->leaveRequest;

        // Find the matching entitlement
        $entitlement = LeaveEntitlement::where('employee_id', $leaveRequest->employee_id)
            ->where('leave_type_id', $leaveRequest->leave_type_id)
            ->where('period_start', '<=', $leaveRequest->start_date)
            ->where('period_end', '>=', $leaveRequest->end_date)
            ->first();

        if ($entitlement) {
            // Reduce taken days by the cancelled amount
            $entitlement->taken_days -= $this->days_to_cancel;

            // remaining_days is now calculated via accessor, no need to update manually
            $entitlement->save();
        }
    }
}
