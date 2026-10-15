<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<?php
    $isPastDate = (strtotime($tanggal) < strtotime(date('Y-m-d')));
    $yesterday  = date('Y-m-d', strtotime('-1 day'));
    $twoDaysAgo = date('Y-m-d', strtotime('-2 days'));
?>

<div class="row mb-4 align-items-center">
    <div class="col-12 col-md-6">
        <h3 class="fw-bold text-dark mb-1">
            <i class="bi bi-clipboard-check text-primary me-2"></i>Input & Edit Presensi Kelas <?= esc($kelas['nama_kelas']) ?>
        </h3>
        <p class="text-muted small mb-0">Kelola dan perbarui presensi harian maupun presensi susulan yang kelewat untuk siswa binaan Anda.</p>
    </div>
    <div class="col-12 col-md-6 mt-3 mt-md-0">
        <!-- Interactive Date Selector Filter -->
        <div class="d-flex flex-column align-items-md-end gap-2">
            <form id="formPilihTanggal" action="<?= site_url('walas/absen') ?>" method="GET" class="d-flex align-items-center justify-content-md-end flex-wrap gap-2">
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
                    <a href="<?= site_url('walas/absen?tanggal=' . date('Y-m-d')) ?>" class="btn btn-outline-secondary <?= $tanggal === date('Y-m-d') ? 'active' : '' ?>">Hari Ini</a>
                    <a href="<?= site_url('walas/absen?tanggal=' . $yesterday) ?>" class="btn btn-outline-secondary <?= $tanggal === $yesterday ? 'active' : '' ?>">Kemarin</a>
                    <a href="<?= site_url('walas/absen?tanggal=' . $twoDaysAgo) ?>" class="btn btn-outline-secondary <?= $tanggal === $twoDaysAgo ? 'active' : '' ?>">2 Hari Lalu</a>
                </div>
            </form>
            
            <!-- Calendar Legend -->
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
    <div class="alert alert-info border-0 shadow-sm rounded-3 d-flex align-items-center justify-content-between flex-wrap gap-2 mb-4" role="alert">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-info-circle-fill fs-4 text-info"></i>
            <div>
                <strong>Hari Minggu (Libur Akhir Pekan):</strong> Tanggal ini adalah hari Minggu sehingga kegiatan KBM dan presensi libur otomatis.
            </div>
        </div>
        <?php if (!empty($hasExistingRecords)): ?>
            <button type="button" class="btn btn-sm btn-danger rounded-pill px-3 fw-bold shadow-sm" onclick="bukaModalSetNull()">
                <i class="bi bi-trash3 me-1"></i> Hapus / Set Null Presensi Tanggal Ini
            </button>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php if (!empty($agendaBebasAbsen)): ?>
    <div class="alert alert-success border-0 shadow-sm rounded-3 d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4" role="alert">
        <div class="d-flex align-items-center gap-3">
            <i class="bi bi-calendar2-check fs-2 text-success"></i>
            <div>
                <strong class="text-dark fs-6">Hari Bebas Presensi: <?= esc($agendaBebasAbsen['nama_agenda']) ?></strong>
                <div class="small text-muted">
                    Kategori: <?= ucfirst($agendaBebasAbsen['tipe']) ?> &bull; Status KBM: Bebas Absen &bull; <?= esc($agendaBebasAbsen['keterangan'] ?? '') ?>
                </div>
                <div class="small text-success fw-semibold mt-1">
                    <i class="bi bi-check-circle me-1"></i> Siswa dibebaskan dari kewajiban presensi (auto null / tidak dihitung alfa).
                </div>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <?php if (!empty($hasExistingRecords)): ?>
                <button type="button" class="btn btn-sm btn-danger rounded-pill px-3 fw-bold shadow-sm" onclick="bukaModalSetNull()">
                    <i class="bi bi-trash3 me-1"></i> Hapus / Set Null Presensi
                </button>
            <?php endif; ?>
            <?php if (!empty($agendaBebasAbsen['target_kelas_id']) && (int)$agendaBebasAbsen['target_kelas_id'] === (int)$kelas['id']): ?>
                <form action="<?= site_url('walas/batalkan-libur') ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan keterangan libur ini?');" class="m-0">
                    <?= csrf_field() ?>
                    <input type="hidden" name="agenda_id" value="<?= $agendaBebasAbsen['id'] ?>">
                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3 fw-semibold">
                        <i class="bi bi-x-circle me-1"></i> Batalkan Penandaan Libur
                    </button>
                </form>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php if ($isPastDate): ?>
    <div class="alert alert-warning border-0 shadow-sm rounded-3 d-flex align-items-center gap-2 mb-4" role="alert">
        <i class="bi bi-clock-history fs-4 text-warning"></i>
        <div>
            <strong>Presensi Susulan / Kelewat:</strong> Anda sedang menginput/mengubah data presensi untuk tanggal <strong><?= date('d F Y', strtotime($tanggal)) ?></strong>.
        </div>
    </div>
