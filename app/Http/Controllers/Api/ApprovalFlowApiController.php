<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ApprovalFlow;
use App\Models\ApprovalStage;
use App\Models\ApprovalStageApprover;
use App\Services\ApprovalFlowService;
use App\Services\ApprovalCacheService;
use App\Services\ApprovalAuditService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

/**
 * API Controller for Approval Flow Management
 */
class ApprovalFlowApiController extends Controller
{
    protected ApprovalFlowService $flowService;
    protected ApprovalCacheService $cacheService;
    protected ApprovalAuditService $auditService;

    public function __construct(
        ApprovalFlowService $flowService,
        ApprovalCacheService $cacheService,
        ApprovalAuditService $auditService
    ) {
        $this->flowService = $flowService;
        $this->cacheService = $cacheService;
        $this->auditService = $auditService;
    }

    /**
     * Get all approval flows
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = ApprovalFlow::query();

            // Apply filters
            if ($request->has('document_type')) {
                $query->where('document_type', $request->document_type);
            }

            if ($request->has('is_active')) {
                $query->where('is_active', $request->boolean('is_active'));
            }

            if ($request->has('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Pagination
            $perPage = $request->get('per_page', 15);
            $flows = $query->with(['stages', 'stages.approvers'])->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $flows->items(),
                'pagination' => [
                    'current_page' => $flows->currentPage(),
                    'last_page' => $flows->lastPage(),
                    'per_page' => $flows->perPage(),
                    'total' => $flows->total(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get approval flows', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve approval flows',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific approval flow
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $flow = ApprovalFlow::with(['stages.approvers', 'stages.actions'])->find($id);

            if (!$flow) {
                return response()->json([
                    'success' => false,
                    'message' => 'Approval flow not found',
                ], 404);
            }

            return response()->json([
                'success' => true,
                'data' => $flow,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get approval flow', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve approval flow',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new approval flow
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'document_type' => 'required|string|max:100',
                'is_active' => 'boolean',
                'stages' => 'array',
                'stages.*.stage_name' => 'required|string|max:255',
                'stages.*.stage_order' => 'required|integer|min:1',
                'stages.*.stage_type' => 'required|in:sequential,parallel',
                'stages.*.is_mandatory' => 'boolean',
                'stages.*.escalation_hours' => 'integer|min:1',
                'stages.*.approvers' => 'array',
                'stages.*.approvers.*.approver_type' => 'required|in:user,role,department',
                'stages.*.approvers.*.approver_id' => 'required|integer',
                'stages.*.approvers.*.is_backup' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $flowData = $request->only(['name', 'description', 'document_type', 'is_active']);
            $stagesData = $request->input('stages', []);

            $flow = $this->flowService->createFlow($flowData, $stagesData);

            // Invalidate cache
            $this->cacheService->invalidateFlowCache($flow->id);

            // Log audit
            $this->auditService->logApprovalFlowCreation($flowData, auth()->user());

            return response()->json([
                'success' => true,
                'message' => 'Approval flow created successfully',
                'data' => $flow->load(['stages.approvers']),
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to create approval flow', [
                'error' => $e->getMessage(),
                'data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to create approval flow',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update an approval flow
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $flow = ApprovalFlow::find($id);

            if (!$flow) {
                return response()->json([
                    'success' => false,
                    'message' => 'Approval flow not found',
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'name' => 'sometimes|required|string|max:255',
                'description' => 'nullable|string',
                'document_type' => 'sometimes|required|string|max:100',
                'is_active' => 'boolean',
                'stages' => 'array',
                'stages.*.stage_name' => 'required|string|max:255',
                'stages.*.stage_order' => 'required|integer|min:1',
                'stages.*.stage_type' => 'required|in:sequential,parallel',
                'stages.*.is_mandatory' => 'boolean',
                'stages.*.escalation_hours' => 'integer|min:1',
                'stages.*.approvers' => 'array',
                'stages.*.approvers.*.approver_type' => 'required|in:user,role,department',
                'stages.*.approvers.*.approver_id' => 'required|integer',
                'stages.*.approvers.*.is_backup' => 'boolean',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $flowData = $request->only(['name', 'description', 'document_type', 'is_active']);
            $stagesData = $request->input('stages', []);

            $oldFlowData = $flow->toArray();
            $flow = $this->flowService->updateFlow($id, $flowData, $stagesData);

            // Invalidate cache
            $this->cacheService->invalidateFlowCache($flow->id);

            // Log audit
            $this->auditService->logApprovalFlowModification($flow->id, $oldFlowData, $flow->toArray(), auth()->user());

            return response()->json([
                'success' => true,
                'message' => 'Approval flow updated successfully',
                'data' => $flow->load(['stages.approvers']),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to update approval flow', [
                'id' => $id,
                'error' => $e->getMessage(),
                'data' => $request->all(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update approval flow',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete an approval flow
     *
     * @param int $id
     * @return JsonResponse
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $flow = ApprovalFlow::find($id);

            if (!$flow) {
                return response()->json([
                    'success' => false,
                    'message' => 'Approval flow not found',
                ], 404);
            }

            // Check if flow is being used
            $activeApprovals = $flow->documentApprovals()->where('overall_status', 'pending')->count();
            if ($activeApprovals > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete flow with active approvals',
                    'active_approvals_count' => $activeApprovals,
                ], 400);
            }

            $flowData = $flow->toArray();
            $this->flowService->deleteFlow($id);

            // Invalidate cache
            $this->cacheService->invalidateFlowCache($id);

            // Log audit
            $this->auditService->logApprovalFlowDeletion($id, $flowData, auth()->user());

            return response()->json([
                'success' => true,
                'message' => 'Approval flow deleted successfully',
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to delete approval flow', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to delete approval flow',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clone an approval flow
     *
     * @param Request $request
     * @param int $id
     * @return JsonResponse
     */
    public function clone(Request $request, int $id): JsonResponse
    {
        try {
            $flow = ApprovalFlow::find($id);

            if (!$flow) {
                return response()->json([
                    'success' => false,
                    'message' => 'Approval flow not found',
                ], 404);
            }

            $validator = Validator::make($request->all(), [
                'new_name' => 'required|string|max:255',
            ]);

            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed',
                    'errors' => $validator->errors(),
                ], 422);
            }

            $newName = $request->input('new_name');
            $clonedFlow = $this->flowService->cloneFlow($id, $newName);

            // Log audit
            $this->auditService->logApprovalFlowCreation([
                'name' => $newName,
                'cloned_from' => $id,
            ], auth()->user());

            return response()->json([
                'success' => true,
                'message' => 'Approval flow cloned successfully',
                'data' => $clonedFlow->load(['stages.approvers']),
            ], 201);
        } catch (\Exception $e) {
            Log::error('Failed to clone approval flow', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to clone approval flow',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get approval flow by document type
     *
     * @param string $documentType
     * @return JsonResponse
     */
    public function getByDocumentType(string $documentType): JsonResponse
    {
        try {
            // Try to get from cache first
            $cachedFlow = $this->cacheService->getCachedApprovalFlow($documentType);

            if ($cachedFlow) {
                return response()->json([
                    'success' => true,
                    'data' => $cachedFlow,
                    'cached' => true,
                ]);
            }

            $flow = $this->flowService->getFlowByDocumentType($documentType);

            if (!$flow) {
                return response()->json([
                    'success' => false,
                    'message' => 'No approval flow found for document type',
                ], 404);
            }

            // Cache the flow
            $this->cacheService->cacheApprovalFlow($documentType, $flow);

            return response()->json([
                'success' => true,
                'data' => $flow->load(['stages.approvers']),
                'cached' => false,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get approval flow by document type', [
                'document_type' => $documentType,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve approval flow',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get approval flow statistics
     *
     * @param int $id
     * @return JsonResponse
     */
    public function statistics(int $id): JsonResponse
    {
        try {
            $flow = ApprovalFlow::find($id);

            if (!$flow) {
                return response()->json([
                    'success' => false,
                    'message' => 'Approval flow not found',
                ], 404);
            }

            $statistics = [
                'total_approvals' => $flow->documentApprovals()->count(),
                'pending_approvals' => $flow->documentApprovals()->where('overall_status', 'pending')->count(),
                'approved_approvals' => $flow->documentApprovals()->where('overall_status', 'approved')->count(),
                'rejected_approvals' => $flow->documentApprovals()->where('overall_status', 'rejected')->count(),
                'cancelled_approvals' => $flow->documentApprovals()->where('overall_status', 'cancelled')->count(),
                'total_stages' => $flow->stages()->count(),
                'total_approvers' => $flow->stages()->withCount('approvers')->get()->sum('approvers_count'),
                'average_completion_time' => $this->calculateAverageCompletionTime($flow),
            ];

            return response()->json([
                'success' => true,
                'data' => $statistics,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get approval flow statistics', [
                'id' => $id,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve approval flow statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Calculate average completion time for a flow
     *
     * @param ApprovalFlow $flow
     * @return float
     */
    private function calculateAverageCompletionTime(ApprovalFlow $flow): float
    {
        $completedApprovals = $flow->documentApprovals()
            ->whereIn('overall_status', ['approved', 'rejected'])
            ->whereNotNull('completed_at')
            ->get();

        if ($completedApprovals->isEmpty()) {
            return 0;
        }

        $totalTime = 0;
        foreach ($completedApprovals as $approval) {
            $totalTime += $approval->submitted_at->diffInHours($approval->completed_at);
        }

        return round($totalTime / $completedApprovals->count(), 2);
    }
}
