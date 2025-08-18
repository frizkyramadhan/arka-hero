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
                    <form method="GET" action="{{ route('recruitment.reports.time-to-hire') }}" class="form-inline">
                        <div class="form-group mr-2">
                            <label class="mr-2">Date From</label>
                            <input type="date" name="date1" class="form-control" value="{{ $date1 }}">
                        </div>
                        <div class="form-group mr-2">
                            <label class="mr-2">Date To</label>
                            <input type="date" name="date2" class="form-control" value="{{ $date2 }}">
                        </div>
                        <div class="form-group mr-2">
                            <label class="mr-2">Department</label>
                            <select name="department" class="form-control">
                                <option value="">All Departments</option>
                                @foreach ($departments as $dept)
                                    <option value="{{ $dept->id }}" {{ $department == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->department_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mr-2">
                            <label class="mr-2">Position</label>
                            <select name="position" class="form-control">
                                <option value="">All Positions</option>
                                @foreach ($positions as $pos)
                                    <option value="{{ $pos->id }}" {{ $position == $pos->id ? 'selected' : '' }}>
                                        {{ $pos->position_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mr-2">
                            <label class="mr-2">Project</label>
                            <select name="project" class="form-control">
                                <option value="">All Projects</option>
                                @foreach ($projects as $proj)
                                    <option value="{{ $proj->id }}" {{ $project == $proj->id ? 'selected' : '' }}>
                                        {{ $proj->project_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary mr-2">Filter</button>
                        <a href="{{ route('recruitment.reports.time-to-hire') }}" class="btn btn-warning mr-2">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                        <a href="{{ route('recruitment.reports.time-to-hire.export', request()->only('date1', 'date2', 'department', 'position', 'project')) }}"
                            class="btn btn-success">
                            <i class="fas fa-file-excel"></i> Export Excel
                        </a>
                    </form>
                </div>

                <div class="card-body">
                    <!-- Report Summary -->
                    <div class="row">
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-info">
                                <div class="inner">
                                    <h3>{{ count($rows) }}</h3>
                                    <p>Total Hired</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-user-check"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>{{ count($rows) > 0 ? round(collect($rows)->avg('total_days'), 1) : 0 }}</h3>
                                    <p>Avg Total Days</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>{{ count($rows) > 0 ? round(collect($rows)->avg('approval_days'), 1) : 0 }}</h3>
                                    <p>Avg Approval Days</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-thumbs-up"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3>{{ count($rows) > 0 ? round(collect($rows)->avg('recruitment_days'), 1) : 0 }}</h3>
                                    <p>Avg Recruitment Days</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="timeToHireTable" class="table table-sm table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="align-middle">Request No</th>
                                    <th class="align-middle">Department</th>
                                    <th class="align-middle">Position</th>
                                    <th class="align-middle">Project</th>
                                    <th class="align-middle">Requested At</th>
                                    <th class="align-middle">Hiring Date</th>
                                    <th class="align-middle">Total Days</th>
                                    <th class="align-middle">Approval Days</th>
                                    <th class="align-middle">Recruitment Days</th>
                                    <th class="align-middle">Status</th>
                                    <th class="align-middle">Latest Approval</th>
                                    <th class="align-middle">Approval Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rows as $row)
                                    <tr>
                                        <td>
                                            <a href="{{ route('recruitment.sessions.candidate', $row['session_id'] ?? 0) }}"
                                                target="_blank" title="View Session Details">
                                                <strong>{{ $row['request_no'] }}</strong>
                                            </a>
                                        </td>
                                        <td>{{ $row['department'] }}</td>
                                        <td>{{ $row['position'] }}</td>
                                        <td>{{ $row['project'] }}</td>
                                        <td
                                            data-order="{{ \Carbon\Carbon::parse($row['requested_at'])->format('Y-m-d H:i:s') }}">
                                            {{ \Carbon\Carbon::parse($row['requested_at'])->format('d/m/Y H:i') }}
                                        </td>
                                        <td data-order="{{ \Carbon\Carbon::parse($row['hiring_date'])->format('Y-m-d') }}">
                                            {{ \Carbon\Carbon::parse($row['hiring_date'])->format('d/m/Y') }}
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $totalDaysClass = match (true) {
                                                    $row['total_days'] <= 30 => 'badge-success',
                                                    $row['total_days'] <= 60 => 'badge-warning',
                                                    default => 'badge-danger',
                                                };
                                            @endphp
                                            <span class="badge {{ $totalDaysClass }}">{{ $row['total_days'] }}
                                                days</span>
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $approvalDaysClass = match (true) {
                                                    $row['approval_days'] <= 7 => 'badge-success',
                                                    $row['approval_days'] <= 14 => 'badge-warning',
                                                    default => 'badge-danger',
                                                };
                                            @endphp
                                            <span class="badge {{ $approvalDaysClass }}">{{ $row['approval_days'] }}
                                                days</span>
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $recruitmentDaysClass = match (true) {
                                                    $row['recruitment_days'] <= 30 => 'badge-success',
                                                    $row['recruitment_days'] <= 60 => 'badge-warning',
                                                    default => 'badge-danger',
                                                };
                                            @endphp
                                            <span
                                                class="badge {{ $recruitmentDaysClass }}">{{ $row['recruitment_days'] }}
                                                days</span>
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $statusClass = match (strtolower($row['status'])) {
                                                    'approved', 'completed' => 'badge-success',
                                                    'pending', 'in_progress' => 'badge-warning',
                                                    'rejected', 'cancelled' => 'badge-danger',
                                                    default => 'badge-secondary',
                                                };
                                            @endphp
                                            <span class="badge {{ $statusClass }}">{{ $row['status'] }}</span>
                                        </td>
                                        <td>{{ $row['latest_approval'] }}</td>
                                        <td class="text-wrap">{{ $row['remarks'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="12" class="text-center text-muted">
                                            <i class="fas fa-info-circle"></i> No data found for the selected criteria
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <!-- DataTables  & Plugins -->
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

    <script>
        $(function() {
            $('#timeToHireTable').DataTable({
                responsive: true,
                pageLength: 25,
                order: [
                    [5, 'desc']
                ], // Sort by Hiring Date (descending)
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel',
                    {
                        extend: 'pdf',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        title: 'Time-to-Hire Analysis Report',
                        filename: 'time_to_hire_{{ date('Y-m-d') }}',
                        customize: function(doc) {
                            doc.defaultStyle.fontSize = 8;
                            doc.styles.tableHeader.fontSize = 9;
                            doc.styles.tableHeader.fillColor = '#f8f9fa';
                            doc.content[1].table.widths = Array(doc.content[1].table.body[0]
                                .length + 1).join('*').split('');
                            doc.pageMargins = [20, 20, 20, 20];
                        }
                    },
                    'print'
                ],
                columnDefs: [{
                        targets: [6, 7, 8, 9],
                        orderable: false,
                        className: 'text-center'
                    },
                    {
                        targets: [11],
                        orderable: false,
                        className: 'text-wrap'
                    }
                ]
            });
        });
    </script>
@endpush

@push('styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
@endpush
