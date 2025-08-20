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
    <!-- DataTables -->
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
        $(document).ready(function() {
            var table = $('#stageDetailTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route('recruitment.reports.stage-detail.data', $stage) }}',
                    type: 'GET',
                    data: function(d) {
                        d.date1 = '{{ $date1 }}';
                        d.date2 = '{{ $date2 }}';
                        d.department = '{{ $department }}';
                        d.position = '{{ $position }}';
                        d.project = '{{ $project }}';
                    }
                },
                columns: [{
                        data: 'fptk_number'
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
                                '" target="_blank" title="View FPTK Details">' + data +
                                '</a><br><small>' + (row.candidate_number || '') + '</small>';
                        }
                    },
                    {
                        data: 'session_number'
                    },
                    {
                        data: 'stage_date'
                    },
                    {
                        data: 'days_in_stage'
                    },
                    @if ($stage === 'interview')
                        {
                            data: 'interview_type'
                        },
                    @endif {
                        data: 'result',
                        render: function(data, type, row) {
                            var colorClass = data.toLowerCase().includes('pass') || data
                                .toLowerCase().includes('hired') || data.toLowerCase().includes(
                                    'complete') ? 'badge-success' :
                                (data.toLowerCase().includes('fail') || data.toLowerCase().includes(
                                    'rejected') ? 'badge-danger' : 'badge-warning');
                            return '<span class="badge ' + colorClass + '">' + data + '</span>';
                        }
                    },
                    {
                        data: 'remarks'
                    }
                ],
                responsive: true,
                pageLength: 25,
                order: [
                    [6, 'desc']
                ], // Sort by Stage Date by default
                dom: 'Bfrtip',
                buttons: [
                    'copy', 'csv', 'excel', 'print',
                    {
                        extend: 'pdf',
                        orientation: 'landscape',
                        pageSize: 'A4'
                    }
                ],
                language: {
                    processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span>'
                }
            });
        });
    </script>
@endpush
