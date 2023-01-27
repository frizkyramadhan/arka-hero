<div class="modal fade text-left" id="modal-employee-{{ $employee->id }}">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Edit Employee - Personal Detail</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ url('employees/' . $employee->id) }}" method="POST">
        @csrf
        @method('PATCH')
        <div class="modal-body">
          <div class="card-body">
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Full Name</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('fullname') is-invalid @enderror" name="fullname" value="{{ old('fullname', $employee->fullname) }}">
                @error('fullname')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">ID Card No.</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('identity_card') is-invalid @enderror" name="identity_card" value="{{ old('identity_card', $employee->identity_card) }}">
                @error('identity_card')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Place of Birth</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('emp_pob') is-invalid @enderror" name="emp_pob" value="{{ old('emp_pob', $employee->emp_pob) }}">
                @error('emp_pob')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Date of Birth</label>
              <div class="col-sm-10">
                <input type="date" class="form-control @error('emp_dob') is-invalid @enderror" name="emp_dob" value="{{ old('emp_dob', $employee->emp_dob) }}">
                @error('emp_dob')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Blood Type</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('blood_type') is-invalid @enderror" name="blood_type" value="{{ old('blood_type', $employee->blood_type) }}">
                @error('blood_type')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Religion</label>
              <div class="col-sm-10">
                <select name="religion_id" class="form-control @error('religion_id') is-invalid @enderror">
                  @foreach ($religions as $religions)
                  <option value="{{ $religions->id }}" {{ old('religion_id', $employee->religion_id) == $religions->id ? 'selected' : '' }}>
                    {{ $religions->religion_name }}
                  </option>
                  @endforeach
                </select>
                @error('religion_id')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Nationality</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('nationality') is-invalid @enderror" name="nationality" value="{{ old('nationality', $employee->nationality) }}">
                @error('nationality')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Gender</label>
              <div class="col-sm-10">
                <select name="gender" class="form-control @error('gender') is-invalid @enderror">
                  <option value="male" {{ old('gender', $employee->gender) == 'male' ? 'selected' : '' }}>Male
                  </option>
                  <option value="female" {{ old('gender', $employee->gender) == 'female' ? 'selected' : '' }}>Female
                  </option>
                </select>
                @error('gender')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Marital</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('marital') is-invalid @enderror" name="marital" value="{{ old('marital', $employee->marital) }}">
                @error('marital')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Address</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('address') is-invalid @enderror" name="address" value="{{ old('address', $employee->address) }}">
                @error('address')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Village</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('village') is-invalid @enderror" name="village" value="{{ old('village', $employee->village) }}">
                @error('village')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Ward</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('ward') is-invalid @enderror" name="ward" value="{{ old('ward', $employee->ward) }}">
                @error('ward')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">District</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('district') is-invalid @enderror" name="district" value="{{ old('district', $employee->district) }}">
                @error('district')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">City</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('city') is-invalid @enderror" name="city" value="{{ old('city', $employee->city) }}">
                @error('city')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Phone</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone', $employee->phone) }}">
                @error('phone')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Email</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email', $employee->email) }}">
                @error('email')
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
