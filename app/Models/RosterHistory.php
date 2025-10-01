<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RosterHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'roster_id',
        'cycle_no',
        'work_days_actual',
        'off_days_actual',
        'remarks'
    ];

    // Relationships
    public function roster()
    {
        return $this->belongsTo(Roster::class);
    }

    // Business Logic Methods
    public function getTotalActualDays()
    {
        return $this->work_days_actual + $this->off_days_actual;
    }

    public function getWorkOffRatio()
    {
        if ($this->off_days_actual > 0) {
            return round($this->work_days_actual / $this->off_days_actual, 2);
        }
        return 0;
    }

    public function getEfficiencyPercentage()
    {
        $template = $this->roster->rosterTemplate;
        $expectedWorkDays = $template->work_days;

        if ($expectedWorkDays > 0) {
            return round(($this->work_days_actual / $expectedWorkDays) * 100, 2);
        }
        return 0;
    }

    public function isOverPerformed()
    {
        $template = $this->roster->rosterTemplate;
        return $this->work_days_actual > $template->work_days;
    }

    public function isUnderPerformed()
    {
        $template = $this->roster->rosterTemplate;
        return $this->work_days_actual < $template->work_days;
    }

    public function getPerformanceStatus()
    {
        if ($this->isOverPerformed()) {
            return 'over_performed';
        } elseif ($this->isUnderPerformed()) {
            return 'under_performed';
        }
        return 'normal';
    }
}
