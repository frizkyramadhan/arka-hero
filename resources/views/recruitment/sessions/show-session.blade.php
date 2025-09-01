@extends('layouts.main')

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="content-wrapper-custom">
        <div class="fptk-header">
            <div class="fptk-header-content">
                <div class="fptk-project">{{ $session->fptk->project->project_name }}</div>
                <h1 class="fptk-number">{{ $session->candidate->fullname ?? 'N/A' }}</h1>
                <div class="fptk-date">
                    <i class="far fa-calendar-alt"></i> {{ date('d F Y', strtotime($session->created_at)) }}
                </div>
                @php
                    $statusMap = [
                        'in_process' => [
                            'label' => 'In Process',
                            'class' => 'badge badge-primary',
                            'icon' => 'fa-clock',
                        ],
                        'hired' => [
                            'label' => 'Hired',
                            'class' => 'badge badge-success',
                            'icon' => 'fa-check-circle',
                        ],
                        'rejected' => [
                            'label' => 'Rejected',
                            'class' => 'badge badge-danger',
                            'icon' => 'fa-times-circle',
                        ],
                        'withdrawn' => [
                            'label' => 'Withdrawn',
                            'class' => 'badge badge-secondary',
                            'icon' => 'fa-user-minus',
                        ],
                        'cancelled' => [
                            'label' => 'Cancelled',
                            'class' => 'badge badge-warning',
                            'icon' => 'fa-ban',
                        ],
                    ];
                    $status = $session->status;
                    $pill = $statusMap[$status] ?? [
                        'label' => ucfirst($status),
                        'class' => 'badge badge-secondary',
                        'icon' => 'fa-question-circle',
                    ];
                @endphp
                <div class="fptk-status-pill">
                    <span class="{{ $pill['class'] }}">
                        <i class="fas {{ $pill['icon'] }}"></i> {{ $pill['label'] }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="fptk-content">
            <div class="row">
                <!-- Left Column -->
                <div class="col-lg-8">
                    <!-- Timeline -->
                    <div class="fptk-card timeline-card">
                        <div class="card-head">
                            <h2><i class="fas fa-clock"></i> Recruitment Timeline</h2>
                        </div>
                        <div class="card-body">
                            @php
                                $stageOrder = [
                                    'cv_review' => 1,
                                    'psikotes' => 2,
                                    'tes_teori' => 3,
                                    'interview' => 4,
                                    'offering' => 5,
                                    'mcu' => 6,
                                    'hire' => 7,
                                ];

                                // Adjust stage order if tes_teori should be skipped
                                if ($session->shouldSkipTheoryTest()) {
                                    $stageOrder['interview'] = 3;
                                    $stageOrder['offering'] = 4;
                                    $stageOrder['mcu'] = 5;
                                    $stageOrder['hire'] = 6;
                                }
                                $currentOrder = $stageOrder[$session->current_stage] ?? 0;
                                $stageClasses = [];
                                $stageEditability = [];
                                $hasFailedStage = false;
                                $failedStageOrder = null;

                                // First pass: determine if any stage failed and which stage
                                foreach (array_keys($stageOrder) as $stageKey) {
                                    $thisOrder = $stageOrder[$stageKey] ?? 0;
                                    if ($thisOrder <= $currentOrder) {
                                        $assessment = $session->getAssessmentByStage($stageKey);
                                        $failed = false;

                                        if ($stageKey === 'interview') {
                                            $interviewStatus = $session->getInterviewStatus();
                                            $failed = $interviewStatus === 'danger';
                                        } else {
                                            if ($assessment) {
                                                $decision = $assessment->decision ?? null;
                                                $result = $assessment->result ?? null;
                                                if ($stageKey === 'cv_review') {
                                                    $failed = $decision === 'not_recommended';
                                                } elseif (in_array($stageKey, ['psikotes', 'tes_teori'])) {
                                                    $failed = $result === 'fail';
                                                } elseif ($stageKey === 'offering') {
                                                    $failed = $result === 'rejected';
                                                } elseif ($stageKey === 'mcu') {
                                                    $failed = $result === 'unfit';
                                                }
                                            }
                                        }

                                        if ($failed && !$hasFailedStage) {
                                            $hasFailedStage = true;
                                            $failedStageOrder = $thisOrder;
                                            break;
                                        }
                                    }
                                }

                                // Second pass: set classes and editability
                                foreach (array_keys($stageOrder) as $stageKey) {
                                    $thisOrder = $stageOrder[$stageKey] ?? 0;
                                    $cls = 'bg-secondary';
                                    if ($thisOrder > $currentOrder) {
                                        $cls = 'bg-secondary';
                                    } elseif ($thisOrder < $currentOrder) {
                                        $assessment = $session->getAssessmentByStage($stageKey);
                                        $passed = $session->isStageCompleted($stageKey);
                                        $failed = false;

                                        if ($stageKey === 'interview') {
                                            // Use helper method for interview status
                                            $interviewStatus = $session->getInterviewStatus();
                                            $cls = 'bg-' . $interviewStatus;
                                        } else {
                                            // Logic for other stages
                                            if ($assessment) {
                                                $decision = $assessment->decision ?? null;
                                                $result = $assessment->result ?? null;
                                                if ($stageKey === 'cv_review') {
                                                    $failed = $decision === 'not_recommended';
                                                } elseif (in_array($stageKey, ['psikotes', 'tes_teori'])) {
                                                    $failed = $result === 'fail';
                                                } elseif ($stageKey === 'offering') {
                                                    $failed = $result === 'rejected';
                                                } elseif ($stageKey === 'mcu') {
                                                    $failed = $result === 'unfit';
                                                }
                                            }
                                            if ($passed) {
                                                $cls = 'bg-success';
                                            } elseif ($failed) {
                                                $cls = 'bg-danger';
                                            } else {
                                                $cls = 'bg-secondary';
                                            }
                                        }
                                    } else {
                                        // current stage
                                        if ($session->stage_status === 'failed') {
                                            $cls = 'bg-danger';
                                        } elseif (in_array($session->stage_status, ['pending', 'in_progress'])) {
                                            if ($stageKey === 'interview') {
                                                // Use helper method for interview status
                                                $interviewStatus = $session->getInterviewStatus();
                                                $cls = 'bg-' . $interviewStatus;
                                            } else {
                                                $cls = 'bg-warning';
                                            }
                                        } else {
                                            // completed
                                            if ($stageKey === 'interview') {
                                                // Use helper method for interview status
                                                $interviewStatus = $session->getInterviewStatus();
                                                $cls = 'bg-' . $interviewStatus;
                                            } else {
                                                // Logic for other completed stages
                                                $assessment = $session->getCurrentStageAssessment();
                                                $decision = $assessment->decision ?? null;
                                                $result = $assessment->result ?? null;
                                                $cls = 'bg-success';
                                                if ($stageKey === 'cv_review' && $decision === 'not_recommended') {
                                                    $cls = 'bg-danger';
                                                }
                                                if (
                                                    in_array($stageKey, ['psikotes', 'tes_teori']) &&
                                                    $result === 'fail'
                                                ) {
                                                    $cls = 'bg-danger';
                                                }
                                                if ($stageKey === 'offering' && $result === 'rejected') {
                                                    $cls = 'bg-danger';
                                                }
                                                if ($stageKey === 'mcu' && $result === 'unfit') {
                                                    $cls = 'bg-danger';
                                                }
                                            }
                                        }
                                    }
                                    $stageClasses[$stageKey] = $cls;

                                    // Determine if this stage is editable
                                    $editable = true;
                                    if ($hasFailedStage && $thisOrder >= $failedStageOrder) {
                                        // If there's a failed stage, disable editing for the failed stage and all subsequent stages
                                        $editable = false;
                                    }
                                    $stageEditability[$stageKey] = $editable;
                                }
                            @endphp
                            <div class="timeline-horizontal">
                                <!-- CV Review -->
                                <div class="timeline-item {{ $stageEditability['cv_review'] ? 'editable' : 'disabled' }}"
                                    @if ($stageEditability['cv_review']) data-toggle="modal" data-target="#cvReviewModal" @endif>
                                    <div class="timeline-marker {{ $stageClasses['cv_review'] }}">
                                        <i class="fas fa-file-alt"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="timeline-title">
                                            CV Review
                                            @if (!$stageEditability['cv_review'])
                                                <i class="fas fa-lock ml-1"
                                                    title="Locked due to previous stage failure"></i>
                                            @endif
                                        </div>
                                        <div class="timeline-date">
                                            @if ($session->current_stage === 'cv_review' && $session->stage_started_at)
                                                {{ date('d M Y', strtotime($session->stage_started_at)) }}
                                            @elseif($session->cvReview && $session->cvReview->reviewed_at)
                                                {{ date('d M Y', strtotime($session->cvReview->reviewed_at)) }}
                                            @else
                                                -
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Psikotes -->
                                <div class="timeline-item {{ $stageEditability['psikotes'] ? 'editable' : 'disabled' }}"
                                    @if ($stageEditability['psikotes']) data-toggle="modal" data-target="#psikotesModal" @endif>
                                    <div class="timeline-marker {{ $stageClasses['psikotes'] }}">
                                        <i class="fas fa-brain"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="timeline-title">
                                            Psikotes
                                            @if (!$stageEditability['psikotes'])
                                                <i class="fas fa-lock ml-1"
                                                    title="Locked due to previous stage failure"></i>
                                            @endif
                                        </div>
                                        <div class="timeline-date">
                                            @if ($session->current_stage === 'psikotes' && $session->stage_started_at)
                                                {{ date('d M Y', strtotime($session->stage_started_at)) }}
                                            @elseif($session->psikotes && $session->psikotes->reviewed_at)
                                                {{ date('d M Y', strtotime($session->psikotes->reviewed_at)) }}
                                            @else
                                                -
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Tes Teori -->
                                <!-- Tes Teori - Only show for mechanic positions -->
                                @if (!$session->shouldSkipTheoryTest())
                                    <div class="timeline-item {{ $stageEditability['tes_teori'] ? 'editable' : 'disabled' }}"
                                        @if ($stageEditability['tes_teori']) data-toggle="modal" data-target="#tesTeoriModal" @endif>
                                        <div class="timeline-marker {{ $stageClasses['tes_teori'] }}">
                                            <i class="fas fa-book"></i>
                                        </div>
                                        <div class="timeline-content">
                                            <div class="timeline-title">
                                                Tes Teori
                                                @if (!$stageEditability['tes_teori'])
                                                    <i class="fas fa-lock ml-1"
                                                        title="Locked due to previous stage failure"></i>
                                                @endif
                                            </div>
                                            <div class="timeline-date">
                                                @if ($session->current_stage === 'tes_teori' && $session->stage_started_at)
                                                    {{ date('d M Y', strtotime($session->stage_started_at)) }}
                                                @elseif($session->tesTeori && $session->tesTeori->reviewed_at)
                                                    {{ date('d M Y', strtotime($session->tesTeori->reviewed_at)) }}
                                                @else
                                                    -
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                <!-- Interview -->
                                <div class="timeline-item {{ $stageEditability['interview'] ? 'editable' : 'disabled' }}"
                                    @if ($stageEditability['interview']) data-toggle="modal" data-target="#interviewModal" @endif>
                                    <div class="timeline-marker {{ $stageClasses['interview'] }}">
                                        <i class="fas fa-user-tie"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="timeline-title">
                                            Interview
                                            @if (!$stageEditability['interview'])
                                                <i class="fas fa-lock ml-1"
                                                    title="Locked due to previous stage failure"></i>
                                            @elseif($session->areAllInterviewsCompleted())
                                                <i class="fas fa-check-circle ml-1 text-success"
                                                    title="All interviews completed"></i>
                                            @elseif($session->interviews()->exists())
                                                <i class="fas fa-clock ml-1 text-warning"
                                                    title="Some interviews pending"></i>
                                            @endif
                                        </div>
                                        <div class="timeline-date">
                                            @if ($session->current_stage === 'interview' && $session->stage_started_at)
                                                {{ date('d M Y', strtotime($session->stage_started_at)) }}
                                            @elseif($session->interviews()->exists())
                                                @php
                                                    $latestInterview = $session
                                                        ->interviews()
                                                        ->latest('reviewed_at')
                                                        ->first();
                                                @endphp
                                                @if ($latestInterview)
                                                    {{ date('d M Y', strtotime($latestInterview->reviewed_at)) }}
                                                @else
                                                    -
                                                @endif
                                            @else
                                                -
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Offering -->
                                <div class="timeline-item {{ $stageEditability['offering'] ? 'editable' : 'disabled' }}"
                                    @if ($stageEditability['offering']) data-toggle="modal" data-target="#offeringModal" @endif>
                                    <div class="timeline-marker {{ $stageClasses['offering'] }}">
                                        <i class="fas fa-handshake"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="timeline-title">
                                            Offering
                                            @if (!$stageEditability['offering'])
                                                <i class="fas fa-lock ml-1"
                                                    title="Locked due to previous stage failure"></i>
                                            @endif
                                        </div>
                                        <div class="timeline-date">
                                            @if ($session->current_stage === 'offering' && $session->stage_started_at)
                                                {{ date('d M Y', strtotime($session->stage_started_at)) }}
                                            @elseif($session->offering && $session->offering->reviewed_at)
                                                {{ date('d M Y', strtotime($session->offering->reviewed_at)) }}
                                            @else
                                                -
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- MCU -->
                                <div class="timeline-item {{ $stageEditability['mcu'] ? 'editable' : 'disabled' }}"
                                    @if ($stageEditability['mcu']) data-toggle="modal" data-target="#mcuModal" @endif>
                                    <div class="timeline-marker {{ $stageClasses['mcu'] }}">
                                        <i class="fas fa-user-md"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="timeline-title">
                                            MCU
                                            @if (!$stageEditability['mcu'])
                                                <i class="fas fa-lock ml-1"
                                                    title="Locked due to previous stage failure"></i>
                                            @endif
                                        </div>
                                        <div class="timeline-date">
                                            @if ($session->current_stage === 'mcu' && $session->stage_started_at)
                                                {{ date('d M Y', strtotime($session->stage_started_at)) }}
                                            @elseif($session->mcu && $session->mcu->reviewed_at)
                                                {{ date('d M Y', strtotime($session->mcu->reviewed_at)) }}
                                            @else
                                                -
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Hire -->
                                <div class="timeline-item {{ $stageEditability['hire'] ? 'editable' : 'disabled' }}"
                                    @if ($stageEditability['hire']) data-toggle="modal" data-target="#hireModal" @endif>
                                    <div class="timeline-marker {{ $stageClasses['hire'] }}">
                                        <i class="fas fa-user-check"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="timeline-title">
                                            Hiring & Onboarding
                                            @if (!$stageEditability['hire'])
                                                <i class="fas fa-lock ml-1"
                                                    title="Locked due to previous stage failure"></i>
                                            @endif
                                        </div>
                                        <div class="timeline-date">
                                            @if ($session->current_stage === 'hire' && $session->stage_started_at)
                                                {{ date('d M Y', strtotime($session->stage_started_at)) }}
                                            @elseif($session->hiring && $session->hiring->reviewed_at)
                                                {{ date('d M Y', strtotime($session->hiring->reviewed_at)) }}
                                            @else
                                                -
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Session Information -->
                    <div class="fptk-card session-info-card">
                        <div class="card-head">
                            <h2><i class="fas fa-info-circle"></i> Session Information</h2>
                        </div>
                        <div class="card-body">
                            <div class="info-grid">
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #3498db;">
                                        <i class="fas fa-hashtag"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Session Number</div>
                                        <div class="info-value">{{ $session->session_number }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #e74c3c;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Candidate</div>
                                        <div class="info-value">{{ $session->candidate->fullname ?? 'N/A' }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #f1c40f;">
                                        <i class="fas fa-user-tie"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Position</div>
                                        <div class="info-value">{{ $session->fptk->position->position_name }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #9b59b6;">
                                        <i class="fas fa-building"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Department</div>
                                        <div class="info-value">{{ $session->fptk->department->department_name }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #1abc9c;">
                                        <i class="fas fa-play-circle"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Current Stage</div>
                                        <div class="info-value">
                                            {{ ucfirst(str_replace('_', ' ', $session->current_stage)) }}
                                            @if ($session->current_stage === 'interview')
                                                <br>
                                                @php
                                                    $interviewSummary = $session->getInterviewSummary();
                                                    $completedInterviews = collect($interviewSummary)
                                                        ->where('completed', true)
                                                        ->count();
                                                    $totalRequired = $session->shouldSkipTheoryTest() ? 2 : 3;
                                                @endphp
                                                <span
                                                    class="badge badge-{{ $completedInterviews === $totalRequired ? 'success' : 'warning' }}">
                                                    {{ $completedInterviews }}/{{ $totalRequired }} Completed
                                                </span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #e67e22;">
                                        <i class="fas fa-percentage"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Progress</div>
                                        <div class="info-value">{{ $progressPercentage }}%</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Assessments moved below right column as full-width -->
                </div>

                <!-- Right Column -->
                <div class="col-lg-4">
                    <!-- Quick Actions -->
                    <div class="fptk-card quick-actions-card">
                        <div class="card-head">
                            <h2><i class="fas fa-bolt"></i> Quick Actions</h2>
                        </div>
                        <div class="card-body">
                            <div class="fptk-action-buttons">
                                <a href="{{ route('recruitment.sessions.show', $session->fptk->id) }}"
                                    class="btn-action back-btn">
                                    <i class="fas fa-arrow-left"></i> Back to FPTK
                                </a>
                            </div>
                        </div>
                    </div>

                    <!-- Progress Summary -->
                    <div class="fptk-card progress-summary-card">
                        <div class="card-head">
                            <h2><i class="fas fa-chart-pie"></i> Progress Summary</h2>
                        </div>
                        <div class="card-body">
                            <div class="progress-summary">
                                <div class="progress-circle">
                                    <div class="progress-circle-inner">
                                        <div class="progress-circle-value">{{ $progressPercentage }}%</div>
                                        <div class="progress-circle-label">Complete</div>
                                    </div>
                                </div>
                                <div class="progress-details">
                                    <div class="progress-detail-item">
                                        <div class="detail-label">Current Stage</div>
                                        <div class="detail-value">
                                            @switch($session->current_stage)
                                                @case('cv_review')
                                                    <span class="badge badge-primary">CV Review</span>
                                                @break

                                                @case('psikotes')
                                                    <span class="badge badge-info">Psikotes</span>
                                                @break

                                                @case('tes_teori')
                                                    <span class="badge badge-warning">Tes Teori</span>
                                                @break

                                                @case('interview')
                                                    <span class="badge badge-success">Interview</span>
                                                @break

                                                @case('offering')
                                                    <span class="badge badge-primary">Offering</span>
                                                @break

                                                @case('mcu')
                                                    <span class="badge badge-info">MCU</span>
                                                @break

                                                @case('hire')
                                                    <span class="badge badge-success">Hiring & Onboarding</span>
                                                @break

                                                @default
                                                    <span
                                                        class="badge badge-secondary">{{ ucfirst(str_replace('_', ' ', $session->current_stage)) }}</span>
                                            @endswitch
                                        </div>
                                    </div>
                                    @if ($session->current_stage === 'interview')
                                        <div class="progress-detail-item">
                                            <div class="detail-label">Interview Progress</div>
                                            <div class="detail-value">
                                                @php
                                                    $interviewSummary = $session->getInterviewSummary();
                                                    $hrStatus = $interviewSummary['hr']['completed']
                                                        ? ($interviewSummary['hr']['result'] === 'recommended'
                                                            ? 'success'
                                                            : 'danger')
                                                        : 'secondary';
                                                    $userStatus = $interviewSummary['user']['completed']
                                                        ? ($interviewSummary['user']['result'] === 'recommended'
                                                            ? 'success'
                                                            : 'danger')
                                                        : 'secondary';
                                                    $trainerStatus = null;
                                                    if (!$session->shouldSkipTheoryTest()) {
                                                        $trainerStatus = $interviewSummary['trainer']['completed']
                                                            ? ($interviewSummary['trainer']['result'] === 'recommended'
                                                                ? 'success'
                                                                : 'danger')
                                                            : 'secondary';
                                                    }
                                                @endphp
                                                <div class="d-flex flex-column gap-1">
                                                    <span class="badge badge-{{ $hrStatus }}">HR</span>
                                                    @if (!$session->shouldSkipTheoryTest())
                                                        <span class="badge badge-{{ $trainerStatus }}">Trainer</span>
                                                    @endif
                                                    <span class="badge badge-{{ $userStatus }}">User</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    <div class="progress-detail-item">
                                        <div class="detail-label">Total Days</div>
                                        <div class="detail-value">
                                            @php
                                                $startDate =
                                                    optional($session->cvReview)->reviewed_at ?? $session->created_at;
                                                $endDate =
                                                    $session->status === 'hired' && $session->final_decision_date
                                                        ? $session->final_decision_date
                                                        : now();
                                                $totalDays = \Carbon\Carbon::parse($startDate)
                                                    ->startOfDay()
                                                    ->diffInDays(\Carbon\Carbon::parse($endDate)->endOfDay());
                                            @endphp
                                            <span class="badge badge-dark">{{ $totalDays }} days</span>
                                        </div>
                                    </div>
                                    <div class="progress-detail-item">
                                        <div class="detail-label">Stage Status</div>
                                        <div class="detail-value">
                                            @if ($session->stage_status === 'pending')
                                                <span class="badge badge-warning">Pending</span>
                                            @elseif ($session->stage_status === 'in_progress')
                                                <span class="badge badge-info">In Progress</span>
                                            @elseif ($session->stage_status === 'completed')
                                                <span class="badge badge-success">Completed</span>
                                            @elseif ($session->stage_status === 'failed')
                                                <span class="badge badge-danger">Failed</span>
                                            @else
                                                <span
                                                    class="badge badge-secondary">{{ ucfirst($session->stage_status) }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="progress-detail-item">
                                        <div class="detail-label">Final Status</div>
                                        <div class="detail-value">
                                            @if ($session->status === 'active')
                                                <span class="badge badge-success">Active</span>
                                            @elseif ($session->status === 'completed')
                                                <span class="badge badge-info">Completed</span>
                                            @elseif ($session->status === 'rejected')
                                                <span class="badge badge-danger">Rejected</span>
                                            @elseif ($session->status === 'failed')
                                                <span class="badge badge-danger">Failed</span>
                                            @else
                                                <span class="badge badge-secondary">{{ ucfirst($session->status) }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Full-width Assessments Section -->
            @php
                $allAssessments = $session->getAllAssessments();

                // Filter assessments based on current stage
                $currentStage = $session->current_stage;
                $stageOrder = [
                    'cv_review' => 1,
                    'psikotes' => 2,
                    'tes_teori' => 3,
                    'interview' => 4,
                    'offering' => 5,
                    'mcu' => 6,
                    'hire' => 7,
                ];

                // Adjust stage order if tes_teori should be skipped
                if ($session->shouldSkipTheoryTest()) {
                    $stageOrder['interview'] = 3;
                    $stageOrder['offering'] = 4;
                    $stageOrder['mcu'] = 5;
                    $stageOrder['hire'] = 6;
                }

                $currentOrder = $stageOrder[$currentStage] ?? 0;

                // Filter assessments to only show stages up to current stage
                $filteredAssessments = [];
                foreach ($allAssessments as $stage => $assessment) {
                    if ($assessment && isset($stageOrder[$stage])) {
                        $stageOrderNum = $stageOrder[$stage];
                        if ($stageOrderNum <= $currentOrder) {
                            $filteredAssessments[$stage] = $assessment;
                        }
                    }
                }

                $hasAssessments = collect($filteredAssessments)->filter()->isNotEmpty();
            @endphp
            @if ($hasAssessments)
                <div class="row">
                    <div class="col-12 assessments-section">
                        <div class="fptk-card assessments-card">
                            <div class="card-head">
                                <h2><i class="fas fa-clipboard-check"></i> Assessments</h2>
                            </div>
                            <div class="card-body">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th>Stage</th>
                                                <th>Date</th>
                                                <th>Status</th>
                                                <th>Score/Result</th>
                                                <th>Notes</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($filteredAssessments as $stage => $assessment)
                                                @if ($assessment)
                                                    <tr>
                                                        <td>
                                                            @switch($stage)
                                                                @case('cv_review')
                                                                    <strong>CV Review</strong>
                                                                    @if ($stage === $session->current_stage)
                                                                        <span class="badge badge-primary ml-2">Current</span>
                                                                    @endif
                                                                @break

                                                                @case('psikotes')
                                                                    <strong>Psikotes</strong>
                                                                    @if ($stage === $session->current_stage)
                                                                        <span class="badge badge-primary ml-2">Current</span>
                                                                    @endif
                                                                @break

                                                                @case('tes_teori')
                                                                    <strong>Tes Teori</strong>
                                                                    @if ($stage === $session->current_stage)
                                                                        <span class="badge badge-primary ml-2">Current</span>
                                                                    @endif
                                                                @break

                                                                @case('interview')
                                                                    <strong>Interview</strong>
                                                                    @if ($stage === $session->current_stage)
                                                                        <span class="badge badge-primary ml-2">Current</span>
                                                                    @endif
                                                                @break

                                                                @case('offering')
                                                                    <strong>Offering</strong>
                                                                    @if ($stage === $session->current_stage)
                                                                        <span class="badge badge-primary ml-2">Current</span>
                                                                    @endif
                                                                @break

                                                                @case('mcu')
                                                                    <strong>MCU</strong>
                                                                    @if ($stage === $session->current_stage)
                                                                        <span class="badge badge-primary ml-2">Current</span>
                                                                    @endif
                                                                @break

                                                                @case('hire')
                                                                    <strong>Hiring & Onboarding</strong>
                                                                    @if ($stage === $session->current_stage)
                                                                        <span class="badge badge-primary ml-2">Current</span>
                                                                    @endif
                                                                @break

                                                                @default
                                                                    <strong>{{ ucfirst(str_replace('_', ' ', $stage)) }}</strong>
                                                                    @if ($stage === $session->current_stage)
                                                                        <span class="badge badge-primary ml-2">Current</span>
                                                                    @endif
                                                            @endswitch
                                                        </td>
                                                        <td>
                                                            @if ($stage === 'interview' && is_array($assessment))
                                                                @php
                                                                    $interviewSummary = $assessment;
                                                                    $hasCompletedInterviews =
                                                                        collect($interviewSummary)
                                                                            ->where('completed', true)
                                                                            ->count() > 0;
                                                                @endphp
                                                                @if ($hasCompletedInterviews)
                                                                    @foreach ($interviewSummary as $type => $interview)
                                                                        @if ($interview['completed'])
                                                                            <div class="mb-1">
                                                                                <small
                                                                                    class="text-muted">{{ ucfirst($type) }}:</small>
                                                                                {{ date('d M Y', strtotime($interview['reviewed_at'])) }}
                                                                            </div>
                                                                        @endif
                                                                    @endforeach
                                                                @else
                                                                    N/A
                                                                @endif
                                                            @else
                                                                {{ $assessment->reviewed_at ? date('d M Y', strtotime($assessment->reviewed_at)) : 'N/A' }}
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @if ($stage === 'interview' && is_array($assessment))
                                                                @php
                                                                    $interviewSummary = $assessment;
                                                                    $completedInterviews = collect(
                                                                        $interviewSummary,
                                                                    )->where('completed', true);
                                                                    $totalRequired = $session->shouldSkipTheoryTest()
                                                                        ? 2
                                                                        : 3;
                                                                    $allRecommended =
                                                                        $completedInterviews
                                                                            ->where('result', 'recommended')
                                                                            ->count() === $completedInterviews->count();
                                                                    $hasRejected =
                                                                        $completedInterviews
                                                                            ->where('result', 'not_recommended')
                                                                            ->count() > 0;
                                                                @endphp
                                                                @if ($completedInterviews->count() > 0)
                                                                    @if ($allRecommended && $completedInterviews->count() === $totalRequired)
                                                                        <span class="badge badge-success">All
                                                                            Recommended</span>
                                                                    @elseif($hasRejected)
                                                                        <span class="badge badge-danger">Some Not
                                                                            Recommended</span>
                                                                    @else
                                                                        <span class="badge badge-warning">Partial
                                                                            ({{ $completedInterviews->count() }}/{{ $totalRequired }})
                                                                        </span>
                                                                    @endif
                                                                @else
                                                                    <span class="badge badge-secondary">Not Started</span>
                                                                @endif
                                                            @else
                                                                @php
                                                                    $statusMap = [
                                                                        'recommended' =>
                                                                            '<span class="badge badge-success">Recommended</span>',
                                                                        'not_recommended' =>
                                                                            '<span class="badge badge-danger">Not Recommended</span>',
                                                                        'pass' =>
                                                                            '<span class="badge badge-success">Pass</span>',
                                                                        'fail' =>
                                                                            '<span class="badge badge-danger">Fail</span>',
                                                                        'accepted' =>
                                                                            '<span class="badge badge-success">Accepted</span>',
                                                                        'rejected' =>
                                                                            '<span class="badge badge-danger">Rejected</span>',
                                                                        'fit' =>
                                                                            '<span class="badge badge-success">Fit</span>',
                                                                        'unfit' =>
                                                                            '<span class="badge badge-danger">Unfit</span>',
                                                                        'follow_up' =>
                                                                            '<span class="badge badge-warning">Follow Up</span>',
                                                                    ];
                                                                    $result =
                                                                        $assessment->decision ??
                                                                        ($assessment->result ?? null);
                                                                    if ($stage === 'hire' && $assessment) {
                                                                        $statusMap['hired'] =
                                                                            '<span class="badge badge-success">Hired</span>';
                                                                        $result = 'hired';
                                                                    }
                                                                @endphp
                                                                {!! $statusMap[$result] ?? '<span class="badge badge-info">Completed</span>' !!}
                                                            @endif
                                                        </td>
                                                        <td>
                                                            @switch($stage)
                                                                @case('cv_review')
                                                                    {{ $assessment->decision ?? 'N/A' }}
                                                                @break

                                                                @case('psikotes')
                                                                    @if ($assessment->online_score && $assessment->offline_score)
                                                                        Online: {{ $assessment->online_score }}, Offline:
                                                                        {{ $assessment->offline_score }}
                                                                    @elseif ($assessment->online_score)
                                                                        Online: {{ $assessment->online_score }}
                                                                    @elseif ($assessment->offline_score)
                                                                        Offline: {{ $assessment->offline_score }}
                                                                    @else
                                                                        N/A
                                                                    @endif
                                                                @break

                                                                @case('tes_teori')
                                                                    {{ $assessment->score ?? 'N/A' }}
                                                                @break

                                                                @case('interview')
                                                                    @if (is_array($assessment))
                                                                        @php
                                                                            $interviewSummary = $assessment;
                                                                            $completedInterviews = collect(
                                                                                $interviewSummary,
                                                                            )->where('completed', true);
                                                                            $totalRequired = $session->shouldSkipTheoryTest()
                                                                                ? 2
                                                                                : 3;
                                                                        @endphp
                                                                        @if ($completedInterviews->count() > 0)
                                                                            @foreach ($completedInterviews as $type => $interview)
                                                                                <div class="mb-1">
                                                                                    <small
                                                                                        class="text-muted">{{ ucfirst($type) }}:</small>
                                                                                    <span
                                                                                        class="badge badge-{{ $interview['result'] === 'recommended' ? 'success' : 'danger' }}">
                                                                                        {{ ucfirst($interview['result']) }}
                                                                                    </span>
                                                                                </div>
                                                                            @endforeach
                                                                        @else
                                                                            N/A
                                                                        @endif
                                                                    @else
                                                                        {{ $assessment->result ?? 'N/A' }}
                                                                    @endif
                                                                @break

                                                                @case('offering')
                                                                    {{ $assessment->result ?? 'N/A' }}
                                                                @break

                                                                @case('mcu')
                                                                    {{ $assessment->result ?? 'N/A' }}
                                                                @break

                                                                @case('hire')
                                                                    {{ strtoupper($assessment->agreement_type) ?? 'N/A' }}
                                                                @break

                                                                @default
                                                                    N/A
                                                            @endswitch
                                                        </td>
                                                        <td>
                                                            @if ($stage === 'interview' && is_array($assessment))
                                                                @php
                                                                    $completedInterviews = collect($assessment)->where(
                                                                        'completed',
                                                                        true,
                                                                    );
                                                                @endphp
                                                                @if ($completedInterviews->count() > 0)
                                                                    @foreach ($completedInterviews as $type => $interview)
                                                                        <div class="mb-1">
                                                                            <strong>{{ ucfirst($type) }}:</strong>
                                                                            {{ Str::limit($interview['notes'] ?? 'N/A', 30) }}
                                                                        </div>
                                                                    @endforeach
                                                                @else
                                                                    N/A
                                                                @endif
                                                            @else
                                                                {{ Str::limit($assessment->notes, 50) ?? 'N/A' }}
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
    @include('recruitment.sessions.partials.modals')
@endsection

@section('styles')
    <!-- Select2 styles for letter number selector -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <style>
        /* Core Layout Styles */
        .content-wrapper-custom {
            background-color: #f8fafc;
            min-height: 100vh;
            padding-bottom: 40px;
        }

        /* Header Styles */
        .fptk-header {
            position: relative;
            height: 120px;
            color: white;
            padding: 20px 30px;
            margin-bottom: 30px;
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .fptk-header-content {
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .fptk-project {
            font-size: 13px;
            margin-bottom: 4px;
            opacity: 0.9;
            letter-spacing: 1px;
        }

        .fptk-number {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .fptk-date {
            font-size: 14px;
            opacity: 0.9;
        }

        .fptk-status-pill {
            position: absolute;
            top: 20px;
            right: 20px;
        }

        .fptk-status-pill .badge {
            font-size: 0.875rem;
            padding: 0.5rem 0.75rem;
            border-radius: 0.375rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Content & Card Styles */
        .fptk-content {
            padding: 0 20px;
        }

        .fptk-card {
            background: white;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .card-head {
            padding: 15px 20px;
            border-bottom: 1px solid #e9ecef;
            background-color: #f8f9fa;
        }

        .card-head h2 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card-body {
            padding: 20px;
        }

        /* Timeline Styles */
        .timeline-horizontal {
            display: flex;
            align-items: center;
            position: relative;
            padding: 20px 0;
            overflow-x: auto;
        }

        .timeline-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            position: relative;
            z-index: 2;
            margin: 0 20px;
            cursor: pointer;
            transition: transform 0.2s ease;
            min-width: 70px;
            max-width: 70px;
        }

        /* Timeline Item States */
        .timeline-item.editable {
            cursor: pointer;
        }

        .timeline-item.disabled {
            cursor: not-allowed;
            opacity: 0.6;
        }

        .timeline-item.disabled:hover .timeline-marker {
            transform: none !important;
            box-shadow: none !important;
        }

        .timeline-item.disabled .timeline-title {
            color: #6c757d !important;
        }

        .timeline-item.disabled .timeline-date {
            color: #adb5bd !important;
        }

        .timeline-item:hover {
            transform: translateY(-2px);
        }

        .timeline-marker {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 18px;
            margin-bottom: 10px;
            border: 3px solid white;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            background: #6c757d;
        }

        .timeline-marker.bg-success {
            background: #28a745 !important;
        }

        .timeline-marker.bg-warning {
            background: #ffc107 !important;
        }

        .timeline-marker.bg-secondary {
            background: #6c757d !important;
        }

        .timeline-marker.bg-danger {
            background: #dc3545 !important;
        }

        .timeline-content {
            text-align: center;
            width: 100%;
            min-height: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .timeline-title {
            font-size: 12px;
            font-weight: 600;
            color: #495057;
            margin-bottom: 5px;
            line-height: 1.2;
            word-wrap: break-word;
        }

        .timeline-date {
            font-size: 10px;
            color: #6c757d;
            line-height: 1.2;
        }

        .timeline-status {
            margin-top: 6px;
        }

        .timeline-status .badge-sm {
            font-size: 10px;
            padding: 2px 6px;
        }

        /* Table Styles */
        .table th {
            background-color: #f8f9fa;
            border-color: #dee2e6;
            font-weight: 600;
            color: #495057;
            vertical-align: middle;
        }

        .table td {
            vertical-align: middle;
            border-color: #dee2e6;
        }

        .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .badge-sm {
            font-size: 0.75em;
            padding: 0.25em 0.5em;
        }

        /* Action Buttons */
        .fptk-action-buttons {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
        }

        .btn-action {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 12px 16px;
            border-radius: 6px;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s;
            gap: 8px;
            color: white;
            text-decoration: none;
            border: none;
            cursor: pointer;
            width: 100%;
            min-height: 44px;
        }

        .advance-btn {
            background-color: #28a745;
        }

        .advance-btn:hover {
            background-color: #218838;
            color: white;
        }

        .cv-review-btn {
            background-color: #17a2b8;
        }

        .cv-review-btn:hover {
            background-color: #138496;
            color: white;
        }

        .psikotes-btn {
            background-color: #6f42c1;
        }

        .psikotes-btn:hover {
            background-color: #5a32a3;
            color: white;
        }

        .complete-btn {
            background-color: #ffc107;
            color: #212529;
        }

        .complete-btn:hover {
            background-color: #e0a800;
            color: #212529;
        }

        .back-btn {
            background-color: #6c757d;
        }

        .back-btn:hover {
            background-color: #5a6268;
            color: white;
        }

        .btn-action:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        /* Progress Summary */
        .progress-summary {
            text-align: center;
        }

        .progress-circle {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: conic-gradient(#28a745 0deg, #28a745 {{ $progressPercentage * 3.6 }}deg, #e9ecef {{ $progressPercentage * 3.6 }}deg, #e9ecef 360deg);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            position: relative;
        }

        .progress-circle::before {
            content: '';
            width: 80px;
            height: 80px;
            background: white;
            border-radius: 50%;
            position: absolute;
        }

        .progress-circle-inner {
            position: relative;
            z-index: 1;
        }

        .progress-circle-value {
            font-size: 24px;
            font-weight: 700;
            color: #495057;
            line-height: 1;
        }

        .progress-circle-label {
            font-size: 10px;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .progress-details {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .progress-detail-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .progress-detail-item:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 500;
            color: #6c757d;
            font-size: 14px;
        }

        /* Interview Progress Badges */
        .progress-detail-item .d-flex.flex-column.gap-1 {
            gap: 0.25rem !important;
        }

        .progress-detail-item .badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }

        /* Current Stage Badge */
        .badge.ml-2 {
            margin-left: 0.5rem !important;
        }

        .badge.badge-primary {
            background-color: #007bff;
            color: white;
        }

        .detail-value {
            font-weight: 600;
            color: #495057;
            font-size: 14px;
        }

        /* Info Grid Styles (match FPTK Information) */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            padding: 20px;
        }

        .info-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .info-icon {
            width: 32px;
            height: 32px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 14px;
            background-color: #3498db;
            /* default, can be overridden inline */
        }

        .info-content {
            flex: 1;
        }

        .info-label {
            font-size: 12px;
            color: #777;
            margin-bottom: 4px;
        }

        .info-value {
            font-weight: 600;
            color: #333;
        }

        .progress-detail-item {
            display: flex;
            justify-content: space-between;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .detail-label {
            font-size: 12px;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .detail-value {
            font-size: 14px;
            font-weight: 600;
            color: #495057;
        }

        /* Table Styles */
        .assessments-card .table {
            margin-bottom: 0;
        }

        .assessments-card .table th {
            background: #f8f9fa;
            border-color: #dee2e6;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .assessments-card .table td {
            vertical-align: middle;
            border-color: #dee2e6;
        }

        /* Modal Styles */
        .timeline-modal-content {
            padding: 20px 0;
        }

        .timeline-modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e9ecef;
        }

        .timeline-modal-date {
            color: #6c757d;
            font-size: 14px;
        }

        .timeline-modal-body {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .timeline-modal-description h6,
        .timeline-modal-assessment h6 {
            font-weight: 600;
            color: #495057;
            margin-bottom: 10px;
        }

        .assessment-details {
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .assessment-item {
            padding: 8px 12px;
            background: #f8f9fa;
            border-radius: 4px;
            font-size: 14px;
        }

        /* Offering Letter Number visibility overrides */
        #offering_letter_number.form-control[readonly] {
            background-color: #fff;
            /* base to prevent Bootstrap gray */
            color: #2c3e50;
            font-weight: 600;
        }

        #offering_letter_number.form-control[readonly].alert-success {
            background-color: #d4edda !important;
            border-color: #c3e6cb !important;
            color: #155724 !important;
        }

        #offering_letter_number.form-control[readonly].alert-warning {
            background-color: #fff3cd !important;
            border-color: #ffeeba !important;
            color: #856404 !important;
        }

        /* Unified Decision Buttons Styling */
        .decision-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
            align-items: center;
        }

        .decision-btn {
            min-width: 120px;
            padding: 12px 20px;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 2px solid;
            background: white;
        }

        .decision-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }

        .decision-btn.btn-outline-success {
            border-color: #28a745;
            color: #28a745;
        }

        .decision-btn.btn-outline-success:hover {
            background-color: #28a745;
            color: white;
        }

        .decision-btn.btn-outline-danger {
            border-color: #dc3545;
            color: #dc3545;
        }

        .decision-btn.btn-outline-danger:hover {
            background-color: #dc3545;
            color: white;
        }

        .decision-btn.btn-outline-warning {
            border-color: #ffc107;
            color: #ffc107;
        }

        .decision-btn.btn-outline-warning:hover {
            background-color: #ffc107;
            color: white;
        }

        /* Unified decision button states */
        .decision-btn.active.btn-outline-success,
        #offeringModal .decision-btn.btn-success,
        #cvReviewModal .decision-btn.btn-success,
        #interviewModal .decision-btn.btn-success {
            background-color: #28a745 !important;
            color: #fff !important;
            border-color: #28a745 !important;
        }

        .decision-btn.active.btn-outline-danger,
        #offeringModal .decision-btn.btn-danger,
        #cvReviewModal .decision-btn.btn-danger,
        #interviewModal .decision-btn.btn-danger {
            background-color: #dc3545 !important;
            color: #fff !important;
            border-color: #dc3545 !important;
        }

        .decision-btn.btn-success,
        .decision-btn.btn-danger {
            color: #fff;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .fptk-content .row {
                display: flex;
                flex-direction: column;
            }

            .fptk-content .col-lg-8 {
                order: 1;
                width: 100%;
            }

            .fptk-content .col-lg-4 {
                order: 2;
                width: 100%;
            }

            /* Ensure assessments stay below right column on mobile */
            .fptk-content .assessments-section {
                order: 3;
                width: 100%;
            }

            .fptk-card {
                margin-bottom: 20px;
            }

            .fptk-content {
                padding: 0 15px;
            }
        }

        @media (max-width: 768px) {
            .fptk-header {
                height: auto;
                padding: 15px;
                position: relative;
            }

            .fptk-header-content {
                padding-right: 80px;
            }

            .fptk-number {
                font-size: 20px;
            }

            .fptk-status-pill {
                position: absolute;
                top: 15px;
                right: 15px;
                margin-top: 0;
                align-self: flex-start;
            }

            .card-body {
                padding: 15px;
            }

            .timeline-horizontal {
                padding: 10px 0;
            }

            .timeline-item {
                margin: 0 10px;
            }

            .timeline-marker {
                width: 40px;
                height: 40px;
                font-size: 14px;
            }

            .timeline-content {
                max-width: 100px;
            }

            .decision-buttons {
                flex-direction: column;
                gap: 10px;
            }

            .decision-btn {
                min-width: 100%;
            }
        }

        @media (min-width: 993px) {
            .fptk-content .row {
                display: flex;
                flex-wrap: wrap;
            }

            .fptk-content .col-lg-8 {
                flex: 0 0 66.666667%;
                max-width: 66.666667%;
            }

            .fptk-content .col-lg-4 {
                flex: 0 0 33.333333%;
                max-width: 33.333333%;
            }
        }
    </style>
@endsection

@section('scripts')
    <!-- Select2 script for letter number selector -->
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $(function() {
            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();

            // Timeline item click animation with validation
            $('.timeline-item').click(function(e) {
                // Check if stage is disabled
                if ($(this).hasClass('disabled')) {
                    e.preventDefault();
                    e.stopPropagation();

                    // Show informative message
                    if (typeof Swal !== 'undefined' && Swal.fire) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Stage Locked',
                            text: 'Cannot edit this stage because a previous stage failed or was rejected.',
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'OK'
                        });
                    } else {
                        alert('Cannot edit this stage because a previous stage failed or was rejected.');
                    }
                    return false;
                }

                // Normal click animation for editable stages
                $(this).addClass('clicked');
                setTimeout(() => {
                    $(this).removeClass('clicked');
                }, 200);
            });

            // CV Review Modal Handlers
            const cvApproveBtn = document.querySelector('#cvReviewModal .btn-outline-success');
            const cvRejectBtn = document.querySelector('#cvReviewModal .btn-outline-danger');
            const cvStatusInput = document.getElementById('cv_review_decision');
            const cvSubmitBtn = document.querySelector('#cvReviewModal .submit-btn');
            const cvRemarkTextarea = document.getElementById('cv_review_notes');

            // Handle decision button clicks (CV Review)
            function selectCvDecision(status) {
                // Remove active class from all buttons
                cvApproveBtn.classList.remove('active');
                cvRejectBtn.classList.remove('active');

                // Add active class to selected button + unified green/red state
                if (status === 'recommended') {
                    cvApproveBtn.classList.add('active');
                } else if (status === 'not_recommended') {
                    cvRejectBtn.classList.add('active');
                }

                // Update hidden input
                cvStatusInput.value = status;

                // Enable submit button if remark is filled
                checkCvFormValidity();
            }

            // Handle button clicks
            if (cvApproveBtn) {
                cvApproveBtn.addEventListener('click', function() {
                    selectCvDecision('recommended');
                    cvApproveBtn.classList.add('active');
                    cvRejectBtn.classList.remove('active');
                });
            }

            if (cvRejectBtn) {
                cvRejectBtn.addEventListener('click', function() {
                    selectCvDecision('not_recommended');
                    cvRejectBtn.classList.add('active');
                    cvApproveBtn.classList.remove('active');
                });
            }

            // Check form validity
            function checkCvFormValidity() {
                const hasStatus = cvStatusInput.value !== '';
                const hasRemark = cvRemarkTextarea.value.trim() !== '';

                if (hasStatus && hasRemark) {
                    cvSubmitBtn.disabled = false;
                    cvSubmitBtn.classList.remove('btn-secondary');
                    cvSubmitBtn.classList.add('btn-primary');
                } else {
                    cvSubmitBtn.disabled = true;
                    cvSubmitBtn.classList.remove('btn-primary');
                    cvSubmitBtn.classList.add('btn-secondary');
                }
            }

            // Listen for remark changes
            if (cvRemarkTextarea) {
                cvRemarkTextarea.addEventListener('input', checkCvFormValidity);
            }

            // CV Review Form -> standard POST with hidden assessment_data
            $('#cvReviewForm').on('submit', function(e) {
                const decision = $('#cv_review_decision').val();
                const notes = $('#cv_review_notes').val();

                if (!decision) {
                    e.preventDefault();
                    toast_error('Please select a decision first.');
                    return;
                }

                if (!notes || !notes.trim()) {
                    e.preventDefault();
                    toast_error('Please provide approval notes.');
                    return;
                }

                const data = {
                    stage: 'cv_review',
                    decision: decision,
                    notes: notes
                };
                $('#cv_review_assessment_data').val(JSON.stringify(data));
            });

            // Psikotes Modal Handlers
            $('#psikotes_online_score').on('input', function() {
                validatePsikotesOnline();
            });

            $('#psikotes_offline_score').on('input', function() {
                validatePsikotesOffline();
            });

            $('#tes_teori_score').on('input', function() {
                updateTesTeoriNotes();
            });

            // Preserve user notes when they type in the textarea
            $('#tes_teori_notes').on('input', function() {
                const score = parseFloat($('#tes_teori_score').val());
                if (!isNaN(score)) {
                    // Only update if there's a valid score
                    updateTesTeoriNotes();
                }
            });

            // Psikotes Form -> direct POST to update-psikotes route
            $('#psikotesForm').on('submit', function(e) {
                const onlineScore = parseFloat($('#psikotes_online_score').val());
                const offlineScore = parseFloat($('#psikotes_offline_score').val());

                if (isNaN(onlineScore) && isNaN(offlineScore)) {
                    e.preventDefault();
                    toast_error('Please provide at least one score (online or offline)');
                    return;
                }
            });

            // Tes Teori Form -> standard POST with hidden assessment_data
            // Tes Teori Form -> direct POST to update-tes-teori route
            $('#tesTeoriForm').on('submit', function(e) {
                const score = parseFloat($('#tes_teori_score').val());

                if (isNaN(score)) {
                    e.preventDefault();
                    toast_error('Please provide a valid score');
                    return;
                }
            });

            // Interview Modal Handlers
            const interviewApproveBtn = document.querySelector('#interviewModal .btn-outline-success');
            const interviewRejectBtn = document.querySelector('#interviewModal .btn-outline-danger');
            const interviewStatusInput = document.getElementById('interview_decision');
            const interviewSubmitBtn = document.querySelector('#interviewModal .submit-btn');
            const interviewRemarkTextarea = document.getElementById('interview_notes');

            // Handle decision button clicks (Interview)
            function selectInterviewDecision(status) {
                // Remove active class from all buttons
                interviewApproveBtn.classList.remove('active');
                interviewRejectBtn.classList.remove('active');

                // Add active class to selected button + unified green/red state
                if (status === 'recommended') {
                    interviewApproveBtn.classList.add('active');
                } else if (status === 'not_recommended') {
                    interviewRejectBtn.classList.add('active');
                }

                // Update hidden input
                interviewStatusInput.value = status;

                // Enable submit button if remark is filled
                checkInterviewFormValidity();
            }

            // Handle button clicks
            if (interviewApproveBtn) {
                interviewApproveBtn.addEventListener('click', function() {
                    selectInterviewDecision('recommended');
                    interviewApproveBtn.classList.add('active');
                    interviewRejectBtn.classList.remove('active');
                });
            }

            if (interviewRejectBtn) {
                interviewRejectBtn.addEventListener('click', function() {
                    selectInterviewDecision('not_recommended');
                    interviewRejectBtn.classList.add('active');
                    interviewApproveBtn.classList.remove('active');
                });
            }

            // Check form validity
            function checkInterviewFormValidity() {
                const hasType = $('#interview_type').val() !== '';
                const hasStatus = interviewStatusInput.value !== '';
                const hasRemark = interviewRemarkTextarea.value.trim() !== '';

                // Check if selected interview type is already completed
                const selectedType = $('#interview_type').val();
                const isTypeCompleted = selectedType && $('#interview_type option:selected').prop('disabled');

                if (hasType && hasStatus && hasRemark && !isTypeCompleted) {
                    interviewSubmitBtn.disabled = false;
                    interviewSubmitBtn.classList.remove('btn-secondary');
                    interviewSubmitBtn.classList.add('btn-primary');
                } else {
                    interviewSubmitBtn.disabled = true;
                    interviewSubmitBtn.classList.remove('btn-primary');
                    interviewSubmitBtn.classList.add('btn-secondary');
                }
            }

            // Listen for remark changes
            if (interviewRemarkTextarea) {
                interviewRemarkTextarea.addEventListener('input', checkInterviewFormValidity);
            }

            // Listen for interview type changes
            $('#interview_type').on('change', function() {
                const selectedType = $(this).val();
                const selectedOption = $(this).find('option:selected');

                // Check if selected type is already completed
                if (selectedOption.prop('disabled')) {
                    // Reset selection if disabled option is selected
                    $(this).val('');
                    toast_error(
                        'This interview type has already been completed. Please select a different type.'
                    );
                    return;
                }

                checkInterviewFormValidity();
            });

            // Interview Form -> standard POST with hidden decision
            $('#interviewForm').on('submit', function(e) {
                const type = $('#interview_type').val();
                const decision = $('#interview_decision').val();
                const notes = $('#interview_notes').val();

                if (!type) {
                    e.preventDefault();
                    toast_error('Please select an interview type first.');
                    return;
                }

                // Check if interview type is already completed
                const selectedOption = $('#interview_type option:selected');
                if (selectedOption.prop('disabled')) {
                    e.preventDefault();
                    toast_error(
                        'This interview type has already been completed. Please select a different type.'
                    );
                    return;
                }

                if (!decision) {
                    e.preventDefault();
                    toast_error('Please select a decision first.');
                    return;
                }

                if (!notes || !notes.trim()) {
                    e.preventDefault();
                    toast_error('Please provide approval notes.');
                    return;
                }
            });

            // Set interview type when modal is opened
            $('#interviewModal').on('show.bs.modal', function() {
                // Reset form - let user choose interview type
                $('#interview_type').val('');
                $('#interview_decision').val('');
                $('#interview_notes').val('');
                $('.decision-btn').removeClass('active');
                $('.submit-btn').prop('disabled', true).removeClass('btn-primary').addClass(
                    'btn-secondary');

                // Re-enable all options first, then disable completed ones
                $('#interview_type option').prop('disabled', false);
                $('#interview_type option[value="hr"]').prop('disabled',
                    {{ $session->isInterviewTypeCompleted('hr') ? 'true' : 'false' }});
                $('#interview_type option[value="user"]').prop('disabled',
                    {{ $session->isInterviewTypeCompleted('user') ? 'true' : 'false' }});
                @if (!$session->shouldSkipTheoryTest())
                    $('#interview_type option[value="trainer"]').prop('disabled',
                        {{ $session->isInterviewTypeCompleted('trainer') ? 'true' : 'false' }});
                @endif
            });

            // MCU Form -> standard POST with hidden assessment_data
            // MCU decision buttons
            const mcuFitBtn = document.querySelector('#mcuModal .btn-outline-success[data-mcu="fit"]');
            const mcuUnfitBtn = document.querySelector('#mcuModal .btn-outline-danger[data-mcu="unfit"]');
            const mcuFollowUpBtn = document.querySelector('#mcuModal .btn-outline-warning[data-mcu="follow_up"]');
            const mcuHiddenInput = document.getElementById('mcu_overall_health');
            const mcuSubmitBtn = document.querySelector('#mcuModal .submit-btn');

            function selectMcuDecision(val) {
                // clear active classes
                [mcuFitBtn, mcuUnfitBtn, mcuFollowUpBtn].forEach(btn => btn && btn.classList.remove('active'));
                if (val === 'fit' && mcuFitBtn) mcuFitBtn.classList.add('active');
                if (val === 'unfit' && mcuUnfitBtn) mcuUnfitBtn.classList.add('active');
                if (val === 'follow_up' && mcuFollowUpBtn) mcuFollowUpBtn.classList.add('active');
                mcuHiddenInput.value = val;
                if (mcuSubmitBtn) {
                    mcuSubmitBtn.disabled = false;
                }
            }

            if (mcuFitBtn) mcuFitBtn.addEventListener('click', () => selectMcuDecision('fit'));
            if (mcuUnfitBtn) mcuUnfitBtn.addEventListener('click', () => selectMcuDecision('unfit'));
            if (mcuFollowUpBtn) mcuFollowUpBtn.addEventListener('click', () => selectMcuDecision('follow_up'));

            $('#mcuForm').on('submit', function(e) {
                if (!$('#mcu_overall_health').val()) {
                    e.preventDefault();
                    toast_error('Please choose MCU decision first');
                }
            });

            // Decision button click handlers for offering (scoped to modal)
            $('#offeringModal .decision-btn').on('click', function() {
                const decision = $(this).data('decision');
                $('#offering_result').val(decision);

                // Update button states (only inside offering modal)
                const $buttons = $('#offeringModal .decision-btn');
                // Clear all color classes from both buttons
                $buttons.removeClass(
                    'active btn-success btn-danger btn-outline-success btn-outline-danger');
                // Reapply base outline according to their own data-decision
                $buttons.each(function() {
                    const type = $(this).data('decision');
                    $(this).addClass(type === 'accepted' ? 'btn-outline-success' :
                        'btn-outline-danger');
                });
                // Mark current as active (CSS will render solid green/red)
                $(this).addClass('active');

                // Enable submit button
                $('#offering_submit_btn').prop('disabled', false);
            });

            // Ensure correct base styles when modal opens
            $('#offeringModal').on('show.bs.modal', function() {
                const $buttons = $('#offeringModal .decision-btn');
                $buttons.removeClass(
                    'active btn-success btn-danger btn-outline-success btn-outline-danger');
                $buttons.each(function() {
                    const type = $(this).data('decision');
                    $(this).addClass(type === 'accepted' ? 'btn-outline-success' :
                        'btn-outline-danger');
                });
                $('#offering_result').val('');
                $('#offering_submit_btn').prop('disabled', true);
            });

            // Mirror LOT Number behavior: update Offering Letter Number display when selector changes
            $(document).on('change', '[name="offering_letter_number_id"]', function() {
                const selectedOption = $(this).find('option:selected');
                const rawText = selectedOption.text();
                const baseLetterNumber = rawText ? rawText.split(' - ')[0] : '';
                const $display = $('#offering_letter_number');

                // Build formatted number: (letter number)/ARKA-HCS/(bulan romawi)/(tahun)
                const months = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
                const now = new Date();
                const romanMonth = months[now.getMonth()];
                const year = now.getFullYear();
                const formatted = baseLetterNumber ? `${baseLetterNumber}/ARKA-HCS/${romanMonth}/${year}` :
                    '';

                if (selectedOption.val() && formatted) {
                    $display.val(formatted);
                    $display.removeClass('alert-warning').addClass('alert-success');
                } else {
                    $display.val('Select letter number above');
                    $display.removeClass('alert-success').addClass('alert-warning');
                }
            });

            // Offering Form validation
            $('#offeringForm').on('submit', function(e) {
                const result = $('#offering_result').val();
                const letterNumberId = $('select[name="offering_letter_number_id"]').val();

                if (!result) {
                    e.preventDefault();
                    toast_error('Please select an offering decision');
                    return;
                }

                if (!letterNumberId) {
                    e.preventDefault();
                    toast_error('Please select an offering letter number');
                    return;
                }
            });

            // Hiring handlers: mirror offering patterns
            // Auto-fill PKWT letter number display
            $(document).on('change', '[name="hiring_letter_number_id"]', function() {
                const selectedOption = $(this).find('option:selected');
                const rawText = selectedOption.text();
                const baseLetterNumber = rawText ? rawText.split(' - ')[0] : '';
                const $display = $('#hiring_letter_number');

                // Format: 0001/ARKA-HO/PKWT-I/VIII/2025
                const months = ['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X', 'XI', 'XII'];
                const now = new Date();
                const romanMonth = months[now.getMonth()];
                const year = now.getFullYear();
                // Remove any leading alpha prefix like 'PKWT' from the base to get pure number (e.g., PKWT0001 -> 0001)
                const numericBase = baseLetterNumber ? baseLetterNumber.replace(/^[A-Za-z]+/, '') : '';
                const formatted = numericBase ?
                    `${numericBase}/ARKA-HO/PKWT-I/${romanMonth}/${year}` : '';

                if (selectedOption.val() && formatted) {
                    $display.val(formatted);
                    $display.removeClass('alert-warning').addClass('alert-success');
                } else {
                    $display.val('Select letter number above');
                    $display.removeClass('alert-success').addClass('alert-warning');
                }
                validateHireForm();
            });

            // Agreement type buttons
            const hirePkwtBtn = document.querySelector(
                '#hireModal .decision-btn.btn-outline-success[data-agreement="pkwt"]');
            const hirePkwttBtn = document.querySelector(
                '#hireModal .decision-btn.btn-outline-warning[data-agreement="pkwtt"]');
            const agreementHidden = document.getElementById('agreement_type');
            const hireSubmitBtn = document.getElementById('hire_submit_btn');

            function selectAgreement(val) {
                if (hirePkwtBtn) hirePkwtBtn.classList.remove('active');
                if (hirePkwttBtn) hirePkwttBtn.classList.remove('active');
                if (val === 'pkwt' && hirePkwtBtn) hirePkwtBtn.classList.add('active');
                if (val === 'pkwtt' && hirePkwttBtn) hirePkwttBtn.classList.add('active');
                agreementHidden.value = val;
                // Toggle FOC required for PKWT
                if (val === 'pkwt') {
                    $('#foc_container').show();
                    $('#administration_foc').attr('required', true);
                } else {
                    $('#foc_container').hide();
                    $('#administration_foc').removeAttr('required').val('');
                }
                validateHireForm();
            }

            if (hirePkwtBtn) hirePkwtBtn.addEventListener('click', () => selectAgreement('pkwt'));
            if (hirePkwttBtn) hirePkwttBtn.addEventListener('click', () => selectAgreement('pkwtt'));

            function validateHireForm() {
                const hasAgreement = $('#agreement_type').val() !== '';
                const hasLetter = $('[name="hiring_letter_number_id"]').val();
                if (hireSubmitBtn) hireSubmitBtn.disabled = !(hasAgreement && hasLetter);
            }

            // Reset Hire modal state when shown
            $('#hireModal').on('show.bs.modal', function() {
                if (hirePkwtBtn) hirePkwtBtn.classList.remove('active');
                if (hirePkwttBtn) hirePkwttBtn.classList.remove('active');
                $('#agreement_type').val('');
                $('#hiring_letter_number').val('Select letter number above').removeClass('alert-success')
                    .addClass('alert-warning');
                if (hireSubmitBtn) hireSubmitBtn.disabled = true;
                $('#foc_container').hide();
                $('#administration_foc').removeAttr('required').val('');
            });

            // Global confirmation for all stage submit forms
            $(document).on('submit', 'form.confirm-submit', function(e) {
                const form = this;
                if (form.dataset.submitting === 'true') {
                    return; // prevent double submit loops
                }
                e.preventDefault();
                const message = form.getAttribute('data-confirm-message') ||
                    'Submit? Data cannot be edited after submission.';

                const proceed = () => {
                    form.dataset.submitting = 'true';
                    if (typeof toast_ === 'function') {
                        toast_('info', 'Submitting...');
                    }
                    form.submit();
                };

                if (typeof Swal !== 'undefined' && Swal.fire) {
                    Swal.fire({
                        title: 'Are you sure?',
                        text: message,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, submit',
                        cancelButtonText: 'Cancel'
                    }).then((result) => {
                        if (result.isConfirmed) proceed();
                    });
                } else {
                    if (confirm(message)) proceed();
                }
            });



            // Simple validations for non-assessment forms (still standard POST)
            $('#rejectForm').on('submit', function(e) {
                const reason = $('#reject_reason').val();
                if (!reason || !reason.trim()) {
                    e.preventDefault();
                    toast_error('Please provide a rejection reason');
                }
            });
            $('#completeForm').on('submit', function(e) {
                const hireDate = $('#hire_date').val();
                if (!hireDate) {
                    e.preventDefault();
                    toast_error('Please provide a hire date');
                }
            });
            $('#cancelForm').on('submit', function(e) {
                const reason = $('#cancel_reason').val();
                if (!reason || !reason.trim()) {
                    e.preventDefault();
                    toast_error('Please provide a cancellation reason');
                }
            });
        });



        function validatePsikotesOnline() {
            const score = parseFloat($('#psikotes_online_score').val());
            const resultDiv = $('#psikotes_online_result');

            if (isNaN(score)) {
                resultDiv.html('');
                return;
            }

            if (score >= 40) {
                resultDiv.html(
                    '<div class="alert alert-success"><i class="fas fa-check"></i> Proses dapat dilanjutkan</div>');
            } else {
                resultDiv.html('<div class="alert alert-danger"><i class="fas fa-times"></i> Tidak Direkomendasikan</div>');
            }
        }

        function validatePsikotesOffline() {
            const score = parseFloat($('#psikotes_offline_score').val());
            const resultDiv = $('#psikotes_offline_result');

            if (isNaN(score)) {
                resultDiv.html('');
                return;
            }

            if (score >= 8) {
                resultDiv.html(
                    '<div class="alert alert-success"><i class="fas fa-check"></i> Proses dapat dilanjutkan</div>');
            } else {
                resultDiv.html('<div class="alert alert-danger"><i class="fas fa-times"></i> Kurang</div>');
            }
        }



        function updateTesTeoriNotes() {
            const score = parseFloat($('#tes_teori_score').val());
            const notesTextarea = $('#tes_teori_notes');

            if (isNaN(score)) {
                // If no score, clear the category from notes
                const currentNotes = notesTextarea.val();
                const lines = currentNotes.split('\n');
                const filteredLines = lines.filter(line => !line.startsWith('Kategori:'));
                notesTextarea.val(filteredLines.join('\n').trim());
                return;
            }

            // Determine category based on score
            let category = '';
            if (score >= 76) {
                category = 'Mechanic Senior';
            } else if (score >= 61) {
                category = 'Mechanic Advance';
            } else if (score >= 46) {
                category = 'Mechanic';
            } else if (score >= 21) {
                category = 'Helper Mechanic';
            } else {
                category = 'Belum Kompeten';
            }

            // Get current notes content
            let currentNotes = notesTextarea.val();

            // Remove existing category line if it exists
            const lines = currentNotes.split('\n');
            const filteredLines = lines.filter(line => !line.startsWith('Kategori:'));
            const userNotes = filteredLines.join('\n').trim();

            // Create new notes with category at the top
            const newNotes = `Kategori: ${category}${userNotes ? '\n\n' + userNotes : ''}`;

            // Update the textarea
            notesTextarea.val(newNotes);
        }

        // Helper functions for toast notifications
        function toast_success(message) {
            if (typeof toast_ !== 'undefined') {
                toast_('success', message);
            } else if (typeof toastr !== 'undefined') {
                toastr.success(message);
            } else {
                alert('Success: ' + message);
            }
        }

        function toast_error(message) {
            if (typeof toast_ !== 'undefined') {
                toast_('error', message);
            } else if (typeof toastr !== 'undefined') {
                toastr.error(message);
            } else {
                alert('Error: ' + message);
            }
        }

        // Autofill department based on position selection in hire modal
        $('#hire_position_id').on('change', function() {
            var position_id = $(this).val();

            if (position_id) {
                var url = "{{ route('employees.getDepartment') }}";

                $.ajax({
                    url: url,
                    type: "GET",
                    data: {
                        position_id: position_id
                    },
                    dataType: 'json',
                    success: function(data) {
                        if (data && data.department_name) {
                            $('#hire_department').val(data.department_name);
                        } else {
                            $('#hire_department').val('');
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.error("Error fetching department: ", textStatus, errorThrown);
                        $('#hire_department').val('');
                    }
                });
            } else {
                $('#hire_department').val('');
            }
        });

        // Initialize department when hire modal is shown
        $('#hireModal').on('shown.bs.modal', function() {
            var selectedPosition = $('#hire_position_id').val();
            if (selectedPosition) {
                $('#hire_position_id').trigger('change');
            }
        });

        // Also initialize department when page loads if position is pre-selected
        var selectedPosition = $('#hire_position_id').val();
        if (selectedPosition) {
            $('#hire_position_id').trigger('change');
        }
    </script>
@endsection
