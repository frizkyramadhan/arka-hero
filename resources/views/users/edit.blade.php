@extends('layouts.main')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Edit User</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Users</a></li>
                        <li class="breadcrumb-item active">Edit</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <form action="{{ route('users.update', $user->id) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="row">
                    <div class="col-md-8">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">User Information</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="name">Name</label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                        name="name" id="name" value="{{ old('name', $user->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" class="form-control @error('email') is-invalid @enderror"
                                        name="email" id="email" value="{{ old('email', $user->email) }}" required>
                                    @error('email')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="password">Password <small class="text-muted">(Leave blank if not
                                            changing)</small></label>
                                    <input type="password" class="form-control @error('password') is-invalid @enderror"
                                        name="password" id="password">
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label for="user_status">Status</label>
                                    <select class="form-control @error('user_status') is-invalid @enderror"
                                        name="user_status" id="user_status" required>
                                        <option value="1"
                                            {{ old('user_status', $user->user_status) == '1' ? 'selected' : '' }}>Active
                                        </option>
                                        <option value="0"
                                            {{ old('user_status', $user->user_status) == '0' ? 'selected' : '' }}>Inactive
                                        </option>
                                    </select>
                                    @error('user_status')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label>Projects</label>
                                    <div class="row">
                                        @foreach ($projects as $project)
                                            <div class="col-md-6">
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" name="projects[]"
                                                        id="project_{{ $project->id }}" value="{{ $project->id }}"
                                                        {{ is_array(old('projects', $userProjectIds)) && in_array($project->id, old('projects', $userProjectIds)) ? 'checked' : '' }}>
                                                    <label class="form-check-label"
                                                        for="project_{{ $project->id }}">{{ $project->project_code }} :
                                                        {{ $project->project_name }}</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    @error('projects')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label>Departments</label>
                                    <div class="row">
                                        @foreach ($departments as $department)
                                            <div class="col-md-6">
                                                <div class="form-check mb-2">
                                                    <input class="form-check-input" type="checkbox" name="departments[]"
                                                        id="department_{{ $department->id }}"
                                                        value="{{ $department->id }}"
                                                        {{ is_array(old('departments', $userDepartmentIds)) && in_array($department->id, old('departments', $userDepartmentIds)) ? 'checked' : '' }}>
                                                    <label class="form-check-label"
                                                        for="department_{{ $department->id }}">{{ $department->department_name }}</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                    @error('departments')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label>Roles</label>
                                    <div class="row">
                                        @php
                                            $isAdministrator = auth()->user()->hasRole('administrator');
                                        @endphp
                                        @foreach ($roles as $role)
                                            @php
                                                $isProtectedRole = in_array($role->name, ['administrator']);
                                                $shouldShow = $isAdministrator || !$isProtectedRole;
                                            @endphp
                                            @if ($shouldShow)
                                                <div class="col-md-6">
                                                    <div class="form-check mb-2">
                                                        <input class="form-check-input role-checkbox" type="checkbox"
                                                            name="roles[]" id="role_{{ $role->id }}"
                                                            value="{{ $role->name }}"
                                                            {{ is_array(old('roles', $userRoleNames)) && in_array($role->name, old('roles', $userRoleNames)) ? 'checked' : '' }}>
                                                        <label class="form-check-label"
                                                            for="role_{{ $role->id }}">{{ $role->name }}</label>
                                                    </div>
                                                </div>
                                            @endif
                                        @endforeach
                                    </div>
                                    @error('roles')
                                        <div class="text-danger">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="form-group">
                                    <label>Permissions from Selected Roles</label>
                                    <div id="role-permissions-preview" class="border rounded p-2"
                                        style="min-height: 40px; background: #f8f9fa;"></div>
                                </div>
                            </div>
                            <div class="card-footer text-right">
                                <a href="{{ route('users.index') }}" class="btn btn-secondary"><i
                                        class="fas fa-arrow-left"></i> Back</a>
                                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Update
                                    User</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        // Data: role-permission mapping
        const rolePermissions = @json(
            $roles->mapWithKeys(function ($role) {
                return [$role->name => $role->permissions->pluck('name')];
            }));

        function updatePermissionPreview() {
            let selectedRoles = [];
            $('.role-checkbox:checked').each(function() {
                selectedRoles.push($(this).val());
            });
            let permissions = [];
            selectedRoles.forEach(function(role) {
                if (rolePermissions[role]) {
                    permissions = permissions.concat(rolePermissions[role]);
                }
            });
            // Remove duplicates
            permissions = [...new Set(permissions)];
            // Sort
            permissions.sort();

            // Group permissions by category
            let groupedPermissions = {};
            permissions.forEach(function(perm) {
                let category = perm.split('.')[0];
                if (!groupedPermissions[category]) {
                    groupedPermissions[category] = [];
                }
                groupedPermissions[category].push(perm);
            });

            let html = '';
            if (Object.keys(groupedPermissions).length > 0) {
                Object.keys(groupedPermissions).forEach(function(category) {
                    html += '<div class="mb-3">';
                    html += '<small class="text-muted text-capitalize">' + category.replace('-', ' ') +
                        '</small><br>';
                    html += '<div class="ml-3">';
                    groupedPermissions[category].forEach(function(perm) {
                        html += '<span class="badge badge-secondary mr-1 mb-1">' + perm + '</span>';
                    });
                    html += '</div>';
                    html += '</div>';
                });
            } else {
                html = '<span class="text-muted">No permissions</span>';
            }
            $('#role-permissions-preview').html(html);
        }

        $(document).ready(function() {
            $('.role-checkbox').on('change', updatePermissionPreview);
            updatePermissionPreview();
        });
    </script>
@endsection
