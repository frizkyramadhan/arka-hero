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
use Illuminate\Database\QueryException;
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
        if (! $roomConsumptionRequest->isPendingHr()) {
            return redirect()->route('room-consumption-requests.my-requests.show', $roomConsumptionRequest)
                ->with('toast_error', 'Pengajuan ini sudah dikonfirmasi HR. Tidak dapat diedit.');
        }
        if (! $roomConsumptionRequest->canBeEditedBy(Auth::user())) {
            abort(403);
        }

        return $this->formView($roomConsumptionRequest, true);
    }

    public function myRequestsUpdate(Request $request, RoomConsumptionRequest $roomConsumptionRequest)
    {
        $this->authorizeView($roomConsumptionRequest, true);
        if (! $roomConsumptionRequest->isPendingHr()) {
            return redirect()->route('room-consumption-requests.my-requests.show', $roomConsumptionRequest)
                ->with('toast_error', 'Pengajuan ini sudah dikonfirmasi HR. Tidak dapat diedit.');
        }
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
            'subtitle' => $doc
                ? 'Edit Room & Consumption Request'
                : ($isPersonal ? 'Create My Room & Consumption Request' : 'Create Room & Consumption Request'),
            'doc' => $doc,
            'projects' => $projects,
            'departments' => $departments,
            'rooms' => $rooms,
            'consumption' => $consumption,
            'isPersonal' => $isPersonal,
            'isPersonalRegMode' => $isPersonal && (! $doc || $doc->isPendingHr()),
            'previewRegNumber' => ($isPersonal && ! $doc)
                ? 'REQ'.sprintf('%05d', $this->maxSubmittedByUserReqSequence() + 1)
                : null,
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

        $personalRegFlow = $isPersonal && (! $doc || $doc->isPendingHr());
        $hrConfirmPending = ! $isPersonal && $doc && $doc->isPendingHr();

        if ($personalRegFlow && $submit) {
            return back()->withInput()->with(
                'toast_error',
                'Pengajuan menunggu konfirmasi HR. Nomor surat RCR akan diassign oleh HR sebelum submit approval.'
            );
        }

        if ($submit && empty($manualApprovers) && ! $personalRegFlow) {
            return back()->withInput()->with('toast_error', 'Please select at least one approver before submitting.');
        }

        if ($submit && empty($data['letter_number_id']) && ! $personalRegFlow) {
            return back()->withInput()->with('toast_error', 'Please select a letter number (RCR) before submitting.');
        }

        if ($hrConfirmPending && empty($data['letter_number_id'])) {
            return back()->withInput()->with('toast_error', 'Pilih nomor surat RCR untuk mengonfirmasi pengajuan karyawan.');
        }

        if ($hrConfirmPending && empty($manualApprovers)) {
            return back()->withInput()->with('toast_error', 'Pilih minimal satu approver untuk pengajuan karyawan.');
        }

        $project = Project::findOrFail($data['project_id']);
        $letterNumberString = null;
        $requestNumber = null;
        $clearSubmittedByUser = false;

        if ($hrConfirmPending && ! empty($data['letter_number_id'])) {
            $letter = LetterNumber::findOrFail($data['letter_number_id']);
            $ownedByThis = $doc && (string) $letter->related_document_id === (string) $doc->id;
            if ($letter->status !== 'reserved' && ! $ownedByThis) {
                return back()->withInput()->with('toast_error', 'Selected letter number is not available.');
            }
            $letterNumberString = $letter->letter_number;
            $letterProjectCode = $letter->project?->project_code
                ?? $letter->project_code
                ?? $project->project_code
                ?? '000H';
            $requestNumber = RoomConsumptionRequest::formatRequestNumber(
                $letterNumberString,
                $letterProjectCode,
                $data['start_date']
            );
            if (RoomConsumptionRequest::where('request_number', $requestNumber)->where('id', '!=', $doc->id)->exists()) {
                return back()->withInput()->with('toast_error', 'Reg. No dari surat ini sudah digunakan: '.$requestNumber);
            }
            $clearSubmittedByUser = true;
        } elseif (! $personalRegFlow && ! empty($data['letter_number_id'])) {
            $letter = LetterNumber::findOrFail($data['letter_number_id']);
            if ($doc && (int) $doc->letter_number_id === (int) $letter->id) {
                $letterNumberString = $letter->letter_number;
            } elseif ($letter->status === 'reserved') {
                $letterNumberString = $letter->letter_number;
            } elseif ($doc && (string) $letter->related_document_id === (string) $doc->id) {
                $letterNumberString = $letter->letter_number;
            } else {
                return back()->withInput()->with('toast_error', 'Selected letter number is not available.');
            }
            $letterProjectCode = $letter->project?->project_code
                ?? $letter->project_code
                ?? '000H';
            $requestNumber = RoomConsumptionRequest::formatRequestNumber(
                $letterNumberString,
                $letterProjectCode,
                $data['start_date']
            );
        }

        if ($submit && $this->shouldCheckRcrRoomConflict($doc, $data)) {
            $conflict = $room->findConflictForDateTime(
                $data['start_date'],
                $data['end_date'],
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
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'],
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
                if ($requestNumber && (! $doc || empty($doc->request_number) || $doc->usesTemporaryRegNumber() || $hrConfirmPending)) {
                    $payload['request_number'] = $requestNumber;
                }
            }

            if ($clearSubmittedByUser) {
                $payload['submitted_by_user'] = false;
            }

            if ($submit) {
                $payload['submitted_at'] = now();
            }

            $previousLetterId = $doc?->letter_number_id ? (int) $doc->letter_number_id : null;

            if ($personalRegFlow && ! $doc) {
                $lockHeld = $this->acquireRcrRegSequenceLock();
                if (DB::connection()->getDriverName() === 'mysql' && ! $lockHeld) {
                    throw new \RuntimeException('Sistem sedang sibuk. Silakan coba lagi sebentar.');
                }
                try {
                    $model = null;
                    for ($attempt = 0; $attempt < 25; $attempt++) {
                        $regNumber = $this->allocateNextSubmittedByUserReqNumber();
                        $payload['request_number'] = $regNumber;
                        $payload['submitted_by_user'] = true;
                        $payload['requested_by'] = Auth::id();
                        try {
                            $model = RoomConsumptionRequest::create($payload);
                            break;
                        } catch (QueryException $e) {
                            $isDuplicate = (int) ($e->errorInfo[1] ?? 0) === 1062
                                || str_contains($e->getMessage(), 'Duplicate entry');
                            if (! $isDuplicate || $attempt === 24) {
                                throw $e;
                            }
                        }
                    }
                    if (! $model) {
                        throw new \RuntimeException('Tidak dapat menghasilkan nomor REQ unik.');
                    }
                } finally {
                    if (! empty($lockHeld)) {
                        $this->releaseRcrRegSequenceLock();
                    }
                }
            } elseif ($doc) {
                $doc->update($payload);
                $model = $doc->fresh();
            } else {
                $payload['requested_by'] = Auth::id();
                $model = RoomConsumptionRequest::create($payload);
            }

            $this->syncConsumptionItems($model, $request->input('consumption', []));

            // Letter number: keep reserved while draft; mark used only on submit
            $newLetterId = ! empty($data['letter_number_id']) ? (int) $data['letter_number_id'] : null;
            if ($previousLetterId && $previousLetterId !== $newLetterId) {
                $this->releaseRcrLetterNumberIfOwned($previousLetterId, $model->id);
            }

            if ($newLetterId) {
                if ($submit) {
                    $this->markRcrLetterNumberUsed($model, $newLetterId);
                } else {
                    // Draft may have been marked used by older behavior — restore reserved
                    $this->releaseRcrLetterNumberIfOwned($newLetterId, $model->id);
                }
            } elseif ($previousLetterId) {
                $model->update([
                    'letter_number_id' => null,
                    'letter_number' => null,
                ]);
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

            if ($personalRegFlow && ! $submit) {
                if (! $doc) {
                    $redirect = route('room-consumption-requests.my-requests');
                    $message = 'Pengajuan Room & Consumption berhasil dikirim. Menunggu konfirmasi HR untuk penetapan nomor surat RCR.';
                } else {
                    $message = 'Pengajuan berhasil diperbarui.';
                }
            } elseif ($hrConfirmPending && $letterNumberString) {
                $message = 'Pengajuan karyawan dikonfirmasi. Reg. No resmi dan approver telah disimpan.';
            } else {
                $message = $submit
                    ? 'Request submitted for approval.'
                    : 'Draft saved.';
            }

            return redirect($redirect)->with('toast_success', $message);
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
            if ($model->isPendingHr()) {
                return back()->with('toast_error', 'Menunggu konfirmasi HR untuk nomor surat RCR sebelum submit approval.');
            }

            return back()->with('toast_error', 'Please select a letter number (RCR) before submitting.');
        }

        // Submit from detail/list does not change location/room/start date — skip conflict re-check
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
                    $model->start_date
                );
            }

            $model->update([
                'status' => RoomConsumptionRequest::STATUS_SUBMITTED,
                'submitted_at' => now(),
                'request_number' => $model->request_number,
            ]);

            $this->markRcrLetterNumberUsed($model, (int) $model->letter_number_id);

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

    /**
     * Room conflict check on submit: always for create; on edit only if location, room, or start date changed.
     */
    private function shouldCheckRcrRoomConflict(?RoomConsumptionRequest $doc, array $data): bool
    {
        if (! $doc) {
            return true;
        }

        $oldStart = $doc->start_date?->format('Y-m-d');

        return (int) $doc->project_id !== (int) $data['project_id']
            || (string) $doc->meeting_room_id !== (string) $data['meeting_room_id']
            || $oldStart !== (string) $data['start_date'];
    }

    /**
     * Mark letter number as used when RCR is submitted for approval.
     * Idempotent if already used by this same RCR; blocks if used by another document.
     */
    private function markRcrLetterNumberUsed(RoomConsumptionRequest $model, int $letterNumberId): void
    {
        $letter = LetterNumber::query()->lockForUpdate()->find($letterNumberId);
        if (! $letter) {
            throw new \RuntimeException('Letter number not found.');
        }

        if ($letter->status === 'cancelled') {
            throw new \RuntimeException('Selected letter number is cancelled.');
        }

        if ($letter->status === 'used'
            && $letter->related_document_id
            && (string) $letter->related_document_id !== (string) $model->id) {
            throw new \RuntimeException('Selected letter number is already used by another document.');
        }

        if ($letter->status === 'reserved'
            || ((string) $letter->related_document_id === (string) $model->id)) {
            $letter->markAsUsed('room_consumption_request', $model->id, Auth::id());
        } elseif ($letter->status !== 'used') {
            throw new \RuntimeException('Selected letter number is not available.');
        }

        // Keep RCR columns in sync with letter master
        if ((int) $model->letter_number_id !== (int) $letter->id || $model->letter_number !== $letter->letter_number) {
            $model->update([
                'letter_number_id' => $letter->id,
                'letter_number' => $letter->letter_number,
            ]);
        }
    }

    /**
     * Return previous letter to reserved if it was owned by this RCR (letter change on draft).
     */
    private function releaseRcrLetterNumberIfOwned(int $letterNumberId, string $rcrId): void
    {
        $letter = LetterNumber::query()->lockForUpdate()->find($letterNumberId);
        if (! $letter) {
            return;
        }

        if ($letter->status === 'used'
            && (string) $letter->related_document_id === (string) $rcrId) {
            $letter->update([
                'status' => 'reserved',
                'related_document_type' => null,
                'related_document_id' => null,
                'used_at' => null,
                'used_by' => null,
            ]);
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
        $data = $request->validate([
            'project_id' => ['required', 'exists:projects,id'],
            'meeting_room_id' => ['required', 'exists:meeting_rooms,id'],
            'department_id' => ['nullable', 'exists:departments,id'],
            'meeting_title' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i'],
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

        if ($data['start_date'] === $data['end_date'] && $data['end_time'] <= $data['start_time']) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'end_time' => ['End time must be after start time on the same day.'],
            ]);
        }

        return $data;
    }

    private function applyFilters($query, Request $request): void
    {
        if ($request->filled('project_id')) {
            $query->where('project_id', $request->project_id);
        }
        if ($request->filled('status')) {
            if ($request->status === 'pending_hr') {
                $query->where('submitted_by_user', true)
                    ->whereNull('letter_number_id')
                    ->where('status', RoomConsumptionRequest::STATUS_DRAFT);
            } else {
                $query->where('status', $request->status);
            }
        }
        if ($request->filled('date_from')) {
            $query->whereDate('end_date', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('start_date', '<=', $request->date_to);
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
            ->addColumn('request_number', fn ($row) => display_text($row->request_number ?: null))
            ->addColumn('project_label', fn ($row) => display_text(trim(($row->project->project_code ?? '').' - '.($row->project->project_name ?? ''), ' -')))
            ->addColumn('room_name', fn ($row) => display_text($row->meetingRoom->room_name ?? null))
            ->addColumn('meeting_date_fmt', fn ($row) => $row->formattedMeetingDateRangeHtml())
            ->addColumn('time_range', function ($row) {
                $s = $row->start_time ? Carbon::parse($row->start_time)->format('H:i') : '';
                $e = $row->end_time ? Carbon::parse($row->end_time)->format('H:i') : '';

                return e(trim("{$s} - {$e}", ' -'));
            })
            ->addColumn('status_badge', fn ($row) => $this->statusBadgeHtml($row))
            ->addColumn('requester', fn ($row) => display_text($row->requestedBy->name ?? null))
            ->addColumn('actions', function ($row) use ($isPersonal) {
                return $this->actionsHtml($row, $isPersonal);
            })
            ->rawColumns(['meeting_date_fmt', 'status_badge', 'actions'])
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
        if ($isPersonal) {
            if ($row->isPendingHr() && (int) $row->requested_by === (int) Auth::id()) {
                $html .= '<a href="'.$edit.'" class="btn btn-sm btn-warning mr-1" title="Edit"><i class="fas fa-edit"></i></a>';
            }
        } elseif ($row->canBeEditedBy(Auth::user())) {
            $html .= '<a href="'.$edit.'" class="btn btn-sm btn-warning mr-1" title="Edit"><i class="fas fa-edit"></i></a>';
        }
        if (! $isPersonal && $row->canSubmitForApproval() && $row->canBeEditedBy(Auth::user()) && ! $row->isPendingHr()) {
            $html .= '<form method="POST" action="'.$submit.'" class="d-inline mr-1" onsubmit="return confirm(\'Submit for approval?\');">'
                .csrf_field()
                .'<button type="submit" class="btn btn-sm btn-success" title="Submit"><i class="fas fa-paper-plane"></i></button></form>';
        }
        if (! $isPersonal && $row->canBeDeletedBy(Auth::user())) {
            $html .= '<form method="POST" action="'.$destroy.'" class="d-inline" onsubmit="return confirm(\'Delete this request?\');">'
                .csrf_field().method_field('DELETE')
                .'<button type="submit" class="btn btn-sm btn-danger" title="Delete"><i class="fas fa-trash"></i></button></form>';
        }
        $html .= '</div>';

        return $html;
    }

    private function statusBadgeHtml(RoomConsumptionRequest $row): string
    {
        if ($row->isPendingHr()) {
            return '<span class="badge badge-warning">Menunggu Konfirmasi HR</span>';
        }

        $status = $row->status;
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

    private function maxSubmittedByUserReqSequence(): int
    {
        $maxSeq = 0;
        $numbers = RoomConsumptionRequest::where('submitted_by_user', true)
            ->where('request_number', 'like', 'REQ%')
            ->pluck('request_number');
        foreach ($numbers as $num) {
            if (preg_match('/^REQ(\d+)$/', (string) $num, $m)) {
                $maxSeq = max($maxSeq, (int) $m[1]);
            }
        }

        return $maxSeq;
    }

    private function allocateNextSubmittedByUserReqNumber(): string
    {
        $sequence = $this->maxSubmittedByUserReqSequence() + 1;
        for ($attempt = 0; $attempt < 100; $attempt++, $sequence++) {
            $regNumber = 'REQ'.sprintf('%05d', $sequence);
            if (! RoomConsumptionRequest::where('request_number', $regNumber)->exists()) {
                return $regNumber;
            }
        }

        throw new \RuntimeException('Tidak dapat menghasilkan nomor REQ unik.');
    }

    private function acquireRcrRegSequenceLock(): bool
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return false;
        }
        $row = DB::selectOne('SELECT GET_LOCK(?, 30) AS acquired', ['rcr_submitted_by_user_reg_seq']);

        return $row && (int) $row->acquired === 1;
    }

    private function releaseRcrRegSequenceLock(): void
    {
        if (DB::connection()->getDriverName() !== 'mysql') {
            return;
        }
        DB::selectOne('SELECT RELEASE_LOCK(?) AS released', ['rcr_submitted_by_user_reg_seq']);
    }
}
