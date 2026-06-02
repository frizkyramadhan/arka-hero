# Overtime Management

<div style="text-align: justify; text-justify: inter-word;">

Panduan ini menjelaskan modul **Overtime Management** di ARKA HERO: ringkasan dan pengelolaan permintaan lembur untuk **staf HR** (menu grup **Overtime Management** di **HERO SECTION**), serta pengajuan lembur mandiri oleh **semua karyawan** lewat **My Features** → **My Overtime Request**.

**Catatan peran:** Menu **Dashboard**, **Requests**, dan **Reports** hanya tampil jika akun Anda memiliki hak akses pengelolaan lembur (umumnya HR). Menu **My Overtime Request** tersedia untuk karyawan yang berhak mengajukan atau melihat permintaan lembur sendiri.

---

## Glosarium

| **Istilah**             | Arti singkat                                                                                                                 |
| :---------------------- | :--------------------------------------------------------------------------------------------------------------------------- |
| **Overtime Requests**   | Halaman daftar permintaan lembur (HR).                                                                                       |
| **My Overtime Request** | Submenu **My Features** bagi karyawan untuk mengajukan dan memantau lembur sendiri.                                          |
| **Register No.**        | Nomor register permintaan lembur, format **YYOT-xxxxx** (misalnya **26OT-00002**); dibuat otomatis saat permintaan disimpan. |
| **Approver Selection**  | Pemilihan approver manual (biasanya **dua** orang berurutan) sebelum pengajuan.                                              |
| **Close Request**       | Tindakan HR pada permintaan **Approved** untuk menandai selesai (modal **Mark as finished**).                                |
| **Submit for Approval** | Mengajukan permintaan **Draft** ke alur approval (hanya dari status draft).                                                 |

---

## 1. Ringkasan Menu

| **Menu**                | **Navigasi (sidebar)**                                     | **Uraian**                                                                                                                       |
| :---------------------- | :--------------------------------------------------------- | :------------------------------------------------------------------------------------------------------------------------------- |
| **Dashboard**           | **HERO SECTION** → **Overtime Management** → **Dashboard** | Ringkasan status lembur, tren bulan ini, proyek teratas, lembur mendatang, dan permintaan terbaru.                               |
| **Requests**            | **HERO SECTION** → **Overtime Management** → **Requests**  | Daftar seluruh permintaan lembur (sesuai project yang dapat diakses akun); filter, tambah, ubah, ajukan, tutup.                  |
| **Reports**             | **HERO SECTION** → **Overtime Management** → **Reports**   | Pintu masuk laporan; saat ini **Overtime Request Report** dengan filter dan ekspor Excel.                                        |
| **My Overtime Request** | **My Features** → **My Overtime Request**                  | Self-service karyawan: daftar, buat, ubah, ajukan, dan lihat detail permintaan lembur sendiri atau yang diikuti sebagai peserta. |

---

## 2. Untuk HR — Dashboard

### Langkah-langkah — membuka **Overtime Management Dashboard**

1. **Login** ke ARKA HERO.
2. Di sidebar, buka **HERO SECTION** → **Overtime Management** → **Dashboard**.
3. Judul halaman: **Overtime Management Dashboard**; breadcrumb aktif: **Overtime requests overview**.

<p align="center" id="overtime-dashboard">
    <img
        src="images/overtime-management-dashboard.png"
        alt="Overtime Management Dashboard — breadcrumb Home Overtime requests overview; Status overtime Total 6 Draft Pending Approved Rejected Finished; metrik Dibuat bulan ini Tanggal OT Approved bisa ditutup Langkah approval terbuka; Top projects by volume; tabel Upcoming overtime pending approved dan Recently created dengan ikon View"
        style="max-width: 90%; width: 90%; height: auto;"
    />
    <br><em>Gambar 2.1 — Overtime Management Dashboard</em>
</p>

### Membaca ringkasan di layar

**Status overtime** — satu baris angka untuk seluruh permintaan lembur di sistem (akses dashboard):

- **Total** — jumlah semua permintaan.
- **Draft** — masih draft.
- **Pending** — menunggu approval.
- **Approved** — disetujui (belum ditutup HR).
- **Rejected** — ditolak.
- **Finished** — sudah ditutup HR.

**Empat metrik di bawahnya:**

