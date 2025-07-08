<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LetterNumber extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $dates = ['letter_date', 'start_date', 'end_date', 'used_at'];

    protected $casts = [
        'used_at' => 'datetime',
        'is_active' => 'boolean',
        'letter_date' => 'date',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    // Relationships
    public function category()
    {
        return $this->belongsTo(LetterCategory::class, 'letter_category_id');
    }

    public function subject()
    {
        return $this->belongsTo(LetterSubject::class, 'subject_id');
    }

    public function administration()
    {
        return $this->belongsTo(Administration::class, 'administration_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function reservedBy()
    {
        return $this->belongsTo(User::class, 'reserved_by');
    }

    public function usedBy()
    {
        return $this->belongsTo(User::class, 'used_by');
    }

    // Integration relationships
    public function officialTravel()
    {
        return $this->hasOne(Officialtravel::class, 'letter_number_id');
    }

    // Dynamic relationship berdasarkan related_document_type
    public function relatedDocument()
    {
        switch ($this->related_document_type) {
            case 'officialtravel':
                return $this->officialTravel();
                // case 'future_document_type':
                //     return $this->futureDocument();
            default:
                return null;
        }
    }

    // Accessor untuk mendapatkan data employee melalui administration
    public function getEmployeeAttribute()
    {
        return $this->administration ? $this->administration->employee : null;
    }

    public function getNikAttribute()
    {
        return $this->administration ? $this->administration->nik : null;
    }

    public function getEmployeeNameAttribute()
    {
        return $this->administration && $this->administration->employee ?
            $this->administration->employee->fullname : null;
    }

    // Mendapatkan project dari administration atau dari field project_id langsung
    public function getEmployeeProjectAttribute()
    {
        if ($this->administration && $this->administration->project) {
            return $this->administration->project;
        }
        return $this->project;
    }

    // Generate letter number
    public function generateLetterNumber()
    {
        // Pastikan relasi category sudah di-load untuk mendapatkan behavior dan code
        if (!$this->relationLoaded('category')) {
            $this->load('category');
        }

        $category = $this->category;
        $year = date('Y', strtotime($this->letter_date));
        $month = date('m', strtotime($this->letter_date));

        $query = static::where('letter_category_id', $this->letter_category_id);

        // Terapkan logika berdasarkan behavior penomoran
        if ($category->numbering_behavior === 'annual_reset') {
            // Reset setiap tahun
            $query->whereYear('letter_date', $year);
        }
        // Untuk 'continuous', kita tidak membatasi query berdasarkan tahun

        $sequence = $query->max('sequence_number') + 1;

        $this->sequence_number = $sequence;
        $this->year = $year; // Kolom tahun tetap diisi untuk pencatatan

        // Format penomoran
        $formattedSequence = sprintf('%04d', $sequence);
        $this->letter_number = "{$category->category_code}{$formattedSequence}";
    }

    /**
     * Converts a month number to its Roman numeral representation.
     *
     * @param int $month
     * @return string
     */
    private function getRomanMonth($month)
    {
        $map = [
            1 => 'I',
            2 => 'II',
            3 => 'III',
            4 => 'IV',
            5 => 'V',
            6 => 'VI',
            7 => 'VII',
            8 => 'VIII',
            9 => 'IX',
            10 => 'X',
            11 => 'XI',
            12 => 'XII'
        ];
        return $map[(int)$month];
    }

    // Mark nomor sebagai used
    public function markAsUsed($documentType, $documentId, $userId = null)
    {
        $this->update([
            'status' => 'used',
            'related_document_type' => $documentType,
            'related_document_id' => $documentId,
            'used_at' => now(),
            'used_by' => $userId ?? auth()->id(),
        ]);
    }

    // Cancel reserved nomor
    public function cancel()
    {
        $this->update([
            'status' => 'cancelled',
        ]);
    }

    // Scope untuk filter berdasarkan status
    public function scopeReserved($query)
    {
        return $query->where('status', 'reserved');
    }

    public function scopeUsed($query)
    {
        return $query->where('status', 'used');
    }

    public function scopeAvailable($query)
    {
        return $query->whereIn('status', ['reserved']);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('letter_category_id', $categoryId);
    }

    public function scopeByYear($query, $year)
    {
        return $query->where('year', $year);
    }

    // Get next sequence number for a category
    public static function getNextSequenceNumber($categoryId)
    {
        $currentYear = date('Y');
        $lastNumber = static::byCategory($categoryId)
            ->where('year', $currentYear)
            ->orderBy('sequence_number', 'desc')
            ->first();

        return $lastNumber ? $lastNumber->sequence_number + 1 : 1;
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->letter_number)) {
                $model->generateLetterNumber();
            }
            $model->reserved_by = auth()->id() ?? 1; // Default to user ID 1 if no auth
            $model->status = 'reserved';
        });
    }
}
