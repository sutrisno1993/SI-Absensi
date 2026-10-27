<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<!-- Flash Message Notification -->
<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm mb-4 border-0" role="alert">
        <i class="bi bi-check-circle-fill me-2 fs-5 align-middle"></i>
        <?= session()->getFlashdata('success') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm mb-4 border-0" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2 fs-5 align-middle"></i>
        <?= session()->getFlashdata('error') ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- Header Dokumen Cetak (Hanya Muncul Saat Print) -->
<div class="d-none d-print-block mb-4 text-center border-bottom pb-3">
    <h4 class="fw-bold mb-1">SISTEM INFORMASI PRESENSI & PEMBINAAN SISWA (SI-ABSEN)</h4>
    <h5 class="fw-bold text-danger mb-1">LAPORAN REKAPITULASI KETERLAMBATAN SISWA</h5>
    <p class="mb-0 text-muted small">Kelas: <strong><?= esc($kelas['nama_kelas']) ?></strong> | Wali Kelas: <strong><?= esc($kelas['nama_walas'] ?? session()->get('nama')) ?></strong></p>
    <small class="text-muted">Periode: <?= date('d/m/Y', strtotime($startDate)) ?> s/d <?= date('d/m/Y', strtotime($endDate)) ?> | Dicetak: <?= date('d F Y, H:i') ?> WIB</small>
</div>

<!-- Header Page -->
<div class="row mb-4 align-items-center d-print-none">
    <div class="col-12 col-md-6">
        <h3 class="fw-bold text-dark mb-1">
            <i class="bi bi-clock-history text-danger me-2"></i>Laporan Keterlambatan Siswa
        </h3>
        <p class="text-muted small mb-0">Pantau catatan keterlambatan murid kelas <?= esc($kelas['nama_kelas']) ?> yang dicatat oleh Tim Guru Piket.</p>
    </div>
    <div class="col-12 col-md-6 mt-3 mt-md-0 text-md-end d-flex flex-wrap gap-2 justify-content-md-end">
        <a href="<?= site_url('walas/keterlambatan/export-excel?start_date=' . esc($startDate) . '&end_date=' . esc($endDate)) ?>" class="btn btn-success fw-semibold rounded-pill px-3 shadow-sm">
            <i class="bi bi-file-earmark-excel me-1"></i> Export Excel
        </a>
        <button type="button" class="btn btn-outline-secondary fw-semibold rounded-pill px-3 shadow-sm" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Cetak Dokumen
        </button>
        <a href="<?= site_url('walas/rekap') ?>" class="btn btn-outline-primary fw-semibold rounded-pill px-3 shadow-sm">
            <i class="bi bi-table me-1"></i> Rekap Presensi
        </a>
    </div>
</div>

