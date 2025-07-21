<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DocumentApproval;
use App\Models\ApprovalAction;
use App\Services\ApprovalEngineService;
use App\Services\ApprovalNotificationService;
use App\Services\ApprovalCacheService;
use App\Services\ApprovalAuditService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

/**
 * API Controller for Approval Actions
 */
class ApprovalActionApiController extends Controller
{
    protected ApprovalEngineService $engineService;
    protected ApprovalNotificationService $notificationService;
    protected ApprovalCacheService $cacheService;
    protected ApprovalAuditService $auditService;

    public function __construct(
        ApprovalEngineService $engineService,
        ApprovalNotificationService $notificationService,
        ApprovalCacheService $cacheService,
        ApprovalAuditService $auditService
    ) {
        $this->engineService = $engineService;
        $this->notificationService = $notificationService;
        $this->cacheService = $cacheService;
        $this->auditService = $auditService;
    }

    /**
     * Get all approval actions
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = ApprovalAction::with(['documentApproval', 'approvalStage', 'approver']);

            // Apply filters
            if ($request->has('document_approval_id')) {
                $query->where('document_approval_id', $request->document_approval_id);
            }

            if ($request->has('approver_id')) {
                $query->where('approver_id', $request->approver_id);
            }

            if ($request->has('action')) {
                $query->where('action', $request->action);
            }

            if ($request->has('date_from')) {
                $query->where('action_date', '>=', $request->date_from);
            }

            if ($request->has('date_to')) {
                $query->where('action_date', '<=', $request->date_to);
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $actions = $query->orderBy('action_date', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $actions->items(),
                'pagination' => [
                    'current_page' => $actions->currentPage(),
                    'last_page' => $actions->lastPage(),
                    'per_page' => $actions->perPage(),
                    'total' => $actions->total(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get approval actions', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve approval actions',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific approval action
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $action = ApprovalAction::with(['documentApproval', 'approvalStage', 'approver'])->find($id);

            if (!$action) {
                return response()->json([
                    'success' => false,
                    'message' => 'Approval action not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $action,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get approval action', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve approval action',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Process approval action
     *
     * @param Request $request
     * @param int $approvalId
     * @return JsonResponse
     */
    public function process(Request $request, int $approvalId): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'action' => 'required|in:approved,rejected,forwarded,delegated',
                'comments' => 'nullable|string|max:1000',
                'forwarded_to' => 'required_if:action,forwarded|integer|exists:users,id',
                'delegated_to' => 'required_if:action,delegated|integer|exists:users,id',
                'additional_data' => 'array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $approval = DocumentApproval::find($approvalId);

