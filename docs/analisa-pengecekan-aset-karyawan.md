# Analisa pengecekan aset karyawan

CRUD inventories tetap di ARAIM. Arka Hero hanya menampilkan, di detail karyawan, inventories yang sedang dipegang orang itu.

## Istilah

Lihat [glosarium](/cursor/stores/self/docs/glossary.md).

- **Inventories** (dulu disebut perbekalan) adalah barang yang dipegang karyawan. **Aset** adalah jenis di katalog.
- **Ikatan karyawan** mengikuti orang Arka Hero, bukan NIK dan bukan data administrasi.
- **Riwayat inventories** adalah urutan pemegang, termasuk pindah ke karyawan lain.
- **Mutasi** di Arka Hero tetap berarti pindah proyek.

## Yang dibangun

**ARAIM, menu karyawan.** Pengguna mencari karyawan Hero lewat nama atau NIK saat ini. Yang disimpan adalah employee id Hero. NIK yang berubah, termasuk karyawan kontrak yang ganti NIK, tidak memutus ikatan.

**Arka Hero, detail karyawan.** Saat halaman dibuka, Hero membaca ARAIM. Tidak ada salinan inventories di Hero. Daftar hanya inventories yang sedang dipegang. Kalau barang itu pernah pindah, detailnya menampilkan riwayat pemegang. Barang yang sudah pindah darinya tidak tetap di daftarnya. Karyawan ARAIM yang belum diikat tidak muncul di detail siapa pun.

## Yang tidak dibangun

- Form verifikasi atau opname. Belum ada di ARAIM, dan tidak diminta.
- Perubahan data inventories dari Hero.
- Menyamakan NIK ARAIM setiap kali NIK Hero berubah.

## Kondisi kode ARAIM.v2

Padanan istilah analisa di kode ini:

| Analisa | Di ARAIM.v2 |
| --- | --- |
| Inventories | Baris `inventories` |
| Pemegang | `inventories.employee_id` → `employees.id` |
| Nomor inventaris | `inventories.inventory_no` |
| Aset (katalog) | `assets` |
| Sedang dipegang | `transfer_status = Available` |
| Baris lama yang sudah pindah | `transfer_status = Mutated` |

- `employees` menyimpan `nik` integer, `id` lokal, dan `hero_employee_id` (UUID Hero, boleh kosong, unik).
- Pindah pemegang ada di `InventoryController::transferProcess`. Baris baru menyalin `inventory_no` dan `transfer_status = Available`. Kalau kuantitas lama habis, baris lama menjadi `Mutated`. Kalau masih ada sisa, baris lama tetap `Available` dengan kuantitas yang berkurang, jadi satu `inventory_no` bisa dipegang dua orang untuk bagian yang berbeda.
- Riwayat pemegang adalah kumpulan baris dengan `inventory_no` yang sama, urut `id`. Tidak ada tabel riwayat terpisah.
- `GET /api/hero/employees/{heroEmployeeId}/inventories` memakai `X-API-Key` (`API_KEY`). Tidak ada endpoint tulis.
- Detail karyawan Hero menampilkan inventories via client live. NIK Hero hidup di administrasi (`active_nik`). Employee id Hero yang stabil.

## Langkah kerja di ARAIM.v2

Diterapkan 2026-11-16. CRUD inventories tidak berubah. Yang ditambah: ikatan orang, ikat data lama sekali, dan API baca untuk Hero.

### 1. Simpan employee id Hero

Migrasi `employees.hero_employee_id`: string 36 karakter, boleh kosong, unik. Ini UUID `employees.id` di Hero, bukan id administrasi dan bukan NIK.

`EmployeeController::store` dan `update` menyimpan nilai itu dari pilihan pengguna. `employees.nik` tidak diisi ulang dari Hero.

### 2. Pencarian orang di menu karyawan

Di `employees/create` dan `employees/edit`, pengguna mencari nama atau NIK Hero saat ini. ARAIM memanggil API baca Hero dari server, lalu menyimpan employee id yang dipilih.

Daftar karyawan menampilkan apakah baris itu sudah terikat. Pengguna bisa mengganti atau melepas ikatan dari form yang sama.

Butuh URL dan token Hero di `.env` (`HERO_API_URL`, `HERO_API_TOKEN`). Hero saat ini punya pencarian by NIK. Pencarian nama memakai endpoint Hero yang mengembalikan employee id, nama, dan NIK aktif. Sebelum endpoint nama itu ada, ikat manual lewat NIK tetap bisa jalan.

### 3. Ikat baris lama sekali

