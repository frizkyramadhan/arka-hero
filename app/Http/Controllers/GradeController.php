<?php

namespace App\Http\Controllers;

use App\Models\Grade;
use Illuminate\Http\Request;

class GradeController extends Controller
{
    public function index()
    {
        $title = 'Grades';
        $subtitle = 'List of Grades';
        return view('grades.index', compact('title', 'subtitle'));
    }

    public function getGrades(Request $request)
    {
        $grades = Grade::orderBy('name', 'asc');

        return datatables()->of($grades)
            ->addIndexColumn()
            ->addColumn('name', function ($grade) {
                return $grade->name;
            })
            ->addColumn('is_active', function ($grade) {
                if ($grade->is_active) {
                    return '<span class="badge badge-success">Active</span>';
                } else {
                    return '<span class="badge badge-danger">Inactive</span>';
                }
            })
            ->filter(function ($instance) use ($request) {
                if (!empty($request->get('search'))) {
                    $instance->where(function ($w) use ($request) {
                        $search = $request->get('search');
                        $w->orWhere('name', 'LIKE', "%$search%");
                    });
                }
            })
            ->addColumn('action', 'grades.action')
            ->rawColumns(['is_active', 'action'])
            ->toJson();
    }

    public function create()
    {
        $title = 'Grades';
        $subtitle = 'Add Grade';
        return view('grades.create', compact('title', 'subtitle'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:grades,name',
        ]);

        Grade::create([
            'name' => $request->name,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('grades.index')->with('toast_success', 'Grade added successfully!');
    }

    public function edit(Grade $grade)
    {
        $title = 'Grades';
        $subtitle = 'Edit Grade';
        return view('grades.edit', compact('title', 'subtitle', 'grade'));
    }

    public function update(Request $request, Grade $grade)
    {
        $request->validate([
            'name' => 'required|unique:grades,name,' . $grade->id,
        ]);

        $grade->update([
            'name' => $request->name,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('grades.index')->with('toast_success', 'Grade updated successfully');
    }

    public function destroy(Grade $grade)
    {
        if ($grade->administrations()->exists()) {
            return redirect()->route('grades.index')->with('toast_error', 'Grade cannot be deleted as it is in use.');
        }
        $grade->delete();
        return redirect()->route('grades.index')->with('toast_success', 'Grade deleted successfully');
    }

    public function changeStatus($id)
    {
        $grade = Grade::findOrFail($id);
        $grade->update(['is_active' => !$grade->is_active]);
        return back()->with('toast_success', 'Grade status changed successfully.');
    }
}
