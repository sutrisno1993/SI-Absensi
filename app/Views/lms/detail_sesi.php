<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="row align-items-center mb-4">
    <div class="col-12 col-md-8">
        <a href="<?= site_url('lms') ?>" class="text-secondary small text-decoration-none d-inline-flex align-items-center mb-1">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard KBM
        </a>
        <h3 class="fw-bold text-dark mb-1">
            <i class="bi bi-book text-primary me-2"></i><?= esc($sesi['judul_materi']) ?>
        </h3>
        <div class="d-flex flex-wrap align-items-center gap-3 text-muted small">
            <span><i class="bi bi-door-open me-1 text-primary"></i>Kelas <strong><?= esc($sesi['nama_kelas']) ?></strong></span>
            <span><i class="bi bi-journal-bookmark me-1 text-info"></i>Mapel: <strong><?= esc($sesi['nama_mapel']) ?></strong></span>
            <span><i class="bi bi-calendar3 me-1 text-secondary"></i><?= date('l, d F Y', strtotime($sesi['tanggal'])) ?></span>
            <span><i class="bi bi-person-badge me-1 text-warning"></i>Guru: <?= esc($sesi['nama_guru']) ?></span>
        </div>
    </div>
    <div class="col-12 col-md-4 text-md-end mt-3 mt-md-0">
        <?php if ($sesi['status'] === 'berlangsung'): ?>
            <span class="badge bg-success bg-opacity-15 text-success border border-success px-3 py-2 rounded-pill fs-6">
                <i class="bi bi-broadcast me-1"></i> KBM Sedang Berlangsung (Live)
            </span>
            <form action="<?= site_url('lms/sesi/' . $sesi['id'] . '/selesaikan') ?>" method="POST" class="d-inline ms-2" onsubmit="return confirm('Tandai pertemuan KBM ini telah selesai?')">
                <?= csrf_field() ?>
                <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1">Selesaikan Sesi</button>
            </form>
        <?php elseif ($sesi['status'] === 'selesai'): ?>
            <span class="badge bg-secondary bg-opacity-15 text-muted border border-secondary px-3 py-2 rounded-pill fs-6">
                <i class="bi bi-check2-all me-1"></i> KBM Selesai
            </span>
        <?php else: ?>
            <span class="badge bg-warning bg-opacity-15 text-warning border border-warning px-3 py-2 rounded-pill fs-6">
                Status: Draft
            </span>
        <?php endif; ?>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Kolom Kiri: Presensi Siswa Khusus Jam Mapel Ini -->
    <div class="col-12 col-xl-7">
        <div class="card card-custom border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold text-dark mb-0">
                        <i class="bi bi-person-check-fill text-success me-2"></i>Presensi Siswa Jam Pelajaran
                    </h5>
                    <small class="text-muted">Total: <?= count($presensiList) ?> Siswa di Kelas <?= esc($sesi['nama_kelas']) ?></small>
                </div>
                <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="tandaiSemuaHadir()">
                    <i class="bi bi-check-all me-1"></i> Set Semua Hadir
                </button>
            </div>

            <form action="<?= site_url('lms/sesi/' . $sesi['id'] . '/presensi') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="table-responsive" style="max-height: 480px;">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light sticky-top">
                            <tr>
                                <th class="text-center" style="width: 40px;">No</th>
                                <th>Nama Lengkap Siswa</th>
                                <th class="text-center" style="width: 200px;">Status Presensi</th>
                                <th style="width: 140px;">Catatan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($presensiList as $idx => $p): ?>
                                <tr>
                                    <td class="text-center text-muted small"><?= $idx + 1 ?></td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= esc($p['nama_siswa']) ?></div>
                                        <small class="text-muted font-monospace"><?= esc($p['nisn']) ?></small>
                                    </td>
                                    <td class="text-center">
                                        <div class="btn-group btn-group-sm" role="group">
                                            <input type="radio" class="btn-check radio-status-h" name="presensi[<?= $p['siswa_id'] ?>][status]" id="p_h_<?= $p['siswa_id'] ?>" value="H" <?= ($p['status'] === 'H') ? 'checked' : '' ?>>
                                            <label class="btn btn-outline-success fw-bold px-2" for="p_h_<?= $p['siswa_id'] ?>">H</label>

                                            <input type="radio" class="btn-check" name="presensi[<?= $p['siswa_id'] ?>][status]" id="p_s_<?= $p['siswa_id'] ?>" value="S" <?= ($p['status'] === 'S') ? 'checked' : '' ?>>
                                            <label class="btn btn-outline-warning fw-bold px-2" for="p_s_<?= $p['siswa_id'] ?>">S</label>

                                            <input type="radio" class="btn-check" name="presensi[<?= $p['siswa_id'] ?>][status]" id="p_i_<?= $p['siswa_id'] ?>" value="I" <?= ($p['status'] === 'I') ? 'checked' : '' ?>>
                                            <label class="btn btn-outline-info fw-bold px-2" for="p_i_<?= $p['siswa_id'] ?>">I</label>

                                            <input type="radio" class="btn-check" name="presensi[<?= $p['siswa_id'] ?>][status]" id="p_a_<?= $p['siswa_id'] ?>" value="A" <?= ($p['status'] === 'A') ? 'checked' : '' ?>>
                                            <label class="btn btn-outline-danger fw-bold px-2" for="p_a_<?= $p['siswa_id'] ?>">A</label>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control form-control-sm" name="presensi[<?= $p['siswa_id'] ?>][catatan]" value="<?= esc($p['catatan'] ?? '') ?>" placeholder="Keterangan...">
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="card-footer bg-white border-0 py-3 text-end">
                    <button type="submit" class="btn btn-primary-custom rounded-pill px-4 fw-semibold shadow-sm">
                        <i class="bi bi-save me-1"></i> Simpan Presensi &amp; Mulai KBM
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Kolom Kanan: Tab Interaktif KBM (Asesmen Live & LKPD Kelompok) -->
    <div class="col-12 col-xl-5">
        <div class="card card-custom border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 pt-3 pb-2">
                <ul class="nav nav-pills nav-fill p-1 bg-light rounded-pill" id="lmsTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active rounded-pill fw-semibold py-2" id="asesmen-tab" data-bs-toggle="tab" data-bs-target="#tabAsesmen" type="button" role="tab">
                            <i class="bi bi-ui-checks me-1"></i> Asesmen Live
                            <span class="badge bg-primary rounded-pill ms-1"><?= count($asesmenList) ?></span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill fw-semibold py-2" id="lkpd-tab" data-bs-toggle="tab" data-bs-target="#tabLkpd" type="button" role="tab">
                            <i class="bi bi-people-fill me-1"></i> LKPD Kelompok
                            <span class="badge bg-success rounded-pill ms-1"><?= count($lkpdList) ?></span>
                        </button>
                    </li>
                </ul>
            </div>

            <div class="card-body p-3">
                <div class="tab-content" id="lmsTabContent">
                    <!-- Tab Pane 1: Asesmen & Kuis Live -->
                    <div class="tab-pane fade show active" id="tabAsesmen" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <small class="text-muted fw-semibold">Diagnostik, Formatif &amp; Kuis Interaktif</small>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalBuatAsesmen">
                                <i class="bi bi-plus-circle me-1"></i> Buat Kuis
                            </button>
                        </div>

                        <?php if (empty($asesmenList)): ?>
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-lightning-charge fs-1 d-block mb-2 text-warning"></i>
                                <h6 class="fw-bold text-dark mb-1">Belum Ada Asesmen Live</h6>
                                <p class="small text-muted mb-3">Luncurkan pertanyaan live formatif atau diagnostik untuk mengukur pemahaman siswa selama materi KBM berlangsung.</p>
                                <button type="button" class="btn btn-sm btn-primary-custom rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalBuatAsesmen">
                                    <i class="bi bi-plus-circle me-1"></i> Buat Kuis Sekarang
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($asesmenList as $a): ?>
                                    <div class="list-group-item p-3 border rounded-3 mb-2 bg-light">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <span class="badge bg-primary bg-opacity-15 text-primary text-uppercase px-2 py-1 rounded-pill mb-1">
                                                    <?= esc($a['tipe']) ?>
                                                </span>
                                                <h6 class="fw-bold text-dark mb-0"><?= esc($a['judul_asesmen']) ?></h6>
                                                <small class="text-muted"><i class="bi bi-clock me-1"></i>Durasi: <?= $a['durasi_menit'] ?> Menit</small>
                                            </div>
                                            <span class="badge <?= $a['status'] === 'aktif' ? 'bg-success' : 'bg-secondary' ?> rounded-pill">
                                                <?= strtoupper($a['status']) ?>
                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                                            <div class="small text-muted">
                                                <i class="bi bi-qr-code me-1 text-primary"></i> Kode: <strong><?= esc($a['token_live']) ?></strong>
                                            </div>
                                            <a href="<?= site_url('lms/asesmen/' . $a['id']) ?>" class="btn btn-sm btn-primary-custom rounded-pill px-3 py-1">
                                                <i class="bi bi-broadcast me-1"></i> Buka Papan Live &rarr;
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Tab Pane 2: LKPD Kelompok -->
                    <div class="tab-pane fade" id="tabLkpd" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <small class="text-muted fw-semibold">Lembar Kerja Kelompok Praktik &amp; Proyek</small>
                            <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalBuatLkpd">
                                <i class="bi bi-plus-circle me-1"></i> Buat LKPD Baru
                            </button>
                        </div>

                        <?php if (empty($lkpdList)): ?>
                            <div class="text-center py-5 text-muted">
                                <i class="bi bi-diagram-3 fs-1 d-block mb-2 text-success"></i>
                                <h6 class="fw-bold text-dark mb-1">Belum Ada LKPD Kelompok</h6>
                                <p class="small text-muted mb-3">Buat lembar kerja kolaboratif digital. Siswa dapat dibagi kelompok otomatis atau mandiri, mengunggah bukti praktik, dan dinilai bersama.</p>
                                <button type="button" class="btn btn-sm btn-success rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalBuatLkpd">
                                    <i class="bi bi-plus-circle me-1"></i> Buat LKPD Sekarang
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="list-group list-group-flush">
                                <?php foreach ($lkpdList as $lk): ?>
                                    <div class="list-group-item p-3 border rounded-3 mb-2 bg-light">
                                        <div class="d-flex justify-content-between align-items-start mb-2">
                                            <div>
                                                <span class="badge bg-success bg-opacity-15 text-success text-uppercase px-2 py-1 rounded-pill mb-1">
                                                    <?= $lk['metode_kelompok'] === 'acak' ? 'Kelompok Acak' : ($lk['metode_kelompok'] === 'manual_guru' ? 'Kelompok Manual' : 'Kelompok Mandiri') ?>
                                                </span>
                                                <h6 class="fw-bold text-dark mb-0"><?= esc($lk['judul_lkpd']) ?></h6>
                                                <?php if (!empty($lk['tenggat_waktu'])): ?>
                                                    <small class="text-danger"><i class="bi bi-alarm me-1"></i>Deadline: <?= date('d M Y, H:i', strtotime($lk['tenggat_waktu'])) ?></small>
                                                <?php endif; ?>
                                            </div>
                                            <span class="badge <?= $lk['status'] === 'aktif' ? 'bg-success' : 'bg-secondary' ?> rounded-pill">
                                                <?= strtoupper($lk['status']) ?>
                                            </span>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                                            <div class="small text-muted">
                                                <i class="bi bi-people me-1 text-success"></i> LKPD Kolaboratif
                                            </div>
                                            <a href="<?= site_url('lms/lkpd/' . $lk['id']) ?>" class="btn btn-sm btn-success rounded-pill px-3 py-1">
                                                <i class="bi bi-kanban me-1"></i> Kelola &amp; Nilai &rarr;
                                            </a>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Buat Asesmen Baru / Import JSON -->
