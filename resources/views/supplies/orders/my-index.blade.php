@extends('layouts.main')

@section('title', $title)

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
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
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">{{ $subtitle }}</h3>
                    <div class="card-tools">
                        @can('personal.supplies.orders.create-own')
                            <a href="{{ route('supplies.orders.my-orders.create') }}" class="btn btn-warning">
                                <i class="fas fa-plus"></i> Add
                            </a>
                        @endcan
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="my-orders-table" class="table table-bordered table-striped" width="100%">
                            <thead>
                                <tr>
                                    <th class="align-middle text-center" width="5%">No</th>
                                    <th class="align-middle">Order No</th>
                                    <th class="align-middle">Project</th>
                                    <th class="align-middle">Date</th>
                                    <th class="align-middle">Department</th>
                                    <th class="align-middle text-center">Status</th>
                                    <th class="align-middle text-center" width="10%">Action</th>
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
    <script>
        $(function() {
            $('#my-orders-table').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                dom: 'rtip',
                ajax: "{{ route('supplies.orders.my-orders.data') }}",
                columns: [
                    { data: 'DT_RowIndex', orderable: false, className: 'text-center' },
                    { data: 'order_number' },
                    { data: 'project_label', orderable: false },
                    { data: 'order_date', defaultContent: '—' },
                    { data: 'department_label', orderable: false },
                    { data: 'status_badge', className: 'text-center', orderable: false },
                    { data: 'action', orderable: false, className: 'text-center' }
                ]
            });
        });
    </script>
@endsection
