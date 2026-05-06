# Prompt generik — User manual ARKA HERO (Markdown)

Salin **seluruh isi bagian “Instruksi untuk AI”** di bawah ke chat (tambahkan **input bab** di bagian akhir). Sesuaikan nama file output jika perlu.

---

## Instruksi untuk AI

Anda adalah penulis dokumentasi produk internal. Hasilkan **satu file Markdown** panduan pengguna untuk aplikasi **ARKA HERO**, dengan narasi **Bahasa Indonesia** yang jelas. **Label antarmuka** (menu, tombol, judul halaman, nama field di layar) **tepat seperti di aplikasi (biasanya English)** — tebalkan label tersebut dengan `**...**` saat dijelaskan.

### Acuan dokumen (gaya aktual di repo)

Selaraskan struktur dan kedalaman dengan bab-bab berikut **sesuai kompleksitas topik**:

| Referensi                       | Gunakan sebagai contoh untuk…                                                                                                                                                                                                                                                                                                |
| :------------------------------ | :--------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **`10-official-travel.md`**     | Bab multi-audiens (HR vs self-service), dashboard dengan sub-poin penjelasan metrik, form panjang dipecah **`**1.**`…`**10.**`**, gambar per blok, tautan internal antar subjudul, `---` antar bagian besar.                                                                                                                 |
| **`11-leave-management.md`**    | Modul sangat dalam: **`### 2.1` / `2.2` / `2.3`** di bawah `## 2`, tabel referensi (setup, **Import Validation Errors**), blok **`**Penjelasan singkat —**`** untuk menyalin struktur jendela/modal, **keterangan gambar** di bawah `<img>`, rujukan silang antar bagian (**bagian 3 (HR), langkah …**) dengan anchor `#id`. |
| **`08-employee-management.md`** | **`## 1.` Ringkasan menu** awal bila banyak entri sidebar, wizard **Add Employee** per tab/step berurutan, beberapa gambar beruntun untuk satu alur (mis. modal setelah tabel), **Kesalahan & bantuan** sebagai section bernomor terakhir.                                                                                   |

Gabungkan pola di atas secara wajar: bab sederhana tidak perlu sub-nomor `2.1`; bab sekompleks **Leave** memang layak memakai hierarki dan tabel rujukan.

### 1) Struktur & penomoran

1. **H1** — judul topik saja (tanpa nomor file, tanpa path repo).
2. **Pembuka** — 1–4 kalimat: **peran pembaca** (HR / karyawan / campuran), **cakupan**, dan jika relevan bahwa label UI mengikuti **bahasa Inggris** seperti di app. Untuk modul campuran, tegas siapa yang memakai menu HR vs **My Features** / setara.
3. **Glosarium** — tabel 2 kolom:
    - Header: `| **Istilah** | Arti singkat |` (atau varian _Istilah di layar_).
    - Pemisah: `| :--- | :--- |` (konsisten dalam satu bab).
    - Entri UI dengan `**...**`; penjelasan boleh memakai _italic_ untuk istilah asing. Baris glosarium boleh panjang jika perlu (lihat **Leave Management**).
4. **Ringkasan menu (opsional)** — `## 1.` atau section awal dengan tabel `| Menu | Uraian singkat |` bila modul punya banyak pintu sidebar (**Employee Management**). Hindari URL di kolom uraian.
5. **Alamat / URL contoh (opsional)** — satu baris **Alamat contoh:** / **Alamat:** + URL + “sesuaikan dengan server Anda”. **Jangan** menjejali bab dengan banyak URL; untuk **Login** satu kali dalam konteks karyawan boleh seperti di **Leave**, asalkan tidak menggantikan riset menu.
6. Pemisah antar blok besar: baris `---` .
7. **Isi utama** — `## 1. …`, `## 2. …` mengikuti alur bisnis.
    - **Judul dengan audiens:** pola seperti **`## N. Untuk HR — …`** vs **`## N. Untuk karyawan … — **My … Request**`** (lihat Official Travel & Leave).
    - **Sub-bab bernomor desimal** bila satu `##` masih terlalu luas: **`### 2.1 …`**, **`### 2.2 …`** (lihat Leave: Entitlements massal vs per karyawan).
