@if ($tax == null)
    <div class="modal fade text-left" id="modal-tax">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Employee - Add Tax Identification Number</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ url('taxidentifications') }}" method="POST">
                    <input type="hidden" name="employee_id" value="{{ old('employee_id', $employee->id) }}">
                    @csrf
                    <div class="modal-body">
                        <h5 class="mb-3 border-bottom pb-2">Tax Information</h5>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="tax_no" class="form-label">Tax Identification Number (NPWP)</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-file-invoice"></i></span>
                                        </div>
                                        <input type="text" class="form-control @error('tax_no') is-invalid @enderror"
                                            id="tax_no" name="tax_no" value="{{ old('tax_no') }}"
                                            placeholder="Enter tax number">
                                    </div>
                                    @error('tax_no')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">Format: 00.000.000.0-000.000</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="tax_valid_date" class="form-label">Tax Registration Date</label>
                                    <div class="input-group date">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                        </div>
                                        <input type="date"
                                            class="form-control @error('tax_valid_date') is-invalid @enderror"
                                            id="tax_valid_date" name="tax_valid_date"
                                            value="{{ old('tax_valid_date') }}">
                                    </div>
                                    @error('tax_valid_date')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-warning mt-3">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Tax information is required for payroll tax deductions and annual tax reporting.
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
    <div class="modal fade text-left" id="modal-tax-{{ $tax->id }}">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Employee - Edit Tax Identification Number</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ url('taxidentifications/' . $tax->id) }}" method="POST">
                    <input type="hidden" name="employee_id" value="{{ old('employee_id', $tax->employee_id) }}">
                    @csrf
                    @method('PATCH')
                    <div class="modal-body">
                        <h5 class="mb-3 border-bottom pb-2">Tax Information</h5>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="tax_no" class="form-label">Tax Identification Number (NPWP)</label>
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-file-invoice"></i></span>
                                        </div>
                                        <input type="text" class="form-control @error('tax_no') is-invalid @enderror"
                                            id="tax_no" name="tax_no" value="{{ old('tax_no', $tax->tax_no) }}"
                                            placeholder="Enter tax number">
                                    </div>
                                    @error('tax_no')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                    <small class="form-text text-muted">Format: 00.000.000.0-000.000</small>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="tax_valid_date" class="form-label">Tax Registration Date</label>
                                    <div class="input-group date">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text"><i class="fas fa-calendar-alt"></i></span>
                                        </div>
                                        <input type="date"
                                            class="form-control @error('tax_valid_date') is-invalid @enderror"
                                            id="tax_valid_date" name="tax_valid_date"
                                            value="{{ old('tax_valid_date', $tax->tax_valid_date) }}">
                                    </div>
                                    @error('tax_valid_date')
                                        <span class="text-danger">{{ $message }}</span>
                                    @enderror
                                </div>
                            </div>
                        </div>
                        <div class="alert alert-warning mt-3">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            Tax information is required for payroll tax deductions and annual tax reporting.
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
