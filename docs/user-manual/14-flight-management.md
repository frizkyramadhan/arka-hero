# Flight Management

<div style="text-align: justify; text-justify: inter-word;">

Panduan ini menjelaskan modul **Flight Management** di ARKA HERO: pengelolaan permintaan tiket penerbangan (**Flight Request Form / FRF**) untuk **staf HR** (menu grup **Flight Management** di **GAMMA SECTION**), serta pengajuan mandiri oleh **karyawan** lewat **My Features** → **My Flight Request**.

**Catatan peran:** Menu **Dashboard**, **Requests**, **Issuances**, dan **Reports** hanya tampil jika akun Anda memiliki hak akses modul penerbangan. **My Flight Request** tersedia bagi karyawan yang berhak mengajukan atau memantau permintaan tiket sendiri.

---

## Glosarium

| **Istilah**                  | Arti singkat                                                                                             |
| :--------------------------- | :------------------------------------------------------------------------------------------------------- |
| **Flight Request (FRF)**     | Formulir permintaan tiket penerbangan; nomor formulir format **YYFRF-xxxxx** (misalnya **26FRF-00001**). |
| **Request Type**             | Jenis sumber permintaan: **Standalone**, **Leave Based**, atau **Travel Based**.                         |
| **Standalone**               | Tiket tidak terikat cuti atau LOT; pemohon dan pengikut (jika ada) diisi pada formulir FRF.              |
| **Leave Based**              | Tiket mengacu pada pengajuan cuti yang sudah ada.                                                        |
| **Travel Based**             | Tiket mengacu pada **Official Travel (LOT)**; data pemohon dan **Followers** mengikuti LOT.              |
| **Followers**                | Karyawan atau penumpang pendamping yang ikut dalam perjalanan (bukan pemohon utama).                     |
| **Letter of Guarantee (LG)** | Surat jaminan tiket yang diterbitkan HR setelah FRF disetujui; dikelola di menu **Issuances**.           |
| **Business Partner**         | Vendor atau mitra bisnis penerbit tiket; dipilih pada LG dan muncul di grafik dashboard.                 |
| **Issued Number**            | Nomor unik LG (misalnya **FR0001/ARKA/LG/I/2026**); terisi otomatis setelah **Letter Number** dipilih.   |
| **Approver Selection**       | Pemilihan approver manual sebelum pengajuan FRF atau penerbitan LG.                                      |

---

## 1. Ringkasan Menu

| **Menu**              | **Navigasi (sidebar)**                                    | **Uraian**                                                 |
| :-------------------- | :-------------------------------------------------------- | :--------------------------------------------------------- |
| **Dashboard**         | **GAMMA SECTION** → **Flight Management** → **Dashboard** | Ringkasan permintaan tiket, status, dan aktivitas terkini. |
| **Requests**          | **GAMMA SECTION** → **Flight Management** → **Requests**  | Daftar seluruh FRF; buat, ubah, ajukan, batalkan, cetak.   |
| **Issuances**         | **GAMMA SECTION** → **Flight Management** → **Issuances** | Penerbitan LG dan detail tiket setelah persetujuan.        |
| **Reports**           | **GAMMA SECTION** → **Flight Management** → **Reports**   | Laporan permintaan tiket (sesuai hak akses).               |
| **My Flight Request** | **My Features** → **My Flight Request**                   | Self-service karyawan: daftar dan pengajuan FRF sendiri.   |

---

## 2. Untuk HR — **Flight Management Dashboard**

Halaman **Flight Management Dashboard** (_Flight Requests & Letter of Guarantee Overview_) memberi gambaran cepat volume FRF, LG, distribusi vendor, serta daftar aktivitas terbaru sebelum Anda membuka menu **Requests** atau **Issuances**.

### Langkah-langkah — membuka **Flight Management Dashboard**

1. **Login** ke ARKA HERO.
2. Buka **GAMMA SECTION** → **Flight Management** → **Dashboard**.
3. Baca ringkasan di layar:

**Bagian atas**

- **Total LG by Business Partner** — diagram pie jumlah LG per **Business Partner** (vendor tiket). Jika belum ada data, tampil pesan **Data not available**.
- **Flight Request, Issuance, and Vendor Statistic** — panel akordion tiga blok (klik judul untuk membuka/menutup):
    - **Flight Request** — hitungan **Total**, **Pending** (gabungan **Draft** + **Submitted**), **Approved**, **Issued**, **Completed**, dan **This Month** (FRF yang dibuat pada bulan berjalan).
    - **Letter of Guarantee (LG)** — hitungan **Total**, **Pending**, **Approved**, dan **This Month** (LG yang dibuat pada bulan berjalan).
    - **Total LG per Vendor** — daftar kode/nama **Business Partner** beserta jumlah LG; jika kosong, tampil **Belum ada data vendor**.

**Bagian tengah**

- **Recent Flight Requests** — tabel hingga delapan FRF terbaru: **FR Number**, **Employee**, **Status** (badge), **Created**, **Action** (ikon **View**). **View All** membuka menu **Requests**.
- **Recent Issuances (LG)** — tabel hingga delapan LG terbaru: **Issued Number**, **Business Partner**, **Status**, **Created**, **Action** (ikon **View**). **View All** membuka menu **Issuances**.

**Bagian bawah — Quick Actions**

- **Flight Requests** — pintasan ke daftar FRF.
- **Issuances (LG)** — pintasan ke daftar LG.
- **Create Letter of Guarantee** — pintasan langsung ke halaman pilih FRF untuk membuat LG (jika tombol tampil dan Anda berhak membuat LG).

<p align="center" id="flight-management-dashboard">
    <img
        src="images/flight-management-dashboard.png"
        alt="Flight Management Dashboard — pie Total LG by Business Partner, akordion Flight Request Letter of Guarantee Total LG per Vendor, tabel Recent Flight Requests dan Recent Issuances LG, Quick Actions Flight Requests Issuances Create Letter of Guarantee"
        style="max-width: 95%; width: 95%; height: auto;"
    />
<br><em>Gambar 2.1 — Flight Management Dashboard (ringkasan FRF, LG, dan aktivitas terbaru)</em>
</p>

