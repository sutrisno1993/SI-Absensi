<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<?php
$liveUrl = site_url('live-presensi/' . esc($sesiKelas['share_token']));
$namaWalasStr = $walas ? $walas['nama_guru'] : 'Wali Kelas';
$hasFoto = !empty($sesiKelas['foto_kelas']) && file_exists(FCPATH . $sesiKelas['foto_kelas']);
$isLocked = ($isSunday || !empty($agendaBebasAbsen));
$isPastDate = (strtotime($tanggal) < strtotime(date('Y-m-d')));
$yesterday  = date('Y-m-d', strtotime('-1 day'));
$twoDaysAgo = date('Y-m-d', strtotime('-2 days'));
?>

<div class="row mb-4 align-items-center">
    <div class="col-12 col-md-6">
        <h3 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
            <i class="bi bi-clipboard-check text-primary"></i>
            <span>Presensi Harian Kelas <?= esc($kelas['nama_kelas']) ?></span>
        </h3>
        <p class="text-muted small mb-0">Input kehadiran teman sekelas, foto dokumentasi anak-anak di kelas, dan bagikan link live ke grup WA orang tua.</p>
    </div>
    <div class="col-12 col-md-6 mt-3 mt-md-0">
        <!-- Interactive Date Selector Filter -->
        <div class="d-flex flex-column align-items-md-end gap-2">
            <form id="formPilihTanggal" action="<?= site_url('pj') ?>" method="GET" class="d-flex align-items-center justify-content-md-end flex-wrap gap-2">
                <label for="datepicker-interaktif" class="form-label mb-0 fw-semibold text-secondary small text-nowrap">
                    <i class="bi bi-calendar3 me-1"></i>Pilih Tanggal:
                </label>
                <div class="input-group input-group-sm w-auto shadow-sm">
                    <input type="text" class="form-control form-control-sm text-center fw-bold bg-dark text-white border-secondary" id="datepicker-interaktif" name="tanggal" value="<?= esc($tanggal) ?>" readonly style="cursor: pointer; min-width: 130px;">
                    <button class="btn btn-outline-secondary" type="button" id="btnBukaKalender" title="Buka Kalender Interaktif">
                        <i class="bi bi-calendar-event"></i>
                    </button>
                </div>
                
                <div class="btn-group btn-group-sm">
                    <a href="<?= site_url('pj?tanggal=' . date('Y-m-d')) ?>" class="btn btn-outline-secondary <?= $tanggal === date('Y-m-d') ? 'active' : '' ?>">Hari Ini</a>
                    <a href="<?= site_url('pj?tanggal=' . $yesterday) ?>" class="btn btn-outline-secondary <?= $tanggal === $yesterday ? 'active' : '' ?>">Kemarin</a>
                    <a href="<?= site_url('pj?tanggal=' . $twoDaysAgo) ?>" class="btn btn-outline-secondary <?= $tanggal === $twoDaysAgo ? 'active' : '' ?>">2 Hari Lalu</a>
                </div>
            </form>
            
            <!-- Action Buttons & Calendar Legend -->
            <div class="d-flex align-items-center gap-2 flex-wrap justify-content-md-end">
                <button type="button" class="btn btn-sm btn-success rounded-pill px-3 shadow-sm fw-semibold" onclick="bukaModalWA()">
                    <i class="bi bi-whatsapp me-1"></i> Laporan WA Ortu
                </button>
                <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalFotoKelas">
                    <i class="bi bi-camera-fill me-1"></i> <?= $hasFoto ? 'Ganti Foto' : 'Upload Foto' ?>
                </button>
            </div>

            <div class="d-flex align-items-center gap-3 small text-muted flex-wrap justify-content-md-end">
                <span class="d-flex align-items-center gap-1">
                    <span class="d-inline-block rounded-circle" style="width: 8px; height: 8px; background-color: #ef4444;"></span>
                    <span style="font-size: 0.75rem;">Merah: Libur / Minggu</span>
                </span>
                <span class="d-flex align-items-center gap-1">
                    <span class="d-inline-block rounded-circle" style="width: 8px; height: 8px; background-color: #f59e0b;"></span>
                    <span style="font-size: 0.75rem;">Kuning: Acara / Agenda</span>
                </span>
                <span class="d-flex align-items-center gap-1">
                    <span class="d-inline-block rounded-circle" style="width: 8px; height: 8px; background-color: #10b981;"></span>
                    <span style="font-size: 0.75rem;">Hijau: KBM Aktif</span>
                </span>
            </div>
        </div>
    </div>
</div>