<?php endif; ?>

<form action="<?= site_url('walas/absen/simpan') ?>" method="POST">
    <?= csrf_field() ?>
    <input type="hidden" name="tanggal" value="<?= esc($tanggal) ?>">

    <div class="card card-custom border-0 mb-4">
        <div class="card-header bg-white border-0 py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary px-3 py-2 fs-6 rounded-pill">
                    <i class="bi bi-calendar-date me-1"></i> <?= date('d F Y', strtotime($tanggal)) ?>
                </span>
                <span class="badge bg-light text-muted border px-2 py-2">
                    Total Siswa: <?= count($daftarSiswa) ?> Orang
                </span>
                <?php if (!empty($hasExistingRecords)): ?>
                    <span class="badge bg-success bg-opacity-15 text-success border border-success border-opacity-25 px-2 py-2">
                        <i class="bi bi-check-circle me-1"></i> Sudah Terisi
                    </span>
                <?php else: ?>
                    <span class="badge bg-secondary bg-opacity-15 text-muted border border-secondary border-opacity-25 px-2 py-2">
                        <i class="bi bi-dash-circle me-1"></i> Belum Diisi (Null)
                    </span>
                <?php endif; ?>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button type="button" class="btn btn-sm btn-outline-success fw-semibold rounded-pill px-3" onclick="setSemuaHadir()">
                    <i class="bi bi-check-all me-1"></i> Set Semua Hadir (H)
                </button>
                <?php if (empty($agendaBebasAbsen) && !$isSunday): ?>
                    <button type="button" class="btn btn-sm btn-outline-warning text-dark border-warning fw-semibold rounded-pill px-3" onclick="bukaModalTandaiLibur('<?= $tanggal ?>', '<?= date('d F Y', strtotime($tanggal)) ?>')">
                        <i class="bi bi-calendar-event me-1"></i> Tandai Libur / Acara
                    </button>
                <?php endif; ?>
                <?php if (!empty($hasExistingRecords)): ?>
                    <button type="button" class="btn btn-sm btn-outline-danger fw-semibold rounded-pill px-3 shadow-sm" onclick="bukaModalSetNull()">
                        <i class="bi bi-trash3 me-1"></i> Hapus / Set Null Presensi
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
                                <td><span class="badge bg-light text-dark border font-monospace"><?= esc($s['nisn']) ?></span></td>
                                <td class="fw-bold text-dark"><?= esc($s['nama_siswa']) ?></td>
                                <td class="text-center">
                                    <?php $currentStatus = $s['absensi_id'] ? $s['status'] : null; ?>
                                    <div class="d-inline-flex gap-2">
                                        <!-- Hadir -->
                                        <label>
                                            <input type="radio" class="status-pill-radio radio-h" name="absensi[<?= $s['siswa_id'] ?>][status]" value="H" <?= $currentStatus === 'H' ? 'checked' : '' ?>>
                                            <span class="status-label" title="Hadir">H</span>
                                        </label>
                                        <!-- Sakit -->
                                        <label>
                                            <input type="radio" class="status-pill-radio" name="absensi[<?= $s['siswa_id'] ?>][status]" value="S" <?= $currentStatus === 'S' ? 'checked' : '' ?>>
                                            <span class="status-label" title="Sakit">S</span>
                                        </label>
                                        <!-- Izin -->
                                        <label>
                                            <input type="radio" class="status-pill-radio" name="absensi[<?= $s['siswa_id'] ?>][status]" value="I" <?= $currentStatus === 'I' ? 'checked' : '' ?>>
                                            <span class="status-label" title="Izin">I</span>
                                        </label>
                                        <!-- Alpa -->
                                        <label>
                                            <input type="radio" class="status-pill-radio" name="absensi[<?= $s['siswa_id'] ?>][status]" value="A" <?= $currentStatus === 'A' ? 'checked' : '' ?>>
                                            <span class="status-label" title="Alpa / Tanpa Keterangan">A</span>
                                        </label>
                                        <?php if (! empty($isKelas2)): ?>
                                        <!-- PKL (Khusus Kelas 2 / XI) -->
                                        <label>
                                            <input type="radio" class="status-pill-radio" name="absensi[<?= $s['siswa_id'] ?>][status]" value="P" <?= $currentStatus === 'P' ? 'checked' : '' ?>>
                                            <span class="status-label" title="Praktik Kerja Lapangan (PKL / Magang)">P</span>
                                        </label>
                                        <?php endif; ?>
                                    </div>
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm bg-light" name="absensi[<?= $s['siswa_id'] ?>][keterangan]" value="<?= esc($s['keterangan'] ?? '') ?>" placeholder="Misal: Demam, Izin dinas...">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (! empty($daftarSiswa)): ?>
            <div class="card-footer bg-white border-0 py-3 text-end">
                <button type="submit" class="btn btn-primary-custom px-4 py-2">
                    <i class="bi bi-cloud-check-fill me-2"></i> Simpan Data Presensi
                </button>
            </div>
        <?php endif; ?>
    </div>
