<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\LetterNumber;
use App\Models\MeetingRoom;
use App\Models\Project;
use App\Models\RoomConsumptionItem;
use App\Models\RoomConsumptionRequest;
use App\Services\ItWoZoomClient;
use App\Support\UserProject;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class RoomConsumptionRequestController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:room-consumption-requests.show')->only(['index', 'data', 'show']);
        $this->middleware('permission:room-consumption-requests.create')->only(['create', 'store']);
        $this->middleware('permission:room-consumption-requests.edit')->only(['edit', 'update', 'submitForApproval']);
        $this->middleware('permission:room-consumption-requests.delete')->only(['destroy', 'cancel']);

        $this->middleware('permission:personal.room-consumption.view-own')->only(['myRequests', 'myRequestsData', 'myRequestShow']);
        $this->middleware('permission:personal.room-consumption.create-own')->only(['myRequestsCreate', 'myRequestsStore']);
        $this->middleware('permission:personal.room-consumption.edit-own')->only(['myRequestsEdit', 'myRequestsUpdate', 'myRequestsSubmitForApproval']);
        $this->middleware('permission:personal.room-consumption.cancel-own')->only(['myRequestsDestroy', 'myRequestsCancel']);
        // Print & IT WO zoom: admin or owner (checked in method)
        $this->middleware('auth')->only([
            'print',
            'requestZoomMeeting',
            'syncZoomMeeting',
            'myRequestsRequestZoomMeeting',
            'myRequestsSyncZoomMeeting',
            'resetZoomItWoDebug',
            'myRequestsResetZoomItWoDebug',
            'zoomAvailability',
        ]);
        $this->middleware('permission:room-consumption-requests.create|personal.room-consumption.create-own|room-consumption-requests.edit|personal.room-consumption.edit-own')
            ->only(['zoomAvailability']);
    }

    private function normalizeManualApprovers($input): array
    {
        if (! is_array($input)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map(static fn ($id) => (int) $id, $input))));
    }

    public function index()
    {
        $projects = UserProject::projectsForSelect();

        return view('room-consumption-requests.index', [
            'title' => 'Room & Consumption Requests',
            'subtitle' => 'List of Requests',
            'projects' => $projects,
            'isPersonal' => false,
        ]);
    }

    public function data(Request $request)
    {
        $query = RoomConsumptionRequest::query()
            ->with(['project', 'meetingRoom', 'department', 'requestedBy'])
            ->orderByDesc('created_at');

        UserProject::scopeToAssignedProjects($query, 'project_id');
        $this->applyFilters($query, $request);

        return $this->datatableResponse($query, false);
    }

    public function create()
    {
        return $this->formView(null, false);
    }

    public function store(Request $request)
    {
        return $this->persist($request, null, false);
    }

    public function show(RoomConsumptionRequest $roomConsumptionRequest)
    {
        $this->authorizeView($roomConsumptionRequest, false);
        $roomConsumptionRequest->load(['project', 'meetingRoom', 'department', 'requestedBy', 'items', 'approvalPlans.approver']);

        return view('room-consumption-requests.show', [
            'title' => 'Room & Consumption Request',
            'subtitle' => 'Request Detail',
            'requestDoc' => $roomConsumptionRequest,
            'isPersonal' => false,
        ]);
    }

    public function edit(RoomConsumptionRequest $roomConsumptionRequest)
    {
        if (! $roomConsumptionRequest->canBeEditedBy(Auth::user())) {
            abort(403);
        }

        return $this->formView($roomConsumptionRequest, false);
    }

    public function update(Request $request, RoomConsumptionRequest $roomConsumptionRequest)
    {
        if (! $roomConsumptionRequest->canBeEditedBy(Auth::user())) {
            abort(403);
        }

        return $this->persist($request, $roomConsumptionRequest, false);
    }

    public function destroy(RoomConsumptionRequest $roomConsumptionRequest)
    {
        if (! $roomConsumptionRequest->canBeDeletedBy(Auth::user())) {
            abort(403);
        }

        try {
            if ($roomConsumptionRequest->letter_number_id) {
                $roomConsumptionRequest->releaseLetterNumber();
            }
            $roomConsumptionRequest->delete();

            return redirect()->route('room-consumption-requests.index')
                ->with('toast_success', 'Request deleted.');
        } catch (\Throwable $e) {
            return back()->with('toast_error', 'Delete failed: '.$e->getMessage());
        }
    }

    public function submitForApproval(RoomConsumptionRequest $roomConsumptionRequest)
    {
        if (! $roomConsumptionRequest->canBeEditedBy(Auth::user())) {
            abort(403);
        }

        return $this->processSubmit($roomConsumptionRequest, route('room-consumption-requests.show', $roomConsumptionRequest));
    }

    public function cancel(RoomConsumptionRequest $roomConsumptionRequest)
    {
        if (! $roomConsumptionRequest->canCancel()) {
            return back()->with('toast_error', 'This request cannot be cancelled.');
        }

        if (! Auth::user()->can('room-consumption-requests.delete')
            && (int) $roomConsumptionRequest->requested_by !== (int) Auth::id()) {
            abort(403);
        }

        $roomConsumptionRequest->update([
            'status' => RoomConsumptionRequest::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);

        app(ApprovalPlanController::class)->closeOpenApprovalPlans('room_consumption_request', $roomConsumptionRequest->id);

        return back()->with('toast_success', 'Request cancelled.');
    }

    public function print(RoomConsumptionRequest $roomConsumptionRequest)
    {
        $user = Auth::user();
        $canAdmin = $user->can('room-consumption-requests.show');
        $canOwn = $user->can('personal.room-consumption.view-own')
            && (int) $roomConsumptionRequest->requested_by === (int) $user->id;
        if (! $canAdmin && ! $canOwn) {
            abort(403);
        }

        $roomConsumptionRequest->load([
            'project',
            'meetingRoom',
            'department',
            'requestedBy',
            'items',
            'approvalPlans.approver.administration.position',
        ]);

        return view('room-consumption-requests.print', [
            'doc' => $roomConsumptionRequest,
        ]);
    }

    public function requestZoomMeeting(RoomConsumptionRequest $roomConsumptionRequest)
    {
        return $this->processRequestZoomMeeting(
            $roomConsumptionRequest,
            false,
            route('room-consumption-requests.show', $roomConsumptionRequest)
        );
    }

    public function syncZoomMeeting(RoomConsumptionRequest $roomConsumptionRequest)
    {
        return $this->processSyncZoomMeeting(
            $roomConsumptionRequest,
            false,
            route('room-consumption-requests.show', $roomConsumptionRequest)
        );
    }

    /**
     * AJAX: Zoom Meeting ID availability (IT WO accounts 131/132/134) for a date.
     */
    public function zoomAvailability(Request $request, ItWoZoomClient $client)
    {
        $validated = $request->validate([
            'date' => ['required', 'date_format:Y-m-d'],
        ]);

        $result = $client->getZoomAvailability($validated['date']);

        if (! ($result['success'] ?? false)) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Failed to load Zoom availability.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'data' => $result['data'] ?? [],
            'trial' => (bool) ($result['trial'] ?? false),
        ]);
    }

    public function myRequestsRequestZoomMeeting(RoomConsumptionRequest $roomConsumptionRequest)
    {
        return $this->processRequestZoomMeeting(
            $roomConsumptionRequest,
            true,
            route('room-consumption-requests.my-requests.show', $roomConsumptionRequest)
        );
    }

    public function myRequestsSyncZoomMeeting(RoomConsumptionRequest $roomConsumptionRequest)
    {
        return $this->processSyncZoomMeeting(
            $roomConsumptionRequest,
            true,
            route('room-consumption-requests.my-requests.show', $roomConsumptionRequest)
        );
    }

    public function resetZoomItWoDebug(RoomConsumptionRequest $roomConsumptionRequest)
    {
        return $this->processResetZoomItWoDebug(
            $roomConsumptionRequest,
            false,
            route('room-consumption-requests.show', $roomConsumptionRequest)
        );
    }

    public function myRequestsResetZoomItWoDebug(RoomConsumptionRequest $roomConsumptionRequest)
    {
        return $this->processResetZoomItWoDebug(
            $roomConsumptionRequest,
            true,
            route('room-consumption-requests.my-requests.show', $roomConsumptionRequest)
        );
    }

    // —— My Features ——

    public function myRequests()
    {
        return view('room-consumption-requests.index', [
            'title' => 'My Room & Consumption Requests',
            'subtitle' => 'My Requests',
            'projects' => UserProject::projectsForSelect(),
            'isPersonal' => true,
        ]);
    }

    public function myRequestsData(Request $request)
    {
        $query = RoomConsumptionRequest::query()
            ->with(['project', 'meetingRoom', 'department', 'requestedBy'])
            ->where('requested_by', Auth::id())
            ->orderByDesc('created_at');

        $this->applyFilters($query, $request);

        return $this->datatableResponse($query, true);
    }

    public function myRequestsCreate()
    {
        return $this->formView(null, true);
    }

    public function myRequestsStore(Request $request)
    {
        return $this->persist($request, null, true);
    }

    public function myRequestShow(RoomConsumptionRequest $roomConsumptionRequest)
    {
        $this->authorizeView($roomConsumptionRequest, true);
        $roomConsumptionRequest->load(['project', 'meetingRoom', 'department', 'requestedBy', 'items', 'approvalPlans.approver']);

        return view('room-consumption-requests.show', [
            'title' => 'My Room & Consumption Request',
            'subtitle' => 'Request Detail',
            'requestDoc' => $roomConsumptionRequest,
            'isPersonal' => true,
        ]);
    }

    public function myRequestsEdit(RoomConsumptionRequest $roomConsumptionRequest)
    {
        $this->authorizeView($roomConsumptionRequest, true);
        if (! $roomConsumptionRequest->canBeEditedBy(Auth::user())) {
            abort(403);
        }

        return $this->formView($roomConsumptionRequest, true);
    }

    public function myRequestsUpdate(Request $request, RoomConsumptionRequest $roomConsumptionRequest)
    {
        $this->authorizeView($roomConsumptionRequest, true);
        if (! $roomConsumptionRequest->canBeEditedBy(Auth::user())) {
            abort(403);
        }

        return $this->persist($request, $roomConsumptionRequest, true);
    }

    public function myRequestsSubmitForApproval(RoomConsumptionRequest $roomConsumptionRequest)
    {
        $this->authorizeView($roomConsumptionRequest, true);
        if (! $roomConsumptionRequest->canBeEditedBy(Auth::user())) {
            abort(403);
        }

        return $this->processSubmit($roomConsumptionRequest, route('room-consumption-requests.my-requests.show', $roomConsumptionRequest));
    }

    public function myRequestsDestroy(RoomConsumptionRequest $roomConsumptionRequest)
    {
        $this->authorizeView($roomConsumptionRequest, true);
        if (! $roomConsumptionRequest->canBeDeletedBy(Auth::user())) {
            abort(403);
        }

        try {
            if ($roomConsumptionRequest->letter_number_id) {
                $roomConsumptionRequest->releaseLetterNumber();
            }
            $roomConsumptionRequest->delete();

            return redirect()->route('room-consumption-requests.my-requests')
                ->with('toast_success', 'Request deleted.');
        } catch (\Throwable $e) {
            return back()->with('toast_error', 'Delete failed: '.$e->getMessage());
        }
    }

    public function myRequestsCancel(RoomConsumptionRequest $roomConsumptionRequest)
    {
        $this->authorizeView($roomConsumptionRequest, true);
        if (! $roomConsumptionRequest->canCancel()) {
            return back()->with('toast_error', 'This request cannot be cancelled.');
        }

        $roomConsumptionRequest->update([
            'status' => RoomConsumptionRequest::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);

        app(ApprovalPlanController::class)->closeOpenApprovalPlans('room_consumption_request', $roomConsumptionRequest->id);

        return back()->with('toast_success', 'Request cancelled.');
    }

    // —— Shared helpers ——

    private function formView(?RoomConsumptionRequest $doc, bool $isPersonal)
    {
        if ($doc) {
            $doc->load('items');
        }

        $projects = UserProject::projectsForSelect();
        $departments = Department::where('department_status', 1)->orderBy('department_name')->get();
        $rooms = collect();
        if ($doc?->project_id) {
            $rooms = MeetingRoom::active()->where('project_id', $doc->project_id)->orderBy('room_name')->get();
        }

        $consumption = [];
        foreach (RoomConsumptionRequest::CONSUMPTION_TYPES as $type => $label) {
            $item = $doc?->items->firstWhere('consumption_type', $type);
            $oldSelected = old("consumption.{$type}.is_selected");
            $isSelected = $oldSelected !== null
                ? (bool) $oldSelected
                : (bool) ($item?->is_selected);
            $consumption[$type] = [
                'label' => $label,
                'is_selected' => $isSelected,
                'description' => old("consumption.{$type}.description", $item?->description),
            ];
        }

        return view('room-consumption-requests.form', [
            'title' => $isPersonal ? 'My Room & Consumption Request' : 'Room & Consumption Request',
            'subtitle' => $doc ? 'Edit Room & Consumption Request' : 'Create Room & Consumption Request',
            'doc' => $doc,
            'projects' => $projects,
            'departments' => $departments,
            'rooms' => $rooms,
            'consumption' => $consumption,
            'isPersonal' => $isPersonal,
            'formAction' => $this->formAction($doc, $isPersonal),
            'method' => $doc ? 'PUT' : 'POST',
            'cancelRoute' => $isPersonal
                ? ($doc ? route('room-consumption-requests.my-requests.show', $doc) : route('room-consumption-requests.my-requests'))
                : ($doc ? route('room-consumption-requests.show', $doc) : route('room-consumption-requests.index')),
        ]);
    }

    private function formAction(?RoomConsumptionRequest $doc, bool $isPersonal): string
    {
        if ($isPersonal) {
            return $doc
                ? route('room-consumption-requests.my-requests.update', $doc)
                : route('room-consumption-requests.my-requests.store');
        }

        return $doc
            ? route('room-consumption-requests.update', $doc)
            : route('room-consumption-requests.store');
    }

    private function persist(Request $request, ?RoomConsumptionRequest $doc, bool $isPersonal)
    {
        $data = $this->validated($request);
        $room = MeetingRoom::findOrFail($data['meeting_room_id']);

        if ((int) $room->project_id !== (int) $data['project_id']) {
            return back()->withInput()->with('toast_error', 'Selected room does not belong to the selected project.');
        }

        if ($r = UserProject::guardProjectInAssignmentScope((int) $data['project_id'])) {
            return $r;
        }

        $manualApprovers = $this->normalizeManualApprovers($request->input('manual_approvers'));
        $submit = ($data['submit_action'] ?? 'draft') === 'submit';
        $status = $submit
            ? RoomConsumptionRequest::STATUS_SUBMITTED
            : RoomConsumptionRequest::STATUS_DRAFT;

        if ($submit && empty($manualApprovers)) {
            return back()->withInput()->with('toast_error', 'Please select at least one approver before submitting.');
        }

        if ($submit && empty($data['letter_number_id'])) {
            return back()->withInput()->with('toast_error', 'Please select a letter number (RCR) before submitting.');
        }

        $project = Project::findOrFail($data['project_id']);
        $letterNumberString = null;
        $requestNumber = null;

        if (! empty($data['letter_number_id'])) {
            $letter = LetterNumber::findOrFail($data['letter_number_id']);
            if ($doc && (int) $doc->letter_number_id === (int) $letter->id) {
                $letterNumberString = $letter->letter_number;
            } elseif ($letter->status !== 'reserved') {
                return back()->withInput()->with('toast_error', 'Selected letter number is not available.');
            } else {
                $letterNumberString = $letter->letter_number;
            }
            $letterProjectCode = $letter->project?->project_code
                ?? $letter->project_code
                ?? '000H';
            $requestNumber = RoomConsumptionRequest::formatRequestNumber(
                $letterNumberString,
                $letterProjectCode,
                now()
            );
        }

        if ($submit) {
            $conflict = $room->findConflictForDateTime(
                $data['meeting_date'],
                $data['start_time'],
                $data['end_time'],
                $doc?->id
            );
            if ($conflict) {
                return back()->withInput()->with([
                    'toast_error' => $room->conflictMessage($conflict),
                    'toast_error_left' => true,
                    'alert_title' => 'Ruangan Terpakai',
                ]);
            }
        }

        DB::beginTransaction();
        try {
            $payload = [
                'meeting_room_id' => $room->id,
                'project_id' => $project->id,
                'department_id' => $data['department_id'] ?? null,
                'meeting_title' => $data['meeting_title'],
                'meeting_date' => $data['meeting_date'],
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'attendees_count' => $data['attendees_count'],
                'facilities' => $data['facilities'] ?? $room->facilities,
                'need_zoom' => ! empty($data['need_zoom']),
                'manual_approvers' => $manualApprovers,
                'notes' => $data['notes'] ?? null,
                'status' => $status,
                'zoom_sync_status' => ! empty($data['need_zoom']) ? 'pending' : 'not_required',
            ];

            if ($letterNumberString) {
                $payload['letter_number_id'] = $data['letter_number_id'];
                $payload['letter_number'] = $letterNumberString;
                // Keep existing Reg. No on edit; generate only when creating or still empty
                if (! $doc || empty($doc->request_number)) {
                    $payload['request_number'] = $requestNumber;
                }
            }

            if ($submit) {
                $payload['submitted_at'] = now();
            }

            if ($doc) {
                $doc->update($payload);
                $model = $doc->fresh();
            } else {
                $payload['requested_by'] = Auth::id();
                $model = RoomConsumptionRequest::create($payload);
            }

            $this->syncConsumptionItems($model, $request->input('consumption', []));

            if (! empty($data['letter_number_id']) && $submit) {
                $letter = LetterNumber::find($data['letter_number_id']);
                if ($letter && $letter->status === 'reserved') {
                    $letter->markAsUsed('room_consumption_request', $model->id, Auth::id());
                }
            }

            if ($submit) {
                $created = app(ApprovalPlanController::class)
                    ->create_manual_approval_plan('room_consumption_request', $model->id);
                if (! $created || (int) $created === 0) {
                    DB::rollBack();

                    return back()->withInput()->with('toast_error', 'Failed to create approval plans.');
                }
            }

            DB::commit();

            if ($submit) {
                // Form "Save & Submit" — sama seperti tombol Submit for Approval
                $model->refresh();
                $this->dispatchZoomItWoAfterSubmit($model);
            }

            $redirect = $isPersonal
                ? route('room-consumption-requests.my-requests.show', $model)
                : route('room-consumption-requests.show', $model);

            return redirect($redirect)->with('toast_success', $submit
                ? 'Request submitted for approval.'
                : 'Draft saved.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('toast_error', 'Save failed: '.$e->getMessage());
        }
    }

    private function processSubmit(RoomConsumptionRequest $model, string $redirectRoute)
    {
        if (! $model->canSubmitForApproval()) {
            return back()->with('toast_error', 'Request cannot be submitted.');
        }

        $approvers = $this->normalizeManualApprovers($model->manual_approvers);
        if (empty($approvers)) {
            return back()->with('toast_error', 'Please select at least one approver.');
        }
        if (! $model->letter_number_id) {
            return back()->with('toast_error', 'Please select a letter number (RCR) before submitting.');
        }

        $room = $model->meetingRoom;
        $conflict = $room->findConflictForDateTime(
            $model->meeting_date->format('Y-m-d'),
            Carbon::parse($model->start_time)->format('H:i:s'),
            Carbon::parse($model->end_time)->format('H:i:s'),
            $model->id
        );
        if ($conflict) {
            return back()->with([
                'toast_error' => $room->conflictMessage($conflict),
                'toast_error_left' => true,
                'alert_title' => 'Ruangan Terpakai',
            ]);
        }

        DB::beginTransaction();
        try {
            if (! $model->request_number && $model->letter_number) {
                $letter = LetterNumber::find($model->letter_number_id);
                $letterProjectCode = $letter?->project?->project_code
                    ?? $letter?->project_code
                    ?? $model->project->project_code
                    ?? '000H';
                $model->request_number = RoomConsumptionRequest::formatRequestNumber(
                    $model->letter_number,
                    $letterProjectCode,
                    now()
                );
            }

            $model->update([
                'status' => RoomConsumptionRequest::STATUS_SUBMITTED,
                'submitted_at' => now(),
                'request_number' => $model->request_number,
            ]);

            $letter = LetterNumber::find($model->letter_number_id);
            if ($letter && $letter->status === 'reserved') {
                $letter->markAsUsed('room_consumption_request', $model->id, Auth::id());
            }

            $created = app(ApprovalPlanController::class)
                ->create_manual_approval_plan('room_consumption_request', $model->id);
            if (! $created || (int) $created === 0) {
                DB::rollBack();

                return back()->with('toast_error', 'Failed to create approval plans.');
            }

            DB::commit();

            // Tombol "Submit for Approval" (detail/list) & route my-requests.submit / .submit
            $model->refresh();
            $this->dispatchZoomItWoAfterSubmit($model);

            return redirect($redirectRoute)->with('toast_success', 'Request submitted for approval.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('toast_error', 'Submit failed: '.$e->getMessage());
        }
    }

    private function syncConsumptionItems(RoomConsumptionRequest $model, array $consumption): void
    {
        foreach (RoomConsumptionRequest::CONSUMPTION_TYPES as $type => $label) {
            $row = $consumption[$type] ?? [];
            $selected = ! empty($row['is_selected']);
            RoomConsumptionItem::updateOrCreate(
                ['request_id' => $model->id, 'consumption_type' => $type],
                [
                    'is_selected' => $selected,
                    'description' => $selected ? ($row['description'] ?? null) : null,
                ]
            );
        }
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'meeting_room_id' => ['required', 'exists:meeting_rooms,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'meeting_title' => ['required', 'string', 'max:255'],
            'meeting_date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'attendees_count' => ['required', 'integer', 'min:1'],
            'facilities' => ['nullable', 'string'],
            'need_zoom' => ['nullable', 'boolean'],
            'notes' => ['nullable', 'string'],
            'letter_number_id' => ['nullable', 'exists:letter_numbers,id'],
            'submit_action' => ['nullable', Rule::in(['draft', 'submit'])],
            'manual_approvers' => ['nullable', 'array'],
            'manual_approvers.*' => ['integer', 'exists:users,id'],
            'consumption' => ['nullable', 'array'],
        ]);
    }

    private function applyFilters($query, Request $request): void
    {
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('date_from')) {
            $query->whereDate('meeting_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('meeting_date', '<=', $request->date_to);
        }
        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($w) use ($q) {
                $w->where('request_number', 'like', "%{$q}%")
                    ->orWhere('meeting_title', 'like', "%{$q}%")
                    ->orWhere('letter_number', 'like', "%{$q}%");
            });
        }
        if ($request->filled('requester_q')) {
            $q = $request->requester_q;
            $query->whereHas('requestedBy', fn ($w) => $w->where('name', 'like', "%{$q}%"));
        }
        if ($request->filled('room_q')) {
            $q = $request->room_q;
            $query->whereHas('meetingRoom', fn ($w) => $w->where('room_name', 'like', "%{$q}%"));
        }
    }

    private function datatableResponse($query, bool $isPersonal)
    {
        return datatables()->of($query)
            ->addIndexColumn()
            ->addColumn('request_number', fn ($row) => e($row->request_number ?: '—'))
            ->addColumn('project_label', fn ($row) => e(($row->project->project_code ?? '').' - '.($row->project->project_name ?? '')))
            ->addColumn('room_name', fn ($row) => e($row->meetingRoom->room_name ?? '—'))
            ->addColumn('meeting_date_fmt', fn ($row) => $row->meeting_date
                ? format_date_with_weekday($row->meeting_date)
                : '—')
            ->addColumn('time_range', function ($row) {
                $s = $row->start_time ? Carbon::parse($row->start_time)->format('H:i') : '';
                $e = $row->end_time ? Carbon::parse($row->end_time)->format('H:i') : '';

                return e(trim("{$s} - {$e}", ' -'));
            })
            ->addColumn('status_badge', fn ($row) => $this->statusBadgeHtml($row->status))
            ->addColumn('requester', fn ($row) => e($row->requestedBy->name ?? '—'))
            ->addColumn('actions', function ($row) use ($isPersonal) {
                return $this->actionsHtml($row, $isPersonal);
            })
            ->rawColumns(['status_badge', 'actions'])
            ->make(true);
    }

    private function actionsHtml(RoomConsumptionRequest $row, bool $isPersonal): string
    {
        $show = $isPersonal
            ? route('room-consumption-requests.my-requests.show', $row)
            : route('room-consumption-requests.show', $row);
        $edit = $isPersonal
            ? route('room-consumption-requests.my-requests.edit', $row)
            : route('room-consumption-requests.edit', $row);
        $submit = $isPersonal
            ? route('room-consumption-requests.my-requests.submit', $row)
            : route('room-consumption-requests.submit', $row);
        $destroy = $isPersonal
            ? route('room-consumption-requests.my-requests.destroy', $row)
            : route('room-consumption-requests.destroy', $row);

        $html = '<div class="btn-group">';
        $html .= '<a href="'.$show.'" class="btn btn-sm btn-info mr-1" title="View"><i class="fas fa-eye"></i></a>';
        if ($row->canBeEditedBy(Auth::user())) {
            $html .= '<a href="'.$edit.'" class="btn btn-sm btn-warning mr-1" title="Edit"><i class="fas fa-edit"></i></a>';
        }
        if ($row->canSubmitForApproval() && $row->canBeEditedBy(Auth::user())) {
            $html .= '<form method="POST" action="'.$submit.'" class="d-inline mr-1" onsubmit="return confirm(\'Submit for approval?\');">'
                .csrf_field()
                .'<button type="submit" class="btn btn-sm btn-success" title="Submit"><i class="fas fa-paper-plane"></i></button></form>';
        }
        if ($row->canBeDeletedBy(Auth::user())) {
            $html .= '<form method="POST" action="'.$destroy.'" class="d-inline" onsubmit="return confirm(\'Delete this request?\');">'
                .csrf_field().method_field('DELETE')
                .'<button type="submit" class="btn btn-sm btn-danger" title="Delete"><i class="fas fa-trash"></i></button></form>';
        }
        $html .= '</div>';

        return $html;
    }

    private function statusBadgeHtml(string $status): string
    {
        $map = [
            'draft' => 'secondary',
            'submitted' => 'info',
            'approved' => 'success',
            'rejected' => 'danger',
            'cancelled' => 'dark',
            'completed' => 'primary',
        ];
        $class = $map[$status] ?? 'secondary';

        return '<span class="badge badge-'.$class.'">'.e(ucfirst($status)).'</span>';
    }

    private function authorizeView(RoomConsumptionRequest $doc, bool $personalOnly): void
    {
        if ($personalOnly) {
            if ((int) $doc->requested_by !== (int) Auth::id()) {
                abort(403);
            }

            return;
        }

        if (! Auth::user()->can('room-consumption-requests.show')) {
            if ((int) $doc->requested_by === (int) Auth::id() && Auth::user()->can('personal.room-consumption.view-own')) {
                return;
            }
            abort(403);
        }
    }

    private function dispatchZoomItWoAfterSubmit(RoomConsumptionRequest $model): void
    {
        $doc = $model->fresh() ?? $model;

        if (! $doc->need_zoom) {
            return;
        }

        if ($doc->status === RoomConsumptionRequest::STATUS_DRAFT) {
            Log::warning('Skipped IT WO Zoom dispatch: RCR still draft', ['rcr_id' => $doc->id]);

            return;
        }

        try {
            app(ItWoZoomClient::class)->dispatchOnSubmit($doc);
        } catch (\Throwable $e) {
            Log::error('Failed to dispatch IT WO Zoom after RCR submit', [
                'rcr_id' => $doc->id,
                'status' => $doc->status,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function authorizeZoomItWoAction(RoomConsumptionRequest $doc, bool $personalOnly): void
    {
        if ($personalOnly) {
            if ((int) $doc->requested_by !== (int) Auth::id()) {
                abort(403);
            }

            return;
        }

        $user = Auth::user();
        if ($user->can('room-consumption-requests.edit')) {
            return;
        }

        if ($user->can('personal.room-consumption.edit-own') && (int) $doc->requested_by === (int) $user->id) {
            return;
        }

        abort(403);
    }

    private function processRequestZoomMeeting(RoomConsumptionRequest $doc, bool $personalOnly, string $redirectRoute)
    {
        $this->authorizeZoomItWoAction($doc, $personalOnly);

        if (! $doc->canRequestZoomItWo()) {
            return redirect($redirectRoute)->with('toast_error', 'Zoom Meeting ID tidak dapat diminta untuk request ini.');
        }

        $client = app(ItWoZoomClient::class);
        $result = $client->createZoomMeetingRequest($doc);

        if (! ($result['success'] ?? false)) {
            return redirect($redirectRoute)->with(
                'toast_error',
                $result['message'] ?? 'Gagal membuat IT Work Order.'
            );
        }

        $data = $result['data'] ?? [];
        $doc->update([
            'it_wo_id' => isset($data['it_wo_id']) ? (string) $data['it_wo_id'] : null,
            'it_wo_number' => $data['it_wo_number'] ?? null,
            'zoom_sync_status' => $this->mapItWoStatusToZoomSync($data['status'] ?? 'open'),
            'zoom_meeting_id' => $data['zoom_meeting_id'] ?? null,
            'zoom_topic' => $data['zoom_topic'] ?? null,
            'zoom_join_url' => $data['zoom_join_url'] ?? null,
            'zoom_passcode' => $data['zoom_passcode'] ?? null,
        ]);

        if (! empty($result['trial'])) {
            $syncResult = $client->syncZoomMeetingRequest($doc->fresh());
            if ($syncResult['success'] ?? false) {
                $this->applyZoomSyncData($doc, $syncResult['data'] ?? []);
            }

            return redirect($redirectRoute)->with(
                'toast_success',
                'Zoom Meeting ID berhasil dibuat (mode percobaan).'
            );
        }

        $woNumber = $data['it_wo_number'] ?? $doc->it_wo_number;
        $message = ! empty($result['idempotent'])
            ? 'IT Work Order sudah ada: '.$woNumber
            : 'IT Work Order berhasil dibuat: '.$woNumber.' (kategori Zoom Meeting ID / Room Meeting ID).';

        return redirect($redirectRoute)->with(
            'toast_success',
            $message.' Gunakan Refresh Zoom Status setelah IT mengisi Meeting ID.'
        );
    }

    private function processSyncZoomMeeting(RoomConsumptionRequest $doc, bool $personalOnly, string $redirectRoute)
    {
        $this->authorizeZoomItWoAction($doc, $personalOnly);

        if (! $doc->canSyncZoomItWo()) {
            return redirect($redirectRoute)->with('toast_error', 'Status Zoom tidak dapat diperbarui untuk request ini.');
        }

        $client = app(ItWoZoomClient::class);
        $result = $client->syncZoomMeetingRequest($doc);

        if (! ($result['success'] ?? false)) {
            return redirect($redirectRoute)->with(
                'toast_error',
                $result['message'] ?? 'Gagal mengambil status dari IT Work Order.'
            );
        }

        $this->applyZoomSyncData($doc, $result['data'] ?? []);

        $message = 'Status Zoom diperbarui.';
        if (! empty($result['trial'])) {
            $message = 'Zoom Meeting ID berhasil diperbarui (mode percobaan).';
        }

        return redirect($redirectRoute)->with('toast_success', $message);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function applyZoomSyncData(RoomConsumptionRequest $doc, array $data): void
    {
        $updates = [
            'zoom_sync_status' => $this->mapItWoStatusToZoomSync($data['status'] ?? $doc->zoom_sync_status),
        ];

        if (! empty($data['it_wo_number'])) {
            $updates['it_wo_number'] = $data['it_wo_number'];
        }
        if (! empty($data['zoom_meeting_id'])) {
            $updates['zoom_meeting_id'] = $data['zoom_meeting_id'];
        }
        if (! empty($data['zoom_topic'])) {
            $updates['zoom_topic'] = $data['zoom_topic'];
        }
        if (! empty($data['zoom_join_url'])) {
            $updates['zoom_join_url'] = $data['zoom_join_url'];
        }
        if (! empty($data['zoom_passcode'])) {
            $updates['zoom_passcode'] = $data['zoom_passcode'];
        }

        $doc->update($updates);
    }

    private function mapItWoStatusToZoomSync(?string $status): string
    {
        return match ($status) {
            'completed', 'done' => 'completed',
            'failed', 'error', 'cancelled' => 'failed',
            'open', 'new' => 'open',
            'processing', 'in_progress' => 'processing',
            default => 'pending',
        };
    }

    private function processResetZoomItWoDebug(RoomConsumptionRequest $doc, bool $personalOnly, string $redirectRoute)
    {
        if (! config('app.debug')) {
            abort(404);
        }

        $this->authorizeZoomItWoAction($doc, $personalOnly);

        if (! $doc->hasZoomItWoDebugState()) {
            return redirect($redirectRoute)->with('toast_error', 'Tidak ada data IT WO / Zoom yang perlu direset.');
        }

        $client = app(ItWoZoomClient::class);
        $itWoId = $doc->it_wo_id ? (string) $doc->it_wo_id : null;
        $deleteNote = '';

        if ($itWoId) {
            $result = $client->deleteZoomMeetingRequest($itWoId);
            if (! ($result['success'] ?? false)) {
                return redirect($redirectRoute)->with(
                    'toast_error',
                    $result['message'] ?? 'Gagal menghapus IT Work Order di rest-server.'
                );
            }
            $deleteNote = ! empty($result['skipped'])
                ? ' (WO tidak ada / trial — hanya reset lokal)'
                : ' dan IT WO #'.$itWoId.' dihapus';
        }

        $client->resetLocalZoomItWoState($doc);

        return redirect($redirectRoute)->with(
            'toast_success',
            'Debug: state IT WO direset'.$deleteNote.'. Silakan Request Zoom via IT WO ulang.'
        );
    }
}
