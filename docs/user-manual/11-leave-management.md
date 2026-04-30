# Leave Management

Panduan ini untuk **staf HR** yang mengoperasikan modul cuti/izin di ARKA HERO (**dashboard**, **entitlements**, **requests**, **reports**) dan untuk **karyawan** yang mengajukan lewat menu pribadi. Fitur ini mengacu pada form cuti / Formulir Izin Meninggalkan Pekerjaan dan Formulir Cuti Panjang, sehingga semua aktivitas yang menggunakan form tersebut akan diakomodir oleh fitur ini.

| **Istilah**                       | Arti singkat                                                                                                                                                  |
| --------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Leave Management**              | Grup menu di **HERO SECTION** untuk dashboard HR, daftar pengajuan, saldo cuti, dan laporan.                                                                  |
| **Leave Entitlement**             | Hak/saldo cuti per karyawan per periode dan jenis cuti; menjadi acuan saat mengajukan **Leave Request**.                                                      |
| **Leave Period**                  | Rentang periode saldo yang terisi otomatis dari **entitlements** saat memilih karyawan dan jenis cuti.                                                        |
| **Leave Request**                 | Pengajuan izin/cuti; memiliki **Register No.**, status alur persetujuan, dan dapat dihubungkan dengan **Flight Request**.                                     |
| **Approver Selection**            | Pemilihan satu atau lebih **approver** untuk menyetujui pengajuan sebelum **Save & Submit**.                                                                  |
| **Flight Request**                | Bagian opsional pada formulir (centang **Check if you need flight ticket reservation**) untuk kebutuhan tiket; terhubung alur modul penerbangan jika dipakai. |
| **Close Leave Request**           | Penutupan pengajuan yang sudah **Approved** dan selesai masa cutinya agar alur administrasi tertutup.                                                         |
| **Cancellation Request**          | Pengajuan pembatalan sebagian/seluruh hari cuti lewat **Request Leave Cancellation** / **Cancellation Request Form**.                                         |
| **Master Data** → **Leave Types** | Pengaturan jenis cuti & kategori (mis. berbayar/tidak); mendefinisikan apa yang bisa diajukan karyawan.                                                       |

---

## 1. Untuk HR — **Leave Management Dashboard**

### Langkah-langkah — membuka **Leave Management Dashboard** (_Leave Analytics and Management Overview_)

1. **Login** ke ARKA HERO.
2. Di sidebar, grup **HERO SECTION** → **Leave Management** → **Dashboard**.
3. Baca ringkasan kartu statistik:

- **Total Requests** — jumlah pengajuan cuti sepanjang waktu (**All time requests**).
    - **Approved** — pengajuan dengan status disetujui dan persentase terhadap total.
    - **Pending** — menunggu persetujuan (**Awaiting approval**).
    - **This Month** — pengajuan pada bulan berjalan (dengan indikator pertumbuhan vs bulan lalu jika tersedia).

4. Pada kartu **Quick Search Employee Entitlement**, ketik nama atau NIK di kotak pencarian (**Search by employee name or NIK...**), lalu klik **Search** untuk melihat ringkasan saldo; tombol **Reset** mengosongkan isian.
5. Tinjau tabel **Open Leave Requests (Ready to Close)** — cuti yang sudah **Approved** dan layak ditutup; gunakan ikon lihat untuk detail atau tombol centang untuk membuka **Close Leave Request** (konfirmasi di jendela pop-up).
6. Tinjau **Pending Cancellation Requests** — pembatalan yang menunggu keputusan; gunakan tombol aksi hijau/merah sesuai izin untuk menyetujui atau menolak (dengan **Notes** di jendela **Action on Cancellation Request** bila diminta).
7. Periksa **Employees Without Entitlements** dan **Employees with Expiring Entitlements** untuk tindak lanjut saldo; gunakan **Action** untuk membuka profil saldo karyawan.
8. Pada **Paid Leave Without Supporting Documents**, pantau cuti berbayar tanpa lampiran yang wajib — sesuai kebijakan, unggah dokumen dari halaman detail pengajuan agar memenuhi ketentuan sebelum batas konversi otomatis.

