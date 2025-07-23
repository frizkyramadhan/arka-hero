@extends('layouts.main')

@section('content')
    <div class="content-wrapper-custom">
        <!-- FPTK Header -->
        <div class="fptk-header">
            <div class="fptk-header-content">
                <div class="fptk-number">{{ $fptk->project->project_name }}</div>
                <h1 class="fptk-destination">{{ $fptk->request_number }}</h1>
                <div class="fptk-date">
                    <i class="far fa-calendar-alt"></i> {{ date('d F Y', strtotime($fptk->required_date)) }}
                </div>
                <div class="fptk-status-pill status-pending">
                    <i class="fas fa-user-check"></i>
                    Pending HR Acknowledgment
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="fptk-content">
            <div class="row">
                <!-- FPTK Details -->
                <div class="col-lg-8">
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
                </div>

                <!-- HR Acknowledgment Form -->
                <div class="col-lg-4">
                    <div class="fptk-card acknowledgment-card">
                        <div class="card-head">
                            <h2><i class="fas fa-user-check"></i> HR Acknowledgment Decision</h2>
                        </div>
                        <div class="card-body">
                            <!-- Decision Buttons -->
                            <div class="decision-buttons mb-4">
                                <div class="decision-header">
                                    <h3>Choose Your Decision</h3>
                                    <p>Select one of the options below to proceed</p>
                                </div>
                                <div class="decision-options">
                                    <button type="button" class="decision-btn approve-btn" data-status="approved">
                                        <div class="btn-icon">
                                            <i class="fas fa-thumbs-up"></i>
                                        </div>
                                        <div class="btn-content">
                                            <div class="btn-title">Approve</div>
                                        </div>
                                    </button>
                                    <button type="button" class="decision-btn reject-btn" data-status="rejected">
                                        <div class="btn-icon">
                                            <i class="fas fa-thumbs-down"></i>
                                        </div>
                                        <div class="btn-content">
                                            <div class="btn-title">Reject</div>
                                        </div>
                                    </button>
                                </div>
                            </div>

                            <!-- Acknowledgment Form -->
                            <form action="{{ route('recruitment.requests.acknowledge', $fptk->id) }}" method="POST"
                                id="acknowledgmentForm">
                                @csrf
                                <input type="hidden" name="acknowledgment_status" id="acknowledgment_status"
                                    value="">

                                <div class="form-group">
                                    <label for="acknowledgment_remark">Acknowledgment Notes <span
                                            class="text-danger">*</span></label>
                                    <textarea class="form-control @error('acknowledgment_remark') is-invalid @enderror" name="acknowledgment_remark"
                                        id="acknowledgment_remark" rows="4"
                                        placeholder="Please provide your acknowledgment details:
• Decision rationale
• Any conditions or requirements
• Additional notes or observations"
                                        required>{{ old('acknowledgment_remark') }}</textarea>
                                    @error('acknowledgment_remark')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary btn-block submit-btn" disabled>
                                        <i class="fas fa-paper-plane"></i>
                                        Submit Acknowledgment
                                    </button>
                                    <a href="{{ route('recruitment.requests.show', $fptk->id) }}"
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

        .fptk-number {
            font-size: 13px;
            margin-bottom: 4px;
            opacity: 0.9;
            letter-spacing: 1px;
        }

        .fptk-destination {
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

        .status-pending {
            background-color: #f39c12;
            color: #ffffff;
        }

        /* Content Styles */
        .fptk-content {
            padding: 0 20px;
        }

        .fptk-card {
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
            font-size: 1.2rem;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 10px;
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

        .btn-subtitle {
            font-size: 0.8rem;
            color: #666;
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

        @media (max-width: 768px) {
            .fptk-header {
                height: auto;
                padding: 15px;
            }

            .fptk-destination {
                font-size: 20px;
            }

            .fptk-status-pill {
                position: static;
                margin-top: 10px;
                align-self: flex-start;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }

            .requirements-grid {
                grid-template-columns: 1fr;
            }

            .decision-options {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const approveBtn = document.querySelector('.approve-btn');
            const rejectBtn = document.querySelector('.reject-btn');
            const statusInput = document.getElementById('acknowledgment_status');
            const submitBtn = document.querySelector('.submit-btn');
            const remarkTextarea = document.getElementById('acknowledgment_remark');

            function selectDecision(status) {
                approveBtn.classList.remove('active');
                rejectBtn.classList.remove('active');

                if (status === 'approved') {
                    approveBtn.classList.add('active');
                } else if (status === 'rejected') {
                    rejectBtn.classList.add('active');
                }

                statusInput.value = status;
                checkFormValidity();
            }

            approveBtn.addEventListener('click', function() {
                selectDecision('approved');
            });

            rejectBtn.addEventListener('click', function() {
                selectDecision('rejected');
            });

            function checkFormValidity() {
                const hasStatus = statusInput.value !== '';
                const hasRemark = remarkTextarea.value.trim() !== '';

                if (hasStatus && hasRemark) {
                    submitBtn.disabled = false;
                    submitBtn.classList.remove('btn-secondary');
                    submitBtn.classList.add('btn-primary');
                } else {
                    submitBtn.disabled = true;
                    submitBtn.classList.remove('btn-primary');
                    submitBtn.classList.add('btn-secondary');
                }
            }

            remarkTextarea.addEventListener('input', checkFormValidity);

            document.getElementById('acknowledgmentForm').addEventListener('submit', function(e) {
                if (!statusInput.value) {
                    e.preventDefault();
                    alert('Please select a decision first.');
                    return;
                }

                if (!remarkTextarea.value.trim()) {
                    e.preventDefault();
                    alert('Please provide acknowledgment notes.');
                    return;
                }

                const status = statusInput.value;
                const confirmMessage = status === 'approved' ?
                    'Are you sure you want to acknowledge this FPTK?' :
                    'Are you sure you want to reject this FPTK?';

                if (!confirm(confirmMessage)) {
                    e.preventDefault();
                }
            });
        });
    </script>
@endsection
