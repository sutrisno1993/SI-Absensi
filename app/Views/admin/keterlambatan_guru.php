<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="row mb-4 align-items-center">
    <div class="col-12 col-md-6">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= site_url('admin') ?>" class="text-decoration-none text-muted">Dashboard</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Keterlambatan Guru</li>
            </ol>
        </nav>
        <h3 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
            <i class="bi bi-alarm-fill text-danger"></i>
            <span>Catatan Keterlambatan Guru</span>
        </h3>
        <p class="text-muted small mb-0">Pantau dan kelola batas jam kedatangan serta rekam jejak keterlambatan dewan guru.</p>
    </div>
    <div class="col-12 col-md-6 mt-3 mt-md-0 text-md-end d-flex flex-wrap justify-content-md-end gap-2">
        <button type="button" class="btn btn-outline-warning rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalPengaturanJam">
            <i class="bi bi-clock-history me-1"></i> Pengaturan Jam Masuk
        </button>
        <a href="<?= site_url('admin/keterlambatan-guru/export-excel?start_date=' . esc($startDate) . '&end_date=' . esc($endDate) . '&guru_id=' . esc($guruId ?? '') . '&shift=' . esc($shift ?? '')) ?>" class="btn btn-success rounded-pill px-3">
            <i class="bi bi-file-earmark-excel-fill me-1"></i> Ekspor Excel
        </a>
    </div>
</div>

<!-- Quick Settings Info Card -->
<div class="card card-custom border-0 shadow-sm mb-4">
    <div class="card-body p-3 p-md-4">
        <div class="row align-items-center g-3">
            <div class="col-12 col-md-8">
                <div class="d-flex gap-3 align-items-start">
                    <div class="bg-danger bg-opacity-15 text-danger p-3 rounded-4 fs-3 flex-shrink-0">
                        <i class="bi bi-clock-fill"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-1 text-white">Standar Batas Jam Masuk Kedatangan Guru Aktif</h6>
                        <p class="text-muted small mb-2">
                            Guru yang diabsen hadir melewati jam batas di bawah ini otomatis dihitung <strong>Terlambat</strong> dan tercatat menit keterlambatannya.
                        </p>
                        <div class="d-flex flex-wrap gap-2 small">
                            <span class="badge bg-warning bg-opacity-15 text-warning border border-warning border-opacity-25 px-3 py-2">
                                <i class="bi bi-sun-fill me-1"></i> Shift Pagi: <strong><?= esc($jamMasukPagi) ?> WIB</strong>
                            </span>
                            <span class="badge bg-info bg-opacity-15 text-info border border-info border-opacity-25 px-3 py-2">
                                <i class="bi bi-cloud-sun-fill me-1"></i> Shift Siang: <strong><?= esc($jamMasukSiang) ?> WIB</strong>
                            </span>
                            <span class="badge bg-secondary bg-opacity-15 text-light border border-secondary border-opacity-25 px-3 py-2">
                                <i class="bi bi-hourglass-split me-1"></i> Toleransi: <strong><?= (int)$toleransi ?> Menit</strong>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-4 text-md-end">
                <button type="button" class="btn btn-outline-light btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalPengaturanJam">
                    <i class="bi bi-pencil-square me-1"></i> Ubah Batas Jam
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Stat Cards -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-4">
        <div class="card card-custom border-0 shadow-sm p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">Total Insiden Terlambat</span>
                    <h3 class="fw-bold text-danger mb-0 mt-1"><?= $totalTerlambat ?> <span class="fs-6 fw-normal text-muted">Kali</span></h3>
                </div>
                <div class="bg-danger bg-opacity-15 text-danger p-3 rounded-circle fs-4">
                    <i class="bi bi-exclamation-octagon-fill"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-4">
        <div class="card card-custom border-0 shadow-sm p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">Akumulasi Keterlambatan</span>
                    <h3 class="fw-bold text-warning mb-0 mt-1"><?= number_format($totalMenit, 0, ',', '.') ?> <span class="fs-6 fw-normal text-muted">Menit</span></h3>
                </div>
                <div class="bg-warning bg-opacity-15 text-warning p-3 rounded-circle fs-4">
                    <i class="bi bi-stopwatch-fill"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-12 col-sm-4">
        <div class="card card-custom border-0 shadow-sm p-3">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">Rata-rata Terlambat</span>
                    <h3 class="fw-bold text-info mb-0 mt-1"><?= $rataMenit ?> <span class="fs-6 fw-normal text-muted">Menit/Insiden</span></h3>
                </div>
                <div class="bg-info bg-opacity-15 text-info p-3 rounded-circle fs-4">
                    <i class="bi bi-speedometer2"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Filter Bar -->
