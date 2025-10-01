@extends('layouts.main')

@section('title', 'Employee Leave Entitlements - ' . $employee->fullname)

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">Employee Leave Entitlements</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('leave.entitlements.index') }}">Leave Entitlements</a>
                        </li>
                        <li class="breadcrumb-item active">{{ $employee->fullname }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- Employee Information Card -->
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user"></i> Employee Information</h3>
                    <div class="card-tools">
                        <a href="{{ route('leave.entitlements.index', ['project_id' => $employee->administrations->first()->project_id ?? null]) }}"
                            class="btn btn-warning">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="mb-3">Basic Information</h5>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th width="35%">NIK:</th>
                                    <td><strong>{{ $employee->administrations->first()->nik ?? 'N/A' }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Name:</th>
                                    <td><strong>{{ $employee->fullname }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Project:</th>
                                    <td>
                                        {{ $employee->administrations->first()->project->project_code ?? 'N/A' }} -
                                        {{ $employee->administrations->first()->project->project_name ?? 'N/A' }}
                                        @if ($employee->administrations->first()->project)
                                            <span
                                                class="badge badge-{{ $employee->administrations->first()->project->leave_type === 'roster' ? 'warning' : 'info' }}">
                                                {{ ucfirst($employee->administrations->first()->project->leave_type) }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Level:</th>
                                    <td>{{ $employee->administrations->first()->level->name ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5 class="mb-3">Employment Details</h5>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th width="35%">DOH:</th>
                                    <td>{{ $employee->administrations->first()->doh ? $employee->administrations->first()->doh->format('d F Y') : 'N/A' }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Years of Service:</th>
                                    <td>
                                        @if ($employee->administrations->first()->doh)
                                            @php
                                                $doh = \Carbon\Carbon::parse($employee->administrations->first()->doh);
                                                $monthsOfService = $doh->diffInMonths(now());
                                                $yearsOfService = round($monthsOfService / 12, 1);
                                            @endphp
                                            <strong>{{ $yearsOfService }} years</strong>
                                            <small class="text-muted">({{ $monthsOfService }} months)</small>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        <span class="badge badge-success">Active</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Project Group:</th>
                                    <td>
                                        @if ($employee->administrations->first()->project)
                                            <span
                                                class="badge badge-{{ $employee->administrations->first()->project->leave_type === 'roster' ? 'warning' : 'info' }}">
                                                {{ $employee->administrations->first()->project->leave_type === 'roster' ? 'Group 2 (Roster-Based)' : 'Group 1 (Regular)' }}
                                            </span>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Leave Entitlements Summary Card -->
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-calendar-alt"></i> Leave Entitlements Summary</h3>
                    <div class="card-tools">
                        <a href="{{ route('leave.entitlements.employee.edit', $employee->id) }}" class="btn btn-warning">
                            <i class="fas fa-edit"></i> Edit Entitlements
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if ($employee->leaveEntitlements->count() > 0)
                        @php
                            // Get available periods from entitlements
                            $availablePeriods = $employee->leaveEntitlements
                                ->map(function ($entitlement) {
                                    return [
                                        'year' => $entitlement->period_start->year,
                                        'period_start' => $entitlement->period_start,
                                        'period_end' => $entitlement->period_end,
                                        'display' =>
                                            $entitlement->period_start->year . '-' . $entitlement->period_end->year,
                                    ];
                                })
                                ->unique('year')
                                ->sortBy('year')
                                ->values();

                            // Get current year or first available year
                            $selectedYear = request('year', $availablePeriods->last()['year'] ?? now()->year);

                            // Filter entitlements by selected year
                            $filteredEntitlements = $employee->leaveEntitlements->filter(function ($entitlement) use (
                                $selectedYear,
                            ) {
                                return $entitlement->period_start->year == $selectedYear;
                            });

                            $groupedEntitlements = $filteredEntitlements->groupBy('leaveType.category');
                        @endphp

                        <!-- Year Filter -->
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="yearFilter" class="form-label">
                                        <i class="fas fa-filter"></i> Filter by Period Year:
                                    </label>
                                    <select id="yearFilter" class="select2bs4 form-control"
                                        onchange="filterByYear(this.value)">
                                        @foreach ($availablePeriods as $period)
                                            <option value="{{ $period['year'] }}"
                                                {{ $period['year'] == $selectedYear ? 'selected' : '' }}>
                                                {{ $period['display'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label class="form-label">&nbsp;</label>
                                    <div>
                                        <span class="badge badge-info">
                                            <i class="fas fa-calendar"></i>
                                            Showing {{ $filteredEntitlements->count() }} entitlements for
                                            {{ $availablePeriods->where('year', $selectedYear)->first()['display'] ?? $selectedYear }}
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if ($groupedEntitlements->count() > 0)
                            @foreach ($groupedEntitlements as $category => $entitlements)
                                <div class="card card-secondary card-outline mb-3">
                                    <div class="card-header py-2">
                                        <h6 class="card-title mb-0">
                                            <i class="fas fa-list mr-2"></i>
                                            {{ ucfirst($category) }} Leave
                                            <span class="badge badge-secondary ml-2">{{ $entitlements->count() }}</span>
                                        </h6>
                                    </div>
                                    <div class="card-body p-0">
                                        <div class="table-responsive">
                                            <table class="table table-sm table-hover mb-0">
                                                <thead class="thead-light">
                                                    <tr>
                                                        <th width="35%">Leave Type</th>
                                                        <th width="25%" class="text-center">Period</th>
                                                        <th width="15%" class="text-center">Entitled</th>
                                                        <th width="15%" class="text-center">Taken</th>
                                                        <th width="10%" class="text-center">Remaining</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach ($entitlements as $entitlement)
                                                        <tr>
                                                            <td>
                                                                <span
                                                                    class="badge badge-info">{{ $entitlement->leaveType->name }}</span>
                                                            </td>
                                                            <td class="text-center">
                                                                <small class="text-muted">
                                                                    {{ $entitlement->period_start->format('d M Y') }} -
                                                                    {{ $entitlement->period_end->format('d M Y') }}
                                                                </small>
                                                            </td>
                                                            <td class="text-center">
                                                                <strong>{{ $entitlement->entitled_days }}</strong>
                                                            </td>
                                                            <td class="text-center">
                                                                {{ $entitlement->taken_days }}
                                                            </td>
                                                            <td class="text-center">
                                                                <span
                                                                    class="badge badge-{{ $entitlement->remaining_days > 0 ? 'success' : 'secondary' }}">
                                                                    {{ $entitlement->remaining_days }}
                                                                </span>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="text-center py-4">
                                <i class="fas fa-calendar-times fa-2x text-muted mb-2"></i>
                                <h6 class="text-muted">No Entitlements Found for {{ $selectedYear }}</h6>
                                <p class="text-muted">This employee doesn't have any leave entitlements for the selected
                                    year.</p>
                                <small class="text-muted">Try selecting a different year from the filter above.</small>
                            </div>
                        @endif
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Leave Entitlements Found</h5>
                            <p class="text-muted">This employee doesn't have any leave entitlements yet.</p>
                            <a href="{{ route('leave.entitlements.employee.edit', $employee->id) }}"
                                class="btn btn-primary">
                                <i class="fas fa-plus"></i> Add Entitlements
                            </a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection

@section('scripts')
    <script>
        $(function() {
            $('.select2bs4').select2({
                theme: 'bootstrap4',
                width: '100%'
            }).on('select2:open', function() {
                document.querySelector('.select2-search__field').focus();
            });
        });

        function filterByYear(year) {
            // Get current URL and add/update year parameter
            const url = new URL(window.location);
            url.searchParams.set('year', year);

            // Navigate to the new URL
            window.location.href = url.toString();
        }
    </script>
@endsection
