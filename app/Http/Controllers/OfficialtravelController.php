<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Project;
use App\Models\ApprovalPlan;
use App\Models\LetterNumber;
use Illuminate\Http\Request;
use App\Models\Accommodation;
use App\Models\ApprovalStage;
use App\Models\Administration;
use App\Models\Officialtravel;
use App\Models\Transportation;
use App\Models\OfficialtravelStop;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\Officialtravel_detail;
use Maatwebsite\Excel\Concerns\WithMapping;
use Spatie\Activitylog\Facades\LogActivity;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\FromCollection;

class OfficialtravelController extends Controller
{
    /**
     * Constructor to add permissions
     */
    public function __construct()
    {
        $this->middleware('permission:official-travels.show')->only(['index', 'show']);
        $this->middleware('permission:official-travels.create')->only('create');
        $this->middleware('permission:official-travels.edit')->only('edit');
        $this->middleware('permission:official-travels.delete')->only('destroy');

        $this->middleware('permission:official-travels.stamp')->only(['showArrivalForm', 'showDepartureForm']);
    }

    /**
     * Authenticated user instance
     */
    protected $user;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = 'Official Travels';
        $subtitle = 'List of Official Travels';
        $projects = Project::where('project_status', 1)->orderBy('project_code', 'asc')->get();

