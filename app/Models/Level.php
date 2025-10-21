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

    public function getTotalCycleDays()
    {
        return $this->work_days + $this->getOffDays();
    }

    public function getRosterPattern()
    {
        if (!$this->work_days) {
            return null; // Level tidak punya roster config
        }

        $workWeeks = $this->work_days / 7;
        $offWeeks = $this->getOffDays() / 7;

        return "{$workWeeks}/{$offWeeks}";
    }

    public function hasRosterConfig()
    {
        return !is_null($this->work_days) && $this->work_days > 0;
    }
}
