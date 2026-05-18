# Roster Management

<div style="text-align: justify; text-justify: inter-word;">

Panduan ini ditujukan kepada **HR** dan administrator yang mengelola jadwal kerja periodik karyawan pada project bertipe **roster**. Modul **Roster Management** mencakup tiga area utama: **Dashboard** ringkasan statistik, halaman **Rosters** untuk manajemen jadwal dan siklus kerja per karyawan, serta **Periodic Leave Requests** untuk pengajuan cuti periodik secara massal.

---

## Glosarium

| **Istilah**                | Arti singkat                                                                                               |
| :------------------------- | :--------------------------------------------------------------------------------------------------------- |
| **Roster**                 | Rekaman jadwal kerja dan cuti periodik milik satu karyawan dalam satu project.                             |
| **Roster Cycle**           | Satu putaran kerja–cuti dalam jadwal roster, terdiri atas **Work Period** dan **Leave Period**.            |
| **Cycle No**               | Nomor urut siklus dalam satu roster; dimulai dari 1.                                                       |
| **Work Period**            | Rentang tanggal kerja aktif dalam satu siklus (Work Start → Work End).                                     |
| **Leave Period**           | Rentang tanggal cuti dalam satu siklus (Leave Start → Leave End), secara default 15 hari setelah Work End. |
| **Work Days**              | Jumlah hari kerja dalam satu siklus, ditentukan oleh konfigurasi **Level** karyawan.                       |
| **Adjusted Days**          | Koreksi jumlah hari kerja terhadap siklus tertentu; positif (+) menambah, negatif (−) mengurangi.          |
| **Leave Entitlement**      | Hak cuti yang terakumulasi dari satu siklus, dihitung berdasarkan Work Days aktual.                        |
| **Accumulated Leave**      | Total hak cuti yang sudah terkumpul dari seluruh siklus yang telah selesai.                                |
| **Leave Taken**            | Total hari cuti yang sudah diambil (disetujui).                                                            |
| **Leave Balance**          | Sisa hak cuti = Accumulated Leave − Leave Taken.                                                           |
| **Work Days Difference**   | Selisih hari kerja setelah memperhitungkan **FB Cycle Ratio**; dipakai untuk deteksi _balancing_.          |
| **FB Cycle Ratio**         | Rasio konversi hari kerja ke hari cuti berdasarkan pola roster level karyawan.                             |
| **Pattern**                | Pola siklus roster (misalnya 63/15) yang ditentukan dari konfigurasi Level.                                |
| **Balancing Roster Cycle** | Kondisi roster yang perlu penyesuaian hari kerja agar siklus tetap seimbang.                               |
| **Periodic Leave**         | Cuti periodik yang diajukan secara massal oleh HR untuk sekelompok karyawan roster yang sudah jatuh tempo. |
| **Batch**                  | Satu kumpulan pengajuan cuti periodik yang disubmit sekaligus; diidentifikasi oleh **Batch ID**.           |
| **Batch ID**               | Kode unik yang dihasilkan sistem untuk setiap pengajuan massal.                                            |
| **Due**                    | Status karyawan yang sudah memasuki atau mendekati masa cuti (Leave Period).                               |
| **W** _(Work Day)_         | Penanda pada kalender: karyawan sedang dalam periode kerja.                                                |
| **L** _(Leave Day)_        | Penanda pada kalender: karyawan sedang dalam periode cuti.                                                 |
| **Off Day**                | Hari di luar Work Period maupun Leave Period; ditampilkan tanda "−" di kalender.                           |
| **Level**                  | Kategori jabatan karyawan yang menentukan jumlah Work Days dan pola roster.                                |
| **Look Ahead Days**        | Jumlah hari ke depan yang dipakai sebagai rentang pencarian karyawan yang akan memasuki masa cuti.         |

---

## 1. Ringkasan Menu

Semua menu berikut dapat diakses melalui sidebar grup **HERO SECTION** → **Roster Management**:

