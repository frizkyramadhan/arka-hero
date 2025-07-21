<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ApprovalMonitoringService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

/**
 * API Controller for Approval System Monitoring
 */
class ApprovalMonitoringApiController extends Controller
{
    protected ApprovalMonitoringService $monitoringService;

    public function __construct(ApprovalMonitoringService $monitoringService)
    {
        $this->monitoringService = $monitoringService;
    }

    /**
     * Get system health
     *
     * @return JsonResponse
     */
    public function getHealth(): JsonResponse
    {
        try {
            $health = $this->monitoringService->getSystemHealth();

            return response()->json([
                'success' => true,
                'data' => $health,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get system health', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve system health',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get performance metrics
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getPerformance(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['date_from', 'date_to']);
            $performance = $this->monitoringService->getPerformanceMetrics($filters);

            return response()->json([
                'success' => true,
                'data' => $performance,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get performance metrics', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve performance metrics',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get system alerts
     *
     * @return JsonResponse
     */
    public function getAlerts(): JsonResponse
    {
        try {
            $alerts = $this->monitoringService->getSystemAlerts();

            return response()->json([
                'success' => true,
                'data' => $alerts,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get system alerts', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve system alerts',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get system uptime
     *
     * @return JsonResponse
     */
    public function getUptime(): JsonResponse
    {
        try {
            $uptime = $this->monitoringService->getSystemUptime();

            return response()->json([
                'success' => true,
                'data' => $uptime,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get system uptime', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve system uptime',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get resource usage
     *
     * @return JsonResponse
     */
    public function getResources(): JsonResponse
    {
        try {
            $resources = $this->monitoringService->getResourceUsage();

            return response()->json([
                'success' => true,
                'data' => $resources,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get resource usage', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve resource usage',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get performance recommendations
     *
     * @return JsonResponse
     */
    public function getRecommendations(): JsonResponse
    {
        try {
            $recommendations = $this->monitoringService->getPerformanceRecommendations();

            return response()->json([
                'success' => true,
                'data' => $recommendations,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get performance recommendations', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve performance recommendations',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate monitoring report
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function generateReport(Request $request): JsonResponse
    {
        try {
            $filters = $request->only(['date_from', 'date_to']);
            $report = $this->monitoringService->generateMonitoringReport($filters);

            return response()->json([
                'success' => true,
                'data' => $report,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to generate monitoring report', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to generate monitoring report',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get system status (public endpoint)
     *
     * @return JsonResponse
     */
    public function getStatus(): JsonResponse
    {
        try {
            $health = $this->monitoringService->getSystemHealth();
            $uptime = $this->monitoringService->getSystemUptime();

            return response()->json([
                'success' => true,
                'data' => [
                    'status' => $health['status'],
                    'uptime' => $uptime,
                    'timestamp' => now(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get system status', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to retrieve system status',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
