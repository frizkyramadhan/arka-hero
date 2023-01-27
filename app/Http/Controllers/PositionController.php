<?php

namespace App\Http\Controllers;

use App\Models\Position;
use App\Models\Department;
use Illuminate\Http\Request;
use App\Imports\PositionImport;
use Maatwebsite\Excel\Facades\Excel;

class PositionController extends Controller
{
    public function index()
    {
        $title = 'Positions';
        $subtitle = 'List of Position';
        $departments = Department::where('department_status', '1')->orderBy('department_name', 'asc')->get();
        return view('position.index', compact('title', 'subtitle', 'departments'));
    }

    public function getPositions(Request $request)
    {
        $position = Position::leftJoin('departments', 'positions.department_id', '=', 'departments.id')
            ->select('positions.*', 'departments.department_name')
            ->orderBy('positions.position_name', 'asc');

        return datatables()->of($position)
            ->addIndexColumn()
            ->addColumn('position_name', function ($position) {
                return $position->position_name;
            })
            ->addColumn('department_name', function ($position) {
                return $position->department_name;
            })
            ->addColumn('position_status', function ($position) {
                if ($position->position_status == '1') {
                    return '<span class="badge badge-success">Active</span>';
                } elseif ($position->position_status == '0') {
                    return '<span class="badge badge-danger">Inactive</span>';
                }
            })
            ->filter(function ($instance) use ($request) {
                if (!empty($request->get('search'))) {
                    $instance->where(function ($w) use ($request) {
                        $search = $request->get('search');
                        $w->orWhere('position_name', 'LIKE', "%$search%")
                            ->orWhere('department_name', 'LIKE', "%$search%")
                            ->orWhere('position_status', 'LIKE', "%$search%");
                    });
                }
            })
            ->addColumn('action', function ($position) {
                $departments = Department::where('department_status', '1')->orderBy('department_name', 'asc')->get();
                return view('position.action', compact('departments', 'position'));
            })
            ->rawColumns(['position_status', 'action'])
            ->toJson();
    }

    public function add()
    {
        return view('position.create');
    }

    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'position_name' => 'required',
            'department_id' => 'required',
            'position_status' => 'required',
        ], [
            'position_name.required' => 'Position Name is required',
            'department_id.required' => 'Department is required',
            'position_status.required' => 'Position Status is required',
        ]);

        Position::create($validatedData);

        return redirect('positions')->with('toast_success', 'Position added successfully!');
    }

    public function edit($slug)
    {
        $position = Position::where('slug', $slug)->first();
        return view('position.edit', compact('position'));
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'position_name' => 'required',
            'department_id' => 'required',
            'position_status' => 'required',
        ], [
            'position_name.required' => 'Position Name is required',
            'department_id.required' => 'Department is required',
            'position_status.required' => 'Position Status is required',
        ]);

        $position = Position::find($id);
        $position->position_name = $request->position_name;
        $position->department_id = $request->department_id;
        $position->position_status = $request->position_status;
        $position->save();

        return redirect('positions')->with('toast_success', 'Position edited successfully');
    }

    public function destroy($id)
    {
        $position = Position::where('id', $id)->first();
        $position->delete();
        return redirect('positions')->with('toast_success', 'Position delete successfully');
    }

    public function import(Request $request)
    {
        $this->validate($request, [
            'file' => 'required|mimes:xls,xlsx'
        ]);

        Excel::import(new PositionImport, request()->file('file'));

        return redirect('positions')->with('toast_success', 'Position imported successfully');
    }
}
