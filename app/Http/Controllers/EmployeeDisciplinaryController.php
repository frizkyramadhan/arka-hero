<?php

namespace App\Http\Controllers;

use App\Exports\EmployeeDisciplinaryExport;
use App\Imports\EmployeeDisciplinaryImport;
use App\Models\DisciplinaryCriterion;
use App\Models\Employee;
use App\Models\EmployeeDisciplinary;
use App\Services\DisciplinaryService;
use App\Support\UserProject;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException as ExcelValidationException;
use Yajra\DataTables\Facades\DataTables;

class EmployeeDisciplinaryController extends Controller
{
    public function __construct(protected DisciplinaryService $service)
    {
        $this->middleware('permission:employee-disciplinaries.show')->only([
            'index', 'show', 'getData', 'employeeStatus', 'export', 'template',
        ]);
        $this->middleware('permission:employee-disciplinaries.show|personal.disciplinary.view-own')->only([
            'download',
        ]);
        $this->middleware('permission:personal.disciplinary.view-own')->only([
            'myRecords', 'myRecordsData', 'myRecordsShow',
        ]);
        $this->middleware('permission:employee-disciplinaries.create')->only([
            'create', 'store', 'terminateForm', 'terminateAfterSp3', 'import',
        ]);
        $this->middleware('permission:employee-disciplinaries.edit')->only([
            'edit', 'update', 'uploadDocument',
        ]);
        $this->middleware('permission:employee-disciplinaries.delete')->only(['destroy']);
    }

    public function index()
    {
        $title = 'Disciplinary';
        $subtitle = 'List of Disciplinary Records';
        $employees = $this->employeesForSelect();
        $typeOptions = EmployeeDisciplinary::TYPE_LABELS;
        $criteria = $this->criteriaForSelect();

        return view('employee-disciplinaries.index', compact(
            'title',
            'subtitle',
            'employees',
            'typeOptions',
            'criteria'
        ));
    }

    public function getData(Request $request)
    {
        $this->service->expireDue();

        $query = $this->filteredQuery($request);

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('employee', function (EmployeeDisciplinary $row) {
                $employee = $row->employee;
                if (! $employee) {
                    return '-';
                }
                $ktp = $employee->identity_card ?: '-';
                $nik = optional($employee->administrations->firstWhere('is_active', 1) ?? $employee->administrations->first())->nik ?: '-';

                return '<strong>'.e($employee->fullname).'</strong>'
                    .'<br><small class="text-muted">ID Card: '.e($ktp).'</small>'
                    .'<br><small class="text-muted">NIK: '.e($nik).'</small>';
            })
            ->addColumn('type_label', fn (EmployeeDisciplinary $row) => display_text($row->type_label))
            ->addColumn('criteria_list', function (EmployeeDisciplinary $row) {
                if ($row->criteria->isEmpty()) {
                    return '<span class="text-muted">-</span>';
                }

                return $row->criteria->map(function ($c) {
                    return '<span class="badge badge-light border mr-1 mb-1">'.e($c->code).'</span>';
                })->implode(' ');
            })
            ->addColumn('effective_date_fmt', fn (EmployeeDisciplinary $row) => $row->effective_date->format('d/m/Y'))
            ->addColumn('end_date_fmt', fn (EmployeeDisciplinary $row) => $row->end_date->format('d/m/Y'))
            ->addColumn('status_badge', function (EmployeeDisciplinary $row) {
                $map = [
                    'active' => 'success',
                    'expired' => 'secondary',
                    'superseded' => 'warning',
                    'terminated' => 'danger',
                ];
                $class = $map[$row->status] ?? 'light';
                $badge = '<span class="badge badge-'.$class.'">'.e(ucfirst($row->status)).'</span>';
                if ($row->allowsDeferredDocument()) {
                    $badge .= '<br><small class="text-warning">Doc pending</small>';
                }

                return $badge;
            })
            ->addColumn('remaining_days', function (EmployeeDisciplinary $row) {
                if ($row->status !== EmployeeDisciplinary::STATUS_ACTIVE) {
                    return '-';
                }

                return '<strong>'.$row->remaining_days.'</strong> days';
            })
            ->addColumn('action', function (EmployeeDisciplinary $model) {
                return view('employee-disciplinaries.action', compact('model'))->render();
            })
            ->rawColumns(['employee', 'criteria_list', 'status_badge', 'remaining_days', 'action'])
            ->toJson();
    }

