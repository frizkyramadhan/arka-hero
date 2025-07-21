<div class="row">
    <div class="col-md-6">
        <table class="table table-borderless">
            <tr>
                <td><strong>Action:</strong></td>
                <td>
                    <span
                        class="badge badge-{{ $action->action === 'approved' ? 'success' : ($action->action === 'rejected' ? 'danger' : 'info') }}">
                        {{ ucfirst($action->action) }}
                    </span>
                </td>
            </tr>
            <tr>
                <td><strong>Approver:</strong></td>
                <td>{{ $action->approver->name ?? 'Unknown' }}</td>
            </tr>
            <tr>
                <td><strong>Action Date:</strong></td>
                <td>{{ $action->action_date->format('M d, Y H:i:s') }}</td>
            </tr>
            <tr>
                <td><strong>Stage:</strong></td>
                <td>{{ $action->approvalStage->stage_name ?? 'Unknown' }}</td>
            </tr>
        </table>
    </div>
    <div class="col-md-6">
        <table class="table table-borderless">
            <tr>
                <td><strong>Document Type:</strong></td>
                <td>{{ ucfirst(str_replace('_', ' ', $action->documentApproval->document_type ?? 'Unknown')) }}</td>
            </tr>
            <tr>
                <td><strong>Document ID:</strong></td>
                <td>{{ $action->documentApproval->document_id ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td><strong>Flow:</strong></td>
                <td>{{ $action->documentApproval->approvalFlow->name ?? 'Unknown' }}</td>
            </tr>
            <tr>
                <td><strong>Is Automatic:</strong></td>
                <td>
                    <span class="badge badge-{{ $action->is_automatic ? 'warning' : 'info' }}">
                        {{ $action->is_automatic ? 'Yes' : 'No' }}
                    </span>
                </td>
            </tr>
        </table>
    </div>
</div>

@if ($action->comments)
    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">
                        <i class="fas fa-comment"></i>
                        Comments
                    </h6>
                </div>
                <div class="card-body">
                    <p class="mb-0">{{ $action->comments }}</p>
                </div>
            </div>
        </div>
    </div>
@endif

@if ($action->forwarded_to)
    <div class="row mt-3">
        <div class="col-12">
            <div class="alert alert-info">
                <i class="fas fa-share"></i>
                <strong>Forwarded to:</strong> {{ $action->forwardedTo->name ?? 'Unknown' }}
            </div>
        </div>
    </div>
@endif

@if ($action->delegated_to)
    <div class="row mt-3">
        <div class="col-12">
            <div class="alert alert-warning">
                <i class="fas fa-user-friends"></i>
                <strong>Delegated to:</strong> {{ $action->delegatedTo->name ?? 'Unknown' }}
            </div>
        </div>
    </div>
@endif

@if ($action->metadata)
    <div class="row mt-3">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <h6 class="card-title">
                        <i class="fas fa-cogs"></i>
                        Additional Information
                    </h6>
                </div>
                <div class="card-body">
                    <pre class="mb-0">{{ json_encode($action->metadata, JSON_PRETTY_PRINT) }}</pre>
                </div>
            </div>
        </div>
    </div>
@endif

<div class="row mt-3">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h6 class="card-title">
                    <i class="fas fa-clock"></i>
                    Timeline
                </h6>
            </div>
            <div class="card-body">
                <div class="timeline">
                    <div class="timeline-item">
                        <div class="timeline-marker bg-info">
                            <i class="fas fa-clock"></i>
                        </div>
                        <div class="timeline-content">
                            <div class="timeline-header">
                                <h6 class="timeline-title">Action Performed</h6>
                                <small class="text-muted">{{ $action->action_date->format('M d, Y H:i:s') }}</small>
                            </div>
                            <div class="timeline-body">
                                <p>{{ ucfirst($action->action) }} by {{ $action->approver->name ?? 'Unknown' }}</p>
                            </div>
                        </div>
                    </div>

                    @if ($action->documentApproval->submitted_at)
                        <div class="timeline-item">
                            <div class="timeline-marker bg-success">
                                <i class="fas fa-paper-plane"></i>
                            </div>
                            <div class="timeline-content">
                                <div class="timeline-header">
                                    <h6 class="timeline-title">Document Submitted</h6>
                                    <small
                                        class="text-muted">{{ $action->documentApproval->submitted_at->format('M d, Y H:i:s') }}</small>
                                </div>
                                <div class="timeline-body">
                                    <p>Submitted by {{ $action->documentApproval->submittedBy->name ?? 'Unknown' }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
