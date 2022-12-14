<a class="btn btn-icon btn-primary" href="{{ url('employeebanks/' . $employeebanks->id . '/show') }}" data-toggle="modal" data-target="#modal-lg-{{ $employeebanks->id }}"><i class="fas fa-pen-square"></i></a>


<div class="modal fade text-left" id="modal-lg-{{ $employeebanks->id }}">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Employee Bank Details</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ url('employeebanks/' . $employeebanks->id) }}" method="POST">
        <div class="modal-body">
          <div class="card-body">
            <div class="tab-content p-0">
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Employee Name</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="fullname" value="{{ $employeebanks->fullname }}" readonly>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Bank Name</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="bank_name" value="{{ $employeebanks->bank_name }}" readonly>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Bank Account No</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="bank_account_no" value="{{ $employeebanks->bank_account_no }}" readonly>
                  
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Bank Account Name</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="bank_account_name" value="{{ $employeebanks->bank_account_name }}" readonly>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Bank Account Branch</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="bank_account_branch" value="{{ $employeebanks->bank_account_branch }}" readonly>
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