**Catatan:**

- Angka pada dashboard mencakup **seluruh** FRF dan LG dalam sistem (bukan hanya milik satu project), kecuali dinyatakan lain di kebijakan internal.
- **Pending** pada **Flight Request** = status **Draft** + **Submitted**; **Pending** pada LG = status persetujuan LG yang masih menunggu.
- Tombol pada **Quick Actions** dan tautan **View All** hanya tampil sesuai **hak akses** akun Anda.

---

## 3. Untuk HR — **Requests** (membuat FRF)

### Langkah-langkah — membuka daftar **Flight Requests**

1. **Login** ke ARKA HERO.
2. Buka **GAMMA SECTION** → **Flight Management** → **Requests**.
3. Buka panel **Filter** (bilah biru dengan ikon corong) untuk menyaring data; tabel menampilkan **No**, **Form Number**, **Employee Name**, **NIK**, **Request Type**, **Purpose**, **Status**, **Requested At**, dan **Actions** (ikon **View**).
4. Untuk membuat FRF baru, klik **Add** di pojok kanan atas kartu daftar.

<p align="center" id="flight-requests-index">
    <img
        src="images/flight-requests-index.png"
        alt="Halaman Flight Requests — judul breadcrumb Home Flight Requests tombol Add bar Filter tabel No Form Number Employee Name NIK Request Type badge Travel Based Standalone Purpose Status Approved Requested At Actions View pagination"
        style="max-width: 90%; width: 90%; height: auto;"
    />
<br><em>Gambar 3.1 — Daftar Flight Requests (HR)</em>
</p>

### Langkah-langkah — **Create Flight Request** (alur umum)

