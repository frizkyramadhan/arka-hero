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
                @php
                    $statusMap = [
                        'draft' => ['label' => 'Draft', 'class' => 'badge badge-secondary', 'icon' => 'fa-edit'],
                        'submitted' => [
                            'label' => 'Submitted',
                            'class' => 'badge badge-info',
                            'icon' => 'fa-paper-plane',
                        ],
                        'approved' => ['label' => 'Open', 'class' => 'badge badge-success', 'icon' => 'fa-plane'],
                        'rejected' => [
                            'label' => 'Rejected',
                            'class' => 'badge badge-danger',
                            'icon' => 'fa-times-circle',
                        ],
                        'closed' => [
                            'label' => 'Closed',
                            'class' => 'badge badge-primary',
                            'icon' => 'fa-check-circle',
                        ],
                        'cancelled' => ['label' => 'Cancelled', 'class' => 'badge badge-warning', 'icon' => 'fa-ban'],
                    ];
                    $status = $officialtravel->status;
                    $pill = $statusMap[$status] ?? [
                        'label' => ucfirst($status),
                        'class' => 'badge badge-secondary',
                        'icon' => 'fa-question-circle',
                    ];
                @endphp
                <div class="travel-status-pill">
                    <span class="{{ $pill['class'] }}">
                        <i class="fas {{ $pill['icon'] }}"></i> {{ $pill['label'] }}
                    </span>
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
                                            {{ $officialtravel->traveler->position->position_name ?? 'N/A' }}
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
                                        <div class="info-meta">Duration: {{ $officialtravel->duration }}</div>
                                    </div>
                                </div>

                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #f1c40f;">
                                        <i class="fas fa-calendar-check"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Current Status</div>
                                        <div class="info-value">
                                            @php
                                                $currentStatus = $officialtravel->getCurrentStopStatus();
                                                $statusLabels = [
                                                    'no_stops' => 'No stops recorded',
                                                    'complete' => 'Latest stop complete',
                                                    'arrival_only' => 'Waiting for departure',
                                                    'departure_only' => 'Departure only recorded',
                                                    'unknown' => 'Unknown status',
                                                ];
                                            @endphp
                                            {{ $statusLabels[$currentStatus] ?? 'Unknown' }}
                                        </div>
                                        <div class="info-meta">
                                            @if ($officialtravel->stops->count() > 0)
                                                {{ $officialtravel->stops->count() }} stop(s) recorded
                                            @else
                                                First arrival
                                            @endif
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
                                            {{ $officialtravel->transportation->transportation_name ?? 'N/A' }}</div>
                                        <div class="info-meta">
                                            {{ $officialtravel->accommodation->accommodation_name ?? 'N/A' }}</div>
                                    </div>
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
                </div>

                <!-- Arrival Form -->
                <div class="col-lg-4">
                    <form action="{{ route('officialtravels.arrivalStamp', $officialtravel->id) }}" method="POST">
                        @csrf
                        <div class="travel-card arrival-card">
                            <div class="card-head">
                                <h2><i class="fas fa-plane-arrival"></i> Arrival Check</h2>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="arrival_at_destination">Arrival Date & Time <span
                                            class="text-danger">*</span></label>
                                    <input type="datetime-local"
                                        class="form-control @error('arrival_at_destination') is-invalid @enderror"
                                        name="arrival_at_destination" id="arrival_at_destination"
                                        value="{{ old('arrival_at_destination', $officialtravel->arrival_at_destination ? date('Y-m-d\TH:i', strtotime($officialtravel->arrival_at_destination)) : date('Y-m-d\TH:i')) }}"
                                        required>
                                    @error('arrival_at_destination')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mt-4">
                                    <label for="arrival_remark">
                                        Arrival Notes <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control @error('arrival_remark') is-invalid @enderror" name="arrival_remark" id="arrival_remark"
                                        rows="4"
                                        placeholder="Please provide arrival details:
• Actual arrival time
• Any delays encountered
• Special circumstances
• Additional notes or observations"
                                        required>{{ old('arrival_remark', $officialtravel->arrival_remark) }}</textarea>
                                    @error('arrival_remark')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary btn-block"
                                        onclick="return confirm('Are you sure you want to confirm the arrival? This action cannot be undone.')">
                                        <i class="fas fa-check-circle"></i>
                                        Confirm Arrival
                                    </button>
                                    <a href="{{ route('officialtravels.show', $officialtravel->id) }}"
                                        class="btn btn-secondary btn-block mt-2">
                                        <i class="fas fa-times"></i>
                                        Cancel
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
        }

        .travel-status-pill .badge {
            font-size: 0.875rem;
            padding: 0.5rem 0.75rem;
            border-radius: 0.375rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
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

            .traveler-list {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection
