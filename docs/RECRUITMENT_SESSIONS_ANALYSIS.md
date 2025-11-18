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
- stage_started_at: DateTime
- stage_completed_at: DateTime
- overall_progress: Decimal (progress percentage)
- status: Enum (in_process, hired, rejected, withdrawn, cancelled)
- final_decision_date: DateTime
- final_decision_by: Foreign key ke users
- final_decision_notes: Text
- stage_durations: JSON (tracking durasi setiap stage)
- created_by: Foreign key ke users
```

#### Assessment Models (One-to-One dengan Session)

1. **RecruitmentCvReview** - Review CV kandidat
2. **RecruitmentPsikotes** - Assessment psikotes
3. **RecruitmentTesTeori** - Tes teori teknis
4. **RecruitmentInterview** - Interview (HR/User/Trainer)
5. **RecruitmentOffering** - Penawaran kerja
6. **RecruitmentMcu** - Medical Check Up
7. **RecruitmentHiring** - Proses hiring dan onboarding

### Relationships

```
RecruitmentSession
├── belongsTo: RecruitmentRequest (FPTK)
├── belongsTo: ManPowerPlanDetail (MPP)
├── belongsTo: RecruitmentCandidate
├── hasOne: RecruitmentCvReview
├── hasOne: RecruitmentPsikotes
├── hasOne: RecruitmentTesTeori
├── hasMany: RecruitmentInterview
├── hasOne: RecruitmentOffering
├── hasOne: RecruitmentMcu
├── hasOne: RecruitmentHiring
└── hasMany: RecruitmentDocument
```

## Business Logic & Workflow

### Stage Management System

#### Standard Recruitment Flow (7 Stages)

```
CV Review → Psikotes → Tes Teori → Interview → Offering → MCU → Hiring
```

#### Simplified Flow (Magang/Harian - 2 Stages)

```
MCU → Hiring
```

#### Conditional Flow (Non-Mechanic Positions)

```
CV Review → Psikotes → Interview → Offering → MCU → Hiring
(Tes Teori di-skip untuk posisi non-teknis)
```

### Stage Status Management

-   **pending**: Stage belum dimulai
-   **in_progress**: Stage sedang berlangsung
-   **completed**: Stage berhasil diselesaikan
-   **failed**: Stage gagal (kandidat ditolak)
-   **skipped**: Stage di-skip berdasarkan kondisi

### Progress Calculation

#### Standard Progress (7 Stages)

```php
STAGE_PROGRESS = [
    'cv_review' => 14.3,    // 1/7 * 100
    'psikotes' => 28.6,     // 2/7 * 100
    'tes_teori' => 42.9,    // 3/7 * 100
    'interview' => 57.1,    // 4/7 * 100
    'offering' => 71.4,     // 5/7 * 100
    'mcu' => 85.7,          // 6/7 * 100
    'hire' => 100,          // 7/7 * 100
]
```

#### Adjusted Progress (6 Stages - Skip Tes Teori)

```php
ADJUSTED_PROGRESS = [
    'cv_review' => 16.7,    // 1/6 * 100
    'psikotes' => 33.3,     // 2/6 * 100
    'interview' => 50.0,    // 3/6 * 100
    'offering' => 66.7,     // 4/6 * 100
    'mcu' => 83.3,          // 5/6 * 100
    'hire' => 100,          // 6/6 * 100
]
```

#### Simplified Progress (Magang/Harian - 2 Stages)

```php
SIMPLIFIED_PROGRESS = [
    'mcu' => 50.0,    // 1/2 * 100
    'hire' => 100,    // 2/2 * 100
]
```

## Assessment Types & Business Rules

### 1. CV Review

**Decision**: recommended/not_recommended
**Pass Criteria**: decision === 'recommended'
**Fail Action**: Session status → 'rejected'

### 2. Psikotes Assessment

**Scores**: online_score, offline_score
**Pass Criteria**:

-   Online: ≥ 40
-   Offline: ≥ 8
    **Result**: pass/fail (jika salah satu gagal → fail)
    **Fail Action**: Session status → 'rejected'

### 3. Tes Teori (Technical Test)

**Score Range**: 0-100
**Categories**:

-   ≥ 76: Mechanic Senior
-   ≥ 61: Mechanic Advance
-   ≥ 46: Mechanic
-   ≥ 21: Helper Mechanic
-   < 21: Belum Kompeten (FAIL)
    **Pass Criteria**: score ≥ 21 (Belum Kompeten = fail)
    **Conditional**: Skip untuk posisi non-mechanic

### 4. Interview Assessment

**Types**: hr, user, trainer (conditional)
**Decision**: recommended/not_recommended
**Required Interviews**:

-   HR Interview: Always required
-   User Interview: Always required
-   Trainer Interview: Required for mechanic positions only
    **Pass Criteria**: All required interviews must be 'recommended'
    **Fail Action**: Individual interview fail → session rejected

### 5. Offering

**Result**: accepted/rejected
**Letter Number**: Auto-generated from letter_numbers table
**Pass Criteria**: result === 'accepted'
**Fail Action**: result === 'rejected' → session rejected

### 6. MCU (Medical Check Up)

**Result**: fit/unfit/follow_up
**Pass Criteria**: result === 'fit'
**Special Handling**: follow_up allows continuation in same stage
**Fail Action**: result === 'unfit' → session rejected

### 7. Hiring & Onboarding

**Process**: Employee & Administration creation
**Agreement Types**: pkwt/pkwtt/magang/harian
**NIK Generation**: Auto-generated unique NIK
**Position Assignment**: From FPTK/MPP data
**Final Action**: Session status → 'hired'

## Controller Architecture

### RecruitmentSessionController

#### Main Methods

-   `index()`: List all sessions with filtering
-   `store()`: Create new session (add candidate to FPTK/MPP)
-   `show($id)`: Show FPTK/MPP with all sessions
-   `showSession($id)`: Show individual session details
-   `destroy($id)`: Remove candidate from session

#### Assessment Update Methods

-   `updateCvReview()`: Process CV review decision
-   `updatePsikotes()`: Process psikotes assessment
-   `updateTesTeori()`: Process technical test
-   `updateInterview()`: Process interview assessment
-   `updateOffering()`: Process job offering
-   `updateMcu()`: Process medical check-up
-   `updateHiring()`: Process final hiring

#### Data Methods

-   `getSessions()`: DataTables data for sessions list
-   `getSessionData()`: AJAX data for single session
-   `getSessionsByFPTK()`: Sessions by FPTK
-   `getSessionsByCandidate()`: Sessions by candidate

### Session Number Generation

**Format**: RSN/YYYY/MM/0001
**Algorithm**:

```php
private function generateSessionNumber()
{
    $year = date('Y');
    $month = str_pad(date('m'), 2, '0', STR_PAD_LEFT);

    return DB::transaction(function () use ($year, $month) {
        $count = RecruitmentSession::where('session_number', 'LIKE', "RSN/{$year}/{$month}/%")->count();
        $newNumber = $count + 1;
        return "RSN/{$year}/{$month}/" . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    });
}
```

## Security & Permissions

### Middleware Permissions

```php
$this->middleware('permission:recruitment-sessions.show')->only('index', 'show', 'showSession', 'getSessions', 'getSessionData', 'getSessionsByFPTK', 'getSessionsByCandidate');
$this->middleware('permission:recruitment-sessions.create')->only('store');
$this->middleware('permission:recruitment-sessions.edit')->only('updateCvReview', 'updatePsikotes', 'updateTesTeori', 'updateInterview', 'updateOffering', 'updateMcu', 'updateHiring', 'closeRequest');
$this->middleware('permission:recruitment-sessions.delete')->only('destroy');
```

### Stage Editability Rules

```php
// Lock failed stages and all subsequent stages
if ($hasFailedStage && $thisOrder >= $failedStageOrder) {
    $editable = false;
    $lockReason = 'Cannot edit this stage because a previous stage failed or was rejected.';
}

