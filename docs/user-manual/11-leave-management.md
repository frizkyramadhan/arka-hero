# Leave Management

Panduan ini untuk **staf HR** yang mengoperasikan modul cuti/izin di ARKA HERO (**dashboard**, **entitlements**, **requests**, **reports**) dan untuk **karyawan** yang mengajukan lewat menu pribadi. Cakupan mencakup saldo cuti (**entitlements**), pengajuan berbasis formulir cuti/izin (termasuk **Long Service Leave** bila dipakai perusahaan), opsi **Flight Request**, pemilihan **Approver**, serta pembatalan (**cancellation**).

| **Istilah**                         | Arti singkat                                                                                                                                                  |
| ----------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **Leave Management**                | Grup menu di **HERO SECTION** untuk dashboard HR, daftar pengajuan, saldo cuti, dan laporan.                                                                  |
| **Leave Entitlement**               | Hak/saldo cuti per karyawan per periode dan jenis cuti; menjadi acuan saat mengajukan **Leave Request**.                                                      |
| **Leave Period**                    | Rentang periode saldo yang terisi otomatis dari **entitlements** saat memilih karyawan dan jenis cuti.                                                        |
| **Leave Request**                   | Pengajuan izin/cuti; memiliki **Register No.**, status alur persetujuan, dan dapat dihubungkan dengan **Flight Request**.                                     |
| **Approver Selection**              | Pemilihan satu atau lebih **approver** untuk menyetujui pengajuan sebelum **Save & Submit**.                                                                  |
| **Flight Request**                  | Bagian opsional pada formulir (centang **Check if you need flight ticket reservation**) untuk kebutuhan tiket; terhubung alur modul penerbangan jika dipakai. |
| **Close** / **Close Leave Request** | Penutupan pengajuan yang sudah **Approved** dan selesai masa cutinya agar alur administrasi tertutup.                                                         |
| **Cancellation Request**            | Pengajuan pembatalan sebagian/seluruh hari cuti lewat **Request Leave Cancellation** / **Cancellation Request Form**.                                         |
| **Master Data** → **Leave Types**   | Pengaturan jenis cuti & kategori (mis. berbayar/tidak); mendefinisikan apa yang bisa diajukan karyawan.                                                       |

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

4. Pada kartu **Quick Search Employee Entitlement**, pilih karyawan di dropdown (pencarian nama atau NIK), lalu klik **Search** untuk melihat ringkasan saldo tanpa membuka halaman **Entitlements** penuh.
5. Tinjau tabel **Open Leave Requests (Ready to Close)** — cuti yang sudah **Approved** dan layak ditutup; gunakan ikon lihat untuk detail atau tombol centang untuk membuka **Close Leave Request** (konfirmasi di jendela pop-up).
6. Tinjau **Pending Cancellation Requests** — pembatalan yang menunggu keputusan; gunakan tombol aksi hijau/merah sesuai izin untuk menyetujui atau menolak (dengan **Notes** di jendela **Action on Cancellation Request** bila diminta).
7. Periksa **Employees Without Entitlements** dan **Employees with Expiring Entitlements** untuk tindak lanjut saldo; gunakan **Action** untuk membuka profil saldo karyawan.
8. Pada **Paid Leave Without Supporting Documents**, pantau cuti berbayar tanpa lampiran yang wajib — sesuai kebijakan, unggah dokumen dari halaman detail pengajuan agar memenuhi ketentuan sebelum batas konversi otomatis.

**Catatan:** Tombol **Close**, persetujuan pembatalan, atau **Clear Entitlements** hanya tampil jika **hak akses** peran Anda mengizinkan (beberapa aksi terbatas untuk **administrator**).

---

## 2. Untuk HR — **Entitlements** (saldo & **Generate** / **Export** / **Import**)

**Leave entitlement** adalah dasar agar **Leave Period** dan sisa hari pada formulir pengajuan konsisten: tanpa saldo periode yang valid, karyawan tidak dapat mengambil cuti sesuai jenis yang dipilih.

### Langkah-langkah — **Project Filter & Generate Entitlements**

1. **Login** ke ARKA HERO.
2. **HERO SECTION** → **Leave Management** → **Entitlements**. Judul halaman: **Leave Entitlement Management**.
3. Pada kartu **Project Filter & Generate Entitlements**, pilih **Select Project** (kode dan nama proyek), lalu klik **Load Employees** untuk memuat daftar. **Clear** mengosongkan filter.
4. Gunakan **Export** untuk mengunduh data template/ekspor (sesuai implementasi; biasanya memuat parameter proyek yang dipilih).
5. Klik **Import** untuk membuka **Import Leave Entitlements**, pilih berkas **Select Excel File** (format **.xlsx** atau **.xls**, ukuran sesuai batas di layar), lalu **Import**. Jika ada baris gagal, sistem menampilkan tabel **Import Validation Errors** dengan Sheet, Row, Column, Value, dan pesan perbaikan.

