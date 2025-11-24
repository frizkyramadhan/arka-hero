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
                        <li class="breadcrumb-item"><a href="{{ route('dashboard.personal') }}">Dashboard</a></li>
                        <li class="breadcrumb-item active">{{ $subtitle }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">{{ $subtitle }}</h3>
                            <div class="card-tools">
                                @can('personal.leave.create-own')
                                    <a href="{{ route('leave.requests.create') }}" class="btn btn-primary btn-sm">
                                        <i class="fas fa-plus"></i> New Leave Request
                                    </a>
                                @endcan
                            </div>
                        </div>
                        <div class="card-body">
                            <table class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th>Leave Type</th>
                                        <th>Period</th>
                                        <th>Days</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($leaveRequests as $request)
                                        <tr>
                                            <td>{{ $request->leaveType->name ?? 'N/A' }}</td>
                                            <td>{{ date('d M Y', strtotime($request->start_date)) }} -
                                                {{ date('d M Y', strtotime($request->end_date)) }}</td>
                                            <td>{{ $request->total_days }} days</td>
                                            <td>
                                                @switch($request->status)
                                                    @case('draft')
                                                        <span class="badge badge-secondary">Draft</span>
                                                    @break

                                                    @case('pending')
                                                        <span class="badge badge-warning">Pending</span>
                                                    @break

                                                    @case('approved')
                                                        <span class="badge badge-success">Approved</span>
                                                    @break

                                                    @case('rejected')
                                                        <span class="badge badge-danger">Rejected</span>
                                                    @break

                                                    @case('cancelled')
                                                        <span class="badge badge-dark">Cancelled</span>
                                                    @break

                                                    @case('closed')
                                                        <span class="badge badge-info">Closed</span>
                                                    @break

                                                    @default
                                                        <span class="badge badge-secondary">{{ ucfirst($request->status) }}</span>
                                                @endswitch
                                            </td>
                                            <td>
                                                <a href="{{ route('leave.requests.show', $request->id) }}"
                                                    class="btn btn-sm btn-info mr-1">
                                                    <i class="fas fa-eye"></i> View
                                                </a>
                                                @if (in_array($request->status, ['draft', 'pending']))
                                                    <a href="{{ route('leave.requests.edit', $request->id) }}"
                                                        class="btn btn-sm btn-warning mr-1">
                                                        <i class="fas fa-edit"></i> Edit
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                        @empty
                                            <tr>
                                                <td colspan="5" class="text-center text-muted">No leave requests found</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endsection

@endsection
