<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Project;
use App\Models\ApprovalPlan;
use App\Models\LeaveRequest;
use App\Models\LeaveEntitlement;
use Illuminate\Http\Request;
use App\Models\ApprovalStage;
use App\Models\Officialtravel;
use App\Models\RecruitmentRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class ApprovalPlanController extends Controller
{
    /**
     * Create approval plans for a document
     *
     * This function creates approval plans for a specific document based on its type.
     * It identifies the appropriate approvers from the ApprovalStage model and
     * creates an approval plan entry for each approver.
     *
     * @param string $document_type Type of document ('officialtravel', 'recruitment_request', 'leave_request')
     * @param int $document_id ID of the document
     * @return int|bool Number of approvers created or false if failed
     */
    public function create_approval_plan($document_type, $document_id)
    {
        // Retrieve the document based on its type
        $request_reason = null; // Default for official travel

        if ($document_type == 'officialtravel') {
            $document = Officialtravel::findOrFail($document_id);
            $project = $document->official_travel_origin;
            // For official travel, use department from main traveler's position
            $traveler = $document->traveler;
            if (!$traveler || !$traveler->position || !$traveler->position->department) {
                Log::error("Main traveler or position/department not found for official travel {$document_id}");
                return false;
            }
            $department_id = $traveler->position->department_id;
            // Official travel doesn't use request_reason (stays null)
        } elseif ($document_type == 'recruitment_request') {
            $document = RecruitmentRequest::findOrFail($document_id);
            $project = $document->project_id;
            // For recruitment request, use the department_id from the document
            $department_id = $document->department_id;
            // Get request_reason for conditional approval
            $request_reason = $document->request_reason;
        } elseif ($document_type == 'leave_request') {
            $document = LeaveRequest::with('leaveType')->findOrFail($document_id);
            $project = $document->administration->project_id;
            $department_id = $document->administration->position->department_id;
            $request_reason = null; // Leave request tidak pakai request_reason
        } else {
            return false; // Invalid document type
        }

        // Debug logging
        Log::info("Creating approval plan for document_type: {$document_type}, document_id: {$document_id}, project: {$project}, department_id: {$department_id}, request_reason: {$request_reason}");

        // Use the new structure with approval_stage_details
        $approvers = ApprovalStage::with(['approver', 'details'])
            ->where('document_type', $document_type)
            ->whereHas('details', function ($query) use ($project, $department_id, $request_reason) {
                $query->where('project_id', $project)
                    ->where('department_id', $department_id);

                // Add request_reason filtering if provided - use original value
                if ($request_reason !== null) {
                    $query->where('request_reason', $request_reason);
                } else {
                    // For official travel, only get stages without request_reason
                    $query->whereNull('request_reason');
                }
            })
            ->orderBy('approval_order', 'asc')
            ->get();

        // For recruitment_request, apply conditional logic based on request_reason and project type
        if ($document_type == 'recruitment_request' && $request_reason) {
            $approvers = $this->getConditionalApprovers($request_reason, $project, $department_id, $approvers);
        }

        // For leave_request, check if it's periodic leave (roster-based)
        if ($document_type == 'leave_request') {
            $isPeriodicLeave = $document->leaveType && $document->leaveType->category === 'periodic';

            if ($isPeriodicLeave) {
                // For periodic leave, use specific hierarchical approval for roster-based leave
                Log::info("Periodic leave detected - using periodic leave hierarchical approval logic");
                $approvers = $this->getPeriodicLeaveHierarchicalApprovers($document, $project, $department_id);
            } else {
                // For regular leave, use hierarchical approval based on level
                Log::info("Regular leave detected - using hierarchical approval based on level");
                $approvers = $this->getHierarchicalApprovers($document, $project, $department_id);
            }
        }

        // Debug logging
        Log::info("Found {$approvers->count()} approvers for this document");
        foreach ($approvers as $approver) {
            Log::info("Approver ID: {$approver->approver_id}, Approval Order: {$approver->approval_order}");
        }

        // If approvers exist, create approval plans
        if ($approvers->count() > 0) {
            $created_count = 0;
            $error_count = 0;
            $errors = [];

            // Create an approval plan for each approver
            foreach ($approvers as $approver) {
                try {
                    // Check if approval plan already exists to prevent duplicates
                    $existing_plan = ApprovalPlan::where('document_id', $document_id)
                        ->where('document_type', $document_type)
                        ->where('approver_id', $approver->approver_id)
                        ->first();

                    if ($existing_plan) {
                        Log::warning("Approval plan already exists for document_id: {$document_id}, approver_id: {$approver->approver_id}");
                        continue;
                    }

                    // Validate approval_order
                    if (empty($approver->approval_order)) {
                        $error_msg = "Approval order is empty for approver {$approver->approver_id}";
                        Log::error($error_msg);
                        $errors[] = $error_msg;
                        $error_count++;
                        continue;
                    }

                    $approval_plan = ApprovalPlan::create([
                        'document_id' => $document_id,
                        'document_type' => $document_type,
                        'approver_id' => $approver->approver_id,
                        'approval_order' => $approver->approval_order,
                        'status' => 0, // Pending
                        'is_open' => true,
                    ]);

                    $created_count++;
                    Log::info("Created approval plan ID: {$approval_plan->id} for approver: {$approver->approver_id} with order: {$approver->approval_order}");
                } catch (\Exception $e) {
                    $error_msg = "Failed to create approval plan for approver {$approver->approver_id}: " . $e->getMessage();
                    Log::error($error_msg);
                    $errors[] = $error_msg;
                    $error_count++;
                }
            }

            // Update document to mark it as submitted and no longer editable
            // Note: leave_request doesn't have submit_at field, it uses created_at
            if ($document_type !== 'leave_request') {
                $document->submit_at = Carbon::now();
                $document->save();
            }

            Log::info("Created {$created_count} approval plans out of {$approvers->count()} approvers. Errors: {$error_count}");

            // Clear cache for pending approvals count for all approvers
            foreach ($approvers as $approver) {
                cache()->forget('pending_approvals_' . $approver->approver_id);
            }

            // If there were errors, log them for debugging
            if (!empty($errors)) {
                Log::error("Errors during approval plan creation: " . implode("; ", $errors));
            }

            return $created_count; // Return number of approvers actually created
        }

        // Return false if no approvers found
        Log::warning("No approvers found for document_type: {$document_type}, project: {$project}, department_id: {$department_id}");

        // Throw exception with clear message for user
        throw new \Exception("No approval stages configured for this project and department combination. Please contact administrator to set up approval workflow.");
    }

    /**
     * Update approval decision
     *
     * This function processes an approval decision (approve, reject)
     * and updates both the approval plan and the associated document.
     *
     * Approval status codes:
     * 0 = Pending
     * 1 = Approved
     * 2 = Reject
     *
     * @param Request $request The HTTP request containing approval data
     * @param int $id The ID of the approval plan to update
     * @return \Illuminate\Http\RedirectResponse Redirect to appropriate page
     */
    public function update(Request $request, $id)
    {
        // Find the approval plan
        $approval_plan = ApprovalPlan::findOrFail($id);

        // Check if approval can be processed (sequential validation)
        if (!$approval_plan->canBeProcessed()) {
            $response = [
                'success' => false,
                'message' => 'Previous approvals must be completed first. Please wait for earlier approvers to process their approvals.'
            ];

            if ($request->ajax()) {
                return response()->json($response, 422);
            }

            return redirect()->back()->with('toast_error', $response['message']);
        }

        // Update the approval plan with the decision
        $approval_plan->update([
            'status' => $request->status,
            'remarks' => $request->remarks,
            'is_read' => $request->remarks ? 0 : 1, // Mark as unread if there are remarks
        ]);

        // Get document type and retrieve the associated document
        $document_type = $approval_plan->document_type;

        if ($document_type == 'officialtravel') {
            $document = Officialtravel::where('id', $approval_plan->document_id)->first();
        } elseif ($document_type == 'recruitment_request') {
            $document = RecruitmentRequest::findOrFail($approval_plan->document_id);
        } elseif ($document_type == 'leave_request') {
            $document = LeaveRequest::findOrFail($approval_plan->document_id);
        } else {
            return false; // Invalid document type
        }

        // Get all active approval plans for this document
        $approval_plans = ApprovalPlan::where('document_id', $document->id)
            ->where('document_type', $document_type)
            ->where('is_open', 1)
            ->get();

        // Count different approval decisions
        $rejected_count = 0;
        $approved_count = 0;

        foreach ($approval_plans as $approval_plan) {
            if ($approval_plan->status == 2) { // Rejected
                $rejected_count++;
            }
            if ($approval_plan->status == 1) { // Approved
                $approved_count++;
            }
        }

        // Handle document rejection
        if ($rejected_count > 0) {
            $document->update([
                'status' => 'rejected',
            ]);

            // Close all open approval plans for this document
            $this->closeOpenApprovalPlans($document_type, $document->id);
        }

        // Handle document approval (when all sequential approvals are completed)
        if ($this->areAllSequentialApprovalsCompleted($approval_plan)) {
            // Update document status to approved
            $updateData = [
                'status' => 'approved',
                'approved_at' => $approval_plan->updated_at,
            ];

            $document->update($updateData);

            // Update leave entitlements ONLY for leave_request documents
            // if ($document_type === 'leave_request') {
            //     $this->updateLeaveEntitlements($document);
            // }

            // Log the approval completion
            Log::info("Document approved successfully", [
                'document_type' => $document_type,
                'document_id' => $document->id,
                'approved_at' => $approval_plan->updated_at,
                'approver_id' => $approval_plan->approver_id
            ]);
        }

        // Determine the appropriate success message based on the approval status
        $status_text = '';
        if ($request->status == 1) {
            $status_text = 'approved';
        } elseif ($request->status == 2) {
            $status_text = 'rejected';
        } else {
            $status_text = 'updated';
        }

        // Check if the request is AJAX
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => ucfirst($document_type) . ' has been ' . $status_text,
                'document_type' => $document_type
            ]);
        }

        // Redirect to appropriate page based on document type for non-AJAX requests
        return redirect()->route('approvals.request.requests.index')->with('success', ucfirst($document_type) . ' has been ' . $status_text);
    }

    /**
     * Get approval status descriptions
     *
     * Returns an array mapping status codes to their text descriptions
     *
     * @return array Array of approval status descriptions
     */
    public function approvalStatus()
    {
        return [
            0 => 'Pending',
            1 => 'Approved',
            2 => 'Reject',
        ];
    }

    /**
     * Close all open approval plans for a document
     *
     * This function is called when a document is rejected or needs revision.
     * It marks all open approval plans for the document as closed (is_open = 0).
     *
     * @param string $document_type Type of document
     * @param int $document_id ID of the document
     * @return void
     */
    public function closeOpenApprovalPlans($document_type, $document_id)
    {
        // Find all open approval plans for this document
        $approval_plans = ApprovalPlan::where('document_id', $document_id)
            ->where('document_type', $document_type)
            ->where('is_open', 1)
            ->get();

        // Close all open approval plans
        if ($approval_plans->count() > 0) {
            foreach ($approval_plans as $approval_plan) {
                $approval_plan->update(['is_open' => 0]);
            }
        }
    }

    /**
     * @deprecated Use closeOpenApprovalPlans() instead
     */
    public function cekExistingAndDisableOpen($document_type, $document_id)
    {
        return $this->closeOpenApprovalPlans($document_type, $document_id);
    }

    /**
     * Bulk approve multiple documents
     *
     * This method allows approving multiple documents at once.
     *
     * @param Request $request The HTTP request containing the IDs of documents to approve
     * @return \Illuminate\Http\JsonResponse JSON response with success/error message
     */
    public function bulkApprove(Request $request)
    {
        // Validate request
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'required|integer',
            'document_type' => 'required|string|in:officialtravel,recruitment_request,leave_request',
            'remarks' => 'nullable|string',
        ]);

        $successCount = 0;
        $failCount = 0;
        $document_type = $request->document_type;

        // Process each approval plan
        foreach ($request->ids as $id) {
            $approval_plan = ApprovalPlan::findOrFail($id);

            // Skip if not the correct document type or already processed
            if ($approval_plan->document_type !== $document_type || $approval_plan->status !== 0 || $approval_plan->is_open !== 1) {
                $failCount++;
                continue;
            }

            // Check sequential approval order - prevent approval of higher order steps before lower order steps
            if (!$this->canProcessSequentialApproval($approval_plan)) {
                $failCount++;
                continue;
            }

            // Update the approval plan
            $approval_plan->update([
                'status' => 1, // Approved
                'remarks' => $request->remarks,
                'is_read' => $request->remarks ? 0 : 1,
            ]);

            // Get the document
            if ($document_type == 'officialtravel') {
                $document = Officialtravel::where('id', $approval_plan->document_id)->first();
            } elseif ($document_type == 'recruitment_request') {
                $document = RecruitmentRequest::findOrFail($approval_plan->document_id);
            } elseif ($document_type == 'leave_request') {
                $document = LeaveRequest::findOrFail($approval_plan->document_id);
            } else {
                $failCount++;
                continue;
            }

            // Get all active approval plans for this document
            $approval_plans = ApprovalPlan::where('document_id', $document->id)
                ->where('document_type', $document_type)
                ->where('is_open', 1)
                ->get();

            // Count approved plans
            $approved_count = $approval_plans->where('status', 1)->count();

            // Check if all approvers have approved
            if ($approved_count === $approval_plans->count()) {
                // Set printable = 1 untuk semua dokumen
                $printable_value = 1;

                // Update document status to approved
                $updateData = [
                    'status' => 'approved',
                    'approved_at' => now(),
                ];

                $document->update($updateData);

                // Update leave entitlements ONLY for leave_request documents
                // if ($document_type === 'leave_request') {
                //     $this->updateLeaveEntitlements($document);
                // }
            }

            $successCount++;
        }

        // Return response
        if ($successCount > 0) {
            // Clear cache for pending approvals count for current user
            cache()->forget('pending_approvals_' . Auth::id());

            $documentTypeLabel = ucfirst($document_type);
            if ($document_type === 'officialtravel') {
                $documentTypeLabel = 'Official Travel';
            } elseif ($document_type === 'recruitment_request') {
                $documentTypeLabel = 'Recruitment Request';
            } elseif ($document_type === 'leave_request') {
                $documentTypeLabel = 'Leave Request';
            }

            return response()->json([
                'success' => true,
                'message' => $successCount . ' ' . $documentTypeLabel . ($successCount > 1 ? 's' : '') . ' have been approved successfully' . ($failCount > 0 ? ' (' . $failCount . ' failed)' : ''),
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Failed to approve any documents',
            ], 422);
        }
    }

    /**
     * Check if an approval plan can be processed sequentially (hybrid approach).
     *
     * This method uses a hybrid approach that:
     * - Supports parallel approvals (multiple approvers with same approval_order)
     * - Requires ALL approvals in previous order groups to be completed
     * - Works with missing, skipped, or non-sequential approval orders
     *
     * Examples:
     * - Sequential: 1 → 2 → 3
     * - Parallel: 1,1 → 3 (both order 1 must approve before order 3)
     * - Missing start: 2 → 3 (order 2 becomes first order)
     * - Skipped: 1 → 3 → 5 (no order 2 or 4)
     * - Mixed: 1,1 → 2,2 → 3 (multiple parallel groups)
     *
     * @param ApprovalPlan $approvalPlan The approval plan to check.
     * @return bool True if it can be processed, false otherwise.
     */
    private function canProcessSequentialApproval($approvalPlan)
    {
        // If approval_order is null or empty, allow processing (fallback for legacy data)
        if (empty($approvalPlan->approval_order)) {
            return true;
        }

        // Get all approval plans for this document, ordered by approval_order
        $allApprovals = ApprovalPlan::where('document_id', $approvalPlan->document_id)
            ->where('document_type', $approvalPlan->document_type)
            ->orderBy('approval_order')
            ->get();

        // If no approvals found, allow processing (shouldn't happen but safe fallback)
        if ($allApprovals->isEmpty()) {
            return true;
        }

        // Group approvals by approval_order
        $orderGroups = $allApprovals->groupBy('approval_order');

        // Get current approval order
        $currentOrder = $approvalPlan->approval_order;

        // If this is the first order group, allow processing
        $firstOrder = $orderGroups->keys()->first();
        if ($currentOrder == $firstOrder) {
            return true;
        }

        // Check if ALL previous order groups are fully approved
        foreach ($orderGroups as $order => $approvals) {
            // Skip current and future orders
            if ($order >= $currentOrder) {
                break;
            }

            // Check if ALL approvals in this order group are approved
            $allApproved = $approvals->every(function ($approval) {
                return $approval->status == 1; // Status 1 = Approved
            });

            // If any previous order group is not fully approved, cannot process
            if (!$allApproved) {
                return false;
            }
        }

        // All previous order groups are fully approved
        return true;
    }

    /**
     * Check if all sequential approvals are completed for a document
     */
    private function areAllSequentialApprovalsCompleted($approvalPlan)
    {
        $allApprovals = ApprovalPlan::where('document_id', $approvalPlan->document_id)
            ->where('document_type', $approvalPlan->document_type)
            ->where('is_open', true)
            ->get();

        // If no approvals exist, return false
        if ($allApprovals->isEmpty()) {
            return false;
        }

        // Check if all approvals are completed (status = 1 for approved)
        foreach ($allApprovals as $approval) {
            if ($approval->status != 1) { // Not approved
                return false;
            }
        }

        // All approvals are completed
        return true;
    }

    /**
     * Get conditional approvers based on request_reason and project type
     *
     * NOTE: For now, we return all configured approvers since the approval stages
     * are already filtered correctly by project/department/request_reason.
     * The conditional logic was causing issues where configured approvers were filtered out.
     */
    private function getConditionalApprovers($request_reason, $project_id, $department_id, $approvers)
    {
        // Log for debugging
        Log::info("Conditional approver logic for request_reason: {$request_reason}, project_id: {$project_id}");
        Log::info("Original approvers count: " . $approvers->count());

        // Return all configured approvers - they are already filtered by the main query
        // based on project_id, department_id, and request_reason matching
        return $approvers;

        // Legacy conditional logic (disabled for now):
        /*
        // Determine project type
        $project_type = $this->getProjectType($project_id);

        // Apply conditional logic based on request_reason
        switch ($request_reason) {
            case 'replacement_resign':
            case 'replacement_promotion':
                // Only HCS Division Manager for replacement reasons
                return $approvers->filter(function ($approver) {
                    return $this->isHCSDivisionManager($approver->approver_id);
                });

            case 'additional_workplan':
                // For additional workplan
                if ($project_type === 'HO' || $project_type === 'BO' || $project_type === 'APS') {
                    // HCS Division Manager → HCL Director
                    return $approvers->filter(function ($approver) {
                        return $this->isHCSDivisionManager($approver->approver_id) ||
                            $this->isHCLDirector($approver->approver_id);
                    });
                } else {
                    // Operational General Manager → HCS Division Manager
                    return $approvers->filter(function ($approver) {
                        return $this->isOperationalGeneralManager($approver->approver_id) ||
                            $this->isHCSDivisionManager($approver->approver_id);
                    });
                }

            case 'other':
                // Return all approvers for other cases
                return $approvers;

            default:
                // Return all approvers for unknown cases
                return $approvers;
        }
        */
    }

    /**
     * Determine project type based on project
     */
    private function getProjectType($project_id)
    {
        $project = Project::find($project_id);

        if (!$project) {
            return 'UNKNOWN';
        }

        $project_code = strtoupper($project->project_code);

        if (str_contains($project_code, '000H')) {
            return 'HO';
        } elseif (str_contains($project_code, '001H')) {
            return 'BO';
        } elseif (str_contains($project_code, 'APS')) {
            return 'APS';
        } else {
            return 'ALL_PROJECT';
        }
    }

    /**
     * Get manager level order from database
     * Uses static cache to avoid repeated database queries
     */
    private function getManagerLevelOrder()
    {
        static $managerLevel = null;

        if ($managerLevel === null) {
            $level = \App\Models\Level::where('name', 'Manager')
                ->where('is_active', 1)
                ->first();
            $managerLevel = $level ? $level->level_order : 5;
        }

        return $managerLevel;
    }

    /**
     * Get director level order from database
     * Uses static cache to avoid repeated database queries
     */
    private function getDirectorLevelOrder()
    {
        static $directorLevel = null;

        if ($directorLevel === null) {
            $level = \App\Models\Level::where('name', 'Director')
                ->where('is_active', 1)
                ->first();
            $directorLevel = $level ? $level->level_order : 6;
        }

        return $directorLevel;
    }

    /**
     * Check if level is director
     */
    private function isDirectorLevel($levelOrder)
    {
        return $levelOrder == $this->getDirectorLevelOrder();
    }

    /**
     * Check if level is manager
     */
    private function isManagerLevel($levelOrder)
    {
        return $levelOrder == $this->getManagerLevelOrder();
    }

    /**
     * Get hierarchical approvers for leave request based on level hierarchy
     *
     * This method implements hierarchical approval based on level order:
     * - Director (level 6): Self-approval
     * - Manager (level 5): Approved by Director only
     * - Other levels (1-4): Approved by maximum 2 levels above
     *
     * Ketentuan:
     * - Non Staff/Foreman/SPV/SPT: approval berjenjang max 2 level di atas
     * - Manager: approval langsung ke Director
     * - Director: self-approval
     */
    private function getHierarchicalApprovers($leaveRequest, $projectId, $departmentId)
    {
        $applicantLevel = $leaveRequest->administration->level;
        if (!$applicantLevel) {
            Log::warning("No level found for leave request applicant", [
                'leave_request_id' => $leaveRequest->id,
                'administration_id' => $leaveRequest->administration_id
            ]);
            return collect();
        }

        $applicantLevelOrder = $applicantLevel->level_order;

        Log::info("Getting hierarchical approvers for leave request", [
            'leave_request_id' => $leaveRequest->id,
            'applicant_level' => $applicantLevel->name,
            'applicant_level_order' => $applicantLevelOrder,
            'project_id' => $projectId,
            'department_id' => $departmentId
        ]);

        // CASE 1: Director level - follow approval_stages setup
        if ($this->isDirectorLevel($applicantLevelOrder)) {
            Log::info("Director level detected - following approval_stages setup", [
                'applicant_level' => $applicantLevel->name,
                'project_id' => $projectId,
                'department_id' => $departmentId
            ]);

            // For Director level, still follow the approval_stages configuration
            // Get approval stages filtered by project and department from approval_stage_details
            $approvalStages = ApprovalStage::where('document_type', 'leave_request')
                ->whereHas('details', function ($query) use ($projectId, $departmentId) {
                    $query->where('project_id', $projectId)
                        ->where('department_id', $departmentId)
                        ->whereNull('request_reason');
                })
                ->with(['approver.employee.administrations' => function ($query) {
                    $query->where('is_active', 1)
                        ->with('level');
                }])
                ->orderBy('approval_order', 'asc')
                ->get();

            $approvers = collect();

            foreach ($approvalStages as $stage) {
                $approver = $stage->approver;

                if (!$approver || !$approver->employee) {
                    continue;
                }

                // Get level from any active administration (not limited to same project)
                $approverAdministration = $approver->employee->administrations
                    ->where('is_active', 1)
                    ->first();

                if (!$approverAdministration || !$approverAdministration->level) {
                    continue;
                }

                $approvers->push((object)[
                    'approver_id' => $approver->id,
                    'approval_order' => $stage->approval_order,
                    'level_id' => $approverAdministration->level->id
                ]);
            }

            Log::info("Found {$approvers->count()} approvers for Director level from approval_stages");

            return $approvers;
        }

        // CASE 2: Manager -> Director only
        if ($this->isManagerLevel($applicantLevelOrder)) {
            Log::info("Manager level detected - getting director approvers only", [
                'applicant_level' => $applicantLevel->name,
                'target_level' => 'Director'
            ]);

            return $this->getDirectorApprovers($projectId, $departmentId);
        }

        // CASE 3: Other levels (1-4) -> max 2 levels above, but not exceeding Manager level
        $maxLevelDifference = 2;
        $minApproverLevel = $applicantLevelOrder + 1;
        $maxApproverLevel = $applicantLevelOrder + $maxLevelDifference;

        // Ensure we don't exceed manager level for non-manager applicants
        $managerLevel = $this->getManagerLevelOrder();
        if ($applicantLevelOrder < $managerLevel) {
            $maxApproverLevel = $managerLevel; // Changed from min($maxApproverLevel, $managerLevel)
        }

        Log::info("Getting approvers for non-manager level", [
            'applicant_level_order' => $applicantLevelOrder,
            'min_approver_level' => $minApproverLevel,
            'max_approver_level' => $maxApproverLevel,
            'max_difference' => $maxLevelDifference
        ]);

        return $this->getApproversWithinLevelRange(
            $minApproverLevel,
            $maxApproverLevel,
            $projectId,
            $departmentId
        );
    }

    /**
     * Get hierarchical approvers for periodic leave request (roster-based)
     *
     * This method implements specific hierarchical approval for periodic leave:
     * - Non Staff/Foreman (level 1-2): First available from SPV(3) or SPT(4) + PM(5)
     * - Supervisor (level 3): SPT(4) + PM(5)
     * - Superintendent (level 4): SPT(4) + PM(5) (can be self)
     * - Manager (level 5): Director(6) only
     * - Director (level 6): Follow approval_stages setup
     *
     * Total approvers: 2 (hierarchical + PM) or 1 (PM to Director)
     */
    private function getPeriodicLeaveHierarchicalApprovers($leaveRequest, $projectId, $departmentId)
    {
        $applicantLevel = $leaveRequest->administration->level;
        if (!$applicantLevel) {
            Log::warning("No level found for periodic leave request applicant", [
                'leave_request_id' => $leaveRequest->id,
                'administration_id' => $leaveRequest->administration_id
            ]);
            return collect();
        }

        $applicantLevelOrder = $applicantLevel->level_order;

        Log::info("Getting periodic leave hierarchical approvers", [
            'leave_request_id' => $leaveRequest->id,
            'applicant_level' => $applicantLevel->name,
            'applicant_level_order' => $applicantLevelOrder,
            'project_id' => $projectId,
            'department_id' => $departmentId
        ]);

        // CASE 1: Director level (6) - follow approval_stages setup
        if ($applicantLevelOrder == 6) {
            Log::info("Director level detected - following approval_stages setup for periodic leave");

            // Get approval stages filtered by project and department from approval_stage_details
            $approvalStages = ApprovalStage::where('document_type', 'leave_request')
                ->whereHas('details', function ($query) use ($projectId, $departmentId) {
                    $query->where('project_id', $projectId)
                        ->where('department_id', $departmentId)
                        ->whereNull('request_reason');
                })
                ->with(['approver.employee.administrations' => function ($query) {
                    $query->where('is_active', 1)
                        ->with('level');
                }])
                ->orderBy('approval_order', 'asc')
                ->get();

            $approvers = collect();

            foreach ($approvalStages as $stage) {
                $approver = $stage->approver;

                if (!$approver || !$approver->employee) {
                    continue;
                }

                // Get level from any active administration (not limited to same project)
                $approverAdministration = $approver->employee->administrations
                    ->where('is_active', 1)
                    ->first();

                if (!$approverAdministration || !$approverAdministration->level) {
                    continue;
                }

                $approvers->push((object)[
                    'approver_id' => $approver->id,
                    'approval_order' => $stage->approval_order,
                    'level_id' => $approverAdministration->level->id
                ]);
            }

            Log::info("Found {$approvers->count()} approvers for Director level periodic leave");
            return $approvers;
        }

        // CASE 2: Manager level (5) - Director only
        if ($applicantLevelOrder == 5) {
            Log::info("Manager level detected - getting director approvers only for periodic leave");
            return $this->getDirectorApprovers($projectId, $departmentId);
        }

        // CASE 3 & 4 & 5: Level 1-4 - Get best hierarchical approver + PM
        // Level 1-2: Try SPV(3) first, if not found try SPT(4) + PM(5)
        // Level 3: SPT(4) + PM(5)
        // Level 4: SPT(4) + PM(5)

        $hierarchicalApprover = null;
        $managerApprover = null;

        // Get all approval stages for this project/department from approval_stage_details
        $approvalStages = ApprovalStage::where('document_type', 'leave_request')
            ->whereHas('details', function ($query) use ($projectId, $departmentId) {
                $query->where('project_id', $projectId)
                    ->where('department_id', $departmentId)
                    ->whereNull('request_reason');
            })
            ->with(['approver.employee.administrations' => function ($query) {
                $query->where('is_active', 1)
                    ->with('level');
            }])
            ->orderBy('approval_order', 'asc')
            ->get();

        // Determine required hierarchical level based on applicant level
        $requiredHierarchicalLevels = [];
        if ($applicantLevelOrder <= 2) {
            // Level 1-2: Try level 3 first, then level 4
            $requiredHierarchicalLevels = [3, 4];
            Log::info("Non Staff/Foreman level - looking for SPV(3) or SPT(4)");
        } elseif ($applicantLevelOrder == 3) {
            // Level 3: Need level 4
            $requiredHierarchicalLevels = [4];
            Log::info("Supervisor level - looking for SPT(4)");
        } elseif ($applicantLevelOrder == 4) {
            // Level 4: Need level 4 (can be self)
            $requiredHierarchicalLevels = [4];
            Log::info("Superintendent level - looking for SPT(4)");
        }

        // Find best hierarchical approver and manager
        foreach ($approvalStages as $stage) {
            $approver = $stage->approver;

            if (!$approver || !$approver->employee) {
                continue;
            }

            // Get level from any active administration (not limited to same project)
            $approverAdministration = $approver->employee->administrations
                ->where('is_active', 1)
                ->first();

            if (!$approverAdministration || !$approverAdministration->level) {
                continue;
            }

            $approverLevelOrder = $approverAdministration->level->level_order;

            // Look for hierarchical approver (SPV or SPT)
            if (!$hierarchicalApprover && in_array($approverLevelOrder, $requiredHierarchicalLevels)) {
                $hierarchicalApprover = (object)[
                    'approver_id' => $approver->id,
                    'approval_order' => 1, // First stage
                    'level_id' => $approverAdministration->level->id,
                    'level_order' => $approverLevelOrder
                ];
                Log::info("Found hierarchical approver", [
                    'approver_name' => $approver->name,
                    'level' => $approverAdministration->level->name,
                    'level_order' => $approverLevelOrder
                ]);
            }

            // Look for Manager (level 5)
            if (!$managerApprover && $approverLevelOrder == 5) {
                $managerApprover = (object)[
                    'approver_id' => $approver->id,
                    'approval_order' => 2, // Second stage (PM always last)
                    'level_id' => $approverAdministration->level->id,
                    'level_order' => $approverLevelOrder
                ];
                Log::info("Found manager approver", [
                    'approver_name' => $approver->name,
                    'level' => $approverAdministration->level->name
                ]);
            }

            // Break if both found
            if ($hierarchicalApprover && $managerApprover) {
                break;
            }
        }

        // Build final approvers list
        $approvers = collect();

        if ($hierarchicalApprover) {
            $approvers->push($hierarchicalApprover);
        }

        if ($managerApprover) {
            $approvers->push($managerApprover);
        }

        Log::info("Final periodic leave approvers", [
            'count' => $approvers->count(),
            'hierarchical_found' => $hierarchicalApprover ? 'Yes' : 'No',
            'manager_found' => $managerApprover ? 'Yes' : 'No'
        ]);

        return $approvers;
    }

    /**
     * Get director approvers for manager-level leave requests
     * Only returns approvers with Director level
     */
    private function getDirectorApprovers($projectId, $departmentId)
    {
        $directorLevelOrder = $this->getDirectorLevelOrder();

        // Get approval stages filtered by project and department from approval_stage_details
        $approvalStages = ApprovalStage::where('document_type', 'leave_request')
            ->whereHas('details', function ($query) use ($projectId, $departmentId) {
                $query->where('project_id', $projectId)
                    ->where('department_id', $departmentId)
                    ->whereNull('request_reason');
            })
            ->with(['approver.employee.administrations' => function ($query) {
                $query->where('is_active', 1)
                    ->with('level');
            }])
            ->orderBy('approval_order', 'asc')
            ->get();

        $approvers = collect();

        foreach ($approvalStages as $stage) {
            $approver = $stage->approver;

            if (!$approver || !$approver->employee) {
                continue;
            }

            // Get level from any active administration (not limited to same project)
            $approverAdministration = $approver->employee->administrations
                ->where('is_active', 1)
                ->first();

            if (!$approverAdministration || !$approverAdministration->level) {
                continue;
            }

            $approverLevelOrder = $approverAdministration->level->level_order;

            // Only include directors
            if ($approverLevelOrder == $directorLevelOrder) {
                $approvers->push((object)[
                    'approver_id' => $approver->id,
                    'approval_order' => $stage->approval_order,
                    'level_id' => $approverAdministration->level_id
                ]);

                Log::info("Added director approver", [
                    'approver_name' => $approver->name,
                    'approver_id' => $approver->id,
                    'approver_level' => $approverAdministration->level->name,
                    'approval_order' => $stage->approval_order
                ]);
            }
        }

        Log::info("Final director approvers", [
            'count' => $approvers->count()
        ]);

        return $approvers;
    }

    /**
     * Get approvers within specific level range
     * Used for non-manager levels with max 2 levels above restriction
     */
    private function getApproversWithinLevelRange($minLevelOrder, $maxLevelOrder, $projectId, $departmentId)
    {
        // Get approval stages filtered by project and department from approval_stage_details
        $approvalStages = ApprovalStage::where('document_type', 'leave_request')
            ->whereHas('details', function ($query) use ($projectId, $departmentId) {
                $query->where('project_id', $projectId)
                    ->where('department_id', $departmentId)
                    ->whereNull('request_reason');
            })
            ->with(['approver.employee.administrations' => function ($query) {
                $query->where('is_active', 1)
                    ->with('level');
            }])
            ->orderBy('approval_order', 'asc')
            ->get();

        Log::info("Found approval stages for level range", [
            'count' => $approvalStages->count(),
            'min_level' => $minLevelOrder,
            'max_level' => $maxLevelOrder
        ]);

        $approvers = collect();

        foreach ($approvalStages as $stage) {
            $approver = $stage->approver;

            if (!$approver || !$approver->employee) {
                Log::warning("Approver has no employee record", [
                    'approver_id' => $stage->approver_id,
                    'approver_name' => $approver->name ?? 'Unknown'
                ]);
                continue;
            }

            // Get level from any active administration (not limited to same project)
            $approverAdministration = $approver->employee->administrations
                ->where('is_active', 1)
                ->first();

            if (!$approverAdministration || !$approverAdministration->level) {
                Log::warning("Approver has no valid administration or level", [
                    'approver_id' => $approver->id,
                    'approver_name' => $approver->name
                ]);
                continue;
            }

            $approverLevelOrder = $approverAdministration->level->level_order;

            Log::info("Checking approver level", [
                'approver_name' => $approver->name,
                'approver_level' => $approverAdministration->level->name,
                'approver_level_order' => $approverLevelOrder,
                'min_required' => $minLevelOrder,
                'max_allowed' => $maxLevelOrder
            ]);

            // Only include approvers within the specified level range
            if ($approverLevelOrder >= $minLevelOrder && $approverLevelOrder <= $maxLevelOrder) {
                $approvers->push((object)[
                    'approver_id' => $approver->id,
                    'approval_order' => $stage->approval_order,
                    'level_id' => $approverAdministration->level_id
                ]);

                Log::info("Added approver to hierarchical list", [
                    'approver_name' => $approver->name,
                    'approver_id' => $approver->id,
                    'approver_level' => $approverAdministration->level->name,
                    'approver_level_order' => $approverLevelOrder,
                    'approval_order' => $stage->approval_order
                ]);
            } else {
                Log::info("Skipped approver - level outside allowed range", [
                    'approver_name' => $approver->name,
                    'approver_level_order' => $approverLevelOrder,
                    'allowed_range' => "{$minLevelOrder}-{$maxLevelOrder}"
                ]);
            }
        }

        Log::info("Final hierarchical approvers within level range", [
            'count' => $approvers->count(),
            'level_range' => "{$minLevelOrder}-{$maxLevelOrder}",
            'approvers' => $approvers->map(function ($approver) {
                return [
                    'approver_id' => $approver->approver_id,
                    'approval_order' => $approver->approval_order
                ];
            })->toArray()
        ]);

        return $approvers;
    }
}
