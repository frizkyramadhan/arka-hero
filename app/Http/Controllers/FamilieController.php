<?php

namespace App\Http\Controllers;

use App\Models\Familie;
use App\Models\Employee;
use App\Models\Family;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FamilieController extends Controller
{

    public function index()
    {
        $title = ' Employee Family';
        $subtitle = ' Employee Family';
        $employees = Employee::orderBy('fullname', 'asc')->get();
        return view('familie.index', compact('title', 'subtitle', 'employees'));
    }

    public function getFamilies(Request $request)
    {
        $families = Family::leftJoin('employees', 'families.employee_id', '=', 'employees.id')
            ->select('families.*', 'employees.fullname')
            ->orderBy('families.family_birthdate', 'asc');
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
            // ->addColumn('families_status', function ($families) {
            //     if ($families->families_status == '1') {
            //         return '<span class="badge badge-success">Active</span>';
            //     } elseif ($families->families_status == '0') {
            //         return '<span class="badge badge-danger">Inactive</span>';
            //     }
            // })
            ->filter(function ($instance) use ($request) {
                if (!empty($request->get('search'))) {
                    $instance->where(function ($w) use ($request) {
                        $search = $request->get('search');
                        $w->orWhere('fullname', 'LIKE', "%$search%")
                            ->orWhere('family_name', 'LIKE', "%$search%")
                            ->orWhere('family_relationship', 'LIKE', "%$search%")
                            ->orWhere('family_birthplace', 'LIKE', "%$search%")
                            ->orWhere('family_birthdate', 'LIKE', "%$search%")
                            ->orWhere('family_remarks', 'LIKE', "%$search%");
                    });
                }
            })
            ->addColumn('action', function ($families) {
                $employees = Employee::orderBy('fullname', 'asc')->get();
                return view('familie.action', compact('employees', 'families'));
            })
            ->addColumn('family_birthdate', function ($families) {
                $date = date("d F Y", strtotime($families->family_birthdate));
                return $date;
            })
            ->rawColumns(['fullname', 'action'])
            // ->addColumn('action', 'familie.action')
            ->toJson();
    }

    public function addFamilie()
    {
        $title = 'Add Family';
        $employee = Employee::orderBy('id', 'asc')->get();
        return view('familie.create', compact('employee', 'title'));
    }


    public function store($employee_id, Request $request)
    {
        $request->validate([
            'employee_id' => 'required',
            'family_name' => 'required',
            'family_relationship' => 'required',
            'family_birthplace' => 'required',
            'family_birthdate' => 'required',
            'family_remarks' => 'required',
        ]);

        $families = new Family();
        $families->employee_id = $request->employee_id;
        $families->family_name = $request->family_name;
        $families->family_relationship = $request->family_relationship;
        $families->family_birthplace = $request->family_birthplace;
        $families->family_birthdate = $request->family_birthdate;
        $families->family_remarks = $request->family_remarks;
        $families->save();

        return redirect('employees/' . $employee_id . '#families')->with('toast_success', 'Family Employee Add Successfully');
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'employee_id' => 'required',
            'family_name' => 'required',
            'family_relationship' => 'required',
            'family_birthplace' => 'required',
            'family_birthdate' => 'required',
            'family_remarks' => 'required',
        ];
        $validatedData = $request->validate($rules);
        Family::where('id', $id)->update($validatedData);

        return redirect('employees/' . $request->employee_id . '#families')->with('toast_success', 'Family Employee Update Successfully');
    }

    public function delete($employee_id, $id)
    {
        $families = Family::where('id', $id)->first();
        $families->delete();
        return redirect('employees/' . $employee_id . '#families')->with('toast_success', 'Family Employee Delete Successfully');
    }

    public function deleteAll($employee_id)
    {
        Family::where('employee_id', $employee_id)->delete();
        return redirect('employees/' . $employee_id . '#families')->with('toast_success', 'Family Employee Delete Successfully');
    }
}
