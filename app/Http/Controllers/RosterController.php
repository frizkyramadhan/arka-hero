<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Roster;
use App\Models\Project;
use App\Models\RosterDetail;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use App\Models\Administration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RosterExport;
use App\Imports\RosterImport;
use Maatwebsite\Excel\Validators\ValidationException;

class RosterController extends Controller
{
    /**
     * Display roster management page
     */
    public function index(Request $request)
    {
        $title = 'Roster Management';

        // Get current user
        $user = auth()->user();

        // Check if user is administrator
        $isAdministrator = $user && $user->roles->pluck('name')->contains('administrator');

        // Get roster-type projects filtered by user projects
        $query = Project::where('leave_type', 'roster')
            ->where('project_status', 1);

        // Filter by user projects if user is not administrator
        if ($user && !$isAdministrator) {
            $userProjectIds = $user->projects->pluck('id')->toArray();
            if (!empty($userProjectIds)) {
                $query->whereIn('id', $userProjectIds);
            } else {
                // If user has no projects, return empty collection
                $query->whereRaw('1 = 0');
            }
        }

        $projects = $query->orderBy('project_code')->get();

        $selectedProject = null;
        $employees = collect();
        $search = $request->get('search', '');

        if ($request->filled('project_id')) {
            $selectedProject = Project::find($request->project_id);

            // Validate that user has access to this project (unless administrator)
            if ($selectedProject && $user && !$isAdministrator) {
                $userProjectIds = $user->projects->pluck('id')->toArray();
                if (!in_array($selectedProject->id, $userProjectIds)) {
                    // User doesn't have access to this project, reset selection
                    $selectedProject = null;
                }
            }

            // Get employees with roster-type levels
            $query = Administration::with([
                'employee',
                'position.department',
                'level',
                'roster' => function ($q) {
                    $q->withCount('rosterDetails');
                }
            ])
                ->where('project_id', $selectedProject->id)
                ->where('is_active', 1);

            // Apply search filter
            if (!empty($search)) {
                $query->where(function ($q) use ($search) {
                    $q->where('nik', 'like', "%{$search}%")
                        ->orWhereHas('employee', function ($employeeQuery) use ($search) {
                            $employeeQuery->where('fullname', 'like', "%{$search}%");
                        });
                });
            }

            $employees = $query->orderBy('nik')->paginate(20)->withQueryString();
        }

        return view('rosters.index', compact(
            'title',
            'projects',
            'selectedProject',
            'employees',
            'search'
        ));
    }

    /**
     * Show roster details for employee
     */
    public function show($rosterId)
    {
        $roster = Roster::with([
            'employee',
            'administration.level',
            'administration.position.department',
            'administration.project',
            'rosterDetails' => function ($q) {
                $q->orderBy('cycle_no');
            }
        ])->findOrFail($rosterId);

        $title = 'Roster Details - ' . ($roster->employee->fullname ?? 'N/A');

        return view('rosters.show', compact('title', 'roster'));
    }

    /**
     * Create roster for employee
     */
    public function store(Request $request)
    {
        $request->validate([
            'administration_id' => 'required|exists:administrations,id'
        ]);

        try {
            $administration = Administration::with('employee', 'level')->findOrFail($request->administration_id);

            // Check if roster already exists
            $existingRoster = Roster::where('administration_id', $administration->id)->first();
            if ($existingRoster) {
                return response()->json([
                    'success' => false,
                    'message' => 'Roster already exists for this employee'
                ], 422);
            }

            // Check if level has roster configuration
            if (!$administration->level || !$administration->level->hasRosterConfig()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Employee level does not have roster configuration'
                ], 422);
            }

