# Recruitment Duration Analytics - Comprehensive Tracking

## 📊 Overview

Ya, sistem recruitment yang dirancang **dapat menampilkan durasi antar tahapan** dengan detail yang sangat akurat. Berikut adalah analisa lengkap untuk tracking durasi dari FPTK hingga Onboarding.

## ⏱️ Time Tracking Structure

### 1. Enhanced Database Schema

#### Tambahan field untuk recruitment_stage_results:

```sql
ALTER TABLE recruitment_stage_results ADD COLUMN started_at TIMESTAMP NULL AFTER scheduled_date;
ALTER TABLE recruitment_stage_results ADD COLUMN actual_duration_hours DECIMAL(8,2) NULL;
ALTER TABLE recruitment_stage_results ADD COLUMN target_duration_hours DECIMAL(8,2) DEFAULT 72; -- 3 hari default
```

#### Tambahan table untuk SLA tracking:

```sql
CREATE TABLE recruitment_stage_sla (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    stage_id BIGINT UNSIGNED NOT NULL,
    target_duration_hours DECIMAL(8,2) NOT NULL,
    warning_threshold_hours DECIMAL(8,2) NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (stage_id) REFERENCES recruitment_stages(id)
);

-- Default SLA untuk setiap stage
INSERT INTO recruitment_stage_sla (stage_id, target_duration_hours, warning_threshold_hours) VALUES
(1, 72, 48),    -- FPTK: 3 hari target, warning 2 hari
(2, 120, 96),   -- CV Review: 5 hari target, warning 4 hari
(3, 168, 144),  -- Psikotes: 7 hari target, warning 6 hari
(4, 96, 72),    -- Tes Teori: 4 hari target, warning 3 hari
(5, 168, 144),  -- Interview HR: 7 hari target, warning 6 hari
(6, 240, 192),  -- Interview User: 10 hari target, warning 8 hari
(7, 120, 96),   -- Offering: 5 hari target, warning 4 hari
(8, 336, 288),  -- MCU: 14 hari target, warning 12 hari
(9, 72, 48),    -- Hire: 3 hari target, warning 2 hari
(10, 168, 144); -- Onboarding: 7 hari target, warning 6 hari
```

## 📈 Analytics Service

### 1. Recruitment Analytics Service