<?php if ($isSunday): ?>
    <div class="alert alert-info border-0 shadow-sm rounded-4 d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4" role="alert">
        <div class="d-flex align-items-center gap-3">
            <i class="bi bi-info-circle-fill fs-2 text-info flex-shrink-0"></i>
            <div>
                <strong class="fs-6">Hari Minggu (Libur Akhir Pekan):</strong>
                <div class="small">
                    Tanggal ini adalah hari Minggu sehingga kegiatan KBM dan presensi libur otomatis.
                    <?php if (!empty($hasExistingRecords)): ?>
                        <div class="mt-1 text-danger fw-semibold">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Data presensi untuk hari Minggu ini sudah terlanjur diinput. Klik tombol <strong>Kosongkan Absensi</strong> agar data presensi dihapus dan status siswa kembali menjadi kosong (belum diisi).
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php if (!empty($hasExistingRecords)): ?>
            <div class="flex-shrink-0">
                <button type="button" class="btn btn-sm btn-danger rounded-pill px-3 py-2 fw-bold shadow-sm" onclick="bukaModalKosongkanAbsen()">
                    <i class="bi bi-trash3 me-1"></i> Kosongkan Absensi Tanggal Ini
                </button>
            </div>
        <?php endif; ?>
    </div>
<?php elseif (!empty($agendaBebasAbsen)): ?>
    <div class="alert border-0 shadow-sm rounded-4 d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4 text-warning" style="background-color: rgba(245, 158, 11, 0.15); border: 1px solid rgba(245, 158, 11, 0.35) !important;">
        <div class="d-flex align-items-center gap-3">
            <i class="bi bi-calendar2-check-fill fs-2 text-warning flex-shrink-0"></i>
            <div>
                <strong class="fs-6 text-white">Hari Bebas Presensi: <?= esc($agendaBebasAbsen['nama_agenda']) ?></strong>
                <div class="small text-light text-opacity-75">
                    Kategori: <?= ucfirst($agendaBebasAbsen['tipe']) ?> &bull; Status KBM: Bebas Absen &bull; <?= esc($agendaBebasAbsen['keterangan'] ?? '') ?>
                    <div class="mt-1 text-success fw-semibold">
                        <i class="bi bi-check-circle me-1"></i> Siswa dibebaskan dari kewajiban presensi (otomatis belum diisi / tidak dihitung alpa).
                    </div>
                    <?php if (!empty($hasExistingRecords)): ?>
                        <div class="mt-1 text-danger fw-semibold">
                            <i class="bi bi-exclamation-triangle-fill me-1"></i> Data presensi pada tanggal bebas absen ini sudah terlanjur tersimpan. Anda dapat mengosongkannya kembali menggunakan tombol di sebelah kanan.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php if (!empty($hasExistingRecords)): ?>
            <div class="flex-shrink-0">
                <button type="button" class="btn btn-sm btn-danger rounded-pill px-3 py-2 fw-bold shadow-sm" onclick="bukaModalKosongkanAbsen()">
                    <i class="bi bi-trash3 me-1"></i> Kosongkan Absensi Tanggal Ini
                </button>
            </div>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if ($isPastDate): ?>
    <div class="alert alert-warning border-0 shadow-sm rounded-3 d-flex align-items-center gap-2 mb-4" role="alert">
        <i class="bi bi-clock-history fs-4 text-warning"></i>
        <div>
            <strong>Presensi Tanggal Lalu:</strong> Anda sedang melihat data presensi untuk tanggal <strong><?= date('d F Y', strtotime($tanggal)) ?></strong>.
        </div>
    </div>
<?php endif; ?>

