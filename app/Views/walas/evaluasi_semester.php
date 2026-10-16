<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<style>
@media print {
    .no-print, .sidebar-wrapper, .navbar, .btn, .card-header .btn {
        display: none !important;
    }
    body {
        background: #fff !important;
        font-size: 11pt;
    }
    .card {
        border: 1px solid #ccc !important;
        box-shadow: none !important;
        page-break-inside: avoid;
    }
    .print-only {
        display: block !important;
    }
}
.print-only {
    display: none;
}
</style>

<!-- Header Rapat Evaluasi -->
<div class="row mb-4 align-items-center no-print">
    <div class="col-12 col-md-7">
        <h3 class="fw-bold text-dark mb-1">
            <i class="bi bi-clipboard2-data-fill text-primary me-2"></i>Evaluasi Rombel Rapat Akhir Semester
        </h3>
        <p class="text-muted small mb-0">
            Laporan dinamika rombel (awal L/P, mutasi keluar, akhir L/P) dan rekam jejak siswa bermasalah untuk bahan rapat kenaikan / akhir semester.
        </p>
    </div>
    <div class="col-12 col-md-5 mt-3 mt-md-0 text-md-end d-flex justify-content-md-end gap-2">
        <button type="button" class="btn btn-outline-dark fw-semibold rounded-pill px-3 shadow-sm" onclick="window.print()">
            <i class="bi bi-printer-fill me-1"></i> Cetak Lembar Rapat
        </button>
        <a href="<?= site_url('walas/evaluasi-semester/export-excel?semester=' . $semester . '&tahun=' . $tahun . '&min_alpa=' . $minAlpa . '&min_terlambat=' . $minTerlambat . '&min_kasus=' . $minKasus) ?>" class="btn btn-success fw-semibold rounded-pill px-3 shadow-sm">
            <i class="bi bi-file-earmark-excel-fill me-1"></i> Unduh Excel
        </a>
    </div>
</div>

<!-- Header Khusus Print -->
<div class="print-only mb-4 text-center border-bottom pb-3">
    <h4 class="fw-bold mb-1">LEMBAR EVALUASI ROMBEL RAPAT AKHIR SEMESTER</h4>
    <h6 class="fw-bold text-uppercase mb-1">KELAS: <?= esc($kelas['nama_kelas']) ?> | <?= esc($labelPeriode) ?></h6>
    <div class="small text-muted">Wali Kelas: <?= esc($kelas['nama_walas'] ?? '-') ?> | Dicetak pada: <?= date('d/m/Y H:i') ?> WIB</div>
</div>

<!-- Filter Panel (Semester, Tahun, Threshold Pelanggaran) -->
<div class="card card-custom border-0 shadow-sm mb-4 no-print">
    <div class="card-body p-3">
        <form action="<?= site_url('walas/evaluasi-semester') ?>" method="GET" class="row g-2 align-items-end">
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-secondary mb-1">Semester</label>
                <select name="semester" class="form-select form-select-sm">
                    <option value="1" <?= $semester === '1' ? 'selected' : '' ?>>Semester 1 (Ganjil)</option>
                    <option value="2" <?= $semester === '2' ? 'selected' : '' ?>>Semester 2 (Genap)</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <label class="form-label small fw-semibold text-secondary mb-1">Tahun Kalender</label>
                <input type="number" name="tahun" class="form-control form-control-sm" value="<?= esc($tahun) ?>" min="2020" max="2035">
            </div>
            <div class="col-4 col-md-2">
                <label class="form-label small fw-semibold text-secondary mb-1">Min. Alpa</label>
                <div class="input-group input-group-sm">
                    <input type="number" name="min_alpa" class="form-control" value="<?= esc($minAlpa) ?>" min="1" max="50">
                    <span class="input-group-text">x</span>
                </div>
            </div>
            <div class="col-4 col-md-2">
                <label class="form-label small fw-semibold text-secondary mb-1">Min. Terlambat</label>
                <div class="input-group input-group-sm">
                    <input type="number" name="min_terlambat" class="form-control" value="<?= esc($minTerlambat) ?>" min="1" max="50">
                    <span class="input-group-text">x</span>
                </div>
            </div>
            <div class="col-4 col-md-2">
                <label class="form-label small fw-semibold text-secondary mb-1">Min. Kasus Pembinaan</label>
                <div class="input-group input-group-sm">
                    <input type="number" name="min_kasus" class="form-control" value="<?= esc($minKasus) ?>" min="1" max="20">
                    <span class="input-group-text">kasus</span>
                </div>
            </div>
            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary-custom flex-grow-1">
                    <i class="bi bi-funnel-fill me-1"></i> Filter
                </button>
                <a href="<?= site_url('walas/evaluasi-semester') ?>" class="btn btn-sm btn-outline-secondary">
                    Reset
                </a>
            </div>
        </form>
    </div>
