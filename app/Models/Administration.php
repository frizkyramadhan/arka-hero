<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Administration extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function position()
    {
        return $this->belongsTo(Position::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Integration dengan Letter Number System
    public function letterNumbers()
    {
        return $this->hasMany(LetterNumber::class, 'administration_id');
    }

    // Scope untuk karyawan aktif
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    // Accessor untuk full employee info
    public function getEmployeeFullInfoAttribute()
    {
        return $this->nik . ' - ' . ($this->employee ? $this->employee->fullname : 'N/A') .
            ' (' . ($this->project ? $this->project->project_name : 'No Project') . ')';
    }
}
