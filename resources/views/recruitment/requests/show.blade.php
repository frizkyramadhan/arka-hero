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
                                        <i class="fas fa-question-circle"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Request Reason</div>
                                        <div class="info-value">
                                            @if ($fptk->request_reason == 'replacement_resign')
                                                Replacement - Resign, Termination, End of Contract
                                            @elseif($fptk->request_reason == 'replacement_promotion')
                                                Replacement - Promotion, Mutation, Demotion
                                            @elseif($fptk->request_reason == 'additional_workplan')
                                                Additional - Workplan
                                            @elseif($fptk->request_reason == 'other')
                                                Other
                                            @elseif($fptk->request_reason == 'replacement')
                                                Replacement (Legacy)
                                            @elseif($fptk->request_reason == 'additional')
                                                Additional (Legacy)
                                            @else
                                                {{ ucfirst(str_replace('_', ' ', $fptk->request_reason)) }}
                                            @endif
                                        </div>
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

                                    <!-- Theory Test Requirement Section -->
                                    <div class="requirement-section">
                                        <div class="section-header">
                                            <div class="section-icon" style="background-color: #e91e63;">
                                                <i class="fas fa-book"></i>
                                            </div>
                                            <div class="section-title">Theory Test Requirement</div>
                                        </div>
                                        <div class="section-content">
                                            @if ($fptk->requires_theory_test)
                                                <div class="theory-test-required">
                                                    <div class="alert alert-warning mb-0">
                                                        <i class="fas fa-exclamation-triangle"></i>
                                                        <strong>Posisi ini memerlukan Tes Teori</strong>
                                                    </div>
                                                    <div class="theory-test-details mt-3">
                                                        <p class="mb-2"><strong>Alasan:</strong></p>
                                                        <ul class="mb-0">
                                                            <li>Posisi mekanik yang memerlukan kompetensi teknis</li>
                                                            <li>Kandidat harus lulus tes teori sebelum interview</li>
                                                            <li>Stage tes teori akan muncul di timeline recruitment</li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="theory-test-not-required">
                                                    <div class="alert alert-info mb-0">
                                                        <i class="fas fa-info-circle"></i>
                                                        <strong>Posisi ini tidak memerlukan Tes Teori</strong>
                                                    </div>
                                                    <div class="theory-test-details mt-3">
                                                        <p class="mb-2"><strong>Alasan:</strong></p>
                                                        <ul class="mb-0">
                                                            <li>Posisi non-teknis yang tidak memerlukan kompetensi mekanik
                                                            </li>
                                                            <li>Kandidat langsung ke interview setelah psikotes</li>
                                                            <li>Stage tes teori akan di-skip di timeline recruitment</li>
                                                        </ul>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="col-lg-4">
                    <!-- Approval Status Card -->
                    <x-approval-status-card :documentType="'recruitment_request'" :documentId="$fptk->id" :mode="$fptk->status === 'draft' ? 'preview' : 'status'" :projectId="$fptk->project_id"
                        :departmentId="$fptk->department_id" :requestReason="$fptk->request_reason" title="Approval Status" />

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

                        @if ($fptk->status != 'rejected' && $fptk->status != 'cancelled')
                            @if ($fptk->status == 'draft')
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
                                @can('recruitment-requests.edit')
                                    <form action="{{ route('recruitment.requests.submit', $fptk->id) }}" method="post"
                                        onsubmit="return confirm('Are you sure you want to submit this FPTK for approval?')">
                                        @csrf
                                        <button type="submit" class="btn-action submit-btn">
                                            <i class="fas fa-paper-plane"></i> Submit for Approval
                                        </button>
                                    </form>
                                @endcan
                            @endif

                            @if ($fptk->status == 'approved')
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

                <!-- Recruitment Sessions (full width table, unified with session view) -->
                <div class="col-lg-12 sessions-section">
                    @if ($sessions->isNotEmpty())
                        <div class="row">
                            <div class="col-12">
                                <div class="fptk-card sessions-table-card">
                                    <div class="card-head d-flex align-items-center justify-content-between">
                                        <h2 class="mb-0"><i class="fas fa-user-graduate"></i> Recruitment Sessions</h2>
                                        <span class="sessions-count">{{ $sessions->count() }}</span>
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
                                                        <th class="text-center align-middle">Tes Teori</th>
                                                        <th class="text-center align-middle">Interview HR</th>
                                                        <th class="text-center align-middle">Interview User</th>
                                                        <th class="text-center align-middle">Offering</th>
                                                        <th class="text-center align-middle">MCU</th>
                                                        <th class="text-center align-middle">Hire</th>
                                                        <th class="text-center align-middle">Onboarding</th>
                                                        <th class="text-center align-middle">Final Status</th>
                                                        <th class="text-center align-middle" style="width: 120px;">Action
                                                        </th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($sessions as $index => $session)
                                                        <tr>
                                                            <td class="text-center">{{ $index + 1 }}</td>
                                                            <td>
                                                                <strong>{{ $session->candidate->fullname ?? 'N/A' }}</strong>
                                                                <br>
                                                                <small class="text-muted">Session:
                                                                    {{ $session->session_number }}</small>
                                                            </td>
                                                            @php
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
                                                                $currentStage = $session->current_stage;
                                                                $stageStatus = $session->stage_status;
                                                            @endphp
                                                            @foreach ($stages as $stage)
                                                                @php
                                                                    $stageIndex = array_search($stage, $stages);
                                                                    $currentStageIndex = array_search(
                                                                        $currentStage,
                                                                        $stages,
                                                                    );
                                                                    if ($stageIndex < $currentStageIndex) {
                                                                        $status = 'completed';
                                                                        $icon = 'fas fa-check-circle text-success';
                                                                    } elseif ($stageIndex == $currentStageIndex) {
                                                                        $status = $stageStatus;
                                                                        $icon =
                                                                            $stageStatus == 'completed'
                                                                                ? 'fas fa-check-circle text-success'
                                                                                : 'fas fa-clock text-warning';
                                                                    } else {
                                                                        $status = 'pending';
                                                                        $icon = 'fas fa-circle text-muted';
                                                                    }
                                                                @endphp
                                                                <td class="text-center">
                                                                    <i class="{{ $icon }}"
                                                                        title="{{ ucfirst($status) }}"></i>
                                                                </td>
                                                            @endforeach
                                                            <td class="text-center">
                                                                @php
                                                                    $finalStatusMap = [
                                                                        'in_process' =>
                                                                            '<span class="badge badge-info">In Process</span>',
                                                                        'hired' =>
                                                                            '<span class="badge badge-success">Hired</span>',
                                                                        'rejected' =>
                                                                            '<span class="badge badge-danger">Rejected</span>',
                                                                        'cancelled' =>
                                                                            '<span class="badge badge-warning">Cancelled</span>',
                                                                    ];
                                                                @endphp
                                                                {!! $finalStatusMap[$session->status] ??
                                                                    '<span class="badge badge-secondary">' . ucfirst($session->status) . '</span>' !!}
                                                            </td>
                                                            <td class="text-center">
                                                                @can('recruitment-sessions.delete')
                                                                    <a href="{{ route('recruitment.sessions.candidate', $session->id) }}"
                                                                        class="btn btn-sm btn-info"
                                                                        title="View Session Details">
                                                                        <i class="fas fa-eye"></i>
                                                                    </a>
                                                                @endcan
                                                                @can('recruitment-sessions.delete')
                                                                    @if ($session->status !== 'hired')
                                                                        <button type="button"
                                                                            class="btn btn-sm btn-danger delete-session-btn"
                                                                            data-session-id="{{ $session->id }}"
                                                                            data-candidate-name="{{ $session->candidate->fullname ?? 'N/A' }}"
                                                                            data-toggle="modal"
                                                                            data-target="#deleteSessionModal"
                                                                            title="Remove Candidate from Session">
                                                                            <i class="fas fa-trash"></i>
                                                                        </button>
                                                                    @endif
                                                                @endcan
                                                            </td>
                                                        </tr>
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
        </div>
    </div>

    <!-- Delete Modal -->
    @if ($fptk->status == 'draft')
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
            color: #333;
            background-color: #ffffff;
            padding: 15px;
            border-radius: 4px;
            border: 1px solid #e9ecef;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
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

        /* Theory Test Requirement Styling */
        .theory-test-required .alert-warning {
            background-color: #fff3cd;
            border-color: #ffeaa7;
            color: #856404;
        }

        .theory-test-not-required .alert-info {
            background-color: #d1ecf1;
            border-color: #bee5eb;
            color: #0c5460;
        }

        .theory-test-details {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            border-left: 4px solid #e9ecef;
        }

        .theory-test-details ul {
            margin-bottom: 0;
            padding-left: 20px;
        }

        .theory-test-details li {
            margin-bottom: 5px;
            color: #495057;
        }

        .theory-test-details li:last-child {
            margin-bottom: 0;
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

        /* Ensure form buttons have same width */
        .fptk-action-buttons form {
            width: 100%;
        }

        .fptk-action-buttons form .btn-action {
            width: 100%;
        }

        .back-btn {
            background-color: #6c757d;
        }

        .back-btn:hover {
            background-color: #5a6268;
            color: white;
        }

        .edit-btn {
            background-color: #007bff;
        }

        .edit-btn:hover {
            background-color: #0056b3;
            color: white;
        }

        .delete-btn {
            background-color: #dc3545;
        }

        .delete-btn:hover {
            background-color: #c82333;
            color: white;
        }

        .print-btn {
            background-color: #28a745;
        }

        .print-btn:hover {
            background-color: #1e7e34;
            color: white;
        }

        .btn-action:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        /* Custom Modal */
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

            /* Ensure sessions section stays below right column on mobile */
            .fptk-content .sessions-section {
                order: 3;
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
