<?php

namespace App\Models;

use App\Models\Administration;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Project extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $fillable = [
        'project_code',
        'project_name',
        'project_location',
        'bowheer',
        'project_status',
        'leave_type'
    ];

    public function administrations()
    {
        return $this->hasMany(Administration::class);
    }

    /**
     * Get rosters through administrations
     */
    public function rosters()
    {
        return $this->hasManyThrough(
            \App\Models\Roster::class,
            Administration::class,
            'project_id', // Foreign key on administrations table
            'administration_id', // Foreign key on rosters table
            'id', // Local key on projects table
            'id' // Local key on administrations table
        );
    }

    /**
     * Get the users that belong to the project.
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_project');
    }

    /**
     * Check if this is a roster-based project
     */
    public function isRosterProject(): bool
    {
        return $this->leave_type === 'roster';
    }

    /**
     * Check if this is a non-roster project
     */
    public function isNonRosterProject(): bool
    {
        return $this->leave_type === 'non_roster';
    }

    /**
     * Get eligible leave types based on project type
     */
    public function getEligibleLeaveTypes(): array
    {
        if ($this->isRosterProject()) {
            // Skip periodic leave for now
            return ['paid', 'unpaid', 'lsl'];
        }

        return ['paid', 'unpaid', 'annual', 'lsl'];
    }

    /**
     * Scope for active projects
     */
    public function scopeActive($query)
    {
        return $query->where('project_status', 1);
    }
}
