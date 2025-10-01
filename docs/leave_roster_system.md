# 📌 HR System Implementation Guide

Dokumen ini menjelaskan rancangan implementasi **Sistem Cuti** dan **Sistem Roster Kerja**. Kedua sistem ini berbeda modul namun saling terhubung, sehingga mendukung kebutuhan HR di proyek tambang maupun head office.

---

## 1. Tujuan Sistem

### Sistem Cuti

-   Mengelola hak cuti karyawan (tahunan, panjang/long service leave, izin lain).
-   Menghitung saldo cuti, akumulasi, dan eligibility berdasarkan masa kerja.
-   Mendukung approval workflow dengan multi-level approval.
-   Menyediakan laporan cuti (by employee, project, type).

### Sistem Roster Kerja

-   Menentukan pola kerja & off karyawan berdasarkan project/level.
-   Menangani balancing jika cuti diambil di tengah siklus.
-   Menyediakan histori siklus kerja per karyawan.
-   Mengintegrasikan dengan sistem cuti (leave requests → roster adjustments).

---

## 2. Struktur Database

### 2.1. Sistem Cuti

#### `leave_types`

| Field                | Keterangan                                   |
| -------------------- | -------------------------------------------- |
| id (PK)              | Primary Key                                  |
| name                 | Annual, LSL Staff, LSL Non Staff, LWP, LWOP  |
| code                 | AL, LSL, LWP, LWOP                           |
| category             | annual / special / unpaid                    |
| default_days         | Default entitlement                          |
| eligible_after_years | Syarat minimal masa kerja                    |
| deposit_days_first   | Khusus LSL: 10 hari di periode pertama       |
| carry_over           | Bisa diakumulasi ke periode berikut (yes/no) |
| remarks              | Catatan khusus                               |

#### `leave_entitlements`

| Field              | Keterangan                                                        |
| ------------------ | ----------------------------------------------------------------- |
| id (PK)            | Primary Key                                                       |
| employee_id (FK)   | Relasi ke karyawan                                                |
| leave_type_id (FK) | Relasi ke leave_types                                             |
| period_start       | Tanggal mulai entitlement (mis. 2025-01-01)                       |
| period_end         | Tanggal akhir entitlement (mis. 2029-12-31 untuk LSL)             |
| entitled_days      | Total hak cuti di periode                                         |
| withdrawable_days  | Jumlah cuti yang bisa diambil/ diuangkan (LSL: 40 first, 50 next) |
| deposit_days       | Khusus periode pertama LSL (10 hari)                              |
| carried_over       | Sisa dari periode lama                                            |
| taken_days         | Total cuti diambil                                                |
| remaining_days     | Saldo akhir                                                       |

#### `leave_requests`

| Field              | Keterangan                            |
| ------------------ | ------------------------------------- |
| id (PK)            | Primary Key                           |
| employee_id (FK)   | Siapa yang cuti                       |
| leave_type_id (FK) | Jenis cuti                            |
| start_date         | Hari pertama cuti                     |
| end_date           | Hari terakhir cuti                    |
| back_to_work_date  | Khusus LSL (tanggal kembali)          |
| reason             | Alasan cuti                           |
| total_days         | Lama cuti                             |
| status             | pending/approved/rejected             |
| leave_period       | Untuk period cuti tahunan dan panjang |
| requested_at       | Kapan diajukan                        |

#### `leave_calculations` (opsional)

| Field              | Isi (contoh LSL)     |
| ------------------ | -------------------- |
| id (PK)            | Primary Key          |
| leave_request_id   | FK ke leave_requests |
| annual_eligibility | 12                   |
| lsl_eligibility    | 50                   |
| outstanding_lsl    | 5                    |
| accumulated_leave  | 28                   |
| entitlement        | 50                   |
| less_this_leave    | 15                   |
| paid_out           | 0                    |
| balance            | 35                   |

#### Approval Tables

-   `approval_stages`
-   `approval_stage_details`
-   `approval_plans`

> **Catatan**: `leave_requests` akan masuk ke `approval_plans` dengan `document_type = 'leave_request'`.

---

### 2.2. Sistem Roster

#### `roster_templates`

| Field             | Keterangan                      |
| ----------------- | ------------------------------- |
| id (PK)           | Primary Key                     |
| project_id (FK)   | Relasi project                  |
| level_id (FK)     | Relasi level                    |
| work_days         | Jumlah hari kerja per siklus    |
| off_days_local    | Jumlah off days untuk lokal     |
| off_days_nonlocal | Jumlah off days untuk non lokal |
| cycle_length      | Panjang siklus (hari)           |
| effective_date    | Tanggal berlaku                 |

