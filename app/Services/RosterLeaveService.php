<?php

namespace App\Services;

use App\Models\Administration;
use App\Models\LeaveEntitlement;
use App\Models\LeaveType;
use App\Models\Roster;
use App\Models\RosterDetail;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RosterLeaveService
{
    /**
     * Get employees who are due for periodic leave
     * Menggunakan data dari roster_details dengan leave_start dan leave_end
     *
     * @param int $projectId
     * @param int $daysAhead Number of days to look ahead (default 7)
     * @param int|null $departmentId Optional: filter by department ID
     * @return \Illuminate\Support\Collection
     */
    public function getEmployeesDueForLeave($projectId, $daysAhead = 7, $departmentId = null)
    {
        $today = now()->startOfDay();
        $targetDate = $today->copy()->addDays($daysAhead);

        // Get all active roster employees in project
        $query = Administration::with(['employee', 'position.department', 'level', 'roster.rosterDetails'])
            ->where('project_id', $projectId)
            ->where('is_active', 1)
            ->whereHas('roster');

        // Filter by department if provided
        if ($departmentId) {
            $query->whereHas('position', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            });
        }

        $administrations = $query->get();

        $employeesDue = [];

        foreach ($administrations as $admin) {
            // Skip if no roster or employee
            if (!$admin->roster || !$admin->employee) continue;

            // Skip if no level (required for Roster Cycle)
            if (!$admin->level) continue;

            // Get next off period from roster_details
            $offPeriod = $this->getNextOffPeriodFromRoster($admin->roster, $daysAhead);

            if (!$offPeriod) continue;

            $nextOffStart = $offPeriod['start'];
            $nextOffEnd = $offPeriod['end'];
            $offDays = $offPeriod['days'];

            // Calculate days until off
            $daysUntilOff = $today->diffInDays($nextOffStart, false);

            // Check if within lookahead window
            $isDue = $daysUntilOff >= 0 && $daysUntilOff <= $daysAhead;

            // Get Roster Cycle safely
            $rosterPattern = $admin->level ? $admin->level->getRosterPattern() : 'No Pattern';

            // Get roster notes from roster_detail remarks
            $rosterNote = $offPeriod['remarks'] ?? null;

            $employeesDue[] = [
                'employee_id' => $admin->employee_id,
                'employee' => $admin->employee,
                'administration' => $admin,
                'roster' => $admin->roster,
                'off_start_date' => $nextOffStart,
                'off_end_date' => $nextOffEnd,
                'off_days' => $offDays,
                'roster_pattern' => $rosterPattern,
                'roster_note' => $rosterNote,
                'is_due' => $isDue,
                'days_until_off' => $daysUntilOff
            ];
        }

        // Sort by days until off (closest first)
        usort($employeesDue, function ($a, $b) {
            return $a['days_until_off'] <=> $b['days_until_off'];
        });

        return collect($employeesDue);
    }

    /**
     * Get next off period from roster_details
     * Membaca data dari table roster_details dengan leave_start dan leave_end
     *
     * @param Roster $roster
     * @param int $daysAhead
     * @return array|null
     */
    private function getNextOffPeriodFromRoster($roster, $daysAhead = 7)
    {
        $today = now()->startOfDay();
        $targetDate = $today->copy()->addDays($daysAhead);

        // Cari roster_detail dengan leave_start yang akan datang dalam range days_ahead
        $nextRosterDetail = RosterDetail::where('roster_id', $roster->id)
            ->whereNotNull('leave_start')
            ->whereNotNull('leave_end')
            ->where('leave_start', '>=', $today)
            ->where('leave_start', '<=', $targetDate)
            ->orderBy('leave_start', 'asc')
            ->first();

        if (!$nextRosterDetail) {
            return null;
        }

        $startDate = Carbon::parse($nextRosterDetail->leave_start)->startOfDay();
        $endDate = Carbon::parse($nextRosterDetail->leave_end)->startOfDay();
        
        // Calculate total days (inclusive of both start and end)
        $totalDays = $startDate->diffInDays($endDate) + 1;

        return [
            'start' => $startDate,
            'end' => $endDate,
            'days' => $totalDays,
            'remarks' => $nextRosterDetail->remarks
        ];
    }

    /**
     * Check if employee has sufficient leave entitlement
     *
     * @param int $employeeId
     * @param int $leaveTypeId
     * @param int $requiredDays
     * @return bool
     */
    public function hasSufficientEntitlement($employeeId, $leaveTypeId, $requiredDays)
    {
        $entitlement = LeaveEntitlement::where('employee_id', $employeeId)
            ->where('leave_type_id', $leaveTypeId)
            ->where('period_start', '<=', now())
            ->where('period_end', '>=', now())
            ->first();

        if (!$entitlement) {
            return false;
        }

        return $entitlement->remaining_days >= $requiredDays;
    }

    /**
     * Get periodic leave type
     *
     * @return \App\Models\LeaveType|null
     */
    public function getPeriodicLeaveType()
    {
        return LeaveType::where('category', 'periodic')
            ->where('is_active', true)
            ->first();
    }
}
