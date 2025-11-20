# Analisa Komprehensif Sistem Recruitment Sessions

## Overview

Sistem Recruitment Sessions adalah komponen inti dari aplikasi ARKA Hero yang mengelola proses rekrutmen kandidat melalui berbagai tahapan assessment. Sistem ini mendukung dua sumber rekrutmen utama: **FPTK (Form Permintaan Tenaga Kerja)** dan **MPP (Man Power Plan)**.

## Arsitektur Sistem

### Model Database

#### RecruitmentSession (Model Utama)

```php
// Struktur utama tabel recruitment_sessions
- id: UUID primary key
- session_number: String (RSN/YYYY/MM/0001 format)
- fptk_id: Foreign key ke recruitment_requests
- mpp_detail_id: Foreign key ke man_power_plan_details
- candidate_id: Foreign key ke recruitment_candidates
- applied_date: Date
- source: String (manual_add/website)
- current_stage: Enum (cv_review, psikotes, tes_teori, interview, offering, mcu, hire)
- stage_status: Enum (pending, in_progress, completed, failed, skipped)
- overall_progress: Integer (0-100)
- stage_started_at: Timestamp
- stage_completed_at: Timestamp
- final_decision_date: Timestamp
- final_decision_by: User ID
- final_decision_notes: Text
- status: Enum (active, completed, rejected, withdrawn, cancelled, hired)
- created_at, updated_at: Timestamps
```

#### Relasi Utama

-   **belongsTo**: candidate, fptk, mppDetail
-   **hasOne**: cvReview, psikotes, tesTeori, offering, mcu, hiring
-   **hasMany**: interviews (polymorphic)
-   **morphMany**: comments, attachments

### Stage Flow Default

#### Full Recruitment Process (Standard)

```
CV Review → Psikotes → Tes Teori → Interview → Offering → MCU → Hire
```

#### Simplified Process (Magang/Harian)

```
MCU → Hire
```

### Business Logic

#### Stage Progression Rules

1. **Forward Progression**: Stage dapat dipindah ke stage berikutnya
2. **Backward Movement**: Diperbolehkan dengan validasi tertentu
3. **Skip Logic**: Tes Teori dapat di-skip berdasarkan posisi (non-mechanic)
4. **Failed Stage Lock**: Stage yang failed akan mengunci stage berikutnya

#### Progress Calculation (Dynamic)

-   **Formula**: `(completed_stages / total_valid_stages) * 100`
-   Progress dinamis berdasarkan jumlah stage yang benar-benar sudah completed (berhasil assessment)
-   Diperbarui secara real-time setiap kali ada perubahan stage atau assessment
-   Diimplementasikan di `RecruitmentSessionService::getProgressPercentage()`
-   Contoh: Jika ada 7 stages dan 3 sudah completed → progress = 42.9%

**Valid Stages per Session Type:**

-   **Standard (7 stages)**: cv_review, psikotes, tes_teori, interview, offering, mcu, hire
-   **Skip Theory Test (6 stages)**: cv_review, psikotes, interview, offering, mcu, hire
-   **Magang/Harian (2 stages)**: mcu, hire

**Progress Display:**

-   `$progressPercentage` di view menggunakan `RecruitmentSessionService::getProgressPercentage()`
-   Menampilkan 1 decimal place untuk precision yang lebih baik
-   Update otomatis di semua tempat tampilan progress (timeline, progress circle, info panel)

## Fitur Baru: Flexible Stage Transition

### Kebutuhan HR

Tim HR memerlukan fleksibilitas untuk:

-   Memulai interview sebelum psikotes
-   Melewati stage tertentu berdasarkan kondisi khusus
-   Mengubah urutan stage sesuai kebutuhan bisnis
-   Progress calculation yang menyesuaikan dengan stage flow yang berubah

### Solusi Implementasi

#### 1. Method transitionStage() di Controller

**Location**: `app/Http/Controllers/RecruitmentSessionController.php`

**Permission Required**: `recruitment-sessions.edit-stages`

**Features**:

-   Validasi permission yang ketat
-   Validasi stage transition logic
-   Force transition option untuk bypass rules
-   Automatic progress recalculation
-   Comprehensive logging untuk audit trail

**Method Signature**:

```php
public function transitionStage(Request $request, $sessionId)
```

**Validation Rules**:

```php
$request->validate([
    'target_stage' => 'required|in:cv_review,psikotes,tes_teori,interview,offering,mcu,hire',
    'reason' => 'required|string|max:500',
    'force_transition' => 'nullable|boolean'
]);
```

#### 2. Route Configuration

**Route**: `POST /recruitment/sessions/{sessionId}/transition-stage`

**Name**: `recruitment.sessions.transition-stage`

#### 3. UI Components

**Quick Actions Button**:

