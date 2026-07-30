{{-- Official Travel — compact cozy --}}
@php
    $emailUi = $emailUi ?? require resource_path('views/emails/documents/partials/ui_tokens.php');
@endphp
@php
    /** @var \App\Models\Officialtravel $officialtravel */
    $officialtravel->loadNotificationRelations();
    $traveler = $officialtravel->traveler;
    $followers = $officialtravel->details ?? collect();
@endphp

<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="{{ $emailUi['section'] }}">
    <tr>
        <td colspan="2" bgcolor="#f1ebe3" style="{{ $emailUi['head'] }}">Official Travel Details</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Document Number</td>
        <td style="{{ $emailUi['value'] }}">{{ $reference }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Travel Date</td>
        <td style="{{ $emailUi['value'] }}">{{ format_date_with_weekday($officialtravel->official_travel_date) }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Destination</td>
        <td style="{{ $emailUi['value'] }}">{{ $officialtravel->itinerarySummaryForDisplay() ?: '—' }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Purpose</td>
        <td style="{{ $emailUi['value'] }}">{{ $officialtravel->purpose ?: '—' }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Departure Date</td>
        <td style="{{ $emailUi['value'] }}">{{ format_date_with_weekday($officialtravel->departure_from) }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Transportation</td>
        <td style="{{ $emailUi['value'] }}">{{ $officialtravel->transportation->transportation_name ?? 'No Transportation' }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Accommodation</td>
        <td style="{{ $emailUi['value'] }}">{{ $officialtravel->accommodation->accommodation_name ?? 'No Accommodation' }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['labelLast'] }}">Duration</td>
        <td style="{{ $emailUi['valueLast'] }}">{{ filled($officialtravel->duration) ? $officialtravel->duration : '—' }}</td>
    </tr>
</table>

<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="{{ $emailUi['section'] }}">
    <tr>
        <td colspan="2" bgcolor="#f1ebe3" style="{{ $emailUi['head'] }}">Traveler</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">NIK - Name</td>
        <td style="{{ $emailUi['value'] }}">
            @if ($traveler)
                {{ $traveler->nik ?? '—' }} - {{ $traveler->employee?->fullname ?? 'Unknown Employee' }}
            @else
                —
            @endif
        </td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Title</td>
        <td style="{{ $emailUi['value'] }}">{{ $traveler?->position?->position_name ?? 'No Position' }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Business Unit</td>
        <td style="{{ $emailUi['value'] }}">{{ $traveler?->project?->project_code ?? 'No Code' }} : {{ $traveler?->project?->project_name ?? 'No Project' }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['labelLast'] }}">Division / Department</td>
        <td style="{{ $emailUi['valueLast'] }}">{{ $traveler?->position?->department?->department_name ?? 'No Department' }}</td>
    </tr>
</table>

@if ($followers->isNotEmpty())
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="{{ $emailUi['section'] }}">
        <tr>
            <td colspan="2" bgcolor="#f1ebe3" style="{{ $emailUi['head'] }}">Followers ({{ $followers->count() }})</td>
        </tr>
        @foreach ($followers as $index => $detail)
            @php $isLast = $index === $followers->count() - 1; @endphp
            <tr>
                <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $isLast ? $emailUi['labelLast'] : $emailUi['label'] }}">
                    {{ $detail->follower?->nik ?? '—' }}
                </td>
                <td style="{{ $isLast ? $emailUi['valueLast'] : $emailUi['value'] }}">
                    <strong>{{ $detail->follower?->employee?->fullname ?? 'Unknown Employee' }}</strong>
                    <br><span style="{{ $emailUi['meta'] }}">{{ $detail->follower?->position?->position_name ?? 'No Position' }}</span>
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
                <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['labelLast'] }}">Remarks</td>
                <td style="{{ $emailUi['valueLast'] }}">{{ $remarks }}</td>
            </tr>
        @endif
    </table>
@endif
