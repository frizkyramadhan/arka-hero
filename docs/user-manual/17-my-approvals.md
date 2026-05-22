<div style="text-align: justify; text-justify: inter-word;">

# My Approvals

| **Versi** | **Tanggal** | **Revisi (ringkas)**                                                                                                                                                                                                                         |
| :-------- | :---------- | :------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 1.1       | 2026-05-22  | Filter **Status** (**All** / **Pending** / **Approved** / **Rejected**, default **Pending**); kolom **Status** (badge keputusan + giliran approval); **Review** / **View**; **Bulk Approve** & centang hanya saat **Pending**; **Sisa Cuti** sejajar **Total Days** pada detail **Leave Request**. |
| 1.0       | —           | Panduan awal: daftar **Pending Approvals**, filter jenis dokumen & tanggal, **Review**, **Bulk Approve**, panel **Approval Decision** dan **Approval Status**.                                                                               |

Panduan ini untuk menjelaskan kepada **pemberi persetujuan (approver)** dan akun yang memiliki **hak akses** melihat antrean serta riwayat dokumen yang memerlukan keputusan Anda di ARKA HERO. Anda akan mempelajari cara membuka daftar, menyaring data (termasuk status), meninjau rincian dokumen, serta menyetujui atau menolak lewat **Approval Decision**. Dokumen yang sudah Anda putuskan tetap dapat dibuka kembali dalam mode baca (**View**).

| **Istilah**                         | Arti singkat                                                                                                                                                                                                                                                                                                                                                                                                      |
| ----------------------------------- | ----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **My Approvals**                    | Menu sidebar ke daftar persetujuan; dapat menampilkan **badge** angka jumlah antrean yang masih **Pending**.                                                                                                                                                                                                                                                                                                      |
| **Approval Requests**               | Judul halaman daftar dan nama bagian di **breadcrumb** (misalnya setelah **Home**).                                                                                                                                                                                                                                                                                                                               |
| **Status** (filter)                 | Filter di kartu **Filters**: **All**, **Pending** (default), **Approved**, **Rejected** — menentukan baris mana yang ditampilkan di tabel.                                                                                                                                                                                                                                                                        |
| **Approval Requests** (kartu tabel) | Judul kartu berisi tabel daftar permintaan persetujuan.                                                                                                                                                                                                                                                                                                                                                            |
| **Approver**                        | Peran pengguna yang ditunjuk pada suatu dokumen untuk memberi keputusan persetujuan sesuai urutan aturan kantor.                                                                                                                                                                                                                                                                                                  |
| **Document Type**                   | Jenis dokumen di filter **Filters**; contoh nilai: **Official Travel** (perjalanan dinas/LOT), **Recruitment Request** (pengajuan rekrutmen/FPTK), **Leave Request** (cuti), **Flight Request** (permintaan tiket/penerbangan), **Letter of Guarantee (LG)** (surat penerbitan tiket), **Overtime Request** (lembur). Di kolom tabel, label dapat sedikit berbeda ejaan dengan opsi filter tetapi maksudnya sama. |
| **Document Number**                 | Nomor atau ringkasan identitas dokumen (tergantung jenis).                                                                                                                                                                                                                                                                                                                                                        |
| **Remarks**                         | Kolom keterangan tambahan di tabel (misalnya pemohon, posisi, atau ringkasan terkait dokumen).                                                                                                                                                                                                                                                                                                                    |
| **Submitted By** / **Submitted At** | Siapa yang mengajukan dan kapan diserahkan untuk persetujuan.                                                                                                                                                                                                                                                                                                                                                     |
| **Status** (kolom tabel)            | Badge keputusan **Anda** pada baris (**Pending**, **Approved**, **Rejected**) plus informasi giliran persetujuan dokumen (misalnya menunggu approver tertentu) dan progres langkah `(x/y)`.                                                                                                                                                                                                                     |
| **Review**                          | Tombol membuka halaman tinjauan dokumen yang masih **Pending** dan formulir keputusan.                                                                                                                                                                                                                                                                                                                          |
| **View**                            | Tombol membuka halaman detail dokumen yang sudah Anda setujui/tolak (hanya baca; tanpa **Approval Decision**).                                                                                                                                                                                                                                                                                                  |
| **Approval Decision**               | Panel memilih **Approve** atau **Reject** dan mengisi catatan — hanya tampil bila status keputusan Anda masih **Pending**.                                                                                                                                                                                                                                                                                       |
| **Your Decision**                   | Panel ringkasan keputusan Anda (**Approved** / **Rejected**) beserta catatan — tampil bila Anda membuka dokumen yang sudah diproses.                                                                                                                                                                                                                                                                             |
| **Approval Notes**                  | Catatan wajib sebelum mengirim keputusan.                                                                                                                                                                                                                                                                                                                                                                         |
| **Bulk Approve**                    | Menyetujui sekaligus beberapa baris yang dicentang (hanya persetujuan, bukan penolakan massal). Hanya tersedia saat filter **Status** = **Pending**.                                                                                                                                                                                                                                                             |
| **Sisa Cuti**                       | Pada detail **Leave Request**, saldo cuti tersisa pegawai untuk **Leave Period** yang sama dengan pengajuan; ditampilkan sejajar dengan **Total Days**.                                                                                                                                                                                                                                                         |

