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
          <div class="card-body">
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Tax No.</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('tax_no') is-invalid @enderror" name="tax_no" value="{{ old('tax_no') }}">
                @error('tax_no')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Tax Valid Date</label>
              <div class="col-sm-10">
                <input type="date" class="form-control @error('tax_valid_date') is-invalid @enderror" name="tax_valid_date" value="{{ old('tax_valid_date') }}">
                @error('tax_valid_date')
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
          <div class="card-body">
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Tax No.</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('tax_no') is-invalid @enderror" name="tax_no" value="{{ old('tax_no', $tax->tax_no) }}">
                @error('tax_no')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Tax Valid Date</label>
              <div class="col-sm-10">
                <input type="date" class="form-control @error('tax_valid_date') is-invalid @enderror" name="tax_valid_date" value="{{ old('tax_valid_date', $tax->tax_valid_date) }}">
                @error('tax_valid_date')
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
@endif
