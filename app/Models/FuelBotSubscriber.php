<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FuelBotSubscriber extends Model
{
    use Uuids;

    protected $fillable = [
        'user_id',
        'telegram_user_id',
        'telegram_username',
        'is_active',
        'notes',
        'created_by',
    ];

    protected $casts = [
        'telegram_user_id' => 'integer',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public static function findActiveByTelegramId(int $telegramUserId): ?self
    {
        return static::query()
            ->where('telegram_user_id', $telegramUserId)
            ->where('is_active', true)
            ->first();
    }
}
