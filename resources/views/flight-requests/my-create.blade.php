@extends('layouts.main')

@section('title', 'Create My Flight Request')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Create Flight Request</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard.personal') }}">My Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('flight-requests.my-requests') }}">My Flight Requests</a></li>
                        <li class="breadcrumb-item active">Create</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <form method="POST" action="{{ route('flight-requests.my-requests.store') }}" id="flightRequestForm">
                @csrf
                <div class="row">
                    <div class="col-md-12">
                        <div class="card card-primary card-outline elevation-3">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-plane mr-2"></i>
                                    <strong>Flight Request Information</strong>
                                </h3>
                            </div>
                            <div class="card-body">
                                <input type="hidden" name="request_type" value="standalone">
                                
                                <div class="form-group">
                                    <label for="employee">
                                        <i class="fas fa-user mr-1"></i>
                                        Employee
                                    </label>
                                    <input type="text" id="employee" class="form-control" value="{{ $employee->fullname ?? 'N/A' }}" disabled>
                                    <small class="text-muted">Auto-filled from your profile</small>
                                </div>

                                <div class="form-group">
                                    <label for="purpose_of_travel">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Purpose of Travel <span class="text-danger">*</span>
                                    </label>
                                    <textarea name="purpose_of_travel" id="purpose_of_travel" 
                                        class="form-control @error('purpose_of_travel') is-invalid @enderror" 
                                        rows="3" required>{{ old('purpose_of_travel') }}</textarea>
                                    @error('purpose_of_travel')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="total_travel_days">
                                        <i class="fas fa-calendar-day mr-1"></i>
                                        Total Travel Days
                                    </label>
                                    <div class="input-group">
                                        <input type="text" name="total_travel_days" id="total_travel_days" 
                                            class="form-control" value="{{ old('total_travel_days') }}">
                                        <div class="input-group-append">
                                            <span class="input-group-text">days</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="notes">
                                        <i class="fas fa-sticky-note mr-1"></i>
                                        Notes
                                    </label>
                                    <textarea name="notes" id="notes" class="form-control" rows="2">{{ old('notes') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <!-- Flight Details -->
                        <div class="card card-info card-outline elevation-3">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-route mr-2"></i>
                                    <strong>Flight Details</strong>
                                </h3>
                            </div>
                            <div class="card-body">
                                <div id="flightDetailsContainer"></div>
                                <button type="button" class="btn btn-sm btn-success mt-2" id="addFlightDetail">
                                    <i class="fas fa-plus"></i> Add Flight Segment
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-12">
                        <div class="card-footer">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save as Draft
                            </button>
                            <a href="{{ route('flight-requests.my-requests') }}" class="btn btn-default">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        let detailIndex = 0;

        $(document).ready(function() {
            addFlightDetail();

            $('#addFlightDetail').click(function() {
                addFlightDetail();
            });
        });

        function addFlightDetail() {
            const segmentType = detailIndex === 0 ? 'departure' : 'return';
            const html = `
                <div class="flight-detail-item border p-3 mb-3" data-index="${detailIndex}">
                    <div class="row">
                        <div class="col-md-12 mb-2">
                            <strong>Segment ${detailIndex + 1}: ${segmentType === 'departure' ? 'Departure' : 'Return'}</strong>
                            <button type="button" class="btn btn-sm btn-danger float-right remove-detail">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <input type="hidden" name="details[${detailIndex}][segment_type]" value="${segmentType}">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>
                                    <i class="fas fa-calendar-alt mr-1"></i>
                                    Flight Date <span class="text-danger">*</span>
                                </label>
                                <div class="input-group date">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                    </div>
                                    <input type="date" name="details[${detailIndex}][flight_date]" class="form-control" required>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>
                                    <i class="fas fa-plane-departure mr-1"></i>
                                    Departure City <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="details[${detailIndex}][departure_city]" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>
                                    <i class="fas fa-plane-arrival mr-1"></i>
                                    Arrival City <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="details[${detailIndex}][arrival_city]" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>
                                    <i class="fas fa-plane mr-1"></i>
                                    Airline
                                </label>
                                <input type="text" name="details[${detailIndex}][airline]" class="form-control" placeholder="Preferred airline">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>
                                    <i class="fas fa-clock mr-1"></i>
                                    Flight Time
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-clock"></i></span>
                                    </div>
                                    <input type="time" name="details[${detailIndex}][flight_time]" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            $('#flightDetailsContainer').append(html);
            detailIndex++;
        }

        $(document).on('click', '.remove-detail', function() {
            $(this).closest('.flight-detail-item').remove();
        });
    </script>
@endsection
