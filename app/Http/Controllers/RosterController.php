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
use App\Services\RosterBalancingService;
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
        $employeeStats = null;
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);
        $search = $request->get('search', '');

        if ($request->filled('project_id')) {
            $selectedProject = Project::find($request->project_id);
            $result = $this->getProjectEmployees($selectedProject, $search);
            $employees = $result['employees'];
            $employeeStats = $result['stats'];
        }

        return view('rosters.index', compact(
            'title',
            'projects',
            'selectedProject',
            'employees',
            'employeeStats',
            'year',
            'month',
            'search'
        ));
    }

    /**
     * Get employees for roster project
     * Returns all active employees from the selected project with search and pagination
     */
    private function getProjectEmployees($project, $search = '', $perPage = 20)
    {
        // Build query for active employees from the project
        $query = Administration::with(['employee', 'position.department', 'level', 'roster'])
            ->where('project_id', $project->id)
            ->where('is_active', 1);

        // Apply search filter
        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nik', 'like', "%{$search}%")
                    ->orWhereHas('employee', function ($employeeQuery) use ($search) {
                        $employeeQuery->where('fullname', 'like', "%{$search}%");
                    })
                    ->orWhereHas('position', function ($positionQuery) use ($search) {
                        $positionQuery->where('position_name', 'like', "%{$search}%")
                            ->orWhereHas('department', function ($departmentQuery) use ($search) {
                                $departmentQuery->where('department_name', 'like', "%{$search}%");
                            });
                    });
            });
        }

        // Get total count before pagination for statistics
        $totalCount = (clone $query)->count();

        // Get all employees for statistics (before pagination)
        $allEmployees = (clone $query)->get();

        // Apply pagination
        $employees = $query->orderBy('nik')->paginate($perPage)->withQueryString();

        // Auto-create roster for employees who have level with work_days configured
        foreach ($employees as $admin) {
            // Only create roster if employee has level with work_days
            if ($admin->level && $admin->level->work_days !== null && !$admin->roster) {
                $admin->roster = $this->createRosterForEmployee($admin);
            }
        }

        // Log total count for debugging
        Log::info('Roster Employee Count Debug', [
            'project_code' => $project->project_code,
            'total_active' => $totalCount,
            'with_level' => $allEmployees->where('level_id', '!=', null)->count(),
            'without_level' => $allEmployees->where('level_id', null)->count(),
            'with_level_and_work_days' => $allEmployees->filter(function ($admin) {
                return $admin->level && $admin->level->work_days !== null;
            })->count(),
            'with_roster' => $allEmployees->where('roster', '!=', null)->count(),
            'search' => $search,
            'per_page' => $perPage
        ]);

        // Prepare statistics for view (based on all employees, not just paginated)
        $stats = [
            'total_active' => $totalCount,
            'with_level' => $allEmployees->where('level_id', '!=', null)->count(),
            'without_level' => $allEmployees->where('level_id', null)->count(),
            'with_level_and_work_days' => $allEmployees->filter(function ($admin) {
                return $admin->level && $admin->level->work_days !== null;
            })->count(),
            'with_roster' => $allEmployees->where('roster', '!=', null)->count()
        ];

        return [
            'employees' => $employees,
            'stats' => $stats
        ];
    }

    /**
     * Auto-create roster for employee
     * Creates roster for all employees, even if they don't have level with work_days
     */
    private function createRosterForEmployee($administration)
    {
        // Check if roster already exists
        $existingRoster = Roster::where('administration_id', $administration->id)
            ->where('is_active', true)
            ->first();

        if ($existingRoster) {
            return $existingRoster;
        }

        // Create roster for employee (even without level with work_days)
        // This allows manual status updates for all employees
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
        try {
            $request->validate([
                'roster_id' => 'nullable|exists:rosters,id',
                'administration_id' => 'nullable|exists:administrations,id',
                'date' => 'required|date',
                'status_code' => 'required|in:D,N,OFF,S,I,A,C',
                'notes' => 'nullable|string|max:500'
            ]);

            $roster = null;

            // If roster_id is provided, use it
            if ($request->filled('roster_id')) {
                $roster = Roster::findOrFail($request->roster_id);
            }
            // If administration_id is provided, find or create roster
            elseif ($request->filled('administration_id')) {
                $administration = Administration::with(['level', 'roster'])->findOrFail($request->administration_id);

                // If roster exists, use it
                if ($administration->roster) {
                    $roster = $administration->roster;
                }
                // Try to create roster for employee
                else {
                    try {
                        $roster = $this->createRosterForEmployee($administration);

                        if (!$roster) {
                            return response()->json([
                                'success' => false,
                                'message' => 'Failed to create roster for this employee.'
                            ], 422);
                        }
                    } catch (\Exception $e) {
                        Log::error('Failed to create roster for employee', [
                            'administration_id' => $administration->id,
                            'error' => $e->getMessage()
                        ]);

                        return response()->json([
                            'success' => false,
                            'message' => 'Failed to create roster: ' . $e->getMessage()
                        ], 422);
                    }
                }
            }
            // Neither roster_id nor administration_id provided
            else {
                return response()->json([
                    'success' => false,
                    'message' => 'Either roster_id or administration_id must be provided.'
                ], 422);
            }

            // Update or create roster daily status
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
                    'name' => $status->getStatusName(),
                    'roster_id' => $roster->id
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Roster Update Status Error: ' . $e->getMessage(), [
                'request' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating status: ' . $e->getMessage()
            ], 500);
        }
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
     * Supports optional search parameter to export filtered results
     */
    public function export(Request $request)
    {
        $request->validate([
            'project_id' => 'required|exists:projects,id',
            'year' => 'required|integer|min:2020|max:2030',
            'month' => 'required|integer|min:1|max:12',
            'search' => 'nullable|string|max:255'
        ]);

        $project = Project::findOrFail($request->project_id);
        $year = $request->get('year');
        $month = $request->get('month');
        $search = $request->get('search', '');

        $filename = sprintf(
            'Roster_%s_%s_%s%s.xlsx',
            $project->project_code,
            Carbon::create($year, $month)->format('Y_m'),
            $search ? '_filtered_' : '',
            now()->format('YmdHis')
        );

        return Excel::download(new RosterExport($project, $year, $month, $search), $filename);
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

            // Log import results
            Log::info('Roster Import Completed', [
                'project_code' => $project->project_code,
                'year' => $year,
                'month' => $month,
                'imported_count' => $results['imported_count'],
                'has_errors' => $results['has_errors'],
                'error_count' => count($results['errors']),
                'errors' => $results['errors']
            ]);

            $message = "Successfully imported {$results['imported_count']} roster entries.";
            if ($results['has_errors']) {
                $message .= " " . count($results['errors']) . " error(s) occurred.";
            }

            return response()->json([
                'success' => true,
                'message' => $message,
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

    /**
     * Apply balancing untuk selected rosters
     */
    public function applyBalancing(Request $request)
    {
        $request->validate([
            'roster_ids' => 'required|array|min:1',
            'roster_ids.*' => 'required|exists:rosters,id',
            'adjustment_days' => 'required|integer|not_in:0',
            'reason' => 'required|string|max:500',
            'effective_date' => 'nullable|date'
        ]);

        try {
            $service = app(RosterBalancingService::class);
            $result = $service->applyBulkBalancing(
                $request->roster_ids,
                $request->adjustment_days,
                $request->reason,
                $request->effective_date
            );

            if ($result['success']) {
                return response()->json([
                    'success' => true,
                    'message' => 'Balancing applied successfully',
                    'data' => $result
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Some balancing failed',
                    'data' => $result
                ], 422);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get balancing preview untuk selected rosters
     */
    public function getBalancingPreview(Request $request)
    {
        $request->validate([
            'roster_ids' => 'required|array|min:1',
            'roster_ids.*' => 'required|exists:rosters,id',
            'adjustment_days' => 'required|integer|not_in:0',
            'effective_date' => 'nullable|date'
        ]);

        $service = app(RosterBalancingService::class);
        $previews = [];

        foreach ($request->roster_ids as $rosterId) {
            $roster = Roster::with(['employee', 'administration.level'])->find($rosterId);
            if (!$roster) continue;

            $currentWorkDays = $roster->getWorkDays();
            $adjustedWorkDays = $currentWorkDays + $request->adjustment_days;
            $estimate = $service->estimateNextPeriodicLeave(
                $roster,
                $request->effective_date ? Carbon::parse($request->effective_date) : now()
            );

            $previews[] = [
                'roster_id' => $rosterId,
                'employee_name' => $roster->employee->fullname ?? 'N/A',
                'current_work_days' => $currentWorkDays,
                'adjusted_work_days' => max(0, $adjustedWorkDays),
                'adjustment_days' => $request->adjustment_days,
                'estimate' => $estimate
            ];
        }

        return response()->json([
            'success' => true,
            'data' => $previews
        ]);
    }

    /**
     * Get balancing history untuk roster
     */
    public function getBalancingHistory($rosterId)
    {
        $roster = Roster::findOrFail($rosterId);
        $service = app(RosterBalancingService::class);

        $history = $service->getHistory($rosterId);

        return response()->json([
            'success' => true,
            'data' => $history->map(function ($adj) {
                return [
                    'id' => $adj->id,
                    'adjustment_type' => $adj->adjustment_type,
                    'adjusted_value' => $adj->adjusted_value,
                    'reason' => $adj->reason,
                    'created_at' => $adj->created_at->format('Y-m-d H:i:s'),
                    'description' => $adj->getAdjustmentDescription()
                ];
            })
        ]);
    }
}
