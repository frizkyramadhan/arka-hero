<?php

namespace App\Http\Controllers;

use App\Models\ManPowerPlan;
use App\Models\ManPowerPlanDetail;
use App\Models\Project;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class ManPowerPlanController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:mpp.show')->only('index', 'show', 'getData');
        $this->middleware('permission:mpp.create')->only('create', 'store');
        $this->middleware('permission:mpp.edit')->only('edit', 'update', 'close');
        $this->middleware('permission:mpp.delete')->only('destroy');
    }

    /**
     * Display a listing of MPP
     */
    public function index(Request $request)
    {
        $projects = Project::all();
        $years = range(date('Y'), date('Y') - 5);

        $title = 'Man Power Plan (MPP)';
        $subtitle = 'List of Man Power Plans';

        return view('recruitment.mpp.index', compact('projects', 'years', 'title', 'subtitle'));
    }

    /**
     * Get all MPP for DataTables
     */
    public function getData(Request $request)
    {
        $query = ManPowerPlan::with(['project', 'creator', 'details']);

        // Apply filters
        if ($request->filled('mpp_number')) {
            $query->where('mpp_number', 'LIKE', "%{$request->mpp_number}%");
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('year')) {
            $query->whereYear('created_at', $request->year);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $query->orderBy('created_at', 'desc');

        return datatables()->of($query)
            ->addIndexColumn()
            ->addColumn('mpp_number', function ($mpp) {
                return $mpp->mpp_number;
            })
            ->addColumn('project_name', function ($mpp) {
                return $mpp->project->project_code ?? '-';
            })
            ->addColumn('title', function ($mpp) {
                return $mpp->title;
            })
            ->addColumn('total_positions_needed', function ($mpp) {
                return $mpp->getTotalPositionsNeeded();
            })
            ->addColumn('total_existing', function ($mpp) {
                return $mpp->getTotalExisting();
            })
            ->addColumn('total_diff', function ($mpp) {
                $diff = $mpp->getTotalDiff();
                $class = $diff > 0 ? 'text-danger' : ($diff < 0 ? 'text-success' : 'text-muted');
                return '<span class="' . $class . '">' . ($diff > 0 ? '+' : '') . $diff . '</span>';
            })
            ->addColumn('completion', function ($mpp) {
                $percentage = $mpp->getCompletionPercentage();
                $class = $percentage >= 100 ? 'bg-success' : ($percentage >= 50 ? 'bg-warning' : 'bg-danger');
                return '<div class="progress">
                    <div class="progress-bar ' . $class . '" role="progressbar" style="width: ' . $percentage . '%">
                        ' . $percentage . '%
                    </div>
                </div>';
            })
            ->addColumn('status', function ($mpp) {
                $class = $mpp->status === 'active' ? 'badge-success' : 'badge-secondary';
                return '<span class="badge ' . $class . '">' . ucfirst($mpp->status) . '</span>';
            })
            ->addColumn('action', function ($mpp) {
                $viewUrl = route('recruitment.mpp.show', $mpp->id);
                $editUrl = route('recruitment.mpp.edit', $mpp->id);
                $deleteUrl = route('recruitment.mpp.destroy', $mpp->id);

                $btn = '<div class="btn-group">';
                $btn .= '<a href="' . $viewUrl . '" class="btn btn-sm btn-info mr-1" title="View"><i class="fas fa-eye"></i></a>';

                if ($mpp->status === 'active') {
                    $btn .= '<a href="' . $editUrl . '" class="btn btn-sm btn-primary mr-1" title="Edit"><i class="fas fa-edit"></i></a>';
                }

                $btn .= '<button type="button" class="btn btn-sm btn-danger btn-delete" data-url="' . $deleteUrl . '" title="Delete"><i class="fas fa-trash"></i></button>';
                $btn .= '</div>';

                return $btn;
            })
            ->rawColumns(['total_diff', 'completion', 'status', 'action'])
            ->make(true);
    }

    /**
     * Show the form for creating a new MPP
     */
    public function create()
    {
        $projects = Project::all();
        // Order positions by department name ASC, then position name ASC (same as detail view)
        $positions = Position::with('department')
            ->where('position_status', 1)
            ->join('departments', 'positions.department_id', '=', 'departments.id')
            ->select('positions.*')
            ->orderBy('departments.department_name', 'asc')
            ->orderBy('positions.position_name', 'asc')
            ->get();

        $title = 'Create Man Power Plan';
        $subtitle = 'Create new MPP document';

        return view('recruitment.mpp.create', compact('projects', 'positions', 'title', 'subtitle'));
    }

    /**
     * Store a newly created MPP in storage
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'details' => 'required|array|min:1',
            'details.*.position_id' => 'required|exists:positions,id',
            'details.*.qty_unit' => 'nullable|integer|min:0',
            'details.*.existing_qty_s' => 'required|integer|min:0',
            'details.*.existing_qty_ns' => 'required|integer|min:0',
            'details.*.plan_qty_s' => 'required|integer|min:0',
            'details.*.plan_qty_ns' => 'required|integer|min:0',
            'details.*.remarks' => 'nullable|string',
            'details.*.requires_theory_test' => 'nullable|boolean',
            'details.*.agreement_type' => 'required|in:pkwt,pkwtt,magang,harian',
        ]);

        DB::beginTransaction();
        try {
            // Create MPP
            $mpp = ManPowerPlan::create([
                'project_id' => $validated['project_id'],
                'title' => $validated['title'],
                'description' => $validated['description'],
                'status' => ManPowerPlan::STATUS_ACTIVE,
                'created_by' => Auth::id(),
            ]);

            // Create MPP Details
            foreach ($validated['details'] as $detail) {
                $mpp->details()->create([
                    'position_id' => $detail['position_id'],
                    'qty_unit' => $detail['qty_unit'] ?? 0,
                    'existing_qty_s' => $detail['existing_qty_s'],
                    'existing_qty_ns' => $detail['existing_qty_ns'],
                    'plan_qty_s' => $detail['plan_qty_s'],
                    'plan_qty_ns' => $detail['plan_qty_ns'],
                    'remarks' => $detail['remarks'] ?? null,
                    'requires_theory_test' => isset($detail['requires_theory_test']) && $detail['requires_theory_test'] == '1',
                    'agreement_type' => $detail['agreement_type'] ?? 'pkwt',
                ]);
            }

            DB::commit();

            return redirect()->route('recruitment.mpp.show', $mpp->id)->with('toast_success', 'MPP created successfully');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error creating MPP: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            return redirect()->back()
                ->withInput()
                ->with('toast_error', 'Failed to create MPP: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified MPP
     */
    public function show($id)
    {
        $mpp = ManPowerPlan::with([
            'project',
            'creator',
            'details.position.department',
            'details.sessions.candidate',
            'details.sessions.fptk',
        ])->findOrFail($id);

        // Sort details by department name ASC, then position name ASC
        $mpp->details = $mpp->details->sortBy(function ($detail) {
            $deptName = $detail->position && $detail->position->department
                ? $detail->position->department->department_name
                : '';
            $positionName = $detail->position ? $detail->position->position_name : '';
            return $deptName . '|' . $positionName;
        })->values();

        $title = 'MPP Details';
        $subtitle = $mpp->mpp_number;

        return view('recruitment.mpp.show', compact('mpp', 'title', 'subtitle'));
    }

    /**
     * Show the form for editing the specified MPP
     */
    public function edit($id)
    {
        $mpp = ManPowerPlan::with([
            'details.position.department',
            'details.sessions'
        ])->findOrFail($id);

        if ($mpp->status === ManPowerPlan::STATUS_CLOSED) {
            return redirect()->route('recruitment.mpp.show', $id)->with('toast_warning', 'Cannot edit closed MPP');
        }

        // Sort details by department name ASC, then position name ASC (same as show method)
        $mpp->details = $mpp->details->sortBy(function ($detail) {
            $deptName = $detail->position && $detail->position->department
                ? $detail->position->department->department_name
                : '';
            $positionName = $detail->position ? $detail->position->position_name : '';
            return $deptName . '|' . $positionName;
        })->values();

        $projects = Project::all();
        // Order positions by department name ASC, then position name ASC (same as detail view)
        $positions = Position::with('department')
            ->where('position_status', 1)
            ->join('departments', 'positions.department_id', '=', 'departments.id')
            ->select('positions.*')
            ->orderBy('departments.department_name', 'asc')
            ->orderBy('positions.position_name', 'asc')
            ->get();

        $title = 'Edit Man Power Plan';
        $subtitle = $mpp->mpp_number;

        return view('recruitment.mpp.edit', compact('mpp', 'projects', 'positions', 'title', 'subtitle'));
    }

    /**
     * Update the specified MPP in storage
     */
    public function update(Request $request, $id)
    {
        $mpp = ManPowerPlan::findOrFail($id);

        if ($mpp->status === ManPowerPlan::STATUS_CLOSED) {
            return redirect()->route('recruitment.mpp.show', $id)->with('toast_warning', 'Cannot update closed MPP');
        }

        // Filter out empty detail entries before validation
        // Only keep details that have position_id (required field)
        $details = $request->input('details', []);
        $filteredDetails = array_filter($details, function ($detail) {
            return !empty($detail['position_id']);
        });

        // Re-index array to ensure sequential keys (0, 1, 2, ...)
        $filteredDetails = array_values($filteredDetails);

        // Replace details in request for validation
        $request->merge(['details' => $filteredDetails]);

        $validated = $request->validate([
            'project_id' => 'required|exists:projects,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'details' => 'required|array|min:1',
            'details.*.id' => 'nullable|exists:man_power_plan_details,id',
            'details.*.position_id' => 'required|exists:positions,id',
            'details.*.qty_unit' => 'nullable|integer|min:0',
            'details.*.existing_qty_s' => 'required|integer|min:0',
            'details.*.existing_qty_ns' => 'required|integer|min:0',
            'details.*.plan_qty_s' => 'required|integer|min:0',
            'details.*.plan_qty_ns' => 'required|integer|min:0',
            'details.*.remarks' => 'nullable|string',
            'details.*.requires_theory_test' => 'nullable|boolean',
            'details.*.agreement_type' => 'required|in:pkwt,pkwtt,magang,harian',
        ]);

        DB::beginTransaction();
        try {
            // Update MPP header
            $mpp->update([
                'project_id' => $validated['project_id'],
                'title' => $validated['title'],
                'description' => $validated['description'],
            ]);

            // Track existing detail IDs
            $existingDetailIds = [];

            // Update or create details
            foreach ($validated['details'] as $detail) {
                if (!empty($detail['id'])) {
                    // Update existing detail
                    $mppDetail = ManPowerPlanDetail::findOrFail($detail['id']);
                    $mppDetail->update([
                        'position_id' => $detail['position_id'],
                        'qty_unit' => $detail['qty_unit'] ?? 0,
                        'existing_qty_s' => $detail['existing_qty_s'],
                        'existing_qty_ns' => $detail['existing_qty_ns'],
                        'plan_qty_s' => $detail['plan_qty_s'],
                        'plan_qty_ns' => $detail['plan_qty_ns'],
                        'remarks' => $detail['remarks'] ?? null,
                        'requires_theory_test' => isset($detail['requires_theory_test']) && $detail['requires_theory_test'] == '1',
                        'agreement_type' => $detail['agreement_type'] ?? 'pkwt',
                    ]);
                    $existingDetailIds[] = $detail['id'];
                } else {
                    // Create new detail
                    $newDetail = $mpp->details()->create([
                        'position_id' => $detail['position_id'],
                        'qty_unit' => $detail['qty_unit'] ?? 0,
                        'existing_qty_s' => $detail['existing_qty_s'],
                        'existing_qty_ns' => $detail['existing_qty_ns'],
                        'plan_qty_s' => $detail['plan_qty_s'],
                        'plan_qty_ns' => $detail['plan_qty_ns'],
                        'remarks' => $detail['remarks'] ?? null,
                        'requires_theory_test' => isset($detail['requires_theory_test']) && $detail['requires_theory_test'] == '1',
                        'agreement_type' => $detail['agreement_type'] ?? 'pkwt',
                    ]);
                    $existingDetailIds[] = $newDetail->id;
                }
            }

            // Delete details that are not in the update (only if they don't have sessions)
            $mpp->details()
                ->whereNotIn('id', $existingDetailIds)
                ->whereDoesntHave('sessions')
                ->delete();

            DB::commit();

            return redirect()->route('recruitment.mpp.show', $mpp->id)->with('toast_success', 'MPP updated successfully');
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error updating MPP: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all(),
                'mpp_id' => $id
            ]);
            return redirect()->back()
                ->withInput()
                ->with('toast_error', 'Failed to update MPP: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified MPP from storage
     */
    public function destroy($id)
    {
        try {
            $mpp = ManPowerPlan::findOrFail($id);

            // Check if any detail has sessions
            $hasSessions = $mpp->details()->has('sessions')->exists();
            if ($hasSessions) {
                return redirect()->back()->with('toast_error', 'Cannot delete MPP with existing recruitment sessions');
            }

            $mpp->delete();

            return redirect()->route('recruitment.mpp.index')->with('toast_success', 'MPP deleted successfully');
        } catch (Exception $e) {
            Log::error('Error deleting MPP: ' . $e->getMessage());
            return redirect()->back()->with('toast_error', 'Failed to delete MPP');
        }
    }

    /**
     * Close the specified MPP
     */
    public function close($id)
    {
        try {
            $mpp = ManPowerPlan::findOrFail($id);

            if ($mpp->status === ManPowerPlan::STATUS_CLOSED) {
                return redirect()->route('recruitment.mpp.show', $id)->with('toast_warning', 'MPP is already closed');
            }

            $mpp->close();

            return redirect()->route('recruitment.mpp.show', $id)->with('toast_success', 'MPP closed successfully');
        } catch (Exception $e) {
            Log::error('Error closing MPP: ' . $e->getMessage());
            return redirect()->back()->with('toast_error', 'Failed to close MPP');
        }
    }
}
