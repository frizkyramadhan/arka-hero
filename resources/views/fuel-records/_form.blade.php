@php
    $record = $fuelRecord ?? null;
@endphp
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            <label>Vehicle <span class="text-danger">*</span></label>
            <select name="vehicle_id" class="form-control select2bs4 @error('vehicle_id') is-invalid @enderror" required>
                <option value="">- Select -</option>
                @foreach ($vehicles as $v)
                    <option value="{{ $v->id }}"
                        @selected(old('vehicle_id', $record->vehicle_id ?? ($selectedVehicleId ?? null)) == $v->id)>
                        {{ $v->kode }} — {{ $v->license_plate }}
                    </option>
                @endforeach
            </select>
            @error('vehicle_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Date <span class="text-danger">*</span></label>
            <input type="date" name="fuel_date" class="form-control @error('fuel_date') is-invalid @enderror"
                value="{{ old('fuel_date', optional($record?->fuel_date)->format('Y-m-d') ?? now()->toDateString()) }}" required>
            @error('fuel_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Odometer <span class="text-danger">*</span></label>
            <input type="number" name="odometer" min="0" class="form-control @error('odometer') is-invalid @enderror"
                value="{{ old('odometer', $record->odometer ?? 0) }}" required>
            @error('odometer')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-3">
        <div class="form-group">
            <label>Fuel Type <span class="text-danger">*</span></label>
            <input type="text" name="fuel_type" class="form-control @error('fuel_type') is-invalid @enderror"
                value="{{ old('fuel_type', $record->fuel_type ?? 'diesel') }}" required>
            @error('fuel_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Qty (Liters) <span class="text-danger">*</span></label>
            <input type="number" step="0.01" min="0.01" name="quantity" id="quantity"
                class="form-control @error('quantity') is-invalid @enderror"
                value="{{ old('quantity', $record->quantity ?? '') }}" required>
            @error('quantity')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Price / Liter <span class="text-danger">*</span></label>
            <input type="number" step="0.01" min="0" name="price_per_liter" id="price_per_liter"
                class="form-control @error('price_per_liter') is-invalid @enderror"
                value="{{ old('price_per_liter', $record->price_per_liter ?? '') }}" required>
            @error('price_per_liter')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>
    <div class="col-md-3">
        <div class="form-group">
            <label>Total (auto)</label>
            <input type="text" id="total_preview" class="form-control" readonly
                value="{{ old('quantity') && old('price_per_liter') ? number_format(old('quantity') * old('price_per_liter'), 0, ',', '.') : ($record ? number_format((float) $record->total_cost, 0, ',', '.') : '') }}">
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-4">
        <div class="form-group">
            <label>Fuel Station</label>
            <input type="text" name="fuel_station" class="form-control"
                value="{{ old('fuel_station', $record->fuel_station ?? '') }}">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Receipt No.</label>
            <input type="text" name="receipt_number" class="form-control"
                value="{{ old('receipt_number', $record->receipt_number ?? '') }}">
        </div>
    </div>
    <div class="col-md-4">
        <div class="form-group">
            <label>Receipt Image / PDF</label>
            <input type="file" name="receipt_image" class="form-control-file" accept=".jpg,.jpeg,.png,.pdf">
        </div>
    </div>
</div>
<div class="form-group">
    <label>Notes</label>
    <textarea name="notes" class="form-control" rows="2">{{ old('notes', $record->notes ?? '') }}</textarea>
</div>
