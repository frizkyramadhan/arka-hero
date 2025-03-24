@extends('layouts.main')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $title }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('officialtravels.index') }}">{{ $title }}</a>
                        </li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <form action="{{ route('officialtravels.update', $officialtravel->id) }}" method="POST"
                id="officialTravelForm">
                @csrf
                @method('PUT')
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
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="official_travel_number">LOT Number <span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                                </div>
                                                <input type="text"
                                                    class="form-control @error('official_travel_number') is-invalid @enderror"
                                                    name="official_travel_number" id="official_travel_number"
                                                    value="{{ old('official_travel_number', $officialtravel->official_travel_number) }}"
                                                    placeholder="Enter Travel Number" readonly>
                                                @error('official_travel_number')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
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
                                                    value="{{ old('official_travel_date', $officialtravel->official_travel_date) }}"
                                                    required />
                                                @error('official_travel_date')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="official_travel_origin">LOT Origin <span
                                                    class="text-danger">*</span></label>
                                            <select
                                                class="form-control select2-primary @error('official_travel_origin') is-invalid @enderror"
                                                name="official_travel_origin" id="official_travel_origin"
                                                style="width: 100%;" required>
                                                <option value="">Select Origin Project</option>
                                                @foreach ($projects as $project)
                                                    <option value="{{ $project->id }}"
                                                        {{ old('official_travel_origin', $officialtravel->official_travel_origin) == $project->id ? 'selected' : '' }}>
                                                        {{ $project->project_code }} - {{ $project->project_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('official_travel_origin')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="traveler_id">Main Traveler <span
                                                    class="text-danger">*</span></label>
                                            <select
                                                class="form-control select2-info @error('traveler_id') is-invalid @enderror"
                                                name="traveler_id" id="traveler_id" style="width: 100%;" required>
                                                <option value="">Select Main Traveler</option>
                                                @foreach ($employees as $employee)
                                                    <option value="{{ $employee['id'] }}"
                                                        data-position="{{ $employee['position'] }}"
                                                        data-project="{{ $employee['project'] }}"
                                                        data-department="{{ $employee['department'] }}"
                                                        data-current-project="{{ $employee['current_project'] }}"
                                                        {{ old('traveler_id', $officialtravel->traveler_id) == $employee['id'] ? 'selected' : '' }}>
                                                        {{ $employee['nik'] }} - {{ $employee['fullname'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('traveler_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-2">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="font-weight-bold">Title</label>
                                            <div class="main-traveler-position">
                                                {{ $officialtravel->traveler->position->position_name }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="font-weight-bold">Business Unit</label>
                                            <div class="main-traveler-project">{{ $officialtravel->project->project_name }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="font-weight-bold">Department</label>
                                            <div class="main-traveler-department">
                                                {{ $officialtravel->traveler->position->department->department_name }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label class="font-weight-bold">Current Project</label>
                                            <div class="main-traveler-current-project">
                                                {{ $officialtravel->traveler->project->project_name }}
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="purpose">Purpose <span class="text-danger">*</span></label>
                                    <textarea class="form-control @error('purpose') is-invalid @enderror" name="purpose" id="purpose" rows="3"
                                        placeholder="Enter Purpose of Travel" required>{{ old('purpose', $officialtravel->purpose) }}</textarea>
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
                                                    name="destination" id="destination"
                                                    value="{{ old('destination', $officialtravel->destination) }}"
                                                    placeholder="Enter Destination" required>
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
                                                    name="departure_from"
                                                    value="{{ old('departure_from', $officialtravel->departure_from) }}"
                                                    required />
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
                                                    name="duration" id="duration"
                                                    value="{{ old('duration', $officialtravel->duration) }}"
                                                    placeholder="e.g. 5 days" required>
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
                                                <th>Current Project</th>
                                                <th width="50px">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($officialtravel->details as $detail)
                                                <tr>
                                                    <td>
                                                        <select class="form-control select2-follower" name="followers[]"
                                                            style="width: 100%;" required>
                                                            <option value="">Select Employee</option>
                                                            @foreach ($employees as $employee)
                                                                <option value="{{ $employee['id'] }}"
                                                                    data-position="{{ $employee['position'] }}"
                                                                    data-project="{{ $employee['project'] }}"
                                                                    data-department="{{ $employee['department'] }}"
                                                                    data-current-project="{{ $employee['current_project'] }}"
                                                                    {{ $detail->follower_id == $employee['id'] ? 'selected' : '' }}>
                                                                    {{ $employee['nik'] }} - {{ $employee['fullname'] }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td><span
                                                            class="employee-position">{{ $detail->follower->position->position_name }}</span>
                                                    </td>
                                                    <td><span
                                                            class="employee-project">{{ $detail->follower->project->project_name }}</span>
                                                    </td>
                                                    <td><span
                                                            class="employee-department">{{ $detail->follower->position->department->department_name }}</span>
                                                    </td>
                                                    <td><span
                                                            class="employee-current-project">{{ $detail->follower->project->project_name }}</span>
                                                    </td>
                                                    <td class="text-center">
                                                        <a href="javascript:void(0)" class="remove-follower"
                                                            title="Remove">
                                                            <i class="fas fa-times-circle"></i>
                                                        </a>
                                                    </td>
                                                </tr>
                                            @endforeach
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
                                        name="transportation_id" id="transportation_id" style="width: 100%;" required>
                                        <option value="">Select Transportation</option>
                                        @foreach ($transportations as $transportation)
                                            <option value="{{ $transportation->id }}"
                                                {{ old('transportation_id', $officialtravel->transportation_id) == $transportation->id ? 'selected' : '' }}>
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
                                        name="accommodation_id" id="accommodation_id" style="width: 100%;" required>
                                        <option value="">Select Accommodation</option>
                                        @foreach ($accommodations as $accommodation)
                                            <option value="{{ $accommodation->id }}"
                                                {{ old('accommodation_id', $officialtravel->accommodation_id) == $accommodation->id ? 'selected' : '' }}>
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

                        <!-- Notes Card -->
                        <div class="card card-secondary card-outline elevation-3">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-user-check mr-2"></i>
                                    <strong>Recommendation & Approval</strong>
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="recommender_id">Recommended By <span class="text-danger">*</span></label>
                                    <select
                                        class="form-control select2-secondary @error('recommender_id') is-invalid @enderror"
                                        name="recommender_id" id="recommender_id" style="width: 100%;" required>
                                        <option value="">Select Recommender</option>
                                        @foreach ($recommenders as $recommender)
                                            <option value="{{ $recommender['id'] }}"
                                                {{ old('recommender_id', $officialtravel->recommendation_by) == $recommender['id'] ? 'selected' : '' }}>
                                                {{ $recommender['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('recommender_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group mt-4">
                                    <label for="approver_id">Approved By <span class="text-danger">*</span></label>
                                    <select
                                        class="form-control select2-secondary @error('approver_id') is-invalid @enderror"
                                        name="approver_id" id="approver_id" style="width: 100%;" required>
                                        <option value="">Select Approver</option>
                                        @foreach ($approvers as $approver)
                                            <option value="{{ $approver['id'] }}"
                                                {{ old('approver_id', $officialtravel->approval_by) == $approver['id'] ? 'selected' : '' }}>
                                                {{ $approver['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('approver_id')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="card elevation-3">
                            <div class="card-body">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-save mr-2"></i> Update Official Travel
                                </button>
                                <a href="{{ route('officialtravels.show', $officialtravel->id) }}"
                                    class="btn btn-secondary btn-block">
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
    <!-- daterange picker -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/daterangepicker/daterangepicker.css') }}">
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

        /* Custom colors for select2 */
        .select2-container--bootstrap4.select2-container--primary .select2-selection {
            border-color: #007bff;
        }

        .select2-container--bootstrap4.select2-container--info .select2-selection {
            border-color: #17a2b8;
        }

        .select2-container--bootstrap4.select2-container--warning .select2-selection {
            border-color: #ffc107;
        }

        /* Animation for adding new rows */
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

        /* Custom styling for the followers table */
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
    </style>
@endsection

@section('scripts')
    <!-- Select2 -->
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $(function() {
            // Initialize Select2 Elements with custom themes
            $('.select2-primary').select2({
                theme: 'bootstrap4',
                placeholder: 'Select an option'
            }).on('select2:open', function() {
                document.querySelector('.select2-search__field').focus();
            });

            $('.select2-info').select2({
                theme: 'bootstrap4',
                placeholder: 'Select an option'
            }).on('select2:open', function() {
                document.querySelector('.select2-search__field').focus();
            }).on('change', function() {
                const $option = $(this).find(':selected');

                // Update main traveler details
                $('.main-traveler-position').text($option.data('position') || '-');
                $('.main-traveler-project').text($option.data('project') || '-');
                $('.main-traveler-department').text($option.data('department') || '-');
                $('.main-traveler-current-project').text($option.data('current-project') || '-');
            });

            $('.select2-warning').select2({
                theme: 'bootstrap4',
                placeholder: 'Select an option'
            }).on('select2:open', function() {
                document.querySelector('.select2-search__field').focus();
            });

            $('.select2-secondary').select2({
                theme: 'bootstrap4',
                placeholder: 'Select an option'
            }).on('select2:open', function() {
                document.querySelector('.select2-search__field').focus();
            });

            // Function to initialize follower select2 with change handler
            function initializeFollowerSelect2($element) {
                $element.select2({
                    theme: 'bootstrap4',
                    placeholder: 'Select Employee'
                }).on('select2:open', function() {
                    document.querySelector('.select2-search__field').focus();
                }).on('change', function() {
                    const $row = $(this).closest('tr');
                    const $option = $(this).find(':selected');

                    // Update employee details
                    $row.find('.employee-position').text($option.data('position') || '-');
                    $row.find('.employee-project').text($option.data('project') || '-');
                    $row.find('.employee-department').text($option.data('department') || '-');
                    $row.find('.employee-current-project').text($option.data('current-project') || '-');
                });
            }

            // Initialize existing follower select2
            $('.select2-follower').each(function() {
                initializeFollowerSelect2($(this));
            });

            // Handle adding new follower row
            $('#addFollowerRow').click(function() {
                addFollowerRow();
            });

            // Function to add new follower row
            function addFollowerRow() {
                const rowHtml = `
                    <tr class="follower-row-new">
                        <td>
                            <select class="form-control select2-follower" name="followers[]" style="width: 100%;" required>
                                <option value="">Select Employee</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee['id'] }}"
                                        data-position="{{ $employee['position'] }}"
                                        data-project="{{ $employee['project'] }}"
                                        data-department="{{ $employee['department'] }}"
                                        data-current-project="{{ $employee['current_project'] }}">
                                        {{ $employee['nik'] }} - {{ $employee['fullname'] }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td><span class="employee-position">-</span></td>
                        <td><span class="employee-project">-</span></td>
                        <td><span class="employee-department">-</span></td>
                        <td><span class="employee-current-project">-</span></td>
                        <td class="text-center">
                            <a href="javascript:void(0)" class="remove-follower" title="Remove">
                                <i class="fas fa-times-circle"></i>
                            </a>
                        </td>
                    </tr>
                `;

                $('#followersTable tbody').append(rowHtml);

                // Initialize Select2 for the new row
                const $newSelect = $('#followersTable tbody tr:last-child .select2-follower');
                initializeFollowerSelect2($newSelect);
            }

            // Handle removing follower row
            $(document).on('click', '.remove-follower', function() {
                $(this).closest('tr').fadeOut(300, function() {
                    $(this).remove();
                });
            });

            // Form validation
            $('#officialTravelForm').on('submit', function(e) {
                let isValid = true;

                // Check required fields
                $(this).find('[required]').each(function() {
                    if (!$(this).val()) {
                        isValid = false;
                        $(this).addClass('is-invalid');
                    } else {
                        $(this).removeClass('is-invalid');
                    }
                });

                if (!isValid) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please fill in all required fields marked with *'
                    });
                }
            });
        });
    </script>
@endsection
