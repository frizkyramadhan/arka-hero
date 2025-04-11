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
                <div
                    class="travel-status-pill {{ $officialtravel->official_travel_status == 'open' ? 'status-open' : 'status-closed' }}">
                    <i class="fas fa-plane-departure"></i>
                    {{ ucfirst($officialtravel->official_travel_status) }}
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
                                        <div class="info-label">Expected Departure</div>
                                        <div class="info-value">{{ $officialtravel->departure_at_destination }}</div>
                                        <div class="info-meta">From:
                                            {{ date('d M Y', strtotime($officialtravel->departure_from)) }}</div>
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

                            <!-- Previous Arrival Info -->
                            <div class="arrival-info mt-4">
                                <h3><i class="fas fa-history"></i> Previous Arrival Information</h3>
                                <div class="info-grid mt-3">
                                    <div class="info-item">
                                        <div class="info-icon" style="background-color: #9b59b6;">
                                            <i class="fas fa-user-check"></i>
                                        </div>
                                        <div class="info-content">
                                            <div class="info-label">Arrival Confirmed By</div>
                                            <div class="info-value">
                                                {{ $officialtravel->arrivalChecker->name ?? 'Unknown' }}</div>
                                            <div class="info-meta">
                                                {{ $officialtravel->arrival_timestamps ? date('d M Y H:i', strtotime($officialtravel->arrival_timestamps)) : 'N/A' }}
                                            </div>
                                        </div>
                                    </div>

                                    <div class="info-item">
                                        <div class="info-icon" style="background-color: #16a085;">
                                            <i class="fas fa-clipboard-list"></i>
                                        </div>
                                        <div class="info-content">
                                            <div class="info-label">Arrival Notes</div>
                                            <div class="info-value">{{ $officialtravel->arrival_remark }}</div>
                                        </div>
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

                <!-- Departure Form -->
                <div class="col-lg-4">
                    <form action="{{ route('officialtravels.departureStamp', $officialtravel->id) }}" method="POST">
                        @csrf
                        <div class="travel-card departure-card">
                            <div class="card-head">
                                <h2><i class="fas fa-plane-departure"></i> Departure Check</h2>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="departure_at_destination">Departure Date & Time <span
                                            class="text-danger">*</span></label>
                                    <input type="datetime-local"
                                        class="form-control @error('departure_at_destination') is-invalid @enderror"
                                        name="departure_at_destination" id="departure_at_destination"
                                        value="{{ old('departure_at_destination', $officialtravel->departure_at_destination ? date('Y-m-d\TH:i', strtotime($officialtravel->departure_at_destination)) : date('Y-m-d\TH:i')) }}"
                                        required>
                                    @error('departure_at_destination')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mt-4">
                                    <label for="departure_remark">
                                        Departure Notes <span class="text-danger">*</span>
                                    </label>
                                    <textarea class="form-control @error('departure_remark') is-invalid @enderror" name="departure_remark"
                                        id="departure_remark" rows="4"
                                        placeholder="Please provide departure details:
• Actual departure time
• Any delays encountered
• Special circumstances
• Additional notes or observations"
                                        required>{{ old('departure_remark', $officialtravel->departure_remark) }}</textarea>
                                    @error('departure_remark')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-actions">
                                    <button type="submit" class="btn btn-primary btn-block"
                                        onclick="return confirm('Are you sure you want to confirm the departure? This action cannot be undone.')">
                                        <i class="fas fa-check-circle"></i>
                                        Confirm Departure
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
            padding: 6px 12px;
            border-radius: 4px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 13px;
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

        .arrival-info h3 {
            font-size: 1.1rem;
            color: #2c3e50;
            margin-bottom: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #eee;
        }

        .arrival-info h3 i {
            margin-right: 8px;
        }

        .additional-travelers {
            border-top: 1px solid #eee;
            padding-top: 20px;
        }

        .additional-travelers h3 {
            font-size: 1.1rem;
            color: #2c3e50;
            margin-bottom: 1rem;
        }

        .additional-travelers h3 i {
            margin-right: 8px;
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

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 11px;
            border-radius: 6px;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.3s ease;
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background: #3498db;
            color: white;
        }

        .btn-primary:hover {
            background: #2980b9;
        }

        .btn-secondary {
            background: #95a5a6;
            color: white;
            text-decoration: none;
            text-align: center;
        }

        .btn-secondary:hover {
            background: #7f8c8d;
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
