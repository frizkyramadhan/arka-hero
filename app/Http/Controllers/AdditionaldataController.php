<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use App\Models\Additionaldata;
use Illuminate\Support\Facades\DB;

class AdditionaldataController extends Controller
{

    public function index()
    {
        $title = ' Additional Data';
        $subtitle = 'Additional Data';
        $employees = Employee::orderBy('fullname', 'asc')->get();
        return view('additionaldata.index', compact('title', 'subtitle', 'employees'));
    }

    public function getAdditionaldata(Request $request)
    {
        $additionaldatas = Additionaldata::leftJoin('employees', 'additionaldatas.employee_id', '=', 'employees.id')
            ->select('additionaldatas.*', 'employees.fullname')
            ->orderBy('additionaldatas.cloth_size', 'asc');

        return datatables()->of($additionaldatas)
            ->addIndexColumn()
            ->addColumn('fullname', function ($additionaldatas) {
                return $additionaldatas->fullname;
            })
            ->addColumn('cloth_size', function ($additionaldatas) {
                return $additionaldatas->cloth_size;
            })
            ->addColumn('pants_size', function ($additionaldatas) {
                return $additionaldatas->pants_size;
            })
            ->addColumn('shoes_size', function ($additionaldatas) {
                return $additionaldatas->shoes_size;
            })
            ->addColumn('height', function ($additionaldatas) {
                return $additionaldatas->height;
            })
            ->addColumn('weight', function ($additionaldatas) {
                return $additionaldatas->weight;
            })
            ->addColumn('glasses', function ($additionaldatas) {
                return $additionaldatas->glasses;
            })
            ->filter(function ($instance) use ($request) {
                if (!empty($request->get('search'))) {
                    $instance->where(function ($w) use ($request) {
                        $search = $request->get('search');
                        $w->orWhere('fullname', 'LIKE', "%$search%")
                            ->orWhere('pants_size', 'LIKE', "%$search%")
                            ->orWhere('shoes_size', 'LIKE', "%$search%")
                            ->orWhere('height', 'LIKE', "%$search%")
                            ->orWhere('weight', 'LIKE', "%$search%")
                            ->orWhere('glasses', 'LIKE', "%$search%");
                    });
                }
            })
            ->addColumn('action', function ($additionaldatas) {
                $employees = Employee::orderBy('fullname', 'asc')->get();
                return view('additionaldata.action', compact('employees', 'additionaldatas'));
            })
            ->rawColumns(['pants_size', 'action'])
            // ->addColumn('action', 'license.action')
            ->toJson();
    }

    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required',
            'cloth_size' => 'required',
            'pants_size' => 'required',
            'shoes_size' => 'required',
            'height' => 'required',
            'weight' => 'required',
            'glasses' => 'required',

        ]);
        Additionaldata::create($request->all());
        return redirect('employees/' . $request->employee_id . '#additional')->with('toast_success', 'Additional Data Added Successfully');
    }

    public function update(Request $request, $id)
    {
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
        Additionaldata::where('id', $id)->update($validatedData);

        return redirect('employees/' . $request->employee_id . '#additional')->with('toast_success', 'Additional Data Employee Update Successfully');
    }

    public function delete($employee_id, $id)
    {
        Additionaldata::where('id', $id)->delete();
        return redirect('employees/' . $employee_id . '#additional')->with('toast_success', 'Additional Data Employee Delete Successfully');
    }
}
