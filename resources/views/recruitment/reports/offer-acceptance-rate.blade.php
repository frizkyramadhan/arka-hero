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
                    <form method="GET" action="{{ route('recruitment.reports.offer-acceptance-rate') }}" class="row"
                        id="filterForm">
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
                                    @foreach ($departments as $dept)
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
                                <label for="position">Position</label>
                                <select name="position" id="position" class="form-control">
                                    <option value="">All Positions</option>
                                    @foreach ($positions as $pos)
                                        <option value="{{ $pos->id }}" {{ $position == $pos->id ? 'selected' : '' }}>
                                            {{ $pos->position_name }}
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
                                    @foreach ($projects as $proj)
                                        <option value="{{ $proj->id }}" {{ $project == $proj->id ? 'selected' : '' }}>
                                            {{ $proj->project_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </form>
                    <div class="row mt-2">
                        <div class="col-12">
                            <button type="submit" form="filterForm" class="btn btn-primary mr-2">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('recruitment.reports.offer-acceptance-rate') }}"
                                class="btn btn-warning mr-2">
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
                        <table id="offerAcceptanceTable" class="table table-sm table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th>Request No</th>
                                    <th>Department</th>
                                    <th>Position</th>
                                    <th>Project</th>
                                    <th>Candidate Name</th>
                                    <th>Offering Date</th>
                                    <th>Response Date</th>
                                    <th>Response Time (Days)</th>
                                    <th>Response</th>
                                    <th>Offering Letter No</th>
                                    <th>Notes</th>
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
            var table = $('#offerAcceptanceTable').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                    url: '{{ route('recruitment.reports.offer-acceptance-rate.data') }}',
                    type: 'GET',
                    data: function(d) {
                        d.date1 = $('input[name="date1"]').val();
                        d.date2 = $('input[name="date2"]').val();
                        d.department = $('select[name="department"]').val();
                        d.position = $('select[name="position"]').val();
                        d.project = $('select[name="project"]').val();
                    }
                },
                columns: [{
                        data: 'request_no',
                        render: function(data, type, row) {
                            return '<a href="/recruitment/sessions/' + row.session_id +
                                '/candidate" target="_blank" title="View Session Details">' + data +
                                '</a>';
                        }
                    },
                    { data: 'department' },
                    { data: 'position' },
                    { data: 'project' },
                    { data: 'candidate_name' },
                    { data: 'offering_date' },
                    { data: 'response_date' },
                    {
                        data: 'response_time',
                        render: function(data, type, row) {
                            if (data === '-') return data;
                            var colorClass = data <= 3 ? 'badge-success' : (data <= 7 ?
                                'badge-warning' : 'badge-danger');
                            return '<span class="badge ' + colorClass + '">' + data +
                                ' days</span>';
                        }
                    },
                    {
                        data: 'response',
                        render: function(data, type, row) {
                            var colorClass = data.toLowerCase() === 'accepted' ? 'badge-success' :
                                (data.toLowerCase() === 'rejected' ? 'badge-danger' :
                                    'badge-warning');
                            return '<span class="badge ' + colorClass + '">' + data + '</span>';
                        }
                    },
                    { data: 'offering_letter_no' },
                    { data: 'notes' }
                ],
                responsive: true,
                pageLength: 25,
                order: [[5, 'desc']],
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
                    position: $('select[name="position"]').val(),
                    project: $('select[name="project"]').val()
                });
                window.location.href = '{{ route('recruitment.reports.offer-acceptance-rate.export') }}' + '?' + params;
            });
        });
    </script>
@endpush
