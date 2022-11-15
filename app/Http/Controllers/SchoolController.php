<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\School;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SchoolController extends Controller
{
    public function schools(Request $request)
    {  
        $keyword = $request->keyword;  
        $schools = School::with('employees')
                            ->where('education_level', 'LIKE', '%'.$keyword.'%')
                            ->orWhere('education_name', 'LIKE', '%'.$keyword.'%')
                            ->orWhereHas('employees', function($query) use($keyword){
                                $query->where('fullname', 'LIKE', '%'.$keyword.'%');
                            })                        
                            ->paginate(5);
                            
        return view('school.index', ['schools' => $schools]);
         
            // $schools = DB::table('schools')
            //     ->join('employees', 'schools.employee_id', '=', 'schools.id')
            //     ->select('schools.*', 'fullname')
            //     ->orderBy('fullname', 'asc')
            //     ->simplePaginate(10);
            // return view('school.index', ['schools' => $schools]);      
    }

    public function AddSchool()
    {
        $employee = Employee::orderBy('id', 'asc')->get();
        return view('school.create', compact('employee'));
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
        $schools = School::create($request->all());
        return redirect('admin/schools')->with('status', 'Education Employee Add Successfully');
    }

    public function EditSchool($slug)
    {
        $schools = School::where('slug', $slug)->first();
        $employee = Employee::orderBy('id', 'asc')->get();

        return view('school.edit', compact('schools', 'employee'));
    }

    public function UpdateSchool(Request $request, $slug)
    {
        $schools = School::where('slug', $slug)->first();
        $rules = [
            'employee_id' => 'required',
            'education_level' => 'required',
            'education_name' => 'required',
            'education_year' => 'required',
            'education_remarks' => 'required',
        ];
        $validatedData = $request->validate($rules);
        School::where('slug', $slug)->update($validatedData);

        return redirect('admin/schools')->with('status', 'Education Employee Update Successfully');
    }

    public function deleteSchool($slug)
    {

        $schools = School::where('slug', $slug)->first();
        $schools->delete();
        return redirect('admin/schools')->with('status', 'Education Employee Delete Successfully');
    }

}
