<?php

namespace App\Models;

use App\Models\Concerns\AllocatesSupplyDocumentNumber;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SupplyStockIn extends Model
{
    use AllocatesSupplyDocumentNumber;
    use HasUuids;

    public const NUMBER_PREFIX = 'SI';

    protected $fillable = [
        'document_number',
        'document_sequence',
        'project_id',
        'stock_date',
        'notes',
        'supply_order_id',
        'created_by',
    ];

    protected $casts = [
        'stock_date' => 'date',
        'document_sequence' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(SupplyOrder::class, 'supply_order_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function items(): HasMany
    {
        return $this->hasMany(SupplyStockInItem::class, 'supply_stock_in_id');
    }
}
