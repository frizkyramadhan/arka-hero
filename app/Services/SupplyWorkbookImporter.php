<?php

namespace App\Services;

use App\Models\Project;
use App\Models\SupplyItem;
use App\Models\SupplyItemCategory;
use App\Models\SupplyStockIn;
use App\Models\SupplyStockInItem;
use App\Models\SupplyStockOut;
use App\Models\SupplyStockOutItem;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;

class SupplyWorkbookImporter
{
    public const CUTOVER_MARKER = 'Supply workbook cutover';

    public const OPENING_DATE = '2026-01-01';

    private const DATA_START_ROW = 7;

    private const HEADER_ROW = 6;

    /** @var array<string, string> */
    private array $itemIdsByCode = [];

    /** @var array<string, int> */
    private array $openingByCode = [];

    /** @var array<string, int> */
    private array $masukTotalsByCode = [];

    /** @var array<string, int> */
    private array $keluarTotalsByCode = [];

    /** @var list<string> */
    private array $warnings = [];

    public function __construct(
        private readonly int $projectId,
        private readonly string $projectCode,
        private readonly int $createdBy,
        private readonly SupplyItemCategory $category,
    ) {}

    /**
     * @return array{
     *     catalog: int,
     *     opening_lines: int,
     *     stock_in_docs: int,
     *     stock_in_lines: int,
     *     stock_out_docs: int,
     *     stock_out_lines: int,
     *     reconciliation_mismatches: int,
     *     warnings: list<string>
     * }
     */
    public function import(string $filePath, string $workbookLabel): array
    {
        $this->warnings = [];
        $this->itemIdsByCode = [];
        $this->openingByCode = [];
        $this->masukTotalsByCode = [];
        $this->keluarTotalsByCode = [];

        $spreadsheet = IOFactory::load($filePath);

        $stats = [
            'catalog' => 0,
            'opening_lines' => 0,
            'stock_in_docs' => 0,
            'stock_in_lines' => 0,
            'stock_out_docs' => 0,
            'stock_out_lines' => 0,
            'reconciliation_mismatches' => 0,
            'warnings' => [],
        ];

        DB::transaction(function () use ($spreadsheet, $workbookLabel, &$stats) {
            $stats['catalog'] = $this->importCatalog($spreadsheet->getSheetByName('Katalog'));

            $openingLines = $this->openingLinesFromCatalog();
            if ($openingLines !== []) {
                $this->createStockIn(
                    self::OPENING_DATE,
                    $openingLines,
                    sprintf('%s — %s — opening balance (Awal)', self::CUTOVER_MARKER, $workbookLabel),
                );
                $stats['opening_lines'] = count($openingLines);
                $stats['stock_in_docs']++;
                $stats['stock_in_lines'] += count($openingLines);
            }

            $masukRows = $this->readMovementRows($spreadsheet->getSheetByName('Masuk'), 'in');
            foreach ($this->groupByDate($masukRows) as $date => $lines) {
                $this->createStockIn(
                    $date,
                    $lines,
                    sprintf('%s — %s — Masuk sheet', self::CUTOVER_MARKER, $workbookLabel),
                );
                $stats['stock_in_docs']++;
                $stats['stock_in_lines'] += count($lines);
            }

            $keluarRows = $this->readMovementRows($spreadsheet->getSheetByName('Keluar'), 'out');
            foreach ($this->groupByDate($keluarRows) as $date => $lines) {
                $this->createStockOut(
                    $date,
                    $lines,
                    sprintf('%s — %s — Keluar sheet', self::CUTOVER_MARKER, $workbookLabel),
                );
                $stats['stock_out_docs']++;
                $stats['stock_out_lines'] += count($lines);
            }
        });

        $stats['reconciliation_mismatches'] = $this->reconcileEndingBalances();
        $stats['warnings'] = $this->warnings;

        return $stats;
    }

    public static function isAlreadyImported(): bool
    {
        return SupplyStockIn::query()
            ->where('notes', 'like', '%'.self::CUTOVER_MARKER.'%')
            ->exists();
    }

