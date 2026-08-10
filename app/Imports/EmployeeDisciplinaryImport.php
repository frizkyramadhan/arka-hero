<?php

namespace App\Imports;

use App\Models\Administration;
use App\Models\DisciplinaryCriterion;
use App\Models\Employee;
use App\Models\EmployeeDisciplinary;
use App\Services\DisciplinaryService;
use App\Support\UserProject;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
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

class EmployeeDisciplinaryImport implements OnEachRow, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    use Importable, SkipsErrors, SkipsFailures;

    public int $created = 0;

    public int $skipped = 0;

    /**
     * Export-only columns ignored on import.
     *
     * @var list<string>
     */
    protected array $ignoredColumns = [
        'end_date',
        'remaining_days',
        'status',
        'imported',
        'imported_doc_later',
        'full_name',
    ];

    public function __construct(protected DisciplinaryService $service)
    {
    }

    public function headingRow(): int
    {
        return 1;
    }

    public function onRow(Row $row): void
    {
        $data = $row->toArray();
        foreach ($this->ignoredColumns as $column) {
            unset($data[$column]);
        }
        $rowNumber = $row->getIndex();

        $identityCard = $this->nullableString($this->castIdentifierToString($data['identity_card'] ?? null));
        $nik = $this->nullableString($this->castIdentifierToString($data['nik'] ?? null));
        $type = $this->normalizeType($data['type'] ?? null);
        $effectiveDate = $this->parseDate($data['effective_date'] ?? null);
        $reason = $this->nullableString($data['reason'] ?? null);
        $ppNotes = $this->nullableString($data['pp_notes'] ?? null);
        $criterionCodesRaw = $this->nullableString($data['criterion_codes'] ?? null);

        if ($identityCard === null && $nik === null && $type === null && $effectiveDate === null && $reason === null) {
            $this->skipped++;

            return;
        }

        if ($identityCard === null && $nik === null) {
            $this->fail($rowNumber, 'identity_card', ['Provide identity_card (NIK KTP) and/or nik (administration).'], $data);

            return;
        }

        if ($type === null) {
            $this->fail($rowNumber, 'type', ['Type is required (coaching, counseling, sp1, sp2, sp3).'], $data);

            return;
        }

        if ($effectiveDate === null) {
            $this->fail($rowNumber, 'effective_date', ['Effective date is required (Y-m-d).'], $data);

            return;
        }

        if ($reason === null) {
            $this->fail($rowNumber, 'reason', ['Reason / description is required.'], $data);

            return;
        }

        $employee = $this->resolveEmployee($identityCard, $nik);
        if (! $employee) {
            $this->fail($rowNumber, $identityCard ? 'identity_card' : 'nik', ['Employee not found for the given identity_card / nik.'], $data);

            return;
        }

        if (! UserProject::canViewEmployee($employee)) {
            $this->fail($rowNumber, 'identity_card', ['You do not have access to import disciplinary data for this employee.'], $data);

            return;
        }

        if ($this->service->requiresTermination($employee)) {
            $this->fail(
                $rowNumber,
                'type',
                ['Employee has an active First & Final Warning. Termination is required before adding another record.'],
                $data
            );

            return;
        }

        try {
            $criterionIds = $this->resolveCriterionIds($type, $criterionCodesRaw);
        } catch (ValidationException $e) {
            $messages = collect($e->errors())->flatten()->all();
            $this->fail($rowNumber, 'criterion_codes', $messages ?: ['Invalid criterion codes.'], $data);

            return;
        }

        try {
            $this->service->create(
                [
                    'employee_id' => $employee->id,
                    'type' => $type,
                    'effective_date' => $effectiveDate,
                    'reason' => $reason,
                    'pp_notes' => $ppNotes,
                    'created_by' => Auth::id(),
                    'imported_at' => now(),
                ],
                $criterionIds,
                null
            );
            $this->created++;
        } catch (ValidationException $e) {
            $attribute = array_key_first($e->errors()) ?: 'type';
            $messages = collect($e->errors())->flatten()->all();
            $this->fail($rowNumber, (string) $attribute, $messages, $data);
        } catch (\Throwable $e) {
            $this->fail($rowNumber, 'system_error', [$e->getMessage()], $data);
        }
    }

    public function rules(): array
    {
        return [
            'identity_card' => ['nullable', 'string', 'max:50'],
            'nik' => ['nullable', 'string', 'max:50'],
            'type' => ['nullable', 'string', 'max:50'],
            'effective_date' => ['nullable'],
            'reason' => ['nullable', 'string'],
            'pp_notes' => ['nullable', 'string'],
            'criterion_codes' => ['nullable', 'string'],
        ];
    }

    /**
     * Cast Excel numeric cells to string before WithValidation runs.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function prepareForValidation($data, $index)
    {
        foreach (['identity_card', 'nik'] as $key) {
            if (! array_key_exists($key, $data) || $data[$key] === null || $data[$key] === '') {
                continue;
            }

            $data[$key] = $this->castIdentifierToString($data[$key]);
        }

        return $data;
    }

    protected function resolveEmployee(?string $identityCard, ?string $nik): ?Employee
    {
        if ($identityCard) {
            $byCard = Employee::query()->where('identity_card', $identityCard)->first();
            if ($byCard) {
                return $byCard;
            }
        }

        if ($nik) {
            $administration = Administration::query()
                ->where('nik', $nik)
                ->orderByDesc('is_active')
                ->first();

            if ($administration) {
                return $administration->employee;
            }
        }

        return null;
    }

    /**
     * @return list<int>
     */
    protected function resolveCriterionIds(string $type, ?string $codesRaw): array
    {
        if ($codesRaw === null) {
            return [];
        }

        $codes = collect(preg_split('/[|,;]+/', $codesRaw) ?: [])
            ->map(fn ($code) => $this->normalizeCriterionCode($code))
            ->filter()
            ->unique()
            ->values();

        if ($codes->isEmpty()) {
            return [];
        }

        $allowedSanctionTypes = $this->criterionSanctionTypesFor($type);

        $matched = DisciplinaryCriterion::query()
            ->active()
            ->where(function ($q) use ($codes) {
                foreach ($codes as $code) {
                    $q->orWhereRaw('LOWER(code) = ?', [strtolower($code)]);
                }
            })
            ->get()
            ->keyBy(fn (DisciplinaryCriterion $c) => strtolower((string) $c->code));

        $ids = [];
        $unknown = [];
        $wrongType = [];

        foreach ($codes as $code) {
            /** @var DisciplinaryCriterion|null $found */
            $found = $matched->get(strtolower($code));
            if (! $found) {
                $unknown[] = $code;
                continue;
            }

            if (! in_array($found->sanction_type, $allowedSanctionTypes, true)) {
                $wrongType[] = sprintf(
                    '%s belongs to %s (row type is %s)',
                    $code,
                    $found->sanction_type,
                    $type
                );
                continue;
            }

            $ids[] = (int) $found->id;
        }

        if ($unknown !== [] || $wrongType !== []) {
            $parts = [];
            if ($unknown !== []) {
                $parts[] = 'Unknown or inactive criterion code(s): '.implode(', ', $unknown);
            }
            if ($wrongType !== []) {
                $parts[] = 'Criterion code(s) do not match this disciplinary type: '.implode('; ', $wrongType)
                    .'. Mapping: PP-22.5.* → coaching/counseling, PP-22.6.* → sp1, PP-22.8.* → sp3.';
            }

            throw ValidationException::withMessages([
                'criterion_codes' => implode(' ', $parts),
            ]);
        }

        return array_values(array_unique($ids));
    }

    /**
     * @return list<string>
     */
    protected function criterionSanctionTypesFor(string $disciplinaryType): array
    {
        return match ($disciplinaryType) {
            EmployeeDisciplinary::TYPE_COACHING,
            EmployeeDisciplinary::TYPE_COUNSELING => ['counseling'],
            EmployeeDisciplinary::TYPE_SP1 => ['sp1'],
            EmployeeDisciplinary::TYPE_SP2 => ['sp2'],
            EmployeeDisciplinary::TYPE_SP3 => ['sp3'],
            default => [$disciplinaryType],
        };
    }

    protected function normalizeCriterionCode(mixed $code): ?string
    {
        if ($code === null || $code === '') {
            return null;
        }

        $normalized = trim((string) $code);
        $normalized = str_replace(["\u{2013}", "\u{2014}", "\u{2212}"], '-', $normalized);
        $normalized = preg_replace('/\s+/', '', $normalized) ?? $normalized;

        return $normalized === '' ? null : $normalized;
    }

    protected function normalizeType(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        $normalized = strtolower(trim((string) $value));
        $aliases = [
            'coaching' => EmployeeDisciplinary::TYPE_COACHING,
            'counseling' => EmployeeDisciplinary::TYPE_COUNSELING,
            'sp1' => EmployeeDisciplinary::TYPE_SP1,
            'sp 1' => EmployeeDisciplinary::TYPE_SP1,
            'warning letter i' => EmployeeDisciplinary::TYPE_SP1,
            'warning letter i (sp1)' => EmployeeDisciplinary::TYPE_SP1,
            'warning letter 1' => EmployeeDisciplinary::TYPE_SP1,
            'sp2' => EmployeeDisciplinary::TYPE_SP2,
            'sp 2' => EmployeeDisciplinary::TYPE_SP2,
            'warning letter ii' => EmployeeDisciplinary::TYPE_SP2,
            'warning letter ii (sp2)' => EmployeeDisciplinary::TYPE_SP2,
            'warning letter 2' => EmployeeDisciplinary::TYPE_SP2,
            'sp3' => EmployeeDisciplinary::TYPE_SP3,
            'sp 3' => EmployeeDisciplinary::TYPE_SP3,
            'first & final warning' => EmployeeDisciplinary::TYPE_SP3,
            'first and final warning' => EmployeeDisciplinary::TYPE_SP3,
            'first & final warning (sp3)' => EmployeeDisciplinary::TYPE_SP3,
            'sp pertama & terakhir' => EmployeeDisciplinary::TYPE_SP3,
        ];

        foreach (EmployeeDisciplinary::TYPE_LABELS as $key => $label) {
            $aliases[strtolower($label)] = $key;
        }

        return $aliases[$normalized] ?? (array_key_exists($normalized, EmployeeDisciplinary::TYPE_LABELS) ? $normalized : null);
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

    /**
     * Normalize identity_card / nik from Excel (often numeric) to string without scientific notation.
     */
    protected function castIdentifierToString(mixed $value): ?string
    {
        if ($value === null || $value === '') {
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
            return sprintf('%.0f', $value);
        }

        $asString = trim((string) $value);

        return $asString === '' ? null : $asString;
    }

    /**
     * @param  list<string>  $errors
     * @param  array<string, mixed>  $data
     */
    protected function fail(int $rowNumber, string $attribute, array $errors, array $data): void
    {
        $this->onFailure(new Failure($rowNumber, $attribute, $errors, $data));
    }
}
