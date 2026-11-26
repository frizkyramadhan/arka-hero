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
        $letter = $this->offeringLetter();

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

    /**
     * The OL stays linked to the session that first used it. A reused candidate
     * keeps that letter number on the new session, so follow source_session_id.
     */
    private function offeringLetter()
    {
        if ($this->relationLoaded('letterNumber') && $this->getRelation('letterNumber')) {
            return $this->getRelation('letterNumber');
        }

        $session = $this->relationLoaded('session') ? $this->getRelation('session') : null;
        if (! $session && $this->session_id && ! $this->relationLoaded('session')) {
            $session = $this->session()->with('sourceSession.offeringLetterRecord')->first();
        }

        $ids = [];
        $guard = 0;
        while ($session && $guard < 6) {
            $ids[] = $session->id;
            if ($session->relationLoaded('offeringLetterRecord') && $session->getRelation('offeringLetterRecord')) {
                return $session->getRelation('offeringLetterRecord');
            }
            if (! $session->source_session_id) {
                break;
            }
            $session = $session->relationLoaded('sourceSession')
                ? $session->getRelation('sourceSession')
                : $session->sourceSession()->with('offeringLetterRecord')->first();
            $guard++;
        }

        $ids = array_values(array_unique(array_filter($ids)));
        if ($ids === []) {
            return null;
        }

        return LetterNumber::query()
            ->where('related_document_type', 'recruitment_offering')
            ->whereIn('related_document_id', $ids)
            ->orderByDesc('id')
            ->first();
    }
}
