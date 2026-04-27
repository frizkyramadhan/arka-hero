<div style="text-align: justify; text-justify: inter-word;">

# Official Travel Management (LOT)

Panduan ini menjelaskan **Letter of Travel (LOT)** / perjalanan dinas resmi di ARKA HERO untuk **staf HR** yang mengelola data LOT (dashboard, daftar permintaan, alur kedatangan–keberangkatan, pelaporan) dan untuk **karyawan selain HR** yang mengajukan lewat menu pribadi/personal.

| **Istilah**                                                | Arti singkat                                                                                                      |
| :--------------------------------------------------------- | :---------------------------------------------------------------------------------------------------------------- |
| **LOT**                                                    | _Letter of Travel_ — surat/tiket perjalanan dinas; nomor **LOT Number** mengacu pada dokumen ini.                 |
| **Official Travels**                                       | Judul halaman daftar permintaan LOT (HR).                                                                         |
| **Letter Number**                                          | Pemilihan nomor surat dari sistem surat (kategori terkait) sebelum LOT terbit.                                    |
| **Travel Stops Timeline**                                  | Riwayat **Stop** / **Checkpoint** perjalanan: tiap stop dapat punya **Arrival** dan **Departure**.                |
| **Approver Selection**                                     | Pemilihan satu atau lebih **approver** yang menyetujui pengajuan.                                                 |
| **Flight Request**                                         | Bagian opsional untuk kebutuhan tiket pesawat (terhubung modul penerbangan jika dipakai).                         |
| **Master Data** → **Transportations** / **Accommodations** | Data referensi untuk pilihan **Transportation** dan **Accommodation** pada formulir LOT (di **GENERAL SECTION**). |

---

## 1. Untuk HR — Dashboard LOT

### Langkah-langkah — membuka **Official Travel Dashboard** (_Official Travel Dashboard_)

1. **Login** ke ARKA HERO.
2. Di sidebar, buka grup **Official Travel Management**, lalu klik **Dashboard**.
3. Baca ringkasan di layar (masing-masing memberi gambaran cepat tentang keadaan LOT):
    - **Total Travels** — jumlah seluruh LOT dari awal pencatatan (acuan “semua waktu”).
    - **Active Travels** — LOT yang statusnya masih berjalan / belum ditutup (secara umum perjalanan “masih aktif” menurut definisi sistem).
    - **Pending Arrivals** — banyaknya LOT yang sudah disetujui tetapi belum ada pencatatan **stempel kedatangan** (perlu tindakan **Arrival Check**).
    - **This Month** — jumlah LOT yang tercatat pada bulan berjalan (berguna melihat aktivitas bulan ini).
    - **Travel Status Overview** — kotak hitung per status alur: **Draft** (masih draf), **Submitted** (sudah diajukan), **Approved** (disetujui), **Rejected** (ditolak); angka membantu melihat kemacetan di tiap tahap.
    - Tabel **Open Official Travels** — daftar LOT yang statusnya “terbuka” (umumnya belum **Closed**), dengan kolom **Travel Number**, **Traveler**, **Destination**, **Date**, **Status**, **Action** untuk membuka detail atau tindakan lanjutan.
4. Di **Quick Actions** gunakan **Pending Arrivals**, **Pending Departures** (jika tersedia dan Anda berhak mencatat stempel kedatangan dan keberangkatan), **View All Travels** ke daftar, atau **New Official Travel** untuk pengajuan baru (jika tombol tampil).

<p align="center">
<img src="images/official-travel-dashboard.png" alt="Official Travel Dashboard: empat ringkasan Total Travels, Active Travels, Pending Arrivals, This Month; Travel Status Overview dengan New Official Travel; Quick Actions; tabel Open Official Travels; panel Monthly Trend dan Top Destinations" style="max-width: 100%; width: 100%; height: auto;" />
</p>

**Catatan:** Jumlah **Pending Arrivals** / **Pending Departures** terkait tugas pencatatan **stempel** untuk perjalanan yang sudah disetujui; tombol terkait hanya tampil jika **hak akses** akun Anda memungkinkan.

