@extends('layouts.main')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $subtitle }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item"><a
                                href="{{ route('officialtravels.my-travels') }}">{{ $title }}</a></li>
                        <li class="breadcrumb-item active">Add New</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="alert alert-info">
                <i class="fas fa-info-circle mr-2"></i>
                This request will be sent to HR. Letter number and official LOT number will be assigned by HR after
                confirmation.
            </div>

            <form action="{{ route('officialtravels.my-travels.store') }}" method="POST" id="officialTravelForm">
                @csrf
                <div class="row">
                    <!-- Left Column -->
                    <div class="col-md-8">
                        <!-- Main Travel Info Card -->
                        <div class="card card-primary card-outline elevation-3">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-plane-departure mr-2"></i>
                                    <strong>Travel Information</strong>
                                </h3>
                            </div>
                            <div class="card-body">
                                <input type="hidden" name="traveler_id" value="{{ $myAdministration->id }}">

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="preview_lot_number">LOT Number</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                                </div>
                                                <input type="text" class="form-control bg-light" id="preview_lot_number"
                                                    value="{{ $previewTravelNumber }}" readonly>
                                            </div>
                                            <small class="form-text text-muted">
                                                Request number (assigned on submit; may change if submitted concurrently).
                                            </small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="main_traveler_display">Main Traveler <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-user"></i></span>
                                                </div>
                                                <input type="text" class="form-control" id="main_traveler_display"
                                                    value="{{ $myAdministration->nik }} - {{ $myAdministration->employee->fullname ?? 'N/A' }}"
                                                    readonly>
                                            </div>
                                            <small class="form-text text-muted">
                                                <i class="fas fa-info-circle"></i>
                                                You are submitting this travel request as the main traveler.
                                            </small>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="official_travel_date">LOT Date <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group date" id="official_travel_date_picker"
                                                data-target-input="nearest">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i
                                                            class="fas fa-calendar-alt"></i></span>
                                                </div>
                                                <input type="date"
                                                    class="form-control @error('official_travel_date') is-invalid @enderror"
                                                    name="official_travel_date"
                                                    value="{{ old('official_travel_date', now()->format('Y-m-d')) }}" />
                                                @error('official_travel_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="official_travel_origin">LOT Origin <span
                                                    class="text-danger">*</span></label>
                                            <select
                                                class="form-control select2-primary @error('official_travel_origin') is-invalid @enderror"
                                                name="official_travel_origin" id="official_travel_origin"
                                                style="width: 100%;">
                                                <option value="">Select Origin Project</option>
                                                @foreach ($projects as $project)
                                                    <option value="{{ $project->id }}"
                                                        {{ old('official_travel_origin') == $project->id ? 'selected' : '' }}>
                                                        {{ $project->project_code }} - {{ $project->project_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('official_travel_origin')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-2">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="font-weight-bold">Title</label>
                                            <div class="main-traveler-position">
                                                {{ $myAdministration->position->position_name ?? '-' }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="font-weight-bold">Business Unit</label>
                                            <div class="main-traveler-project">
                                                {{ $myAdministration->project->project_name ?? '-' }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="font-weight-bold">Department</label>
                                            <div class="main-traveler-department">
                                                {{ optional(optional($myAdministration->position)->department)->department_name ?? '-' }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="purpose">Purpose <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('purpose') is-invalid @enderror" name="purpose" id="purpose" rows="3"
                                        placeholder="Enter Purpose of Travel">{{ old('purpose') }}</textarea>
                                    @error('purpose')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="destination">Destination <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i
                                                            class="fas fa-map-marker-alt"></i></span>
                                                </div>
                                                <input type="text"
                                                    class="form-control @error('destination') is-invalid @enderror"
                                                    name="destination" id="destination" value="{{ old('destination') }}"
                                                    placeholder="Enter Destination">
                                                @error('destination')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="departure_from">Departure Date <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group date" id="departure_from_picker"
                                                data-target-input="nearest">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i
                                                            class="fas fa-plane-departure"></i></span>
                                                </div>
                                                <input type="date"
                                                    class="form-control @error('departure_from') is-invalid @enderror"
                                                    name="departure_from" value="{{ old('departure_from') }}" />
                                                @error('departure_from')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="duration">Duration <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-clock"></i></span>
                                                </div>
                                                <input type="text"
                                                    class="form-control @error('duration') is-invalid @enderror"
                                                    name="duration" id="duration" value="{{ old('duration') }}"
                                                    placeholder="e.g. 5 days">
                                                @error('duration')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Additional Travelers Card -->
                        <div class="card card-success card-outline elevation-3">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-users mr-2"></i>
                                    <strong>Followers</strong>
                                </h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" id="addFollowerRow">
                                        <i class="fas fa-plus"></i> Add Follower
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-striped table-hover" id="followersTable">
                                        <thead>
                                            <tr>
                                                <th>NIK/Name</th>
                                                <th>Title</th>
                                                <th>Business Unit</th>
                                                <th>Department</th>
                                                <th width="50px">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @if (old('followers'))
                                                @foreach (old('followers') as $followerId)
                                                    @php
                                                        $followerData = collect($employees)->firstWhere(
                                                            'id',
                                                            (int) $followerId,
                                                        );
                                                    @endphp
                                                    @if ($followerData && $followerData['id'] != $myAdministration->id)
                                                        <tr>
                                                            <td>
                                                                <select class="form-control select2-follower"
                                                                    name="followers[]" style="width: 100%;">
                                                                    <option value="">Select Employee</option>
                                                                    @foreach ($employees as $emp)
                                                                        @if ($emp['id'] != $myAdministration->id)
                                                                            <option value="{{ $emp['id'] }}"
                                                                                data-position="{{ $emp['position'] }}"
                                                                                data-project="{{ $emp['project'] }}"
                                                                                data-department="{{ $emp['department'] }}"
                                                                                {{ $emp['id'] == $followerId ? 'selected' : '' }}>
                                                                                {{ $emp['nik'] }} -
                                                                                {{ $emp['fullname'] }}
                                                                            </option>
                                                                        @endif
                                                                    @endforeach
                                                                </select>
                                                            </td>
                                                            <td><span
                                                                    class="employee-position">{{ $followerData['position'] }}</span>
                                                            </td>
                                                            <td><span
                                                                    class="employee-project">{{ $followerData['project'] }}</span>
                                                            </td>
                                                            <td><span
                                                                    class="employee-department">{{ $followerData['department'] }}</span>
                                                            </td>
                                                            <td class="text-center">
                                                                <a href="javascript:void(0)" class="remove-follower"
                                                                    title="Remove">
                                                                    <i class="fas fa-times-circle"></i>
                                                                </a>
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            @endif
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="col-md-4">

                        <!-- Travel Arrangements Card -->
                        <div class="card card-warning card-outline elevation-3">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-suitcase mr-2"></i>
                                    <strong>Travel Arrangements</strong>
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="transportation_id">Transportation <span
                                            class="text-danger">*</span></label>
                                    <select
                                        class="form-control select2-warning @error('transportation_id') is-invalid @enderror"
                                        name="transportation_id" id="transportation_id" style="width: 100%;">
                                        <option value="">Select Transportation</option>
                                        @foreach ($transportations as $transportation)
                                            <option value="{{ $transportation->id }}"
                                                {{ old('transportation_id') == $transportation->id ? 'selected' : '' }}>
                                                {{ $transportation->transportation_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('transportation_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="accommodation_id">Accommodation <span class="text-danger">*</span></label>
                                    <select
                                        class="form-control select2-warning @error('accommodation_id') is-invalid @enderror"
                                        name="accommodation_id" id="accommodation_id" style="width: 100%;">
                                        <option value="">Select Accommodation</option>
                                        @foreach ($accommodations as $accommodation)
                                            <option value="{{ $accommodation->id }}"
                                                {{ old('accommodation_id') == $accommodation->id ? 'selected' : '' }}>
                                                {{ $accommodation->accommodation_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('accommodation_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        {{-- Flight Request (Tiket Pesawat) - optional --}}
                        <x-flight-request-fields name-prefix="fr_data" :allow-return-segment="true" />

                        <!-- Action Buttons -->
                        <div class="card elevation-3">
                            <div class="card-body">
                                <button type="submit" class="btn btn-success btn-block">
                                    <i class="fas fa-paper-plane mr-2"></i> Submit to HR
                                </button>
                                <a href="{{ route('officialtravels.my-travels') }}" class="btn btn-secondary btn-block">
                                    <i class="fas fa-times-circle mr-2"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
@endsection

@section('styles')
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <style>
        .select2-container--bootstrap4.select2-container--focus .select2-selection {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .select2-container--bootstrap4 .select2-selection--single {
            height: calc(2.25rem + 2px) !important;
        }

        .select2-container--bootstrap4 .select2-selection__rendered {
            line-height: 2.25rem !important;
        }

        .card {
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .card-header {
            border-radius: calc(0.5rem - 1px) calc(0.5rem - 1px) 0 0;
        }

        .input-group-text {
            border-radius: 0.25rem;
        }

        .btn {
            border-radius: 0.25rem;
        }

        .select2-container--bootstrap4.select2-container--primary .select2-selection {
            border-color: #007bff;
        }

        .select2-container--bootstrap4.select2-container--warning .select2-selection {
            border-color: #ffc107;
        }

        .follower-row-new {
            animation: highlightRow 1s ease-in-out;
        }

        @keyframes highlightRow {
            0% {
                background-color: #fff3cd;
            }

            100% {
                background-color: transparent;
            }
        }

        #followersTable {
            margin-bottom: 0;
        }

        #followersTable thead th {
            background-color: #f4f6f9;
            border-bottom: 2px solid #dee2e6;
        }

        #followersTable tbody td {
            vertical-align: middle;
        }

        .remove-follower {
            color: #dc3545;
            cursor: pointer;
            transition: color 0.2s;
        }

        .remove-follower:hover {
            color: #bd2130;
        }

        @media (max-width: 767.98px) {
            .card-body .row .col-md-6:first-child {
                margin-bottom: 1rem;
            }

            .card-body .row .col-md-6:last-child {
                margin-bottom: 1.5rem;
            }

            .btn-block {
                padding: 0.75rem 1rem;
                font-size: 1rem;
                font-weight: 500;
            }
        }
    </style>
@endsection

@section('scripts')
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $(function() {
            $('.select2-primary').select2({
                theme: 'bootstrap4',
                placeholder: 'Select an option'
            }).on('select2:open', function() {
                document.querySelector('.select2-search__field').focus();
            });

            $('.select2-warning').select2({
                theme: 'bootstrap4',
                placeholder: 'Select an option'
            }).on('select2:open', function() {
                document.querySelector('.select2-search__field').focus();
            });

            $('#addFollowerRow').click(function() {
                addFollowerRow();
            });

            function addFollowerRow() {
                const rowHtml = `
                    <tr class="follower-row-new">
                        <td>
                            <select class="form-control select2-follower" name="followers[]" style="width: 100%;">
                                <option value="">Select Employee</option>
                                @foreach ($employees as $emp)
                                    @if ($emp['id'] != $myAdministration->id)
                                        <option value="{{ $emp['id'] }}"
                                            data-position="{{ $emp['position'] }}"
                                            data-project="{{ $emp['project'] }}"
                                            data-department="{{ $emp['department'] }}">
                                            {{ $emp['nik'] }} - {{ $emp['fullname'] }}
                                        </option>
                                    @endif
                                @endforeach
                            </select>
                        </td>
                        <td><span class="employee-position">-</span></td>
                        <td><span class="employee-project">-</span></td>
                        <td><span class="employee-department">-</span></td>
                        <td class="text-center">
                            <a href="javascript:void(0)" class="remove-follower" title="Remove">
                                <i class="fas fa-times-circle"></i>
                            </a>
                        </td>
                    </tr>`;
                $('#followersTable tbody').append(rowHtml);

                const $newSelect = $('#followersTable tbody tr:last-child .select2-follower');
                $newSelect.select2({
                    theme: 'bootstrap4',
                    placeholder: 'Select Employee'
                }).on('select2:open', function() {
                    document.querySelector('.select2-search__field').focus();
                }).on('change', function() {
                    const $row = $(this).closest('tr');
                    const $option = $(this).find(':selected');
                    $row.find('.employee-position').text($option.data('position') || '-');
                    $row.find('.employee-project').text($option.data('project') || '-');
                    $row.find('.employee-department').text($option.data('department') || '-');
                });
            }

            $(document).on('click', '.remove-follower', function() {
                $(this).closest('tr').fadeOut(300, function() {
                    $(this).remove();
                });
            });

            $('#followersTable .select2-follower').each(function() {
                $(this).on('change', function() {
                    const $opt = $(this).find('option:selected');
                    $(this).closest('tr').find('.employee-position').text($opt.data('position') ||
                        '-');
                    $(this).closest('tr').find('.employee-project').text($opt.data('project') ||
                        '-');
                    $(this).closest('tr').find('.employee-department').text($opt.data(
                        'department') || '-');
                });
            });
        });
    </script>
@endsection
