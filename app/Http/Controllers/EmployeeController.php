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
use App\Imports\TaxImport;
use App\Models\Department;
use App\Imports\BankImport;
use Illuminate\Support\Arr;
use App\Models\Employeebank;
use App\Models\Operableunit;
use Illuminate\Http\Request;
use App\Imports\FamilyImport;
use App\Models\Jobexperience;
use App\Imports\LicenseImport;
use App\Imports\ProjectImport;
use App\Models\Additionaldata;
use App\Models\Administration;
use App\Imports\EmployeeImport;
use App\Imports\PersonalImport;
use App\Imports\PositionImport;
use App\Imports\InsuranceImport;
use App\Imports\DepartmentImport;
use App\Models\Taxidentification;
use App\Imports\TerminationImport;
use App\Exports\MultipleSheetExport;
use App\Imports\MultipleSheetImport;
use Illuminate\Support\Facades\File;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\AdministrationImport;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Validators\ValidationException;
use App\Models\Grade;
use App\Models\Level;

class EmployeeController extends Controller
{
    public function __construct()
    {
        $this->middleware('role_or_permission:employees.show')->only('index', 'show');
        $this->middleware('role_or_permission:employees.create')->only('create');
        $this->middleware('role_or_permission:employees.edit')->only('edit');
        $this->middleware('role_or_permission:employees.delete')->only('destroy');
        $this->middleware('role_or_permission:employees.export')->only('export');
        $this->middleware('role_or_permission:employees.import')->only('import');
    }

    public function index(Request $request)
    {
        $title = 'Employees';
        $subtitle = 'List of Employees';
        $departments = Department::where('department_status', '1')->orderBy('department_name', 'asc')->get();
        $positions = Position::where('position_status', '1')->orderBy('position_name', 'asc')->get();
        $projects = Project::where('project_status', '1')->orderBy('project_code', 'asc')->get();
        $grades = Grade::where('is_active', 1)->orderBy('name', 'asc')->get();
        $levels = Level::where('is_active', 1)->orderBy('name', 'asc')->get();

        return view('employee.index', compact('subtitle', 'title', 'departments', 'positions', 'projects', 'grades', 'levels'));
    }