```php
<?php

namespace App\Services;

use App\Models\RecruitmentCandidate;
use App\Models\RecruitmentStageResult;
use App\Models\RecruitmentRequest;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RecruitmentAnalyticsService
{
    /**
     * Get duration for each stage of a specific candidate
     */
    public function getCandidateStageDurations($candidateId)
    {
        $stageResults = RecruitmentStageResult::with('stage')
            ->where('candidate_id', $candidateId)
            ->orderBy('stage_id')
            ->get();

        $durations = [];
        $totalDuration = 0;

        foreach ($stageResults as $index => $result) {
            $startTime = $result->started_at ?? $result->created_at;
            $endTime = $result->completed_date ?? now();

            $durationHours = $startTime->diffInHours($endTime);
            $durationDays = round($durationHours / 24, 1);

            $durations[] = [
                'stage_name' => $result->stage->stage_name,
                'stage_order' => $result->stage->stage_order,
                'status' => $result->status,
                'started_at' => $startTime,
                'completed_at' => $result->completed_date,
                'duration_hours' => $durationHours,
                'duration_days' => $durationDays,
                'duration_human' => $this->formatDurationHuman($durationHours),
                'is_overdue' => $this->isStageOverdue($result),
                'sla_hours' => $result->stage->sla->target_duration_hours ?? 72,
                'sla_status' => $this->getSLAStatus($result, $durationHours)
            ];

            if ($result->status === 'passed') {
                $totalDuration += $durationHours;
            }
        }

        return [
            'stages' => $durations,
            'total_duration_hours' => $totalDuration,
            'total_duration_days' => round($totalDuration / 24, 1),
            'total_duration_human' => $this->formatDurationHuman($totalDuration),
            'current_stage' => $this->getCurrentStage($stageResults),
            'completion_percentage' => $this->getCompletionPercentage($stageResults)
        ];
    }

    /**
     * Get average duration for each stage across all candidates
     */
    public function getAverageStageDurations($filters = [])
    {
        $query = RecruitmentStageResult::with('stage', 'candidate')
            ->where('status', 'passed')
            ->whereNotNull('completed_date');

        // Apply filters
        if (isset($filters['date_from'])) {
            $query->where('completed_date', '>=', $filters['date_from']);
        }
        if (isset($filters['date_to'])) {
            $query->where('completed_date', '<=', $filters['date_to']);
        }
        if (isset($filters['department_id'])) {
            $query->whereHas('candidate.recruitmentRequest', function($q) use ($filters) {
                $q->where('department_id', $filters['department_id']);
            });
        }

        $results = $query->get();

        $stageAverages = [];
        $stageGroups = $results->groupBy('stage_id');

        foreach ($stageGroups as $stageId => $stageResults) {
            $durations = [];

            foreach ($stageResults as $result) {
                $startTime = $result->started_at ?? $result->created_at;
                $endTime = $result->completed_date;
                $durations[] = $startTime->diffInHours($endTime);
            }

            $averageHours = array_sum($durations) / count($durations);
            $stage = $stageResults->first()->stage;

            $stageAverages[] = [
                'stage_name' => $stage->stage_name,
                'stage_order' => $stage->stage_order,
                'average_hours' => round($averageHours, 2),
                'average_days' => round($averageHours / 24, 1),
                'average_human' => $this->formatDurationHuman($averageHours),
                'sample_size' => count($durations),
                'sla_hours' => $stage->sla->target_duration_hours ?? 72,
                'sla_compliance' => $this->calculateSLACompliance($durations, $stage->sla->target_duration_hours ?? 72)
            ];
        }

        return collect($stageAverages)->sortBy('stage_order')->values();
    }

    /**
     * Get recruitment funnel analytics
     */
    public function getRecruitmentFunnel($filters = [])
    {
        $query = RecruitmentCandidate::with('stageResults.stage');

        // Apply filters
        if (isset($filters['date_from'])) {
            $query->where('applied_at', '>=', $filters['date_from']);
        }
        if (isset($filters['date_to'])) {
            $query->where('applied_at', '<=', $filters['date_to']);
        }

        $candidates = $query->get();

        $funnel = [];
        $stages = collect([
            ['name' => 'Applied', 'order' => 0],
            ['name' => 'CV Review', 'order' => 2],
            ['name' => 'Psikotes', 'order' => 3],
            ['name' => 'Tes Teori', 'order' => 4],
            ['name' => 'Interview HR', 'order' => 5],
            ['name' => 'Interview User', 'order' => 6],
            ['name' => 'Offering', 'order' => 7],
            ['name' => 'MCU', 'order' => 8],
            ['name' => 'Hired', 'order' => 9]
        ]);

        $totalApplied = $candidates->count();

        foreach ($stages as $stage) {
            if ($stage['order'] === 0) {
                $count = $totalApplied;
                $averageDuration = 0;
            } else {
                $passedResults = $candidates->flatMap->stageResults
                    ->where('stage.stage_order', $stage['order'])
                    ->where('status', 'passed');

                $count = $passedResults->count();

                // Calculate average duration for this stage
                $durations = $passedResults->map(function($result) {
                    $startTime = $result->started_at ?? $result->created_at;
                    $endTime = $result->completed_date ?? now();
                    return $startTime->diffInHours($endTime);
                });

                $averageDuration = $durations->count() > 0 ? $durations->average() : 0;
            }

            $funnel[] = [
                'stage_name' => $stage['name'],
                'stage_order' => $stage['order'],
                'count' => $count,
                'percentage' => $totalApplied > 0 ? round(($count / $totalApplied) * 100, 1) : 0,
                'conversion_rate' => $stage['order'] > 0 && isset($funnel[$stage['order'] - 1])
                    ? round(($count / $funnel[$stage['order'] - 1]['count']) * 100, 1)
                    : 100,
                'average_duration_hours' => round($averageDuration, 2),
                'average_duration_days' => round($averageDuration / 24, 1),
                'average_duration_human' => $this->formatDurationHuman($averageDuration)
            ];
        }

        return $funnel;
    }

    /**
     * Get time-to-hire statistics
     */
    public function getTimeToHireStats($filters = [])
    {
        $query = RecruitmentCandidate::with('stageResults')
            ->where('current_status', 'hired');

        // Apply filters
        if (isset($filters['date_from'])) {
            $query->where('applied_at', '>=', $filters['date_from']);
        }
        if (isset($filters['date_to'])) {
            $query->where('applied_at', '<=', $filters['date_to']);
        }

        $hiredCandidates = $query->get();

        $timeToHireData = [];
        foreach ($hiredCandidates as $candidate) {
            $appliedAt = $candidate->applied_at;
            $hiredResult = $candidate->stageResults
                ->where('stage.stage_order', 9)
                ->where('status', 'passed')
                ->first();

            if ($hiredResult && $hiredResult->completed_date) {
                $timeToHireHours = $appliedAt->diffInHours($hiredResult->completed_date);
                $timeToHireData[] = [
                    'candidate_name' => $candidate->fullname,
                    'applied_at' => $appliedAt,
                    'hired_at' => $hiredResult->completed_date,
                    'time_to_hire_hours' => $timeToHireHours,
                    'time_to_hire_days' => round($timeToHireHours / 24, 1),
                    'time_to_hire_human' => $this->formatDurationHuman($timeToHireHours)
                ];
            }
        }

        if (empty($timeToHireData)) {
            return [
                'average_time_to_hire_hours' => 0,
                'average_time_to_hire_days' => 0,
                'average_time_to_hire_human' => 'No data',
                'fastest_hire_days' => 0,
                'slowest_hire_days' => 0,
                'sample_size' => 0
            ];
        }

        $hours = collect($timeToHireData)->pluck('time_to_hire_hours');
        $days = collect($timeToHireData)->pluck('time_to_hire_days');

        return [
            'average_time_to_hire_hours' => round($hours->average(), 2),
            'average_time_to_hire_days' => round($days->average(), 1),
            'average_time_to_hire_human' => $this->formatDurationHuman($hours->average()),
            'fastest_hire_days' => $days->min(),
            'slowest_hire_days' => $days->max(),
            'median_time_to_hire_days' => $days->median(),
            'sample_size' => count($timeToHireData),
            'details' => $timeToHireData
        ];
    }

    /**
     * Get bottleneck analysis
     */
    public function getBottleneckAnalysis($filters = [])
    {
        $stageAverages = $this->getAverageStageDurations($filters);

        $bottlenecks = $stageAverages->map(function($stage) {
            $slaCompliance = $stage['sla_compliance'];
            $overdueFactor = $stage['average_hours'] / $stage['sla_hours'];

            return [
                'stage_name' => $stage['stage_name'],
                'average_days' => $stage['average_days'],
                'sla_days' => round($stage['sla_hours'] / 24, 1),
                'sla_compliance' => $slaCompliance,
                'overdue_factor' => round($overdueFactor, 2),
                'bottleneck_score' => $this->calculateBottleneckScore($stage),
                'recommendation' => $this->getBottleneckRecommendation($stage)
            ];
        })->sortByDesc('bottleneck_score');

        return $bottlenecks;
    }

    // Helper methods
    private function formatDurationHuman($hours)
    {
        if ($hours < 1) {
            return round($hours * 60) . ' minutes';
        } elseif ($hours < 24) {
            return round($hours, 1) . ' hours';
        } else {
            $days = floor($hours / 24);
            $remainingHours = $hours % 24;

            if ($remainingHours < 1) {
                return $days . ' days';
            } else {
                return $days . ' days, ' . round($remainingHours, 1) . ' hours';
            }
        }
    }

    private function isStageOverdue($stageResult)
    {
        $slaHours = $stageResult->stage->sla->target_duration_hours ?? 72;
        $startTime = $stageResult->started_at ?? $stageResult->created_at;
        $endTime = $stageResult->completed_date ?? now();

        return $startTime->diffInHours($endTime) > $slaHours;
    }

    private function getSLAStatus($stageResult, $durationHours)
    {
        $slaHours = $stageResult->stage->sla->target_duration_hours ?? 72;
        $warningHours = $stageResult->stage->sla->warning_threshold_hours ?? 48;

        if ($durationHours <= $warningHours) {
            return 'on_track';
        } elseif ($durationHours <= $slaHours) {
            return 'warning';
        } else {
            return 'overdue';
        }
    }

    private function calculateSLACompliance($durations, $slaHours)
    {
        $onTime = collect($durations)->filter(function($duration) use ($slaHours) {
            return $duration <= $slaHours;
        })->count();

        return round(($onTime / count($durations)) * 100, 1);
    }

    private function getCurrentStage($stageResults)
    {
        return $stageResults->where('status', 'pending')->first();
    }

    private function getCompletionPercentage($stageResults)
    {
        $totalStages = $stageResults->count();
        $completedStages = $stageResults->where('status', 'passed')->count();

        return $totalStages > 0 ? round(($completedStages / $totalStages) * 100, 1) : 0;
    }

    private function calculateBottleneckScore($stage)
    {
        $slaCompliance = $stage['sla_compliance'];
        $overdueFactor = $stage['average_hours'] / $stage['sla_hours'];

        // Higher score = bigger bottleneck
        return round((100 - $slaCompliance) * $overdueFactor, 2);
    }

    private function getBottleneckRecommendation($stage)
    {
        $score = $this->calculateBottleneckScore($stage);

        if ($score > 100) {
            return 'Critical bottleneck - immediate action required';
        } elseif ($score > 50) {
            return 'Major bottleneck - process optimization needed';
        } elseif ($score > 20) {
            return 'Minor bottleneck - consider process improvements';
        } else {
            return 'Stage performing well';
        }
    }
}
```