    public static function wipeImport(): void
    {
        DB::transaction(function () {
            $inIds = SupplyStockIn::query()
                ->where('notes', 'like', '%'.self::CUTOVER_MARKER.'%')
                ->pluck('id');

            if ($inIds->isNotEmpty()) {
                SupplyStockInItem::query()
                    ->whereIn('supply_stock_in_id', $inIds)
                    ->delete();
                SupplyStockIn::query()
                    ->whereIn('id', $inIds)
                    ->delete();
            }

            $outIds = SupplyStockOut::query()
                ->where('notes', 'like', '%'.self::CUTOVER_MARKER.'%')
                ->pluck('id');

            if ($outIds->isNotEmpty()) {
                SupplyStockOutItem::query()
                    ->whereIn('supply_stock_out_id', $outIds)
                    ->delete();
                SupplyStockOut::query()
                    ->whereIn('id', $outIds)
                    ->delete();
            }
        });
    }

    /**
     * @return array{project: Project, created_by: int}
     */
    public static function resolveContext(): array
    {
        $project = Project::query()
            ->where('project_code', '000H')
            ->firstOrFail();

        $createdBy = (int) (DB::table('users')->orderBy('id')->value('id') ?? 1);

        return [
            'project' => $project,
            'created_by' => $createdBy,
        ];
    }

    private function importCatalog(?\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet): int
    {
        if ($sheet === null) {
            throw new \RuntimeException('Workbook is missing the Katalog sheet.');
        }

        $count = 0;

        for ($row = self::DATA_START_ROW; $row <= $sheet->getHighestRow(); $row++) {
            $code = $this->normalizeCode($sheet->getCell('A'.$row)->getValue());
            if ($code === '') {
                continue;
            }

            $name = $this->nullableString($sheet->getCell('B'.$row)->getValue());
            if ($name === null) {
                $this->warnings[] = "Katalog row {$row}: skipped {$code} (empty name).";

                continue;
            }

            $description = $this->nullableString($sheet->getCell('C'.$row)->getValue());
            $opening = $this->normalizeQuantity($sheet->getCell('D'.$row)->getValue());

            $item = SupplyItem::query()->updateOrCreate(
                ['code' => $code],
                [
                    'supply_item_category_id' => $this->category->id,
                    'name' => $name,
                    'description' => $description,
                    'stock_unit' => 'pcs',
                    'status' => 'active',
                ],
            );

            $this->itemIdsByCode[$code] = $item->id;
            $this->openingByCode[$code] = $opening;
            $count++;
        }

        return $count;
    }

    /**
     * @return list<array{supply_item_id: string, quantity: int}>
     */
    private function openingLinesFromCatalog(): array
    {
        $lines = [];

        foreach ($this->openingByCode as $code => $opening) {
            if ($opening <= 0) {
                continue;
            }

            $lines[] = [
                'supply_item_id' => $this->itemIdsByCode[$code],
                'quantity' => $opening,
            ];
        }

        return $lines;
    }

    /**
     * @return list<array{date: string, code: string, quantity: int, location: ?string, person_in_charge: ?string}>
     */
    private function readMovementRows(?\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet $sheet, string $type): array
    {
        if ($sheet === null) {
            throw new \RuntimeException("Workbook is missing the {$type} movement sheet.");
        }

        $rows = [];

        for ($row = self::DATA_START_ROW; $row <= $sheet->getHighestRow(); $row++) {
            $date = $this->excelDate($sheet->getCell('A'.$row)->getValue());
            $code = $this->normalizeCode($sheet->getCell('B'.$row)->getValue());
            $quantity = $this->normalizeQuantity($sheet->getCell('E'.$row)->getValue());

            if ($date === null || $code === '' || $quantity <= 0) {
                continue;
            }

            if (! isset($this->itemIdsByCode[$code])) {
                $this->warnings[] = ucfirst($type)." row {$row}: skipped unknown code {$code}.";

                continue;
            }

            if ($type === 'in') {
                $this->masukTotalsByCode[$code] = ($this->masukTotalsByCode[$code] ?? 0) + $quantity;
            } else {
                $this->keluarTotalsByCode[$code] = ($this->keluarTotalsByCode[$code] ?? 0) + $quantity;
            }

            $rows[] = [
                'date' => $date,
                'code' => $code,
                'quantity' => $quantity,
                'location' => $type === 'out'
                    ? $this->nullableString($sheet->getCell('F'.$row)->getValue()) ?? '—'
                    : null,
                'person_in_charge' => $type === 'out'
                    ? $this->nullableString($sheet->getCell('G'.$row)->getValue()) ?? '—'
                    : null,
            ];
        }

        return $rows;
    }

