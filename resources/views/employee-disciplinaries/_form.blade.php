@php
    $isEdit = isset($record);
    $selectedType = old('type', $isEdit ? $record->type : '');
    $selectedCriteria = old('criterion_ids', $isEdit ? $record->criteria->pluck('id')->all() : []);
    $employeeId = old('employee_id', $isEdit ? $record->employee_id : ($preselectEmployeeId ?? ''));
@endphp

<div class="card">
    <div class="card-header">
        <h3 class="card-title">{{ $subtitle ?? ($isEdit ? 'Edit Disciplinary Record' : 'Add Disciplinary Record') }}</h3>
    </div>
    <div class="card-body">
        <div id="status-banner" class="alert alert-info d-none"></div>
        <div id="terminate-banner" class="alert alert-danger d-none">
            This employee has an active <strong>First & Final Warning (SP3)</strong>.
            New sanctions cannot be issued — proceed with termination.
            <div class="mt-2">
                <a href="#" id="btn-go-terminate" class="btn btn-sm btn-danger">Process Termination</a>
            </div>
        </div>

        <div class="form-group">
            <label for="employee_id">Employee <span class="text-danger">*</span></label>
            <select name="employee_id" id="employee_id"
                class="form-control select2bs4 @error('employee_id') is-invalid @enderror"
                {{ $isEdit ? 'disabled' : 'required' }}>
                <option value="">Select Employee</option>
                @foreach ($employees as $employee)
                    <option value="{{ $employee->id }}"
                        {{ (string) $employeeId === (string) $employee->id ? 'selected' : '' }}>
                        {{ $employee->display_label ?? $employee->fullname }}
                    </option>
                @endforeach
            </select>
            @if ($isEdit)
                <input type="hidden" name="employee_id" value="{{ $record->employee_id }}">
            @endif
            @error('employee_id')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">Primary identity: Name + ID Card (NIK KTP)</small>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="type">Type <span class="text-danger">*</span></label>
                    <select name="type" id="type"
                        class="form-control select2bs4 @error('type') is-invalid @enderror" required>
                        <option value="">Select Type</option>
                        @foreach ($typeOptions as $value => $label)
                            <option value="{{ $value }}" {{ $selectedType === $value ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('type')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label for="effective_date">Effective Date <span class="text-danger">*</span></label>
                    <input type="date" name="effective_date" id="effective_date"
                        class="form-control @error('effective_date') is-invalid @enderror"
                        value="{{ old('effective_date', $isEdit ? $record->effective_date->format('Y-m-d') : date('Y-m-d')) }}"
                        required>
                    @error('effective_date')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <small class="form-text text-muted" id="end-date-hint">
                        Validity: Coaching/Counseling 3 months; Warning Letters 6 months.
                    </small>
                </div>
            </div>
        </div>

        <div class="form-group" id="criteria-group" data-selected='@json($selectedCriteria)'>
            <label>PP Criteria</label>
            <div class="mb-2">
                <input type="text" id="criteria-search" class="form-control form-control-sm"
                    placeholder="Search criteria by code or keyword...">
            </div>
            <div id="criteria-list" class="criteria-checkbox-list border rounded bg-white p-2">
                <div class="text-muted small py-2 px-1" id="criteria-empty">
                    Select a disciplinary type to load PP criteria.
                </div>
            </div>
            <div id="criteria-selected-summary" class="mt-2 d-none">
                <small class="text-muted d-block mb-1"><strong>Selected:</strong></small>
                <div id="criteria-selected-badges"></div>
            </div>
            @error('criterion_ids')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
            <small class="form-text text-muted">Check one or more criteria. Full text is shown for easier reading.</small>
        </div>

        <div class="form-group">
            <label for="pp_notes">PP Notes / Justification</label>
            <textarea name="pp_notes" id="pp_notes" rows="2"
                class="form-control @error('pp_notes') is-invalid @enderror"
                placeholder="Required if master PP criteria are empty, or as additional notes">{{ old('pp_notes', $isEdit ? $record->pp_notes : '') }}</textarea>
            @error('pp_notes')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        <div class="form-group">
            <label for="reason">Reason / Description <span class="text-danger">*</span></label>
            <textarea name="reason" id="reason" rows="4"
                class="form-control @error('reason') is-invalid @enderror"
                required>{{ old('reason', $isEdit ? $record->reason : '') }}</textarea>
            @error('reason')
                <div class="invalid-feedback">{{ $message }}</div>
            @enderror
        </div>

        @php
            $documentOptional = $isEdit && ($record->document_path || $record->allowsDeferredDocument());
        @endphp
        <div class="form-group">
            <label for="document">
                Supporting Document
                @unless ($documentOptional)
                    <span class="text-danger">*</span>
                @endunless
            </label>
            <input type="file" name="document" id="document"
                class="form-control-file @error('document') is-invalid @enderror"
                accept=".pdf,.doc,.docx,.jpg,.jpeg,.png"
                {{ $documentOptional ? '' : 'required' }}>
            @error('document')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
            @if ($isEdit && $record->allowsDeferredDocument())
                <small class="form-text text-muted">
                    Optional for imported records. You can upload the supporting document later.
                </small>
            @else
                <small class="form-text text-muted">Required. Allowed: pdf, doc, docx, jpg, jpeg, png (max 5MB).</small>
            @endif
            @if ($isEdit && $record->document_path)
                <div class="mt-2">
                    <a href="{{ route('employee-disciplinaries.download', $record->id) }}" target="_blank">
                        <i class="fas fa-download"></i> Download current document
                    </a>
                    <div class="form-check mt-1">
                        <input type="checkbox" name="remove_document" value="1" id="remove_document"
                            class="form-check-input">
                        <label for="remove_document" class="form-check-label">
                            Replace / remove current document (upload a new file required)
                        </label>
                    </div>
                </div>
            @endif
        </div>
    </div>
    <div class="card-footer">
        <button type="submit" class="btn btn-primary" id="btn-submit">
            <i class="fas fa-save"></i> {{ $isEdit ? 'Update' : 'Submit' }}
        </button>
        <a href="{{ route('employee-disciplinaries.index') }}" class="btn btn-secondary">Cancel</a>
    </div>
</div>
