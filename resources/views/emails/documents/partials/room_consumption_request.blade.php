{{-- Room & Consumption Request — compact cozy --}}
@php
    $emailUi = $emailUi ?? require resource_path('views/emails/documents/partials/ui_tokens.php');
@endphp
@php
    /** @var \App\Models\RoomConsumptionRequest $roomConsumptionRequest */
    $roomConsumptionRequest->loadNotificationRelations();
    $facilities = $roomConsumptionRequest->notificationFacilities();
    $itemsByType = $roomConsumptionRequest->items->keyBy('consumption_type');
    $hasConsumption = $roomConsumptionRequest->items->where('is_selected', true)->isNotEmpty();
    $startTime = $roomConsumptionRequest->start_time
        ? \Carbon\Carbon::parse($roomConsumptionRequest->start_time)->format('H:i')
        : '—';
    $endTime = $roomConsumptionRequest->end_time
        ? \Carbon\Carbon::parse($roomConsumptionRequest->end_time)->format('H:i')
        : '—';
@endphp

<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="{{ $emailUi['section'] }}">
    <tr>
        <td colspan="2" bgcolor="#f1ebe3" style="{{ $emailUi['head'] }}">Room &amp; Consumption Information</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Reg. No</td>
        <td style="{{ $emailUi['value'] }}">{{ $roomConsumptionRequest->request_number ?: $reference }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Room</td>
        <td style="{{ $emailUi['value'] }}">{{ $roomConsumptionRequest->meetingRoom?->room_name ?: '—' }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Location</td>
        <td style="{{ $emailUi['value'] }}">
            {{ $roomConsumptionRequest->project?->project_code ?: '—' }}
            @if ($roomConsumptionRequest->project?->project_name)
                — {{ $roomConsumptionRequest->project->project_name }}
            @endif
        </td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Department</td>
        <td style="{{ $emailUi['value'] }}">{{ $roomConsumptionRequest->department?->department_name ?: '—' }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Meeting</td>
        <td style="{{ $emailUi['value'] }}">
            <strong>{{ $roomConsumptionRequest->meeting_title ?: '—' }}</strong>
            <br>
            <span style="{{ $emailUi['meta'] }}">
                {{ $roomConsumptionRequest->formattedMeetingDateRange() }}
                · {{ $startTime }} – {{ $endTime }}
            </span>
        </td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Attendees</td>
        <td style="{{ $emailUi['value'] }}">{{ $roomConsumptionRequest->attendees_count ?? '—' }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Need Zoom</td>
        <td style="{{ $emailUi['value'] }}">{{ $roomConsumptionRequest->need_zoom ? 'Yes' : 'No' }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['labelLast'] }}">Requester</td>
        <td style="{{ $emailUi['valueLast'] }}">{{ $roomConsumptionRequest->requestedBy?->name ?: '—' }}</td>
    </tr>
</table>

@if ($facilities->isNotEmpty())
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="{{ $emailUi['section'] }}">
        <tr>
            <td bgcolor="#f1ebe3" style="{{ $emailUi['head'] }}">Facilities</td>
        </tr>
        <tr>
            <td style="{{ $emailUi['single'] }}">{{ $facilities->implode(', ') }}</td>
        </tr>
    </table>
@endif

@if (filled($roomConsumptionRequest->notes))
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="{{ $emailUi['section'] }}">
        <tr>
            <td bgcolor="#f1ebe3" style="{{ $emailUi['head'] }}">Notes</td>
        </tr>
        <tr>
            <td style="{{ $emailUi['single'] }} white-space: pre-wrap;">{{ $roomConsumptionRequest->notes }}</td>
        </tr>
    </table>
@endif

@if ($roomConsumptionRequest->items->isNotEmpty())
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="{{ $emailUi['section'] }}">
        <tr>
            <td colspan="2" bgcolor="#f1ebe3" style="{{ $emailUi['head'] }}">
                Consumption
                <span style="font-weight: normal;">— {{ $hasConsumption ? $roomConsumptionRequest->items->where('is_selected', true)->count().' selected' : 'None selected' }}</span>
            </td>
        </tr>
        @foreach (\App\Models\RoomConsumptionRequest::CONSUMPTION_TYPES as $type => $label)
            @php
                $item = $itemsByType->get($type);
                $selected = (bool) ($item?->is_selected);
                $isLast = $loop->last;
            @endphp
            <tr>
                <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $isLast ? $emailUi['labelLast'] : $emailUi['label'] }}; color: {{ $selected ? '#6a645c' : '#a39d95' }};">
                    {{ $selected ? '✓' : '○' }} {{ $label }}
                </td>
                <td style="{{ $isLast ? $emailUi['valueLast'] : $emailUi['value'] }}; color: {{ $selected ? '#2f2a26' : '#a39d95' }};">
                    {{ $selected ? ($item->description ?: '—') : '—' }}
                </td>
            </tr>
        @endforeach
    </table>
@endif

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