</div>

<!-- ======================================================== -->
<!-- 1. KARTU DINAMIKA ROMBEL (Awal L/P vs Mutasi vs Akhir L/P) -->
<!-- ======================================================== -->
<div class="row g-3 mb-4">
    <!-- Awal Semester -->
    <div class="col-12 col-md-4">
        <div class="card card-custom border-0 p-3 h-100 shadow-sm border-top border-primary border-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="badge bg-primary bg-opacity-10 text-primary fw-bold px-2 py-1">KONDISI AWAL SEMESTER</span>
                <i class="bi bi-person-fill-add text-primary fs-4"></i>
            </div>
            <div class="fs-2 fw-bold text-dark mb-1">
                <?= $dinamika['awal']['total'] ?> <span class="fs-6 fw-normal text-muted">Murid</span>
            </div>
            <div class="d-flex gap-3 text-muted small pt-2 border-top">
                <div><i class="bi bi-gender-male text-primary me-1"></i>Laki-laki: <strong><?= $dinamika['awal']['l'] ?></strong></div>
                <div><i class="bi bi-gender-female text-danger me-1"></i>Perempuan: <strong><?= $dinamika['awal']['p'] ?></strong></div>
            </div>
        </div>
    </div>

    <!-- Mutasi Keluar / Mengundurkan Diri -->
    <div class="col-12 col-md-4">
        <div class="card card-custom border-0 p-3 h-100 shadow-sm border-top border-danger border-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="badge bg-danger bg-opacity-10 text-danger fw-bold px-2 py-1">MUTASI / KELUAR / KELUAR DO</span>
                <i class="bi bi-person-dash-fill text-danger fs-4"></i>
            </div>
            <div class="fs-2 fw-bold text-danger mb-1">
                <?= $dinamika['mutasi']['total'] ?> <span class="fs-6 fw-normal text-muted">Murid</span>
            </div>
            <div class="d-flex gap-3 text-muted small pt-2 border-top">
                <div><i class="bi bi-gender-male text-primary me-1"></i>Laki-laki: <strong><?= $dinamika['mutasi']['l'] ?></strong></div>
                <div><i class="bi bi-gender-female text-danger me-1"></i>Perempuan: <strong><?= $dinamika['mutasi']['p'] ?></strong></div>
            </div>
        </div>
    </div>

    <!-- Akhir Semester Aktif -->
    <div class="col-12 col-md-4">
        <div class="card card-custom border-0 p-3 h-100 shadow-sm border-top border-success border-4">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="badge bg-success bg-opacity-10 text-success fw-bold px-2 py-1">KONDISI AKHIR SEMESTER (AKTIF)</span>
                <i class="bi bi-person-check-fill text-success fs-4"></i>
            </div>
            <div class="fs-2 fw-bold text-success mb-1">
                <?= $dinamika['akhir']['total'] ?> <span class="fs-6 fw-normal text-muted">Murid</span>
            </div>
            <div class="d-flex gap-3 text-muted small pt-2 border-top">
                <div><i class="bi bi-gender-male text-primary me-1"></i>Laki-laki: <strong><?= $dinamika['akhir']['l'] ?></strong></div>
                <div><i class="bi bi-gender-female text-danger me-1"></i>Perempuan: <strong><?= $dinamika['akhir']['p'] ?></strong></div>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================== -->
