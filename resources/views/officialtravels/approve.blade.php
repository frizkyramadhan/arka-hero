@extends('layouts.main')

@section('title', $title)
@section('subtitle', $subtitle)

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
                <div
                    class="travel-status-pill {{ $officialtravel->approval_status == 'pending' ? 'status-draft' : ($officialtravel->approval_status == 'approved' ? 'status-open' : 'status-closed') }}">
                    <i
                        class="fas {{ $officialtravel->approval_status == 'pending' ? 'fa-clock' : ($officialtravel->approval_status == 'approved' ? 'fa-check-circle' : 'fa-times-circle') }}"></i>
                    {{ ucfirst($officialtravel->approval_status) }}
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
                                        <div class="info-label">Traveler</div>
                                        <div class="info-value">{{ $officialtravel->traveler->employee->fullname ?? 'N/A' }}
                                        </div>
                                    </div>
                                </div>

                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #e74c3c;">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Destination</div>
                                        <div class="info-value">{{ $officialtravel->destination }}</div>
                                    </div>
                                </div>

                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #9b59b6;">
                                        <i class="fas fa-tasks"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Purpose</div>
                                        <div class="info-value">{{ $officialtravel->purpose }}</div>
                                    </div>
                                </div>

                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #f1c40f;">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Duration</div>
                                        <div class="info-value">{{ $officialtravel->duration }}</div>
                                    </div>
                                </div>

                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #1abc9c;">
                                        <i class="fas fa-bus"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Transportation</div>
                                        <div class="info-value">
                                            {{ $officialtravel->transportation->transportation_name ?? 'N/A' }}</div>
                                    </div>
                                </div>

                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #e67e22;">
                                        <i class="fas fa-hotel"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Accommodation</div>
                                        <div class="info-value">
                                            {{ $officialtravel->accommodation->accommodation_name ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </div>

                            @if ($officialtravel->details->count() > 0)
                                <div class="additional-travelers mt-4">
                                    <h3 class="mb-3"><i class="fas fa-users"></i> Additional Travelers</h3>
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
                </div>

                <!-- Approval Form -->
                <div class="col-lg-4">
                    <form action="{{ route('officialtravels.approve', $officialtravel->id) }}" method="POST">
                        @csrf
                        <div class="travel-card recommendation-card">
                            <div class="card-head">
                                <h2><i class="fas fa-stamp"></i> Make Approval</h2>
                            </div>
                            <div class="card-body">
                                <div class="recommendation-options mb-4">
                                    <label class="d-block mb-3">Approval Status <span class="text-danger">*</span></label>
                                    <div class="recommendation-buttons">
                                        <label class="recommendation-btn approve">
                                            <input type="radio" name="approval_status" value="approved"
                                                {{ old('approval_status') == 'approved' || $officialtravel->approval_status == 'approved' ? 'checked' : '' }}
                                                required>
                                            <i class="fas fa-check-circle"></i> Approve
                                        </label>
                                        <label class="recommendation-btn reject">
                                            <input type="radio" name="approval_status" value="rejected"
                                                {{ old('approval_status') == 'rejected' || $officialtravel->approval_status == 'rejected' ? 'checked' : '' }}>
                                            <i class="fas fa-times-circle"></i> Reject
                                        </label>
                                    </div>
                                    @error('approval_status')
                                        <div class="text-danger mt-2">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="approval_date">Approval Date <span class="text-danger">*</span></label>
                                    <input type="datetime-local"
                                        class="form-control @error('approval_date') is-invalid @enderror"
                                        name="approval_date" id="approval_date"
                                        value="{{ old('approval_date', $officialtravel->approval_date ? date('Y-m-d\TH:i', strtotime($officialtravel->approval_date)) : date('Y-m-d\TH:i')) }}"
                                        required>
                                    @error('approval_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="approval_remark">Remarks <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('approval_remark') is-invalid @enderror" name="approval_remark"
                                        id="approval_remark" rows="4" placeholder="Enter your approval remarks" required>{{ old('approval_remark') ?? $officialtravel->approval_remark }}</textarea>
                                    @error('approval_remark')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <i class="fas fa-save"></i> Submit Approval
                                    </button>
                                    <a href="{{ route('officialtravels.show', $officialtravel->id) }}"
                                        class="btn btn-secondary btn-block mt-2">
                                        <i class="fas fa-times-circle"></i> Cancel
                                    </a>
                                </div>
                            </div>
                        </div>
                    </form>
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

        .status-draft {
            background-color: #f1c40f;
            color: #000000;
        }

        .status-open {
            background-color: #2ecc71;
            color: #ffffff;
        }

        .status-closed {
            background-color: #e74c3c;
            color: #ffffff;
        }

        /* Content Styles */
        .travel-content {
            padding: 0 20px;
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

        .recommendation-options {
            text-align: center;
        }

        .recommendation-buttons {
            display: flex;
            gap: 15px;
            justify-content: center;
        }

        .recommendation-btn {
            flex: 1;
            padding: 15px;
            border: 2px solid #eee;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            position: relative;
        }

        .recommendation-btn input[type="radio"] {
            position: absolute;
            opacity: 0;
        }

        .recommendation-btn.approve:hover,
        .recommendation-btn.approve input[type="radio"]:checked~* {
            background: #2ecc71;
            color: white;
            border-color: #27ae60;
        }

        .recommendation-btn.reject:hover,
        .recommendation-btn.reject input[type="radio"]:checked~* {
            background: #e74c3c;
            color: white;
            border-color: #c0392b;
        }

        /* Add active state styles */
        .recommendation-btn.approve.active {
            background: #2ecc71;
            color: white;
            border-color: #27ae60;
        }

        .recommendation-btn.reject.active {
            background: #e74c3c;
            color: white;
            border-color: #c0392b;
        }

        .form-actions {
            margin-top: 30px;
        }
    </style>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const radioButtons = document.querySelectorAll('input[name="approval_status"]');

            // Set initial state
            radioButtons.forEach(radio => {
                if (radio.checked) {
                    radio.closest('.recommendation-btn').classList.add('active');
                }
            });

            // Handle changes
            radioButtons.forEach(radio => {
                radio.addEventListener('change', function() {
                    // Remove active class from all buttons
                    document.querySelectorAll('.recommendation-btn').forEach(btn => {
                        btn.classList.remove('active');
                    });

                    // Add active class to selected button
                    if (this.checked) {
                        this.closest('.recommendation-btn').classList.add('active');
                    }
                });
            });
        });
    </script>
@endsection
