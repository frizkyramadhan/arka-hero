<?php

namespace App\Http\Controllers;

use App\Models\ApprovalPlan;
use App\Models\ApprovalStage;
use App\Models\Officialtravel;
use App\Models\RecruitmentRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
                'recruitment_request.creator'
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
                ->addColumn('document_title', function ($approvalPlan) {
                    if ($approvalPlan->document_type === 'officialtravel') {
                        return $approvalPlan->officialtravel && $approvalPlan->officialtravel->traveler
                            ? ($approvalPlan->officialtravel->traveler->nik . ' - ' . ($approvalPlan->officialtravel->traveler->employee->fullname ?? '-'))
                            : '-';
                    } elseif ($approvalPlan->document_type === 'recruitment_request') {
                        return $approvalPlan->recruitment_request ? $approvalPlan->recruitment_request->position_title : '-';
                    }
                    return '-';
                })
                ->addColumn('submitted_by', function ($approvalPlan) {
                    if ($approvalPlan->document_type === 'officialtravel') {
                        return $approvalPlan->officialtravel && $approvalPlan->officialtravel->creator ?
                            $approvalPlan->officialtravel->creator->name : '-';
                    } elseif ($approvalPlan->document_type === 'recruitment_request') {
                        return $approvalPlan->recruitment_request && $approvalPlan->recruitment_request->creator ?
                            $approvalPlan->recruitment_request->creator->name : '-';
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
                ->rawColumns(['action', 'status'])
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

        return view('approval-requests.show', compact('title', 'subtitle', 'approvalPlan'));
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

        // Get all approval plans for this document
        $allApprovalPlans = ApprovalPlan::where('document_id', $approvalPlan->document_id)
            ->where('document_type', $documentType)
            ->where('is_open', true)
            ->get();

        // Count different decisions
        $approvedCount = $allApprovalPlans->where('status', 1)->count();
        $rejectedCount = $allApprovalPlans->where('status', 2)->count();
        $pendingCount = $allApprovalPlans->where('status', 0)->count();

        // If any rejection, reject the document
        if ($rejectedCount > 0) {
            $document->update(['status' => 'rejected']);
            $this->closeAllApprovalPlans($document->id, $documentType);
            return;
        }

        // If all approvers have approved, approve the document
        if ($approvedCount === $allApprovalPlans->count() && $pendingCount === 0) {
            $document->update([
                'status' => 'approved',
                'approved_at' => now(),
            ]);
            $this->closeAllApprovalPlans($document->id, $documentType);
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
                'ids' => 'required|array',
                'ids.*' => 'required|integer',
                'remarks' => 'nullable|string|max:500',
            ]);

            $successCount = 0;
            $failCount = 0;

            foreach ($request->ids as $id) {
                $approvalPlan = ApprovalPlan::findOrFail($id);

                // Check if current user is the approver and status is pending
                if ($approvalPlan->approver_id !== Auth::id() || $approvalPlan->status !== 0) {
                    $failCount++;
                    continue;
                }

                // Update approval plan
                $approvalPlan->update([
                    'status' => 1, // approved
                    'remarks' => $request->remarks,
                    'is_read' => $request->remarks ? 0 : 1,
                ]);

                // Process document status
                $this->processDocumentStatus($approvalPlan);

                $successCount++;
            }

            if ($successCount > 0) {
                // Clear cache for pending approvals count
                $this->clearPendingApprovalsCache();

                $message = "{$successCount} request(s) approved successfully";
                if ($failCount > 0) {
                    $message .= " ({$failCount} failed)";
                }

                return redirect()->route('approval.requests.index')
                    ->with('toast_success', $message);
            } else {
                return redirect()->back()
                    ->with('toast_error', 'No requests could be approved.')
                    ->withInput();
            }
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('toast_error', 'Failed to process bulk approval. ' . $e->getMessage())
                ->withInput();
        }
    }
}
