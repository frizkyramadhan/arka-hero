# Letter Administration

Panduan ini untuk **staf HR** dan petugas administrasi yang mengelola **nomor surat** di ARKA HERO. Modul **Letter Administration** berada di **GENERAL SECTION**.

Nomor surat yang dibuat di sini dipakai oleh modul lain (misalnya **Official Travel**, **Recruitment**, **Flight Management**) lewat pemilihan **Letter Number** pada formulir terkait. Pengaturan **kategori** dan **subjek** surat ada di **Master Data** → **Letter Management Data** (lihat bab _Master Data_, bagian Letter Categories).

**Catatan peran:** Menu **Dashboard** dan **Letter Numbers** hanya tampil jika akun Anda memiliki hak akses modul nomor surat. Daftar nomor surat dibatasi menurut **project** yang terhubung ke akun Anda.

---

## Glosarium

| **Istilah**                | Arti singkat                                                                                                                                                                              |
| -------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Letter Administration**  | Grup menu di **GENERAL SECTION** untuk dashboard penomoran dan daftar **Letter Numbers**.                                                                                                 |
| **Letter Number**          | Nomor surat unik yang dihasilkan sistem; format umum `**[KODE_KATEGORI][URUTAN]`** (misalnya **A0001**, **B0042**, **FR0123**). Urutan empat digit per **project** dan **tahun\*\* surat. |
| **Letter Category**        | Jenis/kelompok surat (kode **A**, **B**, **PKWT**, **PAR**, **FR**, dll.); menentukan perilaku penomoran dan field tambahan pada formulir.                                                |
| **Letter Subject**         | Subjek master per kategori; dipilih dari dropdown atau diganti dengan **Custom Subject**.                                                                                                 |
| **Reserved**               | Status nomor surat baru: siap dipakai, masih dapat **Edit**, **Cancel**, atau **Delete** (jika belum terhubung dokumen).                                                                  |
| **Used**                   | Nomor sudah dipakai pada dokumen modul lain atau ditandai manual; tidak dapat diubah lagi.                                                                                                |
| **Cancelled**              | Nomor dibatalkan; tidak boleh dipakai untuk dokumen baru.                                                                                                                                 |
| **Annual Reset**           | Perilaku penomoran: urutan kembali ke **1** setiap tahun kalender.                                                                                                                        |
| **Continuous**             | Perilaku penomoran: urutan berlanjut tanpa reset tahunan.                                                                                                                                 |
| **Estimated Next Numbers** | Pratinjau nomor berikutnya per kategori di dashboard, berdasarkan project dan tahun berjalan.                                                                                             |
| **Related Document**       | Dokumen sistem (LOT, FPTK, LG, dll.) yang memakai nomor surat; tercatat di detail **Letter Number**.                                                                                      |

---

<br>
<br>

## 1. Ringkasan Menu

| **Menu**              | **Navigasi (sidebar)**                                                                     | **Uraian**                                                                                                                      |
| --------------------- | ------------------------------------------------------------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------- |
| **Dashboard**         | **GENERAL SECTION** → **Letter Administration** → **Dashboard**                            | Ringkasan statistik, perkiraan nomor berikutnya per kategori, aktivitas terbaru, pintasan **Create** / **Import** / **Export**. |
| **Letter Numbers**    | **GENERAL SECTION** → **Letter Administration** → **Letter Numbers**                       | Daftar seluruh nomor surat (sesuai project Anda); filter, buat, ubah, batalkan, hapus, ekspor, impor.                           |
| **Letter Categories** | **GENERAL SECTION** → **Master Data** → **Letter Management Data** → **Letter Categories** | Master kategori & subjek surat (bukan operasi penomoran harian).                                                                |

---

## 2. Letter Administration Dashboard

Halaman **Letter Administration Dashboard** (_Letter Numbering System Overview_) memberi gambaran volume nomor surat, perkiraan nomor berikutnya per kategori, serta pintasan operasi sebelum Anda membuka **Letter Numbers**.

### Langkah-langkah — membuka **Letter Administration Dashboard**

