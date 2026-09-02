@extends('layouts.main')

@section('title', $title)

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
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
                        <li class="breadcrumb-item">Supplies</li>
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
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">{{ $subtitle }}</h3>
                            <div class="card-tools">
                                @can('supplies.item-categories.create')
                                    <a class="btn btn-warning" data-toggle="modal" data-target="#modal-add">
                                        <i class="fas fa-plus"></i> Add
                                    </a>
                                @endcan
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="item-categories-table" class="table table-bordered table-striped" width="100%">
                                    <thead>
                                        <tr>
                                            <th class="align-middle text-center" width="5%">No</th>
                                            <th class="align-middle">Name</th>
                                            <th class="align-middle text-center">Prefix</th>
                                            <th class="align-middle">Description</th>
                                            <th class="align-middle text-center">Items</th>
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

            @can('supplies.item-categories.create')
                <div class="modal fade" id="modal-add">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <form action="{{ route('supplies.item-categories.store') }}" method="POST">
                                @csrf
                                <div class="modal-header">
                                    <h4 class="modal-title">Add Item Category</h4>
                                    <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
                                </div>
                                <div class="modal-body">
                                    @include('supplies.categories._form-fields', ['model' => null])
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
                    $el.select2({ theme: 'bootstrap4', width: '100%', dropdownParent: $modal });
                });
            });

            $('#item-categories-table').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                dom: 'rtip',
                ajax: "{{ route('supplies.item-categories.data') }}",
                columns: [
                    { data: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'name' },
                    { data: 'prefix', className: 'text-center' },
                    { data: 'description', defaultContent: '—' },
                    { data: 'items_count', className: 'text-center' },
                    { data: 'status_badge', className: 'text-center', orderable: false },
                    { data: 'action', orderable: false, searchable: false, className: 'text-center' }
                ]
            });
        });

        function editItemCategory(id) {
            $('#modal-edit-' + id).modal('show');
        }
    </script>
@endsection
