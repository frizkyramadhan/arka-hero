<?php

namespace App\Services;

use App\Models\ApprovalFlow;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Service class for approval caching
 */
class ApprovalCacheService
{
    /**
     * Cache key prefix for approval flows
     */
    private const CACHE_PREFIX = 'approval_flow_';

    /**
     * Cache duration in seconds (1 hour)
     */
    private const CACHE_DURATION = 3600;

    /**
     * Get cached approval flow by document type
     *
     * @param string $documentType The document type
     * @return ApprovalFlow|null The cached flow or null if not found
     */
    public function getCachedApprovalFlow(string $documentType): ?ApprovalFlow
    {
        $cacheKey = $this->getFlowCacheKey($documentType);

        try {
            $cachedData = Cache::get($cacheKey);

            if ($cachedData && $cachedData instanceof ApprovalFlow) {
                Log::info('Approval flow retrieved from cache', [
                    'document_type' => $documentType,
                    'cache_key' => $cacheKey,
                ]);
                return $cachedData;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Failed to get cached approval flow', [
                'document_type' => $documentType,
                'cache_key' => $cacheKey,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Cache approval flow by document type
     *
     * @param string $documentType The document type
     * @param ApprovalFlow $flow The approval flow to cache
     * @return bool True if cached successfully
     */
    public function cacheApprovalFlow(string $documentType, ApprovalFlow $flow): bool
    {
        $cacheKey = $this->getFlowCacheKey($documentType);

        try {
            Cache::put($cacheKey, $flow, self::CACHE_DURATION);

            Log::info('Approval flow cached', [
                'document_type' => $documentType,
                'flow_id' => $flow->id,
                'cache_key' => $cacheKey,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to cache approval flow', [
                'document_type' => $documentType,
                'flow_id' => $flow->id,
                'cache_key' => $cacheKey,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Invalidate approval flow cache
     *
     * @param int $flowId The flow ID
     * @return bool True if invalidated successfully
     */
    public function invalidateFlowCache(int $flowId): bool
    {
        try {
            $flow = ApprovalFlow::find($flowId);
            if (!$flow) {
                return false;
            }

            $cacheKey = $this->getFlowCacheKey($flow->document_type);
            Cache::forget($cacheKey);

            Log::info('Approval flow cache invalidated', [
                'flow_id' => $flowId,
                'document_type' => $flow->document_type,
                'cache_key' => $cacheKey,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to invalidate approval flow cache', [
                'flow_id' => $flowId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Invalidate all approval flow caches
     *
     * @return bool True if invalidated successfully
     */
    public function invalidateAllFlowCaches(): bool
    {
        try {
            $documentTypes = ApprovalFlow::distinct()->pluck('document_type');

            foreach ($documentTypes as $documentType) {
                $cacheKey = $this->getFlowCacheKey($documentType);
                Cache::forget($cacheKey);
            }

            Log::info('All approval flow caches invalidated', [
                'document_types_count' => $documentTypes->count(),
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to invalidate all approval flow caches', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Cache approval statistics
     *
     * @param int $flowId The flow ID
     * @param array $statistics The statistics to cache
     * @return bool True if cached successfully
     */
    public function cacheFlowStatistics(int $flowId, array $statistics): bool
    {
        $cacheKey = $this->getStatisticsCacheKey($flowId);

        try {
            Cache::put($cacheKey, $statistics, self::CACHE_DURATION);

            Log::info('Approval flow statistics cached', [
                'flow_id' => $flowId,
                'cache_key' => $cacheKey,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to cache approval flow statistics', [
                'flow_id' => $flowId,
                'cache_key' => $cacheKey,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get cached approval statistics
     *
     * @param int $flowId The flow ID
     * @return array|null The cached statistics or null if not found
     */
    public function getCachedFlowStatistics(int $flowId): ?array
    {
        $cacheKey = $this->getStatisticsCacheKey($flowId);

        try {
            $cachedData = Cache::get($cacheKey);

            if ($cachedData && is_array($cachedData)) {
                Log::info('Approval flow statistics retrieved from cache', [
                    'flow_id' => $flowId,
                    'cache_key' => $cacheKey,
                ]);
                return $cachedData;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Failed to get cached approval flow statistics', [
                'flow_id' => $flowId,
                'cache_key' => $cacheKey,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Invalidate approval statistics cache
     *
     * @param int $flowId The flow ID
     * @return bool True if invalidated successfully
     */
    public function invalidateStatisticsCache(int $flowId): bool
    {
        try {
            $cacheKey = $this->getStatisticsCacheKey($flowId);
            Cache::forget($cacheKey);

            Log::info('Approval flow statistics cache invalidated', [
                'flow_id' => $flowId,
                'cache_key' => $cacheKey,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to invalidate approval flow statistics cache', [
                'flow_id' => $flowId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Cache user approval permissions
     *
     * @param int $userId The user ID
     * @param array $permissions The permissions to cache
     * @return bool True if cached successfully
     */
    public function cacheUserPermissions(int $userId, array $permissions): bool
    {
        $cacheKey = $this->getUserPermissionsCacheKey($userId);

        try {
            Cache::put($cacheKey, $permissions, self::CACHE_DURATION);

            Log::info('User approval permissions cached', [
                'user_id' => $userId,
                'cache_key' => $cacheKey,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to cache user approval permissions', [
                'user_id' => $userId,
                'cache_key' => $cacheKey,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get cached user approval permissions
     *
     * @param int $userId The user ID
     * @return array|null The cached permissions or null if not found
     */
    public function getCachedUserPermissions(int $userId): ?array
    {
        $cacheKey = $this->getUserPermissionsCacheKey($userId);

        try {
            $cachedData = Cache::get($cacheKey);

            if ($cachedData && is_array($cachedData)) {
                Log::info('User approval permissions retrieved from cache', [
                    'user_id' => $userId,
                    'cache_key' => $cacheKey,
                ]);
                return $cachedData;
            }

            return null;
        } catch (\Exception $e) {
            Log::error('Failed to get cached user approval permissions', [
                'user_id' => $userId,
                'cache_key' => $cacheKey,
                'error' => $e->getMessage(),
            ]);
            return null;
        }
    }

    /**
     * Invalidate user permissions cache
     *
     * @param int $userId The user ID
     * @return bool True if invalidated successfully
     */
    public function invalidateUserPermissionsCache(int $userId): bool
    {
        try {
            $cacheKey = $this->getUserPermissionsCacheKey($userId);
            Cache::forget($cacheKey);

            Log::info('User approval permissions cache invalidated', [
                'user_id' => $userId,
                'cache_key' => $cacheKey,
            ]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to invalidate user approval permissions cache', [
                'user_id' => $userId,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Get flow cache key
     *
     * @param string $documentType The document type
     * @return string The cache key
     */
    private function getFlowCacheKey(string $documentType): string
    {
        return self::CACHE_PREFIX . 'flow_' . $documentType;
    }

    /**
     * Get statistics cache key
     *
     * @param int $flowId The flow ID
     * @return string The cache key
     */
    private function getStatisticsCacheKey(int $flowId): string
    {
        return self::CACHE_PREFIX . 'statistics_' . $flowId;
    }

    /**
     * Get user permissions cache key
     *
     * @param int $userId The user ID
     * @return string The cache key
     */
    private function getUserPermissionsCacheKey(int $userId): string
    {
        return self::CACHE_PREFIX . 'permissions_' . $userId;
    }
}
