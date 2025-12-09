# Analisis Export/Import Karyawan - ARKA HERO

## Executive Summary

Sistem export/import karyawan saat ini menggunakan struktur **14 sheet terpisah** yang menyebabkan kompleksitas tinggi dalam pemrosesan data. Analisa ini mengidentifikasi masalah utama dan memberikan rekomendasi solusi untuk meningkatkan kemudahan penggunaan dan efisiensi.

**Rekomendasi Utama**: Mengadopsi struktur **Master-Detail dengan Pivot Key** yang mengurangi dari 14 sheet menjadi 3-5 sheet, dengan enhancement template-based untuk user experience yang lebih baik.

---

## 1. Current State Analysis

### 1.1 Struktur Export/Import Saat Ini

#### Sheet yang Digunakan (14 Sheets)

1. **personal** - Data personal karyawan (1 row per employee)
2. **administration** - Data administrasi/HR (1 row per employee)
3. **bank accounts** - Data rekening bank (1 row per employee)
4. **tax identification no** - Data NPWP (1 row per employee)
5. **health insurance** - Data asuransi kesehatan (1+ rows per employee, bisa multiple)
6. **license** - Data lisensi/sertifikat (1+ rows per employee, bisa multiple)
7. **family** - Data keluarga (1+ rows per employee, bisa multiple)
8. **education** - Data pendidikan (1+ rows per employee, bisa multiple)
9. **course** - Data pelatihan (1+ rows per employee, bisa multiple)
10. **job experience** - Data pengalaman kerja (1+ rows per employee, bisa multiple)
11. **operable unit** - Data unit operasional (1+ rows per employee, bisa multiple)
12. **emergency call** - Data kontak darurat (1+ rows per employee, bisa multiple)
13. **additional data** - Data tambahan (1 row per employee)
14. **termination** - Data terminasi (1 row per employee yang terminated)

### 1.2 Klasifikasi Data Berdasarkan Relasi

#### One-to-One (1 row per employee):

-   ✅ Personal
-   ✅ Administration
-   ✅ Bank Accounts
-   ✅ Tax Identification
-   ✅ Additional Data
-   ✅ Termination

#### One-to-Many (1+ rows per employee):

-   ⚠️ Health Insurance (multiple polis)
-   ⚠️ License (multiple sertifikat)
-   ⚠️ Family (multiple anggota keluarga)
-   ⚠️ Education (multiple pendidikan)
-   ⚠️ Course (multiple pelatihan)
-   ⚠️ Job Experience (multiple pengalaman)
-   ⚠️ Operable Unit (multiple unit)
-   ⚠️ Emergency Call (multiple kontak darurat)

---

## 2. Problem Analysis

### 2.1 Fragmentasi Data yang Berlebihan

-   **Masalah**: Data satu karyawan tersebar di 14 sheet berbeda
-   **Dampak**:
    -   User harus membuka banyak tab untuk melihat data lengkap satu karyawan
    -   Sulit untuk melakukan cross-reference antar data
    -   Risiko inkonsistensi data tinggi

### 2.2 Kompleksitas Input Data

-   **Masalah**: User harus mengisi data di banyak sheet berbeda
-   **Dampak**:
    -   Proses input memakan waktu lama
    -   User harus memastikan identitas karyawan (Full Name + Identity Card No) konsisten di semua sheet
    -   Mudah terjadi kesalahan input karena harus bolak-balik antar sheet

### 2.3 Masalah Relasi One-to-Many

-   **Masalah**: Sheet seperti family, education, course, license bisa punya multiple rows per employee
-   **Dampak**:
    -   Tidak jelas mana data yang masih terkait dengan karyawan yang sama
    -   Harus mengandalkan Full Name + Identity Card No di setiap row untuk linking
    -   Sulit untuk tracking data karyawan tertentu di sheet yang berbeda

### 2.4 Validasi Cross-Sheet yang Kompleks

-   **Masalah**: Validasi relasi antar sheet sulit dilakukan
-   **Dampak**:
    -   Error handling menjadi kompleks karena error bisa terjadi di sheet berbeda
    -   User harus bolak-balik antar sheet untuk memperbaiki error
    -   Tidak ada validasi real-time antar sheet

### 2.5 Performa Import yang Lambat

-   **Masalah**: Harus memproses 14 sheet secara berurutan
-   **Dampak**:
    -   Dependency antar sheet (misal: personal harus diimport dulu sebelum administration)
    -   Proses import lebih lambat untuk data besar
    -   Memory usage tinggi karena harus load semua sheet

### 2.6 User Experience yang Buruk

-   **Masalah**: Template Excel tidak user-friendly
-   **Dampak**:
    -   Tidak ada instruksi penggunaan
    -   Tidak ada data validation di Excel
    -   Tidak ada color coding untuk required fields
    -   Error messages tidak jelas

---

## 3. Solution Analysis

### 3.1 Alternatif Solusi

#### Solusi 1: Single Sheet dengan Denormalisasi ❌

**Konsep**: Menggabungkan semua data menjadi 1 sheet dengan kolom yang sangat banyak. Data one-to-many di-flatten menggunakan prefix atau numbering.

**Kelebihan**:

-   Semua data karyawan dalam 1 baris
-   Mudah untuk melihat data lengkap
-   Import/export lebih cepat (1 sheet)

**Kekurangan**:

-   Kolom sangat banyak (bisa 100+ kolom)
-   Tidak fleksibel untuk data yang jumlahnya bervariasi
-   Sulit di-maintain jika struktur data berubah
-   Excel bisa lambat dengan banyak kolom

**Rekomendasi**: ❌ **TIDAK DISARANKAN** - Terlalu kompleks dan tidak fleksibel

---

#### Solusi 2: Consolidated Sheet dengan Grouping ⚠️

**Konsep**: Mengurangi dari 14 sheet menjadi 3-4 sheet dengan mengelompokkan data yang relevan.

**Struktur Excel**:

##### Sheet 1: "Employee Master" (Data Core)

