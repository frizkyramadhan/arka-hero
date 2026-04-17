<?php

namespace App\Http\Controllers;

use App\Models\Jobexperience;
use App\Support\UserProject;
use Illuminate\Http\Request;

class JobexperienceController extends Controller
{
    public function index()
    {
        $title = ' Employee Job Experience';
        $subtitle = ' Employee Job Experience';
        $employees = UserProject::employeesForSelect();

        return view('jobexperience.index', compact('title', 'subtitle', 'employees'));
    }

    public function getJobexperiences(Request $request)
    {
        $jobexperiences = Jobexperience::leftJoin('employees', 'jobexperiences.employee_id', '=', 'employees.id')
            ->select('jobexperiences.*', 'employees.fullname')
            ->orderBy('jobexperiences.company_name', 'asc');
        UserProject::scopeQueryToEmployeesLinkedViaAdministrations($jobexperiences, 'jobexperiences.employee_id');

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
            ->filter(function ($instance) use ($request) {
                if (! empty($request->get('search'))) {
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
                $employees = UserProject::employeesForSelect();

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
     * @return \Illuminate\Http\Response
     */
    public function store($employee_id, Request $request)
    {
        if ($r = UserProject::guardEmployeeId($request->employee_id)) {
            return $r;
        }

        $request->validate([
            'employee_id' => 'required',
            'company_name' => 'required',
            'company_address' => 'required',
            'job_position' => 'required',
            'job_duration' => 'required',
            'quit_reason' => 'required',
        ]);

        $jobexperience = new Jobexperience;
        $jobexperience->employee_id = $request->employee_id;
        $jobexperience->company_name = $request->company_name;
        $jobexperience->company_address = $request->company_address;
        $jobexperience->job_position = $request->job_position;
        $jobexperience->job_duration = $request->job_duration;
        $jobexperience->quit_reason = $request->quit_reason;
        $jobexperience->save();

        return redirect('employees/'.$employee_id.'#jobexp')->with('toast_success', 'Job Experience Added Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Jobexperience $jobexperience)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Jobexperience $jobexperience)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Models\Jobexperience  $jobexperience
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $jobexperience = Jobexperience::findOrFail($id);
        if ($r = UserProject::guardEmployeeId($jobexperience->employee_id)) {
            return $r;
        }

        $request->validate([
            'employee_id' => 'required',
            'company_name' => 'required',
            'company_address' => 'required',
            'job_position' => 'required',
            'job_duration' => 'required',
            'quit_reason' => 'required',
        ]);

        if ($r = UserProject::guardEmployeeId($request->employee_id)) {
            return $r;
        }

        $jobexperience->employee_id = $request->employee_id;
        $jobexperience->company_name = $request->company_name;
        $jobexperience->company_address = $request->company_address;
        $jobexperience->job_position = $request->job_position;
        $jobexperience->job_duration = $request->job_duration;
        $jobexperience->quit_reason = $request->quit_reason;
        $jobexperience->save();

        return redirect('employees/'.$jobexperience->employee_id.'#jobexp')->with('toast_success', 'Job Experience Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Jobexperience  $jobexperience
     * @return \Illuminate\Http\Response
     */
    public function delete($employee_id, $id)
    {
        if ($r = UserProject::guardEmployeeId($employee_id)) {
            return $r;
        }
        $jobexperience = Jobexperience::findOrFail($id);
        if ((int) $jobexperience->employee_id !== (int) $employee_id) {
            return UserProject::redirectAccessDenied();
        }
        $jobexperience->delete();

        return redirect('employees/'.$employee_id.'#jobexp')->with('toast_success', 'Job Experience Deleted Successfully');
    }

    public function deleteAll($employee_id)
    {
        if ($r = UserProject::guardEmployeeId($employee_id)) {
            return $r;
        }

        Jobexperience::where('employee_id', $employee_id)->delete();

        return redirect('employees/'.$employee_id.'#jobexp')->with('toast_success', 'Job Experience Deleted Successfully');
    }
}
