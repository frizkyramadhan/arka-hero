@extends('layouts.main')

@section('title', $title)

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $title }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">National Holidays</li>
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
                            <h3 class="card-title"><strong>National Holidays</strong></h3>
                            <div class="card-tools">
                                @can('national-holidays.create')
                                    <button type="button" class="btn btn-primary" data-toggle="modal"
                                        data-target="#modalHolidayCreate">
                                        <i class="fas fa-plus"></i> Add
                                    </button>
                                @endcan
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="national-holidays-table" class="table table-sm table-bordered table-striped"
                                    width="100%">
                                    <thead>
                                        <tr>
                                            <th class="text-center" style="width: 60px;">No</th>
                                            <th>Date</th>
                                            <th>Name</th>
                                            @if ($showActionColumn)
                                                <th class="text-center" style="width: 140px;">Actions</th>
                                            @endif
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

        @can('national-holidays.create')
            <div class="modal fade" id="modalHolidayCreate" tabindex="-1" role="dialog"
                aria-labelledby="modalHolidayCreateLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalHolidayCreateLabel">Add National Holiday</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form method="post" action="{{ route('leave.national-holidays.store') }}">
                            @csrf
                            <input type="hidden" name="_form" value="create">
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="create_holiday_date">Date <span class="text-danger">*</span></label>
                                    <input type="date" name="holiday_date" id="create_holiday_date"
                                        class="form-control @error('holiday_date') is-invalid @enderror"
                                        value="{{ old('_form') === 'create' ? old('holiday_date') : '' }}" required>
                                    @error('holiday_date')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group mb-0">
                                    <label for="create_name">Name / description</label>
                                    <input type="text" name="name" id="create_name" class="form-control"
                                        value="{{ old('_form') === 'create' ? old('name') : '' }}"
                                        placeholder="e.g. Independence Day">
                                    @error('name')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                            <div class="modal-footer justify-content-between">
                                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                <button type="submit" class="btn btn-primary">Save</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endcan

        @can('national-holidays.edit')
            <div class="modal fade" id="modalHolidayEdit" tabindex="-1" role="dialog"
                aria-labelledby="modalHolidayEditLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalHolidayEditLabel">Edit National Holiday</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <form id="formHolidayEdit" method="post" action="">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="_form" value="edit">
                            <input type="hidden" name="edit_update_url" id="edit_update_url"
                                value="{{ old('edit_update_url') }}">
                            <div class="modal-body">
                                <div class="form-group">
                                    <label for="edit_holiday_date">Date <span class="text-danger">*</span></label>
                                    <input type="date" name="holiday_date" id="edit_holiday_date"
                                        class="form-control @error('holiday_date') is-invalid @enderror" required>
                                    @error('holiday_date')
                                        <span class="invalid-feedback d-block">{{ $message }}</span>
                                    @enderror
                                </div>
                                <div class="form-group mb-0">
                                    <label for="edit_name">Name / description</label>
                                    <input type="text" name="name" id="edit_name" class="form-control"
                                        placeholder="e.g. Independence Day">
                                    @error('name')
                                        <span class="text-danger small">{{ $message }}</span>
                                    @enderror
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
        @endcan
    </section>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
@endsection

@push('late-scripts')
    <script>
        $(function() {
            var showActions = @json($showActionColumn);

            var columns = [{
                    data: 'DT_RowIndex',
                    name: 'DT_RowIndex',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                },
                {
                    data: 'holiday_date',
                    name: 'holiday_date',
                    className: 'text-left'
                },
                {
                    data: 'name',
                    name: 'name',
                    orderable: false
                }
            ];

            if (showActions) {
                columns.push({
                    data: 'action',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    className: 'text-center'
                });
            }

            $('#national-holidays-table').DataTable({
                responsive: true,
                autoWidth: true,
                processing: true,
                serverSide: true,
                order: [
                    [1, 'desc']
                ],
                dom: 'frtip',
                ajax: {
                    url: "{{ route('leave.national-holidays.data') }}"
                },
                columns: columns
            });

            @if ($errors->any() && old('_form') === 'create')
                $('#modalHolidayCreate').modal('show');
            @endif

            @if ($errors->any() && old('_form') === 'edit')
                $('#formHolidayEdit').attr('action', @json(old('edit_update_url', '')));
                $('#edit_update_url').val(@json(old('edit_update_url', '')));
                $('#edit_holiday_date').val(@json(old('holiday_date', '')));
                $('#edit_name').val(@json(old('name', '')));
                $('#modalHolidayEdit').modal('show');
            @endif

            $(document).on('click', '#national-holidays-table .btn-edit-holiday', function() {
                var url = $(this).data('update-url');
                var date = $(this).data('date');
                var name = $(this).data('name') || '';
                $('#formHolidayEdit').attr('action', url);
                $('#edit_update_url').val(url);
                $('#edit_holiday_date').val(date);
                $('#edit_name').val(name);
                $('#modalHolidayEdit').modal('show');
            });

            $(document).on('click', '#national-holidays-table .btn-delete-holiday', function(e) {
                e.preventDefault();
                var form = $(this).closest('form');
                Swal.fire({
                    title: 'Delete this holiday?',
                    text: 'This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, delete',
                    cancelButtonText: 'Cancel'
                }).then(function(result) {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });

            var keepCreateModalValues = @json($errors->any() && old('_form') === 'create');
            $('#modalHolidayCreate').on('hidden.bs.modal', function() {
                if (!keepCreateModalValues) {
                    var f = $(this).find('form')[0];
                    if (f) {
                        f.reset();
                    }
                }
            });

        });
    </script>
@endpush