<div class="modal fade" id="modalBuatAsesmen" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-primary text-white rounded-3 p-2">
                        <i class="bi bi-lightning-charge-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0">Buat Asesmen Live Baru</h5>
                        <small class="text-muted">KBM: <?= esc($sesi['judul_materi']) ?></small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= site_url('lms/sesi/' . $sesi['id'] . '/asesmen') ?>" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Judul Asesmen / Kuis <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="judul_asesmen" required placeholder="Contoh: Formatif Siklus DORA &amp; DHCP Server">
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-7">
                            <label class="form-label small fw-semibold text-secondary">Tipe Asesmen</label>
                            <select class="form-select" name="tipe" required>
                                <option value="formatif">Asesmen Formatif (Kuis Materi)</option>
                                <option value="diagnostik">Asesmen Diagnostik (Awal KBM)</option>
                                <option value="kuis_live">Kuis Cepat / Rebutan Live</option>
                                <option value="refleksi">Kuesioner / Refleksi Belajar</option>
                            </select>
                        </div>
                        <div class="col-5">
                            <label class="form-label small fw-semibold text-secondary">Durasi (Menit)</label>
                            <input type="number" class="form-control" name="durasi_menit" value="30" min="5" max="180">
                        </div>
                    </div>

                    <!-- Import Soal dari JSON (Paste Text atau Upload File) -->
                    <div class="p-3 bg-light rounded-3 border mb-2">
                        <label class="form-label small fw-bold text-dark mb-2 d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-file-earmark-code-fill text-success me-1"></i> Masukkan Paket Soal (Opsional)</span>
                            <button type="button" class="btn btn-xs btn-link text-decoration-none p-0" onclick="isiContohJson()">Lihat Contoh Format JSON</button>
                        </label>
                        
                        <ul class="nav nav-pills nav-fill mb-2" id="pillsTabJson" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active py-1 small rounded-pill" id="tab-paste-json" data-bs-toggle="pill" data-bs-target="#pane-paste-json" type="button" role="tab">
                                    <i class="bi bi-clipboard-check me-1"></i> Paste Teks JSON (Hasil AI)
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link py-1 small rounded-pill" id="tab-upload-json" data-bs-toggle="pill" data-bs-target="#pane-upload-json" type="button" role="tab">
                                    <i class="bi bi-upload me-1"></i> Upload Berkas .json
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="pane-paste-json" role="tabpanel">
                                <textarea class="form-control font-monospace" name="paste_json" id="textareaPasteJson" rows="5" style="font-size: 0.78rem;" placeholder='Paste kode JSON dari ChatGPT / Gemini / Claude di sini...&#10;Contoh format:&#10;{&#10;  "soal": [ ... ]&#10;}'></textarea>
                            </div>
                            <div class="tab-pane fade" id="pane-upload-json" role="tabpanel">
                                <input type="file" class="form-control form-control-sm" name="file_json" accept=".json">
                            </div>
                        </div>

                        <div class="small text-muted mt-2" style="font-size: 0.74rem;">
                            Mendukung Pilihan Ganda (opsi a-e &amp; kunci) serta Essay (pedoman jawaban). Butir soal juga bisa ditambah manual kapan saja.
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary-custom rounded-pill px-4 fw-semibold shadow-sm">
                        <i class="bi bi-broadcast me-1"></i> Buat &amp; Aktifkan Asesmen
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Buat LKPD Kelompok Baru -->
<div class="modal fade" id="modalBuatLkpd" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-success text-white rounded-3 p-2">
                        <i class="bi bi-people-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0">Buat LKPD Kelompok Baru</h5>
                        <small class="text-muted">KBM: <?= esc($sesi['judul_materi']) ?></small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= site_url('lms/sesi/' . $sesi['id'] . '/lkpd') ?>" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Judul LKPD / Tugas Proyek <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="judul_lkpd" required placeholder="Contoh: LKPD 1 - Konfigurasi Router &amp; Uji Jaringan">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Petunjuk Pengerjaan &amp; Skenario Masalah</label>
                        <textarea class="form-control" name="petunjuk" rows="3" placeholder="Jelaskan tujuan praktik, langkah-langkah kerja kelompok, dan apa yang harus dilaporkan..."></textarea>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-7">
                            <label class="form-label small fw-semibold text-secondary">Metode Pembagian Kelompok</label>
                            <select class="form-select" name="metode_kelompok">
                                <option value="mandiri_siswa">Mandiri oleh Siswa (Pilih Tim Sendiri)</option>
                                <option value="acak">Acak Otomatis oleh Sistem (Random)</option>
                                <option value="manual_guru">Manual Ditentukan Guru</option>
                            </select>
                        </div>
                        <div class="col-5">
                            <label class="form-label small fw-semibold text-secondary">Tenggat Waktu</label>
                            <input type="datetime-local" class="form-control" name="tenggat_waktu">
                        </div>
                    </div>

                    <div class="p-3 bg-light rounded-3 border mb-2">
                        <label class="form-label small fw-bold text-dark mb-1">
                            <i class="bi bi-paperclip text-success me-1"></i> File Panduan / Modul Praktik (Opsional PDF/Doc)
                        </label>
                        <input type="file" class="form-control form-control-sm" name="file_panduan" accept=".pdf,.doc,.docx,.png,.jpg,.jpeg">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold shadow-sm">
                        <i class="bi bi-check2-circle me-1"></i> Simpan &amp; Terbitkan LKPD
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function isiContohJson() {
    const contoh = {
        "soal": [
            {
                "tipe": "pg",
                "pertanyaan": "Protokol apa yang bertugas memberikan konfigurasi IP address secara otomatis kepada client di jaringan?",
                "opsi": {
                    "a": "DNS",
                    "b": "DHCP",
                    "c": "HTTP",
                    "d": "FTP",
                    "e": "SNMP"
                },
                "kunci": "b",
                "bobot": 10
            },
            {
                "tipe": "essay",
                "pertanyaan": "Jelaskan 4 tahapan proses komunikasi dalam siklus DORA pada protokol DHCP!",
                "pedoman_jawaban": "1. Discover: Client broadcast mencari DHCP Server. 2. Offer: Server menawarkan IP. 3. Request: Client meminta IP yang ditawarkan. 4. Acknowledgment: Server mengonfirmasi dan memberikan sewa IP.",
                "bobot": 20
            }
        ]
    };
    document.getElementById('textareaPasteJson').value = JSON.stringify(contoh, null, 2);
    // pastikan tab paste aktif
    const tabEl = document.getElementById('tab-paste-json');
    if (tabEl) {
        bootstrap.Tab.getOrCreateInstance(tabEl).show();
    }
}

function tandaiSemuaHadir() {
    document.querySelectorAll('.radio-status-h').forEach(radio => {
        radio.checked = true;
    });
}
</script>
<?= $this->endSection() ?>

