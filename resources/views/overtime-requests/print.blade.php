<!DOCTYPE html>
<html lang="id">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Surat Perintah Lembur — {{ $overtimeRequest->register_number ?? 'Draft' }}</title>
        <style>
            @page {
                size: A4 portrait;
                margin: 12mm 14mm;
            }

            * {
                margin: 0;
                padding: 0;
                box-sizing: border-box;
            }

            body {
                font-family: Arial, Helvetica, sans-serif;
                font-size: 11pt;
                color: #000;
                background: #fff;
                padding: 12px;
            }

            .print-wrap {
                max-width: 210mm;
                margin: 0 auto;
            }

            .no-print {
                text-align: center;
                margin-bottom: 14px;
            }

            .no-print button,
            .no-print a {
                display: inline-block;
                padding: 8px 18px;
                font-size: 11pt;
                cursor: pointer;
                margin: 0 4px;
                text-decoration: none;
                border: 1px solid #333;
                background: #f5f5f5;
                color: #000;
                border-radius: 3px;
            }

            .header {
                display: flex;
                align-items: center;
                margin-bottom: 18px;
                gap: 12px;
            }

            .logo-box {
                flex-shrink: 0;
                width: 88px;
            }

            .logo-box img {
                max-width: 88px;
                max-height: 42px;
                width: auto;
                height: auto;
                display: block;
            }

            .logo-fallback {
                background: #e67e22;
                color: #fff;
                font-weight: 700;
                font-size: 16pt;
                letter-spacing: 1px;
                text-align: center;
                padding: 10px 14px;
                border-radius: 2px;
            }

            .doc-title {
                flex: 1;
                text-align: center;
                font-size: 16pt;
                font-weight: 700;
                text-transform: uppercase;
                letter-spacing: 1px;
                padding-right: 88px;
            }

            .meta-fields {
                margin-bottom: 14px;
                font-size: 11pt;
            }

            .meta-fields .row {
                display: flex;
                margin-bottom: 6px;
                align-items: baseline;
            }

            .meta-fields .label {
                width: 120px;
                flex-shrink: 0;
                font-weight: 600;
            }

            .meta-fields .colon {
                width: 16px;
                flex-shrink: 0;
            }

            .meta-fields .value {
                flex: 1;
                border-bottom: 1px solid #000;
                min-height: 1.2em;
                padding: 0 4px 2px;
            }

            table.ot-table {
                width: 100%;
                border-collapse: collapse;
                border: 1.5px solid #000;
                margin-bottom: 22px;
                table-layout: fixed;
            }

            table.ot-table th,
            table.ot-table td {
                border-left: 1px solid #000;
                border-right: 1px solid #000;
                padding: 6px 5px;
                vertical-align: middle;
                font-size: 10pt;
            }

            table.ot-table th {
                border-bottom: 1.5px solid #000;
                border-top: none;
                text-align: center;
                font-weight: 700;
                text-transform: uppercase;
                background: #fff;
            }

            table.ot-table td {
                border-bottom: 1px dotted #000;
                height: 28px;
            }

            table.ot-table tbody tr:last-child td {
                border-bottom: none;
            }

            table.ot-table .col-no {
                width: 36px;
                text-align: center;
            }

            table.ot-table .col-nama {
                width: 18%;
            }

            table.ot-table .col-nik {
                width: 10%;
                text-align: center;
            }

            table.ot-table .col-jabatan {
                width: 16%;
            }

            table.ot-table .col-in,
            table.ot-table .col-out {
                width: 8%;
                text-align: center;
            }

            table.ot-table .col-uraian {
                width: auto;
            }

            .sign-section {
                margin-bottom: 28px;
                margin-top: 8px;
            }

            .sign-columns {
                display: flex;
                justify-content: space-between;
                gap: 16px;
                align-items: flex-start;
            }

            .sign-left {
                width: 34%;
            }

            .sign-right {
                width: 62%;
            }

            .sign-column-title {
                font-weight: 700;
                margin-bottom: 10px;
                font-size: 11pt;
            }

            .sign-right .sign-column-title {
                text-align: center;
            }

            .approval-block {
                text-align: center;
            }

            .sign-line {
                border-bottom: 1px solid #000;
                height: 48px;
                margin-bottom: 0;
            }

            .sign-cell-body {
                padding-top: 6px;
            }

            .approval-label {
                display: block;
                font-weight: 600;
                text-transform: uppercase;
                font-size: 9pt;
                color: #444;
                letter-spacing: 0.2px;
                line-height: 1.3;
                margin-bottom: 4px;
            }

            .sign-role-hint {
                font-size: 9.5pt;
                font-weight: 600;
                margin-bottom: 6px;
                color: #222;
            }

            .sign-person-name {
                display: inline-block;
                min-width: 70%;
                max-width: 100%;
                border-bottom: 1px solid #ccc;
                padding: 2px 4px 3px;
                font-size: 9.5pt;
                color: #222;
                margin-bottom: 8px;
            }

            .approval-status-wrap {
                margin-top: 2px;
            }

            .approval-status {
                display: inline-block;
                font-weight: 600;
                font-size: 8.5pt;
                letter-spacing: 0.02em;
                text-transform: capitalize;
                text-align: center;
                line-height: 1.25;
                padding: 4px 8px;
                border-radius: 4px;
                border: 1px solid transparent;
                white-space: nowrap;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .approval-status.pending {
                background: #fff3cd;
                color: #856404;
                border-color: #e6cf7a;
            }

            .approval-status.approved {
                background: #d4edda;
                color: #155724;
                border-color: #8fce9c;
            }

            .approval-status.reject {
                background: #f8d7da;
                color: #721c24;
                border-color: #e4a8ad;
            }

            .approval-status.ot-doc-draft {
                background: #e9ecef;
                color: #343a40;
                border-color: #ced4da;
            }

            .approval-status.ot-doc-pending {
                background: #fff8e6;
                color: #856404;
                border-color: #e6cf7a;
            }

            .approval-status.ot-doc-approved {
                background: #d4edda;
                color: #155724;
                border-color: #8fce9c;
            }

            .approval-status.ot-doc-finished {
                background: #d1ecf1;
                color: #0c5460;
                border-color: #abdde5;
            }

            .sign-right .approval-blocks {
                display: flex;
                gap: 12px;
            }

            .sign-right .approval-block {
                flex: 1;
            }

            .distribusi {
                font-size: 10pt;
                margin-bottom: 24px;
            }

            .distribusi .title {
                font-weight: 700;
                margin-bottom: 4px;
            }

            .distribusi ol {
                margin-left: 22px;
                padding: 0;
            }

            .distribusi li {
                margin-bottom: 2px;
            }

            .doc-footer {
                display: flex;
                justify-content: space-between;
                align-items: flex-end;
                font-size: 9pt;
                margin-top: 12px;
                padding-top: 4px;
            }

            .doc-footer .code {
                font-weight: 600;
            }

            .doc-footer .rev,
            .doc-footer .page {
                font-style: italic;
            }

            .register-hint {
                text-align: right;
                font-size: 9pt;
                color: #555;
                margin-bottom: 6px;
            }

            @media print {
                body {
                    padding: 0;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }

                .no-print {
                    display: none !important;
                }
            }
        </style>
    </head>

    <body>
        @php
            $details = $overtimeRequest->details ?? collect();
            $minRows = 6;
            $rowCount = max($minRows, $details->count());

            $hariTanggal = $overtimeRequest->overtime_date
                ? \Carbon\Carbon::parse($overtimeRequest->overtime_date)->locale('id')->translatedFormat('l / d F Y')
                : '';

            $projectLabel = $overtimeRequest->project->project_name ?? '';
            if ($overtimeRequest->project?->project_code) {
                $projectLabel = trim($overtimeRequest->project->project_code . ' — ' . $projectLabel);
            }

            $creatorName = $overtimeRequest->requestedBy->name ?? '—';

            $approvalStatusLabels = [
                0 => 'Pending',
                1 => 'Approved',
                2 => 'Reject',
            ];

            $otStatusKey = (string) ($overtimeRequest->status ?? '');
            $otStatusLabel = match ($otStatusKey) {
                'draft' => 'Draft',
                'pending' => 'Pending',
                'approved' => 'Approved',
                'rejected' => 'Rejected',
                'finished' => 'Finished',
                default => ucfirst($otStatusKey !== '' ? $otStatusKey : '—'),
            };
            $otDocStatusClass = match ($otStatusKey) {
                'draft' => 'ot-doc-draft',
                'pending' => 'ot-doc-pending',
                'approved' => 'ot-doc-approved',
                'rejected' => 'reject',
                'finished' => 'ot-doc-finished',
                default => 'pending',
            };

            $sortedApprovalPlans = ($overtimeRequest->approvalPlans ?? collect())
                ->sortBy(function ($plan) {
                    return [$plan->approval_order ?? 999999, $plan->id];
                })
                ->values();

            $manualApproverIds = is_array($overtimeRequest->manual_approvers)
                ? array_values(array_filter($overtimeRequest->manual_approvers))
                : [];

            $approverSlots = [];
            $slotLabels = ['Departement Head', 'Project Manager'];

            for ($slot = 0; $slot < 2; $slot++) {
                $plan = $sortedApprovalPlans->get($slot);
                $name = '—';
                $st = 0;
                $statusText = 'Pending';
                $statusClass = 'pending';

                if ($plan) {
                    $name = $plan->approver->name ?? '—';
                    $st = (int) ($plan->status ?? 0);
                    $statusText = $approvalStatusLabels[$st] ?? 'Pending';
                    $statusClass = match ($st) {
                        1 => 'approved',
                        2 => 'reject',
                        default => 'pending',
                    };
                } elseif (isset($manualApproverIds[$slot])) {
                    $approverUser = \App\Models\User::find($manualApproverIds[$slot]);
                    $name = $approverUser->name ?? '—';
                }

                $approverSlots[] = [
                    'role' => $slotLabels[$slot],
                    'approval_label' => $slot === 0 ? 'Acknowledged By' : 'Approved By',
                    'name' => $name,
                    'status_text' => $statusText,
                    'status_class' => $statusClass,
                ];
            }

            $logoUrl = asset('images/logo_2.jpg');
            $logoFallback = asset('assets/dist/img/logo.png');
        @endphp

        <div class="print-wrap">
            <div class="no-print">
                <button type="button" onclick="window.print()">Cetak / Print</button>
                <a href="javascript:window.close()">Tutup</a>
            </div>

            @if ($overtimeRequest->register_number)
                <div class="register-hint">{{ $overtimeRequest->register_number }}</div>
            @endif

            <div class="header">
                <div class="logo-box">
                    <img src="{{ $logoUrl }}" alt="ARKA"
                        onerror="this.onerror=null;this.src='{{ $logoFallback }}';this.onerror=function(){this.style.display='none';this.nextElementSibling.style.display='block';};">
                    <div class="logo-fallback" style="display:none;">ARKA</div>
                </div>
                <div class="doc-title">Surat Perintah Lembur</div>
            </div>

            <div class="meta-fields">
                <div class="row">
                    <div class="label">Project</div>
                    <div class="colon">:</div>
                    <div class="value">{{ $projectLabel }}</div>
                </div>
                <div class="row">
                    <div class="label">Hari / Tanggal</div>
                    <div class="colon">:</div>
                    <div class="value">{{ $hariTanggal }}</div>
                </div>
            </div>

            <table class="ot-table">
                <thead>
                    <tr>
                        <th class="col-no">No</th>
                        <th class="col-nama">Nama</th>
                        <th class="col-nik">NIK</th>
                        <th class="col-jabatan">Jabatan</th>
                        <th class="col-in">IN</th>
                        <th class="col-out">OUT</th>
                        <th class="col-uraian">Uraian Pekerjaan</th>
                    </tr>
                </thead>
                <tbody>
                    @for ($i = 0; $i < $rowCount; $i++)
                        @php
                            $line = $details->get($i);
                            $admin = $line?->administration;
                            $name = $admin?->employee?->fullname ?? '';
                            $nik = $admin?->nik ?? '';
                            $jabatan = $admin?->position?->position_name ?? '';
                            $timeIn = $line?->time_in ? \Carbon\Carbon::parse($line->time_in)->format('H:i') : '';
                            $timeOut = $line?->time_out ? \Carbon\Carbon::parse($line->time_out)->format('H:i') : '';
                            $uraian = $line?->work_description ?? '';
                        @endphp
                        <tr>
                            <td class="col-no">{{ $i + 1 }}</td>
                            <td class="col-nama">{{ $name }}</td>
                            <td class="col-nik">{{ $nik }}</td>
                            <td class="col-jabatan">{{ $jabatan }}</td>
                            <td class="col-in">{{ $timeIn }}</td>
                            <td class="col-out">{{ $timeOut }}</td>
                            <td class="col-uraian">{{ $uraian }}</td>
                        </tr>
                    @endfor
                </tbody>
            </table>

            <div class="sign-section">
                <div class="sign-columns">
                    <div class="sign-left">
                        <div class="sign-column-title">Dibuat oleh,</div>
                        <div class="approval-block">
                            <div class="approval-status-wrap">
                                <span
                                    class="approval-status ot-doc-status {{ $otDocStatusClass }}">{{ $otStatusLabel }}</span>
                            </div>
                            <div class="sign-line"></div>
                            <div class="sign-cell-body">
                                <div class="sign-person-name">{{ $creatorName }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="sign-right">
                        <div class="sign-column-title">Disetujui Oleh ,</div>
                        <div class="approval-blocks">
                            @foreach ($approverSlots as $slot)
                                <div class="approval-block">
                                    <div class="approval-status-wrap">
                                        <span
                                            class="approval-status {{ $slot['status_class'] }}">{{ $slot['status_text'] }}</span>
                                    </div>
                                    <div class="sign-line"></div>
                                    <div class="sign-cell-body">
                                        <div class="sign-person-name">{{ $slot['name'] }}</div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <div class="distribusi">
                <div class="title">Distribusi :</div>
                <ol>
                    <li>Dept. yang bersangkutan</li>
                    <li>Departemen HR</li>
                    <li>Karyawan</li>
                </ol>
            </div>

            <div class="doc-footer">
                <div class="code">ARKA/HCS/IV/02.03</div>
                <div class="rev">Rev.2</div>
                <div class="page">Page 1/1</div>
            </div>
        </div>
    </body>

</html>
