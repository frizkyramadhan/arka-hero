<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;

class ManPowerPlanHold extends Model
{
    use Uuids;

    protected $fillable = [
        'man_power_plan_id',
        'held_by',
        'held_at',
        'hold_reason',
        'released_by',
        'released_at',
        'release_reason',
    ];

    protected $casts = [
        'held_at' => 'datetime',
        'released_at' => 'datetime',
    ];

    public function manPowerPlan()
    {
        return $this->belongsTo(ManPowerPlan::class, 'man_power_plan_id');
    }

    public function heldBy()
    {
        return $this->belongsTo(User::class, 'held_by');
    }

    public function releasedBy()
    {
        return $this->belongsTo(User::class, 'released_by');
    }

    public function scopeActive($query)
    {
        return $query->whereNull('released_at');
    }

    public function isActive(): bool
    {
        return $this->released_at === null;
    }

    /**
     * Overlap duration in seconds between this hold and [$from, $to].
     */
    public function overlapSeconds($from, $to): int
    {
        $holdStart = $this->held_at;
        $holdEnd = $this->released_at ?? now();

        if (! $from || ! $to || ! $holdStart) {
            return 0;
        }

        $from = \Carbon\Carbon::parse($from);
        $to = \Carbon\Carbon::parse($to);

        if ($to->lte($from) || $holdEnd->lte($holdStart)) {
            return 0;
        }

        $overlapStart = $from->greaterThan($holdStart) ? $from->copy() : $holdStart->copy();
        $overlapEnd = $to->lessThan($holdEnd) ? $to->copy() : $holdEnd->copy();

        if ($overlapEnd->lte($overlapStart)) {
            return 0;
        }

        return (int) $overlapStart->diffInSeconds($overlapEnd);
    }
}
