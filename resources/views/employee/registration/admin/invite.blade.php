@extends('layouts.main')

@section('content')
    <!-- Content Header -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $title }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('employee.registration.admin.index') }}">Employee
                                Registrations</a></li>
                        <li class="breadcrumb-item active">Send Invitation</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <!-- Single Invitation Card -->
                <div class="col-md-6">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-user-plus mr-2"></i>
                                Single Invitation
                            </h3>
                        </div>
                        <form id="singleInviteForm">
                            @csrf
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="email">Employee Email Address</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                                        </div>
                                        <input type="email" class="form-control" id="email" name="email"
                                            placeholder="employee@company.com" required>
                                    </div>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i>
                                        Employee will receive registration link via email
                                    </small>
                                </div>

                                <div class="alert alert-info">
                                    <h6><i class="fas fa-lightbulb"></i> What happens next?</h6>
                                    <ul class="mb-0 pl-3">
                                        <li>Employee receives secure registration link</li>
                                        <li>Link expires in 7 days</li>
                                        <li>Employee completes registration form</li>
                                        <li>You review and approve the submission</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-paper-plane mr-2"></i>
                                    Send Invitation
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Bulk Invitation Card -->
                <div class="col-md-6">
                    <div class="card card-success card-outline">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-users mr-2"></i>
                                Bulk Invitations
                            </h3>
                        </div>
                        <form id="bulkInviteForm">
                            @csrf
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="emails">Email Addresses</label>
                                    <textarea class="form-control" id="emails" name="emails" rows="8"
                                        placeholder="employee1@company.com&#10;employee2@company.com&#10;employee3@company.com" required></textarea>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i>
                                        Enter one email address per line
                                    </small>
                                </div>

                                <div class="alert alert-warning">
                                    <h6><i class="fas fa-exclamation-triangle"></i> Bulk Invitation Tips</h6>
                                    <ul class="mb-0 pl-3">
                                        <li>One email per line</li>
                                        <li>Invalid emails will be skipped</li>
                                        <li>Duplicate emails will be ignored</li>
                                        <li>All invitations expire in 7 days</li>
                                    </ul>
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-success btn-block">
                                    <i class="fas fa-paper-plane mr-2"></i>
                                    Send Bulk Invitations
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <!-- Bootstrap 4 -->
    <script src="{{ asset('assets/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <!-- bs-custom-file-input -->
    <script src="{{ asset('assets/plugins/bs-custom-file-input/bs-custom-file-input.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Update forms to use proper action URLs and remove AJAX
            $('#singleInviteForm').attr('action', '{{ route('employee.registration.admin.invite') }}');
            $('#singleInviteForm').attr('method', 'POST');

            $('#bulkInviteForm').attr('action', '{{ route('employee.registration.admin.bulk-invite') }}');
            $('#bulkInviteForm').attr('method', 'POST');

            // Refresh invitations table
            $('#refreshInvitations').on('click', function() {
                invitationsTable.ajax.reload();
                // Show simple success message without toast
                $(this).find('i').addClass('fa-spin');
                setTimeout(() => {
                    $(this).find('i').removeClass('fa-spin');
                }, 1000);
            });
        });
    </script>
@endsection
