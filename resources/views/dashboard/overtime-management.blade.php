@extends('layouts.main')

@section('title', $title)

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
                        <li class="breadcrumb-item active">{{ $subtitle }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>

    <section class="content">
        <div class="container-fluid">
            <div class="row">
                <div class="col-12 mb-3">
                    <div class="card card-outline card-secondary mb-0">
                        <div class="card-header py-2">
                            <h3 class="card-title text-sm mb-0"><i class="fas fa-chart-bar mr-1"></i> Status overtime</h3>
                        </div>
                        <div class="card-body py-2 px-3">
                            <div class="table-responsive">
                                <table class="table table-sm table-borderless mb-0 text-center">
                                    <thead>
                                        <tr class="text-muted small text-uppercase">
                                            <th class="border-0">Total</th>
                                            <th class="border-0">Draft</th>
                                            <th class="border-0">Pending</th>
                                            <th class="border-0">Approved</th>
                                            <th class="border-0">Rejected</th>
                                            <th class="border-0">Finished</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="align-middle"><strong
                                                    class="h5 mb-0">{{ number_format($totalRequests) }}</strong></td>
                                            <td class="align-middle"><span
                                                    class="badge badge-secondary">{{ number_format($countDraft) }}</span>
                                            </td>
                                            <td class="align-middle"><span
                                                    class="badge badge-warning">{{ number_format($countPending) }}</span>
                                            </td>
                                            <td class="align-middle"><span
                                                    class="badge badge-success">{{ number_format($countApproved) }}</span>
                                            </td>
                                            <td class="align-middle"><span
                                                    class="badge badge-danger">{{ number_format($countRejected) }}</span>
                                            </td>
                                            <td class="align-middle"><span
                                                    class="badge badge-info">{{ number_format($countFinished) }}</span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12 mb-3">
                    <div class="card card-outline card-light">
                        <div class="card-body py-3">
                            <div class="row text-center">
                                <div class="col-6 col-md-3 mb-3 mb-md-0">
                                    <div class="text-muted text-uppercase small">Dibuat bulan ini</div>
                                    <div class="h4 mb-0">{{ number_format($thisMonthCreated) }}</div>
                                    <small class="text-muted">vs {{ number_format($lastMonthCreated) }}
                                        @if ($createdMonthGrowthPct != 0)
                                            <span
                                                class="{{ $createdMonthGrowthPct >= 0 ? 'text-success' : 'text-danger' }}">({{ $createdMonthGrowthPct >= 0 ? '+' : '' }}{{ $createdMonthGrowthPct }}%)</span>
                                        @endif
                                    </small>
                                </div>
                                <div class="col-6 col-md-3 mb-3 mb-md-0">
                                    <div class="text-muted text-uppercase small">Tanggal OT {{ now()->format('M Y') }}</div>
                                    <div class="h4 mb-0">{{ number_format($thisMonthOvertimeDate) }}</div>
                                    <small class="text-muted">Request per tanggal lembur</small>
                                </div>
                                <div class="col-6 col-md-3 mb-3 mb-md-0">
                                    <div class="text-muted text-uppercase small">Approved (bisa ditutup)</div>
                                    <div class="h4 mb-0">{{ number_format($approvedAwaitingHrFinish) }}</div>
                                    <small class="text-muted">Tutup di detail</small>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="text-muted text-uppercase small">Langkah approval terbuka</div>
                                    <div class="h4 mb-0">{{ number_format($pendingApprovalSteps) }}</div>
                                    <small class="text-muted">Approval plan pending</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12 mb-3">
                    <div class="card card-outline card-primary h-100">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-project-diagram mr-1"></i> Top projects by volume</h3>
                        </div>
                        <div class="card-body p-0">
                            @if ($byProject->isEmpty())
                                <p class="text-muted p-3 mb-0">No overtime request data yet.</p>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-sm table-striped mb-0">
                                        <thead>
                                            <tr>
                                                <th>Project</th>
                                                <th class="text-right" style="width: 8rem;">Requests</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($byProject as $row)
                                                <tr>
                                                    <td>
                                                        <span class="text-muted small">{{ $row->project_code }}</span>
                                                        — {{ $row->project_name }}
                                                    </td>
                                                    <td class="text-right">
                                                        <span class="badge badge-primary">{{ $row->request_count }}</span>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-6 mb-3">
                    <div class="card card-outline card-success">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-calendar-day mr-1"></i> Upcoming overtime (pending /
                                approved)</h3>
                        </div>
                        <div class="card-body p-0">
                            @if ($upcomingOvertime->isEmpty())
                                <p class="text-muted p-3 mb-0">No upcoming overtime in this filter.</p>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>OT date</th>
                                                <th>Project</th>
                                                <th>Requester</th>
                                                <th>Status</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($upcomingOvertime as $ot)
                                                @php
                                                    $badge = match ($ot->status) {
                                                        'pending' => 'warning',
                                                        'approved' => 'success',
                                                        default => 'secondary',
                                                    };
                                                @endphp
                                                <tr>
                                                    <td>{{ $ot->overtime_date?->format('d M Y') ?? '—' }}</td>
                                                    <td class="text-truncate" style="max-width: 10rem;"
                                                        title="{{ $ot->project->project_name ?? '' }}">
                                                        {{ $ot->project->project_name ?? '—' }}</td>
                                                    <td>{{ $ot->requestedBy->name ?? '—' }}</td>
                                                    <td><span
                                                            class="badge badge-{{ $badge }}">{{ strtoupper($ot->status) }}</span>
                                                    </td>
                                                    <td class="text-right">
                                                        <a href="{{ route('overtime.requests.show', $ot) }}"
                                                            class="btn btn-xs btn-info"><i class="fas fa-eye"></i></a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 mb-3">
                    <div class="card card-outline card-info">
                        <div class="card-header">
                            <h3 class="card-title"><i class="fas fa-history mr-1"></i> Recently created</h3>
                        </div>
                        <div class="card-body p-0">
                            @if ($recentRequests->isEmpty())
                                <p class="text-muted p-3 mb-0">No requests yet.</p>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-sm table-hover mb-0">
                                        <thead>
                                            <tr>
                                                <th>Created</th>
                                                <th>OT date</th>
                                                <th>Project</th>
                                                <th>Status</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($recentRequests as $ot)
                                                @php
                                                    $badge = match ($ot->status) {
                                                        'draft' => 'secondary',
                                                        'pending' => 'warning',
                                                        'approved' => 'success',
                                                        'rejected' => 'danger',
                                                        'finished' => 'info',
                                                        default => 'secondary',
                                                    };
                                                @endphp
                                                <tr>
                                                    <td class="text-nowrap small">
                                                        {{ $ot->created_at?->format('d M Y H:i') ?? '—' }}</td>
                                                    <td class="text-nowrap small">
                                                        {{ $ot->overtime_date?->format('d M Y') ?? '—' }}</td>
                                                    <td class="text-truncate" style="max-width: 8rem;"
                                                        title="{{ $ot->project->project_name ?? '' }}">
                                                        {{ $ot->project->project_name ?? '—' }}</td>
                                                    <td><span
                                                            class="badge badge-{{ $badge }}">{{ strtoupper($ot->status) }}</span>
                                                    </td>
                                                    <td class="text-right">
                                                        <a href="{{ route('overtime.requests.show', $ot) }}"
                                                            class="btn btn-xs btn-info"><i class="fas fa-eye"></i></a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-12">
                    <a href="{{ route('overtime.requests.index') }}" class="btn btn-primary">
                        <i class="fas fa-list mr-1"></i> Open full request list
                    </a>
                    <a href="{{ route('overtime.reports.request-monitoring') }}" class="btn btn-outline-secondary ml-1">
                        <i class="fas fa-file-alt mr-1"></i> Reports
                    </a>
                    @can('overtime-requests.create')
                        <a href="{{ route('overtime.requests.create') }}" class="btn btn-outline-primary ml-1">
                            <i class="fas fa-plus mr-1"></i> New request
                        </a>
                    @endcan
                </div>
            </div>
        </div>
    </section>
@endsection
