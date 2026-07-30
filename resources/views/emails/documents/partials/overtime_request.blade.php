{{-- Overtime Request — compact cozy --}}
@php
    $emailUi = $emailUi ?? require resource_path('views/emails/documents/partials/ui_tokens.php');
@endphp
@php
    /** @var \App\Models\OvertimeRequest $overtimeRequest */
    $overtimeRequest->loadNotificationRelations();
    $projectLabel = $overtimeRequest->project
        ? trim(($overtimeRequest->project->project_code ? $overtimeRequest->project->project_code.' — ' : '').($overtimeRequest->project->project_name ?? ''))
        : '—';
    $details = $overtimeRequest->details ?? collect();
@endphp

<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="{{ $emailUi['section'] }}">
    <tr>
        <td colspan="2" bgcolor="#f1ebe3" style="{{ $emailUi['head'] }}">Overtime Information</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Register Number</td>
        <td style="{{ $emailUi['value'] }}">{{ $reference }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Project</td>
        <td style="{{ $emailUi['value'] }}">{{ $projectLabel !== '' ? $projectLabel : '—' }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Overtime Date</td>
        <td style="{{ $emailUi['value'] }}">{{ $overtimeRequest->overtime_date ? format_date_with_weekday($overtimeRequest->overtime_date) : '—' }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Created By</td>
        <td style="{{ $emailUi['value'] }}">{{ $overtimeRequest->requestedBy?->name ?: '—' }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Created At</td>
        <td style="{{ $emailUi['value'] }}">{{ $overtimeRequest->created_at ? format_datetime_with_weekday($overtimeRequest->created_at) : '—' }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['labelLast'] }}">Remarks</td>
        <td style="{{ $emailUi['valueLast'] }} white-space: pre-wrap;">{{ $overtimeRequest->remarks ?: '—' }}</td>
    </tr>
</table>

<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="{{ $emailUi['section'] }}">
    <tr>
        <td colspan="2" bgcolor="#f1ebe3" style="{{ $emailUi['head'] }}">Employee Details{{ $details->isNotEmpty() ? ' ('.$details->count().')' : '' }}</td>
    </tr>
    @forelse ($details as $index => $line)
        @php
            $isLast = $index === $details->count() - 1;
            $timeIn = $line->time_in ? \Carbon\Carbon::parse($line->time_in)->format('H:i') : '—';
            $timeOut = $line->time_out ? \Carbon\Carbon::parse($line->time_out)->format('H:i') : '—';
        @endphp
        <tr>
            <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $isLast ? $emailUi['labelLast'] : $emailUi['label'] }}">{{ $index + 1 }}. {{ $line->administration?->nik ?? '—' }}</td>
            <td style="{{ $isLast ? $emailUi['valueLast'] : $emailUi['value'] }}">
                <strong>{{ $line->administration?->employee?->fullname ?? '—' }}</strong>
                <br>
                <span style="{{ $emailUi['meta'] }}">
                    {{ $timeIn }} – {{ $timeOut }}
                    @if ($line->administration?->position?->position_name)
                        · {{ $line->administration->position->position_name }}
                    @endif
                </span>
                @if (filled($line->work_description))
                    <br><span style="{{ $emailUi['meta'] }}">{{ $line->work_description }}</span>
                @endif
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="2" style="{{ $emailUi['muted'] }}">No employee details</td>
        </tr>
    @endforelse
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
