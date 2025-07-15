# Implementasi Letter Number untuk FPTK Recruitment

## 🎯 **OVERVIEW**

Implementasi letter number khusus untuk FPTK (Formulir Permintaan Tenaga Kerja) dalam sistem recruitment menggunakan sistem letter numbering yang sudah ada di aplikasi. Implementasi ini memastikan setiap FPTK memiliki nomor surat yang terstruktur dan terlacak.

## 🏗️ **KOMPONEN IMPLEMENTASI**

### 1. **Model RecruitmentRequest**

-   ✅ Menggunakan trait `HasLetterNumber`
-   ✅ Field `letter_number_id` dan `letter_number` di database
-   ✅ Method `assignFPTKLetterNumber()` untuk auto-assign letter number
-   ✅ Method `getFPTKLetterNumber()` untuk display format
-   ✅ Method `hasValidLetterNumber()` untuk validasi
-   ✅ Method `getLetterNumberInfo()` untuk informasi detail
-   ✅ Auto-assign letter number saat FPTK disetujui

### 2. **Letter Category FPTK**

-   ✅ Category code: `FPTK`
-   ✅ Category name: `Form Permintaan Tenaga Kerja`
-   ✅ Numbering behavior: `annual_reset` (reset setiap tahun)
-   ✅ Auto-creation jika tidak ada dalam seeder

### 3. **RecruitmentLetterNumberService**

-   ✅ Service khusus untuk mengelola letter number FPTK
-   ✅ Method `assignLetterNumberToFPTK()` untuk assignment
-   ✅ Method `getFPTKLetterNumberStats()` untuk statistik
-   ✅ Method `generateFPTKLetterNumberReport()` untuk laporan
-   ✅ Method `bulkAssignLetterNumbers()` untuk bulk assignment
-   ✅ Method `releaseLetterNumberFromFPTK()` untuk cancel assignment

### 4. **Integration dengan Recruitment System**

-   ✅ Dependency injection dalam `RecruitmentSessionService`
-   ✅ Auto-assign letter number saat session dibuat
-   ✅ Helper method untuk get letter number info
-   ✅ Integration dengan existing letter number system

## 📋 **FITUR UTAMA**

### **Auto-Assignment**

```php
// Auto-assign saat FPTK disetujui
$fptk->approve($userId);
// Letter number akan otomatis di-assign

// Manual assignment
$fptk->assignFPTKLetterNumber();
```

### **Display & Information**

```php
// Get formatted letter number
$letterNumber = $fptk->getFPTKLetterNumber();

// Get detailed info
$info = $fptk->getLetterNumberInfo();
// Returns: ['number', 'status', 'category', 'date', 'sequence']

// Check validity
$isValid = $fptk->hasValidLetterNumber();
```

### **Statistics & Reporting**

```php
$service = new RecruitmentLetterNumberService();

// Get statistics
$stats = $service->getFPTKLetterNumberStats(2024);

// Generate report
$report = $service->generateFPTKLetterNumberReport(2024);

// Bulk operations
$results = $service->bulkAssignLetterNumbers($fptkIds);
```

## 🔧 **KONFIGURASI LETTER NUMBER**

### **Format Nomor**

-   **Pattern**: `FPTK####` (contoh: FPTK0001, FPTK0002)
-   **Behavior**: Annual reset (mulai dari 0001 setiap tahun)
-   **Sequence**: Auto-increment per tahun

### **Database Schema**

```sql
-- Letter Category (sudah ada)
INSERT INTO letter_categories (category_code, category_name, numbering_behavior)
VALUES ('FPTK', 'Form Permintaan Tenaga Kerja', 'annual_reset');

-- Letter Number (auto-generated)
letter_number: 'FPTK0001'
sequence_number: 1
year: 2024
status: 'reserved' -> 'used'
```

## 📊 **WORKFLOW INTEGRATION**

### **1. FPTK Creation**

