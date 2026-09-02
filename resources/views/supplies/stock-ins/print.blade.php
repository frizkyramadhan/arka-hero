@php
    $columns = [
        ['key' => 'no', 'label' => 'No'],
        ['key' => 'item', 'label' => 'Item'],
        ['key' => 'qty', 'label' => 'Qty In'],
        ['key' => 'remarks', 'label' => 'Remarks'],
    ];

    $rows = $stockIn->items->map(function ($line, $idx) {
        $item = $line->item;
        $main = trim(($item->code ?? '').' '.($item->name ?? ''));
        $sub = $item->description ?? '';

        return [
            'no' => $idx + 1,
            'item' => ['main' => $main, 'sub' => $sub],
            'qty' => $line->quantity,
            'remarks' => display_text($line->remarks ?? null, ''),
        ];
    })->values()->all();

    $projectLabel = trim(($stockIn->project->project_code ?? '').' — '.($stockIn->project->project_name ?? ''));

    $metaFields = [
        ['label' => 'No. SI', 'value' => $stockIn->document_number],
        ['label' => 'Date', 'value' => $stockIn->stock_date?->format('d F Y')],
        ['label' => 'Project', 'value' => $projectLabel],
    ];

    if ($stockIn->order) {
        $metaFields[] = ['label' => 'Supply Order', 'value' => $stockIn->order->order_number];
    }

    if (filled($stockIn->notes)) {
        $metaFields[] = ['label' => 'Notes', 'value' => $stockIn->notes];
    }

    $signatures = [
        [
            'label' => 'Recorded by,',
            'name' => $stockIn->createdBy->name ?? '',
            'date' => $stockIn->stock_date?->format('d F Y') ?? '',
        ],
        [
            'label' => 'Verified by,',
            'name' => '',
            'date' => '',
        ],
    ];
@endphp

@include('supplies.print._layout', [
    'formTitle' => 'Stock In Form',
    'documentNo' => $stockIn->document_number,
    'metaFields' => $metaFields,
    'columns' => $columns,
    'rows' => $rows,
    'minRows' => 20,
    'signatures' => $signatures,
    'footerCode' => 'ARKA/HCS/IV/06.23',
    'backUrl' => route('supplies.stock-ins.show', $stockIn),
])
