@extends('layouts.main')

@section('content')
    <div class="content-wrapper-custom">
        <div class="leave-request-header">
            <div class="leave-request-header-content">
                <div class="leave-request-project">
                    {{ $leaveRequest->employee->administrations->first()->project->project_name ?? 'N/A' }}</div>
                <h1 class="leave-request-number">Leave Request</h1>
                <div class="leave-request-date">
                    <i class="far fa-calendar-alt"></i> {{ date('d F Y', strtotime($leaveRequest->created_at)) }}
                </div>
                @php
                    $statusMap = [
                        'pending' => ['label' => 'Pending', 'class' => 'badge badge-warning', 'icon' => 'fa-clock'],
                        'approved' => [
                            'label' => 'Approved',
                            'class' => 'badge badge-success',
                            'icon' => 'fa-check-circle',
                        ],
                        'rejected' => [
                            'label' => 'Rejected',
                            'class' => 'badge badge-danger',
                            'icon' => 'fa-times-circle',
                        ],
                        'cancelled' => ['label' => 'Cancelled', 'class' => 'badge badge-secondary', 'icon' => 'fa-ban'],
                        'auto_approved' => [
                            'label' => 'Auto Approved',
                            'class' => 'badge badge-info',
                            'icon' => 'fa-check-circle',
                        ],
                    ];
                    $status = $leaveRequest->status;
                    $pill = $statusMap[$status] ?? [
                        'label' => ucfirst($status),
                        'class' => 'badge badge-secondary',
                        'icon' => 'fa-question-circle',
                    ];
                @endphp
                <div class="leave-request-status-pill">
                    <span class="{{ $pill['class'] }}">
                        <i class="fas {{ $pill['icon'] }}"></i> {{ $pill['label'] }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Main Content -->
        <div class="leave-request-content">
            <div class="row">
                <!-- Left Column -->
                <div class="col-lg-8">
                    <!-- Main Leave Request Info -->
                    <div class="leave-request-card leave-request-info-card">
                        <div class="card-head">
                            <h2><i class="fas fa-calendar-plus"></i> Leave Request Information</h2>
                        </div>
                        <div class="card-body">
                            <div class="info-grid">
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #3498db;">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Employee</div>
                                        <div class="info-value">{{ $leaveRequest->employee->fullname }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #e74c3c;">
                                        <i class="fas fa-calendar-check"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Leave Type</div>
                                        <div class="info-value">{{ $leaveRequest->leaveType->name }}
                                            ({{ $leaveRequest->leaveType->code }})</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #f1c40f;">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Start Date</div>
                                        <div class="info-value">{{ $leaveRequest->start_date->format('d F Y') }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #9b59b6;">
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">End Date</div>
                                        <div class="info-value">{{ $leaveRequest->end_date->format('d F Y') }}</div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #1abc9c;">
                                        <i class="fas fa-calculator"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Total Days</div>
                                        <div class="info-value">{{ $leaveRequest->total_days }}
                                            {{ $leaveRequest->total_days > 1 ? 'days' : 'day' }}</div>
                                        @if ($leaveRequest->getTotalCancelledDays() > 0)
                                            <div class="info-subtext text-warning">
                                                <i class="fas fa-times-circle"></i>
                                                Cancelled: {{ $leaveRequest->getTotalCancelledDays() }}
                                                day{{ $leaveRequest->getTotalCancelledDays() > 1 ? 's' : '' }}
                                            </div>
                                            <div class="info-subtext text-success">
                                                <i class="fas fa-check-circle"></i>
                                                Effective: {{ $leaveRequest->getEffectiveDays() }}
                                                day{{ $leaveRequest->getEffectiveDays() > 1 ? 's' : '' }}
                                            </div>
                                        @endif
                                    </div>
                                </div>

                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #e67e22;">
                                        <i class="fas fa-calendar-plus"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Back to Work Date</div>
                                        <div class="info-value">
                                            {{ $leaveRequest->back_to_work_date ? $leaveRequest->back_to_work_date->format('d F Y') : 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #34495e;">
                                        <i class="fas fa-clock"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Requested At</div>
                                        <div class="info-value">
                                            {{ $leaveRequest->requested_at ? $leaveRequest->requested_at->format('d F Y H:i') : 'N/A' }}
                                        </div>
                                    </div>
                                </div>
                                <div class="info-item">
                                    <div class="info-icon" style="background-color: #2c3e50;">
                                        <i class="fas fa-calendar-week"></i>
                                    </div>
                                    <div class="info-content">
                                        <div class="info-label">Leave Period</div>
                                        <div class="info-value">{{ $leaveRequest->leave_period ?? 'N/A' }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Reason & Supporting Document -->
                    @if (
                        $leaveRequest->reason ||
                            $leaveRequest->supporting_document ||
                            strtolower($leaveRequest->leaveType->category ?? '') === 'paid' ||
                            $leaveRequest->isLSLFlexible())
                        <div class="leave-request-card requirements-card">
                            <div class="card-head">
                                <h2><i class="fas fa-clipboard-list"></i> Additional Information</h2>
                            </div>
                            <div class="card-body">
                                @if ($leaveRequest->reason)
                                    <div class="requirement-section">
                                        <div class="section-header">
                                            <div class="section-icon" style="background-color: #3498db;">
                                                <i class="fas fa-comment-alt"></i>
                                            </div>
                                            <div class="section-title">Reason</div>
                                        </div>
                                        <div class="section-content">
                                            {{ $leaveRequest->reason }}
                                        </div>
                                    </div>
                                @endif

                                @if ($leaveRequest->isLSLFlexible())
                                    <div class="requirement-section">
                                        <div class="section-header">
                                            <div class="section-icon" style="background-color: #f39c12;">
                                                <i class="fas fa-coins"></i>
                                            </div>
                                            <div class="section-title">Long Service Leave Details</div>
                                        </div>
                                        <div class="section-content">
                                            <!-- LSL Breakdown Table -->
                                            <div class="lsl-breakdown-table">
                                                <div class="lsl-table-header">
                                                    <h4><i class="fas fa-list-alt"></i> LSL Breakdown</h4>
                                                </div>
                                                <div class="lsl-table-content">
                                                    <div class="lsl-table-row">
                                                        <div class="lsl-table-cell">
                                                            <div class="lsl-cell-icon">
                                                                <i class="fas fa-calendar-check"></i>
                                                            </div>
                                                            <div class="lsl-cell-content">
                                                                <div class="lsl-cell-label">Leave Taken</div>
                                                                <div class="lsl-cell-description">Days used as actual
                                                                    leave</div>
                                                            </div>
                                                        </div>
                                                        <div class="lsl-table-value">
                                                            <span
                                                                class="lsl-value-number">{{ $leaveRequest->lsl_taken_days ?? 0 }}</span>
                                                            <span class="lsl-value-unit">days</span>
                                                        </div>
                                                    </div>

                                                    <div class="lsl-table-row">
                                                        <div class="lsl-table-cell">
                                                            <div class="lsl-cell-icon">
                                                                <i class="fas fa-money-bill-wave"></i>
                                                            </div>
                                                            <div class="lsl-cell-content">
                                                                <div class="lsl-cell-label">Cash Out</div>
                                                                <div class="lsl-cell-description">Days converted to
                                                                    cash payment</div>
                                                            </div>
                                                        </div>
                                                        <div class="lsl-table-value">
                                                            <span
                                                                class="lsl-value-number">{{ $leaveRequest->lsl_cashout_days ?? 0 }}</span>
                                                            <span class="lsl-value-unit">days</span>
                                                        </div>
                                                    </div>

                                                    <div class="lsl-table-row total-row">
                                                        <div class="lsl-table-cell">
                                                            <div class="lsl-cell-icon">
                                                                <i class="fas fa-calculator"></i>
                                                            </div>
                                                            <div class="lsl-cell-content">
                                                                <div class="lsl-cell-label">Total LSL Used</div>
                                                                <div class="lsl-cell-description">Combined leave and
                                                                    cash out</div>
                                                            </div>
                                                        </div>
                                                        <div class="lsl-table-value">
                                                            <span
                                                                class="lsl-value-number">{{ $leaveRequest->getLSLTotalDays() }}</span>
                                                            <span class="lsl-value-unit">days</span>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            @if (($leaveRequest->lsl_cashout_days ?? 0) > 0)
                                                <div class="lsl-cashout-note">
                                                    <i class="fas fa-info-circle"></i>
                                                    This request includes {{ $leaveRequest->lsl_cashout_days }} day(s)
                                                    of Long Service Leave cash out.
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                @if (strtolower($leaveRequest->leaveType->category ?? '') === 'paid')
                                    <div class="requirement-section">
                                        <div class="section-header">
                                            <div class="section-icon" style="background-color: #e67e22;">
                                                <i class="fas fa-file-upload"></i>
                                            </div>
                                            <div class="section-title">Supporting Document</div>
                                        </div>
                                        <div class="section-content">
                                            @if ($leaveRequest->supporting_document)
                                                <div class="document-actions mb-3">
                                                    <a href="{{ route('leave.requests.download', $leaveRequest) }}"
                                                        target="_blank" class="btn btn-primary btn-sm">
                                                        <i class="fas fa-download"></i> Download Document
                                                    </a>
                                                    @if ($leaveRequest->status === 'pending')
                                                        <button type="button" class="btn btn-danger btn-sm"
                                                            onclick="deleteDocument('{{ $leaveRequest->id }}')">
                                                            <i class="fas fa-trash"></i> Delete Document
                                                        </button>
                                                    @endif
                                                </div>
                                            @else
                                                <div class="alert alert-warning mb-3">
                                                    <i class="fas fa-exclamation-triangle"></i>
                                                    <strong>Paid Leave Type:</strong> This leave type requires supporting
                                                    documentation.
                                                    <br><em>No supporting document has been uploaded yet.</em>
                                                </div>

                                                @if (!in_array($leaveRequest->status, ['closed', 'cancelled']))
                                                    <form action="{{ route('leave.requests.upload', $leaveRequest) }}"
                                                        method="POST" enctype="multipart/form-data" class="upload-form">
                                                        @csrf
                                                        <div class="form-group">
                                                            <label for="supporting_document">Upload Supporting
                                                                Document:</label>
                                                            <input type="file" class="form-control-file"
                                                                id="supporting_document" name="supporting_document"
                                                                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                                                            <small class="form-text text-muted">Accepted formats: PDF, DOC,
                                                                DOCX, JPG, JPEG, PNG (Max: 5MB)</small>
                                                        </div>
                                                        <button type="submit" class="btn btn-success btn-sm">
                                                            <i class="fas fa-upload"></i> Upload Document
                                                        </button>
                                                    </form>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                @endif


                            </div>
                        </div>
                    @endif
                </div>

                <!-- Right Column -->
                <div class="col-lg-4">
                    <!-- Approval Status Card -->
                    @php
                        $employeeAdministration = $leaveRequest->employee->administrations
                            ->where('is_active', 1)
                            ->first();
                        $projectId = $employeeAdministration->project_id ?? null;
                        $departmentId = $employeeAdministration->position->department_id ?? null;
                        $levelId = $employeeAdministration->level_id ?? null;
                        $projectName = $employeeAdministration->project->project_name ?? null;
                        $departmentName = $employeeAdministration->position->department->department_name ?? null;
                    @endphp
                    <x-approval-status-card :documentType="'leave_request'" :documentId="$leaveRequest->id" :mode="'status'" :projectId="$projectId"
                        :departmentId="$departmentId" :levelId="$levelId" :projectName="$projectName" :departmentName="$departmentName" :requestReason="null"
                        title="Approval Status" />

                    <!-- Cancellation Requests Section -->
                    @if ($leaveRequest->cancellations->count() > 0)
                        <div class="card mb-4">
                            <div class="card-header">
                                <h5 class="mb-0">
                                    <i class="fas fa-times-circle text-danger"></i> Cancellation Requests
                                </h5>
                            </div>
                            <div class="card-body">
                                @foreach ($leaveRequest->cancellations as $cancellation)
                                    <div class="cancellation-item mb-3 p-3 border rounded">
                                        <div class="row">
                                            <div class="col-md-8">
                                                <h6 class="mb-2">
                                                    Request to Cancel: {{ $cancellation->days_to_cancel }} day(s)
                                                </h6>
                                                <p class="mb-2"><strong>Reason:</strong> {{ $cancellation->reason }}</p>
                                                <p class="mb-2"><strong>Requested by:</strong>
                                                    {{ $cancellation->requestedBy->name ?? 'N/A' }}</p>
                                                <p class="mb-2"><strong>Requested at:</strong>
                                                    {{ $cancellation->requested_at->format('d/m/Y H:i') }}</p>

                                                @if ($cancellation->confirmed_at)
                                                    <p class="mb-2"><strong>Confirmed by:</strong>
                                                        {{ $cancellation->confirmedBy->name ?? 'N/A' }}</p>
                                                    <p class="mb-2"><strong>Confirmed at:</strong>
                                                        {{ $cancellation->confirmed_at->format('d/m/Y H:i') }}</p>
                                                    @if ($cancellation->confirmation_notes)
                                                        <p class="mb-2"><strong>Notes:</strong>
                                                            {{ $cancellation->confirmation_notes }}</p>
                                                    @endif
                                                @endif
                                            </div>
                                            <div class="col-md-4 text-right">
                                                @if ($cancellation->status === 'pending')
                                                    <span class="badge badge-warning">Pending</span>
                                                    <div class="mt-2">
                                                        <form method="POST"
                                                            action="{{ route('leave.requests.cancellation.approve', $cancellation) }}"
                                                            style="display: inline;">
                                                            @csrf
                                                            <button type="submit" class="btn btn-success btn-sm"
                                                                onclick="return confirm('Approve this cancellation request?')">
                                                                <i class="fas fa-check"></i> Approve
                                                            </button>
                                                        </form>
                                                        <button type="button" class="btn btn-danger btn-sm ml-1"
                                                            data-toggle="modal"
                                                            data-target="#rejectModal{{ $cancellation->id }}">
                                                            <i class="fas fa-times"></i> Reject
                                                        </button>
                                                    </div>
                                                @elseif ($cancellation->status === 'approved')
                                                    <span class="badge badge-success">Approved</span>
                                                @elseif ($cancellation->status === 'rejected')
                                                    <span class="badge badge-danger">Rejected</span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Reject Modal -->
                                    <div class="modal fade" id="rejectModal{{ $cancellation->id }}" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Reject Cancellation Request</h5>
                                                    <button type="button" class="close" data-dismiss="modal">
                                                        <span>&times;</span>
                                                    </button>
                                                </div>
                                                <form method="POST"
                                                    action="{{ route('leave.requests.cancellation.reject', $cancellation) }}">
                                                    @csrf
                                                    <div class="modal-body">
                                                        <div class="form-group">
                                                            <label for="confirmation_notes">Reason for rejection:</label>
                                                            <textarea class="form-control" name="confirmation_notes" rows="3" required></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary"
                                                            data-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-danger">Reject
                                                            Request</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Action Buttons -->
                    <div class="leave-request-action-buttons">
                        <a href="{{ route('leave.requests.index') }}" class="btn-action back-btn">
                            <i class="fas fa-arrow-left"></i> Back to List
                        </a>

                        @if ($leaveRequest->status === 'pending')
                            <a href="{{ route('leave.requests.edit', $leaveRequest) }}" class="btn-action edit-btn">
                                <i class="fas fa-edit"></i> Edit Request
                            </a>
                        @endif

                        @if ($leaveRequest->canBeClosed())
                            <form method="POST" action="{{ route('leave.requests.close', $leaveRequest) }}"
                                style="display: inline;">
                                @csrf
                                <button type="submit" class="btn-action close-btn"
                                    onclick="return confirm('Are you sure you want to close this leave request?')">
                                    <i class="fas fa-check-circle"></i> Close Request
                                </button>
                            </form>
                        @endif

                        @if ($leaveRequest->canBeCancelled())
                            <a href="{{ route('leave.requests.cancellation-form', $leaveRequest) }}"
                                class="btn-action cancel-btn">
                                <i class="fas fa-times-circle"></i> Request Cancellation
                            </a>
                        @endif


                        <a href="{{ route('leave.entitlements.employee.show', $leaveRequest->employee) }}"
                            class="btn-action print-btn">
                            <i class="fas fa-calendar-alt"></i> View Entitlements
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        /* Custom Styles for Leave Request Detail */
        .content-wrapper-custom {
            background-color: #f8fafc;
            min-height: 100vh;
            padding-bottom: 40px;
        }

        /* Header */
        .leave-request-header {
            position: relative;
            height: 120px;
            color: white;
            padding: 20px 30px;
            margin-bottom: 30px;
            background: linear-gradient(135deg, #2c3e50 0%, #3498db 100%);
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .leave-request-header-content {
            position: relative;
            z-index: 2;
            height: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .leave-request-project {
            font-size: 13px;
            margin-bottom: 4px;
            opacity: 0.9;
            letter-spacing: 1px;
        }

        .leave-request-number {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .leave-request-date {
            font-size: 14px;
            opacity: 0.9;
        }

        .leave-request-status-pill {
            position: absolute;
            top: 20px;
            right: 20px;
        }

        .leave-request-status-pill .badge {
            font-size: 0.875rem;
            padding: 0.5rem 0.75rem;
            border-radius: 0.375rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        /* Content Styles */
        .leave-request-content {
            padding: 0 20px;
        }

        /* Cards */
        .leave-request-card {
            background: white;
            border-radius: 6px;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            margin-bottom: 20px;
        }

        .card-head {
            padding: 15px 20px;
            border-bottom: 1px solid #e9ecef;
            background-color: #f8f9fa;
        }

        .card-head h2 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .card-body {
            padding: 20px;
        }

        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
            padding: 20px;
        }

        .info-item {
            display: flex;
            align-items: flex-start;
            gap: 12px;
        }

        .info-icon {
            width: 32px;
            height: 32px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 14px;
            background-color: #3498db;
        }

        .info-content {
            flex: 1;
        }

        .info-label {
            font-size: 12px;
            color: #777;
            margin-bottom: 4px;
        }

        .info-value {
            font-weight: 600;
            color: #333;
        }

        .info-subtext {
            font-size: 0.8rem;
            margin-top: 2px;
            font-weight: 500;
        }

        /* Simplified LSL Flexible Details Styles */
        .lsl-flexible-details {
            background-color: #ffffff;
            border-radius: 8px;
            padding: 20px;
            border: 1px solid #e9ecef;
            margin-top: 0;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        /* LSL Breakdown Table */
        .lsl-breakdown-table {
            background-color: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 20px;
            overflow: hidden;
            border: 1px solid #e9ecef;
        }

        .lsl-table-header {
            background: linear-gradient(135deg, #2c3e50, #34495e);
            color: white;
            padding: 15px 20px;
        }

        .lsl-table-header h4 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .lsl-table-content {
            padding: 0;
        }

        .lsl-table-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 15px 20px;
            border-bottom: 1px solid #e9ecef;
            transition: background-color 0.2s ease;
        }

        .lsl-table-row:last-child {
            border-bottom: none;
        }

        .lsl-table-row:hover {
            background-color: #f1f3f4;
        }

        .lsl-table-row.total-row {
            background: linear-gradient(135deg, #e8f5e8, #f0f8f0);
            border-top: 2px solid #28a745;
            font-weight: 600;
        }

        .lsl-table-cell {
            display: flex;
            align-items: center;
            gap: 12px;
            flex: 1;
        }

        .lsl-cell-icon {
            width: 35px;
            height: 35px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            color: white;
            flex-shrink: 0;
        }

        .lsl-table-row:nth-child(1) .lsl-cell-icon {
            background: linear-gradient(135deg, #3498db, #2980b9);
        }

        .lsl-table-row:nth-child(2) .lsl-cell-icon {
            background: linear-gradient(135deg, #f39c12, #e67e22);
        }

        .lsl-table-row.total-row .lsl-cell-icon {
            background: linear-gradient(135deg, #27ae60, #2ecc71);
        }

        .lsl-cell-content {
            flex: 1;
        }

        .lsl-cell-label {
            font-size: 14px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 2px;
        }

        .lsl-cell-description {
            font-size: 12px;
            color: #6c757d;
        }

        .lsl-table-value {
            display: flex;
            align-items: baseline;
            gap: 4px;
        }

        .lsl-value-number {
            font-size: 18px;
            font-weight: 700;
            color: #2c3e50;
        }

        .lsl-table-row.total-row .lsl-value-number {
            color: #28a745;
        }

        .lsl-value-unit {
            font-size: 12px;
            color: #6c757d;
            font-weight: 500;
        }

        /* Simple Cash Out Note */
        .lsl-cashout-note {
            background-color: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 4px;
            padding: 8px 12px;
            font-size: 12px;
            color: #856404;
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 8px;
        }

        .lsl-cashout-note i {
            color: #f39c12;
        }

        /* Responsive adjustments for simplified LSL details */
        @media (max-width: 768px) {
            .lsl-table-row {
                flex-direction: column;
                align-items: flex-start;
                gap: 10px;
                padding: 12px 15px;
            }

            .lsl-table-cell {
                width: 100%;
            }

            .lsl-table-value {
                align-self: flex-end;
            }

            .lsl-cashout-note {
                padding: 12px;
            }
        }

        @media (max-width: 480px) {
            .lsl-flexible-details {
                padding: 15px;
            }
        }

        /* Section Headers */
        .section-header {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 12px;
        }

        .section-icon {
            width: 32px;
            height: 32px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 14px;
        }

        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #2c3e50;
        }

        .section-content {
            font-size: 14px;
            line-height: 1.6;
            color: #333;
            background-color: #ffffff;
            padding: 15px;
            border-radius: 4px;
            border: 1px solid #e9ecef;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .requirement-section {
            margin-bottom: 20px;
        }

        .requirement-section:last-child {
            margin-bottom: 0;
        }

        /* Document Actions */
        .document-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            flex-wrap: wrap;
        }

        .document-actions .btn {
            margin: 0;
        }

        /* Requester Card */
        .requester-info {
            text-align: center;
        }

        .requester-name {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 8px;
        }

        .requester-email {
            font-size: 14px;
            color: #64748b;
            margin-bottom: 12px;
        }

        .requester-date {
            font-size: 13px;
            color: #64748b;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            margin-bottom: 8px;
        }

        /* Action Buttons */
        .leave-request-action-buttons {
            display: grid;
            grid-template-columns: 1fr;
            gap: 10px;
        }

        .btn-action {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 12px 16px;
            border-radius: 6px;
            font-weight: 500;
            font-size: 14px;
            transition: all 0.2s;
            gap: 8px;
            color: white;
            text-decoration: none;
            border: none;
            cursor: pointer;
            width: 100%;
            min-height: 44px;
        }

        .back-btn {
            background-color: #6c757d;
        }

        .back-btn:hover {
            background-color: #5a6268;
            color: white;
        }

        .edit-btn {
            background-color: #007bff;
        }

        .edit-btn:hover {
            background-color: #0056b3;
            color: white;
        }

        .print-btn {
            background-color: #28a745;
        }

        .print-btn:hover {
            background-color: #1e7e34;
            color: white;
        }

        .close-btn {
            background-color: #17a2b8;
        }

        .close-btn:hover {
            background-color: #138496;
            color: white;
        }

        .cancel-btn {
            background-color: #dc3545;
        }

        .cancel-btn:hover {
            background-color: #c82333;
            color: white;
        }

        .btn-action:hover {
            opacity: 0.9;
            transform: translateY(-1px);
        }

        /* Responsive Adjustments */
        @media (max-width: 992px) {
            .info-grid {
                grid-template-columns: 1fr;
            }

            /* Reorder columns on mobile */
            .leave-request-content .row {
                display: flex;
                flex-direction: column;
            }

            .leave-request-content .col-lg-8 {
                order: 1;
                width: 100%;
            }

            .leave-request-content .col-lg-4 {
                order: 2;
                width: 100%;
            }

            /* Adjust padding for better mobile view */
            .leave-request-content {
                padding: 0 15px;
            }
        }

        @media (max-width: 768px) {
            .leave-request-header {
                height: auto;
                padding: 15px;
                position: relative;
            }

            .leave-request-header-content {
                padding-right: 80px;
            }

            .leave-request-number {
                font-size: 20px;
            }

            .leave-request-status-pill {
                position: absolute;
                top: 15px;
                right: 15px;
                margin-top: 0;
                align-self: flex-start;
            }

            .card-body {
                padding: 15px;
            }

            .info-item {
                padding: 10px 0;
            }
        }
    </style>
@endsection

@section('scripts')
    <script>
        function deleteDocument(leaveRequestId) {
            if (confirm('Are you sure you want to delete this supporting document? This action cannot be undone.')) {
                // Create a form to submit the delete request
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `{{ url('leave/requests') }}/${leaveRequestId}/delete-document`;

                // Add CSRF token
                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';
                form.appendChild(csrfToken);

                // Add method override for DELETE
                const methodField = document.createElement('input');
                methodField.type = 'hidden';
                methodField.name = '_method';
                methodField.value = 'DELETE';
                form.appendChild(methodField);

                // Submit the form
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
@endsection
