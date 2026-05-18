<?php

namespace App\Http\Controllers;

use App\Models\Accommodation;
use App\Models\Administration;
use App\Models\ApprovalPlan;
use App\Models\FlightRequest;
use App\Models\LetterNumber;
use App\Models\Officialtravel;
use App\Models\Officialtravel_detail;
use App\Models\OfficialtravelStop;
use App\Models\Project;
use App\Models\Transportation;
use App\Support\UserProject;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Facades\Excel;

class OfficialtravelController extends Controller
{
    /**
     * Semua proyek aktif untuk dropdown destinasi LOT (tanpa filter user_project).
     *
     * @return \Illuminate\Support\Collection<int, Project>
     */
    protected function activeProjectsForDestinationSelect()
    {
        return Project::query()
            ->where('project_status', 1)
            ->orderBy('project_code')
            ->get();
    }

    /**
     * Stamping is allowed only for destinations the user may checkpoint (project-matched destination, or manual destination → origin project).
     * Role Spatie `administrator` is not restricted.
     *
     * @param  'arrival'|'departure'  $stampKind
     */
    protected function ensureStampAllowedForDestination(
        Officialtravel $officialtravel,
        string $stampKind = 'arrival',
        ?OfficialtravelStop $stop = null,
    ): ?\Illuminate\Http\RedirectResponse {
        $user = Auth::user();
        if (! $user) {
            return redirect()->back()->with('toast_error', 'Unauthorized.');
        }

        if ($user->hasRole('administrator')) {
            return null;
        }

        $assignedCount = $user->projects()->where('project_status', 1)->count();
        if ($assignedCount === 0) {
            return redirect()->back()->with(
                'toast_error',
                'Your account has no active user–project assignments. Ask an admin to assign projects before recording checkpoints.'
            );
        }

        if ($stop === null) {
            return redirect()->back()->with('toast_error', 'No itinerary stop was selected for this checkpoint.');
        }

        if (! UserProject::userCanStampOfficialtravelStop($user, $officialtravel, $stop)) {
            return redirect()->back()->with(
                'toast_error',
                'You can only record checkpoints for destinations that match your assigned projects. Manual destinations are handled by users assigned to this travel\'s origin project.'
            );
        }

        return null;
    }

    /**
     * @return array{0: array<int, string>, 1: array<int, bool>}
     */
    protected function normalizeStopsFromRequest(Request $request): array
    {
        $raw = $request->input('stop_destinations', []);
        $rawManual = $request->input('stop_destinations_manual', []);
        if (! is_array($raw)) {
            $raw = [];
        }
        if (! is_array($rawManual)) {
            $rawManual = [];
        }

        $list = [];
        $manualFlags = [];
        foreach ($raw as $i => $s) {
            $t = preg_replace('/\s+/u', ' ', trim((string) $s));
            if ($t === '') {
                continue;
            }
            $list[] = $t;
            $manualFlags[] = isset($rawManual[$i]) && (string) $rawManual[$i] === '1';
        }

        return [array_values($list), $manualFlags];
    }

    protected function syncOfficialtravelPlannedStops(Officialtravel $officialtravel, array $destinationStrings, array $manualFlags = []): void
    {
        if (! $officialtravel->plannedStopsAreEditable()) {
            return;
        }

        $officialtravel->stops()->delete();
        foreach ($destinationStrings as $i => $dest) {
            OfficialtravelStop::create([
                'official_travel_id' => $officialtravel->id,
                'destination' => $dest,
                'sort_order' => $i,
                'is_manual' => (bool) ($manualFlags[$i] ?? false),
            ]);
        }

        $officialtravel->unsetRelation('stops');
    }

