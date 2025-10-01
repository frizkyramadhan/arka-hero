@extends('layouts.main')

@section('title', 'Leave Request Details')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Leave Request Details</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('leave-requests.index') }}">Leave Requests</a></li>
                        <li class="breadcrumb-item active">Request #{{ $leaveRequest->id }}</li>
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
                            <h3 class="card-title">Request Information</h3>
                            <div class="card-tools">
                                @if ($leaveRequest->status === 'pending')
                                    <a href="{{ route('leave-requests.edit', $leaveRequest) }}"
                                        class="btn btn-primary btn-sm">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                @endif
                                <a href="{{ route('leave-requests.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-arrow-left"></i> Back
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="40%">Employee:</th>
                                            <td>{{ $leaveRequest->employee->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Leave Type:</th>
                                            <td>
                                                <span class="badge badge-info">{{ $leaveRequest->leaveType->name }}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Start Date:</th>
                                            <td>{{ $leaveRequest->start_date->format('d M Y') }}</td>
                                        </tr>
                                        <tr>
                                            <th>End Date:</th>
                                            <td>{{ $leaveRequest->end_date->format('d M Y') }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="40%">Total Days:</th>
                                            <td>{{ $leaveRequest->total_days }} days</td>
                                        </tr>
                                        <tr>
                                            <th>Status:</th>
                                            <td>
                                                @switch($leaveRequest->status)
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

                                                    @case('auto_approved')
                                                        <span class="badge badge-info">Auto Approved</span>
                                                    @break
                                                @endswitch
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Requested At:</th>
                                            <td>{{ $leaveRequest->requested_at ? $leaveRequest->requested_at->format('d M Y H:i') : 'N/A' }}
                                            </td>
                                        </tr>
                                        @if ($leaveRequest->back_to_work_date)
                                            <tr>
                                                <th>Back to Work:</th>
                                                <td>{{ $leaveRequest->back_to_work_date->format('d M Y') }}</td>
                                            </tr>
                                        @endif
                                    </table>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-12">
                                    <h5>Reason:</h5>
                                    <div class="alert alert-light">
                                        {{ $leaveRequest->reason }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Actions</h3>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                @if ($leaveRequest->status === 'pending')
                                    <button type="button" class="btn btn-success"
                                        onclick="approveRequest({{ $leaveRequest->id }})">
                                        <i class="fas fa-check"></i> Approve Request
                                    </button>
                                    <button type="button" class="btn btn-danger"
                                        onclick="rejectRequest({{ $leaveRequest->id }})">
                                        <i class="fas fa-times"></i> Reject Request
                                    </button>
                                @endif
                                <a href="{{ route('leave-requests.edit', $leaveRequest) }}" class="btn btn-primary">
                                    <i class="fas fa-edit"></i> Edit Request
                                </a>
                                <a href="{{ route('leave-entitlements.index', ['employee' => $leaveRequest->employee_id]) }}"
                                    class="btn btn-info">
                                    <i class="fas fa-calendar-alt"></i> View Entitlements
                                </a>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Employee Info</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tr>
                                    <th>Name:</th>
                                    <td>{{ $leaveRequest->employee->name }}</td>
                                </tr>
                                <tr>
                                    <th>Position:</th>
                                    <td>{{ $leaveRequest->employee->position->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Department:</th>
                                    <td>{{ $leaveRequest->employee->department->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Administration:</th>
                                    <td>{{ $leaveRequest->administration->name ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        function approveRequest(id) {
            if (confirm('Are you sure you want to approve this leave request?')) {
                fetch(`/leave/requests/${id}/approve`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                        },
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while approving the request.');
                    });
            }
        }

        function rejectRequest(id) {
            const reason = prompt('Please provide a reason for rejection:');
            if (reason) {
                fetch(`/leave/requests/${id}/reject`, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            reason: reason
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            location.reload();
                        } else {
                            alert('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        alert('An error occurred while rejecting the request.');
                    });
            }
        }
    </script>
@endpush