<!-- 2. TABEL DAFTAR SISWA KELUAR / PINDAH / MENGUNDURKAN DIRI -->
<!-- ======================================================== -->
<div class="card card-custom border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold text-dark mb-0">
                <i class="bi bi-box-arrow-right text-danger me-2"></i>Daftar Siswa Keluar / Pindah / Mengundurkan Diri
            </h5>
            <small class="text-muted">Data mutasi keluar kelas <?= esc($kelas['nama_kelas']) ?> pada periode <?= esc($labelPeriode) ?></small>
        </div>
        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2 rounded-pill">
            <?= count($dinamika['mutasi']['daftar']) ?> Siswa Keluar
        </span>
    </div>
    <div class="table-responsive">
        <table class="table table-custom table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="text-center" style="width: 50px;">No</th>
                    <th style="width: 140px;">NISN</th>
                    <th>Nama Siswa</th>
                    <th class="text-center" style="width: 70px;">L/P</th>
                    <th style="width: 130px;">Tgl Nonaktif</th>
                    <th style="width: 200px;">Alasan Mutasi</th>
                    <th>Catatan & Berkas Surat Pendukung</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($dinamika['mutasi']['daftar'])): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">
                            <i class="bi bi-check-circle text-success fs-2 d-block mb-1"></i>
                            Tidak ada siswa mutasi keluar atau mengundurkan diri pada semester ini.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($dinamika['mutasi']['daftar'] as $mIdx => $ms): ?>
                        <tr>
                            <td class="text-center fw-semibold text-muted"><?= $mIdx + 1 ?></td>
                            <td><span class="badge bg-light text-dark border font-monospace"><?= esc($ms['nisn']) ?></span></td>
                            <td class="fw-bold text-dark"><?= esc($ms['nama_siswa']) ?></td>
                            <td class="text-center">
                                <span class="badge <?= ($ms['jenis_kelamin'] ?? 'L') === 'L' ? 'bg-info bg-opacity-15 text-primary' : 'bg-danger bg-opacity-15 text-danger' ?> rounded-pill px-2 py-1">
                                    <?= esc($ms['jenis_kelamin'] ?? 'L') ?>
                                </span>
                            </td>
                            <td><?= $ms['tanggal_nonaktif'] ? date('d/m/Y', strtotime($ms['tanggal_nonaktif'])) : '-' ?></td>
                            <td>
                                <span class="badge bg-danger bg-opacity-15 text-danger border border-danger border-opacity-25 rounded-pill px-3 py-1">
                                    <?= esc(ucwords(str_replace('_', ' ', $ms['alasan_nonaktif'] ?? 'pindah_sekolah'))) ?>
                                </span>
                            </td>
                            <td>
                                <div class="small text-muted mb-1"><?= esc($ms['catatan_nonaktif'] ?: 'Tidak ada catatan.') ?></div>
                                <?php if (!empty($ms['file_surat_nonaktif'])): ?>
                                    <a href="<?= base_url('uploads/mutasi/' . $ms['file_surat_nonaktif']) ?>" target="_blank" class="btn btn-xs btn-outline-primary py-0 px-2 rounded-pill shadow-xs" style="font-size: 0.75rem;">
                                        <i class="bi bi-file-earmark-arrow-down me-1"></i> Unduh / Buka Berkas Surat
                                    </a>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted border" style="font-size: 0.72rem;">Belum ada lampiran surat</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ======================================================== -->
