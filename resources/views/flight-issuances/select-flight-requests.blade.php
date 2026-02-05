@extends('layouts.main')

@section('title', $title ?? 'Select Flight Requests for LG')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $title ?? 'Select Flight Requests for LG' }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('flight-issuances.index') }}">Flight Issuances</a></li>
                        <li class="breadcrumb-item active">Select FR</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <form method="POST" action="{{ route('flight-issuances.store-selected-frs') }}" id="selectFrForm">
                @csrf
                <div class="row">
                    <div class="col-12">
                        <div id="accordion">
                            <div class="card">
                                <div class="card-header">
                                    <h3 class="card-title"><strong>Select Flight Requests</strong></h3>
                                </div>
                                <div class="card-body">
                                    <div class="card card-primary">
                                        <div class="card-header">
                                            <h4 class="card-title w-100">
                                                <a class="d-block w-100" data-toggle="collapse" href="#collapseOne">
                                                    <i class="fas fa-filter"></i> Filter
                                                </a>
                                            </h4>
                                        </div>
                                        <div id="collapseOne" class="collapse" data-parent="#accordion">
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-2">
                                                        <div class="form-group">
                                                            <label>Status</label>
                                                            <select class="form-control select2bs4" id="status"
                                                                name="status" multiple>
                                                                <option value="approved" selected>Approved</option>
                                                                <option value="issued" selected>Issued</option>
                                                            </select>
                                                            <small class="text-muted">Default: Approved & Issued</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="form-group">
                                                            <label>Form Number</label>
                                                            <input type="text" class="form-control" id="form_number"
                                                                name="form_number" placeholder="Search...">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="form-group">
                                                            <label>Date From</label>
                                                            <input type="date" class="form-control" id="date_from"
                                                                name="date_from">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="form-group">
                                                            <label>Date To</label>
                                                            <input type="date" class="form-control" id="date_to"
                                                                name="date_to">
                                                        </div>
                                                    </div>
                                                    <div class="col-md-2">
                                                        <div class="form-group">
                                                            <label>&nbsp;</label>
                                                            <button type="button" class="btn btn-secondary w-100"
                                                                id="btn-reset" style="margin-bottom: 6px;">
                                                                <i class="fas fa-times"></i> Reset
                                                            </button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="table-responsive">
                                        <table id="flight-requests-table" class="table table-bordered table-striped"
                                            width="100%">
                                            <thead>
                                                <tr>
                                                    <th class="align-middle text-center" width="5%">
                                                        <input type="checkbox" id="selectAllCheckbox">
                                                    </th>
                                                    <th class="align-middle text-center" width="5%">No</th>
                                                    <th class="align-middle">Form Number</th>
                                                    <th class="align-middle">Employee Name</th>
                                                    <th class="align-middle">NIK</th>
                                                    <th class="align-middle">Purpose of Travel</th>
                                                    <th class="align-middle text-center">Status</th>
                                                    <th class="align-middle text-center">Requested At</th>
                                                </tr>
                                            </thead>
                                        </table>
                                    </div>
                                    <div class="mt-3">
                                        <button type="submit" class="btn btn-primary" id="btnContinue" disabled>
                                            <i class="fas fa-arrow-right"></i> Continue with Selected FR
                                        </button>
                                        <a href="{{ route('flight-issuances.index') }}" class="btn btn-default">
                                            <i class="fas fa-times"></i> Cancel
                                        </a>
                                        <span class="ml-2 text-muted" id="selectedCount">0 selected</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('scripts')
    <script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('.select2bs4').select2({
                theme: 'bootstrap4',
                width: '100%',
                placeholder: 'Select Status'
            });

            var table = $("#flight-requests-table").DataTable({
                responsive: true,
                autoWidth: true,
                dom: 'rtip',
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('flight-requests.data') }}",
                    data: function(d) {
                        var statusVal = $('#status').val();
                        // Send status array, or empty to use default (approved & issued)
                        d.status = statusVal && statusVal.length > 0 ? statusVal : null;
                        d.for_issuance = true; // Flag untuk default filter approved & issued
                        d.form_number = $('#form_number').val();
                        d.date_from = $('#date_from').val();
                        d.date_to = $('#date_to').val();
                    }
                },
                columns: [{
                        data: null,
                        name: 'checkbox',
                        orderable: false,
                        searchable: false,
                        className: 'text-center',
                        render: function(data, type, row) {
                            return '<input type="checkbox" class="fr-checkbox" name="flight_request_ids[]" value="' +
                                row.id + '" data-id="' + row.id + '">';
                        }
                    },
                    {
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'form_number',
                        name: 'form_number'
                    },
                    {
                        data: 'employee_name',
                        name: 'employee_name',
                        orderable: false
                    },
                    {
                        data: 'nik',
                        name: 'nik',
                        orderable: false
                    },
                    {
                        data: 'purpose_of_travel',
                        name: 'purpose_of_travel',
                        orderable: false
                    },
                    {
                        data: 'status',
                        name: 'status',
                        className: 'text-center',
                        orderable: false
                    },
                    {
                        data: 'requested_at',
                        name: 'requested_at',
                        className: 'text-center'
                    }
                ],
                order: [
                    [2, 'desc']
                ]
            });

            // Select All checkbox (delegated for dynamic content)
            $(document).on('change', '#selectAllCheckbox', function() {
                $('.fr-checkbox').prop('checked', this.checked);
                updateSelectedCount();
            });

            // Individual checkbox change (delegated for dynamic content)
            $(document).on('change', '.fr-checkbox', function() {
                updateSelectAllState();
                updateSelectedCount();
            });

            // Select All button
            $('#selectAll').on('click', function() {
                $('.fr-checkbox').prop('checked', true);
                $('#selectAllCheckbox').prop('checked', true);
                updateSelectedCount();
            });

            // Deselect All button
            $('#deselectAll').on('click', function() {
                $('.fr-checkbox').prop('checked', false);
                $('#selectAllCheckbox').prop('checked', false);
                updateSelectedCount();
            });

            function updateSelectAllState() {
                var total = $('.fr-checkbox').length;
                var checked = $('.fr-checkbox:checked').length;
                $('#selectAllCheckbox').prop('checked', total > 0 && total === checked);
            }

            function updateSelectedCount() {
                var count = $('.fr-checkbox:checked').length;
                $('#selectedCount').text(count + ' selected');
                $('#btnContinue').prop('disabled', count === 0);
            }

            // Form submit - collect selected IDs
            $('#selectFrForm').on('submit', function(e) {
                var selectedIds = $('.fr-checkbox:checked').map(function() {
                    return $(this).val();
                }).get();

                if (selectedIds.length === 0) {
                    e.preventDefault();
                    alert('Please select at least one Flight Request.');
                    return false;
                }

                // Remove any existing hidden inputs to avoid duplicates
                $('input[name="flight_request_ids[]"]').remove();

                // Add selected IDs as hidden inputs
                selectedIds.forEach(function(id) {
                    $('<input>').attr({
                        type: 'hidden',
                        name: 'flight_request_ids[]',
                        value: id
                    }).appendTo('#selectFrForm');
                });
            });

            // Filter change event
            $('#status, #form_number, #date_from, #date_to').on('change keyup', function() {
                table.ajax.reload(function() {
                    updateSelectAllState();
                    updateSelectedCount();
                });
            });

            // Reset button
            $('#btn-reset').on('click', function() {
                $('#status').val(['approved', 'issued']).trigger('change');
                $('#form_number').val('');
                $('#date_from').val('');
                $('#date_to').val('');
                table.ajax.reload(function() {
                    updateSelectAllState();
                    updateSelectedCount();
                });
            });
        });
    </script>
@endsection
