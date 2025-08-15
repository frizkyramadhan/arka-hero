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
                    <form method="GET" action="{{ route('recruitment.reports.aging') }}" class="form-inline">
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
                                @foreach (\App\Models\Department::orderBy('department_name')->get() as $dept)
                                    <option value="{{ $dept->id }}" {{ $department == $dept->id ? 'selected' : '' }}>
                                        {{ $dept->department_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mr-2">
                            <label class="mr-2">Project</label>
                            <select name="project" class="form-control">
                                <option value="">All Projects</option>
                                @foreach (\App\Models\Project::where('project_status', 1)->orderBy('project_name')->get() as $proj)
                                    <option value="{{ $proj->id }}" {{ $project == $proj->id ? 'selected' : '' }}>
                                        {{ $proj->project_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mr-2">
                            <label class="mr-2">Status</label>
                            <select name="status" class="form-control">
                                <option value="">All Status</option>
                                <option value="draft" {{ $status == 'draft' ? 'selected' : '' }}>Draft</option>
                                <option value="submitted" {{ $status == 'submitted' ? 'selected' : '' }}>Submitted</option>
                                <option value="approved" {{ $status == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ $status == 'rejected' ? 'selected' : '' }}>Rejected</option>
                                <option value="closed" {{ $status == 'closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                        </div>
                        <button type="submit" class="btn btn-primary mr-2">Filter</button>
                        <a href="{{ route('recruitment.reports.aging') }}" class="btn btn-warning mr-2">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                        <a href="{{ route('recruitment.reports.aging.export', request()->only('date1', 'date2', 'department', 'project', 'status')) }}"
                            class="btn btn-success">
                            <i class="fas fa-file-excel"></i> Export Excel
                        </a>
                    </form>
                </div>
                <div class="card-body">
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
                                <th class="align-middle">Approval Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rows as $row)
                                <tr>
                                    <td>
                                        <a href="{{ route('recruitment.requests.show', $row['request_id']) }}"
                                            target="_blank" title="View Request Details">
                                            <strong>{{ $row['request_no'] }}</strong>
                                        </a>
                                    </td>
                                    <td>{{ $row['department'] }}</td>
                                    <td>{{ $row['position'] }}</td>
                                    <td>{{ $row['project'] }}</td>
                                    <td>{{ $row['requested_by'] }}</td>
                                    <td data-order="{{ $row['requested_at_sort'] }}">{{ $row['requested_at'] }}</td>
                                    <td>
                                        @php
                                            $statusClass = match ($row['status']) {
                                                'Draft' => 'badge-warning',
                                                'Submitted' => 'badge-info',
                                                'Approved' => 'badge-success',
                                                'Rejected' => 'badge-danger',
                                                'Closed' => 'badge-secondary',
                                                default => 'badge-light',
                                            };
                                        @endphp
                                        <span class="badge {{ $statusClass }}">{{ $row['status'] }}</span>
                                    </td>
                                    <td>
                                        @php
                                            $daysClass =
                                                $row['days_open'] > 30
                                                    ? 'text-danger'
                                                    : ($row['days_open'] > 14
                                                        ? 'text-warning'
                                                        : 'text-success');
                                        @endphp
                                        <span class="{{ $daysClass }}">{{ $row['days_open'] }}</span>
                                    </td>
                                    <td>{{ $row['latest_approval'] }}</td>
                                    <td data-order="{{ $row['approved_at_sort'] }}">{{ $row['approved_at'] }}</td>
                                    <td>
                                        @if ($row['days_to_approve'] !== '-')
                                            @php
                                                $approvalClass =
                                                    $row['days_to_approve'] > 14
                                                        ? 'text-danger'
                                                        : ($row['days_to_approve'] > 7
                                                            ? 'text-warning'
                                                            : 'text-success');
                                            @endphp
                                            <span class="{{ $approvalClass }}">{{ $row['days_to_approve'] }}</span>
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>{{ $row['remarks'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="12" class="text-center">No data found</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('styles')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
@endpush

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
            $('#agingTable').DataTable({
                responsive: true,
                pageLength: 10,
                order: [
                    [5, 'desc']
                ], // Sort by Requested At (created_at) descending
                dom: 'Bfrtip',
                buttons: [
                    'copy',
                    'csv',
                    'excel',
                    {
                        extend: 'pdf',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        title: 'Request Aging & SLA Report',
                        filename: 'recruitment_aging_{{ date('Y-m-d') }}',
                        customize: function(doc) {
                            // Set margins
                            doc.pageMargins = [20, 20, 20, 20];

                            // Style the title
                            doc.content[1].table.headerRows = 1;
                            doc.content[1].table.widths = Array(doc.content[1].table.body[0]
                                .length + 1).join('*').split('');

                            // Make header bold
                            if (doc.content[1].table.body[0]) {
                                doc.content[1].table.body[0].forEach(function(cell) {
                                    cell.style = 'tableHeader';
                                    cell.fillColor = '#f0f0f0';
                                });
                            }

                            // Set font size for better readability
                            doc.defaultStyle.fontSize = 8;
                            doc.styles.tableHeader = {
                                bold: true,
                                fontSize: 9,
                                color: 'black',
                                fillColor: '#f0f0f0'
                            };
                        }
                    },
                    'print'
                ]
            });
        });
    </script>
@endpush