</form>

<!-- Modal Walas Tandai Libur / Acara Khusus -->
<div class="modal fade" id="modalTandaiLibur" tabindex="-1" aria-labelledby="modalTandaiLiburLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-warning bg-opacity-25 border-bottom border-warning py-3">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="modalTandaiLiburLabel">
                    <i class="bi bi-calendar2-check-fill text-warning"></i>
                    Tandai Hari Libur / Acara Non-KBM
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= site_url('walas/tandai-libur') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="tanggal" id="libur_tanggal" value="">
                
                <div class="modal-body p-4">
                    <div class="alert alert-warning bg-opacity-10 border-0 rounded-3 mb-3 d-flex align-items-start gap-2">
                        <i class="bi bi-info-circle-fill text-warning fs-5 mt-1"></i>
                        <div class="small text-muted">
                            Fitur ini digunakan jika ada kegiatan/libur (misal: <strong>Maulid Nabi, Class Meeting, HUT Sekolah, Hari Libur Bersama</strong>) yang belum tercatat oleh Admin di Kalender Pendidikan. Presensi pada tanggal ini akan dinonaktifkan (bebas absen / auto null) dan notifikasi kelewat akan langsung hilang.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Tanggal Terpilih</label>
                        <input type="text" class="form-control bg-light fw-bold text-primary" id="libur_tanggal_formatted" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="nama_agenda" class="form-label fw-bold text-dark small">Nama Agenda / Keterangan Libur <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_agenda" name="nama_agenda" placeholder="Contoh: Libur Acara Maulid Nabi Muhammad SAW" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Kategori Kegiatan <span class="text-danger">*</span></label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipe" id="tipe_libur" value="libur" checked>
                                <label class="form-check-label small fw-semibold" for="tipe_libur">
                                    <i class="bi bi-calendar-x text-danger me-1"></i> Hari Libur / Bebas KBM
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipe" id="tipe_acara" value="acara">
                                <label class="form-check-label small fw-semibold" for="tipe_acara">
                                    <i class="bi bi-trophy text-primary me-1"></i> Acara / Kegiatan Sekolah
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Cakupan Keterangan</label>
                        <select class="form-select" name="cakupan">
                            <option value="kelas" selected>Hanya Berlaku Untuk Kelas Ini (<?= esc($kelas['nama_kelas'] ?? 'Kelas Saya') ?>)</option>
                            <option value="tingkat">Berlaku Untuk Seluruh Kelas Se-Tingkat</option>
                        </select>
                        <div class="form-text small">Pilih "Hanya Berlaku Untuk Kelas Ini" agar tidak memengaruhi kelas lain, atau se-tingkat jika seluruh angkatan memiliki kegiatan serupa.</div>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold rounded-pill px-4 shadow-sm">
                        <i class="bi bi-check2-circle me-1"></i> Simpan & Bebaskan Absen
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Hapus / Set Null Presensi -->
<div class="modal fade" id="modalSetNull" tabindex="-1" aria-labelledby="modalSetNullLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content card-custom border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-danger bg-opacity-25 border-bottom border-danger border-opacity-25 py-3">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2" id="modalSetNullLabel">
                    <i class="bi bi-trash3-fill text-danger"></i>
                    <span>Hapus / Set Null Presensi Kelas</span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= site_url('walas/absen/hapus') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="tanggal" value="<?= esc($tanggal) ?>">
                
                <div class="modal-body p-4">
                    <div class="alert alert-danger bg-danger bg-opacity-10 border border-danger border-opacity-25 rounded-3 mb-3 d-flex align-items-start gap-2">
                        <i class="bi bi-exclamation-triangle-fill text-danger fs-5 mt-1 flex-shrink-0"></i>
                        <div class="small text-light">
                            Tindakan ini akan <strong>menghapus seluruh record kehadiran siswa kelas <?= esc($kelas['nama_kelas']) ?></strong> untuk tanggal ini dari database.
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
                        Setelah dihapus, status presensi siswa akan kembali menjadi <strong>NULL (kosong / belum diisi)</strong>, tidak akan dihitung hadir maupun alpa pada rekapitulasi, dan dokumentasi foto sesi kelas pada tanggal ini juga akan dibersihkan.
                    </p>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger fw-bold rounded-pill px-4 shadow">
                        <i class="bi bi-trash3 me-1"></i> Ya, Hapus & Set Null
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

