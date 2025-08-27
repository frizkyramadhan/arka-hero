<div class="modal fade text-left" id="modal-termination-{{ $employee->id }}">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-danger">
                <h4 class="modal-title">Terminate Employee</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ url('terminations') }}" method="POST">
                @csrf
                <input type="hidden" name="employee_id" value="{{ old('employee_id', $employee->id) }}">
                <div class="modal-body">
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">Date</label>
                            <div class="col-sm-10">
                                <input type="date"
                                    class="form-control @error('termination_date') is-invalid @enderror"
                                    name="termination_date"
                                    value="{{ old('termination_date', $employee->termination_date) }}">
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
                                        {{ old('termination_reason') == 'End of Contract' ? 'selected' : '' }}>End of
                                        Contract</option>
                                    <option value="End of Project"
                                        {{ old('termination_reason') == 'End of Project' ? 'selected' : '' }}>End of
                                        Project</option>
                                    <option value="Resign"
                                        {{ old('termination_reason') == 'Resign' ? 'selected' : '' }}>Resign</option>
                                    <option value="Termination"
                                        {{ old('termination_reason') == 'Termination' ? 'selected' : '' }}>Termination
                                    </option>
                                    <option value="Retired"
                                        {{ old('termination_reason') == 'Retired' ? 'selected' : '' }}>Retired</option>
                                    <option value="Efficiency"
                                        {{ old('termination_reason') == 'Efficiency' ? 'selected' : '' }}>Efficiency
                                    </option>
                                    <option value="Passed Away"
                                        {{ old('termination_reason') == 'Passed Away' ? 'selected' : '' }}>Passed Away
                                    </option>
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
                                    name="coe_no" value="{{ old('coe_no', $employee->coe_no) }}">
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
                    <button type="submit" class="btn btn-danger"
                        onclick="return confirm('Are you sure to terminate this employee?')">Terminate</button>
                </div>
            </form>
        </div>
    </div>
</div>
