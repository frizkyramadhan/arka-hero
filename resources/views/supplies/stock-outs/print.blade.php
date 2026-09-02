@php
    $columns = [
        ['key' => 'no', 'label' => 'No'],
        ['key' => 'item', 'label' => 'Item'],
        ['key' => 'qty', 'label' => 'Qty Out'],
        ['key' => 'remarks', 'label' => 'Location / PIC'],
    ];

    $rows = $stockOut->items->map(function ($line, $idx) {
        $item = $line->item;
        $main = trim(($item->code ?? '').' '.($item->name ?? ''));
        $sub = $item->description ?? '';
        $remarks = trim($line->location.($line->person_in_charge ? ' — '.$line->person_in_charge : ''));

        return [
            'no' => $idx + 1,
            'item' => ['main' => $main, 'sub' => $sub],
            'qty' => $line->quantity,
            'remarks' => $remarks,
        ];
    })->values()->all();

    $projectLabel = trim(($stockOut->project->project_code ?? '').' — '.($stockOut->project->project_name ?? ''));

    $metaFields = [
        ['label' => 'No. SO', 'value' => $stockOut->document_number],
        ['label' => 'Date', 'value' => $stockOut->stock_date?->format('d F Y')],
        ['label' => 'Project', 'value' => $projectLabel],
    ];

    if (filled($stockOut->notes)) {
        $metaFields[] = ['label' => 'Notes', 'value' => $stockOut->notes];
    }

    $signatures = [
        [
            'label' => 'Issued by,',
            'name' => $stockOut->createdBy->name ?? '',
            'date' => $stockOut->stock_date?->format('d F Y') ?? '',
        ],
        [
            'label' => 'Received by,',
            'name' => '',
            'date' => '',
        ],
    ];
@endphp

@include('supplies.print._layout', [
    'formTitle' => 'Stock Out Form',
    'documentNo' => $stockOut->document_number,
    'metaFields' => $metaFields,
    'columns' => $columns,
    'rows' => $rows,
    'minRows' => 20,
    'signatures' => $signatures,
    'footerCode' => 'ARKA/HCS/IV/06.24',
    'backUrl' => route('supplies.stock-outs.show', $stockOut),
])
