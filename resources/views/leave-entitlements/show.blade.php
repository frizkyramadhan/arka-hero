@extends('layouts.main')

@section('title', 'Leave Entitlement Details')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Leave Entitlement Details</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('leave.entitlements.index') }}">Leave Entitlements</a>
                        </li>
                        <li class="breadcrumb-item active">Entitlement #{{ $entitlement->id }}</li>
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
                            <h3 class="card-title">Entitlement Information</h3>
                            <div class="card-tools">
                                <a href="{{ route('leave.entitlements.edit', $entitlement) }}"
                                    class="btn btn-primary btn-sm">
                                    <i class="fas fa-edit"></i> Edit
                                </a>
                                <a href="{{ route('leave.entitlements.index') }}" class="btn btn-secondary btn-sm">
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
                                            <td>{{ $entitlement->employee->name }}</td>
                                        </tr>
                                        <tr>
                                            <th>Leave Type:</th>
                                            <td>
                                                <span class="badge badge-info">{{ $entitlement->leaveType->name }}</span>
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Period Start:</th>
                                            <td>{{ $entitlement->period_start->format('d M Y') }}</td>
                                        </tr>
                                        <tr>
                                            <th>Period End:</th>
                                            <td>{{ $entitlement->period_end->format('d M Y') }}</td>
                                        </tr>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="40%">Entitled Days:</th>
                                            <td>{{ $entitlement->entitled_days }} days</td>
                                        </tr>
                                        <tr>
                                            <th>Withdrawable Days:</th>
                                            <td>{{ $entitlement->withdrawable_days }} days</td>
                                        </tr>
                                        <tr>
                                            <th>Deposit Days:</th>
                                            <td>{{ $entitlement->deposit_days }} days</td>
                                        </tr>
                                        <tr>
                                            <th>Carried Over:</th>
                                            <td>{{ $entitlement->carried_over }} days</td>
                                        </tr>
                                    </table>
                                </div>
                            </div>

                            <div class="row mt-3">
                                <div class="col-md-6">
                                    <table class="table table-borderless">
                                        <tr>
                                            <th width="40%">Taken Days:</th>
                                            <td>{{ $entitlement->taken_days }} days</td>
                                        </tr>
                                        <tr>
                                            <th>Remaining Days:</th>
                                            <td>
                                                <span
                                                    class="badge badge-{{ $entitlement->remaining_days > 0 ? 'success' : 'danger' }}">
                                                    {{ $entitlement->remaining_days }} days
                                                </span>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Quick Actions</h3>
                        </div>
                        <div class="card-body">
                            <div class="d-grid gap-2">
                                <a href="{{ route('leave.entitlements.edit', $entitlement) }}" class="btn btn-primary">
                                    <i class="fas fa-edit"></i> Edit Entitlement
                                </a>
                                <a href="{{ route('leave-requests.index', ['employee' => $entitlement->employee_id]) }}"
                                    class="btn btn-info">
                                    <i class="fas fa-file-alt"></i> View Requests
                                </a>
                                <a href="{{ route('leave.entitlements.index', ['employee' => $entitlement->employee_id]) }}"
                                    class="btn btn-secondary">
                                    <i class="fas fa-calendar-alt"></i> All Entitlements
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
                                    <td>{{ $entitlement->employee->name }}</td>
                                </tr>
                                <tr>
                                    <th>Position:</th>
                                    <td>{{ $entitlement->employee->position->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Department:</th>
                                    <td>{{ $entitlement->employee->department->name ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                    </div>

                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">Leave Type Info</h3>
                        </div>
                        <div class="card-body">
                            <table class="table table-sm">
                                <tr>
                                    <th>Name:</th>
                                    <td>{{ $entitlement->leaveType->name }}</td>
                                </tr>
                                <tr>
                                    <th>Code:</th>
                                    <td>{{ $entitlement->leaveType->code }}</td>
                                </tr>
                                <tr>
                                    <th>Category:</th>
                                    <td>{{ ucfirst($entitlement->leaveType->category) }}</td>
                                </tr>
                                <tr>
                                    <th>Default Days:</th>
                                    <td>{{ $entitlement->leaveType->default_days }} days</td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
