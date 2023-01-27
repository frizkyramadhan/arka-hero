<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Project;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Religion;
use App\Models\Department;
use App\Models\Termination;
use App\Models\Notification;
use Illuminate\Http\Request;
use App\Models\Administration;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Mail\NotificationSendEmail;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Models\Users;
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;


class ProfileController extends Controller
{
 
    public function dashboard()
    {
        $data = [
            'title' => 'Dashboard'
        ];
        $hoCount = Administration::where('project_id','1')->count();
        $boCount = Administration::where('project_id','2')->count();
        $malinauCount = Administration::where('project_id','3')->count();
        $sbiCount = Administration::where('project_id','4')->count();
        $gpkCount = Administration::where('project_id','5')->count();
        $bekCount = Administration::where('project_id','6')->count();
        $apsCount = Administration::where('project_id','7')->count();
        $employeeCount = Employee::count();
        $terminationCount = Termination::count();
        $Contract   = Administration::whereRaw('datediff(foc, current_date) < 30')->count();
        return view('dashboard', $data, ['hoCount' => $hoCount, 'boCount' => $boCount, 'malinauCount'=> $malinauCount,
        'sbiCount'=> $sbiCount, 'gpkCount'=> $gpkCount, 'bekCount'=> $bekCount, 'apsCount'=>$apsCount, 'employeeCount'=>$employeeCount,'terminationCount'=>$terminationCount, 'Contract'=>$Contract]);
    }


    public function getHobpn(Request $request)
    {
        $employee = Employee::leftJoin('administrations', 'employees.id', '=', 'administrations.employee_id')
            ->leftJoin('projects', 'administrations.project_id', '=', 'projects.id')
            ->leftJoin('positions', 'administrations.position_id', '=', 'positions.id')
            ->leftJoin('departments', 'positions.department_id', '=', 'departments.id')
            ->select('employees.*', 'employees.created_at as created_date', 'administrations.nik', 'administrations.poh', 'administrations.doh', 'administrations.class', 'projects.project_code', 'positions.position_name', 'departments.department_name')
            ->where('project_id', '1')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('terminations')
                    ->whereRaw('terminations.employee_id = employees.id');
            })
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
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('terminations')
                    ->whereRaw('terminations.employee_id = employees.id');
            })
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
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('terminations')
                    ->whereRaw('terminations.employee_id = employees.id');
            })
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
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('terminations')
                    ->whereRaw('terminations.employee_id = employees.id');
            })
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
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('terminations')
                    ->whereRaw('terminations.employee_id = employees.id');
            })
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
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('terminations')
                    ->whereRaw('terminations.employee_id = employees.id');
            })
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
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('terminations')
                    ->whereRaw('terminations.employee_id = employees.id');
            })
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
            ->select('employees.*', 'employees.created_at as created_date', 'administrations.nik', 'administrations.poh','administrations.foc', 'administrations.doh', 'administrations.class', 'projects.project_code', 'positions.position_name', 'departments.department_name')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('terminations')
                    ->whereRaw('terminations.employee_id = employees.id');
            })
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
        $termination = Termination::leftJoin('employees', 'terminations.employee_id', '=', 'employees.id')
            ->select('terminations.*', 'employees.fullname')
            ->orderBy('terminations.coe_no', 'asc');

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
        ->select('employees.*', 'employees.created_at as created_date', 'administrations.nik', 'administrations.poh','administrations.foc', 'administrations.doh', 'administrations.class', 'projects.project_code', 'positions.position_name', 'departments.department_name')
        ->whereRaw('datediff(foc, current_date) < 30')
        ->whereNotExists(function ($query) {
            $query->select(DB::raw(1))
                ->from('terminations')
                ->whereRaw('terminations.employee_id = employees.id');
        })
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


    public function logout()
    {
        auth()->logout();
        return redirect()->route('getLogin')->with('success', 'You have been successfully logged out');
    }
}
