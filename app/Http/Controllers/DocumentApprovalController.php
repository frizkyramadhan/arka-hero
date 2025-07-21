<?php

namespace App\Http\Controllers;

use App\Models\DocumentApproval;
use App\Models\ApprovalAction;
use App\Models\ApprovalStage;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class DocumentApprovalController extends Controller
{
    /**
     * Show approval details with action interface
     */
    public function show(DocumentApproval $approval)
    {
        $title = 'Approval Details';

        // Check if user can view this approval
        if (!$this->canViewApproval($approval)) {
            abort(403, 'You cannot view this approval');
        }

        $approval->load([
            'approvalFlow.stages.approvers.user',
            'approvalFlow.stages.approvers.role',
            'approvalFlow.stages.approvers.department',
            'actions.approver',
            'submittedBy',
            'currentStage.approvers.user',
            'currentStage.approvers.role',
            'currentStage.approvers.department'
        ]);

        // Get next approvers if available
        $nextApprovers = $this->getNextApprovers($approval);

        // Get approval statistics
        $stats = $this->getApprovalStats($approval);

        return view('approval.actions.show', compact('title', 'approval', 'nextApprovers', 'stats'));
    }

    /**
     * Approve document
     */
    public function approve(Request $request, DocumentApproval $approval)
    {
        $title = 'Approve Document';

        $request->validate([
            'comments' => 'nullable|string|max:1000',
            'auto_approve_next' => 'boolean'
        ]);

        try {
            DB::beginTransaction();

            $result = $this->processApprovalAction($approval, 'approve', $request->all());

            if ($result['success']) {
                DB::commit();

                // Auto-approve next stage if requested
                if ($request->boolean('auto_approve_next') && $result['next_stage']) {
                    $this->autoApproveNextStage($approval);
                }

                return response()->json([
                    'success' => true,
                    'message' => 'Document approved successfully',
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

            Log::error('Failed to approve document', [
                'error' => $e->getMessage(),
                'approval_id' => $approval->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to approve document'
            ], 500);
        }
    }

    /**
     * Reject document
     */
    public function reject(Request $request, DocumentApproval $approval)
    {
        $title = 'Reject Document';

        $request->validate([
            'comments' => 'required|string|max:1000',
            'rejection_reason' => 'required|string|max:255'
        ]);

        try {
            DB::beginTransaction();

            $result = $this->processApprovalAction($approval, 'reject', $request->all());

            if ($result['success']) {
                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Document rejected successfully',
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

            Log::error('Failed to reject document', [
                'error' => $e->getMessage(),
                'approval_id' => $approval->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to reject document'
            ], 500);
        }
    }

    /**
     * Forward approval to another user
     */
    public function forward(Request $request, DocumentApproval $approval)
    {
        $title = 'Forward Approval';

        $request->validate([
            'forward_to' => 'required|exists:users,id',
            'comments' => 'nullable|string|max:1000',
            'forward_reason' => 'required|string|max:255'
        ]);

        try {
            DB::beginTransaction();

            $result = $this->processApprovalAction($approval, 'forward', $request->all());

            if ($result['success']) {
                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Approval forwarded successfully',
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

            Log::error('Failed to forward approval', [
                'error' => $e->getMessage(),
                'approval_id' => $approval->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to forward approval'
            ], 500);
        }
    }

    /**
     * Delegate approval to another user
     */
    public function delegate(Request $request, DocumentApproval $approval)
    {
        $title = 'Delegate Approval';

        $request->validate([
            'delegate_to' => 'required|exists:users,id',
            'comments' => 'nullable|string|max:1000',
            'delegation_reason' => 'required|string|max:255',
            'delegation_duration' => 'required|integer|min:1|max:30' // days
        ]);

        try {
            DB::beginTransaction();

            $result = $this->processApprovalAction($approval, 'delegate', $request->all());

            if ($result['success']) {
                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Approval delegated successfully',
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

            Log::error('Failed to delegate approval', [
                'error' => $e->getMessage(),
                'approval_id' => $approval->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delegate approval'
            ], 500);
        }
    }

    /**
     * Request additional information
     */
    public function requestInfo(Request $request, DocumentApproval $approval)
    {
        $title = 'Request Additional Information';

        $request->validate([
            'info_request' => 'required|string|max:1000',
            'deadline' => 'required|date|after:today',
            'priority' => 'required|in:low,medium,high'
        ]);

        try {
            DB::beginTransaction();

            $result = $this->processApprovalAction($approval, 'request_info', $request->all());

            if ($result['success']) {
                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Information request sent successfully',
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

            Log::error('Failed to request additional information', [
                'error' => $e->getMessage(),
                'approval_id' => $approval->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to request additional information'
            ], 500);
        }
    }

    /**
     * Escalate approval
     */
    public function escalate(Request $request, DocumentApproval $approval)
    {
        $title = 'Escalate Approval';

        $request->validate([
            'escalation_reason' => 'required|string|max:255',
            'escalate_to' => 'required|exists:users,id'
        ]);

        try {
            DB::beginTransaction();

            $result = $this->processApprovalAction($approval, 'escalate', $request->all());

            if ($result['success']) {
                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Approval escalated successfully',
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

            Log::error('Failed to escalate approval', [
                'error' => $e->getMessage(),
                'approval_id' => $approval->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to escalate approval'
            ], 500);
        }
    }

    /**
     * Cancel approval
     */
    public function cancel(Request $request, DocumentApproval $approval)
    {
        $title = 'Cancel Approval';

        $request->validate([
            'cancellation_reason' => 'required|string|max:255'
        ]);

        try {
            DB::beginTransaction();

            $result = $this->processApprovalAction($approval, 'cancel', $request->all());

            if ($result['success']) {
                DB::commit();

                return response()->json([
                    'success' => true,
                    'message' => 'Approval cancelled successfully',
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

            Log::error('Failed to cancel approval', [
                'error' => $e->getMessage(),
                'approval_id' => $approval->id,
                'user_id' => auth()->id()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel approval'
            ], 500);
        }
    }

    /**
     * Get approval action form
     */
    public function getActionForm(Request $request, DocumentApproval $approval)
    {
        $title = 'Approval Action Form';

        $action = $request->get('action', 'approve');

        if (!$this->canProcessApproval($approval)) {
            return response()->json([
                'success' => false,
                'message' => 'You cannot process this approval'
            ], 403);
        }

        $formHtml = view('approval.actions.partials.action-form', compact('approval', 'action'))->render();

        return response()->json([
            'success' => true,
            'html' => $formHtml
        ]);
    }

    /**
     * Get approval statistics
     */
    public function getApprovalStats(DocumentApproval $approval): JsonResponse
    {
        try {
            $stats = $this->calculateApprovalStats($approval);

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get approval stats', [
                'error' => $e->getMessage(),
                'approval_id' => $approval->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get approval statistics'
            ], 500);
        }
    }

    /**
     * Process approval action
     */
    private function processApprovalAction(DocumentApproval $approval, string $action, array $data): array
    {
        $user = auth()->user();

        // Check if user can process this approval
        if (!$this->canProcessApproval($approval)) {
            return [
                'success' => false,
                'message' => 'You cannot process this approval'
            ];
        }

        $comments = $data['comments'] ?? '';
        $metadata = [];

        try {
            // Create approval action record
            $approvalAction = ApprovalAction::create([
                'document_approval_id' => $approval->id,
                'approval_stage_id' => $approval->current_stage_id,
                'approver_id' => $user->id,
                'action' => $action,
                'comments' => $comments,
                'action_date' => now(),
                'forwarded_to' => $data['forward_to'] ?? null,
                'delegated_to' => $data['delegate_to'] ?? null,
                'metadata' => $this->buildMetadata($action, $data)
            ]);

            // Update approval status based on action
            switch ($action) {
                case 'approve':
                    $result = $this->handleApproval($approval);
                    break;
                case 'reject':
                    $result = $this->handleRejection($approval);
                    break;
                case 'forward':
                    $result = $this->handleForward($approval, $data['forward_to']);
                    break;
                case 'delegate':
                    $result = $this->handleDelegation($approval, $data['delegate_to']);
                    break;
                case 'request_info':
                    $result = $this->handleRequestInfo($approval, $data);
                    break;
                case 'escalate':
                    $result = $this->handleEscalation($approval, $data);
                    break;
                case 'cancel':
                    $result = $this->handleCancellation($approval, $data);
                    break;
                default:
                    $result = ['success' => false, 'message' => 'Invalid action'];
            }

            // Log the action
            Log::info('Approval action processed', [
                'approval_id' => $approval->id,
                'user_id' => $user->id,
                'action' => $action,
                'comments' => $comments
            ]);

            return $result;
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
    private function handleApproval(DocumentApproval $approval): array
    {
        // Check if this is the final stage
        $currentStage = $approval->currentStage;
        $nextStage = $this->getNextStage($approval);

        if ($nextStage) {
            // Move to next stage
            $approval->update([
                'current_stage_id' => $nextStage->id
            ]);

            return [
                'success' => true,
                'message' => 'Approved and moved to next stage',
                'next_stage' => $nextStage
            ];
        } else {
            // Final approval - complete the approval
            $approval->update([
                'overall_status' => 'approved',
                'completed_at' => now()
            ]);

            return [
                'success' => true,
                'message' => 'Document approved successfully',
                'next_stage' => null
            ];
        }
    }

    /**
     * Handle rejection
     */
    private function handleRejection(DocumentApproval $approval): array
    {
        $approval->update([
            'overall_status' => 'rejected',
            'completed_at' => now()
        ]);

        return [
            'success' => true,
            'message' => 'Document rejected successfully'
        ];
    }

    /**
     * Handle forward action
     */
    private function handleForward(DocumentApproval $approval, $forwardToUserId): array
    {
        // Forward to another user
        $approval->update([
            'current_stage_id' => null // Reset to allow reassignment
        ]);

        return [
            'success' => true,
            'message' => 'Approval forwarded successfully'
        ];
    }

    /**
     * Handle delegation
     */
    private function handleDelegation(DocumentApproval $approval, $delegateToUserId): array
    {
        // Delegate to another user
        return [
            'success' => true,
            'message' => 'Approval delegated successfully'
        ];
    }

    /**
     * Handle request for additional information
     */
    private function handleRequestInfo(DocumentApproval $approval, array $data): array
    {
        // Update approval status to request info
        $approval->update([
            'overall_status' => 'pending_info'
        ]);

        return [
            'success' => true,
            'message' => 'Information request sent successfully'
        ];
    }

    /**
     * Handle escalation
     */
    private function handleEscalation(DocumentApproval $approval, array $data): array
    {
        // Escalate to higher authority
        return [
            'success' => true,
            'message' => 'Approval escalated successfully'
        ];
    }

    /**
     * Handle cancellation
     */
    private function handleCancellation(DocumentApproval $approval, array $data): array
    {
        $approval->update([
            'overall_status' => 'cancelled',
            'completed_at' => now()
        ]);

        return [
            'success' => true,
            'message' => 'Approval cancelled successfully'
        ];
    }

    /**
     * Auto-approve next stage
     */
    private function autoApproveNextStage(DocumentApproval $approval): void
    {
        $nextStage = $this->getNextStage($approval);

        if ($nextStage && $nextStage->auto_approve_conditions) {
            $conditions = json_decode($nextStage->auto_approve_conditions, true);

            // Check if auto-approve conditions are met
            if ($this->checkAutoApproveConditions($approval, $conditions)) {
                $this->handleApproval($approval);
            }
        }
    }

    /**
     * Check auto-approve conditions
     */
    private function checkAutoApproveConditions(DocumentApproval $approval, array $conditions): bool
    {
        // Implement condition checking logic based on approval metadata
        // This is a simplified example
        return true;
    }

    /**
     * Build metadata for approval action
     */
    private function buildMetadata(string $action, array $data): array
    {
        $metadata = [
            'action_type' => $action,
            'processed_at' => now()->toISOString(),
            'user_id' => auth()->id()
        ];

        switch ($action) {
            case 'reject':
                $metadata['rejection_reason'] = $data['rejection_reason'] ?? '';
                break;
            case 'forward':
                $metadata['forward_reason'] = $data['forward_reason'] ?? '';
                $metadata['forwarded_to'] = $data['forward_to'] ?? '';
                break;
            case 'delegate':
                $metadata['delegation_reason'] = $data['delegation_reason'] ?? '';
                $metadata['delegated_to'] = $data['delegate_to'] ?? '';
                $metadata['delegation_duration'] = $data['delegation_duration'] ?? 0;
                break;
            case 'request_info':
                $metadata['info_request'] = $data['info_request'] ?? '';
                $metadata['deadline'] = $data['deadline'] ?? '';
                $metadata['priority'] = $data['priority'] ?? 'medium';
                break;
            case 'escalate':
                $metadata['escalation_reason'] = $data['escalation_reason'] ?? '';
                $metadata['escalated_to'] = $data['escalate_to'] ?? '';
                break;
            case 'cancel':
                $metadata['cancellation_reason'] = $data['cancellation_reason'] ?? '';
                break;
        }

        return $metadata;
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
     * Get next approvers
     */
    private function getNextApprovers(DocumentApproval $approval): array
    {
        $nextStage = $this->getNextStage($approval);

        if (!$nextStage) {
            return [];
        }

        return $nextStage->approvers->map(function ($approver) {
            return [
                'id' => $approver->id,
                'type' => $approver->approver_type,
                'name' => $this->getApproverName($approver),
                'is_backup' => $approver->is_backup
            ];
        })->toArray();
    }

    /**
     * Get approver name
     */
    private function getApproverName($approver): string
    {
        switch ($approver->approver_type) {
            case 'user':
                return $approver->user->name ?? 'Unknown User';
            case 'role':
                return $approver->role->name ?? 'Unknown Role';
            case 'department':
                return $approver->department->name ?? 'Unknown Department';
            default:
                return 'Unknown';
        }
    }

    /**
     * Calculate approval statistics
     */
    private function calculateApprovalStats(DocumentApproval $approval): array
    {
        $totalActions = $approval->actions->count();
        $approvedActions = $approval->actions->where('action', 'approved')->count();
        $rejectedActions = $approval->actions->where('action', 'rejected')->count();
        $forwardedActions = $approval->actions->where('action', 'forwarded')->count();

        $daysPending = $approval->submitted_at->diffInDays(now());
        $avgResponseTime = $approval->actions->avg('response_time_hours') ?? 0;

        return [
            'total_actions' => $totalActions,
            'approved_actions' => $approvedActions,
            'rejected_actions' => $rejectedActions,
            'forwarded_actions' => $forwardedActions,
            'days_pending' => $daysPending,
            'avg_response_time' => round($avgResponseTime, 2),
            'approval_rate' => $totalActions > 0 ? round(($approvedActions / $totalActions) * 100, 2) : 0
        ];
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
}
