# Leave Entitlement Export/Import Plan

## Overview
Rencana implementasi fitur export/import leave entitlement untuk memudahkan input data dalam skala besar. File Excel yang diexport harus bisa diimport kembali (round-trip capability).

---

## Perspektif HR Konsultan

### Kebutuhan Bisnis
1. **Input Data Massal**: HR perlu input entitlement untuk ratusan karyawan sekaligus
2. **Format User-Friendly**: Excel harus mudah dipahami oleh HR staff non-technical
3. **Validasi Visual**: Error harus jelas dan mudah diperbaiki
4. **Round-Trip**: File yang diexport harus bisa langsung diimport kembali tanpa modifikasi struktur

### Struktur Excel yang Diinginkan
```
1 Baris = 1 Karyawan + 1 Period + Semua Leave Types
```

**Kolom:**
1. **NIK** (Key Identifier) - Required
2. **Nama** (Verification) - Auto-filled, read-only suggestion
3. **Project Code** (Context) - Auto-filled, read-only suggestion
4. **Start Period** (YYYY-MM-DD) - Required
5. **End Period** (YYYY-MM-DD) - Required
6. **[Leave Type 1]** (e.g., "Annual Leave") - Optional, default 0
7. **[Leave Type 2]** (e.g., "Long Service Leave") - Optional, default 0
8. **[Leave Type 3]** (e.g., "Sick Leave") - Optional, default 0
9. ... (Semua Leave Types yang aktif)
10. **Deposit Days** (Optional) - Untuk LSL first period
11. **Notes** (Optional) - Catatan khusus

### Best Practices untuk HR
- **Template dengan Data Sample**: File export berisi data existing sebagai contoh
- **Data Validation**: Excel dropdown untuk NIK (jika memungkinkan)
- **Color Coding**: 
  - Header row: Bold, colored background
  - Read-only columns: Light gray background
  - Required columns: Highlighted
- **Error Sheet**: Jika ada error, buat sheet terpisah untuk error log

---

## Perspektif Programmer

### Arsitektur

#### 1. Export Functionality
**Controller Method**: `LeaveEntitlementController::exportTemplate()`

**Langkah-langkah:**
1. Ambil semua active leave types
2. Ambil semua employees dengan administrations aktif
3. Group entitlements by employee + period
4. Buat Excel dengan struktur:
   - **Sheet 1: Data Template**
     - Header: NIK | Nama | Project | Start Period | End Period | [Leave Types...] | Deposit Days | Notes
     - Data: Existing entitlements (jika ada) atau empty rows dengan sample
   - **Sheet 2: Instructions**
     - Panduan penggunaan
     - Format tanggal yang benar
     - Validasi yang berlaku
   - **Sheet 3: Leave Types Reference**
     - List semua leave types dengan code dan default days