    /**
     * Constructor to add permissions
     */
    public function __construct()
    {
        $this->middleware('permission:official-travels.show')->only([
            'index',
            'show',
            'adjustApprovedItinerary',
        ]);
        $this->middleware('permission:official-travels.create')->only('create');
        $this->middleware('permission:official-travels.edit')->only('edit');
        $this->middleware('permission:official-travels.delete')->only('destroy');

        $this->middleware('permission:official-travels.stamp')->only([
            'showArrivalForm',
            'showDepartureForm',
            'arrivalStamp',
            'departureStamp',
            'close',
        ]);
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
        $projects = UserProject::projectsForSelect();

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
            'stops',
            'creator',
        ]);

        UserProject::scopeToAssignedProjects($officialtravels, 'official_travel_origin');

        // Filter by date range
        if (! empty($request->get('date1')) && ! empty($request->get('date2'))) {
            $officialtravels->whereBetween('official_travel_date', [
                $request->get('date1'),
                $request->get('date2'),
            ]);
        }

        // Filter by travel number
        if (! empty($request->get('travel_number'))) {
            $officialtravels->where('official_travel_number', 'LIKE', '%'.$request->get('travel_number').'%');
        }

        // Filter by destination (header + itinerary stops)
        if (! empty($request->get('destination'))) {
            $officialtravels->whereDestinationSearch((string) $request->get('destination'));
        }

        // Filter by NIK
        if (! empty($request->get('nik'))) {
            $officialtravels->whereHas('traveler', function ($query) use ($request) {
                $query->where('nik', 'LIKE', '%'.$request->get('nik').'%');
            });
        }

        // Filter by Traveler Name
        if (! empty($request->get('fullname'))) {
            $officialtravels->whereHas('traveler.employee', function ($query) use ($request) {
                $query->where('fullname', 'LIKE', '%'.$request->get('fullname').'%');
            });
        }

        // Filter by project
        if (! empty($request->get('project'))) {
            $officialtravels->where('official_travel_origin', $request->get('project'));
        }

        // Filter by status
        if (! empty($request->get('status'))) {
            if ($request->get('status') === 'pending_hr') {
                $officialtravels->where('submitted_by_user', true)->whereNull('letter_number_id');
            } else {
                $officialtravels->where('status', $request->get('status'));
            }
        }

        // Global search
        if (! empty($request->get('search'))) {
            $search = $request->get('search');
            $officialtravels->where(function ($query) use ($search) {
                $query->where('official_travel_number', 'LIKE', "%$search%")
                    ->orWhereHas('stops', function ($q) use ($search) {
                        $q->where('destination', 'LIKE', "%$search%");
                    })
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
                    return $traveler->nik.' - '.$traveler->employee->fullname;
                }

                return '-';
            })
            ->addColumn('project', function ($officialtravel) {
                return $officialtravel->project ? $officialtravel->project->project_code : '-';
            })
            ->addColumn('destination', function ($officialtravel) {
                return view('officialtravels.partials.datatable-destination-cell', ['travel' => $officialtravel])->render();
            })
            ->addColumn('status', function ($officialtravel) {
                if ($officialtravel->submitted_by_user && empty($officialtravel->letter_number_id)) {
                    return '<span class="badge badge-info">Menunggu Konfirmasi HR</span>';
                }
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
                        return '<span class="badge badge-light">'.ucfirst($officialtravel->status).'</span>';
                }
            })
            ->addColumn('created_by', function ($officialtravel) {
                $creator = '<small>'.$officialtravel->creator->name.'</small>';

                return $creator;
            })
            ->addColumn('action', function ($model) {
                return view('officialtravels.action', compact('model'))->render();
            })
            ->filterColumn('destination', function ($query, $keyword) {
                if (! is_string($keyword) || trim($keyword) === '') {
                    return;
                }
                $query->whereDestinationSearch($keyword);
            })
            ->rawColumns(['action', 'status', 'created_by', 'destination'])
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

        $projects = UserProject::projectsForSelect();
        $destinationProjects = $this->activeProjectsForDestinationSelect();
        $accommodations = Accommodation::where('accommodation_status', 1)->get();
        $transportations = Transportation::where('transportation_status', 1)->get();

        // Travel Number will be generated based on selected letter number
        $romanMonth = $this->numberToRoman(now()->month);
        $travelNumber = sprintf('ARKA/[Letter Number]/HR/%s/%s', $romanMonth, now()->year);

        // Load employees with their relationships
        $employees = $this->administrationsForOfficialTravelSelectQuery()->get()->map(function ($employee) {
            return [
                'id' => $employee->id,
                'nik' => $employee->nik,
                'fullname' => $employee->employee->fullname ?? 'Unknown',
                'position' => $employee->position->position_name ?? '-',
                'project' => $employee->project->project_name ?? '-',
                'department' => $employee->position->department->department_name ?? '-',
                'position_id' => $employee->position_id,
                'project_id' => $employee->project_id,
                'department_id' => $employee->position->department_id ?? null,
            ];
        });

        return view('officialtravels.create', compact(
            'title',
            'subtitle',
            'projects',
            'destinationProjects',
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
            'displayMode' => $displayMode,
        ])->header('Content-Type', 'text/html');
    }

    /**
     * Store a newly created resource in storage.
     *
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
                'stop_destinations' => 'required|array|min:1',
                'stop_destinations.*' => 'required|string|min:3',
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
                'stop_destinations.required' => 'At least one destination is required.',
                'stop_destinations.min' => 'At least one destination is required.',
                'stop_destinations.*.required' => 'Each destination must be filled in.',
                'stop_destinations.*.min' => 'Each destination must be at least 3 characters.',
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

            [$stopDestinations, $stopDestinationManualFlags] = $this->normalizeStopsFromRequest($request);

            if ($r = $this->guardOfficialtravelRequestOrigin($request)) {
                return $r;
            }

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
                    $travelNumber = sprintf('ARKA/%s/HR/%s/%s', $letterNumberString, $romanMonth, now()->year);
                } else {
                    throw new \Exception('Selected letter number is not available or not reserved. Current status: '.($letterNumberRecord ? $letterNumberRecord->status : 'not found'));
                }
            } else {
                // Generate LOT number with auto sequence if no letter number selected
                $lastTravel = Officialtravel::whereYear('created_at', now()->year)
                    ->orderBy('created_at', 'desc')
                    ->first();

                $sequence = $lastTravel ? (int) substr($lastTravel->official_travel_number, 6, 4) + 1 : 1;
                $travelNumber = sprintf('ARKA/B%04d/HR/%s/%s', $sequence, $romanMonth, now()->year);
            }

            // Check if generated travel number already exists
            $exists = Officialtravel::where('official_travel_number', $travelNumber)->exists();
            if ($exists) {
                throw new \Exception('Generated LOT number already exists: '.$travelNumber.'. Please try again or select a different letter number.');
            }

            // Determine status based on submit action
            $status = $request->submit_action === 'submit' ? 'submitted' : 'draft';
            $submitAt = $request->submit_action === 'submit' ? now() : null;

            // Ensure manual_approvers is an array and preserve order
            $manualApprovers = $request->manual_approvers ?? [];
            if (! is_array($manualApprovers)) {
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
                'destination' => '',
                'duration' => $request->duration,
                'departure_from' => $request->departure_from,
                'transportation_id' => $request->transportation_id,
                'accommodation_id' => $request->accommodation_id,
                'manual_approvers' => $manualApprovers,
                'created_by' => auth()->id(),
                'submit_at' => $submitAt,
            ]);
            $officialtravel->save();

            $this->syncOfficialtravelPlannedStops($officialtravel, $stopDestinations, $stopDestinationManualFlags);

            // Mark letter number as used jika ada
            if ($letterNumberRecord) {
                $letterNumberRecord->markAsUsed('officialtravel', $officialtravel->id);

                // Log the letter number usage for debugging
                Log::info('Letter Number marked as used', [
                    'letter_number_id' => $letterNumberRecord->id,
                    'letter_number' => $letterNumberRecord->letter_number,
                    'officialtravel_id' => $officialtravel->id,
                    'official_travel_number' => $officialtravel->official_travel_number,
                ]);
            }

            // Add followers if any
            if ($request->has('followers') && is_array($request->followers)) {
                foreach ($request->followers as $followerId) {
                    Officialtravel_detail::create([
                        'official_travel_id' => $officialtravel->id,
                        'follower_id' => $followerId,
                    ]);
                }
            }

            // Optional flight request (fr_data) — sama seperti pengajuan my-travels
            FlightRequest::createFromFrData($request, $officialtravel);

            // If submitted, create approval plans using manual approvers
            if ($request->submit_action === 'submit' && ! empty($manualApprovers)) {
                $response = app(ApprovalPlanController::class)->create_manual_approval_plan('officialtravel', $officialtravel->id);
                if (! $response || $response === 0) {
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
                $message .= ' Letter Number: '.$letterNumberString.' (Status changed to Used)';
            }
            $message .= ' LOT Number: '.$travelNumber;

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
                ->with('toast_error', 'Failed to create Official Travel. '.$e->getMessage())
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
            'flightRequests.details',
            'stops.arrivalChecker',
            'stops.departureChecker',
            'latestStop',
        ])->findOrFail($id);

        $destinationProjectsForItineraryAdjust = collect();
        $canAdjustApprovedItinerary = false;
        $user = Auth::user();
        if ($user && UserProject::userMayAdjustApprovedOfficialtravelItinerary($user, $officialtravel)) {
            $canAdjustApprovedItinerary = true;
            $destinationProjectsForItineraryAdjust = $this->activeProjectsForDestinationSelect();
        }

        return view(
            'officialtravels.show',
            compact(
                'title',
                'subtitle',
                'officialtravel',
                'destinationProjectsForItineraryAdjust',
                'canAdjustApprovedItinerary'
            )
        );
    }

    /**
     * Adjust upcoming itinerary legs on an approved LOT (origin project / administrator only).
     */
    public function adjustApprovedItinerary(Request $request, Officialtravel $officialtravel)
    {
        $user = Auth::user();
        if (! $user || ! UserProject::userMayAdjustApprovedOfficialtravelItinerary($user, $officialtravel)) {
            abort(403, 'You cannot change this itinerary.');
        }

        $this->validate($request, [
            'stop_destinations' => 'required|array',
        ], [
            'stop_destinations.required' => 'Please add at least one stop.',
        ]);

        [$list, $flags] = $this->normalizeStopsFromRequest($request);

        if ($list === []) {
            return redirect()->back()
                ->withInput()
                ->with('toast_error', 'Please add at least one stop.');
        }

        foreach ($list as $d) {
            if (mb_strlen(preg_replace('/\s+/u', ' ', trim((string) $d))) < 3) {
                return redirect()->back()
                    ->withInput()
                    ->with('toast_error', 'Each stop must be at least 3 characters.');
            }
        }

        if (count($list) < $officialtravel->approvedItineraryLockedStopCount()) {
            return redirect()->back()
                ->withInput()
                ->with('toast_error', 'You cannot remove stops that already have a checkpoint. Refresh the page if the trip changed.');
        }

        try {
            DB::beginTransaction();
            $officialtravel->replaceItineraryKeepingCheckpointStops($list, $flags);
            DB::commit();
        } catch (\InvalidArgumentException $e) {
            DB::rollBack();

            return redirect()->back()
                ->withInput()
                ->with('toast_error', $e->getMessage());
        }

        return redirect()
            ->route('officialtravels.show', $officialtravel->id)
            ->with('toast_success', 'Itinerary saved.');
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Officialtravel $officialtravel)
    {
        $title = 'Official Travels';
        $subtitle = 'Edit Official Travel';

        $projects = UserProject::projectsForSelect();
        $destinationProjects = $this->activeProjectsForDestinationSelect();
        $accommodations = Accommodation::where('accommodation_status', 1)->get();
        $transportations = Transportation::where('transportation_status', 1)->get();

        // Load relationships with null safety
        $officialtravel->load([
            'details.follower.position.department',
            'details.follower.project',
            'traveler.position.department',
            'traveler.project',
            'project',
            'stops',
        ]);

        // Load employees with their relationships
        $employees = $this->administrationsForOfficialTravelSelectQuery()->get()->map(function ($employee) {
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
                'department_id' => $position ? ($position->department_id ?? ($department ? $department->id : null)) : null,
            ];
        });

        // Get selected followers
        $selectedFollowers = $officialtravel->details->pluck('follower_id')->toArray();

        $existingFlightRequest = $officialtravel->flightRequests()->with('details')->first();

        return view('officialtravels.edit', compact(
            'title',
            'subtitle',
            'officialtravel',
            'projects',
            'destinationProjects',
            'accommodations',
            'transportations',
            'employees',
            'selectedFollowers',
            'existingFlightRequest',
        ));
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Officialtravel $officialtravel)
    {
        try {
            $isPendingHr = $officialtravel->isPendingHr();

            if ($isPendingHr) {
                // HR confirming user submission: require letter number and approvers
                $rules = [
                    'letter_number_id' => 'required|exists:letter_numbers,id',
                    'official_travel_date' => 'required|date',
                    'official_travel_origin' => 'required',
                    'traveler_id' => 'required',
                    'purpose' => 'required',
                    'duration' => 'required',
                    'departure_from' => 'required|date',
                    'transportation_id' => 'required',
                    'accommodation_id' => 'required',
                    'followers' => 'nullable|array',
                    'manual_approvers' => 'required|array|min:1',
                    'manual_approvers.*' => 'exists:users,id',
                ];
                if ($officialtravel->plannedStopsAreEditable()) {
                    $rules['stop_destinations'] = 'required|array|min:1';
                    $rules['stop_destinations.*'] = 'required|string|min:3';
                }
                $this->validate($request, $rules, [
                    'letter_number_id.required' => 'Pilih nomor surat untuk mengonfirmasi pengajuan ini.',
                    'manual_approvers.required' => 'Pilih minimal satu approver.',
                    'stop_destinations.required' => 'Destinasi minimal satu destination wajib diisi.',
                ]);
            } else {
                $rules = [
                    'official_travel_number' => 'required|unique:officialtravels,official_travel_number,'.$officialtravel->id,
                    'official_travel_date' => 'required|date',
                    'official_travel_origin' => 'required',
                    'traveler_id' => 'required',
                    'purpose' => 'required',
                    'duration' => 'required',
                    'departure_from' => 'required|date',
                    'transportation_id' => 'required',
                    'accommodation_id' => 'required',
                    'followers' => 'nullable|array',
                    'manual_approvers' => 'nullable|array|min:1',
                    'manual_approvers.*' => 'exists:users,id',
                ];
                if ($officialtravel->plannedStopsAreEditable()) {
                    $rules['stop_destinations'] = 'required|array|min:1';
                    $rules['stop_destinations.*'] = 'required|string|min:3';
                }
                $this->validate($request, $rules, [
                    'manual_approvers.array' => 'Approvers must be an array.',
                    'manual_approvers.min' => 'Please select at least one approver.',
                    'manual_approvers.*.exists' => 'One or more selected approvers are invalid.',
                    'stop_destinations.required' => 'At least one destination is required.',
                ]);
            }

            [$stopDestinations, $stopDestinationManualFlags] = $officialtravel->plannedStopsAreEditable()
                ? $this->normalizeStopsFromRequest($request)
                : [[], []];

            if ($r = $this->guardOfficialtravelRequestOrigin($request)) {
                return $r;
            }

            DB::beginTransaction();

            if ($officialtravel->status !== 'draft') {
                throw new \Exception('Cannot edit Official Travel that is not in draft or pending HR confirmation status.');
            }

            // Ensure manual_approvers is an array and preserve order
            $manualApprovers = $request->manual_approvers ?? [];
            if (! is_array($manualApprovers)) {
                $manualApprovers = [];
            }
            $manualApprovers = array_values(array_filter($manualApprovers));

            $approversChanged = json_encode($officialtravel->manual_approvers ?? []) !== json_encode($manualApprovers);

            if ($isPendingHr && $request->letter_number_id) {
                // Assign letter number (HR confirmation for user submission)
                $letterNumberRecord = LetterNumber::find($request->letter_number_id);
                if (! $letterNumberRecord || $letterNumberRecord->status !== 'reserved') {
                    throw new \Exception('Nomor surat tidak tersedia atau belum di-reserve. Status: '.($letterNumberRecord ? $letterNumberRecord->status : 'not found'));
                }
                $romanMonth = $this->numberToRoman(now()->month);
                $travelNumber = sprintf('ARKA/%s/HR/%s/%s', $letterNumberRecord->letter_number, $romanMonth, now()->year);
                if (Officialtravel::where('official_travel_number', $travelNumber)->where('id', '!=', $officialtravel->id)->exists()) {
                    throw new \Exception('Nomor LOT dari surat ini sudah digunakan: '.$travelNumber);
                }

                $officialtravel->update([
                    'letter_number_id' => $letterNumberRecord->id,
                    'letter_number' => $letterNumberRecord->letter_number,
                    'official_travel_number' => $travelNumber,
                    'official_travel_date' => $request->official_travel_date,
                    'official_travel_origin' => $request->official_travel_origin,
                    'submitted_by_user' => false,
                    'traveler_id' => $request->traveler_id,
                    'purpose' => $request->purpose,
                    'duration' => $request->duration,
                    'departure_from' => $request->departure_from,
                    'transportation_id' => $request->transportation_id,
                    'accommodation_id' => $request->accommodation_id,
                    'manual_approvers' => $manualApprovers,
                ]);

                $letterNumberRecord->markAsUsed('officialtravel', $officialtravel->id);
                Log::info('LOT pending_hr confirmed by HR: letter number assigned', [
                    'officialtravel_id' => $officialtravel->id,
                    'letter_number_id' => $letterNumberRecord->id,
                ]);
            } else {
                // Normal draft update
                $officialtravel->update([
                    'official_travel_number' => $request->official_travel_number,
                    'official_travel_date' => $request->official_travel_date,
                    'official_travel_origin' => $request->official_travel_origin,
                    'traveler_id' => $request->traveler_id,
                    'purpose' => $request->purpose,
                    'duration' => $request->duration,
                    'departure_from' => $request->departure_from,
                    'transportation_id' => $request->transportation_id,
                    'accommodation_id' => $request->accommodation_id,
                    'manual_approvers' => $manualApprovers,
                ]);
            }

            if ($officialtravel->plannedStopsAreEditable() && $stopDestinations !== []) {
                $this->syncOfficialtravelPlannedStops($officialtravel, $stopDestinations, $stopDestinationManualFlags);
            }

            if ($approversChanged) {
                ApprovalPlan::where('document_id', $officialtravel->id)
                    ->where('document_type', 'officialtravel')
                    ->delete();
                Log::info("Deleted existing approval plans for officialtravel {$officialtravel->id} due to approver changes");
            }

            $officialtravel->details()->delete();
            if ($request->has('followers') && is_array($request->followers)) {
                foreach ($request->followers as $followerId) {
                    Officialtravel_detail::create([
                        'official_travel_id' => $officialtravel->id,
                        'follower_id' => $followerId,
                    ]);
                }
            }

            // Flight request: sync dari fr_data (sama seperti my-travels update)
            $officialtravel->flightRequests()->each(function ($fr) {
                $fr->delete();
            });
            FlightRequest::createFromFrData($request, $officialtravel);

            DB::commit();

            $message = $isPendingHr
                ? 'Pengajuan LOT telah dikonfirmasi. Nomor surat dan nomor LOT telah diisi. Status: Draft.'
                : 'Official Travel updated successfully!';

            return redirect('officialtravels/'.$officialtravel->id)->with('toast_success', $message);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();

            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollback();

            return redirect()->back()
                ->with('toast_error', 'Failed to update Official Travel. '.$e->getMessage())
                ->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
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
                ->with('toast_error', 'Failed to delete Official Travel. '.$e->getMessage());
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

            if (! $response || $response === 0) {
                DB::rollback();

                return redirect()->back()
                    ->with('toast_error', 'Failed to create approval plans. Please ensure at least one approver is selected.');
            }

            DB::commit();

            return redirect()->route('officialtravels.show', $officialtravel->id)
                ->with('toast_success', 'Official travel has been submitted for approval. '.$response.' approver(s) will review your request.');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->back()
                ->with('toast_error', 'Official travel not found.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error submitting official travel for approval: '.$e->getMessage(), [
                'officialtravel_id' => $id,
                'exception' => $e,
            ]);

            return redirect()->back()
                ->with('toast_error', 'Failed to submit official travel for approval: '.$e->getMessage());
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
            'latestStop',
            'stops',
        ])->findOrFail($id);

        if (! $officialtravel->canRecordArrival()) {
            return redirect()->back()->with('toast_error', 'Cannot record arrival at this time. Please check the current status.');
        }

        if (! $officialtravel->userCanStampAnyArrival(Auth::user())) {
            return redirect()->back()->with(
                'toast_error',
                'You are not allowed to record arrival for this travel (no matching project for pending destinations).'
            );
        }

        $arrivalStampCandidates = $officialtravel->stopsEligibleForArrivalStamp(Auth::user());

        $title = 'Official Travels';
        $subtitle = 'Record Arrival';

        return view('officialtravels.arrival', compact('title', 'subtitle', 'officialtravel', 'arrivalStampCandidates'));
    }

    /**
     * Process arrival stamp
     */
    public function arrivalStamp(Request $request, Officialtravel $officialtravel)
    {
        try {
            if (! $officialtravel->canRecordArrival()) {
                return redirect()->back()->with('toast_error', 'Cannot record arrival at this time. Please check the current status.');
            }

            if (! $officialtravel->userCanStampAnyArrival(Auth::user())) {
                return redirect()->back()->with(
                    'toast_error',
                    'You are not allowed to record arrival for this travel (no matching project for pending destinations).'
                );
            }

            $this->validate($request, [
                'arrival_at_destination' => 'required|date',
                'arrival_remark' => 'required|string',
                'official_travel_stop_id' => 'nullable|integer|exists:officialtravel_stops,id',
            ]);

            DB::beginTransaction();

            $officialtravel->load('stops');
            $candidates = $officialtravel->stopsEligibleForArrivalStamp(Auth::user());

            if (! $officialtravel->stops()->exists()) {
                DB::rollBack();

                return redirect()->back()->with(
                    'toast_error',
                    'This official travel has no itinerary stops. Add destinations on the travel record or contact HR.'
                );
            }

            if ($candidates->isEmpty()) {
                DB::rollBack();

                return redirect()->back()->with('toast_error', 'No arrival checkpoint is available for your assigned projects.');
            }

            if ($candidates->count() > 1 && ! $request->filled('official_travel_stop_id')) {
                DB::rollBack();

                return redirect()->back()->with('toast_error', 'Please select which destination you are recording arrival for.');
            }

            $stop = null;
            if ($request->filled('official_travel_stop_id')) {
                $stop = $candidates->firstWhere('id', (int) $request->input('official_travel_stop_id'));
            } else {
                $stop = $candidates->first();
            }

            if (! $stop) {
                DB::rollBack();

                return redirect()->back()->with('toast_error', 'Invalid destination selected for this arrival.');
            }

            if ($redirect = $this->ensureStampAllowedForDestination($officialtravel, 'arrival', $stop)) {
                DB::rollBack();

                return $redirect;
            }

            $stop->update([
                'arrival_at_destination' => $request->arrival_at_destination,
                'arrival_check_by' => Auth::id(),
                'arrival_remark' => $request->arrival_remark,
                'arrival_timestamps' => now(),
            ]);

            $officialtravel->unsetRelation('stops');

            DB::commit();

            return redirect('officialtravels/'.$officialtravel->id)->with('toast_success', 'Arrival recorded successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();

            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollback();

            return redirect()->back()
                ->with('toast_error', 'Failed to record arrival. '.$e->getMessage())
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
            'latestStop',
            'stops',
        ])->findOrFail($id);

        if (! $officialtravel->canRecordDeparture()) {
            return redirect()->back()->with('toast_error', 'Cannot record departure at this time. Please check the current status.');
        }

        if (! $officialtravel->userCanStampAnyDeparture(Auth::user())) {
            return redirect()->back()->with(
                'toast_error',
                'You are not allowed to record departure for this travel (no matching project for pending destinations).'
            );
        }

        $departureStampCandidates = $officialtravel->stopsEligibleForDepartureStamp(Auth::user());

        $title = 'Official Travels';
        $subtitle = 'Record Departure';

        return view('officialtravels.departure', compact('title', 'subtitle', 'officialtravel', 'departureStampCandidates'));
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
                'latestStop',
                'stops',
            ])->findOrFail($id);

            if (! $officialtravel->canRecordDeparture()) {
                return redirect()->back()->with('toast_error', 'Cannot record departure at this time. Please check the current status.');
            }

            if (! $officialtravel->userCanStampAnyDeparture(Auth::user())) {
                return redirect()->back()->with(
                    'toast_error',
                    'You are not allowed to record departure for this travel (no matching project for pending destinations).'
                );
            }

            $request->validate([
                'departure_from_destination' => 'required|date',
                'departure_remark' => 'required|string',
                'official_travel_stop_id' => 'nullable|integer|exists:officialtravel_stops,id',
            ]);

            DB::beginTransaction();

            $candidates = $officialtravel->stopsEligibleForDepartureStamp(Auth::user());

            if ($candidates->isEmpty()) {
                DB::rollBack();

                return redirect()->back()->with('toast_error', 'No departure checkpoint is available for your assigned projects.');
            }

            if ($candidates->count() > 1 && ! $request->filled('official_travel_stop_id')) {
                DB::rollBack();

                return redirect()->back()->with('toast_error', 'Please select which destination you are recording departure for.');
            }

            $stop = null;
            if ($request->filled('official_travel_stop_id')) {
                $stop = $candidates->firstWhere('id', (int) $request->input('official_travel_stop_id'));
            } else {
                $stop = $candidates->first();
            }

            if (! $stop) {
                DB::rollBack();

                return redirect()->back()->with('toast_error', 'Invalid destination selected for this departure.');
            }

            if ($redirect = $this->ensureStampAllowedForDestination($officialtravel, 'departure', $stop)) {
                DB::rollBack();

                return $redirect;
            }

            $stop->update([
                'departure_from_destination' => $request->departure_from_destination,
                'departure_check_by' => auth()->id(),
                'departure_remark' => $request->departure_remark,
                'departure_timestamps' => now(),
            ]);

            $officialtravel->unsetRelation('stops');

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
                ->with('toast_error', 'Failed to record departure. '.$e->getMessage())
                ->withInput();
        }
    }

    public function print(Request $request, Officialtravel $officialtravel)
    {
        $title = 'Official Travels';
        $subtitle = 'Official Travel Details';

        $officialtravel->load([
            'traveler.employee',
            'traveler.position.department',
            'traveler.project',
            'project',
            'transportation',
            'accommodation',
            'details.follower.employee',
            'details.follower.position.department',
            'details.follower.project',
            'stops.arrivalChecker',
            'stops.departureChecker',
            'approval_plans' => function ($q) {
                $q->orderBy('approval_order')->orderBy('id')->with([
                    'approver.administration.position',
                ]);
            },
        ]);

        $printStop = null;
        $printStopLeg = null;

        if ($request->filled('stop')) {
            $printStop = OfficialtravelStop::query()
                ->where('official_travel_id', $officialtravel->id)
                ->whereKey($request->query('stop'))
                ->with(['arrivalChecker', 'departureChecker'])
                ->firstOrFail();

            $printStopLeg = $officialtravel->stops
                ->sortBy(['sort_order', 'id'])
                ->values()
                ->search(fn (OfficialtravelStop $s) => (string) $s->id === (string) $printStop->id);

            if ($printStopLeg === false) {
                abort(404);
            }

            $printStopLeg = (int) $printStopLeg + 1;
        }

        return view('officialtravels.print', compact(
            'title',
            'subtitle',
            'officialtravel',
            'printStop',
            'printStopLeg'
        ));
    }

    /**
     * Close the official travel
     */
    public function close(Officialtravel $officialtravel)
    {
        try {
            // Full itinerary complete, or early close from LOT origin after at least one checkpoint is done
            if (! $officialtravel->userMayClose(Auth::user())) {
                return redirect()->back()->with(
                    'toast_error',
                    'Cannot close this official travel. Complete all checkpoints, or — if the trip ended early at origin — ensure at least one stop has arrival and departure and use an account assigned to the LOT origin project (or administrator).'
                );
            }

            DB::beginTransaction();

            $officialtravel->update([
                'status' => 'closed',
            ]);

            DB::commit();

            return redirect()->route('officialtravels.show', $officialtravel->id)
                ->with('toast_success', 'Official travel has been closed successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('toast_error', 'Failed to close official travel. '.$e->getMessage());
        }
    }

    /**
     * LOT origin project must be in the user's assigned projects (main traveler / followers are not scoped by user_project).
     *
     * @return \Illuminate\Http\RedirectResponse|null
     */
    protected function guardOfficialtravelRequestOrigin(Request $request)
    {
        return UserProject::guardProjectInAssignmentScope((int) $request->official_travel_origin);
    }

    /**
     * Administrations for traveler/follower pickers: all active rows (not filtered by user_project).
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    protected function administrationsForOfficialTravelSelectQuery()
    {
        return Administration::with([
            'employee',
            'position.department',
            'project',
        ])
            ->where('is_active', 1)
            ->orderBy('nik', 'asc');
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
            'I' => 1,
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
                'creator',
            ]);

            UserProject::scopeToAssignedProjects($query, 'official_travel_origin');

            // Apply the same filters as getOfficialtravels
            if (! empty($request->get('date1')) && ! empty($request->get('date2'))) {
                $query->whereBetween('official_travel_date', [
                    $request->get('date1'),
                    $request->get('date2'),
                ]);
            }

            if (! empty($request->get('travel_number'))) {
                $query->where('official_travel_number', 'LIKE', '%'.$request->get('travel_number').'%');
            }

            if (! empty($request->get('destination'))) {
                $query->whereDestinationSearch((string) $request->get('destination'));
            }

            if (! empty($request->get('nik'))) {
                $query->whereHas('traveler', function ($q) use ($request) {
                    $q->where('nik', 'LIKE', '%'.$request->get('nik').'%');
                });
            }

            if (! empty($request->get('fullname'))) {
                $query->whereHas('traveler.employee', function ($q) use ($request) {
                    $q->where('fullname', 'LIKE', '%'.$request->get('fullname').'%');
                });
            }

            if (! empty($request->get('project'))) {
                $query->where('official_travel_origin', $request->get('project'));
            }

            if (! empty($request->get('status'))) {
                if ($request->get('status') === 'pending_hr') {
                    $query->where('submitted_by_user', true)->whereNull('letter_number_id');
                } else {
                    $query->where('status', $request->get('status'));
                }
            }

            if (! empty($request->get('search'))) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('official_travel_number', 'LIKE', "%$search%")
                        ->orWhereHas('stops', function ($sq) use ($search) {
                            $sq->where('destination', 'LIKE', "%$search%");
                        })
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

            return Excel::download(new class($officialtravels) implements FromCollection, WithHeadings, WithMapping
            {
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
                        'Created At',
                    ];
                }

                public function map($officialtravel): array
                {
                    $traveler = $officialtravel->traveler;
                    $travelerName = $traveler && $traveler->employee ?
                        $traveler->nik.' - '.$traveler->employee->fullname : '-';

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
                        $officialtravel->itinerarySummaryForDisplay(),
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
                        $officialtravel->created_at->format('d/m/Y H:i'),
                    ];
                }
            }, 'official_travels_'.date('YmdHis').'.xlsx');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('toast_error', 'Failed to export data: '.$e->getMessage());
        }
    }

    // ========================================
    // SELF-SERVICE METHODS FOR USER ROLE
    // ========================================

    /**
     * Display user's own official travels
     */
    public function myTravels()
    {
        $this->authorize('personal.official-travel.view-own');

        return view('officialtravels.my-travels')
            ->with('title', 'My Official Travel Request')
            ->with('subtitle', 'My Official Travel Request');
    }

    /**
     * Get data for user's own official travels DataTable
     */
    public function myTravelsData(Request $request)
    {
        $this->authorize('personal.official-travel.view-own');

        $user = Auth::user();
        $administrationId = $user->administration_id;

        // Return empty result if user has no active administration
        if (! $administrationId) {
            return datatables()->of(collect())
                ->make(true);
        }

        $query = Officialtravel::with(['traveler.employee', 'project', 'stops', 'creator'])
            ->where(function ($q) use ($administrationId) {
                // User is the main traveler
                $q->where('traveler_id', $administrationId)
                    // OR user is a follower
                    ->orWhereHas('details', function ($detailQuery) use ($administrationId) {
                        $detailQuery->where('follower_id', $administrationId);
                    });
            })
            ->select('officialtravels.*')
            ->orderBy('created_at', 'desc');

        // Apply travel number filter
        if ($request->filled('travel_number')) {
            $query->where('official_travel_number', 'like', '%'.$request->travel_number.'%');
        }

        // Apply status filter
        if ($request->filled('status')) {
            if ($request->status === 'pending_hr') {
                $query->where('submitted_by_user', true)->whereNull('letter_number_id');
            } else {
                $query->where('status', $request->status);
            }
        }

        // Apply role filter
        if ($request->filled('role')) {
            if ($request->role === 'main') {
                $query->where('traveler_id', $administrationId);
            } elseif ($request->role === 'follower') {
                $query->whereHas('details', function ($detailQuery) use ($administrationId) {
                    $detailQuery->where('follower_id', $administrationId);
                })->where('traveler_id', '!=', $administrationId);
            }
        }

        // Apply destination filter (header + itinerary stops)
        if ($request->filled('destination')) {
            $query->whereDestinationSearch((string) $request->destination);
        }

        // Apply traveler filter
        if ($request->filled('traveler')) {
            $query->whereHas('traveler.employee', function ($q) use ($request) {
                $q->where('fullname', 'like', '%'.$request->traveler.'%');
            });
        }

        // Apply date range filter
        if ($request->filled('start_date')) {
            $query->whereDate('official_travel_date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->whereDate('official_travel_date', '<=', $request->end_date);
        }

        return datatables()->of($query)
            ->addIndexColumn()
            ->addColumn('travel_number', function ($row) {
                return $row->official_travel_number ?? 'N/A';
            })
            ->addColumn('travel_date', function ($row) {
                return date('d/m/Y', strtotime($row->official_travel_date));
            })
            ->addColumn('traveler_name', function ($row) {
                return $row->traveler->employee->fullname ?? 'N/A';
            })
            ->addColumn('project', function ($row) {
                return $row->project ? $row->project->project_code : '-';
            })
            ->addColumn('destination', function ($row) {
                return view('officialtravels.partials.datatable-destination-cell', ['travel' => $row])->render();
            })
            ->addColumn('status_badge', function ($row) {
                if ($row->submitted_by_user && empty($row->letter_number_id)) {
                    return '<span class="badge badge-warning">Menunggu Konfirmasi HR</span>';
                }
                $badges = [
                    'draft' => '<span class="badge badge-secondary">Draft</span>',
                    'submitted' => '<span class="badge badge-info">Submitted</span>',
                    'approved' => '<span class="badge badge-success">Approved</span>',
                    'rejected' => '<span class="badge badge-danger">Rejected</span>',
                    'closed' => '<span class="badge badge-dark">Closed</span>',
                ];

                return $badges[$row->status] ?? '<span class="badge badge-secondary">Unknown</span>';
            })
            ->addColumn('created_by', function ($row) {
                return $row->creator ? '<small>'.e($row->creator->name).'</small>' : '-';
            })
            ->addColumn('action', function ($row) {
                $btn = '<a href="'.route('officialtravels.my-travels.show', $row->id).'" class="btn btn-sm btn-info mr-1" title="View">
                            <i class="fas fa-eye"></i>
                        </a>';
                $canEdit = $row->submitted_by_user && empty($row->letter_number_id);
                if ($canEdit) {
                    $btn .= '<a href="'.route('officialtravels.my-travels.edit', $row->id).'" class="btn btn-sm btn-warning mr-1" title="Edit">
                            <i class="fas fa-edit"></i>
                        </a>';
                }

                return $btn;
            })
            ->filterColumn('destination', function ($query, $keyword) {
                if (! is_string($keyword) || trim($keyword) === '') {
                    return;
                }
                $query->whereDestinationSearch($keyword);
            })
            ->rawColumns(['status_badge', 'created_by', 'action', 'destination'])
            ->make(true);
    }

    /**
     * Display own official travel details for personal user
     *
     * PERSONAL/USER: Can only view their own official travels (permission: personal.official-travel.view-own)
     */
    public function myTravelsShow($id)
    {
        $this->authorize('personal.official-travel.view-own');

        $user = Auth::user();

        $officialtravel = Officialtravel::with([
            'traveler.employee',
            'traveler.position.department',
            'project',
            'transportation',
            'accommodation',
            'details.follower.employee',
            'flightRequests.details',
            'stops.arrivalChecker',
            'stops.departureChecker',
            'latestStop',
        ])->findOrFail($id);

        // Ensure user can only view their own official travels
        $administrationId = $user->administration_id;
        if (! $administrationId) {
            abort(403, 'You do not have an active administration record. Please contact HR.');
        }

        $isMainTraveler = $officialtravel->traveler_id === $administrationId;
        $isFollower = $officialtravel->details->contains(function ($detail) use ($administrationId) {
            return $detail->follower_id === $administrationId;
        });

        if (! $isMainTraveler && ! $isFollower) {
            abort(403, 'You can only view your own official travels.');
        }

        $title = 'My Official Travels';
        $subtitle = 'Official Travel Details';

        return view('officialtravels.my-travels-show', compact('title', 'subtitle', 'officialtravel'));
    }

    /**
     * Show edit form for own LOT submission (only when belum dikonfirmasi HR).
     */
    public function myTravelsEdit($id)
    {
        $this->authorize('personal.official-travel.view-own');

        $user = Auth::user();
        $administrationId = $user->administration_id;
        if (! $administrationId) {
            return redirect()->route('officialtravels.my-travels')
                ->with('toast_error', 'Anda tidak memiliki data administrasi aktif. Silakan hubungi HR.');
        }

        $officialtravel = Officialtravel::with([
            'traveler.employee',
            'traveler.position.department',
            'project',
            'transportation',
            'accommodation',
            'details.follower.employee',
            'details.follower.position.department',
            'details.follower.project',
            'stops',
        ])->findOrFail($id);

        if ($officialtravel->traveler_id !== $administrationId) {
            abort(403, 'Anda hanya dapat mengedit pengajuan LOT Anda sendiri.');
        }
        if (! $officialtravel->submitted_by_user || $officialtravel->letter_number_id) {
            return redirect()->route('officialtravels.my-travels.show', $id)
                ->with('toast_error', 'Pengajuan ini sudah dikonfirmasi HR atau bukan pengajuan dari user. Tidak dapat diedit.');
        }

        $title = 'My Official Travel Request';
        $subtitle = 'Edit LOT Request';

        $projects = UserProject::projectsForSelect();
        $destinationProjects = $this->activeProjectsForDestinationSelect();
        $accommodations = Accommodation::where('accommodation_status', 1)->get();
        $transportations = Transportation::where('transportation_status', 1)->get();

        $employees = $this->administrationsForOfficialTravelSelectQuery()->get()->map(function ($employee) {
            return [
                'id' => $employee->id,
                'nik' => $employee->nik,
                'fullname' => $employee->employee->fullname ?? 'Unknown',
                'position' => $employee->position->position_name ?? '-',
                'project' => $employee->project->project_name ?? '-',
                'department' => $employee->position->department->department_name ?? '-',
                'position_id' => $employee->position_id,
                'project_id' => $employee->project_id,
                'department_id' => $employee->position->department_id ?? null,
            ];
        });

        $myAdministration = Administration::with(['employee', 'position.department', 'project'])->find($administrationId);

        $existingFlightRequest = $officialtravel->flightRequests()->with('details')->first();

        return view('officialtravels.my-travels-edit', compact(
            'title',
            'subtitle',
            'projects',
            'destinationProjects',
            'accommodations',
            'transportations',
            'employees',
            'myAdministration',
            'officialtravel',
            'existingFlightRequest',
        ));
    }

    /**
     * Update own LOT submission (only when belum dikonfirmasi HR).
     */
    public function myTravelsUpdate(Request $request, $id)
    {
        $this->authorize('personal.official-travel.view-own');

        $user = Auth::user();
        $administrationId = $user->administration_id;
        if (! $administrationId) {
            return redirect()->route('officialtravels.my-travels')
                ->with('toast_error', 'Anda tidak memiliki data administrasi aktif. Silakan hubungi HR.');
        }

        $officialtravel = Officialtravel::with(['details', 'stops'])->findOrFail($id);
        if ($officialtravel->traveler_id !== $administrationId) {
            abort(403, 'Anda hanya dapat mengedit pengajuan LOT Anda sendiri.');
        }
        if (! $officialtravel->submitted_by_user || $officialtravel->letter_number_id) {
            return redirect()->route('officialtravels.my-travels.show', $id)
                ->with('toast_error', 'Pengajuan ini sudah dikonfirmasi HR. Tidak dapat diedit.');
        }

        try {
            $this->validate($request, [
                'official_travel_date' => 'required|date',
                'official_travel_origin' => 'required|exists:projects,id',
                'purpose' => 'required|string',
                'stop_destinations' => 'required|array|min:1',
                'stop_destinations.*' => 'required|string|min:3',
                'duration' => 'required|string|min:1',
                'departure_from' => 'required|date',
                'transportation_id' => 'required|exists:transportations,id',
                'accommodation_id' => 'required|exists:accommodations,id',
                'followers' => 'nullable|array',
                'followers.*' => 'exists:administrations,id',
            ], [
                'official_travel_date.required' => 'Tanggal LOT wajib diisi.',
                'official_travel_origin.required' => 'Asal LOT wajib dipilih.',
                'purpose.required' => 'Tujuan perjalanan wajib diisi.',
                'stop_destinations.required' => 'Destinasi (minimal satu destination) wajib diisi.',
                'departure_from.after_or_equal' => 'Tanggal keberangkatan tidak boleh di masa lalu.',
                'transportation_id.required' => 'Transportasi wajib dipilih.',
                'accommodation_id.required' => 'Akomodasi wajib dipilih.',
            ]);

            [$stopDestinations, $stopDestinationManualFlags] = $this->normalizeStopsFromRequest($request);

            if ($r = $this->guardOfficialtravelRequestOrigin($request)) {
                return $r;
            }

            DB::beginTransaction();

            $officialtravel->update([
                'official_travel_date' => $request->official_travel_date,
                'official_travel_origin' => $request->official_travel_origin,
                'purpose' => $request->purpose,
                'duration' => $request->duration,
                'departure_from' => $request->departure_from,
                'transportation_id' => $request->transportation_id,
                'accommodation_id' => $request->accommodation_id,
            ]);

            if ($officialtravel->plannedStopsAreEditable()) {
                $this->syncOfficialtravelPlannedStops($officialtravel, $stopDestinations, $stopDestinationManualFlags);
            }

            // Sync followers: remove current, add from request (exclude main traveler)
            $officialtravel->details()->delete();
            if ($request->has('followers') && is_array($request->followers)) {
                foreach (array_filter($request->followers) as $followerId) {
                    if ((int) $followerId !== (int) $administrationId) {
                        Officialtravel_detail::create([
                            'official_travel_id' => $officialtravel->id,
                            'follower_id' => $followerId,
                        ]);
                    }
                }
            }

            // Flight request: if fr_data submitted, replace existing
            $officialtravel->flightRequests()->each(function ($fr) {
                $fr->delete();
            });
            FlightRequest::createFromFrData($request, $officialtravel);

            DB::commit();

            return redirect()
                ->route('officialtravels.my-travels.show', $officialtravel->id)
                ->with('toast_success', 'Pengajuan LOT berhasil diperbarui.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('toast_error', 'Gagal memperbarui: '.$e->getMessage())
                ->withInput();
        }
    }

    /**
     * Highest numeric suffix among REQ* numbers for pengajuan My Official Travel (submitted_by_user).
     */
    private function maxSubmittedByUserReqSequence(): int
    {
        $maxSeq = 0;
        $numbers = Officialtravel::where('submitted_by_user', true)
            ->where('official_travel_number', 'like', 'REQ%')
            ->pluck('official_travel_number');
        foreach ($numbers as $num) {
            if (preg_match('/^REQ(\d+)$/', (string) $num, $m)) {
                $maxSeq = max($maxSeq, (int) $m[1]);
            }
        }

        return $maxSeq;
    }

    /**
     * Next REQxxxxx: max(submitted_by_user REQ) + 1, then skip any official_travel_number already used (globally).
     */
    private function allocateNextSubmittedByUserReqOfficialTravelNumber(): string
    {
        $sequence = $this->maxSubmittedByUserReqSequence() + 1;
        for ($attempt = 0; $attempt < 100; $attempt++, $sequence++) {
            $travelNumber = 'REQ'.sprintf('%05d', $sequence);
            if (! Officialtravel::where('official_travel_number', $travelNumber)->exists()) {
                return $travelNumber;
            }
        }

        throw new \RuntimeException('Tidak dapat menghasilkan nomor REQ unik.');
    }

    private function acquireOfficialTravelReqSequenceLock(): bool
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return false;
        }
        $row = DB::selectOne('SELECT GET_LOCK(?, 30) AS acquired', ['officialtravel_submitted_by_user_req_seq']);

        return $row && (int) $row->acquired === 1;
    }

    private function releaseOfficialTravelReqSequenceLock(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }
        DB::selectOne('SELECT RELEASE_LOCK(?) AS released', ['officialtravel_submitted_by_user_req_seq']);
    }

    /**
     * Show form for user to Add My Official Travel (LOT) (no letter number; HR will confirm later).
     */
    public function myTravelsCreate()
    {
        $this->authorize('personal.official-travel.create-own');

        $user = Auth::user();
        $administrationId = $user->administration_id;
        if (! $administrationId) {
            return redirect()->route('officialtravels.my-travels')
                ->with('toast_error', 'Anda tidak memiliki data administrasi aktif. Silakan hubungi HR.');
        }

        $title = 'My Official Travel Request';
        $subtitle = 'Add My Official Travel (LOT)';

        $projects = UserProject::projectsForSelect();
        $destinationProjects = $this->activeProjectsForDestinationSelect();
        $accommodations = Accommodation::where('accommodation_status', 1)->get();
        $transportations = Transportation::where('transportation_status', 1)->get();

        $employees = $this->administrationsForOfficialTravelSelectQuery()->get()->map(function ($employee) {
            return [
                'id' => $employee->id,
                'nik' => $employee->nik,
                'fullname' => $employee->employee->fullname ?? 'Unknown',
                'position' => $employee->position->position_name ?? '-',
                'project' => $employee->project->project_name ?? '-',
                'department' => $employee->position->department->department_name ?? '-',
                'position_id' => $employee->position_id,
                'project_id' => $employee->project_id,
                'department_id' => $employee->position->department_id ?? null,
            ];
        });

        $myAdministration = Administration::with(['employee', 'position.department', 'project'])->find($administrationId);

        // Preview REQ: berikutnya dari max semua REQ pengajuan user (bukan hanya id terakhir)
        $previewTravelNumber = 'REQ'.sprintf('%05d', $this->maxSubmittedByUserReqSequence() + 1);

        return view('officialtravels.my-travels-create', compact(
            'title',
            'subtitle',
            'projects',
            'destinationProjects',
            'accommodations',
            'transportations',
            'employees',
            'myAdministration',
            'previewTravelNumber',
        ));
    }

    /**
     * Store user LOT submission (status pending_hr; no letter number; HR will confirm and assign letter number).
     */
    public function myTravelsStore(Request $request)
    {
        $this->authorize('personal.official-travel.create-own');

        $user = Auth::user();
        $administrationId = $user->administration_id;
        if (! $administrationId) {
            return redirect()->route('officialtravels.my-travels')
                ->with('toast_error', 'Anda tidak memiliki data administrasi aktif. Silakan hubungi HR.');
        }

        try {
            $this->validate($request, [
                'official_travel_date' => 'required|date',
                'official_travel_origin' => 'required|exists:projects,id',
                'purpose' => 'required|string',
                'stop_destinations' => 'required|array|min:1',
                'stop_destinations.*' => 'required|string|min:3',
                'duration' => 'required|string|min:1',
                'departure_from' => 'required|date|after_or_equal:today',
                'transportation_id' => 'required|exists:transportations,id',
                'accommodation_id' => 'required|exists:accommodations,id',
                'followers' => 'nullable|array',
                'followers.*' => 'exists:administrations,id',
            ], [
                'official_travel_date.required' => 'Tanggal LOT wajib diisi.',
                'official_travel_origin.required' => 'Asal LOT wajib dipilih.',
                'purpose.required' => 'Tujuan perjalanan wajib diisi.',
                'stop_destinations.required' => 'Destinasi (minimal satu destination) wajib diisi.',
                'departure_from.after_or_equal' => 'Tanggal keberangkatan tidak boleh di masa lalu.',
                'transportation_id.required' => 'Transportasi wajib dipilih.',
                'accommodation_id.required' => 'Akomodasi wajib dipilih.',
            ]);

            [$stopDestinations, $stopDestinationManualFlags] = $this->normalizeStopsFromRequest($request);

            if ($r = $this->guardOfficialtravelRequestOrigin($request)) {
                return $r;
            }

            DB::beginTransaction();

            $lockHeld = false;
            try {
                $lockHeld = $this->acquireOfficialTravelReqSequenceLock();
                if (DB::connection()->getDriverName() === 'mysql' && ! $lockHeld) {
                    throw new \RuntimeException('Sistem sedang sibuk melayani pengajuan lain. Silakan coba lagi sebentar.');
                }

                // Nomor: max(REQ dari submitted_by_user) + 1; jika sudah dipakai baris lain, +1 sampai bebas.
                // GET_LOCK (MySQL) dipegang sampai setelah commit di bawah agar urutan tidak tabrakan.
                $officialtravel = null;
                $maxAttempts = 25;
                for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
                    $travelNumber = $this->allocateNextSubmittedByUserReqOfficialTravelNumber();
                    try {
                        $officialtravel = new Officialtravel([
                            'letter_number_id' => null,
                            'letter_number' => null,
                            'official_travel_number' => $travelNumber,
                            'official_travel_date' => $request->official_travel_date,
                            'official_travel_origin' => $request->official_travel_origin,
                            'status' => 'draft',
                            'submitted_by_user' => true,
                            'traveler_id' => $administrationId,
                            'purpose' => $request->purpose,
                            'destination' => '',
                            'duration' => $request->duration,
                            'departure_from' => $request->departure_from,
                            'transportation_id' => $request->transportation_id,
                            'accommodation_id' => $request->accommodation_id,
                            'manual_approvers' => [],
                            'created_by' => $user->id,
                            'submit_at' => null,
                        ]);
                        $officialtravel->save();

                        $this->syncOfficialtravelPlannedStops($officialtravel, $stopDestinations, $stopDestinationManualFlags);

                        break;
                    } catch (QueryException $e) {
                        $isDuplicate = (int) ($e->errorInfo[1] ?? 0) === 1062
                            || str_contains($e->getMessage(), 'Duplicate entry');
                        if (! $isDuplicate || $attempt === $maxAttempts - 1) {
                            throw $e;
                        }
                    }
                }

                if (! $officialtravel) {
                    throw new \RuntimeException('Tidak dapat menghasilkan nomor LOT unik. Silakan coba lagi.');
                }

                if ($request->has('followers') && is_array($request->followers)) {
                    foreach (array_filter($request->followers) as $followerId) {
                        if ((int) $followerId !== (int) $administrationId) {
                            Officialtravel_detail::create([
                                'official_travel_id' => $officialtravel->id,
                                'follower_id' => $followerId,
                            ]);
                        }
                    }
                }

                // Create flight request from fr_data when "Need flight ticket?" was checked
                FlightRequest::createFromFrData($request, $officialtravel);

                DB::commit();
            } finally {
                if ($lockHeld) {
                    $this->releaseOfficialTravelReqSequenceLock();
                }
            }

            return redirect()
                ->route('officialtravels.my-travels')
                ->with('toast_success', 'Pengajuan perjalanan dinas berhasil dikirim. Menunggu konfirmasi HR untuk penetapan nomor surat.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();

            return redirect()->back()
                ->with('toast_error', 'Gagal mengajukan: '.$e->getMessage())
                ->withInput();
        }
    }
}