Perintah artisan, misalnya `employees:bind-hero`. Untuk `employees` yang `hero_employee_id`-nya masih kosong, cocokkan `employees.nik` dengan `active_nik` Hero sebagai teks.

Hanya ikat kalau tepat satu orang Hero yang cocok. Nol atau lebih dari satu dibiarkan kosong dan dicetak di laporan, lalu diikat manual lewat form langkah 2. Baris yang sudah terisi tidak ditimpa. NIK ARAIM tidak diubah.

### 4. API baca untuk Hero

`GET /api/hero/employees/{heroEmployeeId}/inventories`, middleware `api.key` (`X-API-Key`).

- Kalau tidak ada karyawan ARAIM dengan id itu, balas daftar kosong.
- Isi daftar hanya baris `inventories` milik karyawan itu dengan `transfer_status = Available` dan `is_active = 1`.
- Setiap barang menyertakan riwayat: semua baris `inventory_no` yang sama, urut `id`, beserta nama pemegang, NIK ARAIM, `hero_employee_id`, tanggal, kuantitas, dan `transfer_status`.
- Tidak ada endpoint tulis. Hero tidak mengubah inventories.

### Di luar langkah ini

- Form verifikasi atau opname.
- Perubahan inventories dari Hero.
- Menyamakan `employees.nik` saat NIK Hero berubah.
- Mengubah cara `transferProcess` memecah kuantitas.

Keputusan ikatannya: employee id Hero, bukan NIK. Tercatat di `docs/decisions.md`.

## Kondisi kode Arka Hero (saat analisa)

| Kebutuhan | Status di Hero |
| --- | --- |
| UUID `employees.id` stabil | Ada (`Uuids` trait) |
| NIK aktif di administrasi | Ada: generated column `administrations.active_nik` |
| Lookup by NIK | Ada: `GET /api/employees/by-nik/{nik}` |
| Search by name | Ada: `POST /api/employees/search` body `name` — tapi `AdministrationResource` **tidak** mengembalikan `employee.id` |
| Detail karyawan menampilkan inventories | Belum ada |
| Client baca ARAIM | Belum ada (pola eksternal yang mirip: `ItWoZoomClient` + `config/it_wo.php`) |
| Salinan tabel inventories di Hero | Tidak ada dan **tidak** dibangun |

Sumber UI: `EmployeeController::show` → `resources/views/employee/detail.blade.php` (stepper panes). Pane baru mengikuti pola `@include('employee.components.*-pane')`.

## Langkah kerja di Arka Hero

Mulai diimplementasikan 2026-11-16 (lookup + client + pane). Isi `ARAIM_API_URL` / `ARAIM_API_KEY` lalu uji end-to-end dengan inventories ARAIM.

Urutan mengikuti ketergantungan: API yang dipakai ARAIM dulu (ikatan orang), baru baca inventories di detail.

### 0. Prasyarat / kontrak dengan ARAIM

- Endpoint inventories ARAIM sudah live: `GET /api/hero/employees/{heroEmployeeId}/inventories` (`api.key` / `X-API-Key`).
- `ARAIM_API_KEY` di Hero = `API_KEY` di ARAIM (nilai hanya di `.env`, tidak di repo).
- Sepakati bentuk JSON inventories minimal (daftar `Available` + `history[]` per `inventory_no`). Kalau ARAIM belum final, Hero tetap bisa stub client dengan empty/error handling.

### 1. Endpoint lookup orang untuk ARAIM (baca saja)

ARAIM perlu mencari karyawan Hero by nama atau NIK dan menyimpan **UUID employee**, bukan NIK.

**Gap:** `POST /api/employees/search` dan resource administrasi tidak mengekspos `employee.id`. Response terlalu berat untuk autocomplete ikatan.

Tambah endpoint ringan, misalnya:

`GET /api/employees/lookup?q=...` (atau `POST` body `q`)

- Auth: sama dengan API Hero lain (API key / Sanctum yang sudah dipakai eksternal).
- Cocokkan `q` ke `employees.fullname` (LIKE) **atau** `administrations.active_nik` / `nik` aktif.
- Prefer baris administrasi `is_active = 1`.
- Response per orang (dedupe by `employee.id`):

```json
{
  "status": "success",
  "data": [
    {
      "id": "uuid-employee-hero",
      "fullname": "...",
      "active_nik": "..."
    }
  ]
}
```

- Limit hasil (mis. 20). Tidak mengubah data.
- Opsional singkat: `GET /api/employees/{id}/lookup` untuk resolve satu UUID → nama + NIK aktif (berguna saat ARAIM menampilkan ikatan yang sudah tersimpan).

**File tipikal:** `routes/api.php`, method baru di `EmployeeApiController` (atau controller kecil khusus lookup), sync Postman collection "ARKA HERO - API".

