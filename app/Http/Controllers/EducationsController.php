<?php

namespace App\Http\Controllers;

use App\Models\Educations;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EducationsController extends Controller
{
    public function educations()
    {   
        $educations = DB::table('educations')
            ->join('employees', 'educations.employee_id', '=', 'educations.id')
            ->select('educations.*', 'fullname')
            ->orderBy('fullname', 'asc')
            ->paginate(10);
        return view('education.index', ['educations' => $educations]);
       
    }

    public function AddEducations()
    {
        $employee = Employee::orderBy('id', 'asc')->get();
        return view('education.create', compact('employee'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required',
            'education_level' => 'required',
            'education_name' => 'required',
            'education_year' => 'required',
            'education_remarks' => 'required',
           

        ]);
        $educations = Educations::create($request->all());
        return redirect('admin/educations')->with('status', 'Education Employee Add Successfully');
    }
}
