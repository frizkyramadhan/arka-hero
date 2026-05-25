## Instruksi untuk AI

Anda adalah penulis dokumentasi produk internal. Hasilkan **satu file Markdown** panduan pengguna untuk aplikasi **ARKA HERO**, dengan narasi **Bahasa Indonesia** yang jelas. **Label antarmuka** (menu, tombol, judul halaman, nama field di layar) **tepat seperti di aplikasi (biasanya English)** — tebalkan label tersebut dengan `**...**` saat dijelaskan.

### Acuan dokumen (gaya aktual di repo)

Selaraskan struktur dan kedalaman dengan bab-bab berikut **sesuai kompleksitas topik**:

| Referensi                       | Gunakan sebagai contoh untuk…                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              |
| :------------------------------ | :------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| **`10-official-travel.md`**     | Bab multi-audiens (HR vs self-service), dashboard dengan sub-poin penjelasan metrik, form panjang dipecah **`**1.**`…`**10.**`**, gambar per blok, tautan internal antar subjudul, `---` antar bagian besar.                                                                                                                                                                                                                                                                                                               |
| **`11-leave-management.md`**    | Modul sangat dalam: **`### 2.1` / `2.2` / `2.3`** di bawah `## 2`, tabel referensi (setup, **Import Validation Errors**), blok **`**Penjelasan singkat —**`** untuk menyalin struktur jendela/modal, penomoran figur dan rujukan silang antar bagian (**bagian 3 (HR), langkah …**) dengan anchor `#id`. Untuk **markup HTML figur**, samakan **satu `<p>` pembungkus** (`<img>` + `<br><em>Gambar …</em>`) seperti **`16-my-features.md`** saat menyunting atau menulis bab baru.                                         |
| **`08-employee-management.md`** | **`## 1.` Ringkasan menu** awal bila banyak entri sidebar, wizard **Add Employee** per tab/step berurutan, beberapa gambar beruntun untuk satu alur (mis. modal setelah tabel), **Kesalahan & bantuan** sebagai section bernomor terakhir.                                                                                                                                                                                                                                                                                 |
| **`16-my-features.md`**         | Bab **satu audiens (semua karyawan)**: **My Dashboard**, **Update Profile**, seluruh **My Features**; pembuka dalam `<div>` justify; **Glosarium** istilah self-service; narasi **diselaraskan** dengan panduan modul induk (Leave, LOT, Flight); **format figur kanonik**: satu `<p align="center">` berisi `<img>` lalu `<br><em>Gambar …</em>`, dengan `id` pada `<p>` bila perlu tautan; beberapa cuplikan beruntun (**1.1a–c**, dll.); penutup **`## 9. Kesalahan & Bantuan`** + **`### Menghubungi administrator`**. |

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
13. **Rujukan antar bagian** — gunakan teks seperti **lihat bagian 2**, **setara bagian 3 (HR)**, atau tautan `[…](#id-anchor)` / `<a href="#id">…</a>` bila anchor sudah ditetapkan. Set **`id="…"`** pada **`<p align="center" …>` pembungkus figur** (bukan pada `<img>` saja) atau pada heading target agar tautan tidak putus. **Label figur (`<em>Gambar …`) harus berada di dalam `<p>` yang sama** dengan `<img>` supaya anchor dan caption tidak terpisah (lihat **`16-my-features.md`**).

### 2) Kesalahan & bantuan (penutup konten utama)

- Section bernomor terakhir sebelum penutup div (mis. **`## N. Kesalahan & bantuan`**). Judul boleh tanpa sufiks “(end user)”.
- Tabel 3 kolom: `Gejala / pesan (contoh) | Kemungkinan penyebab | Apa yang bisa dicoba` dengan `| :--- |` konsisten.
- Lalu **`### Menghubungi administrator`**: tanpa meminta **password**; cukup **username**, waktu, konteks menu, nomor register/NIK bila relevan, cuplikan pesan. Untuk dokumen murni teknis internal: **`### Jika ada masalah`** setara.
- Jika ada katalog error detail di bagian lain (contoh impor di **2.1**), tabel kesalahan global boleh merujuk **“lihat bagian X”** untuk menghindari duplikasi.

### 3) Gambar & figur

