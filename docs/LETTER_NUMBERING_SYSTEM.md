# Analisis Komprehensif Aplikasi HCSSIS & Desain Fitur Administrasi Penomoran Surat

## 1. ANALISIS STRUKTUR APLIKASI EXISTING

### 1.1 Framework & Arsitektur

-   **Framework**: Laravel (berdasarkan struktur folder dan routing)
-   **Pattern**: MVC (Model-View-Controller)
-   **UI Framework**: AdminLTE dengan Bootstrap 4
-   **Database**: MySQL dengan Eloquent ORM
-   **Authentication**: Laravel built-in authentication
-   **Authorization**: Spatie Permission (roles & permissions)

### 1.2 Struktur Database & Migration Pattern

Aplikasi menggunakan pola migration yang konsisten:

```php
// Contoh dari administrations migration
Schema::create('administrations', function (Blueprint $table) {
    $table->id();
    $table->foreignUuid('employee_id')->references('id')->on('employees');
    $table->foreignId('project_id')->constrained('projects');
    $table->foreignId('position_id')->constrained('positions');
    $table->string('nik');
    $table->boolean('is_active')->default(1);
    $table->foreignId('user_id')->constrained('users');
    $table->timestamps();
});
```

**Pattern yang Konsisten:**

-   Primary key: `id` (auto increment)
-   Foreign keys menggunakan `foreignId()` atau `foreignUuid()`
-   Created/Updated timestamps
-   User tracking dengan `user_id`
-   Soft deletes tidak umum digunakan

### 1.3 Model Pattern

Models menggunakan traits dan relationships yang konsisten:

```php
class Employee extends Model
{
    use HasFactory, Uuids;
    protected $guarded = [];

    // Relationships menggunakan naming convention Laravel
    public function religion() {
        return $this->belongsTo(Religion::class);
    }
}
```

### 1.4 Controller Pattern

Controllers menggunakan pola yang konsisten:

-   DataTables server-side processing
-   Validation menggunakan `$request->validate()`
-   Response menggunakan redirect dengan session flash messages
-   CRUD operations standard

### 1.5 View Pattern & UI Components

**Layout Structure:**

```
layouts/
├── main.blade.php (main layout)
└── partials/
    ├── header.blade.php
    ├── navbar.blade.php
    ├── sidebar.blade.php
    ├── footer.blade.php
    └── scripts.blade.php
```

**UI Components Used:**

-   AdminLTE theme
-   DataTables untuk tabel data
-   Select2 untuk dropdown
-   SweetAlert2 untuk alerts/confirmations
-   Bootstrap modal untuk forms
-   DateRangePicker untuk filter tanggal

### 1.6 Alert System (SweetAlert Pattern)

Aplikasi menggunakan SweetAlert2 secara konsisten:

```javascript
// Pattern dari scripts.blade.php
@if (session('toast_success'))
    Swal.fire({
        icon: 'success',
        title: 'Success',
        text: '{{ session('toast_success') }}',
        confirmButtonColor: '#3085d6',
        confirmButtonText: 'OK'
    });
@endif
```

## 2. DESAIN FITUR ADMINISTRASI PENOMORAN SURAT

### 2.1 Alur Proses Integrasi Sistem

#### 2.1.1 Business Process Flow

Fitur administrasi penomoran surat akan terintegrasi dengan sistem surat yang ada dan yang akan dikembangkan dengan alur proses sebagai berikut:

**Alur 1: Request Nomor → Buat Surat**

1. Tim HCS masuk ke menu **Administrasi Penomoran Surat**
2. Pilih kategori surat yang akan dibuat (A, B, PKWT, PAR, etc.)
3. Isi detail yang diperlukan sesuai kategori
4. System generate nomor surat otomatis
5. Status: **Reserved** (nomor sudah di-reserve tapi surat belum dibuat)
6. Dari halaman detail nomor surat, klik **"Buat Surat"**
7. Redirect ke sistem surat terkait (contoh: LOT) dengan nomor sudah ter-isi
8. Selesaikan pembuatan surat
9. Status berubah ke **Used** (nomor sudah digunakan untuk surat)

**Alur 2: Buat Surat → Request Nomor**

1. Tim HCS masuk ke sistem surat (contoh: LOT - Letter of Official Travel)
2. Mulai create surat baru
3. Pada field nomor surat, ada tombol **"Request Nomor"**
4. Modal popup muncul untuk memilih kategori dan isi detail
5. System generate nomor surat dan langsung mengisi field di form surat
6. Lanjutkan proses pembuatan surat
7. Saat save surat, status nomor otomatis jadi **Used**

#### 2.1.2 Integration Points

-   **LOT (Letter of Official Travel)** - sudah ada dalam aplikasi
-   **Sistem surat lainnya** - akan dikembangkan dengan pattern yang sama
-   **Cross-reference** antara tabel letter_numbers dengan tabel surat terkait
-   **Status tracking** untuk nomor yang sudah di-reserve vs yang sudah digunakan

### 2.2 Database Design

#### 2.2.1 Update Tabel letter_numbers dengan Integration Fields

```sql
CREATE TABLE letter_numbers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    letter_number VARCHAR(50) NOT NULL UNIQUE,
    letter_category_id BIGINT UNSIGNED NOT NULL,
    sequence_number INT NOT NULL,
    year YEAR NOT NULL,
    subject_id BIGINT UNSIGNED,
    custom_subject VARCHAR(200),
    letter_date DATE NOT NULL,
    destination VARCHAR(200),
    remarks TEXT,

    -- Integration fields
    status ENUM('reserved', 'used', 'cancelled') DEFAULT 'reserved',
    related_document_type VARCHAR(50) NULL, -- 'officialtravel', 'future_letter_types'
    related_document_id BIGINT UNSIGNED NULL, -- FK ke tabel surat terkait
    used_at TIMESTAMP NULL, -- kapan nomor digunakan untuk buat surat
    reserved_by BIGINT UNSIGNED, -- user yang reserve nomor
    used_by BIGINT UNSIGNED NULL, -- user yang gunakan nomor untuk buat surat

    -- Fields khusus untuk setiap kategori (existing)
    administration_id BIGINT UNSIGNED NULL, -- Reference ke administrations table
    project_id BIGINT UNSIGNED NULL, -- Tetap ada untuk override jika perlu
    duration VARCHAR(50) NULL,
    start_date DATE NULL,
    end_date DATE NULL,
    classification ENUM('Umum', 'Lembaga Pendidikan', 'Pemerintah') NULL,
    pkwt_type ENUM('PKWT', 'PKWTT') NULL,
    par_type ENUM('new hire', 'promosi', 'mutasi', 'demosi') NULL,
    termination_reason ENUM('mengundurkan diri', 'termination', 'end of contract', 'end of project', 'pensiun', 'meninggal dunia') NULL,
    skpk_reason ENUM('PKWT Berakhir', 'Surat Pengalaman Kerja Hilang') NULL,
    ticket_classification ENUM('Pesawat', 'Kereta Api', 'Bus') NULL,

    is_active BOOLEAN DEFAULT 1,
    user_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (reserved_by) REFERENCES users(id),
    FOREIGN KEY (used_by) REFERENCES users(id),
    FOREIGN KEY (letter_category_id) REFERENCES letter_categories(id),
    FOREIGN KEY (subject_id) REFERENCES letter_subjects(id),
    FOREIGN KEY (administration_id) REFERENCES administrations(id),
    FOREIGN KEY (project_id) REFERENCES projects(id),

    INDEX idx_category_year (letter_category_id, year),
    INDEX idx_letter_date (letter_date),
    INDEX idx_administration (administration_id),
    INDEX idx_status (status),
    INDEX idx_related_document (related_document_type, related_document_id)
);
```

#### 2.2.2 Update Tabel officialtravels untuk Integration

```sql
-- Tambahkan kolom letter_number_id untuk integrasi
ALTER TABLE officialtravels
ADD COLUMN letter_number_id BIGINT UNSIGNED NULL AFTER id,
ADD COLUMN letter_number VARCHAR(50) NULL AFTER letter_number_id,
ADD FOREIGN KEY (letter_number_id) REFERENCES letter_numbers(id);
```

#### 2.2.3 Tabel Master: letter_categories

```sql
CREATE TABLE letter_categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_code VARCHAR(10) NOT NULL UNIQUE,
    category_name VARCHAR(100) NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT 1,
    user_id BIGINT UNSIGNED,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

#### 2.2.5 Tabel Master: letter_subjects

```sql
CREATE TABLE letter_subjects (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    subject_name VARCHAR(200) NOT NULL,
    letter_category_id BIGINT UNSIGNED NOT NULL,
    is_active BOOLEAN DEFAULT 1,
    user_id BIGINT UNSIGNED,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (letter_category_id) REFERENCES letter_categories(id),
    INDEX idx_letter_category (letter_category_id)
);
```

### 2.3 Model Structure dengan Integration Support

#### 2.3.1 LetterNumber Model (Updated dengan Integration)

```php
<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LetterNumber extends Model
{
    use HasFactory;

    protected $guarded = [];

    protected $dates = ['letter_date', 'start_date', 'end_date', 'used_at'];

    protected $casts = [
        'used_at' => 'datetime',
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
        return $this->administration && $this->administration->employee ? $this->administration->employee->fullname : null;
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

    // Integration relationships - polymorphic untuk future scalability
    public function officialTravel()
    {
        return $this->hasOne(Officialtravel::class, 'letter_number_id');
    }

    // Dynamic relationship berdasarkan related_document_type
    public function relatedDocument()
    {
        switch($this->related_document_type) {
            case 'officialtravel':
                return $this->officialTravel();
            // case 'future_document_type':
            //     return $this->futureDocument();
            default:
                return null;
        }
    }

    // Generate letter number
    public function generateLetterNumber()
    {
        // Pastikan relasi category sudah di-load untuk mendapatkan category_code
        if (!$this->relationLoaded('category')) {
            $this->load('category');
        }

        $year = date('Y', strtotime($this->letter_date));
        $sequence = static::where('letter_category_id', $this->letter_category_id)
                         ->where('year', $year)
                         ->max('sequence_number') + 1;

        $this->sequence_number = $sequence;
        $this->year = $year;
        $this->letter_number = $this->category->category_code . sprintf('%04d', $sequence);
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

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            if (empty($model->letter_number)) {
                $model->generateLetterNumber();
            }
            if (auth()->check()) {
                if (empty($model->user_id)) {
                    $model->user_id = auth()->id();
                }
                if (empty($model->reserved_by)) {
                    $model->reserved_by = auth()->id();
                }
            }
            if (empty($model->status)) {
                $model->status = 'reserved';
            }
        });
    }
}
```

#### 2.3.2 Officialtravel Model (Updated untuk Integration)

```php
<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Officialtravel extends Model
{
    use HasFactory, Uuids;

    protected $guarded = [];

    // Integration dengan Letter Number
    public function letterNumber()
    {
        return $this->belongsTo(LetterNumber::class, 'letter_number_id');
    }

    // Existing relationships...
    public function traveler()
    {
        return $this->belongsTo(Administration::class, 'traveler_id', 'id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'official_travel_origin');
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
                $category = LetterCategory::where('category_code', 'B')->first();
                if ($category) {
                    $letterNumber = LetterNumber::create([
                        'letter_category_id' => $category->id,
                        'letter_date' => $model->created_at->toDateString(),
                        'custom_subject' => 'Surat Perjalanan Dinas',
                        'user_id' => auth()->id() ?? $model->created_by,
                    ]);

                    $model->assignLetterNumber($letterNumber->id);
                }
            }
        });
    }
}
```

#### 2.3.3 LetterCategory Model

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class LetterCategory extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function subjects()
    {
        return $this->hasMany(LetterSubject::class, 'letter_category_id');
    }

    public function letterNumbers()
    {
        return $this->hasMany(LetterNumber::class, 'letter_category_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

### 2.4 Controller Integration Logic

#### 2.4.1 LetterNumberController (Updated dengan Integration)

```php
<?php

