@php
    $model = $model ?? null;
@endphp
<div class="form-group row">
    <label class="col-sm-3 col-form-label">Location (Project) <span class="text-danger">*</span></label>
    <div class="col-sm-9">
        <select name="project_id" class="form-control select2bs4" required style="width: 100%;">
            <option value="">— Select Project —</option>
            @foreach ($projects as $project)
                <option value="{{ $project->id }}"
                    {{ old('project_id', $model->project_id ?? '') == $project->id ? 'selected' : '' }}>
                    {{ $project->project_code }} - {{ $project->project_name }}
                </option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-group row">
    <label class="col-sm-3 col-form-label">Room Name <span class="text-danger">*</span></label>
    <div class="col-sm-9">
        <input type="text" name="room_name" class="form-control"
            value="{{ old('room_name', $model->room_name ?? '') }}" required>
    </div>
</div>
<div class="form-group row">
    <label class="col-sm-3 col-form-label">Capacity</label>
    <div class="col-sm-9">
        <input type="number" name="capacity" class="form-control" min="1"
            value="{{ old('capacity', $model->capacity ?? '') }}">
    </div>
</div>
<div class="form-group row">
    <label class="col-sm-3 col-form-label">Facilities</label>
    <div class="col-sm-9">
        <textarea name="facilities" class="form-control" rows="2"
            placeholder="Projector, whiteboard, Zoom, etc.">{{ old('facilities', $model->facilities ?? '') }}</textarea>
    </div>
</div>
<div class="form-group row">
    <label class="col-sm-3 col-form-label">Status <span class="text-danger">*</span></label>
    <div class="col-sm-9">
        <select name="status" class="form-control select2bs4" required style="width: 100%;">
            @foreach (['active' => 'Active', 'inactive' => 'Inactive', 'maintenance' => 'Maintenance'] as $val => $label)
                <option value="{{ $val }}"
                    {{ old('status', $model->status ?? 'active') === $val ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </div>
</div>
<div class="form-group row">
    <label class="col-sm-3 col-form-label">Notes</label>
    <div class="col-sm-9">
        <textarea name="notes" class="form-control" rows="2">{{ old('notes', $model->notes ?? '') }}</textarea>
    </div>
</div>
