<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\LetterNumber;
use App\Models\Project;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\VehicleAssignment;
use App\Models\VehicleAssignmentPassenger;
use App\Models\VehicleAssignmentStop;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class VehicleAssignmentController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:vehicle-assignments.show')->only(['index', 'data', 'show']);
        $this->middleware('permission:vehicle-assignments.create')->only(['create', 'store']);
        $this->middleware('permission:vehicle-assignments.edit')->only(['edit', 'update', 'adjustDestinations', 'updateStops', 'close']);
        $this->middleware('permission:vehicle-assignments.delete')->only(['destroy']);
        $this->middleware('permission:vehicle-assignments.issue')->only(['issue']);
        $this->middleware('permission:vehicle-assignments.print')->only(['print']);
        $this->middleware('permission:vehicle-assignments.cancel')->only(['cancel']);

        $this->middleware('permission:personal.vehicle-assignments.view-own')->only([
            'myTrips', 'myTripsData', 'myTripsShow',
        ]);
        $this->middleware('permission:personal.vehicle-assignments.update-trip')->only([
            'myTripsStart', 'myTripsUpdateStops', 'myTripsAddStop', 'myTripsAdjustDestinations',
        ]);
        $this->middleware('permission:personal.vehicle-assignments.close-own')->only(['myTripsClose']);
    }

    public function index()
    {
        return view('vehicle-assignments.index', [
            'title' => 'Form of Assignment',
            'subtitle' => 'Vehicle trip assignments (FOA)',
            'vehicles' => Vehicle::orderBy('kode')->get(['id', 'kode', 'license_plate']),
        ]);
    }

    public function data(Request $request)
    {
        $query = VehicleAssignment::query()
            ->with(['vehicle', 'driver', 'requestor'])
            ->withCount(['stops', 'passengers'])
            ->orderByDesc('assignment_date')
            ->orderByDesc('form_number');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }
        if ($request->filled('from')) {
            $query->whereDate('assignment_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('assignment_date', '<=', $request->to);
        }
        if ($request->filled('search')) {
            $s = $request->search;
            $query->where(function ($q) use ($s) {
                $q->where('form_number', 'like', "%{$s}%")
                    ->orWhere('driver_name', 'like', "%{$s}%")
                    ->orWhere('license_plate', 'like', "%{$s}%")
                    ->orWhere('vehicle_kode', 'like', "%{$s}%")
                    ->orWhere('origin_destination', 'like', "%{$s}%")
                    ->orWhere('remarks', 'like', "%{$s}%");
            });
        }

        return datatables()->of($query)
            ->addIndexColumn()
            ->addColumn('date_fmt', fn (VehicleAssignment $row) => optional($row->assignment_date)->format('d M Y'))
            ->addColumn('vehicle_label', fn (VehicleAssignment $row) => e($row->vehicle_kode.' — '.$row->license_plate))
            ->addColumn('destinations', function (VehicleAssignment $row) {
                $row->loadMissing('stops');

                return e($row->destinationSummary());
            })
            ->addColumn('status_badge', function (VehicleAssignment $row) {
                return '<span class="badge badge-'.$row->statusBadgeClass().'">'.e($row->statusLabel()).'</span>';
            })
            ->addColumn('action', function (VehicleAssignment $row) {
                return view('vehicle-assignments.partials.action', ['model' => $row])->render();
            })
            ->rawColumns(['vehicle_label', 'status_badge', 'action', 'destinations'])
            ->toJson();
    }

    public function create()
    {
        return view('vehicle-assignments.create', array_merge(
            $this->formSharedData(),
            [
                'title' => 'Form of Assignment',
                'subtitle' => 'Create FOA',
                'assignment' => null,
            ]
        ));
    }

    public function store(Request $request)
    {
        $data = $this->validateHeader($request);
        [$originDest, $originManual, $destinations, $manualFlags] = $this->normalizeOriginAndDestinations($request);
        $passengers = $this->normalizePassengers($request);

        try {
            DB::beginTransaction();

            $vehicle = Vehicle::findOrFail($data['vehicle_id']);
            $employee = Employee::findOrFail($data['driver_employee_id']);
            $driverUserId = User::where('employee_id', $employee->id)->value('id');
            [$letter, $letterNumberString, $formNumber] = $this->resolveLetterAndFormNumber($data);

            $assignment = VehicleAssignment::create([
                'form_number' => $formNumber,
                'letter_number_id' => $letter->id,
                'letter_number' => $letterNumberString,
                'assignment_date' => $data['assignment_date'],
                'driver_employee_id' => $employee->id,
                'driver_name' => $employee->fullname,
                'driver_user_id' => $driverUserId,
                'origin_destination' => $originDest,
                'origin_is_manual' => $originManual,
                'remarks' => $data['remarks'] ?? null,
                'vehicle_id' => $vehicle->id,
                'vehicle_kode' => $vehicle->kode,
                'license_plate' => $vehicle->license_plate,
                'project_id' => $this->resolveProjectId($originDest, $originManual),
                'requested_by' => Auth::id(),
                'status' => VehicleAssignment::STATUS_DRAFT,
                'notes' => $data['notes'] ?? null,
            ]);

            // Draft: keep letter reserved (do not mark used until Issue)
            $this->releaseFoaLetterNumberIfOwned((int) $letter->id, $assignment->id);

            $this->syncPlannedStops($assignment, $originDest, $originManual, $destinations, $manualFlags);
            $this->syncPassengers($assignment, $passengers);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('toast_error', 'Save failed: '.$e->getMessage());
        }

        return redirect()
            ->route('vehicle-assignments.show', $assignment)
            ->with('toast_success', $assignment->form_number.' created as draft.');
    }

    public function show(VehicleAssignment $vehicleAssignment)
    {
        $vehicleAssignment->load([
            'stops',
            'passengers.employee.activeAdministration.position.department',
            'passengers.employee.activeAdministration.project',
            'vehicle',
            'driver',
            'requestor',
            'project',
        ]);

        return view('vehicle-assignments.show', [
            'title' => 'Form of Assignment',
            'subtitle' => $vehicleAssignment->form_number,
            'assignment' => $vehicleAssignment,
            'conflictWarning' => $this->softConflictMessage($vehicleAssignment),
            'canAdjustDestinations' => $vehicleAssignment->canAdjustDestinations()
                && Auth::user()->can('vehicle-assignments.edit'),
            'destinationProjects' => ($vehicleAssignment->canAdjustDestinations()
                && Auth::user()->can('vehicle-assignments.edit'))
                ? $this->activeProjectsForDestinationSelect()
                : collect(),
        ]);
    }

    public function edit(VehicleAssignment $vehicleAssignment)
    {
        if (! $vehicleAssignment->isHeaderEditable() && ! Auth::user()->can('vehicle-assignments.edit')) {
            abort(403);
        }
        if (! $vehicleAssignment->isHeaderEditable()) {
            return redirect()
                ->route('vehicle-assignments.show', $vehicleAssignment)
                ->with('toast_error', 'Only draft FOA can be edited.');
        }

        $vehicleAssignment->load(['stops', 'passengers']);

        return view('vehicle-assignments.edit', array_merge(
            $this->formSharedData(),
            [
                'title' => 'Form of Assignment',
                'subtitle' => 'Edit '.$vehicleAssignment->form_number,
                'assignment' => $vehicleAssignment,
            ]
        ));
    }

    public function update(Request $request, VehicleAssignment $vehicleAssignment)
    {
        if (! $vehicleAssignment->isHeaderEditable()) {
            return redirect()
                ->route('vehicle-assignments.show', $vehicleAssignment)
                ->with('toast_error', 'Only draft FOA can be edited.');
        }

        $data = $this->validateHeader($request);
        [$originDest, $originManual, $destinations, $manualFlags] = $this->normalizeOriginAndDestinations($request);
        $passengers = $this->normalizePassengers($request);

        try {
            DB::beginTransaction();

            $vehicle = Vehicle::findOrFail($data['vehicle_id']);
            $employee = Employee::findOrFail($data['driver_employee_id']);
            $driverUserId = User::where('employee_id', $employee->id)->value('id');

            $previousLetterId = $vehicleAssignment->letter_number_id
                ? (int) $vehicleAssignment->letter_number_id
                : null;
            [$letter, $letterNumberString, $formNumber] = $this->resolveLetterAndFormNumber(
                $data,
                $vehicleAssignment
            );

            $vehicleAssignment->update([
                'form_number' => $formNumber,
                'letter_number_id' => $letter->id,
                'letter_number' => $letterNumberString,
                'assignment_date' => $data['assignment_date'],
                'driver_employee_id' => $employee->id,
                'driver_name' => $employee->fullname,
                'driver_user_id' => $driverUserId,
                'origin_destination' => $originDest,
                'origin_is_manual' => $originManual,
                'remarks' => $data['remarks'] ?? null,
                'vehicle_id' => $vehicle->id,
                'vehicle_kode' => $vehicle->kode,
                'license_plate' => $vehicle->license_plate,
                'project_id' => $this->resolveProjectId($originDest, $originManual),
                'notes' => $data['notes'] ?? null,
            ]);

            $newLetterId = (int) $letter->id;
            if ($previousLetterId && $previousLetterId !== $newLetterId) {
                $this->releaseFoaLetterNumberIfOwned($previousLetterId, $vehicleAssignment->id);
            }
            // Draft: keep letter reserved
            $this->releaseFoaLetterNumberIfOwned($newLetterId, $vehicleAssignment->id);

            $this->syncPlannedStops($vehicleAssignment, $originDest, $originManual, $destinations, $manualFlags);
            $this->syncPassengers($vehicleAssignment, $passengers);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('toast_error', 'Update failed: '.$e->getMessage());
        }

        return redirect()
            ->route('vehicle-assignments.show', $vehicleAssignment)
            ->with('toast_success', 'FOA updated.');
    }

    public function destroy(VehicleAssignment $vehicleAssignment)
    {
        if ($vehicleAssignment->status !== VehicleAssignment::STATUS_DRAFT) {
            return redirect()
                ->route('vehicle-assignments.index')
                ->with('toast_error', 'Only draft FOA can be deleted.');
        }

        try {
            DB::beginTransaction();
            if ($vehicleAssignment->letter_number_id) {
                $this->releaseFoaLetterNumberIfOwned(
                    (int) $vehicleAssignment->letter_number_id,
                    $vehicleAssignment->id
                );
            }
            $vehicleAssignment->delete();
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return redirect()
                ->route('vehicle-assignments.index')
                ->with('toast_error', 'Delete failed: '.$e->getMessage());
        }

        return redirect()
            ->route('vehicle-assignments.index')
            ->with('toast_success', 'FOA deleted.');
    }

    public function issue(VehicleAssignment $vehicleAssignment)
    {
        if (! $vehicleAssignment->canIssue()) {
            return back()->with('toast_error', 'FOA cannot be issued from current status.');
        }

        $vehicleAssignment->load('stops');
        if ($vehicleAssignment->stops->where('stop_type', VehicleAssignmentStop::TYPE_DESTINATION)->isEmpty()) {
            return back()->with('toast_error', 'FOA needs at least one destination before issue.');
        }
        if (! $vehicleAssignment->origin_destination) {
            return back()->with('toast_error', 'FOA origin (lokasi awal) is required before issue.');
        }
        if (! $vehicleAssignment->letter_number_id) {
            return back()->with('toast_error', 'Please select a letter number (FOA) before issuing.');
        }

        try {
            DB::beginTransaction();

            $this->markFoaLetterNumberUsed($vehicleAssignment, (int) $vehicleAssignment->letter_number_id);

            $vehicleAssignment->update([
                'status' => VehicleAssignment::STATUS_ISSUED,
                'issued_at' => now(),
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('toast_error', 'Issue failed: '.$e->getMessage());
        }

        return redirect()
            ->route('vehicle-assignments.show', $vehicleAssignment)
            ->with('toast_success', 'FOA issued. Print and hand to the driver.');
    }

    public function cancel(VehicleAssignment $vehicleAssignment)
    {
        $user = Auth::user();
        $isAdmin = $user->can('vehicle-assignments.cancel');

        if ($vehicleAssignment->status === VehicleAssignment::STATUS_IN_PROGRESS && ! $isAdmin) {
            return back()->with('toast_error', 'Only admin can cancel an in-progress FOA.');
        }
        if (! $vehicleAssignment->canCancelByRequestor() && $vehicleAssignment->status !== VehicleAssignment::STATUS_IN_PROGRESS) {
            return back()->with('toast_error', 'FOA cannot be cancelled from current status.');
        }
        if (in_array($vehicleAssignment->status, [VehicleAssignment::STATUS_CLOSED, VehicleAssignment::STATUS_CANCELLED], true)) {
            return back()->with('toast_error', 'FOA is already closed or cancelled.');
        }

        try {
            DB::beginTransaction();

            if ($vehicleAssignment->letter_number_id) {
                $this->releaseFoaLetterNumberIfOwned(
                    (int) $vehicleAssignment->letter_number_id,
                    $vehicleAssignment->id
                );
            }

            $vehicleAssignment->update([
                'status' => VehicleAssignment::STATUS_CANCELLED,
                'notes' => trim(($vehicleAssignment->notes ? $vehicleAssignment->notes."\n" : '').'Cancelled by '.($user->name ?? $user->id).' at '.now()->toDateTimeString()),
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('toast_error', 'Cancel failed: '.$e->getMessage());
        }

        return redirect()
            ->route('vehicle-assignments.show', $vehicleAssignment)
            ->with('toast_success', 'FOA cancelled.');
    }

    /**
     * Add/edit unlocked destinations on issued / in-progress FOA (LOT itinerary-adjust pattern).
     */
    public function adjustDestinations(Request $request, VehicleAssignment $vehicleAssignment)
    {
        return $this->persistDestinationAdjust($request, $vehicleAssignment);
    }

    /**
     * Driver: same destination adjust form as FOA detail.
     */
    public function myTripsAdjustDestinations(Request $request, VehicleAssignment $vehicleAssignment)
    {
        $this->ensureDriverAccess($vehicleAssignment, true);

        return $this->persistDestinationAdjust($request, $vehicleAssignment);
    }

    protected function persistDestinationAdjust(Request $request, VehicleAssignment $vehicleAssignment)
    {
        if (! $vehicleAssignment->canAdjustDestinations()) {
            return back()->with('toast_error', 'Destinations can only be adjusted when FOA is issued or in progress.');
        }

        $request->validate([
            'stop_destinations' => ['required', 'array'],
            'stop_destinations_manual' => ['nullable', 'array'],
        ], [
            'stop_destinations.required' => 'Please add at least one destination.',
        ]);

        $raw = $request->input('stop_destinations', []);
        $rawManual = $request->input('stop_destinations_manual', []);
        if (! is_array($raw)) {
            $raw = [];
        }
        if (! is_array($rawManual)) {
            $rawManual = [];
        }

        $list = [];
        $flags = [];
        foreach ($raw as $i => $dest) {
            $d = $this->collapseWhitespace((string) $dest);
            if ($d === '') {
                continue;
            }
            if (mb_strlen($d) < 3) {
                return back()->withInput()->with('toast_error', 'Each destination must be at least 3 characters.');
            }
            $manual = (string) ($rawManual[$i] ?? '0') === '1';
            if (! $manual && ! $this->projectLabelExists($d)) {
                return back()->withInput()->with('toast_error', 'Selected project destination is invalid: '.$d);
            }
            $list[] = $d;
            $flags[] = $manual;
        }

        if ($list === []) {
            return back()->withInput()->with('toast_error', 'Please add at least one destination.');
        }

        if (count($list) < $vehicleAssignment->lockedDestinationStopCount()) {
            return back()->withInput()->with(
                'toast_error',
                'Tidak bisa menghapus tujuan yang sudah terisi jam/KM. Refresh halaman jika trip berubah.'
            );
        }

        try {
            DB::beginTransaction();
            $vehicleAssignment->replaceDestinationsKeepingLocked($list, $flags);
            DB::commit();
        } catch (\InvalidArgumentException $e) {
            DB::rollBack();

            return back()->withInput()->with('toast_error', $e->getMessage());
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('toast_error', 'Save failed: '.$e->getMessage());
        }

        return back()->with('toast_success', 'Destinations updated.');
    }

    public function print(VehicleAssignment $vehicleAssignment)
    {
        $vehicleAssignment->load([
            'stops',
            'passengers.employee.activeAdministration.position.department',
            'passengers.employee.activeAdministration.project',
            'vehicle',
            'driver',
            'requestor',
        ]);

        return view('vehicle-assignments.print', [
            'doc' => $vehicleAssignment,
        ]);
    }

    // ----- Driver My Trips -----

    public function myTrips()
    {
        return view('vehicle-assignments.my-trips', [
            'title' => 'My Form of Assignment',
            'subtitle' => 'Trips assigned to you as driver',
        ]);
    }

    public function myTripsData(Request $request)
    {
        $employeeId = Auth::user()?->employee_id;
        $userId = Auth::id();

        $query = VehicleAssignment::query()
            ->with(['vehicle'])
            ->where(function ($q) use ($employeeId, $userId) {
                if ($employeeId) {
                    $q->where('driver_employee_id', $employeeId);
                }
                $q->orWhere('driver_user_id', $userId);
            })
            ->whereIn('status', [
                VehicleAssignment::STATUS_ISSUED,
                VehicleAssignment::STATUS_IN_PROGRESS,
                VehicleAssignment::STATUS_CLOSED,
            ])
            ->orderByDesc('assignment_date')
            ->orderByDesc('form_number');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return datatables()->of($query)
            ->addIndexColumn()
            ->addColumn('date_fmt', fn (VehicleAssignment $row) => optional($row->assignment_date)->format('d M Y'))
            ->addColumn('vehicle_label', fn (VehicleAssignment $row) => e($row->vehicle_kode.' — '.$row->license_plate))
            ->addColumn('status_badge', function (VehicleAssignment $row) {
                return '<span class="badge badge-'.$row->statusBadgeClass().'">'.e($row->statusLabel()).'</span>';
            })
            ->addColumn('action', function (VehicleAssignment $row) {
                $url = route('vehicle-assignments.my-trips.show', $row);

                return '<a href="'.$url.'" class="btn btn-sm btn-info"><i class="fas fa-eye"></i></a>';
            })
            ->rawColumns(['vehicle_label', 'status_badge', 'action'])
            ->toJson();
    }

    public function myTripsShow(VehicleAssignment $vehicleAssignment)
    {
        $this->ensureDriverAccess($vehicleAssignment);
        $vehicleAssignment->load([
            'stops',
            'passengers.employee.activeAdministration.position.department',
            'passengers.employee.activeAdministration.project',
            'vehicle',
            'requestor',
        ]);

        return view('vehicle-assignments.my-show', [
            'title' => 'My Form of Assignment',
            'subtitle' => $vehicleAssignment->form_number,
            'assignment' => $vehicleAssignment,
            'destinationProjects' => $this->activeProjectsForDestinationSelect(),
            'canAdjustDestinations' => $vehicleAssignment->canAdjustDestinations()
                && Auth::user()->can('personal.vehicle-assignments.update-trip'),
        ]);
    }

    public function myTripsStart(Request $request, VehicleAssignment $vehicleAssignment)
    {
        $this->ensureDriverAccess($vehicleAssignment, true);

        if (! $vehicleAssignment->canStart()) {
            return back()->with('toast_error', 'Trip can only be started when FOA is issued.');
        }

        $data = $request->validate([
            'depart_time' => ['required', 'date_format:H:i'],
            'depart_km' => ['required', 'integer', 'min:0'],
        ]);

        $vehicleAssignment->load('stops');
        $firstLeg = $vehicleAssignment->firstLeg();
        if (! $firstLeg) {
            return back()->with('toast_error', 'No destination leg found. Add at least one destination.');
        }

        // First leg "Berangkat" = leave origin (header) toward first destination.
        $firstLeg->update([
            'depart_time' => $data['depart_time'],
            'depart_km' => $data['depart_km'],
        ]);

        $vehicleAssignment->update([
            'status' => VehicleAssignment::STATUS_IN_PROGRESS,
            'started_at' => now(),
        ]);

        return back()->with('toast_success', 'Trip started (berangkat dari origin). Isi jam/KM tiba & leg berikutnya.');
    }

    public function myTripsUpdateStops(Request $request, VehicleAssignment $vehicleAssignment)
    {
        $this->ensureDriverAccess($vehicleAssignment, true);

        return $this->persistTripStopTimes($request, $vehicleAssignment);
    }

    /**
     * HR/admin update jam & KM on FOA detail (same fields as driver My FOA).
     */
    public function updateStops(Request $request, VehicleAssignment $vehicleAssignment)
    {
        return $this->persistTripStopTimes($request, $vehicleAssignment);
    }

    protected function persistTripStopTimes(Request $request, VehicleAssignment $vehicleAssignment)
    {
        if (! $vehicleAssignment->canUpdateTrip() || $vehicleAssignment->status === VehicleAssignment::STATUS_ISSUED) {
            return back()->with('toast_error', 'Start the trip before updating jam/KM.');
        }

        $stopsInput = $request->input('stops', []);
        if (is_array($stopsInput)) {
            foreach ($stopsInput as $i => $row) {
                foreach (['depart_time', 'arrive_time', 'depart_km', 'arrive_km'] as $field) {
                    if (isset($row[$field]) && $row[$field] === '') {
                        $stopsInput[$i][$field] = null;
                    }
                }
            }
            $request->merge(['stops' => $stopsInput]);
        }

        $payload = $request->validate([
            'stops' => ['required', 'array', 'min:1'],
            'stops.*.id' => ['required', 'uuid', 'exists:vehicle_assignment_stops,id'],
            'stops.*.depart_time' => ['nullable', 'date_format:H:i'],
            'stops.*.depart_km' => ['nullable', 'integer', 'min:0'],
            'stops.*.arrive_time' => ['nullable', 'date_format:H:i'],
            'stops.*.arrive_km' => ['nullable', 'integer', 'min:0'],
        ]);

        $vehicleAssignment->load('stops');
        $byId = $vehicleAssignment->stops->keyBy('id');

        foreach ($payload['stops'] as $row) {
            $stop = $byId->get($row['id']);
            if (! $stop || $stop->assignment_id !== $vehicleAssignment->id) {
                continue;
            }
            $stop->update([
                'depart_time' => $row['depart_time'] ?? null,
                'depart_km' => $row['depart_km'] ?? null,
                'arrive_time' => $row['arrive_time'] ?? null,
                'arrive_km' => $row['arrive_km'] ?? null,
            ]);
        }

        $vehicleAssignment->load('stops');
        $this->assertKmNonDecreasing($vehicleAssignment);

        return back()->with('toast_success', 'Trip times/KM updated.');
    }

    public function myTripsAddStop(Request $request, VehicleAssignment $vehicleAssignment)
    {
        $this->ensureDriverAccess($vehicleAssignment, true);

        if (! in_array($vehicleAssignment->status, [VehicleAssignment::STATUS_ISSUED, VehicleAssignment::STATUS_IN_PROGRESS], true)) {
            return back()->with('toast_error', 'Cannot add stops in current status.');
        }

        $data = $request->validate([
            'stop_type' => ['required', 'in:destination,return'],
            'destination' => ['required', 'string', 'min:3', 'max:255'],
            'is_manual' => ['nullable'],
        ]);

        $isManual = $request->boolean('is_manual');
        $destination = $this->collapseWhitespace($data['destination']);

        if (! $isManual && ! $this->projectLabelExists($destination)) {
            throw ValidationException::withMessages([
                'destination' => 'Selected project destination is invalid.',
            ]);
        }

        if ($data['stop_type'] === VehicleAssignmentStop::TYPE_RETURN
            && $vehicleAssignment->stops()->where('stop_type', VehicleAssignmentStop::TYPE_RETURN)->exists()) {
            return back()->with('toast_error', 'Return stop already exists.');
        }

        $maxSeq = (int) $vehicleAssignment->stops()
            ->where('stop_type', '!=', VehicleAssignmentStop::TYPE_ORIGIN)
            ->max('sequence');

        VehicleAssignmentStop::create([
            'assignment_id' => $vehicleAssignment->id,
            'sequence' => $maxSeq + 1,
            'stop_type' => $data['stop_type'],
            'destination' => $destination,
            'is_manual' => $isManual,
            'created_by' => Auth::id(),
        ]);

        return back()->with('toast_success', 'Trip leg added.');
    }

    /**
     * Admin/HR: close FOA at origin from detail page (same rules as driver).
     */
    public function close(Request $request, VehicleAssignment $vehicleAssignment)
    {
        return $this->persistCloseAtOrigin(
            $request,
            $vehicleAssignment,
            route('vehicle-assignments.show', $vehicleAssignment)
        );
    }

    public function myTripsClose(Request $request, VehicleAssignment $vehicleAssignment)
    {
        $this->ensureDriverAccess($vehicleAssignment, true);

        return $this->persistCloseAtOrigin(
            $request,
            $vehicleAssignment,
            route('vehicle-assignments.my-trips.show', $vehicleAssignment)
        );
    }

    protected function persistCloseAtOrigin(Request $request, VehicleAssignment $vehicleAssignment, string $redirectUrl)
    {
        if (! $vehicleAssignment->canClose()) {
            return back()->with('toast_error', 'FOA can only be closed while in progress.');
        }

        $data = $request->validate([
            'arrive_time' => ['required', 'date_format:H:i'],
            'arrive_km' => ['required', 'integer', 'min:0'],
        ]);

        $vehicleAssignment->load(['stops', 'vehicle']);
        $return = $vehicleAssignment->returnStop();

        if (! $return) {
            $legs = $vehicleAssignment->tripLegs();
            $maxSeq = (int) ($legs->max('sequence') ?? -1);
            $return = VehicleAssignmentStop::create([
                'assignment_id' => $vehicleAssignment->id,
                'sequence' => $maxSeq + 1,
                'stop_type' => VehicleAssignmentStop::TYPE_RETURN,
                'destination' => $vehicleAssignment->origin_destination,
                'is_manual' => $vehicleAssignment->origin_is_manual,
                'created_by' => Auth::id(),
            ]);
        }

        $return->update([
            'arrive_time' => $data['arrive_time'],
            'arrive_km' => $data['arrive_km'],
        ]);

        $vehicleAssignment->load('stops');
        $this->assertKmNonDecreasing($vehicleAssignment);

        $vehicleAssignment->update([
            'status' => VehicleAssignment::STATUS_CLOSED,
            'closed_at' => now(),
            'closed_by' => Auth::id(),
        ]);

        $vehicle = $vehicleAssignment->vehicle;
        if ($vehicle && (int) $data['arrive_km'] > (int) $vehicle->odometer) {
            $vehicle->update(['odometer' => (int) $data['arrive_km']]);
        }

        return redirect($redirectUrl)
            ->with('toast_success', 'FOA closed at origin. Vehicle odometer updated if KM increased.');
    }

    // ----- Helpers -----

    protected function formSharedData(): array
    {
        $employees = Employee::query()
            ->whereHas('administrations', fn ($q) => $q->where('is_active', 1))
            ->with(['administrations' => fn ($q) => $q->where('is_active', 1)])
            ->orderBy('fullname')
            ->get();

        $vehicles = Vehicle::where('status', 'active')
            ->orderBy('kode')
            ->get(['id', 'kode', 'license_plate', 'lokasi', 'odometer', 'project_code']);

        return [
            'employees' => $employees,
            'vehicles' => $vehicles,
            'destinationProjects' => $this->activeProjectsForDestinationSelect(),
        ];
    }

    protected function activeProjectsForDestinationSelect()
    {
        return Project::query()
            ->where('project_status', 1)
            ->orderBy('project_code')
            ->get();
    }

    protected function validateHeader(Request $request): array
    {
        return $request->validate([
            'letter_number_id' => ['required', 'integer', 'exists:letter_numbers,id'],
            'assignment_date' => ['required', 'date'],
            'driver_employee_id' => ['required', 'uuid', 'exists:employees,id'],
            'vehicle_id' => ['required', 'uuid', 'exists:vehicles,id'],
            'remarks' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'origin_destination' => ['required', 'string', 'min:3', 'max:255'],
            'origin_is_manual' => ['nullable', 'in:0,1'],
            'stop_destinations' => ['required', 'array', 'min:1'],
            'stop_destinations.*' => ['required', 'string', 'min:3', 'max:255'],
            'stop_destinations_manual' => ['nullable', 'array'],
            'passengers' => ['nullable', 'array'],
            'passengers.*.passenger_name' => ['nullable', 'string', 'max:255'],
            'passengers.*.employee_id' => ['nullable', 'uuid', 'exists:employees,id'],
        ], [
            'letter_number_id.required' => 'Please select a letter number (FOA).',
            'stop_destinations.required' => 'At least one destination is required.',
            'stop_destinations.*.min' => 'Each destination must be at least 3 characters.',
            'origin_destination.required' => 'Origin location is required.',
        ]);
    }

    /**
     * @return array{0:LetterNumber,1:string,2:string}
     */
    protected function resolveLetterAndFormNumber(array $data, ?VehicleAssignment $doc = null): array
    {
        $letter = LetterNumber::with('project')->findOrFail($data['letter_number_id']);
        $ownedByThis = $doc && (string) $letter->related_document_id === (string) $doc->id;

        if ($doc && (int) $doc->letter_number_id === (int) $letter->id) {
            // Same letter already on this draft
        } elseif ($letter->status === 'reserved') {
            // Available
        } elseif ($ownedByThis) {
            // Used/reserved by this FOA
        } else {
            throw ValidationException::withMessages([
                'letter_number_id' => 'Selected letter number is not available.',
            ]);
        }

        $letterNumberString = $letter->letter_number;
        $formNumber = VehicleAssignment::formatFormNumber($letterNumberString);

        $dup = VehicleAssignment::query()
            ->where('form_number', $formNumber)
            ->when($doc, fn ($q) => $q->where('id', '!=', $doc->id))
            ->exists();
        if ($dup) {
            throw ValidationException::withMessages([
                'letter_number_id' => 'FOA No dari surat ini sudah digunakan: '.$formNumber,
            ]);
        }

        return [$letter, $letterNumberString, $formNumber];
    }

    protected function markFoaLetterNumberUsed(VehicleAssignment $model, int $letterNumberId): void
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
            $letter->markAsUsed('vehicle_assignment', $model->id, Auth::id());
        } elseif ($letter->status !== 'used') {
            throw new \RuntimeException('Selected letter number is not available.');
        }

        if ((int) $model->letter_number_id !== (int) $letter->id || $model->letter_number !== $letter->letter_number) {
            $model->update([
                'letter_number_id' => $letter->id,
                'letter_number' => $letter->letter_number,
            ]);
        }
    }

    protected function releaseFoaLetterNumberIfOwned(int $letterNumberId, string $assignmentId): void
    {
        $letter = LetterNumber::query()->lockForUpdate()->find($letterNumberId);
        if (! $letter) {
            return;
        }

        if ($letter->status === 'used'
            && (string) $letter->related_document_id === (string) $assignmentId) {
            $letter->update([
                'status' => 'reserved',
                'related_document_type' => null,
                'related_document_id' => null,
                'used_at' => null,
                'used_by' => null,
            ]);
        }
    }

    /**
     * @return array{0:string,1:bool,2:array<int,string>,3:array<int,bool>}
     */
    protected function normalizeOriginAndDestinations(Request $request): array
    {
        $originDest = $this->collapseWhitespace((string) $request->input('origin_destination', ''));
        $originManual = (string) $request->input('origin_is_manual', '0') === '1';

        if ($originDest === '') {
            throw ValidationException::withMessages([
                'origin_destination' => 'Origin location is required.',
            ]);
        }
        if (! $originManual && ! $this->projectLabelExists($originDest)) {
            throw ValidationException::withMessages([
                'origin_destination' => 'Selected origin project is invalid.',
            ]);
        }

        $raw = $request->input('stop_destinations', []);
        $rawManual = $request->input('stop_destinations_manual', []);
        if (! is_array($raw)) {
            $raw = [];
        }
        if (! is_array($rawManual)) {
            $rawManual = [];
        }

        $destinations = [];
        $manualFlags = [];
        foreach ($raw as $i => $dest) {
            $label = $this->collapseWhitespace((string) $dest);
            if ($label === '') {
                continue;
            }
            $manual = (string) ($rawManual[$i] ?? '0') === '1';
            if (! $manual && ! $this->projectLabelExists($label)) {
                throw ValidationException::withMessages([
                    "stop_destinations.$i" => 'Selected project destination is invalid.',
                ]);
            }
            if (mb_strlen($label) < 3) {
                throw ValidationException::withMessages([
                    "stop_destinations.$i" => 'Each destination must be at least 3 characters.',
                ]);
            }
            $destinations[] = $label;
            $manualFlags[] = $manual;
        }

        if ($destinations === []) {
            throw ValidationException::withMessages([
                'stop_destinations' => 'At least one destination is required.',
            ]);
        }

        return [$originDest, $originManual, $destinations, $manualFlags];
    }

    protected function normalizePassengers(Request $request): array
    {
        $rows = $request->input('passengers', []);
        if (! is_array($rows)) {
            return [];
        }

        $out = [];
        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }
            $name = $this->collapseWhitespace((string) ($row['passenger_name'] ?? ''));
            $employeeId = $row['employee_id'] ?? null;
            if ($employeeId) {
                $emp = Employee::find($employeeId);
                if ($emp) {
                    $name = $name !== '' ? $name : $emp->fullname;
                    $out[] = ['employee_id' => $emp->id, 'passenger_name' => $name];

                    continue;
                }
            }
            if ($name !== '') {
                $out[] = ['employee_id' => null, 'passenger_name' => $name];
            }
        }

        return $out;
    }

    protected function syncPlannedStops(
        VehicleAssignment $assignment,
        string $originDest,
        bool $originManual,
        array $destinations,
        array $manualFlags
    ): void {
        // Origin stays on assignment header only — trip legs are destinations (+ optional return later).
        unset($originDest, $originManual);

        $assignment->stops()->delete();

        $seq = 0;
        foreach ($destinations as $i => $dest) {
            VehicleAssignmentStop::create([
                'assignment_id' => $assignment->id,
                'sequence' => $seq++,
                'stop_type' => VehicleAssignmentStop::TYPE_DESTINATION,
                'destination' => $dest,
                'is_manual' => (bool) ($manualFlags[$i] ?? false),
                'created_by' => Auth::id(),
            ]);
        }
    }

    protected function syncPassengers(VehicleAssignment $assignment, array $passengers): void
    {
        $assignment->passengers()->delete();
        foreach ($passengers as $i => $row) {
            VehicleAssignmentPassenger::create([
                'assignment_id' => $assignment->id,
                'employee_id' => $row['employee_id'] ?? null,
                'passenger_name' => $row['passenger_name'],
                'sort_order' => $i,
            ]);
        }
    }

    protected function resolveProjectId(string $destination, bool $isManual): ?int
    {
        if ($isManual) {
            return null;
        }
        $code = trim(explode(' - ', $destination, 2)[0] ?? '');
        if ($code === '') {
            return null;
        }

        return Project::where('project_code', $code)->value('id');
    }

    protected function projectLabelExists(string $label): bool
    {
        foreach ($this->activeProjectsForDestinationSelect() as $project) {
            if ($label === $project->project_code.' - '.$project->project_name) {
                return true;
            }
        }

        return false;
    }

    protected function collapseWhitespace(string $value): string
    {
        return trim(preg_replace('/\s+/u', ' ', $value) ?? $value);
    }

    protected function softConflictMessage(VehicleAssignment $assignment): ?string
    {
        if (in_array($assignment->status, [VehicleAssignment::STATUS_CLOSED, VehicleAssignment::STATUS_CANCELLED], true)) {
            return null;
        }

        $others = VehicleAssignment::query()
            ->where('vehicle_id', $assignment->vehicle_id)
            ->whereDate('assignment_date', $assignment->assignment_date)
            ->where('id', '!=', $assignment->id)
            ->whereIn('status', [
                VehicleAssignment::STATUS_ISSUED,
                VehicleAssignment::STATUS_IN_PROGRESS,
                VehicleAssignment::STATUS_DRAFT,
            ])
            ->pluck('form_number');

        if ($others->isEmpty()) {
            return null;
        }

        return 'Warning: vehicle already has FOA on this date: '.$others->implode(', ');
    }

    protected function ensureDriverAccess(VehicleAssignment $assignment, bool $write = false): void
    {
        $user = Auth::user();
        if ($user->can('vehicle-assignments.show') && ! $write) {
            return;
        }
        if ($user->can('vehicle-assignments.edit') && $write) {
            // Admin may assist driver updates
            return;
        }

        $employeeId = $user->employee_id;
        $ok = ($employeeId && $assignment->driver_employee_id === $employeeId)
            || ((int) $assignment->driver_user_id === (int) $user->id);

        if (! $ok) {
            abort(403, 'You are not the assigned driver for this FOA.');
        }
    }

    protected function assertKmNonDecreasing(VehicleAssignment $assignment): void
    {
        $last = null;
        foreach ($assignment->tripLegs() as $stop) {
            foreach (['depart_km', 'arrive_km'] as $field) {
                $val = $stop->{$field};
                if ($val === null) {
                    continue;
                }
                if ($last !== null && (int) $val < (int) $last) {
                    throw ValidationException::withMessages([
                        'stops' => 'KM values must not decrease along the trip (found '.$val.' after '.$last.').',
                    ]);
                }
                $last = (int) $val;
            }
        }
    }
}
