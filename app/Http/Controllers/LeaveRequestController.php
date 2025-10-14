<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\LeaveRequestCancellation;
use App\Models\LeaveType;
use App\Models\LeaveEntitlement;
use App\Models\Employee;
use App\Models\Administration;
use App\Models\Project;
use App\Models\Department;
use App\Http\Controllers\ApprovalPlanController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class LeaveRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:leave-requests.show')->only('index', 'show', 'data', 'download', 'getProjectInfo', 'getLeavePeriod', 'getEmployeeLeaveBalance', 'getLeaveTypeInfo', 'getEmployeesByProject', 'getLeaveTypesByEmployee');
        $this->middleware('permission:leave-requests.create')->only('create', 'store', 'upload');
        $this->middleware('permission:leave-requests.edit')->only('edit', 'update', 'deleteDocument');
        $this->middleware('permission:leave-requests.delete')->only('destroy', 'showCancellationForm', 'storeCancellation', 'approveCancellation', 'rejectCancellation', 'close');
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('leave-requests.index')->with('title', 'Leave Requests');
    }

    /**
     * Get data for DataTables
     */

    public function data(Request $request)
    {
        $query = LeaveRequest::with(['employee', 'leaveType', 'administration'])
            ->select('leave_requests.*')->orderBy('created_at', 'desc');

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
                    ->orWhere('status', 'like', "%{$searchValue}%")
                    ->orWhere('total_days', 'like', "%{$searchValue}%");
            });
        }

        // Get total records count
        $totalRecords = LeaveRequest::count();
        $filteredRecords = $query->count();

        // Apply pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $leaveRequests = $query->skip($start)->take($length)->get();

        // Format data for DataTables
        $data = $leaveRequests->map(function ($request, $index) use ($start) {
            $statusBadge = '';
            switch ($request->status) {
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

            return [
                'DT_RowIndex' => $start + $index + 1,
                'employee' => $request->employee->fullname ?? 'N/A',
                'leave_type' => '<span class="badge badge-info">' . $leaveTypeName . '</span>' . $documentIcon,
                'start_date' => $request->start_date->format('d/m/Y'),
                'end_date' => $request->end_date->format('d/m/Y'),
                'total_days' => $request->total_days . ' days',
                'status' => $statusBadge,
                'requested_at' => $request->requested_at ? $request->requested_at->format('d/m/Y H:i') : 'N/A',
                'action' => $actions
            ];
        });

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $data
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $leaveTypes = LeaveType::where('is_active', true)->get();

        // Get all active projects for project selection
        $projects = Project::where('project_status', 1)->get();

        // Get all active departments for department selection
        $departments = Department::where('department_status', 1)->get();

        // Get all employees for standard flow (fallback)
        $employees = Employee::with('administrations')->get();

        return view('leave-requests.create', compact('leaveTypes', 'projects', 'departments', 'employees'))
            ->with('title', 'Create Leave Request');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
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
            'project_id' => 'nullable|exists:projects,id'
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

        $request->validate($validationRules);

        // Get total days from request (either calculated or manually entered)
        $totalDays = $request->total_days;

        // Validate total days against remaining days
        $entitlement = LeaveEntitlement::where('employee_id', $request->employee_id)
            ->where('leave_type_id', $request->leave_type_id)
            ->where('period_start', '<=', $request->start_date)
            ->where('period_end', '>=', $request->end_date)
            ->first();

        if ($entitlement && $totalDays > $entitlement->remaining_days) {
            return back()->with([
                'total_days' => "Total days ({$totalDays}) exceeds remaining leave balance ({$entitlement->remaining_days} days)."
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

            if (!$administration) {
                return back()->with(['employee_id' => 'Employee has no active administration record.']);
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

            // Set reason to null if leave type is not unpaid
            $reason = $request->reason;
            if ($leaveType && $leaveType->category !== 'unpaid') {
                $reason = null;
            }

            // Create leave request
            $leaveRequest = LeaveRequest::create([
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
                'supporting_document' => $supportingDocumentPath
            ]);

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

            // Create approval plan
            $response = app(ApprovalPlanController::class)->create_approval_plan('leave_request', $leaveRequest->id);
            if (!$response) {
                DB::rollback();
                return back()->with(['toast_error' => 'Failed to create approval plans. Please try again.'])->withInput();
            }

            // Create roster adjustment if needed (for roster flow)
            if ($flowType === 'roster') {
                $this->createRosterAdjustment($leaveRequest);
            }

            // Update leave entitlements immediately for testing purposes
            // $this->updateLeaveEntitlements($leaveRequest);

            DB::commit();

            $successMessage = 'Leave request submitted successfully.';
            if ($flowType === 'non_roster') {
                $successMessage = 'Non-roster leave request submitted successfully.';
            } elseif ($flowType === 'roster') {
                $successMessage = 'Roster leave request submitted successfully.';
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
     */
    public function show(LeaveRequest $leaveRequest)
    {
        $leaveRequest->load([
            'employee.administrations.project',
            'employee.administrations.position',
            'leaveType',
            'leaveCalculations',
            'approvalPlans',
            'cancellations.requestedBy',
            'cancellations.confirmedBy'
        ]);

        return view('leave-requests.show', compact('leaveRequest'))->with('title', 'Leave Request Details');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LeaveRequest $leaveRequest)
    {
        // if (!$leaveRequest->canBeCancelled()) {
        //     return back()->with(['toast_error' => 'This leave request cannot be edited.']);
        // }

        // Load projects for project selection
        $projects = Project::where('project_status', 1)->get();

        // Get all active departments for department selection
        $departments = Department::where('department_status', 1)->get();

        return view('leave-requests.edit', compact('leaveRequest', 'projects', 'departments'))->with('title', 'Edit Leave Request');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LeaveRequest $leaveRequest)
    {
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

        $request->validate($validationRules);

        // Validate total days against remaining days
        $employeeId = $request->employee_id;
        $leaveTypeId = $request->leave_type_id;
        $totalDays = $request->total_days;

        $leaveEntitlement = LeaveEntitlement::where('employee_id', $employeeId)
            ->where('leave_type_id', $leaveTypeId)
            ->first();

        if ($leaveEntitlement && $totalDays > $leaveEntitlement->remaining_days) {
            return back()->with([
                'total_days' => "Total days ({$totalDays}) exceeds remaining leave balance ({$leaveEntitlement->remaining_days} days)."
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

            // Set reason to null if leave type is not unpaid
            $reason = $request->reason;
            if ($leaveType && $leaveType->category !== 'unpaid') {
                $reason = null;
            }

            $leaveRequest->update([
                'employee_id' => $request->employee_id,
                'leave_type_id' => $request->leave_type_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'back_to_work_date' => $request->back_to_work_date,
                'reason' => $reason,
                'total_days' => $totalDays,
                'leave_period' => $request->leave_period,
                'supporting_document' => $supportingDocumentPath,
            ]);

            // Update auto conversion date based on leave type and document status
            $leaveRequest->updateAutoConversionDate($request->leave_type_id, (bool)$supportingDocumentPath);

            DB::commit();

            return redirect()->route('leave.requests.show', $leaveRequest)
                ->with('toast_success', 'Leave request updated successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->with(['toast_error' => 'Failed to update leave request: ' . $e->getMessage()])->withInput();
        }
    }

    /**
     * Download supporting document
     */
    public function download(LeaveRequest $leaveRequest)
    {
        if (!$leaveRequest->supporting_document) {
            abort(404, 'Document not found');
        }

        if (!Storage::disk('private')->exists($leaveRequest->supporting_document)) {
            abort(404, 'File not found');
        }

        return response()->streamDownload(function () use ($leaveRequest) {
            echo Storage::disk('private')->get($leaveRequest->supporting_document);
        }, basename($leaveRequest->supporting_document));
    }

    /**
     * Delete supporting document
     */
    public function deleteDocument(LeaveRequest $leaveRequest)
    {
        if (!$leaveRequest->supporting_document) {
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
     */
    public function upload(Request $request, LeaveRequest $leaveRequest)
    {
        // Check if leave request can have documents uploaded
        if (in_array($leaveRequest->status, ['closed', 'cancelled'])) {
            return back()->with(['toast_error' => 'Cannot upload document for closed or cancelled leave requests.']);
        }

        $request->validate([
            'supporting_document' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120' // 5MB max
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

    public function destroy(LeaveRequest $leaveRequest)
    {
        // if (!$leaveRequest->canBeCancelled()) {
        //     return back()->with(['toast_error' => 'This leave request cannot be cancelled.']);
        // }

        // Delete supporting document and folder
        $this->deleteSupportingDocument($leaveRequest);

        $leaveRequest->cancel();

        return redirect()->route('leave-requests.index')
            ->with('toast_success', 'Leave request cancelled successfully.');
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
    public function getEmployeesByProject($projectId)
    {
        $project = Project::findOrFail($projectId);

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
                    'nik' => $admin->nik ?? 'N/A'
                ];
            });

        return response()->json(['employees' => $employees]);
    }

    /**
     * Get leave types by employee for AJAX
     */
    public function getLeaveTypesByEmployee($employeeId)
    {
        $entitlements = LeaveEntitlement::with('leaveType')
            ->where('employee_id', $employeeId)
            ->where('remaining_days', '>', 0)
            ->get()
            ->map(function ($entitlement) {
                return [
                    'leave_type_id' => $entitlement->leave_type_id,
                    'leave_type' => [
                        'name' => $entitlement->leaveType->name,
                        'code' => $entitlement->leaveType->code
                    ],
                    'remaining_days' => $entitlement->remaining_days,
                    'entitled_days' => $entitlement->entitled_days,
                    'taken_days' => $entitlement->taken_days
                ];
            });

        return response()->json(['leaveTypes' => $entitlements]);
    }

    /**
     * Get project information including leave_type for AJAX
     */
    public function getProjectInfo($projectId)
    {
        $project = Project::findOrFail($projectId);

        return response()->json([
            'project' => [
                'id' => $project->id,
                'name' => $project->project_name,
                'code' => $project->project_code,
                'leave_type' => $project->leave_type
            ]
        ]);
    }

    public function getLeavePeriod($employeeId, $leaveTypeId)
    {
        $leaveEntitlement = LeaveEntitlement::where('employee_id', $employeeId)
            ->where('leave_type_id', $leaveTypeId)
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
                'remaining_days' => $leaveEntitlement->remaining_days
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'No leave entitlement found for this employee and leave type'
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
    public function getEmployeeLeaveBalance($employeeId)
    {
        try {
            $employee = Employee::findOrFail($employeeId);
            $leaveEntitlements = LeaveEntitlement::where('employee_id', $employeeId)
                ->with('leaveType')
                ->get();

            $balanceData = $leaveEntitlements->map(function ($entitlement) {
                return [
                    'leave_type' => $entitlement->leaveType->name,
                    'leave_type_code' => $entitlement->leaveType->code,
                    'entitled_days' => $entitlement->entitled_days,
                    'remaining_days' => $entitlement->remaining_days,
                    'used_days' => $entitlement->used_days,
                    'period_start' => $entitlement->period_start,
                    'period_end' => $entitlement->period_end
                ];
            });

            // Get employee administration data for approval flow
            $administration = $employee->administrations->where('is_active', 1)->first();
            $projectId = $administration->project_id ?? null;
            $departmentId = $administration->position->department_id ?? null;
            $levelId = $administration->level_id ?? null;

            return response()->json([
                'success' => true,
                'employee' => [
                    'id' => $employee->id,
                    'name' => $employee->fullname,
                    'nik' => $administration->nik ?? 'N/A',
                    'project_id' => $projectId,
                    'department_id' => $departmentId,
                    'level_id' => $levelId
                ],
                'leave_balance' => $balanceData
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load leave balance: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get leave type information for AJAX calls
     */
    public function getLeaveTypeInfo($leaveTypeId)
    {
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
                    'is_active' => $leaveType->is_active
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load leave type info: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update leave entitlements for approved leave request
     * Using same logic as ApprovalPlanController for consistency
     */
    private function updateLeaveEntitlements($leaveRequest)
    {
        try {
            Log::info("Updating leave entitlements for leave request (immediate calculation)", [
                'leave_request_id' => $leaveRequest->id,
                'employee_id' => $leaveRequest->employee_id,
                'leave_type_id' => $leaveRequest->leave_type_id,
                'total_days' => $leaveRequest->total_days
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

                // Recalculate remaining days
                $entitlement->remaining_days = $entitlement->withdrawable_days - $entitlement->taken_days;

                $entitlement->save();

                Log::info("Successfully updated leave entitlements (immediate calculation)", [
                    'leave_request_id' => $leaveRequest->id,
                    'employee_id' => $leaveRequest->employee_id,
                    'leave_type_id' => $leaveRequest->leave_type_id,
                    'total_days_requested' => $leaveRequest->total_days,
                    'old_taken_days' => $oldTakenDays,
                    'new_taken_days' => $entitlement->taken_days,
                    'remaining_days' => $entitlement->remaining_days,
                    'withdrawable_days' => $entitlement->withdrawable_days
                ]);
            } else {
                Log::warning("No entitlement found for leave request (immediate calculation)", [
                    'leave_request_id' => $leaveRequest->id,
                    'employee_id' => $leaveRequest->employee_id,
                    'leave_type_id' => $leaveRequest->leave_type_id,
                    'start_date' => $leaveRequest->start_date,
                    'end_date' => $leaveRequest->end_date
                ]);
            }
        } catch (\Exception $e) {
            Log::error("Error updating leave entitlements (immediate calculation): " . $e->getMessage(), [
                'leave_request_id' => $leaveRequest->id,
                'employee_id' => $leaveRequest->employee_id,
                'leave_type_id' => $leaveRequest->leave_type_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }
    }

    /**
     * Close leave request
     */
    public function close(LeaveRequest $leaveRequest)
    {
        if (!$leaveRequest->canBeClosed()) {
            return back()->with(['toast_error' => 'This leave request cannot be closed.']);
        }

        try {
            // Get the current user's ID
            $userId = Auth::id();
            if (!$userId) {
                return back()->with(['toast_error' => 'User not authenticated.']);
            }

            $leaveRequest->close($userId);

            return back()->with(['toast_success' => 'Leave request closed successfully.']);
        } catch (\Exception $e) {
            return back()->with(['toast_error' => 'Failed to close leave request: ' . $e->getMessage()]);
        }
    }

    /**
     * Show cancellation request form
     */
    public function showCancellationForm(LeaveRequest $leaveRequest)
    {
        if (!$leaveRequest->canBeCancelled()) {
            return back()->with(['toast_error' => 'This leave request cannot be cancelled.']);
        }

        return view('leave-requests.cancellation-form', compact('leaveRequest'))
            ->with('title', 'Request Leave Cancellation');
    }

    /**
     * Store cancellation request
     */
    public function storeCancellation(Request $request, LeaveRequest $leaveRequest)
    {
        $request->validate([
            'days_to_cancel' => 'required|integer|min:1|max:' . $leaveRequest->total_days,
            'reason' => 'required|string|max:1000'
        ]);

        try {
            $leaveRequest->requestCancellation(
                $request->days_to_cancel,
                $request->reason,
                Auth::id()
            );

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
    public function approveCancellation(LeaveRequestCancellation $cancellation)
    {
        if (!$cancellation->isPending()) {
            return back()->with(['toast_error' => 'This cancellation request has already been processed.']);
        }

        try {
            $cancellation->approve(Auth::id());

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
        if (!$cancellation->isPending()) {
            return back()->with(['toast_error' => 'This cancellation request has already been processed.']);
        }

        $request->validate([
            'confirmation_notes' => 'required|string|max:1000'
        ]);

        try {
            $cancellation->reject(Auth::id(), $request->confirmation_notes);

            return back()->with(['toast_success' => 'Cancellation request rejected successfully.']);
        } catch (\Exception $e) {
            return back()->with(['toast_error' => 'Failed to reject cancellation request: ' . $e->getMessage()]);
        }
    }
}
