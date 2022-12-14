<a class="btn btn-icon btn-primary" href="{{ url('jobexperiences/' . $jobexperiences->id . '/show') }}" data-toggle="modal" data-target="#modal-lg-{{ $jobexperiences->id }}"><i class="fas fa-pen-square"></i></a>



<div class="modal fade text-left" id="modal-lg-{{ $jobexperiences->id }}">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Employee Job Experience Detail</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ url('jobexperiences/' . $jobexperiences->id) }}" method="POST">
        <div class="modal-body">
          <div class="card-body">
            <div class="tab-content p-0">
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Employee Name</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="fullname" value="{{ $jobexperiences->fullname }}" readonly>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Company Name</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="company_name" value="{{ $jobexperiences->company_name }}" readonly>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Job Position</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="job_position" value="{{ $jobexperiences->job_position }}" readonly>
                  
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Job Duration</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="job_duration" value="{{ $jobexperiences->job_duration }}" readonly>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Quit Reason</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="quit_reason" value="{{ $jobexperiences->quit_reason }}" readonly>
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
