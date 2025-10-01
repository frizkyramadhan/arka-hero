@extends('layouts.main')

@section('title', 'Leave Type Details')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Leave Type Details</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('leave.types.index') }}">Leave Types</a></li>
                        <li class="breadcrumb-item active">{{ $leaveType->name }}</li>
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
                            <h3 class="card-title">Leave Type Information</h3>
                            <div class="card-tools">
                                <a href="{{ route('leave.types.edit', $leaveType) }}" class="btn btn-primary btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="{{ route('leave.types.index') }}" class="btn btn-secondary btn-sm">
                                    <i class="fas fa-arrow-left"></i> Back
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="40%">Name:</th>
                                            <td>{{ $leaveType->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Code:</th>
                                            <td><span class="badge badge-info">{{ $leaveType->code }}</span></td>
                                        </tr>
                                        <tr>
                                            <th>Category:</th>
                                            <td>
                                                @switch($leaveType->category)
                                                    @case('annual')
                                                        <span class="badge badge-success">Annual</span>
                                                    @break

                                                    @case('paid')
                                                        <span class="badge badge-warning">Paid</span>
                                                    @break

                                                    @case('unpaid')
                                                        <span class="badge badge-danger">Unpaid</span>
                                                    @break

                                                    @case('lsl')
                                                        <span class="badge badge-primary">Long Service Leave</span>
                                                    @break

                                                    @case('periodic')
                                                        <span class="badge badge-info">Periodic</span>
                                                    @break

                                                    @default
                                                        <span
                                                            class="badge badge-secondary">{{ ucfirst($leaveType->category) }}</span>
                                                @endswitch
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Default Days:</th>
                                            <td>{{ $leaveType->default_days }} days</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="40%">Eligible After:</th>
                                            <td>{{ $leaveType->eligible_after_years }} years</td>
                                        </tr>
                                        <tr>
                                            <th>Deposit Days (First):</th>
                                            <td>{{ $leaveType->deposit_days_first }} days</td>
                                        </tr>
                                        <tr>
                                            <th>Carry Over:</th>
                                            <td>
                                                @if ($leaveType->carry_over)
                                                    <span class="badge badge-success">Yes</span>
                                                @else
                                                    <span class="badge badge-danger">No</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Status:</th>
                                            <td>
                                                @if ($leaveType->is_active)
                                                    <span class="badge badge-success">Active</span>
                                                @else
                                                    <span class="badge badge-danger">Inactive</span>
                                                @endif
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            @if ($leaveType->remarks)
                                <div class="row mt-3">
                                    <div class="col-12">
                                        <h5>Remarks:</h5>
                                        <div class="alert alert-info">
                                            {{ $leaveType->remarks }}
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Statistics</h3>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-6">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-info"><i class="fas fa-calendar-check"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Entitlements</span>
                                            <span
                                                class="info-box-number">{{ $leaveType->leaveEntitlements()->count() }}</span>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="info-box">
                                        <span class="info-box-icon bg-warning"><i class="fas fa-file-alt"></i></span>
                                        <div class="info-box-content">
                                            <span class="info-box-text">Requests</span>
                                            <span class="info-box-number">{{ $leaveType->leaveRequests()->count() }}</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Quick Actions</h3>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('leave.types.edit', $leaveType) }}" class="btn btn-primary">
                                    <i class="fas fa-edit"></i> Edit Leave Type
                                </a>
                                <button type="button" class="btn btn-warning"
                                    onclick="toggleStatus({{ $leaveType->id }})">
                                    <i class="fas fa-toggle-{{ $leaveType->is_active ? 'off' : 'on' }}"></i>
                                    {{ $leaveType->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                                <a href="{{ route('leave.entitlements.index', ['leave_type' => $leaveType->id]) }}"
                                    class="btn btn-info">
                                    <i class="fas fa-calendar-alt"></i> View Entitlements
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    <script>
        function toggleStatus(id) {
            if (confirm('Are you sure you want to toggle the status of this leave type?')) {
                fetch(`/leave-types/${id}/toggle-status`, {
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
                        alert('An error occurred while updating the status.');
                    });
            }
        }
    </script>
@endpush
