@php
$assignment = $assignment ?? null;

$stopRows = old('stop_destinations');
if (! is_array($stopRows)) {
$stopRows = [];
}
$stopRows = array_values(array_filter(array_map('trim', $stopRows), fn ($s) => $s !== ''));
$dbStopManual = [];
if ($stopRows === [] && $assignment) {
foreach ($assignment->stops->where('stop_type', 'destination') as $stopRow) {
$d = trim((string) $stopRow->destination);
if ($d !== '') {
$stopRows[] = $d;
$dbStopManual[] = (bool) $stopRow->is_manual;
}
}
}
if ($stopRows === []) {
$stopRows = [''];
}
$manualFlags = old('stop_destinations_manual');
if (! is_array($manualFlags)) {
$manualFlags = [];
}
@endphp

<div class="d-flex justify-content-between align-items-center mb-2 flex-wrap">
    <label class="mb-0 font-weight-normal">
        <i class="fas fa-map-marker-alt mr-1"></i>
        Destinations <span class="text-danger">*</span>
    </label>
    <button type="button" class="btn btn-sm btn-outline-secondary" id="add-stop-destination">
        <i class="fas fa-plus"></i> Add destination
    </button>
</div>

<div id="destination-stops-container">
    @foreach ($stopRows as $idx => $rowDest)
    @php
    $rowManual = false;
    if (isset($manualFlags[$idx]) && (string) $manualFlags[$idx] === '1') {
    $rowManual = true;
    } elseif (isset($dbStopManual[$idx])) {
    $rowManual = (bool) $dbStopManual[$idx];
    } elseif ($rowDest !== '' && isset($destinationProjects)) {
    $rowManual = true;
    foreach ($destinationProjects as $project) {
    $label = $project->project_code.' - '.$project->project_name;
    if ((string) $rowDest === (string) $label) {
    $rowManual = false;
    break;
    }
    }
    }
    @endphp
    <div class="stop-destination-row border rounded p-2 mb-2" data-stop-row>
        <div class="d-flex justify-content-between align-items-center mb-1">
            <small class="text-muted font-weight-bold stop-row-label">Destination {{ $idx + 1 }}</small>
            <button type="button" class="btn btn-link btn-sm text-danger p-0 remove-stop-destination"
                @if (count($stopRows) <=1) disabled @endif>&times; Remove</button>
        </div>
        <input type="hidden" name="stop_destinations[]" class="stop-dest-hidden" value="{{ $rowDest }}">
        <input type="hidden" name="stop_destinations_manual[]" class="stop-manual-flag"
            value="{{ $rowManual ? '1' : '0' }}">
        <div class="input-group">
            <div class="input-group-prepend">
                <span class="input-group-text p-1" title="External / manual destination">
                    <input type="checkbox" class="stop-manual-cb mx-1" {{ $rowManual ? 'checked' : '' }}
                        aria-label="Manual destination">
                </span>
            </div>
            <div class="stop-project-wrap flex-grow-1 {{ $rowManual ? 'd-none' : '' }}" style="min-width:0">
                <select class="form-control stop-project-select" style="width:100%">
                    <option value="">Select project</option>
                    @foreach ($destinationProjects as $project)
                    @php $destinationOptLabel = $project->project_code.' - '.$project->project_name; @endphp
                    <option value="{{ $destinationOptLabel }}"
                        {{ ! $rowManual && (string) $rowDest === (string) $destinationOptLabel ? 'selected' : '' }}>
                        {{ $destinationOptLabel }}
                    </option>
                    @endforeach
                </select>
            </div>
            <input type="text" class="form-control stop-manual-input {{ $rowManual ? '' : 'd-none' }}"
                style="min-width:0" value="{{ $rowManual ? $rowDest : '' }}"
                placeholder="Manual destination label" autocomplete="off" {{ $rowManual ? '' : 'disabled' }}>
        </div>
    </div>
    @endforeach
</div>

@error('stop_destinations')
<div class="invalid-feedback d-block">{{ $message }}</div>
@enderror
@error('stop_destinations.*')
<div class="invalid-feedback d-block">{{ $message }}</div>
@enderror