6. Setelah karyawan termuat, pada judul kartu **Employee Remaining Leave Entitlements** (disertai kode proyek terpilih), gunakan **Generate Entitlements** bila tombol tersedia untuk membuat/memenahkan saldo per kebijakan perusahaan (biasanya dengan konfirmasi). **Clear Entitlements** (jika tampil) menghapus data saldo sesuai lingkup yang diizinkan dan biasanya hanya untuk peran **administrator**.

7. Dari kolom **Actions**, buka detail karyawan untuk melihat atau mengelola periode saldo (sesuai izin sunting).

**Catatan:** Jenis kolom saldo di tabel mengikuti konfigurasi perusahaan (mis. label seperti **Cuti Tahunan**, **Sakit**, **Ijin Tanpa Upah**, **Cuti Panjang** pada tampilan tertentu). **Leave Types** di **Master Data** harus selaras dengan kebijakan SDM.

---

## 3. Untuk HR — **Requests** (buat pengajuan, **Flight Request**, **Approver Selection**, pembatalan)

### Langkah-langkah — daftar **Leave Requests**

1. **HERO SECTION** → **Leave Management** → **Requests**. Judul halaman: **Leave Requests**.
2. Buka panel **Filter** untuk menyaring **Status**, **Employee**, **Leave Type**, **Start Date**, **End Date**; **Reset** mengembalikan filter.
3. Tabel menampilkan kolom **No**, **Register No.**, **Employee**, **Project**, **Leave Type**, **Start Date**, **End Date**, **Total Days**, **Status**, **Requested At**, **Actions**.
4. **Add** membuka **Create Leave Request**. **Add Periodic Leave** mengarah ke pengajuan massal/jadwal (modul **Periodic Leave Requests** di **Roster Management**) bila organisasi memakainya.

### Langkah-langkah — **Create Leave Request** (_Leave Request Form_)

1. Dari daftar, klik **Add**.
2. Isi **Project** dan **Employee** (karyawan aktif di proyek). **Employee** mengikuti **Project** yang dipilih.
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

- **Leave Request Monitoring** — pemantauan status, filter rentang tanggal, proyek/karyawan, ekspor Excel (**Export Excel** / **Filter** sesuai halaman).
    - **Leave Cancellation Report** — pembatalan cuti dan dampak ke saldo.
    - **Leave Entitlement Detailed** — rincian saldo, deposit/withdraw, utilisasi.
    - **Auto Conversion Tracking** — cuti berbayar tanpa dokumen pendukung mendekati tenggat konversi otomatis ke tidak berbayar.
    - **Leave by Project Report** — pola penggunaan cuti per proyek/dimensi tim.

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

| Gejala / pesan (contoh)                                     | Kemungkinan penyebab                                                 | Apa yang bisa dicoba                                                                                  |
| ----------------------------------------------------------- | -------------------------------------------------------------------- | ----------------------------------------------------------------------------------------------------- |
| **Leave Period** kosong / **Leave Type** tidak bisa dipilih | Belum ada **entitlement** aktif untuk karyawan & jenis cuti tersebut | Pastikan HR sudah **Generate** / impor saldo di **Entitlements** untuk proyek dan periode yang benar. |
| **Import Validation Errors** setelah **Import**             | Format Excel atau nilai sel tidak sesuai template/kolom wajib        | Perbaiki baris yang disebutkan di tabel error, unggah ulang berkas.                                   |
| Tidak bisa **Save & Submit** / pesan approver               | **Approver Selection** belum memenuhi jumlah/aturan                  | Tambahkan approver valid lewat pencarian sampai persyaratan terpenuhi.                                |
| **Pending** terus tanpa keputusan                           | Approver belum bertindak atau notifikasi tidak terbaca               | Hubungi approver terkait lewat saluran resmi perusahaan.                                              |
| Cuti berbayar terdeteksi tanpa dokumen                      | Lampiran belum diunggah                                              | Unggah dokumen di detail pengajuan sebelum tenggat **Auto Conversion** (lihat laporan pemantauan).    |
| Menu **Leave Management** / tombol HR tidak ada             | Peran akun tidak mencakup fitur HR                                   | Minta **administrator** menyesuaikan peran/izin.                                                      |

### Menghubungi administrator

Hubungi **administrator** (atau **IT** / **HR**) jika menu seharusnya ada tetapi hilang, status tidak berubah setelah tindakan yang wajar, atau Anda membutuhkan koreksi **Master Data** (**Leave Types**, **Projects**, data karyawan).

**Jangan** mengirim **password** lewat obrolan atau surel. Sampaikan **username**, **Register No.** pengajuan bila relevan, waktu kejadian, dan cuplikan pesan di layar.

---