    /**
     * @param  list<array{date: string, code: string, quantity: int, location: ?string, person_in_charge: ?string}>  $rows
     * @return array<string, list<array{supply_item_id: string, quantity: int, location?: string, person_in_charge?: string}>>
     */
    private function groupByDate(array $rows): array
    {
        $grouped = [];

        foreach ($rows as $row) {
            $line = [
                'supply_item_id' => $this->itemIdsByCode[$row['code']],
                'quantity' => $row['quantity'],
            ];

            if ($row['location'] !== null) {
                $line['location'] = $row['location'];
                $line['person_in_charge'] = $row['person_in_charge'] ?? '—';
            }

            $grouped[$row['date']][] = $line;
        }

        ksort($grouped);

        return $grouped;
    }

    /**
     * @param  list<array{supply_item_id: string, quantity: int}>  $lines
     */
    private function createStockIn(string $date, array $lines, string $notes): void
    {
        $number = SupplyStockIn::allocateNumber($this->projectId, $this->projectCode);

        $stockIn = SupplyStockIn::create([
            'document_number' => $number['document_number'],
            'document_sequence' => $number['document_sequence'],
            'project_id' => $this->projectId,
            'stock_date' => $date,
            'notes' => $notes,
            'created_by' => $this->createdBy,
        ]);

        foreach ($lines as $line) {
            SupplyStockInItem::create([
                'supply_stock_in_id' => $stockIn->id,
                'supply_item_id' => $line['supply_item_id'],
                'quantity' => $line['quantity'],
            ]);
        }
    }

    /**
     * @param  list<array{supply_item_id: string, quantity: int, location?: string, person_in_charge?: string}>  $lines
     */
    private function createStockOut(string $date, array $lines, string $notes): void
    {
        $number = SupplyStockOut::allocateNumber($this->projectId, $this->projectCode);

        $stockOut = SupplyStockOut::create([
            'document_number' => $number['document_number'],
            'document_sequence' => $number['document_sequence'],
            'project_id' => $this->projectId,
            'stock_date' => $date,
            'notes' => $notes,
            'created_by' => $this->createdBy,
        ]);

        foreach ($lines as $line) {
            SupplyStockOutItem::create([
                'supply_stock_out_id' => $stockOut->id,
                'supply_item_id' => $line['supply_item_id'],
                'quantity' => $line['quantity'],
                'location' => $line['location'] ?? '—',
                'person_in_charge' => $line['person_in_charge'] ?? '—',
            ]);
        }
    }

    private function reconcileEndingBalances(): int
    {
        $mismatches = 0;

        foreach ($this->openingByCode as $code => $opening) {
            $itemId = $this->itemIdsByCode[$code] ?? null;
            if ($itemId === null) {
                continue;
            }

            $expected = $opening
                + ($this->masukTotalsByCode[$code] ?? 0)
                - ($this->keluarTotalsByCode[$code] ?? 0);

            $derived = SupplyStock::endingBalance($itemId, $this->projectId);

            if ($derived !== $expected) {
                $mismatches++;
                $this->warnings[] = "Reconciliation {$code}: workbook Akhir {$expected}, derived {$derived}.";
            }
        }

        return $mismatches;
    }

    private function normalizeCode(mixed $value): string
    {
        return strtoupper(trim((string) ($value ?? '')));
    }

    private function normalizeQuantity(mixed $value): int
    {
        if ($value === null || $value === '') {
            return 0;
        }

        if (is_string($value) && str_starts_with(trim($value), '=')) {
            return 0;
        }

        return max(0, (int) round((float) $value));
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }

    private function excelDate(mixed $value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }

        if (is_numeric($value)) {
            return Carbon::instance(ExcelDate::excelToDateTimeObject((float) $value))->toDateString();
        }

        try {
            return Carbon::parse((string) $value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