<!-- Filter Rentang Tanggal & Preset Cepat -->
<div class="card card-custom border-0 shadow-sm mb-4 d-print-none">
    <div class="card-body p-3">
        <form action="<?= site_url('walas/keterlambatan') ?>" method="GET" class="row g-2 align-items-center">
            <div class="col-12 col-lg-auto">
                <span class="fw-semibold text-secondary small"><i class="bi bi-funnel-fill me-1"></i> Periode:</span>
            </div>
            <div class="col-6 col-sm-auto">
                <input type="date" name="start_date" id="input_start_date" class="form-control form-control-sm" value="<?= esc($startDate) ?>" required>
            </div>
            <div class="col-auto text-muted small">s/d</div>
            <div class="col-6 col-sm-auto">
                <input type="date" name="end_date" id="input_end_date" class="form-control form-control-sm" value="<?= esc($endDate) ?>" required>
            </div>
            <div class="col-12 col-lg-auto d-flex flex-wrap gap-2">
                <button type="submit" class="btn btn-sm btn-primary-custom px-3">
                    <i class="bi bi-search me-1"></i> Filter
                </button>
                <a href="<?= site_url('walas/keterlambatan') ?>" class="btn btn-sm btn-outline-secondary px-3" title="Filter Bulan Ini">
                    Bulan Ini
                </a>
                <button type="button" class="btn btn-sm btn-outline-dark px-3" onclick="setPreset('today')">
                    Hari Ini
                </button>
                <button type="button" class="btn btn-sm btn-outline-dark px-3" onclick="setPreset('7days')">
                    7 Hari Terakhir
                </button>
                <button type="button" class="btn btn-sm btn-outline-dark px-3" onclick="setPreset('semester')">
                    Semester Ini
                </button>
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
                    <div class="text-muted small fw-semibold">Perlu Perhatian (2-3x)</div>
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
<div class="alert alert-light border border-warning border-opacity-50 rounded-4 p-3 mb-4 d-flex align-items-start gap-3 shadow-xs d-print-none">
    <div class="rounded-circle bg-warning bg-opacity-15 text-warning p-2 mt-1">
        <i class="bi bi-shield-exclamation fs-4"></i>
    </div>
    <div class="small">
        <strong class="text-dark d-block mb-1">Standar Operasional Disiplin & Keterlambatan:</strong>
        <ul class="mb-0 ps-3 text-muted">
            <li><strong>Terlambat > 1 kali:</strong> Sistem otomatis memberikan status <span class="badge bg-warning text-dark">Perlu Perhatian</span> dan dilaporkan ke Wali Kelas untuk dipantau secara berkala.</li>
            <li><strong>Terlambat > 3 kali:</strong> Dinyatakan <span class="badge bg-danger">Wajib Pembinaan</span>. Wali Kelas dan Guru BK wajib melakukan konseling, pemanggilan orang tua, dan mencatat berkas pembinaan.</li>
        </ul>
    </div>
</div>

<!-- Nav Tabs: Rekap Per Siswa vs Riwayat Log Harian -->
<ul class="nav nav-pills mb-3 gap-2 d-print-none" id="keterlambatanTab" role="tablist">
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
                            <th class="text-center" style="width: 120px;">Total Terlambat</th>
                            <th class="text-center" style="width: 120px;">Total Durasi</th>
                            <th style="width: 240px;">Riwayat Tanggal</th>
                            <th class="text-center" style="width: 160px;">Status Disiplin</th>
                            <th class="text-end d-print-none" style="width: 140px;">Aksi Tindakan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rekapTerlambat)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
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
                                    <td class="text-end d-print-none">
                                        <?php if ($totT > 3): ?>
                                            <button type="button" class="btn btn-sm btn-danger fw-bold rounded-pill px-3 shadow-xs" onclick="binaSiswaCepat(<?= $r['siswa_id'] ?>, '<?= addslashes(esc($r['nama_siswa'])) ?>', 'Terlambat <?= $totT ?> kali (Akumulasi <?= $totM ?> menit). Riwayat: <?= addslashes(esc($r['riwayat_tgl'] ?? '-')) ?>')">
                                                <i class="bi bi-pencil-square me-1"></i> Bina Siswa
                                            </button>
                                        <?php elseif ($totT > 1): ?>
                                            <button type="button" class="btn btn-sm btn-outline-warning text-dark fw-bold rounded-pill px-3 shadow-xs" onclick="binaSiswaCepat(<?= $r['siswa_id'] ?>, '<?= addslashes(esc($r['nama_siswa'])) ?>', 'Peringatan keterlambatan ke-<?= $totT ?> kali (Total <?= $totM ?> menit)')">
                                                <i class="bi bi-chat-dots me-1"></i> Konseling
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
                            <th class="text-end d-print-none" style="width: 120px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logTerlambat)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">Belum ada riwayat keterlambatan pada periode ini.</td>
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
                                    <td class="text-end d-print-none">
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1" onclick="binaSiswaCepat(<?= $log['siswa_id'] ?>, '<?= addslashes(esc($log['nama_siswa'])) ?>', 'Keterlambatan pada tanggal <?= date('d/m/Y', strtotime($log['tanggal'])) ?> (<?= (int)$log['menit_terlambat'] ?> menit). Alasan: <?= addslashes(esc($log['alasan'] ?: 'Tidak ada keterangan')) ?>')">
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

