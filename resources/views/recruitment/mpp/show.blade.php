@extends('layouts.main')

@section('title', $title)

@section('styles')
    <style>
        .info-box-content {
            padding: 0.5rem;
        }

        /* Table styling */
        .details-table thead th {
            background-color: #28a745;
            color: white;
            font-weight: 600;
            font-size: 0.85rem;
            vertical-align: middle;
            padding: 0.75rem 0.5rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* Plan section - Blue/Info color */
        .details-table thead th.plan-header {
            background-color: #17a2b8;
        }

        /* Existing section - Orange/Warning color */
        .details-table thead th.existing-header {
            background-color: #ffc107;
            color: #212529;
        }

        /* Diff section - Purple/Secondary color */
        .details-table thead th.diff-header {
            background-color: #6c757d;
        }

        /* Plan cells - Light blue background */
        .details-table tbody td.plan-cell,
        .details-table tfoot td.plan-cell {
            background-color: #d1ecf1;
        }

        /* Existing cells - Light yellow/orange background */
        .details-table tbody td.existing-cell,
        .details-table tfoot td.existing-cell {
            background-color: #fff3cd;
        }

        /* Diff cells - Light gray background */
        .details-table tbody td.diff-cell,
        .details-table tfoot td.diff-cell {
            background-color: #e2e3e5;
        }

        /* Hover effect for colored cells */
        .details-table tbody tr:hover td.plan-cell {
            background-color: #bee5eb;
        }

        .details-table tbody tr:hover td.existing-cell {
            background-color: #ffeaa7;
        }

        .details-table tbody tr:hover td.diff-cell {
            background-color: #d6d8db;
        }

        .details-table tbody tr:hover {
            background-color: transparent;
        }

        .details-table tbody tr:nth-child(even) {
            background-color: transparent;
        }

        .details-table tbody tr:nth-child(even):hover {
            background-color: transparent;
        }

        /* Row number and action column alignment */
        .details-table td.row-number {
            text-align: left;
            padding-left: 0.75rem;
        }

        .details-table td:last-child {
            text-align: left;
            padding-left: 0.5rem;
        }

        .summary-row {
            background-color: #e9ecef;
            font-weight: 600;
        }

        .diff-positive {
            color: #28a745;
            font-weight: 600;
        }

        .diff-negative {
            color: #dc3545;
            font-weight: 600;
        }

        .sessions-table {
            font-size: 0.85rem;
        }

        .card {
            border-radius: 0.5rem;
            margin-bottom: 1.5rem;
        }

        .card-header {
            border-radius: calc(0.5rem - 1px) calc(0.5rem - 1px) 0 0;
        }

        .btn {
            border-radius: 0.25rem;
        }
    </style>
@endsection

@section('content')
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $title }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('recruitment.mpp.index') }}">MPP</a></li>
                        <li class="breadcrumb-item active">{{ $subtitle }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <!-- MPP Header Info -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-primary card-outline elevation-3">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-file-alt mr-2"></i>
                                <strong>MPP Information</strong>
                            </h3>
                            <div class="card-tools">
                                @if ($mpp->status === 'active')
                                    <a href="{{ route('recruitment.mpp.edit', $mpp->id) }}" class="btn btn-sm btn-primary">
                                        <i class="fas fa-edit"></i> Edit
                                    </a>
                                    <button type="button" class="btn btn-sm btn-warning" id="btn-close-mpp">
                                        <i class="fas fa-lock"></i> Close MPP
                                    </button>
                                @endif
                                <a href="{{ route('recruitment.mpp.index') }}" class="btn btn-sm btn-secondary">
                                    <i class="fas fa-arrow-left"></i> Back
                                </a>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <strong>MPP Number:</strong><br>
                                    {{ $mpp->mpp_number }}
                                </div>
                                <div class="col-md-3">
                                    <strong>Project:</strong><br>
                                    {{ $mpp->project->project_code }} - {{ $mpp->project->project_name }}
                                </div>
                                <div class="col-md-3">
                                    <strong>Status:</strong><br>
                                    <span
                                        class="badge {{ $mpp->status === 'active' ? 'badge-success' : 'badge-secondary' }}">
                                        {{ ucfirst($mpp->status) }}
                                    </span>
                                </div>
                                <div class="col-md-3">
                                    <strong>Created By:</strong><br>
                                    {{ $mpp->creator->name }}
                                </div>
                            </div>
                            <div class="row mt-2">
                                <div class="col-md-12">
                                    <strong>Title:</strong><br>
                                    {{ $mpp->title }}
                                </div>
                            </div>
                            @if ($mpp->description)
                                <div class="row mt-2">
                                    <div class="col-md-12">
                                        <strong>Description:</strong><br>
                                        {{ $mpp->description }}
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Summary Cards -->
            <div class="row">
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-info">
                        <div class="inner">
                            <h3>{{ $mpp->getTotalPositionsNeeded() }}</h3>
                            <p>Total Plan</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-bullseye"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-success">
                        <div class="inner">
                            <h3>{{ $mpp->getTotalExisting() }}</h3>
                            <p>Total Existing</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-warning">
                        <div class="inner">
                            <h3>{{ abs($mpp->getTotalDiff()) }}</h3>
                            <p>Total Diff</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>
                <div class="col-lg-3 col-6">
                    <div class="small-box bg-purple">
                        <div class="inner">
                            <h3>{{ $mpp->getCompletionPercentage() }}%</h3>
                            <p>Completion</p>
                        </div>
                        <div class="icon">
                            <i class="fas fa-tasks"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Position Details & Sessions -->
            <div class="row">
                <div class="col-md-12">
                    <div class="card card-success card-outline elevation-3">
                        <div class="card-header">
                            <h3 class="card-title">
                                <i class="fas fa-users mr-2"></i>
                                <strong>Position Details & Recruitment Sessions</strong>
                            </h3>
                        </div>
                        <div class="card-body p-0">
                            <div class="p-3 bg-light border-bottom">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle mr-1"></i>
                                    <strong>S</strong> = Staff | <strong>NS</strong> = Non-Staff |
                                    Diff = Plan - Existing
                                </small>
                            </div>
                            <!-- Position Summary Table -->
                            <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                                <table class="table table-bordered table-sm details-table mb-0">
                                    <thead style="position: sticky; top: 0; background: #f8f9fa; z-index: 10;">
                                        <tr>
                                            <th width="3%">#</th>
                                            <th width="20%">Position/Jabatan</th>
                                            <th width="6%" class="text-center">Qty<br>Unit</th>
                                            <th width="6%" class="text-center plan-header" title="Plan Staff">
                                                Plan<br><small>S</small></th>
                                            <th width="6%" class="text-center plan-header" title="Plan Non-Staff">
                                                Plan<br><small>NS</small></th>
                                            <th width="6%" class="text-center plan-header">
                                                Plan<br><small>Total</small></th>
                                            <th width="6%" class="text-center existing-header" title="Existing Staff">
                                                Existing<br><small>S</small></th>
                                            <th width="6%" class="text-center existing-header"
                                                title="Existing Non-Staff">
                                                Existing<br><small>NS</small></th>
                                            <th width="6%" class="text-center existing-header">
                                                Existing<br><small>Total</small></th>
                                            <th width="6%" class="text-center diff-header">
                                                Diff<br><small>S</small>
                                            </th>
                                            <th width="6%" class="text-center diff-header">
                                                Diff<br><small>NS</small>
                                            </th>
                                            <th width="6%" class="text-center diff-header">
                                                Diff<br><small>Total</small></th>
                                            <th width="6%" class="text-center">Theory<br>Test</th>
                                            <th width="8%" class="text-center">Status</th>
                                            <th width="4%" class="text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($mpp->details as $index => $detail)
                                            <tr>
                                                <td class="row-number">
                                                    <span class="badge badge-secondary">{{ $index + 1 }}</span>
                                                </td>
                                                <td>
                                                    <strong>{{ $detail->position->position_name ?? 'N/A' }}</strong>
                                                    @if ($detail->position && $detail->position->department)
                                                        <br><small
                                                            class="text-muted">{{ $detail->position->department->department_name }}</small>
                                                    @endif
                                                    @if ($detail->sessions->isNotEmpty())
                                                        <br><small class="text-muted">{{ $detail->sessions->count() }}
                                                            session(s)</small>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    {{ $detail->qty_unit ?? '-' }}
                                                </td>
                                                <td class="text-center plan-cell">{{ $detail->plan_qty_s }}</td>
                                                <td class="text-center plan-cell">{{ $detail->plan_qty_ns }}</td>
                                                <td class="text-center plan-cell font-weight-bold">
                                                    <span class="badge badge-info">{{ $detail->total_plan }}</span>
                                                </td>
                                                <td class="text-center existing-cell">{{ $detail->existing_qty_s }}</td>
                                                <td class="text-center existing-cell">{{ $detail->existing_qty_ns }}</td>
                                                <td class="text-center existing-cell font-weight-bold">
                                                    <span class="badge badge-warning">{{ $detail->total_existing }}</span>
                                                </td>
                                                <td
                                                    class="text-center diff-cell {{ $detail->diff_s > 0 ? 'diff-positive' : ($detail->diff_s < 0 ? 'diff-negative' : '') }}">
                                                    {{ $detail->diff_s > 0 ? '+' : '' }}{{ $detail->diff_s }}
                                                </td>
                                                <td
                                                    class="text-center diff-cell {{ $detail->diff_ns > 0 ? 'diff-positive' : ($detail->diff_ns < 0 ? 'diff-negative' : '') }}">
                                                    {{ $detail->diff_ns > 0 ? '+' : '' }}{{ $detail->diff_ns }}
                                                </td>
                                                <td
                                                    class="text-center diff-cell {{ $detail->total_diff > 0 ? 'diff-positive' : ($detail->total_diff < 0 ? 'diff-negative' : '') }}">
                                                    {{ $detail->total_diff > 0 ? '+' : '' }}{{ $detail->total_diff }}
                                                </td>
                                                <td class="text-center">
                                                    @if ($detail->requires_theory_test)
                                                        <span class="badge badge-info">
                                                            <i class="fas fa-check-circle"></i>
                                                        </span>
                                                    @else
                                                        <span class="badge badge-secondary">
                                                            <i class="fas fa-times-circle"></i>
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if ($detail->fulfilled_at)
                                                        <span class="badge badge-success">
                                                            <i class="fas fa-check"></i> Fulfilled
                                                        </span>
                                                        <br><small>{{ $detail->fulfilled_at->format('d M Y') }}</small>
                                                        @if ($detail->days_to_fulfill)
                                                            <br><small class="text-muted">({{ $detail->days_to_fulfill }}
                                                                days)</small>
                                                        @endif
                                                    @else
                                                        <span class="badge badge-warning">
                                                            <i class="fas fa-clock"></i> Pending
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <div class="btn-group">
                                                        <button type="button"
                                                            class="btn btn-sm btn-info btn-toggle-sessions"
                                                            data-detail-id="{{ $detail->id }}" title="View Sessions">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                        @if ($mpp->status === 'active' && !$detail->fulfilled_at)
                                                            <button type="button"
                                                                class="btn btn-sm btn-success btn-add-candidate"
                                                                data-detail-id="{{ $detail->id }}"
                                                                data-position-name="{{ $detail->position->position_name ?? 'N/A' }}"
                                                                title="Add Candidate">
                                                                <i class="fas fa-plus"></i>
                                                            </button>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                            <!-- Sessions for this position (hidden by default) -->
                                            <tr class="sessions-row" id="sessions-{{ $detail->id }}"
                                                style="display: none;">
                                                <td colspan="15" class="p-3 bg-light">
                                                    @if ($mpp->status === 'active' && !$detail->fulfilled_at)
                                                        <div class="mb-2">
                                                            <button type="button"
                                                                class="btn btn-sm btn-success btn-add-candidate"
                                                                data-detail-id="{{ $detail->id }}"
                                                                data-position-name="{{ $detail->position->position_name ?? 'N/A' }}">
                                                                <i class="fas fa-plus"></i> Add Candidate
                                                            </button>
                                                        </div>
                                                    @endif
                                                    @if ($detail->sessions->isEmpty())
                                                        <p class="text-center text-muted mb-0">No recruitment sessions yet
                                                            for this position.</p>
                                                    @else
                                                        <div class="table-responsive">
                                                            <table
                                                                class="table table-sm table-striped sessions-table mb-0">
                                                                <thead>
                                                                    <tr>
                                                                        <th>Session Number</th>
                                                                        <th>Candidate</th>
                                                                        <th>Applied Date</th>
                                                                        <th>Current Stage</th>
                                                                        <th>Progress</th>
                                                                        <th>Status</th>
                                                                        <th class="text-center">Action</th>
                                                                    </tr>
                                                                </thead>
                                                                <tbody>
                                                                    @foreach ($detail->sessions as $session)
                                                                        <tr>
                                                                            <td>{{ $session->session_number }}</td>
                                                                            <td>{{ $session->candidate->fullname ?? 'N/A' }}
                                                                            </td>
                                                                            <td>{{ $session->applied_date->format('d M Y') }}
                                                                            </td>
                                                                            <td>{{ ucfirst(str_replace('_', ' ', $session->current_stage)) }}
                                                                            </td>
                                                                            <td>
                                                                                <div class="progress"
                                                                                    style="height: 20px;">
                                                                                    <div class="progress-bar {{ $session->overall_progress >= 100 ? 'bg-success' : 'bg-info' }}"
                                                                                        style="width: {{ $session->overall_progress }}%">
                                                                                        {{ $session->overall_progress }}%
                                                                                    </div>
                                                                                </div>
                                                                            </td>
                                                                            <td>
                                                                                @php
                                                                                    $statusClass = match (
                                                                                        $session->status
                                                                                    ) {
                                                                                        'in_process' => 'badge-warning',
                                                                                        'hired' => 'badge-success',
                                                                                        'rejected' => 'badge-danger',
                                                                                        default => 'badge-secondary',
                                                                                    };
                                                                                @endphp
                                                                                <span
                                                                                    class="badge {{ $statusClass }}">{{ ucfirst(str_replace('_', ' ', $session->status)) }}</span>
                                                                            </td>
                                                                            <td class="text-center">
                                                                                <div class="btn-group">
                                                                                    <a href="{{ route('recruitment.sessions.candidate', $session->id) }}"
                                                                                        class="btn btn-xs btn-info"
                                                                                        target="_blank">
                                                                                        <i class="fas fa-eye"></i> View
                                                                                    </a>
                                                                                </div>
                                                                            </td>
                                                                        </tr>
                                                                    @endforeach
                                                                </tbody>
                                                            </table>
                                                        </div>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr class="summary-row">
                                            <td colspan="2" class="text-right">
                                                <i class="fas fa-calculator mr-1"></i>TOTAL:
                                            </td>
                                            <td></td>
                                            <td class="text-center plan-cell">{{ $mpp->getTotalStaffNeeded() }}</td>
                                            <td class="text-center plan-cell">{{ $mpp->getTotalNonStaffNeeded() }}</td>
                                            <td class="text-center plan-cell">
                                                <span
                                                    class="badge badge-info">{{ $mpp->getTotalPositionsNeeded() }}</span>
                                            </td>
                                            <td class="text-center existing-cell">{{ $mpp->getTotalExistingStaff() }}</td>
                                            <td class="text-center existing-cell">{{ $mpp->getTotalExistingNonStaff() }}
                                            </td>
                                            <td class="text-center existing-cell">
                                                <span class="badge badge-warning">{{ $mpp->getTotalExisting() }}</span>
                                            </td>
                                            <td
                                                class="text-center diff-cell {{ $mpp->getStaffDiff() > 0 ? 'diff-positive' : ($mpp->getStaffDiff() < 0 ? 'diff-negative' : '') }}">
                                                {{ $mpp->getStaffDiff() > 0 ? '+' : '' }}{{ $mpp->getStaffDiff() }}
                                            </td>
                                            <td
                                                class="text-center diff-cell {{ $mpp->getNonStaffDiff() > 0 ? 'diff-positive' : ($mpp->getNonStaffDiff() < 0 ? 'diff-negative' : '') }}">
                                                {{ $mpp->getNonStaffDiff() > 0 ? '+' : '' }}{{ $mpp->getNonStaffDiff() }}
                                            </td>
                                            <td
                                                class="text-center diff-cell {{ $mpp->getTotalDiff() > 0 ? 'diff-positive' : ($mpp->getTotalDiff() < 0 ? 'diff-negative' : '') }}">
                                                {{ $mpp->getTotalDiff() > 0 ? '+' : '' }}{{ $mpp->getTotalDiff() }}
                                            </td>
                                            <td colspan="3"></td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Add Candidate Modal -->
    <div class="modal fade" id="addCandidateModal" tabindex="-1" role="dialog"
        aria-labelledby="addCandidateModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="addCandidateModalLabel">
                        <i class="fas fa-plus"></i> Add Candidate to MPP Detail
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div id="mppDetailInfo" class="alert alert-info" style="display: none;">
                        <strong>Position:</strong> <span id="mppDetailJabatan"></span>
                    </div>
                    <div class="form-group">
                        <label for="candidate_search">Search Candidate/CV</label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="candidate_search"
                                placeholder="Enter candidate name, email, or position applied...">
                            <div class="input-group-append">
                                <button class="btn btn-outline-secondary" type="button" id="search_candidate">
                                    <i class="fas fa-search"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div id="search_results" class="mt-3" style="display: none;">
                        <h6>Search Results</h6>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Position Applied</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody id="candidate_results">
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        $(function() {
            // Toggle sessions visibility
            $('.btn-toggle-sessions').on('click', function() {
                const detailId = $(this).data('detail-id');
                const sessionsRow = $('#sessions-' + detailId);

                if (sessionsRow.is(':visible')) {
                    sessionsRow.hide();
                    $(this).html('<i class="fas fa-eye"></i>');
                } else {
                    $('.sessions-row').hide();
                    $('.btn-toggle-sessions').html('<i class="fas fa-eye"></i>');
                    sessionsRow.show();
                    $(this).html('<i class="fas fa-eye-slash"></i>');
                }
            });

            // Close MPP
            $('#btn-close-mpp').on('click', function() {
                Swal.fire({
                    title: 'Close MPP?',
                    text: "This will prevent new recruitment sessions from being created.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, close it!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $.ajax({
                            url: '{{ route('recruitment.mpp.close', $mpp->id) }}',
                            type: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}'
                            },
                            success: function(response) {
                                if (response.success) {
                                    Swal.fire('Closed!', response.message, 'success')
                                        .then(() => {
                                            window.location.reload();
                                        });
                                } else {
                                    Swal.fire('Error!', response.message, 'error');
                                }
                            },
                            error: function(xhr) {
                                Swal.fire('Error!', 'Failed to close MPP', 'error');
                            }
                        });
                    }
                });
            });

            // Add Candidate Modal Handler
            $(document).on('click', '.btn-add-candidate', function() {
                var detailId = $(this).data('detail-id');
                var positionName = $(this).data('position-name');

                // Store detail ID in modal
                $('#addCandidateModal').data('detail-id', detailId);

                // Update modal title and info
                $('#addCandidateModalLabel').html(
                    '<i class="fas fa-plus"></i> Add Candidate to MPP Detail');
                $('#mppDetailJabatan').text(positionName);
                $('#mppDetailInfo').show();

                // Clear previous search results
                $('#candidate_search').val('');
                $('#search_results').hide();
                $('#candidate_results').empty();

                // Show modal
                $('#addCandidateModal').modal('show');
            });

            // Search Candidate
            $('#search_candidate').on('click', function() {
                var searchTerm = $('#candidate_search').val().trim();

                if (!searchTerm) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Warning',
                        text: 'Please enter a search term',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                // Show loading
                $('#candidate_results').html(
                    '<tr><td colspan="5" class="text-center"><i class="fas fa-spinner fa-spin"></i> Searching...</td></tr>'
                );
                $('#search_results').show();

                // Search candidates
                $.ajax({
                    url: '{{ route('recruitment.candidates.search') }}',
                    type: 'GET',
                    data: {
                        search: searchTerm
                    },
                    success: function(response) {
                        var tbody = $('#candidate_results');
                        tbody.empty();

                        if (!response.success || !response.data || response.data.length === 0) {
                            tbody.html(
                                '<tr><td colspan="5" class="text-center text-muted">No candidates found</td></tr>'
                            );
                        } else {
                            response.data.forEach(function(candidate) {
                                var row = '<tr>' +
                                    '<td>' + (candidate.fullname || candidate.name ||
                                        'N/A') + '</td>' +
                                    '<td>' + (candidate.email || '-') + '</td>' +
                                    '<td>' + (candidate.phone || '-') + '</td>' +
                                    '<td>' + (candidate.position_applied || '-') +
                                    '</td>' +
                                    '<td>' +
                                    '<button class="btn btn-sm btn-primary add-candidate-btn" data-candidate-id="' +
                                    candidate.id + '">' +
                                    '<i class="fas fa-plus"></i> Add</button>' +
                                    '</td>' +
                                    '</tr>';
                                tbody.append(row);
                            });
                        }
                    },
                    error: function(xhr) {
                        $('#candidate_results').html(
                            '<tr><td colspan="5" class="text-center text-danger">Error searching candidates</td></tr>'
                        );
                    }
                });
            });

            // Allow Enter key to trigger search
            $('#candidate_search').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $('#search_candidate').click();
                }
            });

            // Add candidate to MPP Detail from search results
            $(document).on('click', '.add-candidate-btn[data-candidate-id]', function() {
                var candidateId = $(this).data('candidate-id');
                var detailId = $('#addCandidateModal').data('detail-id');

                if (!detailId) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'MPP Detail ID not found',
                        confirmButtonColor: '#3085d6',
                        confirmButtonText: 'OK'
                    });
                    return;
                }

                // Disable button to prevent double click
                $(this).prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Adding...');

                // Add candidate to MPP Detail
                $.ajax({
                    url: '{{ route('recruitment.sessions.store') }}',
                    type: 'POST',
                    data: {
                        candidate_id: candidateId,
                        mpp_detail_id: detailId,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#addCandidateModal').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: 'Success',
                                text: response.message,
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'OK'
                            }).then(() => {
                                // Reload the page to show updated data
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message,
                                confirmButtonColor: '#3085d6',
                                confirmButtonText: 'OK'
                            });
                            // Re-enable button
                            $('.add-candidate-btn[data-candidate-id="' + candidateId + '"]')
                                .prop('disabled', false)
                                .html('<i class="fas fa-plus"></i> Add');
                        }
                    },
                    error: function(xhr) {
                        var message = 'Error adding candidate to MPP Detail';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            message = xhr.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: message,
                            confirmButtonColor: '#3085d6',
                            confirmButtonText: 'OK'
                        });
                        // Re-enable button
                        $('.add-candidate-btn[data-candidate-id="' + candidateId + '"]')
                            .prop('disabled', false)
                            .html('<i class="fas fa-plus"></i> Add');
                    }
                });
            });

            // Clear modal data when closed
            $('#addCandidateModal').on('hidden.bs.modal', function() {
                $(this).removeData('detail-id');
                $('#candidate_search').val('');
                $('#search_results').hide();
                $('#candidate_results').empty();
                $('#mppDetailInfo').hide();
            });
        });
    </script>
@endsection
