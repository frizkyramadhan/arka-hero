<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RosterAdjustment extends Model
{
    use HasFactory;

    protected $fillable = [
        'roster_id',
        'leave_request_id',
        'adjustment_type',
        'adjusted_value',
        'reason'
    ];

    // Relationships
    public function roster()
    {
        return $this->belongsTo(Roster::class);
    }

    public function leaveRequest()
    {
        return $this->belongsTo(LeaveRequest::class);
    }

    // Business Logic Methods
    public function isPositiveAdjustment()
    {
        return $this->adjustment_type === '+days';
    }

    public function isNegativeAdjustment()
    {
        return $this->adjustment_type === '-days';
    }

    public function getEffectiveValue()
    {
        return $this->isPositiveAdjustment() ? $this->adjusted_value : -$this->adjusted_value;
    }

    public function getAdjustmentDescription()
    {
        $type = $this->isPositiveAdjustment() ? 'Added' : 'Reduced';
        return "{$type} {$this->adjusted_value} days - {$this->reason}";
    }

    public function applyToRoster()
    {
        $this->roster->updateAdjustedDays();
    }
}
