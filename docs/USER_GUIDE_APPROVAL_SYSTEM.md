# User Guide: Sistem Approval ARKA HERO

**Versi:** 1.0  
**Tanggal:** 15 Januari 2025  
**Untuk:** Semua User yang Bertugas sebagai Approver

---

## Daftar Isi

1. [Pengenalan Sistem Approval](#1-pengenalan-sistem-approval)
2. [Jenis Dokumen yang Memerlukan Approval](#2-jenis-dokumen-yang-memerlukan-approval)
3. [Mengakses Approval Requests](#3-mengakses-approval-requests)
4. [Memproses Approval Request](#4-memproses-approval-request)
5. [Memahami Approval Stages](#5-memahami-approval-stages)
6. [Sequential Approval Flow](#6-sequential-approval-flow)
7. [Tips dan Best Practices](#7-tips-dan-best-practices)
8. [Troubleshooting](#8-troubleshooting)

---

## 1. Pengenalan Sistem Approval

### Apa itu Sistem Approval?

Sistem Approval di ARKA HERO adalah fitur yang memungkinkan dokumen-dokumen penting (seperti Official Travel, Recruitment Request, dan Leave Request) untuk mendapatkan persetujuan dari pihak-pihak yang berwenang sebelum dokumen tersebut dapat digunakan atau diproses lebih lanjut.

### Bagaimana Sistem Ini Bekerja?

1. **User membuat dokumen** (misalnya: membuat Official Travel atau Recruitment Request)
2. **Dokumen disubmit** untuk mendapatkan approval
3. **Sistem otomatis membuat approval requests** untuk approver yang sesuai berdasarkan konfigurasi
4. **Approver menerima notifikasi** dan dapat melihat daftar approval requests
5. **Approver memproses approval** dengan memilih Approve atau Reject
6. **Dokumen otomatis berubah status** setelah semua approval selesai

### Konsep Penting

- **Approval Stage**: Konfigurasi yang menentukan siapa saja yang harus approve dokumen tertentu
- **Approval Request**: Permintaan approval yang dikirim ke approver tertentu
- **Sequential Approval**: Approval yang harus dilakukan secara berurutan (step 1 harus selesai dulu sebelum step 2)
- **Parallel Approval**: Approval yang dapat dilakukan secara bersamaan oleh beberapa approver

---

## 2. Jenis Dokumen yang Memerlukan Approval

Sistem approval di ARKA HERO mendukung 3 jenis dokumen:

### 2.1 Official Travel (Surat Perjalanan Dinas)

**Deskripsi**: Dokumen untuk mengajukan perjalanan dinas karyawan.

**Informasi yang Ditampilkan**:
- Nomor surat perjalanan dinas
- Tujuan perjalanan (destination)
- Tujuan perjalanan (purpose)
- Durasi perjalanan
- Tanggal keberangkatan
- Informasi traveler (NIK, Nama, Posisi, Department, Project)
- Informasi followers (jika ada)
- Transportasi dan akomodasi

**Kapan Approval Diperlukan**: Setiap kali ada karyawan yang akan melakukan perjalanan dinas.

### 2.2 Recruitment Request (FPTK - Formulir Permintaan Tenaga Kerja)

**Deskripsi**: Dokumen untuk mengajukan permintaan rekrutmen karyawan baru.

**Informasi yang Ditampilkan**:
- Nomor request
- Department dan Project
- Posisi yang dibutuhkan
- Level jabatan
- Jumlah yang dibutuhkan (required quantity)
- Tanggal dibutuhkan (required date)
- Tipe employment
- Request reason (alasan permintaan)
- Job description dan requirements
- Theory test requirement (jika diperlukan)

**Kapan Approval Diperlukan**: Setiap kali ada kebutuhan untuk merekrut karyawan baru.

**Catatan Khusus**: Approval flow untuk Recruitment Request dapat berbeda tergantung pada **Request Reason**:
- **Replacement - Resign**: Penggantian karena karyawan resign/terminated/end of contract
- **Replacement - Promotion**: Penggantian karena karyawan promosi/mutasi/demotion
- **Additional - Workplan**: Tambahan karena rencana kerja
- **Other**: Alasan lainnya

### 2.3 Leave Request (Permintaan Cuti)

**Deskripsi**: Dokumen untuk mengajukan cuti karyawan.

**Informasi yang Ditampilkan**:
- Informasi karyawan (NIK, Nama, Posisi, Department, Project)
- Jenis cuti (Leave Type)
- Tanggal mulai cuti (Start Date)
- Tanggal selesai cuti (End Date)
- Total hari cuti (Total Days)
- Tanggal kembali kerja (Back to Work Date)
- Periode cuti (Leave Period)
- Tanggal request dibuat

**Kapan Approval Diperlukan**: Setiap kali karyawan mengajukan cuti.

**Catatan Khusus**: Approval flow untuk Leave Request mengikuti hierarki level jabatan:
- Karyawan level biasa: Approval dari atasan maksimal 2 level di atasnya (tidak melebihi Manager)
- Manager: Approval hanya dari Director
- Director: Mengikuti konfigurasi approval stages

---

## 3. Mengakses Approval Requests

### 3.1 Cara Mengakses Halaman Approval Requests

1. **Login ke aplikasi ARKA HERO**
2. **Klik menu "Approval Requests"** di sidebar (biasanya ada badge dengan jumlah pending requests)
3. **Halaman Approval Requests akan menampilkan** semua dokumen yang menunggu approval dari Anda

### 3.2 Memahami Tampilan Approval Requests

Halaman Approval Requests menampilkan tabel dengan kolom-kolom berikut:

| Kolom | Deskripsi |
|-------|-----------|
| **Checkbox** | Untuk memilih multiple requests (untuk bulk approve) |
| **Document Type** | Jenis dokumen (Official Travel, Recruitment Request, atau Leave Request) |
| **Document Number** | Nomor atau identifikasi dokumen |
| **Remarks** | Informasi tambahan (misalnya: NIK - Nama untuk Official Travel/Leave Request, atau Position - Project untuk Recruitment Request) |
| **Submitted By** | Nama user yang membuat dokumen |
| **Submitted At** | Tanggal dan waktu dokumen disubmit |
| **Current Approval** | Status approval saat ini dan progress (misalnya: "Pending (2/3)" berarti 2 dari 3 approval sudah selesai) |
| **Action** | Tombol "Review" untuk melihat detail dan memproses approval |

### 3.3 Filter Approval Requests

Anda dapat memfilter approval requests dengan:

1. **Document Type**: Filter berdasarkan jenis dokumen
   - All Types (semua jenis)
   - Official Travel
   - Recruitment Request
   - Leave Request

2. **Date Range**: Filter berdasarkan tanggal submit
   - Date From: Tanggal mulai
   - Date To: Tanggal akhir

3. **Klik tombol "Apply Filters"** untuk menerapkan filter

### 3.4 Bulk Approve (Approve Multiple Requests)

Jika Anda memiliki banyak approval requests yang ingin diapprove sekaligus:

1. **Centang checkbox** pada request yang ingin diapprove
2. **Atau centang "Select All"** untuk memilih semua request di halaman saat ini
3. **Klik tombol "Bulk Approve"** di pojok kanan atas tabel
4. **Konfirmasi** bahwa Anda ingin approve semua request yang dipilih
5. **Sistem akan memproses** semua request yang dapat diapprove (request yang tidak dapat diapprove akan dilewati dengan pesan error)

**Catatan Penting**: 
- Bulk approve hanya akan memproses request yang **sudah bisa diproses** (tidak menunggu approval sebelumnya)
- Request yang masih menunggu approval sebelumnya akan dilewati
- Setiap request yang berhasil diapprove akan menggunakan **remarks yang sama** (jika Anda mengisi remarks)

---

## 4. Memproses Approval Request

### 4.1 Membuka Detail Approval Request

1. **Klik tombol "Review"** pada request yang ingin Anda proses
2. **Halaman detail akan menampilkan**:
   - Informasi lengkap dokumen
   - Form untuk memproses approval
   - Status approval flow (siapa saja yang harus approve dan statusnya)

### 4.2 Memahami Tampilan Detail Approval Request

Halaman detail terbagi menjadi 2 bagian utama:

#### A. Bagian Kiri: Informasi Dokumen

Menampilkan informasi lengkap dokumen sesuai jenisnya:
- **Official Travel**: Detail perjalanan, traveler, followers, transportasi, akomodasi
- **Recruitment Request**: Detail FPTK, job description, requirements
- **Leave Request**: Detail cuti, informasi karyawan

#### B. Bagian Kanan: Approval Form dan Status

**1. Approval Decision Card**
   - Tombol **Approve** (hijau) dan **Reject** (merah)
   - Form untuk mengisi **Approval Notes** (wajib diisi)
   - Tombol **Submit Decision**

**2. Approval Status Card**
   - Informasi **Document Submitter** (siapa yang membuat dokumen)
   - **Approval Flow** (urutan approval dan statusnya)

### 4.3 Memproses Approval

#### Langkah-langkah:

1. **Baca informasi dokumen** dengan seksama di bagian kiri
2. **Klik tombol "Approve" atau "Reject"** sesuai keputusan Anda
   - Tombol yang dipilih akan ter-highlight
3. **Isi Approval Notes** (wajib diisi)
   - Jelaskan alasan keputusan Anda
   - Jika approve: Jelaskan persetujuan atau kondisi khusus
   - Jika reject: Jelaskan alasan penolakan dengan jelas
4. **Klik tombol "Submit Decision"** untuk menyimpan keputusan
5. **Konfirmasi** keputusan Anda pada popup konfirmasi

#### Contoh Approval Notes:

**Untuk Approve:**
```
Disetujui. Perjalanan dinas ini sesuai dengan rencana kerja departemen. 
Mohon untuk melaporkan hasil perjalanan setelah kembali.
```

**Untuk Reject:**
```
Ditolak. Budget untuk perjalanan dinas bulan ini sudah habis. 
Mohon untuk mengajukan ulang di bulan berikutnya atau mencari alternatif solusi.
```

### 4.4 Memahami Approval Flow Status

Di bagian kanan bawah, Anda akan melihat **Approval Flow** yang menampilkan:

- **Step Number**: Nomor urut approval (1, 2, 3, dst)
- **Approver Name**: Nama approver untuk step tersebut
- **Status Badge**: 
  - 🟡 **Pending**: Masih menunggu approval
  - 🟢 **Approved**: Sudah diapprove
  - 🔴 **Rejected**: Ditolak
- **Remarks**: Catatan dari approver (jika sudah diproses)
- **Current Step Indicator**: 
  - "Your turn to review" (biru): Ini adalah giliran Anda untuk approve
  - "Waiting for previous approvals" (kuning): Masih menunggu approval sebelumnya

### 4.5 Status Approval Request

#### Status yang Mungkin Anda Lihat:

1. **Pending (Menunggu)**
   - Approval request masih menunggu untuk diproses
   - Anda dapat memproses jika ini adalah giliran Anda

2. **Waiting for Previous Approvals**
   - Approval request masih menunggu approver sebelumnya
   - Anda belum bisa memproses sampai approval sebelumnya selesai

3. **Approved (Disetujui)**
   - Approval request sudah diapprove
   - Tidak dapat diubah lagi

4. **Rejected (Ditolak)**
   - Approval request ditolak oleh salah satu approver
   - Semua approval request untuk dokumen ini akan ditutup
   - Dokumen akan berstatus "Rejected"

---

## 5. Memahami Approval Stages

### 5.1 Apa itu Approval Stage?

**Approval Stage** adalah konfigurasi yang menentukan:
- **Siapa** yang harus approve dokumen tertentu
- **Urutan** approval (step 1, 2, 3, dst)
- **Kondisi** kapan approval stage ini berlaku (project, department, request reason)

### 5.2 Komponen Approval Stage

Setiap Approval Stage terdiri dari:

1. **Approver**: User yang bertugas sebagai approver
2. **Document Type**: Jenis dokumen (Official Travel, Recruitment Request, atau Leave Request)
3. **Approval Order**: Urutan approval (1, 2, 3, dst)
4. **Project**: Project mana yang menggunakan approval stage ini
5. **Department**: Department mana yang menggunakan approval stage ini
6. **Request Reason** (khusus Recruitment Request): Alasan permintaan yang menggunakan approval stage ini

### 5.3 Contoh Approval Stage

**Contoh 1: Official Travel untuk Project 000H, Department HR**

```
Step 1: Manager HR (Order 1)
Step 2: Division Manager (Order 2)
Step 3: Director (Order 3)
```

**Contoh 2: Recruitment Request untuk Project 000H, Department HR, Request Reason: Replacement - Resign**

```
Step 1: HCS Division Manager (Order 1)
```

**Contoh 3: Recruitment Request untuk Project 000H, Department HR, Request Reason: Additional - Workplan**

```
Step 1: Operational GM (Order 1)
Step 2: HCS Division Manager (Order 2)
```

### 5.4 Sequential vs Parallel Approval

#### Sequential Approval (Berurutan)

Approval yang harus dilakukan secara berurutan. Step 2 tidak bisa diproses sebelum Step 1 selesai.

**Contoh:**
```
Step 1: Manager A → Harus approve dulu
Step 2: Director B → Baru bisa approve setelah Step 1 selesai
```

#### Parallel Approval (Bersamaan)

Beberapa approver dengan approval order yang sama dapat approve secara bersamaan.

**Contoh:**
```
Step 1: Manager A dan Manager B (keduanya Order 1) → Bisa approve bersamaan
Step 2: Director C → Baru bisa approve setelah KEDUA Manager A dan B selesai
```

---

## 6. Sequential Approval Flow

### 6.1 Bagaimana Sequential Approval Bekerja?

Sequential approval memastikan bahwa approval dilakukan secara berurutan. Sistem akan:

1. **Membuat approval requests** untuk semua approver sesuai approval order
2. **Hanya mengaktifkan approval request** untuk approver dengan order terendah yang belum selesai
3. **Mengaktifkan approval request berikutnya** setelah approval sebelumnya selesai
4. **Menyelesaikan proses** setelah semua approval selesai

### 6.2 Contoh Alur Sequential Approval

**Skenario**: Official Travel dengan 3 step approval

```
Dokumen disubmit
    ↓
Step 1: Manager HR (Order 1) → Status: Pending, Can Process: ✅
    ↓ (Manager HR approve)
Step 2: Division Manager (Order 2) → Status: Pending, Can Process: ✅
    ↓ (Division Manager approve)
Step 3: Director (Order 3) → Status: Pending, Can Process: ✅
    ↓ (Director approve)
Dokumen Status: Approved ✅
```

### 6.3 Apa yang Terjadi Jika Approval Ditolak?

Jika salah satu approver menolak (reject):

1. **Dokumen langsung berstatus "Rejected"**
2. **Semua approval request yang masih pending akan ditutup**
3. **Approver berikutnya tidak perlu lagi memproses**
4. **User yang membuat dokumen akan mendapat notifikasi**

**Contoh:**
```
Step 1: Manager HR → Approve ✅
Step 2: Division Manager → Reject ❌
    ↓
Dokumen Status: Rejected
Step 3: Director → Tidak perlu memproses (request ditutup)
```

### 6.4 Kapan Saya Bisa Memproses Approval?

Anda dapat memproses approval request jika:

1. **Approval request adalah untuk Anda** (approver_id sesuai dengan user Anda)
2. **Status masih "Pending"** (belum diproses)
3. **Semua approval sebelumnya sudah selesai** (jika approval order > 1)

**Jika approval order Anda adalah 1:**
- Anda bisa langsung memproses (tidak perlu menunggu)

**Jika approval order Anda > 1:**
- Anda harus menunggu semua approval dengan order lebih kecil selesai
- Sistem akan menampilkan pesan "Waiting for previous approvals" jika belum bisa diproses

---

## 7. Tips dan Best Practices

### 7.1 Tips untuk Approver

1. **Baca dokumen dengan seksama** sebelum memutuskan
   - Pastikan semua informasi sudah lengkap dan benar
   - Periksa apakah dokumen sesuai dengan kebijakan perusahaan

2. **Isi Approval Notes dengan jelas**
   - Jelaskan alasan keputusan Anda
   - Jika approve dengan kondisi, sebutkan kondisinya
   - Jika reject, jelaskan alasan yang konstruktif

3. **Proses approval tepat waktu**
   - Jangan menunda-nunda approval request
   - Ingat bahwa approval Anda mungkin menunggu approval berikutnya

4. **Gunakan filter** untuk menemukan approval request yang spesifik
   - Filter berdasarkan document type jika Anda hanya ingin melihat jenis tertentu
   - Filter berdasarkan tanggal untuk melihat request dalam periode tertentu

5. **Gunakan Bulk Approve dengan hati-hati**
   - Pastikan semua request yang dipilih memang layak untuk diapprove
   - Periksa kembali sebelum melakukan bulk approve

### 7.2 Best Practices

1. **Komunikasi yang Jelas**
   - Jika Anda menolak dokumen, berikan alasan yang jelas dan konstruktif
   - Jika Anda approve dengan kondisi, sebutkan kondisinya di approval notes

2. **Konsistensi**
   - Gunakan standar yang sama untuk approval sejenis
   - Konsisten dalam memberikan approval notes

3. **Responsiveness**
   - Proses approval request dalam waktu yang wajar
   - Jika Anda akan cuti atau tidak bisa memproses, informasikan ke admin untuk mengatur delegasi

4. **Review Approval Flow**
   - Perhatikan approval flow untuk memahami siapa saja yang terlibat
   - Jika ada yang tidak sesuai, hubungi admin

### 7.3 Hal-hal yang Perlu Diperhatikan

1. **Approval tidak dapat dibatalkan**
   - Setelah Anda submit approval atau rejection, keputusan tidak dapat diubah
   - Pastikan keputusan Anda sudah final sebelum submit

2. **Approval Notes wajib diisi**
   - Sistem tidak akan mengizinkan submit tanpa approval notes
   - Isi dengan informasi yang relevan dan jelas

3. **Sequential approval harus berurutan**
   - Anda tidak bisa memproses approval jika approval sebelumnya belum selesai
   - Sabar menunggu giliran Anda

4. **Rejection akan menghentikan proses**
   - Jika Anda reject, semua approval berikutnya akan dibatalkan
   - Pastikan rejection Anda memang diperlukan

---

## 8. Troubleshooting

### 8.1 Saya Tidak Bisa Memproses Approval Request

**Masalah**: Tombol "Submit Decision" tidak aktif atau ada pesan error.

**Solusi**:
1. **Pastikan Anda sudah memilih Approve atau Reject**
   - Klik salah satu tombol decision terlebih dahulu
2. **Pastikan Approval Notes sudah diisi**
   - Approval notes wajib diisi sebelum submit
3. **Periksa apakah ini giliran Anda**
   - Jika approval order > 1, pastikan approval sebelumnya sudah selesai
   - Lihat di Approval Flow, apakah ada pesan "Waiting for previous approvals"

### 8.2 Approval Request Tidak Muncul di Daftar Saya

**Masalah**: Dokumen sudah disubmit tapi tidak muncul di halaman Approval Requests.

**Solusi**:
1. **Periksa filter**
   - Pastikan filter tidak menyembunyikan request yang Anda cari
   - Coba reset filter atau pilih "All Types"
2. **Periksa apakah request sudah diproses**
   - Request yang sudah diproses tidak akan muncul di daftar pending
3. **Hubungi admin** jika masalah masih terjadi

### 8.3 Saya Tidak Bisa Melihat Detail Dokumen

**Masalah**: Halaman detail tidak menampilkan informasi dokumen.

**Solusi**:
1. **Refresh halaman**
   - Tekan F5 atau klik tombol refresh browser
2. **Periksa koneksi internet**
   - Pastikan koneksi internet stabil
3. **Hubungi IT support** jika masalah masih terjadi

### 8.4 Bulk Approve Tidak Berhasil

**Masalah**: Beberapa request tidak berhasil diapprove saat bulk approve.

**Solusi**:
1. **Periksa pesan error**
   - Sistem akan menampilkan pesan untuk request yang gagal
   - Biasanya karena request masih menunggu approval sebelumnya
2. **Proses request yang gagal secara manual**
   - Buka detail request yang gagal
   - Proses satu per satu setelah approval sebelumnya selesai

### 8.5 Approval Flow Tidak Sesuai

**Masalah**: Approval flow yang ditampilkan tidak sesuai dengan yang seharusnya.

**Solusi**:
1. **Hubungi admin** untuk memeriksa konfigurasi Approval Stage
2. **Jangan memproses approval** jika Anda ragu dengan flow yang ditampilkan

---

## 9. Kontak dan Bantuan

Jika Anda mengalami masalah atau memiliki pertanyaan tentang sistem approval:

1. **Hubungi Admin HR** untuk pertanyaan tentang konfigurasi approval stages
2. **Hubungi IT Support** untuk masalah teknis atau bug
3. **Baca dokumentasi** ini kembali untuk referensi

---

## 10. Glosarium

| Istilah | Deskripsi |
|---------|-----------|
| **Approval Stage** | Konfigurasi yang menentukan siapa yang harus approve dokumen tertentu |
| **Approval Request** | Permintaan approval yang dikirim ke approver tertentu |
| **Approval Order** | Urutan approval (1, 2, 3, dst) |
| **Sequential Approval** | Approval yang harus dilakukan secara berurutan |
| **Parallel Approval** | Approval yang dapat dilakukan secara bersamaan |
| **Pending** | Status approval yang masih menunggu untuk diproses |
| **Approved** | Status approval yang sudah disetujui |
| **Rejected** | Status approval yang ditolak |
| **Approval Notes** | Catatan dari approver yang menjelaskan keputusan approval |
| **Document Type** | Jenis dokumen (Official Travel, Recruitment Request, Leave Request) |
| **Request Reason** | Alasan permintaan untuk Recruitment Request |

---

**Dokumen ini akan diperbarui secara berkala. Pastikan Anda membaca versi terbaru.**

**Terima kasih telah menggunakan Sistem Approval ARKA HERO!**

