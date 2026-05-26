<div style="text-align: justify; text-justify: inter-word;">

# Recruitment Management

| **Versi** | **Tanggal** | **Revisi (ringkas)**                                                                                                                                                                      |
| :-------- | :---------- | :---------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 1.1       | 2026-05-25  | **Update Approvers** pada detail FPTK **Submitted**: hanya langkah **Pending** yang dapat diganti; approver yang sudah **Approved**/**Rejected** terkunci.                                |
| 1.0       | 2026-05-25  | Panduan awal: dashboard HR, **Requests (FPTK)** & **Requests (MPP)**, **Candidates (CV)**, **Sessions** (alur tahap rekrutmen), **Reports**, **My Recruitment Request**, troubleshooting. |

Panduan ini menjelaskan modul rekrutmen di ARKA HERO untuk **staf HR** yang mengoperasikan menu grup **Recruitment Management** di **HERO SECTION** (dashboard, permintaan FPTK/MPP, kandidat, sesi rekrutmen, laporan) dan untuk **semua karyawan** yang mengajukan permintaan tenaga kerja lewat **My Features** → **My Recruitment Request**.

| **Istilah**                | Arti singkat                                                                                                                                                                                                                         |
| :------------------------- | :----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Recruitment Management** | Grup menu di **HERO SECTION** untuk dashboard HR, permintaan rekrutmen, kandidat, sesi, dan laporan.                                                                                                                                 |
| **FPTK**                   | _Formulir Permintaan Tenaga Kerja_ — dokumen resmi permintaan rekrutmen; di sistem tercatat sebagai **Recruitment Request (FPTK)** dengan **Request Number** / **FPTK Number**.                                                      |
| **MPP**                    | _Man Power Plan_ — rencana kebutuhan tenaga kerja per proyek; di menu **Requests (MPP)**.                                                                                                                                            |
| **Letter Number**          | Nomor surat dari modul **Letter Administration**; dipilih saat HR membuat FPTK agar **FPTK Number** ter-generate otomatis.                                                                                                           |
| **Approver Selection**     | Pemilihan satu atau lebih **approver** sebelum **Save & Submit** atau **Submit for Approval**; alur persetujuan mengikuti **My Approvals**.                                                                                          |
| **Update Approvers**       | Tombol pada kartu **Approval Status** (detail FPTK **Submitted**) untuk mengganti approver yang masih **Pending**; approver yang sudah memutuskan tidak dapat diubah.                                                                |
| **Recruitment Session**    | Satu proses rekrutmen yang menghubungkan **kandidat** dengan FPTK/MPP yang **Approved**/**Active**; berjalan per tahap (**CV Review**, **Psikotes**, **Interview HR-User-Trainer**, **Offering**, **MCU**, **Hiring & Onboarding**). |
| **Candidate (CV)**         | Data pelamar beserta curriculum vitae di **bank CV** (pool kandidat); dapat dipilih dan dimasukkan ke FPTK **Approved** atau baris MPP **Active** lewat **Recruitment Session**.                                                     |
| **Global Status**          | Status kandidat di pool: **Available**, **In Process**, **Hired**, **Blacklisted**.                                                                                                                                                  |
| **Final Status**           | Status akhir sesi: **In Process**, **Hired**, **Rejected**, **Withdrawn**, **Cancelled**.                                                                                                                                            |
| **My Recruitment Request** | Submenu **My Features** bagi karyawan untuk mengajukan dan memantau FPTK mandiri (nomor sementara **REQxxxxx** sampai HR menetapkan nomor resmi).                                                                                    |
| **Close Request**          | Penutupan FPTK/MPP yang sudah terpenuhi atau tidak lagi dibuka rekrutmen; tersedia di halaman sesi FPTK/MPP yang disetujui.                                                                                                          |

---

## 1. Ringkasan Menu

| **Menu**                   | **Navigasi (sidebar)**                                              | **Uraian**                                                                                                   |
| :------------------------- | :------------------------------------------------------------------ | :----------------------------------------------------------------------------------------------------------- |
| **Dashboard**              | **HERO SECTION** → **Recruitment Management** → **Dashboard**       | Ringkasan FPTK aktif, pool kandidat, sesi, grafik tahap, dan sesi terbaru.                                   |
| **Requests (FPTK)**        | **HERO SECTION** → **Recruitment Management** → **Requests (FPTK)** | Daftar permintaan FPTK HR; filter, tambah, ubah, ajukan, cetak, assign nomor surat.                          |
| **Requests (MPP)**         | **HERO SECTION** → **Recruitment Management** → **Requests (MPP)**  | Daftar **Man Power Plan** per proyek; tambah dan kelola rencana kebutuhan tenaga kerja.                      |
| **Candidates (CV)**        | **HERO SECTION** → **Recruitment Management** → **Candidates (CV)** | **Bank CV** / pool kandidat; simpan profil pelamar, lalu pilih kandidat untuk FPTK/MPP lewat sesi rekrutmen. |
| **Sessions**               | **HERO SECTION** → **Recruitment Management** → **Sessions**        | Daftar FPTK/MPP yang siap rekrutmen beserta progres kandidat; pintu masuk ke detail sesi per kandidat.       |
| **Reports**                | **HERO SECTION** → **Recruitment Management** → **Reports**         | Pintu masuk laporan funnel, aging, time-to-hire, offer acceptance, assessment, stale candidates.             |
| **My Recruitment Request** | **My Features** → **My Recruitment Request**                        | Self-service karyawan: daftar, buat, ubah, lihat FPTK mandiri.                                               |

