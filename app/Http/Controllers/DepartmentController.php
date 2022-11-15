<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;

class DepartmentController extends Controller
{
    public function departments(Request $request)
    {
        $keyword = $request->keyword;

        $departments = Department::where('department_name', 'LIKE', '%'.$keyword.'%')
                        ->paginate(5);
        return view('department.index', ['departments' => $departments]);
        // return view('project.index');
    }

    public function addDept()
    {
        return view('department.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'department_name' => 'required',
            
        ]);
        $departments = Department::create($request->all());
        return redirect('admin/departments')->with('status', 'Department Name Add Successfully');
    }

    public function editDept($slug)
    {
        $departments = Department::where('slug', $slug)->first();
        return view('department.edit', compact('departments'));
    }

    public function updateDept(Request $request, $slug)
    {
        $validated = $request->validate([
            'department_name' => 'required',
           

        ]);

        $departments = Department::where('slug', $slug)->first();
        $departments->slug = null;
        $departments->update($request->all());
        return redirect('admin/departments')->with('status', 'Department Name Edit Successfully');
    }

    public function deleteDept($slug)
    {

        $departments = Department::where('slug', $slug)->first();
        $departments->delete();
        return redirect('admin/departments')->with('status', 'Department Name Delete Successfully');
    }
}
