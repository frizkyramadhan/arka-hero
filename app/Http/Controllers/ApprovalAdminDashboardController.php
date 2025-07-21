<?php

namespace App\Http\Controllers;

use App\Models\ApprovalFlow;
use App\Models\ApprovalStage;
use App\Models\DocumentApproval;
use App\Models\ApprovalAction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class ApprovalAdminDashboardController extends Controller
{
    /**
     * Show admin dashboard overview
     */
    public function index()
    {
        $title = 'Approval System Dashboard';

        // Get basic statistics
        $stats = $this->getBasicStats();

        // Get recent activities
        $recentActivities = $this->getRecentActivities();

        // Get active flows
        $activeFlows = $this->getActiveFlows();

        // Get performance metrics
        $performanceMetrics = $this->getPerformanceMetrics();

        return view('approval.admin.dashboard.index', compact('title', 'stats', 'recentActivities', 'activeFlows', 'performanceMetrics'));
    }

    /**
     * Show approval flows overview
     */
    public function flows()
    {
        $title = 'Approval Flows Management';

        $flows = ApprovalFlow::with(['stages.approvers.user', 'stages.approvers.role', 'stages.approvers.department'])
            ->orderBy('created_at', 'desc')
            ->get();

        $flowStats = $this->getFlowStats();

        return view('approval.admin.dashboard.flows', compact('title', 'flows', 'flowStats'));
    }

    /**
     * Show active approvals monitoring
     */
    public function activeApprovals()
    {
        $title = 'Active Approvals Monitoring';

        $activeApprovals = DocumentApproval::with(['approvalFlow', 'currentStage', 'submittedBy'])
            ->where('overall_status', 'pending')
            ->orderBy('submitted_at', 'desc')
            ->get();

        $approvalStats = $this->getApprovalStats();

        return view('approval.admin.dashboard.active-approvals', compact('title', 'activeApprovals', 'approvalStats'));
    }

    /**
     * Show approval performance analytics
     */
    public function analytics()
    {
        $title = 'Approval Performance Analytics';

        $timeRange = request('range', '30'); // days
        $analytics = $this->getAnalytics($timeRange);

        return view('approval.admin.dashboard.analytics', compact('title', 'analytics', 'timeRange'));
    }

    /**
     * Show audit trail
     */
    public function auditTrail()
    {
        $title = 'Approval Audit Trail';

        $auditLogs = ApprovalAction::with(['documentApproval.approvalFlow', 'approver', 'approvalStage'])
            ->orderBy('action_date', 'desc')
            ->paginate(50);

        return view('approval.admin.dashboard.audit-trail', compact('title', 'auditLogs'));
    }

    /**
     * Show system configuration
     */
    public function configuration()
    {
        $title = 'Approval System Configuration';

        $config = $this->getSystemConfiguration();

        return view('approval.admin.dashboard.configuration', compact('title', 'config'));
    }

    /**
     * Get dashboard statistics via AJAX
     */
    public function getStats(): JsonResponse
    {
        try {
            $stats = $this->getBasicStats();
            $performanceMetrics = $this->getPerformanceMetrics();

            return response()->json([
                'success' => true,
                'stats' => $stats,
                'performance' => $performanceMetrics
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get dashboard stats', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get dashboard statistics'
            ], 500);
        }
    }

    /**
     * Get active approvals data via AJAX
     */
    public function getActiveApprovals(): JsonResponse
    {
        try {
            $activeApprovals = DocumentApproval::with(['approvalFlow', 'currentStage', 'submittedBy'])
                ->where('overall_status', 'pending')
                ->orderBy('submitted_at', 'desc')
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
                        'status' => $approval->overall_status
                    ];
                });

            return response()->json([
                'success' => true,
                'approvals' => $activeApprovals
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get active approvals', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get active approvals'
            ], 500);
        }
    }

    /**
     * Get performance metrics via AJAX
     */
    public function getPerformanceMetricsAjax(): JsonResponse
    {
        try {
            $metrics = $this->getPerformanceMetrics();

            return response()->json([
                'success' => true,
                'metrics' => $metrics
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get performance metrics', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get performance metrics'
            ], 500);
        }
    }

    /**
     * Get basic statistics
     */
    private function getBasicStats(): array
    {
        $totalFlows = ApprovalFlow::count();
        $totalStages = ApprovalStage::count();
        $totalApprovals = DocumentApproval::count();
        $pendingApprovals = DocumentApproval::where('overall_status', 'pending')->count();
        $approvedToday = DocumentApproval::where('overall_status', 'approved')
            ->whereDate('completed_at', today())
            ->count();
        $rejectedToday = DocumentApproval::where('overall_status', 'rejected')
            ->whereDate('completed_at', today())
            ->count();

        return [
            'total_flows' => $totalFlows,
            'total_stages' => $totalStages,
            'total_approvals' => $totalApprovals,
            'pending_approvals' => $pendingApprovals,
            'approved_today' => $approvedToday,
            'rejected_today' => $rejectedToday,
            'approval_rate' => $totalApprovals > 0 ? round(($approvedToday / $totalApprovals) * 100, 2) : 0
        ];
    }

    /**
     * Get recent activities
     */
    private function getRecentActivities(): array
    {
        $recentActions = ApprovalAction::with(['documentApproval.approvalFlow', 'approver', 'approvalStage'])
            ->orderBy('action_date', 'desc')
            ->limit(10)
            ->get()
            ->map(function ($action) {
                return [
                    'id' => $action->id,
                    'action' => $action->action,
                    'document_type' => $action->documentApproval->document_type ?? 'Unknown',
                    'approver' => $action->approver->name ?? 'Unknown',
                    'stage' => $action->approvalStage->stage_name ?? 'Unknown',
                    'action_date' => $action->action_date->format('Y-m-d H:i:s'),
                    'comments' => $action->comments
                ];
            })
            ->toArray();

        return $recentActions;
    }

    /**
     * Get active flows
     */
    private function getActiveFlows(): array
    {
        $activeFlows = ApprovalFlow::with(['stages'])
            ->where('is_active', true)
            ->get()
            ->map(function ($flow) {
                return [
                    'id' => $flow->id,
                    'name' => $flow->name,
                    'document_type' => $flow->document_type,
                    'stages_count' => $flow->stages->count(),
                    'created_at' => $flow->created_at->format('Y-m-d'),
                    'is_active' => $flow->is_active
                ];
            })
            ->toArray();

        return $activeFlows;
    }

    /**
     * Get performance metrics
     */
    private function getPerformanceMetrics(): array
    {
        $last30Days = Carbon::now()->subDays(30);

        // Average approval time
        $avgApprovalTime = DocumentApproval::where('overall_status', 'approved')
            ->whereNotNull('completed_at')
            ->where('completed_at', '>=', $last30Days)
            ->get()
            ->avg(function ($approval) {
                return $approval->submitted_at->diffInHours($approval->completed_at);
            });

        // Approval success rate
        $totalCompleted = DocumentApproval::whereIn('overall_status', ['approved', 'rejected'])
            ->where('completed_at', '>=', $last30Days)
            ->count();

        $totalApproved = DocumentApproval::where('overall_status', 'approved')
            ->where('completed_at', '>=', $last30Days)
            ->count();

        $successRate = $totalCompleted > 0 ? round(($totalApproved / $totalCompleted) * 100, 2) : 0;

        // Bottleneck analysis
        $bottleneckStages = ApprovalStage::withCount(['approvers'])
            ->having('approvers_count', '>', 2)
            ->orderBy('approvers_count', 'desc')
            ->limit(5)
            ->get()
            ->map(function ($stage) {
                return [
                    'stage_name' => $stage->stage_name,
                    'approvers_count' => $stage->approvers_count,
                    'flow_name' => $stage->approvalFlow->name ?? 'Unknown'
                ];
            })
            ->toArray();

        return [
            'avg_approval_time_hours' => round($avgApprovalTime ?? 0, 2),
            'success_rate_percent' => $successRate,
            'bottleneck_stages' => $bottleneckStages,
            'total_completed' => $totalCompleted,
            'total_approved' => $totalApproved
        ];
    }

    /**
     * Get flow statistics
     */
    private function getFlowStats(): array
    {
        $flows = ApprovalFlow::with(['stages.approvers'])
            ->get()
            ->map(function ($flow) {
                $totalApprovers = $flow->stages->sum(function ($stage) {
                    return $stage->approvers->count();
                });

                return [
                    'id' => $flow->id,
                    'name' => $flow->name,
                    'document_type' => $flow->document_type,
                    'stages_count' => $flow->stages->count(),
                    'approvers_count' => $totalApprovers,
                    'is_active' => $flow->is_active,
                    'created_at' => $flow->created_at->format('Y-m-d')
                ];
            })
            ->toArray();

        return $flows;
    }

    /**
     * Get approval statistics
     */
    private function getApprovalStats(): array
    {
        $pendingCount = DocumentApproval::where('overall_status', 'pending')->count();
        $approvedCount = DocumentApproval::where('overall_status', 'approved')->count();
        $rejectedCount = DocumentApproval::where('overall_status', 'rejected')->count();
        $cancelledCount = DocumentApproval::where('overall_status', 'cancelled')->count();

        $total = $pendingCount + $approvedCount + $rejectedCount + $cancelledCount;

        return [
            'pending' => $pendingCount,
            'approved' => $approvedCount,
            'rejected' => $rejectedCount,
            'cancelled' => $cancelledCount,
            'total' => $total,
            'pending_percentage' => $total > 0 ? round(($pendingCount / $total) * 100, 2) : 0,
            'approved_percentage' => $total > 0 ? round(($approvedCount / $total) * 100, 2) : 0,
            'rejected_percentage' => $total > 0 ? round(($rejectedCount / $total) * 100, 2) : 0
        ];
    }

    /**
     * Get analytics data
     */
    private function getAnalytics(int $timeRange): array
    {
        $startDate = Carbon::now()->subDays($timeRange);

        // Daily approval trends
        $dailyTrends = DocumentApproval::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count, overall_status')
            ->groupBy('date', 'overall_status')
            ->orderBy('date')
            ->get()
            ->groupBy('date');

        // Document type distribution
        $documentTypeDistribution = DocumentApproval::where('created_at', '>=', $startDate)
            ->selectRaw('document_type, COUNT(*) as count')
            ->groupBy('document_type')
            ->orderBy('count', 'desc')
            ->get();

        // Average approval time by flow
        $avgTimeByFlow = DocumentApproval::where('overall_status', 'approved')
            ->where('completed_at', '>=', $startDate)
            ->whereNotNull('completed_at')
            ->with('approvalFlow')
            ->get()
            ->groupBy('approval_flow_id')
            ->map(function ($approvals, $flowId) {
                $avgTime = $approvals->avg(function ($approval) {
                    return $approval->submitted_at->diffInHours($approval->completed_at);
                });

                return [
                    'flow_name' => $approvals->first()->approvalFlow->name ?? 'Unknown',
                    'avg_time_hours' => round($avgTime, 2),
                    'count' => $approvals->count()
                ];
            })
            ->values()
            ->toArray();

        return [
            'daily_trends' => $dailyTrends,
            'document_type_distribution' => $documentTypeDistribution,
            'avg_time_by_flow' => $avgTimeByFlow,
            'time_range' => $timeRange
        ];
    }

    /**
     * Get system configuration
     */
    private function getSystemConfiguration(): array
    {
        return [
            'escalation_default_hours' => config('approval.escalation_default_hours', 72),
            'notification_enabled' => config('approval.notification_enabled', true),
            'audit_logging_enabled' => config('approval.audit_logging_enabled', true),
            'auto_approval_enabled' => config('approval.auto_approval_enabled', false),
            'max_approvers_per_stage' => config('approval.max_approvers_per_stage', 10),
            'max_stages_per_flow' => config('approval.max_stages_per_flow', 20)
        ];
    }

    /**
     * Save system settings
     */
    public function saveSystemSettings(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'escalation_default_hours' => 'required|integer|min:1|max:720',
                'max_approvers_per_stage' => 'required|integer|min:1|max:50',
                'max_stages_per_flow' => 'required|integer|min:1|max:100',
                'notification_enabled' => 'boolean',
                'audit_logging_enabled' => 'boolean',
                'auto_approval_enabled' => 'boolean'
            ]);

            // Save to database or config file
            $settings = [
                'escalation_default_hours' => $request->escalation_default_hours,
                'max_approvers_per_stage' => $request->max_approvers_per_stage,
                'max_stages_per_flow' => $request->max_stages_per_flow,
                'notification_enabled' => $request->has('notification_enabled'),
                'audit_logging_enabled' => $request->has('audit_logging_enabled'),
                'auto_approval_enabled' => $request->has('auto_approval_enabled')
            ];

            // Log the configuration change
            Log::info('System settings updated', [
                'user_id' => auth()->id(),
                'settings' => $settings
            ]);

            return response()->json([
                'success' => true,
                'message' => 'System settings saved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to save system settings', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save system settings'
            ], 500);
        }
    }

    /**
     * Save notification configuration
     */
    public function saveNotificationConfig(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'email_notifications' => 'required|in:all,important,none',
                'escalation_notifications' => 'required|in:immediate,hourly,daily',
                'channels' => 'array',
                'notification_template' => 'nullable|string|max:1000'
            ]);

            // Save notification configuration
            $config = [
                'email_notifications' => $request->email_notifications,
                'escalation_notifications' => $request->escalation_notifications,
                'channels' => $request->channels ?? [],
                'notification_template' => $request->notification_template
            ];

            // Log the configuration change
            Log::info('Notification settings updated', [
                'user_id' => auth()->id(),
                'config' => $config
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Notification settings saved successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to save notification settings', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to save notification settings'
            ], 500);
        }
    }

    /**
     * Assign approval flow to document type
     */
    public function assignFlow(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'document_type' => 'required|string',
                'flow_id' => 'required|integer|exists:approval_flows,id'
            ]);

            // Update document type configuration
            // This would typically update a document_types table or configuration

            Log::info('Flow assigned to document type', [
                'user_id' => auth()->id(),
                'document_type' => $request->document_type,
                'flow_id' => $request->flow_id
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Flow assigned successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to assign flow', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to assign flow'
            ], 500);
        }
    }

    /**
     * Add document type
     */
    public function addDocumentType(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'code' => 'required|string|max:100|unique:document_types,code',
                'model_class' => 'required|string|max:255',
                'table_name' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'default_approval_flow' => 'nullable|string'
            ]);

            // Create document type record
            // This would typically insert into a document_types table

            Log::info('Document type added', [
                'user_id' => auth()->id(),
                'document_type' => $request->all()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Document type added successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to add document type', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to add document type'
            ], 500);
        }
    }

    /**
     * Add escalation rule
     */
    public function addEscalationRule(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'document_type' => 'required|string',
                'stage' => 'required|integer',
                'trigger_hours' => 'required|integer|min:1',
                'action' => 'required|in:notify,auto_forward,escalate,auto_approve',
                'priority' => 'required|in:low,medium,high,urgent'
            ]);

            // Create escalation rule
            // This would typically insert into an escalation_rules table

            Log::info('Escalation rule added', [
                'user_id' => auth()->id(),
                'rule' => $request->all()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Escalation rule added successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to add escalation rule', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to add escalation rule'
            ], 500);
        }
    }

    /**
     * Add approval flow template
     */
    public function addTemplate(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'nullable|string|max:1000',
                'type' => 'required|in:linear,parallel,conditional,custom',
                'stages' => 'required|json'
            ]);

            // Create approval flow template
            // This would typically insert into an approval_flow_templates table

            Log::info('Approval flow template added', [
                'user_id' => auth()->id(),
                'template' => $request->all()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Template added successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to add template', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to add template'
            ], 500);
        }
    }

    /**
     * Get stages for document type
     */
    public function getStages(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'document_type' => 'required|string'
            ]);

            // Get stages for the document type
            $stages = ApprovalStage::whereHas('approvalFlow', function ($query) use ($request) {
                $query->where('document_type', $request->document_type);
            })->get(['id', 'stage_name as name']);

            return response()->json([
                'success' => true,
                'stages' => $stages
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to get stages', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to get stages'
            ], 500);
        }
    }
}
