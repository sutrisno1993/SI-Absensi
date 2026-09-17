<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<?php
    $isPastDate = (strtotime($tanggal) < strtotime(date('Y-m-d')));
    $yesterday  = date('Y-m-d', strtotime('-1 day'));
    $twoDaysAgo = date('Y-m-d', strtotime('-2 days'));
?>

<div class="row mb-4 align-items-center">
    <div class="col-12 col-md-6">
        <h3 class="fw-bold text-dark mb-1">
            <i class="bi bi-clipboard-check text-primary me-2"></i>Input & Edit Presensi Kelas <?= esc($kelas['nama_kelas']) ?>
        </h3>
        <p class="text-muted small mb-0">Kelola dan perbarui presensi harian maupun presensi susulan yang kelewat untuk siswa binaan Anda.</p>
    </div>
    <div class="col-12 col-md-6 mt-3 mt-md-0">
        <!-- Date Selector Filter -->
        <form action="<?= site_url('walas/absen') ?>" method="GET" class="d-flex align-items-center justify-content-md-end flex-wrap gap-2">
            <label for="tanggal" class="form-label mb-0 fw-semibold text-secondary small text-nowrap">Pilih Tanggal:</label>
            <input type="date" class="form-control form-control-sm w-auto shadow-sm" id="tanggal" name="tanggal" value="<?= esc($tanggal) ?>" max="<?= date('Y-m-d') ?>" onchange="this.form.submit()">
            
            <div class="btn-group btn-group-sm">
                <a href="<?= site_url('walas/absen?tanggal=' . date('Y-m-d')) ?>" class="btn btn-outline-secondary <?= $tanggal === date('Y-m-d') ? 'active' : '' ?>">Hari Ini</a>
                <a href="<?= site_url('walas/absen?tanggal=' . $yesterday) ?>" class="btn btn-outline-secondary <?= $tanggal === $yesterday ? 'active' : '' ?>">Kemarin</a>
                <a href="<?= site_url('walas/absen?tanggal=' . $twoDaysAgo) ?>" class="btn btn-outline-secondary <?= $tanggal === $twoDaysAgo ? 'active' : '' ?>">2 Hari Lalu</a>
            </div>
        </form>
    </div>
</div>

<?php if ($isPastDate): ?>
    <div class="alert alert-warning border-0 shadow-sm rounded-3 d-flex align-items-center gap-2 mb-4" role="alert">
        <i class="bi bi-clock-history fs-4 text-warning"></i>
        <div>
            <strong>Presensi Susulan / Kelewat:</strong> Anda sedang menginput/mengubah data presensi untuk tanggal <strong><?= date('d F Y', strtotime($tanggal)) ?></strong>.
        </div>
    </div>
<?php endif; ?>

<form action="<?= site_url('walas/absen/simpan') ?>" method="POST">
    <?= csrf_field() ?>
    <input type="hidden" name="tanggal" value="<?= esc($tanggal) ?>">

    <div class="card card-custom border-0 mb-4">
        <div class="card-header bg-white border-0 py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary px-3 py-2 fs-6 rounded-pill">
                    <i class="bi bi-calendar-date me-1"></i> <?= date('d F Y', strtotime($tanggal)) ?>
                </span>
                <span class="badge bg-light text-muted border px-2 py-2">
                    Total Siswa: <?= count($daftarSiswa) ?> Orang
                </span>
            </div>
            <div>
                <button type="button" class="btn btn-sm btn-outline-success fw-semibold rounded-pill px-3" onclick="setSemuaHadir()">
                    <i class="bi bi-check-all me-1"></i> Set Semua Hadir (H)
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-custom table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">No</th>
                        <th style="width: 130px;">NISN</th>
                        <th>Nama Siswa</th>
                        <th class="text-center" style="width: 250px;">Status Kehadiran</th>
                        <th style="width: 260px;">Keterangan (Opsional)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($daftarSiswa)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Belum ada data siswa di kelas ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($daftarSiswa as $idx => $s): ?>
                            <tr>
                                <td class="text-center fw-semibold text-muted"><?= $idx + 1 ?></td>
                                <td><span class="badge bg-light text-dark border font-monospace"><?= esc($s['nisn']) ?></span></td>
                                <td class="fw-bold text-dark"><?= esc($s['nama_siswa']) ?></td>
                                <td class="text-center">
                                    <div class="d-inline-flex gap-2">
                                        <!-- Hadir -->
                                        <label>
                                            <input type="radio" class="status-pill-radio radio-h" name="absensi[<?= $s['siswa_id'] ?>][status]" value="H" <?= ($s['status'] ?? 'H') === 'H' ? 'checked' : '' ?>>
                                            <span class="status-label" title="Hadir">H</span>
                                        </label>
                                        <!-- Sakit -->
                                        <label>
                                            <input type="radio" class="status-pill-radio" name="absensi[<?= $s['siswa_id'] ?>][status]" value="S" <?= ($s['status'] ?? '') === 'S' ? 'checked' : '' ?>>
                                            <span class="status-label" title="Sakit">S</span>
                                        </label>
                                        <!-- Izin -->
                                        <label>
                                            <input type="radio" class="status-pill-radio" name="absensi[<?= $s['siswa_id'] ?>][status]" value="I" <?= ($s['status'] ?? '') === 'I' ? 'checked' : '' ?>>
                                            <span class="status-label" title="Izin">I</span>
                                        </label>
                                        <!-- Alpa -->
                                        <label>
                                            <input type="radio" class="status-pill-radio" name="absensi[<?= $s['siswa_id'] ?>][status]" value="A" <?= ($s['status'] ?? '') === 'A' ? 'checked' : '' ?>>
                                            <span class="status-label" title="Alpa / Tanpa Keterangan">A</span>
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm bg-light" name="absensi[<?= $s['siswa_id'] ?>][keterangan]" value="<?= esc($s['keterangan'] ?? '') ?>" placeholder="Misal: Demam, Izin dinas...">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (! empty($daftarSiswa)): ?>
            <div class="card-footer bg-white border-0 py-3 text-end">
                <button type="submit" class="btn btn-primary-custom px-4 py-2">
                    <i class="bi bi-cloud-check-fill me-2"></i> Simpan Data Presensi
                </button>
            </div>
        <?php endif; ?>
    </div>
</form>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function setSemuaHadir() {
        document.querySelectorAll('.radio-h').forEach(function(radio) {
            radio.checked = true;
        });
    }
</script>
<?= $this->endSection() ?>
