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
            // ->addColumn('courses_status', function ($courses) {
            //     if ($courses->courses_status == '1') {
            //         return '<span class="badge badge-success">Active</span>';
            //     } elseif ($courses->courses_status == '0') {
            //         return '<span class="badge badge-danger">Inactive</span>';
            //     }
            // })
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
    // public function courses()
    // { 
    // $courses = DB::table('courses')
    //         ->join('employees', 'courses.employee_id', '=', 'employees.id')
    //         ->select('courses.*', 'fullname')
    //         ->orderBy('fullname', 'asc')
    //         ->simplePaginate(10);
    //     return view('course.index', ['courses' => $courses]);
    // }

    // public function addCourse()
    // {
    //     $employee = Employee::orderBy('id', 'asc')->get();
    //     return view('course.create', compact('employee'));
    // }

    // public function store(Request $request)
    // {
    //     $validated = $request->validate([
    //         'employee_id' => 'required',
    //         'course_name' => 'required',
    //         'course_year' => 'required',
    //         'course_remarks' => 'required',

    //     ]);
    //     $courses = Course::create($request->all());
    //     return redirect('admin/courses')->with('status', 'Course Employee Add Successfully');
    // }

    // public function editCourse($slug)
    // {
    //     $courses = Course::where('slug', $slug)->first();
    //     $employee = Employee::orderBy('id', 'asc')->get();

    //     return view('course.edit', compact('courses', 'employee'));
    // }

    // public function updateCourse(Request $request, $slug)
    // {
    //     $courses = Course::where('slug', $slug)->first();
    //     $rules = [
    //         'employee_id' => 'required',
    //         'course_name' => 'required',
    //         'course_year' => 'required',
    //         'course_remarks' => 'required',

    //     ];

    //     $validatedData = $request->validate($rules);
    //     Course::where('slug', $slug)->update($validatedData);

    //     return redirect('admin/courses')->with('status', 'Course Employee Update Successfully');
    // }

    // public function deleteCourse($slug)
    // {

    //     $courses = Course::where('slug', $slug)->first();
    //     $courses->delete();
    //     return redirect('admin/courses')->with('status', 'Course Employee Delete Successfully');
    // }
}