-   Personal data
-   Administration data
-   Additional data
-   Tax identification
-   Bank accounts (1 row per employee)
-   **1 row = 1 employee**

##### Sheet 2: "Employee Financial" (Data Keuangan)

-   Health insurance (multiple rows per employee)
-   **1 row = 1 record financial, dengan NIK sebagai key**

##### Sheet 3: "Employee Family & Education" (Data Keluarga & Pendidikan)

-   Family (multiple rows per employee)
-   Education (multiple rows per employee)
-   Course (multiple rows per employee)
-   **1 row = 1 record, dengan NIK sebagai key**

##### Sheet 4: "Employee Professional" (Data Profesional)

-   License (multiple rows per employee)
-   Job experience (multiple rows per employee)
-   Operable unit (multiple rows per employee)
-   Emergency call (multiple rows per employee)
-   **1 row = 1 record, dengan NIK sebagai key**

##### Sheet 5: "Termination" (Data Terminasi)

-   Tetap terpisah karena sifatnya berbeda (karyawan yang sudah tidak aktif)

**Kelebihan**:

-   Mengurangi dari 14 sheet menjadi 5 sheet
-   Data masih terorganisir secara logis
-   Lebih mudah untuk user memahami struktur
-   Masih mempertahankan fleksibilitas untuk one-to-many relationships

**Kekurangan**:

-   Masih ada multiple sheets
-   Data karyawan masih tersebar (tapi lebih sedikit)
-   Perlu konsistensi NIK di semua sheet

**Rekomendasi**: ⚠️ **MODERATE IMPROVEMENT** - Keseimbangan antara kemudahan dan fleksibilitas

---

#### Solusi 3: Master-Detail dengan Pivot Key ✅ **RECOMMENDED**

**Konsep**: 1 sheet master untuk data core, 1 sheet detail untuk semua data one-to-many dengan tipe record.

**Struktur Excel**:

##### Sheet 1: "Employees" (Master Data)

```
| NIK | Full Name | Identity Card | DOB | ... | POH | DOH | Bank Name | Bank Account | Tax No | ... |
```

**1 row = 1 employee** (semua data yang one-to-one termasuk bank accounts)

##### Sheet 2: "Employee Details" (Detail Data - All Types)

```
| NIK | Record Type | Sequence | Field_1 | Field_2 | Field_3 | ... | Field_N |
```

**Record Type** bisa:

-   `FAMILY` - untuk data keluarga
-   `EDUCATION` - untuk data pendidikan
-   `COURSE` - untuk data pelatihan
-   `LICENSE` - untuk data lisensi
-   `JOB_EXP` - untuk pengalaman kerja
-   `INSURANCE` - untuk asuransi kesehatan
-   `EMERGENCY` - untuk kontak darurat
-   `OPERABLE_UNIT` - untuk unit operasional

**Sequence**: Nomor urut untuk multiple records dengan tipe yang sama.

**Contoh Data**:

```
NIK          | Record Type | Seq | Field_1        | Field_2         | Field_3
12345        | FAMILY      | 1   | Spouse         | John Doe        | 1980-01-01
12345        | FAMILY      | 2   | Child          | Jane Doe        | 2010-05-15
12345        | EDUCATION   | 1   | Bachelor       | University A    | 2005
12345        | INSURANCE   | 1   | BPJS Kesehatan | 1234567890      | 2020-01-01
```

##### Sheet 3: "Termination" (Data Terminasi)

-   Tetap terpisah atau bisa digabung ke Employees dengan flag `is_active = 0`

**Kelebihan**:

-   Hanya 2-3 sheet utama
-   Semua data karyawan terlihat jelas dengan NIK sebagai key
-   Fleksibel untuk data yang jumlahnya bervariasi
-   Mudah untuk di-query dan di-validasi
-   Struktur lebih clean dan maintainable
-   Bank accounts sudah termasuk di master data (one-to-one)

**Kekurangan**:

-   Perlu mapping field yang berbeda untuk setiap Record Type
-   User perlu memahami konsep Record Type
-   Import logic lebih kompleks (perlu parsing Record Type)

**Rekomendasi**: ✅ **SANGAT DISARANKAN** - Struktur paling efisien dan fleksibel

---

#### Solusi 4: JSON Column Approach ❌

**Konsep**: 1 sheet master, data one-to-many disimpan sebagai JSON string di kolom khusus.

**Kekurangan**:

-   User tidak bisa edit JSON secara langsung di Excel
-   Perlu tool/editor khusus untuk edit JSON
-   Tidak user-friendly untuk non-technical users

**Rekomendasi**: ❌ **TIDAK DISARANKAN** - Terlalu kompleks untuk user non-technical

---

#### Solusi 5: Template-based dengan Wizard (Enhancement)

**Konsep**: Tetap menggunakan multiple sheets, tetapi dengan:

1. Template Excel yang lebih user-friendly
2. Data validation dan dropdown di Excel
3. Helper sheet dengan instruksi
4. Color coding untuk required fields
5. Summary sheet untuk quick overview

**Kelebihan**:

-   Tidak mengubah struktur data
-   User experience lebih baik
-   Validasi di level Excel
-   Error prevention sebelum import

**Kekurangan**:

-   Masih 14 sheet
-   Masalah fragmentasi tetap ada
-   Perlu development untuk Excel validation

**Rekomendasi**: ✅ **BISA DIKOMBINASIKAN** dengan solusi lain sebagai enhancement

---

### 3.2 Rekomendasi Final

#### Kombinasi Solusi 3 + Solusi 5

##### 1. Implementasi Utama: Solusi 3 (Master-Detail dengan Pivot Key)

-   Mengurangi dari 14 sheet menjadi **3 sheet**:
    -   **Sheet "Employees"** - Master data (one-to-one termasuk bank accounts)
    -   **Sheet "Employee Details"** - Detail data (one-to-many dengan Record Type)
    -   **Sheet "Termination"** - Data terminasi (opsional, bisa digabung ke Employees dengan flag)

##### 2. Enhancement: Solusi 5 (Template-based dengan Wizard)

