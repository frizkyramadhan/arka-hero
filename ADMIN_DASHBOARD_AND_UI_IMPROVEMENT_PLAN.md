# Rencana Implementasi: Dashboard Admin dan Peningkatan UI Role-Permission

Dokumen ini menguraikan rencana untuk mengembangkan dashboard admin baru dan meningkatkan antarmuka pengguna (UI) untuk manajemen role dan permission dalam aplikasi.

## 1. Analisis Komprehensif Aplikasi

Aplikasi ini dibangun menggunakan framework Laravel dan memanfaatkan package `spatie/laravel-permission` untuk manajemen role dan permission. Struktur ini menyediakan fondasi yang kuat untuk kontrol akses berbasis peran.

-   **Model:** `User`, `Role`, `Permission` adalah inti dari sistem otentikasi dan otorisasi. Relasi antar model ini diatur oleh package Spatie.
-   **Controller:** `UserController`, `RoleController`, dan `PermissionController` mengelola logika bisnis untuk masing-masing entitas.
-   **Routing:** Route didefinisikan dalam `routes/web.php` dan dilindungi oleh middleware `permission` dan `role` dari Spatie.
-   **Views:** Tampilan Blade digunakan untuk merender halaman, termasuk formulir dan tabel data.

Kelemahan saat ini adalah tidak adanya dashboard terpusat untuk admin melihat gambaran keseluruhan relasi User-Role-Permission dan antarmuka yang kurang intuitif pada halaman manajemen role.

## 2. Rencana Pembuatan Dashboard Administrator

Tujuan dari dashboard ini adalah untuk memberikan admin visibilitas penuh dan kontrol atas hak akses pengguna.

### 2.1. Desain dan Layout Dashboard

Dashboard akan terdiri dari beberapa komponen utama:

1.  **Ringkasan Statistik:** Kartu (cards) yang menampilkan jumlah total Pengguna, Role, dan Permission.
2.  **Tabel Pengguna (Users Table):**
    -   Menampilkan daftar semua pengguna.
    -   Kolom: Nama, Email, Roles.
    -   Setiap baris akan memiliki tombol "View Details" yang akan membuka modal atau halaman detail.
3.  **Halaman/Modal Detail Pengguna:**
    -   Menampilkan informasi detail pengguna.
    -   Menampilkan daftar Role yang dimiliki pengguna dalam bentuk badge.
    -   Menampilkan daftar semua Permission yang dimiliki pengguna (baik secara langsung maupun melalui role), dikelompokkan berdasarkan kategori (misalnya, "User Management", "Employee Management").
4.  **Tabel Role (Roles Table):**
    -   Menampilkan daftar semua role.
    -   Kolom: Nama Role, Jumlah Pengguna, Jumlah Permission.
    -   Tombol aksi untuk Edit dan Hapus.
5.  **Tabel Permission (Permissions Table):**
    -   Menampilkan daftar semua permission.
    -   Kolom: Nama Permission, Jumlah Role yang menggunakan.

### 2.2. Implementasi Teknis

-   **Backend:**
    -   Menggunakan `UserController` yang sudah ada untuk menangani logika dashboard.
    -   Membuat method untuk mengambil data statistik (`userCount`, `roleCount`, `permissionCount`).
    -   Menggunakan relasi Eloquent (`user->roles`, `user->getAllPermissions()`, `role->permissions`, `role->users`) untuk mengambil data secara efisien.
    -   Menyediakan endpoint data untuk DataTables (jika menggunakan server-side processing).
-   **Frontend:**
    -   Membuat file view baru `resources/views/users/dashboard.blade.php`.
    -   Menggunakan komponen Blade dan styling dari template AdminLTE yang ada.
    -   Menggunakan DataTables untuk menampilkan data dalam tabel yang interaktif (sorting, searching).
    -   Menggunakan Bootstrap Modal untuk menampilkan detail pengguna tanpa perlu me-refresh halaman.

**Contoh Tampilan Modal Detail Pengguna:**

```html
<!-- User Details Modal -->
<div class="modal fade" id="user-details-modal">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">User Details: [User Name]</h4>
                <button type="button" class="close" data-dismiss="modal">
                    &times;
                </button>
            </div>
            <div class="modal-body">
                <p><strong>Email:</strong> [User Email]</p>
                <hr />
                <h5>Roles</h5>
                <p>
                    <span class="badge badge-primary">[Role 1]</span>
                    <span class="badge badge-primary">[Role 2]</span>
                </p>
                <hr />
                <h5>Permissions</h5>
                <div class="permission-groups">
                    <!-- Permissions akan di-render di sini, dikelompokkan per kategori -->
                </div>
            </div>
        </div>
    </div>
</div>
```

## 3. Peningkatan UI Halaman Create/Edit Role

Tujuannya adalah menyederhanakan proses pemilihan permission dengan mengelompokkannya secara logis.

### 3.1. Desain dan Fungsionalitas

-   Pada halaman `create` dan `edit` role, daftar permission akan dikelompokkan ke dalam kategori berdasarkan prefix nama permission (cth: `users.show` -> kategori `users`).
-   Setiap kategori akan menjadi sebuah "card" atau "accordion" yang bisa di-expand/collapse.
-   Setiap kategori akan memiliki checkbox "Select All" untuk memilih/membatalkan pilihan semua permission dalam kategori tersebut.
-   Checkbox "Select All" di bagian atas akan tetap ada untuk memilih semua permission dari semua kategori.