<!-- 3. TABEL SISWA BERMASALAH (POKOK BAHASAN UTAMA RAPAT)      -->
<!-- ======================================================== -->
<div class="card card-custom border-0 shadow-sm mb-4 border-start border-danger border-4">
    <div class="card-header bg-white border-0 py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <div>
            <h5 class="fw-bold text-danger mb-0">
                <i class="bi bi-exclamation-diamond-fill me-2"></i>Daftar Siswa Bermasalah & Pembahasan Rapat Akhir Semester
            </h5>
            <small class="text-muted">
                Siswa dengan <strong>Alpa &ge; <?= $minAlpa ?></strong>, <strong>Terlambat &ge; <?= $minTerlambat ?></strong>, atau <strong>Mendapat Kasus Pembinaan/SP &ge; <?= $minKasus ?> (Perkelahian/Disiplin/Perilaku)</strong>
            </small>
        </div>
        <span class="badge bg-danger text-white fs-6 px-3 py-2 rounded-pill shadow-xs">
            <?= count($siswaBermasalah) ?> Siswa Masuk Agenda Rapat
        </span>
    </div>
    <div class="table-responsive">
        <table class="table table-custom table-hover align-middle mb-0">
            <thead class="table-danger bg-opacity-25">
                <tr>
                    <th class="text-center" style="width: 50px;">No</th>
                    <th style="width: 130px;">NISN</th>
                    <th style="min-width: 170px;">Nama Siswa</th>
                    <th class="text-center" style="width: 55px;">L/P</th>
                    <th class="text-center" style="width: 50px;">H</th>
                    <th class="text-center" style="width: 50px;">S</th>
                    <th class="text-center" style="width: 50px;">I</th>
                    <th class="text-center" style="width: 60px;">A</th>
                    <th class="text-center" style="width: 70px;">Terlambat</th>
                    <th style="min-width: 320px;">Fokus Pembahasan: Pelanggaran & Catatan Kasus Pembinaan</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($siswaBermasalah)): ?>
                    <tr>
                        <td colspan="10" class="text-center py-5 text-muted">
                            <i class="bi bi-patch-check-fill text-success fs-1 d-block mb-2"></i>
                            <strong>Nihil Pelanggaran Kritis!</strong> Seluruh siswa berada di bawah ambang batas pelanggaran.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($siswaBermasalah as $bIdx => $bs): ?>
                        <tr>
                            <td class="text-center fw-bold"><?= $bIdx + 1 ?></td>
                            <td><span class="badge bg-light text-dark border font-monospace"><?= esc($bs['nisn']) ?></span></td>
                            <td>
                                <div class="fw-bold text-dark fs-6"><?= esc($bs['nama_siswa']) ?></div>
                                <small class="text-muted">% Kehadiran: <?= $bs['persen_kehadiran'] ?? 0 ?>%</small>
                            </td>
                            <td class="text-center">
                                <span class="badge <?= ($bs['jenis_kelamin'] ?? 'L') === 'L' ? 'bg-info bg-opacity-15 text-primary' : 'bg-danger bg-opacity-15 text-danger' ?> rounded-pill px-2 py-1">
                                    <?= esc($bs['jenis_kelamin'] ?? 'L') ?>
                                </span>
                            </td>
                            <td class="text-center fw-semibold text-success"><?= $bs['total_h'] ?? 0 ?></td>
                            <td class="text-center fw-semibold text-info"><?= $bs['total_s'] ?? 0 ?></td>
                            <td class="text-center fw-semibold text-primary"><?= $bs['total_i'] ?? 0 ?></td>
                            <td class="text-center">
                                <span class="badge <?= (int)($bs['total_a'] ?? 0) >= $minAlpa ? 'bg-danger text-white fs-6' : 'bg-light text-dark border' ?> px-2 py-1 rounded-pill">
                                    <?= $bs['total_a'] ?? 0 ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge <?= (int)($bs['total_t'] ?? 0) >= $minTerlambat ? 'bg-danger text-white fs-6' : ((int)($bs['total_t'] ?? 0) > 1 ? 'bg-warning text-dark' : 'bg-light text-dark border') ?> px-2 py-1 rounded-pill">
                                    <?= (int)($bs['total_t'] ?? 0) ?>x
                                </span>
                            </td>
                            <td>
                                <!-- 1. Indikator Pelanggaran Presensi (Alpa & Terlambat) -->
                                <div class="d-flex flex-wrap gap-1 mb-2">
                                    <?php if ((int)($bs['total_a'] ?? 0) >= $minAlpa): ?>
                                        <span class="badge bg-danger bg-opacity-15 text-danger border border-danger border-opacity-25 rounded-pill px-2 py-1">
                                            <i class="bi bi-x-octagon-fill me-1"></i> Alpa Kritis (<?= $bs['total_a'] ?>x)
                                        </span>
                                    <?php endif; ?>
                                    <?php if ((int)($bs['total_t'] ?? 0) >= $minTerlambat): ?>
                                        <span class="badge bg-danger bg-opacity-15 text-danger border border-danger border-opacity-25 rounded-pill px-2 py-1">
                                            <i class="bi bi-clock-history me-1"></i> Terlambat Kritis (<?= (int)$bs['total_t'] ?>x)
                                        </span>
                                    <?php elseif ((int)($bs['total_t'] ?? 0) > 1): ?>
                                        <span class="badge bg-warning bg-opacity-20 text-dark border border-warning border-opacity-25 rounded-pill px-2 py-1">
                                            <i class="bi bi-clock me-1"></i> Terlambat > 1x (Perhatian)
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <!-- 2. Riwayat Kasus Pembinaan / Pelanggaran Perilaku (Berantem, SP, dll.) -->
                                <?php if (!empty($bs['kasus_pembinaan'])): ?>
                                    <div class="border-top pt-2">
                                        <div class="d-flex align-items-center gap-1 mb-1">
                                            <span class="badge bg-dark text-white rounded-pill px-2 py-1" style="font-size: 0.72rem;">
                                                <i class="bi bi-shield-fill-exclamation text-warning me-1"></i> <?= count($bs['kasus_pembinaan']) ?>x Kasus Pembinaan / SP
                                            </span>
                                        </div>
                                        <div class="d-flex flex-column gap-1">
                                            <?php foreach ($bs['kasus_pembinaan'] as $kp): ?>
                                                <div class="p-2 bg-light rounded-2 border-start border-danger border-3 shadow-2xs" style="font-size: 0.78rem;">
                                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                                        <span class="fw-bold text-dark">
                                                            <i class="bi bi-caret-right-fill text-danger me-1"></i><?= esc($kp['jenis_tindakan']) ?>
                                                        </span>
                                                        <span class="text-muted" style="font-size: 0.72rem;">
                                                            <i class="bi bi-calendar-event me-1"></i><?= !empty($kp['tanggal_tindakan']) ? date('d/m/Y', strtotime($kp['tanggal_tindakan'])) : '-' ?>
                                                        </span>
                                                    </div>
                                                    <div class="text-dark small">
                                                        <?= esc($kp['catatan_pembinaan'] ?: 'Tidak ada rincian catatan permasalahan') ?>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ======================================================== -->
