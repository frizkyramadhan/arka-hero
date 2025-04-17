<div class="modal fade text-left" id="modal-family">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">Edit Employee - Add Family Data</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ url('families/' . $employee->id) }}" method="POST">
                <input type="hidden" name="employee_id" value="{{ old('employee_id', $employee->id) }}">
                @csrf
                <div class="modal-body">
                    <div class="card-body">
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">Relationship</label>
                            <div class="col-sm-10">
                                <select name="family_relationship"
                                    class="form-control @error('family_relationship') is-invalid @enderror">
                                    <option value="Husband"
                                        {{ old('family_relationship') == 'Husband' ? 'selected' : '' }}>Husband
                                    </option>
                                    <option value="Wife" {{ old('family_relationship') == 'Wife' ? 'selected' : '' }}>
                                        Wife
                                    </option>
                                    <option value="Child"
                                        {{ old('family_relationship') == 'Child' ? 'selected' : '' }}>Child
                                    </option>
                                </select>
                                @error('family_relationship')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">Name</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control @error('family_name') is-invalid @enderror"
                                    name="family_name" value="{{ old('family_name') }}">
                                @error('family_name')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">Birth Place</label>
                            <div class="col-sm-10">
                                <input type="text"
                                    class="form-control @error('family_birthplace') is-invalid @enderror"
                                    name="family_birthplace" value="{{ old('family_birthplace') }}">
                                @error('family_birthplace')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">Birth Date</label>
                            <div class="col-sm-10">
                                <input type="date"
                                    class="form-control @error('family_birthdate') is-invalid @enderror"
                                    name="family_birthdate" value="{{ old('family_birthdate') }}">
                                @error('family_birthdate')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">Remarks</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control @error('family_remarks') is-invalid @enderror"
                                    name="family_remarks" value="{{ old('family_remarks') }}">
                                @error('family_remarks')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>
                        </div>
                        <div class="form-group row">
                            <label class="col-sm-2 col-form-label">BPJS Kesehatan</label>
                            <div class="col-sm-10">
                                <input type="text" class="form-control @error('bpjsks_no') is-invalid @enderror"
                                    name="bpjsks_no" value="{{ old('bpjsks_no') }}">
                                @error('bpjsks_no')
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
                    <button type="submit" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>




@foreach ($families as $family)
    <div class="modal fade text-left" id="modal-family-{{ $family->id }}">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">Edit Employee - Edit Family Data</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form action="{{ url('families/' . $family->id) }}" method="POST">
                    <input type="hidden" name="employee_id" value="{{ old('employee_id', $family->employee_id) }}">
                    @csrf
                    @method('PATCH')
                    <div class="modal-body">
                        <div class="card-body">
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Relationship</label>
                                <div class="col-sm-10">
                                    <select name="family_relationship"
                                        class="form-control @error('family_relationship') is-invalid @enderror">
                                        <option value="Husband"
                                            {{ old('family_relationship', $family->family_relationship) == 'Husband' ? 'selected' : '' }}>
                                            Husband
                                        </option>
                                        <option value="Wife"
                                            {{ old('family_relationship', $family->family_relationship) == 'Wife' ? 'selected' : '' }}>
                                            Wife
                                        </option>
                                        <option value="Child"
                                            {{ old('family_relationship', $family->family_relationship) == 'Child' ? 'selected' : '' }}>
                                            Child
                                        </option>
                                    </select>
                                    @error('family_relationship')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Name</label>
                                <div class="col-sm-10">
                                    <input type="text"
                                        class="form-control @error('family_name') is-invalid @enderror"
                                        name="family_name" value="{{ old('family_name', $family->family_name) }}">
                                    @error('family_name')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Birth Place</label>
                                <div class="col-sm-10">
                                    <input type="text"
                                        class="form-control @error('family_birthplace') is-invalid @enderror"
                                        name="family_birthplace"
                                        value="{{ old('family_birthplace', $family->family_birthplace) }}">
                                    @error('family_birthplace')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Birth Date</label>
                                <div class="col-sm-10">
                                    <input type="date"
                                        class="form-control @error('family_birthdate') is-invalid @enderror"
                                        name="family_birthdate"
                                        value="{{ old('family_birthdate', $family->family_birthdate) }}">
                                    @error('family_birthdate')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">Remarks</label>
                                <div class="col-sm-10">
                                    <input type="text"
                                        class="form-control @error('family_remarks') is-invalid @enderror"
                                        name="family_remarks"
                                        value="{{ old('family_remarks', $family->family_remarks) }}">
                                    @error('family_remarks')
                                        <div class="invalid-feedback">
                                            {{ $message }}
                                        </div>
                                    @enderror
                                </div>
                            </div>
                            <div class="form-group row">
                                <label class="col-sm-2 col-form-label">BPJS Kesehatan</label>
                                <div class="col-sm-10">
                                    <input type="text"
                                        class="form-control @error('bpjsks_no') is-invalid @enderror"
                                        name="bpjsks_no" value="{{ old('bpjsks_no', $family->bpjsks_no) }}">
                                    @error('bpjsks_no')
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
@endforeach
