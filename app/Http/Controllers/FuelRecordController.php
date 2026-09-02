<?php

namespace App\Http\Controllers;

use App\Models\FuelRecord;
use App\Models\Vehicle;
use App\Services\FuelReceiptDuplicateChecker;
use App\Services\OpenRouterReceiptParser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class FuelRecordController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:fuel-records.show')->only(['index', 'data', 'show']);
        $this->middleware('permission:fuel-records.create')->only(['create', 'store']);
        $this->middleware('permission:fuel-records.edit')->only(['edit', 'update']);
        $this->middleware('permission:fuel-records.delete')->only(['destroy']);
        $this->middleware('permission:fuel-records.verify')->only(['pendingVerification', 'pendingVerificationData', 'verify', 'reject']);
        $this->middleware('permission:personal.fuel.view-own')->only(['myRequests', 'myRequestsShow']);
        $this->middleware('permission:personal.fuel.create-own')->only([
            'myRequestsCreate', 'myRequestsStore', 'myRequestsParseReceipt', 'myReceiptTemp',
        ]);
        $this->middleware('permission:personal.fuel.edit-own')->only(['myRequestsEdit', 'myRequestsUpdate']);
        // Receipt image: ownership or office fuel-records.show (checked in method)
        $this->middleware('auth')->only(['myReceipt']);
    }

    public function index()
    {
        $title = 'Fuel Records';
        $subtitle = 'Vehicle fuel refill records';
        $vehicles = Vehicle::orderBy('kode')->get(['id', 'kode', 'license_plate']);

        return view('fuel-records.index', compact('title', 'subtitle', 'vehicles'));
    }

    public function data(Request $request)
    {
        $query = FuelRecord::query()
            ->with(['vehicle', 'driver'])
            ->orderByDesc('fuel_date');

        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->vehicle_id);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('from')) {
            $query->whereDate('fuel_date', '>=', $request->from);
        }
        if ($request->filled('to')) {
            $query->whereDate('fuel_date', '<=', $request->to);
        }

        return datatables()->of($query)
            ->addIndexColumn()
            ->addColumn('vehicle_label', function (FuelRecord $row) {
                $v = $row->vehicle;

                return $v ? e($v->kode.' — '.$v->license_plate) : '—';
            })
            ->addColumn('status_badge', function (FuelRecord $row) {
                return $this->statusBadgeHtml($row->status);
            })
            ->addColumn('fuel_date_fmt', fn (FuelRecord $row) => optional($row->fuel_date)->format('Y-m-d'))
            ->addColumn('quantity_fmt', fn (FuelRecord $row) => number_format((float) $row->quantity, 2))
            ->addColumn('total_cost_fmt', fn (FuelRecord $row) => number_format((float) $row->total_cost, 0, ',', '.'))
            ->addColumn('action', function (FuelRecord $model) {
                return view('fuel-records.action', compact('model'))->render();
            })
            ->rawColumns(['vehicle_label', 'status_badge', 'action'])
            ->toJson();
    }

    public function create(Request $request)
    {
        $title = 'Fuel Records';
        $subtitle = 'Add Fuel Record';
        $vehicles = Vehicle::where('status', 'active')->orderBy('kode')->get(['id', 'kode', 'license_plate', 'fuel_type', 'odometer']);
        $selectedVehicleId = $request->get('vehicle_id');

        return view('fuel-records.create', compact('title', 'subtitle', 'vehicles', 'selectedVehicleId'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        try {
            DB::beginTransaction();
            $data['total_cost'] = round((float) $data['quantity'] * (float) $data['price_per_liter'], 2);
            $this->assertReceiptNotDuplicate($data);
            $data['created_by'] = Auth::id();
            $data['status'] = FuelRecord::STATUS_VERIFIED;
            $data['verified_by'] = Auth::id();
            $data['verified_at'] = now();
            if ($request->hasFile('receipt_image')) {
                $data['receipt_image'] = $this->storeReceipt($request->file('receipt_image'));
            }
            $record = FuelRecord::create($data);
            $this->bumpOdometer($data['vehicle_id'], (int) $data['odometer']);
            DB::commit();

            return redirect()->route('fuel-records.index')
                ->with('toast_success', 'Fuel record added successfully.');
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('toast_error', 'Failed to add fuel record: '.$e->getMessage());
        }
    }

    public function show(FuelRecord $fuelRecord)
    {
        $title = 'Fuel Records';
        $subtitle = 'Fuel Record Detail';
        $fuelRecord->load(['vehicle', 'driver', 'creator', 'verifier', 'claim']);

        return view('fuel-records.show', compact('title', 'subtitle', 'fuelRecord'));
    }

    public function edit(FuelRecord $fuelRecord)
    {
        $title = 'Fuel Records';
        $subtitle = 'Edit Fuel Record';
        $vehicles = Vehicle::orderBy('kode')->get(['id', 'kode', 'license_plate', 'fuel_type', 'odometer']);
        $fuelRecord->load('vehicle');

        return view('fuel-records.edit', compact('title', 'subtitle', 'fuelRecord', 'vehicles'));
    }

    public function update(Request $request, FuelRecord $fuelRecord)
    {
        if ($fuelRecord->status === FuelRecord::STATUS_CLAIMED) {
            return back()->with('toast_error', 'Claimed fuel records cannot be edited.');
        }

        $data = $this->validated($request);

        try {
            DB::beginTransaction();
            $data['total_cost'] = round((float) $data['quantity'] * (float) $data['price_per_liter'], 2);
            $this->assertReceiptNotDuplicate($data, $fuelRecord->id);
            if ($request->hasFile('receipt_image')) {
                $this->deleteReceipt($fuelRecord->receipt_image);
                $data['receipt_image'] = $this->storeReceipt($request->file('receipt_image'));
            }
            $fuelRecord->update($data);
            $this->bumpOdometer($data['vehicle_id'], (int) $data['odometer']);
            DB::commit();

            return redirect()->route('fuel-records.index')
                ->with('toast_success', 'Fuel record updated successfully.');
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('toast_error', 'Failed to update fuel record: '.$e->getMessage());
        }
    }

    public function destroy(FuelRecord $fuelRecord)
    {
        if ($fuelRecord->status === FuelRecord::STATUS_CLAIMED) {
            return back()->with('toast_error', 'Claimed fuel records cannot be deleted.');
        }

        try {
            DB::beginTransaction();
            // File cleanup runs via FuelRecord::deleting (deleteReceiptFile)
            $fuelRecord->delete();
            DB::commit();

            return redirect()->route('fuel-records.index')
                ->with('toast_success', 'Fuel record deleted successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('toast_error', 'Failed to delete fuel record: '.$e->getMessage());
        }
    }

    /* ------------------------------------------------------------------ */
    /* Driver My Features                                                  */
    /* ------------------------------------------------------------------ */

    public function myRequests()
    {
        $title = 'My Fuel Log';
        $subtitle = 'Scan SPBU receipts or enter manually';
        $records = FuelRecord::query()
            ->with('vehicle')
            ->where(function ($q) {
                $q->where('created_by', Auth::id());
                if (Auth::user()?->employee_id) {
                    $q->orWhere('driver_id', Auth::user()->employee_id);
                }
            })
            ->orderByDesc('fuel_date')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('fuel-records.my-requests', compact('title', 'subtitle', 'records'));
    }

    public function myRequestsCreate(OpenRouterReceiptParser $parser)
    {
        $title = 'Log Fuel';
        $subtitle = 'Scan receipt or enter manually';
        $vehicles = Vehicle::where('status', 'active')->orderBy('kode')->get(['id', 'kode', 'license_plate', 'odometer']);
        $aiEnabled = $parser->isConfigured();

        return view('fuel-records.my-create', compact('title', 'subtitle', 'vehicles', 'aiEnabled'));
    }

    public function myRequestsParseReceipt(Request $request, OpenRouterReceiptParser $parser)
    {
        $request->validate([
            'receipt_image' => ['required', 'file', 'max:8192', 'mimes:jpg,jpeg,png,webp'],
        ]);

        if (! $parser->isConfigured()) {
            return response()->json(['success' => false, 'message' => 'AI parsing is not configured. Use manual entry.'], 422);
        }

        $file = $request->file('receipt_image');
        $path = $this->storeReceipt($file);
        $absolute = Storage::disk('private')->path($path);
        $result = $parser->parseFromPath($absolute, $file->getMimeType());

        if (! ($result['success'] ?? false)) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? 'Parse failed',
                'receipt_path' => $path,
            ], 422);
        }

        $data = $result['data'];
        $data['receipt_path'] = $path;
        $data['receipt_url'] = route('fuel-records.my-requests.receipt-temp', ['path' => encrypt($path)]);

        return response()->json(['success' => true, 'data' => $data]);
    }

    /** Temporary preview of an uploaded receipt path (encrypted). */
    public function myReceiptTemp(Request $request)
    {
        try {
            $path = decrypt($request->query('path'));
        } catch (\Throwable) {
            abort(404);
        }

        if (! Storage::disk('private')->exists($path)) {
            abort(404);
        }

        return Storage::disk('private')->response($path);
    }

    public function myRequestsStore(Request $request)
    {
        $data = $this->validatedDriver($request);

        try {
            DB::beginTransaction();

            $data['total_cost'] = $this->resolveTotal($data);
            $this->assertReceiptNotDuplicate($data);
            $data['created_by'] = Auth::id();
            $data['driver_id'] = Auth::user()?->employee_id;
            $data['status'] = FuelRecord::STATUS_SUBMITTED;

            if ($request->filled('receipt_path')) {
                $data['receipt_image'] = $request->input('receipt_path');
            } elseif ($request->hasFile('receipt_image')) {
                $data['receipt_image'] = $this->storeReceipt($request->file('receipt_image'));
            }

            if ($request->filled('ai_raw_json')) {
                $raw = json_decode($request->input('ai_raw_json'), true);
                $data['ai_raw_json'] = is_array($raw) ? $raw : null;
                $data['ai_parsed_at'] = now();
                $data['ai_model'] = $request->input('ai_model');
            }

            unset($data['receipt_path']);
            $record = FuelRecord::create($data);
            $this->bumpOdometer($data['vehicle_id'], (int) $data['odometer']);
            DB::commit();

            return redirect()->route('fuel-records.my-requests')
                ->with('toast_success', 'Fuel log submitted for verification.');
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('toast_error', 'Failed to submit: '.$e->getMessage());
        }
    }

    public function myRequestsShow(FuelRecord $fuelRecord)
    {
        $this->assertOwnFuel($fuelRecord);
        $title = 'My Fuel Log';
        $subtitle = 'Detail';
        $fuelRecord->load('vehicle');

        return view('fuel-records.my-show', compact('title', 'subtitle', 'fuelRecord'));
    }

    public function myRequestsEdit(FuelRecord $fuelRecord)
    {
        $this->assertOwnFuel($fuelRecord);
        if (! $fuelRecord->isEditableByDriver()) {
            return redirect()->route('fuel-records.my-requests.show', $fuelRecord)
                ->with('toast_error', 'This record can no longer be edited.');
        }

        $title = 'My Fuel Log';
        $subtitle = 'Edit';
        $vehicles = Vehicle::where('status', 'active')->orderBy('kode')->get(['id', 'kode', 'license_plate', 'odometer']);
        $fuelRecord->load('vehicle');

        // Keep current vehicle in the list even if it is no longer active
        if ($fuelRecord->vehicle && ! $vehicles->contains('id', $fuelRecord->vehicle_id)) {
            $vehicles = $vehicles->prepend($fuelRecord->vehicle)->values();
        }

        return view('fuel-records.my-edit', compact('title', 'subtitle', 'fuelRecord', 'vehicles'));
    }

    public function myRequestsUpdate(Request $request, FuelRecord $fuelRecord)
    {
        $this->assertOwnFuel($fuelRecord);
        if (! $fuelRecord->isEditableByDriver()) {
            return back()->with('toast_error', 'This record can no longer be edited.');
        }

        $data = $this->validatedDriver($request, false);

        try {
            DB::beginTransaction();
            $data['total_cost'] = $this->resolveTotal($data);
            $this->assertReceiptNotDuplicate($data, $fuelRecord->id);
            $data['status'] = FuelRecord::STATUS_SUBMITTED;
            $data['verification_notes'] = null;
            $data['rejected_at'] = null;
            $data['verified_by'] = null;
            $data['verified_at'] = null;

            if ($request->hasFile('receipt_image')) {
                $this->deleteReceipt($fuelRecord->receipt_image);
                $data['receipt_image'] = $this->storeReceipt($request->file('receipt_image'));
            }

            $fuelRecord->update($data);
            $this->bumpOdometer($data['vehicle_id'], (int) $data['odometer']);
            DB::commit();

            return redirect()->route('fuel-records.my-requests')
                ->with('toast_success', 'Fuel log updated and resubmitted.');
        } catch (ValidationException $e) {
            DB::rollBack();
            throw $e;
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('toast_error', 'Failed to update: '.$e->getMessage());
        }
    }

    public function myReceipt(FuelRecord $fuelRecord)
    {
        $user = Auth::user();
        $owns = $fuelRecord->created_by === $user->id
            || ($user->employee_id && $fuelRecord->driver_id === $user->employee_id);
        if (! $owns && ! $user->can('fuel-records.show')) {
            abort(403);
        }
        if (! $fuelRecord->receipt_image || ! Storage::disk('private')->exists($fuelRecord->receipt_image)) {
            abort(404);
        }

        return Storage::disk('private')->response($fuelRecord->receipt_image);
    }

    /* ------------------------------------------------------------------ */
    /* Office verification                                                 */
    /* ------------------------------------------------------------------ */

    public function pendingVerification()
    {
        $title = 'Pending Fuel Verification';
        $subtitle = 'Review driver-submitted receipts';

        return view('fuel-records.pending', compact('title', 'subtitle'));
    }

    public function pendingVerificationData()
    {
        $query = FuelRecord::query()
            ->with(['vehicle', 'driver', 'creator'])
            ->where('status', FuelRecord::STATUS_SUBMITTED)
            ->orderBy('fuel_date')
            ->orderBy('created_at');

        return datatables()->of($query)
            ->addIndexColumn()
            ->addColumn('fuel_date_fmt', fn (FuelRecord $r) => optional($r->fuel_date)->format('Y-m-d'))
            ->addColumn('quantity_fmt', fn (FuelRecord $r) => number_format((float) $r->quantity, 2))
            ->addColumn('vehicle_label', fn (FuelRecord $r) => $r->vehicle ? display_text($r->vehicle->kode.' — '.$r->vehicle->license_plate) : '—')
            ->addColumn('driver_label', function (FuelRecord $r) {
                if ($r->driver) {
                    $name = $r->driver->fullname
                        ?? trim(($r->driver->first_name ?? '').' '.($r->driver->last_name ?? ''))
                        ?? null;

                    return e($name ?: '—');
                }

                return e(optional($r->creator)->name ?? '—');
            })
            ->addColumn('total_cost_fmt', fn (FuelRecord $r) => number_format((float) $r->total_cost, 0, ',', '.'))
            ->addColumn('receipt_thumb', function (FuelRecord $r) {
                if (! $r->receipt_image) {
                    return '—';
                }

                return '<a href="'.route('fuel-records.receipt', $r).'" target="_blank" class="btn btn-info btn-sm" title="Receipt"><i class="fas fa-image"></i></a>';
            })
            ->addColumn('action', function (FuelRecord $model) {
                return view('fuel-records.pending-action', compact('model'))->render();
            })
            ->rawColumns(['vehicle_label', 'driver_label', 'receipt_thumb', 'action'])
            ->toJson();
    }

    public function verify(Request $request, FuelRecord $fuelRecord)
    {
        if ($fuelRecord->status !== FuelRecord::STATUS_SUBMITTED) {
            return back()->with('toast_error', 'Only submitted records can be verified.');
        }

        $request->validate(['verification_notes' => ['nullable', 'string', 'max:1000']]);

        $fuelRecord->update([
            'status' => FuelRecord::STATUS_VERIFIED,
            'verified_by' => Auth::id(),
            'verified_at' => now(),
            'verification_notes' => $request->input('verification_notes'),
            'rejected_at' => null,
        ]);

        return back()->with('toast_success', 'Fuel record verified.');
    }

    public function reject(Request $request, FuelRecord $fuelRecord)
    {
        if ($fuelRecord->status !== FuelRecord::STATUS_SUBMITTED) {
            return back()->with('toast_error', 'Only submitted records can be rejected.');
        }

        $request->validate(['verification_notes' => ['required', 'string', 'max:1000']]);

        $fuelRecord->update([
            'status' => FuelRecord::STATUS_REJECTED,
            'verified_by' => Auth::id(),
            'verified_at' => null,
            'rejected_at' => now(),
            'verification_notes' => $request->input('verification_notes'),
        ]);

        return back()->with('toast_success', 'Fuel record rejected.');
    }

    /* ------------------------------------------------------------------ */
    /* Helpers                                                             */
    /* ------------------------------------------------------------------ */

    protected function validated(Request $request): array
    {
        return $request->validate([
            'vehicle_id' => ['required', 'uuid', Rule::exists('vehicles', 'id')],
            'fuel_date' => ['required', 'date'],
            'odometer' => ['required', 'integer', 'min:0'],
            'fuel_type' => ['required', 'string', 'max:50'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'price_per_liter' => ['required', 'numeric', 'min:0'],
            'fuel_station' => ['nullable', 'string', 'max:255'],
            'receipt_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'receipt_image' => ['nullable', 'file', 'max:8192', 'mimes:jpg,jpeg,png,pdf,webp'],
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function validatedDriver(Request $request, bool $requireImage = true): array
    {
        $rules = [
            'vehicle_id' => ['required', 'uuid', Rule::exists('vehicles', 'id')],
            'fuel_date' => ['required', 'date'],
            'odometer' => ['required', 'integer', 'min:0'],
            'fuel_type' => ['required', 'string', 'max:50'],
            'quantity' => ['required', 'numeric', 'min:0.01'],
            'price_per_liter' => ['required', 'numeric', 'min:0'],
            'total_cost' => ['nullable', 'numeric', 'min:0'],
            'fuel_station' => ['nullable', 'string', 'max:255'],
            'receipt_number' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string'],
            'receipt_path' => ['nullable', 'string'],
            'ai_raw_json' => ['nullable', 'string'],
            'ai_model' => ['nullable', 'string', 'max:100'],
            'receipt_image' => ['nullable', 'file', 'max:8192', 'mimes:jpg,jpeg,png,webp'],
        ];

        $data = $request->validate($rules);

        if (! empty($data['receipt_path'])) {
            $path = $data['receipt_path'];
            if (! str_starts_with($path, 'fuel_receipts/') || str_contains($path, '..')
                || ! Storage::disk('private')->exists($path)) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'receipt_path' => 'Invalid receipt path. Please scan again.',
                ]);
            }
        }

        if ($requireImage && empty($data['receipt_path']) && ! $request->hasFile('receipt_image')) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'receipt_image' => 'Receipt photo is required.',
            ]);
        }

        return $data;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function resolveTotal(array $data): float
    {
        if (isset($data['total_cost']) && $data['total_cost'] !== null && $data['total_cost'] !== '') {
            return round((float) $data['total_cost'], 2);
        }

        return round((float) $data['quantity'] * (float) $data['price_per_liter'], 2);
    }

    /**
     * Block duplicate SPBU nota: vehicle + date + receipt_number (+ total when present).
     *
     * @param  array<string, mixed>  $data
     */
    protected function assertReceiptNotDuplicate(array $data, ?string $ignoreRecordId = null): void
    {
        $checker = app(FuelReceiptDuplicateChecker::class);
        $duplicate = $checker->findDuplicate(
            (string) $data['vehicle_id'],
            $data['fuel_date'],
            isset($data['receipt_number']) ? (string) $data['receipt_number'] : null,
            $data['total_cost'] ?? null,
            $ignoreRecordId,
        );

        if ($duplicate) {
            throw ValidationException::withMessages([
                'receipt_number' => $checker->messageFor($duplicate),
            ]);
        }
    }

    protected function bumpOdometer(string $vehicleId, int $odometer): void
    {
        $vehicle = Vehicle::find($vehicleId);
        if ($vehicle && $odometer > (int) $vehicle->odometer) {
            $vehicle->update(['odometer' => $odometer]);
        }
    }

    protected function storeReceipt($file): string
    {
        return $file->storeAs(
            'fuel_receipts',
            now()->format('YmdHis').'_'.$file->getClientOriginalName(),
            'private'
        );
    }

    protected function deleteReceipt(?string $path): void
    {
        if ($path && Storage::disk('private')->exists($path)) {
            Storage::disk('private')->delete($path);
        }
    }

    protected function assertOwnFuel(FuelRecord $fuelRecord): void
    {
        $user = Auth::user();
        $owns = $fuelRecord->created_by === $user->id
            || ($user->employee_id && $fuelRecord->driver_id === $user->employee_id);
        if (! $owns) {
            abort(403);
        }
    }

    protected function statusBadgeHtml(?string $status): string
    {
        $map = [
            'submitted' => 'warning',
            'verified' => 'success',
            'rejected' => 'danger',
            'claimed' => 'info',
        ];
        $class = $map[$status] ?? 'secondary';

        return '<span class="badge badge-'.$class.'">'.e(ucfirst((string) $status)).'</span>';
    }
}
