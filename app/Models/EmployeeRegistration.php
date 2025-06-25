<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class EmployeeRegistration extends Model
{
    use HasFactory, Uuids;

    protected $guarded = [];

    protected $casts = [
        'personal_data' => 'array',
        'document_files' => 'array',
        'reviewed_at' => 'datetime',
    ];

    public function token()
    {
        return $this->belongsTo(EmployeeRegistrationToken::class, 'token_id');
    }

    public function reviewedBy()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function documents()
    {
        return $this->hasMany(RegistrationDocument::class, 'registration_id');
    }

    public function scopeStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'submitted');
    }
}
