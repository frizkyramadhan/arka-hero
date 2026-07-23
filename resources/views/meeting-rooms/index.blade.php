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
                                    @can('meeting-rooms.create')
                                        <a class="btn btn-warning" data-toggle="modal" data-target="#modal-add">
                                            <i class="fas fa-plus"></i> Add
                                        </a>
                                    @endcan
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
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Project</label>
                                                        <select class="form-control select2bs4" id="filter_project">
                                                            <option value="">- All -</option>
                                                            @foreach ($projects as $project)
                                                                <option value="{{ $project->id }}">{{ $project->project_code }} - {{ $project->project_name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label>Status</label>
                                                        <select class="form-control select2bs4" id="filter_status">
                                                            <option value="">- All -</option>
                                                            <option value="active">Active</option>
                                                            <option value="inactive">Inactive</option>
                                                            <option value="maintenance">Maintenance</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-4">
                                                    <div class="form-group">
                                                        <label>Room / Facilities</label>
                                                        <input type="text" class="form-control" id="filter_q" placeholder="Room name or facilities" autocomplete="off">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>&nbsp;</label>
                                                        <button type="button" class="btn btn-secondary w-100" id="btn-reset-filter">
                                                            <i class="fas fa-times"></i> Reset
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table id="meeting-rooms-table" class="table table-bordered table-striped" width="100%">
                                        <thead>
                                            <tr>
                                                <th class="align-middle text-center" width="5%">No</th>
                                                <th class="align-middle">Room Name</th>
                                                <th class="align-middle">Location (Project)</th>
                                                <th class="align-middle text-center">Capacity</th>
                                                <th class="align-middle">Facilities</th>
                                                <th class="align-middle text-center" width="10%">Status</th>
                                                <th class="align-middle text-center" width="10%">Action</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            @can('meeting-rooms.create')
                <div class="modal fade" id="modal-add">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <form action="{{ route('meeting-rooms.store') }}" method="POST">
                                @csrf
                                <div class="modal-header">
                                    <h4 class="modal-title">Add Meeting Room</h4>
                                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                </div>
                                <div class="modal-body">
                                    @include('meeting-rooms._form-fields', ['model' => null])
                                </div>
                                <div class="modal-footer justify-content-between">
                                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary">Submit</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            @endcan
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

            // Select2 inside Bootstrap modals
            $(document).on('shown.bs.modal', '.modal', function() {
                var $modal = $(this);
                $modal.find('select.select2bs4').each(function() {
                    var $el = $(this);
                    if ($el.hasClass('select2-hidden-accessible')) {
                        $el.select2('destroy');
                    }
                    $el.select2({
                        theme: 'bootstrap4',
                        width: '100%',
                        dropdownParent: $modal
                    });
                });
            });

            const table = $('#meeting-rooms-table').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                dom: 'rtip',
                ajax: {
                    url: "{{ route('meeting-rooms.data') }}",
                    data: function(d) {
                        d.project_id = $('#filter_project').val();
                        d.status = $('#filter_status').val();
                        d.q = $('#filter_q').val();
                    }
                },
                columns: [
                    { data: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'room_name' },
                    { data: 'project_label', orderable: false },
                    { data: 'capacity', defaultContent: '—', className: 'text-center' },
                    { data: 'facilities', defaultContent: '—', orderable: false },
                    { data: 'status_badge', className: 'text-center', orderable: false },
                    { data: 'action', orderable: false, searchable: false, className: 'text-center' }
                ]
            });

            var filterTextTimer;
            $('#filter_project, #filter_status').on('change', function() { table.ajax.reload(); });
            $('#filter_q').on('keyup', function() {
                clearTimeout(filterTextTimer);
                filterTextTimer = setTimeout(function() { table.ajax.reload(); }, 450);
            });
            $('#btn-reset-filter').on('click', function() {
                $('#filter_project, #filter_status').val('').trigger('change');
                $('#filter_q').val('');
                table.ajax.reload();
            });
        });

        function editMeetingRoom(id) {
            $('#modal-edit-' + id).modal('show');
        }
    </script>
@endsection