| **Menu**                    | Uraian                                                                                             |
| :-------------------------- | :------------------------------------------------------------------------------------------------- |
| **Dashboard**               | Ringkasan statistik roster, tabel _balancing_, permintaan cuti terbaru, dan statistik project.     |
| **Rosters**                 | Daftar karyawan roster per project; kelola siklus kerja, impor/ekspor data, dan tampilan kalender. |
| **Periodic Leave Requests** | Daftar dan pembuatan pengajuan cuti periodik secara massal per batch.                              |

---

## 2. Dashboard

Halaman **Dashboard** memberikan gambaran menyeluruh kondisi roster di seluruh project yang dapat diakses pengguna. Navigasi: **HERO SECTION** → **Roster Management** → **Dashboard**.

<p align="center" id="gambar-1-1">
    <img
        src="images/roster-dashboard.png"
        alt="Halaman Dashboard Roster Management menampilkan empat kartu statistik di baris atas: Total Rosters, Active Cycles, On Leave, dan Periodic Requests"
        style="max-width: 90%; width: 90%; height: auto;"
    />
    <br><em>Gambar 1.1 — Dashboard Roster Management</em>
</p>

### Kartu Statistik

Baris paling atas menampilkan empat kartu ringkasan:

- **Total Rosters** — Jumlah seluruh roster aktif yang terdaftar di semua project yang bisa diakses.
- **Active Cycles** — Jumlah siklus yang sedang berjalan saat ini (periode kerja atau cuti), beserta persentasenya terhadap total siklus.
- **On Leave** — Jumlah siklus yang saat ini berada di periode cuti.
- **Periodic Requests** — Jumlah pengajuan cuti periodik yang dibuat pada bulan berjalan, beserta persentase dari total seluruh batch.

### Balancing Roster Cycle

Tabel **Balancing Roster Cycle** menampilkan daftar karyawan yang jadwal kerjanya memerlukan penyesuaian. Kolom yang tersedia:

- **Employee** — Nama lengkap karyawan.
- **NIK** — Nomor Induk Karyawan.
- **Project** — Kode project tempat karyawan bekerja.
- **Work Days Diff** — Selisih hari kerja; badge hijau berarti surplus, badge merah berarti defisit.
- **Action** — Tombol **View** untuk langsung membuka detail roster karyawan bersangkutan.

<p align="center" id="gambar-1-2">
    <img
        src="images/roster-dashboard-balancing.png"
        alt="Tabel Balancing Roster Cycle menampilkan kolom Employee, NIK, Project, Work Days Diff dengan badge warna, dan tombol View di kolom Action"
        style="max-width: 90%; width: 90%; height: auto;"
    />
    <br><em>Gambar 1.2 — Tabel Balancing Roster Cycle</em>
</p>

### Recent Periodic Leave Requests

Tabel **Recent Periodic Leave Requests** di sisi kanan menampilkan lima batch pengajuan cuti terbaru dengan kolom **Batch ID**, **Notes**, **Total** (jumlah request dalam batch), **Status**, dan **Action** (tombol **View** menuju detail batch).

### Project Statistics

Tabel **Project Statistics** di bagian bawah merangkum kondisi roster per project:

- **Project Code** — Kode project.
- **Total Rosters** — Jumlah roster terdaftar di project tersebut.
- **Active Employees** — Jumlah karyawan aktif yang memiliki roster di project tersebut.

---

## 3. Roster Management — Daftar & Siklus Kerja

Halaman **Rosters** adalah pusat pengelolaan jadwal kerja tiap karyawan. Navigasi: **HERO SECTION** → **Roster Management** → **Rosters**.

### 3.1 Daftar Roster per Proyek

Halaman diawali dengan kartu **Project Filter** untuk memilih project terlebih dahulu sebelum daftar karyawan muncul.

**Cara menampilkan daftar karyawan:**

