<?php

namespace App\Http\Controllers;

use App\Models\Administration;
use App\Models\Level;
use App\Models\OvertimeRequest;
use App\Models\OvertimeRequestDetail;
use App\Models\Project;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class OvertimeRequestController extends Controller
{
    /**
     * @param  mixed  $input
     * @return array<int>
     */
    private function normalizeManualApprovers($input): array
    {
        if (! is_array($input)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map(static fn($id) => (int) $id, $input))));
    }

    public function __construct()
    {
        $this->middleware('permission:overtime-requests.show')->only(['index', 'data']);
        $this->middleware('permission:overtime-requests.create')->only(['create', 'store']);
        $this->middleware('permission:overtime-requests.edit')->only(['edit', 'update', 'submitForApproval']);
        $this->middleware('permission:overtime-requests.delete')->only(['destroy']);
        $this->middleware('permission:overtime-requests.finish')->only(['markFinished']);

        $this->middleware('permission:personal.overtime.view-own')->only(['myRequests', 'myRequestsData', 'myRequestShow']);
        $this->middleware('permission:personal.overtime.create-own')->only(['myRequestsCreate', 'myRequestsStore']);
        $this->middleware('permission:personal.overtime.edit-own')->only(['myRequestsEdit', 'myRequestsUpdate', 'myRequestsSubmitForApproval']);
        $this->middleware('permission:personal.overtime.cancel-own')->only(['myRequestsDestroy']);
    }

    /**
     * HR: daftar (DataTables).
     */
    public function index()
    {
        $projects = $this->activeProjects();

        return view('overtime-requests.index', compact('projects'))->with('title', 'Overtime Requests');
    }

    /**
     * HR: data server-side.
     */
    public function data(Request $request)
    {
        $query = OvertimeRequest::query()
            ->select('overtime_requests.*')
            ->with(['project', 'requestedBy', 'details.administration.employee'])
            ->orderByDesc('created_at');

        $this->applyOvertimeDatatableFilters($query, $request, true);

        return datatables()->of($query)
            ->addIndexColumn()
            ->addColumn('project_name', fn($row) => $row->project->project_name ?? '—')
            ->addColumn('overtime_date_fmt', fn($row) => $row->overtime_date?->format('d/m/Y') ?? '—')
            ->addColumn('status_badge', fn($row) => $this->statusBadgeHtml($row->status))
            ->addColumn('requester', fn($row) => $row->requestedBy->name ?? '—')
            ->addColumn('employees_html', fn($row) => $this->overtimeEmployeesListHtml($row))
            ->addColumn('remarks_html', fn($row) => $this->overtimeRemarksCellHtml($row->remarks))
            ->addColumn('actions', function ($row) {
                $html = '<div class="btn-group">';
                $html .= '<a href="' . route('overtime.requests.show', $row) . '" class="btn btn-sm btn-info mr-1" title="View"><i class="fas fa-eye"></i></a>';
                if ($row->canBeEditedBy(Auth::user())) {
                    $html .= '<a href="' . route('overtime.requests.edit', $row) . '" class="btn btn-sm btn-warning mr-1" title="Edit"><i class="fas fa-edit"></i></a>';
                }
                if (
                    $row->isEditable()
                    && in_array($row->status, [OvertimeRequest::STATUS_DRAFT, OvertimeRequest::STATUS_REJECTED], true)
                    && $row->canBeEditedBy(Auth::user())
                ) {
                    $html .= '<form method="POST" action="' . route('overtime.requests.submit-for-approval', $row) . '" class="d-inline mr-1" onsubmit="return confirm(\'Submit this request for approval?\');">'
                        . csrf_field()
                        . '<button type="submit" class="btn btn-sm btn-success" title="Submit for approval"><i class="fas fa-paper-plane"></i></button></form>';
                }
                if ($row->canBeDeletedBy(Auth::user())) {
                    $html .= '<form method="POST" action="' . route('overtime.requests.destroy', $row) . '" class="d-inline" onsubmit="return confirm(\'Delete this request?\');">'
                        . csrf_field() . method_field('DELETE')
                        . '<button type="submit" class="btn btn-sm btn-danger" title="Delete"><i class="fas fa-trash"></i></button></form>';
                }
                $html .= '</div>';

                return $html;
            })
            ->rawColumns(['status_badge', 'actions', 'employees_html', 'remarks_html'])
            ->make(true);
    }

    public function create()
    {
        $title = 'Overtime Requests';
        $subtitle = 'Add Overtime Request';
        $projects = $this->activeProjects();
        $overtimeRequest = null;
        $details = $this->detailsForCreateForm();
        $formAction = route('overtime.requests.store');
        $method = 'POST';
        $cancelRoute = route('overtime.requests.index');
        $isPersonal = false;

        return view('overtime-requests.create', compact(
            'title',
            'subtitle',
            'projects',
            'overtimeRequest',
            'details',
            'formAction',
            'method',
            'cancelRoute',
            'isPersonal'
        ));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $this->validateLineTimes($request);

        $user = Auth::user();
        $submit = ($data['submit_action'] ?? 'draft') === 'submit';
        $status = $submit
            ? OvertimeRequest::STATUS_PENDING
            : OvertimeRequest::STATUS_DRAFT;

        $manualApprovers = $this->normalizeManualApprovers($request->input('manual_approvers'));

        DB::beginTransaction();
        try {
            $ot = OvertimeRequest::create([
                'project_id' => $data['project_id'],
                'overtime_date' => $data['overtime_date'],
                'status' => $status,
                'requested_by' => $user->id,
                'requested_at' => $status === OvertimeRequest::STATUS_PENDING ? now() : null,
                'remarks' => $data['remarks'] ?? null,
                'manual_approvers' => $manualApprovers,
            ]);

            $this->syncDetails($ot, $request->input('details', []));

            if ($submit && ! empty($manualApprovers)) {
                $created = app(ApprovalPlanController::class)->create_manual_approval_plan('overtime_request', $ot->id);
                if (! $created || (int) $created === 0) {
                    DB::rollBack();

                    return back()
                        ->withInput()
                        ->with('toast_error', 'Failed to create approval plans. Select at least one approver.');
                }
            } elseif ($submit && empty($manualApprovers)) {
                DB::rollBack();

                return back()
                    ->withInput()
                    ->with('toast_error', 'Please select at least one approver before submitting.');
            }

            DB::commit();

            return redirect()
                ->route('overtime.requests.show', $ot)
                ->with('toast_success', $status === OvertimeRequest::STATUS_PENDING
                    ? 'Overtime request submitted.'
                    : 'Overtime draft saved.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('toast_error', 'Save failed: ' . $e->getMessage());
        }
    }

    public function edit(OvertimeRequest $overtimeRequest)
    {
        if (! $overtimeRequest->canBeEditedBy(Auth::user())) {
            abort(403);
        }

        $title = 'Overtime Requests';
        $subtitle = 'Edit Overtime Request';
        $projects = $this->activeProjects();
        $details = $this->detailsForEditForm($overtimeRequest);
        $formAction = route('overtime.requests.update', $overtimeRequest);
        $method = 'PUT';
        $cancelRoute = route('overtime.requests.index');
        $isPersonal = false;

        return view('overtime-requests.edit', compact(
            'title',
            'subtitle',
            'projects',
            'overtimeRequest',
            'details',
            'formAction',
            'method',
            'cancelRoute',
            'isPersonal'
        ));
    }

    public function update(Request $request, OvertimeRequest $overtimeRequest)
    {
        if (! $overtimeRequest->canBeEditedBy(Auth::user())) {
            abort(403);
        }

        $data = $this->validated($request);
        $this->validateLineTimes($request);

        $submit = ($data['submit_action'] ?? 'draft') === 'submit';
        $status = $submit
            ? OvertimeRequest::STATUS_PENDING
            : OvertimeRequest::STATUS_DRAFT;

        $manualApprovers = $this->normalizeManualApprovers($request->input('manual_approvers'));

        $requestedAt = $overtimeRequest->requested_at;
        if ($status === OvertimeRequest::STATUS_PENDING) {
            $requestedAt = $requestedAt ?? now();
        }

        DB::beginTransaction();
        try {
            $overtimeRequest->update([
                'project_id' => $data['project_id'],
                'overtime_date' => $data['overtime_date'],
                'status' => $status,
                'requested_at' => $requestedAt,
                'remarks' => $data['remarks'] ?? null,
                'manual_approvers' => $manualApprovers,
            ]);

            $this->syncDetails($overtimeRequest, $request->input('details', []));

            if ($submit && ! empty($manualApprovers)) {
                $created = app(ApprovalPlanController::class)->create_manual_approval_plan('overtime_request', $overtimeRequest->id);
                if (! $created || (int) $created === 0) {
                    DB::rollBack();

                    return back()
                        ->withInput()
                        ->with('toast_error', 'Failed to create approval plans. Select at least one approver.');
                }
            } elseif ($submit && empty($manualApprovers)) {
                DB::rollBack();

                return back()
                    ->withInput()
                    ->with('toast_error', 'Please select at least one approver before submitting.');
            }

            DB::commit();

            return redirect()
                ->route('overtime.requests.show', $overtimeRequest)
                ->with('toast_success', 'Overtime request updated.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('toast_error', 'Update failed: ' . $e->getMessage());
        }
    }

    public function destroy(OvertimeRequest $overtimeRequest)
    {
        if (! $overtimeRequest->canBeDeletedBy(Auth::user())) {
            abort(403);
        }

        DB::beginTransaction();
        try {
            $overtimeRequest->details()->delete();
            $overtimeRequest->delete();
            DB::commit();

            return redirect()
                ->route('overtime.requests.index')
                ->with('toast_success', 'Overtime request deleted.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('toast_error', 'Delete failed: ' . $e->getMessage());
        }
    }

    /**
     * User: daftar (DataTables).
     */
    public function myRequests()
    {
        $projects = $this->activeProjects();

        return view('overtime-requests.my-requests', compact('projects'))
            ->with('title', 'My Overtime Requests');
    }

    /**
     * User: detail pengajuan sendiri (halaman terpisah dari HR show).
     */
    public function myRequestShow(OvertimeRequest $overtimeRequest)
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            abort(403);
        }
        if (! $this->userCanViewPersonalOvertime($user, $overtimeRequest)) {
            abort(403);
        }

        $overtimeRequest->load([
            'project',
            'requestedBy',
            'finishedBy',
            'details.administration.employee',
            'details.administration.position',
            'approvalPlans.approver',
        ]);

        $title = 'My Overtime Request';

        return view('overtime-requests.my-show', compact('overtimeRequest', 'title'));
    }

    public function myRequestsData(Request $request)
    {
        $user = Auth::user();
        $adminId = $user->administration?->id;

        $query = OvertimeRequest::query()
            ->select('overtime_requests.*')
            ->where(function (Builder $q) use ($user, $adminId) {
                $q->where('requested_by', $user->id);
                if ($adminId) {
                    $q->orWhereHas('details', function (Builder $d) use ($adminId) {
                        $d->where('administration_id', $adminId);
                    });
                }
            })
            ->with(['project', 'details.administration.employee'])
            ->orderByDesc('created_at');

        $this->applyOvertimeDatatableFilters($query, $request, false);

        return datatables()->of($query)
            ->addIndexColumn()
            ->addColumn('project_name', fn($row) => $row->project->project_name ?? '—')
            ->addColumn('overtime_date_fmt', fn($row) => $row->overtime_date?->format('d/m/Y') ?? '—')
            ->addColumn('status_badge', fn($row) => $this->statusBadgeHtml($row->status))
            ->addColumn('employees_html', fn($row) => $this->overtimeEmployeesListHtml($row))
            ->addColumn('remarks_html', fn($row) => $this->overtimeRemarksCellHtml($row->remarks))
            ->addColumn('actions', function ($row) {
                $html = '<a href="' . route('overtime.my-requests.show', $row) . '" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>';
                if ($row->canBeEditedBy(Auth::user())) {
                    $html .= ' <a href="' . route('overtime.my-requests.edit', $row) . '" class="btn btn-sm btn-warning"><i class="fas fa-edit"></i></a>';
                }
                if (
                    $row->isEditable()
                    && in_array($row->status, [OvertimeRequest::STATUS_DRAFT, OvertimeRequest::STATUS_REJECTED], true)
                    && $row->canBeEditedBy(Auth::user())
                ) {
                    $html .= ' <form method="POST" action="' . route('overtime.my-requests.submit-for-approval', $row) . '" class="d-inline" onsubmit="return confirm(\'Submit this request for approval?\');">'
                        . csrf_field()
                        . '<button type="submit" class="btn btn-sm btn-success" title="Submit for approval"><i class="fas fa-paper-plane"></i></button></form>';
                }
                if ($row->canBeDeletedBy(Auth::user())) {
                    $html .= ' <form method="POST" action="' . route('overtime.my-requests.destroy', $row) . '" class="d-inline" onsubmit="return confirm(\'Delete this request?\');">'
                        . csrf_field() . method_field('DELETE')
                        . '<button type="submit" class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button></form>';
                }

                return $html;
            })
            ->rawColumns(['status_badge', 'actions', 'employees_html', 'remarks_html'])
            ->make(true);
    }

    public function myRequestsCreate()
    {
        if ($redirect = $this->ensurePersonalOvertimeLevel(false)) {
            return $redirect;
        }

        $title = 'My Overtime Requests';
        $subtitle = 'Add overtime request';
        $projects = $this->activeProjects();
        $overtimeRequest = null;
        $details = $this->detailsForCreateForm();
        $formAction = route('overtime.my-requests.store');
        $method = 'POST';
        $cancelRoute = route('overtime.my-requests');
        $isPersonal = true;

        return view('overtime-requests.my-create', compact(
            'title',
            'subtitle',
            'projects',
            'overtimeRequest',
            'details',
            'formAction',
            'method',
            'cancelRoute',
            'isPersonal'
        ));
    }

    public function myRequestsStore(Request $request)
    {
        if ($redirect = $this->ensurePersonalOvertimeLevel(true)) {
            return $redirect;
        }

        $data = $this->validated($request);
        $this->validateLineTimes($request);

        $user = Auth::user();
        $submit = ($data['submit_action'] ?? 'draft') === 'submit';
        $status = $submit
            ? OvertimeRequest::STATUS_PENDING
            : OvertimeRequest::STATUS_DRAFT;

        $manualApprovers = $this->normalizeManualApprovers($request->input('manual_approvers'));

        DB::beginTransaction();
        try {
            $ot = OvertimeRequest::create([
                'project_id' => $data['project_id'],
                'overtime_date' => $data['overtime_date'],
                'status' => $status,
                'requested_by' => $user->id,
                'requested_at' => $status === OvertimeRequest::STATUS_PENDING ? now() : null,
                'remarks' => $data['remarks'] ?? null,
                'manual_approvers' => $manualApprovers,
            ]);

            $this->syncDetails($ot, $request->input('details', []));

            if ($submit && ! empty($manualApprovers)) {
                $created = app(ApprovalPlanController::class)->create_manual_approval_plan('overtime_request', $ot->id);
                if (! $created || (int) $created === 0) {
                    DB::rollBack();

                    return back()
                        ->withInput()
                        ->with('toast_error', 'Failed to create approval plans. Select at least one approver.');
                }
            } elseif ($submit && empty($manualApprovers)) {
                DB::rollBack();

                return back()
                    ->withInput()
                    ->with('toast_error', 'Please select at least one approver before submitting.');
            }

            DB::commit();

            return redirect()
                ->route('overtime.my-requests.show', $ot)
                ->with('toast_success', $status === OvertimeRequest::STATUS_PENDING
                    ? 'Overtime request submitted.'
                    : 'Draft saved.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('toast_error', 'Save failed: ' . $e->getMessage());
        }
    }

    public function myRequestsEdit(OvertimeRequest $overtimeRequest)
    {
        if ($redirect = $this->ensurePersonalOvertimeLevel(false)) {
            return $redirect;
        }

        if ((int) $overtimeRequest->requested_by !== (int) Auth::id()) {
            abort(403);
        }

        if (! $overtimeRequest->canBeEditedBy(Auth::user())) {
            abort(403);
        }

        $title = 'My Overtime Requests';
        $subtitle = 'Edit overtime request #' . $overtimeRequest->id;
        $projects = $this->activeProjects();
        $details = $this->detailsForEditForm($overtimeRequest);
        $formAction = route('overtime.my-requests.update', $overtimeRequest);
        $method = 'PUT';
        $cancelRoute = route('overtime.my-requests.show', $overtimeRequest);
        $isPersonal = true;

        return view('overtime-requests.my-edit', compact(
            'title',
            'subtitle',
            'projects',
            'overtimeRequest',
            'details',
            'formAction',
            'method',
            'cancelRoute',
            'isPersonal'
        ));
    }

    public function myRequestsUpdate(Request $request, OvertimeRequest $overtimeRequest)
    {
        if ($redirect = $this->ensurePersonalOvertimeLevel(true)) {
            return $redirect;
        }

        if ((int) $overtimeRequest->requested_by !== (int) Auth::id()) {
            abort(403);
        }

        if (! $overtimeRequest->canBeEditedBy(Auth::user())) {
            abort(403);
        }

        $data = $this->validated($request);
        $this->validateLineTimes($request);

        $submit = ($data['submit_action'] ?? 'draft') === 'submit';
        $status = $submit
            ? OvertimeRequest::STATUS_PENDING
            : OvertimeRequest::STATUS_DRAFT;

        $manualApprovers = $this->normalizeManualApprovers($request->input('manual_approvers'));

        $requestedAt = $overtimeRequest->requested_at;
        if ($status === OvertimeRequest::STATUS_PENDING) {
            $requestedAt = $requestedAt ?? now();
        }

        DB::beginTransaction();
        try {
            $overtimeRequest->update([
                'project_id' => $data['project_id'],
                'overtime_date' => $data['overtime_date'],
                'status' => $status,
                'requested_at' => $requestedAt,
                'remarks' => $data['remarks'] ?? null,
                'manual_approvers' => $manualApprovers,
            ]);

            $this->syncDetails($overtimeRequest, $request->input('details', []));

            if ($submit && ! empty($manualApprovers)) {
                $created = app(ApprovalPlanController::class)->create_manual_approval_plan('overtime_request', $overtimeRequest->id);
                if (! $created || (int) $created === 0) {
                    DB::rollBack();

                    return back()
                        ->withInput()
                        ->with('toast_error', 'Failed to create approval plans. Select at least one approver.');
                }
            } elseif ($submit && empty($manualApprovers)) {
                DB::rollBack();

                return back()
                    ->withInput()
                    ->with('toast_error', 'Please select at least one approver before submitting.');
            }

            DB::commit();

            return redirect()
                ->route('overtime.my-requests.show', $overtimeRequest)
                ->with('toast_success', 'Overtime request updated.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('toast_error', 'Update failed: ' . $e->getMessage());
        }
    }

    public function myRequestsDestroy(OvertimeRequest $overtimeRequest)
    {
        if ((int) $overtimeRequest->requested_by !== (int) Auth::id()) {
            abort(403);
        }

        if (! $overtimeRequest->canBeDeletedBy(Auth::user())) {
            abort(403);
        }

        DB::beginTransaction();
        try {
            $overtimeRequest->details()->delete();
            $overtimeRequest->delete();
            DB::commit();

            return redirect()
                ->route('overtime.my-requests')
                ->with('toast_success', 'Overtime request deleted.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('toast_error', 'Delete failed: ' . $e->getMessage());
        }
    }

    /**
     * AJAX: administrations aktif per project (untuk baris karyawan).
     */
    public function administrationsByProject(Project $project)
    {
        $user = Auth::user();
        if (
            ! $user->can('overtime-requests.create')
            && ! $user->can('overtime-requests.edit')
            && ! $user->can('personal.overtime.create-own')
            && ! $user->can('personal.overtime.edit-own')
        ) {
            abort(403);
        }

        $rows = \App\Models\Administration::query()
            ->where('project_id', $project->id)
            ->where('is_active', 1)
            ->with(['employee', 'position'])
            ->orderBy('nik')
            ->get()
            ->map(function ($a) {
                $name = $a->employee->fullname ?? '-';

                return [
                    'id' => $a->id,
                    'label' => ($a->nik ?? '') . ' - ' . $name,
                ];
            });

        return response()->json($rows);
    }

    /**
     * HR / admin: detail semua; user: hanya dokumen sendiri.
     */
    public function show(OvertimeRequest $overtimeRequest)
    {
        $user = Auth::user();
        if (! $user instanceof User) {
            abort(403);
        }

        $canHr = $user->can('overtime-requests.show');
        $canViewPersonal = $this->userCanViewPersonalOvertime($user, $overtimeRequest);

        if (! $canHr && ! $canViewPersonal) {
            abort(403);
        }

        if ($canViewPersonal && ! $canHr) {
            return redirect()->route('overtime.my-requests.show', $overtimeRequest);
        }

        $overtimeRequest->load([
            'project',
            'requestedBy',
            'finishedBy',
            'details.administration.employee',
            'details.administration.position',
            'approvalPlans',
        ]);

        $title = 'Overtime Request';
        $fromPersonal = $canViewPersonal && ! $user->can('overtime-requests.show');

        return view('overtime-requests.show', compact('overtimeRequest', 'title', 'fromPersonal'));
    }

    public function markFinished(Request $request, OvertimeRequest $overtimeRequest)
    {
        $user = Auth::user();
        if (! $user instanceof User || ! $user->can('overtime-requests.finish')) {
            abort(403);
        }

        if (! $overtimeRequest->canBeMarkedFinishedByHr()) {
            return redirect()
                ->route('overtime.requests.show', $overtimeRequest)
                ->with('toast_error', 'Only approved requests can be marked as finished.');
        }

        $request->validate([
            'finished_remarks' => 'nullable|string|max:2000',
        ]);

        $overtimeRequest->update([
            'status' => OvertimeRequest::STATUS_FINISHED,
            'finished_at' => now(),
            'finished_by' => auth()->id(),
            'finished_remarks' => $request->input('finished_remarks'),
        ]);

        return redirect()
            ->route('overtime.requests.show', $overtimeRequest)
            ->with('toast_success', 'Overtime request marked as finished.');
    }

    /**
     * HR: submit draft/rejected request for approval (from detail or list).
     */
    public function submitForApproval(OvertimeRequest $overtimeRequest)
    {
        return $this->processSubmitForApproval($overtimeRequest);
    }

    /**
     * Self-service: submit draft/rejected request for approval.
     */
    public function myRequestsSubmitForApproval(OvertimeRequest $overtimeRequest)
    {
        return $this->processSubmitForApproval($overtimeRequest);
    }

    private function processSubmitForApproval(OvertimeRequest $overtimeRequest)
    {
        $user = Auth::user();
        if (! $overtimeRequest->canBeEditedBy($user)) {
            abort(403);
        }

        if (! $overtimeRequest->isEditable()) {
            return redirect()
                ->to($this->overtimeDetailUrl($overtimeRequest))
                ->with('toast_error', 'Only draft or rejected requests can be submitted for approval.');
        }

        $manualApprovers = $this->normalizeManualApprovers($overtimeRequest->manual_approvers ?? []);
        // Sementara: minimal 1 approver (sebelumnya wajib tepat 2).
        if (count($manualApprovers) < 1) {
            return redirect()
                ->to($this->overtimeDetailUrl($overtimeRequest))
                ->with('toast_error', 'Select at least one approver on the edit form before submitting.');
        }

        $lineError = $this->validateStoredLinesForSubmit($overtimeRequest);
        if ($lineError !== null) {
            return redirect()
                ->to($this->overtimeDetailUrl($overtimeRequest))
                ->with('toast_error', $lineError);
        }

        DB::beginTransaction();
        try {
            $overtimeRequest->update([
                'status' => OvertimeRequest::STATUS_PENDING,
                'requested_at' => $overtimeRequest->requested_at ?? now(),
                'manual_approvers' => $manualApprovers,
            ]);

            $created = app(ApprovalPlanController::class)->create_manual_approval_plan('overtime_request', $overtimeRequest->id);
            if (! $created || (int) $created === 0) {
                DB::rollBack();

                return redirect()
                    ->to($this->overtimeDetailUrl($overtimeRequest))
                    ->with('toast_error', 'Failed to create approval plans. Please try again or contact HR.');
            }

            DB::commit();

            return redirect()
                ->to($this->overtimeDetailUrl($overtimeRequest))
                ->with('toast_success', 'Overtime request submitted for approval.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()
                ->to($this->overtimeDetailUrl($overtimeRequest))
                ->with('toast_error', 'Submit failed: ' . $e->getMessage());
        }
    }

    /**
     * @return string|null Error message, or null if valid.
     */
    private function validateStoredLinesForSubmit(OvertimeRequest $ot): ?string
    {
        $ot->loadMissing('details');

        if ($ot->details->isEmpty()) {
            return 'Add at least one employee line before submitting.';
        }

        foreach ($ot->details as $i => $line) {
            if (empty($line->time_in) || empty($line->time_out)) {
                return 'Each line must have time in and time out.';
            }
            $in = Carbon::parse($line->time_in);
            $out = Carbon::parse($line->time_out);
            if ($out->lte($in)) {
                return 'End time must be after start time (line ' . ($i + 1) . ').';
            }

            $adminOk = Administration::query()
                ->where('id', $line->administration_id)
                ->where('project_id', $ot->project_id)
                ->exists();

            if (! $adminOk) {
                return 'An employee line does not match the selected project. Please edit the request.';
            }
        }

        return null;
    }

    private function activeProjects()
    {
        return Project::where('project_status', 1)->orderBy('project_code')->get();
    }

    private function defaultDetails(): array
    {
        return [
            [
                'administration_id' => '',
                'time_in' => '',
                'time_out' => '',
                'work_description' => '',
            ],
        ];
    }

    /**
     * Employee rows for create forms: repopulate from session after validation errors.
     */
    private function detailsForCreateForm(): array
    {
        $fallback = $this->defaultDetails();
        $raw = old('details', $fallback);
        if (! is_array($raw) || count($raw) === 0) {
            return $fallback;
        }

        return $this->normalizeDetailRowsFromOld($raw, $fallback);
    }

    /**
     * Employee rows for edit forms: repopulate from session after validation errors, else from DB.
     */
    private function detailsForEditForm(OvertimeRequest $overtimeRequest): array
    {
        $fallback = $this->detailsFromModel($overtimeRequest);
        $raw = old('details', $fallback);
        if (! is_array($raw) || count($raw) === 0) {
            return $fallback;
        }

        return $this->normalizeDetailRowsFromOld($raw, $fallback);
    }

    /**
     * @param  array<int, mixed>  $raw
     * @return array<int, array{administration_id: string, time_in: string, time_out: string, work_description: string}>
     */
    private function normalizeDetailRowsFromOld(array $raw, array $fallback): array
    {
        $out = [];
        foreach (array_values($raw) as $row) {
            if (! is_array($row)) {
                continue;
            }
            $out[] = [
                'administration_id' => isset($row['administration_id']) ? (string) $row['administration_id'] : '',
                'time_in' => isset($row['time_in']) ? (string) $row['time_in'] : '',
                'time_out' => isset($row['time_out']) ? (string) $row['time_out'] : '',
                'work_description' => isset($row['work_description']) ? (string) $row['work_description'] : '',
            ];
        }

        return count($out) > 0 ? $out : $fallback;
    }

    private function detailsFromModel(OvertimeRequest $ot): array
    {
        if ($ot->details->isEmpty()) {
            return $this->defaultDetails();
        }

        return $ot->details->map(function (OvertimeRequestDetail $detail) {
            return [
                'administration_id' => (string) $detail->administration_id,
                'time_in' => $detail->time_in ? Carbon::parse($detail->time_in)->format('H:i') : '',
                'time_out' => $detail->time_out ? Carbon::parse($detail->time_out)->format('H:i') : '',
                'work_description' => $detail->work_description ?? '',
            ];
        })->values()->all();
    }

    private function validated(Request $request): array
    {
        $submit = ($request->input('submit_action') ?? 'draft') === 'submit';

        $rules = [
            'project_id' => 'required|exists:projects,id',
            'overtime_date' => 'required|date',
            'remarks' => 'nullable|string|max:2000',
            'submit_action' => 'nullable|in:draft,submit',
            'details' => 'required|array|min:1',
            'details.*.administration_id' => [
                'required',
                Rule::exists('administrations', 'id')->where(fn($q) => $q->where('project_id', $request->project_id)),
            ],
            'details.*.time_in' => 'required|date_format:H:i',
            'details.*.time_out' => 'required|date_format:H:i',
            'details.*.work_description' => 'nullable|string|max:2000',
        ];

        if ($submit) {
            // Sementara: izinkan 1 approver (sebelumnya wajib tepat 2 — Dept Head/Manager lalu PM/Direktur).
            // $rules['manual_approvers'] = 'required|array|size:2';
            // $rules['manual_approvers.*'] = 'required|distinct|exists:users,id';
            $rules['manual_approvers'] = 'required|array|min:1';
            $rules['manual_approvers.*'] = 'required|distinct|exists:users,id';
        } else {
            $rules['manual_approvers'] = 'nullable|array';
            $rules['manual_approvers.*'] = 'nullable|exists:users,id';
        }

        return $request->validate($rules, [
            'manual_approvers.required' => 'Pilih minimal satu approver sebelum submit.',
            'manual_approvers.min' => 'Pilih minimal satu approver sebelum submit.',
            // 'manual_approvers.size' => 'Pilih tepat dua approver: (1) Dept Head/Manager, (2) Project Manager/Direktur.',
            'manual_approvers.*.distinct' => 'Approver tidak boleh duplikat.',
        ]);
    }

    private function validateLineTimes(Request $request): void
    {
        foreach ($request->input('details', []) as $i => $line) {
            if (empty($line['time_in']) || empty($line['time_out'])) {
                continue;
            }
            $in = Carbon::parse($line['time_in']);
            $out = Carbon::parse($line['time_out']);
            if ($out->lte($in)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    "details.$i.time_out" => 'End time must be after start time.',
                ]);
            }
        }
    }

    private function syncDetails(OvertimeRequest $ot, array $details): void
    {
        $ot->details()->delete();
        foreach ($details as $i => $row) {
            OvertimeRequestDetail::create([
                'overtime_request_id' => $ot->id,
                'administration_id' => $row['administration_id'],
                'time_in' => $row['time_in'],
                'time_out' => $row['time_out'],
                'work_description' => $row['work_description'] ?? null,
                'sort_order' => $i,
            ]);
        }
    }

    /**
     * Filters aligned with DataTable columns (status, project, date, requester, employees, remarks).
     *
     * @param  Builder<\App\Models\OvertimeRequest>  $query
     */
    private function applyOvertimeDatatableFilters(Builder $query, Request $request, bool $includeRequesterFilter): void
    {
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('overtime_date', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('overtime_date', '<=', $request->date_to);
        }

        if ($includeRequesterFilter && $request->filled('requester_q')) {
            $term = $this->sqlLikePattern($request->input('requester_q'));
            $query->whereHas('requestedBy', fn(Builder $q) => $q->where('name', 'like', $term));
        }

        if ($request->filled('employee_q')) {
            $term = $this->sqlLikePattern($request->input('employee_q'));
            $query->where(function (Builder $q) use ($term) {
                $q->whereHas('details.administration', fn(Builder $q2) => $q2->where('nik', 'like', $term))
                    ->orWhereHas('details.administration.employee', fn(Builder $q3) => $q3->where('fullname', 'like', $term));
            });
        }

        if ($request->filled('remarks_q')) {
            $term = $this->sqlLikePattern($request->input('remarks_q'));
            $query->where('remarks', 'like', $term);
        }
    }

    private function sqlLikePattern(string $raw): string
    {
        $s = trim($raw);

        return '%' . addcslashes($s, '%_\\') . '%';
    }

    private function overtimeEmployeesListHtml(OvertimeRequest $row): string
    {
        $lines = [];
        foreach ($row->details as $d) {
            $nik = e($d->administration->nik ?? '—');
            $name = e(optional($d->administration->employee)->fullname ?? '—');
            $lines[] = '<li class="mb-0">' . $nik . ' — ' . $name . '</li>';
        }

        if ($lines === []) {
            return '<span class="text-muted">—</span>';
        }

        return '<ul class="mb-0 pl-3 text-left overtime-dt-employees">'
            . implode('', $lines) . '</ul>';
    }

    private function overtimeRemarksCellHtml(?string $remarks): string
    {
        if ($remarks === null || $remarks === '') {
            return '<span class="text-muted">—</span>';
        }

        return '<div class="text-left text-break overtime-dt-remarks">' . nl2br(e($remarks)) . '</div>';
    }

    /**
     * URL detail: pembuat atau karyawan di baris detail (akses personal) → my-show; selain itu HR show.
     */
    private function overtimeDetailUrl(OvertimeRequest $ot): string
    {
        $user = Auth::user();
        if ($user instanceof User && $this->userCanViewPersonalOvertime($user, $ot)) {
            return route('overtime.my-requests.show', $ot);
        }

        return route('overtime.requests.show', $ot);
    }

    /**
     * User tercantum sebagai pegawai lembur (baris detail) untuk administrasi aktif user.
     */
    private function userIsInOvertimeRequestDetails(User $user, OvertimeRequest $ot): bool
    {
        $adminId = $user->administration?->id;
        if (! $adminId) {
            return false;
        }

        return $ot->details()->where('administration_id', $adminId)->exists();
    }

    /**
     * Halaman "My Overtime": pembuat dokumen atau karyawan di detail (bukan HR).
     */
    private function userCanViewPersonalOvertime(User $user, OvertimeRequest $ot): bool
    {
        if (! $user->can('personal.overtime.view-own')) {
            return false;
        }

        if ((int) $ot->requested_by === (int) $user->id) {
            return true;
        }

        return $this->userIsInOvertimeRequestDetails($user, $ot);
    }

    private function statusBadgeHtml(string $status): string
    {
        $map = [
            OvertimeRequest::STATUS_DRAFT => 'secondary',
            OvertimeRequest::STATUS_PENDING => 'warning',
            OvertimeRequest::STATUS_APPROVED => 'success',
            OvertimeRequest::STATUS_REJECTED => 'danger',
            OvertimeRequest::STATUS_FINISHED => 'info',
        ];
        $c = $map[$status] ?? 'secondary';

        return '<span class="badge badge-' . $c . '">' . strtoupper(e($status)) . '</span>';
    }

    /**
     * Hanya untuk self-service: level Supervisor ke atas.
     *
     * @return RedirectResponse|null Redirect dengan toast_error (SweetAlert di layout) jika tidak lolos.
     */
    private function ensurePersonalOvertimeLevel(bool $backWithInputOnFail): ?RedirectResponse
    {
        $user = Auth::user();
        $admin = $user->administration;
        if (! $admin || ! $admin->level) {
            return $this->redirectPersonalOvertimeDenied(
                $backWithInputOnFail,
                'Data administrasi atau level jabatan tidak ditemukan. Silakan hubungi HR.',
                'Data tidak lengkap',
                'error'
            );
        }

        $supervisor = Level::where('name', 'Supervisor')->first();
        if (! $supervisor) {
            return null;
        }

        $order = (int) ($admin->level->level_order ?? 0);
        if ($order < (int) $supervisor->level_order) {
            return $this->redirectPersonalOvertimeDenied(
                $backWithInputOnFail,
                'Hanya pegawai dengan level Supervisor ke atas yang dapat membuat permintaan lembur.',
                'Tidak diizinkan',
                'warning'
            );
        }

        return null;
    }

    private function redirectPersonalOvertimeDenied(
        bool $backWithInputOnFail,
        string $message,
        string $alertTitle,
        string $alertType
    ): RedirectResponse {
        $redirect = $backWithInputOnFail
            ? back()->withInput()
            : redirect()->route('overtime.my-requests');

        return $redirect
            ->with('toast_error', $message)
            ->with('alert_title', $alertTitle)
            ->with('alert_type', $alertType);
    }
}
