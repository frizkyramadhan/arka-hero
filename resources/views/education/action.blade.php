<a class="btn btn-icon btn-primary btn-xs" data-toggle="modal" data-target="#modal-lg-{{ $educations->id }}"><i class="fas fa fa-eye"></i></a>


<div class="modal fade text-left" id="modal-lg-{{ $educations->id }}">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Employee Education Details</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ url('educations/' . $educations->id) }}" method="POST">
        <div class="modal-body">
          <div class="card-body">
            <div class="tab-content p-0">
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Employee Name</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="fullname" value="{{ $educations->fullname }}" readonly>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Education Address</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="education_address" value="{{ $educations->education_address }}" readonly>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Education Name</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="education_name" value="{{ $educations->education_name }}" readonly>

                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Education Years</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="education_year" value="{{ $educations->education_year }}" readonly>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Education Remarks</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="education_remarks" value="{{ $educations->education_remarks }}" readonly>
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
