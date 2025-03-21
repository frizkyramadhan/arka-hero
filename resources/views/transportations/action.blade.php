<a class="btn btn-icon btn-primary" href="javascript:void(0)" onclick="editTransportation({{ $model->id }})"><i
        class="fas fa-pen-square"></i></a>
<form action="{{ url('transportations/' . $model->id) }}" method="post"
    onsubmit="return confirm('Are you sure want to delete this data?')" class="d-inline">
    @method('delete')
    @csrf
    <button class="btn btn-icon btn-danger"><i class="fas fa-times"></i></button>
</form>

<div class="modal fade text-left" id="modal-edit-{{ $model->id }}">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Edit Transportation</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ url('transportations/' . $model->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-body">
                    <div class="card-body">
                        <div class="tab-content p-0">
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Transportation Name</label>
                                <div class="col-sm-10">
                                    <input type="text"
                                        class="form-control @error('transportation_name') is-invalid @enderror"
                                        name="transportation_name"
                                        value="{{ old('transportation_name', $model->transportation_name) }}"
                                        placeholder="Transportation Name">
                                    @error('transportation_name')
                                        <div class="error invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Status</label>
                                <div class="col-sm-10">
                                    <select name="transportation_status"
                                        class="form-control @error('transportation_status') is-invalid @enderror">
                                        <option value="1"
                                            {{ old('transportation_status', $model->transportation_status) == '1' ? 'selected' : '' }}>
                                            Active</option>
                                        <option value="0"
                                            {{ old('transportation_status', $model->transportation_status) == '0' ? 'selected' : '' }}>
                                            Inactive</option>
                                    </select>
                                    @error('transportation_status')
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
