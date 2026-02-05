@extends('layouts.main')

@section('title', 'Create Flight Request')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Create Flight Request</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('flight-requests.index') }}">Flight Requests</a></li>
                        <li class="breadcrumb-item active">Create</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <form method="POST" action="{{ route('flight-requests.store') }}" id="flightRequestForm">
                @csrf
                <div class="row">
                    <div class="col-md-8">
                        <!-- Request Type & Source Document Selection Card -->
                        <div class="card card-primary card-outline elevation-3 mb-3">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-list mr-2"></i>
                                    <strong>Request Information</strong>
                                </h3>
                            </div>
                            <div class="card-body">
                                <!-- Request Type -->
                                <div class="form-group">
                                    <label for="request_type">
                                        Select Request Type <span class="text-danger">*</span>
                                    </label>
                                    <select name="request_type" id="request_type"
                                        class="form-control select2bs4 @error('request_type') is-invalid @enderror"
                                        required>
                                        <option value="">-- Select Request Type --</option>
                                        <option value="leave_based"
                                            {{ old('request_type') == 'leave_based' ? 'selected' : '' }}>
                                            Leave Request (Cuti)
                                        </option>
                                        <option value="travel_based"
                                            {{ old('request_type') == 'travel_based' ? 'selected' : '' }}>
                                            Official Travel (LOT)
                                        </option>
                                        <option value="standalone"
                                            {{ old('request_type') == 'standalone' ? 'selected' : '' }}>
                                            Standalone
                                        </option>
                                    </select>
                                    @error('request_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <!-- Source Document Selection (Conditional) -->
                                <div class="form-group" id="source_document_group" style="display: none;">
                                    <label for="source_document_id" id="source_document_label">
                                        <span id="source_document_label_text">Select Document</span> <span
                                            class="text-danger">*</span>
                                    </label>
                                    <select name="source_document_id" id="source_document_id"
                                        class="form-control select2bs4">
                                        <option value="">-- Select Document --</option>
                                    </select>
                                    <input type="hidden" name="leave_request_id" id="leave_request_id" value="">
                                    <input type="hidden" name="official_travel_id" id="official_travel_id" value="">
                                </div>

                                <!-- Manual Input Checkbox (for Standalone only) -->
                                <div class="form-group" id="manual_input_group" style="display: none;">
                                    <div class="custom-control custom-checkbox">
                                        <input type="checkbox" class="custom-control-input" id="fill_manually"
                                            name="fill_manually" value="1">
                                        <label class="custom-control-label" for="fill_manually">
                                            Fill employee information manually
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Employee Information Card -->
                        <div class="card card-primary card-outline elevation-3 mb-3" id="employee_info_card"
                            style="display: none;">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-user mr-2"></i>
                                    <strong>Employee Information</strong>
                                </h3>
                            </div>
                            <div class="card-body">
                                <!-- Hidden fields for employee data -->
                                <input type="hidden" name="employee_id" id="employee_id" value="{{ old('employee_id') }}">
                                <input type="hidden" name="administration_id" id="administration_id"
                                    value="{{ old('administration_id') }}">

                                <!-- Compact Employee Info Display -->
                                <table class="table table-sm table-bordered mb-0 employee-info-table">
                                    <tbody>
                                        <tr>
                                            <td class="font-weight-bold">NAME:</td>
                                            <td>
                                                <input type="text" name="employee_name" id="employee_name"
                                                    class="form-control form-control-sm border-0 p-0"
                                                    value="{{ old('employee_name') }}" placeholder="Enter employee name">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">ID NUMBER / NIK:</td>
                                            <td>
                                                <input type="text" name="nik" id="nik"
                                                    class="form-control form-control-sm border-0 p-0"
                                                    value="{{ old('nik') }}" placeholder="Enter NIK">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">POSITION:</td>
                                            <td>
                                                <input type="text" name="position" id="position"
                                                    class="form-control form-control-sm border-0 p-0"
                                                    value="{{ old('position') }}" placeholder="Enter position">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">DEPT/DIVISION:</td>
                                            <td>
                                                <input type="text" name="department" id="department"
                                                    class="form-control form-control-sm border-0 p-0"
                                                    value="{{ old('department') }}" placeholder="Enter department">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">POH:</td>
                                            <td id="poh_display">-</td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">DOH:</td>
                                            <td id="doh_display">-</td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">PROJECT NUMBER:</td>
                                            <td>
                                                <input type="text" name="project" id="project"
                                                    class="form-control form-control-sm border-0 p-0"
                                                    value="{{ old('project') }}" placeholder="Enter project">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">PHONE NUMBER:</td>
                                            <td>
                                                <input type="text" name="phone_number" id="phone_number"
                                                    class="form-control form-control-sm border-0 p-0"
                                                    value="{{ old('phone_number') }}" placeholder="Enter phone number">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">PURPOSE OF TRAVEL:</td>
                                            <td>
                                                <textarea name="purpose_of_travel" id="purpose_of_travel"
                                                    class="form-control form-control-sm border-0 p-0 @error('purpose_of_travel') is-invalid @enderror" rows="2"
                                                    required style="resize: none;">{{ old('purpose_of_travel') }}</textarea>
                                                @error('purpose_of_travel')
                                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                                @enderror
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">TOTAL TRAVEL DAYS:</td>
                                            <td>
                                                <input type="text" name="total_travel_days" id="total_travel_days"
                                                    class="form-control form-control-sm border-0 p-0"
                                                    value="{{ old('total_travel_days') }}" placeholder="-">
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Flight Details -->
                        <div class="card card-info card-outline elevation-3" id="flight_details_card"
                            style="display: none;">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-route mr-2"></i>
                                    <strong>Flight Details</strong>
                                </h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-sm btn-success" id="addFlightDetail">
                                        <i class="fas fa-plus"></i> Add Flight Segment
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div id="flightDetailsContainer">
                                    <!-- Flight detail fields will be added here dynamically -->
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <!-- Notes Card -->
                        <div class="card card-secondary card-outline elevation-3 mb-3" id="notes_card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-sticky-note mr-2"></i>
                                    <strong>Notes</strong>
                                </h3>
                            </div>
                            <div class="card-body">
                                <textarea name="notes" id="notes" class="form-control" rows="3"
                                    placeholder="Additional notes or remarks (optional)">{{ old('notes') }}</textarea>
                            </div>
                        </div>

                        <!-- Manual Approvers -->
                        <div class="card card-warning card-outline elevation-3">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-user-check mr-2"></i>
                                    <strong>Approver Selection</strong>
                                </h3>
                            </div>
                            <div class="card-body">
                                @include('components.manual-approver-selector', [
                                    'name' => 'manual_approvers',
                                    'documentType' => 'flight_request',
                                    'selectedApprovers' => old('manual_approvers', []),
                                ])
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="card elevation-3">
                            <div class="card-body">
                                <div class="row mb-2">
                                    <div class="col-md-6 mb-2 mb-md-0">
                                        <button type="submit" name="submit_action" value="draft"
                                            class="btn btn-warning btn-block">
                                            <i class="fas fa-save mr-2"></i> Save as Draft
                                        </button>
                                    </div>
                                    <div class="col-md-6">
                                        <button type="submit" name="submit_action" value="submit"
                                            class="btn btn-success btn-block">
                                            <i class="fas fa-paper-plane mr-2"></i> Save & Submit
                                        </button>
                                    </div>
                                </div>
                                <a href="{{ route('flight-requests.index') }}" class="btn btn-secondary btn-block">
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
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/sweetalert2/sweetalert2.min.css') }}">
    <style>
        /* Compact Employee Information Style */
        #employee_info_card .card-body {
            padding: 1rem !important;
        }

        .employee-info-table {
            font-size: 0.875rem;
            margin-bottom: 0;
        }

        .employee-info-table tbody tr {
            height: 36px;
        }

        .employee-info-table td {
            padding: 0.35rem 0.6rem;
            vertical-align: middle;
            line-height: 1.5;
        }

        .employee-info-table td:first-child {
            width: 35%;
            font-weight: 600;
            white-space: nowrap;
        }

        .employee-info-table td:last-child {
            width: 65%;
        }

        .employee-info-table .form-control-sm {
            font-size: 0.875rem;
            padding: 0;
            border: none;
            background: transparent;
            box-shadow: none;
            height: 28px;
            line-height: 1.5;
        }

        .employee-info-table .form-control-sm:focus {
            background: #f8f9fa;
            border: 1px solid #80bdff;
            padding: 0.25rem 0.5rem;
            border-radius: 0.25rem;
            height: auto;
            min-height: 28px;
        }

        .employee-info-table textarea.form-control-sm {
            min-height: 28px;
            height: auto;
        }

        .employee-info-table textarea.form-control-sm:focus {
            min-height: 56px;
        }

        .employee-info-table textarea.form-control-sm {
            min-height: 50px;
        }

        .employee-info-table .font-weight-bold {
            width: 35%;
            background-color: #f8f9fa;
        }
    </style>
