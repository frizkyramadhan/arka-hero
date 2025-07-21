<?php

namespace App\Http\Controllers;

use App\Models\DocumentApproval;
use App\Models\ApprovalAction;
use App\Models\ApprovalFlow;
use App\Models\ApprovalStage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ApprovalDashboardController extends Controller
{
    /**
     * Show unified approval dashboard
     */
    public function index()
    {
        $title = 'Approval Dashboard';

        // Get user's pending approvals
        $pendingApprovals = $this->getPendingApprovals();

        // Get user's recent actions
        $recentActions = $this->getRecentActions();

        // Get approval statistics
        $stats = $this->getUserStats();

        // Get approval history
        $recentHistory = $this->getRecentHistory();

        return view('approval.dashboard.index', compact('title', 'pendingApprovals', 'recentActions', 'stats', 'recentHistory'));
    }

    /**
     * Show pending approvals
     */
    public function pending()
    {
        $title = 'Pending Approvals';

        $pendingApprovals = $this->getPendingApprovals();

        return view('approval.dashboard.pending', compact('title', 'pendingApprovals'));
    }

    /**
     * Show approval history
     */
    public function history()
    {
        $title = 'Approval History';

        $history = $this->getApprovalHistory();

        return view('approval.dashboard.history', compact('title', 'history'));
    }

    /**
     * Process approval action
     */
    public function process(Request $request, DocumentApproval $approval)
    {
        $title = 'Process Approval';

        $request->validate([
            'action' => 'required|in:approve,reject,forward,delegate',
            'comments' => 'nullable|string|max:1000',
            'forward_to' => 'required_if:action,forward|exists:users,id',
            'delegate_to' => 'required_if:action,delegate|exists:users,id'
        ]);

        try {
            DB::beginTransaction();

            $result = $this->processApprovalAction($approval, $request->all());

            if ($result['success']) {
                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => $result['message'],
                    'redirect' => route('approval.dashboard.index')
                ]);
            } else {
                DB::rollBack();

                return response()->json([
                    'success' => false,
                    'message' => $result['message']
                ], 400);
            }
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to process approval', [
                'error' => $e->getMessage(),
                'approval_id' => $approval->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process approval'
            ], 500);
        }
    }

    /**
     * Bulk approval actions
     */
    public function bulk(Request $request)
    {
        $title = 'Bulk Approval Actions';

        $request->validate([
            'approval_ids' => 'required|array',
            'approval_ids.*' => 'exists:document_approvals,id',
            'action' => 'required|in:approve,reject',
            'comments' => 'nullable|string|max:1000'
        ]);

        try {
            DB::beginTransaction();

            $results = [];
            $successCount = 0;
            $errorCount = 0;

            foreach ($request->approval_ids as $approvalId) {
                $approval = DocumentApproval::find($approvalId);

                if ($approval && $this->canProcessApproval($approval)) {
                    $result = $this->processApprovalAction($approval, [
                        'action' => $request->action,
                        'comments' => $request->comments
                    ]);

                    if ($result['success']) {
                        $successCount++;
                    } else {
                        $errorCount++;
                    }

                    $results[] = [
                        'approval_id' => $approvalId,
                        'success' => $result['success'],
                        'message' => $result['message']
                    ];
                } else {
                    $errorCount++;
                    $results[] = [
                        'approval_id' => $approvalId,
                        'success' => false,
                        'message' => 'Cannot process this approval'
                    ];
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "Processed {$successCount} approvals successfully. {$errorCount} failed.",
                'results' => $results
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            Log::error('Failed to process bulk approvals', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process bulk approvals'
            ], 500);
        }
    }

    /**
     * Get approval details
     */
    public function show(DocumentApproval $approval)
    {
        $title = 'Approval Details';

        // Check if user can view this approval
        if (!$this->canViewApproval($approval)) {
            abort(403, 'You cannot view this approval');
        }

        $approval->load(['approvalFlow.stages.approvers.user', 'approvalFlow.stages.approvers.role', 'approvalFlow.stages.approvers.department', 'actions.approver', 'submittedBy']);

        return view('approval.dashboard.show', compact('title', 'approval'));
    }

    /**
     * Get pending approvals data via AJAX
     */
    public function getPendingData(): JsonResponse
    {
        try {
            $pendingApprovals = $this->getPendingApprovals();

            return response()->json([
                'success' => true,
                'approvals' => $pendingApprovals
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get pending approvals data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get pending approvals data'
            ], 500);
        }
    }

    /**
     * Get user statistics via AJAX
     */
    public function getUserStatsData(): JsonResponse
    {
        try {
            $stats = $this->getUserStats();

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get user stats data', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get user stats data'
            ], 500);
        }
    }

    /**
     * Get pending approvals for current user
     */
    private function getPendingApprovals(): array
    {
        $user = auth()->user();

        // Get approvals where user is an approver
        $pendingApprovals = DocumentApproval::with(['approvalFlow', 'currentStage', 'submittedBy'])
            ->where('overall_status', 'pending')
            ->whereHas('currentStage.approvers', function ($query) use ($user) {
                $query->where(function ($q) use ($user) {
                    $q->where('approver_type', 'user')->where('approver_id', $user->id)
                        ->orWhere('approver_type', 'role')->whereIn('approver_id', $user->roles->pluck('id'))
                        ->orWhere('approver_type', 'department')->where('approver_id', $user->department_id);
                });
            })
            ->orderBy('submitted_at', 'asc')
            ->get()
            ->map(function ($approval) {
                return [
                    'id' => $approval->id,
                    'document_type' => $approval->document_type,
                    'document_id' => $approval->document_id,
                    'flow_name' => $approval->approvalFlow->name ?? 'Unknown',
                    'current_stage' => $approval->currentStage->stage_name ?? 'Unknown',
                    'submitted_by' => $approval->submittedBy->name ?? 'Unknown',
                    'submitted_at' => $approval->submitted_at->format('Y-m-d H:i:s'),
                    'days_pending' => $approval->submitted_at->diffInDays(now()),
                    'priority' => $this->calculatePriority($approval),
                    'can_approve' => $this->canProcessApproval($approval)
                ];
            })
            ->toArray();

        return $pendingApprovals;
    }

    /**
     * Get recent actions by current user
     */
    private function getRecentActions(): array
    {
        $user = auth()->user();

        $recentActions = ApprovalAction::with(['documentApproval.approvalFlow', 'approvalStage'])
            ->where('approver_id', $user->id)
            ->orderBy('action_date', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($action) {
                return [
                    'id' => $action->id,
                    'action' => $action->action,
                    'document_type' => $action->documentApproval->document_type ?? 'Unknown',
                    'flow_name' => $action->documentApproval->approvalFlow->name ?? 'Unknown',
                    'stage' => $action->approvalStage->stage_name ?? 'Unknown',
                    'action_date' => $action->action_date->format('Y-m-d H:i:s'),
                    'comments' => $action->comments
                ];
            })
            ->toArray();

        return $recentActions;
    }

    /**
     * Get user statistics
     */
    private function getUserStats(): array
    {
        $user = auth()->user();
        $last30Days = Carbon::now()->subDays(30);

        // Get approvals where user is an approver
        $userApprovals = DocumentApproval::whereHas('currentStage.approvers', function ($query) use ($user) {
            $query->where(function ($q) use ($user) {
                $q->where('approver_type', 'user')->where('approver_id', $user->id)
                    ->orWhere('approver_type', 'role')->whereIn('approver_id', $user->roles->pluck('id'))
                    ->orWhere('approver_type', 'department')->where('approver_id', $user->department_id);
            });
        });

        $pendingCount = $userApprovals->where('overall_status', 'pending')->count();
        $approvedCount = ApprovalAction::where('approver_id', $user->id)
            ->where('action', 'approved')
            ->where('action_date', '>=', $last30Days)
            ->count();
        $rejectedCount = ApprovalAction::where('approver_id', $user->id)
            ->where('action', 'rejected')
            ->where('action_date', '>=', $last30Days)
            ->count();

        $totalActions = $approvedCount + $rejectedCount;
        $approvalRate = $totalActions > 0 ? round(($approvedCount / $totalActions) * 100, 2) : 0;

        return [
            'pending_count' => $pendingCount,
            'approved_count' => $approvedCount,
            'rejected_count' => $rejectedCount,
            'approval_rate' => $approvalRate,
            'total_actions' => $totalActions
        ];
    }

    /**
     * Get recent approval history
     */
    private function getRecentHistory(): array
    {
        $user = auth()->user();

        $history = ApprovalAction::with(['documentApproval.approvalFlow', 'approvalStage'])
            ->where('approver_id', $user->id)
            ->orderBy('action_date', 'desc')
            ->limit(20)
            ->get()
            ->map(function ($action) {
                return [
                    'id' => $action->id,
                    'action' => $action->action,
                    'document_type' => $action->documentApproval->document_type ?? 'Unknown',
                    'flow_name' => $action->documentApproval->approvalFlow->name ?? 'Unknown',
                    'stage' => $action->approvalStage->stage_name ?? 'Unknown',
                    'action_date' => $action->action_date->format('Y-m-d H:i:s'),
                    'comments' => $action->comments,
                    'status' => $action->documentApproval->overall_status ?? 'Unknown'
                ];
            })
            ->toArray();

        return $history;
    }

    /**
     * Get full approval history with pagination
     */
    private function getApprovalHistory()
    {
        $user = auth()->user();

        return ApprovalAction::with(['documentApproval.approvalFlow', 'approvalStage'])
            ->where('approver_id', $user->id)
            ->orderBy('action_date', 'desc')
            ->paginate(50);
    }

    /**
     * Process approval action
     */
    private function processApprovalAction(DocumentApproval $approval, array $data): array
    {
        $user = auth()->user();

        // Check if user can process this approval
        if (!$this->canProcessApproval($approval)) {
            return [
                'success' => false,
                'message' => 'You cannot process this approval'
            ];
        }

        $action = $data['action'];
        $comments = $data['comments'] ?? '';

        try {
            // Create approval action record
            ApprovalAction::create([
                'document_approval_id' => $approval->id,
                'approval_stage_id' => $approval->current_stage_id,
                'approver_id' => $user->id,
                'action' => $action,
                'comments' => $comments,
                'action_date' => now(),
                'forwarded_to' => $data['forward_to'] ?? null,
                'delegated_to' => $data['delegate_to'] ?? null
            ]);

            // Update approval status based on action
            if ($action === 'approve') {
                $this->handleApproval($approval);
            } elseif ($action === 'reject') {
                $this->handleRejection($approval);
            } elseif ($action === 'forward') {
                $this->handleForward($approval, $data['forward_to']);
            } elseif ($action === 'delegate') {
                $this->handleDelegation($approval, $data['delegate_to']);
            }

            // Log the action
            Log::info('Approval action processed', [
                'approval_id' => $approval->id,
                'user_id' => $user->id,
                'action' => $action,
                'comments' => $comments
            ]);

            return [
                'success' => true,
                'message' => ucfirst($action) . ' processed successfully'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to process approval action', [
                'error' => $e->getMessage(),
                'approval_id' => $approval->id,
                'user_id' => $user->id,
                'action' => $action
            ]);

            return [
                'success' => false,
                'message' => 'Failed to process approval action'
            ];
        }
    }

    /**
     * Handle approval action
     */
    private function handleApproval(DocumentApproval $approval)
    {
        // Check if this is the final stage
        $currentStage = $approval->currentStage;
        $nextStage = $this->getNextStage($approval);

        if ($nextStage) {
            // Move to next stage
            $approval->update([
                'current_stage_id' => $nextStage->id
            ]);
        } else {
            // Final approval - complete the approval
            $approval->update([
                'overall_status' => 'approved',
                'completed_at' => now()
            ]);
        }
    }

    /**
     * Handle rejection
     */
    private function handleRejection(DocumentApproval $approval)
    {
        $approval->update([
            'overall_status' => 'rejected',
            'completed_at' => now()
        ]);
    }

    /**
     * Handle forward action
     */
    private function handleForward(DocumentApproval $approval, $forwardToUserId)
    {
        // Forward to another user
        $approval->update([
            'current_stage_id' => null // Reset to allow reassignment
        ]);

        // Create new approval action for forwarded user
        ApprovalAction::create([
            'document_approval_id' => $approval->id,
            'approval_stage_id' => $approval->current_stage_id,
            'approver_id' => $forwardToUserId,
            'action' => 'forwarded',
            'action_date' => now()
        ]);
    }

    /**
     * Handle delegation
     */
    private function handleDelegation(DocumentApproval $approval, $delegateToUserId)
    {
        // Delegate to another user
        ApprovalAction::create([
            'document_approval_id' => $approval->id,
            'approval_stage_id' => $approval->current_stage_id,
            'approver_id' => $delegateToUserId,
            'action' => 'delegated',
            'action_date' => now()
        ]);
    }

    /**
     * Get next stage in approval flow
     */
    private function getNextStage(DocumentApproval $approval)
    {
        $currentStage = $approval->currentStage;

        if (!$currentStage) {
            return null;
        }

        return ApprovalStage::where('approval_flow_id', $approval->approval_flow_id)
            ->where('stage_order', '>', $currentStage->stage_order)
            ->orderBy('stage_order')
            ->first();
    }

    /**
     * Check if user can process approval
     */
    private function canProcessApproval(DocumentApproval $approval): bool
    {
        $user = auth()->user();

        if (!$approval->currentStage) {
            return false;
        }

        // Check if user is an approver for current stage
        return $approval->currentStage->approvers()
            ->where(function ($query) use ($user) {
                $query->where('approver_type', 'user')->where('approver_id', $user->id)
                    ->orWhere('approver_type', 'role')->whereIn('approver_id', $user->roles->pluck('id'))
                    ->orWhere('approver_type', 'department')->where('approver_id', $user->department_id);
            })
            ->exists();
    }

    /**
     * Check if user can view approval
     */
    private function canViewApproval(DocumentApproval $approval): bool
    {
        $user = auth()->user();

        // User can view if they are:
        // 1. The submitter
        // 2. An approver for any stage
        // 3. Admin/supervisor

        if ($approval->submitted_by === $user->id) {
            return true;
        }

        if ($user->hasRole(['admin', 'supervisor'])) {
            return true;
        }

        return $approval->approvalFlow->stages()
            ->whereHas('approvers', function ($query) use ($user) {
                $query->where(function ($q) use ($user) {
                    $q->where('approver_type', 'user')->where('approver_id', $user->id)
                        ->orWhere('approver_type', 'role')->whereIn('approver_id', $user->roles->pluck('id'))
                        ->orWhere('approver_type', 'department')->where('approver_id', $user->department_id);
                });
            })
            ->exists();
    }

    /**
     * Calculate priority for approval
     */
    private function calculatePriority(DocumentApproval $approval): string
    {
        $daysPending = $approval->submitted_at->diffInDays(now());

        if ($daysPending > 7) {
            return 'high';
        } elseif ($daysPending > 3) {
            return 'medium';
        } else {
            return 'low';
        }
    }

    /**
     * Get document information
     */
    public function getDocumentInfo(DocumentApproval $approval): JsonResponse
    {
        try {
            $documentInfo = $this->loadDocumentInformation($approval);

            return response()->json([
                'success' => true,
                'html' => $documentInfo
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get document info', [
                'error' => $e->getMessage(),
                'approval_id' => $approval->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load document information'
            ], 500);
        }
    }

    /**
     * Get action details
     */
    public function getActionDetails(ApprovalAction $action): JsonResponse
    {
        try {
            $actionDetails = $this->loadActionDetails($action);

            return response()->json([
                'success' => true,
                'html' => $actionDetails
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get action details', [
                'error' => $e->getMessage(),
                'action_id' => $action->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to load action details'
            ], 500);
        }
    }

    /**
     * Export approval history
     */
    public function exportHistory(Request $request)
    {
        try {
            $user = auth()->user();
            $filters = $request->all();

            $query = ApprovalAction::with(['documentApproval.approvalFlow', 'approvalStage', 'approver'])
                ->where('approver_id', $user->id);

            // Apply filters
            if (!empty($filters['action'])) {
                $query->where('action', $filters['action']);
            }

            if (!empty($filters['document_type'])) {
                $query->whereHas('documentApproval', function ($q) use ($filters) {
                    $q->where('document_type', $filters['document_type']);
                });
            }

            if (!empty($filters['date_range'])) {
                $dateRange = $this->getDateRange($filters['date_range']);
                $query->whereBetween('action_date', [$dateRange['start'], $dateRange['end']]);
            }

            if (!empty($filters['search'])) {
                $search = $filters['search'];
                $query->where(function ($q) use ($search) {
                    $q->where('comments', 'like', "%{$search}%")
                        ->orWhereHas('documentApproval', function ($subQ) use ($search) {
                            $subQ->where('document_id', 'like', "%{$search}%");
                        });
                });
            }

            $actions = $query->orderBy('action_date', 'desc')->get();

            // Generate CSV
            $filename = 'approval_history_' . date('Y-m-d_H-i-s') . '.csv';

            $headers = [
                'Content-Type' => 'text/csv',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];

            $callback = function () use ($actions) {
                $file = fopen('php://output', 'w');

                // CSV headers
                fputcsv($file, [
                    'Date & Time',
                    'Action',
                    'Document Type',
                    'Document ID',
                    'Flow',
                    'Stage',
                    'Comments',
                    'Status'
                ]);

                // CSV data
                foreach ($actions as $action) {
                    fputcsv($file, [
                        $action->action_date->format('Y-m-d H:i:s'),
                        ucfirst($action->action),
                        ucfirst(str_replace('_', ' ', $action->documentApproval->document_type ?? 'Unknown')),
                        $action->documentApproval->document_id ?? 'N/A',
                        $action->documentApproval->approvalFlow->name ?? 'Unknown',
                        $action->approvalStage->stage_name ?? 'Unknown',
                        $action->comments ?? '',
                        ucfirst($action->documentApproval->overall_status ?? 'Unknown')
                    ]);
                }

                fclose($file);
            };

            return response()->stream($callback, 200, $headers);
        } catch (\Exception $e) {
            Log::error('Failed to export approval history', [
                'error' => $e->getMessage(),
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to export approval history'
            ], 500);
        }
    }

    /**
     * Load document information
     */
    private function loadDocumentInformation(DocumentApproval $approval): string
    {
        // This would be implemented based on document type
        // For now, return a generic template
        return view('approval.dashboard.partials.document-info', compact('approval'))->render();
    }

    /**
     * Load action details
     */
    private function loadActionDetails(ApprovalAction $action): string
    {
        return view('approval.dashboard.partials.action-details', compact('action'))->render();
    }

    /**
     * Get date range for filters
     */
    private function getDateRange(string $range): array
    {
        $now = Carbon::now();

        switch ($range) {
            case 'today':
                return [
                    'start' => $now->startOfDay(),
                    'end' => $now->endOfDay()
                ];
            case 'week':
                return [
                    'start' => $now->startOfWeek(),
                    'end' => $now->endOfWeek()
                ];
            case 'month':
                return [
                    'start' => $now->startOfMonth(),
                    'end' => $now->endOfMonth()
                ];
            case 'quarter':
                return [
                    'start' => $now->startOfQuarter(),
                    'end' => $now->endOfQuarter()
                ];
            case 'year':
                return [
                    'start' => $now->startOfYear(),
                    'end' => $now->endOfYear()
                ];
            default:
                return [
                    'start' => Carbon::createFromTimestamp(0),
                    'end' => $now
                ];
        }
    }
}
