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
                        <li class="breadcrumb-item active">Grades</li>
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
                                <a class="btn btn-warning" data-toggle="modal" data-target="#add-grade-modal"><i
                                        class="fas fa-plus"></i> Add</a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="grades-table" width="100%" class="table table-sm table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th class="text-center">No</th>
                                            <th width="50%">Name</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center" width="15%">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Add Grade Modal --}}
        <div class="modal fade" id="add-grade-modal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Add Grade</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="add-grade-form" action="{{ route('grades.store') }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="name">Grade Name</label>
                                <input type="text" name="name" class="form-control" id="name" required>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" name="is_active" class="form-check-input" id="is_active_add" checked>
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

        {{-- Edit Grade Modal --}}
        <div class="modal fade" id="edit-grade-modal">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Edit Grade</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form id="edit-grade-form" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="name_edit">Grade Name</label>
                                <input type="text" name="name" class="form-control" id="name_edit" required>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" name="is_active" class="form-check-input" id="is_active_edit">
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
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
@endsection

@section('scripts')
    <!-- DataTables  & Plugins -->
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
    <script>
        $(function() {
            var table = $("#grades-table").DataTable({
                responsive: true,
                autoWidth: true,
                lengthChange: true,
                lengthMenu: [
                        [10, 25, 50, 100, -1],
                        ['10', '25', '50', '100', 'Show all']
                    ]
                    //, dom: 'lBfrtpi'
                    ,
                dom: 'frtpi',
                buttons: ["copy", "csv", "excel", "pdf", "print", "colvis"],
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('grades.data') }}",
                    data: function(d) {
                        d.search = $("input[type=search][aria-controls=grades-table]").val()
                        console.log(d);
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: "name",
                        name: "name"
                    },
                    {
                        data: "is_active",
                        name: "is_active",
                        className: "text-center"
                    },
                    {
                        data: "action",
                        name: "action",
                        orderable: false,
                        searchable: false,
                        className: "text-center"
                    }
                ],
                order: [
                    [0, 'desc']
                ]
            });

            $('#edit-grade-modal').on('show.bs.modal', function(event) {
                var button = $(event.relatedTarget);
                var gradeId = button.data('id');
                var gradeName = button.data('name');
                var gradeStatus = button.data('status');

                var modal = $(this);
                modal.find('#name_edit').val(gradeName);
                if (gradeStatus == 1) {
                    modal.find('#is_active_edit').prop('checked', true);
                } else {
                    modal.find('#is_active_edit').prop('checked', false);
                }

                var form = modal.find('#edit-grade-form');
                var action = '{{ route('grades.update', ':id') }}';
                action = action.replace(':id', gradeId);
                form.attr('action', action);
            });
        });
    </script>
@endsection
