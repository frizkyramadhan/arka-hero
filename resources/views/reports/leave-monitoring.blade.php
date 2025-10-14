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
                    <form method="GET" action="{{ route('leave.reports.monitoring') }}" class="row" id="filterForm">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="status">Status</label>
                                <select name="status" id="status" class="form-control">
                                    <option value="">All Status</option>
                                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending
                                    </option>
                                    <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>
                                        Approved</option>
                                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>
                                        Rejected</option>
                                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>
                                        Cancelled</option>
                                    <option value="closed" {{ request('status') == 'closed' ? 'selected' : '' }}>Closed
                                    </option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="start_date">Start Date</label>
                                <input type="date" name="start_date" id="start_date" class="form-control"
                                    value="{{ request('start_date') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="end_date">End Date</label>
                                <input type="date" name="end_date" id="end_date" class="form-control"
                                    value="{{ request('end_date') }}">
                            </div>
                        </div>
                        <div class="col-md-2">
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
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="leave_type_id">Leave Type</label>
                                <select name="leave_type_id" id="leave_type_id" class="form-control">
                                    <option value="">All Leave Types</option>
                                    @foreach ($leaveTypes as $leaveType)
                                        <option value="{{ $leaveType->id }}"
                                            {{ request('leave_type_id') == $leaveType->id ? 'selected' : '' }}>
                                            {{ $leaveType->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="project_id">Project</label>
                                <select name="project_id" id="project_id" class="form-control">
                                    <option value="">All Projects</option>
                                    @foreach ($projects as $project)
                                        <option value="{{ $project->id }}"
                                            {{ request('project_id') == $project->id ? 'selected' : '' }}>
                                            {{ $project->project_name }}
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
                            <a href="{{ route('leave.reports.monitoring') }}" class="btn btn-warning mr-2">
                                <i class="fas fa-undo"></i> Reset
                            </a>
                            <a href="{{ route('leave.reports.monitoring.export', request()->only('status', 'start_date', 'end_date', 'employee_id', 'leave_type_id', 'project_id')) }}"
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
                                    <th>Period</th>
                                    <th class="text-center">Days</th>
                                    <th class="text-center">Status</th>
                                    <th>Project</th>
                                    <th>Auto Conversion</th>
                                    <th>Document</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($leaveRequests as $request)
                                    <tr>
                                        <td>
                                            <strong>{{ $request->employee->fullname }}</strong>
                                            <br><small class="text-muted">{{ $request->employee->employee_number }}</small>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">{{ $request->leaveType->name }}</span>
                                            <br><small
                                                class="text-muted">{{ ucfirst($request->leaveType->category) }}</small>
                                        </td>
                                        <td>
                                            {{ $request->start_date->format('d M Y') }}
                                            <br><small class="text-muted">to
                                                {{ $request->end_date->format('d M Y') }}</small>
                                        </td>
                                        <td class="text-center">
                                            <strong>{{ $request->total_days }}</strong>
                                            @if ($request->getEffectiveDays() != $request->total_days)
                                                <br><small class="text-warning">Effective:
                                                    {{ $request->getEffectiveDays() }}</small>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @switch($request->status)
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
                                                    <span class="badge badge-secondary">Cancelled</span>
                                                @break

                                                @case('closed')
                                                    <span class="badge badge-dark">Closed</span>
                                                @break

                                                @default
                                                    <span class="badge badge-light">{{ ucfirst($request->status) }}</span>
                                            @endswitch
                                        </td>
                                        <td>
                                            {{ $request->administration->project->project_name ?? 'Unknown' }}
                                        </td>
                                        <td>
                                            @if ($request->auto_conversion_at)
                                                @php
                                                    $daysUntil = now()->diffInDays($request->auto_conversion_at, false);
                                                @endphp
                                                @if ($daysUntil < 0)
                                                    <span class="text-danger">
                                                        <i class="fas fa-exclamation-triangle"></i> Overdue
                                                        <br><small>{{ $request->auto_conversion_at->format('d M Y H:i') }}</small>
                                                    </span>
                                                @elseif($daysUntil <= 3)
                                                    <span class="text-warning">
                                                        <i class="fas fa-clock"></i> Due Soon
                                                        <br><small>{{ $request->auto_conversion_at->format('d M Y H:i') }}</small>
                                                    </span>
                                                @else
                                                    <span class="text-info">
                                                        <i class="fas fa-calendar"></i> {{ $daysUntil }} days
                                                        <br><small>{{ $request->auto_conversion_at->format('d M Y H:i') }}</small>
                                                    </span>
                                                @endif
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if (!empty($request->supporting_document))
                                                <span class="badge badge-success">
                                                    <i class="fas fa-check"></i> Yes
                                                </span>
                                            @else
                                                <span class="badge badge-warning">
                                                    <i class="fas fa-times"></i> No
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('leave.requests.show', $request->id) }}"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                        <tr>
                                            <td colspan="9" class="text-center">No leave requests found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if ($leaveRequests->hasPages())
                        <div class="card-footer">
                            {{ $leaveRequests->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @endsection
