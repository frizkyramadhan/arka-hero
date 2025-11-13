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
        'deposit_days',
        'taken_days'
    ];

    protected $casts = [
        'period_start' => 'date',
        'period_end' => 'date'
    ];

    /**
     * Get remaining days (calculated attribute)
     * remaining_days = entitled_days - taken_days
     */
    public function getRemainingDaysAttribute()
    {
        return max(0, $this->entitled_days - $this->taken_days);
    }

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
        // No longer needed - remaining_days is now an accessor
        // Kept for backward compatibility if called elsewhere
        return $this->remaining_days;
    }

    public function isEligible()
    {
        return $this->entitled_days > 0;
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

        // remaining_days is now calculated via accessor, no need to save
        $this->save();
    }

    /**
     * Get detailed leave calculation breakdown for this entitlement
     *
     * @return array
     */
    public function getLeaveCalculationDetails()
    {
        $approvedRequests = $this->leaveRequests()
            ->whereIn('status', ['approved', 'cancelled'])
            ->orderBy('start_date')
            ->get();

        $totalTakenDays = $approvedRequests->sum('total_days');
        $totalCancelledDays = 0;
        $totalEffectiveDays = 0;

        $leaveRequestsData = $approvedRequests->map(function ($request) use (&$totalCancelledDays, &$totalEffectiveDays) {
            $cancelledDays = $request->getTotalCancelledDays();
            $effectiveDays = $request->getEffectiveDays();

            $totalCancelledDays += $cancelledDays;
            $totalEffectiveDays += $effectiveDays;

            return [
                'id' => $request->id,
                'start_date' => $request->start_date->format('d M Y'),
                'end_date' => $request->end_date->format('d M Y'),
                'total_days' => $request->total_days,
                'cancelled_days' => $cancelledDays,
                'effective_days' => $effectiveDays,
                'status' => $request->status,
                'reason' => $request->reason,
                'approved_at' => $request->approved_at ? $request->approved_at->format('d M Y H:i') : null,
            ];
        });

        return [
            'entitlement_period' => [
                'start' => $this->period_start->format('d M Y'),
                'end' => $this->period_end->format('d M Y'),
            ],
            'leave_type' => $this->leaveType->name ?? 'Unknown',
            'total_entitlement' => $this->entitled_days,
            'taken_days' => $totalTakenDays,
            'total_cancelled_days' => $totalCancelledDays,
            'total_effective_days' => $totalEffectiveDays,
            'remaining_days' => $this->remaining_days,
            'leave_requests' => $leaveRequestsData,
            'calculation_summary' => [
                'total_entitlement' => $this->entitled_days,
                'total_taken' => $totalTakenDays,
                'total_cancelled' => $totalCancelledDays,
                'total_effective' => $totalEffectiveDays,
                'remaining' => $this->remaining_days,
                'utilization_percentage' => $this->entitled_days > 0 ? round(($totalEffectiveDays / $this->entitled_days) * 100, 2) : 0,
            ]
        ];
    }

    /**
     * Get leave calculation details for specific employee and leave type
     *
     * @param string $employeeId
     * @param int $leaveTypeId
     * @param string|null $periodStart
     * @param string|null $periodEnd
     * @return array|null
     */
    public static function getEmployeeLeaveDetails($employeeId, $leaveTypeId, $periodStart = null, $periodEnd = null)
    {
        $query = self::where('employee_id', $employeeId)
            ->where('leave_type_id', $leaveTypeId);

        if ($periodStart && $periodEnd) {
            $query->where('period_start', '<=', $periodStart)
                ->where('period_end', '>=', $periodEnd);
        }

        $entitlement = $query->first();

        if (!$entitlement) {
            return null;
        }

        return $entitlement->getLeaveCalculationDetails();
    }
}
