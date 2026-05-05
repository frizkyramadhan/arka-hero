# ARKA HERO — Referensi API HTTP

Dokumen ini menjelaskan endpoint HTTP JSON yang didefinisikan di `routes/api.php` (prefix otomatis **`/api`**). Endpoint lain yang mengandung kata `api` di path tetapi memakai middleware **`web`** + autentikasi session (misalnya modul flight) dijelaskan di bagian terpisah.

**Versi route file:** selaras dengan `routes/api.php` di codebase. Jika route berubah, sinkronkan dokumen ini.

---

## Daftar isi

1. [Ringkasan](#1-ringkasan)
2. [Autentikasi & header](#2-autentikasi--header)
3. [Error & rate limit](#3-error--rate-limit)
4. [Master data — Positions, Departments, Projects](#4-master-data--positions-departments-projects)
5. [Employees](#5-employees)
6. [Official travels](#6-official-travels)
7. [Letter numbers (integrasi)](#7-letter-numbers-integrasi)
8. [Letter subjects](#8-letter-subjects)
9. [Leave](#9-leave)
10. [Workforce — profil & aktivitas karyawan](#10-workforce--profil--aktivitas-karyawan) — [Konvensi](#konvensi-workforce) · [Postman](#105-tutorial-postman-workforce)
11. [Catatan teknis](#11-catatan-teknis)
12. [Endpoint lain (bukan `routes/api.php`)](#12-endpoint-lain-bukan-routesapiphp)
13. [Changelog dokumen](#13-changelog-dokumen) · [Revisi terbaru](#131-revisi-terbaru)
14. [Ringkasan endpoint](#14-ringkasan-endpoint)

---

## 1. Ringkasan

| Properti            | Nilai                                                                              |
| ------------------- | ---------------------------------------------------------------------------------- |
| **Base path**       | `{APP_URL}/api` — contoh: `https://192.168.32.146:8080` jika aplikasi di subfolder |
| **Format**          | JSON (`Accept: application/json` disarankan)                                       |
| **Middleware grup** | `api`: throttle, validasi API key (jika aktif), route model binding, cache headers |

Variabel lingkungan terkait (`config/services.php`):

| Env               | Fungsi                                                                  |
| ----------------- | ----------------------------------------------------------------------- |
| `API_KEY`         | Secret yang harus dikirim klien jika validasi aktif                     |
| `API_REQUIRE_KEY` | `true` = wajib header API key; `false` = lewati pengecekan (mis. lokal) |

---

## 2. Autentikasi & header

### API key (disarankan untuk integrasi server-to-server)

Salah satu:

| Cara   | Contoh                            |
| ------ | --------------------------------- |
| Header | `X-API-Key: <API_KEY>`            |
| Bearer | `Authorization: Bearer <API_KEY>` |

### Header umum

| Header         | Keterangan                                        |
| -------------- | ------------------------------------------------- |
| `Accept`       | `application/json`                                |
| `Content-Type` | `application/json` untuk body JSON (`POST`/`PUT`) |

### Izin Spatie (permission)

Satu endpoint **Leave** memakai middleware `permission:leave-types.show` (lihat [§9.2](#92-get-apileavetypesleavetype)). Permintaan tanpa user yang login (hanya API key) biasanya **tidak** memiliki permission tersebut — perilaku aktual: **403** dari Spatie jika tidak ada konteks user. Rencanakan autentikasi pengguna (mis. Sanctum) jika endpoint ini dipakai dari aplikasi yang memeriksa role.

---

## 3. Error & rate limit

| HTTP  | Arti umum                                                              |
| ----- | ---------------------------------------------------------------------- |
| `401` | API key tidak ada / salah (jika `API_REQUIRE_KEY=true`)                |
| `403` | Dilarang (mis. permission)                                             |
| `404` | Data tidak ditemukan / tidak ada hasil                                 |
| `422` | Validasi gagal (body biasanya berisi `errors`)                         |
| `429` | Terlalu banyak request (throttle `api`, default per menit per IP/user) |
| `500` | Kesalahan server                                                       |
| `503` | API key belum dikonfigurasi di server                                  |

Struktur error tidak seragam antar controller: ada yang memakai `status: error`, ada `success: false`, ada `message` saja — periksa contoh di setiap bagian.

---

## 4. Master data — Positions, Departments, Projects

Semua berikut adalah **`Route::apiResource`** — `POST`/`PUT`/`PATCH`/`DELETE` **ada di routing**, tetapi implementasi **store / update / destroy** di controller API saat ini **kosong** (`//`) sehingga dapat mengembalikan respons kosong atau tidak sesuai ekspektasi. Untuk integrasi, utamakan **`GET`** sampai implementasi tulis diisi.

### 4.1 Positions

| Method        | Path                        | Nama route          | Keterangan                                                         |
| ------------- | --------------------------- | ------------------- | ------------------------------------------------------------------ |
| `GET`         | `/api/positions`            | `positions.index`   | Daftar jabatan aktif (`position_status = 1`), urut `position_name` |
| `POST`        | `/api/positions`            | `positions.store`   | Tidak diimplementasikan                                            |
| `GET`         | `/api/positions/{position}` | `positions.show`    | Detail jabatan + `department` (model binding)                      |
| `PUT`/`PATCH` | `/api/positions/{position}` | `positions.update`  | Tidak diimplementasikan                                            |
| `DELETE`      | `/api/positions/{position}` | `positions.destroy` | Tidak diimplementasikan                                            |

**Respons sukses (GET):** resource `PositionResource` — antara lain `id`, `position_name`, `position_status`, `department`, `created_at`, `updated_at`.

### 4.2 Departments

| Method        | Path                            | Nama route            | Keterangan                            |
| ------------- | ------------------------------- | --------------------- | ------------------------------------- |
| `GET`         | `/api/departments`              | `departments.index`   | Departemen aktif + relasi `positions` |
| `POST`        | `/api/departments`              | `departments.store`   | Tidak diimplementasikan               |
| `GET`         | `/api/departments/{department}` | `departments.show`    | Detail                                |
| `PUT`/`PATCH` | `/api/departments/{department}` | `departments.update`  | Tidak diimplementasikan               |
| `DELETE`      | `/api/departments/{department}` | `departments.destroy` | Tidak diimplementasikan               |

**Respons:** `DepartmentResource` (`department_name`, `department_status`, `positions`, dll.).

### 4.3 Projects

| Method        | Path                      | Nama route         | Keterangan                                       |
| ------------- | ------------------------- | ------------------ | ------------------------------------------------ |
| `GET`         | `/api/projects`           | `projects.index`   | Proyek `project_status = 1`, urut `project_code` |
| `POST`        | `/api/projects`           | `projects.store`   | Tidak diimplementasikan                          |
| `GET`         | `/api/projects/{project}` | `projects.show`    | Detail                                           |
| `PUT`/`PATCH` | `/api/projects/{project}` | `projects.update`  | Tidak diimplementasikan                          |
| `DELETE`      | `/api/projects/{project}` | `projects.destroy` | Tidak diimplementasikan                          |

**Respons:** `ProjectResource` (`project_code`, `project_name`, `project_status`, dll.).

---

## 5. Employees

Base path: **`/api/employees`**

Semua respons sukses utama memakai koleksi **`AdministrationResource`** (data administrasi karyawan: `nik`, **`employee`** = `{ "fullname" }` bila relasi dimuat, `position`, `project`, dll.), dibungkus `{ "status": "success", "data": [ ... ] }` kecuali `/list`.

### 5.1 `GET /api/employees`

- **Nama route:** `api.employees.index`
- **Fungsi:** Semua administrasi (dengan relasi employee, religion, position, department, project).
- **404:** Jika tidak ada data.

### 5.2 `GET /api/employees/active`

- Administrasi dengan `is_active = 1`.

### 5.3 `GET /api/employees/list`

- **Nama route:** `api.employees.list`
- **Respons:** Array objek `{ "id", "fullname" }` untuk karyawan yang punya minimal satu administrasi aktif (bukan `AdministrationResource`).

### 5.4 `POST /api/employees/search`

- **Body (JSON, opsional):** filter — jika **tidak ada** filter sama sekali, default: hanya administrasi **aktif**.
- **Parameter (semua opsional, LIKE / filter):**

| Field        | Keterangan                         |
| ------------ | ---------------------------------- |
| `nik`        | Filter `nik` administrasi          |
| `position`   | Nama posisi                        |
| `department` | Nama departemen                    |
| `project`    | `project_code` atau `project_name` |
| `name`       | `fullname` employee                |

### 5.5 `GET /api/employees/by-nik/{nik}`

- **Nama route:** `api.employees.show-by-nik`
- **Path `{nik}`:** NIK pada tabel **administrations** (exact match).
- **404:** Tidak ada administrasi, atau tidak ada yang aktif (sama logika dengan `show`).

### 5.6 `GET /api/employees/{id}`

- **`{id}`:** **`employee_id`** (bukan primary key `administrations`).
- **404:** Tidak ada administrasi, atau tidak ada administrasi aktif.

**Urutan route:** Daftarkan route statis (`list`, `active`, `by-nik/...`) sebelum `/{id}` — sudah benar di `routes/api.php`.

---

## 6. Official travels

Base path: **`/api/official-travels`**

Respons sukses umum: `{ "status": "success", "data": ... }` dengan **`OfficialtravelResource`**: antara lain `official_travel_number`, `official_travel_date`, **`approved_at`** (bila terisi, format `Y-m-d H:i:s`), `destination`, `traveler` (+ `nik`, **`employee`** = `{ "fullname" }`), relasi proyek, `stops`, `approval_plans`, dll.

### 6.1 `POST /api/official-travels/search`

- **Wajib:** minimal **satu** filter berisi nilai tidak kosong.
- **Body (JSON):** salah satu atau lebih: `travel_number`, `traveler`, `department`, `project` (semua partial match `LIKE` kecuali nomor sesuai logika controller).
- **Filter hasil:** `status <> 'draft'`, `is_claimed = 'no'`.
- **400:** Tanpa filter valid.
- **404:** Tidak ada data.

### 6.2 `POST /api/official-travels/search-claimed`

- Filter sama seperti search; hasil: perjalanan yang sudah **claimed** (`is_claimed = yes`) dan ada stop dengan `departure_from_destination` terisi.

### 6.3 `POST /api/official-travels/search-claimable`

- Filter sama; hasil: belum claimed, trip selesai (ada `departure_from_destination` pada stops), dll. sesuai query di controller.

### 6.4 `POST /api/official-travels/detail`

- **Body (JSON):** `{ "travel_number": "<string>" }` (required).
- **200:** Satu objek `OfficialtravelResource`.
- **404:** Nomor tidak ditemukan.

### 6.5 `PUT /api/official-travels/claim`

- **Body (JSON):**
    - `official_travel_number` (required, string)
    - `is_claimed` (required): `yes` atau `no`
- **400:** Sudah claimed saat minta claim lagi.
- Mengupdate `claimed_at` saat claim `yes`.

---

## 7. Letter numbers (integrasi)

Base path umum: **`/api/letter-numbers`**

Banyak endpoint mengembalikan `{ "success": true|false, ... }`.

### 7.1 `POST /api/letter-numbers/request`

Membuat nomor surat baru (reserved).

**Body (validasi):**

| Field                | Aturan                                  |
| -------------------- | --------------------------------------- |
| `letter_category_id` | required, exists `letter_categories.id` |
| `letter_date`        | required, date                          |
| `custom_subject`     | optional, string max 200                |
| `administration_id`  | optional, exists `administrations.id`   |
| `project_id`         | optional, exists `projects.id`          |
| `project_code`       | optional, string max 50                 |
| `destination`        | optional, string max 200                |
| `remarks`            | optional, string                        |

**201:** Objek sukses dengan `data`: `id`, `letter_number`, `category_code`, `sequence_number`, `year`, `status`, `letter_date`, `subject`, dll.

**Catatan:** `user_id` diisi dari `auth()->id()` — pada pemanggilan API tanpa user terautentikasi dapat bernilai `null`.

### 7.2 `POST /api/letter-numbers/{id}/mark-as-used`

**Body:** `document_type` (required), `document_id` (required, integer).

### 7.3 `POST /api/letter-numbers/{id}/cancel`

Membatalkan nomor (harus status `reserved`).

### 7.4 `GET /api/letter-numbers/available/{categoryId}`

- **Path:** `categoryId` = ID kategori (`letter_categories.id`).
- **Query:** `limit` (opsional, default 50, max 100).
- **Respons:** Daftar nomor status `reserved` untuk kategori tersebut.

### 7.5 `GET /api/letter-numbers/subjects/{categoryId}`

Daftar subjek surat aktif untuk kategori.

### 7.6 `GET /api/letter-numbers/{id}`

Detail satu nomor surat (banyak field meta: kategori, status, proyek, dokumen terkait, dll.).

### 7.7 `POST /api/letter-numbers/check-availability`

**Body:** `{ "letter_category_id": <id> }`

**Respons:** Statistik jumlah nomor per status untuk **tahun berjalan** dan `next_sequence` (logika di controller).

### 7.8 `POST /api/letter-numbers/preview-next-number`

**Body:**

| Field                | Aturan         |
| -------------------- | -------------- |
| `letter_category_id` | required       |
| `project_id`         | required       |
| `letter_date`        | optional, date |

Mengembalikan preview nomor berikutnya (tanpa menyimpan).

### Route overlap (legacy)

Di `routes/api.php` juga terdapat:

`GET /api/letter-numbers/available/{categoryCode}` → `LetterNumberController@getAvailableNumbers` (lookup by **`category_code`**).

Di Laravel, URI **`/api/letter-numbers/available/{parameter}`** dapat bentrok dengan route **`.../available/{categoryId}`** yang terdaftar lebih dulu. **Integrator disarankan memakai endpoint §7.4 dengan `categoryId` numerik**; jika perilaku kategori string dibutuhkan, verifikasi urutan route di deployment atau gunakan query/filter terpisah setelah diselaraskan di codebase.

---

## 8. Letter subjects

### 8.1 `GET /api/letter-subjects/by-category/{categoryId}`

- **Nama route:** `api.letter-subjects.by-category`
- **Respons:** Array `{ "id", "subject_name" }` (bukan wrapper `status`).

### 8.2 `GET /api/letter-subjects/available/{documentType}/{categoryId}`

- **Respons:** `{ "success": true, "data": [...], "count", "document_type", "category_id" }`.

---

## 9. Leave

Base: **`/api/leave`**

### 9.1 `GET /api/leave/types`

- **Nama route:** `api.leave.types`
- **Query:** `category` (opsional) — filter kategori cuti.
- **Respons:** Array model `LeaveType` aktif (JSON Eloquent), urut nama.

### 9.2 `GET /api/leave/types/{leaveType}`

- **Middleware tambahan:** `permission:leave-types.show`
- **Path:** `{leaveType}` = ID atau binding model `LeaveType`.
- **Respons:** Objek `LeaveType` (tampilan/show).

### 9.3 `GET /api/leave/types/{leaveType}/statistics`

- **Respons contoh:** `total_entitlements`, `total_requests`, `pending_requests`, `approved_requests`, `rejected_requests`, `total_days_taken`, `average_days_per_request`.

### 9.4 `GET /api/leave/employees/{employee}/balance`

- **`{employee}`:** ID karyawan (`employees.id`).
- **Respons:** Array `{ "leave_type_name", "remaining_days" }` per entitlement dengan sisa hari &gt; 0.

---

## 10. Workforce — profil & aktivitas karyawan

**Base:** `/api/workforce` · **`App\Http\Controllers\Api\V1\EmployeeWorkforceApiController`**.

Aturan gabungan ada di **[Konvensi workforce](#konvensi-workforce)**. Subbagian di bawah ini hanya menjelaskan path, perilaku khusus endpoint, dan contoh singkat.

### Konvensi workforce

**Zona waktu & batas harian:** `APP_TIMEZONE` Laravel; filter memakai awal/akhir hari (inklusif).

**Query rentang** — mana yang dipakai tergantung path:

| Konteks                    | Path contoh                                                                                | Parameter                                    | Catatan                                                                                                                                                                              |
| -------------------------- | ------------------------------------------------------------------------------------------ | -------------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| Profil                     | `…/profile`                                                                                | `year` opsional, `month` hanya dengan `year` | Tanpa `year`: administrasi **`is_active`** saja. Ada `year`: respons punya blok `period`; administrasi tidak aktif boleh ikut jika terminasi masih relevan (lihat baris berikutnya). |
| Aktivitas & daftar dokumen | `/activity`, `…/activity`, `…/leave-requests`, `…/official-travels`, `…/overtime-requests` | Lihat urutan di bawah                        | Respons memuat `period` (field `preset` jika pakai shortcut). Endpoint daftar juga mengembalikan `range` (`from`/`to` duplikat untuk kompatibilitas).                                |

**Urutan untuk aktivitas & daftar dokumen:** ① **`period=today`** atau **`period=yesterday`** (mengabaikan `year` / `month` / `from` / `to`) ② **`year`** + opsional **`month`** — jika dikirim bersamaan **`from`/`to`** tanpa **`period`**, yang dipakai **`year`** (+ `month`) ③ **`from`** & **`to`** (`Y-m-d`, `to` ≥ `from`) ④ tanpa filter → **90 hari terakhir**. Satu hari: shortcut **`period`** atau `from` = `to` = tanggal sama.

| Periode  | Contoh                                                  |
| -------- | ------------------------------------------------------- |
| Hari ini | `period=today` atau `from=2026-05-05&to=2026-05-05`     |
| Kemarin  | `period=yesterday` atau `from=2026-05-04&to=2026-05-04` |

**Sumbu filter & status** — baris tanpa timestamp sumbu di bawah tidak ikut hasil:

| Dokumen | Sumbu rentang                                                         | Status                                                                              |
| ------- | --------------------------------------------------------------------- | ----------------------------------------------------------------------------------- |
| Cuti    | **`approved_at`** (wajib terisi)                                      | `approved`, `auto_approved`, `closed`, `cancelled` · opsional **`cancellations[]`** |
| LOT     | **`approved_at`** · traveler administrasi terpasang                   | `approved`, `closed`                                                                |
| Lembur  | **`finished_at`** · minimal satu detail punya **`administration_id`** | `finished`                                                                          |

**`from` / `to` pada daftar dokumen:** dibandingkan ke **`approved_at`** (cuti & LOT) dan **`finished_at`** (lembur), bukan ke tanggal tampilan cuti / `official_travel_date` / `overtime_date`.

**Path UUID vs `by-nik`:** Untuk **`…/employees/{employee}/…`**, administrasi di respons difilter dengan **awal rentang** yang sama dengan query (atau default 90 hari pada endpoint ber-periode; profil tanpa `year` = hanya aktif). Untuk **`…/by-nik/{nik}/…`**: NIK harus aktif, **atau** tidak aktif dengan **`termination_date`** dan **mulai rentang ≤ terminasi**; jika mulai rentang sepenuhnya setelah terminasi → **404**. Respons administrasi: **`WorkforceAdministrationResource`** (`nik`, `is_active`, `termination_date`, `termination_reason`, ringkas posisi/proyek).

**Objek `employee`:** **`WorkforceEmployeeResource`** = hanya **`fullname`** (root `/profile` & `/activity`, nested LOT, dll.). UUID untuk path tidak ada di objek ini — pakai **`GET /api/employees/list`**. `/api/official-travels/…` non-workforce: pola **`employee`** di bawah traveler sama (hanya nama).

**`register_number`:** cuti & lembur — umumnya `YYLV-*` / `YYOT-*`; boleh `null` (data lama).

#### Field JSON ringkas (integrasi)

Field lain (alasan, `approval_plans`, dll.) tetap ada di payload. **Nama karyawan:** root **`employee`** di `/profile` & `/activity`; di dokumen **`employee.fullname`** (cuti/lembur), LOT **`traveler.employee.fullname`**; lembur multi-peserta: juga **`details[].employee.fullname`**.

**Cuti** (`LeaveRequestSummaryResource` — `leave_requests` atau `data` di `/leave-requests`):

| Kebutuhan    | Field / lokasi                                                                          |
| ------------ | --------------------------------------------------------------------------------------- |
| Register     | `register_number`                                                                       |
| NIK / nama   | `administration.nik`, `employee.fullname`                                               |
| Tanggal cuti | `start_date`, `end_date`; opsional `back_to_work_date`                                  |
| Tipe         | `leave_type`: `code`, `name`, `category`                                                |
| Disetujui    | `approved_at`, `status`; `closed_at` bila ada                                           |
| Pembatalan   | `cancellations[]`: `requested_at`, `confirmed_at`, `status`, `reason`, `days_to_cancel` |

**LOT** (`WorkforceOfficialtravelResource`):

| Kebutuhan        | Field / lokasi                                                                 |
| ---------------- | ------------------------------------------------------------------------------ |
| Nomor            | `official_travel_number`                                                       |
| NIK / nama       | `traveler.nik`, `traveler.employee.fullname`                                   |
| Tanggal / tujuan | `official_travel_date`, `destination`, `purpose`, `departure_from`, `duration` |
| Disetujui        | `approved_at`; tahapan di `approval_plans`                                     |

**Lembur** (`OvertimeRequestSummaryResource`):

| Kebutuhan     | Field / lokasi                                                                                                                     |
| ------------- | ---------------------------------------------------------------------------------------------------------------------------------- |
| Register      | `register_number`                                                                                                                  |
| NIK / nama    | `details[].administration.nik`, **`details[].employee.fullname`**; header **`employee.fullname`** = peserta pertama (`sort_order`) |
| Tanggal / jam | `overtime_date`, `details[].time_in`, `details[].time_out`                                                                         |
| Proyek        | `project`                                                                                                                          |
| Sumbu filter  | **`finished_at`** (filter); ada juga `approved_at`                                                                                 |

---

### 10.1 Profil lengkap (employee + administrations)

| Method | Path                                            | Nama route                               |
| ------ | ----------------------------------------------- | ---------------------------------------- |
| `GET`  | `/api/workforce/employees/{employee}/profile`   | `api.workforce.employees.profile`        |
| `GET`  | `/api/workforce/employees/by-nik/{nik}/profile` | `api.workforce.employees.profile-by-nik` |

`{employee}` = UUID **`employees.id`**; `{nik}` = NIK **`administrations`**. Query & respons **`period`** / administrasi: [Konvensi — profil](#konvensi-workforce).

**Respons:** `success`, `data.employee`, `data.administrations`.

---

### 10.2 Aktivitas agregat

**Path:** `GET /api/workforce/activity` · **`api.workforce.activity`**

Hanya karyawan yang punya **minimal satu** cuti / LOT / lembur yang memenuhi [filter & sumbu](#konvensi-workforce) di periode tersebut. Query rentang: [baris Aktivitas & daftar dokumen](#konvensi-workforce).

**Respons tambahan:** `aggregate_summary` (`employees_with_activity`, total per jenis dokumen); `data[]`: tiap elemen ada **`employee_id`** + blok sama seperti [§10.3](#103-timeline-satu-karyawan) (`employee`, `administrations`, `summary`, array dokumen). Urut `fullname` naik. Tanpa dokumen: `count: 0`, array kosong.

**Contoh:** `?year=2026&month=3` · `?period=yesterday`

---

### 10.3 Timeline satu karyawan

| Method | Path                                             | Nama route                                |
| ------ | ------------------------------------------------ | ----------------------------------------- |
| `GET`  | `/api/workforce/employees/{employee}/activity`   | `api.workforce.employees.activity`        |
| `GET`  | `/api/workforce/employees/by-nik/{nik}/activity` | `api.workforce.employees.activity-by-nik` |

Query rentang: sama [§10.2](#102-aktivitas-agregat). **Respons:** `period`, `employee`, `administrations`, `summary`, `leave_requests`, `official_travels`, `overtime_requests`.

**Contoh:** `/api/workforce/employees/by-nik/13100/activity?year=2026&month=3` · `?period=today`

---

### 10.4 Daftar per jenis (cuti / LOT / lembur)

Rentang query: [Konvensi — urutan parameter](#konvensi-workforce). **`from`/`to`** memakai sumbu yang sama seperti tabel sumbu ([Konvensi](#konvensi-workforce)).

**Global (tanpa gate NIK)**

| Method | Path                               | Nama route                        |
| ------ | ---------------------------------- | --------------------------------- |
| `GET`  | `/api/workforce/leave-requests`    | `api.workforce.leave-requests`    |
| `GET`  | `/api/workforce/official-travels`  | `api.workforce.official-travels`  |
| `GET`  | `/api/workforce/overtime-requests` | `api.workforce.overtime-requests` |

**LOT global:** traveler terpasang & **`employee_id`** traveler terisi. **Lembur global:** minimal satu detail dengan **`administration_id`**.

**Per karyawan**

| Method | Path                                                      | Nama route                                  |
| ------ | --------------------------------------------------------- | ------------------------------------------- |
| `GET`  | `/api/workforce/employees/{employee}/leave-requests`      | `api.workforce.employees.leave-requests`    |
| `GET`  | `/api/workforce/employees/by-nik/{nik}/leave-requests`    | `api.workforce.employees.leave-by-nik`      |
| `GET`  | `/api/workforce/employees/{employee}/official-travels`    | `api.workforce.employees.official-travels`  |
| `GET`  | `/api/workforce/employees/by-nik/{nik}/official-travels`  | `api.workforce.employees.lot-by-nik`        |
| `GET`  | `/api/workforce/employees/{employee}/overtime-requests`   | `api.workforce.employees.overtime-requests` |
| `GET`  | `/api/workforce/employees/by-nik/{nik}/overtime-requests` | `api.workforce.employees.overtime-by-nik`   |

**Respons:** `success`, `period`, `range`, `count`, `data` (koleksi ringkas dokumen sesuai jenis).

---

### 10.5 Tutorial Postman (Workforce)

Panduan singkat menguji endpoint **`/api/workforce/...`** di Postman.

#### Siapkan environment

1. Buat **Environment** baru (mis. `ARKA HERO Local`).
2. Tambah variabel:

| Variable   | Contoh nilai                                                           |
| ---------- | ---------------------------------------------------------------------- |
| `BASE_URL` | `http://192.168.32.146:8080` (sesuaikan `APP_URL` / path Laragon Anda) |
| `API_KEY`  | Nilai `API_KEY` dari `.env` (kosongkan jika `API_REQUIRE_KEY=false`)   |

Pastikan di server: **`API_REQUIRE_KEY=true`** jika ingin menguji header wajib.

#### Header untuk request Workforce

**Opsi A — tab Authorization**

- Type: **Bearer Token**
- Token: `{{API_KEY}}`  
  (Ini **bukan** token user Sanctum; ini secret yang sama dengan `API_KEY` di `.env`.)

**Opsi B — tab Headers**

| Key         | Value              |
| ----------- | ------------------ |
| `X-API-Key` | `{{API_KEY}}`      |
| `Accept`    | `application/json` |

#### Contoh request (`GET`)

Gunakan `{{BASE_URL}}` dari environment.

**A. Profil karyawan lewat NIK (mis. 13100)**

- URL: `{{BASE_URL}}/api/workforce/employees/by-nik/13100/profile`
- Method: `GET`
- Opsional **`year`**, **`month`** — dampaknya ke **`administrations`** dan blok **`period`**; penjelasan di [Konvensi — kolom Profil](#konvensi-workforce).

**Agregat — aktivitas semua karyawan (tanpa NIK/UUID path)**

- URL: `{{BASE_URL}}/api/workforce/activity`
- Params: seperti [Konvensi — Aktivitas & daftar dokumen](#konvensi-workforce): mis. **`year`** (+ **`month`**), **`period=today|yesterday`**, **`from`/`to`**, atau kosong (90 hari).
- Respons: `aggregate_summary`, array `data[]` (tiap elemen punya `employee_id` + blok sama seperti aktivitas tunggal). Hanya karyawan yang punya minimal satu cuti/LOT/lembur sesuai filter di periode itu.

**Daftar dokumen global (tanpa path karyawan)** — parameter rentang sama **[Konvensi](#konvensi-workforce)** (termasuk **`period`**):

| URL                                            |
| ---------------------------------------------- |
| `{{BASE_URL}}/api/workforce/leave-requests`    |
| `{{BASE_URL}}/api/workforce/official-travels`  |
| `{{BASE_URL}}/api/workforce/overtime-requests` |

**B. Aktivitas satu bulan (contoh Maret 2026) — satu karyawan lewat NIK**

- URL: `{{BASE_URL}}/api/workforce/employees/by-nik/13100/activity`
- Tab **Params**:

| Key     | Value  |
| ------- | ------ |
| `year`  | `2026` |
| `month` | `3`    |

Ini memenuhi skenario: di bulan Maret ada cuti / LOT / lembur atau tidak, plus detail di `leave_requests`, `official_travels`, `overtime_requests`, dan `summary`. Sumbu & status dokumen: [Konvensi workforce](#konvensi-workforce).

**C. Aktivitas satu tahun penuh (tanpa bulan)**

- URL: sama `/activity`, Params: hanya `year=2026` (tanpa `month`).

**D. Hanya daftar cuti (rentang tanggal atau tahun/bulan)**

- URL: `{{BASE_URL}}/api/workforce/employees/by-nik/13100/leave-requests`
- Params (opsional) — **salah satu** pola:

| Mode      | Params                                 | Keterangan                                               |
| --------- | -------------------------------------- | -------------------------------------------------------- |
| Shortcut  | `period=today` atau `period=yesterday` | Satu hari server (mengabaikan param rentang lain)        |
| Kalender  | `year=2026`, `month=3`                 | Satu bulan                                               |
| Kalender  | `year=2026` (tanpa `month`)            | Seluruh tahun                                            |
| Eksplisit | `from=2026-03-01`, `to=2026-03-31`     | Rentang bebas                                            |
| Satu hari | `from=2026-05-05`, `to=2026-05-05`     | Mis. **hari ini** (tanggal sama; sesuaikan tanggal)      |
| Satu hari | `from=2026-05-04`, `to=2026-05-04`     | Mis. **kemarin**                                         |
| Default   | _(kosong)_                             | **90 hari terakhir** (dibandingkan ke **`approved_at`**) |

Respons menyertakan `period`, `range`, dan pada tiap cuti dapat ada `cancellations` (array, bisa kosong). Hanya cuti dengan **`approved_at`** terisi dan jatuh dalam rentang.

**E. Hanya perjalanan dinas (LOT)**

- URL: `{{BASE_URL}}/api/workforce/employees/by-nik/13100/official-travels`
- Params: sama seperti **D** + opsi **`period`**; lihat [Konvensi](#konvensi-workforce). LOT: **`approved_at`**. Status **approved** / **closed**.

**F. Hanya lembur**

- URL: `{{BASE_URL}}/api/workforce/employees/by-nik/13100/overtime-requests`
- Params: sama seperti **D** + **`period`**. Lembur: filter **`finished_at`**. Hanya **`finished`**.

**G. Pakai ID karyawan (UUID), bukan NIK**

Ganti path:

- `.../api/workforce/employees/<UUID_KARYAWAN>/profile`
- `.../activity`, `.../leave-requests`, dll.

UUID bisa diambil dari **`GET /api/employees/list`** (field `id` per karyawan) atau dari database. Objek `employee` di respons workforce **tidak** menyertakan `id`.

#### Koleksi Postman (opsional)

1. **New Collection** → nama mis. `ARKA HERO - Workforce`.
2. Di level **Collection** → **Variables**: `BASE_URL`, `API_KEY`.
3. **Authorization** collection: Bearer `{{API_KEY}}` agar semua request mewarisi.
4. Duplikat request per endpoint di atas; query rentang mengikuti [Konvensi](#konvensi-workforce) (`year`/`month`, `period`, `from`/`to`).

#### Contoh request mentah

```http
GET {{BASE_URL}}/api/workforce/employees/by-nik/13100/activity?year=2026&month=3
X-API-Key: <isi_API_KEY_anda>
Accept: application/json
```

#### Troubleshooting cepat

| Gejala                | Yang dicek                                                                                                                                    |
| --------------------- | --------------------------------------------------------------------------------------------------------------------------------------------- |
| `401` + pesan API key | Header `X-API-Key` atau Bearer; `API_KEY` di `.env`; `API_REQUIRE_KEY=true`.                                                                  |
| `404` NIK             | NIK tidak ada, tidak aktif untuk periode tanpa **`termination_date`** yang mengizinkan akses, atau terminasi sebelum awal rentang permintaan. |
| `404` employee UUID   | Pastikan UUID benar (bukan salah salin).                                                                                                      |
| URL 404               | Path harus **`/api/workforce/...`** (ada prefix `api`).                                                                                       |
| `429`                 | Throttle; tunggu atau sesuaikan limit di server.                                                                                              |

---

## 11. Catatan teknis

1. **Throttle:** Grup `api` memakai limiter `api` (lihat `RouteServiceProvider::configureRateLimiting`).
2. **CORS:** Jika dipanggil dari browser di domain lain, sesuaikan `config/cors.php`.
3. **Konsistensi JSON:** Beberapa endpoint memakai pola `status`/`success` berbeda; saat membangun klien, normalisasi di lapisan aplikasi.
4. **Duplikasi folder controller:** Pastikan perubahan dilakukan pada namespace `App\Http\Controllers\Api\V1` yang dipakai autoload (hindari duplikat `v1` vs `V1` di filesystem).

---

## 12. Endpoint lain (bukan `routes/api.php`)

Route berikut **bukan** bagian dari grup `api` di `RouteServiceProvider`, tetapi path-nya mengandung `api` dan mengembalikan JSON untuk UI terautentikasi:

| Method | Path                                       | Autentikasi    |
| ------ | ------------------------------------------ | -------------- |
| `GET`  | `/flight/requests/api/employees`           | `web` + `auth` |
| `GET`  | `/flight/requests/api/leave-requests`      | `web` + `auth` |
| `GET`  | `/flight/requests/api/official-travels`    | `web` + `auth` |
| `GET`  | `/flight/my-requests/api/leave-requests`   | `web` + `auth` |
| `GET`  | `/flight/my-requests/api/official-travels` | `web` + `auth` |

Respons **`.../api/leave-requests`** (flight): setiap elemen memuat `id`, **`register_number`**, `text` (label Select2; diawali nomor register atau fallback `id`), dan `employee_data`.

Untuk integrasi **eksternal** server-to-server, gunakan endpoint di **§1–§10** dengan **API key**; endpoint flight di atas memerlukan **session/login** web, bukan sekadar `API_KEY`.

---

## 13. Changelog dokumen

| Tanggal    | Perubahan                                                                                                                                                                                                                                                             |
| ---------- | --------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------- |
| 2026-04-10 | Dokumen awal berdasarkan `routes/api.php` dan controller `Api\V1`                                                                                                                                                                                                     |
| 2026-04-10 | Bagian **Workforce** (`/api/workforce/...`) — profil, aktivitas, cuti/LOT/lembur                                                                                                                                                                                      |
| 2026-04-10 | **§10.5** Tutorial Postman untuk Workforce                                                                                                                                                                                                                            |
| 2026-04-17 | **§10** Workforce: filter status (cuti, LOT, lembur); query `year`/`month` untuk profil & endpoint terpisah; respons `period` + `range`; field `cancellations` pada ringkasan cuti                                                                                    |
| 2026-04-17 | **§10.5** Postman: contoh params `year`/`month` untuk leave/LOT/overtime                                                                                                                                                                                              |
| 2026-04-17 | **§10** Workforce: filter rentang memakai **`approved_at`** (cuti & LOT) dan **`finished_at`** (lembur); baris dengan timestamp tersebut null tidak ikut                                                                                                              |
| 2026-04-17 | **§10** Workforce: data karyawan dipangkas lewat **`WorkforceEmployeeResource`**; LOT workforce memakai **`WorkforceOfficialtravelResource`**                                                                                                                         |
| 2026-04-20 | **§10** Workforce: gate **NIK aktif / terminasi** untuk `by-nik`; **`WorkforceAdministrationResource`** (`termination_date`, `termination_reason`)                                                                                                                    |
| 2026-05-05 | **§5**, **§6**, **§10**: objek tersemat **`employee`** di JSON API hanya **`fullname`** (`WorkforceEmployeeResource`, `AdministrationResource`, nested LOT di `OfficialtravelResource`)                                                                               |
| 2026-05-05 | **§10** Workforce: [field JSON ringkas](#field-json-ringkas-integrasi) per cuti / LOT / lembur; contoh rentang **hari ini** & **kemarin** (`from`=`to`); koreksi penempatan `employee` di `/profile` vs `/activity`; **§6** respons LOT menyertakan **`approved_at`** |
| 2026-05-06 | **§10**: `GET /api/workforce/activity` (agregat); subbagian **10.1–10.5** + **[Konvensi workforce](#konvensi-workforce)**; Postman: contoh agregat                                                                                                                    |
| 2026-05-07 | **§14** [Ringkasan endpoint](#14-ringkasan-endpoint) — daftar path singkat selaras `routes/api.php`                                                                                                                                                                   |
| 2026-05-08 | **§10** & **§14**: `GET /api/workforce/leave-requests`, `/official-travels`, `/overtime-requests` (daftar global semua karyawan, query rentang sama §10.4)                                                                                                            |
| 2026-05-10 | **§10** susun ulang: subset **[Konvensi workforce](#konvensi-workforce)**; subsection **10.1–10.4** hanya path & perilaku beda endpoint; Tutorial Postman & **§14** merujuk Konvensi                                                                                  |
| 2026-05-10 | **§10** Workforce: query **`period=today`** / **`period=yesterday`** untuk aktivitas agregat, timeline, dan daftar cuti/LOT/lembur; prioritas di atas `year`/`from`/`to`; **`period.preset`** di JSON respons                                                         |

### 13.1 Revisi terbaru

**2026-05-10 — Susun ulang §10 Workforce**

- Satu blok **[Konvensi workforce](#konvensi-workforce)** merangkum rentang query, sumbu filter, UUID/`by-nik`, `employee` / `register_number`, dan tabel **[field JSON ringkas](#field-json-ringkas-integrasi)**. Subbagian **10.1–10.4** hanya membahas perbedaan endpoint; daftar isi punya jangkar cepat ke Konvensi.

**2026-05-09 — Nama karyawan di ringkasan cuti & lembur**

- **`LeaveRequestSummaryResource`**: field **`employee`** = `{ "fullname" }` dari pemohon (`employee_id`), dengan eager load di controller workforce.
- **`OvertimeRequestSummaryResource`**: **`employee.fullname`** pada header = peserta **`details`** pertama (`sort_order`); setiap **`details[]`** berisi **`employee.fullname`** peserta baris tersebut.

**2026-05-08 — Workforce: daftar dokumen global**

- Tanpa path karyawan: **`api.workforce.leave-requests`**, **`api.workforce.official-travels`**, **`api.workforce.overtime-requests`** — param & respons selaras endpoint per karyawan pada §10.4, tanpa gate NIK workforce.

**2026-05-10 — Workforce: shortcut `period=today` / `yesterday`**

- Endpoint aktivitas (**`GET /api/workforce/activity`**, **`.../employees/.../activity`**) serta daftar dokumen (**`leave-requests`**, **`official-travels`**, **`overtime-requests`**) mendukung query **`period`**. Respon **`period`** dapat berisi **`preset`**: **`today`** \| **`yesterday`** bersama **`from`** / **`to`**; **`year`** dan **`month`** **`null`** pada mode ini.

**2026-05-06 — Aktivitas workforce agregat**

- Route **`GET /api/workforce/activity`** (`api.workforce.activity`): query **`year`** wajib jika **`period`** tidak digunakan, **`month`** opsional (atau **`period=today|yesterday`**); respons berisi **`data[]`** dengan **`employee_id`** + payload sama seperti aktivitas tunggal, plus **`aggregate_summary`**.

**2026-05-05 — Objek `employee` minimal**

- **`WorkforceEmployeeResource`**, nested **`employee`** pada **`OfficialtravelResource`** (termasuk workforce LOT), dan **`AdministrationResource`**: hanya **`{ "fullname": "..." }`** (tidak ada `id` di objek tersebut). UUID untuk path workforce dapat di-resolve lewat **`GET /api/employees/list`** atau database.

**2026-05-05 — Workforce & LOT JSON**

- Dokumentasi workforce: tabel ringkas field integrasi per jenis dokumen; pola query satu hari untuk **hari ini** / **kemarin**; klarifikasi bahwa root `employee` ada di `/profile` dan `/activity`, bukan di respons murni `/leave-requests` / `/overtime-requests`.
- **`OfficialtravelResource`** (termasuk workforce LOT): field **`approved_at`** diekspos bila tersedia (selaras dengan filter `approved_at` di API workforce).

**2026-04-17 — Workforce API (`EmployeeWorkforceApiController`)**

- **Perjalanan dinas:** hanya dikembalikan jika `status` ∈ {`approved`, `closed`}.
- **Cuti:** hanya `approved`, `auto_approved`, `closed`, `cancelled`; setiap item memuat relasi **`cancellations`** (untuk pembatalan partial/full) di `LeaveRequestSummaryResource`.
- **Lembur:** hanya permintaan dengan `status` = `finished`.
- **Sumbu tanggal (filter `year`/`month`/`from`/`to` / default 90 hari):** cuti dan LOT dibandingkan ke kolom **`approved_at`** (harus terisi); lembur dibandingkan ke **`finished_at`** (harus terisi). Urutan hasil: cuti & LOT `approved_at` DESC, lembur `finished_at` DESC.
- **Query:** endpoint profil mendukung **`year`** / **`month`** (opsional, informatif di respons). Endpoint `leave-requests`, `official-travels`, dan `overtime-requests` mendukung **`year`** + **`month`** opsional sebagai alternatif dari **`from`** / **`to`**, dengan default tetap 90 hari terakhir bila tidak ada filter.
- **Respons** endpoint terpisah: objek **`period`** (`year`, `month`, `from`, `to`) dan **`range`** (`from`, `to`).
- **Karyawan di workforce:** `WorkforceEmployeeResource` — hanya **`fullname`**. LOT di workforce: `WorkforceOfficialtravelResource`; nested **`employee`** untuk LOT non-workforce sama (hanya nama).
- **`register_number`:** disertakan pada objek ringkas cuti & lembur (`LeaveRequestSummaryResource`, `OvertimeRequestSummaryResource`); pada flight JSON leave list juga field `register_number` + prefix di `text`.
- **Gate NIK workforce:** `by-nik` — aktif saja, atau tidak aktif jika `termination_date` terisi dan **awal rentang ≤ terminasi**; profil tanpa `year` — NIK harus aktif. Administrasi di respons: **`WorkforceAdministrationResource`** dengan terminasi.

---

## 14. Ringkasan endpoint

Daftar ringkas mengikuti **`routes/api.php`** (semua path berawalan **`/api`**). Detail validasi dan respons ada di bagian utama dokumen.

### Master (`Route::apiResource`)

| Resource      | Catatan |
| ------------- | ------- | ------------------------ | --- | ----- | --------------------------------- |
| `positions`   | `GET    | POST /positions`, `GET   | PUT | PATCH | DELETE /positions/{position}`     |
| `departments` | `GET    | POST /departments`, `GET | PUT | PATCH | DELETE /departments/{department}` |
| `projects`    | `GET    | POST /projects`, `GET    | PUT | PATCH | DELETE /projects/{project}`       |

### Workforce

**Rentang waktu:** Aktivitas (`/activity`) & daftar cuti/LOT/lembur — `period=today|yesterday`, atau `year` (+ `month`), atau `from` & `to`; tanpa itu default 90 hari (`period` mengalahkan). Profil (`/profile`) — opsional `year` / `month`. Rinci: [Konvensi workforce](#konvensi-workforce) di [§10](#10-workforce--profil--aktivitas-karyawan).

| Metode | Path                                                  |
| ------ | ----------------------------------------------------- |
| `GET`  | `/workforce/activity`                                 |
| `GET`  | `/workforce/leave-requests`                           |
| `GET`  | `/workforce/official-travels`                         |
| `GET`  | `/workforce/overtime-requests`                        |
| `GET`  | `/workforce/employees/by-nik/{nik}/profile`           |
| `GET`  | `/workforce/employees/by-nik/{nik}/activity`          |
| `GET`  | `/workforce/employees/by-nik/{nik}/leave-requests`    |
| `GET`  | `/workforce/employees/by-nik/{nik}/official-travels`  |
| `GET`  | `/workforce/employees/by-nik/{nik}/overtime-requests` |
| `GET`  | `/workforce/employees/{employee}/profile`             |
| `GET`  | `/workforce/employees/{employee}/activity`            |
| `GET`  | `/workforce/employees/{employee}/leave-requests`      |
| `GET`  | `/workforce/employees/{employee}/official-travels`    |
| `GET`  | `/workforce/employees/{employee}/overtime-requests`   |

### Employees

| Metode | Path                      |
| ------ | ------------------------- |
| `GET`  | `/employees`              |
| `GET`  | `/employees/list`         |
| `GET`  | `/employees/active`       |
| `POST` | `/employees/search`       |
| `GET`  | `/employees/by-nik/{nik}` |
| `GET`  | `/employees/{id}`         |

### Official travels

| Metode | Path                                 |
| ------ | ------------------------------------ |
| `POST` | `/official-travels/search`           |
| `POST` | `/official-travels/search-claimed`   |
| `POST` | `/official-travels/search-claimable` |
| `POST` | `/official-travels/detail`           |
| `PUT`  | `/official-travels/claim`            |

### Letter numbers

| Metode | Path                                                                              |
| ------ | --------------------------------------------------------------------------------- |
| `POST` | `/letter-numbers/request`                                                         |
| `POST` | `/letter-numbers/{id}/mark-as-used`                                               |
| `POST` | `/letter-numbers/{id}/cancel`                                                     |
| `GET`  | `/letter-numbers/available/id/{categoryId}`                                       |
| `GET`  | `/letter-numbers/subjects/{categoryId}`                                           |
| `GET`  | `/letter-numbers/{id}`                                                            |
| `POST` | `/letter-numbers/check-availability`                                              |
| `POST` | `/letter-numbers/preview-next-number`                                             |
| `GET`  | `/letter-numbers/available/{categoryCode}` _(legacy; potensi bentrok — lihat §7)_ |

### Letter subjects

| Metode | Path                                                     |
| ------ | -------------------------------------------------------- |
| `GET`  | `/letter-subjects/by-category/{categoryId}`              |
| `GET`  | `/letter-subjects/available/{documentType}/{categoryId}` |

### Leave

| Metode | Path                                  |
| ------ | ------------------------------------- |
| `GET`  | `/leave/types`                        |
| `GET`  | `/leave/types/{leaveType}`            |
| `GET`  | `/leave/types/{leaveType}/statistics` |
| `GET`  | `/leave/employees/{employee}/balance` |

### Di luar tabel (`web` + session)

Lihat [§12](#12-endpoint-lain-bukan-routesapiphp) — path flight / my-requests (bukan grup `api` standar).