- **Dibuat bulan ini** — jumlah permintaan yang dibuat pada bulan berjalan, dibanding bulan sebelumnya (persentase pertumbuhan jika ada).
- **Tanggal OT [bulan tahun]** — jumlah permintaan menurut **tanggal lembur** pada bulan berjalan.
- **Approved (bisa ditutup)** — permintaan berstatus **Approved** yang masih menunggu penutupan HR di detail.
- **Langkah approval terbuka** — jumlah langkah approval plan yang masih **Pending**.

**Top projects by volume** — tabel **Project** (kode — nama) dan jumlah **Requests** per proyek.

**Upcoming overtime (pending / approved)** — lembur dengan tanggal OT mendatang; kolom **OT date**, **Project**, **Requester**, **Status**, ikon **View** ke detail.

**Recently created** — permintaan terbaru; kolom **Created**, **OT date**, **Project**, **Status**, ikon **View**.

**Tombol di bawah halaman:**

- **Open full request list** — ke halaman **Overtime Requests**.
- **Reports** — ke **Overtime Reports**.
- **New request** — ke form **Add Overtime Request** (jika tombol tampil sesuai hak akses).

---

## 3. Untuk HR — Requests

### 3.1 Daftar & filter

### Langkah-langkah — **Overtime Requests** (daftar & filter)

1. **HERO SECTION** → **Overtime Management** → **Requests**.
2. Halaman **Overtime Requests** menampilkan tombol **Add** (ikon plus, jika diizinkan) dan panel **Filter** (klik judul **Filter** untuk membuka).
3. Isi filter sesuai kebutuhan, lalu tabel akan memuat ulang otomatis:
    - **Status** — **- All -**, **Draft**, **Pending**, **Approved**, **Rejected**, **Finished**.
    - **Project** — **- All -** atau satu proyek.
    - **Date from** / **Date to** — rentang tanggal lembur.
    - **Requester** — nama pemohon (cocok sebagian).
    - **Employees** — NIK atau nama karyawan di baris detail.
    - **Remarks** — teks di kolom catatan.
4. Klik **Reset** untuk mengosongkan filter.
5. Tabel kolom: **No**, **Register No.**, **Project**, **Date**, **Status**, **Requester**, **Employees**, **Remarks**, **Actions** (**View**, **Edit**, **Submit**, **Delete** sesuai status dan hak).

<p align="center" id="overtime-requests-list">
    <img
        src="images/overtime-requests-list.png"
        alt="Overtime Requests — breadcrumb Home Overtime Requests tombol Add bilah Filter biru, tabel kolom No Register No Project Date Status PENDING DRAFT Requester Employees Remarks Actions ikon View Edit Submit Delete"
        style="max-width: 90%; width: 90%; height: auto;"
    />
    <br><em>Gambar 3.1 — Daftar Overtime Requests</em>
</p>

**Catatan:** Daftar HR dibatasi ke **project yang ter-assign** pada akun Anda (sama seperti modul HR lain di ARKA HERO).

---

### 3.2 Membuat & mengubah permintaan

### Langkah-langkah — **Add Overtime Request** / **Edit Overtime Request**

1. Dari daftar, klik **Add**, atau buka **Edit** dari baris berstatus **Draft** yang boleh diubah.
2. Breadcrumb: **Home** → **Overtime Requests** → **Add New** (atau judul edit dengan nomor register).

**Overtime Information**

- **Project** — wajib; pilih dari dropdown (format kode — nama proyek).
- **Overtime date** — tanggal lembur (wajib).
- **Remarks (optional)** — catatan hingga 2000 karakter.

**Employee Details**

- Minimal **satu baris** karyawan.
- **Employee (NIK - Name)** — setelah **Project** dipilih, dropdown terisi karyawan administrasi di proyek tersebut; jika proyek belum dipilih, tampil **— Select project first —**.
- **IN** / **OUT** — jam mulai dan selesai (format waktu).
- **Work description** — deskripsi pekerjaan (placeholder **Description**).
- **Add Row** — tambah baris; ikon hapus di kolom **Action** (minimal satu baris harus tetap ada).

**Approver Selection** (panel kanan)

- Cari approver lewat kotak pencarian (nama atau email).
- Pilih approver sesuai **Approval Rules Information** (buka panel lipat di layar):
    - **Approval pertama:** setara **Department Head / Manager**
    - **Approval kedua:** setara **Project Manager / Direktur**
    - Pilih **tepat dua** approver berurutan (sequential)
- Jika approver tidak ada di daftar, hubungi **HR** (sesuai catatan di panel).

