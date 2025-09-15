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
                    <form method="GET" action="{{ route('recruitment.reports.time-to-hire') }}" class="row"
                        id="filterForm">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="metric_type">Metric Type</label>
                                <select name="metric_type" id="metric_type" class="form-control">
                                    <option value="candidate" {{ $metric_type == 'candidate' ? 'selected' : '' }}>
                                        Time to Hire (Per Candidate)
                                    </option>
                                    <option value="fptk" {{ $metric_type == 'fptk' ? 'selected' : '' }}>
                                        Time to Fill (Per FPTK)
                                    </option>
                                </select>
                            </div>
                        </div>
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
                            <a href="{{ route('recruitment.reports.time-to-hire') }}" class="btn btn-warning mr-2">
                                <i class="fas fa-undo"></i> Reset
                            </a>
                            <button type="button" id="exportExcelBtn" class="btn btn-success">
                                <i class="fas fa-file-excel"></i> Export Excel
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <!-- Metric Type Info -->
                    <div class="alert alert-info" id="metricInfo">
                        <i class="fas fa-info-circle"></i>
                        <span id="metricDescription">
                            @if ($metric_type == 'candidate')
                                <strong>Time to Hire (Per Candidate):</strong> Waktu dari kandidat masuk proses recruitment
                                hingga di-hire.
                                Mengukur efisiensi proses recruitment untuk setiap kandidat individual.
                            @else
                                <strong>Time to Fill (Per FPTK):</strong> Waktu dari FPTK dibuat hingga posisi pertama
                                terisi.
                                Mengukur efisiensi pengisian posisi untuk setiap permintaan recruitment.
                            @endif
                        </span>
                    </div>

                    <div class="table-responsive">
                        <table id="timeToHireTable" class="table table-sm table-bordered table-striped">
                            <thead id="tableHeaders">
                                @if ($metric_type == 'candidate')
                                    <tr>
                                        <th class="align-middle">Candidate Name</th>
                                        <th class="align-middle">Request No</th>
                                        <th class="align-middle">Department</th>
                                        <th class="align-middle">Position</th>
                                        <th class="align-middle">Project</th>
                                        <th class="align-middle">Session Created</th>
                                        <th class="align-middle">Hiring Date</th>
                                        <th class="align-middle">Time to Hire (Days)</th>
                                        <th class="align-middle">Approval Days</th>
                                        <th class="align-middle">Recruitment Days</th>
                                        <th class="align-middle">Employment Type</th>
                                        <th class="align-middle">Status</th>
                                    </tr>
                                @else
                                    <tr>
                                        <th class="align-middle">Request No</th>
                                        <th class="align-middle">Department</th>
                                        <th class="align-middle">Position</th>
                                        <th class="align-middle">Project</th>
                                        <th class="align-middle">FPTK Created</th>
                                        <th class="align-middle">First Hiring Date</th>
                                        <th class="align-middle">Time to Fill (Days)</th>
                                        <th class="align-middle">Approval Days</th>
                                        <th class="align-middle">Recruitment Days</th>
                                        <th class="align-middle">Hired Count</th>
                                        <th class="align-middle">Required Qty</th>
                                        <th class="align-middle">Fill Rate</th>
                                        <th class="align-middle">Employment Type</th>
                                        <th class="align-middle">Status</th>
                                    </tr>
                                @endif
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('styles')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
@endsection

