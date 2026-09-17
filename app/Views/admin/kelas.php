<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="row mb-4 align-items-center">
    <div class="col-12 col-md-6">
        <h3 class="fw-bold text-dark mb-1">
            <i class="bi bi-door-open text-primary me-2"></i>Master Data Kelas & Mapping Walas
        </h3>
        <p class="text-muted small mb-0">Kelola daftar rombongan belajar dan tentukan wali kelas yang mengampu.</p>
    </div>
    <div class="col-12 col-md-6 text-md-end mt-3 mt-md-0">
        <button type="button" class="btn btn-primary-custom" onclick="tambahKelas()">
            <i class="bi bi-plus-circle-fill me-1"></i> Tambah Kelas Baru
        </button>
    </div>
</div>

<div class="card card-custom border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-custom table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="text-center" style="width: 50px;">No</th>
                    <th style="width: 200px;">Nama Kelas</th>
                    <th style="width: 130px;">Shift</th>
                    <th>Wali Kelas (Walas)</th>
                    <th style="width: 180px;">NIP Wali Kelas</th>
                    <th class="text-end" style="width: 150px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($kelasList)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">Belum ada data kelas.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($kelasList as $idx => $k): ?>
                        <tr>
                            <td class="text-center fw-semibold text-muted"><?= $idx + 1 ?></td>
                            <td class="fw-bold text-dark fs-6"><?= esc($k['nama_kelas']) ?></td>
                            <td>
                                <?php if (($k['shift'] ?? 'Pagi') === 'Siang'): ?>
                                    <span class="badge rounded-pill bg-info bg-opacity-10 text-primary border border-info border-opacity-25 px-2 py-1">
                                        <i class="bi bi-cloud-sun me-1"></i> Siang
                                    </span>
                                <?php else: ?>
                                    <span class="badge rounded-pill bg-warning bg-opacity-15 text-warning-emphasis border border-warning border-opacity-25 px-2 py-1">
                                        <i class="bi bi-sun-fill me-1 text-warning"></i> Pagi
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (! empty($k['nama_guru'])): ?>
                                    <span class="fw-semibold text-primary"><i class="bi bi-person-check me-1"></i> <?= esc($k['nama_guru']) ?></span>
                                <?php else: ?>
                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning">Belum Ada Walas</span>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge bg-light text-dark border font-monospace"><?= esc($k['nip'] ?? '-') ?></span></td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1" onclick="editKelas(<?= htmlspecialchars(json_encode($k), ENT_QUOTES, 'UTF-8') ?>)">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </button>
                                <a href="<?= site_url('admin/kelas/hapus/' . $k['id']) ?>" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1 ms-1" onclick="return confirm('Apakah Anda yakin ingin menghapus kelas ini?')">
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

<!-- Modal Form Kelas -->
<div class="modal fade" id="modalKelas" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="modalKelasTitle">Tambah Kelas</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= site_url('admin/kelas/simpan') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="kelas_id">
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <label for="kelas_nama" class="form-label small fw-semibold text-secondary">Nama Kelas <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="kelas_nama" name="nama_kelas" required placeholder="Contoh: XII AK 1">
                    </div>
                    <div class="mb-3">
                        <label for="kelas_shift" class="form-label small fw-semibold text-secondary">Shift Sekolah <span class="text-danger">*</span></label>
                        <select class="form-select" id="kelas_shift" name="shift" required>
                            <option value="Pagi">Pagi</option>
                            <option value="Siang">Siang</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="kelas_walas_id" class="form-label small fw-semibold text-secondary">Pilih Wali Kelas (Walas)</label>
                        <select class="form-select" id="kelas_walas_id" name="walas_id">
                            <option value="">-- Tanpa Wali Kelas (Pilih Nanti) --</option>
                            <?php foreach ($guruList as $g): ?>
                                <option value="<?= $g['id'] ?>"><?= esc($g['nama_guru']) ?> (NIP: <?= esc($g['nip'] ?? '-') ?>)</option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary-custom px-4">Simpan Kelas</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    var modalKelas = new bootstrap.Modal(document.getElementById('modalKelas'));

    function tambahKelas() {
        document.getElementById('modalKelasTitle').innerText = 'Tambah Kelas Baru';
        document.getElementById('kelas_id').value = '';
        document.getElementById('kelas_nama').value = '';
        document.getElementById('kelas_shift').value = 'Pagi';
        document.getElementById('kelas_walas_id').value = '';
        modalKelas.show();
    }

    function editKelas(data) {
        document.getElementById('modalKelasTitle').innerText = 'Edit Data Kelas';
        document.getElementById('kelas_id').value = data.id;
        document.getElementById('kelas_nama').value = data.nama_kelas || '';
        document.getElementById('kelas_shift').value = data.shift || 'Pagi';
        document.getElementById('kelas_walas_id').value = data.walas_id || '';
        modalKelas.show();
    }
</script>
<?= $this->endSection() ?>
