<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Project;
use App\Models\Department;
use App\Models\ApprovalPlan;
use Illuminate\Http\Request;
use App\Models\ApprovalStage;
use App\Models\Officialtravel;
use App\Models\RecruitmentRequest;
use App\Models\ApprovalStageDetail;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Log;

class ApprovalStageController extends Controller
{
    public function index()
    {
        $title = 'Approval Stages';
        $approvers = User::select('id', 'name')->get();
        $projects = Project::orderBy('project_code', 'asc')->get();
        $departments = Department::orderBy('department_name', 'asc')->get();

        return view('approval-stages.index', compact('title', 'approvers', 'projects', 'departments'));
    }

    public function create()
    {
        $title = 'Create Approval Stage';
        $approvers = User::select('id', 'name')->get();
        $projects = Project::orderBy('project_code', 'asc')->get();
        $departments = Department::orderBy('department_name', 'asc')->get();

        return view('approval-stages.create', compact('title', 'approvers', 'projects', 'departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'approver_id' => 'required',
            'document_type' => 'required|string|in:officialtravel,recruitment_request',
            'approval_order' => 'required|integer|min:1',
            'projects' => 'required|array|min:1',
            'departments' => 'required|array|min:1'
        ]);

        // Check for existing combinations before creating
        $existingStage = ApprovalStage::where('approver_id', $request->approver_id)
            ->where('document_type', $request->document_type)
            ->where('approval_order', $request->approval_order)
            ->first();

        if ($existingStage) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['duplicate' => 'Approval stage with this approver, document type, and order already exists.']);
        }

        // Create approval stage
        $approvalStage = ApprovalStage::create([
            'approver_id' => $request->approver_id,
            'document_type' => $request->document_type,
            'approval_order' => $request->approval_order,
        ]);

        // Create details for each project-department combination
        $createdDetails = 0;
        foreach ($request->projects as $projectId) {
            foreach ($request->departments as $departmentId) {
                ApprovalStageDetail::create([
                    'approval_stage_id' => $approvalStage->id,
                    'project_id' => $projectId,
                    'department_id' => $departmentId
                ]);
                $createdDetails++;
            }
        }

        return redirect()->route('approval.stages.index')
            ->with('toast_success', "Approval stage created successfully with {$createdDetails} project-department combinations.");
    }

    public function edit($id)
    {
        try {
            $title = 'Edit Approval Stage';
            $approvalStage = ApprovalStage::with('details.project', 'details.department')->findOrFail($id);
            $approvers = User::select('id', 'name')->get();
            $projects = Project::orderBy('project_code', 'asc')->get();
            $departments = Department::orderBy('department_name', 'asc')->get();

            // Extract selected values from details
            $selectedProjects = $approvalStage->details->pluck('project_id')->unique()->toArray();
            $selectedDepartments = $approvalStage->details->pluck('department_id')->unique()->toArray();

            return view('approval-stages.edit', compact('title', 'approvalStage', 'approvers', 'projects', 'departments', 'selectedProjects', 'selectedDepartments'));
        } catch (\Exception $e) {
            return redirect()->route('approval.stages.index')->with('toast_error', 'Approval stage not found: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'approver_id' => 'required',
            'document_type' => 'required|string|in:officialtravel,recruitment_request',
            'approval_order' => 'required|integer|min:1',
            'projects' => 'required|array|min:1',
            'departments' => 'required|array|min:1'
        ]);

        $approvalStage = ApprovalStage::findOrFail($id);

        // Check for existing combinations before updating (excluding current stage)
        $existingStage = ApprovalStage::where('approver_id', $request->approver_id)
            ->where('document_type', $request->document_type)
            ->where('approval_order', $request->approval_order)
            ->where('id', '!=', $id)
            ->first();

        if ($existingStage) {
            return redirect()->back()
                ->withInput()
                ->withErrors(['duplicate' => 'Approval stage with this approver, document type, and order already exists.']);
        }

        // Check if approval stage fields have changed
        $stageChanged = $approvalStage->approver_id != $request->approver_id ||
            $approvalStage->document_type != $request->document_type ||
            $approvalStage->approval_order != $request->approval_order;

        // Check if project/department combinations have changed
        $currentProjects = $approvalStage->details->pluck('project_id')->unique()->sort()->toArray();
        $currentDepartments = $approvalStage->details->pluck('department_id')->unique()->sort()->toArray();
        $requestProjects = collect($request->projects)->sort()->toArray();
        $requestDepartments = collect($request->departments)->sort()->toArray();

        $detailsChanged = $currentProjects != $requestProjects || $currentDepartments != $requestDepartments;

        // Update approval stage if changed
        if ($stageChanged) {
            $approvalStage->update([
                'approver_id' => $request->approver_id,
                'document_type' => $request->document_type,
                'approval_order' => $request->approval_order,
            ]);
        }

        // Update details only if changed
        if ($detailsChanged) {
            // Remove old details and create new ones
            $approvalStage->details()->delete();

            $updatedDetails = 0;
            foreach ($request->projects as $projectId) {
                foreach ($request->departments as $departmentId) {
                    ApprovalStageDetail::create([
                        'approval_stage_id' => $approvalStage->id,
                        'project_id' => $projectId,
                        'department_id' => $departmentId
                    ]);
                    $updatedDetails++;
                }
            }
        }

        // Prepare success message
        $message = 'Approval stage updated successfully';
        if ($stageChanged && $detailsChanged) {
            $message .= " with {$updatedDetails} project-department combinations";
        } elseif ($stageChanged) {
            $message .= " (stage configuration updated)";
        } elseif ($detailsChanged) {
            $message .= " with {$updatedDetails} project-department combinations";
        } else {
            $message .= " (no changes detected)";
        }

        return redirect()->route('approval.stages.index')
            ->with('toast_success', $message);
    }

    public function destroy($id)
    {
        try {
            // Find the approval stage to get the approver_id
            $approvalStage = ApprovalStage::findOrFail($id);
            $approverId = $approvalStage->approver_id;

            // Get approver name for error/success message
            $approverName = User::find($approverId)->name ?? 'Unknown';

            // Check if there are any active approval plans for this approver
            $activeApprovalPlans = ApprovalPlan::where('approver_id', $approverId)
                ->where('is_open', true)
                ->count();

            if ($activeApprovalPlans > 0) {
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => "Cannot delete approval stages for {$approverName}. This approver has {$activeApprovalPlans} active approval plan(s) that need to be processed first."
                    ], 400);
                }
                return redirect()->route('approval.stages.index')->with('toast_error', "Cannot delete approval stages for {$approverName}. This approver has {$activeApprovalPlans} active approval plan(s) that need to be processed first.");
            }

            // Check if there are any pending approval plans for this approver
            $pendingApprovalPlans = ApprovalPlan::where('approver_id', $approverId)
                ->where('status', 0) // pending status
                ->count();

            if ($pendingApprovalPlans > 0) {
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => "Cannot delete approval stages for {$approverName}. This approver has {$pendingApprovalPlans} pending approval plan(s) that need to be processed first."
                    ], 400);
                }
                return redirect()->route('approval.stages.index')->with('toast_error', "Cannot delete approval stages for {$approverName}. This approver has {$pendingApprovalPlans} pending approval plan(s) that need to be processed first.");
            }

            // Check if there are any documents submitted for approval (officialtravels)
            $submittedOfficialTravels = Officialtravel::where('status', 'submitted')
                ->whereNotNull('submit_at')
                ->whereNull('approved_at')
                ->count();

            // Check if there are any documents submitted for approval (recruitment_requests)
            $submittedRecruitmentRequests = RecruitmentRequest::where('status', 'submitted')
                ->whereNotNull('submit_at')
                ->whereNull('approved_at')
                ->count();

            if ($submittedOfficialTravels > 0 || $submittedRecruitmentRequests > 0) {
                $totalSubmitted = $submittedOfficialTravels + $submittedRecruitmentRequests;
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => "Cannot delete approval stages for {$approverName}. There are {$totalSubmitted} document(s) currently submitted for approval that need to be processed first."
                    ], 400);
                }
                return redirect()->route('approval.stages.index')->with('toast_error', "Cannot delete approval stages for {$approverName}. There are {$totalSubmitted} document(s) currently submitted for approval that need to be processed first.");
            }

            // Delete all approval stages for this approver (details will be deleted via cascade)
            $deletedCount = ApprovalStage::where('approver_id', $approverId)->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "All approval stages for {$approverName} have been deleted successfully. ({$deletedCount} records deleted)",
                    'deleted_count' => $deletedCount
                ]);
            }

            return redirect()->route('approval.stages.index')->with('toast_success', "All approval stages for {$approverName} have been deleted successfully. ({$deletedCount} records deleted)");
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete approval stages: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->route('approval.stages.index')->with('toast_error', 'Failed to delete approval stages: ' . $e->getMessage());
        }
    }

    public function data()
    {
        // Get all approval stages with relationships
        $stages = ApprovalStage::with(['approver', 'details.project', 'details.department'])
            ->orderBy('approver_id', 'asc')
            ->orderBy('document_type', 'asc')
            ->orderBy('approval_order', 'asc')
            ->get();

        // If no stages found, return empty result
        if ($stages->isEmpty()) {
            return DataTables::of(collect([]))
                ->addColumn('approver', function () {
                    return '';
                })
                ->addColumn('document_type', function () {
                    return '';
                })
                ->addColumn('approval_order', function () {
                    return '';
                })
                ->addColumn('projects', function () {
                    return '';
                })
                ->addColumn('departments', function () {
                    return '';
                })
                ->addIndexColumn()
                ->addColumn('action', function () {
                    return view('approval-stages.action', ['id' => 0])->render();
                })
                ->rawColumns(['action', 'approver', 'document_type', 'projects', 'departments', 'approval_order'])
                ->toJson();
        }

        // Group stages by approver and document type, then sort properly
        $groupedData = $stages->groupBy(['approver_id', 'document_type'])->map(function ($approverGroups) {
            return $approverGroups->map(function ($documentGroups) {
                return $documentGroups->first(); // Take first stage from each group
            });
        })->flatten(1)->sortBy(function ($item) {
            // Sort by document_type ASC first, then by approval_order ASC
            return $item->document_type . '_' . str_pad($item->approval_order, 3, '0', STR_PAD_LEFT);
        });

        return DataTables::of($groupedData)
            ->addColumn('approver', function ($stage) {
                return $stage->approver->name;
            })
            ->addColumn('document_type', function ($stage) {
                $documentName = $stage->document_type === 'officialtravel' ? 'Official Travel' : ucfirst(str_replace('_', ' ', $stage->document_type));
                return '<span class="badge badge-warning">' . $documentName . '</span>';
            })
            ->addColumn('projects', function ($stage) {
                // Get all projects from details
                $projects = $stage->details->pluck('project.project_code')->unique()->sort();
                $html = '';
                foreach ($projects as $project) {
                    $html .= '<span class="badge badge-info mr-1 mb-1">' . $project . '</span>';
                }
                return $html;
            })
            ->addColumn('departments', function ($stage) {
                // Get all departments from details
                $departments = $stage->details->pluck('department.department_name')->unique()->sort();
                $html = '';
                foreach ($departments as $department) {
                    $html .= '<span class="badge badge-success mr-1 mb-1">' . $department . '</span>';
                }
                return $html;
            })
            ->addColumn('approval_order', function ($stage) {
                $orderClass = 'badge-secondary'; // Default to secondary
                $html = '<span class="badge ' . $orderClass . ' mr-1 mb-1" title="' . ucfirst(str_replace('_', ' ', $stage->document_type)) . '">';
                $html .= $stage->approval_order;
                $html .= '</span>';
                return $html;
            })
            ->addIndexColumn()
            ->addColumn('action', function ($stage) {
                return view('approval-stages.action', ['id' => $stage->id])->render();
            })
            ->rawColumns(['action', 'approver', 'document_type', 'projects', 'departments', 'approval_order'])
            ->toJson();
    }

    /**
     * Get approval preview for a specific project and department
     */
    public function preview(Request $request)
    {
        try {
            $request->validate([
                'project_id' => 'required|integer',
                'document_type' => 'required|string|in:officialtravel,recruitment_request',
                'department_id' => 'nullable|integer|exists:departments,id'
            ]);

            // For recruitment_request, use department_id from request
            // For officialtravel, use department_id from request (main traveler's department)
            $departmentId = null;

            if ($request->document_type === 'recruitment_request') {
                if ($request->has('department_id')) {
                    $departmentId = $request->department_id;
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Department ID is required for recruitment request'
                    ], 400);
                }
            } elseif ($request->document_type === 'officialtravel') {
                if ($request->has('department_id')) {
                    $departmentId = $request->department_id;
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Department ID is required for official travel (main traveler department)'
                    ], 400);
                }
            }

            // Debug logging
            Log::info("Searching for approval stages with criteria:", [
                'project_id' => $request->project_id,
                'department_id' => $departmentId,
                'document_type' => $request->document_type
            ]);

            // Get approval stages for the specified criteria with order
            $approvalStages = ApprovalStage::with(['approver.departments', 'details' => function ($query) use ($request, $departmentId) {
                $query->where('project_id', $request->project_id)
                    ->where('department_id', $departmentId);
            }])
                ->where('document_type', $request->document_type)
                ->whereHas('details', function ($query) use ($request, $departmentId) {
                    $query->where('project_id', $request->project_id)
                        ->where('department_id', $departmentId);
                })
                ->orderBy('approval_order', 'asc')
                ->get();

            // Debug logging
            Log::info("Found approval stages:", [
                'count' => $approvalStages->count(),
                'stages' => $approvalStages->toArray()
            ]);

            $approvers = $approvalStages->map(function ($stage) {
                return [
                    'id' => $stage->approver->id,
                    'name' => $stage->approver->name,
                    'department' => $stage->approver->departments->first()->department_name ?? 'No Department',
                    'order' => $stage->approval_order,
                ];
            });

            return response()->json([
                'success' => true,
                'approvers' => $approvers
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load approval preview: ' . $e->getMessage()
            ], 500);
        }
    }
}
