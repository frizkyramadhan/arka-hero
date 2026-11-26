<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecruitmentOffering extends Model
{
    protected $fillable = [
        'session_id',
        'offering_letter_number',
        'result',
        'notes',
        'reviewed_by',
        'reviewed_at'
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    protected $dates = [
        'reviewed_at',
        'created_at',
        'updated_at'
    ];

    // Result options
    public const RESULTS = [
        'accepted',
        'rejected',
        'negotiating'
    ];

    /**
     * Relationships
     */
    public function session()
    {
        return $this->belongsTo(RecruitmentSession::class, 'session_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function letterNumber()
    {
        return $this->hasOne(LetterNumber::class, 'related_document_id', 'session_id')
            ->where('related_document_type', 'recruitment_offering')
            ->latest('id');
    }

    /**
     * Scopes
     */
    public function scopeAccepted($query)
    {
        return $query->where('result', 'accepted');
    }

    public function scopeRejected($query)
    {
        return $query->where('result', 'rejected');
    }

    public function scopeNegotiating($query)
    {
        return $query->where('result', 'negotiating');
    }

    /**
     * Accessors & Mutators
     */
    public function getIsAcceptedAttribute()
    {
        return $this->result === 'accepted';
    }

    public function getIsRejectedAttribute()
    {
        return $this->result === 'rejected';
    }

    public function getIsNegotiatingAttribute()
    {
        return $this->result === 'negotiating';
    }

    public function getResultLabelAttribute()
    {
        return ucfirst($this->result);
    }

    /**
     * Offering date is the Letter Date of the OL in Letter Administration.
     * Response date is reviewed_at on this offering.
     *
     * @return array{offering_date: ?\Illuminate\Support\Carbon, response_date: ?\Illuminate\Support\Carbon, response_days: ?int}
     */
    public function acceptanceTiming(): array
    {
        $letter = $this->relationLoaded('letterNumber')
            ? $this->getRelation('letterNumber')
            : $this->letterNumber()->first();

        $offeringAt = $letter && $letter->letter_date
            ? $letter->letter_date->copy()->startOfDay()
            : null;
        $responseAt = $this->reviewed_at ? $this->reviewed_at->copy()->startOfDay() : null;

        $responseDays = null;
        if ($offeringAt && $responseAt) {
            $responseDays = (int) $offeringAt->diffInDays($responseAt);
        } elseif ($offeringAt) {
            $responseDays = (int) $offeringAt->diffInDays(now()->startOfDay());
        }

        return [
            'offering_date' => $offeringAt,
            'response_date' => $responseAt,
            'response_days' => $responseDays,
        ];
    }

    public static function responseTimeLabel($days): string
    {
        if ($days === null || $days === '' || $days === '-') {
            return '-';
        }

        return ((int) $days).' Day(s)';
    }
}
