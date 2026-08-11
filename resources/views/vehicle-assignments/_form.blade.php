@php
$assignment = $assignment ?? null;
$isEdit = (bool) $assignment;

$originDest = old('origin_destination', $assignment->origin_destination ?? '');
$originManual = old('origin_is_manual', ($assignment->origin_is_manual ?? false) ? '1' : '0') === '1';
if ($originDest !== '' && ! $originManual && isset($destinationProjects)) {
    $matched = false;
    foreach ($destinationProjects as $project) {
        if ($originDest === $project->project_code.' - '.$project->project_name) {
            $matched = true;
            break;
        }
    }
    if (! $matched) {
        $originManual = true;
    }
}

$passengers = old('passengers');
if (! is_array($passengers)) {
    $passengers = [];
    if ($assignment) {
        foreach ($assignment->passengers as $p) {
            $passengers[] = [
                'employee_id' => $p->employee_id,
                'passenger_name' => $p->passenger_name,
            ];
        }
    }
}
if ($passengers === []) {
    $passengers = [['employee_id' => '', 'passenger_name' => '']];
}
@endphp

<form id="foaForm" method="POST"
    action="{{ $isEdit ? route('vehicle-assignments.update', $assignment) : route('vehicle-assignments.store') }}">
    @csrf
    @if ($isEdit)
        @method('PUT')
    @endif

    <div class="row">
        {{-- Left column --}}
        <div class="col-md-8">
            <div class="card card-info card-outline elevation-2">
                <div class="card-header py-2">
                    <h3 class="card-title">
                        <i class="fas fa-hashtag mr-2"></i>
                        <strong>Letter Number</strong>
                    </h3>
                </div>
                <div class="card-body py-2">
                    @include('components.smart-letter-number-selector', [
                        'categoryCode' => 'FOA',
                        'required' => true,
                        'selectedValue' => $assignment?->letter_number_id,
                        'includeId' => $assignment?->letter_number_id,
                        'placeholder' => 'Select FOA Letter Number',
                    ])
                </div>
            </div>

            <div class="card card-primary card-outline elevation-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-clipboard-list mr-2"></i>
                        <strong>Assignment Information</strong>
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="form_number_preview">FOA No</label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-hashtag"></i></span>
                                    </div>
                                    <input type="text" id="form_number_preview"
                                        class="form-control {{ $assignment && $assignment->form_number ? 'alert-success' : 'alert-warning' }}"
                                        readonly
                                        value="{{ old('form_number', $assignment->form_number ?? '') }}"
                                        placeholder="{{ $assignment ? '—' : 'Select Letter Number to Generate FOA No' }}">
                                </div>
                                @if (! $assignment)
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i>
                                        FOA No will be auto-generated when you select a letter number above
                                    </small>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="assignment_date">Date <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <div class="input-group-prepend">
                                        <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                    </div>
                                    <input type="date" name="assignment_date" id="assignment_date"
                                        class="form-control @error('assignment_date') is-invalid @enderror"
                                        value="{{ old('assignment_date', optional($assignment?->assignment_date)->format('Y-m-d') ?? date('Y-m-d')) }}"
                                        required>
                                    @error('assignment_date')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="driver_employee_id">Driver <span class="text-danger">*</span></label>
                                <select name="driver_employee_id" id="driver_employee_id"
                                    class="form-control select2bs4 @error('driver_employee_id') is-invalid @enderror"
                                    required style="width:100%">
                                    <option value="">— Select driver —</option>
                                    @foreach ($employees as $emp)
                                        @php
                                            $nik = optional($emp->administrations->first())->nik;
                                            $label = ($nik ? $nik.' — ' : '').$emp->fullname;
                                        @endphp
                                        <option value="{{ $emp->id }}"
                                            @selected(old('driver_employee_id', $assignment->driver_employee_id ?? '') == $emp->id)>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('driver_employee_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="vehicle_id">Vehicle <span class="text-danger">*</span></label>
                                <select name="vehicle_id" id="vehicle_id"
                                    class="form-control select2bs4 @error('vehicle_id') is-invalid @enderror" required
                                    style="width:100%">
                                    <option value="">— Select vehicle —</option>
                                    @foreach ($vehicles as $v)
                                        @php
                                            $projectLabel = '';
                                            if ($v->project_code) {
                                                foreach ($destinationProjects as $p) {
                                                    if ($p->project_code === $v->project_code) {
                                                        $projectLabel = $p->project_code.' - '.$p->project_name;
                                                        break;
                                                    }
                                                }
                                            }
                                        @endphp
                                        <option value="{{ $v->id }}" data-lokasi="{{ $v->lokasi }}"
                                            data-odometer="{{ $v->odometer }}"
                                            data-project-label="{{ $projectLabel }}"
                                            @selected(old('vehicle_id', $assignment->vehicle_id ?? '') == $v->id)>
                                            {{ $v->kode }} — {{ $v->license_plate }}
                                            @if ($v->lokasi)
                                                ({{ $v->lokasi }})
                                            @endif
                                            · KM {{ number_format((int) $v->odometer) }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('vehicle_id')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label>Origin (lokasi awal) <span class="text-danger">*</span></label>
                        <input type="hidden" name="origin_destination" id="origin_dest_hidden"
                            value="{{ $originDest }}">
                        <input type="hidden" name="origin_is_manual" id="origin_manual_flag"
                            value="{{ $originManual ? '1' : '0' }}">
                        <div class="input-group">
                            <div class="input-group-prepend">
                                <span class="input-group-text p-1" title="Manual origin">
                                    <input type="checkbox" id="origin_manual_cb" class="mx-1"
                                        {{ $originManual ? 'checked' : '' }} aria-label="Manual origin">
                                </span>
                            </div>
                            <div id="origin_project_wrap"
                                class="flex-grow-1 {{ $originManual ? 'd-none' : '' }}" style="min-width:0">
                                <select id="origin_project_select" class="form-control" style="width:100%">
                                    <option value="">Select project</option>
                                    @foreach ($destinationProjects as $project)
                                        @php $label = $project->project_code.' - '.$project->project_name; @endphp
                                        <option value="{{ $label }}"
                                            {{ ! $originManual && $originDest === $label ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <input type="text" id="origin_manual_input"
                                class="form-control {{ $originManual ? '' : 'd-none' }}"
                                value="{{ $originManual ? $originDest : '' }}"
                                placeholder="Manual origin location" autocomplete="off"
                                {{ $originManual ? '' : 'disabled' }}>
                        </div>
                        <small class="form-text text-muted">Check box for external origin.</small>
                        @error('origin_destination')
                            <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-group mb-0">
                        <label for="remarks">Remarks / Keterangan</label>
                        <textarea name="remarks" id="remarks" class="form-control" rows="2"
                            placeholder="e.g. Support Team Saranghae">{{ old('remarks', $assignment->remarks ?? '') }}</textarea>
                    </div>
                </div>
            </div>

            <div class="card card-secondary card-outline elevation-2">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-route mr-2"></i>
                        <strong>Destinations</strong>
                    </h3>
                </div>
                <div class="card-body">
                    @include('vehicle-assignments.partials.destination-fields', [
                        'assignment' => $assignment,
                        'destinationProjects' => $destinationProjects,
                    ])
                </div>
            </div>
        </div>

        {{-- Right column --}}
        <div class="col-md-4">
            <div class="card card-success card-outline elevation-3">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-users mr-2"></i>
                        <strong>Passengers</strong>
                    </h3>
                    <div class="card-tools">
                        <button type="button" class="btn btn-tool" id="add-passenger">
                            <i class="fas fa-plus"></i> Add
                        </button>
                    </div>
                </div>
                <div class="card-body" id="passengers-container">
                    @foreach ($passengers as $i => $p)
                        <div class="passenger-row border rounded p-2 mb-2">
                            <div class="form-group mb-2">
                                <label class="small mb-1">Employee (optional)</label>
                                <select name="passengers[{{ $i }}][employee_id]"
                                    class="form-control select2bs4 passenger-employee" style="width:100%">
                                    <option value="">— Optional —</option>
                                    @foreach ($employees as $emp)
                                        @php
                                            $pNik = optional($emp->administrations->first())->nik;
                                            $pLabel = ($pNik ? $pNik.' — ' : '').$emp->fullname;
                                        @endphp
                                        <option value="{{ $emp->id }}" data-name="{{ $emp->fullname }}"
                                            @selected(($p['employee_id'] ?? '') == $emp->id)>
                                            {{ $pLabel }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group mb-2">
                                <label class="small mb-1">Name</label>
                                <input type="text" name="passengers[{{ $i }}][passenger_name]"
                                    class="form-control form-control-sm"
                                    value="{{ $p['passenger_name'] ?? '' }}"
                                    placeholder="Passenger name (free text ok)">
                            </div>
                            <div class="text-right">
                                <button type="button" class="btn btn-xs btn-outline-danger remove-passenger">
                                    <i class="fas fa-times"></i> Remove
                                </button>
                            </div>
                        </div>
                    @endforeach
                    <small class="text-muted">Pengikut perjalanan. Boleh nama bebas atau pilih karyawan.</small>
                </div>
            </div>

            <div class="card card-outline elevation-2">
                <div class="card-body">
                    <button type="submit" class="btn btn-primary btn-block mb-2">
                        <i class="fas fa-save"></i> {{ $isEdit ? 'Update Draft' : 'Save Draft' }}
                    </button>
                    <a href="{{ route('vehicle-assignments.index') }}" class="btn btn-secondary btn-block">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>
