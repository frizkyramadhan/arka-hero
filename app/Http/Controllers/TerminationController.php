<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Termination;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TerminationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = 'Terminated Employees';
        $subtitle = 'List of Terminated Employees';

        return view('termination.index', compact('subtitle', 'title'));
    }

    public function getTerminations(Request $request)
    {
        $employee = Employee::leftJoin('administrations', 'employees.id', '=', 'administrations.employee_id')
            ->leftJoin('projects', 'administrations.project_id', '=', 'projects.id')
            ->leftJoin('positions', 'administrations.position_id', '=', 'positions.id')
            ->leftJoin('departments', 'positions.department_id', '=', 'departments.id')
            ->leftJoin('terminations', 'employees.id', '=', 'terminations.employee_id')
            ->select('employees.*', 'employees.created_at as created_date', 'administrations.nik', 'administrations.poh', 'administrations.doh', 'administrations.class', 'projects.project_code', 'positions.position_name', 'departments.department_name', 'terminations.id as termination_id', 'terminations.termination_date', 'terminations.termination_reason', 'terminations.coe_no')
            ->whereExists(function ($query) {
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

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required',
            'termination_date' => 'required',
            'termination_reason' => 'required',
            'coe_no' => 'required',
        ]);

        $termination = new Termination();
        $termination->employee_id = $request->employee_id;
        $termination->termination_date = $request->termination_date;
        $termination->termination_reason = $request->termination_reason;
        $termination->coe_no = $request->coe_no;
        $termination->user_id = auth()->user()->id;
        $termination->save();

        return redirect('employees')->with('toast_success', 'Employee Terminated Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Termination  $termination
     * @return \Illuminate\Http\Response
     */
    public function show(Termination $termination)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Termination  $termination
     * @return \Illuminate\Http\Response
     */
    public function edit(Termination $termination)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Termination  $termination
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Termination $termination)
    {
        $request->validate([
            'employee_id' => 'required',
            'termination_date' => 'required',
            'termination_reason' => 'required',
            'coe_no' => 'required',
        ]);

        $termination->employee_id = $request->employee_id;
        $termination->termination_date = $request->termination_date;
        $termination->termination_reason = $request->termination_reason;
        $termination->coe_no = $request->coe_no;
        $termination->user_id = auth()->user()->id;
        $termination->save();

        return back()->with('toast_success', 'Employee Termination Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Termination  $termination
     * @return \Illuminate\Http\Response
     */
    public function destroy(Termination $termination)
    {
        $termination->delete();
        return back()->with('toast_success', 'Employee Termination Deleted Successfully');
    }
}
