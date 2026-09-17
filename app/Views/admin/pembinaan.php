<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<!-- Header Halaman -->
<div class="row mb-4 align-items-center">
    <div class="col-12 col-md-7">
        <h3 class="fw-bold text-dark mb-1">
            <i class="bi bi-shield-exclamation text-danger me-2"></i>Riwayat Pembinaan & Siswa Bermasalah
        </h3>
        <p class="text-muted small mb-0">Pusat data penanganan kedisiplinan, pemanggilan orang tua, serta radar deteksi siswa yang memerlukan pembinaan intensif.</p>
    </div>
    <div class="col-12 col-md-5 text-md-end mt-3 mt-md-0 d-flex flex-wrap justify-content-md-end gap-2">
        <a href="<?= site_url('admin/laporan/export-penindakan-excel') ?>" class="btn btn-outline-success" title="Unduh Arsip Lengkap Format Excel">
            <i class="bi bi-file-earmark-excel me-1"></i> Ekspor Excel
        </a>
        <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#modalTambahPembinaan">
            <i class="bi bi-plus-circle-fill me-1"></i> Catat Pembinaan Baru
        </button>
    </div>
</div>

<!-- Alert Notifikasi Flashdata -->
<?php if (session()->getFlashdata('success')): ?>
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4 d-flex align-items-center gap-2" role="alert">
        <i class="bi bi-check-circle-fill fs-5 text-success"></i>
        <div><?= session()->getFlashdata('success') ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<?php if (session()->getFlashdata('error')): ?>
    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4 d-flex align-items-center gap-2" role="alert">
        <i class="bi bi-exclamation-triangle-fill fs-5 text-danger"></i>
        <div><?= session()->getFlashdata('error') ?></div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>

<!-- 4 Kartu Statistik Ringkasan -->
<div class="row g-3 mb-4">
    <!-- Total Tindakan -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-custom border-0 shadow-sm p-3 h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-primary bg-opacity-10 text-primary fs-3">
                    <i class="bi bi-journal-text"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Total Tindakan Pembinaan</div>
                    <div class="fs-4 fw-bold text-dark"><?= count($riwayatPembinaan) ?> Kasus</div>
                    <span class="text-secondary" style="font-size: 0.75rem;">Tercatat oleh Walas & Admin</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Siswa Alpa Kritis -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-custom border-0 shadow-sm p-3 h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-danger bg-opacity-10 text-danger fs-3">
                    <i class="bi bi-person-x-fill"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Siswa Alpa Kritis (&ge; 3)</div>
                    <div class="fs-4 fw-bold text-danger"><?= count($siswaAlpaBermasalah) ?> Siswa</div>
                    <span class="text-danger" style="font-size: 0.75rem;">Perlu Penanganan / Panggilan</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Siswa Sering Terlambat -->
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-custom border-0 shadow-sm p-3 h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-warning bg-opacity-15 text-warning-emphasis fs-3">
                    <i class="bi bi-alarm-fill text-warning"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Sering Terlambat (&ge; 3x)</div>
                    <div class="fs-4 fw-bold text-dark"><?= count($siswaTerlambatBermasalah) ?> Siswa</div>
                    <span class="text-muted" style="font-size: 0.75rem;">Terdata di Meja Piket</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Berkas Bukti -->
    <?php
    $totalBukti = 0;
    foreach ($riwayatPembinaan as $rp) {
        if (!empty($rp['file_bukti'])) $totalBukti++;
    }
    ?>
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-custom border-0 shadow-sm p-3 h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-success bg-opacity-10 text-success fs-3">
                    <i class="bi bi-file-earmark-check-fill"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Bukti Surat / Foto Terlampir</div>
                    <div class="fs-4 fw-bold text-success"><?= $totalBukti ?> Berkas</div>
                    <span class="text-muted" style="font-size: 0.75rem;">Dokumen Pendukung Fisik</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Navigasi Tab Utama -->
