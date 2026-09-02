@extends('layouts.main')

@section('title', $title)

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <style>
        #order-lines .select2-container {
            min-width: 180px;
        }
    </style>
@endsection

@section('content')
    @php
        $storeRoute = $isPersonal
            ? ($order ? route('supplies.orders.my-orders.update', $order) : route('supplies.orders.my-orders.store'))
            : ($order ? route('supplies.orders.update', $order) : route('supplies.orders.store'));
        $cancelRoute = $isPersonal
            ? ($order ? route('supplies.orders.my-orders.show', $order) : route('supplies.orders.my-orders'))
            : ($order ? route('supplies.orders.show', $order) : route('supplies.orders.index'));
        $listRoute = $isPersonal ? route('supplies.orders.my-orders') : route('supplies.orders.index');
        $oldLines = old('items');
        $lines = $oldLines ?? ($order?->items?->map(fn ($i) => [
            'supply_item_id' => $i->supply_item_id,
            'quantity_ordered' => $i->quantity_ordered,
            'remarks' => $i->remarks,
        ])->all() ?? [['supply_item_id' => '', 'quantity_ordered' => 1, 'remarks' => '']]);
        $itemMap = $items->keyBy('id');
        $itemOptionsForJs = $items->map(fn ($i) => [
            'id' => $i->id,
            'label' => $i->code.' — '.$i->name.' ('.$i->stock_unit.')',
            'description' => $i->description ?: '',
        ])->values();
        $defaultDept = old('department_id', $order?->department_id);
        $defaultDate = old('order_date', optional($order?->order_date)->format('Y-m-d') ?? now()->toDateString());
    @endphp
    <div class="content-header">
        <div class="container-fluid">
            <div class="row mb-2">
                <div class="col-sm-6">
                    <h1 class="m-0">{{ $title }}</h1>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-sm-right">
                        <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                        <li class="breadcrumb-item"><a href="{{ $listRoute }}">{{ $isPersonal ? 'My Office Supply Orders' : 'Supply Orders' }}</a></li>
                        <li class="breadcrumb-item active">{{ $order ? 'Edit' : 'Add New' }}</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <form action="{{ $storeRoute }}" method="POST" id="supply-order-form">
                @csrf
                @if ($order) @method('PUT') @endif

                <div class="row">
                    <div class="col-md-8 order-2">
                        @if ($isPersonal)
                            <div class="alert alert-info py-2">
                                <i class="fas fa-info-circle mr-1"></i>
                                Only <strong>Office Supply</strong> catalog items (ATK) can be added to this request.
                            </div>
                        @endif
                        <div class="card card-success card-outline elevation-3">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-boxes mr-1"></i> Items</h3>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" id="btn-add-line" title="Add line">
                                        <i class="fas fa-plus"></i> Add line
                                    </button>
                                </div>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered mb-0" id="order-lines">
                                        <thead>
                                            <tr>
                                                <th class="align-middle">Item</th>
                                                <th class="align-middle">Description</th>
                                                <th class="align-middle text-center" style="width:100px">Qty</th>
                                                <th class="align-middle">Remarks</th>
                                                <th class="align-middle text-center" style="width:50px"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($lines as $idx => $line)
                                                @php
                                                    $item = $itemMap->get($line['supply_item_id'] ?? '');
                                                    $desc = $item->description ?? '';
                                                @endphp
                                                <tr>
                                                    <td>
                                                        <select name="items[{{ $idx }}][supply_item_id]" class="form-control select2bs4 item-select" required>
                                                            <option value="">- Select -</option>
                                                            @foreach ($items as $opt)
                                                                <option value="{{ $opt->id }}" data-description="{{ e(display_text($opt->description ?? '', '')) }}"
                                                                    @selected(($line['supply_item_id'] ?? '') == $opt->id)>
                                                                    {{ $opt->code }} — {{ $opt->name }} ({{ $opt->stock_unit }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    </td>
                                                    <td class="item-description text-muted align-middle">{{ $desc !== '' ? $desc : '—' }}</td>
                                                    <td>
                                                        <input type="number" name="items[{{ $idx }}][quantity_ordered]" class="form-control" min="1" required
                                                            value="{{ $line['quantity_ordered'] ?? 1 }}">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="items[{{ $idx }}][remarks]" class="form-control"
                                                            value="{{ $line['remarks'] ?? '' }}">
                                                    </td>
                                                    <td class="text-center align-middle">
                                                        <button type="button" class="btn btn-sm btn-danger btn-remove-line">&times;</button>
                                                    </td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-4 order-1">
                        <div class="card card-primary card-outline elevation-3">
                            <div class="card-header py-2">
                                <h3 class="card-title"><i class="fas fa-file-invoice mr-1"></i> {{ $isPersonal ? 'Office Supply Order' : 'Supply Order' }}</h3>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <label>Order No</label>
                                    <input type="text" class="form-control" disabled
                                        value="{{ $order->order_number ?? ($previewOrderNumber ?? '—') }}">
                                </div>
                                <div class="form-group">
                                    <label>Project</label>
                                    <input type="text" class="form-control" disabled
                                        value="{{ $administration?->project?->project_code }} — {{ $administration?->project?->project_name }}">
                                    <small class="text-muted">From your active administration</small>
                                </div>
                                <div class="form-group">
                                    <label>Date <span class="text-danger">*</span></label>
                                    <input type="date" name="order_date" class="form-control" required value="{{ $defaultDate }}">
                                </div>
                                <div class="form-group mb-0">
                                    <label>Department <span class="text-danger">*</span></label>
                                    <select name="department_id" class="form-control select2bs4" required>
                                        <option value="">- Select -</option>
                                        @foreach ($departments as $department)
                                            <option value="{{ $department->id }}" @selected((string) $defaultDept === (string) $department->id)>
                                                {{ $department->department_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="card card-info card-outline elevation-2">
                            <div class="card-header py-2">
                                <h3 class="card-title">
                                    <i class="fas fa-users mr-1"></i> Approver Selection
                                </h3>
                            </div>
                            <div class="card-body py-2">
                                @include('components.manual-approver-selector', [
                                    'selectedApprovers' => old('manual_approvers', $order->manual_approvers ?? []),
                                    'documentType' => 'supply_order',
                                    'documentId' => $order?->id,
                                    'mode' => 'edit',
                                    'required' => true,
                                ])
                            </div>
                        </div>

                        <div class="card elevation-3">
                            <div class="card-body">
                                <div class="row mb-2">
                                    <div class="col-6 pr-1">
                                        <button type="submit" name="submit_action" value="draft" class="btn btn-warning btn-block">
                                            <i class="fas fa-save mr-1"></i> Draft
                                        </button>
                                    </div>
                                    <div class="col-6 pl-1">
                                        <button type="submit" name="submit_action" value="submit" class="btn btn-success btn-block">
                                            <i class="fas fa-paper-plane mr-1"></i> Submit
                                        </button>
                                    </div>
                                </div>
                                <a href="{{ $cancelRoute }}" class="btn btn-secondary btn-block">
                                    <i class="fas fa-times-circle mr-1"></i> Cancel
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </section>
@endsection

@section('scripts')
    <script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
    <script>
        $(function() {
            $('.select2bs4').select2({ theme: 'bootstrap4', width: '100%' });
            var idx = {{ count($lines) }};
            var itemOptions = @json($itemOptionsForJs);

            $(document).on('change', '.item-select', function() {
                var desc = $(this).find('option:selected').data('description') || '';
                $(this).closest('tr').find('.item-description').text(desc !== '' ? desc : '—');
            });

            $('#btn-add-line').on('click', function() {
                var $select = $('<select>', {
                    name: 'items['+idx+'][supply_item_id]',
                    class: 'form-control select2bs4 item-select',
                    required: true
                });
                $select.append($('<option>', { value: '', text: '- Select -' }));
                itemOptions.forEach(function(item) {
                    $select.append($('<option>', { value: item.id, text: item.label, 'data-description': item.description }));
                });
                var $row = $('<tr>');
                $row.append($('<td>').append($select));
                $row.append($('<td>', { class: 'item-description text-muted', text: '—' }));
                $row.append($('<td>').append($('<input>', {
                    type: 'number', name: 'items['+idx+'][quantity_ordered]', class: 'form-control', min: 1, required: true, value: 1
                })));
                $row.append($('<td>').append($('<input>', {
                    type: 'text', name: 'items['+idx+'][remarks]', class: 'form-control'
                })));
                $row.append($('<td>', { class: 'text-center' }).append(
                    $('<button>', { type: 'button', class: 'btn btn-sm btn-danger btn-remove-line', html: '&times;' })
                ));
                $('#order-lines tbody').append($row);
                $select.select2({ theme: 'bootstrap4', width: '100%' });
                idx++;
            });
            $(document).on('click', '.btn-remove-line', function() {
                if ($('#order-lines tbody tr').length > 1) {
                    $(this).closest('tr').remove();
                }
            });
        });
    </script>
@endsection
