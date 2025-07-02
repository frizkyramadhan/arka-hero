<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LetterCategory extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function subjects()
    {
        return $this->hasMany(LetterSubject::class, 'category_code', 'category_code');
    }

    public function letterNumbers()
    {
        return $this->hasMany(LetterNumber::class, 'category_code', 'category_code');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    // Accessors
    public function getActiveSubjectsAttribute()
    {
        return $this->subjects()->where('is_active', 1)->get();
    }
}
