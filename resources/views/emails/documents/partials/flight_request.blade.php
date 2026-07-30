{{-- Flight Request — compact cozy --}}
@php
    $emailUi = $emailUi ?? require resource_path('views/emails/documents/partials/ui_tokens.php');
@endphp
@php
    /** @var \App\Models\FlightRequest $flightRequest */
    $flightRequest->loadNotificationRelations();
    $ctx = $flightRequest->notificationEmployeeContext();
    $orderedFlightDetails = $flightRequest->details->sortBy(['segment_order', 'flight_date'])->values();
    $lotFollowers = $flightRequest->request_type === \App\Models\FlightRequest::TYPE_TRAVEL_BASED
        && $flightRequest->officialTravel
        && $flightRequest->officialTravel->details->isNotEmpty();
    $standaloneFollowers = $flightRequest->request_type === \App\Models\FlightRequest::TYPE_STANDALONE
        && $flightRequest->followers->isNotEmpty();
@endphp

<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="{{ $emailUi['section'] }}">
    <tr>
        <td colspan="2" bgcolor="#f1ebe3" style="{{ $emailUi['head'] }}">Employee Information</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Form Number</td>
        <td style="{{ $emailUi['value'] }}">{{ $reference }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Requested At</td>
        <td style="{{ $emailUi['value'] }}">{{ format_date_with_weekday($flightRequest->requested_at ?? $flightRequest->created_at) }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Name</td>
        <td style="{{ $emailUi['value'] }}">{{ $ctx['name'] }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Request Type</td>
        <td style="{{ $emailUi['value'] }}">{{ $ctx['request_type_label'] }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">ID Number / NIK</td>
        <td style="{{ $emailUi['value'] }}">{{ $ctx['nik'] }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Position</td>
        <td style="{{ $emailUi['value'] }}">{{ $ctx['position'] }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Dept/Division</td>
        <td style="{{ $emailUi['value'] }}">{{ $ctx['department'] }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">POH</td>
        <td style="{{ $emailUi['value'] }}">{{ $ctx['poh'] }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">DOH</td>
        <td style="{{ $emailUi['value'] }}">{{ $ctx['doh'] }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Project Number</td>
        <td style="{{ $emailUi['value'] }}">{{ $ctx['project_number'] }}</td>
    </tr>
    @if (! empty($ctx['phone_number']))
        <tr>
            <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Phone Number</td>
            <td style="{{ $emailUi['value'] }}">{{ $ctx['phone_number'] }}</td>
        </tr>
    @endif
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Purpose of Travel</td>
        <td style="{{ $emailUi['value'] }}">{{ $flightRequest->purpose_of_travel ?: '—' }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['labelLast'] }}">Total Travel Days</td>
        <td style="{{ $emailUi['valueLast'] }}">{{ $flightRequest->total_travel_days ?? '—' }}</td>
    </tr>
</table>

@if ($lotFollowers)
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="{{ $emailUi['section'] }}">
        <tr>
            <td colspan="2" bgcolor="#f1ebe3" style="{{ $emailUi['head'] }}">Followers ({{ $flightRequest->officialTravel->details->count() }})</td>
        </tr>
        @foreach ($flightRequest->officialTravel->details as $index => $detail)
            @php $isLast = $index === $flightRequest->officialTravel->details->count() - 1; @endphp
            <tr>
                <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $isLast ? $emailUi['labelLast'] : $emailUi['label'] }}">{{ $detail->follower?->nik ?? '—' }}</td>
                <td style="{{ $isLast ? $emailUi['valueLast'] : $emailUi['value'] }}">
                    <strong>{{ $detail->follower?->employee?->fullname ?? 'Unknown Employee' }}</strong>
                    <br><span style="{{ $emailUi['meta'] }}">{{ $detail->follower?->position?->position_name ?? 'No Position' }} · {{ $detail->follower?->position?->department?->department_name ?? 'No Department' }}</span>
                </td>
            </tr>
        @endforeach
    </table>
@elseif ($standaloneFollowers)
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="{{ $emailUi['section'] }}">
        <tr>
            <td colspan="2" bgcolor="#f1ebe3" style="{{ $emailUi['head'] }}">Followers ({{ $flightRequest->followers->count() }})</td>
        </tr>
        @foreach ($flightRequest->followers as $index => $follower)
            @php
                $isLast = $index === $flightRequest->followers->count() - 1;
                $isEmployeeFollower = ! $follower->isManual();
                $followerPosition = $follower->position ?? ($follower->administration?->position?->position_name ?? null);
                $followerDepartment = $follower->department ?? ($follower->administration?->position?->department?->department_name ?? null);
            @endphp
            <tr>
                <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $isLast ? $emailUi['labelLast'] : $emailUi['label'] }}">{{ $follower->idLabel() }}: {{ $follower->nik ?? 'N/A' }}</td>
                <td style="{{ $isLast ? $emailUi['valueLast'] : $emailUi['value'] }}">
                    <strong>{{ $follower->displayName() }}</strong>
                    @if ($isEmployeeFollower)
                        <br><span style="{{ $emailUi['meta'] }}">{{ $followerPosition ?? 'No Position' }} · {{ $followerDepartment ?? 'No Department' }}</span>
                    @endif
                </td>
            </tr>
        @endforeach
    </table>
@endif

<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="{{ $emailUi['section'] }}">
    <tr>
        <td colspan="2" bgcolor="#f1ebe3" style="{{ $emailUi['head'] }}">Flight Details</td>
    </tr>
    @forelse ($orderedFlightDetails as $index => $detail)
        @php
            $isLast = $index === $orderedFlightDetails->count() - 1;
            $time = $detail->flight_time ? \Carbon\Carbon::parse($detail->flight_time)->format('H:i') : null;
        @endphp
        <tr>
            <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $isLast ? $emailUi['labelLast'] : $emailUi['label'] }}">Flight {{ $index + 1 }}</td>
            <td style="{{ $isLast ? $emailUi['valueLast'] : $emailUi['value'] }}">
                {{ $detail->departure_city ?? '—' }} → {{ $detail->arrival_city ?? '—' }}
                <br>
                <span style="{{ $emailUi['meta'] }}">
                    {{ optional($detail->flight_date)->format('d F Y') ?: '—' }}
                    @if ($time) · {{ $time }} @endif
                    @if ($detail->airline) · {{ $detail->airline }} @endif
                </span>
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="2" style="{{ $emailUi['muted'] }}">No flight details available</td>
        </tr>
    @endforelse
</table>

@if (filled($flightRequest->notes))
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="{{ $emailUi['section'] }}">
        <tr>
            <td bgcolor="#f1ebe3" style="{{ $emailUi['head'] }}">Notes</td>
        </tr>
        <tr>
            <td style="{{ $emailUi['single'] }} white-space: pre-wrap;">{{ $flightRequest->notes }}</td>
        </tr>
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
                <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['labelLast'] }}">Remarks</td>
                <td style="{{ $emailUi['valueLast'] }}">{{ $remarks }}</td>
            </tr>
        @endif
    </table>
@endif
