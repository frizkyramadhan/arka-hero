<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ApprovalFlow extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'description',
        'document_type',
        'is_active',
        'created_by',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * Get the stages for this approval flow.
     */
    public function stages(): HasMany
    {
        return $this->hasMany(ApprovalStage::class)->orderBy('stage_order');
    }

    /**
     * Get the document approvals for this flow.
     */
    public function documentApprovals(): HasMany
    {
        return $this->hasMany(DocumentApproval::class);
    }

    /**
     * Get the user who created this flow.
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Scope a query to only include active flows.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope a query to only include flows for a specific document type.
     */
    public function scopeForDocumentType($query, string $documentType)
    {
        return $query->where('document_type', $documentType);
    }

    /**
     * Get the first stage of this flow.
     */
    public function getFirstStage()
    {
        return $this->stages()->orderBy('stage_order')->first();
    }

    /**
     * Get the last stage of this flow.
     */
    public function getLastStage()
    {
        return $this->stages()->orderBy('stage_order', 'desc')->first();
    }

    /**
     * Check if this flow has any stages.
     */
    public function hasStages(): bool
    {
        return $this->stages()->exists();
    }

    /**
     * Get the total number of stages in this flow.
     */
    public function getStageCount(): int
    {
        return $this->stages()->count();
    }

    /**
     * Check if this flow is valid (has stages and is active).
     */
    public function isValid(): bool
    {
        return $this->is_active && $this->hasStages();
    }
}
