@extends('layouts.main')

@section('content')
    <div class="content-wrapper-custom">
        <div class="fptk-header">
            <div class="fptk-header-content">
                <div class="fptk-project">{{ $fptk->project->project_name }}</div>
                <h1 class="fptk-number">{{ $letterInfo['display_number'] ?? $fptk->request_number }}</h1>
                <div class="fptk-date">
                    <i class="far fa-calendar-alt"></i> {{ date('d F Y', strtotime($fptk->created_at)) }}
                </div>
                <div
                    class="fptk-status-pill {{ $fptk->final_status == 'draft' ? 'status-draft' : ($fptk->final_status == 'submitted' ? 'status-submitted' : ($fptk->final_status == 'pending' ? 'status-pending' : ($fptk->final_status == 'approved' ? 'status-approved' : 'status-rejected'))) }}">
                    <i
                        class="fas {{ $fptk->final_status == 'draft' ? 'fa-edit' : ($fptk->final_status == 'submitted' ? 'fa-paper-plane' : ($fptk->final_status == 'pending' ? 'fa-clock' : ($fptk->final_status == 'approved' ? 'fa-check-circle' : 'fa-times-circle'))) }}"></i>
                    {{ ucfirst($fptk->final_status) }}
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
                                        <i class="fas fa-question-circle"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Request Reason</div>
                                        <div class="info-value">
                                            {{ $fptk->request_reason == 'other' ? $fptk->other_reason : ucfirst(str_replace('_', ' ', $fptk->request_reason)) }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Job Description & Requirements -->
                    <div class="fptk-card requirements-card">
                        <div class="card-head">
                            <h2><i class="fas fa-clipboard-list"></i> Job Description & Requirements</h2>
                        </div>
                        <div class="card-body">
                            <!-- Job Description -->
                            <div class="requirement-section">
                                <div class="section-header">
                                    <div class="section-icon" style="background-color: #3498db;">
                                        <i class="fas fa-clipboard-list"></i>
                                    </div>
                                    <div class="section-title">Job Description</div>
                                </div>
                                <div class="section-content">
                                    {!! nl2br(e($fptk->job_description)) !!}
                                </div>
                            </div>

                            <!-- Basic Requirements Grid -->
                            <div class="requirements-grid">
                                <div class="requirement-item">
                                    <div class="requirement-icon" style="background-color: #3498db;">
                                        <i class="fas fa-venus-mars"></i>
                                    </div>
                                    <div class="requirement-content">
                                        <div class="requirement-label">Gender</div>
                                        <div class="requirement-value">{{ ucfirst($fptk->required_gender) }}</div>
                                    </div>
                                </div>
                                <div class="requirement-item">
                                    <div class="requirement-icon" style="background-color: #e74c3c;">
                                        <i class="fas fa-heart"></i>
                                    </div>
                                    <div class="requirement-content">
                                        <div class="requirement-label">Marital Status</div>
                                        <div class="requirement-value">{{ ucfirst($fptk->required_marital_status) }}</div>
                                    </div>
                                </div>
                                @if ($fptk->required_age_min || $fptk->required_age_max)
                                    <div class="requirement-item">
                                        <div class="requirement-icon" style="background-color: #f1c40f;">
                                            <i class="fas fa-birthday-cake"></i>
                                        </div>
                                        <div class="requirement-content">
                                            <div class="requirement-label">Age Range</div>
                                            <div class="requirement-value">
                                                @if ($fptk->required_age_min && $fptk->required_age_max)
                                                    {{ $fptk->required_age_min }} - {{ $fptk->required_age_max }} years
                                                @elseif($fptk->required_age_min)
                                                    Min {{ $fptk->required_age_min }} years
                                                @elseif($fptk->required_age_max)
                                                    Max {{ $fptk->required_age_max }} years
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endif
                                @if ($fptk->required_education)
                                    <div class="requirement-item">
                                        <div class="requirement-icon" style="background-color: #9b59b6;">
                                            <i class="fas fa-graduation-cap"></i>
                                        </div>
                                        <div class="requirement-content">
                                            <div class="requirement-label">Education</div>
                                            <div class="requirement-value">{{ $fptk->required_education }}</div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Detailed Requirements -->
                            @if (
                                $fptk->required_skills ||
                                    $fptk->required_experience ||
                                    $fptk->required_physical ||
                                    $fptk->required_mental ||
                                    $fptk->other_requirements)
                                <div class="detailed-requirements">
                                    @if ($fptk->required_skills)
                                        <div class="requirement-section">
                                            <div class="section-header">
                                                <div class="section-icon" style="background-color: #e67e22;">
                                                    <i class="fas fa-tools"></i>
                                                </div>
                                                <div class="section-title">Required Skills</div>
                                            </div>
                                            <div class="section-content">{{ $fptk->required_skills }}</div>
                                        </div>
                                    @endif

                                    @if ($fptk->required_experience)
                                        <div class="requirement-section">
                                            <div class="section-header">
                                                <div class="section-icon" style="background-color: #27ae60;">
                                                    <i class="fas fa-briefcase"></i>
                                                </div>
                                                <div class="section-title">Required Experience</div>
                                            </div>
                                            <div class="section-content">{{ $fptk->required_experience }}</div>
                                        </div>
                                    @endif

                                    @if ($fptk->required_physical)
                                        <div class="requirement-section">
                                            <div class="section-header">
                                                <div class="section-icon" style="background-color: #f39c12;">
                                                    <i class="fas fa-dumbbell"></i>
                                                </div>
                                                <div class="section-title">Physical Requirements</div>
                                            </div>
                                            <div class="section-content">{{ $fptk->required_physical }}</div>
                                        </div>
                                    @endif

                                    @if ($fptk->required_mental)
                                        <div class="requirement-section">
                                            <div class="section-header">
                                                <div class="section-icon" style="background-color: #8e44ad;">
                                                    <i class="fas fa-brain"></i>
                                                </div>
                                                <div class="section-title">Mental Requirements</div>
                                            </div>
                                            <div class="section-content">{{ $fptk->required_mental }}</div>
                                        </div>
                                    @endif

                                    @if ($fptk->other_requirements)
                                        <div class="requirement-section">
                                            <div class="section-header">
                                                <div class="section-icon" style="background-color: #34495e;">
                                                    <i class="fas fa-plus-circle"></i>
                                                </div>
                                                <div class="section-title">Other Requirements</div>
                                            </div>
                                            <div class="section-content">{{ $fptk->other_requirements }}</div>
                                        </div>
                                    @endif
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- <!-- Approval Progress Section -->
                    @if ($fptk->approval)
                        <div class="fptk-card approval-progress-card">
                            <div class="card-head">
                                <h2><i class="fas fa-tasks"></i> Approval Progress</h2>
                            </div>
                            <div class="card-body">
                                <!-- Approval Flow Progress -->
                                <div class="approval-flow-progress">
                                    @php
                                        $progress = $fptk->getApprovalProgress();
                                        $timeline = $fptk->getApprovalTimeline();
                                    @endphp

                                    <div class="progress-bar-container">
                                        <div class="progress-bar">
                                            <div class="progress-fill" style="width: {{ $progress['percentage'] }}%">
                                            </div>
                                        </div>
                                        <div class="progress-text">{{ $progress['percentage'] }}% Complete</div>
                                    </div>

                                    <!-- Current Stage Info -->
                                    @if ($progress['current_stage'])
                                        <div class="current-stage-info">
                                            <h4>Current Stage: {{ $progress['current_stage']->stage_name }}</h4>
                                            <p>Stage {{ $progress['completed_stages'] + 1 }} of
                                                {{ $progress['total_stages'] }}</p>

                                            @if ($fptk->isApprovalOverdue())
                                                <div class="alert alert-warning">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                    This approval is overdue and may need escalation.
                                                </div>
                                            @endif
                                        </div>
                                    @endif

                                    <!-- Approval Timeline -->
                                    <div class="approval-timeline">
                                        <h4>Approval Timeline</h4>
                                        <div class="timeline">
                                            @foreach ($timeline as $event)
                                                <div class="timeline-item">
                                                    <div
                                                        class="timeline-marker {{ $event['action'] === 'approved' ? 'approved' : ($event['action'] === 'rejected' ? 'rejected' : 'pending') }}">
                                                        <i
                                                            class="fas fa-{{ $event['action'] === 'approved' ? 'check' : ($event['action'] === 'rejected' ? 'times' : 'clock') }}"></i>
                                                    </div>
                                                    <div class="timeline-content">
                                                        <div class="timeline-header">
                                                            <h6>{{ ucfirst($event['action']) }}</h6>
                                                            <small>{{ $event['date']->format('M d, Y H:i') }}</small>
                                                        </div>
                                                        <div class="timeline-body">
                                                            <p>{{ $event['description'] }}</p>
                                                            @if ($event['user'])
                                                                <small>By: {{ $event['user']->name }}</small>
                                                            @endif
                                                            @if ($event['comments'])
                                                                <div class="comments">
                                                                    <strong>Comments:</strong> {{ $event['comments'] }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <!-- Next Approvers -->
                                    @php
                                        $nextApprovers = $fptk->getNextApprovers();
                                    @endphp
                                    @if (count($nextApprovers) > 0)
                                        <div class="next-approvers">
                                            <h4>Next Approvers</h4>
                                            <div class="approvers-list">
                                                @foreach ($nextApprovers as $approver)
                                                    <div class="approver-item">
                                                        <div class="approver-icon">
                                                            <i class="fas fa-user"></i>
                                                        </div>
                                                        <div class="approver-info">
                                                            <div class="approver-name">{{ $approver['name'] }}</div>
                                                            <div class="approver-type">{{ ucfirst($approver['type']) }}
                                                            </div>
                                                            @if ($approver['is_backup'])
                                                                <span class="badge badge-warning">Backup</span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif --}}

                    <!-- Recruitment Sessions -->
                    @if ($fptk->sessions->isNotEmpty())
                        <div class="fptk-card sessions-card">
                            <div class="card-head">
                                <h2><i class="fas fa-user-graduate"></i> Recruitment Sessions <span
                                        class="sessions-count">{{ $fptk->sessions->count() }}</span></h2>
                            </div>
                            <div class="card-body p-0">
                                <div class="sessions-list">
                                    @foreach ($fptk->sessions as $session)
                                        <div class="session-item">
                                            <div class="session-info">
                                                <div class="session-candidate">{{ $session->candidate->full_name }}</div>
                                                <div class="session-stage">Stage: {{ ucfirst($session->current_stage) }}
                                                </div>
                                                <div class="session-meta">
                                                    <span class="session-email"><i class="fas fa-envelope"></i>
                                                        {{ $session->candidate->email }}</span>
                                                    <span class="session-phone"><i class="fas fa-phone"></i>
                                                        {{ $session->candidate->phone }}</span>
                                                </div>
                                                <div class="session-status">
                                                    <span
                                                        class="status-badge status-{{ $session->status }}">{{ ucfirst($session->status) }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- Right Column -->
                <div class="col-lg-4">
                    <!-- Approval Process -->
                    <div class="fptk-card approval-process-card card-info card-outline elevation-3">
                        <div class="card-head">
                            <h2><i class="fas fa-stream"></i> Approval Hierarchy</h2>
                        </div>
                        <div class="card-body">
                            <div class="approval-flow">
                                <!-- Acknowledged By (HR) -->
                                <div class="approval-step">
                                    <div class="step-icon {{ $fptk->known_status }}">
                                        <i class="fas fa-user-check"></i>
                                    </div>
                                    <div class="step-content">
                                        <div class="step-header">
                                            <h4>Acknowledged By (HR)</h4>
                                            <div class="step-status {{ $fptk->known_status }}">
                                                {{ ucfirst($fptk->known_status) }}</div>
                                        </div>
                                        <div class="step-details">
                                            <div class="step-person">
                                                <i class="fas fa-user"></i>
                                                {{ $fptk->acknowledger->name ?? 'Not assigned' }}
                                            </div>
                                            @if ($fptk->known_at)
                                                <div class="step-date">
                                                    <i class="fas fa-calendar"></i>
                                                    {{ date('d M Y H:i', strtotime($fptk->known_at)) }}
                                                </div>
                                            @endif
                                        </div>
                                        <div class="step-remark">
                                            <blockquote class="remark-text">
                                                @if ($fptk->known_remark)
                                                    {{ $fptk->known_remark }}
                                                @else
                                                    HR&GA Section Head
                                                @endif
                                            </blockquote>
                                        </div>
                                    </div>
                                </div>

                                <!-- Approved By PM -->
                                <div class="approval-step">
                                    <div class="step-icon {{ $fptk->pm_approval_status }}">
                                        <i class="fas fa-user-shield"></i>
                                    </div>
                                    <div class="step-content">
                                        <div class="step-header">
                                            <h4>Approved By (PM)</h4>
                                            <div class="step-status {{ $fptk->pm_approval_status }}">
                                                {{ ucfirst($fptk->pm_approval_status) }}</div>
                                        </div>
                                        <div class="step-details">
                                            <div class="step-person">
                                                <i class="fas fa-user"></i>
                                                {{ $fptk->projectManagerApprover->name ?? 'Not assigned' }}
                                            </div>
                                            @if ($fptk->pm_approved_at)
                                                <div class="step-date">
                                                    <i class="fas fa-calendar"></i>
                                                    {{ date('d M Y H:i', strtotime($fptk->pm_approved_at)) }}
                                                </div>
                                            @endif
                                        </div>
                                        <div class="step-remark">
                                            <blockquote class="remark-text">
                                                @if ($fptk->pm_approval_remark)
                                                    {{ $fptk->pm_approval_remark }}
                                                @else
                                                    Project Manager
                                                @endif
                                            </blockquote>
                                        </div>
                                    </div>
                                </div>

                                <!-- Approved By Director -->
                                <div class="approval-step">
                                    <div class="step-icon {{ $fptk->director_approval_status }}">
                                        <i class="fas fa-crown"></i>
                                    </div>
                                    <div class="step-content">
                                        <div class="step-header">
                                            <h4>Approved By (Director)</h4>
                                            <div class="step-status {{ $fptk->director_approval_status }}">
                                                {{ ucfirst($fptk->director_approval_status) }}</div>
                                        </div>
                                        <div class="step-details">
                                            <div class="step-person">
                                                <i class="fas fa-user"></i>
                                                {{ $fptk->directorApprover->name ?? 'Not assigned' }}
                                            </div>
                                            @if ($fptk->director_approved_at)
                                                <div class="step-date">
                                                    <i class="fas fa-calendar"></i>
                                                    {{ date('d M Y H:i', strtotime($fptk->director_approved_at)) }}
                                                </div>
                                            @endif
                                        </div>
                                        <div class="step-remark">
                                            <blockquote class="remark-text">
                                                @if ($fptk->director_approval_remark)
                                                    {{ $fptk->director_approval_remark }}
                                                @else
                                                    Director/Manager
                                                @endif
                                            </blockquote>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Requested By -->
                    <div class="fptk-card requester-card">
                        <div class="card-head">
                            <h2><i class="fas fa-user-edit"></i> Requested By</h2>
                        </div>
                        <div class="card-body">
                            <div class="requester-info">
                                <div class="requester-name">{{ $fptk->createdBy->name }}</div>
                                <div class="requester-email">{{ $fptk->createdBy->email }}</div>
                                <div class="requester-date">
                                    <i class="fas fa-calendar"></i> {{ date('d F Y H:i', strtotime($fptk->created_at)) }}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="fptk-action-buttons">
                        <a href="{{ route('recruitment.requests.index') }}" class="btn-action back-btn">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>

                        @if ($fptk->final_status != 'rejected' && $fptk->final_status != 'cancelled')
                            @if ($fptk->final_status == 'draft')
                                @can('recruitment-requests.edit')
                                    <a href="{{ route('recruitment.requests.edit', $fptk->id) }}"
                                        class="btn-action edit-btn">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                @endcan

                                @can('recruitment-requests.delete')
                                    <button type="button" class="btn-action delete-btn" data-toggle="modal"
                                        data-target="#deleteModal">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                @endcan

                                <!-- Submit button -->
                                @can('recruitment-requests.submit')
                                    <form action="{{ route('recruitment.requests.submit', $fptk->id) }}" method="post"
                                        onsubmit="return confirm('Are you sure you want to submit this FPTK for approval?')">
                                        @csrf
                                        <button type="submit" class="btn-action submit-btn">
                                            <i class="fas fa-paper-plane"></i> Submit for Approval
                                        </button>
                                    </form>
                                @endcan
                            @endif

                            @if ($fptk->final_status == 'submitted')
                                <!-- HR Acknowledgment button -->
                                @if ($fptk->known_status == 'pending')
                                    @can('recruitment-requests.acknowledge')
                                        @if (Auth::id() == $fptk->known_by)
                                            <a href="{{ route('recruitment.requests.acknowledge-form', $fptk->id) }}"
                                                class="btn-action acknowledge-btn">
                                                <i class="fas fa-user-check"></i> HR Acknowledgment
                                            </a>
                                        @endif
                                    @endcan
                                @endif

                                <!-- Project Manager Approval button -->
                                @if ($fptk->known_status == 'approved' && $fptk->pm_approval_status == 'pending')
                                    @can('recruitment-requests.approve')
                                        @if (Auth::id() == $fptk->approved_by_pm)
                                            <a href="{{ route('recruitment.requests.approve-pm-form', $fptk->id) }}"
                                                class="btn-action pm-approve-btn">
                                                <i class="fas fa-user-shield"></i> PM Approval
                                            </a>
                                        @endif
                                    @endcan
                                @endif

                                <!-- Director Approval button -->
                                @if ($fptk->pm_approval_status == 'approved' && $fptk->director_approval_status == 'pending')
                                    @can('recruitment-requests.approve')
                                        @if (Auth::id() == $fptk->approved_by_director)
                                            <a href="{{ route('recruitment.requests.approve-director-form', $fptk->id) }}"
                                                class="btn-action director-approve-btn">
                                                <i class="fas fa-crown"></i> Director Approval
                                            </a>
                                        @endif
                                    @endcan
                                @endif
                            @endif

                            @if ($fptk->final_status == 'approved')
                                <!-- Assign Letter Number button -->
                                @if (!$fptk->hasLetterNumber())
                                    @can('recruitment-requests.assign-letter-number')
                                        <form action="{{ route('recruitment.requests.assign-letter-number', $fptk->id) }}"
                                            method="post"
                                            onsubmit="return confirm('Are you sure you want to assign a letter number to this FPTK?')">
                                            @csrf
                                            <button type="submit" class="btn-action assign-letter-btn">
                                                <i class="fas fa-hashtag"></i> Assign Letter Number
                                            </button>
                                        </form>
                                    @endcan
                                @endif
                            @endif
                        @endif

                        <a href="{{ route('recruitment.requests.print', $fptk->id) }}" class="btn-action print-btn"
                            target="_blank">
                            <i class="fas fa-print"></i> Print FPTK
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Modal -->
    @if ($fptk->final_status == 'draft')
        <div class="modal fade custom-modal" id="deleteModal" tabindex="-1" role="dialog"
            aria-labelledby="deleteModalLabel" aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="deleteModalLabel">Delete FPTK Request</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="delete-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <p class="delete-message">Are you sure you want to delete this FPTK request?</p>
                        <p class="delete-warning">This action cannot be undone.</p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn-cancel" data-dismiss="modal">Cancel</button>
                        <form action="{{ route('recruitment.requests.destroy', $fptk->id) }}" method="POST"
                            class="d-inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn-confirm-delete">Yes, Delete</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    @endif

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
            padding: 6px 12px;
            border-radius: 4px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
        }

        .status-draft {
            background-color: #f1c40f;
            color: #000000;
        }

        .status-pending {
            background-color: #e67e22;
            color: #ffffff;
        }

        .status-submitted {
            background-color: #3498db;
            color: #ffffff;
        }

        .status-approved {
            background-color: #27ae60;
            color: #ffffff;
        }

        .status-rejected {
            background-color: #e74c3c;
            color: #ffffff;
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

        /* Requirements */
        .requirements-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .requirement-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 4px;
        }

        .requirement-icon {
            width: 28px;
            height: 28px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 12px;
        }

        .requirement-content {
            flex: 1;
        }

        .requirement-label {
            font-size: 13px;
            color: #777;
            margin-bottom: 2px;
        }

        .requirement-value {
            font-weight: 600;
            color: #333;
            font-size: 14px;
        }

        /* Section Headers */
        .section-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }

        .section-icon {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 14px;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #2c3e50;
        }

        .section-content {
            font-size: 14px;
            line-height: 1.6;
            color: #555;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            border-left: 4px solid #3498db;
        }

        .requirement-section {
            margin-bottom: 20px;
        }

        .requirement-section:last-child {
            margin-bottom: 0;
        }

        .detailed-requirements {
            border-top: 1px solid #e9ecef;
            padding-top: 20px;
        }

        /* Sessions */
        .sessions-count {
            background: #3498db;
            color: white;
            font-size: 14px;
            border-radius: 4px;
            padding: 2px 8px;
            margin-left: 8px;
        }

        .sessions-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .session-item {
            padding: 15px;
            border-bottom: 1px solid #edf2f7;
        }

        .session-candidate {
            font-size: 16px;
            font-weight: 500;
            color: #2c3e50;
            margin-bottom: 4px;
        }

        .session-stage {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 6px;
        }

        .session-meta {
            display: flex;
            gap: 15px;
            font-size: 13px;
            color: #64748b;
            margin-bottom: 8px;
        }

        .session-email,
        .session-phone {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .status-badge {
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: 500;
            text-transform: uppercase;
        }

        .status-badge.status-active {
            background-color: #d4edda;
            color: #155724;
        }

        .status-badge.status-completed {
            background-color: #cce5ff;
            color: #004085;
        }

        .status-badge.status-rejected {
            background-color: #f8d7da;
            color: #721c24;
        }

        /* Approval Flow */
        .approval-process-card {
            margin-top: 0;
        }

        .approval-flow {
            position: relative;
            padding: 10px 0;
        }

        .approval-flow::before {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            left: 20px;
            width: 3px;
            background: #e0e0e0;
        }

        .approval-step {
            position: relative;
            padding-left: 60px;
            margin-bottom: 30px;
        }

        .approval-step:last-child {
            margin-bottom: 0;
        }

        .step-icon {
            position: absolute;
            left: 0;
            top: 0;
            width: 40px;
            height: 40px;
            background: #e0e0e0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            z-index: 1;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        }

        .step-icon.approved {
            background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        }

        .step-icon.rejected {
            background: linear-gradient(135deg, #fa709a 0%, #ff0844 100%);
        }

        .step-icon.pending {
            background: linear-gradient(135deg, #f6d365 0%, #fda085 100%);
        }

        .step-content {
            background: white;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
            padding: 15px;
        }

        .step-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .step-header h4 {
            margin: 0;
            font-size: 16px;
            color: #333;
        }

        .step-status {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }

        .step-status.approved {
            background-color: #e6f9e6;
            color: #0f8c0f;
        }

        .step-status.rejected {
            background-color: #ffe6e6;
            color: #cc0000;
        }

        .step-status.pending {
            background-color: #fff4e6;
            color: #cc7a00;
        }

        .step-details {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 13px;
            color: #777;
            margin-bottom: 15px;
            flex-wrap: wrap;
            gap: 10px;
        }

        .step-date {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            color: #555;
            font-weight: 500;
        }

        .step-person {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
            color: #333;
            font-weight: 500;
        }

        .step-remark {
            padding-top: 10px;
            border-top: 1px dashed #eee;
        }

        .remark-text {
            margin: 0;
            padding: 10px 15px;
            background-color: #f8f9fa;
            border-radius: 8px;
            font-size: 14px;
            color: #555;
            border-left: 3px solid #6c757d;
        }

        /* Requester Card */
        .requester-info {
            text-align: center;
        }

        .requester-name {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        .requester-email {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 12px;
        }

        .requester-date {
            font-size: 13px;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
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
            padding: 10px 16px;
            border-radius: 4px;
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

        /* Ensure form buttons have same width */
        .fptk-action-buttons form {
            width: 100%;
        }

        .fptk-action-buttons form .btn-action {
            width: 100%;
        }

        .back-btn {
            background-color: #64748b;
        }

        .back-btn:hover {
            color: white;
        }

        .edit-btn {
            background-color: #3498db;
        }

        .edit-btn:hover {
            color: white;
        }

        .delete-btn {
            background-color: #e74c3c;
        }

        .delete-btn:hover {
            color: white;
        }

        .print-btn {
            background-color: #27ae60;
        }

        .print-btn:hover {
            color: white;
        }

        .btn-action:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        /* Custom Modal */
        .custom-modal .modal-content {
            border-radius: 6px;
            border: none;
        }

        .custom-modal .modal-header {
            background: #f8fafc;
            padding: 15px 20px;
        }

        .custom-modal .modal-title {
            font-size: 16px;
            font-weight: 600;
            color: #2c3e50;
        }

        .delete-icon {
            text-align: center;
            color: #e74c3c;
            font-size: 48px;
            margin-bottom: 15px;
        }

        .delete-message {
            font-size: 16px;
            color: #2c3e50;
            margin-bottom: 10px;
            text-align: center;
        }

        .delete-warning {
            font-size: 13px;
            color: #64748b;
            text-align: center;
        }

        .btn-cancel {
            padding: 8px 16px;
            border-radius: 4px;
            background: #e2e8f0;
            color: #475569;
            font-weight: 500;
            border: none;
        }

        .btn-confirm-delete {
            padding: 8px 16px;
            border-radius: 4px;
            background-color: #e74c3c;
            color: white;
            font-weight: 500;
            border: none;
        }

        /* Action Button Styles */
        .btn-action.submit-btn {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
            color: white;
        }

        .btn-action.submit-btn:hover {
            background: linear-gradient(135deg, #e0a800, #e8590c);
        }

        .btn-action.acknowledge-btn {
            background: linear-gradient(135deg, #17a2b8, #138496);
            color: white;
        }

        .btn-action.acknowledge-btn:hover {
            background: linear-gradient(135deg, #138496, #117a8b);
        }

        .btn-action.pm-approve-btn {
            background: linear-gradient(135deg, #fd7e14, #e8590c);
            color: white;
        }

        .btn-action.pm-approve-btn:hover {
            background: linear-gradient(135deg, #e8590c, #d63384);
        }

        .btn-action.director-approve-btn {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }

        .btn-action.director-approve-btn:hover {
            background: linear-gradient(135deg, #20c997, #17a2b8);
        }

        .btn-action.assign-letter-btn {
            background: linear-gradient(135deg, #6f42c1, #e83e8c);
            color: white;
        }

        .btn-action.assign-letter-btn:hover {
            background: linear-gradient(135deg, #e83e8c, #fd7e14);
        }

        /* Ensure all buttons have consistent styling */
        .btn-action:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.3);
        }

        .btn-action:active {
            transform: translateY(0);
        }

        /* Button text alignment */
        .btn-action i {
            flex-shrink: 0;
        }

        .btn-action span {
            flex: 1;
            text-align: center;
        }

        /* Modal Styles */
        .custom-modal .modal-content {
            border-radius: 10px;
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .custom-modal .modal-header {
            border-bottom: 1px solid #e9ecef;
            padding: 20px;
        }

        .custom-modal .modal-title {
            color: #2c3e50;
            font-weight: 600;
        }

        .custom-modal .modal-body {
            padding: 30px 20px;
            text-align: center;
        }

        .delete-icon {
            text-align: center;
            font-size: 48px;
            margin-bottom: 15px;
            color: #e74c3c;
        }

        .delete-message {
            font-size: 16px;
            color: #2c3e50;
            margin-bottom: 10px;
            text-align: center;
        }

        .delete-warning {
            font-size: 13px;
            color: #e74c3c;
            text-align: center;
        }

        .custom-modal .modal-footer {
            border-top: 1px solid #e9ecef;
            padding: 20px;
            justify-content: center;
        }

        /* Responsive Adjustments */
        @media (max-width: 992px) {
            .info-grid {
                grid-template-columns: 1fr;
            }

            .requirements-grid {
                grid-template-columns: 1fr;
            }

            /* Reorder columns on mobile */
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

            /* Ensure cards maintain proper spacing */
            .fptk-card {
                margin-bottom: 20px;
            }

            /* Adjust padding for better mobile view */
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

            .sessions-list {
                max-height: 300px;
            }
        }

        /* Preserve desktop layout above 992px */
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

<!-- Delete Modal -->
@if ($fptk->final_status == 'draft')
    <div class="modal fade custom-modal" id="deleteModal" tabindex="-1" role="dialog"
        aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Delete FPTK</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="delete-icon">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <div class="delete-message">
                        Are you sure you want to delete this FPTK?
                    </div>
                    <div class="delete-warning">
                        This action cannot be undone. All data will be permanently removed.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <form action="{{ route('recruitment.requests.destroy', $fptk->id) }}" method="POST"
                        class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn-confirm-delete">Delete FPTK</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif
