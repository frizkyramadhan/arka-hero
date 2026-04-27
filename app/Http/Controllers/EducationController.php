<?php

namespace App\Http\Controllers;

use App\Models\Education;
use App\Support\EmployeeSupportingDocumentStorage;
use App\Support\UserProject;
use Illuminate\Http\Request;

class EducationController extends Controller
{
    public function index()
    {
        $title = ' Employee Education';
        $subtitle = ' Employee Education';
        $employees = UserProject::employeesForSelect();

        return view('education.index', compact('title', 'subtitle', 'employees'));
    }

    public function getEducation(Request $request)
    {
        $educations = Education::leftJoin('employees', 'educations.employee_id', '=', 'employees.id')
            ->select('educations.*', 'employees.fullname')
            ->orderBy('fullname', 'asc');
        UserProject::scopeQueryToEmployeesLinkedViaAdministrations($educations, 'educations.employee_id');

        return datatables()->of($educations)
            ->addIndexColumn()
            ->addColumn('fullname', function ($educations) {
                return $educations->fullname;
            })
            ->addColumn('education_name', function ($educations) {
                return $educations->education_name;
            })
            ->addColumn('education_address', function ($educations) {
                return $educations->education_address;
            })
            ->addColumn('education_year', function ($educations) {
                return $educations->education_year;
            })
            ->addColumn('education_remarks', function ($educations) {
                return $educations->education_remarks;
            })
            ->filter(function ($instance) use ($request) {
                if (! empty($request->get('search'))) {
                    $instance->where(function ($w) use ($request) {
                        $search = $request->get('search');
                        $w->orWhere('fullname', 'LIKE', "%$search%")
                            ->orWhere('education_address', 'LIKE', "%$search%")
                            ->orWhere('education_name', 'LIKE', "%$search%")
                            ->orWhere('education_year', 'LIKE', "%$search%")
                            ->orWhere('education_remarks', 'LIKE', "%$search%");
                    });
                }
            })
            ->addColumn('action', function ($educations) {
                $employees = UserProject::employeesForSelect();

                return view('education.action', compact('employees', 'educations'));
            })
            ->rawColumns(['education_name', 'action'])
            // ->addColumn('action', 'education.action')
            ->toJson();
    }

    public function store($employee_id, Request $request)
    {
        if ($r = UserProject::guardEmployeeId($request->employee_id)) {
            return $r;
        }

        $validated = $request->validate([
            'employee_id' => 'required',
            'education_name' => 'required',
            'education_address' => 'required',
            'education_year' => 'nullable|string|max:255',
            'education_remarks' => 'nullable|string|max:255',
            'supporting_document' => EmployeeSupportingDocumentStorage::MIME_RULE,
        ]);

        $path = null;
        if ($request->hasFile('supporting_document')) {
            $path = EmployeeSupportingDocumentStorage::storeForEmployee(
                $request->file('supporting_document'),
                $validated['employee_id'],
                'education'
            );
        }

        unset($validated['supporting_document']);
        Education::create(array_merge($validated, ['diploma_document_path' => $path]));

        return redirect('employees/'.$employee_id.'#education')->with('toast_success', 'Education Employee Add Successfully');
    }

    public function update(Request $request, $id)
    {
        $row = Education::findOrFail($id);
        if ($r = UserProject::guardEmployeeId($row->employee_id)) {
            return $r;
        }

        $rules = [
            'employee_id' => 'required',
            'education_name' => 'required',
            'education_address' => 'required',
            'education_year' => 'nullable|string|max:255',
            'education_remarks' => 'nullable|string|max:255',
            'supporting_document' => EmployeeSupportingDocumentStorage::MIME_RULE,
        ];
        $validatedData = $request->validate($rules);
        if ($r = UserProject::guardEmployeeId($request->employee_id)) {
            return $r;
        }

        if ($request->hasFile('supporting_document')) {
            EmployeeSupportingDocumentStorage::deleteIfExists($row->diploma_document_path);
            $validatedData['diploma_document_path'] = EmployeeSupportingDocumentStorage::storeForEmployee(
                $request->file('supporting_document'),
                $validatedData['employee_id'],
                'education'
            );
        }

        unset($validatedData['supporting_document']);
        Education::where('id', $id)->update($validatedData);

        return redirect('employees/'.$request->employee_id.'#education')->with('toast_success', 'Education Employee Update Successfully');
    }

    public function delete($employee_id, $id)
    {
        if ($r = UserProject::guardEmployeeId($employee_id)) {
            return $r;
        }
        $educations = Education::where('id', $id)->firstOrFail();
        if ((int) $educations->employee_id !== (int) $employee_id) {
            return UserProject::redirectAccessDenied();
        }
        EmployeeSupportingDocumentStorage::deleteIfExists($educations->diploma_document_path);
        $educations->delete();

        return redirect('employees/'.$employee_id.'#education')->with('toast_success', 'Education Employee Delete Successfully');
    }

    public function deleteAll($employee_id)
    {
        if ($r = UserProject::guardEmployeeId($employee_id)) {
            return $r;
        }

        foreach (Education::where('employee_id', $employee_id)->get() as $edu) {
            EmployeeSupportingDocumentStorage::deleteIfExists($edu->diploma_document_path);
        }
        Education::where('employee_id', $employee_id)->delete();

        return redirect('employees/'.$employee_id.'#education')->with('toast_success', 'Education Employee Delete Successfully');
    }

    public function downloadDiploma(Education $education)
    {
        if ($r = UserProject::guardEmployeeId($education->employee_id)) {
            return $r;
        }
        $response = EmployeeSupportingDocumentStorage::downloadResponse($education->diploma_document_path);

        return $response ?? redirect()->back()->with('toast_error', 'Dokumen ijazah tidak ditemukan.');
    }

    public function deleteDiplomaDocument(Education $education)
    {
        if ($r = UserProject::guardEmployeeId($education->employee_id)) {
            return $r;
        }
        EmployeeSupportingDocumentStorage::deleteIfExists($education->diploma_document_path);
        $education->forceFill(['diploma_document_path' => null])->save();

        return redirect('employees/'.$education->employee_id.'#education')->with('toast_success', 'Dokumen ijazah berhasil dihapus.');
    }
}
