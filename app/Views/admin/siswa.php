<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="row mb-4 align-items-center">
    <div class="col-12 col-md-5">
        <h3 class="fw-bold text-dark mb-1">
            <i class="bi bi-people text-primary me-2"></i>Master Data Siswa
        </h3>
        <p class="text-muted small mb-0">Kelola basis data seluruh siswa dan penempatan kelas.</p>
    </div>
    <div class="col-12 col-md-7 text-md-end mt-3 mt-md-0 d-flex flex-wrap justify-content-md-end gap-2">
        <!-- Filter Kelas & Status -->
        <form action="<?= site_url('admin/siswa') ?>" method="GET" class="d-flex align-items-center gap-2">
            <select name="kelas_id" class="form-select form-select-sm w-auto shadow-sm" onchange="this.form.submit()">
                <option value="">-- Semua Kelas --</option>
                <?php foreach ($kelasList as $k): ?>
                    <option value="<?= $k['id'] ?>" <?= $selectedKelasId == $k['id'] ? 'selected' : '' ?>><?= esc($k['nama_kelas']) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="status" class="form-select form-select-sm w-auto shadow-sm" onchange="this.form.submit()">
                <option value="">-- Semua Status --</option>
                <option value="Aktif" <?= ($selectedStatus ?? '') === 'Aktif' ? 'selected' : '' ?>>Hanya Aktif</option>
                <option value="Nonaktif" <?= ($selectedStatus ?? '') === 'Nonaktif' ? 'selected' : '' ?>>Hanya Nonaktif</option>
            </select>
            <?php if (!empty($selectedKelasId) || !empty($selectedStatus)): ?>
                <a href="<?= site_url('admin/siswa') ?>" class="btn btn-sm btn-outline-secondary" title="Reset Filter"><i class="bi bi-x-circle"></i></a>
            <?php endif; ?>
        </form>
        <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#modalImportSiswa">
            <i class="bi bi-file-earmark-excel me-1"></i> Import Excel
        </button>
        <button type="button" class="btn btn-primary-custom" onclick="tambahSiswa()">
            <i class="bi bi-plus-circle-fill me-1"></i> Tambah Siswa Baru
        </button>
    </div>
</div>

<div class="card card-custom border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-custom table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="text-center" style="width: 50px;">No</th>
                    <th style="width: 150px;">NISN</th>
                    <th>Nama Lengkap Siswa</th>
                    <th style="width: 140px;">Kelas</th>
                    <th class="text-center" style="width: 120px;">Status</th>
                    <th class="text-end" style="width: 210px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($siswaList)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">Belum ada data siswa.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($siswaList as $idx => $s): ?>
                        <tr>
                            <td class="text-center fw-semibold text-muted"><?= $idx + 1 ?></td>
                            <td><span class="badge bg-light text-dark border font-monospace"><?= esc($s['nisn']) ?></span></td>
                            <td class="fw-bold text-dark fs-6"><?= esc($s['nama_siswa']) ?></td>
                            <td>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-2 py-1">
                                    <?= esc($s['nama_kelas']) ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <?php if (($s['status'] ?? 'Aktif') === 'Aktif'): ?>
                                    <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-50 px-2 py-1">
                                        <i class="bi bi-check-circle-fill me-1"></i> Aktif
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-danger bg-opacity-20 text-danger border border-danger border-opacity-50 px-2 py-1">
                                        <i class="bi bi-slash-circle-fill me-1"></i> Nonaktif
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <!-- Quick Toggle Status Button -->
                                <form action="<?= site_url('admin/siswa/toggle-status/' . $s['id']) ?>" method="POST" class="d-inline">
                                    <?= csrf_field() ?>
                                    <?php if (($s['status'] ?? 'Aktif') === 'Aktif'): ?>
                                        <button type="submit" class="btn btn-sm btn-outline-warning rounded-pill px-2 py-1" title="Nonaktifkan Siswa (Hilangkan dari presensi kelas)" onclick="return confirm('Nonaktifkan siswa <?= esc($s['nama_siswa']) ?>? Siswa ini tidak akan dimunculkan lagi di presensi murid/kelas.')">
                                            <i class="bi bi-person-x"></i> Nonaktifkan
                                        </button>
                                    <?php else: ?>
                                        <button type="submit" class="btn btn-sm btn-outline-success rounded-pill px-2 py-1" title="Aktifkan Siswa (Munculkan kembali di presensi kelas)" onclick="return confirm('Aktifkan kembali siswa <?= esc($s['nama_siswa']) ?>?')">
                                            <i class="bi bi-person-check"></i> Aktifkan
                                        </button>
                                    <?php endif; ?>
                                </form>

                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1 ms-1" onclick="editSiswa(<?= htmlspecialchars(json_encode($s), ENT_QUOTES, 'UTF-8') ?>)">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <a href="<?= site_url('admin/siswa/hapus/' . $s['id']) ?>" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1 ms-1" onclick="return confirm('Apakah Anda yakin ingin menghapus data siswa ini?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Form Siswa -->
