<?php

namespace App\Http\Controllers;

use App\Models\Familie;
use App\Models\Employee;
use App\Models\Family;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FamilieController extends Controller
{

    public function index()
    {
        $title = ' Employee Family';
        $subtitle = ' Employee Family';
        $employees = Employee::orderBy('fullname', 'asc')->get();
        return view('familie.index', compact('title', 'subtitle', 'employees'));
    }

    public function getFamilies(Request $request)
    {
        $families = Family::leftJoin('employees', 'families.employee_id', '=', 'employees.id')
            ->select('families.*', 'employees.fullname')
            ->orderBy('families.family_birthdate', 'asc');
        // $families = Familie::with('employees');
        return datatables()->of($families)
            ->addIndexColumn()
            ->addColumn('fullname', function ($families) {
                return $families->fullname;
            })
            ->addColumn('family_name', function ($families) {
                return $families->family_name;
            })
            ->addColumn('family_relationship', function ($families) {
                return $families->family_relationship;
            })
            ->addColumn('family_birthplace', function ($families) {
                return $families->family_birthplace;
            })
            ->addColumn('family_birthdate', function ($families) {
                return $families->family_birthdate;
            })
            ->addColumn('family_remarks', function ($families) {
                return $families->family_remarks;
            })
            // ->addColumn('families_status', function ($families) {
            //     if ($families->families_status == '1') {
            //         return '<span class="badge badge-success">Active</span>';
            //     } elseif ($families->families_status == '0') {
            //         return '<span class="badge badge-danger">Inactive</span>';
            //     }
            // })
            ->filter(function ($instance) use ($request) {
                if (!empty($request->get('search'))) {
                    $instance->where(function ($w) use ($request) {
                        $search = $request->get('search');
                        $w->orWhere('fullname', 'LIKE', "%$search%")
                            ->orWhere('family_name', 'LIKE', "%$search%")
                            ->orWhere('family_relationship', 'LIKE', "%$search%")
                            ->orWhere('family_birthplace', 'LIKE', "%$search%")
                            ->orWhere('family_birthdate', 'LIKE', "%$search%")
                            ->orWhere('family_remarks', 'LIKE', "%$search%");
                    });
                }
            })
            ->addColumn('action', function ($families) {
                $employees = Employee::orderBy('fullname', 'asc')->get();
                return view('familie.action', compact('employees', 'families'));
            })
            ->rawColumns(['family_name', 'action'])
            // ->addColumn('action', 'familie.action')
            ->toJson();
    }
    // public function families(Request $request)
    // {    
    //     $keyword = $request->keyword;
    //     $families = Familie::with('employees')
    //                         ->where('family_name', 'LIKE', '%'.$keyword.'%')
    //                         ->orWhere('family_relationship', 'LIKE', '%'.$keyword.'%')
    //                         ->orWhereHas('employees', function($query) use($keyword){
    //                             $query->where('fullname', 'LIKE', '%'.$keyword.'%');
    //                         })                        
    //                         ->paginate(5);
    //     // $families = DB::table('families')
    //     //     ->join('employees', 'families.employee_id', '=', 'employees.id')
    //     //     ->select('families.*', 'fullname')
    //     //     ->orderBy('fullname', 'asc')
    //     //     ->simplePaginate(10);
    //     return view('familie.index', ['families' => $families]);
       
    // }

    // public function addFamilie()
    // {
    //     $employee = Employee::orderBy('id', 'asc')->get();
    //     return view('familie.create', compact('employee'));
    // }
    // public function families(Request $request)
    // {
    //     $title = 'Families';
    //     $keyword = $request->keyword;
    //     $families = Familie::with('employees')
    //         ->where('family_name', 'LIKE', '%' . $keyword . '%')
    //         ->orWhere('family_relationship', 'LIKE', '%' . $keyword . '%')
    //         ->orWhereHas('employees', function ($query) use ($keyword) {
    //             $query->where('fullname', 'LIKE', '%' . $keyword . '%');
    //         })
    //         ->paginate(5);
    //     // $families = DB::table('families')
    //     //     ->join('employees', 'families.employee_id', '=', 'employees.id')
    //     //     ->select('families.*', 'fullname')
    //     //     ->orderBy('fullname', 'asc')
    //     //     ->simplePaginate(10);
    //     return view('familie.index', ['families' => $families], compact('title'));
    // }

    public function addFamilie()
    {
        $title = 'Add Family';
        $employee = Employee::orderBy('id', 'asc')->get();
        return view('familie.create', compact('employee', 'title'));
    }


    // public function store(Request $request)
    // {


   


    //     $request->validate([
    //         'employee_id' => 'required',
    //         'family_name' => 'required',
    //         'family_relationship' => 'required',
    //         'family_birthplace' => 'required',
    //         'family_birthdate' => 'required',
    //         'family_remarks' => 'required',
    //     ]);


    //     $families = new Familie();
    //     $families->employee_id = $request->employee_id;
    //     $families->family_name = $request->family_name;
    //     $families->family_relationship = $request->family_relationship;
    //     $families->family_birthplace = $request->family_birthplace;
    //     $families->family_birthdate = $request->family_birthdate;
    //     $families->family_remarks = $request->family_remarks;
    //     $families->save();

    //     return redirect('admin/families')->with('status', 'Family Employee Add Successfully');
    // }

    // public function editFamilie($slug)
    // {
    //     $families = Familie::where('slug', $slug)->first();
    //     $employee = Employee::orderBy('id', 'asc')->get();

    //     return view('familie.edit', compact('families', 'employee'));
    // }

    // public function updateFamilie(Request $request, $slug)
    // {
    //     $families = Familie::where('slug', $slug)->first();
    //     $rules = [
    //         'employee_id' => 'required',
    //         'family_name' => 'required',
    //         'family_relationship' => 'required',
    //         'family_birthplace' => 'required',
    //         'family_birthdate' => 'required',
    //         'family_remarks' => 'required',
    //     ];
    //     $validatedData = $request->validate($rules);
    //     Familie::where('slug', $slug)->update($validatedData);

    //     return redirect('admin/families')->with('status', 'Family Employee Update Successfully');
    // }


    
    // public function deleteFamilie($slug)
    // {

    public function deleteFamilie($slug)
    {


    //     $families = Familie::where('slug', $slug)->first();
    //     $families->delete();
    //     return redirect('admin/families')->with('status', 'Family Employee Delete Successfully');
    // }
}

}