        return view('officialtravels.index', compact('title', 'subtitle', 'projects'));
    }

    /**
     * Get all official travels for DataTables
     */
    public function getOfficialtravels(Request $request)
    {
        $officialtravels = Officialtravel::with([
            'traveler.employee',
            'project',
            'transportation',
            'accommodation',
            'details.follower.employee',
            'creator'
        ]);

        // Filter by date range
        if (!empty($request->get('date1')) && !empty($request->get('date2'))) {
            $officialtravels->whereBetween('official_travel_date', [
                $request->get('date1'),
                $request->get('date2')
            ]);
        }

        // Filter by travel number
        if (!empty($request->get('travel_number'))) {
            $officialtravels->where('official_travel_number', 'LIKE', '%' . $request->get('travel_number') . '%');
        }

        // Filter by destination
        if (!empty($request->get('destination'))) {
            $officialtravels->where('destination', 'LIKE', '%' . $request->get('destination') . '%');
        }

        // Filter by NIK
        if (!empty($request->get('nik'))) {
            $officialtravels->whereHas('traveler', function ($query) use ($request) {
                $query->where('nik', 'LIKE', '%' . $request->get('nik') . '%');
            });
        }

        // Filter by Traveler Name
        if (!empty($request->get('fullname'))) {
            $officialtravels->whereHas('traveler.employee', function ($query) use ($request) {
                $query->where('fullname', 'LIKE', '%' . $request->get('fullname') . '%');
            });
        }

        // Filter by project
        if (!empty($request->get('project'))) {
            $officialtravels->where('official_travel_origin', $request->get('project'));
        }

        // Filter by status
        if (!empty($request->get('status'))) {
            $officialtravels->where('status', $request->get('status'));
        }

        // Global search
        if (!empty($request->get('search'))) {
            $search = $request->get('search');
            $officialtravels->where(function ($query) use ($search) {
                $query->where('official_travel_number', 'LIKE', "%$search%")
                    ->orWhere('destination', 'LIKE', "%$search%")
                    ->orWhereHas('traveler.employee', function ($q) use ($search) {
                        $q->where('fullname', 'LIKE', "%$search%")
                            ->orWhere('nik', 'LIKE', "%$search%");
                    })
                    ->orWhereHas('project', function ($q) use ($search) {
                        $q->where('project_code', 'LIKE', "%$search%")
                            ->orWhere('project_name', 'LIKE', "%$search%");
                    });
            });
        }

        $officialtravels->orderBy('created_at', 'desc');

        return datatables()->of($officialtravels)
            ->addIndexColumn()
            ->addColumn('official_travel_number', function ($officialtravel) {
                return $officialtravel->official_travel_number;
            })
            ->addColumn('official_travel_date', function ($officialtravel) {
                return date('d/m/Y', strtotime($officialtravel->official_travel_date));
            })
            ->addColumn('traveler', function ($officialtravel) {
                $traveler = $officialtravel->traveler;
                if ($traveler && $traveler->employee) {
                    return $traveler->nik . ' - ' . $traveler->employee->fullname;
                }
                return '-';
            })
            ->addColumn('project', function ($officialtravel) {
                return $officialtravel->project ? $officialtravel->project->project_code : '-';
            })
            ->addColumn('destination', function ($officialtravel) {
                return $officialtravel->destination;
            })
            ->addColumn('status', function ($officialtravel) {
                switch ($officialtravel->status) {
                    case 'draft':
                        return '<span class="badge badge-warning">Draft</span>';
                    case 'submitted':
                        return '<span class="badge badge-info">Submitted</span>';
                    case 'approved':
                        return '<span class="badge badge-success">Approved</span>';
                    case 'rejected':
                        return '<span class="badge badge-danger">Rejected</span>';
                    case 'canceled':
                        return '<span class="badge badge-dark">Canceled</span>';
                    case 'closed':
                        return '<span class="badge badge-secondary">Closed</span>';
                    default:
                        return '<span class="badge badge-light">' . ucfirst($officialtravel->status) . '</span>';
                }
            })
            ->addColumn('created_by', function ($officialtravel) {
                $creator = '<small>' . $officialtravel->creator->name . '</small>';
                return $creator;
            })
            ->addColumn('action', function ($model) {
                return view('officialtravels.action', compact('model'))->render();
            })
            ->rawColumns(['action', 'status', 'created_by'])
            ->toJson();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {

        $title = 'Official Travels';
        $subtitle = 'Add Official Travel (LOT)';

        $user = Auth::user()->load(['projects', 'departments']);
        $projects = Project::whereIn('id', $user->projects->pluck('id'))->where('project_status', 1)->get();
        $accommodations = Accommodation::where('accommodation_status', 1)->get();
        $transportations = Transportation::where('transportation_status', 1)->get();

        // Travel Number will be generated based on selected letter number
        $romanMonth = $this->numberToRoman(now()->month);
        $travelNumber = sprintf("ARKA/[Letter Number]/HR/%s/%s", $romanMonth, now()->year);

        // Load employees with their relationships
        $employees = Administration::with([
            'employee',
            'position.department',
            'project'
        ])
            ->where('is_active', 1)
            ->orderBy('nik', 'asc')->get()->map(function ($employee) {
                return [
                    'id' => $employee->id,
                    'nik' => $employee->nik,
                    'fullname' => $employee->employee->fullname ?? 'Unknown',
                    'position' => $employee->position->position_name ?? '-',
                    'project' => $employee->project->project_name ?? '-',
                    'department' => $employee->position->department->department_name ?? '-',
                    'position_id' => $employee->position_id,
                    'project_id' => $employee->project_id,
                    'department_id' => $employee->position->department_id
                ];
            });


        return view('officialtravels.create', compact(
            'title',
            'subtitle',
            'projects',
            'accommodations',
            'transportations',
            'employees',
            'travelNumber',
            'romanMonth'
        ));
    }

    /**
     * Get approver selector component with different display modes
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getApproverSelector(Request $request)
    {
        $displayMode = $request->get('display_mode', 'modern_selector');
        $selectedApprovers = old('manual_approvers', []);

        return response()->view('components.manual-approver-selector', [
            'selectedApprovers' => $selectedApprovers,
            'required' => true,
            'multiple' => true,
            'placeholder' => 'Pilih approver untuk menyetujui perjalanan dinas ini',
            'helpText' => 'Pilih minimal 1 approver dengan role approver',
            'displayMode' => $displayMode
        ])->header('Content-Type', 'text/html');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        try {
            $this->validate($request, [
                'official_travel_number' => 'nullable',
                'official_travel_date' => 'required|date',
                'official_travel_origin' => 'required|exists:projects,id',
                'traveler_id' => 'required|exists:administrations,id',
                'purpose' => 'required|string',
                'destination' => 'required|string|min:3',
                'duration' => 'required|string|min:1',
                'departure_from' => 'required|date|after_or_equal:today',
                'transportation_id' => 'required|exists:transportations,id',
                'accommodation_id' => 'required|exists:accommodations,id',
                'followers' => 'nullable|array',
                'followers.*' => 'exists:administrations,id',

                // Letter numbering integration fields
                'number_option' => 'nullable|in:existing',
                'letter_number_id' => 'nullable|exists:letter_numbers,id',
                // Manual approvers
                'manual_approvers' => 'required_if:submit_action,submit|array|min:1',
                'manual_approvers.*' => 'exists:users,id',
                'approval_orders' => 'nullable|array',
                'approval_orders.*' => 'integer|min:1',
                // Submit action
                'submit_action' => 'required|in:draft,submit',
            ], [
                'official_travel_date.required' => 'LOT Date is required.',
                'official_travel_date.date' => 'LOT Date must be a valid date.',
                'official_travel_origin.required' => 'LOT Origin is required.',
                'official_travel_origin.exists' => 'Selected LOT Origin is invalid.',
                'traveler_id.required' => 'Main Traveler is required.',
                'traveler_id.exists' => 'Selected Main Traveler is invalid.',
                'purpose.required' => 'Purpose is required.',
                'destination.required' => 'Destination is required.',
                'destination.min' => 'Destination must be at least 3 characters.',
                'duration.required' => 'Duration is required.',
                'duration.min' => 'Duration cannot be empty.',
                'departure_from.required' => 'Departure Date is required.',
                'departure_from.date' => 'Departure Date must be a valid date.',
                'departure_from.after_or_equal' => 'Departure Date cannot be in the past.',
                'transportation_id.required' => 'Transportation is required.',
                'transportation_id.exists' => 'Selected Transportation is invalid.',
                'accommodation_id.required' => 'Accommodation is required.',
                'accommodation_id.exists' => 'Selected Accommodation is invalid.',
                'followers.*.exists' => 'One or more selected followers are invalid.',
                'letter_number_id.exists' => 'Selected Letter Number is invalid.',
                'manual_approvers.required_if' => 'Please select at least one approver.',
                'manual_approvers.array' => 'Approvers must be an array.',
                'manual_approvers.min' => 'Please select at least one approver.',
                'manual_approvers.*.exists' => 'One or more selected approvers are invalid.',
                'approval_orders.array' => 'Approval orders must be an array.',
                'approval_orders.*.integer' => 'Approval order must be a number.',
                'approval_orders.*.min' => 'Approval order must be at least 1.',
                'submit_action.required' => 'Please select an action.',
                'submit_action.in' => 'Invalid submit action.',
            ]);

            DB::beginTransaction();

            // Handle letter number integration and generate LOT number
            $letterNumberId = null;
            $letterNumberString = null;
            $letterNumberRecord = null;
            $romanMonth = $this->numberToRoman(now()->month);

            if ($request->letter_number_id) {
                // Use existing letter number
                $letterNumberRecord = LetterNumber::find($request->letter_number_id);
                if ($letterNumberRecord && $letterNumberRecord->status === 'reserved') {
                    $letterNumberId = $letterNumberRecord->id;
                    $letterNumberString = $letterNumberRecord->letter_number;

                    // Generate LOT number using selected letter number
                    $travelNumber = sprintf("ARKA/%s/HR/%s/%s", $letterNumberString, $romanMonth, now()->year);
                } else {
                    throw new \Exception('Selected letter number is not available or not reserved. Current status: ' . ($letterNumberRecord ? $letterNumberRecord->status : 'not found'));
                }
            } else {
                // Generate LOT number with auto sequence if no letter number selected
                $lastTravel = Officialtravel::whereYear('created_at', now()->year)
                    ->orderBy('created_at', 'desc')
                    ->first();

                $sequence = $lastTravel ? (int)substr($lastTravel->official_travel_number, 6, 4) + 1 : 1;
                $travelNumber = sprintf("ARKA/B%04d/HR/%s/%s", $sequence, $romanMonth, now()->year);
            }

            // Check if generated travel number already exists
            $exists = Officialtravel::where('official_travel_number', $travelNumber)->exists();
            if ($exists) {
                throw new \Exception('Generated LOT number already exists: ' . $travelNumber . '. Please try again or select a different letter number.');
            }

            // Determine status based on submit action
            $status = $request->submit_action === 'submit' ? 'submitted' : 'draft';
            $submitAt = $request->submit_action === 'submit' ? now() : null;

            // Ensure manual_approvers is an array and preserve order
            $manualApprovers = $request->manual_approvers ?? [];
            if (!is_array($manualApprovers)) {
                $manualApprovers = [];
            }
            // Ensure array values are preserved in order (array_values to reset keys)
            $manualApprovers = array_values(array_filter($manualApprovers));

            // Create new official travel
            $officialtravel = new Officialtravel([
                'letter_number_id' => $letterNumberId,
                'letter_number' => $letterNumberString,
                'official_travel_number' => $travelNumber,
                'official_travel_date' => $request->official_travel_date,
                'official_travel_origin' => $request->official_travel_origin,
                'status' => $status,
                'traveler_id' => $request->traveler_id,
                'purpose' => $request->purpose,
                'destination' => $request->destination,
                'duration' => $request->duration,
                'departure_from' => $request->departure_from,
                'transportation_id' => $request->transportation_id,
                'accommodation_id' => $request->accommodation_id,
                'manual_approvers' => $manualApprovers,
                'created_by' => auth()->id(),
                'submit_at' => $submitAt,
            ]);
            $officialtravel->save();

            // Mark letter number as used jika ada
            if ($letterNumberRecord) {
                $letterNumberRecord->markAsUsed('officialtravel', $officialtravel->id);

                // Log the letter number usage for debugging
                Log::info('Letter Number marked as used', [
                    'letter_number_id' => $letterNumberRecord->id,
                    'letter_number' => $letterNumberRecord->letter_number,
                    'officialtravel_id' => $officialtravel->id,
                    'official_travel_number' => $officialtravel->official_travel_number
                ]);
            }

            // Add followers if any
            if ($request->has('followers') && is_array($request->followers)) {
                foreach ($request->followers as $followerId) {
                    Officialtravel_detail::create([
                        'official_travel_id' => $officialtravel->id,
                        'follower_id' => $followerId
                    ]);
                }
            }

            // If submitted, create approval plans using manual approvers
            if ($request->submit_action === 'submit' && !empty($manualApprovers)) {
                $response = app(ApprovalPlanController::class)->create_manual_approval_plan('officialtravel', $officialtravel->id);
                if (!$response || $response === 0) {
                    DB::rollback();
                    return redirect()->back()
                        ->with('toast_error', 'Failed to create approval plans. Please ensure at least one approver is selected.')
                        ->withInput();
                }
            } elseif ($request->submit_action === 'submit' && empty($manualApprovers)) {
                DB::rollback();
                return redirect()->back()
                    ->with('toast_error', 'Please select at least one approver before submitting.')
                    ->withInput();
            }

            DB::commit();

            $message = 'Official Travel created successfully!';
            if ($letterNumberString) {
                $message .= ' Letter Number: ' . $letterNumberString . ' (Status changed to Used)';
            }
            $message .= ' LOT Number: ' . $travelNumber;

            if ($request->submit_action === 'submit') {
                $message .= ' Status: Submitted for approval.';
            } else {
                $message .= ' Status: Saved as draft.';
            }

            return redirect('officialtravels')->with('toast_success', $message);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('toast_error', 'Failed to create Official Travel. ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $title = 'Official Travels';
        $subtitle = 'Official Travel Details';
        $officialtravel = Officialtravel::with([
            'traveler.employee',
            'traveler.position.department',
            'project',
            'transportation',
            'accommodation',
            'details.follower.employee',
            'stops.arrivalChecker',
            'stops.departureChecker',
            'latestStop'
        ])->findOrFail($id);

        return view('officialtravels.show', compact('title', 'subtitle', 'officialtravel'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Officialtravel  $officialtravel
     * @return \Illuminate\Http\Response
     */
    public function edit(Officialtravel $officialtravel)
    {
        $title = 'Official Travels';
        $subtitle = 'Edit Official Travel';

        $user = Auth::user()->load(['projects', 'departments']);
        $projects = Project::whereIn('id', $user->projects->pluck('id'))->where('project_status', 1)->get();
        $accommodations = Accommodation::where('accommodation_status', 1)->get();
        $transportations = Transportation::where('transportation_status', 1)->get();

        // Load relationships with null safety
        $officialtravel->load([
            'details.follower.position.department',
            'details.follower.project',
            'traveler.position.department',
            'traveler.project',
            'project'
        ]);

        // Load employees with their relationships
        $employees = Administration::with([
            'employee',
            'position.department',
            'project'
        ])->get()->map(function ($employee) {
            $position = $employee->position;
            $department = $position ? ($position->department ?? null) : null;

            return [
                'id' => $employee->id,
                'nik' => $employee->nik,
                'fullname' => $employee->employee->fullname ?? 'Unknown',
                'position' => $position ? ($position->position_name ?? '-') : '-',
                'project' => $employee->project->project_name ?? '-',
                'department' => $department ? ($department->department_name ?? '-') : '-',
                'position_id' => $employee->position_id,
                'project_id' => $employee->project_id,
                'department_id' => $position ? ($position->department_id ?? ($department ? $department->id : null)) : null
            ];
        });

        // Get selected followers
        $selectedFollowers = $officialtravel->details->pluck('follower_id')->toArray();

        return view('officialtravels.edit', compact(
            'title',
            'subtitle',
            'officialtravel',
            'projects',
            'accommodations',
            'transportations',
            'employees',
            'selectedFollowers',
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Officialtravel  $officialtravel
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Officialtravel $officialtravel)
    {
        try {
            $this->validate($request, [
                'official_travel_number' => 'required|unique:officialtravels,official_travel_number,' . $officialtravel->id,
                'official_travel_date' => 'required|date',
                'official_travel_origin' => 'required',
                'traveler_id' => 'required',
                'purpose' => 'required',
                'destination' => 'required',
                'duration' => 'required',
                'departure_from' => 'required|date',
                'transportation_id' => 'required',
                'accommodation_id' => 'required',
                'followers' => 'nullable|array',
                // Manual approvers
                'manual_approvers' => 'nullable|array|min:1',
                'manual_approvers.*' => 'exists:users,id',
            ], [
                'manual_approvers.array' => 'Approvers must be an array.',
                'manual_approvers.min' => 'Please select at least one approver.',
                'manual_approvers.*.exists' => 'One or more selected approvers are invalid.',
            ]);

            DB::beginTransaction();

            // Only allow editing if the status is draft
            if ($officialtravel->status != 'draft') {
                throw new \Exception('Cannot edit Official Travel that is not in draft status.');
            }

            // Ensure manual_approvers is an array and preserve order
            $manualApprovers = $request->manual_approvers ?? [];
            if (!is_array($manualApprovers)) {
                $manualApprovers = [];
            }
            // Ensure array values are preserved in order (array_values to reset keys)
            $manualApprovers = array_values(array_filter($manualApprovers));

            // Check if manual_approvers changed
            $approversChanged = json_encode($officialtravel->manual_approvers ?? []) !== json_encode($manualApprovers);

            // Update the official travel
            // Note: arrival_at_destination, arrival_remark, departure_from_destination, departure_remark
            // have been moved to officialtravel_stops table, so they are not updated here
            $officialtravel->update([
                'official_travel_number' => $request->official_travel_number,
                'official_travel_date' => $request->official_travel_date,
                'official_travel_origin' => $request->official_travel_origin,
                'traveler_id' => $request->traveler_id,
                'purpose' => $request->purpose,
                'destination' => $request->destination,
                'duration' => $request->duration,
                'departure_from' => $request->departure_from,
                'transportation_id' => $request->transportation_id,
                'accommodation_id' => $request->accommodation_id,
                'manual_approvers' => $manualApprovers,
            ]);

            // If approvers changed and there are existing approval plans, delete them
            // (They will be recreated when document is submitted)
            if ($approversChanged) {
                ApprovalPlan::where('document_id', $officialtravel->id)
                    ->where('document_type', 'officialtravel')
                    ->delete();
                Log::info("Deleted existing approval plans for officialtravel {$officialtravel->id} due to approver changes");
            }

            // Update followers - remove all existing and add new ones
            $officialtravel->details()->delete();

            if ($request->has('followers') && is_array($request->followers)) {
                foreach ($request->followers as $followerId) {
                    Officialtravel_detail::create([
                        'official_travel_id' => $officialtravel->id,
                        'follower_id' => $followerId
                    ]);
                }
            }

            DB::commit();

            return redirect('officialtravels/' . $officialtravel->id)->with('toast_success', 'Official Travel updated successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('toast_error', 'Failed to update Official Travel. ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Officialtravel  $officialtravel
     * @return \Illuminate\Http\Response
     */
    public function destroy(Officialtravel $officialtravel)
    {
        try {
            DB::beginTransaction();

            // Can only delete if in draft status
            if ($officialtravel->status != 'draft') {
                throw new \Exception('Cannot delete Official Travel that is not in draft status.');
            }

            // Delete details first
            $officialtravel->details()->delete();

            // Then delete the official travel
            $officialtravel->delete();

            DB::commit();

            return redirect('officialtravels')->with('toast_success', 'Official Travel deleted successfully!');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('toast_error', 'Failed to delete Official Travel. ' . $e->getMessage());
        }
    }

    /**
     * Submit official travel for approval using new approval system
     */
    public function submitForApproval($id)
    {
        try {
            $officialtravel = Officialtravel::findOrFail($id);

            // Check if already submitted
            if ($officialtravel->status === 'submitted') {
                return redirect()->back()->with('toast_error', 'Official travel has already been submitted for approval.');
            }

            // Check if status is draft
            if ($officialtravel->status !== 'draft') {
                return redirect()->back()->with('toast_error', 'Only draft official travels can be submitted for approval.');
            }

            // Check if manual approvers are set
            if (empty($officialtravel->manual_approvers)) {
                return redirect()->back()
                    ->with('toast_error', 'Please select at least one approver before submitting for approval.');
            }

            DB::beginTransaction();

            // Update status to submitted
            $officialtravel->update([
                'status' => 'submitted',
                'submit_at' => now(),
            ]);

            // Create approval plans using manual approvers
            $response = app(ApprovalPlanController::class)->create_manual_approval_plan('officialtravel', $officialtravel->id);

            if (!$response || $response === 0) {
                DB::rollback();
                return redirect()->back()
                    ->with('toast_error', 'Failed to create approval plans. Please ensure at least one approver is selected.');
            }

            DB::commit();

            return redirect()->route('officialtravels.show', $officialtravel->id)
                ->with('toast_success', 'Official travel has been submitted for approval. ' . $response . ' approver(s) will review your request.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->back()
                ->with('toast_error', 'Official travel not found.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error submitting official travel for approval: ' . $e->getMessage(), [
                'officialtravel_id' => $id,
                'exception' => $e
            ]);
            return redirect()->back()
                ->with('toast_error', 'Failed to submit official travel for approval: ' . $e->getMessage());
        }
    }

    /**
     * Show arrival stamp form
     */
    public function showArrivalForm($id)
    {
        $officialtravel = Officialtravel::with([
            'traveler.employee',
            'project',
            'transportation',
            'accommodation',
            'details.follower.employee',
            'latestStop'
        ])->findOrFail($id);

        // Cek apakah bisa record arrival
        if (!$officialtravel->canRecordArrival()) {
            return redirect()->back()->with('toast_error', 'Cannot record arrival at this time. Please check the current status.');
        }

        $title = 'Official Travels';
        $subtitle = 'Record Arrival';

        return view('officialtravels.arrival', compact('title', 'subtitle', 'officialtravel'));
    }

    /**
     * Process arrival stamp
     */
    public function arrivalStamp(Request $request, Officialtravel $officialtravel)
    {
        try {
            // Cek apakah bisa record arrival
            if (!$officialtravel->canRecordArrival()) {
                return redirect()->back()->with('toast_error', 'Cannot record arrival at this time. Please check the current status.');
            }

            $this->validate($request, [
                'arrival_at_destination' => 'required|date',
                'arrival_remark' => 'required|string',
            ]);

            DB::beginTransaction();

            // Get or create latest stop
            $latestStop = $officialtravel->latestStop;
            if (!$latestStop || $latestStop->isComplete()) {
                // Create new stop
                $latestStop = OfficialtravelStop::create([
                    'official_travel_id' => $officialtravel->id,
                    'arrival_at_destination' => $request->arrival_at_destination,
                    'arrival_check_by' => Auth::id(),
                    'arrival_remark' => $request->arrival_remark,
                    'arrival_timestamps' => now(),
                ]);
            } else {
                // Update existing stop
                $latestStop->update([
                    'arrival_at_destination' => $request->arrival_at_destination,
                    'arrival_check_by' => Auth::id(),
                    'arrival_remark' => $request->arrival_remark,
                    'arrival_timestamps' => now(),
                ]);
            }

            DB::commit();

            return redirect('officialtravels/' . $officialtravel->id)->with('toast_success', 'Arrival recorded successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('toast_error', 'Failed to record arrival. ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show departure stamp form
     */
    public function showDepartureForm($id)
    {
        $officialtravel = Officialtravel::with([
            'traveler.employee',
            'project',
            'transportation',
            'accommodation',
            'details.follower.employee',
            'latestStop'
        ])->findOrFail($id);

        // Cek apakah bisa record departure
        if (!$officialtravel->canRecordDeparture()) {
            return redirect()->back()->with('toast_error', 'Cannot record departure at this time. Please check the current status.');
        }

        $title = 'Official Travels';
        $subtitle = 'Record Departure';

        return view('officialtravels.departure', compact('title', 'subtitle', 'officialtravel'));
    }

    /**
     * Process departure stamp
     */
    public function departureStamp(Request $request, $id)
    {
        try {
            $officialtravel = Officialtravel::with([
                'traveler.employee',
                'project',
                'transportation',
                'accommodation',
                'details.follower.employee',
                'latestStop'
            ])->findOrFail($id);

            // Cek apakah bisa record departure
            if (!$officialtravel->canRecordDeparture()) {
                return redirect()->back()->with('toast_error', 'Cannot record departure at this time. Please check the current status.');
            }

            $request->validate([
                'departure_from_destination' => 'required|date',
                'departure_remark' => 'required|string'
            ]);

            DB::beginTransaction();

            // Update latest stop with departure info
            $latestStop = $officialtravel->latestStop;
            $latestStop->update([
                'departure_from_destination' => $request->departure_from_destination,
                'departure_check_by' => auth()->id(),
                'departure_remark' => $request->departure_remark,
                'departure_timestamps' => now()
            ]);

            DB::commit();

            return redirect()
                ->route('officialtravels.show', $officialtravel->id)
                ->with('toast_success', 'Departure recorded successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('toast_error', 'Failed to record departure. ' . $e->getMessage())
                ->withInput();
        }
    }

    public function print($id)
    {
        $title = 'Official Travels';
        $subtitle = 'Official Travel Details';
        $officialtravel = Officialtravel::with([
            'traveler.employee',
            'project',
            'transportation',
            'accommodation',
            'details.follower.employee',
            'stops.arrivalChecker',
            'stops.departureChecker'
        ])->findOrFail($id);

        return view('officialtravels.print', compact('title', 'subtitle', 'officialtravel'));
    }


    /**
     * Close the official travel
     */
    public function close(Officialtravel $officialtravel)
    {
        try {
            // Cek apakah bisa close
            if (!$officialtravel->canClose()) {
                return redirect()->back()->with('toast_error', 'Cannot close official travel. Please ensure at least one complete stop (arrival + departure) is recorded.');
            }

            DB::beginTransaction();

            $officialtravel->update([
                'status' => 'closed'
            ]);

            DB::commit();

            return redirect()->route('officialtravels.show', $officialtravel->id)
                ->with('toast_success', 'Official travel has been closed successfully');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('toast_error', 'Failed to close official travel. ' . $e->getMessage());
        }
    }

    /**
     * Convert number to Roman Numeral
     */
    private function numberToRoman($number)
    {
        $map = [
            'M' => 1000,
            'CM' => 900,
            'D' => 500,
            'CD' => 400,
            'C' => 100,
            'XC' => 90,
            'L' => 50,
            'XL' => 40,
            'X' => 10,
            'IX' => 9,
            'V' => 5,
            'IV' => 4,
            'I' => 1
        ];

        $result = '';
        foreach ($map as $roman => $value) {
            while ($number >= $value) {
                $result .= $roman;
                $number -= $value;
            }
        }
        return $result;
    }

    /**
     * Export official travels to Excel
     */
    public function exportExcel(Request $request)
    {
        try {
            $query = Officialtravel::with([
                'traveler.employee',
                'project',
                'transportation',
                'accommodation',
                'details.follower.employee',
                'stops.arrivalChecker',
                'stops.departureChecker',
                'approval_plans.approver',
                'creator'
            ]);

            // Apply the same filters as getOfficialtravels
            if (!empty($request->get('date1')) && !empty($request->get('date2'))) {
                $query->whereBetween('official_travel_date', [
                    $request->get('date1'),
                    $request->get('date2')
                ]);
            }

            if (!empty($request->get('travel_number'))) {
                $query->where('official_travel_number', 'LIKE', '%' . $request->get('travel_number') . '%');
            }

            if (!empty($request->get('destination'))) {
                $query->where('destination', 'LIKE', '%' . $request->get('destination') . '%');
            }

            if (!empty($request->get('nik'))) {
                $query->whereHas('traveler', function ($q) use ($request) {
                    $q->where('nik', 'LIKE', '%' . $request->get('nik') . '%');
                });
            }

            if (!empty($request->get('fullname'))) {
                $query->whereHas('traveler.employee', function ($q) use ($request) {
                    $q->where('fullname', 'LIKE', '%' . $request->get('fullname') . '%');
                });
            }

            if (!empty($request->get('project'))) {
                $query->where('official_travel_origin', $request->get('project'));
            }

            if (!empty($request->get('status'))) {
                $query->where('status', $request->get('status'));
            }

            if (!empty($request->get('search'))) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('official_travel_number', 'LIKE', "%$search%")
                        ->orWhere('destination', 'LIKE', "%$search%")
                        ->orWhereHas('traveler.employee', function ($q) use ($search) {
                            $q->where('fullname', 'LIKE', "%$search%")
                                ->orWhere('nik', 'LIKE', "%$search%");
                        })
                        ->orWhereHas('project', function ($q) use ($search) {
                            $q->where('project_code', 'LIKE', "%$search%")
                                ->orWhere('project_name', 'LIKE', "%$search%");
                        });
                });
            }

            $officialtravels = $query->orderBy('created_at', 'desc')->get();

            return Excel::download(new class($officialtravels) implements FromCollection, WithHeadings, WithMapping {
                private $officialtravels;

                public function __construct($officialtravels)
                {
                    $this->officialtravels = $officialtravels;
                }

                public function collection()
                {
                    return $this->officialtravels;
                }

                public function headings(): array
                {
                    return [
                        'Travel Number',
                        'Date',
                        'Traveler',
                        'Project',
                        'Destination',
                        'Status',
                        'Approve By',
                        'Approve Date',
                        'Approve Remarks',
                        'Arrival Checker',
                        'Arrival Date',
                        'Arrival Remarks',
                        'Departure Checker',
                        'Departure Date',
                        'Departure Remarks',
                        'Created By',
                        'Created At'
                    ];
                }

                public function map($officialtravel): array
                {
                    $traveler = $officialtravel->traveler;
                    $travelerName = $traveler && $traveler->employee ?
                        $traveler->nik . ' - ' . $traveler->employee->fullname : '-';

                    $project = $officialtravel->project ? $officialtravel->project->project_code : '-';

                    $status = match ($officialtravel->status) {
                        'draft' => 'Draft',
                        'submitted' => 'Submitted',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        'canceled' => 'Canceled',
                        'closed' => 'Closed',
                        default => '-'
                    };

                    // Latest approval info from approval_plans (status: 1 = approved)
                    $latestApproval = null;
                    if (isset($officialtravel->approval_plans)) {
                        $approvedPlans = $officialtravel->approval_plans->where('status', 1);
                        if ($approvedPlans->count() > 0) {
                            $latestApproval = $approvedPlans->sortByDesc(function ($plan) {
                                return $plan->updated_at ?? $plan->created_at;
                            })->first();
                        }
                    }
                    $approveBy = ($latestApproval && $latestApproval->approver) ? $latestApproval->approver->name : '-';
                    $approveDate = $latestApproval && ($latestApproval->updated_at || $latestApproval->created_at)
                        ? ($latestApproval->updated_at ?? $latestApproval->created_at)->format('d/m/Y H:i')
                        : '-';
                    $approveRemarks = $latestApproval && $latestApproval->remarks ? $latestApproval->remarks : '-';

                    // Get latest stop information
                    $latestStop = $officialtravel->stops->sortByDesc('created_at')->first();

                    // Arrival information from latest stop
                    $arrivalDate = $latestStop && $latestStop->arrival_at_destination ?
                        date('d/m/Y H:i', strtotime($latestStop->arrival_at_destination)) : '-';
                    $arrivalChecker = $latestStop && $latestStop->arrivalChecker ? $latestStop->arrivalChecker->name : '-';
                    $arrivalRemarks = $latestStop && $latestStop->arrival_remark ? $latestStop->arrival_remark : '-';

                    // Departure information from latest stop
                    $departureDate = $latestStop && $latestStop->departure_from_destination ?
                        date('d/m/Y H:i', strtotime($latestStop->departure_from_destination)) : '-';
                    $departureChecker = $latestStop && $latestStop->departureChecker ? $latestStop->departureChecker->name : '-';
                    $departureRemarks = $latestStop && $latestStop->departure_remark ? $latestStop->departure_remark : '-';

                    return [
                        $officialtravel->official_travel_number,
                        date('d/m/Y', strtotime($officialtravel->official_travel_date)),
                        $travelerName,
                        $project,
                        $officialtravel->destination,
                        $status,
                        $approveBy,
                        $approveDate,
                        $approveRemarks,
                        $arrivalChecker,
                        $arrivalDate,
                        $arrivalRemarks,
                        $departureChecker,
                        $departureDate,
                        $departureRemarks,
                        $officialtravel->creator->name ?? '-',
                        $officialtravel->created_at->format('d/m/Y H:i')
                    ];
                }
            }, 'official_travels_' . date('YmdHis') . '.xlsx');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('toast_error', 'Failed to export data: ' . $e->getMessage());
        }
    }
}
