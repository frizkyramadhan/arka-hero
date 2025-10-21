<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Roster extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'administration_id',
        'start_date',
        'end_date',
        'cycle_no',
        'adjusted_days',
        'is_active'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'is_active' => 'boolean'
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

    // Removed rosterTemplate relationship - now using level directly

    public function rosterAdjustments()
    {
        return $this->hasMany(RosterAdjustment::class);
    }

    public function rosterHistories()
    {
        return $this->hasMany(RosterHistory::class);
    }

    public function dailyStatuses()
    {
        return $this->hasMany(RosterDailyStatus::class);
    }

    // Business Logic Methods - Updated to use level directly
    public function getWorkDays()
    {
        return $this->administration->level->getWorkDays() ?? 0;
    }

    public function getOffDays()
    {
        return $this->administration->level->getOffDays() ?? 14;
    }

    public function getCycleLength()
    {
        return $this->administration->level->getCycleLength() ?? 0;
    }

    public function getRosterPattern()
    {
        return $this->administration->level->getRosterPattern();
    }

    public function calculateActualWorkDays()
    {
        $baseWorkDays = $this->getWorkDays();

        // Apply adjustments from leave requests
        $adjustments = $this->rosterAdjustments()
            ->where('adjustment_type', '-days')
            ->sum('adjusted_value');

        return $baseWorkDays + $this->adjusted_days - $adjustments;
    }

    public function calculateActualOffDays()
    {
        return $this->getOffDays();
    }

    public function getTotalCycleDays()
    {
        return $this->calculateActualWorkDays() + $this->calculateActualOffDays();
    }

    public function isCurrentCycle()
    {
        $now = now()->toDateString();
        return $this->start_date <= $now && $this->end_date >= $now;
    }

    public function isCompleted()
    {
        return $this->end_date < now();
    }

    public function addAdjustment($leaveRequestId, $adjustmentType, $value, $reason)
    {
        return $this->rosterAdjustments()->create([
            'leave_request_id' => $leaveRequestId,
            'adjustment_type' => $adjustmentType,
            'adjusted_value' => $value,
            'reason' => $reason
        ]);
    }

    public function updateAdjustedDays()
    {
        $negativeAdjustments = $this->rosterAdjustments()
            ->where('adjustment_type', '-days')
            ->sum('adjusted_value');

        $positiveAdjustments = $this->rosterAdjustments()
            ->where('adjustment_type', '+days')
            ->sum('adjusted_value');

        $this->adjusted_days = $positiveAdjustments - $negativeAdjustments;
        $this->save();
    }

    public function createHistory()
    {
        return $this->rosterHistories()->create([
            'cycle_no' => $this->cycle_no,
            'work_days_actual' => $this->calculateActualWorkDays(),
            'off_days_actual' => $this->calculateActualOffDays(),
            'remarks' => 'Cycle completed'
        ]);
    }

    // New methods for daily status management
    public function getStatusForDate($date)
    {
        return $this->dailyStatuses()
            ->where('date', $date)
            ->first();
    }

    public function setStatusForDate($date, $statusCode, $notes = null)
    {
        return RosterDailyStatus::updateOrCreate(
            [
                'roster_id' => $this->id,
                'date' => $date
            ],
            [
                'status_code' => $statusCode,
                'notes' => $notes
            ]
        );
    }

    public function getStatusForMonth($year, $month)
    {
        $startDate = \Carbon\Carbon::create($year, $month, 1);
        $endDate = $startDate->copy()->endOfMonth();

        return $this->dailyStatuses()
            ->whereBetween('date', [$startDate, $endDate])
            ->orderBy('date')
            ->get()
            ->keyBy(function ($status) {
                return $status->date->day;
            });
    }
}
