<?php

namespace App\Imports;

use App\Models\Project;
use App\Models\SupplyItem;
use App\Models\SupplyStockIn;
use App\Models\SupplyStockInItem;
use App\Services\SupplyStock;
use App\Support\UserProject;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Validators\Failure;

class SupplyStockInImport implements ToCollection, WithHeadingRow
{
    use Importable;

    public int $created = 0;

    public int $updated = 0;

    /** @var Collection<int, Failure> */
    public Collection $failureList;

    public function __construct()
    {
        $this->failureList = collect();
    }

    public function collection(Collection $rows): void
    {
        $groups = [];

        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2; // heading row = 1
            $data = $this->normalizeRow($row->toArray());

            if ($this->isBlank($data)) {
                continue;
            }

            $errors = $this->validateRow($data);
            if ($errors !== []) {
                foreach ($errors as $attribute => $messages) {
                    $this->addFailure($rowNumber, $attribute, $messages, $data);
                }

                continue;
            }

            $doc = trim((string) $data['document_number']);
            $groupKey = $doc !== ''
                ? 'doc:'.strtoupper($doc)
                : 'new:'.strtoupper($data['project_code']).'|'.$data['stock_date'].'|'.$data['notes'];

            $groups[$groupKey] ??= [
                'document_number' => $doc,
                'project_code' => strtoupper($data['project_code']),
                'stock_date' => $data['stock_date'],
                'notes' => $data['notes'] !== '' ? $data['notes'] : null,
                'lines' => [],
                'row_numbers' => [],
            ];

            // First row wins for header fields within a group
            $groups[$groupKey]['lines'][] = [
                'item_code' => strtoupper($data['item_code']),
                'quantity' => (int) $data['quantity'],
                'remarks' => $data['remarks'] !== '' ? $data['remarks'] : null,
                'row' => $rowNumber,
                'raw' => $data,
            ];
            $groups[$groupKey]['row_numbers'][] = $rowNumber;
        }

        if ($this->failureList->isNotEmpty()) {
            return;
        }

