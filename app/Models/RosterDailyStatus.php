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
        'date' => 'date',
    ];

    // Relationships
    public function roster()
    {
        return $this->belongsTo(Roster::class);
    }

    // Business Logic Methods
    public function getStatusColor()
    {
        return match ($this->status_code) {
            'D' => '#FFFFFF',   // Putih dengan border untuk Day Shift
            'N' => '#ADD8E6',   // Biru muda untuk Night Shift
            'OFF' => '#FFB6C1', // Merah muda untuk Off Work
            'C' => '#90EE90',   // Hijau muda untuk Periodic Leave
            'S' => '#FFE4B5',   // Kuning muda untuk Sick Leave
            'I' => '#E6E6FA',   // Ungu muda untuk Permission
            'A' => '#FF6B6B',   // Merah untuk Absent
            default => '#FFFFFF' // Default putih untuk Day Shift
        };
    }

    public function getStatusName()
    {
        return match ($this->status_code) {
            'D' => 'Shift Siang',
            'N' => 'Shift Malam',
            'OFF' => 'Off Kerja',
            'S' => 'Sakit',
            'I' => 'Izin',
            'A' => 'Alpha',
            'C' => 'Cuti Periodik',
            default => 'Unknown'
        };
    }

    public function isWorkingDay()
    {
        return in_array($this->status_code, ['D', 'N']);
    }

    public function isLeaveDay()
    {
        return in_array($this->status_code, ['C', 'S', 'I']);
    }

    public function isOffDay()
    {
        return $this->status_code === 'OFF';
    }

    public function isAbsenceDay()
    {
        return $this->status_code === 'A';
    }
}
