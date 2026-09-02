<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplyItem extends Model
{
    use HasUuids;

    protected $fillable = [
        'code',
        'supply_item_category_id',
        'name',
        'description',
        'stock_unit',
        'status',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(SupplyItemCategory::class, 'supply_item_category_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(SupplyOrderItem::class, 'supply_item_id');
    }

    public function stockInItems(): HasMany
    {
        return $this->hasMany(SupplyStockInItem::class, 'supply_item_id');
    }

    public function stockOutItems(): HasMany
    {
        return $this->hasMany(SupplyStockOutItem::class, 'supply_item_id');
    }

    public function categoryLabel(): string
    {
        return $this->category->name ?? '—';
    }

    public static function nextCode(SupplyItemCategory $category): string
    {
        $prefix = $category->prefix;
        $max = static::query()
            ->where('supply_item_category_id', $category->id)
            ->where('code', 'like', $prefix.'%')
            ->lockForUpdate()
            ->max('code');

        return static::formatCode($prefix, static::sequenceFromMaxCode($prefix, $max));
    }

    public static function previewCode(SupplyItemCategory $category): string
    {
        $prefix = $category->prefix;
        $max = static::query()
            ->where('supply_item_category_id', $category->id)
            ->where('code', 'like', $prefix.'%')
            ->max('code');

        return static::formatCode($prefix, static::sequenceFromMaxCode($prefix, $max));
    }

    protected static function sequenceFromMaxCode(string $prefix, ?string $max): int
    {
        $n = 0;
        if (is_string($max) && str_starts_with($max, $prefix)) {
            $n = (int) substr($max, strlen($prefix));
        }

        return $n + 1;
    }

    protected static function formatCode(string $prefix, int $sequence): string
    {
        return $prefix.str_pad((string) $sequence, 3, '0', STR_PAD_LEFT);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeOfficeSupply($query)
    {
        return $query->whereHas('category', fn ($q) => $q->where('prefix', SupplyItemCategory::PREFIX_OFFICE_SUPPLY));
    }

    public function hasMovements(): bool
    {
        return $this->stockInItems()->exists()
            || $this->stockOutItems()->exists()
            || $this->orderItems()->exists();
    }
}
