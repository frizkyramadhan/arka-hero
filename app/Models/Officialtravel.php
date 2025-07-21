<?php

namespace App\Models;

use App\Models\User;
use App\Models\Project;
use App\Models\Accommodation;
use App\Models\Administration;
use App\Models\Transportation;
use App\Models\ApprovalFlow;
use App\Models\DocumentApproval;
use App\Traits\HasLetterNumber;
use App\Traits\HasApproval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Officialtravel extends Model
{
    use HasFactory;
    use HasUuids;
    use HasLetterNumber;
    use HasApproval;

    protected $guarded = [];

    // Relationships
    public function traveler()
    {
        return $this->belongsTo(Administration::class, 'traveler_id', 'id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'official_travel_origin');
    }

    public function transportation()
    {
        return $this->belongsTo(Transportation::class);
    }

    public function accommodation()
    {
        return $this->belongsTo(Accommodation::class);
    }

    public function details()
    {
        return $this->hasMany(Officialtravel_detail::class, 'official_travel_id');
    }

    public function arrivalChecker()
    {
        return $this->belongsTo(User::class, 'arrival_check_by');
    }

    public function departureChecker()
    {
        return $this->belongsTo(User::class, 'departure_check_by');
    }

    public function recommender()
    {
        return $this->belongsTo(User::class, 'recommendation_by');
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approval_by');
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Approval System Integration
    public function approvalFlow()
    {
        return $this->belongsTo(ApprovalFlow::class, 'approval_flow_id');
    }

    /**
     * Get document type untuk letter number tracking
     *
     * @return string
     */
    protected function getDocumentType(): string
    {
        return 'officialtravel';
    }

    /**
     * Get approval document type for approval system
     *
     * @return string
     */
    public function getApprovalDocumentType(): string
    {
        return 'officialtravel';
    }

    /**
     * Get approval document ID
     *
     * @return string
     */
    public function getApprovalDocumentId(): string
    {
        return $this->id;
    }

    /**
     * Get approval metadata
     *
     * @return array
     */
    public function getApprovalMetadata(): array
    {
        return [
            'traveler_name' => $this->traveler->name ?? 'Unknown',
            'destination' => $this->official_travel_destination ?? 'Unknown',
            'purpose' => $this->official_travel_purpose ?? 'Unknown',
            'start_date' => $this->official_travel_start_date ?? null,
            'end_date' => $this->official_travel_end_date ?? null,
            'project_name' => $this->project->name ?? 'Unknown',
            'total_cost' => $this->official_travel_total_cost ?? 0,
            'letter_number' => $this->letter_number ?? null,
            'created_at' => $this->created_at->toISOString(),
            'submitted_by' => $this->created_by
        ];
    }

    /**
     * Check if document can be approved
     *
     * @return bool
     */
    public function canBeApproved(): bool
    {
        // Check if document is in draft or submitted status
        return in_array($this->recommendation_status, ['pending', 'draft']) ||
            in_array($this->approval_status, ['pending', 'draft']);
    }

    /**
     * Handle approval completion
     */
    public function onApprovalCompleted(): void
    {
        // Update existing approval status to approved
        $this->update([
            'recommendation_status' => 'approved',
            'approval_status' => 'approved',
            'recommendation_date' => now(),
            'approval_date' => now()
        ]);

        // Log the approval completion
        Log::info('Official Travel approval completed', [
            'document_id' => $this->id,
            'traveler' => $this->traveler->name ?? 'Unknown',
            'approved_at' => now()
        ]);
    }

    /**
     * Handle approval rejection
     */
    public function onApprovalRejected(): void
    {
        // Update existing approval status to rejected
        $this->update([
            'recommendation_status' => 'rejected',
            'approval_status' => 'rejected',
            'recommendation_date' => now(),
            'approval_date' => now()
        ]);

        // Log the approval rejection
        Log::info('Official Travel approval rejected', [
            'document_id' => $this->id,
            'traveler' => $this->traveler->name ?? 'Unknown',
            'rejected_at' => now()
        ]);
    }

    /**
     * Get current approval status
     *
     * @return string
     */
    public function getCurrentApprovalStatus(): string
    {
        if ($this->approval_status === 'rejected' || $this->recommendation_status === 'rejected') {
            return 'rejected';
        }

        if ($this->approval_status === 'approved' && $this->recommendation_status === 'approved') {
            return 'approved';
        }

        if ($this->approval_status === 'pending' && $this->recommendation_status === 'approved') {
            return 'pending_approval';
        }

        if ($this->recommendation_status === 'pending') {
            return 'pending_recommendation';
        }

        return 'pending';
    }

    /**
     * Get approval progress percentage
     *
     * @return int
     */
    public function getApprovalProgress(): int
    {
        $progress = 0;

        if ($this->recommendation_status === 'approved') {
            $progress += 50;
        }

        if ($this->approval_status === 'approved') {
            $progress += 50;
        }

        return $progress;
    }

    /**
     * Check if approval is overdue
     *
     * @return bool
     */
    public function isApprovalOverdue(): bool
    {
        $overdueHours = 72; // 3 days

        if ($this->recommendation_status === 'pending' && $this->created_at->diffInHours(now()) > $overdueHours) {
            return true;
        }

        if (
            $this->approval_status === 'pending' && $this->recommendation_date &&
            $this->recommendation_date->diffInHours(now()) > $overdueHours
        ) {
            return true;
        }

        return false;
    }

    // Integration dengan Letter Number System
    public function letterNumber()
    {
        return $this->belongsTo(LetterNumber::class, 'letter_number_id');
    }

    // Method untuk assign letter number
    public function assignLetterNumber($letterNumberId)
    {
        $letterNumber = LetterNumber::find($letterNumberId);

        if ($letterNumber && $letterNumber->status === 'reserved') {
            $this->letter_number_id = $letterNumberId;
            $this->letter_number = $letterNumber->letter_number;
            $this->save();

            // Mark letter number as used
            $letterNumber->markAsUsed('officialtravel', $this->id);

            return true;
        }

        return false;
    }

    // Auto-assign letter number on creation jika tidak ada
    protected static function boot()
    {
        parent::boot();

        static::created(function ($model) {
            // Jika belum ada letter number, auto-assign (untuk backward compatibility)
            if (!$model->letter_number_id && !$model->letter_number) {
                // Auto-create letter number untuk kategori B (Internal)
                $letterNumber = LetterNumber::create([
                    'category_code' => 'B',
                    'letter_date' => $model->created_at->toDateString(),
                    'custom_subject' => 'Surat Perjalanan Dinas',
                    'administration_id' => $model->traveler_id,
                    'project_id' => $model->official_travel_origin,
                    'user_id' => auth()->id() ?? $model->created_by,
                ]);

                $model->assignLetterNumber($letterNumber->id);
            }
        });
    }
}
