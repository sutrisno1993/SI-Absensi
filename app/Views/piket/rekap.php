<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="row mb-4 align-items-center">
    <div class="col-12 col-md-8">
        <h3 class="fw-bold text-dark mb-1">
            <i class="bi bi-file-earmark-text text-primary me-2"></i>Rekapitulasi Layanan Guru Piket
        </h3>
        <p class="text-muted small mb-0">Laporan rekap presensi guru dan keterlambatan siswa yang tercatat di meja piket.</p>
    </div>
    <div class="col-12 col-md-4 text-md-end mt-3 mt-md-0 d-flex flex-wrap justify-content-md-end gap-2">
        <a href="<?= site_url('piket/rekap/export-excel?' . http_build_query(['start_date' => $startDate, 'end_date' => $endDate])) ?>" class="btn btn-outline-success">
            <i class="bi bi-file-earmark-excel-fill me-1"></i> Download Excel (.xlsx)
        </a>
        <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Cetak Laporan
        </button>
    </div>
</div>

<!-- Filter Rentang Tanggal -->
<div class="card card-custom border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form action="<?= site_url('piket/rekap') ?>" method="GET">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-sm-6 col-md-4">
                    <label class="form-label small fw-semibold text-secondary mb-1">Mulai Tanggal</label>
                    <input type="date" name="start_date" class="form-control" value="<?= esc($startDate) ?>">
                </div>
                <div class="col-12 col-sm-6 col-md-4">
                    <label class="form-label small fw-semibold text-secondary mb-1">Sampai Tanggal</label>
                    <input type="date" name="end_date" class="form-control" value="<?= esc($endDate) ?>">
                </div>
                <div class="col-12 col-md-4">
                    <button type="submit" class="btn btn-primary-custom px-4">
                        <i class="bi bi-filter me-1"></i> Terapkan Filter
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Tabs Rekap Siswa Terlambat & Presensi Guru -->
<div class="card card-custom border-0 shadow-sm">
    <div class="card-header bg-white border-bottom-0 pt-3 px-3">
        <ul class="nav nav-pills card-header-pills" id="rekapPills" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold" id="pills-terlambat-tab" data-bs-toggle="pill" data-bs-target="#pills-terlambat" type="button" role="tab">
                    <i class="bi bi-alarm-fill text-danger me-1"></i> Rekap Siswa Terlambat (<?= count($rekapTerlambat) ?>)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold" id="pills-guru-tab" data-bs-toggle="pill" data-bs-target="#pills-guru" type="button" role="tab">
                    <i class="bi bi-person-badge-fill text-primary me-1"></i> Rekap Presensi Guru (<?= count($rekapGuru) ?>)
                </button>
            </li>
        </ul>
    </div>

    <div class="card-body p-0">
        <div class="tab-content" id="rekapPillsContent">
            <!-- Tab Siswa Terlambat -->
            <div class="tab-pane fade show active" id="pills-terlambat" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-custom table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 50px;">No</th>
                                <th style="width: 120px;">Tanggal</th>
                                <th style="width: 100px;">Jam Datang</th>
                                <th>Nama Siswa</th>
                                <th style="width: 130px;">Kelas</th>
                                <th class="text-center" style="width: 120px;">Keterlambatan</th>
                                <th>Alasan & Tindakan Pembinaan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($rekapTerlambat)): ?>
                                <tr>
                                    <td colspan="7" class="text-center py-5 text-muted">Tidak ada data keterlambatan siswa pada periode ini.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($rekapTerlambat as $idx => $rt): ?>
                                    <tr>
                                        <td class="text-center fw-semibold text-muted"><?= $idx + 1 ?></td>
                                        <td class="fw-semibold text-dark"><?= esc($rt['tanggal']) ?></td>
                                        <td class="fw-bold text-danger font-monospace"><?= esc(substr($rt['jam_masuk'], 0, 5)) ?></td>
                                        <td>
                                            <div class="fw-bold text-dark"><?= esc($rt['nama_siswa']) ?></div>
                                            <div class="small text-muted font-monospace">NISN: <?= esc($rt['nisn']) ?></div>
                                        </td>
                                        <td><span class="badge bg-light text-primary border"><?= esc($rt['nama_kelas'] ?? '-') ?></span></td>
                                        <td class="text-center">
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">
                                                +<?= $rt['menit_terlambat'] ?> menit
                                            </span>
                                        </td>
                                        <td class="small">
                                            <div class="fw-semibold text-dark"><?= esc($rt['alasan'] ?? 'Tanpa alasan') ?></div>
                                            <?php if (! empty($rt['tindakan'])): ?>
                                                <div class="text-muted"><i class="bi bi-shield-check text-success me-1"></i> <?= esc($rt['tindakan']) ?></div>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab Presensi Guru -->
            <div class="tab-pane fade" id="pills-guru" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-custom table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 50px;">No</th>
                                <th style="width: 120px;">Tanggal</th>
                                <th style="width: 100px;">Shift</th>
                                <th style="width: 160px;">NIP</th>
                                <th>Nama Lengkap Guru</th>
                                <th class="text-center" style="width: 100px;">Status</th>
                                <th style="width: 120px;">Jam Masuk</th>
                                <th>Keterangan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($rekapGuru)): ?>
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">Tidak ada data presensi guru pada periode ini.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($rekapGuru as $idx => $rg): ?>
                                    <tr>
                                        <td class="text-center fw-semibold text-muted"><?= $idx + 1 ?></td>
                                        <td class="fw-semibold text-dark"><?= esc($rg['tanggal']) ?></td>
                                        <td>
                                            <?php if ($rg['shift'] === 'Pagi'): ?>
                                                <span class="badge bg-warning bg-opacity-15 text-warning-emphasis border border-warning border-opacity-25">Pagi</span>
                                            <?php else: ?>
                                                <span class="badge bg-info bg-opacity-10 text-primary border border-info border-opacity-25">Siang</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><span class="badge bg-light text-dark border font-monospace"><?= esc($rg['nip'] ?? '-') ?></span></td>
                                        <td class="fw-bold text-dark"><?= esc($rg['nama_guru']) ?></td>
                                        <td class="text-center">
                                            <?php 
                                            $st = $rg['status'];
                                            $stClass = [
                                                'H' => 'bg-success text-white',
                                                'S' => 'bg-info text-white',
                                                'I' => 'bg-warning text-dark',
                                                'A' => 'bg-danger text-white',
                                            ];
                                            ?>
                                            <span class="badge <?= $stClass[$st] ?? 'bg-secondary' ?> px-3 py-1">
                                                <?= $st ?>
                                            </span>
                                        </td>
                                        <td class="font-monospace small"><?= esc($rg['jam_masuk'] ? substr($rg['jam_masuk'], 0, 5) : '-') ?></td>
                                        <td class="small text-muted"><?= esc($rg['keterangan'] ?? '-') ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