**Tombol simpan**

- **Save as Draft** — simpan status **Draft**.
- **Save & Submit** — simpan dan langsung ajukan (**Pending**).
- **Cancel** — kembali ke daftar tanpa menyimpan.

<p align="center" id="overtime-request-add-hr">
    <img
        src="images/overtime-request-add-hr.png"
        alt="Add Overtime Request — breadcrumb Home Overtime Requests Add New; kartu Overtime Information Project Overtime date Remarks optional; Employee Details Add Row IN OUT Work description; Approver Selection pencarian Belum ada approver Approval Rules Information; tombol Save as Draft Save and Submit Cancel"
        style="max-width: 90%; width: 90%; height: auto;"
    />
    <br><em>Gambar 3.2 — Form Add Overtime Request (HR)</em>
</p>

---

### 3.3 Detail, pengajuan, dan penutupan HR

### Langkah-langkah — detail permintaan lembur (HR)

1. Dari daftar, klik ikon **View** di kolom **Actions**.
2. Header: **OVERTIME REQUEST**, **Register No.** (mis. **26OT-00001**), nama **Project**, tanggal lembur, badge status (**DRAFT**, **PENDING**, **APPROVED**, dll.).

**Overtime Information** — **Project**, **Overtime date**, **Created by**, **Created at**, **Remarks**.

**Employee Details** — tabel **No**, **Name**, **NIK**, **Time in**, **Time out**, **Description**.

**Approval Status** — jika sudah ada approver / rencana approval, tampil status tiap langkah (**Pending**, **Approved**, **Rejected**, dll.).

**Panel Actions** (kanan):

- **Back to list** — kembali ke **Overtime Requests**.
- **Edit** — jika status masih **Draft**.
- **Submit for Approval** — hanya untuk status **Draft** (konfirmasi di layar).
- **Delete** — hapus permintaan yang masih boleh dihapus.
- **Close Request** — hanya pada status **Approved** dan jika Anda berhak menutup lembur.

<p align="center" id="overtime-detail-hr">
    <img
        src="images/overtime-request-detail-hr.png"
        alt="Detail OVERTIME REQUEST DRAFT register 26OT-00002 HO Balikpapan — Overtime Information Employee Details Approval Status Step 1 Step 2 Actions Back to list Edit Submit for Approval Delete"
        style="max-width: 90%; width: 90%; height: auto;"
    />
    <br><em>Gambar 3.3 — Detail permintaan lembur (HR, contoh Draft)</em>
</p>

### Langkah-langkah — menutup permintaan (**Close Request**)

1. Pada detail berstatus **Approved**, klik **Close Request**.
2. Modal **Mark as finished** terbuka.
3. Isi **Remarks (optional)** untuk catatan penutupan HR (opsional, maks. 1000 karakter).
4. Klik **Save** (atau **Cancel** untuk membatalkan).
5. Status berubah menjadi **Finished**; bagian **HR completion** menampilkan **Finished at**, **By**, dan **Remarks** penutupan.

<p align="center" id="overtime-close-modal">
    <img
        src="images/overtime-request-close-modal.png"
        alt="Modal Mark as finished — judul Mark as finished field Remarks optional placeholder HR completion notes tombol Cancel Save"
        style="max-width: 45%; width: 45%; height: auto;"
    />
    <br><em>Gambar 3.4 — Modal Mark as finished</em>
</p>

**Alur status (ringkas):** **Draft** → (**Submit for Approval** / **Save & Submit**) → **Pending** → **Approved** atau **Rejected** → (**Close Request** setelah lembur selesai) → **Finished**.

---

## 4. Untuk HR — Reports

### 4.1 Halaman Reports

### Langkah-langkah — **Overtime Reports**

1. **HERO SECTION** → **Overtime Management** → **Reports**.
2. Judul halaman: **Overtime analytics & reports**; breadcrumb **Home** / **Overtime Reports**. Kartu **Overtime Request Report** menjelaskan isi laporan (register **YYOT-xxxxx**, proyek, tanggal lembur, status, pemohon, daftar karyawan di detail, remarks) serta fitur filter dan **Tampilkan data** / ekspor Excel.
3. Klik **View Report** untuk membuka laporan detail.

