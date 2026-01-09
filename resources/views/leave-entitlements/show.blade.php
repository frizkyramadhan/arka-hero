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
            @php
                // Get active administration (is_active = 1)
                $activeAdministration = $employee->administrations->where('is_active', 1)->first();

                // Calculate years of service based on termination reason logic:
                // - If termination_reason = "end of contract" → count from first DOH (continuity)
                // - If termination_reason != "end of contract" → count from DOH after termination (reset)
                $allAdministrations = $employee->administrations->whereNotNull('doh')->sortBy('doh')->values();

                $serviceStartDoh = null;
                $serviceStartNik = null;

                if ($allAdministrations->count() > 0) {
                    // Start with first DOH
                    $serviceStartDoh = $allAdministrations->first()->doh;
                    $serviceStartNik = $allAdministrations->first()->nik;

                    // Check each administration for termination reason
                    foreach ($allAdministrations as $index => $admin) {
                        // Check if this administration has termination
                        if ($admin->termination_date && $admin->termination_reason) {
                            // Normalize termination reason (case insensitive)
                            $terminationReason = strtolower(trim($admin->termination_reason));

                            // If termination reason is NOT "end of contract", reset calculation from next DOH
                            if ($terminationReason !== 'end of contract') {
                                // Find next administration after this termination
                                $nextAdmin = $allAdministrations->firstWhere(function ($next) use ($admin) {
                                    return $next->doh > $admin->termination_date;
                                });

                                if ($nextAdmin) {
                                    $serviceStartDoh = $nextAdmin->doh;
                                    $serviceStartNik = $nextAdmin->nik;
                                }
                            }
                            // If termination reason IS "end of contract", continue using first DOH (no reset)
                        }
                    }
                }
            @endphp

            <!-- Employee Information Card -->
            <div class="card card-outline card-info">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-user"></i> Employee Information</h3>
                    <div class="card-tools">
                        <a href="{{ route('leave.entitlements.index', ['project_id' => $activeAdministration->project_id ?? null]) }}"
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
                                    <td><strong>{{ $activeAdministration->nik ?? 'N/A' }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Name:</th>
                                    <td><strong>{{ $employee->fullname }}</strong></td>
                                </tr>
                                <tr>
                                    <th>Project:</th>
                                    <td>
                                        {{ $activeAdministration->project->project_code ?? 'N/A' }} -
                                        {{ $activeAdministration->project->project_name ?? 'N/A' }}
                                        @if ($activeAdministration->project ?? null)
                                            <span
                                                class="badge badge-{{ ($activeAdministration->project->leave_type ?? '') === 'roster' ? 'warning' : 'info' }}">
                                                {{ ucfirst($activeAdministration->project->leave_type ?? 'N/A') }}
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Level:</th>
                                    <td>{{ $activeAdministration->level->name ?? 'N/A' }}</td>
                                </tr>
                                <tr>
                                    <th>Position:</th>
                                    <td>{{ $activeAdministration->position->position_name ?? 'N/A' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h5 class="mb-3">Employment Details</h5>
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <th width="35%">DOH:</th>
                                    <td>
                                        @if ($serviceStartDoh)
                                            {{ \Carbon\Carbon::parse($serviceStartDoh)->format('d F Y') }}
                                            @if ($serviceStartNik && $activeAdministration && $serviceStartNik != $activeAdministration->nik)
                                                <br><small class="text-info"><i class="fas fa-info-circle"></i> From NIK: {{ $serviceStartNik }}</small>
                                            @endif
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <th>Years of Service:</th>
                                    <td>
                                        @if ($serviceStartDoh)
                                            @php
                                                $doh = \Carbon\Carbon::parse($serviceStartDoh);
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
                                    <th>Staff Type:</th>
                                    <td>
                                        @php
                                            $level = $activeAdministration->level ?? null;
                                            $levelName = $level ? $level->name : '';
                                            // Match the same logic as LeaveEntitlementController::isStaffLevel()
                                            $staffLevels = [
                                                'Director',
                                                'Manager',
                                                'Superintendent',
                                                'Supervisor',
                                                'Foreman/Officer',
                                                'Project Manager',
                                                'SPT',
                                                'SPV',
                                                'FM',
                                            ];
                                            $isStaff = in_array($levelName, $staffLevels);
                                            $staffType = $isStaff ? 'Staff' : 'Non-Staff';
                                        @endphp
                                        <span
                                            class="badge badge-{{ $isStaff ? 'info' : 'secondary' }}">{{ $staffType }}</span>
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
                        <a href="{{ route('leave.entitlements.employee.edit', $employee->id) }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add Entitlements
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if ($employee->leaveEntitlements->count() > 0)
                        @php
                            // Get available periods from entitlements
                            $allPeriods = $employee->leaveEntitlements->map(function ($entitlement) {
                                return [
                                    'year' => $entitlement->period_start->year,
                                    'period_start' => $entitlement->period_start,
                                    'period_end' => $entitlement->period_end,
                                    'display' =>
                                        $entitlement->period_start->year . '-' . $entitlement->period_end->year,
                                    'formatted_start' => $entitlement->period_start->format('d M Y'),
                                    'formatted_end' => $entitlement->period_end->format('d M Y'),
                                ];
                            });

                            // Group by unique period (same start and end date)
                            // Get only last 10 periods
                            $uniquePeriods = $allPeriods
                                ->unique(function ($period) {
                                    return $period['period_start']->format('Y-m-d') .
                                        '-' .
                                        $period['period_end']->format('Y-m-d');
                                })
                                ->sortByDesc('period_start')
                                ->take(10)
                                ->values();

                            // Get selected period (from URL or latest period)
                            $selectedPeriodKey = request('period');
                            $selectedPeriod = null;

                            if ($selectedPeriodKey) {
                                $selectedPeriod = $uniquePeriods->firstWhere(function ($period) use (
                                    $selectedPeriodKey,
                                ) {
                                    return $period['period_start']->format('Y-m-d') .
                                        '-' .
                                        $period['period_end']->format('Y-m-d') ===
                                        $selectedPeriodKey;
                                });
                            }

                            if (!$selectedPeriod) {
                                $selectedPeriod = $uniquePeriods->first();
                            }

                            // Filter entitlements by selected period
                            $filteredEntitlements = $employee->leaveEntitlements->filter(function ($entitlement) use (
                                $selectedPeriod,
                            ) {
                                return $entitlement->period_start->format('Y-m-d') ===
                                    $selectedPeriod['period_start']->format('Y-m-d') &&
                                    $entitlement->period_end->format('Y-m-d') ===
                                        $selectedPeriod['period_end']->format('Y-m-d');
                            });

                            $groupedEntitlements = $filteredEntitlements->groupBy('leaveType.category');
                        @endphp

                        <div class="row">
                            <!-- Left Column: Period List -->
                            <div class="col-md-4">
                                <div class="card card-outline card-primary">
                                    <div class="card-header">
                                        <h3 class="card-title"><i class="fas fa-list"></i> Available Periods</h3>
                                    </div>
                                    <div class="card-body p-0">
                                        <ul class="list-group list-group-flush" id="periodList">
                                            @foreach ($uniquePeriods as $period)
                                                @php
                                                    $periodKey =
                                                        $period['period_start']->format('Y-m-d') .
                                                        '-' .
                                                        $period['period_end']->format('Y-m-d');
                                                    $isActive =
                                                        $selectedPeriod &&
                                                        $selectedPeriod['period_start']->format('Y-m-d') ===
                                                            $period['period_start']->format('Y-m-d') &&
                                                        $selectedPeriod['period_end']->format('Y-m-d') ===
                                                            $period['period_end']->format('Y-m-d');
                                                    $periodCount = $employee->leaveEntitlements
                                                        ->filter(function ($ent) use ($period) {
                                                            return $ent->period_start->format('Y-m-d') ===
                                                                $period['period_start']->format('Y-m-d') &&
                                                                $ent->period_end->format('Y-m-d') ===
                                                                    $period['period_end']->format('Y-m-d');
                                                        })
                                                        ->count();
                                                @endphp
                                                <li class="list-group-item period-item {{ $isActive ? 'active' : '' }}"
                                                    data-period="{{ $periodKey }}"
                                                    onclick="selectPeriod('{{ $periodKey }}')"
                                                    style="cursor: pointer; padding: 0.5rem 0.75rem;">
                                                    <div class="d-flex justify-content-between align-items-center">
                                                        <div class="flex-grow-1">
                                                            <div class="d-flex align-items-center mb-1">
                                                                @if ($isActive)
                                                                    <i class="fas fa-check-circle text-white mr-2"></i>
                                                                @else
                                                                    <i class="fas fa-circle text-muted mr-2"
                                                                        style="font-size: 0.5rem;"></i>
                                                                @endif
                                                                <strong
                                                                    class="{{ $isActive ? 'text-white' : 'text-dark' }}"
                                                                    style="font-size: 0.9rem;">
                                                                    {{ $period['display'] }}
                                                                </strong>
                                                            </div>
                                                            <small class="{{ $isActive ? 'text-white-50' : 'text-muted' }}"
                                                                style="font-size: 0.75rem; display: block; line-height: 1.3;">
                                                                {{ $period['formatted_start'] }} -
                                                                {{ $period['formatted_end'] }}
                                                            </small>
                                                        </div>
                                                        <div class="ml-2">
                                                            <span class="badge badge-{{ $isActive ? 'light' : 'info' }}"
                                                                style="font-size: 0.75rem;">
                                                                {{ $periodCount }}
                                                            </span>
                                                        </div>
                                                    </div>
                                                    @if ($isActive)
                                                        <div class="mt-2 pt-2 border-top border-white border-opacity-50">
                                                            <a href="{{ route('leave.entitlements.employee.edit', ['employee' => $employee->id, 'period_start' => $period['period_start']->format('Y-m-d'), 'period_end' => $period['period_end']->format('Y-m-d')]) }}"
                                                                class="btn btn-sm btn-light btn-block btn-xs"
                                                                onclick="event.stopPropagation();"
                                                                style="font-size: 0.75rem; padding: 0.25rem 0.5rem;">
                                                                <i class="fas fa-edit"></i> Edit
                                                            </a>
                                                        </div>
                                                    @endif
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Column: Entitlement Details -->
                            <div class="col-md-8">
                                <div class="card card-outline card-success">
                                    <div class="card-header">
                                        <h3 class="card-title">
                                            <i class="fas fa-info-circle"></i>
                                            Entitlement Details
                                            @if ($selectedPeriod)
                                                <span class="badge badge-light ml-2">
                                                    {{ $selectedPeriod['formatted_start'] }} -
                                                    {{ $selectedPeriod['formatted_end'] }}
                                                </span>
                                            @endif
                                        </h3>
                                        <div class="card-tools">
                                            <span class="badge badge-info">
                                                <i class="fas fa-calendar"></i>
                                                {{ $filteredEntitlements->count() }} entitlements
                                            </span>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                        @if ($groupedEntitlements->count() > 0)
                                            @foreach ($groupedEntitlements as $category => $entitlements)
                                                <div class="card card-secondary card-outline mb-3">
                                                    <div class="card-header py-2">
                                                        <h6 class="card-title mb-0">
                                                            <i class="fas fa-list mr-2"></i>
                                                            {{ ucfirst($category) }} Leave
                                                            <span
                                                                class="badge badge-secondary ml-2">{{ $entitlements->count() }}</span>
                                                        </h6>
                                                    </div>
                                                    <div class="card-body p-0">
                                                        <div class="table-responsive">
                                                            <table class="table table-sm table-hover mb-0">
                                                                <thead class="thead-light">
                                                                    <tr>
                                                                        <th width="40%">Leave Type</th>
                                                                        <th width="15%" class="text-center">Entitled
                                                                        </th>
                                                                        <th width="15%" class="text-center">Taken</th>
                                                                        <th width="15%" class="text-center">Remaining
                                                                        </th>
                                                                        <th width="15%" class="text-center">Actions
                                                                        </th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach ($entitlements as $entitlement)
                                                                        <tr>
                                                                            <td>
                                                                                <b>{{ $entitlement->leaveType->name }}</b>
                                                                                @if ($entitlement->leaveType->code)
                                                                                    <br><small
                                                                                        class="text-muted">{{ $entitlement->leaveType->code }}</small>
                                                                                @endif
                                                                            </td>
                                                                            <td class="text-center">
                                                                                <strong
                                                                                    class="text-primary">{{ $entitlement->entitled_days }}</strong>
                                                                                <small
                                                                                    class="text-muted d-block">days</small>
                                                                            </td>
                                                                            <td class="text-center">
                                                                                <span
                                                                                    class="text-warning">{{ $entitlement->taken_days }}</span>
                                                                                <small
                                                                                    class="text-muted d-block">days</small>
                                                                            </td>
                                                                            <td class="text-center">
                                                                                <span
                                                                                    class="badge badge-{{ $entitlement->remaining_days > 0 ? 'success' : 'secondary' }} remaining-badge-lg">
                                                                                    {{ $entitlement->remaining_days }}
                                                                                </span>
                                                                                <small
                                                                                    class="text-muted d-block">days</small>
                                                                            </td>
                                                                            <td class="text-center">
                                                                                <a href="{{ route('leave.entitlements.employee.calculation-details', $employee) }}?leave_type_id={{ $entitlement->leave_type_id }}&period_start={{ $entitlement->period_start->format('Y-m-d') }}&period_end={{ $entitlement->period_end->format('Y-m-d') }}"
                                                                                    class="btn btn-sm btn-info"
                                                                                    title="Lihat Rincian Perhitungan">
                                                                                    <i class="fas fa-calculator"></i>
                                                                                </a>
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
                                                <h6 class="text-muted">No Entitlements Found</h6>
                                                <p class="text-muted">This employee doesn't have any leave entitlements for
                                                    the selected period.</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Leave Entitlements Found</h5>
                            <p class="text-muted">This employee doesn't have any leave entitlements yet.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection

@section('styles')
    <style>
        .period-item {
            border-left: 3px solid transparent;
            margin-bottom: 0.25rem;
        }

        .period-item:hover:not(.active) {
            background-color: #f8f9fa;
            border-left-color: #007bff;
        }

        .period-item.active {
            background-color: #007bff !important;
            border-left-color: #0056b3;
            color: white;
        }

        .period-item.active:hover {
            background-color: #0056b3 !important;
        }

        .remaining-badge-lg {
            font-size: 1rem;
            padding: 0.5rem 0.75rem;
            font-weight: 600;
        }

        #periodList {
            max-height: 600px;
            overflow-y: auto;
        }
    </style>
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

        function selectPeriod(periodKey) {
            // Get current URL and add/update period parameter
            const url = new URL(window.location);
            url.searchParams.set('period', periodKey);

            // Remove old 'year' parameter if exists
            url.searchParams.delete('year');

            // Navigate to the new URL
            window.location.href = url.toString();
        }
    </script>
@endsection
