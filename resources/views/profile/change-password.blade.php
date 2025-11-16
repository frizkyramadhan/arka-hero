@extends('layouts.main')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $title }}</h1>
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
            <div class="row">
                <!-- Update Profile Form - Left Column (8) -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-edit"></i> Update Profile</h3>
                        </div>
                        <form action="{{ route('profile.change-password') }}" method="POST">
                            @csrf
                            @method('PUT')
                            <div class="card-body">
                                <div class="form-group">
                                    <label for="name">Name</label>
                                    <input type="text" 
                                        class="form-control @error('name') is-invalid @enderror" 
                                        name="name" 
                                        id="name" 
                                        value="{{ old('name', $user->name) }}" 
                                        required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" 
                                        class="form-control" 
                                        name="email" 
                                        id="email" 
                                        value="{{ $user->email }}" 
                                        disabled>
                                    <small class="form-text text-muted">Email cannot be changed</small>
                                </div>

                                <hr>

                                <h5 class="mb-3"><i class="fas fa-key"></i> Change Password</h5>
                                <p class="text-muted">Leave password fields blank if you don't want to change your password.</p>

                                <div class="form-group">
                                    <label for="current_password">Current Password</label>
                                    <input type="password" 
                                        class="form-control @error('current_password') is-invalid @enderror" 
                                        name="current_password" 
                                        id="current_password">
                                    @error('current_password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="form-group">
                                    <label for="password">New Password</label>
                                    <input type="password" 
                                        class="form-control @error('password') is-invalid @enderror" 
                                        name="password" 
                                        id="password">
                                    @error('password')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <small class="form-text text-muted">Password must be at least 5 characters</small>
                                </div>

                                <div class="form-group">
                                    <label for="password_confirmation">Confirm New Password</label>
                                    <input type="password" 
                                        class="form-control" 
                                        name="password_confirmation" 
                                        id="password_confirmation">
                                </div>
                            </div>
                            <div class="card-footer">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Update Profile
                                </button>
                                <a href="{{ url('/') }}" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- User Information Card - Right Column (4) -->
                <div class="col-md-4">
                    <div class="card card-primary card-outline">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-user"></i> User Information</h3>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-4">Name</dt>
                                <dd class="col-sm-8">{{ $user->name }}</dd>

                                <dt class="col-sm-4">Email</dt>
                                <dd class="col-sm-8">{{ $user->email }}</dd>

                                <dt class="col-sm-4">Status</dt>
                                <dd class="col-sm-8">
                                    @if($user->user_status)
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-danger">Inactive</span>
                                    @endif
                                </dd>

                                @if($user->employee)
                                    <dt class="col-sm-4">Employee</dt>
                                    <dd class="col-sm-8">{{ $user->employee->fullname ?? 'N/A' }}</dd>
                                @endif

                                <dt class="col-sm-4">Roles</dt>
                                <dd class="col-sm-8">
                                    @if($user->roles->count() > 0)
                                        @foreach($user->roles as $role)
                                            <span class="badge badge-info">{{ $role->name }}</span>
                                        @endforeach
                                    @else
                                        <span class="text-muted">No roles assigned</span>
                                    @endif
                                </dd>

                                <dt class="col-sm-4">Member Since</dt>
                                <dd class="col-sm-8">{{ $user->created_at->format('d F Y') }}</dd>
                            </dl>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