1. Klik **Add** pada halaman daftar (atau pintasan dari dashboard jika tersedia).
2. Pada kartu **Request Information**, pilih **Select Request Type**, lalu ikuti langkah khusus per tipe:
    - **Leave Request (Cuti)** → [bagian Leave Based](#langkah-langkah--leave-request-cuti-leave-based) di bawah.
    - **Official Travel (LOT)** → [bagian Travel Based](#langkah-langkah--official-travel-lot-travel-based) di bawah.
    - **Standalone** → [bagian Standalone dengan Followers](#langkah-langkah--standalone-dengan-followers) di bawah.
3. Setelah data pemohon terisi, lengkapi **Flight Details** — **Add Flight Segment** untuk setiap segmen (**Departure City**, **Arrival City**, **Flight Date** wajib; **Flight Time (ETD)** dan **Airline** opsional).
4. Isi **Notes** (opsional) pada panel kanan dan **Approver Selection** (wajib saat mengajukan).
5. Klik **Create Flight Request** / **Submit** / **Save as Draft** sesuai tombol yang tampil.

Di daftar FRF, tipe ditampilkan sebagai badge **Leave Based**, **Travel Based**, atau **Standalone**.

---

### Langkah-langkah — **Leave Request (Cuti)** (Leave Based)

FRF **Leave Based** mengacu pada pengajuan cuti yang **sudah tercatat** di modul **Leave Management**. HR membuat atau melengkapi permintaan tiket untuk karyawan yang cutinya sudah ada.

1. Klik **Add** dari daftar **Flight Requests**.
2. Pada **Select Request Type**, pilih **Leave Request (Cuti)**.
3. Field **Select Leave Request** muncul — pilih baris cuti yang benar (biasanya memuat nomor register cuti, nama karyawan, dan rentang tanggal). Daftar diisi dari pengajuan cuti yang memenuhi syarat di sistem.
4. Setelah cuti dipilih, kartu **Employee Information** tampil dan terisi otomatis dari data cuti terpilih (**NAME**, **NIK**, **POSITION**, **DEPT/DIVISION**, **POH**, **DOH**, **PROJECT NUMBER**, **PHONE NUMBER**, **PURPOSE OF TRAVEL**, **TOTAL TRAVEL DAYS**). Periksa dan sesuaikan **Purpose of Travel** bila perlu.
5. Kartu **Followers** untuk input manual **tidak** digunakan pada tipe ini.
6. Lengkapi **Flight Details** — **Add Flight Segment** untuk setiap rute penerbangan.
7. Isi **Notes** (opsional) dan **Approver Selection**, lalu klik **Save as Draft**, **Save & Submit**, atau **Cancel** sesuai kebutuhan.

<p align="center" id="flight-create-leave-based">
    <img
        src="images/flight-request-create-leave-based.png"
        alt="Create Flight Request HR breadcrumb Flight Requests Create — Leave Request Cuti Select Leave Request 26LV Employee Information Frizky Ramadhan Flight Details Add Flight Segment Notes Approver Selection Save as Draft Save Submit Cancel"
        style="max-width: 90%; width: 90%; height: auto;"
    />
<br><em>Gambar 3.2 — Create FRF Leave Based (terikat pengajuan cuti)</em>
</p>

**Catatan:**

- Jika daftar **Select Leave Request** kosong, pastikan pengajuan cuti karyawan sudah ada dan memenuhi syarat di **Leave Management** (lihat panduan `11-leave-management.md`).
- FRF ini **bukan** pengganti formulir cuti; cuti tetap diproses di modul cuti, sedangkan FRF hanya mencatat kebutuhan tiket.

---

### Langkah-langkah — **Official Travel (LOT)** (Travel Based)

FRF **Travel Based** mengacu pada **Letter of Travel (LOT)** yang sudah tercatat di modul **Official Travel Management**. Data pemohon utama dan daftar **Followers** mengikuti LOT yang dipilih.

1. Klik **Add** dari daftar **Flight Requests**.
2. Pada **Select Request Type**, pilih **Official Travel (LOT)**.
3. Field **Select Official Travel** muncul — pilih LOT yang benar (biasanya memuat nomor surat/LOT, nama traveler, dan ringkasan perjalanan).
4. Setelah LOT dipilih, kartu **Employee Information** terisi otomatis dari data traveler LOT (**NAME**, **NIK**, jabatan, departemen, proyek, **Purpose of Travel**, **Total Travel Days**, dll.).
5. Kartu **Followers** tampil sebagai **baca saja** — daftar pengikut diambil dari detail LOT (nama, jabatan, departemen, proyek). **Tidak dapat** menambah, mengubah, atau menghapus pengikut di halaman FRF. Jika daftar salah, perbaiki pada pengajuan **Official Travel (LOT)** (lihat panduan `10-official-travel.md`).
6. Lengkapi **Flight Details** — **Add Flight Segment** sesuai rute tiket yang dibutuhkan.
7. Isi **Notes** (opsional) dan **Approver Selection**, lalu klik **Save as Draft**, **Save & Submit**, atau **Cancel** sesuai kebutuhan.

<p align="center" id="flight-create-official-travel-lot">
    <img
        src="images/flight-request-create-official-travel-lot.png"
        alt="Create Flight Request HR Official Travel LOT — Select Official Travel ARKA B0009 Employee Information Frizky Ramadhan kartu Followers Muhammad Fadhlan Ramadhan Flight Details Add Flight Segment Notes Approver Selection Save as Draft Save Submit"
        style="max-width: 90%; width: 90%; height: auto;"
    />
<br><em>Gambar 3.3 — Create FRF Travel Based (terikat LOT, Followers dari LOT)</em>
</p>

**Catatan:**

- Badge di daftar: **Travel Based**.
- Pengikut yang ikut terbang tetapi tidak tercatat di LOT tidak otomatis masuk FRF — perbarui LOT terlebih dahulu atau buat FRF **Standalone** terpisah sesuai kebijakan HR.

---

### Langkah-langkah — **Standalone** dengan **Followers**

Pada tipe **Standalone**, HR atau karyawan dapat menambahkan **pengikut** yang ikut naik pesawat. Pengikut **hanya** untuk **Standalone**; pada **Travel Based**, daftar mengikuti LOT.

**Data pemohon utama**

1. Pilih **Standalone** di **Select Request Type**.
2. Field **Select Employee** muncul — pilih karyawan dari dropdown (format **Nama (NIK)**). Setelah dipilih, kartu **Employee Information** tampil dengan data terisi otomatis (**NAME**, **NIK**, **POSITION**, **DEPT/DIVISION**, **POH**, **DOH**, **PROJECT NUMBER**, **PHONE NUMBER**). Lengkapi **PURPOSE OF TRAVEL** (wajib) dan **TOTAL TRAVEL DAYS** bila diperlukan.

<p align="center" id="flight-create-standalone-select-employee">
    <img
        src="images/flight-request-create-standalone-select-employee.png"
        alt="Create Flight Request Standalone HR — Select Employee Frizky Ramadhan Employee Information terisi otomatis Notes Approver Selection Save as Draft Save Submit"
        style="max-width: 90%; width: 90%; height: auto;"
    />
<br><em>Gambar 3.4 — Standalone: pilih karyawan di Select Employee (data pemohon utama)</em>
</p>

**Atau isian manual pemohon**

3. Centang **Fill employee information manually** pada kartu **Request Information**. Dropdown **Select Employee** tidak dipakai; kartu **Employee Information** tampil dengan field kosong siap diisi.
4. Ketik **NAME**, **ID NUMBER / NIK**, **POSITION**, **DEPT/DIVISION**, **PROJECT NUMBER**, **PHONE NUMBER**, **PURPOSE OF TRAVEL** (wajib), dan **TOTAL TRAVEL DAYS** pada tabel **Employee Information**. Field **POH** dan **DOH** umumnya tetap **—** pada mode manual.

<p align="center" id="flight-create-standalone-manual-employee">
    <img
        src="images/flight-request-create-standalone-manual-employee.png"
        alt="Create Flight Request Standalone HR — Fill employee information manually dicentang Employee Information placeholder Enter employee name NIK position department project phone purpose Notes Approver Selection"
        style="max-width: 90%; width: 90%; height: auto;"
    />
<br><em>Gambar 3.5 — Standalone: isian manual data pemohon utama</em>
</p>

**Menambah pengikut (opsional)**

5. Setelah kartu **Employee Information** tampil, kartu **Followers** muncul (opsional — standalone only).
6. Klik **Add Follower** untuk menambah baris.
7. **Per baris pengikut**, pilih salah satu cara (kolom tabel: **Employee / Manual**, **Title**, **Name**, **NIK / KTP**, **Phone**, **Action**):
    - **Dari data karyawan** — biarkan checkbox di kolom pertama **tidak** dicentang; pada dropdown **Select Employee** pilih baris **NIK - Nama** (misalnya **12730 - Suyanto**). Kolom **Name**, **NIK / KTP**, dan **Phone** terisi otomatis (baca saja). Pilih **Title** (**Mr.**, **Mrs.**, atau **Inf.**) bila perlu.
    - **Input manual** — centang checkbox di kolom pertama (mode manual per baris); dropdown karyawan disembunyikan. Isi **Title**, ketik **Name**, **NIK / KTP**, dan **Phone** pada field yang dapat diedit di baris tersebut.
8. Klik ikon **X** pada kolom **Action** untuk menghapus baris pengikut.
9. Lengkapi **Flight Details**, **Notes**, dan **Approver Selection**, lalu klik **Save as Draft**, **Save & Submit**, atau **Cancel**.

<p align="center" id="flight-create-standalone-followers">
    <img
        src="images/flight-request-create-standalone-followers.png"
        alt="Create Flight Request Standalone — kartu Followers optional standalone only Add Follower tabel baris 12730 Suyanto Mr Name NIK Phone otomatis baris manual Mrs Nabila Ayu Pus KTP Phone checkbox Employee Manual Title Action catatan info Select an employee"
        style="max-width: 90%; width: 90%; height: auto;"
    />
<br><em>Gambar 3.6 — Standalone: kartu Followers (karyawan + isian manual dalam satu tabel)</em>
</p>

**Catatan:**

- Pengikut **opsional**; jika hanya pemohon utama, biarkan **Followers** kosong.
- Petunjuk: pilih karyawan agar **Name**/NIK/Phone otomatis, centang checkbox untuk input manual per baris.
- Jika **Request Type** diubah dari **Standalone**, data pengikut FRF akan terhapus.
- Untuk **Travel Based**, edit pengikut di modul **Official Travel (LOT)**, bukan di FRF.

---

### Langkah-langkah — melihat detail FRF (HR)

1. Dari daftar **Flight Requests**, klik **View** pada baris yang dipilih.
2. Halaman detail menampilkan **Employee Information** (serta **REQUEST TYPE** — **Leave Based**, **Travel Based**, atau **Standalone**, dengan referensi cuti/LOT bila ada), **Followers** (dari LOT atau Standalone; tidak ada untuk Leave Based), **Flight Details**, **Selected Approvers**, dan **Notes**.
3. Pada **Travel Based**, kartu **Followers** menampilkan daftar dari LOT (nama, jabatan, NIK, departemen, proyek) beserta badge jumlah pengikut. Pada **Standalone**, pengikut karyawan menampilkan jabatan/departemen/proyek; pengikut manual hanya nama, NIK/KTP, dan telepon.
4. Tombol **Edit** (Draft), **Delete** (Draft), **Cancel**, **Print**, dan **Back to List** mengikuti status dan hak akses.
5. Jika FRF berstatus **Approved** atau **Issued**, kartu **Letter of Guarantee (LG)** tampil di **panel kanan** (di bawah **Selected Approvers**). Gunakan tombol **Add LG** pada kartu tersebut untuk membuat LG langsung dari FRF ini — lihat [Jalur 1 — dari detail FRF](#jalur-1--dari-detail-frf-approvedissued) ([Gambar 4.2](#flight-request-detail-add-lg)) di bagian **Issuances**. Jika LG sudah pernah dibuat, daftar LG tampil di kartu yang sama beserta tautan **View Details**.

<p align="center" id="flight-detail-standalone-followers">
    <img
        src="images/flight-request-detail-standalone-followers.png"
        alt="Detail Flight Request 26FRF-00019 Approved — Employee Information Desi Astika Indah kartu Followers Adelia Ramadhani Flight Details Balikpapan Jakarta Selected Approvers Approved kartu Letter of Guarantee Add LG Actions Back to List Cancel Print"
        style="max-width: 90%; width: 90%; height: auto;"
    />
<br><em>Gambar 3.8 — Detail FRF Approved (Standalone) dengan Followers, Flight Details, dan kartu Add LG</em>
</p>

### Langkah-langkah — **Edit Flight Request** (HR)

1. Buka detail FRF, lalu klik **Edit** (atau **Edit** langsung dari kolom **Actions** pada daftar jika tersedia).
2. Ubah **Request Type**, data pemohon, **Followers** (hanya **Standalone**), **Flight Details**, **Notes**, atau **Approver Selection** sesuai kebutuhan.
3. Klik **Update Flight Request** / **Submit** sesuai tombol yang tampil.
4. Setelah disimpan dari menu **Requests**, sistem mengarahkan kembali ke halaman detail FRF di **Flight Management** → **Requests**, bukan ke **My Flight Request**.

**Catatan:** Pengeditan umumnya hanya untuk status **Draft**.

---

## 4. Untuk HR — **Issuances** (Letter of Guarantee)

Setelah FRF berstatus **Approved** (atau **Issued** bila LG tambahan), tim HR menerbitkan **Letter of Guarantee (LG)**. LG dapat dibuat **langsung dari detail FRF** (pintasan **Add LG**) atau lewat menu **Issuances** bila perlu memilih beberapa FRF sekaligus. LG menghubungkan satu atau beberapa FRF dengan detail tiket per penumpang (booking code, harga, dll.).

### Langkah-langkah — membuka daftar **Flight Issuances**

1. **Login** ke ARKA HERO.
2. Buka **GAMMA SECTION** → **Flight Management** → **Issuances**.
3. Gunakan panel **Filter** untuk menyaring **Issued Number**, **FR Number**, **Business Partner**, **Date From**, dan **Date To**.
4. Tabel menampilkan **No**, **Issued Number**, **Issued Date**, **FR Number** (satu atau lebih FRF per baris), **Business Partner**, **Total Tickets**, **Total Price**, **Issued By**, dan **Actions** (**View**, **Edit**, **Print**).

<p align="center" id="flight-issuances-index">
    <img
        src="images/flight-issuances-index.png"
        alt="Flight Request Issuances Letter of Guarantee LG — breadcrumb Home Flight Issuances tombol Add panel Filter tabel Issued Number FR0005 ARKA LG Issued Date FR Number 26FRF Business Partner Total Tickets Total Price Issued By Administrator Actions View Edit Print pagination Showing entries"
        style="max-width: 90%; width: 90%; height: auto;"
    />
<br><em>Gambar 4.1 — Daftar Flight Issuances (HR)</em>
</p>

### Langkah-langkah — **Create Letter of Guarantee (LG)**

HR dapat membuat LG lewat **dua jalur**. Keduanya berakhir pada formulir yang sama (**Langkah B**); perbedaannya hanya cara memilih FRF referensi.

#### Jalur 1 — dari detail FRF (Approved/Issued)

Gunakan jalur ini bila Anda sudah membuka FRF yang akan diterbitkan LG-nya — umumnya setelah FRF disetujui approver.

1. Buka **GAMMA SECTION** → **Flight Management** → **Requests**.
2. Pada daftar, klik **View** pada FRF berstatus **Approved** (atau **Issued** jika menambah LG tambahan untuk FRF yang sama).
3. Pada halaman detail, lihat kartu **Letter of Guarantee (LG)** di **panel kanan** (di bawah **Selected Approvers**). Kartu ini tampil setelah FRF disetujui, atau jika FRF sudah pernah memiliki LG.
4. Jika belum ada LG, sistem menampilkan pesan _No Letter of Guarantee yet. Use **Add LG** above to create one._
5. Klik **Add LG** di pojok kanan header kartu. Tombol ini tampil bila FRF berstatus **Approved** atau **Issued** dan Anda memiliki hak membuat LG.
6. Halaman **Create Letter of Guarantee** terbuka **langsung** dengan FRF tersebut sebagai referensi — **tanpa** halaman pilih FRF. Lanjut ke [Langkah B — mengisi formulir LG](#langkah-b--mengisi-formulir-lg).

<p align="center" id="flight-request-detail-add-lg">
    <img
        src="images/flight-request-detail-add-lg.png"
        alt="Detail Flight Request 26FRF-00019 Approved — Employee Information Desi Astika Indah Official Travel LOT Selected Approvers Bernadeta Lita Eddy Nasri Approved kartu Letter of Guarantee LG tombol Add LG No Letter of Guarantee yet"
        style="max-width: 90%; width: 90%; height: auto;"
    />
<br><em>Gambar 4.2 — Detail FRF Approved dengan kartu Add LG (Jalur 1)</em>
</p>

**Catatan jalur 1:**

- Satu FRF dapat memiliki **beberapa LG**; tombol **Add LG** tetap tersedia pada status **Approved** dan **Issued**.
- Setelah LG tersimpan, kartu pada detail FRF menampilkan daftar LG (**Issued Number**, **Issued Date**, **Business Partner**, **Total Tickets**) dan tombol **View Details** ke halaman LG terkait.
- Status FRF dapat berubah menjadi **Issued** setelah LG pertama kali tersimpan.

#### Jalur 2 — dari menu Issuances (beberapa FRF sekaligus)

**Langkah A — memilih Flight Request**

1. Buka **GAMMA SECTION** → **Flight Management** → **Issuances**, lalu klik **Add** pada daftar LG.
2. Halaman **Select Flight Requests for LG** terbuka.
3. Gunakan panel **Filter** (**Status**, **Form Number**, **Date From**, **Date To**) untuk mempersempit daftar. Hanya FRF berstatus **Approved** atau **Issued** yang dapat dipilih.
4. Centang satu atau lebih baris FRF pada tabel (**Form Number**, **Employee Name**, **NIK**, **Purpose of Travel**, **Status**, **Requested At**). Penghitung di bawah tabel menampilkan jumlah baris terpilih (misalnya _0 selected_).
5. Klik **Continue with Selected FR**. Sistem memvalidasi bahwa FRF yang dipilih memenuhi syarat penerbitan LG. Klik **Cancel** untuk kembali ke daftar **Issuances** tanpa melanjutkan.
6. Lanjut ke [Langkah B — mengisi formulir LG](#langkah-b--mengisi-formulir-lg).

<p align="center" id="flight-issuances-select-fr">
    <img
        src="images/flight-issuances-select-fr.png"
        alt="Select Flight Requests for LG breadcrumb Home Flight Issuances Select FR panel Filter tabel 26FRF-00019 Desi Astika Indah 10254 kunjungan ke BO jakarta Approved 21 May 2026 Continue with Selected FR Cancel 0 selected filtered from 15 total entries"
        style="max-width: 90%; width: 90%; height: auto;"
    />
<br><em>Gambar 4.3 — Memilih FRF sebelum membuat LG (Jalur 2)</em>
</p>

#### Langkah B — mengisi formulir LG

1. Halaman **Create Flight Issuance** / **Create Letter of Guarantee** terbuka; FRF terpilih (dari **Jalur 1** atau **Langkah A**) tercatat sebagai ringkasan pada **sidebar kanan** (**FR Number**, nama/NIK, **Purpose**, **Travel Days**, **Flight Details**).
2. **Letter Number** — pada kartu **Letter Number**, pilih nomor surat kategori **FR** (wajib). Gunakan **Refresh List** atau **+ Create New** bila daftar kosong. Jika muncul peringatan _No available letter numbers found_, pastikan nomor surat kategori **FR** sudah tersedia untuk proyek Anda.
3. **LG Information** — **Issued Number** terisi otomatis (format `[Letter Number]/ARKA/LG/...`) setelah nomor surat dipilih; lengkapi **Issued Date** (wajib), **Business Partner** (opsional), dan **Notes** (opsional).
4. **Ticket Details** — klik **Add Ticket** untuk setiap penumpang yang perlu tiket:
    - **Passenger Name** — pilih karyawan dari dropdown **Select Employee**, **atau** centang **Manual** lalu ketik nama penumpang.
    - Isi **Booking Code**, **Detail Reservation**, **Ticket Price**, serta komponen biaya lain (**Service Charge**, **Service VAT**, **151 (Advance)**, **622 (Company)**) sesuai kebutuhan.
    - Ikon **X** pada kartu tiket menghapus baris tersebut.
5. Pada sidebar, gunakan ringkasan **Flight Request** (dan kartu **Followers** bila FRF memiliki pengikut) sebagai referensi siapa saja yang perlu tiket — pemohon utama dan pengikut dari FRF **Standalone** atau **Travel Based**.
6. **Approver Selection** — cari dan pilih approver LG pada panel kanan sebelum menyimpan.
7. Klik **Create LG**. Klik **Cancel** untuk kembali tanpa menyimpan. Status FRF terkait dapat berubah menjadi **Issued** setelah LG tersimpan.

<p align="center" id="flight-issuances-create">
    <img
        src="images/flight-issuances-create.png"
        alt="Create Flight Issuance Create LG — Letter Number No available letter numbers LG Information Issued Number Issued Date Ticket Details Passenger Name Manual sidebar Flight Request 26FRF-00019 Desi Astika Indah Flight Details Balikpapan Jakarta Followers Adelia Ramadhani Approver Selection Create LG Cancel"
        style="max-width: 90%; width: 90%; height: auto;"
    />
<br><em>Gambar 4.4 — Form Create LG (Letter Number, Ticket Details, ringkasan FRF dan Followers)</em>
</p>

**Catatan:**

- **Jalur 1** (detail FRF) cocok untuk satu FRF; **Jalur 2** (**Add** dari **Issuances**) cocok bila satu LG menghubungkan **beberapa FRF** sekaligus.
- Daftar **Followers** pada sidebar **tidak** menggantikan entri **Ticket Details**; HR tetap menambahkan baris tiket per penumpang yang benar-benar dipesan.
- Penumpang dapat dipilih dari data karyawan atau diinput **Manual** pada setiap baris tiket.

---

### Langkah-langkah — melihat detail LG (HR)

1. Dari daftar **Issuances**, klik **View** (ikon mata) pada baris LG.
2. Halaman detail menampilkan badge status LG (misalnya **Pending**) di header, lalu:
    - **LG Information** — **Issued Number**, **Issued Date**, **Letter Number**, **Business Partner**, **Issued By**, **Total Tickets**, **Total Price**, **Notes**.
    - **Ticket Details** — kartu per tiket (**Passenger**, **Booking Code**, **Detail Reservation**, rincian **Ticket Price**, **Service Charge**, **Service VAT**, **622 (Company)**, **151 (Advance)**).
    - **Related Flight Request(s)** — ringkasan FRF terkait (**FR Number**, **Purpose**, **Requested Date**) dengan tombol **View**, serta daftar **Followers** bila ada.
    - **Selected Approvers** — approver LG beserta status persetujuan.
3. Panel **Actions** menyediakan **Edit**, **Print**, **Delete**, dan **Back to List** sesuai status dan hak akses.

<p align="center" id="flight-issuances-detail">
    <img
        src="images/flight-issuances-detail.png"
        alt="Detail Letter of Guarantee FR0127 ARKA LG Pending — LG Information Total Tickets 2 Total Price Rp 2400000 Ticket Details Desi Astika Indah Adelia Ramadhani Booking Code Related Flight Request 26FRF-00019 Followers Selected Approvers Eddy Nasri Actions Edit Print Delete Back to List"
        style="max-width: 90%; width: 90%; height: auto;"
    />
<br><em>Gambar 4.5 — Detail LG (Pending) dengan Ticket Details, FRF terkait, dan Followers</em>
</p>

### Langkah-langkah — **Edit LG**

1. Buka detail LG, lalu klik **Edit** (atau **Edit** dari kolom **Actions** pada daftar).
2. Ubah **Letter Number**, **Issued Number**, **Issued Date**, **Business Partner**, **Notes**, **Ticket Details**, atau **Approver Selection**.
3. Klik **Update LG** untuk menyimpan, atau **Cancel** untuk kembali ke detail tanpa menyimpan.

**Catatan:** **Edit** mungkin tidak tersedia jika FRF terkait sudah berstatus **Completed** (sesuai aturan aplikasi).

### Langkah-langkah — **Print** dan **Delete**

- **Print** — dari detail atau **Actions** pada daftar, klik **Print** untuk membuka surat LG (format cetak); daftar **Followers** dari FRF terkait ikut tercantum bila ada.
- **Delete** — dari detail, klik **Delete**, konfirmasi penghapusan; gunakan hanya bila LG dibuat salah dan kebijakan internal mengizinkan.

---

<br>
<br>
<br>

## 5. Untuk karyawan — **My Flight Request**

**My Flight Request** digunakan untuk mengajukan permintaan tiket penerbangan, baik yang terkait dengan cuti, perjalanan dinas, maupun perjalanan mandiri (_standalone_).

**Navigasi:** **My Features** → **My Flight Request**

### Langkah-langkah — Daftar permintaan tiket & filter

Tabel pada halaman **My Flight Request** adalah **satu daftar gabungan** untuk semua permintaan tiket yang terkait dengan akun Anda. Selain pengajuan yang Anda mulai di sini lewat **New Request**, baris dapat **muncul otomatis** karena Anda sudah meminta tiket sebelumnya:

- **Berkaitan dengan cuti** — pada formulir **Create My Leave Request**, jika Anda mencentang **Flight Request** dan mengisi segmen penerbangan, sistem mencatat kebutuhan tiket yang terpasang pada nomor pengajuan cuti Anda. Di daftar tiket, permintaan itu biasanya dapat dikenali lewat filter **Request Type** → **Leave Based**.
- **Berkaitan dengan perjalanan dinas (LOT)** — pada form **Add My Official Travel (LOT)**, jika Anda mencentang **Check if you need flight ticket reservation**, kebutuhan tiket terikat dengan permohonan LOT (nomor **REQxxxxx** sampai HR mengonfirmasi). Di daftar, tipe yang dipakai umumnya **Travel Based**.

<p align="center" id="my-flight-requests-list">
    <img
        src="images/my-flight-requests-list.png"
        alt="Halaman My Flight Requests breadcrumb My Dashboard judul tabel kolom No Form Number Request Type badge Leave Based Travel Based Standalone Purpose Status Draft Requested At Actions View Edit tombol New Request bar Filter"
        style="max-width: 90%; width: 90%; height: auto;"
    />
<br><em>Gambar 5.1 — Daftar My Flight Requests</em>
</p>

Gunakan daftar ini untuk **memantau status** yang sama untuk semua jalur (**Draft**, **Submitted**, **Approved**, **Issued**, dll.) dan membuka detail **View** / **Edit** bila tombol tersebut tersedia. Kolom **Request Type** memuat badge seperti **Leave Based**, **Travel Based**, dan **Standalone**; tombol pada kolom **Actions** mengikuti status dan hak akses. Contoh halaman detail setelah **View**: status **Draft** pada Gambar 5.7; **Submitted** dengan kartu approver pada Gambar 5.8. Alur persetujuan cuti atau LOT tetap berjalan di modul masing-masing; permintaan tiket mengikuti kebijakan **Flight Management** dan koordinasi **HR HO Balikpapan**.

**Filter** tersedia:

- **Status** — Draft, Submitted, Approved, Issued, Completed, Rejected, Cancelled.
- **Request Type** — Standalone, Leave Based, Travel Based.
- **Form Number** — cari berdasarkan nomor formulir.

<br>

### Langkah-langkah — Membuat permintaan tiket baru

Klik **New Request**. Halaman **Create Flight Request** terbuka (breadcrumb umum: **My Dashboard / My Flight Requests / Create**). Isian tambahan di bawah **Request Type** mengikuti pilihan Anda; langkah **Flight Details** dan tombol akhir sama untuk semua jalur.

**Alur yang sama (semua Request Type)**

1. Di **Select Request Type**, pilih **Standalone**, **Leave Request (Cuti)**, atau **Official Travel (LOT)**. Di daftar permintaan, tipe itu terlihat sebagai badge **Standalone**, **Leave Based**, atau **Travel Based**.
2. Lengkapi blok yang muncul setelah langkah 1 — ringkasannya ada pada daftar **Perbedaan per tipe** di bawah (pemilih cuti/LOT, data karyawan, pengikut, dll.).
3. **Flight Details** — **Add Flight Segment** untuk tiap destination. Field wajib bertanda \*: **Departure City**, **Arrival City**, **Flight Date**. **Flight Time (ETD)** dan **Airline** bersifat pelengkap. Ikon **X** pada kartu segmen menghapus segmen tersebut.
4. **Notes** di panel kanan bersifat opsional.
5. **Create Flight Request** menyimpan/mengajukan sesuai alur aplikasi; **Cancel** keluar tanpa mengirim. Jika layar Anda menampilkan **Save as Draft** atau **Submit**, gunakan opsi itu — **Submit** mengarahkan permohonan ke pemrosesan tiket di HO Balikpapan dan dapat mengubah status menjadi **Submitted**.

**Perbedaan per tipe**

| **Pilihan di form**       | **Badge di daftar** | **Followers di formulir tiket**                                     |
| :------------------------ | :------------------ | :------------------------------------------------------------------ |
| **Standalone**            | **Standalone**      | Dapat ditambah di kartu **Followers** (opsional) — lihat Gambar 5.4 |
| **Leave Request (Cuti)**  | **Leave Based**     | Mengikuti cuti terpilih; tanpa blok pengikut terpisah               |
| **Official Travel (LOT)** | **Travel Based**    | Hanya baca dari LOT; perbaiki pengikut di modul LOT                 |

- **Standalone** — Tiket tidak terikat cuti atau LOT. **Employee Information** umumnya dari profil akun; centang **Fill employee information manually** bila Anda harus mengisi sendiri data pemohon (Gambar 5.2, Gambar 5.3). Pada **Standalone** saja, kartu **Followers** (opsional) memungkinkan menambah pengikut lewat **Add Follower**: pilih karyawan di dropdown (**NIK - Nama**) atau centang checkbox di baris untuk isian manual (**Title**, **Name**, **NIK / KTP**, **Phone**) — Gambar 5.4. Detail pengikut HR dijelaskan pada bagian **Standalone dengan Followers** (bab 3). Field yang masih kosong atau bertanda **—** ikuti perilaku form sampai terisi.
- **Leave Request (Cuti)** — Cuti yang dipilih harus sudah ada di modul cuti. Pada **Select Leave Request**, pilih baris yang tepat (biasanya memuat nomor register dan rentang tanggal). Data karyawan dan ringkasan perjalanan terisi otomatis dari cuti tersebut (Gambar 5.5).
- **Official Travel (LOT)** — LOT harus sudah tercatat di modul perjalanan dinas. Pada **Select Official Travel**, pilih LOT yang benar. Data pemohon utama dan blok **Followers** mengikuti LOT; **daftar pengikut tidak diubah di halaman tiket** — jika salah, perbaiki pada pengajuan LOT atau koordinasikan dengan HR (Gambar 5.6).

Pada **Leave Request** atau **Official Travel**, opsi mirip **Fill employee information manually** bila tampil mengikuti kebijakan layar Anda.

<p align="center" id="my-flight-create-standalone">
    <img
        src="images/my-flight-request-create-standalone.png"
        alt="Create Flight Request breadcrumb My Dashboard My Flight Requests Create Select Request Type Standalone Employee Information NAME NIK POSITION Purpose of Travel Flight Details Add Flight Segment Departure Arrival Flight Date ETD Airline Notes Create Flight Request Cancel"
        style="max-width: 79%; width: 79%; height: auto;"
    />
<br><em>Gambar 5.2 — Standalone (data dari profil)</em>
</p>

<p align="center" id="my-flight-create-standalone-manual">
    <img
        src="images/my-flight-request-create-standalone-manual-employee.png"
        alt="Create Flight Request Standalone Fill employee information manually checked Employee Information placeholders NAME NIK POSITION Flight Details Notes Create Flight Request"
        style="max-width: 79%; width: 79%; height: auto;"
    />
<br><em>Gambar 5.3 — Standalone isian manual</em>
</p>

<p align="center" id="my-flight-create-standalone-followers">
    <img
        src="images/my-flight-request-create-standalone-followers.png"
        alt="Create Flight Request Standalone — kartu Followers optional standalone only Add Follower tabel Employee Manual Title Name NIK KTP Phone checkbox karyawan atau manual Flight Details Create Flight Request"
        style="max-width: 90%; width: 90%; height: auto;"
    />
<br><em>Gambar 5.4 — Standalone dengan kartu Followers</em>
</p>

<p align="center" id="my-flight-create-leave-based">
    <img
        src="images/my-flight-request-create-leave-based.png"
        alt="Create Flight Request Leave Request Cuti Select Leave Request register tanggal Employee Information Purpose Total Travel Days Flight Details Add Flight Segment Notes Create Flight Request Cancel"
        style="max-width: 90%; width: 90%; height: auto;"
    />
<br><em>Gambar 5.5 — Leave Based</em>
</p>

<p align="center" id="my-flight-create-official-travel-lot">
    <img
        src="images/my-flight-request-create-official-travel-lot.png"
        alt="Create Flight Request Official Travel LOT Select Official Travel ARKA Employee Information Purpose Total Travel Days Followers badge Flight Details Add Flight Segment Notes Create Flight Request Cancel"
        style="max-width: 90%; width: 90%; height: auto;"
    />
<br><em>Gambar 5.6 — Travel Based (LOT)</em>
</p>

**Catatan:** Setelah tiket diterbitkan oleh tim HO Balikpapan, status berubah menjadi **Issued**. Pastikan data penumpang dan rute sudah benar sebelum mengajukan.

### Melihat detail permintaan tiket

Pada baris yang dipilih di daftar, klik **View** untuk membuka halaman ringkas satu permohonan tiket.

**Status Draft** pada badge judul berarti permintaan **belum selesai dalam alur pemrosesan HR / tim tiket** pada tahap tersebut — umumnya belum setara dengan pengajuan yang sudah **Submitted**, disetujui (**Approved**), atau tiket sudah **Issued**. Anda masih dapat mengoreksi data lewat **Edit** jika tombol tampil, menghapus (**Delete**), membatalkan (**Cancel**), atau kembali ke daftar (**Back to My Requests**). Tombol **Print** memuat cetak ringkas jika diperlukan; kombinasi tombol mengikuti status dan hak akses akun.

Cuplikan berikut adalah contoh detail permintaan **Travel Based** yang mengacu pada **Official Travel (LOT)** (nomor surat dan ringkasan perjalanan di blok **REQUEST TYPE**), lengkap dengan **Followers** dari LOT dan satu segmen **Flight Details** — masih **Draft** sebelum diproses lebih lanjut oleh HR.

<p align="center" id="my-flight-request-detail-draft">
    <img
        src="images/my-flight-request-detail-draft-travel-based.png"
        alt="Detail flight request Draft 26FRF Employee Information REQUEST TYPE Official Travel LOT ARKA Followers Flight Details Balikpapan Bali Actions Back Edit Delete Cancel Print"
        style="max-width: 90%; width: 90%; height: auto;"
    />
<br><em>Gambar 5.7 — Detail tiket Draft (Travel Based)</em>
</p>

Setelah permintaan **diajukan** dan masuk ke pemrosesan HR / alur tiket, badge status pada judul detail dapat berubah menjadi **Submitted** (atau status lain yang ditampilkan di lingkungan Anda). Pada tahap ini, halaman detail biasanya menampilkan kartu **Selected Approvers** berisi daftar approver dengan badge status tiap orang (**Pending** sampai ada yang menyetujui atau menolak). Konten **Employee Information**, **Followers**, dan **Flight Details** tetap sebagai referensi; Anda **tidak lagi menyunting** data utama seperti pada **Draft** — tombol **Edit** / **Delete** sering **hilang** atau tidak tersedia. Panel **Actions** pada contoh berikut menyediakan **Back to My Requests**, **Cancel**, dan **Print** (susunan pasti mengikuti status dan hak akses).

<p align="center" id="my-flight-request-detail-submitted">
    <img
        src="images/my-flight-request-detail-submitted-travel-based.png"
        alt="Detail flight request Submitted badge Selected Approvers Pending Employee Information Official Travel LOT Followers Flight Details Actions Back Cancel Print"
        style="max-width: 90%; width: 90%; height: auto;"
    />
<br><em>Gambar 5.8 — Detail tiket Submitted</em>
</p>

### Membatalkan permintaan tiket

Pembatalan dilakukan dari **halaman detail** (klik **View** pada baris di daftar).

1. Pada panel **Actions**, klik tombol **Cancel** jika tombol tersebut tampil.
2. Di jendela **Cancel Flight Request**, isi **Reason for Cancellation** (wajib).
3. Klik **Cancel Request** untuk mengirim pembatalan, atau **Close** untuk menutup jendela tanpa mengubah data.

<p align="center" id="my-flight-request-cancel-modal">
    <img
        src="images/my-flight-request-cancel-modal.png"
        alt="Modal Cancel Flight Request judul field Reason for Cancellation wajib tombol Close dan Cancel Request"
        style="max-width: 50%; width: 50%; height: auto;"
    />
<br><em>Gambar 5.9 — Modal Cancel Flight Request</em>
</p>

Setelah berhasil, status permintaan menjadi **Cancelled**. Tombol **Cancel** **tidak** ditampilkan untuk status yang sudah tidak bisa dibatalkan melalui jalur ini — misalnya **Issued**, **Completed**, **Rejected**, atau jika permintaan sudah **Cancelled**. Sesuai aturan aplikasi, pembatalan dari akun karyawan umumnya masih diperbolehkan pada status **Draft**, **Submitted**, atau **Approved** (sampai tiket diterbitkan atau alur menutup opsi tersebut).

---

## 6. Kesalahan & bantuan

| Gejala / pesan (contoh)                | Kemungkinan penyebab                                                                                 | Apa yang bisa dicoba                                                                             |
| :------------------------------------- | :--------------------------------------------------------------------------------------------------- | :----------------------------------------------------------------------------------------------- |
| Kartu **Followers** tidak muncul       | **Request Type** bukan **Standalone** / **Travel Based**, atau **Employee Information** belum tampil | **Standalone**: lengkapi pemohon dulu; **Travel Based**: pilih LOT dulu agar pengikut LOT tampil |
| **Select Leave Request** kosong        | Belum ada pengajuan cuti yang memenuhi syarat                                                        | Buat/verifikasi cuti di **Leave Management** terlebih dahulu                                     |
| **Select Official Travel** kosong      | Belum ada LOT yang memenuhi syarat                                                                   | Buat/verifikasi LOT di **Official Travel Management** terlebih dahulu                            |
| Validasi pengikut manual               | **Name**, **NIK**, atau **Phone** kosong saat **Fill follower information manually** dicentang       | Lengkapi ketiga field pada baris yang bermasalah                                                 |
| Validasi pengikut karyawan             | Dropdown karyawan belum dipilih                                                                      | Pilih **NIK - Nama** pada baris tersebut                                                         |
| Pengikut LOT salah                     | FRF bertipe **Travel Based**                                                                         | Ubah pengikut di pengajuan **Official Travel (LOT)**, bukan di FRF                               |
| Tidak bisa mengubah FRF                | Status bukan **Draft** / **Submitted** (sesuai kebijakan)                                            | Hubungi HR; buat permintaan baru jika diperlukan                                                 |
| **Continue with Selected FR** nonaktif | Belum ada FRF dicentang, atau FRF tidak memenuhi syarat LG                                           | Centang minimal satu FRF berstatus **Approved** / **Issued**                                     |
| Tidak bisa **Edit LG**                 | FRF terkait sudah **Completed**                                                                      | Koordinasikan dengan administrator; buat LG baru jika diperlukan                                 |
| Validasi tiket LG                      | **Passenger Name** kosong saat **Manual** tidak dicentang / sebaliknya                               | Pilih karyawan **atau** centang **Manual** dan isi nama                                          |

### Menghubungi administrator

Sertakan **username**, waktu kejadian, menu yang dibuka (**Flight Requests** atau **My Flight Request**), **Form Number** FRF, dan cuplikan pesan error. Jangan mengirim kata sandi.

</div>

---
