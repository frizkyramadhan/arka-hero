<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Roster extends Model
{
    use HasFactory, Uuids;

    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'employee_id',
        'administration_id'
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

    public function rosterDetails()
    {
        return $this->hasMany(RosterDetail::class)->orderBy('cycle_no');
    }

    public function latestDetail()
    {
        return $this->hasOne(RosterDetail::class)->latestOfMany('cycle_no');
    }

    public function currentDetail()
    {
        return $this->hasOne(RosterDetail::class)
            ->where('work_start', '<=', now())
            ->where('work_end', '>=', now())
            ->orderBy('cycle_no', 'desc');
    }

    // Business Logic Methods

    /**
     * Get FB Cycle ratio from level configuration
     *
     * Rumus FB Cycle Ratio:
     * Untuk cycle 2w/9w (work_days = 63 hari = 9 weeks):
     *
     * FB Cycle Ratio = ((15/7) × workWeeks) / (7 × workWeeks)
     *                = ((15/7) × 9) / (7 × 9)
     *                = (15 × 9) / (7 × 7 × 9)
     *                = 15 / 49
     *                = 0.306122...
     *
     * Atau menggunakan totalWeeks (workWeeks + offWeeks):
     * FB Cycle Ratio = ((15/7) × totalWeeks) / (workWeeks × totalWeeks)
     *                = ((15/7) × 11) / (9 × 11)  [untuk 2w/9w: 9+2=11]
     *                = (15/7) / 9
     *                = 15 / 63
     *                = 0.238095...
     *
     * Format: 2w/9w = 0.24 (rounded)
     */
    public function getFbCycleRatio()
    {
        if (!$this->administration || !$this->administration->level) {
            return 0;
        }

        $level = $this->administration->level;

        // Check if level has roster configuration
        if (!$level->hasRosterConfig()) {
            return 0;
        }

        // Calculate base leave ratio: 15 working days / 7 days per week = 2.142857143
        // This represents 2 weeks of leave in working days
        $baseLeaveRatio = 15 / 7;

        $offWeeks = $level->off_days / 7;  // e.g., 14 days = 2 weeks
        $workWeeks = $level->work_days / 7; // e.g., 63 days = 9 weeks

        $totalWeeks = $offWeeks + $workWeeks; // Total cycle weeks (work + leave)

        // FB Cycle Ratio menggunakan totalWeeks (workWeeks + offWeeks)
        // Formula: ((15/7) × totalWeeks) / (workWeeks × totalWeeks)
        // Simplified: (15/7) / workWeeks
        // Example for 2w/9w: ((15/7) × 11) / (9 × 11) = (15/7) / 9 = 0.238095
        // Atau dengan workWeeks saja: ((15/7) × 9) / (7 × 9) = 15/49 = 0.306122
        $ratio = ($baseLeaveRatio * $totalWeeks) / ($workWeeks * $totalWeeks);

        return $ratio;
    }

    /**
     * Calculate leave entitlement for given work days
     *
     * Rumus Entitlement:
     * Entitlement = Actual Work Days × FB Cycle Ratio
     *
     * Untuk cycle 2w/9w dengan actual work days = 70 hari:
     *
     * Menggunakan rumus dengan workWeeks:
     * Entitlement = 70 × (((15/7) × 9) / (7 × 9))
     *             = 70 × (15/49)
     *             = 70 × 0.306122
     *             = 21.43 hari
     *
     * Menggunakan rumus dengan totalWeeks:
     * Entitlement = 70 × (((15/7) × 11) / (9 × 11))
     *             = 70 × ((15/7) / 9)
     *             = 70 × 0.238095
     *             = 16.67 hari (rounded to 16.8)
     *
     * @param float $workDays Actual work days in the cycle
     * @return float Leave entitlement in days (rounded to 2 decimals)
     */
    public function calculateLeaveEntitlement($workDays)
    {
        $ratio = $this->getFbCycleRatio();
        return round($workDays * $ratio, 2);
    }

    /**
     * Get work days from level configuration
     */
    public function getWorkDays()
    {
        if (!$this->administration || !$this->administration->level) {
            return 0;
        }
        return $this->administration->level->work_days ?? 0;
    }

    /**
     * Get off days from level configuration
     */
    public function getOffDays()
    {
        if (!$this->administration || !$this->administration->level) {
            return 14;
        }
        return $this->administration->level->off_days ?? 14;
    }

    /**
     * Get Roster Cycle (e.g., "2w/9w")
     */
    public function getRosterPattern()
    {
        if (!$this->administration || !$this->administration->level) {
            return 'N/A';
        }
        return $this->administration->level->getRosterPattern() ?? 'N/A';
    }

    /**
     * Get roster pattern in display format (off/work instead of work/off)
     * This is for display purposes only, does not change calculation logic
     */
    public function getRosterPatternDisplay()
    {
        if (!$this->administration || !$this->administration->level) {
            return 'N/A';
        }
        return $this->administration->level->getRosterPatternDisplay() ?? 'N/A';
    }

    /**
     * Get total accumulated leave days
     */
    public function getTotalAccumulatedLeave()
    {
        return $this->rosterDetails->sum(function ($detail) {
            $actualWorkDays = $detail->getActualWorkDays();
            return $this->calculateLeaveEntitlement($actualWorkDays);
        });
    }

    /**
     * Get total leave taken
     */
    public function getTotalLeaveTaken()
    {
        return $this->rosterDetails->sum(function ($detail) {
            return $detail->getLeaveDays();
        });
    }

    /**
     * Get remaining leave balance
     */
    public function getLeaveBalance()
    {
        return $this->getTotalAccumulatedLeave() - $this->getTotalLeaveTaken();
    }
}
