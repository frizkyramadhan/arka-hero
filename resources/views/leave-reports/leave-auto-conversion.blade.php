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
                    <form method="GET" action="{{ route('leave.reports.auto-conversion') }}" class="row"
                        id="filterForm">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="conversion_status">Conversion Status</label>
                                <select name="conversion_status" id="conversion_status" class="form-control select2">
                                    <option value="">All Status</option>
                                    <option value="overdue"
                                        {{ request('conversion_status') == 'overdue' ? 'selected' : '' }}>Overdue</option>
                                    <option value="due_soon"
                                        {{ request('conversion_status') == 'due_soon' ? 'selected' : '' }}>Due Soon (≤3
                                        days)</option>
                                    <option value="upcoming"
                                        {{ request('conversion_status') == 'upcoming' ? 'selected' : '' }}>Upcoming (>3
                                        days)</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label for="employee_id">Employee</label>
                                <select name="employee_id" id="employee_id" class="form-control select2">
                                    <option value="">All Employees</option>
                                    @foreach ($employees as $employee)
                                        <option value="{{ $employee->id }}"
                                            {{ request('employee_id') == $employee->id ? 'selected' : '' }}>
                                            {{ $employee->administrations->first()->nik ?? 'N/A' }} -
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
                            <a href="{{ route('leave.reports.auto-conversion', ['show_all' => 1]) }}"
                                class="btn btn-info mr-2">
                                <i class="fas fa-list"></i> Show All
                            </a>
                            <a href="{{ route('leave.reports.auto-conversion') }}" class="btn btn-warning mr-2">
                                <i class="fas fa-undo"></i> Reset
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
                                    <th>Leave Period</th>
                                    <th class="text-center">Days</th>
                                    <th class="text-center">LSL Details</th>
                                    <th>Project</th>
                                    <th>Auto Conversion Date</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-center">Document</th>
                                    <th class="text-center">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($autoConversions as $conversion)
                                    <tr
                                        class="{{ $conversion['conversion_status'] == 'overdue' ? 'table-danger' : ($conversion['conversion_status'] == 'due_soon' ? 'table-warning' : '') }}">
                                        <td>
                                            <strong>{{ $conversion['employee_name'] }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-info">{{ $conversion['leave_type_name'] }}</span>
                                        </td>
                                        <td>
                                            {{ $conversion['start_date'] }}
                                            <br><small class="text-muted">to {{ $conversion['end_date'] }}</small>
                                        </td>
                                        <td class="text-center">
                                            <strong>{{ $conversion['total_days'] }}</strong>
                                        </td>
                                        <td class="text-center">
                                            @if (!empty($conversion['lsl_details']) && $conversion['lsl_details'] !== '-')
                                                <div class="lsl-info">
                                                    <small class="text-primary">
                                                        <i class="fas fa-calendar-check"></i>
                                                        {{ $conversion['lsl_details'] }}
                                                    </small>
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            {{ $conversion['project_name'] }}
                                        </td>
                                        <td>
                                            {{ $conversion['auto_conversion_at'] }}
                                            <br><small class="text-muted">Requested:
                                                {{ $conversion['created_at'] }}</small>
                                        </td>
                                        <td class="text-center">
                                            @switch($conversion['conversion_status'])
                                                @case('overdue')
                                                    <span class="badge badge-danger">
                                                        <i class="fas fa-exclamation-triangle"></i> Overdue
                                                        <br><small>{{ abs($conversion['days_until_conversion']) }} days ago</small>
                                                    </span>
                                                @break

                                                @case('due_soon')
                                                    <span class="badge badge-warning">
                                                        <i class="fas fa-clock"></i> Due Soon
                                                        <br><small>{{ $conversion['days_until_conversion'] }} days left</small>
                                                    </span>
                                                @break

                                                @case('upcoming')
                                                    <span class="badge badge-info">
                                                        <i class="fas fa-calendar"></i> Upcoming
                                                        <br><small>{{ $conversion['days_until_conversion'] }} days left</small>
                                                    </span>
                                                @break
                                            @endswitch
                                        </td>
                                        <td class="text-center">
                                            @if ($conversion['has_document'])
                                                <span class="badge badge-success">
                                                    <i class="fas fa-check"></i> Yes
                                                </span>
                                            @else
                                                <span class="badge badge-danger">
                                                    <i class="fas fa-times"></i> No
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('leave.requests.show', $conversion['id']) }}"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                        <tr>
                                            <td colspan="10" class="text-center">No auto conversion requests found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if ($autoConversions->hasPages())
                        <div class="card-footer">
                            {{ $autoConversions->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>

                @if (count($autoConversions) > 0)
                    <div class="card mt-3">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-info-circle"></i> Auto Conversion Information
                            </h5>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <h6><strong>Auto Conversion Rules:</strong></h6>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-info-circle text-info"></i> Paid leave requests without supporting
                                            documents</li>
                                        <li><i class="fas fa-info-circle text-info"></i> Auto conversion deadline: 12 days after
                                            request creation</li>
                                        <li><i class="fas fa-info-circle text-info"></i> Conversion changes leave type from paid
                                            to unpaid</li>
                                        <li><i class="fas fa-info-circle text-info"></i> Document upload cancels auto conversion
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <h6><strong>Status Indicators:</strong></h6>
                                    <ul class="list-unstyled">
                                        <li><span class="badge badge-danger">Red</span> Overdue - Conversion should have
                                            happened</li>
                                        <li><span class="badge badge-warning">Yellow</span> Due Soon - Conversion within 3 days
                                        </li>
                                        <li><span class="badge badge-info">Blue</span> Upcoming - Conversion more than 3 days
                                            away</li>
                                    </ul>
                                    <h6><strong>Action Required:</strong></h6>
                                    <ul class="list-unstyled">
                                        <li><i class="fas fa-exclamation-triangle text-danger"></i> Follow up with employees
                                            for
                                            overdue conversions</li>
                                        <li><i class="fas fa-clock text-warning"></i> Send reminders for due soon conversions
                                        </li>
                                        <li><i class="fas fa-file-upload text-info"></i> Encourage document upload to prevent
                                            conversion</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </section>
    @endsection

    @section('styles')
        <!-- Select2 -->
        <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    @endsection

    @section('scripts')
        <!-- Select2 -->
        <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
        <script>
            $(document).ready(function() {
                // Initialize Select2
                $('.select2').select2({
                    theme: 'bootstrap4',
                    width: '100%'
                });
            });
        </script>
    @endsection
