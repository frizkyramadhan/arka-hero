<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LeaveEntitlement extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'leave_type_id',
        'period_start',
        'period_end',
        'entitled_days',
        'withdrawable_days',
        'deposit_days',
        'carried_over',
        'taken_days',
        'remaining_days'
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date'
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'employee_id', 'employee_id')
            ->where('leave_type_id', $this->leave_type_id)
            ->whereBetween('start_date', [$this->period_start, $this->period_end]);
    }

    // Business Logic Methods
    public function calculateRemainingDays()
    {
        $this->remaining_days = $this->withdrawable_days - $this->taken_days;
        return $this->remaining_days;
    }

    public function isEligible()
    {
        return $this->withdrawable_days > 0;
    }

    public function canTakeLeave($days)
    {
        return $this->remaining_days >= $days;
    }

    public function updateTakenDays()
    {
        $this->taken_days = $this->leaveRequests()
            ->where('status', 'approved')
            ->sum('total_days');

        $this->calculateRemainingDays();
        $this->save();
    }
}