@endsection

@section('scripts')
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/sweetalert2/sweetalert2.min.js') }}"></script>
    <script>
        let detailIndex = 0;

        $(document).ready(function() {
            // Initialize Select2
            $('.select2bs4').select2({
                theme: 'bootstrap4',
                width: '100%'
            });

            // API Routes
            const apiRoutes = {
                leaveRequests: '{{ route('flight-requests.api.leave-requests') }}',
                officialTravels: '{{ route('flight-requests.api.official-travels') }}',
                employees: '{{ route('flight-requests.api.employees') }}'
            };

            // Handle Request Type Change
            $('#request_type').on('change', function() {
                const requestType = $(this).val();

                // Reset source document selection
                $('#source_document_id').val('').trigger('change');
                $('#leave_request_id').val('');
                $('#official_travel_id').val('');
                $('#fill_manually').prop('checked', false);

                // Hide employee info card and clear follower info
                $('#employee_info_card').hide();
                clearFollowersFromNotes();
                clearEmployeeInfo();

                if (requestType === 'leave_based') {
                    loadLeaveRequests();
                    $('#source_document_group').show();
                    $('#source_document_label_text').text('Select Leave Request');
                    $('#manual_input_group').hide();
                } else if (requestType === 'travel_based') {
                    loadOfficialTravels();
                    $('#source_document_group').show();
                    $('#source_document_label_text').text('Select Official Travel');
                    $('#manual_input_group').hide();
                } else if (requestType === 'standalone') {
                    loadEmployees();
                    $('#source_document_group').show();
                    $('#source_document_label_text').text('Select Employee');
                    $('#manual_input_group').show();
                } else {
                    $('#source_document_group').hide();
                    $('#manual_input_group').hide();
                }
            });

            // Handle Manual Input Checkbox (for Standalone)
            $('#fill_manually').on('change', function() {
                const isChecked = $(this).is(':checked');

                if (isChecked) {
                    // Hide employee dropdown
                    $('#source_document_group').hide();
                    $('#source_document_id').val('').trigger('change');
                    $('#leave_request_id').val('');
                    $('#official_travel_id').val('');
                    $('#employee_id').val(''); // Clear employee_id for manual input

                    // Clear and show employee info card for manual input
                    clearEmployeeInfo();
                    $('#employee_info_card').show();
                    $('#flight_details_card').show();
                    clearFollowersFromNotes();
                } else {
                    // Show employee dropdown
                    $('#source_document_group').show();
                    $('#employee_info_card').hide();
                    clearEmployeeInfo();
                }
            });

            // Handle Source Document Selection
            $('#source_document_id').on('change', function() {
                const selectedOption = $(this).find('option:selected');
                const requestType = $('#request_type').val();
                const fillManually = $('#fill_manually').is(':checked');

                // Skip if manual input is checked
                if (fillManually) {
                    return;
                }

                if (selectedOption.val()) {
                    const employeeData = selectedOption.data('employee');
                    const followers = selectedOption.data('followers') || [];

                    if (employeeData) {
                        fillEmployeeInfo(employeeData);
                        $('#employee_info_card').show();

                        // Set hidden fields based on request type
                        if (requestType === 'leave_based') {
                            $('#leave_request_id').val(selectedOption.val());
                            $('#official_travel_id').val('');
                            // Clear follower info for leave request
                            clearFollowersFromNotes();
                        } else if (requestType === 'travel_based') {
                            $('#official_travel_id').val(selectedOption.val());
                            $('#leave_request_id').val('');
                            // Add follower info to notes if available
                            addFollowersToNotes(followers);
                        } else if (requestType === 'standalone') {
                            // For standalone, set employee_id
                            $('#employee_id').val(selectedOption.val());
                            // Clear follower info for standalone
                            clearFollowersFromNotes();
                        }
                    }
                } else {
                    clearEmployeeInfo();
                    $('#employee_info_card').hide();
                    clearFollowersFromNotes();
                }
            });

            // Load Leave Requests from API
            function loadLeaveRequests() {
                $('#source_document_id').html('<option value="">Loading...</option>').prop('disabled', true);

                $.ajax({
                    url: apiRoutes.leaveRequests,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        let options = '<option value="">-- Select Leave Request --</option>';
                        data.forEach(function(item) {
                            options +=
                                `<option value="${item.id}" data-employee='${JSON.stringify(item.employee_data)}'>${item.text}</option>`;
                        });
                        $('#source_document_id').html(options).prop('disabled', false);
                    },
                    error: function() {
                        $('#source_document_id').html('<option value="">Error loading data</option>')
                            .prop('disabled', false);
                    }
                });
            }

            // Load Official Travels from API
            function loadOfficialTravels() {
                $('#source_document_id').html('<option value="">Loading...</option>').prop('disabled', true);

                $.ajax({
                    url: apiRoutes.officialTravels,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        let options = '<option value="">-- Select Official Travel --</option>';
                        data.forEach(function(item) {
                            options +=
                                `<option value="${item.id}"
                                    data-employee='${JSON.stringify(item.employee_data)}'
                                    data-followers='${JSON.stringify(item.followers || [])}'>${item.text}</option>`;
                        });
                        $('#source_document_id').html(options).prop('disabled', false);
                    },
                    error: function() {
                        $('#source_document_id').html('<option value="">Error loading data</option>')
                            .prop('disabled', false);
                    }
                });
            }

            // Load Employees from API
            function loadEmployees() {
                $('#source_document_id').html('<option value="">Loading...</option>').prop('disabled', true);

                $.ajax({
                    url: apiRoutes.employees,
                    type: 'GET',
                    dataType: 'json',
                    success: function(data) {
                        let options = '<option value="">-- Select Employee --</option>';
                        data.forEach(function(item) {
                            options +=
                                `<option value="${item.id}" data-employee='${JSON.stringify(item.employee_data)}'>${item.text}</option>`;
                        });
                        $('#source_document_id').html(options).prop('disabled', false);
                    },
                    error: function() {
                        $('#source_document_id').html('<option value="">Error loading data</option>')
                            .prop('disabled', false);
                    }
                });
            }

            // Fill Employee Information
            function fillEmployeeInfo(data) {
                $('#employee_id').val(data.employee_id || '');
                $('#administration_id').val(data.administration_id || '');
                $('#employee_name').val(data.employee_name || '');
                $('#nik').val(data.nik || '');
                $('#position').val(data.position || '');
                $('#department').val(data.department || '');
                $('#poh_display').text(data.poh || '-');
                $('#doh_display').text(data.doh || '-');
                $('#project').val(data.project || '');
                $('#phone_number').val(data.phone_number || '');
                $('#purpose_of_travel').val(data.purpose_of_travel || '');
                $('#total_travel_days').val(data.total_travel_days || '');

                // Show flight details card
                $('#flight_details_card').show();
            }

            // Clear Employee Information
            function clearEmployeeInfo() {
                $('#employee_id').val('');
                $('#administration_id').val('');
                $('#employee_name').val('');
                $('#nik').val('');
                $('#position').val('');
                $('#department').val('');
                $('#poh_display').text('-');
                $('#doh_display').text('-');
                $('#project').val('');
                $('#phone_number').val('');
                $('#purpose_of_travel').val('');
                $('#total_travel_days').val('');

                // Hide flight details card and clear follower info from notes
                $('#flight_details_card').hide();
                clearFollowersFromNotes();
            }

            // Add Followers Information to Notes Textarea
            function addFollowersToNotes(followers) {
                // First, remove any existing follower info
                clearFollowersFromNotes();

                if (followers && followers.length > 0) {
                    let followerText = '\n\n--- Followers ---\n';
                    followers.forEach(function(follower) {
                        followerText += `- ${follower.name} (${follower.nik}) - ${follower.position}\n`;
                    });

                    const currentNotes = $('#notes').val();
                    // Add new follower info to the end
                    $('#notes').val((currentNotes.trim() + followerText).trim());
                }
            }

            // Clear Followers Information from Notes Textarea
            function clearFollowersFromNotes() {
                const currentNotes = $('#notes').val();
                if (!currentNotes) return;

                // Remove follower section: "--- Followers ---" and all lines after it until end
                // Handle both cases: with leading newlines and without
                let cleanedNotes = currentNotes.replace(/\n\n--- Followers ---[\s\S]*$/, '');
                cleanedNotes = cleanedNotes.replace(/\n--- Followers ---[\s\S]*$/, '');
                cleanedNotes = cleanedNotes.replace(/^--- Followers ---[\s\S]*$/, '');

                $('#notes').val(cleanedNotes.trim());
            }

            // Add initial flight detail
            addFlightDetail();

            // Add flight detail button
            $('#addFlightDetail').click(function() {
                addFlightDetail();
            });

            // Form submission validation
            $('#flightRequestForm').on('submit', function(e) {
                const requestType = $('#request_type').val();
                const sourceDocId = $('#source_document_id').val();
                const fillManually = $('#fill_manually').is(':checked');
                const employeeId = $('#employee_id').val();
                const employeeName = $('#employee_name').val();

                // Validate request type is selected
                if (!requestType) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please select a request type',
                        confirmButtonColor: '#3085d6'
                    });
                    return false;
                }

                // Validate source document is selected (skip if manual input is checked for standalone)
                if (!fillManually && !sourceDocId) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Please select a ' + (requestType === 'leave_based' ?
                            'leave request' : requestType === 'travel_based' ?
                            'official travel' : 'employee'),
                        confirmButtonColor: '#3085d6'
                    });
                    return false;
                }

                // Validate employee info is filled
                if (!employeeId && !employeeName) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'error',
                        title: 'Validation Error',
                        text: 'Employee information is required',
                        confirmButtonColor: '#3085d6'
                    });
                    return false;
                }
            });
        });

        function addFlightDetail() {
            const segmentType = detailIndex === 0 ? 'departure' : 'return';
            const html = `
                <div class="flight-detail-item border p-3 mb-3" data-index="${detailIndex}">
                    <div class="row">
                        <div class="col-md-12 mb-2">
                            <strong>Flight ${detailIndex + 1}</strong>
                            <button type="button" class="btn btn-sm btn-danger float-right remove-detail" data-index="${detailIndex}">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                        <input type="hidden" name="details[${detailIndex}][segment_type]" value="${segmentType}">
                        <!-- Row 1: Departure and Arrival Cities -->
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>
                                    <i class="fas fa-plane-departure mr-1"></i>
                                    Departure City <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="details[${detailIndex}][departure_city]" class="form-control" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>
                                    <i class="fas fa-plane-arrival mr-1"></i>
                                    Arrival City <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="details[${detailIndex}][arrival_city]" class="form-control" required>
                            </div>
                        </div>
                        <!-- Row 2: Flight Date, Flight Time, and Airline -->
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
                                    <i class="fas fa-clock mr-1"></i>
                                    Flight Time (ETD)
                                </label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-clock"></i></span>
                                    </div>
                                    <input type="time" name="details[${detailIndex}][flight_time]" class="form-control">
                                </div>
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
                    </div>
                </div>
            `;
            $('#flightDetailsContainer').append(html);
            detailIndex++;
        }

        // Remove flight detail
        $(document).on('click', '.remove-detail', function() {
            $(this).closest('.flight-detail-item').remove();
            // Re-number flight segments
            renumberFlightSegments();
        });

        // Re-number flight segments
        function renumberFlightSegments() {
            $('.flight-detail-item').each(function(index) {
                $(this).find('strong').text(`Flight ${index + 1}`);
                $(this).attr('data-index', index);
                // Update all input names with new index
                $(this).find('input, select, textarea').each(function() {
                    const name = $(this).attr('name');
                    if (name) {
                        const newName = name.replace(/details\[\d+\]/, `details[${index}]`);
                        $(this).attr('name', newName);
                    }
                });
                $(this).find('.remove-detail').attr('data-index', index);
            });
            // Update detailIndex to match current count
            detailIndex = $('.flight-detail-item').length;
        }
    </script>
@endsection
