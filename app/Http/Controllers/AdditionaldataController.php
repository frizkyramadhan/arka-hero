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
            // ->addColumn('additionaldatas_status', function ($additionaldatas) {
            //     if ($additionaldatas->additionaldatas_status == '1') {
            //         return '<span class="badge badge-success">Active</span>';
            //     } elseif ($additionaldatas->additionaldatas_status == '0') {
            //         return '<span class="badge badge-danger">Inactive</span>';
            //     }
            // })
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
    // public function additionaldatas(Request $request)
    // { 
    //     $keyword = $request->keyword;
    //     $additionaldatas = Additionaldata::with('employees')
    //                                     ->where('cloth_size', 'LIKE', '%'.$keyword.'%')
    //                                     ->orWhere('pants_size', 'LIKE', '%'.$keyword.'%')
    //                                     ->orWhere('shoes_size', 'LIKE', '%'.$keyword.'%')
    //                                     ->orWhereHas('employees', function($query) use($keyword){
    //                                         $query->where('fullname', 'LIKE', '%'.$keyword.'%');
    //                                     })                        
    //                                     ->paginate(5);

    // // $additionaldatas = DB::table('additionaldatas')
    // //         ->join('employees', 'additionaldatas.employee_id', '=', 'employees.id')
    // //         ->select('additionaldatas.*', 'fullname')
    // //         ->orderBy('fullname', 'asc')
    // //         ->simplePaginate(10);
    //     return view('additionaldata.index', ['additionaldatas' => $additionaldatas]);
    // }

    // public function Addadditionaldata()
    // {
    //     $employee = Employee::orderBy('id', 'asc')->get();
    //     return view('additionaldata.create', compact('employee'));
    // }

    // public function store(Request $request)
    // {
    //     $validated = $request->validate([
    //         'employee_id' => 'required',
    //         'cloth_size' => 'required',
    //         'pants_size' => 'required',
    //         'shoes_size' => 'required',
    //         'height' => 'required',
    //         'weight' => 'required',
    //         'glasses' => 'required',

    //     ]);
    //     $additionaldatas = Additionaldata::create($request->all());
    //     return redirect('admin/additionaldatas')->with('status', 'Additional Data Employee Add Successfully');
    // }

    // public function editAdditionaldata($slug)
    // {
    //     $additionaldatas = Additionaldata::where('slug', $slug)->first();
    //     $employee = Employee::orderBy('id', 'asc')->get();

    //     return view('additionaldata.edit', compact('additionaldatas', 'employee'));
    // }

    public function update(Request $request, $id)
    {
        // $additionaldatas = Additionaldata::where('id', $id)->first();
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

        return redirect('employees/' . $request->employee_id)->with('toast_success', 'Additional Data Employee Update Successfully');
    }

    // public function deleteAdditionaldata($slug)
    // {

    //     $additionaldatas = Additionaldata::where('slug', $slug)->first();
    //     $additionaldatas->delete();
    //     return redirect('admin/additionaldatas')->with('status', 'Additional Data Employee Delete Successfully');
    // }
}
