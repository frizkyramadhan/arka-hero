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
        $this->middleware('permission:official-travels.recommend')->only('recommend');
        $this->middleware('permission:official-travels.approve')->only('approve');
        $this->middleware('permission:official-travels.stamp')->only(['arrivalStamp', 'departureStamp']);
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

        return view('officialtravels.index', compact('title', 'subtitle'));
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
            'approver'
        ])->orderBy('created_at', 'desc');

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
            ->filter(function ($instance) use ($request) {
                if (!empty($request->get('search'))) {
                    $instance->where(function ($w) use ($request) {
                        $search = $request->get('search');
                        $w->orWhere('official_travel_number', 'LIKE', "%$search%")
                            ->orWhere('destination', 'LIKE', "%$search%")
                            ->orWhereHas('traveler.employee', function ($q) use ($search) {
                                $q->where('fullname', 'LIKE', "%$search%")
                                    ->orWhere('nik', 'LIKE', "%$search%");
                            });
                    });
                }
            })
            ->addColumn('action', function ($model) {
                return view('officialtravels.action', compact('model'))->render();
            })
            ->rawColumns(['action', 'status', 'recommendation', 'approval'])
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
            ->whereMonth('created_at', now()->month)
            ->orderBy('id', 'desc')
            ->first();

        $sequence = $lastTravel ? (int)substr($lastTravel->official_travel_number, 6, 4) + 1 : 1;
        $romanMonth = $this->numberToRoman(now()->month);
        $travelNumber = sprintf("ARKA/B%04d/HR/%s/%s", $sequence, $romanMonth, now()->year);

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
        $title = 'Official Travels';
        $subtitle = 'Recommend Official Travel';
        $officialtravel->load(['traveler.employees', 'project', 'transportation', 'accommodation', 'details.follower.employees']);

        return view('officialtravels.recommend', compact('title', 'subtitle', 'officialtravel'));
    }

    /**
     * Process recommendation
     */
    public function recommend(Request $request, Officialtravel $officialtravel)
    {
        try {
            $this->validate($request, [
                'recommendation_status' => 'required|in:approved,rejected',
                'recommendation_remark' => 'required',
            ]);

            DB::beginTransaction();

            $officialtravel->update([
                'recommendation_status' => $request->recommendation_status,
                'recommendation_remark' => $request->recommendation_remark,
                'recommendation_by' => Auth::id(),
                'recommendation_date' => now(),
                'recommendation_timestamps' => now(),
            ]);

            DB::commit();

            return redirect('officialtravels')->with('toast_success', 'Official Travel recommendation processed successfully!');
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
        $title = 'Official Travels';
        $subtitle = 'Approve Official Travel';
        $officialtravel->load(['traveler.employees', 'project', 'transportation', 'accommodation', 'details.follower.employees', 'recommender']);

        if ($officialtravel->recommendation_status != 'approved') {
            return redirect()->back()->with('toast_error', 'Cannot approve Official Travel that has not been recommended.');
        }

        return view('officialtravels.approve', compact('title', 'subtitle', 'officialtravel'));
    }

    /**
     * Process approval
     */
    public function approve(Request $request, Officialtravel $officialtravel)
    {
        try {
            $this->validate($request, [
                'approval_status' => 'required|in:approved,rejected',
                'approval_remark' => 'required',
            ]);

            DB::beginTransaction();

            if ($officialtravel->recommendation_status != 'approved') {
                throw new \Exception('Cannot approve Official Travel that has not been recommended.');
            }

            $officialtravel->update([
                'approval_status' => $request->approval_status,
                'approval_remark' => $request->approval_remark,
                'approval_by' => Auth::id(),
                'approval_date' => now(),
                'approval_timestamps' => now(),
                'official_travel_status' => $request->approval_status == 'approved' ? 'open' : 'draft',
            ]);

            DB::commit();

            return redirect('officialtravels')->with('toast_success', 'Official Travel approval processed successfully!');
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
        $title = 'Official Travels';
        $subtitle = 'Arrival Stamp';
        $officialtravel->load(['traveler.employees', 'project', 'transportation', 'accommodation', 'details.follower.employees']);

        if ($officialtravel->official_travel_status != 'open') {
            return redirect()->back()->with('toast_error', 'Cannot stamp arrival for Official Travel that is not open.');
        }

        return view('officialtravels.arrival', compact('title', 'subtitle', 'officialtravel'));
    }

    /**
     * Process arrival stamp
     */
    public function arrivalStamp(Request $request, Officialtravel $officialtravel)
    {
        try {
            $this->validate($request, [
                'arrival_remark' => 'required',
            ]);

            DB::beginTransaction();

            if ($officialtravel->official_travel_status != 'open') {
                throw new \Exception('Cannot stamp arrival for Official Travel that is not open.');
            }

            $officialtravel->update([
                'arrival_check_by' => Auth::id(),
                'arrival_remark' => $request->arrival_remark,
                'arrival_timestamps' => now(),
            ]);

            DB::commit();

            return redirect('officialtravels')->with('toast_success', 'Official Travel arrival stamped successfully!');
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
        $title = 'Official Travels';
        $subtitle = 'Departure Stamp';
        $officialtravel->load(['traveler.employees', 'project', 'transportation', 'accommodation', 'details.follower.employees']);

        if ($officialtravel->official_travel_status != 'open' || !$officialtravel->arrival_check_by) {
            return redirect()->back()->with('toast_error', 'Cannot stamp departure before arrival is recorded.');
        }

        return view('officialtravels.departure', compact('title', 'subtitle', 'officialtravel'));
    }

    /**
     * Process departure stamp
     */
    public function departureStamp(Request $request, Officialtravel $officialtravel)
    {
        try {
            $this->validate($request, [
                'departure_remark' => 'required',
            ]);

            DB::beginTransaction();

            if ($officialtravel->official_travel_status != 'open' || !$officialtravel->arrival_check_by) {
                throw new \Exception('Cannot stamp departure before arrival is recorded.');
            }

            $officialtravel->update([
                'departure_check_by' => Auth::id(),
                'departure_remark' => $request->departure_remark,
                'departure_timestamps' => now(),
                'official_travel_status' => 'closed',
            ]);

            DB::commit();

            return redirect('officialtravels')->with('toast_success', 'Official Travel departure stamped and closed successfully!');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollback();
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('toast_error', 'Failed to stamp departure. ' . $e->getMessage())
                ->withInput();
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
