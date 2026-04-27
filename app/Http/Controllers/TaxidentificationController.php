<?php

namespace App\Http\Controllers;

use App\Models\Taxidentification;
use App\Support\EmployeeSupportingDocumentStorage;
use App\Support\UserProject;
use Illuminate\Http\Request;

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
        $employees = UserProject::employeesForSelect(null, UserProject::EMPLOYEE_SELECT_ACTIVE_ADMINISTRATION);

        return view('taxidentification.index', compact('title', 'subtitle', 'employees'));
    }

    public function getTaxidentifications(Request $request)
    {
        $taxidentifications = Taxidentification::leftJoin('employees', 'taxidentifications.employee_id', '=', 'employees.id')
            ->select('taxidentifications.*', 'employees.fullname')
            ->orderBy('taxidentifications.tax_no', 'asc');
        UserProject::scopeQueryToEmployeesLinkedViaAdministrations($taxidentifications, 'taxidentifications.employee_id');

        return datatables()->of($taxidentifications)
            ->addIndexColumn()
            ->addColumn('fullname', function ($taxidentifications) {
                return $taxidentifications->fullname;
            })
            ->addColumn('tax_no', function ($taxidentifications) {
                return $taxidentifications->tax_no;
            })
            ->addColumn('tax_valid_date', function ($taxidentifications) {
                if (! $taxidentifications->tax_valid_date) {
                    return '-';
                }

                return $taxidentifications->tax_valid_date->format('d F Y');
            })
            // ->addColumn('position_status', function ($position) {
            //     if ($position->position_status == '1') {
            //         return '<span class="badge badge-success">Active</span>';
            //     } elseif ($position->position_status == '0') {
            //         return '<span class="badge badge-danger">Inactive</span>';
            //     }
            // })
            ->filter(function ($instance) use ($request) {
                if (! empty($request->get('search'))) {
                    $instance->where(function ($w) use ($request) {
                        $search = $request->get('search');
                        $w->orWhere('fullname', 'LIKE', "%$search%")
                            ->orWhere('tax_no', 'LIKE', "%$search%")
                            ->orWhere('tax_valid_date', 'LIKE', "%$search%");
                    });
                }
            })
            ->addColumn('action', function ($taxidentifications) {
                $employees = UserProject::employeesForSelect(null, UserProject::EMPLOYEE_SELECT_ACTIVE_ADMINISTRATION);

                return view('taxidentification.action', compact('employees', 'taxidentifications'));
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
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if ($r = UserProject::guardEmployeeId($request->employee_id)) {
            return $r;
        }

        $validated = $request->validate([
            'employee_id' => 'required',
            'tax_no' => 'required',
            'tax_valid_date' => 'nullable|date',
            'npwp_document' => EmployeeSupportingDocumentStorage::MIME_RULE,
        ]);

        $path = null;
        if ($request->hasFile('npwp_document')) {
            $path = EmployeeSupportingDocumentStorage::storeForEmployee(
                $request->file('npwp_document'),
                $validated['employee_id'],
                'npwp'
            );
        }

        unset($validated['npwp_document']);
        Taxidentification::create(array_merge($validated, ['npwp_document_path' => $path]));

        return redirect('employees/'.$request->employee_id.'#tax')->with('toast_success', 'Tax Identification Added Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function show(Taxidentification $taxidentification)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function edit(Taxidentification $taxidentification)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Taxidentification $taxidentification)
    {
        if ($r = UserProject::guardEmployeeId($taxidentification->employee_id)) {
            return $r;
        }

        $validated = $request->validate([
            'employee_id' => 'required',
            'tax_no' => 'required',
            'tax_valid_date' => 'nullable|date',
            'npwp_document' => EmployeeSupportingDocumentStorage::MIME_RULE,
        ]);
        if ($r = UserProject::guardEmployeeId($request->employee_id)) {
            return $r;
        }

        if ($request->hasFile('npwp_document')) {
            EmployeeSupportingDocumentStorage::deleteIfExists($taxidentification->npwp_document_path);
            $validated['npwp_document_path'] = EmployeeSupportingDocumentStorage::storeForEmployee(
                $request->file('npwp_document'),
                $validated['employee_id'],
                'npwp'
            );
        }

        unset($validated['npwp_document']);
        $taxidentification->update($validated);

        return redirect('employees/'.$request->employee_id.'#tax')->with('toast_success', 'Tax Identification Updated Successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Taxidentification  $taxidentification
     * @return \Illuminate\Http\Response
     */
    public function delete($employee_id, $id)
    {
        if ($r = UserProject::guardEmployeeId($employee_id)) {
            return $r;
        }
        $row = Taxidentification::where('id', $id)->firstOrFail();
        if ((int) $row->employee_id !== (int) $employee_id) {
            return UserProject::redirectAccessDenied();
        }
        EmployeeSupportingDocumentStorage::deleteIfExists($row->npwp_document_path);
        Taxidentification::where('id', $id)->delete();

        return redirect('employees/'.$employee_id.'#tax')->with('toast_success', 'Tax Identification Deleted Successfully');
    }

    public function downloadNpwp(Taxidentification $taxidentification)
    {
        if ($r = UserProject::guardEmployeeId($taxidentification->employee_id)) {
            return $r;
        }
        $response = EmployeeSupportingDocumentStorage::downloadResponse($taxidentification->npwp_document_path);

        return $response ?? redirect()->back()->with('toast_error', 'Dokumen NPWP tidak ditemukan.');
    }

    public function deleteNpwpDocument(Taxidentification $taxidentification)
    {
        if ($r = UserProject::guardEmployeeId($taxidentification->employee_id)) {
            return $r;
        }
        EmployeeSupportingDocumentStorage::deleteIfExists($taxidentification->npwp_document_path);
        $taxidentification->forceFill(['npwp_document_path' => null])->save();

        return redirect('employees/'.$taxidentification->employee_id.'#tax')->with('toast_success', 'Dokumen NPWP berhasil dihapus.');
    }
}
