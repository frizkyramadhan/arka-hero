{{-- Flight Ticket Issuance — compact cozy --}}
@php
    $emailUi = $emailUi ?? require resource_path('views/emails/documents/partials/ui_tokens.php');
@endphp
@php
    /** @var \App\Models\FlightRequestIssuance $issuance */
    $issuance->loadNotificationRelations();
    $details = $issuance->issuanceDetails;
    $totalPrice = $details->sum(fn ($detail) => (float) ($detail->ticket_price ?? 0));
@endphp

<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="{{ $emailUi['section'] }}">
    <tr>
        <td colspan="2" bgcolor="#f1ebe3" style="{{ $emailUi['head'] }}">LG Information</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Issued Number</td>
        <td style="{{ $emailUi['value'] }}">{{ $issuance->issued_number ?: '—' }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Issued Date</td>
        <td style="{{ $emailUi['value'] }}">{{ $issuance->issued_date ? $issuance->issued_date->format('d F Y') : '—' }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Letter Number</td>
        <td style="{{ $emailUi['value'] }}">{{ $issuance->letter_number ?: '—' }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Business Partner</td>
        <td style="{{ $emailUi['value'] }}">{{ $issuance->businessPartner?->bp_name ?: '—' }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Issued By</td>
        <td style="{{ $emailUi['value'] }}">{{ $issuance->issuedBy?->name ?: '—' }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['label'] }}">Total Tickets</td>
        <td style="{{ $emailUi['value'] }}">{{ $details->count() }}</td>
    </tr>
    <tr>
        <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ filled($issuance->notes) ? $emailUi['label'] : $emailUi['labelLast'] }}">Total Price</td>
        <td style="{{ filled($issuance->notes) ? $emailUi['value'] : $emailUi['valueLast'] }}">Rp {{ number_format($totalPrice, 0, ',', '.') }}</td>
    </tr>
    @if (filled($issuance->notes))
        <tr>
            <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['labelLast'] }}">Notes</td>
            <td style="{{ $emailUi['valueLast'] }} white-space: pre-wrap;">{{ $issuance->notes }}</td>
        </tr>
    @endif
</table>

<table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="{{ $emailUi['section'] }}">
    <tr>
        <td colspan="2" bgcolor="#f1ebe3" style="{{ $emailUi['head'] }}">Ticket Details</td>
    </tr>
    @forelse ($details as $index => $detail)
        @php
            $isLast = $index === $details->count() - 1;
            $price = $detail->ticket_price ? 'Rp '.number_format((float) $detail->ticket_price, 0, ',', '.') : '—';
        @endphp
        <tr>
            <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $isLast ? $emailUi['labelLast'] : $emailUi['label'] }}">Ticket {{ $detail->ticket_order ?? ($index + 1) }}</td>
            <td style="{{ $isLast ? $emailUi['valueLast'] : $emailUi['value'] }}">
                <strong>{{ $detail->resolved_passenger_name ?: '—' }}</strong>
                <br><span style="{{ $emailUi['meta'] }}">Booking: {{ $detail->booking_code ?: '—' }} · {{ $price }}</span>
                @if (filled($detail->detail_reservation))
                    <br><span style="{{ $emailUi['meta'] }} white-space: pre-wrap;">{{ $detail->detail_reservation }}</span>
                @endif
            </td>
        </tr>
    @empty
        <tr>
            <td colspan="2" style="{{ $emailUi['muted'] }}">No ticket details</td>
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
                <td class="detail-label" width="{{ $emailUi['labelWidth'] }}" bgcolor="#faf8f5" style="{{ $emailUi['labelLast'] }}">Remarks</td>
                <td style="{{ $emailUi['valueLast'] }}">{{ $remarks }}</td>
            </tr>
        @endif
    </table>
@endif
