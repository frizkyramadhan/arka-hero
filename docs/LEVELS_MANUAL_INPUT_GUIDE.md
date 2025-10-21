# 📋 PANDUAN INPUT MANUAL DATA LEVELS

Setelah menjalankan migration, Anda perlu mengisi data roster configuration untuk setiap level yang menggunakan sistem roster.

## 🎯 **Level yang Perlu Diisi Data Roster**

Berdasarkan analisis sistem, berikut adalah level yang menggunakan roster:

### **1. Manager**

```sql
UPDATE levels SET
    off_days = 14,
    work_days = 42,
    cycle_length = 56
WHERE name = 'Manager';
```

**Pattern:** 6/2 (6 minggu kerja, 2 minggu off)

### **2. Superintendent**

```sql
UPDATE levels SET
    off_days = 14,
    work_days = 42,
    cycle_length = 56
WHERE name = 'Superintendent';
```

**Pattern:** 6/2 (6 minggu kerja, 2 minggu off)

### **3. Supervisor**

```sql
UPDATE levels SET
    off_days = 14,
    work_days = 56,
    cycle_length = 70
WHERE name = 'Supervisor';
```

**Pattern:** 8/2 (8 minggu kerja, 2 minggu off)

### **4. Foreman/Officer**

```sql
UPDATE levels SET
    off_days = 14,
    work_days = 63,
    cycle_length = 77
WHERE name = 'Foreman/Officer';
```

**Pattern:** 9/2 (9 minggu kerja, 2 minggu off)

### **5. Non Staff-Non Skill**

```sql
UPDATE levels SET
    off_days = 14,
    work_days = 70,
    cycle_length = 84
WHERE name = 'Non Staff-Non Skill';
```

**Pattern:** 10/2 (10 minggu kerja, 2 minggu off)

## 🚫 **Level yang TIDAK Perlu Diisi**

Level berikut **TIDAK** menggunakan sistem roster, biarkan kolom `work_days`, `off_days`, dan `cycle_length` tetap `NULL`:

-   **Director** - Tidak menggunakan roster system

## 📊 **Script SQL Lengkap**

```sql
-- Manager (6/2 pattern)
UPDATE levels SET
    off_days = 14,
    work_days = 42,
    cycle_length = 56
WHERE name = 'Manager';

-- Superintendent (6/2 pattern)
UPDATE levels SET
    off_days = 14,
    work_days = 42,
    cycle_length = 56
WHERE name = 'Superintendent';

-- Supervisor (8/2 pattern)
UPDATE levels SET
    off_days = 14,
    work_days = 56,
    cycle_length = 70
WHERE name = 'Supervisor';

-- Foreman/Officer (9/2 pattern)
UPDATE levels SET
    off_days = 14,
    work_days = 63,
    cycle_length = 77
WHERE name = 'Foreman/Officer';

-- Non Staff-Non Skill (10/2 pattern)
UPDATE levels SET
    off_days = 14,
    work_days = 70,
    cycle_length = 84
WHERE name = 'Non Staff-Non Skill';
```

## ✅ **Verifikasi Data**

Setelah mengisi data, verifikasi dengan query berikut:

```sql
-- Cek level yang sudah dikonfigurasi
SELECT name, work_days, off_days, cycle_length,
       CONCAT(work_days/7, '/', off_days/7) as pattern
FROM levels
WHERE work_days IS NOT NULL
ORDER BY work_days;

-- Hasil yang diharapkan:
-- Manager: 42, 14, 56, 6/2
-- Superintendent: 42, 14, 56, 6/2
-- Supervisor: 56, 14, 70, 8/2
-- Foreman/Officer: 63, 14, 77, 9/2
-- Non Staff-Non Skill: 70, 14, 84, 10/2
```

## 🔄 **Setelah Input Data**

1. **Jalankan migration:**

    ```bash
    php artisan migrate
    ```

2. **Input data levels** menggunakan SQL di atas

3. **Test sistem roster** dengan:
    - Buka halaman roster management
    - Pilih project roster (017C atau 022C)
    - Pastikan karyawan muncul dengan roster yang benar

## ⚠️ **Catatan Penting**

-   **Semua level roster memiliki 14 hari off** (konsisten)
-   **Pattern berbeda hanya pada work_days** (42, 56, 63, 70)
-   **cycle_length = work_days + off_days**
-   **Level Director tidak perlu dikonfigurasi** (biarkan NULL)

## 🎯 **Hasil Akhir**

Setelah input data ini, sistem akan:

-   ✅ Auto-create roster untuk karyawan di project roster
-   ✅ Auto-adjust roster saat karyawan pindah project/level
-   ✅ Menggunakan pattern yang benar berdasarkan level
-   ✅ Tidak perlu manual intervention untuk roster management
