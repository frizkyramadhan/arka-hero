<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\ApprovalPlan;
use Illuminate\Http\Request;
use App\Models\ApprovalStage;
use App\Models\Officialtravel;
use App\Models\RecruitmentRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
        if ($document_type == 'officialtravel') {
            $document = Officialtravel::findOrFail($document_id);
            $project = $document->official_travel_origin;
        } elseif ($document_type == 'recruitment_request') {
            $document = RecruitmentRequest::findOrFail($document_id);
            $project = $document->project_id;
        } else {
            return false; // Invalid document type
        }

        // Get all approvers for this document type, project and department
        $user = Auth::user();

        // Check if user has departments
        if (!$user->departments || $user->departments->isEmpty()) {
            Log::error("User {$user->id} has no departments assigned");
            return false;
        }

        $department_id = $user->departments->first()->id;

        // Debug logging
        Log::info("Creating approval plan for document_type: {$document_type}, document_id: {$document_id}, project: {$project}, department_id: {$department_id}");

        $approvers = ApprovalStage::where('project_id', $project)
            ->where('department_id', $department_id)
            ->where('document_type', $document_type)
            ->get();

        // Debug logging
        Log::info("Found {$approvers->count()} approvers for this document");
        foreach ($approvers as $approver) {
            Log::info("Approver ID: {$approver->approver_id}, Project ID: {$approver->project_id}, Department ID: {$approver->department_id}");
        }

        // If approvers exist, create approval plans
        if ($approvers->count() > 0) {
            $created_count = 0;

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

                    $approval_plan = ApprovalPlan::create([
                        'document_id' => $document_id,
                        'document_type' => $document_type,
                        'approver_id' => $approver->approver_id,
                    ]);

                    $created_count++;
                    Log::info("Created approval plan ID: {$approval_plan->id} for approver: {$approver->approver_id}");
                } catch (\Exception $e) {
                    Log::error("Failed to create approval plan for approver {$approver->approver_id}: " . $e->getMessage());
                }
            }

            // Update document to mark it as submitted and no longer editable
            $document->submit_at = Carbon::now();
            $document->save();

            Log::info("Created {$created_count} approval plans out of {$approvers->count()} approvers");

            // Clear cache for pending approvals count for all approvers
            foreach ($approvers as $approver) {
                cache()->forget('pending_approvals_' . $approver->approver_id);
            }

            return $created_count; // Return number of approvers actually created
        }

        // Return false if no approvers found
        Log::warning("No approvers found for document_type: {$document_type}, project: {$project}, department_id: {$department_id}");
        return false;
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
        // Find and update the approval plan with the decision
        $approval_plan = ApprovalPlan::findOrFail($id);
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

        // Handle document approval (when all approvers have approved)
        if ($approved_count === $approval_plans->count()) {
            // Update document status to approved
            $updateData = [
                'status' => 'approved',
                'approved_at' => $approval_plan->updated_at,
            ];

            $document->update($updateData);
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
}
