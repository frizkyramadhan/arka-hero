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
                    <form method="GET" action="{{ route('recruitment.reports.offer-acceptance-rate') }}"
                        class="form-inline">
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
                        <a href="{{ route('recruitment.reports.offer-acceptance-rate') }}" class="btn btn-warning mr-2">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                        <a href="{{ route('recruitment.reports.offer-acceptance-rate.export', request()->only('date1', 'date2', 'department', 'position', 'project')) }}"
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
                                    <p>Total Offers</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-file-contract"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-success">
                                <div class="inner">
                                    <h3>{{ count($rows) > 0 ? count(array_filter($rows, fn($row) => strtolower($row['response']) === 'accepted')) : 0 }}
                                    </h3>
                                    <p>Accepted</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-danger">
                                <div class="inner">
                                    <h3>{{ count($rows) > 0 ? count(array_filter($rows, fn($row) => strtolower($row['response']) === 'rejected')) : 0 }}
                                    </h3>
                                    <p>Rejected</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-times-circle"></i>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-3 col-6">
                            <div class="small-box bg-warning">
                                <div class="inner">
                                    <h3>{{ count($rows) > 0 ? count(array_filter($rows, fn($row) => strtolower($row['response']) === 'pending')) : 0 }}
                                    </h3>
                                    <p>Pending</p>
                                </div>
                                <div class="icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table id="offerAcceptanceTable" class="table table-sm table-bordered table-striped">
                            <thead>
                                <tr>
                                    <th class="align-middle">Request No</th>
                                    <th class="align-middle">Department</th>
                                    <th class="align-middle">Position</th>
                                    <th class="align-middle">Project</th>
                                    <th class="align-middle">Candidate Name</th>
                                    <th class="align-middle">Offering Date</th>
                                    <th class="align-middle">Response Date</th>
                                    <th class="align-middle">Response Time (Days)</th>
                                    <th class="align-middle">Response</th>
                                    <th class="align-middle">Offering Letter No</th>
                                    <th class="align-middle">Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rows as $row)
                                    <tr>
                                        <td>
                                            <a href="{{ route('recruitment.sessions.candidate', $row['session_id'] ?? 0) }}"
                                                target="_blank" title="View Session Details">
                                                <strong>{{ $row['request_no'] }}</strong>
                                                <i class="fas fa-external-link-alt fa-xs ml-1"></i>
                                            </a>
                                        </td>
                                        <td>{{ $row['department'] }}</td>
                                        <td>{{ $row['position'] }}</td>
                                        <td>{{ $row['project'] }}</td>
                                        <td>
                                            <strong>{{ $row['candidate_name'] }}</strong>
                                        </td>
                                        <td data-order="{{ $row['offering_date_sort'] }}">{{ $row['offering_date'] }}</td>
                                        <td data-order="{{ $row['response_date_sort'] }}">{{ $row['response_date'] }}</td>
                                        <td class="text-center">
                                            @if ($row['response_time'] !== '-')
                                                @php
                                                    $timeClass = match (true) {
                                                        $row['response_time'] <= 3 => 'badge-success',
                                                        $row['response_time'] <= 7 => 'badge-warning',
                                                        default => 'badge-danger',
                                                    };
                                                @endphp
                                                <span class="badge {{ $timeClass }}">{{ $row['response_time'] }}
                                                    days</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $responseClass = match (strtolower($row['response'])) {
                                                    'accepted' => 'badge-success',
                                                    'rejected' => 'badge-danger',
                                                    'pending' => 'badge-warning',
                                                    default => 'badge-secondary',
                                                };
                                            @endphp
                                            <span class="badge {{ $responseClass }}">{{ $row['response'] }}</span>
                                        </td>
                                        <td>{{ $row['offering_letter_no'] }}</td>
                                        <td class="text-wrap">{{ $row['notes'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="11" class="text-center text-muted">
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
            $('#offerAcceptanceTable').DataTable({
                responsive: true,
                pageLength: 25,
                order: [
                    [5, 'desc']
                ], // Sort by Offering Date (descending)
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel',
                    {
                        extend: 'pdf',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        title: 'Offer Acceptance Rate Report',
                        filename: 'offer_acceptance_rate_{{ date('Y-m-d') }}',
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
                        targets: [0, 4, 7, 8],
                        orderable: false,
                        className: 'text-center'
                    },
                    {
                        targets: [10],
                        orderable: false,
                        className: 'text-wrap'
                    } // Notes column
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
