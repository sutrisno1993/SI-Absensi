<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<!-- Breadcrumb Navigation -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= site_url('admin') ?>">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= site_url('admin/laporan') ?>">Laporan Global</a></li>
        <li class="breadcrumb-item"><a href="<?= site_url('admin/laporan/kelas/' . $siswa['kelas_id']) ?>">Kelas <?= esc($siswa['nama_kelas']) ?></a></li>
        <li class="breadcrumb-item active" aria-current="page"><?= esc($siswa['nama_siswa']) ?></li>
    </ol>
</nav>

<!-- Header & Profile Card Siswa -->
<div class="card card-custom border-0 shadow-sm p-4 mb-4">
    <div class="row align-items-center">
        <div class="col-12 col-md-8 d-flex align-items-center gap-3">
            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 64px; height: 64px; font-size: 1.6rem;">
                <?= strtoupper(substr($siswa['nama_siswa'], 0, 1)) ?>
            </div>
            <div>
                <h4 class="fw-bold text-dark mb-1"><?= esc($siswa['nama_siswa']) ?></h4>
                <div class="d-flex flex-wrap align-items-center gap-2 text-muted small">
                    <span><i class="bi bi-card-text me-1"></i> NISN: <code><?= esc($siswa['nisn']) ?></code></span>
                    <span>&bull;</span>
                    <span><i class="bi bi-door-open me-1"></i> Kelas: <strong class="text-dark"><?= esc($siswa['nama_kelas']) ?></strong></span>
                    <span>&bull;</span>
                    <span><i class="bi bi-person me-1"></i> Walas: <strong><?= esc($siswa['nama_walas'] ?? 'Belum Ditentukan') ?></strong></span>
                </div>
            </div>
        </div>
        <div class="col-12 col-md-4 text-md-end mt-3 mt-md-0 d-flex flex-wrap justify-content-md-end gap-2">
            <a href="<?= site_url('admin/laporan/export-siswa-excel/' . $siswa['id']) ?>" class="btn btn-outline-success btn-sm">
                <i class="bi bi-file-earmark-excel-fill me-1"></i> Download Excel (.xlsx)
            </a>
            <a href="<?= site_url('admin/laporan/kelas/' . $siswa['kelas_id']) ?>" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
            <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
                <i class="bi bi-printer me-1"></i> Cetak
            </button>
        </div>
    </div>
</div>

<!-- Statistik Ringkas Kehadiran Siswa -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card card-custom border-0 p-3 text-center">
            <div class="text-muted small fw-semibold">Hadir (H)</div>
            <div class="fs-2 fw-bold text-success mt-1"><?= $stats['total_h'] ?></div>
            <small class="text-muted">Hari Mengikuti KBM</small>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-custom border-0 p-3 text-center">
            <div class="text-muted small fw-semibold">Sakit (S)</div>
            <div class="fs-2 fw-bold text-primary mt-1"><?= $stats['total_s'] ?></div>
            <small class="text-muted">Hari Sakit</small>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-custom border-0 p-3 text-center">
            <div class="text-muted small fw-semibold">Izin (I)</div>
            <div class="fs-2 fw-bold text-warning mt-1"><?= $stats['total_i'] ?></div>
            <small class="text-muted">Hari Izin Resmi</small>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card card-custom border-0 p-3 text-center <?= $stats['total_a'] > 3 ? 'border-danger border-2' : '' ?>">
            <div class="text-muted small fw-semibold">Alpa (A)</div>
            <div class="fs-2 fw-bold text-danger mt-1"><?= $stats['total_a'] ?></div>
            <small class="<?= $stats['total_a'] > 3 ? 'text-danger fw-bold' : 'text-muted' ?>">
                <?= $stats['total_a'] > 3 ? '⚠️ Melebihi Batas (3x)' : 'Tanpa Keterangan' ?>
            </small>
        </div>
    </div>
</div>

