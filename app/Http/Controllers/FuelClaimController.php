<?php

namespace App\Http\Controllers;

use App\Models\FuelClaim;
use App\Models\FuelRecord;
use App\Models\Vehicle;
use App\Services\FuelReceiptDuplicateChecker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class FuelClaimController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:fuel-claims.show')->only(['index', 'data', 'show', 'print']);
        $this->middleware('permission:fuel-claims.create')->only(['create', 'store']);
        $this->middleware('permission:fuel-claims.edit')->only(['addReceipts', 'removeReceipt', 'updateReceipt']);
        $this->middleware('permission:fuel-claims.delete')->only(['destroy', 'cancel']);
        $this->middleware('permission:fuel-claims.ready')->only(['markReady']);
    }

    public function index()
    {
        $title = 'Fuel Claims';
        $subtitle = 'Bundles of verified receipts for fund realization';

        return view('fuel-claims.index', compact('title', 'subtitle'));
    }

    public function data(Request $request)
    {
        $query = FuelClaim::query()->withCount('records')->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        return datatables()->of($query)
            ->addIndexColumn()
            ->addColumn('period', function (FuelClaim $c) {
                $from = optional($c->period_from)->format('Y-m-d') ?? '—';
                $to = optional($c->period_to)->format('Y-m-d') ?? '—';

                return e($from.' → '.$to);
            })
            ->addColumn('total_cost_fmt', fn (FuelClaim $c) => number_format((float) $c->total_cost, 0, ',', '.'))
            ->addColumn('status_badge', function (FuelClaim $c) {
                $map = [
                    'draft' => 'secondary',
                    'ready' => 'success',
                    'sent' => 'info',
                    'realized' => 'primary',
                    'cancelled' => 'danger',
                ];

                return '<span class="badge badge-'.($map[$c->status] ?? 'secondary').'">'.e(ucfirst($c->status)).'</span>';
            })
            ->addColumn('action', function (FuelClaim $model) {
                return view('fuel-claims.action', compact('model'))->render();
            })
            ->rawColumns(['period', 'status_badge', 'action'])
            ->toJson();
    }

    public function create()
    {
        $title = 'Fuel Claims';
        $subtitle = 'Create claim bundle';
        $verified = FuelRecord::query()
            ->with('vehicle')
            ->where('status', FuelRecord::STATUS_VERIFIED)
            ->whereNull('fuel_claim_id')
            ->orderBy('fuel_date')
            ->get();

        return view('fuel-claims.create', compact('title', 'subtitle', 'verified'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'fuel_record_ids' => ['required', 'array', 'min:1'],
            'fuel_record_ids.*' => ['uuid', 'exists:fuel_records,id'],
            'period_from' => ['nullable', 'date'],
            'period_to' => ['nullable', 'date'],
            'notes' => ['nullable', 'string'],
        ]);

        try {
            DB::beginTransaction();

            $records = FuelRecord::query()
                ->whereIn('id', $data['fuel_record_ids'])
                ->where('status', FuelRecord::STATUS_VERIFIED)
                ->whereNull('fuel_claim_id')
                ->lockForUpdate()
                ->get();

            if ($records->count() !== count($data['fuel_record_ids'])) {
                throw new \RuntimeException('Some selected records are not available (must be verified and unclaimed).');
            }

            $claim = FuelClaim::create([
                'claim_number' => FuelClaim::generateClaimNumber(),
                'period_from' => $data['period_from'] ?? $records->min('fuel_date'),
                'period_to' => $data['period_to'] ?? $records->max('fuel_date'),
                'status' => FuelClaim::STATUS_DRAFT,
                'notes' => $data['notes'] ?? null,
                'created_by' => Auth::id(),
                'total_quantity' => $records->sum('quantity'),
                'total_cost' => $records->sum('total_cost'),
            ]);

            FuelRecord::whereIn('id', $records->pluck('id'))->update([
                'fuel_claim_id' => $claim->id,
                'status' => FuelRecord::STATUS_CLAIMED,
            ]);

            DB::commit();

            return redirect()->route('fuel-claims.show', $claim)
                ->with('toast_success', 'Fuel claim '.$claim->claim_number.' created.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('toast_error', $e->getMessage());
        }
    }

    public function show(FuelClaim $fuelClaim)
    {
        $title = 'Fuel Claims';
        $subtitle = $fuelClaim->claim_number;
        $fuelClaim->load(['records.vehicle', 'records.driver', 'creator']);
        $availableRecords = collect();
        $vehicles = collect();

        if ($fuelClaim->status === FuelClaim::STATUS_DRAFT && Auth::user()->can('fuel-claims.edit')) {
            $availableRecords = FuelRecord::query()
                ->with('vehicle')
                ->where('status', FuelRecord::STATUS_VERIFIED)
                ->whereNull('fuel_claim_id')
                ->orderBy('fuel_date')
                ->get();
            $vehicles = Vehicle::query()
                ->orderBy('kode')
                ->get(['id', 'kode', 'license_plate']);
        }

        return view('fuel-claims.show', compact('title', 'subtitle', 'fuelClaim', 'availableRecords', 'vehicles'));
    }

    public function print(FuelClaim $fuelClaim)
    {
        $fuelClaim->load(['records.vehicle', 'creator']);

        $records = $fuelClaim->records
            ->sortBy([
                ['fuel_date', 'asc'],
                ['created_at', 'asc'],
            ])
            ->values();

        $receiptImages = [];
        foreach ($records as $record) {
            $receiptImages[$record->id] = $this->receiptDataUri($record);
        }

        $pages = $records->chunk(9)->values();

        return view('fuel-claims.print', compact('fuelClaim', 'pages', 'receiptImages'));
    }

    public function addReceipts(Request $request, FuelClaim $fuelClaim)
    {
        $data = $request->validate([
            'fuel_record_ids' => ['required', 'array', 'min:1'],
            'fuel_record_ids.*' => ['uuid', 'distinct', 'exists:fuel_records,id'],
        ]);

        try {
            DB::transaction(function () use ($fuelClaim, $data) {
                $claim = FuelClaim::query()->lockForUpdate()->findOrFail($fuelClaim->id);

                if ($claim->status !== FuelClaim::STATUS_DRAFT) {
                    throw new \RuntimeException('Receipts can only be added to draft claims.');
                }

                $records = FuelRecord::query()
                    ->whereIn('id', $data['fuel_record_ids'])
                    ->where('status', FuelRecord::STATUS_VERIFIED)
                    ->whereNull('fuel_claim_id')
                    ->lockForUpdate()
                    ->get();

                if ($records->count() !== count($data['fuel_record_ids'])) {
                    throw new \RuntimeException('Some selected receipts are no longer available.');
                }

                FuelRecord::query()
                    ->whereIn('id', $records->pluck('id'))
                    ->update([
                        'fuel_claim_id' => $claim->id,
                        'status' => FuelRecord::STATUS_CLAIMED,
                    ]);

                $claim->recalculateTotals();
            });

            return back()->with('toast_success', 'Receipts added to the claim.');
        } catch (\Throwable $e) {
            return back()->withInput()->with('toast_error', $e->getMessage());
        }
    }

    public function removeReceipt(FuelClaim $fuelClaim, FuelRecord $fuelRecord)
    {
        try {
            DB::transaction(function () use ($fuelClaim, $fuelRecord) {
                $claim = FuelClaim::query()->lockForUpdate()->findOrFail($fuelClaim->id);

                if ($claim->status !== FuelClaim::STATUS_DRAFT) {
                    throw new \RuntimeException('Receipts can only be removed from draft claims.');
                }

                $record = FuelRecord::query()->lockForUpdate()->findOrFail($fuelRecord->id);

                if ($record->fuel_claim_id !== $claim->id) {
                    throw new \RuntimeException('Receipt does not belong to this claim.');
                }

                $record->update([
                    'fuel_claim_id' => null,
                    'status' => FuelRecord::STATUS_VERIFIED,
                ]);

                $claim->recalculateTotals();
            });

            return back()->with('toast_success', 'Receipt removed from the claim.');
        } catch (\Throwable $e) {
            return back()->with('toast_error', $e->getMessage());
        }
    }

    public function updateReceipt(Request $request, FuelClaim $fuelClaim, FuelRecord $fuelRecord)
    {
        try {
            $data = $request->validate([
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

            DB::transaction(function () use ($request, $fuelClaim, $fuelRecord, $data) {
                $claim = FuelClaim::query()->lockForUpdate()->findOrFail($fuelClaim->id);

                if ($claim->status !== FuelClaim::STATUS_DRAFT) {
                    throw new \RuntimeException('Receipts can only be edited on draft claims.');
                }

                $record = FuelRecord::query()->lockForUpdate()->findOrFail($fuelRecord->id);

                if ($record->fuel_claim_id !== $claim->id) {
                    throw new \RuntimeException('Receipt does not belong to this claim.');
                }

                $data['total_cost'] = round((float) $data['quantity'] * (float) $data['price_per_liter'], 2);

                $checker = app(FuelReceiptDuplicateChecker::class);
                $duplicate = $checker->findDuplicate(
                    (string) $data['vehicle_id'],
                    $data['fuel_date'],
                    isset($data['receipt_number']) ? (string) $data['receipt_number'] : null,
                    $data['total_cost'],
                    $record->id,
                );

                if ($duplicate) {
                    throw ValidationException::withMessages([
                        'receipt_number' => $checker->messageFor($duplicate),
                    ]);
                }

                if ($request->hasFile('receipt_image')) {
                    if ($record->receipt_image && Storage::disk('private')->exists($record->receipt_image)) {
                        Storage::disk('private')->delete($record->receipt_image);
                    }
                    $file = $request->file('receipt_image');
                    $data['receipt_image'] = $file->storeAs(
                        'fuel_receipts',
                        now()->format('YmdHis').'_'.$file->getClientOriginalName(),
                        'private'
                    );
                }

                $record->update($data);

                $vehicle = Vehicle::query()->find($data['vehicle_id']);
                if ($vehicle && (int) $data['odometer'] > (int) $vehicle->odometer) {
                    $vehicle->update(['odometer' => (int) $data['odometer']]);
                }

                $claim->recalculateTotals();
            });

            return back()->with('toast_success', 'Receipt updated.');
        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput()
                ->with('edit_receipt_id', $fuelRecord->id);
        } catch (\Throwable $e) {
            return back()->withInput()
                ->with('edit_receipt_id', $fuelRecord->id)
                ->with('toast_error', $e->getMessage());
        }
    }

    public function markReady(FuelClaim $fuelClaim)
    {
        if ($fuelClaim->status !== FuelClaim::STATUS_DRAFT) {
            return back()->with('toast_error', 'Only draft claims can be marked ready.');
        }
        if ($fuelClaim->records()->count() < 1) {
            return back()->with('toast_error', 'Claim has no items.');
        }

        $fuelClaim->recalculateTotals();
        $fuelClaim->update([
            'status' => FuelClaim::STATUS_READY,
            'ready_at' => now(),
        ]);

        return back()->with('toast_success', 'Claim marked ready for external realization.');
    }

    public function cancel(FuelClaim $fuelClaim)
    {
        if (! in_array($fuelClaim->status, [FuelClaim::STATUS_DRAFT, FuelClaim::STATUS_READY], true)) {
            return back()->with('toast_error', 'Only draft/ready claims can be cancelled.');
        }

        try {
            DB::beginTransaction();

            FuelRecord::where('fuel_claim_id', $fuelClaim->id)->update([
                'fuel_claim_id' => null,
                'status' => FuelRecord::STATUS_VERIFIED,
            ]);

            $fuelClaim->update([
                'status' => FuelClaim::STATUS_CANCELLED,
                'total_quantity' => 0,
                'total_cost' => 0,
            ]);

            DB::commit();

            return redirect()->route('fuel-claims.index')
                ->with('toast_success', 'Claim cancelled; items returned to verified.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('toast_error', $e->getMessage());
        }
    }

    public function destroy(FuelClaim $fuelClaim)
    {
        if ($fuelClaim->status !== FuelClaim::STATUS_DRAFT && $fuelClaim->status !== FuelClaim::STATUS_CANCELLED) {
            return back()->with('toast_error', 'Only draft or cancelled claims can be deleted.');
        }

        try {
            DB::beginTransaction();
            FuelRecord::where('fuel_claim_id', $fuelClaim->id)->update([
                'fuel_claim_id' => null,
                'status' => FuelRecord::STATUS_VERIFIED,
            ]);
            $fuelClaim->delete();
            DB::commit();

            return redirect()->route('fuel-claims.index')->with('toast_success', 'Claim deleted.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('toast_error', $e->getMessage());
        }
    }

    /**
     * Embed private receipt files as data-URI for print (images only).
     */
    protected function receiptDataUri(FuelRecord $record): ?string
    {
        $path = $record->receipt_image;
        if (! $path || ! Storage::disk('private')->exists($path)) {
            return null;
        }

        $absolute = Storage::disk('private')->path($path);
        $mime = @mime_content_type($absolute) ?: null;
        if (! $mime || ! str_starts_with($mime, 'image/')) {
            return null;
        }

        $binary = Storage::disk('private')->get($path);
        if ($binary === false || $binary === null || $binary === '') {
            return null;
        }

        return 'data:'.$mime.';base64,'.base64_encode($binary);
    }
}