---

## 2. Untuk HR — Daftar permintaan (**Requests**)

### Langkah-langkah — **Official Travels** (daftar & filter)

1. **Login** ke ARKA HERO.
2. Di sidebar, **Official Travel Management** → **Requests**.
3. Gunakan **Filter** (buka panel **Filter**) untuk **Date From**, **Date To**, **Travel Number**, **Destination**, **NIK**, **Traveler Name**, **Project**, **Status**, dan kriteria lain jika tersedia.
4. Baca tabel; gunakan **Export** untuk ekspor data jika perlu.
5. Klik **Add** (tombol kuning dengan ikon **+**) bila diizinkan, untuk buat LOT baru.
6. Pada baris data, gunakan ikon/aksi (misalnya **View** / **Edit**) sesuai tampilan untuk membuka detail atau **Edit**.

<p align="center">
<img src="images/official-travels-list.png" alt="Halaman Official Travels: judul, breadcrumb Home, tombol Export dan Add, bar Filter, tabel dengan kolom No, Travel Number, Date, Traveler, Project, Destination, Status, Creator, Action (ikon mata), serta pagination" style="max-width: 80%; width: 80%; height: auto;" />
</p>

**Catatan:** Opsi **Status** di filter dapat mencakup nilai seperti **Draft**, **Menunggu Konfirmasi HR**, **Submitted**, **Approved**, **Rejected**, **Closed** — tergantung proses di perusahaan.

---

## 3. Formulir pengajuan HR — **Letter Number**, **Official Travel Detail**, **Flight Request**, **Approver Selection**

### Langkah-langkah — buat atau ubah LOT (_Add Official Travel (LOT)_ / _Edit Official Travel_)

**1.** Buka **Add** dari halaman **Official Travels** (lihat bagian 2), atau buka data yang sudah ada lewat **Edit** dari daftar.
**2. Letter Number**  
Pilih nomor surat lewat bagian **Letter Number** (kategori surat yang dipakai organisasi; untuk LOT kategorinya (B) Surat Internal). Setelah memilih, **LOT Number** umumnya terisi otomatis; baca teks bantuan di bawah isian jika ada. Jika nomor surat belum ada, klik **Create New**.

<p align="center" id="step-02-letter-number">
<img src="images/official-travels-step-02-letter-number.png" alt="Form LOT: bagian Letter Number, LOT Number, tombol Create New" style="max-width: 75%; width: 75%; height: auto;" />
</p>

Jika membuat nomor surat lewat **Create New**, tampil form **Create Letter Number** (**Basic Information**, **Next Number Preview**, isian proyek, kategori, tanggal, subjek, dst.).

<p align="center" id="step-02b-create-letter">
<img src="images/official-travels-step-02b-create-letter-number.png" alt="Form Create Letter Number: Next Number Preview, Project, Letter Category, Letter Date, Subject, tujuan surat" style="max-width: 75%; width: 75%; height: auto;" />
</p>

**3. Travel Information**  
Isi **LOT Date**, **LOT Origin** (pilih project asal), **Main Traveler**, **Purpose**, **Destination**, **Departure Date**, **Duration**. **Title**, **Business Unit**, **Department** terisi otomatis dari **Main Traveler** (bukan diisi manual di sini).

<p align="center" id="step-03-travel-information">
<img src="images/official-travels-step-03-travel-information.png" alt="Kartu Travel Information: LOT Date, Origin, Main Traveler, Purpose, Destination, Departure Date, Duration" style="max-width: 75%; width: 75%; height: auto;" />
</p>

**4. Followers** (opsional)  
Klik **Add Follower** untuk menambah baris, pilih karyawan; **X** / **Remove** untuk menghapus baris.

<p align="center" id="step-04-followers">
<img src="images/official-travels-step-04-followers.png" alt="Kartu Followers: tabel NIK, Title, Business Unit, Department, Add Follower" style="max-width: 75%; width: 75%; height: auto;" />
</p>

