<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <title>Form of Assignment — {{ $doc->form_number }}</title>
    <style>
        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 12px;
            color: #000;
            margin: 16px;
            background: #fff;
        }

        .form-wrap {
            border: 1.5px solid #000;
            max-width: 920px;
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
            padding: 10px 14px;
            min-width: 140px;
        }

        .logo-cell img {
            max-height: 48px;
            width: auto;
            display: block;
        }

        .title-cell {
            flex: 1;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 10px 16px;
            text-align: center;
        }

        .title-cell .title {
            font-size: 18px;
            font-weight: 700;
            color: #444;
            letter-spacing: 1px;
            text-transform: uppercase;
            margin: 0;
        }

        .title-cell .subtitle {
            margin-top: 4px;
            font-size: 11px;
            color: #666;
        }

        .no-cell {
            border-left: 1.5px solid #000;
            padding: 8px 14px;
            min-width: 160px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 4px;
        }

        .no-cell .no-label {
            font-size: 11px;
            font-weight: 700;
            color: #555;
        }

        .no-cell .no-value {
            font-size: 16px;
            font-weight: 700;
            letter-spacing: 0.3px;
        }

        .no-cell .letter-meta {
            font-size: 10px;
            color: #555;
        }

        table.form {
            width: 100%;
            border-collapse: collapse;
        }

        table.form td,
        table.form th {
            border: 1px solid #000;
            padding: 7px 10px;
            vertical-align: top;
        }

        .section-head {
            background: #efefef;
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            padding: 6px 10px !important;
        }

        .label {
            font-weight: 700;
            white-space: nowrap;
            width: 150px;
            background: #fafafa;
        }

        .value {
            min-height: 1.15em;
        }

        .muted {
            color: #666;
            font-size: 10px;
            font-style: italic;
        }

        .badge-manual {
            display: inline-block;
            border: 1px solid #999;
            border-radius: 2px;
            padding: 0 4px;
            font-size: 9px;
            color: #555;
            margin-left: 4px;
            font-style: normal;
            vertical-align: middle;
        }

        /* Passengers */
        table.passengers {
            width: 100%;
            border-collapse: collapse;
        }

        table.passengers th {
            background: #f3f3f3;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            text-align: left;
        }

        table.passengers td,
        table.passengers th {
            border: 1px solid #000;
            padding: 5px 8px;
            vertical-align: top;
        }

        table.passengers .col-no {
            width: 36px;
            text-align: center;
        }

        table.passengers .col-nik {
            width: 110px;
        }

        table.passengers .col-pos {
            width: 22%;
        }

        table.passengers .col-dept {
            width: 22%;
        }

        /* Trip log — match detail columns */
        table.stops {
            width: 100%;
            border-collapse: collapse;
            table-layout: fixed;
        }

        table.stops th {
            background: #f3f3f3;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            text-align: left;
        }

        table.stops td,
        table.stops th {
            border: 1px solid #000;
            padding: 6px 8px;
            vertical-align: middle;
        }

        table.stops .col-leg {
            width: 22%;
        }

        table.stops .col-dest {
            width: 30%;
        }

        table.stops .col-time {
            width: 14%;
            text-align: center;
        }

        table.stops .col-km {
            width: 10%;
            text-align: right;
        }

        table.stops td.col-time,
        table.stops td.col-km {
            font-variant-numeric: tabular-nums;
        }

        table.stops .empty-cell {
            height: 28px;
        }

        .sign-row td {
            height: 110px;
            vertical-align: top;
            text-align: center;
            width: 33.33%;
        }

        .sign-title {
            font-weight: 700;
            margin-bottom: 6px;
            line-height: 1.35;
        }

        .sign-space {
            height: 58px;
        }

        .sign-line {
            border-top: 1px solid #000;
            width: 75%;
            margin: 0 auto 6px;
        }

        .sign-name {
            font-size: 11px;
            font-weight: 600;
        }

        .sign-role {
            font-size: 10px;
            color: #555;
        }

        .footer {
            border-top: 1.5px solid #000;
            padding: 6px 10px;
            font-size: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .status-chip {
            display: inline-block;
            border: 1px solid #000;
            padding: 1px 8px;
            font-size: 10px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .toolbar {
            max-width: 920px;
            margin: 0 auto 12px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 8px;
        }

        .toolbar .meta {
            font-size: 12px;
            color: #444;
        }

        .toolbar button {
            padding: 6px 12px;
            cursor: pointer;
        }

        @media print {
            body {
                margin: 0;
            }

            .no-print {
                display: none !important;
            }

            .form-wrap {
                border-color: #000;
                max-width: none;
            }
        }
    </style>
</head>

<body>
    @php
    $legs = $doc->tripLegs();
    $minBlankLegs = max(0, 3 - $legs->count());
    $destinations = $doc->stops
    ->where('stop_type', \App\Models\VehicleAssignmentStop::TYPE_DESTINATION)
    ->pluck('destination')
    ->filter()
    ->values();
    @endphp

    <div class="toolbar no-print">
        <div class="meta">
            <strong>{{ $doc->form_number }}</strong>
            · {{ $doc->statusLabel() }}
            · {{ $doc->assignment_date?->locale('id')->translatedFormat('d M Y') }}
        </div>
        <div>
            <button type="button" onclick="window.print()">Print</button>
            <button type="button" onclick="window.close()">Close</button>
        </div>
    </div>

    <div class="form-wrap">
        <div class="header">
            <div class="logo-cell">
                <img src="{{ asset('assets/dist/img/logo.png') }}" alt="ARKA"
                    onerror="this.style.display='none';this.nextElementSibling.style.display='block';">
                <strong style="display:none;color:#c0392b;font-size:22px;letter-spacing:2px">ARKA</strong>
            </div>
            <div class="title-cell">
                <p class="title">Form of Assignment</p>
            </div>
            <div class="no-cell">
                <div class="no-label">FOA No.</div>
                <div class="no-value">{{ $doc->form_number }}</div>
                @if ($doc->letter_number && $doc->letter_number !== $doc->form_number)
                <div class="letter-meta">Letter: {{ $doc->letter_number }}</div>
                @endif
                <div class="letter-meta">
                    <span class="status-chip">{{ $doc->statusLabel() }}</span>
                </div>
            </div>
        </div>

        <table class="form">
            <tr>
                <td colspan="4" class="section-head">Assignment Information</td>
            </tr>
            <tr>
                <td class="label">Hari / Tanggal</td>
                <td class="value" colspan="3">
                    {{ $doc->assignment_date
                        ? $doc->assignment_date->locale('id')->translatedFormat('l, d F Y')
                        : '—' }}
                </td>
            </tr>
            <tr>
                <td class="label">Nama (Driver)</td>
                <td class="value" colspan="3">{{ $doc->driver_name ?: '—' }}</td>
            </tr>
            <tr>
                <td class="label">Origin</td>
                <td class="value" colspan="3">
                    {{ $doc->origin_destination ?: '—' }}
                    @if ($doc->origin_is_manual)
                    <span class="badge-manual">Manual</span>
                    @endif
                </td>
            </tr>
            <tr>
                <td class="label">Tujuan</td>
                <td class="value" colspan="3">
                    @if ($destinations->isEmpty())
                    —
                    @else
                    {{ $destinations->implode(' → ') }}
                    @endif
                </td>
            </tr>
            <tr>
                <td class="label">Keterangan</td>
                <td class="value" colspan="3">{{ $doc->remarks ?: '—' }}</td>
            </tr>
            <tr>
                <td class="label">Kendaraan</td>
                <td class="value" colspan="3">
                    {{ $doc->license_plate }}
                    @if ($doc->vehicle_kode)
                    ({{ $doc->vehicle_kode }})
                    @endif
                </td>
            </tr>
            <tr>
                <td class="label">Requestor</td>
                <td class="value" colspan="3">{{ $doc->requestor?->name ?: '—' }}</td>
            </tr>
        </table>

        <table class="passengers">
            <tr>
                <td colspan="5" class="section-head">Passengers / Pengikut</td>
            </tr>
            <tr>
                <th class="col-no">No</th>
                <th>Name</th>
                <th class="col-nik">NIK</th>
                <th class="col-pos">Position</th>
                <th class="col-dept">Department</th>
            </tr>
            @forelse ($doc->passengers as $i => $p)
            @php
            $admin = $p->employee?->activeAdministration;
            $position = $admin?->position;
            $department = $position?->department;
            @endphp
            <tr>
                <td class="col-no">{{ $i + 1 }}</td>
                <td>
                    {{ $p->passenger_name ?: ($p->employee?->fullname ?? '—') }}
                    @if (! $p->employee_id)
                    <span class="badge-manual">External</span>
                    @endif
                </td>
                <td class="col-nik">{{ $admin?->nik ?: '—' }}</td>
                <td class="col-pos">{{ $position?->position_name ?: '—' }}</td>
                <td class="col-dept">{{ $department?->department_name ?: '—' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="5" style="text-align:center;color:#777">No passengers</td>
            </tr>
            @endforelse
        </table>

        <table class="stops">
            <tr>
                <td colspan="6" class="section-head">Trip Log — Jam Berangkat / Tiba</td>
            </tr>
            <tr>
                <th class="col-leg">Leg</th>
                <th class="col-dest">Tujuan</th>
                <th class="col-time">Berangkat</th>
                <th class="col-km">KM</th>
                <th class="col-time">Tiba</th>
                <th class="col-km">KM</th>
            </tr>
            @foreach ($legs as $stop)
            <tr>
                <td class="col-leg"><strong>{{ $stop->legLabel() }}</strong></td>
                <td class="col-dest">
                    {{ $stop->destination }}
                    @if ($stop->is_manual)
                    <span class="badge-manual">Manual</span>
                    @endif
                </td>
                <td class="col-time">{{ $stop->depart_time ? substr((string) $stop->depart_time, 0, 5) : '' }}</td>
                <td class="col-km">{{ $stop->depart_km !== null ? number_format($stop->depart_km) : '' }}</td>
                <td class="col-time">{{ $stop->arrive_time ? substr((string) $stop->arrive_time, 0, 5) : '' }}</td>
                <td class="col-km">{{ $stop->arrive_km !== null ? number_format($stop->arrive_km) : '' }}</td>
            </tr>
            @endforeach
            @for ($i = 0; $i < $minBlankLegs; $i++)
                <tr>
                <td class="col-leg empty-cell"></td>
                <td class="col-dest empty-cell"></td>
                <td class="col-time empty-cell"></td>
                <td class="col-km empty-cell"></td>
                <td class="col-time empty-cell"></td>
                <td class="col-km empty-cell"></td>
                </tr>
                @endfor
                @if ($legs->isEmpty() && $minBlankLegs === 0)
                <tr>
                    <td colspan="6" style="text-align:center;color:#777">No destinations yet</td>
                </tr>
                @endif
        </table>

        <table class="form">
            <tr>
                <td colspan="3" class="section-head">Acknowledgement</td>
            </tr>
            <tr class="sign-row">
                <td>
                    <div class="sign-title">Dibuat,<br>Keberangkatan</div>
                    <div class="sign-space"></div>
                    <div class="sign-line"></div>
                    <div class="sign-name">{{ $doc->requestor?->name ?: '________________' }}</div>
                    <div class="sign-role">Requestor</div>
                </td>
                <td>
                    <div class="sign-title">Diketahui,<br>Tiba di Tujuan</div>
                    <div class="sign-space"></div>
                    <div class="sign-line"></div>
                    <div class="sign-name">________________</div>
                    <div class="sign-role">At destination</div>
                </td>
                <td>
                    <div class="sign-title">Diketahui / Disetujui,<br>Kedatangan Tujuan</div>
                    <div class="sign-space"></div>
                    <div class="sign-line"></div>
                    <div class="sign-name">{{ '________________' }}</div>
                    <div class="sign-role"></div>
                </td>
            </tr>
        </table>

        <div class="footer">
            <span>ARKA/HCS/IV/04.02</span>
            <span>Rev. 2</span>
            <span>Page 1 / 1</span>
        </div>
    </div>
</body>

</html>