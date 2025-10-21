<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Roster;
use App\Models\Project;
use Illuminate\Http\Request;
use App\Models\Administration;
use App\Models\RosterDailyStatus;
use App\Exports\RosterExport;
use App\Imports\RosterImport;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;

class RosterController extends Controller
{
    /**
     * Display roster management page with project filter
     */
    public function index(Request $request)
    {
        $title = 'Roster Management';
        $projects = Project::where('leave_type', 'roster')
            ->where('project_status', 1)
            ->orderBy('project_code')
            ->get();

        $selectedProject = null;
        $employees = collect();
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        if ($request->filled('project_id')) {
            $selectedProject = Project::find($request->project_id);
            $employees = $this->getProjectEmployees($selectedProject);
        }

        return view('rosters.index', compact(
            'title',
            'projects',
            'selectedProject',
            'employees',
            'year',
            'month'
        ));
    }

    /**
     * Get employees for roster project
     */
    private function getProjectEmployees($project)
    {
        $employees = Administration::with(['employee', 'position', 'level', 'roster'])
            ->where('project_id', $project->id)
            ->where('is_active', 1)
            ->whereHas('level', function ($query) {
                $query->whereNotNull('work_days');
            })
            ->orderBy('nik')
            ->get();

        // Auto-create roster for employees who don't have one
        foreach ($employees as $admin) {
            if (!$admin->roster) {
                $admin->roster = $this->createRosterForEmployee($admin);
            }
        }

        // Debug: Log roster information for each employee
        $debugInfo = [];
        foreach ($employees as $admin) {
            // Try to get employee name directly from database
            $employeeName = DB::table('employees')
                ->where('id', $admin->employee_id)
                ->value('fullname');

            $debugInfo[] = [
                'name' => $employeeName ?? 'Unknown',
                'nik' => $admin->nik ?? 'Unknown',
                'level' => $admin->level->name ?? 'Unknown',
                'roster_pattern' => $admin->level->getRosterPattern() ?? 'No Roster',
                'work_days' => $admin->level->work_days ?? 0,
                'off_days' => $admin->level->off_days ?? 0,
                'has_roster' => $admin->roster ? 'Yes' : 'No',
                'roster_id' => $admin->roster->id ?? null,
                'employee_id' => $admin->employee_id,
                'employee_relation' => $admin->employee ? 'Loaded' : 'Not Loaded'
            ];
        }

        Log::info('Roster Debug Info:', $debugInfo);

        return $employees;
    }

    /**
     * Auto-create roster for employee
     */
    private function createRosterForEmployee($administration)
    {
        // Check if level has roster configuration
        if (!$administration->level || !$administration->level->hasRosterConfig()) {
            return null;
        }

        return Roster::create([
            'employee_id' => $administration->employee_id,
            'administration_id' => $administration->id,
            'start_date' => now()->startOfMonth(),
            'end_date' => now()->addMonths(3)->endOfMonth(),
            'cycle_no' => 1,
            'adjusted_days' => 0,
            'is_active' => true
        ]);
    }

    /**
     * Update roster status via AJAX
     */
    public function updateStatus(Request $request)
    {
        $request->validate([
            'roster_id' => 'required|exists:rosters,id',
            'date' => 'required|date',
            'status_code' => 'required|in:D,N,OFF,S,I,A,C',
            'notes' => 'nullable|string|max:500'
        ]);

        $roster = Roster::findOrFail($request->roster_id);

        $status = RosterDailyStatus::updateOrCreate(
            [
                'roster_id' => $roster->id,
                'date' => $request->date
            ],
            [
                'status_code' => $request->status_code,
                'notes' => $request->notes
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Status updated successfully',
            'data' => [
                'status' => $status->status_code,
                'color' => $status->getStatusColor(),
                'name' => $status->getStatusName()
            ]
        ]);
    }

    /**
     * Bulk update statuses
     */
    public function bulkUpdate(Request $request)
    {
        $request->validate([
            'updates' => 'required|array',
            'updates.*.roster_id' => 'required|exists:rosters,id',
            'updates.*.date' => 'required|date',
            'updates.*.status_code' => 'required|in:D,N,OFF,S,I,A,C'
        ]);

        $updated = 0;
        foreach ($request->updates as $update) {
            RosterDailyStatus::updateOrCreate(
                [
                    'roster_id' => $update['roster_id'],
                    'date' => $update['date']
                ],
                [
                    'status_code' => $update['status_code'],
                    'notes' => $update['notes'] ?? null
                ]
            );
            $updated++;
        }

        return response()->json([
            'success' => true,
            'message' => "Updated {$updated} roster entries"
        ]);
    }

