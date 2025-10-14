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
    /**
     * Get display label for request reason
     */
    private function getRequestReasonLabel($reason)
    {
        return match ($reason) {
            // New detailed reasons
            'replacement_resign' => 'Replacement - Resign, Termination, End of Contract',
            'replacement_promotion' => 'Replacement - Promotion, Mutation, Demotion',
            'additional_workplan' => 'Additional - Workplan',
            'other' => 'Other',
            default => 'Unknown'
        };
    }

    public function index()
    {
        $title = 'Approval Stages';
        $approvers = User::select('id', 'name')->orderBy('name', 'asc')->get();
        $projects = Project::where('project_status', 1)->orderBy('project_code', 'asc')->get();
        $departments = Department::where('department_status', 1)->orderBy('department_name', 'asc')->get();

        return view('approval-stages.index', compact('title', 'approvers', 'projects', 'departments'));
    }

    public function create()
    {
        $title = 'Create Approval Stage';
        $approvers = User::select('id', 'name')->orderBy('name', 'asc')->get();
        $projects = Project::where('project_status', 1)->orderBy('project_code', 'asc')->get();
        $departments = Department::where('department_status', 1)->orderBy('department_name', 'asc')->get();

        return view('approval-stages.create', compact('title', 'approvers', 'projects', 'departments'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'approver_id' => 'required',
            'document_type' => 'required|string|in:officialtravel,recruitment_request,leave_request',
            'approval_order' => 'required|integer|min:1',
            'projects' => 'required|array|min:1',
            'departments' => 'required|array|min:1',
            'request_reasons' => 'nullable|array',
            'request_reasons.*' => 'string|in:replacement_resign,replacement_promotion,additional_workplan,other'
        ]);

        // Check for existing combinations before creating
        $requestReasons = $request->request_reasons ?? [];

        // Handle request_reason logic based on document type
        if (empty($requestReasons)) {
            if ($request->document_type === 'recruitment_request') {
                $requestReasons = [null]; // For backward compatibility
            } elseif ($request->document_type === 'officialtravel') {
                $requestReasons = [null]; // officialtravel doesn't use request_reason, set to null
            } elseif ($request->document_type === 'leave_request') {
                $requestReasons = [null]; // leave_request doesn't use request_reason, set to null
            } else {
                $requestReasons = [null]; // Default fallback
            }
        }

        // Check for duplicate combinations at the detail level
        $duplicateDetails = [];
        foreach ($request->projects as $projectId) {
            foreach ($request->departments as $departmentId) {
                foreach ($requestReasons as $requestReason) {
                    // Check if this exact combination already exists
                    $existingDetail = ApprovalStageDetail::whereHas('approvalStage', function ($query) use ($request) {
                        $query->where('approver_id', $request->approver_id)
                            ->where('document_type', $request->document_type)
                            ->where('approval_order', $request->approval_order);
                    })
                        ->where('project_id', $projectId)
                        ->where('department_id', $departmentId)
                        ->where('request_reason', $requestReason)
                        ->with(['approvalStage.approver', 'project', 'department'])
                        ->first();

                    if ($existingDetail) {
                        $duplicateDetails[] = [
                            'project' => $existingDetail->project,
                            'department' => $existingDetail->department,
                            'approver' => $existingDetail->approvalStage->approver,
                            'request_reason' => $requestReason
                        ];
                    }
                }
            }
        }

        if (!empty($duplicateDetails)) {
            $errorMessage = "Duplicate configuration detected! The following combinations already exist:<br><ul>";
            foreach ($duplicateDetails as $detail) {
                $errorMessage .= "<li><strong>Project:</strong> {$detail['project']->project_code}, ";
                $errorMessage .= "<strong>Department:</strong> {$detail['department']->department_name}, ";
                $errorMessage .= "<strong>Approver:</strong> {$detail['approver']->name}";
                if ($detail['request_reason']) {
                    $reasonLabel = $this->getRequestReasonLabel($detail['request_reason']);
                    $errorMessage .= ", <strong>Request Reason:</strong> {$reasonLabel}";
                }
                $errorMessage .= "</li>";
            }
            $errorMessage .= "</ul>Please choose different combinations or modify the existing approval stage.";

            return redirect()->back()
                ->withInput()
                ->withErrors(['duplicate' => $errorMessage]);
        }

        try {
            // Create approval stage
            $approvalStage = ApprovalStage::create([
                'approver_id' => $request->approver_id,
                'document_type' => $request->document_type,
                'approval_order' => $request->approval_order,
            ]);

            // Create details for each project-department combination
            $createdDetails = 0;
            $requestReasons = $request->request_reasons ?? [];

            // Handle request_reason logic based on document type
            if (empty($requestReasons)) {
                if ($request->document_type === 'recruitment_request') {
                    $requestReasons = [null]; // For backward compatibility
                } elseif ($request->document_type === 'officialtravel') {
                    $requestReasons = [null]; // officialtravel doesn't use request_reason, set to null
                } elseif ($request->document_type === 'leave_request') {
                    $requestReasons = [null]; // leave_request doesn't use request_reason, set to null
                } else {
                    $requestReasons = [null]; // Default fallback
                }
            }

            foreach ($request->projects as $projectId) {
                foreach ($request->departments as $departmentId) {
                    foreach ($requestReasons as $requestReason) {
                        ApprovalStageDetail::create([
                            'approval_stage_id' => $approvalStage->id,
                            'project_id' => $projectId,
                            'department_id' => $departmentId,
                            'request_reason' => $requestReason
                        ]);
                        $createdDetails++;
                    }
                }
            }
        } catch (\Illuminate\Database\QueryException $e) {
            // Handle duplicate entry error
            if ($e->getCode() == 23000 && str_contains($e->getMessage(), 'unique_stage_detail')) {
                // Extract the duplicate key information
                preg_match("/Duplicate entry '([^']+)' for key/", $e->getMessage(), $matches);
                $duplicateKey = $matches[1] ?? 'unknown';

                // Parse the duplicate key to get meaningful information
                $keyParts = explode('-', $duplicateKey);
                $duplicateStageId = $keyParts[0] ?? 'unknown';
                $duplicateProjectId = $keyParts[1] ?? 'unknown';
                $duplicateDepartmentId = $keyParts[2] ?? 'unknown';

                // Get the existing stage details for better error message
                $existingDetail = ApprovalStageDetail::with(['approvalStage.approver', 'project', 'department'])
                    ->where('approval_stage_id', $duplicateStageId)
                    ->where('project_id', $duplicateProjectId)
                    ->where('department_id', $duplicateDepartmentId)
                    ->first();

                if ($existingDetail) {
                    $errorMessage = "Duplicate configuration detected! ";
                    $errorMessage .= "The combination of ";
                    $errorMessage .= "<strong>Project: {$existingDetail->project->project_code}</strong>, ";
                    $errorMessage .= "<strong>Department: {$existingDetail->department->department_name}</strong> ";
                    $errorMessage .= "already exists for ";
                    $errorMessage .= "<strong>{$existingDetail->approvalStage->approver->name}</strong> ";
                    $errorMessage .= "in the approval stage configuration.";

                    if ($request->document_type === 'recruitment_request' && $existingDetail->request_reason) {
                        $reasonLabel = $this->getRequestReasonLabel($existingDetail->request_reason);
                        $errorMessage .= " (Request Reason: <strong>{$reasonLabel}</strong>)";
                    }
                } else {
                    $errorMessage = "Duplicate configuration detected! This combination of project, department, and approver already exists in the approval stage configuration.";
                }

                return redirect()->back()
                    ->withInput()
                    ->withErrors(['duplicate' => $errorMessage]);
            }

            // Re-throw other database errors
            throw $e;
        }

        return redirect()->route('approval.stages.index')
            ->with('toast_success', "Approval stage created successfully with {$createdDetails} project-department combinations.");
    }

    public function edit($id)
    {
        try {
            $title = 'Edit Approval Stage';
            $approvalStage = ApprovalStage::with('details.project', 'details.department')->findOrFail($id);
            $approvers = User::select('id', 'name')->orderBy('name', 'asc')->get();
            $projects = Project::where('project_status', 1)->orderBy('project_code', 'asc')->get();
            $departments = Department::where('department_status', 1)->orderBy('department_name', 'asc')->get();

            // Extract selected values from details
            $selectedProjects = $approvalStage->details->pluck('project_id')->unique()->toArray();
            $selectedDepartments = $approvalStage->details->pluck('department_id')->unique()->toArray();
            $selectedRequestReasons = $approvalStage->details->pluck('request_reason')->filter()->unique()->toArray();

            return view('approval-stages.edit', compact('title', 'approvalStage', 'approvers', 'projects', 'departments', 'selectedProjects', 'selectedDepartments', 'selectedRequestReasons'));
        } catch (\Exception $e) {
            return redirect()->route('approval.stages.index')->with('toast_error', 'Approval stage not found: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'approver_id' => 'required',
            'document_type' => 'required|string|in:officialtravel,recruitment_request,leave_request',
            'approval_order' => 'required|integer|min:1',
            'projects' => 'required|array|min:1',
            'departments' => 'required|array|min:1',
            'request_reasons' => 'nullable|array',
            'request_reasons.*' => 'string|in:replacement_resign,replacement_promotion,additional_workplan,other'
        ]);

        $approvalStage = ApprovalStage::findOrFail($id);

        // Check for existing combinations before updating (excluding current stage)
        $requestReasons = $request->request_reasons ?? [];

        // Handle request_reason logic based on document type
        if (empty($requestReasons)) {
            if ($request->document_type === 'recruitment_request') {
                $requestReasons = [null]; // For backward compatibility
            } elseif ($request->document_type === 'officialtravel') {
                $requestReasons = [null]; // officialtravel doesn't use request_reason, set to null
            } elseif ($request->document_type === 'leave_request') {
                $requestReasons = [null]; // leave_request doesn't use request_reason, set to null
            } else {
                $requestReasons = [null]; // Default fallback
            }
        }

        // Check for duplicate combinations at the detail level
        $duplicateDetails = [];
        foreach ($request->projects as $projectId) {
            foreach ($request->departments as $departmentId) {
                foreach ($requestReasons as $requestReason) {
                    // Check if this exact combination already exists (excluding current stage)
                    $existingDetail = ApprovalStageDetail::whereHas('approvalStage', function ($query) use ($request, $id) {
                        $query->where('approver_id', $request->approver_id)
                            ->where('document_type', $request->document_type)
                            ->where('approval_order', $request->approval_order)
                            ->where('id', '!=', $id);
                    })
                        ->where('project_id', $projectId)
                        ->where('department_id', $departmentId)
                        ->where('request_reason', $requestReason)
                        ->with(['approvalStage.approver', 'project', 'department'])
                        ->first();

                    if ($existingDetail) {
                        $duplicateDetails[] = [
                            'project' => $existingDetail->project,
                            'department' => $existingDetail->department,
                            'approver' => $existingDetail->approvalStage->approver,
                            'request_reason' => $requestReason
                        ];
                    }
                }
            }
        }

        if (!empty($duplicateDetails)) {
            $errorMessage = "Duplicate configuration detected! The following combinations already exist:<br><ul>";
            foreach ($duplicateDetails as $detail) {
                $errorMessage .= "<li><strong>Project:</strong> {$detail['project']->project_code}, ";
                $errorMessage .= "<strong>Department:</strong> {$detail['department']->department_name}, ";
                $errorMessage .= "<strong>Approver:</strong> {$detail['approver']->name}";
                if ($detail['request_reason']) {
                    $reasonLabel = $this->getRequestReasonLabel($detail['request_reason']);
                    $errorMessage .= ", <strong>Request Reason:</strong> {$reasonLabel}";
                }
                $errorMessage .= "</li>";
            }
            $errorMessage .= "</ul>Please choose different combinations or modify the existing approval stage.";

            return redirect()->back()
                ->withInput()
                ->withErrors(['duplicate' => $errorMessage]);
        }

        // Check if approval stage fields have changed
        $stageChanged = $approvalStage->approver_id != $request->approver_id ||
            $approvalStage->document_type != $request->document_type ||
            $approvalStage->approval_order != $request->approval_order;

        // Check if project/department/request_reason combinations have changed
        $currentProjects = $approvalStage->details->pluck('project_id')->unique()->sort()->toArray();
        $currentDepartments = $approvalStage->details->pluck('department_id')->unique()->sort()->toArray();
        $currentRequestReasons = $approvalStage->details->pluck('request_reason')->filter()->unique()->sort()->toArray();
        $requestProjects = collect($request->projects)->sort()->toArray();
        $requestDepartments = collect($request->departments)->sort()->toArray();
        $requestRequestReasons = collect($request->request_reasons ?? [])->sort()->toArray();

        $detailsChanged = $currentProjects != $requestProjects ||
            $currentDepartments != $requestDepartments ||
            $currentRequestReasons != $requestRequestReasons;

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
            try {
                // Remove old details and create new ones
                $approvalStage->details()->delete();

                $updatedDetails = 0;
                $requestReasons = $request->request_reasons ?? [];

                // Handle request_reason logic based on document type
                if (empty($requestReasons)) {
                    if ($request->document_type === 'recruitment_request') {
                        $requestReasons = [null]; // For backward compatibility
                    } elseif ($request->document_type === 'officialtravel') {
                        $requestReasons = [null]; // officialtravel doesn't use request_reason, set to null
                    } elseif ($request->document_type === 'leave_request') {
                        $requestReasons = [null]; // leave_request doesn't use request_reason, set to null
                    } else {
                        $requestReasons = [null]; // Default fallback
                    }
                }

                foreach ($request->projects as $projectId) {
                    foreach ($request->departments as $departmentId) {
                        foreach ($requestReasons as $requestReason) {
                            ApprovalStageDetail::create([
                                'approval_stage_id' => $approvalStage->id,
                                'project_id' => $projectId,
                                'department_id' => $departmentId,
                                'request_reason' => $requestReason
                            ]);
                            $updatedDetails++;
                        }
                    }
                }
            } catch (\Illuminate\Database\QueryException $e) {
                // Handle duplicate entry error
                if ($e->getCode() == 23000 && str_contains($e->getMessage(), 'unique_stage_detail')) {
                    // Extract the duplicate key information
                    preg_match("/Duplicate entry '([^']+)' for key/", $e->getMessage(), $matches);
                    $duplicateKey = $matches[1] ?? 'unknown';

                    // Parse the duplicate key to get meaningful information
                    $keyParts = explode('-', $duplicateKey);
                    $duplicateStageId = $keyParts[0] ?? 'unknown';
                    $duplicateProjectId = $keyParts[1] ?? 'unknown';
                    $duplicateDepartmentId = $keyParts[2] ?? 'unknown';

                    // Get the existing stage details for better error message
                    $existingDetail = ApprovalStageDetail::with(['approvalStage.approver', 'project', 'department'])
                        ->where('approval_stage_id', $duplicateStageId)
                        ->where('project_id', $duplicateProjectId)
                        ->where('department_id', $duplicateDepartmentId)
                        ->first();

                    if ($existingDetail) {
                        $errorMessage = "Duplicate configuration detected! ";
                        $errorMessage .= "The combination of ";
                        $errorMessage .= "<strong>Project: {$existingDetail->project->project_code}</strong>, ";
                        $errorMessage .= "<strong>Department: {$existingDetail->department->department_name}</strong> ";
                        $errorMessage .= "already exists for ";
                        $errorMessage .= "<strong>{$existingDetail->approvalStage->approver->name}</strong> ";
                        $errorMessage .= "in the approval stage configuration.";

                        if ($request->document_type === 'recruitment_request' && $existingDetail->request_reason) {
                            $reasonLabel = $this->getRequestReasonLabel($existingDetail->request_reason);
                            $errorMessage .= " (Request Reason: <strong>{$reasonLabel}</strong>)";
                        }
                    } else {
                        $errorMessage = "Duplicate configuration detected! This combination of project, department, and approver already exists in the approval stage configuration.";
                    }

                    return redirect()->back()
                        ->withInput()
                        ->withErrors(['duplicate' => $errorMessage]);
                }

                // Re-throw other database errors
                throw $e;
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
            // Find the specific approval stage to delete
            $approvalStage = ApprovalStage::findOrFail($id);
            $approverId = $approvalStage->approver_id;
            $approvalOrder = $approvalStage->approval_order;
            $documentType = $approvalStage->document_type;

            // Get approver name for error/success message
            $approverName = User::find($approverId)->name ?? 'Unknown';

            // Check if there are any active approval plans that use this specific stage
            $activeApprovalPlans = ApprovalPlan::where('approver_id', $approverId)
                ->where('document_type', $documentType)
                ->where('approval_order', $approvalOrder)
                ->where('is_open', 1) // open status
                ->count();

            if ($activeApprovalPlans > 0) {
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => "Cannot delete this approval stage for {$approverName} (Order {$approvalOrder}). There are {$activeApprovalPlans} active approval plan(s) using this stage that need to be processed first."
                    ], 400);
                }
                return redirect()->route('approval.stages.index')->with('toast_error', "Cannot delete this approval stage for {$approverName} (Order {$approvalOrder}). There are {$activeApprovalPlans} active approval plan(s) using this stage that need to be processed first.");
            }

            // Check if there are any documents submitted for approval that would use this stage
            $submittedDocuments = 0;

            if ($documentType === 'officialtravel') {
                $submittedDocuments = Officialtravel::where('status', 'submitted')
                    ->whereNotNull('submit_at')
                    ->whereNull('approved_at')
                    ->count();
            } elseif ($documentType === 'recruitment_request') {
                $submittedDocuments = RecruitmentRequest::where('status', 'submitted')
                    ->whereNotNull('submit_at')
                    ->whereNull('approved_at')
                    ->count();
            } elseif ($documentType === 'leave_request') {
                $submittedDocuments = \App\Models\LeaveRequest::where('status', 'pending')
                    ->whereNull('approved_at')
                    ->count();
            }

            if ($submittedDocuments > 0) {
                $docTypeName = match ($documentType) {
                    'officialtravel' => 'official travel',
                    'recruitment_request' => 'recruitment request',
                    'leave_request' => 'leave request',
                    default => $documentType
                };
                if (request()->ajax()) {
                    return response()->json([
                        'success' => false,
                        'message' => "Cannot delete this approval stage. There are {$submittedDocuments} {$docTypeName} document(s) currently submitted for approval that need to be processed first."
                    ], 400);
                }
                return redirect()->route('approval.stages.index')->with('toast_error', "Cannot delete this approval stage. There are {$submittedDocuments} {$docTypeName} document(s) currently submitted for approval that need to be processed first.");
            }

            // Get count of details that will be deleted
            $detailsCount = $approvalStage->details()->count();

            // Delete the specific approval stage (details will be deleted via cascade)
            $approvalStage->delete();

            if (request()->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => "Approval stage for {$approverName} (Order {$approvalOrder}, {$documentType}) has been deleted successfully. ({$detailsCount} detail records removed)",
                    'deleted_count' => 1
                ]);
            }

            return redirect()->route('approval.stages.index')->with('toast_success', "Approval stage for {$approverName} (Order {$approvalOrder}, {$documentType}) has been deleted successfully. ({$detailsCount} detail records removed)");
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to delete approval stage: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->route('approval.stages.index')->with('toast_error', 'Failed to delete approval stage: ' . $e->getMessage());
        }
    }

    public function data(Request $request)
    {
        // Build query with relationships
        $query = ApprovalStage::with(['approver', 'details.project', 'details.department']);

        // Apply filters
        if ($request->filled('document_type')) {
            $query->where('document_type', $request->document_type);
        }

        if ($request->filled('approver_id')) {
            $query->where('approver_id', $request->approver_id);
        }

        if ($request->filled('project_id')) {
            $query->whereHas('details', function ($q) use ($request) {
                $q->where('project_id', $request->project_id);
            });
        }

        if ($request->filled('department_id')) {
            $query->whereHas('details', function ($q) use ($request) {
                $q->where('department_id', $request->department_id);
            });
        }


        // Get filtered stages
        $stages = $query->orderBy('approver_id', 'asc')
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

        // Group stages by approver, document type, AND approval order to show all stages separately
        $groupedData = $stages->groupBy(['approver_id', 'document_type', 'approval_order'])->map(function ($approverGroups) {
            return $approverGroups->map(function ($documentGroups) {
                return $documentGroups->map(function ($orderGroups) {
                    return $orderGroups->first(); // Take first stage from each approval order group
                });
            });
        })->flatten(2)->sortBy(function ($item) {
            // Sort by approver name, then document_type, then approval_order
            return $item->approver->name . '_' . $item->document_type . '_' . str_pad($item->approval_order, 3, '0', STR_PAD_LEFT);
        });

        return DataTables::of($groupedData)
            ->addColumn('approver', function ($stage) {
                return $stage->approver->name;
            })
            ->addColumn('document_type', function ($stage) {
                $documentName = match ($stage->document_type) {
                    'officialtravel' => 'Official Travel',
                    'recruitment_request' => 'Recruitment Request',
                    'leave_request' => 'Leave Request',
                    default => ucfirst(str_replace('_', ' ', $stage->document_type))
                };

                $badgeClass = match ($stage->document_type) {
                    'officialtravel' => 'badge-warning',
                    'recruitment_request' => 'badge-info',
                    'leave_request' => 'badge-success',
                    default => 'badge-secondary'
                };

                $html = '<span class="badge ' . $badgeClass . '">' . $documentName . '</span>';

                // Add request reason information for recruitment_request
                if ($stage->document_type === 'recruitment_request') {
                    $requestReasons = $stage->details->pluck('request_reason')->filter()->unique()->sort();
                    if ($requestReasons->isNotEmpty()) {
                        $html .= '<br><small class="text-muted">';
                        $html .= '<i class="fas fa-tag"></i> Request Reasons:';
                        $html .= '<ul class="mb-0 mt-1" style="padding-left: 15px;">';
                        foreach ($requestReasons as $reason) {
                            $reasonLabel = $this->getRequestReasonLabel($reason);
                            $html .= '<li>' . $reasonLabel . '</li>';
                        }
                        $html .= '</ul>';
                        $html .= '</small>';
                    } else {
                        $html .= '<br><small class="text-danger"><i class="fas fa-exclamation-circle"></i> Reason cannot be empty</small>';
                    }
                }

                return $html;
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
                'document_type' => 'required|string|in:officialtravel,recruitment_request,leave_request',
                'department_id' => 'nullable|integer|exists:departments,id',
                'request_reason' => 'nullable|string',
                'level_id' => 'nullable|integer|exists:levels,id'
            ]);

            // For recruitment_request, use department_id from request
            // For officialtravel, use department_id from request (main traveler's department)
            $departmentId = null;
            $requestReason = $request->request_reason;

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
            } elseif ($request->document_type === 'leave_request') {
                if ($request->has('department_id')) {
                    $departmentId = $request->department_id;
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Department ID is required for leave request (employee department)'
                    ], 400);
                }
            }

            // Debug logging
            Log::info("Searching for approval stages with criteria:", [
                'project_id' => $request->project_id,
                'department_id' => $departmentId,
                'document_type' => $request->document_type,
                'request_reason' => $requestReason
            ]);

            // Get approval stages for the specified criteria with order
            $approvalStages = ApprovalStage::with(['approver.departments', 'details' => function ($query) use ($request, $departmentId, $requestReason) {
                $query->where('project_id', $request->project_id)
                    ->where('department_id', $departmentId);

                // Add request_reason filtering if provided
                if ($requestReason !== null) {
                    $query->where('request_reason', $requestReason);
                } else {
                    // For official travel or recruitment without request_reason, only get stages without request_reason
                    $query->whereNull('request_reason');
                }
            }])
                ->where('document_type', $request->document_type)
                ->whereHas('details', function ($query) use ($request, $departmentId, $requestReason) {
                    $query->where('project_id', $request->project_id)
                        ->where('department_id', $departmentId);

                    // Add request_reason filtering if provided
                    if ($requestReason !== null) {
                        $query->where('request_reason', $requestReason);
                    } else {
                        // For official travel or recruitment without request_reason, only get stages without request_reason
                        $query->whereNull('request_reason');
                    }
                })
                ->orderBy('approval_order', 'asc')
                ->get();

            // Debug logging
            Log::info("Found approval stages:", [
                'count' => $approvalStages->count(),
                'stages' => $approvalStages->toArray()
            ]);

            // For leave_request, filter approvers based on level hierarchy with hierarchical rules
            if ($request->document_type === 'leave_request' && $request->has('level_id')) {
                $levelId = $request->level_id;

                // Get the applicant's level order
                $applicantLevel = \App\Models\Level::find($levelId);

                if ($applicantLevel) {
                    $applicantLevelOrder = $applicantLevel->level_order;

                    Log::info("Filtering approvers for leave_request based on hierarchical level rules:", [
                        'applicant_level_id' => $levelId,
                        'applicant_level' => $applicantLevel->name,
                        'applicant_level_order' => $applicantLevelOrder
                    ]);

                    // Get dynamic level orders
                    $managerLevel = \App\Models\Level::where('name', 'Manager')->where('is_active', 1)->first();
                    $directorLevel = \App\Models\Level::where('name', 'Director')->where('is_active', 1)->first();
                    $managerLevelOrder = $managerLevel ? $managerLevel->level_order : 5;
                    $directorLevelOrder = $directorLevel ? $directorLevel->level_order : 6;

                    // CASE 1: Director level - follow approval_stages setup
                    if ($applicantLevelOrder == $directorLevelOrder) {
                        Log::info("Director level detected - following approval_stages setup");
                        // For Director level, still follow the approval_stages configuration
                        // If no approval stages are configured, return empty collection
                        $filteredStages = $approvalStages->filter(function ($stage) use ($request) {
                            // Include all approvers configured in approval_stages for this project/department
                            return true; // All configured approvers are valid for Director level
                        });
                    }
                    // CASE 2: Manager -> Director only
                    elseif ($applicantLevelOrder == $managerLevelOrder) {
                        Log::info("Manager level detected - only director can approve");

                        $filteredStages = $approvalStages->filter(function ($stage) use ($directorLevelOrder, $request) {
                            $approver = $stage->approver;
                            $employee = $approver->employee;

                            if (!$employee) {
                                return false;
                            }

                            $administration = $employee->administrations()
                                ->where('is_active', 1)
                                ->where('project_id', $request->project_id)
                                ->with('level')
                                ->first();

                            if (!$administration || !$administration->level) {
                                return false;
                            }

                            // Only include directors
                            $isDirector = $administration->level->level_order == $directorLevelOrder;

                            Log::info("Manager approver check:", [
                                'approver_name' => $approver->name,
                                'approver_level_order' => $administration->level->level_order,
                                'is_director' => $isDirector
                            ]);

                            return $isDirector;
                        });
                    }
                    // CASE 3: Other levels (1-4) -> max 2 levels above, but not exceeding Manager level
                    else {
                        $maxLevelDifference = 2;
                        $minApproverLevel = $applicantLevelOrder + 1;
                        $maxApproverLevel = $applicantLevelOrder + $maxLevelDifference;

                        // Ensure we don't exceed manager level for non-manager applicants
                        if ($applicantLevelOrder < $managerLevelOrder) {
                            $maxApproverLevel = $managerLevelOrder;
                        }

                        Log::info("Non-manager level detected - max 2 levels above, capped at manager:", [
                            'applicant_level_order' => $applicantLevelOrder,
                            'min_approver_level' => $minApproverLevel,
                            'max_approver_level' => $maxApproverLevel,
                            'manager_level_order' => $managerLevelOrder
                        ]);

                        $filteredStages = $approvalStages->filter(function ($stage) use ($minApproverLevel, $maxApproverLevel, $request) {
                            $approver = $stage->approver;
                            $employee = $approver->employee;

                            if (!$employee) {
                                return false;
                            }

                            $administration = $employee->administrations()
                                ->where('is_active', 1)
                                ->where('project_id', $request->project_id)
                                ->with('level')
                                ->first();

                            if (!$administration || !$administration->level) {
                                return false;
                            }

                            $approverLevelOrder = $administration->level->level_order;

                            // Check if approver is within allowed range
                            $isWithinRange = ($approverLevelOrder >= $minApproverLevel && $approverLevelOrder <= $maxApproverLevel);

                            Log::info("Non-manager approver check:", [
                                'approver_name' => $approver->name,
                                'approver_level' => $administration->level->name,
                                'approver_level_order' => $approverLevelOrder,
                                'allowed_range' => "{$minApproverLevel}-{$maxApproverLevel}",
                                'is_within_range' => $isWithinRange
                            ]);

                            return $isWithinRange;
                        });
                    }

                    $approvalStages = $filteredStages;

                    Log::info("Filtered approval stages count:", [
                        'count' => $approvalStages->count()
                    ]);
                }
            }

            $approvers = $approvalStages->map(function ($stage) {
                return [
                    'id' => $stage->approver->id,
                    'name' => $stage->approver->name,
                    'department' => $stage->approver->departments->first()->department_name ?? 'No Department',
                    'order' => $stage->approval_order,
                ];
            })->values(); // Re-index array to ensure it's sequential

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
