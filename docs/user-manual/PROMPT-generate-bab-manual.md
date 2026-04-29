# Prompt generik — User manual ARKA HERO (Markdown)

Salin **seluruh isi bagian “Instruksi untuk AI”** di bawah ke chat (tambahkan **input bab** di bagian akhir). Sesuaikan nama file output jika perlu.

---

## Instruksi untuk AI

Anda adalah penulis dokumentasi produk internal. Hasilkan **satu file Markdown** panduan pengguna untuk aplikasi **ARKA HERO**, dengan narasi **Bahasa Indonesia** yang jelas. **Label antarmuka** (menu, tombol, judul halaman, nama field di layar) **tepat seperti di aplikasi (biasanya English)** — tebalkan label tersebut dengan `**...**` saat dijelaskan.

**Acuan gaya terbaru:** utamakan konsistensi dengan `docs/user-manual/10-official-travel.md` (bab kompleks, multi-audiens, banyak gambar). Untuk bab ringkas, rujuk juga `05-register-dan-login.md`, `06-user-role-permission.md`.

### 1) Struktur wajib

1. **H1** — judul topik, tanpa kode file, path repo, atau nomor urut file (mis. jangan tulis `10-…` di judul).
2. **Pembuka** — 1–3 kalimat: **untuk siapa** bab ini (boleh membedakan peran, mis. staf HR vs karyawan self-service), **cakupan fitur**, dan bila perlu bahwa teks di layar mengikuti **bahasa Inggris** seperti di aplikasi.
3. **Glosarium** — tabel 2 kolom:
    - Header: `| **Istilah** | Arti singkat |` (atau _Istilah di layar_ bila semua entri label UI).
    - Baris pemisah dengan perataan kolom yang konsisten, mis. `| :--- | :--- |` (sama seperti di `10-official-travel.md`).
    - Istilah kunci UI pakai `**...**`; singkatan/istilah asing boleh dijelaskan dengan _italic_ di kolom arti.

4. **Alamat contoh (opsional)** — baris **Alamat contoh:** atau **Alamat:** + URL `http://…` + kalimat singkat: sesuaikan dengan server perusahaan. Jangan menyalin banyak URL teknis ke dalam narasi.
5. Pemisah antar bagian besar: baris `---` .
6. **Isi per nomor** — `## 1. …`, `## 2. …` mengikuti alur bisnis.
    - Bila satu bab melayani peran berbeda, gunakan klausul di judul, mis. **`## 3. Untuk HR — …`** vs **`## 6. Karyawan (non–HR) — …`** (contoh pola dari Official Travel).
7. **Subalurnya:** `### Langkah-langkah — [judul tindakan]` dengan **label UI penting** ditebalkan di judul; tambahkan `(_English label_)` bila membantu.
8. **Navigasi:** **Login** → grup sidebar **tepat seperti di app** (mis. **Official Travel Management**, **My Features**) → sub-menu.  
   Baris _Atau buka: …_ hanya jika perlu, **satu baris singkat** (bukan daftar path panjang).
9. **Variasi “langkah” (samakan praktik Official Travel):**
    - **Daftar bernomor** `1.` `2.` … untuk alur linear pendek–menengah.
    - **Sub-poin** `-` dengan pola **Label UI** — penjelasan singkat (gunakan em dash) untuk merangkum isi kartu/ringkasan dashboard (lihat penjelasan metrik di dashboard LOT).
    - **Form panjang / banyak layar:** pecah menjadi langkah **`**1.**`…`**8.**`** atau **`**2. Letter Number**`** + paragraf + gambar per blok; hindari satu daftar bernomor 20+ item tanpa jeda visual.
10. **Catatan** — gunakan `**Catatan:**` untuk kebijakan, pengecualian, **hak akses** / izin, urutan wajib (tanpa jargon pemrograman). Lebih dari satu **Catatan** dalam satu bagian boleh.
11. **Setelah …** — subjudul penutup alur (mis. _Setelah membuka dashboard_) hanya jika memang memudahkan pembaca.
12. **Kesalahan & bantuan** — section bernomor terakhir sebelum penutup div (mis. `## 7. Kesalahan & bantuan`). Tabel 3 kolom:

    `Gejala / pesan (contoh) | Kemungkinan penyebab | Apa yang bisa dicoba`

    dengan pemisah kolom `| :--- |` konsisten. Lalu `### Menghubungi administrator` (tanpa meminta **password**; cukup username, waktu, ringkasan pesan). Jika hanya admin teknis: cukup `### Jika ada masalah`.

