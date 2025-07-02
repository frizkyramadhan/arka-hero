<?php

namespace App\Traits;

use App\Models\LetterNumber;
use Illuminate\Support\Facades\Log;

trait HasLetterNumber
{
    /**
     * Relationship ke Letter Number
     */
    public function letterNumber()
    {
        return $this->belongsTo(LetterNumber::class, 'letter_number_id');
    }

    /**
     * Assign letter number ke dokumen
     *
     * @param int $letterNumberId
     * @return bool
     */
    public function assignLetterNumber($letterNumberId)
    {
        try {
            $letterNumber = LetterNumber::find($letterNumberId);
            if (!$letterNumber) {
                return false;
            }

            if ($letterNumber->status !== 'reserved') {
                return false;
            }

            // Update dokumen dengan letter number
            $this->letter_number_id = $letterNumberId;
            $this->letter_number = $letterNumber->letter_number;
            $this->save();

            // Mark letter number as used
            $letterNumber->markAsUsed(
                $this->getDocumentType(),
                $this->id,
                auth()->id()
            );

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to assign letter number: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Release letter number (cancel assignment)
     *
     * @return bool
     */
    public function releaseLetterNumber()
    {
        try {
            if ($this->letter_number_id) {
                $letterNumber = LetterNumber::find($this->letter_number_id);
                if ($letterNumber && $letterNumber->status === 'used') {
                    // Reset letter number to reserved
                    $letterNumber->update([
                        'status' => 'reserved',
                        'related_document_type' => null,
                        'related_document_id' => null,
                        'used_at' => null,
                        'used_by' => null,
                    ]);
                }

                // Clear from document
                $this->letter_number_id = null;
                $this->letter_number = null;
                $this->save();
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to release letter number: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get document type untuk tracking di letter number system
     * Must be implemented by child classes
     *
     * @return string
     */
    abstract protected function getDocumentType(): string;

    /**
     * Check apakah dokumen sudah punya letter number
     *
     * @return bool
     */
    public function hasLetterNumber(): bool
    {
        return !empty($this->letter_number_id) && !empty($this->letter_number);
    }

    /**
     * Get formatted letter number dengan informasi tambahan
     *
     * @return string
     */
    public function getFormattedLetterNumber(): string
    {
        if (!$this->hasLetterNumber()) {
            return 'No Letter Number';
        }

        $status = $this->letterNumber ? $this->letterNumber->status : 'unknown';
        return $this->letter_number . ' (' . ucfirst($status) . ')';
    }

    /**
     * Boot method untuk auto-handling letter number
     */
    protected static function bootHasLetterNumber()
    {
        // When deleting document, release letter number
        static::deleting(function ($model) {
            if ($model->hasLetterNumber()) {
                $model->releaseLetterNumber();
            }
        });
    }

    /**
     * Scope untuk dokumen yang punya letter number
     */
    public function scopeWithLetterNumber($query)
    {
        return $query->whereNotNull('letter_number_id')
            ->whereNotNull('letter_number');
    }

    /**
     * Scope untuk dokumen tanpa letter number
     */
    public function scopeWithoutLetterNumber($query)
    {
        return $query->whereNull('letter_number_id')
            ->orWhereNull('letter_number');
    }
}