<p align="center" id="overtime-reports-index">
    <img
        src="images/overtime-reports-index.png"
        alt="Overtime analytics and reports — breadcrumb Home Overtime Reports kartu Overtime Request Report deskripsi fitur filter Tampilkan data tombol View Report"
        style="max-width: 90%; width: 90%; height: auto;"
    />
    <br><em>Gambar 4.1 — Halaman Overtime analytics &amp; reports</em>
</p>

---

### 4.2 Overtime Request Report (monitoring)

### Langkah-langkah — **Report Overtime Requests**

1. Dari **Overtime Reports**, buka **View Report** (breadcrumb: **Reports** → **Overtime Requests**).
2. Panel **Filter Options**:
    - **Status** — **Select status**, **All status**, atau nilai spesifik (**Draft** … **Finished**).
    - **Project** — **Select project**, **All projects**, atau satu proyek.
    - **OT date from** / **OT date to**
    - **Register No.** — contoh placeholder **e.g. 26OT-**
    - **Requester**, **Employees**, **Remarks** — filter teks seperti di daftar HR.
3. Klik **Tampilkan data** — wajib mengisi minimal satu filter (misalnya pilih **All status** atau **All projects**, atau tanggal / teks lain). Jika belum, sistem memperingatkan untuk memilih filter terlebih dahulu.
4. Tabel **Report Data** kolom: **No**, **Register No.**, **Project**, **OT date**, **Status**, **Requester**, **Employees**, **Remarks**, **Requested at**, **Action** (ikon lihat detail).
5. **Reset** — muat ulang halaman filter kosong.
6. **Export to Excel** — unduh Excel dengan filter yang sama (minimal satu filter harus aktif).

<p align="center" id="overtime-report-monitoring">
    <img
        src="images/overtime-report-request-monitoring.png"
        alt="Report Overtime Requests — breadcrumb Reports Overtime Requests tombol Back to Reports Filter Options Status Project OT date Register No Requester Employees Remarks Tampilkan data Reset Export to Excel Report Data tabel kosong pesan Pilih Status Project lalu klik Tampilkan data"
        style="max-width: 90%; width: 90%; height: auto;"
    />
    <br><em>Gambar 4.2 — Report Overtime Requests (filter &amp; tabel)</em>
</p>

**Catatan:** Pesan kosong pada tabel mengingatkan: _Pilih Status / Project (atau All), atau isi filter lain, lalu klik Tampilkan data._

---

## 5. Untuk karyawan — My Overtime Request

Bagian ini untuk **semua karyawan** yang mengajukan lembur sendiri. Ringkasan aktivitas lembur juga tersedia di **My Dashboard** (kartu **Overtime** dan **Recent Overtime Requests**); lihat panduan **My Dashboard & My Features** untuk widget dashboard.

**Navigasi:** **My Features** → **My Overtime Request**

**Catatan:** Halaman menampilkan permintaan yang **Anda buat** dan permintaan yang **Anda ikuti sebagai peserta** di baris **Employee details** (misalnya ditambahkan HR/atasan).

---

### Langkah-langkah — Daftar & filter

Dari sidebar **My Features**, buka **My Overtime Request**. Halaman **My Overtime Requests** menampilkan breadcrumb **Home** / **My Dashboard** / **My Overtime Requests**, tombol **Add**, panel **Filter** (**Status**, **Project**, **Date from**, **Date to**, **Employees**, **Remarks**), dan tabel kolom **No**, **Register No.**, **Project**, **Date**, **Status**, **Employees**, **Remarks**, **Actions**.

<p align="center" id="my-overtime-requests-list">
    <img
        src="images/my-overtime-requests-list.png"
        alt="My Overtime Requests — tombol Add Filter tabel Register No Project Date Status Employees Remarks Actions View Edit Submit Delete"
        style="max-width: 90%; width: 90%; height: auto;"
    />
    <br><em>Gambar 5.1 — Daftar My Overtime Requests</em>
</p>

---

### Langkah-langkah — Membuat permintaan lembur baru

1. Klik **Add** — judul form **Add overtime request**.
2. Kartu **Overtime information**:
    - **Project** — wajib.
    - **Overtime date** — wajib.
    - **Remarks** — opsional.
3. Kartu **Employee details**:
    - **Employee (NIK — Name)** — setelah proyek dipilih.
    - **IN**, **OUT**, **Work description**.
    - Tombol **Row** (ikon plus) menambah baris; ikon **×** di **Action** menghapus baris (minimal satu baris).
