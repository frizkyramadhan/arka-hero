<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Level extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'level_order',
        'is_active',
        'off_days',
        'work_days',
        'cycle_length'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'level_order' => 'integer',
        'off_days' => 'integer',
        'work_days' => 'integer',
        'cycle_length' => 'integer'
    ];

    public function administrations()
    {
        return $this->hasMany(Administration::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    // Business Logic Methods for Roster
    public function getOffDays()
    {
        return $this->off_days ?? 14; // Default 14 jika null
    }

    public function getWorkDays()
    {
        return $this->work_days;
    }

    public function getCycleLength()
    {
        // Use stored cycle_length if available, otherwise calculate it
        if (!is_null($this->cycle_length)) {
            return $this->cycle_length;
        }
        return $this->getTotalCycleDays();
    }

    public function getTotalCycleDays()
    {
        return $this->work_days + $this->getOffDays();
    }

    public function getRosterPattern()
    {
        if (!$this->work_days) {
            return null; // Level tidak punya roster cycle
        }

        $workWeeks = $this->work_days / 7;
        $offWeeks = $this->getOffDays() / 7;

        return "{$workWeeks}/{$offWeeks}";
    }

    /**
     * Get roster pattern in display format (off/work instead of work/off)
     * This is for display purposes only, does not change calculation logic
     */
    public function getRosterPatternDisplay()
    {
        if (!$this->work_days) {
            return null; // Level tidak punya roster cycle
        }

        $workWeeks = $this->work_days / 7;
        $offWeeks = $this->getOffDays() / 7;

        return "{$offWeeks}/{$workWeeks}";
    }

    public function hasRosterConfig()
    {
        return !is_null($this->work_days) && $this->work_days > 0;
    }
}
