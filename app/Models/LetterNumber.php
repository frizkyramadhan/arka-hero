<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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

    /**
     * Generate letter number dengan pendekatan yang lebih reliable
     * Menggunakan database sequence untuk menghindari race condition
     */
    public function generateLetterNumberReliable()
    {
        // Pastikan relasi category sudah di-load
        if (!$this->relationLoaded('category')) {
            $this->load('category');
        }

        $category = $this->category;
        $year = date('Y', strtotime($this->letter_date));

        // Gunakan database transaction dengan retry logic
        return DB::transaction(function () use ($category, $year) {
            $maxAttempts = 5;
            $attempt = 0;

            do {
                $attempt++;

                // Dapatkan sequence number terbaru dengan lock
                $query = static::where('letter_category_id', $this->letter_category_id);

                if ($category->numbering_behavior === 'annual_reset') {
                    $query->whereYear('letter_date', $year);
                }

                // Gunakan lockForUpdate untuk mencegah race condition
                $lastNumber = $query->lockForUpdate()
                    ->orderBy('sequence_number', 'desc')
                    ->first();

                $nextSequence = $lastNumber ? $lastNumber->sequence_number + 1 : 1;

                // Set sequence dan generate letter number
                $this->sequence_number = $nextSequence;
                $this->year = $year;
                $formattedSequence = sprintf('%04d', $nextSequence);

                // Format letter number (format asli tanpa tahun)
                $this->letter_number = "{$category->category_code}{$formattedSequence}";

                // Cek apakah letter number sudah ada untuk tahun yang sama (double check)
                $exists = static::where('letter_number', $this->letter_number)
                    ->where('year', $year)
                    ->exists();

                if (!$exists) {
                    return true; // Berhasil generate unique number
                }

                // Jika masih ada, tunggu dan coba lagi
                if ($attempt < $maxAttempts) {
                    usleep(200000); // Tunggu 0.2 detik
                }
            } while ($attempt < $maxAttempts);

            throw new \Exception("Failed to generate unique letter number after {$maxAttempts} attempts");
        });
    }

    /**
     * Generate letter number (legacy method - now uses generateLetterNumberReliable)
     */
    public function generateLetterNumber()
    {
        return $this->generateLetterNumberReliable();
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

    /**
     * Get next sequence number dengan cara yang aman untuk menghindari race condition
     *
     * @param int $categoryId
     * @param string|null $year
     * @return int
     */
    public static function getNextSequenceNumberSafe($categoryId, $year = null)
    {
        $year = $year ?? date('Y');

        return DB::transaction(function () use ($categoryId, $year) {
            $category = LetterCategory::find($categoryId);
            if (!$category) {
                throw new \Exception('Letter category not found');
            }

            $query = static::where('letter_category_id', $categoryId);

            if ($category->numbering_behavior === 'annual_reset') {
                $query->whereYear('letter_date', $year);
            }

            $lastNumber = $query->lockForUpdate()
                ->orderBy('sequence_number', 'desc')
                ->first();

            return $lastNumber ? $lastNumber->sequence_number + 1 : 1;
        });
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

    /**
     * Create letter number dengan retry logic untuk menghindari duplicate entry
     *
     * @param array $attributes
     * @return static
     * @throws \Exception
     */
    public static function createWithRetry(array $attributes)
    {
        $maxAttempts = 5;
        $attempt = 0;

        do {
            $attempt++;

            try {
                return static::create($attributes);
            } catch (\Illuminate\Database\QueryException $e) {
                // Cek apakah error adalah duplicate entry
                if ($e->getCode() == 23000 && strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    if ($attempt >= $maxAttempts) {
                        throw new \Exception("Failed to create letter number after {$maxAttempts} attempts due to duplicate entry");
                    }

                    // Tunggu sebentar sebelum retry
                    usleep(300000); // 0.3 detik
                    continue;
                }

                // Jika bukan duplicate entry, throw exception asli
                throw $e;
            }
        } while ($attempt < $maxAttempts);

        throw new \Exception("Failed to create letter number after {$maxAttempts} attempts");
    }

    /**
     * Create or Update letter number berdasarkan ID atau letter_number
     *
     * @param array $attributes
     * @return static
     */
    public static function createOrUpdate(array $attributes)
    {
        // Priority 1: Update by ID if provided
        if (!empty($attributes['id'])) {
            $existingRecord = static::find($attributes['id']);
            if ($existingRecord) {
                $existingRecord->update($attributes);
                return $existingRecord;
            }
        }

        // Priority 2: Update by letter_number if provided
        if (!empty($attributes['letter_number'])) {
            $existingRecord = static::where('letter_number', $attributes['letter_number'])->first();
            if ($existingRecord) {
                $existingRecord->update($attributes);
                return $existingRecord;
            }
        }

        // Priority 3: Create new record
        return static::create($attributes);
    }

    /**
     * Get estimated next letter number for a category
     */
    public static function getEstimatedNextNumber($categoryId, $year = null)
    {
        if (!$year) {
            $year = date('Y');
        }

        try {
            $category = LetterCategory::find($categoryId);
            if (!$category) {
                return null;
            }

            $query = static::where('letter_category_id', $categoryId);

            // Check if numbering_behavior exists and handle accordingly
            if (isset($category->numbering_behavior) && $category->numbering_behavior === 'annual_reset') {
                $query->whereYear('letter_date', $year);
            }

            $lastNumber = $query->orderBy('sequence_number', 'desc')->first();
            $nextSequence = $lastNumber ? $lastNumber->sequence_number + 1 : 1;
            $formattedSequence = sprintf('%04d', $nextSequence);

            return [
                'next_sequence' => $nextSequence,
                'next_letter_number' => "{$category->category_code}{$formattedSequence}",
                'year' => $year,
                'category_code' => $category->category_code
            ];
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error getting estimated next number: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Get estimated next numbers for all active categories
     */
    public static function getEstimatedNextNumbersForAllCategories($year = null)
    {
        if (!$year) {
            $year = date('Y');
        }

        $categories = LetterCategory::where('is_active', 1)->get();
        $estimates = [];

        foreach ($categories as $category) {
            $estimates[$category->id] = self::getEstimatedNextNumber($category->id, $year);
        }

        return $estimates;
    }

    /**
     * Get last few numbers for a category
     */
    public static function getLastNumbersForCategory($categoryId, $limit = 5, $year = null)
    {
        try {
            $category = LetterCategory::find($categoryId);
            if (!$category) {
                return collect();
            }

            $query = static::where('letter_category_id', $categoryId)
                ->where('status', '!=', 'cancelled')
                ->orderBy('sequence_number', 'desc');

            if (isset($category->numbering_behavior) && $category->numbering_behavior === 'annual_reset' && $year) {
                $query->whereYear('letter_date', $year);
            }

            return $query->limit($limit)->get();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error getting last numbers for category: ' . $e->getMessage());
            return collect();
        }
    }

    /**
     * Get letter count for a category
     */
    public static function getLetterCountForCategory($categoryId, $year = null)
    {
        try {
            $category = LetterCategory::find($categoryId);
            if (!$category) {
                return 0;
            }

            $query = static::where('letter_category_id', $categoryId)
                ->where('status', '!=', 'cancelled');

            if (isset($category->numbering_behavior) && $category->numbering_behavior === 'annual_reset' && $year) {
                $query->whereYear('letter_date', $year);
            }

            return $query->count();
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error getting letter count for category: ' . $e->getMessage());
            return 0;
        }
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            // Hanya generate jika letter_number benar-benar kosong
            if (empty($model->letter_number) || $model->letter_number === null) {
                try {
                    $model->generateLetterNumberReliable();
                } catch (\Exception $e) {
                    // Log error dan throw kembali untuk handling di controller
                    \Illuminate\Support\Facades\Log::error('Failed to generate letter number: ' . $e->getMessage());
                    throw $e;
                }
            }

            // Set default values hanya jika belum diset
            if (empty($model->reserved_by)) {
                $model->reserved_by = auth()->id() ?? 1;
            }
            if (empty($model->status)) {
                $model->status = 'reserved';
            }
        });

        static::updating(function ($model) {
            // Pastikan letter_number tidak di-override saat update
            if ($model->isDirty('letter_number') && !empty($model->getOriginal('letter_number'))) {
                // Jika letter_number berubah dan sebelumnya sudah ada, log perubahan
                \Illuminate\Support\Facades\Log::info('Letter number updated', [
                    'id' => $model->id,
                    'old_letter_number' => $model->getOriginal('letter_number'),
                    'new_letter_number' => $model->letter_number,
                    'user_id' => auth()->id()
                ]);
            }
        });
    }
}
