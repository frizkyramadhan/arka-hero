@extends('layouts.main')

@section('content')
    <div class="content-wrapper-custom">
        <!-- Document Header -->
        <div class="document-header">
            <div class="document-header-content">
                <div class="document-type">{{ ucfirst(str_replace('_', ' ', $approvalPlan->document_type)) }}</div>
                <h1 class="document-number">
                    @if ($approvalPlan->document_type === 'officialtravel')
                        @php $document = App\Models\Officialtravel::find($approvalPlan->document_id); @endphp
                        {{ $document->official_travel_number ?? 'N/A' }}
                    @elseif($approvalPlan->document_type === 'recruitment_request')
                        @php $document = App\Models\RecruitmentRequest::find($approvalPlan->document_id); @endphp
                        {{ $document->request_number ?? 'N/A' }}
                    @endif
                </h1>
                <div class="document-date">
                    <i class="far fa-calendar-alt"></i>
                    @if ($approvalPlan->document_type === 'officialtravel')
                        {{ date('d F Y', strtotime($document->official_travel_date ?? now())) }}
                    @elseif($approvalPlan->document_type === 'recruitment_request')
                        {{ date('d F Y', strtotime($document->created_at ?? now())) }}
                    @endif
                </div>
                <div class="document-status-pill status-pending-approval">
                    <i class="fas fa-clock"></i>
                    Pending Approval
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="document-content">
            <div class="row">
                <!-- Document Details -->
                <div class="col-lg-8">
                    @if ($approvalPlan->document_type === 'officialtravel')
                        <!-- Official Travel Document -->
                        <div class="document-card document-info-card">
                            <div class="card-head">
                                <h2><i class="fas fa-plane"></i> Official Travel Details</h2>
                            </div>
                            <div class="card-body">
                                @php $document = App\Models\Officialtravel::with(['traveler.employee', 'traveler.position.department', 'traveler.project', 'transportation', 'accommodation', 'details.follower.employee', 'details.follower.position.department', 'details.follower.project'])->find($approvalPlan->document_id); @endphp

                                <!-- Travel Details -->
                                <div class="info-grid">
                                    <div class="info-item">
                                        <div class="info-icon" style="background-color: #3498db;">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </div>
                                        <div class="info-content">
                                            <div class="info-label">Destination</div>
                                            <div class="info-value">{{ $document->destination }}</div>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-icon" style="background-color: #e74c3c;">
                                            <i class="fas fa-tasks"></i>
                                        </div>
                                        <div class="info-content">
                                            <div class="info-label">Purpose</div>
                                            <div class="info-value">{{ $document->purpose }}</div>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-icon" style="background-color: #f1c40f;">
                                            <i class="fas fa-clock"></i>
                                        </div>
                                        <div class="info-content">
                                            <div class="info-label">Duration</div>
                                            <div class="info-value">{{ $document->duration }}</div>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-icon" style="background-color: #9b59b6;">
                                            <i class="fas fa-calendar-plus"></i>
                                        </div>
                                        <div class="info-content">
                                            <div class="info-label">Departure Date</div>
                                            <div class="info-value">
                                                {{ date('d F Y', strtotime($document->departure_from)) }}</div>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-icon" style="background-color: #1abc9c;">
                                            <i class="fas fa-bus"></i>
                                        </div>
                                        <div class="info-content">
                                            <div class="info-label">Transportation</div>
                                            <div class="info-value">
                                                {{ $document->transportation->transportation_name ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-icon" style="background-color: #e67e22;">
                                            <i class="fas fa-hotel"></i>
                                        </div>
                                        <div class="info-content">
                                            <div class="info-label">Accommodation</div>
                                            <div class="info-value">
                                                {{ $document->accommodation->accommodation_name ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Traveler Information -->
                        <div class="document-card traveler-info-card">
                            <div class="card-head">
                                <h2><i class="fas fa-user"></i> Traveler</h2>
                            </div>
                            <div class="card-body">
                                <div class="traveler-details">
                                    <div class="traveler-detail-item">
                                        <i class="fas fa-id-card detail-icon"></i>
                                        <div class="detail-content">
                                            <div class="detail-label">NIK - Name</div>
                                            <div class="detail-value">{{ $document->traveler->nik }} -
                                                {{ $document->traveler->employee->fullname ?? 'Unknown Employee' }}</div>
                                        </div>
                                    </div>
                                    <div class="traveler-detail-item">
                                        <i class="fas fa-sitemap detail-icon"></i>
                                        <div class="detail-content">
                                            <div class="detail-label">Title</div>
                                            <div class="detail-value">
                                                {{ $document->traveler->position->position_name ?? 'No Position' }}</div>
                                        </div>
                                    </div>
                                    <div class="traveler-detail-item">
                                        <i class="fas fa-globe detail-icon"></i>
                                        <div class="detail-content">
                                            <div class="detail-label">Business Unit</div>
                                            <div class="detail-value">
                                                {{ $document->traveler->project->project_code ?? 'No Code' }} :
                                                {{ $document->traveler->project->project_name ?? 'No Project' }}</div>
                                        </div>
                                    </div>
                                    <div class="traveler-detail-item">
                                        <i class="fas fa-building detail-icon"></i>
                                        <div class="detail-content">
                                            <div class="detail-label">Division / Department</div>
                                            <div class="detail-value">
                                                {{ $document->traveler->position->department->department_name ?? 'No Department' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Followers List -->
                        @if ($document->details && $document->details->isNotEmpty())
                            <div class="document-card followers-info-card">
                                <div class="card-head">
                                    <h2><i class="fas fa-users"></i> Followers <span
                                            class="followers-count">{{ $document->details->count() }}</span></h2>
                                </div>
                                <div class="card-body p-0">
                                    <div class="followers-list">
                                        @foreach ($document->details as $detail)
                                            <div class="follower-item">
                                                <div class="follower-info">
                                                    <div class="follower-name">
                                                        {{ $detail->follower->employee->fullname ?? 'Unknown Employee' }}
                                                    </div>
                                                    <div class="follower-position">
                                                        {{ $detail->follower->position->position_name ?? 'No Position' }}
                                                    </div>
                                                    <div class="follower-meta">
                                                        <span class="follower-nik"><i class="fas fa-id-card"></i>
                                                            {{ $detail->follower->nik }}</span>
                                                        <span class="follower-department"><i class="fas fa-sitemap"></i>
                                                            {{ $detail->follower->position->department->department_name ?? 'No Department' }}</span>
                                                    </div>
                                                    <div class="follower-project">
                                                        <i class="fas fa-project-diagram"></i>
                                                        {{ $detail->follower->project->project_code ?? 'No Code' }} :
                                                        {{ $detail->follower->project->project_name ?? 'No Project' }}
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif
                    @elseif($approvalPlan->document_type === 'recruitment_request')
                        @php $document = App\Models\RecruitmentRequest::with(['department', 'project', 'position', 'level', 'createdBy'])->find($approvalPlan->document_id); @endphp

                        <!-- FPTK Information Card -->
                        <div class="document-card document-info-card">
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
                                            <div class="info-value">{{ $document->department->department_name }}</div>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-icon" style="background-color: #e74c3c;">
                                            <i class="fas fa-project-diagram"></i>
                                        </div>
                                        <div class="info-content">
                                            <div class="info-label">Project</div>
                                            <div class="info-value">{{ $document->project->project_code }} -
                                                {{ $document->project->project_name }}</div>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-icon" style="background-color: #f1c40f;">
                                            <i class="fas fa-user-tie"></i>
                                        </div>
                                        <div class="info-content">
                                            <div class="info-label">Position</div>
                                            <div class="info-value">{{ $document->position->position_name }}</div>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-icon" style="background-color: #9b59b6;">
                                            <i class="fas fa-layer-group"></i>
                                        </div>
                                        <div class="info-content">
                                            <div class="info-label">Level</div>
                                            <div class="info-value">{{ $document->level->name }}</div>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-icon" style="background-color: #1abc9c;">
                                            <i class="fas fa-users"></i>
                                        </div>
                                        <div class="info-content">
                                            <div class="info-label">Required Quantity</div>
                                            <div class="info-value">{{ $document->required_qty }}
                                                {{ $document->required_qty > 1 ? 'persons' : 'person' }}</div>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-icon" style="background-color: #e67e22;">
                                            <i class="fas fa-calendar-check"></i>
                                        </div>
                                        <div class="info-content">
                                            <div class="info-label">Required Date</div>
                                            <div class="info-value">
                                                {{ date('d F Y', strtotime($document->required_date)) }}</div>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-icon" style="background-color: #34495e;">
                                            <i class="fas fa-briefcase"></i>
                                        </div>
                                        <div class="info-content">
                                            <div class="info-label">Employment Type</div>
                                            <div class="info-value">
                                                {{ ucfirst(str_replace('_', ' ', $document->employment_type)) }}</div>
                                        </div>
                                    </div>
                                    <div class="info-item">
                                        <div class="info-icon" style="background-color: #2c3e50;">
                                            <i class="fas fa-question-circle"></i>
                                        </div>
                                        <div class="info-content">
                                            <div class="info-label">Request Reason</div>
                                            <div class="info-value">
                                                {{ $document->request_reason == 'other' ? $document->other_reason : ucfirst(str_replace('_', ' ', $document->request_reason)) }}
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
                                                @if ($document->requires_theory_test)
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
                        <div class="document-card requirements-card">
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
                                        {!! nl2br(e($document->job_description)) !!}
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
                                            <div class="requirement-value">{{ ucfirst($document->required_gender) }}</div>
                                        </div>
                                    </div>
                                    <div class="requirement-item">
                                        <div class="requirement-icon" style="background-color: #e74c3c;">
                                            <i class="fas fa-heart"></i>
                                        </div>
                                        <div class="requirement-content">
                                            <div class="requirement-label">Marital Status</div>
                                            <div class="requirement-value">
                                                {{ ucfirst($document->required_marital_status) }}</div>
                                        </div>
                                    </div>
                                    @if ($document->required_age_min || $document->required_age_max)
                                        <div class="requirement-item">
                                            <div class="requirement-icon" style="background-color: #f1c40f;">
                                                <i class="fas fa-birthday-cake"></i>
                                            </div>
                                            <div class="requirement-content">
                                                <div class="requirement-label">Age Range</div>
                                                <div class="requirement-value">
                                                    @if ($document->required_age_min && $document->required_age_max)
                                                        {{ $document->required_age_min }} -
                                                        {{ $document->required_age_max }} years
                                                    @elseif($document->required_age_min)
                                                        Min {{ $document->required_age_min }} years
                                                    @elseif($document->required_age_max)
                                                        Max {{ $document->required_age_max }} years
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                    @if ($document->required_education)
                                        <div class="requirement-item">
                                            <div class="requirement-icon" style="background-color: #9b59b6;">
                                                <i class="fas fa-graduation-cap"></i>
                                            </div>
                                            <div class="requirement-content">
                                                <div class="requirement-label">Education</div>
                                                <div class="requirement-value">{{ $document->required_education }}</div>
                                            </div>
                                        </div>
                                    @endif
                                </div>

                                <!-- Detailed Requirements -->
                                @if (
                                    $document->required_skills ||
                                        $document->required_experience ||
                                        $document->required_physical ||
                                        $document->required_mental ||
                                        $document->other_requirements)
                                    <div class="detailed-requirements">
                                        @if ($document->required_skills)
                                            <div class="requirement-section">
                                                <div class="section-header">
                                                    <div class="section-icon" style="background-color: #e67e22;">
                                                        <i class="fas fa-tools"></i>
                                                    </div>
                                                    <div class="section-title">Required Skills</div>
                                                </div>
                                                <div class="section-content">{{ $document->required_skills }}</div>
                                            </div>
                                        @endif

                                        @if ($document->required_experience)
                                            <div class="requirement-section">
                                                <div class="section-header">
                                                    <div class="section-icon" style="background-color: #27ae60;">
                                                        <i class="fas fa-briefcase"></i>
                                                    </div>
                                                    <div class="section-title">Required Experience</div>
                                                </div>
                                                <div class="section-content">{{ $document->required_experience }}</div>
                                            </div>
                                        @endif

                                        @if ($document->required_physical)
                                            <div class="requirement-section">
                                                <div class="section-header">
                                                    <div class="section-icon" style="background-color: #f39c12;">
                                                        <i class="fas fa-dumbbell"></i>
                                                    </div>
                                                    <div class="section-title">Physical Requirements</div>
                                                </div>
                                                <div class="section-content">{{ $document->required_physical }}</div>
                                            </div>
                                        @endif

                                        @if ($document->required_mental)
                                            <div class="requirement-section">
                                                <div class="section-header">
                                                    <div class="section-icon" style="background-color: #8e44ad;">
                                                        <i class="fas fa-brain"></i>
                                                    </div>
                                                    <div class="section-title">Mental Requirements</div>
                                                </div>
                                                <div class="section-content">{{ $document->required_mental }}</div>
                                            </div>
                                        @endif

                                        @if ($document->other_requirements)
                                            <div class="requirement-section">
                                                <div class="section-header">
                                                    <div class="section-icon" style="background-color: #34495e;">
                                                        <i class="fas fa-plus-circle"></i>
                                                    </div>
                                                    <div class="section-title">Other Requirements</div>
                                                </div>
                                                <div class="section-content">{{ $document->other_requirements }}</div>
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
                                                @if ($document->requires_theory_test)
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
                                                                <li>Posisi non-teknis yang tidak memerlukan kompetensi
                                                                    mekanik</li>
                                                                <li>Kandidat langsung ke interview setelah psikotes</li>
                                                                <li>Stage tes teori akan di-skip di timeline recruitment
                                                                </li>
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
                    @endif
                </div>

                <!-- Approval Form goes here-->

                <!-- Approval Form -->
                <div class="col-lg-4">
                    <!-- Approval Decision -->
                    <div class="document-card approval-card">
                        <div class="card-head">
                            <h2><i class="fas fa-user-shield"></i> Approval Decision</h2>
                        </div>
                        <div class="card-body">
                            <!-- Decision Buttons -->
                            <div class="decision-buttons mb-4">
                                <div class="decision-header">
                                    <h3>Choose Your Decision</h3>
                                    <p>Select one of the options below to proceed</p>
                                </div>
                                <div class="decision-options">
                                    <button type="button" class="decision-btn approve-btn" data-status="1">
                                        <div class="btn-icon">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                        <div class="btn-content">
                                            <div class="btn-title">Approve</div>
                                        </div>
                                    </button>
                                    <button type="button" class="decision-btn reject-btn" data-status="2">
                                        <div class="btn-icon">
                                            <i class="fas fa-times-circle"></i>
                                        </div>
                                        <div class="btn-content">
                                            <div class="btn-title">Reject</div>
                                        </div>
                                    </button>
                                </div>
                            </div>

                            <!-- Approval Form -->
                            <form action="{{ route('approval.requests.process', $approvalPlan->id) }}" method="POST"
                                id="approvalForm">
                                @csrf
                                <input type="hidden" name="status" id="status" value="">

                                <div class="form-group">
                                    <label for="remarks">Approval Notes <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('remarks') is-invalid @enderror" name="remarks" id="remarks" rows="4"
                                        placeholder="Please provide your approval details:
• Decision rationale
• Any conditions or requirements
• Additional notes or observations"
                                        required>{{ old('remarks') }}</textarea>
                                    @error('remarks')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="btn btn-success btn-block submit-btn" disabled>
                                        <i class="fas fa-paper-plane"></i>
                                        Submit Decision
                                    </button>
                                    <a href="{{ route('approval.requests.index') }}"
                                        class="btn btn-secondary btn-block mt-2">
                                        <i class="fas fa-times"></i>
                                        Cancel
                                    </a>
                                </div>
                            </form>
                        </div>
                    </div>
                    <!-- Approval Status -->
                    <div class="document-card approval-status-card">
                        <div class="card-head">
                            <h2><i class="fas fa-user-check"></i> Approval Status</h2>
                        </div>
                        <div class="card-body">
                            <!-- Submitter Information -->
                            <div class="submitter-section mb-4">
                                <h3 class="section-title">
                                    <i class="fas fa-user-edit"></i> Document Submitter
                                </h3>
                                <div class="submitter-info">
                                    <div class="submitter-avatar">
                                        <i class="fas fa-user-circle"></i>
                                    </div>
                                    <div class="submitter-details">
                                        <div class="submitter-name">
                                            @if ($approvalPlan->document_type === 'officialtravel')
                                                {{ $document->creator->name ?? 'N/A' }}
                                            @elseif($approvalPlan->document_type === 'recruitment_request')
                                                {{ $document->createdBy->name ?? 'N/A' }}
                                            @endif
                                        </div>
                                        <div class="submitter-meta">
                                            <span class="submit-date">
                                                <i class="far fa-calendar-alt"></i>
                                                @if ($approvalPlan->document_type === 'officialtravel')
                                                    {{ $document->submit_at ? date('d M Y H:i', strtotime($document->submit_at)) : 'N/A' }}
                                                @elseif($approvalPlan->document_type === 'recruitment_request')
                                                    {{ $document->submit_at ? date('d M Y H:i', strtotime($document->submit_at)) : 'N/A' }}
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Approval Flow -->
                            <div class="approval-flow-section">
                                <h3 class="section-title">
                                    <i class="fas fa-sitemap"></i> Approval Flow
                                </h3>
                                @php
                                    $allApprovalPlans = App\Models\ApprovalPlan::where(
                                        'document_id',
                                        $approvalPlan->document_id,
                                    )
                                        ->where('document_type', $approvalPlan->document_type)
                                        ->where('is_open', true)
                                        ->orderBy('approval_order', 'asc')
                                        ->get();
                                @endphp

                                <div class="approval-flow">
                                    @foreach ($allApprovalPlans as $index => $plan)
                                        @php
                                            // Determine step classes
                                            $stepClasses = 'approval-step';

                                            // Check if this is the current user's step
if ($plan->id === $approvalPlan->id) {
    $stepClasses .= ' current-step';
}

// Check if step is completed
if ($plan->status !== 0) {
    $stepClasses .= ' completed-step';
}

// Check if step can be processed (for sequential approval)
$canProcess = true;
if ($plan->approval_order > 1 && $plan->status === 0) {
    for ($order = 1; $order < $plan->approval_order; $order++) {
        $previousApproval = $allApprovalPlans
            ->where('approval_order', $order)
            ->first();
        if (!$previousApproval || $previousApproval->status !== 1) {
            $canProcess = false;
            break;
        }
    }
}

// Add waiting class if step cannot be processed yet
if (
    $plan->id === $approvalPlan->id &&
    $plan->status === 0 &&
    !$canProcess
) {
    $stepClasses .= ' waiting-step';
}

// Add specific status classes for better styling
if ($plan->status === 1) {
    $stepClasses .= ' step-approved';
} elseif ($plan->status === 2) {
    $stepClasses .= ' step-rejected';
} elseif ($plan->status === 0) {
    if ($plan->id === $approvalPlan->id && !$canProcess) {
        $stepClasses .= ' step-waiting';
    } elseif ($plan->id === $approvalPlan->id && $canProcess) {
        $stepClasses .= ' step-ready';
    } else {
        $stepClasses .= ' step-pending';
                                                }
                                            }
                                        @endphp

                                        <div class="{{ $stepClasses }}">
                                            <div class="step-number">{{ $plan->approval_order ?? $index + 1 }}</div>
                                            <div class="step-content">
                                                <div class="step-header">
                                                    <div class="approver-info">
                                                        <div class="approver-name">{{ $plan->approver->name ?? 'N/A' }}
                                                        </div>
                                                        <div class="approver-role">Approver</div>
                                                    </div>
                                                    <div class="step-status">
                                                        @if ($plan->status === 0)
                                                            <span class="status-badge pending">
                                                                <i class="fas fa-clock"></i> Pending
                                                            </span>
                                                        @elseif($plan->status === 1)
                                                            <span class="status-badge approved">
                                                                <i class="fas fa-check"></i> Approved
                                                            </span>
                                                        @elseif($plan->status === 2)
                                                            <span class="status-badge rejected">
                                                                <i class="fas fa-times"></i> Rejected
                                                            </span>
                                                        @endif
                                                    </div>
                                                </div>

                                                @if ($plan->status !== 0 && $plan->remarks)
                                                    <div class="step-remarks">
                                                        <div class="remarks-label">
                                                            <i class="fas fa-comment"></i> Remarks:
                                                        </div>
                                                        <div class="remarks-content">{{ $plan->remarks }}</div>
                                                        <div class="remarks-time">
                                                            <i class="far fa-clock"></i>
                                                            {{ $plan->updated_at ? date('d M Y H:i', strtotime($plan->updated_at)) : 'N/A' }}
                                                        </div>
                                                    </div>
                                                @endif

                                                @if ($plan->id === $approvalPlan->id && $plan->status === 0)
                                                    @php
                                                        // Check if this step can be processed (all previous steps approved)
                                                        $canProcess = true;
                                                        if ($plan->approval_order > 1) {
                                                            for ($order = 1; $order < $plan->approval_order; $order++) {
                                                                $previousApproval = $allApprovalPlans
                                                                    ->where('approval_order', $order)
                                                                    ->first();
                                                                if (
                                                                    !$previousApproval ||
                                                                    $previousApproval->status !== 1
                                                                ) {
                                                                    $canProcess = false;
                                                                    break;
                                                                }
                                                            }
                                                        }
                                                    @endphp

                                                    @if ($canProcess)
                                                        <div class="current-step-indicator">
                                                            <i class="fas fa-arrow-right"></i> Your turn to review
                                                        </div>
                                                    @else
                                                        <div class="current-step-waiting">
                                                            <i class="fas fa-clock"></i> Waiting for previous approvals
                                                        </div>
                                                    @endif
                                                @endif
                                            </div>
                                        </div>

                                        @if ($index < count($allApprovalPlans) - 1)
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </div>
@endsection

@section('styles')
    <style>
        .content-wrapper-custom {
            background-color: #f8fafc;
            min-height: 100vh;
            padding-bottom: 40px;
        }

        /* Header */
        .document-header {
            position: relative;
            height: 110px;
            color: white;
            padding: 18px 25px;
            margin-bottom: 25px;
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .document-header-content {
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .document-type {
            font-size: 13px;
            margin-bottom: 4px;
            opacity: 0.9;
            letter-spacing: 1px;
        }

        .document-number {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .document-date {
            font-size: 14px;
            opacity: 0.9;
        }

        .document-status-pill {
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

        .status-pending-approval {
            background-color: #e67e22;
            color: #ffffff;
        }

        /* Content Styles */
        .document-content {
            padding: 0 15px;
        }

        .document-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .approval-status-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        /* Current Approval Summary Styles */
        .current-approval-summary {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            border-left: 4px solid #3498db;
        }

        .current-status-display {
            margin-top: 15px;
        }

        .status-overview {
            text-align: center;
        }

        .status-badge-large {
            margin-bottom: 20px;
        }

        .status-badge-large .badge {
            font-size: 1.2rem;
            padding: 10px 20px;
            border-radius: 6px;
        }

        .status-details-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .status-detail-item {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 10px;
            background: white;
            border-radius: 6px;
            border: 1px solid #e9ecef;
        }

        .status-detail-item i {
            color: #3498db;
            width: 16px;
        }

        .detail-label {
            font-weight: 600;
            color: #495057;
            font-size: 0.9rem;
        }

        .detail-value {
            color: #2c3e50;
            font-weight: 500;
            margin-left: auto;
        }

        .status-message .alert {
            margin-bottom: 0;
            font-size: 0.9rem;
            border-radius: 6px;
        }

        .approval-status-info {
            padding: 10px 0;
        }

        .status-badge {
            text-align: center;
        }

        .status-badge .badge {
            font-size: 1rem;
            padding: 8px 16px;
        }

        .status-details {
            font-size: 0.9rem;
        }

        .status-item {
            margin-bottom: 12px;
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .status-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .status-item i {
            width: 16px;
            color: #6c757d;
        }

        .status-message .alert {
            margin-bottom: 0;
            font-size: 0.85rem;
        }

        .card-head {
            padding: 15px;
            border-bottom: 1px solid #eee;
        }

        .card-head h2 {
            margin: 0;
            font-size: 1.4rem;
            color: #2c3e50;
        }

        .card-body {
            padding: 15px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            padding: 15px;
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

        .info-meta {
            color: #718096;
            font-size: 0.875rem;
        }

        .purpose-section {
            border-top: 1px solid #eee;
            padding-top: 20px;
        }

        .purpose-section h3 {
            font-size: 1.1rem;
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .purpose-content {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            color: #2c3e50;
            line-height: 1.6;
        }

        .additional-travelers {
            border-top: 1px solid #eee;
            padding-top: 20px;
        }

        .traveler-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .traveler-item {
            background: #f8f9fa;
            padding: 10px 15px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Traveler Info Card Styles */
        .traveler-info-card {
            overflow: hidden;
        }

        .traveler-details {
            padding: 15px;
        }

        .traveler-detail-item {
            display: flex;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid #edf2f7;
        }

        .traveler-detail-item:last-child {
            border-bottom: none;
        }

        .detail-icon {
            color: #3498db;
            margin-right: 12px;
            font-size: 16px;
        }

        .detail-content {
            flex: 1;
        }

        .detail-label {
            font-size: 12px;
            color: #777;
            margin-bottom: 4px;
        }

        .detail-value {
            font-weight: 600;
            color: #333;
        }

        /* Followers Info Card Styles */
        .followers-info-card {
            overflow: hidden;
        }

        .followers-count {
            background: #3498db;
            color: white;
            font-size: 14px;
            border-radius: 4px;
            padding: 2px 8px;
            margin-left: 8px;
        }

        .followers-list {
            max-height: 400px;
            overflow-y: auto;
        }

        .follower-item {
            padding: 15px;
            border-bottom: 1px solid #edf2f7;
        }

        .follower-name {
            font-size: 16px;
            font-weight: 500;
            color: #2c3e50;
            margin-bottom: 4px;
        }

        .follower-position {
            font-size: 15px;
            color: #64748b;
            margin-bottom: 6px;
        }

        .follower-meta {
            display: flex;
            gap: 15px;
            font-size: 14px;
            color: #64748b;
            margin-bottom: 6px;
        }

        .follower-nik,
        .follower-department {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .follower-project {
            font-size: 14px;
            color: #64748b;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .follower-meta i,
        .follower-project i {
            font-size: 14px;
            width: 16px;
            text-align: center;
        }

        /* Status Grid */
        .status-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .status-item {
            display: flex;
            align-items: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .status-icon {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: white;
        }

        .status-content {
            flex: 1;
        }

        .status-label {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }

        .status-value {
            font-weight: 600;
            color: #2c3e50;
        }

        .status-meta {
            margin-top: 5px;
        }

        /* Decision Buttons */
        .decision-buttons {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
        }

        .decision-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .decision-header h3 {
            font-size: 1.1rem;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .decision-header p {
            color: #666;
            font-size: 0.9rem;
            margin: 0;
        }

        .decision-options {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }

        .decision-btn {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 12px;
            text-align: left;
        }

        .decision-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .decision-btn.active {
            border-color: #3498db;
            background: #ebf8ff;
        }

        .decision-btn.approve-btn.active {
            border-color: #27ae60;
            background: #d4edda;
        }

        .decision-btn.reject-btn.active {
            border-color: #e74c3c;
            background: #f8d7da;
        }

        .btn-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: white;
        }

        .approve-btn .btn-icon {
            background: #27ae60;
        }

        .reject-btn .btn-icon {
            background: #e74c3c;
        }

        .btn-content {
            flex: 1;
        }

        .btn-title {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 2px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 500;
        }

        .form-control {
            width: 100%;
            padding: 10px 15px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            border-color: #3498db;
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
            outline: none;
        }

        .form-control::placeholder {
            color: #95a5a6;
        }

        .text-danger {
            color: #e74c3c;
        }

        .form-actions {
            margin-top: 30px;
        }

        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        .badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            font-weight: 500;
        }

        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }

        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }

        /* Approval Status New Styles */
        .section-title {
            font-size: 1.1rem;
            color: #2c3e50;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
        }

        .section-title i {
            color: #3498db;
        }

        /* Submitter Section */
        .submitter-info {
            display: flex;
            align-items: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #3498db;
        }

        .submitter-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #3498db;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: white;
            font-size: 1.5rem;
        }

        .submitter-details {
            flex: 1;
        }

        .submitter-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 5px;
        }

        .submitter-meta {
            display: flex;
            gap: 20px;
            font-size: 0.9rem;
            color: #6c757d;
        }

        .submit-date,
        .submit-status {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        /* Approval Flow */
        .approval-flow {
            position: relative;
        }

        .approval-step {
            display: flex;
            align-items: flex-start;
            margin-bottom: 20px;
            position: relative;
        }

        .step-number {
            width: 35px;
            height: 35px;
            border-radius: 50%;
            background: #e9ecef;
            color: #6c757d;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-right: 15px;
            flex-shrink: 0;
            border: 2px solid #dee2e6;
        }

        .current-step .step-number {
            background: #3498db;
            color: white;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }

        .waiting-step .step-number {
            background: #f39c12;
            color: white;
            border-color: #f39c12;
        }

        .completed-step .step-number {
            background: #27ae60;
            color: white;
            border-color: #27ae60;
        }

        /* Specific status step number styling */
        .step-approved .step-number {
            background: #27ae60;
            color: white;
            border-color: #27ae60;
        }

        .step-rejected .step-number {
            background: #e74c3c;
            color: white;
            border-color: #e74c3c;
        }

        .step-waiting .step-number {
            background: #f39c12;
            color: white;
            border-color: #f39c12;
        }

        .step-ready .step-number {
            background: #3498db;
            color: white;
            border-color: #3498db;
            box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.2);
        }

        .step-pending .step-number {
            background: #e9ecef;
            color: #6c757d;
            border-color: #dee2e6;
        }

        .step-content {
            flex: 1;
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            border-left: 4px solid #dee2e6;
        }

        .current-step .step-content {
            border-left-color: #3498db;
            background: #e3f2fd;
        }

        .waiting-step .step-content {
            border-left-color: #f39c12;
            background: #fff3cd;
        }

        .completed-step .step-content {
            border-left-color: #27ae60;
        }

        /* Specific status step content styling */
        .step-approved .step-content {
            border-left-color: #27ae60;
            background: #d4edda;
        }

        .step-rejected .step-content {
            border-left-color: #e74c3c;
            background: #f8d7da;
        }

        .step-waiting .step-content {
            border-left-color: #f39c12;
            background: #fff3cd;
        }

        .step-ready .step-content {
            border-left-color: #3498db;
            background: #e3f2fd;
        }

        .step-pending .step-content {
            border-left-color: #dee2e6;
            background: #f8f9fa;
        }

        .step-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .approver-name {
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 2px;
        }

        .approver-role {
            font-size: 0.85rem;
            color: #6c757d;
        }

        .status-badge {
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .status-badge.pending {
            background: #fff3cd;
            color: #856404;
        }

        .status-badge.approved {
            background: #d4edda;
            color: #155724;
        }

        .status-badge.rejected {
            background: #f8d7da;
            color: #721c24;
        }

        .step-remarks {
            margin-top: 10px;
            padding: 10px;
            background: white;
            border-radius: 6px;
            border-left: 3px solid #3498db;
        }

        .remarks-label {
            font-size: 0.85rem;
            color: #6c757d;
            margin-bottom: 5px;
            font-weight: 500;
        }

        .remarks-content {
            color: #2c3e50;
            margin-bottom: 5px;
            line-height: 1.4;
        }

        .remarks-time {
            font-size: 0.8rem;
            color: #95a5a6;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .current-step-indicator {
            margin-top: 8px;
            padding: 6px 12px;
            background: #3498db;
            color: white;
            border-radius: 4px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 5px;
            font-weight: 500;
        }

        .current-step-waiting {
            margin-top: 8px;
            padding: 6px 12px;
            background: #f39c12;
            color: white;
            border-radius: 4px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 5px;
            font-weight: 500;
        }

        /* Status Summary */
        .status-summary {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(120px, 1fr));
            gap: 15px;
        }

        .summary-item {
            display: flex;
            align-items: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            text-align: center;
        }

        .summary-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 12px;
            color: white;
            font-size: 1.1rem;
        }

        .summary-icon.approved {
            background: #27ae60;
        }

        .summary-icon.rejected {
            background: #e74c3c;
        }

        .summary-icon.pending {
            background: #f39c12;
        }

        .summary-icon.total {
            background: #3498db;
        }

        .summary-count {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 2px;
        }

        .summary-label {
            font-size: 0.85rem;
            color: #6c757d;
            font-weight: 500;
        }

        @media (max-width: 768px) {
            .document-header {
                height: auto;
                padding: 15px;
            }

            .document-number {
                font-size: 20px;
            }

            .document-status-pill {
                position: static;
                margin-top: 10px;
                align-self: flex-start;
            }

            .info-grid {
                grid-template-columns: 1fr;
                padding: 15px;
            }

            .status-grid {
                grid-template-columns: 1fr;
            }

            .traveler-list {
                grid-template-columns: 1fr;
            }

            .followers-list {
                max-height: 300px;
            }

            .info-grid {
                grid-template-columns: 1fr;
                padding: 15px;
            }

            .decision-options {
                grid-template-columns: 1fr 1fr;
            }

            .requirements-grid {
                grid-template-columns: 1fr;
            }
        }

        /* Requirements Card */
        .requirements-card {
            margin-top: 20px;
        }

        /* Requirements Grid */
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

        /* Section Headers for Requirements */
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
    </style>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const approveBtn = document.querySelector('.approve-btn');
            const rejectBtn = document.querySelector('.reject-btn');
            const statusInput = document.getElementById('status');
            const submitBtn = document.querySelector('.submit-btn');
            const remarkTextarea = document.getElementById('remarks');

            // Handle decision button clicks
            function selectDecision(status) {
                // Remove active class from all buttons
                approveBtn.classList.remove('active');
                rejectBtn.classList.remove('active');

                // Add active class to selected button
                if (status === '1') {
                    approveBtn.classList.add('active');
                } else if (status === '2') {
                    rejectBtn.classList.add('active');
                }

                // Update hidden input
                statusInput.value = status;

                // Enable submit button if remark is filled
                checkFormValidity();
            }

            // Handle button clicks
            approveBtn.addEventListener('click', function() {
                selectDecision('1');
            });

            rejectBtn.addEventListener('click', function() {
                selectDecision('2');
            });

            // Check form validity
            function checkFormValidity() {
                const hasStatus = statusInput.value !== '';
                const hasRemark = remarkTextarea.value.trim() !== '';

                if (hasStatus && hasRemark) {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('btn-secondary');
                    submitBtn.classList.add('btn-success');
                } else {
                    submitBtn.disabled = true;
                    submitBtn.classList.remove('btn-success');
                    submitBtn.classList.add('btn-secondary');
                }
            }

            // Listen for remark changes
            remarkTextarea.addEventListener('input', checkFormValidity);

            // Form submission
            document.getElementById('approvalForm').addEventListener('submit', function(e) {
                if (!statusInput.value) {
                    e.preventDefault();
                    alert('Please select a decision first.');
                    return;
                }

                if (!remarkTextarea.value.trim()) {
                    e.preventDefault();
                    alert('Please provide approval notes.');
                    return;
                }

                const status = statusInput.value;
                let confirmMessage = '';

                if (status === '1') {
                    confirmMessage = 'Are you sure you want to approve this request?';
                } else if (status === '2') {
                    confirmMessage = 'Are you sure you want to reject this request?';
                }

                if (!confirm(confirmMessage)) {
                    e.preventDefault();
                }
            });
        });
    </script>
@endsection
