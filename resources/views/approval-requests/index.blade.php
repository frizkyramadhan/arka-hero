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
                                    <option value="leave_request">Leave Request</option>
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
                            Swal.fire({
                                icon: 'error',
                                title: 'Session Expired',
                                text: 'Session expired. Please refresh the page and login again.',
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'OK'
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: 'Failed to load approval requests data. Please try again.',
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'OK'
                            });
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

                // Enhanced validation for no selection
                if (selectedIds.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No Selection',
                        text: 'Please select at least one request to approve.',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                // Confirmation dialog with SweetAlert
                Swal.fire({
                    title: 'Bulk Approval Confirmation',
                    text: 'Are you sure you want to approve ' + selectedIds.length +
                        ' request(s)? This action cannot be undone.',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Yes, Approve All',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Show loading state
                        var $bulkBtn = $('button:contains("Bulk Approve")');
                        var originalText = $bulkBtn.html();
                        $bulkBtn.prop('disabled', true).html(
                            '<i class="fas fa-spinner fa-spin mr-2"></i> Processing...');

                        $.ajax({
                            url: '{{ route('approval.requests.bulk-approve') }}',
                            type: 'POST',
                            data: {
                                ids: selectedIds,
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Success',
                                        text: response.message,
                                        confirmButtonColor: '#3085d6',
                                        confirmButtonText: 'OK'
                                    });

                                    table.ajax.reload();
                                    $('#selectAll').prop('checked', false);

                                    // Show detailed success info if available
                                    if (response.successCount && response.failCount > 0) {
                                        setTimeout(function() {
                                            Swal.fire({
                                                icon: 'info',
                                                title: 'Process Details',
                                                text: 'Processed: ' +
                                                    response.successCount +
                                                    ' approved, ' +
                                                    response.failCount +
                                                    ' failed',
                                                confirmButtonColor: '#3085d6',
                                                confirmButtonText: 'OK'
                                            });
                                        }, 1000);
                                    }
                                } else {
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: response.message ||
                                            'Unknown error occurred',
                                        confirmButtonColor: '#3085d6',
                                        confirmButtonText: 'OK'
                                    });
                                }
                            },
                            error: function(xhr, status, error) {
                                var errorMessage = 'Failed to process bulk approval.';

                                try {
                                    var response = JSON.parse(xhr.responseText);
                                    if (response.message) {
                                        errorMessage = response.message;
                                    }
                                } catch (e) {
                                    // Use default message if JSON parsing fails
                                }

                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: errorMessage,
                                    confirmButtonColor: '#3085d6',
                                    confirmButtonText: 'OK'
                                });

                                console.error('Bulk approval error:', {
                                    status: status,
                                    error: error,
                                    response: xhr.responseText
                                });
                            },
                            complete: function() {
                                // Restore button state
                                $bulkBtn.prop('disabled', false).html(originalText);
                            }
                        });
                    }
                });
            };

            // Session messages are handled by global scripts.blade.php
        });
    </script>
@endsection
