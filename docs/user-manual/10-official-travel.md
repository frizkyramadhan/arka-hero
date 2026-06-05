<div style="text-align: justify; text-justify: inter-word;">

# Official Travel Management (LOT)

| **Versi** | **Tanggal** | **Revisi (ringkas)**                                                                                                                                      |
| :-------- | :---------- | :-------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 1.2       | 2026-06-05  | **Update Approvers** pada detail LOT **Submitted**: hanya langkah **Pending** yang dapat diganti; approver yang sudah **Approved**/**Rejected** terkunci. |
| 1.1       | 2026-05-13  | **Multi-stop** & destinasi **manual** (centang).                                                                                                          |
| 1.0       | 2026-05-08  | Panduan LOT: dashboard & permintaan HR, formulir, stempel & tutup, laporan, **My Official Travel Request**, troubleshooting.                              |

Panduan ini menjelaskan **Letter of Travel (LOT)** / perjalanan dinas resmi di ARKA HERO untuk **staf HR** yang mengelola data LOT (dashboard, daftar permintaan, alur kedatangan–keberangkatan, pelaporan) dan untuk **karyawan selain HR** yang mengajukan lewat menu pribadi/personal.

| **Istilah**                                                | Arti singkat                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                  |
| :--------------------------------------------------------- | :------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **LOT**                                                    | _Letter of Travel_ — surat/tiket perjalanan dinas; nomor **LOT Number** mengacu pada dokumen ini.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             |
| **Official Travels**                                       | Judul halaman daftar permintaan LOT (HR).                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     |
| **Letter Number**                                          | Pemilihan nomor surat dari sistem surat (kategori terkait) sebelum LOT terbit.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                |
| **Travel Stops Timeline**                                  | Riwayat perjalanan yang terdiri dari urutan **Stop** / **Checkpoint**; tiap stop dapat memiliki waktu kedatangan (**Arrival**) dan keberangkatan (**Departure**) (keberangkatan).                                                                                                                                                                                                                                                                                                                                                                                                                                                                             |
| **Stop** / **Checkpoint** / **Destination**                | Titik tujuan sepanjang perjalanan dinas (LOT). Satu perjalanan dapat terdiri dari beberapa stop/checkpoint/tujuan. Pada setiap tujuan, dicatat waktu kedatangan (**Arrival**) dan waktu keberangkatan (**Departure**). Tujuan dapat berupa project internal perusahaan, kota, dinas, vendor, dsb. Aturan utama: <br>- Minimal ada satu tujuan di setiap LOT. <br>- Jika terdapat beberapa tujuan, maka pemilihan tujuan bukan berarti urutan perjalanan. <br>- Jika di salah satu tujuan sudah ada pencatatan waktu kedatangan, maka tujuan lain tidak bisa melakukan pencatatan yang sama sampai sudah ada pencatatan waktu keberangkatan di lokasi pertama. |
| **Approver Selection**                                     | Pemilihan satu atau lebih **approver** yang menyetujui pengajuan.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             |
| **Update Approvers**                                       | Tombol pada kartu **Approval Status** (detail LOT **Submitted**) untuk mengganti approver yang masih **Pending**; approver yang sudah memutuskan tidak dapat diubah.                                                                                                                                                                                                                                                                                                                                                                                                                                                                                          |
| **Flight Request**                                         | Bagian opsional untuk kebutuhan tiket pesawat (terhubung modul penerbangan jika dipakai).                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     |
| **Master Data** → **Transportations** / **Accommodations** | Data referensi untuk pilihan **Transportation** dan **Accommodation** pada formulir LOT (di **GENERAL SECTION**).                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                             |

---

<br>

## 1. Untuk HR — Dashboard LOT

### Langkah-langkah — membuka **Official Travel Dashboard** (_Official Travel Dashboard_)

1. **Login** ke ARKA HERO.
2. Di sidebar, buka grup **Official Travel Management**, lalu klik **Dashboard**.
3. Baca ringkasan di layar (masing-masing memberi gambaran cepat tentang keadaan LOT):
    - **Cakupan data (project Anda):** ringkasan angka dan tabel di bawah memakai LOT yang **relevan dengan project ter-assign** pada akun login Anda (biasanya **LOT Origin** dan/atau **stop** yang cocok dengan assignment di sistem). **Pending Arrivals** dan **Pending Departures** hanya bermakna jika Anda punya **hak mencatat stempel**; isinya dibatasi ke LOT / stop yang **menurut aturan boleh Anda tangani**, tidak semua LOT dalam ringkasan umum.
    - **Total Travels** — jumlah LOT dalam **lingkup project** Anda sejak pencatatan (acuan “semua waktu” tetap dipotong oleh filter assignment).
    - **Active Travels** — di antara LOT dalam lingkup tersebut, yang statusnya masih berjalan / belum ditutup.
    - **Pending Arrivals** — jumlah LOT yang proses kedatangannya (**Arrival**) masih pending.
    - **This Month** — jumlah LOT dalam lingkup project Anda yang tercatat pada bulan berjalan.
    - **Travel Status Overview** — kotak hitung per status (**Draft**, **Submitted**, **Approved**, **Rejected**) dalam **lingkup project** yang sama.
    - Tabel **Open Official Travels** — baris LOT terbuka dalam lingkup itu; kolom **Travel Number**, **Traveler**, **Destination**, **Date**, **Status**, **Action**. **Destination** bisa **lebih dari satu baris** jika banyak checkpoint.
4. Di **Quick Actions** gunakan **Pending Arrivals**, **Pending Departures** (jika tersedia dan Anda berhak mencatat stempel kedatangan dan keberangkatan), **View All Travels** ke daftar, atau **New Official Travel** untuk pengajuan baru (jika tombol tampil).

<p align="center">
<img src="images/official-travel-dashboard.png" alt="Official Travel Dashboard: empat ringkasan Total Travels, Active Travels, Pending Arrivals, This Month; Travel Status Overview dengan New Official Travel; Quick Actions; tabel Open Official Travels; panel Monthly Trend dan Top Destinations" style="max-width: 95%; width: 95%; height: auto;" />
</p>

**Catatan:** Jumlah **Pending Arrivals** / **Pending Departures** terkait tugas pencatatan **stempel** untuk perjalanan yang sudah disetujui; tombol terkait hanya tampil jika **hak akses** akun Anda memungkinkan.

---

## 2. Untuk HR — Daftar permintaan (**Requests**)

### Langkah-langkah — **Official Travels** (daftar & filter)

1. **Login** ke ARKA HERO.
2. Di sidebar, **Official Travel Management** → **Requests**.
3. Gunakan **Filter** (buka panel **Filter**) untuk **Date From**, **Date To**, **Travel Number**, **Destination**, **NIK**, **Traveler Name**, **Project**, **Status**, dan kriteria lain jika tersedia. **Destination** memfilter berdasarkan teks di **tiap stop** itinerary. Di tabel, kolom **Destination** menampilkan **daftar bertingkat** (beberapa bullet) jika LOT punya banyak stop; satu teks jika hanya satu tujuan.
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

Jika membuat nomor surat lewat **Create New**, tampil form **Create Letter Number** (**Basic Information**, **Next Number Preview**, isian project, kategori, tanggal, subjek, dst.).

<p align="center" id="step-02b-create-letter">
<img src="images/official-travels-step-02b-create-letter-number.png" alt="Form Create Letter Number: Next Number Preview, Project, Letter Category, Letter Date, Subject, tujuan surat" style="max-width: 75%; width: 75%; height: auto;" />
</p>

**3. Travel Information**  
Pada kartu **Travel Information**: **LOT Number** memakai pola `ARKA/[Letter Number]/HR-[Project Code]/bulan-romawi/tahun`; isian disesuaikan setelah Anda memilih nomor surat dan **LOT Origin** di langkah sebelumnya (ada teks bantuan di bawah field). Isi **LOT Date**, **LOT Origin** (dropdown **project asal**), dan **Main Traveler**. **Title**, **Business Unit**, dan **Department** di bagian bawah kartu adalah **ringkasan otomatis** dari karyawan yang dipilih sebagai Main Traveler — tidak perlu diketik di sini.

<p align="center" id="step-03-travel-information">
<img src="images/official-travels-step-03-travel-information.png" alt="Kartu Travel Information: LOT Number (template ARKA/[Letter Number]/HR-[Project Code]/...), LOT Date, LOT Origin, Main Traveler, serta Title/Business Unit/Department read-only" style="max-width: 75%; width: 75%; height: auto;" />
</p>

**4. Destination & schedule** (kartu berikutnya pada form yang sama) mengatur **itinerary** dan jadwal ringkas:

- **Beberapa destinasi:** klik **+ Add destination** untuk menambah **stop** (Destination 1, 2, …). Tiap baris punya tautan **Remove** untuk menghapus stop yang tidak dipakai.
- **Pilih project (kotak manual tidak dicentang):** gunakan dropdown **Select project** — destinasi dianggap terikat ke **project aktif** yang dipilih.
- **Input manual (kotak dicentang):** field berubah menjadi teks **Manual destination label**; ketik label destinasi bebas. Sesuai petunjuk di layar, **stempel checkpoint** pada stop **manual** mengikuti **penugasan proyek LOT Origin** (proyek asal perjalanan), bukan daftar project destinasi.
- Isi juga **Purpose**, **Departure Date**, dan **Duration** pada bagian ini.

<p align="center" id="step-03-destination-schedule">
<img src="images/official-travels-step-03-destination-schedule.png" alt="Kartu Destination & schedule: Add destination, Destination 1 Select project, Destination 2 centang manual dengan Manual destination label, Purpose, Departure Date, Duration" style="max-width: 75%; width: 75%; height: auto;" />
</p>

**5. Followers** (opsional)  
Klik **Add Follower** untuk menambah baris, pilih karyawan; **X** / **Remove** untuk menghapus baris.

<p align="center" id="step-04-followers">
<img src="images/official-travels-step-04-followers.png" alt="Kartu Followers: tabel NIK, Title, Business Unit, Department, Add Follower" style="max-width: 75%; width: 75%; height: auto;" />
</p>

**6. Travel Arrangements**  
Pilih **Transportation** dan **Accommodation** (data dari **Master Data** → **Official Travel Data**).

<p align="center" id="step-05-travel-arrangements">
<img src="images/official-travels-step-05-travel-arrangements.png" alt="Kartu Travel Arrangements: dropdown Transportation dan Accommodation" style="max-width: 50%; width: 50%; height: auto;" />
</p>

**7. Flight Request** (opsional)  
Centang **Check if you need flight ticket reservation**; isi segmen penerbangan sesuai form. Jika LOT disetujui, pengajuan tiket diproses HR HO Balikpapan sesuai kebijakan.

<p align="center" id="step-06-flight-request">
<img src="images/official-travels-step-06-flight-request.png" alt="Kartu Flight Request: centang Check if you need flight ticket reservation, segmen Flight 1 dengan From, To, Date, Time, Airline, tombol Add Flight Segment" style="max-width: 50%; width: 50%; height: auto;" />
</p>

**8. Approver Selection**  
Cari approver (nama atau email), baca **Approval Rules Information** untuk mengetahui aturan pemilihan approval untuk LOT. Jika approver tidak tersedia, hubungi HR/IT HO Balikpapan.

<p align="center" id="step-07-approver-selection">
<img src="images/official-travels-step-07-approver-selection.png" alt="Kartu Approver Selection: pencarian, approver terpilih berurut, Approval Rules Information" style="max-width: 50%; width:50%; height: auto;" />
</p>

**9.** Simpan: **Save as Draft** (bisa diubah lewat **Edit** sampai disetujui) atau **Save & Submit** (ajukan ke approver). **Cancel** kembali ke daftar.

**Detail LOT — Cetak:** tombol **Print** bertipe **split** (gabungan tombol utama + panah). Bagian utama: cetak **semua** destinasi dan seluruh blok stempel pada satu halaman. **Menu tarik-turun:** pilih **satu stop** — satu lembar cetak hanya untuk destinasi itu (berguna memberikan formulir stempel per lokasi). Jika LOT **hanya satu** destinasi tanpa baris itinerary terpisah, hanya tampil tombol cetak biasa.

**10.** Untuk LOT yang masih **Draft**, buka detail LOT lalu klik **Submit for Approval** untuk mengajukan persetujuan.

**11.** Untuk LOT dari **My Official Travel Request** (nomor **REQxxxxx**), buka detail LOT, klik **Konfirmasi & Isi Nomor Surat**; pilih nomor surat (seperti langkah 2), tentukan approver (seperti langkah 7), **Update**; lalu di detail LOT klik **Submit for Approval** untuk meneruskan ke proses approval.

### Detail LOT — status dan alur persetujuan

Setelah LOT dibuat atau diajukan, buka detail lewat **View** dari daftar **Official Travels**. Panel kanan menampilkan **Travel Stops Timeline**, **Flight Request** (jika ada), dan kartu **Approval Status** (daftar approver beserta status langkah).

**Aksi (panel kanan dan tombol aksi, sesuai status dan hak akses)**

| Status                        | Aksi umum                                                                                                                      |
| :---------------------------- | :----------------------------------------------------------------------------------------------------------------------------- |
| **Draft**                     | **Edit**, **Delete**, **Submit for Approval**                                                                                  |
| **Submitted**                 | Approver memproses lewat **My Approvals**; HR dapat **Update Approvers** selama masih ada langkah **Pending** (lihat di bawah) |
| **Approved** / **Open**       | **Record Arrival**, **Record Departure**, **Edit itinerary** (jika berwenang), **Close Official Travel**, **Print**            |
| Semua (kecuali ditolak/batal) | **Print**, **Back to List**                                                                                                    |

**Catatan:** Setelah **Submit for Approval**, isian LOT (traveler, destinasi, jadwal, transportasi, dll.) **tidak dapat diedit** lagi lewat **Edit**; yang masih dapat disesuaikan HR hanya **approver pada langkah Pending** lewat **Update Approvers** di kartu **Approval Status**.

**Penjelasan singkat — alur persetujuan LOT**

1. Pembuat (HR atau karyawan via **My Official Travel Request** yang dikonfirmasi HR) menyimpan **Draft** atau mengajukan (**Submitted**).
2. **Approver** yang dipilih memberi keputusan di **My Approvals** (jenis dokumen **Official Travel**).
3. Setelah **Approved**, perjalanan dapat distempel (**Arrival** / **Departure**) dan ditutup sesuai bagian 4.

<a id="mengubah-approver-pending-lot"></a>

### Langkah-langkah — mengubah approver yang masih **Pending** (_Update Approvers_)

Fitur ini hanya untuk **HR** dengan hak **`official-travels.edit`**, pada detail LOT berstatus **Submitted**, dan **bukan** dari halaman **My Official Travel Request** karyawan.

1. Buka detail LOT (**View** dari daftar **Official Travels**).
2. Pada kartu **Approval Status** di kolom kanan, baca daftar approver:
    - Approver yang sudah **Approved** atau **Rejected** ditampilkan dengan badge status dan **tidak** memiliki tombol hapus (terkunci, border putus-putus).
    - Approver yang masih **Pending** dapat dihapus (ikon **×**) lalu diganti lewat kotak pencarian approver.
3. Untuk **mengganti** approver pending: klik **×** pada baris pending, ketik nama/email approver pengganti (minimal 2 karakter), pilih dari daftar — orang baru akan masuk pada **urutan yang sama**.
4. Anda juga dapat **menghapus** approver pending di akhir daftar atau **menambah** approver pending baru di urutan belakang, selama minimal satu approver tetap ada dan approver terkunci tidak diubah.
5. Klik **Update Approvers**. Pesan sukses mengonfirmasi pembaruan langkah pending; approver baru menerima antrean di **My Approvals**.

**Catatan:**

- Form **Update Approvers** **tidak** muncul jika semua langkah sudah diputuskan (tidak ada **Pending** tersisa) atau LOT bukan **Submitted**.
- Urutan approver mengikuti nomor **1**, **2**, **3**, … pada badge; approver terkunci tetap di posisi semula.
- Approver **tidak boleh duplikat** dalam satu LOT.
- Tampilan kartu **Approval Status** memakai komponen yang sama dengan modul FPTK; perilaku terkunci/pending identik.

---

## 4. Alur Perjalanan Dinas — **Arrivals**, **Departures**, **Stops/Checkpoint**, **Edit Destination**, **Close**

1. Setelah LOT disetujui (sering tampil sebagai **Open** / **Approved** di badge status), karyawan yang melakukan perjalanan dinas akan mendapatkan nomor LOT.
2. Setelah karyawan sampai di lokasi tujuan, **petugas HR di lokasi** mencari LOT berdasarkan nomor LOT, lalu melakukan [Arrival Check](#langkah-langkah--record-arrival-arrival-check).
3. Saat karyawan akan meninggalkan lokasi tujuan dan kembali ke lokasi awal atau ke lokasi selanjutnya, petugas HR yang berwenang mencari LOT tersebut, lalu melakukan [Departure Check](#langkah-langkah--record-departure-departure-check).
4. Jika perjalanan dilanjutkan ke lokasi berikutnya (masih dalam lingkungan perusahaan), **petugas HR di lokasi berikutnya** mengulang **Arrival** dan **Departure** seperti langkah 2–3, mengikuti aturan **checkpoint** di atas. Jika perjalanan dilakukan keluar lingkungan perusahaan, **stop/checkpoint** akan ditangani **petugas HR di lokasi awal** karyawan—sesuai kebijakan setempat.
5. Pencarian nomor LOT bisa dilakukan dari List Official Travel atau dari Dashboard Official Travel
6. Setiap entri memuat **Destination #N** dengan subbagian **Arrival** / **Departure** (jika sudah tercatat) atau keterangan seperti **Arrival Only** / **Departure Only** tergantung isian yang sudah ada di **Travel Stops Timeline**.

<p align="center" id="travel-stops-timeline-example">
<img src="images/official-travels-travel-stops-timeline.png" alt="Travel Stops Timeline: kartu DESTINATION 1 untuk satu stop (contoh kode project dan kota), badge Complete, progress arrival–departure, detail Arrival dan Departure dengan waktu, petugas, dan catatan" style="max-width: 50%; width: 50%; height: auto;" />
</p>

7. Jika belum ada **stop/checkpoint**, timeline dapat menunjukkan bahwa pencatatan belum dimulai.
8. Penutupan LOT: ketika seluruh rangkaian selesai, atau — bila dinas dihentikan lebih awal — melalui aturan **Close** di [menutup perjalanan](#langkah-langkah--menutup-perjalanan-close) (termasuk **penutupan dini** dari project **LOT Origin** setelah minimal satu checkpoint lengkap).

### Langkah-langkah — **Record Arrival** (_Arrival Check_)

1. Di halaman detail LOT (atau lewat **Open Official Travels** / antrean **Pending Arrivals** di dashboard bila Anda berwenang), klik **Record Arrival** jika tersedia. Pada LOT **beberapa destinasi**, bila lebih dari satu stop relevan untuk akun Anda, pilih **Destination (checkpoint)** di formulir sebelum mengisi waktu dan catatan.
2. Pada kartu **Arrival Check**, isi **Arrival Date & Time** dan **Arrival Notes** (wajib).
3. Klik **Confirm Arrival**; baca pertanyaan konfirmasi di jendela pop-up. **Cancel** kembali ke detail.

<p align="center">
<img src="images/official-travels-arrival-check.png" alt="Halaman detail LOT dengan status Open: kiri Travel Details dan Accompanying Travelers; kanan kartu Arrival Check berisi Arrival Date & Time, Arrival Notes, tombol Confirm Arrival dan Cancel" style="max-width: 85%; width: 85%; height: auto;" />
</p>

### Langkah-langkah — **Record Departure** (_Departure Check_)

1. Setelah urutan stempel memungkinkan, klik **Record Departure** di halaman detail.
2. Baca **Current Stop Information** (misalnya **Arrival Confirmed By**, **Arrival Notes**).
3. Isi **Departure Date & Time** dan **Departure Notes** pada **Departure Check**; klik **Confirm Departure**; setujui peringatan bila muncul.

<p align="center">
<img src="images/official-travels-departure-check.png" alt="Halaman detail LOT dengan status Open: kiri Travel Details, Current Stop Information, Accompanying Travelers; kanan kartu Departure Check berisi Departure Date & Time, Departure Notes, tombol Confirm Departure dan Cancel" style="max-width: 85%; width: 85%; height: auto;" />
</p>

**Catatan:** Pada satu **stop**, **arrival** harus lebih dulu dari **departure**. Pada LOT **multi–destinasi**, **urutan antar stop** mengikuti subbagian **LOT satu checkpoint dan banyak checkpoint**; tombol dapat disembunyikan bila **hak akses** atau fase checkpoint belum mengizinkan.

### Langkah-langkah — Menyesuaikan tujuan setelah LOT disetujui (**Edit itinerary**)

Jika karyawan melakukan perjalanan ke tujuan berbeda di luar rencana, **staf HR pada project LOT Origin** dapat memperbarui (menambah/mengubah) destinasi perjalanan. Sehingga LOT berstatus **Approved** (biasanya badge **Open**) dan sudah memiliki **stop** di database bisa diperbarui tujuannya.

1. Buka **halaman detail LOT**.
2. Pada kolom kanan (di bawah timeline perjalanan), cari kartu **Edit itinerary** — hanya muncul untuk **staf HR pada project LOT Origin**.
3. Baris dengan **Locked** / ikon gembok: stop yang sudah pernah distempel — **tidak boleh** diubah atau dihapus dari form ini.
4. Baris tanpa kunci: dapat diedit, ditambah lewat **+ Add Destinations**, atau dihapus (**Remove**) sesuai kebutuhan; setelah semua stop terkunci, Anda tetap dapat **menambah** destinasi baru di bawahnya.
5. Simpan lewat tombol **Save** pada kartu tersebut.

<p align="center" id="edit-itinerary-screenshot">
<img src="images/official-travels-edit-itinerary.png" alt="Kartu Edit itinerary: teks bantuan LOT origin dan jumlah stop terkunci, Destination 1 Locked (sudah distempel), Destination 2 dapat diedit dengan dropdown/centang manual, tombol Add Destinations dan Save" style="max-width: 50%; width: 50%; height: auto;" />
</p>

### Langkah-langkah — Menutup Perjalanan (**Close**)

1. Buka detail LOT bila kondisi memungkinkan penutupan (lihat aturan di bawah).
2. Klik **Close Official Travel**; di jendela **Close Travel Request** baca teks peringatan di layar. Jika LOT **multi–destinasi** tetapi **belum semua** checkpoint selesai, akan ada teks tambahan agar penutupan hanya dilakukan bila perjalanan **benar–benar** dihentikan lebih awal (misalnya kembali ke lokasi asal tanpa melanjutkan destinasi berikutnya).
3. Klik **Yes, Close Travel** untuk melanjutkan. Setelah sukses, status dapat menjadi **Closed** dan perubahan berikutnya dibatasi.

**Dua situasi penutupan:**

- **Semua checkpoint selesai** — setiap stop yang direncanakan sudah memiliki **arrival** dan **departure**; **staf HR pada project LOT Origin** dapat menutup LOT (sesuai tampilan tombol di detail atau dashboard).
- **Penutupan dini dari lokasi awal (LOT Origin)** — masih ada stop yang **belum** lengkap, tetapi **minimal satu** stop sudah tercatat **arrival** **dan** **departure**. Untuk skenario “beberapa destinasi tidak jadi dan kembali ke lokasi awal”. Setelah ditutup, stop yang belum terpakai tidak lagi dapat di-stempel.

**Catatan:** Pencatatan stempel dan penutupan hanya tersedia bila **hak akses** dan **penugasan project** Anda sesuai; bila menu atau tombol tidak muncul, minta bantuan **tim HR / IT**.

---

## 5. Untuk HR — **Reports**

### Langkah-langkah — buka ringkasan laporan

1. **Login** ke ARKA HERO.
2. **Official Travel Management** → **Reports**.
3. Baca penjelasan kartu (analitik & laporan LOT), lalu klik **View Report** pada **Official Travel Requests Report** untuk membuka halaman laporan.
4. Di halaman **Report Official Travel Requests**, isi **Filter Options** (**Status**, **Project (origin)**, rentang **LOT date**, **LOT number**, **Destination**, **Traveler**, **Purpose**, dan sebagainya), lalu klik **Tampilkan data** untuk memuat tabel. Isian **Destination** mencocokkan teks pada **tiap stop** itinerary (sama konsepnya dengan filter daftar **Requests**). Kolom **Destination** pada tabel laporan menampilkan **beberapa poin** bila LOT banyak stop; file **Export to Excel** menyertakan rangkuman teks berantai (**A → B → …**) untuk kolom destinasi. Gunakan **Reset** bila perlu mengosongkan filter, dan **Export to Excel** jika tersedia. Tombol **Back to Reports** mengembalikan ke ringkasan laporan.

<p align="center">
<img src="images/official-travel-reports-travel-requests.png" alt="Report Official Travel Requests: Filter Options dengan Tampilkan data, Reset, Export to Excel; bagian Report Data berisi tabel LOT, Traveler, Status, Action" style="max-width: 95%; width: 95%; height: auto;" />
</p>

**Catatan:** Laporan ini umumnya memerlukan setidaknya satu kriteria filter dipilih dulu; jika tabel tampil kosong, coba pilih **Date**, **Status**, project, atau isian filter lain, lalu muat ulang.

---

## 6. Karyawan (non–HR) — **My Official Travel Request**

Bagi karyawan dengan peran “user” (menu **My Features**), pengajuan pribadi lewat item berikut (bukan menu **Official Travel Management** di atas).

### Langkah-langkah — buka daftar & ajukan

1. **Login** ke ARKA HERO.
2. Di sidebar, **My Features** → **My Official Travel Request** (bukan menu HR **Official Travel Management**).
3. Buka panel **Filter** bila perlu; gunakan **Travel Number**, **Status**, **Role** (**Main Traveler** / **Follower**), **Destination** (mencocokkan stop mana pun pada itinerary), dan lainnya. Kolom destinasi pada daftar mengikuti tampilan **banyak tujuan** jika LOT memiliki beberapa stop.
4. Klik **New Request** untuk mengajukan permintaan baru (jika tombol tampil). <a href="#submit-lot-request">Lihat gambar</a>.
5. Isi form **Add My Official Travel (LOT)** — ikuti banner informasi di atas (nomor surat dan LOT final oleh HR). Pada kartu **Travel Information**: nomor sementara **REQxxxxx**, **Main Traveler** (diri Anda, read-only), **LOT Date**, **LOT Origin**, ringkasan Title/BU/Department. Pada **Destination & schedule**: **+ Add destination** untuk beberapa stop; per baris pilih **Select project** atau centang **manual** lalu isi teks bebas (stempel stop manual mengikuti penugasan **LOT Origin**); isi **Purpose**, **Departure Date**, **Duration**. Lengkapi **Followers** (opsional), **Travel Arrangements**, dan **Flight Request** bila perlu; lalu klik **Submit to HR**. LOT tetap berformat **REQxxxxx** sampai HR mengonfirmasi; nomor LOT resmi dan surat ditetapkan HR.

<p align="center" id="submit-lot-request">
<img src="images/my-official-travel-submit-lot-request.png" alt="Form Add My Official Travel (LOT): banner informasi HR, Travel Information dengan REQ dan Main Traveler read-only, Destination & schedule multi-stop dengan Add destination dan centang manual, Followers, Travel Arrangements, Flight Request, tombol Submit to HR dan Cancel" style="max-width: 88%; width: 88%; height: auto;" />
</p>

6. Untuk melihat detail atau mengubah: di daftar, gunakan action **View** atau **Edit** pada baris terkait. Di halaman **detail**, tombol **Print** berperilaku sama seperti di modul HR: **cetak penuh** lewat bagian utama tombol; **satu destinasi** lewat menu tarik-turun bila LOT punya beberapa **stop**.
7. Langkah selanjutnya diproses **tim HR** (konfirmasi surat, pengisian nomor LOT resmi seperti ARKA/Bxxxx/HR/IV/2026, dan penetapan approver).

---

## 7. Kesalahan & bantuan

| Gejala / pesan (contoh)                                            | Kemungkinan penyebab                                                                                            | Apa yang bisa dicoba                                                                                                                                                                                               |
| :----------------------------------------------------------------- | :-------------------------------------------------------------------------------------------------------------- | :----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| Tombol **Close** tidak muncul padahal satu destinasi sudah selesai | Akun Anda **bukan** penugasan **LOT Origin**, atau belum ada stop dengan **arrival + departure** lengkap        | Penutupan dini hanya untuk project **LOT Origin**. Pastikan minimal **satu** checkpoint **lengkap** untuk skenario dini; lengkapi **semua** stop untuk penutupan penuh.                                            |
| Tombol **Add** / **Record Arrival** / **Close** tidak muncul       | Akun tidak memiliki **hak akses** atau penugasan project yang diperlukan                                        | Hubungi **tim HR / pengelola akses** agar peran dan penugasan project disesuaikan dengan tugas Anda.                                                                                                               |
| **LOT Number** tidak terisi                                        | **Letter Number** belum dipilih atau aturan surat kantor belum terpenuhi                                        | Pilih kembali surat; ikuti kebijakan **Letter Administration** (nomor surat) di perusahaan Anda.                                                                                                                   |
| Tabel **Reports** selalu kosong                                    | Filter belum diisi cukup (sering wajib minimal satu kriteria)                                                   | Pilih setidaknya satu kriteria, lalu muat ulang.                                                                                                                                                                   |
| Tidak dapat **Confirm Arrival** / **Confirm Departure**            | Urutan **checkpoint** / fase paralel–kunci belum memungkinkan, atau akun bukan penanggung stempel destinasi itu | Periksa **Travel Stops Timeline** dan aturan multi–destinasi di bagian 4; pastikan **departure** pada stop yang sedang mengunci sudah selesai bila stop lain menunggu; tanyakan HR bila ragu.                      |
| Pesan wajib pilih approver ( **Approver Selection** )              | Jumlah approver belum memenuhi syarat                                                                           | Pilih approver lewat pencarian hingga memenuhi aturan.                                                                                                                                                             |
| LOT **Submitted** tidak bisa **Edit** isian perjalanan             | LOT sudah diajukan ke approver                                                                                  | Isian LOT (traveler, destinasi, jadwal, dll.) tidak bisa **Edit** lagi; untuk ganti approver pending gunakan **Update Approvers** di kartu **Approval Status** (lihat [bagian 3](#mengubah-approver-pending-lot)). |
| Tombol **Update Approvers** tidak tampil                           | Semua langkah sudah diputuskan atau status bukan **Submitted**                                                  | Normal jika tidak ada langkah **Pending**; selesaikan alur approval atau buat LOT baru bila perlu.                                                                                                                 |
| Pesan tidak dapat menutup LOT                                      | Syarat penutupan (penuh atau dini dari origin) belum terpenuhi, atau akun tidak berwenang                       | Baca [menutup perjalanan](#langkah-langkah--menutup-perjalanan-close); penutupan dini memerlukan akun **LOT Origin** dan minimal satu stop **lengkap**.                                                            |
| Menu **Print** tidak punya panah / hanya satu tombol               | LOT hanya **satu** destinasi tanpa baris itinerary terpisah                                                     | Yang ada split button hanya untuk LOT **multi-stop**; LOT tunggal tetap satu tombol **Print**.                                                                                                                     |
| Kartu **Edit itinerary** tidak tampil                              | LOT belum **Approved**, tidak punya stop, atau Anda bukan staf **LOT Origin**                                   | Periksa status dan **project asal** LOT; kartu ini untuk penugasan **LOT Origin**.                                                                                                                                 |
| Akses ditolak, atau halaman “tidak ditemukan”                      | Tautan atau nomor bukan milik data Anda, atau bukan bagian wewenang Anda                                        | Buka kembali dari **menu** dan daftar; jangan menebak tautan; pastikan memakai akun yang benar.                                                                                                                    |

### Eskalasi ke HR / tim dukungan

Hubungi **HR**, **IT**, atau **pengelola akses** di perusahaan Anda jika: menu tidak tampil padahal seharusnya bisa, status LOT tidak berubah setelah tindakan wajar, pesan di layar tidak ada di tabel di atas, atau Anda butuh koreksi data master (**Transportations**, **Accommodations**, **Projects**).

**Jangan** mengirim **password** lewat obrolan atau surel. Cukup sampaikan **username**, nomor **LOT** / **Travel Number**, waktu kejadian, dan cuplikan pesan error.

---

</div>
