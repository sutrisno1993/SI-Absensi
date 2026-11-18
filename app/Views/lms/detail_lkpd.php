<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="container-fluid py-4">

    <!-- Flash Message -->
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i><?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i><?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Breadcrumb & Header Info -->
    <div class="card card-custom border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb mb-1 small">
                            <li class="breadcrumb-item"><a href="<?= site_url('lms') ?>" class="text-decoration-none">LMS</a></li>
                            <li class="breadcrumb-item"><a href="<?= site_url('lms/sesi/' . $lkpd['sesi_id']) ?>" class="text-decoration-none"><?= esc($lkpd['judul_materi']) ?></a></li>
                            <li class="breadcrumb-item active" aria-current="page">LKPD Kelompok</li>
                        </ol>
                    </nav>
                    <div class="d-flex align-items-center gap-2">
                        <h4 class="fw-bold text-dark mb-0"><?= esc($lkpd['judul_lkpd']) ?></h4>
                        <span class="badge <?= $lkpd['status'] === 'aktif' ? 'bg-success' : 'bg-secondary' ?> rounded-pill">
                            <?= strtoupper($lkpd['status']) ?>
                        </span>
                    </div>
                    <div class="d-flex flex-wrap align-items-center gap-3 text-muted small mt-2">
                        <span><i class="bi bi-mortarboard me-1 text-primary"></i>Kelas: <strong><?= esc($lkpd['nama_kelas']) ?></strong></span>
                        <span><i class="bi bi-book me-1 text-success"></i>Mapel: <strong><?= esc($lkpd['nama_mapel']) ?></strong></span>
                        <span><i class="bi bi-diagram-3 me-1 text-info"></i>Metode: <strong><?= $lkpd['metode_kelompok'] === 'acak' ? 'Acak Sistem' : ($lkpd['metode_kelompok'] === 'manual_guru' ? 'Manual Guru' : 'Mandiri Siswa') ?></strong></span>
                        <?php if (!empty($lkpd['tenggat_waktu'])): ?>
                            <span class="text-danger"><i class="bi bi-alarm me-1"></i>Batas: <strong><?= date('d M Y, H:i', strtotime($lkpd['tenggat_waktu'])) ?></strong></span>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Tombol Aksi Guru -->
                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalBagiAcak">
                        <i class="bi bi-shuffle me-1"></i> Bagi Kelompok Acak
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalTambahKelompok">
                        <i class="bi bi-plus-circle me-1"></i> Tambah Kelompok Manual
                    </button>
                    <form action="<?= site_url('lms/lkpd/' . $lkpd['id'] . '/toggle-status') ?>" method="POST" class="d-inline">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn btn-sm <?= $lkpd['status'] === 'aktif' ? 'btn-outline-danger' : 'btn-outline-success' ?> rounded-pill px-3">
                            <i class="bi <?= $lkpd['status'] === 'aktif' ? 'bi-lock' : 'bi-unlock' ?> me-1"></i>
                            <?= $lkpd['status'] === 'aktif' ? 'Tutup LKPD' : 'Buka LKPD' ?>
                        </button>
                    </form>
                    <a href="<?= site_url('lms/lkpd/' . $lkpd['id'] . '/hapus') ?>" onclick="return confirm('Yakin ingin menghapus LKPD ini beserta seluruh kelompok dan jawabannya?')" class="btn btn-sm btn-outline-secondary rounded-pill px-2">
                        <i class="bi bi-trash"></i>
                    </a>
                </div>
            </div>

            <!-- Petunjuk Pengerjaan & File Panduan -->
            <?php if (!empty($lkpd['petunjuk']) || !empty($lkpd['file_panduan'])): ?>
                <div class="mt-3 p-3 bg-light rounded-3 border">
                    <?php if (!empty($lkpd['petunjuk'])): ?>
                        <div class="small text-secondary mb-2" style="white-space: pre-line;">
                            <strong class="text-dark"><i class="bi bi-info-circle me-1"></i>Petunjuk Skenario / Langkah Praktik:</strong><br>
                            <?= esc($lkpd['petunjuk']) ?>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($lkpd['file_panduan'])): ?>
                        <a href="<?= base_url('uploads/lkpd/' . $lkpd['file_panduan']) ?>" target="_blank" class="btn btn-xs btn-outline-primary rounded-pill px-3 py-1">
                            <i class="bi bi-paperclip me-1"></i> Unduh Berkas Panduan / Modul Praktik
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Banner Tautan Pengerjaan Siswa (Live Link & QR Code) -->
    <?php
    $liveUrl = site_url('live-lkpd/' . $lkpd['token_lkpd']);
    $waText  = urlencode("Halo rekan-rekan kelas {$lkpd['nama_kelas']}, silakan buka LKPD Kelompok: {$lkpd['judul_lkpd']} melalui link berikut:\n\n{$liveUrl}");
    ?>
    <div class="card card-custom border-0 shadow-sm mb-4 bg-primary bg-opacity-10">
        <div class="card-body p-4">
            <div class="row align-items-center g-3">
                <div class="col-12 col-md-auto text-center">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=100x100&data=<?= urlencode($liveUrl) ?>" alt="QR Code LKPD" class="img-fluid rounded-3 shadow-sm bg-white p-1" style="max-width: 90px;">
                </div>
                <div class="col-12 col-md">
                    <h6 class="fw-bold text-dark mb-1">
                        <i class="bi bi-share text-success me-1"></i> Tautan Lembar Kerja Siswa (Tanpa Password)
                    </h6>
                    <div class="input-group input-group-sm mb-2" style="max-width: 550px;">
                        <input type="text" class="form-control bg-white" id="linkLkpdInput" value="<?= $liveUrl ?>" readonly>
                        <button class="btn btn-dark" type="button" onclick="salinLinkLkpd()">
                            <i class="bi bi-clipboard me-1"></i> Salin
                        </button>
                    </div>
                    <small class="text-muted d-block">Siswa membuka link/QR ini di HP, memilih kelompoknya, mengerjakan bersama, dan mengunggah laporan/foto praktik.</small>
                </div>
                <div class="col-12 col-md-auto text-end">
                    <a href="https://api.whatsapp.com/send?text=<?= $waText ?>" target="_blank" class="btn btn-success rounded-pill px-3 shadow-sm">
                        <i class="bi bi-whatsapp me-1"></i> Bagikan ke Grup WA
                    </a>
                    <a href="<?= $liveUrl ?>" target="_blank" class="btn btn-outline-dark rounded-pill px-3 shadow-sm">
                        <i class="bi bi-box-arrow-up-right me-1"></i> Coba Buka
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Siswa yang Belum Memiliki Kelompok (Alert) -->
    <?php if (!empty($siswaBelumAdaKelompok)): ?>
        <div class="alert alert-warning border-0 rounded-4 shadow-sm mb-4">
            <div class="d-flex align-items-start gap-2">
                <i class="bi bi-exclamation-triangle-fill fs-5 mt-1 text-warning"></i>
                <div class="flex-grow-1">
                    <strong class="d-block mb-1">Ada <?= count($siswaBelumAdaKelompok) ?> siswa yang belum masuk ke kelompok:</strong>
                    <div class="d-flex flex-wrap gap-1">
                        <?php foreach ($siswaBelumAdaKelompok as $sb): ?>
                            <span class="badge bg-white text-dark border px-2 py-1 rounded-pill small"><?= esc($sb['nama_siswa']) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <button type="button" class="btn btn-sm btn-warning rounded-pill px-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#modalBagiAcak">
                    <i class="bi bi-shuffle me-1"></i> Bagi Acak Semua
                </button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Daftar Kelompok & Lembar Pengerjaan (Grid Cards) -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h5 class="fw-bold text-dark mb-0">
            <i class="bi bi-people-fill text-success me-2"></i>Daftar Kelompok &amp; Lembar Kerja (<?= count($kelompokList) ?> Kelompok)
        </h5>
    </div>

    <?php if (empty($kelompokList)): ?>
        <div class="card card-custom border-0 shadow-sm p-5 text-center text-muted">
            <i class="bi bi-people display-4 text-secondary mb-3"></i>
            <h5 class="fw-bold text-dark">Belum Ada Kelompok Terdaftar</h5>
            <p class="small text-muted mb-4">Anda dapat membagi siswa kelas ini ke dalam beberapa kelompok secara otomatis, membuat kelompok manual, atau membiarkan siswa memilih kelompok mandiri dari link LKPD.</p>
            <div class="d-flex justify-content-center gap-2">
                <button type="button" class="btn btn-success rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalBagiAcak">
                    <i class="bi bi-shuffle me-1"></i> Bagi Kelompok Acak Otomatis
                </button>
                <button type="button" class="btn btn-outline-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalTambahKelompok">
                    <i class="bi bi-plus-circle me-1"></i> Buat Kelompok Manual
                </button>
            </div>
        </div>
    <?php else: ?>
        <div class="row g-4">
            <?php foreach ($kelompokList as $k): ?>
                <?php
                $jawaban = $k['jawaban'];
                $isSubmitted = !empty($jawaban) && in_array($jawaban['status_pengumpulan'], ['dikumpulkan', 'dinilai']);
                $isGraded = !empty($jawaban) && $jawaban['status_pengumpulan'] === 'dinilai';
                ?>
                <div class="col-12 col-lg-6">
                    <div class="card card-custom border-0 shadow-sm h-100">
                        <!-- Card Header Kelompok -->
                        <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                            <div>
                                <span class="badge bg-success bg-opacity-15 text-success rounded-pill px-2 py-1 mb-1">
                                    Kelompok #<?= $k['nomor_kelompok'] ?>
                                </span>
                                <h5 class="fw-bold text-dark mb-0"><?= esc($k['nama_kelompok']) ?></h5>
                            </div>
                            <div>
                                <?php if ($isGraded): ?>
                                    <span class="badge bg-primary px-3 py-2 rounded-pill fs-6">
                                        Nilai: <?= (float)$jawaban['skor_kelompok'] ?>
                                    </span>
                                <?php elseif ($isSubmitted): ?>
                                    <span class="badge bg-info px-3 py-2 rounded-pill">
                                        <i class="bi bi-check2-all me-1"></i> Sudah Dikumpulkan
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary px-3 py-2 rounded-pill">
                                        <i class="bi bi-hourglass-split me-1"></i> Belum Mengumpulkan
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="card-body p-3 pt-0">
                            <!-- Anggota Tim -->
                            <div class="mb-3">
                                <label class="small fw-bold text-muted text-uppercase mb-1" style="font-size: 0.72rem;">Anggota Tim (<?= count($k['anggota']) ?> Siswa)</label>
                                <div class="d-flex flex-wrap gap-1">
                                    <?php foreach ($k['anggota'] as $ang): ?>
                                        <span class="badge <?= $ang['peran'] === 'Ketua' ? 'bg-warning text-dark' : 'bg-light text-dark border' ?> px-2 py-1 rounded-pill small">
                                            <i class="bi <?= $ang['peran'] === 'Ketua' ? 'bi-star-fill text-warning' : 'bi-person' ?> me-1"></i>
                                            <?= esc($ang['nama_siswa']) ?>
                                            <small class="text-muted">(<?= esc($ang['peran'] ?: 'Anggota') ?>)</small>
                                            <?php if ($ang['nilai_individu'] !== null): ?>
                                                <strong class="text-primary ms-1">[<?= (float)$ang['nilai_individu'] ?>]</strong>
                                            <?php endif; ?>
                                        </span>
                                    <?php endforeach; ?>
                                </div>
                            </div>

                            <!-- Laporan Pengerjaan & Dokumen Praktik -->
                            <div class="p-3 bg-light rounded-3 border mb-3">
                                <label class="small fw-bold text-dark mb-1">
                                    <i class="bi bi-journal-text text-primary me-1"></i> Ringkasan Hasil Praktik / Jawaban:
                                </label>
                                <?php if (!empty($jawaban['ringkasan_kerja'])): ?>
                                    <p class="small text-secondary mb-2" style="white-space: pre-line;"><?= esc($jawaban['ringkasan_kerja']) ?></p>
                                <?php else: ?>
                                    <p class="small text-muted fst-italic mb-2">Belum ada pengisian ringkasan kerja.</p>
                                <?php endif; ?>

                                <!-- Lampiran / Foto & Link Proyek -->
                                <div class="d-flex flex-wrap gap-2 pt-2 border-top">
                                    <?php if (!empty($jawaban['file_lampiran'])): ?>
                                        <a href="<?= base_url('uploads/lkpd/' . $jawaban['file_lampiran']) ?>" target="_blank" class="btn btn-xs btn-outline-success rounded-pill px-3 py-1">
                                            <i class="bi bi-file-earmark-image me-1"></i> Lihat Foto / Dokumen Praktik
                                        </a>
                                    <?php endif; ?>
                                    <?php if (!empty($jawaban['link_eksternal'])): ?>
                                        <a href="<?= esc($jawaban['link_eksternal']) ?>" target="_blank" class="btn btn-xs btn-outline-primary rounded-pill px-3 py-1">
                                            <i class="bi bi-box-arrow-up-right me-1"></i> Buka Link Proyek (GitHub/Canva)
                                        </a>
                                    <?php endif; ?>
                                    <?php if (!empty($jawaban['waktu_kumpul'])): ?>
                                        <small class="text-muted ms-auto align-self-center" style="font-size: 0.72rem;">
                                            <i class="bi bi-clock me-1"></i> Dikumpulkan: <?= date('d M Y, H:i', strtotime($jawaban['waktu_kumpul'])) ?>
                                        </small>
                                    <?php endif; ?>
                                </div>
                            </div>

                            <!-- Form Penilaian Guru -->
                            <form action="<?= site_url('lms/lkpd/' . $lkpd['id'] . '/nilai') ?>" method="POST" class="p-3 bg-white rounded-3 border">
                                <?= csrf_field() ?>
                                <input type="hidden" name="kelompok_id" value="<?= $k['id'] ?>">

                                <div class="row g-2 mb-2">
                                    <div class="col-4">
                                        <label class="form-label small fw-bold text-dark mb-1">Skor Kelompok (0-100)</label>
                                        <input type="number" step="0.1" min="0" max="100" class="form-control form-control-sm fw-bold text-primary" name="skor_kelompok" value="<?= $jawaban['skor_kelompok'] ?? '' ?>" required placeholder="Misal: 85">
                                    </div>
                                    <div class="col-8">
                                        <label class="form-label small fw-bold text-dark mb-1">Catatan / Feedback Guru</label>
                                        <input type="text" class="form-control form-control-sm" name="feedback_guru" value="<?= esc($jawaban['feedback_guru'] ?? '') ?>" placeholder="Bagus, topologi rapi dan DHCP berfungsi">
                                    </div>
                                </div>

                                <!-- Accordion Penyesuaian Nilai Individu (Opsional) -->
                                <div class="accordion accordion-flush mb-2" id="accIndividu<?= $k['id'] ?>">
                                    <div class="accordion-item border-0">
                                        <h2 class="accordion-header">
                                            <button class="accordion-button collapsed py-1 px-0 small text-muted" type="button" data-bs-toggle="collapse" data-bs-target="#colIndividu<?= $k['id'] ?>">
                                                <i class="bi bi-sliders me-1"></i> Sesuaikan Nilai Khusus Individu (Opsional)
                                            </button>
                                        </h2>
                                        <div id="colIndividu<?= $k['id'] ?>" class="accordion-collapse collapse">
                                            <div class="accordion-body px-0 pt-2 pb-0">
                                                <small class="text-muted d-block mb-2">Isi hanya jika anak tertentu memiliki kontribusi berbeda dari skor kelompok:</small>
                                                <?php foreach ($k['anggota'] as $ang): ?>
                                                    <div class="row g-2 align-items-center mb-1">
                                                        <div class="col-7">
                                                            <small class="text-truncate d-block"><?= esc($ang['nama_siswa']) ?></small>
                                                        </div>
                                                        <div class="col-5">
                                                            <input type="number" step="0.1" min="0" max="100" class="form-control form-control-sm py-0" name="nilai_individu[<?= $ang['id'] ?>]" value="<?= $ang['nilai_individu'] ?? '' ?>" placeholder="Default kelompok">
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="text-end">
                                    <button type="submit" class="btn btn-sm btn-primary-custom rounded-pill px-3 fw-semibold">
                                        <i class="bi bi-check-circle me-1"></i> Simpan Nilai &amp; Feedback
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Modal Bagi Kelompok Acak Otomatis -->
<div class="modal fade" id="modalBagiAcak" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-success text-white rounded-3 p-2">
                        <i class="bi bi-shuffle fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0">Bagi Kelompok Secara Acak</h5>
                        <small class="text-muted">Total Siswa Aktif: <?= count($siswaKelas) ?> Siswa</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= site_url('lms/lkpd/' . $lkpd['id'] . '/bagi-acak') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body py-4">
                    <p class="small text-secondary mb-3">
                        Sistem akan mengacak seluruh siswa aktif di kelas <strong><?= esc($lkpd['nama_kelas']) ?></strong> dan membagikannya secara merata ke dalam sejumlah kelompok. Siswa pertama di setiap kelompok otomatis ditunjuk sebagai Ketua.
                    </p>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Mau Dibagi Berapa Kelompok?</label>
                        <select class="form-select" name="jumlah_kelompok" required>
                            <option value="3">3 Kelompok (± <?= ceil(count($siswaKelas) / 3) ?> siswa/kelompok)</option>
                            <option value="4">4 Kelompok (± <?= ceil(count($siswaKelas) / 4) ?> siswa/kelompok)</option>
                            <option value="5" selected>5 Kelompok (± <?= ceil(count($siswaKelas) / 5) ?> siswa/kelompok)</option>
                            <option value="6">6 Kelompok (± <?= ceil(count($siswaKelas) / 6) ?> siswa/kelompok)</option>
                            <option value="7">7 Kelompok (± <?= ceil(count($siswaKelas) / 7) ?> siswa/kelompok)</option>
                            <option value="8">8 Kelompok (± <?= ceil(count($siswaKelas) / 8) ?> siswa/kelompok)</option>
                        </select>
                    </div>
                    <div class="alert alert-warning small border-0 mb-0 py-2">
                        <i class="bi bi-info-circle me-1"></i> Catatan: Jika sebelumnya sudah ada kelompok yang terbentuk, kelompok lama akan diperbarui/dirombak ulang.
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold shadow-sm">
                        <i class="bi bi-shuffle me-1"></i> Mulai Acak Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Tambah Kelompok Manual -->
