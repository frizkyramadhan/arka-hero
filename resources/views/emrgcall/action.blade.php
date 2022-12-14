<a class="btn btn-icon btn-primary" href="{{ url('emrgcalls/' . $emrgcalls->id . '/show') }}" data-toggle="modal" data-target="#modal-lg-{{ $emrgcalls->id }}"><i class="fas fa-pen-square"></i></a>


<div class="modal fade text-left" id="modal-lg-{{ $emrgcalls->id }}">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Employee Emergency Call</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ url('emrgcalls/' . $emrgcalls->id) }}" method="POST">
        <div class="modal-body">
          <div class="card-body">
            <div class="tab-content p-0">
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Employee Name</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="fullname" value="{{ $emrgcalls->fullname }}" readonly>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Emergency Call Name</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="emrg_call_name" value="{{ $emrgcalls->emrg_call_name }}" readonly>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Emergency Call Relation</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="emrg_call_relation" value="{{ $emrgcalls->emrg_call_relation }}" readonly>
                  
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Emergency Call Phone</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="emrg_call_phone" value="{{ $emrgcalls->emrg_call_phone }}" readonly>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Emergency Call Address</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="emrg_call_address" value="{{ $emrgcalls->emrg_call_address }}" readonly>
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
