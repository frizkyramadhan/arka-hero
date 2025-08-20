@extends('layouts.main')

@section('content')
    <div class="content-wrapper-custom">
        <div class="fptk-header">
            <div class="fptk-header-content">
                <div class="fptk-project">{{ $fptk->project->project_name }}</div>
                <h1 class="fptk-number">{{ $fptk->request_number }}</h1>
                <div class="fptk-date">
                    <i class="far fa-calendar-alt"></i> {{ date('d F Y', strtotime($fptk->created_at)) }}
                </div>
                @php
                    $statusMap = [
                        'draft' => ['label' => 'Draft', 'class' => 'badge badge-secondary', 'icon' => 'fa-edit'],
                        'submitted' => [
                            'label' => 'Submitted',
                            'class' => 'badge badge-info',
                            'icon' => 'fa-paper-plane',
                        ],
                        'approved' => [
                            'label' => 'Approved',
                            'class' => 'badge badge-success',
                            'icon' => 'fa-check-circle',
                        ],
                        'rejected' => [
                            'label' => 'Rejected',
                            'class' => 'badge badge-danger',
                            'icon' => 'fa-times-circle',
                        ],
                        'cancelled' => ['label' => 'Cancelled', 'class' => 'badge badge-warning', 'icon' => 'fa-ban'],
                        'closed' => [
                            'label' => 'Closed',
                            'class' => 'badge badge-primary',
                            'icon' => 'fa-check-circle',
                        ],
                    ];
                    $status = $fptk->status;
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
                    <!-- Main FPTK Info -->
                    <div class="fptk-card fptk-info-card">
                        <div class="card-head">
                            <h2><i class="fas fa-user-tie"></i> FPTK Information</h2>
                        </div>
                        <div class="card-body">
                            <div class="info-grid">
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #3498db;">
                                        <i class="fas fa-building"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Department</div>
                                        <div class="info-value">{{ $fptk->department->department_name }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #e74c3c;">
                                        <i class="fas fa-project-diagram"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Project</div>
                                        <div class="info-value">{{ $fptk->project->project_code }} -
                                            {{ $fptk->project->project_name }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #f1c40f;">
                                        <i class="fas fa-user-tie"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Position</div>
                                        <div class="info-value">{{ $fptk->position->position_name }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #9b59b6;">
                                        <i class="fas fa-layer-group"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Level</div>
                                        <div class="info-value">{{ $fptk->level->name }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #1abc9c;">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Required Quantity</div>
                                        <div class="info-value">{{ $fptk->required_qty }}
                                            {{ $fptk->required_qty > 1 ? 'persons' : 'person' }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #e67e22;">
                                        <i class="fas fa-calendar-check"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Required Date</div>
                                        <div class="info-value">{{ date('d F Y', strtotime($fptk->required_date)) }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #34495e;">
                                        <i class="fas fa-briefcase"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Employment Type</div>
                                        <div class="info-value">
                                            {{ ucfirst(str_replace('_', ' ', $fptk->employment_type)) }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #2c3e50;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Created By</div>
                                        <div class="info-value">{{ $fptk->createdBy->name ?? 'N/A' }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #e91e63;">
                                        <i class="fas fa-book"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Theory Test Requirement</div>
                                        <div class="info-value">
                                            @if ($fptk->requires_theory_test)
                                                <span class="badge badge-warning">
                                                    <i class="fas fa-check-circle"></i> Required
                                                </span>
                                                <br><small class="text-muted">Posisi mekanik/teknis</small>
                                            @else
                                                <span class="badge badge-secondary">
                                                    <i class="fas fa-times-circle"></i> Not Required
                                                </span>
                                                <br><small class="text-muted">Posisi non-teknis</small>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Recruitment Progress -->
                    <div class="fptk-card progress-card">
                        <div class="card-head">
                            <h2><i class="fas fa-chart-line"></i> Recruitment Progress</h2>
                        </div>
                        <div class="card-body">
                            <!-- Theory Test Requirement Info -->
                            <div class="theory-test-info mb-4">
                                <div class="alert {{ $fptk->requires_theory_test ? 'alert-warning' : 'alert-info' }} mb-0">
                                    <div class="d-flex align-items-center">
                                        <i class="fas {{ $fptk->requires_theory_test ? 'fa-exclamation-triangle' : 'fa-info-circle' }} mr-3"
                                            style="font-size: 1.2em;"></i>
                                        <div>
                                            <strong>
                                                @if ($fptk->requires_theory_test)
                                                    Posisi ini memerlukan Tes Teori
                                                @else
                                                    Posisi ini tidak memerlukan Tes Teori
                                                @endif
                                            </strong>
                                            <br>
                                            <small class="text-muted">
                                                @if ($fptk->requires_theory_test)
                                                    Kandidat harus lulus tes teori sebelum interview. Stage tes teori akan
                                                    muncul di timeline recruitment.
                                                @else
                                                    Kandidat langsung ke interview setelah psikotes. Stage tes teori akan
                                                    di-skip di timeline recruitment.
                                                @endif
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @php
                                $hiredCount = $sessions->where('status', 'hired')->count();
                                $inProcessCount = $sessions
                                    ->whereIn('status', [
                                        'in_process',
                                        'cv_review',
                                        'psikotes',
                                        'interview',
                                        'offering',
                                        'mcu',
                                    ])
                                    ->count();
                                $rejectedCount = $sessions->where('status', 'rejected')->count();
                                $otherCount = $sessions->count() - $hiredCount - $inProcessCount - $rejectedCount;
                                $totalSessions = $sessions->count();
                            @endphp
                            <div class="progress-stats">
                                <div class="progress-item">
                                    <div class="progress-label">
                                        <i class="fas fa-check-circle text-success"></i> Hired
                                    </div>
                                    <div class="progress-bar-container">
                                        <div class="progress-bar"
                                            style="width: {{ $totalSessions > 0 ? ($hiredCount / $totalSessions) * 100 : 0 }}%; background-color: #28a745;">
                                        </div>
                                    </div>
                                    <div class="progress-count">{{ $hiredCount }}</div>
                                </div>
                                <div class="progress-item">
                                    <div class="progress-label">
                                        <i class="fas fa-clock text-info"></i> In Process
                                    </div>
                                    <div class="progress-bar-container">
                                        <div class="progress-bar"
                                            style="width: {{ $totalSessions > 0 ? ($inProcessCount / $totalSessions) * 100 : 0 }}%; background-color: #17a2b8;">
                                        </div>
                                    </div>
                                    <div class="progress-count">{{ $inProcessCount }}</div>
                                </div>
                                <div class="progress-item">
                                    <div class="progress-label">
                                        <i class="fas fa-times-circle text-danger"></i> Rejected
                                    </div>
                                    <div class="progress-bar-container">
                                        <div class="progress-bar"
                                            style="width: {{ $totalSessions > 0 ? ($rejectedCount / $totalSessions) * 100 : 0 }}%; background-color: #dc3545;">
                                        </div>
                                    </div>
                                    <div class="progress-count">{{ $rejectedCount }}</div>
                                </div>
                                @if ($otherCount > 0)
                                    <div class="progress-item">
                                        <div class="progress-label">
                                            <i class="fas fa-question-circle text-secondary"></i> Other
                                        </div>
                                        <div class="progress-bar-container">
                                            <div class="progress-bar"
                                                style="width: {{ $totalSessions > 0 ? ($otherCount / $totalSessions) * 100 : 0 }}%; background-color: #6c757d;">
                                            </div>
                                        </div>
                                        <div class="progress-count">{{ $otherCount }}</div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
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
                                @can('recruitment-sessions.create')
                                    @if ($fptk->status !== 'closed')
                                        <button type="button" class="btn-action add-candidate-btn" data-toggle="modal"
                                            data-target="#addCandidateModal">
                                            <i class="fas fa-plus"></i> Add Candidate
                                        </button>
                                    @endif
                                @endcan
                                <a href="{{ route('dashboard.recruitment') }}" class="btn-action dashboard-btn">
                                    <i class="fas fa-chart-bar"></i> View Dashboard
                                </a>
                                <a href="{{ route('recruitment.sessions.index') }}" class="btn-action back-btn">
                                    <i class="fas fa-arrow-left"></i> Back to Sessions
                                </a>
                                @if ($fptk->status !== 'closed')
                                    <form method="POST"
                                        action="{{ route('recruitment.sessions.close-request', $fptk->id) }}"
                                        class="d-block confirm-submit"
                                        data-confirm-message="Close this recruitment request (FPTK)? You cannot undo this action.">
                                        @csrf
                                        <button type="submit" class="btn btn-warning btn-block">
                                            <i class="fas fa-lock"></i> Close Request
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Summary Stats -->
                    <div class="fptk-card summary-card p-2">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-chart-pie mr-2 text-primary"></i>
                            <span class="font-weight-bold">Summary</span>
                        </div>
                        <div class="d-flex flex-wrap justify-content-between text-center">
                            <div class="flex-fill px-1">
                                <div class="rounded-circle mx-auto mb-1"
                                    style="width:32px;height:32px;background:#3498db;display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-users text-white"></i>
                                </div>
                                <div class="font-weight-bold">{{ $sessions->count() }}</div>
                                <small class="text-muted">Total</small>
                            </div>
                            <div class="flex-fill px-1">
                                <div class="rounded-circle mx-auto mb-1"
                                    style="width:32px;height:32px;background:#e74c3c;display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-user-check text-white"></i>
                                </div>
                                <div class="font-weight-bold">{{ $hiredCount }}</div>
                                <small class="text-muted">Hired</small>
                            </div>
                            <div class="flex-fill px-1">
                                <div class="rounded-circle mx-auto mb-1"
                                    style="width:32px;height:32px;background:#f1c40f;display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-clock text-white"></i>
                                </div>
                                <div class="font-weight-bold">{{ $inProcessCount }}</div>
                                <small class="text-muted">In Process</small>
                            </div>
                            <div class="flex-fill px-1">
                                <div class="rounded-circle mx-auto mb-1"
                                    style="width:32px;height:32px;background:#e67e22;display:flex;align-items:center;justify-content:center;">
                                    <i class="fas fa-percentage text-white"></i>
                                </div>
                                <div class="font-weight-bold">
                                    {{ $fptk->required_qty > 0 ? round(($hiredCount / $fptk->required_qty) * 100) : 0 }}%
                                </div>
                                <small class="text-muted">Fill Rate</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Candidate Sessions Table -->
            <div class="fptk-card sessions-table-card">
                <div class="card-head">
                    <h2><i class="fas fa-list"></i> Candidate Sessions</h2>
                    @if (!$fptk->requires_theory_test)
                        <small class="text-muted">
                            <i class="fas fa-info-circle"></i>
                            Tes Teori stage di-skip untuk posisi non-mekanik
                        </small>
                    @endif
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="text-center align-middle" style="width: 50px;">No</th>
                                    <th class="align-middle">Candidate Name</th>
                                    <th class="text-center align-middle">CV Review</th>
                                    <th class="text-center align-middle">Psikotes</th>
                                    @if ($fptk->requires_theory_test)
                                        <th class="text-center align-middle">Tes Teori</th>
                                    @endif
                                    <th class="text-center align-middle">Interview HR</th>
                                    <th class="text-center align-middle">Interview User</th>
                                    <th class="text-center align-middle">Offering</th>
                                    <th class="text-center align-middle">MCU</th>
                                    <th class="text-center align-middle">Hire</th>
                                    <th class="text-center align-middle">Onboarding</th>
                                    <th class="text-center align-middle">Final Status</th>
                                    <th class="text-center align-middle" style="width: 120px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($sessions as $index => $session)
                                    <tr>
                                        <td class="text-center">{{ $index + 1 }}</td>
                                        <td>
                                            <strong>{{ $session->candidate->fullname ?? 'N/A' }}</strong>
                                            <br>
                                            <small class="text-muted">Session: {{ $session->session_number }}</small>
                                        </td>
                                        @php
                                            // Define stages based on theory test requirement
                                            if ($fptk->requires_theory_test) {
                                                $stages = [
                                                    'cv_review',
                                                    'psikotes',
                                                    'tes_teori',
                                                    'interview_hr',
                                                    'interview_user',
                                                    'offering',
                                                    'mcu',
                                                    'hire',
                                                    'onboarding',
                                                ];
                                            } else {
                                                $stages = [
                                                    'cv_review',
                                                    'psikotes',
                                                    'interview_hr',
                                                    'interview_user',
                                                    'offering',
                                                    'mcu',
                                                    'hire',
                                                    'onboarding',
                                                ];
                                            }
                                            $currentStage = $session->current_stage;
                                            $stageStatus = $session->stage_status;
                                        @endphp
                                        @foreach ($stages as $stage)
                                            @php
                                                $stageIndex = array_search($stage, $stages);
                                                $currentStageIndex = array_search($currentStage, $stages);

                                                // Use model method to check if stage is completed
                                                $isStageCompleted = $session->isStageCompleted($stage);
                                                $assessment = $session->getAssessmentByStage($stage);

                                                // Determine stage status based on actual data
                                                if ($stageIndex < $currentStageIndex || $isStageCompleted) {
                                                    // Previous stages or completed stages
                                                    if ($isStageCompleted) {
                                                        $icon = 'fas fa-check-circle text-success';
                                                        $tooltip = 'Completed';
                                                    } else {
                                                        // Stage was attempted but not completed (failed)
                                                        $icon = 'fas fa-times-circle text-danger';
                                                        $tooltip = 'Failed';
                                                    }
                                                } elseif ($stageIndex == $currentStageIndex) {
                                                    // Current stage - check actual status
                                                    if ($stageStatus == 'completed' || $isStageCompleted) {
                                                        $icon = 'fas fa-check-circle text-success';
                                                        $tooltip = 'Completed';
                                                    } elseif ($stageStatus == 'failed' || $stageStatus == 'rejected') {
                                                        $icon = 'fas fa-times-circle text-danger';
                                                        $tooltip = 'Failed';
                                                    } elseif ($stageStatus == 'in_progress') {
                                                        $icon = 'fas fa-clock text-warning';
                                                        $tooltip = 'In Progress';
                                                    } else {
                                                        $icon = 'fas fa-clock text-warning';
                                                        $tooltip = 'Waiting';
                                                    }
                                                } elseif (
                                                    $stageIndex > $currentStageIndex &&
                                                    $session->status == 'rejected'
                                                ) {
                                                    // Future stages but session is rejected
                                                    $icon = 'fas fa-ban text-secondary';
                                                    $tooltip = 'Not Applicable';
                                                } else {
                                                    // Future stages
                                                    $icon = 'fas fa-circle text-muted';
                                                    $tooltip = 'Pending';
                                                }

                                                // Override with specific assessment result if available
                                                if ($assessment) {
                                                    switch ($stage) {
                                                        case 'cv_review':
                                                            if (isset($assessment->decision)) {
                                                                if ($assessment->decision === 'recommended') {
                                                                    $icon = 'fas fa-check-circle text-success';
                                                                    $tooltip = 'Recommended';
                                                                } elseif ($assessment->decision === 'not_recommended') {
                                                                    $icon = 'fas fa-times-circle text-danger';
                                                                    $tooltip = 'Not Recommended';
                                                                } else {
                                                                    $icon = 'fas fa-clock text-warning';
                                                                    $tooltip = 'Under Review';
                                                                }
                                                            }
                                                            break;
                                                        case 'psikotes':
                                                        case 'tes_teori':
                                                            if (isset($assessment->result)) {
                                                                if ($assessment->result === 'pass') {
                                                                    $icon = 'fas fa-check-circle text-success';
                                                                    $tooltip = 'Passed';
                                                                } elseif ($assessment->result === 'fail') {
                                                                    $icon = 'fas fa-times-circle text-danger';
                                                                    $tooltip = 'Failed';
                                                                } else {
                                                                    $icon = 'fas fa-clock text-warning';
                                                                    $tooltip = 'In Progress';
                                                                }
                                                            }
                                                            break;
                                                        case 'interview_hr':
                                                        case 'interview_user':
                                                            if (isset($assessment->result)) {
                                                                if ($assessment->result === 'recommended') {
                                                                    $icon = 'fas fa-check-circle text-success';
                                                                    $tooltip = 'Recommended';
                                                                } elseif ($assessment->result === 'not_recommended') {
                                                                    $icon = 'fas fa-times-circle text-danger';
                                                                    $tooltip = 'Not Recommended';
                                                                } else {
                                                                    $icon = 'fas fa-clock text-warning';
                                                                    $tooltip = 'In Progress';
                                                                }
                                                            }
                                                            break;
                                                        case 'offering':
                                                            if (isset($assessment->response)) {
                                                                if ($assessment->response === 'accepted') {
                                                                    $icon = 'fas fa-check-circle text-success';
                                                                    $tooltip = 'Accepted';
                                                                } elseif ($assessment->response === 'rejected') {
                                                                    $icon = 'fas fa-times-circle text-danger';
                                                                    $tooltip = 'Rejected';
                                                                } else {
                                                                    $icon = 'fas fa-clock text-warning';
                                                                    $tooltip = 'Waiting Response';
                                                                }
                                                            }
                                                            break;
                                                        case 'mcu':
                                                            if (isset($assessment->result)) {
                                                                if ($assessment->result === 'fit') {
                                                                    $icon = 'fas fa-check-circle text-success';
                                                                    $tooltip = 'Fit';
                                                                } elseif ($assessment->result === 'unfit') {
                                                                    $icon = 'fas fa-times-circle text-danger';
                                                                    $tooltip = 'Unfit';
                                                                } else {
                                                                    $icon = 'fas fa-clock text-warning';
                                                                    $tooltip = 'In Progress';
                                                                }
                                                            }
                                                            break;
                                                        case 'hire':
                                                        case 'onboarding':
                                                            // These stages just need to exist to be considered completed
                                                            $icon = 'fas fa-check-circle text-success';
                                                            $tooltip = 'Completed';
                                                            break;
                                                    }
                                                }
                                            @endphp
                                            <td class="text-center">
                                                <i class="{{ $icon }}" title="{{ $tooltip }}"
                                                    data-toggle="tooltip"></i>
                                            </td>
                                        @endforeach
                                        <td class="text-center">
                                            @php
                                                $finalStatusMap = [
                                                    'in_process' => '<span class="badge badge-info">In Process</span>',
                                                    'hired' => '<span class="badge badge-success">Hired</span>',
                                                    'rejected' => '<span class="badge badge-danger">Rejected</span>',
                                                    'cancelled' => '<span class="badge badge-warning">Cancelled</span>',
                                                ];
                                            @endphp
                                            {!! $finalStatusMap[$session->status] ??
                                                '<span class="badge badge-secondary">' . ucfirst($session->status) . '</span>' !!}
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('recruitment.sessions.candidate', $session->id) }}"
                                                class="btn btn-sm btn-info" title="View Session Details">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            @can('recruitment-sessions.delete')
                                                @if ($session->status !== 'hired')
                                                    <button type="button" class="btn btn-sm btn-danger delete-session-btn"
                                                        data-session-id="{{ $session->id }}"
                                                        data-candidate-name="{{ $session->candidate->fullname ?? 'N/A' }}"
                                                        data-toggle="modal" data-target="#deleteSessionModal"
                                                        title="Remove Candidate from Session">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                @endif
                                            @endcan
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $fptk->requires_theory_test ? '13' : '12' }}"
                                            class="text-center text-muted">
                                            <i class="fas fa-inbox fa-2x mb-2"></i>
                                            <br>No candidate sessions found for this FPTK
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Session Modal -->
    <div class="modal fade" id="deleteSessionModal" tabindex="-1" role="dialog"
        aria-labelledby="deleteSessionModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteSessionModalLabel">
                        <i class="fas fa-exclamation-triangle text-danger"></i> Remove Candidate from Session
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <i class="fas fa-user-minus fa-3x text-danger"></i>
                    </div>
                    <p class="text-center">
                        Are you sure you want to remove <strong id="candidateNameToDelete"></strong> from this recruitment
                        session?
                    </p>
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i>
                        <strong>Warning:</strong> This action will permanently remove the candidate from this FPTK session.
                        All progress and assessment data will be lost.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <form id="deleteSessionForm" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger">
                            <i class="fas fa-trash"></i> Remove Candidate
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Candidate Modal -->
    <div class="modal fade" id="addCandidateModal" tabindex="-1" role="dialog"
        aria-labelledby="addCandidateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCandidateModalLabel">
                        <i class="fas fa-plus"></i> Add Candidate to FPTK
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="candidate_search">Search Candidate/CV</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="candidate_search"
                                placeholder="Enter candidate name, email, or position applied...">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" id="search_candidate">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div id="search_results" class="mt-3" style="display: none;">
                        <h6>Search Results</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Position Applied</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="candidate_results">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        /* Custom Styles for FPTK Detail */
        .content-wrapper-custom {
            background-color: #f8fafc;
            min-height: 100vh;
            padding-bottom: 40px;
        }

        /* Header */
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

        /* Content Styles */
        .fptk-content {
            padding: 0 20px;
        }

        /* Cards */
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

        .card-head small {
            margin-top: 5px;
            font-size: 12px;
            color: #6c757d;
            display: block;
        }

        .card-head small i {
            margin-right: 5px;
        }

        .card-body {
            padding: 20px;
        }

        /* Info Grid */
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

        /* Progress Stats */
        .progress-stats {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        /* Theory Test Info Styling */
        .theory-test-info .alert {
            border-radius: 8px;
            border: none;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .theory-test-info .alert-warning {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            color: #856404;
        }

        .theory-test-info .alert-info {
            background-color: #d1ecf1;
            border-left: 4px solid #17a2b8;
            color: #0c5460;
        }

        .theory-test-info .alert i {
            color: inherit;
        }

        .theory-test-info .alert strong {
            font-size: 14px;
            line-height: 1.4;
        }

        .theory-test-info .alert small {
            font-size: 12px;
            line-height: 1.3;
        }

        .progress-item {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .progress-label {
            min-width: 120px;
            font-weight: 500;
            color: #495057;
        }

        .progress-bar-container {
            flex: 1;
            height: 8px;
            background: #e9ecef;
            border-radius: 4px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            border-radius: 4px;
            transition: width 0.3s ease;
        }

        .progress-count {
            min-width: 40px;
            text-align: right;
            font-weight: 600;
            color: #495057;
        }

        /* Summary Stats */
        .summary-stats {
            display: flex;
            flex-direction: column;
            gap: 15px;
        }

        .summary-item {
            display: flex;
            align-items: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .summary-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: white;
            font-size: 16px;
        }

        .summary-content {
            flex: 1;
        }

        .summary-value {
            font-size: 24px;
            font-weight: 700;
            color: #495057;
            line-height: 1;
        }

        .summary-label {
            font-size: 12px;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 5px;
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

        .add-candidate-btn {
            background-color: #007bff;
        }

        .add-candidate-btn:hover {
            background-color: #0056b3;
            color: white;
        }

        .dashboard-btn {
            background-color: #17a2b8;
        }

        .dashboard-btn:hover {
            background-color: #138496;
            color: white;
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

        /* Table Styles */
        .sessions-table-card .table {
            margin-bottom: 0;
        }

        .sessions-table-card .table th {
            background: #f8f9fa;
            border-color: #dee2e6;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .sessions-table-card .table td {
            vertical-align: middle;
            border-color: #dee2e6;
        }

        /* Button Group Styles */
        .btn-group .btn {
            border-radius: 0;
        }

        .btn-group .btn:first-child {
            border-top-left-radius: 0.25rem;
            border-bottom-left-radius: 0.25rem;
        }

        .btn-group .btn:last-child {
            border-top-right-radius: 0.25rem;
            border-bottom-right-radius: 0.25rem;
        }

        .btn-group .btn:not(:last-child) {
            border-right: 1px solid rgba(255, 255, 255, 0.3);
        }

        /* Delete Button Hover Effect */
        .btn-danger:hover {
            background-color: #c82333;
            border-color: #bd2130;
        }

        /* Stage Status Icons */
        .sessions-table-card .table td i {
            font-size: 16px;
            cursor: help;
        }

        /* Hover effects for status icons */
        .sessions-table-card .table td i:hover {
            transform: scale(1.1);
            transition: transform 0.2s ease;
        }

        /* Status specific styling */
        .sessions-table-card .table td i.text-success {
            filter: drop-shadow(0 0 2px rgba(40, 167, 69, 0.3));
        }

        .sessions-table-card .table td i.text-danger {
            filter: drop-shadow(0 0 2px rgba(220, 53, 69, 0.3));
        }

        .sessions-table-card .table td i.text-warning {
            filter: drop-shadow(0 0 2px rgba(255, 193, 7, 0.3));
        }

        .sessions-table-card .table td i.text-info {
            filter: drop-shadow(0 0 2px rgba(23, 162, 184, 0.3));
        }

        /* Responsive Adjustments */
        @media (max-width: 992px) {
            .info-grid {
                grid-template-columns: 1fr;
            }

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

            .info-item {
                padding: 10px 0;
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
    <script>
        $(function() {
            // Initialize Bootstrap tooltips
            $('[data-toggle="tooltip"]').tooltip();

            // Confirm submit for Close Request with toast feedback
            $(document).on('submit', 'form.confirm-submit', function(e) {
                const form = this;
                if (form.dataset.submitting === 'true') return;
                e.preventDefault();
                const message = form.getAttribute('data-confirm-message') ||
                    'Submit? Data cannot be edited after submission.';
                const proceed = () => {
                    form.dataset.submitting = 'true';
                    if (typeof toast_ === 'function') toast_('info', 'Submitting...');
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
            // Search candidate functionality
            $('#search_candidate').click(function() {
                searchCandidates();
            });

            $('#candidate_search').keypress(function(e) {
                if (e.which == 13) {
                    searchCandidates();
                }
            });

            function searchCandidates() {
                var query = $('#candidate_search').val();
                if (query.length < 3) {
                    alert('Please enter at least 3 characters to search');
                    return;
                }

                $.ajax({
                    url: '{{ route('recruitment.candidates.search') }}',
                    type: 'GET',
                    data: {
                        query: query
                    },
                    success: function(response) {
                        displaySearchResults(response.candidates);
                    },
                    error: function() {
                        alert('Error searching candidates');
                    }
                });
            }

            function displaySearchResults(candidates) {
                var tbody = $('#candidate_results');
                tbody.empty();

                if (candidates.length === 0) {
                    tbody.append(
                        '<tr><td colspan="5" class="text-center text-muted">No candidates found</td></tr>');
                } else {
                    candidates.forEach(function(candidate) {
                        var row = '<tr>' +
                            '<td>' + candidate.name + '</td>' +
                            '<td>' + candidate.email + '</td>' +
                            '<td>' + candidate.phone + '</td>' +
                            '<td>' + (candidate.position_applied || '-') + '</td>' +
                            '<td>' +
                            '<button class="btn btn-sm btn-primary add-candidate-btn" data-candidate-id="' +
                            candidate.id + '">' +
                            '<i class="fas fa-plus"></i> Add</button>' +
                            '</td>' +
                            '</tr>';
                        tbody.append(row);
                    });
                }

                $('#search_results').show();
            }

            // Handle delete session modal
            $(document).on('click', '.delete-session-btn', function() {
                var sessionId = $(this).data('session-id');
                var candidateName = $(this).data('candidate-name');

                // Update modal content
                $('#candidateNameToDelete').text(candidateName);
                $('#deleteSessionForm').attr('action', '{{ route('recruitment.sessions.destroy', '') }}/' +
                    sessionId);
            });

            // Handle delete session form submission
            $('#deleteSessionForm').on('submit', function(e) {
                e.preventDefault();

                var form = $(this);
                var url = form.attr('action');

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: form.serialize(),
                    success: function(response) {
                        if (response.success) {
                            $('#deleteSessionModal').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message,
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'OK'
                            }).then((result) => {
                                // Reload the page to show updated data
                                location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message,
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function(xhr) {
                        var message = 'Error removing candidate from session';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: message,
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            });

            // Add candidate to FPTK from search results
            $(document).on('click', '.add-candidate-btn[data-candidate-id]', function() {
                var candidateId = $(this).data('candidate-id');
                var fptkId = '{{ $fptk->id }}';

                // Add candidate to FPTK
                $.ajax({
                    url: '{{ route('recruitment.sessions.store') }}',
                    type: 'POST',
                    data: {
                        candidate_id: candidateId,
                        fptk_id: fptkId,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#addCandidateModal').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message,
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'OK'
                            });
                            // Reload the page to show updated data
                            location.reload();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message,
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'OK'
                            });
                        }
                    },
                    error: function(xhr) {
                        var message = 'Error adding candidate to FPTK';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: message,
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'OK'
                        });
                    }
                });
            });
        });
    </script>
@endsection