    public function export(Request $request)
    {
        $query = $this->filteredQuery($request);

        return Excel::download(
            new EmployeeDisciplinaryExport($query),
            'employee-disciplinaries-'.now()->format('Y-m-d').'.xlsx'
        );
    }

    public function template()
    {
        $empty = EmployeeDisciplinary::query()->whereRaw('1 = 0')->with(['employee.administrations', 'criteria']);

        return Excel::download(
            new EmployeeDisciplinaryExport($empty),
            'employee-disciplinaries-import-template.xlsx'
        );
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:xls,xlsx'],
        ], [
            'file.required' => 'Please select a file to import.',
            'file.mimes' => 'The file must be an Excel file (.xls or .xlsx).',
        ]);

        try {
            $import = new EmployeeDisciplinaryImport($this->service);
            Excel::import($import, $request->file('file'));

            $failures = $import->failures();
            if ($failures->isNotEmpty()) {
                $message = "Import finished with errors: {$import->created} created.";

                return back()
                    ->with('toast_warning', $message)
                    ->with('failures', $this->formatImportFailures($failures));
            }

            $message = "Import completed: {$import->created} created.";
            if ($import->skipped > 0) {
                $message .= " {$import->skipped} empty row(s) skipped.";
            }

            return redirect()->route('employee-disciplinaries.index')
                ->with('toast_success', $message);
        } catch (ExcelValidationException $e) {
            return back()->with('failures', $this->formatImportFailures($e->failures()));
        } catch (\Throwable $e) {
            return back()->with('toast_error', 'Import failed: '.$e->getMessage());
        }
    }

    public function uploadDocument(Request $request, EmployeeDisciplinary $employeeDisciplinary)
    {
        if (! $employeeDisciplinary->allowsDeferredDocument()) {
            return back()->with('toast_error', 'Document upload later is only available for imported records without a document.');
        }

        $request->validate([
            'document' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);

        try {
            $this->service->attachDocument($employeeDisciplinary, $request->file('document'));
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        return back()->with('toast_success', 'Supporting document uploaded successfully.');
    }

    public function myRecords()
    {
        $title = 'My Disciplinary Record';
        $subtitle = 'List of My Disciplinary Records';
        $typeOptions = EmployeeDisciplinary::TYPE_LABELS;
        $criteria = $this->criteriaForSelect();

        return view('employee-disciplinaries.my-index', compact(
            'title',
            'subtitle',
            'typeOptions',
            'criteria'
        ));
    }

    public function myRecordsData(Request $request)
    {
        $this->service->expireDue();

        $employeeId = auth()->user()->employee_id;
        if (! $employeeId) {
            return DataTables::of(collect())->toJson();
        }

        $query = EmployeeDisciplinary::query()
            ->with(['criteria'])
            ->where('employee_id', $employeeId)
            ->select('employee_disciplinaries.*');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('criterion_id')) {
            $criterionId = (int) $request->criterion_id;
            $query->whereHas('criteria', function ($cq) use ($criterionId) {
                $cq->where('disciplinary_criteria.id', $criterionId);
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('effective_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('effective_date', '<=', $request->date_to);
        }

        $query->orderByDesc('effective_date')->orderByDesc('id');

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('type_label', fn (EmployeeDisciplinary $row) => display_text($row->type_label))
            ->addColumn('criteria_list', function (EmployeeDisciplinary $row) {
                if ($row->criteria->isEmpty()) {
                    return '<span class="text-muted">-</span>';
                }

                return $row->criteria->map(function ($c) {
                    return '<span class="badge badge-light border mr-1 mb-1">'.e($c->code).'</span>';
                })->implode(' ');
            })
            ->addColumn('effective_date_fmt', fn (EmployeeDisciplinary $row) => $row->effective_date->format('d/m/Y'))
            ->addColumn('end_date_fmt', fn (EmployeeDisciplinary $row) => $row->end_date->format('d/m/Y'))
            ->addColumn('status_badge', function (EmployeeDisciplinary $row) {
                $map = [
                    'active' => 'success',
                    'expired' => 'secondary',
                    'superseded' => 'warning',
                    'terminated' => 'danger',
                ];
                $class = $map[$row->status] ?? 'light';

                return '<span class="badge badge-'.$class.'">'.e(ucfirst($row->status)).'</span>';
            })
            ->addColumn('remaining_days', function (EmployeeDisciplinary $row) {
                if ($row->status !== EmployeeDisciplinary::STATUS_ACTIVE) {
                    return '-';
                }

                return '<strong>'.$row->remaining_days.'</strong> days';
            })
            ->addColumn('action', function (EmployeeDisciplinary $row) {
                return '<a class="btn btn-icon btn-info" href="'.route('employee-disciplinaries.my-records.show', $row->id).'" title="View">'
                    .'<i class="fas fa-eye"></i></a>';
            })
            ->rawColumns(['criteria_list', 'status_badge', 'remaining_days', 'action'])
            ->toJson();
    }

    public function myRecordsShow(EmployeeDisciplinary $employeeDisciplinary)
    {
        $this->assertOwnsDisciplinary($employeeDisciplinary);

        $employeeDisciplinary->load([
            'employee.administrations' => function ($q) {
                $q->with(['position.department', 'project'])->orderByDesc('is_active');
            },
            'criteria',
            'creator',
        ]);

        $administration = $employeeDisciplinary->employee
            ? ($employeeDisciplinary->employee->administrations->firstWhere('is_active', 1)
                ?? $employeeDisciplinary->employee->administrations->first())
            : null;

        return view('employee-disciplinaries.show', [
            'title' => 'My Disciplinary Record',
            'subtitle' => 'Disciplinary Detail',
            'record' => $employeeDisciplinary,
            'administration' => $administration,
            'personalMode' => true,
        ]);
    }

    public function create(Request $request)
    {
        $title = 'Disciplinary';
        $subtitle = 'Add Disciplinary Record';
        $employees = $this->employeesForSelect();
        $typeOptions = EmployeeDisciplinary::TYPE_LABELS;
        $preselectEmployeeId = $request->get('employee_id');

        return view('employee-disciplinaries.create', compact(
            'title',
            'subtitle',
            'employees',
            'typeOptions',
            'preselectEmployeeId'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'type' => 'required|in:coaching,counseling,sp1,sp2,sp3',
            'effective_date' => 'required|date',
            'reason' => 'required|string|max:5000',
            'pp_notes' => 'nullable|string|max:5000',
            'criterion_ids' => 'nullable|array',
            'criterion_ids.*' => 'integer|exists:disciplinary_criteria,id',
            'document' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120',
        ]);

        $employee = Employee::findOrFail($data['employee_id']);
        if (! UserProject::canViewEmployee($employee)) {
            return UserProject::redirectAccessDenied();
        }

        if ($this->service->requiresTermination($employee)) {
            return redirect()
                ->route('employee-disciplinaries.terminate-form', $employee->id)
                ->with('toast_error', 'Employee has an active First & Final Warning. Proceed with termination.');
        }

        try {
            $this->service->create(
                array_merge($data, ['created_by' => auth()->id()]),
                $request->input('criterion_ids', []),
                $request->file('document')
            );
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        return redirect()->route('employee-disciplinaries.index')
            ->with('toast_success', 'Disciplinary record saved successfully');
    }

    public function show(EmployeeDisciplinary $employeeDisciplinary)
    {
        $employeeDisciplinary->load([
            'employee.administrations' => function ($q) {
                $q->with(['position.department', 'project'])->orderByDesc('is_active');
            },
            'criteria',
            'creator',
        ]);

        $administration = $employeeDisciplinary->employee
            ? ($employeeDisciplinary->employee->administrations->firstWhere('is_active', 1)
                ?? $employeeDisciplinary->employee->administrations->first())
            : null;

        $title = 'Disciplinary';
        $subtitle = 'Disciplinary Detail';

        return view('employee-disciplinaries.show', [
            'title' => $title,
            'subtitle' => $subtitle,
            'record' => $employeeDisciplinary,
            'administration' => $administration,
            'personalMode' => false,
        ]);
    }

    public function edit(EmployeeDisciplinary $employeeDisciplinary)
    {
        $employeeDisciplinary->load(['employee.administrations', 'criteria']);
        $title = 'Disciplinary';
        $subtitle = 'Edit Disciplinary Record';
        $employees = $this->employeesForSelect();
        $typeOptions = EmployeeDisciplinary::TYPE_LABELS;
        $statusSummary = $this->service->statusSummary($employeeDisciplinary->employee_id, $employeeDisciplinary);

        return view('employee-disciplinaries.edit', [
            'title' => $title,
            'subtitle' => $subtitle,
            'record' => $employeeDisciplinary,
            'employees' => $employees,
            'typeOptions' => $typeOptions,
            'statusSummary' => $statusSummary,
        ]);
    }

    public function update(Request $request, EmployeeDisciplinary $employeeDisciplinary)
    {
        $documentRequired = ! (
            ($employeeDisciplinary->document_path && ! $request->boolean('remove_document'))
            || $employeeDisciplinary->allowsDeferredDocument()
        );

        $data = $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'type' => 'required|in:coaching,counseling,sp1,sp2,sp3',
            'effective_date' => 'required|date',
            'reason' => 'required|string|max:5000',
            'pp_notes' => 'nullable|string|max:5000',
            'criterion_ids' => 'nullable|array',
            'criterion_ids.*' => 'integer|exists:disciplinary_criteria,id',
            'document' => [
                $documentRequired ? 'required' : 'nullable',
                'file',
                'mimes:pdf,doc,docx,jpg,jpeg,png',
                'max:5120',
            ],
            'remove_document' => 'nullable|boolean',
        ]);

        if ((string) $data['employee_id'] !== (string) $employeeDisciplinary->employee_id) {
            return redirect()->back()->with('toast_error', 'Employee cannot be changed.')->withInput();
        }

        // Imported records without a document must not lose deferred-upload eligibility via remove_document.
        if ($employeeDisciplinary->allowsDeferredDocument() && $request->boolean('remove_document') && ! $request->file('document')) {
            return redirect()->back()
                ->with('toast_error', 'Imported records without a document cannot clear a document they do not have.')
                ->withInput();
        }

        try {
            $this->service->update(
                $employeeDisciplinary,
                $data,
                $request->input('criterion_ids', []),
                $request->file('document'),
                $request->boolean('remove_document')
            );
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        return redirect()->route('employee-disciplinaries.index')
            ->with('toast_success', 'Disciplinary record updated successfully');
    }

    public function destroy(EmployeeDisciplinary $employeeDisciplinary)
    {
        if ($employeeDisciplinary->document_path) {
            Storage::disk('public')->delete($employeeDisciplinary->document_path);
        }
        $employeeDisciplinary->criteria()->detach();
        $employeeDisciplinary->delete();

        return redirect()->route('employee-disciplinaries.index')
            ->with('toast_success', 'Disciplinary record deleted successfully');
    }

    public function employeeStatus(Employee $employee)
    {
        if (! UserProject::canViewEmployee($employee)) {
            return response()->json(['message' => 'Access denied'], 403);
        }

        return response()->json($this->service->statusSummary($employee));
    }

    public function terminateForm(Employee $employee)
    {
        if (! UserProject::canViewEmployee($employee)) {
            return UserProject::redirectAccessDenied();
        }

        if (! $this->service->requiresTermination($employee)) {
            return redirect()
                ->route('employee-disciplinaries.create')
                ->with('toast_error', 'Employee does not require termination from First & Final Warning.');
        }

        $title = 'Disciplinary';
        $subtitle = 'Termination after First & Final Warning';
        $statusSummary = $this->service->statusSummary($employee);
        $administration = $employee->activeAdministration;

        return view('employee-disciplinaries.terminate', compact(
            'title',
            'subtitle',
            'employee',
            'statusSummary',
            'administration'
        ));
    }

    public function terminateAfterSp3(Request $request, Employee $employee)
    {
        if (! UserProject::canViewEmployee($employee)) {
            return UserProject::redirectAccessDenied();
        }

        $data = $request->validate([
            'termination_date' => 'required|date',
            'termination_reason' => 'required|string|max:1000',
        ]);

        try {
            $this->service->handleRepeatAfterSp3(
                $employee,
                \Carbon\Carbon::parse($data['termination_date']),
                $data['termination_reason']
            );
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        return redirect()->route('employee-disciplinaries.index')
            ->with('toast_success', 'Employee termination processed after First & Final Warning.');
    }

    public function download(EmployeeDisciplinary $employeeDisciplinary)
    {
        $this->assertOwnsDisciplinaryOrHr($employeeDisciplinary);

        if (! $employeeDisciplinary->document_path || ! Storage::disk('public')->exists($employeeDisciplinary->document_path)) {
            return back()->with('toast_error', 'Document not found');
        }

        return Storage::disk('public')->download($employeeDisciplinary->document_path);
    }

    protected function filteredQuery(Request $request): Builder
    {
        $query = EmployeeDisciplinary::query()
            ->with(['employee.administrations', 'criteria'])
            ->select('employee_disciplinaries.*');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('employee_id')) {
            $query->where('employee_id', $request->employee_id);
        }

        if ($request->filled('criterion_id')) {
            $criterionId = (int) $request->criterion_id;
            $query->whereHas('criteria', function ($cq) use ($criterionId) {
                $cq->where('disciplinary_criteria.id', $criterionId);
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('effective_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('effective_date', '<=', $request->date_to);
        }

        // Ignore DataTables' built-in `search` array param; filtering uses custom fields only.

        return $query->orderByDesc('effective_date')->orderByDesc('id');
    }

    protected function criteriaForSelect()
    {
        return DisciplinaryCriterion::query()
            ->active()
            ->orderBy('sort_order')
            ->orderBy('code')
            ->get(['id', 'code', 'title', 'article_reference']);
    }

    protected function formatImportFailures(iterable $failures)
    {
        return collect($failures)->map(function ($failure) {
            $values = $failure->values();
            $attribute = $failure->attribute();
            $value = is_array($values) && array_key_exists($attribute, $values) ? $values[$attribute] : null;

            return [
                'sheet' => 'Disciplinary',
                'row' => $failure->row(),
                'attribute' => $attribute,
                'value' => $value,
                'errors' => implode(', ', $failure->errors()),
            ];
        });
    }

    protected function assertOwnsDisciplinary(EmployeeDisciplinary $record): void
    {
        $employeeId = auth()->user()->employee_id;
        if (! $employeeId || (int) $record->employee_id !== (int) $employeeId) {
            abort(403, 'You can only view your own disciplinary records.');
        }
    }

    protected function assertOwnsDisciplinaryOrHr(EmployeeDisciplinary $record): void
    {
        $user = auth()->user();
        if ($user->can('employee-disciplinaries.show')) {
            return;
        }

        $this->assertOwnsDisciplinary($record);
    }

    protected function employeesForSelect()
    {
        return UserProject::employeesForSelect(null, UserProject::EMPLOYEE_SELECT_ACTIVE_ADMINISTRATION, 'fullname')
            ->map(function ($employee) {
                $employee->display_label = trim(sprintf(
                    '%s | ID Card: %s | NIK: %s',
                    $employee->fullname,
                    $employee->identity_card ?: '-',
                    $employee->nik ?: '-'
                ));

                return $employee;
            });
    }
}
