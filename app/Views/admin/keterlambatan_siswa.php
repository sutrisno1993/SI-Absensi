<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<!-- Flash Message Notification -->
<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm mb-4 border-0 d-print-none" role="alert">
        <i class="bi bi-check-circle-fill me-2 fs-5 align-middle"></i>
        <?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm mb-4 border-0 d-print-none" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2 fs-5 align-middle"></i>
        <?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- Header Dokumen Cetak (Hanya Muncul Saat Print) -->
<div class="d-none d-print-block mb-4 text-center border-bottom pb-3">
    <h4 class="fw-bold mb-1">SISTEM INFORMASI PRESENSI & PEMBINAAN SISWA (SI-ABSEN)</h4>
    <h5 class="fw-bold text-danger mb-1">LAPORAN KETERLAMBATAN SISWA GLOBAL (SELURUH KELAS)</h5>
    <small class="text-muted">Periode: <?= date('d/m/Y', strtotime($startDate)) ?> s/d <?= date('d/m/Y', strtotime($endDate)) ?> | Dicetak: <?= date('d F Y, H:i') ?> WIB</small>
</div>

<!-- Header Page -->
<div class="row mb-4 align-items-center d-print-none">
    <div class="col-12 col-md-6">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= site_url('admin') ?>" class="text-decoration-none">Dashboard</a></li>
                <li class="breadcrumb-item active text-muted" aria-current="page">Laporan Keterlambatan Siswa</li>
            </ol>
        </nav>
        <h3 class="fw-bold text-dark mb-1">
            <i class="bi bi-alarm-fill text-danger me-2"></i>Laporan Keterlambatan Siswa
        </h3>
        <p class="text-muted small mb-0">Pantau, analisis rombel, dan tindak lanjuti rekam jejak kedatangan siswa seluruh rombel sekolah.</p>
    </div>
    <div class="col-12 col-md-6 mt-3 mt-md-0 text-md-end d-flex flex-wrap gap-2 justify-content-md-end">
        <?php
            $exportUrl = site_url('admin/keterlambatan-siswa/export-excel') . '?' . http_build_query([
                'start_date'      => $startDate,
                'end_date'        => $endDate,
                'kelas_id'        => $kelasId,
                'status_disiplin' => $statusDisiplin,
                'q'               => $search,
            ]);
        ?>
        <a href="<?= $exportUrl ?>" class="btn btn-success fw-semibold rounded-pill px-3 shadow-sm">
            <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
        </a>
        <button type="button" class="btn btn-outline-secondary fw-semibold rounded-pill px-3 shadow-sm" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Cetak Dokumen
        </button>
        <a href="<?= site_url('admin/pembinaan') ?>" class="btn btn-outline-danger fw-semibold rounded-pill px-3 shadow-sm">
            <i class="bi bi-shield-exclamation me-1"></i> Buku Pembinaan
        </a>
    </div>
</div>

