<?php

namespace App\Http\Controllers;

use App\Models\ApprovalPlan;
use App\Models\ApprovalStage;
use App\Models\Officialtravel;
use App\Models\RecruitmentRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ApprovalRequestController extends Controller
{
    /**
     * Display a listing of approval requests for the current user
     */
    public function index()
    {
        $title = 'Approval Requests';
        $subtitle = 'Pending Approvals';

        return view('approval-requests.index', compact('title', 'subtitle'));
    }

    /**
     * Get approval requests for DataTables
     */
    public function getApprovalRequests(Request $request)
    {
        try {
            $approvalPlans = ApprovalPlan::with([
                'approver',
                'officialtravel.creator',
                'recruitment_request.createdBy'
            ])
                ->where('approver_id', Auth::id())
                ->where('is_open', true)
                ->where('status', 0); // pending

            // Filter by document type
            if (!empty($request->get('document_type'))) {
                $approvalPlans->where('document_type', $request->get('document_type'));
            }

            // Filter by date range
            if (!empty($request->get('date1')) && !empty($request->get('date2'))) {
                $approvalPlans->whereBetween('created_at', [
                    $request->get('date1'),
                    $request->get('date2')
                ]);
            }

            // Global search
            if (!empty($request->get('search'))) {
                $search = $request->get('search');
                $approvalPlans->where(function ($query) use ($search) {
                    // Search in approval plans table
                    $query->where('document_type', 'LIKE', "%$search%");

                    // Search in related documents
                    $query->orWhereHas('officialtravel', function ($q) use ($search) {
                        $q->where('official_travel_number', 'LIKE', "%$search%")
                            ->orWhere('destination', 'LIKE', "%$search%");
                    });

                    $query->orWhereHas('recruitment_request', function ($q) use ($search) {
                        $q->where('request_number', 'LIKE', "%$search%")
                            ->orWhere('position_title', 'LIKE', "%$search%");
                    });
                });
            }

            $approvalPlans->orderBy('created_at', 'desc');

            return datatables()->of($approvalPlans)
                ->addIndexColumn()
                ->addColumn('document_type', function ($approvalPlan) {
                    return ucfirst(str_replace('_', ' ', $approvalPlan->document_type));
                })
                ->addColumn('document_number', function ($approvalPlan) {
                    if ($approvalPlan->document_type === 'officialtravel') {
                        return $approvalPlan->officialtravel ? $approvalPlan->officialtravel->official_travel_number : '-';
                    } elseif ($approvalPlan->document_type === 'recruitment_request') {
                        return $approvalPlan->recruitment_request ? $approvalPlan->recruitment_request->request_number : '-';
                    }
                    return '-';
                })
                ->addColumn('current_approval', function ($approvalPlan) {
                    $currentInfo = $this->getCurrentApprovalInfo($approvalPlan->document_id, $approvalPlan->document_type);

                    if (!$currentInfo) {
                        return '<span class="badge badge-secondary">No Info</span>';
                    }

                    $statusClass = match ($currentInfo['status']) {
                        'pending' => 'badge-warning',
                        'completed' => 'badge-success',
                        'rejected' => 'badge-danger',
                        default => 'badge-secondary'
                    };

                    $statusText = ucfirst($currentInfo['status']);
                    $message = $currentInfo['message'];
                    $progress = "({$currentInfo['completed_orders']}/{$currentInfo['total_orders']})";

                    return "
                        <div class='text-left'>
                            <span class='badge {$statusClass}'>{$statusText}</span>
                            <br>
                            <small class='text-muted'>{$message}</small>
                            <br>
                            <small class='text-info'>{$progress}</small>
                        </div>
                    ";
                })
                ->addColumn('remarks', function ($approvalPlan) {
                    if ($approvalPlan->document_type === 'officialtravel') {
                        return $approvalPlan->officialtravel && $approvalPlan->officialtravel->traveler
                            ? ($approvalPlan->officialtravel->traveler->nik . ' - ' . ($approvalPlan->officialtravel->traveler->employee->fullname ?? '-'))
                            : '-';
                    } elseif ($approvalPlan->document_type === 'recruitment_request') {
                        return $approvalPlan->recruitment_request ? $approvalPlan->recruitment_request->position->position_name : '-';
                    }
                    return '-';
                })
                ->addColumn('submitted_by', function ($approvalPlan) {
                    if ($approvalPlan->document_type === 'officialtravel') {
                        return $approvalPlan->officialtravel && $approvalPlan->officialtravel->creator ?
                            $approvalPlan->officialtravel->creator->name : '-';
                    } elseif ($approvalPlan->document_type === 'recruitment_request') {
                        return $approvalPlan->recruitment_request && $approvalPlan->recruitment_request->createdBy ?
                            $approvalPlan->recruitment_request->createdBy->name : '-';
                    }
                    return '-';
                })
                ->addColumn('submitted_at', function ($approvalPlan) {
                    if ($approvalPlan->document_type === 'officialtravel') {
                        return $approvalPlan->officialtravel && $approvalPlan->officialtravel->submit_at ?
                            date('d/m/Y H:i', strtotime($approvalPlan->officialtravel->submit_at)) : '-';
                    } elseif ($approvalPlan->document_type === 'recruitment_request') {
                        return $approvalPlan->recruitment_request && $approvalPlan->recruitment_request->submit_at ?
                            date('d/m/Y H:i', strtotime($approvalPlan->recruitment_request->submit_at)) : '-';
                    }
                    return '-';
                })
                ->addColumn('status', function ($approvalPlan) {
                    return '<span class="badge badge-warning">Pending</span>';
                })
                ->addColumn('action', function ($model) {
                    return view('approval-requests.action', compact('model'))->render();
                })
                ->rawColumns(['action', 'status', 'current_approval'])
                ->toJson();
        } catch (\Exception $e) {
            Log::error('Error in getApprovalRequests: ' . $e->getMessage());
            return response()->json([
                'error' => 'Failed to load approval requests: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Show approval form for a specific request
     */
    public function show($id)
    {
        $approvalPlan = ApprovalPlan::with([
            'approver'
        ])->findOrFail($id);

        // Check if current user is the approver
        if ($approvalPlan->approver_id !== Auth::id()) {
            return redirect()->back()->with('toast_error', 'You are not authorized to approve this request.');
        }

        // Check if already processed
        if ($approvalPlan->status !== 0) {
            return redirect()->back()->with('toast_error', 'This request has already been processed.');
        }

        $title = 'Approval Request';
        $subtitle = 'Review and Approve';

        // Get current approval information
        $currentApprovalInfo = $this->getCurrentApprovalInfo($approvalPlan->document_id, $approvalPlan->document_type);

        return view('approval-requests.show', compact('title', 'subtitle', 'approvalPlan', 'currentApprovalInfo'));
    }

    /**
     * Process approval decision
     */
    public function processApproval(Request $request, $id)
    {
        try {
            $approvalPlan = ApprovalPlan::findOrFail($id);

            // Check if current user is the approver
            if ($approvalPlan->approver_id !== Auth::id()) {
                return redirect()->back()->with('toast_error', 'You are not authorized to approve this request.');
            }

            // Check if already processed
            if ($approvalPlan->status !== 0) {
                return redirect()->back()->with('toast_error', 'This request has already been processed.');
            }

            // Check sequential approval order
            if (!$this->canProcessApproval($approvalPlan)) {
                return redirect()->back()->with('toast_error', 'Previous approvals must be completed first. Please wait for earlier approvers to process their approvals.');
            }

            $this->validate($request, [
                'status' => 'required|in:1,2', // 1=approved, 2=reject
                'remarks' => 'nullable|string|max:500',
            ]);

            // Update approval plan
            $approvalPlan->update([
                'status' => $request->status,
                'remarks' => $request->remarks,
                'is_read' => $request->remarks ? 0 : 1,
            ]);

            // Process document status based on approval decision
            $this->processDocumentStatus($approvalPlan);

            $statusText = match ($request->status) {
                1 => 'approved',
                2 => 'rejected',
                default => 'processed'
            };

            // Clear cache for pending approvals count
            $this->clearPendingApprovalsCache();

            return redirect()->route('approval.requests.index')
                ->with('toast_success', "Request has been {$statusText} successfully.");
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('toast_error', 'Failed to process approval. ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Clear cache for pending approvals count
     */
    private function clearPendingApprovalsCache()
    {
        cache()->forget('pending_approvals_' . Auth::id());
    }

    /**
     * Check if current approval can be processed based on sequential order
     */
    private function canProcessApproval($approvalPlan)
    {
        // Get approval stage to check approval order
        $approvalStage = ApprovalStage::where('document_type', $approvalPlan->document_type)
            ->where('approver_id', $approvalPlan->approver_id)
            ->whereHas('details', function ($query) use ($approvalPlan) {
                $query->where('project_id', $this->getDocumentProjectId($approvalPlan))
                    ->where('department_id', $this->getDocumentDepartmentId($approvalPlan));
            })
            ->first();

        // If no approval stage found, allow processing
        if (!$approvalStage) {
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
     * Get project ID from document
     */
    private function getDocumentProjectId($approvalPlan)
    {
        if ($approvalPlan->document_type === 'officialtravel') {
            $document = Officialtravel::find($approvalPlan->document_id);
            return $document ? $document->project_id : null;
        } elseif ($approvalPlan->document_type === 'recruitment_request') {
            $document = RecruitmentRequest::find($approvalPlan->document_id);
            return $document ? $document->project_id : null;
        }
        return null;
    }

    /**
     * Get department ID from document
     */
    private function getDocumentDepartmentId($approvalPlan)
    {
        if ($approvalPlan->document_type === 'officialtravel') {
            $document = Officialtravel::find($approvalPlan->document_id);
            return $document ? $document->department_id : null;
        } elseif ($approvalPlan->document_type === 'recruitment_request') {
            $document = RecruitmentRequest::find($approvalPlan->document_id);
            return $document ? $document->department_id : null;
        }
        return null;
    }

    /**
     * Get current approval information for a document
     */
    private function getCurrentApprovalInfo($documentId, $documentType)
    {
        // Get all approval plans for this document ordered by approval_order
        $allApprovalPlans = ApprovalPlan::with('approver')->where('document_id', $documentId)
            ->where('document_type', $documentType)
            ->where('is_open', true)
            ->orderBy('approval_order', 'asc')
            ->get();

        if ($allApprovalPlans->isEmpty()) {
            return null;
        }

        // Check if there are any rejections
        $rejectedApprovals = $allApprovalPlans->where('status', 2);
        if ($rejectedApprovals->isNotEmpty()) {
            $firstRejection = $rejectedApprovals->first();
            return [
                'status' => 'rejected',
                'current_approver' => $firstRejection->approver->name,
                'current_order' => $firstRejection->approval_order,
                'total_orders' => $allApprovalPlans->count(),
                'completed_orders' => $allApprovalPlans->where('status', 1)->count(),
                'rejected_orders' => $allApprovalPlans->where('status', 2)->count(),
                'message' => 'Document rejected'
            ];
        }

        // Find pending approvals
        $pendingApprovals = $allApprovalPlans->where('status', 0);

        if ($pendingApprovals->isEmpty()) {
            // All approvals are completed
            $lastApproval = $allApprovalPlans->where('status', 1)->last();
            return [
                'status' => 'completed',
                'current_approver' => $lastApproval ? $lastApproval->approver->name : 'Unknown',
                'current_order' => $lastApproval ? $lastApproval->approval_order : null,
                'total_orders' => $allApprovalPlans->count(),
                'completed_orders' => $allApprovalPlans->where('status', 1)->count(),
                'rejected_orders' => $allApprovalPlans->where('status', 2)->count(),
                'message' => 'All approvals completed'
            ];
        }

        // Find the current approval order (minimum order among pending)
        $currentOrder = $pendingApprovals->min('approval_order');

        // Get all pending approvals for the current order
        $currentOrderApprovals = $pendingApprovals->where('approval_order', $currentOrder);

        // Check if current order has multiple approvers (parallel approval)
        $isParallel = $currentOrderApprovals->count() > 1;

        // Prepare approver information
        if ($isParallel) {
            // Multiple approvers for the same order (parallel)
            $approverNames = $currentOrderApprovals->pluck('approver.name')->toArray();
            $approversList = implode(', ', array_slice($approverNames, 0, -1)) .
                (count($approverNames) > 1 ? ' and ' . end($approverNames) : $approverNames[0]);

            $message = "Waiting for {$approversList} (Step {$currentOrder} - Parallel)";
            $currentApprover = $approversList;
        } else {
            // Single approver for this order (sequential)
            $singleApproval = $currentOrderApprovals->first();
            $currentApprover = $singleApproval->approver->name;
            $message = "Waiting for {$currentApprover} (Step {$currentOrder})";
        }

        return [
            'status' => 'pending',
            'current_approver' => $currentApprover,
            'current_order' => $currentOrder,
            'total_orders' => $allApprovalPlans->count(),
            'completed_orders' => $allApprovalPlans->where('status', 1)->count(),
            'rejected_orders' => 0,
            'is_sequential' => !$isParallel,
            'is_parallel' => $isParallel,
            'parallel_approvers_count' => $currentOrderApprovals->count(),
            'message' => $message
        ];
    }

    /**
     * Check if all sequential approvals are completed
     */
    private function areAllSequentialApprovalsCompleted($approvalPlan, $allApprovalPlans)
    {
        // Get approval stage to check if sequential approval is required
        $approvalStage = ApprovalStage::where('document_type', $approvalPlan->document_type)
            ->where('approver_id', $approvalPlan->approver_id)
            ->whereHas('details', function ($query) use ($approvalPlan) {
                $query->where('project_id', $this->getDocumentProjectId($approvalPlan))
                    ->where('department_id', $this->getDocumentDepartmentId($approvalPlan));
            })
            ->first();

        // All approvals are sequential by default based on approval_order
        // Check if all approvals are completed
        $approvedCount = $allApprovalPlans->where('status', 1)->count();
        $pendingCount = $allApprovalPlans->where('status', 0)->count();
        return $approvedCount === $allApprovalPlans->count() && $pendingCount === 0;

        // For sequential approval, check if all previous orders are approved
        $maxOrder = $allApprovalPlans->max('approval_order');

        for ($order = 1; $order <= $maxOrder; $order++) {
            $approvalAtOrder = $allApprovalPlans->where('approval_order', $order)->first();

            if (!$approvalAtOrder || $approvalAtOrder->status !== 1) {
                return false; // This order is not approved yet
            }
        }

        return true; // All sequential approvals completed
    }

    /**
     * Check if current user can process this specific approval step
     */
    private function canCurrentUserProcessThisStep($approvalPlan, $allApprovalPlans)
    {
        // Get approval stage to check if sequential approval is required
        $approvalStage = ApprovalStage::where('document_type', $approvalPlan->document_type)
            ->where('approver_id', $approvalPlan->approver_id)
            ->whereHas('details', function ($query) use ($approvalPlan) {
                $query->where('project_id', $this->getDocumentProjectId($approvalPlan))
                    ->where('department_id', $this->getDocumentDepartmentId($approvalPlan));
            })
            ->first();

        // All approvals are sequential by default based on approval_order
        // Allow processing for first order, check previous orders for others

        // For sequential approval, check if all previous orders are approved
        $currentOrder = $approvalPlan->approval_order;

        // If this is the first order (order 1), allow processing
        if ($currentOrder <= 1) {
            return true;
        }

        // Check if all previous orders are approved
        for ($order = 1; $order < $currentOrder; $order++) {
            $previousApproval = $allApprovalPlans->where('approval_order', $order)->first();

            if (!$previousApproval || $previousApproval->status !== 1) {
                return false; // Previous order is not approved yet
            }
        }

        return true; // All previous orders are approved
    }

    /**
     * Process document status based on approval decision
     */
    private function processDocumentStatus($approvalPlan)
    {
        $documentType = $approvalPlan->document_type;

        // Get document based on type
        if ($documentType === 'officialtravel') {
            $document = Officialtravel::find($approvalPlan->document_id);
        } elseif ($documentType === 'recruitment_request') {
            $document = RecruitmentRequest::find($approvalPlan->document_id);
        } else {
            return; // Invalid document type
        }

        if (!$document) {
            return; // Document not found
        }

        // Get all approval plans for this document ordered by approval_order
        $allApprovalPlans = ApprovalPlan::where('document_id', $approvalPlan->document_id)
            ->where('document_type', $documentType)
            ->where('is_open', true)
            ->orderBy('approval_order', 'asc')
            ->get();

        // Count different decisions
        $approvedCount = $allApprovalPlans->where('status', 1)->count();
        $rejectedCount = $allApprovalPlans->where('status', 2)->count();
        $pendingCount = $allApprovalPlans->where('status', 0)->count();

        // If current approval is rejected, immediately reject the document
        if ($approvalPlan->status === 2) {
            $document->update([
                'status' => 'rejected',
                'rejected_at' => now(),
            ]);

            // Close all remaining approval plans
            $this->closeAllApprovalPlans($document->id, $documentType);

            Log::info("Document {$documentType} ID: {$document->id} rejected by approver {$approvalPlan->approver_id}. All approval plans closed.");
            return;
        }

        // If any previous approval was rejected, reject the document
        if ($rejectedCount > 0) {
            $document->update(['status' => 'rejected']);
            $this->closeAllApprovalPlans($document->id, $documentType);
            return;
        }

        // Check if all sequential approvals are completed
        if ($this->areAllSequentialApprovalsCompleted($approvalPlan, $allApprovalPlans)) {
            $document->update([
                'status' => 'approved',
                'approved_at' => now(),
            ]);
            $this->closeAllApprovalPlans($document->id, $documentType);

            Log::info("Document {$documentType} ID: {$document->id} approved. All sequential approvals completed.");
        }
    }

    /**
     * Close all approval plans for a document
     */
    private function closeAllApprovalPlans($documentId, $documentType)
    {
        ApprovalPlan::where('document_id', $documentId)
            ->where('document_type', $documentType)
            ->where('is_open', true)
            ->update(['is_open' => false]);
    }

    /**
     * Filter approval requests by type
     */
    public function filterByType(Request $request)
    {
        try {
            $documentType = $request->get('document_type', 'all');

            $query = ApprovalPlan::where('approver_id', Auth::id())
                ->where('is_open', true)
                ->where('status', 0);

            if ($documentType !== 'all') {
                $query->where('document_type', $documentType);
            }

            $approvalPlans = $query->get();

            return response()->json([
                'success' => true,
                'data' => $approvalPlans
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to filter requests: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Bulk approve multiple requests
     */
    public function bulkApprove(Request $request)
    {
        try {
            $this->validate($request, [
                'ids' => 'required|array|min:1',
                'ids.*' => 'required|integer|exists:approval_plans,id',
                'remarks' => 'nullable|string|max:500',
            ]);

            $successCount = 0;
            $failCount = 0;
            $errors = [];

            // Use database transaction for data consistency
            DB::beginTransaction();

            try {
                foreach ($request->ids as $id) {
                    try {
                        $approvalPlan = ApprovalPlan::findOrFail($id);

                        // Check if current user is the approver and status is pending
                        if ($approvalPlan->approver_id !== Auth::id()) {
                            $errors[] = "Request ID {$id}: You are not authorized to approve this request.";
                            $failCount++;
                            continue;
                        }

                        if ($approvalPlan->status !== 0) {
                            $errors[] = "Request ID {$id}: Request has already been processed.";
                            $failCount++;
                            continue;
                        }

                        // Check sequential approval order for bulk approve
                        if (!$this->canProcessApproval($approvalPlan)) {
                            $errors[] = "Request ID {$id}: Previous approvals must be completed first.";
                            $failCount++;
                            continue;
                        }

                        // Update approval plan
                        $approvalPlan->update([
                            'status' => 1, // approved
                            'remarks' => $request->remarks,
                            'is_read' => $request->remarks ? 0 : 1,
                            'approved_at' => now(),
                        ]);

                        // Process document status
                        $this->processDocumentStatus($approvalPlan);

                        $successCount++;

                        // Log successful approval
                        Log::info("Bulk approval successful", [
                            'approval_plan_id' => $approvalPlan->id,
                            'document_type' => $approvalPlan->document_type,
                            'document_id' => $approvalPlan->document_id,
                            'approver_id' => Auth::id(),
                            'remarks' => $request->remarks
                        ]);
                    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                        $errors[] = "Request ID {$id}: Request not found.";
                        $failCount++;
                        continue;
                    } catch (\Exception $e) {
                        $errors[] = "Request ID {$id}: " . $e->getMessage();
                        $failCount++;
                        continue;
                    }
                }

                // If any successful approvals, commit transaction
                if ($successCount > 0) {
                    DB::commit();

                    // Clear cache for pending approvals count
                    $this->clearPendingApprovalsCache();

                    $message = "{$successCount} request(s) approved successfully";
                    if ($failCount > 0) {
                        $message .= " ({$failCount} failed)";
                    }

                    return response()->json([
                        'success' => true,
                        'message' => $message,
                        'successCount' => $successCount,
                        'failCount' => $failCount
                    ]);
                } else {
                    // No successful approvals, rollback transaction
                    DB::rollBack();

                    $errorMessage = 'No requests could be approved.';
                    if (!empty($errors)) {
                        $errorMessage .= ' ' . implode(' ', array_slice($errors, 0, 3));
                        if (count($errors) > 3) {
                            $errorMessage .= ' and ' . (count($errors) - 3) . ' more errors.';
                        }
                    }

                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage,
                        'errors' => $errors
                    ], 400);
                }
            } catch (\Exception $e) {
                DB::rollBack();
                throw $e;
            }
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed. Please check your input.',
                'errors' => $e->validator->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Bulk approval failed: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'request_ids' => $request->ids ?? [],
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process bulk approval. Please try again.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
