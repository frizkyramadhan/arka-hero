<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class VehicleDocument extends Model
{
    use Uuids;

    public const TYPES = ['stnk', 'pkb', 'kir', 'insurance', 'other'];

    protected $fillable = [
        'vehicle_id',
        'document_type',
        'document_number',
        'document_name',
        'issue_date',
        'expiry_date',
        'issuing_authority',
        'status',
        'notes',
        'file_path',
        'file_name',
        'file_size',
        'file_uploaded_at',
        'created_by',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'expiry_date' => 'date',
        'file_uploaded_at' => 'datetime',
        'file_size' => 'integer',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function hasFile(): bool
    {
        return ! empty($this->file_path)
            && Storage::disk('private')->exists($this->file_path);
    }

    public function getDaysRemainingAttribute(): ?int
    {
        if (! $this->expiry_date) {
            return null;
        }

        return (int) now()->startOfDay()->diffInDays($this->expiry_date->copy()->startOfDay(), false);
    }

    public function refreshExpiryStatus(): void
    {
        if (! $this->expiry_date || $this->status === 'archived') {
            return;
        }

        if ($this->expiry_date->lt(now()->startOfDay())) {
            if ($this->status !== 'expired') {
                $this->update(['status' => 'expired']);
            }
        } elseif ($this->status === 'expired') {
            $this->update(['status' => 'active']);
        }
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            'active' => 'badge-success',
            'expired' => 'badge-danger',
            'pending_renewal' => 'badge-warning',
            'archived' => 'badge-secondary',
            default => 'badge-secondary',
        };
    }
}