**Alamat:** `http://192.168.32.146:8080/approval/requests` — sesuaikan dengan server dan lingkungan perusahaan Anda.

---

## 1. Membuka **My Approvals** dan menyaring daftar (**Filters**)

### Langkah-langkah — daftar persetujuan dan mempersempit tabel

1. **Login** ke ARKA HERO.
2. Di sidebar, klik **My Approvals**. Jika ada **badge** angka di samping menu, itu perkiraan jumlah permintaan yang masih menunggu keputusan Anda (**Pending**).
3. Pastikan judul halaman menampilkan **Approval Requests** dan **breadcrumb** berisi **Home** → **Approval Requests**.
4. Pada kartu **Filters**:
    - **Status** — pilih **Pending** (default, antrean aktif), **Approved**, **Rejected**, atau **All** untuk melihat riwayat keputusan Anda.
    - **Document Type** — pilih jenis dokumen atau biarkan **All Types**.
    - **Date From** / **Date To** — batasi rentang tanggal bila perlu.
5. Klik **Apply Filters** agar tabel memuat ulang data.
6. Gunakan kotak pencarian bawaan tabel (biasanya di kanan atas area tabel) untuk mencari teks yang dikenali sistem; jika tidak ada hasil, muncul pesan seperti **No approval requests match your search criteria.**

<p align="center" id="my-approvals-list">
    <img
        src="images/my_approvals_pending_list.png"
        alt="Halaman Approval Requests Arka HERO: sidebar My Approvals aktif dengan badge antrean; kartu Filters berisi Status Pending Document Type Date From Date To Apply Filters; kartu Approval Requests dengan Bulk Approve kolom centang tabel Document Type Document Number Remarks Submitted By Submitted At Status badge Pending dengan teks menunggu approver dan progres langkah tombol Review pagination"
        style="max-width: 80%; width: 80%; height: auto;"
    />
</p>

**Catatan filter Status**

| Filter       | Isi tabel                                                                 | Centang / **Bulk Approve** |
| ------------ | ------------------------------------------------------------------------- | -------------------------- |
| **Pending**  | Hanya baris yang belum Anda putuskan                                      | Tampil                     |
| **Approved** | Hanya baris yang sudah Anda setujui                                       | Disembunyikan              |
| **Rejected** | Hanya baris yang sudah Anda tolak                                         | Disembunyikan              |
| **All**      | Semua keputusan Anda (Pending, Approved, Rejected)                        | Disembunyikan              |

**Catatan:** Menu **My Approvals** hanya tampil jika akun Anda diberi role approver. Jika tidak ada, hubungi **administrator** (bukan semua karyawan otomatis menjadi approver).

---

## 2. Meninjau dan memutuskan satu dokumen — **Review** / **View**

### Langkah-langkah — dari tabel ke halaman detail

1. Pada kartu **Approval Requests**, baca kolom **Document Type**, **Document Number**, **Remarks**, **Submitted By**, **Submitted At**, dan **Status** (badge keputusan Anda plus informasi giliran persetujuan dokumen).
2. Untuk baris **Pending**, klik **Review**. Untuk baris **Approved** atau **Rejected**, klik **View**. <a href="#my-approvals-review">Lihat gambar</a>.
3. Di halaman tinjauan, baca rincian dokumen (berbeda per jenis: perjalanan dinas, cuti, rekrutmen, dan lain-lain).
4. **Bila status keputusan Anda masih Pending** — di sisi kanan, buka panel **Approval Decision**:
    - Pada bagian **Choose Your Decision**, klik **Approve** atau **Reject**. Tanpa memilih salah satu, tombol **Submit Decision** tetap tidak aktif.
    - Isi **Approval Notes** (wajib, bertanda merah).
    - Klik **Submit Decision** untuk mengirim. Gunakan **Cancel** jika ingin kembali ke daftar tanpa mengirim.
5. **Bila Anda sudah memutuskan** — panel **Your Decision** menampilkan ringkasan (**You approved this request** / **You rejected this request**) dan catatan Anda; tombol **Back to List** mengembalikan ke daftar.
6. Setelah mengirim keputusan baru, Anda biasanya dikembalikan ke daftar **My Approvals** dengan pesan sukses singkat.

<p align="center" id="my-approvals-review">
    <img
        src="images/my_approvals_review_decision.png"
        alt="Halaman Review Leave Request: header Leave Request badge Pending Approval; kiri kartu Leave Request Information dengan Total Days dan Sisa Cuti sejajar Employee Information; kanan kartu Approval Decision Choose Your Decision Approve Reject Approval Notes Submit Decision Cancel; kartu Approval Status Document Submitter Approval Flow"
        style="max-width: 80%; width: 80%; height: auto;"
    />
</p>

### Khusus **Leave Request** — **Total Days** dan **Sisa Cuti**

Pada kartu **Leave Request Information**, baris **Total Days** menampilkan dua kolom sejajar:

