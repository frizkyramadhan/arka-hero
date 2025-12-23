@extends('layouts.main')

@section('title', $title)

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $title }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('rosters.index') }}">Roster Management</a></li>
                        <li class="breadcrumb-item active">Details</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Employee Information Card -->
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user mr-2"></i>Employee Information</h3>
                    <div class="card-tools">
                        <a href="{{ route('rosters.index', ['project_id' => $roster->administration->project_id]) }}"
                            class="btn btn-sm btn-secondary">
                            <i class="fas fa-arrow-left mr-1"></i> Back
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="150" class="font-weight-bold">NIK</td>
                                    <td>: {{ $roster->administration->nik }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Full Name</td>
                                    <td>: {{ $roster->employee->fullname ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Position</td>
                                    <td>: {{ $roster->administration->position->position_name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Department</td>
                                    <td>: {{ $roster->administration->position->department->department_name ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td width="150" class="font-weight-bold">Project</td>
                                    <td>: {{ $roster->administration->project->project_code ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Level</td>
                                    <td>: <span
                                            class="badge badge-info">{{ $roster->administration->level->name ?? 'N/A' }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Roster Cycle</td>
                                    <td>: <span class="badge badge-success">{{ $roster->getRosterPatternDisplay() }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">FB Cycle Ratio</td>
                                    <td>: <span
                                            class="badge badge-primary">{{ round($roster->getFbCycleRatio(), 2) }}</span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Card -->
            <div class="row">
                <div class="col-md-3 col-sm-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-success"><i class="fas fa-calendar-check"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Accumulated Leave</span>
                            <span class="info-box-number">{{ round($roster->getTotalAccumulatedLeave()) }} days</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-warning"><i class="fas fa-umbrella-beach"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Leave Taken</span>
                            <span class="info-box-number">{{ round($roster->getTotalLeaveTaken()) }} days</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-info"><i class="fas fa-piggy-bank"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Leave Balance</span>
                            <span class="info-box-number">{{ round($roster->getLeaveBalance()) }} days</span>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6">
                    <div class="info-box">
                        <span class="info-box-icon bg-primary"><i class="fas fa-calculator"></i></span>
                        <div class="info-box-content">
                            <span class="info-box-text">Work Days Difference</span>
                            <span class="info-box-number">
                                @php
                                    $accumulatedLeave = $roster->getTotalAccumulatedLeave();
                                    $leaveTaken = $roster->getTotalLeaveTaken();
                                    $fbRatio = $roster->getFbCycleRatio();
                                    $workDaysDiff = $fbRatio > 0 ? ($accumulatedLeave - $leaveTaken) / $fbRatio : 0;
                                @endphp
                                {{ round($workDaysDiff) }} days
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cycle Management Card -->
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-list mr-2"></i>Cycle Management</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-sm btn-primary" id="btnAddCycle">
                            <i class="fas fa-plus mr-1"></i> Add Cycle
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    @if ($roster->rosterDetails->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered table-sm">
                                <thead class="thead-light">
                                    <tr>
                                        <th width="5%" class="text-center">Cycle</th>
                                        <th width="15%">Work Period</th>
                                        <th width="10%" class="text-center">Work Days</th>
                                        <th width="10%" class="text-center">Adjusted</th>
                                        <th width="15%">Leave Period</th>
                                        <th width="10%" class="text-center">Leave Days</th>
                                        <th width="10%" class="text-center">Entitlement</th>
                                        <th width="10%" class="text-center">Status</th>
                                        <th width="15%" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($roster->rosterDetails as $detail)
                                        <tr>
                                            <td class="text-center">
                                                <strong>#{{ $detail->cycle_no }}</strong>
                                            </td>
                                            <td>
                                                <small class="text-nowrap">
                                                    <strong>{{ $detail->work_start->format('d/m/Y') }}</strong>
                                                    <span class="text-muted">to</span>
                                                    <strong>{{ $detail->work_end->format('d/m/Y') }}</strong>
                                                </small>
                                            </td>
                                            <td class="text-center">
                                                <span
                                                    class="badge badge-primary">{{ $detail->getActualWorkDays() }}</span>
                                            </td>
                                            <td class="text-center">
                                                @if ($detail->adjusted_days != 0)
                                                    <span
                                                        class="badge badge-{{ $detail->adjusted_days > 0 ? 'success' : 'danger' }}">
                                                        {{ $detail->adjusted_days > 0 ? '+' : '' }}{{ $detail->adjusted_days }}
                                                    </span>
                                                @else
                                                    <span class="badge badge-secondary">0</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($detail->leave_start && $detail->leave_end)
                                                    <small class="text-nowrap">
                                                        <strong>{{ $detail->leave_start->format('d/m/Y') }}</strong>
                                                        <span class="text-muted">to</span>
                                                        <strong>{{ $detail->leave_end->format('d/m/Y') }}</strong>
                                                    </small>
                                                @else
                                                    <span class="badge badge-secondary">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if ($detail->leave_start && $detail->leave_end)
                                                    <span class="badge badge-warning">{{ $detail->getLeaveDays() }}</span>
                                                @else
                                                    <span class="badge badge-secondary">-</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                <span
                                                    class="badge badge-info">{{ number_format($detail->getLeaveEntitlement(), 2) }}</span>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge {{ $detail->getStatusBadgeClass() }}">
                                                    {{ $detail->getStatusLabel() }}
                                                </span>
                                            </td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-info btn-sm btn-view-cycle mr-1"
                                                    data-cycle-id="{{ $detail->id }}" title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                                <button type="button" class="btn btn-warning btn-sm btn-edit-cycle mr-1"
                                                    data-cycle-id="{{ $detail->id }}" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button type="button" class="btn btn-danger btn-sm btn-delete-cycle"
                                                    data-cycle-id="{{ $detail->id }}" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @if ($detail->remarks)
                                            <tr class="bg-light">
                                                <td></td>
                                                <td colspan="8" class="py-1">
                                                    <small><strong>Remarks:</strong> {{ $detail->remarks }}</small>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle mr-2"></i>
                            No cycles found. Click <strong>"Add Cycle Manually"</strong> to add a new cycle.
                        </div>
                    @endif
                </div>
            </div>

            <!-- Note Section -->
            <div class="card card-outline card-secondary">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-info-circle mr-2"></i>Note</h3>
                </div>
                <div class="card-body">
                    <ol class="mb-0">
                        <li>Cuti dimundurkan dari jadwal cuti sebenarnya karena kebutuhan perusahaan, berhak mendapatkan
                            kompensasi pada cuti berikutnya.</li>
                        <li>Cuti dimundurkan dari jadwal cuti sebenarnya karena urusan pribadi, tetap bekerja sesuai dengan
                            jumlah hari kerja pada jabatan masing-masing.</li>
                        <li>Cuti maju karena keperluan perusahaan, pada hari kerja berikutnya tetap bekerja sesuai dengan
                            jumlah hari kerja pada jabatan masing-masing.</li>
                        <li>Cuti maju karena keperluan pribadi, maka kekurangan hari kerja akan ditambahkan pada hari kerja
                            berikutnya.</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Add/Edit Cycle -->
    <div class="modal fade" id="modalCycle" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-primary">
                    <h5 class="modal-title" id="modalCycleTitle">Add Cycle</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form id="formCycle">
                    <input type="hidden" id="cycle_id" name="cycle_id">
                    <input type="hidden" id="form_method" value="POST">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card card-outline card-info">
                                    <div class="card-header">
                                        <h3 class="card-title">Work Period</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="work_start">Start Date <span class="text-danger">*</span></label>
                                            <input type="date" class="form-control" id="work_start" name="work_start"
                                                required>
                                            <small class="form-text text-muted">
                                                <i class="fas fa-info-circle"></i> Auto-set from last cycle if exists
                                            </small>
                                        </div>
                                        <div class="form-group">
                                            <label for="work_end">
                                                End Date <span class="text-danger">*</span>
                                                <i class="fas fa-calculator text-info" title="Auto-calculated"></i>
                                            </label>
                                            <input type="date" class="form-control bg-light" id="work_end"
                                                name="work_end" required readonly>
                                            <small class="form-text text-muted">
                                                <i class="fas fa-magic"></i> Auto: work_start +
                                                {{ $roster->administration->level->work_days ?? 63 }} days + adjusted_days
                                            </small>
                                        </div>
                                        <div class="form-group">
                                            <label for="adjusted_days">
                                                Adjusted Days
                                            </label>
                                            <input type="number" class="form-control" id="adjusted_days"
                                                name="adjusted_days" value="0">
                                            <small class="form-text text-muted">
                                                <i class="fas fa-balance-scale"></i> Positive (+) to add days, Negative (-)
                                                to reduce days
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card card-outline card-warning">
                                    <div class="card-header">
                                        <h3 class="card-title">Leave Period</h3>
                                    </div>
                                    <div class="card-body">
                                        <div class="form-group">
                                            <label for="leave_start">
                                                Start Date
                                                <i class="fas fa-calculator text-info" title="Auto-calculated"></i>
                                            </label>
                                            <input type="date" class="form-control bg-light" id="leave_start"
                                                name="leave_start" readonly>
                                            <small class="form-text text-muted">
                                                <i class="fas fa-magic"></i> Auto: work_end + 1 day
                                            </small>
                                        </div>
                                        <div class="form-group">
                                            <label for="leave_end">
                                                End Date
                                                <i class="fas fa-calculator text-info" title="Auto-calculated"></i>
                                            </label>
                                            <input type="date" class="form-control bg-light" id="leave_end"
                                                name="leave_end" readonly>
                                            <small class="form-text text-muted">
                                                <i class="fas fa-magic"></i> Auto: leave_start + 15 days
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="remarks">Remarks</label>
                            <textarea class="form-control" id="remarks" name="remarks" rows="3" placeholder="Optional notes..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save mr-1"></i> Save
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal View Cycle Details -->
    <div class="modal fade" id="modalViewCycle" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-info">
                    <h5 class="modal-title">Cycle Details</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="cycleDetailsContent">
                    <!-- Will be filled dynamically -->
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    <script>
        const ROSTER_ID = '{{ $roster->id }}';
        const WORK_DAYS = {{ $roster->administration->level->work_days ?? 63 }};
        const OFF_DAYS = {{ $roster->administration->level->getOffDays() ?? 14 }};
        const HAS_CYCLES = {{ $roster->rosterDetails->count() }};
        const LAST_LEAVE_END =
            @if ($roster->rosterDetails->count() > 0)
                '{{ $roster->rosterDetails->sortByDesc('cycle_no')->first()->leave_end->format('Y-m-d') }}'
            @else
                null
            @endif ;

        $(document).ready(function() {

            // Auto-calculation function
            function calculateCycleDates() {
                const workStart = $('#work_start').val();
                const adjustedDays = parseInt($('#adjusted_days').val()) || 0;

                if (!workStart) return;

                // Calculate work_end = work_start + work_days + adjusted_days
                const workStartDate = new Date(workStart);
                const workEndDate = new Date(workStartDate);
                workEndDate.setDate(workEndDate.getDate() + WORK_DAYS + adjustedDays);

                // Calculate leave_start = work_end + 1
                const leaveStartDate = new Date(workEndDate);
                leaveStartDate.setDate(leaveStartDate.getDate() + 1);

                // Calculate leave_end = leave_start + 15 days - 1
                const leaveEndDate = new Date(leaveStartDate);
                leaveEndDate.setDate(leaveEndDate.getDate() + 15 - 1);

                // Format dates to YYYY-MM-DD
                $('#work_end').val(formatDate(workEndDate));
                $('#leave_start').val(formatDate(leaveStartDate));
                $('#leave_end').val(formatDate(leaveEndDate));
            }

            // Helper function to format date to YYYY-MM-DD
            function formatDate(date) {
                const year = date.getFullYear();
                const month = String(date.getMonth() + 1).padStart(2, '0');
                const day = String(date.getDate()).padStart(2, '0');
                return `${year}-${month}-${day}`;
            }

            // Event listeners for auto-calculation
            $('#work_start').on('change', function() {
                calculateCycleDates();
            });

            $('#adjusted_days').on('input change', function() {
                calculateCycleDates();
            });

            // Add Cycle Manually
            $('#btnAddCycle').click(function() {
                $('#modalCycleTitle').text('Add Cycle Manually');
                $('#formCycle')[0].reset();
                $('#cycle_id').val('');
                $('#form_method').val('POST');

                // Set field states based on whether cycles exist
                if (HAS_CYCLES > 0 && LAST_LEAVE_END) {
                    // If cycles exist, auto-set work_start from last leave_end + 1
                    // But keep it editable so user can adjust if needed
                    const lastLeaveEnd = new Date(LAST_LEAVE_END);
                    lastLeaveEnd.setDate(lastLeaveEnd.getDate() + 1);
                    $('#work_start').val(formatDate(lastLeaveEnd)).prop('readonly', false).removeClass(
                        'bg-light');

                    // Auto-calculate other fields
                    calculateCycleDates();
                } else {
                    // If no cycles, work_start is manual
                    $('#work_start').prop('readonly', false).val('').removeClass('bg-light');
                }

                // Make calculated fields readonly
                $('#work_end, #leave_start, #leave_end').prop('readonly', true);
                $('#adjusted_days').val(0);

                // Add visual indicator for auto-calculated fields
                $('#work_end, #leave_start, #leave_end').addClass('bg-light');

                $('#modalCycle').modal('show');
            });

            // Submit Cycle Form
            $('#formCycle').submit(function(e) {
                e.preventDefault();

                const cycleId = $('#cycle_id').val();
                const method = $('#form_method').val();
                const url = method === 'POST' ?
                    `{{ url('rosters') }}/${ROSTER_ID}/cycles` :
                    `{{ url('rosters/cycles') }}/${cycleId}`;

                const formData = {
                    _token: '{{ csrf_token() }}',
                    work_start: $('#work_start').val(),
                    work_end: $('#work_end').val(),
                    adjusted_days: $('#adjusted_days').val() || 0,
                    leave_start: $('#leave_start').val() || null,
                    leave_end: $('#leave_end').val() || null,
                    remarks: $('#remarks').val()
                };

                if (method === 'PUT') {
                    formData._method = 'PUT';
                }

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            toast_success(response.message);
                            $('#modalCycle').modal('hide');
                            setTimeout(() => {
                                window.location.reload();
                            }, 1000);
                        }
                    },
                    error: function(xhr) {
                        const message = xhr.responseJSON?.message || 'Failed to save cycle';
                        toast_error(message);
                    }
                });
            });

            // View Cycle Details
            $(document).on('click', '.btn-view-cycle', function() {
                const cycleId = $(this).data('cycle-id');
                const $btn = $(this);
                const $row = $btn.closest('tr');

                // Show loading
                $('#cycleDetailsContent').html(
                    '<div class="text-center"><i class="fas fa-spinner fa-spin fa-2x"></i><p>Loading...</p></div>'
                );
                $('#modalViewCycle').modal('show');

                // Fetch cycle details from row data (since we have all data in the table)
                const cycleNo = $row.find('td:eq(0)').text().trim();
                const workPeriod = $row.find('td:eq(1)').text().trim();
                const workDays = $row.find('td:eq(2)').text().trim();
                const adjusted = $row.find('td:eq(3)').text().trim();
                const leavePeriod = $row.find('td:eq(4)').text().trim();
                const leaveDays = $row.find('td:eq(5)').text().trim();
                const entitlement = $row.find('td:eq(6)').text().trim();
                const status = $row.find('td:eq(7)').text().trim();

                let html = '<div class="row">';
                html += '<div class="col-md-12"><h5>Cycle #' + cycleNo + ' Details</h5><hr></div>';
                html += '</div>';
                html += '<div class="row">';
                html += '<div class="col-md-6">';
                html += '<table class="table table-sm table-borderless">';
                html += '<tr><td class="font-weight-bold" width="40%">Cycle Number:</td><td>' + cycleNo +
                    '</td></tr>';
                html += '<tr><td class="font-weight-bold">Work Period:</td><td>' + workPeriod +
                    '</td></tr>';
                html += '<tr><td class="font-weight-bold">Work Days:</td><td>' + workDays + '</td></tr>';
                html += '<tr><td class="font-weight-bold">Adjusted Days:</td><td>' + adjusted +
                    '</td></tr>';
                html += '</table>';
                html += '</div>';
                html += '<div class="col-md-6">';
                html += '<table class="table table-sm table-borderless">';
                html += '<tr><td class="font-weight-bold" width="40%">Leave Period:</td><td>' + (leavePeriod
                    .includes('Not Set') ? '<span class="badge badge-secondary">Not Set</span>' :
                    leavePeriod) + '</td></tr>';
                html += '<tr><td class="font-weight-bold">Leave Days:</td><td>' + (leaveDays === '-' ?
                    '<span class="badge badge-secondary">-</span>' : leaveDays) + '</td></tr>';
                html += '<tr><td class="font-weight-bold">Leave Entitlement:</td><td>' + entitlement +
                    '</td></tr>';
                html += '<tr><td class="font-weight-bold">Status:</td><td>' + status + '</td></tr>';
                html += '</table>';
                html += '</div>';
                html += '</div>';

                $('#cycleDetailsContent').html(html);
            });

            // Edit Cycle
            $(document).on('click', '.btn-edit-cycle', function() {
                const cycleId = $(this).data('cycle-id');

                // Show loading state
                const $btn = $(this);
                const originalHtml = $btn.html();
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                // Fetch cycle data from server
                $.ajax({
                    url: `{{ url('rosters/cycles') }}/${cycleId}`,
                    type: 'GET',
                    success: function(response) {
                        if (response.success) {
                            const data = response.data;

                            // Set form title
                            $('#modalCycleTitle').text('Edit Cycle');
                            $('#cycle_id').val(data.id);
                            $('#form_method').val('PUT');

                            // Populate form fields
                            $('#work_start').val(data.work_start);
                            $('#work_end').val(data.work_end);
                            $('#adjusted_days').val(data.adjusted_days || 0);
                            $('#leave_start').val(data.leave_start || '');
                            $('#leave_end').val(data.leave_end || '');
                            $('#remarks').val(data.remarks || '');

                            // For edit mode, enable work_start and adjusted_days for editing
                            // Calculated fields (work_end, leave_start, leave_end) remain readonly
                            $('#work_start').prop('readonly', false).removeClass('bg-light');
                            $('#adjusted_days').prop('readonly', false).removeClass('bg-light');
                            $('#work_end, #leave_start, #leave_end').prop('readonly', true)
                                .addClass('bg-light');

                            // Trigger calculation to ensure dates are correct
                            calculateCycleDates();

                            // Show modal
                            $('#modalCycle').modal('show');
                        } else {
                            toast_error(response.message || 'Failed to load cycle data');
                        }
                    },
                    error: function(xhr) {
                        const message = xhr.responseJSON?.message ||
                            'Failed to load cycle data';
                        toast_error(message);
                    },
                    complete: function() {
                        // Restore button state
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                });
            });


            // Delete Cycle
            $(document).on('click', '.btn-delete-cycle', function() {
                const cycleId = $(this).data('cycle-id');

                Swal.fire({
                    title: 'Delete Cycle',
                    text: 'Are you sure you want to delete this cycle?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Delete',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: `{{ url('rosters/cycles') }}/${cycleId}`,
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                _method: 'DELETE'
                            },
                            success: function(response) {
                                if (response.success) {
                                    toast_success(response.message);
                                    setTimeout(() => {
                                        window.location.reload();
                                    }, 1000);
                                }
                            },
                            error: function(xhr) {
                                const message = xhr.responseJSON?.message ||
                                    'Failed to delete cycle';
                                toast_error(message);
                            }
                        });
                    }
                });
            });
        });
    </script>

    <style>
        /* Compact badge styling */
        .table-sm .badge {
            font-size: 0.75rem;
            padding: 0.25rem 0.5rem;
        }

        /* Compact table cell padding */
        .table-sm td {
            padding: 0.5rem 0.5rem;
            vertical-align: middle;
        }

        .table-sm th {
            padding: 0.5rem 0.5rem;
            font-size: 0.875rem;
        }
    </style>
@endsection
