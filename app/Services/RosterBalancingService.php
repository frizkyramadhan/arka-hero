<?php

namespace App\Services;

use App\Models\Roster;
use App\Models\RosterAdjustment;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RosterBalancingService
{
    /**
     * Apply manual balancing untuk work_days
     */
    public function applyBalancing($rosterId, $days, $reason, $effectiveDate = null)
    {
        $roster = Roster::findOrFail($rosterId);
        
        // Validate: days tidak boleh 0
        if ($days == 0) {
            throw new \InvalidArgumentException('Adjustment days cannot be zero');
        }
        
        // Create adjustment record
        $adjustment = RosterAdjustment::create([
            'roster_id' => $rosterId,
            'leave_request_id' => null, // Manual balancing
            'adjustment_type' => $days > 0 ? '+days' : '-days',
            'adjusted_value' => abs($days),
            'reason' => "Manual balancing: {$reason}",
        ]);
        
        // Update roster adjusted_days
        $roster->updateAdjustedDays();
        
        return $adjustment;
    }
    
    /**
     * Apply balancing untuk multiple rosters
     */
    public function applyBulkBalancing(array $rosterIds, $days, $reason, $effectiveDate = null)
    {
        $results = [];
        $errors = [];
        
        foreach ($rosterIds as $rosterId) {
            try {
                $adjustment = $this->applyBalancing($rosterId, $days, $reason, $effectiveDate);
                $results[] = [
                    'roster_id' => $rosterId,
                    'success' => true,
                    'adjustment_id' => $adjustment->id
                ];
            } catch (\Exception $e) {
                $errors[] = [
                    'roster_id' => $rosterId,
                    'error' => $e->getMessage()
                ];
            }
        }
        
        return [
            'success' => count($errors) === 0,
            'results' => $results,
            'errors' => $errors
        ];
    }
    
    /**
     * Estimate next periodic leave date berdasarkan adjusted work_days
     */
    public function estimateNextPeriodicLeave($roster, $fromDate = null)
    {
        $fromDate = $fromDate ? Carbon::parse($fromDate) : now();
        $adjustedWorkDays = $roster->getAdjustedWorkDays();
        $offDays = $roster->getOffDays(); // Tetap 14 hari
        
        // Hitung kapan cycle berikutnya selesai
        $workPeriodEnd = $fromDate->copy()->addDays($adjustedWorkDays);
        
        // Periodic leave mulai setelah work_days selesai
        $periodicLeaveStart = $workPeriodEnd->copy()->addDay();
        $periodicLeaveEnd = $periodicLeaveStart->copy()->addDays($offDays - 1);
        
        return [
            'work_period_start' => $fromDate,
            'work_period_end' => $workPeriodEnd,
            'periodic_leave_start' => $periodicLeaveStart,
            'periodic_leave_end' => $periodicLeaveEnd,
            'adjusted_work_days' => $adjustedWorkDays,
            'off_days' => $offDays,
            'total_cycle_days' => $adjustedWorkDays + $offDays
        ];
    }
    
    /**
     * Get balancing history untuk roster
     */
    public function getHistory($rosterId)
    {
        return RosterAdjustment::where('roster_id', $rosterId)
            ->whereNull('leave_request_id') // Hanya manual balancing
            ->orderBy('created_at', 'desc')
            ->get();
    }
}