### 3.2. Implementasi Frontend (Tanpa Perubahan Backend)

Perubahan ini murni pada sisi frontend menggunakan JavaScript (jQuery) pada file `resources/views/roles/create.blade.php` and `resources/views/roles/edit.blade.php`.

**Langkah-langkah:**

1.  **Modifikasi View Blade:**
    -   Struktur HTML akan diubah untuk membungkus permission dalam div-div berdasarkan kategori. Ini bisa dilakukan saat me-looping variabel `$permissions` dari controller.
2.  **Logika Pengelompokan di Blade:**

    -   Sebelum loop, kita akan mengelompokkan permission di dalam view.

    ```php
    // In your create.blade.php or edit.blade.php before the form
    @php
        $groupedPermissions = $permissions->groupBy(function($permission) {
            return explode('.', $permission->name)[0];
        });
    @endphp
    ```

3.  **Render HTML Berdasarkan Grup:**

    -   Loop melalui `$groupedPermissions`.

    ```html
    <!-- Di dalam card-body permissions -->
    @foreach($groupedPermissions as $category => $permissionList)
    <div class="card card-outline card-primary mb-3">
        <div class="card-header">
            <h3 class="card-title text-capitalize">
                {{ str_replace('-', ' ', $category) }}
            </h3>
            <div class="card-tools">
                <input
                    type="checkbox"
                    class="select-all-category"
                    data-category="{{ $category }}"
                />
                <label class="mr-2">Select All</label>
                <button
                    type="button"
                    class="btn btn-tool"
                    data-card-widget="collapse"
                >
                    <i class="fas fa-minus"></i>
                </button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($permissionList as $permission)
                <div class="col-md-6">
                    <div class="form-check mb-2">
                        <input
                            class="form-check-input permission-checkbox"
                            data-category="{{ $category }}"
                            type="checkbox"
                            name="permissions[]"
                            value="{{ $permission->name }}"
                            id="perm_{{ $permission->id }}"
                        />
                        <label
                            class="form-check-label"
                            for="perm_{{ $permission->id }}"
                            >{{ $permission->name }}</label
                        >
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endforeach
    ```

4.  **JavaScript (jQuery) untuk Fungsionalitas:**

    -   Tambahkan script di section `@section('scripts')`.

    ```javascript
    $(document).ready(function () {
        // Global "Select All"
        $("#select_all_permissions").click(function () {
            $(".permission-checkbox, .select-all-category").prop(
                "checked",
                $(this).is(":checked")
            );
        });

        // Category-specific "Select All"
        $(".select-all-category").click(function () {
            var category = $(this).data("category");
            $('.permission-checkbox[data-category="' + category + '"]').prop(
                "checked",
                $(this).is(":checked")
            );
            updateGlobalSelectAllState();
        });

        // Single permission checkbox
        $(".permission-checkbox").click(function () {
            var category = $(this).data("category");
            if (
                $(
                    '.permission-checkbox[data-category="' +
                        category +
                        '"]:checked'
                ).length ==
                $('.permission-checkbox[data-category="' + category + '"]')
                    .length
            ) {
                $(
                    '.select-all-category[data-category="' + category + '"]'
                ).prop("checked", true);
            } else {
                $(
                    '.select-all-category[data-category="' + category + '"]'
                ).prop("checked", false);
            }
            updateGlobalSelectAllState();
        });

        function updateGlobalSelectAllState() {
            if (
                $(".permission-checkbox:checked").length ==
                $(".permission-checkbox").length
            ) {
                $("#select_all_permissions").prop("checked", true);
            } else {
                $("#select_all_permissions").prop("checked", false);
            }
        }

        // Initialize state on page load
        $(".select-all-category").each(function () {
            var category = $(this).data("category");
            if (
                $(
                    '.permission-checkbox[data-category="' +
                        category +
                        '"]:checked'
                ).length ==
                $('.permission-checkbox[data-category="' + category + '"]')
                    .length
            ) {
                $(this).prop("checked", true);
            }
        });
        updateGlobalSelectAllState();
    });
    ```

## 4. Langkah-langkah Implementasi

1.  **Buat Branch Baru:** `feature/admin-dashboard-ui-improvements`.
2.  **Backend Dashboard:**
    -   Tambahkan method baru di `UserController` untuk dashboard.
    -   Tambahkan route baru di `web.php` untuk dashboard.
    -   Implementasikan method untuk mengambil data.
3.  **Frontend Dashboard:**
    -   Buat view `users/dashboard.blade.php`.
    -   Tambahkan link ke dashboard di sidebar navigasi utama (`sidebar.blade.php`).
    -   Implementasikan tabel dan modal menggunakan DataTables dan Bootstrap.
4.  **Peningkatan UI Role (Frontend Only):**
    -   Update file `resources/views/roles/create.blade.php` dan `edit.blade.php` dengan struktur HTML dan logika pengelompokan seperti di atas.
    -   Tambahkan kode JavaScript ke dalam section `scripts` di kedua file tersebut.
5.  **Testing:**
    -   Pastikan dashboard menampilkan data yang benar.
    -   Verifikasi fungsionalitas modal detail pengguna.
    -   Uji pengelompokan permission, "Select All" per kategori, dan "Select All" global di halaman create/edit role.
    -   Pastikan form create/edit role masih berfungsi dengan baik setelah perubahan UI.
6.  **Pull Request:** Buat Pull Request untuk me-review dan me-merge perubahan ke branch utama.