            if (!$approval) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document approval not found',
                ], 404);
            }

            // Check if user can perform this action
            $canApprove = $this->engineService->canUserApprove($approvalId, auth()->user());
            if (!$canApprove) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to perform this action',
                ], 403);
            }

            $action = $request->input('action');
            $comments = $request->input('comments');
            $additionalData = $request->input('additional_data', []);

            // Add forwarded/delegated user to additional data
            if ($action === 'forwarded' && $request->has('forwarded_to')) {
                $additionalData['forwarded_to'] = $request->input('forwarded_to');
            }

            if ($action === 'delegated' && $request->has('delegated_to')) {
                $additionalData['delegated_to'] = $request->input('delegated_to');
            }

            $result = $this->engineService->processApproval(
                $approvalId,
                auth()->id(),
                $action,
                $comments,
                $additionalData
            );

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to process approval action',
                ], 500);
            }

            // Invalidate cache
            $this->cacheService->invalidateApprovalCache($approvalId);

            // Send notifications based on action type
            switch ($action) {
                case 'approved':
                    $this->notificationService->notifyApprovalComplete($approval);
                    break;
                case 'rejected':
                    $this->notificationService->notifyApprovalRejected($approval);
                    break;
                default:
                    // For forwarded and delegated, use general notification
                    $this->notificationService->notifyPendingApproval($approval);
                    break;
            }

            return response()->json([
                'success' => true,
                'message' => 'Approval action processed successfully',
                'data' => [
                    'approval_id' => $approvalId,
                    'action' => $action,
                    'processed_by' => auth()->id(),
                    'processed_at' => now(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to process approval action', [
                'approval_id' => $approvalId,
                'action' => $request->input('action'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to process approval action',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Submit document for approval
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function submit(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'document_type' => 'required|string|max:100',
                'document_id' => 'required|string|max:255',
                'metadata' => 'array',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $documentType = $request->input('document_type');
            $documentId = $request->input('document_id');
            $metadata = $request->input('metadata', []);

            $approval = $this->engineService->submitForApproval(
                $documentType,
                $documentId,
                auth()->id(),
                $metadata
            );

            // Send notifications
            $this->notificationService->notifyPendingApproval($approval);

            return response()->json([
                'success' => true,
                'message' => 'Document submitted for approval successfully',
                'data' => $approval->load(['flow', 'currentStage']),
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to submit document for approval', [
                'document_type' => $request->input('document_type'),
                'document_id' => $request->input('document_id'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to submit document for approval',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Cancel approval
     *
     * @param Request $request
     * @param int $approvalId
     * @return JsonResponse
     */
    public function cancel(Request $request, int $approvalId): JsonResponse
    {
        try {
            $approval = DocumentApproval::find($approvalId);

            if (!$approval) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document approval not found',
                ], 404);
            }

            // Check if user can cancel this approval
            if ($approval->submitted_by !== auth()->id() && !auth()->user()->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to cancel this approval',
                ], 403);
            }

            $result = $this->engineService->cancelApproval($approvalId, auth()->id());

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to cancel approval',
                ], 500);
            }

            // Invalidate cache
            $this->cacheService->invalidateApprovalCache($approvalId);

            // Send notifications
            $this->notificationService->notifyApprovalCancelled($approval);

            return response()->json([
                'success' => true,
                'message' => 'Approval cancelled successfully',
                'data' => [
                    'approval_id' => $approvalId,
                    'cancelled_by' => auth()->id(),
                    'cancelled_at' => now(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to cancel approval', [
                'approval_id' => $approvalId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel approval',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Escalate approval
     *
     * @param Request $request
     * @param int $approvalId
     * @return JsonResponse
     */
    public function escalate(Request $request, int $approvalId): JsonResponse
    {
        try {
            $approval = DocumentApproval::find($approvalId);

            if (!$approval) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document approval not found',
                ], 404);
            }

            // Check if user can escalate this approval
            if (!auth()->user()->hasRole('admin')) {
                return response()->json([
                    'success' => false,
                    'message' => 'You are not authorized to escalate this approval',
                ], 403);
            }

            $result = $this->engineService->escalateApproval($approvalId);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to escalate approval',
                ], 500);
            }

            // Invalidate cache
            $this->cacheService->invalidateApprovalCache($approvalId);

            // Send notifications
            $this->notificationService->sendEscalationNotification($approval, auth()->user());

            return response()->json([
                'success' => true,
                'message' => 'Approval escalated successfully',
                'data' => [
                    'approval_id' => $approvalId,
                    'escalated_by' => auth()->id(),
                    'escalated_at' => now(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to escalate approval', [
                'approval_id' => $approvalId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to escalate approval',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get next approvers for an approval
     *
     * @param int $approvalId
     * @return JsonResponse
     */
    public function nextApprovers(int $approvalId): JsonResponse
    {
        try {
            $approval = DocumentApproval::find($approvalId);

            if (!$approval) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document approval not found',
                ], 404);
            }

            $nextApprovers = $this->engineService->getNextApprovers($approvalId);

            return response()->json([
                'success' => true,
                'data' => $nextApprovers,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get next approvers', [
                'approval_id' => $approvalId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get next approvers',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get approval actions for a specific approval
     *
     * @param int $approvalId
     * @return JsonResponse
     */
    public function approvalActions(int $approvalId): JsonResponse
    {
        try {
            $approval = DocumentApproval::find($approvalId);

            if (!$approval) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document approval not found',
                ], 404);
            }

            // Try to get from cache first
            $cachedActions = $this->cacheService->getCachedApprovalActions($approvalId);

            if ($cachedActions) {
                return response()->json([
                    'success' => true,
                    'data' => $cachedActions,
                    'cached' => true,
                ]);
            }

            $actions = ApprovalAction::with(['approver', 'approvalStage'])
                ->where('document_approval_id', $approvalId)
                ->orderBy('action_date', 'asc')
                ->get();

            // Cache the actions
            $this->cacheService->cacheApprovalActions($approvalId, $actions);

            return response()->json([
                'success' => true,
                'data' => $actions,
                'cached' => false,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get approval actions', [
                'approval_id' => $approvalId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get approval actions',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Bulk process approval actions
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function bulkProcess(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'approvals' => 'required|array',
                'approvals.*.approval_id' => 'required|integer|exists:document_approvals,id',
                'approvals.*.action' => 'required|in:approved,rejected,forwarded,delegated',
                'approvals.*.comments' => 'nullable|string|max:1000',
                'approvals.*.forwarded_to' => 'required_if:approvals.*.action,forwarded|integer|exists:users,id',
                'approvals.*.delegated_to' => 'required_if:approvals.*.action,delegated|integer|exists:users,id',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $results = [];
            $successCount = 0;
            $errorCount = 0;

            foreach ($request->input('approvals') as $approvalData) {
                try {
                    $approvalId = $approvalData['approval_id'];
                    $action = $approvalData['action'];
                    $comments = $approvalData['comments'] ?? null;
                    $additionalData = [];

                    if ($action === 'forwarded' && isset($approvalData['forwarded_to'])) {
                        $additionalData['forwarded_to'] = $approvalData['forwarded_to'];
                    }

                    if ($action === 'delegated' && isset($approvalData['delegated_to'])) {
                        $additionalData['delegated_to'] = $approvalData['delegated_to'];
                    }

                    $result = $this->engineService->processApproval(
                        $approvalId,
                        auth()->id(),
                        $action,
                        $comments,
                        $additionalData
                    );

                    if ($result) {
                        $successCount++;
                        $results[] = [
                            'approval_id' => $approvalId,
                            'status' => 'success',
                            'action' => $action,
                        ];

                        // Invalidate cache
                        $this->cacheService->invalidateApprovalCache($approvalId);
                    } else {
                        $errorCount++;
                        $results[] = [
                            'approval_id' => $approvalId,
                            'status' => 'error',
                            'message' => 'Failed to process action',
                        ];
                    }
                } catch (\Exception $e) {
                    $errorCount++;
                    $results[] = [
                        'approval_id' => $approvalData['approval_id'],
                        'status' => 'error',
                        'message' => $e->getMessage(),
                    ];
                }
            }

            return response()->json([
                'success' => true,
                'message' => 'Bulk processing completed',
                'data' => [
                    'total_processed' => count($request->input('approvals')),
                    'success_count' => $successCount,
                    'error_count' => $errorCount,
                    'results' => $results,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to bulk process approval actions', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to bulk process approval actions',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
