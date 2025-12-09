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
                                            <td colspan="5">
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
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    @endif
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center">No data available for the selected period
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
                            <i class="fas fa-info-circle"></i> Information
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h6><strong>Terms:</strong></h6>
                                <ul class="list-unstyled">
                                    <li><strong>Total Requests:</strong> Number of leave requests</li>
                                    <li><strong>Total Days:</strong> Sum of all requested days</li>
                                    <li><strong>Effective Days:</strong> Actual days taken (excluding cancelled)</li>
                                </ul>
                            </div>
                            <div class="col-md-6">
                                <h6><strong>Note:</strong></h6>
                                <ul class="list-unstyled">
                                    <li><i class="fas fa-info-circle text-info"></i> Click "Details" to see breakdown by leave type</li>
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
