<?php

namespace App\Http\Controllers;

use App\Models\ApprovalStage;
use App\Models\ApprovalPlan;
use App\Models\Officialtravel;
use App\Models\RecruitmentRequest;
use App\Models\User;
use App\Models\Project;
use App\Models\Department;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

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
            'projects' => 'required|array|min:1',
            'departments' => 'required|array|min:1',
            'documents' => 'required|array|min:1',
        ]);

        $departments = Department::whereIn('id', $request->departments)->get();

        foreach ($departments as $department) {
            foreach ($request->projects as $project) {
                foreach ($request->documents as $document) {

                    // check for duplication
                    $check = ApprovalStage::where('department_id', $department->id)
                        ->where('approver_id', $request->approver_id)
                        ->where('project_id', $project)
                        ->where('document_type', $document)
                        ->first();

                    if ($check) {
                        continue;
                    }

                    ApprovalStage::create([
                        'department_id' => $department->id,
                        'approver_id' => $request->approver_id,
                        'project_id' => $project,
                        'document_type' => $document,
                    ]);
                }
            }
        }

        return redirect()->route('approval.stages.index')->with('toast_success', 'Approval stage created successfully.');
    }

    public function edit($id)
    {
        try {
            $title = 'Edit Approval Stage';
            $approvalStage = ApprovalStage::findOrFail($id);
            $approvers = User::select('id', 'name')->get();
            $projects = Project::orderBy('project_code', 'asc')->get();
            $departments = Department::orderBy('department_name', 'asc')->get();

            // Get all stages for this approver to pre-fill the form
            $approverStages = ApprovalStage::where('approver_id', $approvalStage->approver_id)->get();

            // Extract unique values for pre-filling
            $selectedProjects = $approverStages->pluck('project_id')->unique()->toArray();
            $selectedDepartments = $approverStages->pluck('department_id')->unique()->toArray();
            $selectedDocuments = $approverStages->pluck('document_type')->unique()->toArray();

            return view('approval-stages.edit', compact('title', 'approvalStage', 'approvers', 'projects', 'departments', 'selectedProjects', 'selectedDepartments', 'selectedDocuments'));
        } catch (\Exception $e) {
            return redirect()->route('approval.stages.index')->with('toast_error', 'Approval stage not found: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'approver_id' => 'required',
            'projects' => 'required|array|min:1',
            'departments' => 'required|array|min:1',
            'documents' => 'required|array|min:1',
        ]);

        $approvalStage = ApprovalStage::findOrFail($id);

        // Delete existing stages for this approver
        ApprovalStage::where('approver_id', $request->approver_id)->delete();

        $departments = Department::whereIn('id', $request->departments)->get();

        foreach ($departments as $department) {
            foreach ($request->projects as $project) {
                foreach ($request->documents as $document) {
                    ApprovalStage::create([
                        'department_id' => $department->id,
                        'approver_id' => $request->approver_id,
                        'project_id' => $project,
                        'document_type' => $document,
                    ]);
                }
            }
        }

        return redirect()->route('approval.stages.index')->with('toast_success', 'Approval stage updated successfully.');
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
                return redirect()->route('approval.stages.index')->with('toast_error', "Cannot delete approval stages for {$approverName}. This approver has {$activeApprovalPlans} active approval plan(s) that need to be processed first.");
            }

            // Check if there are any pending approval plans for this approver
            $pendingApprovalPlans = ApprovalPlan::where('approver_id', $approverId)
                ->where('status', 0) // pending status
                ->count();

            if ($pendingApprovalPlans > 0) {
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
                return redirect()->route('approval.stages.index')->with('toast_error', "Cannot delete approval stages for {$approverName}. There are {$totalSubmitted} document(s) currently submitted for approval that need to be processed first.");
            }

            // Delete all approval stages for this approver
            $deletedCount = ApprovalStage::where('approver_id', $approverId)->delete();

            return redirect()->route('approval.stages.index')->with('toast_success', "All approval stages for {$approverName} have been deleted successfully. ({$deletedCount} records deleted)");
        } catch (\Exception $e) {
            return redirect()->route('approval.stages.index')->with('toast_error', 'Failed to delete approval stages: ' . $e->getMessage());
        }
    }

    public function data()
    {
        // Get all users who have approval stages
        $approvers = User::select('id', 'name')
            ->whereHas('approval_stages')
            ->get();

        // If no approvers found, return empty result
        if ($approvers->isEmpty()) {
            return DataTables::of(collect([]))
                ->addColumn('approver', function () {
                    return '';
                })
                ->addColumn('projects', function () {
                    return '';
                })
                ->addColumn('departments', function () {
                    return '';
                })
                ->addColumn('documents', function () {
                    return '';
                })
                ->addIndexColumn()
                ->addColumn('action', function () {
                    return view('approval-stages.action', ['id' => 0])->render();
                })
                ->rawColumns(['action', 'approver', 'projects', 'departments', 'documents'])
                ->toJson();
        }

        return DataTables::of($approvers)
            ->addColumn('approver', function ($approver) {
                return '<span class="badge badge-primary">' . $approver->name . '</span>';
            })
            ->addColumn('projects', function ($approver) {
                $stages = ApprovalStage::where('approver_id', $approver->id)->with('project')->get();
                $projects = $stages->pluck('project.project_code')->unique()->sort();
                $html = '';
                foreach ($projects as $project) {
                    $html .= '<span class="badge badge-info mr-1 mb-1">' . $project . '</span>';
                }
                return $html;
            })
            ->addColumn('departments', function ($approver) {
                $stages = ApprovalStage::where('approver_id', $approver->id)->with('department')->get();
                $departments = $stages->pluck('department.department_name')->unique()->sort();
                $html = '';
                foreach ($departments as $department) {
                    $html .= '<span class="badge badge-success mr-1 mb-1">' . $department . '</span>';
                }
                return $html;
            })
            ->addColumn('documents', function ($approver) {
                $stages = ApprovalStage::where('approver_id', $approver->id)->get();
                $documents = $stages->pluck('document_type')->unique()->sort();
                $html = '';
                foreach ($documents as $document) {
                    $documentName = $document === 'officialtravel' ? 'Official Travel' : ucfirst(str_replace('_', ' ', $document));
                    $html .= '<span class="badge badge-warning mr-1 mb-1">' . $documentName . '</span>';
                }
                return $html;
            })
            ->addIndexColumn()
            ->addColumn('action', function ($approver) {
                // Get the first approval stage for this approver to use its ID
                $firstStage = ApprovalStage::where('approver_id', $approver->id)->first();
                $stageId = $firstStage ? $firstStage->id : 0;

                return view('approval-stages.action', ['id' => $stageId])->render();
            })
            ->rawColumns(['action', 'approver', 'projects', 'departments', 'documents'])
            ->toJson();
    }

    /**
     * Get approval preview for a specific project using logged-in user's department
     */
    public function preview(Request $request)
    {
        try {
            $request->validate([
                'project_id' => 'required|integer',
                'document_type' => 'required|string|in:officialtravel,recruitment_request'
            ]);

            // Get logged-in user's department
            $user = auth()->user();
            $userDepartment = $user->departments->first();

            if (!$userDepartment) {
                return response()->json([
                    'success' => false,
                    'message' => 'User has no department assigned'
                ], 404);
            }

            // Get approval stages for the specified criteria
            $approvalStages = ApprovalStage::with(['approver.departments'])
                ->where('project_id', $request->project_id)
                ->where('department_id', $userDepartment->id)
                ->where('document_type', $request->document_type)
                ->orderBy('id', 'asc')
                ->get();

            $approvers = $approvalStages->map(function ($stage) {
                $approverDepartment = $stage->approver->departments->first();
                return [
                    'id' => $stage->approver->id,
                    'name' => $stage->approver->name,
                    'department' => $approverDepartment ? $approverDepartment->department_name : 'No Department'
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
