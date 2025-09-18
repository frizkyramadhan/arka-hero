<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BondViolation extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_bond_id',
        'violation_date',
        'reason',
        'days_worked',
        'days_remaining',
        'calculated_penalty_amount',
        'penalty_paid_amount',
        'payment_due_date'
    ];

    protected $casts = [
        'violation_date' => 'date',
        'calculated_penalty_amount' => 'decimal:2',
        'penalty_paid_amount' => 'decimal:2',
        'payment_due_date' => 'date',
    ];

    // Relationships
    public function employeeBond()
    {
        return $this->belongsTo(EmployeeBond::class);
    }

    // Accessors
    public function getRemainingPenaltyAttribute()
    {
        return $this->calculated_penalty_amount - $this->penalty_paid_amount;
    }

    public function getFormattedCalculatedPenaltyAttribute()
    {
        return 'Rp ' . number_format($this->calculated_penalty_amount, 0, ',', '.');
    }

    public function getFormattedPaidPenaltyAttribute()
    {
        return 'Rp ' . number_format($this->penalty_paid_amount, 0, ',', '.');
    }

    public function getFormattedRemainingPenaltyAttribute()
    {
        return 'Rp ' . number_format($this->remaining_penalty, 0, ',', '.');
    }

    public function getPaymentStatusAttribute()
    {
        if ($this->penalty_paid_amount == 0) {
            return 'pending';
        } elseif ($this->penalty_paid_amount >= $this->calculated_penalty_amount) {
            return 'paid';
        } else {
            return 'partial';
        }
    }

    // Scopes
    public function scopePending($query)
    {
        return $query->whereRaw('penalty_paid_amount = 0');
    }

    public function scopePaid($query)
    {
        return $query->whereRaw('penalty_paid_amount >= calculated_penalty_amount');
    }

    public function scopePartial($query)
    {
        return $query->whereRaw('penalty_paid_amount > 0 AND penalty_paid_amount < calculated_penalty_amount');
    }
}