Tanpa endpoint ini, ARAIM masih bisa ikat lewat NIK manual (`showByNik`) — tapi autocomplete nama di menu karyawan ARAIM menunggu langkah ini.

### 2. Konfigurasi client ARAIM di Hero

- `.env`: `ARAIM_API_URL` (include `/api`, contoh `http://localhost/araim.v2/api`), `ARAIM_API_KEY` (= ARAIM `API_KEY`).
- `config/araim.php`: `base_url`, `api_key`, `timeout`.
- Kalau `base_url` atau `api_key` kosong → mode off: pane unconfigured.

### 3. Service baca inventories

`App\Services\AraimInventoriesClient`:

- `inventoriesForEmployee(string $heroEmployeeId): array`
- `GET {ARAIM_API_URL}/hero/employees/{id}/inventories` + header `X-API-Key` (bukan Bearer).
- Timeout pendek; gagal jaringan / 401 / 5xx → log warning, return struktur error yang bisa ditampilkan UI (bukan exception mentah ke user).
- Tidak menulis DB Hero. Tidak cache wajib di v1 (boleh cache request singkat nanti kalau lambat).

Satu self-check kecil: unit/feature test HTTP fake → mapping response; atau demo assert di service jika tim tetap pada pola "satu check".

### 4. Muat inventories di detail karyawan

Di `EmployeeController::show`:

- Panggil client dengan `$employee->id`.
- Pass ke view: daftar inventories + status integrasi (`ok` / `empty` / `unconfigured` / `error`).
- Jangan blok halaman detail lama: kalau ARAIM down, panes lain tetap tampil; pane inventories menunjukkan error ramah.

Alternatif lazy (lebih ringan): route AJAX `GET employees/{employee}/inventories` yang dipanggil saat pane dibuka — pilih ini kalau timeout ARAIM sering mengganggu TTFB detail. Default analisa: "saat halaman dibuka"; AJAX tetap memenuhi maksud baca live tanpa salinan.

### 5. UI pane Inventories

Di `detail.blade.php` + `resources/views/employee/components/inventories-pane.blade.php` (nama bebas, konsisten dengan pane lain):

- Step baru di stepper: **Inventories**.
- Tabel/list hanya barang yang sedang dipegang (dari API; sudah difilter Available di ARAIM).
- Kolom minimal: nomor inventaris, nama aset/katalog, kuantitas, tanggal (kalau ada).
- Expand/collapse **riwayat pemegang** per barang (dari `history` API): nama, NIK ARAIM, status transfer, tanggal, urut `id`.
- Empty state: "Tidak ada inventories aktif" (termasuk karyawan belum diikat di ARAIM — API balas kosong).
- Tidak ada tombol edit/transfer/verifikasi.

Permission: ikut akses view employee yang sudah ada (`UserProject::canViewEmployee`). Tidak perlu permission tulis baru.

### 6. Dokumentasi & sync

- Catat keputusan di `docs/decisions.md` bila belum: ikatan = UUID Hero; Hero read-only terhadap inventories.
- Update `docs/architecture.md` singkat: integrasi ARAIM inventories (arah panah: Hero → ARAIM baca; ARAIM → Hero lookup).
- Update `MEMORY.md` satu entri singkat.
- Sync endpoint lookup ke Postman.
- Update checklist di `docs/todo.md`.

### Di luar langkah Hero

- Form verifikasi / opname.
- CRUD atau mutasi inventories dari Hero.
- Menyimpan salinan `inventories` di DB Hero.
- Menyamakan NIK ARAIM saat NIK Hero berubah.
- Mengubah arti "mutasi" Hero (tetap = pindah proyek).

### Urutan implementasi disarankan

1. Lookup API (unblok autocomplete ARAIM) + Postman.
2. Config + `AraimInventoriesClient` + HTTP fake check.
3. Pane UI + wire di `show` (atau AJAX lazy).
4. Uji end-to-end: karyawan terikat di ARAIM → detail Hero menampilkan inventories + riwayat; belum terikat → kosong; ARAIM mati → error pane saja.

### Estimasi lingkup file (Hero)

| Area | File (perkiraan) |
| --- | --- |
| Lookup API | `routes/api.php`, `EmployeeApiController` |
| Config | `config/araim.php`, `.env.example` |
| Client | `app/Services/AraimInventoriesClient.php` |
| Controller | `EmployeeController::show` (± route AJAX) |
| UI | `detail.blade.php`, `components/inventories-pane.blade.php` |
| Docs | analisa ini, `decisions.md`, `architecture.md`, `todo.md`, `MEMORY.md` |