<p align="center" id="leave-management-dashboard">
    <img
        src="images/leave_management_dashboard.png"
        alt="Leave Management Dashboard: judul dan breadcrumb Leave Analytics and Management Overview; kartu Total Requests, Approved, Pending, This Month; Quick Search Employee Entitlement (Search, Reset); grid tabel Open Leave Requests, Pending Cancellation Requests, Employees Without Entitlements, Employees with Expiring Entitlements; tabel Paid Leave Without Supporting Documents"
        style="max-width: 74%; width: 74%; height: auto;"
    />
</p>

**Catatan:** Tombol **Close**, persetujuan pembatalan, atau **Clear Entitlements** hanya tampil jika **hak akses** peran Anda mengizinkan (beberapa aksi terbatas untuk **administrator**).

---

## 2. Untuk HR — **Leave Entitlements**

**Leave entitlement** adalah kumpulan hak cuti per karyawan yang terdiri dari berbagai **leave type** (jenis cuti) beserta jumlah hari per periode. Data ini menjadi dasar **Leave Period** dan sisa hari pada formulir **Leave Request**; tanpa entitlement untuk periode yang berlaku, karyawan tidak dapat mengambil cuti sesuai jenis yang dipilih.

### Setup awal master data dan pola **Generate** / **Export** / **Import**

Sebelum tim rutin hanya menjalankan **Generate Entitlements**, pastikan fondasi berikut sudah konsisten dengan kebijakan perusahaan:

| Tahap                     | Uraian                                                                                                                                                                                                                                                                           |
| :------------------------ | :------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Master di sistem**      | **Leave Types** (kategori paid/unpaid, nama kolom di layar), **Projects**, serta penempatan karyawan (**Employee Management**) untuk administration aktif — keliru di master akan ikut terbawa ke saldo.                                                                         |
| **Export**                | Di halaman **Entitlements**, pilih **project**, **Load Employees**, lalu **Export**. Berguna untuk: mendapatkan **template** struktur kolom + opsional **data saldo** (`include_data`) sebagai baseline; dokumentasi; atau penyelerasian dengan spreadsheet HR lain **offline**. |
| **Import**                | Dipakai saat **setup awal** atau **koreksi massal** dalam bentuk Excel (format `.xlsx`/`.xls` sesuai batas aplikasi). Isi berkas mengikuti hasil/kolom dari ekspor; setelah unggah, perbaiki entri yang gagal lewat tabel **Import Validation Errors** lalu unggah ulang.        |
| **Generate Entitlements** | Menjalankan pengisian saldo cuti **secara otomatis dan massal** untuk **semua karyawan aktif** pada **project** yang Anda pilih — Anda tidak perlu mengisi tiap orang secara manual.                                                                                             |

### 2.1 Halaman **Leave Entitlement Management** — secara massal

Di halaman ini proses yang akan dilakukan adalah mengisi hak/saldo cuti dengan **Generate**, mengolah spreadsheet dengan **Export** dan **Import**, serta melihat table **Leave Entitlement** per project.

Untuk **setup awal**, urutan yang disarankan: **Load Employees** → **Generate Entitlements** (untuk men-generate data entitlement ter-update) → **Export** → edit berkas Excel → **Import** kembali.