1. Pada field **Select Project**, pilih project yang diinginkan. Daftar karyawan akan muncul secara otomatis.
2. Optionally, ketik NIK atau nama di field **Search Employee** lalu klik **Filter** untuk mempersempit hasil.

<p align="center" id="gambar-3-1">
    <img
        src="images/roster-index-filter.png"
        alt="Kartu Project Filter pada halaman Rosters dengan dropdown Select Project, field Search Employee, dan tombol Filter"
        style="max-width: 90%; width: 90%; height: auto;"
    />
    <br><em>Gambar 3.1 — Filter project pada halaman Rosters</em>
</p>

Setelah project dipilih, kartu **Employees** menampilkan tabel dengan kolom berikut:

| Kolom         | Keterangan                                                                                                                         |
| :------------ | :--------------------------------------------------------------------------------------------------------------------------------- |
| **NIK**       | Nomor Induk Karyawan.                                                                                                              |
| **Full Name** | Nama lengkap karyawan.                                                                                                             |
| **Position**  | Nama jabatan.                                                                                                                      |
| **Level**     | Kategori level jabatan (ditampilkan sebagai badge).                                                                                |
| **Pattern**   | Pola siklus roster dari konfigurasi Level (misalnya `63/15`). Badge abu-abu berarti belum ada konfigurasi.                         |
| **Cycles**    | Jumlah siklus yang sudah terdaftar dalam roster karyawan tersebut.                                                                 |
| **Status**    | Status siklus aktif saat ini: **Active** (hijau), **On Leave** (kuning), **No Active Cycle** (biru), atau **No Roster** (abu-abu). |
| **Action**    | Tombol aksi sesuai kondisi roster (lihat [bagian 3.2](#section-3-2-create)).                                                       |

<p align="center" id="gambar-3-2">
    <img
        src="images/roster-index-list.png"
        alt="Tabel Employees pada halaman Rosters menampilkan kolom NIK, Full Name, Position, Level, Pattern, Cycles, Status, dan Action"
        style="max-width: 90%; width: 90%; height: auto;"
    />
    <br><em>Gambar 3.2 — Daftar karyawan roster per project</em>
</p>

### 3.2 Membuat Roster Baru

<a id="section-3-2-create"></a>

Karyawan yang **belum memiliki roster** dan levelnya sudah dikonfigurasi akan menampilkan tombol **Create** di kolom **Action**.

**Langkah-langkah:**

1. Pada baris karyawan yang belum memiliki roster, klik tombol **Create** (ikon +).
2. Konfirmasi dialog **Create Roster** yang muncul dengan mengklik **Yes, Create**.
3. Sistem akan membuat roster kosong (0 cycles) untuk karyawan tersebut.
4. Kolom **Cycles** berubah menjadi `0 cycles` dan kolom **Action** berganti menampilkan tombol **View** dan **Delete**.

**Catatan:** Jika karyawan belum memiliki konfigurasi roster di Level-nya, kolom **Action** menampilkan badge **Not Available** dan roster tidak dapat dibuat.

<p align="center" id="gambar-3-3">
    <img
        src="images/roster-create-modal.png"
        alt="Dialog konfirmasi Create Roster dengan nama karyawan, tombol Yes Create berwarna hijau, dan tombol Cancel"
        style="max-width: 50%; width: 50%; height: auto;"
    />
    <br><em>Gambar 3.3 — Dialog konfirmasi pembuatan roster</em>
</p>

**Menghapus roster:** Klik tombol hapus (ikon tempat sampah merah) pada baris karyawan, lalu konfirmasi dialog **Delete Roster**. Seluruh data siklus akan ikut terhapus.

### 3.3 Detail Roster & Cycle Management

Klik tombol **View** (ikon mata biru) pada baris karyawan untuk membuka halaman detail roster.

#### Informasi Karyawan

Kartu **Employee Information** menampilkan data dasar:

- Kolom kiri: **NIK**, **Full Name**, **Position**, **Department**.
- Kolom kanan: **Project**, **Level**, **Roster Cycle** (pola, misalnya `63/15`), **FB Cycle Ratio** (rasio konversi hari kerja ke hari cuti).

#### Statistik Siklus

Empat kartu statistik di bawahnya menampilkan ringkasan akumulasi:

- **Accumulated Leave** — Total hak cuti yang sudah terakumulasi dari seluruh siklus.
- **Leave Taken** — Total hari cuti yang sudah diambil.
- **Leave Balance** — Sisa saldo cuti.
- **Work Days Difference** — Selisih hari kerja setelah diperhitungkan dengan FB Cycle Ratio; nilai positif berarti surplus kerja, nilai negatif perlu _balancing_.

<p align="center" id="gambar-3-4">
    <img
        src="images/roster-show-stats.png"
        alt="Halaman detail roster menampilkan kartu Employee Information di atas dan empat kartu statistik Accumulated Leave, Leave Taken, Leave Balance, Work Days Difference"
        style="max-width: 90%; width: 90%; height: auto;"
    />
    <br><em>Gambar 3.4 — Informasi karyawan dan statistik siklus</em>
</p>

#### Tabel Cycle Management

Kartu **Cycle Management** menampilkan seluruh siklus dengan kolom:

| Kolom            | Keterangan                                                                 |
| :--------------- | :------------------------------------------------------------------------- |
| **Cycle**        | Nomor siklus (misalnya `#1`, `#2`).                                        |
| **Work Period**  | Rentang tanggal kerja (format dd/mm/yyyy).                                 |
| **Work Days**    | Jumlah hari kerja aktual pada siklus ini.                                  |
| **Adjusted**     | Nilai penyesuaian; hijau (+), merah (−), abu-abu (0).                      |
| **Leave Period** | Rentang tanggal cuti; tanda `−` jika belum ditetapkan.                     |
| **Leave Days**   | Jumlah hari cuti; tanda `−` jika belum ada Leave Period.                   |
| **Entitlement**  | Hak cuti yang terakumulasi dari siklus ini (desimal).                      |
| **Status**       | Status siklus saat ini (misalnya **Active**, **On Leave**, **Completed**). |
| **Action**       | Tombol **View** (detail), **Edit** (ubah), **Delete** (hapus siklus).      |

Jika karyawan memiliki **Remarks** pada suatu siklus, catatan tersebut ditampilkan di baris abu-abu tepat di bawah baris siklus bersangkutan.

<p align="center" id="gambar-3-5">
    <img
        src="images/roster-show-cycles.png"
        alt="Tabel Cycle Management menampilkan baris-baris siklus dengan kolom Work Period, Work Days, Adjusted, Leave Period, Leave Days, Entitlement, Status, dan tombol aksi"
        style="max-width: 90%; width: 90%; height: auto;"
    />
    <br><em>Gambar 3.5 — Tabel Cycle Management</em>
</p>

#### Menambah Siklus Baru

Klik tombol **Add Cycle** di pojok kanan atas kartu **Cycle Management**. Modal **Add Cycle** akan terbuka.

**Penjelasan singkat — modal Add/Edit Cycle:**

- **Work Period** (kiri):
    - **Start Date** — Tanggal mulai kerja. Jika sudah ada siklus sebelumnya, nilai ini terisi otomatis dari Leave End siklus terakhir + 1 hari (tetap bisa diubah).
    - **End Date** — Dihitung otomatis: Work Start + Work Days (dari Level) + Adjusted Days. Field ini _read-only_.
    - **Adjusted Days** — Koreksi hari kerja; isi angka positif untuk menambah, negatif untuk mengurangi.
- **Leave Period** (kanan):
    - **Start Date** — Dihitung otomatis: Work End + 1 hari. _Read-only_.
    - **End Date** — Dihitung otomatis: Leave Start + 15 hari. _Read-only_.
- **Remarks** — Catatan opsional untuk siklus ini.

<p align="center" id="gambar-3-6">
    <img
        src="images/roster-cycle-modal.png"
        alt="Modal Add Cycle dengan dua panel: Work Period (Start Date, End Date read-only, Adjusted Days) dan Leave Period (Start Date dan End Date read-only), serta field Remarks dan tombol Save"
        style="max-width: 80%; width: 80%; height: auto;"
    />
    <br><em>Gambar 3.6 — Modal tambah/ubah siklus</em>
</p>

Klik **Save** untuk menyimpan. Halaman akan dimuat ulang dan siklus baru muncul di tabel.

#### Mengedit atau Menghapus Siklus

- **Edit:** Klik tombol edit (ikon pensil kuning). Modal yang sama terbuka dengan data siklus yang ada; ubah **Start Date** atau **Adjusted Days**, lalu klik **Save**.
- **Delete:** Klik tombol hapus (ikon tempat sampah merah), lalu konfirmasi dialog **Delete Cycle**. Siklus akan dihapus permanen.

#### Catatan Kebijakan Perusahaan

Di bagian bawah halaman terdapat kartu **Note** yang memuat kebijakan terkait cuti yang dimundurkan atau dimajukan:

1. Cuti dimundurkan karena kebutuhan perusahaan → karyawan berhak mendapat kompensasi pada cuti berikutnya.
2. Cuti dimundurkan karena keperluan pribadi → tetap bekerja sesuai jumlah hari kerja jabatan.
3. Cuti dimajukan karena keperluan perusahaan → hari kerja berikutnya tetap sesuai ketetapan.
4. Cuti dimajukan karena keperluan pribadi → kekurangan hari kerja ditambahkan ke siklus berikutnya.

---

### 3.4 Impor Data Roster dari Excel

Fitur **Import** memungkinkan pengunggahan data roster secara massal dari file Excel.

**Langkah-langkah:**

1. Di halaman **Rosters** (setelah memilih project atau tanpa pilih project), klik tombol **Import** (biru) di pojok kanan atas kartu **Project Filter**. Modal **Import Roster Data** terbuka.
2. Klik **Select Excel File**, lalu pilih file berformat `.xlsx` atau `.xls` (maksimal 10 MB).
3. Klik **Import** untuk memulai proses unggah.

**Format kolom Excel yang diterima (sesuai urutan):**

| Kolom         | Wajib | Keterangan            |
| :------------ | :---: | :-------------------- |
| NIK           |   ✓   | Nomor Induk Karyawan  |
| Full Name     |   —   | Informasional         |
| Position      |   —   | Informasional         |
| Level         |   —   | Informasional         |
| Pattern       |   —   | Informasional         |
| Cycle No      |   ✓   | Nomor siklus          |
| Work Start    |   ✓   | Tanggal mulai kerja   |
| Work End      |   ✓   | Tanggal selesai kerja |
| Adjusted Days |   —   | Koreksi hari kerja    |
| Leave Start   |   —   | Tanggal mulai cuti    |
| Leave End     |   —   | Tanggal selesai cuti  |
| Remarks       |   —   | Catatan               |
| Status        |   —   | Informasional         |

Jika ada baris yang gagal divalidasi, sistem menampilkan daftar kesalahan di dalam modal (baris mana dan kolom apa yang bermasalah).

<p align="center" id="gambar-3-7">
    <img
        src="images/roster-import-modal.png"
        alt="Modal Import Roster Data dengan field pilih file Excel, keterangan format kolom, dan tombol Import berwarna biru"
        style="max-width: 60%; width: 60%; height: auto;"
    />
    <br><em>Gambar 3.7 — Modal impor data roster dari Excel</em>
</p>

### 3.5 Ekspor Data Roster

Setelah memilih project di halaman **Rosters**, klik tombol **Export** (hijau) di pojok kanan atas. File Excel akan langsung diunduh berisi data seluruh roster untuk project yang dipilih.

**Catatan:** Tombol **Export** hanya aktif apabila project sudah dipilih.

### 3.6 Calendar View

Tampilan **Calendar View** memperlihatkan status kerja dan cuti seluruh karyawan roster dalam satu project secara visual per hari.

**Cara mengakses:** Dari halaman **Rosters** (setelah memilih project), klik tombol **Calendar View** (kuning). Atau navigasi langsung via URL kalender.

**Filter yang tersedia:**

- **Select Project** — Pilih project (wajib).
- **Month** — Pilih bulan.
- **Year** — Pilih tahun.

Klik **Filter** untuk menampilkan data.

**Legenda warna:**

| Badge                       | Arti                                                |
| :-------------------------- | :-------------------------------------------------- |
| **W** (putih dengan border) | **Work Day** — Karyawan sedang dalam periode kerja. |
| **L** (hijau muda)          | **Leave Day** — Karyawan sedang dalam periode cuti. |
| **−** (abu-abu)             | **Off Day** — Di luar periode kerja/cuti.           |

Nama karyawan pada kolom pertama adalah tautan yang dapat diklik untuk langsung membuka halaman detail roster.

<p align="center" id="gambar-3-8">
    <img
        src="images/roster-calendar.png"
        alt="Calendar View menampilkan tabel dengan kolom Employee, NIK, Position, diikuti kolom tanggal 1 hingga 31; setiap sel berisi badge W (Work Day), L (Leave Day), atau tanda abu-abu (Off Day)"
        style="max-width: 90%; width: 90%; height: auto;"
    />
    <br><em>Gambar 3.8 — Calendar View roster per project</em>
</p>

**Catatan:** Kolom **Employee**, **NIK**, dan **Position** bersifat _sticky_ (tetap terlihat saat tabel digulir ke kanan).

---

## 4. Periodic Leave Requests

Menu **Periodic Leave Requests** digunakan untuk mengajukan cuti periodik secara massal bagi karyawan roster yang sudah memasuki atau mendekati masa cuti. Navigasi: **HERO SECTION** → **Roster Management** → **Periodic Leave Requests**.

### 4.1 Daftar Batch

Halaman indeks menampilkan daftar seluruh batch pengajuan yang pernah dibuat, dengan kolom:

- **Batch ID** — Kode unik batch; klik untuk membuka detail.
- **Total Requests** — Jumlah pengajuan cuti dalam batch ini.
- **Notes** — Catatan singkat yang diisi saat pembuatan (jika ada).
- **Created At** — Tanggal dan waktu pembuatan batch.
- **Actions** — Tombol **View** (ikon mata) untuk membuka detail batch.

<p align="center" id="gambar-4-1">
    <img
        src="images/roster-periodic-index.png"
        alt="Halaman Periodic Leave Requests menampilkan tabel daftar batch dengan kolom Batch ID, Total Requests, Notes, Created At, dan Actions"
        style="max-width: 90%; width: 90%; height: auto;"
    />
    <br><em>Gambar 4.1 — Daftar batch Periodic Leave Requests</em>
</p>

Klik tombol **Create Periodic Leave Request** di pojok kanan atas untuk membuat pengajuan baru.

### 4.2 Membuat Periodic Leave Request

Halaman **Create Periodic Leave Request** terdiri atas beberapa bagian yang harus diisi secara berurutan.

#### Langkah-langkah — Filter Karyawan

**1. Filter Periodic Leave Employees**

Isi filter berikut lalu klik **Search Employees**:

- **Project** _(wajib)_ — Pilih project bertipe roster.
- **Department** _(opsional)_ — Biarkan kosong untuk semua departemen, atau pilih departemen tertentu.
- **Look Ahead Days** — Jumlah hari ke depan sebagai rentang pencarian karyawan yang akan memasuki masa cuti (default: 14 hari, maksimal 60 hari).

Klik **Search Employees** untuk memuat daftar karyawan.

<p align="center" id="gambar-4-2">
    <img
        src="images/roster-periodic-create-filter.png"
        alt="Bagian Filter Periodic Leave Employees dengan dropdown Project, Department, input Look Ahead Days, dan tombol Search Employees"
        style="max-width: 90%; width: 90%; height: auto;"
    />
    <br><em>Gambar 4.2 — Filter pencarian karyawan untuk Periodic Leave</em>
</p>

#### Langkah-langkah — Pemilihan Karyawan

**2. Employee List**

Tabel karyawan yang memenuhi kriteria akan muncul dengan kolom:

- **Checkbox** — Centang untuk menyertakan karyawan dalam batch.
- **NIK**, **Employee Name**, **Position**, **Department**.
- **Start Date**, **End Date** — Tanggal leave period yang diusulkan berdasarkan roster.
- **Roster Note** — Catatan khusus dari data roster (jika ada).
- **Status** — Badge **Due** (hijau, sudah saatnya cuti) atau **Upcoming** (kuning, akan segera jatuh tempo).
- **Flight?** — Klik ikon pesawat untuk menambahkan data tiket penerbangan bagi karyawan bersangkutan.

Gunakan tombol **Select All** atau **Deselect All** untuk memilih/membatalkan semua sekaligus. Badge **X selected** di pojok kanan kartu memperlihatkan jumlah karyawan yang dipilih.

<p align="center" id="gambar-4-3">
    <img
        src="images/roster-periodic-create-employees.png"
        alt="Tabel Employee List dengan checkbox di tiap baris, kolom NIK, Employee Name, Position, Department, Start Date, End Date, Roster Note, Status (badge Due/Upcoming), dan kolom Flight"
        style="max-width: 90%; width: 90%; height: auto;"
    />
    <br><em>Gambar 4.3 — Daftar karyawan yang dapat disertakan dalam batch</em>
</p>

**Menambah data tiket penerbangan (opsional):**
Klik ikon pesawat di kolom **Flight?** pada baris karyawan. Modal **Add Flight Request** terbuka. Centang _Employee needs flight ticket reservation_, lalu isi segmen penerbangan (From, To, Date, Time, Airline). Tambah segmen lebih dari satu jika karyawan memerlukan penerbangan transit dengan klik **Add Flight Segment**. Klik **Save Flight Data** untuk menyimpan.

#### Langkah-langkah — Approval & Pengiriman

**3. Approval Preview**

Setelah karyawan dipilih, bagian **Approval Preview** memuat alur persetujuan per departemen secara otomatis. Pilih approver yang sesuai untuk setiap departemen yang terwakili dalam batch.

**4. Notes & Submit**

- **Periodic Leave Notes** _(opsional)_ — Isi catatan umum untuk seluruh batch ini.
- Klik tombol **Submit Leave Request (N Employees)** untuk mengirim semua pengajuan sekaligus. Nilai N menunjukkan jumlah karyawan yang dipilih.

<p align="center" id="gambar-4-4">
    <img
        src="images/roster-periodic-create-approval.png"
        alt="Panel Approval Preview di kiri menampilkan selector approver per departemen; panel Notes & Submit di kanan berisi textarea Periodic Leave Notes dan tombol Submit Leave Request"
        style="max-width: 90%; width: 90%; height: auto;"
    />
    <br><em>Gambar 4.4 — Approval Preview dan panel Notes & Submit</em>
</p>

**Catatan:** Tombol **Submit** hanya aktif jika setidaknya satu karyawan sudah dipilih dan approver sudah ditentukan untuk setiap departemen yang terlibat.

### 4.3 Detail Batch

Klik **Batch ID** pada tabel daftar, atau tombol **View**, untuk membuka halaman detail batch.

**Batch Information** (kolom kiri) menampilkan:

- **Batch ID** — Kode unik batch.
- **Total Requests** — Jumlah pengajuan dalam batch.
- **Created At** — Waktu pembuatan.
- **Created By** — Nama pengguna yang membuat batch.
- **Notes** — Catatan batch (jika diisi).

**Batch Actions** (kolom kanan, muncul jika ada request dengan status _Pending_) — Klik **Cancel All Pending Requests** untuk membatalkan semua request yang belum diproses sekaligus.

Di bawah informasi utama, pengajuan dalam batch dikelompokkan berdasarkan **Department**. Tiap tabel departemen memiliki kolom:

- **Register No.** — Nomor register pengajuan cuti.
- **NIK**, **Employee Name**, **Position**, **Project**.
- **Start Date**, **End Date**, **Days** — Rentang dan jumlah hari cuti.
- **Status** — Status masing-masing pengajuan.
- **Flight** — Informasi tiket (jika ada).
- **Action** — Tombol untuk melihat detail pengajuan individual.

<p align="center" id="gambar-4-5">
    <img
        src="images/roster-periodic-show.png"
        alt="Halaman detail batch Periodic Leave Request menampilkan Batch Information di kiri, Batch Actions di kanan, dan tabel request dikelompokkan per departemen di bawahnya"
        style="max-width: 90%; width: 90%; height: auto;"
    />
    <br><em>Gambar 4.5 — Halaman detail batch Periodic Leave Request</em>
</p>

---

## 5. Kesalahan & Bantuan

| Gejala / pesan                                                                          | Kemungkinan penyebab                                                                    | Apa yang bisa dicoba                                                                          |
| :-------------------------------------------------------------------------------------- | :-------------------------------------------------------------------------------------- | :-------------------------------------------------------------------------------------------- |
| Tombol **Create** tidak muncul di kolom Action                                          | Level karyawan belum memiliki konfigurasi roster                                        | Hubungi administrator untuk mengatur konfigurasi Level                                        |
| Tombol **Export** / **Calendar View** / **Create Periodic Leave** tidak aktif (abu-abu) | Proyek belum dipilih di filter                                                          | Pilih project terlebih dahulu di dropdown **Select Project**                                  |
| Setelah klik **Search Employees**, daftar karyawan kosong                               | Tidak ada karyawan yang jatuh tempo dalam rentang **Look Ahead Days**                   | Tambah nilai **Look Ahead Days** (misalnya 30 atau 60 hari) atau periksa data roster karyawan |
| Import gagal dengan pesan kesalahan per baris                                           | Kolom wajib (NIK, Cycle No, Work Start, Work End) tidak diisi atau format tanggal salah | Periksa file Excel; pastikan format tanggal sesuai (`YYYY-MM-DD`) dan kolom wajib terisi      |
| Tombol **Submit Leave Request** tidak aktif                                             | Belum ada karyawan yang dipilih atau approver belum ditentukan                          | Centang minimal satu karyawan dan pilih approver untuk setiap departemen                      |
| Halaman detail roster menampilkan **Work Days Difference** negatif                      | Siklus belum seimbang (lebih banyak cuti dari yang seharusnya)                          | Tambah **Adjusted Days** positif pada siklus berikutnya untuk menyeimbangkan                  |
| Status karyawan menampilkan **No Roster**                                               | Roster belum dibuat untuk karyawan tersebut                                             | Klik tombol **Create** pada baris karyawan di halaman Rosters                                 |

### Menghubungi administrator

Jika masalah tidak dapat diselesaikan secara mandiri, hubungi administrator sistem dengan menyertakan informasi berikut:

- **Username** yang digunakan saat masalah terjadi.
- **Waktu** terjadinya masalah (tanggal dan jam).
- **Menu yang dibuka** (misalnya: Roster Management → Rosters → Detail Roster #123).
- **NIK karyawan** atau **Batch ID** yang berkaitan (jika relevan).
- **Cuplikan pesan error** yang muncul di layar (jika ada).

</div>

---
