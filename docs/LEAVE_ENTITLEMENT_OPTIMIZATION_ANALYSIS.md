# Leave Entitlements Table Optimization Analysis

## Executive Summary

Analisis struktur tabel `leave_entitlements` menunjukkan beberapa kolom yang **redundan**, **tidak digunakan**, atau **over-engineered**. Rekomendasi optimasi untuk meningkatkan maintainability dan mengurangi kompleksitas.

---

## Struktur Table Saat Ini

```sql
leave_entitlements
├── id (bigint, PK)
├── employee_id (char(36), FK)
├── leave_type_id (bigint, FK)
├── period_start (date)
├── period_end (date)
├── entitled_days (int, default: 0)
├── withdrawable_days (int, default: 0)  ⚠️ REDUNDANT
├── deposit_days (int, default: 0)       ⚠️ UNDERUTILIZED
├── carried_over (int, default: 0)       ⚠️ UNUSED
├── taken_days (int, default: 0)
├── remaining_days (int, default: 0)     ⚠️ CALCULATED FIELD
├── created_at (timestamp)
└── updated_at (timestamp)
```

---

## Analisis Detail Kolom

### 1. `withdrawable_days` - **REDUNDANT** ⚠️

**Status**: Selalu sama dengan `entitled_days` di semua record (374/374 = 100%)

**Bukti**:
```sql
SELECT COUNT(*) FROM leave_entitlements 
WHERE withdrawable_days != entitled_days;
-- Result: 0
```

**Penggunaan di Code**:
- `LeaveEntitlementController.php:635`: `'withdrawable_days' => $entitlementDays`
- `LeaveEntitlementController.php:873`: `'withdrawable_days' => $entitledDays`
- `LeaveRequestController.php:945`: `remaining_days = withdrawable_days - taken_days`
- `LeaveEntitlement.php:51`: `remaining_days = withdrawable_days - taken_days`

**Masalah**:
- Tidak ada logika bisnis yang mengubah `withdrawable_days` secara berbeda dari `entitled_days`
- Menambah kompleksitas tanpa manfaat
- Menyebabkan potensi inconsistency jika salah satu field diupdate tanpa yang lain

**Rekomendasi**: **HAPUS** kolom ini, gunakan `entitled_days` saja

---

### 2. `remaining_days` - **CALCULATED FIELD** ⚠️

**Status**: Selalu dihitung sebagai `withdrawable_days - taken_days` (atau `entitled_days - taken_days`)

**Bukti**:
```sql
SELECT COUNT(*) FROM leave_entitlements 
WHERE remaining_days != (withdrawable_days - taken_days);
-- Result: 0
```

**Penggunaan di Code**:
- `LeaveEntitlement.php:51`: `calculateRemainingDays()` method
- `LeaveRequestController.php:945`: `$entitlement->remaining_days = $entitlement->withdrawable_days - $entitlement->taken_days`
- Selalu di-update setelah `taken_days` berubah

**Masalah**:
- Stored field yang sebenarnya adalah computed value
- Potensi data inconsistency jika calculation tidak di-trigger
- Membutuhkan manual update setiap kali `taken_days` berubah

**Rekomendasi**: **HAPUS** dari database, buat sebagai **accessor** di model:
```php
public function getRemainingDaysAttribute()
{
    return max(0, $this->entitled_days - $this->taken_days);
}
```

---

### 3. `deposit_days` - **UNDERUTILIZED** ⚠️

**Status**: Hanya digunakan di 20 dari 374 record (5.3%)

**Bukti**:
```sql
SELECT COUNT(*) FROM leave_entitlements WHERE deposit_days > 0;
-- Result: 20
```

**Penggunaan di Code**:
- `LeaveEntitlementController.php:637`: `'deposit_days' => $leaveType->getDepositDays()`
- `LeaveType.php`: Method `getDepositDays()` untuk LSL first period (40/10 split)
- Hanya relevan untuk LSL (Long Service Leave) first period

**Masalah**:
- Hampir tidak pernah digunakan (94.7% record = 0)
- Logic deposit days sepertinya tidak pernah di-update secara dinamis
- Mungkin lebih tepat disimpan di level `leave_type` atau `leave_request` untuk LSL

**Rekomendasi**: 
- **OPSI A**: Hapus dari `leave_entitlements`, simpan di `leave_requests` untuk LSL yang di-cashout
- **OPSI B**: Pertahankan jika ada rencana implementasi deposit days untuk leave types lain

**Keputusan**: Tergantung business requirement untuk LSL deposit mechanism

---

### 4. `carried_over` - **UNUSED** ❌

**Status**: Tidak pernah digunakan (0 dari 374 record = 0%)

**Bukti**:
```sql
SELECT COUNT(*) FROM leave_entitlements WHERE carried_over > 0;
-- Result: 0
```

**Penggunaan di Code**:
- `LeaveEntitlementController.php:638`: `'carried_over' => 0`
- `LeaveEntitlementController.php:876`: `'carried_over' => 0`
- Selalu diset ke 0, tidak pernah di-update

**Masalah**:
- Dead code - tidak ada business logic yang menggunakan field ini
- Menambah kompleksitas tanpa manfaat
- Tidak ada fitur "carry over" leave yang diimplementasikan

