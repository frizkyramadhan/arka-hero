<?php

namespace App\Http\Controllers;

use App\Models\Operableunit;
use App\Support\UserProject;
use Illuminate\Http\Request;

class OperableunitController extends Controller
{
    public function index()
    {
        $title = 'Operable Unit';
        $subtitle = 'Operable Unit';
        $employees = UserProject::employeesForSelect(null, UserProject::EMPLOYEE_SELECT_ACTIVE_ADMINISTRATION);

        return view('operableunit.index', compact('title', 'subtitle', 'employees'));
    }

    public function getOperableunits(Request $request)
    {
        $operableunits = Operableunit::leftJoin('employees', 'operableunits.employee_id', '=', 'employees.id')
            ->select('operableunits.*', 'employees.fullname')
            ->orderBy('operableunits.unit_name', 'asc');
        UserProject::scopeQueryToEmployeesLinkedViaAdministrations($operableunits, 'operableunits.employee_id');

        return datatables()->of($operableunits)
            ->addIndexColumn()
            ->addColumn('fullname', function ($operableunits) {
                return $operableunits->fullname;
            })
            ->addColumn('unit_name', function ($operableunits) {
                return $operableunits->unit_name;
            })
            ->addColumn('unit_type', function ($operableunits) {
                return $operableunits->unit_type;
            })
            ->addColumn('unit_remarks', function ($operableunits) {
                return $operableunits->unit_remarks;
            })
            ->filter(function ($instance) use ($request) {
                if (! empty($request->get('search'))) {
                    $instance->where(function ($w) use ($request) {
                        $search = $request->get('search');
                        $w->orWhere('fullname', 'LIKE', "%$search%")
                            ->orWhere('unit_name', 'LIKE', "%$search%")
                            ->orWhere('unit_type', 'LIKE', "%$search%")
                            ->orWhere('unit_remarks', 'LIKE', "%$search%");
                    });
                }
            })
            ->addColumn('action', function ($operableunits) {
                $employees = UserProject::employeesForSelect(null, UserProject::EMPLOYEE_SELECT_ACTIVE_ADMINISTRATION);

                return view('operableunit.action', compact('employees', 'operableunits'));
            })
            ->rawColumns(['unit_name', 'action'])
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
     * @return \Illuminate\Http\Response
     */
    public function store($employee_id, Request $request)
    {
        if ($r = UserProject::guardEmployeeId($request->employee_id)) {
            return $r;
        }

        $request->validate([
            'employee_id' => 'required',
            'unit_name' => 'required',
            'unit_type' => 'required',
            'unit_remarks' => 'required',
        ]);

        $operableunit = new Operableunit;
        $operableunit->employee_id = $request->employee_id;
        $operableunit->unit_name = $request->unit_name;
        $operableunit->unit_type = $request->unit_type;
        $operableunit->unit_remarks = $request->unit_remarks;
        $operableunit->save();

        return redirect('employees/'.$employee_id.'#unit')->with('toast_success', 'Operable Unit Added Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Operableunit $operableunit)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Operableunit $operableunit)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Models\Operableunit  $operableunit
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $operableunit = Operableunit::findOrFail($id);
        if ($r = UserProject::guardEmployeeId($operableunit->employee_id)) {
            return $r;
        }

        $request->validate([
            'employee_id' => 'required',
            'unit_name' => 'required',
            'unit_type' => 'required',
            'unit_remarks' => 'required',
        ]);

        if ($r = UserProject::guardEmployeeId($request->employee_id)) {
            return $r;
        }

        $operableunit->employee_id = $request->employee_id;
        $operableunit->unit_name = $request->unit_name;
        $operableunit->unit_type = $request->unit_type;
        $operableunit->unit_remarks = $request->unit_remarks;
        $operableunit->save();

        return redirect('employees/'.$request->employee_id.'#unit')->with('toast_success', 'Operable Unit Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Operableunit  $operableunit
     * @return \Illuminate\Http\Response
     */
    public function delete($employee_id, $id)
    {
        if ($r = UserProject::guardEmployeeId($employee_id)) {
            return $r;
        }
        $operableunit = Operableunit::findOrFail($id);
        if ((int) $operableunit->employee_id !== (int) $employee_id) {
            return UserProject::redirectAccessDenied();
        }
        $operableunit->delete();

        return redirect('employees/'.$employee_id.'#unit')->with('toast_success', 'Operable Unit Deleted Successfully');
    }

    public function deleteAll($employee_id)
    {
        if ($r = UserProject::guardEmployeeId($employee_id)) {
            return $r;
        }

        Operableunit::where('employee_id', $employee_id)->delete();

        return redirect('employees/'.$employee_id.'#unit')->with('toast_success', 'Operable Unit Deleted Successfully');
    }
}
