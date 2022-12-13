<a class="btn btn-icon btn-primary" href="{{ url('insurances/' . $insurances->id . '/show') }}" data-toggle="modal" data-target="#modal-lg-{{ $insurances->id }}"><i class="fas fa-pen-square"></i></a>


<div class="modal fade text-left" id="modal-lg-{{ $insurances->id }}">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Employee Insurance Details</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ url('insurances/' . $insurances->id) }}" method="POST">
        <div class="modal-body">
          <div class="card-body">
            <div class="tab-content p-0">
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Employee Name</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="fullname" value="{{ $insurances->fullname }}" readonly>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Health Insurance Type</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="health_insurance_type" value="{{ $insurances->health_insurance_type }}" readonly>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Health Insurance No</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="health_insurance_no" value="{{ $insurances->health_insurance_no }}" readonly>
                  
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Health Facility</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="health_facility" value="{{ $insurances->health_facility }}" readonly>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Insurance Remarks</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="health_insurance_remarks" value="{{ $insurances->health_insurance_remarks }}" readonly>
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
