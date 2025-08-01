<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Emrgcall;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EmrgcallController extends Controller
{

    public function index()
    {
        $title = ' Employee Emergency Call';
        $subtitle = 'Employee Emergency Call';
        $employees = Employee::orderBy('fullname', 'asc')->get();
        return view('emrgcall.index', compact('title', 'subtitle', 'employees'));
    }

    public function getEmrgcall(Request $request)
    {
        $emrgcalls = Emrgcall::leftJoin('employees', 'emrgcalls.employee_id', '=', 'employees.id')
            ->select('emrgcalls.*', 'employees.fullname')
            ->orderBy('emrgcalls.emrg_call_name', 'asc');

        return datatables()->of($emrgcalls)
            ->addIndexColumn()
            ->addColumn('fullname', function ($emrgcalls) {
                return $emrgcalls->fullname;
            })
            ->addColumn('emrg_call_name', function ($emrgcalls) {
                return $emrgcalls->emrg_call_name;
            })
            ->addColumn('emrg_call_relation', function ($emrgcalls) {
                return $emrgcalls->emrg_call_relation;
            })
            ->addColumn('emrg_call_phone', function ($emrgcalls) {
                return $emrgcalls->emrg_call_phone;
            })
            ->addColumn('emrg_call_address', function ($emrgcalls) {
                return $emrgcalls->emrg_call_address;
            })
            ->filter(function ($instance) use ($request) {
                if (!empty($request->get('search'))) {
                    $instance->where(function ($w) use ($request) {
                        $search = $request->get('search');
                        $w->orWhere('fullname', 'LIKE', "%$search%")
                            ->orWhere('emrg_call_name', 'LIKE', "%$search%")
                            ->orWhere('emrg_call_relation', 'LIKE', "%$search%")
                            ->orWhere('emrg_call_phone', 'LIKE', "%$search%")
                            ->orWhere('emrg_call_address', 'LIKE', "%$search%");
                    });
                }
            })
            ->addColumn('action', function ($emrgcalls) {
                $employees = Employee::orderBy('fullname', 'asc')->get();
                return view('emrgcall.action', compact('employees', 'emrgcalls'));
            })
            ->rawColumns(['emrg_call_name', 'action'])
            // ->addColumn('action', 'emrgcall.action')
            ->toJson();
    }

    public function store($employee_id, Request $request)
    {
        $request->validate([
            'employee_id' => 'required',
            'emrg_call_name' => 'required',
            'emrg_call_relation' => 'required',
            'emrg_call_phone' => 'required',
            'emrg_call_address' => 'required',

        ]);
        Emrgcall::create($request->all());
        return redirect('employees/' . $employee_id . '#emergency')->with('toast_success', 'Emergency Call Added Successfully');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'employee_id' => 'required',
            'emrg_call_name' => 'required',
            'emrg_call_relation' => 'required',
            'emrg_call_phone' => 'required',
            'emrg_call_address' => 'required',
        ]);

        $emrgcalls = Emrgcall::where('id', $id)->first();
        $emrgcalls->update($request->all());

        return redirect('employees/' . $request->employee_id . '#emergency')->with('toast_success', 'Emergency Call Updated Successfully');
    }

    public function delete($employee_id, $id)
    {
        $emrgcalls = Emrgcall::where('id', $id)->first();
        $emrgcalls->delete();
        return redirect('employees/' . $employee_id . '#emergency')->with('toast_success', 'Emergency Call Deleted Successfully');
    }

    public function deleteAll($employee_id)
    {
        Emrgcall::where('employee_id', $employee_id)->delete();
        return redirect('employees/' . $employee_id . '#emergency')->with('toast_success', 'Emergency Call Deleted Successfully');
    }
}
