@extends('layouts.main')
@section('content')
<div class="content-header">
  <div class="container-fluid">
    <div class="row mb-2">
      <div class="col-sm-6">
        <h1 class="m-0">{{ $subtitle }}</h1>
      </div><!-- /.col -->
      <div class="col-sm-6">
        <ol class="breadcrumb float-sm-right">
          <li class="breadcrumb-item"><a href="#">Home</a></li>
          <li class="breadcrumb-item active">Employee</li>
        </ol>
      </div><!-- /.col -->
    </div><!-- /.row -->
  </div><!-- /.container-fluid -->
</div>
<!-- /.content-header -->

<section class="content">
  <div class="container-fluid">
    <div class="row">
      <div class="col-12 mb-2 text-right">
        <a href="{{ url('employees') }}" class="btn btn-warning"><i class="fas fa-undo"></i>
          Back</a>
      </div>
    </div>
    <div class="row">
      <div class="col-md-3">

        <!-- Profile Image -->
        <div class="card card-primary card-outline">
          <div class="card-body box-profile">
            <div class="text-center">
              <img class="profile-user-img img-fluid img-circle" src="{{ asset('assets/dist/img/avatar6.png') }}" alt="User profile picture">
            </div>

            <h3 class="profile-username text-center">{{ $employee->fullname }}</h3>

            <p class="text-muted text-center">{{ $employee->email }}</p>

            <ul class="list-group list-group-unbordered mb-3">
              <li class="list-group-item"><a href="#personal">Personal Detail</a></li>
              <li class="list-group-item"><a href="#banks">Bank Accounts</a></li>
              <li class="list-group-item"><a href="#insurances">Health Insurances</a></li>
              <li class="list-group-item"><a href="#families">Families</a></li>
              <li class="list-group-item"><a href="#educations">Educations</a></li>
              <li class="list-group-item"><a href="#courses">Courses</a></li>
              <li class="list-group-item"><a href="#jobs">Job Experiences</a></li>
              <li class="list-group-item"><a href="#units">Operable Units</a></li>
              <li class="list-group-item"><a href="#licenses">Licenses</a></li>
              <li class="list-group-item"><a href="#emergencies">Emergency Calls</a></li>
              <li class="list-group-item"><a href="#additional">Additional Data</a></li>
              <li class="list-group-item"><a href="#administration">Administration</a></li>
              <li class="list-group-item"><a href="#images">Images</a></li>
            </ul>

            <a href="#" class="btn btn-danger btn-block"><b>Terminate</b></a>
          </div>
          <!-- /.card-body -->
        </div>
        <!-- /.card -->
      </div>
      <!-- /.col -->
      <div class="col-md-9">
        <div class="card card-gray" id="personal">
          <div class="card-header">
            <h2 class="card-title">Personal Detail</h2>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="maximize"><i class="fas fa-expand"></i>
              </button>
            </div>
            <!-- /.card-tools -->
          </div>
          <!-- /.card-header -->
          <div class="card-body">
            <dl class="row">
              <dt class="col-sm-3">Full Name</dt>
              <dd class="col-sm-9">{{ $employee->fullname ?? '-' }}</dd>
              <dt class="col-sm-3">ID Card No.</dt>
              <dd class="col-sm-9">{{ $employee->identity_card ?? '-' }}</dd>
              <dt class="col-sm-3">Place/Date of Birth</dt>
              <dd class="col-sm-9">{{ $employee->emp_pob ?? '-' }}, {{ date('d-M-Y', strtotime($employee->dob)) ?? '-' }}</dd>
              <dt class="col-sm-3">Blood Type</dt>
              <dd class="col-sm-9">{{ $employee->blood_type ?? '-' }}</dd>
              <dt class="col-sm-3">Religion</dt>
              <dd class="col-sm-9">{{ $employee->religion->religion_name ?? '-' }}</dd>
              <dt class="col-sm-3">Nationality</dt>
              <dd class="col-sm-9">{{ $employee->nationality ?? '-' }}</dd>
              <dt class="col-sm-3">Gender</dt>
              <dd class="col-sm-9">{{ $employee->gender ?? '-' }}</dd>
              <dt class="col-sm-3">Marital</dt>
              <dd class="col-sm-9">{{ $employee->marital ?? '-' }}</dd>
              <dt class="col-sm-3">Address</dt>
              <dd class="col-sm-9">{{ $employee->address ?? '-' }}</dd>
              <dt class="col-sm-3">Village</dt>
              <dd class="col-sm-9">{{ $employee->village ?? '-' }}</dd>
              <dt class="col-sm-3">Ward</dt>
              <dd class="col-sm-9">{{ $employee->ward ?? '-' }}</dd>
              <dt class="col-sm-3">District</dt>
              <dd class="col-sm-9">{{ $employee->district ?? '-' }}</dd>
              <dt class="col-sm-3">City</dt>
              <dd class="col-sm-9">{{ $employee->city ?? '-' }}</dd>
              <dt class="col-sm-3">Phone</dt>
              <dd class="col-sm-9">{{ $employee->phone ?? '-' }}</dd>
              <dt class="col-sm-3">Email</dt>
              <dd class="col-sm-9">{{ $employee->email ?? '-' }}</dd>
            </dl>
          </div>
          <!-- /.card-body -->
        </div>
        <!-- /.card -->
        <div class="card card-gray" id="banks">
          <div class="card-header">
            <h2 class="card-title">Bank Account</h2>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="maximize"><i class="fas fa-expand"></i>
              </button>
            </div>
            <!-- /.card-tools -->
          </div>
          <!-- /.card-header -->
          <div class="card-body">
            <dl class="row">
              <dt class="col-sm-3">Bank</dt>
              <dd class="col-sm-9">{{ $bank->banks->bank_name ?? '-' }}</dd>
              <dt class="col-sm-3">Account No.</dt>
              <dd class="col-sm-9">{{ $bank->bank_account_no ?? '-' }}</dd>
              <dt class="col-sm-3">Account Name</dt>
              <dd class="col-sm-9">{{ $bank->bank_account_name ?? '-' }}</dd>
              <dt class="col-sm-3">Branch</dt>
              <dd class="col-sm-9">{{ $bank->bank_account_branch ?? '-' }}</dd>
            </dl>
          </div>
          <!-- /.card-body -->
        </div>
        <!-- /.card -->
        <div class="card card-gray" id="insurances">
          <div class="card-header">
            <h2 class="card-title">Health Insurance</h2>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="maximize"><i class="fas fa-expand"></i>
              </button>
            </div>
            <!-- /.card-tools -->
          </div>
          <!-- /.card-header -->
          <div class="card-body table-responsive p-0" style="height: 200px;">
            <table class="table table-hover table-head-fixed text-nowrap">
              <thead>
                <tr>
                  <th>Insurance</th>
                  <th>Insurance No</th>
                  <th>Health Facility</th>
                  <th>Remarks</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @if ($insurances->isEmpty())
                <tr>
                  <td colspan="5" class="text-center"><span class="badge bg-warning">No Data Available</span></td>
                </tr>
                @else
                @foreach ($insurances as $insurance)
                <tr>
                  <td>{{ $insurance->health_insurance_type == 'bpjskt' ? 'BPJS Ketenagakerjaan' : 'BPJS Kesehatan'  }}</td>
                  <td>{{ $insurance->health_insurance_no }}</td>
                  <td>{{ $insurance->health_facility }}</td>
                  <td>{{ $insurance->health_insurance_remarks }}</td>
                  <td>Edit | Delete</td>
                </tr>
                @endforeach
                @endif
              </tbody>
            </table>
          </div>
          <!-- /.card-body -->
        </div>
        <!-- /.card -->
        <div class="card card-gray" id="families">
          <div class="card-header">
            <h2 class="card-title">Families</h2>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="maximize"><i class="fas fa-expand"></i>
              </button>
            </div>
            <!-- /.card-tools -->
          </div>
          <!-- /.card-header -->
          <div class="card-body table-responsive p-0" style="height: 200px;">
            <table class="table table-hover table-head-fixed text-nowrap">
              <thead>
                <tr>
                  <th>Relationship</th>
                  <th>Name</th>
                  <th>Birth Place</th>
                  <th>Birth Date</th>
                  <th>Remarks</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @if ($families->isEmpty())
                <tr>
                  <td colspan="6" class="text-center"><span class="badge bg-warning">No Data Available</span></td>
                </tr>
                @else
                @foreach ($families as $family)
                <tr>
                  <td>{{ $family->family_relationship }}</td>
                  <td>{{ $family->family_name }}</td>
                  <td>{{ $family->family_birthplace }}</td>
                  <td>{{ date('d-M-Y', strtotime($family->family_birthdate)) }}</td>
                  <td>{{ $family->family_remarks }}</td>
                  <td>Edit | Delete</td>
                </tr>
                @endforeach
                @endif
              </tbody>
            </table>
          </div>
          <!-- /.card-body -->
        </div>
        <!-- /.card -->
        <div class="card card-gray" id="educations">
          <div class="card-header">
            <h2 class="card-title">Educations</h2>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="maximize"><i class="fas fa-expand"></i>
              </button>
            </div>
            <!-- /.card-tools -->
          </div>
          <!-- /.card-header -->
          <div class="card-body table-responsive p-0" style="height: 200px;">
            <table class="table table-hover table-head-fixed text-nowrap">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Address</th>
                  <th>Year</th>
                  <th>Remarks</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @if ($educations->isEmpty())
                <tr>
                  <td colspan="5" class="text-center"><span class="badge bg-warning">No Data Available</span></td>
                </tr>
                @else
                @foreach ($educations as $education)
                <tr>
                  <td>{{ $education->education_name }}</td>
                  <td>{{ $education->education_address }}</td>
                  <td>{{ $education->education_year }}</td>
                  <td>{{ $education->education_remarks }}</td>
                  <td>Edit | Delete</td>
                </tr>
                @endforeach
                @endif
              </tbody>
            </table>
          </div>
          <!-- /.card-body -->
        </div>
        <div class="card card-gray" id="courses">
          <div class="card-header">
            <h2 class="card-title">Courses</h2>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="maximize"><i class="fas fa-expand"></i>
              </button>
            </div>
            <!-- /.card-tools -->
          </div>
          <!-- /.card-header -->
          <div class="card-body table-responsive p-0" style="height: 200px;">
            <table class="table table-hover table-head-fixed text-nowrap">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Address</th>
                  <th>Year</th>
                  <th>Remarks</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @if ($courses->isEmpty())
                <tr>
                  <td colspan="5" class="text-center"><span class="badge bg-warning">No Data Available</span></td>
                </tr>
                @else
                @foreach ($courses as $course)
                <tr>
                  <td>{{ $course->course_name }}</td>
                  <td>{{ $course->course_address }}</td>
                  <td>{{ $course->course_year }}</td>
                  <td>{{ $course->course_remarks }}</td>
                  <td>Edit | Delete</td>
                </tr>
                @endforeach
                @endif
              </tbody>
            </table>
          </div>
          <!-- /.card-body -->
        </div>
        <div class="card card-gray" id="jobs">
          <div class="card-header">
            <h2 class="card-title">Job Experiences</h2>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="maximize"><i class="fas fa-expand"></i>
              </button>
            </div>
            <!-- /.card-tools -->
          </div>
          <!-- /.card-header -->
          <div class="card-body table-responsive p-0" style="height: 200px;">
            <table class="table table-hover table-head-fixed text-nowrap">
              <thead>
                <tr>
                  <th>Name</th>
                  <th>Address</th>
                  <th>Position</th>
                  <th>Duration</th>
                  <th>Quit Reason</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @if ($jobs->isEmpty())
                <tr>
                  <td colspan="6" class="text-center"><span class="badge bg-warning">No Data Available</span></td>
                </tr>
                @else
                @foreach ($jobs as $job)
                <tr>
                  <td>{{ $job->company_name }}</td>
                  <td>{{ $job->company_address }}</td>
                  <td>{{ $job->job_position }}</td>
                  <td>{{ $job->job_duration }}</td>
                  <td>{{ $job->quit_reason }}</td>
                  <td>Edit | Delete</td>
                </tr>
                @endforeach
                @endif
              </tbody>
            </table>
          </div>
          <!-- /.card-body -->
        </div>
        <div class="card card-gray" id="units">
          <div class="card-header">
            <h2 class="card-title">Operable Units</h2>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="maximize"><i class="fas fa-expand"></i>
              </button>
            </div>
            <!-- /.card-tools -->
          </div>
          <!-- /.card-header -->
          <div class="card-body table-responsive p-0" style="height: 200px;">
            <table class="table table-hover table-head-fixed text-nowrap">
              <thead>
                <tr>
                  <th>Unit Name</th>
                  <th>Unit Type / Class</th>
                  <th>Remarks</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @if ($units->isEmpty())
                <tr>
                  <td colspan="4" class="text-center"><span class="badge bg-warning">No Data Available</span></td>
                </tr>
                @else
                @foreach ($units as $unit)
                <tr>
                  <td>{{ $unit->unit_name }}</td>
                  <td>{{ $unit->unit_type }}</td>
                  <td>{{ $unit->unit_remarks }}</td>
                  <td>Edit | Delete</td>
                </tr>
                @endforeach
                @endif
              </tbody>
            </table>
          </div>
          <!-- /.card-body -->
        </div>
        <div class="card card-gray" id="licenses">
          <div class="card-header">
            <h2 class="card-title">Licenses</h2>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="maximize"><i class="fas fa-expand"></i>
              </button>
            </div>
            <!-- /.card-tools -->
          </div>
          <!-- /.card-header -->
          <div class="card-body table-responsive p-0" style="height: 200px;">
            <table class="table table-hover table-head-fixed text-nowrap">
              <thead>
                <tr>
                  <th>License Type</th>
                  <th>License No</th>
                  <th>Validity Period</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @if ($licenses->isEmpty())
                <tr>
                  <td colspan="4" class="text-center"><span class="badge bg-warning">No Data Available</span></td>
                </tr>
                @else
                @foreach ($licenses as $license)
                <tr>
                  <td>{{ $license->driver_license_type }}</td>
                  <td>{{ $license->driver_license_no }}</td>
                  <td>{{ date('d-M-Y', strtotime($license->driver_license_exp)) }}</td>
                  <td>Edit | Delete</td>
                </tr>
                @endforeach
                @endif
              </tbody>
            </table>
          </div>
          <!-- /.card-body -->
        </div>
        <div class="card card-gray" id="emergencies">
          <div class="card-header">
            <h2 class="card-title">Emergency Calls</h2>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="maximize"><i class="fas fa-expand"></i>
              </button>
            </div>
            <!-- /.card-tools -->
          </div>
          <!-- /.card-header -->
          <div class="card-body table-responsive p-0" style="height: 200px;">
            <table class="table table-hover table-head-fixed text-nowrap">
              <thead>
                <tr>
                  <th>Status</th>
                  <th>Full Name</th>
                  <th>Address</th>
                  <th>Phone</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @if ($emergencies->isEmpty())
                <tr>
                  <td colspan="5" class="text-center"><span class="badge bg-warning">No Data Available</span></td>
                </tr>
                @else
                @foreach ($emergencies as $emergency)
                <tr>
                  <td>{{ $emergency->emrg_call_relation }}</td>
                  <td>{{ $emergency->emrg_call_name }}</td>
                  <td>{{ $emergency->emrg_call_address }}</td>
                  <td>{{ $emergency->emrg_call_phone }}</td>
                  <td>Edit | Delete</td>
                </tr>
                @endforeach
                @endif
              </tbody>
            </table>
          </div>
          <!-- /.card-body -->
        </div>
        <div class="card card-gray" id="additional">
          <div class="card-header">
            <h2 class="card-title">Additional Data</h2>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="maximize"><i class="fas fa-expand"></i>
              </button>
            </div>
            <!-- /.card-tools -->
          </div>
          <!-- /.card-header -->
          <div class="card-body">
            <dl class="row">
              <dt class="col-sm-3">Cloth Size</dt>
              <dd class="col-sm-9">{{ $additional->cloth_size ?? '-' }}</dd>
              <dt class="col-sm-3">Pants Size</dt>
              <dd class="col-sm-9">{{ $additional->pants_size ?? '-' }}</dd>
              <dt class="col-sm-3">Shoes Size</dt>
              <dd class="col-sm-9">{{ $additional->shoes_size ?? '-' }}</dd>
              <dt class="col-sm-3">Height</dt>
              <dd class="col-sm-9">{{ $additional->height ?? '-' }}</dd>
              <dt class="col-sm-3">Weight</dt>
              <dd class="col-sm-9">{{ $additional->weight ?? '-' }}</dd>
              <dt class="col-sm-3">Glasses</dt>
              <dd class="col-sm-9">{{ $additional->glasses ?? '-' }}</dd>
            </dl>
          </div>
          <!-- /.card-body -->
        </div>
        <div class="card card-gray" id="administration">
          <div class="card-header">
            <h2 class="card-title">Administration</h2>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="maximize"><i class="fas fa-expand"></i>
              </button>
            </div>
            <!-- /.card-tools -->
          </div>
          <!-- /.card-header -->
          <div class="card-body table-responsive p-0" style="height: 200px;">
            <table class="table table-hover table-head-fixed text-nowrap">
              <thead>
                <tr>
                  <th>NIK</th>
                  <th>POH</th>
                  <th>DOH</th>
                  <th>Department</th>
                  <th>Position</th>
                  <th>Project</th>
                  <th>Class</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @if ($administrations->isEmpty())
                <tr>
                  <td colspan="8" class="text-center"><span class="badge bg-warning">No Data Available</span></td>
                </tr>
                @else
                @foreach ($administrations as $administration)
                <tr>
                  <td>{{ $administration->nik }}</td>
                  <td>{{ $administration->poh }}</td>
                  <td>{{ date('d-M-Y', strtotime($administration->doh)) }}</td>
                  <td>{{ $administration->department_name }}</td>
                  <td>{{ $administration->position_name }}</td>
                  <td>{{ $administration->project_code }}</td>
                  <td>{{ $administration->class }}</td>
                  <td>Edit | Delete</td>
                </tr>
                @endforeach
                @endif
              </tbody>
            </table>
          </div>
          <!-- /.card-body -->
        </div>
        <div class="card card-gray" id="images">
          <div class="card-header">
            <h2 class="card-title">Images</h2>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="maximize"><i class="fas fa-expand"></i>
              </button>
            </div>
            <!-- /.card-tools -->
          </div>
          <!-- /.card-header -->
          <div class="card-body">
            <div class="container">
              <div class="row justify-content-between">
                <div class="col-6">
                  <div class="row">
                    <form class="form-horizontal" action="{{ url('employees/addImages/' . $employee->id) }}" method="POST" enctype="multipart/form-data">
                      @csrf
                      <div class="form-group">
                        <div class="input-group">
                          <div>
                            <input type="file" name="filename[]" multiple>
                          </div>
                          <div class="input-group-append">
                            <button type="submit" class="btn btn-sm btn-primary">Submit</button>
                          </div>
                        </div>
                      </div>
                    </form>
                  </div>
                </div>
                <div class="col-6 text-right">
                  <a href="{{ url('employees/deleteImages/' . $employee->id) }}" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete all images?');"><i class="fas fa-trash"></i> Delete All</a>
                </div>
              </div>
            </div>
            <div class="row">
              @foreach ($images as $image)
              <div class="col-sm-2 text-center">
                <a href="{{ asset('images/'.$image->employee_id.'/'.$image->filename) }}" data-toggle="lightbox" data-title="{{ $image->filename }}" data-gallery="gallery">
                  <img src="{{ asset('images/'.$image->employee_id.'/'.$image->filename) }}" class="img-fluid mb-2" alt="{{ $image->filename }}" />
                </a>
                <a href="{{ url('employees/deleteImage/' . $image->id) }}" class="btn btn-danger btn-sm mb-2" onclick="return confirm('Are you sure you want to delete this image?');"><i class="fas fa-trash"></i> Delete</a>
              </div>
              @endforeach
            </div>
          </div>
          <!-- /.card-body -->
        </div>
        <!-- /.card -->
      </div>
      <!-- /.card -->
    </div>
    <!-- /.col -->
  </div>
  <!-- /.col -->
  </div>
  <!-- /.row -->
  </div>
  <!-- /.container-fluid -->

  <a id="back-to-top" href="#" class="btn btn-primary back-to-top" role="button" aria-label="Scroll to top">
    <i class="fas fa-chevron-up"></i>
  </a>
</section>
@endsection

@section('styles')
<!-- Ekko Lightbox -->
<link rel="stylesheet" href="{{ asset('assets/plugins/ekko-lightbox/ekko-lightbox.css') }}">
@endsection

@section('scripts')
<!-- Ekko Lightbox -->
<script src="{{ asset('assets/plugins/ekko-lightbox/ekko-lightbox.min.js') }}"></script>
<script>
  $(function() {
    $(document).on('click', '[data-toggle="lightbox"]', function(event) {
      event.preventDefault();
      $(this).ekkoLightbox({
        alwaysShowClose: true
      });
    });
  })

</script>
@endsection
