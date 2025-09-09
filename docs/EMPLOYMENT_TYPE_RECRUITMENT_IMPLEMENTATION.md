# Employment Type Based Recruitment Implementation

## Overview

Implementasi sistem recruitment yang disesuaikan berdasarkan employment_type, khususnya untuk tipe **magang** dan **harian** yang hanya memerlukan tahapan **MCU** dan **Hiring & Onboarding**.

## Changes Made

### 1. RecruitmentSession Model (`app/Models/RecruitmentSession.php`)

#### New Method: `shouldSkipStagesForEmploymentType()`

```php
public function shouldSkipStagesForEmploymentType(): bool
{
    return in_array($this->fptk->employment_type, ['magang', 'harian']);
}
```

#### Updated Method: `getAdjustedStageProgress()`

-   **Magang & Harian**: Hanya 2 tahapan (MCU: 50%, Hiring: 100%)
-   **Regular Types**: Tetap menggunakan tahapan lengkap

#### Updated Method: `getNextStageAttribute()`

-   **Magang & Harian**: MCU → Hire → Onboarding
-   **Regular Types**: Tetap menggunakan flow normal

#### Updated Method: `calculateActualProgress()`

-   Menyesuaikan perhitungan progress berdasarkan employment type

### 2. RecruitmentWorkflowService (`app/Services/RecruitmentWorkflowService.php`)

#### Updated Method: `getStageBusinessRules()`

-   **Magang & Harian**:
    -   MCU: passing_score 70 (lebih rendah), auto_advance true
    -   Hire: required_fields [start_date, employment_type], auto_advance false
-   **Regular Types**: Tetap menggunakan rules normal

### 3. RecruitmentSessionService (`app/Services/RecruitmentSessionService.php`)

#### Updated Method: `getSessionTimeline()`

-   Menyesuaikan stages yang ditampilkan berdasarkan employment type
-   **Magang & Harian**: Hanya menampilkan MCU dan Hire stages

### 4. View Updates (`resources/views/recruitment/sessions/show.blade.php`)

#### Table Headers

-   **Magang & Harian**: Hanya menampilkan kolom MCU dan Hiring & Onboarding
-   **Regular Types**: Menampilkan semua kolom tahapan

#### Stage Logic

-   Menyesuaikan array stages berdasarkan employment type
-   Menyesuaikan colspan untuk empty state

#### Info Messages

-   Menampilkan pesan khusus untuk magang/harian: "Hanya tahapan MCU dan Hiring & Onboarding"

## Test Results

### Magang Employment Type

```
FPTK Magang found: FPTK-2025-0005
Employment Type: magang
Should skip stages for employment type: Yes
Adjusted stage progress: {"mcu":50,"hire":100}
Next stage: hire
```

### Harian Employment Type

```
FPTK Harian found: FPTK-2025-0015
Employment Type: harian
Should skip stages for employment type: Yes
Adjusted stage progress: {"mcu":50,"hire":100}
Next stage: hire
```

### Regular Employment Type (PKWTT)

```
FPTK PKWTT found: FPTK-2025-0003
Employment Type: pkwtt
Should skip stages for employment type: No
Adjusted stage progress: {"cv_review":14.3,"psikotes":28.6,"tes_teori":42.9,"interview":57.1,"offering":71.4,"mcu":85.7,"hire":100}
Next stage: psikotes
```

### Business Rules Test

```
MCU rules for magang: {"required_fields":["overall_health"],"passing_score":70,"auto_advance":true}
Hire rules for magang: {"required_fields":["start_date","employment_type"],"auto_advance":false}
MCU rules for PKWTT: {"required_fields":["overall_health"],"passing_score":80,"auto_advance":true}
```

## Benefits

1. **Efisiensi Proses**: Magang dan harian tidak perlu melalui tahapan panjang
2. **Fleksibilitas**: Sistem dapat menangani berbagai employment type dengan flow yang berbeda
3. **User Experience**: UI menampilkan hanya tahapan yang relevan
4. **Maintainability**: Kode terstruktur dan mudah dipelihara

## Future Enhancements

1. **Configurable Stages**: Membuat stages dapat dikonfigurasi per employment type
2. **Dynamic Rules**: Business rules dapat disesuaikan melalui admin panel
3. **Analytics**: Tracking khusus untuk employment type yang berbeda
4. **Notifications**: Notifikasi yang disesuaikan dengan employment type

## Files Modified

-   `app/Models/RecruitmentSession.php`
-   `app/Services/RecruitmentWorkflowService.php`
-   `app/Services/RecruitmentSessionService.php`
-   `resources/views/recruitment/sessions/show.blade.php`

## Database Impact

Tidak ada perubahan struktur database. Implementasi menggunakan field `employment_type` yang sudah ada di tabel `recruitment_requests`.
