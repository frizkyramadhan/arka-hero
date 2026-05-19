<div class="import-modal-guide">
    <div class="row">
        <div class="col-lg-7 mb-3 mb-lg-0">
            <div class="card card-outline card-info h-100 mb-0">
                <div class="card-header py-2">
                    <h6 class="card-title mb-0 font-weight-bold">
                        <i class="fas fa-list-ol mr-1"></i> Langkah-langkah (disarankan: ekspor → edit → impor)
                    </h6>
                </div>
                <div class="card-body py-3 import-modal-steps">
                    <ol class="pl-3 mb-0">
                        <li class="mb-3">
                            <strong>Ekspor template dari project</strong>
                            <ul class="mb-0 mt-1 pl-3 text-muted">
                                <li>Pilih <strong>project</strong> di <strong>Project Filter</strong> (wajib agar
                                    tombol <strong>Export</strong> aktif).</li>
                                <li>
                                    Klik tombol
                                    @if ($selectedProject)
                                        <a href="{{ route('rosters.export', array_filter(['project_id' => $selectedProject->id, 'search' => $search])) }}"
                                            class="btn btn-xs btn-success py-0 px-1">
                                            <i class="fas fa-file-excel"></i> Export
                                        </a>
                                    @else
                                        <span class="badge badge-success">Export</span>
                                    @endif
                                    (hijau) di pojok kanan atas kartu filter.
                                </li>
                                <li>File <code>roster-export-YYYY-MM-DD.xlsx</code> terunduh berisi karyawan project
                                    (dan filter pencarian jika dipakai) beserta siklus yang sudah ada.</li>
                            </ul>
                        </li>
                        <li class="mb-3">
                            <strong>Edit file Excel</strong>
                            <ul class="mb-0 mt-1 pl-3 text-muted">
                                <li><strong>Jangan ubah</strong> baris header dan urutan kolom.</li>
                                <li><strong>Siklus baru:</strong> tambah baris; isi <strong>NIK</strong>,
                                    <strong>Cycle No</strong>, <strong>Work Start</strong>, <strong>Adjusted Days</strong>
                                    (opsional). Kolom <strong>Work End</strong>, <strong>Leave Start</strong>,
                                    <strong>Leave End</strong>, <strong>Status</strong> boleh dikosongkan — dihitung
                                    otomatis (sama seperti <strong>Add Cycle</strong>).</li>
                                <li><strong>Ubah siklus:</strong> cari baris <strong>NIK</strong> +
                                    <strong>Cycle No</strong>; ubah <strong>Work Start</strong> dan/atau
                                    <strong>Adjusted Days</strong>; kosongkan tanggal cuti &amp; status agar dihitung
                                    ulang saat impor.</li>
                                <li><strong>Full Name</strong>, <strong>Position</strong>, <strong>Level</strong>
                                    hanya informasi.</li>
                                <li>Simpan sebagai <code>.xlsx</code> atau <code>.xls</code>.</li>
                            </ul>
                        </li>
                        <li>
                            <strong>Impor file yang sudah diedit</strong>
                            <ul class="mb-0 mt-1 pl-3 text-muted">
                                <li>Pilih file di bawah, lalu klik <strong>Import</strong>.</li>
                                <li>Baris <strong>NIK</strong> + <strong>Cycle No</strong> yang sama diperbarui; baris
                                    baru menambah siklus (roster dibuat otomatis jika belum ada).</li>
                            </ul>
                        </li>
                    </ol>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <div class="card card-outline card-secondary h-100 mb-0">
                <div class="card-header py-2">
                    <h6 class="card-title mb-0 font-weight-bold">
                        <i class="fas fa-table mr-1"></i> Format kolom Excel
                    </h6>
                </div>
                <div class="card-body py-2 px-2">
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered table-striped mb-0 import-modal-columns">
                            <thead class="thead-light">
                                <tr>
                                    <th>Kolom</th>
                                    <th class="text-center wajib-col">Wajib</th>
                                    <th>Keterangan</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>NIK</td>
                                    <td class="text-center text-success font-weight-bold">✓</td>
                                    <td>Nomor Induk Karyawan</td>
                                </tr>
                                <tr>
                                    <td>Full Name</td>
                                    <td class="text-center text-muted">—</td>
                                    <td>Informasional</td>
                                </tr>
                                <tr>
                                    <td>Position</td>
                                    <td class="text-center text-muted">—</td>
                                    <td>Informasional</td>
                                </tr>
                                <tr>
                                    <td>Level</td>
                                    <td class="text-center text-muted">—</td>
                                    <td>Informasional</td>
                                </tr>
                                <tr>
                                    <td>Cycle No</td>
                                    <td class="text-center text-success font-weight-bold">✓</td>
                                    <td>Nomor siklus</td>
                                </tr>
                                <tr>
                                    <td>Work Start</td>
                                    <td class="text-center text-success font-weight-bold">✓</td>
                                    <td>Tanggal mulai kerja</td>
                                </tr>
                                <tr>
                                    <td>Work End</td>
                                    <td class="text-center text-muted">—</td>
                                    <td>Otomatis; boleh kosong</td>
                                </tr>
                                <tr>
                                    <td>Adjusted Days</td>
                                    <td class="text-center text-success font-weight-bold">✓</td>
                                    <td>Default 0</td>
                                </tr>
                                <tr>
                                    <td>Leave Start / End</td>
                                    <td class="text-center text-muted">—</td>
                                    <td>Otomatis; boleh kosong</td>
                                </tr>
                                <tr>
                                    <td>Remarks</td>
                                    <td class="text-center text-muted">—</td>
                                    <td>Catatan</td>
                                </tr>
                                <tr>
                                    <td>Status</td>
                                    <td class="text-center text-muted">—</td>
                                    <td>Otomatis; boleh kosong</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-outline card-light import-modal-upload mt-3 mb-0">
        <div class="card-body py-3">
            <div class="form-group mb-0">
                <label for="file" class="font-weight-bold mb-2">
                    <i class="fas fa-upload mr-1"></i> Select Excel File
                    <span class="text-danger">*</span>
                </label>
                <input type="file" class="form-control-file" id="file" name="file" accept=".xlsx,.xls" required>
                <small class="form-text text-muted">
                    Format <code>.xlsx</code> atau <code>.xls</code>, maksimal 10 MB
                </small>
            </div>
        </div>
    </div>

    @if (session('failures'))
        <div id="modalImportErrors" class="alert alert-warning mt-3 mb-0">
            <strong><i class="fas fa-exclamation-triangle mr-1"></i> Import Errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach (session('failures') as $failure)
                    <li>Row {{ $failure['row'] }} ({{ $failure['attribute'] }}): {{ $failure['errors'] }}</li>
                @endforeach
            </ul>
        </div>
    @endif
</div>