    /**
     * Export roster to Excel
     */
    public function export(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'year' => 'required|integer|min:2020|max:2030',
            'month' => 'required|integer|min:1|max:12'
        ]);

        $project = Project::findOrFail($request->project_id);
        $year = $request->get('year');
        $month = $request->get('month');

        $filename = sprintf(
            'Roster_%s_%s_%s.xlsx',
            $project->project_code,
            Carbon::create($year, $month)->format('Y_m'),
            now()->format('YmdHis')
        );

        return Excel::download(new RosterExport($project, $year, $month), $filename);
    }

    /**
     * Import roster from Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'year' => 'required|integer|min:2020|max:2030',
            'month' => 'required|integer|min:1|max:12',
            'file' => 'required|file|mimes:xlsx,xls|max:10240', // Max 10MB
            'clear_existing' => 'boolean'
        ]);

        $project = Project::findOrFail($request->project_id);
        $year = $request->get('year');
        $month = $request->get('month');
        $clearExisting = $request->get('clear_existing', false);

        try {
            $import = new RosterImport($project, $year, $month);

            // Clear existing data if requested
            if ($clearExisting) {
                $deletedCount = $import->clearExistingData();
                Log::info("Cleared {$deletedCount} existing roster entries before import");
            }

            // Import the file
            Excel::import($import, $request->file('file'));

            $results = $import->getImportResults();

            return response()->json([
                'success' => true,
                'message' => 'Roster imported successfully',
                'data' => $results
            ]);
        } catch (\Exception $e) {
            Log::error('Roster Import Failed: ' . $e->getMessage(), [
                'project_id' => $project->id,
                'year' => $year,
                'month' => $month,
                'file' => $request->file('file')->getClientOriginalName()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Import failed: ' . $e->getMessage(),
                'data' => [
                    'project_code' => $project->project_code,
                    'year' => $year,
                    'month' => $month
                ]
            ], 422);
        }
    }

    /**
     * Get roster data for employees for specific month/year
     */
    private function getRosterData($employees, $year, $month)
    {
        $rosterData = [];

        foreach ($employees as $admin) {
            if (!$admin->roster) {
                continue;
            }

            $rosterData[$admin->employee_id] = [];

            $daysInMonth = Carbon::create($year, $month)->daysInMonth;

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $currentDate = Carbon::create($year, $month, $day);

                $dayStatus = RosterDailyStatus::where('roster_id', $admin->roster->id)
                    ->where('date', $currentDate->format('Y-m-d'))
                    ->first();

                $rosterData[$admin->employee_id][$day] = [
                    'status' => $dayStatus ? $dayStatus->status_code : 'D',
                    'notes' => $dayStatus ? $dayStatus->notes : null,
                    'date' => $currentDate->format('Y-m-d')
                ];
            }
        }

        return $rosterData;
    }

    /**
     * Generate CSV data for export
     */
    private function generateCsvData($employees, $rosterData, $year, $month)
    {
        $daysInMonth = Carbon::create($year, $month)->daysInMonth;

        // CSV Headers
        $headers = ['NO', 'Name', 'NIK', 'Position'];
        for ($day = 1; $day <= $daysInMonth; $day++) {
            $headers[] = $day;
        }

        $csv = implode(',', $headers) . "\n";

        // CSV Data
        foreach ($employees as $index => $admin) {
            $row = [
                $index + 1,
                '"' . $admin->employee->fullname . '"',
                $admin->nik,
                '"' . $admin->position->position_name . '"'
            ];

            for ($day = 1; $day <= $daysInMonth; $day++) {
                $dayData = $rosterData[$admin->employee_id][$day] ?? ['status' => 'D'];
                $row[] = $dayData['status'];
            }

            $csv .= implode(',', $row) . "\n";
        }

        return $csv;
    }

    /**
     * Clear roster data for specific project, year, and month
     */
    public function clearRoster(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'year' => 'required|integer|min:2020|max:2030',
            'month' => 'required|integer|min:1|max:12'
        ]);

        $project = Project::findOrFail($request->project_id);
        $year = $request->get('year');
        $month = $request->get('month');

        // Get all rosters for the project
        $rosters = Roster::whereHas('administration', function ($query) use ($project) {
            $query->where('project_id', $project->id);
        })->get();

        $deletedCount = 0;

        // Delete roster daily statuses for the specified month/year
        foreach ($rosters as $roster) {
            $startDate = Carbon::create($year, $month, 1)->startOfMonth();
            $endDate = Carbon::create($year, $month, 1)->endOfMonth();

            $deleted = RosterDailyStatus::where('roster_id', $roster->id)
                ->whereBetween('date', [$startDate, $endDate])
                ->delete();

            $deletedCount += $deleted;
        }

        return response()->json([
            'success' => true,
            'message' => "Cleared {$deletedCount} roster entries for {$project->project_code} - {$year}/{$month}",
            'data' => [
                'deleted_count' => $deletedCount,
                'project_code' => $project->project_code,
                'year' => $year,
                'month' => $month
            ]
        ]);
    }
}
