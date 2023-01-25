@include('layouts.partials.header')
<body class="hold-transition layout-top-nav">
  <div class="wrapper">
    <div class="content-wrapper">
      <div class="container-fluid">
        <div class="row">
          <div class="col-3">
            <!-- Profile Image -->
            <div class="card card-primary card-outline">
              <div class="card-body box-profile">
                <div class="text-center">
                  <img class="profile-user-img img-fluid img-circle" src="{{ asset('assets/dist/img/avatar6.png') }}" alt="User profile picture">
                </div>

                <h3 class="profile-username text-center">{{ $employee->fullname }}</h3>

                <p class="text-muted text-center">{{ $employee->identity_card }}</p>

                {{-- <ul class="list-group list-group-unbordered mb-3">
                  <a href="#personal">
                    <li class="list-group-item">Personal Detail</li>
                  </a>
                  <a href="#administration">
                    <li class="list-group-item">Administration</li>
                  </a>
                  <a href="#banks">
                    <li class="list-group-item">Bank Accounts</li>
                  </a>
                  <a href="#tax">
                    <li class="list-group-item">Tax Identification Number</li>
                  </a>
                  <a href="#insurances">
                    <li class="list-group-item">Health Insurances</li>
                  </a>
                  <a href="#licenses">
                    <li class="list-group-item">Licenses</li>
                  </a>
                  <a href="#families">
                    <li class="list-group-item">Families</li>
                  </a>
                  <a href="#educations">
                    <li class="list-group-item">Educations</li>
                  </a>
                  <a href="#courses">
                    <li class="list-group-item">Courses</li>
                  </a>
                  <a href="#jobs">
                    <li class="list-group-item">Job Experiences</li>
                  </a>
                  <a href="#units">
                    <li class="list-group-item">Operable Units</li>
                  </a>
                  <a href="#emergencies">
                    <li class="list-group-item">Emergency Calls</li>
                  </a>
                  <a href="#additional">
                    <li class="list-group-item">Additional Data</li>
                  </a>
                  <a href="#images">
                    <li class="list-group-item">Images</li>
                  </a>
                </ul>
                @can('superadmin')
                <form action="{{ url('employees/'.$employee->id) }}" method="post" onsubmit="return confirm('This employee and all his/her data will be deleted. Are you sure?')" class="d-inline">
                @method('delete')
                @csrf
                <button class="btn btn-danger btn-block"><b>Delete Employee</b></button>
                </form>
                @endcan --}}
              </div>
              <!-- /.card-body -->
            </div>
            <!-- /.card -->
          </div>
          <!-- /.col -->
          <div class="col-9">
            <div class="card card-gray" id="personal">
              <div class="card-header">
                <h2 class="card-title">Personal Detail</h2>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <dl class="row">
                  <dt class="col-sm-3">Full Name</dt>
                  <dd class="col-sm-9">{{ $employee->fullname ?? '-' }}</dd>
                  <dt class="col-sm-3">ID Card No.</dt>
                  <dd class="col-sm-9">{{ $employee->identity_card ?? '-' }}</dd>
                  <dt class="col-sm-3">Place/Date of Birth</dt>
                  <dd class="col-sm-9">{{ $employee->emp_pob ?? '-' }}, {{ date('d-M-Y', strtotime($employee->emp_dob)) ?? '-' }}</dd>
                  <dt class="col-sm-3">Blood Type</dt>
                  <dd class="col-sm-9">{{ $employee->blood_type ?? '-' }}</dd>
                  <dt class="col-sm-3">Religion</dt>
                  <dd class="col-sm-9">{{ $employee->religion->religion_name ?? '-' }}</dd>
                  <dt class="col-sm-3">Nationality</dt>
                  <dd class="col-sm-9">{{ $employee->nationality ?? '-' }}</dd>
                  <dt class="col-sm-3">Gender</dt>
                  <dd class="col-sm-9">{{ $employee->gender == 'male' ? 'Male' : 'Female' }}</dd>
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
            <div class="card card-gray" id="administration">
              <div class="card-header">
                <h2 class="card-title">Administration</h2>
              </div>
              <!-- /.card-header -->
              <div class="card-body table-responsive p-0">
                <table class="table table-hover table-head-fixed">
                  <thead>
                    <tr>
                      <th class="text-center">Status</th>
                      <th>NIK</th>
                      <th>POH</th>
                      <th>DOH</th>
                      <th>Department</th>
                      <th>Position</th>
                      <th>Project</th>
                      <th>Class</th>
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
                      <td class="text-center">
                        @if ($administration->is_active == 1)
                        <span class="badge bg-success">Active</span>
                        @else
                        <span class="badge bg-danger">Inactive</span>
                        @endif
                      </td>
                      <td>{{ $administration->nik }}</td>
                      <td>{{ $administration->poh }}</td>
                      <td>{{ date('d-M-Y', strtotime($administration->doh)) }}</td>
                      <td>{{ $administration->department_name }}</td>
                      <td>{{ $administration->position_name }}</td>
                      <td>{{ $administration->project_code }}</td>
                      <td>{{ $administration->class }}</td>
                    </tr>
                    @endforeach
                    @endif
                  </tbody>
                </table>
              </div>
            </div>
            <div class="card card-gray" id="banks">
              <div class="card-header">
                <h2 class="card-title">Bank Account</h2>
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
            <div class="card card-gray" id="tax">
              <div class="card-header">
                <h2 class="card-title">Tax Identification Number</h2>
              </div>
              <!-- /.card-header -->
              <div class="card-body">
                <dl class="row">
                  <dt class="col-sm-3">Tax Identification No.</dt>
                  <dd class="col-sm-9">{{ $tax->tax_no ?? '-' }}</dd>
                  <dt class="col-sm-3">Valid Date</dt>
                  <dd class="col-sm-9">{{ $tax ? date('d-M-Y', strtotime($tax->tax_valid_date)) : '-' }}</dd>
                </dl>
              </div>
              <!-- /.card-body -->
            </div>
            <div class="card card-gray" id="insurances">
              <div class="card-header">
                <h2 class="card-title">Health Insurance</h2>
              </div>
              <!-- /.card-header -->
              <div class="card-body table-responsive p-0">
                <table class="table table-hover table-head-fixed">
                  <thead>
                    <tr>
                      <th>Insurance</th>
                      <th>Insurance No</th>
                      <th>Health Facility</th>
                      <th>Remarks</th>
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
              </div>
              <!-- /.card-header -->
              <div class="card-body table-responsive p-0">
                <table class="table table-hover table-head-fixed">
                  <thead>
                    <tr>
                      <th>License Type</th>
                      <th>License No</th>
                      <th>Validity Period</th>
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
                      <td>{{ $license->driver_license_exp ? date('d-M-Y', strtotime($license->driver_license_exp)) : '-' }}</td>
                    </tr>
                    @endforeach
                    @endif
                  </tbody>
                </table>
              </div>
            </div>
            <div class="card card-gray" id="families">
              <div class="card-header">
                <h2 class="card-title">Families</h2>
              </div>
              <!-- /.card-header -->
              <div class="card-body table-responsive p-0">
                <table class="table table-hover table-head-fixed">
                  <thead>
                    <tr>
                      <th>Relationship</th>
                      <th>Name</th>
                      <th>Birth Place</th>
                      <th>Birth Date</th>
                      <th>Remarks</th>
                      <th>BPJS Kesehatan</th>
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
                      <td>{{ $family->bpjsks_no }}</td>
                    </tr>
                    @endforeach
                    @endif
                  </tbody>
                </table>
              </div>
              <!-- /.card-body -->
            </div>
            <div class="card card-gray" id="educations">
              <div class="card-header">
                <h2 class="card-title">Educations</h2>
              </div>
              <!-- /.card-header -->
              <div class="card-body table-responsive p-0">
                <table class="table table-hover table-head-fixed">
                  <thead>
                    <tr>
                      <th>Name</th>
                      <th>Address</th>
                      <th>Year</th>
                      <th>Remarks</th>
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
              </div>
              <!-- /.card-header -->
              <div class="card-body table-responsive p-0">
                <table class="table table-hover table-head-fixed">
                  <thead>
                    <tr>
                      <th>Name</th>
                      <th>Address</th>
                      <th>Year</th>
                      <th>Remarks</th>
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
              </div>
              <!-- /.card-header -->
              <div class="card-body table-responsive p-0">
                <table class="table table-hover table-head-fixed">
                  <thead>
                    <tr>
                      <th>Name</th>
                      <th>Address</th>
                      <th>Position</th>
                      <th>Duration</th>
                      <th>Quit Reason</th>
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
              </div>
              <!-- /.card-header -->
              <div class="card-body table-responsive p-0">
                <table class="table table-hover table-head-fixed">
                  <thead>
                    <tr>
                      <th>Unit Name</th>
                      <th>Unit Type / Class</th>
                      <th>Remarks</th>
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
                    </tr>
                    @endforeach
                    @endif
                  </tbody>
                </table>
              </div>
            </div>
            <div class="card card-gray" id="emergencies">
              <div class="card-header">
                <h2 class="card-title">Emergency Calls</h2>
              </div>
              <!-- /.card-header -->
              <div class="card-body table-responsive p-0">
                <table class="table table-hover table-head-fixed">
                  <thead>
                    <tr>
                      <th>Status</th>
                      <th>Full Name</th>
                      <th>Address</th>
                      <th>Phone</th>
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
                    </tr>
                    @endforeach
                    @endif
                  </tbody>
                </table>
              </div>
            </div>
            <div class="card card-gray" id="additional">
              <div class="card-header">
                <h2 class="card-title">Additional Data</h2>
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
            {{-- <div class="card card-gray" id="images">
              <div class="card-header">
                <h2 class="card-title">Images</h2>
              </div>
              <div class="card-body">
                <div class="row">
                  @foreach ($images as $image)
                  <div class="col-sm-3 text-center">
                    <img src="{{ asset('images/'.$image->employee_id.'/'.$image->filename) }}" class="img-fluid mb-2" alt="{{ $image->filename }}" />
          </div>
          @endforeach
        </div>
      </div>
    </div> --}}
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
</body>
@include('layouts.partials.scripts')
