<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DisciplinaryCriterion extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'title',
        'description',
        'article_reference',
        'sanction_type',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function disciplinaries(): BelongsToMany
    {
        return $this->belongsToMany(
            EmployeeDisciplinary::class,
            'employee_disciplinary_criterion',
            'disciplinary_criterion_id',
            'employee_disciplinary_id'
        )->withTimestamps();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForSanctionType($query, string $type)
    {
        return $query->where('sanction_type', $type);
    }

    public function getDisplayLabelAttribute(): string
    {
        $parts = [$this->code, $this->title];
        if ($this->article_reference) {
            $parts[] = '('.$this->article_reference.')';
        }

        return implode(' — ', array_filter($parts));
    }
}