-   Tambahkan **Sheet "Instructions"** - Panduan penggunaan
-   Tambahkan **Sheet "Summary"** - Ringkasan dan validasi
-   Data validation di Excel
-   Color coding dan helper

#### Struktur Final yang Direkomendasikan:

1. **Sheet "Instructions"** - Panduan penggunaan template
2. **Sheet "Summary"** - Ringkasan karyawan dan status validasi
3. **Sheet "Employees"** - Master data (one-to-one):
    - Personal data
    - Administration data
    - Bank accounts (1 rekening per employee)
    - Tax identification
    - Additional data
4. **Sheet "Employee Details"** - Detail data (one-to-many):
    - Family (Record Type: FAMILY)
    - Education (Record Type: EDUCATION)
    - Course (Record Type: COURSE)
    - License (Record Type: LICENSE)
    - Job Experience (Record Type: JOB_EXP)
    - Health Insurance (Record Type: INSURANCE)
    - Emergency Call (Record Type: EMERGENCY)
    - Operable Unit (Record Type: OPERABLE_UNIT)
5. **Sheet "Termination"** - Data terminasi (atau bisa digabung ke Employees dengan flag)

#### Manfaat:

✅ **Mengurangi kompleksitas** dari 14 sheet menjadi 3-5 sheet  
✅ **Data karyawan lebih mudah di-track** dengan NIK sebagai key  
✅ **Fleksibel** untuk data yang jumlahnya bervariasi  
✅ **User experience lebih baik** dengan instruksi dan validasi  
✅ **Maintainable dan scalable**  
✅ **Bank accounts sudah termasuk di master data** (tidak perlu sheet terpisah)

#### Implementasi Considerations:

1. **Perlu refactor export/import classes**

    - Buat `EmployeeMasterExport` untuk sheet Employees
    - Buat `EmployeeDetailExport` untuk sheet Employee Details dengan Record Type
    - Update `MultipleSheetExport` untuk menggunakan struktur baru

2. **Perlu mapping logic untuk Record Type**

    - Mapping field untuk setiap Record Type
    - Validasi Record Type yang valid
    - Error handling untuk Record Type yang tidak dikenal

3. **Perlu validasi cross-sheet**

    - NIK consistency check
    - Validasi employee exists sebelum import detail
    - Validasi required fields per Record Type

4. **Perlu migration script**

    - Convert data existing ke format baru
    - Backup data sebelum migration
    - Rollback mechanism jika ada masalah

5. **Perlu dokumentasi untuk user**

    - User manual untuk template baru
    - Video tutorial (opsional)
    - FAQ untuk common issues

6. **Perlu testing**
    - Unit test untuk export/import logic
    - Integration test untuk full flow
    - User acceptance test dengan HR staff

---

## 4. Implementation Details

### 4.1 Struktur Excel Detail (Solusi 3 - Recommended)

#### Sheet 1: "Instructions" (Panduan)

**Konten:**

```
╔══════════════════════════════════════════════════════════════════════════╗
║                    PANDUAN PENGGUNAAN TEMPLATE                            ║
╚══════════════════════════════════════════════════════════════════════════╝

1. CARA PENGGUNAAN:
   - Sheet "Employees" berisi data master karyawan (1 baris = 1 karyawan)
   - Sheet "Employee Details" berisi data detail (multiple rows per karyawan)
   - Gunakan NIK sebagai identifier untuk menghubungkan data antar sheet

2. IDENTIFIER:
   - NIK (Nomor Induk Karyawan) adalah key utama
   - Pastikan NIK konsisten di semua sheet
   - NIK harus unik untuk setiap karyawan

3. RECORD TYPE di Sheet "Employee Details":
   - FAMILY: Data keluarga (Spouse, Child, Parent, dll)
   - EDUCATION: Data pendidikan formal
   - COURSE: Data pelatihan/kursus
   - LICENSE: Data lisensi/sertifikat
   - JOB_EXP: Data pengalaman kerja
   - INSURANCE: Data asuransi kesehatan
   - EMERGENCY: Data kontak darurat
   - OPERABLE_UNIT: Data unit operasional

4. SEQUENCE:
   - Nomor urut untuk multiple records dengan Record Type yang sama
   - Contoh: FAMILY dengan Sequence 1, 2, 3 untuk 3 anggota keluarga

5. VALIDASI:
   - NIK harus ada di sheet Employees sebelum bisa digunakan di Employee Details
   - Record Type harus sesuai dengan daftar yang valid
   - Sequence harus berupa angka positif

6. TIPS:
   - Gunakan Excel Filter untuk mencari data karyawan tertentu
   - Sort berdasarkan NIK untuk memudahkan tracking
   - Pastikan semua required fields terisi sebelum import
```

---

#### Sheet 2: "Summary" (Ringkasan)

**Struktur:**

```
| NIK | Full Name | Identity Card | Project Code | Status | Validation Errors |
|-----|-----------|---------------|--------------|--------|-------------------|
| 12345 | John Doe | 1234567890 | 000H | ✅ Valid | - |
| 12346 | Jane Smith | 0987654321 | 001H | ⚠️ Warning | Missing bank account |
| 12347 | Bob Wilson | 1122334455 | 017C | ❌ Error | Invalid NIK format |
```

**Fungsi:**

-   Quick overview semua karyawan yang akan diimport
-   Status validasi per karyawan
-   Daftar error yang perlu diperbaiki
-   Bisa digunakan untuk tracking progress

---

#### Sheet 3: "Employees" (Master Data)

**Struktur Kolom:**