#### `rosters`

| Field              | Keterangan              |
| ------------------ | ----------------------- |
| id (PK)            | Primary Key             |
| employee_id (FK)   | Relasi ke karyawan      |
| roster_template_id | FK ke roster_templates  |
| start_date         | Mulai siklus            |
| end_date           | Akhir siklus            |
| cycle_no           | Nomor siklus            |
| adjusted_days      | Penyesuaian karena cuti |

#### `roster_adjustments`

| Field            | Keterangan                    |
| ---------------- | ----------------------------- |
| id (PK)          | Primary Key                   |
| roster_id (FK)   | Relasi ke roster              |
| leave_request_id | Relasi ke leave_requests      |
| adjustment_type  | +days / -days                 |
| adjusted_value   | Nilai penyesuaian             |
| reason           | Alasan adjustment (mis. cuti) |

#### `roster_histories`

| Field            | Keterangan           |
| ---------------- | -------------------- |
| id (PK)          | Primary Key          |
| roster_id (FK)   | Relasi ke roster     |
| cycle_no         | Nomor siklus         |
| work_days_actual | Realisasi hari kerja |
| off_days_actual  | Realisasi hari off   |
| remarks          | Catatan              |

---

## 3. Aturan Bisnis

### Annual Leave

-   Baru aktif setelah 1 tahun masa kerja.
-   Jatah 12 hari/tahun.
-   Tidak bisa carry forward kecuali policy khusus (misalnya level manager).

### Long Service Leave (LSL)

-   Staff: eligible setelah 5 tahun.
-   Non-staff: eligible setelah 6 tahun.
-   Periode pertama: 40 hari withdrawable + 10 hari deposit.
-   Periode berikutnya: full 50 hari withdrawable.
-   Carry over: sisa hari withdrawable ditambahkan ke periode berikut.

### Leave With Pay (izin khusus)

-   Marriage = 3 hari
-   Child birth = 2 hari
-   Sick (dokter) = sesuai rekomendasi
-   Government duty = sesuai dokumen
-   Tidak mengurangi jatah annual leave.

### Leave Without Pay

-   Bisa diajukan dengan alasan apapun.
-   Tidak mengurangi saldo cuti.
-   Potong payroll (integrasi ke payroll system).

### Balancing Roster

-   Jika cuti diambil sebelum siklus selesai → work days dipotong.
-   Siklus berikutnya ditambah untuk kompensasi.

---

## 4. Integrasi Cuti & Roster

-   `leave_requests` → update ke `leave_entitlements`.
-   Jika karyawan di project roster-based:

    -   `leave_requests` → insert ke `roster_adjustments`.
    -   `rosters.adjusted_days` diupdate.

-   Approval tetap lewat `approval_plans`.

---

## 5. Contoh Kasus

### Case 1: Annual Leave HO Staff

-   Masa kerja 18 bulan → eligible annual leave.
-   Ajukan cuti 5 hari.
-   Sistem cek `leave_entitlements` → 12 - 5 = 7 hari remaining.
-   Tidak mempengaruhi roster.

### Case 2: Roster NS Project 017

-   Siklus = 70 kerja + 15 off.
-   Karyawan ambil cuti 3 hari di hari ke-60.
-   `roster_adjustments`: -3 hari.
-   Cycle 1 actual = 67 hari kerja.
-   Cycle 2 diperpanjang → 73 hari.

### Case 3: Long Service Leave Staff

-   Sudah 5 tahun → entitled 50 hari (40 dapat diambil, 10 deposit).
-   Ajukan cuti panjang 20 hari.
-   `leave_calculations`:

    -   Entitlement = 50, Less = 20, Balance = 30 (10 deposit, 20 remaining).

-   Approval berjalan sesuai workflow.

---

## 6. Laporan

-   **Leave Reports**: saldo cuti, pemakaian, akumulasi.
-   **Roster Reports**: planned vs actual, adjustment karena cuti.
-   **Integrated Reports**: total kerja, off, cuti.

---

## 7. Kesimpulan

-   Sistem **Cuti** fokus pada hak cuti & approval.
-   Sistem **Roster** fokus pada siklus kerja per project.
-   Hubungan keduanya ada pada **leave_requests ↔ roster_adjustments**.
-   Desain ini fleksibel untuk mendukung project site dan head office.

