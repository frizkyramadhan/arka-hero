<?php

namespace App\Http\Controllers;

use App\Models\Insurance;
use App\Support\EmployeeSupportingDocumentStorage;
use App\Support\UserProject;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class InsuranceController extends Controller
{
    public function index()
    {
        $title = ' Employee Insurance';
        $subtitle = ' Employee Insurance';
        $employees = UserProject::employeesForSelect();

        return view('insurance.index', compact('title', 'subtitle', 'employees'));
    }

    public function getInsurances(Request $request)
    {
        $insurances = Insurance::leftJoin('employees', 'insurances.employee_id', '=', 'employees.id')
            ->select('insurances.*', 'employees.fullname')
            ->orderBy('insurances.health_insurance_no', 'asc');
        UserProject::scopeQueryToEmployeesLinkedViaAdministrations($insurances, 'insurances.employee_id');

        return datatables()->of($insurances)
            ->addIndexColumn()
            ->addColumn('insurances_name', function ($insurances) {
                return $insurances->fullname;
            })
            ->addColumn('health_insurance_type', function ($insurances) {
                return $insurances->health_insurance_type;
            })
            ->addColumn('health_insurance_no', function ($insurances) {
                return $insurances->health_insurance_no;
            })
            ->addColumn('health_facility', function ($insurances) {
                return $insurances->health_facility;
            })
            ->addColumn('health_insurance_remarks', function ($insurances) {
                return $insurances->health_insurance_remarks;
            })
            ->filter(function ($instance) use ($request) {
                if (! empty($request->get('search'))) {
                    $instance->where(function ($w) use ($request) {
                        $search = $request->get('search');
                        $w->orWhere('fullname', 'LIKE', "%$search%")
                            ->orWhere('health_insurance_type', 'LIKE', "%$search%")
                            ->orWhere('health_insurance_no', 'LIKE', "%$search%")
                            ->orWhere('health_facility', 'LIKE', "%$search%");
                    });
                }
            })
            ->addColumn('action', function ($insurances) {
                $employees = UserProject::employeesForSelect();

                return view('insurance.action', compact('employees', 'insurances'));
            })
            ->rawColumns(['health_insurance_no', 'action'])
            ->toJson();
    }

    public function store($employee_id, Request $request)
    {
        if ($r = UserProject::guardEmployeeId($request->employee_id)) {
            return $r;
        }

        $request->validate([
            'employee_id' => 'required',
            'health_insurance_type' => 'required',
            'health_insurance_no' => 'required|unique:insurances,health_insurance_no',
            'health_facility' => 'nullable|string',
            'health_insurance_remarks' => 'nullable|string',
            'supporting_document' => EmployeeSupportingDocumentStorage::MIME_RULE,
        ]);

        $insurances = new Insurance;
        $insurances->employee_id = $request->employee_id;
        $insurances->health_insurance_type = $request->health_insurance_type;
        $insurances->health_insurance_no = $request->health_insurance_no;
        $insurances->health_facility = $request->health_facility;
        $insurances->health_insurance_remarks = $request->health_insurance_remarks;
        if ($request->hasFile('supporting_document')) {
            $insurances->document_path = EmployeeSupportingDocumentStorage::storeForEmployee(
                $request->file('supporting_document'),
                $request->employee_id,
                'insurance'
            );
        }
        $insurances->save();

        return redirect('employees/'.$employee_id.'#insurance')->with('status', 'Insurance Employee Add Successfully');
    }

    public function update(Request $request, $id)
    {
        $insurances = Insurance::where('id', $id)->firstOrFail();
        if ($r = UserProject::guardEmployeeId($insurances->employee_id)) {
            return $r;
        }

        $validatedData = $request->validate([
            'employee_id' => 'required',
            'health_insurance_type' => 'required',
            'health_insurance_no' => [
                'required',
                Rule::unique('insurances', 'health_insurance_no')->ignore($insurances->id),
            ],
            'health_facility' => 'nullable|string',
            'health_insurance_remarks' => 'nullable|string',
            'supporting_document' => EmployeeSupportingDocumentStorage::MIME_RULE,
        ]);
        if ($r = UserProject::guardEmployeeId($request->employee_id)) {
            return $r;
        }

        if ($request->hasFile('supporting_document')) {
            EmployeeSupportingDocumentStorage::deleteIfExists($insurances->document_path);
            $validatedData['document_path'] = EmployeeSupportingDocumentStorage::storeForEmployee(
                $request->file('supporting_document'),
                $validatedData['employee_id'],
                'insurance'
            );
        }

        unset($validatedData['supporting_document']);
        Insurance::where('id', $id)->update($validatedData);

        return redirect('employees/'.$request->employee_id.'#insurance')->with('toast_success', 'Insurance Employee Update Successfully');
    }

    public function delete($employee_id, $id)
    {
        if ($r = UserProject::guardEmployeeId($employee_id)) {
            return $r;
        }
        $insurances = Insurance::where('id', $id)->firstOrFail();
        if ((int) $insurances->employee_id !== (int) $employee_id) {
            return UserProject::redirectAccessDenied();
        }
        EmployeeSupportingDocumentStorage::deleteIfExists($insurances->document_path);
        $insurances->delete();

        return redirect('employees/'.$employee_id.'#insurance')->with('toast_success', 'Insurance Delete Successfully');
    }

    public function deleteAll($employee_id)
    {
        if ($r = UserProject::guardEmployeeId($employee_id)) {
            return $r;
        }

        foreach (Insurance::where('employee_id', $employee_id)->get() as $row) {
            EmployeeSupportingDocumentStorage::deleteIfExists($row->document_path);
        }
        Insurance::where('employee_id', $employee_id)->delete();

        return redirect('employees/'.$employee_id.'#insurance')->with('toast_success', 'Insurance Delete Successfully');
    }

    public function downloadDocument(Insurance $insurance)
    {
        if ($r = UserProject::guardEmployeeId($insurance->employee_id)) {
            return $r;
        }
        $response = EmployeeSupportingDocumentStorage::downloadResponse($insurance->document_path);

        return $response ?? redirect()->back()->with('toast_error', 'Dokumen BPJS tidak ditemukan.');
    }

    public function deleteSupportingDocument(Insurance $insurance)
    {
        if ($r = UserProject::guardEmployeeId($insurance->employee_id)) {
            return $r;
        }
        EmployeeSupportingDocumentStorage::deleteIfExists($insurance->document_path);
        $insurance->forceFill(['document_path' => null])->save();

        return redirect('employees/'.$insurance->employee_id.'#insurance')->with('toast_success', 'Dokumen pendukung berhasil dihapus.');
    }
}
