<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CourseController extends Controller
{
    public function courses()
    { 
    $courses = DB::table('courses')
            ->join('employees', 'courses.employee_id', '=', 'employees.id')
            ->select('courses.*', 'fullname')
            ->orderBy('fullname', 'asc')
            ->simplePaginate(10);
        return view('course.index', ['courses' => $courses]);
    }

    public function addCourse()
    {
        $employee = Employee::orderBy('id', 'asc')->get();
        return view('course.create', compact('employee'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required',
            'course_name' => 'required',
            'course_year' => 'required',
            'course_remarks' => 'required',

        ]);
        $courses = Course::create($request->all());
        return redirect('admin/courses')->with('status', 'Course Employee Add Successfully');
    }

    public function editCourse($slug)
    {
        $courses = Course::where('slug', $slug)->first();
        $employee = Employee::orderBy('id', 'asc')->get();

        return view('course.edit', compact('courses', 'employee'));
    }

    public function updateCourse(Request $request, $slug)
    {
        $courses = Course::where('slug', $slug)->first();
        $rules = [
            'employee_id' => 'required',
            'course_name' => 'required',
            'course_year' => 'required',
            'course_remarks' => 'required',

        ];

        $validatedData = $request->validate($rules);
        Course::where('slug', $slug)->update($validatedData);

        return redirect('admin/courses')->with('status', 'Course Employee Update Successfully');
    }

    public function deleteCourse($slug)
    {

        $courses = Course::where('slug', $slug)->first();
        $courses->delete();
        return redirect('admin/courses')->with('status', 'Course Employee Delete Successfully');
    }
}