1. **Cuplikan layar** harus **mencerminkan ARKA HERO** sesuai langkah; sinkronkan teks bila UI berubah.
2. **Letak** — setelah langkah (atau sub-langkah) yang dibuktikan.
3. **Format wajib (HTML)** — **satu blok `<p>`** berisi **`<img>`** dan **keterangan figur**; **jangan** memakai `<p>` terpisah hanya untuk `<em>Gambar …</em>`. Setelah penutup tag `<img />`, tambahkan **baris baru** berisi `<br><em>…</em>`, lalu `</p>`. Patokan salin langsung dari **`16-my-features.md`**:

```html
<p align="center" id="anchor-opsional-kebab-atau-snake">
    <img
        src="images/nama_file_snake_case.png"
        alt="Deskripsi konkret: area layar, label/kolom/tombol yang terlihat — aksesibilitas"
        style="max-width: 90%; width: 90%; height: auto;"
    />
    <br /><em>Gambar 2.1 — Ringkasan untuk pembaca cetak</em>
</p>
```

- **`id`** — taruh pada `<p align="center" …>` saat figur perlu ditaut (`[Gambar …](#id)`, **Lihat gambar**, atau rujukan teks). Untuk beberapa gambar berurutan, **hanya `<p>` yang dirujuk** yang wajib ber-`id`; sisanya boleh `<p align="center">` tanpa `id`.
- **`<br>`** — pakai bentuk `<br>` (bukan wajib `<br />`); **tanpa** indentasi di depan `<br>` agar seragam dengan **`16-my-features.md`**.
- **`alt`** — deskriptif (bahasa Indonesia boleh); hindari teks figur pendek saja.

4. **Penomoran figur** — dalam satu bab: **`Gambar [nomor section].[urut]`** (mis. **Gambar 1.3**), sufiks huruf untuk rangkaian dalam section yang sama (**Gambar 1.1a**, **1.1b**, **1.1c**), atau indeks desimal (**Gambar 4.5b**) bila perlu menyisipkan figur antara dua nomor utama — **konsisten** sepanjang dokumen.

5. **Lebar (`style`)** — ikuti **`16-my-features.md`**: dashboard/list/form lebar umumnya **`90%`** (plus `max-width` sama); form tiket supaya tidak pecah **`73%`–`80%`**; satu panel sempit/modal **`50%`** (sesuaikan isi); variasi **`85%`** untuk form panjang tertentu. Utamakan keterbacaan di **PDF**, bukan melulu `100%`.

6. **Nama berkas** — `snake_case`, deskriptif; contoh: `my-dashboard-01.png`, `my-flight-request-cancel-modal.png`, `my-recruitment-request-detail-draft.png`.

7. **Lokasi** — file di **`docs/user-manual/images/`**; di Markdown: `images/berkas.png`.

8. **Tautan ke figur** — `[teks](#id)` menuju **`id` pada `<p>`** pembungkus (caption ikut satu blok dengan gambar).

9. **Placeholder** — tetap tulis `src` final; setelah mengganti berkas, **perbarui `alt` dan teks `<em>Gambar …</em>`**.

### 4) Screenshot — menangkap & menyelaraskan (manusia + AI)

1. Sumber: aplikasi ARKA HERO (versi UI sama dengan dokumentasi); satu frame utama **satu maksud** langkah.
2. Frame mencakup konteks: sidebar aktif, area yang disebut teks.
3. **Privasi** — sensor data sensitif atau pakai akun demo.
4. **PNG** disarankan; hindari kompresi yang membuat teks kabur.
5. Alur: tulis langkah → tangkap → simpan `images/` → cocokkan `src` → isi `alt` + teks `<em>Gambar …</em>` **di dalam `<p>` yang sama** dengan `<img>` (§3).
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
- Teks tombol, **modal**, placeholder, header kolom **DataTables**, judul tab/wizard, label upload (tipe/ukuran file).
- Jangan mengarang menu atau label yang tidak ada.

### 9) Output

- Keluarkan **hanya isi file `.md` lengkap** (siap disimpan), kecuali diminta sebaliknya.
- Nama file: `NN-nama-topik.md` (nomor tim dokumen).

---

## Input bab (diisi manusia lalu ditempel bersama instruksi di atas)

### Contoh terisi — **`12-my-features.md`** (sinkron dengan repo)

Gunakan blok ini sebagai **template lengkap** saat meregenerasi atau menyamakan gaya bab **My Dashboard & My Features**:

