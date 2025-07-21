<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ApprovalCacheService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * API Controller for Approval Cache Management
 */
class ApprovalCacheApiController extends Controller
{
    protected ApprovalCacheService $cacheService;

    public function __construct(ApprovalCacheService $cacheService)
    {
        $this->cacheService = $cacheService;
    }

    /**
     * Get cache statistics
     *
     * @return JsonResponse
     */
    public function getStatistics(): JsonResponse
    {
        try {
            $statistics = $this->cacheService->getCacheStatistics();

            return response()->json([
                'success' => true,
                'data' => $statistics,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get cache statistics', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve cache statistics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Clear all cache
     *
     * @return JsonResponse
     */
    public function clearAll(): JsonResponse
    {
        try {
            $result = $this->cacheService->clearAllApprovalCache();

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to clear all cache',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'All approval cache cleared successfully',
                'data' => [
                    'cleared_at' => now(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to clear all cache', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to clear all cache',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Warm up user cache
     *
     * @param int $userId
     * @return JsonResponse
     */
    public function warmUpUser(int $userId): JsonResponse
    {
        try {
            $result = $this->cacheService->warmUpUserCache($userId);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to warm up user cache',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'User cache warmed up successfully',
                'data' => [
                    'user_id' => $userId,
                    'warmed_at' => now(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to warm up user cache', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to warm up user cache',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Warm up flow cache
     *
     * @param int $flowId
     * @return JsonResponse
     */
    public function warmUpFlow(int $flowId): JsonResponse
    {
        try {
            $result = $this->cacheService->warmUpFlowCache($flowId);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to warm up flow cache',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Flow cache warmed up successfully',
                'data' => [
                    'flow_id' => $flowId,
                    'warmed_at' => now(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to warm up flow cache', [
                'flow_id' => $flowId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to warm up flow cache',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Invalidate approval cache
     *
     * @param int $approvalId
     * @return JsonResponse
     */
    public function invalidateApproval(int $approvalId): JsonResponse
    {
        try {
            $result = $this->cacheService->invalidateApprovalCache($approvalId);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to invalidate approval cache',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Approval cache invalidated successfully',
                'data' => [
                    'approval_id' => $approvalId,
                    'invalidated_at' => now(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to invalidate approval cache', [
                'approval_id' => $approvalId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to invalidate approval cache',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Invalidate user cache
     *
     * @param int $userId
     * @return JsonResponse
     */
    public function invalidateUser(int $userId): JsonResponse
    {
        try {
            $result = $this->cacheService->invalidateUserCache($userId);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to invalidate user cache',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'User cache invalidated successfully',
                'data' => [
                    'user_id' => $userId,
                    'invalidated_at' => now(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to invalidate user cache', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to invalidate user cache',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Invalidate flow cache
     *
     * @param int $flowId
     * @return JsonResponse
     */
    public function invalidateFlow(int $flowId): JsonResponse
    {
        try {
            $result = $this->cacheService->invalidateFlowCache($flowId);

            if (!$result) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to invalidate flow cache',
                ], 500);
            }

            return response()->json([
                'success' => true,
                'message' => 'Flow cache invalidated successfully',
                'data' => [
                    'flow_id' => $flowId,
                    'invalidated_at' => now(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to invalidate flow cache', [
                'flow_id' => $flowId,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to invalidate flow cache',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
