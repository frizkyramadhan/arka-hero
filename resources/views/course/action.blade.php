<a class="btn btn-icon btn-primary" href="{{ url('courses/' . $courses->id . '/show') }}" data-toggle="modal" data-target="#modal-lg-{{ $courses->id }}"><i class="fas fa-pen-square"></i></a>


<div class="modal fade text-left" id="modal-lg-{{ $courses->id }}">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Employee Course Details</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ url('courses/' . $courses->id) }}" method="POST">
        <div class="modal-body">
          <div class="card-body">
            <div class="tab-content p-0">
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Employee Name</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="fullname" value="{{ $courses->fullname }}" readonly>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Courses Name</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="course_name" value="{{ $courses->course_name }}" readonly>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Courses Years</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="course_year" value="{{ $courses->course_year }}" readonly>
                  
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Courses Remarks</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="course_remarks" value="{{ $courses->course_remarks }}" readonly>
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