<!-- TABEL UTAMA: TRACK RECORD KETIDAKHADIRAN SISWA -->
<div class="card card-custom border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
        <div class="d-flex align-items-center gap-2">
            <div class="bg-danger bg-opacity-10 text-danger rounded-3 p-2">
                <i class="bi bi-calendar-x-fill fs-5"></i>
            </div>
            <div>
                <h5 class="fw-bold text-dark mb-0">Track Record Tanggal Ketidakhadiran</h5>
                <small class="text-muted">Daftar seluruh tanggal siswa tidak masuk sekolah beserta alasannya</small>
            </div>
        </div>
        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-3 py-2 rounded-pill">
            Total Ketidakhadiran: <?= count($trackRecord) ?> Hari
        </span>
    </div>

    <div class="table-responsive">
        <table class="table table-custom table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="text-center" style="width: 60px;">No</th>
                    <th style="width: 200px;">Hari & Tanggal</th>
                    <th class="text-center" style="width: 140px;">Status Presensi</th>
                    <th>Keterangan / Alasan Tidak Masuk</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($trackRecord)): ?>
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">
                            <i class="bi bi-emoji-smile fs-1 text-success d-block mb-2"></i>
                            <span class="fw-bold text-success fs-6">Rekor Kehadiran Sempurna!</span>
                            <p class="small text-muted mb-0">Siswa ini tidak pernah tercatat Sakit, Izin, maupun Alpa.</p>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $hariIndo = ['Sunday'=>'Minggu', 'Monday'=>'Senin', 'Tuesday'=>'Selasa', 'Wednesday'=>'Rabu', 'Thursday'=>'Kamis', 'Friday'=>'Jumat', 'Saturday'=>'Sabtu'];
                    foreach ($trackRecord as $idx => $tr): 
                        $namaHari = $hariIndo[date('l', strtotime($tr['tanggal']))] ?? date('l', strtotime($tr['tanggal']));
                    ?>
                        <tr>
                            <td class="text-center fw-semibold text-muted"><?= $idx + 1 ?></td>
                            <td>
                                <div class="fw-bold text-dark"><?= $namaHari ?>, <?= date('d/m/Y', strtotime($tr['tanggal'])) ?></div>
                                <small class="text-muted"><?= date('d F Y', strtotime($tr['tanggal'])) ?></small>
                            </td>
                            <td class="text-center">
                                <?php if ($tr['status'] === 'S'): ?>
                                    <span class="badge badge-s px-3 py-2 fs-6 rounded-pill">
                                        <i class="bi bi-heart-pulse me-1"></i> Sakit (S)
                                    </span>
                                <?php elseif ($tr['status'] === 'I'): ?>
                                    <span class="badge badge-i px-3 py-2 fs-6 rounded-pill">
                                        <i class="bi bi-envelope-paper me-1"></i> Izin (I)
                                    </span>
                                <?php elseif ($tr['status'] === 'A'): ?>
                                    <span class="badge badge-a px-3 py-2 fs-6 rounded-pill bg-danger text-white">
                                        <i class="bi bi-x-circle me-1"></i> Alpa (A)
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (! empty($tr['keterangan'])): ?>
                                    <div class="fw-semibold text-dark"><?= esc($tr['keterangan']) ?></div>
                                <?php else: ?>
                                    <span class="text-muted fst-italic">Tidak ada keterangan / Tanpa kabar</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Riwayat Pembinaan untuk Siswa Ini (Jika Ada) -->
<?php if (! empty($riwayatPembinaan)): ?>
    <div class="card card-custom border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-0 py-3">
            <h5 class="fw-bold text-dark mb-0">
                <i class="bi bi-shield-exclamation text-primary me-2"></i>Catatan Tindakan Pembinaan yang Pernah Diterima
            </h5>
        </div>
        <div class="table-responsive">
            <table class="table table-custom table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">No</th>
                        <th style="width: 140px;">Tanggal</th>
                        <th style="width: 200px;">Jenis Tindakan</th>
                        <th>Catatan Pembinaan Walas</th>
                        <th class="text-center" style="width: 120px;">Bukti Berkas</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($riwayatPembinaan as $idx => $rp): ?>
                        <tr>
                            <td class="text-center fw-semibold text-muted"><?= $idx + 1 ?></td>
                            <td class="fw-semibold text-nowrap"><?= date('d/m/Y', strtotime($rp['tanggal_tindakan'])) ?></td>
                            <td><span class="badge bg-light text-primary border px-2 py-1"><?= esc($rp['jenis_tindakan']) ?></span></td>
                            <td class="small text-secondary"><?= nl2br(esc($rp['catatan_pembinaan'])) ?></td>
                            <td class="text-center">
                                <?php if (! empty($rp['file_bukti'])): ?>
                                    <a href="<?= base_url('uploads/pembinaan/' . $rp['file_bukti']) ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1">
                                        <i class="bi bi-paperclip me-1"></i> Berkas
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?= $this->endSection() ?>
