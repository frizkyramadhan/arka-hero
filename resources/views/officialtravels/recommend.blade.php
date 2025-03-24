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
                        <li class="breadcrumb-item active">Recommend</li>
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
                    <div class="card card-warning card-outline">
                        <div class="card-header">
                            <h3 class="card-title">{{ $subtitle }}</h3>
                        </div>
                        <!-- /.card-header -->

                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-12">
                                    <div class="callout callout-info">
                                        <h5><i class="fas fa-info-circle"></i> Official Travel Details:</h5>
                                        <p>Please review the following travel request before making your recommendation.</p>
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
                                            <span class="info-box-text text-muted">Travel Date</span>
                                            <span
                                                class="info-box-number text-bold">{{ $officialtravel->official_travel_date }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-box bg-light">
                                        <div class="info-box-content">
                                            <span class="info-box-text text-muted">Origin Project</span>
                                            <span
                                                class="info-box-number text-bold">{{ $officialtravel->project->project_name ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-box bg-light">
                                        <div class="info-box-content">
                                            <span class="info-box-text text-muted">Traveler</span>
                                            <span
                                                class="info-box-number text-bold">{{ $officialtravel->traveler->employees->fullname ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-12">
                                    <div class="info-box bg-light">
                                        <div class="info-box-content">
                                            <span class="info-box-text text-muted">Purpose</span>
                                            <span class="info-box-number">{{ $officialtravel->purpose }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-box bg-light">
                                        <div class="info-box-content">
                                            <span class="info-box-text text-muted">Destination</span>
                                            <span
                                                class="info-box-number text-bold">{{ $officialtravel->destination }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-box bg-light">
                                        <div class="info-box-content">
                                            <span class="info-box-text text-muted">Duration</span>
                                            <span class="info-box-number text-bold">{{ $officialtravel->duration }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-box bg-light">
                                        <div class="info-box-content">
                                            <span class="info-box-text text-muted">Transportation</span>
                                            <span
                                                class="info-box-number text-bold">{{ $officialtravel->transportation->transportation_name ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-box bg-light">
                                        <div class="info-box-content">
                                            <span class="info-box-text text-muted">Accommodation</span>
                                            <span
                                                class="info-box-number text-bold">{{ $officialtravel->accommodation->accommodation_name ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="info-box bg-light">
                                        <div class="info-box-content">
                                            <span class="info-box-text text-muted">Departure Date</span>
                                            <span
                                                class="info-box-number text-bold">{{ $officialtravel->departure_from }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="info-box bg-light">
                                        <div class="info-box-content">
                                            <span class="info-box-text text-muted">Expected Return</span>
                                            <span
                                                class="info-box-number text-bold">{{ $officialtravel->departure_at_destination }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if ($officialtravel->details->count() > 0)
                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="card card-outline card-secondary">
                                            <div class="card-header">
                                                <h3 class="card-title">Additional Travelers</h3>
                                            </div>
                                            <div class="card-body">
                                                <ul class="list-group">
                                                    @foreach ($officialtravel->details as $detail)
                                                        <li
                                                            class="list-group-item d-flex justify-content-between align-items-center">
                                                            {{ $detail->follower->employees->fullname ?? 'Unknown' }}
                                                            <span class="badge badge-primary badge-pill">Follower</span>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif

                            <form action="{{ route('officialtravels.recommend', $officialtravel->id) }}" method="POST">
                                @csrf
                                <div class="row mt-4">
                                    <div class="col-md-12">
                                        <div class="card card-outline card-primary">
                                            <div class="card-header">
                                                <h3 class="card-title">Recommendation</h3>
                                            </div>
                                            <div class="card-body">
                                                <div class="form-group">
                                                    <label>Recommendation Status <span class="text-danger">*</span></label>
                                                    <div class="d-flex">
                                                        <div class="custom-control custom-radio mr-4">
                                                            <input class="custom-control-input" type="radio"
                                                                id="approved" name="recommendation_status"
                                                                value="approved"
                                                                {{ old('recommendation_status') == 'approved' ? 'checked' : '' }}
                                                                required>
                                                            <label for="approved"
                                                                class="custom-control-label">Approve</label>
                                                        </div>
                                                        <div class="custom-control custom-radio">
                                                            <input class="custom-control-input" type="radio"
                                                                id="rejected" name="recommendation_status"
                                                                value="rejected"
                                                                {{ old('recommendation_status') == 'rejected' ? 'checked' : '' }}>
                                                            <label for="rejected"
                                                                class="custom-control-label">Reject</label>
                                                        </div>
                                                    </div>
                                                    @error('recommendation_status')
                                                        <div class="text-danger">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                <div class="form-group">
                                                    <label for="recommendation_remark">Remarks <span
                                                            class="text-danger">*</span></label>
                                                    <textarea class="form-control @error('recommendation_remark') is-invalid @enderror" name="recommendation_remark"
                                                        id="recommendation_remark" rows="3" placeholder="Enter your recommendation remarks" required>{{ old('recommendation_remark') }}</textarea>
                                                    @error('recommendation_remark')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> Submit Recommendation
                                        </button>
                                        <a href="{{ route('officialtravels.index') }}" class="btn btn-secondary">
                                            <i class="fas fa-times-circle"></i> Cancel
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
