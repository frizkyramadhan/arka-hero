@extends('layouts.main')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0 font-weight-bold">{{ $title }}</h1>
                    <p class="text-muted mb-0 small">{{ $subtitle }}</p>
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
            <!-- Welcome Card -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="card card-outline card-primary shadow-sm">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h4 class="mb-1 text-primary">
                                        <i class="fas fa-user-circle mr-2"></i>
                                        Welcome back, {{ auth()->user()->name }}!
                                    </h4>
                                    <p class="text-muted mb-0">
                                        Manage your requests and track your activities from one place.
                                    </p>
                                </div>
                                <div class="col-md-4 text-right">
                                    <div class="btn-group">
                                        @can('personal.leave.create-own')
                                            <a href="{{ route('leave.my-requests.create') }}" class="btn btn-primary btn-sm">
                                                <i class="fas fa-calendar-plus mr-1"></i> Leave
                                            </a>
                                        @endcan
                                        @can('personal.official-travel.create-own')
                                            <a href="{{ route('officialtravels.my-travels') }}" class="btn btn-success btn-sm">
                                                <i class="fas fa-route mr-1"></i> Travel
                                            </a>
                                        @endcan
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <!-- Leave Requests -->
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card card-outline card-info shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted text-uppercase mb-1"
                                        style="font-size: 0.75rem; letter-spacing: 0.5px;">
                                        Leave Requests
                                    </h6>
                                    <h2 class="mb-0 font-weight-bold">{{ $leaveStats['total_requests'] }}</h2>
                                    <small class="text-muted">
                                        <i class="fas fa-check-circle text-success mr-1"></i>
                                        {{ $leaveStats['approved_requests'] }} Approved
                                    </small>
                                </div>
                                <div class="icon-circle bg-info">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-info" role="progressbar"
                                        style="width: {{ $leaveStats['total_requests'] > 0 ? ($leaveStats['approved_requests'] / $leaveStats['total_requests']) * 100 : 0 }}%"
                                        aria-valuenow="{{ $leaveStats['approved_requests'] }}" aria-valuemin="0"
                                        aria-valuemax="{{ $leaveStats['total_requests'] }}">
                                    </div>
                                </div>
                            </div>
                            <a href="{{ route('leave.my-requests') }}" class="btn btn-sm btn-info btn-block mt-3">
                                View Details <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Official Travels -->
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card card-outline card-success shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted text-uppercase mb-1"
                                        style="font-size: 0.75rem; letter-spacing: 0.5px;">
                                        Official Travels
                                    </h6>
                                    <h2 class="mb-0 font-weight-bold">{{ $travelStats['total_travels'] }}</h2>
                                    <small class="text-muted">
                                        <i class="fas fa-route text-success mr-1"></i>
                                        {{ $travelStats['upcoming_travels'] }} Upcoming
                                    </small>
                                </div>
                                <div class="icon-circle bg-success">
                                    <i class="fas fa-route"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-success" role="progressbar"
                                        style="width: {{ $travelStats['total_travels'] > 0 ? ($travelStats['upcoming_travels'] / $travelStats['total_travels']) * 100 : 0 }}%"
                                        aria-valuenow="{{ $travelStats['upcoming_travels'] }}" aria-valuemin="0"
                                        aria-valuemax="{{ $travelStats['total_travels'] }}">
                                    </div>
                                </div>
                            </div>
                            <a href="{{ route('officialtravels.my-travels') }}"
                                class="btn btn-sm btn-success btn-block mt-3">
                                View Details <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Recruitment Requests -->
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card card-outline card-warning shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted text-uppercase mb-1"
                                        style="font-size: 0.75rem; letter-spacing: 0.5px;">
                                        Recruitment
                                    </h6>
                                    <h2 class="mb-0 font-weight-bold">{{ $recruitmentStats['total_requests'] }}</h2>
                                    <small class="text-muted">
                                        <i class="fas fa-check-circle text-success mr-1"></i>
                                        {{ $recruitmentStats['approved_requests'] }} Approved
                                    </small>
                                </div>
                                <div class="icon-circle bg-warning">
                                    <i class="fas fa-user-tie"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-warning" role="progressbar"
                                        style="width: {{ $recruitmentStats['total_requests'] > 0 ? ($recruitmentStats['approved_requests'] / $recruitmentStats['total_requests']) * 100 : 0 }}%"
                                        aria-valuenow="{{ $recruitmentStats['approved_requests'] }}" aria-valuemin="0"
                                        aria-valuemax="{{ $recruitmentStats['total_requests'] }}">
                                    </div>
                                </div>
                            </div>
                            <a href="{{ route('recruitment.my-requests') }}" class="btn btn-sm btn-warning btn-block mt-3">
                                View Details <i class="fas fa-arrow-right ml-1"></i>
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Pending Approvals -->
                <div class="col-lg-3 col-md-6 mb-3">
                    <div class="card card-outline card-danger shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h6 class="text-muted text-uppercase mb-1"
                                        style="font-size: 0.75rem; letter-spacing: 0.5px;">
                                        Pending Approvals
                                    </h6>
                                    <h2 class="mb-0 font-weight-bold">{{ $pendingApprovals }}</h2>
                                    <small class="text-muted">
                                        <i class="fas fa-exclamation-circle text-danger mr-1"></i>
                                        Requires Action
                                    </small>
                                </div>
                                <div class="icon-circle bg-danger">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                            </div>
                            <div class="mt-3">
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-danger" role="progressbar"
                                        style="width: {{ $pendingApprovals > 0 ? 100 : 0 }}%"
                                        aria-valuenow="{{ $pendingApprovals }}" aria-valuemin="0" aria-valuemax="100">
                                    </div>
                                </div>
                            </div>
                            @can('personal.approval.view-pending')
                                <a href="{{ route('approval.requests.index') }}"
                                    class="btn btn-sm btn-danger btn-block mt-3">
                                    Review Now <i class="fas fa-arrow-right ml-1"></i>
                                </a>
                            @else
                                <button class="btn btn-sm btn-secondary btn-block mt-3" disabled>
                                    No Access <i class="fas fa-lock ml-1"></i>
                                </button>
                            @endcan
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Row -->
            <div class="row">
                <!-- Recent Leave Requests -->
                <div class="col-lg-6 mb-4">
                    <div class="card card-outline card-info shadow-sm">
                        <div class="card-header border-bottom">
                            <h3 class="card-title mb-0">
                                <i class="fas fa-calendar-check mr-2 text-info"></i>
                                Recent Leave Requests
                            </h3>
                            <div class="card-tools">
                                <a href="{{ route('leave.my-requests') }}" class="btn btn-sm btn-info">
                                    <i class="fas fa-list mr-1"></i> View All
                                </a>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            @forelse($recentLeaveRequests as $request)
                                <div class="list-group list-group-flush">
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1 font-weight-bold">
                                                    {{ $request->leaveType->name ?? 'N/A' }}
                                                </h6>
                                                <p class="mb-1 text-muted small">
                                                    <i class="far fa-calendar mr-1"></i>
                                                    {{ \Carbon\Carbon::parse($request->start_date)->format('M d') }} -
                                                    {{ \Carbon\Carbon::parse($request->end_date)->format('M d, Y') }}
                                                </p>
                                                <small class="text-muted">
                                                    <i class="far fa-clock mr-1"></i>
                                                    {{ $request->total_days }}
                                                    {{ $request->total_days == 1 ? 'day' : 'days' }}
                                                </small>
                                            </div>
                                            <div class="ml-3">
                                                <span
                                                    class="badge badge-lg
                                                @if ($request->status == 'approved') badge-success
                                                @elseif($request->status == 'pending') badge-warning
                                                @elseif($request->status == 'rejected') badge-danger
                                                @else badge-secondary @endif">
                                                    {{ ucfirst($request->status) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted mb-0">No leave requests found</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Current Leave Entitlements -->
                <div class="col-lg-6 mb-3">
                    <div class="card card-outline card-primary shadow-sm h-100 d-flex flex-column">
                        <div class="card-header border-bottom py-2">
                            <div class="d-flex justify-content-between align-items-center">
                                <h5 class="card-title mb-0">
                                    <i class="fas fa-calendar-week mr-2 text-primary"></i>
                                    Current Leave Entitlements
                                </h5>
                                <div class="d-flex align-items-center">
                                    <button type="button" class="btn btn-sm btn-outline-primary mr-2"
                                        id="toggleAllEntitlements">
                                        <i class="fas fa-chevron-down" id="toggleAllIcon"></i>
                                        <span id="toggleAllText">Expand All</span>
                                    </button>
                                    <a href="{{ route('leave.my-entitlements') }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-chart-pie mr-1"></i> Details
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-0 flex-grow-1" style="max-height: 470px; overflow-y: auto;">
                            <div class="accordion mb-0" id="entitlementsAccordion">
                                @forelse($leaveEntitlements as $index => $entitlement)
                                    @php
                                        $usagePercentage =
                                            $entitlement->entitled_days > 0
                                                ? ($entitlement->taken_days / $entitlement->entitled_days) * 100
                                                : 0;
                                        $collapseId = 'collapseEntitlement' . $index;
                                        $headingId = 'headingEntitlement' . $index;
                                        $isLast = $loop->last;
                                    @endphp
                                    <div class="card border-0 {{ !$isLast ? 'border-bottom' : '' }}">
                                        <div class="card-header p-2" id="{{ $headingId }}">
                                            <button class="btn btn-link btn-block text-left p-0 entitlement-toggle"
                                                type="button" data-toggle="collapse" data-target="#{{ $collapseId }}"
                                                aria-expanded="false" aria-controls="{{ $collapseId }}">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <h6 class="mb-0 font-weight-bold text-primary">
                                                        <i class="fas fa-calendar-check mr-1"></i>
                                                        {{ $entitlement->leaveType->name ?? 'N/A' }}
                                                    </h6>
                                                    <div class="d-flex align-items-center">
                                                        <span class="badge badge-success mr-2">
                                                            {{ $entitlement->remaining_days }} days
                                                        </span>
                                                        <i class="fas fa-chevron-down entitlement-chevron"></i>
                                                    </div>
                                                </div>
                                            </button>
                                        </div>
                                        <div id="{{ $collapseId }}" class="collapse"
                                            aria-labelledby="{{ $headingId }}">
                                            <div class="card-body p-3 pb-2">
                                                <div class="row text-center mb-2">
                                                    <div class="col-4">
                                                        <div class="border-right">
                                                            <div class="text-muted small mb-1">Entitled</div>
                                                            <div class="font-weight-bold">
                                                                {{ $entitlement->entitled_days }}</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-4">
                                                        <div class="border-right">
                                                            <div class="text-muted small mb-1">Used</div>
                                                            <div class="font-weight-bold text-warning">
                                                                {{ $entitlement->taken_days }}</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-4">
                                                        <div class="text-muted small mb-1">Remaining</div>
                                                        <div class="font-weight-bold text-success">
                                                            {{ $entitlement->remaining_days }}</div>
                                                    </div>
                                                </div>
                                                <div class="progress mb-2" style="height: 5px;">
                                                    <div class="progress-bar bg-success" role="progressbar"
                                                        style="width: {{ 100 - $usagePercentage }}%"
                                                        aria-valuenow="{{ 100 - $usagePercentage }}" aria-valuemin="0"
                                                        aria-valuemax="100">
                                                    </div>
                                                </div>
                                                <small class="text-muted mb-3 d-block">
                                                    <i class="far fa-calendar mr-1"></i>
                                                    Valid until {{ $entitlement->period_end->format('M d, Y') }}
                                                </small>
                                                @can('personal.leave.create-own')
                                                    @if ($entitlement->remaining_days > 0)
                                                        <a href="{{ route('leave.my-requests.create', ['leave_type' => $entitlement->leave_type_id]) }}"
                                                            class="btn btn-sm btn-primary btn-block">
                                                            <i class="fas fa-calendar-plus mr-1"></i> Request
                                                            {{ $entitlement->leaveType->name ?? 'Leave' }}
                                                        </a>
                                                    @else
                                                        <button class="btn btn-sm btn-secondary btn-block" disabled>
                                                            <i class="fas fa-ban mr-1"></i> No Balance Available
                                                        </button>
                                                    @endif
                                                @endcan
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="alert alert-info m-3" style="font-size: 0.95rem;">
                                        <div class="text-center">
                                            <i class="fas fa-info-circle mb-2" style="font-size: 1.3rem;"></i>
                                            <p class="mb-2 font-weight-bold">Leave balance/entitlement belum tersedia</p>
                                            <p class="mb-1" style="font-size: 0.9rem;">Silakan hubungi HR HO Balikpapan
                                                untuk mengatur leave entitlement Anda.</p>
                                            <p class= "mt-2">
                                                <i class="fas fa-phone-alt mr-1"></i>
                                                <strong>HR HO Balikpapan</strong>
                                            </p>
                                        </div>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Official Travels & Profile Completeness -->
            <div class="row mb-4">
                <!-- Recent Official Travels -->
                <div class="col-lg-6 mb-3 mb-lg-0">
                    <div class="card card-outline card-success shadow-sm h-100">
                        <div class="card-header border-bottom">
                            <h3 class="card-title mb-0">
                                <i class="fas fa-plane-departure mr-2 text-success"></i>
                                Recent Official Travels
                            </h3>
                            <div class="card-tools">
                                <a href="{{ route('officialtravels.my-travels') }}" class="btn btn-sm btn-success">
                                    <i class="fas fa-list mr-1"></i> View All
                                </a>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            @forelse($recentTravels as $travel)
                                @php
                                    $latestStop = $travel->stops
                                        ->sortByDesc(function ($stop) {
                                            $latestDate = null;
                                            if ($stop->arrival_at_destination) {
                                                $latestDate = $stop->arrival_at_destination;
                                            }
                                            if (
                                                $stop->departure_from_destination &&
                                                (!$latestDate || $stop->departure_from_destination->gt($latestDate))
                                            ) {
                                                $latestDate = $stop->departure_from_destination;
                                            }
                                            return $latestDate ? $latestDate->timestamp : 0;
                                        })
                                        ->first();

                                    $lastActivity = null;
                                    $lastActivityType = null;
                                    $lastActivityDate = null;

                                    if ($latestStop) {
                                        if (
                                            $latestStop->departure_from_destination &&
                                            $latestStop->arrival_at_destination
                                        ) {
                                            if (
                                                $latestStop->departure_from_destination->gt(
                                                    $latestStop->arrival_at_destination,
                                                )
                                            ) {
                                                $lastActivity = 'departure';
                                                $lastActivityType = 'Departure';
                                                $lastActivityDate = $latestStop->departure_from_destination;
                                            } else {
                                                $lastActivity = 'arrival';
                                                $lastActivityType = 'Arrival';
                                                $lastActivityDate = $latestStop->arrival_at_destination;
                                            }
                                        } elseif ($latestStop->departure_from_destination) {
                                            $lastActivity = 'departure';
                                            $lastActivityType = 'Departure';
                                            $lastActivityDate = $latestStop->departure_from_destination;
                                        } elseif ($latestStop->arrival_at_destination) {
                                            $lastActivity = 'arrival';
                                            $lastActivityType = 'Arrival';
                                            $lastActivityDate = $latestStop->arrival_at_destination;
                                        }
                                    }
                                @endphp
                                <div class="list-group list-group-flush">
                                    <div class="list-group-item">
                                        <div class="d-flex justify-content-between align-items-start">
                                            <div class="flex-grow-1">
                                                <h6 class="mb-1 font-weight-bold">
                                                    {{ $travel->destination }}
                                                </h6>
                                                <p class="mb-1 text-muted small">
                                                    <i class="far fa-calendar mr-1"></i>
                                                    {{ \Carbon\Carbon::parse($travel->official_travel_date)->format('M d, Y') }}
                                                </p>
                                                <small class="text-muted">
                                                    <i class="fas fa-building mr-1"></i>
                                                    {{ $travel->project->project_code ?? 'N/A' }}
                                                </small>
                                                @if ($lastActivityDate)
                                                    <small class="text-muted d-block mt-1">
                                                        <i
                                                            class="fas {{ $lastActivity == 'arrival' ? 'fa-plane-arrival' : 'fa-plane-departure' }} mr-1"></i>
                                                        <strong>{{ $lastActivityType }}:</strong>
                                                        {{ $lastActivityDate->format('M d, Y H:i') }}
                                                    </small>
                                                @endif
                                            </div>
                                            <div class="ml-3">
                                                <span
                                                    class="badge badge-lg
                                                @if ($travel->status == 'approved') badge-success
                                                @elseif($travel->status == 'submitted') badge-info
                                                @elseif($travel->status == 'rejected') badge-danger
                                                @elseif($travel->status == 'closed') badge-secondary
                                                @else badge-warning @endif">
                                                    {{ ucfirst($travel->status) }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-5">
                                    <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                    <p class="text-muted mb-0">No official travels found</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Profile Completeness -->
                <div class="col-lg-6">
                    <div class="card card-outline card-warning shadow-sm h-100 d-flex flex-column">
                        <div class="card-header border-bottom py-2">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-user-check mr-2 text-warning"></i>
                                Profile Completeness
                            </h5>
                        </div>
                        <div class="card-body flex-grow-1">
                            <div class="row align-items-center mb-3">
                                <div class="col-md-8">
                                    <h4 class="mb-1">
                                        <span class="font-weight-bold">{{ $completenessPercentage }}%</span>
                                        <small class="text-muted">Complete</small>
                                    </h4>
                                    <div class="progress" style="height: 25px;">
                                        <div class="progress-bar progress-bar-striped progress-bar-animated
                                            @if ($completenessPercentage >= 80) bg-success
                                            @elseif($completenessPercentage >= 50) bg-warning
                                            @else bg-danger @endif"
                                            role="progressbar" style="width: {{ $completenessPercentage }}%"
                                            aria-valuenow="{{ $completenessPercentage }}" aria-valuemin="0"
                                            aria-valuemax="100">
                                            {{ $completenessPercentage }}%
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-4 text-right">
                                    <a href="{{ route('profile.my-profile') }}" class="btn btn-warning btn-sm">
                                        <i class="fas fa-eye mr-1"></i> See Profile
                                    </a>
                                </div>
                            </div>

                            @if (count($missingSections) > 0)
                                <div class="alert alert-light border-left-warning"
                                    style="border-left: 4px solid #ffc107;">
                                    <h6 class="mb-2">
                                        <i class="fas fa-exclamation-triangle mr-2 text-warning"></i>
                                        Missing Information:
                                    </h6>
                                    <div class="row">
                                        @php
                                            $sectionLabels = [
                                                'bank' => 'Bank Account',
                                                'tax' => 'Tax Identification',
                                                'insurance' => 'Insurance',
                                                'license' => 'License',
                                                'family' => 'Family',
                                                'education' => 'Education',
                                                'course' => 'Course',
                                                'job' => 'Job Experience',
                                                'emergency' => 'Emergency Contact',
                                                'unit' => 'Operable Unit',
                                                'additional' => 'Additional Data',
                                            ];
                                        @endphp
                                        @foreach ($missingSections as $section)
                                            <div class="col-md-6 mb-2">
                                                <i class="fas fa-circle text-warning mr-2" style="font-size: 0.5rem;"></i>
                                                {{ $sectionLabels[$section] ?? ucfirst($section) }}
                                            </div>
                                        @endforeach
                                    </div>
                                    <p class="mb-0 mt-2 small text-muted">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Please contact HR Department to update your profile information.
                                    </p>
                                </div>
                            @else
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle mr-2"></i>
                                    <strong>Congratulations!</strong> Your profile is complete.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @push('styles')
        <style>
            /* Icon Circle */
            .icon-circle {
                width: 60px;
                height: 60px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                color: white;
                font-size: 1.5rem;
            }

            /* Card Enhancements */
            .card-outline {
                border-top: 3px solid;
            }

            .card-outline.shadow-sm {
                transition: transform 0.2s ease, box-shadow 0.2s ease;
            }

            .card-outline.shadow-sm:hover {
                transform: translateY(-2px);
                box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
            }

            /* Description Block */
            .description-block {
                text-align: center;
                padding: 0.5rem 0;
            }

            .description-block.border-right {
                border-right: 1px solid #dee2e6;
            }

            .description-block .description-percentage {
                font-size: 1.5rem;
                display: block;
                margin-bottom: 0.5rem;
            }

            .description-block .description-header {
                font-size: 1.5rem;
                font-weight: bold;
                margin: 0.25rem 0;
                color: #495057;
            }

            .description-block .description-text {
                font-size: 0.7rem;
                text-transform: uppercase;
                font-weight: 600;
                color: #6c757d;
                letter-spacing: 0.5px;
            }

            /* List Group Item Enhancements */
            .list-group-item {
                border-left: none;
                border-right: none;
                transition: background-color 0.2s ease;
            }

            .list-group-item:hover {
                background-color: #f8f9fa;
            }

            .list-group-item:first-child {
                border-top: none;
            }

            .list-group-item:last-child {
                border-bottom: none;
            }

            /* Badge Sizes */
            .badge-sm {
                font-size: 0.7rem;
                padding: 0.25rem 0.5rem;
                font-weight: 600;
            }

            .badge-lg {
                font-size: 0.875rem;
                padding: 0.5rem 0.75rem;
                font-weight: 600;
            }

            /* Button Enhancements */
            .btn-lg {
                padding: 1.25rem;
                font-size: 0.95rem;
                transition: transform 0.2s ease, box-shadow 0.2s ease;
            }

            .btn-lg:hover {
                transform: translateY(-2px);
            }

            .btn-lg i.fa-2x {
                font-size: 2rem;
            }

            /* Progress Bar */
            .progress {
                border-radius: 10px;
                overflow: hidden;
            }

            .progress-bar {
                transition: width 0.6s ease;
            }

            /* Border Left Primary */
            .border-left-primary {
                border-left: 4px solid #007bff !important;
            }

            /* Typography */
            .font-weight-bold {
                font-weight: 700 !important;
            }

            /* Responsive */
            @media (max-width: 768px) {
                .icon-circle {
                    width: 50px;
                    height: 50px;
                    font-size: 1.25rem;
                }

                .btn-lg {
                    padding: 1rem;
                    font-size: 0.875rem;
                }

                .description-block .description-header {
                    font-size: 1.25rem;
                }
            }
        </style>
    @endpush

    @push('scripts')
        <script>
            $(document).ready(function() {
                let allExpanded = false;
                const $toggleBtn = $('#toggleAllEntitlements');
                const $toggleIcon = $('#toggleAllIcon');
                const $toggleText = $('#toggleAllText');
                const $allCollapses = $('#entitlementsAccordion .collapse');
                const $allChevrons = $('.entitlement-chevron');

                // Toggle all accordions
                $toggleBtn.on('click', function() {
                    if (allExpanded) {
                        // Collapse all
                        $allCollapses.collapse('hide');
                        $allChevrons.removeClass('fa-chevron-up').addClass('fa-chevron-down');
                        $toggleIcon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
                        $toggleText.text('Expand All');
                        allExpanded = false;
                    } else {
                        // Expand all
                        $allCollapses.collapse('show');
                        $allChevrons.removeClass('fa-chevron-down').addClass('fa-chevron-up');
                        $toggleIcon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
                        $toggleText.text('Collapse All');
                        allExpanded = true;
                    }
                });

                // Update chevron on individual accordion toggle
                $allCollapses.on('show.bs.collapse', function() {
                    $(this).closest('.card').find('.entitlement-chevron')
                        .removeClass('fa-chevron-down').addClass('fa-chevron-up');
                });

                $allCollapses.on('hide.bs.collapse', function() {
                    $(this).closest('.card').find('.entitlement-chevron')
                        .removeClass('fa-chevron-up').addClass('fa-chevron-down');
                });

                // Update toggle button state when all are expanded/collapsed
                $allCollapses.on('shown.bs.collapse hidden.bs.collapse', function() {
                    const allShown = $allCollapses.filter(function() {
                        return $(this).hasClass('show');
                    }).length === $allCollapses.length;

                    const allHidden = $allCollapses.filter(function() {
                        return $(this).hasClass('show');
                    }).length === 0;

                    if (allShown) {
                        allExpanded = true;
                        $toggleIcon.removeClass('fa-chevron-down').addClass('fa-chevron-up');
                        $toggleText.text('Collapse All');
                    } else if (allHidden) {
                        allExpanded = false;
                        $toggleIcon.removeClass('fa-chevron-up').addClass('fa-chevron-down');
                        $toggleText.text('Expand All');
                    }
                });
            });
        </script>
    @endpush
@endsection
