<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use App\Models\Level;
use App\Models\Project;
use App\Models\Employee;
use App\Models\Position;
use Illuminate\Http\Request;
use App\Models\Administration;
use Illuminate\Support\Facades\DB;

class AdministrationController extends Controller
{

    public function index()
    {
        $title = ' Employee Administration';
        $subtitle = ' Employee Administration';
        $employees = Employee::orderBy('fullname', 'asc')->get();
        return view('administration.index', compact('title', 'subtitle', 'employees'));
    }

    public function getAdministration(Request $request)
    {
        $administrations = Administration::join('projects', 'administrations.project_id', '=', 'projects.id')
            ->join('employees', 'administrations.employee_id', '=', 'employees.id')
            ->join('positions', 'administrations.position_id', '=', 'positions.id')
            ->leftJoin('grades', 'administrations.grade_id', '=', 'grades.id')
            ->leftJoin('levels', 'administrations.level_id', '=', 'levels.id')
            ->select('administrations.*', 'fullname', 'position_name', 'project_name', 'grades.name as grade_name', 'levels.name as level_name')
            ->orderBy('nik', 'desc');

        return datatables()->of($administrations)
            ->addIndexColumn()
            ->addColumn('fullname', function ($administrations) {
                return $administrations->fullname;
            })
            ->addColumn('project_name', function ($administrations) {
                return $administrations->project_name;
            })
            ->addColumn('position_name', function ($administrations) {
                return $administrations->position_name;
            })
            ->addColumn('grade_name', function ($administrations) {
                return $administrations->grade_name;
            })
            ->addColumn('level_name', function ($administrations) {
                return $administrations->level_name;
            })
            ->addColumn('nik', function ($administrations) {
                return $administrations->nik;
            })
            ->addColumn('class', function ($administrations) {
                return $administrations->class;
            })
            ->addColumn('doh', function ($administrations) {
                if ($administrations->doh == null) {
                    return '-';
                } else {
                    $date = date("d F Y", strtotime($administrations->doh));
                    return $date;
                }
            })
            ->addColumn('foc', function ($administrations) {
                if ($administrations->foc == null) {
                    return '-';
                } else {
                    $date = date("d F Y", strtotime($administrations->foc));
                    return $date;
                }
            })
            ->addColumn('agreement', function ($administrations) {
                return $administrations->agreement;
            })
            ->addColumn('company_program', function ($administrations) {
                return $administrations->company_program;
            })
            ->addColumn('no_fptk', function ($administrations) {
                return $administrations->no_fptk;
            })
            ->addColumn('no_sk_active', function ($administrations) {
                return $administrations->no_sk_active;
            })
            ->addColumn('poh', function ($administrations) {
                return $administrations->poh;
            })
            ->addColumn('basic_salary', function ($administrations) {
                return $administrations->basic_salary;
            })
            ->addColumn('site_allowance', function ($administrations) {
                return $administrations->site_allowance;
            })
            ->addColumn('other_allowance', function ($administrations) {
                return $administrations->other_allowance;
            })
            ->addColumn('is_active', function ($administrations) {
                if ($administrations->is_active == '1') {
                    return '<span class="badge badge-success">Active</span>';
                } elseif ($administrations->is_active == '0') {
                    return '<span class="badge badge-danger">Inactive</span>';
                }
            })
            ->filter(function ($instance) use ($request) {
                if (!empty($request->get('search'))) {
                    $instance->where(function ($w) use ($request) {
                        $search = $request->get('search');
                        $w->orWhere('fullname', 'LIKE', "%$search%")
                            ->orWhere('project_name', 'LIKE', "%$search%")
                            ->orWhere('position_name', 'LIKE', "%$search%")
                            ->orWhere('grades.name', 'LIKE', "%$search%")
                            ->orWhere('levels.name', 'LIKE', "%$search%")
                            ->orWhere('nik', 'LIKE', "%$search%")
                            ->orWhere('doh', 'LIKE', "%$search%")
                            ->orWhere('class', 'LIKE', "%$search%")
                            ->orWhere('doh', 'LIKE', "%$search%")
                            ->orWhere('poh', 'LIKE', "%$search%")
                            ->orWhere('poh', 'LIKE', "%$search%")
                            ->orWhere('foc', 'LIKE', "%$search%")
                            ->orWhere('agreement', 'LIKE', "%$search%")
                            ->orWhere('company_program', 'LIKE', "%$search%")
                            ->orWhere('no_fptk', 'LIKE', "%$search%")
                            ->orWhere('no_sk_active', 'LIKE', "%$search%")
                            ->orWhere('is_active', 'LIKE', "%$search%");
                    });
                }
            })
            ->addColumn('action', function ($administrations) {
                $employees = Employee::orderBy('fullname', 'asc')->get();
                return view('administration.action', compact('employees', 'administrations'));
            })
            ->rawColumns(['is_active', 'action'])
            ->toJson();
    }

