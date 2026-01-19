@extends('layouts.main')

@section('title', 'Leave Entitlement Management')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Leave Entitlement Management</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Leave Entitlements</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Display Import Errors -->
            @if (session()->has('failures'))
                <div class="card card-danger">
                    <div class="card-header">
                        <h3 class="card-title"><i class="icon fas fa-exclamation-triangle"></i> Import Validation Errors
                        </h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <div class="card-body" style="display: block;">
                        <div class="table-responsive">
                            <table class="table table-sm table-striped">
                                <thead>
                                    <tr>
                                        <th style="width: 5%">Sheet</th>
                                        <th class="text-center" style="width: 5%">Row</th>
                                        <th style="width: 20%">Column</th>
                                        <th style="width: 20%">Value</th>
                                        <th>Error Message</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach (session()->get('failures') as $failure)
                                        <tr>
                                            <td>{{ $failure['sheet'] }}</td>
                                            <td class="text-center">{{ $failure['row'] }}</td>
                                            <td>
                                                <strong>{{ ucwords(str_replace('_', ' ', $failure['attribute'])) }}</strong>
                                            </td>
                                            <td>
                                                @if (isset($failure['value']))
                                                    {{ $failure['value'] }}
                                                @endif
                                            </td>
                                            <td>
                                                {!! nl2br(e($failure['errors'])) !!}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="mt-1">
                            <small class="text-muted">
                                <i class="fas fa-info-circle"></i>
                                Please correct these errors in your Excel file and try importing again.
                            </small>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Project Filter and Generate Entitlements Card -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Project Filter & Generate Entitlements</h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" data-card-widget="collapse">
                            <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- Project Filter -->
                        <div class="col-md-12">
                            <form method="GET" action="{{ route('leave.entitlements.index') }}" id="projectFilterForm">
                                <div class="form-group">
                                    <div class="row align-items-end">
                                        <div class="col-md-4">
                                            <label for="project_id">Select Project</label>
                                            <select name="project_id" id="project_id" class="select2bs4 form-control"
                                                required>
                                                <option value="">Choose Project...</option>
                                                <option value="all" {{ $showAllProjects ? 'selected' : '' }}>All Projects
                                                </option>
                                                @foreach ($projects as $project)
                                                    <option value="{{ $project->id }}"
                                                        {{ $selectedProject && $selectedProject->id == $project->id ? 'selected' : '' }}>
                                                        {{ $project->project_code }} - {{ $project->project_name }}
                                                        @if ($project->leave_type)
                                                            ({{ ucfirst($project->leave_type) }})
                                                        @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                        <div class="col-md-8">
                                            <div class="btn-group" role="group">
                                                <button type="submit" class="btn btn-primary">
                                                    <i class="fas fa-search"></i> Load Employees
                                                </button>
                                                <a href="{{ route('leave.entitlements.index') }}"
                                                    class="btn btn-secondary">
                                                    <i class="fas fa-times"></i> Clear
                                                </a>
                                                <a href="#" id="exportBtn" class="btn btn-info"
                                                    onclick="exportData(event)">
                                                    <i class="fas fa-file-excel"></i> Export
                                                </a>
                                                <button type="button" class="btn btn-success" data-toggle="modal"
                                                    data-target="#importModal">
                                                    <i class="fas fa-file-upload"></i> Import
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        <!-- Generate Entitlements -->
                        @if ($selectedProject || $showAllProjects)
                            <div class="col-md-6">
                                {{-- <form method="POST" action="{{ route('leave.entitlements.generate-project') }}">
                                    @csrf
                                    <input type="hidden" name="project_id"
                                        value="{{ $showAllProjects ? 'all' : $selectedProject->id }}">
                                    <div class="form-group">
                                        <label for="year">Year</label>
                                        <select name="year" id="year" class="select2bs4 form-control" required>
                                            @for ($year = now()->year - 1; $year <= now()->year + 1; $year++)
                                                <option value="{{ $year }}"
                                                    {{ $year == now()->year ? 'selected' : '' }}>
                                                    {{ $year }}
                                                </option>
                                            @endfor
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        @if ($showAllProjects)
                                            <button type="submit" class="btn btn-success"
                                                onclick="return confirm('Generate entitlements for all employees in all projects?')">
                                                <i class="fas fa-plus"></i> Generate Entitlements
                                            </button>
                                            <a href="{{ route('leave.entitlements.export-project', ['project_id' => 'all']) }}"
                                                class="btn btn-info">
                                                <i class="fas fa-file-excel"></i> Export Excel
                                            </a>
                                        @else
                                            <button type="submit" class="btn btn-success"
                                                onclick="return confirm('Generate entitlements for all employees in {{ $selectedProject->project_code }}?')">
                                                <i class="fas fa-plus"></i> Generate Entitlements
                                            </button>
                                            <a href="{{ route('leave.entitlements.export-project', ['project_id' => $selectedProject->id]) }}"
                                                class="btn btn-info">
                                                <i class="fas fa-file-excel"></i> Export Excel
                                            </a>
                                        @endif
                                    </div>
                                </form> --}}
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @if ($selectedProject || $showAllProjects)
                <!-- Employee Entitlements Card -->
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            @if ($showAllProjects)
                                Employee Remaining Leave Entitlements - All Projects
                                <span class="badge badge-secondary">All Projects</span>
                            @else
                                Employee Remaining Leave Entitlements - {{ $selectedProject->project_code }}
                            @endif
                        </h3>
                        @if ($showAllProjects || $selectedProject)
                            <div class="card-tools">
                                @if (auth()->user()->hasRole('administrator'))
                                    <button type="button" class="btn btn-danger btn-sm mr-2"
                                        onclick="confirmClearEntitlements()">
                                        <i class="fas fa-trash"></i> Clear Entitlements
                                    </button>
                                @endif
                                @can('leave-entitlements.create')
                                    @if (!$showAllProjects && $selectedProject)
                                        <button type="button" class="btn btn-success btn-sm"
                                            onclick="confirmGenerateEntitlements()">
                                            <i class="fas fa-magic"></i> Generate Entitlements
                                        </button>
                                    @endif
                                @endcan
                            </div>
                        @endif
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table id="employeesTable" class="table table-bordered table-striped" width="100%">
                                <thead>
                                    <tr>
                                        <th class="text-center align-middle">No</th>
                                        <th class="align-middle">NIK</th>
                                        <th class="align-middle">Nama</th>
                                        <th class="align-middle">Position</th>
                                        @if ($showAllProjects)
                                            <th class="align-middle">Project</th>
                                        @endif
                                        <th class="align-middle">DOH</th>
                                        <th class="text-center align-middle">Cuti Tahunan</th>
                                        <th class="text-center align-middle">Sakit</th>
                                        <th class="text-center align-middle">Ijin Tanpa Upah</th>
                                        <th class="text-center align-middle">Cuti Panjang</th>
                                        <th class="text-center align-middle">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Data will be loaded via DataTable -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @else
                <!-- Empty State Card -->
                <div class="card">
                    <div class="card-body text-center">
                        <i class="fas fa-filter fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">Select a Project to View Employee Entitlements</h4>
                        <p class="text-muted">Choose a project from the filter above to load employee leave entitlements.
                        </p>
                    </div>
                </div>
            @endif
        </div>
    </section>

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Import Leave Entitlements</h5>
                    <button type="button" class="close" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <form action="{{ route('leave.entitlements.import-template') }}" method="POST"
                    enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="file">Select Excel File</label>
                            <input type="file" name="file" id="file" class="form-control-file"
                                accept=".xlsx,.xls" required>
                            <small class="form-text text-muted">File format: .xlsx or .xls (max 10MB)</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-upload"></i> Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <style>
        .swal-wide {
            width: 600px !important;
        }

        .swal-wide .swal2-html-container {
            text-align: left !important;
        }

        /* Compact table styling */
        #employeesTable {
            font-size: 0.9rem;
        }

        #employeesTable thead th {
            padding: 0.5rem 0.4rem;
            font-size: 0.85rem;
            font-weight: 600;
            white-space: nowrap;
        }

        #employeesTable tbody td {
            padding: 0.4rem 0.4rem;
            vertical-align: middle;
        }

        #employeesTable .badge {
            font-size: 0.8rem;
            padding: 0.3rem 0.6rem;
            font-weight: 700;
        }

        #employeesTable .btn {
            padding: 0.3rem 0.6rem;
            font-size: 0.8rem;
        }

        #employeesTable .btn i {
            font-size: 0.75rem;
        }
    </style>