namespace App\Http\Controllers;

use App\Models\LetterNumber;
use App\Models\LetterCategory;
use App\Models\LetterSubject;
use App\Models\Employee;
use App\Models\Project;
use Illuminate\Http\Request;

class LetterNumberController extends Controller
{
    public function index()
    {
        $title = 'Administrasi Penomoran Surat';
        $subtitle = 'Daftar Nomor Surat';
        $categories = LetterCategory::where('is_active', 1)->get();

        return view('letter-numbers.index', compact('title', 'subtitle', 'categories'));
    }

    public function getLetterNumbers(Request $request)
    {
        $letterNumbers = LetterNumber::with(['category', 'subject', 'employee', 'project', 'reservedBy', 'usedBy'])
            ->when($request->letter_category_id, function($query, $categoryId) {
                return $query->where('letter_category_id', $categoryId);
            })
            ->when($request->status, function($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->date_from, function($query, $date) {
                return $query->where('letter_date', '>=', $date);
            })
            ->when($request->date_to, function($query, $date) {
                return $query->where('letter_date', '<=', $date);
            })
            ->orderBy('letter_date', 'desc')
            ->orderBy('sequence_number', 'desc');

        return datatables()->of($letterNumbers)
            ->addIndexColumn()
            ->addColumn('category_name', function ($row) {
                return $row->category->category_name ?? '-';
            })
            ->addColumn('subject_display', function ($row) {
                return $row->subject->subject_name ?? $row->custom_subject ?? '-';
            })
            ->addColumn('status_badge', function ($row) {
                $badges = [
                    'reserved' => '<span class="badge badge-warning">Reserved</span>',
                    'used' => '<span class="badge badge-success">Used</span>',
                    'cancelled' => '<span class="badge badge-danger">Cancelled</span>',
                ];
                return $badges[$row->status] ?? '<span class="badge badge-secondary">Unknown</span>';
            })
            ->addColumn('integration_info', function ($row) {
                if ($row->related_document_type && $row->related_document_id) {
                    $link = $this->getDocumentLink($row->related_document_type, $row->related_document_id);
                    return "<a href='{$link}' class='btn btn-sm btn-info'>View {$row->related_document_type}</a>";
                }
                return '-';
            })
            ->addColumn('action', function ($row) {
                return view('letter-numbers.action', compact('row'));
            })
            ->rawColumns(['status_badge', 'integration_info', 'action'])
            ->toJson();
    }

    public function store(Request $request)
    {
        $rules = [
            'letter_category_id' => 'required|exists:letter_categories,id',
            'letter_date' => 'required|date',
            'destination' => 'nullable|string|max:200',
            'remarks' => 'nullable|string',
        ];

        // Dynamic validation based on category...
        $category = LetterCategory::find($request->letter_category_id);
        if ($category) {
            // Lakukan validasi tambahan berdasarkan category->category_code
        }

        $request->validate($rules);

        $letterNumber = new LetterNumber();
        $letterNumber->fill($request->all());
        $letterNumber->user_id = auth()->id();

        // Handle employee data for relevant categories
        if ($category && in_array($category->category_code, ['PKWT', 'PAR', 'CRTE', 'SKPK']) && $request->employee_id) {
            $employee = Employee::with('administrations')->find($request->employee_id);
            $letterNumber->employee_name = $employee->fullname;
            $letterNumber->nik = $employee->administrations->first()->nik ?? null;
        }

        $letterNumber->save();

        return redirect()->route('letter-numbers.index')
                        ->with('toast_success', 'Nomor surat berhasil dibuat: ' . $letterNumber->letter_number);
    }

    public function show($id)
    {
        $letterNumber = LetterNumber::with(['category', 'subject', 'employee', 'project', 'reservedBy', 'usedBy'])
                                   ->findOrFail($id);

        $title = 'Detail Nomor Surat';
        $relatedDocument = $letterNumber->relatedDocument();

        return view('letter-numbers.show', compact('title', 'letterNumber', 'relatedDocument'));
    }

    // API Endpoint untuk request nomor dari sistem surat lain
    public function requestNumber(Request $request)
    {
        $request->validate([
            'letter_category_id' => 'required|exists:letter_categories,id',
            'letter_date' => 'required|date',
            'custom_subject' => 'nullable|string|max:200',
            'employee_id' => 'nullable|exists:employees,id',
            'project_id' => 'nullable|exists:projects,id',
        ]);

        try {
            $letterNumber = new LetterNumber();
            $letterNumber->letter_category_id = $request->letter_category_id;
            $letterNumber->letter_date = $request->letter_date;
            $letterNumber->custom_subject = $request->custom_subject;
            $letterNumber->employee_id = $request->employee_id;
            $letterNumber->project_id = $request->project_id;
            $letterNumber->user_id = auth()->id();
            $letterNumber->save();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $letterNumber->id,
                    'letter_number' => $letterNumber->letter_number,
                    'letter_category_id' => $letterNumber->letter_category_id,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat nomor surat: ' . $e->getMessage()
            ], 500);
        }
    }

    // API untuk mark nomor sebagai used
    public function markAsUsed(Request $request, $id)
    {
        $request->validate([
            'document_type' => 'required|string',
            'document_id' => 'required|integer',
        ]);

        $letterNumber = LetterNumber::findOrFail($id);

        if ($letterNumber->status !== 'reserved') {
            return response()->json([
                'success' => false,
                'message' => 'Nomor surat tidak dalam status reserved'
            ], 400);
        }

        try {
            $letterNumber->markAsUsed($request->document_type, $request->document_id);

            return response()->json([
                'success' => true,
                'message' => 'Nomor surat berhasil digunakan'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengupdate status nomor surat: ' . $e->getMessage()
            ], 500);
        }
    }

    // Helper untuk generate link ke dokumen terkait
    private function getDocumentLink($documentType, $documentId)
    {
        switch($documentType) {
            case 'officialtravel':
                return route('officialtravels.show', $documentId);
            // case 'future_document_type':
            //     return route('future_documents.show', $documentId);
            default:
                return '#';
        }
    }

    // API untuk get available numbers (untuk dropdown di form surat)
    public function getAvailableNumbers(Request $request)
    {
        $categoryId = $request->get('category_id');

        $numbers = LetterNumber::with(['category', 'subject'])
                              ->where('letter_category_id', $categoryId)
                              ->where('status', 'reserved')
                              ->orderBy('sequence_number', 'desc')
                              ->limit(50)
                              ->get()
                              ->map(function($item) {
                                  return [
                                      'id' => $item->id,
                                      'letter_number' => $item->letter_number,
                                      'subject' => $item->subject->subject_name ?? $item->custom_subject,
                                      'letter_date' => $item->letter_date->format('d/m/Y'),
                                  ];
                              });

        return response()->json($numbers);
    }
}
```

#### 2.4.2 OfficialtravelController Integration Update

```php
<?php
// Update pada OfficialtravelController untuk support integration

class OfficialtravelController extends Controller
{
    public function create()
    {
        $title = 'Tambah Perjalanan Dinas';
        // ... existing code ...

        // Tambahan untuk integration
        $categoryB = LetterCategory::where('category_code', 'B')->first();
        $availableLetterNumbers = collect();
        if ($categoryB) {
            $availableLetterNumbers = LetterNumber::where('letter_category_id', $categoryB->id)
                                                ->where('status', 'reserved')
                                                ->orderBy('sequence_number', 'desc')
                                                ->limit(20)
                                                ->get();
        }

        return view('officialtravels.create', compact(
            'title', /* ... existing variables ... */
            'availableLetterNumbers'
        ));
    }