<!-- Card Bagikan Link Presensi Realtime ke Grup WA Orang Tua -->
<div class="card card-custom border-0 shadow-sm mb-4 bg-primary bg-opacity-10 border-start border-4 border-primary">
    <div class="card-body p-4">
        <div class="row align-items-center g-3">
            <div class="col-12 col-lg-8">
                <div class="d-flex align-items-start gap-3">
                    <div class="bg-primary bg-opacity-20 text-primary p-3 rounded-4 fs-3 flex-shrink-0">
                        <i class="bi bi-broadcast"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h5 class="fw-bold text-white mb-0">Link Pemantauan Realtime Orang Tua Murid</h5>
                            <span class="badge bg-danger bg-opacity-25 text-danger border border-danger border-opacity-50 px-2 py-1 small">
                                <i class="bi bi-record-circle-fill me-1"></i> Live Active
                            </span>
                        </div>
                        <p class="text-light small mb-2">
                            Tautan publik ini dapat diakses langsung oleh orang tua di HP mereka tanpa perlu login. Menampilkan rekap kehadiran langsung dan foto suasana anak-anak di kelas hari ini.
                        </p>
                        
                        <!-- Mini Counter -->
                        <div class="d-flex flex-wrap gap-2 small">
                            <?php if (!empty($hasExistingRecords)): ?>
                                <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-30 px-2 py-1">
                                    Hadir: <strong><?= $countH ?></strong>
                                </span>
                                <span class="badge bg-warning bg-opacity-20 text-warning border border-warning border-opacity-30 px-2 py-1">
                                    Sakit: <strong><?= $countS ?></strong>
                                </span>
                                <span class="badge bg-info bg-opacity-20 text-info border border-info border-opacity-30 px-2 py-1">
                                    Izin: <strong><?= $countI ?></strong>
                                </span>
                                <span class="badge bg-danger bg-opacity-20 text-danger border border-danger border-opacity-30 px-2 py-1">
                                    Alpha: <strong><?= $countA ?></strong>
                                </span>
                                <?php if (! empty($isKelas2)): ?>
                                <span class="badge px-2 py-1" style="background-color: rgba(139,92,246,0.25) !important; color: #c4b5fd !important; border: 1px solid rgba(139,92,246,0.4) !important;">
                                    PKL: <strong><?= $countP ?></strong>
                                </span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="badge bg-secondary bg-opacity-25 text-light border border-secondary border-opacity-50 px-2 py-1">
                                    <i class="bi bi-dash-circle me-1"></i> Status: Belum Diisi (Kosong)
                                </span>
                            <?php endif; ?>
                            <span class="badge bg-dark text-muted border px-2 py-1">
                                Total: <?= count($daftarSiswa) ?> Siswa
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-4 text-lg-end">
                <div class="d-flex flex-column gap-2">
                    <button type="button" class="btn btn-success rounded-pill px-3 fw-semibold" onclick="bukaModalWA()">
                        <i class="bi bi-whatsapp me-1"></i> Kirim ke WhatsApp Ortu
                    </button>
                    <div class="input-group input-group-sm">
                        <input type="text" id="liveShareUrl" class="form-control font-monospace bg-dark text-light border-secondary" value="<?= esc($liveUrl) ?>" readonly>
                        <button type="button" class="btn btn-primary-custom" onclick="salinLinkLive()">
                            <i class="bi bi-clipboard"></i> Salin
                        </button>
                    </div>
                    <a href="<?= esc($liveUrl) ?>" target="_blank" class="text-info small text-decoration-none text-center">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Lihat Tampilan Live Orang Tua
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Card Dokumentasi Foto Anak-Anak di Kelas -->
<div class="card card-custom border-0 shadow-sm mb-4">
    <div class="card-body p-3 p-md-4">
        <div class="row align-items-center g-3">
            <div class="col-12 col-md-3 text-center">
                <?php if ($hasFoto): ?>
                    <a href="<?= base_url(esc($sesiKelas['foto_kelas'])) ?>" target="_blank" title="Klik untuk memperbesar">
                        <img src="<?= base_url(esc($sesiKelas['foto_kelas'])) ?>" alt="Dokumentasi Kelas" class="img-fluid rounded-3 border border-secondary border-opacity-25 shadow-sm" style="max-height: 140px; object-fit: cover; width: 100%;">
                    </a>
                <?php else: ?>
                    <div class="p-4 rounded-3 bg-dark border border-dashed border-secondary text-muted text-center">
                        <i class="bi bi-camera fs-1 d-block mb-1 text-secondary"></i>
                        <span class="small">Belum ada foto kegiatan kelas</span>
                    </div>
                <?php endif; ?>
            </div>
            <div class="col-12 col-md-6">
                <h5 class="fw-bold text-white mb-1 d-flex align-items-center gap-2">
                    <i class="bi bi-images text-warning"></i> Foto Suasana Kelas Hari Ini
                </h5>
                <p class="text-muted small mb-2">
                    <?php if ($hasFoto): ?>
                        <span class="text-success"><i class="bi bi-check-circle-fill me-1"></i> Foto berhasil dilampirkan.</span> Foto ini akan langsung tampil saat orang tua membuka link live presensi.
                    <?php else: ?>
                        Jepret langsung suasana belajar/presensi anak-anak di kelas menggunakan kamera HP agar orang tua dapat melihat keadaan kelas secara nyata.
                    <?php endif; ?>
                </p>
                <div class="small text-muted">
                    Format yang didukung: <strong>JPG, JPEG, PNG, WEBP</strong> (Maksimal 5MB).
                </div>
            </div>
            <div class="col-12 col-md-3 text-md-end">
                <button type="button" class="btn btn-outline-warning rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalFotoKelas">
                    <i class="bi bi-camera-fill me-1"></i> <?= $hasFoto ? 'Ganti / Ambil Ulang Foto' : 'Ambil Foto Kelas' ?>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Form Input Presensi Siswa -->
