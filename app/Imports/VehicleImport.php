<?php

namespace App\Imports;

use App\Models\Vehicle;
use App\Models\VehicleDocument;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Maatwebsite\Excel\Concerns\SkipsFailures;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsOnFailure;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Validators\Failure;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class VehicleImport implements OnEachRow, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    use Importable, SkipsErrors, SkipsFailures;

    public int $created = 0;

    public int $updated = 0;

    /**
     * Computed export-only columns (ignored on import).
     *
     * @var list<string>
     */
    protected array $ignoredColumns = [
        'stnk_days_remaining',
        'pkb_days_remaining',
        'kir_days_remaining',
    ];

    public function headingRow(): int
    {
        return 1;
    }

    public function onRow(Row $row): void
    {
        $data = $row->toArray();
        // Days-remaining columns are export-only; never used for create/update.
        foreach ($this->ignoredColumns as $column) {
            unset($data[$column]);
        }
        $rowNumber = $row->getIndex();

        $kode = trim((string) ($data['kode'] ?? ''));
        $plate = trim((string) ($data['license_plate'] ?? ''));

        if ($kode === '' && $plate === '') {
            return;
        }

        if ($kode === '' || $plate === '') {
            $this->onFailure(new Failure(
                $rowNumber,
                $kode === '' ? 'kode' : 'license_plate',
                ['Both kode and license_plate are required to create or update a vehicle.'],
                $data
            ));

            return;
        }

        try {
            DB::beginTransaction();

            $vehicle = Vehicle::where('kode', $kode)->first()
                ?? Vehicle::where('license_plate', $plate)->first();

            $payload = [
                'kode' => $kode,
                'license_plate' => $plate,
                'pic' => $this->nullableString($data['pic'] ?? null),
                'lokasi' => $this->nullableString($data['lokasi'] ?? null),
                'keterangan' => $this->nullableString($data['keterangan'] ?? null),
                'status' => $this->normalizeEnum($data['status'] ?? null, ['active', 'inactive', 'maintenance', 'sold', 'accident'], 'active'),
                'type' => $this->normalizeEnum($data['type'] ?? null, ['sedan', 'suv', 'mpv', 'truck', 'bus', 'motorcycle', 'pickup', 'other'], 'other'),
                'ownership' => $this->normalizeEnum($data['ownership'] ?? null, ['company', 'rental', 'employee'], 'company'),
                'fuel_type' => $this->normalizeEnum($data['fuel_type'] ?? null, ['gasoline', 'diesel', 'electric', 'hybrid', 'other'], null),
                'transmission' => $this->normalizeEnum($data['transmission'] ?? null, ['manual', 'automatic'], null),
                'year' => $this->nullableInt($data['year'] ?? null),
                'odometer' => $this->nullableInt($data['odometer'] ?? null) ?? 0,
                'brand' => $this->nullableString($data['brand'] ?? null),
                'model' => $this->nullableString($data['model'] ?? null),
                'project_code' => $this->nullableString($data['project_code'] ?? null),
            ];

            if ($vehicle) {
                // Avoid unique conflicts when swapping identifiers across rows.
                $kodeTaken = Vehicle::where('kode', $kode)->where('id', '!=', $vehicle->id)->exists();
                $plateTaken = Vehicle::where('license_plate', $plate)->where('id', '!=', $vehicle->id)->exists();
                if ($kodeTaken || $plateTaken) {
                    $this->onFailure(new Failure(
                        $rowNumber,
                        $kodeTaken ? 'kode' : 'license_plate',
                        ['Another vehicle already uses this kode or license_plate.'],
                        $data
                    ));
                    DB::rollBack();

                    return;
                }

                $vehicle->update($payload);
                $this->updated++;
            } else {
                $vehicle = Vehicle::create($payload);
                $this->created++;
            }

            $this->syncCoreDocumentDates($vehicle, [
                'stnk' => $this->parseDate($data['stnk_expiry'] ?? null),
                'pkb' => $this->parseDate($data['pkb_expiry'] ?? null),
                'kir' => $this->parseDate($data['kir_expiry'] ?? null),
            ]);

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->onFailure(new Failure(
                $rowNumber,
                'system_error',
                [$e->getMessage()],
                $data
            ));
        }
    }

    public function rules(): array
    {
        return [
            'kode' => ['nullable', 'string', 'max:50'],
            'license_plate' => ['nullable', 'string', 'max:20'],
            'status' => ['nullable', 'string'],
            'type' => ['nullable', 'string'],
            'ownership' => ['nullable', 'string'],
        ];
    }

    /**
     * @param  array<string, ?string>  $expiries
     */
    protected function syncCoreDocumentDates(Vehicle $vehicle, array $expiries): void
    {
        foreach (['stnk' => 'STNK & Plate', 'pkb' => 'PKB', 'kir' => 'KIR'] as $type => $label) {
            $expiry = $expiries[$type] ?? null;
            if (! $expiry) {
                continue;
            }

            $doc = $vehicle->documents()
                ->where('document_type', $type)
                ->whereIn('status', ['active', 'expired', 'pending_renewal'])
                ->orderByDesc('expiry_date')
                ->first();

            $status = Carbon::parse($expiry)->lt(now()->startOfDay()) ? 'expired' : 'active';

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

    protected function parseDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        try {
            if ($value instanceof \DateTimeInterface) {
                return Carbon::parse($value)->toDateString();
            }

            if (is_numeric($value)) {
                return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value))->toDateString();
            }

            return Carbon::parse((string) $value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }

    protected function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    protected function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }

    /**
     * @param  list<string>  $allowed
     */
    protected function normalizeEnum(mixed $value, array $allowed, ?string $default): ?string
    {
        if ($value === null || $value === '') {
            return $default;
        }

        $normalized = strtolower(trim((string) $value));

        return in_array($normalized, $allowed, true) ? $normalized : $default;
    }
}