    public function store(Request $request)
    {
        // ... existing validation ...

        $officialtravel = new Officialtravel();
        $officialtravel->fill($request->all());

        // Handle letter number integration
        if ($request->letter_number_id) {
            // Menggunakan nomor yang sudah ada
            $letterNumber = LetterNumber::find($request->letter_number_id);
            if ($letterNumber && $letterNumber->status === 'reserved') {
                $officialtravel->letter_number_id = $letterNumber->id;
                $officialtravel->letter_number = $letterNumber->letter_number;
            }
        } elseif ($request->request_new_number) {
            // Request nomor baru via API
            $categoryB = LetterCategory::where('category_code', 'B')->first();
            if ($categoryB) {
                $letterNumber = LetterNumber::create([
                    'letter_category_id' => $categoryB->id,
                    'letter_date' => $request->departure_date ?? now()->toDateString(),
                    'custom_subject' => 'Surat Perjalanan Dinas',
                    'project_id' => $request->official_travel_origin,
                    'user_id' => auth()->id(),
                ]);

                $officialtravel->letter_number_id = $letterNumber->id;
                $officialtravel->letter_number = $letterNumber->letter_number;
            }
        }

        $officialtravel->save();

        // Mark letter number as used jika ada
        if ($officialtravel->letter_number_id) {
            $letterNumber = LetterNumber::find($officialtravel->letter_number_id);
            $letterNumber->markAsUsed('officialtravel', $officialtravel->id);
        }

        return redirect()->route('officialtravels.index')
                        ->with('toast_success', 'Perjalanan dinas berhasil dibuat dengan nomor: ' . $officialtravel->letter_number);
    }
}
```

### 2.5 Migration Files

#### 2.3.1 Create Letter Categories Migration

```php
<?php
// database/migrations/YYYY_MM_DD_create_letter_categories_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('letter_categories', function (Blueprint $table) {
            $table->id();
            $table->string('category_code', 10)->unique();
            $table->string('category_name', 100);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(1);
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('letter_categories');
    }
};
```

#### 2.3.2 Create Letter Numbers Migration

```php
<?php
// database/migrations/YYYY_MM_DD_create_letter_numbers_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('letter_numbers', function (Blueprint $table) {
            $table->id();
            $table->string('letter_number', 50)->unique();
            $table->foreignId('letter_category_id')->constrained('letter_categories');
            $table->integer('sequence_number');
            $table->year('year');
            $table->foreignId('subject_id')->nullable()->constrained('letter_subjects');
            $table->string('custom_subject', 200)->nullable();
            $table->date('letter_date');
            $table->string('destination', 200)->nullable();
            $table->text('remarks')->nullable();

            // Fields khusus
            $table->foreignId('administration_id')->nullable()->constrained('administrations');
            $table->foreignId('project_id')->nullable()->constrained('projects');
            $table->string('duration', 50)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->enum('classification', ['Umum', 'Lembaga Pendidikan', 'Pemerintah'])->nullable();
            $table->enum('pkwt_type', ['PKWT', 'PKWTT'])->nullable();
            $table->enum('par_type', ['new hire', 'promosi', 'mutasi', 'demosi'])->nullable();
            $table->enum('termination_reason', ['mengundurkan diri', 'termination', 'end of contract', 'end of project', 'pensiun', 'meninggal dunia'])->nullable();
            $table->enum('skpk_reason', ['PKWT Berakhir', 'Surat Pengalaman Kerja Hilang'])->nullable();
            $table->enum('ticket_classification', ['Pesawat', 'Kereta Api', 'Bus'])->nullable();

            $table->boolean('is_active')->default(1);
            $table->foreignId('user_id')->constrained('users');
            $table->timestamps();

            $table->index(['letter_category_id', 'year']);
            $table->index('letter_date');
            $table->index('administration_id');
        });
    }

    public function down()
    {
        Schema::dropIfExists('letter_numbers');
    }
};
```

### 2.4 Controller Structure

#### 2.4.1 LetterNumberController

```php
<?php

namespace App\Http\Controllers;

use App\Models\LetterNumber;
use App\Models\LetterCategory;
use App\Models\LetterSubject;
use App\Models\Employee;
use App\Models\Project;
use Illuminate\Http\Request;

class LetterNumberController extends Controller
{
    public function index()
    {
        $title = 'Administrasi Penomoran Surat';
        $subtitle = 'Daftar Nomor Surat';
        $categories = LetterCategory::where('is_active', 1)->get();

        return view('letter-numbers.index', compact('title', 'subtitle', 'categories'));
    }

    public function getLetterNumbers(Request $request)
    {
        $letterNumbers = LetterNumber::with(['category', 'subject', 'employee', 'project', 'user'])
            ->when($request->letter_category_id, function($query, $categoryId) {
                return $query->where('letter_category_id', $categoryId);
            })
            ->when($request->date_from, function($query, $date) {
                return $query->where('letter_date', '>=', $date);
            })
            ->when($request->date_to, function($query, $date) {
                return $query->where('letter_date', '<=', $date);
            })
            ->orderBy('letter_date', 'desc')
            ->orderBy('sequence_number', 'desc');

        return datatables()->of($letterNumbers)
            ->addIndexColumn()
            ->addColumn('category_name', function ($row) {
                return $row->category->category_name ?? '-';
            })
            ->addColumn('subject_display', function ($row) {
                return $row->subject->subject_name ?? $row->custom_subject ?? '-';
            })
            ->addColumn('employee_display', function ($row) {
                return $row->employee_name ?? $row->employee->fullname ?? '-';
            })
            ->addColumn('project_display', function ($row) {
                return $row->project->project_name ?? '-';
            })
            ->addColumn('action', function ($row) {
                return view('letter-numbers.action', compact('row'));
            })
            ->rawColumns(['action'])
            ->toJson();
    }

    public function create($categoryId = null)
    {
        $title = 'Buat Nomor Surat';
        $categories = LetterCategory::where('is_active', 1)->get();
        $employees = Employee::orderBy('fullname')->get();
        $projects = Project::orderBy('project_name')->get();

        $selectedCategory = null;
        $subjects = collect();

        if ($categoryId) {
            $selectedCategory = LetterCategory::find($categoryId);
            if($selectedCategory) {
                $subjects = LetterSubject::where('letter_category_id', $categoryId)
                                       ->where('is_active', 1)
                                       ->get();
            }
        }

        return view('letter-numbers.create', compact('title', 'categories', 'subjects', 'employees', 'projects', 'selectedCategory'));
    }

    public function store(Request $request)
    {
        $rules = [
            'letter_category_id' => 'required|exists:letter_categories,id',
            'letter_date' => 'required|date',
            'destination' => 'nullable|string|max:200',
            'remarks' => 'nullable|string',
        ];

        // Dynamic validation based on category
        $category = LetterCategory::find($request->letter_category_id);
        if ($category) {
            switch ($category->category_code) {
                case 'PKWT':
                    // PKWT no longer requires administration_id (NIK) - allowing import without NIK
                    $rules['duration'] = 'required|string';
                    $rules['start_date'] = 'required|date';
                    $rules['end_date'] = 'required|date|after:start_date';
                    $rules['pkwt_type'] = 'required|in:PKWT,PKWTT';
                    break;

                case 'PAR':
                    // PAR no longer requires administration_id (NIK) - allowing import without NIK
                    $rules['par_type'] = 'required|in:new hire,promosi,mutasi,demosi';
                    break;

                case 'CRTE':
                    // CRTE no longer requires administration_id (NIK) - allowing import without NIK
                    break;

                case 'SKPK':
                    // SKPK no longer requires administration_id (NIK)
                    break;
            }
        }

        $request->validate($rules);

        $letterNumber = new LetterNumber();
        $letterNumber->fill($request->all());
        $letterNumber->user_id = auth()->id();

        // Handle employee data for relevant categories
        if ($category && in_array($category->category_code, ['PKWT', 'PAR', 'CRTE', 'SKPK']) && $request->employee_id) {
            $employee = Employee::with('administrations')->find($request->employee_id);
            $letterNumber->employee_name = $employee->fullname;
            $letterNumber->nik = $employee->administrations->first()->nik ?? null;
        }

        $letterNumber->save();

        return redirect()->route('letter-numbers.index')
                        ->with('toast_success', 'Nomor surat berhasil dibuat: ' . $letterNumber->letter_number);
    }
}
```

### 2.5 View Structure

#### 2.5.1 Index View (letter-numbers/index.blade.php)

```blade
@extends('layouts.main')

