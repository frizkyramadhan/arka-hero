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
                        <li class="breadcrumb-item active">Roster Management</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Project Selection Card -->
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-filter mr-2"></i>Project Filter</h3>
                    <div class="card-tools">
                        <div class="btn-group">
                            @if ($selectedProject)
                                <a href="{{ route('leave.periodic-requests.create', ['project_id' => $selectedProject->id]) }}"
                                    class="btn btn-sm btn-primary" title="Create Periodic Leave">
                                    <i class="fas fa-calendar-plus mr-1"></i> Create Periodic Leave
                                </a>
                                <a href="{{ route('rosters.calendar', ['project_id' => $selectedProject->id]) }}"
                                    class="btn btn-sm btn-warning" title="Calendar View">
                                    <i class="fas fa-calendar-alt mr-1"></i> Calendar View
                                </a>
                                <a href="{{ route('rosters.export', ['project_id' => $selectedProject->id]) }}"
                                    class="btn btn-sm btn-success" title="Export to Excel">
                                    <i class="fas fa-file-excel mr-1"></i> Export
                                </a>
                            @else
                                <button type="button" class="btn btn-sm btn-primary" disabled title="Select project first">
                                    <i class="fas fa-calendar-plus mr-1"></i> Create Periodic Leave
                                </button>
                                <button type="button" class="btn btn-sm btn-warning" disabled title="Select project first">
                                    <i class="fas fa-calendar-alt mr-1"></i> Calendar View
                                </button>
                                <button type="button" class="btn btn-sm btn-success" disabled title="Select project first">
                                    <i class="fas fa-file-excel mr-1"></i> Export
                                </button>
                            @endif
                            <button type="button" class="btn btn-sm btn-info" data-toggle="modal"
                                data-target="#modalImport" title="Import from Excel">
                                <i class="fas fa-file-import mr-1"></i> Import
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('rosters.index') }}" id="filterForm">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="project_id">Select Project <span class="text-danger">*</span></label>
                                    <select name="project_id" id="project_id" class="form-control select2" required>
                                        <option value="">-- Select Project --</option>
                                        @foreach ($projects as $project)
                                            <option value="{{ $project->id }}"
                                                {{ $selectedProject && $selectedProject->id == $project->id ? 'selected' : '' }}>
                                                {{ $project->project_code }} - {{ $project->project_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="search">Search Employee</label>
                                    <input type="text" name="search" id="search" class="form-control"
                                        placeholder="NIK or Name" value="{{ $search }}">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <label>&nbsp;</label>
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-search mr-1"></i> Filter
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Import Modal -->
            <div class="modal fade" id="modalImport" tabindex="-1">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header bg-info">
                            <h5 class="modal-title">Import Roster Data</h5>
                            <button type="button" class="close text-white" data-dismiss="modal">
                                <span>&times;</span>
                            </button>
                        </div>
                        <form action="{{ route('rosters.import') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="file">Select Excel File <span class="text-danger">*</span></label>
                                    <input type="file" class="form-control-file" id="file" name="file"
                                        accept=".xlsx,.xls" required>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> File must be in .xlsx or .xls format (max 10MB)
                                    </small>
                                </div>
                                <div class="alert alert-info">
                                    <strong>Format:</strong> NIK, Full Name, Position, Level, Pattern, Cycle No, Work Start,
                                    Work End, Adjusted Days,
                                    Leave Start, Leave End, Remarks, Status<br>
                                    <small><i class="fas fa-info-circle"></i> Position, Level, Pattern are informational
                                        only. NIK, Cycle No, Work Start, and Work End are required.</small>
                                </div>
                                @if (session('failures'))
                                    <div class="alert alert-warning">
                                        <strong>Import Errors:</strong>
                                        <ul class="mb-0">
                                            @foreach (session('failures') as $failure)
                                                <li>Row {{ $failure['row'] }} ({{ $failure['attribute'] }}):
                                                    {{ $failure['errors'] }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-upload mr-1"></i> Import
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            @if ($selectedProject)
                <!-- Employee List Card -->
                <div class="card card-primary card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-users mr-2"></i>Employees - {{ $selectedProject->project_code }}
                        </h3>
                        <div class="card-tools">
                            <span class="badge badge-primary">{{ $employees->total() }} employees</span>
                        </div>
                    </div>
                    <div class="card-body">
                        @if ($employees->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover table-bordered">
                                    <thead class="thead-light">
                                        <tr>
                                            <th width="5%">No</th>
                                            <th width="10%">NIK</th>
                                            <th width="20%">Full Name</th>
                                            <th width="15%">Position</th>
                                            <th width="10%">Level</th>
                                            <th width="10%">Pattern</th>
                                            <th width="10%">Cycles</th>
                                            <th width="10%">Status</th>
                                            <th width="10%" class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($employees as $index => $admin)
                                            <tr>
                                                <td>{{ $employees->firstItem() + $index }}</td>
                                                <td><strong>{{ $admin->nik }}</strong></td>
                                                <td>{{ $admin->employee->fullname ?? 'N/A' }}</td>
                                                <td>{{ $admin->position->position_name ?? 'N/A' }}</td>
                                                <td>
                                                    @if ($admin->level)
                                                        <span class="badge badge-info">{{ $admin->level->name }}</span>
                                                    @else
                                                        <span class="badge badge-secondary">No Level</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($admin->level && $admin->level->hasRosterConfig())
                                                        <span
                                                            class="badge badge-success">{{ $admin->level->getRosterPatternDisplay() }}</span>
                                                    @else
                                                        <span class="badge badge-secondary">-</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($admin->roster)
                                                        <span class="badge badge-primary">
                                                            {{ $admin->roster->roster_details_count ?? 0 }} cycles
                                                        </span>
                                                    @else
                                                        <span class="badge badge-secondary">0 cycles</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if ($admin->roster)
                                                        @php
                                                            $currentDetail = $admin->roster->currentDetail;
                                                        @endphp
                                                        @if ($currentDetail)
                                                            @if ($currentDetail->isOnLeave())
                                                                <span class="badge badge-warning">On Leave</span>
                                                            @elseif($currentDetail->isActive())
                                                                <span class="badge badge-success">Active</span>
                                                            @endif
                                                        @else
                                                            <span class="badge badge-info">No Active Cycle</span>
                                                        @endif
                                                    @else
                                                        <span class="badge badge-secondary">No Roster</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if ($admin->roster)
                                                        <a href="{{ route('rosters.show', $admin->roster->id) }}"
                                                            class="btn btn-sm btn-info" title="View Details">
                                                            <i class="fas fa-eye"></i>
                                                        </a>
                                                        <button type="button"
                                                            class="btn btn-sm btn-danger btn-delete-roster"
                                                            data-roster-id="{{ $admin->roster->id }}"
                                                            data-administration-id="{{ $admin->id }}"
                                                            data-employee-name="{{ $admin->employee->fullname ?? 'N/A' }}"
                                                            data-has-roster-config="{{ $admin->level && $admin->level->hasRosterConfig() ? '1' : '0' }}"
                                                            title="Delete Roster">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    @else
                                                        @if ($admin->level && $admin->level->hasRosterConfig())
                                                            <button type="button"
                                                                class="btn btn-sm btn-success btn-create-roster"
                                                                data-administration-id="{{ $admin->id }}"
                                                                data-employee-name="{{ $admin->employee->fullname ?? 'N/A' }}"
                                                                title="Create Roster">
                                                                <i class="fas fa-plus"></i> Create
                                                            </button>
                                                        @else
                                                            <span class="badge badge-secondary"
                                                                title="Level tidak memiliki roster cycle">
                                                                <i class="fas fa-ban"></i> Not Available
                                                            </span>
                                                        @endif
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="mt-3">
                                {{ $employees->appends(request()->query())->links() }}
                            </div>
                        @else
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle mr-2"></i>
                                No employees found for this project{{ $search ? ' with search: "' . $search . '"' : '' }}.
                            </div>
                        @endif
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-body">
                        <div class="alert alert-info mb-0">
                            <h5><i class="icon fas fa-info-circle"></i> Information</h5>
                            Please select a project to view employees and manage their rosters.
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Initialize Select2
            $('.select2').select2({
                theme: 'bootstrap4'
            });

            // Auto-submit on project change
            $('#project_id').change(function() {
                $('#filterForm').submit();
            });

            // Create Roster
            $(document).on('click', '.btn-create-roster', function() {
                const $btn = $(this);
                const administrationId = $btn.data('administration-id');
                const employeeName = $btn.data('employee-name');
                const $row = $btn.closest('tr');

                Swal.fire({
                    title: 'Create Roster',
                    html: `Create roster for <strong>${employeeName}</strong>?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Create',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading state
                        $btn.prop('disabled', true).html(
                            '<i class="fas fa-spinner fa-spin"></i> Creating...');

                        $.ajax({
                            url: '{{ route('rosters.store') }}',
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                administration_id: administrationId
                            },
                            success: function(response) {
                                if (response.success) {
                                    toast_success(response.message);

                                    // Update UI immediately instead of full page reload
                                    const rosterId = response.data.roster_id;

                                    // Update Cycles column
                                    $row.find('td:eq(6)').html(
                                        '<span class="badge badge-primary">0 cycles</span>'
                                    );

                                    // Update Status column
                                    $row.find('td:eq(7)').html(
                                        '<span class="badge badge-info">No Active Cycle</span>'
                                    );

                                    // Update Action column
                                    const actionCell = $row.find('td:eq(8)');
                                    actionCell.html(`
                                <a href="{{ url('rosters') }}/${rosterId}" class="btn btn-sm btn-info" title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <button type="button" class="btn btn-sm btn-danger btn-delete-roster"
                                    data-roster-id="${rosterId}"
                                    data-administration-id="${administrationId}"
                                    data-employee-name="${employeeName}"
                                    data-has-roster-config="1"
                                    title="Delete Roster">
                                    <i class="fas fa-trash"></i>
                                </button>
                            `);
                                }
                            },
                            error: function(xhr) {
                                const message = xhr.responseJSON?.message ||
                                    'Failed to create roster';
                                toast_error(message);

                                // Restore button state
                                $btn.prop('disabled', false).html(
                                    '<i class="fas fa-plus"></i> Create');
                            }
                        });
                    }
                });
            });

            // Delete Roster
            $(document).on('click', '.btn-delete-roster', function() {
                const $btn = $(this);
                const rosterId = $btn.data('roster-id');
                const employeeName = $btn.data('employee-name');
                const $row = $btn.closest('tr');

                Swal.fire({
                    title: 'Delete Roster',
                    html: `Are you sure you want to delete roster for <strong>${employeeName}</strong>?<br><small class="text-danger">All cycle data will be deleted!</small>`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Delete',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading state
                        $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i>');

                        $.ajax({
                            url: `{{ url('rosters') }}/${rosterId}`,
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                _method: 'DELETE'
                            },
                            success: function(response) {
                                if (response.success) {
                                    toast_success(response.message);

                                    // Get data from button
                                    const administrationId = $btn.data(
                                        'administration-id');
                                    const hasRosterConfig = $btn.data(
                                        'has-roster-config') === 1;

                                    // Update Cycles column
                                    $row.find('td:eq(6)').html(
                                        '<span class="badge badge-secondary">0 cycles</span>'
                                    );

                                    // Update Status column
                                    $row.find('td:eq(7)').html(
                                        '<span class="badge badge-secondary">No Roster</span>'
                                    );

                                    // Update Action column
                                    const actionCell = $row.find('td:eq(8)');
                                    if (hasRosterConfig) {
                                        actionCell.html(`
                                    <button type="button" class="btn btn-sm btn-success btn-create-roster"
                                        data-administration-id="${administrationId}"
                                        data-employee-name="${employeeName}"
                                        title="Create Roster">
                                        <i class="fas fa-plus"></i> Create
                                    </button>
                                `);
                                    } else {
                                        actionCell.html(`
                                    <span class="badge badge-secondary" title="Level tidak memiliki roster cycle">
                                        <i class="fas fa-ban"></i> Not Available
                                    </span>
                                `);
                                    }
                                }
                            },
                            error: function(xhr) {
                                const message = xhr.responseJSON?.message ||
                                    'Failed to delete roster';
                                toast_error(message);

                                // Restore button state
                                $btn.prop('disabled', false).html(
                                    '<i class="fas fa-trash"></i>');
                            }
                        });
                    }
                });
            });
        });
    </script>
@endsection