    public function store($employee_id, Request $request)
    {
        $request->validate([
            'employee_id' => 'required',
            'project_id' => 'required',
            'position_id' => 'required',
            'grade_id' => 'nullable|exists:grades,id',
            'level_id' => 'nullable|exists:levels,id',
            'nik' => 'required|unique:administrations',
            'class' => 'required',
            'doh' => 'required',
            'poh' => 'required',

        ]);
        $administration = new Administration;
        $administration->employee_id = $request->employee_id;
        $administration->project_id = $request->project_id;
        $administration->position_id = $request->position_id;
        $administration->grade_id = $request->grade_id;
        $administration->level_id = $request->level_id;
        $administration->nik = $request->nik;
        $administration->class = $request->class;
        $administration->doh = $request->doh;
        $administration->poh = $request->poh;
        $administration->foc = $request->foc;
        $administration->agreement = $request->agreement;
        $administration->company_program = $request->company_program;
        $administration->no_fptk = $request->no_fptk;
        $administration->no_sk_active = $request->no_sk_active;
        $administration->basic_salary = $request->basic_salary;
        $administration->site_allowance = $request->site_allowance;
        $administration->other_allowance = $request->other_allowance;
        $administration->is_active = $request->is_active;
        $administration->user_id = auth()->user()->id;
        $administration->save();

        Administration::where('employee_id', $employee_id)->where('id', '!=', $administration->id)->update(['is_active' => 0]);

        return redirect('employees/' . $employee_id . '#administration')->with('toast_success', 'Administration Added Successfully');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'employee_id' => 'required',
            'project_id' => 'required',
            'position_id' => 'required',
            'grade_id' => 'nullable|exists:grades,id',
            'level_id' => 'nullable|exists:levels,id',
            'nik' => 'required|unique:administrations,nik,' . $id,
            'class' => 'required',
            'doh' => 'required',
            'poh' => 'required',
        ]);

        $administration = Administration::where('id', $id)->first();
        $administration->employee_id = $request->employee_id;
        $administration->project_id = $request->project_id;
        $administration->position_id = $request->position_id;
        $administration->grade_id = $request->grade_id;
        $administration->level_id = $request->level_id;
        $administration->nik = $request->nik;
        $administration->class = $request->class;
        $administration->doh = $request->doh;
        $administration->poh = $request->poh;
        $administration->foc = $request->foc;
        $administration->agreement = $request->agreement;
        $administration->company_program = $request->company_program;
        $administration->no_fptk = $request->no_fptk;
        $administration->no_sk_active = $request->no_sk_active;
        $administration->basic_salary = $request->basic_salary;
        $administration->site_allowance = $request->site_allowance;
        $administration->other_allowance = $request->other_allowance;
        $administration->is_active = $request->is_active;
        $administration->termination_date = $request->termination_date;
        $administration->termination_reason = $request->termination_reason;
        $administration->coe_no = $request->coe_no;
        $administration->user_id = auth()->user()->id;
        $administration->save();

        return redirect('employees/' . $request->employee_id . '#administration')->with('toast_success', 'Administration Updated Successfully');
    }

    public function delete($employee_id, $id)
    {
        $administrations = Administration::where('id', $id)->first();
        $administrations->delete();
        return redirect('employees/' . $employee_id . '#administration')->with('toast_success', 'Administration Deleted Successfully');
    }

    public function deleteAll($employee_id)
    {
        Administration::where('employee_id', $employee_id)->delete();
        return redirect('employees/' . $employee_id . '#administration')->with('toast_success', 'Administration Deleted Successfully');
    }

    // function for change is_active status
    public function changeStatus($employee_id, $id)
    {
        $administration = Administration::find($id);
        $administration->is_active = 1;
        $administration->save();
        Administration::where('employee_id', $administration->employee_id)->where('id', '!=', $id)->update(['is_active' => 0]);

        return redirect('employees/' . $employee_id . '#administration')->with('toast_success', 'Administration Status Changed Successfully');
    }
}
