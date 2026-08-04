@php
    $idSuffix = ($prefix ?? '') === 'manual' ? '_m' : '';
    $record = $record ?? null;
    $selectedVehicleId = old('vehicle_id', $record->vehicle_id ?? null);
@endphp
<div class="form-group">
    <label for="vehicle_id{{ $idSuffix }}">Vehicle <span class="text-danger">*</span></label>
    <select name="vehicle_id" id="vehicle_id{{ $idSuffix }}" class="form-control select2bs4" required>
        <option value="">— Select —</option>
        @foreach ($vehicles as $v)
            <option value="{{ $v->id }}" data-odometer="{{ $v->odometer }}"
                @selected((string) $selectedVehicleId === (string) $v->id)>
                {{ $v->kode }} — {{ $v->license_plate }}
            </option>
        @endforeach
    </select>
</div>
<div class="form-row">
    <div class="form-group col-6">
        <label>Date <span class="text-danger">*</span></label>
        <input type="date" name="fuel_date" id="fuel_date{{ $idSuffix }}" class="form-control"
            value="{{ old('fuel_date', optional($record?->fuel_date)->format('Y-m-d') ?? now()->toDateString()) }}" required>
    </div>
    <div class="form-group col-6">
        <label>Odometer (KM) <span class="text-danger">*</span></label>
        <input type="number" name="odometer" id="odometer{{ $idSuffix }}" class="form-control" min="0"
            value="{{ old('odometer', $record->odometer ?? '') }}" required>
        <small class="text-muted">From handwritten KM on nota</small>
    </div>
</div>
<div class="form-group">
    <label>Fuel type <span class="text-danger">*</span></label>
    <input type="text" name="fuel_type" id="fuel_type{{ $idSuffix }}" class="form-control" list="fuel-types"
        value="{{ old('fuel_type', $record->fuel_type ?? '') }}" required placeholder="PERTAMAX">
    <datalist id="fuel-types">
        <option value="PERTAMAX">
        <option value="Pertalite">
        <option value="Pertamina Dex">
        <option value="Solar">
        <option value="Dexlite">
    </datalist>
</div>
<div class="form-row">
    <div class="form-group col-4">
        <label>Qty (L) <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0.01" name="quantity" id="quantity{{ $idSuffix }}"
            class="form-control" value="{{ old('quantity', $record->quantity ?? '') }}" required>
    </div>
    <div class="form-group col-4">
        <label>Price / L <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0" name="price_per_liter" id="price_per_liter{{ $idSuffix }}"
            class="form-control" value="{{ old('price_per_liter', $record->price_per_liter ?? '') }}" required>
    </div>
    <div class="form-group col-4">
        <label>Total</label>
        <input type="number" step="0.01" min="0" name="total_cost" id="total_cost{{ $idSuffix }}"
            class="form-control" value="{{ old('total_cost', $record->total_cost ?? '') }}">
    </div>
</div>
<div class="form-group">
    <label>Fuel station</label>
    <input type="text" name="fuel_station" id="fuel_station{{ $idSuffix }}" class="form-control"
        value="{{ old('fuel_station', $record->fuel_station ?? '') }}" placeholder="SPBU …">
</div>
<div class="form-group">
    <label>Receipt no.</label>
    <input type="text" name="receipt_number" id="receipt_number{{ $idSuffix }}" class="form-control"
        value="{{ old('receipt_number', $record->receipt_number ?? '') }}">
</div>
<div class="form-group">
    <label>Notes</label>
    <textarea name="notes" id="notes{{ $idSuffix }}" class="form-control" rows="2">{{ old('notes', $record->notes ?? '') }}</textarea>
</div>