- **Nama & nomor file target:** `12-my-features.md`
- **Judul H1:** `My Dashboard & My Features` (tanpa nomor path).
- **Pembaca target:** semua karyawan ber-akun; label UI **English** seperti di app; narasi **Bahasa Indonesia**.
- **Struktur isi (urutan `##`):**
    1. **Glosarium** — istilah self-service: **My Dashboard**, **My Features**, **LOT**, **FPTK**, **Entitlement**, **NIK**, **DOH**, **FOC**, status (**Draft**, **Pending**, …), **Approver**, **LSL**, **Standalone**, **Profile Completeness**.
    2. **`## 1. My Dashboard`** — welcome, banner username, tab Overview/…, kartu statistik, widget **Recent …** (tiga baris); figur beruntun **Gambar 1.1a–c** + figur terpisah **1.2**, **1.3**, **1.4a–c** dengan **`id`** untuk tautan internal (contoh `#gambar-11c`, `#my-dashboard-username-warning`).
    3. **`## 2. Update Profile`** — anchor manual `<a id="update-profile"></a>` bila perlu; form **Update Profile** / **Change Password**; tautan dari banner dashboard.
    4. **`## 3. My Profile`** — stepper tab, penjelasan warna ikon, daftar tab & isi (read-only).
    5. **`## 4. My Leave Request`** — selaras narasi dengan **_Leave Management_** (My Leave Request); entitlement; create form; **detail LSL + Flight Draft**; **Request Leave Cancellation**; HR HO Balikpapan / Flight Management bila relevan.
    6. **`## 5. My Official Travel Request`** — selaras dengan **Official Travel (LOT)**; form Add LOT; subsection **arrival, departure, closing** + cetak/stempel vendor/pemerintah bila perlu.
    7. **`## 6. My Flight Request`** — daftar gabungan Leave Based / Travel Based / Standalone; **Create** per tipe (beberapa figur); **detail Draft / Submitted**; **modal Cancel**; anchor untuk subsection pembatalan.
    8. **`## 7. My Overtime Request`** — daftar, form Add, detail Draft / aksi.
    9. **`## 8. My Recruitment Request`** — daftar FPTK, create, detail Draft, detail Approved + **Recruitment Sessions**.
    10. **`## 9. Kesalahan & Bantuan`** — tabel 3 kolom + **`### Menghubungi administrator`**.
- **Menu sidebar (label persis):** **My Dashboard**; grup **My Features** → **My Profile**, **My Leave Request**, **My Official Travel Request**, **My Flight Request**, **My Overtime Request**, **My Recruitment Request**.
- **`<div>` justify:** bungkus narasi utama seperti di file aktual (`text-align: justify; text-justify: inter-word;`), tutup **sebelum** `---` penutup bab.
- **Gambar:** ikuti **§3 Gambar & figur** di atas (satu `<p>` = `<img>` + `<br><em>Gambar …</em>`); nama berkas & nomor figur mengikuti **`16-my-features.md`** saat ini.
- **Anchor tambahan (opsional):** sisipkan `<a id="…"></a>` di atas `##` / subsection bila perlu taut stabil antar bab (contoh pada file aktual: `#update-profile`, `#section-4-my-leave-request`, `#section-5-my-official-travel`, `#melihat-detail-permintaan-tiket`, `#my-flight-request-cancel`, `#my-overtime-request-detail`, `#my-recruitment-request-detail`, `#my-recruitment-sessions`).
- **Riset codebase:** sidebar, route, view untuk setiap menu di atas; jangan mengarang label.

**Template generik (bab lain):**

- **Nama & nomor file target:** (`09-recruitment-management.md`)
- **Topik & outline:** (bullet besar per `## 1. Dashboard, ## 2. Request (FPTK), ## 3. Request (MPP), ## 4. Candidate (CV), ## 5. Session Approved FPTK/MPP, Candidate Session (CV Review, Psikotes, Interview HR-User-Trainer, Offering, MCU, Hiring & Onboarding), ## 6. Reports, ## 7. My Recruitment Request` dan semua rinciannya)
- **Pembaca target:** (1-6 untuk HR, 7 untuk semua karyawan)
- **Menu sidebar (label persis):** (HERO Section -> Recruitment Management)
- **URL contoh (opsional, untuk riset):** (satu base URL; jarang dimasukkan ke narasi)
- **Screenshot:** daftar `images/…png` + apa yang tampak + nomor figur; tandai **placeholder**
- **Tabel / katalog khusus:** (jika perlu)
- **Kasus error khas (opsional):**
- **Kebijakan / istilah perusahaan (opsional):** (HO, nomor surat, dsb.)
- **Penyimpangan gaya (opsional):** (mis. tanpa div justify)

**Akhir instruksi — tempel input di atas, lalu minta “buat file Markdown-nya”.**
