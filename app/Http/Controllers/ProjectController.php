<?php

namespace App\Http\Controllers;

use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function projects(Request $request)
    {
        $keyword = $request->keyword;
        $projects = Project::where('project_code', 'LIKE', '%'.$keyword.'%')
                            ->orWhere('project_name','LIKE', '%'.$keyword.'%')
                            ->orWhere('project_location','LIKE', '%'.$keyword.'%')
                            ->orWhere('bowheer','LIKE', '%'.$keyword.'%')
                            ->paginate(5);
        return view('project.index', ['projects' => $projects]);
        // return view('project.index');
    }

    public function addProjects()
    {
        return view('project.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'project_code' => 'required|unique:projects|',
            'project_name' => 'required|unique:projects|',
            'project_location' => 'required',
            'bowheer' => 'required',
            
        ]);
        $projects = Project::create($request->all());
        return redirect('admin/projects')->with('status', 'Project Add Successfully');
    }

    public function editProject($slug)
    {
        $project = Project::where('slug', $slug)->first();
        return view('project.edit', compact('project'));
    }

    public function updateProject(Request $request, $slug)
    {
        $validated = $request->validate([
            'project_code' => 'required',
            'project_name' => 'required',
            'project_location' => 'required',
            'bowheer' => 'required',

        ]);

        $projects = Project::where('slug', $slug)->first();
        $projects->slug = null;
        $projects->update($request->all());
        return redirect('admin/projects')->with('status', 'Project Edit Successfully');
    }

    public function deleteProject($slug)
    {

        $projects = Project::where('slug', $slug)->first();
        $projects->delete($slug);
        return redirect('admin/projects')->with('status', 'Project Delete Successfully');
    }
}