<form action="<?= site_url('pj/simpan') ?>" method="POST" enctype="multipart/form-data">
    <?= csrf_field() ?>
    <input type="hidden" name="tanggal" value="<?= esc($tanggal) ?>">

    <div class="card card-custom border-0 mb-4">
        <div class="card-header bg-transparent border-0 py-3 px-4 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <span class="badge bg-primary px-3 py-2 fs-6 rounded-pill">
                    <i class="bi bi-calendar-date me-1"></i> <?= date('d F Y', strtotime($tanggal)) ?>
                </span>
                <span class="badge bg-dark text-muted border px-2 py-2">
                    Total Siswa: <?= count($daftarSiswa) ?> Orang
                </span>
                <?php if (!empty($hasExistingRecords)): ?>
                    <span class="badge bg-success bg-opacity-15 text-success border border-success border-opacity-25 px-2 py-2">
                        <i class="bi bi-check-circle me-1"></i> Sudah Terisi
                    </span>
                <?php else: ?>
                    <span class="badge bg-secondary bg-opacity-15 text-muted border border-secondary border-opacity-25 px-2 py-2">
                        <i class="bi bi-dash-circle me-1"></i> Belum Diisi (Kosong)
                    </span>
                <?php endif; ?>
            </div>
            <div class="d-flex gap-2 flex-wrap align-items-center">
                <?php if (!$isLocked): ?>
                    <button type="button" class="btn btn-sm btn-outline-success fw-semibold rounded-pill px-3" onclick="setSemuaHadir()">
                        <i class="bi bi-check-all me-1"></i> Set Semua Hadir (H)
                    </button>
                <?php else: ?>
                    <span class="badge bg-danger bg-opacity-20 text-danger border border-danger border-opacity-30 px-3 py-2 rounded-pill">
                        <i class="bi bi-lock-fill me-1"></i> Presensi Dikunci (Hari Libur / Acara)
                    </span>
                <?php endif; ?>
                <?php if (!empty($hasExistingRecords)): ?>
                    <button type="button" class="btn btn-sm btn-outline-danger fw-semibold rounded-pill px-3 shadow-sm" onclick="bukaModalKosongkanAbsen()">
                        <i class="bi bi-trash3 me-1"></i> Kosongkan Absensi
                    </button>
                <?php endif; ?>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-custom table-hover align-middle mb-0">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">No</th>
                        <th style="width: 130px;">NISN</th>
                        <th>Nama Siswa</th>
                        <th class="text-center" style="width: 250px;">Status Kehadiran</th>
                        <th style="width: 260px;">Keterangan (Opsional)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($daftarSiswa)): ?>
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                Belum ada data siswa di kelas ini.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($daftarSiswa as $idx => $s): ?>
                            <tr>
                                <td class="text-center fw-semibold text-muted"><?= $idx + 1 ?></td>
                                <td><span class="badge bg-dark text-light border font-monospace"><?= esc($s['nisn']) ?></span></td>
                                <td class="fw-bold text-white"><?= esc($s['nama_siswa']) ?></td>
                                <td class="text-center">
                                    <?php $currentStatus = $s['absensi_id'] ? $s['status'] : null; ?>
                                    <div class="d-inline-flex gap-2">
                                        <!-- Hadir -->
                                        <label>
                                            <input type="radio" class="status-pill-radio radio-h" name="absensi[<?= $s['siswa_id'] ?>][status]" value="H" <?= $currentStatus === 'H' ? 'checked' : '' ?> <?= $isLocked ? 'disabled' : '' ?>>
                                            <span class="status-label" title="Hadir">H</span>
                                        </label>
                                        <!-- Sakit -->
                                        <label>
                                            <input type="radio" class="status-pill-radio" name="absensi[<?= $s['siswa_id'] ?>][status]" value="S" <?= $currentStatus === 'S' ? 'checked' : '' ?> <?= $isLocked ? 'disabled' : '' ?>>
                                            <span class="status-label" title="Sakit">S</span>
                                        </label>
                                        <!-- Izin -->
                                        <label>
                                            <input type="radio" class="status-pill-radio" name="absensi[<?= $s['siswa_id'] ?>][status]" value="I" <?= $currentStatus === 'I' ? 'checked' : '' ?> <?= $isLocked ? 'disabled' : '' ?>>
                                            <span class="status-label" title="Izin">I</span>
                                        </label>
                                        <!-- Alpa -->
                                        <label>
                                            <input type="radio" class="status-pill-radio" name="absensi[<?= $s['siswa_id'] ?>][status]" value="A" <?= $currentStatus === 'A' ? 'checked' : '' ?> <?= $isLocked ? 'disabled' : '' ?>>
                                            <span class="status-label" title="Alpa / Tanpa Keterangan">A</span>
                                        </label>
                                        <?php if (! empty($isKelas2)): ?>
                                        <!-- PKL (Khusus Kelas 2 / XI) -->
                                        <label>
                                            <input type="radio" class="status-pill-radio" name="absensi[<?= $s['siswa_id'] ?>][status]" value="P" <?= $currentStatus === 'P' ? 'checked' : '' ?> <?= $isLocked ? 'disabled' : '' ?>>
                                            <span class="status-label" title="Praktik Kerja Lapangan (PKL / Magang)">P</span>
                                        </label>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm bg-dark text-light border-secondary" name="absensi[<?= $s['siswa_id'] ?>][keterangan]" value="<?= esc($s['keterangan'] ?? '') ?>" placeholder="Misal: Demam, Surat dokter..." <?= $isLocked ? 'disabled' : '' ?>>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (! empty($daftarSiswa)): ?>
            <div class="card-footer bg-transparent border-0 py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <span class="text-muted small">
                    <i class="bi bi-info-circle me-1"></i> Setelah menyimpan, link realtime otomatis diperbarui secara instan.
                </span>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <?php if (!empty($hasExistingRecords)): ?>
                        <button type="button" class="btn btn-outline-danger px-3 py-2 rounded-pill fw-semibold" onclick="bukaModalKosongkanAbsen()">
                            <i class="bi bi-trash3 me-1"></i> Kosongkan Absensi
                        </button>
                    <?php endif; ?>
                    <?php if ($isLocked): ?>
                        <button type="button" class="btn btn-secondary px-4 py-2 rounded-pill fw-semibold" disabled>
                            <i class="bi bi-lock-fill me-2"></i> Presensi Dikunci (Hari Libur / Acara)
                        </button>
                    <?php else: ?>
                        <button type="submit" class="btn btn-primary-custom px-4 py-2 rounded-pill fw-semibold">
                            <i class="bi bi-cloud-check-fill me-2"></i> Simpan Data Presensi
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</form>

