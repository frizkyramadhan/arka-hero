<a class="btn btn-icon btn-primary btn-xs" href="{{ url('additionaldatas/' . $additionaldatas->id . '/show') }}" data-toggle="modal" data-target="#modal-lg-{{ $additionaldatas->id }}"><i class="fas fa fa-eye"></i></a>


<div class="modal fade text-left" id="modal-lg-{{ $additionaldatas->id }}">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Additional Data Employee</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ url('additionaldatas/' . $additionaldatas->id) }}" method="POST">
        <div class="modal-body">
          <div class="card-body">
            <div class="tab-content p-0">
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Employee Name</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="fullname" value="{{ $additionaldatas->fullname }}" readonly>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Cloth Size</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="cloth_size" value="{{ $additionaldatas->cloth_size }}" readonly>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Pants Size</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="pants_size" value="{{ $additionaldatas->pants_size }}" readonly>
                  
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Shoes Size</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="shoes_size" value="{{ $additionaldatas->shoes_size }}" readonly>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Height</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="height" value="{{ $additionaldatas->height }}" readonly>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Weight</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="weight" value="{{ $additionaldatas->weight }}" readonly>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Glasses</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="glasses" value="{{ $additionaldatas->glasses }}" readonly>
                </div>
              </div>
             
            </div>
          </div>
        </div>
        <div class="modal-footer justify-content-between">
          <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
        </div>
      </form>
    </div>
  </div>
</div>
