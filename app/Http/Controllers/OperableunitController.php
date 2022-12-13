<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\Operableunit;
use Illuminate\Http\Request;

class OperableunitController extends Controller
{

    
    public function index()
    {
        $title = ' Operable Unit';
        $subtitle = ' Operable Unit';
        $employees = Employee::orderBy('fullname', 'asc')->get();
        return view('operableunit.index', compact('title', 'subtitle', 'employees'));
    }

    public function getOperableunits(Request $request)
    {
        $operableunits = Operableunit::leftJoin('employees', 'operableunits.employee_id', '=', 'employees.id')
            ->select('operableunits.*', 'employees.fullname')
            ->orderBy('operableunits.unit_name', 'asc');

        return datatables()->of($operableunits)
            ->addIndexColumn()
            ->addColumn('fullname', function ($operableunits) {
                return $operableunits->fullname;
            })
            ->addColumn('unit_name', function ($operableunits) {
                return $operableunits->unit_name;
            })
            ->addColumn('unit_type', function ($operableunits) {
                return $operableunits->unit_type;
            })
            ->addColumn('unit_remarks', function ($operableunits) {
                return $operableunits->unit_remarks;
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
                            ->orWhere('unit_name', 'LIKE', "%$search%")
                            ->orWhere('unit_type', 'LIKE', "%$search%")
                            ->orWhere('unit_remarks', 'LIKE', "%$search%");
                    });
                }
            })
            ->addColumn('action', function ($operableunits) {
                $employees = Employee::orderBy('fullname', 'asc')->get();
                return view('operableunit.action', compact('employees', 'operableunits'));
            })
            ->rawColumns(['unit_name', 'action'])
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
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Operableunit  $operableunit
     * @return \Illuminate\Http\Response
     */
    public function show(Operableunit $operableunit)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Operableunit  $operableunit
     * @return \Illuminate\Http\Response
     */
    public function edit(Operableunit $operableunit)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Operableunit  $operableunit
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Operableunit $operableunit)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Operableunit  $operableunit
     * @return \Illuminate\Http\Response
     */
    public function destroy(Operableunit $operableunit)
    {
        //
    }
}
