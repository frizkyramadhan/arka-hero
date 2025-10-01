<?php

namespace App\Http\Controllers;

use App\Models\Administration;
use App\Models\Project;
use App\Models\Employee;
use App\Models\Position;
use App\Models\Department;
use App\Models\Termination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TerminationController extends Controller
{
    public function __construct()
    {
        $this->middleware('role_or_permission:employees.termination')->only('index');
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = 'Termination';
        $subtitle = 'List of Terminated Employees';

        return view('termination.index', compact('subtitle', 'title'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = 'Termination';
        $subtitle = 'Termination Form';
        $departments = Department::where('department_status', '1')->orderBy('department_name', 'asc')->get();
        $positions = Position::where('position_status', '1')->orderBy('position_name', 'asc')->get();
        $projects = Project::where('project_status', '1')->orderBy('project_code', 'asc')->get();

        return view('termination.create', compact('subtitle', 'title', 'departments', 'positions', 'projects'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    // public function store(Request $request)
    // {
    //     $request->validate([
    //         'termination_date' => 'required',
    //         'termination_reason' => 'required',
    //         'coe_no' => 'required',
    //     ]);

    //     $termination = new Termination();
    //     $termination->termination_date = $request->termination_date;
    //     $termination->termination_reason = $request->termination_reason;
    //     $termination->coe_no = $request->coe_no;
    //     $termination->user_id = auth()->user()->id;
    //     $termination->save();

    //     return redirect('employees')->with('toast_success', 'Employee Terminated Successfully');
    // }

    // public function show(Termination $termination)
    // {
    //     //
    // }

    // public function edit(Termination $termination)
    // {
    //     //
    // }

    public function update(Request $request, $id)
    {
        $request->validate([
            'termination_date' => 'required',
            'termination_reason' => 'required',
            'coe_no' => 'required',
        ]);

        $administration = Administration::where('id', $id)->first();
        $administration->termination_date = $request->termination_date;
        $administration->termination_reason = $request->termination_reason;
        $administration->coe_no = $request->coe_no;
        $administration->is_active = 0; // Set to inactive when termination data is updated
        $administration->user_id = auth()->user()->id;
        $administration->save();

        return back()->with('toast_success', 'Employee Termination Updated Successfully');
    }

    public function delete($id)
    {
        $administration = Administration::where('id', $id)->first();
        $administration->termination_date = NULL;
        $administration->termination_reason = NULL;
        $administration->coe_no = NULL;
        $administration->is_active = 1;
        $administration->user_id = auth()->user()->id;
        $administration->save();

        return back()->with('toast_success', 'Employee Termination Deleted Successfully');
    }

    public function getTerminations(Request $request)
    {
        $employee = Employee::leftJoin('administrations', 'employees.id', '=', 'administrations.employee_id')
            ->leftJoin('projects', 'administrations.project_id', '=', 'projects.id')
            ->leftJoin('positions', 'administrations.position_id', '=', 'positions.id')
            ->leftJoin('departments', 'positions.department_id', '=', 'departments.id')
            ->select('employees.fullname', 'employees.created_at as created_date', 'administrations.*', 'projects.project_code', 'positions.position_name', 'departments.department_name')
            ->where('administrations.is_active', 0)
            // ->whereExists(function ($query) {
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
            ->addColumn('department_name', function ($employee) {
                return $employee->department_name;
            })
            ->addColumn('position_name', function ($employee) {
                return $employee->position_name;
            })
            ->addColumn('project_code', function ($employee) {
                return $employee->project_code;
            })
            ->addColumn('doh', function ($employee) {
                return date('d-M-Y', strtotime($employee->doh));
            })
            ->addColumn('termination_date', function ($employee) {
                return date('d-M-Y', strtotime($employee->termination_date));
            })
            ->addColumn('termination_reason', function ($employee) {
                return $employee->termination_reason;
            })
            ->addColumn('coe_no', function ($employee) {
                return $employee->coe_no;
            })
            ->filter(function ($instance) use ($request) {
                if (!empty($request->get('search'))) {
                    $instance->where(function ($w) use ($request) {
                        $search = $request->get('search');
                        $w->orWhere('nik', 'LIKE', "%$search%")
                            ->orWhere('fullname', 'LIKE', "%$search%")
                            ->orWhere('doh', 'LIKE', "%$search%")
                            ->orWhere('department_name', 'LIKE', "%$search%")
                            ->orWhere('position_name', 'LIKE', "%$search%")
                            ->orWhere('project_code', 'LIKE', "%$search%")
                            ->orWhere('termination_date', 'LIKE', "%$search%")
                            ->orWhere('termination_reason', 'LIKE', "%$search%")
                            ->orWhere('coe_no', 'LIKE', "%$search%");
                    });
                }
            })
            ->addColumn('action', 'termination.action')
            ->rawColumns(['action'])
            ->toJson();
    }

    public function getEmployees(Request $request)
    {
        $employee = Employee::leftJoin('administrations', 'employees.id', '=', 'administrations.employee_id')
            ->leftJoin('projects', 'administrations.project_id', '=', 'projects.id')
            ->leftJoin('positions', 'administrations.position_id', '=', 'positions.id')
            ->leftJoin('departments', 'positions.department_id', '=', 'departments.id')
            ->select('employees.fullname', 'employees.created_at as created_date', 'administrations.*', 'projects.project_code', 'positions.position_name', 'departments.department_name')
            ->where('administrations.is_active', 1)
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
                            ->orWhere('project_code', 'LIKE', "%$search%")
                            ->orWhere('class', 'LIKE', "%$search%")
                            ->orWhere('employees.created_at', 'LIKE', "%$search%");
                    });
                }
            })
            ->addColumn('checkbox', '
            <div class="form-check">
                <input type="checkbox" name="ids_check[]" class="form-check-input" value="{{$id}}">
            </div>')
            ->addColumn('coe_no', '
            <div class="form-group">
                <input type="text" name="coe_no[]" class="form-control">
            </div>')
            ->rawColumns(['checkbox', 'coe_no'])
            ->toJson();
    }

    // massTermination
    public function massTermination(Request $request)
    {
        $ids = $request->ids_check;
        $termination_date = $request->termination_date;
        $termination_reason = $request->termination_reason;

        $administration = Administration::whereIn('id', $ids)->get();

        foreach ($administration as $key => $value) {
            $termination = Administration::find($value->id);
            $termination->termination_date = $termination_date;
            $termination->termination_reason = $termination_reason;
            $termination->coe_no = $request->coe_no[$key];
            $termination->is_active = 0;
            $termination->user_id = auth()->user()->id;
            $termination->save();
        }

        return redirect('terminations')->with('toast_success', 'Termination Added Successfully');
    }
}
