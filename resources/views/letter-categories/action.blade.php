<a class="btn btn-icon btn-info mr-1" href="{{ route('letter-subjects.index-by-category', $model->category_code) }}"
    title="Manage Subjects">
    <i class="fas fa-list"></i>
</a>
<a class="btn btn-icon btn-primary mr-1" href="{{ url('letter-categories/' . $model->id . '/edit') }}" data-toggle="modal"
    data-target="#modal-lg-{{ $model->id }}" title="Edit Category"><i class="fas fa-pen-square"></i></a>
<button class="btn btn-icon btn-danger" onclick="deleteCategory({{ $model->id }}, '{{ $model->category_name }}')"
    title="Delete Category"><i class="fas fa-times"></i></button>

<div class="modal fade text-left" id="modal-lg-{{ $model->id }}">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Edit Letter Category</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ url('letter-categories/' . $model->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="card-body">
                        <div class="tab-content p-0">
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Category Code <span
                                        class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <input type="text"
                                        class="form-control @error('category_code') is-invalid @enderror"
                                        name="category_code" value="{{ old('category_code', $model->category_code) }}"
                                        placeholder="e.g., A, B, PKWT" maxlength="10" required>
                                    <small class="form-text text-muted">Maximum 10 characters</small>
                                    @error('category_code')
                                        <div class="error invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Category Name <span
                                        class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <input type="text"
                                        class="form-control @error('category_name') is-invalid @enderror"
                                        name="category_name" value="{{ old('category_name', $model->category_name) }}"
                                        placeholder="e.g., Surat Eksternal" maxlength="100" required>
                                    @error('category_name')
                                        <div class="error invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Description</label>
                                <div class="col-sm-9">
                                    <textarea class="form-control @error('description') is-invalid @enderror" name="description" rows="3"
                                        placeholder="Optional description">{{ old('description', $model->description) }}</textarea>
                                    @error('description')
                                        <div class="error invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-3 col-form-label">Status <span class="text-danger">*</span></label>
                                <div class="col-sm-9">
                                    <select name="is_active"
                                        class="form-control @error('is_active') is-invalid @enderror" required>
                                        <option value="1"
                                            {{ old('is_active', $model->is_active) == '1' ? 'selected' : '' }}>Active
                                        </option>
                                        <option value="0"
                                            {{ old('is_active', $model->is_active) == '0' ? 'selected' : '' }}>Inactive
                                        </option>
                                    </select>
                                    @error('is_active')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer justify-content-between">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Update</button>
                </div>
            </form>
        </div>
    </div>
</div>
