# Official Travel (LOT): Destination, Stamp Checkpoint, dan Rute Dinamis

Dokumen ini merangkum **latar belakang perubahan**, **perilaku aplikasi sekarang**, serta **implikasi bisnis** (termasuk skenario LOT mampir dan rute yang baru diputuskan setelah berangkat).

---

## 1. Kondisi dulu (sebelum penyesuaian destination)

- **Destination** umumnya diisi **manual** (teks bebas).
- **Siapa boleh melakukan stamp chckpoint (arrival & departure)** kedatangan dan keberangkatan tidak diikat keras ke **project tujuan** di data LOT.
- Akibatnya, untuk perjalanan **A → B**, user di lokasi lain secara teknis **bisa saja** ikut memproses stamp jika mereka punya akses ke halaman tersebut—**tidak selaras** dengan keinginan bisnis: _checkpoint di B seharusnya menjadi tanggung jawab admin yang mewakili lokasi B_.

---

## 2. Perubahan yang dilakukan (intinya)

### 2.1 Destination di form (create / edit)

- **Destination utama** bisa dipilih dari **daftar project aktif** (semua project berstatus aktif, tidak dibatasi `user_project` hanya untuk field ini—supaya dropdown konsisten dengan kebutuhan “tujuan dinas”).
- **LOT Origin** (lokasi awal bisnis) tetap mengikuti aturan **user–project** seperti semula (siapa mengelola project apa).
- Tersedia **opsi manual**: centang “isi manual” untuk destination **teks bebas** bila tujuan tidak cocok dengan format project.

### 2.2 Stamp kedatangan & keberangkatan (checkpoint)

- Hanya **user** yang di `user_project`-nya **cocok** dengan **nilai kolom `destination`** pada LOT yang boleh melakukan **Record Arrival** dan **Record Departure**.
- **Cocok** diartikan konsisten dengan label project yang dipakai di form, misalnya:
    - sama persis dengan `KODE - NAMA_PROYEK`, atau
    - sama dengan kode saja / nama saja, atau
    - teks destination diawali `KODE -` (misal manual tapi tetap pakai awalan kode).
- **Close LOT** sengaja **tidak** memakai cek destination: secara bisnis, penutupan dianggap dari **sisi lokasi awal / proses HR**, bukan dari “tujuan stamp” terakhir.

### 2.3 Listing & dashboard

- Daftar **pending arrival / departure** di dashboard hanya menghitung LOT yang **destinasinya** juga cocok dengan assignment user (kecuali user dengan role **`administrator`** melihat semua).
- Hal ini supaya angka “tunggu stamp” tidak menyesatkan user yang tidak berhak memproses LOT itu.

---

## 3. Multi checkpoint (arrival → departure → arrival lagi)

**Ya, perilaku ini sudah ada di kode dan tidak hilang karena perubahan destination.**

- Satu LOT bisa punya **beberapa baris** di tabel **`officialtravel_stops`** (satu “putaran” kedatangan + keberangkatan per tahap).
- Setelah **satu stop lengkap** (sudah ada arrival dan departure), aturan bisnis **mengizinkan** pencatatan **arrival** lagi untuk **putaran berikutnya** (pemberhentian berikut).
- Tombol **Record Arrival** akan bisa muncul lagi **setelah** stop terakhir dianggap **selesai** (ada departure), selama status LOT masih **`approved`** dan user lolos izin stamp + cocok destination.

Singkatnya: **satu nomor LOT bisa mencerminkan beberapa kali “singgah stamp” secara berurutan**, asalkan pola stop di sistem diisi berurutan.

---

## 4. Dampak bisnis yang perlu disadari

### 4.1 Satu field `destination` vs banyak lokasi nyata

- **`destination`** di LOT saat ini **satu nilai** untuk **seluruh dokumen**.
- Stamp **mengikat** ke nilai itu.
- Jika karyawan **mampir** ke lokasi project lain **(mis. dari B ke C)** yang **bukan** tercermin di `destination`, maka **admin C** secara sistem **tidak akan lolos** cek stamp—meskipun secara operasi mereka yang ada di lokasi C.

