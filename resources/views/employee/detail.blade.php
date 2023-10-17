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
        <a href="{{ url('employees/print/'. $employee->id) }}" class="btn btn-primary" target="blank"><i class="fas fa-print"></i>
          Print</a>
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
              @if ($profile)
              <img class="img-thumbnail" src="{{ asset('images/'.$profile->employee_id.'/'.$profile->filename) }}" alt="User profile picture">
              @else
              <img class="profile-user-img img-fluid img-circle" src="{{ asset('assets/dist/img/avatar6.png') }}" alt="User profile picture">
              @endif
            </div>

            <h3 class="profile-username text-center">{{ $employee->fullname }}</h3>

            <p class="text-muted text-center">{{ $employee->identity_card }}</p>

            <ul class="list-group list-group-unbordered mb-3">
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
            @endcan
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
          <div class="card-footer">
            <div class="col-12 text-right">
              <a class="btn btn-sm btn-icon btn-primary" data-toggle="modal" data-target="#modal-employee-{{ $employee->id }}"><i class="fas fa-pen-square"></i></a>
            </div>
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
                  <th class="text-center">Status</th>
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
                  <td class="text-center">
                    @if ($administration->is_active == 1)
                    <span class="badge bg-success">Active</span>
                    @else
                    <form action="{{ url('administrations/changeStatus/'.$employee->id.'/'.$administration->id) }}" method="POST">
                      @csrf
                      @method('PATCH')
                      <button type="submit" class="badge bg-danger">Inactive</button>
                    </form>
                    @endif
                  </td>
                  <td>{{ $administration->nik }}</td>
                  <td>{{ $administration->poh }}</td>
                  <td>{{ date('d-M-Y', strtotime($administration->doh)) }}</td>
                  <td>{{ $administration->department_name }}</td>
                  <td>{{ $administration->position_name }}</td>
                  <td>{{ $administration->project_code }}</td>
                  <td>{{ $administration->class }}</td>
                  <td>
                    <a class=" btn btn-sm btn-icon btn-primary" data-toggle="modal" data-target="#modal-administration-{{ $administration->id }}"><i class="fas fa-pen-square"></i></a>
                    <form action="{{ url('administrations/'.$employee->id.'/'.$administration->id) }}" method="post" onsubmit="return confirm('Are you sure want to delete this data?')" class="d-inline">
                      @method('delete')
                      @csrf
                      <button class="btn btn-sm btn-icon btn-danger"><i class="fas fa-times"></i></button>
                    </form>
                  </td>
                </tr>
                @endforeach
                @endif
              </tbody>
            </table>
          </div>
          <div class="card-footer">
            <div class="col-12 text-right">
              <a class="btn btn-sm btn-icon btn-warning" data-toggle="modal" data-target="#modal-administration" title="Add Administration Data"><i class="fas fa-plus"></i></a>
              @if ($administrations->isNotEmpty())
              <form action="{{ url('administrations/'.$employee->id) }}" method="post" onsubmit="return confirm('Are you sure want to delete all this administration data?')" class="d-inline">
                @method('delete')
                @csrf
                <button class="btn btn-sm btn-icon btn-danger" title="Delete Administration Data"><i class="fas fa-trash"></i></button>
              </form>
              @endif
            </div>
          </div>
        </div>
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
          <div class="card-footer">
            <div class="col-12 text-right">
              @if ($bank == null)
              <a class="btn btn-sm btn-icon btn-warning" data-toggle="modal" data-target="#modal-bank"><i class="fas fa-plus"></i></a>
              @else
              <a class="btn btn-sm btn-icon btn-primary" data-toggle="modal" data-target="#modal-bank-{{ $bank->id }}"><i class="fas fa-pen-square"></i></a>
              <form action="{{ url('employeebanks/'.$employee->id.'/'.$bank->id) }}" method="post" onsubmit="return confirm('Are you sure want to delete this bank account data?')" class="d-inline">
                @method('delete')
                @csrf
                <button class="btn btn-sm btn-icon btn-danger" title="Delete Bank Account Data"><i class="fas fa-trash"></i></button>
              </form>
              @endif
            </div>
          </div>
          <!-- /.card-body -->
        </div>
        <div class="card card-gray" id="tax">
          <div class="card-header">
            <h2 class="card-title">Tax Identification Number</h2>
            <div class="card-tools">
              <button type="button" class="btn btn-tool" data-card-widget="maximize"><i class="fas fa-expand"></i>
              </button>
            </div>
            <!-- /.card-tools -->
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
          <div class="card-footer">
            <div class="col-12 text-right">
              @if ($tax == null)
              <a class="btn btn-sm btn-icon btn-warning" data-toggle="modal" data-target="#modal-tax"><i class="fas fa-plus"></i></a>
              @else
              <a class="btn btn-sm btn-icon btn-primary" data-toggle="modal" data-target="#modal-tax-{{ $tax->id }}"><i class="fas fa-pen-square"></i></a>
              <form action="{{ url('taxidentifications/'.$employee->id.'/'.$tax->id) }}" method="post" onsubmit="return confirm('Are you sure want to delete this tax identification data?')" class="d-inline">
                @method('delete')
                @csrf
                <button class="btn btn-sm btn-icon btn-danger" title="Delete Tax Identification Data"><i class="fas fa-trash"></i></button>
              </form>
              @endif
            </div>
          </div>
          <!-- /.card-body -->
        </div>
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
                  <td>
                    <a class="btn btn-sm btn-icon btn-primary" data-toggle="modal" data-target="#modal-insurance-{{ $insurance->id }}"><i class="fas fa-pen-square"></i></a>
                    <form action="{{ url('insurances/'.$employee->id.'/'. $insurance->id) }}" method="post" onsubmit="return confirm('Are you sure want to delete this data?')" class="d-inline">
                      @method('delete')
                      @csrf
                      <button class="btn btn-sm btn-icon btn-danger"><i class="fas fa-times"></i></button>
                    </form>
                  </td>
                </tr>
                @endforeach
                @endif
              </tbody>
            </table>
          </div>
          <!-- /.card-body -->
          <div class="card-footer">
            <div class="col-12 text-right">
              <a class="btn btn-sm btn-icon btn-warning" data-toggle="modal" data-target="#modal-insurance" title="Add Insurance"><i class="fas fa-plus"></i></a>
              @if ($insurances->isNotEmpty())
              <form action="{{ url('insurances/'.$employee->id) }}" method="post" onsubmit="return confirm('Are you sure want to delete all this insurance data?')" class="d-inline">
                @method('delete')
                @csrf
                <button class="btn btn-sm btn-icon btn-danger" title="Delete Insurance Data"><i class="fas fa-trash"></i></button>
              </form>
              @endif
            </div>
          </div>
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
                  <td>{{ $license->driver_license_exp ? date('d-M-Y', strtotime($license->driver_license_exp)) : '-' }}</td>
                  <td>
                    <a class="btn btn-sm btn-icon btn-primary" href="{{ url('licenses/' . $license->id . '/edit') }}" data-toggle="modal" data-target="#modal-license-{{ $license->id }}"><i class="fas fa-pen-square"></i></a>
                    <form action="{{ url('licenses/'.$employee->id.'/'.$license->id) }}" method="post" onsubmit="return confirm('Are you sure want to delete this data?')" class="d-inline">
                      @method('delete')
                      @csrf
                      <button class="btn btn-sm btn-icon btn-danger"><i class="fas fa-times"></i></button>
                    </form>
                  </td>
                </tr>
                @endforeach
                @endif
              </tbody>
            </table>
          </div>
          <div class="card-footer">
            <div class="col-12 text-right">
              <a class="btn btn-sm btn-icon btn-warning" data-toggle="modal" data-target="#modal-license" title="Add License"><i class="fas fa-plus"></i></a>
              @if ($licenses->isNotEmpty())
              <form action="{{ url('licenses/'.$employee->id) }}" method="post" onsubmit="return confirm('Are you sure want to delete all this license data?')" class="d-inline">
                @method('delete')
                @csrf
                <button class="btn btn-sm btn-icon btn-danger" title="Delete License Data"><i class="fas fa-trash"></i></button>
              </form>
              @endif
            </div>
          </div>
        </div>
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
                  <th>BPJS Kesehatan</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                @if ($families->isEmpty())
                <tr>
                  <td colspan="7" class="text-center"><span class="badge bg-warning">No Data Available</span></td>
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
                  <td>
                    <a class="btn btn-sm btn-icon btn-primary" data-toggle="modal" data-target="#modal-family-{{ $family->id }}"><i class="fas fa-pen-square"></i></a>
                    <form action="{{ url('families/'.$employee->id.'/'. $family->id) }}" method="post" onsubmit="return confirm('Are you sure want to delete this data?')" class="d-inline">
                      @method('delete')
                      @csrf
                      <button class="btn btn-sm btn-icon btn-danger"><i class="fas fa-times"></i></button>
                    </form>
                  </td>
                </tr>
                @endforeach
                @endif
              </tbody>
            </table>
          </div>
          <!-- /.card-body -->
          <div class="card-footer">
            <div class="col-12 text-right">
              <a class="btn btn-sm btn-icon btn-warning" data-toggle="modal" data-target="#modal-family" title="Add Family"><i class="fas fa-plus"></i></a>
              @if ($families->isNotEmpty())
              <form action="{{ url('families/'.$employee->id) }}" method="post" onsubmit="return confirm('Are you sure want to delete all this family data?')" class="d-inline">
                @method('delete')
                @csrf
                <button class="btn btn-sm btn-icon btn-danger" title="Delete Family Data"><i class="fas fa-trash"></i></button>
              </form>
              @endif
            </div>
          </div>
        </div>
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
                  <td>
                    <a class="btn btn-sm btn-icon btn-primary" data-toggle="modal" data-target="#modal-education-{{ $education->id }}"><i class="fas fa-pen-square"></i></a>
                    <form action="{{ url('educations/'.$employee->id.'/'. $education->id) }}" method="post" onsubmit="return confirm('Are you sure want to delete this data?')" class="d-inline">
                      @method('delete')
                      @csrf
                      <button class="btn btn-sm btn-icon btn-danger"><i class="fas fa-times"></i></button>
                    </form>
                  </td>
                </tr>
                @endforeach
                @endif
              </tbody>
            </table>
          </div>
          <!-- /.card-body -->
          <div class="card-footer">
            <div class="col-12 text-right">
              <a class="btn btn-sm btn-icon btn-warning" data-toggle="modal" data-target="#modal-education" title="Add Education"><i class="fas fa-plus"></i></a>
              @if ($educations->isNotEmpty())
              <form action="{{ url('educations/'.$employee->id) }}" method="post" onsubmit="return confirm('Are you sure want to delete all this education data?')" class="d-inline">
                @method('delete')
                @csrf
                <button class="btn btn-sm btn-icon btn-danger" title="Delete Education Data"><i class="fas fa-trash"></i></button>
              </form>
              @endif
            </div>
          </div>
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
                  <td>
                    <a class="btn btn-sm btn-icon btn-primary" href="{{ url('courses/' . $course->id . '/edit') }}" data-toggle="modal" data-target="#modal-course-{{ $course->id }}"><i class="fas fa-pen-square"></i></a>
                    <form action="{{ url('courses/'.$employee->id.'/'.$course->id) }}" method="post" onsubmit="return confirm('Are you sure want to delete this data?')" class="d-inline">
                      @method('delete')
                      @csrf
                      <button class="btn btn-sm btn-icon btn-danger"><i class="fas fa-times"></i></button>
                    </form>
                  </td>
                </tr>
                @endforeach
                @endif
              </tbody>
            </table>
          </div>
          <!-- /.card-body -->
          <div class="card-footer">
            <div class="col-12 text-right">
              <a class="btn btn-sm btn-icon btn-warning" data-toggle="modal" data-target="#modal-course" title="Add Course"><i class="fas fa-plus"></i></a>
              @if ($courses->isNotEmpty())
              <form action="{{ url('courses/'.$employee->id) }}" method="post" onsubmit="return confirm('Are you sure want to delete all this course data?')" class="d-inline">
                @method('delete')
                @csrf
                <button class="btn btn-sm btn-icon btn-danger" title="Delete Course Data"><i class="fas fa-trash"></i></button>
              </form>
              @endif
            </div>
          </div>
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
                  <td>
                    <a class="btn btn-sm btn-icon btn-primary" href="{{ url('jobexperiences/' . $job->id . '/edit') }}" data-toggle="modal" data-target="#modal-job-{{ $job->id }}"><i class="fas fa-pen-square"></i></a>
                    <form action="{{ url('jobexperiences/'.$employee->id.'/'. $job->id) }}" method="post" onsubmit="return confirm('Are you sure want to delete this data?')" class="d-inline">
                      @method('delete')
                      @csrf
                      <button class="btn btn-sm btn-icon btn-danger"><i class="fas fa-times"></i></button>
                    </form>
                  </td>
                </tr>
                @endforeach
                @endif
              </tbody>
            </table>
          </div>
          <!-- /.card-body -->
          <div class="card-footer">
            <div class="col-12 text-right">
              <a class="btn btn-sm btn-icon btn-warning" data-toggle="modal" data-target="#modal-job" title="Add Job Experience"><i class="fas fa-plus"></i></a>
              @if ($jobs->isNotEmpty())
              <form action="{{ url('jobexperiences/'.$employee->id) }}" method="post" onsubmit="return confirm('Are you sure want to delete all this job experience data?')" class="d-inline">
                @method('delete')
                @csrf
                <button class="btn btn-sm btn-icon btn-danger" title="Delete Job Experience Data"><i class="fas fa-trash"></i></button>
              </form>
              @endif
            </div>
          </div>
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
                  <td>
                    <a class="btn btn-sm btn-icon btn-primary" data-toggle="modal" data-target="#modal-unit-{{ $unit->id }}"><i class="fas fa-pen-square"></i></a>
                    <form action="{{ url('operableunits/'.$employee->id.'/'. $unit->id) }}" method="post" onsubmit="return confirm('Are you sure want to delete this data?')" class="d-inline">
                      @method('delete')
                      @csrf
                      <button class="btn btn-sm btn-icon btn-danger"><i class="fas fa-times"></i></button>
                    </form>
                  </td>
                </tr>
                @endforeach
                @endif
              </tbody>
            </table>
          </div>
          <div class="card-footer">
            <div class="col-12 text-right">
              <a class="btn btn-sm btn-icon btn-warning" data-toggle="modal" data-target="#modal-unit" title="Add Operable Unit"><i class="fas fa-plus"></i></a>
              @if ($units->isNotEmpty())
              <form action="{{ url('operableunits/'.$employee->id) }}" method="post" onsubmit="return confirm('Are you sure want to delete all this operable unit data?')" class="d-inline">
                @method('delete')
                @csrf
                <button class="btn btn-sm btn-icon btn-danger" title="Delete Operable Unit Data"><i class="fas fa-trash"></i></button>
              </form>
              @endif
            </div>
          </div>
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
                  <td>
                    <a class="btn btn-sm btn-icon btn-primary" data-toggle="modal" data-target="#modal-emergency-{{ $emergency->id }}"><i class="fas fa-pen-square"></i></a>
                    <form action="{{ url('emrgcalls/'.$employee->id.'/'.$emergency->id) }}" method="post" onsubmit="return confirm('Are you sure want to delete this data?')" class="d-inline">
                      @method('delete')
                      @csrf
                      <button class="btn btn-sm btn-icon btn-danger"><i class="fas fa-times"></i></button>
                    </form>
                  </td>
                </tr>
                @endforeach
                @endif
              </tbody>
            </table>
          </div>
          <div class="card-footer">
            <div class="col-12 text-right">
              <a class="btn btn-sm btn-icon btn-warning" data-toggle="modal" data-target="#modal-emergency" title="Add Emergency Call"><i class="fas fa-plus"></i></a>
              @if ($emergencies->isNotEmpty())
              <form action="{{ url('emrgcalls/'.$employee->id) }}" method="post" onsubmit="return confirm('Are you sure want to delete all this emergency data?')" class="d-inline">
                @method('delete')
                @csrf
                <button class="btn btn-sm btn-icon btn-danger" title="Delete Emergency Data"><i class="fas fa-trash"></i></button>
              </form>
              @endif
            </div>
          </div>
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
          <div class="card-footer">
            <div class="col-12 text-right">
              @if ($additional == null)
              <a class="btn btn-sm btn-icon btn-warning" data-toggle="modal" data-target="#modal-additional"><i class="fas fa-plus"></i></a>
              @else
              <a class="btn btn-sm btn-icon btn-primary" data-toggle="modal" data-target="#modal-additional-{{ $additional->id }}"><i class="fas fa-pen-square"></i></a>
              <form action="{{ url('additionaldatas/'.$employee->id.'/'.$additional->id) }}" method="post" onsubmit="return confirm('Are you sure want to delete this additional data?')" class="d-inline">
                @method('delete')
                @csrf
                <button class="btn btn-sm btn-icon btn-danger" title="Delete Additional Data"><i class="fas fa-trash"></i></button>
              </form>
              @endif
            </div>
          </div>
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
                  <a href="{{ url('employees/deleteImages/' . $employee->id) }}" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete all images?');"><i class="fas fa-trash"></i> Delete All Images</a>
                </div>
              </div>
            </div>
            <div class="row">
              @foreach ($images as $image)
              <div class="col-sm-3 text-center">
                <a href="{{ asset('images/'.$image->employee_id.'/'.$image->filename) }}" data-toggle="lightbox" data-title="{{ $image->filename }}" data-gallery="gallery">
                  <img src="{{ asset('images/'.$image->employee_id.'/'.$image->filename) }}" class="img-fluid mb-2" alt="{{ $image->filename }}" />
                </a>
                @if ($image->is_profile == 0)
                <a href="{{ url('employees/setProfile/' . $employee->id.'/'.$image->id) }}" class="btn btn-primary btn-sm mb-2" title="Set Profile Picture"><i class="fas fa-id-badge"></i></a>
                @endif
                <a href="{{ url('employees/deleteImage/' . $employee->id.'/'.$image->id) }}" class="btn btn-danger btn-sm mb-2" onclick="return confirm('Are you sure you want to delete this image?');" title="Delete Image"><i class="fas fa-trash"></i></a>
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
@include('employee.modal-employee')
@include('employee.modal-bank')
@include('employee.modal-tax')
@include('employee.modal-insurance')
@include('employee.modal-family')
@include('employee.modal-education')
@include('employee.modal-course')
@include('employee.modal-job')
@include('employee.modal-unit')
@include('employee.modal-license')
@include('employee.modal-emergency')
@include('employee.modal-additional')
@include('employee.modal-administration')

