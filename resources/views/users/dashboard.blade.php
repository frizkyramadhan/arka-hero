@extends('layouts.main')

@section('content')
    <!-- Content Header (Page header) -->
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $title }}</h1>
                </div><!-- /.col -->
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item active">{{ $title }}</li>
                    </ol>
                </div><!-- /.col -->
            </div><!-- /.row -->
        </div><!-- /.container-fluid -->
    </div>
    <!-- /.content-header -->

    <!-- Main content -->
    <section class="content">
        <div class="container-fluid">
            <!-- Statistics Cards -->
            <div class="row">
                <div class="col-lg-4 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ $stats['users'] }}</h3>
                            <p>Total Users</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <a href="{{ route('users.index') }}" class="small-box-footer">
                            Manage Users <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-4 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ $stats['roles'] }}</h3>
                            <p>Total Roles</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-user-tag"></i>
                        </div>
                        <a href="{{ route('roles.index') }}" class="small-box-footer">
                            Manage Roles <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
                <div class="col-lg-4 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ $stats['permissions'] }}</h3>
                            <p>Total Permissions</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-key"></i>
                        </div>
                        <a href="{{ route('permissions.index') }}" class="small-box-footer">
                            Manage Permissions <i class="fas fa-arrow-circle-right"></i>
                        </a>
                    </div>
                </div>
            </div>

            <!-- Main Dashboard Content -->
            <div class="row">
                <!-- Users Table -->
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <strong>Users Overview</strong>
                            </h3>
                        </div>
                        <div class="card-body">
                            <table id="users-dashboard-table" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>No</th>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Roles</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($users as $index => $user)
                                        <tr>
                                            <td>{{ $index + 1 }}</td>
                                            <td>{{ $user->name }}</td>
                                            <td>{{ $user->email }}</td>
                                            <td>
                                                @foreach ($user->roles as $role)
                                                    <span class="badge badge-info mr-1">{{ $role->name }}</span>
                                                @endforeach
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-primary view-user-details"
                                                    data-user-id="{{ $user->id }}">
                                                    <i class="fas fa-eye"></i> View Details
                                                </button>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row mt-4">
                <!-- Roles Table -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <strong>Roles Overview</strong>
                            </h3>
                        </div>
                        <div class="card-body">
                            <table id="roles-dashboard-table" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Role Name</th>
                                        <th>Users Count</th>
                                        <th>Permissions Count</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($roles as $role)
                                        <tr>
                                            <td>{{ $role->name }}</td>
                                            <td>{{ $role->users_count }}</td>
                                            <td>{{ $role->permissions_count }}</td>
                                            <td>
                                                <a href="{{ route('roles.edit', $role->id) }}"
                                                    class="btn btn-sm btn-warning">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Permissions Table -->
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <strong>Permissions Overview</strong>
                            </h3>
                        </div>
                        <div class="card-body">
                            <table id="permissions-dashboard-table" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Permission Name</th>
                                        <th>Used by Roles</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($permissions as $permission)
                                        <tr>
                                            <td>{{ $permission->name }}</td>
                                            <td>{{ $permission->roles_count }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- User Details Modal -->
    <div class="modal fade" id="user-details-modal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">User Details</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="user-details-content">
                        <!-- Content will be loaded here -->
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            // Initialize DataTables
            $('#users-dashboard-table').DataTable({
                responsive: true,
                autoWidth: false,
                lengthChange: true,
                lengthMenu: [
                    [10, 25, 50, -1],
                    ['10', '25', '50', 'Show all']
                ],
                dom: 'frtpi',
                columnDefs: [{
                    targets: [0, 4],
                    orderable: false
                }]
            });

            $('#roles-dashboard-table').DataTable({
                responsive: true,
                autoWidth: false,
                lengthChange: true,
                lengthMenu: [
                    [10, 25, 50, -1],
                    ['10', '25', '50', 'Show all']
                ],
                dom: 'frtpi',
                columnDefs: [{
                    targets: [3],
                    orderable: false
                }]
            });

            $('#permissions-dashboard-table').DataTable({
                responsive: true,
                autoWidth: false,
                lengthChange: true,
                lengthMenu: [
                    [10, 25, 50, -1],
                    ['10', '25', '50', 'Show all']
                ],
                dom: 'frtpi'
            });

            // Handle view user details
            $('.view-user-details').click(function() {
                var userId = $(this).data('user-id');

                // Show loading
                $('#user-details-content').html(
                    '<div class="text-center"><i class="fas fa-spinner fa-spin"></i> Loading...</div>');
                $('#user-details-modal').modal('show');

                // Fetch user details
                $.get('/users/' + userId + '/details', function(data) {
                    var html = '<p><strong>Name:</strong> ' + data.user.name + '</p>';
                    html += '<p><strong>Email:</strong> ' + data.user.email + '</p>';
                    html += '<hr>';

                    // Roles
                    html += '<h5>Roles</h5>';
                    html += '<p>';
                    if (data.user.roles.length > 0) {
                        data.user.roles.forEach(function(role) {
                            html += '<span class="badge badge-primary mr-1">' + role.name +
                                '</span>';
                        });
                    } else {
                        html += '<span class="text-muted">No roles assigned</span>';
                    }
                    html += '</p>';
                    html += '<hr>';

                    // Permissions grouped by category
                    html += '<h5>Permissions</h5>';
                    html += '<div class="permission-groups">';

                    if (Object.keys(data.permissions).length > 0) {
                        Object.keys(data.permissions).forEach(function(category) {
                            html += '<div class="mb-3">';
                            html += '<h6 class="text-capitalize">' + category.replace('-',
                                ' ') + '</h6>';
                            html += '<div class="ml-3">';
                            data.permissions[category].forEach(function(permission) {
                                html +=
                                    '<span class="badge badge-secondary mr-1 mb-1">' +
                                    permission.name + '</span>';
                            });
                            html += '</div>';
                            html += '</div>';
                        });
                    } else {
                        html += '<span class="text-muted">No permissions assigned</span>';
                    }

                    html += '</div>';

                    $('#user-details-content').html(html);
                }).fail(function() {
                    $('#user-details-content').html(
                        '<div class="alert alert-danger">Error loading user details</div>');
                });
            });
        });
    </script>
@endsection
