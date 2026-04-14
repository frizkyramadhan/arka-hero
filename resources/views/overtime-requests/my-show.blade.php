@extends('layouts.main')

@section('title', $title ?? 'Overtime Request Detail')

@section('content')
    @php
        $user = \Illuminate\Support\Facades\Auth::user();
    @endphp

    @include('partials.official-travel-detail-styles')

    <div class="content-wrapper-custom">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show mx-3 mt-3" role="alert">
                {{ session('success') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show mx-3 mt-3" role="alert">
                {{ session('error') }}
                <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                        aria-hidden="true">&times;</span></button>
            </div>
        @endif

        <div class="travel-header">
            <div class="travel-header-content">
                <div class="travel-number">OVERTIME REQUEST</div>
                <div class="travel-destination">{{ $overtimeRequest->project->project_name ?? '—' }}</div>
                <div class="travel-date">
                    <i class="far fa-calendar-alt mr-1"></i>
                    {{ $overtimeRequest->overtime_date ? $overtimeRequest->overtime_date->format('d M Y') : '—' }}
                </div>
            </div>
            <div class="travel-status-pill">
                @php
                    $status = $overtimeRequest->status;
                    $statusPillClass = match ($status) {
                        'draft' => 'overtime-pill-draft',
                        'pending' => 'overtime-pill-pending',
                        'approved' => 'overtime-pill-approved',
                        'rejected' => 'overtime-pill-rejected',
                        'finished' => 'overtime-pill-finished',
                        default => 'overtime-pill-draft',
                    };
                    $statusIcon = match ($status) {
                        'pending' => 'fa-clock',
                        'approved' => 'fa-check-circle',
                        'rejected' => 'fa-times-circle',
                        'finished' => 'fa-flag-checkered',
                        default => 'fa-file-alt',
                    };
                @endphp
                <span class="overtime-status-pill {{ $statusPillClass }}">
                    <i class="fas {{ $statusIcon }}"></i>
                    {{ strtoupper(str_replace('_', ' ', $status)) }}
                </span>
            </div>
        </div>

        <div class="travel-content">
            <div class="row">
                <div class="col-lg-8">
                    <div class="travel-card">
                        <div class="card-head">
                            <h2><i class="fas fa-info-circle"></i> Overtime Information</h2>
                        </div>
                        <div class="card-body p-0">
                            <div class="info-grid">
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #3498db;"><i
                                            class="fas fa-project-diagram"></i></div>
                                    <div class="info-content">
                                        <div class="info-label">Project</div>
                                        <div class="info-value">{{ $overtimeRequest->project->project_name ?? '—' }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #2ecc71;"><i
                                            class="fas fa-calendar-day"></i></div>
                                    <div class="info-content">
                                        <div class="info-label">Overtime date</div>
                                        <div class="info-value">
                                            {{ $overtimeRequest->overtime_date ? $overtimeRequest->overtime_date->format('d M Y') : '—' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #9b59b6;"><i
                                            class="fas fa-user-tie"></i></div>
                                    <div class="info-content">
                                        <div class="info-label">Created by</div>
                                        <div class="info-value">{{ $overtimeRequest->requestedBy->name ?? '—' }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #e67e22;"><i class="fas fa-clock"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Created at</div>
                                        <div class="info-value">
                                            {{ $overtimeRequest->created_at ? $overtimeRequest->created_at->format('d M Y H:i') : '—' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="info-item overtime-remarks-item" style="grid-column: 1 / -1;">
                                    <div class="info-icon" style="background-color: #1abc9c;"><i
                                            class="fas fa-comment-alt"></i></div>
                                    <div class="info-content">
                                        <div class="info-label">Remarks</div>
                                        <div class="info-value overtime-remarks-value">
                                            {{ $overtimeRequest->remarks ?: '—' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="travel-card">
                        <div class="card-head">
                            <h2><i class="fas fa-users"></i> Employee Details</h2>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>No</th>
                                            <th>Name</th>
                                            <th>NIK</th>
                                            <th>Time in</th>
                                            <th>Time out</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($overtimeRequest->details as $i => $line)
                                            <tr>
                                                <td>{{ $i + 1 }}</td>
                                                <td>{{ $line->administration->employee->fullname ?? '—' }}</td>
                                                <td>{{ $line->administration->nik ?? '—' }}</td>
                                                <td>{{ $line->time_in ? \Carbon\Carbon::parse($line->time_in)->format('H:i') : '—' }}
                                                </td>
                                                <td>{{ $line->time_out ? \Carbon\Carbon::parse($line->time_out)->format('H:i') : '—' }}
                                                </td>
                                                <td>{{ $line->work_description ?? '—' }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center text-muted">No employee lines.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    @if ($overtimeRequest->status === 'finished' && ($overtimeRequest->finished_at || $overtimeRequest->finished_remarks))
                        <div class="travel-card">
                            <div class="card-head">
                                <h2><i class="fas fa-check-double"></i> HR completion</h2>
                            </div>
                            <div class="card-body">
                                @if ($overtimeRequest->finished_at)
                                    <p class="mb-1"><strong>Finished at:</strong>
                                        {{ $overtimeRequest->finished_at->format('d M Y H:i') }}</p>
                                @endif
                                @if ($overtimeRequest->finishedBy)
                                    <p class="mb-1"><strong>By:</strong> {{ $overtimeRequest->finishedBy->name }}</p>
                                @endif
                                @if ($overtimeRequest->finished_remarks)
                                    <p class="mb-0"><strong>Remarks:</strong> {{ $overtimeRequest->finished_remarks }}
                                    </p>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>

                <div class="col-lg-4">
                    @if (!empty($overtimeRequest->manual_approvers) || $overtimeRequest->approvalPlans->isNotEmpty())
                        <div class="travel-card mb-3">
                            <div class="card-head">
                                <h2><i class="fas fa-users"></i> Approval Status</h2>
                            </div>
                            <div class="card-body">
                                @include('components.manual-approver-selector', [
                                    'mode' => 'view',
                                    'documentType' => 'overtime_request',
                                    'documentId' => $overtimeRequest->id,
                                    'selectedApprovers' => $overtimeRequest->manual_approvers ?? [],
                                ])
                            </div>
                        </div>
                    @endif

                    @php
                        $u = $user;
                        $isUser = $u instanceof \App\Models\User;
                        $editable = $overtimeRequest->isEditable();
                        $deletable = $overtimeRequest->isDeletable();
                        $draftOrRejected = in_array($overtimeRequest->status, ['draft', 'rejected'], true);
                        $canEdit = $isUser && $editable && $u->can('personal.overtime.edit-own');
                        $canSubmit = $canEdit && $draftOrRejected;
                        $canDelete = $isUser && $deletable && $u->can('personal.overtime.cancel-own');
                    @endphp

                    <div class="travel-action-buttons">
                        <a href="{{ route('overtime.my-requests') }}" class="btn-action back-btn">
                            <i class="fas fa-arrow-left"></i> Back to my list
                        </a>

                        @if ($canEdit)
                            <a href="{{ route('overtime.my-requests.edit', $overtimeRequest) }}" class="btn-action edit-btn">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        @endif

                        @if ($canSubmit)
                            <form method="POST"
                                action="{{ route('overtime.my-requests.submit-for-approval', $overtimeRequest) }}"
                                onsubmit="return confirm('Submit this overtime request for approval?');">
                                @csrf
                                <button type="submit" class="btn-action submit-approval-btn w-100">
                                    <i class="fas fa-paper-plane"></i> Submit for Approval
                                </button>
                            </form>
                        @endif

                        @if ($canDelete)
                            <form method="POST" action="{{ route('overtime.my-requests.destroy', $overtimeRequest) }}"
                                class="d-inline" onsubmit="return confirm('Delete this overtime request?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-action delete-btn w-100">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
