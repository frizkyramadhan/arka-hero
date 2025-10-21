<?php

namespace App\Http\Controllers;

use App\Models\Level;
use Illuminate\Http\Request;

class LevelController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = 'Levels';
        $subtitle = 'List of Levels';
        return view('levels.index', compact('title', 'subtitle'));
    }

    public function getLevels(Request $request)
    {
        $levels = Level::orderBy('level_order', 'asc')->orderBy('name', 'asc');

        return datatables()->of($levels)
            ->addIndexColumn()
            ->addColumn('name', function ($level) {
                return $level->name;
            })
            ->addColumn('level_order', function ($level) {
                return '<span class="badge badge-info">' . $level->level_order . '</span>';
            })
            ->addColumn('roster_config', function ($level) {
                if ($level->hasRosterConfig()) {
                    return '<span class="badge badge-info">' . $level->getRosterPattern() . '</span>';
                } else {
                    return '<span class="badge badge-secondary">No Roster</span>';
                }
            })
            ->addColumn('is_active', function ($level) {
                if ($level->is_active) {
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
            ->addColumn('action', 'levels.action')
            ->rawColumns(['is_active', 'level_order', 'roster_config', 'action'])
            ->toJson();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $title = 'Levels';
        $subtitle = 'Add Level';
        return view('levels.create', compact('title', 'subtitle'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:levels,name',
            'level_order' => 'required|integer|min:1|unique:levels,level_order',
            'off_days' => 'nullable|integer|min:0|max:30',
            'work_days' => 'nullable|integer|min:0|max:100',
            'cycle_length' => 'nullable|integer|min:0|max:150',
        ]);

        Level::create([
            'name' => $request->name,
            'level_order' => $request->level_order,
            'is_active' => $request->has('is_active'),
            'off_days' => $request->off_days,
            'work_days' => $request->work_days,
            'cycle_length' => $request->cycle_length,
        ]);

        return redirect()->route('levels.index')->with('toast_success', 'Level added successfully!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Level  $level
     * @return \Illuminate\Http\Response
     */
    public function show(Level $level)
    {
        // Logic to show a single level
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Level  $level
     * @return \Illuminate\Http\Response
     */
    public function edit(Level $level)
    {
        $title = 'Levels';
        $subtitle = 'Edit Level';
        return view('levels.edit', compact('title', 'subtitle', 'level'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Level  $level
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Level $level)
    {
        $request->validate([
            'name' => 'required|unique:levels,name,' . $level->id,
            'level_order' => 'required|integer|min:1|unique:levels,level_order,' . $level->id,
            'off_days' => 'nullable|integer|min:0|max:30',
            'work_days' => 'nullable|integer|min:0|max:100',
            'cycle_length' => 'nullable|integer|min:0|max:150',
        ]);

        $level->update([
            'name' => $request->name,
            'level_order' => $request->level_order,
            'is_active' => $request->has('is_active'),
            'off_days' => $request->off_days,
            'work_days' => $request->work_days,
            'cycle_length' => $request->cycle_length,
        ]);

        return redirect()->route('levels.index')->with('toast_success', 'Level updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Level  $level
     * @return \Illuminate\Http\Response
     */
    public function destroy(Level $level)
    {
        if ($level->administrations()->exists()) {
            return redirect()->route('levels.index')->with('toast_error', 'Level cannot be deleted as it is in use.');
        }
        $level->delete();
        return redirect()->route('levels.index')->with('toast_success', 'Level deleted successfully');
    }

    /**
     * Change the status of the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function changeStatus($id)
    {
        $level = Level::findOrFail($id);
        $level->update(['is_active' => !$level->is_active]);
        return back()->with('toast_success', 'Level status changed successfully.');
    }
}