@section('scripts')
    <!-- DataTables -->
    <script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>

    <script>
        $(document).ready(function() {
            var currentMetricType = '{{ $metric_type }}';
            var table;

            function initializeTable() {
                // Destroy existing table if it exists
                if (table) {
                    try {
                        table.destroy();
                    } catch (e) {
                        console.log('Error destroying table:', e);
                    }
                    table = null;
                }

                // Check if DataTable exists and destroy it properly
                if ($.fn.DataTable.isDataTable('#timeToHireTable')) {
                    try {
                        $('#timeToHireTable').DataTable().destroy();
                    } catch (e) {
                        console.log('Error destroying existing DataTable:', e);
                    }
                }

                // Recreate table structure to ensure clean state
                var tableHtml = '<table id="timeToHireTable" class="table table-sm table-bordered table-striped">' +
                    '<thead id="tableHeaders">' +
                    '</thead>' +
                    '<tbody>' +
                    '</tbody>' +
                    '</table>';

                // Find the table container and replace it
                var tableContainer = $('#timeToHireTable').closest('.table-responsive');
                if (tableContainer.length > 0) {
                    tableContainer.html(tableHtml);
                } else {
                    $('#timeToHireTable').parent().html(tableHtml);
                }

                var columns = getColumnsForMetricType(currentMetricType);
                var orderColumn = getOrderColumnForMetricType(currentMetricType);

                console.log('Initializing table for metric type:', currentMetricType);
                console.log('Columns:', columns);

                // Update headers before initializing DataTable
                updateTableHeaders();

                table = $('#timeToHireTable').DataTable({
                    processing: true,
                    serverSide: true,
                    searching: false,
                    ajax: {
                        url: '{{ route('recruitment.reports.time-to-hire.data') }}',
                        type: 'GET',
                        data: function(d) {
                            d.date1 = $('input[name="date1"]').val();
                            d.date2 = $('input[name="date2"]').val();
                            d.department = $('select[name="department"]').val();
                            d.position = $('select[name="position"]').val();
                            d.project = $('select[name="project"]').val();
                            d.metric_type = $('select[name="metric_type"]').val();

                            console.log('AJAX data being sent:', {
                                metric_type: d.metric_type,
                                date1: d.date1,
                                date2: d.date2,
                                department: d.department,
                                position: d.position,
                                project: d.project
                            });
                        },
                        error: function(xhr, error, thrown) {
                            console.error('AJAX Error:', error, thrown);
                            console.error('Response:', xhr.responseText);
                        }
                    },
                    columns: columns,
                    responsive: true,
                    pageLength: 25,
                    order: [orderColumn, 'desc'],
                    language: {
                        processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span>'
                    }
                });
            }

            function getColumnsForMetricType(metricType) {
                if (metricType === 'candidate') {
                    return [{
                            data: 'candidate_name',
                            render: function(data, type, row) {
                                return '<strong>' + data + '</strong><br><small class="text-muted">' + (row
                                    .candidate_number || '') + '</small>';
                            }
                        },
                        {
                            data: 'request_no',
                            render: function(data, type, row) {
                                return '<a href="{{ route('recruitment.sessions.show', '') }}/' + row
                                    .request_id +
                                    '" target="_blank" title="View Request Details">' + data +
                                    '</a>';
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
                            data: 'session_created_at'
                        },
                        {
                            data: 'hiring_date'
                        },
                        {
                            data: 'time_to_hire_days',
                            render: function(data, type, row) {
                                var badgeClass = data <= 30 ? 'badge-success' : data <= 60 ?
                                    'badge-warning' : 'badge-danger';
                                return '<span class="badge ' + badgeClass + '">' + data + ' days</span>';
                            }
                        },
                        {
                            data: 'approval_days'
                        },
                        {
                            data: 'recruitment_days'
                        },
                        {
                            data: 'employment_type',
                            render: function(data, type, row) {
                                var badgeClass = data.toLowerCase() === 'regular' ? 'badge-primary' :
                                    'badge-info';
                                return '<span class="badge ' + badgeClass + '">' + data + '</span>';
                            }
                        },
                        {
                            data: 'status',
                            render: function(data, type, row) {
                                var badgeClass = data.toLowerCase() === 'approved' ? 'badge-success' :
                                    'badge-warning';
                                return '<span class="badge ' + badgeClass + '">' + data + '</span>';
                            }
                        }
                    ];
                } else {
                    return [{
                            data: 'request_no',
                            render: function(data, type, row) {
                                return '<a href="{{ route('recruitment.sessions.show', '') }}/' + row
                                    .fptk_id +
                                    '" target="_blank" title="View FPTK Details">' + data +
                                    '</a>';
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
                            data: 'fptk_created_at'
                        },
                        {
                            data: 'first_hiring_date'
                        },
                        {
                            data: 'time_to_fill_days',
                            render: function(data, type, row) {
                                var badgeClass = data <= 45 ? 'badge-success' : data <= 90 ?
                                    'badge-warning' : 'badge-danger';
                                return '<span class="badge ' + badgeClass + '">' + data + ' days</span>';
                            }
                        },
                        {
                            data: 'approval_days'
                        },
                        {
                            data: 'recruitment_days'
                        },
                        {
                            data: 'hired_count'
                        },
                        {
                            data: 'required_qty'
                        },
                        {
                            data: 'fill_rate',
                            render: function(data, type, row) {
                                var fillRate = parseFloat(data);
                                var badgeClass = fillRate >= 100 ? 'badge-success' : fillRate >= 50 ?
                                    'badge-warning' : 'badge-danger';
                                return '<span class="badge ' + badgeClass + '">' + data + '</span>';
                            }
                        },
                        {
                            data: 'employment_type',
                            render: function(data, type, row) {
                                var badgeClass = data.toLowerCase() === 'regular' ? 'badge-primary' :
                                    'badge-info';
                                return '<span class="badge ' + badgeClass + '">' + data + '</span>';
                            }
                        },
                        {
                            data: 'status',
                            render: function(data, type, row) {
                                var badgeClass = data.toLowerCase() === 'approved' ? 'badge-success' :
                                    'badge-warning';
                                return '<span class="badge ' + badgeClass + '">' + data + '</span>';
                            }
                        }
                    ];
                }
            }

            function getOrderColumnForMetricType(metricType) {
                return metricType === 'candidate' ? 6 : 6; // Time to Hire/Fill column
            }

            function updateMetricDescription(metricType) {
                var description = '';
                if (metricType === 'candidate') {
                    description =
                        '<strong>Time to Hire (Per Candidate):</strong> Waktu dari kandidat masuk proses recruitment hingga di-hire. Mengukur efisiensi proses recruitment untuk setiap kandidat individual.';
                } else {
                    description =
                        '<strong>Time to Fill (Per FPTK):</strong> Waktu dari FPTK dibuat hingga posisi pertama terisi. Mengukur efisiensi pengisian posisi untuk setiap permintaan recruitment.';
                }
                $('#metricDescription').html(description);
            }

            // Initialize table
            initializeTable();

            // Handle metric type change
            $('#metric_type').on('change', function() {
                currentMetricType = $(this).val();
                console.log('Metric type changed to:', currentMetricType);

                updateMetricDescription(currentMetricType);
                updateTableHeaders();

                // Reinitialize table immediately
                initializeTable();
            });

            function updateTableHeaders() {
                var headers = '';
                if (currentMetricType === 'candidate') {
                    headers = '<tr>' +
                        '<th class="align-middle">Candidate Name</th>' +
                        '<th class="align-middle">Request No</th>' +
                        '<th class="align-middle">Department</th>' +
                        '<th class="align-middle">Position</th>' +
                        '<th class="align-middle">Project</th>' +
                        '<th class="align-middle">Session Created</th>' +
                        '<th class="align-middle">Hiring Date</th>' +
                        '<th class="align-middle">Time to Hire (Days)</th>' +
                        '<th class="align-middle">Approval Days</th>' +
                        '<th class="align-middle">Recruitment Days</th>' +
                        '<th class="align-middle">Employment Type</th>' +
                        '<th class="align-middle">Status</th>' +
                        '</tr>';
                } else {
                    headers = '<tr>' +
                        '<th class="align-middle">Request No</th>' +
                        '<th class="align-middle">Department</th>' +
                        '<th class="align-middle">Position</th>' +
                        '<th class="align-middle">Project</th>' +
                        '<th class="align-middle">FPTK Created</th>' +
                        '<th class="align-middle">First Hiring Date</th>' +
                        '<th class="align-middle">Time to Fill (Days)</th>' +
                        '<th class="align-middle">Approval Days</th>' +
                        '<th class="align-middle">Recruitment Days</th>' +
                        '<th class="align-middle">Hired Count</th>' +
                        '<th class="align-middle">Required Qty</th>' +
                        '<th class="align-middle">Fill Rate</th>' +
                        '<th class="align-middle">Employment Type</th>' +
                        '<th class="align-middle">Status</th>' +
                        '</tr>';
                }
                $('#tableHeaders').html(headers);
                console.log('Table headers updated for:', currentMetricType);
            }

            // Refresh table when form is submitted
            $('#filterForm').on('submit', function(e) {
                e.preventDefault();
                if (table) {
                    table.ajax.reload();
                }
            });

            // Export Excel with current filters
            $('#exportExcelBtn').on('click', function() {
                var params = $.param({
                    date1: $('input[name="date1"]').val(),
                    date2: $('input[name="date2"]').val(),
                    department: $('select[name="department"]').val(),
                    position: $('select[name="position"]').val(),
                    project: $('select[name="project"]').val(),
                    metric_type: $('select[name="metric_type"]').val()
                });
                window.location.href = '{{ route('recruitment.reports.time-to-hire.export') }}' + '?' +
                    params;
            });
        });
    </script>
@endsection
