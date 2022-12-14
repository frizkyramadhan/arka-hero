<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Employee;
use App\Models\Employeebank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeebankController extends Controller
{
    public function employeebanks(Request $request)
    {
        $keyword = $request->keyword;
        $employeebanks = Employeebank::with(['employees', 'banks'])
            ->where('bank_account_no', 'LIKE', '%' . $keyword . '%')
            ->orWhere('bank_account_name', 'LIKE', '%' . $keyword . '%')
            ->orWhereHas('employees', function ($query) use ($keyword) {
                $query->where('fullname', 'LIKE', '%' . $keyword . '%');
            })
            ->paginate(5);
        // $employeebanks = DB::table('employeebanks')
        //     ->join('employees', 'employeebanks.employee_id', '=', 'employees.id')
        //     ->join('banks', 'employeebanks.bank_id', '=', 'banks.id')
        //     ->select('employeebanks.*', 'fullname', 'bank_name')
        //     ->orderBy('fullname', 'asc')
        //     ->simplePaginate(15);
        return view('employeebank.index', ['employeebanks' => $employeebanks]);
    }
    public function AddEmployeebank()
    {
        $employee = Employee::orderBy('id', 'asc')->get();
        $banks = Bank::all();
        return view('employeebank.create', compact('employee', 'banks'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'employee_id' => 'required',
            'bank_id' => 'required',
            'bank_account_no' => 'required',
            'bank_account_name' => 'required',
            'bank_account_branch' => 'required',

        ]);
        $employeebanks = Employeebank::create($request->all());
        return redirect('admin/employeebanks')->with('status', 'Bank Employee Add Successfully');
    }

    public function editEmployeebank($slug)
    {
        $employeebanks = Employeebank::where('slug', $slug)->first();
        $employee = Employee::orderBy('id', 'asc')->get();
        $banks = Bank::all();
        return view('employeebank.edit', compact('employeebanks', 'employee', 'banks'));
    }

    public function updateEmployeebank(Request $request, $slug)
    {
        $employeebanks = Employeebank::where('slug', $slug)->first();
        $rules = [
            'employee_id' => 'required',
            'bank_id' => 'required',
            'bank_account_no' => 'required',
            'bank_account_name' => 'required',
            'bank_account_branch' => 'required',

        ];

        $validatedData = $request->validate($rules);
        Employeebank::where('slug', $slug)->update($validatedData);

        return redirect('admin/employeebanks')->with('status', 'Bank Employee Update Successfully');
    }

    public function deleteEmployeebank($slug)
    {

        $employeebanks = Employeebank::where('slug', $slug)->first();
        $employeebanks->delete();
        return redirect('admin/employeebanks')->with('status', 'Bank Employee Delete Successfully');
    }
}
