<?php

namespace App\Http\Controllers;

use App\Exports\VehicleExport;
use App\Imports\VehicleImport;
use App\Models\FuelRecord;
use App\Models\Vehicle;
use App\Models\VehicleDocument;
use App\Services\ArkFleetClient;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Validators\ValidationException;

class VehicleController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:vehicles.show')->only(['index', 'data', 'show', 'export', 'template']);
        $this->middleware('permission:vehicles.create')->only(['create', 'store']);
        $this->middleware('permission:vehicles.edit')->only(['edit', 'update']);
        $this->middleware('permission:vehicles.create|vehicles.edit')->only(['arkfleetEquipments', 'import']);
        $this->middleware('permission:vehicles.delete')->only(['destroy']);
    }

    public function index()
    {
        $title = 'Vehicles';
        $subtitle = 'Light Vehicle document validity monitoring';

        return view('vehicles.index', compact('title', 'subtitle'));
    }

    public function data(Request $request)
    {
        $query = $this->filteredVehiclesQuery($request);

        return datatables()->of($query)
            ->addIndexColumn()
            ->addColumn('stnk_cell', function (Vehicle $row) {
                return Vehicle::formatExpiryCell(
                    $row->documentExpiry('stnk'),
                    $row->daysRemainingFor('stnk')
                );
            })
            ->addColumn('pkb_cell', function (Vehicle $row) {
                return Vehicle::formatExpiryCell(
                    $row->documentExpiry('pkb'),
                    $row->daysRemainingFor('pkb')
                );
            })
            ->addColumn('kir_cell', function (Vehicle $row) {
                return Vehicle::formatExpiryCell(
                    $row->documentExpiry('kir'),
                    $row->daysRemainingFor('kir')
                );
            })
            ->addColumn('status_badge', function (Vehicle $row) {
                $map = [
                    'active' => 'success',
                    'inactive' => 'secondary',
                    'maintenance' => 'warning',
                    'sold' => 'dark',
                    'accident' => 'danger',
                ];
                $class = $map[$row->status] ?? 'secondary';

                return '<span class="badge badge-'.$class.'">'.e(ucfirst($row->status)).'</span>';
            })
            ->addColumn('action', function (Vehicle $model) {
                return view('vehicles.action', compact('model'))->render();
            })
            ->rawColumns(['stnk_cell', 'pkb_cell', 'kir_cell', 'status_badge', 'action'])
            ->toJson();
    }

    public function export(Request $request)
    {
        $query = $this->filteredVehiclesQuery($request);

        return Excel::download(
            new VehicleExport($query),
            'vehicles-'.now()->format('Y-m-d').'.xlsx'
        );
    }

    public function template()
    {
        $empty = Vehicle::query()->whereRaw('1 = 0')->with('documents');

        return Excel::download(
            new VehicleExport($empty),
            'vehicles-import-template.xlsx'
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
            $import = new VehicleImport;
            Excel::import($import, $request->file('file'));

            $failures = $import->failures();
            if ($failures->isNotEmpty()) {
                return back()->with('failures', $this->formatImportFailures($failures));
            }

            $message = "Import completed: {$import->created} created, {$import->updated} updated.";

            return redirect()->route('vehicles.index')->with('toast_success', $message);
        } catch (ValidationException $e) {
            return back()->with('failures', $this->formatImportFailures($e->failures()));
        } catch (\Throwable $e) {
            return back()->with('toast_error', 'Import failed: '.$e->getMessage());
        }
    }

    public function create()
    {
        $title = 'Vehicles';
        $subtitle = 'Add Vehicle';
        $equipments = [];

        return view('vehicles.create', compact('title', 'subtitle', 'equipments'));
    }

    public function store(Request $request)
    {
        $data = $this->validatedVehicle($request);

        try {
            DB::beginTransaction();

            $vehicle = Vehicle::create($data);
            $this->syncCoreDocuments($vehicle, $request);

            DB::commit();

            return redirect()->route('vehicles.show', $vehicle)
                ->with('toast_success', 'Vehicle added successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('toast_error', 'Failed to add vehicle: '.$e->getMessage());
        }
    }

    public function show(Vehicle $vehicle)
    {
        $title = 'Vehicles';
        $subtitle = $vehicle->kode.' — '.$vehicle->license_plate;
        $vehicle->load([
            'documents' => fn ($q) => $q->orderBy('document_type')->orderByDesc('expiry_date'),
            'fuelRecords' => fn ($q) => $q->orderByDesc('fuel_date')->limit(20),
            'assignments' => fn ($q) => $q->orderByDesc('assignment_date')->orderByDesc('form_number')->limit(20),
        ]);

        return view('vehicles.show', compact('title', 'subtitle', 'vehicle'));
    }

    public function edit(Vehicle $vehicle)
    {
        $title = 'Vehicles';
        $subtitle = 'Edit '.$vehicle->kode;
        $equipments = [];
        $vehicle->load('documents');

        return view('vehicles.edit', compact('title', 'subtitle', 'vehicle', 'equipments'));
    }

    public function update(Request $request, Vehicle $vehicle)
    {
        $data = $this->validatedVehicle($request, $vehicle);

        try {
            DB::beginTransaction();

            $vehicle->update($data);
            $this->syncCoreDocuments($vehicle, $request);

            DB::commit();

            return redirect()->route('vehicles.show', $vehicle)
                ->with('toast_success', 'Vehicle updated successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->withInput()->with('toast_error', 'Failed to update vehicle: '.$e->getMessage());
        }
    }

    public function destroy(Vehicle $vehicle)
    {
        try {
            DB::beginTransaction();
            // Delete via Eloquent so FuelRecord::deleting removes receipt files
            // (DB cascadeOnDelete would skip model events and leave orphan files).
            $vehicle->fuelRecords()->each(function (FuelRecord $record) {
                $record->delete();
            });
            $vehicle->delete();
            DB::commit();

            return redirect()->route('vehicles.index')
                ->with('toast_success', 'Vehicle deleted successfully.');
        } catch (\Throwable $e) {
            DB::rollBack();

            return back()->with('toast_error', 'Failed to delete vehicle: '.$e->getMessage());
        }
    }

    public function arkfleetEquipments(ArkFleetClient $arkFleet)
    {
        $result = $arkFleet->getLightVehicleEquipments();

        return response()->json($result);
    }

    protected function filteredVehiclesQuery(Request $request): Builder
    {
        $query = Vehicle::query()
            ->with(['documents' => function ($q) {
                $q->whereIn('document_type', ['stnk', 'pkb', 'kir'])
                    ->whereIn('status', ['active', 'expired', 'pending_renewal']);
            }])
            ->orderBy('kode');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('lokasi')) {
            $query->where(function ($w) use ($request) {
                $w->where('lokasi', 'like', '%'.$request->lokasi.'%')
                    ->orWhere('project_code', 'like', '%'.$request->lokasi.'%');
            });
        }

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function ($w) use ($q) {
                $w->where('kode', 'like', "%{$q}%")
                    ->orWhere('license_plate', 'like', "%{$q}%")
                    ->orWhere('pic', 'like', "%{$q}%")
                    ->orWhere('keterangan', 'like', "%{$q}%");
            });
        }

        if ($request->filled('validity')) {
            $this->applyValidityFilter($query, (string) $request->validity, (int) $request->input('validity_days', 30));
        }

        return $query;
    }

    protected function applyValidityFilter(Builder $query, string $validity, int $days): void
    {
        $days = max(1, min(365, $days));
        $today = now()->toDateString();
        $until = now()->addDays($days)->toDateString();
        $coreTypes = ['stnk', 'pkb', 'kir'];

        $coreDocs = function ($q) use ($coreTypes) {
            $q->whereIn('document_type', $coreTypes)
                ->whereIn('status', ['active', 'expired', 'pending_renewal'])
                ->whereNotNull('expiry_date');
        };

        match ($validity) {
            'expired' => $query->whereHas('documents', function ($q) use ($coreDocs, $today) {
                $coreDocs($q);
                $q->whereDate('expiry_date', '<', $today);
            }),
            'expiring' => $query->whereHas('documents', function ($q) use ($coreDocs, $today, $until) {
                $coreDocs($q);
                $q->whereDate('expiry_date', '>=', $today)
                    ->whereDate('expiry_date', '<=', $until);
            }),
            'valid' => $query->whereHas('documents', function ($q) use ($coreDocs, $until) {
                $coreDocs($q);
                $q->whereDate('expiry_date', '>', $until);
            })->whereDoesntHave('documents', function ($q) use ($coreDocs, $until) {
                $coreDocs($q);
                $q->whereDate('expiry_date', '<=', $until);
            }),
            'missing' => $query->where(function ($w) use ($coreTypes) {
                foreach ($coreTypes as $type) {
                    $w->orWhereDoesntHave('documents', function ($q) use ($type) {
                        $q->where('document_type', $type)
                            ->whereIn('status', ['active', 'expired', 'pending_renewal'])
                            ->whereNotNull('expiry_date');
                    });
                }
            }),
            default => null,
        };
    }

    /**
     * @param  iterable<\Maatwebsite\Excel\Validators\Failure>  $failures
     * @return \Illuminate\Support\Collection<int, array<string, mixed>>
     */
    protected function formatImportFailures(iterable $failures)
    {
        return collect($failures)->map(function ($failure) {
            $values = $failure->values();
            $attribute = $failure->attribute();
            $value = is_array($values) && array_key_exists($attribute, $values) ? $values[$attribute] : null;

            return [
                'sheet' => 'Vehicles',
                'row' => $failure->row(),
                'attribute' => $attribute,
                'value' => $value,
                'errors' => implode(', ', $failure->errors()),
            ];
        });
    }

    /**
     * @return array<string, mixed>
     */
    protected function validatedVehicle(Request $request, ?Vehicle $vehicle = null): array
    {
        // Hidden ArkFleet fields often arrive as "" — Laravel nullable+integer rejects empty strings.
        // Capacity is not shown in the UI; coerce silently so users never see capacity errors.
        foreach (['arkfleet_equipment_id', 'year', 'odometer', 'capacity'] as $field) {
            $value = $request->input($field);
            if ($value === '' || $value === null) {
                $request->merge([$field => null]);
            } elseif ($field === 'capacity' && is_numeric($value)) {
                $request->merge(['capacity' => (int) $value]);
            } elseif ($field === 'capacity') {
                $request->merge(['capacity' => null]);
            }
        }

        $validated = $request->validate([
            'arkfleet_equipment_id' => ['nullable', 'integer'],
            'kode' => [
                'required',
                'string',
                'max:50',
                Rule::unique('vehicles', 'kode')->ignore($vehicle?->id),
            ],
            'license_plate' => [
                'required',
                'string',
                'max:20',
                Rule::unique('vehicles', 'license_plate')->ignore($vehicle?->id),
            ],
            'pic' => ['nullable', 'string', 'max:255'],
            'lokasi' => ['nullable', 'string', 'max:100'],
            'keterangan' => ['nullable', 'string'],
            'brand' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:255'],
            'year' => ['nullable', 'integer', 'min:1980', 'max:2100'],
            'color' => ['nullable', 'string', 'max:50'],
            'type' => ['required', Rule::in(['sedan', 'suv', 'mpv', 'truck', 'bus', 'motorcycle', 'pickup', 'other'])],
            'ownership' => ['required', Rule::in(['company', 'rental', 'employee'])],
            'vin' => ['nullable', 'string', 'max:100'],
            'engine_number' => ['nullable', 'string', 'max:100'],
            'transmission' => ['nullable', Rule::in(['manual', 'automatic'])],
            'fuel_type' => ['nullable', Rule::in(['gasoline', 'diesel', 'electric', 'hybrid', 'other'])],
            'capacity' => ['nullable', 'integer', 'min:1'],
            'status' => ['required', Rule::in(['active', 'inactive', 'maintenance', 'sold', 'accident'])],
            'odometer' => ['nullable', 'integer', 'min:0'],
            'arkfleet_status' => ['nullable', 'string', 'max:50'],
            'project_code' => ['nullable', 'string', 'max:50'],
            'stnk_expiry' => ['nullable', 'date'],
            'pkb_expiry' => ['nullable', 'date'],
            'kir_expiry' => ['nullable', 'date'],
        ], [
            'kode.required' => 'Vehicle code is required (from ArkFleet).',
            'license_plate.required' => 'License plate is required.',
        ]);

        if (! empty($validated['arkfleet_equipment_id'])) {
            $validated['arkfleet_sync_at'] = now();
        }

        unset($validated['stnk_expiry'], $validated['pkb_expiry'], $validated['kir_expiry']);

        return $validated;
    }

    protected function syncCoreDocuments(Vehicle $vehicle, Request $request): void
    {
        foreach (['stnk' => 'STNK & Plate', 'pkb' => 'PKB', 'kir' => 'KIR'] as $type => $label) {
            $field = $type.'_expiry';
            if (! $request->filled($field)) {
                continue;
            }

            $expiry = $request->input($field);
            $doc = $vehicle->documents()
                ->where('document_type', $type)
                ->whereIn('status', ['active', 'expired', 'pending_renewal'])
                ->orderByDesc('expiry_date')
                ->first();

            $status = \Carbon\Carbon::parse($expiry)->lt(now()->startOfDay()) ? 'expired' : 'active';

            if ($doc) {
                $doc->update([
                    'expiry_date' => $expiry,
                    'status' => $status,
                    'document_name' => $label,
                ]);
            } else {
                VehicleDocument::create([
                    'vehicle_id' => $vehicle->id,
                    'document_type' => $type,
                    'document_name' => $label,
                    'expiry_date' => $expiry,
                    'status' => $status,
                    'created_by' => Auth::id(),
                ]);
            }
        }
    }
}