// Lock completed stages
if ($isCompleted) {
    $editable = false;
    $lockReason = 'This stage has been completed and cannot be edited.';
}
```

## API Routes

### Web Routes (routes/web.php)

```php
// Main session routes
Route::group(['prefix' => 'recruitment/sessions', 'middleware' => ['auth']], function () {
    Route::get('/', [RecruitmentSessionController::class, 'index'])->name('recruitment.sessions.index');
    Route::get('/data', [RecruitmentSessionController::class, 'getSessions'])->name('recruitment.sessions.data');
    Route::get('/{id}', [RecruitmentSessionController::class, 'show'])->name('recruitment.sessions.show');
    Route::get('/candidate/{id}', [RecruitmentSessionController::class, 'showSession'])->name('recruitment.sessions.candidate');
    Route::post('/', [RecruitmentSessionController::class, 'store'])->name('recruitment.sessions.store');

    // Assessment updates
    Route::post('/{sessionId}/update-cv-review', [RecruitmentSessionController::class, 'updateCvReview'])->name('recruitment.sessions.update-cv-review');
    Route::post('/{sessionId}/update-psikotes', [RecruitmentSessionController::class, 'updatePsikotes'])->name('recruitment.sessions.update-psikotes');
    Route::post('/{sessionId}/update-tes-teori', [RecruitmentSessionController::class, 'updateTesTeori'])->name('recruitment.sessions.update-tes-teori');
    Route::post('/{sessionId}/update-interview', [RecruitmentSessionController::class, 'updateInterview'])->name('recruitment.sessions.update-interview');
    Route::post('/{sessionId}/update-offering', [RecruitmentSessionController::class, 'updateOffering'])->name('recruitment.sessions.update-offering');
    Route::post('/{sessionId}/update-mcu', [RecruitmentSessionController::class, 'updateMcu'])->name('recruitment.sessions.update-mcu');
    Route::post('/{sessionId}/update-hiring', [RecruitmentSessionController::class, 'updateHiring'])->name('recruitment.sessions.update-hiring');

    // Management
    Route::post('/{sessionId}/close-request', [RecruitmentSessionController::class, 'closeRequest'])->name('recruitment.sessions.close-request');
    Route::delete('/{id}', [RecruitmentSessionController::class, 'destroy'])->name('recruitment.sessions.destroy');

    // Data endpoints
    Route::get('/{id}/data', [RecruitmentSessionController::class, 'getSessionData'])->name('recruitment.sessions.single-data');
    Route::get('/fptk/{fptkId}/sessions', [RecruitmentSessionController::class, 'getSessionsByFPTK'])->name('recruitment.sessions.by-fptk');
    Route::get('/candidate/{candidateId}/sessions', [RecruitmentSessionController::class, 'getSessionsByCandidate'])->name('recruitment.sessions.by-candidate');
});
```

### API Routes (routes/api.php)

```php
Route::group(['prefix' => 'recruitment-sessions'], function () {
    Route::get('/', [RecruitmentSessionController::class, 'apiIndex']);
    Route::post('/', [RecruitmentSessionController::class, 'apiStore']);
    Route::get('/{id}', [RecruitmentSessionController::class, 'apiShow']);
    Route::put('/{id}', [RecruitmentSessionController::class, 'apiUpdate']);
    Route::delete('/{id}', [RecruitmentSessionController::class, 'apiDestroy']);

    // Assessment updates via API
    Route::post('/{id}/update-cv-review', [RecruitmentSessionController::class, 'apiUpdateCvReview']);
    Route::post('/{id}/update-psikotes', [RecruitmentSessionController::class, 'apiUpdatePsikotes']);
    Route::post('/{id}/update-tes-teori', [RecruitmentSessionController::class, 'apiUpdateTesTeori']);
    Route::post('/{id}/update-interview', [RecruitmentSessionController::class, 'apiUpdateInterview']);
    Route::post('/{id}/update-offering', [RecruitmentSessionController::class, 'apiUpdateOffering']);
    Route::post('/{id}/update-mcu', [RecruitmentSessionController::class, 'apiUpdateMcu']);
    Route::post('/{id}/update-hiring', [RecruitmentSessionController::class, 'apiUpdateHiring']);
    Route::post('/{id}/close-request', [RecruitmentSessionController::class, 'apiCloseRequest']);
});
```

## View Components

### Main Views

1. **`recruitment/sessions/index.blade.php`**: List semua sessions dengan filtering
2. **`recruitment/sessions/show.blade.php`**: Tampilan FPTK/MPP dengan semua sessions
3. **`recruitment/sessions/show-session.blade.php`**: Detail individual session

### Partial Views

-   **`recruitment/sessions/partials/modals.blade.php`**: Semua modal form untuk assessment
-   **`recruitment/sessions/action.blade.php`**: Action buttons untuk DataTables

### UI Features

-   **Timeline View**: Horizontal timeline dengan status visual
-   **Progress Tracking**: Circular progress indicator
-   **Stage Locking**: Visual indicators untuk stage yang tidak bisa diedit
-   **Assessment Forms**: Modal forms untuk setiap jenis assessment
-   **Status Badges**: Color-coded status indicators
-   **DataTables Integration**: Server-side processing untuk large datasets

## Business Rules & Validation

### Session Creation Rules

1. **Source Validation**: Harus ada fptk_id ATAU mpp_detail_id
2. **Duplicate Prevention**: Kandidat tidak boleh ada di FPTK/MPP yang sama
3. **Status Validation**: FPTK harus 'approved', MPP harus 'active'
4. **Employment Type Logic**: Magang/harian langsung ke MCU stage

### Stage Advancement Rules

1. **Sequential Processing**: Stage harus completed sebelum advance
2. **Assessment Requirements**: Beberapa stage butuh assessment data
3. **Conditional Logic**: Tes Teori skip untuk posisi non-mechanic
4. **Failure Handling**: Stage fail → reject session & lock subsequent stages

### Data Integrity Rules

1. **Transaction Safety**: Semua updates menggunakan DB transactions
2. **Status Synchronization**: Candidate global_status sync dengan session status
3. **Letter Number Management**: Auto-assign dan mark as used
4. **Position Tracking**: Update FPTK positions_filled saat hire

## Services & Helper Classes

### RecruitmentSessionService

-   **createSession()**: Logic untuk membuat session baru
-   **advanceToNextStage()**: Logic untuk advance stage
-   **processAssessmentData()**: Process assessment submissions
-   **getSessionTimeline()**: Generate timeline data
-   **getProgressPercentage()**: Calculate progress percentage

### Business Logic Methods

```php
// Stage completion checking
public function isStageCompleted($stage)

