# 📌 HR System Implementation Guide

Dokumen ini menjelaskan rancangan implementasi **Sistem Cuti** dan **Sistem Roster Kerja**. Kedua sistem ini berbeda modul namun saling terhubung, sehingga mendukung kebutuhan HR di proyek tambang maupun head office.

---

## 1. Tujuan Sistem

### Sistem Cuti

- Mengelola hak cuti karyawan (tahunan, panjang/long service leave, izin lain).
- Menghitung saldo cuti, akumulasi, dan eligibility berdasarkan masa kerja.
- Mendukung approval workflow dengan multi-level approval.
- Menyediakan laporan cuti (by employee, project, type).

### Sistem Roster Kerja

- Menentukan pola kerja & off karyawan berdasarkan project/level.
- Menangani balancing jika cuti diambil di tengah siklus.
- Menyediakan histori siklus kerja per karyawan.
- Mengintegrasikan dengan sistem cuti (leave requests → roster adjustments).

---

## 2. Struktur Database

### 2.1. Sistem Cuti

#### `leave_types`

```sql
id (PK)
name (varchar) -- Annual, LSL, Unpaid, dll
code (varchar)
category (varchar)
default_days (int)
eligibility_rule (json / varchar)
is_active (boolean)
```

#### `leave_entitlements`

```sql
id (PK)
employee_id (FK)
leave_type_id (FK)
year (year)
entitled_days (int)
taken_days (int)
remaining_days (int)
accumulated_days (int)
outstanding_days (int)
is_paid_out (boolean)
created_at, updated_at
```

#### `leave_requests`

```sql
id (PK)
employee_id (FK)
administration_id (FK)
leave_type_id (FK)
start_date (date)
end_date (date)
back_to_work_date (date)
leave_period (varchar) -- khusus cuti panjang
reason (text)
status (enum: pending, approved, rejected)
created_at, updated_at
```

#### `leave_calculations` (opsional)

```sql
id (PK)
leave_request_id (FK)
annual_eligibility (int)
lsl_eligibility (int)
outstanding_lsl (int)
accumulated_leave (int)
entitlement (int)
less_this_leave (int)
paid_out (int)
balance (int)
created_at, updated_at
```

#### Approval Tables (existing)

- `approval_stages`
- `approval_stage_details`
- `approval_plans`

> **Catatan**: `leave_requests` akan masuk ke `approval_plans` dengan `document_type = 'leave_request'`.

---

### 2.2. Sistem Roster

#### `roster_templates`

```sql
id (PK)
project_id (FK)
level_id (FK)
work_days (int)
off_days_local (int)
off_days_nonlocal (int)
cycle_length (int)
effective_date (date)
```

#### `rosters`

```sql
id (PK)
employee_id (FK)
administration_id (FK)
roster_template_id (FK)
start_date (date)
end_date (date)
cycle_no (int)
adjusted_days (int)
```

#### `roster_adjustments`

```sql
id (PK)
roster_id (FK)
leave_request_id (FK)
adjustment_type (enum: +days, -days)
adjusted_value (int)
reason (varchar)
created_at, updated_at
```

#### `roster_histories`

```sql
id (PK)
roster_id (FK)
cycle_no (int)
work_days_actual (int)
off_days_actual (int)
remarks (text)
created_at, updated_at
```

---

## 3. Aturan Bisnis

### Cuti Tahunan

- Hanya dapat digunakan setelah 1 tahun masa kerja.
- 12 hari kerja per tahun (HO/BO/APS/021/025).
- Tidak bisa carry forward kecuali level manager.

### Cuti Panjang (Long Service Leave)

- Eligible setelah 5 tahun (staff) atau 6 tahun (non-staff).
- Hak cuti 50 hari setiap periode.
- Periode pertama: 40 hari dapat diambil/diuangkan, 10 hari dideposit.
- Periode berikutnya: full 50 hari dapat diambil.
- Sisa cuti diakumulasi ke periode berikut.

### Roster Project (017 & 022)

- SPT/PM = 42 kerja / 14 off
- SPV = 56 kerja / 14 off
- FM = 63 kerja / 14 off
- NS = 70 kerja / 15 off (non lokal), 14 off (lokal)
- NS dapat cuti hanya setelah 2x PKWT / 12 bulan.

### Balancing Roster

- Jika cuti diambil sebelum siklus selesai → work days dipotong.
- Siklus berikutnya ditambah untuk kompensasi.

---

## 4. Integrasi Cuti & Roster

- **leave\_requests** → update ke **leave\_entitlements**.
- Jika karyawan di project roster-based:
  - leave\_requests → insert ke `roster_adjustments`.
  - `rosters.adjusted_days` diupdate.
- Approval tetap lewat `approval_plans`.

---

## 5. Contoh Kasus

### Case 1: Annual Leave HO Staff

- Masa kerja 18 bulan → eligible annual leave.
- Ajukan cuti 5 hari.
- Sistem cek `leave_entitlements` → 12 - 5 = 7 hari remaining.
- Tidak mempengaruhi roster.

### Case 2: Roster NS Project 017

- Siklus = 70 kerja + 15 off.
- Karyawan ambil cuti 3 hari di hari ke-60.
- `roster_adjustments`: -3 hari.
- Cycle 1 actual = 67 hari kerja.
- Cycle 2 diperpanjang → 73 hari.

### Case 3: Long Service Leave Staff

- Sudah 5 tahun → entitled 50 hari (40 dapat diambil, 10 deposit).
- Ajukan cuti panjang 20 hari.
- `leave_calculations`:
  - Entitlement = 50, Less = 20, Balance = 30 (10 deposit, 20 remaining).
- Approval berjalan sesuai workflow.

---

## 6. Laporan

- **Leave Reports**: saldo cuti, pemakaian, akumulasi.
- **Roster Reports**: planned vs actual, adjustment karena cuti.
- **Integrated Reports**: total kerja, off, cuti.

---

## 7. Kesimpulan

- Sistem **Cuti** fokus pada hak cuti & approval.
- Sistem **Roster** fokus pada siklus kerja per project.
- Hubungan keduanya ada pada **leave\_requests ↔ roster\_adjustments**.
- Desain ini fleksibel untuk mendukung project site (017, 022) dan HO/BO.