@section('content')
<div class="content-header">
    <div class="container-fluid">
        <div class="row mb-2">
            <div class="col-sm-6">
                <h1 class="m-0">{{ $title }}</h1>
            </div>
            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-right">
                    <li class="breadcrumb-item"><a href="{{ url('/') }}">Home</a></li>
                    <li class="breadcrumb-item active">Administrasi Surat</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<section class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header">
                        <h3 class="card-title">{{ $subtitle }}</h3>
                        <div class="card-tools">
                            <div class="btn-group">
                                <button type="button" class="btn btn-warning dropdown-toggle" data-toggle="dropdown">
                                    <i class="fas fa-plus"></i> Buat Surat
                                </button>
                                <div class="dropdown-menu">
                                    <a class="dropdown-item" href="{{ route('letter-numbers.create', ['categoryId' => 1]) }}">
                                        <i class="fas fa-file-alt"></i> Surat Eksternal (A)
                                    </a>
                                    <a class="dropdown-item" href="{{ route('letter-numbers.create', ['categoryId' => 2]) }}">
                                        <i class="fas fa-file-alt"></i> Surat Internal (B)
                                    </a>
                                    <a class="dropdown-item" href="{{ route('letter-numbers.create', ['categoryId' => 3]) }}">
                                        <i class="fas fa-file-contract"></i> PKWT
                                    </a>
                                    <a class="dropdown-item" href="{{ route('letter-numbers.create', ['categoryId' => 4]) }}">
                                        <i class="fas fa-user-tie"></i> PAR
                                    </a>
                                    <a class="dropdown-item" href="{{ route('letter-numbers.create', ['categoryId' => 5]) }}">
                                        <i class="fas fa-certificate"></i> Surat Pengalaman Kerja
                                    </a>
                                    <a class="dropdown-item" href="{{ route('letter-numbers.create', ['categoryId' => 6]) }}">
                                        <i class="fas fa-certificate"></i> Surat Keterangan Pengalaman
                                    </a>
                                    <a class="dropdown-item" href="{{ route('letter-numbers.create', ['categoryId' => 7]) }}">
                                        <i class="fas fa-sticky-note"></i> Memo
                                    </a>
                                    <a class="dropdown-item" href="{{ route('letter-numbers.create', ['categoryId' => 8]) }}">
                                        <i class="fas fa-users"></i> FPTK
                                    </a>
                                    <a class="dropdown-item" href="{{ route('letter-numbers.create', ['categoryId' => 9]) }}">
                                        <i class="fas fa-plane"></i> Permintaan Tiket
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Card -->
                    <div class="card-body">
                        <div class="card card-primary collapsed-card">
                            <div class="card-header">
                                <h4 class="card-title">
                                    <a data-card-widget="collapse" href="#">
                                        <i class="fas fa-filter"></i> Filter Data
                                    </a>
                                </h4>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Kategori Surat</label>
                                            <select class="form-control select2bs4" id="filter-category">
                                                <option value="">- Semua Kategori -</option>
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->id }}">
                                                        {{ $category->category_name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Tanggal Dari</label>
                                            <input type="date" class="form-control" id="filter-date-from">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Tanggal Sampai</label>
                                            <input type="date" class="form-control" id="filter-date-to">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>&nbsp;</label>
                                            <button type="button" id="btn-reset-filter" class="btn btn-danger btn-block">
                                                <i class="fas fa-times"></i> Reset Filter
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Data Table -->
                        <div class="table-responsive">
                            <table id="letter-numbers-table" class="table table-bordered table-striped">
                                <thead>
                                    <tr>
                                        <th width="5%">No</th>
                                        <th>Nomor Surat</th>
                                        <th>Kategori</th>
                                        <th>Perihal</th>
                                        <th>Tanggal</th>
                                        <th>Karyawan</th>
                                        <th>Project</th>
                                        <th>Tujuan</th>
                                        <th>Pembuat</th>
                                        <th width="10%">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection

@section('styles')
<link rel="stylesheet" href="{{ asset('assets/plugins/datatables-bs4/css/dataTables.bootstrap4.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/select2/css/select2.min.css') }}">
<link rel="stylesheet" href="{{ asset('assets/plugins/select2-bootstrap4-theme/select2-bootstrap4.min.css') }}">
@endsection

@section('scripts')
<script src="{{ asset('assets/plugins/datatables/jquery.dataTables.min.js') }}"></script>
<script src="{{ asset('assets/plugins/datatables-bs4/js/dataTables.bootstrap4.min.js') }}"></script>
<script src="{{ asset('assets/plugins/select2/js/select2.full.min.js') }}"></script>

<script>
$(function() {
    // Initialize Select2
    $('.select2bs4').select2({
        theme: 'bootstrap4'
    });

    // Initialize DataTable
    var table = $("#letter-numbers-table").DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: "{{ route('letter-numbers.data') }}",
            data: function(d) {
                d.letter_category_id = $('#filter-category').val();
                d.date_from = $('#filter-date-from').val();
                d.date_to = $('#filter-date-to').val();
            }
        },
        columns: [
            {data: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center'},
            {data: 'letter_number', name: 'letter_number'},
            {data: 'category_name', name: 'category_name'},
            {data: 'subject_display', name: 'subject_display'},
            {data: 'letter_date', name: 'letter_date'},
            {data: 'employee_display', name: 'employee_display'},
            {data: 'project_display', name: 'project_display'},
            {data: 'destination', name: 'destination'},
            {data: 'user.name', name: 'user.name'},
            {data: 'action', orderable: false, searchable: false}
        ],
        dom: 'frtpi',
        lengthChange: true,
        lengthMenu: [[10, 25, 50, 100, -1], ['10', '25', '50', '100', 'Semua']]
    });

    // Filter events
    $('#filter-category, #filter-date-from, #filter-date-to').change(function() {
        table.draw();
    });

    $('#btn-reset-filter').click(function() {
        $('#filter-category').val('').trigger('change');
        $('#filter-date-from, #filter-date-to').val('');
        table.draw();
    });
});
</script>
@endsection
```

### 2.6 Routing Structure dengan Integration Support

```php
// routes/web.php
Route::group(['middleware' => ['auth']], function () {
    // Letter Number Management Routes
    Route::prefix('letter-numbers')->name('letter-numbers.')->group(function () {
        Route::get('/', [LetterNumberController::class, 'index'])->name('index');
        Route::get('/data', [LetterNumberController::class, 'getLetterNumbers'])->name('data');
        Route::get('/create/{categoryId?}', [LetterNumberController::class, 'create'])->name('create');
        Route::post('/', [LetterNumberController::class, 'store'])->name('store');
        Route::get('/{id}', [LetterNumberController::class, 'show'])->name('show');
        Route::get('/{id}/edit', [LetterNumberController::class, 'edit'])->name('edit');
        Route::put('/{id}', [LetterNumberController::class, 'update'])->name('update');
        Route::delete('/{id}', [LetterNumberController::class, 'destroy'])->name('destroy');
        Route::get('/{id}/print', [LetterNumberController::class, 'print'])->name('print');

        // Integration API Routes
        Route::post('/request-number', [LetterNumberController::class, 'requestNumber'])->name('request');
        Route::post('/{id}/mark-as-used', [LetterNumberController::class, 'markAsUsed'])->name('mark-used');
        Route::get('/available', [LetterNumberController::class, 'getAvailableNumbers'])->name('available');
        Route::post('/{id}/cancel', [LetterNumberController::class, 'cancel'])->name('cancel');
    });

    // Master data for letter numbering
    Route::resource('letter-categories', LetterCategoryController::class);
    Route::resource('letter-subjects', LetterSubjectController::class);

    // API routes for dynamic dropdowns
    Route::get('/api/letter-subjects/{categoryId}', [LetterSubjectController::class, 'getByCategory']);
});
```

### 2.7 UI Integration Pattern

#### 2.7.1 Integration Component untuk LOT Create Form

```blade
<!-- resources/views/officialtravels/create.blade.php -->
<!-- Tambahan section untuk letter number integration -->
<div class="card card-info">
    <div class="card-header">
        <h4 class="card-title">Nomor Surat</h4>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Pilihan Nomor Surat</label>
                    <div class="custom-control custom-radio">
                        <input class="custom-control-input" type="radio" id="use_existing" name="number_option" value="existing">
                        <label for="use_existing" class="custom-control-label">Gunakan Nomor yang Sudah Ada</label>
                    </div>
                    <div class="custom-control custom-radio">
                        <input class="custom-control-input" type="radio" id="request_new" name="number_option" value="new" checked>
                        <label for="request_new" class="custom-control-label">Request Nomor Baru</label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Option 1: Pilih dari nomor yang sudah ada -->
        <div id="existing_number_section" style="display: none;">
            <div class="row">
                <div class="col-md-8">
                    <div class="form-group">
                        <label>Pilih Nomor Surat</label>
                        <select class="form-control select2bs4" id="letter_number_id" name="letter_number_id">
                            <option value="">- Pilih Nomor Surat -</option>
                            @foreach($availableLetterNumbers as $letterNumber)
                                <option value="{{ $letterNumber->id }}" data-number="{{ $letterNumber->letter_number }}">
                                    {{ $letterNumber->letter_number }} - {{ $letterNumber->custom_subject }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>&nbsp;</label>
                        <button type="button" id="btn-refresh-numbers" class="btn btn-info btn-block">
                            <i class="fas fa-sync"></i> Refresh List
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Option 2: Request nomor baru -->
        <div id="new_number_section">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Kategori Surat</label>
                        <select class="form-control" name="letter_category" id="letter_category">
                            <option value="A">A - Surat Eksternal</option>
                            <option value="B">B - Surat Internal</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>Perihal</label>
                        <input type="text" class="form-control" name="letter_subject"
                               id="letter_subject" value="Surat Perjalanan Dinas" readonly>
                    </div>
                </div>
            </div>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                Nomor surat akan di-generate otomatis ketika form disimpan.
            </div>
        </div>

        <!-- Display generated/selected number -->
        <div id="selected_number_display" style="display: none;">
            <div class="alert alert-success">
                <strong>Nomor Surat Terpilih:</strong> <span id="display_number"></span>
            </div>
        </div>
    </div>
</div>
```

#### 2.7.2 JavaScript Integration Helper

```javascript
// resources/js/letter-number-integration.js
class LetterNumberIntegration {
    constructor() {
        this.initEventHandlers();
    }

    initEventHandlers() {
        // Handle radio button changes
        $('input[name="number_option"]').change((e) => {
            this.toggleNumberOptions($(e.target).val());
        });

        // Handle letter number selection
        $("#letter_number_id").change((e) => {
            this.displaySelectedNumber($(e.target));
        });

        // Handle refresh numbers
        $("#btn-refresh-numbers").click(() => {
            this.refreshAvailableNumbers();
        });
    }

    toggleNumberOptions(option) {
        if (option === "existing") {
            $("#existing_number_section").show();
            $("#new_number_section").hide();
        } else {
            $("#existing_number_section").hide();
            $("#new_number_section").show();
        }
        $("#selected_number_display").hide();
    }

    displaySelectedNumber(selectElement) {
        var selectedOption = selectElement.find("option:selected");
        if (selectedOption.val()) {
            $("#display_number").text(selectedOption.data("number"));
            $("#selected_number_display").show();
        } else {
            $("#selected_number_display").hide();
        }
    }

    refreshAvailableNumbers(categoryId = 2) {
        // Default to category 'B'
        $.get(`/letter-numbers/available?category_id=${categoryId}`, (data) => {
            var select = $("#letter_number_id");
            select
                .empty()
                .append('<option value="">- Pilih Nomor Surat -</option>');

            $.each(data, (index, item) => {
                select.append(
                    $("<option>", {
                        value: item.id,
                        text: `${item.letter_number} - ${item.subject}`,
                        "data-number": item.letter_number,
                    })
                );
            });
        });
    }

    requestNewNumber(formData) {
        return $.ajax({
            url: "/letter-numbers/request-number",
            type: "POST",
            data: formData,
            headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
            },
        });
    }
}

