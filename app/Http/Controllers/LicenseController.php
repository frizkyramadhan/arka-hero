<?php

namespace App\Http\Controllers;

use App\Models\License;
use App\Models\Employee;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LicenseController extends Controller
{
    public function licenses(Request $request)
    {   
        $keyword = $request->keyword;
        $licenses = License::with('employees')
                            ->where('driver_license_no', 'LIKE', '%'.$keyword.'%')
                            ->orWhere('driver_license_type', 'LIKE', '%'.$keyword.'%')
                            ->orWhereHas('employees', function($query) use($keyword){
                                $query->where('fullname', 'LIKE', '%'.$keyword.'%');
                            })                        
                            ->paginate(5);
                          
        // $licenses = DB::table('licenses')
        //     ->join('employees', 'licenses.employee_id', '=', 'employees.id')
        //     ->select('licenses.*', 'fullname')
        //     ->orderBy('fullname', 'asc')
        //     ->simplePaginate(10);
        return view('license.index', ['licenses' => $licenses]);
       
    }

    public function addLicense()
    {
        $employee = Employee::orderBy('id', 'asc')->get();
        return view('license.create', compact('employee'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required',
            'driver_license_no' => 'required|unique:licenses|',
            'driver_license_type' => 'required',
            'driver_license_exp' => 'required',
        ]);

        $licenses = new License();
        $licenses->employee_id = $request->employee_id;
        $licenses->driver_license_no = $request->driver_license_no;
        $licenses->driver_license_type = $request->driver_license_type;
        $licenses->driver_license_exp = $request->driver_license_exp;
        $licenses->save();

        return redirect('admin/licenses')->with('status', 'Drivers Licenses Add Successfully');
    }

    public function editLicense($slug)
    {
        $licenses = License::where('slug', $slug)->first();
        $employee = Employee::orderBy('id', 'asc')->get();

        return view('license.edit', compact('licenses', 'employee'));
    }

    public function updateLicense(Request $request, $slug)
    {


        $validated = $request->validate([
            'employee_id' => 'required',
            'driver_license_no' => 'required',
            'driver_license_type' => 'required',
            'driver_license_exp' => 'required',
            

        ]);

        $licenses = License::where('slug', $slug)->first();
        $licenses->slug = null;
        $licenses->update($request->all());
        return redirect('admin/licenses')->with('status', 'Drivers Licenses Edit Successfully');
    }

    public function deleteLicense($slug)
    {

        $licenses = License::where('slug', $slug)->first();
        $licenses->delete();
        return redirect('admin/licenses')->with('status', 'Drivers Licenses Delete Successfully');
    }


}
