<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use App\Models\Additionaldata;
use Illuminate\Support\Facades\DB;

class AdditionaldataController extends Controller
{
    public function additionaldatas(Request $request)
    { 
        $keyword = $request->keyword;
        $additionaldatas = Additionaldata::with('employees')
                                        ->where('cloth_size', 'LIKE', '%'.$keyword.'%')
                                        ->orWhere('pants_size', 'LIKE', '%'.$keyword.'%')
                                        ->orWhere('shoes_size', 'LIKE', '%'.$keyword.'%')
                                        ->orWhereHas('employees', function($query) use($keyword){
                                            $query->where('fullname', 'LIKE', '%'.$keyword.'%');
                                        })                        
                                        ->paginate(5);

    // $additionaldatas = DB::table('additionaldatas')
    //         ->join('employees', 'additionaldatas.employee_id', '=', 'employees.id')
    //         ->select('additionaldatas.*', 'fullname')
    //         ->orderBy('fullname', 'asc')
    //         ->simplePaginate(10);
        return view('additionaldata.index', ['additionaldatas' => $additionaldatas]);
    }

    public function Addadditionaldata()
    {
        $employee = Employee::orderBy('id', 'asc')->get();
        return view('additionaldata.create', compact('employee'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required',
            'cloth_size' => 'required',
            'pants_size' => 'required',
            'shoes_size' => 'required',
            'height' => 'required',
            'weight' => 'required',
            'glasses' => 'required',

        ]);
        $additionaldatas = Additionaldata::create($request->all());
        return redirect('admin/additionaldatas')->with('status', 'Additional Data Employee Add Successfully');
    }

    public function editAdditionaldata($slug)
    {
        $additionaldatas = Additionaldata::where('slug', $slug)->first();
        $employee = Employee::orderBy('id', 'asc')->get();

        return view('additionaldata.edit', compact('additionaldatas', 'employee'));
    }

    public function updateAdditionaldata(Request $request, $slug)
    {
        $additionaldatas = Additionaldata::where('slug', $slug)->first();
        $rules = [
            'employee_id' => 'required',
            'cloth_size' => 'required',
            'pants_size' => 'required',
            'shoes_size' => 'required',
            'height' => 'required',
            'weight' => 'required',
            'glasses' => 'required',

        ];

        $validatedData = $request->validate($rules);
        Additionaldata::where('slug', $slug)->update($validatedData);

        return redirect('admin/additionaldatas')->with('status', 'Additional Data Employee Update Successfully');
    }

    public function deleteAdditionaldata($slug)
    {

        $additionaldatas = Additionaldata::where('slug', $slug)->first();
        $additionaldatas->delete();
        return redirect('admin/additionaldatas')->with('status', 'Additional Data Employee Delete Successfully');
    }
}
