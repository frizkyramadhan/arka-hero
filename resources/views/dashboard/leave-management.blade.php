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
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">{{ $subtitle }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Statistics Cards -->
            <div class="row mb-3">
                <!-- Total Leave Requests -->
                <div class="col-lg-3 col-md-6 mb-2">
                    <div class="info-box bg-gradient-primary" style="padding: 8px;">
                        <span class="info-box-icon" style="width: 50px; height: 50px; line-height: 50px;"><i
                                class="fas fa-calendar-alt"></i></span>
                        <div class="info-box-content" style="padding-left: 8px;">
                            <span class="info-box-text" style="font-size: 0.9rem;">Total Requests</span>
                            <span class="info-box-number"
                                style="font-size: 1.4rem;">{{ number_format($totalLeaveRequests) }}</span>
                            <div class="progress" style="height: 3px; margin: 4px 0;">
                                <div class="progress-bar" style="width: 100%"></div>
                            </div>
                            <span class="progress-description" style="font-size: 0.75rem;">
                                All time requests
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Approved Requests -->
                <div class="col-lg-3 col-md-6 mb-2">
                    <div class="info-box bg-gradient-success" style="padding: 8px;">
                        <span class="info-box-icon" style="width: 50px; height: 50px; line-height: 50px;"><i
                                class="fas fa-check-circle"></i></span>
                        <div class="info-box-content" style="padding-left: 8px;">
                            <span class="info-box-text" style="font-size: 0.9rem;">Approved</span>
                            <span class="info-box-number"
                                style="font-size: 1.4rem;">{{ number_format($approvedRequests) }}</span>
                            <div class="progress" style="height: 3px; margin: 4px 0;">
                                <div class="progress-bar"
                                    style="width: {{ $totalLeaveRequests > 0 ? ($approvedRequests / $totalLeaveRequests) * 100 : 0 }}%">
                                </div>
                            </div>
                            <span class="progress-description" style="font-size: 0.75rem;">
                                {{ $totalLeaveRequests > 0 ? round(($approvedRequests / $totalLeaveRequests) * 100, 1) : 0 }}%
                                rate
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Pending Requests -->
                <div class="col-lg-3 col-md-6 mb-2">
                    <div class="info-box bg-gradient-warning" style="padding: 8px;">
                        <span class="info-box-icon" style="width: 50px; height: 50px; line-height: 50px;"><i
                                class="fas fa-clock"></i></span>
                        <div class="info-box-content" style="padding-left: 8px;">
                            <span class="info-box-text" style="font-size: 0.9rem;">Pending</span>
                            <span class="info-box-number"
                                style="font-size: 1.4rem;">{{ number_format($pendingRequests) }}</span>
                            <div class="progress" style="height: 3px; margin: 4px 0;">
                                <div class="progress-bar"
                                    style="width: {{ $totalLeaveRequests > 0 ? ($pendingRequests / $totalLeaveRequests) * 100 : 0 }}%">
                                </div>
                            </div>
                            <span class="progress-description" style="font-size: 0.75rem;">
                                Awaiting approval
                            </span>
                        </div>
                    </div>
                </div>

                <!-- This Month -->
                <div class="col-lg-3 col-md-6 mb-2">
                    <div class="info-box bg-gradient-info" style="padding: 8px;">
                        <span class="info-box-icon" style="width: 50px; height: 50px; line-height: 50px;"><i
                                class="fas fa-chart-line"></i></span>
                        <div class="info-box-content" style="padding-left: 8px;">
                            <span class="info-box-text" style="font-size: 0.9rem;">This Month</span>
                            <span class="info-box-number"
                                style="font-size: 1.4rem;">{{ number_format($thisMonthRequests) }}</span>
                            <div class="progress" style="height: 3px; margin: 4px 0;">
                                <div class="progress-bar"
                                    style="width: {{ $thisMonthRequests > 0 ? ($thisMonthRequests / $totalLeaveRequests) * 100 : 0 }}%">
                                </div>
                            </div>
                            <span class="progress-description" style="font-size: 0.75rem;">
                                @if ($monthlyGrowth != 0)
                                    {{ $monthlyGrowth > 0 ? '+' : '' }}{{ $monthlyGrowth }}% vs last month
                                @else
                                    {{ date('M Y') }}
                                @endif
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Search Entitlement -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card card-outline card-info">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-search mr-2"></i>Quick Search Employee Entitlement
                            </h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <select id="employeeSearch" class="form-control select2bs4" style="width: 100%;">
                                        <option></option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <button type="button" class="btn btn-primary" onclick="searchEmployee()">
                                        <i class="fas fa-search"></i> Search
                                    </button>
                                </div>
                            </div>
                            <div id="employeeEntitlementResult" class="mt-3" style="display: none;">
                                <!-- Results will be populated here -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Dashboard Content -->
            <div class="row">
                <!-- Open Leave Requests -->
                <div class="col-lg-6 mb-4">
                    <div class="card card-outline card-primary">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-calendar-check mr-2"></i>Open Leave Requests (Ready to Close)
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-sm" id="openLeaveRequestsTable"
                                    width="100%" cellspacing="0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="py-1 px-2">Employee</th>
                                            <th class="py-1 px-2">Leave Type</th>
                                            <th class="py-1 px-2">Period</th>
                                            <th class="py-1 px-2">Days</th>
                                            <th class="py-1 px-2" style="width: 15%; text-align: center;">Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cancellation Requests -->
                <div class="col-lg-6 mb-4">
                    <div class="card card-outline card-warning">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-times-circle mr-2"></i>Pending Cancellation Requests
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-sm" id="pendingCancellationsTable"
                                    width="100%" cellspacing="0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="py-1 px-2">Employee</th>
                                            <th class="py-1 px-2">Leave Type</th>
                                            <th class="py-1 px-2">Days to Cancel</th>
                                            <th class="py-1 px-2">Reason</th>
                                            <th class="py-1 px-2" style="text-align: center">Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Employee Entitlement Management -->
            <div class="row">
                <!-- Employees Without Entitlements -->
                <div class="col-lg-6 mb-4">
                    <div class="card card-outline card-warning">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-exclamation-triangle mr-2"></i>Employees Without Entitlements
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-sm"
                                    id="employeesWithoutEntitlementsTable" width="100%" cellspacing="0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="py-1 px-2">Employee</th>
                                            <th class="py-1 px-2">NIK</th>
                                            <th class="py-1 px-2">DOH</th>
                                            <th class="py-1 px-2">Position</th>
                                            <th class="py-1 px-2">Department</th>
                                            <th class="py-1 px-2">Project</th>
                                            <th class="py-1 px-2" style="text-align: center">Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Employees with Expiring Entitlements -->
                <div class="col-lg-6 mb-4">
                    <div class="card card-outline card-danger">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-clock mr-2"></i>Employees with Expiring Entitlements
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-sm"
                                    id="employeesWithExpiringEntitlementsTable" width="100%" cellspacing="0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="py-1 px-2">Employee</th>
                                            <th class="py-1 px-2">NIK</th>
                                            <th class="py-1 px-2">Expires</th>
                                            <th class="py-1 px-2" style="text-align: center">Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Paid Leave Without Documents -->
                <div class="col-lg-12 mb-4">
                    <div class="card card-outline card-danger">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-file-upload mr-2"></i>Paid Leave Without Supporting Documents
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-sm" id="paidLeaveWithoutDocsTable"
                                    width="100%" cellspacing="0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th class="py-1 px-2">Employee</th>
                                            <th class="py-1 px-2">Leave Type</th>
                                            <th class="py-1 px-2">Period</th>
                                            <th class="py-1 px-2">Days</th>
                                            <th class="py-1 px-2">Days Remaining</th>
                                            <th class="py-1 px-2">Status</th>
                                            <th class="py-1 px-2" style="text-align: center">Action</th>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Modals -->
    <!-- Close Leave Request Modal -->
    <div class="modal fade" id="closeLeaveRequestModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Close Leave Request</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>Are you sure you want to close this leave request?</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="confirmCloseLeaveRequest">Close Request</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Cancellation Action Modal -->
    <div class="modal fade" id="cancellationActionModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cancellationModalTitle">Action on Cancellation Request</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label for="cancellationNotes">Notes:</label>
                        <textarea class="form-control" id="cancellationNotes" rows="3" placeholder="Enter your notes here..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-success" id="confirmCancellationAction">Confirm</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <style>
        /* Compact table styling */
        .table-sm th,
        .table-sm td {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
            line-height: 1.25;
        }

        .table-sm .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
            line-height: 1.2;
        }

        .table-sm .badge {
            font-size: 0.7rem;
            padding: 0.25rem 0.4rem;
        }

        /* Compact card styling */
        .card-body {
            padding: 0.75rem;
        }

        .card-header {
            padding: 0.5rem 0.75rem;
        }

        /* Compact info box styling */
        .info-box {
            margin-bottom: 0.5rem;
        }

        .info-box .info-box-content {
            padding: 0.5rem;
        }

        .info-box .info-box-text {
            font-size: 0.875rem;
            font-weight: 600;
        }

        .info-box .info-box-number {
            font-size: 1.5rem;
            font-weight: 700;
        }

        /* Compact form styling */
        .form-group {
            margin-bottom: 0.75rem;
        }

        .form-control-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.75rem;
        }

        /* Extra small button styling */
        .btn-xs {
            padding: 0.125rem 0.25rem;
            font-size: 0.65rem;
            line-height: 1.2;
            border-radius: 0.2rem;
        }

        .btn-xs i {
            font-size: 0.7rem;
        }
    </style>