Setelah operasi rutin, biasanya cukup menjalankan **Generate Entitlements** **secara berkala** misal di saat akhir bulan; **Export**/**Import** dipakai jika perlu rekonsiliasi, audit, atau koreksi massal — tidak sebagai aktivitas mingguan wajib.

1. **Login** ke ARKA HERO → sidebar **HERO SECTION** → **Leave Management** → **Entitlements**. Judul halaman: **Leave Entitlement Management**.

2. Pada kartu **Project Filter & Generate Entitlements**, pilih **Select Project**, lalu **Load Employees**. Gunakan **Clear** jika ingin mengosongkan pilihan project.

3. **Generate Entitlements** — Klik tombol **Generate Entitlements** (jika tersedia), konfirmasi sesuai dialog sistem, lalu tunggu hingga selesai. Baca ulang jendela informasi berikut bila perlu agar selaras dengan kebijakan HR.

<p align="center" id="leave-entitlements-filter">
    <img
        src="images/leave_entitlements_project_filter.png"
        alt="Leave Entitlement Management: Select Project, Load Employees, Clear, Export, Import"
        style="max-width: 90%; width: 90%; height: auto;"
    />
</p>

**Penjelasan singkat — jendela informasi _Generate Entitlements for [kode project]_**

Jendela ini muncul saat Anda akan atau baru saja menjalankan **Generate Entitlements**. Judulnya menyebut **kode project** (contoh: **Generate Entitlements for 000H**). Yang biasanya dijelaskan di layar:

- **Siapa yang dapat saldo** — **Semua karyawan aktif** pada **project** tersebut (administration **aktif**).
- **Jenis cuti yang dibuatkan** — kombinasi **Paid Leave**, **Unpaid Leave**, **Annual Leave** (sering setelah masa kerja tertentu), **LSL** (ambang masa kerja mengikuti aturan sistem) — detail pasti ada di teks jendela.
- **Periode berlaku** — mengacu pada **DOH** di **Employee Management** dan **tahun/periode berjalan** (misalnya **Current Year**).
- **Anti-duplikat** — untuk **periode yang sama**, entitlement yang **sudah ada** akan **dilewati** sehingga tidak terjadi penggandaan data.
- **Service Start DOH** (cara menghitung masa kerja untuk keperluan aturan cuti):
    - Penghentian **End of Contract** → masa kerja dapat **diteruskan** dari **DOH pertama** (misalnya rehire dalam satu rantai kontrak).
    - Alasan berhenti **selain** _End of Contract_ → masa kerja **dihitung ulang** dari **DOH penempatan baru**.
    - Karyawan dengan **beberapa NIK** dan riwayat **End of Contract** → perhitungan dapat mengacu ke **DOH paling awal** (sesuai penjelasan di layar).

<p align="center" id="leave-entitlements-generate-info">
    <img
        src="images/leave_entitlements_generate_info.png"
        alt="Jendela Generate Entitlements for project: ringkasan jenis cuti, periode tahun berjalan, catatan anti-duplikat, dan penjelasan Service Start DOH untuk End of Contract vs lainnya"
        style="max-width: 50%; width: 50%; height: auto;"
    />
</p>

4. Setelah data termuat, tampil kartu **Employee Remaining Leave Entitlements**: ringkasan saldo per karyawan dan kolom **Actions**. Nama kolom jenis cuti mengikuti **Leave Types** (misalnya **Cuti Tahunan**, **Sakit**, **Ijin Tanpa Upah**, **Cuti Panjang**) yang dikonfigurasi di **Master Data** → **Leave Management Data** → **Leave Types**.

5. **Export** — unduh berkas Excel (template beserta data, sesuai parameter **project**). Berikut **contoh** bentuk berkas yang akan Anda edit di aplikasi spreadsheet luar (nama kolom, warna header, dan jumlah kolom dapat sedikit berbeda mengikuti **Leave Types** serta **project** Anda):

6. Edit nilai di Excel sesuai kebutuhan — **pertahankan struktur kolom dan format template** dari hasil **Export** agar **Import** dapat memprosesnya.

<p align="center" id="leave-entitlements-export-template-excel">
    <img
        src="images/leave_entitlements_export_template_excel.png"
        alt="Contoh template Excel entitlement: kolom identitas (Nama, NIK, Position, DOH, Project, Start Period, End Period dengan format tanggal DD-MM-YYYY); kolom jenis cuti berwarna (misalnya Cuti Tahunan, Karyawan sendiri kawin, Sakit, Izin Tanpa Upah, Cuti Panjang - Staff)"
        style="max-width: 100%; width: 100%; height: auto;"
    />
</p>

7. **Import:** dari kartu filter yang sama, klik **Import** untuk membuka **Import Leave Entitlements**, pilih **Select Excel File**, lalu **Import**. Jika ada kesalahan, perbaiki berdasarkan **Import Validation Errors**, lalu unggah ulang.

**Import Validation Errors — pesan yang umum muncul**

Tabel di layar menampilkan **Sheet**, **Row**, **Column** (sering berisi referensi **NIK**), **Value**, dan **Error Message**. Baris yang lolos tetap tersimpan; baris gagal harus diperbaiki di Excel lalu **Import** ulang.

| Jenis        | Contoh pesan di layar (bahasa Inggris dari sistem)              | Yang harus dicek                                                                                                                   |
| :----------- | :-------------------------------------------------------------- | :--------------------------------------------------------------------------------------------------------------------------------- |
| Berkas       | _Please select a file to import._                               | Pilih berkas sebelum mengunggah.                                                                                                   |
| Berkas       | _The file must be a file of type: xlsx, xls._                   | Hanya **.xlsx** atau **.xls**.                                                                                                     |
| Berkas       | _The file may not be greater than 10MB._                        | Perkecil atau bagi data; batas **10 MB**.                                                                                          |
| Baris data   | _NIK is required_                                               | Isi kolom **NIK** pada baris tersebut.                                                                                             |
| Baris data   | _NIK '…' not found or not active_                               | Pastikan **NIK** sama dengan karyawan yang administration-nya **aktif** di sistem.                                                 |
| Baris data   | _Start Period and End Period are required_                      | Isi **Start Period** dan **End Period** (nama kolom bisa mengikuti template **Export**).                                           |
| Baris data   | _Invalid date format. Use YYYY-MM-DD or Excel date format_      | Format tanggal tidak dikenali — gunakan format tanggal standar atau format tanggal Excel (angka serial) seperti dari **Export**.   |
| Baris data   | _Start Period must be before End Period_                        | Tanggal mulai harus **lebih awal** dari tanggal akhir periode.                                                                     |
| Baris data   | _Deposit Days cannot be negative_                               | Kolom **Deposit Days** tidak boleh bernilai negatif.                                                                               |
| Gagal sistem | Pesan _System error_, _Import Failed_, atau teks kesalahan umum | Coba lagi; jika berulang, hubungi **administrator** dengan ringkasan waktu impor dan cuplikan pesan (tanpa mengirim **password**). |

**Catatan:**

- Untuk kombinasi **Cuti Panjang Staff / Non Staff** yang tidak sesuai level jabatan, sistem biasanya **melewati** kolom yang tidak relevan **tanpa** menampilkan error baris untuk hal itu.
- Nilai non-angka pada kolom jenis cuti sering dianggap **nol**, bukan selalu sebagai error baris.

---

### 2.2 Halaman **Leave Entitlement** — per karyawan (**Employee Leave Entitlements**)

1. Di halaman Leave Entitlements (setelah **Load Employees**), di kolom **Actions**, klik ikon **View** (tombol biru dengan ikon mata) pada baris yang ingin Anda tinjau.
2. Pada kartu **Employee Information**, baca **NIK**, **Name**, **Project**, **Level**, **Position**, **DOH**, **Years of Service**, **Status**, **Staff Type**, dan tombol **Back to List** untuk kembali ke daftar project.
3. Jika karyawan sudah punya entitlement, pada kartu **Leave Entitlements Summary** terdapat tombol **Add Entitlements** dan daftar **Available Periods** (tiap baris = satu rentang **period_start**–**period_end**, dengan **Edit** / **Delete** per periode). Klik periode untuk melihat rincian di **Entitlement Details** di sebelah kanan — tabel per kategori (misalnya **Annual Leave**, **Paid Leave**, **Unpaid Leave**, **LSL**) dengan kolom **Leave Type**, **Entitled**, **Taken**, **Remaining**, dan **Actions** (termasuk tautan **Calculation** per jenis cuti jika tersedia).

<p align="center" id="leave-entitlements-employee-detail">
    <img
        src="images/leave_entitlements_employee_detail.png"
        alt="Halaman Employee Leave Entitlements: kartu Employee Information (NIK, nama, project, level, jabatan, DOH, masa kerja, status, staff type, Back to List) dan Leave Entitlements Summary (Add Entitlements, sidebar Available Periods dengan periode terpilih, Entitlement Details berupa tabel saldo per jenis cuti)"
        style="max-width: 90%; width: 90%; height: auto;"
    />
</p>
---

### 2.3 Tambah entitlement — **Add Entitlements** (**Create Entitlements**)

1. Di halaman **Employee Leave Entitlements**, klik **Add Entitlements** (tombol biru dengan ikon **+** di pojok kartu **Leave Entitlements Summary**).
2. Sistem membuka formulir **Add Employee Leave Entitlements** / **Edit Employee Leave Entitlements** dengan banner nama karyawan, periode aktif (tahun/periode mengikuti aturan bisnis & DOH), dan kolom **Employee Info** di sisi kiri (Level, Position, DOH, Service, **Staff Type**, dll.).
3. Bagian utama berupa tab berdasarkan kategori cuti (**Paid Leave**, **Periodic Leave**, **LSL**, **Unpaid Leave** — sesuai yang ada di sistem). Pada tiap tab, tabel memuat kolom **Leave Type**, **Default**, **Entitlement (Days)**, dan **Used**.
4. Sesuaikan angka di **Entitlement (Days)** per jenis cuti (input angka; satuan **days**). Nilai default mengikuti perhitungan sistem; Anda dapat mengubahnya sesuai kebijakan HR.
5. Klik **Create Entitlements** untuk menyimpan saldo periode baru. Gunakan **Back to Summary** jika ingin kembali tanpa menyimpan.

<p align="center" id="leave-entitlements-add-form">
    <img
        src="images/leave_entitlements_add_form.png"
        alt="Form Add Employee Leave Entitlements: banner periode, Employee Info, tab Paid/Unpaid leave, kolom Entitlement Days dan tombol Create Entitlements"
        style="max-width: 90%; width: 90%; height: auto;"
    />
</p>

**Catatan:** Mode tambah memakai periode yang dihitung untuk tahun/periode berjalan sesuai logika project (bukan mengedit periode lama). Jika saldo untuk periode itu sudah ada, ikuti prosedur edit di bagian berikut atau sesuaikan dengan HR lead.

---

### 2.4 Ubah entitlement per periode — **Edit** (**Save Changes**)

1. Di halaman **Employee Leave Entitlements**, pada **Available Periods**, pastikan periode yang ingin diubah dipilih (baris ter-highlight).
2. Pada baris periode aktif, klik **Edit** (tombol terang dengan ikon pensil).
3. Form sama seperti tambah, tetapi judul dapat menampilkan mode edit; kolom **Used** menampilkan pemakaian (**taken**) jika sudah ada pengajuan cuti. Sesuaikan **Entitlement (Days)** per **Leave Type**.
4. Klik **Save Changes** untuk menyimpan. **Back to Summary** membatalkan navigasi kembali ke ringkasan.

<p align="center" id="leave-entitlements-edit-form">
    <img
        src="images/leave_entitlements_edit_form.png"
        alt="Form edit entitlement per periode: tab jenis cuti, kolom Used dan Entitlement Days, tombol Save Changes"
        style="max-width: 90%; width: 90%; height: auto;"
    />
</p>

**Catatan:** Menurunkan hari di bawah yang sudah terpakai dapat dibatasi oleh aturan sistem — ikuti pesan validasi di layar.

---

### 2.5 Hapus entitlement untuk satu periode — **Delete** (**Delete Entitlements**)

1. Di **Employee Leave Entitlements**, pilih periode yang ingin dihapus di **Available Periods**.
2. Klik **Delete** (tombol merah dengan ikon tempat sampah) pada baris periode yang aktif.
3. Jendela konfirmasi **Delete Entitlements** menampilkan peringatan bahwa aksi tidak dapat dibatalkan dan meminta mengetik **`DELETE`** pada kotak konfirmasi sebelum **Delete All Entitlements**. **Cancel** menutup jendela tanpa menghapus.

<p align="center" id="leave-entitlements-delete-confirm">
    <img
        src="images/leave_entitlements_delete_confirm.png"
        alt="Dialog Delete Entitlements: peringatan permanen, konfirmasi ketik DELETE, tombol Delete All Entitlements dan Cancel"
        style="max-width: 75%; width: 75%; height: auto;"
    />
</p>

**Catatan:** Penghapusan menghapus **seluruh** entitlement untuk **periode** tersebut (semua jenis cuti dalam periode itu). Pastikan tidak ada kebutuhan audit atau pelaporan yang masih bergantung pada data tersebut. Tombol **Generate Entitlements**, **Import**, **Edit**, dan **Delete** mengikuti **hak akses** (**leave-entitlements.create** / **edit** / **delete**, dll.).

---

## 3. Untuk HR — **Requests** (buat pengajuan, **Flight Request**, **Approver Selection**, pembatalan)

### Langkah-langkah — daftar **Leave Requests**

1. **HERO SECTION** → **Leave Management** → **Requests**. Judul halaman: **Leave Requests**.
2. Buka panel **Filter** untuk menyaring **Status**, **Employee**, **Leave Type**, **Start Date**, **End Date**; **Reset** mengembalikan filter.
3. Tabel menampilkan kolom **No**, **Register No.**, **Employee**, **Project**, **Leave Type**, **Start Date**, **End Date**, **Total Days**, **Status**, **Requested At**, **Actions**.
4. **Add** membuka **Create Leave Request**. **Add Periodic Leave** mengarah ke pengajuan massal/jadwal (modul **Periodic Leave Requests** di **Roster Management**) bila organisasi memakainya.

### Langkah-langkah — **Create Leave Request** (_Leave Request Form_)

1. Dari daftar, klik **Add**.
2. Isi **Project** dan **Employee** (karyawan aktif di project). **Employee** mengikuti **Project** yang dipilih.
3. Pilih **Leave Type**; **Leave Period** terisi otomatis dari **entitlements**. Sesuaikan **Leave Date** (rentang) sesuai kebutuhan. Untuk jenis cuti tertentu (mis. **Long Service Leave**), isian seperti **Leave Days**, **Cash Out**, **Total Days** dapat tampil sesuai aturan di layar.
4. Lampirkan dokumen pendukung jika kolom **Supporting Document** atau unggahan sejenis wajib untuk cuti berbayar — ikuti validasi di formulir.
5. Pada kolom kanan, bagian **Flight Request**: centang **Check if you need flight ticket reservation** bila perlu tiket; isi segmen **Flight 1** (**From** / **To**, tanggal, maskapai, waktu), tambahkan segmen jika ada **Return** atau penerbangan lanjutan (**Add** segmen sesuai tombol di UI).
6. Pada kartu **Approver Selection**, cari dan tambahkan minimal satu **approver** yang valid (pencarian nama/email); ikuti teks bantuan di kartu untuk aturan minimal.

7. Panel **Leave Balance** menampilkan ringkasan saldo setelah karyawan dipilih — gunakan untuk cek cepat sebelum kirim.
8. Klik **Save & Submit** untuk mengirim ke alur persetujuan, atau **Cancel** kembali ke daftar.

### Setelah pengajuan tersimpan — detail, persetujuan, tutup, dokumen

1. Buka baris lewat **Actions** di daftar untuk masuk ke halaman detail (ikon sesuai tampilan).
2. Pembuat dan **approver** mengikuti alur **approval** standar (status **Pending**, **Approved**, **Rejected**, dll.).
3. Untuk pengajuan **Approved** yang sudah selesai masa cutinya, HR dapat **Close** lewat alur di dashboard atau detail agar status administrasi **Closed** (melalui konfirmasi **Close Leave Request**).
4. **Request Leave Cancellation** (judul **Request Leave Cancellation** / form **Cancellation Request Form**) digunakan jika karyawan atau HR perlu membatalkan sebagian/seluruh hari — kirim form, lalu untuk HR pantau di **Pending Cancellation Requests** pada dashboard.

**Catatan:** Pengajuan dari jalur HR dapat mencakup **Flight Request** yang terhubung dengan proses tiket di **Flight Management** sesuai kebijakan HO/kantor pusat.

---

## 4. Untuk HR — **Reports**

### Langkah-langkah — ringkasan laporan

1. **HERO SECTION** → **Leave Management** → **Reports**.
2. Pilih kartu laporan yang sesuai, lalu klik **View Report**:

- **Leave Request Monitoring** — pemantauan status, filter rentang tanggal, project/karyawan, ekspor Excel (**Export Excel** / **Filter** sesuai halaman).
    - **Leave Cancellation Report** — pembatalan cuti dan dampak ke saldo.
    - **Leave Entitlement Detailed** — rincian saldo, deposit/withdraw, utilisasi.
    - **Auto Conversion Tracking** — cuti berbayar tanpa dokumen pendukung mendekati tenggat konversi otomatis ke tidak berbayar.
    - **Leave by Project Report** — pola penggunaan cuti per project/dimensi tim.

1. Di halaman laporan (mis. pemantauan), gunakan **Filter Options**, lalu **Filter** untuk memuat data; **Export Excel** bila tersedia.

**Catatan:** Isi filter harus cukup spesifik agar tabel tidak tampak kosong secara keliru — cocokkan rentang tanggal dan status dengan data yang ada.

---

## 5. Karyawan (non–HR) — **My Leave Request**

Bagi karyawan yang tidak memakai menu HR di atas, pengajuan pribadi lewat **My Features** (bukan **Leave Management** di **HERO SECTION**).

### Langkah-langkah — daftar & pengajuan baru

1. **Login** ke ARKA HERO.
2. Sidebar: **My Features** → **My Leave Request** (satu pintu untuk daftar pengajuan dan akses terkait saldo pribadi jika diizinkan).
3. Gunakan filter/daftar untuk melihat status; klik **Add** / buat baru (sesuai tombol di layar) untuk membuka **Create Leave Request** dengan karyawan yang sudah tetap diri Anda sendiri.
4. Isi **Leave Request Form** seperti pada bagian 3 (tanpa memilih karyawan lain): **Leave Type**, **Leave Date**, **Flight Request** opsional, **Approver Selection**, lalu **Save & Submit**.
5. Untuk pembatalan, buka detail pengajuan lalu ikuti alur **Request Leave Cancellation** menuju **Cancellation Request Form**.

**Catatan:** Jika menu **My Leave Request** tidak tampil, akun Anda mungkin tidak memiliki izin pengajuan pribadi — hubungi **administrator**.

---

## 6. Kesalahan & bantuan

| Gejala / pesan (contoh)                                     | Kemungkinan penyebab                                                                                                                       | Apa yang bisa dicoba                                                                                                                                                      |
| ----------------------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------ | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Leave Period** kosong / **Leave Type** tidak bisa dipilih | Belum ada **entitlement** aktif untuk karyawan & jenis cuti tersebut                                                                       | Pastikan HR sudah **Generate** / impor saldo di **Entitlements** untuk project dan periode yang benar.                                                                    |
| **Import Validation Errors** setelah **Import**             | Format berkas (tipe/ukuran), **NIK** tidak aktif, **Start**/**End Period** kosong atau tanggal tidak valid, **Deposit Days** negatif, dll. | Rincian pesan umum ada pada **bagian 2.1** (_Import Validation Errors — pesan yang umum muncul_); perbaiki baris di Excel mengikuti **Error Message**, lalu unggah ulang. |
| Tidak bisa **Save & Submit** / pesan approver               | **Approver Selection** belum memenuhi jumlah/aturan                                                                                        | Tambahkan approver valid lewat pencarian sampai persyaratan terpenuhi.                                                                                                    |
| **Pending** terus tanpa keputusan                           | Approver belum bertindak atau notifikasi tidak terbaca                                                                                     | Hubungi approver terkait lewat saluran resmi perusahaan.                                                                                                                  |
| Cuti berbayar terdeteksi tanpa dokumen                      | Lampiran belum diunggah                                                                                                                    | Unggah dokumen di detail pengajuan sebelum tenggat **Auto Conversion** (lihat laporan pemantauan).                                                                        |
| Menu **Leave Management** / tombol HR tidak ada             | Peran akun tidak mencakup fitur HR                                                                                                         | Minta **administrator** menyesuaikan peran/izin.                                                                                                                          |

### Menghubungi administrator

Hubungi **administrator** (atau **IT** / **HR**) jika menu seharusnya ada tetapi hilang, status tidak berubah setelah tindakan yang wajar, atau Anda membutuhkan koreksi **Master Data** (**Leave Types**, **Projects**, data karyawan).

**Jangan** mengirim **password** lewat obrolan atau surel. Sampaikan **username**, **Register No.** pengajuan bila relevan, waktu kejadian, dan cuplikan pesan di layar.

---
