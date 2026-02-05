<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Support\Str;
use App\Models\ApprovalPlan;
use App\Models\LeaveRequest;
use Illuminate\Http\Request;
use App\Models\FlightRequest;
use App\Models\Administration;
use App\Models\Officialtravel;
use App\Models\BusinessPartner;
use Illuminate\Support\Facades\DB;
use App\Models\FlightRequestDetail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use App\Models\FlightRequestIssuance;
use App\Http\Controllers\ApprovalPlanController;

class FlightRequestController extends Controller
{
    public function __construct()
    {
        // Admin/HCS permissions
        $this->middleware('permission:flight-requests.show')->only('index', 'data', 'show');
        $this->middleware('permission:flight-requests.create')->only('create', 'store');
        $this->middleware('permission:flight-requests.edit')->only('edit', 'update');
        $this->middleware('permission:flight-requests.delete')->only('destroy', 'cancel');

        // Personal permissions
        $this->middleware('permission:personal.flight.view-own')->only('myRequests', 'myRequestsData', 'myRequestsShow');
        $this->middleware('permission:personal.flight.create-own')->only('myRequestsCreate', 'myRequestsStore');
        $this->middleware('permission:personal.flight.edit-own')->only('myRequestsEdit', 'myRequestsUpdate');
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('flight-requests.index')->with('title', 'Flight Requests');
    }

    /**
     * Get data for DataTables (server-side)
     */
    public function data(Request $request)
    {
        $query = FlightRequest::with(['employee', 'administration', 'requestedBy', 'leaveRequest', 'officialTravel'])
            ->select('flight_requests.*')
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $status = $request->status;
            if (is_array($status)) {
                $query->whereIn('status', $status);
            } else {
                $query->where('status', $status);
            }
        } elseif ($request->has('for_issuance')) {
            // Default: only show approved and issued for issuance selection
            $query->whereIn('status', [FlightRequest::STATUS_APPROVED, FlightRequest::STATUS_ISSUED]);
        }

        if ($request->filled('request_type')) {
            $query->where('request_type', $request->request_type);
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('form_number')) {
            $query->where('form_number', 'like', "%{$request->form_number}%");
        }

