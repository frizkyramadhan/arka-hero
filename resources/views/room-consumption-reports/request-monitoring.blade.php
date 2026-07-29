@extends('layouts.main')

@section('title', $title ?? 'Report Room & Consumption Requests')

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col">
                    <h1 class="m-0 font-weight-bold">
                        <i class="fas fa-door-open text-warning mr-2"></i>
                        {{ $title }}
                    </h1>
                    <nav aria-label="breadcrumb" class="mt-1">
                        <ol class="breadcrumb mb-0 py-0 bg-transparent small">
                            <li class="breadcrumb-item"><a href="{{ route('room-consumption-requests.reports.index') }}">Reports</a></li>
                            <li class="breadcrumb-item active">Requests</li>
                        </ol>
                    </nav>
                </div>
                <div class="col-auto">
                    <a href="{{ route('room-consumption-requests.reports.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Reports
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card card-outline card-warning shadow-sm mb-4">
                <div class="card-header py-3 bg-white border-bottom">
                    <h5 class="card-title mb-0 text-dark">
                        <i class="fas fa-filter text-warning mr-2"></i>Filter Options
                    </h5>
                </div>
                <div class="card-body">
                    @php
                        $statusChoice = isset($filters['status']) && (string) $filters['status'] !== '' ? (string) $filters['status'] : null;
                        $projectChoice = isset($filters['project_id']) && (string) $filters['project_id'] !== '' ? (string) $filters['project_id'] : null;
                    @endphp
                    <form id="filterForm" onsubmit="return false">
                        <div class="row align-items-end">
                            <div class="col-md-6 col-lg-2 form-group mb-lg-0">
                                <label class="small font-weight-bold text-muted">Status</label>
                                <select class="form-control select2bs4" name="status" id="status" style="width: 100%;">
                                    <option value="" {{ $statusChoice === null ? 'selected' : '' }}>Select status</option>
                                    <option value="all" {{ $statusChoice === 'all' ? 'selected' : '' }}>All status</option>
                                    @foreach (['draft','submitted','approved','rejected','cancelled','completed'] as $st)
                                        <option value="{{ $st }}" {{ $statusChoice === $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-3 form-group mb-lg-0">
                                <label class="small font-weight-bold text-muted">Project</label>
                                <select class="form-control select2bs4" name="project_id" id="project_id" style="width: 100%;">
                                    <option value="" {{ $projectChoice === null ? 'selected' : '' }}>Select project</option>
                                    <option value="all" {{ $projectChoice === 'all' ? 'selected' : '' }}>All projects</option>
                                    @foreach ($projects as $p)
                                        <option value="{{ $p->id }}" {{ $projectChoice !== null && $projectChoice !== 'all' && (string) $p->id === $projectChoice ? 'selected' : '' }}>
                                            {{ $p->project_code }} - {{ $p->project_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-2 form-group mb-lg-0">
                                <label class="small font-weight-bold text-muted">Meeting from</label>
                                <input type="date" class="form-control" name="date_from" id="date_from" value="{{ $filters['date_from'] ?? '' }}">
                            </div>
                            <div class="col-md-6 col-lg-2 form-group mb-lg-0">
                                <label class="small font-weight-bold text-muted">Meeting to</label>
                                <input type="date" class="form-control" name="date_to" id="date_to" value="{{ $filters['date_to'] ?? '' }}">
                            </div>
                            <div class="col-md-6 col-lg-3 form-group mb-lg-0">
                                <label class="small font-weight-bold text-muted">Reg. No</label>
                                <input type="text" class="form-control" name="request_number" id="request_number" value="{{ $filters['request_number'] ?? '' }}" placeholder="e.g. 0001/HCS-">
                            </div>
                        </div>
                        <div class="row align-items-end mt-2">
                            <div class="col-md-4 form-group mb-lg-0">
                                <label class="small font-weight-bold text-muted">Requester</label>
                                <input type="text" class="form-control" id="requester_q" name="requester_q" value="{{ $filters['requester_q'] ?? '' }}" placeholder="Name (partial)">
                            </div>
                            <div class="col-md-4 form-group mb-lg-0">
                                <label class="small font-weight-bold text-muted">Room</label>
                                <input type="text" class="form-control" id="room_q" name="room_q" value="{{ $filters['room_q'] ?? '' }}" placeholder="Room name">
                            </div>
                            <div class="col-md-4 form-group mb-lg-0">
                                <label class="small font-weight-bold text-muted">Meeting title</label>
                                <input type="text" class="form-control" id="title_q" name="title_q" value="{{ $filters['title_q'] ?? '' }}" placeholder="Title">
                            </div>
                        </div>
                        <div class="row mt-3 pt-3 border-top">
                            <div class="col-12 d-flex flex-wrap align-items-center">
                                <button type="button" id="btn-show-data" class="btn btn-warning mr-2 mb-1">
                                    <i class="fas fa-search mr-1"></i> Tampilkan data
                                </button>
                                <a href="{{ route('room-consumption-requests.reports.request-monitoring') }}" class="btn btn-outline-secondary mb-1 mr-2">
                                    <i class="fas fa-undo mr-1"></i> Reset
                                </a>
                                <a href="#" id="btn-export-excel" class="btn btn-success btn-sm mb-1">
                                    <i class="fas fa-file-excel mr-1"></i> Export to Excel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="rcr-report-table" class="table table-bordered table-striped" width="100%">
                            <thead>
                                <tr>
                                    <th class="text-center" width="5%">No</th>
                                    <th>Reg. No</th>
                                    <th>Project</th>
                                    <th>Room</th>
                                    <th>Title</th>
                                    <th>Meeting Dates</th>
                                    <th>Created At</th>
                                    <th class="text-center">Target</th>
                                    <th>Time</th>
                                    <th class="text-center">Status</th>
                                    <th>Requester</th>
                                    <th class="text-center" width="8%">Actions</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $(function() {
            $('.select2bs4').select2({ theme: 'bootstrap4', width: '100%' });

            var table = $('#rcr-report-table').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                dom: 'rtip',
                ajax: {
                    url: "{{ route('room-consumption-requests.reports.request-monitoring.data') }}",
                    data: function(d) {
                        d.status = $('#status').val();
                        d.project_id = $('#project_id').val();
                        d.date_from = $('#date_from').val();
                        d.date_to = $('#date_to').val();
                        d.request_number = $('#request_number').val();
                        d.requester_q = $('#requester_q').val();
                        d.room_q = $('#room_q').val();
                        d.title_q = $('#title_q').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'request_number', orderable: false },
                    { data: 'project_label', orderable: false },
                    { data: 'room_name', orderable: false },
                    { data: 'meeting_title', orderable: false },
                    { data: 'meeting_date_fmt', className: 'text-center' },
                    { data: 'created_at_fmt', className: 'text-center' },
                    { data: 'target_days', orderable: false, className: 'text-center' },
                    { data: 'time_range', orderable: false, className: 'text-center' },
                    { data: 'status_badge', orderable: false, className: 'text-center' },
                    { data: 'requester', orderable: false },
                    { data: 'actions', orderable: false, searchable: false, className: 'text-center' }
                ]
            });

            function hasFilter() {
                return $('#status').val() || $('#project_id').val() || $('#date_from').val() || $('#date_to').val()
                    || $('#request_number').val() || $('#requester_q').val() || $('#room_q').val() || $('#title_q').val();
            }

            $('#btn-show-data').on('click', function() {
                if (!hasFilter()) {
                    alert('Please apply at least one filter before loading data.');
                    return;
                }
                table.ajax.reload();
            });

            $('#btn-export-excel').on('click', function(e) {
                e.preventDefault();
                if (!hasFilter()) {
                    alert('Please apply at least one filter before exporting.');
                    return;
                }
                var params = $.param({
                    status: $('#status').val(),
                    project_id: $('#project_id').val(),
                    date_from: $('#date_from').val(),
                    date_to: $('#date_to').val(),
                    request_number: $('#request_number').val(),
                    requester_q: $('#requester_q').val(),
                    room_q: $('#room_q').val(),
                    title_q: $('#title_q').val()
                });
                window.location = "{{ route('room-consumption-requests.reports.request-monitoring.export') }}?" + params;
            });
        });
    </script>
@endsection
