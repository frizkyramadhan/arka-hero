<?php

namespace App\Http\Controllers;

use App\Models\Position;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PositionController extends Controller
{
    public function positions(Request $request)
    {

        $keyword = $request->keyword;

        $positions = Position::with('departments')
                                ->where('position_name', 'LIKE', '%'.$keyword.'%')
                                ->orWhereHas('departments', function($query) use($keyword){
                                    $query->where('position_name', 'LIKE', '%'.$keyword.'%');
                                })                        
                                ->paginate(5);

        return view('position.index', ['positions' => $positions]);


        // // $positions = DB::table('positions')
        // //     ->join('departments', 'positions.department_id', '=', 'departments.id')
        // //     ->select('positions.*', 'department_name')
        // //     ->orderBy('department_name', 'asc')
        // //     ->simplePaginate(5);
        
    }

    public function addPost()
    {
        $departments = Department::where('department_status', 1)->orderBy('department_name', 'asc')->get();
        return view('position.create', compact('departments'));
    }

    public function store(Request $request)
    {

       
        $request->validate([
            'position_name' => 'required',
            'department_id' => 'required',
        ]);

        $position = new Position();
        $position->position_name = $request->position_name;
        $position->department_id = $request->department_id;
        $position->save();

        return redirect('admin/positions')->with('status', 'Position Add Successfully');
    }

    public function editPost($slug)
    {
        $position = Position::where('slug', $slug)->first();
        $departments = Department::where('department_status', 1)->orderBy('department_name', 'asc')->get();

        return view('position.edit', compact('position', 'departments'));
    }

    public function updatePost(Request $request, $slug)
    {


        $validated = $request->validate([
            'position_name' => 'required',
            'department_id' => 'required',
            

        ]);

        $positions = Position::where('slug', $slug)->first();
        $positions->slug = null;
        $positions->update($request->all());
        return redirect('admin/positions')->with('status', 'Position Edit Successfully');
    }

    public function deletePost($slug)
    {

        $positions = Position::where('slug', $slug)->first();
        $positions->delete($slug);
        return redirect('admin/positions')->with('status', 'Position Delete Successfully');
    }
}
