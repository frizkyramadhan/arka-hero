<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class RegistrationDocument extends Model
{
    use HasFactory, Uuids;

    protected $guarded = [];

    protected $casts = [
        'is_verified' => 'boolean',
        'file_size' => 'integer',
    ];

    public function registration()
    {
        return $this->belongsTo(EmployeeRegistration::class, 'registration_id');
    }

    public function getFileSizeHumanAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeByDocumentType($query, $type)
    {
        return $query->where('document_type', $type);
    }
}
