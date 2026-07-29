@extends('layouts.main')

@section('title', $title)

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $title }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item active">{{ $title }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div id="accordion">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">{{ $subtitle }}</h3>
                                <div class="card-tools">
                                    @if ($isPersonal)
                                        @can('personal.room-consumption.create-own')
                                            <a href="{{ route('room-consumption-requests.my-requests.create') }}"
                                                class="btn btn-warning">
                                                <i class="fas fa-plus"></i> Add
                                            </a>
                                        @endcan
                                    @else
                                        @can('room-consumption-requests.create')
                                            <a href="{{ route('room-consumption-requests.create') }}" class="btn btn-warning">
                                                <i class="fas fa-plus"></i> Add
                                            </a>
                                        @endcan
                                    @endif
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="card card-primary">
                                    <div class="card-header">
                                        <h4 class="card-title w-100">
                                            <a class="d-block w-100" data-toggle="collapse" href="#collapseFilter">
                                                <i class="fas fa-filter"></i> Filter
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="collapseFilter" class="collapse" data-parent="#accordion">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label>Status</label>
                                                        <select class="form-control select2bs4" id="filter_status">
                                                            <option value="">- All -</option>
                                                            @unless ($isPersonal)
                                                                <option value="pending_hr">Menunggu Konfirmasi HR</option>
                                                            @endunless
                                                            @foreach (['draft', 'submitted', 'approved', 'rejected', 'cancelled', 'completed'] as $st)
                                                                <option value="{{ $st }}">{{ ucfirst($st) }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Project</label>
                                                        <select class="form-control select2bs4" id="filter_project_id">
                                                            <option value="">- All -</option>
                                                            @foreach ($projects as $p)
                                                                <option value="{{ $p->id }}">{{ $p->project_code }}
                                                                    - {{ $p->project_name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label>Meeting from</label>
                                                        <input type="date" class="form-control" id="filter_date_from">
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label>Meeting to</label>
                                                        <input type="date" class="form-control" id="filter_date_to">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>&nbsp;</label>
                                                        <button type="button" class="btn btn-secondary w-100"
                                                            id="btn-reset-filter">
                                                            <i class="fas fa-times"></i> Reset
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Reg. No / Title</label>
                                                        <input type="text" class="form-control" id="filter_q"
                                                            placeholder="Reg. No or meeting title" autocomplete="off">
                                                    </div>
                                                </div>
                                                @unless ($isPersonal)
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label>Requester</label>
                                                            <input type="text" class="form-control" id="filter_requester_q"
                                                                placeholder="Name (partial)" autocomplete="off">
                                                        </div>
                                                    </div>
                                                @endunless
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Room</label>
                                                        <input type="text" class="form-control" id="filter_room_q"
                                                            placeholder="Room name" autocomplete="off">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table id="rcr-table" class="table table-bordered table-striped" width="100%">
                                        <thead>
                                            <tr>
                                                <th class="align-middle text-center" width="5%">No</th>
                                                <th class="align-middle text-nowrap">Reg. No</th>
                                                <th class="align-middle">Project</th>
                                                <th class="align-middle">Room</th>
                                                <th class="align-middle">Meeting Dates</th>
                                                <th class="align-middle">Time</th>
                                                <th class="align-middle text-center">Status</th>
                                                @unless ($isPersonal)
                                                    <th class="align-middle">Requester</th>
                                                @endunless
                                                <th class="align-middle text-center" width="12%">Actions</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            const isPersonal = @json($isPersonal);
            $('.select2bs4').select2({
                theme: 'bootstrap4',
                width: '100%'
            });

            const columns = [{
                    data: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                },
                {
                    data: 'request_number',
                    orderable: false,
                    className: 'text-nowrap'
                },
                {
                    data: 'project_label',
                    orderable: false
                },
                {
                    data: 'room_name',
                    orderable: false
                },
                {
                    data: 'meeting_date_fmt',
                    className: 'align-middle'
                },
                {
                    data: 'time_range',
                    orderable: false,
                    className: 'text-center'
                },
                {
                    data: 'status_badge',
                    orderable: false,
                    className: 'text-center'
                },
            ];
            if (!isPersonal) {
                columns.push({
                    data: 'requester',
                    orderable: false
                });
            }
            columns.push({
                data: 'actions',
                orderable: false,
                searchable: false,
                className: 'text-center'
            });

            var table = $('#rcr-table').DataTable({
                responsive: true,
                processing: true,
                serverSide: true,
                searching: false,
                dom: 'rtip',
                ajax: {
                    url: isPersonal ?
                        "{{ route('room-consumption-requests.my-requests.data') }}" :
                        "{{ route('room-consumption-requests.data') }}",
                    data: function(d) {
                        d.status = $('#filter_status').val();
                        d.project_id = $('#filter_project_id').val();
                        d.date_from = $('#filter_date_from').val();
                        d.date_to = $('#filter_date_to').val();
                        d.q = $('#filter_q').val();
                        d.requester_q = $('#filter_requester_q').val();
                        d.room_q = $('#filter_room_q').val();
                    }
                },
                columns: columns,
                order: [
                    [4, 'desc']
                ]
            });

            var filterTextTimer;
            $('#filter_status, #filter_project_id').on('change', function() {
                table.draw();
            });
            $('#filter_date_from, #filter_date_to').on('change', function() {
                table.draw();
            });
            $('#filter_q, #filter_requester_q, #filter_room_q').on('keyup', function() {
                clearTimeout(filterTextTimer);
                filterTextTimer = setTimeout(function() {
                    table.draw();
                }, 450);
            });
            $('#btn-reset-filter').on('click', function() {
                $('#filter_status, #filter_project_id').val('').trigger('change');
                $('#filter_date_from, #filter_date_to, #filter_q, #filter_requester_q, #filter_room_q').val(
                    '');
                table.draw();
            });
        });
    </script>
@endsection
