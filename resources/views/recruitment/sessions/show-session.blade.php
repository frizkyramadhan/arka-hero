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
                            <div class="timeline-horizontal">
                                <!-- CV Review -->
                                <div class="timeline-item" data-toggle="modal" data-target="#cvReviewModal">
                                    <div
                                        class="timeline-marker {{ $session->current_stage === 'cv_review' ? ($session->stage_status === 'completed' ? 'bg-success' : ($session->stage_status === 'failed' ? 'bg-danger' : 'bg-warning')) : ($session->getProgressPercentage() >= 10 ? 'bg-success' : 'bg-secondary') }}">
                                        <i class="fas fa-file-alt"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="timeline-title">CV Review</div>
                                        <div class="timeline-date">
                                            @if ($session->current_stage === 'cv_review' && $session->stage_started_at)
                                                {{ date('d M Y', strtotime($session->stage_started_at)) }}
                                            @elseif($session->getProgressPercentage() >= 10)
                                                {{ date('d M Y', strtotime($session->stage_completed_at ?? now())) }}
                                            @else
                                                -
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Psikotes -->
                                <div class="timeline-item" data-toggle="modal" data-target="#psikotesModal">
                                    <div
                                        class="timeline-marker {{ $session->current_stage === 'psikotes' ? ($session->stage_status === 'completed' ? 'bg-success' : ($session->stage_status === 'failed' ? 'bg-danger' : 'bg-warning')) : ($session->getProgressPercentage() >= 20 ? 'bg-success' : 'bg-secondary') }}">
                                        <i class="fas fa-brain"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="timeline-title">Psikotes</div>
                                        <div class="timeline-date">
                                            @if ($session->current_stage === 'psikotes' && $session->stage_started_at)
                                                {{ date('d M Y', strtotime($session->stage_started_at)) }}
                                            @elseif($session->getProgressPercentage() >= 20)
                                                {{ date('d M Y', strtotime($session->stage_completed_at ?? now())) }}
                                            @else
                                                -
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Tes Teori -->
                                <div class="timeline-item" data-toggle="modal" data-target="#tesTeoriModal">
                                    <div
                                        class="timeline-marker {{ $session->current_stage === 'tes_teori' ? ($session->stage_status === 'completed' ? 'bg-success' : ($session->stage_status === 'failed' ? 'bg-danger' : 'bg-warning')) : ($session->getProgressPercentage() >= 30 ? 'bg-success' : 'bg-secondary') }}">
                                        <i class="fas fa-book"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="timeline-title">Tes Teori</div>
                                        <div class="timeline-date">
                                            @if ($session->current_stage === 'tes_teori' && $session->stage_started_at)
                                                {{ date('d M Y', strtotime($session->stage_started_at)) }}
                                            @elseif($session->getProgressPercentage() >= 30)
                                                {{ date('d M Y', strtotime($session->stage_completed_at ?? now())) }}
                                            @else
                                                -
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Interview HR -->
                                <div class="timeline-item" data-toggle="modal" data-target="#interviewHrModal">
                                    <div
                                        class="timeline-marker {{ $session->current_stage === 'interview_hr' ? ($session->stage_status === 'completed' ? 'bg-success' : ($session->stage_status === 'failed' ? 'bg-danger' : 'bg-warning')) : ($session->getProgressPercentage() >= 40 ? 'bg-success' : 'bg-secondary') }}">
                                        <i class="fas fa-user-tie"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="timeline-title">Interview HR</div>
                                        <div class="timeline-date">
                                            @if ($session->current_stage === 'interview_hr' && $session->stage_started_at)
                                                {{ date('d M Y', strtotime($session->stage_started_at)) }}
                                            @elseif($session->getProgressPercentage() >= 40)
                                                {{ date('d M Y', strtotime($session->stage_completed_at ?? now())) }}
                                            @else
                                                -
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Interview User -->
                                <div class="timeline-item" data-toggle="modal" data-target="#interviewUserModal">
                                    <div
                                        class="timeline-marker {{ $session->current_stage === 'interview_user' ? ($session->stage_status === 'completed' ? 'bg-success' : ($session->stage_status === 'failed' ? 'bg-danger' : 'bg-warning')) : ($session->getProgressPercentage() >= 50 ? 'bg-success' : 'bg-secondary') }}">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="timeline-title">Interview User</div>
                                        <div class="timeline-date">
                                            @if ($session->current_stage === 'interview_user' && $session->stage_started_at)
                                                {{ date('d M Y', strtotime($session->stage_started_at)) }}
                                            @elseif($session->getProgressPercentage() >= 50)
                                                {{ date('d M Y', strtotime($session->stage_completed_at ?? now())) }}
                                            @else
                                                -
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Offering -->
                                <div class="timeline-item" data-toggle="modal" data-target="#offeringModal">
                                    <div
                                        class="timeline-marker {{ $session->current_stage === 'offering' ? ($session->stage_status === 'completed' ? 'bg-success' : ($session->stage_status === 'failed' ? 'bg-danger' : 'bg-warning')) : ($session->getProgressPercentage() >= 60 ? 'bg-success' : 'bg-secondary') }}">
                                        <i class="fas fa-handshake"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="timeline-title">Offering</div>
                                        <div class="timeline-date">
                                            @if ($session->current_stage === 'offering' && $session->stage_started_at)
                                                {{ date('d M Y', strtotime($session->stage_started_at)) }}
                                            @elseif($session->getProgressPercentage() >= 60)
                                                {{ date('d M Y', strtotime($session->stage_completed_at ?? now())) }}
                                            @else
                                                -
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- MCU -->
                                <div class="timeline-item" data-toggle="modal" data-target="#mcuModal">
                                    <div
                                        class="timeline-marker {{ $session->current_stage === 'mcu' ? ($session->stage_status === 'completed' ? 'bg-success' : ($session->stage_status === 'failed' ? 'bg-danger' : 'bg-warning')) : ($session->getProgressPercentage() >= 70 ? 'bg-success' : 'bg-secondary') }}">
                                        <i class="fas fa-user-md"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="timeline-title">MCU</div>
                                        <div class="timeline-date">
                                            @if ($session->current_stage === 'mcu' && $session->stage_started_at)
                                                {{ date('d M Y', strtotime($session->stage_started_at)) }}
                                            @elseif($session->getProgressPercentage() >= 70)
                                                {{ date('d M Y', strtotime($session->stage_completed_at ?? now())) }}
                                            @else
                                                -
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Hire -->
                                <div class="timeline-item" data-toggle="modal" data-target="#hireModal">
                                    <div
                                        class="timeline-marker {{ $session->current_stage === 'hire' ? ($session->stage_status === 'completed' ? 'bg-success' : ($session->stage_status === 'failed' ? 'bg-danger' : 'bg-warning')) : ($session->getProgressPercentage() >= 80 ? 'bg-success' : 'bg-secondary') }}">
                                        <i class="fas fa-user-check"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="timeline-title">Hire</div>
                                        <div class="timeline-date">
                                            @if ($session->current_stage === 'hire' && $session->stage_started_at)
                                                {{ date('d M Y', strtotime($session->stage_started_at)) }}
                                            @elseif($session->getProgressPercentage() >= 80)
                                                {{ date('d M Y', strtotime($session->stage_completed_at ?? now())) }}
                                            @else
                                                -
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <!-- Onboarding -->
                                <div class="timeline-item" data-toggle="modal" data-target="#onboardingModal">
                                    <div
                                        class="timeline-marker {{ $session->current_stage === 'onboarding' ? ($session->stage_status === 'completed' ? 'bg-success' : ($session->stage_status === 'failed' ? 'bg-danger' : 'bg-warning')) : ($session->getProgressPercentage() >= 90 ? 'bg-success' : 'bg-secondary') }}">
                                        <i class="fas fa-graduation-cap"></i>
                                    </div>
                                    <div class="timeline-content">
                                        <div class="timeline-title">Onboarding</div>
                                        <div class="timeline-date">
                                            @if ($session->current_stage === 'onboarding' && $session->stage_started_at)
                                                {{ date('d M Y', strtotime($session->stage_started_at)) }}
                                            @elseif($session->getProgressPercentage() >= 90)
                                                {{ date('d M Y', strtotime($session->stage_completed_at ?? now())) }}
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
                                            {{ ucfirst(str_replace('_', ' ', $session->current_stage)) }}</div>
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

                    <!-- Assessments -->
                    @if ($session->assessments && $session->assessments->count() > 0)
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
                                                <th>Type</th>
                                                <th>Date</th>
                                                <th>Status</th>
                                                <th>Score</th>
                                                <th>Notes</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($session->assessments as $assessment)
                                                <tr>
                                                    <td>{{ ucfirst(str_replace('_', ' ', $assessment->stage)) }}</td>
                                                    <td>{{ ucfirst(str_replace('_', ' ', $assessment->type)) }}</td>
                                                    <td>{{ $assessment->scheduled_date ? date('d M Y', strtotime($assessment->scheduled_date)) : 'N/A' }}
                                                    </td>
                                                    <td>
                                                        @php
                                                            $statusMap = [
                                                                'scheduled' =>
                                                                    '<span class="badge badge-info">Scheduled</span>',
                                                                'completed' =>
                                                                    '<span class="badge badge-success">Completed</span>',
                                                                'cancelled' =>
                                                                    '<span class="badge badge-warning">Cancelled</span>',
                                                                'failed' =>
                                                                    '<span class="badge badge-danger">Failed</span>',
                                                            ];
                                                        @endphp
                                                        {!! $statusMap[$assessment->status] ??
                                                            '<span class="badge badge-secondary">' . ucfirst($assessment->status) . '</span>' !!}
                                                    </td>
                                                    <td>{{ $assessment->score ?? 'N/A' }}</td>
                                                    <td>{{ Str::limit($assessment->notes, 50) ?? 'N/A' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
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
                                @if ($session->current_stage === 'psikotes' && $session->stage_status === 'pending')
                                    <button type="button" class="btn-action psikotes-btn" data-toggle="modal"
                                        data-target="#psikotesModal">
                                        <i class="fas fa-brain"></i> Psikotes Assessment
                                    </button>
                                @elseif ($session->canAdvanceToNextStage())
                                    <button type="button" class="btn-action advance-btn" data-toggle="modal"
                                        data-target="#advanceModal">
                                        <i class="fas fa-arrow-right"></i> Advance to Next Stage
                                    </button>
                                @endif
                                <button type="button" class="btn-action reject-btn" data-toggle="modal"
                                    data-target="#rejectModal">
                                    <i class="fas fa-times"></i> Reject Session
                                </button>
                                <button type="button" class="btn-action complete-btn" data-toggle="modal"
                                    data-target="#completeModal">
                                    <i class="fas fa-check-double"></i> Complete Session
                                </button>
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
                                            {{ ucfirst(str_replace('_', ' ', $session->current_stage)) }}</div>
                                    </div>
                                    <div class="progress-detail-item">
                                        <div class="detail-label">Stage Status</div>
                                        <div class="detail-value">{{ ucfirst($session->stage_status) }}</div>
                                    </div>
                                    <div class="progress-detail-item">
                                        <div class="detail-label">Final Status</div>
                                        <div class="detail-value">{{ ucfirst($session->status) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    @include('recruitment.sessions.partials.modals')
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
        }

        .timeline-marker.completed {
            background: #28a745;
        }

        .timeline-marker.active {
            background: #ffc107;
        }

        .timeline-marker.pending {
            background: #6c757d;
        }

        .timeline-marker.failed {
            background: #dc3545;
        }

        /* AdminLTE Color Classes */
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

        .reject-btn {
            background-color: #dc3545;
        }

        .reject-btn:hover {
            background-color: #c82333;
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
            gap: 10px;
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

        /* Timeline Modal Styles */
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
            // Initialize tooltips
            $('[data-toggle="tooltip"]').tooltip();

            // Timeline item click animation
            $('.timeline-item').click(function() {
                $(this).addClass('clicked');
                setTimeout(() => {
                    $(this).removeClass('clicked');
                }, 200);
            });

            // CV Review Modal Handlers
            $('#cvPassBtn').click(function() {
                handleCVReviewDecision('pass');
            });

            $('#cvFailBtn').click(function() {
                handleCVReviewDecision('fail');
            });

            // Psikotes Modal Handlers
            $('#psikotes_online_score').on('input', function() {
                validatePsikotesOnline();
            });

            $('#psikotes_offline_score').on('input', function() {
                validatePsikotesOffline();
            });

            // Psikotes Form
            $('#psikotesForm').submit(function(e) {
                e.preventDefault();
                handlePsikotesSubmission();
            });

            // Tes Teori Form
            $('#tesTeoriForm').submit(function(e) {
                e.preventDefault();
                handleTesTeoriSubmission();
            });

            // Interview HR Form
            $('#interviewHrForm').submit(function(e) {
                e.preventDefault();
                handleInterviewHrSubmission();
            });

            // Interview User Form
            $('#interviewUserForm').submit(function(e) {
                e.preventDefault();
                handleInterviewUserSubmission();
            });

            // MCU Form
            $('#mcuForm').submit(function(e) {
                e.preventDefault();
                handleMcuSubmission();
            });

            // Offering Form
            $('#offeringForm').submit(function(e) {
                e.preventDefault();
                handleOfferingSubmission();
            });

            // Hire Form
            $('#hireForm').submit(function(e) {
                e.preventDefault();
                handleHireSubmission();
            });

            // Onboarding Form
            $('#onboardingForm').submit(function(e) {
                e.preventDefault();
                handleOnboardingSubmission();
            });

            // Form handlers
            $('#advanceForm').submit(function(e) {
                e.preventDefault();
                handleAdvanceStage();
            });

            $('#rejectForm').submit(function(e) {
                e.preventDefault();
                handleRejectSession();
            });

            $('#completeForm').submit(function(e) {
                e.preventDefault();
                handleCompleteSession();
            });

            $('#cancelForm').submit(function(e) {
                e.preventDefault();
                handleCancelSession();
            });
        });

        function handleCVReviewDecision(decision) {
            const sessionId = '{{ $session->id }}';
            const notes = decision === 'pass' ? 'CV Review: Passed' : 'CV Review: Failed';

            $.ajax({
                url: '{{ route('recruitment.sessions.advance-stage', ':id') }}'.replace(':id', sessionId),
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    notes: notes,
                    assessment_data: {
                        decision: decision,
                        stage: 'cv_review'
                    }
                },
                success: function(response) {
                    if (response.success) {
                        $('#cvReviewModal').modal('hide');
                        toast_success(response.message);
                        setTimeout(() => {
                            if (response.session_ended) {
                                window.location.href = response.redirect;
                            } else if (response.auto_advanced) {
                                window.location.reload();
                            } else {
                                window.location.reload();
                            }
                        }, 1500);
                    } else {
                        toast_error(response.message || 'Failed to process CV review');
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    toast_error(response?.message || 'An error occurred while processing CV review');
                }
            });
        }

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

        function handlePsikotesSubmission() {
            const sessionId = '{{ $session->id }}';
            const onlineScore = parseFloat($('#psikotes_online_score').val());
            const offlineScore = parseFloat($('#psikotes_offline_score').val());
            const notes = $('#psikotes_notes').val();

            // Validate that at least one score is provided
            if (isNaN(onlineScore) && isNaN(offlineScore)) {
                toast_error('Please provide at least one score (online or offline)');
                return;
            }

            // Determine overall result
            let overallResult = 'pass';
            let resultDetails = [];

            if (!isNaN(onlineScore)) {
                if (onlineScore >= 40) {
                    resultDetails.push('Online: Pass');
                } else {
                    resultDetails.push('Online: Fail');
                    overallResult = 'fail';
                }
            }

            if (!isNaN(offlineScore)) {
                if (offlineScore >= 8) {
                    resultDetails.push('Offline: Pass');
                } else {
                    resultDetails.push('Offline: Fail');
                    overallResult = 'fail';
                }
            }

            const assessmentData = {
                online_score: onlineScore,
                offline_score: offlineScore,
                overall_result: overallResult,
                result_details: resultDetails.join(', '),
                stage: 'psikotes'
            };

            $.ajax({
                url: `/recruitment/sessions/${sessionId}/advance-stage`,
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    notes: notes,
                    assessment_data: JSON.stringify(assessmentData)
                },
                success: function(response) {
                    if (response.success) {
                        $('#psikotesModal').modal('hide');
                        toast_success(response.message);
                        setTimeout(() => {
                            if (response.session_ended) {
                                window.location.href = response.redirect;
                            } else {
                                window.location.reload();
                            }
                        }, 1500);
                    } else {
                        toast_error(response.message || 'Failed to submit psikotes assessment');
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    toast_error(response?.message || 'An error occurred while submitting assessment');
                }
            });
        }

        function handleAdvanceStage() {
            const sessionId = '{{ $session->id }}';
            const notes = $('#advance_notes').val();

            $.ajax({
                url: '{{ route('recruitment.sessions.advance-stage', ':id') }}'.replace(':id', sessionId),
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    notes: notes
                },
                success: function(response) {
                    if (response.success) {
                        $('#advanceModal').modal('hide');
                        toast_success(response.message);
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        toast_error(response.message || 'Failed to advance stage');
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    toast_error(response?.message || 'An error occurred while advancing stage');
                }
            });
        }

        function handleRejectSession() {
            const sessionId = '{{ $session->id }}';
            const reason = $('#reject_reason').val();

            if (!reason.trim()) {
                toast_error('Please provide a rejection reason');
                return;
            }

            $.ajax({
                url: '{{ route('recruitment.sessions.reject', ':id') }}'.replace(':id', sessionId),
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    rejection_reason: reason
                },
                success: function(response) {
                    if (response.success) {
                        $('#rejectModal').modal('hide');
                        toast_success(response.message);
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        toast_error(response.message || 'Failed to reject session');
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    toast_error(response?.message || 'An error occurred while rejecting session');
                }
            });
        }

        function handleCompleteSession() {
            const sessionId = '{{ $session->id }}';
            const hireDate = $('#hire_date').val();
            const employeeId = $('#employee_id').val();
            const notes = $('#complete_notes').val();

            if (!hireDate) {
                toast_error('Please provide a hire date');
                return;
            }

            $.ajax({
                url: '{{ route('recruitment.sessions.complete', ':id') }}'.replace(':id', sessionId),
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    hire_date: hireDate,
                    employee_id: employeeId,
                    notes: notes
                },
                success: function(response) {
                    if (response.success) {
                        $('#completeModal').modal('hide');
                        toast_success(response.message);
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        toast_error(response.message || 'Failed to complete session');
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    toast_error(response?.message || 'An error occurred while completing session');
                }
            });
        }

        function handleCancelSession() {
            const sessionId = '{{ $session->id }}';
            const reason = $('#cancel_reason').val();

            if (!reason.trim()) {
                toast_error('Please provide a cancellation reason');
                return;
            }

            $.ajax({
                url: '{{ route('recruitment.sessions.cancel', ':id') }}'.replace(':id', sessionId),
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    cancel_reason: reason
                },
                success: function(response) {
                    if (response.success) {
                        $('#cancelModal').modal('hide');
                        toast_success(response.message);
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        toast_error(response.message || 'Failed to cancel session');
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    toast_error(response?.message || 'An error occurred while cancelling session');
                }
            });
        }

        // Tes Teori Modal Handlers
        $('#tesTeoriSubmitBtn').click(function() {
            handleTesTeoriSubmission();
        });

        function handleTesTeoriSubmission() {
            const sessionId = '{{ $session->id }}';
            const score = parseFloat($('#tes_teori_score').val());
            const duration = parseInt($('#tes_teori_duration').val());
            const notes = $('#tes_teori_notes').val();

            if (isNaN(score)) {
                toast_error('Please provide a valid score');
                return;
            }

            const result = score >= 75 ? 'pass' : 'fail';
            const assessmentData = {
                score: score,
                duration: duration,
                result: result,
                stage: 'tes_teori'
            };

            $.ajax({
                url: '{{ route('recruitment.sessions.advance-stage', ':id') }}'.replace(':id', sessionId),
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    notes: notes,
                    assessment_data: JSON.stringify(assessmentData)
                },
                success: function(response) {
                    if (response.success) {
                        $('#tesTeoriModal').modal('hide');
                        toast_success(response.message);
                        setTimeout(() => {
                            if (response.session_ended) {
                                window.location.href = response.redirect;
                            } else if (response.auto_advanced) {
                                window.location.reload();
                            } else {
                                window.location.reload();
                            }
                        }, 1500);
                    } else {
                        toast_error(response.message || 'Failed to submit tes teori assessment');
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    toast_error(response?.message || 'An error occurred while submitting assessment');
                }
            });
        }

        // Interview HR Modal Handlers
        $('#interviewHrSubmitBtn').click(function() {
            handleInterviewHrSubmission();
        });

        // Calculate overall score for Interview HR
        $('#interview_hr_communication, #interview_hr_attitude, #interview_hr_cultural_fit').on('input', function() {
            calculateInterviewHrOverall();
        });

        function calculateInterviewHrOverall() {
            const communication = parseFloat($('#interview_hr_communication').val()) || 0;
            const attitude = parseFloat($('#interview_hr_attitude').val()) || 0;
            const culturalFit = parseFloat($('#interview_hr_cultural_fit').val()) || 0;

            const overall = ((communication + attitude + culturalFit) / 3) * 10;
            $('#interview_hr_overall').val(overall.toFixed(2));
        }

        function handleInterviewHrSubmission() {
            const sessionId = '{{ $session->id }}';
            const communication = parseFloat($('#interview_hr_communication').val());
            const attitude = parseFloat($('#interview_hr_attitude').val());
            const culturalFit = parseFloat($('#interview_hr_cultural_fit').val());
            const overall = parseFloat($('#interview_hr_overall').val());
            const notes = $('#interview_hr_notes').val();

            if (isNaN(communication) || isNaN(attitude) || isNaN(culturalFit)) {
                toast_error('Please provide all scores');
                return;
            }

            const result = overall >= 70 ? 'pass' : 'fail';
            const assessmentData = {
                communication: communication,
                attitude: attitude,
                cultural_fit: culturalFit,
                overall: overall,
                result: result,
                stage: 'interview_hr'
            };

            $.ajax({
                url: '{{ route('recruitment.sessions.advance-stage', ':id') }}'.replace(':id', sessionId),
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    notes: notes,
                    assessment_data: JSON.stringify(assessmentData)
                },
                success: function(response) {
                    if (response.success) {
                        $('#interviewHrModal').modal('hide');
                        toast_success(response.message);
                        setTimeout(() => {
                            if (response.session_ended) {
                                window.location.href = response.redirect;
                            } else if (response.auto_advanced) {
                                window.location.reload();
                            } else {
                                window.location.reload();
                            }
                        }, 1500);
                    } else {
                        toast_error(response.message || 'Failed to submit interview HR assessment');
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    toast_error(response?.message || 'An error occurred while submitting assessment');
                }
            });
        }

        // Interview User Modal Handlers
        $('#interviewUserSubmitBtn').click(function() {
            handleInterviewUserSubmission();
        });

        // Calculate overall score for Interview User
        $('#interview_user_technical, #interview_user_experience, #interview_user_problem_solving').on('input', function() {
            calculateInterviewUserOverall();
        });

        function calculateInterviewUserOverall() {
            const technical = parseFloat($('#interview_user_technical').val()) || 0;
            const experience = parseFloat($('#interview_user_experience').val()) || 0;
            const problemSolving = parseFloat($('#interview_user_problem_solving').val()) || 0;

            const overall = ((technical + experience + problemSolving) / 3) * 10;
            $('#interview_user_overall').val(overall.toFixed(2));
        }

        function handleInterviewUserSubmission() {
            const sessionId = '{{ $session->id }}';
            const technical = parseFloat($('#interview_user_technical').val());
            const experience = parseFloat($('#interview_user_experience').val());
            const problemSolving = parseFloat($('#interview_user_problem_solving').val());
            const overall = parseFloat($('#interview_user_overall').val());
            const notes = $('#interview_user_notes').val();

            if (isNaN(technical) || isNaN(experience) || isNaN(problemSolving)) {
                toast_error('Please provide all scores');
                return;
            }

            const result = overall >= 75 ? 'pass' : 'fail';
            const assessmentData = {
                technical: technical,
                experience: experience,
                problem_solving: problemSolving,
                overall: overall,
                result: result,
                stage: 'interview_user'
            };

            $.ajax({
                url: '{{ route('recruitment.sessions.advance-stage', ':id') }}'.replace(':id', sessionId),
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    notes: notes,
                    assessment_data: JSON.stringify(assessmentData)
                },
                success: function(response) {
                    if (response.success) {
                        $('#interviewUserModal').modal('hide');
                        toast_success(response.message);
                        setTimeout(() => {
                            if (response.session_ended) {
                                window.location.href = response.redirect;
                            } else if (response.auto_advanced) {
                                window.location.reload();
                            } else {
                                window.location.reload();
                            }
                        }, 1500);
                    } else {
                        toast_error(response.message || 'Failed to submit interview user assessment');
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    toast_error(response?.message || 'An error occurred while submitting assessment');
                }
            });
        }

        // MCU Modal Handlers
        $('#mcuSubmitBtn').click(function() {
            handleMcuSubmission();
        });

        function handleMcuSubmission() {
            const sessionId = '{{ $session->id }}';
            const bloodPressure = $('#mcu_blood_pressure').val();
            const heartRate = parseInt($('#mcu_heart_rate').val());
            const bloodSugar = parseFloat($('#mcu_blood_sugar').val());
            const overallHealth = $('#mcu_overall_health').val();
            const notes = $('#mcu_notes').val();

            if (!overallHealth) {
                toast_error('Please select overall health condition');
                return;
            }

            let result = 'pass';
            if (overallHealth === 'unfit') {
                result = 'fail';
            } else if (overallHealth === 'conditional') {
                result = 'conditional';
            }

            const assessmentData = {
                blood_pressure: bloodPressure,
                heart_rate: heartRate,
                blood_sugar: bloodSugar,
                overall_health: overallHealth,
                result: result,
                stage: 'mcu'
            };

            $.ajax({
                url: '{{ route('recruitment.sessions.advance-stage', ':id') }}'.replace(':id', sessionId),
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    notes: notes,
                    assessment_data: JSON.stringify(assessmentData)
                },
                success: function(response) {
                    if (response.success) {
                        $('#mcuModal').modal('hide');
                        toast_success(response.message);
                        setTimeout(() => {
                            if (response.session_ended) {
                                window.location.href = response.redirect;
                            } else if (response.auto_advanced) {
                                window.location.reload();
                            } else {
                                window.location.reload();
                            }
                        }, 1500);
                    } else {
                        toast_error(response.message || 'Failed to submit MCU assessment');
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    toast_error(response?.message || 'An error occurred while submitting assessment');
                }
            });
        }

        // Offering Modal Handlers
        $('#offeringSubmitBtn').click(function() {
            handleOfferingSubmission();
        });

        function handleOfferingSubmission() {
            const sessionId = '{{ $session->id }}';
            const salary = $('#offering_salary').val();
            const position = $('#offering_position').val();
            const startDate = $('#offering_start_date').val();
            const status = $('#offering_status').val();
            const notes = $('#offering_notes').val();

            if (!status) {
                toast_error('Please select offering status');
                return;
            }

            const assessmentData = {
                salary: salary,
                position: position,
                start_date: startDate,
                status: status,
                stage: 'offering'
            };

            $.ajax({
                url: '{{ route('recruitment.sessions.advance-stage', ':id') }}'.replace(':id', sessionId),
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    notes: notes,
                    assessment_data: JSON.stringify(assessmentData)
                },
                success: function(response) {
                    if (response.success) {
                        $('#offeringModal').modal('hide');
                        toast_success(response.message);
                        setTimeout(() => {
                            if (response.session_ended) {
                                window.location.href = response.redirect;
                            } else if (response.auto_advanced) {
                                window.location.reload();
                            } else {
                                window.location.reload();
                            }
                        }, 1500);
                    } else {
                        toast_error(response.message || 'Failed to submit offering');
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    toast_error(response?.message || 'An error occurred while submitting offering');
                }
            });
        }

        // Hire Modal Handlers
        $('#hireSubmitBtn').click(function() {
            handleHireSubmission();
        });

        function handleHireSubmission() {
            const sessionId = '{{ $session->id }}';
            const hireDate = $('#hire_date').val();
            const employeeId = $('#hire_employee_id').val();
            const contractType = $('#hire_contract_type').val();
            const department = $('#hire_department').val();
            const position = $('#hire_position').val();
            const notes = $('#hire_notes').val();

            if (!hireDate) {
                toast_error('Please provide hire date');
                return;
            }

            const assessmentData = {
                hire_date: hireDate,
                employee_id: employeeId,
                contract_type: contractType,
                department: department,
                position: position,
                stage: 'hire'
            };

            $.ajax({
                url: '{{ route('recruitment.sessions.advance-stage', ':id') }}'.replace(':id', sessionId),
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    notes: notes,
                    assessment_data: JSON.stringify(assessmentData)
                },
                success: function(response) {
                    if (response.success) {
                        $('#hireModal').modal('hide');
                        toast_success(response.message);
                        setTimeout(() => {
                            if (response.session_ended) {
                                window.location.href = response.redirect;
                            } else if (response.auto_advanced) {
                                window.location.reload();
                            } else {
                                window.location.reload();
                            }
                        }, 1500);
                    } else {
                        toast_error(response.message || 'Failed to submit hire');
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    toast_error(response?.message || 'An error occurred while submitting hire');
                }
            });
        }

        // Onboarding Modal Handlers
        $('#onboardingSubmitBtn').click(function() {
            handleOnboardingSubmission();
        });

        // Interview HR calculation
        function calculateInterviewHrOverall() {
            const communication = parseFloat($('#interview_hr_communication').val()) || 0;
            const attitude = parseFloat($('#interview_hr_attitude').val()) || 0;
            const culturalFit = parseFloat($('#interview_hr_cultural_fit').val()) || 0;

            const overall = ((communication + attitude + culturalFit) / 3) * 10;
            $('#interview_hr_overall').val(overall.toFixed(2));
        }

        // Interview User calculation
        function calculateInterviewUserOverall() {
            const technical = parseFloat($('#interview_user_technical').val()) || 0;
            const experience = parseFloat($('#interview_user_experience').val()) || 0;
            const problemSolving = parseFloat($('#interview_user_problem_solving').val()) || 0;

            const overall = ((technical + experience + problemSolving) / 3) * 10;
            $('#interview_user_overall').val(overall.toFixed(2));
        }

        // Add event listeners for calculation
        $('#interview_hr_communication, #interview_hr_attitude, #interview_hr_cultural_fit').on('input', function() {
            calculateInterviewHrOverall();
        });

        $('#interview_user_technical, #interview_user_experience, #interview_user_problem_solving').on('input', function() {
            calculateInterviewUserOverall();
        });

        function handleOnboardingSubmission() {
            const sessionId = '{{ $session->id }}';
            const startDate = $('#onboarding_start_date').val();
            const duration = $('#onboarding_duration').val();
            const mentor = $('#onboarding_mentor').val();
            const status = $('#onboarding_status').val();
            const notes = $('#onboarding_notes').val();

            if (!status) {
                toast_error('Please select onboarding status');
                return;
            }

            const assessmentData = {
                start_date: startDate,
                duration: duration,
                mentor: mentor,
                status: status,
                stage: 'onboarding'
            };

            $.ajax({
                url: '{{ route('recruitment.sessions.advance-stage', ':id') }}'.replace(':id', sessionId),
                method: 'POST',
                data: {
                    _token: $('meta[name="csrf-token"]').attr('content'),
                    notes: notes,
                    assessment_data: JSON.stringify(assessmentData)
                },
                success: function(response) {
                    if (response.success) {
                        $('#onboardingModal').modal('hide');
                        toast_success(response.message);
                        setTimeout(() => {
                            if (response.session_ended) {
                                window.location.href = response.redirect;
                            } else if (response.session_completed) {
                                window.location.reload();
                            } else {
                                window.location.reload();
                            }
                        }, 1500);
                    } else {
                        toast_error(response.message || 'Failed to submit onboarding');
                    }
                },
                error: function(xhr) {
                    const response = xhr.responseJSON;
                    toast_error(response?.message || 'An error occurred while submitting onboarding');
                }
            });
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

        // Validate if stage can be accessed
        function canAccessStage(stageName) {
            const currentStage = '{{ $session->current_stage }}';
            const stageStatus = '{{ $session->stage_status }}';

            const stageOrder = {
                'cv_review': 1,
                'psikotes': 2,
                'tes_teori': 3,
                'interview_hr': 4,
                'interview_user': 5,
                'offering': 6,
                'mcu': 7,
                'hire': 8,
                'onboarding': 9
            };

            const currentStageOrder = stageOrder[currentStage] || 0;
            const targetStageOrder = stageOrder[stageName] || 0;

            // Can only access current stage or completed stages
            if (targetStageOrder <= currentStageOrder) {
                return true;
            }

            // Cannot access future stages
            return false;
        }

        // Disable/enable timeline items based on current stage
        function updateTimelineAccess() {
            const currentStage = '{{ $session->current_stage }}';
            const stageStatus = '{{ $session->stage_status }}';

            const stageOrder = {
                'cv_review': 1,
                'psikotes': 2,
                'tes_teori': 3,
                'interview_hr': 4,
                'interview_user': 5,
                'offering': 6,
                'mcu': 7,
                'hire': 8,
                'onboarding': 9
            };

            const currentStageOrder = stageOrder[currentStage] || 0;

            // Disable future stages
            Object.keys(stageOrder).forEach(stage => {
                const stageOrderNum = stageOrder[stage];
                const timelineItem = $(`.timeline-item[data-target="#${stage}Modal"]`);

                if (stageOrderNum > currentStageOrder) {
                    timelineItem.addClass('disabled');
                    timelineItem.css('opacity', '0.5');
                    timelineItem.css('cursor', 'not-allowed');
                } else {
                    timelineItem.removeClass('disabled');
                    timelineItem.css('opacity', '1');
                    timelineItem.css('cursor', 'pointer');
                }
            });
        }

        // Initialize timeline access on page load
        $(document).ready(function() {
            updateTimelineAccess();
        });
    </script>
@endsection