// Initialize when document ready
$(function () {
    if ($("#letter_number_id").length) {
        new LetterNumberIntegration();
    }
});
```

### 2.7 Menu Integration (Sidebar)

Tambahkan di `resources/views/layouts/partials/sidebar.blade.php`:

```blade
{{-- Setelah Official Travels --}}
<li class="nav-item {{ Request::is('letter-numbers*') || Request::is('letter-categories*') || Request::is('letter-subjects*') ? 'menu-open' : '' }}">
    <a href="#" class="nav-link {{ Request::is('letter-numbers*') || Request::is('letter-categories*') || Request::is('letter-subjects*') ? 'active' : '' }}">
        <i class="nav-icon fas fa-file-alt"></i>
        <p>
            Administrasi Surat
            <i class="fas fa-angle-left right"></i>
        </p>
    </a>
    <ul class="nav nav-treeview">
        <li class="nav-item">
            <a href="{{ route('letter-numbers.index') }}" class="nav-link {{ Request::is('letter-numbers*') ? 'active' : '' }}">
                <i class="far fa-circle nav-icon"></i>
                <p>Penomoran Surat</p>
            </a>
        </li>
        @can('master-data.show')
            <li class="nav-item">
                <a href="{{ route('letter-categories.index') }}" class="nav-link {{ Request::is('letter-categories*') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Kategori Surat</p>
                </a>
            </li>
            <li class="nav-item">
                <a href="{{ route('letter-subjects.index') }}" class="nav-link {{ Request::is('letter-subjects*') ? 'active' : '' }}">
                    <i class="far fa-circle nav-icon"></i>
                    <p>Master Perihal</p>
                </a>
            </li>
        @endcan
    </ul>
</li>
```

## 3. IMPLEMENTASI TAHAPAN (UPDATED dengan Integration Support)

### Phase 1: Database & Foundation

1. **Database Setup**
    - Buat migration files untuk `letter_categories`, `letter_subjects`, `letter_numbers`
    - Update migration `officialtravels` untuk menambah integration fields
    - Jalankan migration dan testing database structure
2. **Models & Relationships**

    - Implementasi model `LetterNumber` dengan integration methods
    - Update model `Officialtravel` untuk support integration
    - Implementasi model `LetterCategory` dan `LetterSubject`
    - Testing relationships dan integration methods

3. **Data Seeding**
    - Seed kategori surat default (A, B, PKWT, PAR, dll)
    - Seed perihal default untuk setiap kategori
    - Migrate existing LOT data jika diperlukan

### Phase 2: Core Integration Logic

1. **Letter Number Controller**
    - Implementasi CRUD operations
    - Implementasi API endpoints untuk integration
    - Testing auto-numbering logic
2. **Integration dengan LOT**

    - Update `OfficialtravelController` untuk support integration
    - Testing backward compatibility
    - Migration script untuk existing LOT data

3. **API Development**
    - Request number API
    - Mark as used API
    - Available numbers API
    - Testing API responses dan error handling

### Phase 3: User Interface Integration

1. **Master Category Management UI**

    - Index view dengan list kategori surat
    - Create/edit form untuk kategori baru
    - Validasi kode kategori unik
    - Delete protection untuk kategori yang sudah digunakan

2. **Master Subject Management UI**

    - Index view dengan filter per kategori
    - Create/edit form dengan dynamic category selection
    - Bulk import/export subjects
    - Delete protection untuk subject yang sudah digunakan

3. **Letter Number Management UI**

    - Index view dengan status tracking
    - Create/edit forms dengan dynamic fields
    - Detail view dengan integration info

4. **LOT Integration UI**

    - Update create form untuk letter number selection
    - Radio button untuk existing vs new number
    - AJAX integration untuk seamless experience

5. **JavaScript Components**
    - Letter number integration helper class
    - AJAX handlers untuk API calls
    - Real-time validation dan feedback
    - Dynamic form handling untuk master data

### Phase 4: Integration Testing & Deployment

1. **Comprehensive Testing**
    - Unit testing untuk models dan relationships
    - Integration testing untuk API endpoints
    - UI testing untuk user flows (both directions)
    - Performance testing dengan large datasets
2. **Documentation & Training**

    - Update user manual/documentation
    - Training untuk tim HCS tentang alur baru
    - Testing dengan real scenarios

3. **Deployment & Monitoring**
    - Staging deployment dan testing
    - Production deployment dengan rollback plan
    - Monitoring untuk performance dan error tracking
    - Post-deployment support dan adjustments

### Phase 5: Future Expansion Framework

1. **Standardization**

    - Create template/boilerplate untuk sistem surat baru
    - Standardize integration pattern
    - Documentation untuk developer

2. **Plugin Architecture**
    - Develop framework untuk easy integration surat baru
    - API documentation untuk third-party integration
    - Version control untuk letter number system

## 4. CONSIDERATIONS & BEST PRACTICES

### 4.1 Auto-numbering Logic

-   Sequence per kategori per tahun
-   Handle concurrent requests dengan database locks
-   Backup mechanism untuk sequence corruption

### 4.2 Performance Optimization

-   Index database columns yang sering di-query
-   Implement caching untuk dropdown data
-   Pagination untuk large datasets

### 4.3 Security

-   Validation yang ketat untuk semua inputs
-   Authorization checks untuk setiap action
-   Audit trail untuk perubahan data penting

### 4.4 Extensibility

-   Flexible field system untuk kategori baru
-   Plugin system untuk custom letter types
-   API-ready untuk integration dengan sistem lain

### 4.5 Data Integrity

-   Foreign key constraints
-   Soft deletes untuk data penting
-   Regular backup procedures

## 5. DATA SEEDING

### 5.1 Kategori Surat Default

```php
// database/seeders/LetterCategorySeeder.php
$categories = [
    ['category_code' => 'A', 'category_name' => 'Surat Eksternal', 'description' => 'Surat untuk pihak eksternal'],
    ['category_code' => 'B', 'category_name' => 'Surat Internal', 'description' => 'Surat untuk internal perusahaan'],
    ['category_code' => 'PKWT', 'category_name' => 'Surat PKWT', 'description' => 'Perjanjian Kerja Waktu Tertentu'],
    ['category_code' => 'PAR', 'category_name' => 'Surat PAR', 'description' => 'Personal Action Request'],
    ['category_code' => 'CRTE', 'category_name' => 'Surat Pengalaman Kerja', 'description' => 'Certificate of Employment'],
    ['category_code' => 'SKPK', 'category_name' => 'Surat Ket. Pengalaman Kerja', 'description' => 'Surat Keterangan Pengalaman Kerja'],
    ['category_code' => 'MEMO', 'category_name' => 'Surat Memo', 'description' => 'Internal Memo'],
    ['category_code' => 'FPTK', 'category_name' => 'Form Permintaan Tenaga Kerja', 'description' => 'FPTK'],
    ['category_code' => 'FR', 'category_name' => 'Form Request Tiket', 'description' => 'Permintaan Tiket Perjalanan'],
];
```

## 6. KESIMPULAN ANALISIS

### 6.1 Summary Fitur Integration

Analisis komprehensif ini telah mengupdate desain fitur administrasi penomoran surat dengan **full integration support** untuk sistem surat yang ada (LOT) dan yang akan dikembangkan ke depannya. Key features yang ditambahkan:

#### **Dual Flow Process:**

1. **Alur 1: Request Nomor → Buat Surat**

    - Tim HCS request nomor di Administrasi Penomoran Surat
    - Status: `Reserved` → redirect ke sistem surat terkait
    - Status berubah ke `Used` setelah surat dibuat

2. **Alur 2: Buat Surat → Request Nomor**
    - Tim HCS mulai dari sistem surat (LOT)
    - Request nomor via modal/API call
    - Seamless integration tanpa pindah halaman

#### **Technical Integration:**

-   **Database**: Added status tracking, cross-reference fields, integration tables
-   **API**: RESTful endpoints untuk request, mark as used, get available numbers
-   **UI**: Radio buttons, AJAX calls, real-time feedback
-   **Models**: Integration methods, dynamic relationships, auto-assignment logic

### 6.2 Benefits untuk Tim HCS

1. **Flexibility**: Bisa mulai dari mana saja (administrasi penomoran atau sistem surat)
2. **Consistency**: Nomor surat terpusat dan tidak duplikasi
3. **Tracking**: Clear visibility nomor mana yang sudah/belum digunakan
4. **Scalability**: Framework siap untuk sistem surat baru
5. **User Experience**: Seamless workflow tanpa friction

### 6.3 Future Development Readiness

Design ini sudah **future-proof** untuk sistem surat lainnya:

-   **Template Integration Pattern** yang bisa di-copy untuk surat baru
-   **Standardized API** untuk consistent integration
-   **Plugin Architecture** untuk easy expansion
-   **Documentation Framework** untuk developer guidance

### 6.4 Implementation Priority

**Phase 1-2 (Critical)**: Database setup dan basic integration dengan LOT
**Phase 3-4 (Important)**: UI enhancement dan comprehensive testing  
**Phase 5 (Future)**: Framework standardization untuk sistem surat baru

Analisis ini memberikan **blueprint lengkap** untuk implementasi fitur administrasi penomoran surat yang:

-   ✅ **Terintegrasi sempurna** dengan aplikasi HCSSIS existing
-   ✅ **Support dual flow process** sesuai kebutuhan user
-   ✅ **Ready untuk expansion** sistem surat ke depannya
-   ✅ **Maintain backward compatibility** dengan LOT existing
-   ✅ **Follow best practices** Laravel dan AdminLTE patterns yang sudah ada

## UPDATE: Penggunaan Tabel Administrations untuk Data Karyawan

### Update Database Schema untuk menggunakan Administrations

Berdasarkan struktur tabel `administrations` yang sudah ada, sistem penomoran surat akan diupdate untuk menggunakan data dari tabel tersebut sebagai sumber data karyawan (NIK, nama, project).

**Struktur tabel administrations:**

-   `id` - Primary key
-   `employee_id` - Foreign key ke employees (UUID)
-   `project_id` - Foreign key ke projects
-   `position_id` - Foreign key ke positions
-   `nik` - NIK karyawan
-   `class` - Class karyawan
-   `doh` - Date of Hire
-   `poh` - Place of Hire
-   `basic_salary`, `site_allowance`, `other_allowance` - Data gaji
-   `termination_date`, `termination_reason` - Data terminasi
-   `coe_no` - Certificate of Employment number
-   `is_active` - Status aktif
-   `user_id` - User yang membuat record

### Updated Database Design

```sql
-- Updated letter_numbers table structure
ALTER TABLE letter_numbers
DROP COLUMN employee_id,
DROP COLUMN nik,
DROP COLUMN employee_name,
ADD COLUMN administration_id BIGINT UNSIGNED NULL AFTER related_document_id,
ADD FOREIGN KEY (administration_id) REFERENCES administrations(id),
DROP INDEX idx_employee,
ADD INDEX idx_administration (administration_id);
```

### Updated Model Relationships

```php
<?php
// Updated LetterNumber Model dengan administrations relationship

