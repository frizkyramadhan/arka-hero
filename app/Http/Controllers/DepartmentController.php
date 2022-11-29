<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Illuminate\Http\Request;
use App\Imports\DepartmentImport;
use Maatwebsite\Excel\Facades\Excel;

class DepartmentController extends Controller
{
    public function index()
    {
        $title = 'Departments';
        $subtitle = 'List of Department';
        return view('department.index', compact('title', 'subtitle'));
    }

    public function getDepartments(Request $request)
    {
        $department = Department::orderBy('department_name', 'asc');

        return datatables()->of($department)
            ->addIndexColumn()
            ->addColumn('department_name', function ($department) {
                return $department->department_name;
            })
            ->addColumn('slug', function ($department) {
                return $department->slug;
            })
            ->addColumn('department_status', function ($department) {
                if ($department->department_status == '1') {
                    return '<span class="badge badge-success">Active</span>';
                } elseif ($department->department_status == '0') {
                    return '<span class="badge badge-danger">Inactive</span>';
                }
            })
            ->filter(function ($instance) use ($request) {
                if (!empty($request->get('search'))) {
                    $instance->where(function ($w) use ($request) {
                        $search = $request->get('search');
                        $w->orWhere('department_name', 'LIKE', "%$search%")
                            ->orWhere('slug', 'LIKE', "%$search%")
                            ->orWhere('department_status', 'LIKE', "%$search%");
                    });
                }
            })
            ->addColumn('action', 'department.action')
            ->rawColumns(['department_status', 'action'])
            ->toJson();
    }

    public function addDepartments()
    {
        return view('department.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'department_name' => 'required',
            'slug' => 'required',
            'department_status' => 'required',
        ], [
            'department_name.required' => 'Department Name is required',
            'slug.required' => 'slug is required',
            'department_status.required' => 'Department Status is required',
        ]);

        Department::create($validatedData);

        return redirect('departments')->with('toast_success', 'Department added successfully!');
    }

    public function edit($slug)
    {
        $department = Department::where('slug', $slug)->first();
        return view('department.edit', compact('department'));
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'department_name' => 'required',
            'slug' => 'required',
            'department_status' => 'required',
        ], [
            'department_name.required' => 'Department Name is required',
            'slug.required' => 'slug is required',
            'department_status.required' => 'Department Status is required',
        ]);

        $department = Department::find($id);
        $department->department_name = $request->department_name;
        $department->slug = $request->slug;
        $department->department_status = $request->department_status;
        $department->save();

        return redirect('departments')->with('toast_success', 'Department edited successfully');
    }

    public function destroy($id)
    {
        $department = Department::where('id', $id)->first();
        $department->delete();
        return redirect('departments')->with('toast_success', 'Department delete successfully');
    }

    public function import(Request $request)
    {
        $this->validate($request, [
            'file' => 'required|mimes:xls,xlsx'
        ]);

        Excel::import(new DepartmentImport, request()->file('file'));

        return redirect('departments')->with('toast_success', 'Department imported successfully');
    }
}