<div class="card card-custom border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form action="<?= site_url('admin/keterlambatan-guru') ?>" method="GET">
            <div class="row g-2 align-items-end">
                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label small fw-semibold text-secondary mb-1">Dari Tanggal</label>
                    <input type="date" name="start_date" class="form-control form-control-sm" value="<?= esc($startDate) ?>">
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label small fw-semibold text-secondary mb-1">Sampai Tanggal</label>
                    <input type="date" name="end_date" class="form-control form-control-sm" value="<?= esc($endDate) ?>">
                </div>
                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label small fw-semibold text-secondary mb-1">Pilih Guru</label>
                    <select name="guru_id" class="form-select form-select-sm">
                        <option value="">-- Semua Guru --</option>
                        <?php foreach ($guruList as $g): ?>
                            <option value="<?= $g['id'] ?>" <?= ($guruId == $g['id']) ? 'selected' : '' ?>>
                                <?= esc($g['nama_guru']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-12 col-sm-6 col-md-2">
                    <label class="form-label small fw-semibold text-secondary mb-1">Shift</label>
                    <select name="shift" class="form-select form-select-sm">
                        <option value="">-- Semua Shift --</option>
                        <option value="Pagi" <?= ($shift === 'Pagi') ? 'selected' : '' ?>>Pagi</option>
                        <option value="Siang" <?= ($shift === 'Siang') ? 'selected' : '' ?>>Siang</option>
                    </select>
                </div>
                <div class="col-12 col-md-1 d-flex gap-1">
                    <button type="submit" class="btn btn-primary-custom btn-sm w-100" title="Terapkan Filter">
                        <i class="bi bi-funnel-fill"></i>
                    </button>
                    <a href="<?= site_url('admin/keterlambatan-guru') ?>" class="btn btn-outline-secondary btn-sm" title="Reset Filter">
                        <i class="bi bi-arrow-counterclockwise"></i>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Table Data Keterlambatan -->
<div class="card card-custom border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 pt-3 px-4 pb-2 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
            <i class="bi bi-table text-primary"></i> Log Presensi Terlambat
        </h5>
        <span class="badge bg-secondary bg-opacity-20 text-light border px-3 py-2">
            Ditemukan <?= count($keterlambatanList) ?> Catatan
        </span>
    </div>

    <div class="table-responsive">
        <table class="table table-custom table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="text-center" style="width: 50px;">No</th>
                    <th>Tanggal & Hari</th>
                    <th>Nama Guru / NIP</th>
                    <th class="text-center">Shift</th>
                    <th class="text-center">Jam Kedatangan</th>
                    <th class="text-center">Durasi Terlambat</th>
                    <th>Alasan / Keterangan</th>
                    <th class="text-center">Pencatat</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($keterlambatanList)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
                            <i class="bi bi-check-circle-fill text-success fs-1 d-block mb-2"></i>
                            Tidak ada data keterlambatan guru pada rentang waktu ini.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($keterlambatanList as $idx => $row): ?>
                        <tr>
                            <td class="text-center fw-semibold text-muted"><?= $idx + 1 ?></td>
                            <td>
                                <div class="fw-bold text-white"><?= date('d/m/Y', strtotime($row['tanggal'])) ?></div>
                                <small class="text-muted"><?= esc($row['hari'] ?? date('l', strtotime($row['tanggal']))) ?></small>
                            </td>
                            <td>
                                <div class="fw-bold text-white"><?= esc($row['nama_guru']) ?></div>
                                <small class="text-muted font-monospace">NIP: <?= esc($row['nip'] ?? '-') ?></small>
                            </td>
                            <td class="text-center">
                                <?php if ($row['shift'] === 'Pagi'): ?>
                                    <span class="badge bg-warning bg-opacity-15 text-warning border border-warning border-opacity-25 px-2 py-1">
                                        <i class="bi bi-sun-fill me-1"></i> Pagi
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-info bg-opacity-15 text-info border border-info border-opacity-25 px-2 py-1">
                                        <i class="bi bi-cloud-sun-fill me-1"></i> Siang
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-dark text-light border px-2 py-1 font-monospace fs-6">
                                    <i class="bi bi-clock me-1 text-danger"></i>
                                    <?= !empty($row['jam_masuk']) ? esc(substr($row['jam_masuk'], 0, 5)) : '-' ?> WIB
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-danger bg-opacity-20 text-danger border border-danger border-opacity-25 px-3 py-2 fs-6 fw-bold">
                                    +<?= (int)$row['menit_terlambat'] ?> Menit
                                </span>
                            </td>
                            <td>
                                <?php if (!empty($row['keterangan'])): ?>
                                    <span class="text-light"><?= esc($row['keterangan']) ?></span>
                                <?php else: ?>
                                    <span class="text-muted fst-italic">Tanpa keterangan</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-muted border px-2 py-1 small">
                                    <?= esc($row['dicatat_oleh_user'] ?? 'Guru Piket') ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Pengaturan Jam Masuk Guru -->
<div class="modal fade" id="modalPengaturanJam" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content card-custom border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2">
                    <i class="bi bi-sliders text-warning"></i>
                    <span>Pengaturan Jam Masuk Guru</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= site_url('admin/keterlambatan-guru/pengaturan') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body py-4">
                    <div class="alert alert-info border-0 rounded-3 small mb-3">
                        <i class="bi bi-info-circle-fill me-1"></i> Jam yang disetel di sini menjadi acuan batas kehadiran guru piket. Kedatangan setelah jam ini otomatis dihitung sebagai <strong>Terlambat</strong>.
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">
                            <i class="bi bi-sun-fill text-warning me-1"></i> Batas Jam Masuk Shift Pagi (HH:MM)
                        </label>
                        <input type="time" name="jam_masuk_pagi" class="form-control" value="<?= esc($jamMasukPagi) ?>" required>
                        <div class="form-text text-muted small">Contoh: <strong>06:30</strong> (Guru datang pukul 07:00 akan tercatat terlambat 30 menit).</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">
                            <i class="bi bi-cloud-sun-fill text-info me-1"></i> Batas Jam Masuk Shift Siang (HH:MM)
                        </label>
                        <input type="time" name="jam_masuk_siang" class="form-control" value="<?= esc($jamMasukSiang) ?>" required>
                        <div class="form-text text-muted small">Contoh: <strong>12:30</strong> (Guru datang setelah jam 12:30 dihitung terlambat).</div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-secondary">
                            <i class="bi bi-hourglass-split text-light me-1"></i> Toleransi Keterlambatan (Menit)
                        </label>
                        <input type="number" name="toleransi_menit" class="form-control" min="0" max="60" value="<?= (int)$toleransi ?>">
                        <div class="form-text text-muted small">Isi <strong>0</strong> jika tidak ada toleransi waktu kedatangan.</div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary-custom rounded-pill px-4">
                        <i class="bi bi-check-circle-fill me-1"></i> Simpan Pengaturan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