## 📊 Dashboard Components

### 1. Duration Dashboard Controller

```php
<?php

namespace App\Http\Controllers;

use App\Services\RecruitmentAnalyticsService;
use Illuminate\Http\Request;

class RecruitmentAnalyticsController extends Controller
{
    protected $analyticsService;

    public function __construct(RecruitmentAnalyticsService $analyticsService)
    {
        $this->middleware('auth');
        $this->analyticsService = $analyticsService;
    }

    public function dashboard(Request $request)
    {
        $filters = $request->only(['date_from', 'date_to', 'department_id']);

        $data = [
            'stage_averages' => $this->analyticsService->getAverageStageDurations($filters),
            'funnel' => $this->analyticsService->getRecruitmentFunnel($filters),
            'time_to_hire' => $this->analyticsService->getTimeToHireStats($filters),
            'bottlenecks' => $this->analyticsService->getBottleneckAnalysis($filters)
        ];

        return view('recruitment.analytics.dashboard', compact('data'));
    }

    public function candidateTimeline($candidateId)
    {
        $timeline = $this->analyticsService->getCandidateStageDurations($candidateId);
        return response()->json($timeline);
    }

    public function exportDurationReport(Request $request)
    {
        $filters = $request->only(['date_from', 'date_to', 'department_id']);

        // Implementation for Excel export
        return Excel::download(new RecruitmentDurationReport($filters), 'recruitment-duration-report.xlsx');
    }
}
```

