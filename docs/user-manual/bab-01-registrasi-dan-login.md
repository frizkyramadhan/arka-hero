# Panduan Pengguna Arka HERO — Registrasi & Login

**Alamat aplikasi:** `http://192.168.32.146:8080`  
Dokumen ini memakai bahasa Indonesia yang ringan. Istilah asing dari antarmuka aplikasi **tetap ditulis dalam bahasa Inggris** seperti di layar, lalu diberi penjelasan singkat.

---

## Istilah penting (glosarium singkat)

| Istilah (Inggris) | Arti singkat                                                                               |
| ----------------- | ------------------------------------------------------------------------------------------ |
| **URL**           | Alamat situs di internet / jaringan. Contoh: `http://192.168.32.146:8080/login`.           |
| **Browser**       | Program untuk membuka situs, misalnya Chrome, Edge, Firefox.                               |
| **Register**      | Mendaftar / membuat akun baru di sistem.                                                   |
| **Login**         | Masuk ke sistem dengan username atau email dan password.                                   |
| **Username**      | Nama pengguna unik untuk login (bukan nama lengkap).                                       |
| **Email**         | Alamat surat elektronik. Di aplikasi ini, jika dipakai harus berdomain `@arka.co.id`.      |
| **Password**      | Kata sandi rahasia untuk mengamankan akun.                                                 |
| **Sign In**       | Tombol untuk **masuk** setelah mengisi login dan password (sama fungsinya dengan “Login”). |
| **Full name**     | Nama lengkap Anda (sesuai yang ingin tampil di sistem).                                    |
| **IT**            | Tim teknologi informasi yang mengurus server dan aktivasi akun.                            |

---

## 1. Registrasi (Register) — membuat akun baru

**Register** artinya **mendaftar** agar punya akun di Arka HERO. Setelah formulir berhasil dikirim, akun **belum otomatis bisa dipakai login**; tim **IT** harus **mengaktifkan** akun Anda terlebih dahulu.

### Langkah-langkah

1. Buka **browser**, lalu ketik atau tempel **URL** berikut di bilah alamat:  
   **`http://192.168.32.146:8080/register`**
2. Isi kolom sesuai petunjuk di bawah.
3. Klik tombol **Register**.

### Isian formulir

| Di layar (Inggris)               | Penjelasan                                                                                                                       |
| -------------------------------- | -------------------------------------------------------------------------------------------------------------------------------- |
| **Full name**                    | Nama lengkap Anda. Wajib diisi.                                                                                                  |
| **Username**                     | Wajib. Minimal 3 karakter. Hanya huruf, angka, tanda hubung (`-`), dan garis bawah (`_`). Tidak boleh sama dengan pengguna lain. |
| **Email (Optional) @arka.co.id** | Opsional. Jika diisi, harus email valid dan berakhiran **`@arka.co.id`**. Boleh dikosongkan.                                     |
| **Password**                     | Wajib. Minimal **5 karakter**. Simpan baik-baik; jangan dibagikan.                                                               |

Setelah sukses, halaman akan mengarah ke **Login** dan menampilkan pesan agar Anda **menghubungi IT** untuk **mengaktifkan akun**.

### Tampilan halaman Register

![Halaman Register — formulir pendaftaran Arka HERO](images/register.png)

_Gambar: contoh tampilan halaman **Register** di alamat `http://192.168.32.146:8080/register`._

### Jika ada pesan salah (error)

- **Username** sudah dipakai orang lain → ganti **username** lain.
- **Username** tidak sesuai aturan → periksa huruf, angka, `-`, `_`, minimal 3 karakter.
- **Email** salah format, sudah terdaftar, atau bukan `@arka.co.id` → perbaiki atau kosongkan email.
- **Password** kurang dari 5 karakter → perpanjang **password**.

---

## 2. Login — masuk ke aplikasi

**Login** artinya **masuk** ke Arka HERO setelah akun **sudah diaktifkan** oleh **IT**. Tanpa aktivasi, login akan gagal meskipun **password** benar.

### Langkah-langkah

1. Buka **URL**: **`http://192.168.32.146:8080/login`**  
   (Anda juga bisa klik **Login!** dari halaman Register jika ada.)
2. Pada kolom **Username or Email**, isi salah satu:
    - **Username** yang Anda daftarkan, **atau**
    - **Email** dengan akhiran **`@arka.co.id`** (jika Anda punya dan sudah terdaftar).
3. Isi **Password**.
4. Klik **Sign In** untuk masuk.

### Tampilan halaman Login

![Halaman Login — kolom Username or Email dan Password, tombol Sign In](images/login.png)

_Gambar: contoh tampilan halaman **Login** di alamat `http://192.168.32.146:8080/login`._

### Setelah berhasil

Anda akan masuk ke halaman utama aplikasi (biasanya alamat **`http://192.168.32.146:8080/`**).

### Jika login gagal

- Pastikan **username** / **email** dan **password** benar (perhatikan huruf besar-kecil).
- Pastikan akun sudah **diaktifkan** oleh **IT** (ingat pesan setelah **Register**).
- Jika login pakai **email**, pastikan domain **`@arka.co.id`** sesuai aturan aplikasi.
- Jika tetap gagal, hubungi **IT** dan sebutkan **username** Anda (jangan kirim **password** lewat chat yang tidak aman).

---

## Ringkasan alamat (URL)

| Kegiatan                | URL lengkap                           |
| ----------------------- | ------------------------------------- |
| Register                | `http://192.168.32.146:8080/register` |
| Login                   | `http://192.168.32.146:8080/login`    |
| Beranda (setelah login) | `http://192.168.32.146:8080/`         |

---

_Bagian ini dapat diekspor ke PDF (misalnya dari Markdown) atau digabung dengan bab berikutnya dalam satu dokumen._
