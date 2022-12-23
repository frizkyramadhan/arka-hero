<?php

namespace App\Http\Controllers;

use App\Models\Educations;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EducationsController extends Controller
{
    public function index()
    {
        $title = ' Employee Education';
        $subtitle = ' Employee Education';
        $employees = Employee::orderBy('fullname', 'asc')->get();
        return view('education.index', compact('title', 'subtitle', 'employees'));
    }

    public function getEducations(Request $request)
    {
        $educations = Educations::leftJoin('employees', 'educations.employee_id', '=', 'employees.id')
            ->select('educations.*', 'employees.fullname')
            ->orderBy('educations.education_name', 'asc');

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
            // ->addColumn('educations_status', function ($educations) {
            //     if ($educations->educations_status == '1') {
            //         return '<span class="badge badge-success">Active</span>';
            //     } elseif ($educations->educations_status == '0') {
            //         return '<span class="badge badge-danger">Inactive</span>';
            //     }
            // })
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
            ->toJson();
    }

    // public function store(Request $request)
    // {
    //     $validated = $request->validate([
    //         'employee_id' => 'required',
    //         'education_level' => 'required',
    //         'education_name' => 'required',
    //         'education_year' => 'required',
    //         'education_remarks' => 'required',


    //     ]);
    //     $educations = Educations::create($request->all());
    //     return redirect('admin/educations')->with('status', 'Education Employee Add Successfully');
    // }
}