13. **Gambar / screenshot** — wajib memakai **cuplikan layar aplikasi yang sesuai** dengan langkah dan label UI di teks (bukan gambar generik yang tidak mencerminkan ARKA HERO). Letakkan **setelah** langkah atau subbab yang digambarkan. Format HTML:

```html
<p align="center" id="anchor-opsional-snake-case">
    <img
        src="images/nama_file_snake_case.png"
        alt="Deskripsi konkret: area layar, label tombol/kolom yang terlihat — untuk aksesibilitas"
        style="max-width: 100%; width: 100%; height: auto;"
    />
</p>
```

- **Nama file:** snake_case, deskriptif; untuk urutan form panjang, pola seperti `modul-step-02-letter-number.png` (lihat `10-official-travel.md`).
- **Lokasi berkas:** simpan aset ke folder **`docs/user-manual/images/`**; di Markdown gunakan path relatif `images/nama_berkas.png` (relatif terhadap file bab `.md`).
- **Lebar tampilan (`width` / `max-width`):** dashboard / daftar / laporan lebar → `100%`; form multi-bagian → umumnya `75%`; satu kartu/panel sempit → `55%`–`75%` — sesuaikan agar teks di PDF/HTML masih terbaca (hindari memperkecil sampai label UI tidak jelas).
- **Tautan dari teks:** `<a href="#anchor-opsional-snake-case">Lihat gambar</a>`.
- **Tanpa atribut `id`** pada `<p>` hanya bila gambar tidak pernah dirujuk tautan internal.
- **Placeholder:** jika screenshot belum tersedia, tetap tulis blok `<img>` dengan `src` dan nama file final yang sudah disepakati; penulis manusia atau AI kemudian **mengganti berkas** di `images/` tanpa mengubah nama file. Setelah gambar diganti, **sesuaikan `alt`** agar cocok dengan isi layar terbaru.

14. **Tautan internal antar bagian** — bila alur melanjutkan ke subjudul lain di **bab yang sama**, boleh memakai tautan Markdown ke heading, mis. `[Arrival Check](#langkah-langkah--record-arrival-arrival-check)` (sesuaikan slug dengan renderer yang dipakai tim).

15. **Rata kiri–kanan (disarankan untuk bab narasi panjang)** — seluruh isi (kecuali kebutuhan khusus) dibungkus:

    `<div style="text-align: justify; text-justify: inter-word;">` … `</div>`

    Penutup `</div>` **sebelum** `---` terakhir bab; setelah `</div>` boleh satu baris `---` penutup file.

16. **Ringkasan menu (opsional)** — jika banyak halaman, tabel `| Menu | Uraian singkat |` tanpa menjejali URL.

17. **Hindari teks “developer”** — tanpa: nama field database, nama route/controller, `@can`/`@hasrole` di teks user-facing, metode API/HTTP, kode error mesin, daftar endpoint, `PATCH`/`GET`, `403/404` kecuali sudah diterjemahkan ke bahasa pengguna. **Hak akses / izin** dijelaskan dengan kata sehari-hari.

### 2) Screenshot — cara menangkap dan menyelaraskan dengan tutorial

Gunakan panduan ini agar **gambar selaras dengan narasi** dan layak cetak/PDF.

