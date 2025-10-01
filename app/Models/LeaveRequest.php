<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'administration_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'back_to_work_date',
        'reason',
        'total_days',
        'status',
        'leave_period',
        'requested_at'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'back_to_work_date' => 'date',
        'requested_at' => 'datetime'
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

    public function leaveCalculations()
    {
        return $this->hasMany(LeaveCalculation::class);
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

    public function canBeCancelled()
    {
        return in_array($this->status, ['pending', 'approved']) &&
            $this->start_date > now();
    }

    public function approve()
    {
        $this->status = 'approved';
        $this->save();

        // Update leave entitlement
        $this->updateLeaveEntitlement();
    }

    public function reject()
    {
        $this->status = 'rejected';
        $this->save();
    }

    public function cancel()
    {
        $this->status = 'cancelled';
        $this->save();
    }

    private function updateLeaveEntitlement()
    {
        $entitlement = LeaveEntitlement::where('employee_id', $this->employee_id)
            ->where('leave_type_id', $this->leave_type_id)
            ->where('period_start', '<=', $this->start_date)
            ->where('period_end', '>=', $this->end_date)
            ->first();

        if ($entitlement) {
            $entitlement->updateTakenDays();
        }
    }
}