| No  | Column Name       | Type   | Required | Description                           | Example              |
| --- | ----------------- | ------ | -------- | ------------------------------------- | -------------------- |
| 1   | NIK               | Text   | ✅ Yes   | Nomor Induk Karyawan (Key)            | 12345                |
| 2   | Full Name         | Text   | ✅ Yes   | Nama lengkap karyawan                 | John Doe             |
| 3   | Identity Card No  | Text   | ✅ Yes   | Nomor KTP                             | 1234567890123456     |
| 4   | Place of Birth    | Text   | ✅ Yes   | Tempat lahir                          | Jakarta              |
| 5   | Date of Birth     | Date   | ✅ Yes   | Tanggal lahir                         | 1990-01-15           |
| 6   | Blood Type        | Text   | ❌ No    | Golongan darah                        | A                    |
| 7   | Religion          | Text   | ✅ Yes   | Agama                                 | Islam                |
| 8   | Nationality       | Text   | ❌ No    | Kewarganegaraan                       | Indonesian           |
| 9   | Gender            | Text   | ✅ Yes   | Jenis kelamin                         | Male / Female        |
| 10  | Marital Status    | Text   | ❌ No    | Status pernikahan                     | Married / Single     |
| 11  | Address           | Text   | ❌ No    | Alamat lengkap                        | Jl. Sudirman No. 1   |
| 12  | Village           | Text   | ❌ No    | Kelurahan                             | Menteng              |
| 13  | Ward              | Text   | ❌ No    | Kecamatan                             | Menteng              |
| 14  | District          | Text   | ❌ No    | Kabupaten/Kota                        | Jakarta Pusat        |
| 15  | City              | Text   | ❌ No    | Kota                                  | Jakarta              |
| 16  | Phone             | Text   | ❌ No    | Nomor telepon                         | 081234567890         |
| 17  | Email             | Text   | ❌ No    | Email                                 | john.doe@arka.co.id  |
| 18  | POH               | Text   | ✅ Yes   | Place of Hire                         | Jakarta              |
| 19  | DOH               | Date   | ✅ Yes   | Date of Hire                          | 2020-01-01           |
| 20  | FOC               | Date   | ❌ No    | First of Contract                     | 2020-01-01           |
| 21  | Department        | Text   | ✅ Yes   | Nama departemen                       | HR                   |
| 22  | Position          | Text   | ✅ Yes   | Nama posisi                           | HR Manager           |
| 23  | Grade             | Text   | ❌ No    | Grade karyawan                        | G5                   |
| 24  | Level             | Text   | ❌ No    | Level karyawan                        | Manager              |
| 25  | Project Code      | Text   | ✅ Yes   | Kode project                          | 000H                 |
| 26  | Project Name      | Text   | ❌ No    | Nama project                          | Head Office          |
| 27  | Class             | Text   | ❌ No    | Kelas karyawan                        | Staff                |
| 28  | Agreement         | Text   | ❌ No    | Jenis perjanjian                      | PKWT                 |
| 29  | Company Program   | Text   | ❌ No    | Program perusahaan                    | -                    |
| 30  | FPTK No           | Text   | ❌ No    | Nomor FPTK                            | FPTK-2020-001        |
| 31  | SK Active No      | Text   | ❌ No    | Nomor SK Aktif                        | SK-2020-001          |
| 32  | Basic Salary      | Number | ❌ No    | Gaji pokok                            | 5000000              |
| 33  | Site Allowance    | Number | ❌ No    | Tunjangan site                        | 1000000              |
| 34  | Other Allowance   | Number | ❌ No    | Tunjangan lain                        | 500000               |
| 35  | Bank Name         | Text   | ❌ No    | Nama bank                             | BCA                  |
| 36  | Bank Account      | Text   | ❌ No    | Nomor rekening                        | 1234567890           |
| 37  | Account Name      | Text   | ❌ No    | Nama pemilik rekening                 | John Doe             |
| 38  | Bank Branch       | Text   | ❌ No    | Cabang bank                           | Sudirman             |
| 39  | Tax No            | Text   | ❌ No    | Nomor NPWP                            | 12.345.678.9-012.000 |
| 40  | Tax Name          | Text   | ❌ No    | Nama di NPWP                          | John Doe             |
| 41  | Tax Address       | Text   | ❌ No    | Alamat di NPWP                        | Jl. Sudirman No. 1   |
| 42  | Additional Data 1 | Text   | ❌ No    | Data tambahan 1                       | -                    |
| 43  | Additional Data 2 | Text   | ❌ No    | Data tambahan 2                       | -                    |
| 44  | Additional Data 3 | Text   | ❌ No    | Data tambahan 3                       | -                    |
| 45  | Is Active         | Text   | ✅ Yes   | Status aktif (1=Active, 0=Terminated) | 1                    |

**Contoh Data:**

```
NIK | Full Name | Identity Card No | DOB | ... | Bank Name | Bank Account | Tax No | ...
12345 | John Doe | 1234567890123456 | 1990-01-15 | ... | BCA | 1234567890 | 12.345.678.9-012.000 | ...
12346 | Jane Smith | 0987654321098765 | 1985-05-20 | ... | Mandiri | 9876543210 | 09.876.543.2-109.000 | ...
```

---

#### Sheet 4: "Employee Details" (Detail Data - All Types)

**Struktur Kolom:**

| No  | Column Name | Type   | Required | Description                             |
| --- | ----------- | ------ | -------- | --------------------------------------- |
| 1   | NIK         | Text   | ✅ Yes   | Nomor Induk Karyawan (Key)              |
| 2   | Record Type | Text   | ✅ Yes   | Tipe record (FAMILY, EDUCATION, dll)    |
| 3   | Sequence    | Number | ✅ Yes   | Nomor urut (1, 2, 3, ...)               |
| 4   | Field_1     | Text   | Varies   | Field pertama (berbeda per Record Type) |
| 5   | Field_2     | Text   | Varies   | Field kedua                             |
| 6   | Field_3     | Text   | Varies   | Field ketiga                            |
| 7   | Field_4     | Text   | Varies   | Field keempat                           |
| 8   | Field_5     | Text   | Varies   | Field kelima                            |
| 9   | Field_6     | Text   | Varies   | Field keenam                            |
| 10  | Field_7     | Text   | Varies   | Field ketujuh                           |
| 11  | Field_8     | Text   | Varies   | Field kedelapan                         |
| 12  | Field_9     | Text   | Varies   | Field kesembilan                        |
| 13  | Field_10    | Text   | Varies   | Field kesepuluh                         |
| 14  | Remarks     | Text   | ❌ No    | Catatan tambahan                        |