<!-- 4. TABEL REKAP PRESENSI LENGKAP SELURUH SISWA ROMBEL       -->
<!-- ======================================================== -->
<div class="card card-custom border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
        <div>
            <h5 class="fw-bold text-dark mb-0">
                <i class="bi bi-table text-primary me-2"></i>Rekapitulasi Presensi Seluruh Anggota Kelas
            </h5>
            <small class="text-muted">Total kehadiran, sakit, izin, alpa, dan keterlambatan semester</small>
        </div>
        <span class="badge bg-light text-muted border"><?= count($rekapSiswa) ?> Siswa Aktif</span>
    </div>
    <div class="table-responsive">
        <table class="table table-custom table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="text-center" style="width: 50px;">No</th>
                    <th style="width: 140px;">NISN</th>
                    <th>Nama Lengkap Siswa</th>
                    <th class="text-center" style="width: 60px;">L/P</th>
                    <th class="text-center" style="width: 70px;">H</th>
                    <th class="text-center" style="width: 70px;">S</th>
                    <th class="text-center" style="width: 70px;">I</th>
                    <th class="text-center" style="width: 70px;">A</th>
                    <th class="text-center" style="width: 110px;">Terlambat (T)</th>
                    <th class="text-center" style="width: 110px;">% Kehadiran</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rekapSiswa)): ?>
                    <tr>
                        <td colspan="10" class="text-center py-5 text-muted">Belum ada data presensi pada rentang tanggal semester ini.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rekapSiswa as $idx => $s): ?>
                        <tr>
                            <td class="text-center fw-semibold text-muted"><?= $idx + 1 ?></td>
                            <td><span class="badge bg-light text-dark border font-monospace"><?= esc($s['nisn']) ?></span></td>
                            <td class="fw-bold text-dark"><?= esc($s['nama_siswa']) ?></td>
                            <td class="text-center">
                                <span class="badge <?= ($s['jenis_kelamin'] ?? 'L') === 'L' ? 'bg-info bg-opacity-15 text-primary' : 'bg-danger bg-opacity-15 text-danger' ?> rounded-pill px-2 py-1">
                                    <?= esc($s['jenis_kelamin'] ?? 'L') ?>
                                </span>
                            </td>
                            <td class="text-center fw-semibold text-success"><?= $s['total_h'] ?></td>
                            <td class="text-center fw-semibold text-info"><?= $s['total_s'] ?></td>
                            <td class="text-center fw-semibold text-primary"><?= $s['total_i'] ?></td>
                            <td class="text-center fw-semibold <?= (int)$s['total_a'] > 0 ? 'text-danger fw-bold' : 'text-muted' ?>">
                                <?= $s['total_a'] ?>
                            </td>
                            <td class="text-center">
                                <?php $tCount = (int)($s['total_t'] ?? 0); ?>
                                <?php if ($tCount > 3): ?>
                                    <span class="badge bg-danger rounded-pill px-2 py-1"><?= $tCount ?>x</span>
                                <?php elseif ($tCount > 0): ?>
                                    <span class="badge bg-warning text-dark rounded-pill px-2 py-1"><?= $tCount ?>x</span>
                                <?php else: ?>
                                    <span class="text-muted">0</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center fw-bold">
                                <?= $s['persen_kehadiran'] ?? 0 ?>%
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ======================================================== -->
<!-- 5. RIWAYAT KASUS PEMBINAAN & TINDAKAN SP SEMESTER INI       -->
<!-- ======================================================== -->
<div class="card card-custom border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="fw-bold text-dark mb-0">
            <i class="bi bi-shield-check text-warning me-2"></i>Catatan Pembinaan / Surat Peringatan (SP) Semester Ini
        </h5>
        <small class="text-muted">Rekam jejak konseling, pembinaan walas, dan surat pemanggilan orang tua</small>
    </div>
    <div class="table-responsive">
        <table class="table table-custom table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="text-center" style="width: 50px;">No</th>
                    <th style="width: 120px;">Tanggal</th>
                    <th>Nama Siswa</th>
                    <th>Guru Pembina</th>
                    <th>Permasalahan Siswa</th>
                    <th>Bentuk Tindakan</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($riwayatPembinaan)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">
                            <i class="bi bi-shield-check text-success fs-2 d-block mb-1"></i>
                            Tidak ada catatan kasus pembinaan / SP pada semester ini.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($riwayatPembinaan as $pIdx => $pb): ?>
                        <tr>
                            <td class="text-center fw-semibold text-muted"><?= $pIdx + 1 ?></td>
                            <td><?= !empty($pb['tanggal_tindakan']) ? date('d/m/Y', strtotime($pb['tanggal_tindakan'])) : '-' ?></td>
                            <td class="fw-bold text-dark"><?= esc($pb['nama_siswa']) ?></td>
                            <td><?= esc($pb['nama_walas'] ?? 'Wali Kelas') ?></td>
                            <td class="small text-muted"><?= esc($pb['catatan_pembinaan'] ?? '-') ?></td>
                            <td class="small text-dark fw-semibold"><?= esc($pb['jenis_tindakan'] ?? '-') ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Tanda Tangan Khusus Cetak -->
<div class="print-only mt-5 pt-3">
    <div class="row">
        <div class="col-6 text-center">
            Mengetahui,<br>Kepala Sekolah
            <br><br><br><br>
            <strong>( .................................................. )</strong>
        </div>
        <div class="col-6 text-center">
            Wali Kelas <?= esc($kelas['nama_kelas']) ?>
            <br><br><br><br>
            <strong><?= esc($kelas['nama_walas'] ?? '( .................................................. )') ?></strong>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
