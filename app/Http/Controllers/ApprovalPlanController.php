<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Project;
use App\Models\ApprovalPlan;
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
     * @param string $document_type Type of document ('officialtravel', 'recruitment_request')
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
            $document->submit_at = Carbon::now();
            $document->save();

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
            'document_type' => 'required|string|in:officialtravel,recruitment_request',
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
     * Check if an approval plan can be processed sequentially.
     * This prevents approval of higher order steps before lower order steps are completed.
     * Same approval_order values can be processed in parallel after previous orders are completed.
     *
     * @param ApprovalPlan $approvalPlan The approval plan to check.
     * @return bool True if it can be processed, false otherwise.
     */
    private function canProcessSequentialApproval($approvalPlan)
    {
        // If approval_order is null or empty, allow processing (fallback)
        if (empty($approvalPlan->approval_order)) {
            return true;
        }

        // Check if previous approvals are completed based on approval_order
        $previousApprovals = ApprovalPlan::where('document_id', $approvalPlan->document_id)
            ->where('document_type', $approvalPlan->document_type)
            ->where('approval_order', '<', $approvalPlan->approval_order)
            ->where('status', 1) // Approved
            ->count();

        $expectedPrevious = $approvalPlan->approval_order - 1;

        // If previous orders are completed, this order can be processed
        // Multiple steps with same approval_order can be processed in parallel
        return $previousApprovals >= $expectedPrevious;
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
}
