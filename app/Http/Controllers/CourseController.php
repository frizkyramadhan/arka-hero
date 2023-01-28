<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseController extends Controller
{

    public function index()
    {
        $title = ' Employee Course';
        $subtitle = ' Employee Course';
        $employees = Employee::orderBy('fullname', 'asc')->get();
        return view('course.index', compact('title', 'subtitle', 'employees'));
    }

    public function getCourse(Request $request)
    {
        $courses = Course::leftJoin('employees', 'courses.employee_id', '=', 'employees.id')
            ->select('courses.*', 'employees.fullname')
            ->orderBy('courses.course_name', 'asc');

        return datatables()->of($courses)
            ->addIndexColumn()
            ->addColumn('fullname', function ($courses) {
                return $courses->fullname;
            })
            ->addColumn('course_name', function ($courses) {
                return $courses->course_name;
            })
            ->addColumn('course_year', function ($courses) {
                return $courses->course_year;
            })
            ->addColumn('course_remarks', function ($courses) {
                return $courses->course_remarks;
            })
            ->filter(function ($instance) use ($request) {
                if (!empty($request->get('search'))) {
                    $instance->where(function ($w) use ($request) {
                        $search = $request->get('search');
                        $w->orWhere('fullname', 'LIKE', "%$search%")
                            ->orWhere('course_name', 'LIKE', "%$search%")
                            ->orWhere('course_year', 'LIKE', "%$search%")
                            ->orWhere('course_remarks', 'LIKE', "%$search%");
                    });
                }
            })
            ->addColumn('action', function ($courses) {
                $employees = Employee::orderBy('fullname', 'asc')->get();
                return view('course.action', compact('employees', 'courses'));
            })
            ->rawColumns(['course_name', 'action'])
            // ->addColumn('action', 'course.action')
            ->toJson();
    }

    public function store($employee_id, Request $request)
    {
        $request->validate([
            'employee_id' => 'required',
            'course_name' => 'required',
            'course_year' => 'required',
            'course_remarks' => 'required',

        ]);
        Course::create($request->all());
        return redirect('employees/' . $employee_id . '#courses')->with('toast_success', 'Course Employee Add Successfully');
    }

    public function update(Request $request, $id)
    {
        $rules = [
            'employee_id' => 'required',
            'course_name' => 'required',
            'course_year' => 'required',
            'course_remarks' => 'required',

        ];

        $validatedData = $request->validate($rules);
        Course::where('id', $id)->update($validatedData);

        return redirect('employees/' . $request->employee_id . '#courses')->with('toast_success', 'Course Employee Update Successfully');
    }

    public function delete($employee_id, $id)
    {
        $courses = Course::where('id', $id)->first();
        $courses->delete();
        return redirect('employees/' . $employee_id . '#courses')->with('toast_success', 'Course Employee Delete Successfully');
    }

    public function deleteAll($employee_id)
    {
        Course::where('employee_id', $employee_id)->delete();
        return redirect('employees/' . $employee_id . '#courses')->with('toast_success', 'Course Employee Delete Successfully');
    }
}
