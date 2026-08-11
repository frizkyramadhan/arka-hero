<?php

namespace App\Models;

use App\Traits\HasLetterNumber;
use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class VehicleAssignment extends Model
{
    use HasLetterNumber;
    use Uuids;

    public const STATUS_DRAFT = 'draft';

    public const STATUS_ISSUED = 'issued';

    public const STATUS_IN_PROGRESS = 'in_progress';

    public const STATUS_CLOSED = 'closed';

    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'form_number',
        'letter_number_id',
        'letter_number',
        'assignment_date',
        'driver_employee_id',
        'driver_name',
        'driver_user_id',
        'origin_destination',
        'origin_is_manual',
        'remarks',
        'vehicle_id',
        'vehicle_kode',
        'license_plate',
        'project_id',
        'requested_by',
        'status',
        'issued_at',
        'started_at',
        'closed_at',
        'closed_by',
        'notes',
    ];

    protected $casts = [
        'assignment_date' => 'date',
        'origin_is_manual' => 'boolean',
        'issued_at' => 'datetime',
        'started_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    protected function getDocumentType(): string
    {
        return 'vehicle_assignment';
    }

    /**
     * Format FOA No from letter number: FOA0001
     */
    public static function formatFormNumber(string $letterNumberString): string
    {
        $raw = strtoupper(trim($letterNumberString));
        if (str_starts_with($raw, 'FOA')) {
            $raw = substr($raw, 3);
        }
        $numericPart = preg_replace('/\D+/', '', $raw) ?: '0';
        $numericPart = str_pad((int) $numericPart, 4, '0', STR_PAD_LEFT);

        return 'FOA'.$numericPart;
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Employee::class, 'driver_employee_id');
    }

    public function driverUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'driver_user_id');
    }

    public function requestor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requested_by');
    }

    public function closedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'closed_by');
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function stops(): HasMany
    {
        return $this->hasMany(VehicleAssignmentStop::class, 'assignment_id')->orderBy('sequence');
    }

    public function passengers(): HasMany
    {
        return $this->hasMany(VehicleAssignmentPassenger::class, 'assignment_id')->orderBy('sort_order');
    }

    public function originStop(): ?VehicleAssignmentStop
    {
        return null;
    }

    public function firstLeg(): ?VehicleAssignmentStop
    {
        return $this->stops
            ->reject(fn (VehicleAssignmentStop $s) => $s->stop_type === VehicleAssignmentStop::TYPE_ORIGIN)
            ->sortBy('sequence')
            ->first();
    }

    public function tripLegs()
    {
        return $this->stops
            ->reject(fn (VehicleAssignmentStop $s) => $s->stop_type === VehicleAssignmentStop::TYPE_ORIGIN)
            ->sortBy('sequence')
            ->values();
    }

    public function returnStop(): ?VehicleAssignmentStop
    {
        return $this->stops->firstWhere('stop_type', VehicleAssignmentStop::TYPE_RETURN);
    }

    public function destinationSummary(): string
    {
        $labels = $this->stops
            ->where('stop_type', VehicleAssignmentStop::TYPE_DESTINATION)
            ->pluck('destination')
            ->filter()
            ->values();

        return $labels->isEmpty() ? '—' : $labels->implode(' → ');
    }

    public function isHeaderEditable(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function canIssue(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function canStart(): bool
    {
        return $this->status === self::STATUS_ISSUED;
    }

    public function canUpdateTrip(): bool
    {
        return in_array($this->status, [self::STATUS_ISSUED, self::STATUS_IN_PROGRESS], true);
    }

    /** Requestor/admin may add/edit unlocked destinations after issue (LOT itinerary adjust pattern). */
    public function canAdjustDestinations(): bool
    {
        return in_array($this->status, [self::STATUS_ISSUED, self::STATUS_IN_PROGRESS], true);
    }

    public function lockedDestinationStopCount(): int
    {
        return $this->stops()
            ->where('stop_type', VehicleAssignmentStop::TYPE_DESTINATION)
            ->get()
            ->filter(fn (VehicleAssignmentStop $s) => $s->hasTripActivity())
            ->count();
    }

    /**
     * Rebuild destination legs; keep locked (jam/KM filled) stops in order.
     * Return leg is preserved separately at the end.
     *
     * @param  array<int, string>  $destinationStrings
     * @param  array<int, bool>  $manualFlags
     *
     * @throws \InvalidArgumentException
     */
    public function replaceDestinationsKeepingLocked(array $destinationStrings, array $manualFlags): void
    {
        $normDest = function (?string $s): string {
            return preg_replace('/\s+/u', ' ', trim((string) $s)) ?? '';
        };

        $ordered = $this->stops()
            ->where('stop_type', VehicleAssignmentStop::TYPE_DESTINATION)
            ->orderBy('sequence')
            ->orderBy('id')
            ->get();

        $returnStop = $this->stops()
            ->where('stop_type', VehicleAssignmentStop::TYPE_RETURN)
            ->orderBy('sequence')
            ->first();

        $lockedQueue = $ordered->filter(fn (VehicleAssignmentStop $s) => $s->hasTripActivity())->values();

        $plan = [];
        foreach ($destinationStrings as $i => $destRaw) {
            $d = $normDest($destRaw ?? '');
            if ($d === '') {
                continue;
            }
            $flag = (bool) ($manualFlags[$i] ?? false);
            $nextLocked = $lockedQueue->first();
            if ($nextLocked instanceof VehicleAssignmentStop
                && $d === $normDest($nextLocked->destination)
                && $flag === (bool) $nextLocked->is_manual) {
                $plan[] = ['type' => 'locked', 'stop' => $nextLocked];
                $lockedQueue->shift();

                continue;
            }

            $plan[] = ['type' => 'new', 'dest' => $d, 'manual' => $flag];
        }

        if ($lockedQueue->isNotEmpty()) {
            throw new \InvalidArgumentException('Tidak bisa menghapus/ubah tujuan yang sudah terisi jam atau KM.');
        }

        if ($plan === []) {
            throw new \InvalidArgumentException('Minimal satu tujuan diperlukan.');
        }

        $keepIds = collect($plan)
            ->where('type', 'locked')
            ->pluck('stop.id')
            ->filter()
            ->all();

        if ($returnStop) {
            $keepIds[] = $returnStop->id;
        }

        VehicleAssignmentStop::query()
            ->where('assignment_id', $this->id)
            ->whereNotIn('id', $keepIds)
            ->delete();

        $seq = 0;
        foreach ($plan as $item) {
            if ($item['type'] === 'locked') {
                $stop = $item['stop'];
                if ((int) $stop->sequence !== $seq) {
                    $stop->update(['sequence' => $seq]);
                }
                $seq++;

                continue;
            }

            VehicleAssignmentStop::create([
                'assignment_id' => $this->id,
                'sequence' => $seq++,
                'stop_type' => VehicleAssignmentStop::TYPE_DESTINATION,
                'destination' => $item['dest'],
                'is_manual' => $item['manual'],
                'created_by' => auth()->id(),
            ]);
        }

        if ($returnStop) {
            $returnStop->update(['sequence' => $seq]);
        }

        $this->unsetRelation('stops');
    }

    public function canClose(): bool
    {
        return $this->status === self::STATUS_IN_PROGRESS;
    }

    public function canCancelByRequestor(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_ISSUED], true);
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'secondary',
            self::STATUS_ISSUED => 'info',
            self::STATUS_IN_PROGRESS => 'primary',
            self::STATUS_CLOSED => 'success',
            self::STATUS_CANCELLED => 'danger',
            default => 'secondary',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_ISSUED => 'Issued',
            self::STATUS_IN_PROGRESS => 'In Progress',
            self::STATUS_CLOSED => 'Closed',
            self::STATUS_CANCELLED => 'Cancelled',
            default => ucfirst((string) $this->status),
        };
    }

    public function statusHint(): string
    {
        return match ($this->status) {
            self::STATUS_DRAFT => 'Masih bisa diedit. Letter number tetap reserved sampai Issue.',
            self::STATUS_ISSUED => 'Siap dicetak. Menunggu driver memulai trip. Destinations masih bisa ditambah.',
            self::STATUS_IN_PROGRESS => 'Driver sedang mengisi jam & KM. Destinations masih bisa ditambah (yang sudah terisi jam/KM terkunci).',
            self::STATUS_CLOSED => 'Perjalanan selesai; kendaraan sudah kembali ke origin.',
            self::STATUS_CANCELLED => 'FOA dibatalkan.',
            default => '',
        };
    }

    /** @deprecated Prefer formatFormNumber() via letter number */
    public static function nextFormNumber(): string
    {
        $max = static::query()
            ->whereRaw("form_number REGEXP '^[0-9]+$'")
            ->selectRaw('MAX(CAST(form_number AS UNSIGNED)) as max_num')
            ->value('max_num');

        return str_pad((string) (((int) $max) + 1), 6, '0', STR_PAD_LEFT);
    }
}
