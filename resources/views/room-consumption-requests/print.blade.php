<!DOCTYPE html>
<html lang="id">

    <head>
        <meta charset="UTF-8">
        <title>Room &amp; Consumption Request — {{ $doc->request_number ?: 'Draft' }}</title>
        <style>
            * {
                box-sizing: border-box;
            }

            body {
                font-family: Arial, Helvetica, sans-serif;
                font-size: 12px;
                color: #000;
                margin: 16px;
            }

            .form-wrap {
                border: 1.5px solid #000;
                max-width: 900px;
                margin: 0 auto;
            }

            .header {
                display: flex;
                align-items: stretch;
                border-bottom: 1.5px solid #000;
            }

            .logo-cell {
                display: flex;
                align-items: center;
                justify-content: center;
                border-right: 1.5px solid #000;
                padding: 8px 12px;
                min-width: 120px;
            }

            .logo-cell img {
                max-height: 42px;
                width: auto;
                display: block;
            }

            .title {
                flex: 1;
                display: flex;
                align-items: center;
                justify-content: center;
                font-size: 18px;
                font-weight: 700;
                color: #666;
                letter-spacing: 1px;
                text-transform: uppercase;
                padding: 12px 16px;
            }

            table.form {
                width: 100%;
                border-collapse: collapse;
            }

            table.form td {
                border: 1px solid #000;
                padding: 8px 10px;
                vertical-align: top;
            }

            .label {
                font-weight: 600;
                white-space: nowrap;
            }

            .value {
                font-weight: 400;
                min-height: 1.2em;
            }

            .inline-field .label {
                display: inline;
            }

            .inline-field .value {
                display: inline;
                margin-left: 4px;
            }

            .col-1-3 {
                width: 33.33%;
            }

            .col-2-3 {
                width: 66.67%;
            }

            .col-label {
                width: 28%;
            }

            .col-value {
                width: 72%;
            }

            .grey-bar {
                background: #d9d9d9;
                height: 14px;
                border-left: none;
                border-right: none;
                padding: 0 !important;
            }

            .meeting-label {
                font-weight: 600;
                width: 28%;
            }

            .consumption-head {
                background: #d9d9d9;
                font-weight: 600;
                text-align: center;
            }

            .consumption-head td {
                background: #d9d9d9;
            }

            .check-col {
                width: 8%;
                text-align: center;
                vertical-align: middle !important;
            }

            .type-col {
                width: 28%;
                font-weight: 600;
                vertical-align: middle !important;
            }

            .desc-col {
                width: 64%;
                min-height: 28px;
            }

            .tick {
                font-size: 16px;
                font-weight: 700;
            }

            .sign-head {
                text-align: center;
                font-weight: 700;
                padding: 8px !important;
            }

            .sign-body {
                min-height: 150px;
                text-align: center;
                vertical-align: top !important;
                padding: 12px 10px 16px !important;
            }

            .signature-approval-meta {
                margin-top: 8px;
                line-height: 1.35;
            }

            .signature-approval-status-row {
                margin-bottom: 6px;
            }

            .signature-approval-date {
                font-size: 9pt;
                color: #555;
                margin-bottom: 8px;
            }

            .signature-approver-name {
                font-size: 10pt;
                font-weight: 600;
                color: #222;
                margin-bottom: 4px;
            }

            .signature-approver-position {
                font-size: 9pt;
                color: #444;
                margin-bottom: 0;
            }

            /* Approval status pills — same as officialtravels/print */
            .approval-status {
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
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                color-adjust: exact !important;
            }

            .approval-status.pending {
                background: #fff3cd !important;
                color: #856404 !important;
                border-color: #e6cf7a !important;
            }

            .approval-status.approved {
                background: #d4edda !important;
                color: #155724 !important;
                border-color: #8fce9c !important;
            }

            .approval-status.reject {
                background: #f8d7da !important;
                color: #721c24 !important;
                border-color: #e4a8ad !important;
            }

            .approval-status.fr-doc-cancelled {
                background: #fdebd0 !important;
                color: #533f03 !important;
                border-color: #e8c98f !important;
            }

            .approval-status.fr-doc-submitted {
                background: #fff8e6 !important;
                color: #856404 !important;
                border-color: #f0d77a !important;
            }

            @media print {
                body {
                    margin: 0;
                }

                .form-wrap {
                    border-width: 1.5px;
                    max-width: none;
                }

                .approval-status,
                .approval-status.pending,
                .approval-status.approved,
                .approval-status.reject,
                .approval-status.fr-doc-cancelled,
                .approval-status.fr-doc-submitted {
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                    color-adjust: exact !important;
                }
            }
        </style>
    </head>

    <body>
        @php
            $itemsByType = $doc->items->keyBy('consumption_type');
            $types = \App\Models\RoomConsumptionRequest::CONSUMPTION_TYPES;
            $approvalPlanStatusLabels = [
                0 => 'Pending',
                1 => 'Approved',
                2 => 'Reject',
                3 => 'Cancelled',
                4 => 'Revised',
            ];
            $plans = $doc->approvalPlans->sortBy([['approval_order', 'asc'], ['id', 'asc']])->values();
            $approver1 = $plans->get(0);
            $approver2 = $plans->get(1);
            $start = $doc->start_time ? \Carbon\Carbon::parse($doc->start_time)->format('H:i') : '';
            $end = $doc->end_time ? \Carbon\Carbon::parse($doc->end_time)->format('H:i') : '';
            $waktu = trim($start . ($start && $end ? ' - ' : '') . $end);
        @endphp

        <div class="form-wrap">
            {{-- Header --}}
            <div class="header">
                <div class="logo-cell">
                    <img src="{{ asset('assets/dist/img/logo.png') }}" alt="ARKA">
                </div>
                <div class="title">Room &amp; Consumption Request Form</div>
            </div>

            {{-- Top info --}}
            <table class="form">
                <tr>
                    <td class="col-1-3 inline-field">
                        <span class="label">Reg. No :</span>
                        <br>
                        <span class="value">{{ $doc->request_number ?: '' }}</span>
                    </td>
                    <td class="col-1-3 inline-field">
                        <span class="label">Nama Ruangan :</span>
                        <br>
                        <span class="value">{{ $doc->meetingRoom->room_name ?? '' }}</span>
                    </td>
                    <td class="col-1-3 inline-field">
                        <span class="label">Lokasi :</span>
                        <br>
                        <span class="value">{{ $doc->project->project_code ?? '' }}</span>
                    </td>
                </tr>
                <tr>
                    <td class="col-1-3 inline-field">
                        <span class="label">Divisi / Departemen :</span>
                        <br>
                        <span class="value">{{ $doc->department->department_name ?? '' }}</span>
                    </td>
                    <td class="col-2-3 inline-field" colspan="2">
                        <span class="label">Fasilitas :</span>
                        <br>
                        <span class="value">{{ $doc->facilities ?: '' }}</span>
                    </td>
                </tr>
                <tr>
                    <td colspan="3" class="grey-bar"></td>
                </tr>
            </table>

            {{-- Meeting details --}}
            <table class="form">
                <tr>
                    <td class="meeting-label">Tanggal Meeting</td>
                    <td class="col-value">{{ $doc->meeting_date?->format('d F Y') }}</td>
                </tr>
                <tr>
                    <td class="meeting-label">Judul Meeting</td>
                    <td class="col-value">{{ $doc->meeting_title }}</td>
                </tr>
                <tr>
                    <td class="meeting-label">Waktu Meeting</td>
                    <td class="col-value">{{ $waktu }}</td>
                </tr>
                <tr>
                    <td class="meeting-label">Jumlah Peserta Meeting</td>
                    <td class="col-value">{{ $doc->attendees_count }}</td>
                </tr>
            </table>

            {{-- Consumption --}}
            <table class="form">
                <tr class="consumption-head">
                    <td class="type-col"></td>
                    <td class="check-col">(✓)</td>
                    <td class="desc-col">Deskripsi / Jenis Makanan / Minuman</td>
                </tr>
                @foreach ($types as $type => $label)
                    @php $item = $itemsByType->get($type); @endphp
                    <tr>
                        <td class="type-col">{{ $label }}</td>
                        <td class="check-col">
                            @if ($item && $item->is_selected)
                                <span class="tick">✓</span>
                            @endif
                        </td>
                        <td class="desc-col">{{ $item->description ?? '' }}</td>
                    </tr>
                @endforeach
            </table>

            {{-- Signatures (LOT-style status badges) --}}
            <table class="form">
                <tr>
                    <td class="sign-head col-1-3">Requested by,</td>
                    <td class="sign-head col-1-3">Approved by,</td>
                    <td class="sign-head col-1-3">Approved by,</td>
                </tr>
                <tr>
                    <td class="sign-body">
                        <div class="signature-approval-meta">
                            @if ($doc->submitted_at)
                                <div class="signature-approval-status-row">
                                    <span class="approval-status approved">Submitted</span>
                                </div>
                                <div class="signature-approval-date">{{ $doc->submitted_at->format('d/m/Y') }}</div>
                            @elseif ($doc->status === 'draft')
                                <div class="signature-approval-status-row">
                                    <span class="approval-status pending">Draft</span>
                                </div>
                                <div class="signature-approval-date">—</div>
                            @else
                                <div class="signature-approval-status-row">
                                    <span class="approval-status fr-doc-submitted">{{ ucfirst($doc->status) }}</span>
                                </div>
                                <div class="signature-approval-date">
                                    {{ optional($doc->updated_at)->format('d/m/Y') ?: '—' }}
                                </div>
                            @endif
                            <div class="signature-approver-name">{{ $doc->requestedBy->name ?? '—' }}</div>
                        </div>
                    </td>
                    @foreach ([$approver1, $approver2] as $plan)
                        <td class="sign-body">
                            <div class="signature-approval-meta">
                                @if ($plan)
                                    @php
                                        $st = (int) ($plan->status ?? 0);
                                        $statusText = $approvalPlanStatusLabels[$st] ?? 'Pending';
                                        $statusClass = match ($st) {
                                            1 => 'approved',
                                            2 => 'reject',
                                            3 => 'fr-doc-cancelled',
                                            4 => 'fr-doc-submitted',
                                            default => 'pending',
                                        };
                                        $planTs = $plan->decisionAt() ?? $plan->created_at;
                                        $position = $plan->approver?->administration?->position?->position_name;
                                    @endphp
                                    <div class="signature-approval-status-row">
                                        <span class="approval-status {{ $statusClass }}">{{ $statusText }}</span>
                                    </div>
                                    <div class="signature-approval-date">
                                        {{ $st !== 0 && $planTs ? $planTs->format('d/m/Y') : '—' }}
                                    </div>
                                    <div class="signature-approver-name">{{ $plan->approver->name ?? '—' }}</div>
                                    <div class="signature-approver-position">{{ $position ?: '—' }}</div>
                                @else
                                    <div class="signature-approval-status-row">
                                        <span class="approval-status pending">Pending</span>
                                    </div>
                                    <div class="signature-approval-date">—</div>
                                    <div class="signature-approver-name">—</div>
                                    <div class="signature-approver-position">—</div>
                                @endif
                            </div>
                        </td>
                    @endforeach
                </tr>
            </table>
        </div>
    </body>

</html>
