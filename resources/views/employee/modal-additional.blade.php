<div class="modal fade text-left" id="modal-additional-{{ $additional->id }}">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Edit Employee - Additional Data</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ url('additionaldatas/' . $additional->id) }}" method="POST">
        <input type="hidden" name="employee_id" value="{{ old('employee_id', $additional->employee_id) }}">
        @csrf
        @method('PATCH')
        <div class="modal-body">
          <div class="card-body">
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Cloth Size</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('cloth_size') is-invalid @enderror" name="cloth_size" value="{{ old('cloth_size', $additional->cloth_size) }}">
                @error('cloth_size')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Pants Size</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('pants_size') is-invalid @enderror" name="pants_size" value="{{ old('pants_size', $additional->pants_size) }}">
                @error('pants_size')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Shoes Size</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('shoes_size') is-invalid @enderror" name="shoes_size" value="{{ old('shoes_size', $additional->shoes_size) }}">
                @error('shoes_size')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Height</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('height') is-invalid @enderror" name="height" value="{{ old('height', $additional->height) }}">
                @error('height')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Weight</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('weight') is-invalid @enderror" name="weight" value="{{ old('weight', $additional->weight) }}">
                @error('weight')
                <div class="invalid-feedback">
                  {{ $message }}
                </div>
                @enderror
              </div>
            </div>
            <div class="form-group row">
              <label class="col-sm-2 col-form-label">Glasses</label>
              <div class="col-sm-10">
                <input type="text" class="form-control @error('glasses') is-invalid @enderror" name="glasses" value="{{ old('glasses', $additional->glasses) }}">
                @error('glasses')
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
