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
                        @if ($officialtravel->isPendingHr())
                        <!-- Konfirmasi Pengajuan dari User: Pilih Nomor Surat -->
                        <div class="card card-info card-outline elevation-2">
                            <div class="card-header py-2">
                                <h3 class="card-title">
                                    <i class="fas fa-check-double mr-2"></i>
                                    <strong>Konfirmasi Pengajuan dari User</strong>
                                </h3>
                            </div>
                            <div class="card-body py-2">
                                <p class="text-muted small mb-2">Pengajuan ini dari My Travels. Pilih nomor surat untuk mengonfirmasi dan menghasilkan nomor LOT resmi.</p>
                                @include('components.smart-letter-number-selector', [
                                    'categoryCode' => 'B',
                                    'required' => true,
                                ])
                            </div>
                        </div>
                        @endif

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
                                                @if ($officialtravel->isPendingHr())
                                                <input type="text"
                                                    class="form-control"
                                                    id="official_travel_number"
                                                    value="{{ old('official_travel_number', $officialtravel->official_travel_number) }}"
                                                    readonly>
                                                <small class="form-text text-muted">Akan diganti dengan nomor LOT resmi setelah memilih nomor surat di atas.</small>
                                                @else
                                                <input type="text"
                                                    class="form-control @error('official_travel_number') is-invalid @enderror"
                                                    name="official_travel_number" id="official_travel_number"
                                                    value="{{ old('official_travel_number', $officialtravel->official_travel_number) }}"
                                                    placeholder="Enter Travel Number" readonly>
                                                @error('official_travel_number')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                @endif
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
                                                    value="{{ old('official_travel_date', $officialtravel->official_travel_date ? $officialtravel->official_travel_date->format('Y-m-d') : '') }}"
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
                                                        data-department-id="{{ $employee['department_id'] }}"
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
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="font-weight-bold">Title</label>
                                            <div class="main-traveler-position">
                                                {{ $officialtravel->traveler->position->position_name ?? '-' }}</div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="font-weight-bold">Business Unit</label>
                                            <div class="main-traveler-project">
                                                {{ $officialtravel->project->project_name ?? '-' }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label class="font-weight-bold">Department</label>
                                            <div class="main-traveler-department">
                                                {{ $officialtravel->traveler->position->department->department_name ?? '-' }}
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
                                    @php
                                        $destinationOld = old('destination', $officialtravel->destination ?? '');
                                        if ((string) old('destination_is_manual') === '1') {
                                            $destinationIsManual = true;
                                        } else {
                                            $destinationIsManual = true;
                                            foreach ($destinationProjects as $project) {
                                                $destinationOptCheck =
                                                    $project->project_code . ' - ' . $project->project_name;
                                                if ((string) $destinationOld === (string) $destinationOptCheck) {
                                                    $destinationIsManual = false;
                                                    break;
                                                }
                                            }
                                        }
                                    @endphp
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="destination_is_manual">
                                                <i class="fas fa-map-marker-alt mr-1"></i>
                                                Destination <span class="text-danger">*</span>
                                            </label>
                                            <input type="hidden" name="destination" id="destination_value"
                                                value="{{ $destinationOld }}" required>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        <input type="checkbox" name="destination_is_manual"
                                                            id="destination_is_manual" value="1"
                                                            {{ $destinationIsManual ? 'checked' : '' }}
                                                            title="Enter destination as free text"
                                                            aria-label="Manual destination">
                                                    </span>
                                                </div>
                                                <div id="destination_project_select_wrap"
                                                    class="flex-fill {{ $destinationIsManual ? 'd-none' : '' }}"
                                                    style="min-width: 0;">
                                                    <select
                                                        class="form-control select2-primary @error('destination') is-invalid @enderror"
                                                        id="destination_project_select" style="width: 100%;">
                                                        <option value="">Select Project</option>
                                                        @foreach ($destinationProjects as $project)
                                                            @php
                                                                $destinationOptLabel =
                                                                    $project->project_code . ' - ' . $project->project_name;
                                                            @endphp
                                                            <option value="{{ $destinationOptLabel }}"
                                                                {{ !$destinationIsManual && (string) $destinationOld === (string) $destinationOptLabel ? 'selected' : '' }}>
                                                                {{ $destinationOptLabel }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <input type="text"
                                                    class="form-control flex-fill @error('destination') is-invalid @enderror {{ $destinationIsManual ? '' : 'd-none' }}"
                                                    id="destination_manual_input"
                                                    style="min-width: 0;"
                                                    value="{{ $destinationIsManual ? $destinationOld : '' }}"
                                                    placeholder="Enter destination" autocomplete="off"
                                                    {{ $destinationIsManual ? '' : 'disabled' }}>
                                            </div>
                                            <small class="form-text text-muted">Choose an active project code, or tick the
                                                box for manual entry.</small>
                                            @error('destination')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
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
                                                    value="{{ old('departure_from', $officialtravel->departure_from ? $officialtravel->departure_from->format('Y-m-d') : '') }}"
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
                                                <th width="50px">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($officialtravel->details as $detail)
                                                <tr>
                                                    <td>
                                                        <select class="form-control select2-follower" name="followers[]"
                                                            style="width: 100%;">
                                                            <option value="">Select Employee</option>
                                                            @foreach ($employees as $employee)
                                                                <option value="{{ $employee['id'] }}"
                                                                    data-position="{{ $employee['position'] }}"
                                                                    data-project="{{ $employee['project'] }}"
                                                                    data-department="{{ $employee['department'] }}"
                                                                    data-department-id="{{ $employee['department_id'] }}"
                                                                    {{ $detail->follower_id == $employee['id'] ? 'selected' : '' }}>
                                                                    {{ $employee['nik'] }} - {{ $employee['fullname'] }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td><span
                                                            class="employee-position">{{ $detail->follower->position->position_name ?? '-' }}</span>
                                                    </td>
                                                    <td><span
                                                            class="employee-project">{{ $detail->follower->project->project_name ?? '-' }}</span>
                                                    </td>
                                                    <td><span
                                                            class="employee-department">{{ $detail->follower->position->department->department_name ?? '-' }}</span>
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

                        {{-- Flight Request (Tiket Pesawat) - optional --}}
                        <x-flight-request-fields name-prefix="fr_data" :allow-return-segment="true" :existing-flight-request="$existingFlightRequest ?? null" />

                        <!-- Manual Approver Selection Card -->
                        <div class="card card-info card-outline elevation-2">
                            <div class="card-header py-2">
                                <h3 class="card-title">
                                    <i class="fas fa-users mr-2"></i>
                                    <strong>Approver Selection</strong>
                                </h3>
                            </div>
                            <div class="card-body py-2">
                                @include('components.manual-approver-selector', [
                                    'selectedApprovers' => old(
                                        'manual_approvers',
                                        $officialtravel->manual_approvers ?? []),
                                    'required' => $officialtravel->isPendingHr(),
                                    'multiple' => true,
                                    'helpText' => $officialtravel->isPendingHr()
                                        ? 'Pilih minimal satu approver untuk mengonfirmasi pengajuan ini.'
                                        : 'Pilih approver untuk approval (opsional, dapat dipilih saat submit)',
                                    'documentType' => 'officialtravel',
                                ])
                            </div>
                        </div>

                        <!-- Approval Status Card -->
                        {{-- <x-approval-status-card :documentType="'officialtravel'" :documentId="$officialtravel->id" mode="preview" :projectId="old('official_travel_origin', $officialtravel->official_travel_origin)"
                            :departmentId="$officialtravel->traveler->position->department_id ?? null" title="Approval Status" id="dynamicApprovalCard" /> --}}

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

        .select2-container--bootstrap4.select2-container--success .select2-selection {
            border-color: #28a745;
        }

        .select2-container--bootstrap4.select2-container--success .select2-selection {
            border-color: #28a745;
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

        #destination_project_select_wrap .select2-container {
            width: 100% !important;
        }
    </style>
@endsection

@section('scripts')
    <!-- Select2 -->
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $(function() {
            // Initialize Select2 Elements with custom themes (destination select handled on toggle)
            $('.select2-primary').not('#destination_project_select').select2({
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
            });

            $('.select2-warning').select2({
                theme: 'bootstrap4',
                placeholder: 'Select an option'
            }).on('select2:open', function() {
                document.querySelector('.select2-search__field').focus();
            });

            $('.select2-success').select2({
                theme: 'bootstrap4',
                placeholder: 'Select an option'
            }).on('select2:open', function() {
                document.querySelector('.select2-search__field').focus();
            });

            // Initialize recommendation and approval select2
            $('#recommendation_by, #approval_by').select2({
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
                            <select class="form-control select2-follower" name="followers[]" style="width: 100%;">
                                <option value="">Select Employee</option>
                                @foreach ($employees as $employee)
                                    <option value="{{ $employee['id'] }}"
                                        data-position="{{ $employee['position'] }}"
                                        data-project="{{ $employee['project'] }}"
                                        data-department="{{ $employee['department'] }}"
                                        data-department-id="{{ $employee['department_id'] }}">
                                        {{ $employee['nik'] }} - {{ $employee['fullname'] }}
                                    </option>
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

            // Dynamic Approval Status Card Update
            function updateApprovalStatusCard() {
                const projectId = $('#official_travel_origin').val();
                const $selectedOption = $('#traveler_id option:selected');
                const departmentId = $selectedOption.data('department-id') || null;

                console.log('Updating approval status card with:', {
                    projectId,
                    departmentId
                });

                // Get the approval status card component
                const $approvalCard = $('#dynamicApprovalCard');

                if (!$approvalCard.length) {
                    console.error('Approval status card not found');
                    return;
                }

                // Update the component props by re-rendering it
                if (projectId && departmentId) {
                    // Show loading state
                    $approvalCard.find('.card-body').html(`
                        <div class="text-center py-3">
                            <i class="fas fa-spinner fa-spin text-info"></i>
                            <div class="mt-2">Loading approval flow...</div>
                        </div>
                    `);

                    // Fetch new approval stages
                    $.ajax({
                        url: '{{ route('approval.stages.preview') }}',
                        method: 'GET',
                        data: {
                            project_id: projectId,
                            department_id: departmentId,
                            document_type: 'officialtravel'
                        },
                        success: function(response) {
                            console.log('Updated approval preview response:', response);
                            if (response.success && response.approvers.length > 0) {
                                let html = '<div class="approval-flow preview-mode">';

                                response.approvers.forEach((approver, index) => {
                                    html += `
                                        <div class="approval-step preview-step">
                                            <div class="step-number">${approver.order || index + 1}</div>
                                            <div class="step-content">
                                                <div class="approver-name">${approver.name}</div>
                                                <div class="approver-department">${approver.department}</div>
                                                <div class="step-label">Step ${approver.order || index + 1}</div>
                                            </div>
                                        </div>
                                    `;
                                });

                                html += '</div>';
                                $approvalCard.find('.card-body').html(html);
                            } else {
                                $approvalCard.find('.card-body').html(`
                                    <div class="text-center text-muted py-3">
                                        <i class="fas fa-info-circle"></i>
                                        <div class="mt-2">No approval flow configured for this project and department</div>
                                        <small class="text-muted">Project ID: ${projectId}, Department ID: ${departmentId}</small>
                                    </div>
                                `);
                            }
                        },
                        error: function(xhr, status, error) {
                            console.log('Approval preview update error:', {
                                xhr,
                                status,
                                error
                            });
                            $approvalCard.find('.card-body').html(`
                                <div class="text-center text-danger py-3">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    <div class="mt-2">Failed to load approval flow</div>
                                    <small class="text-muted">${error}</small>
                                </div>
                            `);
                        }
                    });
                } else {
                    // Show message when project or department is not selected
                    $approvalCard.find('.card-body').html(`
                        <div class="text-center text-muted py-3">
                            <i class="fas fa-info-circle"></i>
                            <div class="mt-2">Select both project and main traveler to see approval flow</div>
                        </div>
                    `);
                }
            }

            // Listen for project and main traveler changes
            $('#official_travel_origin, #traveler_id').on('change', function() {
                updateApprovalStatusCard();
            });

            // Initial load of approval status card
            $(document).ready(function() {
                updateApprovalStatusCard();
            });

            (function initOfficialTravelDestinationField() {
                var $form = $('#officialTravelForm');
                if (!$form.length) return;
                if ($form.data('destinationManualBound')) return;
                $form.data('destinationManualBound', true);

                function destinationSyncHidden(root) {
                    var $root = root ? $(root) : $(document);
                    var hidden = $root.find('#destination_value');
                    if (!hidden.length) return;
                    var manualCb = $root.find('#destination_is_manual');
                    var manualInput = $root.find('#destination_manual_input');
                    var projectSelect = $root.find('#destination_project_select');
                    if (manualCb.is(':checked')) {
                        hidden.val((manualInput.val() || '').trim());
                    } else {
                        hidden.val((projectSelect.val() || '').trim());
                    }
                }

                function destinationToggleMode(root) {
                    var $root = root ? $(root) : $(document);
                    var manualCb = $root.find('#destination_is_manual');
                    var manualInput = $root.find('#destination_manual_input');
                    var projectSelect = $root.find('#destination_project_select');
                    var $wrap = $root.find('#destination_project_select_wrap');

                    if (manualCb.is(':checked')) {
                        if (projectSelect.hasClass('select2-hidden-accessible')) {
                            projectSelect.select2('destroy');
                        }
                        $wrap.addClass('d-none');
                        manualInput.removeClass('d-none');
                        manualInput.prop('disabled', false);
                        projectSelect.prop('disabled', true);
                        if (!manualInput.val() && projectSelect.val()) {
                            manualInput.val(projectSelect.val());
                        }
                    } else {
                        manualInput.addClass('d-none');
                        manualInput.prop('disabled', true);
                        $wrap.removeClass('d-none');
                        projectSelect.prop('disabled', false);
                        if (projectSelect.val() === '' && (manualInput.val() || '').trim() !== '') {
                            var wanted = (manualInput.val() || '').trim();
                            var match = projectSelect.find('option').filter(function() {
                                return $(this).val() === wanted;
                            }).first();
                            if (match.length) {
                                projectSelect.val(wanted);
                            }
                        }
                        if (!projectSelect.hasClass('select2-hidden-accessible')) {
                            projectSelect.select2({
                                theme: 'bootstrap4',
                                placeholder: 'Select an option'
                            }).on('select2:open', function() {
                                document.querySelector('.select2-search__field').focus();
                            });
                        }
                        projectSelect.trigger('change');
                    }
                    destinationSyncHidden(root);
                }

                var formEl = $form.get(0);
                destinationToggleMode(formEl);
                $form.on('change', '#destination_is_manual', function() {
                    destinationToggleMode(formEl);
                });
                $form.on('change', '#destination_project_select', function() {
                    destinationSyncHidden(formEl);
                });
                $form.on('input', '#destination_manual_input', function() {
                    destinationSyncHidden(formEl);
                });
                $form.on('submit', function() {
                    destinationSyncHidden(formEl);
                });
            })();

            // Form validation
            $('#officialTravelForm').on('submit', function(e) {
                let isValid = true;
                let firstInvalidField = null;
                let invalidFields = [];

                // Check required fields (exclude followers[] as they are optional)
                $(this).find('[required]').each(function() {
                    const $field = $(this);
                    const fieldName = $field.attr('name');
                    const fieldId = $field.attr('id');

                    // Skip disabled or readonly fields
                    if ($field.prop('disabled') || $field.prop('readonly')) {
                        return;
                    }

                    // Skip followers[] fields as they are optional
                    if (fieldName && fieldName.includes('followers[]')) {
                        return;
                    }

                    // Skip manual_approvers_required as it's optional in edit mode
                    if (fieldName && fieldName === 'manual_approvers_required') {
                        return;
                    }

                    // For select2 fields, check if value exists
                    if ($field.hasClass('select2-hidden-accessible')) {
                        const value = $field.val();
                        if (!value || value === '' || value === null) {
                            isValid = false;
                            $field.addClass('is-invalid');
                            // Also add invalid class to select2 container for visual feedback
                            $field.next('.select2-container').addClass('is-invalid');
                            invalidFields.push(fieldName || fieldId || 'Unknown Select2 field');
                            if (!firstInvalidField) {
                                firstInvalidField = $field;
                            }
                        } else {
                            $field.removeClass('is-invalid');
                            $field.next('.select2-container').removeClass('is-invalid');
                        }
                    } else {
                        // For regular fields (text, textarea, date, etc.)
                        const fieldValue = $field.val();
                        const isEmpty = !fieldValue || (typeof fieldValue === 'string' && fieldValue
                            .trim() === '');

                        if (isEmpty) {
                            isValid = false;
                            $field.addClass('is-invalid');
                            invalidFields.push(fieldName || fieldId || 'Unknown field');
                            if (!firstInvalidField) {
                                firstInvalidField = $field;
                            }
                        } else {
                            $field.removeClass('is-invalid');
                        }
                    }
                });

                if (!isValid) {
                    e.preventDefault();

                    // Debug: log invalid fields
                    console.log('Invalid fields:', invalidFields);

                    // Scroll to first invalid field
                    if (firstInvalidField) {
                        $('html, body').animate({
                            scrollTop: firstInvalidField.offset().top - 100
                        }, 500);
                        firstInvalidField.focus();
                    }

                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please fill in all required fields marked with *',
                        footer: invalidFields.length > 0 ? 'Missing fields: ' + invalidFields.join(
                            ', ') : ''
                    });
                }
            });


        });
    </script>
@endsection
