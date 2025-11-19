<?php

namespace App\Models;

use App\Models\User;
use App\Models\Project;
use App\Models\Accommodation;
use App\Models\Administration;
use App\Models\Transportation;
use App\Traits\HasLetterNumber;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Officialtravel extends Model
{
    use HasFactory;
    use HasUuids;
    use HasLetterNumber;


    protected $guarded = [];

    protected $casts = [
        'official_travel_date' => 'date',
        'departure_from' => 'date',
        // Note: arrival_at_destination and departure_from_destination have been moved to officialtravel_stops table
        'manual_approvers' => 'array',
    ];

    // Constants (Legacy approval constants removed - using new approval system)

    // Status enum values
    public const STATUS_DRAFT = 'draft';
    public const STATUS_SUBMITTED = 'submitted';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';
    public const STATUS_CANCELLED = 'cancelled';
    public const STATUS_CLOSED = 'closed';

    public static function getStatusOptions()
    {
        return [
            self::STATUS_DRAFT => 'Draft',
            self::STATUS_SUBMITTED => 'Submitted',
            self::STATUS_APPROVED => 'Approved',
            self::STATUS_REJECTED => 'Rejected',
            self::STATUS_CANCELLED => 'Cancelled',
            self::STATUS_CLOSED => 'Closed',
        ];
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
        return $this->hasMany(OfficialtravelStop::class, 'official_travel_id', 'id');
    }

    public function latestStop()
    {
        return $this->hasOne(OfficialtravelStop::class, 'official_travel_id', 'id')->latest();
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



    /**
     * Get document type untuk letter number tracking
     *
     * @return string
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

        $latestStop = $this->latestStop;
        if (!$latestStop) {
            return true; // No stops yet, can record arrival
        }

        // Can record arrival if latest stop is complete (has both arrival and departure)
        // or if latest stop has no arrival yet
        return $latestStop->isComplete() || !$latestStop->hasArrival();
    }

    public function canRecordDeparture()
    {
        if ($this->status !== 'approved') {
            return false;
        }

        $latestStop = $this->latestStop;
        if (!$latestStop) {
            return false; // No stops yet, need arrival first
        }

        // Can record departure if latest stop has arrival but no departure
        return $latestStop->hasArrival() && !$latestStop->hasDeparture();
    }

    public function canClose()
    {
        if ($this->status !== 'approved') {
            return false;
        }

        $latestStop = $this->latestStop;
        if (!$latestStop) {
            return false; // No stops yet
        }

        // Can close if latest stop is complete (has both arrival and departure)
        return $latestStop->isComplete();
    }

    public function getCurrentStopStatus()
    {
        $latestStop = $this->latestStop;
        if (!$latestStop) {
            return 'no_stops';
        }

        if ($latestStop->isComplete()) {
            return 'complete';
        } elseif ($latestStop->isArrivalOnly()) {
            return 'arrival_only';
        } elseif ($latestStop->isDepartureOnly()) {
            return 'departure_only';
        }

        return 'unknown';
    }

    // Get manual approvers as User collection
    public function getManualApprovers()
    {
        if (empty($this->manual_approvers)) {
            return collect();
        }

        return User::whereIn('id', $this->manual_approvers)->get();
    }

    // Auto-assign letter number on creation jika tidak ada
    protected static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            // Jika belum ada letter number, auto-assign (untuk backward compatibility)
            if (!$model->letter_number_id && !$model->letter_number) {
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