<ul class="nav nav-tabs nav-tabs-custom mb-4" id="pembinaanTab" role="tablist">
    <li class="nav-item" role="presentation">
        <button class="nav-link active fw-bold d-flex align-items-center gap-2" id="riwayat-tab" data-bs-toggle="tab" data-bs-target="#tabRiwayat" type="button" role="tab">
            <i class="bi bi-journal-bookmark-fill text-primary"></i>
            <span>Log Riwayat Tindakan Pembinaan</span>
            <span class="badge bg-primary rounded-pill"><?= count($riwayatPembinaan) ?></span>
        </button>
    </li>
    <li class="nav-item" role="presentation">
        <button class="nav-link fw-bold d-flex align-items-center gap-2" id="radar-tab" data-bs-toggle="tab" data-bs-target="#tabRadar" type="button" role="tab">
            <i class="bi bi-radar text-danger"></i>
            <span>Radar Siswa Bermasalah (Peringatan Dini)</span>
            <span class="badge bg-danger rounded-pill"><?= count($siswaAlpaBermasalah) + count($siswaTerlambatBermasalah) ?></span>
        </button>
    </li>
</ul>

<div class="tab-content" id="pembinaanTabContent">
    <!-- ============================================================== -->
    <!-- TAB 1: LOG RIWAYAT TINDAKAN PEMBINAAN                          -->
    <!-- ============================================================== -->
    <div class="tab-pane fade show active" id="tabRiwayat" role="tabpanel">
        <!-- Filter Card -->
        <div class="card card-custom border-0 shadow-sm mb-4">
            <div class="card-body p-3">
                <form action="<?= site_url('admin/pembinaan') ?>" method="GET" class="row g-2 align-items-end">
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-semibold text-secondary mb-1">Filter Rombel Kelas</label>
                        <select name="kelas_id" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">-- Semua Kelas --</option>
                            <?php foreach ($kelasList as $k): ?>
                                <option value="<?= $k['id'] ?>" <?= ($selectedKelas == $k['id']) ? 'selected' : '' ?>>
                                    <?= esc($k['nama_kelas']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-3">
                        <label class="form-label small fw-semibold text-secondary mb-1">Jenis Tindakan</label>
                        <select name="jenis_tindakan" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">-- Semua Jenis Tindakan --</option>
                            <?php
                            $opsiTindakan = [
                                'Peringatan Lisan',
                                'Surat Peringatan I (SP 1)',
                                'Surat Peringatan II (SP 2)',
                                'Surat Peringatan III (SP 3)',
                                'Pemanggilan Orang Tua / Wali',
                                'Konseling Khusus BK / Kesiswaan',
                                'Surat Perjanjian Siswa',
                                'Skorsing Sementara',
                                'Lainnya'
                            ];
                            foreach ($opsiTindakan as $ot): ?>
                                <option value="<?= $ot ?>" <?= ($selectedJenis === $ot) ? 'selected' : '' ?>><?= $ot ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-semibold text-secondary mb-1">Cari Siswa / Catatan</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                            <input type="text" name="q" class="form-control" placeholder="Nama siswa, NISN, atau kata kunci catatan..." value="<?= esc($keyword ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-12 col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary w-100">
                            <i class="bi bi-funnel me-1"></i> Filter
                        </button>
                        <?php if ($selectedKelas || $selectedJenis || $keyword): ?>
                            <a href="<?= site_url('admin/pembinaan') ?>" class="btn btn-sm btn-outline-secondary" title="Reset Filter">
                                <i class="bi bi-arrow-counterclockwise"></i>
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>
        </div>

        <!-- Tabel Riwayat Pembinaan -->
        <div class="card card-custom border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold text-dark mb-0">
                    <i class="bi bi-table me-2 text-primary"></i>Daftar Penanganan Kasus Siswa
                </h5>
                <span class="badge bg-light text-dark border">Total: <?= count($riwayatPembinaan) ?> Rekaman Kasus</span>
            </div>
            <div class="table-responsive">
                <table class="table table-custom table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th style="width: 120px;">Tanggal</th>
                            <th>Nama Siswa & NISN</th>
                            <th style="width: 120px;">Kelas</th>
                            <th>Pembina / Walas</th>
                            <th style="width: 180px;">Jenis Tindakan</th>
                            <th>Catatan Kasus & Komitmen</th>
                            <th class="text-center" style="width: 100px;">Berkas Bukti</th>
                            <th class="text-center" style="width: 80px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($riwayatPembinaan)): ?>
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="bi bi-shield-check fs-2 text-success d-block mb-1"></i>
                                    <span class="fw-bold text-dark">Tidak Ada Catatan Pembinaan</span>
                                    <div class="small">Belum ada riwayat tindakan pembinaan yang sesuai dengan filter pencarian.</div>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($riwayatPembinaan as $idx => $rp): ?>
                                <?php
                                $badgeClass = 'bg-secondary';
                                if (str_contains($rp['jenis_tindakan'], 'SP 1')) $badgeClass = 'bg-warning text-dark';
                                elseif (str_contains($rp['jenis_tindakan'], 'SP 2') || str_contains($rp['jenis_tindakan'], 'SP 3')) $badgeClass = 'bg-danger text-white';
                                elseif (str_contains($rp['jenis_tindakan'], 'Orang Tua')) $badgeClass = 'bg-danger bg-opacity-75 text-white';
                                elseif (str_contains($rp['jenis_tindakan'], 'Konseling')) $badgeClass = 'bg-info text-dark';
                                elseif (str_contains($rp['jenis_tindakan'], 'Lisan')) $badgeClass = 'bg-light text-secondary border';
                                ?>
                                <tr>
                                    <td class="text-center fw-semibold text-muted"><?= $idx + 1 ?></td>
                                    <td>
                                        <div class="fw-semibold text-dark"><?= date('d/m/Y', strtotime($rp['tanggal_tindakan'])) ?></div>
                                        <small class="text-muted"><?= date('H:i', strtotime($rp['created_at'])) ?> WIB</small>
                                    </td>
                                    <td>
                                        <a href="<?= site_url('admin/laporan/siswa/' . $rp['siswa_id']) ?>" class="fw-bold text-primary text-decoration-none" title="Lihat Profil & Track Record">
                                            <?= esc($rp['nama_siswa']) ?>
                                        </a>
                                        <div class="font-monospace small text-muted">NISN: <?= esc($rp['nisn']) ?></div>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border"><?= esc($rp['nama_kelas']) ?></span>
                                    </td>
                                    <td>
                                        <div class="small fw-semibold text-dark"><?= esc($rp['nama_walas'] ?? 'Admin / Kesiswaan') ?></div>
                                        <small class="text-muted">Pembina</small>
                                    </td>
                                    <td>
                                        <span class="badge <?= $badgeClass ?> py-1 px-2 text-wrap" style="font-size: 0.78rem;">
                                            <?= esc($rp['jenis_tindakan']) ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="small text-dark" style="max-width: 320px; white-space: normal;">
                                            <?= esc(mb_strimwidth($rp['catatan_pembinaan'], 0, 100, '...')) ?>
                                        </div>
                                        <?php if (mb_strlen($rp['catatan_pembinaan']) > 100): ?>
                                            <a href="javascript:void(0)" class="small text-primary text-decoration-none" onclick="lihatDetailCatatan(<?= htmlspecialchars(json_encode($rp), ENT_QUOTES, 'UTF-8') ?>)">
                                                Baca Selengkapnya &rarr;
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?php if (! empty($rp['file_bukti'])): ?>
                                            <?php
                                            $ext = strtolower(pathinfo($rp['file_bukti'], PATHINFO_EXTENSION));
                                            $isImg = in_array($ext, ['jpg', 'jpeg', 'png', 'webp']);
                                            ?>
                                            <?php if ($isImg): ?>
                                                <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-2 py-1" onclick="previewFotoBukti('<?= base_url('uploads/pembinaan/' . $rp['file_bukti']) ?>', '<?= esc($rp['nama_siswa']) ?>', '<?= esc($rp['jenis_tindakan']) ?>')" title="Lihat Foto Bukti">
                                                    <i class="bi bi-image me-1"></i>Foto Bukti
                                                </button>
                                            <?php else: ?>
                                                <a href="<?= base_url('uploads/pembinaan/' . $rp['file_bukti']) ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1" title="Buka Dokumen / PDF">
                                                    <i class="bi bi-file-earmark-pdf me-1"></i>Dokumen
                                                </a>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <span class="text-muted small">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= site_url('admin/pembinaan/hapus/' . $rp['id']) ?>" class="btn btn-sm btn-outline-danger border-0" onclick="return confirm('Apakah Anda yakin ingin menghapus catatan pembinaan ini?')" title="Hapus Riwayat">
                                            <i class="bi bi-trash-fill"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- TAB 2: RADAR SISWA BERMASALAH (PERINGATAN DINI)                -->
    <!-- ============================================================== -->
    <div class="tab-pane fade" id="tabRadar" role="tabpanel">
        <div class="row g-4">
            <!-- Radar 1: Siswa dengan Alpa >= 3 -->
            <div class="col-12 col-xl-6">
                <div class="card card-custom border-0 shadow-sm h-100">
                    <div class="card-header bg-danger text-white py-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-exclamation-triangle-fill fs-5"></i>
                            <h6 class="fw-bold mb-0">Siswa dengan Alpa Kritis (&ge; 3 Kali)</h6>
                        </div>
                        <span class="badge bg-white text-danger fw-bold font-monospace"><?= count($siswaAlpaBermasalah) ?> Siswa</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 520px;">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th style="width: 40px;">No</th>
                                        <th>Nama Siswa & Kelas</th>
                                        <th class="text-center" style="width: 90px;">Total Alpa</th>
                                        <th class="text-center" style="width: 100px;">Status Bina</th>
                                        <th class="text-center" style="width: 120px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($siswaAlpaBermasalah)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-5 text-muted">
                                                <i class="bi bi-shield-fill-check fs-2 text-success d-block mb-1"></i>
                                                <span class="fw-bold text-dark">Luar Biasa!</span>
                                                <div class="small">Tidak ada siswa yang memiliki akumulasi Alpa &ge; 3.</div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($siswaAlpaBermasalah as $idx => $sa): ?>
                                            <tr>
                                                <td class="text-center text-muted small"><?= $idx + 1 ?></td>
                                                <td>
                                                    <a href="<?= site_url('admin/laporan/siswa/' . $sa['siswa_id']) ?>" class="fw-bold text-dark text-decoration-none">
                                                        <?= esc($sa['nama_siswa']) ?>
                                                    </a>
                                                    <div class="small text-muted">
                                                        <span class="badge bg-light text-dark border"><?= esc($sa['nama_kelas']) ?></span>
                                                        <span class="ms-1 font-monospace">NISN: <?= esc($sa['nisn']) ?></span>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-danger fs-6 px-2 py-1"><?= $sa['total_alpa'] ?> Alpa</span>
                                                </td>
                                                <td class="text-center">
                                                    <?php if ($sa['total_pembinaan'] > 0): ?>
                                                        <span class="badge bg-success bg-opacity-10 text-success border border-success" title="Sudah pernah dibina">
                                                            <?= $sa['total_pembinaan'] ?>x Dibina
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">
                                                            Belum Dibina!
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-danger rounded-pill px-2 py-1" onclick="bukaModalBinaSiswa(<?= $sa['siswa_id'] ?>, '<?= esc($sa['nama_siswa']) ?>', '<?= esc($sa['nama_kelas']) ?>', 'Alpa Kritis (<?= $sa['total_alpa'] ?>x)')">
                                                        <i class="bi bi-pencil-square me-1"></i>Bina
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

            <!-- Radar 2: Siswa Sering Terlambat >= 3 Kali -->
            <div class="col-12 col-xl-6">
                <div class="card card-custom border-0 shadow-sm h-100">
                    <div class="card-header bg-warning text-dark py-3 d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-alarm-fill fs-5 text-dark"></i>
                            <h6 class="fw-bold mb-0 text-dark">Siswa Sering Terlambat di Meja Piket (&ge; 3 Kali)</h6>
                        </div>
                        <span class="badge bg-dark text-white fw-bold font-monospace"><?= count($siswaTerlambatBermasalah) ?> Siswa</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 520px;">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th style="width: 40px;">No</th>
                                        <th>Nama Siswa & Kelas</th>
                                        <th class="text-center" style="width: 100px;">Terlambat</th>
                                        <th class="text-center" style="width: 100px;">Status Bina</th>
                                        <th class="text-center" style="width: 120px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($siswaTerlambatBermasalah)): ?>
                                        <tr>
                                            <td colspan="5" class="text-center py-5 text-muted">
                                                <i class="bi bi-clock-history fs-2 text-success d-block mb-1"></i>
                                                <span class="fw-bold text-dark">Tertib Kedisiplinan!</span>
                                                <div class="small">Tidak ada siswa dengan catatan keterlambatan &ge; 3 kali.</div>
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($siswaTerlambatBermasalah as $idx => $st): ?>
                                            <tr>
                                                <td class="text-center text-muted small"><?= $idx + 1 ?></td>
                                                <td>
                                                    <a href="<?= site_url('admin/laporan/siswa/' . $st['siswa_id']) ?>" class="fw-bold text-dark text-decoration-none">
                                                        <?= esc($st['nama_siswa']) ?>
                                                    </a>
                                                    <div class="small text-muted">
                                                        <span class="badge bg-light text-dark border"><?= esc($st['nama_kelas']) ?></span>
                                                        <span class="ms-1 font-monospace">NISN: <?= esc($st['nisn']) ?></span>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-warning text-dark fs-6 px-2 py-1"><?= $st['total_terlambat'] ?>x Masuk</span>
                                                    <div class="text-muted" style="font-size: 0.7rem;"><?= $st['total_menit'] ?> Menit</div>
                                                </td>
                                                <td class="text-center">
                                                    <?php if ($st['total_pembinaan'] > 0): ?>
                                                        <span class="badge bg-success bg-opacity-10 text-success border border-success" title="Sudah pernah dibina">
                                                            <?= $st['total_pembinaan'] ?>x Dibina
                                                        </span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">
                                                            Belum Dibina!
                                                        </span>
                                                    <?php endif; ?>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-warning text-dark fw-semibold rounded-pill px-2 py-1" onclick="bukaModalBinaSiswa(<?= $st['siswa_id'] ?>, '<?= esc($st['nama_siswa']) ?>', '<?= esc($st['nama_kelas']) ?>', 'Sering Terlambat (<?= $st['total_terlambat'] ?>x)')">
                                                        <i class="bi bi-pencil-square me-1"></i>Bina
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
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL 1: INPUT TINDAKAN PEMBINAAN BARU                        -->
<!-- ============================================================== -->
<div class="modal fade" id="modalTambahPembinaan" tabindex="-1" aria-labelledby="modalTambahPembinaanLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-danger text-white py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-shield-plus fs-4"></i>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="modalTambahPembinaanLabel">Input Tindakan Pembinaan Siswa</h5>
                        <small class="text-white-50">Catat penanganan kedisiplinan dan lampirkan bukti fisik/surat perjanjian</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= site_url('admin/pembinaan/simpan') ?>" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <!-- Pilih Siswa -->
                        <div class="col-12 col-md-8">
                            <label class="form-label fw-semibold small text-secondary">
                                Pilih Siswa Bermasalah <span class="text-danger">*</span>
                            </label>
                            <select name="siswa_id" id="modalSelectSiswa" class="form-select" required>
                                <option value="">-- Cari dan Pilih Siswa --</option>
                                <?php 
                                $currentKelasGroup = '';
                                foreach ($siswaList as $s): 
                                    if ($s['nama_kelas'] !== $currentKelasGroup) {
                                        if ($currentKelasGroup !== '') echo '</optgroup>';
                                        $currentKelasGroup = $s['nama_kelas'];
                                        echo '<optgroup label="Kelas ' . esc($currentKelasGroup) . '">';
                                    }
                                ?>
                                    <option value="<?= $s['id'] ?>" data-walas="<?= $s['walas_id'] ?>">
                                        <?= esc($s['nama_siswa']) ?> (NISN: <?= esc($s['nisn']) ?>)
                                    </option>
                                <?php endforeach; ?>
                                <?php if ($currentKelasGroup !== '') echo '</optgroup>'; ?>
                            </select>
                        </div>

                        <!-- Tanggal Tindakan -->
                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold small text-secondary">
                                Tanggal Tindakan <span class="text-danger">*</span>
                            </label>
                            <input type="date" name="tanggal_tindakan" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <!-- Jenis Tindakan -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold small text-secondary">
                                Jenis Tindakan Pembinaan <span class="text-danger">*</span>
                            </label>
                            <select name="jenis_tindakan" class="form-select" required>
                                <option value="Peringatan Lisan">Peringatan Lisan / Teguran Pertama</option>
                                <option value="Surat Peringatan I (SP 1)">Surat Peringatan I (SP 1)</option>
                                <option value="Surat Peringatan II (SP 2)">Surat Peringatan II (SP 2)</option>
                                <option value="Surat Peringatan III (SP 3)">Surat Peringatan III (SP 3)</option>
                                <option value="Pemanggilan Orang Tua / Wali">Pemanggilan Orang Tua / Wali</option>
                                <option value="Konseling Khusus BK / Kesiswaan">Konseling Khusus BK / Kesiswaan</option>
                                <option value="Surat Perjanjian Siswa">Surat Perjanjian Siswa & Komitmen</option>
                                <option value="Skorsing Sementara">Skorsing Sementara</option>
                                <option value="Lainnya">Lainnya / Penanganan Terpadu</option>
                            </select>
                        </div>

                        <!-- Pembina (Wali Kelas / Guru) -->
                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold small text-secondary">
                                Guru Pembina / Wali Kelas
                            </label>
                            <select name="walas_id" id="modalSelectPembina" class="form-select">
                                <option value="">-- Ikuti Wali Kelas Otomatis --</option>
                                <?php foreach ($guruList as $g): ?>
                                    <option value="<?= $g['id'] ?>"><?= esc($g['nama_guru']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Catatan Pembinaan -->
                        <div class="col-12">
                            <label class="form-label fw-semibold small text-secondary">
                                Catatan Pembinaan, Latar Belakang Masalah & Komitmen Siswa <span class="text-danger">*</span>
                            </label>
                            <textarea name="catatan_pembinaan" id="modalCatatanPembinaan" class="form-control" rows="4" placeholder="Uraikan alasan pembinaan (misal: Alpa berturut-turut, keterlambatan berulang), hasil pertemuan dengan siswa/orang tua, serta sanksi atau komitmen yang disepakati..." required></textarea>
                        </div>

                        <!-- Upload Berkas Bukti -->
                        <div class="col-12">
                            <label class="form-label fw-semibold small text-secondary">
                                Lampiran Berkas Bukti Fisik (Opsional)
                            </label>
                            <input type="file" name="file_bukti" class="form-control" accept=".jpg,.jpeg,.png,.pdf">
                            <div class="form-text" style="font-size: 0.75rem;">Format: JPG, PNG, atau PDF. Maksimal 2MB (contoh: foto surat pernyataan, tanda tangan orang tua).</div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-4 justify-content-between">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger px-4">
                        <i class="bi bi-save-fill me-1"></i> Simpan Catatan Pembinaan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ============================================================== -->
<!-- MODAL 2: DETAIL CATATAN PEMBINAAN                             -->
<!-- ============================================================== -->
<div class="modal fade" id="modalDetailPembinaan" tabindex="-1" aria-labelledby="modalDetailPembinaanLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light py-3">
                <h5 class="modal-title fw-bold text-dark mb-0" id="modalDetailPembinaanLabel">Detail Riwayat Pembinaan</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="p-3 bg-light rounded-3 mb-3 border">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <h6 class="fw-bold text-dark mb-0" id="detailNamaSiswa">-</h6>
                        <span class="badge bg-danger" id="detailJenisTindakan">-</span>
                    </div>
                    <div class="text-muted small">
                        Kelas: <strong id="detailKelas">-</strong> | Tanggal: <strong id="detailTanggal">-</strong>
                    </div>
                    <div class="text-muted small">
                        Pembina: <strong id="detailPembina">-</strong>
                    </div>
                </div>

                <label class="form-label fw-bold text-dark small mb-1">Catatan Kasus & Komitmen:</label>
                <div class="p-3 border rounded-3 bg-white mb-3" style="max-height: 250px; overflow-y: auto; white-space: pre-wrap; font-size: 0.9rem;" id="detailCatatan">
                    -
                </div>

                <div id="detailContainerBukti" class="d-none">
                    <label class="form-label fw-bold text-dark small mb-1">Berkas Bukti Terlampir / Foto:</label>
                    <div id="detailImageWrapper" class="mb-2 text-center d-none">
                        <img id="detailImgBukti" src="" class="img-fluid rounded-3 border shadow-sm" style="max-height: 260px; object-fit: contain; cursor: pointer;" onclick="previewFotoBukti(this.src, document.getElementById('detailNamaSiswa').innerText, document.getElementById('detailJenisTindakan').innerText)" title="Klik untuk memperbesar">
                        <div class="text-muted small mt-1"><i class="bi bi-zoom-in me-1"></i>Klik foto untuk memperbesar</div>
                    </div>
                    <div>
                        <a href="#" target="_blank" class="btn btn-sm btn-outline-primary" id="detailLinkBukti">
                            <i class="bi bi-file-earmark-arrow-down me-1"></i> Buka / Unduh Berkas Lengkap
                        </a>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2 px-3">
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Zoom Foto Bukti Pembinaan -->
<div class="modal fade" id="modalPreviewFotoBukti" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow bg-dark text-white">
            <div class="modal-header border-secondary py-3">
                <h6 class="modal-title fw-bold d-flex align-items-center gap-2" id="titlePreviewFotoBukti">
                    <i class="bi bi-image text-warning"></i>
                    <span>Foto Bukti Tindakan Pembinaan</span>
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center p-3" style="background: #000;">
                <img id="imgPreviewFotoBukti" src="" class="img-fluid rounded-3" style="max-height: 520px; object-fit: contain;">
            </div>
            <div class="modal-footer border-secondary py-2 px-3 justify-content-between">
                <a href="#" id="linkUnduhFotoBukti" target="_blank" class="btn btn-sm btn-outline-light rounded-pill px-3">
                    <i class="bi bi-download me-1"></i> Buka File Asli / Unduh
                </a>
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function previewFotoBukti(url, namaSiswa, jenisTindakan) {
        document.getElementById('titlePreviewFotoBukti').innerText = 'Foto Bukti: ' + namaSiswa + ' (' + jenisTindakan + ')';
        document.getElementById('imgPreviewFotoBukti').src = url;
        document.getElementById('linkUnduhFotoBukti').href = url;
        const modal = new bootstrap.Modal(document.getElementById('modalPreviewFotoBukti'));
        modal.show();
    }

    function lihatDetailCatatan(data) {
        document.getElementById('detailNamaSiswa').innerText = data.nama_siswa + ' (' + data.nisn + ')';
        document.getElementById('detailKelas').innerText = data.nama_kelas;
        document.getElementById('detailTanggal').innerText = data.tanggal_tindakan;
        document.getElementById('detailPembina').innerText = data.nama_walas || 'Admin / Kesiswaan';
        document.getElementById('detailJenisTindakan').innerText = data.jenis_tindakan;
        document.getElementById('detailCatatan').innerText = data.catatan_pembinaan;

        const containerBukti = document.getElementById('detailContainerBukti');
        const linkBukti = document.getElementById('detailLinkBukti');
        const imgWrapper = document.getElementById('detailImageWrapper');
        const imgEl = document.getElementById('detailImgBukti');

        if (data.file_bukti) {
            containerBukti.classList.remove('d-none');
            const fileUrl = '<?= base_url('uploads/pembinaan/') ?>' + '/' + data.file_bukti;
            linkBukti.href = fileUrl;

            // Cek apakah berkas bertipe gambar
            const ext = data.file_bukti.split('.').pop().toLowerCase();
            if (['jpg', 'jpeg', 'png', 'webp'].includes(ext)) {
                imgEl.src = fileUrl;
                imgWrapper.classList.remove('d-none');
            } else {
                imgWrapper.classList.add('d-none');
            }
        } else {
            containerBukti.classList.add('d-none');
            imgWrapper.classList.add('d-none');
        }

        const modalEl = document.getElementById('modalDetailPembinaan');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }

    function bukaModalBinaSiswa(siswaId, namaSiswa, namaKelas, catatanAwal) {
        const selectSiswa = document.getElementById('modalSelectSiswa');
        if (selectSiswa) {
            selectSiswa.value = siswaId;
        }

        const catatanTextarea = document.getElementById('modalCatatanPembinaan');
        if (catatanTextarea) {
            catatanTextarea.value = 'Pembinaan terkait: ' + catatanAwal + '.\n';
        }

        const modalEl = document.getElementById('modalTambahPembinaan');
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }
</script>
<?= $this->endSection() ?>
