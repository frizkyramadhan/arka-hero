@extends('layouts.main')

@section('content')
    <div class="content-wrapper-custom">
        <!-- Travel Header -->
        <div class="travel-header">
            <div class="travel-header-content">
                <div class="travel-number">{{ $officialtravel->project->project_name }}</div>
                <h1 class="travel-destination">{{ $officialtravel->official_travel_number }}</h1>
                <div class="travel-date">
                    <i class="far fa-calendar-alt"></i> {{ date('d F Y', strtotime($officialtravel->official_travel_date)) }}
                </div>
                <div class="travel-status-pill status-pending-approval">
                    <i class="fas fa-check-circle"></i>
                    Pending Approval
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="travel-content">
            <div class="row">
                <!-- Travel Details -->
                <div class="col-lg-8">
                    <div class="travel-card travel-info-card">
                        <div class="card-head">
                            <h2><i class="fas fa-info-circle"></i> Travel Details</h2>
                        </div>
                        <div class="card-body">
                            <div class="info-grid">
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #3498db;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Main Traveler</div>
                                        <div class="info-value">{{ $officialtravel->traveler->employee->fullname ?? 'N/A' }}
                                        </div>
                                        <div class="info-meta">
                                            {{ $officialtravel->traveler->position->position_name ?? 'N/A' }}</div>
                                    </div>
                                </div>

                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #e74c3c;">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Destination</div>
                                        <div class="info-value">{{ $officialtravel->destination }}</div>
                                        <div class="info-meta">Duration: {{ $officialtravel->duration }}</div>
                                    </div>
                                </div>

                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #f1c40f;">
                                        <i class="fas fa-calendar-check"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Departure Date</div>
                                        <div class="info-value">
                                            {{ date('d M Y', strtotime($officialtravel->departure_from)) }}</div>
                                        <div class="info-meta">Expected Return:
                                            {{ $officialtravel->arrival_at_destination }}</div>
                                    </div>
                                </div>

                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #27ae60;">
                                        <i class="fas fa-plane"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Transportation</div>
                                        <div class="info-value">
                                            {{ $officialtravel->transportation->transportation_name ?? 'N/A' }}</div>
                                        <div class="info-meta">
                                            {{ $officialtravel->accommodation->accommodation_name ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </div>

                            <div class="purpose-section mt-4">
                                <h3><i class="fas fa-bullseye"></i> Travel Purpose</h3>
                                <div class="purpose-content">
                                    {{ $officialtravel->purpose }}
                                </div>
                            </div>

                            @if ($officialtravel->details->count() > 0)
                                <div class="additional-travelers mt-4">
                                    <h3><i class="fas fa-users"></i> Accompanying Travelers</h3>
                                    <div class="traveler-list">
                                        @foreach ($officialtravel->details as $detail)
                                            <div class="traveler-item">
                                                <i class="fas fa-user"></i>
                                                <span>{{ $detail->follower->employee->fullname ?? 'Unknown' }}</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Recommendation Status -->
                    <div class="travel-card recommendation-status-card">
                        <div class="card-head">
                            <h2><i class="fas fa-user-check"></i> Recommendation Status</h2>
                        </div>
                        <div class="card-body">
                            <div class="status-grid">
                                <div class="status-item">
                                    <div class="status-icon" style="background-color: #f39c12;">
                                        <i class="fas fa-user-check"></i>
                                    </div>
                                    <div class="status-content">
                                        <div class="status-label">Recommender</div>
                                        <div class="status-value">{{ $officialtravel->recommender->name ?? 'N/A' }}</div>
                                        <div class="status-meta">
                                            <span
                                                class="badge badge-{{ $officialtravel->recommendation_status == 'approved' ? 'success' : ($officialtravel->recommendation_status == 'rejected' ? 'danger' : 'warning') }}">
                                                {{ ucfirst($officialtravel->recommendation_status) }}
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                @if ($officialtravel->recommendation_date)
                                    <div class="status-item">
                                        <div class="status-icon" style="background-color: #3498db;">
                                            <i class="fas fa-calendar-alt"></i>
                                        </div>
                                        <div class="status-content">
                                            <div class="status-label">Recommendation Date</div>
                                            <div class="status-value">
                                                {{ date('d M Y H:i', strtotime($officialtravel->recommendation_date)) }}
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            @if ($officialtravel->recommendation_remark)
                                <div class="remark-section mt-4">
                                    <h3><i class="fas fa-comment"></i> Recommendation Notes</h3>
                                    <div class="remark-content">
                                        {{ $officialtravel->recommendation_remark }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Approval Form -->
                <div class="col-lg-4">
                    <div class="travel-card approval-card">
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
                                    <button type="button" class="decision-btn approve-btn" data-status="approved">
                                        <div class="btn-icon">
                                            <i class="fas fa-check-circle"></i>
                                        </div>
                                        <div class="btn-content">
                                            <div class="btn-title">Approve</div>
                                        </div>
                                    </button>
                                    <button type="button" class="decision-btn reject-btn" data-status="rejected">
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
                            <form action="{{ route('officialtravels.approve', $officialtravel->id) }}" method="POST"
                                id="approvalForm">
                                @csrf
                                <input type="hidden" name="approval_status" id="approval_status" value="">

                                <div class="form-group">
                                    <label for="approval_remark">Approval Notes <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('approval_remark') is-invalid @enderror" name="approval_remark"
                                        id="approval_remark" rows="4"
                                        placeholder="Please provide your approval details:
• Decision rationale
• Any conditions or requirements
• Additional notes or observations"
                                        required>{{ old('approval_remark') }}</textarea>
                                    @error('approval_remark')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="btn btn-success btn-block submit-btn" disabled>
                                        <i class="fas fa-paper-plane"></i>
                                        Submit Approval
                                    </button>
                                    <a href="{{ route('officialtravels.show', $officialtravel->id) }}"
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
        .travel-header {
            position: relative;
            height: 120px;
            color: white;
            padding: 20px 30px;
            margin-bottom: 30px;
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .travel-header-content {
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .travel-number {
            font-size: 13px;
            margin-bottom: 4px;
            opacity: 0.9;
            letter-spacing: 1px;
        }

        .travel-destination {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .travel-date {
            font-size: 14px;
            opacity: 0.9;
        }

        .travel-status-pill {
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
        .travel-content {
            padding: 0 20px;
        }

        .travel-card {
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

        .remark-section {
            border-top: 1px solid #eee;
            padding-top: 20px;
        }

        .remark-section h3 {
            font-size: 1.1rem;
            color: #2c3e50;
            margin-bottom: 15px;
        }

        .remark-content {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            color: #2c3e50;
            line-height: 1.6;
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

        @media (max-width: 768px) {
            .travel-header {
                height: auto;
                padding: 15px;
            }

            .travel-destination {
                font-size: 20px;
            }

            .travel-status-pill {
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
            const statusInput = document.getElementById('approval_status');
            const submitBtn = document.querySelector('.submit-btn');
            const remarkTextarea = document.getElementById('approval_remark');

            // Handle decision button clicks
            function selectDecision(status) {
                // Remove active class from all buttons
                approveBtn.classList.remove('active');
                rejectBtn.classList.remove('active');

                // Add active class to selected button
                if (status === 'approved') {
                    approveBtn.classList.add('active');
                } else if (status === 'rejected') {
                    rejectBtn.classList.add('active');
                }

                // Update hidden input
                statusInput.value = status;

                // Enable submit button if remark is filled
                checkFormValidity();
            }

            // Handle button clicks
            approveBtn.addEventListener('click', function() {
                selectDecision('approved');
            });

            rejectBtn.addEventListener('click', function() {
                selectDecision('rejected');
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
                const confirmMessage = status === 'approved' ?
                    'Are you sure you want to approve this travel request? This is the final approval.' :
                    'Are you sure you want to reject this travel request? This will deny the travel.';

                if (!confirm(confirmMessage)) {
                    e.preventDefault();
                }
            });
        });
    </script>
@endsection