```
1. User creates FPTK → status: 'draft'
2. Request number generated as fallback
3. No letter number assigned yet
```

### **2. FPTK Approval**

```
1. FPTK approved → status: 'approved'
2. Auto-assign letter number dari category FPTK
3. Letter number status: 'reserved' → 'used'
4. FPTK dapat menerima aplikasi kandidat
```

### **3. Session Creation**

```
1. Candidate apply ke FPTK
2. Check FPTK has letter number
3. Auto-assign jika belum ada
4. Create recruitment session
```

## 🚀 **CARA PENGGUNAAN**

### **1. Setup (Sudah Otomatis)**

```php
// Letter category sudah di-seed otomatis
php artisan db:seed --class=RecruitmentRolePermissionSeeder
```

### **2. Dalam Controller**

```php
use App\Services\RecruitmentLetterNumberService;

class RecruitmentController extends Controller
{
    protected $letterService;

    public function __construct(RecruitmentLetterNumberService $letterService)
    {
        $this->letterService = $letterService;
    }

    public function assignLetterNumber($fptkId)
    {
        $fptk = RecruitmentRequest::find($fptkId);
        $success = $this->letterService->assignLetterNumberToFPTK($fptk);

        return response()->json(['success' => $success]);
    }
}
```

### **3. Dalam View**

```php
@foreach($fptks as $fptk)
    <tr>
        <td>{{ $fptk->getFPTKLetterNumber() }}</td>
        <td>{{ $fptk->request_number }}</td>
        <td>
            @if($fptk->hasValidLetterNumber())
                <span class="badge badge-success">Valid</span>
            @else
                <span class="badge badge-warning">No Letter Number</span>
            @endif
        </td>
    </tr>
@endforeach
```

## 🎯 **KEUNTUNGAN IMPLEMENTASI**

### **1. Standardisasi**

-   Semua FPTK memiliki nomor surat yang terstruktur
-   Format konsisten: FPTK0001, FPTK0002, dst.
-   Tracking yang jelas dan terlacak

### **2. Automation**

-   Auto-assign saat FPTK disetujui
-   Fallback ke request number jika diperlukan
-   Error handling yang baik

### **3. Integration**

-   Seamless dengan existing letter number system
-   Tidak mengganggu workflow yang sudah ada
-   Backward compatible

### **4. Reporting**

-   Statistik penggunaan letter number
-   Laporan bulanan dan tahunan
-   Export dan analytics

## 🔐 **SECURITY & VALIDATION**

### **1. Validasi**

```php
// Validasi format
$service->validateFPTKLetterNumber('FPTK0001'); // true

// Validasi assignment
$fptk->hasValidLetterNumber(); // true/false
```

### **2. Error Handling**

```php
try {
    $fptk->assignFPTKLetterNumber();
} catch (Exception $e) {
    Log::error('Letter number assignment failed: ' . $e->getMessage());
}
```

### **3. Rollback**

```php
// Release letter number (untuk cancel FPTK)
$service->releaseLetterNumberFromFPTK($fptk);
```

## 📈 **MONITORING & MAINTENANCE**

### **1. Logs**

-   Assignment berhasil/gagal
-   Auto-assignment triggers
-   Error tracking

### **2. Statistics**

-   Total letter number issued per tahun
-   Usage rate per departemen
-   Performance metrics

### **3. Maintenance**

-   Annual reset otomatis
-   Cleanup unused letter numbers
-   Database optimization

---

**✅ IMPLEMENTASI SELESAI**

Sistem letter number untuk FPTK recruitment telah berhasil diimplementasikan dengan fitur lengkap:

-   Auto-assignment saat approval
-   Integration dengan existing system
-   Comprehensive service layer
-   Error handling & logging
-   Statistics & reporting
-   Bulk operations support

Sistem siap digunakan untuk production dengan konfigurasi yang sudah optimal.
