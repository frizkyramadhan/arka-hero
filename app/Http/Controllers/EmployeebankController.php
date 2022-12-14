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
    // public function employeebanks(Request $request)
    // {
    //     $keyword = $request->keyword;
    //     $employeebanks = Employeebank::with(['employees','banks'])
    //                                 ->where('bank_account_no', 'LIKE', '%'.$keyword.'%')
    //                                 ->orWhere('bank_account_name', 'LIKE', '%'.$keyword.'%')
    //                                 ->orWhereHas('employees', function($query) use($keyword){
    //                                     $query->where('fullname', 'LIKE', '%'.$keyword.'%');
    //                                 })                        
    //                                 ->paginate(5);
    //     // $employeebanks = DB::table('employeebanks')
    //     //     ->join('employees', 'employeebanks.employee_id', '=', 'employees.id')
    //     //     ->join('banks', 'employeebanks.bank_id', '=', 'banks.id')
    //     //     ->select('employeebanks.*', 'fullname', 'bank_name')
    //     //     ->orderBy('fullname', 'asc')
    //     //     ->simplePaginate(15);
    //     return view('employeebank.index', ['employeebanks' => $employeebanks]);
    // }
    // public function AddEmployeebank()
    // {
    //     $employee = Employee::orderBy('id', 'asc')->get();
    //     $banks = Bank::all();
    //     return view('employeebank.create', compact('employee', 'banks'));
    // }

    // public function store(Request $request)
    // {
    //     $validated = $request->validate([
    //         'employee_id' => 'required',
    //         'bank_id' => 'required',
    //         'bank_account_no' => 'required',
    //         'bank_account_name' => 'required',
    //         'bank_account_branch' => 'required',

    //     ]);
    //     $employeebanks = Employeebank::create($request->all());
    //     return redirect('admin/employeebanks')->with('status', 'Bank Employee Add Successfully');
    // }


    // public function editEmployeebank($slug)
    // {
    //     $employeebanks = Employeebank::where('slug', $slug)->first();
    //     $employee = Employee::orderBy('id', 'asc')->get();
    //     $banks = Bank::all();
    //     return view('employeebank.edit', compact('employeebanks','employee','banks'));
    // }


    // public function updateEmployeebank(Request $request, $slug)
    // {
    //     $employeebanks = Employeebank::where('slug', $slug)->first();
    //     $rules = [
    //         'employee_id' => 'required',
    //         'bank_id' => 'required',
    //         'bank_account_no' => 'required',
    //         'bank_account_name' => 'required',
    //         'bank_account_branch' => 'required',

    //     ];

    //     $validatedData = $request->validate($rules);
    //     Employeebank::where('slug', $slug)->update($validatedData);

    //     return redirect('admin/employeebanks')->with('status', 'Bank Employee Update Successfully');
    // }

    // public function deleteEmployeebank($slug)
    // {

    //     $employeebanks = Employeebank::where('slug', $slug)->first();
    //     $employeebanks->delete();
    //     return redirect('admin/employeebanks')->with('status', 'Bank Employee Delete Successfully');
    // }
}
