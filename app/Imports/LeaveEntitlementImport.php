<?php

namespace App\Imports;

use App\Models\Administration;
use App\Models\LeaveEntitlement;
use App\Models\LeaveType;
use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class LeaveEntitlementImport implements ToCollection
{
    private $leaveTypes;

    private $errors = [];

    private $successCount = 0;

    private $skippedCount = 0;

    public function __construct()
    {
        $this->leaveTypes = LeaveType::where('is_active', true)
            ->where(function ($query) {
                $query->where('name', '!=', 'Cuti Periodik Site')
                    ->where('name', 'NOT LIKE', '%Periodik Site%');
            })
            ->orderBy('code')
            ->get();

        Log::info('Leave Types loaded for import', [
            'types' => $this->leaveTypes->map(fn ($lt) => [
                'id' => $lt->id,
                'name' => $lt->name,
                'category' => $lt->category,
            ])->toArray(),
        ]);
    }

    public function collection(Collection $rows)
    {
        if ($rows->isEmpty()) {
            return;
        }

        $headerRowIndex = $this->detectHeaderRowIndex($rows);
        $groupRow = $headerRowIndex > 0 ? $rows[$headerRowIndex - 1]->toArray() : [];
        $detailRow = $rows[$headerRowIndex]->toArray();
        $columnKeys = $this->buildColumnKeys($groupRow, $detailRow);

        Log::info('Leave entitlement import header detected', [
            'header_row_index' => $headerRowIndex,
            'column_keys' => $columnKeys,
        ]);

        $rowNumber = $headerRowIndex + 2;

        for ($i = $headerRowIndex + 1; $i < $rows->count(); $i++) {
            $rowValues = $rows[$i]->toArray();
            $rowArray = $this->mapRowValues($columnKeys, $rowValues);

            if ($this->isEmptyRow($rowArray)) {
                $rowNumber++;

                continue;
            }

            DB::beginTransaction();
            try {
                $result = $this->processRow($rowArray, $rowNumber);

                if ($result['success']) {
                    DB::commit();
                    $this->successCount++;
                } else {
                    DB::rollBack();
                    $this->skippedCount++;
                    $this->errors[] = [
                        'row' => $rowNumber,
                        'nik' => $this->resolveNikFromRow($rowArray),
                        'errors' => $result['errors'],
                    ];
                }
            } catch (\Throwable $e) {
                DB::rollBack();
                $this->skippedCount++;
                Log::error("Import error at row {$rowNumber}", [
                    'exception' => $e,
                ]);
                $this->errors[] = [
                    'row' => $rowNumber,
                    'nik' => $this->resolveNikFromRow($rowArray),
                    'errors' => [$this->humanReadableImportError($e)],
                ];
            }

            $rowNumber++;
        }

        Log::info('Leave Entitlement Import Completed', [
            'total_rows' => max(0, $rowNumber - $headerRowIndex - 2),
            'success' => $this->successCount,
            'skipped' => $this->skippedCount,
            'errors' => count($this->errors),
        ]);
    }

    private function processRow(array $row, int $rowNumber): array
    {
        $nik = $this->resolveNikFromRow($row);
        if ($nik === null || $nik === '') {
            return ['success' => false, 'errors' => ['NIK is required']];
        }

        $administration = Administration::where('nik', $nik)
            ->where('is_active', 1)
            ->first();

        if (! $administration || ! $administration->employee) {
            return ['success' => false, 'errors' => ["NIK '{$nik}' not found or not active"]];
        }

        if ($this->isCompactLayout($row)) {
            return $this->processCompactRow($row, $administration, $administration->employee);
        }

        return $this->processLegacyRow($row, $administration, $administration->employee);
    }

    /**
     * One row per employee: annual block (G-I) + LSL block (J-N).
     */
    private function processCompactRow(array $row, Administration $administration, $employee): array
    {
        $errors = [];
        $processed = false;

        $depositDays = (int) ($this->getColumnValue($row, 'deposit_days') ?? 0);
        if ($depositDays < 0) {
            return ['success' => false, 'errors' => ['Deposit Days cannot be negative']];
        }

        $annualStart = $this->getAnnualStartPeriod($row);
        $annualEnd = $this->getAnnualEndPeriod($row);
        $cutiTahunan = $this->getColumnValue($row, 'cuti_tahunan');

        $lslStart = $this->getLslStartPeriod($row);
        $lslEnd = $this->getLslEndPeriod($row);
        $lslStaff = $this->getColumnValue($row, 'cuti_panjang_staff');
        $lslNonStaff = $this->getColumnValue($row, 'cuti_panjang_non_staff');

        $hasAnnualData = $this->hasValue($annualStart) || $this->hasValue($annualEnd) || $this->hasValue($cutiTahunan);
        $hasLslData = $this->hasValue($lslStart) || $this->hasValue($lslEnd)
            || $this->hasValue($lslStaff) || $this->hasValue($lslNonStaff) || $depositDays > 0;

        if ($hasAnnualData) {
            if (! $this->hasValue($annualStart) || ! $this->hasValue($annualEnd)) {
                $errors[] = 'Start Period and End Period (Periode Tahunan) are required when updating Cuti Tahunan';
            } else {
                $periodStart = $this->parseDate($annualStart);
                $periodEnd = $this->parseDate($annualEnd);

                if (! $periodStart || ! $periodEnd) {
                    $errors[] = 'Invalid annual period date format. Use e.g. 13 Dec 2025, DD/MM/YYYY, or YYYY-MM-DD';
                } elseif ($periodStart >= $periodEnd) {
                    $errors[] = 'Annual Start Period must be before End Period';
                } else {
                    $this->importAnnualBlock($employee, $periodStart, $periodEnd, $cutiTahunan);
                    $processed = true;
                }
            }
        }

        if ($hasLslData) {
            if (! $this->hasValue($lslStart) || ! $this->hasValue($lslEnd)) {
                $errors[] = 'Start Period and End Period (Periode Cuti Panjang) are required when updating Cuti Panjang';
            } else {
                $periodStart = $this->parseDate($lslStart);
                $periodEnd = $this->parseDate($lslEnd);

                if (! $periodStart || ! $periodEnd) {
                    $errors[] = 'Invalid LSL period date format. Use e.g. 13 Dec 2024, DD/MM/YYYY, or YYYY-MM-DD';
                } elseif ($periodStart >= $periodEnd) {
                    $errors[] = 'LSL Start Period must be before End Period';
                } else {
                    $this->importLslBlock($employee, $administration, $periodStart, $periodEnd, $lslStaff, $lslNonStaff, $depositDays);
                    $processed = true;
                }
            }
        }

        if (! empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        if (! $processed) {
            return ['success' => false, 'errors' => ['No entitlement data to import for this row']];
        }

        return ['success' => true, 'errors' => []];
    }

    private function importAnnualBlock($employee, Carbon $periodStart, Carbon $periodEnd, mixed $cutiTahunanRaw): void
    {
        foreach ($this->leaveTypes as $leaveType) {
            if (! in_array($leaveType->category, ['annual', 'paid', 'unpaid'], true)) {
                continue;
            }

            $existing = $this->findEntitlementForPeriod(
                $employee->id,
                (int) $leaveType->id,
                $periodStart,
                $periodEnd,
            );

            if ($leaveType->category === 'annual') {
                $entitledDays = $this->normalizeEntitledDays($cutiTahunanRaw);
            } else {
                if (! $existing) {
                    continue;
                }
                $entitledDays = (int) $existing->entitled_days;
            }

            $takenDays = $existing ? (int) $existing->taken_days : 0;
            $entitledDays = max($entitledDays, $takenDays);

            if ($entitledDays === 0 && ! $existing && $leaveType->category !== 'annual') {
                continue;
            }

            if ($entitledDays === 0 && ! $existing && $leaveType->category === 'annual' && ! $this->hasValue($cutiTahunanRaw)) {
                continue;
            }

            $this->saveImportedEntitlement(
                $employee->id,
                (int) $leaveType->id,
                $periodStart,
                $periodEnd,
                $entitledDays,
                $takenDays,
                0,
            );
        }
    }

    private function importLslBlock(
        $employee,
        Administration $administration,
        Carbon $periodStart,
        Carbon $periodEnd,
        mixed $lslStaffRaw,
        mixed $lslNonStaffRaw,
        int $depositDays,
    ): void {
        $levelName = $administration->level?->name ?? '';
        $isStaff = $this->isStaffLevel($levelName);

        foreach ($this->leaveTypes as $leaveType) {
            if ($leaveType->category !== 'lsl') {
                continue;
            }

            $isCutiPanjangStaff = stripos($leaveType->name, 'Staff') !== false
                && stripos($leaveType->name, 'Non') === false;
            $isCutiPanjangNonStaff = stripos($leaveType->name, 'Non Staff') !== false;

            if ($isStaff && ! $isCutiPanjangStaff) {
                continue;
            }
            if (! $isStaff && ! $isCutiPanjangNonStaff) {
                continue;
            }

            $rawValue = $isCutiPanjangStaff ? $lslStaffRaw : $lslNonStaffRaw;
            $existing = $this->findEntitlementForPeriod(
                $employee->id,
                (int) $leaveType->id,
                $periodStart,
                $periodEnd,
            );

            if (! $this->hasValue($rawValue) && ! $existing && $depositDays === 0) {
                continue;
            }

            $entitledDays = $this->hasValue($rawValue)
                ? $this->normalizeEntitledDays($rawValue)
                : ($existing ? (int) $existing->entitled_days : 0);

            $takenDays = $existing ? (int) $existing->taken_days : 0;
            $entitledDays = max($entitledDays, $takenDays);

            if ($entitledDays === 0 && ! $existing && $depositDays === 0) {
                continue;
            }

            $this->saveImportedEntitlement(
                $employee->id,
                (int) $leaveType->id,
                $periodStart,
                $periodEnd,
                $entitledDays,
                $takenDays,
                $depositDays,
            );
        }
    }

    private function processLegacyRow(array $row, Administration $administration, $employee): array
    {
        $startPeriod = $this->getColumnValue($row, 'start_period')
            ?: $this->getColumnValue($row, 'start period')
            ?: $this->getColumnValue($row, 'Start Period');
        $endPeriod = $this->getColumnValue($row, 'end_period')
            ?: $this->getColumnValue($row, 'end period')
            ?: $this->getColumnValue($row, 'End Period');

        if (empty($startPeriod) || empty($endPeriod)) {
            return ['success' => false, 'errors' => ['Start Period and End Period are required']];
        }

        $periodStart = $this->parseDate($startPeriod);
        $periodEnd = $this->parseDate($endPeriod);

        if (! $periodStart || ! $periodEnd) {
            return ['success' => false, 'errors' => ['Invalid date format. Use e.g. 01 Jan 2026, DD/MM/YYYY, or YYYY-MM-DD']];
        }

        if ($periodStart >= $periodEnd) {
            return ['success' => false, 'errors' => ['Start Period must be before End Period']];
        }

        $periodType = $this->resolvePeriodTypeFromRow($row);
        $depositDays = (int) ($this->getColumnValue($row, 'deposit_days') ?? 0);

        if ($depositDays < 0) {
            return ['success' => false, 'errors' => ['Deposit Days cannot be negative']];
        }

        foreach ($this->leaveTypes as $leaveType) {
            if (! $this->shouldProcessLeaveTypeForPeriod($leaveType, $periodType)) {
                continue;
            }

            $entitledDays = $this->getLeaveTypeValue($row, $leaveType->name);
            $entitledDays = $this->normalizeEntitledDays($entitledDays);

            $isCutiPanjang = stripos($leaveType->name, 'Cuti Panjang') !== false;

            if ($isCutiPanjang) {
                $levelName = $administration->level?->name ?? '';
                $isStaff = $this->isStaffLevel($levelName);
                $isCutiPanjangStaff = stripos($leaveType->name, 'Staff') !== false
                    && stripos($leaveType->name, 'Non') === false;
                $isCutiPanjangNonStaff = stripos($leaveType->name, 'Non Staff') !== false;

                if ($isStaff && ! $isCutiPanjangStaff) {
                    continue;
                }
                if (! $isStaff && ! $isCutiPanjangNonStaff) {
                    continue;
                }
            }

            $existing = $this->findEntitlementForPeriod(
                $employee->id,
                (int) $leaveType->id,
                $periodStart,
                $periodEnd,
            );

            $takenDays = $existing ? (int) $existing->taken_days : 0;
            $entitledDays = max($entitledDays, $takenDays);

            $isPaidOrUnpaid = in_array($leaveType->category, ['paid', 'unpaid'], true);
            if ($entitledDays === 0 && ! $existing && ! $isPaidOrUnpaid) {
                continue;
            }

            $finalDepositDays = ($leaveType->category === 'lsl') ? $depositDays : 0;

            $this->saveImportedEntitlement(
                $employee->id,
                (int) $leaveType->id,
                $periodStart,
                $periodEnd,
                $entitledDays,
                $takenDays,
                $finalDepositDays,
            );
        }

        return ['success' => true, 'errors' => []];
    }

    private function detectHeaderRowIndex(Collection $rows): int
    {
        foreach ($rows as $index => $row) {
            $values = array_map(
                fn ($value) => strtolower(trim((string) $value)),
                $row->toArray()
            );

            $hasCutiTahunan = $this->rowContains($values, 'cuti tahunan');
            $hasTipePeriode = $this->rowContains($values, 'tipe periode');
            $hasStartPeriod = $this->rowContains($values, 'start period');

            if ($hasCutiTahunan || $hasTipePeriode || ($hasStartPeriod && $index > 0)) {
                return $index;
            }
        }

        foreach ($rows as $index => $row) {
            $values = array_map(
                fn ($value) => strtolower(trim((string) $value)),
                $row->toArray()
            );

            if (in_array('nik', $values, true)) {
                return $index;
            }
        }

        return min(1, max(0, $rows->count() - 1));
    }

    /**
     * @return list<string>
     */
    private function buildColumnKeys(array $groupRow, array $detailRow): array
    {
        $keys = [];
        $startPeriodCount = 0;
        $endPeriodCount = 0;
        $maxColumns = max(count($groupRow), count($detailRow));

        for ($index = 0; $index < $maxColumns; $index++) {
            $headerText = trim((string) ($detailRow[$index] ?? ''));

            if ($headerText === '') {
                $headerText = trim((string) ($groupRow[$index] ?? ''));
            }

            $normalized = $this->normalizeColumnName($headerText);

            if ($normalized === 'start_period') {
                $keys[] = $startPeriodCount === 0 ? 'start_period' : 'start_period_'.$startPeriodCount;
                $startPeriodCount++;

                continue;
            }

            if ($normalized === 'end_period') {
                $keys[] = $endPeriodCount === 0 ? 'end_period' : 'end_period_'.$endPeriodCount;
                $endPeriodCount++;

                continue;
            }

            $keys[] = $normalized !== '' ? $normalized : '__empty_'.count($keys);
        }

        return $keys;
    }

    private function mapRowValues(array $columnKeys, array $rowValues): array
    {
        $mapped = [];

        foreach ($columnKeys as $index => $key) {
            if (str_starts_with($key, '__empty_')) {
                continue;
            }
            $mapped[$key] = $rowValues[$index] ?? null;
        }

        return $mapped;
    }

    private function isCompactLayout(array $row): bool
    {
        if ($this->resolvePeriodTypeFromRow($row) !== null) {
            return false;
        }

        return array_key_exists('cuti_tahunan', $row)
            || array_key_exists('cuti_panjang_staff', $row)
            || array_key_exists('cuti_panjang_non_staff', $row)
            || array_key_exists('start_period_1', $row);
    }

    private function getAnnualStartPeriod(array $row): mixed
    {
        return $this->getColumnValue($row, 'start_period')
            ?: $this->getColumnByIndex($row, 6);
    }

    private function getAnnualEndPeriod(array $row): mixed
    {
        return $this->getColumnValue($row, 'end_period')
            ?: $this->getColumnByIndex($row, 7);
    }

    private function getLslStartPeriod(array $row): mixed
    {
        return $this->getColumnValue($row, 'start_period_1')
            ?: $this->getColumnByIndex($row, 9);
    }

    private function getLslEndPeriod(array $row): mixed
    {
        return $this->getColumnValue($row, 'end_period_1')
            ?: $this->getColumnByIndex($row, 10);
    }

    private function getColumnByIndex(array $row, int $index): mixed
    {
        $values = array_values($row);

        return $values[$index] ?? null;
    }

    private function normalizeEntitledDays(mixed $value): int
    {
        if ($value === null || $value === '') {
            return 0;
        }

        if (! is_numeric($value)) {
            return 0;
        }

        return max(0, (int) $value);
    }

    private function hasValue(mixed $value): bool
    {
        return $value !== null && trim((string) $value) !== '';
    }

    private function rowContains(array $values, string $needle): bool
    {
        foreach ($values as $value) {
            if (str_contains($value, $needle)) {
                return true;
            }
        }

        return false;
    }

    private function findEntitlementForPeriod(
        string $employeeId,
        int $leaveTypeId,
        Carbon $periodStart,
        Carbon $periodEnd,
    ): ?LeaveEntitlement {
        return LeaveEntitlement::query()
            ->where('employee_id', $employeeId)
            ->where('leave_type_id', $leaveTypeId)
            ->whereDate('period_start', $periodStart)
            ->whereDate('period_end', $periodEnd)
            ->first();
    }

    private function saveImportedEntitlement(
        string $employeeId,
        int $leaveTypeId,
        Carbon $periodStart,
        Carbon $periodEnd,
        int $entitledDays,
        int $takenDays,
        int $finalDepositDays,
    ): void {
        $model = $this->findEntitlementForPeriod($employeeId, $leaveTypeId, $periodStart, $periodEnd);

        $values = [
            'entitled_days' => $entitledDays,
            'taken_days' => $takenDays,
            'deposit_days' => $finalDepositDays,
        ];

        if ($model) {
            $model->fill($values);
            $model->save();

            return;
        }

        try {
            LeaveEntitlement::create(array_merge([
                'employee_id' => $employeeId,
                'leave_type_id' => $leaveTypeId,
                'period_start' => $periodStart->toDateString(),
                'period_end' => $periodEnd->toDateString(),
            ], $values));
        } catch (UniqueConstraintViolationException $e) {
            $this->syncEntitlementAfterConflict($employeeId, $leaveTypeId, $periodStart, $periodEnd, $values, $e);
        } catch (QueryException $e) {
            if ($this->isUniquePeriodConstraintViolation($e)) {
                $this->syncEntitlementAfterConflict($employeeId, $leaveTypeId, $periodStart, $periodEnd, $values, $e);

                return;
            }
            throw $e;
        }
    }

    private function syncEntitlementAfterConflict(
        string $employeeId,
        int $leaveTypeId,
        Carbon $periodStart,
        Carbon $periodEnd,
        array $values,
        \Throwable $original,
    ): void {
        $retry = $this->findEntitlementForPeriod($employeeId, $leaveTypeId, $periodStart, $periodEnd);
        if ($retry) {
            $retry->fill($values);
            $retry->save();

            return;
        }

        throw $original;
    }

    private function isUniquePeriodConstraintViolation(QueryException $e): bool
    {
        $msg = $e->getMessage();

        if (str_contains($msg, 'leave_ent_unique_period')) {
            return true;
        }

        return str_contains($msg, 'leave_entitlements')
            && (str_contains($msg, 'Duplicate entry')
                || str_contains($msg, '1062')
                || str_contains($msg, 'UNIQUE constraint failed'));
    }

    private function humanReadableImportError(\Throwable $e): string
    {
        if ($this->isThrowableUniquePeriodViolation($e)) {
            return 'Data hak cuti untuk kombinasi karyawan (NIK), jenis cuti, dan periode tanggal tersebut sudah ada—biasanya baris ini harusnya diperbarui otomatis. '
                .'Jika pesan ini tetap muncul, coba impor ulang; bila masih gagal, hubungi administrator. '
                .'(Memilih project di layar entitlement tidak mempengaruhi impor file Excel.)';
        }

        return 'Gagal memproses baris impor karena kesalahan sistem. Detail teknis sudah dicatat di log aplikasi.';
    }

    private function isThrowableUniquePeriodViolation(\Throwable $e): bool
    {
        if ($e instanceof UniqueConstraintViolationException) {
            return true;
        }

        if ($e instanceof QueryException && $this->isUniquePeriodConstraintViolation($e)) {
            return true;
        }

        $msg = $e->getMessage();

        return str_contains($msg, 'leave_ent_unique_period')
            || (str_contains($msg, 'Duplicate entry') && str_contains($msg, 'leave_entitlements'));
    }

    private function resolvePeriodTypeFromRow(array $row): ?string
    {
        $raw = $this->getColumnValue($row, 'tipe_periode')
            ?: $this->getColumnValue($row, 'tipe periode')
            ?: $this->getColumnValue($row, 'Tipe Periode');

        if ($raw === null || trim((string) $raw) === '') {
            return null;
        }

        $normalized = strtolower(trim((string) $raw));

        if (str_contains($normalized, 'panjang') || $normalized === 'lsl') {
            return 'lsl';
        }

        if (str_contains($normalized, 'tahunan') || $normalized === 'annual') {
            return 'annual';
        }

        return null;
    }

    private function shouldProcessLeaveTypeForPeriod(LeaveType $leaveType, ?string $periodType): bool
    {
        if ($periodType === null) {
            return true;
        }

        if ($periodType === 'lsl') {
            return $leaveType->category === 'lsl';
        }

        return in_array($leaveType->category, ['annual', 'paid', 'unpaid'], true);
    }

    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if ($this->hasValue($value)) {
                return false;
            }
        }

        return true;
    }

    private function resolveNikFromRow(array $row): ?string
    {
        $candidates = [
            $this->getColumnValue($row, 'nik'),
        ];

        foreach (['NIK', 'nik'] as $key) {
            if (isset($row[$key]) && $row[$key] !== null && $row[$key] !== '') {
                $candidates[] = $row[$key];
            }
        }

        foreach ($candidates as $raw) {
            $normalized = $this->normalizeNikCellValue($raw);
            if ($normalized !== null && $normalized !== '') {
                return $normalized;
            }
        }

        $sequential = array_values($row);
        if (isset($sequential[1])) {
            $normalized = $this->normalizeNikCellValue($sequential[1]);
            if ($normalized !== null && $normalized !== '') {
                return $normalized;
            }
        }

        return null;
    }

    private function normalizeNikCellValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_string($value)) {
            $trimmed = trim($value);

            return $trimmed === '' ? null : $trimmed;
        }

        if (is_int($value)) {
            return (string) $value;
        }

        if (is_float($value)) {
            $rounded = round($value, 0);
            if (abs($value - $rounded) < 0.000001) {
                return (string) (int) $rounded;
            }

            return trim((string) $value);
        }

        if (is_numeric($value)) {
            return (string) $value;
        }

        $asString = trim((string) $value);

        return $asString === '' ? null : $asString;
    }

    private function getColumnValue(array $row, string $columnName): mixed
    {
        $normalized = $this->normalizeColumnName($columnName);

        if (array_key_exists($normalized, $row)) {
            return $row[$normalized];
        }

        if (array_key_exists($columnName, $row)) {
            return $row[$columnName];
        }

        $variations = [
            strtolower($columnName),
            str_replace('_', ' ', strtolower($columnName)),
            ucwords(str_replace('_', ' ', $columnName)),
        ];

        foreach ($variations as $variation) {
            if (array_key_exists($variation, $row)) {
                return $row[$variation];
            }
        }

        return null;
    }

    private function getLeaveTypeValue(array $row, string $leaveTypeName): mixed
    {
        static $loggedColumns = false;
        if (! $loggedColumns) {
            Log::info('Excel columns detected', [
                'columns' => array_keys($row),
            ]);
            $loggedColumns = true;
        }

        $variations = $this->generateColumnVariations($leaveTypeName);
        foreach ($variations as $variation) {
            if (array_key_exists($variation, $row)) {
                return $row[$variation];
            }
        }

        $cleanName = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $leaveTypeName));
        foreach (array_keys($row) as $column) {
            $cleanColumn = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $column));
            if ($cleanColumn === $cleanName) {
                return $row[$column];
            }
        }

        return null;
    }

    private function generateColumnVariations(string $name): array
    {
        $variations = [];

        $variations[] = $name;
        $variations[] = strtolower($name);

        $laravelExcelActual = str_replace('/', '', $name);
        $laravelExcelActual = str_replace(' ', '_', $laravelExcelActual);
        $laravelExcelActual = str_replace('-', '_', $laravelExcelActual);
        $laravelExcelActual = preg_replace('/_+/', '_', strtolower($laravelExcelActual));
        $laravelExcelActual = trim($laravelExcelActual, '_');
        $variations[] = $laravelExcelActual;

        $slug = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $name));
        $variations[] = trim($slug, '-');

        $underscore = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $name));
        $variations[] = trim($underscore, '_');

        $variations[] = $this->normalizeColumnName($name);

        $dashNormalized = str_replace([' ', '/', '-'], '-', $name);
        $dashNormalized = preg_replace('/-+/', '-', strtolower($dashNormalized));
        $variations[] = trim($dashNormalized, '-');

        $variations[] = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $name));
        $variations[] = str_replace(' ', '_', strtolower($name));

        $allToUnderscore = preg_replace('/[^a-zA-Z0-9]+/', '_', $name);
        $allToUnderscore = preg_replace('/_+/', '_', strtolower($allToUnderscore));
        $variations[] = trim($allToUnderscore, '_');

        $allToDash = preg_replace('/[^a-zA-Z0-9]+/', '-', $name);
        $allToDash = preg_replace('/-+/', '-', strtolower($allToDash));
        $variations[] = trim($allToDash, '-');

        return array_values(array_filter(array_unique($variations), fn ($v) => ! empty($v)));
    }

    private function isStaffLevel(string $levelName): bool
    {
        if ($levelName === '') {
            return false;
        }

        $staffLevels = [
            'Director',
            'Manager',
            'Superintendent',
            'Supervisor',
            'Foreman/Officer',
            'Project Manager',
            'SPT',
            'SPV',
            'FM',
        ];

        return in_array($levelName, $staffLevels, true);
    }

    private function normalizeColumnName(string $name): string
    {
        $normalized = str_replace([' ', '/', '-'], '_', $name);
        $normalized = preg_replace('/_+/', '_', $normalized);
        $normalized = strtolower($normalized);

        return trim($normalized, '_');
    }

    private function parseDate(mixed $dateValue): ?Carbon
    {
        if (empty($dateValue)) {
            return null;
        }

        $dateValue = trim((string) $dateValue);

        if (is_numeric($dateValue)) {
            try {
                $dateTime = Date::excelToDateTimeObject((float) $dateValue);

                return Carbon::instance($dateTime)->startOfDay();
            } catch (\Exception $e) {
                if (strlen($dateValue) == 8 && is_numeric($dateValue)) {
                    try {
                        return Carbon::createFromFormat('Ymd', $dateValue)->startOfDay();
                    } catch (\Exception $e2) {
                    }
                }
            }
        }

        if (preg_match('/^(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{4})$/', $dateValue, $matches)) {
            $firstPart = (int) $matches[1];
            $secondPart = (int) $matches[2];
            $separator = $matches[0][strpos($matches[0], $matches[1]) + strlen($matches[1])];

            if ($firstPart > 12) {
                try {
                    $date = Carbon::createFromFormat('d'.$separator.'m'.$separator.'Y', $dateValue);
                    if ($date && $date->year >= 1900 && $date->year <= 2100) {
                        return $date->startOfDay();
                    }
                } catch (\Exception $e) {
                }
            } elseif ($secondPart > 12) {
                try {
                    $date = Carbon::createFromFormat('m'.$separator.'d'.$separator.'Y', $dateValue);
                    if ($date && $date->year >= 1900 && $date->year <= 2100) {
                        return $date->startOfDay();
                    }
                } catch (\Exception $e) {
                }
            } else {
                try {
                    $date = Carbon::createFromFormat('d'.$separator.'m'.$separator.'Y', $dateValue);
                    if ($date && $date->year >= 1900 && $date->year <= 2100 && $date->day <= 31 && $date->month <= 12) {
                        return $date->startOfDay();
                    }
                } catch (\Exception $e) {
                    try {
                        $date = Carbon::createFromFormat('m'.$separator.'d'.$separator.'Y', $dateValue);
                        if ($date && $date->year >= 1900 && $date->year <= 2100) {
                            return $date->startOfDay();
                        }
                    } catch (\Exception $e2) {
                    }
                }
            }
        }

        foreach (['d M Y', 'j M Y', 'd M, Y'] as $format) {
            try {
                $date = Carbon::createFromFormat($format, $dateValue);
                if ($date && $date->year >= 1900 && $date->year <= 2100) {
                    return $date->startOfDay();
                }
            } catch (\Exception $e) {
            }
        }

        $formats = [
            'd/m/Y', 'd-m-Y', 'd.m.Y', 'Y-m-d', 'Y/m/d', 'Y.m.d', 'Ymd',
            'm/d/Y', 'm-d-Y', 'm.d.Y', 'd M Y', 'd F Y', 'M d, Y', 'F d, Y',
        ];

        foreach ($formats as $format) {
            try {
                $date = Carbon::createFromFormat($format, $dateValue);
                if ($date && $date->year >= 1900 && $date->year <= 2100) {
                    return $date->startOfDay();
                }
            } catch (\Exception $e) {
            }
        }

        if (preg_match('/(\d{1,2})[\/\-\.](\d{1,2})[\/\-\.](\d{4})/', $dateValue, $matches)) {
            try {
                $day = (int) $matches[1];
                $month = (int) $matches[2];
                $year = (int) $matches[3];
                if ($day >= 1 && $day <= 31 && $month >= 1 && $month <= 12 && $year >= 1900 && $year <= 2100) {
                    return Carbon::create($year, $month, $day)->startOfDay();
                }
            } catch (\Exception $e) {
            }
        }

        if (preg_match('/(\d{4})[\/\-\.](\d{1,2})[\/\-\.](\d{1,2})/', $dateValue, $matches)) {
            try {
                $year = (int) $matches[1];
                $month = (int) $matches[2];
                $day = (int) $matches[3];
                if ($day >= 1 && $day <= 31 && $month >= 1 && $month <= 12 && $year >= 1900 && $year <= 2100) {
                    return Carbon::create($year, $month, $day)->startOfDay();
                }
            } catch (\Exception $e) {
            }
        }

        return null;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function getSuccessCount(): int
    {
        return $this->successCount;
    }

    public function getSkippedCount(): int
    {
        return $this->skippedCount;
    }
}
