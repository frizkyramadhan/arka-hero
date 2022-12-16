<a class="btn btn-icon btn-primary btn-xs" href="{{ url('administrations/' . $administrations->id . '/show') }}" data-toggle="modal" data-target="#modal-lg-{{ $administrations->id }}"><i class="fas fa fa-eye"></i></a>


<div class="modal fade text-left" id="modal-lg-{{ $administrations->id }}">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Administration Data Employee</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ url('administrations/' . $administrations->id) }}" method="POST">
        <div class="modal-body">
          <div class="card-body">
            <div class="tab-content p-0">
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Employee Name</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="fullname" value="{{ $administrations->fullname }}" readonly>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Project Name</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="project_name" value="{{ $administrations->project_name }}" readonly>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Position Name</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="position_name" value="{{ $administrations->position_name }}" readonly>
                  
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">NIK</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="nik" value="{{ $administrations->nik }}" readonly>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Class</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="class" value="{{ $administrations->class }}" readonly>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">DOH</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="doh" value="{{  showDateTime($administrations->doh, 'l, d F Y') }}" readonly>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">POH</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="poh" value="{{ $administrations->poh }}" readonly>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Basic Salary</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="basic_salary" value="{{ $administrations->basic_salary }}" readonly>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Site Allowance</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="site_allowance" value="{{ $administrations->site_allowance }}" readonly>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Other Allowance</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="other_allowance" value="{{ $administrations->other_allowance }}" readonly>
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
