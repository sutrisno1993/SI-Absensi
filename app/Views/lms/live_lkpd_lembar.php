<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? 'LKPD Kolaboratif Siswa') ?></title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <style>
        :root {
            --primary-emerald: #064e3b;
            --primary-emerald-hover: #043d2e;
            --emerald-accent: #10b981;
            --bg-light: #f8fafc;
            --text-dark: #0f172a;
        }
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-light);
            color: var(--text-dark);
            min-height: 100vh;
        }
        .header-brand {
            background: linear-gradient(135deg, #064e3b 0%, #0f766e 100%);
            color: white;
            border-bottom-left-radius: 2rem;
            border-bottom-right-radius: 2rem;
        }
        .card-custom {
            border-radius: 1.25rem;
            background: #ffffff;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.05), 0 8px 10px -6px rgba(0, 0, 0, 0.01);
        }
        .btn-emerald {
            background-color: var(--primary-emerald);
            color: white;
            border: none;
            transition: all 0.2s ease;
        }
        .btn-emerald:hover {
            background-color: var(--primary-emerald-hover);
            color: white;
            transform: translateY(-1px);
        }
        .badge-soft-emerald {
            background-color: rgba(16, 185, 129, 0.15);
            color: #065f46;
        }
    </style>
</head>
<body>

    <!-- Header Brand -->
    <div class="header-brand py-4 px-3 shadow-sm mb-4">
        <div class="container" style="max-width: 760px;">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-white text-success rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="bi bi-people-fill fs-5"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-white">LKPD KELOMPOK DIGITAL</h6>
                        <small class="text-white-50">SMKS 1 MARET JAKARTA</small>
                    </div>
                </div>
                <span class="badge <?= $lkpd['status'] === 'aktif' ? 'bg-success' : 'bg-danger' ?> rounded-pill px-3 py-2">
                    <i class="bi <?= $lkpd['status'] === 'aktif' ? 'bi-broadcast' : 'bi-lock' ?> me-1"></i>
                    <?= strtoupper($lkpd['status']) ?>
                </span>
            </div>

            <h4 class="fw-bold text-white mb-1"><?= esc($lkpd['judul_lkpd']) ?></h4>
            <div class="d-flex flex-wrap gap-3 small text-white-50 mt-2">
                <span><i class="bi bi-book me-1"></i><?= esc($lkpd['nama_mapel']) ?></span>
                <span><i class="bi bi-person me-1"></i>Guru: <?= esc($lkpd['nama_guru']) ?></span>
                <span><i class="bi bi-mortarboard me-1"></i>Kelas: <?= esc($lkpd['nama_kelas']) ?></span>
            </div>
        </div>
    </div>

    <div class="container pb-5" style="max-width: 760px;">

        <!-- Flash Alert -->
        <?php if (session()->getFlashdata('success')): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-3" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?= session()->getFlashdata('success') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-sm mb-3" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= session()->getFlashdata('error') ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- STEP 1: PILIH IDENTITAS SISWA (JIKA BELUM MEMILIH) -->
        <?php if (! $siswaTerpilih): ?>
            <div class="card card-custom border-0 p-4 mb-4">
                <div class="text-center mb-4">
                    <div class="bg-success bg-opacity-10 text-success rounded-circle d-inline-flex p-3 mb-2">
                        <i class="bi bi-person-bounding-box fs-2"></i>
                    </div>
                    <h5 class="fw-bold text-dark mb-1">Siapa Nama Kamu?</h5>
                    <p class="small text-muted mb-0">Pilih nama kamu dari daftar siswa kelas <strong><?= esc($lkpd['nama_kelas']) ?></strong></p>
                </div>

                <form method="GET" action="<?= site_url('live-lkpd/' . $lkpd['token_lkpd']) ?>">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Daftar Siswa Kelas <?= esc($lkpd['nama_kelas']) ?></label>
                        <select name="siswa_id" class="form-select form-select-lg rounded-3" required>
                            <option value="">-- Sentuh untuk Memilih Nama Kamu --</option>
                            <?php foreach ($siswaKelas as $s): ?>
                                <option value="<?= $s['id'] ?>">
                                    <?= esc($s['nama_siswa']) ?> (NISN: <?= $s['nisn'] ?: '-' ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-emerald rounded-pill w-100 py-3 fw-bold fs-6 shadow-sm">
                        Lanjut ke Lembar LKPD <i class="bi bi-arrow-right ms-1"></i>
                    </button>
                </form>
            </div>

        <!-- STEP 2: SISWA BELUM PUNYA KELOMPOK (PILIH GABUNG / BUAT BARU) -->
        <?php elseif (! $kelompokSaya): ?>
            <div class="card card-custom border-0 p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
                    <div>
                        <small class="text-muted d-block">Identitas Kamu:</small>
                        <h6 class="fw-bold text-dark mb-0"><?= esc($siswaTerpilih['nama_siswa']) ?></h6>
                    </div>
                    <a href="<?= site_url('live-lkpd/' . $lkpd['token_lkpd']) ?>" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        Ganti Nama
                    </a>
                </div>

                <div class="text-center py-2 mb-3">
                    <h5 class="fw-bold text-dark mb-1">Pilih atau Bentuk Kelompok Kamu</h5>
                    <p class="small text-muted">Kamu belum terdaftar di kelompok manapun pada LKPD ini. Silakan bergabung ke kelompok yang sudah ada atau buat kelompok baru bersama temanmu.</p>
                </div>

                <!-- Opsi 1: Gabung ke Kelompok yang Sudah Ada -->
                <?php if (!empty($kelompokList)): ?>
                    <h6 class="fw-bold text-secondary mb-2 small text-uppercase">1. Gabung ke Kelompok yang Sudah Ada:</h6>
                    <div class="list-group mb-4">
                        <?php foreach ($kelompokList as $k): ?>
                            <div class="list-group-item p-3 rounded-3 mb-2 border">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h6 class="fw-bold text-dark mb-1"><?= esc($k['nama_kelompok']) ?></h6>
                                        <small class="text-muted d-block">
                                            Ketua: <strong><?= esc($k['nama_ketua'] ?: '-') ?></strong> | Anggota: <?= count($k['anggota']) ?> Siswa
                                        </small>
                                        <div class="d-flex flex-wrap gap-1 mt-1">
                                            <?php foreach ($k['anggota'] as $ang): ?>
                                                <span class="badge bg-light text-dark border small" style="font-size: 0.68rem;"><?= esc($ang['nama_siswa']) ?></span>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                    <form action="<?= site_url('live-lkpd/' . $lkpd['token_lkpd'] . '/gabung') ?>" method="POST">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="siswa_id" value="<?= $siswaTerpilih['id'] ?>">
                                        <input type="hidden" name="action" value="gabung">
                                        <input type="hidden" name="kelompok_id" value="<?= $k['id'] ?>">
                                        <button type="submit" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold">
                                            Gabung <i class="bi bi-box-arrow-in-right ms-1"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>

                <!-- Opsi 2: Buat Kelompok Baru Mandiri -->
                <h6 class="fw-bold text-secondary mb-2 small text-uppercase">2. Atau Bentuk Kelompok Baru:</h6>
                <form action="<?= site_url('live-lkpd/' . $lkpd['token_lkpd'] . '/gabung') ?>" method="POST" class="p-3 bg-light rounded-3 border">
                    <?= csrf_field() ?>
                    <input type="hidden" name="siswa_id" value="<?= $siswaTerpilih['id'] ?>">
                    <input type="hidden" name="action" value="buat_baru">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Nama Kelompok Baru</label>
                        <input type="text" class="form-control" name="nama_kelompok_baru" placeholder="Contoh: Kelompok Alpha / Tim Cisco Lab" required>
                        <small class="text-muted">Kamu akan otomatis menjadi Ketua Kelompok.</small>
                    </div>
                    <button type="submit" class="btn btn-emerald rounded-pill px-4 fw-semibold shadow-sm w-100">
                        <i class="bi bi-plus-circle me-1"></i> Buat Kelompok &amp; Mulai Pengerjaan
                    </button>
                </form>
            </div>

        <!-- STEP 3: LEMBAR PENGERJAAN LKPD KOLABORATIF (SISWA SUDAH DI KELOMPOK) -->
        <?php else: ?>
            <?php
            $jawaban = $kelompokSaya['jawaban'];
            $isSubmitted = !empty($jawaban) && in_array($jawaban['status_pengumpulan'], ['dikumpulkan', 'dinilai']);
            $isGraded = !empty($jawaban) && $jawaban['status_pengumpulan'] === 'dinilai';
            ?>

            <!-- Banner Info Identitas & Kelompok -->
            <div class="card card-custom border-0 p-4 mb-4">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 pb-3 border-bottom">
                    <div>
                        <span class="badge badge-soft-emerald rounded-pill px-3 py-1 mb-1 fw-bold">
                            Kelompok #<?= $kelompokSaya['nomor_kelompok'] ?>
                        </span>
                        <h4 class="fw-bold text-dark mb-0"><?= esc($kelompokSaya['nama_kelompok']) ?></h4>
                    </div>
                    <div class="text-end">
                        <small class="text-muted d-block">Kamu masuk sebagai:</small>
                        <strong class="text-dark"><?= esc($siswaTerpilih['nama_siswa']) ?></strong>
                        <a href="<?= site_url('live-lkpd/' . $lkpd['token_lkpd']) ?>" class="small text-decoration-none text-muted d-block">(Ganti)</a>
                    </div>
                </div>

                <!-- Anggota Kelompok -->
                <div class="mb-2">
                    <label class="small fw-bold text-secondary text-uppercase mb-1" style="font-size: 0.72rem;">Anggota Tim:</label>
                    <div class="d-flex flex-wrap gap-1">
                        <?php foreach ($kelompokSaya['anggota'] as $ang): ?>
                            <span class="badge <?= $ang['peran'] === 'Ketua' ? 'bg-warning text-dark' : 'bg-light text-dark border' ?> px-2 py-1 rounded-pill small">
                                <i class="bi <?= $ang['peran'] === 'Ketua' ? 'bi-star-fill text-warning' : 'bi-person' ?> me-1"></i>
                                <?= esc($ang['nama_siswa']) ?>
                                <small class="text-muted">(<?= esc($ang['peran'] ?: 'Anggota') ?>)</small>
                            </span>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Petunjuk Skenario / Soal LKPD dari Guru -->
            <div class="card card-custom border-0 p-4 mb-4 bg-white">
                <h5 class="fw-bold text-dark mb-2">
                    <i class="bi bi-file-earmark-text text-success me-2"></i>Petunjuk &amp; Langkah Kerja
                </h5>
                <?php if (!empty($lkpd['petunjuk'])): ?>
                    <div class="p-3 bg-light rounded-3 border small text-secondary mb-3" style="white-space: pre-line; line-height: 1.6;">
                        <?= esc($lkpd['petunjuk']) ?>
                    </div>
                <?php endif; ?>

                <?php if (!empty($lkpd['file_panduan'])): ?>
                    <a href="<?= base_url('uploads/lkpd/' . $lkpd['file_panduan']) ?>" target="_blank" class="btn btn-outline-success rounded-pill px-4 btn-sm">
                        <i class="bi bi-file-earmark-arrow-down me-1"></i> Unduh Lampiran Panduan / Modul Guru
                    </a>
                <?php endif; ?>
            </div>

            <!-- Tampilan Jika Sudah Dinilai Guru -->
            <?php if ($isGraded): ?>
                <div class="card card-custom border-0 p-4 mb-4 bg-primary bg-opacity-10">
                    <div class="text-center py-2">
                        <div class="badge bg-primary fs-3 px-4 py-2 rounded-pill mb-2">
                            Nilai: <?= (float)$jawaban['skor_kelompok'] ?>
                        </div>
                        <h5 class="fw-bold text-dark mb-1">LKPD Telah Selesai Dinilai oleh Guru!</h5>
                        <?php if (!empty($jawaban['feedback_guru'])): ?>
                            <div class="p-3 bg-white rounded-3 shadow-sm mt-3 text-start small">
                                <strong class="text-dark"><i class="bi bi-chat-left-quote text-primary me-1"></i>Catatan / Feedback Guru:</strong>
                                <p class="mb-0 text-secondary mt-1"><?= esc($jawaban['feedback_guru']) ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php elseif ($isSubmitted): ?>
                <div class="alert alert-success rounded-4 border-0 shadow-sm p-4 mb-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="bg-success text-white rounded-circle p-2">
                            <i class="bi bi-check2-all fs-3"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-1">LKPD Sudah Dikumpulkan!</h6>
                            <p class="small mb-0 text-secondary">
                                Jawaban dan laporan praktik kelompok telah terkirim pada <?= date('d M Y, H:i', strtotime($jawaban['waktu_kumpul'])) ?>. Guru sedang memeriksa lembar kerja kelompok Anda.
                            </p>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Form Lembar Kerja Kolaboratif Kelompok -->
            <div class="card card-custom border-0 p-4 mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold text-dark mb-0">
                        <i class="bi bi-pencil-square text-success me-2"></i>Lembar Pengisian Kelompok
                    </h5>
                    <?php if ($isSubmitted): ?>
                        <span class="badge bg-success rounded-pill px-3 py-1">Tersimpan</span>
                    <?php endif; ?>
                </div>

                <form action="<?= site_url('live-lkpd/' . $lkpd['token_lkpd'] . '/submit') ?>" method="POST" enctype="multipart/form-data">
                    <?= csrf_field() ?>
                    <input type="hidden" name="kelompok_id" value="<?= $kelompokSaya['id'] ?>">
                    <input type="hidden" name="siswa_id" value="<?= $siswaTerpilih['id'] ?>">

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">
                            1. Ringkasan Hasil Kerja / Jawaban / Analisis Kelompok <span class="text-danger">*</span>
                        </label>
                        <textarea class="form-control" name="ringkasan_kerja" rows="6" required placeholder="Tuliskan hasil diskusi kelompok, konfigurasi yang dilakukan, atau jawaban pertanyaan praktik..." <?= $lkpd['status'] !== 'aktif' ? 'readonly' : '' ?>><?= esc($jawaban['ringkasan_kerja'] ?? '') ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">
                            2. Tautan Dokumen Eksternal (Opsional)
                        </label>
                        <input type="url" class="form-control" name="link_eksternal" value="<?= esc($jawaban['link_eksternal'] ?? '') ?>" placeholder="https://drive.google.com/... atau https://github.com/... atau Canva" <?= $lkpd['status'] !== 'aktif' ? 'readonly' : '' ?>>
                        <small class="text-muted">Masukkan link Google Drive, Canva, Figma, atau GitHub jika laporan berupa slide/file besar.</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-secondary">
                            3. Unggah Bukti Foto Praktik / Screenshot / Dokumen (Opsional)
                        </label>
                        <input type="file" class="form-control" name="file_lampiran" accept=".jpg,.jpeg,.png,.pdf,.docx,.zip" <?= $lkpd['status'] !== 'aktif' ? 'disabled' : '' ?>>
                        <?php if (!empty($jawaban['file_lampiran'])): ?>
                            <div class="mt-2 small">
                                <span class="text-muted">Berkas terunggah:</span>
                                <a href="<?= base_url('uploads/lkpd/' . $jawaban['file_lampiran']) ?>" target="_blank" class="fw-bold text-success text-decoration-none">
                                    <i class="bi bi-file-earmark-check me-1"></i>Lihat Berkas Yang Dikirim
                                </a>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($lkpd['status'] === 'aktif'): ?>
                        <button type="submit" class="btn btn-emerald rounded-pill w-100 py-3 fw-bold fs-6 shadow-sm">
                            <i class="bi bi-send-check me-1"></i>
                            <?= $isSubmitted ? 'Perbarui & Simpan Ulang LKPD' : 'Kumpulkan LKPD Kelompok' ?>
                        </button>
                    <?php else: ?>
                        <div class="alert alert-secondary small text-center rounded-3 mb-0">
                            <i class="bi bi-lock me-1"></i> Pengumpulan LKPD telah ditutup oleh Guru.
                        </div>
                    <?php endif; ?>
                </form>
            </div>
        <?php endif; ?>

        <div class="text-center text-muted small mt-4">
            &copy; <?= date('Y') ?> SMKS 1 Maret Jakarta &bull; Sistem LMS &amp; Presensi Sekolah
        </div>
    </div>

    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