### 2. Real-time Duration Tracking

```php
// resources/views/recruitment/analytics/duration-tracker.blade.php
<div class="card">
    <div class="card-header">
        <h3 class="card-title">Stage Duration Tracking</h3>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Stage</th>
                        <th>Target (Days)</th>
                        <th>Average (Days)</th>
                        <th>SLA Compliance</th>
                        <th>Status</th>
                        <th>Bottleneck Score</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($data['stage_averages'] as $stage)
                    <tr>
                        <td>{{ $stage['stage_name'] }}</td>
                        <td>{{ round($stage['sla_hours'] / 24, 1) }}</td>
                        <td>
                            <span class="badge badge-{{ $stage['average_days'] > ($stage['sla_hours'] / 24) ? 'danger' : 'success' }}">
                                {{ $stage['average_days'] }}
                            </span>
                        </td>
                        <td>
                            <div class="progress">
                                <div class="progress-bar bg-{{ $stage['sla_compliance'] >= 80 ? 'success' : ($stage['sla_compliance'] >= 60 ? 'warning' : 'danger') }}"
                                     style="width: {{ $stage['sla_compliance'] }}%">
                                    {{ $stage['sla_compliance'] }}%
                                </div>
                            </div>
                        </td>
                        <td>
                            @if($stage['sla_compliance'] >= 80)
                                <span class="badge badge-success">Good</span>
                            @elseif($stage['sla_compliance'] >= 60)
                                <span class="badge badge-warning">Warning</span>
                            @else
                                <span class="badge badge-danger">Critical</span>
                            @endif
                        </td>
                        <td>{{ $stage['bottleneck_score'] ?? 'N/A' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
```

### 3. Candidate Timeline View

