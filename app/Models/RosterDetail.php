<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RosterDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'roster_id',
        'cycle_no',
        'work_start',
        'work_end',
        'adjusted_days',
        'leave_start',
        'leave_end',
        'status',
        'remarks'
    ];

    protected $casts = [
        'work_start' => 'date',
        'work_end' => 'date',
        'leave_start' => 'date',
        'leave_end' => 'date',
        'adjusted_days' => 'integer',
        'cycle_no' => 'integer'
    ];

    // Relationships
    public function roster()
    {
        return $this->belongsTo(Roster::class);
    }

    // Business Logic Methods

    /**
     * Get base work days (from configuration)
     */
    public function getBaseWorkDays()
    {
        if (!$this->roster) {
            return 0;
        }
        return $this->roster->getWorkDays();
    }

    /**
     * Get actual work days (considering date range)
     * This calculates the actual days between work_start and work_end (work_end - work_start - 1)
     * Note: adjusted_days is only used during create/edit to calculate work_end, not included in total work days
     */
    public function getActualWorkDays()
    {
        if (!$this->work_start || !$this->work_end) {
            return 0;
        }

        // Calculate actual work days from date range (work_end - work_start - 1)
        $workStart = Carbon::parse($this->work_start);
        $workEnd = Carbon::parse($this->work_end);

        // Calculate days difference
        return $workStart->diffInDays($workEnd);
    }

    /**
     * Get leave days taken in this cycle
     */
    public function getLeaveDays()
    {
        if (!$this->leave_start || !$this->leave_end) {
            return 0;
        }

        $leaveStart = Carbon::parse($this->leave_start);
        $leaveEnd = Carbon::parse($this->leave_end);

        // Add 1 because we include both start and end date
        return $leaveStart->diffInDays($leaveEnd) + 1;
    }

    /**
     * Calculate leave entitlement for this cycle
     *
     * Rumus Entitlement:
     * Entitlement = Actual Work Days × FB Cycle Ratio
     *
     * Dimana:
     * - Actual Work Days = work_end - work_start (selisih hari kerja)
     * - FB Cycle Ratio = ((15/7) × totalWeeks) / (workWeeks × totalWeeks)
     *
     * Contoh untuk cycle 2w/9w:
     * - Work Start: 05 Feb 2026
     * - Work End: 16 Apr 2026
     * - Actual Work Days: 70 hari
     * - FB Cycle Ratio: ((15/7) × 11) / (9 × 11) = 0.24
     * - Entitlement: 70 × 0.24 = 16.8 hari
     *
     * @return float Leave entitlement in days (rounded to 2 decimals)
     */
    public function getLeaveEntitlement()
    {
        if (!$this->roster) {
            return 0;
        }

        $actualWorkDays = $this->getActualWorkDays();
        return $this->roster->calculateLeaveEntitlement($actualWorkDays);
    }

    /**
     * Get leave balance for this cycle
     */
    public function getLeaveBalance()
    {
        return $this->getLeaveEntitlement() - $this->getLeaveDays();
    }

    /**
     * Check if this cycle is currently active
     */
    public function isActive()
    {
        $now = now();
        return $this->work_start <= $now && $this->work_end >= $now;
    }

    /**
     * Check if currently in leave period
     */
    public function isOnLeave()
    {
        if (!$this->leave_start || !$this->leave_end) {
            return false;
        }

        $now = now();
        return $this->leave_start <= $now && $this->leave_end >= $now;
    }

    /**
     * Check if this cycle is completed
     */
    public function isCompleted()
    {
        if ($this->leave_end) {
            return $this->leave_end < now();
        }
        return $this->work_end < now();
    }

    /**
     * Get total cycle duration (work + leave)
     */
    public function getTotalCycleDays()
    {
        return $this->getActualWorkDays() + $this->getLeaveDays();
    }

    /**
     * Auto-update status based on current date
     */
    public function updateStatus()
    {
        $now = now();

        if ($this->leave_end && $now->gt($this->leave_end)) {
            $this->status = 'completed';
        } elseif ($this->leave_start && $this->leave_end && $now->between($this->leave_start, $this->leave_end)) {
            $this->status = 'on_leave';
        } elseif ($now->between($this->work_start, $this->work_end)) {
            $this->status = 'active';
        } else {
            $this->status = 'scheduled';
        }

        return $this->save();
    }

    /**
     * Get status label for display
     */
    public function getStatusLabel()
    {
        return match ($this->status) {
            'scheduled' => 'Scheduled',
            'active' => 'Active',
            'on_leave' => 'On Leave',
            'completed' => 'Completed',
            default => 'Unknown'
        };
    }

    /**
     * Get status badge color
     */
    public function getStatusBadgeClass()
    {
        return match ($this->status) {
            'scheduled' => 'badge-info',
            'active' => 'badge-success',
            'on_leave' => 'badge-warning',
            'completed' => 'badge-secondary',
            default => 'badge-secondary'
        };
    }
}