            $roster = Roster::create([
                'employee_id' => $administration->employee_id,
                'administration_id' => $administration->id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Roster created successfully',
                'data' => [
                    'roster_id' => $roster->id
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Roster Create Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to create roster: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete roster
     */
    public function destroy($rosterId)
    {
        try {
            $roster = Roster::with('rosterDetails')->findOrFail($rosterId);

            // Delete all related roster details first (should cascade automatically, but being explicit)
            $roster->rosterDetails()->delete();

            // Delete the roster
            $roster->delete();

            return response()->json([
                'success' => true,
                'message' => 'Roster deleted successfully'
            ]);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            Log::error('Roster Delete Error: Roster not found', ['roster_id' => $rosterId]);
            return response()->json([
                'success' => false,
                'message' => 'Roster not found'
            ], 404);
        } catch (\Exception $e) {
            Log::error('Roster Delete Error: ' . $e->getMessage(), [
                'roster_id' => $rosterId,
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete roster: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get cycle detail for editing
     */
    public function getCycle($cycleId)
    {
        try {
            $rosterDetail = RosterDetail::findOrFail($cycleId);

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $rosterDetail->id,
                    'cycle_no' => $rosterDetail->cycle_no,
                    'work_start' => $rosterDetail->work_start->format('Y-m-d'),
                    'work_end' => $rosterDetail->work_end->format('Y-m-d'),
                    'adjusted_days' => $rosterDetail->adjusted_days,
                    'leave_start' => $rosterDetail->leave_start ? $rosterDetail->leave_start->format('Y-m-d') : null,
                    'leave_end' => $rosterDetail->leave_end ? $rosterDetail->leave_end->format('Y-m-d') : null,
                    'remarks' => $rosterDetail->remarks,
                    'status' => $rosterDetail->status
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Roster Get Cycle Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get cycle: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Add cycle to roster
     */
    public function addCycle(Request $request, $rosterId)
    {
        $request->validate([
            'work_start' => 'required|date',
            'work_end' => 'required|date|after:work_start',
            'adjusted_days' => 'nullable|integer',
            'leave_start' => 'nullable|date|after_or_equal:work_end',
            'leave_end' => 'nullable|date|after:leave_start',
            'remarks' => 'nullable|string|max:1000'
        ]);

        try {
            $roster = Roster::with('rosterDetails')->findOrFail($rosterId);

            // Get next cycle number
            $nextCycleNo = $roster->rosterDetails()->max('cycle_no') + 1;

            $rosterDetail = RosterDetail::create([
                'roster_id' => $roster->id,
                'cycle_no' => $nextCycleNo,
                'work_start' => $request->work_start,
                'work_end' => $request->work_end,
                'adjusted_days' => $request->adjusted_days ?? 0,
                'leave_start' => $request->leave_start,
                'leave_end' => $request->leave_end,
                'remarks' => $request->remarks
            ]);

            // Auto-update status based on dates
            $rosterDetail->updateStatus();

            return response()->json([
                'success' => true,
                'message' => 'Cycle added successfully',
                'data' => $rosterDetail->load('roster')
            ]);
        } catch (\Exception $e) {
            Log::error('Roster Add Cycle Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to add cycle: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update cycle details
     */
    public function updateCycle(Request $request, $cycleId)
    {
        $request->validate([
            'work_start' => 'required|date',
            'work_end' => 'required|date|after:work_start',
            'adjusted_days' => 'nullable|integer',
            'leave_start' => 'nullable|date|after_or_equal:work_end',
            'leave_end' => 'nullable|date|after:leave_start',
            'remarks' => 'nullable|string|max:1000'
        ]);

        try {
            $rosterDetail = RosterDetail::findOrFail($cycleId);

            $rosterDetail->update([
                'work_start' => $request->work_start,
                'work_end' => $request->work_end,
                'adjusted_days' => $request->adjusted_days ?? 0,
                'leave_start' => $request->leave_start,
                'leave_end' => $request->leave_end,
                'remarks' => $request->remarks
            ]);

            // Auto-update status based on dates
            $rosterDetail->updateStatus();

            return response()->json([
                'success' => true,
                'message' => 'Cycle updated successfully',
                'data' => $rosterDetail->fresh()->load('roster')
            ]);
        } catch (\Exception $e) {
            Log::error('Roster Update Cycle Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to update cycle: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete cycle
     */
    public function deleteCycle($cycleId)
    {
        try {
            $rosterDetail = RosterDetail::findOrFail($cycleId);
            $rosterDetail->delete();

            return response()->json([
                'success' => true,
                'message' => 'Cycle deleted successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Roster Delete Cycle Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete cycle: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get roster statistics
     */
    public function getStatistics($rosterId)
    {
        try {
            $roster = Roster::with('rosterDetails')->findOrFail($rosterId);

            $accumulatedLeave = $roster->getTotalAccumulatedLeave();
            $leaveTaken = $roster->getTotalLeaveTaken();
            $fbRatio = $roster->getFbCycleRatio();
            $workDaysDiff = $fbRatio > 0 ? ($accumulatedLeave - $leaveTaken) / $fbRatio : 0;

            $statistics = [
                'completed_cycles' => $roster->rosterDetails()->where('leave_end', '<', now())->count(),
                'active_cycle' => $roster->currentDetail,
                'total_work_days' => $roster->rosterDetails()->sum(DB::raw('DATEDIFF(work_end, work_start) + 1')),
                'total_leave_days' => $roster->rosterDetails()->whereNotNull('leave_start')->sum(DB::raw('DATEDIFF(leave_end, leave_start) + 1')),
                'total_accumulated_leave' => $accumulatedLeave,
                'total_leave_taken' => $leaveTaken,
                'leave_balance' => $roster->getLeaveBalance(),
                'work_days_diff' => $workDaysDiff,
                'fb_cycle_ratio' => $fbRatio,
                'roster_pattern' => $roster->getRosterPatternDisplay()
            ];

            return response()->json([
                'success' => true,
                'data' => $statistics
            ]);
        } catch (\Exception $e) {
            Log::error('Roster Statistics Error: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to get statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Export roster data to Excel
     */
    public function export(Request $request)
    {
        $projectId = $request->get('project_id');
        $fileName = 'roster-export-' . date('Y-m-d') . '.xlsx';

        return Excel::download(new RosterExport($projectId), $fileName);
    }

    /**
     * Import roster data from Excel
     */
    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls|max:10240', // 10MB max
        ], [
            'file.required' => 'Please select a file to import.',
            'file.mimes' => 'The file must be a file of type: xlsx, xls.',
            'file.max' => 'The file may not be greater than 10MB.'
        ]);

        try {
            $import = new RosterImport();
            Excel::import($import, $request->file('file'));

            $successCount = $import->getSuccessCount();
            $skippedCount = $import->getSkippedCount();
            $errors = $import->getErrors();

            if (empty($errors)) {
                return redirect()->route('rosters.index')
                    ->with('toast_success', "Successfully imported {$successCount} roster cycle records.");
            } else {
                // Format errors for display
                $formattedFailures = collect();
                foreach ($errors as $error) {
                    $formattedFailures->push([
                        'sheet' => 'Roster',
                        'row' => $error['row'],
                        'attribute' => 'NIK: ' . ($error['nik'] ?: 'N/A'),
                        'value' => '',
                        'errors' => implode(', ', $error['errors']),
                    ]);
                }

                $message = "Imported {$successCount} records successfully. Skipped {$skippedCount} records due to validation errors.";
                return back()
                    ->with('failures', $formattedFailures)
                    ->with('toast_warning', $message);
            }
        } catch (ValidationException $e) {
            $failures = collect();
            foreach ($e->failures() as $failure) {
                $failures->push([
                    'sheet' => $failure->sheet() ?? 'Roster',
                    'row' => $failure->row(),
                    'attribute' => implode(', ', $failure->attribute()),
                    'value' => $failure->values()[$failure->attribute()[0]] ?? '',
                    'errors' => implode(', ', $failure->errors()),
                ]);
            }

            return back()
                ->with('failures', $failures)
                ->with('toast_error', 'Validation errors occurred during import.');
        } catch (\Exception $e) {
            Log::error('Roster Import Error: ' . $e->getMessage());
            return back()
                ->with('toast_error', 'Failed to import roster: ' . $e->getMessage());
        }
    }

    /**
     * Display roster calendar view
     */
    public function calendar(Request $request)
    {
        $title = 'Roster Calendar View';

        // Get all roster-type projects
        $projects = Project::where('leave_type', 'roster')
            ->where('project_status', 1)
            ->orderBy('project_code')
            ->get();

        $selectedProject = null;
        $year = (int)$request->get('year', date('Y'));
        $month = (int)$request->get('month', date('m'));
        $calendarData = [];

        if ($request->filled('project_id')) {
            $selectedProject = Project::find($request->project_id);

            // Get all administrations with rosters in the project
            $administrations = Administration::with([
                'employee',
                'position',
                'level',
                'roster.rosterDetails' => function ($q) {
                    $q->orderBy('cycle_no');
                }
            ])
                ->where('project_id', $selectedProject->id)
                ->where('is_active', 1)
                ->whereHas('roster')
                ->orderBy('nik')
                ->get();

            // Get number of days in the month
            $daysInMonth = Carbon::create($year, $month, 1)->daysInMonth;
            $firstDay = Carbon::create($year, $month, 1);
            $lastDay = Carbon::create($year, $month, $daysInMonth);

            // Build calendar data
            foreach ($administrations as $administration) {
                $roster = $administration->roster;
                if (!$roster) {
                    continue;
                }

                $employeeData = [
                    'roster_id' => $roster->id,
                    'nik' => $administration->nik,
                    'name' => $administration->employee->fullname ?? 'N/A',
                    'position' => $administration->position->position_name ?? '',
                    'level' => $administration->level->name ?? '',
                    'days' => []
                ];

                // Initialize all days in month
                for ($day = 1; $day <= $daysInMonth; $day++) {
                    $currentDate = Carbon::create($year, $month, $day);
                    $employeeData['days'][$day] = [
                        'date' => $currentDate->format('Y-m-d'),
                        'status' => 'off', // default
                        'cycle_no' => null,
                        'type' => null // 'work', 'leave', 'off'
                    ];
                }

                // Process roster details
                foreach ($roster->rosterDetails as $detail) {
                    $workStart = Carbon::parse($detail->work_start);
                    $workEnd = Carbon::parse($detail->work_end);
                    $leaveStart = $detail->leave_start ? Carbon::parse($detail->leave_start) : null;
                    $leaveEnd = $detail->leave_end ? Carbon::parse($detail->leave_end) : null;

                    // Check if work period overlaps with selected month
                    $monthStart = Carbon::create($year, $month, 1);
                    $monthEnd = Carbon::create($year, $month, $daysInMonth)->endOfDay();

                    // Mark work days - show all dates from work_start to work_end that fall within selected month
                    if ($workEnd->gte($monthStart) && $workStart->lte($monthEnd)) {
                        // Determine the actual start and end dates to iterate
                        $actualWorkStart = $workStart->lt($monthStart) ? $monthStart->copy() : $workStart->copy();
                        $actualWorkEnd = $workEnd->gt($monthEnd) ? $monthEnd->copy() : $workEnd->copy();

                        $workDate = $actualWorkStart->copy();
                        while ($workDate->lte($actualWorkEnd)) {
                            // Only mark if the date is within the selected month
                            if ($workDate->month == $month && $workDate->year == $year) {
                                $day = $workDate->day;
                                if (isset($employeeData['days'][$day])) {
                                    $employeeData['days'][$day]['status'] = 'work';
                                    $employeeData['days'][$day]['cycle_no'] = $detail->cycle_no;
                                    $employeeData['days'][$day]['type'] = 'work';
                                }
                            }
                            $workDate->addDay();
                        }
                    }

                    // Mark leave days - show all dates from leave_start to leave_end that fall within selected month
                    if ($leaveStart && $leaveEnd) {
                        if ($leaveEnd->gte($monthStart) && $leaveStart->lte($monthEnd)) {
                            // Determine the actual start and end dates to iterate
                            $actualLeaveStart = $leaveStart->lt($monthStart) ? $monthStart->copy() : $leaveStart->copy();
                            $actualLeaveEnd = $leaveEnd->gt($monthEnd) ? $monthEnd->copy() : $leaveEnd->copy();

                            $leaveDate = $actualLeaveStart->copy();
                            while ($leaveDate->lte($actualLeaveEnd)) {
                                // Only mark if the date is within the selected month
                                if ($leaveDate->month == $month && $leaveDate->year == $year) {
                                    $day = $leaveDate->day;
                                    if (isset($employeeData['days'][$day])) {
                                        // Leave takes priority over work if they overlap
                                        $employeeData['days'][$day]['status'] = 'leave';
                                        $employeeData['days'][$day]['cycle_no'] = $detail->cycle_no;
                                        $employeeData['days'][$day]['type'] = 'leave';
                                    }
                                }
                                $leaveDate->addDay();
                            }
                        }
                    }
                }

                $calendarData[] = $employeeData;
            }
        }

        return view('rosters.calendar', compact(
            'title',
            'projects',
            'selectedProject',
            'year',
            'month',
            'calendarData'
        ));
    }

    /**
     * Display roster and periodic leave dashboard
     */
    public function dashboard()
    {
        $title = 'Roster & Periodic Leave Dashboard';

        // Roster Statistics
        $totalRosters = Roster::count();
        $rostersWithCycles = Roster::whereHas('rosterDetails')->count();
        $totalCycles = RosterDetail::count();
        $activeCycles = RosterDetail::where('work_start', '<=', now())
            ->where('work_end', '>=', now())
            ->count();
        $onLeaveCycles = RosterDetail::whereNotNull('leave_start')
            ->whereNotNull('leave_end')
            ->where('leave_start', '<=', now())
            ->where('leave_end', '>=', now())
            ->count();

        // Periodic Leave Statistics
        $totalPeriodicRequests = LeaveRequest::where('is_batch_request', true)
            ->whereNotNull('batch_id')
            ->distinct('batch_id')
            ->count('batch_id');

        $pendingPeriodicRequests = LeaveRequest::where('is_batch_request', true)
            ->where('status', 'pending')
            ->distinct('batch_id')
            ->count('batch_id');

        $approvedPeriodicRequests = LeaveRequest::where('is_batch_request', true)
            ->where('status', 'approved')
            ->distinct('batch_id')
            ->count('batch_id');

        $thisMonthPeriodicRequests = LeaveRequest::where('is_batch_request', true)
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->distinct('batch_id')
            ->count('batch_id');

        // Project Statistics
        $rosterProjects = Project::where('leave_type', 'roster')
            ->where('project_status', 1)
            ->withCount([
                'rosters',
                'administrations' => function ($q) {
                    $q->where('is_active', 1);
                }
            ])
            ->orderBy('project_code')
            ->get();

        // Recent Periodic Leave Requests
        $recentPeriodicRequests = LeaveRequest::where('is_batch_request', true)
            ->whereNotNull('batch_id')
            ->select(
                'batch_id',
                'bulk_notes',
                DB::raw('MIN(created_at) as created_at'),
                DB::raw('COUNT(*) as total_requests'),
                DB::raw('MIN(status) as status')
            )
            ->groupBy('batch_id', 'bulk_notes')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        // Employees Needing Balancing
        // Karyawan dengan selisih besar antara accumulated leave dan leave taken (> 5 hari atau < -5 hari)
        $employeesNeedingBalancing = Roster::with([
            'administration.employee',
            'administration.project',
            'administration.position.department',
            'rosterDetails'
        ])
            ->whereHas('rosterDetails')
            ->get()
            ->map(function ($roster) {
                // Calculate accumulated leave and leave taken
                $accumulatedLeave = $roster->rosterDetails->sum(function ($detail) {
                    return $detail->getLeaveEntitlement();
                });

                $leaveTaken = $roster->rosterDetails->sum(function ($detail) {
                    return $detail->getLeaveDays();
                });

                $balance = $accumulatedLeave - $leaveTaken;
                $fbCycleRatio = $roster->getFbCycleRatio();
                $workDaysDifference = $fbCycleRatio > 0 ? round($balance / $fbCycleRatio, 2) : 0;

                return [
                    'roster' => $roster,
                    'accumulated_leave' => round($accumulatedLeave, 2),
                    'leave_taken' => round($leaveTaken, 2),
                    'balance' => round($balance, 2),
                    'work_days_difference' => $workDaysDifference
                ];
            })
            ->filter(function ($item) {
                // Filter: selisih > 5 hari atau < -5 hari
                return abs($item['work_days_difference']) > 5;
            })
            ->sortByDesc(function ($item) {
                return abs($item['work_days_difference']);
            })
            ->take(20)
            ->values();

        // Prepare data for DataTables
        $employeesNeedingBalancingData = $employeesNeedingBalancing->map(function ($item) {
            $roster = $item['roster'];
            $admin = $roster->administration;
            $employee = $admin->employee ?? null;

            return [
                'employee_name' => $employee->fullname ?? 'N/A',
                'employee_nik' => $admin->nik ?? '-',
                'project' => $admin->project->project_code ?? '-',
                'accumulated_leave' => number_format($item['accumulated_leave'], 2),
                'leave_taken' => number_format($item['leave_taken'], 2),
                'balance' => number_format($item['balance'], 2),
                'work_days_difference' => number_format($item['work_days_difference'], 2),
                'roster_id' => $roster->id
            ];
        });

        // Prepare recent periodic requests data
        $recentPeriodicRequestsData = $recentPeriodicRequests->map(function ($batch) {
            $statusBadge = $batch->status === 'approved' ? 'success' : ($batch->status === 'pending' ? 'warning' : 'secondary');
            return [
                'batch_id' => $batch->batch_id,
                'notes' => $batch->bulk_notes ?? '-',
                'total' => number_format($batch->total_requests),
                'status_badge' => '<span class="badge badge-' . $statusBadge . '">' . ucfirst($batch->status) . '</span>',
                'batch_id_url' => route('leave.periodic-requests.show', $batch->batch_id)
            ];
        });

        return view('rosters.dashboard', compact(
            'title',
            'totalRosters',
            'rostersWithCycles',
            'totalCycles',
            'activeCycles',
            'onLeaveCycles',
            'totalPeriodicRequests',
            'pendingPeriodicRequests',
            'approvedPeriodicRequests',
            'thisMonthPeriodicRequests',
            'rosterProjects',
            'recentPeriodicRequests',
            'recentPeriodicRequestsData',
            'employeesNeedingBalancingData'
        ));
    }
}
