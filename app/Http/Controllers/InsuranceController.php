<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Insurance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InsuranceController extends Controller
{
    public function insurances(Request $request)
    {   
        $keyword = $request->keyword;
        $insurances = Insurance::with('employees')
                                ->where('health_insurance_type', 'LIKE', '%'.$keyword.'%')
                                ->orWhere('health_insurance_no', 'LIKE', '%'.$keyword.'%')
                                ->orWhereHas('employees', function($query) use($keyword){
                                    $query->where('fullname', 'LIKE', '%'.$keyword.'%');
                                })                        
                                ->paginate(5);
        // $insurances = DB::table('insurances')
        //     ->join('employees', 'insurances.employee_id', '=', 'employees.id')
        //     ->select('insurances.*', 'fullname')
        //     ->orderBy('fullname', 'asc')
        //     ->simplePaginate(10);
        return view('insurance.index', ['insurances' => $insurances]);
       
    }

    public function addInsurance()
    {
        $employee = Employee::orderBy('id', 'asc')->get();
        return view('insurance.create', compact('employee'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required',
            'health_insurance_type' => 'required',
            'health_insurance_no' => 'required|unique:insurances|',
            'health_facility' => 'required',
            'health_insurance_remarks' => 'required',
        ]);

        $insurances = new Insurance();
        $insurances->employee_id = $request->employee_id;
        $insurances->health_insurance_type = $request->health_insurance_type;
        $insurances->health_insurance_no = $request->health_insurance_no;
        $insurances->health_facility = $request->health_facility;
        $insurances->health_insurance_remarks = $request->health_insurance_remarks;
        $insurances->save();

        return redirect('admin/insurances')->with('status', 'Insurance Employee Add Successfully');
    }

    public function editInsurance($slug)
    {
        $insurances = Insurance::where('slug', $slug)->first();
        $employee = Employee::orderBy('id', 'asc')->get();

        return view('insurance.edit', compact('insurances', 'employee'));
    }

    public function updateInsurance(Request $request, $slug)
    {
        $insurances = Insurance::where('slug', $slug)->first();
        $rules = [
            'employee_id' => 'required',
            'health_insurance_type' => 'required',
            'health_insurance_no' => 'required',
            'health_facility' => 'required',
            'health_insurance_remarks' => 'required',
        ];

        if ($request->health_insurance_no != $insurances->health_insurance_no) {
            $rules['health_insurance_no'] = 'required|unique:insurances';
        }

        $validatedData = $request->validate($rules);
        Insurance::where('slug', $slug)->update($validatedData);

        return redirect('admin/insurances')->with('status', 'Insurance Employee Update Successfully');
    }

    public function deleteInsurance($slug)
    {

        $insurances = Insurance::where('slug', $slug)->first();
        $insurances->delete();
        return redirect('admin/insurances')->with('status', 'Insurance Delete Successfully');
    }
}
