<?php

namespace App\Http\Controllers;

use App\Models\Familie;
use App\Models\Family;
use App\Support\UserProject;
use Illuminate\Http\Request;

class FamilieController extends Controller
{
    public function index()
    {
        $title = ' Employee Family';
        $subtitle = ' Employee Family';
        $employees = UserProject::employeesForSelect();

        return view('familie.index', compact('title', 'subtitle', 'employees'));
    }

    public function getFamilies(Request $request)
    {
        $families = Family::leftJoin('employees', 'families.employee_id', '=', 'employees.id')
            ->select('families.*', 'employees.fullname')
            ->orderBy('families.family_birthdate', 'asc');
        UserProject::scopeQueryToEmployeesLinkedViaAdministrations($families, 'families.employee_id');

        // $families = Familie::with('employees');
        return datatables()->of($families)
            ->addIndexColumn()
            ->addColumn('fullname', function ($families) {
                return $families->fullname;
            })
            ->addColumn('family_name', function ($families) {
                return $families->family_name;
            })
            ->addColumn('family_relationship', function ($families) {
                return $families->family_relationship;
            })
            ->addColumn('family_birthplace', function ($families) {
                return $families->family_birthplace;
            })
            ->addColumn('family_birthdate', function ($families) {
                return $families->family_birthdate;
            })
            ->addColumn('family_remarks', function ($families) {
                return $families->family_remarks;
            })
            ->addColumn('bpjsks_no', function ($families) {
                return $families->bpjsks_no;
            })
            ->filter(function ($instance) use ($request) {
                if (! empty($request->get('search'))) {
                    $instance->where(function ($w) use ($request) {
                        $search = $request->get('search');
                        $w->orWhere('fullname', 'LIKE', "%$search%")
                            ->orWhere('family_name', 'LIKE', "%$search%")
                            ->orWhere('family_relationship', 'LIKE', "%$search%")
                            ->orWhere('family_birthplace', 'LIKE', "%$search%")
                            ->orWhere('family_birthdate', 'LIKE', "%$search%")
                            ->orWhere('family_remarks', 'LIKE', "%$search%")
                            ->orWhere('bpjsks_no', 'LIKE', "%$search%");
                    });
                }
            })
            ->addColumn('action', function ($families) {
                $employees = UserProject::employeesForSelect();

                return view('familie.action', compact('employees', 'families'));
            })
            ->addColumn('family_birthdate', function ($families) {
                $date = date('d F Y', strtotime($families->family_birthdate));

                return $date;
            })
            ->rawColumns(['fullname', 'action'])
            // ->addColumn('action', 'familie.action')
            ->toJson();
    }

    public function addFamilie()
    {
        $title = 'Add Family';
        $employee = UserProject::employeesForSelect();

        return view('familie.create', compact('employee', 'title'));
    }

    public function store($employee_id, Request $request)
    {
        if ($r = UserProject::guardEmployeeId($request->employee_id)) {
            return $r;
        }

        $request->validate([
            'employee_id' => 'required',
            'family_name' => 'required',
            'family_relationship' => 'required',
            'family_birthplace' => 'required',
            'family_birthdate' => 'required',
            'family_remarks' => 'required',
        ]);

        $families = new Family;
        $families->employee_id = $request->employee_id;
        $families->family_name = $request->family_name;
        $families->family_relationship = $request->family_relationship;
        $families->family_birthplace = $request->family_birthplace;
        $families->family_birthdate = $request->family_birthdate;
        $families->family_remarks = $request->family_remarks;
        $families->bpjsks_no = $request->bpjsks_no;
        $families->save();

        return redirect('employees/'.$employee_id.'#family')->with('toast_success', 'Family Employee Add Successfully');
    }

    public function update(Request $request, $id)
    {
        $family = Family::findOrFail($id);
        if ($r = UserProject::guardEmployeeId($family->employee_id)) {
            return $r;
        }

        $request->validate([
            'employee_id' => 'required',
            'family_name' => 'required',
            'family_relationship' => 'required',
            'family_birthplace' => 'required',
            'family_birthdate' => 'required',
            'family_remarks' => 'required',
        ]);

        if ($r = UserProject::guardEmployeeId($request->employee_id)) {
            return $r;
        }

        $family->employee_id = $request->employee_id;
        $family->family_name = $request->family_name;
        $family->family_relationship = $request->family_relationship;
        $family->family_birthplace = $request->family_birthplace;
        $family->family_birthdate = $request->family_birthdate;
        $family->family_remarks = $request->family_remarks;
        $family->bpjsks_no = $request->bpjsks_no;
        $family->save();

        return redirect('employees/'.$request->employee_id.'#family')->with('toast_success', 'Family Employee Update Successfully');
    }

    public function delete($employee_id, $id)
    {
        if ($r = UserProject::guardEmployeeId($employee_id)) {
            return $r;
        }
        $families = Family::where('id', $id)->firstOrFail();
        if ((int) $families->employee_id !== (int) $employee_id) {
            return UserProject::redirectAccessDenied();
        }
        $families->delete();

        return redirect('employees/'.$employee_id.'#family')->with('toast_success', 'Family Employee Delete Successfully');
    }

    public function deleteAll($employee_id)
    {
        if ($r = UserProject::guardEmployeeId($employee_id)) {
            return $r;
        }

        Family::where('employee_id', $employee_id)->delete();

        return redirect('employees/'.$employee_id.'#family')->with('toast_success', 'Family Employee Delete Successfully');
    }
}
