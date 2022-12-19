<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Image;
use App\Models\Course;
use App\Models\Family;
use App\Models\License;
use App\Models\Project;
use App\Models\Employee;
use App\Models\Emrgcall;
use App\Models\Position;
use App\Models\Religion;
use App\Models\Education;
use App\Models\Insurance;
use App\Models\Department;
use Illuminate\Support\Arr;
use App\Models\Employeebank;
use App\Models\Operableunit;
use Illuminate\Http\Request;
use App\Models\Jobexperience;
use App\Models\Additionaldata;
use App\Models\Administration;
use App\Models\Taxidentification;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $title = 'Employees';
        $subtitle = 'List of Employees';

        return view('employee.index', compact('subtitle', 'title'));
    }

    public function getEmployees(Request $request)
    {
        $employee = Employee::leftJoin('administrations', 'employees.id', '=', 'administrations.employee_id')
            ->leftJoin('projects', 'administrations.project_id', '=', 'projects.id')
            ->leftJoin('positions', 'administrations.position_id', '=', 'positions.id')
            ->leftJoin('departments', 'positions.department_id', '=', 'departments.id')
            ->select('employees.*', 'employees.created_at as created_date', 'administrations.nik', 'administrations.poh', 'administrations.doh', 'administrations.class', 'projects.project_code', 'positions.position_name', 'departments.department_name')
            ->orderBy('administrations.nik', 'desc');

        return datatables()->of($employee)
            ->addIndexColumn()
            ->addColumn('nik', function ($employee) {
                return $employee->nik;
            })
            ->addColumn('fullname', function ($employee) {
                return $employee->fullname;
            })
            ->addColumn('poh', function ($employee) {
                return $employee->poh;
            })
            ->addColumn('doh', function ($employee) {
                return date('d-M-Y', strtotime($employee->doh));
            })
            ->addColumn('department_name', function ($employee) {
                return $employee->department_name;
            })
            ->addColumn('position_name', function ($employee) {
                return $employee->position_name;
            })
            ->addColumn('project_code', function ($employee) {
                return $employee->project_code;
            })
            ->addColumn('class', function ($employee) {
                return $employee->class;
            })
            ->addColumn('created_date', function ($employee) {
                return date('d-M-Y', strtotime($employee->created_date));
            })
            ->filter(function ($instance) use ($request) {
                if (!empty($request->get('search'))) {
                    $instance->where(function ($w) use ($request) {
                        $search = $request->get('search');
                        $w->orWhere('nik', 'LIKE', "%$search%")
                            ->orWhere('fullname', 'LIKE', "%$search%")
                            ->orWhere('poh', 'LIKE', "%$search%")
                            ->orWhere('doh', 'LIKE', "%$search%")
                            ->orWhere('department_name', 'LIKE', "%$search%")
                            ->orWhere('position_name', 'LIKE', "%$search%")
                            ->orWhere('project_code', 'LIKE', "%$search%")
                            ->orWhere('class', 'LIKE', "%$search%")
                            ->orWhere('employees.created_at', 'LIKE', "%$search%");
                    });
                }
            })
            ->addColumn('action', 'employee.action')
            ->rawColumns(['action'])
            ->toJson();
    }

    public function getDepartment()
    {
        $departments = Department::whereHas('positions', function ($query) {
            $query->whereId(request()->input('position_id', 0));
        })->orderBy('department_name', 'asc')->first();

        return response()->json($departments);
    }

    public function create()
    {
        $title = 'Employees';
        $subtitle = 'Add Employee';
        $religions = Religion::orderBy('id', 'asc')->get();
        $banks = Bank::orderBy('bank_name', 'asc')->get();
        $positions = Position::with('departments')->orderBy('position_name', 'asc')->get();
        $projects = Project::orderBy('project_code', 'asc')->get();
        return view('employee.create', compact('title', 'subtitle', 'religions', 'banks', 'positions', 'projects'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'fullname' => 'required',
            'emp_pob' => 'required',
            'emp_dob' => 'required',
            'nik' => 'required|unique:administrations',
            'poh' => 'required',
            'doh' => 'required',
            'foc' => 'required',
            'class' => 'required',
            'position_id' => 'required',
            'project_id' => 'required',
            'filename.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ], [
            'fullname.required' => 'Full Name is required',
            'emp_pob.required' => 'Place of Birth is required',
            'emp_dob.required' => 'Date of Birth is required',
            'poh.required' => 'Place of Hire is required',
            'doh.required' => 'Date of Hire is required',
            'foc.required' => 'Finish Of Contract is required',
            'class.required' => 'Class is required',
            'position_id.required' => 'Position is required',
            'project_id.required' => 'Project is required',
            'nik.required' => 'NIK is required',
            'nik.unique' => 'NIK already exists'
        ]);

        $data = $request->all();

        $employee = new Employee;
        $employee->fullname = $data['fullname'];
        $employee->emp_pob = $data['emp_pob'];
        $employee->emp_dob = $data['emp_dob'];
        $employee->blood_type = $data['blood_type'];
        $employee->religion_id = $data['religion_id'];
        $employee->nationality = $data['nationality'];
        $employee->gender = $data['gender'];
        $employee->marital = $data['marital'];
        $employee->address = $data['address'];
        $employee->village = $data['village'];
        $employee->ward = $data['ward'];
        $employee->district = $data['district'];
        $employee->city = $data['city'];
        $employee->phone = $data['phone'];
        $employee->email = $data['email'];
        $employee->identity_card = $data['identity_card'];
        $employee->user_id = auth()->user()->id;
        $employee->save();

        $checkBank = $data['bank_id'] == null ? false : true;
        if ($checkBank == true) {
            $bank = new Employeebank();
            $bank->employee_id = $employee->id;
            $bank->bank_id = $data['bank_id'];
            $bank->bank_account_no = $data['bank_account_no'];
            $bank->bank_account_name = $data['bank_account_name'];
            $bank->bank_account_branch = $data['bank_account_branch'];
            $bank->save();
        }

        $checkInsurance = Arr::exists($data, 'health_insurance_type');
        if ($checkInsurance == true) {
            foreach ($data['health_insurance_type'] as $insurance => $value) {
                $insurances = array(
                    'employee_id' => $employee->id,
                    'health_insurance_type' => $data['health_insurance_type'][$insurance],
                    'health_insurance_no' => $data['health_insurance_no'][$insurance],
                    'health_facility' => $data['health_facility'][$insurance],
                    'health_insurance_remarks' => $data['health_insurance_remarks'][$insurance],
                );
                Insurance::create($insurances);
            }
        }

        $checkFamily = Arr::exists($data, 'family_relationship');
        if ($checkFamily == true) {
            foreach ($data['family_relationship'] as $family => $value) {
                $families = array(
                    'employee_id' => $employee->id,
                    'family_relationship' => $data['family_relationship'][$family],
                    'family_name' => $data['family_name'][$family],
                    'family_birthplace' => $data['family_birthplace'][$family],
                    'family_birthdate' => $data['family_birthdate'][$family],
                    'family_remarks' => $data['family_remarks'][$family],
                );
                Family::create($families);
            }
        }

        $checkEducation = Arr::exists($data, 'education_name');
        if ($checkEducation == true) {
            foreach ($data['education_name'] as $education => $value) {
                $educations = array(
                    'employee_id' => $employee->id,
                    'education_name' => $data['education_name'][$education],
                    'education_address' => $data['education_address'][$education],
                    'education_year' => $data['education_year'][$education],
                    'education_remarks' => $data['education_remarks'][$education],
                );
                Education::create($educations);
            }
        }

        $checkCourse = Arr::exists($data, 'course_name');
        if ($checkCourse == true) {
            foreach ($data['course_name'] as $course => $value) {
                $courses = array(
                    'employee_id' => $employee->id,
                    'course_name' => $data['course_name'][$course],
                    'course_address' => $data['course_address'][$course],
                    'course_year' => $data['course_year'][$course],
                    'course_remarks' => $data['course_remarks'][$course],
                );
                Course::create($courses);
            }
        }

        $checkJob = Arr::exists($data, 'company_name');
        if ($checkJob == true) {
            foreach ($data['company_name'] as $job => $value) {
                $jobs = array(
                    'employee_id' => $employee->id,
                    'company_name' => $data['company_name'][$job],
                    'company_address' => $data['company_address'][$job],
                    'job_position' => $data['job_position'][$job],
                    'job_duration' => $data['job_duration'][$job],
                    'quit_reason' => $data['quit_reason'][$job],
                );
                Jobexperience::create($jobs);
            }
        }

        $checkUnit = Arr::exists($data, 'unit_name');
        if ($checkUnit == true) {
            foreach ($data['unit_name'] as $unit => $value) {
                $units = array(
                    'employee_id' => $employee->id,
                    'unit_name' => $data['unit_name'][$unit],
                    'unit_type' => $data['unit_type'][$unit],
                    'unit_remarks' => $data['unit_remarks'][$unit],
                );
                Operableunit::create($units);
            }
        }

        $checkLicense = Arr::exists($data, 'driver_license_type');
        if ($checkLicense == true) {
            foreach ($data['driver_license_type'] as $license => $value) {
                $licenses = array(
                    'employee_id' => $employee->id,
                    'driver_license_type' => $data['driver_license_type'][$license],
                    'driver_license_no' => $data['driver_license_no'][$license],
                    'driver_license_exp' => $data['driver_license_exp'][$license],
                );
                License::create($licenses);
            }
        }

        $checkEmergency = Arr::exists($data, 'emrg_call_relation');
        if ($checkEmergency == true) {
            foreach ($data['emrg_call_relation'] as $emergency => $value) {
                $emergencies = array(
                    'employee_id' => $employee->id,
                    'emrg_call_relation' => $data['emrg_call_relation'][$emergency],
                    'emrg_call_name' => $data['emrg_call_name'][$emergency],
                    'emrg_call_address' => $data['emrg_call_address'][$emergency],
                    'emrg_call_phone' => $data['emrg_call_phone'][$emergency],
                );
                Emrgcall::create($emergencies);
            }
        }

        $checkAdditionalData = $data['cloth_size'] == null ? false : true;
        if ($checkAdditionalData == true) {
            $additional = new Additionaldata();
            $additional->employee_id = $employee->id;
            $additional->cloth_size = $data['cloth_size'];
            $additional->pants_size = $data['pants_size'];
            $additional->shoes_size = $data['shoes_size'];
            $additional->height = $data['height'];
            $additional->weight = $data['weight'];
            $additional->glasses = $data['glasses'];
            $additional->save();
        }

        $checkAdministration = $data['nik'] == null ? false : true;
        if ($checkAdministration == true) {
            $administration = new Administration();
            $administration->employee_id = $employee->id;
            $administration->project_id = $data['project_id'];
            $administration->position_id = $data['position_id'];
            $administration->nik = $data['nik'];
            $administration->class = $data['class'];
            $administration->doh = $data['doh'];
            $administration->poh = $data['poh'];
            $administration->foc = $data['foc'];
            $administration->agreement = $data['agreement'];
            $administration->company_program = $data['company_program'];
            $administration->no_fptk = $data['no_fptk'];
            $administration->no_sk_active = $data['no_sk_active'];
            $administration->basic_salary = $data['basic_salary'];
            $administration->site_allowance = $data['site_allowance'];
            $administration->other_allowance = $data['other_allowance'];
            $administration->is_active = 1;
            $administration->save();
        }

        $checkTaxidentification = $data['tax_no'] == null ? false : true;
        if ($checkTaxidentification == true) {
            $taxidentification = new Taxidentification();
            $taxidentification->employee_id = $employee->id;
            $taxidentification->tax_no = $data['tax_no'];
            $taxidentification->tax_valid_date = $data['tax_valid_date'];
            $taxidentification->save();
        }

        if ($request->hasfile('filename')) {
            $directories = Storage::directories('public/images/' . $employee->id);
            if (count($directories) == 0) {
                $path = public_path() . '/images/' . $employee->id;
                File::makeDirectory($path, $mode = 0777, true, true);
            }
            foreach ($request->file('filename') as $image) {
                $name = $image->getClientOriginalName();
                $image->move(public_path() . '/images/' . $employee->id, $name);

                $image = new Image();
                $image->employee_id = $employee->id;
                $image->filename = $name;

                $image->save();
            }
        }

        return redirect('employees')->with('toast_success', 'Employee added successfully!');
    }

    public function show($id)
    {
        $title = 'Employees';
        $subtitle = 'Detail Employee';
        $employee = Employee::with(['religion'])->withTrashed()->where('id', $id)->first();
        $bank = Employeebank::with(['banks'])->where('employee_id', $id)->first();
        $insurances = Insurance::where('employee_id', $id)->get();
        $families = Family::where('employee_id', $id)->get();
        $educations = Education::where('employee_id', $id)->get();
        $courses = Course::where('employee_id', $id)->get();
        $jobs = Jobexperience::where('employee_id', $id)->get();
        $units = Operableunit::where('employee_id', $id)->get();
        $licenses = License::where('employee_id', $id)->get();
        $emergencies = Emrgcall::where('employee_id', $id)->get();
        $additional = Additionaldata::where('employee_id', $id)->first();
        $administrations = Administration::leftJoin('projects', 'administrations.project_id', '=', 'projects.id')
            ->leftJoin('positions', 'administrations.position_id', '=', 'positions.id')
            ->leftJoin('departments', 'positions.department_id', '=', 'departments.id')
            ->select('administrations.*', 'projects.project_code', 'positions.position_name', 'departments.department_name')
            ->where('employee_id', $id)
            ->orderBy('administrations.nik', 'desc')
            ->get();
        $images = Image::where('employee_id', $id)->get();

        // for select option
        $religions = Religion::orderBy('id', 'asc')->get();
        $getBanks = Bank::orderBy('bank_name', 'asc')->get();

        return view('employee.detail', compact('title', 'subtitle', 'employee', 'bank', 'insurances', 'families', 'educations', 'courses', 'jobs', 'units', 'licenses', 'emergencies', 'additional', 'administrations', 'images', 'religions', 'getBanks'));
    }

    public function edit($id)
    {
        $title = 'Employees';
        $subtitle = 'Detail Employee';
        $employees = Employee::where('id', $id)->first();
        $religions = Religion::orderBy('religion_name', 'asc')->get();

        return view('employee.edit', compact('religions', 'employees', 'title', 'subtitle'));
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'fullname' => 'required',
            'emp_pob' => 'required',
            'emp_dob' => 'required',
        ], [
            'fullname.required' => 'Full Name is required',
            'emp_pob.required' => 'Place of Birth is required',
            'emp_dob.required' => 'Date of Birth is required',
        ]);

        $employee = Employee::find($id);
        $employee->fullname = $request->fullname;
        $employee->emp_pob = $request->emp_pob;
        $employee->emp_dob = $request->emp_dob;
        $employee->blood_type = $request->blood_type;
        $employee->religion_id = $request->religion_id;
        $employee->nationality = $request->nationality;
        $employee->gender = $request->gender;
        $employee->marital = $request->marital;
        $employee->address = $request->address;
        $employee->village = $request->village;
        $employee->ward = $request->ward;
        $employee->district = $request->district;
        $employee->city = $request->city;
        $employee->phone = $request->phone;
        $employee->email = $request->email;
        $employee->identity_card = $request->identity_card;
        $employee->user_id = auth()->user()->id;
        $employee->save();

        return redirect('employees/' . $id)->with('toast_success', 'Employee edited successfully');
    }



    public function deleteEmployee($id)
    {
        $employee = Employee::where('id', $id)->first();
        return view('employee.delete', ['employee' => $employee]);


        // $employees = Employee::where('id', $id)->first();
        // $employees->delete();
        // return redirect('admin/employees')->with('status', 'Employee Delete Successfully');
    }


    public function destroy($id)
    {
        // $employee = Employee::where('id', $id)->first();
        // return view('employee.delete', ['employee' => $employee]);


        $employees = Employee::where('id', $id)->first();
        $employees->delete();
        return redirect('admin/employees')->with('status', 'Employee Delete Successfully');
    }
}
