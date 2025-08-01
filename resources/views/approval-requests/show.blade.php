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
                    <div class="document-card document-info-card">
                        <div class="card-head">
                            <h2><i class="fas fa-info-circle"></i> Document Details</h2>
                        </div>
                        <div class="card-body">
                            @if ($approvalPlan->document_type === 'officialtravel')
                                @php $document = App\Models\Officialtravel::find($approvalPlan->document_id); @endphp
                                <div class="info-grid">
                                    <div class="info-item">
                                        <div class="info-icon" style="background-color: #3498db;">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div class="info-content">
                                            <div class="info-label">Main Traveler</div>
                                            <div class="info-value">{{ $document->traveler->employee->fullname ?? 'N/A' }}
                                            </div>
                                            <div class="info-meta">
                                                {{ $document->traveler->position->position_name ?? 'N/A' }}</div>
                                        </div>
                                    </div>

                                    <div class="info-item">
                                        <div class="info-icon" style="background-color: #e74c3c;">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </div>
                                        <div class="info-content">
                                            <div class="info-label">Destination</div>
                                            <div class="info-value">{{ $document->destination }}</div>
                                            <div class="info-meta">Duration: {{ $document->duration }}</div>
                                        </div>
                                    </div>

                                    <div class="info-item">
                                        <div class="info-icon" style="background-color: #f1c40f;">
                                            <i class="fas fa-calendar-check"></i>
                                        </div>
                                        <div class="info-content">
                                            <div class="info-label">Departure Date</div>
                                            <div class="info-value">
                                                {{ date('d M Y', strtotime($document->departure_from)) }}</div>
                                            <div class="info-meta">Expected Return: {{ $document->arrival_at_destination }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="info-item">
                                        <div class="info-icon" style="background-color: #27ae60;">
                                            <i class="fas fa-plane"></i>
                                        </div>
                                        <div class="info-content">
                                            <div class="info-label">Transportation</div>
                                            <div class="info-value">
                                                {{ $document->transportation->transportation_name ?? 'N/A' }}</div>
                                            <div class="info-meta">
                                                {{ $document->accommodation->accommodation_name ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="purpose-section mt-4">
                                    <h3><i class="fas fa-bullseye"></i> Travel Purpose</h3>
                                    <div class="purpose-content">
                                        {{ $document->purpose }}
                                    </div>
                                </div>

                                @if ($document->details && $document->details->count() > 0)
                                    <div class="additional-travelers mt-4">
                                        <h3><i class="fas fa-users"></i> Accompanying Travelers</h3>
                                        <div class="traveler-list">
                                            @foreach ($document->details as $detail)
                                                <div class="traveler-item">
                                                    <i class="fas fa-user"></i>
                                                    <span>{{ $detail->follower->employee->fullname ?? 'Unknown' }}</span>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @elseif($approvalPlan->document_type === 'recruitment_request')
                                @php $document = App\Models\RecruitmentRequest::find($approvalPlan->document_id); @endphp
                                <div class="info-grid">
                                    <div class="info-item">
                                        <div class="info-icon" style="background-color: #3498db;">
                                            <i class="fas fa-user-tie"></i>
                                        </div>
                                        <div class="info-content">
                                            <div class="info-label">Position Title</div>
                                            <div class="info-value">{{ $document->position->position_name }}</div>
                                            <div class="info-meta">Quantity: {{ $document->required_qty }}</div>
                                        </div>
                                    </div>

                                    <div class="info-item">
                                        <div class="info-icon" style="background-color: #e74c3c;">
                                            <i class="fas fa-building"></i>
                                        </div>
                                        <div class="info-content">
                                            <div class="info-label">Department</div>
                                            <div class="info-value">{{ $document->department->department_name ?? 'N/A' }}
                                            </div>
                                            <div class="info-meta">{{ $document->project->project_name ?? 'N/A' }}</div>
                                        </div>
                                    </div>

                                    <div class="info-item">
                                        <div class="info-icon" style="background-color: #27ae60;">
                                            <i class="fas fa-layer-group"></i>
                                        </div>
                                        <div class="info-content">
                                            <div class="info-label">Level</div>
                                            <div class="info-value">{{ $document->level->name }}</div>
                                            <div class="info-meta">Type: {{ $document->employment_type }}</div>
                                        </div>
                                    </div>

                                    <div class="info-item">
                                        <div class="info-icon" style="background-color: #f1c40f;">
                                            <i class="fas fa-calendar-check"></i>
                                        </div>
                                        <div class="info-content">
                                            <div class="info-label">Required Date</div>
                                            <div class="info-value">
                                                {{ date('d M Y', strtotime($document->required_date)) }}</div>
                                            <div class="info-meta">Reason:
                                                {{ $document->request_reason == 'other' ? $document->other_reason : ucfirst(str_replace('_', ' ', $document->request_reason)) }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @if ($document->job_description)
                                    <div class="purpose-section mt-4">
                                        <h3><i class="fas fa-bullseye"></i> Job Description</h3>
                                        <div class="purpose-content">
                                            {{ $document->job_description }}
                                        </div>
                                    </div>
                                @endif
                            @endif
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
                                            <span class="submit-status">
                                                <i class="fas fa-clock"></i>
                                                Pending Approval
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
                                        ->orderBy('id', 'asc')
                                        ->get();
                                @endphp

                                <div class="approval-flow">
                                    @foreach ($allApprovalPlans as $index => $plan)
                                        <div
                                            class="approval-step {{ $plan->id === $approvalPlan->id ? 'current-step' : '' }} {{ $plan->status !== 0 ? 'completed-step' : '' }}">
                                            <div class="step-number">{{ $index + 1 }}</div>
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
                                                    <div class="current-step-indicator">
                                                        <i class="fas fa-arrow-right"></i> Your turn to review
                                                    </div>
                                                @endif
                                            </div>
                                        </div>

                                        @if ($index < count($allApprovalPlans) - 1)
                                        @endif
                                    @endforeach
                                </div>
                            </div>

                            <!-- Document Status Summary -->
                            {{-- <div class="status-summary-section mt-4">
                                <h3 class="section-title">
                                    <i class="fas fa-chart-pie"></i> Status Summary
                                </h3>
                                <div class="status-summary">
                                    @php
                                        $totalApprovers = $allApprovalPlans->count();
                                        $approvedCount = $allApprovalPlans->where('status', 1)->count();
                                        $rejectedCount = $allApprovalPlans->where('status', 2)->count();
                                        $pendingCount = $allApprovalPlans->where('status', 0)->count();
                                    @endphp

                                    <div class="summary-item">
                                        <div class="summary-icon approved">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                        <div class="summary-content">
                                            <div class="summary-count">{{ $approvedCount }}</div>
                                            <div class="summary-label">Approved</div>
                                        </div>
                                    </div>

                                    <div class="summary-item">
                                        <div class="summary-icon rejected">
                                            <i class="fas fa-times-circle"></i>
                                        </div>
                                        <div class="summary-content">
                                            <div class="summary-count">{{ $rejectedCount }}</div>
                                            <div class="summary-label">Rejected</div>
                                        </div>
                                    </div>

                                    <div class="summary-item">
                                        <div class="summary-icon pending">
                                            <i class="fas fa-clock"></i>
                                        </div>
                                        <div class="summary-content">
                                            <div class="summary-count">{{ $pendingCount }}</div>
                                            <div class="summary-label">Pending</div>
                                        </div>
                                    </div>

                                    <div class="summary-item">
                                        <div class="summary-icon total">
                                            <i class="fas fa-users"></i>
                                        </div>
                                        <div class="summary-content">
                                            <div class="summary-count">{{ $totalApprovers }}</div>
                                            <div class="summary-label">Total</div>
                                        </div>
                                    </div>
                                </div>
                            </div> --}}
                        </div>
                    </div>
                </div>

                <!-- Approval Form -->
                <div class="col-lg-4">
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
            height: 120px;
            color: white;
            padding: 20px 30px;
            margin-bottom: 30px;
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
            padding: 0 20px;
        }

        .document-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .card-head {
            padding: 20px;
            border-bottom: 1px solid #eee;
        }

        .card-head h2 {
            margin: 0;
            font-size: 1.4rem;
            color: #2c3e50;
        }

        .card-body {
            padding: 20px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .info-item {
            display: flex;
            align-items: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }

        .info-icon {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: white;
        }

        .info-content {
            flex: 1;
        }

        .info-label {
            color: #666;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }

        .info-value {
            font-weight: 600;
            color: #2c3e50;
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

        .completed-step .step-number {
            background: #27ae60;
            color: white;
            border-color: #27ae60;
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

        .completed-step .step-content {
            border-left-color: #27ae60;
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
            }

            .status-grid {
                grid-template-columns: 1fr;
            }

            .traveler-list {
                grid-template-columns: 1fr;
            }

            .decision-options {
                grid-template-columns: 1fr 1fr;
            }
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
