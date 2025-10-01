<a class="btn btn-icon btn-primary" href="{{ url('projects/' . $model->id . '/edit') }}" data-toggle="modal"
    data-target="#modal-lg-{{ $model->id }}"><i class="fas fa-pen-square"></i></a>
<form action="{{ url('projects/' . $model->id) }}" method="post"
    onsubmit="return confirm('Are you sure want to delete this data?')" class="d-inline">
    @method('delete')
    @csrf
    <button class="btn btn-icon btn-danger"><i class="fas fa-times"></i></button>
</form>

<div class="modal fade text-left" id="modal-lg-{{ $model->id }}">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Edit Project</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ url('projects/' . $model->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="card-body">
                        <div class="tab-content p-0">
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Project Code</label>
                                <div class="col-sm-10">
                                    <input type="text"
                                        class="form-control @error('project_code') is-invalid @enderror"
                                        name="project_code" value="{{ old('project_code', $model->project_code) }}"
                                        placeholder="Project Code">
                                    @error('project_code')
                                        <div class="error invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Project Name</label>
                                <div class="col-sm-10">
                                    <input type="text"
                                        class="form-control @error('project_name') is-invalid @enderror"
                                        name="project_name" value="{{ old('project_name', $model->project_name) }}"
                                        placeholder="Project Name">
                                    @error('project_name')
                                        <div class="error invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Project Location</label>
                                <div class="col-sm-10">
                                    <input type="text"
                                        class="form-control @error('project_location') is-invalid @enderror"
                                        name="project_location"
                                        value="{{ old('project_location', $model->project_location) }}"
                                        placeholder="Project Location">
                                    @error('project_location')
                                        <div class="error invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Bowheer</label>
                                <div class="col-sm-10">
                                    <input type="text" class="form-control @error('bowheer') is-invalid @enderror"
                                        name="bowheer" value="{{ old('bowheer', $model->bowheer) }}"
                                        placeholder="Bowheer">
                                    @error('bowheer')
                                        <div class="error invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Leave Type</label>
                                <div class="col-sm-10">
                                    <select name="leave_type"
                                        class="form-control @error('leave_type') is-invalid @enderror" required>
                                        <option value="">Choose Leave Type...</option>
                                        <option value="non_roster"
                                            {{ old('leave_type', $model->leave_type) == 'non_roster' ? 'selected' : '' }}>
                                            Non-Roster</option>
                                        <option value="roster"
                                            {{ old('leave_type', $model->leave_type) == 'roster' ? 'selected' : '' }}>
                                            Roster</option>
                                    </select>
                                    @error('leave_type')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Status</label>
                                <div class="col-sm-10">
                                    <select name="project_status"
                                        class="form-control @error('project_status') is-invalid @enderror">
                                        <option value="1"
                                            {{ old('project_status', $model->project_status) == '1' ? 'selected' : '' }}>
                                            Active</option>
                                        <option value="0"
                                            {{ old('project_status', $model->project_status) == '0' ? 'selected' : '' }}>
                                            Inactive
                                        </option>
                                    </select>
                                    @error('project_status')
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
