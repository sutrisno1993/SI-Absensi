<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="row mb-4 align-items-center">
    <div class="col-12 col-md-8">
        <h3 class="fw-bold text-dark mb-1">
            <i class="bi bi-speedometer2 text-primary me-2"></i>Dashboard Administrator
        </h3>
        <p class="text-muted small mb-0">Selamat datang di panel kontrol Sistem Informasi Presensi & Pembinaan Siswa (SI-ABSEN).</p>
    </div>
    <div class="col-12 col-md-4 text-md-end mt-3 mt-md-0">
        <a href="<?= site_url('admin/laporan') ?>" class="btn btn-primary-custom">
            <i class="bi bi-file-earmark-bar-graph me-1"></i> Laporan Global Sekolah
        </a>
    </div>
</div>

<!-- Statistik Metrik Utama -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-custom border-0 p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-person-badge-fill fs-3"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Total Guru</div>
                    <div class="fs-4 fw-bold text-dark"><?= $totalGuru ?> Orang</div>
                    <a href="<?= site_url('admin/guru') ?>" class="small text-decoration-none">Kelola Guru &rarr;</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-custom border-0 p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-success bg-opacity-10 text-success">
                    <i class="bi bi-door-open-fill fs-3"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Total Kelas</div>
                    <div class="fs-4 fw-bold text-dark"><?= $totalKelas ?> Rombel</div>
                    <a href="<?= site_url('admin/kelas') ?>" class="small text-success text-decoration-none">Kelola Kelas &rarr;</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-custom border-0 p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-info bg-opacity-10 text-info">
                    <i class="bi bi-people-fill fs-3"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Total Siswa</div>
                    <div class="fs-4 fw-bold text-dark"><?= $totalSiswa ?> Murid</div>
                    <a href="<?= site_url('admin/siswa') ?>" class="small text-info text-decoration-none">Kelola Siswa &rarr;</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-custom border-0 p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-shield-lock-fill fs-3"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Pengguna Sistem</div>
                    <div class="fs-4 fw-bold text-dark"><?= $totalUser ?> Akun</div>
                    <a href="<?= site_url('admin/users') ?>" class="small text-warning text-decoration-none">Kelola Akun &rarr;</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- SECTION: DAFTAR SISWA PALING BANYAK ALPA (TANPA KETERANGAN) -->
