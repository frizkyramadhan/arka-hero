<a class="btn btn-icon btn-primary" href="javascript:void(0)" onclick="editUser({{ $model->id }})"><i class="fas fa-pen-square"></i></a>
<form action="{{ url('users/' . $model->id) }}" method="post" onsubmit="return confirm('Are you sure want to delete this data?')" class="d-inline">
  @method('delete')
  @csrf
  <button class="btn btn-icon btn-danger"><i class="fas fa-times"></i></button>
</form>

<div class="modal fade text-left" id="modal-edit-{{ $model->id }}">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Edit User</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ url('users/' . $model->id) }}" method="POST">
        @csrf
        @method('PATCH')
        <div class="modal-body">
          <div class="card-body">
            <div class="tab-content p-0">
              <div class="form-group row">
                <label class="col-sm-2 col-form-label">User Name</label>
                <div class="col-sm-10">
                  <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', $model->name) }}" placeholder="User Name">
                  @error('name')
                  <div class="error invalid-feedback">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-2 col-form-label">Email</label>
                <div class="col-sm-10">
                  <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', $model->email) }}" placeholder="Email">
                  @error('email')
                  <div class="error invalid-feedback">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-2 col-form-label">Password</label>
                <div class="col-sm-10">
                  <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" value="{{ old('password') }}" placeholder="Leave it blank if you don't want to change the password">
                  @error('password')
                  <div class="error invalid-feedback">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-2 col-form-label">Roles</label>
                <div class="col-sm-10">
                  <select name="roles[]" class="form-control select2-edit-{{ $model->id }} @error('roles') is-invalid @enderror" multiple="multiple" data-placeholder="Select roles" style="width: 100%">
                    @foreach($roles as $role)
                    <option value="{{ $role->name }}" {{ in_array($role->name, $model->roles->pluck('name')->toArray()) ? 'selected' : '' }}>{{ $role->name }}</option>
                    @endforeach
                  </select>
                  @error('roles')
                  <div class="invalid-feedback">
                    {{ $message }}
                  </div>
                  @enderror
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-2 col-form-label">Status</label>
                <div class="col-sm-10">
                  <select name="user_status" class="form-control @error('user_status') is-invalid @enderror">
                    <option value="1" {{ old('user_status', $model->user_status) == '1' ? 'selected' : '' }}>Active</option>
                    <option value="0" {{ old('user_status', $model->user_status) == '0' ? 'selected' : '' }}>Inactive</option>
                  </select>
                  @error('user_status')
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
