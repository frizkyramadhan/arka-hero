@extends('layouts.main')

@section('title', $title ?? 'Create My Flight Request')

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
                        <li class="breadcrumb-item"><a href="{{ route('flight-requests.my-requests') }}">My Flight
                                Requests</a></li>
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
                <input type="hidden" name="from_my_requests" value="1">
                <div class="row">
                    <div class="col-md-8">
                        <!-- Request Type & Source Document (same as flight/requests/create, options filtered for current user) -->
                        <div class="card card-primary card-outline elevation-3 mb-3">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-list mr-2"></i>
                                    <strong>Request Information</strong>
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="request_type">Select Request Type <span class="text-danger">*</span></label>
                                    <select name="request_type" id="request_type"
                                        class="form-control select2bs4 @error('request_type') is-invalid @enderror"
                                        required>
                                        <option value="">-- Select Request Type --</option>
                                        <option value="leave_based"
                                            {{ old('request_type') == 'leave_based' ? 'selected' : '' }}>Leave Request
                                            (Cuti)</option>
                                        <option value="travel_based"
                                            {{ old('request_type') == 'travel_based' ? 'selected' : '' }}>Official Travel
                                            (LOT)</option>
                                        <option value="standalone"
                                            {{ old('request_type') == 'standalone' ? 'selected' : '' }}>Standalone</option>
                                    </select>
                                    @error('request_type')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group" id="source_document_group" style="display: none;">
                                    <label for="source_document_id" id="source_document_label">
                                        <span id="source_document_label_text">Select Document</span>
                                        <span class="text-danger" id="source_document_required">*</span>
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
                                            name="fill_manually" value="1" {{ old('fill_manually') ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="fill_manually">
                                            Fill employee information manually
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Employee Information Card (same editable table as create) -->
                        <div class="card card-primary card-outline elevation-3 mb-3" id="employee_info_card"
                            style="display: none;">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <i class="fas fa-user mr-2"></i>
                                    <strong>Employee Information</strong>
                                </h3>
                            </div>
                            <div class="card-body">
                                <input type="hidden" name="employee_id" id="employee_id"
                                    value="{{ old('employee_id', $employee->id ?? '') }}">
                                <input type="hidden" name="administration_id" id="administration_id"
                                    value="{{ old('administration_id', $administration->id ?? '') }}">
                                <table class="table table-sm table-bordered mb-0 employee-info-table">
                                    <tbody>
                                        <tr>
                                            <td class="font-weight-bold">NAME:</td>
                                            <td>
                                                <input type="text" name="employee_name" id="employee_name"
                                                    class="form-control form-control-sm border-0 p-0"
                                                    value="{{ old('employee_name', $employee->fullname ?? ($employee->name ?? '')) }}"
                                                    placeholder="Enter employee name">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">ID NUMBER / NIK:</td>
                                            <td>
                                                <input type="text" name="nik" id="nik"
                                                    class="form-control form-control-sm border-0 p-0"
                                                    value="{{ old('nik', $employee->nik ?? '') }}" placeholder="Enter NIK">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">POSITION:</td>
                                            <td>
                                                <input type="text" name="position" id="position"
                                                    class="form-control form-control-sm border-0 p-0"
                                                    value="{{ old('position', $administration && $administration->position ? $administration->position->position_name : '') }}"
                                                    placeholder="Enter position">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">DEPT/DIVISION:</td>
                                            <td>
                                                <input type="text" name="department" id="department"
                                                    class="form-control form-control-sm border-0 p-0"
                                                    value="{{ old('department', $administration && $administration->position && $administration->position->department ? $administration->position->department->department_name : '') }}"
                                                    placeholder="Enter department">
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
                                                    value="{{ old('project', $administration && $administration->project ? $administration->project->project_name ?? $administration->project->project_code : '') }}"
                                                    placeholder="Enter project">
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="font-weight-bold">PHONE NUMBER:</td>
                                            <td>
                                                <input type="text" name="phone_number" id="phone_number"
                                                    class="form-control form-control-sm border-0 p-0"
                                                    value="{{ old('phone_number', $employee->phone ?? ($employee->phone_number ?? '')) }}"
                                                    placeholder="Enter phone number">
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

                        <!-- Flight Details Card -->
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
                                <div id="flightDetailsContainer"></div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4">
                        <!-- Notes Card -->
                        <div class="card card-secondary card-outline elevation-3 mb-3">
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
                        {{-- <div class="card card-warning card-outline elevation-3 mb-3">
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
                        </div> --}}

                        <!-- Action Buttons -->
                        <div class="card elevation-3">
                            <div class="card-body">
                                <div class="row mb-2">
                                    <div class="col-md-12 mb-2 mb-md-0">
                                        <button type="submit" name="submit_action" value="draft"
                                            class="btn btn-primary btn-block">
                                            <i class="fas fa-save mr-2"></i> Create Flight Request
                                        </button>
                                    </div>
                                    {{-- <div class="col-md-6">
                                        <button type="submit" name="submit_action" value="submit"
                                            class="btn btn-success btn-block">
                                            <i class="fas fa-paper-plane mr-2"></i> Save & Submit
                                        </button>
                                    </div> --}}
                                </div>
                                <a href="{{ route('flight-requests.my-requests') }}" class="btn btn-secondary btn-block">
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
    <style>
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
            background-color: #f8f9fa;
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
    </style>
@endsection

@section('scripts')
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        // Data profil user (untuk Standalone — Kosongkan)
        const myProfileData = @json($myProfileData);

        const apiRoutes = {
            leaveRequests: '{{ route('flight-requests.my-requests.api.leave-requests') }}',
            officialTravels: '{{ route('flight-requests.my-requests.api.official-travels') }}',
        };

        let detailIndex = 0;

        $(document).ready(function() {
            $('.select2bs4').select2({
                theme: 'bootstrap4',
                width: '100%'
            });

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
                $('#flight_details_card').hide();
            }

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
                $('#employee_info_card').show();
                $('#flight_details_card').show();
            }

            $('#request_type').on('change', function() {
                const requestType = $(this).val();
                $('#source_document_id').val('').trigger('change');
                $('#leave_request_id').val('');
                $('#official_travel_id').val('');
                $('#employee_info_card').hide();
                $('#flight_details_card').hide();
                clearEmployeeInfo();

                if (requestType === 'leave_based') {
                    $('#source_document_group').show();
                    $('#source_document_required').show();
                    $('#source_document_label_text').text('Select Leave Request');
                    $('#source_document_id').html('<option value="">-- Select Leave Request --</option>');
                    $('#manual_input_group').hide();
                    $('#fill_manually').prop('checked', false);
                    loadMyLeaveRequests();
                } else if (requestType === 'travel_based') {
                    $('#source_document_group').show();
                    $('#source_document_required').show();
                    $('#source_document_label_text').text('Select Official Travel');
                    $('#manual_input_group').hide();
                    $('#fill_manually').prop('checked', false);
                    $('#source_document_id').html('<option value="">-- Select Official Travel --</option>');
                    loadMyOfficialTravels();
                } else if (requestType === 'standalone') {
                    $('#source_document_group').hide();
                    $('#manual_input_group').show();
                    if ($('#fill_manually').is(':checked')) {
                        $('#employee_id').val('');
                        $('#administration_id').val('');
                        $('#employee_info_card').show();
                        $('#flight_details_card').show();
                    } else {
                        fillEmployeeInfo(myProfileData);
                        $('#employee_info_card').show();
                        $('#flight_details_card').show();
                    }
                } else {
                    $('#source_document_group').hide();
                    $('#manual_input_group').hide();
                    $('#fill_manually').prop('checked', false);
                }
            });

            // Handle Manual Input Checkbox (for Standalone)
            $('#fill_manually').on('change', function() {
                const isChecked = $(this).is(':checked');
                if (isChecked) {
                    $('#source_document_id').val('');
                    $('#leave_request_id').val('');
                    $('#official_travel_id').val('');
                    $('#employee_id').val('');
                    $('#administration_id').val('');
                    clearEmployeeInfo();
                    $('#employee_info_card').show();
                    $('#flight_details_card').show();
                } else {
                    fillEmployeeInfo(myProfileData);
                    $('#employee_info_card').show();
                }
            });

            $('#source_document_id').on('change', function() {
                if ($('#fill_manually').is(':checked')) return;
                const selectedOption = $(this).find('option:selected');
                const requestType = $('#request_type').val();
                const rawEmployee = selectedOption.attr('data-employee');
                const rawFollowers = selectedOption.attr('data-followers');
                let employeeData = null;
                let followers = [];
                try {
                    if (rawEmployee) employeeData = typeof rawEmployee === 'string' ? JSON.parse(
                        rawEmployee) : rawEmployee;
                    if (rawFollowers) followers = typeof rawFollowers === 'string' ? JSON.parse(
                        rawFollowers) : rawFollowers;
                } catch (e) {}
                if (!Array.isArray(followers)) followers = [];

                if (selectedOption.val()) {
                    if (employeeData) {
                        fillEmployeeInfo(employeeData);
                        if (requestType === 'leave_based') {
                            $('#leave_request_id').val(selectedOption.val());
                            $('#official_travel_id').val('');
                        } else if (requestType === 'travel_based') {
                            $('#official_travel_id').val(selectedOption.val());
                            $('#leave_request_id').val('');
                            if (followers.length > 0) {
                                var t = '\n\n--- Followers ---\n';
                                followers.forEach(function(f) {
                                    t += '- ' + (f.name || '') + ' (' + (f.nik || '') + ') - ' + (f
                                        .position || '') + '\n';
                                });
                                $('#notes').val(($('#notes').val().trim() + t).trim());
                            }
                        }
                    }
                } else {
                    if (requestType === 'standalone') {
                        fillEmployeeInfo(myProfileData);
                    } else {
                        clearEmployeeInfo();
                        $('#employee_info_card').hide();
                    }
                    $('#leave_request_id').val('');
                    $('#official_travel_id').val('');
                }
            });

            function loadMyLeaveRequests() {
                $('#source_document_id').prop('disabled', true);
                $.get(apiRoutes.leaveRequests).done(function(data) {
                    var opts = '<option value="">-- Select Leave Request --</option>';
                    (data || []).forEach(function(item) {
                        var emp = JSON.stringify(item.employee_data || {}).replace(/"/g, '&quot;');
                        opts += '<option value="' + item.id + '" data-employee="' + emp + '">' + (
                            item.text || '') + '</option>';
                    });
                    $('#source_document_id').html(opts).prop('disabled', false);
                }).fail(function() {
                    $('#source_document_id').html('<option value="">Error loading data</option>').prop(
                        'disabled', false);
                });
            }

            function loadMyOfficialTravels() {
                $('#source_document_id').prop('disabled', true);
                $.get(apiRoutes.officialTravels).done(function(data) {
                    var opts = '<option value="">-- Select Official Travel --</option>';
                    (data || []).forEach(function(item) {
                        var emp = JSON.stringify(item.employee_data || {}).replace(/"/g, '&quot;');
                        var fol = JSON.stringify(item.followers || []).replace(/"/g, '&quot;');
                        opts += '<option value="' + item.id + '" data-employee="' + emp +
                            '" data-followers="' + fol + '">' + (item.text || '') + '</option>';
                    });
                    $('#source_document_id').html(opts).prop('disabled', false);
                }).fail(function() {
                    $('#source_document_id').html('<option value="">Error loading data</option>').prop(
                        'disabled', false);
                });
            }

            // Init by old value or default standalone
            var oldType = '{{ old('request_type', 'standalone') }}';
            var oldLeaveId = '{{ old('leave_request_id') }}';
            var oldTravelId = '{{ old('official_travel_id') }}';
            if (oldType) {
                $('#request_type').val(oldType).trigger('change');
                setTimeout(function() {
                    if (oldType === 'leave_based' && oldLeaveId) $('#source_document_id').val(oldLeaveId)
                        .trigger('change');
                    else if (oldType === 'travel_based' && oldTravelId) $('#source_document_id').val(
                        oldTravelId).trigger('change');
                }, 600);
            }

            addFlightDetail();
            $('#addFlightDetail').click(function() {
                addFlightDetail();
            });
        });

        function addFlightDetail() {
            const segmentType = detailIndex === 0 ? 'departure' : 'return';
            const html = '<div class="flight-detail-item border p-3 mb-3" data-index="' + detailIndex + '">' +
                '<div class="row"><div class="col-md-12 mb-2"><strong>Flight ' + (detailIndex + 1) + '</strong>' +
                '<button type="button" class="btn btn-sm btn-danger float-right remove-detail"><i class="fas fa-times"></i></button></div>' +
                '<input type="hidden" name="details[' + detailIndex + '][segment_type]" value="' + segmentType + '">' +
                '<div class="col-md-6"><div class="form-group"><label><i class="fas fa-plane-departure mr-1"></i> Departure City <span class="text-danger">*</span></label>' +
                '<input type="text" name="details[' + detailIndex +
                '][departure_city]" class="form-control" required></div></div>' +
                '<div class="col-md-6"><div class="form-group"><label><i class="fas fa-plane-arrival mr-1"></i> Arrival City <span class="text-danger">*</span></label>' +
                '<input type="text" name="details[' + detailIndex +
                '][arrival_city]" class="form-control" required></div></div>' +
                '<div class="col-md-4"><div class="form-group"><label><i class="fas fa-calendar-alt mr-1"></i> Flight Date <span class="text-danger">*</span></label>' +
                '<input type="date" name="details[' + detailIndex +
                '][flight_date]" class="form-control" required></div></div>' +
                '<div class="col-md-4"><div class="form-group"><label><i class="fas fa-clock mr-1"></i> Flight Time (ETD)</label>' +
                '<input type="time" name="details[' + detailIndex + '][flight_time]" class="form-control"></div></div>' +
                '<div class="col-md-4"><div class="form-group"><label><i class="fas fa-plane mr-1"></i> Airline</label>' +
                '<input type="text" name="details[' + detailIndex +
                '][airline]" class="form-control" placeholder="Preferred airline"></div></div>' +
                '</div></div>';
            $('#flightDetailsContainer').append(html);
            detailIndex++;
        }

        $(document).on('click', '.remove-detail', function() {
            $(this).closest('.flight-detail-item').remove();
            renumberFlightSegments();
        });

        function renumberFlightSegments() {
            $('.flight-detail-item').each(function(index) {
                $(this).attr('data-index', index);
                $(this).find('strong').text('Flight ' + (index + 1));
                var segType = index === 0 ? 'departure' : 'return';
                $(this).find('input[type="hidden"]').val(segType).attr('name', 'details[' + index +
                    '][segment_type]');
                $(this).find('input[type="text"], input[type="date"], input[type="time"]').each(function() {
                    var n = $(this).attr('name');
                    if (n) $(this).attr('name', n.replace(/details\[\d+\]/, 'details[' + index + ']'));
                });
            });
            detailIndex = $('.flight-detail-item').length;
        }
    </script>
@endsection