class LetterNumber extends Model
{
    // ... existing code ...

    public function administration()
    {
        return $this->belongsTo(Administration::class, 'administration_id');
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
}
```

### Updated Administration Model

```php
<?php
// Tambahan relationship di Administration model

class Administration extends Model
{
    // ... existing relationships ...

    public function letterNumbers()
    {
        return $this->hasMany(LetterNumber::class, 'administration_id');
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class, 'employee_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class, 'project_id');
    }

    public function position()
    {
        return $this->belongsTo(Position::class, 'position_id');
    }

    // Scope untuk karyawan aktif
    public function scopeActive($query)
    {
        return $query->where('is_active', 1);
    }
}
```

### Updated Controller Logic

```php
<?php
// Updated LetterNumberController untuk menggunakan administrations

class LetterNumberController extends Controller
{
    public function create($categoryCode = null)
    {
        $title = 'Buat Nomor Surat';
        $categories = LetterCategory::where('is_active', 1)->get();

        // Menggunakan administrations aktif untuk dropdown karyawan
        $administrations = Administration::with(['employee', 'project', 'position'])
                                       ->active()
                                       ->orderBy('nik')
                                       ->get();
        $projects = Project::orderBy('project_name')->get();

        // ... existing code ...

        return view('letter-numbers.create', compact(
            'title', 'categories', 'subjects', 'administrations', 'projects', 'selectedCategory'
        ));
    }

    public function store(Request $request)
    {
        $rules = [
            'letter_category_id' => 'required|exists:letter_categories,id',
            'letter_date' => 'required|date',
            'destination' => 'nullable|string|max:200',
            'remarks' => 'nullable|string',
        ];

        // Dynamic validation based on category
        $category = LetterCategory::find($request->letter_category_id);
        if ($category) {
            switch ($category->category_code) {
                case 'PKWT':
                    // PKWT no longer requires administration_id (NIK) - allowing import without NIK
                    $rules['duration'] = 'required|string';
                    $rules['start_date'] = 'required|date';
                    $rules['end_date'] = 'required|date|after:start_date';
                    $rules['pkwt_type'] = 'required|in:PKWT,PKWTT';
                    break;

                case 'PAR':
                    // PAR no longer requires administration_id (NIK) - allowing import without NIK
                    $rules['par_type'] = 'required|in:new hire,promosi,mutasi,demosi';
                    break;

                case 'CRTE':
                    // CRTE no longer requires administration_id (NIK) - allowing import without NIK
                    break;

                case 'SKPK':
                    // SKPK no longer requires administration_id (NIK)
                    break;
            }
        }

        $request->validate($rules);

        $letterNumber = new LetterNumber();
        $letterNumber->fill($request->all());
        $letterNumber->user_id = auth()->id();

        // Handle employee data for relevant categories
        if ($category && in_array($category->category_code, ['PKWT', 'PAR', 'CRTE', 'SKPK']) && $request->employee_id) {
            $employee = Employee::with('administrations')->find($request->employee_id);
            $letterNumber->employee_name = $employee->fullname;
            $letterNumber->nik = $employee->administrations->first()->nik ?? null;
        }

        $letterNumber->save();

        return redirect()->route('letter-numbers.index')
                        ->with('toast_success', 'Nomor surat berhasil dibuat: ' . $letterNumber->letter_number);
    }

    public function getLetterNumbers(Request $request)
    {
        $letterNumbers = LetterNumber::with([
                'category', 'subject', 'administration.employee',
                'administration.project', 'project', 'user', 'reservedBy', 'usedBy'
            ])
            ->when($request->letter_category_id, function($query, $categoryId) {
                return $query->where('letter_category_id', $categoryId);
            })
            ->when($request->status, function($query, $status) {
                return $query->where('status', $status);
            })
            ->when($request->date_from, function($query, $date) {
                return $query->where('letter_date', '>=', $date);
            })
            ->when($request->date_to, function($query, $date) {
                return $query->where('letter_date', '<=', $date);
            })
            ->orderBy('letter_date', 'desc')
            ->orderBy('sequence_number', 'desc');

        return datatables()->of($letterNumbers)
            ->addIndexColumn()
            ->addColumn('category_name', function ($row) {
                return $row->category->category_name ?? '-';
            })
            ->addColumn('subject_display', function ($row) {
                return $row->subject->subject_name ?? $row->custom_subject ?? '-';
            })
            ->addColumn('employee_display', function ($row) {
                if ($row->administration && $row->administration->employee) {
                    return $row->administration->employee->fullname .
                           ' (' . $row->administration->nik . ')';
                }
                return '-';
            })
            ->addColumn('project_display', function ($row) {
                // Prioritas: project dari administration, lalu project langsung
                $project = $row->administration && $row->administration->project
                          ? $row->administration->project
                          : $row->project;
                return $project ? $project->project_name : '-';
            })
            ->addColumn('status_badge', function ($row) {
                $badges = [
                    'reserved' => '<span class="badge badge-warning">Reserved</span>',
                    'used' => '<span class="badge badge-success">Used</span>',
                    'cancelled' => '<span class="badge badge-danger">Cancelled</span>',
                ];
                return $badges[$row->status] ?? '<span class="badge badge-secondary">Unknown</span>';
            })
            ->addColumn('action', function ($row) {
                return view('letter-numbers.action', compact('row'));
            })
            ->rawColumns(['status_badge', 'action'])
            ->toJson();
    }

    // Updated API endpoint untuk request number
    public function requestNumber(Request $request)
    {
        $request->validate([
            'letter_category_id' => 'required|exists:letter_categories,id',
            'letter_date' => 'required|date',
            'custom_subject' => 'nullable|string|max:200',
            'administration_id' => 'nullable|exists:administrations,id',
            'project_id' => 'nullable|exists:projects,id',
        ]);

        try {
            $letterNumber = new LetterNumber();
            $letterNumber->letter_category_id = $request->letter_category_id;
            $letterNumber->letter_date = $request->letter_date;
            $letterNumber->custom_subject = $request->custom_subject;
            $letterNumber->administration_id = $request->administration_id;
            $letterNumber->project_id = $request->project_id;
            $letterNumber->user_id = auth()->id();
            $letterNumber->save();

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $letterNumber->id,
                    'letter_number' => $letterNumber->letter_number,
                    'letter_category_id' => $letterNumber->letter_category_id,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal membuat nomor surat: ' . $e->getMessage()
            ], 500);
        }
    }
}
```

### Updated View untuk Employee Selection

```blade
{{-- resources/views/letter-numbers/create.blade.php --}}
{{-- Updated employee selection section --}}

<div id="employee-section" style="display: none;">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label>Pilih Karyawan <span class="text-danger">*</span></label>
                <select class="form-control select2bs4" name="administration_id" id="administration_id">
                    <option value="">- Pilih Karyawan -</option>
                    @foreach($administrations as $admin)
                        <option value="{{ $admin->id }}"
                                data-nik="{{ $admin->nik }}"
                                data-employee-name="{{ $admin->employee->fullname ?? '' }}"
                                data-project-id="{{ $admin->project_id }}"
                                data-project-name="{{ $admin->project->project_name ?? '' }}">
                            {{ $admin->nik }} - {{ $admin->employee->fullname ?? 'N/A' }}
                            ({{ $admin->project->project_name ?? 'No Project' }})
                        </option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label>Project</label>
                <input type="text" class="form-control" id="display_project" readonly>
                <small class="form-text text-muted">
                    Project akan otomatis terisi berdasarkan data administrasi karyawan
                </small>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-md-12">
            <div class="alert alert-info">
                <strong>Info:</strong>
                Data NIK, nama karyawan, dan project akan otomatis diambil dari data administrasi yang dipilih.
            </div>
        </div>
    </div>
</div>

<script>
// JavaScript untuk menampilkan info karyawan saat dipilih
$('#administration_id').change(function() {
    var selectedOption = $(this).find('option:selected');
    if (selectedOption.val()) {
        $('#display_project').val(selectedOption.data('project-name'));
    } else {
        $('#display_project').val('');
    }
});
</script>
```

### Benefits Menggunakan Administrations Table

1. **Data Consistency**: NIK, nama, dan project selalu konsisten dengan data administrasi aktual
2. **Single Source of Truth**: Tidak ada duplikasi data karyawan di berbagai tempat
3. **Real-time Updates**: Perubahan data karyawan otomatis terefleksi di surat
4. **Better Relationships**: Leverage existing relationships yang sudah dibangun
5. **Data Integrity**: Foreign key constraints memastikan referential integrity
6. **Historical Tracking**: Bisa track perubahan administrasi karyawan dari waktu ke waktu

### Migration Script untuk Update Existing Data

```php
<?php
// Migration untuk update existing letter_numbers data

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\LetterNumber;
use App\Models\Administration;