<!-- Filter Bar Komprehensif -->
<div class="card card-custom border-0 shadow-sm mb-4 d-print-none">
    <div class="card-body p-3">
        <form action="<?= site_url('admin/keterlambatan-siswa') ?>" method="GET">
            <div class="row g-2 align-items-center">
                <div class="col-12 col-md-3">
                    <label class="form-label small fw-semibold text-secondary mb-1">Rentang Tanggal:</label>
                    <div class="d-flex align-items-center gap-1">
                        <input type="date" name="start_date" id="input_start_date" class="form-control form-control-sm" value="<?= esc($startDate) ?>" required>
                        <span class="text-muted small">-</span>
                        <input type="date" name="end_date" id="input_end_date" class="form-control form-control-sm" value="<?= esc($endDate) ?>" required>
                    </div>
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold text-secondary mb-1">Filter Kelas:</label>
                    <select name="kelas_id" class="form-select form-select-sm">
                        <option value="">Semua Kelas</option>
                        <?php foreach ($kelasList as $k): ?>
                            <option value="<?= $k['id'] ?>" <?= ($kelasId == $k['id']) ? 'selected' : '' ?>>
                                <?= esc($k['nama_kelas']) ?> (<?= esc($k['shift']) ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold text-secondary mb-1">Status Disiplin:</label>
                    <select name="status_disiplin" class="form-select form-select-sm">
                        <option value="">Pernah Terlambat (>0x)</option>
                        <option value="wajib_bina" <?= ($statusDisiplin === 'wajib_bina') ? 'selected' : '' ?>>Wajib Pembinaan (>3x)</option>
                        <option value="perhatian" <?= ($statusDisiplin === 'perhatian') ? 'selected' : '' ?>>Perlu Perhatian (2-3x)</option>
                        <option value="tertib" <?= ($statusDisiplin === 'tertib') ? 'selected' : '' ?>>Tertib (0x)</option>
                        <option value="all" <?= ($statusDisiplin === 'all') ? 'selected' : '' ?>>Seluruh Siswa Aktif</option>
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <label class="form-label small fw-semibold text-secondary mb-1">Shift / Jam Masuk:</label>
                    <select name="shift" class="form-select form-select-sm">
                        <option value="">Semua Shift</option>
                        <option value="Pagi" <?= ($shift === 'Pagi') ? 'selected' : '' ?>>Pagi (06:30)</option>
                        <option value="Siang" <?= ($shift === 'Siang') ? 'selected' : '' ?>>Siang (12:30)</option>
                    </select>
                </div>

                <div class="col-6 col-md-3">
                    <label class="form-label small fw-semibold text-secondary mb-1">Cari Nama / NISN:</label>
                    <div class="input-group input-group-sm">
                        <input type="text" name="q" class="form-control" placeholder="Ketik nama atau NISN..." value="<?= esc($search ?? '') ?>">
                        <button type="submit" class="btn btn-primary-custom px-3">
                            <i class="bi bi-search"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Quick Preset Filter Buttons -->
            <div class="d-flex flex-wrap gap-2 mt-3 pt-2 border-top align-items-center">
                <span class="small fw-semibold text-muted me-1"><i class="bi bi-lightning-charge-fill text-warning me-1"></i>Preset Cepat:</span>
                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-3" onclick="setPreset('today')">Hari Ini</button>
                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-3" onclick="setPreset('7days')">7 Hari Terakhir</button>
                <a href="<?= site_url('admin/keterlambatan-siswa') ?>" class="btn btn-xs btn-outline-primary rounded-pill px-3">Bulan Ini</a>
                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-3" onclick="setPreset('semester')">Semester Ini</button>
                <a href="<?= site_url('admin/keterlambatan-siswa') ?>" class="btn btn-xs btn-light text-muted rounded-pill px-3 ms-auto">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Filter
                </a>
            </div>
        </form>
    </div>
</div>

<!-- 4 Kartu Metrik Ringkasan Eksekutif -->
<div class="row g-3 mb-4">
    <div class="col-6 col-lg-3">
        <div class="card card-custom border-0 p-3 h-100 shadow-sm">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-secondary bg-opacity-10 text-secondary">
                    <i class="bi bi-stopwatch fs-3"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Total Insiden</div>
                    <div class="fs-4 fw-bold text-dark"><?= (int)($stats['total_kejadian'] ?? 0) ?> Kali</div>
                    <small class="text-muted">Akumulasi <?= (int)($stats['total_menit'] ?? 0) ?> Menit</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card card-custom border-0 p-3 h-100 shadow-sm">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-info bg-opacity-10 text-info">
                    <i class="bi bi-people-fill fs-3"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Siswa Terlambat</div>
                    <div class="fs-4 fw-bold text-info"><?= (int)($stats['total_siswa_terlambat'] ?? 0) ?> Siswa</div>
                    <small class="text-muted">Dari <?= (int)($stats['total_kelas_terdampak'] ?? 0) ?> Rombel Terdampak</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card card-custom border-0 p-3 h-100 shadow-sm border-start border-warning border-4">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-warning bg-opacity-15 text-warning">
                    <i class="bi bi-exclamation-triangle-fill fs-3"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Perlu Perhatian (2-3x)</div>
                    <div class="fs-4 fw-bold text-warning"><?= (int)($stats['siswa_perhatian'] ?? 0) ?> Siswa</div>
                    <small class="text-muted">Laporan Wali Kelas</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-6 col-lg-3">
        <div class="card card-custom border-0 p-3 h-100 shadow-sm border-start border-danger border-4">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-danger bg-opacity-15 text-danger">
                    <i class="bi bi-shield-fill-exclamation fs-3"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Wajib Pembinaan (>3x)</div>
                    <div class="fs-4 fw-bold text-danger"><?= (int)($stats['siswa_wajib_bina'] ?? 0) ?> Siswa</div>
                    <small class="text-muted">Perlu Intervensi BK / Kesiswaan</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Nav Tabs 3-in-1: Rekap Siswa vs Analitik Rombel vs Log Piket -->
<ul class="nav nav-pills mb-3 gap-2 d-print-none" id="keterlambatanTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active rounded-pill px-4 fw-semibold shadow-xs" id="siswa-tab" data-bs-toggle="pill" data-bs-target="#tab-siswa" type="button" role="tab">
            <i class="bi bi-person-lines-fill me-1"></i> Rekap Siswa Lintas Rombel (<?= count($rekapSiswa) ?>)
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link rounded-pill px-4 fw-semibold shadow-xs" id="rombel-tab" data-bs-toggle="pill" data-bs-target="#tab-rombel" type="button" role="tab">
            <i class="bi bi-bar-chart-line-fill me-1"></i> Analitik & Ranking Rombel (<?= count($rekapKelas) ?>)
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link rounded-pill px-4 fw-semibold shadow-xs" id="log-tab" data-bs-toggle="pill" data-bs-target="#tab-log" type="button" role="tab">
            <i class="bi bi-journal-text me-1"></i> Riwayat Meja Piket Sekolah (<?= count($logTerlambat) ?>)
        </button>
    </li>
</ul>

<div class="tab-content" id="keterlambatanTabContent">
    <!-- ========================================== -->
    <!-- TAB 1: REKAP SISWA LINTAS ROMBEL -->
    <!-- ========================================== -->
    <div class="tab-pane fade show active" id="tab-siswa" role="tabpanel">
        <div class="card card-custom border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold text-dark mb-0">Daftar Rekapitulasi Siswa Terlambat</h5>
                <span class="badge bg-light text-muted border"><?= count($rekapSiswa) ?> Siswa Ditemukan</span>
            </div>
            <div class="table-responsive">
                <table class="table table-custom table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th style="width: 130px;">NISN</th>
                            <th>Nama Siswa</th>
                            <th style="width: 140px;">Kelas</th>
                            <th class="text-center" style="width: 50px;">L/P</th>
                            <th class="text-center" style="width: 120px;">Total Terlambat</th>
                            <th class="text-center" style="width: 120px;">Total Durasi</th>
                            <th style="width: 220px;">Riwayat Tanggal</th>
                            <th class="text-center" style="width: 160px;">Status Disiplin</th>
                            <th class="text-end d-print-none" style="width: 130px;">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rekapSiswa)): ?>
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">
                                    <i class="bi bi-check-circle-fill text-success fs-1 d-block mb-2"></i>
                                    Tidak ada data keterlambatan siswa yang sesuai dengan filter ini.
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($rekapSiswa as $idx => $r): ?>
                                <?php 
                                    $totT = (int)$r['total_terlambat']; 
                                    $totM = (int)$r['total_menit'];
                                ?>
                                <tr class="<?= $totT > 3 ? 'table-danger bg-opacity-25' : ($totT > 1 ? 'table-warning bg-opacity-25' : '') ?>">
                                    <td class="text-center fw-semibold text-muted"><?= $idx + 1 ?></td>
                                    <td><span class="badge bg-light text-dark border font-monospace"><?= esc($r['nisn']) ?></span></td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= esc($r['nama_siswa']) ?></div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary bg-opacity-10 text-dark fw-bold border">
                                            <?= esc($r['nama_kelas'] ?? '-') ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge <?= ($r['jenis_kelamin'] ?? 'L') === 'L' ? 'bg-info bg-opacity-15 text-primary' : 'bg-danger bg-opacity-15 text-danger' ?> rounded-pill px-2 py-1">
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
                                            <small class="text-muted font-monospace d-block" style="font-size: 0.76rem;">
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
                                    <td class="text-end d-print-none">
                                        <?php if ($totT > 0): ?>
                                            <button type="button" class="btn btn-sm <?= $totT > 3 ? 'btn-danger' : 'btn-outline-danger' ?> rounded-pill px-3 fw-bold shadow-xs" onclick="binaSiswaCepat(<?= $r['siswa_id'] ?>, '<?= addslashes(esc($r['nama_siswa'])) ?>', 'Terlambat <?= $totT ?> kali (Akumulasi <?= $totM ?> menit). Riwayat: <?= addslashes(esc($r['riwayat_tgl'] ?? '-')) ?>')">
                                                <i class="bi bi-shield-plus me-1"></i> Bina Siswa
                                            </button>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
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

    <!-- ========================================== -->
    <!-- TAB 2: ANALITIK & RANKING ROMBEL -->
    <!-- ========================================== -->
    <div class="tab-pane fade" id="tab-rombel" role="tabpanel">
        <div class="card card-custom border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold text-dark mb-0">Leaderboard & Rekapitulasi Rombel</h5>
                    <small class="text-muted">Peringkat rombel diurutkan dari kasus keterlambatan tertinggi.</small>
                </div>
                <span class="badge bg-light text-muted border"><?= count($rekapKelas) ?> Rombel Terdata</span>
            </div>
            <div class="table-responsive">
                <table class="table table-custom table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 80px;">Peringkat</th>
                            <th>Nama Kelas</th>
                            <th class="text-center" style="width: 100px;">Shift</th>
                            <th>Wali Kelas</th>
                            <th class="text-center" style="width: 120px;">Siswa Kelas</th>
                            <th class="text-center" style="width: 140px;">Siswa Terlambat</th>
                            <th class="text-center" style="width: 130px;">Total Kejadian</th>
                            <th class="text-center" style="width: 130px;">Total Durasi</th>
                            <th style="width: 200px;">Tingkat Keterlambatan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rekapKelas)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">Belum ada data rombel.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($rekapKelas as $kIdx => $rk): ?>
                                <?php
                                    $totSiswa = (int)($rk['total_siswa_kelas'] ?? 0);
                                    $totLate  = (int)($rk['total_siswa_terlambat'] ?? 0);
                                    $totInc   = (int)($rk['total_kejadian'] ?? 0);
                                    $totMnt   = (int)($rk['total_menit'] ?? 0);
                                    $pct      = $totSiswa > 0 ? round(($totLate / $totSiswa) * 100, 1) : 0;
                                    $isTop    = ($kIdx < 3 && $totInc > 0);
                                ?>
                                <tr class="<?= $isTop ? 'table-danger bg-opacity-10' : '' ?>">
                                    <td class="text-center">
                                        <?php if ($kIdx === 0 && $totInc > 0): ?>
                                            <span class="badge bg-danger rounded-circle p-2 fs-6" title="Peringkat 1 Keterlambatan Terbanyak">#1</span>
                                        <?php elseif ($kIdx === 1 && $totInc > 0): ?>
                                            <span class="badge bg-warning text-dark rounded-circle p-2 fs-6">#2</span>
                                        <?php elseif ($kIdx === 2 && $totInc > 0): ?>
                                            <span class="badge bg-secondary rounded-circle p-2 fs-6">#3</span>
                                        <?php else: ?>
                                            <span class="fw-semibold text-muted">#<?= $kIdx + 1 ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark fs-6"><?= esc($rk['nama_kelas']) ?></div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border"><?= esc($rk['shift'] ?? 'Pagi') ?></span>
                                    </td>
                                    <td class="small text-muted">
                                        <i class="bi bi-person me-1"></i><?= esc($rk['nama_walas'] ?: 'Belum ditentukan') ?>
                                    </td>
                                    <td class="text-center fw-semibold text-muted"><?= $totSiswa ?> Siswa</td>
                                    <td class="text-center">
                                        <?php if ($totLate > 0): ?>
                                            <span class="badge bg-danger bg-opacity-15 text-danger border border-danger border-opacity-25 px-2 py-1 rounded-pill fw-bold">
                                                <?= $totLate ?> Siswa
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-success bg-opacity-15 text-success rounded-pill px-2 py-1">0 Siswa</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center fw-bold fs-6 <?= $totInc > 0 ? 'text-danger' : 'text-muted' ?>">
                                        <?= $totInc ?> Kali
                                    </td>
                                    <td class="text-center fw-bold text-dark">
                                        <?= $totMnt > 0 ? $totMnt . ' Menit' : '-' ?>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="progress flex-grow-1" style="height: 8px;">
                                                <div class="progress-bar <?= $pct > 25 ? 'bg-danger' : ($pct > 10 ? 'bg-warning' : 'bg-info') ?>" role="progressbar" style="width: <?= min(100, $pct) ?>%"></div>
                                            </div>
                                            <span class="small fw-bold text-dark" style="width: 45px;"><?= $pct ?>%</span>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ========================================== -->
    <!-- TAB 3: RIWAYAT MEJA PIKET SEKOLAH -->
    <!-- ========================================== -->
    <div class="tab-pane fade" id="tab-log" role="tabpanel">
        <div class="card card-custom border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold text-dark mb-0">Catatan Kronologis Harian Meja Guru Piket</h5>
                <span class="badge bg-light text-muted border"><?= count($logTerlambat) ?> Kejadian Terdata</span>
            </div>
            <div class="table-responsive">
                <table class="table table-custom table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th style="width: 110px;">Tanggal</th>
                            <th class="text-center" style="width: 90px;">Jam</th>
                            <th style="width: 120px;">NISN</th>
                            <th>Nama Siswa</th>
                            <th style="width: 130px;">Kelas</th>
                            <th class="text-center" style="width: 100px;">Terlambat</th>
                            <th>Alasan Siswa</th>
                            <th>Tindakan Piket</th>
                            <th style="width: 130px;">Pencatat</th>
                            <th class="text-end d-print-none" style="width: 100px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logTerlambat)): ?>
                            <tr>
                                <td colspan="11" class="text-center py-5 text-muted">Belum ada catatan keterlambatan pada filter periode ini.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($logTerlambat as $lIdx => $log): ?>
                                <tr>
                                    <td class="text-center fw-semibold text-muted"><?= $lIdx + 1 ?></td>
                                    <td class="fw-semibold text-dark"><?= date('d/m/Y', strtotime($log['tanggal'])) ?></td>
                                    <td class="text-center font-monospace fw-semibold text-danger"><?= esc($log['jam_masuk']) ?></td>
                                    <td><span class="badge bg-light text-dark border font-monospace"><?= esc($log['nisn']) ?></span></td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= esc($log['nama_siswa']) ?></div>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary bg-opacity-10 text-dark fw-bold border">
                                            <?= esc($log['nama_kelas'] ?? '-') ?>
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-danger bg-opacity-15 text-danger border border-danger border-opacity-25 px-2 py-1 rounded-pill">
                                            +<?= (int)$log['menit_terlambat'] ?>m
                                        </span>
                                    </td>
                                    <td class="small text-muted"><?= esc($log['alasan'] ?: '-') ?></td>
                                    <td class="small text-dark"><?= esc($log['tindakan'] ?: '-') ?></td>
                                    <td class="small text-muted">
                                        <i class="bi bi-person-badge me-1"></i><?= esc($log['pencatat'] ?: 'Guru Piket') ?>
                                    </td>
                                    <td class="text-end d-print-none">
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1" onclick="binaSiswaCepat(<?= $log['siswa_id'] ?>, '<?= addslashes(esc($log['nama_siswa'])) ?>', 'Keterlambatan pada tanggal <?= date('d/m/Y', strtotime($log['tanggal'])) ?> (+<?= (int)$log['menit_terlambat'] ?> menit). Alasan: <?= addslashes(esc($log['alasan'] ?: 'Tidak ada keterangan')) ?>')">
                                            <i class="bi bi-shield-plus me-1"></i> Bina
                                        </button>
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