**Mapping Field per Record Type:**

##### Record Type: FAMILY

| Field   | Column Name  | Description                               | Required |
| ------- | ------------ | ----------------------------------------- | -------- |
| Field_1 | Relationship | Hubungan (Spouse, Child, Parent, Sibling) | ✅ Yes   |
| Field_2 | Name         | Nama anggota keluarga                     | ✅ Yes   |
| Field_3 | Birthplace   | Tempat lahir                              | ❌ No    |
| Field_4 | Birthdate    | Tanggal lahir                             | ❌ No    |
| Field_5 | Insurance No | Nomor BPJS Kesehatan                      | ❌ No    |
| Field_6 | Remarks      | Catatan                                   | ❌ No    |

**Contoh:**

```
NIK | Record Type | Seq | Field_1 | Field_2 | Field_3 | Field_4 | Field_5 | Field_6
12345 | FAMILY | 1 | Spouse | Jane Doe | Jakarta | 1992-03-10 | 1234567890 | -
12345 | FAMILY | 2 | Child | John Doe Jr | Jakarta | 2015-07-20 | - | -
```

##### Record Type: EDUCATION

| Field   | Column Name    | Description                                 | Required |
| ------- | -------------- | ------------------------------------------- | -------- |
| Field_1 | Education Name | Nama pendidikan (SD, SMP, SMA, S1, S2, dll) | ✅ Yes   |
| Field_2 | Institution    | Nama institusi                              | ✅ Yes   |
| Field_3 | Address        | Alamat institusi                            | ❌ No    |
| Field_4 | Year           | Tahun lulus                                 | ❌ No    |
| Field_5 | Major          | Jurusan (untuk S1/S2)                       | ❌ No    |
| Field_6 | Remarks        | Catatan                                     | ❌ No    |

**Contoh:**

```
NIK | Record Type | Seq | Field_1 | Field_2 | Field_3 | Field_4 | Field_5 | Field_6
12345 | EDUCATION | 1 | S1 | Universitas Indonesia | Depok | 2012 | Computer Science | -
12345 | EDUCATION | 2 | SMA | SMA Negeri 1 Jakarta | Jakarta | 2008 | IPA | -
```

##### Record Type: COURSE

| Field   | Column Name    | Description           | Required |
| ------- | -------------- | --------------------- | -------- |
| Field_1 | Course Name    | Nama pelatihan/kursus | ✅ Yes   |
| Field_2 | Institution    | Lembaga penyelenggara | ❌ No    |
| Field_3 | Start Date     | Tanggal mulai         | ❌ No    |
| Field_4 | End Date       | Tanggal selesai       | ❌ No    |
| Field_5 | Certificate No | Nomor sertifikat      | ❌ No    |
| Field_6 | Remarks        | Catatan               | ❌ No    |

**Contoh:**

```
NIK | Record Type | Seq | Field_1 | Field_2 | Field_3 | Field_4 | Field_5 | Field_6
12345 | COURSE | 1 | Project Management | PMI | 2020-01-15 | 2020-01-20 | PM-2020-001 | -
```

##### Record Type: LICENSE

| Field   | Column Name  | Description             | Required |
| ------- | ------------ | ----------------------- | -------- |
| Field_1 | License Name | Nama lisensi/sertifikat | ✅ Yes   |
| Field_2 | License No   | Nomor lisensi           | ❌ No    |
| Field_3 | Issued By    | Diterbitkan oleh        | ❌ No    |
| Field_4 | Issue Date   | Tanggal terbit          | ❌ No    |
| Field_5 | Valid Date   | Tanggal berlaku sampai  | ❌ No    |
| Field_6 | Remarks      | Catatan                 | ❌ No    |

**Contoh:**

```
NIK | Record Type | Seq | Field_1 | Field_2 | Field_3 | Field_4 | Field_5 | Field_6
12345 | LICENSE | 1 | SIM A | 1234567890 | Polres Jakarta | 2020-01-01 | 2025-01-01 | -
12345 | LICENSE | 2 | Sertifikat PMP | PMP-12345 | PMI | 2019-06-01 | 2024-06-01 | -
```

##### Record Type: JOB_EXP

| Field   | Column Name  | Description           | Required |
| ------- | ------------ | --------------------- | -------- |
| Field_1 | Company Name | Nama perusahaan       | ✅ Yes   |
| Field_2 | Position     | Posisi/jabatan        | ✅ Yes   |
| Field_3 | Start Date   | Tanggal mulai kerja   | ❌ No    |
| Field_4 | End Date     | Tanggal selesai kerja | ❌ No    |
| Field_5 | Description  | Deskripsi pekerjaan   | ❌ No    |
| Field_6 | Remarks      | Catatan               | ❌ No    |

**Contoh:**

```
NIK | Record Type | Seq | Field_1 | Field_2 | Field_3 | Field_4 | Field_5 | Field_6
12345 | JOB_EXP | 1 | PT ABC | Software Developer | 2015-01-01 | 2019-12-31 | Develop web applications | -
```

##### Record Type: INSURANCE

| Field   | Column Name    | Description                         | Required |
| ------- | -------------- | ----------------------------------- | -------- |
| Field_1 | Insurance Type | Jenis asuransi (BPJS, Private, dll) | ✅ Yes   |
| Field_2 | Insurance No   | Nomor asuransi                      | ✅ Yes   |
| Field_3 | Start Date     | Tanggal mulai                       | ❌ No    |
| Field_4 | End Date       | Tanggal berakhir                    | ❌ No    |
| Field_5 | Provider       | Provider asuransi                   | ❌ No    |
| Field_6 | Remarks        | Catatan                             | ❌ No    |

**Contoh:**

```
NIK | Record Type | Seq | Field_1 | Field_2 | Field_3 | Field_4 | Field_5 | Field_6
12345 | INSURANCE | 1 | BPJS Kesehatan | 1234567890 | 2020-01-01 | - | BPJS | -
12345 | INSURANCE | 2 | Private Insurance | PRV-12345 | 2020-01-01 | 2024-12-31 | Allianz | -
```