1. **Sumber tangkapan** — ambil dari aplikasi ARKA HERO (lingkungan dev/staging/produksi sesuai kebijakan tim). Pastikan **versi UI** dan label sama dengan yang dijelaskan di bab (setelah riset `sidebar` / view).
2. **Isi frame** — satu screenshot utama fokus pada **satu tujuan langkah**: misalnya menu sidebar yang aktif, judul halaman, area filter/tabel/tombol yang disebut di teks. Hindari memotong bagian penting (breadcrumb, judul kartu, tombol aksi) kecuali sengaja untuk zoom panel.
3. **Konsistensi dengan teks** — label yang disebut di langkah (mis. **Bulk Approve**, **Pending Approvals**) harus **terlihat** di gambar atau dijelaskan di `alt` jika hanya sebagian terpotong. Jika UI berubah, **perbarui teks bab** atau **ganti screenshot**.
4. **Privasi & data** — sensor atau gunakan data contoh yang diizinkan (nama, NIK, gaji, surel) sesuai kebijakan perusahaan; jangan menyebar data nyata sensitif di manual publik.
5. **Teknis file** — format **PNG** disarankan untuk antarmuka; hindari kompresi agresif yang membuat teks kabur. Nama berkas **snake_case**, satu adegan per berkas kecuali alur memang satu layar panjang.
6. **Alur kerja penyisipan** — (a) tulis/revisi langkah Markdown → (b) tangkap layar yang membuktikan langkah itu → (c) simpan ke `docs/user-manual/images/` → (d) pastikan `src` di bab mengarah ke nama berkas itu → (e) isi `alt` dengan deskripsi konkret (komponen, kolom tabel, warna badge status bila relevan), bukan “screenshot” kosong.
7. **Export PDF** — setelah mengganti gambar, generate ulang PDF bila tim memakai alur tersebut, agar bab tidak masih memuat cuplikan lama.

### 3) Sebelum menulis, riset di codebase (wajib jika tersedia)

- `routes/web.php`, `resources/views/layouts/partials/sidebar.blade.php` (label menu & hierarki), judul di controller / variabel `$title` / `$subtitle`, serta teks di `resources/views/…` agar **label, menu, dan alur** akurat.
- Cek teks **tombol**, **placeholder**, **modal**, **breadcrumb**, dan nama kolom **DataTables** jika halaman memakai tabel.
- Jangan mengarang nama menu atau label yang tidak ada di UI.

### 4) Output

- Keluarkan **hanya isi file `.md` lengkap** (siap disimpan), kecuali diminta sebaliknya.
- Nama file disarankan: `NN-nama-topik.md` (nomor disepakati tim dokumentasi).

---

## Input bab (diisi manusia lalu ditempel bersama instruksi di atas)

- **Nama & nomor file target:** (contoh: `11-leave-management.md`)
- **Topik & sub-bab / alur utama:** (Leave Management (catatan: mengacu pada form cuti / Formulir Izin Meninggalkan Pekerjaan dan Formulir Cuti Panjang, sehingga semua aktivitas yang menggunakan form tersebut akan diakomodir oleh fitur ini)
  a. Dashboard
  b. Entitlement (definisi yang benar menurut role bisnis perusahaan, dan keterkaitan antara leave entitlement dengan leave request)
  Generate Entitlement, Export, Import
  c. Request
  Create Leave Request, Flight Request, Approval Selection, Cancellation
  d. Report
  e. My Leave Request (untuk karyawan non-HR))
- **Pembaca target:** (HR)
- **Menu sidebar & path (teks persis jika sudah yakin):** (HERO SECTION -> Leave Management)
- **Rute/URL singkat (opsional, untuk riset saja — jangan penuhi dokumen dengan URL):** (…)
- **Screenshot:** untuk tiap subbab utama, sebutkan **nama file** di `images/` + **apa yang harus tampak** di layar (contoh: “halaman list dengan filter terbuka”). Tandai jika masih **placeholder**. Setelah ada berkas nyata, pastikan cuplikan **match** label di langkah.
- **Kasus error khas (opsional):** (sebutkan error yang mungkin terjadi)
- **Batasan kebijakan / istilah perusahaan (opsional):** (mis. nama cabang, HO, format nomor surat — agar narasi konsisten)
- **Konsolidasi gaya khusus (opsional):** (mis. “tanpa URL sama sekali”, “satu gambar per subbab”, “bab tanpa glosarium” — hanya jika tim menyimpangi default)

**Akhir instruksi — tempel input di atas, lalu minta “buat file Markdown-nya”.**