📋 FASE IMPLEMENTASI SISTEM CUTI & ROSTER KERJA

PHASE 1: Database Schema Implementation
Estimasi: 2-3 hari
1.1. Create Migration Files
create_leave_types_table.php
create_leave_entitlements_table.php
create_leave_requests_table.php
create_leave_calculations_table.php
create_roster_templates_table.php
create_rosters_table.php
create_roster_adjustments_table.php
create_roster_histories_table.php
1.2. Database Relationships
Foreign key constraints
Index optimization
Data seeding untuk leave_types (Annual, LSL, Unpaid, dll)

PHASE 2: Model Implementation
Estimasi: 3-4 hari
2.1. Core Models
LeaveType.php - Jenis cuti dengan eligibility rules
LeaveEntitlement.php - Hak cuti per karyawan per tahun
LeaveRequest.php - Permintaan cuti
LeaveCalculation.php - Kalkulasi cuti (opsional)
2.2. Roster Models
RosterTemplate.php - Template roster per project/level
Roster.php - Roster aktual karyawan
RosterAdjustment.php - Penyesuaian roster karena cuti
RosterHistory.php - Histori siklus kerja
2.3. Business Logic Methods
Leave eligibility calculation
Roster balancing logic
Leave entitlement updates

PHASE 3: Controller Implementation
Estimasi: 4-5 hari
3.1. Leave Management Controllers
LeaveRequestController.php - CRUD permintaan cuti
LeaveEntitlementController.php - Kelola hak cuti
LeaveReportController.php - Laporan cuti
3.2. Roster Management Controllers
RosterController.php - Kelola roster karyawan
RosterTemplateController.php - Kelola template roster
RosterReportController.php - Laporan roster
3.3. Integration Controllers
LeaveRosterIntegrationController.php - Integrasi cuti-roster

PHASE 4: View Implementation
Estimasi: 5-6 hari
4.1. Leave Management Views
leave-requests/index.blade.php - Daftar permintaan cuti
leave-requests/create.blade.php - Form pengajuan cuti
leave-requests/show.blade.php - Detail permintaan cuti
leave-requests/approve.blade.php - Form approval cuti
4.2. Roster Management Views
rosters/index.blade.php - Daftar roster
rosters/create.blade.php - Form buat roster
rosters/balance.blade.php - Form balancing roster
4.3. Report Views
reports/leave-summary.blade.php - Ringkasan cuti
reports/roster-summary.blade.php - Ringkasan roster

PHASE 5: Approval Integration
Estimasi: 2-3 hari
5.1. Extend Existing Approval System
Integrate leave_requests dengan approval_plans
Update approval_stage_details untuk leave requests
Modify approval workflow untuk cuti
5.2. Approval Views
Extend existing approval views untuk cuti
Add leave-specific approval logic

PHASE 6: Business Logic Implementation
Estimasi: 6-7 hari
6.1. Leave Calculation Engine
Annual leave eligibility (12 hari setelah 1 tahun)
Long Service Leave calculation (50 hari setelah 5-6 tahun)
Leave balance tracking
Carry forward rules
6.2. Roster Balancing System
Work/off days calculation
Leave impact on roster cycles
Compensation logic untuk cuti di tengah siklus
6.3. Validation Rules
Leave request validation
Roster conflict detection
Business rule enforcement

PHASE 7: Reporting System
Estimasi: 3-4 hari
7.1. Leave Reports
Employee leave balance
Leave usage by project/type
Leave accumulation reports
7.2. Roster Reports
Planned vs actual work days
Roster adjustments due to leave
Cycle completion reports
7.3. Integrated Reports
Total work/off/leave summary
Project-wise leave and roster analysis

PHASE 8: Testing & Validation
Estimasi: 3-4 hari
8.1. Unit Testing
Model business logic testing
Calculation accuracy testing
8.2. Integration Testing
Leave-roster integration testing
Approval workflow testing
8.3. User Acceptance Testing
Test semua business rules
Validate dengan user requirements

🎯 PRIORITAS IMPLEMENTASI
High Priority (Must Have)
Phase 1-2: Database & Models
Phase 3: Basic Controllers
Phase 6: Core Business Logic
Phase 5: Approval Integration
Medium Priority (Should Have)
Phase 4: Basic Views
Phase 7: Essential Reports
Low Priority (Nice to Have)
Phase 8: Advanced Testing
Phase 7: Advanced Reports