-   Hanya muncul untuk user dengan permission `recruitment-sessions.edit-stages`
-   Menggunakan icon `fas fa-exchange-alt`
-   Background color teal (#17a2b8)

**Modal Features**:

-   Dropdown target stage (filtered berdasarkan session type)
-   Textarea untuk reason (required, min 10 characters)
-   Checkbox force transition untuk bypass validation
-   Warning system untuk backward/skipping transitions
-   Real-time form validation

#### 4. Helper Methods

**getValidStagesForSession($session)**:

-   Menentukan stage yang valid berdasarkan employment type
-   Handle simplified process untuk magang/harian

**validateStageTransition($session, $targetStage)**:

-   Validasi forward transitions (selalu diperbolehkan)
-   Validasi backward transitions dengan failed stage check
-   Return detailed error messages

**hasFailedAssessment($session, $stage)**:

-   Check apakah stage tertentu sudah failed
-   Support semua jenis assessment (CV, psikotes, tes teori, interview, offering, MCU)

**calculateProgressForStage($session, $targetStage)**:

-   **Formula**: `(completed_stages / total_valid_stages) * 100`
-   Hitung progress dinamis berdasarkan jumlah stage yang sudah benar-benar completed
-   Menggunakan `isStageCompleted()` untuk menentukan stage yang valid
-   Progress akurat berdasarkan hasil assessment, bukan posisi stage

### Security & Permission

#### Permission System

-   **recruitment-sessions.edit-stages**: Khusus untuk stage transition
-   Separate dari permission edit biasa
-   Middleware protection di controller level
-   Blade directive `@can` untuk UI visibility

#### Audit Trail

-   Semua transition dicatat di log dengan detail:
    -   User ID yang melakukan transition
    -   Stage asal dan tujuan
    -   Reason yang diberikan
    -   Force transition flag
    -   Timestamp

### Validation Logic

#### Normal Transition Rules

1. **Forward Movement**: Selalu diperbolehkan
2. **Backward Movement**: Diperbolehkan tapi ada warning
3. **Stage Skipping**: Diperbolehkan dengan warning
4. **Failed Stage Check**: Tidak bisa backward melewati stage yang failed

#### Force Transition

-   Bypass semua validation rules
-   Require checkbox confirmation
-   Logged sebagai forced transition
-   Untuk kasus emergency/special business needs

### UI/UX Considerations

#### Visual Feedback

-   Warning alerts untuk backward/skipping transitions
-   Disabled submit button sampai form valid
-   Loading states selama processing
-   Success/error toasts

#### Form Validation

-   Target stage required
-   Reason minimum 10 characters
-   Real-time validation feedback
-   Bootstrap 4 styling consistency

### Testing Scenarios

#### Valid Transitions

1. CV Review → Psikotes (forward)
2. Interview → Offering (forward)
3. MCU → Hire (final stage)

#### Warning Transitions

1. Psikotes → CV Review (backward)
2. CV Review → Interview (skipping stages)

#### Invalid Transitions

1. Same stage transition (blocked)
2. Invalid stage for session type (blocked)
3. Backward through failed stage (blocked, unless force)

### Database Impact

#### Fields Updated

-   `current_stage`: Target stage
-   `stage_status`: Reset to 'pending'
-   `stage_started_at`: Current timestamp
-   `overall_progress`: Recalculated value
-   `stage_completed_at`: Reset to null

#### Logging

-   Laravel log dengan context lengkap
-   User tracking untuk audit
-   Force transition flag untuk monitoring

### Future Enhancements

#### Potential Features

1. **Stage Flow Templates**: Predefined stage sequences
2. **Conditional Transitions**: Business rule based transitions
3. **Bulk Transitions**: Multiple sessions at once
4. **Transition History**: UI untuk melihat history transitions
5. **Approval Workflow**: Require approval untuk certain transitions

#### Configuration Options

1. **Stage Dependencies**: Define prerequisite stages
2. **Progress Weights**: Custom progress calculation per stage
3. **Transition Rules**: Configurable validation rules
4. **Notification System**: Alert stakeholders on transitions

### Implementation Checklist

-   [x] Controller method dengan validation
-   [x] Route configuration
-   [x] Permission system setup
-   [x] UI modal dan form
-   [x] JavaScript validation
-   [x] CSS styling
-   [x] Progress recalculation logic
-   [x] Audit logging
-   [x] Error handling
-   [x] Testing scenarios

### Code Quality Standards

#### PHP Standards

-   PSR-12 compliance
-   Comprehensive error handling
-   Type hinting untuk parameters
-   Detailed PHPDoc comments

#### JavaScript Standards

-   ES6+ syntax
-   JSLint compliance
-   Event delegation patterns
-   Form validation best practices

#### Security Standards

-   Input sanitization
-   CSRF protection
-   Permission checks
-   SQL injection prevention

---

**Status**: ✅ **COMPLETED** - Flexible Stage Transition feature telah berhasil diimplementasikan dengan semua requirement terpenuhi.
