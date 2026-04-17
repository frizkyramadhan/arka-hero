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
10. [Workforce — profil & aktivitas karyawan](#10-workforce--profil--aktivitas-karyawan) · [Tutorial Postman](#104-tutorial-postman-workforce)
11. [Catatan teknis](#11-catatan-teknis)
12. [Endpoint lain (bukan `routes/api.php`)](#12-endpoint-lain-bukan-routesapiphp)
13. [Changelog dokumen](#13-changelog-dokumen) · [Revisi terbaru](#131-revisi-terbaru)

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

Semua respons sukses utama memakai koleksi **`AdministrationResource`** (data administrasi karyawan: `nik`, `employee`, `position`, `project`, dll.), dibungkus `{ "status": "success", "data": [ ... ] }` kecuali `/list`.

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

Respons sukses umum: `{ "status": "success", "data": ... }` dengan **`OfficialtravelResource`** (struktur detail perjalanan dinas).

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

Base path: **`/api/workforce`**

Controller: `App\Http\Controllers\Api\V1\EmployeeWorkforceApiController`.

**Sumber data, filter status, dan sumbu tanggal** (parameter `year` / `month` / `from` / `to` serta default 90 hari memakai **batas inklusif** pada datetime `00:00:00`–`23:59:59` setempat):

| Jenis | Filter rentang (sumbu waktu) | Status yang dikembalikan |
| ----- | ---------------------------- | ------------------------ |
| **Cuti** | Kolom **`approved_at`** jatuh dalam periode (wajib **not null**) | `approved`, `auto_approved`, `closed`, `cancelled`. Setiap item ringkas dapat menyertakan array **`cancellations`** (pengajuan pembatalan partial/full: `days_to_cancel`, `reason`, `status`, dll.). |
| **Perjalanan dinas (LOT)** | Kolom **`approved_at`** dalam periode; `traveler` = administrasi karyawan (wajib **not null**) | Hanya **`approved`** dan **`closed`**. |
| **Lembur** | Kolom **`finished_at`** dalam periode; ada baris `overtime_request_details` dengan `administration_id` milik karyawan (wajib **not null**) | Hanya **`finished`**. |

Baris dengan `approved_at` / `finished_at` kosong tidak muncul di hasil workforce meskipun statusnya cocok.

**Objek `employee` (karyawan) di semua respons workforce** memakai **`WorkforceEmployeeResource`**: hanya field `id`, `fullname`, `emp_pob`, `emp_dob` (tanggal `Y-m-d` jika tersedia), `gender`, `address`, `phone`.  
Ini berlaku untuk `data.employee` (profil & aktivitas) dan untuk nested `traveler.employee` / `follower.employee` pada **`WorkforceOfficialtravelResource`** (LOT workforce). Endpoint `/api/official-travels/...` lain tetap memakai `OfficialtravelResource` penuh untuk nested employee.

### 10.1 Profil lengkap (employee + administrations)

| Method | Path                                            | Nama route                               | Keterangan                                                  |
| ------ | ----------------------------------------------- | ---------------------------------------- | ----------------------------------------------------------- |
| `GET`  | `/api/workforce/employees/{employee}/profile`   | `api.workforce.employees.profile`        | `{employee}` = UUID `employees.id`                          |
| `GET`  | `/api/workforce/employees/by-nik/{nik}/profile` | `api.workforce.employees.profile-by-nik` | `{nik}` = NIK pada tabel `administrations` (contoh `13100`) |

**Query (opsional):** `year` (integer `2000`–`2100`), `month` (`1`–`12`, hanya bersama `year`). Tidak memfilter isi profil; jika `year` ada, respons dapat menyertakan blok **`period`** (`year`, `month`, `from`, `to`) untuk konsistensi dengan endpoint lain.

**Respons:** `success`, `data.employee` (`WorkforceEmployeeResource`), `data.administrations` (`AdministrationResource` collection).

### 10.2 Timeline aktivitas (cuti + LOT + lembur per bulan/tahun)

| Method | Path                                             | Nama route                                |
| ------ | ------------------------------------------------ | ----------------------------------------- |
| `GET`  | `/api/workforce/employees/{employee}/activity`   | `api.workforce.employees.activity`        |
| `GET`  | `/api/workforce/employees/by-nik/{nik}/activity` | `api.workforce.employees.activity-by-nik` |

**Query (wajib/opsional):**

| Parameter | Aturan                                                                                     |
| --------- | ------------------------------------------------------------------------------------------ |
| `year`    | **Wajib** (integer, mis. `2026`)                                                           |
| `month`   | Opsional (`1`–`12`). Jika diisi → hanya bulan tersebut; jika tidak → seluruh tahun `year`. |

**Respons:** `period` (`year`, `month`, `from`, `to`), `employee` (`WorkforceEmployeeResource`), `administrations`, `summary` (jumlah per jenis dokumen), `leave_requests` (ringkas + **`cancellations`** bila ada), `official_travels` (`WorkforceOfficialtravelResource`), `overtime_requests` (ringkas + detail baris lembur). Isi dokumen memakai filter status dan sumbu tanggal seperti tabel di atas (`approved_at` / `finished_at`).

**Contoh (Maret, NIK 13100):**  
`GET /api/workforce/employees/by-nik/13100/activity?year=2026&month=3`

### 10.3 Endpoint terpisah per jenis dokumen

**Rentang periode** — salah satu mode berikut:

1. **`year`** (opsional sebagai pengganti rentang eksplisit): kalender penuh untuk tahun tersebut; **`month`** opsional (`1`–`12`) untuk satu bulan. Jika `month` dipakai, **`year` wajib**.
2. **`from`** dan **`to`** (`Y-m-d`, `to` ≥ `from`).
3. Tanpa keduanya: default **90 hari terakhir** sampai hari ini.

Jika **`year`** dikirim bersamaan dengan `from`/`to`, backend memakai **`year`** (dan `month` jika ada) untuk menghitung rentang.

**Makna `from` / `to` bagi tiap jenis:** sama untuk ketiga endpoint — dipetakan ke inklusi pada **`leave_requests.approved_at`**, **`officialtravels.approved_at`**, dan **`overtime_requests.finished_at`** (bukan lagi overlap tanggal cuti atau `official_travel_date` / `overtime_date`).

**Respons umum (cuti / LOT / lembur):** `success`, **`period`** (`year`, `month` dapat `null`, `from`, `to`), **`range`** (`from`, `to`, sama dengan periode efektif — disalin untuk kompatibilitas klien lama), `count`, `data`.

| Method | Path                                                      | Nama route                                  |
| ------ | --------------------------------------------------------- | ------------------------------------------- |
| `GET`  | `/api/workforce/employees/{employee}/leave-requests`      | `api.workforce.employees.leave-requests`    |
| `GET`  | `/api/workforce/employees/by-nik/{nik}/leave-requests`    | `api.workforce.employees.leave-by-nik`      |
| `GET`  | `/api/workforce/employees/{employee}/official-travels`    | `api.workforce.employees.official-travels`  |
| `GET`  | `/api/workforce/employees/by-nik/{nik}/official-travels`  | `api.workforce.employees.lot-by-nik`        |
| `GET`  | `/api/workforce/employees/{employee}/overtime-requests`   | `api.workforce.employees.overtime-requests` |
| `GET`  | `/api/workforce/employees/by-nik/{nik}/overtime-requests` | `api.workforce.employees.overtime-by-nik`   |

### 10.4 Tutorial Postman (Workforce)

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
- Opsional Params: `year`, `month` (hanya mencerminkan periode di respons, tidak memfilter profil).
- Harus dapat JSON `success`, `data.employee`, `data.administrations`.

**B. Aktivitas satu bulan (contoh Maret 2026)**

- URL: `{{BASE_URL}}/api/workforce/employees/by-nik/13100/activity`
- Tab **Params**:

| Key     | Value  |
| ------- | ------ |
| `year`  | `2026` |
| `month` | `3`    |

Ini memenuhi skenario: di bulan Maret ada cuti / LOT / lembur atau tidak, plus detail di `leave_requests`, `official_travels`, `overtime_requests`, dan `summary`. Pemfilteran periode: **`approved_at`** (cuti & LOT) dan **`finished_at`** (lembur). Status: seperti tabel §10.

**C. Aktivitas satu tahun penuh (tanpa bulan)**

- URL: sama `/activity`, Params: hanya `year=2026` (tanpa `month`).

**D. Hanya daftar cuti (rentang tanggal atau tahun/bulan)**

- URL: `{{BASE_URL}}/api/workforce/employees/by-nik/13100/leave-requests`
- Params (opsional) — **salah satu** pola:

| Mode | Params | Keterangan |
| ---- | ------ | ---------- |
| Kalender | `year=2026`, `month=3` | Satu bulan |
| Kalender | `year=2026` (tanpa `month`) | Seluruh tahun |
| Eksplisit | `from=2026-03-01`, `to=2026-03-31` | Rentang bebas |
| Default | *(kosong)* | **90 hari terakhir** (dibandingkan ke **`approved_at`**) |

Respons menyertakan `period`, `range`, dan pada tiap cuti dapat ada `cancellations` (array, bisa kosong). Hanya cuti dengan **`approved_at`** terisi dan jatuh dalam rentang.

**E. Hanya perjalanan dinas (LOT)**

- URL: `{{BASE_URL}}/api/workforce/employees/by-nik/13100/official-travels`
- Params: sama seperti **D** (`year`/`month` atau `from`/`to` atau default 90 hari). Filter ke **`approved_at`**. Hanya status **approved** dan **closed**.

**F. Hanya lembur**

- URL: `{{BASE_URL}}/api/workforce/employees/by-nik/13100/overtime-requests`
- Params: sama seperti **D**. Filter ke **`finished_at`**. Hanya header lembur berstatus **finished**.

**G. Pakai ID karyawan (UUID), bukan NIK**

Ganti path:

- `.../api/workforce/employees/<UUID_KARYAWAN>/profile`
- `.../activity`, `.../leave-requests`, dll.

UUID bisa diambil dari respons profil (`data.employee.id`) atau dari database.

#### Koleksi Postman (opsional)

1. **New Collection** → nama mis. `ARKA HERO - Workforce`.
2. Di level **Collection** → **Variables**: `BASE_URL`, `API_KEY`.
3. **Authorization** collection: Bearer `{{API_KEY}}` agar semua request mewarisi.
4. Duplikat request per endpoint di atas; untuk `activity` tambahkan query params `year` / `month`.

#### Contoh request mentah

```http
GET {{BASE_URL}}/api/workforce/employees/by-nik/13100/activity?year=2026&month=3
X-API-Key: <isi_API_KEY_anda>
Accept: application/json
```

#### Troubleshooting cepat

| Gejala                | Yang dicek                                                                   |
| --------------------- | ---------------------------------------------------------------------------- |
| `401` + pesan API key | Header `X-API-Key` atau Bearer; `API_KEY` di `.env`; `API_REQUIRE_KEY=true`. |
| `404` NIK             | NIK harus ada di `administrations.nik`.                                      |
| `404` employee UUID   | Pastikan UUID benar (bukan salah salin).                                     |
| URL 404               | Path harus **`/api/workforce/...`** (ada prefix `api`).                      |
| `429`                 | Throttle; tunggu atau sesuaikan limit di server.                             |

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

Untuk integrasi **eksternal** server-to-server, gunakan endpoint di **§1–§10** dengan **API key**; endpoint flight di atas memerlukan **session/login** web, bukan sekadar `API_KEY`.

---

## 13. Changelog dokumen

| Tanggal    | Perubahan                                                                        |
| ---------- | -------------------------------------------------------------------------------- |
| 2026-04-10 | Dokumen awal berdasarkan `routes/api.php` dan controller `Api\V1`                |
| 2026-04-10 | Bagian **Workforce** (`/api/workforce/...`) — profil, aktivitas, cuti/LOT/lembur |
| 2026-04-10 | **§10.4** Tutorial Postman untuk Workforce                                       |
| 2026-04-17 | **§10** Workforce: filter status (cuti, LOT, lembur); query `year`/`month` untuk profil & endpoint terpisah; respons `period` + `range`; field `cancellations` pada ringkasan cuti |
| 2026-04-17 | **§10.4** Postman: contoh params `year`/`month` untuk leave/LOT/overtime         |
| 2026-04-17 | **§10** Workforce: filter rentang memakai **`approved_at`** (cuti & LOT) dan **`finished_at`** (lembur); baris dengan timestamp tersebut null tidak ikut |
| 2026-04-17 | **§10** Workforce: data karyawan dipangkas lewat **`WorkforceEmployeeResource`**; LOT workforce memakai **`WorkforceOfficialtravelResource`** |

### 13.1 Revisi terbaru

**2026-04-17 — Workforce API (`EmployeeWorkforceApiController`)**

- **Perjalanan dinas:** hanya dikembalikan jika `status` ∈ {`approved`, `closed`}.
- **Cuti:** hanya `approved`, `auto_approved`, `closed`, `cancelled`; setiap item memuat relasi **`cancellations`** (untuk pembatalan partial/full) di `LeaveRequestSummaryResource`.
- **Lembur:** hanya permintaan dengan `status` = `finished`.
- **Sumbu tanggal (filter `year`/`month`/`from`/`to` / default 90 hari):** cuti dan LOT dibandingkan ke kolom **`approved_at`** (harus terisi); lembur dibandingkan ke **`finished_at`** (harus terisi). Urutan hasil: cuti & LOT `approved_at` DESC, lembur `finished_at` DESC.
- **Query:** endpoint profil mendukung **`year`** / **`month`** (opsional, informatif di respons). Endpoint `leave-requests`, `official-travels`, dan `overtime-requests` mendukung **`year`** + **`month`** opsional sebagai alternatif dari **`from`** / **`to`**, dengan default tetap 90 hari terakhir bila tidak ada filter.
- **Respons** endpoint terpisah: objek **`period`** (`year`, `month`, `from`, `to`) dan **`range`** (`from`, `to`).
- **Karyawan di workforce:** `WorkforceEmployeeResource` — hanya `id`, `fullname`, `emp_pob`, `emp_dob`, `gender`, `address`, `phone`. LOT di workforce: `WorkforceOfficialtravelResource` (subset employee pada traveler/follower sama).
