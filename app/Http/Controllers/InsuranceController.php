<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Insurance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InsuranceController extends Controller
{


    public function index()
    {
        $title = ' Employee Insurance';
        $subtitle = ' Employee Insurance';
        $employees = Employee::orderBy('fullname', 'asc')->get();
        return view('insurance.index', compact('title', 'subtitle', 'employees'));
    }

    public function getInsurances(Request $request)
    {
        $insurances = Insurance::leftJoin('employees', 'insurances.employee_id', '=', 'employees.id')
            ->select('insurances.*', 'employees.fullname')
            ->orderBy('insurances.health_insurance_no', 'asc');

        return datatables()->of($insurances)
            ->addIndexColumn()
            ->addColumn('insurances_name', function ($insurances) {
                return $insurances->fullname;
            })
            ->addColumn('health_insurance_type', function ($insurances) {
                return $insurances->health_insurance_type;
            })
            ->addColumn('health_insurance_no', function ($insurances) {
                return $insurances->health_insurance_no;
            })
            ->addColumn('health_facility', function ($insurances) {
                return $insurances->health_facility;
            })
            ->addColumn('health_insurance_remarks', function ($insurances) {
                return $insurances->health_insurance_remarks;
            })
            ->filter(function ($instance) use ($request) {
                if (!empty($request->get('search'))) {
                    $instance->where(function ($w) use ($request) {
                        $search = $request->get('search');
                        $w->orWhere('fullname', 'LIKE', "%$search%")
                            ->orWhere('health_insurance_type', 'LIKE', "%$search%")
                            ->orWhere('health_insurance_no', 'LIKE', "%$search%")
                            ->orWhere('health_facility', 'LIKE', "%$search%");
                    });
                }
            })
            ->addColumn('action', function ($insurances) {
                $employees = Employee::orderBy('fullname', 'asc')->get();
                return view('insurance.action', compact('employees', 'insurances'));
            })
            ->rawColumns(['health_insurance_no', 'action'])
            ->toJson();
    }

    public function store($employee_id, Request $request)
    {
        $request->validate([
            'employee_id' => 'required',
            'health_insurance_type' => 'required',
            'health_insurance_no' => 'required|unique:insurances|',
        ]);

        $insurances = new Insurance();
        $insurances->employee_id = $request->employee_id;
        $insurances->health_insurance_type = $request->health_insurance_type;
        $insurances->health_insurance_no = $request->health_insurance_no;
        $insurances->health_facility = $request->health_facility;
        $insurances->health_insurance_remarks = $request->health_insurance_remarks;
        $insurances->save();

        return redirect('employees/' . $employee_id . '#insurance')->with('status', 'Insurance Employee Add Successfully');
    }

    public function update(Request $request, $id)
    {
        $insurances = Insurance::where('id', $id)->first();
        $rules = [
            'employee_id' => 'required',
            'health_insurance_type' => 'required',
            'health_insurance_no' => 'required',
            'health_facility' => 'required',
            'health_insurance_remarks' => 'required',
        ];

        if ($request->health_insurance_no != $insurances->health_insurance_no) {
            $rules['health_insurance_no'] = 'unique:insurances';
        }

        $validatedData = $request->validate($rules);
        Insurance::where('id', $id)->update($validatedData);

        return redirect('employees/' . $request->employee_id . '#insurance')->with('toast_success', 'Insurance Employee Update Successfully');
    }

    public function delete($employee_id, $id)
    {
        $insurances = Insurance::where('id', $id)->first();
        $insurances->delete();
        return redirect('employees/' . $employee_id . '#insurance')->with('toast_success', 'Insurance Delete Successfully');
    }

    public function deleteAll($employee_id)
    {
        Insurance::where('employee_id', $employee_id)->delete();
        return redirect('employees/' . $employee_id . '#insurance')->with('toast_success', 'Insurance Delete Successfully');
    }
}