return new class extends Migration
{
    public function up()
    {
        // Tambah kolom administration_id dulu
        Schema::table('letter_numbers', function (Blueprint $table) {
            $table->foreignId('administration_id')->nullable()->after('related_document_id');
        });

        // Migrate existing data jika ada
        $letterNumbers = LetterNumber::whereNotNull('employee_id')->get();

        foreach ($letterNumbers as $letterNumber) {
            // Cari administrasi berdasarkan employee_id dan nik
            $administration = Administration::where('employee_id', $letterNumber->employee_id)
                                          ->where('nik', $letterNumber->nik)
                                          ->where('is_active', 1)
                                          ->first();

            if ($administration) {
                $letterNumber->administration_id = $administration->id;
                $letterNumber->save();
            }
        }

        // Remove old columns setelah migration selesai
        Schema::table('letter_numbers', function (Blueprint $table) {
            $table->dropForeign(['employee_id']);
            $table->dropIndex(['employee_id']);
            $table->dropColumn(['employee_id', 'nik', 'employee_name']);

            // Add new foreign key and index
            $table->foreign('administration_id')->references('id')->on('administrations');
            $table->index('administration_id');
        });
    }

    public function down()
    {
        Schema::table('letter_numbers', function (Blueprint $table) {
            $table->dropForeign(['administration_id']);
            $table->dropIndex(['administration_id']);
            $table->dropColumn('administration_id');

            // Restore old columns
            $table->foreignUuid('employee_id')->nullable()->constrained('employees');
            $table->string('nik', 50)->nullable();
            $table->string('employee_name', 100)->nullable();
            $table->index('employee_id');
        });
    }
};
```

Dengan update ini, sistem penomoran surat akan fully integrated dengan data administrasi karyawan yang sudah ada, memastikan konsistensi data dan menghindari duplikasi informasi.

## 7. UPDATE 2: FITUR BEHAVIOR PENOMORAN (ANNUAL RESET VS. CONTINUOUS)

Berdasarkan permintaan untuk menambah fleksibilitas pada sistem penomoran surat, desain diperbarui untuk mendukung dua jenis "behavior" penomoran yang berbeda untuk setiap kategori surat.

### 7.1 Analisis Kebutuhan & Dampak

#### 7.1.1 Kebutuhan Bisnis

Terdapat dua skenario kebutuhan untuk sequence nomor surat:

1.  **Annual Reset**: Nomor urut (sequence) direset kembali ke `0001` setiap awal tahun. Ini adalah behavior standar untuk sebagian besar surat resmi. Contoh: `A0001/HC/I/2023` akan diikuti oleh `A0001/HC/I/2024` di tahun berikutnya.
2.  **Continuous**: Nomor urut terus bertambah (increment) tanpa pernah direset, mengabaikan pergantian tahun. Ini berguna untuk dokumen internal seperti Memo atau dokumen lain yang memerlukan sequence unik berkelanjutan. Contoh: `MEMO0531` akan diikuti oleh `MEMO0532` terlepas dari tahun pembuatan.

#### 7.1.2 Dampak Perubahan

Untuk mengimplementasikan fitur ini, diperlukan perubahan pada komponen berikut:

-   **Database**: Tabel `letter_categories` perlu kolom baru untuk menyimpan behavior penomoran.
-   **Model**: Logika pada method `LetterNumber::generateLetterNumber()` perlu dimodifikasi secara signifikan untuk menangani kedua behavior.
-   **Controller & View**: Form untuk mengelola `LetterCategory` (create & edit) perlu field baru untuk mengatur behavior ini.
-   **Seeder**: Seeder untuk `letter_categories` perlu diupdate untuk mengisi nilai default behavior.

### 7.2 Desain Perubahan Teknis

#### 7.2.1 Update Skema Database: `letter_categories`

Kolom baru `numbering_behavior` akan ditambahkan ke tabel `letter_categories`.

```sql
-- Perubahan pada tabel letter_categories
ALTER TABLE letter_categories
ADD COLUMN numbering_behavior ENUM('annual_reset', 'continuous') NOT NULL DEFAULT 'annual_reset' COMMENT 'Defines numbering reset behavior' AFTER description;
```

Struktur tabel `letter_categories` yang telah diperbarui:

```sql
CREATE TABLE letter_categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    category_code VARCHAR(10) NOT NULL UNIQUE,
    category_name VARCHAR(100) NOT NULL,
    description TEXT,
    numbering_behavior ENUM('annual_reset', 'continuous') NOT NULL DEFAULT 'annual_reset',
    is_active BOOLEAN DEFAULT 1,
    user_id BIGINT UNSIGNED,
    created_at TIMESTAMP,
    updated_at TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id)
);
```

#### 7.2.2 Update Logika Model: `LetterNumber::generateLetterNumber()`

Method `generateLetterNumber()` di dalam model `LetterNumber` akan diubah untuk memeriksa `numbering_behavior` dari kategori terkait sebelum menentukan `sequence_number`.

```php
<?php
// app/Models/LetterNumber.php

// ...

    // Generate letter number (Updated Logic)
    public function generateLetterNumber()
    {
        // Pastikan relasi category sudah di-load untuk mendapatkan behavior dan code
        if (!$this->relationLoaded('category')) {
            $this->load('category');
        }

        $category = $this->category;
        $year = date('Y', strtotime($this->letter_date));

        $query = static::where('letter_category_id', $this->letter_category_id);

        // Terapkan logika berdasarkan behavior penomoran
        if ($category->numbering_behavior === 'annual_reset') {
            // Reset setiap tahun
            $query->where('year', $year);
        }
        // Untuk 'continuous', kita tidak membatasi berdasarkan tahun

        $sequence = $query->max('sequence_number') + 1;

        $this->sequence_number = $sequence;
        $this->year = $year; // Kolom tahun tetap diisi untuk pencatatan
        $this->letter_number = $this->category->category_code . sprintf('%04d', $sequence);
    }

// ...
```

#### 7.2.3 Update Seeder: `LetterCategorySeeder`

Seeder untuk `letter_categories` diupdate untuk menyertakan nilai `numbering_behavior`. Sebagai contoh, kita akan mengatur `PKWT`, `PAR`, `CRTE`, dan `SKPK` untuk menggunakan penomoran _continuous_.

```php
<?php
// database/seeders/LetterCategorySeeder.php

$categories = [
    ['category_code' => 'A', 'category_name' => 'Surat Eksternal', 'description' => 'Surat untuk pihak eksternal', 'numbering_behavior' => 'annual_reset'],
    ['category_code' => 'B', 'category_name' => 'Surat Internal', 'description' => 'Surat untuk internal perusahaan', 'numbering_behavior' => 'annual_reset'],
    ['category_code' => 'PKWT', 'category_name' => 'Surat PKWT', 'description' => 'Perjanjian Kerja Waktu Tertentu', 'numbering_behavior' => 'continuous'],
    ['category_code' => 'PAR', 'category_name' => 'Surat PAR', 'description' => 'Personal Action Request', 'numbering_behavior' => 'continuous'],
    ['category_code' => 'CRTE', 'category_name' => 'Surat Pengalaman Kerja', 'description' => 'Certificate of Employment', 'numbering_behavior' => 'continuous'],
    ['category_code' => 'SKPK', 'category_name' => 'Surat Ket. Pengalaman Kerja', 'description' => 'Surat Keterangan Pengalaman Kerja', 'numbering_behavior' => 'continuous'],
    ['category_code' => 'MEMO', 'category_name' => 'Surat Memo', 'description' => 'Internal Memo', 'numbering_behavior' => 'annual_reset'],
    ['category_code' => 'FPTK', 'category_name' => 'Form Permintaan Tenaga Kerja', 'description' => 'FPTK', 'numbering_behavior' => 'annual_reset'],
    ['category_code' => 'FR', 'category_name' => 'Form Request Tiket', 'description' => 'Permintaan Tiket Perjalanan', 'numbering_behavior' => 'annual_reset'],
];

// Logika untuk insert data...
```

#### 7.2.4 Update Controller & View: `LetterCategory` Management

Untuk memungkinkan user mengelola behavior ini, form create dan edit untuk kategori surat harus diupdate.

**Contoh Update pada `LetterCategoryController`:**

```php
<?php
// app/Http/Controllers/LetterCategoryController.php

// Pada method store() dan update()
public function store(Request $request)
{
    $request->validate([
        'category_code' => 'required|string|max:10|unique:letter_categories,category_code',
        'category_name' => 'required|string|max:100',
        'description' => 'nullable|string',
        'numbering_behavior' => 'required|in:annual_reset,continuous', // Validasi input baru
        'is_active' => 'required|boolean',
    ]);

    // ... logika create ...
}

public function update(Request $request, LetterCategory $letterCategory)
{
    $request->validate([
        'category_code' => 'required|string|max:10|unique:letter_categories,category_code,' . $letterCategory->id,
        'category_name' => 'required|string|max:100',
        'description' => 'nullable|string',
        'numbering_behavior' => 'required|in:annual_reset,continuous', // Validasi input baru
        'is_active' => 'required|boolean',
    ]);

    // ... logika update ...
}
```

**Contoh Update pada View `resources/views/letter-categories/create.blade.php`:**

```blade
{{-- ... Form fields lainnya ... --}}

<div class="form-group">
    <label for="description">Deskripsi</label>
    <textarea name="description" id="description" class="form-control" rows="3">{{ old('description') }}</textarea>
</div>

<div class="form-group">
    <label>Behavior Penomoran <span class="text-danger">*</span></label>
    <div class="custom-control custom-radio">
        <input class="custom-control-input" type="radio" id="annual_reset" name="numbering_behavior" value="annual_reset" {{ old('numbering_behavior', 'annual_reset') == 'annual_reset' ? 'checked' : '' }}>
        <label for="annual_reset" class="custom-control-label">Annual Reset</label>
        <small class="form-text text-muted">Nomor urut akan direset setiap awal tahun.</small>
    </div>
    <div class="custom-control custom-radio">
        <input class="custom-control-input" type="radio" id="continuous" name="numbering_behavior" value="continuous" {{ old('numbering_behavior') == 'continuous' ? 'checked' : '' }}>
        <label for="continuous" class="custom-control-label">Continuous</label>
        <small class="form-text text-muted">Nomor urut akan terus bertambah tanpa reset.</small>
    </div>
</div>

<div class="form-group">
    <label>Status</label>
    {{-- ... --}}
