@props([
    'documentType' => '',
    'documentId' => null,
    'title' => 'Approval Status',
    'mode' => 'status', // 'status' or 'preview'
    'projectId' => null,
    'departmentId' => null,
    'projectName' => null,
    'departmentName' => null,
    'requestReason' => null,
    'levelId' => null,
    'id' => 'approvalStatusCard',
])

<div class="card card-info card-outline elevation-3" id="{{ $id }}">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-clipboard-check mr-2"></i>
            <strong>{{ $title }}</strong>
        </h3>
    </div>
    <div class="card-body">
        @if ($mode === 'preview')
            {{-- Approval Preview Mode --}}
            <div id="approvalPreview">
                @php
                    $hasRequiredFields = false;
                    if ($documentType === 'recruitment_request') {
                        $hasRequiredFields = $projectId && $departmentId && $requestReason;
                    } elseif ($documentType === 'officialtravel') {
                        $hasRequiredFields = $projectId && $departmentId;
                    } elseif ($documentType === 'leave_request') {
                        $hasRequiredFields = $projectId && $departmentId;
                    } else {
                        $hasRequiredFields = $projectId && $departmentId;
                    }
                @endphp

                @if ($hasRequiredFields)
                    <div class="text-center py-3">
                        <i class="fas fa-spinner fa-spin text-info"></i>
                        <div class="mt-2">Loading approval flow...</div>
                    </div>
                @else
                    <div class="text-center py-3">
                        <i class="fas fa-info-circle text-info"></i>
                        <div class="mt-2">
                            @if ($documentType === 'recruitment_request')
                                Select project, department, and request reason to see approval flow
                            @elseif ($documentType === 'officialtravel')
                                Select project and main traveler to see approval flow
                            @elseif ($documentType === 'leave_request')
                                Select employee to see approval flow
                            @else
                                Select required fields to see approval flow
                            @endif
                        </div>

                        @if ($documentType === 'recruitment_request' && (!$projectId || !$departmentId || !$requestReason))
                            <div class="mt-2">
                                <small class="text-muted">
                                    Missing:
                                    @if (!$projectId)
                                        Project
                                    @endif
                                    @if (!$departmentId)
                                        Department
                                    @endif
                                    @if (!$requestReason)
                                        Request Reason
                                    @endif
                                </small>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            @if ($hasRequiredFields)
                <script>
                    // Debug: Check if values are being passed correctly
                    console.log('Component props:', {
                        projectId: {{ $projectId ?? 'null' }},
                        departmentId: {{ $departmentId ?? 'null' }},
                        requestReason: '{{ $requestReason ?? '' }}',
                        documentType: '{{ $documentType }}',
                        mode: '{{ $mode }}'
                    });

                    @php
                        $isValidForRecruitment = $documentType === 'recruitment_request' ? $projectId && $departmentId && $requestReason : true;
                        $isValidForOfficialTravel = $documentType === 'officialtravel' ? $projectId && $departmentId : true;
                        $isValidForLeaveRequest = $documentType === 'leave_request' ? $projectId && $departmentId : true;
                        $isValidOverall = $isValidForRecruitment && $isValidForOfficialTravel && $isValidForLeaveRequest;
                    @endphp

                    // Debug: Check if the values are valid based on document type
                    @if (!$isValidOverall)
                        console.error('Invalid component props for {{ $documentType }}:', {
                            projectId: {{ $projectId ?? 'null' }},
                            departmentId: {{ $departmentId ?? 'null' }},
                            requestReason: '{{ $requestReason ?? '' }}',
                            documentType: '{{ $documentType }}'
                        });
                    @else
                        console.log('Component props are valid for {{ $documentType }}, proceeding with approval preview');
                    @endif

                    // Wait for jQuery to be available
                    function loadApprovalPreview() {
                        if (typeof $ === 'undefined') {
                            // If jQuery is not available yet, wait a bit and try again
                            setTimeout(loadApprovalPreview, 100);
                            return;
                        }

                        // Validate that we have valid values based on document type
                        @if ($documentType === 'recruitment_request')
                            if (!{{ $projectId }} || !{{ $departmentId }} || !'{{ $requestReason }}') {
                                console.error('Invalid project, department ID, or request reason for recruitment request');
                                $('#approvalPreview').html(`
                                    <div class="text-center text-danger py-3">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <div class="mt-2">Invalid project, department, or request reason configuration</div>
                                        <small class="text-muted">
                                            @if ($projectName && $departmentName)
                                                Project: {{ $projectName }}, Department: {{ $departmentName }}, Request Reason: {{ $requestReason }}
                                            @else
                                                Project ID: {{ $projectId }}, Department ID: {{ $departmentId }}, Request Reason: {{ $requestReason }}
                                            @endif
                                        </small>
                                    </div>
                                `);
                                return;
                            }
                        @elseif ($documentType === 'leave_request')
                            if (!{{ $projectId }} || !{{ $departmentId }}) {
                                console.error('Invalid project or department ID for leave request');
                                $('#approvalPreview').html(`
                                    <div class="text-center text-danger py-3">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <div class="mt-2">Invalid project or department configuration</div>
                                        <small class="text-muted">
                                            @if ($projectName && $departmentName)
                                                Project: {{ $projectName }}, Department: {{ $departmentName }}
                                            @else
                                                Project ID: {{ $projectId }}, Department ID: {{ $departmentId }}
                                            @endif
                                        </small>
                                    </div>
                                `);
                                return;
                            }
                        @else
                            if (!{{ $projectId }} || !{{ $departmentId }}) {
                                console.error('Invalid project or department ID');
                                $('#approvalPreview').html(`
                                    <div class="text-center text-danger py-3">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <div class="mt-2">Invalid project or department configuration</div>
                                        <small class="text-muted">
                                            @if ($projectName && $departmentName)
                                                Project: {{ $projectName }}, Department: {{ $departmentName }}
                                            @else
                                                Project ID: {{ $projectId }}, Department ID: {{ $departmentId }}
                                            @endif
                                        </small>
                                    </div>
                                `);
                                return;
                            }
                        @endif

                        const requestData = {
                            project_id: {{ $projectId }},
                            department_id: {{ $departmentId }},
                            document_type: '{{ $documentType }}'
                        };

                        @if ($documentType === 'recruitment_request' && $requestReason)
                            requestData.request_reason = '{{ $requestReason }}';
                        @endif

                        @if ($documentType === 'leave_request' && $levelId)
                            requestData.level_id = {{ $levelId }};
                        @endif

                        console.log('Loading approval preview for:', requestData);

                        // Fetch approval stages
                        $.ajax({
                            url: '{{ route('approval.stages.preview') }}',
                            method: 'GET',
                            data: requestData,
                            success: function(response) {
                                console.log('Approval preview response:', response);
                                console.log('Response structure:', {
                                    success: response.success,
                                    approversCount: response.approvers ? response.approvers.length :
                                        'no approvers array',
                                    approvers: response.approvers
                                });

                                if (response.success && response.approvers && response.approvers.length > 0) {
                                    let html = '<div class="approval-flow preview-mode">';

                                    response.approvers.forEach((approver, index) => {
                                        console.log('Processing approver:', approver);
                                        html += `
                                            <div class="approval-step preview-step">
                                                <div class="step-number">${approver.order || index + 1}</div>
                                                <div class="step-content">
                                                    <div class="approver-name">${approver.name}</div>
                                                    <div class="approver-department">${approver.department}</div>
                                                    <div class="step-label">Step ${approver.order || index + 1}</div>
                                                </div>
                                            </div>
                                        `;
                                    });

                                    html += '</div>';
                                    $('#approvalPreview').html(html);
                                } else {
                                    $('#approvalPreview').html(`
                                        <div class="text-center text-muted py-3">
                                            <i class="fas fa-info-circle"></i>
                                            <div class="mt-2">No approval flow configured for this combination</div>
                                            <small class="text-muted">
                                                @if ($projectName && $departmentName)
                                                    Project: {{ $projectName }}, Department: {{ $departmentName }}
                                                @else
                                                    Project ID: {{ $projectId }}, Department ID: {{ $departmentId }}
                                                @endif
                                                @if ($documentType === 'recruitment_request' && $requestReason)
                                                    , Request Reason: {{ formatRequestReason($requestReason) }}
                                                @endif
                                            </small>
                                        </div>
                                    `);
                                }
                            },
                            error: function(xhr, status, error) {
                                console.log('Approval preview error:', {
                                    xhr,
                                    status,
                                    error
                                });
                                $('#approvalPreview').html(`
                                    <div class="text-center text-danger py-3">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <div class="mt-2">Failed to load approval flow</div>
                                        <small class="text-muted">${error}</small>
                                        @if ($projectName && $departmentName)
                                            <br><small class="text-muted">Project: {{ $projectName }}, Department: {{ $departmentName }}</small>
                                        @endif
                                    </div>
                                `);
                            }
                        });
                    }

                    // Start loading when DOM is ready
                    if (document.readyState === 'loading') {
                        document.addEventListener('DOMContentLoaded', loadApprovalPreview);
                    } else {
                        loadApprovalPreview();
                    }
                </script>
            @endif
        @else
            {{-- Approval Status Mode --}}
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
        border-left-color: #007bff;
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

    /* Preview Mode Styling - Compact Design */
    .approval-flow.preview-mode {
        max-height: 300px;
        overflow-y: auto;
        padding: 0.25rem;
    }

    .approval-step.preview-step {
        display: flex;
        align-items: center;
        padding: 0.75rem;
        margin-bottom: 0.5rem;
        background: #f8f9fa;
        border-radius: 0.375rem;
        border: 1px solid #e9ecef;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        border-left: none;
        position: relative;
    }

    .step-number {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        background: #007bff;
        color: white;
        border-radius: 50%;
        font-weight: bold;
        font-size: 0.9rem;
        margin-right: 0.75rem;
        flex-shrink: 0;
        box-shadow: 0 1px 3px rgba(0, 123, 255, 0.3);
    }

    .step-content {
        flex-grow: 1;
        padding: 0.125rem 0;
    }

    .step-content .approver-name {
        font-weight: bold;
        font-size: 0.9rem;
        color: #333;
        margin-bottom: 0.125rem;
        line-height: 1.1;
    }

    .step-content .approver-department {
        font-size: 0.8rem;
        color: #6c757d;
        margin-bottom: 0.125rem;
        line-height: 1.1;
    }

    .step-content .step-label {
        font-size: 0.7rem;
        color: #6c757d;
        font-style: italic;
        line-height: 1.1;
    }

    /* Step 1: Blue */
    .approval-step.preview-step:nth-child(1) .step-number {
        background: #007bff;
        box-shadow: 0 2px 4px rgba(0, 123, 255, 0.3);
    }

    /* Step 2: Blue */
    .approval-step.preview-step:nth-child(2) .step-number {
        background: #007bff;
        box-shadow: 0 2px 4px rgba(0, 123, 255, 0.3);
    }

    /* Step 3: Blue (atau bisa diubah ke green jika diinginkan) */
    .approval-step.preview-step:nth-child(3) .step-number {
        background: #007bff;
        box-shadow: 0 2px 4px rgba(0, 123, 255, 0.3);
    }

    /* Hover effect untuk card */
    .approval-step.preview-step:hover {
        background: #e9ecef;
        transition: background-color 0.2s ease;
    }

    /* Responsive design - Compact */
    @media (max-width: 768px) {
        .approval-flow.preview-mode {
            padding: 0.125rem;
        }

        .approval-step.preview-step {
            padding: 0.5rem;
            margin-bottom: 0.375rem;
        }

        .step-number {
            width: 28px;
            height: 28px;
            font-size: 0.8rem;
            margin-right: 0.5rem;
        }

        .step-content .approver-name {
            font-size: 0.8rem;
        }

        .step-content .approver-department {
            font-size: 0.7rem;
        }

        .step-content .step-label {
            font-size: 0.65rem;
        }
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
