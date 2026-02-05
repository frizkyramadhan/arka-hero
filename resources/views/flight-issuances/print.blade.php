<!DOCTYPE html>
<html lang="id">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Letter of Guarantee - {{ $issuance->issued_number }}</title>
        <style>
            @page {
                margin: 2cm;
            }

            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: Arial, sans-serif;
                font-size: 11pt;
                line-height: 1.4;
                color: #000;
                background: white;
                display: flex;
                justify-content: center;
                padding: 20px;
            }

            .print-container {
                width: 100%;
                max-width: 800px;
                /* A4 width: 210mm = 794px at 96 DPI */
                max-width: 210mm;
                margin: 0 auto;
                background: white;
            }

            .header {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                margin-bottom: 20px;
                padding-bottom: 10px;
                border-bottom: 1px solid #000;
            }

            .logo-section {
                width: 200px;
                display: flex;
                align-items: center;
                justify-content: center;
            }

            .logo-section img {
                max-width: 100%;
                height: auto;
                display: block;
            }

            .company-info {
                text-align: right;
                flex: 1;
                padding-left: 20px;
            }

            .company-name {
                font-size: 14pt;
                font-weight: bold;
                margin-bottom: 3px;
            }

            .company-type {
                font-size: 9pt;
                margin-bottom: 5px;
            }

            .company-address {
                font-size: 9pt;
                margin-bottom: 2px;
            }

            .company-contact {
                font-size: 9pt;
            }

            .recipient-section {
                margin-top: 20px;
                margin-bottom: 15px;
            }

            .recipient {
                margin-bottom: 5px;
            }

            .date {
                text-align: right;
                margin-bottom: 15px;
            }

            .subject-section {
                margin-bottom: 15px;
                text-align: center;
            }

            .subject-content {
                display: inline-block;
                text-align: left;
            }

            .subject-line {
                margin-bottom: 3px;
            }

            .body-intro {
                margin-bottom: 20px;
            }

            .ticket-details {
                margin-bottom: 20px;
            }

            .ticket-item {
                margin-bottom: 18px;
            }

            .ticket-field {
                display: flex;
                margin-bottom: 3px;
            }

            .ticket-label {
                width: 220px;
                font-weight: normal;
            }

            .ticket-value {
                flex: 1;
            }

            .service-charge-section {
                margin-top: 20px;
                padding-top: 12px;
                border-top: 1px solid #333;
            }

            .service-charge-section .ticket-field {
                margin-bottom: 4px;
            }

            .service-charge-total .ticket-label,
            .service-charge-total .ticket-value {
                font-weight: bold;
            }

            .closing {
                margin-bottom: 30px;
            }

            .signature-section {
                margin-top: 50px;
            }

            .signature-line {
                margin-bottom: 5px;
            }

            .signature-name {
                font-weight: bold;
                margin-top: 5px;
            }

            .signature-title {
                font-size: 9pt;
            }

            @media print {
                body {
                    print-color-adjust: exact;
                    -webkit-print-color-adjust: exact;
                    padding: 0;
                }

                .print-container {
                    max-width: 100%;
                    margin: 0;
                }

                .no-print {
                    display: none;
                }
            }
        </style>
    </head>

    <body>
        <div class="print-container">
            <!-- Header -->
            <div class="header">
                <div class="logo-section">
                    <img src="{{ asset('images/logo_1.png') }}" alt="ARKA Mining and Construction Logo">
                </div>
                <div class="company-info">
                    <div class="company-name">PT. ARKANANTA APTA PRATISTA</div>
                    <div class="company-type">Mining and Construction</div>
                    <div class="company-address">JI.MT.Haryono No.131 - 133 RT.43 Balikpapan 76126</div>
                    <div class="company-contact">Telp.: (0542) 7212 710 Fax.: (0542) 7212 730</div>
                </div>
            </div>

            <!-- Date -->
            <div class="date">
                Balikpapan, {{ $issuance->issued_date ? $issuance->issued_date->format('d F Y') : date('d F Y') }}
            </div>

            <!-- Recipient -->
            <div class="recipient-section">
                <div class="recipient">Kepada Yth,</div>
                <div class="recipient">Pimpinan {{ $issuance->businessPartner->bp_name ?? 'Business Partner' }}</div>
                <div class="recipient">Balikpapan</div>
            </div>

            <!-- Subject -->
            <div class="subject-section">
                <div class="subject-content">
                    <div class="subject-line">Hal : Permohonan Issued</div>
                    <div class="subject-line">No : {{ $issuance->issued_number }}</div>
                </div>
            </div>

            <!-- Body Introduction -->
            <div class="body-intro">
                Dengan hormat,
                <br>
                Bersama ini kami ajukan surat permohonan issued tiket karyawan kami sebagai berikut:
            </div>

            <!-- Ticket Details -->
            <div class="ticket-details">
                @php
                    $flightRequest = $issuance->flightRequests->first();
                    $flightDetails =
                        $flightRequest && $flightRequest->details
                            ? $flightRequest->details->sortBy(['segment_order', 'flight_date'])->values()
                            : collect();
                @endphp
                @foreach ($issuance->issuanceDetails as $index => $detail)
                    <div class="ticket-item">
                        <div class="ticket-field">
                            <span class="ticket-label">KODE BOOKING</span>
                            <span class="ticket-value">: {{ $detail->booking_code ?? '-' }}</span>
                        </div>
                        @php
                            // Try to get flight detail by index (ticket order - 1) or use detail_reservation
                            $flightDetail = $flightDetails->get($index) ?? null;
                            $detailReservation = $detail->detail_reservation;
                        @endphp
                        <div class="ticket-field">
                            <span class="ticket-label">DETAIL RESERVASI</span>
                            <span class="ticket-value">:
                                @if ($detailReservation)
                                    {{ $detailReservation }}
                                @elseif ($flightDetail && $flightDetail->flight_date)
                                    @php
                                        $departure = strtoupper($flightDetail->departure_city ?? '');
                                        $arrival = strtoupper($flightDetail->arrival_city ?? '');
                                        // Get airport codes - use first 3 letters uppercase
                                        $depCode =
                                            strlen($departure) >= 3
                                                ? strtoupper(substr($departure, 0, 3))
                                                : strtoupper($departure);
                                        $arrCode =
                                            strlen($arrival) >= 3
                                                ? strtoupper(substr($arrival, 0, 3))
                                                : strtoupper($arrival);
                                        // Format: 06 JAN 2026 (bulan uppercase)
                                        $dateStr = strtoupper($flightDetail->flight_date->format('d M Y'));
                                        $timeStr = $flightDetail->flight_time
                                            ? \Carbon\Carbon::parse($flightDetail->flight_time)->format('H.i')
                                            : '-';
                                    @endphp
                                    {{ $dateStr }} // {{ $depCode }} {{ $arrCode }} //
                                    {{ $timeStr }}
                                @else
                                    -
                                @endif
                            </span>
                        </div>
                        <div class="ticket-field">
                            <span class="ticket-label">NAMA PENUMPANG</span>
                            <span class="ticket-value">: {{ strtoupper($detail->passenger_name) }}</span>
                        </div>
                        <div class="ticket-field">
                            <span class="ticket-label">TICKET PRICE</span>
                            <span class="ticket-value">:
                                {{ $detail->ticket_price ? number_format($detail->ticket_price, 0, ',', '.') : '-' }}</span>
                        </div>
                    </div>
                @endforeach

                @php
                    $totalServiceCharge = $issuance->issuanceDetails->sum('service_charge') ?? 0;
                    $totalServiceVat = $issuance->issuanceDetails->sum('service_vat') ?? 0;
                @endphp
                @if ($totalServiceCharge > 0 || $totalServiceVat > 0)
                    <div class="service-charge-section">
                        <div class="ticket-field">
                            <span class="ticket-label">SERVICE CHARGE</span>
                            <span class="ticket-value">: Rp
                                {{ number_format($totalServiceCharge, 0, ',', '.') }}</span>
                        </div>
                        <div class="ticket-field">
                            <span class="ticket-label">SERVICE CHARGE VAT 11%</span>
                            <span class="ticket-value">: Rp {{ number_format($totalServiceVat, 0, ',', '.') }}</span>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Closing -->
            <div class="closing">
                Demikian surat permohonan ini kami sampaikan. Atas perhatian dan kerjasama yang baik kami ucapkan
                terimakasih.
            </div>

            <!-- Signature -->
            @php
                $approverIds = $issuance->manual_approvers ?? [];
                $approverNames = collect($approverIds)
                    ->map(function ($userId) {
                        return \App\Models\User::find($userId)?->name;
                    })
                    ->filter()
                    ->values();
                $signatoryName = $approverNames->isNotEmpty()
                    ? $approverNames->last()
                    : $issuance->issuedBy->name ?? 'PT. ARKANANTA APTA PRATISTA';
            @endphp
            <div class="signature-section">
                <div class="signature-line">Hormat kami,</div>
                <div class="signature-line" style="margin-top: 50px;">
                    <div class="signature-name">{{ $signatoryName }}</div>
                    <div class="signature-title">HCS Div. Manager</div>
                </div>
            </div>
        </div>

        <script>
            window.onload = function() {
                window.print();
            };
        </script>
    </body>

</html>