##### Record Type: EMERGENCY

| Field   | Column Name  | Description         | Required |
| ------- | ------------ | ------------------- | -------- |
| Field_1 | Name         | Nama kontak darurat | ✅ Yes   |
| Field_2 | Relationship | Hubungan            | ✅ Yes   |
| Field_3 | Phone        | Nomor telepon       | ✅ Yes   |
| Field_4 | Address      | Alamat              | ❌ No    |
| Field_5 | -            | -                   | -        |
| Field_6 | Remarks      | Catatan             | ❌ No    |

**Contoh:**

```
NIK | Record Type | Seq | Field_1 | Field_2 | Field_3 | Field_4 | Field_5 | Field_6
12345 | EMERGENCY | 1 | Jane Doe | Spouse | 081234567890 | Jl. Sudirman No. 1 | - | -
12345 | EMERGENCY | 2 | John Doe Sr | Father | 081987654321 | Jl. Thamrin No. 2 | - | -
```

##### Record Type: OPERABLE_UNIT

| Field   | Column Name | Description           | Required |
| ------- | ----------- | --------------------- | -------- |
| Field_1 | Unit Name   | Nama unit operasional | ✅ Yes   |
| Field_2 | Unit Code   | Kode unit             | ❌ No    |
| Field_3 | Start Date  | Tanggal mulai         | ❌ No    |
| Field_4 | End Date    | Tanggal selesai       | ❌ No    |
| Field_5 | Description | Deskripsi             | ❌ No    |
| Field_6 | Remarks     | Catatan               | ❌ No    |

**Contoh:**

```
NIK | Record Type | Seq | Field_1 | Field_2 | Field_3 | Field_4 | Field_5 | Field_6
12345 | OPERABLE_UNIT | 1 | Unit A | UA-001 | 2020-01-01 | - | Main operational unit | -
```

---

#### Sheet 5: "Termination" (Data Terminasi)

**Struktur Kolom:**

| No  | Column Name        | Type | Required | Description                     |
| --- | ------------------ | ---- | -------- | ------------------------------- |
| 1   | Full Name          | Text | ✅ Yes   | Nama lengkap karyawan           |
| 2   | Identity Card No   | Text | ✅ Yes   | Nomor KTP                       |
| 3   | NIK                | Text | ✅ Yes   | Nomor Induk Karyawan            |
| 4   | POH                | Text | ❌ No    | Place of Hire                   |
| 5   | DOH                | Date | ❌ No    | Date of Hire                    |
| 6   | Department         | Text | ❌ No    | Departemen                      |
| 7   | Position           | Text | ❌ No    | Posisi                          |
| 8   | Project Code       | Text | ❌ No    | Kode project                    |
| 9   | Termination Date   | Date | ✅ Yes   | Tanggal terminasi               |
| 10  | Termination Reason | Text | ✅ Yes   | Alasan terminasi                |
| 11  | COE No             | Text | ❌ No    | Nomor Certificate of Employment |

**Contoh Data:**

```
Full Name | Identity Card No | NIK | POH | DOH | Department | Position | Project Code | Termination Date | Termination Reason | COE No
John Doe | 1234567890123456 | 12345 | Jakarta | 2020-01-01 | HR | HR Manager | 000H | 2024-12-31 | End of Contract | COE-2024-001
```

---

### 4.2 Format Excel yang Direkomendasikan

#### Styling & Formatting

##### Header Row (Row 1):

