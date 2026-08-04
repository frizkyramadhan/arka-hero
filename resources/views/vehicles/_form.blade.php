@php
$vehicle = $vehicle ?? null;
$cancelRoute = $cancelRoute ?? route('vehicles.index');
$submitLabel = $submitLabel ?? 'Save';
$docExpiry = function (string $type) use ($vehicle) {
if (! $vehicle) {
return old($type.'_expiry');
}
$doc = $vehicle->documents->where('document_type', $type)->sortByDesc('expiry_date')->first();

return old($type.'_expiry', optional($doc?->expiry_date)->format('Y-m-d'));
};
@endphp

<input type="hidden" name="arkfleet_equipment_id" id="arkfleet_equipment_id"
    value="{{ old('arkfleet_equipment_id', $vehicle->arkfleet_equipment_id ?? '') }}">
<input type="hidden" name="arkfleet_status" id="arkfleet_status"
    value="{{ old('arkfleet_status', $vehicle->arkfleet_status ?? '') }}">
<input type="hidden" name="project_code" id="project_code"
    value="{{ old('project_code', $vehicle->project_code ?? '') }}">
<input type="hidden" name="description" id="description"
    value="{{ old('description', $vehicle->description ?? '') }}">
<input type="hidden" name="brand" id="brand" value="{{ old('brand', $vehicle->brand ?? '') }}">
<input type="hidden" name="model" id="model" value="{{ old('model', $vehicle->model ?? '') }}">
<input type="hidden" name="color" id="color" value="{{ old('color', $vehicle->color ?? '') }}">
<input type="hidden" name="vin" id="vin" value="{{ old('vin', $vehicle->vin ?? '') }}">
<input type="hidden" name="engine_number" id="engine_number"
    value="{{ old('engine_number', $vehicle->engine_number ?? '') }}">
<input type="hidden" name="capacity" id="capacity" value="{{ old('capacity', $vehicle->capacity ?? '') }}">

<div id="arkfleet-warning" class="alert alert-warning alert-dismissible d-none">
    <button type="button" class="close" data-dismiss="alert" aria-label="Close">
        <span aria-hidden="true">&times;</span>
    </button>
    <i class="fas fa-exclamation-triangle mr-1"></i>
    <span id="arkfleet-warning-text">Failed to load ArkFleet data.</span>
    You can still enter the Code manually.
</div>

