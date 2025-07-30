@extends('layouts.main')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>Approval Requests</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Approval Requests</li>
                    </ol>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Pending Approval Requests</h3>
                            <div class="card-tools">
                                <div class="btn-group">
                                    <button type="button" class="btn btn-sm btn-outline-secondary dropdown-toggle"
                                        data-toggle="dropdown">
                                        <i class="fas fa-filter"></i> Filter by Type
                                    </button>
                                    <div class="dropdown-menu">
                                        <a class="dropdown-item" href="#" onclick="filterByType('all')">All
                                            Documents</a>
                                        <a class="dropdown-item" href="#"
                                            onclick="filterByType('officialtravel')">Official Travel</a>
                                        <a class="dropdown-item" href="#"
                                            onclick="filterByType('recruitment_request')">Recruitment Request</a>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-success" onclick="showBulkApproveModal()">
                                    <i class="fas fa-check-double"></i> Bulk Approve
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <table id="approval-requests-table" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>
                                            <input type="checkbox" id="select-all">
                                        </th>
                                        <th>No</th>
                                        <th>Document Type</th>
                                        <th>Document Number</th>
                                        <th>Created Date</th>
                                        <th>Submit Date</th>
                                        <th>Requestor</th>
                                        <th>Days</th>
                                        <th>Status</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Bulk Approve Modal -->
    <div class="modal fade" id="bulkApproveModal" tabindex="-1" role="dialog" aria-labelledby="bulkApproveModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="bulkApproveForm">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title" id="bulkApproveModalLabel">Bulk Approve Documents</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="bulk_document_type">Document Type <span class="text-danger">*</span></label>
                            <select class="form-control" name="document_type" id="bulk_document_type" required>
                                <option value="">Select Document Type</option>
                                <option value="officialtravel">Official Travel</option>
                                <option value="recruitment_request">Recruitment Request</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="bulk_remarks">Remarks (Optional)</label>
                            <textarea class="form-control" name="remarks" id="bulk_remarks" rows="3" placeholder="Enter approval remarks..."></textarea>
                        </div>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            This will approve all selected documents of the specified type.
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success">Bulk Approve</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Approval Decision Modal -->
    <div class="modal fade" id="approvalModal" tabindex="-1" role="dialog" aria-labelledby="approvalModalLabel"
        aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <form id="approvalForm">
                    @csrf
                    <input type="hidden" id="approval_plan_id" name="approval_plan_id">
                    <div class="modal-header">
                        <h5 class="modal-title" id="approvalModalLabel">Approval Decision</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="status">Decision <span class="text-danger">*</span></label>
                            <select class="form-control" name="status" id="status" required>
                                <option value="">Select Decision</option>
                                <option value="1">Approve</option>
                                <option value="2">Revise</option>
                                <option value="3">Reject</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="remarks">Remarks (Optional)</label>
                            <textarea class="form-control" name="remarks" id="remarks" rows="3"
                                placeholder="Enter approval remarks..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Submit Decision</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            var table = $('#approval-requests-table').DataTable({
                processing: true,
                serverSide: false,
                ajax: "{{ route('approval.requests.data') }}",
                columns: [{
                        data: 'checkbox',
                        name: 'checkbox',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'DT_RowIndex',
                        name: 'DT_RowIndex',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'document_type',
                        name: 'document_type'
                    },
                    {
                        data: 'nomor',
                        name: 'nomor'
                    },
                    {
                        data: 'created_at',
                        name: 'created_at'
                    },
                    {
                        data: 'submit_at',
                        name: 'submit_at'
                    },
                    {
                        data: 'requestor',
                        name: 'requestor'
                    },
                    {
                        data: 'days',
                        name: 'days'
                    },
                    {
                        data: 'status_badge',
                        name: 'status_badge'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            // Select all checkbox
            $('#select-all').on('change', function() {
                $('.approval-checkbox').prop('checked', $(this).prop('checked'));
            });

            // Filter by document type
            window.filterByType = function(type) {
                $.ajax({
                    url: "{{ route('approval.requests.filter-by-type') }}",
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        document_type: type
                    },
                    success: function(response) {
                        if (response.success) {
                            table.clear().rows.add(response.data).draw();
                        }
                    }
                });
            };

            // Show bulk approve modal
            window.showBulkApproveModal = function() {
                var selectedIds = [];
                $('.approval-checkbox:checked').each(function() {
                    selectedIds.push($(this).val());
                });

                if (selectedIds.length === 0) {
                    toastr.warning('Please select at least one document to approve.');
                    return;
                }

                $('#bulkApproveModal').modal('show');
            };

            // Bulk approve form submission
            $('#bulkApproveForm').on('submit', function(e) {
                e.preventDefault();

                var selectedIds = [];
                $('.approval-checkbox:checked').each(function() {
                    selectedIds.push($(this).val());
                });

                var formData = $(this).serializeArray();
                formData.push({
                    name: 'ids',
                    value: selectedIds
                });

                $.ajax({
                    url: "{{ route('approval.plans.bulk-approve') }}",
                    type: 'POST',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            $('#bulkApproveModal').modal('hide');
                            table.ajax.reload();
                        }
                    },
                    error: function(xhr) {
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            toastr.error(xhr.responseJSON.message);
                        } else {
                            toastr.error('An error occurred while processing the request.');
                        }
                    }
                });
            });

            // Show approval modal
            window.showApprovalModal = function(id) {
                $('#approval_plan_id').val(id);
                $('#approvalModal').modal('show');
            };

            // Approval form submission
            $('#approvalForm').on('submit', function(e) {
                e.preventDefault();

                var id = $('#approval_plan_id').val();
                var formData = $(this).serialize();

                $.ajax({
                    url: "{{ route('approval.plans.update', '') }}/" + id,
                    type: 'PUT',
                    data: formData,
                    success: function(response) {
                        if (response.success) {
                            toastr.success(response.message);
                            $('#approvalModal').modal('hide');
                            table.ajax.reload();
                        }
                    },
                    error: function(xhr) {
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            toastr.error(xhr.responseJSON.message);
                        } else {
                            toastr.error('An error occurred while processing the request.');
                        }
                    }
                });
            });
        });
    </script>
@endpush