@endsection

@section('styles')
<!-- Ekko Lightbox -->
<link rel="stylesheet" href="{{ asset('assets/plugins/ekko-lightbox/ekko-lightbox.css') }}">
<!-- Select2 -->
<link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
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
<!-- Select2 -->
<script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>
<script>
  $(function() {
    // select2
    $('.select2').select2()
    $('.select2bs4').select2({
      theme: 'bootstrap4'
    })
    $(document).on('select2:open', () => {
      document.querySelector('.select2-search__field').focus();
    })


    // autofill department placeholder based on position_id on add modal
    $('.position_id').on('change', function() {
      var position_id = $('.position_id').val();
      $.ajax({
        url: "{{ route('employees.getDepartment') }}"
        , type: "GET"
        , data: {
          position_id: position_id
        }
        , success: function(data) {
          console.log(position_id);
          console.log(data.department_name);
          // set value to department the data
          $('.department').val(data.department_name);
        }
      });
    });

    // autofill department placeholder based on position_id on edit modal
    @foreach($administrations as $administration)
    $(document).ready(function() {
      var position_id = $('.position_id{{ $administration->id }}').val();
      $.ajax({
        url: "{{ route('employees.getDepartment') }}"
        , type: "GET"
        , data: {
          position_id: position_id
        }
        , success: function(data) {
          console.log(position_id);
          console.log(data.department_name);
          // set value to department the data
          $('.department{{ $administration->id }}').val(data.department_name);
        }
      });
    });
    $('.position_id{{ $administration->id }}').on('change', function() {
      var position_id = $('.position_id{{ $administration->id }}').val();
      $.ajax({
        url: "{{ route('employees.getDepartment') }}"
        , type: "GET"
        , data: {
          position_id: position_id
        }
        , success: function(data) {
          console.log(position_id);
          console.log(data.department_name);
          // set value to department the data
          $('.department{{ $administration->id }}').val(data.department_name);
        }
      });
    });
    @endforeach
  });

</script>
@endsection
