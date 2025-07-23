<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Accommodation;
use App\Models\Administration;
use App\Models\Officialtravel;
use App\Models\Transportation;
use App\Models\Officialtravel_detail;
use App\Models\LetterNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Spatie\Activitylog\Facades\LogActivity;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

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
        $this->middleware('permission:official-travels.recommend')->only(['showRecommendForm', 'recommend']);
        $this->middleware('permission:official-travels.approve')->only(['showApprovalForm', 'approve']);
    }

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
            'recommender',
            'approver',
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
            $officialtravels->where('official_travel_status', $request->get('status'));
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
                if ($officialtravel->official_travel_status == 'draft') {
                    return '<span class="badge badge-secondary">Draft</span>';
                } elseif ($officialtravel->official_travel_status == 'open') {
                    return '<span class="badge badge-primary">Open</span>';
                } elseif ($officialtravel->official_travel_status == 'closed') {
                    return '<span class="badge badge-success">Closed</span>';
                } elseif ($officialtravel->official_travel_status == 'canceled') {
                    return '<span class="badge badge-danger">Canceled</span>';
                }
            })
            ->addColumn('recommendation', function ($officialtravel) {
                if ($officialtravel->recommendation_status == 'pending') {
                    return '<span class="badge badge-warning">Pending</span>';
                } elseif ($officialtravel->recommendation_status == 'approved') {
                    return '<span class="badge badge-success">Approved</span>';
                } elseif ($officialtravel->recommendation_status == 'rejected') {
                    return '<span class="badge badge-danger">Rejected</span>';
                }
                return '<span class="badge badge-secondary">-</span>';
            })

            ->addColumn('created_by', function ($officialtravel) {
                $creator = '<small>' . $officialtravel->creator->name . '</small>';
                return $creator;
            })
            ->addColumn('action', function ($model) {
                return view('officialtravels.action', compact('model'))->render();
            })
            ->rawColumns(['action', 'status', 'recommendation', 'created_by'])
            ->toJson();
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
                'recommender',
                'approver',
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
                $query->where('official_travel_status', $request->get('status'));
            }

            if (!empty($request->get('recommendation'))) {
                $query->where('recommendation_status', $request->get('recommendation'));
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
                        'Recommendation',
                        'Recommend By',
                        'Recommend Date',

                        'Approve By',
                        'Approve Date',
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

                    $status = match ($officialtravel->official_travel_status) {
                        'draft' => 'Draft',
                        'open' => 'Open',
                        'closed' => 'Closed',
                        'canceled' => 'Canceled',
                        default => '-'
                    };

                    $recommendation = match ($officialtravel->recommendation_status) {
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                        default => '-'
                    };

                    $recommendBy = $officialtravel->recommender ? $officialtravel->recommender->name : '-';
                    $recommendDate = $officialtravel->recommendation_date ?
                        date('d/m/Y H:i', strtotime($officialtravel->recommendation_date)) : '-';



                    $approveBy = $officialtravel->approver ? $officialtravel->approver->name : '-';
                    $approveDate = $officialtravel->approval_date ?
                        date('d/m/Y H:i', strtotime($officialtravel->approval_date)) : '-';

                    // Arrival information
                    $arrivalDate = $officialtravel->arrival_at_destination ?
                        date('d/m/Y H:i', strtotime($officialtravel->arrival_at_destination)) : '-';
                    $arrivalChecker = $officialtravel->arrivalChecker ? $officialtravel->arrivalChecker->name : '-';
                    $arrivalRemarks = $officialtravel->arrival_remark ?? '-';

                    // Departure information
                    $departureDate = $officialtravel->departure_from_destination ?
                        date('d/m/Y H:i', strtotime($officialtravel->departure_from_destination)) : '-';
                    $departureChecker = $officialtravel->departureChecker ? $officialtravel->departureChecker->name : '-';
                    $departureRemarks = $officialtravel->departure_remark ?? '-';

                    return [
                        $officialtravel->official_travel_number,
                        date('d/m/Y', strtotime($officialtravel->official_travel_date)),
                        $travelerName,
                        $project,
                        $officialtravel->destination,
                        $status,
                        $recommendation,
                        $recommendBy,
                        $recommendDate,

                        $approveBy,
                        $approveDate,
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

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = 'Official Travels';
        $subtitle = 'Add Official Travel (LOT)';
        $projects = Project::where('project_status', 1)->get();
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

        // Get recommenders for recommendation
        $recommenders = User::permission('official-travels.recommend')
            ->select('id', 'name')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name
                ];
            });

        // Get approvers for approval
        $approvers = User::permission('official-travels.approve')
            ->select('id', 'name')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name
                ];
            });

        return view('officialtravels.create', compact(
            'title',
            'subtitle',
            'projects',
            'accommodations',
            'transportations',
            'employees',
            'recommenders',
            'approvers',
            'travelNumber',
            'romanMonth'
        ));
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
                // Recommendation fields
                'recommendation_by' => 'required|exists:users,id',
                'recommendation_remark' => 'nullable|string',
                // Approval fields
                'approval_by' => 'required|exists:users,id',
                'approval_remark' => 'nullable|string',
                // Letter numbering integration fields
                'number_option' => 'nullable|in:existing',
                'letter_number_id' => 'nullable|exists:letter_numbers,id',
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
                'recommendation_by.required' => 'Recommender is required.',
                'recommendation_by.exists' => 'Selected Recommender is invalid.',
                'approval_by.required' => 'Approver is required.',
                'approval_by.exists' => 'Selected Approver is invalid.',
                'letter_number_id.exists' => 'Selected Letter Number is invalid.',
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

            // Create new official travel
            $officialtravel = new Officialtravel([
                'letter_number_id' => $letterNumberId,
                'letter_number' => $letterNumberString,
                'official_travel_number' => $travelNumber,
                'official_travel_date' => $request->official_travel_date,
                'official_travel_origin' => $request->official_travel_origin,
                'official_travel_status' => 'draft',
                'traveler_id' => $request->traveler_id,
                'purpose' => $request->purpose,
                'destination' => $request->destination,
                'duration' => $request->duration,
                'departure_from' => $request->departure_from,
                'transportation_id' => $request->transportation_id,
                'accommodation_id' => $request->accommodation_id,
                'recommendation_by' => $request->recommendation_by,
                'recommendation_remark' => $request->recommendation_remark,
                'recommendation_status' => 'pending',
                'approval_by' => $request->approval_by,
                'approval_remark' => $request->approval_remark,
                'approval_status' => 'pending',
                'created_by' => auth()->id(),
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



            DB::commit();

            $message = 'Official Travel created successfully!';
            if ($letterNumberString) {
                $message .= ' Letter Number: ' . $letterNumberString . ' (Status changed to Used)';
            }
            $message .= ' LOT Number: ' . $travelNumber;

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
            'project',
            'transportation',
            'accommodation',
            'details.follower.employee',
            'arrivalChecker',
            'departureChecker',
            'recommender',
            'approver'
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
        $projects = Project::where('project_status', 1)->get();
        $accommodations = Accommodation::where('accommodation_status', 1)->get();
        $transportations = Transportation::where('transportation_status', 1)->get();
        $officialtravel->load(['details']);

        // Load employees with their relationships
        $employees = Administration::with([
            'employee',
            'position.department',
            'project'
        ])->get()->map(function ($employee) {
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

        // Get recommenders for recommendation
        $recommenders = User::permission('official-travels.recommend')
            ->select('id', 'name')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name
                ];
            });

        // Get approvers for approval
        $approvers = User::permission('official-travels.approve')
            ->select('id', 'name')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name
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
            'recommenders',
            'approvers'
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
                // Recommendation fields
                'recommendation_by' => 'required|exists:users,id',
                'recommendation_remark' => 'nullable|string',
                // Approval fields
                'approval_by' => 'required|exists:users,id',
                'approval_remark' => 'nullable|string',
            ]);

            DB::beginTransaction();

            // Only allow editing if the status is draft
            if ($officialtravel->official_travel_status != 'draft') {
                throw new \Exception('Cannot edit Official Travel that is not in draft status.');
            }

            // Update the official travel
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
                'recommendation_by' => $request->recommendation_by,
                'recommendation_remark' => $request->recommendation_remark,
                'approval_by' => $request->approval_by,
                'approval_remark' => $request->approval_remark,
                'arrival_at_destination' => $request->arrival_at_destination,
                'arrival_remark' => $request->arrival_remark ?? $officialtravel->arrival_remark,
                'departure_from_destination' => $request->departure_from_destination,
                'departure_remark' => $request->departure_remark ?? $officialtravel->departure_remark,
            ]);

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
            if ($officialtravel->official_travel_status != 'draft') {
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
     * Show recommendation form
     */
    public function showRecommendForm($id)
    {
        $officialtravel = Officialtravel::with([
            'traveler.employee',
            'project',
            'transportation',
            'accommodation',
            'details.follower.employee'
        ])->findOrFail($id);

        // Check if user can recommend
        if (!auth()->user()->can('official-travels.recommend')) {
            return redirect()->back()->with('toast_error', 'You do not have permission to recommend this official travel');
        }

        // Check if user is assigned as recommender
        if (auth()->id() != $officialtravel->recommendation_by) {
            return redirect()->back()->with('toast_error', 'You are not assigned as recommender for this official travel');
        }

        // Check if already recommended
        if ($officialtravel->recommendation_status != 'pending') {
            return redirect()->back()->with('toast_error', 'This official travel has already been recommended');
        }

        $title = 'Official Travels';
        $subtitle = 'Recommend Official Travel';

        return view('officialtravels.recommend', compact('title', 'subtitle', 'officialtravel'));
    }

    /**
     * Process recommendation
     */
    public function recommend(Request $request, $id)
    {
        try {
            $officialtravel = Officialtravel::findOrFail($id);

            // Check if user can recommend
            if (!auth()->user()->can('official-travels.recommend')) {
                return redirect()->back()->with('toast_error', 'You do not have permission to recommend this official travel');
            }

            // Check if user is assigned as recommender
            if (auth()->id() != $officialtravel->recommendation_by) {
                return redirect()->back()->with('toast_error', 'You are not assigned as recommender for this official travel');
            }

            // Check if already recommended
            if ($officialtravel->recommendation_status != 'pending') {
                return redirect()->back()->with('toast_error', 'This official travel has already been recommended');
            }

            $this->validate($request, [
                'recommendation_status' => 'required|in:approved,rejected',
                'recommendation_remark' => 'required|string|min:3',
            ]);

            DB::beginTransaction();

            $officialtravel->update([
                'recommendation_status' => $request->recommendation_status,
                'recommendation_remark' => $request->recommendation_remark,
                'recommendation_date' => now(),
                'recommendation_timestamps' => now(),
            ]);

            DB::commit();

            $status = $request->recommendation_status == 'approved' ? 'recommended' : 'rejected';
            return redirect()->route('officialtravels.show', $officialtravel->id)
                ->with('toast_success', "Official travel has been {$status} successfully");
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('toast_error', 'Failed to process recommendation. ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Show approval form
     */
    public function showApprovalForm($id)
    {
        $officialtravel = Officialtravel::with([
            'traveler.employee',
            'project',
            'transportation',
            'accommodation',
            'details.follower.employee'
        ])->findOrFail($id);

        // Check if user can approve
        if (!auth()->user()->can('official-travels.approve')) {
            return redirect()->back()->with('toast_error', 'You do not have permission to approve this official travel');
        }

        // Check if user is assigned as approver
        if (auth()->id() != $officialtravel->approval_by) {
            return redirect()->back()->with('toast_error', 'You are not assigned as approver for this official travel');
        }

        // Check if recommendation is approved
        if ($officialtravel->recommendation_status != 'approved') {
            return redirect()->back()->with('toast_error', 'Cannot approve official travel that has not been recommended');
        }

        // Check if already approved
        if ($officialtravel->approval_status != 'pending') {
            return redirect()->back()->with('toast_error', 'This official travel has already been approved');
        }

        $title = 'Official Travels';
        $subtitle = 'Approve Official Travel';

        return view('officialtravels.approve', compact('title', 'subtitle', 'officialtravel'));
    }

    /**
     * Process approval
     */
    public function approve(Request $request, $id)
    {
        try {
            $officialtravel = Officialtravel::findOrFail($id);

            // Check if user can approve
            if (!auth()->user()->can('official-travels.approve')) {
                return redirect()->back()->with('toast_error', 'You do not have permission to approve this official travel');
            }

            // Check if user is assigned as approver
            if (auth()->id() != $officialtravel->approval_by) {
                return redirect()->back()->with('toast_error', 'You are not assigned as approver for this official travel');
            }

            // Check if recommendation is approved
            if ($officialtravel->recommendation_status != 'approved') {
                return redirect()->back()->with('toast_error', 'Cannot approve official travel that has not been recommended');
            }

            // Check if already approved
            if ($officialtravel->approval_status != 'pending') {
                return redirect()->back()->with('toast_error', 'This official travel has already been approved');
            }

            $this->validate($request, [
                'approval_status' => 'required|in:approved,rejected',
                'approval_remark' => 'required|string|min:3',
            ]);

            DB::beginTransaction();

            $officialtravel->update([
                'approval_status' => $request->approval_status,
                'approval_remark' => $request->approval_remark,
                'approval_date' => now(),
                'approval_timestamps' => now(),
                'official_travel_status' => $request->approval_status == 'approved' ? 'open' : 'draft',
            ]);

            DB::commit();

            $status = $request->approval_status == 'approved' ? 'approved' : 'rejected';
            return redirect()->route('officialtravels.show', $officialtravel->id)
                ->with('toast_success', "Official travel has been {$status} successfully");
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('toast_error', 'Failed to process approval. ' . $e->getMessage())
                ->withInput();
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
            'details.follower.employee'
        ])->findOrFail($id);

        // Cek status official travel
        if ($officialtravel->official_travel_status != 'open') {
            return redirect()->back()->with('toast_error', 'Cannot stamp arrival for Official Travel that is not open');
        }



        // Cek apakah arrival sudah pernah direcord
        if ($officialtravel->arrival_check_by) {
            return redirect()->back()->with('toast_error', 'Arrival has already been stamped for this Official Travel');
        }

        $title = 'Official Travels';
        $subtitle = 'Arrival Stamp';

        return view('officialtravels.arrival', compact('title', 'subtitle', 'officialtravel'));
    }

    /**
     * Process arrival stamp
     */
    public function arrivalStamp(Request $request, Officialtravel $officialtravel)
    {
        try {
            // Cek status official travel
            if ($officialtravel->official_travel_status != 'open') {
                return redirect()->back()->with('toast_error', 'Cannot stamp arrival for Official Travel that is not open');
            }

            // Cek apakah arrival sudah pernah direcord
            if ($officialtravel->arrival_check_by) {
                return redirect()->back()->with('toast_error', 'Arrival has already been stamped for this Official Travel');
            }

            $this->validate($request, [
                'arrival_at_destination' => 'required|date',
                'arrival_remark' => 'required|string',
            ]);

            DB::beginTransaction();

            $officialtravel->update([
                'arrival_at_destination' => $request->arrival_at_destination,
                'arrival_check_by' => Auth::id(),
                'arrival_remark' => $request->arrival_remark,
                'arrival_timestamps' => now(),
            ]);

            DB::commit();

            return redirect('officialtravels/' . $officialtravel->id)->with('toast_success', 'Official Travel arrival stamped successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('toast_error', 'Failed to stamp arrival. ' . $e->getMessage())
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
            'details.follower.employee'
        ])->findOrFail($id);

        // Cek status official travel
        if ($officialtravel->official_travel_status != 'open') {
            return redirect()->back()->with('toast_error', 'Cannot stamp departure for Official Travel that is not open');
        }

        // Cek apakah arrival sudah direcord
        if (!$officialtravel->arrival_check_by) {
            return redirect()->back()->with('toast_error', 'Cannot stamp departure before arrival is recorded');
        }

        // Cek apakah departure sudah pernah direcord
        if ($officialtravel->departure_check_by) {
            return redirect()->back()->with('toast_error', 'Departure has already been stamped for this Official Travel');
        }

        $title = 'Official Travels';
        $subtitle = 'Departure Stamp';

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
                'details.follower.employee'
            ])->findOrFail($id);

            // Cek status official travel
            if ($officialtravel->official_travel_status != 'open') {
                return redirect()->back()->with('toast_error', 'Cannot stamp departure for Official Travel that is not open');
            }

            // Cek apakah arrival sudah direcord
            if (!$officialtravel->arrival_check_by) {
                return redirect()->back()->with('toast_error', 'Cannot stamp departure before arrival is recorded');
            }

            // Cek apakah departure sudah pernah direcord
            if ($officialtravel->departure_check_by) {
                return redirect()->back()->with('toast_error', 'Departure has already been stamped for this Official Travel');
            }

            $request->validate([
                'departure_from_destination' => 'required|date',
                'departure_remark' => 'required|string'
            ]);

            DB::beginTransaction();

            $officialtravel->update([
                'departure_from_destination' => $request->departure_from_destination,
                'departure_remark' => $request->departure_remark,
                'departure_check_by' => auth()->id(),
                'departure_timestamps' => now()
            ]);

            DB::commit();

            return redirect()
                ->route('officialtravels.show', $officialtravel->id)
                ->with('toast_success', 'Departure has been confirmed successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('toast_error', 'Failed to stamp departure. ' . $e->getMessage())
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
            'arrivalChecker',
            'departureChecker',
            'recommender'
        ])->findOrFail($id);

        return view('officialtravels.print', compact('title', 'subtitle', 'officialtravel'));
    }

    /**
     * Cancel approval
     */


    /**
     * Close the official travel
     */
    public function close(Officialtravel $officialtravel)
    {
        try {
            // Validasi status
            if ($officialtravel->official_travel_status != 'open') {
                return redirect()->back()->with('toast_error', 'Only open official travels can be closed');
            }

            // Validasi arrival dan departure
            if (!$officialtravel->arrival_check_by || !$officialtravel->departure_check_by) {
                return redirect()->back()->with('toast_error', 'Cannot close official travel before arrival and departure are recorded');
            }

            DB::beginTransaction();

            $officialtravel->update([
                'official_travel_status' => 'closed'
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
     * Test Letter Number Integration (for debugging)
     */
    public function testLetterNumberIntegration()
    {
        // Get letter numbers with status reserved
        $reservedLetters = LetterNumber::where('status', 'reserved')
            ->where('category_code', 'B')
            ->with(['category', 'subject'])
            ->get();

        // Get official travels with letter numbers
        $officialtravelsWithLetters = Officialtravel::whereNotNull('letter_number_id')
            ->with(['letterNumber'])
            ->get();

        return response()->json([
            'reserved_letter_numbers' => $reservedLetters->map(function ($letter) {
                return [
                    'id' => $letter->id,
                    'letter_number' => $letter->letter_number,
                    'status' => $letter->status,
                    'subject' => $letter->subject->subject_name ?? 'No Subject',
                    'created_at' => $letter->created_at
                ];
            }),
            'official_travels_with_letters' => $officialtravelsWithLetters->map(function ($travel) {
                return [
                    'id' => $travel->id,
                    'official_travel_number' => $travel->official_travel_number,
                    'letter_number_id' => $travel->letter_number_id,
                    'letter_number' => $travel->letter_number,
                    'letter_number_status' => $travel->letterNumber ? $travel->letterNumber->status : 'No Letter Number',
                    'created_at' => $travel->created_at
                ];
            })
        ]);
    }



    /**
     * Get overdue approvals
     */






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
}
