<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\FuelClaim;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FuelClaimApiController extends Controller
{
    /**
     * GET /api/v1/fuel-claims
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min(100, max(1, (int) $request->input('per_page', 25)));
        $query = FuelClaim::query()->withCount('records')->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        } else {
            $query->where('status', FuelClaim::STATUS_READY);
        }

        $paginator = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $paginator->getCollection()->map(fn (FuelClaim $c) => $this->summary($c)),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'last_page' => $paginator->lastPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
            ],
        ]);
    }

    /**
     * GET /api/v1/fuel-claims/{fuelClaim}
     */
    public function show(FuelClaim $fuelClaim): JsonResponse
    {
        $fuelClaim->load(['records.vehicle']);

        return response()->json([
            'success' => true,
            'data' => $this->detail($fuelClaim),
        ]);
    }

    /**
     * PUT /api/v1/fuel-claims/{fuelClaim}/sent
     */
    public function markSent(Request $request, FuelClaim $fuelClaim): JsonResponse
    {
        if (! in_array($fuelClaim->status, [FuelClaim::STATUS_READY, FuelClaim::STATUS_SENT], true)) {
            return response()->json(['success' => false, 'message' => 'Claim must be ready.'], 422);
        }

        $data = $request->validate([
            'external_ref' => ['nullable', 'string', 'max:100'],
        ]);

        $fuelClaim->update([
            'status' => FuelClaim::STATUS_SENT,
            'sent_at' => now(),
            'external_ref' => $data['external_ref'] ?? $fuelClaim->external_ref,
        ]);

        return response()->json(['success' => true, 'data' => $this->summary($fuelClaim->fresh())]);
    }

    /**
     * PUT /api/v1/fuel-claims/{fuelClaim}/realized
     */
    public function markRealized(Request $request, FuelClaim $fuelClaim): JsonResponse
    {
        if (! in_array($fuelClaim->status, [FuelClaim::STATUS_SENT, FuelClaim::STATUS_READY, FuelClaim::STATUS_REALIZED], true)) {
            return response()->json(['success' => false, 'message' => 'Claim cannot be marked realized in current status.'], 422);
        }

        $data = $request->validate([
            'external_ref' => ['nullable', 'string', 'max:100'],
        ]);

        $fuelClaim->update([
            'status' => FuelClaim::STATUS_REALIZED,
            'realized_at' => now(),
            'sent_at' => $fuelClaim->sent_at ?? now(),
            'external_ref' => $data['external_ref'] ?? $fuelClaim->external_ref,
        ]);

        return response()->json(['success' => true, 'data' => $this->summary($fuelClaim->fresh())]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function summary(FuelClaim $c): array
    {
        return [
            'id' => $c->id,
            'claim_number' => $c->claim_number,
            'period_from' => optional($c->period_from)->toDateString(),
            'period_to' => optional($c->period_to)->toDateString(),
            'total_quantity' => (float) $c->total_quantity,
            'total_cost' => (float) $c->total_cost,
            'status' => $c->status,
            'records_count' => $c->records_count ?? $c->records()->count(),
            'external_ref' => $c->external_ref,
            'ready_at' => optional($c->ready_at)?->toIso8601String(),
            'sent_at' => optional($c->sent_at)?->toIso8601String(),
            'realized_at' => optional($c->realized_at)?->toIso8601String(),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function detail(FuelClaim $c): array
    {
        return array_merge($this->summary($c), [
            'notes' => $c->notes,
            'items' => $c->records->map(function ($r) {
                return [
                    'id' => $r->id,
                    'vehicle_id' => $r->vehicle_id,
                    'vehicle_code' => $r->vehicle?->kode,
                    'license_plate' => $r->vehicle?->license_plate,
                    'fuel_date' => optional($r->fuel_date)->toDateString(),
                    'odometer' => $r->odometer,
                    'fuel_type' => $r->fuel_type,
                    'quantity' => (float) $r->quantity,
                    'price_per_liter' => (float) $r->price_per_liter,
                    'total_cost' => (float) $r->total_cost,
                    'fuel_station' => $r->fuel_station,
                    'receipt_number' => $r->receipt_number,
                    'has_receipt_image' => ! empty($r->receipt_image),
                ];
            })->values(),
        ]);
    }
}
