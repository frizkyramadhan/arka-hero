<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'category',
        'default_days',
        'eligible_after_years',
        'deposit_days_first',
        'carry_over',
        'remarks',
        'is_active'
    ];

    protected $casts = [
        'carry_over' => 'boolean',
        'is_active' => 'boolean'
    ];

    // Relationships
    public function leaveEntitlements()
    {
        return $this->hasMany(LeaveEntitlement::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    // Business Logic Methods
    public function isAnnualLeave()
    {
        return $this->category === 'annual';
    }

    public function isLongServiceLeave()
    {
        return $this->category === 'lsl';
    }

    public function isPaidLeave()
    {
        return $this->category === 'paid';
    }

    public function isUnpaidLeave()
    {
        return $this->category === 'unpaid';
    }

    public function requiresApproval()
    {
        return in_array($this->category, ['paid', 'unpaid']);
    }

    public function canCarryOver()
    {
        return $this->carry_over;
    }

    public function getEligibilityYears()
    {
        return $this->eligible_after_years;
    }

    public function getDepositDays()
    {
        return $this->deposit_days_first;
    }
}
