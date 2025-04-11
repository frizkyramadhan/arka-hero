<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Accommodation;
use App\Models\Administration;
use App\Models\Officialtravel;
use App\Models\Transportation;
use App\Models\Officialtravel_detail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Spatie\Activitylog\Facades\LogActivity;

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
        $this->middleware('permission:official-travels.recommend')->only(['showRecommendForm', 'recommend']);
        $this->middleware('permission:official-travels.approve')->only(['showApprovalForm', 'approve']);
        $this->middleware('permission:official-travels.stamp')->only(['showArrivalForm', 'showDepartureForm']);
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

        // Filter by recommendation status
        if (!empty($request->get('recommendation'))) {
            $officialtravels->where('recommendation_status', $request->get('recommendation'));
        }

        // Filter by approval status
        if (!empty($request->get('approval'))) {
            $officialtravels->where('approval_status', $request->get('approval'));
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
                $badge = '';
                if ($officialtravel->recommendation_status == 'pending') {
                    $badge = '<span class="badge badge-warning">Pending</span>';
                } elseif ($officialtravel->recommendation_status == 'approved') {
                    $badge = '<span class="badge badge-success">Approved</span>';
                } elseif ($officialtravel->recommendation_status == 'rejected') {
                    $badge = '<span class="badge badge-danger">Rejected</span>';
                }

                $recommender = $officialtravel->recommender ? '<br><small>' . $officialtravel->recommender->name . '</small>' : '';
                return $badge . $recommender;
            })
            ->addColumn('approval', function ($officialtravel) {
                $badge = '';
                if ($officialtravel->approval_status == 'pending') {
                    $badge = '<span class="badge badge-warning">Pending</span>';
                } elseif ($officialtravel->approval_status == 'approved') {
                    $badge = '<span class="badge badge-success">Approved</span>';
                } elseif ($officialtravel->approval_status == 'rejected') {
                    $badge = '<span class="badge badge-danger">Rejected</span>';
                }

                $approver = $officialtravel->approver ? '<br><small>' . $officialtravel->approver->name . '</small>' : '';
                return $badge . $approver;
            })
            ->addColumn('created_by', function ($officialtravel) {
                $creator = '<small>' . $officialtravel->creator->name . '</small>';
                return $creator;
            })
            ->addColumn('action', function ($model) {
                return view('officialtravels.action', compact('model'))->render();
            })
            ->rawColumns(['action', 'status', 'recommendation', 'approval', 'created_by'])
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
        $subtitle = 'Add Official Travel';
        $projects = Project::where('project_status', 1)->get();
        $accommodations = Accommodation::where('accommodation_status', 1)->get();
        $transportations = Transportation::where('transportation_status', 1)->get();

        // Generate Travel Number
        $lastTravel = Officialtravel::whereYear('created_at', now()->year)
            ->orderBy('created_at', 'desc')
            ->first();

        $sequence = $lastTravel ? (int)substr($lastTravel->official_travel_number, 6, 4) + 1 : 1;
        $romanMonth = $this->numberToRoman(now()->month);
        $travelNumber = sprintf("ARKA/B%04d/HR/%s/%s", $sequence, $romanMonth, now()->year);

        // Load employees with their relationships
        $employees = Administration::with([
            'employee',
            'position.department',
            'project'
        ])->orderBy('nik', 'asc')->get()->map(function ($employee) {
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

        // Get users with recommend and approve permissions
        $recommenders = User::permission('official-travels.recommend')
            ->select('id', 'name')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name
                ];
            });

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
            'travelNumber'
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
                'official_travel_number' => 'required|unique:officialtravels,official_travel_number',
                'official_travel_date' => 'required|date',
                'official_travel_origin' => 'required|exists:projects,id',
                'traveler_id' => 'required|exists:administrations,id',
                'purpose' => 'required|string',
                'destination' => 'required|string',
                'duration' => 'required|string',
                'departure_from' => 'required|date',
                'transportation_id' => 'required|exists:transportations,id',
                'accommodation_id' => 'required|exists:accommodations,id',
                'followers' => 'nullable|array',
                'followers.*' => 'exists:administrations,id',
                'recommender_id' => 'required|exists:users,id',
                'approver_id' => 'required|exists:users,id|different:recommender_id',
            ]);

            DB::beginTransaction();

            // Create new official travel
            $officialtravel = Officialtravel::create([
                'official_travel_number' => $request->official_travel_number,
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
                'recommendation_status' => 'pending',
                'recommendation_by' => $request->recommender_id,
                'recommendation_remark' => null,
                'recommendation_date' => null,
                'approval_status' => 'pending',
                'approval_by' => $request->approver_id,
                'approval_remark' => null,
                'approval_date' => null,
                'created_by' => auth()->id(),
            ]);

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

            return redirect('officialtravels')->with('toast_success', 'Official Travel created successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
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
     * @param  \App\Models\Officialtravel  $officialtravel
     * @return \Illuminate\Http\Response
     */
    public function show(Officialtravel $officialtravel)
    {
        $title = 'Official Travels';
        $subtitle = 'Official Travel Details';
        $officialtravel->load(['traveler.employee', 'project', 'transportation', 'accommodation', 'details.follower.employee', 'arrivalChecker', 'departureChecker', 'recommender', 'approver']);

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

        // Get users with recommend and approve permissions
        $recommenders = User::permission('official-travels.recommend')
            ->select('id', 'name')
            ->get()
            ->map(function ($user) {
                return [
                    'id' => $user->id,
                    'name' => $user->name
                ];
            });

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

        return view('officialtravels.edit', compact('title', 'subtitle', 'officialtravel', 'projects', 'accommodations', 'transportations', 'employees', 'selectedFollowers', 'recommenders', 'approvers'));
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
                'arrival_at_destination' => $request->arrival_at_destination,
                'arrival_remark' => $request->arrival_remark ?? $officialtravel->arrival_remark,
                'departure_at_destination' => $request->departure_at_destination,
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
    public function showRecommendForm(Officialtravel $officialtravel)
    {
        // Cek permission
        if (!auth()->user()->can('official-travels.recommend')) {
            return redirect()->back()->with('toast_error', 'You do not have permission to modify recommendations');
        }

        // Cek apakah approval sudah diproses
        if ($officialtravel->approval_status !== 'pending') {
            return redirect()->back()->with('toast_error', 'Cannot modify recommendation after approval has been processed');
        }

        // Cek apakah user adalah recommender yang ditugaskan
        if (auth()->id() !== $officialtravel->recommendation_by) {
            return redirect()->back()->with('toast_error', 'You are not assigned as the recommender for this travel request');
        }

        // Lanjutkan dengan kode yang ada
        $title = 'Official Travels';
        $subtitle = 'Recommend Official Travel';
        $officialtravel->load(['traveler.employee', 'project', 'transportation', 'accommodation', 'details.follower.employee', 'arrivalChecker', 'departureChecker', 'recommender', 'approver']);

        return view('officialtravels.recommend', compact('title', 'subtitle', 'officialtravel'));
    }

    /**
     * Process recommendation
     */
    public function recommend(Request $request, Officialtravel $officialtravel)
    {
        try {
            // Check if travel can be recommended
            if ($officialtravel->recommendation_status !== 'pending' && $officialtravel->approval_status !== 'pending') {
                return redirect()->back()
                    ->with('toast_error', 'Cannot change recommendation after approval has been processed.');
            }

            // Validate request
            $this->validate($request, [
                'recommendation_status' => 'required|in:approved,rejected',
                'recommendation_remark' => 'required|string',
                'recommendation_date' => 'required|date_format:Y-m-d\TH:i',
            ], [
                'recommendation_status.required' => 'Please select whether to approve or reject this travel request.',
                'recommendation_status.in' => 'Invalid recommendation status.',
                'recommendation_remark.required' => 'Please provide remarks for your recommendation.',
                'recommendation_remark.min' => 'Recommendation remarks must be at least 10 characters.',
                'recommendation_date.required' => 'Please select the recommendation date.',
                'recommendation_date.date_format' => 'The recommendation date format is invalid.',
            ]);

            DB::beginTransaction();

            $officialtravel->update([
                'recommendation_status' => $request->recommendation_status,
                'recommendation_remark' => $request->recommendation_remark,
                'recommendation_date' => $request->recommendation_date,
                'recommendation_timestamps' => now(),
                'official_travel_status' => $request->recommendation_status === 'rejected' ? 'canceled' : 'draft'
            ]);

            DB::commit();

            $status = $request->recommendation_status === 'approved' ? 'approved' : 'rejected';
            return redirect('officialtravels/' . $officialtravel->id)
                ->with('toast_success', "Official Travel has been {$status} successfully!");
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
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
    public function showApprovalForm(Officialtravel $officialtravel)
    {
        // Cek permission
        if (!auth()->user()->can('official-travels.approve')) {
            return redirect()->back()->with('toast_error', 'You do not have permission to modify approvals');
        }

        // Cek apakah arrival sudah direcord
        if ($officialtravel->arrival_at_destination) {
            return redirect()->back()->with('toast_error', 'Cannot modify approval after arrival has been recorded');
        }

        // Cek apakah user adalah approver yang ditugaskan
        if (auth()->id() !== $officialtravel->approval_by) {
            return redirect()->back()->with('toast_error', 'You are not assigned as the approver for this travel request');
        }

        // Cek status recommendation
        if ($officialtravel->recommendation_status !== 'approved') {
            return redirect()->back()->with('toast_error', 'Cannot approve travel request that has not been recommended');
        }

        // Check if travel is rejected
        if ($officialtravel->approval_status === 'rejected') {
            return redirect()->back()
                ->with('toast_error', 'This travel request has already been rejected and cannot be modified.');
        }

        // Lanjutkan dengan kode yang ada
        $title = 'Official Travels';
        $subtitle = 'Approve Official Travel';
        $officialtravel->load(['traveler.employee', 'project', 'transportation', 'accommodation', 'details.follower.employee', 'arrivalChecker', 'departureChecker', 'recommender', 'approver']);

        return view('officialtravels.approve', compact('title', 'subtitle', 'officialtravel'));
    }

    /**
     * Process approval
     */
    public function approve(Request $request, Officialtravel $officialtravel)
    {
        try {
            // Check if arrival has been recorded
            if ($officialtravel->arrival_at_destination) {
                return redirect()->back()
                    ->with('toast_error', 'Cannot modify approval after arrival has been recorded.');
            }

            // Check if travel is rejected
            if ($officialtravel->approval_status === 'rejected') {
                return redirect()->back()
                    ->with('toast_error', 'This travel request has already been rejected and cannot be modified.');
            }

            // Check if recommendation is approved
            if ($officialtravel->recommendation_status !== 'approved') {
                return redirect()->back()
                    ->with('toast_error', 'Cannot approve travel request that has not been recommended or was rejected.');
            }

            // Validate request
            $this->validate($request, [
                'approval_status' => 'required|in:approved,rejected',
                'approval_remark' => 'required|string',
                'approval_date' => 'required|date_format:Y-m-d\TH:i',
            ], [
                'approval_status.required' => 'Please select whether to approve or reject this travel request.',
                'approval_status.in' => 'Invalid approval status.',
                'approval_remark.required' => 'Please provide remarks for your approval.',
                'approval_remark.min' => 'Approval remarks must be at least 10 characters.',
                'approval_date.required' => 'Please select the approval date.',
                'approval_date.date_format' => 'The approval date format is invalid.',
            ]);

            DB::beginTransaction();

            $officialtravel->update([
                'approval_status' => $request->approval_status,
                'approval_remark' => $request->approval_remark,
                'approval_by' => Auth::id(),
                'approval_date' => $request->approval_date,
                'approval_timestamps' => now(),
                'official_travel_status' => $request->approval_status === 'approved' ? 'open' : 'canceled'
            ]);

            DB::commit();

            $status = $request->approval_status === 'approved' ? 'approved' : 'rejected';
            return redirect('officialtravels/' . $officialtravel->id)
                ->with('toast_success', "Official Travel has been {$status} successfully!");
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
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
    public function showArrivalForm(Officialtravel $officialtravel)
    {
        // Cek status official travel
        if ($officialtravel->official_travel_status != 'open') {
            return redirect()->back()->with('toast_error', 'Cannot stamp arrival for Official Travel that is not open');
        }

        // Cek apakah sudah disetujui
        if ($officialtravel->approval_status !== 'approved') {
            return redirect()->back()->with('toast_error', 'Cannot stamp arrival for Official Travel that has not been approved');
        }

        // Cek apakah arrival sudah pernah direcord
        if ($officialtravel->arrival_check_by) {
            return redirect()->back()->with('toast_error', 'Arrival has already been stamped for this Official Travel');
        }

        $title = 'Official Travels';
        $subtitle = 'Arrival Stamp';
        $officialtravel->load(['traveler.employee', 'project', 'transportation', 'accommodation', 'details.follower.employee']);

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
    public function showDepartureForm(Officialtravel $officialtravel)
    {
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
        $officialtravel->load(['traveler.employee', 'project', 'transportation', 'accommodation', 'details.follower.employee']);

        return view('officialtravels.departure', compact('title', 'subtitle', 'officialtravel'));
    }

    /**
     * Process departure stamp
     */
    public function departureStamp(Request $request, $id)
    {
        try {
            $officialtravel = Officialtravel::findOrFail($id);

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
                'departure_at_destination' => 'required|date',
                'departure_remark' => 'required|string'
            ]);

            DB::beginTransaction();

            $officialtravel->update([
                'departure_at_destination' => $request->departure_at_destination,
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

    public function print(Officialtravel $officialtravel)
    {
        $title = 'Official Travels';
        $subtitle = 'Official Travel Details';
        $officialtravel->load(['traveler.employee', 'project', 'transportation', 'accommodation', 'details.follower.employee', 'arrivalChecker', 'departureChecker', 'recommender', 'approver']);

        return view('officialtravels.print', compact('title', 'subtitle', 'officialtravel'));
    }

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
