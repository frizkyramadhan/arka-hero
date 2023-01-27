<a class="btn btn-icon btn-primary btn-xs" href="{{ url('taxidentifications/' . $taxidentifications->id . '/show') }}" data-toggle="modal" data-target="#modal-lg-{{ $taxidentifications->id }}"><i class="fas fa fa-eye"></i></a>


<div class="modal fade text-left" id="modal-lg-{{ $taxidentifications->id }}">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h4 class="modal-title">Employee Tax Identification</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <form action="{{ url('taxidentificationss/' . $taxidentifications->id) }}" method="POST">
        <div class="modal-body">
          <div class="card-body">
            <div class="tab-content p-0">
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Employee Name</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="fullname" value="{{ $taxidentifications->fullname }}" readonly>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Tax No</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="tax_no" value="{{ $taxidentifications->tax_no }}" readonly>
                </div>
              </div>
              <div class="form-group row">
                <label class="col-sm-3 col-form-label">Tax Valid Date</label>
                <div class="col-sm-8">
                  <input type="text" class="form-control" name="tax_valid_date" value="{{  showDateTime($taxidentifications->tax_valid_date, 'l, d F Y') }}" readonly>
                  
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
