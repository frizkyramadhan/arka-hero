<?php

namespace App\Models;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class EmployeeBond extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_id',
        'letter_number_id',
        'letter_number',
        'employee_bond_number',
        'bond_name',
        'description',
        'start_date',
        'end_date',
        'total_bond_duration_months',
        'total_investment_value',
        'status',
        'document_path'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'total_investment_value' => 'decimal:2',
    ];

    // Relationships
    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function letterNumber()
    {
        return $this->belongsTo(LetterNumber::class);
    }

    public function violations()
    {
        return $this->hasMany(BondViolation::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpiringSoon($query, $days = 30)
    {
        return $query->where('status', 'active')
            ->where('end_date', '<=', now()->addDays($days));
    }

    // Accessors
    public function getRemainingDaysAttribute()
    {
        if ($this->status !== 'active') {
            return 0;
        }

        return max(0, now()->diffInDays($this->end_date, false));
    }

    public function getPenaltyPerMonthAttribute()
    {
        return $this->total_investment_value / $this->total_bond_duration_months;
    }

    public function getPenaltyPerDayAttribute()
    {
        return $this->penalty_per_month / 30; // Assuming 30 days per month
    }

    // Methods
    /**
     * Calculate prorate penalty based on violation date
     */
    public function calculateProratePenalty($violationDate = null)
    {
        $violationDate = $violationDate ?? now();

        // Ensure violation date is within bond period
        if ($violationDate < $this->start_date || $violationDate > $this->end_date) {
            return [
                'is_valid' => false,
                'message' => 'Violation date must be within bond period',
                'penalty_amount' => 0,
                'calculation_details' => []
            ];
        }

        // Calculate total days in bond period
        $totalDays = $this->start_date->diffInDays($this->end_date);

        // Calculate days worked (from start to violation date)
        $daysWorked = $this->start_date->diffInDays($violationDate);

        // Calculate remaining days
        $remainingDays = $violationDate->diffInDays($this->end_date);

        // Calculate prorate penalty
        $penaltyAmount = ($this->total_investment_value * $remainingDays) / $totalDays;

        // Calculate percentage worked
        $percentageWorked = ($daysWorked / $totalDays) * 100;
        $percentageRemaining = ($remainingDays / $totalDays) * 100;

        return [
            'is_valid' => true,
            'penalty_amount' => round($penaltyAmount, 2),
            'calculation_details' => [
                'total_days' => $totalDays,
                'days_worked' => $daysWorked,
                'remaining_days' => $remainingDays,
                'percentage_worked' => round($percentageWorked, 2),
                'percentage_remaining' => round($percentageRemaining, 2),
                'investment_value' => $this->total_investment_value,
                'penalty_per_month' => round($this->penalty_per_month, 2),
                'penalty_per_day' => round($this->penalty_per_day, 2)
            ]
        ];
    }

    /**
     * Create violation record with prorate calculation
     */
    public function createViolation($violationDate, $reason = null)
    {
        $calculation = $this->calculateProratePenalty($violationDate);

        if (!$calculation['is_valid']) {
            throw new \Exception($calculation['message']);
        }

        $details = $calculation['calculation_details'];

        return $this->violations()->create([
            'violation_date' => $violationDate,
            'reason' => $reason,
            'days_worked' => $details['days_worked'],
            'days_remaining' => $details['remaining_days'],
            'calculated_penalty_amount' => $calculation['penalty_amount']
        ]);
    }
}