<div class="row">
    {{-- Left: Identity + Classification --}}
    <div class="col-12 col-lg-8">
        {{-- Identity --}}
        <div class="card card-primary card-outline elevation-2 vehicle-form-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-id-card mr-2"></i><strong>Identity</strong>
                </h3>
                <div class="card-tools">
                    <span id="arkfleet-status-badge" class="badge badge-secondary">
                        <i class="fas fa-spinner fa-spin"></i> Loading ArkFleet
                    </span>
                </div>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Select a Light Vehicle from ArkFleet to auto-fill Code, License Plate, and Location.
                    Fields marked <span class="text-danger">*</span> are required.
                </p>

                <div class="row">
                    <div class="col-lg-7 col-md-8">
                        <div class="form-group" id="arkfleet-select-wrap">
                            <label for="arkfleet_select">
                                <i class="fas fa-database text-muted mr-1"></i>
                                ArkFleet Light Vehicle <span class="text-danger">*</span>
                            </label>
                            <select id="arkfleet_select" class="form-control select2bs4" disabled
                                style="width: 100%;"
                                data-equipments-url="{{ route('vehicles.arkfleet.equipments') }}"
                                data-selected-id="{{ old('arkfleet_equipment_id', $vehicle->arkfleet_equipment_id ?? '') }}"
                                data-selected-kode="{{ old('kode', $vehicle->kode ?? '') }}">
                                <option value="">Loading ArkFleet equipments…</option>
                            </select>
                            <small id="arkfleet-loading-hint" class="form-text text-muted">
                                <i class="fas fa-spinner fa-spin"></i> Loading Light Vehicles from ArkFleet…
                            </small>
                            <small id="arkfleet-ready-hint" class="form-text text-muted d-none">
                                <i class="fas fa-search"></i> Search by unit code, plate, or description
                            </small>
                        </div>
                    </div>
                    <div class="col-lg-2 col-md-4">
                        <div class="form-group">
                            <label for="kode">
                                Code <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-barcode"></i></span>
                                </div>
                                <input type="text" name="kode" id="kode"
                                    class="form-control @error('kode') is-invalid @enderror"
                                    value="{{ old('kode', $vehicle->kode ?? '') }}" required maxlength="50" readonly
                                    placeholder="Auto">
                            </div>
                            @error('kode')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-lg-3 col-md-12">
                        <div class="form-group">
                            <label for="license_plate">
                                License Plate <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <div class="input-group-prepend">
                                    <span class="input-group-text"><i class="fas fa-car"></i></span>
                                </div>
                                <input type="text" name="license_plate" id="license_plate"
                                    class="form-control @error('license_plate') is-invalid @enderror"
                                    value="{{ old('license_plate', $vehicle->license_plate ?? '') }}" required maxlength="20"
                                    placeholder="B 1234 XYZ">
                            </div>
                            @error('license_plate')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group mb-md-0">
                            <label for="pic">
                                <i class="fas fa-user text-muted mr-1"></i> PIC
                            </label>
                            <input type="text" name="pic" id="pic"
                                class="form-control @error('pic') is-invalid @enderror"
                                value="{{ old('pic', $vehicle->pic ?? '') }}" maxlength="255"
                                placeholder="Person in charge">
                            @error('pic')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-md-0">
                            <label for="lokasi">
                                <i class="fas fa-map-marker-alt text-muted mr-1"></i> Location
                            </label>
                            <input type="text" name="lokasi" id="lokasi"
                                class="form-control @error('lokasi') is-invalid @enderror"
                                value="{{ old('lokasi', $vehicle->lokasi ?? $vehicle->project_code ?? '') }}" maxlength="100"
                                placeholder="Project / site">
                            <small class="form-text text-muted">
                                Auto-filled from ArkFleet <code>project_code</code>; editable
                            </small>
                            @error('lokasi')
                            <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-0">
                            <label for="status">
                                Status <span class="text-danger">*</span>
                            </label>
                            <select name="status" id="status" class="form-control select2bs4" required style="width: 100%;">
                                @foreach (['active', 'inactive', 'maintenance', 'sold', 'accident'] as $s)
                                <option value="{{ $s }}" @selected(old('status', $vehicle->status ?? 'active') === $s)>
                                    {{ ucfirst($s) }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Classification --}}
        <div class="card card-secondary card-outline elevation-2 vehicle-form-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-cogs mr-2"></i><strong>Classification</strong>
                </h3>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="type">Type</label>
                            <select name="type" id="type" class="form-control select2bs4" style="width: 100%;">
                                @foreach (['sedan', 'suv', 'mpv', 'truck', 'bus', 'motorcycle', 'pickup', 'other'] as $t)
                                <option value="{{ $t }}" @selected(old('type', $vehicle->type ?? 'other') === $t)>
                                    {{ ucfirst($t) }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="ownership">Ownership</label>
                            <select name="ownership" id="ownership" class="form-control select2bs4" style="width: 100%;">
                                @foreach (['company' => 'Company', 'rental' => 'Rental', 'employee' => 'Employee'] as $val => $label)
                                <option value="{{ $val }}" @selected(old('ownership', $vehicle->ownership ?? 'company') === $val)>
                                    {{ $label }}
                                </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="fuel_type">Fuel Type</label>
                            <select name="fuel_type" id="fuel_type" class="form-control select2bs4" style="width: 100%;">
                                <option value="">— Select —</option>
                                @foreach (['gasoline', 'diesel', 'electric', 'hybrid', 'other'] as $f)
                                <option value="{{ $f }}" @selected(old('fuel_type', $vehicle->fuel_type ?? '') === $f)>
                                    {{ ucfirst($f) }}
                                </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">May auto-fill from ArkFleet</small>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="transmission">Transmission</label>
                            <select name="transmission" id="transmission" class="form-control select2bs4" style="width: 100%;">
                                <option value="">— Select —</option>
                                <option value="manual" @selected(old('transmission', $vehicle->transmission ?? '') === 'manual')>Manual</option>
                                <option value="automatic" @selected(old('transmission', $vehicle->transmission ?? '') === 'automatic')>Automatic</option>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="odometer">
                                <i class="fas fa-tachometer-alt text-muted mr-1"></i> Odometer (km)
                            </label>
                            <input type="number" name="odometer" id="odometer" class="form-control" min="0"
                                value="{{ old('odometer', $vehicle->odometer ?? 0) }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group mb-0">
                            <label for="year">
                                <i class="fas fa-calendar-alt text-muted mr-1"></i> Year
                            </label>
                            <input type="number" name="year" id="year" class="form-control" min="1980" max="2100"
                                value="{{ old('year', $vehicle->year ?? '') }}" placeholder="e.g. 2022">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Right: Document Validity, Remarks, Actions --}}
    <div class="col-12 col-lg-4">
        {{-- Document Validity --}}
        <div class="card card-warning card-outline elevation-2 vehicle-form-card vehicle-doc-validity-card">
            <div class="card-header py-2">
                <h3 class="card-title">
                    <i class="fas fa-file-alt mr-2"></i><strong>Document Validity</strong>
                </h3>
            </div>
            <div class="card-body">
                <p class="text-muted vehicle-doc-validity-hint">
                    Optional expiry dates for monitoring.
                </p>

                <div class="vehicle-validity-tile">
                    <div class="vehicle-validity-tile-head">
                        <span class="vehicle-validity-icon bg-primary">
                            <i class="fas fa-id-card"></i>
                        </span>
                        <div>
                            <label for="stnk_expiry" class="mb-0">STNK &amp; Plate</label>
                            <small class="d-block text-muted">Registration expiry</small>
                        </div>
                    </div>
                    <input type="date" name="stnk_expiry" id="stnk_expiry" class="form-control"
                        value="{{ $docExpiry('stnk') }}">
                </div>

                <div class="vehicle-validity-tile">
                    <div class="vehicle-validity-tile-head">
                        <span class="vehicle-validity-icon bg-info">
                            <i class="fas fa-receipt"></i>
                        </span>
                        <div>
                            <label for="pkb_expiry" class="mb-0">PKB</label>
                            <small class="d-block text-muted">Tax expiry</small>
                        </div>
                    </div>
                    <input type="date" name="pkb_expiry" id="pkb_expiry" class="form-control"
                        value="{{ $docExpiry('pkb') }}">
                </div>

                <div class="vehicle-validity-tile mb-0">
                    <div class="vehicle-validity-tile-head">
                        <span class="vehicle-validity-icon bg-success">
                            <i class="fas fa-clipboard-check"></i>
                        </span>
                        <div>
                            <label for="kir_expiry" class="mb-0">KIR</label>
                            <small class="d-block text-muted">Inspection expiry</small>
                        </div>
                    </div>
                    <input type="date" name="kir_expiry" id="kir_expiry" class="form-control"
                        value="{{ $docExpiry('kir') }}">
                </div>
            </div>
        </div>

        {{-- Remarks --}}
        <div class="card card-outline elevation-2 vehicle-form-card">
            <div class="card-header">
                <h3 class="card-title">
                    <i class="fas fa-sticky-note mr-2"></i><strong>Remarks</strong>
                </h3>
            </div>
            <div class="card-body">
                <div class="form-group mb-0">
                    <label for="keterangan">Notes</label>
                    <textarea name="keterangan" id="keterangan" rows="4" class="form-control"
                        placeholder="e.g. MOTORCYCLE, assigned to HO Operations, special notes…">{{ old('keterangan', $vehicle->keterangan ?? '') }}</textarea>
                </div>
            </div>
        </div>

        {{-- Actions (RCR form pattern) --}}
        <div class="card elevation-3 vehicle-form-actions">
            <div class="card-body">
                <button type="submit" class="btn btn-success btn-block">
                    <i class="fas fa-save mr-2"></i> {{ $submitLabel }}
                </button>
                <a href="{{ $cancelRoute }}" class="btn btn-secondary btn-block">
                    <i class="fas fa-times-circle mr-2"></i> Cancel
                </a>
            </div>
        </div>
    </div>
</div>