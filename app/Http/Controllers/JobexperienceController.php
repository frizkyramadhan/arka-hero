<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use App\Models\Jobexperience;

class JobexperienceController extends Controller
{
   
    public function index()
    {

       
        
        $title = ' Employee Job Experience';
        $subtitle = ' Employee Job Experience';
        $employees = Employee::orderBy('fullname', 'asc')->get();
        return view('jobexperience.index', compact('title', 'subtitle', 'employees'));
    
    }

    public function getJobexperiences(Request $request)
    {
        $jobexperiences = Jobexperience::leftJoin('employees', 'jobexperiences.employee_id', '=', 'employees.id')
            ->select('jobexperiences.*', 'employees.fullname')
            ->orderBy('jobexperiences.company_name', 'asc');

        return datatables()->of($jobexperiences)
            ->addIndexColumn()
            ->addColumn('fullname', function ($jobexperiences) {
                return $jobexperiences->fullname;
            })
            ->addColumn('company_name', function ($jobexperiences) {
                return $jobexperiences->company_name;
            })
            ->addColumn('job_position', function ($jobexperiences) {
                return $jobexperiences->job_position;
            })
            ->addColumn('job_duration', function ($jobexperiences) {
                return $jobexperiences->job_duration;
            })
            ->addColumn('quit_reason', function ($jobexperiences) {
                return $jobexperiences->quit_reason;
            })
            // ->addColumn('position_status', function ($position) {
            //     if ($position->position_status == '1') {
            //         return '<span class="badge badge-success">Active</span>';
            //     } elseif ($position->position_status == '0') {
            //         return '<span class="badge badge-danger">Inactive</span>';
            //     }
            // })
            ->filter(function ($instance) use ($request) {
                if (!empty($request->get('search'))) {
                    $instance->where(function ($w) use ($request) {
                        $search = $request->get('search');
                        $w->orWhere('fullname', 'LIKE', "%$search%")
                            ->orWhere('job_position', 'LIKE', "%$search%")
                            ->orWhere('company_name', 'LIKE', "%$search%")
                            ->orWhere('job_duration', 'LIKE', "%$search%")
                            ->orWhere('quit_reason', 'LIKE', "%$search%");
                    });
                }
            })
            ->addColumn('action', function ($jobexperiences) {
                $employees = Employee::orderBy('fullname', 'asc')->get();
                return view('jobexperience.action', compact('employees', 'jobexperiences'));
            })
            ->rawColumns(['job_position', 'action'])
            ->toJson();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Jobexperience  $jobexperience
     * @return \Illuminate\Http\Response
     */
    public function show(Jobexperience $jobexperience)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Jobexperience  $jobexperience
     * @return \Illuminate\Http\Response
     */
    public function edit(Jobexperience $jobexperience)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Jobexperience  $jobexperience
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Jobexperience $jobexperience)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Jobexperience  $jobexperience
     * @return \Illuminate\Http\Response
     */
    public function destroy(Jobexperience $jobexperience)
    {
        //
    }
}
