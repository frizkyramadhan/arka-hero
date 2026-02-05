@extends('layouts.main')

@section('title', $title ?? 'Flight Request Issuances')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $title ?? 'Flight Request Issuances' }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item active">Flight Issuances</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div id="accordion">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title"><strong>Letter of Guarantee (LG)</strong></h3>
                                <div class="card-tools">
                                    @can('flight-issuances.create')
                                        <a href="{{ route('flight-issuances.select-flight-requests') }}" class="btn btn-primary">
                                            <i class="fas fa-plus"></i> Add
                                        </a>
                                    @endcan
                                </div>
                            </div>
                            <!-- /.card-header -->
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
                                                        <label>Issued Number</label>
                                                        <input type="text" class="form-control" id="issued_number" name="issued_number" placeholder="Search...">
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label>FR Number</label>
                                                        <input type="text" class="form-control" id="fr_number" name="fr_number" placeholder="Search...">
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label>Business Partner</label>
                                                        <select class="form-control select2bs4" id="business_partner_id" name="business_partner_id">
                                                            <option value="">- All -</option>
                                                            @foreach(\App\Models\BusinessPartner::active()->get() as $bp)
                                                                <option value="{{ $bp->id }}">{{ $bp->bp_code }} - {{ $bp->bp_name }}</option>
                                                            @endforeach
                                                        </select>
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label>Date From</label>
                                                        <input type="date" class="form-control" id="date_from" name="date_from">
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label>Date To</label>
                                                        <input type="date" class="form-control" id="date_to" name="date_to">
                                                    </div>
                                                </div>
                                                <div class="col-md-2">
                                                    <div class="form-group">
                                                        <label>&nbsp;</label>
                                                        <button type="button" class="btn btn-secondary w-100" id="btn-reset" style="margin-bottom: 6px;">
                                                            <i class="fas fa-times"></i> Reset
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="table-responsive">
                                    <table id="flight-issuances-table" class="table table-bordered table-striped" width="100%">
                                        <thead>
                                            <tr>
                                                <th class="align-middle text-center" width="5%">No</th>
                                                <th class="align-middle">Issued Number</th>
                                                <th class="align-middle text-center">Issued Date</th>
                                                <th class="align-middle">FR Number</th>
                                                <th class="align-middle">Business Partner</th>
                                                <th class="align-middle text-center">Total Tickets</th>
                                                <th class="align-middle text-right">Total Price</th>
                                                <th class="align-middle">Issued By</th>
                                                <th class="align-middle text-center" width="12%">Actions</th>
                                            </tr>
                                        </thead>
                                    </table>
                                </div>
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
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('scripts')
    <script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
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
        $(document).ready(function() {
            // Initialize Select2
            $('.select2bs4').select2({
                theme: 'bootstrap4',
                width: '100%'
            });

            var table = $("#flight-issuances-table").DataTable({
                responsive: true,
                autoWidth: true,
                dom: 'rtip',
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('flight-issuances.data') }}",
                    data: function(d) {
                        d.issued_number = $('#issued_number').val(),
                        d.fr_number = $('#fr_number').val(),
                        d.business_partner_id = $('#business_partner_id').val(),
                        d.date_from = $('#date_from').val(),
                        d.date_to = $('#date_to').val()
                    }
                },
                columns: [
                    {
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                    {
                        data: 'issued_number',
                        name: 'issued_number'
                    },
                    {
                        data: 'issued_date',
                        name: 'issued_date',
                        className: 'text-center'
                    },
                    {
                        data: 'fr_number',
                        name: 'fr_number'
                    },
                    {
                        data: 'business_partner',
                        name: 'business_partner',
                        orderable: false
                    },
                    {
                        data: 'total_tickets',
                        name: 'total_tickets',
                        className: 'text-center'
                    },
                    {
                        data: 'total_price',
                        name: 'total_price',
                        className: 'text-right'
                    },
                    {
                        data: 'issued_by',
                        name: 'issued_by',
                        orderable: false
                    },
                    {
                        data: 'actions',
                        name: 'actions',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ],
                order: [[1, 'desc']]
            });

            // Filter change event
            $('#issued_number, #fr_number, #business_partner_id, #date_from, #date_to').on('change keyup', function() {
                table.ajax.reload();
            });

            // Reset button
            $('#btn-reset').on('click', function() {
                $('#issued_number').val('');
                $('#fr_number').val('');
                $('#business_partner_id').val('').trigger('change');
                $('#date_from').val('');
                $('#date_to').val('');
                table.ajax.reload();
            });
        });
    </script>
@endsection