Ini bukan “bug”, melainkan **batasan model data saat ini**: sistem belum punya **project per checkpoint** yang bisa diubah per stop tanpa mengubah satu string destination global.

### 4.2 Mampir yang baru diputuskan setelah checkpoint B

Seringkali karyawan **baru memutuskan** mampir ke C atau D **setelah** tiba di B. Itu wajar secara operasional.

**Implikasi:**

- Sistem tidak wajib tahu C/D **sejak berangkat dari A**.
- Yang diperlukan adalah **cara resmi** memperbarui data LOT **setelah** keputusan itu ada, contohnya:
    - **HR mengubah / menambah** informasi rute (amendment), atau
    - **Alur pengajuan** dari karyawan → persetujuan HR, lalu data checkpoint berikutnya diperbarui.

Tanpa langkah itu, stamp di C tetap tidak selaras dengan aturan “hanya admin C” kalau `destination` masih menggambarkan hanya B.

### 4.3 Skenario singkat (ringkas)

| Skenario                     | Inti kebutuhan                           | Catatan ke arah aplikasi                                                                                                                                                                               |
| ---------------------------- | ---------------------------------------- | ------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------ |
| **A → B**                    | Stamp hanya admin B                      | Sudah sejalan dengan `destination` = B dan cek user–project                                                                                                                                            |
| **B → C (project internal)** | Stamp hanya admin C                      | Perlu data tambahan: mis. **project per stop** atau **amendment destination/segmen** agar admin C terpakai sebelum stamp C                                                                             |
| **B → D (eksternal)**        | Tidak ada admin D di sistem; pulang ke A | Proses komunikasi ke admin A sudah umum; aplikasi idealnya punya aturan **checkpoint pulang di origin** agar **admin A tidak terblokir** hanya karena `destination` masih menggambarkan B/D dalam teks |
| **Close di A**               | Admin A menutup                          | Sudah dipisah dari cek destination                                                                                                                                                                     |

---

## 5. Arah pengembangan yang disarankan (bukan wajib sudah jadi kode)

Agar semua skenario di atas **rapi** dan **bisa diaudit**:

1. **Checkpoint bertanda project (per stop atau per segmen)**  
   Setiap putaran stamp punya **`checkpoint_project_id`** (atau setara) sehingga izin stamp memeriksa **project stop itu**, bukan hanya `destination` global.

2. **Amendment / pengajuan perubahan rute**  
   HR (atau workflow karyawan → HR) dapat **menambah segmen** setelah keputusan mampir ada—tanpa harus menebak semua tujuan sejak A.

3. **Tipe segmen**  
   Mis. **internal** (stamp oleh admin project), **eksternal** (tanpa stamp onsite), **pulang ke origin** (stamp/aturan khusus admin A).

Dokumen ini **tidak mengganti** keputusan formal HR; tujuannya menyelaraskan **pemahaman tim** tentang apa yang sudah ada di aplikasi dan **celah** yang muncul bila proses nyata lebih kaya dari satu string destination.

---

## 6. Referensi teknis singkat (untuk developer)

- Model: `Officialtravel::canRecordArrival()`, `canRecordDeparture()`, `canClose()`; relasi `stops` / `latestStop`.
- Dukungan stamp destination: `App\Support\UserProject::destinationMatchesUserAssignedProjects()`, `scopeOfficialTravelsDestinationStampMatch()`.
- Controller: `OfficialtravelController` (arrival/departure + `ensureStampAllowedForDestination`; `close` tanpa cek destination).
- Form destination: `resources/views/officialtravels/create.blade.php`, `edit.blade.php`, `my-travels-create.blade.php`, `my-travels-edit.blade.php`; koleksi project untuk dropdown destination: `activeProjectsForDestinationSelect()` di controller.

---

_Dokumen ini disusun sebagai ringkasan analisis produk/teknis; perbarui jika kebijakan bisnis atau implementasi di codebase berubah._
