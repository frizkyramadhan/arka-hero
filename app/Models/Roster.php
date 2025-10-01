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
        'roster_template_id',
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

    public function rosterTemplate()
    {
        return $this->belongsTo(RosterTemplate::class);
    }

    public function rosterAdjustments()
    {
        return $this->hasMany(RosterAdjustment::class);
    }

    public function rosterHistories()
    {
        return $this->hasMany(RosterHistory::class);
    }

    // Business Logic Methods
    public function calculateActualWorkDays()
    {
        $template = $this->rosterTemplate;
        $baseWorkDays = $template->work_days;

        // Apply adjustments from leave requests
        $adjustments = $this->rosterAdjustments()
            ->where('adjustment_type', '-days')
            ->sum('adjusted_value');

        return $baseWorkDays + $this->adjusted_days - $adjustments;
    }

    public function calculateActualOffDays()
    {
        $template = $this->rosterTemplate;
        $baseOffDays = $template->off_days_local; // Assuming local for now

        // Apply adjustments from leave requests
        $adjustments = $this->rosterAdjustments()
            ->where('adjustment_type', '+days')
            ->sum('adjusted_value');

        return $baseOffDays + $adjustments;
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
}
