<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\LeaveEntitlement;
use App\Models\Employee;
use App\Models\Administration;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LeaveRequestController extends Controller
{
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
            ->select('leave_requests.*');

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
                default:
                    $statusBadge = '<span class="badge badge-secondary">' . ucfirst($request->status) . '</span>';
            }

            $actions = '<div class="btn-group" role="group">';
            $actions .= '<a href="' . route('leave.requests.show', $request) . '" class="btn btn-info btn-sm mr-1"><i class="fas fa-eye"></i></a>';

            if ($request->canBeCancelled()) {
                $actions .= '<a href="' . route('leave.requests.edit', $request) . '" class="btn btn-warning btn-sm mr-1"><i class="fas fa-edit"></i></a>';
            }

            if ($request->isPending()) {
                $actions .= '<form method="POST" action="' . route('leave.requests.approve', $request) . '" style="display: inline;" onsubmit="return confirm(\'Approve this leave request?\')">';
                $actions .= csrf_field();
                $actions .= '<button type="submit" class="btn btn-success btn-sm mr-1"><i class="fas fa-check"></i></button>';
                $actions .= '</form>';

                $actions .= '<form method="POST" action="' . route('leave.requests.reject', $request) . '" style="display: inline;" onsubmit="return confirm(\'Reject this leave request?\')">';
                $actions .= csrf_field();
                $actions .= '<button type="submit" class="btn btn-danger btn-sm mr-1"><i class="fas fa-times"></i></button>';
                $actions .= '</form>';
            }

            $actions .= '</div>';

            return [
                'DT_RowIndex' => $start + $index + 1,
                'employee' => $request->employee->fullname ?? 'N/A',
                'leave_type' => '<span class="badge badge-info">' . ($request->leaveType->name ?? 'N/A') . '</span>',
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
    public function create()
    {
        $leaveTypes = LeaveType::where('is_active', true)->get();
        $employees = Employee::with('administrations')->get();

        return view('leave-requests.create', compact('leaveTypes', 'employees'))->with('title', 'Create Leave Request');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
            'back_to_work_date' => 'nullable|date|after:end_date'
        ]);

        DB::beginTransaction();
        try {
            // Get employee's current administration
            $administration = Administration::where('employee_id', $request->employee_id)
                ->where('is_active', true)
                ->first();

            if (!$administration) {
                return back()->withErrors(['employee_id' => 'Employee has no active administration record.']);
            }

            // Calculate total days
            $startDate = \Carbon\Carbon::parse($request->start_date);
            $endDate = \Carbon\Carbon::parse($request->end_date);
            $totalDays = $startDate->diffInDays($endDate) + 1;

            // Check leave entitlement
            $leaveType = LeaveType::find($request->leave_type_id);
            $entitlement = LeaveEntitlement::where('employee_id', $request->employee_id)
                ->where('leave_type_id', $request->leave_type_id)
                ->where('period_start', '<=', $request->start_date)
                ->where('period_end', '>=', $request->end_date)
                ->first();

            if ($entitlement && !$entitlement->canTakeLeave($totalDays)) {
                return back()->withErrors(['total_days' => 'Insufficient leave balance. Available: ' . $entitlement->remaining_days . ' days']);
            }

            // Create leave request
            $leaveRequest = LeaveRequest::create([
                'employee_id' => $request->employee_id,
                'administration_id' => $administration->id,
                'leave_type_id' => $request->leave_type_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'back_to_work_date' => $request->back_to_work_date,
                'reason' => $request->reason,
                'total_days' => $totalDays,
                'status' => 'pending',
                'leave_period' => $request->leave_period,
                'requested_at' => now()
            ]);

            // Create approval plan
            $this->createApprovalPlan($leaveRequest);

            DB::commit();

            return redirect()->route('leave-requests.show', $leaveRequest)
                ->with('success', 'Leave request submitted successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to create leave request: ' . $e->getMessage()]);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(LeaveRequest $leaveRequest)
    {
        $leaveRequest->load(['employee', 'leaveType', 'administration', 'leaveCalculations', 'approvalPlans']);

        return view('leave-requests.show', compact('leaveRequest'))->with('title', 'Leave Request Details');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(LeaveRequest $leaveRequest)
    {
        if (!$leaveRequest->canBeCancelled()) {
            return back()->withErrors(['error' => 'This leave request cannot be edited.']);
        }

        $leaveTypes = LeaveType::where('is_active', true)->get();
        $employees = Employee::with('administrations')->get();

        return view('leave-requests.edit', compact('leaveRequest', 'leaveTypes', 'employees'))->with('title', 'Edit Leave Request');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, LeaveRequest $leaveRequest)
    {
        if (!$leaveRequest->canBeCancelled()) {
            return back()->withErrors(['error' => 'This leave request cannot be updated.']);
        }

        $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string|max:1000',
            'back_to_work_date' => 'nullable|date|after:end_date'
        ]);

        DB::beginTransaction();
        try {
            // Calculate total days
            $startDate = \Carbon\Carbon::parse($request->start_date);
            $endDate = \Carbon\Carbon::parse($request->end_date);
            $totalDays = $startDate->diffInDays($endDate) + 1;

            $leaveRequest->update([
                'leave_type_id' => $request->leave_type_id,
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'back_to_work_date' => $request->back_to_work_date,
                'reason' => $request->reason,
                'total_days' => $totalDays,
                'leave_period' => $request->leave_period
            ]);

            DB::commit();

            return redirect()->route('leave-requests.show', $leaveRequest)
                ->with('success', 'Leave request updated successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to update leave request: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(LeaveRequest $leaveRequest)
    {
        if (!$leaveRequest->canBeCancelled()) {
            return back()->withErrors(['error' => 'This leave request cannot be cancelled.']);
        }

        $leaveRequest->cancel();

        return redirect()->route('leave-requests.index')
            ->with('success', 'Leave request cancelled successfully.');
    }

    /**
     * Approve leave request
     */
    public function approve(LeaveRequest $leaveRequest)
    {
        if (!$leaveRequest->isPending()) {
            return back()->withErrors(['error' => 'Only pending leave requests can be approved.']);
        }

        DB::beginTransaction();
        try {
            $leaveRequest->approve();

            // Create roster adjustment if needed
            $this->createRosterAdjustment($leaveRequest);

            DB::commit();

            return back()->with('success', 'Leave request approved successfully.');
        } catch (\Exception $e) {
            DB::rollback();
            return back()->withErrors(['error' => 'Failed to approve leave request: ' . $e->getMessage()]);
        }
    }

    /**
     * Reject leave request
     */
    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        if (!$leaveRequest->isPending()) {
            return back()->withErrors(['error' => 'Only pending leave requests can be rejected.']);
        }

        $request->validate([
            'rejection_reason' => 'required|string|max:500'
        ]);

        $leaveRequest->reject();

        return back()->with('success', 'Leave request rejected successfully.');
    }

    /**
     * Create approval plan for leave request
     */
    private function createApprovalPlan(LeaveRequest $leaveRequest)
    {
        // This will be implemented in Phase 5: Approval Integration
        // For now, we'll create a basic approval plan
        \App\Models\ApprovalPlan::create([
            'document_id' => $leaveRequest->id,
            'document_type' => 'leave_request',
            'approver_id' => Auth::id(),
            'status' => 0,
            'is_open' => true,
            'is_read' => false,
            'approval_order' => 1
        ]);
    }

    /**
     * Create roster adjustment if employee is on roster-based project
     */
    private function createRosterAdjustment(LeaveRequest $leaveRequest)
    {
        $administration = $leaveRequest->administration;
        $roster = \App\Models\Roster::where('employee_id', $leaveRequest->employee_id)
            ->where('is_active', true)
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
}
