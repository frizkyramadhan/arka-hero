<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Nota — {{ $fuelClaim->claim_number }}</title>
    <style>
        * {
            box-sizing: border-box;
        }

        @page {
            size: A4 portrait;
            margin: 8mm;
        }

        html,
        body {
            margin: 0;
            padding: 0;
            background: #e9ecef;
            color: #111;
            font-family: Arial, Helvetica, sans-serif;
            font-size: 11px;
        }

        .toolbar {
            position: sticky;
            top: 0;
            z-index: 10;
            display: flex;
            gap: 8px;
            align-items: center;
            justify-content: flex-end;
            padding: 10px 16px;
            background: #f4f6f9;
            border-bottom: 1px solid #ddd;
        }

        .toolbar a,
        .toolbar button {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 1px solid #bbb;
            background: #fff;
            color: #222;
            border-radius: 4px;
            padding: 6px 12px;
            font-size: 12px;
            text-decoration: none;
            cursor: pointer;
        }

        .toolbar button.primary {
            background: #007bff;
            border-color: #007bff;
            color: #fff;
        }

        .sheet {
            width: 194mm;
            /* A4 content area ≈ 297mm - 16mm margins; keep slightly under to avoid blank 2nd page */
            height: 277mm;
            margin: 12px auto;
            padding: 0;
            display: flex;
            flex-direction: column;
            background: #fff;
            box-shadow: 0 1px 4px rgba(0, 0, 0, .12);
            overflow: hidden;
            page-break-inside: avoid;
            break-inside: avoid;
            page-break-after: auto;
            break-after: auto;
        }

        .sheet.has-next {
            page-break-after: always;
            break-after: page;
        }

        .sheet-header {
            display: flex;
            align-items: center;
            gap: 10px;
            border: 1px solid #222;
            padding: 6px 10px;
            margin-bottom: 3mm;
            flex: 0 0 auto;
        }

        .sheet-header img.logo {
            height: 28px;
            width: auto;
        }

        .sheet-header .title-block {
            flex: 1;
            min-width: 0;
        }

        .sheet-header h1 {
            margin: 0;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.3px;
        }

        .sheet-header .meta {
            margin-top: 2px;
            color: #333;
            line-height: 1.35;
        }

        .sheet-header .page-no {
            white-space: nowrap;
            font-weight: 600;
        }

        .grid {
            flex: 1 1 auto;
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            grid-template-rows: repeat(3, 1fr);
            gap: 3mm;
            min-height: 0;
            overflow: hidden;
        }

        .cell {
            border: 1px solid #444;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            min-height: 0;
            background: #fafafa;
        }

        .cell.empty {
            border-style: dashed;
            border-color: #ccc;
            background: #fff;
        }

        .cell-caption {
            flex: 0 0 auto;
            padding: 3px 5px;
            border-bottom: 1px solid #ddd;
            background: #fff;
            line-height: 1.25;
        }

        .cell-caption strong {
            display: block;
            font-size: 10px;
        }

        .cell-caption span {
            display: block;
            color: #444;
            font-size: 9px;
        }

        .cell-photo {
            flex: 1 1 auto;
            min-height: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2mm;
            background: #fff;
            overflow: hidden;
        }

        .cell-photo img {
            max-width: 100%;
            max-height: 100%;
            width: auto;
            height: auto;
            object-fit: contain;
        }

        .cell-photo .placeholder {
            color: #888;
            font-size: 10px;
            text-align: center;
            padding: 8px;
        }

        @media print {
            html,
            body {
                background: #fff !important;
                height: auto !important;
                overflow: hidden !important;
            }

            .toolbar {
                display: none !important;
            }

            .sheet {
                width: 100% !important;
                height: 277mm !important;
                max-height: 277mm !important;
                margin: 0 !important;
                box-shadow: none !important;
                overflow: hidden !important;
            }

            .sheet.has-next {
                page-break-after: always !important;
                break-after: page !important;
            }

            .sheet:not(.has-next) {
                page-break-after: avoid !important;
                break-after: avoid !important;
            }
        }
    </style>
</head>

<body>
    @php
        $pageCount = max(1, $pages->count());
    @endphp

    <div class="toolbar no-print">
        <a href="{{ route('fuel-claims.show', $fuelClaim) }}">Kembali</a>
        <button type="button" class="primary" onclick="window.print()">Cetak</button>
    </div>

    @forelse ($pages as $pageIndex => $pageRecords)
        @php
            $cells = $pageRecords->values();
            while ($cells->count() < 9) {
                $cells->push(null);
            }
            $hasNext = ($pageIndex + 1) < $pageCount;
        @endphp
        <section class="sheet{{ $hasNext ? ' has-next' : '' }}">
            <header class="sheet-header">
                <img class="logo" src="{{ asset('assets/dist/img/logo.png') }}" alt="ARKA">
                <div class="title-block">
                    <h1>Fuel Claim — {{ $fuelClaim->claim_number }}</h1>
                    <div class="meta">
                        Periode:
                        {{ optional($fuelClaim->period_from)->format('d/m/Y') ?: '—' }}
                        –
                        {{ optional($fuelClaim->period_to)->format('d/m/Y') ?: '—' }}
                        &nbsp;|&nbsp;
                        {{ $fuelClaim->records->count() }} nota
                        &nbsp;|&nbsp;
                        {{ number_format((float) $fuelClaim->total_quantity, 2) }} L
                        &nbsp;|&nbsp;
                        Rp {{ number_format((float) $fuelClaim->total_cost, 0, ',', '.') }}
                    </div>
                </div>
                @if ($pageCount > 1)
                    <div class="page-no">
                        Hal. {{ $pageIndex + 1 }}/{{ $pageCount }}
                    </div>
                @endif
            </header>

            <div class="grid">
                @foreach ($cells as $record)
                    @if ($record)
                        <article class="cell">
                            <div class="cell-caption">
                                <strong>
                                    {{ optional($record->fuel_date)->format('d/m/Y') }}
                                    —
                                    {{ $record->vehicle?->kode ?: '—' }}
                                </strong>
                                <span>
                                    {{ $record->vehicle?->license_plate ?: '' }}
                                    @if ($record->quantity)
                                        · {{ number_format((float) $record->quantity, 2) }} L
                                    @endif
                                    @if ($record->total_cost)
                                        · Rp {{ number_format((float) $record->total_cost, 0, ',', '.') }}
                                    @endif
                                </span>
                            </div>
                            <div class="cell-photo">
                                @if (! empty($receiptImages[$record->id]))
                                    <img src="{{ $receiptImages[$record->id] }}"
                                        alt="Nota {{ $record->vehicle?->kode }}">
                                @else
                                    <div class="placeholder">
                                        Foto nota tidak tersedia
                                        @if ($record->receipt_image)
                                            <br>(bukan file gambar)
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </article>
                    @else
                        <div class="cell empty"></div>
                    @endif
                @endforeach
            </div>
        </section>
    @empty
        <section class="sheet">
            <header class="sheet-header">
                <img class="logo" src="{{ asset('assets/dist/img/logo.png') }}" alt="ARKA">
                <div class="title-block">
                    <h1>Fuel Claim — {{ $fuelClaim->claim_number }}</h1>
                    <div class="meta">Tidak ada nota pada claim ini.</div>
                </div>
            </header>
            <div class="grid">
                @for ($i = 0; $i < 9; $i++)
                    <div class="cell empty"></div>
                @endfor
            </div>
        </section>
    @endforelse
</body>

</html>
