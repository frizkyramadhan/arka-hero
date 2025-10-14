@extends('layouts.main')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ $title }}</h1>
                </div>
                <div class="col-sm-6">
                    <div class="float-right">
                        <a href="{{ route('leave.reports.index') }}" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Back to Reports
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">
                        <i class="fas fa-filter"></i> Filter Options
                    </h5>
                </div>
                <div class="card-body">
                    <form method="GET" action="{{ route('leave.reports.cancellation') }}" class="row" id="filterForm">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="status">Cancellation Status</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending
                                    </option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>
                                        Approved</option>
                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>
                                        Rejected</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="start_date">Request Date From</label>
                                <input type="date" name="start_date" id="start_date" class="form-control"
                                    value="{{ request('start_date') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="end_date">Request Date To</label>
                                <input type="date" name="end_date" id="end_date" class="form-control"
                                    value="{{ request('end_date') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="employee_id">Employee</label>
                                <select name="employee_id" id="employee_id" class="form-control">
                                    <option value="">All Employees</option>
                                    @foreach ($employees as $employee)
                                        <option value="{{ $employee->id }}"
                                            {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                            {{ $employee->fullname }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </form>
                    <div class="row mt-2">
                        <div class="col-12">
                            <button type="submit" form="filterForm" class="btn btn-primary mr-2">
                                <i class="fas fa-search"></i> Filter
                            </button>
                            <a href="{{ route('leave.reports.cancellation') }}" class="btn btn-warning mr-2">
                                <i class="fas fa-undo"></i> Reset
                            </a>
                            <a href="{{ route('leave.reports.cancellation.export', request()->only('status', 'start_date', 'end_date', 'employee_id')) }}"
                                class="btn btn-success">
                                <i class="fas fa-file-excel"></i> Export Excel
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Leave Type</th>
                                    <th>Original Leave</th>
                                    <th class="text-center">Days to Cancel</th>
                                    <th class="text-center">Status</th>
                                    <th>Reason</th>
                                    <th>Requested By</th>
                                    <th>Requested At</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($cancellations as $cancellation)
                                    <tr>
                                        <td>
                                            <strong>{{ $cancellation->leaveRequest->employee->fullname }}</strong>
                                            <br><small
                                                class="text-muted">{{ $cancellation->leaveRequest->employee->employee_number }}</small>
                                        </td>
                                        <td>
                                            <span
                                                class="badge badge-info">{{ $cancellation->leaveRequest->leaveType->name }}</span>
                                        </td>
                                        <td>
                                            {{ $cancellation->leaveRequest->start_date->format('d M Y') }}
                                            <br><small class="text-muted">to
                                                {{ $cancellation->leaveRequest->end_date->format('d M Y') }}</small>
                                            <br><small class="text-muted">Total:
                                                {{ $cancellation->leaveRequest->total_days }} days</small>
                                        </td>
                                        <td class="text-center">
                                            <strong>{{ $cancellation->days_to_cancel }}</strong>
                                            @if ($cancellation->days_to_cancel == $cancellation->leaveRequest->total_days)
                                                <br><small class="text-danger">Full Cancellation</small>
                                            @else
                                                <br><small class="text-warning">Partial Cancellation</small>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @switch($cancellation->status)
                                                @case('pending')
                                                    <span class="badge badge-warning">Pending</span>
                                                @break

                                                @case('approved')
                                                    <span class="badge badge-success">Approved</span>
                                                @break

                                                @case('rejected')
                                                    <span class="badge badge-danger">Rejected</span>
                                                @break

                                                @default
                                                    <span class="badge badge-light">{{ ucfirst($cancellation->status) }}</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            <div style="max-width: 200px; word-wrap: break-word;">
                                                {{ $cancellation->reason }}
                                            </div>
                                        </td>
                                        <td>
                                            {{ $cancellation->requestedBy->name ?? 'Unknown' }}
                                        </td>
                                        <td>
                                            {{ $cancellation->requested_at->format('d M Y H:i') }}
                                            @if ($cancellation->confirmed_at)
                                                <br><small class="text-muted">Confirmed:
                                                    {{ $cancellation->confirmed_at->format('d M Y H:i') }}</small>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('leave.requests.show', $cancellation->leaveRequest->id) }}"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> View Leave
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center">No cancellation requests found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if ($cancellations->hasPages())
                        <div class="card-footer">
                            {{ $cancellations->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @endsection