<!-- Modal Aksi Cepat Pembinaan Siswa (Terintegrasi Walas) -->
<div class="modal fade d-print-none" id="modalBinaSiswa" tabindex="-1" aria-labelledby="modalBinaSiswaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-danger text-white py-3">
                <h5 class="modal-title fw-bold d-flex align-items-center gap-2" id="modalBinaSiswaLabel">
                    <i class="bi bi-shield-shaded"></i>
                    Input Tindakan Pembinaan Siswa
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form action="<?= site_url('walas/pembinaan/simpan') ?>" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                
                <div class="modal-body py-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-7">
                            <label for="modal_siswa_id" class="form-label fw-semibold small text-secondary">Pilih Siswa Binaan <span class="text-danger">*</span></label>
                            <select class="form-select" id="modal_siswa_id" name="siswa_id" required>
                                <option value="">-- Pilih Siswa --</option>
                                <?php foreach ($daftarSiswa ?? [] as $s): ?>
                                    <option value="<?= $s['id'] ?>"><?= esc($s['nama_siswa']) ?> (<?= esc($s['nisn']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-5">
                            <label for="tanggal_tindakan" class="form-label fw-semibold small text-secondary">Tanggal Tindakan <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tanggal_tindakan" name="tanggal_tindakan" value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="col-12">
                            <label for="jenis_tindakan" class="form-label fw-semibold small text-secondary">Jenis Tindakan Pembinaan <span class="text-danger">*</span></label>
                            <select class="form-select" id="jenis_tindakan" name="jenis_tindakan" required>
                                <option value="">-- Pilih Jenis Tindakan --</option>
                                <option value="Konseling Khusus Wali Kelas" selected>Konseling Khusus Wali Kelas</option>
                                <option value="Pemanggilan Orang Tua / Wali">Pemanggilan Orang Tua / Wali</option>
                                <option value="Surat Peringatan 1 (SP1)">Surat Peringatan 1 (SP1)</option>
                                <option value="Surat Peringatan 2 (SP2)">Surat Peringatan 2 (SP2)</option>
                                <option value="Kunjungan Rumah (Homevisit)">Kunjungan Rumah (Homevisit)</option>
                                <option value="Pelimpahan ke Guru BK / Konselor">Pelimpahan ke Guru BK / Konselor</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="catatan_pembinaan" class="form-label fw-semibold small text-secondary">Catatan Pembinaan & Hasil Diskusi <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="catatan_pembinaan" name="catatan_pembinaan" rows="4" placeholder="Uraikan alasan pembinaan (misal: Sering terlambat masuk sekolah), hasil konseling, serta komitmen siswa / wali..." required></textarea>
                        </div>

                        <div class="col-12">
                            <label for="file_bukti" class="form-label fw-semibold small text-secondary">Upload Bukti Berkas (Foto / Surat / PDF)</label>
                            <input type="file" class="form-control" id="file_bukti" name="file_bukti" accept=".jpg,.jpeg,.png,.pdf">
                            <div class="form-text small">Format file: JPG, PNG, atau PDF. Ukuran berkas maksimal 2MB.</div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger fw-bold rounded-pill px-4 shadow-sm">
                        <i class="bi bi-save-fill me-1"></i> Simpan Tindakan Pembinaan
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
        font-size: 11pt;
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
        padding: 5px !important;
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
        var selectSiswa = document.getElementById('modal_siswa_id');
        if (selectSiswa) {
            selectSiswa.value = siswaId;
        }
        var catatanEl = document.getElementById('catatan_pembinaan');
        if (catatanEl && alasanDefault) {
            catatanEl.value = 'Pembinaan Disiplin Keterlambatan: ' + alasanDefault + '.\nSiswa bersedia mematuhi tata tertib jam kedatangan sekolah.';
        }
        var modal = new bootstrap.Modal(document.getElementById('modalBinaSiswa'));
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
                // Semester Ganjil (Juli - Desember)
                startInput.value = curYear + '-07-01';
                endInput.value   = curYear + '-12-31';
            } else {
                // Semester Genap (Januari - Juni)
                startInput.value = curYear + '-01-01';
                endInput.value   = curYear + '-06-30';
            }
        }
        startInput.closest('form').submit();
    }
</script>
<?= $this->endSection() ?>
