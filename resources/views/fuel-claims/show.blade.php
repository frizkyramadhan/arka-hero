@extends('layouts.main')

@section('title', $title)

@section('content')
@php
    $statusClass = [
        'draft' => 'secondary',
        'ready' => 'success',
        'sent' => 'info',
        'realized' => 'primary',
        'cancelled' => 'danger',
    ][$fuelClaim->status] ?? 'secondary';
@endphp
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ $fuelClaim->claim_number }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('fuel-claims.index') }}">Fuel Claims</a></li>
                    <li class="breadcrumb-item active">Detail</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Claim summary</h3>
                        <div class="card-tools">
                            <a href="{{ route('fuel-claims.print', $fuelClaim) }}" target="_blank"
                                class="btn btn-default btn-sm" title="Cetak nota">
                                <i class="fas fa-print"></i> Print
                            </a>
                            <a href="{{ route('fuel-claims.index') }}" class="btn btn-default btn-sm">
                                <i class="fas fa-arrow-left"></i> Back
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-striped mb-0">
                            <tr>
                                <th width="40%">Status</th>
                                <td><span class="badge badge-{{ $statusClass }}">{{ ucfirst($fuelClaim->status) }}</span></td>
                            </tr>
                            <tr>
                                <th>Period</th>
                                <td>
                                    {{ optional($fuelClaim->period_from)->format('Y-m-d') }}
                                    →
                                    {{ optional($fuelClaim->period_to)->format('Y-m-d') }}
                                </td>
                            </tr>
                            <tr>
                                <th>Items</th>
                                <td>{{ $fuelClaim->records->count() }}</td>
                            </tr>
                            <tr>
                                <th>Total qty</th>
                                <td>{{ number_format((float) $fuelClaim->total_quantity, 2) }} L</td>
                            </tr>
                            <tr>
                                <th>Total cost</th>
                                <td>Rp {{ number_format((float) $fuelClaim->total_cost, 0, ',', '.') }}</td>
                            </tr>
                            <tr>
                                <th>External ref</th>
                                <td>{{ $fuelClaim->external_ref ?: '—' }}</td>
                            </tr>
                            <tr>
                                <th>Notes</th>
                                <td>{{ $fuelClaim->notes ?: '—' }}</td>
                            </tr>
                        </table>
                    </div>
                    @if (in_array($fuelClaim->status, ['draft', 'ready'], true))
                        <div class="card-footer">
                            @if ($fuelClaim->status === 'draft')
                                @can('fuel-claims.ready')
                                    <form action="{{ route('fuel-claims.ready', $fuelClaim) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-success btn-sm"
                                            onclick="return confirm('Mark ready for external realization?')">
                                            <i class="fas fa-check"></i> Mark ready
                                        </button>
                                    </form>
                                @endcan
                            @endif
                            @can('fuel-claims.delete')
                                <form action="{{ route('fuel-claims.cancel', $fuelClaim) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button class="btn btn-danger btn-sm"
                                        onclick="return confirm('Cancel claim and return items to verified?')">
                                        <i class="fas fa-ban"></i> Cancel claim
                                    </button>
                                </form>
                            @endcan
                        </div>
                    @endif
                </div>
            </div>
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">Receipts in this claim</h3>
                        @if ($fuelClaim->status === 'draft')
                            @can('fuel-claims.edit')
                                <div class="card-tools">
                                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal"
                                        data-target="#add-receipt-modal">
                                        <i class="fas fa-plus"></i> Add receipt
                                    </button>
                                </div>
                            @endcan
                        @endif
                    </div>
                    <div class="card-body table-responsive p-0">
                        <table class="table table-bordered table-striped mb-0">
                            <thead>
                                <tr>
                                    <th class="align-middle">Date</th>
                                    <th class="align-middle">Vehicle</th>
                                    <th class="align-middle">Type</th>
                                    <th class="align-middle">Qty</th>
                                    <th class="align-middle">Total</th>
                                    <th class="align-middle text-center">Receipt</th>
                                    @if ($fuelClaim->status === 'draft')
                                        @can('fuel-claims.edit')
                                            <th class="align-middle text-center">Action</th>
                                        @endcan
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($fuelClaim->records as $r)
                                    <tr>
                                        <td>{{ optional($r->fuel_date)->format('Y-m-d') }}</td>
                                        <td>{{ $r->vehicle?->kode }} — {{ $r->vehicle?->license_plate }}</td>
                                        <td>{{ $r->fuel_type }}</td>
                                        <td>{{ number_format((float) $r->quantity, 2) }}</td>
                                        <td>{{ number_format((float) $r->total_cost, 0, ',', '.') }}</td>
                                        <td class="text-center">
                                            @if ($r->receipt_image)
                                                <a href="{{ route('fuel-records.receipt', $r) }}" target="_blank"
                                                    class="btn btn-info btn-sm" title="Receipt">
                                                    <i class="fas fa-image"></i>
                                                </a>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        @if ($fuelClaim->status === 'draft')
                                            @can('fuel-claims.edit')
                                                <td class="text-center text-nowrap">
                                                    <button type="button"
                                                        class="btn btn-warning btn-sm btn-edit-receipt"
                                                        title="Edit receipt"
                                                        data-toggle="modal"
                                                        data-target="#edit-receipt-modal"
                                                        data-update-url="{{ route('fuel-claims.receipts.update', [$fuelClaim, $r]) }}"
                                                        data-id="{{ $r->id }}"
                                                        data-vehicle-id="{{ $r->vehicle_id }}"
                                                        data-fuel-date="{{ optional($r->fuel_date)->format('Y-m-d') }}"
                                                        data-odometer="{{ $r->odometer }}"
                                                        data-fuel-type="{{ $r->fuel_type }}"
                                                        data-quantity="{{ $r->quantity }}"
                                                        data-price-per-liter="{{ $r->price_per_liter }}"
                                                        data-total-cost="{{ number_format((float) $r->total_cost, 0, ',', '.') }}"
                                                        data-fuel-station="{{ $r->fuel_station }}"
                                                        data-receipt-number="{{ $r->receipt_number }}"
                                                        data-notes="{{ $r->notes }}"
                                                        data-receipt-url="{{ $r->receipt_image ? route('fuel-records.receipt', $r) : '' }}">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    <form class="d-inline"
                                                        action="{{ route('fuel-claims.receipts.destroy', [$fuelClaim, $r]) }}"
                                                        method="POST">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="btn btn-danger btn-sm"
                                                            title="Remove receipt"
                                                            onclick="return confirm('Remove this receipt from the claim?')">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </td>
                                            @endcan
                                        @endif
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ $fuelClaim->status === 'draft' && auth()->user()->can('fuel-claims.edit') ? 7 : 6 }}"
                                            class="text-center text-muted">No receipts</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

