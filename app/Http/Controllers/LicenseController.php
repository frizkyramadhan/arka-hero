<?php

namespace App\Http\Controllers;

use App\Models\License;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LicenseController extends Controller
{

    public function index()
    {
        $title = 'Driver Licensee';
        $subtitle = 'Driver Licensee';
        $employees = Employee::orderBy('fullname', 'asc')->get();
        return view('license.index', compact('title', 'subtitle', 'employees'));
    }

    public function getLicenses(Request $request)
    {
        $license = License::leftJoin('employees', 'licenses.employee_id', '=', 'employees.id')
            ->select('licenses.*', 'employees.fullname')
            ->orderBy('licenses.driver_license_no', 'asc');

        return datatables()->of($license)
            ->addIndexColumn()
            ->addColumn('fullname', function ($license) {
                return $license->fullname;
            })
            ->addColumn('driver_license_no', function ($license) {
                return $license->driver_license_no;
            })
            ->addColumn('driver_license_type', function ($license) {
                return $license->driver_license_type;
            })
            ->addColumn('driver_license_exp', function ($license) {
                return $license->driver_license_exp;
            })

            ->filter(function ($instance) use ($request) {
                if (!empty($request->get('search'))) {
                    $instance->where(function ($w) use ($request) {
                        $search = $request->get('search');
                        $w->orWhere('driver_license_no', 'LIKE', "%$search%")
                            ->orWhere('driver_license_type', 'LIKE', "%$search%")
                            ->orWhere('driver_license_exp', 'LIKE', "%$search%")
                            ->orWhere('fullname', 'LIKE', "%$search%");
                    });
                }
            })
            ->addColumn('action', function ($license) {
                $employees = Employee::orderBy('fullname', 'asc')->get();
                return view('license.action', compact('employees', 'license'));
            })

            ->addColumn('driver_license_exp', function ($license) {
                $date = date("d F Y", strtotime($license->driver_license_exp));
                return $date;
            })
            ->rawColumns(['fullname', 'action'])
            ->toJson();
    }

    public function addLicense()
    {
        $employee = Employee::orderBy('id', 'asc')->get();
        return view('license.create', compact('employee'));
    }

    public function store($employee_id, Request $request)
    {
        $request->validate([
            'employee_id' => 'required',
            'driver_license_no' => 'required',
            'driver_license_type' => 'required',
            'driver_license_exp' => 'required',
        ]);

        $licenses = new License();
        $licenses->employee_id = $request->employee_id;
        $licenses->driver_license_no = $request->driver_license_no;
        $licenses->driver_license_type = $request->driver_license_type;
        $licenses->driver_license_exp = $request->driver_license_exp;
        $licenses->save();

        return redirect('employees/' . $employee_id . '#license')->with('toast_success', 'Driver License Added Successfully');
    }

    public function edit($id)
    {
        $licenses = License::where('id', $id)->first();
        $employee = Employee::orderBy('id', 'asc')->get();

        return view('license.edit', compact('licenses', 'employee'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'employee_id' => 'required',
            'driver_license_no' => 'required',
            'driver_license_type' => 'required',
            'driver_license_exp' => 'required',
        ]);

        $licenses = License::find($id);
        $licenses->employee_id = $request->employee_id;
        $licenses->driver_license_no = $request->driver_license_no;
        $licenses->driver_license_type = $request->driver_license_type;
        $licenses->driver_license_exp = $request->driver_license_exp;
        $licenses->save();

        return redirect('employees/' . $request->employee_id . '#license')->with('toast_success', 'Driver License Update Successfully');
    }

    public function delete($employee_id, $id)
    {
        $licenses = License::find($id);
        $licenses->delete();
        return redirect('employees/' . $employee_id . '#license')->with('toast_success', 'Driver License Delete Successfully');
    }

    public function deleteAll($employee_id)
    {
        License::where('employee_id', $employee_id)->delete();
        return redirect('employees/' . $employee_id . '#license')->with('toast_success', 'Driver License Delete Successfully');
    }
}
