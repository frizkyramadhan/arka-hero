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
                        <a href="{{ route('recruitment.reports.funnel', request()->only('date1', 'date2', 'department', 'position', 'project')) }}"
                            class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Funnel
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
                    <h3 class="card-title">
                        <i class="fas fa-list"></i>
                        {{ ucwords(str_replace('_', ' ', $stage)) }} Stage Details
                    </h3>
                    <div class="card-tools">
                        <span class="badge badge-primary">{{ count($rows) }} Records</span>
                    </div>
                </div>
                <div class="card-body">
                    <table id="stageDetailTable" class="table table-sm table-bordered table-striped">
                        <thead>
                            <tr>
                                <th class="align-middle">FPTK Number</th>
                                <th class="align-middle">Department</th>
                                <th class="align-middle">Position</th>
                                <th class="align-middle">Project</th>
                                <th class="align-middle">Candidate</th>
                                <th class="align-middle">Session</th>
                                <th class="align-middle">Stage Date</th>
                                <th class="align-middle">Days</th>
                                @if ($stage === 'interview')
                                    <th class="align-middle">Type</th>
                                @endif
                                <th class="align-middle">Result</th>
                                <th class="align-middle">Remarks</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($rows as $row)
                                <tr>
                                    <td><strong>{{ $row['fptk_number'] }}</strong></td>
                                    <td>{{ $row['department'] }}</td>
                                    <td>{{ $row['position'] }}</td>
                                    <td>{{ $row['project'] }}</td>
                                    <td>
                                        <a href="{{ route('recruitment.sessions.candidate', $row['session_id']) }}"
                                            target="_blank" title="View Session Details">
                                            {{ $row['candidate_name'] }}
                                        </a><br>
                                        <small>{{ $row['candidate_number'] }}</small>
                                    </td>
                                    <td>{{ $row['session_number'] }}</td>
                                    <td>{{ $row['stage_date'] }}</td>
                                    <td>{{ $row['days_in_stage'] }} days</td>
                                    @if ($stage === 'interview')
                                        <td>{{ $row['interview_type'] ?: '-' }}</td>
                                    @endif
                                    <td>{{ ucfirst($row['result']) }}</td>
                                    <td>{{ $row['remarks'] }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $stage === 'interview' ? '11' : '10' }}" class="text-center">
                                        <div class="py-4">
                                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                            <h5 class="text-muted">No data found for this stage</h5>
                                            <p class="text-muted">Try adjusting your filters or check a different date
                                                range.</p>
                                        </div>
                                    </td>
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
            $('#stageDetailTable').DataTable({
                responsive: true,
                pageLength: 25,
                order: [
                    [6, 'desc']
                ], // Sort by Stage Date (descending)
                dom: 'Bfrtip',
                buttons: [
                    'copy',
                    'csv',
                    'excel',
                    {
                        extend: 'pdf',
                        orientation: 'landscape',
                        pageSize: 'A4',
                        title: 'Stage Detail Report - {{ ucwords(str_replace('_', ' ', $stage)) }}',
                        filename: 'stage_detail_{{ $stage }}_{{ date('Y-m-d') }}',
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
