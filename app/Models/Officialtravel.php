<?php

namespace App\Models;

use App\Models\User;
use App\Models\Project;
use App\Models\Accommodation;
use App\Models\Administration;
use App\Models\Transportation;
use App\Traits\HasLetterNumber;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Officialtravel extends Model
{
    use HasFactory;
    use HasUuids;
    use HasLetterNumber;


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



    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
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
