{{-- Leave Request — compact cozy --}}
@php
    $emailUi = $emailUi ?? require resource_path('views/emails/documents/partials/ui_tokens.php');
@endphp
@php
    /** @var \App\Models\LeaveRequest $leaveRequest */
    $leaveRequest->loadNotificationRelations();
    $admin = $leaveRequest->notificationActiveAdministration();
    $entitlement = $leaveRequest->matchingEntitlement();
    $totalDays = (float) ($leaveRequest->total_days ?? 0);
    $remaining = $entitlement?->remaining_days;
    $leaveType = $leaveRequest->leaveType?->name ?? '—';
    if ($leaveRequest->leaveType?->code) {
        $leaveType .= ' ('.$leaveRequest->leaveType->code.')';
    }
@endphp

<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="{{ $emailUi['section'] }}">
    <tr>
        <td colspan="2" bgcolor="#f1ebe3" style="{{ $emailUi['head'] }}">Leave Request Information</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Register Number</td>
        <td style="{{ $emailUi['value'] }}">{{ $reference }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Employee</td>
        <td style="{{ $emailUi['value'] }}">{{ $leaveRequest->employee?->fullname ?? '—' }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Leave Type</td>
        <td style="{{ $emailUi['value'] }}">{{ $leaveType }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Start Date</td>
        <td style="{{ $emailUi['value'] }}">{{ $leaveRequest->start_date ? format_date_with_weekday($leaveRequest->start_date) : '—' }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">End Date</td>
        <td style="{{ $emailUi['value'] }}">{{ $leaveRequest->end_date ? format_date_with_weekday($leaveRequest->end_date) : '—' }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Total Days</td>
        <td style="{{ $emailUi['value'] }}">{{ $totalDays }} {{ $totalDays > 1 ? 'days' : 'day' }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Sisa Cuti</td>
        <td style="{{ $emailUi['value'] }}">
            @if ($remaining === null)
                N/A
            @else
                {{ $remaining }} {{ ((float) $remaining) > 1 ? 'days' : 'day' }}
            @endif
        </td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Back to Work Date</td>
        <td style="{{ $emailUi['value'] }}">{{ $leaveRequest->back_to_work_date ? format_date_with_weekday($leaveRequest->back_to_work_date) : 'N/A' }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Requested At</td>
        <td style="{{ $emailUi['value'] }}">{{ $leaveRequest->created_at ? format_datetime_with_weekday($leaveRequest->created_at) : 'N/A' }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ filled($leaveRequest->reason) ? $emailUi['label'] : $emailUi['labelLast'] }}">Leave Period</td>
        <td style="{{ filled($leaveRequest->reason) ? $emailUi['value'] : $emailUi['valueLast'] }}">{{ $leaveRequest->leave_period ?: '—' }}</td>
    </tr>
    @if (filled($leaveRequest->reason))
        <tr>
            <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['labelLast'] }}">Reason</td>
            <td style="{{ $emailUi['valueLast'] }} white-space: pre-wrap;">{{ $leaveRequest->reason }}</td>
        </tr>
    @endif
</table>

<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="{{ $emailUi['section'] }}">
    <tr>
        <td colspan="2" bgcolor="#f1ebe3" style="{{ $emailUi['head'] }}">Employee Information</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">NIK - Name</td>
        <td style="{{ $emailUi['value'] }}">{{ $admin?->nik ?? 'N/A' }} - {{ $leaveRequest->employee?->fullname ?? 'Unknown Employee' }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Position</td>
        <td style="{{ $emailUi['value'] }}">{{ $admin?->position?->position_name ?? 'No Position' }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Business Unit</td>
        <td style="{{ $emailUi['value'] }}">{{ $admin?->project?->project_code ?? 'No Code' }} : {{ $admin?->project?->project_name ?? 'No Project' }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['labelLast'] }}">Division / Department</td>
        <td style="{{ $emailUi['valueLast'] }}">{{ $admin?->position?->department?->department_name ?? 'No Department' }}</td>
    </tr>
</table>

@if (! empty($approvalOrder) || ! empty($remarks))
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="{{ $emailUi['section'] }}">
        @if (! empty($approvalOrder))
            <tr>
                <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ ! empty($remarks) ? $emailUi['label'] : $emailUi['labelLast'] }}">Approval Step</td>
                <td style="{{ ! empty($remarks) ? $emailUi['value'] : $emailUi['valueLast'] }}">#{{ $approvalOrder }}</td>
            </tr>
        @endif
        @if (! empty($remarks))
            <tr>
                <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['labelLast'] }}">Approval Remarks</td>
                <td style="{{ $emailUi['valueLast'] }}">{{ $remarks }}</td>
            </tr>
        @endif
    </table>
@endif