4. **Approver Selection** — pilih dua approver sesuai **Approval Rules Information** (sama seperti bagian HR di atas).
5. **Save draft** — simpan **Draft**; **Submit** — ajukan langsung (**Pending**); **Cancel** — batalkan.

<p align="center" id="my-overtime-create">
    <img
        src="images/my-overtime-request-add-new.png"
        alt="Add overtime request — Overtime information Employee details Row Approver Selection Save draft Submit Cancel"
        style="max-width: 90%; width: 90%; height: auto;"
    />
    <br><em>Gambar 5.2 — Add overtime request (karyawan)</em>
</p>

<a id="my-overtime-request-detail"></a>

---

### Detail permintaan lembur (karyawan)

Klik ikon **View** di kolom **Actions**.

Halaman detail: header **OVERTIME REQUEST**, **Register No.**, **Project**, tanggal, badge status. **Overtime Information** (**Project**, **Created by**, **Remarks**, **Overtime date**, **Created at**). **Employee Details** (**Name**, **NIK**, **Time in**, **Time out**, **Description**).

<p align="center" id="my-overtime-detail-draft">
    <img
        src="images/my-overtime-request-detail-draft.png"
        alt="Detail OVERTIME REQUEST DRAFT — Actions Back to my list Edit Submit for Approval Delete"
        style="max-width: 90%; width: 90%; height: auto;"
    />
    <br><em>Gambar 5.3 — Detail permintaan lembur (Draft)</em>
</p>

**Jika masih Draft**, panel **Actions** biasanya menampilkan:

- **Back to my list**
- **Edit** — form **Edit overtime request #[Register No.]**
- **Submit for Approval** — ajukan dari detail (konfirmasi)
- **Delete** — hapus permanen (konfirmasi)

Setelah **Pending** / **Approved**, tombol mengikuti status dan hak akses Anda (karyawan umumnya tidak melihat **Close Request**; penutupan dilakukan HR).

---

## 6. Kesalahan & bantuan

| **Gejala / pesan (contoh)**                                         | **Kemungkinan penyebab**                                                  | **Apa yang bisa dicoba**                                                                                     |
| :------------------------------------------------------------------ | :------------------------------------------------------------------------ | :----------------------------------------------------------------------------------------------------------- |
| Menu **Overtime Management** tidak terlihat                         | Akun tanpa hak HR lembur                                                  | Gunakan **My Overtime Request** jika tersedia; hubungi HR untuk hak akses                                    |
| Dropdown karyawan kosong / **— Select project first —**             | **Project** belum dipilih atau tidak ada karyawan di proyek               | Pilih **Project** yang benar; pastikan data administrasi karyawan–proyek sudah ada                           |
| **At least one line is required.** saat menghapus baris             | Hanya tersisa satu baris di **Employee Details**                          | Biarkan minimal satu baris karyawan                                                                          |
| Tidak bisa **Submit** / **Save & Submit**                           | Approver belum dipilih atau kurang dari dua                               | Lengkapi **Approver Selection** sesuai **Approval Rules Information**                                        |
| Laporan: _Pilih Status (atau All status)… lalu klik Tampilkan data_ | Belum ada filter yang dianggap aktif                                      | Pilih **All status** atau **All projects**, atau isi tanggal / register / teks lain, lalu **Tampilkan data** |
| **Export to Excel** tidak mengunduh                                 | Filter belum diisi                                                        | Sama seperti memuat tabel — isi filter lalu ekspor                                                           |
| **Close Request** tidak muncul                                      | Status bukan **Approved** atau bukan akun HR penutup                      | Tunggu approval selesai; pastikan lembur sudah disetujui                                                     |
| Permintaan tidak muncul di daftar HR                                | Di luar project yang di-assign akun HR                                    | Gunakan akun/filter proyek yang sesuai; cek assignment project                                               |
| **Edit** / **Delete** tidak tampil                                  | Status sudah **Pending** / **Approved** / **Rejected** / **Finished** atau bukan pembuat | Hanya **Draft** yang dapat diubah atau dihapus pemohon/HR sesuai hak akses                                 |

### Menghubungi administrator

Sampaikan kepada administrator atau HR:

- **Username** (bukan password)
- **Waktu** kejadian
- **Menu** yang dibuka (mis. **Overtime Requests** atau **My Overtime Request**)
- **Register No.** permintaan (mis. **26OT-00012**) bila ada
- **NIK** Anda jika masalah terkait baris karyawan
- **Cuplikan pesan** di layar

</div>

---
