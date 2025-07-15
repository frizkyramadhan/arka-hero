<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\Uuids;

class RecruitmentDocument extends Model
{
    use Uuids;

    protected $fillable = [
        'session_id',
        'document_type',
        'original_filename',
        'stored_filename',
        'file_path',
        'file_size',
        'mime_type',
        'uploaded_by',
        'is_verified',
        'related_assessment_id',
    ];

    protected $casts = [
        'file_size' => 'integer',
        'is_verified' => 'boolean',
    ];

    protected $dates = [
        'created_at',
        'updated_at',
    ];

    // Document types
    public const DOCUMENT_TYPES = [
        'cv',
        'certificate',
        'portfolio',
        'test_result',
        'interview_report',
        'offer_letter',
        'contract',
        'mcu_report',
        'other'
    ];

    /**
     * Relationships
     */
    public function session()
    {
        return $this->belongsTo(RecruitmentSession::class, 'session_id');
    }

    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function relatedAssessment()
    {
        return $this->belongsTo(RecruitmentAssessment::class, 'related_assessment_id');
    }

    /**
     * Scopes
     */
    public function scopeByType($query, $type)
    {
        return $query->where('document_type', $type);
    }

    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    public function scopeUnverified($query)
    {
        return $query->where('is_verified', false);
    }

    /**
     * Accessors & Mutators
     */
    public function getFileSizeHumanAttribute()
    {
        $bytes = $this->file_size;
        $units = ['B', 'KB', 'MB', 'GB'];

        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }

        return round($bytes, 2) . ' ' . $units[$i];
    }

    public function getFileExistsAttribute()
    {
        return file_exists(storage_path('app/' . $this->file_path));
    }

    /**
     * Business Logic Methods
     */
    public function verify($userId = null)
    {
        $this->update([
            'is_verified' => true,
        ]);
    }

    public function unverify()
    {
        $this->update([
            'is_verified' => false,
        ]);
    }

    public function getDownloadUrl()
    {
        return route('recruitment.documents.download', $this->id);
    }
}
