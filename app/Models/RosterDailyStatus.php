<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RosterDailyStatus extends Model
{
    use HasFactory;

    protected $table = 'roster_daily_status';

    protected $fillable = [
        'roster_id',
        'date',
        'status_code',
        'notes'
    ];

    protected $casts = [
        'date' => 'date'
    ];

    // Relationships
    public function roster()
    {
        return $this->belongsTo(Roster::class);
    }
}

