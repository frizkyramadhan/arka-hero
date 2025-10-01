<div class="btn-group">
    <a title="Edit" class="btn btn-sm btn-icon btn-primary" data-toggle="modal"
        data-target="#modal-termination-{{ $model->id }}"><i class="fas fa-pen-square"></i></a>
    <button type="button" class="btn btn-sm btn-primary dropdown-toggle dropdown-icon" data-toggle="dropdown">
        <span class="sr-only">Toggle Dropdown</span>
    </button>
    <div class="dropdown-menu" role="menu">
        <form action="{{ url('terminations/delete/' . $model->id) }}" method="post"
            onsubmit="return confirm('Are you sure want to delete this data?')" class="d-inline">
            @method('PATCH')
            @csrf
            <button class="dropdown-item bg-danger" title="Delete"><i class="fas fa-times"></i> Delete</button>
        </form>
    </div>
</div>

<div class="modal fade text-left" id="modal-termination-{{ $model->id }}">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Terminate Employee</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ url('terminations/' . $model->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <input type="hidden" name="id" value="{{ old('id', $model->id) }}">
                <div class="modal-body">
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">Date</label>
                            <div class="col-sm-10">
                                <input type="date"
                                    class="form-control @error('termination_date') is-invalid @enderror"
                                    name="termination_date"
                                    value="{{ old('termination_date', $model->termination_date) }}">
                                @error('termination_date')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">Reason</label>
                            <div class="col-sm-10">
                                <select name="termination_reason" class="form-control" style="width: 100%;">
                                    <option value="End of Contract"
                                        {{ old('termination_reason', $model->termination_reason) == 'End of Contract' ? 'selected' : '' }}>
                                        End of Contract</option>
                                    <option value="End of Project"
                                        {{ old('termination_reason', $model->termination_reason) == 'End of Project' ? 'selected' : '' }}>
                                        End of Project</option>
                                    <option value="Resign"
                                        {{ old('termination_reason', $model->termination_reason) == 'Resign' ? 'selected' : '' }}>
                                        Resign</option>
                                    <option value="Termination"
                                        {{ old('termination_reason', $model->termination_reason) == 'Termination' ? 'selected' : '' }}>
                                        Termination</option>
                                    <option value="Retired"
                                        {{ old('termination_reason', $model->termination_reason) == 'Retired' ? 'selected' : '' }}>
                                        Retired</option>
                                    <option value="Efficiency"
                                        {{ old('termination_reason', $model->termination_reason) == 'Efficiency' ? 'selected' : '' }}>
                                        Efficiency</option>
                                    <option value="Passed Away"
                                        {{ old('termination_reason', $model->termination_reason) == 'Passed Away' ? 'selected' : '' }}>
                                        Passed Away</option>
                                    <option value="Canceled"
                                        {{ old('termination_reason', $model->termination_reason) == 'Canceled' ? 'selected' : '' }}>
                                        Canceled</option>
                                </select>
                                @error('termination_reson')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row align-items-center">
                            <label class="col-sm-2 col-form-label">Certificate of Employment</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control @error('coe_no') is-invalid @enderror"
                                    name="coe_no" value="{{ old('coe_no', $model->coe_no) }}">
                                @error('coe_no')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
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
