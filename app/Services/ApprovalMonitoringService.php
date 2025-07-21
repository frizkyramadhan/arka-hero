<?php

namespace App\Services;

use App\Models\DocumentApproval;
use App\Models\ApprovalAction;
use App\Models\ApprovalFlow;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Collection;

/**
 * Service class for handling approval system monitoring and health checks.
 */
class ApprovalMonitoringService
{
    protected ApprovalCacheService $cacheService;
    protected ApprovalAuditService $auditService;

    public function __construct(
        ApprovalCacheService $cacheService,
        ApprovalAuditService $auditService
    ) {
        $this->cacheService = $cacheService;
        $this->auditService = $auditService;
    }

    /**
     * Get system health status.
     *
     * @return array The health status
     */
    public function getSystemHealth(): array
    {
        try {
            $health = [
                'status' => 'healthy',
                'timestamp' => now(),
                'checks' => [],
            ];

            // Database connectivity check
            $health['checks']['database'] = $this->checkDatabaseHealth();

            // Cache system check
            $health['checks']['cache'] = $this->checkCacheHealth();

            // Approval flow check
            $health['checks']['approval_flows'] = $this->checkApprovalFlowsHealth();

            // Performance check
            $health['checks']['performance'] = $this->checkPerformanceHealth();

            // Determine overall status
            $failedChecks = array_filter($health['checks'], function ($check) {
                return $check['status'] === 'failed';
            });

            if (count($failedChecks) > 0) {
                $health['status'] = 'unhealthy';
            }

            return $health;
        } catch (\Exception $e) {
            Log::error('Failed to get system health', [
                'error' => $e->getMessage(),
            ]);

            return [
                'status' => 'error',
                'timestamp' => now(),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check database health.
     *
     * @return array The database health status
     */
    private function checkDatabaseHealth(): array
    {
        try {
            $startTime = microtime(true);

            // Test database connection
            DB::connection()->getPdo();

            // Test basic query
            $approvalCount = DocumentApproval::count();

            $responseTime = (microtime(true) - $startTime) * 1000; // Convert to milliseconds

            return [
                'status' => 'healthy',
                'response_time_ms' => round($responseTime, 2),
                'approval_count' => $approvalCount,
                'connection' => 'established',
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
                'connection' => 'failed',
            ];
        }
    }

    /**
     * Check cache health.
     *
     * @return array The cache health status
     */
    private function checkCacheHealth(): array
    {
        try {
            $startTime = microtime(true);

            // Test cache write
            $testKey = 'health_check_' . time();
            $testValue = 'test_value';
            Cache::put($testKey, $testValue, 60);

            // Test cache read
            $retrievedValue = Cache::get($testKey);

            // Clean up
            Cache::forget($testKey);

            $responseTime = (microtime(true) - $startTime) * 1000;

            return [
                'status' => 'healthy',
                'response_time_ms' => round($responseTime, 2),
                'write_test' => $retrievedValue === $testValue ? 'passed' : 'failed',
                'driver' => config('cache.default'),
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
                'driver' => config('cache.default'),
            ];
        }
    }

    /**
     * Check approval flows health.
     *
     * @return array The approval flows health status
     */
    private function checkApprovalFlowsHealth(): array
    {
        try {
            $activeFlows = ApprovalFlow::where('is_active', true)->count();
            $totalFlows = ApprovalFlow::count();
            $flowsWithStages = ApprovalFlow::whereHas('stages')->count();

            return [
                'status' => 'healthy',
                'active_flows' => $activeFlows,
                'total_flows' => $totalFlows,
                'flows_with_stages' => $flowsWithStages,
                'stages_per_flow_avg' => $totalFlows > 0 ? round($flowsWithStages / $totalFlows, 2) : 0,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Check performance health.
     *
     * @return array The performance health status
     */
    private function checkPerformanceHealth(): array
    {
        try {
            $startTime = microtime(true);

            // Test approval query performance
            $pendingApprovals = DocumentApproval::where('overall_status', 'pending')->count();

            $queryTime = (microtime(true) - $startTime) * 1000;

            return [
                'status' => $queryTime < 1000 ? 'healthy' : 'warning',
                'query_time_ms' => round($queryTime, 2),
                'pending_approvals' => $pendingApprovals,
                'performance_threshold_ms' => 1000,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get system performance metrics.
     *
     * @param array $filters Optional filters
     * @return array The performance metrics
     */
    public function getPerformanceMetrics(array $filters = []): array
    {
        try {
            $metrics = [
                'timestamp' => now(),
                'database' => $this->getDatabaseMetrics($filters),
                'cache' => $this->getCacheMetrics(),
                'approval_processing' => $this->getApprovalProcessingMetrics($filters),
                'user_activity' => $this->getUserActivityMetrics($filters),
            ];

            return $metrics;
        } catch (\Exception $e) {
            Log::error('Failed to get performance metrics', [
                'error' => $e->getMessage(),
            ]);
            return [
                'timestamp' => now(),
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get database performance metrics.
     *
     * @param array $filters Optional filters
     * @return array The database metrics
     */
    private function getDatabaseMetrics(array $filters = []): array
    {
        try {
            $query = DocumentApproval::query();

            // Apply filters
            if (isset($filters['date_from'])) {
                $query->where('submitted_at', '>=', $filters['date_from']);
            }

            if (isset($filters['date_to'])) {
                $query->where('submitted_at', '<=', $filters['date_to']);
            }

            $totalApprovals = $query->count();
            $pendingApprovals = $query->where('overall_status', 'pending')->count();
            $completedApprovals = $query->whereIn('overall_status', ['approved', 'rejected'])->count();

            // Get average approval time
            $completedApprovalsWithTime = $query->whereIn('overall_status', ['approved', 'rejected'])
                ->whereNotNull('completed_at')
                ->get();

            $totalApprovalTime = 0;
            $completedCount = 0;

            foreach ($completedApprovalsWithTime as $approval) {
                $approvalTime = $approval->submitted_at->diffInHours($approval->completed_at);
                $totalApprovalTime += $approvalTime;
                $completedCount++;
            }

            $averageApprovalTime = $completedCount > 0 ? round($totalApprovalTime / $completedCount, 2) : 0;

            return [
                'total_approvals' => $totalApprovals,
                'pending_approvals' => $pendingApprovals,
                'completed_approvals' => $completedApprovals,
                'average_approval_time_hours' => $averageApprovalTime,
                'completion_rate' => $totalApprovals > 0 ? round(($completedApprovals / $totalApprovals) * 100, 2) : 0,
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get cache performance metrics.
     *
     * @return array The cache metrics
     */
    private function getCacheMetrics(): array
    {
        try {
            $cacheStats = $this->cacheService->getCacheStatistics();

            return [
                'driver' => $cacheStats['cache_driver'] ?? 'unknown',
                'prefix' => $cacheStats['cache_prefix'] ?? 'unknown',
                'ttl_default' => $cacheStats['cache_ttl_default'] ?? 3600,
                'ttl_user_permissions' => $cacheStats['cache_ttl_user_permissions'] ?? 1800,
                'ttl_statistics' => $cacheStats['cache_ttl_statistics'] ?? 900,
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get approval processing metrics.
     *
     * @param array $filters Optional filters
     * @return array The approval processing metrics
     */
    private function getApprovalProcessingMetrics(array $filters = []): array
    {
        try {
            $query = ApprovalAction::query();

            // Apply filters
            if (isset($filters['date_from'])) {
                $query->where('action_date', '>=', $filters['date_from']);
            }

            if (isset($filters['date_to'])) {
                $query->where('action_date', '<=', $filters['date_to']);
            }

            $totalActions = $query->count();
            $approvedActions = $query->where('action', 'approved')->count();
            $rejectedActions = $query->where('action', 'rejected')->count();
            $forwardedActions = $query->where('action', 'forwarded')->count();
            $delegatedActions = $query->where('action', 'delegated')->count();

            // Get unique users who performed actions
            $uniqueUsers = $query->distinct('approver_id')->count();

            return [
                'total_actions' => $totalActions,
                'approved_actions' => $approvedActions,
                'rejected_actions' => $rejectedActions,
                'forwarded_actions' => $forwardedActions,
                'delegated_actions' => $delegatedActions,
                'unique_users' => $uniqueUsers,
                'approval_rate' => $totalActions > 0 ? round(($approvedActions / $totalActions) * 100, 2) : 0,
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get user activity metrics.
     *
     * @param array $filters Optional filters
     * @return array The user activity metrics
     */
    private function getUserActivityMetrics(array $filters = []): array
    {
        try {
            $query = ApprovalAction::with('approver');

            // Apply filters
            if (isset($filters['date_from'])) {
                $query->where('action_date', '>=', $filters['date_from']);
            }

            if (isset($filters['date_to'])) {
                $query->where('action_date', '<=', $filters['date_to']);
            }

            $actions = $query->get();

            // Group by user
            $userActivity = $actions->groupBy('approver_id')->map(function ($userActions) {
                return [
                    'total_actions' => $userActions->count(),
                    'approved_actions' => $userActions->where('action', 'approved')->count(),
                    'rejected_actions' => $userActions->where('action', 'rejected')->count(),
                    'forwarded_actions' => $userActions->where('action', 'forwarded')->count(),
                    'delegated_actions' => $userActions->where('action', 'delegated')->count(),
                ];
            });

            return [
                'total_users' => $userActivity->count(),
                'most_active_user' => $userActivity->sortByDesc('total_actions')->first(),
                'average_actions_per_user' => $userActivity->count() > 0 ? round($actions->count() / $userActivity->count(), 2) : 0,
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get system alerts.
     *
     * @return array The system alerts
     */
    public function getSystemAlerts(): array
    {
        try {
            $alerts = [];

            // Check for overdue approvals
            $overdueApprovals = DocumentApproval::where('overall_status', 'pending')
                ->where('submitted_at', '<', now()->subDays(7))
                ->count();

            if ($overdueApprovals > 0) {
                $alerts[] = [
                    'type' => 'warning',
                    'message' => "{$overdueApprovals} approvals are overdue (more than 7 days)",
                    'severity' => 'medium',
                ];
            }

            // Check for high pending approvals
            $pendingApprovals = DocumentApproval::where('overall_status', 'pending')->count();
            if ($pendingApprovals > 100) {
                $alerts[] = [
                    'type' => 'warning',
                    'message' => "High number of pending approvals: {$pendingApprovals}",
                    'severity' => 'medium',
                ];
            }

            // Check for inactive approval flows
            $inactiveFlows = ApprovalFlow::where('is_active', false)->count();
            if ($inactiveFlows > 0) {
                $alerts[] = [
                    'type' => 'info',
                    'message' => "{$inactiveFlows} approval flows are inactive",
                    'severity' => 'low',
                ];
            }

            return $alerts;
        } catch (\Exception $e) {
            Log::error('Failed to get system alerts', [
                'error' => $e->getMessage(),
            ]);
            return [
                [
                    'type' => 'error',
                    'message' => 'Failed to retrieve system alerts',
                    'severity' => 'high',
                ],
            ];
        }
    }

    /**
     * Get system uptime.
     *
     * @return array The uptime information
     */
    public function getSystemUptime(): array
    {
        try {
            // This is a simplified approach. In production, you might want to
            // implement more sophisticated uptime tracking
            return [
                'status' => 'operational',
                'last_check' => now(),
                'uptime_percentage' => 99.9, // Placeholder
                'last_incident' => null,
            ];
        } catch (\Exception $e) {
            return [
                'status' => 'unknown',
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get resource usage.
     *
     * @return array The resource usage
     */
    public function getResourceUsage(): array
    {
        try {
            return [
                'memory_usage' => memory_get_usage(true),
                'memory_peak' => memory_get_peak_usage(true),
                'memory_limit' => ini_get('memory_limit'),
                'disk_free_space' => disk_free_space('/'),
                'disk_total_space' => disk_total_space('/'),
            ];
        } catch (\Exception $e) {
            return [
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Get performance recommendations.
     *
     * @return array The performance recommendations
     */
    public function getPerformanceRecommendations(): array
    {
        try {
            $recommendations = [];

            // Check for slow queries
            $slowApprovals = DocumentApproval::where('overall_status', 'pending')
                ->where('submitted_at', '<', now()->subDays(5))
                ->count();

            if ($slowApprovals > 10) {
                $recommendations[] = [
                    'type' => 'performance',
                    'message' => 'Consider implementing approval escalation for slow approvals',
                    'priority' => 'medium',
                ];
            }

            // Check cache hit rate (simplified)
            $recommendations[] = [
                'type' => 'cache',
                'message' => 'Monitor cache hit rates and adjust TTL values as needed',
                'priority' => 'low',
            ];

            // Check for database optimization
            $totalApprovals = DocumentApproval::count();
            if ($totalApprovals > 10000) {
                $recommendations[] = [
                    'type' => 'database',
                    'message' => 'Consider implementing database indexing for large approval datasets',
                    'priority' => 'medium',
                ];
            }

            return $recommendations;
        } catch (\Exception $e) {
            return [
                [
                    'type' => 'error',
                    'message' => 'Failed to generate performance recommendations',
                    'priority' => 'high',
                ],
            ];
        }
    }

    /**
     * Generate monitoring report.
     *
     * @param array $filters Optional filters
     * @return array The monitoring report
     */
    public function generateMonitoringReport(array $filters = []): array
    {
        try {
            return [
                'timestamp' => now(),
                'health_status' => $this->getSystemHealth(),
                'performance_metrics' => $this->getPerformanceMetrics($filters),
                'system_alerts' => $this->getSystemAlerts(),
                'uptime' => $this->getSystemUptime(),
                'resource_usage' => $this->getResourceUsage(),
                'recommendations' => $this->getPerformanceRecommendations(),
            ];
        } catch (\Exception $e) {
            Log::error('Failed to generate monitoring report', [
                'error' => $e->getMessage(),
            ]);
            return [
                'timestamp' => now(),
                'error' => $e->getMessage(),
            ];
        }
    }
}
