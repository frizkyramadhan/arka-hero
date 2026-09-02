@php
    $model = $model ?? null;
    $categories = $categories ?? collect();
    $categoryCodePreviews = $categoryCodePreviews ?? [];
@endphp
<div class="form-group">
    <label>Item Category @if(!$model) <span class="text-danger">*</span> @endif</label>
    @if($model)
        <input type="text" class="form-control"
            value="{{ $model->categoryLabel() }} ({{ $model->category->prefix ?? '' }})" disabled>
        <small class="text-muted">Item code {{ $model->code }} is assigned from this category prefix.</small>
    @else
        <select name="supply_item_category_id" id="supply_item_category_id" class="form-control select2bs4" required>
            <option value="">- Select -</option>
            @foreach($categories->where('status', 'active') as $category)
                <option value="{{ $category->id }}" @selected(old('supply_item_category_id') == $category->id)>
                    {{ $category->name }} ({{ $category->prefix }})
                </option>
            @endforeach
        </select>
    @endif
</div>
@if(!$model)
    <div class="form-group">
        <label>Item code</label>
        <input type="text" class="form-control" id="item-code-preview" disabled
            value="{{ old('supply_item_category_id') && isset($categoryCodePreviews[old('supply_item_category_id')]) ? $categoryCodePreviews[old('supply_item_category_id')] : '' }}"
            placeholder="Select category">
        <small class="text-muted">Preview of the next code in this category.</small>
    </div>
@endif
<div class="form-group">
    <label>Name <span class="text-danger">*</span></label>
    <input type="text" name="name" class="form-control" required maxlength="255"
        value="{{ old('name', $model->name ?? '') }}">
</div>
<div class="form-group">
    <label>Description</label>
    <textarea name="description" class="form-control" rows="2">{{ old('description', $model->description ?? '') }}</textarea>
</div>
<div class="form-group">
    <label>Stock unit <span class="text-danger">*</span></label>
    <input type="text" name="stock_unit" class="form-control" required maxlength="50"
        placeholder="pcs, box, rim, pack"
        value="{{ old('stock_unit', $model->stock_unit ?? '') }}">
</div>
<div class="form-group">
    <label>Status <span class="text-danger">*</span></label>
    <select name="status" class="form-control select2bs4" required>
        <option value="active" @selected(old('status', $model->status ?? 'active') === 'active')>Active</option>
        <option value="inactive" @selected(old('status', $model->status ?? '') === 'inactive')>Inactive</option>
    </select>
</div>
