<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;
use Carbon\Carbon;

class ManPowerPlanDetail extends Model
{
    use Uuids;

    public $incrementing = false;
    protected $keyType = 'string';
    protected $fillable = [
        'mpp_id',
        'qty_unit',
        'existing_qty_s',
        'existing_qty_ns',
        'plan_qty_s',
        'plan_qty_ns',
        'position_id',
        'fulfilled_at',
        'remarks',
        'requires_theory_test',
        'agreement_type',
    ];

    protected $casts = [
        'qty_unit' => 'integer',
        'existing_qty_s' => 'integer',
        'existing_qty_ns' => 'integer',
        'plan_qty_s' => 'integer',
        'plan_qty_ns' => 'integer',
        'fulfilled_at' => 'datetime',
        'requires_theory_test' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relationships
     */
    public function mpp()
    {
        return $this->belongsTo(ManPowerPlan::class, 'mpp_id');
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }


    public function sessions()
    {
        return $this->hasMany(RecruitmentSession::class, 'mpp_detail_id');
    }

    /**
     * Get hired sessions
     */
    public function hiredSessions()
    {
        return $this->hasMany(RecruitmentSession::class, 'mpp_detail_id')
            ->where('status', 'hired');
    }

    /**
     * Get active sessions
     */
    public function activeSessions()
    {
        return $this->hasMany(RecruitmentSession::class, 'mpp_detail_id')
            ->where('status', 'in_process');
    }

    /**
     * Accessors
     */

    /**
     * Get total existing quantity (S + NS)
     */
    public function getTotalExistingAttribute()
    {
        return $this->existing_qty_s + $this->existing_qty_ns;
    }

    /**
     * Get total plan quantity (S + NS)
     */
    public function getTotalPlanAttribute()
    {
        return $this->plan_qty_s + $this->plan_qty_ns;
    }

    /**
     * Get staff difference (Existing S - Plan S)
     */
    public function getDiffSAttribute()
    {
        return $this->existing_qty_s - $this->plan_qty_s;
    }

    /**
     * Get non-staff difference (Existing NS - Plan NS)
     */
    public function getDiffNsAttribute()
    {
        return $this->existing_qty_ns - $this->plan_qty_ns;
    }

    /**
     * Get total difference (Total Existing - Total Plan)
     */
    public function getTotalDiffAttribute()
    {
        return $this->total_existing - $this->total_plan;
    }

    /**
     * Get days to fulfill (from MPP creation to fulfillment)
     */
    public function getDaysToFulfillAttribute()
    {
        if (!$this->fulfilled_at) {
            return null;
        }

        return $this->mpp->created_at->diffInDays($this->fulfilled_at);
    }

    /**
     * Get fulfillment status
     */
    public function getIsFulfilledAttribute()
    {
        return $this->fulfilled_at !== null;
    }

    /**
     * Get remaining need (how many more positions needed)
     */
    public function getRemainingNeedAttribute()
    {
        $hired = $this->hiredSessions()->count();
        // total_diff = existing - plan (negative means still needed)
        // remaining = plan - existing - hired = -(total_diff) - hired
        return max(0, -$this->total_diff - $hired);
    }

    /**
     * Business Logic Methods
     */

    /**
     * Check if this detail can receive applications
     */
    public function canReceiveApplications()
    {
        // Can receive if MPP is active and not yet fulfilled
        return $this->mpp->canReceiveApplications() && !$this->is_fulfilled;
    }

    /**
     * Get remaining need count
     */
    public function getRemainingNeed()
    {
        return $this->remaining_need;
    }

    /**
     * Mark as fulfilled
     */
    public function markAsFulfilled()
    {
        $this->update(['fulfilled_at' => now()]);

        // Check if all details are fulfilled, then close the MPP
        $this->checkAndCloseMPP();

        return true;
    }

    /**
     * Unmark fulfillment
     */
    public function unmarkFulfillment()
    {
        $this->update(['fulfilled_at' => null]);

        // Reopen MPP if it was closed
        if ($this->mpp->is_closed) {
            $this->mpp->reopen();
        }

        return true;
    }

    /**
     * Determine if should increment staff or non-staff based on MPP Detail needs
     * Priority: Based on which one is still needed (diff < 0 means still needed)
     * If both needed, choose the one with more negative diff (more needed)
     * If same diff, use agreement_type as tie-breaker (pkwtt = staff, others = non-staff)
     *
     * @param string|null $agreementType Optional agreement type for tie-breaker
     * @return bool True if should increment staff, false if non-staff
     */
    public function shouldIncrementStaff($agreementType = null)
    {
        $diffS = $this->diff_s; // existing_qty_s - plan_qty_s (negative means still needed)
        $diffNs = $this->diff_ns; // existing_qty_ns - plan_qty_ns (negative means still needed)

        // If only staff is needed (diff_s < 0, diff_ns >= 0)
        if ($diffS < 0 && $diffNs >= 0) {
            return true;
        }

        // If only non-staff is needed (diff_ns < 0, diff_s >= 0)
        if ($diffNs < 0 && $diffS >= 0) {
            return false;
        }

        // If both are needed (diff_s < 0 && diff_ns < 0)
        if ($diffS < 0 && $diffNs < 0) {
            // Choose the one with more negative diff (more needed)
            if ($diffS < $diffNs) {
                return true; // Staff needs more
            } elseif ($diffNs < $diffS) {
                return false; // Non-staff needs more
            } else {
                // Same diff, use agreement_type as tie-breaker
                // If agreement_type is pkwtt, prefer staff; otherwise prefer non-staff
                return ($agreementType === 'pkwtt');
            }
        }

        // If both are fulfilled (diff_s >= 0 && diff_ns >= 0)
        // Use agreement_type as fallback
        return ($agreementType === 'pkwtt');
    }

    /**
     * Increment existing quantity based on employee type (staff/non-staff)
     *
     * @param bool $isStaff True if staff, false if non-staff
     * @return void
     */
    public function incrementExistingQuantity($isStaff = true)
    {
        if ($isStaff) {
            $this->increment('existing_qty_s');
        } else {
            $this->increment('existing_qty_ns');
        }
    }

    /**
     * Auto-increment existing quantity based on MPP Detail needs
     * This method automatically determines whether to increment staff or non-staff
     * based on which one is still needed
     *
     * @param string|null $agreementType Optional agreement type for tie-breaker
     * @return void
     */
    public function autoIncrementExistingQuantity($agreementType = null)
    {
        $isStaff = $this->shouldIncrementStaff($agreementType);
        $this->incrementExistingQuantity($isStaff);
    }

    /**
     * Decrement existing quantity based on employee type (staff/non-staff)
     *
     * @param bool $isStaff True if staff, false if non-staff
     * @return void
     */
    public function decrementExistingQuantity($isStaff = true)
    {
        if ($isStaff) {
            $this->decrement('existing_qty_s');
            // Ensure it doesn't go below 0
            if ($this->existing_qty_s < 0) {
                $this->update(['existing_qty_s' => 0]);
            }
        } else {
            $this->decrement('existing_qty_ns');
            // Ensure it doesn't go below 0
            if ($this->existing_qty_ns < 0) {
                $this->update(['existing_qty_ns' => 0]);
            }
        }
    }

    /**
     * Auto-check fulfillment based on hired sessions
     */
    public function checkFulfillment()
    {
        $hiredCount = $this->hiredSessions()->count();
        // total_diff = existing - plan (negative means still needed)
        // needed = plan - existing = -total_diff
        $needed = -$this->total_diff;

        // If hired count meets or exceeds needed, mark as fulfilled
        if ($needed > 0 && $hiredCount >= $needed && !$this->is_fulfilled) {
            $this->markAsFulfilled();
        }
    }

    /**
     * Check if all MPP details are fulfilled and close MPP if yes
     */
    protected function checkAndCloseMPP()
    {
        $totalDetails = $this->mpp->details()->count();
        $fulfilledDetails = $this->mpp->details()->whereNotNull('fulfilled_at')->count();

        if ($totalDetails === $fulfilledDetails && $totalDetails > 0) {
            $this->mpp->close();
        }
    }

    /**
     * Scopes
     */
    public function scopeFulfilled($query)
    {
        return $query->whereNotNull('fulfilled_at');
    }

    public function scopeUnfulfilled($query)
    {
        return $query->whereNull('fulfilled_at');
    }

    public function scopeByPosition($query, $positionId)
    {
        return $query->where('position_id', $positionId);
    }

    /**
     * Check if this MPP detail requires theory test
     *
     * @return bool
     */
    public function requiresTheoryTest(): bool
    {
        return $this->requires_theory_test ?? false;
    }
}
