<?php

namespace App\Http\Controllers;

use App\Models\Bank;
use App\Models\Employee;
use App\Models\Employeebank;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmployeebankController extends Controller
{

    public function index()
    {
        $title = ' Employee Bank';
        $subtitle = ' Employee Bank';
        $employees = Employee::orderBy('fullname', 'asc')->get();
        return view('employeebank.index', compact('title', 'subtitle', 'employees'));
    }

    public function getEmployeebank(Request $request)
    {
        // $employeebanks = Employeebank::leftJoin('employees', 'employeebanks.employee_id', '=', 'employees.id')
        //     ->select('employeebanks.*', 'employees.fullname')
        //     ->orderBy('employeebanks.bank_account_no', 'asc');
        $employeebanks = DB::table('employeebanks')
            ->join('employees', 'employeebanks.employee_id', '=', 'employees.id')
            ->join('banks', 'employeebanks.bank_id', '=', 'banks.id')
            ->select('employeebanks.*', 'fullname', 'bank_name')
            ->orderBy('fullname', 'asc');
        return datatables()->of($employeebanks)
            ->addIndexColumn()
            ->addColumn('fullname', function ($employeebanks) {
                return $employeebanks->fullname;
            })
            ->addColumn('bank_name', function ($employeebanks) {
                return $employeebanks->bank_name;
            })
            ->addColumn('bank_account_no', function ($employeebanks) {
                return $employeebanks->bank_account_no;
            })
            ->addColumn('bank_account_name', function ($employeebanks) {
                return $employeebanks->bank_account_name;
            })
            ->addColumn('bank_account_branch', function ($employeebanks) {
                return $employeebanks->bank_account_branch;
            })
            // ->addColumn('position_status', function ($position) {
            //     if ($position->position_status == '1') {
            //         return '<span class="badge badge-success">Active</span>';
            //     } elseif ($position->position_status == '0') {
            //         return '<span class="badge badge-danger">Inactive</span>';
            //     }
            // })
            ->filter(function ($instance) use ($request) {
                if (!empty($request->get('search'))) {
                    $instance->where(function ($w) use ($request) {
                        $search = $request->get('search');
                        $w->orWhere('fullname', 'LIKE', "%$search%")
                            ->orWhere('bank_name', 'LIKE', "%$search%")
                            ->orWhere('bank_account_no', 'LIKE', "%$search%")
                            ->orWhere('bank_account_name', 'LIKE', "%$search%")
                            ->orWhere('bank_account_branch', 'LIKE', "%$search%");
                    });
                }
            })
            ->addColumn('action', function ($employeebanks) {
                $employees = Employee::orderBy('fullname', 'asc')->get();
                return view('employeebank.action', compact('employees', 'employeebanks'));
            })
            ->rawColumns(['bank_account_no', 'action'])
            ->toJson();
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required',
            'bank_id' => 'required',
            'bank_account_no' => 'required',
            'bank_account_name' => 'required',
            'bank_account_branch' => 'required',

        ]);
        Employeebank::create($request->all());
        return redirect('employees/' . $request->employee_id . '#bank')->with('toast_success', 'Bank Added Successfully');
    }


    // public function editEmployeebank($slug)
    // {
    //     $employeebanks = Employeebank::where('slug', $slug)->first();
    //     $employee = Employee::orderBy('id', 'asc')->get();
    //     $banks = Bank::all();
    //     return view('employeebank.edit', compact('employeebanks','employee','banks'));
    // }


    public function update(Request $request, $id)
    {
        $rules = $request->validate([
            'employee_id' => 'required',
            'bank_id' => 'required',
            'bank_account_no' => 'required',
            'bank_account_name' => 'required',
            'bank_account_branch' => 'required',
        ], [
            'employee_id.required' => 'Employee Name is required',
            'bank_id.required' => 'Bank Name is required',
            'bank_account_no.required' => 'Bank Account No is required',
            'bank_account_name.required' => 'Bank Account Name is required',
            'bank_account_branch.required' => 'Bank Account Branch is required',
        ]);

        Employeebank::where('id', $id)->update($rules);

        return redirect('employees/' . $request->employee_id . '#bank')->with('toast_success', 'Bank Account Update Successfully');
    }

    public function delete($employee_id, $id)
    {
        Employeebank::where('id', $id)->delete();
        return redirect('employees/' . $employee_id . '#bank')->with('toast_success', 'Bank Account Delete Successfully');
    }
}
