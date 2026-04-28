<!DOCTYPE html>
<html lang="id">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{{ $flightRequest->form_number ?? 'N/A' }} - Flight Request</title>
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
                max-width: 210mm;
                margin: 0 auto;
                background: white;
                border: 1px solid #000;
                padding: 20px;
            }

            .header {
                display: flex;
                justify-content: space-between;
                align-items: flex-start;
                margin-bottom: 20px;
                padding-bottom: 15px;
                border-bottom: 1px solid #000;
            }

            .logo-section {
                width: 180px;
                display: flex;
                align-items: center;
                justify-content: flex-start;
            }

            .logo-section img {
                max-width: 100%;
                height: auto;
                display: block;
            }

            .document-title {
                flex: 1;
                text-align: center;
                padding-left: 15px;
            }

            .document-title-line1 {
                font-size: 14pt;
                font-weight: bold;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .document-title-line2 {
                font-size: 14pt;
                font-weight: bold;
                text-transform: uppercase;
                letter-spacing: 0.5px;
                margin-top: 2px;
            }

            /* Employee Information - cozy style */
            .employee-info-section .section-header {
                background: linear-gradient(180deg, #eaeaea 0%, #e0e0e0 100%);
                border: 1px solid #bbb;
                border-bottom: none;
                padding: 10px 14px;
                font-weight: bold;
                text-transform: uppercase;
                font-size: 10pt;
                letter-spacing: 0.3px;
                border-radius: 4px 4px 0 0;
            }

            .employee-info-section .section-body {
                border: 1px solid #bbb;
                border-top: none;
                padding: 14px 16px;
                background-color: #fafafa;
                border-radius: 0 0 4px 4px;
            }

            .employee-info-section .info-row {
                display: flex;
                align-items: center;
                min-height: 30px;
                padding: 4px 0;
                margin-bottom: 2px;
            }

            .employee-info-section .info-row:nth-child(even) {
                background-color: rgba(255, 255, 255, 0.5);
            }

            .employee-info-section .info-row .label {
                width: 180px;
                font-weight: 600;
                text-transform: uppercase;
                flex-shrink: 0;
                line-height: 1.3;
                color: #444;
                font-size: 10pt;
            }

            .employee-info-section .info-row .colon {
                width: 20px;
                text-align: center;
                flex-shrink: 0;
                color: #666;
            }

            .employee-info-section .info-row .value {
                flex: 1;
                border-bottom: 1px solid #ccc;
                min-height: 22px;
                display: flex;
                align-items: center;
                padding-left: 4px;
                color: #222;
            }

            /* Request Flight Booking - same cozy style */
            .flight-booking-section .section-header {
                background: linear-gradient(180deg, #eaeaea 0%, #e0e0e0 100%);
                border: 1px solid #bbb;
                border-bottom: none;
                padding: 10px 14px;
                font-weight: bold;
                text-transform: uppercase;
                font-size: 10pt;
                letter-spacing: 0.3px;
                border-radius: 4px 4px 0 0;
            }

            .flight-booking-section .section-body {
                border: 1px solid #bbb;
                border-top: none;
                padding: 14px 16px;
                background-color: #fafafa;
                border-radius: 0 0 4px 4px;
            }

            .flight-booking-section .flight-table th,
            .flight-booking-section .flight-table td {
                border: 1px solid #bbb;
                padding: 8px 10px;
            }

            .flight-booking-section .flight-table th {
                background: linear-gradient(180deg, #eaeaea 0%, #e0e0e0 100%);
                font-weight: 600;
                color: #444;
            }

            .flight-booking-section .flight-table tbody tr:nth-child(even) {
                background-color: rgba(255, 255, 255, 0.5);
            }

            .flight-booking-section .flight-table tbody td {
                color: #222;
            }

            /* Other sections keep original */
            .section-header {
                background-color: #d3d3d3;
                border: 1px solid #000;
                padding: 6px 10px;
                font-weight: bold;
                text-transform: uppercase;
                font-size: 10pt;
                margin-bottom: 0;
            }

            .section-body {
                border: 1px solid #000;
                border-top: none;
                padding: 10px 12px;
            }

            .info-row {
                display: flex;
                align-items: center;
                height: 28px;
                margin-bottom: 0;
            }

            .info-row .label {
                width: 180px;
                font-weight: bold;
                text-transform: uppercase;
                flex-shrink: 0;
                line-height: 1.2;
            }

            .info-row .colon {
                width: 20px;
                text-align: center;
                flex-shrink: 0;
            }

            .info-row .value {
                flex: 1;
                border-bottom: 1px solid #000;
                height: 100%;
                display: flex;
                align-items: center;
                min-height: 0;
            }

            .flight-table {
                width: 100%;
                border-collapse: collapse;
                font-size: 10pt;
            }

            .flight-table th,
            .flight-table td {
                border: 1px solid #000;
                padding: 6px 8px;
                text-align: center;
            }

            .flight-table th {
                background-color: #f5f5f5;
                font-weight: bold;
                text-transform: uppercase;
            }

            .flight-table td {
                text-align: left;
            }

            .flight-table tbody tr {
                min-height: 36px;
            }

            .flight-table tbody td {
                min-height: 36px;
                vertical-align: middle;
            }

            .flight-table .col-date {
                width: 18%;
            }

            .flight-table .col-from {
                width: 20%;
            }

            .flight-table .col-to {
                width: 20%;
            }

            .flight-table .col-flight {
                width: 22%;
            }

            .flight-table .col-etd {
                width: 20%;
            }

            /* Note section - cozy style */
            .note-section {
                margin-top: 15px;
            }

            .note-section .note-label {
                font-weight: 600;
                text-transform: uppercase;
                margin-bottom: 6px;
                font-size: 10pt;
                color: #444;
                letter-spacing: 0.3px;
            }

            .note-section .note-box {
                border: 1px solid #bbb;
                min-height: auto;
                padding: 10px 12px;
                background: #fafafa;
                color: #222;
                font-size: 10pt;
                border-radius: 4px;
            }

            /* Approval section - compact + cozy style */
            .approval-section {
                margin-top: 15px;
            }

            .approval-section .approval-row {
                display: flex;
                align-items: center;
                justify-content: space-between;
                margin-bottom: 10px;
                min-height: 34px;
                padding: 3px 0;
            }

            .approval-section .approval-row:last-child {
                margin-bottom: 0;
            }

            .approval-section .approval-left {
                display: flex;
                align-items: baseline;
                flex-shrink: 0;
                width: 160px;
            }

            .approval-section .approval-label {
                font-weight: 600;
                text-transform: uppercase;
                font-size: 10pt;
                color: #444;
                flex-shrink: 0;
                letter-spacing: 0.3px;
            }

            .approval-section .approval-left span:not(.approval-label) {
                color: #666;
                margin: 0 2px;
            }

            .approval-section .approval-center {
                flex: 1;
                min-width: 0;
                padding-left: 8px;
                text-align: left;
            }

            .approval-section .approval-name {
                display: inline-block;
                min-width: 120px;
                border-bottom: 1px solid #ccc;
                padding-bottom: 1px;
                font-size: 10pt;
                color: #222;
            }

            .approval-section .approval-right {
                display: flex;
                align-items: center;
                justify-content: center;
                flex-shrink: 0;
                min-width: 140px;
                text-align: center;
            }

            /* Pill / subtle “button” — readable in print but not oversized */
            .approval-section .approval-status {
                display: inline-block;
                font-weight: 600;
                font-size: 10.5pt;
                letter-spacing: 0.02em;
                text-transform: capitalize;
                text-align: center;
                line-height: 1.3;
                padding: 5px 14px;
                border-radius: 5px;
                border: 1px solid transparent;
                box-sizing: border-box;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .approval-section .approval-status.pending {
                background: #fff3cd;
                color: #856404;
                border-color: #e6cf7a;
            }

            .approval-section .approval-status.approved {
                background: #d4edda;
                color: #155724;
                border-color: #8fce9c;
            }

            .approval-section .approval-status.reject {
                background: #f8d7da;
                color: #721c24;
                border-color: #e4a8ad;
            }

            .approval-section .approval-status.fr-doc-draft {
                background: #e9ecef;
                color: #343a40;
                border-color: #ced4da;
            }

            .approval-section .approval-status.fr-doc-submitted {
                background: #fff8e6;
                color: #856404;
                border-color: #f0d77a;
            }

            .approval-section .approval-status.fr-doc-issued {
                background: #cfe2ff;
                color: #084298;
                border-color: #9ec5fe;
            }

            .approval-section .approval-status.fr-doc-completed {
                background: #e2e3e5;
                color: #2b3035;
                border-color: #c5c8cb;
            }

            .approval-section .approval-status.fr-doc-cancelled {
                background: #fdebd0;
                color: #533f03;
                border-color: #e8c98f;
            }

            /* Footer - cozy style */
            .footer {
                display: flex;
                justify-content: space-between;
                margin-top: 24px;
                padding-top: 10px;
                font-size: 9pt;
                border-top: 1px solid #bbb;
                color: #555;
            }

            @media print {
                body {
                    print-color-adjust: exact;
                    -webkit-print-color-adjust: exact;
                    padding: 0;
                }

                .print-container {
                    max-width: 100%;
                    border: 1px solid #000;
                }

                .no-print {
                    display: none;
                }
            }
        </style>
    </head>

    <body>
        @php
            $employee = $flightRequest->employee;
            $administration = $flightRequest->administration ?? ($employee ? $employee->activeAdministration : null);
            $name = $flightRequest->employee_name ?? ($employee ? $employee->fullname : 'N/A');
            $nik = $flightRequest->nik ?? ($administration ? $administration->nik : 'N/A');
            $position =
                $flightRequest->position ??
                ($administration && $administration->position ? $administration->position->position_name : 'N/A');
            $department =
                $flightRequest->department ??
                ($administration && $administration->position && $administration->position->department
                    ? $administration->position->department->department_name
                    : 'N/A');
            $project =
                $flightRequest->project ??
                ($administration && $administration->project ? $administration->project->project_name : 'N/A');
            $projectCode = $administration && $administration->project ? $administration->project->project_code : null;
            $projectNumber = $projectCode ? $projectCode . ' - ' . $project : $project;
            $phoneNumber = $flightRequest->phone_number ?? ($administration ? $administration->phone_number : '-');
            $poh = $flightRequest->poh ?? ($administration && $administration->poh ? $administration->poh : 'N/A');
            $doh =
                $administration && $administration->doh
                    ? \Carbon\Carbon::parse($administration->doh)->format('d F Y')
                    : 'N/A';
            $flightDetails = $flightRequest->details
                ? $flightRequest->details->sortBy(['segment_order', 'flight_date'])->values()
                : collect();
            $requestedByName = $flightRequest->requestedBy->name ?? $name;
            $frStatusKey = (string) ($flightRequest->status ?? '');
            $frStatusLabel = \App\Models\FlightRequest::getStatusOptions()[$frStatusKey]
                ?? ucfirst(str_replace('_', ' ', $frStatusKey !== '' ? $frStatusKey : '—'));
            $frDocStatusClass = match ($frStatusKey) {
                \App\Models\FlightRequest::STATUS_DRAFT => 'fr-doc-draft',
                \App\Models\FlightRequest::STATUS_SUBMITTED => 'fr-doc-submitted',
                \App\Models\FlightRequest::STATUS_APPROVED => 'approved',
                \App\Models\FlightRequest::STATUS_ISSUED => 'fr-doc-issued',
                \App\Models\FlightRequest::STATUS_COMPLETED => 'fr-doc-completed',
                \App\Models\FlightRequest::STATUS_REJECTED => 'reject',
                \App\Models\FlightRequest::STATUS_CANCELLED => 'fr-doc-cancelled',
                default => 'pending',
            };
            $sortedApprovalPlans = $flightRequest->approvalPlans
                ->sortBy(function ($plan) {
                    return [$plan->approval_order ?? 999999, $plan->id];
                })
                ->values();
            $approvalStatusLabels = [
                0 => 'Pending',
                1 => 'Approved',
                2 => 'Reject',
            ];
        @endphp

        <div class="print-container">
            <!-- Header -->
            <div class="header">
                <div class="logo-section">
                    <img src="{{ asset('images/logo_2.jpg') }}" alt="ARKA"
                        style="height: 50px; width: 200px; max-height: 100%;">
                </div>
                <div class="document-title">
                    <div class="document-title-line1">Permintaan Penerbangan / </div>
                    <div class="document-title-line2">Flight Request</div>
                </div>
            </div>

            <!-- Employee Information -->
            <div class="employee-info-section">
                <div class="section-header">Employee Information</div>
                <div class="section-body">
                    <div class="info-row">
                        <span class="label">Name</span>
                        <span class="colon">:</span>
                        <span class="value">{{ $name }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">ID Number / NIK</span>
                        <span class="colon">:</span>
                        <span class="value">{{ $nik }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Position</span>
                        <span class="colon">:</span>
                        <span class="value">{{ $position }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Dept/Division</span>
                        <span class="colon">:</span>
                        <span class="value">{{ $department }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">POH</span>
                        <span class="colon">:</span>
                        <span class="value">{{ $poh }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">DOH</span>
                        <span class="colon">:</span>
                        <span class="value">{{ $doh }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Project Number</span>
                        <span class="colon">:</span>
                        <span class="value">{{ $projectNumber }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Phone Number</span>
                        <span class="colon">:</span>
                        <span class="value">{{ $phoneNumber }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Purpose of Travel</span>
                        <span class="colon">:</span>
                        <span class="value">{{ $flightRequest->purpose_of_travel ?? '-' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Total Travel Days</span>
                        <span class="colon">:</span>
                        <span class="value">{{ $flightRequest->total_travel_days ?? '-' }}</span>
                    </div>
                </div>
            </div>

            <!-- Request Flight Booking -->
            <div class="flight-booking-section" style="margin-top: 15px;">
                <div class="section-header">Request Flight Booking</div>
                <div class="section-body">
                    @if (
                        $flightRequest->request_type === \App\Models\FlightRequest::TYPE_TRAVEL_BASED &&
                            $flightRequest->officialTravel &&
                            $flightRequest->officialTravel->details->isNotEmpty())
                        <div style="margin-bottom: 12px;">
                            <div class="info-row" style="margin-bottom: 6px;">
                                <span class="label">Follower/s</span>
                                <span class="colon">:</span>
                            </div>
                            <table class="flight-table" style="margin-top: 0;">
                                <thead>
                                    <tr>
                                        <th style="text-align: center; width: 8%;">No.</th>
                                        <th style="text-align: center;">Name / NIK</th>
                                        <th style="text-align: center;">Title</th>
                                        <th style="text-align: center;">Business Unit</th>
                                        <th style="text-align: center;">Department</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($flightRequest->officialTravel->details as $key => $detail)
                                        <tr>
                                            <td style="text-align: center;">{{ $key + 1 }}</td>
                                            <td>{{ $detail->follower->employee->fullname ?? 'N/A' }} /
                                                {{ $detail->follower->nik ?? 'N/A' }}</td>
                                            <td>{{ $detail->follower->position->position_name ?? 'N/A' }}</td>
                                            <td>{{ $detail->follower->project->project_name ?? 'N/A' }}</td>
                                            <td>{{ $detail->follower->position->department->department_name ?? 'N/A' }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                    <table class="flight-table">
                        <thead>
                            <tr>
                                <th class="col-date">Date</th>
                                <th class="col-from">From</th>
                                <th class="col-to">To</th>
                                <th class="col-flight">Flight By</th>
                                <th class="col-etd">ETD</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($flightDetails as $detail)
                                <tr>
                                    <td>{{ $detail->flight_date ? $detail->flight_date->format('d. m. Y') : '-' }}</td>
                                    <td>{{ $detail->departure_city ?? '-' }}</td>
                                    <td>{{ $detail->arrival_city ?? '-' }}</td>
                                    <td>{{ $detail->airline ?? '-' }}</td>
                                    <td>{{ $detail->flight_time ? \Carbon\Carbon::parse($detail->flight_time)->format('H.i') : '-' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Note -->
            <div class="note-section">
                <div class="note-label">Note :</div>
                <div class="note-box">{{ $flightRequest->notes ?? ' ' }}</div>
            </div>

            <!-- Approval status -->
            <div class="approval-section">
                <div class="approval-row">
                    <div class="approval-left">
                        <span class="approval-label">Travel Request By</span>
                    </div>
                    <div class="approval-center">
                        <span> : </span>
                        <span class="approval-name">{{ $requestedByName }}</span>
                    </div>
                    <div class="approval-right">
                        <span class="approval-status fr-doc-status {{ $frDocStatusClass }}">{{ $frStatusLabel }}</span>
                    </div>
                </div>
                @forelse ($sortedApprovalPlans as $idx => $plan)
                    @php
                        $planCount = $sortedApprovalPlans->count();
                        if ($planCount === 1) {
                            $rowLabel = 'Acknowledged By';
                        } elseif ($idx === 0) {
                            $rowLabel = 'Acknowledged By';
                        } elseif ($idx === $planCount - 1) {
                            $rowLabel = 'Travel Approved By';
                        } else {
                            $rowLabel = 'Approved By';
                        }
                        $st = (int) ($plan->status ?? 0);
                        $statusText = $approvalStatusLabels[$st] ?? 'Pending';
                        $statusClass = match ($st) {
                            1 => 'approved',
                            2 => 'reject',
                            default => 'pending',
                        };
                        $approverName = $plan->approver->name ?? '—';
                    @endphp
                    <div class="approval-row">
                        <div class="approval-left">
                            <span class="approval-label">{{ $rowLabel }}</span>
                        </div>
                        <div class="approval-center">
                            <span> : </span>
                            <span class="approval-name">{{ $approverName }}</span>
                        </div>
                        <div class="approval-right">
                            <span class="approval-status {{ $statusClass }}">{{ $statusText }}</span>
                        </div>
                    </div>
                @empty
                @endforelse
            </div>

            <!-- Footer -->
            <div class="footer">
                <div>ARKA/HCS/IV/04.03</div>
                <div>Rev.2</div>
                <div>Page 1/1</div>
            </div>
        </div>

        <script>
            window.onload = function() {
                window.print();
            };
        </script>
    </body>

</html>
