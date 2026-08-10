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
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                    <li class="breadcrumb-item active">PP Criteria</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">
                            <strong>{{ $subtitle }}</strong>
                        </h3>
                        <div class="card-tools">
                            @can('disciplinary-criteria.create')
                            <a class="btn btn-warning" data-toggle="modal" data-target="#add-criterion-modal">
                                <i class="fas fa-plus"></i> Add
                            </a>
                            @endcan
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-3">
                            <div class="col-md-3">
                                <select id="sanction-type-filter" class="form-control select2bs4">
                                    <option value="">All Sanction Types</option>
                                    @foreach ($sanctionTypes as $value => $label)
                                    <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-3">
                                <select id="active-filter" class="form-control select2bs4">
                                    <option value="">All Status</option>
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table id="criteria-table" width="100%" class="table table-sm table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th class="text-center">No</th>
                                        <th>Code</th>
                                        <th>Title</th>
                                        <th>Article</th>
                                        <th class="text-center">Sanction Type</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-center">Sort</th>
                                        <th class="text-center" width="15%">Action</th>
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

    {{-- Add Modal --}}
    <div class="modal fade" id="add-criterion-modal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Add PP Criterion</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ route('disciplinary-criteria.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="code">Code <span class="text-danger">*</span></label>
                                    <input type="text" name="code" id="code" class="form-control" required
                                        maxlength="50" placeholder="e.g. PP-22.5.A">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="sanction_type">Sanction Type <span class="text-danger">*</span></label>
                                    <select name="sanction_type" id="sanction_type" class="form-control select2bs4"
                                        required>
                                        <option value="">Select</option>
                                        @foreach ($sanctionTypes as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="sort_order">Sort Order</label>
                                    <input type="number" name="sort_order" id="sort_order" class="form-control"
                                        min="0" value="0">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="title">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title" class="form-control" required
                                maxlength="255">
                        </div>
                        <div class="form-group">
                            <label for="article_reference">Article Reference</label>
                            <input type="text" name="article_reference" id="article_reference"
                                class="form-control" placeholder="Pasal 22 ayat (6) huruf a">
                        </div>
                        <div class="form-group">
                            <label for="description">Description</label>
                            <textarea name="description" id="description" rows="4" class="form-control"></textarea>
                        </div>
                        <div class="form-check">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input"
                                id="is_active_add" checked>
                            <label class="form-check-label" for="is_active_add">Active</label>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div class="modal fade" id="edit-criterion-modal">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit PP Criterion</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="edit-criterion-form" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="code_edit">Code <span class="text-danger">*</span></label>
                                    <input type="text" name="code" id="code_edit" class="form-control" required
                                        maxlength="50">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="sanction_type_edit">Sanction Type <span
                                            class="text-danger">*</span></label>
                                    <select name="sanction_type" id="sanction_type_edit"
                                        class="form-control select2bs4" required>
                                        <option value="">Select</option>
                                        @foreach ($sanctionTypes as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="sort_order_edit">Sort Order</label>
                                    <input type="number" name="sort_order" id="sort_order_edit"
                                        class="form-control" min="0" value="0">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="title_edit">Title <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="title_edit" class="form-control" required
                                maxlength="255">
                        </div>
                        <div class="form-group">
                            <label for="article_reference_edit">Article Reference</label>
                            <input type="text" name="article_reference" id="article_reference_edit"
                                class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="description_edit">Description</label>
                            <textarea name="description" id="description_edit" rows="4" class="form-control"></textarea>
                        </div>
                        <div class="form-check">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" class="form-check-input"
                                id="is_active_edit">
                            <label class="form-check-label" for="is_active_edit">Active</label>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('scripts')
<script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
<script src="{{ asset('assets/plugins/jszip/jszip.min.js') }}"></script>
<script src="{{ asset('assets/plugins/pdfmake/pdfmake.min.js') }}"></script>
<script src="{{ asset('assets/plugins/pdfmake/vfs_fonts.js') }}"></script>
<script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>
<script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
<script>
    $(function() {
        $('.select2bs4').select2({
            theme: 'bootstrap4',
            width: '100%',
            allowClear: true
        });

        // Re-init select2 inside modals
        $('#add-criterion-modal, #edit-criterion-modal').on('shown.bs.modal', function() {
            $(this).find('.select2bs4').select2({
                theme: 'bootstrap4',
                width: '100%',
                dropdownParent: $(this)
            });
        });

        var table = $("#criteria-table").DataTable({
            responsive: true,
            autoWidth: true,
            lengthChange: true,
            lengthMenu: [
                [10, 25, 50, 100, -1],
                ['10', '25', '50', '100', 'Show all']
            ],
            dom: 'frtpi',
            buttons: ["copy", "csv", "excel", "pdf", "print", "colvis"],
            processing: true,
            serverSide: true,
            ajax: {
                url: "{{ route('disciplinary-criteria.data') }}",
                data: function(d) {
                    d.sanction_type = $('#sanction-type-filter').val();
                    d.is_active = $('#active-filter').val();
                    d.search = $("input[type=search][aria-controls=criteria-table]").val() || '';
                }
            },
            columns: [{
                    data: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                },
                {
                    data: 'code',
                    name: 'code'
                },
                {
                    data: 'title',
                    name: 'title'
                },
                {
                    data: 'article_reference',
                    name: 'article_reference',
                    defaultContent: '-'
                },
                {
                    data: 'sanction_type_label',
                    name: 'sanction_type',
                    className: 'text-center'
                },
                {
                    data: 'is_active',
                    name: 'is_active',
                    className: 'text-center'
                },
                {
                    data: 'sort_order',
                    name: 'sort_order',
                    className: 'text-center'
                },
                {
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                }
            ],
            order: [
                [0, 'desc']
            ]
        });

        $('#sanction-type-filter, #active-filter').on('change', function() {
            table.ajax.reload();
        });

        $('#edit-criterion-modal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var modal = $(this);
            var id = button.data('id');

            modal.find('#code_edit').val(button.data('code'));
            modal.find('#title_edit').val(button.data('title'));
            modal.find('#description_edit').val(button.data('description') || '');
            modal.find('#article_reference_edit').val(button.data('article') || '');
            modal.find('#sort_order_edit').val(button.data('sort-order') || 0);
            modal.find('#sanction_type_edit').val(button.data('sanction-type')).trigger('change');
            modal.find('#is_active_edit').prop('checked', button.data('status') == 1);

            var action = @json(url('disciplinary-criteria')) + '/' + encodeURIComponent(id);
            modal.find('#edit-criterion-form').attr('action', action);
        });
    });
</script>
@endsection