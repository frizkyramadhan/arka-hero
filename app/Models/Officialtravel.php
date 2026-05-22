<?php

namespace App\Models;

use App\Support\UserProject;
use App\Traits\HasLetterNumber;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;

class Officialtravel extends Model
{
    use HasFactory;
    use HasLetterNumber;
    use HasUuids;

    protected $guarded = [];

    protected $casts = [
        'official_travel_date' => 'date',
        'departure_from' => 'date',
        'approved_at' => 'datetime',
        'submitted_by_user' => 'boolean',
        // Note: arrival_at_destination and departure_from_destination have been moved to officialtravel_stops table
        'manual_approvers' => 'array',
    ];

    // Constants (Legacy approval constants removed - using new approval system)

    // Status enum values
    public const STATUS_DRAFT = 'draft';

    public const STATUS_PENDING_HR = 'pending_hr'; // User submission awaiting HR confirmation & letter number

    public const STATUS_SUBMITTED = 'submitted';

    public const STATUS_APPROVED = 'approved';

    public const STATUS_REJECTED = 'rejected';

    public const STATUS_CANCELLED = 'cancelled';

    public const STATUS_CLOSED = 'closed';

    public static function getStatusOptions()
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_PENDING_HR => 'Menunggu Konfirmasi HR',
            self::STATUS_SUBMITTED => 'Submitted',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_CLOSED => 'Closed',
        ];
    }

    /** Whether this LOT was submitted by user (my-travels) and is waiting for HR to assign letter number. */
    public function isPendingHr(): bool
    {
        return (bool) $this->submitted_by_user && empty($this->letter_number_id);
    }

    // Relationships
    public function traveler()
    {
        return $this->belongsTo(Administration::class, 'traveler_id', 'id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'official_travel_origin');
    }

    public function transportation()
    {
        return $this->belongsTo(Transportation::class);
    }

    public function accommodation()
    {
        return $this->belongsTo(Accommodation::class);
    }

    public function details()
    {
        return $this->hasMany(Officialtravel_detail::class, 'official_travel_id');
    }

    public function stops()
    {
        return $this->hasMany(OfficialtravelStop::class, 'official_travel_id', 'id')
            ->orderBy('sort_order')
            ->orderBy('id');
    }

    /**
     * Last destination by itinerary order (not necessarily latest stamp activity).
     */
    public function latestStop()
    {
        return $this->hasOne(OfficialtravelStop::class, 'official_travel_id', 'id')
            ->orderByDesc('sort_order')
            ->orderByDesc('id');
    }

    /** Planned destinations can be replaced only before any stamp exists on any stop. */
    public function plannedStopsAreEditable(): bool
    {
        return ! $this->stops()
            ->where(function ($q) {
                $q->whereNotNull('arrival_at_destination')
                    ->orWhereNotNull('departure_from_destination');
            })
            ->exists();
    }

    /**
     * Stops that already have arrival or departure (checkpoint) — cannot be edited or removed via itinerary adjust.
     */
    public function approvedItineraryLockedStopCount(): int
    {
        return $this->stops()
            ->where(function ($q) {
                $q->whereNotNull('arrival_at_destination')
                    ->orWhereNotNull('departure_from_destination');
            })
            ->count();
    }

    public function userMayAdjustApprovedItineraryAtOrigin(?User $user = null): bool
    {
        $user = $user ?? auth()->user();

        return UserProject::userMayAdjustApprovedOfficialtravelItinerary($user, $this);
    }

    /**
     * Rebuild itinerary: every stop with a checkpoint must stay (same label + manual flag), in order.
     * Stops without checkpoint are replaced from the request (add / remove / edit).
     *
     * @param  array<int, string>  $destinationStrings  Normalized list from {@see OfficialtravelController::normalizeStopsFromRequest}
     * @param  array<int, bool>  $manualFlags
     *
     * @throws InvalidArgumentException
     */
    public function replaceItineraryKeepingCheckpointStops(array $destinationStrings, array $manualFlags): void
    {
        $normDest = function (?string $s): string {
            return preg_replace('/\s+/u', ' ', trim((string) $s));
        };

        $ordered = $this->stops()->orderBy('sort_order')->orderBy('id')->get();
        $lockedQueue = $ordered->filter(function (OfficialtravelStop $s) {
            return $s->hasArrival() || $s->hasDeparture();
        })->values();

        $plan = [];
        foreach ($destinationStrings as $i => $destRaw) {
            $d = $normDest($destRaw ?? '');
            if ($d === '') {
                continue;
            }
            $flag = (bool) ($manualFlags[$i] ?? false);
            $nextLocked = $lockedQueue->first();
            if ($nextLocked instanceof OfficialtravelStop
                && $d === $normDest($nextLocked->destination)
                && $flag === (bool) $nextLocked->is_manual) {
                $plan[] = ['type' => 'locked', 'stop' => $nextLocked];
                $lockedQueue->shift();

                continue;
            }

            $plan[] = ['type' => 'new', 'dest' => $d, 'manual' => $flag];
        }

        if ($lockedQueue->isNotEmpty()) {
            throw new InvalidArgumentException('You cannot remove or change stops that already have a checkpoint.');
        }

        $keepIds = collect($plan)
            ->where('type', 'locked')
            ->pluck('stop.id')
            ->filter()
            ->all();

        OfficialtravelStop::query()
            ->where('official_travel_id', $this->id)
            ->whereNotIn('id', $keepIds)
            ->delete();

        $order = 0;
        foreach ($plan as $item) {
            if ($item['type'] === 'locked') {
                $stop = $item['stop'];
                if ((int) $stop->sort_order !== $order) {
                    $stop->update(['sort_order' => $order]);
                }
                $order++;

                continue;
            }

            OfficialtravelStop::create([
                'official_travel_id' => $this->id,
                'destination' => $item['dest'],
                'sort_order' => $order,
                'is_manual' => $item['manual'],
            ]);
            $order++;
        }

        $this->unsetRelation('stops');
    }

    public function nextArrivalStop(): ?OfficialtravelStop
    {
        return $this->stops()
            ->whereNull('arrival_at_destination')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();
    }

    public function nextDepartureStop(): ?OfficialtravelStop
    {
        return $this->stops()
            ->whereNotNull('arrival_at_destination')
            ->whereNull('departure_from_destination')
            ->orderBy('sort_order')
            ->orderBy('id')
            ->first();
    }

    /**
     * Legacy DB column `officialtravels.destination` is unused: itinerary labels live on `officialtravel_stops`.
     * Reads return the same itinerary summary as the UI/API; writes always persist an empty string.
     */
    protected function destination(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->itinerarySummaryForDisplay(),
            set: fn (mixed $value) => '',
        );
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approval_plans()
    {
        return $this->hasMany(ApprovalPlan::class, 'document_id', 'id')
            ->where('document_type', 'officialtravel');
    }

    public function flightRequests()
    {
        return $this->hasMany(FlightRequest::class, 'official_travel_id');
    }

    /**
     * Get document type untuk letter number tracking
     */
    protected function getDocumentType(): string
    {
        return 'officialtravel';
    }

    // Integration dengan Letter Number System
    public function letterNumber()
    {
        return $this->belongsTo(LetterNumber::class, 'letter_number_id');
    }

    // Method untuk assign letter number
    public function assignLetterNumber($letterNumberId)
    {
        $letterNumber = LetterNumber::find($letterNumberId);

        if ($letterNumber && $letterNumber->status === 'reserved') {
            $this->letter_number_id = $letterNumberId;
            $this->letter_number = $letterNumber->letter_number;
            $this->save();

            // Mark letter number as used
            $letterNumber->markAsUsed('officialtravel', $this->id);

            return true;
        }

        return false;
    }

    // Business logic methods for stops
    public function canRecordArrival()
    {
        if ($this->status !== 'approved') {
            return false;
        }

        // While any leg is waiting for departure, no new arrivals (multi: parallel first wave ends after first arrival;
        // single: one leg at a time).
        if ($this->stops()->exists()) {
            $awaitingDeparture = $this->stops()
                ->whereNotNull('arrival_at_destination')
                ->whereNull('departure_from_destination')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->exists();
            if ($awaitingDeparture) {
                return false;
            }
        }

        return $this->nextArrivalStop() !== null;
    }

    public function canRecordDeparture()
    {
        if ($this->status !== 'approved') {
            return false;
        }

        return $this->nextDepartureStop() !== null;
    }

    public function canClose()
    {
        if ($this->status !== 'approved') {
            return false;
        }

        if (! $this->stops()->exists()) {
            return false;
        }

        return ! $this->stops()->where(function ($q) {
            $q->whereNull('arrival_at_destination')
                ->orWhereNull('departure_from_destination');
        })->exists();
    }

    /** Any stop with both arrival and departure recorded. */
    public function hasAtLeastOneFullyCompletedStop(): bool
    {
        return $this->stops()
            ->whereNotNull('arrival_at_destination')
            ->whereNotNull('departure_from_destination')
            ->exists();
    }

    /**
     * Multi-leg LOT may be closed early from origin when the planned route is shortened,
     * if at least one checkpoint is complete and other legs are left open.
     */
    public function eligibleForEarlyCloseFromOrigin(): bool
    {
        if ($this->status !== self::STATUS_APPROVED) {
            return false;
        }

        if (! $this->stops()->exists()) {
            return false;
        }

        if ($this->canClose()) {
            return false;
        }

        return $this->hasAtLeastOneFullyCompletedStop();
    }

    /**
     * Whether this user may execute Close: all stops complete, or early close allowed for LOT origin (or administrator).
     */
    public function userMayClose(?User $user = null): bool
    {
        $user = $user ?? auth()->user();
        if (! $user instanceof User || $this->status !== self::STATUS_APPROVED) {
            return false;
        }

        if ($this->canClose()) {
            return true;
        }

        if (! $this->eligibleForEarlyCloseFromOrigin()) {
            return false;
        }

        if ($user->hasRole('administrator')) {
            return true;
        }

        $originId = $this->official_travel_origin;

        return $originId !== null && UserProject::canAccessProjectId((int) $originId, $user);
    }

    public function getCurrentStopStatus()
    {
        if (! $this->stops()->exists()) {
            return 'no_stops';
        }

        if ($this->stops()->whereNotNull('arrival_at_destination')->whereNull('departure_from_destination')->exists()) {
            return 'arrival_only';
        }

        if ($this->stops()->whereNull('arrival_at_destination')->exists()) {
            return 'pending_arrival';
        }

        return 'complete';
    }

    /** @return \Illuminate\Support\Collection<int, OfficialtravelStop> */
    public function stopsEligibleForArrivalStamp(?User $user = null): \Illuminate\Support\Collection
    {
        $user = $user ?? auth()->user();
        if (! $user instanceof User) {
            return collect();
        }

        if (! $this->stops()->exists()) {
            return collect();
        }

        // Multi-destination: until any arrival is recorded, every leg without arrival may stamp in parallel (per project rules).
        // Once some leg is awaiting departure, no new arrivals anywhere until that leg departs.
        if ($this->stops()->count() > 1) {
            if ($this->nextDepartureStop() !== null) {
                return collect();
            }

            $candidates = $this->stops()
                ->whereNull('arrival_at_destination')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->get();
            if ($user->hasRole('administrator')) {
                return $candidates->values();
            }

            return $candidates
                ->filter(fn (OfficialtravelStop $s) => UserProject::userCanStampOfficialtravelStop($user, $this, $s))
                ->values();
        }

        // Single planned stop: one arrival leg at a time (same lock rule via canRecordArrival).
        $next = $this->nextArrivalStop();
        if ($next === null) {
            return collect();
        }

        if ($user->hasRole('administrator')) {
            return collect([$next]);
        }

        return UserProject::userCanStampOfficialtravelStop($user, $this, $next)
            ? collect([$next])
            : collect();
    }

    /** @return \Illuminate\Support\Collection<int, OfficialtravelStop> */
    public function stopsEligibleForDepartureStamp(?User $user = null): \Illuminate\Support\Collection
    {
        $user = $user ?? auth()->user();
        if (! $user instanceof User) {
            return collect();
        }

        $next = $this->nextDepartureStop();
        if ($next === null) {
            return collect();
        }

        if ($user->hasRole('administrator')) {
            return collect([$next]);
        }

        return UserProject::userCanStampOfficialtravelStop($user, $this, $next)
            ? collect([$next])
            : collect();
    }

    public function userCanStampAnyArrival(?User $user = null): bool
    {
        $user = $user ?? auth()->user();
        if (! $user instanceof User || $this->status !== self::STATUS_APPROVED) {
            return false;
        }

        if (! $this->canRecordArrival()) {
            return false;
        }

        if (! $this->stops()->exists()) {
            return false;
        }

        return $this->stopsEligibleForArrivalStamp($user)->isNotEmpty();
    }

    public function userCanStampAnyDeparture(?User $user = null): bool
    {
        $user = $user ?? auth()->user();
        if (! $user instanceof User || $this->status !== self::STATUS_APPROVED) {
            return false;
        }

        return $this->canRecordDeparture()
            && $this->stopsEligibleForDepartureStamp($user)->isNotEmpty();
    }

    /**
     * Full route text for lists/dashboards: ordered stops joined with arrows.
     */
    public function itinerarySummaryForDisplay(): string
    {
        $stops = $this->relationLoaded('stops')
            ? $this->stops->sortBy(['sort_order', 'id'])->values()
            : $this->stops()->orderBy('sort_order')->orderBy('id')->get();

        if ($stops->isNotEmpty()) {
            $labels = $stops->pluck('destination')->filter(fn ($d) => filled($d));
            if ($labels->isNotEmpty()) {
                return $labels->implode(' → ');
            }
        }

        return '';
    }

    /**
     * Ordered destination strings for dashboard list cells (one list item per checkpoint).
     *
     * @return \Illuminate\Support\Collection<int, string>
     */
    public function itineraryDestinationList(): \Illuminate\Support\Collection
    {
        $stops = $this->relationLoaded('stops')
            ? $this->stops->sortBy(['sort_order', 'id'])->values()
            : $this->stops()->orderBy('sort_order')->orderBy('id')->get();

        if ($stops->isNotEmpty()) {
            $labels = $stops->pluck('destination')
                ->filter(fn ($d) => filled($d))
                ->map(fn ($d) => trim((string) $d))
                ->values();
            if ($labels->isNotEmpty()) {
                return $labels;
            }
        }

        return collect();
    }

    /**
     * Match any planned stop destination (lists, export, DataTable filters, API search).
     */
    public function scopeWhereDestinationSearch(Builder $query, string $term): Builder
    {
        $term = trim($term);
        if ($term === '') {
            return $query;
        }
        $like = '%'.$term.'%';

        return $query->whereHas('stops', function (Builder $sq) use ($like) {
            $sq->where('officialtravel_stops.destination', 'like', $like);
        });
    }

    /** Next leg needing arrival (stamp queues / dashboard). */
    public function pendingArrivalDestinationLabel(): string
    {
        if ($this->stops()->count() > 1 && $this->nextDepartureStop() === null) {
            $labels = $this->stops()
                ->whereNull('arrival_at_destination')
                ->orderBy('sort_order')
                ->orderBy('id')
                ->pluck('destination')
                ->filter(fn ($d) => filled($d));
            if ($labels->count() > 1) {
                return $labels->implode('; ');
            }
        }

        $next = $this->nextArrivalStop();
        if ($next && filled($next->destination)) {
            return $next->destination;
        }

        return $this->itinerarySummaryForDisplay();
    }

    /** Next leg needing departure (stamp queues). */
    public function pendingDepartureDestinationLabel(): string
    {
        $next = $this->nextDepartureStop();
        if ($next && filled($next->destination)) {
            return $next->destination;
        }

        return '—';
    }

    // Get manual approvers as User collection
    public function getManualApprovers()
    {
        if (empty($this->manual_approvers)) {
            return collect();
        }

        return User::whereIn('id', $this->manual_approvers)->get();
    }

    /**
     * Format official travel (LOT) number.
     * Pattern: ARKA/[Letter Number]/HR-[Project Code]/bulan-romawi/tahun
     */
    public static function formatOfficialTravelNumber(string $letterNumber, string $projectCode, ?\DateTimeInterface $date = null): string
    {
        $date = $date ? \Carbon\Carbon::parse($date) : now();
        $romanMonth = self::monthToRoman((int) $date->format('n'));

        return sprintf('ARKA/%s/HR-%s/%s/%s', $letterNumber, $projectCode, $romanMonth, $date->format('Y'));
    }

    /**
     * Placeholder LOT number shown before letter number and project are selected.
     */
    public static function officialTravelNumberPlaceholder(?string $projectCode = null, ?\DateTimeInterface $date = null): string
    {
        $date = $date ? \Carbon\Carbon::parse($date) : now();
        $romanMonth = self::monthToRoman((int) $date->format('n'));
        $projectPart = $projectCode ?: '[Project Code]';

        return sprintf('ARKA/[Letter Number]/HR-%s/%s/%s', $projectPart, $romanMonth, $date->format('Y'));
    }

    private static function monthToRoman(int $number): string
    {
        $map = [
            'M' => 1000,
            'CM' => 900,
            'D' => 500,
            'CD' => 400,
            'C' => 100,
            'XC' => 90,
            'L' => 50,
            'XL' => 40,
            'X' => 10,
            'IX' => 9,
            'V' => 5,
            'IV' => 4,
            'I' => 1,
        ];

        $result = '';
        foreach ($map as $roman => $value) {
            while ($number >= $value) {
                $result .= $roman;
                $number -= $value;
            }
        }

        return $result;
    }

    // Auto-assign letter number on creation jika tidak ada
    protected static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            // Pengajuan dari user (submitted_by_user): tidak auto-assign letter number; HR yang assign saat konfirmasi
            if (! empty($model->submitted_by_user)) {
                return;
            }
            // Jika belum ada letter number, auto-assign (untuk backward compatibility)
            if (! $model->letter_number_id && ! $model->letter_number) {
                // Auto-create letter number untuk kategori B (Internal)
                $letterNumber = LetterNumber::createWithRetry([
                    'category_code' => 'B',
                    'letter_date' => $model->created_at->toDateString(),
                    'custom_subject' => 'Surat Perjalanan Dinas',
                    'administration_id' => $model->traveler_id,
                    'project_id' => $model->official_travel_origin,
                    'user_id' => auth()->id() ?? $model->created_by,
                ]);

                $model->assignLetterNumber($letterNumber->id);
            }
        });
    }
}
