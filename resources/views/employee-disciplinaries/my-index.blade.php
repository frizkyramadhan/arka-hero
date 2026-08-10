@extends('layouts.main')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $title }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard.personal') }}">My Dashboard</a></li>
                        <li class="breadcrumb-item active">My Disciplinary Record</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-lg-12">
                    <div id="accordion">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title mb-0">
                                    <strong>{{ $subtitle }}</strong>
                                </h3>
                            </div>
                            <div class="card-body">
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    You can view your coaching, counseling, and warning letter (SP) records. Create, edit,
                                    and delete are managed by HR.
                                </div>

                                <div class="card card-primary">
                                    <div class="card-header">
                                        <h4 class="card-title w-100">
                                            <a class="d-block w-100" data-toggle="collapse" href="#filterMyDisciplinary">
                                                <i class="fas fa-filter"></i> Filter
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="filterMyDisciplinary" class="collapse" data-parent="#accordion">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Status</label>
                                                        <select class="form-control select2bs4" id="status-filter">
                                                            <option value="">All Status</option>
                                                            <option value="active">Active</option>
                                                            <option value="expired">Expired</option>
                                                            <option value="superseded">Superseded</option>
                                                            <option value="terminated">Terminated</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Type</label>
                                                        <select class="form-control select2bs4" id="type-filter">
                                                            <option value="">All Types</option>
                                                            @foreach ($typeOptions as $value => $label)
                                                                <option value="{{ $value }}">{{ $label }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>PP Criteria</label>
                                                        <select class="form-control select2bs4" id="criterion-filter">
                                                            <option value="">All PP Criteria</option>
                                                            @foreach ($criteria as $criterion)
                                                                <option value="{{ $criterion->id }}">
                                                                    {{ $criterion->display_label }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Date From</label>
                                                        <input type="date" class="form-control" id="date_from">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>Date To</label>
                                                        <input type="date" class="form-control" id="date_to">
                                                    </div>
                                                </div>
                                                <div class="col-md-3">
                                                    <div class="form-group">
                                                        <label>&nbsp;</label>
                                                        <button type="button" id="btn-reset"
                                                            class="btn btn-danger btn-block">
                                                            <i class="fas fa-times"></i> Reset Filter
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="table-responsive">
                                    <table id="my-disciplinary-table" width="100%"
                                        class="table table-sm table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th class="text-center">No</th>
                                                <th class="text-center">Type</th>
                                                <th>PP Criteria</th>
                                                <th class="text-center">Effective Date</th>
                                                <th class="text-center">Valid Until</th>
                                                <th class="text-center">Status</th>
                                                <th class="text-center">Remaining</th>
                                                <th class="text-center" width="10%">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
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

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('scripts')
    <script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $(function() {
            $('.select2bs4').select2({
                theme: 'bootstrap4',
                placeholder: 'Select an option',
                allowClear: true,
                width: '100%'
            });

            var table = $("#my-disciplinary-table").DataTable({
                responsive: true,
                autoWidth: true,
                searching: false,
                dom: 'rtip',
                processing: true,
                serverSide: true,
                ajax: {
                    url: @json(route('employee-disciplinaries.my-records.data')),
                    data: function(d) {
                        d.status = $('#status-filter').val();
                        d.type = $('#type-filter').val();
                        d.criterion_id = $('#criterion-filter').val();
                        d.date_from = $('#date_from').val();
                        d.date_to = $('#date_to').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'type_label',
                        className: 'text-center'
                    },
                    {
                        data: 'criteria_list',
                        orderable: false
                    },
                    {
                        data: 'effective_date_fmt',
                        className: 'text-center'
                    },
                    {
                        data: 'end_date_fmt',
                        className: 'text-center'
                    },
                    {
                        data: 'status_badge',
                        className: 'text-center',
                        orderable: false
                    },
                    {
                        data: 'remaining_days',
                        className: 'text-center',
                        orderable: false
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ],
                order: []
            });

            $('#status-filter, #type-filter, #criterion-filter, #date_from, #date_to').on('change', function() {
                table.ajax.reload();
            });

            $('#btn-reset').on('click', function() {
                $('#status-filter, #type-filter, #criterion-filter').val('').trigger('change');
                $('#date_from, #date_to').val('');
                table.ajax.reload();
            });
        });
    </script>
@endsection
