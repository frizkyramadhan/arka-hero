@props([
    'departmentId' => null,
    'departmentName' => '',
    'selectedApprovers' => [],
    'required' => true,
    'multiple' => true,
    'documentType' => 'leave_request',
])

@php
    $componentId = 'bulk-approver-' . ($departmentId ?? uniqid());
    $safeDepartmentId = $departmentId ?? 'unknown-' . uniqid();
@endphp

<div class="bulk-approver-selector-card card card-info card-outline mb-3" data-department-id="{{ $safeDepartmentId }}" data-department-name="{{ $departmentName }}">
    <div class="card-header py-2">
        <h3 class="card-title">
            <i class="fas fa-sitemap mr-2"></i>
            <strong>{{ $departmentName }}</strong>
            <span class="badge badge-light ml-2" id="employee-count-{{ $componentId }}">0 employees</span>
        </h3>
    </div>
    <div class="card-body py-2">
        @include('components.manual-approver-selector', [
            'selectedApprovers' => $selectedApprovers,
            'required' => $required,
            'multiple' => $multiple,
            'helpText' => 'Pilih minimal 1 approver dengan role approver untuk department ini',
            'documentType' => $documentType,
        ])
        
        <!-- Hidden input untuk menyimpan manual approvers per department -->
        <input type="hidden" name="department_approvers[{{ $safeDepartmentId }}]" 
               id="department-approvers-{{ $safeDepartmentId }}" 
               value="{{ json_encode($selectedApprovers) }}">
    </div>
</div>

