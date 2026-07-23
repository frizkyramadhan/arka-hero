<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RoomConsumptionItem extends Model
{
    use HasUuids;

    protected $fillable = [
        'request_id',
        'consumption_type',
        'is_selected',
        'description',
    ];

    protected $casts = [
        'is_selected' => 'boolean',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(RoomConsumptionRequest::class, 'request_id');
    }

    public function typeLabel(): string
    {
        return RoomConsumptionRequest::CONSUMPTION_TYPES[$this->consumption_type] ?? $this->consumption_type;
    }
}
