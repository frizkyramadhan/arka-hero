<a class="btn btn-icon btn-primary btn-xs" href="{{ url('operableunits/' . $operableunits->id . '/show') }}" data-toggle="modal" data-target="#modal-lg-{{ $operableunits->id }}"><i class="fas fa fa-eye"></i></a>

<div class="modal fade text-left" id="modal-lg-{{ $operableunits->id }}">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Employee Operable Unit</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ url('operableunits/' . $operableunits->id) }}" method="POST">
        <div class="modal-body">
          <div class="card-body">
            <div class="tab-content p-0">
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Employee Name</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="fullname" value="{{ $operableunits->fullname }}" readonly>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Unit Name</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="unit_name" value="{{ $operableunits->unit_name }}" readonly>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Unit Type</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="unit_type" value="{{ $operableunits->unit_type }}" readonly>
                  
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Unit Remarks</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="unit_remarks" value="{{ $operableunits->unit_remarks }}" readonly>
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
