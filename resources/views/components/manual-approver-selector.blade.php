@props([
    'selectedApprovers' => [],
    'required' => false,
    'multiple' => true,
    // 'helpText' => 'Pilih user dengan role approver untuk menyetujui dokumen ini',
    'documentType' => null, // 'recruitment_request', 'leave_request', 'officialtravel', 'flight_request'
    'mode' => 'edit', // 'edit' or 'view' - view mode is read-only, only displays selected approvers
    'documentId' => null, // Document ID to fetch approval plans in view mode
])

@php
    // Get all users with approver role
    $approvers = \App\Models\User::whereHas('roles', function ($query) {
        $query->where('name', 'approver');
    })
        ->orderBy('name')
        ->get();

    // Get approval plans if in view mode and documentId is provided
    $approvalPlans = collect();
    if ($mode === 'view' && $documentId && $documentType) {
        $approvalPlans = \App\Models\ApprovalPlan::where('document_id', $documentId)
            ->where('document_type', $documentType)
            ->with('approver')
            ->orderBy('approval_order', 'asc')
            ->get()
            ->keyBy('approver_id');
    }

    // Get selected approvers data with approval status
    $selectedApproversData = collect($selectedApprovers)
        ->map(function ($id) use ($approvers, $approvalPlans) {
            $approver = $approvers->find($id);
            if (!$approver) {
                return null;
            }

            // Get approval plan for this approver
            $approvalPlan = $approvalPlans->get($id);

            return (object) [
                'id' => $approver->id,
                'name' => $approver->name,
                'email' => $approver->email,
                'status' => $approvalPlan ? $approvalPlan->status : null,
                'remarks' => $approvalPlan ? $approvalPlan->remarks : null,
                'approval_order' => $approvalPlan ? $approvalPlan->approval_order : null,
            ];
        })
        ->filter();

    // Prepare approvers data for JavaScript
    $approversData = $approvers
        ->map(function ($approver) {
            return [
                'id' => $approver->id,
                'name' => $approver->name,
                'email' => $approver->email,
            ];
        })
        ->values();

    $componentId = 'approver-selector-' . uniqid();
@endphp

@php
    $isViewMode = $mode === 'view';

    // Status mapping
    $statusMap = [
        0 => ['label' => 'Pending', 'class' => 'badge-warning', 'icon' => 'fa-clock'],
        1 => ['label' => 'Approved', 'class' => 'badge-success', 'icon' => 'fa-check-circle'],
        2 => ['label' => 'Rejected', 'class' => 'badge-danger', 'icon' => 'fa-times-circle'],
        3 => ['label' => 'Cancelled', 'class' => 'badge-secondary', 'icon' => 'fa-ban'],
        4 => ['label' => 'Revised', 'class' => 'badge-info', 'icon' => 'fa-edit'],
    ];
@endphp