    public function getEmployees(Request $request)
    {
        $employee = Employee::leftJoin('administrations', 'employees.id', '=', 'administrations.employee_id')
            ->leftJoin('projects', 'administrations.project_id', '=', 'projects.id')
            ->leftJoin('positions', 'administrations.position_id', '=', 'positions.id')
            ->leftJoin('departments', 'positions.department_id', '=', 'departments.id')
            ->leftJoin('grades', 'administrations.grade_id', '=', 'grades.id')
            ->leftJoin('levels', 'administrations.level_id', '=', 'levels.id')
            ->select('employees.*', 'administrations.nik', 'administrations.poh', 'administrations.doh', 'administrations.class', 'projects.project_code', 'positions.position_name', 'departments.department_name', 'grades.name as grade_name', 'levels.name as level_name')
            ->where('administrations.is_active', '1')
            // ->whereNotExists(function ($query) {
            //     $query->select(DB::raw(1))
            //         ->from('terminations')
            //         ->whereRaw('terminations.employee_id = employees.id');
            // })
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
                if ($employee->doh == null)
                    return null;
                else
                    return date('d-M-Y', strtotime($employee->doh));
            })
            ->addColumn('department_name', function ($employee) {
                return $employee->department_name;
            })
            ->addColumn('position_name', function ($employee) {
                return $employee->position_name;
            })
            ->addColumn('grade_name', function ($employee) {
                return $employee->grade_name;
            })
            ->addColumn('level_name', function ($employee) {
                return $employee->level_name;
            })
            ->addColumn('project_code', function ($employee) {
                return $employee->project_code;
            })
            ->addColumn('class', function ($employee) {
                return $employee->class;
            })
            ->filter(function ($instance) use ($request) {
                if (!empty($request->get('date1') && !empty($request->get('date2')))) {
                    $instance->where(function ($w) use ($request) {
                        $date1 = $request->get('date1');
                        $date2 = $request->get('date2');
                        $w->whereBetween('doh', array($date1, $date2));
                    });
                }
                if (!empty($request->get('nik'))) {
                    $instance->where(function ($w) use ($request) {
                        $nik = $request->get('nik');
                        $w->orWhere('nik', 'LIKE', '%' . $nik . '%');
                    });
                }
                if (!empty($request->get('fullname'))) {
                    $instance->where(function ($w) use ($request) {
                        $fullname = $request->get('fullname');
                        $w->orWhere('fullname', 'LIKE', '%' . $fullname . '%');
                    });
                }
                if (!empty($request->get('poh'))) {
                    $instance->where(function ($w) use ($request) {
                        $poh = $request->get('poh');
                        $w->orWhere('poh', 'LIKE', '%' . $poh . '%');
                    });
                }
                if (!empty($request->get('department_name'))) {
                    $instance->where(function ($w) use ($request) {
                        $department_name = $request->get('department_name');
                        $w->orWhere('department_name', 'LIKE', '%' . $department_name . '%');
                    });
                }
                if (!empty($request->get('position_name'))) {
                    $instance->where(function ($w) use ($request) {
                        $position_name = $request->get('position_name');
                        $w->orWhere('position_name', 'LIKE', '%' . $position_name . '%');
                    });
                }
                if (!empty($request->get('grade_id'))) {
                    $instance->where('administrations.grade_id', $request->get('grade_id'));
                }
                if (!empty($request->get('level_id'))) {
                    $instance->where('administrations.level_id', $request->get('level_id'));
                }
                if (!empty($request->get('project_code'))) {
                    $instance->where(function ($w) use ($request) {
                        $project_code = $request->get('project_code');
                        $w->orWhere('project_code', 'LIKE', '%' . $project_code . '%');
                    });
                }
                if (!empty($request->get('class'))) {
                    $instance->where(function ($w) use ($request) {
                        $class = $request->get('class');
                        $w->orWhere('class', 'LIKE', '%' . $class . '%');
                    });
                }
                if (!empty($request->get('search'))) {
                    $instance->where(function ($w) use ($request) {
                        $search = $request->get('search');
                        $w->orWhere('nik', 'LIKE', "%$search%")
                            ->orWhere('fullname', 'LIKE', "%$search%")
                            ->orWhere('poh', 'LIKE', "%$search%")
                            ->orWhere('doh', 'LIKE', "%$search%")
                            ->orWhere('department_name', 'LIKE', "%$search%")
                            ->orWhere('position_name', 'LIKE', "%$search%")
                            ->orWhere('grades.name', 'LIKE', "%$search%")
                            ->orWhere('levels.name', 'LIKE', "%$search%")
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

    public function create()
    {
        $title = 'Employees';
        $subtitle = 'Add Employee';
        $religions = Religion::orderBy('id', 'asc')->get();
        $banks = Bank::orderBy('bank_name', 'asc')->get();
        $positions = Position::with('department')->orderBy('position_name', 'asc')->get();
        $projects = Project::orderBy('project_code', 'asc')->get();
        $grades = Grade::where('is_active', 1)->orderBy('name', 'asc')->get();
        $levels = Level::where('is_active', 1)->orderBy('name', 'asc')->get();
        return view('employee.create', compact('title', 'subtitle', 'religions', 'banks', 'positions', 'projects', 'grades', 'levels'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'fullname' => 'required',
            'identity_card' => 'required|unique:employees',
            'emp_pob' => 'required',
            'emp_dob' => 'required',
            'nik' => 'required|unique:administrations',
            'poh' => 'required',
            'doh' => 'required',
            'class' => 'required',
            'position_id' => 'required',
            'project_id' => 'required',
            'grade_id' => 'nullable|exists:grades,id',
            'level_id' => 'nullable|exists:levels,id',
            'filename.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048'
        ], [
            'fullname.required' => 'Full Name is required',
            'identity_card.required' => 'Identity Card No is required',
            'identity_card.unique' => 'Identity Card No already exists',
            'emp_pob.required' => 'Place of Birth is required',
            'emp_dob.required' => 'Date of Birth is required',
            'poh.required' => 'Place of Hire is required',
            'doh.required' => 'Date of Hire is required',
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
                    'bpjsks_no' => $data['bpjsks_no'][$family],
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
            $administration->grade_id = $data['grade_id'] ?? null;
            $administration->level_id = $data['level_id'] ?? null;
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
            $administration->is_active = $data['is_active'];
            $administration->user_id = auth()->user()->id;
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
        $employee = Employee::with(['religion'])->where('id', $id)->first();
        $banks = Bank::orderBy('bank_name', 'asc')->get();
        $bank = Employeebank::with(['banks'])->where('employee_id', $id)->first();
        $tax = Taxidentification::where('employee_id', $id)->first();
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
            ->leftJoin('grades', 'administrations.grade_id', '=', 'grades.id')
            ->leftJoin('levels', 'administrations.level_id', '=', 'levels.id')
            ->select('administrations.*', 'projects.project_code', 'positions.position_name', 'departments.department_name', 'grades.name as grade_name', 'levels.name as level_name')
            ->where('employee_id', $id)
            ->orderBy('administrations.nik', 'desc')
            ->get();
        $images = Image::where('employee_id', $id)->get();
        $profile = Image::where('employee_id', $id)->where('is_profile', '=', '1')->first();
        // for select option
        $religions = Religion::orderBy('id', 'asc')->get();
        $getBanks = Bank::orderBy('bank_name', 'asc')->get();
        $positions = Position::with('department')->orderBy('position_name', 'asc')->get();
        $projects = Project::orderBy('project_code', 'asc')->get();
        $grades = Grade::where('is_active', 1)->orderBy('name', 'asc')->get();
        $levels = Level::where('is_active', 1)->orderBy('name', 'asc')->get();

        return view('employee.detail', compact('title', 'subtitle', 'employee', 'banks', 'bank', 'tax', 'insurances', 'families', 'educations', 'courses', 'jobs', 'units', 'licenses', 'emergencies', 'additional', 'administrations', 'images', 'religions', 'getBanks', 'positions', 'projects', 'grades', 'levels', 'profile'));
    }

    public function edit($id)
    {
        $title = 'Employees';
        $subtitle = 'Detail Employee';
        $employees = Employee::where('id', $id)->first();
        $religions = Religion::orderBy('religion_name', 'asc')->get();
        $emergencies = Emrgcall::where('employee_id', $id)->get();
        $additional = Additionaldata::where('employee_id', $id)->first();
        $administrations = Administration::leftJoin('projects', 'administrations.project_id', '=', 'projects.id')
            ->leftJoin('positions', 'administrations.position_id', '=', 'positions.id')
            ->leftJoin('departments', 'positions.department_id', '=', 'departments.id')
            ->leftJoin('grades', 'administrations.grade_id', '=', 'grades.id')
            ->leftJoin('levels', 'administrations.level_id', '=', 'levels.id')
            ->select('administrations.*', 'projects.project_code', 'positions.position_name', 'departments.department_name', 'grades.name as grade_name', 'levels.name as level_name')
            ->where('employee_id', $id)
            ->orderBy('administrations.nik', 'desc')
            ->get();
        $activeAdministration = Administration::with('position.department', 'project', 'grade', 'level')->where('employee_id', $id)->where('is_active', '1')->first();
        $images = Image::where('employee_id', $id)->get();
        $profile = Image::where('employee_id', $id)->where('is_profile', '=', '1')->first();
        // for select option
        $religions = Religion::orderBy('id', 'asc')->get();
        $getBanks = Bank::orderBy('bank_name', 'asc')->get();
        $positions = Position::with('department')->orderBy('position_name', 'asc')->get();
        $projects = Project::orderBy('project_code', 'asc')->get();

        return view('employee.edit', compact('religions', 'employees', 'title', 'subtitle', 'emergencies', 'additional', 'administrations', 'activeAdministration', 'images', 'religions', 'getBanks', 'positions', 'projects', 'profile'));
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

    public function destroy($employee_id)
    {
        $images = Image::where('employee_id', $employee_id)->get();
        foreach ($images as $image) {
            // delete image
            $img = public_path('images/' . $image->employee_id . '/' . $image->filename);
            if (file_exists($img)) {
                unlink($img);
                Image::where('id', $image->id)->delete();
            }
        }
        Administration::where('employee_id', $employee_id)->delete();
        Employeebank::where('employee_id', $employee_id)->delete();
        Taxidentification::where('employee_id', $employee_id)->delete();
        Insurance::where('employee_id', $employee_id)->delete();
        License::where('employee_id', $employee_id)->delete();
        Family::where('employee_id', $employee_id)->delete();
        Education::where('employee_id', $employee_id)->delete();
        Course::where('employee_id', $employee_id)->delete();
        Jobexperience::where('employee_id', $employee_id)->delete();
        Operableunit::where('employee_id', $employee_id)->delete();
        Emrgcall::where('employee_id', $employee_id)->delete();
        Additionaldata::where('employee_id', $employee_id)->delete();
        Employee::where('id', $employee_id)->delete();

        return redirect('employees')->with('toast_success', 'Employee Delete Successfully');
    }

    public function print($id)
    {
        $title = 'Employees';
        $subtitle = 'Detail Employee';
        $employee = Employee::with(['religion'])->where('id', $id)->first();
        $banks = Bank::orderBy('bank_name', 'asc')->get();
        $bank = Employeebank::with(['banks'])->where('employee_id', $id)->first();
        $tax = Taxidentification::where('employee_id', $id)->first();
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
            ->leftJoin('grades', 'administrations.grade_id', '=', 'grades.id')
            ->leftJoin('levels', 'administrations.level_id', '=', 'levels.id')
            ->select('administrations.*', 'projects.project_code', 'positions.position_name', 'departments.department_name', 'grades.name as grade_name', 'levels.name as level_name')
            ->where('employee_id', $id)
            ->orderBy('administrations.nik', 'desc')
            ->get();
        $activeAdministration = Administration::with('position.department', 'project', 'grade', 'level')->where('employee_id', $id)->where('is_active', '1')->first();
        $images = Image::where('employee_id', $id)->get();
        $profile = Image::where('employee_id', $id)->where('is_profile', '=', '1')->first();
        // for select option
        $religions = Religion::orderBy('id', 'asc')->get();
        $getBanks = Bank::orderBy('bank_name', 'asc')->get();
        $positions = Position::with('department')->orderBy('position_name', 'asc')->get();
        $projects = Project::orderBy('project_code', 'asc')->get();

        return view('employee.print', compact('title', 'subtitle', 'employee', 'bank', 'tax', 'insurances', 'families', 'educations', 'courses', 'jobs', 'units', 'licenses', 'emergencies', 'additional', 'administrations', 'images', 'religions', 'getBanks', 'positions', 'projects', 'profile', 'activeAdministration'));
    }

    public function addImages($id, Request $request)
    {
        $employee = Employee::find($id);

        $this->validate($request, [
            'filename' => 'required',
            'filename.*' => 'image|mimes:jpeg,png,jpg|max:2048'
        ]);

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
                $image->is_profile = 0;

                $image->save();
            }
        }

        return redirect('employees/' . $id . '#image')->with('toast_success', 'Images uploaded successfully');
    }

    public function deleteImage($employee_id, $id)
    {
        $images = Image::where('employee_id', $employee_id)->get();
        if ($images->count() == 1) { // jika image sisa 1, hapus juga foldernya
            $image = Image::find($id);
            // delete image
            $img = public_path('images/' . $image->employee_id . '/' . $image->filename);
            if (file_exists($img)) {
                unlink($img);
                Image::where('id', $image->id)->delete();
                File::deleteDirectory(public_path('images/' . $image->employee_id));
            }
        } else {
            $image = Image::find($id);
            // delete image
            $img = public_path('images/' . $image->employee_id . '/' . $image->filename);
            if (file_exists($img)) {
                unlink($img);
                Image::where('id', $image->id)->delete();
            }
        }

        return redirect('employees/' . $image->employee_id . '#image')->with('toast_success', 'Image successfully deleted!');
    }

    public function deleteImages($employee_id)
    {
        $images = Image::where('employee_id', $employee_id)->get();
        // delete folder
        $path = public_path('images/' . $employee_id);
        if (file_exists($path)) {
            Image::where('employee_id', $employee_id)->delete();
            File::deleteDirectory($path);
        }

        // delete image
        // foreach ($images as $image) {
        //     $img = public_path('images/' . $image->employee_id . '/' . $image->filename);
        //     if (file_exists($img)) {
        //         unlink($img);
        //         Image::where('id', $image->id)->delete();
        //     }
        // }
        return redirect('employees/' . $employee_id . '#image')->with('toast_success', 'All images successfully deleted!');
    }

    public function setProfile($employee_id, $id)
    {
        $image = Image::find($id);
        $image->is_profile = 1;
        $image->save();
        Image::where('employee_id', $employee_id)->where('id', '!=', $id)->update(['is_profile' => 0]);

        return redirect('employees/' . $employee_id . '#image')->with('toast_success', 'Profile Picture Set Successfully');
    }

    public function getDepartment()
    {
        $departments = Department::whereHas('positions', function ($query) {
            $query->whereId(request()->input('position_id', 0));
        })->orderBy('department_name', 'asc')->first();

        return response()->json($departments);
    }

    public function personal()
    {
        $title = 'Personal Detail';
        $subtitle = 'Personal Detail';
        $religions = Religion::where('religion_status', '1')->orderBy('id', 'asc')->get();

        return view('employee.personal', compact('subtitle', 'title', 'religions'));
    }

    public function getPersonals(Request $request)
    {
        $employee = Employee::leftJoin('religions', 'employees.religion_id', '=', 'religions.id')
            ->select('employees.*', 'employees.created_at as created_date', 'religions.religion_name')
            ->orderBy('fullname', 'asc');

        return datatables()->of($employee)
            ->addIndexColumn()
            ->addColumn('fullname', function ($employee) {
                return $employee->fullname;
            })
            ->addColumn('emp_pob', function ($employee) {
                return $employee->emp_pob;
            })
            ->addColumn('emp_dob', function ($employee) {
                if ($employee->emp_dob == null)
                    return null;
                else
                    return date('d-M-Y', strtotime($employee->emp_dob));
            })
            ->addColumn('religion_name', function ($employee) {
                return $employee->religion_name;
            })
            ->addColumn('gender', function ($employee) {
                if ($employee->gender == 'male') {
                    return 'Male';
                } else {
                    return 'Female';
                }
            })
            ->addColumn('marital', function ($employee) {
                return $employee->marital;
            })
            ->addColumn('address', function ($employee) {
                return $employee->address;
            })
            ->addColumn('village', function ($employee) {
                return $employee->village;
            })
            ->addColumn('ward', function ($employee) {
                return $employee->ward;
            })
            ->addColumn('district', function ($employee) {
                return $employee->district;
            })
            ->addColumn('city', function ($employee) {
                return $employee->city;
            })
            ->addColumn('phone', function ($employee) {
                return $employee->phone;
            })
            ->filter(function ($instance) use ($request) {
                if (!empty($request->get('date1') && !empty($request->get('date2')))) {
                    $instance->where(function ($w) use ($request) {
                        $date1 = $request->get('date1');
                        $date2 = $request->get('date2');
                        $w->whereBetween('emp_dob', array($date1, $date2));
                    });
                }
                if (!empty($request->get('fullname'))) {
                    $instance->where(function ($w) use ($request) {
                        $fullname = $request->get('fullname');
                        $w->orWhere('fullname', 'LIKE', '%' . $fullname . '%');
                    });
                }
                if (!empty($request->get('emp_pob'))) {
                    $instance->where(function ($w) use ($request) {
                        $emp_pob = $request->get('emp_pob');
                        $w->orWhere('emp_pob', 'LIKE', '%' . $emp_pob . '%');
                    });
                }
                if (!empty($request->get('religion_name'))) {
                    $instance->where(function ($w) use ($request) {
                        $religion_name = $request->get('religion_name');
                        $w->orWhere('religion_name', 'LIKE', '%' . $religion_name . '%');
                    });
                }
                if (!empty($request->get('gender'))) {
                    $instance->where(function ($w) use ($request) {
                        $gender = $request->get('gender');
                        $w->orWhere('gender', 'LIKE', '%' . $gender . '%');
                    });
                }
                if (!empty($request->get('marital'))) {
                    $instance->where(function ($w) use ($request) {
                        $marital = $request->get('marital');
                        $w->orWhere('marital', 'LIKE', '%' . $marital . '%');
                    });
                }
                if (!empty($request->get('address'))) {
                    $instance->where(function ($w) use ($request) {
                        $address = $request->get('address');
                        $w->orWhere('address', 'LIKE', '%' . $address . '%');
                    });
                }
                if (!empty($request->get('village'))) {
                    $instance->where(function ($w) use ($request) {
                        $village = $request->get('village');
                        $w->orWhere('village', 'LIKE', '%' . $village . '%');
                    });
                }
                if (!empty($request->get('ward'))) {
                    $instance->where(function ($w) use ($request) {
                        $ward = $request->get('ward');
                        $w->orWhere('ward', 'LIKE', '%' . $ward . '%');
                    });
                }
                if (!empty($request->get('district'))) {
                    $instance->where(function ($w) use ($request) {
                        $district = $request->get('district');
                        $w->orWhere('district', 'LIKE', '%' . $district . '%');
                    });
                }
                if (!empty($request->get('city'))) {
                    $instance->where(function ($w) use ($request) {
                        $city = $request->get('city');
                        $w->orWhere('city', 'LIKE', '%' . $city . '%');
                    });
                }
                if (!empty($request->get('phone'))) {
                    $instance->where(function ($w) use ($request) {
                        $phone = $request->get('phone');
                        $w->orWhere('phone', 'LIKE', '%' . $phone . '%');
                    });
                }
            })
            ->addColumn('action', 'employee.action-personal')
            ->rawColumns(['action'])
            ->toJson();
    }

    // public function importComplete(Request $request)
    // {
    //     $failures = collect();

    //     try {
    //         $import = new MultipleSheetImport();
    //         $import->import($request->file('employee'));
    //         // Excel::import($import, $request->file('employee'));

    //         // Ambil semua failure dari tiap sheet
    //         foreach ($import->sheets() as $sheetImport) {
    //             foreach ($sheetImport->failures() as $failure) {
    //                 $failures->push([
    //                     'sheet'     => method_exists($sheetImport, 'getSheetName') ? $sheetImport->getSheetName() : 'Unknown',
    //                     'row'       => $failure->row(),
    //                     'attribute' => $failure->attribute(),
    //                     'value'     => $failure->values()[$failure->attribute()] ?? null,
    //                     'errors'    => implode(', ', $failure->errors()),
    //                 ]);
    //             }
    //         }

    //         return redirect('employees')->with('toast_success', 'Data imported successfully');
    //     } catch (ValidationException $e) {
    //         // Menangkap error dari Laravel Excel (bukan yang dalam SkipsFailures)
    //         foreach ($e->failures() as $failure) {
    //             $failures->push([
    //                 'sheet'     => 'Unknown (validation exception)',
    //                 'row'       => $failure->row(),
    //                 'attribute' => $failure->attribute(),
    //                 'value'     => $failure->values()[$failure->attribute()] ?? null,
    //                 'errors'    => implode(', ', $failure->errors()),
    //             ]);
    //         }
    //         dd($failures);
    //         return redirect('employees')->withFailures($failures);
    //     } catch (\Throwable $e) {
    //         // Menangkap error tak terduga lain (misalnya file corrupt, format salah, dll)
    //         return redirect('employees')->with('toast_error', 'Terjadi kesalahan saat import: ' . $e->getMessage());
    //     }
    // }

    public function export()
    {
        return (new MultipleSheetExport())->download('export-' . date('Y-m-d') . '.xlsx');
    }

    public function import(Request $request)
    {
        $this->validate($request, [
            'employee' => 'required|mimes:xls,xlsx',
        ], [
            'employee.required' => 'Please select a file to import',
            'employee.mimes' => 'The file must be a file of type: xls, xlsx',
        ]);

        try {
            $import = new MultipleSheetImport();
            // $import = new PersonalImport(); ok
            // $import = new AdministrationImport(); ok
            // $import = new BankImport(); ok
            // $import = new TaxImport(); ok
            // $import = new InsuranceImport();
            Excel::import($import, $request->file('employee'));

            // Cek apakah ada validation failures manual
            $failures = collect();

            if (method_exists($import, 'sheets')) {
                foreach ($import->sheets() as $sheetName => $sheetImport) {
                    if (method_exists($sheetImport, 'failures')) {
                        foreach ($sheetImport->failures() as $failure) {
                            // Jika failure adalah objek Failure dari Laravel Excel
                            if (method_exists($failure, 'row')) {
                                $failures->push([
                                    'sheet'     => method_exists($sheetImport, 'getSheetName') ? $sheetImport->getSheetName() : $sheetName,
                                    'row'       => $failure->row(),
                                    'attribute' => $failure->attribute(),
                                    'value'     => $failure->values()[$failure->attribute()] ?? null,
                                    'errors'    => implode(', ', $failure->errors()),
                                ]);
                            } else {
                                // Jika failure adalah array dari manual failures
                                $failures->push($failure);
                            }
                        }
                    }
                }
            }

            if ($failures->isNotEmpty()) {
                return back()->with('failures', $failures);
            }

            return redirect('employees')->with('toast_success', 'Data imported successfully');
        } catch (ValidationException $e) {
            $failures = collect();
            $sheetName = 'Unknown';
            // Coba dapatkan nama sheet dari import_employee jika tersedia
            if (method_exists($import, 'getSheetName')) {
                $sheetName = $import->getSheetName();
            }

            foreach ($e->failures() as $failure) {
                $failures->push([
                    'sheet'     => $sheetName,
                    'row'       => $failure->row(),
                    'attribute' => $failure->attribute(),
                    'value'     => $failure->values()[$failure->attribute()] ?? null,
                    'errors'    => implode(', ', $failure->errors()),
                ]);
            }

            return back()->with('failures', $failures);
        } catch (\Throwable $e) {
            $failures = collect([
                [
                    'sheet' => 'Unknown',
                    'row' => '-',
                    'attribute' => 'System Error',
                    'value' => null,
                    'errors' => 'An error occurred during import: ' . $e->getMessage()
                ]
            ]);
            return back()->with('failures', $failures);
        }
    }
}
