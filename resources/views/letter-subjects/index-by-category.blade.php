@extends('layouts.main')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $title }}</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="#">Master Data</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('letter-categories.index') }}">Letter Categories</a>
                        </li>
                        <li class="breadcrumb-item active">{{ $category->category_name }} Subjects</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Left col -->
                <div class="col-lg-12">
                    <!-- Custom tabs (Charts with tabs)-->
                    <div id="accordion">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">
                                    <strong>{{ $subtitle }}</strong>
                                </h3>
                                <div class="card-tools">
                                    <ul class="nav nav-pills ml-auto">
                                        <li class="nav-item mr-2">
                                            <a class="btn btn-secondary" href="{{ route('letter-categories.index') }}">
                                                <i class="fas fa-arrow-left"></i> Back to Categories
                                            </a>
                                            <a class="btn btn-warning" data-toggle="modal" data-target="#modal-add"><i
                                                    class="fas fa-plus"></i>
                                                Add Subject</a>
                                        </li>
                                    </ul>
                                </div>
                            </div><!-- /.card-header -->
                            <div class="card-body">
                                <!-- Category Info -->
                                <div class="alert alert-info">
                                    <h5><i class="icon fas fa-info"></i> Category Information</h5>
                                    <strong>Code:</strong> {{ $category->category_code }} |
                                    <strong>Name:</strong> {{ $category->category_name }} |
                                    <strong>Description:</strong> {{ $category->description ?: 'No description' }}
                                </div>



                                <div class="table-responsive">
                                    <table id="subjects-table" width="100%"
                                        class="table table-sm table-bordered table-striped">
                                        <thead>
                                            <tr>
                                                <th class="text-center">No</th>
                                                <th>Subject Name</th>
                                                <th class="text-center">Status</th>
                                                <th class="text-center">Created By</th>
                                                <th class="text-center">Created At</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                        </tbody>
                                    </table>
                                </div>
                            </div><!-- /.card-body -->
                        </div>
                    </div>
                    <!-- /.card -->
                </div>
                <!-- right col -->
            </div>
            <!-- /.row (main row) -->
        </div>

        <!-- Add Modal -->
        <div class="modal fade" id="modal-add">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title">Add Letter Subject</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <form class="form-horizontal" id="form-add-subject">
                        <div class="modal-body">
                            @csrf
                            <div class="card-body">
                                <div class="tab-content p-0">
                                    <div class="form-group row">
                                        <label class="col-sm-3 col-form-label">Category</label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control"
                                                value="{{ $category->category_name }} ({{ $category->category_code }})"
                                                readonly>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-3 col-form-label">Subject Name <span
                                                class="text-danger">*</span></label>
                                        <div class="col-sm-9">
                                            <input type="text" class="form-control" name="subject_name"
                                                placeholder="e.g., Surat Perjalanan Dinas" maxlength="200" required>
                                            <div class="invalid-feedback" id="error-subject_name"></div>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-sm-3 col-form-label">Status <span
                                                class="text-danger">*</span></label>
                                        <div class="col-sm-9">
                                            <select name="is_active" class="form-control" required>
                                                <option value="1">Active</option>
                                                <option value="0">Inactive</option>
                                            </select>
                                            <div class="invalid-feedback" id="error-is_active"></div>
                                        </div>
                                    </div>
                                </div>
                            </div><!-- /.card-body -->
                        </div>
                        <div class="modal-footer justify-content-between">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Submit</button>
                        </div>
                    </form>
                </div>
                <!-- /.modal-content -->
            </div>
            <!-- /.modal-dialog -->
        </div>
        <!-- /.modal -->
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
    <script src="{{ asset('vendor/sweetalert/sweetalert.all.js') }}"></script>

    <!-- Page specific script -->
    <script>
        $(function() {
            var table = $("#subjects-table").DataTable({
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
                    url: "{{ route('letter-subjects.data-by-category', $category->id) }}",
                    data: function(d) {
                        d.search = $("input[type=search][aria-controls=subjects-table]").val();
                    }
                },
                columns: [{
                    data: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                }, {
                    data: "subject_name",
                    name: "subject_name",
                    orderable: true
                }, {
                    data: "is_active",
                    name: "is_active",
                    orderable: false,
                    className: "text-center"
                }, {
                    data: "created_by",
                    name: "created_by",
                    orderable: false,
                    className: "text-center"
                }, {
                    data: "created_at",
                    name: "created_at",
                    orderable: true,
                    className: "text-center"
                }, {
                    data: "action",
                    name: "action",
                    orderable: false,
                    searchable: false,
                    className: "text-center"
                }],
                fixedColumns: true,
            });

            // Add subject form submission
            $('#form-add-subject').on('submit', function(e) {
                e.preventDefault();

                // Clear previous errors
                $('.form-control').removeClass('is-invalid');
                $('.invalid-feedback').text('');

                var formData = new FormData(this);

                $.ajax({
                    url: "{{ route('letter-subjects.store-by-category', $category->id) }}",
                    type: 'POST',
                    data: formData,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message,
                                confirmButtonColor: '#3085d6'
                            });
                            $('#modal-add').modal('hide');
                            $('#form-add-subject')[0].reset();
                            table.draw();
                        }
                    },
                    error: function(xhr) {
                        if (xhr.status === 422) {
                            var errors = xhr.responseJSON.errors;
                            $.each(errors, function(key, value) {
                                $('input[name="' + key + '"], select[name="' + key +
                                    '"]').addClass('is-invalid');
                                $('#error-' + key).text(value[0]);
                            });
                        } else {
                            var response = xhr.responseJSON;
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message ||
                                    'An error occurred while adding subject',
                                confirmButtonColor: '#3085d6'
                            });
                        }
                    }
                });
            });

            // Reset form when modal is hidden
            $('#modal-add').on('hidden.bs.modal', function() {
                $('#form-add-subject')[0].reset();
                $('.form-control').removeClass('is-invalid');
                $('.invalid-feedback').text('');
            });
        });

        // Global function for delete action
        function deleteSubject(id, subjectName) {
            Swal.fire({
                title: 'Confirm Delete',
                text: 'Are you sure you want to delete subject "' + subjectName + '"?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, Delete!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route('letter-subjects.destroy', ':id') }}'.replace(':id', id),
                        type: 'POST',
                        data: {
                            _method: 'DELETE',
                            _token: $('meta[name="csrf-token"]').attr('content')
                        },
                        dataType: 'json',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            'Accept': 'application/json',
                            'Content-Type': 'application/x-www-form-urlencoded'
                        },
                        success: function(response) {
                            if (response && response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Success',
                                    text: response.message,
                                    confirmButtonColor: '#3085d6'
                                });
                                $('#subjects-table').DataTable().draw();
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message || 'Unexpected response format',
                                    confirmButtonColor: '#3085d6'
                                });
                            }
                        },
                        error: function(xhr) {
                            var response = xhr.responseJSON;
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: (response && response.message) ||
                                    'An error occurred while deleting subject',
                                confirmButtonColor: '#3085d6'
                            });
                        }
                    });
                }
            });
        }

        // Global function for update action
        function updateSubject(id, subjectName, isActive) {
            // Fill the edit form and show modal
            var form = $('#form-edit-subject-' + id);
            form.find('input[name="subject_name"]').val(subjectName);
            form.find('select[name="is_active"]').val(isActive);
            $('#modal-edit-' + id).modal('show');
        }
    </script>
@endsection
