<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LetterSubject extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function category()
    {
        return $this->belongsTo(LetterCategory::class, 'letter_category_id');
    }

    public function letterNumbers()
    {
        return $this->hasMany(LetterNumber::class, 'subject_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('letter_category_id', $categoryId);
    }

    public function scopeWithDocumentModel($query)
    {
        return $query->whereNotNull('document_model');
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('subject_name', 'asc');
    }

    // Document Integration Methods
    public function hasDocumentIntegration()
    {
        return !empty($this->document_model);
    }

    public function getDocumentModelClass()
    {
        if (!$this->hasDocumentIntegration()) {
            return null;
        }

        $modelClass = 'App\\Models\\' . ucfirst($this->document_model);

        if (class_exists($modelClass)) {
            return $modelClass;
        }

        return null;
    }

    // Validation Rules
    public static function validationRules($id = null)
    {
        return [
            'subject_name' => 'required|string|max:255',
            'letter_category_id' => 'required|exists:letter_categories,id',
            'document_model' => [
                'nullable',
                'string',
                'max:100',
                'unique:letter_subjects,document_model' . ($id ? ',' . $id : ''),
            ],
            'is_active' => 'required|boolean',
            'user_id' => 'required|exists:users,id',
        ];
    }

    public static function validationMessages()
    {
        return [
            'subject_name.required' => 'Nama subject harus diisi',
            'subject_name.max' => 'Nama subject maksimal 255 karakter',
            'letter_category_id.required' => 'Category harus diisi',
            'letter_category_id.exists' => 'Category tidak valid',
            'document_model.unique' => 'Document model sudah digunakan oleh subject lain. Satu document model hanya boleh memiliki satu subject.',
            'document_model.max' => 'Document model maksimal 100 karakter',
            'is_active.required' => 'Status aktif harus diisi',
            'is_active.boolean' => 'Status aktif harus berupa true/false',
            'user_id.required' => 'User ID harus diisi',
            'user_id.exists' => 'User tidak ditemukan',
        ];
    }
}
