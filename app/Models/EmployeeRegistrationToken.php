<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmployeeRegistrationToken extends Model
{
    use HasFactory, Uuids;

    protected $guarded = [];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    public function createdBy()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function registration()
    {
        return $this->hasOne(EmployeeRegistration::class, 'token_id');
    }

    public function isExpired()
    {
        return $this->expires_at->isPast();
    }

    public function scopeValid($query)
    {
        return $query->where('status', 'pending')
            ->where('expires_at', '>', now());
    }
}
