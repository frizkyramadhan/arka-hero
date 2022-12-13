<a class="btn btn-icon btn-primary" href="{{ url('families/' . $families->id . '/show') }}" data-toggle="modal" data-target="#modal-lg-{{ $families->id }}"><i class="fas fa-pen-square"></i></a>



<div class="modal fade text-left" id="modal-lg-{{ $families->id }}">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Employee Family Detail</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ url('families/' . $families->id) }}" method="POST">
        <div class="modal-body">
          <div class="card-body">
            <div class="tab-content p-0">
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Employee Name</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="fullname" value="{{ $families->fullname }}" readonly>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Family Name</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="family_name" value="{{ $families->family_name }}" readonly>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Family Relationship</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="family_relationship" value="{{ $families->family_relationship }}" readonly>
                  
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Family Birthplace</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="family_birthplace" value="{{ $families->family_birthplace }}" readonly>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Family Birthdate</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="family_birthdate" value="{{ $families->family_birthdate }}" readonly>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Family Remark</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="family_remarks" value="{{ $families->family_remarks }}" readonly>
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
