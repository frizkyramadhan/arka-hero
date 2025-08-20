@extends('layouts.main')

@section('content')
    <section class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1>{{ $subtitle }}</h1>
                </div>
                <div class="col-sm-6">
                    <div class="float-right">
                        <a href="{{ route('recruitment.reports.index') }}" class="btn btn-secondary">
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
                    <form method="GET" action="{{ route('recruitment.reports.funnel') }}" class="row" id="filterForm">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="date1">Date From</label>
                                <input type="date" name="date1" id="date1" class="form-control"
                                    value="{{ $date1 }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="date2">Date To</label>
                                <input type="date" name="date2" id="date2" class="form-control"
                                    value="{{ $date2 }}">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="department">Department</label>
                                <select name="department" id="department" class="form-control">
                                    <option value="">All Departments</option>
                                    @foreach (\App\Models\Department::orderBy('department_name')->get() as $dept)
                                        <option value="{{ $dept->id }}"
                                            {{ $department == $dept->id ? 'selected' : '' }}>
                                            {{ $dept->department_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="position">Position</label>
                                <select name="position" id="position" class="form-control">
                                    <option value="">All Positions</option>
                                    @foreach (\App\Models\Position::orderBy('position_name')->get() as $pos)
                                        <option value="{{ $pos->id }}" {{ $position == $pos->id ? 'selected' : '' }}>
                                            {{ $pos->position_name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="project">Project</label>
                                <select name="project" id="project" class="form-control">
                                    <option value="">All Projects</option>
                                    @foreach (\App\Models\Project::where('project_status', 1)->orderBy('project_code')->get() as $proj)
                                        <option value="{{ $proj->id }}" {{ $project == $proj->id ? 'selected' : '' }}>
                                            {{ $proj->project_name }}
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
                            <a href="{{ route('recruitment.reports.funnel') }}" class="btn btn-warning mr-2">
                                <i class="fas fa-undo"></i> Reset
                            </a>
                            <a href="{{ route('recruitment.reports.funnel.export', request()->only('date1', 'date2', 'department', 'position', 'project')) }}"
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
                                    <th>Stage</th>
                                    <th class="text-center">Flow Type</th>
                                    <th class="text-center">Previous Stage</th>
                                    <th class="text-center">Total Candidates</th>
                                    <th class="text-right">Conversion Rate</th>
                                    <th class="text-right">Avg Days in Stage</th>
                                    <th style="width:15%">Progress</th>
                                    <th class="text-center" style="width:10%">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rows as $row)
                                    <tr
                                        class="{{ isset($row['flow_type']) && $row['flow_type'] === 'technical_only' ? 'table-warning' : (isset($row['flow_type']) && $row['flow_type'] === 'combined' ? 'table-info' : '') }}">
                                        <td>
                                            <strong>
                                                @if (isset($row['stage_display']))
                                                    {{ $row['stage_display'] }}
                                                @else
                                                    {{ $row['stage'] }}
                                                @endif
                                            </strong>
                                            @if (isset($row['flow_type']) && $row['flow_type'] === 'technical_only')
                                                <br><small class="text-muted">
                                                    <i class="fas fa-cog"></i> Technical positions only
                                                </small>
                                            @elseif (isset($row['flow_type']) && $row['flow_type'] === 'combined')
                                                <br><small class="text-muted">
                                                    <i class="fas fa-merge"></i> Combined from both flows
                                                </small>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if (isset($row['flow_type']))
                                                @switch($row['flow_type'])
                                                    @case('standard')
                                                        <span class="badge badge-secondary">
                                                            <i class="fas fa-stream"></i> Standard
                                                        </span>
                                                    @break

                                                    @case('technical_only')
                                                        <span class="badge badge-warning">
                                                            <i class="fas fa-cog"></i> Technical
                                                        </span>
                                                    @break

                                                    @case('combined')
                                                        <span class="badge badge-info">
                                                            <i class="fas fa-merge"></i> Combined
                                                        </span>
                                                    @break

                                                    @default
                                                        <span class="badge badge-light">-</span>
                                                @endswitch
                                            @else
                                                <span class="badge badge-light">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if ($row['stage'] === 'CV Review')
                                                <span class="text-muted">-</span>
                                            @else
                                                <span
                                                    class="badge badge-secondary">{{ $row['previous_stage_count'] }}</span>
                                                @if (isset($row['flow_type']) && $row['flow_type'] === 'combined')
                                                    <br><small class="text-muted">
                                                        Tech: {{ $row['from_technical'] ?? 0 }} |
                                                        Non-Tech: {{ $row['from_non_technical'] ?? 0 }}
                                                    </small>
                                                @endif
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge badge-primary">{{ $row['total_candidates'] }}</span>
                                            @if (isset($row['eligible_candidates']) && $row['eligible_candidates'] > 0)
                                                <br><small class="text-muted">
                                                    from {{ $row['eligible_candidates'] }} eligible
                                                </small>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            @if ($row['conversion_rate'] > 0)
                                                @php
                                                    $rateClass =
                                                        $row['conversion_rate'] >= 70
                                                            ? 'text-success'
                                                            : ($row['conversion_rate'] >= 40
                                                                ? 'text-warning'
                                                                : 'text-danger');
                                                @endphp
                                                <span class="{{ $rateClass }}">
                                                    <strong>{{ $row['conversion_rate'] }}%</strong>
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-right">
                                            @if ($row['total_candidates'] > 0)
                                                @php
                                                    $avgDays = $row['avg_days_in_stage'] ?: 1;
                                                    $daysClass =
                                                        $avgDays <= 3
                                                            ? 'text-success'
                                                            : ($avgDays <= 7
                                                                ? 'text-warning'
                                                                : 'text-danger');
                                                @endphp
                                                <span class="{{ $daysClass }}">
                                                    {{ $avgDays }} days
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($row['conversion_rate'] > 0)
                                                <div class="progress" style="height: 20px;">
                                                    @php
                                                        $progressClass =
                                                            $row['conversion_rate'] >= 70
                                                                ? 'bg-success'
                                                                : ($row['conversion_rate'] >= 40
                                                                    ? 'bg-warning'
                                                                    : 'bg-danger');
                                                    @endphp
                                                    <div class="progress-bar {{ $progressClass }}" role="progressbar"
                                                        style="width: {{ min($row['conversion_rate'], 100) }}%"
                                                        aria-valuenow="{{ $row['conversion_rate'] }}" aria-valuemin="0"
                                                        aria-valuemax="100">
                                                        {{ $row['conversion_rate'] }}%
                                                    </div>
                                                </div>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <a href="{{ route('recruitment.reports.funnel.stage', ['stage' => strtolower(str_replace(' ', '_', $row['stage'])), 'date1' => $date1, 'date2' => $date2, 'department' => $department, 'position' => $position, 'project' => $project]) }}"
                                                class="btn btn-sm btn-outline-primary">
                                                <i class="fas fa-eye"></i> Details
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center">No data found</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    @if (count($rows) > 0)
                        <div class="card-footer">
                            <div class="row">
                                <div class="col-md-6">
                                    <small class="text-muted">
                                        <i class="fas fa-info-circle"></i>
                                        <strong>Conversion Rate:</strong> Percentage of candidates progressing from previous
                                        stage.
                                        <br>
                                        <strong>Colors:</strong>
                                        <span class="text-success">Green ≥70%</span>,
                                        <span class="text-warning">Yellow 40-69%</span>,
                                        <span class="text-danger">Red < 40%</span>
                                                <br>
                                                <strong>Days in Stage:</strong>
                                                <span class="text-success">Green ≤3 days</span>,
                                                <span class="text-warning">Yellow 4-7 days</span>,
                                                <span class="text-danger">Red >7 days</span>
                                    </small>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">
                                        <i class="fas fa-route"></i>
                                        <strong>Flow Types:</strong>
                                        <br>
                                        <span class="badge badge-secondary badge-sm">
                                            <i class="fas fa-stream"></i> Standard
                                        </span> All positions follow this stage
                                        <br>
                                        <span class="badge badge-warning badge-sm">
                                            <i class="fas fa-cog"></i> Technical
                                        </span> Only technical positions (requires theory test)
                                        <br>
                                        <span class="badge badge-info badge-sm">
                                            <i class="fas fa-merge"></i> Combined
                                        </span> Merged from both technical & non-technical flows
                                    </small>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @endsection