<div class="modal fade" id="modalTambahKelompok" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-primary text-white rounded-3 p-2">
                        <i class="bi bi-people-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0">Tambah Kelompok Manual</h5>
                        <small class="text-muted">Tentukan nama dan pilih anggota kelompok</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= site_url('lms/lkpd/' . $lkpd['id'] . '/tambah-kelompok') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Nama Kelompok <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_kelompok" required placeholder="Contoh: Kelompok Alpha / Tim Router MikroTik">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Ketua Kelompok (Opsional)</label>
                        <select class="form-select" name="ketua_siswa_id">
                            <option value="">-- Pilih Siswa Sebagai Ketua --</option>
                            <?php foreach ($siswaKelas as $sk): ?>
                                <option value="<?= $sk['id'] ?>"><?= esc($sk['nama_siswa']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-secondary">Pilih Anggota Siswa</label>
                        <div class="border rounded-3 p-2" style="max-height: 200px; overflow-y: auto;">
                            <?php foreach ($siswaKelas as $sk): ?>
                                <div class="form-check small py-1">
                                    <input class="form-check-input" type="checkbox" name="anggota_siswa_ids[]" value="<?= $sk['id'] ?>" id="siswaChk<?= $sk['id'] ?>">
                                    <label class="form-check-label" for="siswaChk<?= $sk['id'] ?>">
                                        <?= esc($sk['nama_siswa']) ?>
                                    </label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary-custom rounded-pill px-4 fw-semibold shadow-sm">
                        <i class="bi bi-check-circle me-1"></i> Simpan Kelompok
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function salinLinkLkpd() {
    const input = document.getElementById('linkLkpdInput');
    input.select();
    input.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(input.value);
    alert('Tautan LKPD berhasil disalin ke clipboard!');
}
</script>
<?= $this->endSection() ?>
