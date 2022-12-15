<a class="btn btn-icon btn-primary" href="{{ url('licenses/' . $license->id . '/show') }}" data-toggle="modal" data-target="#modal-lg-{{ $license->id }}"><i class="fas fa-pen-square"></i></a>

<div class="modal fade text-left" id="modal-lg-{{ $license->id }}">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Employee Driver Licensee</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ url('licenses/' . $license->id) }}" method="POST">
        <div class="modal-body">
          <div class="card-body">
            <div class="tab-content p-0">
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Employee Name</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="fullname" value="{{ $license->fullname }}" readonly>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Driver License No</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="driver_license_no" value="{{ $license->driver_license_no }}" readonly>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Driver License Type</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="driver_license_type" value="{{ $license->driver_license_type }}" readonly>
                  
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Driver License Exp</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="driver_license_exp" value="{{  showDateTime($license->driver_license_exp, 'l, d F Y') }}" readonly>
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
