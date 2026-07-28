<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;
use Illuminate\Support\Facades\DB;

class ManPowerPlan extends Model
{
    use Uuids;

    protected $fillable = [
        'mpp_number',
        'project_id',
        'title',
        'description',
        'status',
        'status_before_hold',
        'created_by',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Status constants
    const STATUS_ACTIVE = 'active';
    const STATUS_CLOSED = 'closed';
    const STATUS_ON_HOLD = 'on_hold';
    const STATUSES = ['active', 'closed', 'on_hold'];
    const HOLDABLE_STATUSES = ['active'];

    /**
     * Get status options for forms
     */
    public static function getStatusOptions()
    {
        return [
            self::STATUS_ACTIVE => 'Active',
            self::STATUS_CLOSED => 'Closed',
            self::STATUS_ON_HOLD => 'On Hold',
        ];
    }

    /**
     * Relationships
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function details()
    {
        return $this->hasMany(ManPowerPlanDetail::class, 'mpp_id');
    }

    public function holds()
    {
        return $this->hasMany(ManPowerPlanHold::class, 'man_power_plan_id')->orderByDesc('held_at');
    }

    public function activeHold()
    {
        return $this->hasOne(ManPowerPlanHold::class, 'man_power_plan_id')->whereNull('released_at');
    }

    public function sessions()
    {
        return $this->hasManyThrough(
            RecruitmentSession::class,
            ManPowerPlanDetail::class,
            'mpp_id', // Foreign key on man_power_plan_details table
            'mpp_detail_id', // Foreign key on recruitment_sessions table
            'id', // Local key on man_power_plans table
            'id' // Local key on man_power_plan_details table
        );
    }

    /**
     * Scopes
     */
    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeClosed($query)
    {
        return $query->where('status', self::STATUS_CLOSED);
    }

    public function scopeOnHold($query)
    {
        return $query->where('status', self::STATUS_ON_HOLD);
    }

    public function scopeByProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    /**
     * Accessors
     */
    public function getIsActiveAttribute()
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function getIsClosedAttribute()
    {
        return $this->status === self::STATUS_CLOSED;
    }

    public function getIsOnHoldAttribute()
    {
        return $this->status === self::STATUS_ON_HOLD;
    }

    public function isOnHold(): bool
    {
        return $this->status === self::STATUS_ON_HOLD;
    }

    public function canBeHeld(): bool
    {
        return in_array($this->status, self::HOLDABLE_STATUSES, true) && ! $this->activeHold()->exists();
    }

    /**
     * Total hold seconds overlapping [$from, $to].
     */
    public function totalHoldSecondsBetween($from, $to): int
    {
        if (! $from || ! $to) {
            return 0;
        }

        $holds = $this->relationLoaded('holds') ? $this->holds : $this->holds()->get();

        return (int) $holds->sum(fn (ManPowerPlanHold $hold) => $hold->overlapSeconds($from, $to));
    }

    public function totalHoldDaysBetween($from, $to): int
    {
        return (int) floor($this->totalHoldSecondsBetween($from, $to) / 86400);
    }

    public function adjustedDaysBetween($from, $to): int
    {
        if (! $from || ! $to) {
            return 0;
        }

        $from = \Carbon\Carbon::parse($from);
        $to = \Carbon\Carbon::parse($to);
        $calendarDays = (int) $from->diffInDays($to);
        $holdDays = $this->totalHoldDaysBetween($from, $to);

        return max(0, $calendarDays - $holdDays);
    }

    /**
     * Business Logic Methods
     */

    /**
     * Get total positions needed (sum of all plan quantities)
     */
    public function getTotalPositionsNeeded()
    {
        return $this->details->sum(function ($detail) {
            return $detail->total_plan;
        });
    }

    /**
     * Get total existing positions (sum of all existing quantities)
     */
    public function getTotalExisting()
    {
        return $this->details->sum(function ($detail) {
            return $detail->total_existing;
        });
    }

    /**
     * Get total difference (existing - plan)
     */
    public function getTotalDiff()
    {
        return $this->details->sum(function ($detail) {
            return $detail->total_diff;
        });
    }

    /**
     * Get total staff needed
     */
    public function getTotalStaffNeeded()
    {
        return $this->details->sum('plan_qty_s');
    }

    /**
     * Get total non-staff needed
     */
    public function getTotalNonStaffNeeded()
    {
        return $this->details->sum('plan_qty_ns');
    }

    /**
     * Get total existing staff
     */
    public function getTotalExistingStaff()
    {
        return $this->details->sum('existing_qty_s');
    }

    /**
     * Get total existing non-staff
     */
    public function getTotalExistingNonStaff()
    {
        return $this->details->sum('existing_qty_ns');
    }

    /**
     * Get staff difference
     */
    public function getStaffDiff()
    {
        return $this->details->sum(function ($detail) {
            return $detail->diff_s;
        });
    }

    /**
     * Get non-staff difference
     */
    public function getNonStaffDiff()
    {
        return $this->details->sum(function ($detail) {
            return $detail->diff_ns;
        });
    }

    /**
     * Get completion percentage
     */
    public function getCompletionPercentage()
    {
        $totalNeeded = $this->getTotalPositionsNeeded();
        if ($totalNeeded === 0) return 100;

        $totalFulfilled = $this->details->filter(function ($detail) {
            return $detail->fulfilled_at !== null;
        })->count();

        $totalDetails = $this->details->count();
        if ($totalDetails === 0) return 0;

        return round(($totalFulfilled / $totalDetails) * 100, 2);
    }

    /**
     * Check if MPP can receive applications
     */
    public function canReceiveApplications()
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    /**
     * Close the MPP
     */
    public function close()
    {
        $this->update(['status' => self::STATUS_CLOSED]);
        return true;
    }

    /**
     * Reopen the MPP
     */
    public function reopen()
    {
        $this->update(['status' => self::STATUS_ACTIVE]);
        return true;
    }

    /**
     * Generate unique MPP number
     */
    public static function generateMPPNumber()
    {
        $year = date('Y');
        $month = date('m');

        // Get last sequence for this year/month
        $lastMPP = static::whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->orderBy('created_at', 'desc')
            ->first();

        $sequence = 1;
        if ($lastMPP && $lastMPP->mpp_number) {
            // Extract sequence from MPP number: MPP/YYYY/MM/SEQUENCE
            if (preg_match('/MPP\/\d{4}\/\d{2}\/(\d+)$/', $lastMPP->mpp_number, $matches)) {
                $sequence = (int)$matches[1] + 1;
            }
        }

        return sprintf('MPP/%s/%s/%04d', $year, $month, $sequence);
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->mpp_number)) {
                $model->mpp_number = static::generateMPPNumber();
            }
        });
    }
}