<div id="{{ $componentId }}" class="approver-selector-component {{ $isViewMode ? 'view-mode' : '' }}">
    <div class="form-group">
        @if (!$isViewMode)
            <!-- Search Input (Hidden in view mode) -->
            <div class="input-group mb-2">
                <div class="input-group-prepend">
                    <span class="input-group-text"><i class="fas fa-search"></i></span>
                </div>
                <input type="text" id="approver-search-{{ $componentId }}" name="approver_search_{{ $componentId }}"
                    class="form-control approver-search-input"
                    placeholder="Ketik nama atau email approver untuk mencari..." autocomplete="off"
                    aria-label="Search approver">
            </div>

            <!-- Approver List (Hidden by default, shown when typing) -->
            <div class="approver-list-container" style="display: none;">
                <div class="approver-list" id="approverList_{{ $componentId }}">
                    <!-- Approver buttons will be inserted here -->
                </div>
            </div>
        @endif

        <!-- Selected Approvers -->
        <div class="selected-approvers-container {{ $isViewMode ? 'mt-0' : 'mt-3' }}">
            <div class="selected-approvers-list" id="selectedApproversList_{{ $componentId }}">
                @foreach ($selectedApproversData as $index => $approver)
                    @php
                        $order = $approver->approval_order ?? $index + 1;
                        $status = $approver->status;
                        $statusInfo = $status !== null ? $statusMap[$status] ?? null : null;
                    @endphp
                    <div class="selected-approver-badge {{ $isViewMode ? 'view-mode-badge' : '' }}"
                        data-approver-id="{{ $approver->id }}">
                        <span class="approver-order">{{ $order }}</span>
                        <div class="approver-info">
                            <div class="approver-header">
                                <span class="approver-name">{{ $approver->name }}</span>
                                @if ($isViewMode && $statusInfo)
                                    <span class="approval-status-badge badge {{ $statusInfo['class'] }}">
                                        <i class="fas {{ $statusInfo['icon'] }}"></i> {{ $statusInfo['label'] }}
                                    </span>
                                @endif
                            </div>
                            <span class="approver-email">{{ $approver->email }}</span>
                            @if ($isViewMode && $approver->remarks)
                                <div class="approver-remarks">
                                    <i class="fas fa-comment-alt"></i> {{ $approver->remarks }}
                                </div>
                            @endif
                        </div>
                        @if (!$isViewMode)
                            <button type="button" class="btn-remove-approver" data-approver-id="{{ $approver->id }}">
                                <i class="fas fa-times"></i>
                            </button>
                            <input type="hidden" name="manual_approvers[]" value="{{ $approver->id }}">
                        @endif
                    </div>
                @endforeach
            </div>
            @if ($selectedApproversData->isEmpty())
                <div class="text-muted text-center py-2">
                    <small><i class="fas fa-info-circle"></i>
                        {{ $isViewMode ? 'Tidak ada approver yang dipilih' : 'Belum ada approver yang dipilih' }}</small>
                </div>
                @if (!$isViewMode)
                    {{-- Ensure field exists for validation even when empty (disabled so it won't be sent) --}}
                    <input type="hidden" name="manual_approvers[]" value="" disabled>
                @endif
            @endif
        </div>

        {{-- @if ($helpText)
            <small class="form-text text-muted mt-2">{{ $helpText }}</small>
        @endif --}}

        <!-- Approval Rules Information (Hidden in view mode) -->
        @if ($documentType && !$isViewMode)
            <div class="approval-rules-info mt-3">
                <button type="button" class="btn btn-sm btn-outline-info w-100 text-left approval-rules-toggle"
                    data-toggle="collapse" data-target="#approvalRules_{{ $componentId }}" aria-expanded="false"
                    aria-controls="approvalRules_{{ $componentId }}">
                    <i class="fas fa-info-circle mr-2"></i>
                    <strong>Informasi Rule Approval</strong>
                    <i class="fas fa-chevron-down float-right mt-1 approval-rules-icon"></i>
                </button>
                <div class="collapse mt-2" id="approvalRules_{{ $componentId }}">
                    <div class="card card-body bg-light">
                        @if ($documentType === 'recruitment_request')
                            <h6 class="text-primary mb-2">
                                <i class="fas fa-user-tie mr-1"></i> Recruitment Request (FPTK)
                            </h6>
                            <ul class="mb-0 pl-3">
                                <li>Approval flow ditentukan berdasarkan kombinasi <strong>Project</strong>,
                                    <strong>Department</strong>, dan <strong>Request Reason</strong>
                                </li>
                                <li><strong>Request Reason: Additional</strong>
                                    <ul class="mt-1">
                                        <li><strong>Project HO, BO, atau APS:</strong>
                                            <ol class="mt-1">
                                                <li>Division Manager masing-masing department</li>
                                                <li>HCS Division Manager</li>
                                                <li>HCL Director</li>
                                            </ol>
                                        </li>
                                        <li><strong>Site Project:</strong>
                                            <ol class="mt-1">
                                                <li>Project Manager</li>
                                                <li>Operation General Manager</li>
                                                <li>HCS Division Manager</li>
                                            </ol>
                                        </li>
                                    </ul>
                                </li>
                                <li><strong>Request Reason: Replacement</strong>
                                    <ul class="mt-1">
                                        <li><strong>Project HO, BO, atau APS:</strong>
                                            <ol class="mt-1">
                                                <li>Division Manager masing-masing department</li>
                                                <li>HCS Division Manager</li>
                                            </ol>
                                        </li>
                                        <li><strong>Site Project:</strong>
                                            <ol class="mt-1">
                                                <li>Project Manager</li>
                                                <li>HCS Division Manager</li>
                                            </ol>
                                        </li>
                                    </ul>
                                </li>
                                <li>Approval dilakukan secara <strong>sequential</strong> sesuai urutan yang dipilih
                                </li>
                                <li>Approver yang dipilih harus memiliki role <strong>approver</strong></li>
                                <li class="mt-2"><strong><i class="fas fa-exclamation-triangle text-warning"></i>
                                        Catatan:</strong> Jika approver yang diperlukan tidak tersedia dalam Approver
                                    Selection, harap menghubungi <strong>HR HO Balikpapan</strong></li>
                            </ul>
                        @elseif($documentType === 'leave_request')
                            <h6 class="text-primary mb-2">
                                <i class="fas fa-calendar-alt mr-1"></i> Leave Request
                            </h6>
                            <ul class="mb-0 pl-3">
                                <li>Approval flow ditentukan berdasarkan kombinasi <strong>Project</strong>,
                                    <strong>Department</strong>, dan <strong>Level</strong> karyawan
                                </li>
                                <li>Level karyawan diambil dari administration record yang aktif</li>
                                <li>Approval dilakukan secara <strong>sequential</strong> sesuai urutan yang dipilih
                                </li>
                                <li>Approver yang dipilih harus memiliki role <strong>approver</strong></li>
                                <li>Setiap approver akan menerima notifikasi untuk approve/reject request</li>
                                <li class="mt-2"><strong><i class="fas fa-exclamation-triangle text-warning"></i>
                                        Catatan:</strong> Jika approver yang diperlukan tidak tersedia dalam Approver
                                    Selection, harap menghubungi <strong>HR HO Balikpapan</strong></li>
                            </ul>
                        @elseif($documentType === 'officialtravel')
                            <h6 class="text-primary mb-2">
                                <i class="fas fa-plane-departure mr-1"></i> Official Travel (LOT)
                            </h6>
                            <ul class="mb-0 pl-3">
                                <li>Approval flow ditentukan berdasarkan kombinasi <strong>Project Origin</strong> dan
                                    <strong>Department</strong> traveler
                                </li>
                                <li>Department diambil dari posisi traveler yang aktif</li>
                                <li>Project diambil dari <strong>Official Travel Origin</strong></li>
                                <li>Approval dilakukan secara <strong>sequential</strong> sesuai urutan yang dipilih
                                </li>
                                <li>Approver yang dipilih harus memiliki role <strong>approver</strong></li>
                                <li>Setiap approver akan menerima notifikasi untuk approve/reject perjalanan dinas</li>
                                <li class="mt-2"><strong><i class="fas fa-exclamation-triangle text-warning"></i>
                                        Catatan:</strong> Jika approver yang diperlukan tidak tersedia dalam Approver
                                    Selection, harap menghubungi <strong>HR HO Balikpapan</strong></li>
                            </ul>
                        @elseif($documentType === 'flight_request')
                            <h6 class="text-primary mb-2">
                                <i class="fas fa-plane mr-1"></i> Flight Request (FRF)
                            </h6>
                            <ul class="mb-0 pl-3">
                                <li>Untuk <strong>Site</strong>: Approval pertama dilakukan oleh <strong>HR</strong>,
                                    Approval kedua dilakukan oleh <strong>PJO</strong></li>
                                <li>Untuk <strong>HO</strong>: Approval pertama dilakukan oleh <strong>Department
                                        Head/Manager Divisi</strong>, Approval kedua dilakukan oleh <strong>HCS
                                        Manager/Direktur</strong></li>
                                <li>Approval dilakukan secara <strong>sequential</strong> sesuai urutan yang dipilih
                                </li>
                                <li>Approver yang dipilih harus memiliki role <strong>approver</strong></li>
                                <li>Setiap approver akan menerima notifikasi untuk approve/reject flight request</li>
                                <li class="mt-2"><strong><i class="fas fa-exclamation-triangle text-warning"></i>
                                        Catatan:</strong> Jika approver yang diperlukan tidak tersedia dalam Approver
                                    Selection, harap menghubungi <strong>HR HO Balikpapan</strong></li>
                            </ul>
                        @elseif($documentType === 'flight_request_issuance')
                            <h6 class="text-primary mb-2">
                                <i class="fas fa-file-invoice mr-1"></i> Flight Request Issuance (LG)
                            </h6>
                            <ul class="mb-0 pl-3">
                                <li>Approval untuk Letter of Guarantee (LG) dilakukan oleh <strong>HCS Division Manager</strong></li>
                                <li>Approval dilakukan secara <strong>sequential</strong> sesuai urutan yang dipilih</li>
                                <li>Approver yang dipilih harus memiliki role <strong>approver</strong></li>
                                <li>Setiap approver akan menerima notifikasi untuk approve/reject issuance</li>
                                <li class="mt-2"><strong><i class="fas fa-exclamation-triangle text-warning"></i>
                                        Catatan:</strong> Jika approver yang diperlukan tidak tersedia dalam Approver
                                    Selection, harap menghubungi <strong>HR HO Balikpapan</strong></li>
                            </ul>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        @if (!$isViewMode)
            @error('manual_approvers')
                <div class="text-danger mt-1"><small>{{ $message }}</small></div>
            @enderror

            @if ($required)
                <input type="hidden" name="manual_approvers_required" value="" required>
            @endif
        @endif
    </div>
</div>

<style>
    .approver-selector-component {
        width: 100%;
    }

    .approver-selector-component .form-group {
        width: 100%;
    }

    .approver-selector-component .form-label {
        font-weight: 600;
        margin-bottom: 0.5rem;
    }

    .approver-selector-component .input-group {
        width: 100%;
        display: flex;
        align-items: stretch;
    }

    .approver-selector-component .input-group-prepend {
        display: flex;
    }

    .approver-selector-component .input-group-text {
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .approver-selector-component .form-control {
        flex: 1;
        width: auto;
    }

    .approver-list-container {
        width: 100%;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        background: #fff;
        max-height: 300px;
        overflow-y: auto;
        margin-bottom: 0.5rem;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    .approver-list {
        width: 100%;
        padding: 0.5rem;
    }

    .approver-item-btn {
        display: block;
        width: 100%;
        text-align: left;
        padding: 0.75rem 1rem;
        margin-bottom: 0.5rem;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        background: #fff;
        cursor: pointer;
        transition: all 0.2s ease;
    }

    .approver-item-btn:hover {
        background: #f8f9fa;
        border-color: #007bff;
        transform: translateX(4px);
    }

    .approver-item-btn:last-child {
        margin-bottom: 0;
    }

    .approver-item-name {
        font-weight: 600;
        color: #333;
        display: block;
        margin-bottom: 0.25rem;
    }

    .approver-item-email {
        font-size: 0.875rem;
        color: #6c757d;
        display: block;
    }

    .selected-approvers-container {
        width: 100%;
        min-height: 50px;
        border: 1px solid #dee2e6;
        border-radius: 0.375rem;
        background: #f8f9fa;
        padding: 0.75rem;
    }

    .selected-approvers-list {
        width: 100%;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .selected-approver-badge {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 0.5rem;
        width: 100%;
        padding: 0.5rem 0.75rem;
        background: #007bff;
        color: #fff;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        animation: slideIn 0.3s ease-out;
        position: relative;
    }

    .selected-approver-badge .approver-info {
        display: flex;
        flex-direction: column;
        gap: 0.125rem;
        flex: 1;
        margin-left: 0.5rem;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(-10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .approver-order {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 24px;
        height: 24px;
        background: rgba(255, 255, 255, 0.3);
        border-radius: 50%;
        font-weight: bold;
        font-size: 0.75rem;
    }

    .selected-approver-badge .approver-name {
        font-weight: 600;
        display: block;
    }

    .selected-approver-badge .approver-email {
        font-size: 0.8rem;
        opacity: 0.9;
        display: block;
    }

    .btn-remove-approver {
        background: transparent;
        border: none;
        color: #fff;
        cursor: pointer;
        padding: 0;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: all 0.2s ease;
        margin-left: auto;
        flex-shrink: 0;
    }

    .btn-remove-approver:hover {
        background: rgba(255, 255, 255, 0.3);
        transform: scale(1.1);
    }

    .no-results {
        padding: 1rem;
        text-align: center;
        color: #6c757d;
        font-size: 0.875rem;
    }

    .approval-rules-info {
        width: 100%;
    }

    .approval-rules-toggle {
        border: 1px solid #17a2b8;
        transition: all 0.3s ease;
    }

    .approval-rules-toggle:hover {
        background-color: #17a2b8;
        color: #fff;
    }

    .approval-rules-toggle[aria-expanded="true"] .approval-rules-icon {
        transform: rotate(180deg);
    }

    .approval-rules-icon {
        transition: transform 0.3s ease;
    }

    .approval-rules-info .card {
        border: 1px solid #dee2e6;
        font-size: 0.875rem;
    }

    .approval-rules-info ul {
        margin-bottom: 0.5rem;
    }

    .approval-rules-info ul li {
        margin-bottom: 0.5rem;
        line-height: 1.6;
    }

    .approval-rules-info ul ul {
        margin-top: 0.5rem;
        margin-bottom: 0.5rem;
    }

    /* View mode styles */
    .approver-selector-component.view-mode .selected-approvers-container {
        border: none;
        background: transparent;
        padding: 0;
    }

    .approver-selector-component.view-mode .selected-approver-badge.view-mode-badge {
        background: #e9ecef;
        color: #495057;
        border: 1px solid #dee2e6;
    }

    .approver-selector-component.view-mode .selected-approver-badge.view-mode-badge .approver-order {
        background: #6c757d;
        color: #fff;
    }

    .approver-selector-component.view-mode .selected-approver-badge.view-mode-badge .approver-name {
        color: #212529;
    }

    .approver-selector-component.view-mode .selected-approver-badge.view-mode-badge .approver-email {
        color: #6c757d;
    }

    /* Approval status and remarks in view mode */
    .approver-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.5rem;
        margin-bottom: 0.25rem;
        flex-wrap: wrap;
    }

    .approval-status-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        font-weight: 600;
        white-space: nowrap;
    }

    .approver-remarks {
        margin-top: 0.5rem;
        padding: 0.5rem;
        background: rgba(255, 255, 255, 0.5);
        border-radius: 0.25rem;
        font-size: 0.875rem;
        color: #495057;
        border-left: 3px solid #6c757d;
    }

    .approver-remarks i {
        margin-right: 0.25rem;
        color: #6c757d;
    }

    .approver-remarks strong {
        color: #212529;
    }
</style>

<script>
    // Wait for jQuery to be available
    (function() {
        function initWhenReady() {
            if (typeof jQuery === 'undefined' || typeof $ === 'undefined') {
                setTimeout(initWhenReady, 100);
                return;
            }

            $(document).ready(function() {
                const componentId = '{{ $componentId }}';
                const $component = $('#' + componentId);
                const isViewMode = @json($isViewMode);
                const $searchInput = $component.find('.approver-search-input');
                const $approverListContainer = $component.find('.approver-list-container');
                const $approverList = $component.find('#approverList_' + componentId);
                const $selectedList = $component.find('#selectedApproversList_' + componentId);
                const isRequired = @json($required);

                // Skip all interactive functionality in view mode
                if (isViewMode) {
                    return;
                }

                // Approvers data
                const approvers = @json($approversData);

                // Selected approver IDs
                let selectedIds = @json($selectedApproversData->pluck('id')->toArray());

                // Search functionality
                $searchInput.on('input', function() {
                    const searchTerm = $(this).val().toLowerCase().trim();

                    if (searchTerm.length < 2) {
                        $approverListContainer.hide();
                        return;
                    }

                    // Filter approvers
                    const filtered = approvers.filter(function(approver) {
                        // Skip already selected (compare as strings to handle type differences)
                        if (selectedIds.some(id => String(id) === String(approver.id))) {
                            return false;
                        }
                        return approver.name.toLowerCase().includes(searchTerm) ||
                            approver.email.toLowerCase().includes(searchTerm);
                    });

                    // Render approver buttons
                    if (filtered.length > 0) {
                        $approverList.empty();
                        filtered.forEach(function(approver) {
                            const $btn = $('<button>')
                                .attr('type', 'button')
                                .addClass('approver-item-btn')
                                .data('approver-id', approver.id)
                                .html(
                                    '<span class="approver-item-name">' + approver.name +
                                    '</span>' +
                                    '<span class="approver-item-email">' + approver.email +
                                    '</span>'
                                );
                            $approverList.append($btn);
                        });
                        $approverListContainer.show();
                    } else {
                        $approverList.html(
                            '<div class="no-results"><i class="fas fa-info-circle"></i> Tidak ada approver yang ditemukan</div>'
                        );
                        $approverListContainer.show();
                    }
                });

                // Click approver button to select
                $approverList.on('click', '.approver-item-btn', function() {
                    const approverId = $(this).data('approver-id');
                    const approver = approvers.find(a => String(a.id) === String(approverId));

                    if (!approver || selectedIds.some(id => String(id) === String(approverId))) {
                        return;
                    }

                    // Add to selected
                    selectedIds.push(approverId);
                    const order = selectedIds.length;

                    // Create badge
                    const $badge = $('<div>')
                        .addClass('selected-approver-badge')
                        .attr('data-approver-id', approverId)
                        .html(
                            '<span class="approver-order">' + order + '</span>' +
                            '<div class="approver-info">' +
                            '<span class="approver-name">' + approver.name + '</span>' +
                            '<span class="approver-email">' + approver.email + '</span>' +
                            '</div>' +
                            '<button type="button" class="btn-remove-approver" data-approver-id="' +
                            approverId + '">' +
                            '<i class="fas fa-times"></i></button>' +
                            '<input type="hidden" name="manual_approvers[]" value="' + approverId +
                            '">'
                        );

                    // Hide empty message if exists
                    $component.find('.selected-approvers-container .text-muted').hide();

                    // Remove disabled empty input if exists
                    $component.find('input[name="manual_approvers[]"][disabled]').remove();

                    $selectedList.append($badge);
                    updateOrderNumbers();

                    // Remove from search results
                    $(this).fadeOut(200, function() {
                        $(this).remove();
                        // Hide container if empty
                        if ($approverList.find('.approver-item-btn').length === 0) {
                            $approverListContainer.hide();
                        }
                    });

                    // Clear search
                    $searchInput.val('');

                    // Remove required validation
                    $component.find('input[name="manual_approvers_required"]').remove();

                    // Ensure all hidden inputs are enabled (not disabled)
                    $component.find('input[name="manual_approvers[]"]').prop('disabled', false);

                    // Debug: Log current hidden inputs
                    const currentInputs = $component.find('input[name="manual_approvers[]"]').map(
                        function() {
                            return {
                                value: $(this).val(),
                                disabled: $(this).prop('disabled')
                            };
                        }).get();
                    console.log('Approver selected. Current hidden inputs:', currentInputs);
                });

                // Remove approver
                $selectedList.on('click', '.btn-remove-approver', function(e) {
                    e.stopPropagation();
                    const approverId = $(this).data('approver-id');
                    const $badge = $(this).closest('.selected-approver-badge');

                    // Remove from selected IDs
                    selectedIds = selectedIds.filter(id => String(id) !== String(approverId));

                    // Remove badge
                    $badge.fadeOut(200, function() {
                        $(this).remove();
                        updateOrderNumbers();

                        // Show empty message if no selected
                        if ($selectedList.find('.selected-approver-badge').length === 0) {
                            $component.find('.selected-approvers-container .text-muted')
                                .show();

                            // Add a disabled hidden input to ensure field exists for validation
                            // This helps Laravel validation detect the field
                            $component.find('input[name="manual_approvers[]"][disabled]')
                                .remove();
                            $component.find('.selected-approvers-list').append(
                                '<input type="hidden" name="manual_approvers[]" value="" disabled>'
                            );
                        }

                        // Add required validation back if needed
                        if (selectedIds.length === 0 && isRequired) {
                            $component.find('input[name="manual_approvers_required"]')
                                .remove();
                            $component.find('.form-group').append(
                                '<input type="hidden" name="manual_approvers_required" value="" required>'
                            );
                        }
                    });
                });

                // Update order numbers
                function updateOrderNumbers() {
                    $selectedList.find('.selected-approver-badge').each(function(index) {
                        $(this).find('.approver-order').text(index + 1);
                    });
                }

                // Ensure all hidden inputs are enabled before form submission
                $component.closest('form').on('submit', function(e) {
                    // Remove all disabled hidden inputs
                    $component.find('input[name="manual_approvers[]"][disabled]').remove();

                    // Ensure all remaining hidden inputs are enabled
                    $component.find('input[name="manual_approvers[]"]').prop('disabled', false);

                    // Debug: Log before submit
                    const inputsBeforeSubmit = $component.find('input[name="manual_approvers[]"]')
                        .map(function() {
                            return {
                                value: $(this).val(),
                                disabled: $(this).prop('disabled'),
                                name: $(this).attr('name')
                            };
                        }).get();
                    console.log('Before form submit - hidden inputs:', inputsBeforeSubmit);
                });

                // Hide approver list when clicking outside
                $(document).on('click.approverSelector_' + componentId, function(e) {
                    const target = $(e.target);
                    // Don't hide if clicking inside component
                    if (!$component.is(target) &&
                        !$component.has(target).length &&
                        !target.closest('.approver-list-container').length &&
                        !target.closest('.approver-search-input').length) {
                        $approverListContainer.hide();
                    }
                });

                // Handle approval rules toggle icon rotation
                $component.find('.approval-rules-toggle').on('click', function() {
                    const $icon = $(this).find('.approval-rules-icon');
                    const isExpanded = $(this).attr('aria-expanded') === 'true';
                    // Icon rotation is handled by CSS, but we can add additional logic here if needed
                });
            });
        }

        initWhenReady();
    })();
</script>
