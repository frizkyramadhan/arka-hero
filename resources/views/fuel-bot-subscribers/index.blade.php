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
                                @can('fuel-bot-subscribers.create')
                                <a class="btn btn-warning" data-toggle="modal" data-target="#modal-add">
                                    <i class="fas fa-plus"></i> Add
                                </a>
                                @endcan
                            </div>
                        </div>
                        <div class="card-body">
                            <p class="text-muted small mb-3">
                                Drivers kirim <code>/start</code> atau <code>/id</code> ke
                                <a href="https://t.me/ARKAHeroFuel_bot" target="_blank" rel="noopener">@ARKAHeroFuel_bot</a>
                                untuk mendapatkan User ID, lalu daftarkan di sini.
                            </p>

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
                                                    <label>Status</label>
                                                    <select class="form-control select2bs4" id="filter_status">
                                                        <option value="">- All -</option>
                                                        <option value="active">Active</option>
                                                        <option value="inactive">Inactive</option>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label>Search</label>
                                                    <input type="text" class="form-control" id="filter_q"
                                                        placeholder="User, email, Telegram ID, username"
                                                        autocomplete="off">
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
                                    </div>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table id="fuel-bot-subscribers-table" class="table table-bordered table-striped"
                                    width="100%">
                                    <thead>
                                        <tr>
                                            <th class="align-middle text-center" width="5%">No</th>
                                            <th class="align-middle">User</th>
                                            <th class="align-middle">Telegram User ID</th>
                                            <th class="align-middle">Username</th>
                                            <th class="align-middle">Notes</th>
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

        @can('fuel-bot-subscribers.create')
        <div class="modal fade" id="modal-add">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <form action="{{ route('fuel-bot-subscribers.store') }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h4 class="modal-title">Add Subscriber</h4>
                            <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                        </div>
                        <div class="modal-body">
                            @include('fuel-bot-subscribers._form-fields', [
                                'model' => null,
                                'users' => $users,
                                'prefix' => 'add_',
                            ])
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

        var table = $('#fuel-bot-subscribers-table').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            dom: 'rtip',
            ajax: {
                url: '{{ route('fuel-bot-subscribers.data') }}',
                data: function(d) {
                    d.status = $('#filter_status').val();
                    d.q = $('#filter_q').val();
                }
            },
            columns: [
                { data: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                { data: 'user_label', name: 'user_id' },
                { data: 'telegram_user_id' },
                { data: 'username_label', orderable: false, searchable: false },
                { data: 'notes_short', orderable: false, searchable: false },
                { data: 'status_badge', orderable: false, searchable: false, className: 'text-center' },
                { data: 'action', orderable: false, searchable: false, className: 'text-center' }
            ],
            order: [[2, 'desc']]
        });

        var filterTextTimer;
        $('#filter_status').on('change', function() { table.ajax.reload(); });
        $('#filter_q').on('keyup', function() {
            clearTimeout(filterTextTimer);
            filterTextTimer = setTimeout(function() { table.ajax.reload(); }, 450);
        });
        $('#btn-reset-filter').on('click', function() {
            $('#filter_status').val('').trigger('change');
            $('#filter_q').val('');
            table.ajax.reload();
        });
    });

    function editFuelBotSubscriber(id) {
        $('#modal-edit-' + id).modal('show');
    }
</script>
@endsection