        if ($request->filled('date_from')) {
            $query->where('requested_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->where('requested_at', '<=', $request->date_to);
        }

        // Get total records count
        $totalRecords = FlightRequest::count();
        $filteredRecords = $query->count();

        // Apply pagination
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $flightRequests = $query->skip($start)->take($length)->get();

        // Format data for DataTables
        $data = $flightRequests->map(function ($request, $index) use ($start) {
            $statusBadge = $this->getStatusBadge($request->status);
            $requestTypeBadge = $this->getRequestTypeBadge($request->request_type);

            $employeeName = $request->employee_name ?? ($request->employee->fullname ?? 'N/A');
            $employeeNik = $request->nik ?? ($request->employee->nik ?? 'N/A');

            $actions = '<div class="btn-group">';
            $actions .= '<a href="' . route('flight-requests.show', $request->id) . '" class="btn btn-sm btn-info mr-1" title="View"><i class="fas fa-eye"></i></a>';

            if (in_array($request->status, [FlightRequest::STATUS_DRAFT, FlightRequest::STATUS_SUBMITTED])) {
                $actions .= '<a href="' . route('flight-requests.edit', $request->id) . '" class="btn btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i></a>';
            }

            $actions .= '</div>';

            return [
                'id' => $request->id,
                'DT_RowIndex' => $start + $index + 1,
                'form_number' => $request->form_number ?? '-',
                'employee_name' => $employeeName,
                'nik' => $employeeNik,
                'request_type' => $requestTypeBadge,
                'purpose_of_travel' => Str::limit($request->purpose_of_travel, 50),
                'status' => $statusBadge,
                'requested_at' => $request->requested_at ? $request->requested_at->format('d/m/Y H:i') : '-',
                'actions' => $actions,
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
     */
    public function create(Request $request)
    {
        $title = 'Create Flight Request';
        $businessPartners = BusinessPartner::active()->get();

        return view('flight-requests.create', compact('businessPartners', 'title'));
    }

    /**
     * Get available leave requests for flight booking
     */
    public function getLeaveRequests(Request $request)
    {
        $leaveRequests = LeaveRequest::with([
            'employee',
            'administration.position.department',
            'administration.project'
        ])
            ->whereIn('status', ['pending', 'approved'])
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(function ($leave) {
                $employee = $leave->employee;
                $admin = $leave->administration;

                return [
                    'id' => $leave->id,
                    'text' => sprintf(
                        '%s - %s (%s to %s)',
                        $employee ? $employee->fullname : 'N/A',
                        $admin ? $admin->nik : 'N/A',
                        $leave->start_date->format('d M Y'),
                        $leave->end_date->format('d M Y')
                    ),
                    'employee_data' => [
                        'employee_id' => $employee ? $employee->id : null,
                        'administration_id' => $admin ? $admin->id : null,
                        'employee_name' => $employee ? $employee->fullname : '',
                        'nik' => $admin ? $admin->nik : '',
                        'position' => ($admin && $admin->position) ? $admin->position->position_name : '',
                        'department' => ($admin && $admin->position && $admin->position->department) ? $admin->position->department->department_name : '',
                        'poh' => $admin ? ($admin->poh ?? '') : '',
                        'doh' => ($admin && $admin->doh) ? $admin->doh->format('d F Y') : '',
                        'project' => ($admin && $admin->project) ? $admin->project->project_name : '',
                        'phone_number' => $employee ? ($employee->phone ?? '') : '',
                        'purpose_of_travel' => $leave->reason ?? '',
                        'total_travel_days' => $leave->total_days ?? ''
                    ]
                ];
            });

        return response()->json($leaveRequests);
    }

    /**
     * Get available official travels for flight booking
     */
    public function getOfficialTravels(Request $request)
    {
        $officialTravels = Officialtravel::with([
            'traveler.employee',
            'traveler.position.department',
            'traveler.project',
            'details.follower.employee',
            'details.follower.position'
        ])
            ->whereIn('status', ['submitted', 'approved'])
            ->orderBy('departure_from', 'desc')
            ->get()
            ->map(function ($travel) {
                $traveler = $travel->traveler;
                $employee = $traveler ? $traveler->employee : null;

                // Get followers
                $followers = $travel->details->map(function ($detail) {
                    $follower = $detail->follower;
                    $followerEmployee = $follower ? $follower->employee : null;
                    return [
                        'name' => $followerEmployee ? $followerEmployee->fullname : 'N/A',
                        'nik' => $follower ? $follower->nik : 'N/A',
                        'position' => ($follower && $follower->position) ? $follower->position->position_name : 'N/A'
                    ];
                })->toArray();

                return [
                    'id' => $travel->id,
                    'text' => sprintf(
                        '%s - %s (%s)',
                        $travel->official_travel_number ?? 'N/A',
                        $employee ? $employee->fullname : 'N/A',
                        $travel->destination
                    ),
                    'employee_data' => [
                        'employee_id' => $employee ? $employee->id : null,
                        'administration_id' => $traveler ? $traveler->id : null,
                        'employee_name' => $employee ? $employee->fullname : '',
                        'nik' => $traveler ? $traveler->nik : '',
                        'position' => ($traveler && $traveler->position) ? $traveler->position->position_name : '',
                        'department' => ($traveler && $traveler->position && $traveler->position->department) ? $traveler->position->department->department_name : '',
                        'poh' => $traveler ? ($traveler->poh ?? '') : '',
                        'doh' => ($traveler && $traveler->doh) ? $traveler->doh->format('d F Y') : '',
                        'project' => ($traveler && $traveler->project) ? $traveler->project->project_name : '',
                        'phone_number' => $employee ? ($employee->phone ?? '') : '',
                        'purpose_of_travel' => $travel->purpose ?? '',
                        'total_travel_days' => $travel->duration ?? ''
                    ],
                    'followers' => $followers
                ];
            });

        return response()->json($officialTravels);
    }

    /**
     * Get employee list for standalone requests
     */
    public function getEmployees(Request $request)
    {
        $administrations = Administration::with([
            'employee',
            'position.department',
            'project'
        ])
            ->where('is_active', 1)
            ->whereHas('employee') // Ensure employee exists
            ->get()
            ->map(function ($admin) {
                $employee = $admin->employee;
                $position = $admin->position;
                $department = $position && $position->department ? $position->department->department_name : '';

                return [
                    'id' => $employee ? $employee->id : null,
                    'employee_name' => $employee ? $employee->fullname : 'N/A',
                    'text' => sprintf(
                        '%s (%s)',
                        $employee ? $employee->fullname : 'N/A',
                        $admin->nik ?? 'N/A'
                    ),
                    'employee_data' => [
                        'employee_id' => $employee ? $employee->id : null,
                        'administration_id' => $admin->id,
                        'employee_name' => $employee ? $employee->fullname : '',
                        'nik' => $admin->nik ?? '',
                        'position' => $position ? ($position->position_name ?? '') : '',
                        'department' => $department,
                        'poh' => $admin->poh ?? '',
                        'doh' => $admin->doh ? $admin->doh->format('d F Y') : '',
                        'project' => $admin->project ? ($admin->project->project_name ?? '') : '',
                        'phone_number' => $employee ? ($employee->phone ?? '') : ''
                    ]
                ];
            })
            ->filter(function ($item) {
                // Filter out items where employee is null (shouldn't happen, but safety check)
                return $item['id'] !== null;
            })
            ->sortBy('employee_name') // Sort by employee name
            ->values(); // Re-index array after filter and sort

        return response()->json($administrations);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'request_type' => 'required|in:standalone,leave_based,travel_based',
            'employee_id' => 'nullable|exists:employees,id',
            'administration_id' => 'nullable|exists:administrations,id',
            'employee_name' => 'nullable|string|max:255',
            'nik' => 'nullable|string|max:50',
            'position' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'project' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'purpose_of_travel' => 'required|string',
            'total_travel_days' => 'nullable|string|max:50',
            'leave_request_id' => 'nullable|exists:leave_requests,id',
            'official_travel_id' => 'nullable|exists:officialtravels,id',
            'manual_approvers' => 'required_if:submit_action,submit|array|min:1',
            'manual_approvers.*' => 'exists:users,id',
            'notes' => 'nullable|string',
            'submit_action' => 'required|in:draft,submit',
            'details' => 'required|array|min:1',
            'details.*.segment_type' => 'required|in:departure,return',
            'details.*.flight_date' => 'required|date',
            'details.*.departure_city' => 'required|string|max:100',
            'details.*.arrival_city' => 'required|string|max:100',
            'details.*.airline' => 'nullable|string|max:100',
            'details.*.flight_time' => 'nullable|date_format:H:i',
        ]);

        DB::beginTransaction();
        try {
            // Generate form number
            $formNumber = $this->generateFormNumber();

            // Determine status based on submit action
            $status = $request->submit_action === 'submit' ? FlightRequest::STATUS_SUBMITTED : FlightRequest::STATUS_DRAFT;
            $requestedAt = $request->submit_action === 'submit' ? now() : null;

            // Ensure manual_approvers is an array and preserve order
            $manualApprovers = $request->manual_approvers ?? [];
            if (!is_array($manualApprovers)) {
                $manualApprovers = [];
            }
            // Ensure array values are preserved in order (array_values to reset keys)
            $manualApprovers = array_values(array_filter($manualApprovers));

            // Create flight request
            $flightRequest = FlightRequest::create([
                'form_number' => $formNumber,
                'request_type' => $validated['request_type'],
                'employee_id' => $validated['employee_id'] ?? null,
                'administration_id' => $validated['administration_id'] ?? null,
                'employee_name' => $validated['employee_name'] ?? null,
                'nik' => $validated['nik'] ?? null,
                'position' => $validated['position'] ?? null,
                'department' => $validated['department'] ?? null,
                'project' => $validated['project'] ?? null,
                'phone_number' => $validated['phone_number'] ?? null,
                'purpose_of_travel' => $validated['purpose_of_travel'],
                'total_travel_days' => $validated['total_travel_days'] ?? null,
                'leave_request_id' => $validated['leave_request_id'] ?? null,
                'official_travel_id' => $validated['official_travel_id'] ?? null,
                'status' => $status,
                'manual_approvers' => !empty($manualApprovers) ? $manualApprovers : null,
                'requested_by' => Auth::id(),
                'requested_at' => $requestedAt,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Create flight request details
            foreach ($validated['details'] as $index => $detail) {
                FlightRequestDetail::create([
                    'flight_request_id' => $flightRequest->id,
                    'segment_order' => $index + 1,
                    'segment_type' => $detail['segment_type'],
                    'flight_date' => $detail['flight_date'],
                    'departure_city' => $detail['departure_city'],
                    'arrival_city' => $detail['arrival_city'],
                    'airline' => $detail['airline'] ?? null,
                    'flight_time' => $detail['flight_time'] ?? null,
                ]);
            }

            // If submitted, create approval plans using manual approvers (same pattern as official travel)
            if ($request->submit_action === 'submit') {
                if (empty($manualApprovers)) {
                    DB::rollBack();
                    return back()->withInput()
                        ->with('toast_error', 'Please select at least one approver before submitting.');
                }

                $response = app(ApprovalPlanController::class)->create_manual_approval_plan('flight_request', $flightRequest->id);

                if (!$response || $response === 0) {
                    DB::rollBack();
                    return back()->withInput()
                        ->with('toast_error', 'Failed to create approval plans. Please ensure at least one approver is selected.');
                }
            }

            DB::commit();

            $message = 'Flight Request created successfully!';
            if ($request->submit_action === 'submit') {
                $message .= ' Status: Submitted for approval.';
            } else {
                $message .= ' Status: Saved as draft.';
            }

            return redirect()->route('flight-requests.index')
                ->with('toast_success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Flight Request creation failed: ' . $e->getMessage());

            return back()->withInput()
                ->with('toast_error', 'Failed to create Flight Request: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $title = 'Flight Request Details';
        $flightRequest = FlightRequest::with([
            'employee',
            'administration',
            'leaveRequest.employee',
            'leaveRequest.administration',
            'officialTravel.traveler.employee',
            'details',
            'issuances.issuanceDetails',
            'issuances.businessPartner',
            'issuances.issuedBy',
            'requestedBy',
            'cancelledBy',
            'approvalPlans.approver'
        ])->findOrFail($id);

        return view('flight-requests.show', compact('flightRequest', 'title'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $title = 'Edit Flight Request';
        $flightRequest = FlightRequest::with(['details', 'employee', 'administration'])->findOrFail($id);

        if (!in_array($flightRequest->status, [FlightRequest::STATUS_DRAFT, FlightRequest::STATUS_SUBMITTED])) {
            return redirect()->route('flight-requests.show', $id)
                ->with('toast_error', 'Cannot edit Flight Request with current status.');
        }

        $employees = Employee::with(['activeAdministration'])->get();
        $businessPartners = BusinessPartner::active()->get();

        return view('flight-requests.edit', compact('flightRequest', 'employees', 'businessPartners', 'title'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $flightRequest = FlightRequest::findOrFail($id);

        if (!in_array($flightRequest->status, [FlightRequest::STATUS_DRAFT, FlightRequest::STATUS_SUBMITTED])) {
            return back()->with('toast_error', 'Cannot update Flight Request with current status.');
        }

        $validated = $request->validate([
            'request_type' => 'required|in:standalone,leave_based,travel_based',
            'employee_id' => 'nullable|exists:employees,id',
            'administration_id' => 'nullable|exists:administrations,id',
            'employee_name' => 'nullable|string|max:255',
            'nik' => 'nullable|string|max:50',
            'position' => 'nullable|string|max:255',
            'department' => 'nullable|string|max:255',
            'project' => 'nullable|string|max:255',
            'phone_number' => 'nullable|string|max:20',
            'purpose_of_travel' => 'required|string',
            'total_travel_days' => 'nullable|string|max:50',
            'leave_request_id' => 'required_if:request_type,leave_based|nullable|exists:leave_requests,id',
            'official_travel_id' => 'required_if:request_type,travel_based|nullable|exists:officialtravels,id',
            'manual_approvers' => 'nullable|array',
            'manual_approvers.*' => 'exists:users,id',
            'notes' => 'nullable|string',
            'details' => 'required|array|min:1',
            'details.*.segment_type' => 'required|in:departure,return',
            'details.*.flight_date' => 'required|date',
            'details.*.departure_city' => 'required|string|max:100',
            'details.*.arrival_city' => 'required|string|max:100',
            'details.*.airline' => 'nullable|string|max:100',
            'details.*.flight_time' => 'nullable|date_format:H:i',
        ], [
            'request_type.required' => 'Request type is required.',
            'request_type.in' => 'Invalid request type selected.',
            'leave_request_id.required_if' => 'Please select a leave request when request type is Leave Request.',
            'leave_request_id.exists' => 'Selected leave request does not exist.',
            'official_travel_id.required_if' => 'Please select an official travel when request type is Official Travel.',
            'official_travel_id.exists' => 'Selected official travel does not exist.',
        ]);

        DB::beginTransaction();
        try {
            // Ensure only one source document is set based on request type
            $leaveRequestId = null;
            $officialTravelId = null;

            if ($validated['request_type'] === 'leave_based') {
                $leaveRequestId = $validated['leave_request_id'] ?? null;
                $officialTravelId = null;
            } elseif ($validated['request_type'] === 'travel_based') {
                $leaveRequestId = null;
                $officialTravelId = $validated['official_travel_id'] ?? null;
            } else {
                // standalone
                $leaveRequestId = null;
                $officialTravelId = null;
            }

            // Update flight request
            $flightRequest->update([
                'request_type' => $validated['request_type'],
                'employee_id' => $validated['employee_id'] ?? null,
                'administration_id' => $validated['administration_id'] ?? null,
                'employee_name' => $validated['employee_name'] ?? null,
                'nik' => $validated['nik'] ?? null,
                'position' => $validated['position'] ?? null,
                'department' => $validated['department'] ?? null,
                'project' => $validated['project'] ?? null,
                'phone_number' => $validated['phone_number'] ?? null,
                'purpose_of_travel' => $validated['purpose_of_travel'],
                'total_travel_days' => $validated['total_travel_days'] ?? null,
                'leave_request_id' => $leaveRequestId,
                'official_travel_id' => $officialTravelId,
                'manual_approvers' => $validated['manual_approvers'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Delete existing details
            $flightRequest->details()->delete();

            // Create new details
            foreach ($validated['details'] as $index => $detail) {
                FlightRequestDetail::create([
                    'flight_request_id' => $flightRequest->id,
                    'segment_order' => $index + 1,
                    'segment_type' => $detail['segment_type'],
                    'flight_date' => $detail['flight_date'],
                    'departure_city' => $detail['departure_city'],
                    'arrival_city' => $detail['arrival_city'],
                    'airline' => $detail['airline'] ?? null,
                    'flight_time' => $detail['flight_time'] ?? null,
                ]);
            }

            DB::commit();

            return redirect()->route('flight-requests.show', $flightRequest->id)
                ->with('toast_success', 'Flight Request updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Flight Request update failed: ' . $e->getMessage());

            return back()->withInput()
                ->with('toast_error', 'Failed to update Flight Request: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $flightRequest = FlightRequest::findOrFail($id);

        if (!in_array($flightRequest->status, [FlightRequest::STATUS_DRAFT])) {
            return back()->with('toast_error', 'Only draft Flight Requests can be deleted.');
        }

        DB::beginTransaction();
        try {
            $flightRequest->delete();
            DB::commit();

            return redirect()->route('flight-requests.index')
                ->with('toast_success', 'Flight Request deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Flight Request deletion failed: ' . $e->getMessage());

            return back()->with('toast_error', 'Failed to delete Flight Request.');
        }
    }

    /**
     * Submit flight request for approval
     */
    public function submit(Request $request, $id)
    {
        $flightRequest = FlightRequest::findOrFail($id);

        if ($flightRequest->status !== FlightRequest::STATUS_DRAFT) {
            return back()->with('toast_error', 'Only draft Flight Requests can be submitted.');
        }

        DB::beginTransaction();
        try {
            $flightRequest->update([
                'status' => FlightRequest::STATUS_SUBMITTED,
                'requested_at' => now(),
            ]);

            // Check if manual approvers are set
            if (empty($flightRequest->manual_approvers)) {
                DB::rollBack();
                return back()->with('toast_error', 'Please select at least one approver before submitting.');
            }

            // Create approval plans using manual approvers (same pattern as official travel)
            $response = app(\App\Http\Controllers\ApprovalPlanController::class)->create_manual_approval_plan('flight_request', $flightRequest->id);

            if (!$response || $response === 0) {
                DB::rollBack();
                return back()->with('toast_error', 'Failed to create approval plans. Please ensure at least one approver is selected.');
            }

            DB::commit();

            return back()->with('toast_success', 'Flight Request submitted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Flight Request submission failed: ' . $e->getMessage());

            return back()->with('toast_error', 'Failed to submit Flight Request.');
        }
    }

    /**
     * Cancel flight request
     */
    public function cancel(Request $request, $id)
    {
        $flightRequest = FlightRequest::findOrFail($id);

        if (!$flightRequest->canBeCancelled()) {
            return back()->with('toast_error', 'Flight Request cannot be cancelled in current status.');
        }

        $validated = $request->validate([
            'cancellation_reason' => 'required|string',
        ]);

        DB::beginTransaction();
        try {
            $flightRequest->update([
                'status' => FlightRequest::STATUS_CANCELLED,
                'cancellation_reason' => $validated['cancellation_reason'],
                'cancelled_by' => Auth::id(),
                'cancelled_at' => now(),
            ]);

            // Delete related approval plans
            $deletedCount = ApprovalPlan::where('document_id', $flightRequest->id)
                ->where('document_type', 'flight_request')
                ->delete();

            if ($deletedCount > 0) {
                Log::info("Deleted {$deletedCount} approval plan(s) for flight_request {$flightRequest->id} due to cancellation");
            }

            DB::commit();

            return back()->with('toast_success', 'Flight Request cancelled successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Flight Request cancellation failed: ' . $e->getMessage());

            return back()->with('toast_error', 'Failed to cancel Flight Request.');
        }
    }

    /**
     * Mark flight request as completed
     */
    public function complete(Request $request, $id)
    {
        $flightRequest = FlightRequest::findOrFail($id);

        if ($flightRequest->status !== FlightRequest::STATUS_ISSUED) {
            return back()->with('toast_error', 'Only issued Flight Requests can be marked as completed.');
        }

        try {
            $flightRequest->update([
                'status' => FlightRequest::STATUS_COMPLETED,
                'completed_at' => now(),
            ]);

            return back()->with('toast_success', 'Flight Request marked as completed.');
        } catch (\Exception $e) {
            Log::error('Flight Request complete failed: ' . $e->getMessage());

            return back()->with('toast_error', 'Failed to complete Flight Request.');
        }
    }

    /**
     * My Requests - Personal view
     */
    public function myRequests()
    {
        return view('flight-requests.my-requests')->with('title', 'My Flight Requests');
    }

    /**
     * My Requests Data - DataTables
     */
    public function myRequestsData(Request $request)
    {
        $user = Auth::user();

        $query = FlightRequest::with(['details', 'requestedBy'])
            ->where('requested_by', $user->id)
            ->select('flight_requests.*')
            ->orderBy('created_at', 'desc');

        // Apply filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return datatables()->of($query)
            ->addIndexColumn()
            ->addColumn('form_number', function ($row) {
                return $row->form_number ?? '-';
            })
            ->addColumn('request_type', function ($row) {
                return $this->getRequestTypeBadge($row->request_type);
            })
            ->addColumn('purpose', function ($row) {
                return Str::limit($row->purpose_of_travel, 50);
            })
            ->addColumn('status_badge', function ($row) {
                return $this->getStatusBadge($row->status);
            })
            ->addColumn('requested_at', function ($row) {
                return $row->requested_at ? $row->requested_at->format('d/m/Y H:i') : '-';
            })
            ->addColumn('actions', function ($row) {
                $actions = '<a href="' . route('flight-requests.my-requests.show', $row->id) . '" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>';
                if (in_array($row->status, [FlightRequest::STATUS_DRAFT, FlightRequest::STATUS_SUBMITTED])) {
                    $actions .= ' <a href="' . route('flight-requests.my-requests.edit', $row->id) . '" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>';
                }
                return $actions;
            })
            ->rawColumns(['request_type', 'status_badge', 'actions'])
            ->make(true);
    }

    /**
     * My Requests Show
     */
    public function myRequestsShow($id)
    {
        $title = 'My Flight Request Details';
        $user = Auth::user();
        $flightRequest = FlightRequest::with([
            'details',
            'issuances.issuanceDetails',
            'issuances.businessPartner',
            'requestedBy'
        ])->where('requested_by', $user->id)->findOrFail($id);

        return view('flight-requests.my-show', compact('flightRequest', 'title'));
    }

    /**
     * My Requests Create
     */
    public function myRequestsCreate()
    {
        $title = 'Create My Flight Request';
        $user = Auth::user();
        $employee = $user->employee;
        $administration = $employee->activeAdministration ?? null;

        return view('flight-requests.my-create', compact('employee', 'administration', 'title'));
    }

    /**
     * My Requests Store
     */
    public function myRequestsStore(Request $request)
    {
        // Similar to store() but auto-set employee_id and administration_id from logged user
        $user = Auth::user();
        $employee = $user->employee;

        if (!$employee) {
            return back()->with('toast_error', 'Employee not found.');
        }

        $request->merge([
            'employee_id' => $employee->id,
            'administration_id' => $employee->activeAdministration->id ?? null,
        ]);

        return $this->store($request);
    }

    /**
     * My Requests Edit
     */
    public function myRequestsEdit($id)
    {
        $title = 'Edit My Flight Request';
        $user = Auth::user();
        $flightRequest = FlightRequest::with(['details'])
            ->where('requested_by', $user->id)
            ->findOrFail($id);

        if (!in_array($flightRequest->status, [FlightRequest::STATUS_DRAFT, FlightRequest::STATUS_SUBMITTED])) {
            return redirect()->route('flight-requests.my-requests.show', $id)
                ->with('toast_error', 'Cannot edit Flight Request with current status.');
        }

        return view('flight-requests.my-edit', compact('flightRequest', 'title'));
    }

    /**
     * My Requests Update
     */
    public function myRequestsUpdate(Request $request, $id)
    {
        $user = Auth::user();
        $flightRequest = FlightRequest::where('requested_by', $user->id)->findOrFail($id);

        return $this->update($request, $flightRequest->id);
    }

    // Helper Methods

    private function getStatusBadge($status)
    {
        $badges = [
            FlightRequest::STATUS_DRAFT => '<span class="badge badge-secondary">Draft</span>',
            FlightRequest::STATUS_SUBMITTED => '<span class="badge badge-info">Submitted</span>',
            FlightRequest::STATUS_APPROVED => '<span class="badge badge-success">Approved</span>',
            FlightRequest::STATUS_ISSUED => '<span class="badge badge-primary">Issued</span>',
            FlightRequest::STATUS_COMPLETED => '<span class="badge badge-dark">Completed</span>',
            FlightRequest::STATUS_REJECTED => '<span class="badge badge-danger">Rejected</span>',
            FlightRequest::STATUS_CANCELLED => '<span class="badge badge-warning">Cancelled</span>',
        ];

        return $badges[$status] ?? '<span class="badge badge-secondary">' . ucfirst($status) . '</span>';
    }

    private function getRequestTypeBadge($type)
    {
        $badges = [
            FlightRequest::TYPE_STANDALONE => '<span class="badge badge-primary">Standalone</span>',
            FlightRequest::TYPE_LEAVE_BASED => '<span class="badge badge-info">Leave Based</span>',
            FlightRequest::TYPE_TRAVEL_BASED => '<span class="badge badge-success">Travel Based</span>',
        ];

        return $badges[$type] ?? '<span class="badge badge-secondary">' . ucfirst($type) . '</span>';
    }

    private function generateFormNumber()
    {
        $year = date('y');
        $lastRequest = FlightRequest::where('form_number', 'like', "{$year}FRF-%")
            ->orderBy('form_number', 'desc')
            ->first();

        if ($lastRequest && preg_match('/\d+$/', $lastRequest->form_number, $matches)) {
            $nextNumber = intval($matches[0]) + 1;
        } else {
            $nextNumber = 1;
        }

        return sprintf('%sFRF-%05d', $year, $nextNumber);
    }

    /**
     * Print flight request
     */
    public function print($id)
    {
        $title = 'Flight Requests';
        $subtitle = 'Flight Request Details';
        $flightRequest = FlightRequest::with([
            'employee',
            'administration.position.department',
            'administration.project',
            'details',
            'leaveRequest.employee',
            'leaveRequest.administration',
            'officialTravel.traveler.employee',
            'requestedBy',
            'approvalPlans.approver'
        ])->findOrFail($id);

        return view('flight-requests.print', compact('title', 'subtitle', 'flightRequest'));
    }
}
