@extends('layouts.main')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $title }}</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('officialtravels.index') }}">{{ $title }}</a>
                        </li>
                        <li class="breadcrumb-item active">Departure Stamp</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card card-dark card-outline">
                        <div class="card-header">
                            <h3 class="card-title">{{ $subtitle }}</h3>
                        </div>
                        <!-- /.card-header -->

                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="callout callout-info">
                                        <h5><i class="fas fa-plane-departure"></i> Departure Confirmation</h5>
                                        <p>Confirm the departure of travelers from the destination. This will finalize the
                                            official travel and mark it as completed.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-box bg-light">
                                        <div class="info-box-content">
                                            <span class="info-box-text text-muted">Travel Number</span>
                                            <span
                                                class="info-box-number text-bold">{{ $officialtravel->official_travel_number }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-box bg-light">
                                        <div class="info-box-content">
                                            <span class="info-box-text text-muted">Destination</span>
                                            <span
                                                class="info-box-number text-bold">{{ $officialtravel->destination }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-box bg-light">
                                        <div class="info-box-content">
                                            <span class="info-box-text text-muted">Main Traveler</span>
                                            <span
                                                class="info-box-number text-bold">{{ $officialtravel->traveler->employees->fullname ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-box bg-light">
                                        <div class="info-box-content">
                                            <span class="info-box-text text-muted">Expected Departure Date</span>
                                            <span
                                                class="info-box-number text-bold">{{ $officialtravel->departure_at_destination }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="card card-outline card-primary">
                                        <div class="card-header">
                                            <h3 class="card-title">Arrival Information</h3>
                                        </div>
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="info-box bg-info">
                                                        <span class="info-box-icon"><i class="fas fa-user-check"></i></span>
                                                        <div class="info-box-content">
                                                            <span class="info-box-text">Arrival Confirmed By</span>
                                                            <span
                                                                class="info-box-number">{{ $officialtravel->arrivalChecker->name ?? 'Unknown' }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="info-box bg-info">
                                                        <span class="info-box-icon"><i
                                                                class="fas fa-calendar-check"></i></span>
                                                        <div class="info-box-content">
                                                            <span class="info-box-text">Arrival Date</span>
                                                            <span
                                                                class="info-box-number">{{ $officialtravel->arrival_at_destination }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="info-box bg-info">
                                                        <span class="info-box-icon"><i class="fas fa-clock"></i></span>
                                                        <div class="info-box-content">
                                                            <span class="info-box-text">Arrival Timestamp</span>
                                                            <span
                                                                class="info-box-number">{{ $officialtravel->arrival_timestamps ? date('Y-m-d H:i:s', strtotime($officialtravel->arrival_timestamps)) : 'N/A' }}</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row mt-3">
                                                <div class="col-md-12">
                                                    <div class="callout callout-info">
                                                        <h5>Arrival Notes:</h5>
                                                        <p>{{ $officialtravel->arrival_remark }}</p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if ($officialtravel->details->count() > 0)
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="card card-outline card-secondary">
                                            <div class="card-header">
                                                <h3 class="card-title">Travel Members</h3>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <ul class="list-group">
                                                            <li class="list-group-item bg-dark">
                                                                {{ $officialtravel->traveler->employees->fullname ?? 'Unknown' }}
                                                                <span class="badge badge-light">Main Traveler</span>
                                                            </li>
                                                            @foreach ($officialtravel->details as $detail)
                                                                <li class="list-group-item">
                                                                    {{ $detail->follower->employees->fullname ?? 'Unknown' }}
                                                                    <span class="badge badge-primary">Follower</span>
                                                                </li>
                                                            @endforeach
                                                        </ul>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <form action="{{ route('officialtravels.departureStamp', $officialtravel->id) }}"
                                method="POST">
                                @csrf
                                <div class="row mt-4">
                                    <div class="col-md-12">
                                        <div class="card card-outline card-dark">
                                            <div class="card-header">
                                                <h3 class="card-title">Confirm Departure</h3>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-12">
                                                        <div class="alert alert-warning">
                                                            <i class="fas fa-exclamation-triangle"></i>
                                                            <strong>Warning:</strong> Once you confirm the departure, this
                                                            official travel will be marked as completed and cannot be
                                                            modified further.
                                                        </div>
                                                        <div class="form-group">
                                                            <label for="departure_remark">Departure Notes <span
                                                                    class="text-danger">*</span></label>
                                                            <textarea class="form-control @error('departure_remark') is-invalid @enderror" name="departure_remark"
                                                                id="departure_remark" rows="4"
                                                                placeholder="Enter departure confirmation notes, include any important information about the departure." required>{{ old('departure_remark', $officialtravel->departure_remark) }}</textarea>
                                                            @error('departure_remark')
                                                                <div class="invalid-feedback">{{ $message }}</div>
                                                            @enderror
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="card-footer">
                                                <div class="time-info text-center mb-3">
                                                    <h3>Current Date & Time</h3>
                                                    <div id="current-time" class="current-time-display">
                                                        <span id="date">{{ now()->format('Y-m-d') }}</span>
                                                        <span id="time">{{ now()->format('H:i:s') }}</span>
                                                    </div>
                                                    <div class="small text-muted mt-2">This timestamp will be recorded with
                                                        the departure stamp</div>
                                                </div>
                                                <button type="submit" class="btn btn-dark btn-lg btn-block">
                                                    <i class="fas fa-stamp"></i> Stamp Departure & Complete Travel
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <a href="{{ route('officialtravels.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-arrow-left"></i> Back to List
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <!-- /.card-body -->
                    </div>
                    <!-- /.card -->
                </div>
                <!-- /.col -->
            </div>
            <!-- /.row -->
        </div>
        <!-- /.container-fluid -->
    </section>
@endsection

@push('styles')
    <style>
        .current-time-display {
            font-size: 32px;
            color: #343a40;
            background-color: #f8f9fa;
            border-radius: 5px;
            padding: 15px;
            margin: 10px 0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        #date,
        #time {
            margin: 0 10px;
            font-weight: bold;
        }
    </style>
@endpush

@push('scripts')
    <script>
        $(function() {
            // Update the time every second
            setInterval(function() {
                var now = new Date();
                var dateStr = now.getFullYear() + '-' +
                    String(now.getMonth() + 1).padStart(2, '0') + '-' +
                    String(now.getDate()).padStart(2, '0');
                var timeStr = String(now.getHours()).padStart(2, '0') + ':' +
                    String(now.getMinutes()).padStart(2, '0') + ':' +
                    String(now.getSeconds()).padStart(2, '0');

                $('#date').text(dateStr);
                $('#time').text(timeStr);
            }, 1000);
        });
    </script>
@endpush
