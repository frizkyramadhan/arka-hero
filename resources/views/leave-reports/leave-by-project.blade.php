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
                    <form method="GET" action="{{ route('leave.reports.by-project') }}" class="row" id="filterForm">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="start_date">Start Date</label>
                                <input type="date" name="start_date" id="start_date" class="form-control"
                                    value="{{ request('start_date') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="end_date">End Date</label>
                                <input type="date" name="end_date" id="end_date" class="form-control"
                                    value="{{ request('end_date') }}">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label for="project_id">Project</label>
                                <select name="project_id" id="project_id" class="form-control select2">
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
                            <a href="{{ route('leave.reports.by-project', ['show_all' => 1]) }}" class="btn btn-info mr-2">
                                <i class="fas fa-list"></i> Show All
                            </a>
                            <a href="{{ route('leave.reports.by-project') }}" class="btn btn-warning mr-2">
                                <i class="fas fa-undo"></i> Reset
                            </a>
                            <a href="{{ route('leave.reports.export', ['type' => 'by_project', 'start_date' => request('start_date'), 'end_date' => request('end_date'), 'project_id' => request('project_id')]) }}"
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
                                    <th>Project Name</th>
                                    <th class="text-center">Total Requests</th>
                                    <th class="text-center">Total Days</th>
                                    <th class="text-center">Effective Days</th>
                                    <th class="text-center">Cancelled Days</th>
                                    <th class="text-center">LSL Stats</th>
                                    <th class="text-center">Utilization Rate</th>
                                    <th class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($projectData as $project)
                                    <tr>
                                        <td>
                                            <strong>{{ $project['project_name'] }}</strong>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-info">{{ $project['total_requests'] }}</span>
                                        </td>
                                        <td class="text-center">
                                            <strong>{{ $project['total_days'] }}</strong>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-success">{{ $project['effective_days'] }}</span>
                                        </td>
                                        <td class="text-center">
                                            @if ($project['cancelled_days'] > 0)
                                                <span class="badge badge-warning">{{ $project['cancelled_days'] }}</span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if (isset($project['lsl_stats']) && $project['lsl_stats']['total_lsl_requests'] > 0)
                                                <div class="lsl-stats">
                                                    <small class="text-primary">
                                                        <i class="fas fa-calendar-check"></i>
                                                        {{ $project['lsl_stats']['total_lsl_leave_days'] }}d
                                                    </small>
                                                    @if ($project['lsl_stats']['total_lsl_cashout_days'] > 0)
                                                        <br><small class="text-warning">
                                                            <i class="fas fa-money-bill-wave"></i>
                                                            {{ $project['lsl_stats']['total_lsl_cashout_days'] }}d
                                                        </small>
                                                    @endif
                                                    <br><small class="text-success">
                                                        <strong>{{ $project['lsl_stats']['total_lsl_requests'] }} req
                                                            ({{ $project['lsl_stats']['total_lsl_days'] }}d)
                                                        </strong>
                                                    </small>
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $utilization =
                                                    $project['total_days'] > 0
                                                        ? round(
                                                            ($project['effective_days'] / $project['total_days']) * 100,
                                                            2,
                                                        )
                                                        : 0;
                                            @endphp
                                            @if ($utilization >= 80)
                                                <span class="badge badge-success">{{ $utilization }}%</span>
                                            @elseif($utilization >= 50)
                                                <span class="badge badge-warning">{{ $utilization }}%</span>
                                            @else
                                                <span class="badge badge-danger">{{ $utilization }}%</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-outline-primary"
                                                onclick="toggleDetails('{{ str_replace([' ', '-'], '_', $project['project_name']) }}')">
                                                <i class="fas fa-eye"></i> Details
                                            </button>
                                        </td>
                                    </tr>
                                    <!-- Leave Type Breakdown -->
                                    @if (isset($project['by_type']) && count($project['by_type']) > 0)
                                        <tr id="details-{{ str_replace([' ', '-'], '_', $project['project_name']) }}"
                                            class="details-row" style="display: none;">
                                            <td colspan="7">
                                                <div class="ml-4">
                                                    <h6 class="text-muted">Breakdown by Leave Type:</h6>
                                                    <div class="table-responsive">
                                                        <table class="table table-sm table-borderless">
                                                            <thead>
                                                                <tr>
                                                                    <th>Leave Type</th>
                                                                    <th class="text-center">Requests</th>
                                                                    <th class="text-center">Total Days</th>
                                                                    <th class="text-center">Effective Days</th>
                                                                    <th class="text-center">Cancelled Days</th>
                                                                    <th class="text-center">Utilization Rate</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($project['by_type'] as $typeName => $typeData)
                                                                    <tr>
                                                                        <td>
                                                                            <i
                                                                                class="fas fa-level-up-alt fa-rotate-90"></i>
                                                                            {{ $typeName }}
                                                                        </td>
                                                                        <td class="text-center">{{ $typeData['count'] }}
                                                                        </td>
                                                                        <td class="text-center">
                                                                            {{ $typeData['total_days'] }}</td>
                                                                        <td class="text-center">
                                                                            {{ $typeData['effective_days'] }}</td>
                                                                        <td class="text-center">
                                                                            {{ $typeData['cancelled_days'] }}</td>
                                                                        <td class="text-center">
                                                                            <span
                                                                                class="badge badge-{{ $typeData['utilization_rate'] > 80 ? 'success' : ($typeData['utilization_rate'] > 60 ? 'warning' : 'danger') }}">
                                                                                {{ $typeData['utilization_rate'] }}%
                                                                            </span>
                                                                        </td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @else
                                        <!-- Debug: Show if by_type is empty -->
                                        <tr id="details-{{ str_replace([' ', '-'], '_', $project['project_name']) }}"
                                            class="details-row" style="display: none;">
                                            <td colspan="7">
                                                <div class="ml-4">
                                                    <div class="alert alert-info">
                                                        <i class="fas fa-info-circle"></i>
                                                        No breakdown data available for this project.
                                                        All leave requests may be of the same type.
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">No data available for the selected period
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            @if (count($projectData) > 0)
                <div class="card mt-3">
                    <div class="card-header">
                        <h5 class="card-title mb-0">
                            <i class="fas fa-info-circle"></i> Project Analysis Legend
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><strong>Metrics Breakdown:</strong></h6>
                                <ul class="list-unstyled">
                                    <li><strong>Total Requests:</strong> Number of leave requests submitted</li>
                                    <li><strong>Total Days:</strong> Sum of all requested leave days</li>
                                    <li><strong>Effective Days:</strong> Actual days taken (excluding cancelled)</li>
                                    <li><strong>Cancelled Days:</strong> Days cancelled from approved requests</li>
                                    <li><strong>Utilization Rate:</strong> Effective days vs total days percentage</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6><strong>Utilization Rate:</strong></h6>
                                <ul class="list-unstyled">
                                    <li><span class="badge badge-success">Green ≥80%</span> High utilization</li>
                                    <li><span class="badge badge-warning">Yellow 50-79%</span> Moderate utilization</li>
                                    <li><span class="badge badge-danger">Red <50%< /span> Low utilization</li>
                                </ul>
                                <h6><strong>Special Notes:</strong></h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-info-circle text-info"></i> Click "Details" to see breakdown by
                                        leave type</li>
                                    <li><i class="fas fa-info-circle text-info"></i> Cancelled days are excluded from
                                        effective calculation</li>
                                    <li><i class="fas fa-info-circle text-info"></i> Utilization rate helps identify
                                        project workload patterns</li>
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

            // Auto-submit form on date change
            $('input[name="start_date"], input[name="end_date"]').change(function() {
                $(this).closest('form').submit();
            });

            // Toggle details function
            window.toggleDetails = function(projectName) {
                $('#details-' + projectName).toggle();
            };
        });
    </script>
@endsection
