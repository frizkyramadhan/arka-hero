<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\Request;
use App\Models\Taxidentification;

class TaxidentificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $title = ' Tax Identification';
        $subtitle = ' Tax Identification';
        $employees = Employee::orderBy('fullname', 'asc')->get();
        return view('taxidentification.index', compact('title', 'subtitle', 'employees'));
    }

    public function getTaxidentifications(Request $request)
    {
        $taxidentifications = Taxidentification::leftJoin('employees', 'taxidentifications.employee_id', '=', 'employees.id')
            ->select('taxidentifications.*', 'employees.fullname')
            ->orderBy('taxidentifications.tax_no', 'asc');

        return datatables()->of($taxidentifications)
            ->addIndexColumn()
            ->addColumn('fullname', function ($taxidentifications) {
                return $taxidentifications->fullname;
            })
            ->addColumn('tax_no', function ($taxidentifications) {
                return $taxidentifications->tax_no;
            })
            ->addColumn('tax_valid_date', function ($taxidentifications) {
                return $taxidentifications->tax_valid_date;
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
                            ->orWhere('tax_no', 'LIKE', "%$search%")
                            ->orWhere('tax_valid_date', 'LIKE', "%$search%");
                    });
                }
            })
            ->addColumn('action', function ($taxidentifications) {
                $employees = Employee::orderBy('fullname', 'asc')->get();
                return view('taxidentification.action', compact('employees', 'taxidentifications'));
            })
            ->addColumn('tax_valid_date', function ($taxidentifications) {
                $date = date("d F Y", strtotime($taxidentifications->tax_valid_date));
                return $date;
            })
            ->rawColumns(['fullname', 'action'])
            ->toJson();
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'employee_id' => 'required',
            'tax_no' => 'required',
            'tax_valid_date' => 'required'

        ]);
        Taxidentification::create($request->all());
        return redirect('employees/' . $request->employee_id . '#tax')->with('toast_success', 'Tax Identification Added Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Taxidentification  $taxidentification
     * @return \Illuminate\Http\Response
     */
    public function show(Taxidentification $taxidentification)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Taxidentification  $taxidentification
     * @return \Illuminate\Http\Response
     */
    public function edit(Taxidentification $taxidentification)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Taxidentification  $taxidentification
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Taxidentification $taxidentification)
    {
        $request->validate([
            'employee_id' => 'required',
            'tax_no' => 'required',
            'tax_valid_date' => 'required'

        ]);
        $taxidentification->update($request->all());
        return redirect('employees/' . $request->employee_id . '#tax')->with('toast_success', 'Tax Identification Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Taxidentification  $taxidentification
     * @return \Illuminate\Http\Response
     */
    public function delete($employee_id, $id)
    {
        Taxidentification::where('id', $id)->delete();
        return redirect('employees/' . $employee_id . '#tax')->with('toast_success', 'Tax Identification Deleted Successfully');
    }
}