@endsection

@section('scripts')
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
        $(function() {
            $('.select2bs4').select2({
                theme: 'bootstrap4',
                width: '100%'
            }).on('select2:open', function() {
                document.querySelector('.select2-search__field').focus();
            });
        });

        $(document).ready(function() {
            // Auto-submit form on project selection
            $('#project_id').change(function() {
                if ($(this).val()) {
                    $(this).closest('form').submit();
                }
            });

            // Export function
            window.exportData = function(event) {
                event.preventDefault();
                const projectId = $('#project_id').val();

                // Build export URL
                let exportUrl = "{{ route('leave.entitlements.export-template') }}?include_data=1";

                // Add project_id parameter if project is selected
                if (projectId) {
                    exportUrl += "&project_id=" + encodeURIComponent(projectId);
                }

                // Redirect to export URL
                window.location.href = exportUrl;
            };

            // Clear Entitlements Confirmation
            window.confirmClearEntitlements = function() {
                const projectId = "{{ $showAllProjects ? 'all' : $selectedProject->id ?? '' }}";
                const projectCode =
                    "{{ $showAllProjects ? 'All Projects' : $selectedProject->project_code ?? '' }}";
                const leaveType =
                    "{{ $showAllProjects ? 'All Projects' : $selectedProject->leave_type ?? '' }}";

                Swal.fire({
                    title: '<i class="fas fa-trash text-danger"></i> Clear All Entitlements',
                    html: `
                        <div class="text-left">
                            <p><strong>⚠️ WARNING: This action cannot be undone!</strong></p>
                            <p>You are about to delete <strong>ALL</strong> leave entitlements for:</p>
                            <ul class="text-left">
                                <li><strong>Project:</strong> ${projectCode}</li>
                                <li><strong>Type:</strong> ${leaveType.charAt(0).toUpperCase() + leaveType.slice(1)} Project</li>
                            </ul>
                            <p class="text-danger"><strong>This will permanently remove all entitlement records for all employees in this project.</strong></p>
                            <div class="form-group mt-3">
                                <label for="confirmText">Type <strong>YES</strong> to confirm:</label>
                                <input type="text" id="confirmText" class="form-control" placeholder="Type YES here">
                            </div>
                        </div>
                    `,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-trash"></i> Clear All Entitlements',
                    cancelButtonText: '<i class="fas fa-times"></i> Cancel',
                    width: '600px',
                    customClass: {
                        popup: 'swal-wide'
                    },
                    preConfirm: () => {
                        const confirmValue = document.getElementById('confirmText').value;
                        if (confirmValue !== 'YES') {
                            Swal.showValidationMessage('Please type YES to confirm');
                            return false;
                        }
                        return true;
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Create form and submit
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = '{{ route('leave.entitlements.clear-entitlements') }}';

                        // Add CSRF token
                        const csrfToken = document.createElement('input');
                        csrfToken.type = 'hidden';
                        csrfToken.name = '_token';
                        csrfToken.value = '{{ csrf_token() }}';
                        form.appendChild(csrfToken);

                        // Add project_id
                        const projectIdInput = document.createElement('input');
                        projectIdInput.type = 'hidden';
                        projectIdInput.name = 'project_id';
                        projectIdInput.value = projectId;
                        form.appendChild(projectIdInput);

                        // Add confirm
                        const confirmInput = document.createElement('input');
                        confirmInput.type = 'hidden';
                        confirmInput.name = 'confirm';
                        confirmInput.value = 'yes';
                        form.appendChild(confirmInput);

                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            };

            // Initialize DataTable if project is selected
            @if ($selectedProject || $showAllProjects)
                initializeDataTable();
            @endif
        });

        function initializeDataTable() {
            $('#employeesTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('leave.entitlements.data') }}",
                    data: function(d) {
                        @if ($showAllProjects)
                            d.project_id = 'all';
                        @elseif ($selectedProject)
                            d.project_id = {{ $selectedProject->id }};
                        @endif
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'nik',
                        name: 'nik'
                    },
                    {
                        data: 'name',
                        name: 'name'
                    },
                    {
                        data: 'position',
                        name: 'position'
                    },
                    @if ($showAllProjects)
                        {
                            data: 'project',
                            name: 'project'
                        },
                    @endif {
                        data: 'doh',
                        name: 'doh'
                    },
                    {
                        data: 'annual',
                        name: 'annual',
                        className: 'text-center',
                        render: function(data, type, row) {
                            return '<span class="badge badge-info">' + (row.annual_remaining || 0) +
                                '</span>';
                        }
                    },
                    {
                        data: 'sick',
                        name: 'sick',
                        className: 'text-center',
                        render: function(data, type, row) {
                            return '<span class="badge badge-primary">' + (row.sick_remaining || 0) +
                                '</span>';
                        }
                    },
                    {
                        data: 'unpaid',
                        name: 'unpaid',
                        className: 'text-center',
                        render: function(data, type, row) {
                            return '<span class="badge badge-warning">' + (row.unpaid_remaining || 0) +
                                '</span>';
                        }
                    },
                    {
                        data: 'lsl',
                        name: 'lsl',
                        className: 'text-center',
                        render: function(data, type, row) {
                            return '<span class="badge badge-success">' + (row.lsl_remaining || 0) +
                                '</span>';
                        }
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data) {
                            return '<div class="btn-group" role="group">' +
                                '<a href="' + data.view_url +
                                '" class="btn btn-info mr-1" title="View Details">' +
                                '<i class="fas fa-eye"></i>' +
                                '</a>' +
                                '<a href="' + data.edit_url +
                                '" class="btn btn-warning" title="Edit Entitlements">' +
                                '<i class="fas fa-edit"></i>' +
                                '</a>' +
                                '</div>';
                        }
                    }
                ],
                order: [
                    [1, 'asc']
                ], // Default order by NIK
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, 100],
                    [10, 25, 50, 100]
                ],
                language: {
                    processing: "Loading...",
                    search: "Search:",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "Showing 0 to 0 of 0 entries",
                    infoFiltered: "(filtered from _MAX_ total entries)",
                    paginate: {
                        first: "First",
                        last: "Last",
                        next: "Next",
                        previous: "Previous"
                    },
                    emptyTable: "No employees found for this project."
                },
                dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>' +
                    '<"row"<"col-sm-12"tr>>' +
                    '<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>',
                responsive: true
            });
        }

        @if (!$showAllProjects && $selectedProject)
            /**
             * Confirm and generate leave entitlements for selected project
             * Uses service start DOH calculation based on termination reason
             */
            function confirmGenerateEntitlements() {
                const config = {
                    projectCode: '{{ $selectedProject->project_code }}',
                    projectType: '{{ $selectedProject->leave_type }}',
                    currentYear: '{{ now()->year }}',
                    projectId: '{{ $selectedProject->id }}',
                    route: '{{ route('leave.entitlements.generate-selected-project') }}',
                    csrfToken: '{{ csrf_token() }}'
                };

                const isRoster = config.projectType === 'roster';
                const projectGroup = isRoster ? 'Group 2 (Roster)' : 'Group 1 (Regular)';
                const groupBadgeClass = isRoster ? 'warning' : 'info';

                // Build leave types list based on project type
                const leaveTypes = [
                    '• Paid Leave (event-based)',
                    '• Unpaid Leave'
                ];

                if (!isRoster) {
                    leaveTypes.push('• Annual Leave (12+ months service)');
                }

                leaveTypes.push(
                    isRoster ?
                    '• LSL (Long Service Leave - 60/72 months service)' :
                    '• LSL (60/72 months service)'
                );

                // Build period information
                const periodInfo = isRoster ?
                    '• Calendar Year (Jan-Dec)' :
                    '• DOH-based Period (calculated from service start DOH)';

                Swal.fire({
                    title: '<i class="fas fa-magic text-success"></i> Generate Leave Entitlements',
                    html: `
                    <div class="text-left">
                        <div class="alert alert-info mb-3">
                            <i class="fas fa-info-circle"></i>
                            <strong>Generate Entitlements for ${config.projectCode}</strong>
                        </div>

                        <p class="mb-3">
                            This will generate default leave entitlements for all active employees in the
                            <strong>${config.projectCode}</strong> project based on the following rules:
                        </p>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <h6><i class="fas fa-list text-primary"></i> Leave Types:</h6>
                                <ul class="list-unstyled">
                                    <li>
                                        <span class="badge badge-${groupBadgeClass}">${projectGroup}</span>
                                    </li>
                                    ${leaveTypes.map(type => `<li>${type}</li>`).join('')}
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6><i class="fas fa-calendar text-success"></i> Period:</h6>
                                <ul class="list-unstyled">
                                    <li>${periodInfo}</li>
                                    <li>• Current Year: ${config.currentYear}</li>
                                </ul>
                            </div>
                        </div>

                        <div class="alert alert-warning">
                            <i class="fas fa-exclamation-triangle"></i>
                            <strong>Note:</strong> This will create entitlements for the current year period.
                            Existing entitlements for the same period will be skipped to prevent duplicates.
                        </div>

                        <div class="alert alert-info">
                            <i class="fas fa-calculator"></i>
                            <strong>Service Start DOH Calculation:</strong>
                            <p class="mb-2 mt-2">
                                Employee service period is calculated from the <strong>service start DOH</strong>,
                                which is determined by termination reasons:
                            </p>
                            <ul class="mb-0">
                                <li>
                                    <i class="fas fa-check-circle text-success"></i>
                                    <strong>"End of Contract"</strong> → Service continues from <strong>first DOH</strong>
                                    (masa kerja lanjut, termasuk rehire)
                                </li>
                                <li>
                                    <i class="fas fa-times-circle text-danger"></i>
                                    <strong>Other reasons</strong> → Service resets from <strong>new hire DOH</strong>
                                    (masa kerja dihitung ulang)
                                </li>
                            </ul>
                            <p class="mb-0 mt-2">
                                <small>
                                    <i class="fas fa-info-circle"></i>
                                    Example: Employee with multiple NIKs and "End of Contract" terminations
                                    will have service calculated from the earliest DOH.
                                </small>
                            </p>
                        </div>
                    </div>
                `,
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: '<i class="fas fa-magic"></i> Generate Entitlements',
                    cancelButtonText: '<i class="fas fa-times"></i> Cancel',
                    width: '650px',
                    customClass: {
                        popup: 'swal-wide'
                    },
                    didOpen: () => {
                        // Add loading state on confirm
                        const confirmButton = document.querySelector('.swal2-confirm');
                        if (confirmButton) {
                            confirmButton.addEventListener('click', function() {
                                Swal.showLoading();
                            });
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        submitGenerateForm(config);
                    }
                });
            }

            /**
             * Submit form to generate entitlements
             * @param {Object} config - Configuration object with route, csrfToken, projectId
             */
            function submitGenerateForm(config) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = config.route;

                // Add CSRF token
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = config.csrfToken;
                form.appendChild(csrfInput);

                // Add project_id
                const projectInput = document.createElement('input');
                projectInput.type = 'hidden';
                projectInput.name = 'project_id';
                projectInput.value = config.projectId;
                form.appendChild(projectInput);

                document.body.appendChild(form);
                form.submit();
            }
        @endif
    </script>
@endsection