</div>
```

### 7.3 Rencana Implementasi

Implementasi fitur ini dapat dilakukan dalam beberapa langkah:

1.  **Migration**: Buat file migration baru untuk menambahkan kolom `numbering_behavior` ke tabel `letter_categories`.
    ```bash
    php artisan make:migration add_numbering_behavior_to_letter_categories_table
    ```
2.  **Model Update**: Update method `generateLetterNumber()` di model `LetterNumber`.
3.  **Seeder Update**: Update `LetterCategorySeeder` untuk menambahkan data `numbering_behavior`. Jalankan ulang seeder jika diperlukan.
    ```bash
    php artisan db:seed --class=LetterCategorySeeder
    ```
4.  **UI Update**: Modifikasi `LetterCategoryController` beserta view `create` dan `edit`-nya untuk bisa mengelola field baru.
5.  **Testing**: Lakukan pengujian menyeluruh untuk kedua behavior (annual reset dan continuous) untuk memastikan logika penomoran berjalan sesuai harapan.

## 8. LANGKAH-LANGKAH IMPLEMENTASI FITUR BEHAVIOR PENOMORAN

Berikut adalah panduan langkah demi langkah untuk mengimplementasikan fitur behavior penomoran.

### Langkah 1: Buat dan Jalankan Migration

Pertama, buat file migration untuk menambahkan kolom `numbering_behavior` ke tabel `letter_categories`.

1.  **Jalankan command Artisan** untuk membuat file migration:

    ```bash
    php artisan make:migration add_numbering_behavior_to_letter_categories_table --table=letter_categories
    ```

2.  **Edit file migration** yang baru dibuat di `database/migrations/`. Isi dengan kode berikut untuk menambahkan kolom `numbering_behavior` dan untuk menghapusnya saat rollback.

    ```php
    <?php

    use Illuminate\Database\Migrations\Migration;
    use Illuminate\Database\Schema\Blueprint;
    use Illuminate\Support\Facades\Schema;

    return new class extends Migration
    {
        public function up()
        {
            Schema::table('letter_categories', function (Blueprint $table) {
                $table->enum('numbering_behavior', ['annual_reset', 'continuous'])
                      ->default('annual_reset')
                      ->after('description')
                      ->comment('Defines numbering reset behavior');
            });
        }

        public function down()
        {
            Schema::table('letter_categories', function (Blueprint $table) {
                $table->dropColumn('numbering_behavior');
            });
        }
    };
    ```

3.  **Jalankan migrasi** untuk menerapkan perubahan ke database:
    ```bash
    php artisan migrate
    ```

### Langkah 2: Update LetterCategory Seeder

Update seeder untuk mengisi nilai default `numbering_behavior` pada kategori yang sudah ada.

1.  **Buka file `database/seeders/LetterCategorySeeder.php`**.
2.  **Modifikasi array `$categories`** untuk menyertakan key `numbering_behavior`. Atur `PKWT`, `PAR`, `CRTE`, dan `SKPK` ke `continuous` dan sisanya ke `annual_reset`.

    ```php
    // database/seeders/LetterCategorySeeder.php
    <?php

    namespace Database\Seeders;

    use App\Models\User;
    use App\Models\LetterCategory;
    use Illuminate\Database\Seeder;

    class LetterCategorySeeder extends Seeder
    {
        public function run()
        {
            $user = User::where('email', 'admin@mail.com')->first();
            $categories = [
                ['category_code' => 'A', 'category_name' => 'Surat Eksternal', 'description' => 'Surat untuk pihak eksternal', 'numbering_behavior' => 'annual_reset'],
                ['category_code' => 'B', 'category_name' => 'Surat Internal', 'description' => 'Surat untuk internal perusahaan', 'numbering_behavior' => 'annual_reset'],
                ['category_code' => 'PKWT', 'category_name' => 'Surat PKWT', 'description' => 'Perjanjian Kerja Waktu Tertentu', 'numbering_behavior' => 'continuous'],
                ['category_code' => 'PAR', 'category_name' => 'Surat PAR', 'description' => 'Personal Action Request', 'numbering_behavior' => 'continuous'],
                ['category_code' => 'CRTE', 'category_name' => 'Surat Pengalaman Kerja', 'description' => 'Certificate of Employment', 'numbering_behavior' => 'continuous'],
                ['category_code' => 'SKPK', 'category_name' => 'Surat Ket. Pengalaman Kerja', 'description' => 'Surat Keterangan Pengalaman Kerja', 'numbering_behavior' => 'continuous'],
                ['category_code' => 'MEMO', 'category_name' => 'Surat Memo', 'description' => 'Internal Memo', 'numbering_behavior' => 'annual_reset'],
                ['category_code' => 'FPTK', 'category_name' => 'Form Permintaan Tenaga Kerja', 'description' => 'FPTK', 'numbering_behavior' => 'annual_reset'],
                ['category_code' => 'FR', 'category_name' => 'Form Request Tiket', 'description' => 'Permintaan Tiket Perjalanan', 'numbering_behavior' => 'annual_reset'],
            ];

            foreach ($categories as $category) {
                LetterCategory::updateOrCreate(
                    ['category_code' => $category['category_code']],
                    [
                        'category_name' => $category['category_name'],
                        'description' => $category['description'],
                        'numbering_behavior' => $category['numbering_behavior'],
                        'user_id' => $user->id,
                    ]
                );
            }
        }
    }
    ```

3.  **Jalankan ulang seeder** untuk memperbarui data di database:
    ```bash
    php artisan db:seed --class=LetterCategorySeeder
    ```

### Langkah 3: Update Logika Model `LetterNumber`

Modifikasi method `generateLetterNumber()` di `app/Models/LetterNumber.php` untuk menangani logika penomoran baru.

```php
// app/Models/LetterNumber.php
// ...
    public function generateLetterNumber()
    {
        // Pastikan relasi category sudah di-load untuk mendapatkan behavior dan code
        if (!$this->relationLoaded('category')) {
            $this->load('category');
        }

        $category = $this->category;
        $year = date('Y', strtotime($this->letter_date));

        $query = static::where('letter_category_id', $this->letter_category_id);

        // Terapkan logika berdasarkan behavior penomoran
        if ($category->numbering_behavior === 'annual_reset') {
            // Reset setiap tahun
            $query->where('year', $year);
        }
        // Untuk 'continuous', kita tidak membatasi query berdasarkan tahun

        $sequence = $query->max('sequence_number') + 1;

        $this->sequence_number = $sequence;
        $this->year = $year; // Kolom tahun tetap diisi untuk pencatatan

        // Format penomoran bisa disesuaikan jika perlu
        $this->letter_number = $this->category->category_code . sprintf('%04d', $sequence);
    }
// ...
```

### Langkah 4: Update `LetterCategoryController`

Tambahkan validasi untuk field `numbering_behavior` di method `store` dan `update`.

```php
// app/Http/Controllers/LetterCategoryController.php
// ...
    public function store(Request $request)
    {
        $request->validate([
            'category_code' => 'required|string|max:10|unique:letter_categories,category_code',
            'category_name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'numbering_behavior' => 'required|in:annual_reset,continuous', // Validasi baru
        ]);

        // ... (logika store lainnya)
    }

    public function update(Request $request, LetterCategory $letterCategory)
    {
        $request->validate([
            'category_code' => 'required|string|max:10|unique:letter_categories,category_code,' . $letterCategory->id,
            'category_name' => 'required|string|max:100',
            'description' => 'nullable|string',
            'numbering_behavior' => 'required|in:annual_reset,continuous', // Validasi baru
        ]);

        // ... (logika update lainnya)
    }
// ...
```

### Langkah 5: Update View Letter Category

Tambahkan input radio button di form create dan edit agar user dapat memilih behavior penomoran.

1.  **Edit file `resources/views/letter-categories/create.blade.php`**:
    Tambahkan kode berikut sebelum form group untuk "Status".

    ```blade
    <div class="form-group">
        <label>Behavior Penomoran <span class="text-danger">*</span></label>
        <div class="custom-control custom-radio">
            <input class="custom-control-input" type="radio" id="annual_reset" name="numbering_behavior" value="annual_reset" checked>
            <label for="annual_reset" class="custom-control-label">Annual Reset</label>
            <small class="form-text text-muted">Nomor urut akan direset ke 1 setiap awal tahun.</small>
        </div>
        <div class="custom-control custom-radio">
            <input class="custom-control-input" type="radio" id="continuous" name="numbering_behavior" value="continuous">
            <label for="continuous" class="custom-control-label">Continuous</label>
            <small class="form-text text-muted">Nomor urut akan terus bertambah tanpa direset.</small>
        </div>
    </div>
    ```

2.  **Edit file `resources/views/letter-categories/edit.blade.php`**:
    Tambahkan kode berikut dengan logika untuk menampilkan value yang sudah ada.

    ```blade
    <div class="form-group">
        <label>Behavior Penomoran <span class="text-danger">*</span></label>
        <div class="custom-control custom-radio">
            <input class="custom-control-input" type="radio" id="annual_reset" name="numbering_behavior" value="annual_reset" {{ $letterCategory->numbering_behavior == 'annual_reset' ? 'checked' : '' }}>
            <label for="annual_reset" class="custom-control-label">Annual Reset</label>
            <small class="form-text text-muted">Nomor urut akan direset ke 1 setiap awal tahun.</small>
        </div>
        <div class="custom-control custom-radio">
            <input class="custom-control-input" type="radio" id="continuous" name="numbering_behavior" value="continuous" {{ $letterCategory->numbering_behavior == 'continuous' ? 'checked' : '' }}>
            <label for="continuous" class="custom-control-label">Continuous</label>
            <small class="form-text text-muted">Nomor urut akan terus bertambah tanpa direset.</small>
        </div>
    </div>
    ```

### Langkah 6: Pengujian Akhir

Setelah semua perubahan diterapkan, lakukan pengujian untuk memverifikasi fungsionalitas:

1.  **Buat Nomor Surat Baru (Annual Reset)**:
    -   Pilih kategori dengan behavior `annual_reset` (misal: Surat Eksternal - A).
    -   Buat beberapa surat di tahun ini. Pastikan nomor urutnya bertambah (A0001, A0002).
    -   Ubah tanggal pembuatan surat ke tahun berikutnya. Pastikan nomor urutnya reset kembali ke A0001.
2.  **Buat Nomor Surat Baru (Continuous)**:
    -   Pilih kategori dengan behavior `continuous` (misal: Memo).
    -   Buat beberapa surat di tahun ini. Catat nomor terakhir (misal: MEMO0123).
    -   Ubah tanggal pembuatan ke tahun berikutnya. Pastikan nomor urutnya melanjutkan dari nomor terakhir (MEMO0124).
3.  **CRUD Kategori Surat**:
    -   Pastikan Anda dapat membuat kategori baru dengan memilih salah satu behavior.
    -   Pastikan Anda dapat mengedit behavior kategori yang sudah ada dan perubahan tersebut terefleksi pada penomoran berikutnya.