@endpush

@push('scripts')
    <script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/jszip/jszip.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/pdfmake/pdfmake.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/pdfmake/vfs_fonts.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Initialize Select2 Elements with custom themes
            $('.select2bs4').select2({
                theme: 'bootstrap4',
                placeholder: 'Select an option'
            }).on('select2:open', function() {
                document.querySelector('.select2-search__field').focus();
            });

            // Initialize Select2 for employee search with Bootstrap 4 theme
            $('#employeeSearch').select2({
                theme: 'bootstrap4',
                placeholder: 'Search by employee name or NIK...',
                allowClear: true,
                ajax: {
                    url: '{{ route('dashboard.leave-management.search-employees') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term,
                            page: params.page
                        };
                    },
                    processResults: function(data, params) {
                        return {
                            results: data.map(function(item) {
                                return {
                                    id: item.id,
                                    text: item.text
                                };
                            })
                        };
                    },
                    cache: true
                }
            }).on('select2:open', function() {
                document.querySelector('.select2-search__field').focus();
            });

            // Initialize DataTables (Client-side)
            $('#openLeaveRequestsTable').DataTable({
                data: @json($openLeaveRequestsData),
                columns: [{
                        data: 'employee_name'
                    },
                    {
                        data: 'leave_type'
                    },
                    {
                        data: 'leave_period'
                    },
                    {
                        data: 'total_days'
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ],
                pageLength: 5,
                lengthMenu: [
                    [5, 10, 25],
                    [5, 10, 25]
                ]
            });

            $('#pendingCancellationsTable').DataTable({
                data: @json($pendingCancellationsData),
                columns: [{
                        data: 'employee_name'
                    },
                    {
                        data: 'leave_type'
                    },
                    {
                        data: 'days_to_cancel'
                    },
                    {
                        data: 'reason'
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ],
                pageLength: 5,
                lengthMenu: [
                    [5, 10, 25],
                    [5, 10, 25]
                ]
            });

            $('#paidLeaveWithoutDocsTable').DataTable({
                data: @json($paidLeaveWithoutDocsData),
                columns: [{
                        data: 'employee_name'
                    },
                    {
                        data: 'leave_type'
                    },
                    {
                        data: 'leave_period'
                    },
                    {
                        data: 'total_days'
                    },
                    {
                        data: 'days_remaining'
                    },
                    {
                        data: 'status_badge'
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ],
                pageLength: 5,
                lengthMenu: [
                    [5, 10, 25],
                    [5, 10, 25]
                ]
            });

            $('#employeesWithoutEntitlementsTable').DataTable({
                data: @json($employeesWithoutEntitlementsData),
                columns: [{
                        data: 'employee_name'
                    },
                    {
                        data: 'employee_nik'
                    },
                    {
                        data: 'doh'
                    },
                    {
                        data: 'position'
                    },
                    {
                        data: 'department'
                    },
                    {
                        data: 'project'
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ],
                pageLength: 5,
                lengthMenu: [
                    [5, 10, 25],
                    [5, 10, 25]
                ]
            });

            $('#employeesWithExpiringEntitlementsTable').DataTable({
                data: @json($employeesWithExpiringEntitlementsData),
                columns: [{
                        data: 'employee_name'
                    },
                    {
                        data: 'employee_nik'
                    },
                    {
                        data: 'expires',
                        className: 'text-center'
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ],
                pageLength: 5,
                lengthMenu: [
                    [5, 10, 25],
                    [5, 10, 25]
                ]
            });
        });

        // Global variables for modal actions
        let currentLeaveRequestId = null;
        let currentCancellationId = null;
        let currentCancellationAction = null;

        // Employee search function
        function searchEmployee() {
            const employeeId = $('#employeeSearch').val();
            if (!employeeId) {
                alert('Please select an employee first.');
                return;
            }

            // Show loading
            $('#employeeEntitlementResult').html(
                '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>').show();

            // Get employee entitlements from API
            $.ajax({
                url: '{{ route('dashboard.leave-management.search-employees') }}',
                method: 'GET',
                data: {
                    employee_id: employeeId
                },
                success: function(response) {
                    if (response.length > 0) {
                        const employee = response[0];
                        let html = `
                     <div class="card card-outline card-info">
                         <div class="card-header">
                             <h6 class="mb-0">
                                 <i class="fas fa-user mr-2"></i>${employee.name} (${employee.nik})
                             </h6>
                             <small class="text-muted">
                                 <i class="fas fa-briefcase mr-1"></i>${employee.position} |
                                 <i class="fas fa-building mr-1"></i>${employee.department}
                             </small>
                         </div>
                         <div class="card-body">
                             <div class="table-responsive">
                                 <table class="table table-bordered table-striped">
                                     <thead class="thead-light">
                                         <tr>
                                             <th><i class="fas fa-calendar-alt mr-1"></i>Leave Type</th>
                                             <th><i class="fas fa-gift mr-1"></i>Entitled Days</th>
                                             <th><i class="fas fa-check-circle mr-1"></i>Used Days</th>
                                             <th><i class="fas fa-clock mr-1"></i>Remaining Days</th>
                                             <th><i class="fas fa-calendar mr-1"></i>Period</th>
                                         </tr>
                                     </thead>
                                     <tbody>
                 `;

                        if (employee.entitlements && employee.entitlements.length > 0) {
                            employee.entitlements.forEach(function(entitlement) {
                                const remainingDays = entitlement.remaining_days || 0;
                                const usedDays = entitlement.used_days || 0;
                                const entitledDays = entitlement.entitled_days || 0;

                                html += `
                             <tr>
                                 <td><strong>${entitlement.leave_type}</strong></td>
                                 <td><span class="badge badge-primary">${entitledDays}</span></td>
                                 <td><span class="badge badge-warning">${usedDays}</span></td>
                                 <td><span class="badge badge-${remainingDays > 0 ? 'success' : 'danger'}">${remainingDays}</span></td>
                                 <td><small class="text-muted">${entitlement.period}</small></td>
                             </tr>
                         `;
                            });
                        } else {
                            html += `
                             <tr>
                                 <td colspan="5" class="text-center text-muted">
                                     <i class="fas fa-info-circle mr-2"></i>No leave entitlements found for this employee.
                                 </td>
                             </tr>
                         `;
                        }

                        html += `
                                     </tbody>
                                 </table>
                             </div>
                         </div>
                     </div>
                 `;

                        $('#employeeEntitlementResult').html(html);
                    } else {
                        $('#employeeEntitlementResult').html(
                            '<div class="alert alert-warning"><i class="fas fa-exclamation-triangle mr-2"></i>No employee found with the selected ID.</div>'
                        );
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error loading employee entitlements:', error);
                    $('#employeeEntitlementResult').html(
                        '<div class="alert alert-danger"><i class="fas fa-exclamation-circle mr-2"></i>Error loading employee entitlements. Please try again.</div>'
                    );
                }
            });
        }

        // Close leave request function
        function closeLeaveRequest(leaveRequestId) {
            currentLeaveRequestId = leaveRequestId;
            $('#closeLeaveRequestModal').modal('show');
        }

        $('#confirmCloseLeaveRequest').click(function() {
            if (currentLeaveRequestId) {
                $.ajax({
                    url: `{{ route('leave.requests.close', 'PLACEHOLDER') }}`.replace('PLACEHOLDER',
                        currentLeaveRequestId),
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        $('#closeLeaveRequestModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: 'Leave request closed successfully.',
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                    },
                    error: function(xhr) {
                        let errorMessage = 'Error closing leave request.';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: errorMessage,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                });
            }
        });

        // Cancellation functions
        function approveCancellation(cancellationId) {
            currentCancellationId = cancellationId;
            currentCancellationAction = 'approve';
            $('#cancellationModalTitle').text('Approve Cancellation Request');
            $('#confirmCancellationAction').removeClass('btn-danger').addClass('btn-success').text('Approve');
            $('#cancellationActionModal').modal('show');
        }

        function rejectCancellation(cancellationId) {
            currentCancellationId = cancellationId;
            currentCancellationAction = 'reject';
            $('#cancellationModalTitle').text('Reject Cancellation Request');
            $('#confirmCancellationAction').removeClass('btn-success').addClass('btn-danger').text('Reject');
            $('#cancellationActionModal').modal('show');
        }

        $('#confirmCancellationAction').click(function() {
            if (currentCancellationId && currentCancellationAction) {
                const notes = $('#cancellationNotes').val();

                // Validate notes for reject action
                if (currentCancellationAction === 'reject' && !notes.trim()) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Notes Required',
                        text: 'Please provide notes for rejecting the cancellation request.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                    return;
                }

                let routeUrl;
                if (currentCancellationAction === 'approve') {
                    routeUrl = `{{ route('leave.requests.cancellation.approve', 'PLACEHOLDER') }}`.replace(
                        'PLACEHOLDER', currentCancellationId);
                } else {
                    routeUrl = `{{ route('leave.requests.cancellation.reject', 'PLACEHOLDER') }}`.replace(
                        'PLACEHOLDER', currentCancellationId);
                }

                $.ajax({
                    url: routeUrl,
                    method: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        confirmation_notes: notes
                    },
                    success: function(response) {
                        $('#cancellationActionModal').modal('hide');
                        Swal.fire({
                            icon: 'success',
                            title: 'Success!',
                            text: `Cancellation request ${currentCancellationAction}d successfully.`,
                            timer: 2000,
                            showConfirmButton: false
                        }).then(() => {
                            location.reload();
                        });
                        $('#cancellationNotes').val('');
                    },
                    error: function(xhr) {
                        let errorMessage =
                            `Error ${currentCancellationAction}ing cancellation request.`;
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: errorMessage,
                            timer: 2000,
                            showConfirmButton: false
                        });
                    }
                });
            }
        });

        // Send reminder function
        function sendReminder(leaveRequestId) {
            $.ajax({
                url: `/leave/requests/${leaveRequestId}/send-reminder`,
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}'
                },
                success: function(response) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: 'Reminder sent successfully.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Error sending reminder.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            });
        }

        // Bulk reminder function
        function sendBulkReminder() {
            Swal.fire({
                title: 'Confirm Action',
                text: 'Send reminder to all employees with paid leave without supporting documents?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, Send Reminders'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '#', // Route not implemented yet
                        method: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success!',
                                text: 'Bulk reminders sent successfully.',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        },
                        error: function() {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error!',
                                text: 'Error sending bulk reminders.',
                                timer: 2000,
                                showConfirmButton: false
                            });
                        }
                    });
                }
            });
        }

        // View all cancellations
        function viewAllCancellations() {
            window.location.href = '{{ route('leave.reports.cancellation') }}';
        }
    </script>
@endpush