1. **Login** ke ARKA HERO.
2. Buka **GENERAL SECTION** → **Letter Administration** → **Dashboard**.
3. Baca tiap bagian dashboard secara berurutan: [ringkasan metrik](#21-letter-administration-summary) → [perkiraan nomor berikutnya](#22-estimated-next-numbers) → [tabel kategori & aktivitas terbaru](#23-letter-categories-dan-recent-letter-numbers) → [pintasan operasi](#24-quick-actions).

### 2.1 Letter Administration Summary

Kartu paling atas berjudul **Letter Administration Summary** (_Overview & Key Metrics_) menampilkan empat metrik utama:

- **Total Letters** — jumlah seluruh nomor surat dalam sistem.
- **Reserved** — nomor berstatus **Reserved** (belum terpakai).
- **Used** — nomor yang sudah dipakai.
- **This Month** — nomor yang dibuat pada bulan berjalan.

Tombol **View All Letters** di pojok kanan header kartu membuka menu **Letter Numbers**.

<p align="center" id="letter-administration-dashboard-summary">
    <img
        src="images/letter-administration-dashboard-summary.png"
        alt="Letter Administration Dashboard — judul breadcrumb Home Letter Numbering System Overview, kartu Letter Administration Summary Overview Key Metrics Total Letters Reserved Used This Month tombol View All Letters"
        style="max-width: 90%; width: 90%; height: auto;"
    />
<br><em>Gambar 2.1 — Letter Administration Summary (metrik Total Letters, Reserved, Used, This Month)</em>
</p>

### 2.2 Estimated Next Numbers

Panel **Estimated Next Numbers** (_Current Year: …_) berisi grid kartu per **Letter Category**. Setiap kartu menampilkan:

- **Next Available Number** — pratinjau nomor berikutnya (tahun berjalan).
- **Sequence** dan **Year** — urutan internal dan tahun penomoran.
- Badge **Annual Reset** atau **Continuous** — perilaku penomoran kategori.
- Jumlah **letters** dan **Recent Numbers** (hingga tiga nomor terakhir).
- Tombol **View All** (filter daftar per kategori) dan **Create** (form buat nomor untuk kategori itu).

Panel dapat dilipat dengan ikon **−** di header kartu.

<p align="center" id="letter-administration-dashboard-estimated">
    <img
        src="images/letter-administration-dashboard-estimated-next-numbers.png"
        alt="Panel Estimated Next Numbers Current Year 2026 — grid kartu kategori A Surat Eksternal B Surat Internal CRTE FPTK FR MEMO PAR PKWT SKPK SPM dengan Next Available Number Sequence Year badge Annual Reset Continuous 0 letters Updated View All Create"
        style="max-width: 90%; width: 90%; height: auto;"
    />
<br><em>Gambar 2.2 — Estimated Next Numbers (pratinjau nomor berikutnya per Letter Category)</em>
</p>

### 2.3 Letter Categories dan Recent Letter Numbers

Di bawah grid perkiraan nomor, dua tabel berdampingan:

**Letter Categories** (kiri) — daftar kategori dengan kolom **Category**, **Total**, dan **Status** (badge **R** = **Reserved**, **U** = **Used**, **C** = **Cancelled**). Pagination tersedia di bawah tabel.

**Recent Letter Numbers** (kanan) — nomor surat terbaru dengan kolom **Letter #**, **Category**, **Status**, **Created**, dan **Action** (tombol **View** untuk membuka detail). Pagination tersedia di bawah tabel.

### 2.4 Quick Actions

Empat kartu pintasan di bagian bawah halaman:

- **Create New** — **Generate letter number** → tombol **Create Letter Number**
- **Import** — **Bulk import letters** → tombol **Import Excel**
- **Export** — **Download report** → tombol **Export Excel**
- **Manage** — **Letter categories** → tombol **Manage Categories** (menu **Letter Categories** di **Master Data**)

<p align="center" id="letter-administration-dashboard-tables-actions">
    <img
        src="images/letter-administration-dashboard-tables-quick-actions.png"
        alt="Dashboard Letter Administration — tabel Letter Categories Category Total Status badge R U C pagination, tabel Recent Letter Numbers Letter Category Status Used Reserved Created Action View, Quick Actions Create New Import Export Manage dengan tombol Create Letter Number Import Excel Export Excel Manage Categories"
        style="max-width: 90%; width: 90%; height: auto;"
    />
<br><em>Gambar 2.3 — Letter Categories, Recent Letter Numbers, dan Quick Actions</em>
</p>

**Catatan:**

- Statistik dashboard mencakup data global; daftar **Letter Numbers** tetap difilter menurut **project** akun Anda.
- Kategori **CRTE**, **PKWT**, dan **SKPK** hanya tampil bagi pengguna yang terhubung ke project **000H**.
- Panel **Estimated Next Numbers** dapat dilipat dengan ikon **−** di header kartu.

---

## 3. Letter Numbers — daftar dan filter

### Langkah-langkah — membuka daftar **Letter Numbers**

1. **Login** ke ARKA HERO.
2. Buka **GENERAL SECTION** → **Letter Administration** → **Letter Numbers**.
3. Judul halaman: **Letter Number Administration**; subjudul kartu: **Letter Numbers List**.
4. Di pojok kanan atas kartu daftar tersedia:

- **Export** — unduh data ke Excel.
    - **Import** — buka modal impor Excel.
    - **Add** — buat nomor surat baru.

5. Buka panel **Filter** (bilah biru dengan ikon corong) untuk menyaring data. Filter diterapkan otomatis saat Anda mengubah isian; **Reset Filter** mengosongkan semua filter.

| **Filter**                  | **Fungsi**                                                         |
| --------------------------- | ------------------------------------------------------------------ |
| **Date From** / **Date To** | Rentang **Letter Date**.                                           |
| **Project**                 | Batasi ke satu **project** (hanya project yang Anda miliki akses). |
| **Letter Category**         | Kode dan nama kategori.                                            |
| **Letter Number**           | Pencarian teks sebagian nomor.                                     |
| **Destination**             | Pencarian tujuan/alamat surat.                                     |
| **Remarks**                 | Pencarian catatan.                                                 |
| **Status**                  | **Reserved**, **Used**, atau **Cancelled**.                        |

1. Tabel menampilkan kolom **No**, **Project**, **Letter Number**, **Category**, **Subject**, **Date**, **Destination**, **Remarks**, **Status** (badge warna), dan **Actions**.

<p align="center" id="letter-numbers-index">
    <img
        src="images/letter-numbers-index.png"
        alt="Halaman Letter Numbers List — tombol Export Import Add panel Filter biru tabel No Project Letter Number Category Subject Date Destination Remarks Status badge Used Reserved Actions View Edit Cancel Delete pagination Showing entries"
        style="max-width: 90%; width: 90%; height: auto;"
    />
<br><em>Gambar 3.1 — Daftar Letter Numbers dengan filter dan tombol Export, Import, Add</em>
</p>

**Actions** pada setiap baris (sesuai status dan hak akses):

- **View** (ikon mata) — detail nomor surat.
- **Edit** (ikon pensil) — hanya jika status **Reserved**.
- **Cancel** (ikon larangan) — batalkan nomor **Reserved**.
- **Delete** (ikon tempat sampah) — hanya **Reserved** dan belum terhubung dokumen.
- **View Document** (ikon tautan hijau) — jika nomor sudah dipakai modul lain (misalnya LOT).

Indikator **Filters Applied** muncul di bawah filter bila ada filter aktif; **Quick Reset** mengosongkan filter tanpa meninggalkan halaman.

---

## 4. Membuat nomor surat — **Create Letter Number**

Anda dapat membuka form dari **Add** pada daftar, **Create Letter Number** / **Create** di dashboard, atau tautan **Create** pada kartu kategori di **Estimated Next Numbers** (kategori sudah terpilih).

### Langkah-langkah — **Create Letter Number** (informasi dasar)

1. Klik **Add** atau pintasan **Create** dari dashboard.
2. Pada kartu **Basic Information**, isi field berikut:

**1. Project** (wajib)

Pilih **Project** dari dropdown. Setiap project memiliki urutan nomor sendiri. Teks bantuan: _Each project has its own sequence number. Required for generating letter numbers._

**2. Letter Category** (wajib)

Pilih kategori (misalnya **A - Surat Eksternal**, **B - Surat Internal**, **FR - Form Request Tiket**). Jika Anda masuk lewat kartu kategori di dashboard, field ini sudah terisi dan tidak dapat diubah.

Setelah **Project** dan **Letter Category** dipilih, panel **Next Number Preview** menampilkan pratinjau nomor berikutnya dan **Sequence**.

**3. Letter Date** (wajib)

Tanggal surat; default hari ini. Tahun penomoran mengikuti tanggal ini.

**4. Subject** / **Custom Subject**

Pilih **Subject** dari master subjek kategori, atau isi **Custom Subject** jika subjek tidak ada di daftar.

**5. Destination/Address/Project** dan **Remarks**

Opsional; untuk tujuan surat dan catatan internal.

1. Lengkapi **field dinamis** sesuai kategori (lihat [bagian 4.1](#41-field-dinamis-per-letter-category) di bawah).
2. Panel kanan **Additional Information** menjelaskan bahwa nomor dihasilkan otomatis, format `**[CATEGORY][SEQUENCE]`**, dan status awal **Reserved\*\*.
3. Klik **Save Letter Number**. Pesan sukses menampilkan nomor yang terbentuk; Anda kembali ke daftar **Letter Numbers**.

<p align="center" id="letter-numbers-create">
    <img
        src="images/letter-numbers-create-basic.png"
        alt="Form Create Letter Number — breadcrumb Letter Administration Basic Information Next Number Preview B0139 Sequence 139 Project 000H Letter Category B Surat Internal Letter Date Subject Custom Subject Destination Remarks Additional Information auto-generated Reserved Save Letter Number Back"
        style="max-width: 85%; width: 85%; height: auto;"
    />
<br><em>Gambar 4.1 — Create Letter Number: informasi dasar dan pratinjau nomor berikutnya</em>
</p>

### 4.1 Field dinamis per **Letter Category**

Setelah **Letter Category** dipilih, kartu tambahan muncul di bawah **Basic Information** sesuai kode kategori:

| **Kategori**             | **Kartu / field tambahan**             | **Keterangan**                                                                                                             |
| ------------------------ | -------------------------------------- | -------------------------------------------------------------------------------------------------------------------------- |
| **A**                    | **Letter Classification**              | **Classification**: **Umum**, **Lembaga Pendidikan**, atau **Pemerintah** (opsional di form; wajib saat impor kategori A). |
| **B**                    | —                                      | Tidak ada kartu khusus di form create (klasifikasi hanya kategori A).                                                      |
| **PKWT**                 | **Agreement Data** + **Employee Data** | **Type** (**PKWT** / **PKWTT**, wajib), **Duration**, **Start Date**, **End Date**; **Select Employee** opsional.          |
| **PAR**                  | **PAR Data** + **Employee Data**       | **PAR Type** (wajib): **New Hire**, **Promosi**, **Mutasi**, **Demosi**; **Select Employee** opsional.                     |
| **CRTE**, **SKPK**       | **Employee Data**                      | **Select Employee** opsional; NIK dan project karyawan terisi otomatis saat dipilih.                                       |
| **FR**                   | **Ticket Classification Data**         | **Ticket Classification** (wajib): **Pesawat**, **Kereta Api**, atau **Bus**.                                              |
| **MEMO**, **FPTK**, dll. | —                                      | Hanya field dasar; tanpa kartu dinamis khusus di form.                                                                     |

<p align="center" id="letter-numbers-create-pkwt">
    <img
        src="images/letter-numbers-create-pkwt.png"
        alt="Create Letter Number kategori PKWT — Basic Information Next Number Preview PKWT6401 Agreement Data Type PKWT PKWTT Duration Start Date End Date Employee Data Select Employee NIK Project Additional Information Save Letter Number"
        style="max-width: 85%; width: 85%; height: auto;"
    />
<br><em>Gambar 4.2 — Contoh field dinamis kategori PKWT (Agreement Data dan Employee Data)</em>
</p>

**Catatan:**

- Memilih karyawan pada **Employee Data** dapat mengisi otomatis **Project** sesuai penempatan karyawan.
- Kategori **CRTE**, **PKWT**, **SKPK** hanya dapat dibuat oleh pengguna dengan akses project **000H**.
- Nomor surat **FR** umumnya dipakai saat menerbitkan **Letter of Guarantee** di modul **Flight Management** (lihat bab _Flight Management_).

---

## 5. Detail, ubah, dan aksi nomor surat

### Langkah-langkah — melihat detail **Letter Number Details**

1. Dari daftar, klik ikon **View** pada baris nomor surat.
2. Halaman **Letter Number Details** menampilkan:

- **Letter Number Information** — nomor, kategori, tanggal, subjek, tujuan, pembuat, tanggal pakai (jika **Used**), **Remarks**, badge status.
    - **Employee Data** — jika ada karyawan terkait (NIK, nama, project, jabatan, DOH).
    - **PKWT Data**, **PAR Data**, **Letter Classification**, atau **Ticket Classification** — sesuai kategori.
    - **Integration Information** — jika nomor sudah dipakai dokumen lain: tipe dokumen, ID, tanggal pakai, tombol **View …** (misalnya **View Letter of Guarantee**, **View Official Travel Letter**) — lihat [bagian 7](#7-integrasi-dengan-modul-lain).

<p align="center" id="letter-numbers-detail">
    <img
        src="images/letter-numbers-detail-reserved.png"
        alt="Letter Number Details — badge RESERVED Letter Number B0056 Category Surat Internal Letter Date Subject Destination Created By Actions Back to List Edit Data Cancel Number Mark as Used Manual Delete Status Information How to Use"
        style="max-width: 90%; width: 90%; height: auto;"
    />
<br><em>Gambar 5.1 — Detail nomor surat berstatus Reserved dengan panel Actions</em>
</p>

### Langkah-langkah — **Edit Data** (nomor Reserved)

1. Dari detail atau daftar, klik **Edit** / **Edit Data** (hanya status **Reserved**).
2. Ubah field yang diizinkan (sama seperti form create, kecuali nomor dan kategori jika sudah terkunci).
3. Klik **Update Letter Number** / **Save** untuk menyimpan.

Jika status sudah **Used** atau **Cancelled**, sistem menolak pengeditan dengan pesan _Letter number cannot be edited because it has been used or cancelled_.

### Aksi lain pada nomor **Reserved**

| **Tombol / aksi**         | **Kapan tersedia**                           | **Dampak**                                                                     |
| ------------------------- | -------------------------------------------- | ------------------------------------------------------------------------------ |
| **Cancel Number**         | Status **Reserved**                          | Status menjadi **Cancelled**; konfirmasi **Confirm Cancel** di jendela pop-up. |
| **Mark as Used (Manual)** | **Reserved**, belum ada **Related Document** | Menandai nomor terpakai tanpa dokumen sistem; tidak dapat dibatalkan.          |
| **Delete**                | **Reserved**, belum terhubung dokumen        | Menghapus permanen nomor; konfirmasi **Confirm Delete**.                       |

Panel **Status Information** di sisi kanan menjelaskan makna status dan petunjuk **How to Use** untuk nomor **Reserved**: buat dokumen di modul terkait lalu pilih nomor dari dropdown **Letter Number**.

---

## 6. Ekspor dan impor Excel

### Langkah-langkah — **Export**

1. Dari **Letter Numbers** atau dashboard, klik **Export** / **Export Excel**.
2. Berkas Excel terunduh (nama contoh: `letter-numbers-2026-05-21.xlsx`) berisi kolom antara lain: **id**, **year**, **project_code**, **letter_number**, **sequence_number**, **category_code**, **letter_date**, **status**, **destination**, **subject_master**, **nik**, **pkwt_type**, **par_type**, **ticket_classification**, dan lain-lain.
3. Gunakan hasil ekspor sebagai **template** struktur kolom atau arsip data.

### Langkah-langkah — **Import**

1. Klik **Import** / **Import Excel** (daftar atau dashboard).
2. Pada modal **Import Letter Numbers** / **Import Data Nomor Surat**, pilih berkas **.xls** atau **.xlsx**, lalu klik **Import**.
3. Jika semua baris valid, muncul pesan **Data imported successfully!** dan daftar diperbarui.
4. Jika ada baris gagal, kartu merah **Import Validation Errors** menampilkan tabel **Sheet**, **Row**, **Column**, **Value**, **Error Message**. Perbaiki berkas Excel sesuai pesan, lalu impor ulang.

### 6.1 Katalog validasi impor (contoh pesan)

| **Jenis**     | **Contoh pesan**                                                                          | **Yang dicek**                                         |
| ------------- | ----------------------------------------------------------------------------------------- | ------------------------------------------------------ |
| Kategori      | _Category Code is required._ / _The selected Category Code is invalid._                   | Kode kategori ada dan aktif.                           |
| Tanggal       | _The Letter Date must be a valid date._                                                   | **letter_date** dapat diparse.                         |
| Klasifikasi A | _Classification is required for category A._                                              | Kategori **A** wajib **classification**.               |
| PKWT          | _PKWT Type is required for category PKWT._ / _The end date must be after the start date._ | **pkwt_type**, **start_date**, **end_date** konsisten. |
| PAR           | _PAR Type is required for category PAR._                                                  | **par_type** terisi.                                   |
| FR            | _Ticket Classification is required for category FR._                                      | **ticket_classification** terisi.                      |
| Subjek        | _The subject '…' is not valid for category '…'._                                          | **subject_master** cocok dengan subjek kategori.       |
| Status        | _The Status must be one of: reserved, used, cancelled._                                   | Nilai **status** valid.                                |
| Berkas        | _Please select a file to import._ / _The file must be a file of type: xls, xlsx._         | Berkas dipilih dan bertipe Excel.                      |

Untuk daftar lengkap error impor, lihat tabel **Import Validation Errors** setelah unggah; perbaiki baris yang disebutkan sebelum mengimpor ulang.

---

## 7. Integrasi dengan modul lain

Nomor surat **Reserved** dapat dipilih saat membuat atau mengedit dokumen di modul berikut. Setelah dokumen disimpan, status nomor biasanya berubah menjadi **Used** dan muncul di **Integration Information**.

| **Modul**                     | **Pemilihan nomor**                                                           | **Kategori umum**            |
| ----------------------------- | ----------------------------------------------------------------------------- | ---------------------------- |
| **Official Travel (LOT)**     | Bagian **Letter Number** pada form LOT; **Create New** membuka form yang sama | **B** (Surat Internal)       |
| **Recruitment (FPTK)**        | Pemilihan nomor surat pada permintaan rekrutmen                               | **FPTK**                     |
| **Flight Management (LG)**    | **Letter Number** saat **Create Letter of Guarantee**                         | **FR**                       |
| **PKWT / offering rekrutmen** | Otomatis atau manual sesuai alur rekrutmen                                    | **PKWT**, **CRTE**, **SKPK** |

### Langkah-langkah — melihat **Integration Information**

1. Buka detail nomor surat (**Letter Number Details**) yang sudah berstatus **Used** (badge hijau).
2. Di bawah **Letter Number Information**, baca kartu tambahan sesuai kategori (misalnya **Ticket Classification** untuk kategori **FR**).
3. Kartu **Integration Information** menampilkan:
    - Pesan **Letter Number Already Used** — nomor sudah terhubung dokumen sistem.
    - **Document Type** — jenis dokumen sumber (misalnya penerbitan **Letter of Guarantee** / LG).
    - **Document ID** — identifikasi dokumen terkait.
    - **Usage Date** — waktu nomor ditandai terpakai.
4. Klik **View …** (misalnya **View Letter of Guarantee**, **View Official Travel Letter**, **View Recruitment Session**) untuk membuka halaman dokumen terkait.
5. Panel **Status Information** di kanan menampilkan **Status: Used** — nomor tidak dapat diubah lagi; tombol **Actions** hanya **Back to List**.

| **Document Type** (contoh di layar) | **Tombol tautan** | **Halaman tujuan** |
| :---------------------------------- | :---------------- | :----------------- |
| `Officialtravel` | **View Official Travel Letter** | Detail LOT |
| `Recruitment_request` | **View Recruitment Request** | Detail FPTK |
| `Recruitment_offering` / `Recruitment_hiring` | **View Recruitment Session** | Detail sesi kandidat (rekrutmen) |
| `Flight_request_issuance` | **View Letter of Guarantee** | Detail LG (**Flight Management** → **Issuances**) |
| `Employee_bond` | **View Employee Bond** | Detail employee bond |

<p align="center" id="letter-numbers-detail-integration">
    <img
        src="images/letter-numbers-detail-integration.png"
        alt="Letter Number Details status USED FR0127 Ticket Classification Pesawat Integration Information Letter Number Already Used Document Type Document ID Usage Date tombol View Document Status Information Used"
        style="max-width: 80%; width: 80%; height: auto;"
    />
<br><em>Gambar 7.1 — Detail nomor surat terintegrasi (contoh kategori FR dipakai Letter of Guarantee)</em>
</p>

**Catatan:** Alur pemilihan **Letter Number** pada LOT dijelaskan juga di bab _Official Travel Management_ (bagian formulir **Letter Number**). Modul **Letter Administration** fokus pada pembuatan, persediaan, dan monitoring nomor; pemakaian di formulir modul lain tidak menggantikan pencatatan di sini.

---

## 8. Master data terkait (Letter Categories)

Sebelum penomoran rutin, pastikan **Letter Categories** dan **Letter Subjects** sudah benar di **Master Data** → **Letter Management Data**. Setiap kategori memiliki **Numbering Behavior** (**Annual Reset** / **Continuous**) yang mempengaruhi urutan di dashboard **Estimated Next Numbers**.

### Langkah-langkah — membuka **Letter Categories**

1. Buka **GENERAL SECTION** → **Master Data** → **Letter Management Data** → **Letter Categories** (atau klik **Manage Categories** dari **Quick Actions** di dashboard).
2. Judul halaman: **Letter Categories**; subjudul kartu: **List of Letter Categories**.
3. Tabel menampilkan kolom **No**, **Code**, **Category Name**, **Description**, **Behavior** (**Annual Reset** / **Continuous**), **Subjects** (jumlah subjek), **Status**, dan **Actions** (ikon **Manage Subjects**, **Edit**, **Delete**).
4. Klik **Add Category** untuk kategori baru; dari kolom **Subjects** atau ikon daftar, buka **Letter Subjects** per kategori.

Ringkasan setup:

1. **Letter Categories** — kode, nama, perilaku penomoran, status aktif.
2. **Letter Subjects** — subjek per kategori (**Manage Subjects** dari baris kategori).
3. Jangan hapus kategori/subjek yang masih dipakai nomor aktif.

<p align="center" id="letter-administration-letter-categories">
    <img
        src="images/letter-administration-letter-categories.png"
        alt="Halaman Letter Categories List — breadcrumb Master Data tombol Add Category tabel Code Category Name Description Behavior Annual Reset Continuous Subjects Status Active Actions Manage Subjects Edit Delete Search pagination"
        style="max-width: 90%; width: 90%; height: auto;"
    />
<br><em>Gambar 8.1 — Daftar Letter Categories di Master Data</em>
</p>

Detail field lengkap dan pengelolaan subjek dijelaskan juga di bab _Master Data_, bagian **Letter Management Data**.

---

## 9. Kesalahan & bantuan

| **Gejala / pesan (contoh)**                                            | **Kemungkinan penyebab**                                      | **Apa yang bisa dicoba**                                                                                                       |
| ---------------------------------------------------------------------- | ------------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------ |
| Menu **Letter Administration** tidak muncul                            | Akun tanpa hak akses nomor surat                              | Minta administrator menambahkan izin modul **Letter Numbers**.                                                                 |
| Daftar nomor kosong padahal ada data                                   | Filter project/status aktif; project akun tidak mencakup data | Reset filter; pastikan akun terhubung ke **project** yang benar.                                                               |
| Tidak bisa pilih kategori **PKWT** / **CRTE** / **SKPK**               | Akun tidak terhubung project **000H**                         | Hubungi administrator project/akses HO.                                                                                        |
| _Letter number cannot be edited because it has been used or cancelled_ | Status bukan **Reserved**                                     | Buat nomor baru; nomor **Used**/**Cancelled** tidak dapat diubah.                                                              |
| _Letter number cannot be deleted because it is linked to a document_   | Nomor sudah dipakai modul lain                                | Buka dokumen terkait dari **Integration Information** atau **View Document**.                                                  |
| _You do not have access to this letter number_                         | Nomor milik project di luar akses Anda                        | Gunakan akun dengan akses project yang sama atau minta penyesuaian hak.                                                        |
| **Next Number Preview** tidak muncul                                   | **Project** atau **Letter Category** belum dipilih            | Lengkapi kedua field; refresh halaman jika perlu.                                                                              |
| Impor gagal banyak baris                                               | Format kolom, kategori, atau field wajib per kategori salah   | Baca **Import Validation Errors** ([bagian 6.1](#61-katalog-validasi-impor-contoh-pesan)); bandingkan dengan hasil **Export**. |
| Nomor tidak muncul di dropdown modul lain                              | Status bukan **Reserved** atau project tidak cocok            | Buat nomor baru **Reserved** untuk project yang sama; cek filter di pemilih nomor.                                             |

### Menghubungi administrator

Siapkan **username** (bukan password), waktu kejadian, menu yang dibuka (**Dashboard** / **Letter Numbers**), **project**, **Letter Category**, nomor surat jika ada, serta cuplikan pesan error atau baris **Import Validation Errors**. Administrator dapat mengecek hak akses project, kategori terbatas **000H**, dan integrasi dokumen terkait.
