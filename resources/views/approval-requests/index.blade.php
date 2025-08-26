@extends('layouts.main')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $subtitle }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item active">{{ $title }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Filters Card -->
            <div class="card card-primary card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-filter mr-2"></i>
                        <strong>Filters</strong>
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="document_type">Document Type</label>
                                <select class="form-control" id="document_type">
                                    <option value="">All Types</option>
                                    <option value="officialtravel">Official Travel</option>
                                    <option value="recruitment_request">Recruitment Request</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="date1">Date From</label>
                                <input type="date" class="form-control" id="date1">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="date2">Date To</label>
                                <input type="date" class="form-control" id="date2">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>&nbsp;</label>
                                <button type="button" class="btn btn-primary btn-block" onclick="applyFilters()">
                                    <i class="fas fa-search mr-2"></i> Apply Filters
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Approval Requests Table -->
            <div class="card card-success card-outline">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-list mr-2"></i>
                        <strong>Pending Approval Requests</strong>
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-success" onclick="bulkApprove()">
                            <i class="fas fa-check-double mr-2"></i> Bulk Approve
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover" id="approvalRequestsTable">
                            <thead>
                                <tr>
                                    <th class="align-middle" width="5%">
                                        <input type="checkbox" id="selectAll">
                                    </th>
                                    <th class="align-middle">Document Type</th>
                                    <th class="align-middle">Document Number</th>
                                    <th class="align-middle">Remarks</th>
                                    <th class="align-middle">Submitted By</th>
                                    <th class="align-middle">Submitted At</th>
                                    <th class="align-middle">Current Approval</th>
                                    <th class="text-center align-middle">Action</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('styles')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <!-- Toastr -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/toastr/toastr.min.css') }}">
    <style>
        .card {
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .card-header {
            border-radius: calc(0.5rem - 1px) calc(0.5rem - 1px) 0 0;
        }

        .btn {
            border-radius: 0.25rem;
        }

        .table th {
            background-color: #f4f6f9;
            border-bottom: 2px solid #dee2e6;
        }

        .badge {
            font-size: 0.75rem;
        }
    </style>
@endsection

@section('scripts')
    <!-- DataTables -->
    <script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>


    <script>
        $(function() {
            // Initialize DataTable
            var table = $('#approvalRequestsTable').DataTable({
                processing: true,
                serverSide: false,
                ajax: {
                    url: '{{ route('approval.requests.data') }}',
                    data: function(d) {
                        d.document_type = $('#document_type').val();
                        d.date1 = $('#date1').val();
                        d.date2 = $('#date2').val();
                    },
                    error: function(xhr, error, thrown) {
                        console.error('DataTables error:', error);
                        console.error('Response:', xhr.responseText);

                        // Check if response is HTML (redirect to login)
                        if (xhr.responseText.includes('<!DOCTYPE html>') || xhr.responseText.includes(
                                'login')) {
                            toastr.error('Session expired. Please refresh the page and login again.');
                        } else {
                            toastr.error('Failed to load approval requests data. Please try again.');
                        }
                    }
                },
                columns: [{
                        data: 'id',
                        orderable: false,
                        searchable: false,
                        render: function(data) {
                            return '<input type="checkbox" class="approval-checkbox" value="' +
                                data + '">';
                        }
                    },
                    {
                        data: 'document_type'
                    },
                    {
                        data: 'document_number'
                    },
                    {
                        data: 'remarks'
                    },
                    {
                        data: 'submitted_by'
                    },
                    {
                        data: 'submitted_at'
                    },
                    {
                        data: 'current_approval',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'id',
                        orderable: false,
                        searchable: false,
                        render: function(data) {
                            return '<a href="{{ url('approval/requests') }}/' + data +
                                '" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i> Review</a>';
                        }
                    }
                ],
                order: [
                    [5, 'desc']
                ], // Sort by submitted_at desc
                pageLength: 25,
                responsive: true,
                language: {
                    processing: '<i class="fa fa-spinner fa-spin fa-3x fa-fw"></i><span class="sr-only">Loading...</span>',
                    emptyTable: 'No approval requests found.',
                    zeroRecords: 'No approval requests match your search criteria.'
                }
            });

            // Handle select all checkbox
            $('#selectAll').change(function() {
                $('.approval-checkbox').prop('checked', $(this).prop('checked'));
            });

            // Handle individual checkbox changes
            $(document).on('change', '.approval-checkbox', function() {
                if (!$(this).prop('checked')) {
                    $('#selectAll').prop('checked', false);
                } else {
                    var allChecked = $('.approval-checkbox:checked').length === $('.approval-checkbox')
                        .length;
                    $('#selectAll').prop('checked', allChecked);
                }
            });

            // Apply filters function
            window.applyFilters = function() {
                table.ajax.reload();
            };

            // Bulk approve function
            window.bulkApprove = function() {
                var selectedIds = [];
                $('.approval-checkbox:checked').each(function() {
                    selectedIds.push($(this).val());
                });

                if (selectedIds.length === 0) {
                    toastr.warning('Please select at least one request to approve.');
                    return;
                }

                if (confirm('Are you sure you want to approve ' + selectedIds.length + ' request(s)?')) {
                    $.ajax({
                        url: '{{ route('approval.requests.bulk-approve') }}',
                        type: 'POST',
                        data: {
                            ids: selectedIds,
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                toastr.success(response.message);
                                table.ajax.reload();
                                $('#selectAll').prop('checked', false);
                            } else {
                                toastr.error(response.message);
                            }
                        },
                        error: function(xhr) {
                            toastr.error('Failed to process bulk approval.');
                        }
                    });
                }
            };

            // Show toastr messages
            @if (session('toast_success'))
                toastr.success('{{ session('toast_success') }}');
            @endif

            @if (session('toast_error'))
                toastr.error('{{ session('toast_error') }}');
            @endif
        });
    </script>
@endsection
