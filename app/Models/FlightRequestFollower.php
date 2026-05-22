<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlightRequestFollower extends Model
{
    use HasFactory, Uuids;

    protected $guarded = [];

    public function flightRequest()
    {
        return $this->belongsTo(FlightRequest::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function administration()
    {
        return $this->belongsTo(Administration::class);
    }

    public function isManual(): bool
    {
        return $this->employee_id === null && $this->administration_id === null;
    }

    public function displayName(): string
    {
        $name = 'N/A';

        if ($this->follower_name) {
            $name = $this->follower_name;
        } elseif ($this->relationLoaded('employee') && $this->employee) {
            $name = $this->employee->fullname ?? 'N/A';
        } elseif ($this->administration && $this->administration->relationLoaded('employee') && $this->administration->employee) {
            $name = $this->administration->employee->fullname ?? 'N/A';
        }

        if ($this->title && $name !== 'N/A') {
            $title = $this->title;
            if (! str_ends_with($title, '.')) {
                $title .= '.';
            }

            return $name.' ('.$title.')';
        }

        return $name;
    }

    public function idLabel(): string
    {
        return $this->isManual() ? 'KTP' : 'NIK';
    }
}
