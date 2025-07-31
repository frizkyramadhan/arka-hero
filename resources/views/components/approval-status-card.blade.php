@props([
    'documentType' => '',
    'documentId' => null,
    'title' => 'Approval Status',
])

<div class="card card-info card-outline elevation-3">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-clipboard-check mr-2"></i>
            <strong>{{ $title }}</strong>
        </h3>
    </div>
    <div class="card-body">
        @php
            $approvalPlans = \App\Models\ApprovalPlan::with(['approver'])
                ->where('document_type', $documentType)
                ->where('document_id', $documentId)
                ->orderBy('id', 'asc')
                ->get();
        @endphp

        @if ($approvalPlans->count() > 0)
            <div class="approval-flow">
                @foreach ($approvalPlans as $index => $plan)
                    <div class="approval-step mb-3">
                        <div class="d-flex align-items-center mb-2">
                            <span class="badge badge-primary mr-2">{{ $index + 1 }}</span>
                            <div class="flex-grow-1">
                                <strong>{{ $plan->approver->name ?? 'Unknown' }}</strong>
                                <small class="text-muted d-block">
                                    @if ($plan->approver && $plan->approver->departments->first())
                                        {{ $plan->approver->departments->first()->department_name }}
                                    @else
                                        No Department
                                    @endif
                                </small>
                            </div>
                            <span
                                class="badge badge-{{ $plan->status == 1 ? 'success' : ($plan->status == 2 ? 'danger' : 'warning') }}">
                                {{ $plan->status == 1 ? 'Approved' : ($plan->status == 2 ? 'Rejected' : 'Pending') }}
                            </span>
                        </div>

                        @if ($plan->status != 0 && $plan->remarks)
                            <div class="approval-remark">
                                <small class="text-muted">
                                    <i class="fas fa-comment"></i> {{ $plan->remarks }}
                                </small>
                            </div>
                        @endif

                        @if ($plan->status != 0 && $plan->updated_at)
                            <div class="approval-time">
                                <small class="text-muted">
                                    <i class="fas fa-clock"></i>
                                    {{ $plan->updated_at->format('d/m/Y H:i') }}
                                </small>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center text-muted py-3">
                <i class="fas fa-info-circle"></i>
                <div class="mt-2">No approval flow configured</div>
            </div>
        @endif
    </div>
</div>

<style>
    /* Approval Status Card Styles */
    .card.card-info.card-outline {
        border-top: 3px solid #17a2b8;
    }

    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #dee2e6;
    }

    .card-title {
        color: #495057;
        font-size: 1.1rem;
        margin: 0;
    }

    /* Approval Flow Styling */
    .approval-flow {
        max-height: 400px;
        overflow-y: auto;
    }

    .approval-step {
        padding: 12px;
        border-left: 3px solid #007bff;
        background: #f8f9fa;
        border-radius: 6px;
        margin-bottom: 12px;
        position: relative;
    }

    .approval-step:last-child {
        border-left-color: #28a745;
    }

    /* Remove connecting lines to match OfficialTravel style */
    .approval-step:not(:last-child)::after {
        display: none;
    }

    .approval-step .badge {
        min-width: 25px;
        height: 25px;
        line-height: 15px;
        font-size: 0.75rem;
    }

    .approval-remark {
        margin-top: 8px;
        padding: 8px 12px;
        background: #e9ecef;
        border-radius: 4px;
        border-left: 3px solid #6c757d;
    }

    .approval-time {
        margin-top: 4px;
        font-size: 0.8rem;
    }

    .approval-step .badge.badge-success {
        background-color: #28a745;
    }

    .approval-step .badge.badge-danger {
        background-color: #dc3545;
    }

    .approval-step .badge.badge-warning {
        background-color: #ffc107;
        color: #212529;
    }
</style>
