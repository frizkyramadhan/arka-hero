<?php

namespace App\Imports;

use App\Models\SupplyItem;
use App\Models\SupplyItemCategory;
use Illuminate\Support\Collection;
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

class SupplyItemImport implements OnEachRow, WithHeadingRow, WithValidation, SkipsOnError, SkipsOnFailure
{
    use Importable, SkipsErrors, SkipsFailures;

    public int $created = 0;

    public int $updated = 0;

    /** @var Collection<int, SupplyItemCategory>|null */
    private ?Collection $categories = null;

    public function headingRow(): int
    {
        return 1;
    }

    public function onRow(Row $row): void
    {
        $data = $row->toArray();
        $rowNumber = $row->getIndex();

        $code = trim((string) ($data['code'] ?? ''));
        $name = trim((string) ($data['name'] ?? ''));

        if ($code === '' && $name === '') {
            return;
        }

        $status = $this->normalizeStatus($data['status'] ?? null);
        $stockUnit = trim((string) ($data['stock_unit'] ?? ''));
        $description = $this->nullableString($data['description'] ?? null);

        try {
            DB::beginTransaction();

            $existing = $code !== '' ? SupplyItem::query()->where('code', $code)->first() : null;

            if ($existing) {
                if ($name === '') {
                    $this->onFailure(new Failure(
                        $rowNumber,
                        'name',
                        ['Name is required when updating an existing item by code.'],
                        $data
                    ));
                    DB::rollBack();

                    return;
                }

                if ($stockUnit === '') {
                    $this->onFailure(new Failure(
                        $rowNumber,
                        'stock_unit',
                        ['Stock unit is required when updating an existing item.'],
                        $data
                    ));
                    DB::rollBack();

                    return;
                }

                $existing->update([
                    'name' => $name,
                    'description' => $description,
                    'stock_unit' => $stockUnit,
                    'status' => $status,
                ]);

                $this->updated++;
                DB::commit();

                return;
            }

            if ($name === '') {
                $this->onFailure(new Failure(
                    $rowNumber,
                    'name',
                    ['Name is required to create a new catalog item.'],
                    $data
                ));
                DB::rollBack();

                return;
            }

            if ($stockUnit === '') {
                $this->onFailure(new Failure(
                    $rowNumber,
                    'stock_unit',
                    ['Stock unit is required to create a new catalog item.'],
                    $data
                ));
                DB::rollBack();

                return;
            }

            $category = $this->resolveCategory($data);
            if (! $category) {
                $this->onFailure(new Failure(
                    $rowNumber,
                    'category_prefix',
                    ['Item category not found. Provide a valid category_prefix or category_name from Master Data.'],
                    $data
                ));
                DB::rollBack();

                return;
            }

            if ($code !== '') {
                if (! str_starts_with(strtoupper($code), strtoupper($category->prefix))) {
                    $this->onFailure(new Failure(
                        $rowNumber,
                        'code',
                        ["Item code must start with category prefix {$category->prefix}."],
                        $data
                    ));
                    DB::rollBack();

                    return;
                }

                if (SupplyItem::query()->where('code', $code)->exists()) {
                    $this->onFailure(new Failure(
                        $rowNumber,
                        'code',
                        ['Another catalog item already uses this code.'],
                        $data
                    ));
                    DB::rollBack();

                    return;
                }

                $itemCode = strtoupper($code);
            } else {
                $itemCode = SupplyItem::nextCode($category);
            }

            SupplyItem::create([
                'code' => $itemCode,
                'supply_item_category_id' => $category->id,
                'name' => $name,
                'description' => $description,
                'stock_unit' => $stockUnit,
                'status' => $status,
            ]);

            $this->created++;
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
            'code' => ['nullable', 'string', 'max:50'],
            'category_name' => ['nullable', 'string', 'max:255'],
            'category_prefix' => ['nullable', 'string', 'max:20'],
            'name' => ['nullable', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'stock_unit' => ['nullable', 'string', 'max:50'],
            'status' => ['nullable', 'string'],
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function resolveCategory(array $data): ?SupplyItemCategory
    {
        $prefix = strtoupper(trim((string) ($data['category_prefix'] ?? '')));
        if ($prefix !== '') {
            $match = $this->categories()->first(
                fn (SupplyItemCategory $category) => strtoupper($category->prefix) === $prefix
            );
            if ($match) {
                return $match;
            }
        }

        $name = trim((string) ($data['category_name'] ?? ''));
        if ($name !== '') {
            $needle = strtolower($name);

            return $this->categories()->first(
                fn (SupplyItemCategory $category) => strtolower($category->name) === $needle
            );
        }

        return null;
    }

    /**
     * @return Collection<int, SupplyItemCategory>
     */
    private function categories(): Collection
    {
        return $this->categories ??= SupplyItemCategory::query()->orderBy('name')->get();
    }

    private function normalizeStatus(mixed $value): string
    {
        $normalized = strtolower(trim((string) ($value ?? 'active')));

        return in_array($normalized, ['active', 'inactive'], true) ? $normalized : 'active';
    }

    private function nullableString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
