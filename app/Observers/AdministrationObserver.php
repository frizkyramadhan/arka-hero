<?php

namespace App\Observers;

use App\Models\Administration;
use App\Models\Roster;
use App\Models\RosterDailyStatus;
use Illuminate\Support\Facades\Log;

class AdministrationObserver
{
    /**
     * Handle the Administration "created" event.
     */
    public function created(Administration $administration): void
    {
        $this->handleRosterAdjustment($administration);
    }

    /**
     * Handle the Administration "updated" event.
     */
    public function updated(Administration $administration): void
    {
        // Only handle roster adjustment if project_id or level_id changed
        if ($administration->isDirty(['project_id', 'level_id'])) {
            $this->handleRosterAdjustment($administration);
        }
    }

    /**
     * Handle roster adjustment when administration changes
     */
    private function handleRosterAdjustment(Administration $administration): void
    {
        // Only process if this is the active administration
        if (!$administration->is_active) {
            return;
        }

        // 1. Cleanup old rosters for this employee
        $this->cleanupOldRosters($administration->employee_id);

        // 2. Create new roster if project is roster-based
        if ($administration->project && $administration->project->leave_type === 'roster') {
            $this->createNewRoster($administration);
        }
    }

    /**
     * Cleanup old rosters for employee
     */
    private function cleanupOldRosters(string $employeeId): void
    {
        $oldRosters = Roster::where('employee_id', $employeeId)->get();

        $deletedCount = 0;
        $deletedStatusCount = 0;

        foreach ($oldRosters as $roster) {
            // Count daily statuses before deletion
            $statusCount = $roster->dailyStatuses->count();

            // Delete all daily statuses first
            RosterDailyStatus::where('roster_id', $roster->id)->delete();

            // Delete the roster
            $roster->delete();

            $deletedCount++;
            $deletedStatusCount += $statusCount;
        }

        // Log cleanup activity if there were deletions
        if ($deletedCount > 0) {
            Log::info("Roster cleanup completed: {$deletedCount} rosters and {$deletedStatusCount} daily statuses deleted for employee {$employeeId}");
        }
    }

    /**
     * Create new roster for administration
     */
    private function createNewRoster(Administration $administration): void
    {
        // Check if level has roster configuration
        if (!$administration->level || !$administration->level->hasRosterConfig()) {
            Log::info("No roster config found for level: " . ($administration->level->name ?? 'Unknown'));
            return;
        }

        try {
            Roster::create([
                'employee_id' => $administration->employee_id,
                'administration_id' => $administration->id,
                'start_date' => now()->startOfMonth(),
                'end_date' => now()->addMonths(3)->endOfMonth(),
                'cycle_no' => 1,
                'adjusted_days' => 0,
                'is_active' => true
            ]);

            Log::info("New roster created for employee {$administration->employee_id} with level {$administration->level->name}");
        } catch (\Exception $e) {
            Log::error("Failed to create roster for employee {$administration->employee_id}: " . $e->getMessage());
        }
    }
}
