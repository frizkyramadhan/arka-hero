# Rencana Implementasi Fitur Grade dan Level

Dokumen ini menguraikan rencana untuk menambahkan fungsionalitas **Grade** dan **Level** ke dalam sistem, yang akan terintegrasi dengan modul **Administration** yang sudah ada.

## 1. Pendahuluan

Tujuan dari fitur ini adalah untuk mengklasifikasikan data administrasi karyawan berdasarkan Grade dan Level tertentu. Ini akan melibatkan pembuatan entitas baru untuk Grade dan Level, serta memperbarui struktur data dan antarmuka pengguna (UI) yang ada untuk mengakomodasi perubahan ini.

## 2. Perubahan Database

Kita akan membuat dua tabel baru (`grades` dan `levels`) dan memperbarui tabel `administrations` yang sudah ada.

### a. Migrasi Tabel `grades`

Tabel ini akan menyimpan semua data master untuk Grade.

-   **Nama File Migrasi:** `YYYY_MM_DD_HHMMSS_create_grades_table.php`
-   **Struktur Tabel:**
    -   `id` (Primary Key, BigInt, Unsigned, Auto-increment)
    -   `name` (String) -> Nama grade (e.g., "Grade A", "Grade B")
    -   `is_active` (Boolean, default true) -> Status aktif/tidak aktif
    -   `timestamps` (created_at, updated_at)

### b. Migrasi Tabel `levels`

Tabel ini akan menyimpan semua data master untuk Level.

-   **Nama File Migrasi:** `YYYY_MM_DD_HHMMSS_create_levels_table.php`
-   **Struktur Tabel:**
    -   `id` (Primary Key, BigInt, Unsigned, Auto-increment)
    -   `name` (String) -> Nama level (e.g., "Level 1", "Staff", "Manager")
    -   `is_active` (Boolean, default true) -> Status aktif/tidak aktif
    -   `timestamps` (created_at, updated_at)

### c. Update Migrasi Tabel `administrations`

Kita akan menambahkan dua kolom baru sebagai foreign key ke tabel `administrations`.

-   **Nama File Migrasi:** `YYYY_MM_DD_HHMMSS_add_grade_and_level_to_administrations_table.php`
-   **Perubahan:**
    -   Tambahkan `grade_id` (Foreign Key ke `grades.id`, nullable)
    -   Tambahkan `level_id` (Foreign Key ke `levels.id`, nullable)
    -   Kolom dibuat `nullable` untuk memastikan data yang ada tidak error.

```php
Schema::table('administrations', function (Blueprint $table) {
    $table->foreignId('grade_id')->nullable()->constrained('grades')->after('position_id');
    $table->foreignId('level_id')->nullable()->constrained('levels')->after('grade_id');
});
```

## 3. Model Eloquent

### a. Model `Grade`

-   **Lokasi:** `app/Models/Grade.php`
-   **Relasi:**
    -   `hasMany(Administration::class)`: Satu grade dapat dimiliki oleh banyak data administrasi.
-   **Scope:**
    -   `scopeActive($query)`: Untuk mengambil hanya grade yang aktif.

### b. Model `Level`

-   **Lokasi:** `app/Models/Level.php`
-   **Relasi:**
    -   `hasMany(Administration::class)`: Satu level dapat dimiliki oleh banyak data administrasi.
-   **Scope:**
    -   `scopeActive($query)`: Untuk mengambil hanya level yang aktif.

### c. Update Model `Administration`

-   **Lokasi:** `app/Models/Administration.php`
-   **Perubahan:**
    -   Tambahkan relasi `belongsTo` ke `Grade` dan `Level`.

```php
public function grade()
{
    return $this->belongsTo(Grade::class);
}

public function level()
{
    return $this->belongsTo(Level::class);
}
```

## 4. Controller

### a. `GradeController` & `LevelController` (Baru)