**Catatan peran:** Menu **Dashboard** sampai **Reports** hanya tampil jika akun memiliki hak akses rekrutmen HR. **My Recruitment Request** tampil terpisah di grup **My Features** dan tidak membuka submenu **Recruitment Management**.

---

## 2. Untuk HR — **Recruitment Dashboard**

### Langkah-langkah — membuka **Recruitment Dashboard** (_Recruitment Analytics and Overview_)

1. **Login** ke ARKA HERO.
2. Di sidebar, buka **HERO SECTION** → **Recruitment Management** → **Dashboard**.
3. Baca kartu ringkasan:

- **Active/Approved FPTK** — jumlah permintaan FPTK yang masih aktif/disahkan (**All requests currently active**).
- **Candidate Pool** — kandidat **Available** atau **In Process** (**Available or in process**).
- **Total Sessions** — total sesi rekrutmen sepanjang waktu (**All time applications**).
- **New Applications** — sesi baru pada bulan berjalan (**Applications in [bulan tahun]**).

4. Tinjau grafik **Active Sessions by Stage** (donut) dan panel **Quick Statistics**: **Success Rate**, **Active Rate**, **Rejection Rate**.
5. Pada tabel **Recent Sessions**, baca kolom **FPTK/MPP Number**, **Position**, **Candidate**, **Stage**, **Status**, **Applied Date**; gunakan ikon **View** atau tombol **View All** untuk ke halaman **Sessions**.
6. Panel **Stage Breakdown** menampilkan jumlah sesi per tahap aktif; **Quick Actions** → **View All Sessions** membuka daftar sesi lengkap.

<p align="center" id="recruitment-dashboard">
    <img
        src="images/recruitment-management-dashboard.png"
        alt="Recruitment Dashboard judul Recruitment Analytics and Overview breadcrumb Home; kartu Active/Approved FPTK 20, Candidate Pool 122, Total Sessions 31, New Applications 1; donut Active Sessions by Stage Psikotes interview MCU; Quick Statistics Success Rate Active Rate Rejection Rate; tabel Recent Sessions kolom FPTK/MPP Number Position Candidate Stage Status Applied Date Action; Stage Breakdown dan Quick Actions View All Sessions"
        style="max-width: 90%; width: 90%; height: auto;"
    />
<br><em>Gambar 2.1 — Recruitment Dashboard</em>
</p>

---

## 3. Untuk HR — **Requests (FPTK)**

### 3.1 Daftar dan filter **Recruitment Request (FPTK)**

1. **Login** → sidebar **HERO SECTION** → **Recruitment Management** → **Requests (FPTK)**.
2. Judul kartu: **Recruitment Request (FPTK)**. Tombol **Export** (jika tersedia) mengunduh data; tombol **Add** (kuning, ikon **+**) membuka form baru.
3. Buka panel **Filter** untuk menyaring:

- **Request Number**, **Department**, **Position**, **Level**
- **Date From** / **Date To**
- **Status** — **Draft**, **Submitted**, **Approved**, **Rejected**, **Cancelled**, **Closed**

4. Tabel menampilkan kolom **No**, **Request Number**, **Department**, **Position**, **Level**, **Employment Type**, **Status**, **Requested By**, **Action** (ikon **View**).
5. Klik **Reset** di filter untuk mengosongkan kriteria.

<p align="center" id="recruitment-fptk-list">
    <img
        src="images/recruitment-requests-fptk-list.png"
        alt="Recruitment Request FPTK breadcrumb Home FPTK tombol Add panel Filter Request Number Department Position Level Date From Date To Status Reset tabel No Request Number Department Position Level Employment Type Status badge Submitted Approved Closed Requested By Action ikon View pagination Showing entries"
        style="max-width: 90%; width: 90%; height: auto;"
    />
<br><em>Gambar 3.1 — Daftar Requests (FPTK)</em>
</p>

### 3.2 Form tambah atau ubah FPTK (_Create Recruitment Request (FPTK)_ / _Edit Recruitment Request (FPTK)_)

Klik **Add** dari daftar, atau buka **Edit** dari detail FPTK berstatus **Draft**.

**1. Letter Number**

Pilih nomor surat kategori **FPTK** lewat komponen **Letter Number**. Setelah dipilih, field **FPTK Number** terisi otomatis (format mengikuti penomoran perusahaan, misalnya **[Letter Number]/HCS-[Project Code]/FPTK/[bulan Romawi]/[tahun]**). Jika nomor surat belum ada, gunakan opsi **Create New** dari selector (selaras modul **Letter Administration**).

**2. FPTK Information**

- **FPTK Number** — ter-generate dari **Letter Number**.
- **Request Date** — tanggal permintaan.
- **Department**, **Project**, **Position**, **Level** — wajib.
- **Job Description** — wajib; uraian tugas posisi.

**3. Request Details**

- **Required Quantity** — jumlah orang (1–50).
- **Required Date** — tanggal kebutuhan tenaga kerja.
- **Request Reason** — **Additional - Workplan**, **Replacement - Promotion, Mutation, Demotion**, atau **Replacement - Resign, Termination, End of Contract**.
- **Employment Type** — **PKWTT (Permanent)**, **PKWT (Contract)**, **Daily Worker**, **Internship**.

**4. Requirements**

- **Gender** — **Male**, **Female**, **Any**.
- **Marital Status** — **Single**, **Married**, **Any**.
- **Min Age** / **Max Age**, **Education**, **Required Skills**, **Required Experience** — pelengkap sesuai kebutuhan.