<div class="modal fade" id="modalSiswa" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="modalSiswaTitle">Tambah Siswa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= site_url('admin/siswa/simpan') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="siswa_id">
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <label for="siswa_nisn" class="form-label small fw-semibold text-secondary">NISN (Nomor Induk Siswa Nasional) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="siswa_nisn" name="nisn" required placeholder="Contoh: 0061234501">
                    </div>
                    <div class="mb-3">
                        <label for="siswa_nama" class="form-label small fw-semibold text-secondary">Nama Lengkap Siswa <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="siswa_nama" name="nama_siswa" required placeholder="Contoh: Ahmad Fauzi">
                    </div>
                    <div class="mb-3">
                        <label for="siswa_kelas_id" class="form-label small fw-semibold text-secondary">Pilih Kelas Siswa <span class="text-danger">*</span></label>
                        <select class="form-select" id="siswa_kelas_id" name="kelas_id" required>
                            <option value="">-- Pilih Kelas --</option>
                            <?php foreach ($kelasList as $k): ?>
                                <option value="<?= $k['id'] ?>"><?= esc($k['nama_kelas']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="siswa_status" class="form-label small fw-semibold text-secondary">Status Siswa <span class="text-danger">*</span></label>
                        <select class="form-select" id="siswa_status" name="status" required>
                            <option value="Aktif">Aktif (Mengikuti Presensi & KBM)</option>
                            <option value="Nonaktif">Nonaktif (Pindah / Lulus / Berhenti - Tidak Muncul di Presensi)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary-custom px-4">Simpan Siswa</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Import Excel Siswa -->
<div class="modal fade" id="modalImportSiswa" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-success text-white rounded-3 p-2">
                        <i class="bi bi-file-earmark-excel-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark">Import Siswa dari Excel</h5>
                        <p class="text-muted small mb-0">Unggah berkas spreadsheet .xlsx, .xls, atau .csv</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= site_url('admin/siswa/import') ?>" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-body py-4">
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <div class="fw-bold small text-dark">Gunakan Format Template Resmi</div>
                                <div class="text-muted small">Susunan kolom baris 1 (Header): NISN, Nama Siswa, Nama Kelas</div>
                            </div>
                            <a href="<?= site_url('admin/siswa/download-template') ?>" class="btn btn-sm btn-outline-success text-nowrap">
                                <i class="bi bi-download me-1"></i> Unduh Template (.xlsx)
                            </a>
                        </div>
                        <!-- Tabel Contoh Format -->
                        <div class="table-responsive mt-2">
                            <table class="table table-bordered table-sm bg-white mb-0 text-center" style="font-size: 0.78rem;">
                                <thead class="table-primary text-dark">
                                    <tr>
                                        <th>Kolom A (NISN)</th>
                                        <th>Kolom B (Nama Siswa)</th>
                                        <th>Kolom C (Nama Kelas)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="font-monospace">0061234509</td>
                                        <td class="text-start">Ahmad Dani Pratama</td>
                                        <td>XII AK 1</td>
                                    </tr>
                                    <tr>
                                        <td class="font-monospace">0061234510</td>
                                        <td class="text-start">Bella Nur Safitri</td>
                                        <td>XII AK 1</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="file_excel_siswa" class="form-label small fw-semibold text-secondary">Pilih Berkas Excel (.xlsx / .xls / .csv) <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="file_excel_siswa" name="file_excel" accept=".xlsx,.xls,.csv" required>
                        <div class="form-text small">Jika nama kelas belum ada di sistem, sistem akan otomatis mendaftarkan kelas baru tersebut.</div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success px-4">
                        <i class="bi bi-cloud-arrow-up-fill me-1"></i> Mulai Import Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    var modalSiswa = new bootstrap.Modal(document.getElementById('modalSiswa'));

    function tambahSiswa() {
        document.getElementById('modalSiswaTitle').innerText = 'Tambah Siswa Baru';
        document.getElementById('siswa_id').value = '';
        document.getElementById('siswa_nisn').value = '';
        document.getElementById('siswa_nama').value = '';
        document.getElementById('siswa_kelas_id').value = '<?= $selectedKelasId ?: '' ?>';
        document.getElementById('siswa_status').value = 'Aktif';
        modalSiswa.show();
    }

    function editSiswa(data) {
        document.getElementById('modalSiswaTitle').innerText = 'Edit Data Siswa';
        document.getElementById('siswa_id').value = data.id;
        document.getElementById('siswa_nisn').value = data.nisn || '';
        document.getElementById('siswa_nama').value = data.nama_siswa || '';
        document.getElementById('siswa_kelas_id').value = data.kelas_id || '';
        document.getElementById('siswa_status').value = data.status || 'Aktif';
        modalSiswa.show();
    }
</script>
<?= $this->endSection() ?>