<!-- Modal Upload Foto Dokumentasi Kelas -->
<div class="modal fade" id="modalFotoKelas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content card-custom border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2">
                    <i class="bi bi-camera text-warning"></i>
                    <span>Foto Dokumentasi Kelas Hari Ini</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= site_url('pj/upload-foto') ?>" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-body py-4">
                    <div class="text-center mb-3">
                        <div id="previewFotoBox" class="p-3 rounded-3 bg-dark border border-dashed border-secondary mb-2" style="min-height: 180px; display: flex; align-items: center; justify-content: center;">
                            <?php if ($hasFoto): ?>
                                <img id="previewImg" src="<?= base_url(esc($sesiKelas['foto_kelas'])) ?>" class="img-fluid rounded-3" style="max-height: 220px; object-fit: contain;">
                            <?php else: ?>
                                <img id="previewImg" src="" class="img-fluid rounded-3 d-none" style="max-height: 220px; object-fit: contain;">
                                <div id="emptyPreviewText" class="text-muted">
                                    <i class="bi bi-camera-fill fs-1 d-block mb-1 text-secondary"></i>
                                    <span>Pilih gambar atau jepret langsung dari kamera HP</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Pilih Berkas Foto / Buka Kamera HP</label>
                        <input type="file" name="foto_kelas" id="fileFotoInput" class="form-control" accept="image/*" capture="environment" onchange="previewFile(this)" required>
                        <div class="form-text text-muted small">Di smartphone, tombol ini otomatis membuka kamera belakang atau galeri foto.</div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary-custom rounded-pill px-4">
                        <i class="bi bi-cloud-arrow-up-fill me-1"></i> Unggah Foto
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Bagikan Link ke WhatsApp Orang Tua -->
<div class="modal fade" id="modalShareWA" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content card-custom border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2">
                    <i class="bi bi-whatsapp text-success"></i>
                    <span>Bagikan Laporan Presensi ke Grup WA Orang Tua</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body py-4">
                <div class="alert alert-success bg-opacity-10 border-0 rounded-3 small mb-3 text-light">
                    <i class="bi bi-info-circle-fill text-success me-1"></i> Pesan ini telah disusun otomatis dengan format sopan dan rapi, berisi rekap kehadiran serta tautan live untuk orang tua.
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold text-secondary">Tinjauan Pesan WhatsApp:</label>
                    <textarea id="textPesanWA" class="form-control font-monospace small bg-dark text-light border-secondary" rows="9" readonly></textarea>
                </div>
            </div>
            <div class="modal-footer border-0 pt-0 d-flex justify-content-between">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Tutup</button>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-outline-info rounded-pill px-3" onclick="salinTeksWA()">
                        <i class="bi bi-clipboard me-1"></i> Salin Pesan
                    </button>
                    <button type="button" class="btn btn-success rounded-pill px-3 fw-semibold" onclick="kirimLangsungWA()">
                        <i class="bi bi-whatsapp me-1"></i> Buka WhatsApp
                    </button>
                </div>
            </div>
        </div>
    </div>
