<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function index()
    {
        $title = 'Projects';
        $subtitle = 'List of Project';
        return view('project.index', compact('title', 'subtitle'));
    }

    public function getProjects(Request $request)
    {
        $projects = Project::orderBy('project_code', 'asc');

        return datatables()->of($projects)
            ->addIndexColumn()
            ->addColumn('project_code', function ($projects) {
                return $projects->project_code;
            })
            ->addColumn('project_name', function ($projects) {
                return $projects->project_name;
            })
            ->addColumn('project_location', function ($projects) {
                return $projects->project_location;
            })
            ->addColumn('bowheer', function ($projects) {
                return $projects->bowheer;
            })
            ->addColumn('leave_type', function ($projects) {
                if ($projects->leave_type == 'roster') {
                    return '<span class="badge badge-warning">Roster</span>';
                } elseif ($projects->leave_type == 'non_roster') {
                    return '<span class="badge badge-info">Non-Roster</span>';
                } else {
                    return '<span class="badge badge-secondary">Not Set</span>';
                }
            })
            ->addColumn('project_status', function ($projects) {
                if ($projects->project_status == '1') {
                    return '<span class="badge badge-success">Active</span>';
                } elseif ($projects->project_status == '0') {
                    return '<span class="badge badge-danger">Inactive</span>';
                }
            })
            ->filter(function ($instance) use ($request) {
                if (!empty($request->get('search'))) {
                    $instance->where(function ($w) use ($request) {
                        $search = $request->get('search');
                        $w->orWhere('project_code', 'LIKE', "%$search%")
                            ->orWhere('project_name', 'LIKE', "%$search%")
                            ->orWhere('project_location', 'LIKE', "%$search%")
                            ->orWhere('bowheer', 'LIKE', "%$search%")
                            ->orWhere('leave_type', 'LIKE', "%$search%")
                            ->orWhere('project_status', 'LIKE', "%$search%");
                    });
                }
            })
            ->addColumn('action', 'project.action')
            ->rawColumns(['leave_type', 'project_status', 'action'])
            ->toJson();
    }

    public function addProjects()
    {
        return view('project.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'project_code' => 'required',
            'project_name' => 'required',
            'project_location' => 'required',
            'bowheer' => 'required',
            'leave_type' => 'required|in:non_roster,roster',
            'project_status' => 'required',
        ], [
            'project_code.required' => 'Project Code is required',
            'project_name.required' => 'Project Name is required',
            'project_location.required' => 'Project Location is required',
            'bowheer.required' => 'Bowheer is required',
            'leave_type.required' => 'Leave Type is required',
            'leave_type.in' => 'Leave Type must be either Non-Roster or Roster',
            'project_status.required' => 'Project Status is required',
        ]);

        Project::create($validatedData);

        return redirect('projects')->with('toast_success', 'Project added successfully!');
    }

    public function edit($slug)
    {
        $project = Project::where('slug', $slug)->first();
        return view('project.edit', compact('project'));
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'project_code' => 'required',
            'project_name' => 'required',
            'project_location' => 'required',
            'bowheer' => 'required',
            'leave_type' => 'required|in:non_roster,roster',
            'project_status' => 'required',
        ], [
            'project_code.required' => 'Project Code is required',
            'project_name.required' => 'Project Name is required',
            'project_location.required' => 'Project Location is required',
            'bowheer.required' => 'Bowheer is required',
            'leave_type.required' => 'Leave Type is required',
            'leave_type.in' => 'Leave Type must be either Non-Roster or Roster',
            'project_status.required' => 'Project Status is required',
        ]);

        $projects = Project::find($id);
        $projects->project_code = $request->project_code;
        $projects->project_name = $request->project_name;
        $projects->project_location = $request->project_location;
        $projects->bowheer = $request->bowheer;
        $projects->leave_type = $request->leave_type;
        $projects->project_status = $request->project_status;
        $projects->save();

        return redirect('projects')->with('toast_success', 'Project edited successfully');
    }

    public function destroy($id)
    {
        $project = Project::where('id', $id)->first();
        $project->delete();
        return redirect('projects')->with('toast_success', 'Project delete successfully');
    }
}
