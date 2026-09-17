<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="row mb-4 align-items-center">
    <div class="col-12 col-md-7">
        <div class="d-flex align-items-center gap-2 mb-1">
            <span class="badge bg-primary text-white rounded-pill px-3 py-1">
                <i class="bi bi-shield-check me-1"></i> MEJA PIKET SEKOLAH
            </span>
            <span class="text-muted small">Hari ini: <strong><?= $hari ?>, <?= date('d M Y') ?></strong></span>
        </div>
        <h3 class="fw-bold text-dark mb-0">Portal Layanan Guru Piket</h3>
    </div>
    <div class="col-12 col-md-5 text-md-end mt-3 mt-md-0 d-flex justify-content-md-end gap-2">
        <a href="<?= site_url('piket/guru-absen') ?>" class="btn btn-primary-custom">
            <i class="bi bi-person-check-fill me-1"></i> Presensi Guru
        </a>
        <a href="<?= site_url('piket/siswa-terlambat') ?>" class="btn btn-danger text-white rounded-3 shadow-sm">
            <i class="bi bi-alarm-fill me-1"></i> Catat Siswa Terlambat
        </a>
    </div>
</div>

<!-- Shift Info Alert -->
<div class="card border-0 shadow-sm rounded-4 mb-4" style="background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%); color: white;">
    <div class="card-body p-4">
        <div class="row align-items-center">
            <div class="col-12 col-md-8">
                <div class="d-flex align-items-center gap-3 mb-2">
                    <span class="badge bg-white text-primary px-3 py-2 fw-bold fs-6 rounded-pill">
                        Shift Aktif: <?= $activeShift ?>
                    </span>
                    <span class="text-white-50 small">
                        Batas Jam Masuk: <strong><?= $activeShift === 'Pagi' ? '07:00 WIB' : '12:30 WIB' ?></strong>
                    </span>
                </div>
                <h4 class="fw-bold mb-1">Operasional Piket Berlangsung</h4>
                <p class="mb-0 text-white-50 small">
                    Pastikan kedatangan guru dan siswa yang hadir melewati batas waktu tercatat dengan tertib.
                </p>
            </div>
            <div class="col-12 col-md-4 text-md-end mt-3 mt-md-0">
                <div class="bg-white bg-opacity-10 p-3 rounded-4 backdrop-blur">
                    <div class="text-white-50 small">Petugas Piket Hari Ini (<?= $hari ?>):</div>
                    <div class="fw-bold mt-1">
                        <?php 
                        $petugasAktif = ($activeShift === 'Pagi') ? $petugasPagi : $petugasSiang;
                        ?>
                        <?php if (empty($petugasAktif)): ?>
                            <span class="badge bg-warning text-dark">Belum Diatur Admin</span>
                        <?php else: ?>
                            <?php foreach ($petugasAktif as $p): ?>
                                <div><i class="bi bi-person-circle me-1"></i> <?= esc($p['nama_guru']) ?></div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Statistik Cepat Hari Ini -->
<div class="row g-3 mb-4">
    <!-- Siswa Terlambat -->
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card card-custom border-0 shadow-sm p-3 h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-danger bg-opacity-10 text-danger fs-3">
                    <i class="bi bi-clock-history"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Siswa Terlambat Hari Ini</div>
                    <div class="fs-4 fw-bold text-dark"><?= $terlambatHariIni ?> Orang</div>
                    <a href="<?= site_url('piket/siswa-terlambat') ?>" class="small text-danger text-decoration-none">Input Keterlambatan &rarr;</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Guru Shift Pagi -->
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card card-custom border-0 shadow-sm p-3 h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-warning bg-opacity-15 text-warning-emphasis fs-3">
                    <i class="bi bi-sun-fill text-warning"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Kehadiran Guru (Pagi)</div>
                    <div class="fs-4 fw-bold text-dark">
                        <?= $countGuruPagi ?> <span class="fs-6 text-muted fw-normal">/ <?= $wajibGuruPagi ?> Wajib</span>
                    </div>
                    <a href="<?= site_url('piket/guru-absen?shift=Pagi') ?>" class="small text-primary text-decoration-none">Buka Presensi Pagi &rarr;</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Guru Shift Siang -->
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card card-custom border-0 shadow-sm p-3 h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-info bg-opacity-10 text-primary fs-3">
                    <i class="bi bi-cloud-sun-fill text-primary"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Kehadiran Guru (Siang)</div>
                    <div class="fs-4 fw-bold text-dark">
                        <?= $countGuruSiang ?> <span class="fs-6 text-muted fw-normal">/ <?= $wajibGuruSiang ?> Wajib</span>
                    </div>
                    <a href="<?= site_url('piket/guru-absen?shift=Siang') ?>" class="small text-primary text-decoration-none">Buka Presensi Siang &rarr;</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 5 Siswa Terlambat Terakhir Hari Ini -->
<div class="card card-custom border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <div class="rounded-3 p-2 bg-danger bg-opacity-10 text-danger">
                <i class="bi bi-alarm-fill fs-5"></i>
            </div>
            <div>
                <h5 class="fw-bold text-dark mb-0">Catatan Siswa Terlambat Hari Ini</h5>
                <small class="text-muted">Daftar siswa yang datang terlambat dan mendapatkan pembinaan meja piket</small>
            </div>
        </div>
        <a href="<?= site_url('piket/siswa-terlambat') ?>" class="btn btn-sm btn-outline-danger">
            Lihat Semua (<?= $terlambatHariIni ?>) &rarr;
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-custom table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="text-center" style="width: 50px;">No</th>
                    <th style="width: 140px;">Jam Datang</th>
                    <th style="width: 140px;">NISN</th>
                    <th>Nama Siswa</th>
                    <th style="width: 130px;">Kelas</th>
                    <th class="text-center" style="width: 120px;">Terlambat</th>
                    <th>Alasan / Pembinaan</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentTerlambat)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-check-circle-fill text-success fs-3 d-block mb-1"></i>
                            <span class="fw-bold text-dark">Belum ada siswa terlambat hari ini.</span>
                            <div class="small">Semua siswa hadir tepat waktu atau belum ada catatan baru.</div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recentTerlambat as $idx => $rt): ?>
                        <tr>
                            <td class="text-center fw-semibold text-muted"><?= $idx + 1 ?></td>
                            <td class="fw-bold text-danger font-monospace">
                                <i class="bi bi-clock me-1"></i> <?= esc($rt['jam_masuk']) ?>
                            </td>
                            <td><span class="badge bg-light text-dark border font-monospace"><?= esc($rt['nisn']) ?></span></td>
                            <td class="fw-bold text-dark"><?= esc($rt['nama_siswa']) ?></td>
                            <td><span class="badge bg-light text-primary border"><?= esc($rt['nama_kelas'] ?? '-') ?></span></td>
                            <td class="text-center">
                                <?php if ($rt['menit_terlambat'] > 0): ?>
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">
                                        +<?= $rt['menit_terlambat'] ?> menit
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-light text-secondary border">0 mnt</span>
                                <?php endif; ?>
                            </td>
                            <td class="small">
                                <div class="text-dark fw-semibold"><?= esc($rt['alasan'] ?? 'Tanpa alasan') ?></div>
                                <?php if (! empty($rt['tindakan'])): ?>
                                    <div class="text-muted"><i class="bi bi-shield me-1"></i> <?= esc($rt['tindakan']) ?></div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
<?= $this->endSection() ?>
