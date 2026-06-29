<!DOCTYPE html>
<html lang="id">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{{ $leaveRequest->register_number ?? 'Permohonan Cuti' }} - Formulir Cuti</title>
        <style>
            @page {
                margin: 1.5cm;
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
                background: #fff;
                padding: 16px;
            }

            .print-container {
                max-width: 210mm;
                margin: 0 auto;
            }

            .header {
                display: flex;
                align-items: center;
                justify-content: space-between;
                border-bottom: 2px solid #000;
                padding-bottom: 12px;
                margin-bottom: 16px;
            }

            .logo-section img {
                height: 48px;
                width: auto;
            }

            .document-title {
                text-align: center;
                flex: 1;
                padding: 0 16px;
            }

            .document-title h1 {
                font-size: 14pt;
                text-transform: uppercase;
                letter-spacing: 0.5px;
            }

            .document-title .doc-number {
                text-align: center;
                font-weight: bold;
                font-size: 11pt;
                margin-top: 6px;
            }

            .section-title {
                font-size: 10pt;
                font-weight: bold;
                text-transform: uppercase;
                background: #f0f0f0;
                border: 1px solid #000;
                border-bottom: none;
                padding: 6px 10px;
            }

            .section-body {
                border: 1px solid #000;
                padding: 10px 12px;
                margin-bottom: 14px;
            }

            .info-row {
                display: flex;
                margin-bottom: 4px;
            }

            .info-row .label {
                width: 170px;
                font-weight: 600;
                flex-shrink: 0;
            }

            .info-row .colon {
                width: 14px;
                flex-shrink: 0;
            }

            .info-row .value {
                flex: 1;
            }

            .reason-box {
                border: 1px solid #ccc;
                min-height: 48px;
                padding: 8px 10px;
                margin-top: 4px;
                background: #fafafa;
            }

            .approval-table {
                width: 100%;
                border-collapse: collapse;
                margin-top: 4px;
            }

            .approval-table th,
            .approval-table td {
                border: 1px solid #000;
                padding: 8px 10px;
                vertical-align: middle;
            }

            .approval-table th {
                background: #f0f0f0;
                font-size: 10pt;
                text-align: center;
            }

            .approval-table td.col-no {
                width: 40px;
                text-align: center;
            }

            .approval-table td.col-role {
                width: 28%;
            }

            .approval-table td.col-name {
                width: 32%;
            }

            .approval-table td.col-date {
                width: 18%;
                text-align: center;
            }

            .approval-table td.col-ttd {
                width: 18%;
                text-align: center;
                font-weight: bold;
            }

            .status-approved {
                color: #155724;
            }

            .status-reject {
                color: #721c24;
            }

            .status-pending {
                color: #856404;
            }

            .footer {
                display: flex;
                justify-content: space-between;
                margin-top: 20px;
                font-size: 9pt;
                color: #555;
            }

            .no-print {
                text-align: center;
                margin-bottom: 16px;
            }

            .no-print button {
                padding: 8px 20px;
                font-size: 11pt;
                cursor: pointer;
            }

            @media print {
                body {
                    padding: 0;
                    -webkit-print-color-adjust: exact;
                    print-color-adjust: exact;
                }

                .no-print {
                    display: none;
                }
            }
        </style>
    </head>

    <body>
        @php
            $employee = $leaveRequest->employee;
            $administration =
                $leaveRequest->administration ??
                ($employee ? $employee->activeAdministration : null) ??
                ($employee ? $employee->administrations->where('is_active', 1)->first() : null);

            $name = $employee->fullname ?? '—';
            $nik = $administration->nik ?? ($employee->nik ?? '—');
            $position = $administration?->position?->position_name ?? '—';
            $department = $administration?->position?->department?->department_name ?? '—';
            $project = $administration?->project?->project_name ?? '—';
            $projectCode = $administration?->project?->project_code;
            $projectLabel = $projectCode ? "{$projectCode} - {$project}" : $project;

            $formatDateId = function ($value) {
                if ($value === null || $value === '') {
                    return '—';
                }

                try {
                    return \Carbon\Carbon::parse($value)->locale('id')->translatedFormat('d F Y');
                } catch (\Throwable) {
                    return (string) $value;
                }
            };

            $formatLeavePeriodId = function (?string $period) use ($formatDateId) {
                if (! $period) {
                    return null;
                }

                $parts = preg_split('/\s*-\s*/', trim($period), 2);
                if (count($parts) !== 2) {
                    return $period;
                }

                try {
                    $start = $formatDateId(trim($parts[0]));
                    $end = $formatDateId(trim($parts[1]));

                    return "{$start} - {$end}";
                } catch (\Throwable) {
                    return $period;
                }
            };

            $doh = $administration?->doh ? $formatDateId($administration->doh) : '—';

            $leaveTypeName = $leaveRequest->leaveType->name ?? '—';
            $leaveCategory = strtolower($leaveRequest->leaveType->category ?? '');

            $requestStatusLabels = [
                'draft' => 'Draf',
                'pending' => 'Menunggu',
                'approved' => 'Disetujui',
                'rejected' => 'Ditolak',
                'cancelled' => 'Dibatalkan',
                'closed' => 'Ditutup',
                'auto_approved' => 'Disetujui Otomatis',
            ];
            $requestStatus = $requestStatusLabels[$leaveRequest->status] ?? ucfirst($leaveRequest->status ?? '—');

            $sortedApprovalPlans = $leaveRequest->approvalPlans
                ->sortBy(fn($plan) => [$plan->approval_order ?? 999999, $plan->id])
                ->values();

            $approvalStatusLabels = [
                0 => 'Menunggu',
                1 => 'Disetujui',
                2 => 'Ditolak',
                3 => 'Dibatalkan',
                4 => 'Direvisi',
            ];

            $requestedByName = $leaveRequest->requestedBy->name ?? $name;
            $requestedAt = $leaveRequest->requested_at
                ? $formatDateId($leaveRequest->requested_at)
                : ($leaveRequest->created_at ? $formatDateId($leaveRequest->created_at) : '—');
        @endphp

        <div class="no-print">
            <button type="button" onclick="window.print()">Cetak</button>
        </div>

        <div class="print-container">
            <div class="header">
                <div class="logo-section">
                    <img src="{{ asset('images/logo_2.jpg') }}" alt="ARKA">
                </div>
                <div class="document-title">
                    <h1>Formulir Permohonan Cuti</h1>
                    <div class="doc-number">No. {{ $leaveRequest->register_number ?? '—' }}</div>
                </div>
            </div>

            <div class="section-title">Data Karyawan</div>
            <div class="section-body">
                <div class="info-row">
                    <span class="label">Nama</span>
                    <span class="colon">:</span>
                    <span class="value">{{ $name }}</span>
                </div>
                <div class="info-row">
                    <span class="label">NIK</span>
                    <span class="colon">:</span>
                    <span class="value">{{ $nik }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Jabatan</span>
                    <span class="colon">:</span>
                    <span class="value">{{ $position }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Departemen</span>
                    <span class="colon">:</span>
                    <span class="value">{{ $department }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Proyek</span>
                    <span class="colon">:</span>
                    <span class="value">{{ $projectLabel }}</span>
                </div>
                <div class="info-row">
                    <span class="label">Tanggal Masuk Kerja</span>
                    <span class="colon">:</span>
                    <span class="value">{{ $doh }}</span>
                </div>
            </div>

            <div class="section-title">Detail Cuti</div>
            <div class="section-body">
                <div class="info-row">
                    <span class="label">Jenis Cuti</span>
                    <span class="colon">:</span>
                    <span class="value">{{ $leaveTypeName }}</span>
                </div>
                @if ($leaveRequest->leave_period)
                    <div class="info-row">
                        <span class="label">Periode Hak Cuti</span>
                        <span class="colon">:</span>
                        <span class="value">{{ $formatLeavePeriodId($leaveRequest->leave_period) }}</span>
                    </div>
                @endif
                <div class="info-row">
                    <span class="label">Tanggal Mulai</span>
                    <span class="colon">:</span>
                    <span class="value">
                        @if ($leaveRequest->isLSLCashoutOnly())
                            —
                        @else
                            {{ $leaveRequest->start_date ? $formatDateId($leaveRequest->start_date) : '—' }}
                        @endif
                    </span>
                </div>
                <div class="info-row">
                    <span class="label">Tanggal Selesai</span>
                    <span class="colon">:</span>
                    <span class="value">
                        @if ($leaveRequest->isLSLCashoutOnly())
                            —
                        @else
                            {{ $leaveRequest->end_date ? $formatDateId($leaveRequest->end_date) : '—' }}
                        @endif
                    </span>
                </div>
                <div class="info-row">
                    <span class="label">Kembali Bekerja</span>
                    <span class="colon">:</span>
                    <span class="value">
                        {{ $leaveRequest->back_to_work_date ? $formatDateId($leaveRequest->back_to_work_date) : '—' }}
                    </span>
                </div>

                @if ($leaveRequest->isLSLFlexible())
                    <div class="info-row">
                        <span class="label">Cuti Diambil</span>
                        <span class="colon">:</span>
                        <span class="value">{{ $leaveRequest->lsl_taken_days ?? 0 }} hari</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Pencairan Cuti</span>
                        <span class="colon">:</span>
                        <span class="value">{{ $leaveRequest->lsl_cashout_days ?? 0 }} hari</span>
                    </div>
                    <div class="info-row">
                        <span class="label">Total Cuti Panjang</span>
                        <span class="colon">:</span>
                        <span class="value">{{ $leaveRequest->getLSLTotalDays() }} hari</span>
                    </div>
                @else
                    <div class="info-row">
                        <span class="label">Total Hari</span>
                        <span class="colon">:</span>
                        <span class="value">
                            {{ $leaveRequest->total_days ?? 0 }} hari
                            @if ($leaveRequest->getTotalCancelledDays() > 0)
                                (Efektif: {{ $leaveRequest->getEffectiveDays() }} hari)
                            @endif
                        </span>
                    </div>
                @endif

                <div class="info-row">
                    <span class="label">Status Pengajuan</span>
                    <span class="colon">:</span>
                    <span class="value">{{ $requestStatus }}</span>
                </div>

                @if ($leaveRequest->reason)
                    <div style="margin-top: 8px;">
                        <strong>Alasan :</strong>
                        <div class="reason-box">{{ $leaveRequest->reason }}</div>
                    </div>
                @endif

                @if ($leaveCategory === 'paid' || $leaveRequest->supporting_document)
                    <div class="info-row" style="margin-top: 8px;">
                        <span class="label">Dokumen Pendukung</span>
                        <span class="colon">:</span>
                        <span class="value">{{ $leaveRequest->supporting_document ? 'Ada' : 'Belum ada' }}</span>
                    </div>
                @endif
            </div>

            <div class="section-title">Persetujuan</div>
            <div class="section-body" style="padding: 0; border: none;">
                <table class="approval-table">
                    <thead>
                        <tr>
                            <th class="col-no">No</th>
                            <th class="col-role">Keterangan</th>
                            <th class="col-name">Nama</th>
                            <th class="col-date">Tanggal</th>
                            <th class="col-ttd">TTD</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="col-no">1</td>
                            <td class="col-role">Diajukan Oleh</td>
                            <td class="col-name">{{ $requestedByName }}</td>
                            <td class="col-date">{{ $requestedAt }}</td>
                            <td class="col-ttd">Diajukan</td>
                        </tr>
                        @forelse ($sortedApprovalPlans as $idx => $plan)
                            @php
                                $planCount = $sortedApprovalPlans->count();
                                if ($planCount === 1) {
                                    $rowLabel = 'Disetujui Oleh';
                                } elseif ($idx === 0) {
                                    $rowLabel = 'Diketahui Oleh';
                                } elseif ($idx === $planCount - 1) {
                                    $rowLabel = 'Disetujui Oleh';
                                } else {
                                    $rowLabel = 'Disetujui Oleh';
                                }

                                $st = (int) ($plan->status ?? 0);
                                $statusText = $approvalStatusLabels[$st] ?? 'Menunggu';
                                $statusClass = match ($st) {
                                    1 => 'status-approved',
                                    2 => 'status-reject',
                                    default => 'status-pending',
                                };
                                $approverName = $plan->approver->name ?? '—';
                                $planTs = $plan->updated_at ?? $plan->created_at;
                                $planDate = $st !== 0 && $planTs ? $formatDateId($planTs) : '—';
                            @endphp
                            <tr>
                                <td class="col-no">{{ $idx + 2 }}</td>
                                <td class="col-role">{{ $rowLabel }}</td>
                                <td class="col-name">{{ $approverName }}</td>
                                <td class="col-date">{{ $planDate }}</td>
                                <td class="col-ttd {{ $statusClass }}">{{ $statusText }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td class="col-no">2</td>
                                <td class="col-role">Disetujui Oleh</td>
                                <td class="col-name">—</td>
                                <td class="col-date">—</td>
                                <td class="col-ttd status-pending">Menunggu</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="footer">
                <div>Dibuat oleh ARKA HERO</div>
                <div>Halaman 1/1</div>
            </div>
        </div>

        <script>
            window.onload = function() {
                window.print();
            };
        </script>
    </body>

</html>