<!-- Modal Konfirmasi Kosongkan Absensi -->
<div class="modal fade" id="modalKosongkanAbsen" tabindex="-1" aria-labelledby="modalKosongkanAbsenLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content card-custom border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-danger bg-opacity-25 border-bottom border-danger border-opacity-25 py-3">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2" id="modalKosongkanAbsenLabel">
                    <i class="bi bi-trash3-fill text-danger"></i>
                    <span>Kosongkan Data Presensi Kelas</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= site_url('pj/absen/hapus') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="tanggal" value="<?= esc($tanggal) ?>">
                
                <div class="modal-body p-4">
                    <div class="alert alert-danger bg-danger bg-opacity-10 border border-danger border-opacity-25 rounded-3 mb-3 d-flex align-items-start gap-2">
                        <i class="bi bi-exclamation-triangle-fill text-danger fs-5 mt-1 flex-shrink-0"></i>
                        <div class="small text-light">
                            Tindakan ini akan <strong>menghapus dan mengosongkan seluruh data presensi siswa kelas <?= esc($kelas['nama_kelas']) ?></strong> untuk tanggal ini dari sistem.
                        </div>
                    </div>

                    <div class="card bg-dark border-secondary p-3 mb-3 rounded-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Kelas:</span>
                            <span class="fw-bold text-white"><?= esc($kelas['nama_kelas']) ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Tanggal Presensi:</span>
                            <span class="fw-bold text-danger"><?= date('d F Y', strtotime($tanggal)) ?></span>
                        </div>
                        <div class="d-flex justify-content-between">
                            <span class="text-muted small">Total Siswa Terdaftar:</span>
                            <span class="fw-bold text-white"><?= count($daftarSiswa) ?> Siswa</span>
                        </div>
                    </div>

                    <p class="small text-muted mb-0">
                        Setelah dikosongkan, status kehadiran seluruh siswa akan kembali menjadi <strong>Belum Diisi (Kosong)</strong>, tidak akan tercatat hadir, sakit, izin, maupun alpa pada rekapitulasi, dan foto kegiatan kelas pada tanggal ini juga akan dibersihkan.
                    </p>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger fw-bold rounded-pill px-4 shadow">
                        <i class="bi bi-trash3 me-1"></i> Ya, Kosongkan Presensi
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<!-- Flatpickr CSS & JS (CDN) -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/id.js"></script>

<style>
/* Styling Flatpickr Dark Emerald / Hijau Tua Theme */
.flatpickr-calendar {
    background: #08281b !important;
    border: 1px solid rgba(52, 211, 153, 0.25) !important;
    box-shadow: 0 15px 35px rgba(0, 0, 0, 0.7) !important;
    border-radius: 16px !important;
    overflow: hidden;
    color: #f0fdf4 !important;
    font-family: 'Plus Jakarta Sans', sans-serif !important;
}
.flatpickr-calendar .flatpickr-months {
    background: #0c3323 !important;
    border-bottom: 1px solid rgba(52, 211, 153, 0.15);
    padding: 6px 0;
}
.flatpickr-calendar .flatpickr-month,
.flatpickr-calendar .flatpickr-current-month,
.flatpickr-calendar .flatpickr-current-month .cur-month,
.flatpickr-calendar .flatpickr-current-month input.cur-year {
    color: #f0fdf4 !important;
    font-weight: 700;
}
.flatpickr-calendar span.flatpickr-weekday {
    color: #6ee7b7 !important;
    font-weight: 600;
}
.flatpickr-calendar .flatpickr-prev-month svg,
.flatpickr-calendar .flatpickr-next-month svg {
    fill: #10b981 !important;
}
.flatpickr-day {
    color: #f0fdf4 !important;
    border-radius: 8px !important;
    height: 36px !important;
    line-height: 36px !important;
    position: relative;
    font-size: 13px;
    font-weight: 500;
}
.flatpickr-day:hover {
    background: rgba(16, 185, 129, 0.25) !important;
}
.flatpickr-day.selected {
    background: #10b981 !important;
    border-color: #10b981 !important;
    color: #ffffff !important;
    font-weight: 800 !important;
    box-shadow: 0 0 10px rgba(16, 185, 129, 0.6) !important;
}

