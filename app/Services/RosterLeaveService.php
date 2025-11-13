<?php

namespace App\Services;

use App\Models\Administration;
use App\Models\LeaveEntitlement;
use App\Models\LeaveType;
use App\Models\Roster;
use App\Models\RosterDailyStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RosterLeaveService
{
    /**
     * Get employees who are due for periodic leave
     * Menggunakan data dari roster_daily_status dengan status_code = 'C'
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
        $query = Administration::with(['employee', 'position.department', 'level', 'roster.dailyStatuses'])
            ->where('project_id', $projectId)
            ->where('is_active', 1)
            ->whereHas('roster', function ($q) {
                $q->where('is_active', 1);
            });

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
            
            // Skip if no level (required for roster pattern)
            if (!$admin->level) continue;

            // Get next off period from roster_daily_status
            $offPeriod = $this->getNextOffPeriodFromRoster($admin->roster, $daysAhead);

            if (!$offPeriod) continue;

            $nextOffStart = $offPeriod['start'];
            $nextOffEnd = $offPeriod['end'];
            $offDays = $offPeriod['days'];

            // Calculate days until off
            $daysUntilOff = $today->diffInDays($nextOffStart, false);

            // Check if within lookahead window
            // Hanya ambil jika tanggal C pertama dalam range days_ahead
            $isDue = $daysUntilOff >= 0 && $daysUntilOff <= $daysAhead;

            // Get roster pattern safely
            $rosterPattern = $admin->level ? $admin->level->getRosterPattern() : 'No Pattern';

            // Get roster notes for the first day of off period
            $rosterNote = null;
            $firstDayStatus = RosterDailyStatus::where('roster_id', $admin->roster->id)
                ->where('date', $nextOffStart->format('Y-m-d'))
                ->where('status_code', 'C')
                ->first();
            
            if ($firstDayStatus && $firstDayStatus->notes) {
                $rosterNote = $firstDayStatus->notes;
            }

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
     * Get next off period from roster_daily_status
     * Membaca data dari table roster_daily_status dengan status_code = 'C'
     *
     * @param Roster $roster
     * @param int $daysAhead
     * @return array|null
     */
    private function getNextOffPeriodFromRoster($roster, $daysAhead = 7)
    {
        $today = now()->startOfDay();
        $targetDate = $today->copy()->addDays($daysAhead);

        // Cari tanggal C pertama setelah today() dalam range days_ahead
        $firstLeaveDate = RosterDailyStatus::where('roster_id', $roster->id)
            ->where('status_code', 'C')
            ->where('date', '>=', $today)
            ->where('date', '<=', $targetDate)
            ->orderBy('date', 'asc')
            ->first();

        if (!$firstLeaveDate) {
            return null;
        }

        $startDate = Carbon::parse($firstLeaveDate->date)->startOfDay();

        // Cari semua tanggal C yang berurutan mulai dari tanggal pertama
        // untuk menentukan end_date dan total days
        $consecutiveLeaveDates = RosterDailyStatus::where('roster_id', $roster->id)
            ->where('status_code', 'C')
            ->where('date', '>=', $startDate)
            ->orderBy('date', 'asc')
            ->get();

        $endDate = $startDate;
        $totalDays = 1;

        // Hitung consecutive leave days
        foreach ($consecutiveLeaveDates as $index => $leaveDate) {
            if ($index == 0) continue; // Skip first date

            $currentDate = Carbon::parse($leaveDate->date)->startOfDay();
            $expectedNextDate = $endDate->copy()->addDay();

            // Check if consecutive
            if ($currentDate->equalTo($expectedNextDate)) {
                $endDate = $currentDate;
                $totalDays++;
            } else {
                // Break if not consecutive
                break;
            }
        }

        return [
            'start' => $startDate,
            'end' => $endDate,
            'days' => $totalDays
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