<div class="card card-custom border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2">
            <div class="rounded-3 p-2 bg-danger bg-opacity-10 text-danger">
                <i class="bi bi-person-x-fill fs-4"></i>
            </div>
            <div>
                <h5 class="fw-bold text-dark mb-0">Daftar Siswa Paling Banyak Alpa (Tanpa Keterangan)</h5>
                <small class="text-muted">Peringkat siswa dengan akumulasi ketidakhadiran tanpa izin tertinggi di sekolah</small>
            </div>
        </div>
        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-3 py-2 rounded-pill">
            Monitoring Kedisiplinan Siswa
        </span>
    </div>

    <div class="table-responsive">
        <table class="table table-custom table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="text-center" style="width: 60px;">Peringkat</th>
                    <th style="width: 140px;">NISN</th>
                    <th>Nama Lengkap Siswa</th>
                    <th style="width: 140px;">Kelas</th>
                    <th>Wali Kelas</th>
                    <th class="text-center" style="width: 140px;">Total Alpa</th>
                    <th class="text-center" style="width: 170px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($topSiswaAlpa)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-check-circle-fill fs-2 text-success d-block mb-2"></i>
                            <span class="fw-bold text-success">Tidak Ada Siswa Alpa!</span>
                            <div class="small text-muted">Seluruh siswa tercatat hadir dengan tertib atau memiliki keterangan resmi.</div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($topSiswaAlpa as $rank => $sa): ?>
                        <tr>
                            <td class="text-center">
                                <?php if ($rank === 0): ?>
                                    <span class="badge rounded-circle bg-warning text-dark p-2 fs-6 shadow-sm" title="Peringkat 1 Terbanyak">🥇</span>
                                <?php elseif ($rank === 1): ?>
                                    <span class="badge rounded-circle bg-secondary text-white p-2 fs-6 shadow-sm" title="Peringkat 2 Terbanyak">🥈</span>
                                <?php elseif ($rank === 2): ?>
                                    <span class="badge rounded-circle bg-danger bg-opacity-75 text-white p-2 fs-6 shadow-sm" title="Peringkat 3 Terbanyak">🥉</span>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted border"><?= $rank + 1 ?></span>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge bg-light text-dark border font-monospace"><?= esc($sa['nisn']) ?></span></td>
                            <td>
                                <a href="<?= site_url('admin/laporan/siswa/' . $sa['siswa_id']) ?>" class="fw-bold text-dark text-decoration-none d-inline-flex align-items-center gap-1">
                                    <span><?= esc($sa['nama_siswa']) ?></span>
                                    <i class="bi bi-box-arrow-up-right small text-primary"></i>
                                </a>
                            </td>
                            <td>
                                <a href="<?= site_url('admin/laporan/kelas/' . $sa['kelas_id']) ?>" class="badge bg-primary bg-opacity-10 text-primary border border-primary text-decoration-none px-2 py-1">
                                    <?= esc($sa['nama_kelas']) ?>
                                </a>
                            </td>
                            <td><span class="text-secondary small"><?= esc($sa['nama_walas'] ?? 'Belum Ditentukan') ?></span></td>
                            <td class="text-center">
                                <span class="badge bg-danger px-3 py-2 fs-6 rounded-pill shadow-sm">
                                    <i class="bi bi-x-circle me-1"></i> <?= $sa['total_alpa'] ?> Hari Alpa
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="<?= site_url('admin/laporan/siswa/' . $sa['siswa_id']) ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 text-nowrap">
                                    <i class="bi bi-clock-history me-1"></i> Rekam Jejak &rarr;
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Rekapitulasi Presensi Seluruh Kelas -->
    <div class="col-12 col-xl-7">
        <div class="card card-custom border-0 h-100">
            <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
                <h5 class="fw-bold text-dark mb-0">
                    <i class="bi bi-bar-chart-line text-primary me-2"></i>Rekap Presensi per Kelas
                </h5>
                <span class="badge bg-light text-muted border">Akumulatif</span>
            </div>
            <div class="table-responsive">
                <table class="table table-custom table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Kelas</th>
                            <th>Wali Kelas</th>
                            <th class="text-center">Siswa</th>
                            <th class="text-center">Hadir</th>
                            <th class="text-center">Alpa</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rekapGlobal)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Belum ada data kelas.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($rekapGlobal as $rg): ?>
                                <tr>
                                    <td class="fw-bold text-dark"><?= esc($rg['nama_kelas']) ?></td>
                                    <td><?= esc($rg['nama_walas'] ?? 'Belum Ditentukan') ?></td>
                                    <td class="text-center"><span class="badge bg-light text-dark border"><?= $rg['total_siswa'] ?></span></td>
                                    <td class="text-center"><span class="badge badge-h px-2 py-1"><?= $rg['total_h'] ?></span></td>
                                    <td class="text-center">
                                        <span class="badge badge-a px-2 py-1 <?= $rg['total_a'] > 0 ? 'bg-danger text-white' : '' ?>">
                                            <?= $rg['total_a'] ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Aktivitas Pembinaan Terakhir -->
    <div class="col-12 col-xl-5">
        <div class="card card-custom border-0 h-100">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="fw-bold text-dark mb-0">
                    <i class="bi bi-clock-history text-primary me-2"></i>Pembinaan Terkini
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($riwayatPembinaan)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-check-circle fs-1 text-success d-block mb-2"></i>
                        Belum ada tindakan pembinaan dicatat.
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach (array_slice($riwayatPembinaan, 0, 5) as $rp): ?>
                            <div class="list-group-item px-3 py-3 border-0 border-bottom">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold text-dark"><?= esc($rp['nama_siswa']) ?> <span class="badge bg-light text-muted border"><?= esc($rp['nama_kelas']) ?></span></span>
                                    <small class="text-muted"><?= date('d/m/Y', strtotime($rp['tanggal_tindakan'])) ?></small>
                                </div>
                                <div class="small text-primary fw-semibold mb-1"><?= esc($rp['jenis_tindakan']) ?></div>
                                <div class="small text-muted text-truncate"><?= esc($rp['catatan_pembinaan']) ?></div>
                                <small class="text-secondary d-block mt-1"><i class="bi bi-person me-1"></i> Walas: <?= esc($rp['nama_walas']) ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
