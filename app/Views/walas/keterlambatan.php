<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="row mb-4 align-items-center">
    <div class="col-12 col-md-7">
        <h3 class="fw-bold text-dark mb-1">
            <i class="bi bi-clock-history text-danger me-2"></i>Laporan Keterlambatan Siswa
        </h3>
        <p class="text-muted small mb-0">Pantau catatan keterlambatan murid kelas <?= esc($kelas['nama_kelas']) ?> yang dicatat oleh Tim Guru Piket.</p>
    </div>
    <div class="col-12 col-md-5 mt-3 mt-md-0 text-md-end">
        <a href="<?= site_url('walas/rekap') ?>" class="btn btn-outline-primary fw-semibold rounded-pill px-3 shadow-sm me-2">
            <i class="bi bi-table me-1"></i> Rekap Presensi
        </a>
        <a href="<?= site_url('walas/evaluasi-semester') ?>" class="btn btn-primary-custom fw-semibold rounded-pill px-3 shadow-sm">
            <i class="bi bi-clipboard2-data me-1"></i> Evaluasi Semester
        </a>
    </div>
</div>

<!-- Filter Rentang Tanggal -->
<div class="card card-custom border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form action="<?= site_url('walas/keterlambatan') ?>" method="GET" class="row g-2 align-items-center">
            <div class="col-12 col-sm-auto">
                <span class="fw-semibold text-secondary small"><i class="bi bi-funnel-fill me-1"></i> Rentang Tanggal:</span>
            </div>
            <div class="col-6 col-sm-auto">
                <input type="date" name="start_date" class="form-control form-control-sm" value="<?= esc($startDate) ?>" required>
            </div>
            <div class="col-auto text-muted small">s/d</div>
            <div class="col-6 col-sm-auto">
                <input type="date" name="end_date" class="form-control form-control-sm" value="<?= esc($endDate) ?>" required>
            </div>
            <div class="col-12 col-sm-auto d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary-custom px-3">
                    <i class="bi bi-search me-1"></i> Filter
                </button>
                <a href="<?= site_url('walas/keterlambatan') ?>" class="btn btn-sm btn-outline-secondary px-3">
                    Bulan Ini
                </a>
            </div>
        </form>
    </div>
</div>

<!-- Kartu Ringkasan Keterlambatan -->
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card card-custom border-0 p-3 h-100 shadow-sm">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-secondary bg-opacity-10 text-secondary">
                    <i class="bi bi-stopwatch fs-3"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Total Insiden</div>
                    <div class="fs-4 fw-bold text-dark"><?= $totalKejadian ?> Kali</div>
                    <small class="text-muted">Kejadian terlambat</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card card-custom border-0 p-3 h-100 shadow-sm">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-info bg-opacity-10 text-info">
                    <i class="bi bi-people-fill fs-3"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Siswa Terlambat</div>
                    <div class="fs-4 fw-bold text-info"><?= $totalSiswaTerlambat ?> Siswa</div>
                    <small class="text-muted">Pernah terlambat</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card card-custom border-0 p-3 h-100 shadow-sm border-start border-warning border-4">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-warning bg-opacity-15 text-warning">
                    <i class="bi bi-exclamation-triangle-fill fs-3"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Perlu Perhatian (>1x)</div>
                    <div class="fs-4 fw-bold text-warning"><?= $siswaPerhatian ?> Siswa</div>
                    <small class="text-muted">Dilaporkan ke Walas</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-md-3">
        <div class="card card-custom border-0 p-3 h-100 shadow-sm border-start border-danger border-4">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-danger bg-opacity-15 text-danger">
                    <i class="bi bi-shield-fill-exclamation fs-3"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Wajib Dibina (>3x)</div>
                    <div class="fs-4 fw-bold text-danger"><?= $siswaWajibBina ?> Siswa</div>
                    <small class="text-muted">Tindakan Pembinaan</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Ketentuan Tata Tertib Keterlambatan -->
<div class="alert alert-light border border-warning border-opacity-50 rounded-4 p-3 mb-4 d-flex align-items-start gap-3 shadow-xs">
    <div class="rounded-circle bg-warning bg-opacity-15 text-warning p-2 mt-1">
        <i class="bi bi-shield-exclamation fs-4"></i>
    </div>
    <div class="small">
        <strong class="text-dark d-block mb-1">Standar Operasional Disiplin & Keterlambatan:</strong>
        <ul class="mb-0 ps-3 text-muted">
            <li><strong>Terlambat > 1 kali:</strong> Sistem otomatis memberikan penanda <span class="badge bg-warning text-dark">Perlu Perhatian</span> dan dilaporkan ke Wali Kelas untuk dipantau secara berkala.</li>
            <li><strong>Terlambat > 3 kali:</strong> Dinyatakan <span class="badge bg-danger">Wajib Pembinaan</span>. Wali Kelas dan Guru BK wajib melakukan konseling, pemanggilan orang tua, dan menerbitkan surat peringatan pembinaan.</li>
        </ul>
    </div>
</div>

<!-- Nav Tabs: Rekap Per Siswa vs Riwayat Log Harian -->
<ul class="nav nav-pills mb-3 gap-2" id="keterlambatanTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active rounded-pill px-4 fw-semibold shadow-xs" id="rekap-tab" data-bs-toggle="pill" data-bs-target="#tab-rekap" type="button" role="tab">
            <i class="bi bi-person-lines-fill me-1"></i> Rekap Per Siswa
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link rounded-pill px-4 fw-semibold shadow-xs" id="log-tab" data-bs-toggle="pill" data-bs-target="#tab-log" type="button" role="tab">
            <i class="bi bi-journal-text me-1"></i> Riwayat Insiden Keterlambatan (<?= count($logTerlambat) ?>)
        </button>
    </li>
