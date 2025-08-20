@extends('layouts.main')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ $subtitle }}</h1>
                </div>
                <div class="col-sm-6">
                    <div class="float-right">
                        <a href="{{ route('recruitment.reports.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-filter"></i> Filter Options
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('recruitment.reports.aging') }}" class="row" id="filterForm">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="date1">Date From</label>
                                <input type="date" name="date1" id="date1" class="form-control"
                                    value="{{ $date1 }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="date2">Date To</label>
                                <input type="date" name="date2" id="date2" class="form-control"
                                    value="{{ $date2 }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="department">Department</label>
                                <select name="department" id="department" class="form-control">
                                    <option value="">All Departments</option>
                                    @foreach (\App\Models\Department::orderBy('department_name')->get() as $dept)
                                        <option value="{{ $dept->id }}"
                                            {{ $department == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->department_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="project">Project</label>
                                <select name="project" id="project" class="form-control">
                                    <option value="">All Projects</option>
                                    @foreach (\App\Models\Project::where('project_status', 1)->orderBy('project_code')->get() as $proj)
                                        <option value="{{ $proj->id }}" {{ $project == $proj->id ? 'selected' : '' }}>
                                            {{ $proj->project_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="draft" {{ $status == 'draft' ? 'selected' : '' }}>Draft
                                    </option>
                                    <option value="submitted" {{ $status == 'submitted' ? 'selected' : '' }}>Submitted
                                    </option>
                                    <option value="approved" {{ $status == 'approved' ? 'selected' : '' }}>Approved
                                    </option>
                                    <option value="rejected" {{ $status == 'rejected' ? 'selected' : '' }}>Rejected
                                    </option>
                                    <option value="closed" {{ $status == 'closed' ? 'selected' : '' }}>Closed</option>
                                </select>
                            </div>
                        </div>
                    </form>
                    <div class="row mt-2">
                        <div class="col-12">
                            <button type="submit" form="filterForm" class="btn btn-primary mr-2">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('recruitment.reports.aging') }}" class="btn btn-warning mr-2">
                                <i class="fas fa-undo"></i> Reset
                            </a>
                            <button type="button" id="exportExcelBtn" class="btn btn-success">
                                <i class="fas fa-file-excel"></i> Export Excel
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="agingTable" class="table table-sm table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="align-middle">Request No</th>
                                    <th class="align-middle">Department</th>
                                    <th class="align-middle">Position</th>
                                    <th class="align-middle">Project</th>
                                    <th class="align-middle">Requested By</th>
                                    <th class="align-middle">Requested At</th>
                                    <th class="align-middle">Status</th>
                                    <th class="align-middle">Days Open</th>
                                    <th class="align-middle">Latest Approval</th>
                                    <th class="align-middle">Approved At</th>
                                    <th class="align-middle">Days to Approve</th>
                                    <th class="align-middle">Remarks</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('styles')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
@endpush

@push('scripts')
    <!-- DataTables -->
    <script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            var table = $('#agingTable').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                    url: '{{ route('recruitment.reports.aging.data') }}',
                    type: 'GET',
                    data: function(d) {
                        d.date1 = $('input[name="date1"]').val();
                        d.date2 = $('input[name="date2"]').val();
                        d.department = $('select[name="department"]').val();
                        d.project = $('select[name="project"]').val();
                        d.status = $('select[name="status"]').val();
                    }
                },
                columns: [{
                        data: 'request_no',
                        render: function(data, type, row) {
                            return '<a href="{{ route('recruitment.sessions.show', '') }}/' +
                                row.request_id +
                                '" target="_blank" title="View Request Details">' + data + '</a>';
                        }
                    },
                    {
                        data: 'department'
                    },
                    {
                        data: 'position'
                    },
                    {
                        data: 'project'
                    },
                    {
                        data: 'requested_by'
                    },
                    {
                        data: 'requested_at'
                    },
                    {
                        data: 'status'
                    },
                    {
                        data: 'days_open'
                    },
                    {
                        data: 'latest_approval'
                    },
                    {
                        data: 'approved_at'
                    },
                    {
                        data: 'days_to_approve'
                    },
                    {
                        data: 'remarks'
                    }
                ],
                responsive: true,
                pageLength: 10,
                order: [
                    [5, 'desc']
                ],
                language: {
                    processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span>'
                }
            });

            // Refresh table when form is submitted
            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                table.ajax.reload();
            });

            // Export Excel with current filters
            $('#exportExcelBtn').on('click', function() {
                var params = $.param({
                    date1: $('input[name="date1"]').val(),
                    date2: $('input[name="date2"]').val(),
                    department: $('select[name="department"]').val(),
                    project: $('select[name="project"]').val(),
                    status: $('select[name="status"]').val()
                });
                window.location.href = '{{ route('recruitment.reports.aging.export') }}' + '?' + params;
            });
        });
    </script>
@endpush