        foreach ($groups as $group) {
            $this->persistGroup($group);
        }
    }

    /**
     * @return Collection<int, Failure>
     */
    public function failures(): Collection
    {
        return $this->failureList;
    }

    /**
     * @param  array<string, mixed>  $group
     */
    private function persistGroup(array $group): void
    {
        $sampleRow = $group['lines'][0]['raw'] ?? [];
        $firstRow = $group['row_numbers'][0] ?? 2;

        try {
            DB::beginTransaction();

            $project = Project::query()
                ->whereRaw('UPPER(project_code) = ?', [$group['project_code']])
                ->first();

            if (! $project) {
                $this->addFailure($firstRow, 'project_code', ['Project code not found.'], $sampleRow);
                DB::rollBack();

                return;
            }

            if (! UserProject::canAccessProjectId((int) $project->id)) {
                $this->addFailure($firstRow, 'project_code', ['You do not have access to this project.'], $sampleRow);
                DB::rollBack();

                return;
            }

            $itemCodes = collect($group['lines'])->pluck('item_code')->unique()->values();
            $items = SupplyItem::query()
                ->whereIn('code', $itemCodes->all())
                ->get()
                ->keyBy(fn (SupplyItem $item) => strtoupper($item->code));

            $resolvedLines = [];
            foreach ($group['lines'] as $line) {
                $item = $items->get($line['item_code']);
                if (! $item) {
                    $this->addFailure($line['row'], 'item_code', ["Item code {$line['item_code']} not found."], $line['raw']);
                    DB::rollBack();

                    return;
                }
                $resolvedLines[] = [
                    'supply_item_id' => $item->id,
                    'quantity' => $line['quantity'],
                    'remarks' => $line['remarks'],
                    'item' => $item,
                    'row' => $line['row'],
                    'raw' => $line['raw'],
                ];
            }

            $existing = $group['document_number'] !== ''
                ? SupplyStockIn::query()
                    ->whereRaw('UPPER(document_number) = ?', [strtoupper($group['document_number'])])
                    ->with('items')
                    ->first()
                : null;

            if ($group['document_number'] !== '' && ! $existing) {
                $this->addFailure($firstRow, 'document_number', ['Stock In document not found. Leave blank to create a new document.'], $sampleRow);
                DB::rollBack();

                return;
            }

            if ($existing) {
                if ((int) $existing->project_id !== (int) $project->id) {
                    $this->addFailure($firstRow, 'project_code', ['Project must match the existing Stock In document.'], $sampleRow);
                    DB::rollBack();

                    return;
                }

                $balanceError = $this->assertEndingBalancesOk(
                    (int) $project->id,
                    $existing->items->groupBy('supply_item_id')->map->sum('quantity')->all(),
                    collect($resolvedLines)->groupBy('supply_item_id')->map->sum('quantity')->all(),
                );
                if ($balanceError) {
                    $this->addFailure($firstRow, 'quantity', [$balanceError], $sampleRow);
                    DB::rollBack();

                    return;
                }

                $existing->update([
                    'stock_date' => $group['stock_date'],
                    'notes' => $group['notes'],
                ]);
                $existing->items()->delete();
                foreach ($resolvedLines as $line) {
                    SupplyStockInItem::create([
                        'supply_stock_in_id' => $existing->id,
                        'supply_item_id' => $line['supply_item_id'],
                        'quantity' => $line['quantity'],
                        'remarks' => $line['remarks'],
                    ]);
                }
                $this->updated++;
            } else {
                $number = SupplyStockIn::allocateNumber((int) $project->id, $project->project_code);
                $stockIn = SupplyStockIn::create([
                    'document_number' => $number['document_number'],
                    'document_sequence' => $number['document_sequence'],
                    'project_id' => $project->id,
                    'stock_date' => $group['stock_date'],
                    'notes' => $group['notes'],
                    'created_by' => Auth::id(),
                ]);
                foreach ($resolvedLines as $line) {
                    SupplyStockInItem::create([
                        'supply_stock_in_id' => $stockIn->id,
                        'supply_item_id' => $line['supply_item_id'],
                        'quantity' => $line['quantity'],
                        'remarks' => $line['remarks'],
                    ]);
                }
                $this->created++;
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            $this->addFailure($firstRow, 'system_error', [$e->getMessage()], $sampleRow);
        }
    }

    /**
     * @param  array<string, int>  $oldByItem
     * @param  array<string, int>  $newByItem
     */
    private function assertEndingBalancesOk(int $projectId, array $oldByItem, array $newByItem): ?string
    {
        $itemIds = array_unique(array_merge(array_keys($oldByItem), array_keys($newByItem)));
        foreach ($itemIds as $itemId) {
            $old = (int) ($oldByItem[$itemId] ?? 0);
            $new = (int) ($newByItem[$itemId] ?? 0);
            $ending = SupplyStock::endingBalance($itemId, $projectId);
            if ($ending - $old + $new < 0) {
                $item = SupplyItem::query()->find($itemId);
                $label = trim(($item->code ?? '').' '.($item->name ?? 'Item'));

                return "{$label}: ending balance would become negative.";
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, list<string>>
     */
    private function validateRow(array $data): array
    {
        $errors = [];
        if ($data['project_code'] === '') {
            $errors['project_code'] = ['Project code is required.'];
        }
        if ($data['stock_date'] === '' || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $data['stock_date'])) {
            $errors['stock_date'] = ['Stock date must be YYYY-MM-DD.'];
        }
        if ($data['item_code'] === '') {
            $errors['item_code'] = ['Item code is required.'];
        }
        if ($data['quantity'] === '' || (int) $data['quantity'] < 1) {
            $errors['quantity'] = ['Quantity must be an integer >= 1.'];
        }

        return $errors;
    }

    /**
     * @param  array<string, mixed>  $row
     * @return array{document_number: string, project_code: string, stock_date: string, notes: string, item_code: string, quantity: string, remarks: string}
     */
    private function normalizeRow(array $row): array
    {
        $date = $row['stock_date'] ?? '';
        if ($date instanceof \DateTimeInterface) {
            $date = $date->format('Y-m-d');
        } elseif (is_numeric($date)) {
            // Excel serial date
            $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject((float) $date)->format('Y-m-d');
        } else {
            $date = trim((string) $date);
            if ($date !== '' && preg_match('/^(\d{1,2})\/(\d{1,2})\/(\d{4})$/', $date, $m)) {
                $date = sprintf('%04d-%02d-%02d', (int) $m[3], (int) $m[2], (int) $m[1]);
            }
        }

        return [
            'document_number' => trim((string) ($row['document_number'] ?? '')),
            'project_code' => trim((string) ($row['project_code'] ?? '')),
            'stock_date' => $date,
            'notes' => trim((string) ($row['notes'] ?? '')),
            'item_code' => trim((string) ($row['item_code'] ?? '')),
            'quantity' => trim((string) ($row['quantity'] ?? '')),
            'remarks' => trim((string) ($row['remarks'] ?? '')),
        ];
    }

    /**
     * @param  array{document_number: string, project_code: string, stock_date: string, notes: string, item_code: string, quantity: string, remarks: string}  $data
     */
    private function isBlank(array $data): bool
    {
        return $data['document_number'] === ''
            && $data['project_code'] === ''
            && $data['stock_date'] === ''
            && $data['item_code'] === ''
            && $data['quantity'] === '';
    }

    /**
     * @param  list<string>  $messages
     * @param  array<string, mixed>  $values
     */
    private function addFailure(int $row, string $attribute, array $messages, array $values): void
    {
        $this->failureList->push(new Failure($row, $attribute, $messages, $values));
    }
}