</ul>

<div class="tab-content" id="keterlambatanTabContent">
    <!-- TAB 1: REKAP PER SISWA -->
    <div class="tab-pane fade show active" id="tab-rekap" role="tabpanel">
        <div class="card card-custom border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold text-dark mb-0">Rekapitulasi Keterlambatan Siswa Kelas <?= esc($kelas['nama_kelas']) ?></h5>
                <span class="badge bg-light text-muted border"><?= count($rekapTerlambat) ?> Siswa Aktif</span>
            </div>
            <div class="table-responsive">
                <table class="table table-custom table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th style="width: 140px;">NISN</th>
                            <th>Nama Siswa</th>
                            <th class="text-center" style="width: 60px;">L/P</th>
                            <th class="text-center" style="width: 130px;">Total Terlambat</th>
                            <th class="text-center" style="width: 130px;">Total Durasi</th>
                            <th style="width: 250px;">Riwayat Tanggal</th>
                            <th class="text-center" style="width: 180px;">Status Disiplin</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rekapTerlambat)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-check-circle-fill text-success fs-1 d-block mb-2"></i>
                                    Tidak ada catatan keterlambatan pada periode ini.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($rekapTerlambat as $idx => $r): ?>
                                <?php 
                                    $totT = (int)$r['total_terlambat']; 
                                    $totM = (int)$r['total_menit'];
                                ?>
                                <tr class="<?= $totT > 3 ? 'table-danger bg-opacity-25' : ($totT > 1 ? 'table-warning bg-opacity-25' : '') ?>">
                                    <td class="text-center fw-semibold text-muted"><?= $idx + 1 ?></td>
                                    <td><span class="badge bg-light text-dark border font-monospace"><?= esc($r['nisn']) ?></span></td>
                                    <td class="fw-bold text-dark"><?= esc($r['nama_siswa']) ?></td>
                                    <td class="text-center">
                                        <span class="badge <?= ($r['jenis_kelamin'] ?? 'L') === 'L' ? 'bg-info bg-opacity-15 text-primary border border-info border-opacity-25' : 'bg-danger bg-opacity-15 text-danger border border-danger border-opacity-25' ?> rounded-pill px-2 py-1">
                                            <?= esc($r['jenis_kelamin'] ?? 'L') ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($totT > 0): ?>
                                            <span class="badge bg-danger rounded-pill px-3 py-1 fs-6"><?= $totT ?> Kali</span>
                                        <?php else: ?>
                                            <span class="text-muted fw-semibold">0</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($totM > 0): ?>
                                            <span class="fw-bold text-dark"><?= $totM ?> Menit</span>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($r['riwayat_tgl'])): ?>
                                            <small class="text-muted font-monospace d-block" style="font-size: 0.78rem;">
                                                <?= esc($r['riwayat_tgl']) ?>
                                            </small>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($totT > 3): ?>
                                            <span class="badge bg-danger text-white border border-danger px-3 py-2 rounded-pill shadow-xs">
                                                <i class="bi bi-shield-fill-exclamation me-1"></i> Wajib Pembinaan
                                            </span>
                                        <?php elseif ($totT > 1): ?>
                                            <span class="badge bg-warning text-dark border border-warning px-3 py-2 rounded-pill shadow-xs">
                                                <i class="bi bi-exclamation-triangle-fill me-1"></i> Perlu Perhatian
                                            </span>
                                        <?php elseif ($totT === 1): ?>
                                            <span class="badge bg-info bg-opacity-15 text-primary border border-info border-opacity-25 px-2 py-1 rounded-pill">
                                                Tercatat 1x
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-success bg-opacity-15 text-success border border-success border-opacity-25 px-2 py-1 rounded-pill">
                                                <i class="bi bi-check-circle-fill me-1"></i> Tertib
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- TAB 2: RIWAYAT DETAIL LOG HARIAN PIKET -->
    <div class="tab-pane fade" id="tab-log" role="tabpanel">
        <div class="card card-custom border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="fw-bold text-dark mb-0">Catatan Harian Guru Piket</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-custom table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th style="width: 120px;">Tanggal</th>
                            <th>Nama Siswa</th>
                            <th class="text-center" style="width: 110px;">Jam Masuk</th>
                            <th class="text-center" style="width: 120px;">Terlambat</th>
                            <th>Alasan Siswa</th>
                            <th>Tindakan Piket</th>
                            <th style="width: 140px;">Pencatat</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logTerlambat)): ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">Belum ada riwayat keterlambatan pada periode ini.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($logTerlambat as $lIdx => $log): ?>
                                <tr>
                                    <td class="text-center fw-semibold text-muted"><?= $lIdx + 1 ?></td>
                                    <td class="fw-semibold text-dark"><?= date('d/m/Y', strtotime($log['tanggal'])) ?></td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= esc($log['nama_siswa']) ?></div>
                                        <small class="text-muted font-monospace"><?= esc($log['nisn']) ?></small>
                                    </td>
                                    <td class="text-center font-monospace fw-semibold text-danger"><?= esc($log['jam_masuk']) ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-danger bg-opacity-15 text-danger border border-danger border-opacity-25 px-2 py-1 rounded-pill">
                                            +<?= (int)$log['menit_terlambat'] ?> Menit
                                        </span>
                                    </td>
                                    <td class="small text-muted"><?= esc($log['alasan'] ?: '-') ?></td>
                                    <td class="small text-dark"><?= esc($log['tindakan'] ?: '-') ?></td>
                                    <td class="small text-muted">
                                        <i class="bi bi-person-badge me-1"></i> <?= esc($log['pencatat'] ?: 'Guru Piket') ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