**5. Travel Arrangements**  
Pilih **Transportation** dan **Accommodation** (data dari **Master Data** → **Official Travel Data**).

<p align="center" id="step-05-travel-arrangements">
<img src="images/official-travels-step-05-travel-arrangements.png" alt="Kartu Travel Arrangements: dropdown Transportation dan Accommodation" style="max-width: 55%; width: 55%; height: auto;" />
</p>

**6. Flight Request** (opsional)  
Centang **Check if you need flight ticket reservation**; isi segmen penerbangan sesuai form. Jika LOT disetujui, pengajuan tiket diproses HR HO Balikpapan sesuai kebijakan.

<p align="center" id="step-06-flight-request">
<img src="images/official-travels-step-06-flight-request.png" alt="Kartu Flight Request: centang Check if you need flight ticket reservation, segmen Flight 1 dengan From, To, Date, Time, Airline, tombol Add Flight Segment" style="max-width: 55%; width: 55%; height: auto;" />
</p>

**7. Approver Selection**  
Cari approver (nama atau email), baca **Approval Rules Information** untuk mengetahui aturan pemilihan approval untuk LOT. Jika approver tidak tersedia, hubungi HR/IT HO Balikpapan.

<p align="center" id="step-07-approver-selection">
<img src="images/official-travels-step-07-approver-selection.png" alt="Kartu Approver Selection: pencarian, approver terpilih berurut, Approval Rules Information" style="max-width: 55%; width: 75%; height: auto;" />
</p>

**8.** Simpan: **Save as Draft** (bisa diubah lewat **Edit** sampai disetujui) atau **Save & Submit** (ajukan ke approver). **Cancel** kembali ke daftar. Di detail LOT, gunakan **Print** jika tersedia.

**9.** Untuk LOT yang masih **Draft**, buka detail LOT lalu klik **Submit for Approval** untuk mengajukan persetujuan.

**10.** Untuk LOT dari **My Official Travel Request** (nomor **REQxxxxx**), buka detail LOT, klik **Konfirmasi & Isi Nomor Surat**; pilih nomor surat (seperti langkah 2), tentukan approver (seperti langkah 7), **Update**; lalu di detail LOT klik **Submit for Approval** untuk meneruskan ke proses approval.

---

## 4. Alur Perjalanan Dinas — **Arrivals**, **Departures**, **Stops/Checkpoint**, **Close**

