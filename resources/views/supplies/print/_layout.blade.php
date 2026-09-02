<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>{{ $formTitle }} — {{ $documentNo ?: 'Draft' }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 12mm 14mm;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11pt;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 12px;
        }

        .no-print {
            text-align: center;
            margin-bottom: 14px;
        }

        .no-print button,
        .no-print a {
            display: inline-block;
            margin: 0 6px;
            padding: 8px 18px;
            font-size: 13px;
            cursor: pointer;
            text-decoration: none;
            border: 1px solid #ccc;
            background: #f4f4f4;
            color: #333;
            border-radius: 4px;
        }

        .no-print button.primary {
            background: #007bff;
            border-color: #007bff;
            color: #fff;
        }

        .form-wrap {
            max-width: 210mm;
            margin: 0 auto;
        }

        .header {
            display: flex;
            align-items: center;
            gap: 18px;
            padding: 4px 0 18px;
            min-height: auto;
        }

        .logo-cell {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            flex-shrink: 0;
            padding: 0;
            background: transparent;
        }

        .logo-cell img {
            max-height: 36px;
            width: auto;
            height: auto;
            display: block;
            object-fit: contain;
        }

        .logo-fallback {
            color: #c0392b;
            font-weight: 800;
            font-size: 22px;
            letter-spacing: 2px;
        }

        .title-cell {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 8px;
            font-size: 20px;
            font-weight: 700;
            color: #777;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            text-align: center;
        }

        .meta-block {
            padding: 0 0 12px;
        }

        .meta-line {
            margin-bottom: 10px;
            font-size: 11pt;
            line-height: 1.4;
        }

        .meta-line:last-child {
            margin-bottom: 0;
        }

        .meta-label {
            font-weight: 600;
            display: inline-block;
            min-width: 110px;
        }

        .meta-value {
            display: inline-block;
            min-width: 280px;
            border-bottom: 1px dotted #000;
            padding-bottom: 1px;
        }

        table.lines {
            width: 100%;
            border-collapse: collapse;
        }

        table.lines th,
        table.lines td {
            border: 1px solid #000;
            padding: 6px 8px;
            vertical-align: top;
        }

        table.lines thead th {
            background: #d9d9d9;
            font-weight: 700;
            text-align: center;
        }

        table.lines .col-no {
            width: 6%;
            text-align: center;
        }

        table.lines .col-qty {
            width: 12%;
            text-align: center;
        }

        table.lines .col-remarks {
            width: 22%;
        }

        table.lines tbody td {
            min-height: 28px;
            height: 28px;
        }

        .item-main {
            font-weight: 600;
        }

        .item-sub {
            font-size: 10pt;
            color: #333;
            margin-top: 2px;
        }

        .sign-row {
            display: flex;
            margin-top: 8px;
        }

        .sign-cell {
            flex: 1;
            padding: 10px 8px 16px;
            text-align: center;
            min-height: 120px;
        }

        .sign-cell+.sign-cell {
            border-left: none;
        }

        .sign-label {
            font-weight: 600;
            margin-bottom: 64px;
            text-align: left;
        }

        .sign-name {
            font-weight: 600;
            border-top: 1px solid #000;
            display: inline-block;
            min-width: 200px;
            padding-top: 4px;
        }

        .sign-date {
            font-size: 10pt;
            color: #444;
            margin-top: 4px;
        }

        .footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0 0;
            font-size: 10pt;
        }

        @media print {
            body {
                padding: 0;
            }

            .no-print {
                display: none !important;
            }

            .form-wrap {
                max-width: none;
            }

            table.lines thead th {
                background: #d9d9d9 !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }

            .logo-cell img {
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>

<body>
    @php
    $rows = $rows ?? [];
    $minRows = (int) ($minRows ?? 20);
    $padCount = max(0, $minRows - count($rows));
    @endphp

    <div class="no-print">
        <button type="button" class="primary" onclick="window.print()">Print</button>
        @if (!empty($backUrl))
        <a href="{{ $backUrl }}">Back</a>
        @endif
    </div>

    <div class="form-wrap">
        <div class="header">
            <div class="logo-cell">
                <img src="{{ url('assets/dist/img/logo.png') }}" alt="ARKA">
            </div>
            <div class="title-cell">{{ $formTitle }}</div>
        </div>

        <div class="meta-block">
            @foreach ($metaFields as $field)
            <div class="meta-line">
                <span class="meta-label">{{ $field['label'] }} :</span>
                <span class="meta-value">{{ $field['value'] ?? '' }}</span>
            </div>
            @endforeach
        </div>

        <table class="lines">
            <thead>
                <tr>
                    @foreach ($columns as $col)
                    <th @class([ 'col-no'=> ($col['key'] ?? '') === 'no',
                        'col-qty' => ($col['key'] ?? '') === 'qty',
                        'col-remarks' => ($col['key'] ?? '') === 'remarks',
                        ])>{{ $col['label'] }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $i => $row)
                <tr>
                    @foreach ($columns as $col)
                    @php $key = $col['key']; @endphp
                    <td @class([ 'col-no'=> $key === 'no',
                        'col-qty' => $key === 'qty',
                        'col-remarks' => $key === 'remarks',
                        ])>
                        @if ($key === 'item' && is_array($row['item'] ?? null))
                        <div class="item-main">{{ $row['item']['main'] ?? '' }}</div>
                        @if (!empty($row['item']['sub']))
                        <div class="item-sub">{{ $row['item']['sub'] }}</div>
                        @endif
                        @else
                        {{ $row[$key] ?? '' }}
                        @endif
                    </td>
                    @endforeach
                </tr>
                @endforeach
                @for ($i = 0; $i < $padCount; $i++)
                    <tr>
                    @foreach ($columns as $col)
                    <td @class([ 'col-no'=> ($col['key'] ?? '') === 'no',
                        'col-qty' => ($col['key'] ?? '') === 'qty',
                        'col-remarks' => ($col['key'] ?? '') === 'remarks',
                        ])>
                        @if (($col['key'] ?? '') === 'no')
                        {{ count($rows) + $i + 1 }}
                        @endif
                    </td>
                    @endforeach
                    </tr>
                    @endfor
            </tbody>
        </table>

        <div class="sign-row">
            @foreach ($signatures as $sign)
            <div class="sign-cell">
                <div class="sign-label">{{ $sign['label'] }}</div>
                @if (!empty($sign['name']))
                <div class="sign-name">{{ $sign['name'] }}</div>
                @endif
                @if (!empty($sign['date']))
                <div class="sign-date">{{ $sign['date'] }}</div>
                @endif
            </div>
            @endforeach
        </div>

        <div class="footer">
            <span>{{ $footerCode ?? 'ARKA/HCS/IV/06.22' }}</span>
            <span>{{ $footerRev ?? 'Rev.1' }}</span>
            <span>Page 1 / 1</span>
        </div>
    </div>
</body>

</html>