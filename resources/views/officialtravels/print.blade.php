<!DOCTYPE html>
<html lang="en">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>LETTER OF OFFICIAL TRAVEL
            @if (!empty($printStop))
                — {{ \Illuminate\Support\Str::limit($printStop->destination, 48) }}
            @endif
        </title>
        {{-- <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> --}}
        <!-- Google Font: Source Sans Pro -->
        <link rel="stylesheet" href="{{ asset('assets/dist/css/font.css') }}">
        <!-- Font Awesome -->
        <link rel="stylesheet" href="{{ asset('assets/plugins/fontawesome-free/css/all.min.css') }}">
        <!-- SweetAlert2 -->
        <link rel="stylesheet" href="{{ asset('assets/plugins/sweetalert2-theme-bootstrap-4/bootstrap-4.min.css') }}">
        <!-- Theme style -->
        <link rel="stylesheet" href="{{ asset('assets/dist/css/adminlte.min.css') }}">
        <style>
            body {
                font-family: Arial, sans-serif;
                font-size: 11pt;
            }

            .header {
                display: flex;
                align-items: center;
                gap: 20px;
                margin-bottom: 15px;
            }

            .logo {
                width: 150px;
            }

            .title {
                font-size: 16pt;
                font-weight: bold;
            }

            .number {
                text-align: center;
                margin-bottom: 20px;
                font-weight: bold;
                font-size: 14pt;
            }

            .info-row {
                display: flex;
                margin-bottom: 2px;
                line-height: 1;
            }

            .label {
                width: 170px;
                font-weight: bold;
                margin-bottom: 0;
                padding: 0;
            }

            .colon {
                width: 20px;
                text-align: center;
                margin-bottom: 0;
                padding: 0;
            }

            .value {
                flex: 1;
                margin-bottom: 0;
                padding: 0;
                line-height: 1;
            }

            .signature-section {
                margin-top: 30px;
            }

            .signature-date {
                margin-bottom: 10px;
            }

            .signature-boxes {
                display: flex;
                justify-content: space-between;
                gap: 100px;
            }

            .signature-box {
                text-align: center;
                flex: 1;
            }

            .signature-approval-meta {
                margin-top: 8px;
                margin-bottom: 4px;
                line-height: 1.35;
            }

            .signature-approval-meta .approver-block+.approver-block {
                margin-top: 10px;
            }

            .signature-section .signature-approver-name {
                font-size: 10pt;
                font-weight: 600;
                color: #222;
                margin-bottom: 4px;
            }

            .signature-section .signature-approver-position {
                font-size: 9pt;
                color: #444;
                margin-bottom: 0;
            }

            .signature-section .signature-approval-status-row {
                margin-bottom: 6px;
            }

            .signature-section .signature-approval-date {
                font-size: 9pt;
                color: #555;
                margin-bottom: 8px;
            }

            /* Approval status pills — aligned with flight-requests/print.blade.php */
            .signature-section .approval-status {
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

            .signature-section .approval-status.pending {
                background: #fff3cd;
                color: #856404;
                border-color: #e6cf7a;
            }

            .signature-section .approval-status.approved {
                background: #d4edda;
                color: #155724;
                border-color: #8fce9c;
            }

            .signature-section .approval-status.reject {
                background: #f8d7da;
                color: #721c24;
                border-color: #e4a8ad;
            }

            .signature-section .approval-status.fr-doc-cancelled {
                background: #fdebd0;
                color: #533f03;
                border-color: #e8c98f;
            }

            .signature-section .approval-status.fr-doc-submitted {
                background: #fff8e6;
                color: #856404;
                border-color: #f0d77a;
            }

            .signature-line {
                margin: 40px 0 10px 0;
                border-bottom: 1px solid #000;
                width: 200px;
                display: inline-block;
            }

            .dotted-line {
                border-bottom: 1px dotted black;
                display: inline-block;
                min-width: 200px;
            }

            .footer {
                display: flex;
                justify-content: space-between;
                margin-top: 30px;
                font-size: 10pt;
            }

            table td {
                padding: 3px 8px !important;
                line-height: 1.2 !important;
            }

            table th {
                padding: 5px 8px !important;
                line-height: 1.2 !important;
            }

            .arrival-departure td {
                padding: 8px !important;
            }

            .arrival-departure div {
                margin-bottom: 5px;
            }

            @media print {
                body {
                    padding: 20px;
                    print-color-adjust: exact;
                    -webkit-print-color-adjust: exact;
                }
            }
        </style>
    </head>

    <body>
        <div class="container-fluid">
            <div class="header">
                <img src="{{ asset('assets/dist/img/logo.png') }}" alt="ARKA" class="logo">
                <div class="title">LETTER OF OFFICIAL TRAVEL</div>
            </div>

            <div class="number">
                No. {{ $officialtravel->official_travel_number }}
            </div>
            @if (!empty($printStop))
                <div class="text-center small font-weight-bold mb-2">
                    Print sheet: destination {{ $printStopLeg }} of {{ $officialtravel->stops->count() }} only
                </div>
            @endif

            <div class="info-row">
                <div class="label">Name / NIK</div>
                <div class="colon">:</div>
                <div class="value">{{ $officialtravel->traveler->employee->fullname ?? 'N/A' }} /
                    {{ $officialtravel->traveler->nik ?? 'N/A' }}</div>
            </div>

            <div class="info-row">
                <div class="label">Title</div>
                <div class="colon">:</div>
                <div class="value">{{ $officialtravel->traveler->position->position_name ?? 'N/A' }}</div>
            </div>

            <div class="info-row">
                <div class="label">Business Unit</div>
                <div class="colon">:</div>
                <div class="value">{{ $officialtravel->traveler->project->project_name ?? 'N/A' }}</div>
            </div>

            <div class="info-row">
                <div class="label">Division / Department</div>
                <div class="colon">:</div>
                <div class="value">{{ $officialtravel->traveler->position->department->department_name ?? 'N/A' }}
                </div>
            </div>

            <br>

            <div class="info-row">
                <div class="label">Purpose of Travel</div>
                <div class="colon">:</div>
                <div class="value">{{ $officialtravel->purpose }}</div>
            </div>

            <div class="info-row align-items-start">
                <div class="label">Destination(s)</div>
                <div class="colon">:</div>
                <div class="value">
                    @if (!empty($printStop))
                        {{ $printStop->destination }}
                        @if ($printStop->is_manual)
                            <span class="text-muted">(manual)</span>
                        @endif
                    @elseif ($officialtravel->stops->isNotEmpty())
                        <ol class="mb-0 pl-3">
                            @foreach ($officialtravel->stops as $stop)
                                <li>
                                    {{ $stop->destination }}
                                    @if ($stop->is_manual)
                                        <span class="text-muted">(manual)</span>
                                    @endif
                                </li>
                            @endforeach
                        </ol>
                    @else
                        {{ $officialtravel->destination }}
                    @endif
                </div>
            </div>

            <div class="info-row">
                <div class="label">Duration of Travel</div>
                <div class="colon">:</div>
                <div class="value">{{ $officialtravel->duration }}</div>
            </div>

            <div class="info-row">
                <div class="label">Departure from</div>
                <div class="colon">:</div>
                <div class="value">{{ date('d F Y', strtotime($officialtravel->departure_from)) ?? 'N/A' }}</div>
            </div>

            <div class="mt-3 mb-2">
                <div class="mb-1">
                    <strong>Arrivals and Departures : </strong><i>(filled by officer on destination with Stamp and
                        Sign)</i>
                </div>
                @if (!empty($printStop))
                    <div class="mb-3">
                        <h6><strong>Destination {{ $printStopLeg }}</strong> — {{ $printStop->destination }}
                            @if ($printStop->is_manual)
                                <span class="text-muted">(manual)</span>
                            @endif
                        </h6>
                        <table class="table table-sm table-bordered arrival-departure">
                            <tr>
                                <td width="50%">
                                    <strong>Arrival at Destination</strong>
                                </td>
                                <td width="50%">
                                    <strong>Departure from Destination</strong>
                                </td>
                            </tr>
                            <tr>
                                <td width="50%" height="120px">
                                    <div>
                                        <strong>Checked by:</strong><br>
                                        {{ $printStop->arrivalChecker->name ?? '.........................' }}
                                    </div>
                                    <div>
                                        <strong>Remarks:</strong><br>
                                        {{ $printStop->arrival_remark ?? '.........................' }}
                                    </div>
                                    <div>
                                        <strong>Date:</strong><br>
                                        {{ $printStop->arrival_at_destination ? date('d/m/Y', strtotime($printStop->arrival_at_destination)) : '.........................' }}
                                    </div>
                                </td>
                                <td width="50%" height="120px">
                                    <div>
                                        <strong>Checked by:</strong><br>
                                        {{ $printStop->departureChecker->name ?? '.........................' }}
                                    </div>
                                    <div>
                                        <strong>Remarks:</strong><br>
                                        {{ $printStop->departure_remark ?? '.........................' }}
                                    </div>
                                    <div>
                                        <strong>Date:</strong><br>
                                        {{ $printStop->departure_from_destination ? date('d/m/Y', strtotime($printStop->departure_from_destination)) : '.........................' }}
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                @elseif ($officialtravel->stops->count() > 0)
                    @foreach ($officialtravel->stops as $index => $stop)
                        <div class="mb-3">
                            <h6><strong>Destination {{ $index + 1 }}</strong> — {{ $stop->destination }}
                                @if ($stop->is_manual)
                                    <span class="text-muted">(manual)</span>
                                @endif
                            </h6>
                            <table class="table table-sm table-bordered arrival-departure">
                                <tr>
                                    <td width="50%">
                                        <strong>Arrival at Destination</strong>
                                    </td>
                                    <td width="50%">
                                        <strong>Departure from Destination</strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="50%" height="120px">
                                        <div>
                                            <strong>Checked by:</strong><br>
                                            {{ $stop->arrivalChecker->name ?? '.........................' }}
                                        </div>
                                        <div>
                                            <strong>Remarks:</strong><br>
                                            {{ $stop->arrival_remark ?? '.........................' }}
                                        </div>
                                        <div>
                                            <strong>Date:</strong><br>
                                            {{ $stop->arrival_at_destination ? date('d/m/Y', strtotime($stop->arrival_at_destination)) : '.........................' }}
                                        </div>
                                    </td>
                                    <td width="50%" height="120px">
                                        <div>
                                            <strong>Checked by:</strong><br>
                                            {{ $stop->departureChecker->name ?? '.........................' }}
                                        </div>
                                        <div>
                                            <strong>Remarks:</strong><br>
                                            {{ $stop->departure_remark ?? '.........................' }}
                                        </div>
                                        <div>
                                            <strong>Date:</strong><br>
                                            {{ $stop->departure_from_destination ? date('d/m/Y', strtotime($stop->departure_from_destination)) : '.........................' }}
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    @endforeach
                @else
                    <div class="mb-3">
                        <table class="table table-sm table-bordered arrival-departure">
                            <tr>
                                <td width="50%">
                                    <strong>Arrival at Destination</strong>
                                </td>
                                <td width="50%">
                                    <strong>Departure from Destination</strong>
                                </td>
                            </tr>
                            <tr>
                                <td width="50%" height="120px">
                                    <div>
                                        <strong>Checked by:</strong><br>
                                        .........................
                                    </div>
                                    <div>
                                        <strong>Remarks:</strong><br>
                                        .........................
                                    </div>
                                    <div>
                                        <strong>Date:</strong><br>
                                        .........................
                                    </div>
                                </td>
                                <td width="50%" height="120px">
                                    <div>
                                        <strong>Checked by:</strong><br>
                                        .........................
                                    </div>
                                    <div>
                                        <strong>Remarks:</strong><br>
                                        .........................
                                    </div>
                                    <div>
                                        <strong>Date:</strong><br>
                                        .........................
                                    </div>
                                </td>
                            </tr>
                        </table>
                    </div>
                @endif
            </div>

            <div class="info-row">
                <div class="label">Follower/s</div>
                <div class="colon">:</div>
            </div>
            <table class="table table-sm table-bordered" width="100%">
                <thead>
                    <tr>
                        <th class="text-center" width="5%">No.</th>
                        <th class="text-center">Name / NIK</th>
                        <th class="text-center">Title</th>
                        <th class="text-center">Business Unit</th>
                        <th class="text-center">Department</th>
                    </tr>
                </thead>
                <tbody>
                    @if ($officialtravel->details->count() > 0)
                        @foreach ($officialtravel->details as $key => $detail)
                            <tr>
                                <td class="text-center">{{ $key + 1 }}</td>
                                <td>{{ $detail->follower->employee->fullname ?? 'N/A' }} /
                                    {{ $detail->follower->nik ?? 'N/A' }}</td>
                                <td>{{ $detail->follower->position->position_name ?? 'N/A' }}</td>
                                <td>{{ $detail->follower->project->project_name ?? 'N/A' }}</td>
                                <td>{{ $detail->follower->position->department->department_name ?? 'N/A' }}</td>
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="5" class="text-center">No Follower Available</td>
                        </tr>
                    @endif
                </tbody>
            </table>

            <div class="mt-4">
                <div class="info-row">
                    <div class="label">Note</div>
                    <div class="colon">:</div>
                </div>
                <div class="info-row">
                    <div class="label">Transportation</div>
                    <div class="colon">:</div>
                    <div class="value">
                        {{ $officialtravel->transportation->transportation_name }}</div>
                </div>
                <div class="info-row">
                    <div class="label">Acommodation</div>
                    <div class="colon">:</div>
                    <div class="value">
                        {{ $officialtravel->accommodation->accommodation_name }}
                    </div>
                </div>
            </div>

            @php
                $approvalPlanStatusLabels = [
                    0 => 'Pending',
                    1 => 'Approved',
                    2 => 'Reject',
                    3 => 'Cancelled',
                    4 => 'Revised',
                ];
                $printApprovalPlans = ($officialtravel->approval_plans ?? collect())->sortBy([
                    ['approval_order', 'asc'],
                    ['id', 'asc'],
                ]);
                $printDistinctOrders = $printApprovalPlans
                    ->pluck('approval_order')
                    ->filter(fn($o) => $o !== null)
                    ->unique()
                    ->sort()
                    ->values();
                $printRecommendedPlans = collect();
                $printFinalApprovalPlans = collect();
                if ($printDistinctOrders->isNotEmpty()) {
                    $printMinOrder = $printDistinctOrders->first();
                    $printMaxOrder = $printDistinctOrders->last();
                    $printRecommendedPlans = $printApprovalPlans->where('approval_order', $printMinOrder)->values();
                    $printFinalApprovalPlans = $printApprovalPlans->where('approval_order', $printMaxOrder)->values();
                } elseif ($printApprovalPlans->isNotEmpty()) {
                    $printRecommendedPlans = $printApprovalPlans->values();
                    $printFinalApprovalPlans = $printApprovalPlans->values();
                }
            @endphp

            <div class="signature-section">
                <div class="signature-date">
                    Balikpapan, {{ date('d F Y', strtotime($officialtravel->official_travel_date)) }}
                </div>
                <div class="signature-boxes">
                    <div class="signature-box">
                        <div>Approved by,</div>
                        <div class="signature-approval-meta">
                            @forelse ($printFinalApprovalPlans as $plan)
                                @php
                                    $lotSt = (int) ($plan->status ?? 0);
                                    $lotStatusText = $approvalPlanStatusLabels[$lotSt] ?? 'Pending';
                                    $lotStatusClass = match ($lotSt) {
                                        1 => 'approved',
                                        2 => 'reject',
                                        3 => 'fr-doc-cancelled',
                                        4 => 'fr-doc-submitted',
                                        default => 'pending',
                                    };
                                    $lotPlanTs = $plan->updated_at ?? $plan->created_at;
                                    $lotApproverPosition = $plan->approver?->administration?->position?->position_name;
                                @endphp
                                <div class="approver-block">
                                    <br>
                                    <div class="signature-approval-status-row">
                                        <span
                                            class="approval-status {{ $lotStatusClass }}">{{ $lotStatusText }}</span>
                                    </div>
                                    <div class="signature-approval-date">
                                        {{ $lotSt !== 0 && $lotPlanTs ? $lotPlanTs->format('d/m/Y') : '—' }}
                                    </div>
                                    <div class="signature-approver-name">{{ $plan->approver->name ?? '—' }}</div>
                                    <div class="signature-approver-position">
                                        {{ $lotApproverPosition ?? '—' }}
                                    </div>
                                </div>
                            @empty
                                <div class="text-muted">—</div>
                            @endforelse
                        </div>
                        @if ($printFinalApprovalPlans->isEmpty())
                            <div style="margin-top: 48px">( Position )</div>
                        @else
                            <div style="margin-top: 36px; min-height: 28px;" aria-hidden="true"></div>
                        @endif
                    </div>
                    <div class="signature-box">
                        <div>Recommended by,</div>
                        <div class="signature-approval-meta">
                            @forelse ($printRecommendedPlans as $plan)
                                @php
                                    $lotSt = (int) ($plan->status ?? 0);
                                    $lotStatusText = $approvalPlanStatusLabels[$lotSt] ?? 'Pending';
                                    $lotStatusClass = match ($lotSt) {
                                        1 => 'approved',
                                        2 => 'reject',
                                        3 => 'fr-doc-cancelled',
                                        4 => 'fr-doc-submitted',
                                        default => 'pending',
                                    };
                                    $lotPlanTs = $plan->updated_at ?? $plan->created_at;
                                    $lotApproverPosition = $plan->approver?->administration?->position?->position_name;
                                @endphp
                                <div class="approver-block">
                                    <br>
                                    <div class="signature-approval-status-row">
                                        <span
                                            class="approval-status {{ $lotStatusClass }}">{{ $lotStatusText }}</span>
                                    </div>
                                    <div class="signature-approval-date">
                                        {{ $lotSt !== 0 && $lotPlanTs ? $lotPlanTs->format('d/m/Y') : '—' }}
                                    </div>
                                    <div class="signature-approver-name">{{ $plan->approver->name ?? '—' }}</div>
                                    <div class="signature-approver-position">
                                        {{ $lotApproverPosition ?? '—' }}
                                    </div>
                                </div>
                            @empty
                                <div class="text-muted">—</div>
                            @endforelse
                        </div>
                        @if ($printRecommendedPlans->isEmpty())
                            <div style="margin-top: 48px">( Position )</div>
                        @else
                            <div style="margin-top: 36px; min-height: 28px;" aria-hidden="true"></div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="footer">
                <div>ARKA/HCS/IV/04.01</div>
                <div>Rev.2</div>
                <div>Page 1/ 1</div>
            </div>
        </div>
    </body>

</html>