<!-- Modal Aksi Cepat Pembinaan Siswa (Admin Pusat / BK / Kesiswaan) -->
<div class="modal fade d-print-none" id="modalBinaSiswaAdmin" tabindex="-1" aria-labelledby="modalBinaSiswaAdminLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-danger text-white py-3">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="modalBinaSiswaAdminLabel">
                    <i class="bi bi-shield-shaded"></i>
                    Input Tindakan Pembinaan Siswa (Admin Pusat / Kesiswaan / BK)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form action="<?= site_url('admin/pembinaan/simpan') ?>" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="redirect_to" value="<?= current_url(true)->getPath() . '?' . http_build_query($_GET) ?>">
                
                <div class="modal-body py-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-7">
                            <label for="admin_modal_siswa_id" class="form-label fw-semibold small text-secondary">Pilih Siswa Binaan <span class="text-danger">*</span></label>
                            <select class="form-select" id="admin_modal_siswa_id" name="siswa_id" required>
                                <option value="">-- Cari / Pilih Siswa --</option>
                                <?php foreach ($daftarSiswa as $s): ?>
                                    <option value="<?= $s['id'] ?>">
                                        <?= esc($s['nama_siswa']) ?> (<?= esc($s['nisn']) ?>) - <?= esc($s['nama_kelas'] ?? 'Rombel') ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-5">
                            <label for="admin_tanggal_tindakan" class="form-label fw-semibold small text-secondary">Tanggal Tindakan <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="admin_tanggal_tindakan" name="tanggal_tindakan" value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="col-12">
                            <label for="admin_jenis_tindakan" class="form-label fw-semibold small text-secondary">Jenis Tindakan Pembinaan <span class="text-danger">*</span></label>
                            <select class="form-select" id="admin_jenis_tindakan" name="jenis_tindakan" required>
                                <option value="">-- Pilih Jenis Tindakan --</option>
                                <option value="Konseling Khusus Guru BK" selected>Konseling Khusus Guru BK</option>
                                <option value="Pemanggilan Orang Tua / Wali Siswa">Pemanggilan Orang Tua / Wali Siswa</option>
                                <option value="Surat Peringatan 1 (SP1)">Surat Peringatan 1 (SP1)</option>
                                <option value="Surat Peringatan 2 (SP2)">Surat Peringatan 2 (SP2)</option>
                                <option value="Surat Peringatan Terakhir (SP3) & Skorsing">Surat Peringatan Terakhir (SP3) & Skorsing</option>
                                <option value="Sidang Pleno Komite Disiplin Kesiswaan">Sidang Pleno Komite Disiplin Kesiswaan</option>
                                <option value="Kunjungan Rumah (Homevisit) Terpadu">Kunjungan Rumah (Homevisit) Terpadu</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="admin_catatan_pembinaan" class="form-label fw-semibold small text-secondary">Catatan Pembinaan & Hasil Sidang / Konseling <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="admin_catatan_pembinaan" name="catatan_pembinaan" rows="4" placeholder="Uraikan pelanggaran keterlambatan yang terjadi, hasil pertemuan dengan siswa dan orang tua, serta komitmen tertulis..." required></textarea>
                        </div>

                        <div class="col-12">
                            <label for="admin_file_bukti" class="form-label fw-semibold small text-secondary">Upload Bukti Berkas (Foto Konseling / Surat Panggilan / Berita Acara / PDF)</label>
                            <input type="file" class="form-control" id="admin_file_bukti" name="file_bukti" accept=".jpg,.jpeg,.png,.pdf">
                            <div class="form-text small">Format file: JPG, PNG, atau PDF. Ukuran berkas maksimal 2MB.</div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger fw-bold rounded-pill px-4 shadow-sm">
                        <i class="bi bi-save-fill me-1"></i> Simpan Catatan Pembinaan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