**5. Additional Requirements** _(panel kanan)_

- **Physical Requirements**, **Mental Requirements**, **Other Requirements**.
- Centang **Posisi ini memerlukan Tes Teori** jika posisi mekanik atau membutuhkan kompetensi teknis (**Tes Teori** akan muncul di alur sesi).

**6. Approver Selection**

Pilih approver sesuai dengan aturan approval yang berlaku. Klik **Approval Rules Information** untuk melihat aturanya. Wajib diisi sebelum pengajuan.

**7. Simpan**

- **Save as Draft** — dapat diedit kemudian.
- **Save & Submit** — langsung mengajukan; setelah submit FPTK **tidak dapat diedit** lagi (konfirmasi di layar).
- **Cancel** — kembali ke daftar tanpa menyimpan.

<p align="center" id="recruitment-fptk-create">
    <img
        src="images/recruitment-requests-fptk-create.png"
        alt="Create Recruitment Request FPTK breadcrumb Add New Letter Number Refresh List Create New FPTK Information Request Details Requirements Additional Requirements Approver Selection Save as Draft Save and Submit Cancel"
        style="max-width: 85%; width: 85%; height: auto;"
    />
<br><em>Gambar 3.2 — Form Create Recruitment Request (FPTK)</em>
</p>

**Catatan:** Pengajuan mandiri karyawan lewat **My Recruitment Request** tidak memakai **Letter Number** di form awal; nomor sementara **REQxxxxx** diganti HR setelah konfirmasi — lihat [bagian 8](#section-8-my-recruitment-request).

### 3.3 Detail FPTK, persetujuan, dan nomor resmi

Buka detail lewat ikon **View** pada daftar. Judul: **Detail Recruitment Request**; header menampilkan nomor FPTK, proyek, tanggal, dan badge status.

**Membaca halaman detail**

- Kartu **FPTK Information** — department, project, position, level, quantity, required date, employment type, alasan, kebutuhan tes teori, job description, requirements.
- **Approval Status** — daftar approver berurutan dengan badge status (**Pending**, **Approved**, **Rejected**, dll.) bila sudah diajukan. Pada FPTK **Submitted** yang masih punya langkah **Pending**, HR dapat membuka form **Approver Selection** di kartu yang sama (lihat [mengubah approver pending](#mengubah-approver-pending) di bawah).
- **Requested By** — pembuat permintaan.
- Tabel **Recruitment Sessions** — muncul setelah ada kandidat terdaftar; kolom tahap (**CV Review**, **Psikotes**, **Tes Teori**, **Interview HR**, **Interview User**, **Offering**, **MCU**, **Hire**, **Onboarding**, **Final Status**).

<p align="center" id="recruitment-fptk-detail-reading">
    <img
        src="images/recruitment-requests-fptk-detail-submitted-overview.png"
        alt="Detail FPTK Submitted badge Submitted header nomor FPTK tanggal kartu FPTK Information Department Project Position Level Required Quantity Required Date Employment Type Request Reason Theory Test Requirement Not Required kartu Approval Status daftar approver Approved dan Pending Job Description Requirements"
        style="max-width: 71%; width: 71%; height: auto;"
    />
<br><em>Gambar 3.3 — Membaca halaman detail FPTK Submitted: <strong>FPTK Information</strong> dan <strong>Approval Status</strong></em>
</p>

<p align="center" id="recruitment-fptk-job-description-requirements">
    <img
        src="images/recruitment-requests-fptk-job-description-requirements.png"
        alt="Job Description and Requirements Job Description Gender Marital Status Age Range Education Required Skills Required Experience Physical Requirements Mental Requirements Other Requirements Theory Test Requirement Not Required Requested By Back to List Print FPTK"
        style="max-width: 71%; width: 71%; height: auto;"
    />
<br><em>Gambar 3.4 — <strong>Job Description &amp; Requirements</strong>, kebutuhan posisi, dan <strong>Requested By</strong> pada detail FPTK Submitted</em>
</p>

**Aksi (panel kanan, sesuai status dan hak akses)**

| Status                        | Aksi umum                                                                                                                      |
| :---------------------------- | :----------------------------------------------------------------------------------------------------------------------------- |
| **Draft**                     | **Edit**, **Delete**, **Submit for Approval** (HR; konfirmasi SweetAlert — setelah submit isi FPTK tidak bisa diedit)          |
| **Submitted**                 | Approver memproses lewat **My Approvals**; HR dapat **Update Approvers** selama masih ada langkah **Pending** (lihat di bawah) |
| **Approved**                  | **Assign Letter Number** jika belum ada nomor surat resmi                                                                      |
| Semua (kecuali ditolak/batal) | **Print FPTK**, **Back to List**                                                                                               |

**Catatan:** Setelah **Submit for Approval**, field FPTK (department, posisi, quantity, dll.) **tidak dapat diedit** lagi; yang masih dapat disesuaikan HR hanya **approver pada langkah Pending** lewat **Update Approvers**.

**Penjelasan singkat — alur persetujuan FPTK**

1. Pembuat (HR atau karyawan via HR) menyimpan **Draft** atau mengajukan (**Submitted**).
2. **Approver** yang dipilih memberi keputusan di **My Approvals** (lihat panduan **My Approvals**, jenis dokumen **Recruitment Request**).
3. Setelah **Approved**, HR menetapkan **Letter Number** bila belum otomatis — tombol **Assign Letter Number**.
4. FPTK **Approved** dengan nomor resmi siap menerima kandidat di menu **Sessions**.

<a id="mengubah-approver-pending"></a>

### Langkah-langkah — mengubah approver yang masih **Pending** (_Update Approvers_)

Fitur ini hanya untuk **HR** dengan hak **`recruitment-requests.edit`**, pada detail FPTK berstatus **Submitted**, dan **bukan** dari halaman **My Recruitment Request** karyawan.

1. Buka detail FPTK (**View** dari daftar **Requests (FPTK)**).
2. Pada kartu **Approval Status** di kolom kanan, baca daftar approver:
    - Approver yang sudah **Approved** atau **Rejected** ditampilkan dengan badge status dan **tidak** memiliki tombol hapus (terkunci).
    - Approver yang masih **Pending** dapat dihapus (ikon **×**) lalu diganti lewat kotak pencarian approver.
3. Untuk **mengganti** approver pending: klik **×** pada baris pending, ketik nama/email approver pengganti (minimal 2 karakter), pilih dari daftar — orang baru akan masuk pada **urutan yang sama**.
4. Anda juga dapat **menghapus** approver pending di akhir daftar atau **menambah** approver pending baru di urutan belakang, selama minimal satu approver tetap ada dan approver terkunci tidak diubah.
5. Klik **Update Approvers**. Pesan sukses mengonfirmasi pembaruan langkah pending; approver baru menerima antrean di **My Approvals**.

**Catatan:**

- Form **Update Approvers** **tidak** muncul jika semua langkah sudah diputuskan (tidak ada **Pending** tersisa) atau FPTK bukan **Submitted**.
- Urutan approver mengikuti nomor **1**, **2**, **3**, … pada badge; approver terkunci tetap di posisi semula.
- Approver **tidak boleh duplikat** dalam satu FPTK.

<p align="center" id="recruitment-fptk-approval-status-update">
    <img
        src="images/recruitment-requests-fptk-approval-status-update.png"
        alt="Kartu Approval Status pencarian approver daftar urut 1 Hanggoro Approved 2 Wahyu Ansyar Approved terkunci border putus-putus 3 Achmad Faonizan 4 Eddy Nasri Pending tombol x catatan Approver Approved Rejected tidak dapat diubah hanya Pending dapat diganti tombol Update Approvers"
        style="max-width: 50%; width: 50%; height: auto;"
    />
<br><em>Gambar 3.5 — Kartu Approval Status: approver terkunci (Approved) dan pending (ikon ×) dengan tombol Update Approvers</em>
</p>

---

## 4. Untuk HR — **Requests (MPP)**

**Man Power Plan (MPP)** merencanakan kebutuhan tenaga kerja per **Project** untuk periode tertentu, terpisah dari FPTK per posisi.

### Langkah-langkah — daftar dan filter MPP

1. Sidebar **HERO SECTION** → **Recruitment Management** → **Requests (MPP)**.
2. Judul kartu: **Man Power Plan (MPP)**; tombol **Add** untuk MPP baru.
3. **Filter**: **MPP Number**, **Project**, **Status** (**Active** / **Closed**), **Year**; **Reset** mengosongkan filter.
4. Tabel: **No**, **MPP Number**, **Project**, **Title**, **Plan**, **Existing**, **Diff**, **Completion**, **Status**, **Action**.

<p align="center" id="recruitment-mpp-list">
    <img
        src="images/recruitment-requests-mpp-list.png"
        alt="Daftar Man Power Plan MPP: filter MPP Number Project Status Year, tabel MPP Number Project Title Plan Existing Diff Completion Status Action"
        style="max-width: 90%; width: 90%; height: auto;"
    />
<br><em>Gambar 4.1 — Daftar <strong>Man Power Plan (MPP)</strong>: filter, tabel Plan/Existing/Diff/Completion, dan aksi View/Edit/Delete</em>
</p>

### Langkah-langkah — membuat MPP (_Add New_)

1. Klik **Add**. Form **MPP Information**:
    - **Project**, **Title** (wajib), **Description** (opsional).
2. Isi baris detail posisi (tabel di form): **Position**, **Plan Quantity**, **Existing Quantity** — sistem menghitung **Difference**.
3. Simpan sesuai tombol di layar (**Save MPP** / **Cancel**). MPP berstatus **Active** siap dipakai di **Sessions** untuk rekrutmen per detail posisi.

<p align="center" id="recruitment-mpp-create">
    <img
        src="images/recruitment-requests-mpp-create.png"
        alt="Form Create new MPP document MPP Information Project Title Description Position Details Add Position Plan Existing Diff Theory Test Agreement Type Save MPP Cancel"
        style="max-width: 85%; width: 85%; height: auto;"
    />
<br><em>Gambar 4.2 — Form <strong>Create new MPP document</strong>: <strong>MPP Information</strong>, tabel detail posisi, dan tombol <strong>Save MPP</strong></em>
</p>

### Langkah-langkah — melihat detail MPP (_MPP Details_)

1. Dari daftar **Man Power Plan (MPP)**, klik ikon **View** (biru) pada kolom **Action**.
2. Halaman **MPP Details** menampilkan:
    - Kartu **MPP Information** — **MPP Number**, **Project**, **Status**, **Created By**, **Title** (dan **Description** jika diisi).
    - Empat kartu ringkasan: **Total Plan**, **Total Existing**, **Total Diff**, **Completion** (%).
    - Tabel **Position Details & Recruitment Sessions** — per baris posisi: jabatan/department, **Qty Unit**, **Plan** / **Existing** / **Diff** (kolom **S**, **NS**, **Total**), **Theory Test**, **Status** posisi (**Pending** atau **Fulfilled**), dan aksi baris.

**Aksi di header (kanan atas kartu MPP Information)**

| Tombol        | Kapan tampil             | Fungsi                                                                                                                               |
| :------------ | :----------------------- | :----------------------------------------------------------------------------------------------------------------------------------- |
| **Edit**      | MPP berstatus **Active** | Membuka form ubah data MPP dan detail posisi.                                                                                        |
| **Close MPP** | MPP berstatus **Active** | Menutup MPP (konfirmasi SweetAlert). Setelah ditutup, sesi rekrutmen baru **tidak** dapat dibuat; status berubah menjadi **Closed**. |
| **Back**      | Selalu                   | Kembali ke daftar **Requests (MPP)**.                                                                                                |

**Aksi per baris posisi (kolom Action)**

| Tombol            | Kapan tampil                                                     | Fungsi                                                                                                                                                                                                             |
| :---------------- | :--------------------------------------------------------------- | :----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **View Sessions** | Selalu (ikon mata, biru)                                         | Menampilkan atau menyembunyikan daftar **Recruitment Sessions** untuk posisi tersebut. Sub-tabel menampilkan **Session Number**, **Candidate**, **Applied Date**, **Current Stage**, **Progress**, dan **Status**. |
| **Add Candidate** | MPP **Active** dan posisi belum **Fulfilled** (ikon plus, hijau) | Membuka modal **Add Candidate to MPP Detail** — cari kandidat/CV lalu hubungkan ke baris MPP untuk memulai sesi rekrutmen. Tombol yang sama juga tersedia di area sesi yang dibuka.                                |

**Aksi pada sub-tabel sesi (setelah View Sessions)**

| Tombol   | Fungsi                                                                                                       |
| :------- | :----------------------------------------------------------------------------------------------------------- |
| **View** | Membuka halaman timeline sesi kandidat di tab baru (**Recruitment Session** — lihat bagian **6. Sessions**). |

<p align="center" id="recruitment-mpp-detail">
    <img
        src="images/recruitment-requests-mpp-detail.png"
        alt="MPP Details MPP Information Total Plan Existing Diff Completion Position Details Recruitment Sessions View Sessions Add Candidate session Hired Current Stage Hire Progress Edit Close MPP Back"
        style="max-width: 90%; width: 90%; height: auto;"
    />
<br><em>Gambar 4.3 — Halaman <strong>MPP Details</strong>: ringkasan plan/existing/diff, tabel posisi dengan sesi rekrutmen (contoh baris diperluas), dan tombol <strong>Edit</strong>, <strong>Close MPP</strong>, <strong>Back</strong></em>
</p>

**Catatan:** Rekrutmen dari MPP tidak memakai alur FPTK/letter number yang sama; sesi kandidat terhubung ke baris **MPP Detail** (posisi dalam rencana).

---

## 5. Untuk HR — **Candidates (CV)**

Menu **Candidates (CV)** berfungsi sebagai **bank CV** (_candidate pool_) — repositori data pelamar dan berkas CV yang **terpisah** dari permintaan rekrutmen (**Requests (FPTK)** / **Requests (MPP)**). HR terlebih dahulu menyimpan dan mengelola profil kandidat di sini; kandidat dengan **Global Status** **Available** dapat dipilih dan dimasukkan ke proses rekrutmen dengan cara:

- **Apply to FPTK** — dari detail kandidat, pilih FPTK **Approved** yang masih membuka slot (lihat bagian **3. Requests (FPTK)**).
- **Add Candidate** — dari detail **MPP Details**, pilih kandidat untuk baris posisi MPP **Active** (lihat bagian **4. Requests (MPP)**).

Kedua cara di atas membuat **Recruitment Session** baru yang menghubungkan kandidat dengan FPTK atau MPP, lalu melanjutkan tahap rekrutmen di menu **Sessions** (lihat bagian **6**).

<br>

### Langkah-langkah — daftar kandidat

1. Sidebar **Recruitment Management** → **Candidates (CV)**.
2. Judul: **Recruitment Candidates (CV)**; tombol **Add** menambah kandidat baru.
3. **Filter**: **Candidate Number**, **Full Name**, **Email**, **Phone**, **Education Level**, **Position Applied**, **Global Status**, **Registration Date From/To**.
4. Tabel: **No**, **Candidate Number**, **Full Name**, **Email**, **Phone**, **Education**, **Position Applied**, **Global Status**, **Registration Date**, **Action**.

<p align="center" id="recruitment-candidates-list">
    <img
        src="images/recruitment-candidates-list.png"
        alt="Recruitment Candidates CV tombol Add filter Candidate Number Full Name Email Phone Education Level Position Applied Global Status Registration Date From To Reset tabel Candidate Number Full Name Global Status Available In Process Action View Edit Delete"
        style="max-width: 90%; width: 90%; height: auto;"
    />
<br><em>Gambar 5.1 — Daftar <strong>Recruitment Candidates (CV)</strong>: filter, status <strong>Available</strong>/<strong>In Process</strong>, dan aksi View/Edit/Delete</em>
</p>

### Langkah-langkah — menambah kandidat (_Add New_)

1. Dari daftar **Recruitment Candidates (CV)**, klik **Add**.
2. Isi form **Add New Candidate**:

**1. Personal Information** — **Full Name**, **Email**, **Phone Number**, **Date of Birth**, **Address** (wajib).

**2. Professional Information** — **Last Education Level**, **Years of Experience** (wajib); **Position Applied For**, **Skills & Competencies**, **Certifications**, **Previous Companies** (opsional).

**3. Salary Information** — **Current Salary**, **Expected Salary** (opsional).

**4. Remarks** _(panel kanan)_ — **Additional Notes** (opsional).

**5. CV Upload** — unggah berkas CV (**PDF**, **DOC**, **DOCX**, **ZIP**, **RAR**; maks. 10 MB).

**6. Simpan** — **Save Candidate** atau **Back** ke daftar.

<p align="center" id="recruitment-candidates-create">
    <img
        src="images/recruitment-candidates-create.png"
        alt="Add New Candidate Personal Information Professional Information Salary Information Remarks CV Upload Save Candidate Back"
        style="max-width: 90%; width: 90%; height: auto;"
    />
<br><em>Gambar 5.2 — Form <strong>Add New Candidate</strong>: data pribadi, profesional, gaji, unggah CV, dan tombol <strong>Save Candidate</strong></em>
</p>

### Langkah-langkah — detail kandidat dan aksi

Buka kandidat lewat **View**. Panel aksi umum:

- **Download CV** — unduh lampiran CV dari bank CV.
- **Apply to FPTK** — modal **Apply to FPTK**: pilih FPTK **Approved** yang masih membuka slot; sistem membuat **Recruitment Session** baru dan menghubungkan kandidat ke permintaan FPTK tersebut.
- **Add Candidate** _(dari daftar)_ — pintasan serupa **Apply to FPTK** untuk kandidat **Available** (ikon plus biru pada kolom **Action** di daftar).
- **Blacklist** / **Remove from Blacklist** — modal **Blacklist Candidate** dengan **Blacklist Reason** wajib.
- **Edit**, **Print** (jika tersedia), **Delete** (sesuai status dan hak akses).

Untuk MPP, kandidat dari bank CV juga dapat dimasukkan lewat **Add Candidate** pada detail **MPP Details** (bukan dari halaman detail kandidat).

**Catatan:** Bank CV bukan dokumen rekrutmen tersendiri — kandidat baru masuk proses hanya setelah dihubungkan ke FPTK/MPP lewat **Recruitment Session**. Kandidat **Blacklisted** tidak dapat dilamar ke FPTK/MPP baru. **Global Status** berubah otomatis saat masuk sesi (**In Process**) atau selesai (**Hired**).

---

## 6. Untuk HR — **Sessions**

Sesi rekrutmen menghubungkan **kandidat** dengan FPTK **Approved** atau baris **MPP Active**. Satu FPTK/MPP dapat memiliki beberapa sesi (beberapa kandidat).

### 6.1 Daftar **Recruitment Sessions**

1. Sidebar **Recruitment Management** → **Sessions** (tombol **Dashboard** di kanan atas kembali ke dashboard rekrutmen).
2. **Filter**: **FPTK/MPP Number**, **Department**, **Position**, **Required Date From/To**.
3. Tabel: **No**, **Source** (FPTK atau MPP), **Project**, **FPTK/MPP No.**, **Position**, **Candidate Count**, **Overall Progress**, **Final Status**, **Required Date**, **Action**.
4. Klik **View** pada baris untuk membuka halaman sesi FPTK/MPP (daftar kandidat per permintaan).

<p align="center" id="recruitment-sessions-list">
    <img
        src="images/recruitment-sessions-list.png"
        alt="Recruitment Sessions: filter FPTK/MPP Number Department Position Required Date, tabel Source Project FPTK MPP No Position Candidate Count Overall Progress Final Status Action"
        style="max-width: 90%; width: 90%; height: auto;"
    />
<br><em>Gambar 6.1 — Daftar Recruitment Sessions (placeholder)</em>
</p>

### 6.2 Halaman sesi FPTK/MPP (Approved)

Dari daftar sesi, **View** membuka halaman dengan header nomor FPTK/MPP, proyek, status, dan tabel kandidat (kolom tahap sama seperti di detail FPTK).

- **Add Candidate** — modal **Add Candidate**: cari nama/email/posisi di **Search Candidate/CV**, pilih **Select** pada hasil, konfirmasi penambahan sesi.
- **Close Request** — menutup FPTK/MPP yang sudah terpenuhi (konfirmasi di layar); rekrutmen baru ke permintaan tersebut tidak dibuka lagi.

### 6.3 Detail sesi per kandidat (_Recruitment Timeline_)

Klik **View** pada baris kandidat (dari tabel sesi FPTK/MPP atau **Recent Sessions**). Header menampilkan nama kandidat, proyek, badge **Final Status** (**In Process**, **Hired**, **Rejected**, dll.).

**Recruitment Timeline** — urutan tahap dengan indikator warna (abu = belum, kuning = berjalan, hijau = lulus, merah = gagal). Tahap aktif dapat dibuka untuk input penilaian (ikon/edit pada tahap yang **unlocked**).

| Tahap (UI)     | Ringkasan                                                                                                                                                     |
| :------------- | :------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **CV Review**  | Keputusan **Recommended** / **Not Recommended** + **Review Date**, **Notes**.                                                                                 |
| **Psikotes**   | Skor **Psikotes Online** dan **Psikotes Offline**; kriteria layar: ≥ 40 lanjut, &lt; 40 tidak direkomendasikan.                                               |
| **Tes Teori**  | Hanya jika FPTK mencentang tes teori atau posisi mekanik; skor dan keputusan lulus/gagal.                                                                     |
| **Interview**  | Sub-tipe **HR Interview**, **User Interview**, **Trainer Interview** (pilih **Interview Type**); keputusan per wawancara; semua wajib selesai sebelum lanjut. |
| **Offering**   | **Offering Letter Number** (selector surat), keputusan offering (**Accepted** / **Rejected**).                                                                |
| **MCU**        | **Fit to Work**, **Unfit**, atau **Follow Up** + **Review Date**, **Notes**.                                                                                  |
| **Hire**       | Data **Personal Data** kandidat (KTP, DOB, dll.) — info layar: data otomatis masuk ke **Employee** dan **Administration**.                                    |
| **Onboarding** | Kolom terpisah di tabel ringkasan; melengkapi proses onboarding setelah **Hire**.                                                                             |

**Pengecualian alur**

| Kondisi                                                    | Tahap yang dijalankan                               |
| :--------------------------------------------------------- | :-------------------------------------------------- |
| **Employment Type** = **Internship** atau **Daily Worker** | Hanya **MCU** dan **Hire** (proses disederhanakan). |
| Posisi **tanpa Tes Teori**                                 | Tahap **Tes Teori** dilewati; progress disesuaikan. |

<p align="center" id="recruitment-session-timeline">
    <img
        src="images/recruitment-session-candidate-timeline.png"
        alt="Detail sesi kandidat: header nama kandidat badge In Process, Recruitment Timeline CV Review Psikotes Tes Teori Interview Offering MCU Hire, tombol aksi tahap aktif"
        style="max-width: 90%; width: 90%; height: auto;"
    />
<br><em>Gambar 6.2 — Recruitment Timeline sesi kandidat (placeholder)</em>
</p>

### 6.4 Mengisi penilaian per tahap (modal)

Setiap tahap membuka modal khusus; pola umum:

1. Klik tahap aktif pada **Recruitment Timeline**.
2. Isi field wajib (bertanda merah).
3. Pilih keputusan (**Recommended**, **Pass**, **Approve**, **Fit to Work**, dll. sesuai tahap).
4. **Submit Decision** / **Submit Assessment** — konfirmasi **You cannot edit after submission**.
5. Jika lulus, sistem memindahkan **current stage** ke tahap berikutnya; jika gagal, **Final Status** menjadi **Rejected**.

**Penjelasan singkat — modal CV Review (_Choose Your Decision_)**

- **CV Review Decision** — **Recommended** atau **Not Recommended**.
- **Review Date**, **Notes** (wajib).
- **Submit Decision** aktif setelah keputusan dipilih.

**Penjelasan singkat — modal Interview**

- **Interview Type** — **HR Interview**, **User Interview**, **Trainer Interview** (tipe yang sudah selesai disabled).
- **Interview Decision**, tanggal, catatan, dan field penilaian sesuai form.
- Ulangi untuk setiap tipe wawancara yang diwajibkan posisi tersebut.

**Penjelasan singkat — modal Hire (_Hire Stage_)**

Banner info: _The data you enter here will be automatically saved to the Employee and Administration records._ Isi **Personal Data** (minimal **Fullname**, **Identity Card No**, dan field wajib lain), lalu submit. Setelah sukses, status sesi dapat menjadi **Hired**.

---

## 7. Untuk HR — **Reports**

Buka **HERO SECTION** → **Recruitment Management** → **Reports**. Judul: **Recruitment Reports**. Setiap kartu memiliki tombol **View Report**:

| Laporan                              | Isi singkat                                                |
| :----------------------------------- | :--------------------------------------------------------- |
| **Recruitment Funnel by Stage**      | Progres kandidat per tahap; filter tanggal; ekspor Excel.  |
| **Request Aging & SLA**              | Lama proses FPTK, bottleneck persetujuan, kepatuhan SLA.   |
| **Time-to-Hire Analysis**            | Hari dari pembuatan permintaan hingga onboarding kandidat. |
| **Offer Acceptance Rate**            | Tingkat penerimaan offering per departemen/posisi.         |
| **Interview & Assessment Analytics** | Hasil assessment kandidat yang lulus **CV Review**.        |
| **Stale Candidates Report**          | Kandidat tanpa aktivitas/progres terbaru.                  |

<p align="center" id="recruitment-reports-index">
    <img
        src="images/recruitment-reports-index.png"
        alt="Recruitment Reports: kartu Recruitment Funnel Request Aging Time-to-Hire Offer Acceptance Rate Interview Assessment Analytics Stale Candidates dengan tombol View Report"
        style="max-width: 90%; width: 90%; height: auto;"
    />
<br><em>Gambar 7.1 — Halaman Reports (placeholder)</em>
</p>

Pada masing-masing laporan, gunakan filter yang tersedia di layar lalu **Export** / **View Report** sesuai label tombol.

---

<a id="section-8-my-recruitment-request"></a>

## 8. Untuk karyawan — **My Recruitment Request**

Bagian ini untuk **semua karyawan** yang berhak mengajukan FPTK mandiri. Navigasi: **My Features** → **My Recruitment Request** (bukan submenu **Recruitment Management**).

**Alur singkat karyawan**

1. Buat permintaan (**Add**) → isi form **Create My Recruitment Request (FPTK)** → **Submit to HR** (menyimpan **Draft** dengan nomor **REQxxxxx**).
2. HR meninjau, dapat mengubah/mengajukan ke approver, menetapkan **Letter Number** / nomor FPTK resmi.
3. Setelah **Approved**, karyawan memantau **Recruitment Sessions** di detail permintaan.

Narasi lengkap (daftar, filter, form, detail Draft/Approved, tabel sesi) selaras dengan bab **My Dashboard & My Features** — lihat **bagian 8** pada `16-my-features.md` dan figur berikut:

<p align="center" id="my-recruitment-requests-list-ref">
    <img
        src="images/my-recruitment-requests-list.png"
        alt="My Recruitment Requests daftar FPTK karyawan"
        style="max-width: 90%; width: 90%; height: auto;"
    />
<br><em>Gambar 8.1 — My Recruitment Requests (reuse dari My Features)</em>
</p>

<p align="center" id="my-recruitment-create-ref">
    <img
        src="images/my-recruitment-request-create-fptk.png"
        alt="Create My Recruitment Request FPTK"
        style="max-width: 85%; width: 85%; height: auto;"
    />
<br><em>Gambar 8.2 — Create My Recruitment Request (FPTK)</em>
</p>

<p align="center" id="my-recruitment-sessions-ref">
    <img
        src="images/my-recruitment-recruitment-sessions.png"
        alt="Recruitment Sessions pada detail FPTK Approved"
        style="max-width: 90%; width: 90%; height: auto;"
    />
<br><em>Gambar 8.3 — Recruitment Sessions (tampilan karyawan)</em>
</p>

**Catatan penting untuk karyawan**

- **Submit for Approval** pada detail **My Recruitment Request** umumnya **tidak** ditampilkan; kelanjutan approval dan nomor resmi ditangani **HR**.
- **Assign Letter Number** hanya tersedia untuk HR di menu **Requests (FPTK)**.
- Approver yang dipilih pada form HR berbeda dengan pengajuan mandiri; karyawan cukup melengkapi data permintaan dan menunggu tindak lanjut HR.

---

## 9. Kesalahan & bantuan

| Gejala / pesan (contoh)                                                        | Kemungkinan penyebab                                                     | Apa yang bisa dicoba                                                                                                                                                                                   |
| :----------------------------------------------------------------------------- | :----------------------------------------------------------------------- | :----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Please select at least one approver before submitting**                      | **Approver Selection** kosong                                            | Pilih minimal satu approver sebelum **Save & Submit** / **Submit for Approval**.                                                                                                                       |
| **Only draft recruitment requests can be submitted**                           | Status bukan **Draft**                                                   | Buka permintaan **Draft** atau minta HR membuat entri baru.                                                                                                                                            |
| **After submitting, this FPTK cannot be edited anymore**                       | FPTK sudah **Submitted**                                                 | Isi FPTK (department, posisi, dll.) tidak bisa **Edit** lagi; untuk ganti approver pending gunakan **Update Approvers** di kartu **Approval Status** (lihat [bagian 3.3](#mengubah-approver-pending)). |
| **Approver yang sudah disetujui atau ditolak tidak dapat diubah atau dihapus** | Mencoba mengubah/menghapus approver yang sudah **Approved**/**Rejected** | Hanya ubah baris **Pending**; approver terkunci tetap di posisinya.                                                                                                                                    |
| **Approver tidak boleh duplikat**                                              | Approver yang sama dipilih lebih dari sekali                             | Pilih approver berbeda untuk setiap urutan.                                                                                                                                                            |
| Tombol **Update Approvers** tidak tampil                                       | Semua langkah sudah diputuskan atau status bukan **Submitted**           | Normal jika tidak ada langkah **Pending**; selesaikan alur approval atau buat FPTK baru bila perlu.                                                                                                    |
| **FPTK hanya dapat dihapus dalam status draft**                                | Menghapus FPTK non-Draft                                                 | Hanya **Draft** yang bisa **Delete**.                                                                                                                                                                  |
| Tombol tahap sesi tidak aktif / terkunci                                       | Tahap sebelumnya belum selesai atau sesi **Rejected**                    | Selesaikan tahap berurutan; baca **Recruitment Timeline** dan tooltip alasan kunci.                                                                                                                    |
| **Psikotes** &lt; 40 / **Not Recommended**                                     | Kandidat tidak memenuhi kriteria tahap                                   | Sesi berakhir **Rejected**; cari kandidat lain atau evaluasi ulang kebijakan penilaian.                                                                                                                |
| **Apply to FPTK** gagal / FPTK tidak muncul                                    | FPTK belum **Approved**, slot penuh, atau kandidat **Blacklisted**       | Pastikan FPTK disetujui dan masih buka; cek **Global Status** kandidat.                                                                                                                                |
| Menu **Recruitment Management** tidak tampil                                   | Hak akses HR rekrutmen belum diberikan                                   | Hubungi administrator untuk role/permission rekrutmen.                                                                                                                                                 |
| Nomor masih **REQxxxxx**                                                       | HR belum assign **Letter Number**                                        | Tunggu konfirmasi HR atau tanyakan status di **Requests (FPTK)**.                                                                                                                                      |

### Menghubungi administrator

Siapkan **username** (bukan password), waktu kejadian, menu yang dibuka (**Requests (FPTK)**, **Sessions**, **My Recruitment Request**, dll.), **Request Number** / **FPTK Number** / **Candidate Number**, nama kandidat bila relevan, dan cuplikan pesan error di layar. Untuk masalah persetujuan, sebutkan juga apakah dokumen sudah muncul di **My Approvals** approver terkait.

</div>

---