**Rekomendasi**: **HAPUS** kolom ini

---

## Rekomendasi Optimasi

### Priority 1: High Impact, Low Risk

#### 1. Hapus `withdrawable_days`
- **Impact**: Menghilangkan redundancy, mengurangi kompleksitas
- **Risk**: Low (tidak ada perbedaan nilai dengan `entitled_days`)
- **Action**: 
  - Update semua query yang menggunakan `withdrawable_days` → `entitled_days`
  - Update `remaining_days` calculation: `entitled_days - taken_days`
  - Migration untuk drop column

#### 2. Hapus `remaining_days` dari database, buat sebagai accessor
- **Impact**: Menghilangkan calculated field, mencegah inconsistency
- **Risk**: Low (selalu dihitung ulang)
- **Action**:
  - Buat accessor di model `LeaveEntitlement`
  - Update semua query yang SELECT `remaining_days` → gunakan accessor
  - Migration untuk drop column

#### 3. Hapus `carried_over`
- **Impact**: Menghilangkan unused field
- **Risk**: Very Low (tidak pernah digunakan)
- **Action**:
  - Migration untuk drop column
  - Hapus dari fillable array di model

### Priority 2: Medium Impact, Need Business Decision

#### 4. Evaluasi `deposit_days`
- **Impact**: Menghilangkan underutilized field (jika tidak diperlukan)
- **Risk**: Medium (perlu konfirmasi business requirement untuk LSL)
- **Action**:
  - Diskusikan dengan stakeholder: apakah deposit days masih diperlukan?
  - Jika ya: pertahankan, tapi dokumentasikan usage
  - Jika tidak: hapus dan simpan logic di `leave_requests` untuk LSL cashout

---

## Struktur Table Setelah Optimasi

```sql
leave_entitlements
├── id (bigint, PK)
├── employee_id (char(36), FK)
├── leave_type_id (bigint, FK)
├── period_start (date)
├── period_end (date)
├── entitled_days (int, default: 0)      ✅ SINGLE SOURCE OF TRUTH
├── taken_days (int, default: 0)         ✅ ACTUAL USAGE
├── deposit_days (int, default: 0)       ⚠️ EVALUATE (LSL only)
├── created_at (timestamp)
└── updated_at (timestamp)

-- Computed (via Accessor):
-- remaining_days = max(0, entitled_days - taken_days)
```

**Pengurangan**: Dari 12 kolom → 10 kolom (atau 9 jika deposit_days dihapus)

---

## Migration Plan

### Phase 1: Code Updates
1. Update `LeaveEntitlement` model:
   - Hapus `withdrawable_days`, `carried_over` dari fillable
   - Tambah accessor `getRemainingDaysAttribute()`
   - Update `calculateRemainingDays()` method

2. Update `LeaveEntitlementController`:
   - Ganti semua `withdrawable_days` → `entitled_days`
   - Update `remaining_days` calculation
   - Hapus `carried_over` assignments

3. Update `LeaveRequestController`:
   - Update `remaining_days` calculation logic
   - Gunakan accessor untuk `remaining_days`

### Phase 2: Database Migration
```php
Schema::table('leave_entitlements', function (Blueprint $table) {
    $table->dropColumn(['withdrawable_days', 'remaining_days', 'carried_over']);
});
```

### Phase 3: Data Migration (if needed)
```sql
-- Backup existing data
CREATE TABLE leave_entitlements_backup AS SELECT * FROM leave_entitlements;

-- Verify no data loss
SELECT COUNT(*) FROM leave_entitlements_backup;
SELECT COUNT(*) FROM leave_entitlements;
```

---

## Benefits

1. **Simplified Data Model**: Mengurangi kompleksitas dan confusion
2. **Data Integrity**: Menghilangkan potensi inconsistency (calculated fields)
3. **Performance**: Sedikit lebih cepat (less columns to read/write)
4. **Maintainability**: Code lebih mudah di-maintain dan di-debug
5. **Clarity**: Single source of truth untuk entitlement days

---

## Risks & Mitigation

### Risk 1: Breaking Changes
- **Mitigation**: Comprehensive testing sebelum deployment
- **Rollback Plan**: Keep backup table, migration down script ready

### Risk 2: Accessor Performance
- **Mitigation**: Accessor sangat ringan (simple calculation)
- **Alternative**: Jika perlu, gunakan DB view atau materialized column

### Risk 3: Deposit Days Requirement
- **Mitigation**: Validasi dengan business stakeholder sebelum hapus
- **Alternative**: Pertahankan jika ada rencana future usage

---

## Conclusion

**Recommended Actions**:
1. ✅ **Hapus `withdrawable_days`** (redundant)
2. ✅ **Hapus `remaining_days`** dari DB, buat accessor (calculated)
3. ✅ **Hapus `carried_over`** (unused)
4. ⚠️ **Evaluasi `deposit_days`** dengan business (underutilized tapi mungkin diperlukan)

**Estimated Impact**: 
- Code reduction: ~50-100 lines
- Database size: ~15% reduction per row
- Complexity: Significantly reduced
- Maintainability: Improved

