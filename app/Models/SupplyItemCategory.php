<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplyItemCategory extends Model
{
    use HasUuids;

    /** Default seeded prefix for office supplies (ATK workbook origin). */
    public const PREFIX_OFFICE_SUPPLY = 'GAA';

    protected $fillable = [
        'name',
        'prefix',
        'description',
        'status',
    ];

    protected static function booted(): void
    {
        static::saving(function (self $category) {
            $category->prefix = strtoupper(trim((string) $category->prefix));
        });
    }

    public function items(): HasMany
    {
        return $this->hasMany(SupplyItem::class, 'supply_item_category_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function canChangePrefix(): bool
    {
        return ! $this->items()->exists();
    }
}