/* 🔴 Hari Libur & Hari Minggu (Merah) */
.flatpickr-day.fp-day-libur {
    color: #f87171 !important;
    font-weight: 700 !important;
    background-color: rgba(239, 68, 68, 0.18) !important;
    border: 1px dashed rgba(239, 68, 68, 0.5) !important;
}
.flatpickr-day.fp-day-libur::after {
    content: '';
    position: absolute;
    bottom: 3px;
    left: 50%;
    transform: translateX(-50%);
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background-color: #ef4444;
}

/* 🟡 Hari Acara / Agenda Kegiatan (Kuning) */
.flatpickr-day.fp-day-acara {
    color: #fbbf24 !important;
    font-weight: 700 !important;
    background-color: rgba(245, 158, 11, 0.18) !important;
    border: 1px dashed rgba(245, 158, 11, 0.5) !important;
}
.flatpickr-day.fp-day-acara::after {
    content: '';
    position: absolute;
    bottom: 3px;
    left: 50%;
    transform: translateX(-50%);
    width: 5px;
    height: 5px;
    border-radius: 50%;
    background-color: #f59e0b;
}

/* 🟢 Hari Aktif Belajar (Hijau dot subtle) */
.flatpickr-day:not(.flatpickr-disabled):not(.prevMonthDay):not(.nextMonthDay):not(.fp-day-libur):not(.fp-day-acara)::after {
    content: '';
    position: absolute;
    bottom: 3px;
    left: 50%;
    transform: translateX(-50%);
    width: 4px;
    height: 4px;
    border-radius: 50%;
    background-color: #10b981;
    opacity: 0.7;
}
</style>

