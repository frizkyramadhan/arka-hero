@extends('layouts.main')

@section('title', $title)

@section('styles')
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
    <style>
        #stock-in-lines .select2-container {
            min-width: 180px;
        }
    </style>
@endsection

@section('content')
    @php
        $oldLines = old('items');
        $lines = $oldLines ?? ($prefillLines ?: [['supply_item_id' => '', 'quantity' => 1, 'supply_order_item_id' => '', 'description' => '', 'remarks' => '']]);
        $itemMap = $items->keyBy('id');
        $itemOptionsForJs = $items->map(fn ($i) => [
            'id' => $i->id,
            'label' => $i->code.' — '.$i->name.' ('.$i->stock_unit.')',
            'description' => $i->description ?: '',
        ])->values();
        $cancelUrl = $prefillOrder
            ? route('supplies.orders.show', $prefillOrder)
            : route('supplies.stock-ins.index');
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
                        <li class="breadcrumb-item"><a href="{{ route('supplies.stock-ins.index') }}">Stock In</a></li>
                        <li class="breadcrumb-item active">Add New</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
    <section class="content">
        <div class="container-fluid">
            <form method="POST" action="{{ route('supplies.stock-ins.store') }}" id="stock-in-form">
                @csrf
                @if ($prefillOrder)
                    <input type="hidden" name="supply_order_id" value="{{ $prefillOrder->id }}">
                @endif

                <div class="row">
                    <div class="col-md-8 order-2">
                        <div class="card card-success card-outline elevation-3">
                            <div class="card-header">
                                <h3 class="card-title"><i class="fas fa-boxes mr-1"></i> Items</h3>
                                @unless ($prefillOrder)
                                    <div class="card-tools">
                                        <button type="button" class="btn btn-tool" id="btn-add-line" title="Add line">
                                            <i class="fas fa-plus"></i> Add line
                                        </button>
                                    </div>
                                @endunless
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered mb-0" id="stock-in-lines">
                                        <thead>
                                            <tr>
                                                <th class="align-middle">Item</th>
                                                <th class="align-middle">Description</th>
                                                <th class="align-middle text-center" style="width:120px">Qty in</th>
                                                <th class="align-middle">Remarks</th>
                                                <th class="align-middle text-center" style="width:50px"></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($lines as $idx => $line)
                                                @php
                                                    $item = $itemMap->get($line['supply_item_id'] ?? '');
                                                    $desc = $line['description'] ?? ($item->description ?? '');
                                                @endphp
                                                <tr>
                                                    <td>
                                                        @if (!empty($line['supply_order_item_id']))
                                                            <input type="hidden" name="items[{{ $idx }}][supply_order_item_id]" value="{{ $line['supply_order_item_id'] }}">
                                                        @endif
                                                        <select name="items[{{ $idx }}][supply_item_id]" class="form-control select2bs4 item-select" required
                                                            @if (!empty($line['supply_order_item_id'])) disabled @endif>
                                                            <option value="">- Select -</option>
                                                            @foreach ($items as $opt)
                                                                <option value="{{ $opt->id }}" data-description="{{ e(display_text($opt->description ?? '', '')) }}"
                                                                    @selected(($line['supply_item_id'] ?? '') == $opt->id)>
                                                                    {{ $opt->code }} — {{ $opt->name }} ({{ $opt->stock_unit }})
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @if (!empty($line['supply_order_item_id']))
                                                            <input type="hidden" name="items[{{ $idx }}][supply_item_id]" value="{{ $line['supply_item_id'] }}">
                                                        @endif
                                                    </td>
                                                    <td class="item-description text-muted align-middle">{{ $desc !== '' ? $desc : '—' }}</td>
                                                    <td>
                                                        <input type="number" name="items[{{ $idx }}][quantity]" class="form-control" min="1" required
                                                            value="{{ $line['quantity'] ?? 1 }}">
                                                    </td>
                                                    <td>
                                                        <input type="text" name="items[{{ $idx }}][remarks]" class="form-control"
                                                            value="{{ $line['remarks'] ?? '' }}">
                                                    </td>
                                                    <td class="text-center align-middle">
                                                        <button type="button" class="btn btn-sm btn-danger btn-remove-line" title="Remove line">&times;</button>
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
                                <h3 class="card-title"><i class="fas fa-file-invoice mr-1"></i> Stock In</h3>
                            </div>
                            <div class="card-body">
                                @if ($prefillOrder)
                                    <div class="alert alert-info py-2 px-3 small mb-3">
                                        Receipt for Supply Order <strong>{{ $prefillOrder->order_number }}</strong>.
                                        Quantities default to outstanding. Remove lines for items not received in this receipt.
                                    </div>
                                @endif
                                <div class="form-group">
                                    <label>SI No</label>
                                    <input type="text" class="form-control" id="document-number-preview" disabled
                                        value="{{ $previewDocumentNumber }}">
                                </div>
                                <div class="form-group">
                                    <label>Project <span class="text-danger">*</span></label>
                                    @if ($prefillOrder)
                                        <input type="hidden" name="project_id" value="{{ $prefillOrder->project_id }}">
                                        <input type="text" class="form-control" disabled
                                            value="{{ $prefillOrder->project->project_code }} - {{ $prefillOrder->project->project_name }}">
                                    @else
                                        <select name="project_id" id="project_id_select" class="form-control select2bs4" required>
                                            <option value="">- Select -</option>
                                            @foreach ($projects as $project)
                                                <option value="{{ $project->id }}" @selected(old('project_id') == $project->id)>
                                                    {{ $project->project_code }} - {{ $project->project_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @endif
                                </div>
                                <div class="form-group">
                                    <label>Date <span class="text-danger">*</span></label>
                                    <input type="date" name="stock_date" class="form-control" required
                                        value="{{ old('stock_date', now()->toDateString()) }}">
                                </div>
                                <div class="form-group mb-0">
                                    <label>Notes</label>
                                    <textarea name="notes" class="form-control" rows="3">{{ old('notes') }}</textarea>
                                </div>
                            </div>
                        </div>

                        <div class="card elevation-3">
                            <div class="card-body">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="fas fa-save mr-1"></i> Save
                                </button>
                                <a href="{{ $cancelUrl }}" class="btn btn-secondary btn-block">
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
            var documentNumberPreviews = @json($documentNumberPreviews ?? []);

            function updateDocumentNumberPreview() {
                var projectId = $('#project_id_select').val();
                var preview = projectId && documentNumberPreviews[projectId]
                    ? documentNumberPreviews[projectId]
                    : '';
                $('#document-number-preview').val(preview);
            }

            $('#project_id_select').on('change', updateDocumentNumberPreview);

            function fillDescription($select) {
                var desc = $select.find('option:selected').data('description') || '';
                $select.closest('tr').find('.item-description').text(desc !== '' ? desc : '—');
            }

            $(document).on('change', '.item-select', function() {
                fillDescription($(this));
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
                    type: 'number',
                    name: 'items['+idx+'][quantity]',
                    class: 'form-control',
                    min: 1,
                    required: true,
                    value: 1
                })));
                $row.append($('<td>').append($('<input>', {
                    type: 'text',
                    name: 'items['+idx+'][remarks]',
                    class: 'form-control'
                })));
                $row.append($('<td>', { class: 'text-center' }).append(
                    $('<button>', { type: 'button', class: 'btn btn-sm btn-danger btn-remove-line', html: '&times;' })
                ));
                $('#stock-in-lines tbody').append($row);
                $select.select2({ theme: 'bootstrap4', width: '100%' });
                idx++;
            });
            $(document).on('click', '.btn-remove-line', function() {
                if ($('#stock-in-lines tbody tr').length > 1) {
                    $(this).closest('tr').remove();
                }
            });
        });
    </script>
@endsection
