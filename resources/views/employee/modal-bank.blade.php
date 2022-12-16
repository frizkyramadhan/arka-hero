<div class="modal fade text-left" id="modal-bank-{{ $bank->id }}">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Edit Employee - Bank Account</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ url('employeebanks/' . $bank->id) }}" method="POST">
        <input type="hidden" name="employee_id" value="{{ old('employee_id', $bank->employee_id) }}">
        @csrf
        @method('PATCH')
        <div class="modal-body">
          <div class="card-body">
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Bank</label>
              <div class="col-sm-10">
                <select name="bank_id" class="form-control @error('bank_id') is-invalid @enderror">
                  @foreach ($getBanks as $gb)
                  <option value="{{ $gb->id }}" {{ old('bank_id', $bank->bank_id) == $gb->id ? 'selected' : '' }}>
                    {{ $gb->bank_name }}
                  </option>
                  @endforeach
                </select>
                @error('bank_id')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Account No.</label>
              <div class="col-sm-10">
                <input type="number" class="form-control @error('bank_account_no') is-invalid @enderror" name="bank_account_no" value="{{ old('bank_account_no', $bank->bank_account_no) }}">
                @error('bank_account_no')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Account Name</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('bank_account_name') is-invalid @enderror" name="bank_account_name" value="{{ old('bank_account_name', $bank->bank_account_name) }}">
                @error('bank_account_name')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Branch</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('bank_account_branch') is-invalid @enderror" name="bank_account_branch" value="{{ old('bank_account_branch', $bank->bank_account_branch) }}">
                @error('bank_account_branch')
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
