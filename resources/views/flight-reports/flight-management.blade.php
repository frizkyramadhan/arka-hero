@extends('layouts.main')

@section('title', $title ?? 'Flight Management Report')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row align-items-center">
                <div class="col">
                    <h1 class="m-0 font-weight-bold">
                        <i class="fas fa-plane-departure text-primary mr-2"></i>
                        {{ $title ?? 'Flight Management Report' }}
                    </h1>
                    <nav aria-label="breadcrumb" class="mt-1">
                        <ol class="breadcrumb mb-0 py-0 bg-transparent small">
                            <li class="breadcrumb-item"><a href="{{ route('flight.reports.index') }}">Reports</a></li>
                            <li class="breadcrumb-item active">Flight Management</li>
                        </ol>
                    </nav>
                </div>
                <div class="col-auto">
                    <a href="{{ route('flight.reports.index') }}" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left mr-1"></i> Back to Reports
                    </a>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            {{-- Filter Card --}}
            <div class="card card-outline card-primary shadow-sm mb-4">
                <div class="card-header py-3 bg-white border-bottom">
                    <h5 class="card-title mb-0 text-dark">
                        <i class="fas fa-filter text-primary mr-2"></i>Filter Options
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('flight.reports.flight-management') }}" id="filterForm">
                        <div class="row align-items-end">
                            <div class="col-md-6 col-lg-3 form-group mb-lg-0">
                                <label for="issued_number" class="small font-weight-bold text-muted">No. LG / Issued Number</label>
                                <input type="text" class="form-control" name="issued_number" id="issued_number"
                                    value="{{ $filters['issued_number'] ?? '' }}" placeholder="Search...">
                            </div>
                            <div class="col-md-6 col-lg-3 form-group mb-lg-0">
                                <label for="business_partner_id" class="small font-weight-bold text-muted">Business Partner</label>
                                <select class="form-control select2" name="business_partner_id" id="business_partner_id">
                                    <option value="">Select Business Partner</option>
                                    <option value="all" {{ ($filters['business_partner_id'] ?? '') == 'all' ? 'selected' : '' }}>All</option>
                                    @foreach ($businessPartners as $bp)
                                        <option value="{{ $bp->id }}"
                                            {{ ($filters['business_partner_id'] ?? '') == $bp->id ? 'selected' : '' }}>
                                            {{ $bp->bp_code }} — {{ $bp->bp_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 col-lg-2 form-group mb-lg-0">
                                <label for="date_from" class="small font-weight-bold text-muted">Date From</label>
                                <input type="date" class="form-control" name="date_from" id="date_from"
                                    value="{{ $filters['date_from'] ?? '' }}">
                            </div>
                            <div class="col-md-6 col-lg-2 form-group mb-lg-0">
                                <label for="date_to" class="small font-weight-bold text-muted">Date To</label>
                                <input type="date" class="form-control" name="date_to" id="date_to"
                                    value="{{ $filters['date_to'] ?? '' }}">
                            </div>
                            <div class="col-12 col-lg-2 form-group mb-lg-0 d-flex flex-wrap">
                                <button type="submit" form="filterForm" class="btn btn-primary mr-2 mb-1">
                                    <i class="fas fa-search mr-1"></i> Filter
                                </button>
                                <a href="{{ route('flight.reports.flight-management') }}" class="btn btn-outline-secondary mb-1">
                                    <i class="fas fa-undo mr-1"></i> Reset
                                </a>
                            </div>
                        </div>
                        <div class="row mt-3 pt-3 border-top">
                            <div class="col-12">
                                <a href="#" id="btn-export-excel" class="btn btn-success btn-sm" title="Apply filter first, then export">
                                    <i class="fas fa-file-excel mr-1"></i> Export to Excel
                                </a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Report Table Card --}}
            <div class="card shadow-sm">
                <div class="card-header py-3 bg-white border-bottom">
                    <h5 class="card-title mb-0 text-dark">
                        <i class="fas fa-table text-primary mr-2"></i>Report Data
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive flight-report-table-wrapper">
                        <table id="flight-management-report-table" class="table table-hover table-bordered mb-0 flight-management-table"
                            style="width: 100%">
                            <thead class="thead-light">
                                <tr>
                                    <th class="text-center align-middle bg-light">No</th>
                                    <th class="align-middle bg-light">Name</th>
                                    <th class="text-center align-middle bg-light">NIK</th>
                                    <th class="text-center align-middle bg-light">Site</th>
                                    <th class="align-middle bg-light">Route</th>
                                    <th class="align-middle bg-light">Booking Code</th>
                                    <th class="text-center align-middle bg-light">Departure</th>
                                    <th class="text-center align-middle bg-light">Arrival</th>
                                    <th class="text-center report-currency-col align-middle bg-light">622 (Company)</th>
                                    <th class="text-center report-currency-col align-middle bg-light">151 (Advance)</th>
                                    <th class="text-center align-middle bg-light">FR Request Date</th>
                                    <th class="text-center align-middle bg-light">Issued Date</th>
                                    <th class="text-center align-middle bg-light">Target</th>
                                    <th class="align-middle bg-light">No. LG</th>
                                    <th class="align-middle bg-light">Vendor</th>
                                    <th class="text-center report-currency-col align-middle bg-light">Price</th>
                                    <th class="text-center report-currency-col align-middle bg-light">Service Charge</th>
                                    <th class="text-center report-currency-col align-middle bg-light">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <style>
        /* Flight Management Report - Enhanced table & layout */
        .flight-report-table-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }

        .flight-management-table {
            font-size: 0.875rem;
        }

        .flight-management-table thead th {
            white-space: nowrap;
            font-weight: 600;
            color: #495057;
            border-bottom: 2px solid #dee2e6;
            padding: 0.75rem 0.5rem;
        }

        .flight-management-table tbody td {
            padding: 0.6rem 0.5rem;
            vertical-align: middle;
        }

        .flight-management-table tbody tr:hover {
            background-color: #f8f9fa;
        }

        .report-currency-col {
            min-width: 100px;
        }

        .report-currency-cell {
            position: relative;
            padding-left: 2.25rem;
            text-align: right;
        }

        .report-currency-cell .report-rp {
            position: absolute;
            left: 0.5rem;
            text-align: left;
            color: #6c757d;
            font-size: 0.8em;
        }

        .report-currency-cell .report-amount {
            text-align: right;
        }

        .card-outline.card-primary {
            border-top: 3px solid #007bff;
        }

        .content-header .breadcrumb {
            font-size: 0.875rem;
        }
    </style>
