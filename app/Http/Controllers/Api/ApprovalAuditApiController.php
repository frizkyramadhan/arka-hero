<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ApprovalAuditService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * API Controller for Approval Audit Trail
 */
class ApprovalAuditApiController extends Controller
{
    protected ApprovalAuditService $auditService;

    public function __construct(ApprovalAuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * Get approval audit trail
     *
     * @param int $approvalId
     * @param Request $request
     * @return JsonResponse
     */
    public function getApprovalAudit(int $approvalId, Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['date_from', 'date_to', 'action_type']);
            $auditTrail = $this->auditService->getApprovalAuditTrail($approvalId, $filters);

            return response()->json([
                'success' => true,
                'data' => $auditTrail,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get approval audit trail', [
                'approval_id' => $approvalId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve approval audit trail',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get user audit trail
     *
     * @param int $userId
     * @param Request $request
     * @return JsonResponse
     */
    public function getUserAudit(int $userId, Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['date_from', 'date_to', 'action_type']);
            $auditTrail = $this->auditService->getUserApprovalAuditTrail($userId, $filters);

            return response()->json([
                'success' => true,
                'data' => $auditTrail,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get user audit trail', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve user audit trail',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get system audit trail
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getSystemAudit(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['date_from', 'date_to', 'event_type', 'user_id']);
            $auditTrail = $this->auditService->getSystemApprovalAuditTrail($filters);

            return response()->json([
                'success' => true,
                'data' => $auditTrail,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get system audit trail', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve system audit trail',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get audit statistics
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getAuditStatistics(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['date_from', 'date_to', 'event_type']);
            $statistics = $this->auditService->getAuditStatistics($filters);

            return response()->json([
                'success' => true,
                'data' => $statistics,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get audit statistics', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve audit statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Export audit trail to CSV
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function exportToCsv(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['date_from', 'date_to', 'event_type', 'user_id']);
            $auditTrail = $this->auditService->getSystemApprovalAuditTrail($filters);

            $csvContent = $this->auditService->exportAuditTrailToCsv($auditTrail);

            return response()->json([
                'success' => true,
                'data' => [
                    'csv_content' => $csvContent,
                    'filename' => 'audit_trail_' . now()->format('Y-m-d_H-i-s') . '.csv',
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to export audit trail to CSV', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to export audit trail to CSV',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
