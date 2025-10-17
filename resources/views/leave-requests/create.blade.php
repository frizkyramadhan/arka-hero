@extends('layouts.main')

@section('title', 'Create Leave Request')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Create Leave Request</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('leave.requests.index') }}">Leave Requests</a></li>
                        <li class="breadcrumb-item active">Create</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8">
                    <div class="card card-primary card-outline elevation-3">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-calendar-plus mr-2"></i>
                                <strong>Leave Request Form</strong>
                            </h3>
                        </div>
                        <form method="POST" action="{{ route('leave.requests.store') }}" enctype="multipart/form-data">
                            @csrf
                            <div class="card-body">
                                <!-- Project & Employee Selection -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="project_id">
                                                <i class="fas fa-building mr-1"></i>
                                                Project <span class="text-danger">*</span>
                                            </label>
                                            <select name="project_id" id="project_id"
                                                class="select2bs4 form-control @error('project_id') is-invalid @enderror"
                                                required>
                                                <option value="">Select Project</option>
                                                @foreach ($projects as $project)
                                                    <option value="{{ $project->id }}"
                                                        {{ old('project_id') == $project->id ? 'selected' : '' }}>
                                                        {{ $project->project_name }} ({{ $project->project_code }})
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('project_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="employee_id">
                                                <i class="fas fa-user mr-1"></i>
                                                Employee <span class="text-danger">*</span>
                                            </label>
                                            <select name="employee_id" id="employee_id"
                                                class="select2bs4 form-control @error('employee_id') is-invalid @enderror"
                                                required disabled>
                                                <option value="">Select Employee</option>
                                                @php
                                                    $projectId = old('project_id');
                                                    $employees = \App\Models\Administration::with([
                                                        'employee',
                                                        'position',
                                                    ])
                                                        ->where('project_id', $projectId)
                                                        ->where('is_active', 1)
                                                        ->orderBy('nik', 'asc')
                                                        ->get()
                                                        ->map(function ($admin) {
                                                            return [
                                                                'id' => $admin->employee_id,
                                                                'fullname' => $admin->employee->fullname,
                                                                'position' => $admin->position->position_name ?? 'N/A',
                                                                'nik' => $admin->nik ?? 'N/A',
                                                            ];
                                                        });
                                                @endphp
                                                <option value="">Select Employee</option>
                                                @foreach ($employees as $employee)
                                                    <option value="{{ $employee['id'] }}"
                                                        {{ old('employee_id') == $employee['id'] ? 'selected' : '' }}>
                                                        {{ $employee['nik'] }} - {{ $employee['fullname'] }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('employee_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Leave Type Selection -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="leave_type_id">
                                                <i class="fas fa-calendar-check mr-1"></i>
                                                Leave Type <span class="text-danger">*</span>
                                            </label>
                                            <select name="leave_type_id" id="leave_type_id"
                                                class="select2bs4 form-control @error('leave_type_id') is-invalid @enderror"
                                                required disabled>
                                                <option value="">Select Leave Type</option>
                                                @foreach (\App\Models\LeaveType::all() as $leaveType)
                                                    <option value="{{ $leaveType->id }}"
                                                        {{ old('leave_type_id') == $leaveType->id ? 'selected' : '' }}>
                                                        {{ $leaveType->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('leave_type_id')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="leave_period">
                                                <i class="fas fa-calendar-week mr-1"></i>
                                                Leave Period
                                            </label>
                                            <input type="text" name="leave_period" id="leave_period"
                                                class="form-control @error('leave_period') is-invalid @enderror"
                                                value="{{ old('leave_period') }}" readonly>
                                            <small class="form-text text-muted">Automatically filled from leave
                                                entitlements</small>
                                            @error('leave_period')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Leave Date Selection -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>
                                                <i class="fas fa-calendar-alt mr-1"></i>
                                                Leave Date <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        <i class="far fa-calendar-alt"></i>
                                                    </span>
                                                </div>
                                                <input type="text" class="form-control float-right" id="leave_date"
                                                    placeholder="Select date range" required
                                                    value="{{ old('start_date') && old('end_date') ? \Carbon\Carbon::parse(old('start_date'))->format('d/m/Y') . ' - ' . \Carbon\Carbon::parse(old('end_date'))->format('d/m/Y') : '' }}">
                                                <input type="hidden" name="start_date" id="start_date"
                                                    value="{{ old('start_date') }}">
                                                <input type="hidden" name="end_date" id="end_date"
                                                    value="{{ old('end_date') }}">
                                            </div>
                                            <small class="form-text text-muted" id="weekend_info" style="display: none;">
                                                <i class="fas fa-info-circle mr-1"></i>
                                                Weekend (Saturday & Sunday) are disabled for non-roster projects
                                            </small>
                                            @error('start_date')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                            @error('end_date')
                                                <div class="invalid-feedback d-block">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="back_to_work_date">
                                                <i class="fas fa-calendar-plus mr-1"></i>
                                                Back to Work Date
                                            </label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">
                                                        <i class="far fa-calendar-alt"></i>
                                                    </span>
                                                </div>
                                                <input type="text" name="back_to_work_date" id="back_to_work_date"
                                                    class="form-control @error('back_to_work_date') is-invalid @enderror"
                                                    value="{{ old('back_to_work_date') ? \Carbon\Carbon::parse(old('back_to_work_date'))->format('d/m/Y') : '' }}"
                                                    placeholder="Select back to work date">
                                            </div>
                                            @error('back_to_work_date')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                <!-- Leave Period & Total Days -->
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>
                                                <i class="fas fa-calculator mr-1"></i>
                                                Total Days <span class="text-danger">*</span>
                                            </label>
                                            <div class="input-group">
                                                <input type="number" id="total_days_input" class="form-control"
                                                    min="1" max="365" placeholder="Enter days" required
                                                    value="{{ old('total_days') }}">
                                                <div class="input-group-append">
                                                    <span class="input-group-text">days</span>
                                                </div>
                                            </div>
                                            <input type="hidden" name="total_days" id="total_days_hidden"
                                                value="{{ old('total_days') }}" required>
                                            <small class="form-text text-muted">
                                                Calculated automatically from date range. <br>
                                                <span class="text-warning">You can also manually adjust the number of
                                                    days.</span>
                                            </small>
                                        </div>
                                    </div>
                                    <div class="col-md-6" id="reason_field" style="display: none;">
                                        <div class="form-group">
                                            <label for="reason">
                                                <i class="fas fa-comment-alt mr-1"></i>
                                                Reason <span class="text-danger">*</span>
                                            </label>
                                            <textarea name="reason" id="reason" rows="3" class="form-control @error('reason') is-invalid @enderror"
                                                placeholder="Please provide a detailed reason for your leave request...">{{ old('reason') }}</textarea>
                                            @error('reason')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6" id="document_field" style="display: none;">
                                        <div class="form-group">
                                            <label for="supporting_document">
                                                <i class="fas fa-file-upload mr-1"></i>
                                                Supporting Document
                                            </label>
                                            <div class="input-group">
                                                <div class="custom-file">
                                                    <input type="file" name="supporting_document"
                                                        id="supporting_document"
                                                        class="custom-file-input @error('supporting_document') is-invalid @enderror"
                                                        accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.rar,.zip">
                                                    <label class="custom-file-label" for="supporting_document">
                                                        Choose file...
                                                    </label>
                                                </div>
                                            </div>
                                            <small class="form-text text-muted">
                                                <i class="fas fa-info-circle mr-1"></i>
                                                Upload supporting document/evidence (PDF, DOC, DOCX, JPG, PNG, RAR, ZIP).
                                                Max size: 2MB
                                            </small>
                                            @error('supporting_document')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- LSL Flexible Section - Only appears when LSL is selected --}}
                                <div class="row" id="lsl_flexible_section" style="display: none;">
                                    <div class="col-md-12">
                                        <div class="card card-warning card-outline">
                                            <div class="card-header">
                                                <h5 class="card-title">
                                                    <i class="fas fa-coins mr-2"></i>
                                                    <strong>Long Service Leave</strong>
                                                    <small class="text-muted">(Can be combined with cash out)</small>
                                                </h5>
                                            </div>
                                            <div class="card-body">
                                                <div class="alert alert-warning">
                                                    <i class="fas fa-info-circle mr-2"></i>
                                                    <strong>Note:</strong> This feature is only available for Long Service
                                                    Leave (LSL)
                                                </div>

                                                <div class="row">
                                                    <div class="col-lg-4">
                                                        <div class="form-group">
                                                            <label for="lsl_taken_days">
                                                                <i class="fas fa-calendar-check mr-1"></i>
                                                                Leave Days
                                                            </label>
                                                            <div class="input-group">
                                                                <input type="number" class="form-control"
                                                                    id="lsl_taken_days" name="lsl_taken_days"
                                                                    min="0" value="0">
                                                                <div class="input-group-append">
                                                                    <span class="input-group-text">days</span>
                                                                </div>
                                                            </div>
                                                            <small class="form-text text-muted">
                                                                Can be edited manually. Calculated from date range by
                                                                default.
                                                            </small>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-4">
                                                        <div class="form-group">
                                                            <label for="lsl_cashout_checkbox">
                                                                <i class="fas fa-money-bill-wave mr-1"></i>
                                                                Cash Out
                                                            </label>
                                                            <div class="input-group">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text">
                                                                        <input type="checkbox" id="lsl_cashout_checkbox"
                                                                            name="lsl_cashout_enabled">
                                                                    </span>
                                                                </div>
                                                                <input type="number" class="form-control"
                                                                    id="lsl_cashout_days" name="lsl_cashout_days"
                                                                    min="0" value="0" disabled>
                                                                <div class="input-group-append">
                                                                    <span class="input-group-text">days</span>
                                                                </div>
                                                            </div>
                                                            <small class="form-text text-muted">
                                                                Check to cash out some Long Service Leave days
                                                            </small>
                                                        </div>
                                                    </div>
                                                    <div class="col-lg-4">
                                                        <div class="form-group">
                                                            <label for="lsl_total_days">
                                                                <i class="fas fa-calculator mr-1"></i>
                                                                Total Days
                                                            </label>
                                                            <div class="input-group">
                                                                <input type="number" class="form-control"
                                                                    id="lsl_total_days" name="lsl_total_days"
                                                                    min="0" value="0" readonly>
                                                                <div class="input-group-append">
                                                                    <span class="input-group-text">days</span>
                                                                </div>
                                                            </div>
                                                            <small class="form-text text-muted">
                                                                Sum of Leave Days + Cash Out Days
                                                            </small>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Submit Request
                                </button>
                                <a href="{{ route('leave.requests.index') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="col-md-4">
                    <!-- Approval Preview Card -->
                    @include('components.approval-status-card', [
                        'documentType' => 'leave_request',
                        'mode' => 'preview',
                        'title' => 'Approval Preview',
                        'projectId' => old('project_id'),
                        'departmentId' => old('department_id'),
                        'projectName' => $projects->where('id', old('project_id'))->first()->project_name ?? null,
                        'departmentName' =>
                            $departments->where('id', old('department_id'))->first()->department_name ?? null,
                        'requestReason' => null,
                    ])

                    <!-- Leave Balance Card -->
                    <div class="card card-success card-outline elevation-3 mt-3">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-wallet mr-2"></i>
                                <strong>Leave Balance</strong>
                            </h3>
                        </div>
                        <div class="card-body">
                            <div id="leave_balance_info">
                                <div class="text-center py-3">
                                    <i class="fas fa-info-circle text-info"></i>
                                    <div class="mt-2">Select an employee to view leave balance</div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
@endsection

@section('styles')
    <!-- Tempusdominus Bootstrap 4 -->
    <link rel="stylesheet"
        href="{{ asset('assets/plugins/tempusdominus-bootstrap-4/css/tempusdominus-bootstrap-4.min.css') }}">
    <!-- Date Range Picker -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/daterangepicker/daterangepicker.css') }}">
    <style>
        .custom-file-label::after {
            content: "Browse";
        }

        .custom-file-label.selected::after {
            content: "";
        }
    </style>
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
@endsection

@section('scripts')
    <!-- Moment.js -->
    <script src="{{ asset('assets/plugins/moment/moment.min.js') }}"></script>
    <!-- Date Range Picker -->
    <script src="{{ asset('assets/plugins/daterangepicker/daterangepicker.js') }}"></script>
    <!-- Select2 -->
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            // ============================================================================
            // SIMPLIFIED CREATE FORM - CLEAN & MAINTAINABLE
            // ============================================================================

            // Route configuration
            const routes = {
                projectInfo: '{{ route('leave.requests.project-info', ':id') }}',
                employeesByProject: '{{ route('leave.requests.employees-by-project', ':id') }}',
                leaveTypesByEmployee: '{{ route('leave.requests.leave-types-by-employee', ':id') }}',
                employeeLeaveBalance: '{{ route('leave.requests.employee.leave-balance', ':id') }}',
                leaveTypeInfo: '{{ route('leave.requests.leave-type.info', ':id') }}',
                leavePeriod: '{{ route('leave.requests.leave-period', [':employee', ':leavetype']) }}'
            };

            // Project and department data for display
            const projects = @json($projects->pluck('project_name', 'id'));
            const departments = @json($departments->pluck('department_name', 'id'));

            // Project data with leave type information
            const projectData = @json($projects);

            // Initialize all components on page load
            initializeForm();

            // ============================================================================
            // LSL FLEXIBLE FUNCTIONALITY - ONLY FOR LONG SERVICE LEAVE
            // ============================================================================

            // Initialize LSL flexible functionality
            initializeLSLFlexible();

            function initializeLSLFlexible() {
                // Attach event handlers for LSL flexible
                $('#lsl_cashout_checkbox').on('change', onLSLCashoutCheckboxChange);
                $('#lsl_cashout_days').on('input', onLSLCashoutDaysChange);
                $('#lsl_taken_days').on('input', onLSLTakenDaysChange);
            }

            function onLSLCashoutCheckboxChange() {
                const isChecked = $(this).is(':checked');
                const $cashoutInput = $('#lsl_cashout_days');

                if (isChecked) {
                    $cashoutInput.prop('disabled', false);
                    $cashoutInput.focus();
                } else {
                    $cashoutInput.prop('disabled', true);
                    $cashoutInput.val(0);
                }

                calculateLSLFlexible();
            }

            function onLSLCashoutDaysChange() {
                calculateLSLFlexible();
            }

            function onLSLTakenDaysChange() {
                calculateLSLFlexible();
            }


            function calculateLSLFlexible() {
                // Get taken days from manual input or calculate from date range if empty
                let takenDays = parseInt($('#lsl_taken_days').val()) || 0;

                // Always calculate from date range and update the field
                const calculatedDays = calculateActiveDaysFromDateRange();
                if (calculatedDays > 0) {
                    $('#lsl_taken_days').val(calculatedDays);
                    takenDays = calculatedDays;
                }

                const cashoutDays = parseInt($('#lsl_cashout_days').val()) || 0;
                const totalDays = takenDays + cashoutDays;

                // Update total days display
                $('#lsl_total_days').val(totalDays);

                // Get remaining days from selected leave type option
                const $option = $('#leave_type_id option:selected');
                const remainingDays = parseInt($option.data('remaining')) || 0;

                // Clear previous validation
                clearLSLValidation();

                // Validation - tidak boleh melebihi remaining_days
                if (totalDays > remainingDays && remainingDays > 0) {
                    // Add visual validation to input fields
                    $('#lsl_cashout_days').addClass('is-invalid');
                    $('#lsl_taken_days').addClass('is-invalid');
                    $('#lsl_total_days').addClass('is-invalid');

                    // Show validation alert like in the image
                    showLSLValidation(
                        `Total days (${totalDays}) exceeds remaining leave balance (${remainingDays} days)`);
                } else {
                    // Remove validation classes
                    $('#lsl_cashout_days').removeClass('is-invalid');
                    $('#lsl_taken_days').removeClass('is-invalid');
                    $('#lsl_total_days').removeClass('is-invalid');
                }

                // Update hidden field for form submission
                $('#total_days_hidden').val(totalDays);
            }

            function calculateActiveDaysFromDateRange() {
                const startDate = $('#start_date').val();
                const endDate = $('#end_date').val();

                if (!startDate || !endDate) {
                    return 0;
                }

                const start = moment(startDate);
                const end = moment(endDate);

                if (!start.isValid() || !end.isValid()) {
                    return 0;
                }

                // Calculate total days excluding disabled dates (weekends for non-roster projects)
                const projectId = $('#project_id').val();
                const isNonRosterProject = isProjectNonRoster(projectId);

                let activeDays = 0;
                const current = start.clone();

                while (current.isSameOrBefore(end, 'day')) {
                    // For non-roster projects, exclude weekends (Saturday=6, Sunday=0)
                    if (isNonRosterProject) {
                        if (current.day() !== 0 && current.day() !== 6) {
                            activeDays++;
                        }
                    } else {
                        // For roster projects, count all days
                        activeDays++;
                    }
                    current.add(1, 'day');
                }

                return activeDays;
            }

            // Function to show/hide LSL flexible section - ONLY FOR LSL
            function toggleLSLFlexibleSection(show) {
                if (show) {
                    $('#lsl_flexible_section').slideDown();
                    // Hide original total days input and show LSL specific inputs
                    $('#total_days_input').closest('.form-group').hide();
                } else {
                    $('#lsl_flexible_section').slideUp();
                    // Show original total days input for other leave types
                    $('#total_days_input').closest('.form-group').show();
                    // Reset values when hiding
                    $('#lsl_cashout_checkbox').prop('checked', false);
                    $('#lsl_cashout_days').val(0).prop('disabled', true);
                    $('#lsl_taken_days').val(0);
                    $('#lsl_total_days').val(0);
                    // Clear LSL validation when hiding
                    clearLSLValidation();
                    calculateLSLFlexible();
                }
            }

            // ============================================================================
            // INITIALIZATION
            // ============================================================================

            function initializeForm() {
                // Initialize Select2
                $('.select2bs4').select2({
                    theme: 'bootstrap4',
                    width: '100%'
                }).on('select2:open', function() {
                    document.querySelector('.select2-search__field').focus();
                });

                // Initialize Date Pickers
                setupDatePickers();

                // Initialize File Input
                setupFileInput();

                // Attach Event Handlers
                attachEventHandlers();

                // Restore old form data if validation fails
                restoreOldData();
            }

            // ============================================================================
            // DATE PICKERS SETUP
            // ============================================================================

            function setupDatePickers() {
                // Leave date range picker - will be reconfigured based on project selection
                configureLeaveDatePicker();

                // Back to work date picker
                $('#back_to_work_date').daterangepicker({
                    singleDatePicker: true,
                    autoUpdateInput: false,
                    locale: {
                        cancelLabel: 'Clear',
                        format: 'DD/MM/YYYY'
                    },
                    minDate: moment(),
                    opens: 'left'
                }).on('apply.daterangepicker', function(ev, picker) {
                    $(this).val(picker.startDate.format('DD/MM/YYYY'));
                }).on('cancel.daterangepicker', function() {
                    $(this).val('');
                });
            }

            function configureLeaveDatePicker() {
                const projectId = $('#project_id').val();
                const isNonRosterProject = isProjectNonRoster(projectId);

                // Show/hide weekend info based on project type
                if (isNonRosterProject) {
                    $('#weekend_info').show();
                } else {
                    $('#weekend_info').hide();
                }

                // Base configuration
                const baseConfig = {
                    autoUpdateInput: false,
                    locale: {
                        cancelLabel: 'Clear',
                        format: 'DD/MM/YYYY'
                    },
                    opens: 'left'
                };

                // Add weekend disable for non-roster projects
                if (isNonRosterProject) {
                    baseConfig.isInvalidDate = function(date) {
                        // Disable Saturday (6) and Sunday (0)
                        return date.day() === 0 || date.day() === 6;
                    };
                }

                // Destroy existing daterangepicker and recreate with new config
                $('#leave_date').data('daterangepicker') && $('#leave_date').data('daterangepicker').remove();

                $('#leave_date').daterangepicker(baseConfig)
                    .on('apply.daterangepicker', function(ev, picker) {
                        $(this).val(
                            `${picker.startDate.format('DD/MM/YYYY')} - ${picker.endDate.format('DD/MM/YYYY')}`
                        );
                        $('#start_date').val(picker.startDate.format('YYYY-MM-DD'));
                        $('#end_date').val(picker.endDate.format('YYYY-MM-DD'));
                        calculateTotalDays();
                        calculateLSLFlexible(); // Calculate LSL flexible when date changes
                    })
                    .on('cancel.daterangepicker', function() {
                        $(this).val('');
                        $('#start_date, #end_date, #total_days_input, #total_days_hidden').val('');
                    });
            }

            function isProjectNonRoster(projectId) {
                if (!projectId) return false;

                const project = projectData.find(p => p.id == projectId);
                return project && project.leave_type === 'non_roster';
            }

            // ============================================================================
            // FILE INPUT SETUP
            // ============================================================================

            function setupFileInput() {
                $('.custom-file-input').on('change', function() {
                    const fileName = $(this).val().split('\\').pop();
                    $(this).next('.custom-file-label').addClass("selected").html(fileName);
                });
            }

            // ============================================================================
            // EVENT HANDLERS
            // ============================================================================

            function attachEventHandlers() {
                $('#project_id').on('change', onProjectChange);
                $('#employee_id').on('change', onEmployeeChange);
                $('#leave_type_id').on('change', onLeaveTypeChange);
                $('#total_days_input').on('input', onTotalDaysChange);
            }

            // ============================================================================
            // RESTORE OLD DATA (after validation fails)
            // ============================================================================

            function restoreOldData() {
                const oldProjectId = '{{ old('project_id') }}';
                const oldEmployeeId = '{{ old('employee_id') }}';
                const oldLeaveTypeId = '{{ old('leave_type_id') }}';

                // Restore project and load employees
                if (oldProjectId) {
                    loadProjectEmployees(oldProjectId);

                    // Configure date picker based on restored project
                    configureLeaveDatePicker();

                    // Restore employee after employees loaded
                    if (oldEmployeeId) {
                        setTimeout(function() {
                            $('#employee_id').val(oldEmployeeId).trigger('change');

                            // Restore leave type after leave types loaded
                            if (oldLeaveTypeId) {
                                setTimeout(function() {
                                    $('#leave_type_id').val(oldLeaveTypeId).trigger('change');
                                }, 100);
                            }
                        }, 100);
                    }
                }
            }

            // ============================================================================
            // EVENT HANDLERS - PROJECT
            // ============================================================================

            function onProjectChange() {
                const projectId = $(this).val();

                if (!projectId) {
                    resetEmployeeField();
                    resetLeaveTypeField();
                    resetLeaveBalanceDisplay();
                    // Reconfigure date picker for default behavior
                    configureLeaveDatePicker();
                    return;
                }

                // Load employees for selected project
                loadProjectEmployees(projectId);

                // Reconfigure date picker based on project type
                configureLeaveDatePicker();

                // Recalculate total days if dates are already selected
                if ($('#start_date').val() && $('#end_date').val()) {
                    calculateTotalDays();
                }
            }

            // ============================================================================
            // EVENT HANDLERS - EMPLOYEE
            // ============================================================================

            function onEmployeeChange() {
                const employeeId = $(this).val();

                if (!employeeId) {
                    resetLeaveTypeField();
                    resetLeaveBalanceDisplay();
                    $('#leave_period').val('');
                    return;
                }

                // Load employee leave balance and available leave types
                loadEmployeeLeaveBalance(employeeId);
                loadEmployeeLeaveTypes(employeeId);

                // If leave type already selected, load period
                const leaveTypeId = $('#leave_type_id').val();
                if (leaveTypeId) {
                    loadEmployeeLeavePeriod(employeeId, leaveTypeId);
                }
            }

            // ============================================================================
            // EVENT HANDLERS - LEAVE TYPE
            // ============================================================================

            function onLeaveTypeChange() {
                const leaveTypeId = $(this).val();
                const employeeId = $('#employee_id').val();

                if (!leaveTypeId) {
                    hideConditionalFields();
                    $('#leave_period').val('');
                    clearValidation();
                    return;
                }

                // Load leave type info (for conditional fields)
                loadLeaveTypeInfo(leaveTypeId);

                // Load leave period if employee selected
                if (employeeId) {
                    loadEmployeeLeavePeriod(employeeId, leaveTypeId);
                }

                // Validate total days if entered
                const totalDays = parseInt($('#total_days_input').val());
                if (totalDays > 0) {
                    validateLeaveBalance(totalDays);
                }
            }

            // ============================================================================
            // EVENT HANDLERS - TOTAL DAYS
            // ============================================================================

            function onTotalDaysChange() {
                const totalDays = parseInt($(this).val());

                if (totalDays > 0) {
                    $('#total_days_hidden').val(totalDays);
                    validateLeaveBalance(totalDays);
                } else {
                    $('#total_days_hidden').val('');
                    clearValidation();
                }
            }

            // ============================================================================
            // AJAX CALLS - PROJECT
            // ============================================================================

            function loadProjectEmployees(projectId) {
                const url = routes.employeesByProject.replace(':id', projectId);

                $.get(url)
                    .done(function(data) {
                        const $select = $('#employee_id');
                        $select.empty().append('<option value="">Select Employee</option>');

                        if (data.employees && data.employees.length > 0) {
                            data.employees.forEach(function(employee) {
                                $select.append(
                                    `<option value="${employee.id}">${employee.nik} - ${employee.fullname}</option>`
                                );
                            });
                            $select.prop('disabled', false);
                        }
                    })
                    .fail(function() {
                        showAlert('Failed to load employees', 'error');
                    });
            }

            // ============================================================================
            // AJAX CALLS - EMPLOYEE
            // ============================================================================

            function loadEmployeeLeaveBalance(employeeId) {
                const url = routes.employeeLeaveBalance.replace(':id', employeeId);

                $.get(url)
                    .done(function(data) {
                        if (data.success && data.leave_balance) {
                            displayLeaveBalance(data.leave_balance);

                            // Load approval preview if we have project and department data
                            if (data.employee && data.employee.project_id && data.employee.department_id) {
                                const projectName = projects[data.employee.project_id] || null;
                                const departmentName = departments[data.employee.department_id] || null;
                                loadApprovalPreview(data.employee.project_id, data.employee.department_id, data
                                    .employee.level_id, projectName, departmentName);
                            }
                        } else {
                            resetLeaveBalanceDisplay();
                        }
                    })
                    .fail(function() {
                        showAlert('Failed to load leave balance', 'error');
                    });
            }

            function loadEmployeeLeaveTypes(employeeId) {
                const url = routes.leaveTypesByEmployee.replace(':id', employeeId);

                $.get(url)
                    .done(function(data) {
                        const $select = $('#leave_type_id');
                        const currentValue = $select.val(); // Preserve current selection

                        $select.empty().append('<option value="">Select Leave Type</option>');

                        if (data.leaveTypes && data.leaveTypes.length > 0) {
                            data.leaveTypes.forEach(function(item) {
                                $select.append(
                                    `<option value="${item.leave_type_id}" data-remaining="${item.remaining_days}">
                                        ${item.leave_type.name} (${item.leave_type.code}) - ${item.remaining_days} days remaining
                                    </option>`
                                );
                            });

                            $select.prop('disabled', false);

                            // Restore previous selection if exists
                            if (currentValue) {
                                $select.val(currentValue);
                            }
                        }
                    })
                    .fail(function() {
                        showAlert('Failed to load leave types', 'error');
                    });
            }

            function loadEmployeeLeavePeriod(employeeId, leaveTypeId) {
                const url = routes.leavePeriod
                    .replace(':employee', employeeId)
                    .replace(':leavetype', leaveTypeId);

                $.get(url)
                    .done(function(data) {
                        if (data.success && data.leave_period) {
                            $('#leave_period').val(data.leave_period);
                        } else {
                            $('#leave_period').val('');
                        }
                    })
                    .fail(function() {
                        $('#leave_period').val('');
                    });
            }

            // ============================================================================
            // AJAX CALLS - LEAVE TYPE
            // ============================================================================

            function loadLeaveTypeInfo(leaveTypeId) {
                const url = routes.leaveTypeInfo.replace(':id', leaveTypeId);

                $.get(url)
                    .done(function(data) {
                        if (data.success && data.leave_type) {
                            handleConditionalFields(data.leave_type.category);

                            // Check if this is LSL (Long Service Leave)
                            const isLSL = data.leave_type.name &&
                                (data.leave_type.name.toLowerCase().includes('long service') ||
                                    data.leave_type.name.toLowerCase().includes('cuti panjang') ||
                                    data.leave_type.name.toLowerCase().includes('lsl'));

                            // Show/hide LSL flexible section
                            toggleLSLFlexibleSection(isLSL);
                        } else {
                            hideConditionalFields();
                            toggleLSLFlexibleSection(false);
                        }
                    })
                    .fail(function() {
                        hideConditionalFields();
                        toggleLSLFlexibleSection(false);
                    });
            }

            // ============================================================================
            // UI HELPERS - CONDITIONAL FIELDS
            // ============================================================================

            function handleConditionalFields(category) {
                hideConditionalFields();

                const cat = category ? category.toLowerCase() : '';

                if (cat === 'unpaid') {
                    $('#reason_field').show();
                    $('#reason').prop('required', true);
                } else if (cat === 'paid') {
                    $('#document_field').show();
                }
            }

            function hideConditionalFields() {
                $('#reason_field, #document_field').hide();
                $('#reason').prop('required', false).val('');
                $('#supporting_document').val('');
            }

            // ============================================================================
            // UI HELPERS - LEAVE BALANCE DISPLAY
            // ============================================================================

            function displayLeaveBalance(balances) {
                if (!balances || balances.length === 0) {
                    resetLeaveBalanceDisplay();
                    return;
                }

                let html = '<div class="table-responsive"><table class="table table-sm">';
                html += '<thead><tr><th>Leave Type</th><th class="text-right">Balance</th></tr></thead><tbody>';

                balances.forEach(function(item) {
                    html += `<tr>
                        <td>${item.leave_type} (${item.leave_type_code})</td>
                        <td class="text-right">
                            <span class="badge badge-info">${item.remaining_days} days</span>
                        </td>
                    </tr>`;
                });

                html += '</tbody></table></div>';
                $('#leave_balance_info').html(html);
            }

            // ============================================================================
            // UI HELPERS - CALCULATIONS
            // ============================================================================

            function calculateTotalDays() {
                const startDate = $('#start_date').val();
                const endDate = $('#end_date').val();

                if (!startDate || !endDate) {
                    $('#total_days_input, #total_days_hidden').val('');
                    return;
                }

                const start = moment(startDate);
                const end = moment(endDate);

                if (!start.isValid() || !end.isValid()) {
                    $('#total_days_input, #total_days_hidden').val('');
                    return;
                }

                // Calculate total days excluding disabled dates (weekends for non-roster projects)
                const projectId = $('#project_id').val();
                const isNonRosterProject = isProjectNonRoster(projectId);

                let activeDays = 0;
                const current = start.clone();

                while (current.isSameOrBefore(end, 'day')) {
                    // For non-roster projects, exclude weekends (Saturday=6, Sunday=0)
                    if (isNonRosterProject) {
                        if (current.day() !== 0 && current.day() !== 6) {
                            activeDays++;
                        }
                    } else {
                        // For roster projects, count all days
                        activeDays++;
                    }
                    current.add(1, 'day');
                }

                $('#total_days_input, #total_days_hidden').val(activeDays);

                validateLeaveBalance(activeDays);
            }

            // ============================================================================
            // VALIDATION
            // ============================================================================

            function validateLeaveBalance(requestedDays) {
                const leaveTypeId = $('#leave_type_id').val();
                const employeeId = $('#employee_id').val();

                if (!leaveTypeId || !employeeId) {
                    clearValidation();
                    return;
                }

                // Get remaining days from selected option
                const $option = $('#leave_type_id option:selected');
                const remainingDays = parseInt($option.data('remaining')) || 0;

                if (requestedDays > remainingDays) {
                    showValidation(
                        `Total days (${requestedDays}) exceeds remaining leave balance (${remainingDays} days)`,
                        'error'
                    );
                } else {
                    clearValidation();
                }
            }

            function showValidation(message, type = 'warning') {
                clearValidation();

                const alertClass = type === 'error' ? 'alert-danger' : 'alert-warning';
                const iconClass = type === 'error' ? 'fas fa-exclamation-triangle' : 'fas fa-info-circle';

                const html = `
                    <div class="alert ${alertClass} alert-dismissible fade show mt-2" id="validation-alert">
                        <i class="${iconClass} mr-2"></i>${message}
                    </div>
                `;

                $('#total_days_hidden').after(html);
            }

            function clearValidation() {
                $('#validation-alert').remove();
            }

            // LSL Validation Functions
            function showLSLValidation(message) {
                clearLSLValidation();

                const html = `
                    <div class="alert alert-danger alert-dismissible fade show mt-2" id="lsl-validation-alert">
                        <i class="fas fa-exclamation-triangle mr-2"></i>${message}
                    </div>
                `;

                $('#lsl_flexible_section .card-body').append(html);
            }

            function clearLSLValidation() {
                $('#lsl-validation-alert').remove();
            }

            // ============================================================================
            // RESET FUNCTIONS
            // ============================================================================

            function resetEmployeeField() {
                $('#employee_id')
                    .prop('disabled', true)
                    .empty()
                    .append('<option value="">Select Employee</option>');
            }

            function resetLeaveTypeField() {
                $('#leave_type_id')
                    .prop('disabled', true)
                    .empty()
                    .append('<option value="">Select Leave Type</option>');
            }

            function resetLeaveBalanceDisplay() {
                $('#leave_balance_info').html(`
                    <div class="text-center py-3">
                        <i class="fas fa-info-circle text-muted"></i>
                        <div class="mt-2 text-muted">Select an employee to view leave balance</div>
                    </div>
                `);
            }

            // ============================================================================
            // APPROVAL PREVIEW
            // ============================================================================

            function loadApprovalPreview(projectId, departmentId, levelId, projectName = null, departmentName =
                null) {
                if (!projectId || !departmentId) {
                    $('#approvalPreview').html(`
                        <div class="text-center py-3">
                            <i class="fas fa-info-circle text-info"></i>
                            <div class="mt-2">Select employee to see approval flow</div>
                        </div>
                    `);
                    return;
                }

                // Show loading state
                $('#approvalPreview').html(`
                    <div class="text-center py-3">
                        <i class="fas fa-spinner fa-spin text-info"></i>
                        <div class="mt-2">Loading approval flow...</div>
                    </div>
                `);

                // Prepare request data
                const requestData = {
                    project_id: projectId,
                    department_id: departmentId,
                    document_type: 'leave_request'
                };

                // Add level_id if available
                if (levelId) {
                    requestData.level_id = levelId;
                }

                console.log('Loading approval preview with data:', requestData);

                // Fetch approval stages
                $.ajax({
                    url: '{{ route('approval.stages.preview') }}',
                    method: 'GET',
                    data: requestData,
                    success: function(response) {
                        console.log('Approval preview response:', response);

                        if (response.success && response.approvers && response.approvers.length > 0) {
                            let html = '<div class="approval-flow preview-mode">';
                            html +=
                                '<h6 class="text-info mb-3"><i class="fas fa-route"></i> Approval Flow</h6>';

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
                            $('#approvalPreview').html(html);
                        } else {
                            $('#approvalPreview').html(`
                                <div class="text-center text-muted py-3">
                                    <i class="fas fa-info-circle"></i>
                                    <div class="mt-2">No approval flow configured for this employee</div>
                                    <small class="text-muted">
                                        ${projectName && departmentName ?
                                            `Project: ${projectName}, Department: ${departmentName}` :
                                            `Project ID: ${projectId}, Department ID: ${departmentId}`
                                        }
                                    </small>
                                </div>
                            `);
                        }
                    },
                    error: function(xhr, status, error) {
                        console.error('Approval preview error:', error);
                        $('#approvalPreview').html(`
                            <div class="text-center text-danger py-3">
                                <i class="fas fa-exclamation-triangle"></i>
                                <div class="mt-2">Failed to load approval flow</div>
                                <small class="text-muted">${error}</small>
                                ${projectName && departmentName ?
                                    `<br><small class="text-muted">Project: ${projectName}, Department: ${departmentName}</small>` :
                                    ''
                                }
                            </div>
                        `);
                    }
                });
            }

            // ============================================================================
            // UTILITY FUNCTIONS
            // ============================================================================

            function showAlert(message, type = 'info') {
                console.error(message);
                // You can implement toast notification here if needed
            }

        });
    </script>
@endsection
