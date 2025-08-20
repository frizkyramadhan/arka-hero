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
                    <form method="GET" action="{{ route('recruitment.reports.interview-assessment-analytics') }}"
                        class="row" id="filterForm">
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
                            <a href="{{ route('recruitment.reports.interview-assessment-analytics') }}"
                                class="btn btn-warning mr-2">
                                <i class="fas fa-undo"></i> Reset
                            </a>
                            <button type="button" id="exportExcelBtn" class="btn btn-success mr-2">
                                <i class="fas fa-file-excel"></i> Export Excel
                            </button>
                            <button type="button" class="btn btn-info" data-toggle="modal"
                                data-target="#scoringIndexModal">
                                <i class="fas fa-calculator"></i> Scoring Index
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="interviewAssessmentTable" class="table table-sm table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="align-middle">Request No</th>
                                    <th class="align-middle">Department</th>
                                    <th class="align-middle">Position</th>
                                    <th class="align-middle">Project</th>
                                    <th class="align-middle">Candidate Name</th>
                                    <th class="align-middle">Psikotes Result</th>
                                    <th class="align-middle">Tes Teori Result</th>
                                    <th class="align-middle">Interview Result</th>
                                    <th class="align-middle">Overall Assessment</th>
                                    <th class="align-middle">Notes</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Scoring Index Modal -->
    <div class="modal fade" id="scoringIndexModal" tabindex="-1" role="dialog" aria-labelledby="scoringIndexModalLabel"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="scoringIndexModalLabel">
                        <i class="fas fa-calculator"></i> Scoring Index - Overall Assessment Rules
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row mb-3">
                        <div class="col-12">
                            <h6 class="text-primary">Scoring System</h6>
                            <div class="row text-center">
                                <div class="col-md-3">
                                    <div class="border rounded p-3 bg-success text-white">
                                        <h5>2 Points</h5>
                                        <p class="mb-0">Pass / Recommended</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-3 bg-warning text-dark">
                                        <h5>1 Point</h5>
                                        <p class="mb-0">Pending / Average</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-3 bg-danger text-white">
                                        <h5>0 Points</h5>
                                        <p class="mb-0">Fail / Not Recommended</p>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="border rounded p-3 bg-secondary text-white">
                                        <h5>-</h5>
                                        <p class="mb-0">No Data</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <h6 class="text-success">Assessment Rules</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-bordered table-striped">
                                    <thead class="table-dark">
                                        <tr>
                                            <th class="text-center">Psikotes</th>
                                            <th class="text-center">Tes Teori (Optional)</th>
                                            <th class="text-center">Interview HR</th>
                                            <th class="text-center">Interview User</th>
                                            <th class="text-center">Total Score</th>
                                            <th class="text-center">Overall Result</th>
                                            <th class="text-center">Color</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Excellent (Green Rows - 4 entries) -->
                                        <tr class="table-success">
                                            <td class="text-center">2</td>
                                            <td class="text-center">2</td>
                                            <td class="text-center">2</td>
                                            <td class="text-center">2</td>
                                            <td class="text-center"><strong>8</strong></td>
                                            <td class="text-center"><span class="badge badge-success">Excellent</span>
                                            </td>
                                            <td class="text-center"><span class="badge badge-success">Green</span></td>
                                        </tr>
                                        <tr class="table-success">
                                            <td class="text-center">2</td>
                                            <td class="text-center">2</td>
                                            <td class="text-center">2</td>
                                            <td class="text-center">1</td>
                                            <td class="text-center"><strong>7</strong></td>
                                            <td class="text-center"><span class="badge badge-success">Excellent</span>
                                            </td>
                                            <td class="text-center"><span class="badge badge-success">Green</span></td>
                                        </tr>
                                        <tr class="table-success">
                                            <td class="text-center">2</td>
                                            <td class="text-center">2</td>
                                            <td class="text-center">1</td>
                                            <td class="text-center">2</td>
                                            <td class="text-center"><strong>7</strong></td>
                                            <td class="text-center"><span class="badge badge-success">Excellent</span>
                                            </td>
                                            <td class="text-center"><span class="badge badge-success">Green</span></td>
                                        </tr>
                                        <tr class="table-success">
                                            <td class="text-center">2</td>
                                            <td class="text-center">1</td>
                                            <td class="text-center">2</td>
                                            <td class="text-center">2</td>
                                            <td class="text-center"><strong>7</strong></td>
                                            <td class="text-center"><span class="badge badge-success">Excellent</span>
                                            </td>
                                            <td class="text-center"><span class="badge badge-success">Green</span></td>
                                        </tr>

                                        <!-- Good (Blue Rows - 8 entries) -->
                                        <tr class="table-info">
                                            <td class="text-center">2</td>
                                            <td class="text-center">2</td>
                                            <td class="text-center">2</td>
                                            <td class="text-center">0</td>
                                            <td class="text-center"><strong>6</strong></td>
                                            <td class="text-center"><span class="badge badge-info">Good</span></td>
                                            <td class="text-center"><span class="badge badge-info">Blue</span></td>
                                        </tr>
                                        <tr class="table-info">
                                            <td class="text-center">2</td>
                                            <td class="text-center">2</td>
                                            <td class="text-center">0</td>
                                            <td class="text-center">2</td>
                                            <td class="text-center"><strong>6</strong></td>
                                            <td class="text-center"><span class="badge badge-info">Good</span></td>
                                            <td class="text-center"><span class="badge badge-info">Blue</span></td>
                                        </tr>
                                        <tr class="table-info">
                                            <td class="text-center">2</td>
                                            <td class="text-center">2</td>
                                            <td class="text-center">1</td>
                                            <td class="text-center">1</td>
                                            <td class="text-center"><strong>6</strong></td>
                                            <td class="text-center"><span class="badge badge-info">Good</span></td>
                                            <td class="text-center"><span class="badge badge-info">Blue</span></td>
                                        </tr>
                                        <tr class="table-info">
                                            <td class="text-center">2</td>
                                            <td class="text-center">1</td>
                                            <td class="text-center">2</td>
                                            <td class="text-center">1</td>
                                            <td class="text-center"><strong>6</strong></td>
                                            <td class="text-center"><span class="badge badge-info">Good</span></td>
                                            <td class="text-center"><span class="badge badge-info">Blue</span></td>
                                        </tr>
                                        <tr class="table-info">
                                            <td class="text-center">2</td>
                                            <td class="text-center">1</td>
                                            <td class="text-center">1</td>
                                            <td class="text-center">2</td>
                                            <td class="text-center"><strong>6</strong></td>
                                            <td class="text-center"><span class="badge badge-info">Good</span></td>
                                            <td class="text-center"><span class="badge badge-info">Blue</span></td>
                                        </tr>
                                        <tr class="table-info">
                                            <td class="text-center">2</td>
                                            <td class="text-center">1</td>
                                            <td class="text-center">2</td>
                                            <td class="text-center">0</td>
                                            <td class="text-center"><strong>5</strong></td>
                                            <td class="text-center"><span class="badge badge-info">Good</span></td>
                                            <td class="text-center"><span class="badge badge-info">Blue</span></td>
                                        </tr>
                                        <tr class="table-info">
                                            <td class="text-center">2</td>
                                            <td class="text-center">1</td>
                                            <td class="text-center">0</td>
                                            <td class="text-center">2</td>
                                            <td class="text-center"><strong>5</strong></td>
                                            <td class="text-center"><span class="badge badge-info">Good</span></td>
                                            <td class="text-center"><span class="badge badge-info">Blue</span></td>
                                        </tr>
                                        <tr class="table-info">
                                            <td class="text-center">2</td>
                                            <td class="text-center">1</td>
                                            <td class="text-center">1</td>
                                            <td class="text-center">1</td>
                                            <td class="text-center"><strong>5</strong></td>
                                            <td class="text-center"><span class="badge badge-info">Good</span></td>
                                            <td class="text-center"><span class="badge badge-info">Blue</span></td>
                                        </tr>

                                        <!-- Average (Yellow Rows - 2 entries) -->
                                        <tr class="table-warning">
                                            <td class="text-center">2</td>
                                            <td class="text-center">2</td>
                                            <td class="text-center">0</td>
                                            <td class="text-center">0</td>
                                            <td class="text-center"><strong>4</strong></td>
                                            <td class="text-center"><span class="badge badge-warning">Average</span></td>
                                            <td class="text-center"><span class="badge badge-warning">Yellow</span></td>
                                        </tr>
                                        <tr class="table-warning">
                                            <td class="text-center">1</td>
                                            <td class="text-center">1</td>
                                            <td class="text-center">1</td>
                                            <td class="text-center">1</td>
                                            <td class="text-center"><strong>4</strong></td>
                                            <td class="text-center"><span class="badge badge-warning">Average</span></td>
                                            <td class="text-center"><span class="badge badge-warning">Yellow</span></td>
                                        </tr>

                                        <!-- Poor (Orange/Red Rows - 3 entries) -->
                                        <tr class="table-danger">
                                            <td class="text-center">2</td>
                                            <td class="text-center">1</td>
                                            <td class="text-center">0</td>
                                            <td class="text-center">0</td>
                                            <td class="text-center"><strong>3</strong></td>
                                            <td class="text-center"><span class="badge badge-danger">Poor</span></td>
                                            <td class="text-center"><span class="badge badge-danger">Red</span></td>
                                        </tr>
                                        <tr class="table-danger">
                                            <td class="text-center">2</td>
                                            <td class="text-center">0</td>
                                            <td class="text-center">0</td>
                                            <td class="text-center">0</td>
                                            <td class="text-center"><strong>2</strong></td>
                                            <td class="text-center"><span class="badge badge-danger">Poor</span></td>
                                            <td class="text-center"><span class="badge badge-danger">Red</span></td>
                                        </tr>
                                        <tr class="table-danger">
                                            <td class="text-center">0</td>
                                            <td class="text-center">0</td>
                                            <td class="text-center">0</td>
                                            <td class="text-center">0</td>
                                            <td class="text-center"><strong>0</strong></td>
                                            <td class="text-center"><span class="badge badge-danger">Poor</span></td>
                                            <td class="text-center"><span class="badge badge-danger">Red</span></td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="row mt-3">
                        <div class="col-12">
                            <h6 class="text-info">Key Rules</h6>
                            <div class="alert alert-info">
                                <ul class="mb-0">
                                    <li><strong>Psikotes Fail (0):</strong> Automatically results in <span
                                            class="badge badge-danger">Poor</span> regardless of other scores</li>
                                    <li><strong>Excellent (7-8):</strong> Psikotes Pass (2) + High scores in other stages
                                    </li>
                                    <li><strong>Good (5-6):</strong> Psikotes Pass (2) + Moderate scores in other stages
                                    </li>
                                    <li><strong>Average (4):</strong> Psikotes Pass (2) + Low scores, or Psikotes Pending
                                        (1) + Balanced scores</li>
                                    <li><strong>Poor (0-3):</strong> Psikotes Fail (0) or very low combined scores</li>
                                    <li><strong>Total Combinations:</strong> 17 different scoring combinations</li>
                                    <li><strong>Distribution:</strong> 4 Excellent, 8 Good, 2 Average, 3 Poor</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
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
            var table = $('#interviewAssessmentTable').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ajax: {
                    url: '{{ route('recruitment.reports.interview-assessment-analytics.data') }}',
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
                        data: 'request_no'
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
                        data: 'candidate_name',
                        render: function(data, type, row) {
                            return '<a href="{{ route('recruitment.sessions.candidate', '') }}/' +
                                row.session_id +
                                '" target="_blank" title="View Request Details">' + data +
                                '</a>';
                        }
                    },
                    {
                        data: 'psikotes_result',
                        render: function(data, type, row) {
                            // Extract result and score from combined data
                            var result = data;
                            var score = '';
                            if (data.includes('(')) {
                                var parts = data.split('(');
                                result = parts[0].trim();
                                score = '(' + parts[1];
                            }

                            var colorClass = result.toLowerCase() === 'pass' ? 'badge-success' :
                                (result.toLowerCase() === 'fail' ? 'badge-danger' :
                                    'badge-warning');

                            var html = '<span class="badge ' + colorClass + '">' + result +
                                '</span>';
                            if (score) {
                                html += '<br><small class="text-muted">' + score + '</small>';
                            }
                            return html;
                        }
                    },
                    {
                        data: 'tes_teori_result',
                        render: function(data, type, row) {
                            // Extract result and score from combined data
                            var result = data;
                            var score = '';
                            if (data.includes('(')) {
                                var parts = data.split('(');
                                result = parts[0].trim();
                                score = '(' + parts[1];
                            }

                            var colorClass = result.toLowerCase() === 'pass' ? 'badge-success' :
                                (result.toLowerCase() === 'fail' ? 'badge-danger' :
                                    'badge-warning');

                            var html = '<span class="badge ' + colorClass + '">' + result +
                                '</span>';
                            if (score) {
                                html += '<br><small class="text-muted">' + score + '</small>';
                            }
                            return html;
                        }
                    },
                    {
                        data: 'interview_result',
                        render: function(data, type, row) {
                            // Extract type and result from combined data
                            var type = '';
                            var result = data;

                            if (data.includes(' - ')) {
                                var parts = data.split(' - ');
                                type = parts[0];
                                result = parts[1];
                            } else if (data.includes('HR') || data.includes('User')) {
                                type = data;
                                result = '';
                            }

                            var html = '';
                            if (type) {
                                html += '<small class="text-muted">' + type + '</small><br>';
                            }

                            if (result) {
                                var colorClass = result.toLowerCase().includes('pass') || result
                                    .toLowerCase().includes('recommended') ? 'badge-success' :
                                    (result.toLowerCase().includes('fail') || result.toLowerCase()
                                        .includes('not') ? 'badge-danger' : 'badge-warning');
                                html += '<span class="badge ' + colorClass + '">' + result +
                                    '</span>';
                            }

                            return html || '-';
                        }
                    },
                    {
                        data: 'overall_assessment',
                        render: function(data, type, row) {
                            var colorClass = data === 'Excellent' ? 'badge-success' :
                                (data === 'Good' ? 'badge-info' :
                                    (data === 'Average' ? 'badge-warning' : 'badge-danger'));
                            return '<span class="badge ' + colorClass + '">' + data + '</span>';
                        }
                    },
                    {
                        data: 'notes'
                    }
                ],
                responsive: true,
                pageLength: 25,
                order: [
                    [0, 'asc']
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
                    position: $('select[name="position"]').val(),
                    project: $('select[name="project"]').val()
                });
                window.location.href =
                    '{{ route('recruitment.reports.interview-assessment-analytics.export') }}' + '?' +
                    params;
            });
        });
    </script>
@endpush
