<?php

namespace App\Services;

use App\Models\DocumentApproval;
use App\Models\ApprovalAction;
use App\Models\ApprovalFlow;
use App\Models\ApprovalStage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ApprovalAnalyticsService
{
    /**
     * Get overall approval performance metrics
     */
    public function getOverallMetrics(): array
    {
        $totalApprovals = DocumentApproval::count();
        $pendingApprovals = DocumentApproval::where('overall_status', 'pending')->count();
        $approvedApprovals = DocumentApproval::where('overall_status', 'approved')->count();
        $rejectedApprovals = DocumentApproval::where('overall_status', 'rejected')->count();

        $avgApprovalTime = $this->getAverageApprovalTime();
        $avgResponseTime = $this->getAverageResponseTime();

        return [
            'total_approvals' => $totalApprovals,
            'pending_approvals' => $pendingApprovals,
            'approved_approvals' => $approvedApprovals,
            'rejected_approvals' => $rejectedApprovals,
            'approval_rate' => $totalApprovals > 0 ? round(($approvedApprovals / $totalApprovals) * 100, 2) : 0,
            'rejection_rate' => $totalApprovals > 0 ? round(($rejectedApprovals / $totalApprovals) * 100, 2) : 0,
            'average_approval_time_hours' => $avgApprovalTime,
            'average_response_time_hours' => $avgResponseTime
        ];
    }

    /**
     * Get approval performance by document type
     */
    public function getPerformanceByDocumentType(): array
    {
        $documentTypes = DocumentApproval::select('document_type')
            ->distinct()
            ->pluck('document_type');

        $performance = [];

        foreach ($documentTypes as $documentType) {
            $approvals = DocumentApproval::where('document_type', $documentType);
            $total = $approvals->count();
            $approved = $approvals->where('overall_status', 'approved')->count();
            $rejected = $approvals->where('overall_status', 'rejected')->count();
            $pending = $approvals->where('overall_status', 'pending')->count();

            $avgTime = $this->getAverageApprovalTimeByDocumentType($documentType);

            $performance[] = [
                'document_type' => $documentType,
                'total' => $total,
                'approved' => $approved,
                'rejected' => $rejected,
                'pending' => $pending,
                'approval_rate' => $total > 0 ? round(($approved / $total) * 100, 2) : 0,
                'average_time_hours' => $avgTime
            ];
        }

        return $performance;
    }

    /**
     * Get approval performance by stage
     */
    public function getPerformanceByStage(): array
    {
        $stages = ApprovalStage::with(['approvalFlow', 'approvers'])
            ->get();

        $performance = [];

        foreach ($stages as $stage) {
            $actions = ApprovalAction::where('approval_stage_id', $stage->id);
            $total = $actions->count();
            $approved = $actions->where('action', 'approved')->count();
            $rejected = $actions->where('action', 'rejected')->count();
            $escalated = $actions->where('action', 'escalated')->count();

            $avgTime = $this->getAverageStageTime($stage->id);

            $performance[] = [
                'stage_id' => $stage->id,
                'stage_name' => $stage->stage_name,
                'flow_name' => $stage->approvalFlow->name,
                'total_actions' => $total,
                'approved' => $approved,
                'rejected' => $rejected,
                'escalated' => $escalated,
                'approval_rate' => $total > 0 ? round(($approved / $total) * 100, 2) : 0,
                'average_time_hours' => $avgTime,
                'escalation_rate' => $total > 0 ? round(($escalated / $total) * 100, 2) : 0
            ];
        }

        return $performance;
    }

    /**
     * Get approval performance by approver
     */
    public function getPerformanceByApprover(): array
    {
        $approvers = ApprovalAction::select('approver_id')
            ->distinct()
            ->with('approver')
            ->get();

        $performance = [];

        foreach ($approvers as $approverAction) {
            $approver = $approverAction->approver;
            if (!$approver) continue;

            $actions = ApprovalAction::where('approver_id', $approver->id);
            $total = $actions->count();
            $approved = $actions->where('action', 'approved')->count();
            $rejected = $actions->where('action', 'rejected')->count();
            $delegated = $actions->where('action', 'delegated')->count();

            $avgTime = $this->getAverageApproverTime($approver->id);

            $performance[] = [
                'approver_id' => $approver->id,
                'approver_name' => $approver->name,
                'approver_email' => $approver->email,
                'total_actions' => $total,
                'approved' => $approved,
                'rejected' => $rejected,
                'delegated' => $delegated,
                'approval_rate' => $total > 0 ? round(($approved / $total) * 100, 2) : 0,
                'average_time_hours' => $avgTime,
                'delegation_rate' => $total > 0 ? round(($delegated / $total) * 100, 2) : 0
            ];
        }

        return $performance;
    }

    /**
     * Get approval trends over time
     */
    public function getApprovalTrends(int $days = 30): array
    {
        $startDate = Carbon::now()->subDays($days);

        $trends = DocumentApproval::where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total,
                        SUM(CASE WHEN overall_status = "approved" THEN 1 ELSE 0 END) as approved,
                        SUM(CASE WHEN overall_status = "rejected" THEN 1 ELSE 0 END) as rejected,
                        SUM(CASE WHEN overall_status = "pending" THEN 1 ELSE 0 END) as pending')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        return $trends->map(function ($trend) {
            return [
                'date' => $trend->date,
                'total' => $trend->total,
                'approved' => $trend->approved,
                'rejected' => $trend->rejected,
                'pending' => $trend->pending,
                'approval_rate' => $trend->total > 0 ? round(($trend->approved / $trend->total) * 100, 2) : 0
            ];
        })->toArray();
    }

    /**
     * Get bottleneck analysis
     */
    public function getBottleneckAnalysis(): array
    {
        $stages = ApprovalStage::with(['approvalFlow'])
            ->get();

        $bottlenecks = [];

        foreach ($stages as $stage) {
            $avgTime = $this->getAverageStageTime($stage->id);
            $escalationRate = $this->getStageEscalationRate($stage->id);
            $pendingCount = $this->getPendingCountByStage($stage->id);

            $bottlenecks[] = [
                'stage_id' => $stage->id,
                'stage_name' => $stage->stage_name,
                'flow_name' => $stage->approvalFlow->name,
                'average_time_hours' => $avgTime,
                'escalation_rate' => $escalationRate,
                'pending_count' => $pendingCount,
                'bottleneck_score' => $this->calculateBottleneckScore($avgTime, $escalationRate, $pendingCount)
            ];
        }

        // Sort by bottleneck score (highest first)
        usort($bottlenecks, function ($a, $b) {
            return $b['bottleneck_score'] <=> $a['bottleneck_score'];
        });

        return $bottlenecks;
    }

    /**
     * Get approval success rate by flow
     */
    public function getSuccessRateByFlow(): array
    {
        $flows = ApprovalFlow::with(['stages'])
            ->get();

        $successRates = [];

        foreach ($flows as $flow) {
            $approvals = DocumentApproval::where('approval_flow_id', $flow->id);
            $total = $approvals->count();
            $completed = $approvals->whereIn('overall_status', ['approved', 'rejected'])->count();
            $approved = $approvals->where('overall_status', 'approved')->count();

            $avgTime = $this->getAverageApprovalTimeByFlow($flow->id);

            $successRates[] = [
                'flow_id' => $flow->id,
                'flow_name' => $flow->name,
                'document_type' => $flow->document_type,
                'total_approvals' => $total,
                'completed_approvals' => $completed,
                'approved_approvals' => $approved,
                'success_rate' => $completed > 0 ? round(($approved / $completed) * 100, 2) : 0,
                'completion_rate' => $total > 0 ? round(($completed / $total) * 100, 2) : 0,
                'average_time_hours' => $avgTime
            ];
        }

        return $successRates;
    }

    /**
     * Get average approval time
     */
    private function getAverageApprovalTime(): float
    {
        $completedApprovals = DocumentApproval::whereIn('overall_status', ['approved', 'rejected'])
            ->whereNotNull('completed_at')
            ->get();

        if ($completedApprovals->count() === 0) {
            return 0;
        }

        $totalHours = $completedApprovals->sum(function ($approval) {
            return $approval->submitted_at->diffInHours($approval->completed_at);
        });

        return round($totalHours / $completedApprovals->count(), 2);
    }

    /**
     * Get average response time
     */
    private function getAverageResponseTime(): float
    {
        $firstActions = ApprovalAction::select('document_approval_id')
            ->selectRaw('MIN(action_date) as first_action_date')
            ->groupBy('document_approval_id');

        $responseTimes = DocumentApproval::joinSub($firstActions, 'first_actions', function ($join) {
            $join->on('document_approvals.id', '=', 'first_actions.document_approval_id');
        })
            ->selectRaw('TIMESTAMPDIFF(HOUR, submitted_at, first_action_date) as response_hours')
            ->get();

        if ($responseTimes->count() === 0) {
            return 0;
        }

        return round($responseTimes->avg('response_hours'), 2);
    }

    /**
     * Get average approval time by document type
     */
    private function getAverageApprovalTimeByDocumentType(string $documentType): float
    {
        $completedApprovals = DocumentApproval::where('document_type', $documentType)
            ->whereIn('overall_status', ['approved', 'rejected'])
            ->whereNotNull('completed_at')
            ->get();

        if ($completedApprovals->count() === 0) {
            return 0;
        }

        $totalHours = $completedApprovals->sum(function ($approval) {
            return $approval->submitted_at->diffInHours($approval->completed_at);
        });

        return round($totalHours / $completedApprovals->count(), 2);
    }

    /**
     * Get average stage time
     */
    private function getAverageStageTime(int $stageId): float
    {
        $actions = ApprovalAction::where('approval_stage_id', $stageId)
            ->whereIn('action', ['approved', 'rejected'])
            ->get();

        if ($actions->count() === 0) {
            return 0;
        }

        $totalHours = $actions->sum(function ($action) {
            $approval = $action->documentApproval;
            return $approval->submitted_at->diffInHours($action->action_date);
        });

        return round($totalHours / $actions->count(), 2);
    }

    /**
     * Get average approver time
     */
    private function getAverageApproverTime(int $approverId): float
    {
        $actions = ApprovalAction::where('approver_id', $approverId)
            ->whereIn('action', ['approved', 'rejected'])
            ->get();

        if ($actions->count() === 0) {
            return 0;
        }

        $totalHours = $actions->sum(function ($action) {
            $approval = $action->documentApproval;
            return $approval->submitted_at->diffInHours($action->action_date);
        });

        return round($totalHours / $actions->count(), 2);
    }

    /**
     * Get stage escalation rate
     */
    private function getStageEscalationRate(int $stageId): float
    {
        $totalActions = ApprovalAction::where('approval_stage_id', $stageId)->count();
        $escalatedActions = ApprovalAction::where('approval_stage_id', $stageId)
            ->where('action', 'escalated')
            ->count();

        return $totalActions > 0 ? round(($escalatedActions / $totalActions) * 100, 2) : 0;
    }

    /**
     * Get pending count by stage
     */
    private function getPendingCountByStage(int $stageId): int
    {
        return DocumentApproval::where('current_stage_id', $stageId)
            ->where('overall_status', 'pending')
            ->count();
    }

    /**
     * Get average approval time by flow
     */
    private function getAverageApprovalTimeByFlow(int $flowId): float
    {
        $completedApprovals = DocumentApproval::where('approval_flow_id', $flowId)
            ->whereIn('overall_status', ['approved', 'rejected'])
            ->whereNotNull('completed_at')
            ->get();

        if ($completedApprovals->count() === 0) {
            return 0;
        }

        $totalHours = $completedApprovals->sum(function ($approval) {
            return $approval->submitted_at->diffInHours($approval->completed_at);
        });

        return round($totalHours / $completedApprovals->count(), 2);
    }

    /**
     * Calculate bottleneck score
     */
    private function calculateBottleneckScore(float $avgTime, float $escalationRate, int $pendingCount): float
    {
        // Normalize factors (0-1 scale)
        $timeScore = min($avgTime / 72, 1); // Normalize to 72 hours max
        $escalationScore = $escalationRate / 100; // Already percentage
        $pendingScore = min($pendingCount / 10, 1); // Normalize to 10 pending max

        // Weighted average
        return round(($timeScore * 0.4 + $escalationScore * 0.4 + $pendingScore * 0.2) * 100, 2);
    }
}
