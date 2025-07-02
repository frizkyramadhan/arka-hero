@extends('layouts.main')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Letter Number Administration</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="#">Apps</a></li>
                        <li class="breadcrumb-item active">Letter Administration</li>
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
                            <h3 class="card-title">Letter Numbers List</h3>
                            <div class="card-tools">
                                <div class="btn-group dropleft">
                                    <a href="{{ route('letter-numbers.create') }}" class="btn btn-primary">
                                        <i class="fas fa-plus"></i> Create Letter
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="card-body">
                            <!-- Filter Card -->
                            <div id="accordion">
                                <div class="card card-primary">
                                    <div class="card-header">
                                        <h4 class="card-title w-100">
                                            <a class="d-block w-100" data-toggle="collapse" href="#collapseFilter"
                                                aria-expanded="false" aria-controls="collapseFilter">
                                                <i class="fas fa-filter"></i> Filter
                                            </a>
                                        </h4>
                                    </div>
                                    <div id="collapseFilter" class="collapse" data-parent="#accordion">
                                        <div class="card-body">
                                            <div class="row">
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label>Date From</label>
                                                        <input type="date" class="form-control" id="filter-date-from">
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label>Date To</label>
                                                        <input type="date" class="form-control" id="filter-date-to">
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label>Letter Category</label>
                                                        <select class="form-control select2bs4" id="filter-category">
                                                            <option value="">- All -</option>
                                                            @foreach ($categories as $category)
                                                                <option value="{{ $category->category_code }}">
                                                                    {{ $category->category_name }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label>Status</label>
                                                        <select class="form-control select2bs4" id="filter-status">
                                                            <option value="">- All -</option>
                                                            <option value="reserved">Reserved</option>
                                                            <option value="used">Used</option>
                                                            <option value="cancelled">Cancelled</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label>Destination</label>
                                                        <input type="text" class="form-control" id="filter-destination"
                                                            placeholder="Search destination...">
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label>Remarks</label>
                                                        <input type="text" class="form-control" id="filter-remarks"
                                                            placeholder="Search remarks...">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="row">
                                                <div class="col-md-12 text-right">
                                                    <button type="button" id="btn-reset-filter" class="btn btn-danger">
                                                        <i class="fas fa-times"></i> Reset Filter
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Data Table -->
                            <div class="table-responsive">
                                <table id="letter-numbers-table" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th class="align-middle" width="5%">No</th>
                                            <th class="align-middle">Letter Number</th>
                                            <th class="align-middle">Category</th>
                                            <th class="align-middle">Subject</th>
                                            <th class="align-middle">Date</th>
                                            <th class="align-middle">Destination</th>
                                            {{-- <th class="align-middle">Employee</th>
                                            <th class="align-middle">Project</th> --}}
                                            <th class="align-middle">Remarks</th>
                                            <th class="align-middle">Status</th>
                                            <th class="align-middle" width="10%">Action</th>
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
    <script src="{{ asset('vendor/sweetalert/sweetalert.all.js') }}"></script>

    <script>
        $(function() {
            // Initialize Select2
            $('.select2bs4').select2({
                theme: 'bootstrap4'
            });

            // Initialize DataTable
            var table = $("#letter-numbers-table").DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('letter-numbers.data') }}",
                    data: function(d) {
                        d.category_code = $('#filter-category').val();
                        d.status = $('#filter-status').val();
                        d.date_from = $('#filter-date-from').val();
                        d.date_to = $('#filter-date-to').val();
                        d.destination = $('#filter-destination').val();
                        d.remarks = $('#filter-remarks').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'letter_number',
                        name: 'letter_number'
                    },
                    {
                        data: 'category_name',
                        name: 'category.category_name'
                    },
                    {
                        data: 'subject_display',
                        name: 'subject_display'
                    },
                    {
                        data: 'letter_date',
                        name: 'letter_date'
                    },
                    {
                        data: 'destination',
                        name: 'destination'
                    },
                    // {
                    //     data: 'employee_display',
                    //     name: 'employee_display'
                    // },
                    // {
                    //     data: 'project_display',
                    //     name: 'project_display'
                    // },
                    {
                        data: 'remarks',
                        name: 'remarks',
                        orderable: false,
                        searchable: false,
                    },
                    {
                        data: 'status_badge',
                        name: 'status',
                        className: 'text-center'
                    },
                    {
                        data: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ],
                dom: 'rtpi',
                lengthChange: true,
                lengthMenu: [
                    [10, 25, 50, 100, -1],
                    ['10', '25', '50', '100', 'All']
                ],
                order: [
                    [4, 'desc']
                ], // Order by letter_date desc
                language: {
                    processing: "Processing...",
                    lengthMenu: "Show _MENU_ entries per page",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "Showing 0 to 0 of 0 entries",
                    infoFiltered: "(filtered from _MAX_ total entries)",
                    zeroRecords: "No matching records found",
                    emptyTable: "No data available in table",
                    paginate: {
                        first: "First",
                        previous: "Previous",
                        next: "Next",
                        last: "Last"
                    }
                }
            });

            // Filter events
            $('#btn-reset-filter').click(function() {
                $('#filter-category').val('').trigger('change');
                $('#filter-status').val('').trigger('change');
                $('#filter-date-from, #filter-date-to').val('');
                $('#filter-destination, #filter-remarks').val('');
                table.draw();
            });

            // Auto apply filter on change
            $('#filter-category, #filter-status, #filter-date-from, #filter-date-to').on('change', function() {
                table.draw();
            });

            // Auto apply filter on keyup for text inputs (with debounce)
            var timeout;
            $('#filter-destination, #filter-remarks').on('keyup', function() {
                clearTimeout(timeout);
                timeout = setTimeout(function() {
                    table.draw();
                }, 500); // Wait 500ms after user stops typing
            });

            // Delete function
            $(document).on('click', '.btn-delete', function() {
                var id = $(this).data('id');
                var letterNumber = $(this).data('letter-number');

                Swal.fire({
                    title: 'Confirm Delete',
                    text: 'Are you sure you want to delete letter number ' + letterNumber + '?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, Delete!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route('letter-numbers.destroy', ':id') }}'.replace(
                                ':id', id),
                            type: 'DELETE',
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
                                    table.draw();
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: response.message,
                                        confirmButtonColor: '#3085d6'
                                    });
                                }
                            },
                            error: function(xhr) {
                                var response = xhr.responseJSON;
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message ||
                                        'An error occurred while deleting the letter number',
                                    confirmButtonColor: '#3085d6'
                                });
                            }
                        });
                    }
                });
            });

            // Cancel function
            $(document).on('click', '.btn-cancel', function() {
                var id = $(this).data('id');
                var letterNumber = $(this).data('letter-number');

                Swal.fire({
                    title: 'Confirm Cancel',
                    text: 'Are you sure you want to cancel letter number ' + letterNumber + '?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#f39c12',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Yes, Cancel!',
                    cancelButtonText: 'No'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route('letter-numbers.cancel', ':id') }}'.replace(
                                ':id', id),
                            type: 'POST',
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
                                    table.draw();
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: response.message,
                                        confirmButtonColor: '#3085d6'
                                    });
                                }
                            },
                            error: function(xhr) {
                                var response = xhr.responseJSON;
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message ||
                                        'An error occurred while cancelling the letter number',
                                    confirmButtonColor: '#3085d6'
                                });
                            }
                        });
                    }
                });
            });
        });

        // Global functions for action buttons
        function deleteLetterNumber(id, letterNumber) {
            Swal.fire({
                title: 'Confirm Delete',
                text: 'Are you sure you want to delete letter number ' + letterNumber + '?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, Delete!',
                cancelButtonText: 'Cancel'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route('letter-numbers.destroy', ':id') }}'.replace(':id', id),
                        type: 'DELETE',
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
                                $('#letter-numbers-table').DataTable().draw();
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message,
                                    confirmButtonColor: '#3085d6'
                                });
                            }
                        },
                        error: function(xhr) {
                            var response = xhr.responseJSON;
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message ||
                                    'An error occurred while deleting the letter number',
                                confirmButtonColor: '#3085d6'
                            });
                        }
                    });
                }
            });
        }

        function cancelLetterNumber(id, letterNumber) {
            Swal.fire({
                title: 'Confirm Cancel',
                text: 'Are you sure you want to cancel letter number ' + letterNumber + '?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#f39c12',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Yes, Cancel!',
                cancelButtonText: 'No'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '{{ route('letter-numbers.cancel', ':id') }}'.replace(':id', id),
                        type: 'POST',
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
                                $('#letter-numbers-table').DataTable().draw();
                            } else {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message,
                                    confirmButtonColor: '#3085d6'
                                });
                            }
                        },
                        error: function(xhr) {
                            var response = xhr.responseJSON;
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message ||
                                    'An error occurred while cancelling the letter number',
                                confirmButtonColor: '#3085d6'
                            });
                        }
                    });
                }
            });
        }
    </script>
@endsection
