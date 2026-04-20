@extends('layouts.main')

@section('title', 'Overtime Request Detail')

@section('content')
    @php
        $user = \Illuminate\Support\Facades\Auth::user();
        $fromPersonal = $fromPersonal ?? false;
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
                @if ($overtimeRequest->register_number)
                    <div class="text-white-50 small mb-1" style="letter-spacing: 0.05em;">
                        <i class="fas fa-hashtag"></i> {{ $overtimeRequest->register_number }}
                    </div>
                @endif
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
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #1abc9c;"><i
                                            class="fas fa-comment-alt"></i></div>
                                    <div class="info-content">
                                        <div class="info-label">Remarks</div>
                                        <div class="info-value">
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
                        $permEdit = $fromPersonal ? 'personal.overtime.edit-own' : 'overtime-requests.edit';
                        $permDelete = $fromPersonal ? 'personal.overtime.cancel-own' : 'overtime-requests.delete';
                        $canEdit = $isUser && $editable && $u->can($permEdit);
                        $canSubmit = $canEdit && $draftOrRejected;
                        $canDelete = $isUser && $deletable && $u->can($permDelete);
                        $canFinishHr =
                            !$fromPersonal &&
                            $isUser &&
                            $u->can('overtime-requests.finish') &&
                            $overtimeRequest->status === 'approved';
                    @endphp

                    <div class="travel-action-buttons">
                        <a href="{{ $fromPersonal ? route('overtime.my-requests') : route('overtime.requests.index') }}"
                            class="btn-action back-btn">
                            <i class="fas fa-arrow-left"></i> {{ $fromPersonal ? 'Back to my list' : 'Back to list' }}
                        </a>

                        @if ($canEdit)
                            <a href="{{ $fromPersonal ? route('overtime.my-requests.edit', $overtimeRequest) : route('overtime.requests.edit', $overtimeRequest) }}"
                                class="btn-action edit-btn">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                        @endif

                        @if ($canSubmit)
                            <form method="POST"
                                action="{{ $fromPersonal ? route('overtime.my-requests.submit-for-approval', $overtimeRequest) : route('overtime.requests.submit-for-approval', $overtimeRequest) }}"
                                onsubmit="return confirm('Submit this overtime request for approval?');">
                                @csrf
                                <button type="submit" class="btn-action submit-approval-btn w-100">
                                    <i class="fas fa-paper-plane"></i> Submit for Approval
                                </button>
                            </form>
                        @endif

                        @if ($canDelete)
                            <form method="POST"
                                action="{{ $fromPersonal ? route('overtime.my-requests.destroy', $overtimeRequest) : route('overtime.requests.destroy', $overtimeRequest) }}"
                                class="d-inline" onsubmit="return confirm('Delete this overtime request?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn-action delete-btn w-100">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        @endif

                        @if ($canFinishHr)
                            <button type="button" class="btn-action finish-btn" data-toggle="modal"
                                data-target="#finishModal">
                                <i class="fas fa-lock"></i> Close Request
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (
        !$fromPersonal &&
            $user instanceof \App\Models\User &&
            $user->can('overtime-requests.finish') &&
            $overtimeRequest->status === 'approved')
        <div class="modal fade" id="finishModal" tabindex="-1" role="dialog" aria-labelledby="finishModalLabel"
            aria-hidden="true">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <form action="{{ route('overtime.requests.finish', $overtimeRequest) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="finishModalLabel">Mark as finished</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <div class="form-group">
                                <label for="finished_remarks">Remarks (optional)</label>
                                <textarea name="finished_remarks" id="finished_remarks" class="form-control" rows="3" maxlength="1000"
                                    placeholder="HR completion notes">{{ old('finished_remarks') }}</textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-info">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endsection
