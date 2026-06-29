<?php

namespace App\Http\Controllers;

use App\Models\Administration;
use App\Models\ApprovalPlan;
use App\Models\Department;
use App\Models\Employee;
use App\Models\FlightRequest;
use App\Models\LeaveEntitlement;
use App\Models\LeaveRequest;
use App\Models\LeaveRequestCancellation;
use App\Models\LeaveType;
use App\Models\NationalHoliday;
use App\Models\Project;
use App\Models\User;
use App\Support\UserProject;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class LeaveRequestController extends Controller
{
    public function __construct()
    {
        // ========================================
        // ADMIN/HR PERMISSIONS
        // ========================================
        // These permissions are for admin/HR roles (administrator, hr-manager, hr-supervisor, hr-staff)
        // They can manage ALL leave requests across the organization

        // View all leave requests (index, data, download)
        $this->middleware('permission:leave-requests.show')->only('index', 'data', 'download', 'indexFilterOptions');

        // Create leave requests for any employee
        $this->middleware('permission:leave-requests.create')->only('create', 'store');

        // Edit any leave request
        $this->middleware('permission:leave-requests.edit')->only('edit', 'update', 'deleteDocument', 'upload', 'close');

        // Delete leave request (destroy) and close completed requests
        // Note: destroy() uses dual permission inside the method; showCancellationForm/storeCancellation use leave-requests.cancel (see below)
        $this->middleware('permission:leave-requests.delete')->only('destroy');
        $this->middleware('permission:leave-requests.cancel')->only('approveCancellation', 'rejectCancellation');

        // AJAX methods for admin (project info, employees by project)
        // Note: getLeaveTypeInfo is handled as dual access method (see below)
        $this->middleware('permission:leave-requests.show')->only('getProjectInfo', 'getEmployeesByProject');

        // ========================================
        // PERSONAL/USER PERMISSIONS
        // ========================================
        // These permissions are for regular users (user role)
        // They can only manage their OWN leave requests

        // View own leave requests
        $this->middleware('permission:personal.leave.view-own')->only('myRequests', 'myRequestsData', 'myRequestsShow', 'myRequestsFilterOptions');

        // Create own leave requests
        // Note: getLeaveTypeInfo is handled as dual access method (see below)
        $this->middleware('permission:personal.leave.create-own')->only('myRequestsCreate', 'myRequestsStore', 'getLeaveTypesByEmployee', 'getLeavePeriod', 'getEmployeeLeaveBalance');

        // Edit own leave requests
        // Note: getLeaveTypeInfo is handled as dual access method (see below)
        $this->middleware('permission:personal.leave.edit-own')->only('myRequestsEdit', 'myRequestsUpdate');

        // View own entitlements
        $this->middleware('permission:personal.leave.view-entitlements')->only('myEntitlements', 'myEntitlementsCalculationDetails');

        // ========================================
        // METHODS WITH DUAL PERMISSION ACCESS
        // ========================================
        // These methods can be accessed by BOTH admin and personal users
        // Permission checks are handled manually within the methods:
        // - show() - admin can see all, personal can see own
        // - edit() - admin can edit all, personal can edit own (if has personal.leave.edit-own)
        // - update() - admin can update all, personal can update own (if has personal.leave.edit-own)
        // - store() - admin can create for anyone, personal can create for self
        // - showCancellationForm() - HR: leave-requests.cancel + show; personal: personal.leave.cancel-own (own only)
        // - storeCancellation() - HR: leave-requests.cancel + show; personal: personal.leave.cancel-own (own only)
        // - destroy() - admin (leave-requests.delete) or employee (personal.leave.edit-own, own request only); see method body
        // - getLeaveTypeInfo() - admin can access with leave-requests.show, personal can access with personal.leave.create-own or personal.leave.edit-own
        // Note: These methods are NOT in middleware to allow dual access
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $projects = UserProject::projectsForSelect();

        return view('leave-requests.index', compact('projects'))
            ->with('title', 'Leave Requests');
    }

    /**
     * JSON for admin leave-requests index filters (web session — not under /api key middleware).
     */
    public function indexFilterOptions()
    {
        $rows = UserProject::employeesForSelect(
            null,
            UserProject::EMPLOYEE_SELECT_ACTIVE_ADMINISTRATION,
            'nik'
        );

        $employees = $rows
            ->unique('id')
            ->sortBy(fn(Employee $e) => mb_strtolower((string) ($e->nik ?? '')))
            ->values()
            ->map(function (Employee $e) {
                $nik = (string) ($e->nik ?? '');
                $name = $e->fullname ?? '';

                return [
                    'id' => $e->id,
                    'nik' => $nik,
                    'fullname' => $name,
                    'label' => $nik !== '' ? $nik . ' — ' . $name : $name,
                ];
            });

        $leaveTypes = LeaveType::query()
            ->where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name'])
            ->values()
            ->map(fn(LeaveType $lt) => [
                'id' => $lt->id,
                'code' => $lt->code,
                'name' => $lt->name,
                'label' => ($lt->code ? $lt->code . ' — ' . $lt->name : $lt->name),
            ]);

        return response()->json([
            'employees' => $employees,
            'leave_types' => $leaveTypes,
        ]);
    }

    /**
     * JSON for "My leave requests" filter dropdowns.
     */
    public function myRequestsFilterOptions()
    {
        $leaveTypes = LeaveType::query()
            ->where('is_active', true)
            ->orderBy('code')
            ->get(['id', 'code', 'name'])
            ->values()
            ->map(fn(LeaveType $lt) => [
                'id' => $lt->id,
                'code' => $lt->code,
                'name' => $lt->name,
                'label' => ($lt->code ? $lt->code . ' — ' . $lt->name : $lt->name),
            ]);

        return response()->json([
            'leave_types' => $leaveTypes,
        ]);
    }

    /**
     * Get data for DataTables
     */
    public function data(Request $request)
    {
        $scopedBase = LeaveRequest::query()->whereHas('administration', function ($q) {
            UserProject::scopeToAssignedProjects($q, 'project_id');
        });

        $totalRecords = (clone $scopedBase)->count();

        $query = (clone $scopedBase)
            ->with(['employee', 'leaveType', 'administration.project', 'approvalPlans'])
            ->select('leave_requests.*')
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('leave_type_id')) {
            $query->where('leave_type_id', $request->leave_type_id);
        }

        if ($request->filled('project_id')) {
            $query->whereHas('administration', function ($q) use ($request) {
                $q->where('project_id', $request->project_id);
            });
        }

        if ($request->filled('start_date')) {
            $query->where('start_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('end_date', '<=', $request->end_date);
        }

        // Global search
        if ($request->filled('search.value')) {
            $searchValue = $request->input('search.value');
            $query->where(function ($q) use ($searchValue) {
                $q->whereHas('employee', function ($employeeQuery) use ($searchValue) {
                    $employeeQuery->where('fullname', 'like', "%{$searchValue}%");
                })
                    ->orWhereHas('leaveType', function ($leaveTypeQuery) use ($searchValue) {
                        $leaveTypeQuery->where('name', 'like', "%{$searchValue}%");
                    })
                    ->orWhereHas('administration.project', function ($pq) use ($searchValue) {
                        $pq->where('project_code', 'like', "%{$searchValue}%")
                            ->orWhere('project_name', 'like', "%{$searchValue}%");
                    })
                    ->orWhere('status', 'like', "%{$searchValue}%")
                    ->orWhere('total_days', 'like', "%{$searchValue}%")
                    ->orWhere('register_number', 'like', "%{$searchValue}%");
            });
        }

        $filteredRecords = $query->count();

        // Apply pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $leaveRequests = $query->skip($start)->take($length)->get();

        // Format data for DataTables
        $data = $leaveRequests->map(function ($request, $index) use ($start) {
            $statusBadge = '';
            switch ($request->status) {
                case 'draft':
                    $statusBadge = '<span class="badge badge-secondary">Draft</span>';
                    break;
                case 'pending':
                    $statusBadge = '<span class="badge badge-warning">Pending</span>';
                    break;
                case 'approved':
                    $statusBadge = '<span class="badge badge-success">Approved</span>';
                    break;
                case 'rejected':
                    $statusBadge = '<span class="badge badge-danger">Rejected</span>';
                    break;
                case 'cancelled':
                    $statusBadge = '<span class="badge badge-secondary">Cancelled</span>';
                    break;
                case 'auto_approved':
                    $statusBadge = '<span class="badge badge-success">Auto Approved</span>';
                    break;
                case 'closed':
                    $statusBadge = '<span class="badge badge-dark">Closed</span>';
                    break;
                default:
                    $statusBadge = '<span class="badge badge-secondary">' . ucfirst($request->status) . '</span>';
            }

            $actions = '<div class="btn-group" role="group">';
            $actions .= '<a href="' . route('leave.requests.show', $request) . '" class="btn btn-info btn-sm mr-1"><i class="fas fa-eye"></i></a>';
            $actions .= '<a href="' . route('leave.requests.edit', $request) . '" class="btn btn-warning btn-sm mr-1"><i class="fas fa-edit"></i></a>';

            if ($request->canBeDeletedBeforeApproval() && auth()->user()->can('leave-requests.delete')) {
                $actions .= '<form method="POST" action="' . route('leave.requests.destroy', $request) . '" class="d-inline" onsubmit="return confirm(\'Delete this leave request? This cannot be undone.\');">';
                $actions .= csrf_field() . method_field('DELETE');
                $actions .= '<button type="submit" class="btn btn-danger btn-sm mr-1" title="Delete"><i class="fas fa-trash"></i></button></form>';
            }

            // if ($request->canBeCancelled()) {
            //     $actions .= '<a href="' . route('leave.requests.edit', $request) . '" class="btn btn-warning btn-sm mr-1"><i class="fas fa-edit"></i></a>';
            // }

            $actions .= '</div>';

            // Format leave type with document indicator for paid leave types
            $leaveTypeName = $request->leaveType->name ?? 'N/A';
            $leaveTypeCategory = strtolower($request->leaveType->category ?? '');
            $documentIcon = '';

            if ($leaveTypeCategory === 'paid') {
                if ($request->supporting_document) {
                    $documentIcon = ' <span class="text-success" title="Supporting document uploaded">✅</span>';
                } else {
                    $documentIcon = ' <span class="text-warning" title="Supporting document required">⚠️</span>';
                }
            }

            // Add money icon for LSL flexible with cash out
            $moneyIcon = '';
            if ($request->isLSLFlexible() && ($request->lsl_cashout_days ?? 0) > 0) {
                $moneyIcon = ' <span class="text-warning" title="Long Service Leave with cash out">💵</span>';
            }

            return [
                'DT_RowIndex' => $start + $index + 1,
                'register_number' => e($request->register_number ?? '—'),
                'employee' => $request->employee->fullname ?? 'N/A',
                'project' => e($request->administration?->project?->project_code ?? '—'),
                'leave_type' => '<span class="badge badge-info">' . $leaveTypeName . '</span>' . $documentIcon . $moneyIcon,
                'start_date' => $request->start_date->format('d/m/Y'),
                'end_date' => $request->end_date->format('d/m/Y'),
                'total_days' => $request->total_days . ' days',
                'status' => $statusBadge,
                'requested_at' => $request->requested_at ? $request->requested_at->format('d/m/Y H:i') : 'N/A',
                'action' => $actions,
            ];
        });

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * ADMIN/HR: Can create leave requests for any employee (permission: leave-requests.create)
     * PERSONAL/USER: Can create leave requests for themselves (permission: personal.leave.create-own)
     *
     * Note: This method is protected by middleware 'permission:leave-requests.create'
     * Personal users should use myRequestsCreate() instead
     */
    public function create(Request $request)
    {
        // This method is for admin/HR only (protected by middleware)
        // Personal users should use myRequestsCreate() which is protected by 'personal.leave.create-own'

        $leaveTypes = LeaveType::where('is_active', true)->orderBy('code', 'asc')->get();

        // Projects from user_project assignment
        $projects = UserProject::projectsForSelect();

        // Get all active departments for department selection
        $departments = Department::where('department_status', 1)->get();

        // Get all employees for standard flow (fallback)
        $employees = Employee::with('administrations')->get();

        $nationalHolidayDates = NationalHoliday::datesForJs();
        $nationalHolidayMap = NationalHoliday::mapDateToNameForJs();

        return view('leave-requests.create', compact('leaveTypes', 'projects', 'departments', 'employees', 'nationalHolidayDates', 'nationalHolidayMap'))
            ->with('title', 'Create Leave Request');
    }

    /**
     * Store a newly created resource in storage.
     *
     * ADMIN/HR: Can create leave requests for any employee (permission: leave-requests.create)
     * PERSONAL/USER: Can create leave requests for themselves (permission: personal.leave.create-own)
     *
     * Note: This method can be called by both admin (via create form) and personal users (via myRequestsStore)
     */
    public function store(Request $request)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        // Check if user has either admin or personal permission
        if (! $user->can('leave-requests.create') && ! $user->can('personal.leave.create-own')) {
            abort(403, 'Unauthorized action. You do not have permission to create leave requests.');
        }

        // If personal user (has personal permission but not admin permission), force employee_id to their own
        if ($user->can('personal.leave.create-own') && ! $user->can('leave-requests.create')) {
            $request->merge(['employee_id' => $user->employee_id]);
        }

        // Convert date format from dd/mm/yyyy to Y-m-d if needed
        if ($request->has('start_date') && strpos($request->start_date, '/') !== false) {
            try {
                $request->merge(['start_date' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->start_date)->format('Y-m-d')]);
            } catch (\Exception $e) {
                // If parsing fails, let validation handle it
            }
        }
        if ($request->has('end_date') && strpos($request->end_date, '/') !== false) {
            try {
                $request->merge(['end_date' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->end_date)->format('Y-m-d')]);
            } catch (\Exception $e) {
                // If parsing fails, let validation handle it
            }
        }
        if ($request->has('back_to_work_date') && $request->back_to_work_date && strpos($request->back_to_work_date, '/') !== false) {
            try {
                $request->merge(['back_to_work_date' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->back_to_work_date)->format('Y-m-d')]);
            } catch (\Exception $e) {
                // If parsing fails, let validation handle it
            }
        }

        // Get leave type to determine if reason is required
        $leaveType = LeaveType::find($request->leave_type_id);
        $isUnpaidLeave = $leaveType && (
            str_contains(strtolower($leaveType->name), 'izin tanpa upah') ||
            str_contains(strtolower($leaveType->category), 'unpaid')
        );

        $validationRules = [
            'employee_id' => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'back_to_work_date' => 'nullable|date|after:end_date',
            'leave_period' => 'nullable|string|max:255',
            'project_id' => 'nullable|exists:projects,id',
        ];

        // Add reason validation only for unpaid leave
        if ($isUnpaidLeave) {
            $validationRules['reason'] = 'required|string|max:1000';
        } else {
            $validationRules['reason'] = 'nullable|string|max:1000';
        }

        // Add supporting document validation for paid leave
        $validationRules['supporting_document'] = 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png,rar,zip|max:2048'; // 2MB max

        // Add total_days validation
        $validationRules['total_days'] = 'required|integer|min:1|max:365';

        // Add LSL flexible validation (only for LSL)
        $isLSL = $this->isLSLLeaveType($leaveType);
        $lslUsageMode = $isLSL ? $this->normalizeLSLUsageMode($request->input('lsl_usage_mode')) : null;

        if ($isLSL) {
            $validationRules['lsl_usage_mode'] = 'required|in:leave_only,cashout_only,combined';
            $validationRules['lsl_taken_days'] = 'nullable|integer|min:0';
            $validationRules['lsl_cashout_days'] = $lslUsageMode === 'cashout_only'
                ? 'required|integer|min:1|max:365'
                : 'nullable|integer|min:0';

            if ($lslUsageMode === 'cashout_only') {
                $validationRules['start_date'] = 'nullable|date';
                $validationRules['end_date'] = 'nullable|date|after_or_equal:start_date';
            }
        }

        // Manual approvers - following pattern from OfficialtravelController and RecruitmentRequestController
        $validationRules['manual_approvers'] = 'required|array|min:1';
        $validationRules['manual_approvers.*'] = 'exists:users,id';

        $request->validate($validationRules, [
            'manual_approvers.required' => 'Please select at least one approver.',
            'manual_approvers.array' => 'Approvers must be an array.',
            'manual_approvers.min' => 'Please select at least one approver.',
            'manual_approvers.*.exists' => 'One or more selected approvers are invalid.',
        ]);

        $this->validateLeaveDatesAgainstNationalHolidays($request->back_to_work_date);

        if (! $isLSL) {
            $request->merge(['total_days' => $this->computeBillableLeaveDaysFromRequest($request)]);
        }

        $administrationPreview = Administration::where('employee_id', $request->employee_id)
            ->where('is_active', 1)
            ->first();
        if ($administrationPreview && ($r = UserProject::guardProjectInAssignmentScope((int) $administrationPreview->project_id))) {
            return $r;
        }
        if ($request->filled('project_id') && ($r = UserProject::guardProjectInAssignmentScope((int) $request->project_id))) {
            return $r;
        }

        // Get total days from request (either calculated or manually entered)
        $totalDays = $request->total_days ?? 0;
        $takenDays = $totalDays;
        $cashoutEnabled = false;
        $cashoutDays = 0;

        if ($isLSL) {
            $lslTotals = $this->processLSLFlexibleRequest($request);
            $takenDays = $lslTotals['taken_days'];
            $cashoutDays = $lslTotals['cashout_days'];
            $totalDays = $lslTotals['total_days'];

            if ($errors = $this->validateLSLFlexibleBusinessRules($lslTotals['usage_mode'], $takenDays, $cashoutDays, $totalDays)) {
                return back()->with($errors)->withInput();
            }
        }

        // Validate total_days is present
        if (! $totalDays || $totalDays <= 0) {
            return back()->with([
                'total_days' => 'Total days is required and must be greater than 0.',
            ])->withInput();
        }

        // Validate total days against remaining days
        $entitlement = $this->findLeaveEntitlementForRequest($request, (int) $request->employee_id, (int) $request->leave_type_id);

        if ($entitlement && $totalDays > $entitlement->remaining_days) {
            return back()->with([
                'total_days' => "Total days ({$totalDays}) exceeds remaining leave balance ({$entitlement->remaining_days} days).",
            ])->withInput();
        }

        // Handle file upload for supporting document
        $supportingDocumentPath = null;
        if ($request->hasFile('supporting_document')) {
            $file = $request->file('supporting_document');
            $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $extension = $file->getClientOriginalExtension();
            $timestamp = now()->format('YmdHis');
            $fileName = $originalName . '_' . $timestamp . '.' . $extension;

            // Generate temporary ID for folder before creating record
            $tempId = 'temp_' . time() . '_' . rand(1000, 9999);
            $supportingDocumentPath = $file->storeAs("leave_requests/{$tempId}", $fileName, 'private');
        }

        DB::beginTransaction();
        try {
            // Get employee's current administration
            $administration = Administration::where('employee_id', $request->employee_id)
                ->where('is_active', 1)
                ->first();

            if (! $administration) {
                DB::rollback();

                return back()->withErrors(['employee_id' => 'Employee has no active administration record.'])->withInput();
            }

            // Determine flow type based on project
            $flowType = 'standard'; // default
            if ($request->project_id) {
                $project = Project::find($request->project_id);
                $flowType = $project->leave_type; // 'non_roster' or 'roster'
            }

            // Get leave type for validation
            $leaveType = LeaveType::find($request->leave_type_id);

            // Determine if this is a batch request (for roster flow)
            $isBatchRequest = $flowType === 'roster' ? true : false;
            $batchId = $isBatchRequest ? 'BATCH_' . time() . '_' . $request->employee_id : null;

            // Normalize manual_approvers array - following pattern from OfficialtravelController
            $manualApprovers = $request->manual_approvers ?? [];
            if (! is_array($manualApprovers)) {
                $manualApprovers = [];
            }
            // Ensure array values are preserved in order (array_values to reset keys)
            $manualApprovers = array_values(array_filter($manualApprovers));

            // Set reason to null if leave type is not unpaid
            $reason = $request->reason;
            if ($leaveType && $leaveType->category !== 'unpaid') {
                $reason = null;
            }

            // Prepare data for leave request creation
            $leaveRequestData = [
                'employee_id' => $request->employee_id,
                'administration_id' => $administration->id,
                'leave_type_id' => $request->leave_type_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'back_to_work_date' => $request->back_to_work_date,
                'reason' => $reason,
                'total_days' => $totalDays,
                'status' => 'pending',
                'leave_period' => $request->leave_period,
                'requested_at' => now(),
                'requested_by' => Auth::id(),
                'is_batch_request' => $isBatchRequest,
                'batch_id' => $batchId,
                'supporting_document' => $supportingDocumentPath,
                'manual_approvers' => $manualApprovers,
            ];

            // Add LSL flexible fields if this is LSL
            if ($isLSL) {
                $leaveRequestData['lsl_taken_days'] = $takenDays; // From manual input or calculated
                $leaveRequestData['lsl_cashout_days'] = $cashoutDays;
            }

            // Create leave request
            $leaveRequest = LeaveRequest::create($leaveRequestData);

            // Refresh model to ensure all data including manual_approvers is loaded
            $leaveRequest->refresh();

            // Set auto conversion date for paid leave without supporting document
            $leaveRequest->setAutoConversionDate();

            // Move file to correct folder with leave request ID
            if ($supportingDocumentPath) {
                $newPath = "leave_requests/{$leaveRequest->id}/" . basename($supportingDocumentPath);
                Storage::disk('private')->move($supportingDocumentPath, $newPath);
                $leaveRequest->update(['supporting_document' => $newPath]);

                // Clear auto conversion date since document is uploaded
                $leaveRequest->clearAutoConversionDate();
            }

            // Create approval plan using manual approvers - following pattern from OfficialtravelController
            if (! empty($manualApprovers)) {
                // Double-check that manual_approvers is set on the model
                if (empty($leaveRequest->manual_approvers)) {
                    Log::warning('Manual approvers not found on leave request after creation', [
                        'leave_request_id' => $leaveRequest->id,
                        'manual_approvers_input' => $manualApprovers,
                    ]);
                    DB::rollback();

                    return back()->with(['toast_error' => 'Failed to save manual approvers. Please try again.'])->withInput();
                }

                $response = app(ApprovalPlanController::class)->create_manual_approval_plan('leave_request', $leaveRequest->id);
                if (! $response || $response === 0) {
                    DB::rollback();

                    return back()->with(['toast_error' => 'Failed to create approval plans. Please ensure at least one approver is selected.'])->withInput();
                }
            } else {
                DB::rollback();

                return back()->with(['toast_error' => 'Please select at least one approver before submitting.'])->withInput();
            }

            // Create roster adjustment if needed (for roster flow)
            if ($flowType === 'roster') {
                $this->createRosterAdjustment($leaveRequest);
            }

            // Create flight request from fr_data when "Need flight ticket?" was checked
            FlightRequest::createFromFrData($request, $leaveRequest);

            // Update leave entitlements immediately for testing purposes
            // $this->updateLeaveEntitlements($leaveRequest);

            DB::commit();

            $successMessage = 'Leave request submitted successfully.';
            if ($flowType === 'non_roster') {
                $successMessage = 'Non-roster leave request submitted successfully.';
            } elseif ($flowType === 'roster') {
                $successMessage = 'Roster leave request submitted successfully.';
            }

            // Jika request berasal dari my requests (misal dari route my-requests.store), redirect ke my-requests,
            // walaupun yang akses admin/HR. Kalau tidak, tetap ke leave.requests.index.
            if ($request->route() && in_array($request->route()->getName(), ['leave.my-requests.store', 'leave.my-requests.update', 'leave.my-requests'])) {
                return redirect()->route('leave.my-requests')
                    ->with('toast_success', $successMessage);
            }

            return redirect()->route('leave.requests.index')
                ->with('toast_success', $successMessage);
        } catch (\Exception $e) {
            DB::rollback();

            // Clean up temp file if exists
            if ($supportingDocumentPath && Storage::disk('private')->exists($supportingDocumentPath)) {
                Storage::disk('private')->delete($supportingDocumentPath);
                $tempFolder = dirname($supportingDocumentPath);
                if (Storage::disk('private')->exists($tempFolder)) {
                    Storage::disk('private')->deleteDirectory($tempFolder);
                }
            }

            return back()->with(['toast_error' => 'Failed to create leave request: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     *
     * ADMIN/HR: Can view any leave request (permission: leave-requests.show)
     * PERSONAL/USER: Can only view their own leave requests (permission: personal.leave.view-own)
     *
     * Note: This method is NOT protected by middleware to allow dual access
     */
    public function show(LeaveRequest $leaveRequest)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        // Check if user has either admin or personal permission
        if (! $user->can('leave-requests.show') && ! $user->can('personal.leave.view-own')) {
            abort(403, 'Unauthorized action. You do not have permission to view leave requests.');
        }

        // If personal user (has personal permission but not admin permission), ensure they can only view their own
        if ($user->can('personal.leave.view-own') && ! $user->can('leave-requests.show')) {
            if ($leaveRequest->employee_id !== $user->employee_id) {
                abort(403, 'You can only view your own leave requests.');
            }
        }

        if ($r = $this->guardLeaveRequestProjectForHrUser($leaveRequest)) {
            return $r;
        }

        $leaveRequest->load([
            'employee.administrations.project',
            'employee.administrations.position',
            'leaveType',
            'approvalPlans',
            'cancellations.requestedBy',
            'cancellations.confirmedBy',
            'flightRequests.details',
        ]);

        return view('leave-requests.show', compact('leaveRequest'))->with('title', 'Leave Request Details');
    }

    /**
     * Print leave request form.
     *
     * ADMIN/HR: leave-requests.show
     * PERSONAL/USER: personal.leave.view-own (own requests only)
     */
    public function print(LeaveRequest $leaveRequest)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        if (! $user->can('leave-requests.show') && ! $user->can('personal.leave.view-own')) {
            abort(403, 'Unauthorized action. You do not have permission to print leave requests.');
        }

        if ($user->can('personal.leave.view-own') && ! $user->can('leave-requests.show')) {
            if ($leaveRequest->employee_id !== $user->employee_id) {
                abort(403, 'You can only print your own leave requests.');
            }
        }

        if ($r = $this->guardLeaveRequestProjectForHrUser($leaveRequest)) {
            return $r;
        }

        $leaveRequest->load([
            'employee.administrations.project',
            'employee.administrations.position.department',
            'administration.position.department',
            'administration.project',
            'leaveType',
            'requestedBy',
            'approvalPlans' => function ($q) {
                $q->orderBy('approval_order')->orderBy('id')->with([
                    'approver.administration.position',
                ]);
            },
        ]);

        return view('leave-requests.print', compact('leaveRequest'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * ADMIN/HR: Can edit any leave request (permission: leave-requests.edit)
     * PERSONAL/USER: Can only edit their own leave requests (permission: personal.leave.edit-own)
     *
     * Note: This method is NOT protected by middleware to allow dual access
     */
    public function edit(LeaveRequest $leaveRequest)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        // Check if user has either admin or personal permission
        if (! $user->can('leave-requests.edit') && ! $user->can('personal.leave.edit-own')) {
            abort(403, 'Unauthorized action. You do not have permission to edit leave requests.');
        }

        // If personal user (has personal permission but not admin permission), ensure they can only edit their own
        if ($user->can('personal.leave.edit-own') && ! $user->can('leave-requests.edit')) {
            if ($leaveRequest->employee_id !== $user->employee_id) {
                abort(403, 'You can only edit your own leave requests.');
            }

            // Personal users can only edit leave requests that are not yet approved
            // Allowed statuses: 'draft', 'pending'
            // Not allowed: 'approved', 'rejected', 'cancelled', 'closed', 'auto_approved'
            if (! in_array($leaveRequest->status, ['draft', 'pending'])) {
                return back()->with(['toast_error' => 'You can only edit leave requests that are not yet approved.']);
            }
        }

        if ($r = $this->guardLeaveRequestProjectForHrUser($leaveRequest)) {
            return $r;
        }

        // Load leave types for leave type selection
        $leaveTypes = LeaveType::where('is_active', true)->orderBy('code', 'asc')->get();

        // Load projects for project selection
        $projects = UserProject::projectsForSelect();

        // Get all active departments for department selection
        $departments = Department::where('department_status', 1)->get();

        $leaveRequest->load(['flightRequests.details']);
        $existingFlightRequest = $leaveRequest->flightRequests()->with('details')->first();

        $nationalHolidayDates = NationalHoliday::datesForJs();
        $nationalHolidayMap = NationalHoliday::mapDateToNameForJs();

        return view('leave-requests.edit', compact('leaveRequest', 'leaveTypes', 'projects', 'departments', 'existingFlightRequest', 'nationalHolidayDates', 'nationalHolidayMap'))->with('title', 'Edit Leave Request');
    }

    /**
     * Update the specified resource in storage.
     *
     * ADMIN/HR: Can update any leave request (permission: leave-requests.edit)
     * PERSONAL/USER: Can only update their own leave requests (permission: personal.leave.edit-own)
     *
     * Note: This method is NOT protected by middleware to allow dual access
     */
    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        // Check if user has either admin or personal permission
        if (! $user->can('leave-requests.edit') && ! $user->can('personal.leave.edit-own')) {
            abort(403, 'Unauthorized action. You do not have permission to update leave requests.');
        }

        // If personal user (has personal permission but not admin permission), ensure they can only update their own
        if ($user->can('personal.leave.edit-own') && ! $user->can('leave-requests.edit')) {
            if ($leaveRequest->employee_id !== $user->employee_id) {
                abort(403, 'You can only update your own leave requests.');
            }

            // Personal users can only update leave requests that are not yet approved
            // Allowed statuses: 'draft', 'pending'
            // Not allowed: 'approved', 'rejected', 'cancelled', 'closed', 'auto_approved'
            if (! in_array($leaveRequest->status, ['draft', 'pending'])) {
                return back()->with(['toast_error' => 'You can only update leave requests that are not yet approved.']);
            }

            // Force employee_id to current user's employee_id to prevent tampering
            $request->merge(['employee_id' => $user->employee_id]);
        }

        // Convert date format from dd/mm/yyyy to Y-m-d if needed
        if ($request->has('start_date') && strpos($request->start_date, '/') !== false) {
            try {
                $request->merge(['start_date' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->start_date)->format('Y-m-d')]);
            } catch (\Exception $e) {
                // If parsing fails, let validation handle it
            }
        }
        if ($request->has('end_date') && strpos($request->end_date, '/') !== false) {
            try {
                $request->merge(['end_date' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->end_date)->format('Y-m-d')]);
            } catch (\Exception $e) {
                // If parsing fails, let validation handle it
            }
        }
        if ($request->has('back_to_work_date') && $request->back_to_work_date && strpos($request->back_to_work_date, '/') !== false) {
            try {
                $request->merge(['back_to_work_date' => \Carbon\Carbon::createFromFormat('d/m/Y', $request->back_to_work_date)->format('Y-m-d')]);
            } catch (\Exception $e) {
                // If parsing fails, let validation handle it
            }
        }

        // if (!$leaveRequest->canBeCancelled()) {
        //     return back()->with(['toast_error' => 'This leave request cannot be updated.']);
        // }

        // Get the selected leave type to check category
        $leaveType = LeaveType::find($request->leave_type_id);

        // Build validation rules
        $validationRules = [
            'project_id' => 'required|exists:projects,id',
            'employee_id' => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'back_to_work_date' => 'nullable|date|after:end_date',
            'leave_period' => 'nullable|string|max:255',
            'total_days' => 'required|integer|min:1|max:365',
        ];

        // Add conditional validation based on leave type category
        if ($leaveType && $leaveType->category === 'unpaid') {
            $validationRules['reason'] = 'required|string|max:1000';
        }

        // Add supporting document validation for paid leave types
        if ($leaveType && $leaveType->category === 'paid') {
            $validationRules['supporting_document'] = 'nullable|file|mimes:pdf,doc,docx,jpg,jpeg,png,rar,zip|max:2048';
        }

        // Add LSL flexible validation (only for LSL)
        $isLSL = $this->isLSLLeaveType($leaveType);
        $lslUsageMode = $isLSL ? $this->normalizeLSLUsageMode($request->input('lsl_usage_mode')) : null;

        if ($isLSL) {
            $validationRules['lsl_usage_mode'] = 'required|in:leave_only,cashout_only,combined';
            $validationRules['lsl_taken_days'] = 'nullable|integer|min:0';
            $validationRules['lsl_cashout_days'] = $lslUsageMode === 'cashout_only'
                ? 'required|integer|min:1|max:365'
                : 'nullable|integer|min:0';

            if ($lslUsageMode === 'cashout_only') {
                $validationRules['start_date'] = 'nullable|date';
                $validationRules['end_date'] = 'nullable|date|after_or_equal:start_date';
            }
        }

        // Manual approvers - following pattern from OfficialtravelController and RecruitmentRequestController
        $validationRules['manual_approvers'] = 'nullable|array|min:1';
        $validationRules['manual_approvers.*'] = 'exists:users,id';

        $request->validate($validationRules, [
            'manual_approvers.array' => 'Approvers must be an array.',
            'manual_approvers.min' => 'Please select at least one approver.',
            'manual_approvers.*.exists' => 'One or more selected approvers are invalid.',
        ]);

        $this->validateLeaveDatesAgainstNationalHolidays($request->back_to_work_date);

        if (! $isLSL) {
            $request->merge(['total_days' => $this->computeBillableLeaveDaysFromRequest($request)]);
        }

        if ($r = $this->guardLeaveRequestProjectForHrUser($leaveRequest)) {
            return $r;
        }

        if ($r = UserProject::guardProjectInAssignmentScope((int) $request->project_id)) {
            return $r;
        }

        // Handle LSL flexible calculation
        $employeeId = $request->employee_id;
        $leaveTypeId = $request->leave_type_id;
        $totalDays = $request->total_days ?? 0;
        $takenDays = $totalDays;
        $cashoutDays = 0;

        if ($isLSL) {
            $lslTotals = $this->processLSLFlexibleRequest($request);
            $takenDays = $lslTotals['taken_days'];
            $cashoutDays = $lslTotals['cashout_days'];
            $totalDays = $lslTotals['total_days'];

            if ($errors = $this->validateLSLFlexibleBusinessRules($lslTotals['usage_mode'], $takenDays, $cashoutDays, $totalDays)) {
                return back()->with($errors)->withInput();
            }
        }

        // Validate total_days is present (after LSL calculation if applicable)
        if (! $totalDays || $totalDays <= 0) {
            return back()->with([
                'total_days' => 'Total days is required and must be greater than 0.',
            ])->withInput();
        }

        $leaveEntitlement = $this->findLeaveEntitlementForRequest($request, (int) $employeeId, (int) $leaveTypeId);

        if ($leaveEntitlement && $totalDays > $leaveEntitlement->remaining_days) {
            return back()->with([
                'total_days' => "Total days ({$totalDays}) exceeds remaining leave balance ({$leaveEntitlement->remaining_days} days).",
            ])->withInput();
        }

        DB::beginTransaction();
        try {
            // Handle file upload for supporting document
            $supportingDocumentPath = $leaveRequest->supporting_document; // Keep existing file by default

            if ($request->hasFile('supporting_document')) {
                // Delete old file and folder if exists
                $this->deleteSupportingDocument($leaveRequest);

                $file = $request->file('supporting_document');
                $originalName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $extension = $file->getClientOriginalExtension();
                $timestamp = now()->format('YmdHis');
                $fileName = $originalName . '_' . $timestamp . '.' . $extension;
                $supportingDocumentPath = $file->storeAs("leave_requests/{$leaveRequest->id}", $fileName, 'private');
            }

            // Normalize manual_approvers array - following pattern from OfficialtravelController
            $manualApprovers = $request->manual_approvers ?? [];
            if (! is_array($manualApprovers)) {
                $manualApprovers = [];
            }
            // Ensure array values are preserved in order (array_values to reset keys)
            $manualApprovers = array_values(array_filter($manualApprovers));

            // Check if manual_approvers changed - following pattern from RecruitmentRequestController
            $approversChanged = json_encode($leaveRequest->manual_approvers ?? []) !== json_encode($manualApprovers);

            // Set reason to null if leave type is not unpaid
            $reason = $request->reason;
            if ($leaveType && $leaveType->category !== 'unpaid') {
                $reason = null;
            }

            // Prepare update data
            $updateData = [
                'employee_id' => $request->employee_id,
                'leave_type_id' => $request->leave_type_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'back_to_work_date' => $request->back_to_work_date,
                'reason' => $reason,
                'total_days' => $totalDays,
                'leave_period' => $request->leave_period,
                'supporting_document' => $supportingDocumentPath,
                'manual_approvers' => $manualApprovers,
            ];

            // Add LSL flexible fields if it's LSL
            if ($isLSL) {
                $updateData['lsl_taken_days'] = $takenDays;
                $updateData['lsl_cashout_days'] = $cashoutDays;
            } else {
                // Reset LSL fields for non-LSL leave types
                $updateData['lsl_taken_days'] = 0;
                $updateData['lsl_cashout_days'] = 0;
            }

            $leaveRequest->update($updateData);

            // Sync flight request from fr_data (same as store: replace existing)
            $leaveRequest->flightRequests()->each(function ($fr) {
                $fr->delete();
            });
            FlightRequest::createFromFrData($request, $leaveRequest);

            // If approvers changed and there are existing approval plans, delete them
            // (They will be recreated when document is submitted)
            if ($approversChanged) {
                ApprovalPlan::where('document_id', $leaveRequest->id)
                    ->where('document_type', 'leave_request')
                    ->delete();
                Log::info("Deleted existing approval plans for leave_request {$leaveRequest->id} due to approver changes");
            }

            // Update auto conversion date based on leave type and document status
            $leaveRequest->updateAutoConversionDate($request->leave_type_id);

            DB::commit();

            // Samakan dengan function store: jika request.route adalah my-requests.update, redirect ke my-requests WALAU yang akses admin/HR
            /** @var \App\Models\User $user */
            $user = auth()->user();
            if ($request->route() && in_array($request->route()->getName(), ['leave.my-requests.update', 'leave.my-requests.edit', 'leave.my-requests'])) {
                return redirect()->route('leave.my-requests')
                    ->with('toast_success', 'Leave request updated successfully.');
            }

            return redirect()->route('leave.requests.show', $leaveRequest)
                ->with('toast_success', 'Leave request updated successfully.');
        } catch (\Exception $e) {
            DB::rollback();

            return back()->with(['toast_error' => 'Failed to update leave request: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Download supporting document
     *
     * ADMIN/HR: Can download any leave request document (permission: leave-requests.show)
     * PERSONAL/USER: Can only download their own leave request documents (permission: personal.leave.view-own)
     *
     * Note: Protected by middleware 'permission:leave-requests.show' for admin
     * Personal users need manual check
     */
    public function download(LeaveRequest $leaveRequest)
    {
        $user = auth()->user();

        // If personal user (has personal permission but not admin permission), ensure they can only download their own
        if ($user->can('personal.leave.view-own') && ! $user->can('leave-requests.show')) {
            if ($leaveRequest->employee_id !== $user->employee_id) {
                abort(403, 'You can only download your own leave request documents.');
            }
        }

        if ($r = $this->guardLeaveRequestProjectForHrUser($leaveRequest)) {
            return $r;
        }

        if (! $leaveRequest->supporting_document) {
            abort(404, 'Document not found');
        }

        if (! Storage::disk('private')->exists($leaveRequest->supporting_document)) {
            abort(404, 'File not found');
        }

        return response()->streamDownload(function () use ($leaveRequest) {
            echo Storage::disk('private')->get($leaveRequest->supporting_document);
        }, basename($leaveRequest->supporting_document));
    }

    /**
     * Delete supporting document
     *
     * ADMIN/HR: Can delete any leave request document (permission: leave-requests.edit)
     * PERSONAL/USER: Can only delete their own leave request documents (permission: personal.leave.edit-own)
     *
     * Note: Protected by middleware 'permission:leave-requests.edit' for admin
     * Personal users need manual check
     */
    public function deleteDocument(LeaveRequest $leaveRequest)
    {
        $user = auth()->user();

        // If personal user (has personal permission but not admin permission), ensure they can only delete their own
        if ($user->can('personal.leave.edit-own') && ! $user->can('leave-requests.edit')) {
            if ($leaveRequest->employee_id !== $user->employee_id) {
                abort(403, 'You can only delete your own leave request documents.');
            }
        }

        if ($r = $this->guardLeaveRequestProjectForHrUser($leaveRequest)) {
            return $r;
        }

        if (! $leaveRequest->supporting_document) {
            return back()->with(['toast_error' => 'No document found to delete.']);
        }

        if ($leaveRequest->status !== 'pending') {
            return back()->with(['toast_error' => 'Only pending leave requests can have their documents deleted.']);
        }

        try {
            // Delete the file and folder
            $this->deleteSupportingDocument($leaveRequest);

            // Update the database
            $leaveRequest->update(['supporting_document' => null]);

            // Set auto conversion date since document is deleted
            $leaveRequest->setAutoConversionDate();

            return back()->with(['toast_success' => 'Supporting document deleted successfully.']);
        } catch (\Exception $e) {
            return back()->with(['toast_error' => 'Failed to delete document: ' . $e->getMessage()]);
        }
    }

    /**
     * Upload supporting document
     *
     * ADMIN/HR: Can upload documents for any leave request (permission: leave-requests.edit)
     * PERSONAL/USER: Can only upload documents for their own leave requests (permission: personal.leave.edit-own)
     *
     * Note: Protected by middleware 'permission:leave-requests.edit' for admin
     * Personal users need manual check
     */
    public function upload(Request $request, LeaveRequest $leaveRequest)
    {
        $user = auth()->user();

        // If personal user (has personal permission but not admin permission), ensure they can only upload for their own
        if ($user->can('personal.leave.edit-own') && ! $user->can('leave-requests.edit')) {
            if ($leaveRequest->employee_id !== $user->employee_id) {
                abort(403, 'You can only upload documents for your own leave requests.');
            }
        }

        if ($r = $this->guardLeaveRequestProjectForHrUser($leaveRequest)) {
            return $r;
        }

        // Check if leave request can have documents uploaded
        if (in_array($leaveRequest->status, ['closed', 'cancelled'])) {
            return back()->with(['toast_error' => 'Cannot upload document for closed or cancelled leave requests.']);
        }

        $request->validate([
            'supporting_document' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120', // 5MB max
        ]);

        try {
            // Delete existing document if any
            if ($leaveRequest->supporting_document) {
                $this->deleteSupportingDocument($leaveRequest);
            }

            // Store the new document
            $file = $request->file('supporting_document');
            $fileName = 'leave_request_' . $leaveRequest->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $filePath = 'leave_requests/' . $fileName;

            Storage::disk('private')->put($filePath, file_get_contents($file));

            // Update the database
            $leaveRequest->update(['supporting_document' => $filePath]);

            return back()->with(['toast_success' => 'Supporting document uploaded successfully.']);
        } catch (\Exception $e) {
            return back()->with(['toast_error' => 'Failed to upload document: ' . $e->getMessage()]);
        }
    }

    /**
     * Permanently delete a leave request before any approval action.
     *
     * ADMIN/HR: leave-requests.delete (scoped by project assignment)
     * PERSONAL: personal.leave.edit-own, own employee only
     */
    public function destroy(LeaveRequest $leaveRequest): RedirectResponse
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        if (
            ! $user->can('leave-requests.delete')
            && ! ($user->can('personal.leave.edit-own') && $leaveRequest->employee_id === $user->employee_id)
        ) {
            abort(403, 'You do not have permission to delete this leave request.');
        }

        if ($r = $this->guardLeaveRequestProjectForHrUser($leaveRequest)) {
            return $r;
        }

        if (! $leaveRequest->canBeDeletedBeforeApproval()) {
            return back()->with('toast_error', 'This leave request cannot be deleted because approval has already started or the status is not eligible.');
        }

        $redirectToMyList = $leaveRequest->employee_id === $user->employee_id
            && ! $user->can('leave-requests.show');

        DB::beginTransaction();
        try {
            $this->deleteSupportingDocument($leaveRequest);

            ApprovalPlan::where('document_id', $leaveRequest->id)
                ->where('document_type', 'leave_request')
                ->delete();

            $leaveRequest->flightRequests()->delete();

            $leaveRequest->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Failed to delete leave request: ' . $e->getMessage(), [
                'leave_request_id' => $leaveRequest->id,
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('toast_error', 'Failed to delete leave request: ' . $e->getMessage());
        }

        if ($redirectToMyList) {
            return redirect()->route('leave.my-requests')
                ->with('toast_success', 'Leave request deleted successfully.');
        }

        return redirect()->route('leave.requests.index')
            ->with('toast_success', 'Leave request deleted successfully.');
    }

    /**
     * Delete supporting document and its folder
     */
    private function deleteSupportingDocument(LeaveRequest $leaveRequest)
    {
        if ($leaveRequest->supporting_document) {
            $filePath = $leaveRequest->supporting_document;
            $folderPath = dirname($filePath);

            // Delete the file
            if (Storage::disk('private')->exists($filePath)) {
                Storage::disk('private')->delete($filePath);
            }

            // Delete the folder if it's empty
            if (Storage::disk('private')->exists($folderPath)) {
                $files = Storage::disk('private')->files($folderPath);
                if (empty($files)) {
                    Storage::disk('private')->deleteDirectory($folderPath);
                }
            }
        }
    }

    /**
     * Get employees by project for AJAX
     */
    public function getEmployeesByProject(int|string $projectId)
    {
        $project = Project::findOrFail($projectId);

        if (! UserProject::canAccessProjectId((int) $project->id)) {
            abort(403);
        }

        $employees = Administration::with(['employee', 'position'])
            ->where('project_id', $projectId)
            ->where('is_active', 1)
            ->orderBy('nik', 'asc')
            ->get()
            ->map(function ($admin) {
                return [
                    'id' => $admin->employee_id,
                    'fullname' => $admin->employee->fullname,
                    'position' => $admin->position->position_name ?? 'N/A',
                    'nik' => $admin->nik ?? 'N/A',
                ];
            });

        return response()->json(['employees' => $employees]);
    }

    /**
     * Get leave types by employee for AJAX
     */
    public function getLeaveTypesByEmployee(string $employeeId)
    {
        // Check if user is accessing their own data or has admin permission
        $user = Auth::user();
        $isPersonalUser = $user->can('personal.leave.create-own') && ! $user->can('leave-requests.show');

        if ($isPersonalUser && $user->employee_id !== $employeeId) {
            return response()->json([
                'success' => false,
                'message' => 'You can only view your own leave types',
            ], 403);
        }

        $today = now()->toDateString();

        $entitlements = LeaveEntitlement::where('employee_id', $employeeId)
            ->whereRaw('(entitled_days - taken_days) > 0') // remaining_days is now accessor
            ->where('period_start', '<=', $today)
            ->where('period_end', '>=', $today)
            ->with(['leaveType' => function ($query) {
                $query->where('is_active', 1)->orderBy('code', 'asc');
            }])
            ->join('leave_types', 'leave_entitlements.leave_type_id', '=', 'leave_types.id')
            ->where('leave_types.is_active', 1) // Only show active leave types
            ->orderBy('leave_types.code', 'asc')
            ->select('leave_entitlements.*')
            ->get()
            ->filter(function ($entitlement) {
                // Additional filter: ensure leaveType relationship exists and is active
                return $entitlement->leaveType && $entitlement->leaveType->is_active;
            })
            ->map(function ($entitlement) {
                return [
                    'entitlement_id' => $entitlement->id, // Add entitlement ID
                    'leave_type_id' => $entitlement->leave_type_id,
                    'leave_type' => [
                        'name' => $entitlement->leaveType->name,
                        'code' => $entitlement->leaveType->code,
                    ],
                    'period_start' => $entitlement->period_start->format('Y-m-d'),
                    'period_end' => $entitlement->period_end->format('Y-m-d'),
                    'period_display' => $entitlement->period_start->format('d M Y') . ' - ' . $entitlement->period_end->format('d M Y'),
                    'remaining_days' => $entitlement->remaining_days, // Accessor will calculate automatically
                    'entitled_days' => $entitlement->entitled_days,
                    'taken_days' => $entitlement->taken_days,
                ];
            })
            ->sortBy('leave_type.code')
            ->values();

        return response()->json(['leaveTypes' => $entitlements]);
    }

    /**
     * Get project information including leave_type for AJAX
     */
    public function getProjectInfo(int|string $projectId)
    {
        $project = Project::findOrFail($projectId);

        if (! UserProject::canAccessProjectId((int) $project->id)) {
            abort(403);
        }

        return response()->json([
            'project' => [
                'id' => $project->id,
                'name' => $project->project_name,
                'code' => $project->project_code,
                'leave_type' => $project->leave_type,
            ],
        ]);
    }

    public function getLeavePeriod(string $employeeId, int|string $leaveTypeId)
    {
        // Check if user is accessing their own data or has admin permission
        $user = Auth::user();
        $isPersonalUser = $user->can('personal.leave.create-own') && ! $user->can('leave-requests.show');

        if ($isPersonalUser && $user->employee_id !== $employeeId) {
            return response()->json([
                'success' => false,
                'message' => 'You can only view your own leave period',
            ], 403);
        }

        $today = now()->toDateString();

        $leaveEntitlement = LeaveEntitlement::where('employee_id', $employeeId)
            ->where('leave_type_id', $leaveTypeId)
            ->where('period_start', '<=', $today)
            ->where('period_end', '>=', $today)
            ->first();

        if ($leaveEntitlement) {
            // Format tanggal menjadi format yang lebih user-friendly
            $startDate = \Carbon\Carbon::parse($leaveEntitlement->period_start)->format('d M Y');
            $endDate = \Carbon\Carbon::parse($leaveEntitlement->period_end)->format('d M Y');
            $formattedPeriod = $startDate . ' - ' . $endDate;

            return response()->json([
                'success' => true,
                'leave_period' => $formattedPeriod,
                'period_start' => $leaveEntitlement->period_start,
                'period_end' => $leaveEntitlement->period_end,
                'entitled_days' => $leaveEntitlement->entitled_days,
                'remaining_days' => $leaveEntitlement->remaining_days,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No active leave entitlement found for this employee and leave type',
        ]);
    }

    /**
     * Create roster adjustment if employee is on roster-based project
     */
    private function createRosterAdjustment(LeaveRequest $leaveRequest)
    {
        $administration = $leaveRequest->administration;
        $roster = \App\Models\Roster::where('employee_id', $leaveRequest->employee_id)
            ->where('is_active', 1)
            ->where('start_date', '<=', $leaveRequest->start_date)
            ->where('end_date', '>=', $leaveRequest->end_date)
            ->first();

        if ($roster) {
            $roster->addAdjustment(
                $leaveRequest->id,
                '-days',
                $leaveRequest->total_days,
                'Leave request: ' . $leaveRequest->leaveType->name
            );
        }
    }

    /**
     * Get employee leave balance for AJAX calls
     */
    public function getEmployeeLeaveBalance(string $employeeId)
    {
        try {
            $employee = Employee::findOrFail($employeeId);

            // Check if user is accessing their own data or has admin permission
            $user = Auth::user();
            $isPersonalUser = $user->can('personal.leave.create-own') && ! $user->can('leave-requests.show');

            if ($isPersonalUser && $user->employee_id !== $employeeId) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only view your own leave balance',
                ], 403);
            }

            $today = now()->toDateString();

            $leaveEntitlements = LeaveEntitlement::where('employee_id', $employeeId)
                ->where('period_start', '<=', $today)
                ->where('period_end', '>=', $today)
                ->with(['leaveType' => function ($query) {
                    $query->orderBy('code', 'asc');
                }])
                ->join('leave_types', 'leave_entitlements.leave_type_id', '=', 'leave_types.id')
                ->orderBy('leave_types.code', 'asc')
                ->select('leave_entitlements.*')
                ->get();

            $balanceData = $leaveEntitlements->map(function ($entitlement) {
                return [
                    'leave_type' => $entitlement->leaveType->name,
                    'leave_type_code' => $entitlement->leaveType->code,
                    'entitled_days' => $entitlement->entitled_days,
                    'remaining_days' => $entitlement->remaining_days,
                    'used_days' => $entitlement->used_days,
                    'period_start' => $entitlement->period_start,
                    'period_end' => $entitlement->period_end,
                ];
            })
                ->sortBy('leave_type_code')
                ->values();

            // Get employee administration data for approval flow
            // OLD APPROVAL SYSTEM - COMMENTED OUT (using manual approvers now)
            // $administration = $employee->administrations->where('is_active', 1)->first();
            // $projectId = $administration->project_id ?? null;
            // $departmentId = $administration->position->department_id ?? null;
            // $levelId = $administration->level_id ?? null;

            return response()->json([
                'success' => true,
                'employee' => [
                    'id' => $employee->id,
                    'name' => $employee->fullname,
                    // OLD APPROVAL SYSTEM - COMMENTED OUT (using manual approvers now)
                    // 'nik' => $administration->nik ?? 'N/A',
                    // 'project_id' => $projectId,
                    // 'department_id' => $departmentId,
                    // 'level_id' => $levelId
                ],
                'leave_balance' => $balanceData,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to load leave balance', [
                'employee_id' => $employeeId,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load leave balance: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get leave type information for AJAX calls
     */
    /**
     * Get leave type information
     *
     * ADMIN/HR: Can access with permission:leave-requests.show
     * PERSONAL/USER: Can access with permission:personal.leave.create-own or personal.leave.edit-own
     */
    public function getLeaveTypeInfo(int|string $leaveTypeId)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        // Check if user has either admin or personal permission
        if (
            ! $user->can('leave-requests.show') &&
            ! $user->can('personal.leave.create-own') &&
            ! $user->can('personal.leave.edit-own')
        ) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized. You do not have permission to access this resource.',
            ], 403);
        }

        try {
            $leaveType = LeaveType::findOrFail($leaveTypeId);

            return response()->json([
                'success' => true,
                'leave_type' => [
                    'id' => $leaveType->id,
                    'name' => $leaveType->name,
                    'code' => $leaveType->code,
                    'category' => $leaveType->category,
                    'description' => $leaveType->description,
                    'max_days_per_year' => $leaveType->max_days_per_year,
                    'requires_approval' => $leaveType->requires_approval,
                    'is_active' => $leaveType->is_active,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load leave type info: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update leave entitlements for approved leave request
     * Using same logic as ApprovalPlanController for consistency
     */
    private function updateLeaveEntitlements(LeaveRequest $leaveRequest): void
    {
        try {
            Log::info('Updating leave entitlements for leave request (immediate calculation)', [
                'leave_request_id' => $leaveRequest->id,
                'employee_id' => $leaveRequest->employee_id,
                'leave_type_id' => $leaveRequest->leave_type_id,
                'total_days' => $leaveRequest->total_days,
            ]);

            // Find the matching entitlement for this employee and leave type
            $entitlement = LeaveEntitlement::where('employee_id', $leaveRequest->employee_id)
                ->where('leave_type_id', $leaveRequest->leave_type_id)
                ->where('period_start', '<=', $leaveRequest->start_date)
                ->where('period_end', '>=', $leaveRequest->end_date)
                ->first();

            if ($entitlement) {
                // Update taken days
                $oldTakenDays = $entitlement->taken_days;
                $entitlement->taken_days += $leaveRequest->total_days;

                // remaining_days is now calculated via accessor, no need to update manually
                $entitlement->save();

                Log::info('Successfully updated leave entitlements (immediate calculation)', [
                    'leave_request_id' => $leaveRequest->id,
                    'employee_id' => $leaveRequest->employee_id,
                    'leave_type_id' => $leaveRequest->leave_type_id,
                    'total_days_requested' => $leaveRequest->total_days,
                    'old_taken_days' => $oldTakenDays,
                    'new_taken_days' => $entitlement->taken_days,
                    'remaining_days' => $entitlement->remaining_days,
                    'entitled_days' => $entitlement->entitled_days,
                ]);
            } else {
                Log::warning('No entitlement found for leave request (immediate calculation)', [
                    'leave_request_id' => $leaveRequest->id,
                    'employee_id' => $leaveRequest->employee_id,
                    'leave_type_id' => $leaveRequest->leave_type_id,
                    'start_date' => $leaveRequest->start_date,
                    'end_date' => $leaveRequest->end_date,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error updating leave entitlements (immediate calculation): ' . $e->getMessage(), [
                'leave_request_id' => $leaveRequest->id,
                'employee_id' => $leaveRequest->employee_id,
                'leave_type_id' => $leaveRequest->leave_type_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
        }
    }

    /**
     * Close leave request
     *
     * ADMIN/HR: Can close any leave request (permission: leave-requests.delete)
     * PERSONAL/USER: Can close their own leave requests (if allowed by business logic)
     *
     * Note: Protected by middleware 'permission:leave-requests.delete' for admin
     */
    public function close(LeaveRequest $leaveRequest)
    {
        $user = auth()->user();

        // If personal user (has personal permission but not admin permission), ensure they can only close their own
        if ($user->can('personal.leave.edit-own') && ! $user->can('leave-requests.delete')) {
            if ($leaveRequest->employee_id !== $user->employee_id) {
                abort(403, 'You can only close your own leave requests.');
            }
        }

        if ($r = $this->guardLeaveRequestProjectForHrUser($leaveRequest)) {
            return $r;
        }

        if (! $leaveRequest->canBeClosed()) {
            return back()->with(['toast_error' => 'This leave request cannot be closed.']);
        }

        try {
            $leaveRequest->close($user->id);

            return back()->with(['toast_success' => 'Leave request closed successfully.']);
        } catch (\Exception $e) {
            return back()->with(['toast_error' => 'Failed to close leave request: ' . $e->getMessage()]);
        }
    }

    /**
     * Show cancellation request form
     *
     * ADMIN/HR: leave-requests.cancel + leave-requests.show
     * PERSONAL/USER: personal.leave.cancel-own (own requests only)
     */
    public function showCancellationForm(LeaveRequest $leaveRequest)
    {
        $this->assertCanRequestLeaveCancellation($this->authUser(), $leaveRequest);

        if ($r = $this->guardLeaveRequestProjectForHrUser($leaveRequest)) {
            return $r;
        }

        if (! $leaveRequest->canBeCancelled()) {
            return back()->with(['toast_error' => 'This leave request cannot be cancelled.']);
        }

        return view('leave-requests.cancellation-form', compact('leaveRequest'))
            ->with('title', 'Request Leave Cancellation');
    }

    /**
     * Store cancellation request
     *
     * ADMIN/HR: leave-requests.cancel + leave-requests.show
     * PERSONAL/USER: personal.leave.cancel-own (own requests only)
     */
    public function storeCancellation(Request $request, LeaveRequest $leaveRequest)
    {
        $user = $this->authUser();
        $this->assertCanRequestLeaveCancellation($user, $leaveRequest);

        if ($r = $this->guardLeaveRequestProjectForHrUser($leaveRequest)) {
            return $r;
        }

        // Calculate available days to cancel (total days minus already cancelled days)
        $totalCancelledDays = $leaveRequest->getTotalCancelledDays();
        $availableDaysToCancel = $leaveRequest->total_days - $totalCancelledDays;

        if ($availableDaysToCancel <= 0) {
            return back()->with(['toast_error' => 'All days from this leave request have already been cancelled.']);
        }

        $request->validate([
            'days_to_cancel' => 'required|integer|min:1|max:' . $availableDaysToCancel,
            'reason' => 'required|string|max:1000',
        ], [
            'days_to_cancel.max' => "You can only cancel up to {$availableDaysToCancel} day(s). {$totalCancelledDays} day(s) have already been cancelled from this leave request.",
        ]);

        try {
            $leaveRequest->requestCancellation(
                $request->days_to_cancel,
                $request->reason,
                $user->id
            );

            // Redirect sesuai route asal - TANPA permission check, HANYA berdasarkan route name atau URL
            // Jika dari my-requests.cancellation (POST dari my-requests.cancellation-form), redirect ke my-requests.show
            // Jika dari cancellation (POST dari cancellation-form), redirect ke leave.requests.show

            // Ambil route name, path, dan URL untuk pengecekan
            $routeName = $request->route() ? $request->route()->getName() : null;
            $fullUrl = $request->fullUrl();
            $path = $request->path();

            // Debug logging (uncomment untuk debug)
            // \Log::info('Cancellation redirect debug', [
            //     'route_name' => $routeName,
            //     'path' => $path,
            //     'full_url' => $fullUrl,
            //     'user_id' => $user->id,
            //     'has_cancel_permission' => $user->can('leave-requests.cancel'),
            //     'has_show_permission' => $user->can('leave-requests.show')
            // ]);

            // Check route name - ini adalah cara paling reliable
            // Route name untuk my-requests: 'leave.my-requests.cancellation'
            // Route name untuk admin: 'leave.requests.cancellation'
            $isFromMyRequests = false;

            // Priority 1: Check route name
            if ($routeName === 'leave.my-requests.cancellation') {
                $isFromMyRequests = true;
            }
            // Priority 2: Check path (lebih reliable dari URL)
            elseif (strpos($path, 'my-requests') !== false) {
                $isFromMyRequests = true;
            }
            // Priority 3: Check full URL sebagai fallback terakhir
            elseif (strpos($fullUrl, '/my-requests/') !== false) {
                $isFromMyRequests = true;
            }

            if ($isFromMyRequests) {
                // Dari my-requests.cancellation-form, redirect ke my-requests.show
                return redirect()->route('leave.my-requests.show', $leaveRequest)
                    ->with('toast_success', 'Cancellation request submitted successfully. Waiting for HR confirmation.');
            }

            // Default: dari cancellation-form (leave.requests.cancellation), redirect ke leave.requests.show
            return redirect()->route('leave.requests.show', $leaveRequest)
                ->with('toast_success', 'Cancellation request submitted successfully. Waiting for HR confirmation.');
        } catch (\Exception $e) {
            return back()->with(['toast_error' => 'Failed to submit cancellation request: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Approve cancellation request
     */
    public function approveCancellation(Request $request, LeaveRequestCancellation $cancellation)
    {
        $cancellation->load('leaveRequest.administration');
        if ($cancellation->leaveRequest && ($r = $this->guardLeaveRequestProjectForHrUser($cancellation->leaveRequest))) {
            return $r;
        }

        if (! $cancellation->isPending()) {
            return back()->with(['toast_error' => 'This cancellation request has already been processed.']);
        }

        $request->validate([
            'confirmation_notes' => 'nullable|string|max:1000',
        ]);

        try {
            $cancellation->approve(Auth::id(), $request->confirmation_notes);

            return back()->with(['toast_success' => 'Cancellation request approved successfully.']);
        } catch (\Exception $e) {
            return back()->with(['toast_error' => 'Failed to approve cancellation request: ' . $e->getMessage()]);
        }
    }

    /**
     * Reject cancellation request
     */
    public function rejectCancellation(Request $request, LeaveRequestCancellation $cancellation)
    {
        $cancellation->load('leaveRequest.administration');
        if ($cancellation->leaveRequest && ($r = $this->guardLeaveRequestProjectForHrUser($cancellation->leaveRequest))) {
            return $r;
        }

        if (! $cancellation->isPending()) {
            return back()->with(['toast_error' => 'This cancellation request has already been processed.']);
        }

        $request->validate([
            'confirmation_notes' => 'required|string|max:1000',
        ]);

        try {
            $cancellation->reject(Auth::id(), $request->confirmation_notes);

            return back()->with(['toast_success' => 'Cancellation request rejected successfully.']);
        } catch (\Exception $e) {
            return back()->with(['toast_error' => 'Failed to reject cancellation request: ' . $e->getMessage()]);
        }
    }

    // ========================================
    // SELF-SERVICE METHODS FOR USER ROLE
    // ========================================

    /**
     * Display user's own leave requests
     */
    public function myRequests()
    {
        return view('leave-requests.my-requests', [
            'title' => 'My Leave Request',
        ]);
    }

    /**
     * Show the form for creating a new leave request for personal user
     */
    public function myRequestsCreate(Request $request)
    {
        $leaveTypes = LeaveType::where('is_active', true)->orderBy('code', 'asc')->get();

        $user = Auth::user();
        $employee = $user->employee;
        $administration = $employee?->activeAdministration;
        if ($administration) {
            $administration->load(['project', 'position.department']);
        }
        $project = $administration?->project;
        $department = $administration?->position?->department;

        // Projects from user_project assignment
        $projects = UserProject::projectsForSelect();

        // Get all active departments for department selection (if needed)
        $departments = Department::where('department_status', 1)->get();

        // Get pre-selected leave type from query parameter
        $selectedLeaveTypeId = $request->query('leave_type');

        $nationalHolidayDates = NationalHoliday::datesForJs();
        $nationalHolidayMap = NationalHoliday::mapDateToNameForJs();

        return view('leave-requests.my-create', compact('leaveTypes', 'projects', 'departments', 'nationalHolidayDates', 'nationalHolidayMap'))
            ->with('title', 'Create My Leave Request')
            ->with('defaultEmployeeId', $user->employee_id)
            ->with('defaultProject', $project)
            ->with('defaultDepartment', $department)
            ->with('defaultAdministration', $administration)
            ->with('selectedLeaveTypeId', $selectedLeaveTypeId);
    }

    /**
     * Store a newly created leave request for personal user
     */
    public function myRequestsStore(Request $request)
    {
        // Force employee_id to current user's employee_id
        $request->merge(['employee_id' => auth()->user()->employee_id]);

        // Call the existing store method which will handle all logic
        // The store method already has redirect logic for personal users
        return $this->store($request);
    }

    /**
     * Display own leave request details for personal user
     *
     * PERSONAL/USER: Can only view their own leave requests (permission: personal.leave.view-own)
     */
    public function myRequestsShow(LeaveRequest $leaveRequest)
    {
        $user = auth()->user();

        // Ensure user can only view their own leave requests
        if ($leaveRequest->employee_id !== $user->employee_id) {
            abort(403, 'You can only view your own leave requests.');
        }

        $leaveRequest->load([
            'employee.administrations.project',
            'employee.administrations.position',
            'leaveType',
            'approvalPlans',
            'cancellations.requestedBy',
            'cancellations.confirmedBy',
        ]);

        return view('leave-requests.show', compact('leaveRequest'))
            ->with('title', 'My Leave Request Details');
    }

    /**
     * Show the form for editing own leave request for personal user
     *
     * PERSONAL/USER: Can only edit their own leave requests (permission: personal.leave.edit-own)
     * Only allowed for status: 'draft', 'pending' (not yet approved)
     */
    public function myRequestsEdit(LeaveRequest $leaveRequest)
    {
        $user = auth()->user();

        // Ensure user can only edit their own leave requests
        if ($leaveRequest->employee_id !== $user->employee_id) {
            abort(403, 'You can only edit your own leave requests.');
        }

        // Personal users can only edit leave requests that are not yet approved
        // Allowed statuses: 'draft', 'pending'
        // Not allowed: 'approved', 'rejected', 'cancelled', 'closed', 'auto_approved'
        if (! in_array($leaveRequest->status, ['draft', 'pending'])) {
            return redirect()->route('leave.my-requests')
                ->with('toast_error', 'You can only edit leave requests that are not yet approved.');
        }

        // Load leave types for leave type selection
        $leaveTypes = LeaveType::where('is_active', true)->orderBy('code', 'asc')->get();

        // Load projects for project selection (needed for JavaScript)
        $projects = UserProject::projectsForSelect();

        // Get all active departments for department selection (needed for JavaScript)
        $departments = Department::where('department_status', 1)->get();

        $leaveRequest->load(['flightRequests.details']);
        $existingFlightRequest = $leaveRequest->flightRequests()->with('details')->first();

        $nationalHolidayDates = NationalHoliday::datesForJs();
        $nationalHolidayMap = NationalHoliday::mapDateToNameForJs();

        return view('leave-requests.my-edit', compact('leaveRequest', 'leaveTypes', 'projects', 'departments', 'existingFlightRequest', 'nationalHolidayDates', 'nationalHolidayMap'))
            ->with('title', 'Edit My Leave Request');
    }

    /**
     * Update own leave request for personal user
     *
     * PERSONAL/USER: Can only update their own leave requests (permission: personal.leave.edit-own)
     * Only allowed for status: 'draft', 'pending' (not yet approved)
     */
    public function myRequestsUpdate(Request $request, LeaveRequest $leaveRequest)
    {
        $user = auth()->user();

        // Ensure user can only update their own leave requests
        if ($leaveRequest->employee_id !== $user->employee_id) {
            abort(403, 'You can only update your own leave requests.');
        }

        // Personal users can only update leave requests that are not yet approved
        // Allowed statuses: 'draft', 'pending'
        // Not allowed: 'approved', 'rejected', 'cancelled', 'closed', 'auto_approved'
        if (! in_array($leaveRequest->status, ['draft', 'pending'])) {
            return redirect()->route('leave.my-requests')
                ->with('toast_error', 'You can only update leave requests that are not yet approved.');
        }

        // Force employee_id to current user's employee_id to prevent tampering
        $request->merge(['employee_id' => $user->employee_id]);

        // Call the existing update method which will handle all logic
        // The update method already has redirect logic for personal users (line 803-805)
        // It will automatically redirect to my-requests for personal users
        return $this->update($request, $leaveRequest);
    }

    /**
     * Get data for user's own leave requests DataTable
     */
    public function myRequestsData(Request $request)
    {
        $user = Auth::user();

        $query = LeaveRequest::with(['leaveType', 'administration.project', 'requestedBy', 'approvalPlans'])
            ->where('employee_id', $user->employee_id)
            ->select('leave_requests.*')
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('leave_type_id')) {
            $query->where('leave_type_id', $request->leave_type_id);
        }

        if ($request->filled('start_date')) {
            $query->where('start_date', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->where('end_date', '<=', $request->end_date);
        }

        return datatables()->of($query)
            ->addIndexColumn()
            ->addColumn('register_number', function ($row) {
                return e($row->register_number ?? '—');
            })
            ->addColumn('project', function ($row) {
                return e($row->administration?->project?->project_code ?? '—');
            })
            ->addColumn('leave_type', function ($row) {
                return '<span class="badge badge-info">' . ($row->leaveType->name ?? 'N/A') . '</span>';
            })
            ->addColumn('start_date', function ($row) {
                return $row->start_date ? \Carbon\Carbon::parse($row->start_date)->format('d/m/Y') : 'N/A';
            })
            ->addColumn('end_date', function ($row) {
                return $row->end_date ? \Carbon\Carbon::parse($row->end_date)->format('d/m/Y') : 'N/A';
            })
            ->addColumn('total_days', function ($row) {
                return $row->total_days . ' days';
            })
            ->addColumn('status_badge', function ($row) {
                $badges = [
                    'draft' => '<span class="badge badge-secondary">Draft</span>',
                    'pending' => '<span class="badge badge-warning">Pending</span>',
                    'approved' => '<span class="badge badge-success">Approved</span>',
                    'rejected' => '<span class="badge badge-danger">Rejected</span>',
                    'cancelled' => '<span class="badge badge-dark">Cancelled</span>',
                    'closed' => '<span class="badge badge-info">Closed</span>',
                    'auto_approved' => '<span class="badge badge-success">Auto Approved</span>',
                ];

                return $badges[$row->status] ?? '<span class="badge badge-secondary">' . e(ucfirst((string) $row->status)) . '</span>';
            })
            ->addColumn('requested_at', function ($row) {
                return $row->requested_at ? \Carbon\Carbon::parse($row->requested_at)->format('d/m/Y H:i') : 'N/A';
            })
            ->addColumn('action', function ($row) use ($user) {
                $btn = '<div class="btn-group" role="group">';

                $btn .= '<a href="' . route('leave.my-requests.show', $row->id) . '" class="btn btn-info btn-sm mr-1"><i class="fas fa-eye"></i></a>';

                if ($row->status === 'draft' || $row->status === 'pending') {
                    $btn .= '<a href="' . route('leave.my-requests.edit', $row->id) . '" class="btn btn-warning btn-sm mr-1"><i class="fas fa-edit"></i></a>';
                }

                if ($row->canBeDeletedBeforeApproval() && $user->can('personal.leave.edit-own')) {
                    $btn .= '<form method="POST" action="' . route('leave.my-requests.destroy', $row->id) . '" class="d-inline" onsubmit="return confirm(\'Delete this leave request? This cannot be undone.\');">';
                    $btn .= csrf_field() . method_field('DELETE');
                    $btn .= '<button type="submit" class="btn btn-danger btn-sm mr-1" title="Delete"><i class="fas fa-trash"></i></button></form>';
                }

                $btn .= '</div>';

                return $btn;
            })
            ->rawColumns(['leave_type', 'status_badge', 'action'])
            ->make(true);
    }

    /**
     * Display user's leave entitlements
     */
    public function myEntitlements()
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (! $employee) {
            return redirect()->route('dashboard.personal')
                ->with('toast_error', 'Employee information not found. Please contact HR to setup your leave entitlements.');
        }

        $employee->load([
            'administrations.project',
            'administrations.level',
            'administrations.position',
        ]);

        // Recalculate taken_days from approved leave requests (considering cancellations)
        $entitlements = LeaveEntitlement::with(['leaveType'])
            ->where('employee_id', $employee->id)
            ->orderBy('period_end', 'desc')
            ->get()
            ->map(function ($entitlement) {
                // Recalculate taken_days from approved leave requests (effective days after cancellation)
                $approvedRequests = LeaveRequest::where('employee_id', $entitlement->employee_id)
                    ->where('leave_type_id', $entitlement->leave_type_id)
                    ->whereIn('status', ['approved', 'auto_approved'])
                    ->whereBetween('start_date', [$entitlement->period_start, $entitlement->period_end])
                    ->get();

                // Calculate effective taken days (total_days - cancelled_days)
                $effectiveTakenDays = $approvedRequests->sum(function ($request) {
                    return $request->getEffectiveDays();
                });

                // Update taken_days if different (to keep it in sync)
                if ($entitlement->taken_days != $effectiveTakenDays) {
                    $entitlement->taken_days = $effectiveTakenDays;
                    $entitlement->save();
                }

                return $entitlement;
            });

        return view('leave-requests.my-entitlements', compact('entitlements', 'employee'))
            ->with('title', 'My Leave Entitlements');
    }

    /**
     * Show calculation details for personal user's own entitlements
     */
    public function myEntitlementsCalculationDetails(Request $request)
    {
        $user = Auth::user();
        $employee = $user->employee;

        if (! $employee) {
            return redirect()->route('leave.my-entitlements')
                ->with('toast_error', 'Employee information not found.');
        }

        $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'period_start' => 'nullable|date',
            'period_end' => 'nullable|date|after:period_start',
        ]);

        $leaveTypeId = $request->leave_type_id;
        $periodStart = $request->period_start;
        $periodEnd = $request->period_end;

        // Get leave calculation details using the model method
        $calculationDetails = LeaveEntitlement::getEmployeeLeaveDetails(
            $employee->id,
            $leaveTypeId,
            $periodStart,
            $periodEnd
        );

        if (! $calculationDetails) {
            return redirect()->route('leave.my-entitlements')
                ->with('toast_error', 'No leave entitlement found for this leave type and period.');
        }

        // Load additional data for the view
        $employee->load(['administrations.project', 'administrations.level']);
        $leaveType = LeaveType::findOrFail($leaveTypeId);
        $fromMyEntitlementsCalculation = true;

        if (! $periodStart || ! $periodEnd) {
            $periodStart = $calculationDetails['entitlement_period']['start'] ?? null;
            $periodEnd = $calculationDetails['entitlement_period']['end'] ?? null;
        }

        return view('leave-entitlements.calculation-details', compact(
            'employee',
            'leaveType',
            'calculationDetails',
            'fromMyEntitlementsCalculation',
            'periodStart',
            'periodEnd'
        ))->with('title', 'Leave Calculation Details - ' . $employee->fullname);
    }

    /**
     * Show cancellation request form for personal user
     *
     * PERSONAL/USER: personal.leave.cancel-own (own requests only)
     */
    public function myRequestsCancellationForm(LeaveRequest $leaveRequest)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        // Ensure user can only cancel their own leave requests
        if ($leaveRequest->employee_id !== $user->employee_id) {
            abort(403, 'You can only cancel your own leave requests.');
        }

        // Call the existing showCancellationForm method which will handle all logic
        return $this->showCancellationForm($leaveRequest);
    }

    /**
     * Store cancellation request for personal user
     *
     * PERSONAL/USER: personal.leave.cancel-own (own requests only)
     */
    public function myRequestsStoreCancellation(Request $request, LeaveRequest $leaveRequest)
    {
        /** @var \App\Models\User $user */
        $user = auth()->user();

        // Ensure user can only cancel their own leave requests
        if ($leaveRequest->employee_id !== $user->employee_id) {
            abort(403, 'You can only cancel your own leave requests.');
        }

        // Call the existing storeCancellation method which will handle all logic
        // The storeCancellation method already has redirect logic for personal users
        return $this->storeCancellation($request, $leaveRequest);
    }

    // ========================================
    // PRIVATE HELPERS
    // ========================================

    private function authUser(): User
    {
        /** @var User|null $user */
        $user = auth()->user();
        if (! $user instanceof User) {
            abort(403);
        }

        return $user;
    }

    private function assertCanRequestLeaveCancellation(User $user, LeaveRequest $leaveRequest): void
    {
        if (! $user->can('leave-requests.cancel') && ! $user->can('personal.leave.cancel-own')) {
            abort(403, 'Unauthorized action. You do not have permission to cancel leave requests.');
        }

        $isHrCancellation = $user->can('leave-requests.cancel') && $user->can('leave-requests.show');

        if (! $isHrCancellation && $leaveRequest->employee_id !== $user->employee_id) {
            abort(403, 'You can only cancel your own leave requests.');
        }
    }

    /**
     * Billable leave days from start/end date (weekends & holidays excluded for non-roster projects).
     */
    private function computeBillableLeaveDaysFromRequest(Request $request): int
    {
        $projectForDays = $request->filled('project_id') ? Project::find($request->project_id) : null;
        if (! $projectForDays) {
            $adminForDays = Administration::where('employee_id', $request->employee_id)->where('is_active', 1)->first();
            $projectForDays = $adminForDays?->project_id ? Project::find($adminForDays->project_id) : null;
        }
        $isNonRosterForDays = $projectForDays && $projectForDays->isNonRosterProject();

        return NationalHoliday::countBillableLeaveDaysInRange(
            \Carbon\Carbon::parse($request->start_date)->toDateString(),
            \Carbon\Carbon::parse($request->end_date)->toDateString(),
            $isNonRosterForDays
        );
    }

    private function isLSLLeaveType(?LeaveType $leaveType): bool
    {
        return $leaveType && (
            str_contains(strtolower($leaveType->name), 'long service') ||
            str_contains(strtolower($leaveType->name), 'cuti panjang') ||
            str_contains(strtolower($leaveType->category), 'lsl')
        );
    }

    private function normalizeLSLUsageMode(?string $mode): string
    {
        return in_array($mode, ['leave_only', 'cashout_only', 'combined'], true) ? $mode : 'leave_only';
    }

    /**
     * @return array{usage_mode: string, taken_days: int, cashout_days: int, total_days: int}
     */
    private function processLSLFlexibleRequest(Request $request): array
    {
        $mode = $this->normalizeLSLUsageMode($request->input('lsl_usage_mode'));

        if ($mode === 'cashout_only') {
            $today = now()->toDateString();
            $request->merge([
                'start_date' => $request->input('start_date') ?: $today,
                'end_date' => $request->input('end_date') ?: $today,
                'back_to_work_date' => null,
            ]);
            $takenDays = 0;
            $cashoutDays = (int) ($request->lsl_cashout_days ?? 0);
        } elseif ($mode === 'leave_only') {
            $takenDays = (int) ($request->lsl_taken_days ?? 0);
            if ($takenDays <= 0) {
                $takenDays = $this->computeBillableLeaveDaysFromRequest($request);
            }
            $cashoutDays = 0;
        } else {
            $takenDays = (int) ($request->lsl_taken_days ?? 0);
            if ($takenDays <= 0) {
                $takenDays = $this->computeBillableLeaveDaysFromRequest($request);
            }
            $cashoutDays = (int) ($request->lsl_cashout_days ?? 0);
        }

        $totalDays = $takenDays + $cashoutDays;

        $request->merge([
            'total_days' => $totalDays,
            'lsl_taken_days' => $takenDays,
            'lsl_cashout_days' => $cashoutDays,
            'lsl_usage_mode' => $mode,
        ]);

        return [
            'usage_mode' => $mode,
            'taken_days' => $takenDays,
            'cashout_days' => $cashoutDays,
            'total_days' => $totalDays,
        ];
    }

    /**
     * @return array<string, string>|null
     */
    private function validateLSLFlexibleBusinessRules(string $mode, int $takenDays, int $cashoutDays, int $totalDays): ?array
    {
        if ($mode === 'cashout_only' && $cashoutDays <= 0) {
            return ['lsl_cashout_days' => 'Cash out days must be greater than 0 for cash-out-only requests.'];
        }

        if ($mode !== 'cashout_only' && $takenDays <= 0) {
            return ['start_date' => 'Leave days must be greater than 0. Please select a valid leave date range.'];
        }

        if ($totalDays <= 0) {
            return ['total_days' => 'Total days must be greater than 0.'];
        }

        if ($cashoutDays > $totalDays) {
            return ['lsl_cashout_days' => 'Cash out days cannot exceed total days.'];
        }

        return null;
    }

    private function findLeaveEntitlementForRequest(Request $request, int $employeeId, int $leaveTypeId): ?LeaveEntitlement
    {
        $start = $request->start_date;
        $end = $request->end_date;

        if ($start && $end) {
            $entitlement = LeaveEntitlement::where('employee_id', $employeeId)
                ->where('leave_type_id', $leaveTypeId)
                ->where('period_start', '<=', $start)
                ->where('period_end', '>=', $end)
                ->first();

            if ($entitlement) {
                return $entitlement;
            }
        }

        $today = now()->toDateString();

        return LeaveEntitlement::where('employee_id', $employeeId)
            ->where('leave_type_id', $leaveTypeId)
            ->where('period_start', '<=', $today)
            ->where('period_end', '>=', $today)
            ->first();
    }

    /**
     * Leave range may include national holidays (total_days still excludes them for non-roster via countBillableLeaveDaysInRange).
     * Back to work cannot fall on a national holiday.
     */
    private function validateLeaveDatesAgainstNationalHolidays(?string $backToWorkDate): void
    {
        if ($backToWorkDate) {
            $ymd = \Carbon\Carbon::parse($backToWorkDate)->format('Y-m-d');
            if (NationalHoliday::isHolidayDate($ymd)) {
                throw ValidationException::withMessages([
                    'back_to_work_date' => 'Tanggal kembali kerja tidak boleh jatuh pada hari libur nasional.',
                ]);
            }
        }
    }

    /**
     * HR (leave-requests.show|edit|delete): restrict by administration project via user_project.
     */
    private function guardLeaveRequestProjectForHrUser(LeaveRequest $leaveRequest): ?RedirectResponse
    {
        $user = auth()->user();
        if (! $user instanceof User) {
            return null;
        }
        if (! $user->can('leave-requests.show') && ! $user->can('leave-requests.edit') && ! $user->can('leave-requests.delete') && ! $user->can('leave-requests.cancel')) {
            return null;
        }
        $leaveRequest->loadMissing('administration');
        if (! $leaveRequest->administration) {
            return null;
        }

        return UserProject::guardProjectInAssignmentScope((int) $leaveRequest->administration->project_id);
    }
}