8. **Subjudul langkah** — utamakan:

    `### Langkah-langkah — [frasa tindakan](_Judul layar dalam bahasa Inggris bila perlu_)`

    Gunakan **tanda panjang (em dash)** `—` sebelum subjudul, bukan hanya `-` tunggal, agar seragam dengan bab terbaru.

9. **Isi langkah** — kombinasi yang diperbolehkan:
    - Daftar bernomor `1.` `2.` …
    - Sub-poin `-` dengan pola **`Nama Label UI`** — penjelasan (em dash atau setelah titik dua), untuk dashboard / kartu ringkasan.
    - **Langkah “tebal” berikut paragraf + gambar:** `**2. Letter Number**` baris baru lalu teks; cocok untuk form panjang (**Official Travel**, **Employee** tab demi tab).
    - Satu nomor langkah boleh memuat **paragraf penjelasan dalam** dan sub-bullet (lihat **My Leave Request** langkah 3).
10. **Blok penjelasan khusus** — untuk menyalin isi jendela bantuan/modal tanpa mengubah makna:
    - Contoh judul: **`**Penjelasan singkat — jendela …**`**, lalu bullet dengan teks yang selaras UI.
11. **Tabel dalam isi (selain glosarium & error)** — diperbolehkan untuk: matriks tahap setup, katalog pesan validasi impor, perbandingan peran, dsb. Gunakan pemisah `| :--- |` dan header jelas (contoh: **Tahap** | **Uraian**; **Jenis** | **Contoh pesan** | **Yang dicek**).
12. **Catatan** — `**Catatan:**` untuk hak akses, urutan wajib, pengecualian bisnis; boleh beberapa kali per bagian. Bahasa pengguna, bukan jargon engineer.
13. **Rujukan antar bagian** — gunakan teks seperti **lihat bagian 2**, **setara bagian 3 (HR)**, atau tautan `[…](#id-anchor)` / `<a href="#id">…</a>` bila anchor sudah ditetapkan. Set **`id="…"`** pada `<p align="center" …>` gambar atau heading target agar tautan tidak putus.

### 2) Kesalahan & bantuan (penutup konten utama)

- Section bernomor terakhir sebelum penutup div (mis. **`## N. Kesalahan & bantuan`**). Judul boleh tanpa sufiks “(end user)”.
- Tabel 3 kolom: `Gejala / pesan (contoh) | Kemungkinan penyebab | Apa yang bisa dicoba` dengan `| :--- |` konsisten.
- Lalu **`### Menghubungi administrator`**: tanpa meminta **password**; cukup **username**, waktu, konteks menu, nomor register/NIK bila relevan, cuplikan pesan. Untuk dokumen murni teknis internal: **`### Jika ada masalah`** setara.
- Jika ada katalog error detail di bagian lain (contoh impor di **2.1**), tabel kesalahan global boleh merujuk **“lihat bagian X”** untuk menghindari duplikasi.

### 3) Gambar & figur

1. **Cuplikan layar** harus **mencerminkan ARKA HERO** sesuai langkah; sinkronkan teks bila UI berubah.
2. **Letak** — setelah langkah (atau sub-langkah) yang dibuktikan.
3. **Format dasar:**

```html
<p align="center" id="anchor-opsional-snake-case">
    <img
        src="images/nama_file_snake_case.png"
        alt="Deskripsi konkret: area layar, label/kolom/tombol yang terlihat — untuk aksesibilitas"
        style="max-width: 100%; width: 100%; height: auto;"
    />
</p>
```

4. **Keterangan figur (disarankan untuk bab panjang)** — pola yang dipakai **Leave**:

    `<br/><em>Gambar 2.1a — … ringkasan untuk pembaca cetak …</em>`

    Nomor figur: **`Gambar [nomor section].[urut]`** atau **`Gambar N.Nx`** konsisten dalam satu bab. `<em>` boleh memuat `**strong**` untuk label UI.

5. **Nama berkas** — `snake_case`, deskriptif; urutan form: `modul-step-03-travel-information.png`, `employee-add-employment.png`, dsb.
6. **Lokasi** — file di **`docs/user-manual/images/`**; di Markdown: `images/berkas.png`.
7. **Lebar** — patokan: layar/dashboard/list penuh **`width: 100%`**; form sedang **`75%`–`90%`**; satu panel/modal **`50%`–`75%`**; pastikan teks di PDF tidak hilang.
8. **Tautan “Lihat gambar”** — `<a href="#anchor">Lihat gambar</a>` ke `id` pada `<p>` gambar.
9. **Placeholder** — tetap tulis `src` final; ganti berkas di `images/` nanti; setelah replace, **perbarui `alt` dan keterangan `Gambar …`**.

