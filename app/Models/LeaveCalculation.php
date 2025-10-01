<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveCalculation extends Model
{
    use HasFactory;

    protected $fillable = [
        'leave_request_id',
        'annual_eligibility',
        'lsl_eligibility',
        'outstanding_lsl',
        'accumulated_leave',
        'entitlement',
        'less_this_leave',
        'paid_out',
        'balance'
    ];

    // Relationships
    public function leaveRequest()
    {
        return $this->belongsTo(LeaveRequest::class);
    }

    // Business Logic Methods
    public function calculateBalance()
    {
        $this->balance = $this->entitlement - $this->less_this_leave - $this->paid_out;
        return $this->balance;
    }

    public function calculateEntitlement()
    {
        // Calculate total entitlement based on annual and LSL eligibility
        $this->entitlement = $this->annual_eligibility + $this->lsl_eligibility + $this->accumulated_leave;
        return $this->entitlement;
    }

    public function isLSLFirstPeriod()
    {
        return $this->lsl_eligibility > 0 && $this->outstanding_lsl > 0;
    }

    public function getWithdrawableAmount()
    {
        if ($this->isLSLFirstPeriod()) {
            // First period LSL: 40 days withdrawable, 10 days deposit
            return min($this->lsl_eligibility, 40);
        }
        return $this->lsl_eligibility;
    }

    public function getDepositAmount()
    {
        if ($this->isLSLFirstPeriod()) {
            return max(0, $this->lsl_eligibility - 40);
        }
        return 0;
    }
}