-   **Lokasi:** `app/Http/Controllers/GradeController.php` dan `app/Http/Controllers/LevelController.php`
-   **Fungsi:**
    -   Membuat Controller resource baru untuk mengelola operasi CRUD (Create, Read, Update, Delete) untuk Grade dan Level.
    -   Menambahkan metode untuk mengubah status `is_active`.
    -   Perlu dilengkapi dengan validasi request.

### b. `AdministrationController` (Update)

-   **Lokasi:** `app/Http/Controllers/AdministrationController.php`
-   **Perubahan:**
    -   **`store` & `update` methods:**
        -   Tambahkan validasi untuk `grade_id` dan `level_id`.
        -   Simpan `grade_id` dan `level_id` saat membuat atau memperbarui data administrasi.
    -   **`getAdministration` method (untuk DataTables):**
        -   Lakukan `join` dengan tabel `grades` dan `levels`.
        -   Tampilkan `grade.name` dan `level.name` di DataTables.
    -   **Method yang melayani view (e.g., `create`, `edit`):**
        -   Kirim data semua grade dan level yang **aktif** ke view agar bisa ditampilkan di form sebagai pilihan dropdown.

## 5. Routing

-   **Lokasi:** `routes/web.php`
-   **Perubahan:**
    -   Tambahkan resource routes untuk `GradeController` dan `LevelController`.
    -   Tambahkan route `POST` atau `PUT` untuk mengubah status aktif/tidak aktif.
    -   Pastikan rute ini dilindungi oleh middleware yang sesuai (misalnya, autentikasi admin).

```php
Route::resource('grades', GradeController::class);
Route::post('grades/status/{id}', [GradeController::class, 'changeStatus'])->name('grades.status');
Route::resource('levels', LevelController::class);
Route::post('levels/status/{id}', [LevelController::class, 'changeStatus'])->name('levels.status');
```

## 6. Views (Antarmuka Pengguna)

### a. CRUD Views untuk Grade & Level

-   Buat satu set view lengkap (index, create, edit) untuk Grade dan Level.
-   Lokasi: `resources/views/grades/` dan `resources/views/levels/`.
-   Tampilan harus konsisten dengan desain yang ada di aplikasi.
-   Tampilan `index` harus menyertakan indikator status (misalnya, badge) dan tombol untuk mengubah status.

### b. Update View Administrasi

-   **Lokasi:** `resources/views/administration/`
-   **Perubahan:**
    -   **`create.blade.php` & `edit.blade.php`:**
        -   Tambahkan dua field `<select>` (dropdown) baru: satu untuk memilih Grade dan satu lagi untuk Level. Pilihan dropdown ini harus diisi dengan data dari `GradeController` dan `LevelController`.
    -   **`index.blade.php` (atau file yang menampilkan datatable):**
        -   Tambahkan kolom baru di tabel untuk menampilkan "Grade" dan "Level".

### c. Update Navigasi / Sidebar

-   **Lokasi:** (kemungkinan di `resources/views/layouts/partials/sidebar.blade.php` atau sejenisnya)
-   **Perubahan:**
    -   Tambahkan link menu baru di sidebar untuk mengarah ke halaman manajemen Grade dan Level. Sebaiknya diletakkan di bawah menu "Master Data" atau sejenisnya.

## 7. Seeder (Opsional tapi direkomendasikan)

Untuk memudahkan pengembangan dan testing, buat seeder untuk data awal.

-   `database/seeders/GradeSeeder.php`
-   `database/seeders/LevelSeeder.php`
-   Panggil seeder ini dari `DatabaseSeeder.php`.

## 8. Rangkuman Langkah Implementasi

Berikut adalah checklist tugas yang perlu dilakukan:

-   [x] 1. Buat migrasi untuk tabel `grades` (dengan kolom `is_active`).
-   [x] 2. Buat migrasi untuk tabel `levels` (dengan kolom `is_active`).
-   [x] 3. Jalankan migrasi untuk membuat tabel di database.
-   [x] 4. Buat migrasi untuk menambahkan `grade_id` dan `level_id` ke `administrations`.
-   [x] 5. Jalankan migrasi kedua.
-   [x] 6. Buat model `Grade.php` dengan relasi dan scope `active`.
-   [x] 7. Buat model `Level.php` dengan relasi dan scope `active`.
-   [x] 8. Update model `Administration.php` dengan relasi `belongsTo`.
-   [x] 9. Buat `GradeController` dan `LevelController` (resourceful) dengan fungsi `changeStatus`.
-   [x] 10. Tambahkan rute untuk `grades` dan `levels` di `web.php`, termasuk rute untuk `changeStatus`.
-   [x] 11. Buat views CRUD untuk Grades (`resources/views/grades/`), termasuk indikator status.
-   [x] 12. Buat views CRUD untuk Levels (`resources/views/levels/`), termasuk indikator status.
-   [x] 13. Update `AdministrationController` & `EmployeeController` untuk menangani `grade_id`, `level_id`, dan hanya mengambil grade/level yang aktif.
-   [x] 14. Update views `administration` & `employee` (`create`, `edit`, `index`, `print`) untuk menyertakan field dan kolom Grade/Level.
-   [x] 15. Update menu sidebar dengan link ke manajemen Grade dan Level.
-   [x] 16. Buat `GradeLevelSeeder` untuk mengisi data awal.
-   [x] 17. Lakukan testing menyeluruh pada fungsionalitas CRUD Grade, Level, dan Administrasi, termasuk perubahan status.
-   [x] 18. Sesuaikan fungsionalitas Ekspor dan Impor Excel untuk menyertakan data Grade dan Level.

## 9. Ringkasan Implementasi

Fitur Grade dan Level telah berhasil diimplementasikan dan diintegrasikan ke dalam sistem. Berikut adalah ringkasan dari pekerjaan yang telah diselesaikan:

1.  **Modul Grade & Level:**

    -   Fungsionalitas CRUD penuh telah dibuat untuk Grade dan Level.
    -   Antarmuka pengguna menggunakan DataTables untuk menampilkan data, dengan form tambah/edit yang muncul dalam modal untuk pengalaman pengguna yang lebih baik.
    -   Fitur untuk mengubah status (aktif/tidak aktif) telah diimplementasikan.
    -   Link navigasi baru telah ditambahkan ke sidebar untuk akses mudah.

2.  **Integrasi dengan Karyawan & Administrasi:**

    -   Formulir pembuatan karyawan baru sekarang menyertakan dropdown untuk memilih Grade dan Level.
    -   Modal untuk menambah dan mengedit data administrasi karyawan (di halaman detail karyawan) juga telah diperbarui dengan dropdown Grade dan Level.
    -   Tabel riwayat pekerjaan di halaman detail dan halaman cetak karyawan kini menampilkan kolom Grade dan Level.

3.  **Pembaruan DataTables & Controller:**

    -   Halaman daftar karyawan (`/employees`) dan administrasi (`/administrations`) sekarang menampilkan kolom Grade dan Level.
    -   Filter berdasarkan Grade dan Level telah ditambahkan ke halaman daftar karyawan.
    -   `EmployeeController` dan `AdministrationController` telah disesuaikan untuk mengambil dan memproses data terkait.

4.  **Seeder & Data Awal:**

    -   Sebuah `GradeLevelSeeder` telah dibuat dan dijalankan untuk mengisi database dengan data awal untuk Grade dan Level, mempercepat proses pengembangan dan testing.

5.  **Fungsi Ekspor & Impor:**
    -   Fungsi ekspor data karyawan ke Excel sekarang menyertakan kolom untuk Grade dan Level.
    -   Fungsi impor dari Excel juga telah disesuaikan untuk dapat memvalidasi dan menyimpan data Grade dan Level dari file yang diunggah.
    -   Perbaikan telah dilakukan untuk mengatasi error "ambiguous column" yang terjadi saat ekspor.