| **Total Days** | **Sisa Cuti** |
| -------------- | ------------- |
| Jumlah hari yang diajukan | Saldo cuti tersisa pegawai |

**Sisa Cuti** dihitung dari entitlement yang **Leave Period**-nya sama dengan pengajuan (bukan hanya dari tanggal mulai/selesai cuti). Approver dapat membandingkan jumlah hari diajukan dengan sisa saldo sebelum menyetujui.

**Catatan:** Jika dokumen memakai **urutan persetujuan bertingkat**, sistem dapat menolak keputusan Anda sampai approver pada tingkat sebelumnya selesai — pesan di layar menjelaskan bahwa persetujuan sebelumnya harus diselesaikan terlebih dahulu.

---

## 3. Menyetujui banyak dokumen sekaligus — **Bulk Approve**

### Langkah-langkah — centang lalu konfirmasi

1. Pastikan filter **Status** = **Pending** (kolom centang dan tombol **Bulk Approve** hanya tampil pada filter ini).
2. Di tabel **Approval Requests**, centang kotak di baris yang ingin disetujui (atau gunakan kotak centang di header untuk memilih semua yang tampil pada halaman saat ini).
3. Klik **Bulk Approve**.
4. Baca jendela konfirmasi (**Bulk Approval Confirmation**). Jika setuju, lanjutkan dengan opsi konfirmasi (misalnya **Yes, Approve All**); **Cancel** membatalkan.
5. Tunggu hingga proses selesai; sistem dapat menampilkan ringkasan jika sebagian berhasil dan sebagian gagal.
6. Tabel akan dimuat ulang; baris yang sudah diproses tidak lagi muncul pada filter **Pending**.

**Catatan:** **Bulk Approve** hanya untuk **menyetujui** dokumen terpilih yang memang sudah siap diproses oleh Anda (termasuk aturan urutan). Dokumen yang gagal biasanya tetap ada di daftar dengan alasan terkait urutan atau status.

---

## 4. Panel status — **Approval Status**

Di halaman **Review** / **View**, kartu **Approval Status** menampilkan ringkasan seperti **Document Submitter** dan jejak approver lain bila tersedia, sehingga Anda dapat memverifikasi siapa yang mengajukan dan siapa saja yang sudah memutuskan.

---

## Kesalahan & bantuan (end user)

| Gejala / pesan (contoh)                                         | Kemungkinan penyebab                                                            | Apa yang bisa dicoba                                                                 |
| --------------------------------------------------------------- | ------------------------------------------------------------------------------- | ------------------------------------------------------------------------------------ |
| Menu **My Approvals** tidak ada                                 | Akun tidak punya izin melihat antrean persetujuan                               | Minta **administrator** menambahkan hak akses yang sesuai peran Anda.                |
| **You are not authorized to approve this request**              | Bukan Anda sebagai approver untuk baris itu, atau tautan bukan milik tugas Anda | Buka lagi dari **My Approvals**; jangan memakai tautan lama dari orang lain.         |
| **This request has already been processed**                     | Keputusan sudah pernah dikirim (saat mencoba approve/reject ulang)              | Buka baris lewat **View** untuk melihat riwayat; ubah filter **Status** bila perlu.  |
| **Previous approvals must be completed first**                  | Urutan persetujuan: giliran Anda belum jalan                                    | Tunggu approver sebelumnya; koordinasi internal bila mendesak.                       |
| **Submit Decision** tidak bisa diklik                           | Belum memilih **Approve** atau **Reject**                                       | Klik salah satu tombol keputusan, lalu isi **Approval Notes**.                       |
| **No Selection** / minta pilih permintaan saat **Bulk Approve** | Tidak ada baris yang dicentang                                                  | Centang minimal satu baris; pastikan filter **Status** = **Pending**.                |
| **Bulk Approve** / centang tidak tampil                         | Filter **Status** bukan **Pending**                                             | Pilih **Pending** di filter **Status**, lalu **Apply Filters**.                      |
| Daftar kosong / **No approval requests found**                  | Tidak ada data untuk filter aktif                                               | Ubah **Status** (misalnya **All**) atau kosongkan **Document Type** / tanggal.       |
| **Sisa Cuti: N/A** pada detail cuti                             | Entitlement untuk **Leave Period** pengajuan tidak ditemukan                    | Verifikasi data entitlement pegawai dengan **HR** sebelum memutuskan.                |
| Peringatan **Session Expired** saat tabel memuat                | Sesi login habis                                                                | **Login** ulang dan buka kembali **My Approvals**.                                   |

### Menghubungi administrator

Hubungi **administrator** (atau **IT** / **HR**) jika izin seharusnya ada tetapi menu hilang, status dokumen tidak berubah setelah Anda yakin sudah mengirim keputusan, atau pesan di layar tidak tercakup di tabel di atas.

**Jangan** mengirim **password**. Cukup sampaikan **username** Anda, jenis dokumen (**Document Type**), nomor atau petunjuk dokumen yang terlihat di layar, waktu kejadian, dan kutipan pesan singkat dari aplikasi.

---

</div>