@media print {
    .sidebar, .topbar, .navbar, .btn, .nav-pills, .alert, form, .card-header span, .d-print-none {
        display: none !important;
    }
    body {
        background: #fff !important;
        font-size: 10pt;
    }
    .card {
        border: none !important;
        box-shadow: none !important;
    }
    .table {
        border: 1px solid #000 !important;
        width: 100% !important;
    }
    .table th, .table td {
        border: 1px solid #000 !important;
        padding: 4px !important;
    }
    .badge {
        border: 1px solid #999 !important;
        color: #000 !important;
        background: transparent !important;
    }
}
</style>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function binaSiswaCepat(siswaId, namaSiswa, alasanDefault) {
        var selectSiswa = document.getElementById('admin_modal_siswa_id');
        if (selectSiswa) {
            selectSiswa.value = siswaId;
        }
        var catatanEl = document.getElementById('admin_catatan_pembinaan');
        if (catatanEl && alasanDefault) {
            catatanEl.value = 'Pembinaan Pelanggaran Disiplin Keterlambatan: ' + alasanDefault + '.\nSiswa bersedia menandatangani komitmen perbaikan disiplin.';
        }
        var modal = new bootstrap.Modal(document.getElementById('modalBinaSiswaAdmin'));
        modal.show();
    }

    function setPreset(tipe) {
        var startInput = document.getElementById('input_start_date');
        var endInput   = document.getElementById('input_end_date');
        var now = new Date();

        function formatDate(d) {
            var month = '' + (d.getMonth() + 1);
            var day = '' + d.getDate();
            var year = d.getFullYear();
            if (month.length < 2) month = '0' + month;
            if (day.length < 2) day = '0' + day;
            return [year, month, day].join('-');
        }

        if (tipe === 'today') {
            startInput.value = formatDate(now);
            endInput.value   = formatDate(now);
        } else if (tipe === '7days') {
            var past7 = new Date();
            past7.setDate(now.getDate() - 7);
            startInput.value = formatDate(past7);
            endInput.value   = formatDate(now);
        } else if (tipe === 'semester') {
            var curMonth = now.getMonth() + 1;
            var curYear  = now.getFullYear();
            if (curMonth >= 7) {
                startInput.value = curYear + '-07-01';
                endInput.value   = curYear + '-12-31';
            } else {
                startInput.value = curYear + '-01-01';
                endInput.value   = curYear + '-06-30';
            }
        }
        startInput.closest('form').submit();
    }
</script>
<?= $this->endSection() ?>
