@if ($bank == null)
    <div class="modal fade text-left" id="modal-bank">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Employee - Add Bank Account</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ url('employeebanks') }}" method="POST">
                    <input type="hidden" name="employee_id" value="{{ old('employee_id', $employee->id) }}">
                    @csrf
                    <div class="modal-body">
                        <div class="card-body">
                            <h6 class="mb-3 text-muted">Bank Account Information</h6>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="bank_id" class="form-label">Bank</label>
                                        <select id="bank_id" name="bank_id"
                                            class="form-control @error('bank_id') is-invalid @enderror">
                                            <option value="">- Select Bank -</option>
                                            @foreach ($getBanks as $gb)
                                                <option value="{{ $gb->id }}"
                                                    {{ old('bank_id') == $gb->id ? 'selected' : '' }}>
                                                    {{ $gb->bank_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('bank_id')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="bank_account_no" class="form-label">Account Number</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-credit-card"></i></span>
                                            </div>
                                            <input type="text"
                                                class="form-control @error('bank_account_no') is-invalid @enderror"
                                                id="bank_account_no" name="bank_account_no"
                                                value="{{ old('bank_account_no') }}" placeholder="Enter account number">
                                        </div>
                                        @error('bank_account_no')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="bank_account_name" class="form-label">Account Name</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                            </div>
                                            <input type="text"
                                                class="form-control @error('bank_account_name') is-invalid @enderror"
                                                id="bank_account_name" name="bank_account_name"
                                                value="{{ old('bank_account_name') }}"
                                                placeholder="Enter account holder name">
                                        </div>
                                        @error('bank_account_name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                        <small class="form-text text-muted">Name as it appears on the bank
                                            account</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="bank_account_branch" class="form-label">Branch</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-building"></i></span>
                                            </div>
                                            <input type="text"
                                                class="form-control @error('bank_account_branch') is-invalid @enderror"
                                                id="bank_account_branch" name="bank_account_branch"
                                                value="{{ old('bank_account_branch') }}"
                                                placeholder="Enter branch name">
                                        </div>
                                        @error('bank_account_branch')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between">
                        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Save</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@else
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
                            <h6 class="mb-3 text-muted">Bank Account Information</h6>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="bank_id" class="form-label">Bank</label>
                                        <select id="bank_id" name="bank_id"
                                            class="form-control @error('bank_id') is-invalid @enderror">
                                            <option value="">- Select Bank -</option>
                                            @foreach ($getBanks as $gb)
                                                <option value="{{ $gb->id }}"
                                                    {{ old('bank_id', $bank->bank_id) == $gb->id ? 'selected' : '' }}>
                                                    {{ $gb->bank_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('bank_id')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="bank_account_no" class="form-label">Account Number</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i
                                                        class="fas fa-credit-card"></i></span>
                                            </div>
                                            <input type="text"
                                                class="form-control @error('bank_account_no') is-invalid @enderror"
                                                id="bank_account_no" name="bank_account_no"
                                                value="{{ old('bank_account_no', $bank->bank_account_no) }}"
                                                placeholder="Enter account number">
                                        </div>
                                        @error('bank_account_no')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="bank_account_name" class="form-label">Account Name</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-user"></i></span>
                                            </div>
                                            <input type="text"
                                                class="form-control @error('bank_account_name') is-invalid @enderror"
                                                id="bank_account_name" name="bank_account_name"
                                                value="{{ old('bank_account_name', $bank->bank_account_name) }}"
                                                placeholder="Enter account holder name">
                                        </div>
                                        @error('bank_account_name')
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                        <small class="form-text text-muted">Name as it appears on the bank
                                            account</small>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-8">
                                    <div class="form-group">
                                        <label for="bank_account_branch" class="form-label">Branch</label>
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text"><i class="fas fa-building"></i></span>
                                            </div>
                                            <input type="text"
                                                class="form-control @error('bank_account_branch') is-invalid @enderror"
                                                id="bank_account_branch" name="bank_account_branch"
                                                value="{{ old('bank_account_branch', $bank->bank_account_branch) }}"
                                                placeholder="Enter branch name">
                                        </div>
                                        @error('bank_account_branch')
                                            <span class="text-danger">{{ $message }}</span>
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
@endif
