@extends('layouts.main')

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $title }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="#">Home</a></li>
                        <li class="breadcrumb-item active">{{ $subtitle }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">

            <!-- Summary Overview Card -->
            <div class="card mb-4">
                <div class="card-header bg-gradient-dark">
                    <h3 class="card-title text-white">
                        <i class="fas fa-chart-line"></i> Letter Administration Summary
                        <small class="text-white-50 ml-2">Overview & Key Metrics</small>
                    </h3>
                    <div class="card-tools">
                        <a href="{{ route('letter-numbers.index') }}" class="btn btn-sm btn-outline-light">
                            <i class="fas fa-list mr-1"></i>View All Letters
                        </a>
                    </div>
                </div>
                <div class="card-body py-2">
                    <div class="row text-center">
                        @php
                            $stats = [
                                [
                                    'icon' => 'fa-file-alt',
                                    'bg' => 'primary',
                                    'val' => $totalLetters,
                                    'label' => 'Total Letters',
                                ],
                                [
                                    'icon' => 'fa-clock',
                                    'bg' => 'warning',
                                    'val' => $reservedLetters,
                                    'label' => 'Reserved',
                                ],
                                [
                                    'icon' => 'fa-check-circle',
                                    'bg' => 'success',
                                    'val' => $usedLetters,
                                    'label' => 'Used',
                                ],
                                [
                                    'icon' => 'fa-calendar-alt',
                                    'bg' => 'info',
                                    'val' => $thisMonthLetters,
                                    'label' => 'This Month',
                                ],
                            ];
                        @endphp
                        @foreach ($stats as $s)
                            <div class="col-6 col-md-3 mb-2">
                                <div
                                    class="d-flex align-items-center justify-content-center border rounded bg-light py-2 px-1 h-100">
                                    <span class="badge bg-{{ $s['bg'] }} mr-2"
                                        style="font-size:1.3rem;padding:.5rem .7rem;">
                                        <i class="fas {{ $s['icon'] }} text-white"></i>
                                    </span>
                                    <div class="text-left">
                                        <div class="font-weight-bold text-{{ $s['bg'] }}" style="font-size:1.1rem;">
                                            {{ number_format($s['val']) }}
                                        </div>
                                        <small class="text-muted">{{ $s['label'] }}</small>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Estimated Next Numbers -->
            <div class="card mb-4">
                <div class="card-header bg-gradient-info">
                    <div class="d-flex justify-content-between align-items-center">
                        <h3 class="card-title text-white mb-0">
                            <i class="fas fa-calculator"></i> Estimated Next Numbers
                        </h3>
                        <div class="d-flex align-items-center">
                            <form method="GET" action="{{ route('dashboard.letter-administration') }}" class="form-inline mr-2" id="filterForm">
                                @if($userProjects->count() > 1)
                                    <label for="project_filter" class="text-white-50 mr-2 mb-0">Project:</label>
                                    <select name="project_id" id="project_filter" class="form-control form-control-sm mr-2" onchange="this.form.submit()">
                                        @foreach($userProjects as $project)
                                            <option value="{{ $project->id }}" {{ $selectedProjectId == $project->id ? 'selected' : '' }}>
                                                {{ $project->project_code }} - {{ $project->project_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                @else
                                    <input type="hidden" name="project_id" value="{{ $selectedProjectId }}">
                                    <span class="text-white-50 mr-2">
                                        <i class="fas fa-building"></i> {{ $userProjects->first()->project_code ?? 'N/A' }}
                                    </span>
                                @endif
                                <label for="year_filter" class="text-white-50 mr-2 mb-0">Year:</label>
                                <select name="year" id="year_filter" class="form-control form-control-sm" onchange="this.form.submit()">
                                    @foreach($availableYears as $year)
                                        <option value="{{ $year }}" {{ $selectedYear == $year ? 'selected' : '' }}>
                                            {{ $year }}
                                        </option>
                                    @endforeach
                                </select>
                            </form>
                            <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                <i class="fas fa-minus text-white"></i>
                            </button>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="alert alert-info mb-3">
                        <i class="fas fa-info-circle"></i> 
                        <strong>Note:</strong> 
                        Estimated next numbers shown for <strong>{{ $userProjects->where('id', $selectedProjectId)->first()->project_code ?? 'Selected Project' }}</strong> 
                        for year <strong>{{ $selectedYear }}</strong>. Numbers are calculated based on the sequence used in this project.
                    </div>
                    <!-- Category Cards Grid -->
                    <div class="row">
                        @foreach ($categories as $category)
                            @php
                                $estimate = $estimatedNextNumbers[$category->id] ?? null;
                                $letterCount = $letterCountsByCategory[$category->id] ?? 0;
                                $lastNumbers = $lastNumbersByCategory[$category->id] ?? collect();
                            @endphp
                            <div class="col-lg-4 col-md-6 col-sm-6 col-12 mb-3">
                                <div class="card h-100 border-0 shadow-sm compact-card">
                                    <div class="card-header bg-light border-0 py-2">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-0 font-weight-bold text-dark compact-title">
                                                    <span
                                                        class="badge badge-primary mr-2">{{ $category->category_code }}</span>
                                                    {{ $category->category_name }}
                                                </h6>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="card-body compact-body">
                                        @if ($estimate)
                                            <!-- Next Number Display -->
                                            <div class="text-center mb-2">
                                                <div class="display-5 font-weight-bold text-primary mb-1">
                                                    {{ $estimate['next_letter_number'] }}
                                                </div>
                                                <div class="text-muted">
                                                    <small>Next Available Number</small>
                                                </div>
                                            </div>

                                            <!-- Numbering Info -->
                                            <div class="row text-center mb-2">
                                                <div class="col-6">
                                                    <div class="border-right">
                                                        <div class="h6 text-info mb-0">{{ $estimate['next_sequence'] }}
                                                        </div>
                                                        <small class="text-muted">Sequence</small>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <div class="h6 text-success mb-0">{{ $estimate['year'] }}</div>
                                                    <small class="text-muted">Year</small>
                                                </div>
                                            </div>

                                            <!-- Behavior & Count -->
                                            <div class="row mb-2">
                                                <div class="col-6">
                                                    @if (isset($category->numbering_behavior))
                                                        @if ($category->numbering_behavior === 'annual_reset')
                                                            <span class="badge badge-warning badge-pill compact-badge"
                                                                data-toggle="tooltip" title="Numbers reset to 1 each year">
                                                                <i class="fas fa-calendar-alt mr-1"></i>Annual Reset
                                                            </span>
                                                        @else
                                                            <span class="badge badge-success badge-pill compact-badge"
                                                                data-toggle="tooltip"
                                                                title="Numbers continue from previous sequence">
                                                                <i class="fas fa-arrow-up mr-1"></i>Continuous
                                                            </span>
                                                        @endif
                                                    @endif
                                                </div>
                                                <div class="col-6 text-right">
                                                    <span class="badge badge-secondary badge-pill compact-badge">
                                                        <i class="fas fa-file-alt mr-1"></i>{{ $letterCount }} letters
                                                    </span>
                                                </div>
                                            </div>

                                            <!-- Recent Numbers -->
                                            @if ($lastNumbers->count() > 0)
                                                <div class="border-top pt-2">
                                                    <small class="text-muted d-block mb-1">
                                                        <i class="fas fa-history mr-1"></i>Recent Numbers:
                                                    </small>
                                                    <div class="d-flex flex-wrap">
                                                        @foreach ($lastNumbers->take(3) as $lastNumber)
                                                            <span
                                                                class="badge badge-light mr-1 mb-1 px-1 py-0 compact-number-badge">
                                                                <code>{{ $lastNumber->letter_number }}</code>
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        @else
                                            <!-- No Data State -->
                                            <div class="text-center py-3">
                                                <div class="text-muted mb-1">
                                                    <i class="fas fa-inbox fa-2x"></i>
                                                </div>
                                                <div class="h6 text-muted">No Data Available</div>
                                                <small class="text-muted">No previous numbers found</small>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="card-footer bg-light border-0 compact-footer">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <small class="text-muted">
                                                <i class="fas fa-clock mr-1"></i>Updated: {{ now()->format('M d, H:i') }}
                                            </small>
                                            <div class="btn-group btn-group-sm">
                                                <a href="{{ route('letter-numbers.index', ['letter_category_id' => $category->id]) }}"
                                                    class="btn btn-outline-secondary"
                                                    title="View all letters in {{ $category->category_name }} category">
                                                    <i class="fas fa-list mr-1"></i>View All
                                                </a>
                                                <a href="{{ route('letter-numbers.create', ['categoryId' => $category->category_code]) }}"
                                                    class="btn btn-outline-primary">
                                                    <i class="fas fa-plus mr-1"></i>Create
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                </div>
            </div>

            <!-- Categories and Recent Activity -->
            <div class="row mb-4">
                <!-- Categories Stats -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-tags mr-1"></i>
                                Letter Categories
                            </h3>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-sm table-bordered table-striped" id="categoriesTable">
                                <thead>
                                    <tr>
                                        <th>Category</th>
                                        <th>Total</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- DataTables will populate this -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Recent Letters -->
                <div class="col-lg-6">
                    <div class="card">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-history mr-1"></i>
                                Recent Letter Numbers
                            </h3>
                        </div>
                        <div class="card-body table-responsive p-0">
                            <table class="table table-sm table-bordered table-striped" id="recentLettersTable">
                                <thead>
                                    <tr>
                                        <th>Letter #</th>
                                        <th>Category</th>
                                        <th>Status</th>
                                        <th>Created</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- DataTables will populate this -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Cards -->
            <div class="row mb-4">
                <div class="col-lg-3 col-6">
                    <div class="card bg-gradient-primary">
                        <div class="card-body">
                            <div class="d-flex">
                                <p class="d-flex flex-column">
                                    <span class="text-bold text-lg">Create New</span>
                                    <span>Generate letter number</span>
                                </p>
                                <p class="ml-auto d-flex flex-column text-right">
                                    <span class="text-white">
                                        <i class="fas fa-plus fa-2x"></i>
                                    </span>
                                </p>
                            </div>
                            <div class="d-flex flex-row justify-content-end">
                                <a href="{{ route('letter-numbers.create') }}" class="btn btn-sm btn-light">
                                    Create Letter Number
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="card bg-gradient-success">
                        <div class="card-body">
                            <div class="d-flex">
                                <p class="d-flex flex-column">
                                    <span class="text-bold text-lg">Import</span>
                                    <span>Bulk import letters</span>
                                </p>
                                <p class="ml-auto d-flex flex-column text-right">
                                    <span class="text-white">
                                        <i class="fas fa-upload fa-2x"></i>
                                    </span>
                                </p>
                            </div>
                            <div class="d-flex flex-row justify-content-end">
                                <button class="btn btn-sm btn-light" data-toggle="modal" data-target="#importModal">
                                    Import Excel
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="card bg-gradient-warning">
                        <div class="card-body">
                            <div class="d-flex">
                                <p class="d-flex flex-column">
                                    <span class="text-bold text-lg">Export</span>
                                    <span>Download report</span>
                                </p>
                                <p class="ml-auto d-flex flex-column text-right">
                                    <span class="text-white">
                                        <i class="fas fa-download fa-2x"></i>
                                    </span>
                                </p>
                            </div>
                            <div class="d-flex flex-row justify-content-end">
                                <a href="{{ route('letter-numbers.export') }}" class="btn btn-sm btn-light">
                                    Export Excel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-6">
                    <div class="card bg-gradient-info">
                        <div class="card-body">
                            <div class="d-flex">
                                <p class="d-flex flex-column">
                                    <span class="text-bold text-lg">Manage</span>
                                    <span>Letter categories</span>
                                </p>
                                <p class="ml-auto d-flex flex-column text-right">
                                    <span class="text-white">
                                        <i class="fas fa-cogs fa-2x"></i>
                                    </span>
                                </p>
                            </div>
                            <div class="d-flex flex-row justify-content-end">
                                <a href="{{ route('letter-categories.index') }}" class="btn btn-sm btn-light">
                                    Manage Categories
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- Import Modal -->
    <div class="modal fade" id="importModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('letter-numbers.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header">
                        <h4 class="modal-title">Import Letter Numbers</h4>
                        <button type="button" class="close" data-dismiss="modal">×</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="file">Select Excel File</label>
                            <input type="file" class="form-control" name="file" accept=".xls,.xlsx" required>
                            <small class="form-text text-muted">
                                Supported formats: .xls, .xlsx
                            </small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Import</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <style>
        /* Enhanced Estimated Next Numbers Styling */
        .card.shadow-sm {
            transition: all 0.3s ease;
            border-radius: 0.75rem;
        }

        .card.shadow-sm:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }

        .display-4 {
            font-size: 2.5rem;
            font-weight: 700;
            line-height: 1.2;
        }

        .badge-pill {
            border-radius: 50rem;
            padding: 0.5em 1em;
        }

        .bg-gradient-primary {
            background: linear-gradient(45deg, #007bff, #0056b3);
        }

        .bg-gradient-success {
            background: linear-gradient(45deg, #28a745, #1e7e34);
        }

        .bg-gradient-warning {
            background: linear-gradient(45deg, #ffc107, #e0a800);
        }

        .bg-gradient-info {
            background: linear-gradient(45deg, #17a2b8, #138496);
        }

        .bg-gradient-secondary {
            background: linear-gradient(45deg, #6c757d, #545b62);
        }

        .info-box.bg-gradient-primary .info-box-icon,
        .info-box.bg-gradient-success .info-box-icon,
        .info-box.bg-gradient-warning .info-box-icon,
        .info-box.bg-gradient-info .info-box-icon {
            background: rgba(255, 255, 255, 0.2);
            color: #fff;
        }

        .info-box.bg-gradient-primary .info-box-content,
        .info-box.bg-gradient-success .info-box-content,
        .info-box.bg-gradient-warning .info-box-content,
        .info-box.bg-gradient-info .info-box-content {
            color: #fff;
        }

        .info-box.bg-gradient-primary .info-box-text,
        .info-box.bg-gradient-success .info-box-text,
        .info-box.bg-gradient-warning .info-box-text,
        .info-box.bg-gradient-info .info-box-text {
            color: rgba(255, 255, 255, 0.8);
        }

        .border-right {
            border-right: 1px solid #dee2e6 !important;
        }

        .card-header.bg-light {
            background-color: #f8f9fa !important;
            border-bottom: 1px solid #e9ecef;
        }

        .dropdown-toggle::after {
            margin-left: 0.255em;
        }

        .btn-outline-secondary:hover {
            background-color: #6c757d;
            border-color: #6c757d;
            color: #fff;
        }

        .text-white-50 {
            color: rgba(255, 255, 255, 0.5) !important;
        }

        .card-tools .btn-tool {
            color: rgba(255, 255, 255, 0.8);
            background: transparent;
            border: none;
            padding: 0.25rem 0.5rem;
        }

        .card-tools .btn-tool:hover {
            color: #fff;
            background: rgba(255, 255, 255, 0.1);
        }

        .h5 {
            font-size: 1.25rem;
            font-weight: 500;
        }

        .h6 {
            font-size: 1rem;
            font-weight: 500;
        }

        .font-weight-bold {
            font-weight: 700 !important;
        }

        .text-dark {
            color: #343a40 !important;
        }

        .d-flex.flex-wrap {
            flex-wrap: wrap;
        }

        .badge-light {
            background-color: #f8f9fa;
            color: #6c757d;
            border: 1px solid #dee2e6;
        }

        .badge-light code {
            background: transparent;
            color: inherit;
            padding: 0;
        }

        .py-3 {
            padding-top: 1rem !important;
            padding-bottom: 1rem !important;
        }

        .py-4 {
            padding-top: 1.5rem !important;
            padding-bottom: 1.5rem !important;
        }

        .pt-3 {
            padding-top: 1rem !important;
        }

        .mb-0 {
            margin-bottom: 0 !important;
        }

        .mb-1 {
            margin-bottom: 0.25rem !important;
        }

        .mb-2 {
            margin-bottom: 0.5rem !important;
        }

        .mb-3 {
            margin-bottom: 1rem !important;
        }

        .mb-4 {
            margin-bottom: 1.5rem !important;
        }

        .mt-4 {
            margin-top: 1.5rem !important;
        }

        .mr-1 {
            margin-right: 0.25rem !important;
        }

        .mr-2 {
            margin-right: 0.5rem !important;
        }

        .ml-2 {
            margin-left: 0.5rem !important;
        }

        .px-2 {
            padding-left: 0.5rem !important;
            padding-right: 0.5rem !important;
        }

        .py-1 {
            padding-top: 0.25rem !important;
            padding-bottom: 0.25rem !important;
        }

        .d-block {
            display: block !important;
        }

        .text-center {
            text-align: center !important;
        }

        .text-right {
            text-align: right !important;
        }

        .justify-content-between {
            justify-content: space-between !important;
        }

        .align-items-center {
            align-items: center !important;
        }

        .fa-3x {
            font-size: 3em;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .display-4 {
                font-size: 2rem;
            }

            .h5 {
                font-size: 1.1rem;
            }

            .col-xl-4 {
                margin-bottom: 1rem;
            }
        }

        /* Compact Category Cards Styling */
        .compact-card {
            transition: all 0.3s ease;
            border-radius: 0.5rem;
        }

        .compact-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }

        .compact-title {
            font-size: 0.9rem;
            line-height: 1.2;
        }

        .compact-title+small a {
            color: #6c757d;
            transition: color 0.2s ease;
        }

        .compact-title+small a:hover {
            color: #007bff;
        }

        .compact-title+small .fas {
            font-size: 0.7rem;
        }

        .compact-body {
            padding: 0.75rem;
        }

        .compact-footer {
            padding: 0.5rem 0.75rem;
        }

        .display-5 {
            font-size: 2rem;
            font-weight: 700;
            line-height: 1.1;
        }

        .compact-badge {
            font-size: 0.7rem;
            padding: 0.3em 0.6em;
        }

        .compact-number-badge {
            font-size: 0.65rem;
            padding: 0.2em 0.4em;
        }

        .compact-btn {
            padding: 0.2rem 0.4rem;
            font-size: 0.8rem;
        }

        .compact-action-btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
        }

        .compact-card .card-header {
            padding: 0.5rem 0.75rem;
        }

        .compact-card .card-body {
            padding: 0.75rem;
        }

        .compact-card .card-footer {
            padding: 0.5rem 0.75rem;
        }

        .compact-card .h6 {
            font-size: 0.9rem;
            font-weight: 500;
        }

        .compact-card .small {
            font-size: 0.75rem;
        }

        .compact-card .text-muted {
            font-size: 0.75rem;
        }

        .compact-card .fa-2x {
            font-size: 1.5em;
        }

        .compact-card .border-top {
            margin-top: 0.5rem;
        }

        .compact-card .pt-2 {
            padding-top: 0.5rem !important;
        }

        .compact-card .mb-2 {
            margin-bottom: 0.5rem !important;
        }

        .compact-card .mb-1 {
            margin-bottom: 0.25rem !important;
        }

        .compact-card .py-3 {
            padding-top: 0.75rem !important;
            padding-bottom: 0.75rem !important;
        }

        .compact-card .py-2 {
            padding-top: 0.5rem !important;
            padding-bottom: 0.5rem !important;
        }

        .compact-card .py-1 {
            padding-top: 0.25rem !important;
            padding-bottom: 0.25rem !important;
        }

        .compact-card .px-1 {
            padding-left: 0.25rem !important;
            padding-right: 0.25rem !important;
        }

        .compact-card .py-0 {
            padding-top: 0 !important;
            padding-bottom: 0 !important;
        }

        /* Reduce spacing between cards */
        .col-lg-4.mb-3,
        .col-md-6.mb-3,
        .col-sm-6.mb-3,
        .col-12.mb-3 {
            margin-bottom: 1rem !important;
        }

        /* Responsive adjustments for compact cards */
        @media (max-width: 992px) {
            .col-lg-4 {
                margin-bottom: 0.75rem !important;
            }
        }

        @media (max-width: 768px) {
            .col-md-6 {
                margin-bottom: 0.75rem !important;
            }

            .compact-card .display-5 {
                font-size: 1.75rem;
            }

            .compact-card .h6 {
                font-size: 0.85rem;
            }
        }

        @media (max-width: 576px) {
            .col-sm-6 {
                margin-bottom: 0.75rem !important;
            }

            .compact-card .display-5 {
                font-size: 1.5rem;
            }

            .compact-card .compact-body {
                padding: 0.5rem;
            }

            .compact-card .compact-footer {
                padding: 0.4rem 0.5rem;
            }
        }

        /* Make badges more compact */
        .compact-badge.badge-pill {
            border-radius: 1rem;
        }

        .compact-number-badge.badge-light {
            border-radius: 0.25rem;
        }

        /* Optimize text sizes for compact display */
        .compact-card .info-box-number {
            font-size: 1.5rem;
        }

        .compact-card .info-box-text {
            font-size: 0.8rem;
        }

        /* Reduce icon sizes in compact mode */
        .compact-card .fas.fa-ellipsis-v {
            font-size: 0.8rem;
        }

        .compact-card .fas.fa-plus,
        .compact-card .fas.fa-clock {
            font-size: 0.7rem;
        }

        .compact-card .fas.fa-calendar-alt,
        .compact-card .fas.fa-arrow-up,
        .compact-card .fas.fa-file-alt,
        .compact-card .fas.fa-history {
            font-size: 0.7rem;
        }

        /* Button group styling for compact cards */
        .compact-footer .btn-group-sm .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
            border-radius: 0.25rem;
        }

        .compact-footer .btn-group-sm .btn:first-child {
            border-top-right-radius: 0;
            border-bottom-right-radius: 0;
        }

        .compact-footer .btn-group-sm .btn:last-child {
            border-top-left-radius: 0;
            border-bottom-left-radius: 0;
        }

        .compact-footer .btn-group-sm .btn:not(:first-child):not(:last-child) {
            border-radius: 0;
        }

        /* Dropdown menu badge styling */
        .dropdown-item .badge-sm {
            font-size: 0.65rem;
            padding: 0.2em 0.4em;
        }

        .dropdown-item .ml-2 {
            margin-left: 0.5rem !important;
        }

        /* Summary Overview Card Styling */
        .bg-gradient-dark {
            background: linear-gradient(45deg, #343a40, #495057);
        }

        .summary-stat {
            padding: 1rem 0;
            text-align: center;
        }

        .summary-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
        }

        .summary-number {
            font-size: 2rem;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 0.5rem;
        }

        .summary-label {
            font-size: 0.9rem;
            color: #6c757d;
            font-weight: 500;
        }

        .summary-content {
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Card tools button styling */
        .card-tools .btn-outline-light {
            border-color: rgba(255, 255, 255, 0.3);
            color: rgba(255, 255, 255, 0.8);
            transition: all 0.2s ease;
        }

        .card-tools .btn-outline-light:hover {
            background-color: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.5);
            color: #fff;
        }

        .card-tools .btn-sm {
            padding: 0.25rem 0.5rem;
            font-size: 0.8rem;
        }

        /* Responsive adjustments for summary card */
        @media (max-width: 768px) {
            .summary-number {
                font-size: 1.5rem;
            }

            .summary-icon {
                width: 50px;
                height: 50px;
                font-size: 1.25rem;
            }

            .summary-label {
                font-size: 0.8rem;
            }
        }

        @media (max-width: 576px) {
            .summary-number {
                font-size: 1.25rem;
            }

            .summary-icon {
                width: 45px;
                height: 45px;
                font-size: 1.1rem;
            }

            .summary-label {
                font-size: 0.75rem;
            }
        }
    </style>
@endsection

@section('scripts')
    <!-- DataTables  & Plugins -->
    <script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-responsive/js/dataTables.responsive.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-responsive/js/responsive.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/dataTables.buttons.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.bootstrap4.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/jszip/jszip.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/pdfmake/pdfmake.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/pdfmake/vfs_fonts.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.html5.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.print.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/datatables-buttons/js/buttons.colVis.min.js') }}"></script>
    <script src="{{ asset('assets/plugins/chart.js/Chart.min.js') }}"></script>
    <script>
        $(document).ready(function() {
            // Initialize DataTables
            $('#categoriesTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('dashboard.lettersByCategory') }}",
                columns: [{
                        data: 'category_name',
                        name: 'category_name'
                    },
                    {
                        data: 'total_letters',
                        name: 'total_letters',
                        searchable: false
                    },
                    {
                        data: 'status_breakdown',
                        name: 'status_breakdown',
                        orderable: false,
                        searchable: false
                    }
                ],
                pageLength: 4,
                lengthChange: false,
                info: false,
                searching: false,
                order: [
                    [1, 'desc']
                ]
            });

            $('#recentLettersTable').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('dashboard.recentLetters') }}",
                columns: [{
                        data: 'letter_number',
                        name: 'letter_number'
                    },
                    {
                        data: 'category',
                        name: 'category'
                    },
                    {
                        data: 'status_badge',
                        name: 'status_badge',
                        orderable: false
                    },
                    {
                        data: 'created_date',
                        name: 'created_at'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    }
                ],
                pageLength: 5,
                lengthChange: false,
                info: false,
                searching: false,
                order: [
                    [3, 'desc']
                ]
            });

            // Initialize any charts or additional functionality here
            console.log('Estimated Next Numbers dashboard initialized');
        });
    </script>
@endsection
