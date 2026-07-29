<?php

namespace App\Models;

use Carbon\Carbon;
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

    public function isAvailableForDateTime(
        string $startDate,
        string $endDate,
        string $startTime,
        string $endTime,
        ?string $excludeRequestId = null
    ): bool {
        return $this->findConflictForDateTime($startDate, $endDate, $startTime, $endTime, $excludeRequestId) === null;
    }

    /**
     * Return the first overlapping submitted/approved request for this room, if any.
     * Overlap uses full datetime ranges (start_date+start_time) … (end_date+end_time).
     */
    public function findConflictForDateTime(
        string $startDate,
        string $endDate,
        string $startTime,
        string $endTime,
        ?string $excludeRequestId = null
    ): ?RoomConsumptionRequest {
        $newStart = Carbon::parse($startDate.' '.$startTime);
        $newEnd = Carbon::parse($endDate.' '.$endTime);

        $query = $this->requests()
            ->with(['requestedBy.employee'])
            ->whereIn('status', [
                RoomConsumptionRequest::STATUS_SUBMITTED,
                RoomConsumptionRequest::STATUS_APPROVED,
            ])
            ->whereDate('start_date', '<=', $endDate)
            ->whereDate('end_date', '>=', $startDate)
            ->orderBy('start_date')
            ->orderBy('start_time');

        if ($excludeRequestId) {
            $query->where('id', '!=', $excludeRequestId);
        }

        foreach ($query->get() as $candidate) {
            $existingStart = Carbon::parse(
                $candidate->start_date->format('Y-m-d').' '.
                Carbon::parse($candidate->start_time)->format('H:i:s')
            );
            $existingEnd = Carbon::parse(
                $candidate->end_date->format('Y-m-d').' '.
                Carbon::parse($candidate->end_time)->format('H:i:s')
            );

            if ($existingStart->lt($newEnd) && $existingEnd->gt($newStart)) {
                return $candidate;
            }
        }

        return null;
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
        $date = $conflict->formattedMeetingDateRange();
        $start = $conflict->start_time
            ? Carbon::parse($conflict->start_time)->format('H:i')
            : '—';
        $end = $conflict->end_time
            ? Carbon::parse($conflict->end_time)->format('H:i')
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
