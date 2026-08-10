<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class FuelBotSubmission extends Model
{
    use Uuids;

    public const STATUS_RECEIVED = 'received';

    public const STATUS_PARSING = 'parsing';

    public const STATUS_AWAITING_CONFIRM = 'awaiting_confirm';

    public const STATUS_PUSHING = 'pushing';

    public const STATUS_SYNCED = 'synced';

    public const STATUS_REJECTED_BY_DRIVER = 'rejected_by_driver';

    public const STATUS_FAILED = 'failed';

    public const STATUS_IGNORED = 'ignored';

    protected $fillable = [
        'client_uuid',
        'telegram_user_id',
        'chat_id',
        'user_id',
        'status',
        'receipt_path',
        'telegram_file_id',
        'parsed_json',
        'ai_model',
        'caption',
        'fuel_record_id',
        'error_message',
        'confirmed_at',
        'synced_at',
    ];

    protected $casts = [
        'telegram_user_id' => 'integer',
        'chat_id' => 'integer',
        'parsed_json' => 'array',
        'confirmed_at' => 'datetime',
        'synced_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $model) {
            if (empty($model->client_uuid)) {
                $model->client_uuid = (string) Str::uuid();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function fuelRecord(): BelongsTo
    {
        return $this->belongsTo(FuelRecord::class);
    }

    public function isAwaitingConfirm(): bool
    {
        return $this->status === self::STATUS_AWAITING_CONFIRM;
    }

    /**
     * Status → human label, in pipeline order.
     *
     * @return array<string, string>
     */
    public static function statusLabels(): array
    {
        return [
            self::STATUS_RECEIVED => 'Received',
            self::STATUS_PARSING => 'Parsing (AI)',
            self::STATUS_AWAITING_CONFIRM => 'Awaiting confirm',
            self::STATUS_PUSHING => 'Pushing to HERO',
            self::STATUS_SYNCED => 'Synced',
            self::STATUS_REJECTED_BY_DRIVER => 'Cancelled by driver',
            self::STATUS_FAILED => 'Failed',
            self::STATUS_IGNORED => 'Ignored',
        ];
    }

    /**
     * Status → Bootstrap badge suffix.
     *
     * @return array<string, string>
     */
    public static function statusColors(): array
    {
        return [
            self::STATUS_RECEIVED => 'secondary',
            self::STATUS_PARSING => 'info',
            self::STATUS_AWAITING_CONFIRM => 'warning',
            self::STATUS_PUSHING => 'primary',
            self::STATUS_SYNCED => 'success',
            self::STATUS_REJECTED_BY_DRIVER => 'dark',
            self::STATUS_FAILED => 'danger',
            self::STATUS_IGNORED => 'secondary',
        ];
    }

    public function statusLabel(): string
    {
        return self::statusLabels()[$this->status] ?? ucfirst((string) $this->status);
    }

    public function statusColor(): string
    {
        return self::statusColors()[$this->status] ?? 'secondary';
    }
}
