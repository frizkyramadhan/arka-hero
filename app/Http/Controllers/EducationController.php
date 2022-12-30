<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Education;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EducationController extends Controller
{

    public function index()
    {
        $title = ' Employee Education';
        $subtitle = ' Employee Education';
        $employees = Employee::orderBy('fullname', 'asc')->get();
        return view('education.index', compact('title', 'subtitle', 'employees'));
    }

    public function getEducation(Request $request)
    {
        $educations = Education::leftJoin('employees', 'educations.employee_id', '=', 'employees.id')
            ->select('educations.*', 'employees.fullname')
            ->orderBy('fullname', 'asc');

        return datatables()->of($educations)
            ->addIndexColumn()
            ->addColumn('fullname', function ($educations) {
                return $educations->fullname;
            })
            ->addColumn('education_name', function ($educations) {
                return $educations->education_name;
            })
            ->addColumn('education_address', function ($educations) {
                return $educations->education_address;
            })
            ->addColumn('education_year', function ($educations) {
                return $educations->education_year;
            })
            ->addColumn('education_remarks', function ($educations) {
                return $educations->education_remarks;
            })
            ->filter(function ($instance) use ($request) {
                if (!empty($request->get('search'))) {
                    $instance->where(function ($w) use ($request) {
                        $search = $request->get('search');
                        $w->orWhere('fullname', 'LIKE', "%$search%")
                            ->orWhere('education_address', 'LIKE', "%$search%")
                            ->orWhere('education_name', 'LIKE', "%$search%")
                            ->orWhere('education_year', 'LIKE', "%$search%")
                            ->orWhere('education_remarks', 'LIKE', "%$search%");
                    });
                }
            })
            ->addColumn('action', function ($educations) {
                $employees = Employee::orderBy('fullname', 'asc')->get();
                return view('education.action', compact('employees', 'educations'));
            })
            ->rawColumns(['education_name', 'action'])
            // ->addColumn('action', 'education.action')
            ->toJson();
    }

    public function store($employee_id, Request $request)
    {
        $request->validate([
            'employee_id' => 'required',
            'education_name' => 'required',
            'education_address' => 'required',
            'education_year' => 'required',
            'education_remarks' => 'required',

        ]);
        Education::create($request->all());
        return redirect('employees/' . $employee_id . '#educations')->with('toast_success', 'Education Employee Add Successfully');
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'employee_id' => 'required',
            'education_name' => 'required',
            'education_address' => 'required',
            'education_year' => 'required',
            'education_remarks' => 'required',
        ];
        $validatedData = $request->validate($rules);
        Education::where('id', $id)->update($validatedData);

        return redirect('employees/' . $request->employee_id . '#educations')->with('toast_success', 'Education Employee Update Successfully');
    }

    public function delete($employee_id, $id)
    {
        $educations = Education::where('id', $id)->first();
        $educations->delete();
        return redirect('employees/' . $employee_id . '#educations')->with('toast_success', 'Education Employee Delete Successfully');
    }

    public function deleteAll($employee_id)
    {
        Education::where('employee_id', $employee_id)->delete();
        return redirect('employees/' . $employee_id . '#educations')->with('toast_success', 'Education Employee Delete Successfully');
    }
}
