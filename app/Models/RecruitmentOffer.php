<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;

class RecruitmentOffer extends Model
{
    use Uuids;

    protected $fillable = [
        'session_id',
        'offer_letter_number',
        'basic_salary',
        'allowances',
        'benefits',
        'contract_duration',
        'probation_period',
        'start_date',
        'offer_valid_until',
        'status',
        'sent_at',
        'responded_at',
        'response_notes',
        'negotiation_history',
        'created_by',
    ];

    protected $casts = [
        'basic_salary' => 'decimal:2',
        'allowances' => 'array',
        'benefits' => 'array',
        'contract_duration' => 'integer',
        'probation_period' => 'integer',
        'start_date' => 'date',
        'offer_valid_until' => 'date',
        'sent_at' => 'datetime',
        'responded_at' => 'datetime',
        'negotiation_history' => 'array',
    ];

    protected $dates = [
        'start_date',
        'offer_valid_until',
        'sent_at',
        'responded_at',
        'created_at',
        'updated_at',
    ];

    // Offer statuses
    public const STATUSES = [
        'draft',
        'sent',
        'accepted',
        'rejected',
        'expired',
        'withdrawn'
    ];

    /**
     * Relationships
     */
    public function session()
    {
        return $this->belongsTo(RecruitmentSession::class, 'session_id');
    }

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scopes
     */
    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopeSent($query)
    {
        return $query->where('status', 'sent');
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', 'accepted');
    }

    public function scopeRejected($query)
    {
        return $query->where('status', 'rejected');
    }

    public function scopeExpired($query)
    {
        return $query->where('status', 'expired');
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'sent')
            ->where('offer_valid_until', '>', now());
    }

    public function scopeExpiredOffers($query)
    {
        return $query->where('status', 'sent')
            ->where('offer_valid_until', '<', now());
    }

    /**
     * Accessors & Mutators
     */
    public function getIsDraftAttribute()
    {
        return $this->status === 'draft';
    }

    public function getIsSentAttribute()
    {
        return $this->status === 'sent';
    }

    public function getIsAcceptedAttribute()
    {
        return $this->status === 'accepted';
    }

    public function getIsRejectedAttribute()
    {
        return $this->status === 'rejected';
    }

    public function getIsExpiredAttribute()
    {
        return $this->status === 'expired' ||
            ($this->status === 'sent' && $this->offer_valid_until < now());
    }

    public function getIsActiveAttribute()
    {
        return $this->status === 'sent' && $this->offer_valid_until > now();
    }

    public function getTotalCompensationAttribute()
    {
        $total = $this->basic_salary;

        if ($this->allowances) {
            foreach ($this->allowances as $allowance) {
                if (is_numeric($allowance)) {
                    $total += $allowance;
                }
            }
        }

        return $total;
    }

    public function getAllowancesFormattedAttribute()
    {
        if (!$this->allowances) {
            return [];
        }

        $formatted = [];
        foreach ($this->allowances as $key => $value) {
            $formatted[] = [
                'name' => ucwords(str_replace('_', ' ', $key)),
                'amount' => is_numeric($value) ? number_format($value, 0, ',', '.') : $value,
            ];
        }

        return $formatted;
    }

    public function getBenefitsFormattedAttribute()
    {
        if (!$this->benefits) {
            return [];
        }

        $formatted = [];
        foreach ($this->benefits as $key => $value) {
            $formatted[] = [
                'name' => ucwords(str_replace('_', ' ', $key)),
                'value' => is_bool($value) ? ($value ? 'Ya' : 'Tidak') : $value,
            ];
        }

        return $formatted;
    }

    public function getDaysUntilExpiryAttribute()
    {
        if (!$this->offer_valid_until) {
            return null;
        }

        return now()->diffInDays($this->offer_valid_until, false);
    }

    public function getContractTypeAttribute()
    {
        if ($this->contract_duration) {
            return 'PKWT (' . $this->contract_duration . ' bulan)';
        }

        return 'PKWTT';
    }

    /**
     * Business Logic Methods
     */
    public function send()
    {
        if ($this->status !== 'draft') {
            return false;
        }

        $this->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        // TODO: Send notification email to candidate

        return true;
    }

    public function accept($notes = null)
    {
        if ($this->status !== 'sent') {
            return false;
        }

        if ($this->offer_valid_until < now()) {
            return false; // Expired
        }

        $this->update([
            'status' => 'accepted',
            'responded_at' => now(),
            'response_notes' => $notes,
        ]);

        return true;
    }

    public function reject($reason = null)
    {
        if ($this->status !== 'sent') {
            return false;
        }

        $this->update([
            'status' => 'rejected',
            'responded_at' => now(),
            'response_notes' => $reason,
        ]);

        return true;
    }

    public function withdraw($reason = null)
    {
        if (!in_array($this->status, ['draft', 'sent'])) {
            return false;
        }

        $this->update([
            'status' => 'withdrawn',
            'response_notes' => $reason,
        ]);

        return true;
    }

    public function markAsExpired()
    {
        if ($this->status !== 'sent') {
            return false;
        }

        $this->update([
            'status' => 'expired',
        ]);

        return true;
    }

    public function extendValidity($newExpiryDate)
    {
        if ($this->status !== 'sent') {
            return false;
        }

        $this->update([
            'offer_valid_until' => $newExpiryDate,
        ]);

        return true;
    }

    public function addNegotiationRound($round)
    {
        $history = $this->negotiation_history ?? [];
        $history[] = array_merge($round, ['timestamp' => now()]);

        $this->update([
            'negotiation_history' => $history,
        ]);

        return true;
    }

    public function updateCompensation($basicSalary, $allowances = null, $benefits = null)
    {
        if ($this->status !== 'draft') {
            return false;
        }

        $updateData = ['basic_salary' => $basicSalary];

        if ($allowances !== null) {
            $updateData['allowances'] = $allowances;
        }

        if ($benefits !== null) {
            $updateData['benefits'] = $benefits;
        }

        $this->update($updateData);

        return true;
    }

    public function canBeModified()
    {
        return $this->status === 'draft';
    }

    public function canBeAccepted()
    {
        return $this->status === 'sent' && $this->offer_valid_until > now();
    }

    public function canBeRejected()
    {
        return $this->status === 'sent';
    }

    public function canBeWithdrawn()
    {
        return in_array($this->status, ['draft', 'sent']);
    }

    /**
     * Generate unique offer letter number
     */
    public static function generateOfferNumber()
    {
        $year = date('Y');
        $month = date('m');

        $lastNumber = static::whereRaw('YEAR(created_at) = ? AND MONTH(created_at) = ?', [$year, $month])
            ->orderBy('created_at', 'desc')
            ->first();

        $sequence = 1;
        if ($lastNumber && preg_match('/OFL\/\d+\/\d+\/(\d+)$/', $lastNumber->offer_letter_number, $matches)) {
            $sequence = intval($matches[1]) + 1;
        }

        return sprintf('OFL/%d/%02d/%04d', $year, $month, $sequence);
    }

    /**
     * Boot method
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->offer_letter_number)) {
                $model->offer_letter_number = static::generateOfferNumber();
            }
        });
    }
}