@if ($fuelClaim->status === 'draft')
    @can('fuel-claims.edit')
        <div class="modal fade" id="add-receipt-modal" tabindex="-1" role="dialog"
            aria-labelledby="add-receipt-modal-label" aria-hidden="true">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <form action="{{ route('fuel-claims.receipts.store', $fuelClaim) }}" method="POST">
                        @csrf
                        <div class="modal-header">
                            <h5 class="modal-title" id="add-receipt-modal-label">Add verified receipts</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            @if ($availableRecords->isEmpty())
                                <div class="alert alert-info mb-0">
                                    No verified unclaimed receipts are available.
                                </div>
                            @else
                                <div class="table-responsive">
                                    <table class="table table-bordered table-striped mb-0">
                                        <thead>
                                            <tr>
                                                <th class="text-center" width="40">
                                                    <input type="checkbox" id="check-all-available">
                                                </th>
                                                <th>Date</th>
                                                <th>Vehicle</th>
                                                <th>Type</th>
                                                <th>Qty</th>
                                                <th>Total</th>
                                                <th>Station</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($availableRecords as $record)
                                                <tr>
                                                    <td class="text-center">
                                                        <input type="checkbox" name="fuel_record_ids[]"
                                                            value="{{ $record->id }}" class="available-receipt">
                                                    </td>
                                                    <td>{{ optional($record->fuel_date)->format('Y-m-d') }}</td>
                                                    <td>
                                                        {{ $record->vehicle?->kode }} —
                                                        {{ $record->vehicle?->license_plate }}
                                                    </td>
                                                    <td>{{ $record->fuel_type }}</td>
                                                    <td>{{ number_format((float) $record->quantity, 2) }}</td>
                                                    <td>{{ number_format((float) $record->total_cost, 0, ',', '.') }}</td>
                                                    <td>{{ $record->fuel_station ?: '—' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @endif
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                            @if ($availableRecords->isNotEmpty())
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Add selected receipts
                                </button>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="modal fade" id="edit-receipt-modal" tabindex="-1" role="dialog"
            aria-labelledby="edit-receipt-modal-label" aria-hidden="true">
            <div class="modal-dialog modal-lg" role="document">
                <div class="modal-content">
                    <form id="edit-receipt-form" method="POST" enctype="multipart/form-data"
                        action="{{ session('edit_receipt_id') ? route('fuel-claims.receipts.update', [$fuelClaim, session('edit_receipt_id')]) : '#' }}">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title" id="edit-receipt-modal-label">Edit receipt</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            @php $editErrorCount = isset($errors) ? count($errors) : 0; @endphp
                            @if ($editErrorCount > 0)
                                <div class="alert alert-danger">
                                    <ul class="mb-0 pl-3">
                                        @foreach ($errors->all() as $error)
                                            <li>{{ $error }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            @endif
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>Vehicle <span class="text-danger">*</span></label>
                                        <select name="vehicle_id" id="edit-vehicle-id" class="form-control" required>
                                            <option value="">- Select -</option>
                                            @foreach ($vehicles as $v)
                                                <option value="{{ $v->id }}"
                                                    @selected(old('vehicle_id') == $v->id)>
                                                    {{ $v->kode }} — {{ $v->license_plate }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Date <span class="text-danger">*</span></label>
                                        <input type="date" name="fuel_date" id="edit-fuel-date"
                                            class="form-control" value="{{ old('fuel_date') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Odometer <span class="text-danger">*</span></label>
                                        <input type="number" name="odometer" id="edit-odometer" min="0"
                                            class="form-control" value="{{ old('odometer') }}" required>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Fuel Type <span class="text-danger">*</span></label>
                                        <input type="text" name="fuel_type" id="edit-fuel-type"
                                            class="form-control" value="{{ old('fuel_type') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Qty (Liters) <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" min="0.01" name="quantity"
                                            id="edit-quantity" class="form-control"
                                            value="{{ old('quantity') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Price / Liter <span class="text-danger">*</span></label>
                                        <input type="number" step="0.01" min="0" name="price_per_liter"
                                            id="edit-price-per-liter" class="form-control"
                                            value="{{ old('price_per_liter') }}" required>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Total (auto)</label>
                                        <input type="text" id="edit-total-preview" class="form-control" readonly
                                            value="">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Fuel Station</label>
                                        <input type="text" name="fuel_station" id="edit-fuel-station"
                                            class="form-control" value="{{ old('fuel_station') }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>No. Trans / Receipt No.</label>
                                        <input type="text" name="receipt_number" id="edit-receipt-number"
                                            class="form-control" value="{{ old('receipt_number') }}">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>Replace Receipt Image / PDF</label>
                                        <input type="file" name="receipt_image" class="form-control-file"
                                            accept=".jpg,.jpeg,.png,.pdf,.webp">
                                        <small class="form-text text-muted">
                                            Leave empty to keep current file.
                                            <a href="#" id="edit-current-receipt" target="_blank" class="d-none">
                                                View current
                                            </a>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group mb-0">
                                <label>Notes</label>
                                <textarea name="notes" id="edit-notes" class="form-control"
                                    rows="2">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Save changes
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endcan
@endif
@endsection

@section('scripts')
<script>
    function formatEditTotal() {
        var qty = parseFloat($('#edit-quantity').val()) || 0;
        var price = parseFloat($('#edit-price-per-liter').val()) || 0;
        var total = Math.round(qty * price);
        $('#edit-total-preview').val(total.toLocaleString('id-ID'));
    }

    function fillEditReceiptModal($btn) {
        var $form = $('#edit-receipt-form');
        $form.attr('action', $btn.data('update-url'));
        $('#edit-vehicle-id').val($btn.data('vehicle-id'));
        $('#edit-fuel-date').val($btn.data('fuel-date'));
        $('#edit-odometer').val($btn.data('odometer'));
        $('#edit-fuel-type').val($btn.data('fuel-type'));
        $('#edit-quantity').val($btn.data('quantity'));
        $('#edit-price-per-liter').val($btn.data('price-per-liter'));
        $('#edit-fuel-station').val($btn.data('fuel-station') || '');
        $('#edit-receipt-number').val($btn.data('receipt-number') || '');
        $('#edit-notes').val($btn.data('notes') || '');
        formatEditTotal();

        var receiptUrl = $btn.data('receipt-url');
        var $link = $('#edit-current-receipt');
        if (receiptUrl) {
            $link.attr('href', receiptUrl).removeClass('d-none');
        } else {
            $link.addClass('d-none').attr('href', '#');
        }
    }

    $('#check-all-available').on('change', function() {
        $('.available-receipt').prop('checked', this.checked);
    });

    $(document).on('click', '.btn-edit-receipt', function() {
        fillEditReceiptModal($(this));
    });

    $('#edit-quantity, #edit-price-per-liter').on('input', formatEditTotal);

    @if (session('edit_receipt_id'))
        $(function() {
            var $btn = $('.btn-edit-receipt[data-id="{{ session('edit_receipt_id') }}"]').first();
            if ($btn.length) {
                fillEditReceiptModal($btn);
                @if (old('vehicle_id'))
                    $('#edit-vehicle-id').val(@json(old('vehicle_id')));
                    $('#edit-fuel-date').val(@json(old('fuel_date')));
                    $('#edit-odometer').val(@json(old('odometer')));
                    $('#edit-fuel-type').val(@json(old('fuel_type')));
                    $('#edit-quantity').val(@json(old('quantity')));
                    $('#edit-price-per-liter').val(@json(old('price_per_liter')));
                    $('#edit-fuel-station').val(@json(old('fuel_station')));
                    $('#edit-receipt-number').val(@json(old('receipt_number')));
                    $('#edit-notes').val(@json(old('notes')));
                    formatEditTotal();
                @endif
                $('#edit-receipt-modal').modal('show');
            }
        });
    @endif
</script>
@endsection