<script>
    const kalenderEvents = <?= json_encode($kalenderEvents ?? []) ?>;

    // Inisialisasi Flatpickr Interaktif
    const fpTanggal = flatpickr("#datepicker-interaktif", {
        locale: "id",
        dateFormat: "Y-m-d",
        defaultDate: "<?= esc($tanggal) ?>",
        maxDate: "<?= date('Y-m-d', strtotime('+30 days')) ?>",
        onDayCreate: function(dObj, dStr, fp, dayElem) {
            const dateStr = fp.formatDate(dayElem.dateObj, "Y-m-d");
            const dayOfWeek = dayElem.dateObj.getDay(); // 0 is Sunday

            // 1. Hari Minggu -> Merah
            if (dayOfWeek === 0) {
                dayElem.classList.add('fp-day-libur', 'fp-day-minggu');
                dayElem.title = "Hari Minggu (Libur)";
                return;
            }

            // 2. Cek apakah ada agenda di kalender
            const event = kalenderEvents.find(e => dateStr >= e.tanggal_mulai && dateStr <= e.tanggal_selesai);
            if (event) {
                if (event.tipe === 'libur' || event.keterangan_kbm === 'bebas_absen') {
                    dayElem.classList.add('fp-day-libur');
                    dayElem.title = "Libur: " + event.nama_agenda;
                } else {
                    dayElem.classList.add('fp-day-acara');
                    dayElem.title = "Acara: " + event.nama_agenda;
                }
            }
        },
        onChange: function(selectedDates, dateStr, instance) {
            if (dateStr) {
                window.location.href = "<?= site_url('pj') ?>?tanggal=" + dateStr;
            }
        }
    });

    document.getElementById('btnBukaKalender')?.addEventListener('click', function() {
        fpTanggal.open();
    });

    function bukaModalKosongkanAbsen() {
        var modal = new bootstrap.Modal(document.getElementById('modalKosongkanAbsen'));
        modal.show();
    }

    function setSemuaHadir() {
        document.querySelectorAll('.radio-h').forEach(function(radio) {
            radio.checked = true;
        });
    }

    // Validasi: semua siswa wajib punya status kehadiran sebelum simpan
    document.querySelector('form[action*="pj/simpan"]').addEventListener('submit', function(e) {
        if (<?= $isLocked ? 'true' : 'false' ?>) {
            e.preventDefault();
            alert('Presensi Ditolak: Hari ini adalah hari libur / acara sekolah. Presensi dikunci untuk siswa.');
            return false;
        }

        const rows = document.querySelectorAll('form[action*="pj/simpan"] tbody tr');
        let belumDiisi = [];

        rows.forEach(function(row) {
            const radios = row.querySelectorAll('input[type="radio"]');
            if (radios.length === 0) return; // skip baris kosong

            const ada = Array.from(radios).some(r => r.checked);
            if (!ada) {
                const namaSiswa = row.querySelector('td:nth-child(3)');
                if (namaSiswa) {
                    belumDiisi.push(namaSiswa.textContent.trim());
                    row.style.outline = '2px solid #dc3545';
                    row.style.outlineOffset = '-2px';
                    row.style.borderRadius = '8px';
                }
            } else {
                row.style.outline = 'none';
            }
        });

        if (belumDiisi.length > 0) {
            e.preventDefault();
            const maxShow = 5;
            let pesan = 'Status kehadiran belum dipilih untuk:\n';
            pesan += belumDiisi.slice(0, maxShow).map((n, i) => (i + 1) + '. ' + n).join('\n');
            if (belumDiisi.length > maxShow) {
                pesan += '\n...dan ' + (belumDiisi.length - maxShow) + ' siswa lainnya.';
            }
            pesan += '\n\nSilakan pilih status kehadiran (H/S/I/A) untuk semua siswa sebelum menyimpan.';
            alert(pesan);

            // Scroll ke baris pertama yang belum diisi
            const firstEmpty = document.querySelector('form[action*="pj/simpan"] tbody tr[style*="outline"]');
            if (firstEmpty) firstEmpty.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });

    function previewFile(input) {
        const file = input.files[0];
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById('previewImg');
                img.src = e.target.result;
                img.classList.remove('d-none');
                const emptyText = document.getElementById('emptyPreviewText');
                if (emptyText) emptyText.classList.add('d-none');
            }
            reader.readAsDataURL(file);
        }
    }

    function salinLinkLive() {
        const urlInput = document.getElementById('liveShareUrl');
        navigator.clipboard.writeText(urlInput.value).then(() => {
            alert("Tautan live presensi orang tua berhasil disalin ke clipboard!");
        }).catch(() => {
            prompt("Salin link live presensi:", urlInput.value);
        });
    }

    function getFormattedPesanWA() {
        const kelas = "<?= esc($kelas['nama_kelas']) ?>";
        const tglIndo = "<?= date('d F Y', strtotime($tanggal)) ?>";
        const h = "<?= $countH ?>";
        const s = "<?= $countS ?>";
        const i = "<?= $countI ?>";
        const a = "<?= $countA ?>";
        const walas = "<?= esc($namaWalasStr) ?>";
        const url = "<?= esc($liveUrl) ?>";

        return `Assalamu'alaikum Warahmatullahi Wabarakatuh,\n` +
               `Yth. Bapak/Ibu Orang Tua / Wali Murid Kelas ${kelas},\n\n` +
               `Berikut kami laporkan pembaruan status kehadiran siswa dan suasana kelas pada:\n` +
               `📅 Hari/Tanggal: ${tglIndo}\n` +
               `📊 Rekap Kehadiran: Hadir (${h}) | Sakit (${s}) | Izin (${i}) | Alpha (${a})\n\n` +
               `📸 Untuk melihat dokumentasi foto suasana anak-anak di kelas hari ini serta rincian presensi kehadiran siswa secara realtime, silakan buka tautan resmi berikut:\n` +
               `👉 ${url}\n\n` +
               `Terima kasih atas perhatian dan kerjasamanya.\nWassalamu'alaikum Wr. Wb.\n` +
               `- Perwakilan Kelas & Wali Kelas (${walas})`;
    }

    function bukaModalWA() {
        document.getElementById('textPesanWA').value = getFormattedPesanWA();
        const modal = new bootstrap.Modal(document.getElementById('modalShareWA'));
        modal.show();
    }

    function salinTeksWA() {
        const text = document.getElementById('textPesanWA').value;
        navigator.clipboard.writeText(text).then(() => {
            alert("Pesan WhatsApp berhasil disalin! Silakan paste ke grup WhatsApp Orang Tua Murid.");
        }).catch(() => {
            prompt("Salin teks di bawah:", text);
        });
    }

    function kirimLangsungWA() {
        const text = document.getElementById('textPesanWA').value;
        const encoded = encodeURIComponent(text);
        window.open(`https://api.whatsapp.com/send?text=${encoded}`, '_blank');
    }
</script>
<?= $this->endSection() ?>