// Progress calculation
public function calculateActualProgress()

// Next stage determination
public function getNextStageAttribute()

// Interview status checking
public function getInterviewStatus()
```

## Key Features

### 1. Dual Source Support (FPTK & MPP)

-   **FPTK**: Traditional recruitment requests
-   **MPP**: Manpower planning with quota management
-   **Unified Interface**: Single UI untuk kedua source type

### 2. Flexible Assessment Flow

-   **Conditional Stages**: Tes Teori optional berdasarkan position type
-   **Employment Type Logic**: Simplified flow untuk magang/harian
-   **Interview Types**: HR/User/Trainer berdasarkan requirements

### 3. Comprehensive Progress Tracking

-   **Real-time Progress**: Update otomatis berdasarkan stage completion
-   **Duration Tracking**: Monitor stage duration vs target
-   **Timeline Visualization**: Visual progress dengan status indicators

### 4. Advanced Security

-   **Stage Locking**: Prevent editing completed/failed stages
-   **Permission-based Access**: Granular permissions per operation
-   **Audit Trail**: Track semua changes dengan user info

### 5. Data Integrity

-   **Transaction Safety**: Rollback on failure
-   **Duplicate Prevention**: Unique constraints pada critical data
-   **Status Synchronization**: Auto-update related records

### 6. User Experience

-   **Visual Feedback**: Color-coded status, progress bars, icons
-   **Form Validation**: Client & server-side validation
-   **Error Handling**: Comprehensive error messages
-   **Responsive Design**: Mobile-friendly interface

## Integration Points

### External Systems

-   **Letter Number Service**: Generate official document numbers
-   **Employee Management**: Create employee records saat hiring
-   **Administration System**: Create admin records dengan NIK generation

### Internal Modules

-   **Candidate Management**: Update candidate global status
-   **FPTK Management**: Track positions filled
-   **MPP Management**: Manage quota fulfillment
-   **Document Management**: Handle recruitment documents

## Performance Considerations

### Database Optimization

-   **Eager Loading**: Relationships loaded on-demand
-   **Indexing**: Proper indexes pada foreign keys dan status columns
-   **Query Optimization**: Select only needed columns

### Caching Strategy

-   **View Caching**: Cache compiled Blade templates
-   **Config Caching**: Cache configuration data
-   **Route Caching**: Cache routes untuk production

### Monitoring & Logging

-   **Comprehensive Logging**: All operations logged dengan context
-   **Error Tracking**: Detailed error messages dengan stack traces
-   **Performance Monitoring**: Track query performance dan response times

## Future Enhancements

### Potential Improvements

1. **Batch Operations**: Bulk assessment updates
2. **Advanced Reporting**: Detailed analytics dan metrics
3. **Email Notifications**: Automated notifications untuk stage changes
4. **Document Management**: Integrated document upload/verification
5. **Mobile App**: Mobile interface untuk recruiters
6. **AI Integration**: Automated CV screening dan candidate matching

### Scalability Considerations

1. **Database Sharding**: Split large tables berdasarkan tahun/bulan
2. **Queue Processing**: Move heavy operations ke background jobs
3. **Caching Layer**: Redis untuk session data dan progress calculations
4. **API Rate Limiting**: Prevent abuse pada public endpoints

---

## Kesimpulan

Sistem Recruitment Sessions ARKA Hero adalah sistem yang sangat komprehensif dengan business logic yang kompleks. Sistem ini berhasil mengelola:

-   **Multi-source Recruitment**: FPTK dan MPP dengan logic berbeda
-   **Flexible Assessment Flow**: Conditional stages berdasarkan position type
-   **Comprehensive Tracking**: Progress, duration, dan status monitoring
-   **Data Integrity**: Transaction safety dan validation rules
-   **User Experience**: Intuitive UI dengan visual feedback
-   **Security**: Permission-based access dengan stage locking

Arsitektur sistem yang solid dengan separation of concerns yang baik membuat sistem ini maintainable dan scalable untuk kebutuhan rekrutmen perusahaan yang kompleks.
