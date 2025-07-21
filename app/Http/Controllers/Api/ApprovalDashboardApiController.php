<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DocumentApproval;
use App\Models\ApprovalAction;
use App\Services\ApprovalEngineService;
use App\Services\ApprovalCacheService;
use App\Services\ApprovalPermissionService;
use App\Services\ApprovalMonitoringService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * API Controller for Approval Dashboard
 */
class ApprovalDashboardApiController extends Controller
{
    protected ApprovalEngineService $engineService;
    protected ApprovalCacheService $cacheService;
    protected ApprovalPermissionService $permissionService;
    protected ApprovalMonitoringService $monitoringService;

    public function __construct(
        ApprovalEngineService $engineService,
        ApprovalCacheService $cacheService,
        ApprovalPermissionService $permissionService,
        ApprovalMonitoringService $monitoringService
    ) {
        $this->engineService = $engineService;
        $this->cacheService = $cacheService;
        $this->permissionService = $permissionService;
        $this->monitoringService = $monitoringService;
    }

    /**
     * Get dashboard overview
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function overview(Request $request): JsonResponse
    {
        try {
            $userId = auth()->id();
            $user = auth()->user();

            // Get pending approvals for user
            $pendingApprovals = $this->getPendingApprovals($userId, $request);

            // Get recent actions
            $recentActions = $this->getRecentActions($userId, $request);

            // Get statistics
            $statistics = $this->getUserStatistics($userId);

            // Get system health
            $systemHealth = $this->monitoringService->getSystemHealth();

            return response()->json([
                'success' => true,
                'data' => [
                    'pending_approvals' => $pendingApprovals,
                    'recent_actions' => $recentActions,
                    'statistics' => $statistics,
                    'system_health' => $systemHealth,
                    'user_permissions' => $this->permissionService->getUserApprovalPermissions($user),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get dashboard overview', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve dashboard overview',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get pending approvals
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function pending(Request $request): JsonResponse
    {
        try {
            $userId = auth()->id();
            $pendingApprovals = $this->getPendingApprovals($userId, $request);

            return response()->json([
                'success' => true,
                'data' => $pendingApprovals,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get pending approvals', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve pending approvals',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get approval history
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function history(Request $request): JsonResponse
    {
        try {
            $userId = auth()->id();
            $history = $this->getApprovalHistory($userId, $request);

            return response()->json([
                'success' => true,
                'data' => $history,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get approval history', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve approval history',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get approval statistics
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function statistics(Request $request): JsonResponse
    {
        try {
            $userId = auth()->id();
            $filters = $request->only(['date_from', 'date_to', 'document_type']);

            $statistics = $this->getUserStatistics($userId, $filters);

            return response()->json([
                'success' => true,
                'data' => $statistics,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get approval statistics', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve approval statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get system monitoring data
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function monitoring(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['date_from', 'date_to']);

            $monitoringData = [
                'health_status' => $this->monitoringService->getSystemHealth(),
                'performance_metrics' => $this->monitoringService->getPerformanceMetrics($filters),
                'system_alerts' => $this->monitoringService->getSystemAlerts(),
                'resource_usage' => $this->monitoringService->getResourceUsage(),
            ];

            return response()->json([
                'success' => true,
                'data' => $monitoringData,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get monitoring data', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve monitoring data',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get pending approvals for user
     *
     * @param int $userId
     * @param Request $request
     * @return array
     */
    private function getPendingApprovals(int $userId, Request $request): array
    {
        // Try to get from cache first
        $cachedApprovals = $this->cacheService->getCachedPendingApprovals($userId);

        if ($cachedApprovals && !$request->has('refresh')) {
            return $cachedApprovals->toArray();
        }

        $query = DocumentApproval::with(['flow', 'currentStage', 'submittedBy'])
            ->where('overall_status', 'pending');

        // Filter by document type
        if ($request->has('document_type')) {
            $query->where('document_type', $request->document_type);
        }

        // Filter by date range
        if ($request->has('date_from')) {
            $query->where('submitted_at', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('submitted_at', '<=', $request->date_to);
        }

        // Get approvals where user is an approver
        $user = auth()->user();
        $approvalIds = $this->permissionService->getUserPendingApprovalIds($user);

        $query->whereIn('id', $approvalIds);

        // Apply sorting
        $sortBy = $request->get('sort_by', 'submitted_at');
        $sortOrder = $request->get('sort_order', 'desc');
        $query->orderBy($sortBy, $sortOrder);

        // Pagination
        $perPage = $request->get('per_page', 15);
        $approvals = $query->paginate($perPage);

        // Cache the results
        $this->cacheService->cachePendingApprovals($userId, collect($approvals->items()));

        return [
            'data' => $approvals->items(),
            'pagination' => [
                'current_page' => $approvals->currentPage(),
                'last_page' => $approvals->lastPage(),
                'per_page' => $approvals->perPage(),
                'total' => $approvals->total(),
            ],
        ];
    }

    /**
     * Get recent actions for user
     *
     * @param int $userId
     * @param Request $request
     * @return array
     */
    private function getRecentActions(int $userId, Request $request): array
    {
        $query = ApprovalAction::with(['documentApproval', 'approvalStage'])
            ->where('approver_id', $userId);

        // Filter by date range
        if ($request->has('date_from')) {
            $query->where('action_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('action_date', '<=', $request->date_to);
        }

        // Filter by action type
        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        // Get recent actions
        $limit = $request->get('limit', 10);
        $actions = $query->orderBy('action_date', 'desc')
            ->limit($limit)
            ->get();

        return $actions->toArray();
    }

    /**
     * Get approval history for user
     *
     * @param int $userId
     * @param Request $request
     * @return array
     */
    private function getApprovalHistory(int $userId, Request $request): array
    {
        $query = ApprovalAction::with(['documentApproval', 'approvalStage'])
            ->where('approver_id', $userId);

        // Filter by date range
        if ($request->has('date_from')) {
            $query->where('action_date', '>=', $request->date_from);
        }

        if ($request->has('date_to')) {
            $query->where('action_date', '<=', $request->date_to);
        }

        // Filter by action type
        if ($request->has('action')) {
            $query->where('action', $request->action);
        }

        // Filter by document type
        if ($request->has('document_type')) {
            $query->whereHas('documentApproval', function ($q) use ($request) {
                $q->where('document_type', $request->document_type);
            });
        }

        // Pagination
        $perPage = $request->get('per_page', 15);
        $actions = $query->orderBy('action_date', 'desc')->paginate($perPage);

        return [
            'data' => $actions->items(),
            'pagination' => [
                'current_page' => $actions->currentPage(),
                'last_page' => $actions->lastPage(),
                'per_page' => $actions->perPage(),
                'total' => $actions->total(),
            ],
        ];
    }

    /**
     * Get user statistics
     *
     * @param int $userId
     * @param array $filters
     * @return array
     */
    private function getUserStatistics(int $userId, array $filters = []): array
    {
        $query = ApprovalAction::where('approver_id', $userId);

        // Apply filters
        if (isset($filters['date_from'])) {
            $query->where('action_date', '>=', $filters['date_from']);
        }

        if (isset($filters['date_to'])) {
            $query->where('action_date', '<=', $filters['date_to']);
        }

        if (isset($filters['document_type'])) {
            $query->whereHas('documentApproval', function ($q) use ($filters) {
                $q->where('document_type', $filters['document_type']);
            });
        }

        $totalActions = $query->count();
        $approvedActions = $query->where('action', 'approved')->count();
        $rejectedActions = $query->where('action', 'rejected')->count();
        $forwardedActions = $query->where('action', 'forwarded')->count();
        $delegatedActions = $query->where('action', 'delegated')->count();

        // Get pending approvals count
        $user = auth()->user();
        $pendingCount = count($this->permissionService->getUserPendingApprovalIds($user));

        // Calculate average response time
        $recentActions = ApprovalAction::where('approver_id', $userId)
            ->whereIn('action', ['approved', 'rejected'])
            ->where('action_date', '>=', now()->subDays(30))
            ->get();

        $totalResponseTime = 0;
        $responseCount = 0;

        foreach ($recentActions as $action) {
            $approval = $action->documentApproval;
            if ($approval && $approval->submitted_at) {
                $responseTime = $approval->submitted_at->diffInHours($action->action_date);
                $totalResponseTime += $responseTime;
                $responseCount++;
            }
        }

        $averageResponseTime = $responseCount > 0 ? round($totalResponseTime / $responseCount, 2) : 0;

        return [
            'total_actions' => $totalActions,
            'approved_actions' => $approvedActions,
            'rejected_actions' => $rejectedActions,
            'forwarded_actions' => $forwardedActions,
            'delegated_actions' => $delegatedActions,
            'pending_approvals' => $pendingCount,
            'approval_rate' => $totalActions > 0 ? round(($approvedActions / $totalActions) * 100, 2) : 0,
            'average_response_time_hours' => $averageResponseTime,
            'recent_activity' => [
                'last_7_days' => $query->where('action_date', '>=', now()->subDays(7))->count(),
                'last_30_days' => $query->where('action_date', '>=', now()->subDays(30))->count(),
            ],
        ];
    }
}
