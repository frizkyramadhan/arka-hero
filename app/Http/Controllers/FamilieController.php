<?php

namespace App\Http\Controllers;

use App\Models\Familie;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FamilieController extends Controller
{
    public function families(Request $request)
    {    
        $keyword = $request->keyword;
        $families = Familie::with('employees')
                            ->where('family_name', 'LIKE', '%'.$keyword.'%')
                            ->orWhere('family_relationship', 'LIKE', '%'.$keyword.'%')
                            ->orWhereHas('employees', function($query) use($keyword){
                                $query->where('fullname', 'LIKE', '%'.$keyword.'%');
                            })                        
                            ->paginate(5);
        // $families = DB::table('families')
        //     ->join('employees', 'families.employee_id', '=', 'employees.id')
        //     ->select('families.*', 'fullname')
        //     ->orderBy('fullname', 'asc')
        //     ->simplePaginate(10);
        return view('familie.index', ['families' => $families]);
       
    }

    public function addFamilie()
    {
        $employee = Employee::orderBy('id', 'asc')->get();
        return view('familie.create', compact('employee'));
    }

    public function store(Request $request)
    {

     
        $request->validate([
            'employee_id' => 'required',
            'family_name' => 'required',
            'family_relationship' => 'required',
            'family_birthplace' => 'required',
            'family_birthdate' => 'required',
            'family_remarks' => 'required',
        ]);

        $families = new Familie();
        $families->employee_id = $request->employee_id;
        $families->family_name = $request->family_name;
        $families->family_relationship = $request->family_relationship;
        $families->family_birthplace = $request->family_birthplace;
        $families->family_birthdate = $request->family_birthdate;
        $families->family_remarks = $request->family_remarks;
        $families->save();

        return redirect('admin/families')->with('status', 'Family Employee Add Successfully');
    }

    public function editFamilie($slug)
    {
        $families = Familie::where('slug', $slug)->first();
        $employee = Employee::orderBy('id', 'asc')->get();

        return view('familie.edit', compact('families', 'employee'));
    }

    public function updateFamilie(Request $request, $slug)
    {
        $families = Familie::where('slug', $slug)->first();
        $rules = [
            'employee_id' => 'required',
            'family_name' => 'required',
            'family_relationship' => 'required',
            'family_birthplace' => 'required',
            'family_birthdate' => 'required',
            'family_remarks' => 'required',
        ];
        $validatedData = $request->validate($rules);
        Familie::where('slug', $slug)->update($validatedData);

        return redirect('admin/families')->with('status', 'Family Employee Update Successfully');
    }

    
    public function deleteFamilie($slug)
    {

        $families = Familie::where('slug', $slug)->first();
        $families->delete();
        return redirect('admin/families')->with('status', 'Family Employee Delete Successfully');
    }
}