1. Setelah LOT disetujui (sering tampil sebagai **Open** / **Approved** di badge status), karyawan yang melakukan perjalanan dinas akan mendapatkan nomor LOT.
2. Setelah karyawan sampai di lokasi tujuan, admin HR yang di lokasi akan mencari LOT berdasarkan nomor LOT, kemudian melakukan proses [Arrival Check](#langkah-langkah--record-arrival-arrival-check)
3. Saat karyawan akan meninggalkan lokasi tujuan dan kembali ke lokasi awal atau ke lokasi selanjutnya, admin HR akan mencari LOT berdasarkan nomor LOT, kemudian melakukan proses [Departure Check](#langkah-langkah--record-departure-departure-check)
4. Jika karyawan melakukan perjalanan ke lokasi selanjutnya / tidak langsung kembali ke lokasi awal tapi masih dalam lingkungan perusahaan, maka admin HR di lokasi selanjutnya harus melakukan proses **Arrival Check** dan **Departure Check** seperti no.2 dan 3. Jika karyawan melakukan perjalanan ke luar lingkungan perusahaan, maka pencatatan **stop/checkpoint** dilakukan oleh admin HR lokasi awal karyawan.
5. Pencarian nomor LOT bisa dilakukan dari List Official Travel atau dari Dashboard Official Travel
6. Setiap entri memuat **Stop #N** dengan subbagian **Arrival** / **Departure** (jika sudah tercatat) atau keterangan seperti **Arrival Only** / **Departure Only** tergantung isian yang sudah ada.
7. Jika belum ada **stop/checkpoint**, timeline dapat menunjukkan bahwa pencatatan belum dimulai.
8. Jika karyawan sudah kembali ke lokasi awal, admin HR akan mencari LOT berdasarkan nomor LOT, kemudian melakukan proses [Close Official Travel](#langkah-langkah--menutup-perjalanan-close)

### Langkah-langkah — **Record Arrival** (_Arrival Check_)

1. Di halaman detail LOT, klik **Record Arrival** jika tersedia.
2. Pada kartu **Arrival Check**, isi **Arrival Date & Time** dan **Arrival Notes** (wajib).
3. Klik **Confirm Arrival**; baca pertanyaan konfirmasi di jendela pop-up. **Cancel** kembali ke detail.

<p align="center">
<img src="images/official-travels-arrival-check.png" alt="Halaman detail LOT dengan status Open: kiri Travel Details dan Accompanying Travelers; kanan kartu Arrival Check berisi Arrival Date & Time, Arrival Notes, tombol Confirm Arrival dan Cancel" style="max-width: 75%; width: 75%; height: auto;" />
</p>

### Langkah-langkah — **Record Departure** (_Departure Check_)

1. Setelah urutan stempel memungkinkan, klik **Record Departure** di halaman detail.
2. Baca **Current Stop Information** (misalnya **Arrival Confirmed By**, **Arrival Notes**).
3. Isi **Departure Date & Time** dan **Departure Notes** pada **Departure Check**; klik **Confirm Departure**; setujui peringatan bila muncul.

<p align="center">
<img src="images/official-travels-departure-check.png" alt="Halaman detail LOT dengan status Open: kiri Travel Details, Current Stop Information, Accompanying Travelers; kanan kartu Departure Check berisi Departure Date & Time, Departure Notes, tombol Confirm Departure dan Cancel" style="max-width: 75%; width: 75%; height: auto;" />
</p>

**Catatan:** Urutan wajar: **arrival** pada suatu **stop** sebelum **departure**; aksi mungkin ditolak jika urutan belum benar atau **hak akses** tidak memadai.

### Langkah-langkah — menutup perjalanan (**Close**)

1. Buka detail LOT bila kondisi sudah memenuhi syarat penutupan (misalnya perjalanan selesai menurut kebijakan).
2. Klik **Close Official Travel**; di jendela **Close Travel Request** baca teks peringatan di layar.
3. Klik **Yes, Close Travel** untuk melanjutkan. Setelah sukses, status dapat menjadi **Closed** dan perubahan berikutnya dibatasi.

**Catatan:** Pencatatan stempel dan penutupan hanya tersedia bila **hak akses** Anda sesuai; bila menu atau tombol tidak muncul, hubungi **administrator**.

---

## 5. Untuk HR — **Reports**

### Langkah-langkah — buka ringkasan laporan

1. **Login** ke ARKA HERO.
2. **Official Travel Management** → **Reports**.
3. Baca penjelasan kartu (analitik & laporan LOT), lalu klik **View Report** pada **Official Travel Requests Report** untuk membuka halaman laporan.
4. Di halaman **Report Official Travel Requests**, isi **Filter Options** (**Status**, **Project (origin)**, rentang **LOT date**, **LOT number**, **Destination**, **Traveler**, **Purpose**, dan sebagainya), lalu klik **Tampilkan data** untuk memuat tabel. Gunakan **Reset** bila perlu mengosongkan filter, dan **Export to Excel** jika tersedia. Tombol **Back to Reports** mengembalikan ke ringkasan laporan.

<p align="center">
<img src="images/official-travel-reports-travel-requests.png" alt="Report Official Travel Requests: Filter Options dengan Tampilkan data, Reset, Export to Excel; bagian Report Data berisi tabel LOT, Traveler, Status, Action" style="max-width: 100%; width: 100%; height: auto;" />
</p>

**Catatan:** Laporan ini umumnya memerlukan setidaknya satu kriteria filter dipilih dulu; jika tabel tampil kosong, coba pilih **Date**, **Status**, proyek, atau isian filter lain, lalu muat ulang.

---

## 6. Karyawan (non–HR) — **My Official Travel Request**

Bagi karyawan dengan peran “user” (menu **My Features**), pengajuan pribadi lewat item berikut (bukan menu **Official Travel Management** di atas).

### Langkah-langkah — buka daftar & ajukan

1. **Login** ke ARKA HERO.
2. Di sidebar, **My Features** → **My Official Travel Request** (bukan menu HR **Official Travel Management**).
3. Buka panel **Filter** bila perlu; gunakan **Travel Number**, **Status**, **Role** (**Main Traveler** / **Follower**), dan lainnya.
4. Klik **New Request** untuk mengajukan permintaan baru (jika tombol tampil). <a href="#submit-lot-request">Lihat gambar</a>.
5. Isi form **Submit LOT Request** (informasi perjalanan, **Followers**, **Travel Arrangements**, **Flight Request**, dan seterusnya), lalu klik **Submit to HR** untuk mengirim. LOT akan terbentuk nomor dengan format **REQxxxxx** sampai HR mengonfirmasi; surat resmi dan nomor LOT final ditetapkan HR.
6. Untuk melihat detail atau mengubah: di daftar, gunakan action **View** atau **Edit** pada baris terkait.
7. Langkah selanjutnya akan diproses oleh admin HR untuk proses konfirmasi dengan mengisi nomor LOT yang resmi (ARKA/Bxxxx/HR/IV/2026) dan menentukan approver yang sesuai.

<p align="center" id="submit-lot-request">
<img src="images/my-official-travel-submit-lot-request.png" alt="Form Submit LOT Request: peringatan HR, Travel Information, Travel Arrangements, Flight Request, Followers, Submit to HR, Cancel" style="max-width: 100%; width: 100%; height: auto;" />
</p>

---

## 7. Kesalahan & bantuan

| Gejala / pesan (contoh)                                      | Kemungkinan penyebab                                                        | Apa yang bisa dicoba                                                                             |
| :----------------------------------------------------------- | :-------------------------------------------------------------------------- | :----------------------------------------------------------------------------------------------- |
| Tombol **Add** / **Record Arrival** / **Close** tidak muncul | Akun tidak memiliki **hak akses** yang diperlukan                           | Hubungi **administrator** agar izin memakai fitur ini disesuaikan dengan tugas Anda.             |
| **LOT Number** tidak terisi                                  | **Letter Number** belum dipilih atau aturan surat kantor belum terpenuhi    | Pilih kembali surat; ikuti kebijakan **Letter Administration** (nomor surat) di perusahaan Anda. |
| Tabel **Reports** selalu kosong                              | Filter belum diisi cukup (sering wajib minimal satu kriteria)               | Pilih setidaknya satu kriteria, lalu muat ulang.                                                 |
| Tidak dapat **Confirm Arrival** / **Confirm Departure**      | Urutan pencatatan stempel belum benar, atau pemberi stempel bukan akun Anda | Periksa **Travel Stops Timeline**; selesaikan stempel sebelumnya; tanyakan HR bila ragu.         |
| Pesan wajib pilih approver ( **Approver Selection** )        | Jumlah approver belum memenuhi syarat                                       | Pilih approver lewat pencarian hingga memenuhi aturan.                                           |
| Akses ditolak, atau halaman “tidak ditemukan”                | Tautan atau nomor bukan milik data Anda, atau bukan bagian wewenang Anda    | Buka kembali dari **menu** dan daftar; jangan menebak tautan; pastikan memakai akun yang benar.  |

### Menghubungi administrator

Hubungi **administrator** (atau **IT** / **HR**) jika: menu tidak tampil padahal seharusnya, status LOT tidak berubah setelah tindakan wajar, pesan di layar tidak tercantum di tabel, atau Anda membutuhkan koreksi data master (**Transportations**, **Accommodations**, **Projects**).

**Jangan** mengirim **password** lewat obrolan atau surel. Cukup sampaikan **username**, nomor **LOT** / **Travel Number**, waktu kejadian, dan cuplikan pesan error.

---

</div>
