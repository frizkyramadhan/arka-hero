@extends('layouts.main')

@section('title', 'Create My Leave Request')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Create My Leave Request</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard.personal') }}">My Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('leave.my-requests') }}">My Leave Request</a></li>
                        <li class="breadcrumb-item active">Create</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <form method="POST" action="{{ route('leave.my-requests.store') }}" enctype="multipart/form-data">
                @csrf
                {{-- Hidden fields for employee and project --}}
                <input type="hidden" name="employee_id" id="employee_id" value="{{ $defaultEmployeeId }}">
                <input type="hidden" name="project_id" id="project_id" value="{{ $defaultProject->id ?? '' }}">

                <div class="row">
                    <div class="col-md-8">
                        <div class="card card-primary card-outline elevation-3">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-calendar-plus mr-2"></i>
                                    <strong>My Leave Request Form</strong>
                                </h3>
                            </div>

                            <div class="card-body">
                                {{-- Employee Info Display --}}
                                <div class="row mb-3">
                                    <div class="col-md-6">
                                        <div class="info-box bg-light">
                                            <span class="info-box-icon bg-info"><i class="fas fa-user"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Employee</span>
                                                <span
                                                    class="info-box-number">{{ auth()->user()->employee->fullname ?? 'N/A' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="info-box bg-light">
                                            <span class="info-box-icon bg-primary"><i class="fas fa-building"></i></span>
                                            <div class="info-box-content">
                                                <span class="info-box-text">Project</span>
                                                <span class="info-box-number">{{ $defaultProject->project_code ?? 'N/A' }} -
                                                    {{ $defaultProject->project_name ?? 'N/A' }}</span>
                                            </div>
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
                                                @foreach ($leaveTypes as $leaveType)
                                                    <option value="{{ $leaveType->id }}"
                                                        {{ old('leave_type_id', $selectedLeaveTypeId ?? '') == $leaveType->id ? 'selected' : '' }}>
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
                                                    value="@if (old('start_date') && old('end_date')) @php
                                                        $start = old('start_date');
                                                        $end = old('end_date');
                                                        if (strpos($start, '/') === false) {
                                                            try { $start = \Carbon\Carbon::parse($start)->format('d/m/Y'); } catch (\Exception $e) { $start = old('start_date'); }
                                                        }
                                                        if (strpos($end, '/') === false) {
                                                            try { $end = \Carbon\Carbon::parse($end)->format('d/m/Y'); } catch (\Exception $e) { $end = old('end_date'); }
                                                        }
                                                        echo $start . ' - ' . $end;
                                                    @endphp @endif">
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
                                                <input type="text" id="back_to_work_date"
                                                    class="form-control @error('back_to_work_date') is-invalid @enderror"
                                                    value="@if (old('back_to_work_date')) @php
                                                        $date = old('back_to_work_date');
                                                        if (strpos($date, '/') === false) {
                                                            try { $date = \Carbon\Carbon::parse($date)->format('d/m/Y'); } catch (\Exception $e) { $date = old('back_to_work_date'); }
                                                        }
                                                        echo $date;
                                                    @endphp @endif"
                                                    placeholder="Select back to work date">
                                                <input type="hidden" name="back_to_work_date"
                                                    id="back_to_work_date_hidden"
                                                    value="@if (old('back_to_work_date')) @php
                                                        $date = old('back_to_work_date');
                                                        if (strpos($date, '/') !== false) {
                                                            try { $date = \Carbon\Carbon::createFromFormat('d/m/Y', $date)->format('Y-m-d'); } catch (\Exception $e) { $date = old('back_to_work_date'); }
                                                        } else {
                                                            try { $date = \Carbon\Carbon::parse($date)->format('Y-m-d'); } catch (\Exception $e) { $date = old('back_to_work_date'); }
                                                        }
                                                        echo $date;
                                                    @endphp @endif">
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
                        </div>
                    </div>

                    <div class="col-md-4">
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
                                    'selectedApprovers' => old('manual_approvers', []),
                                    'required' => true,
                                    'multiple' => true,
                                    'helpText' => 'Pilih minimal 1 approver dengan role approver',
                                    'documentType' => 'leave_request',
                                ])
                            </div>
                        </div>

                        <!-- Action Buttons Card -->
                        <div class="card card-outline elevation-2 mt-3">
                            <div class="card-body p-3">
                                <button type="submit" class="btn btn-success btn-block mb-2">
                                    <i class="fas fa-paper-plane mr-2"></i>Save & Submit
                                </button>
                                <a href="{{ route('leave.my-requests') }}" class="btn btn-secondary btn-block">
                                    <i class="fas fa-times-circle mr-2"></i>Cancel
                                </a>
                            </div>
                        </div>

                        <!-- Approval Preview Card -->
                        {{-- @include('components.approval-status-card', [
                            'documentType' => 'leave_request',
                            'mode' => 'preview',
                            'title' => 'Approval Preview',
                            'projectId' => old('project_id'),
                            'departmentId' => old('department_id'),
                            'projectName' =>
                                $projects->where('id', old('project_id'))->first()->project_name ?? null,
                            'departmentName' =>
                                $departments->where('id', old('department_id'))->first()->department_name ?? null,
                            'requestReason' => null,
                        ]) --}}

                        <!-- Leave Balance Card -->
                        <div class="card card-success card-outline elevation-3 mt-3">
                            <div class="card-header py-2">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-wallet mr-1"></i>
                                    <strong>Leave Balance</strong>
                                </h5>
                            </div>
                            <div class="card-body p-0" style="max-height: 300px; overflow-y: auto;">
                                <div id="leave_balance_info" class="p-2">
                                    <div class="text-center py-2">
                                        <i class="fas fa-spinner fa-spin text-info"></i>
                                        <small class="d-block mt-1">Loading...</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
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

        /* Leave Balance Card Styles - Compact */
        #leave_balance_info {
            min-height: 60px;
        }

        #leave_balance_info .table {
            font-size: 0.95rem;
        }

        #leave_balance_info .table thead th {
            position: sticky;
            top: 0;
            background-color: #f8f9fa;
            z-index: 10;
            border-bottom: 1px solid #dee2e6;
            padding: 0.25rem 0.5rem;
        }

        #leave_balance_info .table tbody td {
            padding: 0.25rem 0.5rem;
        }

        #leave_balance_info .table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .card-body::-webkit-scrollbar {
            width: 6px;
        }

        .card-body::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }

        .card-body::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 3px;
        }

        .card-body::-webkit-scrollbar-thumb:hover {
            background: #555;
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

            // Fixed employee and project for personal request
            const employeeId = "{{ $defaultEmployeeId }}";
            const projectId = {{ $defaultProject->id ?? 'null' }};
            const currentProject = projectData.find(p => p.id == projectId);

            // Store current entitlement period for date picker limits
            let currentEntitlementPeriod = {
                start: null,
                end: null
            };

            // Initialize all components on page load
            initializeForm();

            // Auto-load leave balance and leave types for current user
            if (employeeId) {
                loadEmployeeLeaveBalance(employeeId);
                loadEmployeeLeaveTypes(employeeId);
            }

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
                const selectedProjectId = $('#project_id').val() || projectId;
                const isNonRosterProject = isProjectNonRoster(selectedProjectId);

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

                // Attach Event Handlers (simplified for personal request)
                attachEventHandlers();

                // Configure date picker based on user's project
                if (projectId && currentProject) {
                    configureLeaveDatePicker();
                }

                // Restore old form data if validation fails
                restoreOldData();
            }

            // ============================================================================
            // DATE PICKERS SETUP
            // ============================================================================

            function setupDatePickers() {
                // Leave date range picker - configured based on user's project
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
                    $('#back_to_work_date_hidden').val(picker.startDate.format('YYYY-MM-DD'));
                }).on('cancel.daterangepicker', function() {
                    $(this).val('');
                    $('#back_to_work_date_hidden').val('');
                });
            }

            function configureLeaveDatePicker() {
                const selectedProjectId = $('#project_id').val() || projectId;
                const isNonRosterProject = isProjectNonRoster(selectedProjectId);

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

                // Add minDate and maxDate based on entitlement period
                if (currentEntitlementPeriod.start && currentEntitlementPeriod.end) {
                    baseConfig.minDate = currentEntitlementPeriod.start;
                    baseConfig.maxDate = currentEntitlementPeriod.end;
                } else {
                    // Default: allow dates from today onwards
                    baseConfig.minDate = moment();
                }

                // Add weekend disable for non-roster projects
                if (isNonRosterProject) {
                    const originalIsInvalidDate = baseConfig.isInvalidDate;
                    baseConfig.isInvalidDate = function(date) {
                        // Disable Saturday (6) and Sunday (0)
                        if (date.day() === 0 || date.day() === 6) {
                            return true;
                        }
                        // Also check original isInvalidDate if exists
                        if (originalIsInvalidDate) {
                            return originalIsInvalidDate.call(this, date);
                        }
                        return false;
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
                // Simplified handlers for personal request - no project/employee change needed
                $('#leave_type_id').on('change', onLeaveTypeChange);
                $('#total_days_input').on('input', onTotalDaysChange);
            }

            // ============================================================================
            // RESTORE OLD DATA (after validation fails)
            // ============================================================================

            function restoreOldData() {
                const oldLeaveTypeId = '{{ old('leave_type_id', $selectedLeaveTypeId ?? '') }}';

                // Restore leave type if validation failed or pre-selected from dashboard
                if (oldLeaveTypeId) {
                    setTimeout(function() {
                        $('#leave_type_id').val(oldLeaveTypeId).trigger('change');
                    }, 100);
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

                if (!leaveTypeId) {
                    hideConditionalFields();
                    $('#leave_period').val('');
                    clearValidation();
                    // Reset entitlement period and reconfigure date picker
                    currentEntitlementPeriod.start = null;
                    currentEntitlementPeriod.end = null;
                    configureLeaveDatePicker();
                    return;
                }

                // Load leave type info (for conditional fields)
                loadLeaveTypeInfo(leaveTypeId);

                // Always load leave period when leave type is selected (employeeId is always available for personal request)
                if (employeeId) {
                    // Clear leave period first to show loading state
                    $('#leave_period').val('Loading...');
                    loadEmployeeLeavePeriod(employeeId, leaveTypeId);
                } else {
                    // If no employee selected, clear leave period
                    $('#leave_period').val('');
                    currentEntitlementPeriod.start = null;
                    currentEntitlementPeriod.end = null;
                    configureLeaveDatePicker();
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
                        resetLeaveBalanceDisplay();
                        showAlert('Failed to load leave balance', 'error');
                    });
            }

            function loadEmployeeLeaveTypes(employeeId) {
                const url = routes.leaveTypesByEmployee.replace(':id', employeeId);

                $.get(url)
                    .done(function(data) {
                        const $select = $('#leave_type_id');
                        const currentValue = $select.val(); // Preserve current selection
                        const currentEntitlementId = $select.find('option:selected').data(
                            'entitlement-id'); // Preserve entitlement ID

                        $select.empty().append('<option value="">Select Leave Type</option>');

                        if (data.leaveTypes && data.leaveTypes.length > 0) {
                            // Group by leave_type_id to detect duplicates
                            const leaveTypeGroups = {};
                            data.leaveTypes.forEach(function(item) {
                                if (!leaveTypeGroups[item.leave_type_id]) {
                                    leaveTypeGroups[item.leave_type_id] = [];
                                }
                                leaveTypeGroups[item.leave_type_id].push(item);
                            });

                            // Create options
                            data.leaveTypes.forEach(function(item) {
                                // Always use normal format (without period in option text)
                                const optionText =
                                    `${item.leave_type.name} (${item.leave_type.code}) - ${item.remaining_days} days remaining`;

                                $select.append(
                                    `<option value="${item.leave_type_id}"
                                        data-entitlement-id="${item.entitlement_id}"
                                        data-remaining="${item.remaining_days}"
                                        data-period-start="${item.period_start}"
                                        data-period-end="${item.period_end}"
                                        data-period-display="${item.period_display}">
                                        ${optionText}
                                    </option>`
                                );
                            });

                            $select.prop('disabled', false);

                            // Restore previous selection if exists
                            if (currentValue && currentEntitlementId) {
                                // Try to find exact match with entitlement ID
                                const matchingOption = $select.find(
                                    `option[value="${currentValue}"][data-entitlement-id="${currentEntitlementId}"]`
                                );
                                if (matchingOption.length) {
                                    matchingOption.prop('selected', true);
                                } else {
                                    // Fallback to just leave_type_id
                                    $select.val(currentValue);
                                }
                                // Trigger change event to load leave period for restored selection
                                $select.trigger('change');
                            } else if (currentValue) {
                                $select.val(currentValue);
                                $select.trigger('change');
                            }
                        }
                    })
                    .fail(function() {
                        showAlert('Failed to load leave types', 'error');
                    });
            }

            function loadEmployeeLeavePeriod(employeeId, leaveTypeId) {
                // Check if we have period info from selected option
                const $selectedOption = $('#leave_type_id option:selected');
                const periodStart = $selectedOption.data('period-start');
                const periodEnd = $selectedOption.data('period-end');

                // If period info is available from dropdown, use it directly (no API call needed)
                if (periodStart && periodEnd) {
                    const startMoment = moment(periodStart);
                    const endMoment = moment(periodEnd);
                    const periodDisplay = startMoment.format('DD MMM YYYY') + ' - ' + endMoment.format(
                        'DD MMM YYYY');

                    $('#leave_period').val(periodDisplay);
                    currentEntitlementPeriod.start = startMoment;
                    currentEntitlementPeriod.end = endMoment;
                    configureLeaveDatePicker();
                    return;
                }

                // Fallback to API call (for backward compatibility or if data not in dropdown)
                const url = routes.leavePeriod
                    .replace(':employee', employeeId)
                    .replace(':leavetype', leaveTypeId);

                $.get(url)
                    .done(function(data) {
                        if (data.success && data.leave_period) {
                            $('#leave_period').val(data.leave_period);

                            // Store period dates for date picker limits
                            if (data.period_start && data.period_end) {
                                currentEntitlementPeriod.start = moment(data.period_start);
                                currentEntitlementPeriod.end = moment(data.period_end);
                            } else {
                                currentEntitlementPeriod.start = null;
                                currentEntitlementPeriod.end = null;
                            }

                            // Reconfigure date picker with period limits
                            configureLeaveDatePicker();
                        } else {
                            $('#leave_period').val('');
                            currentEntitlementPeriod.start = null;
                            currentEntitlementPeriod.end = null;
                            configureLeaveDatePicker();
                        }
                    })
                    .fail(function() {
                        $('#leave_period').val('');
                        currentEntitlementPeriod.start = null;
                        currentEntitlementPeriod.end = null;
                        configureLeaveDatePicker();
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

                let html = '<table class="table table-sm table-hover mb-0 table-borderless">';
                html +=
                    '<thead class="thead-light sticky-top"><tr><th class="py-1" style="font-size: 0.85rem;">Leave Type</th><th class="text-right py-1" style="font-size: 0.85rem;">Balance</th></tr></thead><tbody>';

                balances.forEach(function(item) {
                    const badgeClass = item.remaining_days > 0 ? 'badge-info' : 'badge-secondary';
                    html += `<tr class="py-0">
                        <td class="py-1">
                            <small class="font-weight-bold d-block" style="font-size: 0.9rem;">${item.leave_type}</small>
                            <small class="text-muted" style="font-size: 0.8rem;">${item.leave_type_code}</small>
                        </td>
                        <td class="text-right align-middle py-1">
                            <span class="badge ${badgeClass}" style="font-size: 0.85rem; padding: 0.25rem 0.5rem;">${item.remaining_days}</span>
                        </td>
                    </tr>`;
                });

                html += '</tbody></table>';
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
                const selectedProjectId = $('#project_id').val() || projectId;
                const isNonRosterProject = isProjectNonRoster(selectedProjectId);

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
                    <div class="alert alert-info mb-0 py-2" style="font-size: 0.95rem;">
                        <div class="text-center">
                            <i class="fas fa-info-circle mb-2" style="font-size: 1.3rem;"></i>
                            <p class="mb-2 font-weight-bold">Leave balance/entitlement belum tersedia</p>
                            <p class="mb-1" style="font-size: 0.9rem;">Silakan hubungi HR HO Balikpapan untuk mengatur leave entitlement Anda.</p>
                            <p class="mb-0 mt-2">
                                <i class="fas fa-phone-alt mr-1"></i>
                                <strong>HR HO Balikpapan</strong>
                            </p>
                        </div>
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
            // FORM SUBMISSION HANDLER
            // ============================================================================

            // Ensure total_days is always set before form submission
            $('form').on('submit', function(e) {
                // Get total_days from hidden field
                let totalDays = $('#total_days_hidden').val();

                // If LSL section is visible, calculate from LSL fields
                if ($('#lsl_flexible_section').is(':visible')) {
                    const takenDays = parseInt($('#lsl_taken_days').val()) || 0;
                    const cashoutDays = parseInt($('#lsl_cashout_days').val()) || 0;
                    totalDays = takenDays + cashoutDays;
                } else {
                    // For non-LSL, use value from input or hidden field
                    totalDays = parseInt($('#total_days_input').val()) || parseInt($('#total_days_hidden')
                        .val()) || 0;
                }

                // Ensure total_days is set
                if (!totalDays || totalDays <= 0) {
                    e.preventDefault();
                    alert(
                        'Total days must be greater than 0. Please select a date range or enter total days.'
                    );
                    return false;
                }

                // Update hidden field with calculated value
                $('#total_days_hidden').val(totalDays);
            });

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