### 4) Screenshot — menangkap & menyelaraskan (manusia + AI)

1. Sumber: aplikasi ARKA HERO (versi UI sama dengan dokumentasi); satu frame utama **satu maksud** langkah.
2. Frame mencakup konteks: sidebar aktif, breadcrumb/judul, area yang disebut teks.
3. **Privasi** — sensor data sensitif atau pakai akun demo.
4. **PNG** disarankan; hindari kompresi yang membuat teks kabur.
5. Alur: tulis langkah → tangkap → simpan `images/` → cocokkan `src` → isi `alt` + keterangan figur.
6. Setelah mengganti banyak gambar, **regenerasi PDF** jika dipakai tim.

### 5) Navigasi sidebar

- Tulis jejak **tepat seperti label menu**, mis.: **HERO SECTION** → **Leave Management** → **Dashboard**.
- **Disarankan** simbol panjang `→` (bukan `->`) untuk bab baru agar selaras dengan tipografi Indonesia/Inggris.
- Grup: **Official Travel Management**, **My Features**, dll. — sesuaikan hasil baca `sidebar` blade.

### 6) Bungkus teks (justify)

- Untuk narasi panjang, bungkus seluruh isi (kecuali kebutuhan khusus) dengan:

    `<div style="text-align: justify; text-justify: inter-word;">` … `</div>`

- Penutup `</div>` **sebelum** `---` penutup bab. **Seragamkan** dengan seri dokumen lain (Employee & Official memakai; jika satu bab tanpa div, kesepakatan tim dokumen).

### 7) Yang dihindari (teks “developer”)

Jangan sertakan untuk pembaca akhir: nama field database mentah, nama route/controller, `@can` / `@hasrole` di narasi, metode HTTP, endpoint, kode `403/404` kecuali sudah diterjemahkan ke gejala pengguna.

**Pengecualian terbatas:** nama **lembar** workbook / template Excel **persis seperti yang tercetak** pada file unduhan (boleh `backtick` jika itu memang label yang dibaca pemakai di Excel), tanpa menjelaskan skema database.

### 8) Sebelum menulis — riset codebase (wajib jika repo ada)

- `routes/web.php`, `resources/views/layouts/partials/sidebar.blade.php`, judul `$title` / `$subtitle`, view `resources/views/…`.
- Teks tombol, **modal**, **breadcrumb**, placeholder, header kolom **DataTables**, judul tab/wizard, label upload (tipe/ukuran file).
- Jangan mengarang menu atau label yang tidak ada.

### 9) Output

- Keluarkan **hanya isi file `.md` lengkap** (siap disimpan), kecuali diminta sebaliknya.
- Nama file: `NN-nama-topik.md` (nomor tim dokumen).

---

## Input bab (diisi manusia lalu ditempel bersama instruksi di atas)

- **Nama & nomor file target:** (`16-my-features.md`)
- **Topik & outline:** (semua fitur yang ada di dalam My Dashboard, Update Profile (change password), My Features)
- **Pembaca target:** (semua karyawan)
- **Menu sidebar (label persis):** (My Dashboard, My Features -> My Profile, My Leave Request, My Official Travel Request, My Flight Request, My Overtime Request, My Recruitment Request)
- **URL contoh (opsional, untuk riset):** (satu base URL; tidak wajar dijejalkan ke dokumen)
- **Screenshot:** daftar `images/…png` + **apa yang harus tampak** + nomor figur jika sudah direncanakan; tandai **placeholder**
- **Tabel / katalog khusus:** (mis. “sertakan tabel error impor seperti Leave 2.1” — jika perlu)
- **Kasus error khas (opsional):**
- **Kebijakan / istilah perusahaan (opsional):** (HO, format nomor, dsb.)
- **Penyimpangan gaya (opsional):** (mis. tanpa div justify, tanpa ringkasan menu, bahasa Inggris untuk judul H1 saja)

**Akhir instruksi — tempel input di atas, lalu minta “buat file Markdown-nya”.**
