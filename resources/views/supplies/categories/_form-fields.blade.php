@php
    $model = $model ?? null;
    $prefixLocked = $model && ! $model->canChangePrefix();
@endphp
<div class="form-group">
    <label>Name <span class="text-danger">*</span></label>
    <input type="text" name="name" class="form-control" required maxlength="255"
        value="{{ old('name', $model->name ?? '') }}">
</div>
<div class="form-group">
    <label>Item code prefix <span class="text-danger">*</span></label>
    @if ($prefixLocked)
        <input type="text" class="form-control" value="{{ $model->prefix }}" disabled>
        <small class="text-muted">Prefix cannot change after catalog items exist.</small>
    @else
        <input type="text" name="prefix" class="form-control text-uppercase" required maxlength="10"
            placeholder="GAA" value="{{ old('prefix', $model->prefix ?? '') }}">
        <small class="text-muted">Letters only. Used as the start of item codes (e.g. GAA001).</small>
    @endif
</div>
<div class="form-group">
    <label>Description</label>
    <textarea name="description" class="form-control" rows="2">{{ old('description', $model->description ?? '') }}</textarea>
</div>
<div class="form-group">
    <label>Status <span class="text-danger">*</span></label>
    <select name="status" class="form-control select2bs4" required>
        <option value="active" @selected(old('status', $model->status ?? 'active') === 'active')>Active</option>
        <option value="inactive" @selected(old('status', $model->status ?? '') === 'inactive')>Inactive</option>
    </select>
</div>
