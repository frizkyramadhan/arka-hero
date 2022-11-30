<?php

namespace App\Http\Controllers;

use App\Models\Gender;
use App\Models\Employee;
use App\Models\Religion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeeController extends Controller
{
    public function employees(Request $request)
    {
        $title = 'Employees';
        $keyword = $request->keyword;
        $employees = Employee::with('religion', 'genders')
            ->where('fullname', 'LIKE', '%' . $keyword . '%')
            ->orWhere('emp_pob', 'LIKE', '%' . $keyword . '%')
            ->orWhere('emp_dob', 'LIKE', '%' . $keyword . '%')
            ->orWhere('address', 'LIKE', '%' . $keyword . '%')
            ->orWhereHas('religion', function ($query) use ($keyword) {
                $query->where('fullname', 'LIKE', '%' . $keyword . '%');
            })
            // ->orWhereHas('genders', function($query) use($keyword){
            //     $query->where('fullname', 'LIKE', '%'.$keyword.'%');
            // })                         
            ->paginate(5);

        return view('employee.index', compact('employees', 'title'));
        // $employees = DB::table('employees')
        //         ->join('religions', 'employees.religion_id', '=', 'religions.id')
        //         ->join('genders', 'employees.gender_id', '=', 'genders.id')
        //         ->select('employees.*', 'name_gender','religion_name')
        //         ->orderBy('fullname', 'asc')
        //         ->simplePaginate(10);
        //     return view('employee.index', ['employees' => $employees]);
    }

    public function addEmployee()
    {
        $religions = Religion::orderBy('religion_name', 'asc')->get();
        $genders = Gender::orderBy('name_gender', 'asc')->get();
        return view('employee.create', compact('religions', 'genders'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'fullname' => 'required',
            'emp_pob' => 'required',
        ]);

        $newName = '';

        if ($request->file('gambar')) {
            $extension = $request->file('gambar')->getClientOriginalExtension();
            $newName = $request->fullname . '-' . now()->timestamp . '.' . $extension;
            $request->file('gambar')->storeAs('cover', $newName);
        }

        $request['image'] = $newName;

        $employee = Employee::create($request->all());
        return redirect('admin/employees')->with('status', 'Employee added successfully');
    }

    public function editEmployee($slug)
    {
        $employees = Employee::where('slug', $slug)->first();
        $religions = Religion::orderBy('religion_name', 'asc')->get();
        $genders = Gender::orderBy('name_gender', 'asc')->get();

        return view('employee.edit', compact('religions', 'genders', 'employees'));
    }

    public function updateEmployee(Request $request, $slug)
    {


        if ($request->file('gambar')) {
            $extension = $request->file('gambar')->getClientOriginalExtension();
            $newName = $request->fullname . '-' . now()->timestamp . '-' . $extension;
            $request->file('gambar')->storeAs('cover', $newName);
            $request['image'] = $newName;
        }

        $employee = Employee::where('slug', $slug)->first();
        $employee->update($request->all());
        return redirect('admin/employees')->with('status', 'Employee Edit successfully');
    }



    public function deleteEmployee($slug)
    {
        $employee = Employee::where('slug', $slug)->first();
        return view('employee.delete', ['employee' => $employee]);


        // $employees = Employee::where('slug', $slug)->first();
        // $employees->delete();
        // return redirect('admin/employees')->with('status', 'Employee Delete Successfully');
    }


    public function destroyEmployee($slug)
    {
        // $employee = Employee::where('slug', $slug)->first();
        // return view('employee.delete', ['employee' => $employee]);


        $employees = Employee::where('slug', $slug)->first();
        $employees->delete();
        return redirect('admin/employees')->with('status', 'Employee Delete Successfully');
    }

    public function detailEmployee($slug)
    {
        $employees = Employee::where('slug', $slug)->first();
        $religions = Religion::orderBy('religion_name', 'asc')->get();
        $genders = Gender::orderBy('name_gender', 'asc')->get();

        return view('employee.detail', compact('religions', 'genders', 'employees'));
    }
}
