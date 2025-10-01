<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RosterTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id',
        'level_id',
        'work_days',
        'off_days_local',
        'off_days_nonlocal',
        'cycle_length',
        'effective_date',
        'is_active'
    ];

    protected $casts = [
        'effective_date' => 'date',
        'is_active' => 'boolean'
    ];

    // Relationships
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function level()
    {
        return $this->belongsTo(Level::class);
    }

    public function rosters()
    {
        return $this->hasMany(Roster::class);
    }

    // Business Logic Methods
    public function getOffDaysForLocation($isLocal = true)
    {
        return $isLocal ? $this->off_days_local : $this->off_days_nonlocal;
    }

    public function getTotalCycleDays()
    {
        return $this->work_days + $this->off_days_local;
    }

    public function isEffectiveForDate($date)
    {
        return $this->effective_date <= $date && $this->is_active;
    }

    public function getWorkOffRatio()
    {
        if ($this->off_days_local > 0) {
            return round($this->work_days / $this->off_days_local, 2);
        }
        return 0;
    }

    public function calculateCycleEndDate($startDate)
    {
        return $startDate->addDays($this->cycle_length - 1);
    }

    public function isProjectRosterBased()
    {
        // Check if this project uses roster system (projects 017, 022, etc.)
        return in_array($this->project->project_code ?? '', ['017C', '022C']);
    }
}