**Excel Format:**
- Header row: Bold, background color (#4472C4), white text
- Data rows: Alternating colors untuk readability
- Read-only columns: Locked (Nama, Project)
- Date columns: Format YYYY-MM-DD dengan validation
- Number columns: Integer validation, min 0

#### 2. Import Functionality
**Controller Method**: `LeaveEntitlementController::importTemplate()`

**Langkah-langkah:**
1. **Validasi File**:
   - Cek format file (.xlsx, .xls)
   - Cek struktur kolom (header harus sesuai)
   - Cek minimal required columns

2. **Pre-Processing**:
   - Baca Excel file
   - Parse header row untuk mapping kolom
   - Validasi struktur kolom

3. **Data Validation** (Row by Row):
   - **NIK**: Must exist in employees table
   - **Start Period & End Period**: 
     - Valid date format
     - Start < End
     - Period tidak overlap dengan existing (opsional, bisa replace)
   - **Leave Types**: 
     - Valid integer (>= 0)
     - Leave type exists and active
   - **Deposit Days**: 
     - Valid integer (>= 0)
     - Hanya untuk LSL (optional validation)

4. **Batch Processing**:
   - Group valid rows
   - Process dalam transaction
   - Handle duplicates (update existing vs create new)

5. **Error Handling**:
   - Collect semua error per row
   - Buat error report Excel
   - Return success count + error count

6. **Response**:
   - Success: Redirect dengan success message + summary
   - Partial Success: Show error report download link
   - Failure: Show error list + allow re-upload

### Technical Implementation

#### Dependencies
```php
use Maatwebsite\Excel\Facades\Excel;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithValidation;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
```

#### Export Class Structure
```php
class LeaveEntitlementExport implements 
    FromCollection, 
    WithHeadings, 
    WithMapping,
    WithStyles,
    WithColumnWidths
{
    // Generate template dengan existing data
    // Include instructions sheet
    // Include leave types reference sheet
}
```

#### Import Class Structure
```php
class LeaveEntitlementImport implements 
    ToCollection,
    WithHeadingRow,
    WithValidation,
    WithBatchInserts,
    WithChunkReading
{
    // Process import dengan validasi
    // Handle errors
    // Generate error report
}
```

### Data Integrity Rules

#### Business Rules yang Harus Divalidasi:
1. **Employee Validation**:
   - NIK harus exist di employees table
   - Employee harus punya active administration

2. **Period Validation**:
   - Start Period < End Period
   - Period length reasonable (1-24 months typical)
   - Tidak overlap dengan existing period (jika mode update)

3. **Leave Type Validation**:
   - Leave type harus exist dan active
   - Nilai >= 0 (integer)
   - Tidak melebihi batas maksimum (jika ada)

4. **Deposit Days Validation**:
   - Hanya untuk LSL (optional warning)
   - Nilai >= 0

5. **Duplicate Handling**:
   - Check: employee_id + leave_type_id + period_start + period_end
   - Options:
     - **Skip**: Abaikan jika sudah ada
     - **Update**: Update existing entitlement
     - **Replace**: Delete old + create new

### Error Handling Strategy

#### Error Types:
1. **File Errors**: Invalid file format, corrupted file
2. **Structure Errors**: Missing columns, wrong header
3. **Data Validation Errors**: 
   - Invalid NIK
   - Invalid date format
   - Invalid leave type value
4. **Business Logic Errors**:
   - Employee tidak punya active administration
   - Period overlap
   - Invalid period range

#### Error Report Format:
```excel
Sheet: Errors
Columns: Row Number | Column | Error Type | Error Message | Value
```

### Performance Considerations

1. **Chunk Reading**: Baca file dalam chunks untuk file besar
2. **Batch Insert**: Insert data dalam batch untuk performa
3. **Transaction**: Gunakan database transaction untuk rollback jika error
4. **Queue Processing**: Untuk file sangat besar (>1000 rows), consider queue job

---

## Struktur Excel Detail

### Sheet 1: Data Template

**Header Row:**
| NIK | Nama | Project | Start Period | End Period | Annual Leave | LSL | Sick Leave | ... | Deposit Days | Notes |
|-----|------|---------|--------------|------------|--------------|-----|------------|-----|--------------|-------|

**Data Rows:**
- Existing entitlements (jika export)
- Empty rows dengan sample (jika template baru)

**Formatting:**
- Header: Bold, Blue background (#4472C4), White text
- NIK: Text, Center aligned
- Nama: Text, Left aligned, Read-only (gray background)
- Project: Text, Left aligned, Read-only (gray background)
- Dates: Date format (YYYY-MM-DD), Center aligned
- Leave Types: Number, Integer, >= 0, Center aligned
- Deposit Days: Number, Integer, >= 0, Center aligned
- Notes: Text, Left aligned

### Sheet 2: Instructions

**Content:**
1. **Cara Penggunaan**:
   - Isi kolom NIK, Start Period, End Period (required)
   - Isi entitlement days untuk setiap leave type (optional, default 0)
   - Isi Deposit Days jika diperlukan (optional)
   - Tambahkan Notes jika ada catatan khusus

2. **Validasi**:
   - NIK harus exist di sistem
   - Start Period < End Period
   - Semua nilai numeric >= 0
   - Format tanggal: YYYY-MM-DD

3. **Tips**:
   - Gunakan copy-paste untuk data yang sama
   - Pastikan tidak ada baris kosong di tengah data
   - Kolom Nama dan Project akan otomatis terisi setelah import

### Sheet 3: Leave Types Reference

**Content:**
| Leave Type Code | Leave Type Name | Category | Default Days | Notes |
|-----------------|-----------------|----------|--------------|-------|

---

## Implementation Phases

### Phase 1: Basic Export
- [ ] Create export method
- [ ] Generate Excel dengan existing data
- [ ] Include header row
- [ ] Basic formatting

### Phase 2: Enhanced Export
- [ ] Add Instructions sheet
- [ ] Add Leave Types Reference sheet
- [ ] Advanced formatting (colors, validation)
- [ ] Handle multiple periods per employee

### Phase 3: Basic Import
- [ ] File upload handler
- [ ] Read Excel file
- [ ] Basic validation (structure, required fields)
- [ ] Simple error reporting

### Phase 4: Advanced Import
- [ ] Full data validation
- [ ] Business rule validation
- [ ] Batch processing
- [ ] Detailed error report
- [ ] Duplicate handling options

### Phase 5: UI Integration
- [ ] Add export button di index page
- [ ] Add import form dengan file upload
- [ ] Progress indicator untuk import besar
- [ ] Success/error notification

### Phase 6: Testing & Optimization
- [ ] Unit tests
- [ ] Integration tests
- [ ] Performance testing dengan file besar
- [ ] User acceptance testing

---

## API Endpoints

### Export
```
GET /leave-entitlements/export-template
Query Parameters:
  - project_id (optional): Filter by project
  - year (optional): Filter by period year
  - include_data (default: true): Include existing data or empty template
```

### Import
```
POST /leave-entitlements/import-template
Request:
  - file: Excel file (.xlsx, .xls)
  - duplicate_mode: skip|update|replace
  - validate_only: true|false (optional, untuk preview)
```

---

## Security Considerations

1. **File Upload Validation**:
   - Hanya terima .xlsx dan .xls
   - Validasi file size (max 10MB)
   - Scan untuk malicious content

2. **Permission Check**:
   - Export: `leave-entitlements.show`
   - Import: `leave-entitlements.create` + `leave-entitlements.edit`

3. **Data Access Control**:
   - User hanya bisa import data untuk employee yang mereka punya akses
   - Audit log untuk semua import/export activities

---

## Future Enhancements

1. **Template Versioning**: Support multiple template versions
2. **Bulk Update**: Update existing entitlements via Excel
3. **Partial Import**: Import hanya untuk specific leave types
4. **Scheduled Export**: Auto-export periodic reports
5. **API Integration**: REST API untuk programmatic import/export

