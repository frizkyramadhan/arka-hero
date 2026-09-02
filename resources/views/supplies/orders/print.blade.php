@php
    $columns = [
        ['key' => 'no', 'label' => 'No'],
        ['key' => 'item', 'label' => 'Office Supplies'],
        ['key' => 'qty', 'label' => 'Quantity'],
        ['key' => 'remarks', 'label' => 'Remarks'],
    ];

    $rows = $order->items->map(function ($line, $idx) {
        $item = $line->item;
        $main = trim(($item->code ?? '').' '.($item->name ?? ''));
        $sub = $item->description ?? '';

        return [
            'no' => $idx + 1,
            'item' => ['main' => $main, 'sub' => $sub],
            'qty' => $line->quantity_ordered,
            'remarks' => $line->remarks ?? '',
        ];
    })->values()->all();

    $approvedPlan = $order->approvalPlans
        ->where('status', 1)
        ->sortBy([['approval_order', 'asc'], ['id', 'asc']])
        ->last();

    $signatures = [
        [
            'label' => 'Request by,',
            'name' => $order->requestedBy->name ?? '',
            'date' => $order->submitted_at?->format('d F Y') ?? '',
        ],
        [
            'label' => 'Approved by,',
            'name' => $approvedPlan?->approver?->name ?? '',
            'date' => $approvedPlan?->decisionAt()?->format('d F Y') ?? '',
        ],
    ];

    $metaFields = [
        ['label' => 'No. Order', 'value' => $order->order_number],
        ['label' => 'Date', 'value' => $order->order_date?->format('d F Y')],
        ['label' => 'Department', 'value' => $order->department->department_name ?? ''],
    ];

    $backUrl = $isPersonal
        ? route('supplies.orders.my-orders.show', $order)
        : route('supplies.orders.show', $order);
@endphp

@include('supplies.print._layout', [
    'formTitle' => 'Office Supplies Order Form',
    'documentNo' => $order->order_number,
    'metaFields' => $metaFields,
    'columns' => $columns,
    'rows' => $rows,
    'minRows' => 20,
    'signatures' => $signatures,
    'footerCode' => 'ARKA/HCS/IV/06.22',
    'backUrl' => $backUrl,
])