.flatpickr-day.fp-day-libur.selected,
.flatpickr-day.fp-day-acara.selected {
    background: #10b981 !important;
    color: #ffffff !important;
    border: 1px solid #10b981 !important;
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
                window.location.href = "<?= site_url('walas/absen') ?>?tanggal=" + dateStr;
            }
        }
    });

    document.getElementById('btnBukaKalender')?.addEventListener('click', function() {
        fpTanggal.open();
    });

    function setSemuaHadir() {
        document.querySelectorAll('.radio-h').forEach(function(radio) {
            radio.checked = true;
        });
    }

    function bukaModalTandaiLibur(tanggal, formatted) {
        document.getElementById('libur_tanggal').value = tanggal;
        document.getElementById('libur_tanggal_formatted').value = formatted;
        document.getElementById('nama_agenda').value = '';
        var modal = new bootstrap.Modal(document.getElementById('modalTandaiLibur'));
        modal.show();
    }

    function bukaModalSetNull() {
        var modal = new bootstrap.Modal(document.getElementById('modalSetNull'));
        modal.show();
    }

    // Validasi: semua siswa wajib punya status kehadiran sebelum simpan
    document.querySelector('form[action*="walas/absen/simpan"]').addEventListener('submit', function(e) {
        const rows = document.querySelectorAll('tbody tr');
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
            const firstEmpty = document.querySelector('tbody tr[style*="outline"]');
            if (firstEmpty) firstEmpty.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    });
</script>
<?= $this->endSection() ?>
