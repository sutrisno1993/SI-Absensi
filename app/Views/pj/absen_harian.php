<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<?php
$liveUrl = site_url('live-presensi/' . esc($sesiKelas['share_token']));
$namaWalasStr = $walas ? $walas['nama_guru'] : 'Wali Kelas';
$hasFoto = !empty($sesiKelas['foto_kelas']) && file_exists(FCPATH . $sesiKelas['foto_kelas']);
?>

<div class="row mb-4 align-items-center">
    <div class="col-12 col-md-7">
        <h3 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
            <i class="bi bi-clipboard-check text-primary"></i>
            <span>Presensi Harian Kelas <?= esc($kelas['nama_kelas']) ?></span>
        </h3>
        <p class="text-muted small mb-0">Input kehadiran teman sekelas, foto dokumentasi anak-anak di kelas, dan bagikan link live ke grup WA orang tua.</p>
    </div>
    <div class="col-12 col-md-5 mt-3 mt-md-0 text-md-end d-flex flex-wrap justify-content-md-end gap-2">
        <button type="button" class="btn btn-success rounded-pill px-3 shadow-sm fw-semibold" onclick="bukaModalWA()">
            <i class="bi bi-whatsapp me-1"></i> Bagikan Link Realtime ke Ortu
        </button>
        <button type="button" class="btn btn-outline-info rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalFotoKelas">
            <i class="bi bi-camera-fill me-1"></i> <?= $hasFoto ? 'Ganti Foto Kelas' : 'Upload Foto Kelas' ?>
        </button>
    </div>
</div>

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
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary px-3 py-2 fs-6 rounded-pill">
                    <i class="bi bi-calendar-date me-1"></i> <?= date('d F Y', strtotime($tanggal)) ?>
                </span>
                <span class="badge bg-dark text-muted border px-2 py-2">
                    Total Siswa: <?= count($daftarSiswa) ?> Orang
                </span>
            </div>
            <div class="d-flex gap-2">
                <button type="button" class="btn btn-sm btn-outline-success fw-semibold rounded-pill px-3" onclick="setSemuaHadir()">
                    <i class="bi bi-check-all me-1"></i> Set Semua Hadir (H)
                </button>
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
                                    <div class="d-inline-flex gap-2">
                                        <!-- Hadir -->
                                        <label>
                                            <input type="radio" class="status-pill-radio radio-h" name="absensi[<?= $s['siswa_id'] ?>][status]" value="H" <?= ($s['status'] ?? 'H') === 'H' ? 'checked' : '' ?>>
                                            <span class="status-label" title="Hadir">H</span>
                                        </label>
                                        <!-- Sakit -->
                                        <label>
                                            <input type="radio" class="status-pill-radio" name="absensi[<?= $s['siswa_id'] ?>][status]" value="S" <?= ($s['status'] ?? '') === 'S' ? 'checked' : '' ?>>
                                            <span class="status-label" title="Sakit">S</span>
                                        </label>
                                        <!-- Izin -->
                                        <label>
                                            <input type="radio" class="status-pill-radio" name="absensi[<?= $s['siswa_id'] ?>][status]" value="I" <?= ($s['status'] ?? '') === 'I' ? 'checked' : '' ?>>
                                            <span class="status-label" title="Izin">I</span>
                                        </label>
                                        <!-- Alpa -->
                                        <label>
                                            <input type="radio" class="status-pill-radio" name="absensi[<?= $s['siswa_id'] ?>][status]" value="A" <?= ($s['status'] ?? '') === 'A' ? 'checked' : '' ?>>
                                            <span class="status-label" title="Alpa / Tanpa Keterangan">A</span>
                                        </label>
                                    </div>
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm bg-dark text-light border-secondary" name="absensi[<?= $s['siswa_id'] ?>][keterangan]" value="<?= esc($s['keterangan'] ?? '') ?>" placeholder="Misal: Demam, Surat dokter...">
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
                <button type="submit" class="btn btn-primary-custom px-4 py-2 rounded-pill fw-semibold">
                    <i class="bi bi-cloud-check-fill me-2"></i> Simpan Data Presensi
                </button>
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
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function setSemuaHadir() {
        document.querySelectorAll('.radio-h').forEach(function(radio) {
            radio.checked = true;
        });
    }

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
