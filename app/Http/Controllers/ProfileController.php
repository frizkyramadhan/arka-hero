<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Bank;
use App\Models\User;
use App\Models\Image;
use App\Models\Users;
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
use App\Models\Termination;
use App\Models\Employeebank;
use App\Models\Notification;
use App\Models\Operableunit;
use Illuminate\Http\Request;
use App\Models\Jobexperience;
use PHPMailer\PHPMailer\SMTP;
use App\Models\Additionaldata;
use App\Models\Administration;
use Yajra\DataTables\DataTables;
use App\Models\Taxidentification;
use Illuminate\Support\Facades\DB;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;
use App\Mail\NotificationSendEmail;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{

    public function dashboard()
    {
        $data = [
            'title' => 'Dashboard'
        ];
        $hoCount = Administration::where('project_id', '1')->where('is_active', '1')->count();
        $boCount = Administration::where('project_id', '2')->where('is_active', '1')->count();
        $malinauCount = Administration::where('project_id', '3')->where('is_active', '1')->count();
        $sbiCount = Administration::where('project_id', '4')->where('is_active', '1')->count();
        $gpkCount = Administration::where('project_id', '5')->where('is_active', '1')->count();
        $bekCount = Administration::where('project_id', '6')->where('is_active', '1')->count();
        $apsCount = Administration::where('project_id', '7')->where('is_active', '1')->count();
        $employeeCount = Employee::count();
        $terminationCount = Administration::where('is_active', '0')->count();
        $Contract   = Administration::whereRaw('datediff(foc, current_date) < 30')->count();
        return view('dashboard', $data, [
            'hoCount' => $hoCount,
            'boCount' => $boCount,
            'malinauCount' => $malinauCount,
            'sbiCount' => $sbiCount,
            'gpkCount' => $gpkCount,
            'bekCount' => $bekCount,
            'apsCount' => $apsCount,
            'employeeCount' => $employeeCount,
            'terminationCount' => $terminationCount,
            'Contract' => $Contract
        ]);
    }

    /**
     * Display project employee summary page.
     *
     * @param int $projectId
     * @return \Illuminate\Http\Response
     */
    public function projectSummary($projectId)
    {
        $project = Project::findOrFail($projectId);

        $title = 'Project Summary';
        $subtitle = $project->project_code . ' - ' . $project->project_name;

        // Get employee count for this project
        $employeeCount = Administration::where('project_id', $projectId)
            ->where('is_active', '1')
            ->count();

        // Get department statistics for this project
        $departmentStats = Department::withCount(['administrations' => function ($query) use ($projectId) {
            $query->where('is_active', '1')
                ->where('project_id', $projectId);
        }])
            ->where('department_status', '1')
            ->having('administrations_count', '>', 0)
            ->orderBy('administrations_count', 'desc')
            ->get();

        return view('summary.project', compact('title', 'subtitle', 'project', 'employeeCount', 'departmentStats'));
    }

    /**
     * Get employees data for a specific project.
     *
     * @param Request $request
     * @param int $projectId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEmployeesByProject(Request $request, $projectId)
    {
        $query = DB::table('employees')
            ->join('administrations', 'employees.id', '=', 'administrations.employee_id')
            ->join('positions', 'administrations.position_id', '=', 'positions.id')
            ->join('departments', 'positions.department_id', '=', 'departments.id')
            ->select(
                'employees.id',
                'administrations.nik',
                'employees.fullname',
                'departments.department_name',
                'positions.position_name',
                'administrations.class'
            )
            ->where('administrations.project_id', $projectId)
            ->where('administrations.is_active', '1');

        return DataTables::of($query)
            ->addIndexColumn()
            ->filterColumn('nik', function ($query, $keyword) {
                $query->where('administrations.nik', 'like', "%{$keyword}%");
            })
            ->filterColumn('fullname', function ($query, $keyword) {
                $query->where('employees.fullname', 'like', "%{$keyword}%");
            })
            ->filterColumn('department_name', function ($query, $keyword) {
                $query->where('departments.department_name', 'like', "%{$keyword}%");
            })
            ->filterColumn('position_name', function ($query, $keyword) {
                $query->where('positions.position_name', 'like', "%{$keyword}%");
            })
            ->filterColumn('class', function ($query, $keyword) {
                $query->where('administrations.class', 'like', "%{$keyword}%");
            })
            ->addColumn('action', function ($row) {
                return '<a href="' . route('employees.show', $row->id) . '" class="btn btn-sm btn-info">
                    <i class="fas fa-eye"></i> Detail
                </a>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Display department employee summary page.
     *
     * @param int $departmentId
     * @return \Illuminate\Http\Response
     */
    public function departmentSummary($departmentId)
    {
        $department = Department::findOrFail($departmentId);

        $title = 'Department Summary';
        $subtitle = $department->department_name;

        // Get employee count for this department
        $employeeCount = DB::table('employees')
            ->join('administrations', 'employees.id', '=', 'administrations.employee_id')
            ->join('positions', 'administrations.position_id', '=', 'positions.id')
            ->join('departments', 'positions.department_id', '=', 'departments.id')
            ->where('departments.id', $departmentId)
            ->where('administrations.is_active', '1')
            ->count();

        // Get project statistics for this department
        $projectStats = Project::withCount(['administrations' => function ($query) use ($departmentId) {
            $query->whereHas('position', function ($q) use ($departmentId) {
                $q->where('department_id', $departmentId);
            })
                ->where('is_active', '1');
        }])
            ->where('project_status', '1')
            ->having('administrations_count', '>', 0)
            ->orderBy('administrations_count', 'desc')
            ->get();

        return view('summary.department', compact('title', 'subtitle', 'department', 'employeeCount', 'projectStats'));
    }

    /**
     * Get employees data for a specific department.
     *
     * @param Request $request
     * @param int $departmentId
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEmployeesByDepartment(Request $request, $departmentId)
    {
        $query = DB::table('employees')
            ->join('administrations', 'employees.id', '=', 'administrations.employee_id')
            ->join('positions', 'administrations.position_id', '=', 'positions.id')
            ->join('departments', 'positions.department_id', '=', 'departments.id')
            ->join('projects', 'administrations.project_id', '=', 'projects.id')
            ->select(
                'employees.id',
                'administrations.nik',
                'employees.fullname',
                'projects.project_code',
                'positions.position_name',
                'administrations.class'
            )
            ->where('departments.id', $departmentId)
            ->where('administrations.is_active', '1');

        return DataTables::of($query)
            ->addIndexColumn()
            ->filterColumn('nik', function ($query, $keyword) {
                $query->where('administrations.nik', 'like', "%{$keyword}%");
            })
            ->filterColumn('fullname', function ($query, $keyword) {
                $query->where('employees.fullname', 'like', "%{$keyword}%");
            })
            ->filterColumn('project_code', function ($query, $keyword) {
                $query->where('projects.project_code', 'like', "%{$keyword}%");
            })
            ->filterColumn('position_name', function ($query, $keyword) {
                $query->where('positions.position_name', 'like', "%{$keyword}%");
            })
            ->filterColumn('class', function ($query, $keyword) {
                $query->where('administrations.class', 'like', "%{$keyword}%");
            })
            ->addColumn('action', function ($row) {
                return '<a href="' . route('employees.show', $row->id) . '" class="btn btn-sm btn-info">
                    <i class="fas fa-eye"></i> Detail
                </a>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Display staff/non-staff employee summary page.
     *
     * @return \Illuminate\Http\Response
     */
    public function staffSummary()
    {
        $title = 'Staff/Non-Staff Summary';
        $subtitle = 'Employee Classification';

        // Get staff and non-staff counts
        $staffCount = Administration::where('is_active', '1')->where('class', 'staff')->count();
        $nonStaffCount = Administration::where('is_active', '1')->where('class', '!=', 'staff')->count();

        return view('summary.staff', compact('title', 'subtitle', 'staffCount', 'nonStaffCount'));
    }

    /**
     * Get staff/non-staff employees data.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStaffEmployees(Request $request)
    {
        $query = DB::table('employees')
            ->join('administrations', 'employees.id', '=', 'administrations.employee_id')
            ->join('positions', 'administrations.position_id', '=', 'positions.id')
            ->join('departments', 'positions.department_id', '=', 'departments.id')
            ->join('projects', 'administrations.project_id', '=', 'projects.id')
            ->select(
                'employees.id',
                'administrations.nik',
                'employees.fullname',
                'projects.project_code',
                'positions.position_name',
                'administrations.class'
            )
            ->where('administrations.is_active', '1');

        if ($request->has('class')) {
            $query->where('administrations.class', $request->class);
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->filterColumn('nik', function ($query, $keyword) {
                $query->where('administrations.nik', 'like', "%{$keyword}%");
            })
            ->filterColumn('fullname', function ($query, $keyword) {
                $query->where('employees.fullname', 'like', "%{$keyword}%");
            })
            ->filterColumn('project_code', function ($query, $keyword) {
                $query->where('projects.project_code', 'like', "%{$keyword}%");
            })
            ->filterColumn('position_name', function ($query, $keyword) {
                $query->where('positions.position_name', 'like', "%{$keyword}%");
            })
            ->addColumn('action', function ($row) {
                return '<a href="' . route('employees.show', $row->id) . '" class="btn btn-sm btn-info">
                    <i class="fas fa-eye"></i> Detail
                </a>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Display permanent/contract employee summary page.
     *
     * @return \Illuminate\Http\Response
     */
    public function employmentSummary()
    {
        $title = 'Employment Status Summary';
        $subtitle = 'Permanent/Contract Employees';

        // Get permanent and contract counts
        $permanentCount = Administration::where('is_active', '1')->whereNull('foc')->count();
        $contractCount = Administration::where('is_active', '1')->whereNotNull('foc')->count();

        return view('summary.employment', compact('title', 'subtitle', 'permanentCount', 'contractCount'));
    }

    /**
     * Get permanent/contract employees data.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getEmploymentEmployees(Request $request)
    {
        $query = DB::table('employees')
            ->join('administrations', 'employees.id', '=', 'administrations.employee_id')
            ->join('positions', 'administrations.position_id', '=', 'positions.id')
            ->join('departments', 'positions.department_id', '=', 'departments.id')
            ->join('projects', 'administrations.project_id', '=', 'projects.id')
            ->select(
                'employees.id',
                'administrations.nik',
                'employees.fullname',
                'projects.project_code',
                'positions.position_name',
                'administrations.foc',
                DB::raw('CASE WHEN administrations.foc IS NULL THEN "Permanent" ELSE "Contract" END as employment_status')
            )
            ->where('administrations.is_active', '1');

        if ($request->has('status')) {
            if ($request->status === 'permanent') {
                $query->whereNull('administrations.foc');
            } else {
                $query->whereNotNull('administrations.foc');
            }
        }

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('foc', function ($row) {
                return $row->foc ? date('d-M-Y', strtotime($row->foc)) : '-';
            })
            ->filterColumn('nik', function ($query, $keyword) {
                $query->where('administrations.nik', 'like', "%{$keyword}%");
            })
            ->filterColumn('fullname', function ($query, $keyword) {
                $query->where('employees.fullname', 'like', "%{$keyword}%");
            })
            ->filterColumn('project_code', function ($query, $keyword) {
                $query->where('projects.project_code', 'like', "%{$keyword}%");
            })
            ->filterColumn('position_name', function ($query, $keyword) {
                $query->where('positions.position_name', 'like', "%{$keyword}%");
            })
            ->addColumn('action', function ($row) {
                return '<a href="' . url('employees/' . $row->id . '#administration') . '" class="btn btn-sm btn-info">
                    <i class="fas fa-eye"></i> Detail
                </a>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    /**
     * Display birthday employee summary page.
     *
     * @return \Illuminate\Http\Response
     */
    public function birthdaySummary()
    {
        $title = 'Birthday Summary';
        $subtitle = 'Employees with Birthday in ' . date('F');

        // Get birthday count - using proper month format with leading zero
        // $birthdayCount = Employee::with(['administration' => function ($query) {
        //     $query->where('is_active', '1');
        // }])->whereMonth('emp_dob', date('m'))->count();

        $birthdayCount = DB::table('employees')
            ->join('administrations', 'employees.id', '=', 'administrations.employee_id',)
            ->join('positions', 'administrations.position_id', '=', 'positions.id')
            ->join('departments', 'positions.department_id', '=', 'departments.id')
            ->join('projects', 'administrations.project_id', '=', 'projects.id')
            ->select(
                'employees.id',
                'administrations.nik',
                'employees.fullname',
                'employees.emp_dob',
                'projects.project_code',
                'positions.position_name',
                'administrations.class'
            )
            ->where('administrations.is_active', '1')
            ->whereMonth('employees.emp_dob', date('m'))
            ->count();

        return view('summary.birthday', compact('title', 'subtitle', 'birthdayCount'));
    }

    /**
     * Get birthday employees data.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBirthdayEmployees(Request $request)
    {
        $query = DB::table('employees')
            ->join('administrations', 'employees.id', '=', 'administrations.employee_id',)
            ->join('positions', 'administrations.position_id', '=', 'positions.id')
            ->join('departments', 'positions.department_id', '=', 'departments.id')
            ->join('projects', 'administrations.project_id', '=', 'projects.id')
            ->select(
                'employees.id',
                'administrations.nik',
                'employees.fullname',
                'employees.emp_dob',
                'projects.project_code',
                'positions.position_name',
                'administrations.class'
            )
            ->where('administrations.is_active', '1')
            ->whereMonth('employees.emp_dob', date('m'));

        return DataTables::of($query)
            ->addIndexColumn()
            ->filterColumn('nik', function ($query, $keyword) {
                $query->where('administrations.nik', 'like', "%{$keyword}%");
            })
            ->filterColumn('fullname', function ($query, $keyword) {
                $query->where('employees.fullname', 'like', "%{$keyword}%");
            })
            ->filterColumn('project_code', function ($query, $keyword) {
                $query->where('projects.project_code', 'like', "%{$keyword}%");
            })
            ->filterColumn('position_name', function ($query, $keyword) {
                $query->where('positions.position_name', 'like', "%{$keyword}%");
            })
            ->addColumn('birthday', function ($row) {
                return date('d F', strtotime($row->emp_dob));
            })
            ->addColumn('age', function ($row) {
                return Carbon::parse($row->emp_dob)->age;
            })
            ->addColumn('action', function ($row) {
                return '<a href="' . route('employees.show', $row->id) . '" class="btn btn-sm btn-info">
                    <i class="fas fa-eye"></i> Detail
                </a>';
            })
            ->rawColumns(['action'])
            ->make(true);
    }

    public function getHobpn(Request $request)
    {
        $employee = Employee::leftJoin('administrations', 'employees.id', '=', 'administrations.employee_id')
            ->leftJoin('projects', 'administrations.project_id', '=', 'projects.id')
            ->leftJoin('positions', 'administrations.position_id', '=', 'positions.id')
            ->leftJoin('departments', 'positions.department_id', '=', 'departments.id')
            ->select('employees.*', 'employees.created_at as created_date', 'administrations.nik', 'administrations.poh', 'administrations.doh', 'administrations.class', 'projects.project_code', 'positions.position_name', 'departments.department_name')
            ->where('project_id', '1')
            ->where('is_active', '1')
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
            ->toJson();
    }

    public function getBojkt(Request $request)
    {
        $employee = Employee::leftJoin('administrations', 'employees.id', '=', 'administrations.employee_id')
            ->leftJoin('projects', 'administrations.project_id', '=', 'projects.id')
            ->leftJoin('positions', 'administrations.position_id', '=', 'positions.id')
            ->leftJoin('departments', 'positions.department_id', '=', 'departments.id')
            ->select('employees.*', 'employees.created_at as created_date', 'administrations.nik', 'administrations.poh', 'administrations.doh', 'administrations.class', 'projects.project_code', 'positions.position_name', 'departments.department_name')
            ->where('project_id', '2')
            ->where('is_active', '1')
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
            ->toJson();
    }

    public function getKpuc(Request $request)
    {
        $employee = Employee::leftJoin('administrations', 'employees.id', '=', 'administrations.employee_id')
            ->leftJoin('projects', 'administrations.project_id', '=', 'projects.id')
            ->leftJoin('positions', 'administrations.position_id', '=', 'positions.id')
            ->leftJoin('departments', 'positions.department_id', '=', 'departments.id')
            ->select('employees.*', 'employees.created_at as created_date', 'administrations.nik', 'administrations.poh', 'administrations.doh', 'administrations.class', 'projects.project_code', 'positions.position_name', 'departments.department_name')
            ->where('project_id', '3')
            ->where('is_active', '1')
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
            ->toJson();
    }

    public function getSbi(Request $request)
    {
        $employee = Employee::leftJoin('administrations', 'employees.id', '=', 'administrations.employee_id')
            ->leftJoin('projects', 'administrations.project_id', '=', 'projects.id')
            ->leftJoin('positions', 'administrations.position_id', '=', 'positions.id')
            ->leftJoin('departments', 'positions.department_id', '=', 'departments.id')
            ->select('employees.*', 'employees.created_at as created_date', 'administrations.nik', 'administrations.poh', 'administrations.doh', 'administrations.class', 'projects.project_code', 'positions.position_name', 'departments.department_name')
            ->where('project_id', '4')
            ->where('is_active', '1')
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
            ->toJson();
    }

    public function getGpk(Request $request)
    {
        $employee = Employee::leftJoin('administrations', 'employees.id', '=', 'administrations.employee_id')
            ->leftJoin('projects', 'administrations.project_id', '=', 'projects.id')
            ->leftJoin('positions', 'administrations.position_id', '=', 'positions.id')
            ->leftJoin('departments', 'positions.department_id', '=', 'departments.id')
            ->select('employees.*', 'employees.created_at as created_date', 'administrations.nik', 'administrations.poh', 'administrations.doh', 'administrations.class', 'projects.project_code', 'positions.position_name', 'departments.department_name')
            ->where('project_id', '5')
            ->where('is_active', '1')
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

            ->toJson();
    }

    public function getBek(Request $request)
    {
        $employee = Employee::leftJoin('administrations', 'employees.id', '=', 'administrations.employee_id')
            ->leftJoin('projects', 'administrations.project_id', '=', 'projects.id')
            ->leftJoin('positions', 'administrations.position_id', '=', 'positions.id')
            ->leftJoin('departments', 'positions.department_id', '=', 'departments.id')
            ->select('employees.*', 'employees.created_at as created_date', 'administrations.nik', 'administrations.poh', 'administrations.doh', 'administrations.class', 'projects.project_code', 'positions.position_name', 'departments.department_name')
            ->where('project_id', '6')
            ->where('is_active', '1')
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

            ->toJson();
    }

    public function getAps(Request $request)
    {
        $employee = Employee::leftJoin('administrations', 'employees.id', '=', 'administrations.employee_id')
            ->leftJoin('projects', 'administrations.project_id', '=', 'projects.id')
            ->leftJoin('positions', 'administrations.position_id', '=', 'positions.id')
            ->leftJoin('departments', 'positions.department_id', '=', 'departments.id')
            ->select('employees.*', 'employees.created_at as created_date', 'administrations.nik', 'administrations.poh', 'administrations.doh', 'administrations.class', 'projects.project_code', 'positions.position_name', 'departments.department_name')
            ->where('project_id', '7')
            ->where('is_active', '1')
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

            ->toJson();
    }

    public function getEmployee(Request $request)
    {
        $employee = Employee::leftJoin('administrations', 'employees.id', '=', 'administrations.employee_id')
            ->leftJoin('projects', 'administrations.project_id', '=', 'projects.id')
            ->leftJoin('positions', 'administrations.position_id', '=', 'positions.id')
            ->leftJoin('departments', 'positions.department_id', '=', 'departments.id')
            ->select('employees.*', 'employees.created_at as created_date', 'administrations.nik', 'administrations.poh', 'administrations.foc', 'administrations.doh', 'administrations.class', 'projects.project_code', 'positions.position_name', 'departments.department_name')
            ->where('is_active', '1')
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
                return date('d-M-Y', strtotime($employee->doh));
            })
            ->addColumn('foc', function ($employee) {
                return date('d-M-Y', strtotime($employee->foc));
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

            ->filter(function ($instance) use ($request) {
                if (!empty($request->get('search'))) {
                    $instance->where(function ($w) use ($request) {
                        $search = $request->get('search');
                        $w->orWhere('nik', 'LIKE', "%$search%")
                            ->orWhere('fullname', 'LIKE', "%$search%")
                            ->orWhere('poh', 'LIKE', "%$search%")
                            ->orWhere('doh', 'LIKE', "%$search%")
                            ->orWhere('foc', 'LIKE', "%$search%")
                            ->orWhere('department_name', 'LIKE', "%$search%")
                            ->orWhere('position_name', 'LIKE', "%$search%")
                            ->orWhere('project_code', 'LIKE', "%$search%")
                            ->orWhere('class', 'LIKE', "%$search%")
                            ->orWhere('employees.created_at', 'LIKE', "%$search%");
                    });
                }
            })

            ->toJson();
    }

    public function getTermination(Request $request)
    {
        $termination = Employee::leftJoin('administrations', 'employees.id', '=', 'administrations.employee_id')
            ->leftJoin('projects', 'administrations.project_id', '=', 'projects.id')
            ->leftJoin('positions', 'administrations.position_id', '=', 'positions.id')
            ->leftJoin('departments', 'positions.department_id', '=', 'departments.id')
            ->select('employees.fullname', 'employees.created_at as created_date', 'administrations.*', 'projects.project_code', 'positions.position_name', 'departments.department_name')
            ->where('administrations.is_active', 0)
            ->orderBy('administrations.nik', 'desc');

        return datatables()->of($termination)
            ->addIndexColumn()
            ->addColumn('fullname', function ($termination) {
                return $termination->fullname;
            })
            ->addColumn('termination_date', function ($termination) {
                return $termination->termination_date;
            })
            ->addColumn('termination_reason', function ($termination) {
                return $termination->termination_reason;
            })
            ->addColumn('coe_no', function ($termination) {
                return $termination->coe_no;
            })
            ->filter(function ($instance) use ($request) {
                if (!empty($request->get('search'))) {
                    $instance->where(function ($w) use ($request) {
                        $search = $request->get('search');
                        $w->orWhere('fullname', 'LIKE', "%$search%")
                            ->orWhere('termination_date', 'LIKE', "%$search%")
                            ->orWhere('termination_reason', 'LIKE', "%$search%")
                            ->orWhere('coe_no', 'LIKE', "%$search%");
                    });
                }
            })
            ->toJson();
    }

    public function getContract(Request $request)
    {
        $employee = Employee::leftJoin('administrations', 'employees.id', '=', 'administrations.employee_id')
            ->leftJoin('projects', 'administrations.project_id', '=', 'projects.id')
            ->leftJoin('positions', 'administrations.position_id', '=', 'positions.id')
            ->leftJoin('departments', 'positions.department_id', '=', 'departments.id')
            ->select('employees.*', 'employees.created_at as created_date', 'administrations.nik', 'administrations.poh', 'administrations.foc', 'administrations.doh', 'administrations.class', 'projects.project_code', 'positions.position_name', 'departments.department_name')
            ->whereRaw('datediff(foc, current_date) < 30')
            ->where('is_active', '1')
            // ->whereNotExists(function ($query) {
            //     $query->select(DB::raw(1))
            //         ->from('terminations')
            //         ->whereRaw('terminations.employee_id = employees.id');
            // })
            ->orderBy('administrations.foc', 'desc');

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
            ->addColumn('foc', function ($employee) {
                return date('d-M-Y', strtotime($employee->foc));
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

            ->toJson();
    }

    /**
     * Display the user's own employee profile (self-service).
     *
     * @return \Illuminate\Http\Response
     */
    public function myProfile()
    {
        $this->authorize('personal.profile.view-own');

        $user = Auth::user();

        if (!$user->employee_id) {
            return redirect()->route('dashboard.personal')
                ->with('toast_error', 'Employee profile not found. Please contact HR.');
        }

        $employee = Employee::with(['religion'])
            ->where('id', $user->employee_id)
            ->firstOrFail();

        // Ensure user can only view their own profile
        if ($employee->id !== $user->employee_id) {
            abort(403, 'You can only view your own profile');
        }

        $banks = Bank::orderBy('bank_name', 'asc')->get();
        $bank = Employeebank::with(['banks'])->where('employee_id', $employee->id)->first();
        $tax = Taxidentification::where('employee_id', $employee->id)->first();
        $insurances = Insurance::where('employee_id', $employee->id)->get();
        $families = Family::where('employee_id', $employee->id)->get();
        $educations = Education::where('employee_id', $employee->id)->get();
        $courses = Course::where('employee_id', $employee->id)->get();
        $jobs = Jobexperience::where('employee_id', $employee->id)->get();
        $licenses = License::where('employee_id', $employee->id)->get();
        $emergencies = Emrgcall::where('employee_id', $employee->id)->get();
        $additional = Additionaldata::where('employee_id', $employee->id)->first();
        $units = Operableunit::where('employee_id', $employee->id)->get();
        $images = Image::where('employee_id', $employee->id)->get();

        // Get active administration data
        $activeAdministration = Administration::with([
            'position.department',
            'project',
            'grade',
            'level'
        ])
            ->where('employee_id', $employee->id)
            ->where('is_active', '1')
            ->first();

        // Get all administrations for history
        $administrations = Administration::leftJoin('projects', 'administrations.project_id', '=', 'projects.id')
            ->leftJoin('positions', 'administrations.position_id', '=', 'positions.id')
            ->leftJoin('departments', 'positions.department_id', '=', 'departments.id')
            ->leftJoin('grades', 'administrations.grade_id', '=', 'grades.id')
            ->leftJoin('levels', 'administrations.level_id', '=', 'levels.id')
            ->select('administrations.*', 'projects.project_code', 'positions.position_name', 'departments.department_name', 'grades.name as grade_name', 'levels.name as level_name')
            ->where('employee_id', $employee->id)
            ->orderBy('administrations.nik', 'desc')
            ->get();

        $profile = Image::where('employee_id', $employee->id)
            ->where('is_profile', '=', '1')
            ->first();

        $title = 'My Profile';
        $subtitle = 'My Profile';

        return view('profile.my-profile', compact(
            'title',
            'subtitle',
            'employee',
            'banks',
            'bank',
            'tax',
            'insurances',
            'families',
            'educations',
            'courses',
            'jobs',
            'licenses',
            'emergencies',
            'additional',
            'units',
            'images',
            'activeAdministration',
            'administrations',
            'profile'
        ));
    }

    /**
     * Show the profile page with user information and forms.
     *
     * @return \Illuminate\Http\Response
     */
    public function showChangePasswordForm()
    {
        $title = 'My Profile';
        $subtitle = 'Manage your profile information';
        $user = Auth::user()->load(['employee', 'roles']);

        return view('profile.change-password', compact('title', 'subtitle', 'user'));
    }

    /**
     * Update the user's profile (name and/or password).
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();
        $rules = [];
        $messages = [];

        // Always validate name
        $rules['name'] = ['required', 'string', 'max:255'];
        $messages['name.required'] = 'Name is required';
        $messages['name.max'] = 'Name must not exceed 255 characters';

        // Always validate username
        $rules['username'] = ['required', 'alpha_dash', 'min:3', 'max:255', 'unique:users,username,' . $user->id];
        $messages['username.required'] = 'Username is required';
        $messages['username.unique'] = 'Username already exists';
        $messages['username.alpha_dash'] = 'Username can only contain letters, numbers, dashes and underscores';
        $messages['username.min'] = 'Username must be at least 3 characters';
        $messages['username.max'] = 'Username must not exceed 255 characters';

        // Validate password only if provided
        if ($request->filled('password') || $request->filled('current_password')) {
            $rules['current_password'] = ['required'];
            $rules['password'] = ['required', 'min:5', 'confirmed'];
            $messages['current_password.required'] = 'Current password is required when changing password';
            $messages['password.required'] = 'New password is required';
            $messages['password.min'] = 'Password must be at least 5 characters';
            $messages['password.confirmed'] = 'Password confirmation does not match';
        }

        $validated = $request->validate($rules, $messages);

        // Update name
        $user->name = $request->name;

        // Update username
        $user->username = $request->username;

        // Update password if provided
        if ($request->filled('password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return back()->withErrors(['current_password' => 'Current password is incorrect']);
            }
            $user->password = Hash::make($request->password);
        }

        $user->save();

        $message = 'Profile updated successfully';
        if ($request->filled('password')) {
            $message = 'Name, username and password updated successfully';
        } else {
            $message = 'Name and username updated successfully';
        }

        return redirect()->route('profile.change-password')->with('toast_success', $message);
    }
}