@endsection

@section('scripts')
    <script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            $('.select2').select2({
                theme: 'bootstrap4',
                width: '100%'
            });

            var table = $('#flight-management-report-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('flight.reports.flight-management.data') }}",
                    data: function(d) {
                        d.issued_number = $('#issued_number').val();
                        d.business_partner_id = $('#business_partner_id').val();
                        d.date_from = $('#date_from').val();
                        d.date_to = $('#date_to').val();
                    }
                },
                columns: [{
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'nama',
                        name: 'nama'
                    },
                    {
                        data: 'nik',
                        name: 'nik',
                        className: 'text-center'
                    },
                    {
                        data: 'site',
                        name: 'site',
                        className: 'text-center'
                    },
                    {
                        data: 'rute',
                        name: 'rute'
                    },
                    {
                        data: 'kode_booking',
                        name: 'kode_booking'
                    },
                    {
                        data: 'departure',
                        name: 'departure',
                        className: 'text-center'
                    },
                    {
                        data: 'arrival',
                        name: 'arrival',
                        className: 'text-center'
                    },
                    {
                        data: 'company_amount',
                        name: 'company_amount',
                        orderable: false,
                        searchable: false,
                        className: 'report-currency-cell',
                        render: function(val) {
                            if (!val || val === '-') return '-';
                            return '<span class="report-rp">Rp</span><span class="report-amount">' +
                                val + '</span>';
                        }
                    },
                    {
                        data: 'advance_display',
                        name: 'advance_display',
                        orderable: false,
                        searchable: false,
                        className: 'report-currency-cell',
                        render: function(val) {
                            if (!val || val === '-') return '-';
                            return '<span class="report-rp">Rp</span><span class="report-amount">' +
                                val + '</span>';
                        }
                    },
                    {
                        data: 'tanggal_fr_masuk',
                        name: 'tanggal_fr_masuk',
                        className: 'text-center'
                    },
                    {
                        data: 'tanggal_issued',
                        name: 'tanggal_issued',
                        className: 'text-center'
                    },
                    {
                        data: 'target',
                        name: 'target',
                        className: 'text-center'
                    },
                    {
                        data: 'no_lg',
                        name: 'no_lg'
                    },
                    {
                        data: 'vendor',
                        name: 'vendor'
                    },
                    {
                        data: 'harga',
                        name: 'harga',
                        orderable: false,
                        searchable: false,
                        className: 'report-currency-cell',
                        render: function(val) {
                            return val ?
                                '<span class="report-rp">Rp</span><span class="report-amount">' +
                                val + '</span>' : '-';
                        }
                    },
                    {
                        data: 'service_charge',
                        name: 'service_charge',
                        orderable: false,
                        searchable: false,
                        className: 'report-currency-cell',
                        render: function(val) {
                            if (!val || val === '-') return '-';
                            return '<span class="report-rp">Rp</span><span class="report-amount">' +
                                val + '</span>';
                        }
                    },
                    {
                        data: 'jumlah',
                        name: 'jumlah',
                        orderable: false,
                        searchable: false,
                        className: 'report-currency-cell',
                        render: function(val) {
                            return val ?
                                '<span class="report-rp">Rp</span><span class="report-amount">' +
                                val + '</span>' : '-';
                        }
                    }
                ],
                order: [
                    [0, 'asc']
                ],
                pageLength: 25,
                lengthMenu: [
                    [10, 25, 50, 100, 250, 500],
                    [10, 25, 50, 100, 250, 500]
                ],
                language: {
                    emptyTable: 'Apply filter to load data.',
                    zeroRecords: 'No matching records found.',
                    processing: 'Loading...',
                    info: 'Showing _START_ to _END_ of _TOTAL_ entries',
                    infoEmpty: 'Showing 0 to 0 of 0 entries',
                    infoFiltered: '(filtered from _MAX_ total entries)',
                    lengthMenu: 'Show _MENU_ entries',
                    paginate: {
                        first: 'First',
                        last: 'Last',
                        next: 'Next',
                        previous: 'Previous'
                    }
                }
            });

            $('#issued_number, #business_partner_id, #date_from, #date_to').on('change keyup', function() {
                table.ajax.reload();
            });

            $('#btn-export-excel').on('click', function(e) {
                e.preventDefault();
                var params = new URLSearchParams({
                    issued_number: $('#issued_number').val() || '',
                    business_partner_id: $('#business_partner_id').val() || '',
                    date_from: $('#date_from').val() || '',
                    date_to: $('#date_to').val() || ''
                });
                var hasFilter = $('#issued_number').val() || $('#business_partner_id').val() || $(
                    '#date_from').val() || $('#date_to').val();
                if (!hasFilter) {
                    alert('Please apply at least one filter before exporting.');
                    return;
                }
                window.location.href = "{{ route('flight.reports.flight-management.export') }}?" + params
                    .toString();
            });
        });
    </script>
@endsection