-   **Background Color**: Blue (#4472C4)
-   **Text Color**: White
-   **Font**: Bold
-   **Alignment**: Center
-   **Freeze Panes**: Row 1 (agar header selalu terlihat saat scroll)

##### Data Rows:

-   **Alternating Row Colors**: Light gray untuk row genap (mudah dibaca)
-   **Text Alignment**:
    -   Left untuk text fields
    -   Center untuk dates dan numbers
    -   Center untuk NIK, Record Type, Sequence

##### Column Widths:

-   **Auto-size** untuk semua kolom
-   **Minimum width**: 10 characters
-   **Maximum width**: 50 characters (untuk text panjang)

##### Data Validation:

-   **NIK**: Dropdown dari sheet Employees (untuk Employee Details)
-   **Record Type**: Dropdown dengan values: FAMILY, EDUCATION, COURSE, LICENSE, JOB_EXP, INSURANCE, EMERGENCY, OPERABLE_UNIT
-   **Sequence**: Number validation (>= 1)
-   **Dates**: Date format validation
-   **Required Fields**: Highlight dengan yellow background

##### Conditional Formatting:

-   **Duplicate NIK**: Highlight dengan red (jika ada duplikasi di sheet Employees)
-   **Missing Required Fields**: Highlight dengan orange
-   **Invalid Record Type**: Highlight dengan red
-   **NIK Not Found**: Highlight dengan red (untuk Employee Details jika NIK tidak ada di Employees)

---

### 4.3 Contoh File Excel Lengkap

#### Sheet "Employees" - Sample Data:

```
NIK    | Full Name | Identity Card No | DOB       | ... | Bank Name | Bank Account | Tax No
-------|-----------|-------------------|-----------|-----|-----------|--------------|----------
12345  | John Doe  | 1234567890123456  | 1990-01-15| ... | BCA       | 1234567890   | 12.345.678.9-012.000
12346  | Jane Smith| 0987654321098765  | 1985-05-20| ... | Mandiri   | 9876543210   | 09.876.543.2-109.000
```

#### Sheet "Employee Details" - Sample Data:

```
NIK   | Record Type | Seq | Field_1      | Field_2         | Field_3    | Field_4      | Field_5
------|-------------|-----|--------------|-----------------|------------|--------------|----------
12345 | FAMILY      | 1   | Spouse       | Jane Doe        | Jakarta    | 1992-03-10   | 1234567890
12345 | FAMILY      | 2   | Child        | John Doe Jr     | Jakarta    | 2015-07-20   | -
12345 | EDUCATION   | 1   | S1           | UI              | Depok      | 2012         | Computer Science
12345 | LICENSE     | 1   | SIM A        | 1234567890      | Polres     | 2020-01-01   | 2025-01-01
12345 | INSURANCE   | 1   | BPJS         | 1234567890      | BPJS       | 2020-01-01   | -
12346 | FAMILY      | 1   | Spouse       | Bob Smith       | Bandung    | 1987-08-15   | 0987654321
12346 | EDUCATION   | 1   | S1           | ITB             | Bandung    | 2010         | Engineering
```

---

## 5. Technical Considerations

### 5.1 Keterkaitan Sheet "Administration" dan Sheet "Termination"

#### Struktur Data di Database

Data termination **TIDAK** disimpan di tabel terpisah, melainkan di kolom yang sama dengan data administration di tabel `administrations`:

-   **Kolom `is_active`** (boolean, default `1`): Menentukan status aktif/tidak aktif
-   **Kolom `termination_date`** (nullable): Tanggal terminasi
-   **Kolom `termination_reason`** (nullable): Alasan terminasi
-   **Kolom `coe_no`** (nullable): Nomor Certificate of Employment

#### Logika Export

**TerminationExport** mengexport data dengan filter:

```php
->where('administrations.is_active', 0)
```

-   Hanya mengexport karyawan dengan `is_active = 0`
-   Sheet "Termination" adalah **subset** dari Sheet "Administration" yang sudah di-terminate
-   Data yang sama, hanya difilter berdasarkan status

#### Logika Import

**TerminationImport** saat import selalu set:

```php
'is_active' => 0, // Set to 0 for termination
```

-   Import termination otomatis mengubah status menjadi tidak aktif
-   Data termination disimpan di tabel `administrations` yang sama

#### Hubungan Dua Arah

-   **Sheet "Administration" → Sheet "Termination"**:
    -   Jika `is_active = 0` di Sheet "Administration", karyawan tersebut muncul di Sheet "Termination"
-   **Sheet "Termination" → Sheet "Administration"**:
    -   Import Sheet "Termination" otomatis set `is_active = 0` di Sheet "Administration"

#### Masalah Redundansi

-   **Redundansi Data**: Sheet "Termination" adalah duplikasi data dari Sheet "Administration" dengan filter `is_active = 0`
-   **Inkonsistensi Potensial**: Jika `is_active = 0` di Sheet "Administration" tapi tidak ada di Sheet "Termination" (atau sebaliknya)
-   **Kompleksitas Maintenance**: User harus maintain 2 sheet untuk data yang sebenarnya sama

#### Rekomendasi: Menggabungkan Sheet Termination

**Hapus Sheet "Termination" terpisah** dan gunakan kolom `is_active` di Sheet "Administration" sebagai flag:

-   `is_active = 1` → Karyawan aktif
-   `is_active = 0` → Karyawan terminated (dengan data `termination_date`, `termination_reason`, `coe_no`)

**Keuntungan**:

-   Mengurangi jumlah sheet (dari 14 menjadi 13)
-   Menghilangkan redundansi data
-   Memudahkan maintenance (satu sumber data)
-   Konsistensi data lebih terjaga

### 5.2 Analisis Skenario Rehire

#### Struktur Database untuk Rehire

**Tabel `employees`:**

-   `identity_card` (KTP) adalah **UNIQUE constraint**
-   Satu KTP = satu employee record
-   Employee diidentifikasi berdasarkan `identity_card`, **bukan nama**

**Tabel `administrations`:**

-   Satu `employee_id` bisa punya **multiple `administrations`** dengan NIK berbeda
-   Tidak ada unique constraint pada kombinasi `employee_id + nik`
-   Business rule: hanya boleh ada **1 active administration** per employee (`is_active = 1`)

#### Skenario Rehire: Contoh Frizky

**Data di Excel (setelah digabung):**

```
| Full Name | Identity Card | NIK   | is_active | termination_date | ... |
|-----------|---------------|-------|-----------|------------------|-----|
| Frizky    | 1234567890    | 13100 | 0         | 2024-01-15       | ... |
| Frizky    | 1234567890    | 15100 | 1         | NULL             | ... |
```

**Hasil di Database setelah Import:**

**Tabel `employees` (1 record):**

```
| id  | fullname | identity_card | ... |
|-----|----------|---------------|-----|
| abc | Frizky   | 1234567890    | ... |
```

**Tabel `administrations` (2 records):**

```
| id | employee_id | nik   | is_active | termination_date | ... |
|----|-------------|-------|-----------|------------------|-----|
| 1  | abc         | 13100 | 0         | 2024-01-15       | ... |
| 2  | abc         | 15100 | 1         | NULL             | ... |
```

#### Logika Import untuk Rehire

**PersonalImport:**

```php
$employee = Employee::updateOrCreate(
    ['identity_card' => trim($row['identity_card_no'])], // KEY: identity_card
    $employeeData
);
```

-   Karena `identity_card` sama, akan **update 1 employee record** yang sama
-   **TIDAK** akan membuat 2 employee records

**AdministrationImport:**

```php
$administration = Administration::updateOrCreate(
    [
        'employee_id' => $employee->id,
        'is_active' => 1  // KEY: hanya untuk active
    ],
    $administrationData
);
```

-   Untuk baris dengan `is_active = 0`: akan create administration baru (karena key berbeda)
-   Untuk baris dengan `is_active = 1`: akan update/create active administration

#### Kesimpulan Skenario Rehire

**TIDAK akan ada 2 employee dengan nama Frizky**. Akan ada:

-   **1 employee record** (karena `identity_card` sama)
-   **2 administration records** (karena NIK berbeda):
    -   NIK 13100 (terminated, `is_active = 0`)
    -   NIK 15100 (rehire, `is_active = 1`)

#### Implikasi untuk Struktur Excel

Jika sheet termination digabung ke sheet Employees/Administration:

**Keuntungan:**

-   ✅ Mendukung skenario rehire dengan baik
-   ✅ Satu employee bisa punya multiple administrations (history)
-   ✅ Data termination tetap tersimpan sebagai history

**Yang Perlu Diperhatikan:**

-   ✅ Pastikan `identity_card` **konsisten** di semua baris untuk employee yang sama
-   ✅ NIK harus **berbeda** untuk setiap administration
-   ✅ Hanya boleh ada **1 baris dengan `is_active = 1`** per `identity_card`
-   ✅ Bisa ada **multiple baris dengan `is_active = 0`** (history termination)

**Struktur Excel yang Direkomendasikan:**

```
| Identity Card | Full Name | NIK | is_active | DOH | termination_date | termination_reason | ... |
|---------------|-----------|-----|-----------|-----|-------------------|-------------------|-----|
| 1234567890    | Frizky    | 13100 | 0      | ... | 2024-01-15        | End of Contract   | ... |
| 1234567890    | Frizky    | 15100 | 1      | ... | NULL              | NULL              | ... |
```

**Kesimpulan**: Struktur database sudah mendukung rehire dengan baik. Menggabungkan sheet termination ke sheet Employees/Administration **TIDAK akan menimbulkan masalah duplikasi employee**.

---

## 6. Comparison & Conclusion

### 6.1 Perbandingan Struktur

| Aspek               | Struktur Saat Ini         | Struktur Rekomendasi        |
| ------------------- | ------------------------- | --------------------------- |
| **Jumlah Sheet**    | 14 sheets                 | 3-5 sheets                  |
| **Data Master**     | Tersebar di 6 sheet       | 1 sheet (Employees)         |
| **Data Detail**     | Tersebar di 8 sheet       | 1 sheet (Employee Details)  |
| **Key Identifier**  | Full Name + Identity Card | NIK (lebih reliable)        |
| **Kemudahan Input** | Sulit (banyak sheet)      | Lebih mudah (sedikit sheet) |
| **Validasi**        | Cross-sheet complex       | NIK-based simple            |
| **Maintainability** | Sulit                     | Lebih mudah                 |
| **User Experience** | Buruk                     | Lebih baik                  |

### 6.2 Kesimpulan

Struktur export/import saat ini dengan **14 sheet terpisah** memang menyulitkan proses pemrosesan data, terutama untuk:

-   Input data baru dalam jumlah besar
-   Update data existing
-   Validasi dan error handling
-   User experience

**Rekomendasi utama** adalah mengadopsi **Solusi 3 (Master-Detail dengan Pivot Key)** yang akan:

-   Mengurangi kompleksitas dari 14 sheet menjadi 3 sheet
-   Memudahkan tracking data dengan NIK sebagai key
-   Tetap fleksibel untuk data one-to-many
-   Lebih mudah di-maintain dan di-scale

Dengan enhancement dari **Solusi 5 (Template-based dengan Wizard)**, user experience akan semakin baik dengan instruksi, validasi, dan helper yang jelas.

### 6.3 Implikasi Teknis dari Analisis

Berdasarkan analisis di **Section 5 (Technical Considerations)**, terdapat beberapa poin penting yang perlu dipertimbangkan dalam implementasi:

#### 6.3.1 Penggabungan Sheet Termination

Seperti dijelaskan di **Section 5.1**, Sheet "Termination" sebenarnya adalah subset dari Sheet "Administration" dengan filter `is_active = 0`. Oleh karena itu:

-   **Rekomendasi**: Menggabungkan Sheet "Termination" ke dalam Sheet "Employees" dengan menggunakan kolom `is_active` sebagai flag
-   **Manfaat**: Mengurangi redundansi data dan memudahkan maintenance
-   **Implementasi**: Tambahkan kolom `is_active`, `termination_date`, `termination_reason`, dan `coe_no` ke Sheet "Employees"

#### 6.3.2 Dukungan Skenario Rehire

Berdasarkan analisis di **Section 5.2**, struktur database sudah mendukung skenario rehire dengan baik:

-   **Key Identifier**: `identity_card` (KTP) adalah unique key untuk `employees` table
-   **Multiple Administrations**: Satu employee bisa punya multiple `administrations` dengan NIK berbeda
-   **Business Rule**: Hanya boleh ada 1 active administration (`is_active = 1`) per employee
-   **History Preservation**: Data termination tetap tersimpan sebagai history dengan `is_active = 0`

**Implikasi untuk Struktur Excel**:

-   ✅ Struktur yang direkomendasikan (Master-Detail dengan Pivot Key) **kompatibel** dengan skenario rehire
-   ✅ Tidak akan terjadi duplikasi employee karena menggunakan `identity_card` sebagai key
-   ✅ Multiple administrations untuk employee yang sama dapat di-handle dengan baik
-   ✅ Data termination dapat diintegrasikan ke Sheet "Employees" tanpa masalah

#### 6.3.3 Validasi yang Diperlukan

Untuk memastikan integritas data dalam struktur baru:

1. **Validasi `identity_card`**: Pastikan konsisten di semua baris untuk employee yang sama
2. **Validasi NIK**: Harus berbeda untuk setiap administration
3. **Validasi `is_active`**: Hanya boleh ada 1 baris dengan `is_active = 1` per `identity_card`
4. **Validasi History**: Multiple baris dengan `is_active = 0` diperbolehkan untuk history termination

---

## 7. Next Steps

1. ✅ **Analisa selesai** - Dokumen ini
2. ⏳ **Review dengan stakeholder** - Diskusikan rekomendasi dengan HR
3. ⏳ **Prototype** - Buat sample Excel dengan struktur baru
4. ⏳ **User Testing** - Test dengan HR staff untuk feedback
5. ⏳ **Implementation** - Develop export/import dengan struktur baru
6. ⏳ **Migration** - Convert data existing ke format baru
7. ⏳ **Training** - Training untuk HR staff
8. ⏳ **Documentation** - User manual dan video tutorial

---

**Dokumen ini dibuat untuk**: Analisa dan rekomendasi struktur export/import karyawan  
**Tanggal**: 2025-12-02  
**Versi**: 1.0  
**Status**: ✅ Analisa Selesai - Siap untuk Review
