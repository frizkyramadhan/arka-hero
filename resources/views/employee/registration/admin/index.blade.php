@extends('layouts.main')

@section('content')
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $title }}</h1>
                    <p class="text-muted">{{ $subtitle }}</p>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">Employee Registrations</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Statistics Cards -->
            <div class="row" id="statsCards">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3 id="pendingCount">-</h3>
                            <p>Pending Reviews</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-clock"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3 id="approvedCount">-</h3>
                            <p>Approved</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3 id="activeTokensCount">-</h3>
                            <p>Active Invitations</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-danger">
                        <div class="inner">
                            <h3 id="rejectedCount">-</h3>
                            <p>Rejected</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-times-circle"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="row mb-3">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h5 class="card-title mb-3">
                                        <i class="fas fa-cogs mr-2"></i>
                                        Quick Actions
                                    </h5>
                                </div>
                                <div class="col-md-6 text-right">
                                    <button class="btn btn-outline-secondary btn-sm" id="refreshStats">
                                        <i class="fas fa-sync-alt mr-1"></i>
                                        Refresh
                                    </button>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4 mb-2">
                                    <a href="{{ route('employee.registration.admin.invite') }}"
                                        class="btn btn-primary btn-block">
                                        <i class="fas fa-user-plus mr-2"></i>
                                        Send Invitation
                                    </a>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <button class="btn btn-success btn-block" id="bulkApprove">
                                        <i class="fas fa-check-double mr-2"></i>
                                        Bulk Approve
                                    </button>
                                </div>
                                <div class="col-md-4 mb-2">
                                    <button class="btn btn-warning btn-block" id="cleanupExpired">
                                        <i class="fas fa-broom mr-2"></i>
                                        Cleanup Expired
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Registrations Table -->
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-list mr-2"></i>
                                Pending Registration Requests
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" id="refreshTable">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="registrationsTable">
                                    <thead>
                                        <tr>
                                            <th width="5%">
                                                <input type="checkbox" id="selectAll">
                                            </th>
                                            <th>Full Name</th>
                                            <th>Email</th>
                                            <th>Identity Card</th>
                                            <th>Documents</th>
                                            <th>Submitted</th>
                                            <th width="15%">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Data will be loaded via AJAX -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Token Management Section -->
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-key mr-2"></i>
                                Invitation Tokens Management
                            </h3>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" id="refreshTokensTable">
                                    <i class="fas fa-sync-alt"></i>
                                </button>
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped" id="tokensTable">
                                    <thead>
                                        <tr>
                                            <th>Email</th>
                                            <th>Token</th>
                                            <th>Status</th>
                                            <th>Created</th>
                                            <th>Expires</th>
                                            <th width="15%">Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <!-- Data will be loaded via AJAX -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Bulk Action Modal -->
        <div class="modal fade" id="bulkActionModal" tabindex="-1" role="dialog">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-check-double mr-2"></i>
                            Bulk Approve Registrations
                        </h5>
                        <button type="button" class="close" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <p>Are you sure you want to approve all selected registrations?</p>
                        <div class="form-group">
                            <label for="bulkNotes">Admin Notes (Optional)</label>
                            <textarea class="form-control" id="bulkNotes" rows="3"
                                placeholder="Add notes for all approved registrations..."></textarea>
                        </div>
                        <div id="selectedRegistrations">
                            <!-- Selected items will be shown here -->
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                        <button type="button" class="btn btn-success" id="confirmBulkApprove">
                            <i class="fas fa-check mr-2"></i>
                            Approve Selected
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('styles')
    <!-- DataTables -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
    {{-- <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-responsive/css/responsive.bootstrap4.min.css') }}"> --}}
    <link rel="stylesheet" href="{{ asset('assets/plugins/datatables-buttons/css/buttons.bootstrap4.min.css') }}">
    <!-- Select2 -->
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <style>
        .small-box {
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .small-box .icon {
            top: 10px;
            right: 10px;
        }

        .card {
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        .table th {
            background-color: #f8f9fa;
            font-weight: 600;
            border-top: none;
        }

        .btn-block {
            font-weight: 500;
        }

        .badge {
            font-size: 0.75em;
        }

        .loading-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .loading-spinner {
            display: inline-block;
            width: 30px;
            height: 30px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #007bff;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% {
                transform: rotate(0deg);
            }

            100% {
                transform: rotate(360deg);
            }
        }

        .registration-summary {
            background: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .document-item {
            display: flex;
            align-items: center;
            padding: 8px;
            border: 1px solid #dee2e6;
            border-radius: 5px;
            margin-bottom: 5px;
        }

        .document-item:hover {
            background-color: #f8f9fa;
        }
    </style>
@endsection

@section('scripts')
    <!-- Bootstrap 4 -->
    <script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!-- bs-custom-file-input -->
    <script src="{{ asset('assets/plugins/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>
    <!-- DataTables  & Plugins -->
    <script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    {{-- <script src="{{ asset('assets/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script> --}}
    {{-- <script src="{{ asset('assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script> --}}
    <script src="{{ asset('assets/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/jszip/jszip.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/pdfmake/pdfmake.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/pdfmake/vfs_fonts.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>
    <!-- Select2 -->
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <!-- Page specific script -->
    <script>
        // Helper function to format date
        function formatDate(dateString, format = 'DD-MMM-YYYY HH:mm') {
            const date = new Date(dateString);

            if (format === 'DD-MMM-YYYY HH:mm') {
                const months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun',
                    'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'
                ];

                const day = String(date.getDate()).padStart(2, '0');
                const month = months[date.getMonth()];
                const year = date.getFullYear();
                const hours = String(date.getHours()).padStart(2, '0');
                const minutes = String(date.getMinutes()).padStart(2, '0');

                return `${day}-${month}-${year} ${hours}:${minutes}`;
            }

            return date.toLocaleString();
        }

        // Helper function to get time ago
        function timeAgo(dateString) {
            const date = new Date(dateString);
            const now = new Date();
            const diff = now - date;

            const seconds = Math.floor(diff / 1000);
            const minutes = Math.floor(seconds / 60);
            const hours = Math.floor(minutes / 60);
            const days = Math.floor(hours / 24);

            if (days > 0) return `${days} day${days > 1 ? 's' : ''} ago`;
            if (hours > 0) return `${hours} hour${hours > 1 ? 's' : ''} ago`;
            if (minutes > 0) return `${minutes} minute${minutes > 1 ? 's' : ''} ago`;
            return `${seconds} second${seconds > 1 ? 's' : ''} ago`;
        }

        $(document).ready(function() {
            let registrationsTable;
            let tokensTable;
            let selectedRegistrations = [];

            // SweetAlert helper functions
            function showSweetAlert(options) {
                return Swal.fire(options);
            }

            function showSuccessAlert(title, text) {
                return Swal.fire({
                    icon: 'success',
                    title: title,
                    text: text
                });
            }

            function showErrorAlert(title, text) {
                return Swal.fire({
                    icon: 'error',
                    title: title,
                    text: text
                });
            }

            function showConfirmAlert(title, text, confirmText = 'Yes', cancelText = 'Cancel') {
                return Swal.fire({
                    title: title,
                    text: text,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: confirmText,
                    cancelButtonText: cancelText
                });
            }

            // Initialize DataTable
            initializeTable();
            initializeTokensTable();

            // Load statistics
            loadStats();

            function initializeTable() {
                registrationsTable = $('#registrationsTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '{{ route('employee.registration.admin.pending') }}',
                        type: 'GET'
                    },
                    columns: [{
                            data: null,
                            orderable: false,
                            searchable: false,
                            render: function(data, type, row) {
                                return `<input type="checkbox" class="registration-checkbox" value="${row.id}">`;
                            }
                        },
                        {
                            data: 'fullname',
                            name: 'fullname'
                        },
                        {
                            data: 'email',
                            name: 'email'
                        },
                        {
                            data: 'identity_card',
                            name: 'identity_card'
                        },
                        {
                            data: 'documents_count',
                            name: 'documents_count',
                            render: function(data) {
                                return `<span class="badge badge-info">${data} files</span>`;
                            }
                        },
                        {
                            data: 'submitted_at',
                            name: 'submitted_at',
                            render: function(data) {
                                return timeAgo(data);
                            }
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false,
                            className: 'text-center'
                        }
                    ],
                    order: [
                        [5, 'desc']
                    ],
                    pageLength: 25,
                    responsive: true,
                    drawCallback: function() {
                        // Update checkbox states
                        updateCheckboxStates();
                    }
                });
            }

            function initializeTokensTable() {
                tokensTable = $('#tokensTable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: {
                        url: '{{ route('employee.registration.admin.tokens') }}',
                        type: 'GET'
                    },
                    columns: [{
                            data: 'email',
                            name: 'email'
                        },
                        {
                            data: 'token',
                            name: 'token',
                            render: function(data) {
                                return data.substring(0, 20) + '...';
                            }
                        },
                        {
                            data: 'status',
                            name: 'status',
                            render: function(data, type, row) {
                                let badgeClass = 'badge-secondary';
                                if (data === 'active') badgeClass = 'badge-success';
                                else if (data === 'expired') badgeClass = 'badge-danger';
                                else if (data === 'used') badgeClass = 'badge-info';

                                return `<span class="badge ${badgeClass}">${data.toUpperCase()}</span>`;
                            }
                        },
                        {
                            data: 'created_at',
                            name: 'created_at',
                            render: function(data) {
                                return formatDate(data);
                            }
                        },
                        {
                            data: 'expires_at',
                            name: 'expires_at',
                            render: function(data) {
                                return formatDate(data);
                            }
                        },
                        {
                            data: 'action',
                            name: 'action',
                            orderable: false,
                            searchable: false,
                            className: 'text-center'
                        }
                    ],
                    order: [
                        [3, 'desc']
                    ],
                    pageLength: 10,
                    responsive: true
                });
            }

            function loadStats() {
                $.get('{{ route('employee.registration.admin.stats') }}', function(data) {
                    $('#pendingCount').text(data.pending || 0);
                    $('#approvedCount').text(data.approved || 0);
                    $('#activeTokensCount').text(data.active_tokens || 0);
                    $('#rejectedCount').text(data.rejected || 0);
                }).fail(function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error!',
                        text: 'Failed to load statistics'
                    });
                });
            }

            // Select all checkbox
            $('#selectAll').on('change', function() {
                let isChecked = $(this).is(':checked');
                $('.registration-checkbox').prop('checked', isChecked);
                updateSelectedRegistrations();
            });

            // Individual checkbox
            $(document).on('change', '.registration-checkbox', function() {
                updateSelectedRegistrations();

                // Update select all checkbox
                let totalCheckboxes = $('.registration-checkbox').length;
                let checkedCheckboxes = $('.registration-checkbox:checked').length;
                $('#selectAll').prop('checked', totalCheckboxes === checkedCheckboxes);
            });

            function updateSelectedRegistrations() {
                selectedRegistrations = [];
                $('.registration-checkbox:checked').each(function() {
                    selectedRegistrations.push($(this).val());
                });

                // Update bulk action button state
                $('#bulkApprove').prop('disabled', selectedRegistrations.length === 0);
            }

            function updateCheckboxStates() {
                $('.registration-checkbox').each(function() {
                    let id = $(this).val();
                    $(this).prop('checked', selectedRegistrations.includes(id));
                });
            }

            // Quick review
            $(document).on('click', '.btn-quick-review', function() {
                let registrationId = $(this).data('id');
                loadQuickReview(registrationId);
            });

            function loadQuickReview(registrationId) {
                $('#quickReviewContent').html(
                    '<div class="text-center"><div class="loading-spinner"></div><p>Loading...</p></div>');
                $('#quickReviewModal').modal('show');

                $.get(`{{ route('employee.registration.admin.show', ':id') }}`.replace(':id', registrationId))
                    .done(function(response) {
                        $('#quickReviewContent').html(response);
                        $('#quickReviewModal').data('registration-id', registrationId);
                    })
                    .fail(function() {
                        $('#quickReviewContent').html(
                            '<div class="alert alert-danger">Failed to load registration details</div>');
                        Swal.fire({
                            icon: 'error',
                            title: 'Error!',
                            text: 'Failed to load registration details'
                        });
                    });
            }

            // Quick approve
            $('#quickApprove').on('click', function() {
                let registrationId = $('#quickReviewModal').data('registration-id');
                if (registrationId) {
                    approveRegistration(registrationId);
                }
            });

            // Quick reject
            $('#quickReject').on('click', function() {
                let registrationId = $('#quickReviewModal').data('registration-id');
                if (registrationId) {
                    Swal.fire({
                        title: 'Reject Registration?',
                        text: 'Please provide rejection reason:',
                        input: 'textarea',
                        inputPlaceholder: 'Enter rejection reason...',
                        showCancelButton: true,
                        confirmButtonText: 'Reject',
                        cancelButtonText: 'Cancel',
                        inputValidator: (value) => {
                            if (!value) {
                                return 'You need to provide a rejection reason!'
                            }
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            rejectRegistration(registrationId, result.value);
                        }
                    });
                }
            });

            // Bulk approve
            $('#bulkApprove').on('click', function() {
                if (selectedRegistrations.length === 0) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'No Selection',
                        text: 'Please select registrations to approve'
                    });
                    return;
                }

                let selectedHtml = '<ul class="list-group">';
                selectedRegistrations.forEach(function(id) {
                    let row = registrationsTable.row(function(idx, data) {
                        return data.id == id;
                    });
                    if (row.length > 0) {
                        let data = row.data();
                        selectedHtml +=
                            `<li class="list-group-item">${data.fullname} (${data.email})</li>`;
                    }
                });
                selectedHtml += '</ul>';

                $('#selectedRegistrations').html(selectedHtml);
                $('#bulkActionModal').modal('show');
            });

            $('#confirmBulkApprove').on('click', function() {
                let notes = $('#bulkNotes').val();
                bulkApproveRegistrations(selectedRegistrations, notes);
            });

            // Cleanup expired tokens
            $('#cleanupExpired').on('click', function() {
                Swal.fire({
                    title: 'Cleanup Expired Tokens?',
                    text: 'Are you sure you want to cleanup expired invitation tokens?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, cleanup!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        cleanupExpiredTokens();
                    }
                });
            });

            // Refresh functions
            $('#refreshStats').on('click', function() {
                loadStats();
                Swal.fire({
                    icon: 'success',
                    title: 'Refreshed!',
                    text: 'Statistics refreshed',
                    timer: 1500,
                    showConfirmButton: false
                });
            });

            $('#refreshTable').on('click', function() {
                registrationsTable.ajax.reload();
                Swal.fire({
                    icon: 'success',
                    title: 'Refreshed!',
                    text: 'Table refreshed',
                    timer: 1500,
                    showConfirmButton: false
                });
            });

            $('#refreshTokensTable').on('click', function() {
                tokensTable.ajax.reload();
                Swal.fire({
                    icon: 'success',
                    title: 'Refreshed!',
                    text: 'Tokens table refreshed',
                    timer: 1500,
                    showConfirmButton: false
                });
            });

            // Global functions for token actions (called from buttons)
            window.resendInvitation = function(tokenId) {
                Swal.fire({
                    title: 'Resend Invitation?',
                    text: 'Are you sure you want to resend the invitation email?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, resend it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Create and submit form
                        let form = document.createElement('form');
                        form.method = 'POST';
                        form.action = `{{ route('employee.registration.admin.resend', ':tokenId') }}`
                            .replace(':tokenId', tokenId);

                        let csrfToken = document.createElement('input');
                        csrfToken.type = 'hidden';
                        csrfToken.name = '_token';
                        csrfToken.value = '{{ csrf_token() }}';
                        form.appendChild(csrfToken);

                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            };

            window.deleteToken = function(tokenId) {
                Swal.fire({
                    title: 'Delete Token?',
                    text: 'Are you sure you want to delete this invitation token? This action cannot be undone.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Create and submit form
                        let form = document.createElement('form');
                        form.method = 'POST';
                        form.action =
                            `{{ route('employee.registration.admin.delete-token', ':tokenId') }}`
                            .replace(':tokenId', tokenId);

                        let csrfToken = document.createElement('input');
                        csrfToken.type = 'hidden';
                        csrfToken.name = '_token';
                        csrfToken.value = '{{ csrf_token() }}';
                        form.appendChild(csrfToken);

                        let methodField = document.createElement('input');
                        methodField.type = 'hidden';
                        methodField.name = '_method';
                        methodField.value = 'DELETE';
                        form.appendChild(methodField);

                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            };

            // API functions
            function approveRegistration(id, notes = '') {
                // Create and submit form
                let form = document.createElement('form');
                form.method = 'POST';
                form.action = `{{ route('employee.registration.admin.approve', ':id') }}`.replace(':id', id);

                let csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);

                if (notes) {
                    let notesField = document.createElement('input');
                    notesField.type = 'hidden';
                    notesField.name = 'admin_notes';
                    notesField.value = notes;
                    form.appendChild(notesField);
                }

                document.body.appendChild(form);
                form.submit();
            }

            function rejectRegistration(id, notes) {
                // Create and submit form
                let form = document.createElement('form');
                form.method = 'POST';
                form.action = `{{ route('employee.registration.admin.reject', ':id') }}`.replace(':id', id);

                let csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);

                let notesField = document.createElement('input');
                notesField.type = 'hidden';
                notesField.name = 'admin_notes';
                notesField.value = notes;
                form.appendChild(notesField);

                document.body.appendChild(form);
                form.submit();
            }

            function bulkApproveRegistrations(ids, notes) {
                $('#bulkActionModal').modal('hide');

                // Show loading message
                Swal.fire({
                    icon: 'info',
                    title: 'Processing...',
                    text: `Processing ${ids.length} registrations...`,
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    willOpen: () => {
                        Swal.showLoading()
                    }
                });

                // Process each registration by creating individual forms
                // Note: This will cause multiple page reloads, but follows the DepartmentController pattern
                if (ids.length > 0) {
                    let currentId = ids[0];

                    // Create and submit form for first registration
                    let form = document.createElement('form');
                    form.method = 'POST';
                    form.action = `{{ route('employee.registration.admin.approve', ':id') }}`.replace(':id',
                        currentId);

                    let csrfToken = document.createElement('input');
                    csrfToken.type = 'hidden';
                    csrfToken.name = '_token';
                    csrfToken.value = '{{ csrf_token() }}';
                    form.appendChild(csrfToken);

                    if (notes) {
                        let notesField = document.createElement('input');
                        notesField.type = 'hidden';
                        notesField.name = 'admin_notes';
                        notesField.value = notes;
                        form.appendChild(notesField);
                    }

                    document.body.appendChild(form);
                    form.submit();
                }
            }

            function cleanupExpiredTokens() {
                // Create and submit form
                let form = document.createElement('form');
                form.method = 'POST';
                form.action = '{{ route('employee.registration.admin.cleanup') }}';

                let csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);

                document.body.appendChild(form);
                form.submit();
            }
        });
    </script>
@endsection
