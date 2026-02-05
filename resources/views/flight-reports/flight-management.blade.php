@extends('layouts.main')

@section('title', $title ?? 'Flight Management Report')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ $title ?? 'Flight Management Report' }}</h1>
                </div>
                <div class="col-sm-6">
                    <div class="float-right">
                        <a href="{{ route('flight.reports.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-filter"></i> Filter Options
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('flight.reports.flight-management') }}" class="row"
                        id="filterForm">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="issued_number">No. LG / Issued Number</label>
                                <input type="text" class="form-control" name="issued_number" id="issued_number"
                                    value="{{ $filters['issued_number'] ?? '' }}" placeholder="Search...">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="business_partner_id">Business Partner</label>
                                <select class="form-control select2" name="business_partner_id" id="business_partner_id">
                                    <option value="">— All —</option>
                                    @foreach ($businessPartners as $bp)
                                        <option value="{{ $bp->id }}"
                                            {{ ($filters['business_partner_id'] ?? '') == $bp->id ? 'selected' : '' }}>
                                            {{ $bp->bp_code }} — {{ $bp->bp_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="date_from">Date From</label>
                                <input type="date" class="form-control" name="date_from" id="date_from"
                                    value="{{ $filters['date_from'] ?? '' }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="date_to">Date To</label>
                                <input type="date" class="form-control" name="date_to" id="date_to"
                                    value="{{ $filters['date_to'] ?? '' }}">
                            </div>
                        </div>
                    </form>
                    <div class="row mt-2">
                        <div class="col-12">
                            <button type="submit" form="filterForm" class="btn btn-primary mr-2">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('flight.reports.flight-management') }}"
                                class="btn btn-outline-secondary mr-2">
                                <i class="fas fa-undo"></i> Reset
                            </a>
                            <a href="#" id="btn-export-excel" class="btn btn-success"
                                title="Apply filter first, then export">
                                <i class="fas fa-file-excel"></i> Export to Excel
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table id="flight-management-report-table" class="table table-bordered table-striped table-sm"
                            style="width: 100%">
                            <thead>
                                <tr>
                                    <th class="text-center align-middle">No</th>
                                    <th class="align-middle">Name</th>
                                    <th class="text-center align-middle">NIK</th>
                                    <th class="text-center align-middle">Site</th>
                                    <th class="align-middle">Route</th>
                                    <th class="align-middle">Booking Code</th>
                                    <th class="text-center align-middle">Departure</th>
                                    <th class="text-center align-middle">Arrival</th>
                                    <th class="text-center report-currency-col align-middle">622 (Company)</th>
                                    <th class="text-center report-currency-col align-middle">151 (Advance)</th>
                                    <th class="text-center align-middle">FR Request Date</th>
                                    <th class="text-center align-middle">Issued Date</th>
                                    <th class="text-center align-middle">Target</th>
                                    <th class="align-middle">No. LG</th>
                                    <th class="align-middle">Vendor</th>
                                    <th class="text-center report-currency-col align-middle">Price</th>
                                    <th class="text-center report-currency-col align-middle">Service Charge</th>
                                    <th class="text-center report-currency-col align-middle">Total</th>
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
        }

        .report-currency-cell .report-amount {
            text-align: right;
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
