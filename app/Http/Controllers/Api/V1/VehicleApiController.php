<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Vehicle;
use App\Models\VehicleDocument;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VehicleApiController extends Controller
{
    /**
     * GET /api/v1/vehicles — paginated vehicles with STNK/PKB/KIR validity summary.
     *
     * Query: per_page (default 25, max 100), status, search (kode / plate / pic / lokasi)
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min(100, max(1, (int) $request->input('per_page', 25)));

        $query = Vehicle::query()
            ->with(['documents' => fn ($q) => $q->orderBy('document_type')->orderByDesc('expiry_date')])
            ->orderBy('kode');

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($search = trim((string) $request->input('search', ''))) {
            $query->where(function ($q) use ($search) {
                $q->where('kode', 'like', "%{$search}%")
                    ->orWhere('license_plate', 'like', "%{$search}%")
                    ->orWhere('pic', 'like', "%{$search}%")
                    ->orWhere('lokasi', 'like', "%{$search}%")
                    ->orWhere('project_code', 'like', "%{$search}%");
            });
        }

        $paginator = $query->paginate($perPage);

        $data = $paginator->getCollection()->map(fn (Vehicle $vehicle) => $this->vehicleSummary($vehicle));

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => $this->paginationMeta($paginator),
        ]);
    }

    /**
     * GET /api/v1/vehicles/{vehicle} — vehicle detail + all documents validity.
     */
    public function show(Vehicle $vehicle): JsonResponse
    {
        $vehicle->load(['documents' => fn ($q) => $q->orderBy('document_type')->orderByDesc('expiry_date')]);

        return response()->json([
            'success' => true,
            'data' => $this->vehicleDetail($vehicle),
        ]);
    }

    /**
     * GET /api/v1/vehicle-documents/expiring — expired or expiring within N days.
     *
     * Query: days (default 30), per_page, document_type, include_archived (0|1)
     */
    public function expiringDocuments(Request $request): JsonResponse
    {
        $days = max(0, (int) $request->input('days', 30));
        $perPage = min(100, max(1, (int) $request->input('per_page', 25)));
        $until = now()->startOfDay()->addDays($days);

        $query = VehicleDocument::query()
            ->with('vehicle:id,kode,license_plate,pic,lokasi,project_code,status')
            ->whereNotNull('expiry_date')
            ->whereDate('expiry_date', '<=', $until->toDateString())
            ->orderBy('expiry_date')
            ->orderBy('document_type');

        if (! $request->boolean('include_archived')) {
            $query->where('status', '!=', 'archived');
        }

        if ($request->filled('document_type')) {
            $query->where('document_type', $request->input('document_type'));
        }

        $paginator = $query->paginate($perPage);

        $data = $paginator->getCollection()->map(function (VehicleDocument $doc) {
            $vehicle = $doc->vehicle;

            return [
                'id' => $doc->id,
                'vehicle_id' => $doc->vehicle_id,
                'kode' => $vehicle?->kode,
                'license_plate' => $vehicle?->license_plate,
                'pic' => $vehicle?->pic,
                'lokasi' => $vehicle?->lokasi ?? $vehicle?->project_code,
                'vehicle_status' => $vehicle?->status,
                'document_type' => $doc->document_type,
                'document_name' => $doc->document_name,
                'document_number' => $doc->document_number,
                'expiry_date' => optional($doc->expiry_date)->format('Y-m-d'),
                'days_remaining' => $doc->days_remaining,
                'status' => $doc->status,
            ];
        });

        return response()->json([
            'success' => true,
            'data' => $data,
            'meta' => array_merge($this->paginationMeta($paginator), [
                'days' => $days,
                'until' => $until->toDateString(),
            ]),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    protected function vehicleSummary(Vehicle $vehicle): array
    {
        return [
            'id' => $vehicle->id,
            'kode' => $vehicle->kode,
            'license_plate' => $vehicle->license_plate,
            'pic' => $vehicle->pic,
            'lokasi' => $vehicle->lokasi ?? $vehicle->project_code,
            'project_code' => $vehicle->project_code,
            'status' => $vehicle->status,
            'validity' => [
                'stnk' => $this->validityEntry($vehicle, 'stnk'),
                'pkb' => $this->validityEntry($vehicle, 'pkb'),
                'kir' => $this->validityEntry($vehicle, 'kir'),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function vehicleDetail(Vehicle $vehicle): array
    {
        $summary = $this->vehicleSummary($vehicle);

        $summary['type'] = $vehicle->type;
        $summary['ownership'] = $vehicle->ownership;
        $summary['fuel_type'] = $vehicle->fuel_type;
        $summary['brand'] = $vehicle->brand;
        $summary['model'] = $vehicle->model;
        $summary['year'] = $vehicle->year;
        $summary['odometer'] = $vehicle->odometer;
        $summary['keterangan'] = $vehicle->keterangan;

        $summary['documents'] = $vehicle->documents->map(fn (VehicleDocument $doc) => [
            'id' => $doc->id,
            'document_type' => $doc->document_type,
            'document_name' => $doc->document_name,
            'document_number' => $doc->document_number,
            'issue_date' => optional($doc->issue_date)->format('Y-m-d'),
            'expiry_date' => optional($doc->expiry_date)->format('Y-m-d'),
            'days_remaining' => $doc->days_remaining,
            'status' => $doc->status,
            'issuing_authority' => $doc->issuing_authority,
            'has_file' => ! empty($doc->file_path),
        ])->values();

        return $summary;
    }

    /**
     * @return array{expiry_date: ?string, days_remaining: ?int, status: ?string, document_id: ?string}|null
     */
    protected function validityEntry(Vehicle $vehicle, string $type): ?array
    {
        $doc = $vehicle->activeDocument($type);
        if (! $doc) {
            return null;
        }

        return [
            'document_id' => $doc->id,
            'expiry_date' => optional($doc->expiry_date)->format('Y-m-d'),
            'days_remaining' => $doc->days_remaining,
            'status' => $doc->status,
        ];
    }

    /**
     * @param  \Illuminate\Contracts\Pagination\LengthAwarePaginator  $paginator
     * @return array<string, int>
     */
    protected function paginationMeta($paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ];
    }
}
