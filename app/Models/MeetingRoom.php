<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MeetingRoom extends Model
{
    use HasUuids;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_INACTIVE = 'inactive';

    public const STATUS_MAINTENANCE = 'maintenance';

    protected $fillable = [
        'project_id',
        'room_name',
        'capacity',
        'facilities',
        'status',
        'notes',
    ];

    protected $casts = [
        'capacity' => 'integer',
    ];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function requests(): HasMany
    {
        return $this->hasMany(RoomConsumptionRequest::class, 'meeting_room_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function isAvailableForDateTime(string $date, string $startTime, string $endTime, ?string $excludeRequestId = null): bool
    {
        return $this->findConflictForDateTime($date, $startTime, $endTime, $excludeRequestId) === null;
    }

    /**
     * Return the first overlapping submitted/approved request for this room, if any.
     */
    public function findConflictForDateTime(string $date, string $startTime, string $endTime, ?string $excludeRequestId = null): ?RoomConsumptionRequest
    {
        $query = $this->requests()
            ->with(['requestedBy.employee'])
            ->where('meeting_date', $date)
            ->whereIn('status', [
                RoomConsumptionRequest::STATUS_SUBMITTED,
                RoomConsumptionRequest::STATUS_APPROVED,
            ])
            ->where(function ($q) use ($startTime, $endTime) {
                $q->where(function ($inner) use ($startTime, $endTime) {
                    $inner->where('start_time', '<', $endTime)
                        ->where('end_time', '>', $startTime);
                });
            })
            ->orderBy('start_time');

        if ($excludeRequestId) {
            $query->where('id', '!=', $excludeRequestId);
        }

        return $query->first();
    }

    /**
     * Simple Indonesian message explaining the room conflict.
     */
    public function conflictMessage(RoomConsumptionRequest $conflict): string
    {
        $requester = $conflict->requestedBy?->employee?->fullname
            ?: $conflict->requestedBy?->name
            ?: 'Tidak diketahui';
        $title = $conflict->meeting_title ?: '(tanpa judul)';
        $date = $conflict->meeting_date
            ? format_date_with_weekday($conflict->meeting_date)
            : '—';
        $start = $conflict->start_time
            ? \Carbon\Carbon::parse($conflict->start_time)->format('H:i')
            : '—';
        $end = $conflict->end_time
            ? \Carbon\Carbon::parse($conflict->end_time)->format('H:i')
            : '—';

        return "Ruangan ini sudah dipakai:\n\n"
            .'• Ruangan: '.$this->room_name."\n"
            .'• Meeting: '.$title."\n"
            .'• Requester: '.$requester."\n"
            .'• Tanggal: '.$date."\n"
            .'• Waktu: '.$start.'–'.$end."\n\n"
            .'Silakan pilih ruangan lain atau ubah tanggal/waktu meeting.';
    }
}
