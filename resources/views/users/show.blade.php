@extends('layouts.main')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">User Details</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('users.index') }}">Users</a></li>
                        <li class="breadcrumb-item active">Show</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">User Information</h3>
                        </div>
                        <div class="card-body">
                            <dl class="row">
                                <dt class="col-sm-3">Name</dt>
                                <dd class="col-sm-9">{{ $user->name }}</dd>

                                <dt class="col-sm-3">Email</dt>
                                <dd class="col-sm-9">{{ $user->email }}</dd>

                                <dt class="col-sm-3">Status</dt>
                                <dd class="col-sm-9">
                                    @if ($user->user_status == '1')
                                        <span class="badge badge-success">Active</span>
                                    @else
                                        <span class="badge badge-danger">Inactive</span>
                                    @endif
                                </dd>

                                <dt class="col-sm-3">Roles</dt>
                                <dd class="col-sm-9">
                                    @forelse($user->roles as $role)
                                        <span class="badge badge-info mr-1">{{ $role->name }}</span>
                                    @empty
                                        <span class="text-muted">No roles assigned</span>
                                    @endforelse
                                </dd>
                            </dl>
                            <hr>
                            <h5>Permissions</h5>
                            <div class="border rounded p-2" style="background: #f8f9fa;">
                                @if ($permissions->count())
                                    @foreach ($permissions->sortBy('name') as $perm)
                                        <span class="badge badge-secondary mr-1 mb-1">{{ $perm->name }}</span>
                                    @endforeach
                                @else
                                    <span class="text-muted">No permissions</span>
                                @endif
                            </div>
                        </div>
                        <div class="card-footer text-right">
                            <a href="{{ route('users.index') }}" class="btn btn-secondary"><i class="fas fa-arrow-left"></i>
                                Back</a>
                            <a href="{{ route('users.edit', $user->id) }}" class="btn btn-warning"><i
                                    class="fas fa-edit"></i> Edit</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
