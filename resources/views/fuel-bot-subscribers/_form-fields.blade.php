@php
    $model = $model ?? null;
    $prefix = $prefix ?? '';
@endphp
<div class="form-group row">
    <label class="col-sm-3 col-form-label" for="{{ $prefix }}user_id">
        User <span class="text-danger">*</span>
    </label>
    <div class="col-sm-9">
        <select name="user_id" id="{{ $prefix }}user_id"
            class="form-control select2bs4 @error('user_id') is-invalid @enderror" required style="width: 100%;">
            <option value="">— Select user —</option>
            @foreach ($users as $user)
                <option value="{{ $user->id }}"
                    {{ old('user_id', $model->user_id ?? '') == $user->id ? 'selected' : '' }}>
                    {{ $user->name }}@if($user->email) ({{ $user->email }})@endif
                </option>
            @endforeach
        </select>
        @error('user_id')
            <div class="invalid-feedback d-block">{{ $message }}</div>
        @enderror
    </div>
</div>
<div class="form-group row">
    <label class="col-sm-3 col-form-label" for="{{ $prefix }}telegram_user_id">
        Telegram User ID <span class="text-danger">*</span>
    </label>
    <div class="col-sm-9">
        <input type="number" name="telegram_user_id" id="{{ $prefix }}telegram_user_id"
            class="form-control @error('telegram_user_id') is-invalid @enderror"
            value="{{ old('telegram_user_id', $model->telegram_user_id ?? '') }}" required min="1">
        <small class="form-text text-muted">Dari pesan /start atau /id di bot (angka panjang).</small>
        @error('telegram_user_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
<div class="form-group row">
    <label class="col-sm-3 col-form-label" for="{{ $prefix }}telegram_username">Telegram username</label>
    <div class="col-sm-9">
        <input type="text" name="telegram_username" id="{{ $prefix }}telegram_username"
            class="form-control @error('telegram_username') is-invalid @enderror"
            value="{{ old('telegram_username', $model->telegram_username ?? '') }}"
            placeholder="optional, tanpa @">
        @error('telegram_username')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
<div class="form-group row">
    <label class="col-sm-3 col-form-label" for="{{ $prefix }}notes">Notes</label>
    <div class="col-sm-9">
        <textarea name="notes" id="{{ $prefix }}notes" rows="2"
            class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $model->notes ?? '') }}</textarea>
        @error('notes')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>
<div class="form-group row">
    <label class="col-sm-3 col-form-label">Status</label>
    <div class="col-sm-9">
        <div class="custom-control custom-checkbox mt-2">
            <input type="checkbox" class="custom-control-input" id="{{ $prefix }}is_active" name="is_active"
                value="1" @checked(old('is_active', $model->is_active ?? true))>
            <label class="custom-control-label" for="{{ $prefix }}is_active">Active</label>
        </div>
    </div>
</div>
