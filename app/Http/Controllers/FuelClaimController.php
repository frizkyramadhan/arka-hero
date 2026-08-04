<?php

namespace App\Http\Controllers;

use App\Models\FuelClaim;
use App\Models\FuelRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FuelClaimController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:fuel-claims.show')->only(['index', 'data', 'show']);
        $this->middleware('permission:fuel-claims.create')->only(['create', 'store']);
        $this->middleware('permission:fuel-claims.edit')->only(['edit', 'update', 'removeItem']);
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

        return view('fuel-claims.show', compact('title', 'subtitle', 'fuelClaim'));
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
}