```php
// resources/views/recruitment/candidates/timeline.blade.php
<div class="timeline">
    @foreach($timeline['stages'] as $stage)
    <div class="timeline-item {{ $stage['status'] }}">
        <div class="timeline-marker {{ $stage['sla_status'] }}">
            <i class="fas fa-{{ $stage['status'] === 'passed' ? 'check' : ($stage['status'] === 'failed' ? 'times' : 'clock') }}"></i>
        </div>
        <div class="timeline-content">
            <h4>{{ $stage['stage_name'] }}</h4>
            <p class="timeline-meta">
                <strong>Duration:</strong> {{ $stage['duration_human'] }}
                <br>
                <strong>SLA:</strong> {{ round($stage['sla_hours'] / 24, 1) }} days
                @if($stage['is_overdue'])
                    <span class="badge badge-danger">Overdue</span>
                @endif
            </p>
            @if($stage['started_at'])
                <p class="timeline-dates">
                    <strong>Started:</strong> {{ $stage['started_at']->format('d M Y H:i') }}
                    @if($stage['completed_at'])
                        <br><strong>Completed:</strong> {{ $stage['completed_at']->format('d M Y H:i') }}
                    @endif
                </p>
            @endif
        </div>
    </div>
    @endforeach
</div>

<div class="summary-card">
    <h4>Overall Summary</h4>
    <div class="row">
        <div class="col-md-4">
            <div class="stat-box">
                <h5>Total Duration</h5>
                <span class="stat-value">{{ $timeline['total_duration_human'] }}</span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-box">
                <h5>Completion</h5>
                <span class="stat-value">{{ $timeline['completion_percentage'] }}%</span>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-box">
                <h5>Current Stage</h5>
                <span class="stat-value">
                    {{ $timeline['current_stage']->stage->stage_name ?? 'Completed' }}
                </span>
            </div>
        </div>
    </div>
</div>
```

## 📈 Reporting Features

### 1. Time-to-Hire Report

```php
// Contoh output report
{
    "average_time_to_hire_hours": 504.5,
    "average_time_to_hire_days": 21.0,
    "average_time_to_hire_human": "21 days, 0.5 hours",
    "fastest_hire_days": 15.2,
    "slowest_hire_days": 35.8,
    "median_time_to_hire_days": 19.5,
    "sample_size": 25
}
```

### 2. Stage Performance Report

```php
// Contoh output untuk setiap stage
[
    {
        "stage_name": "FPTK",
        "average_days": 2.5,
        "sla_days": 3.0,
        "sla_compliance": 85.5,
        "bottleneck_score": 12.3
    },
    {
        "stage_name": "CV Review",
        "average_days": 6.8,
        "sla_days": 5.0,
        "sla_compliance": 65.2,
        "bottleneck_score": 45.7
    }
]
```

### 3. Bottleneck Analysis

```php
// Identifikasi bottleneck berdasarkan:
// - SLA compliance rendah
// - Average duration tinggi
// - Jumlah kandidat yang stuck di stage tersebut
```

## 🎯 Key Metrics Dashboard

### 1. Real-time Metrics

-   **FPTK → CV**: Average 5.2 days (Target: 5 days) ✅
-   **CV → Psikotes**: Average 8.1 days (Target: 7 days) ⚠️
-   **Psikotes → Tes Teori**: Average 4.5 days (Target: 4 days) ✅
-   **Tes Teori → Interview HR**: Average 9.2 days (Target: 7 days) ❌
-   **Interview HR → Interview User**: Average 12.5 days (Target: 10 days) ❌
-   **Interview User → Offering**: Average 4.8 days (Target: 5 days) ✅
-   **Offering → MCU**: Average 15.2 days (Target: 14 days) ⚠️
-   **MCU → Hire**: Average 2.1 days (Target: 3 days) ✅
-   **Hire → Onboarding**: Average 6.8 days (Target: 7 days) ✅

### 2. Overall Performance

-   **Total Time-to-Hire**: 68.4 days average
-   **Fastest Hire**: 45.2 days
-   **Slowest Hire**: 95.8 days
-   **SLA Compliance**: 72.5%

## 🚨 Alert System

### 1. Automated Alerts

```php
// Sistem alert otomatis untuk:
// - Stage yang mendekati SLA deadline
// - Kandidat yang stuck di satu stage terlalu lama
// - Department dengan time-to-hire tertinggi
// - Bottleneck yang perlu immediate action
```

### 2. Notification Types

-   **Email alerts** untuk HR Manager
-   **Dashboard notifications** untuk recruiters
-   **Weekly reports** untuk stakeholders
-   **Monthly analytics** untuk management

## 🎯 Implementation Benefits

### 1. Visibility

-   **Real-time tracking** setiap tahapan
-   **Bottleneck identification** yang akurat
-   **Performance benchmarking** antar department
-   **Historical trend analysis**

### 2. Optimization

-   **Process improvement** berdasarkan data
-   **Resource allocation** yang lebih efisien
-   **SLA management** yang terukur
-   **Predictive analytics** untuk planning

### 3. Accountability

-   **Clear ownership** setiap tahapan
-   **Performance measurement** individual dan team
-   **Audit trail** yang lengkap
-   **Compliance tracking** terhadap SLA

---

**Kesimpulan:** Sistem ini memberikan **visibility penuh** terhadap durasi setiap tahapan recruitment, dari permintaan FPTK hingga onboarding, dengan analytics yang mendalam untuk continuous improvement.
