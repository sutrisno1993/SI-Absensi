<!DOCTYPE html>
<html lang="id" data-bs-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?> - SI-ABSEN</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 CSS & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <style>
        :root {
            --bg-body: #070a13;
            --bg-card: #101728;
            --bg-card-header: #151f36;
            --border-color: rgba(255, 255, 255, 0.08);
            --primary-gradient: linear-gradient(135deg, #6366f1 0%, #4f46e5 50%, #3b82f6 100%);
        }

        body {
            background-color: var(--bg-body);
            color: #f8fafc;
            font-family: 'Plus Jakarta Sans', sans-serif;
            min-height: 100vh;
        }

        .card-custom {
            background-color: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 1rem;
        }

        .pulse-indicator {
            display: inline-block;
            width: 9px;
            height: 9px;
            background-color: #22c55e;
            border-radius: 50%;
            box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
            animation: pulse-green 2s infinite;
        }

        @keyframes pulse-green {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
            }
            70% {
                transform: scale(1);
                box-shadow: 0 0 0 8px rgba(34, 197, 94, 0);
            }
            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(34, 197, 94, 0);
            }
        }

        .photo-container {
            border-radius: 0.85rem;
            overflow: hidden;
            background: #000;
            border: 1px solid rgba(255, 255, 255, 0.12);
        }

        .photo-container img {
            width: 100%;
            max-height: 380px;
            object-fit: cover;
            cursor: pointer;
            transition: transform 0.25s ease;
        }

        .photo-container img:hover {
            transform: scale(1.02);
        }

        .status-badge-pill {
            font-size: 0.85rem;
            font-weight: 700;
            padding: 0.35rem 0.75rem;
            border-radius: 50rem;
        }

        .student-item {
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            transition: background 0.15s ease;
        }

        .student-item:last-child {
            border-bottom: none;
        }

        .student-item:hover {
            background: rgba(255, 255, 255, 0.03);
        }
    </style>
</head>
<body class="py-3 py-md-5">

<div class="container" style="max-width: 820px;">

    <!-- Top Branding & Realtime Status -->
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div class="d-flex align-items-center gap-2">
            <div class="bg-primary text-white p-2 rounded-3 fs-5">
                <i class="bi bi-mortarboard-fill"></i>
            </div>
            <div>
                <span class="fw-bold fs-6 text-white">SI-ABSEN</span>
                <span class="text-muted small d-none d-sm-inline"> &bull; Monitoring Presensi & Suasana Kelas</span>
            </div>
        </div>
        <div>
            <span class="badge bg-success bg-opacity-15 text-success border border-success border-opacity-30 px-3 py-2 rounded-pill d-flex align-items-center gap-2">
                <span class="pulse-indicator"></span>
                <span>Realtime Aktif</span>
            </span>
        </div>
    </div>

    <!-- Main Header Card -->
    <div class="card card-custom border-0 shadow-sm p-4 mb-4" style="background: linear-gradient(145deg, #121a2d 0%, #0d1322 100%);">
        <div class="row align-items-center g-3">
            <div class="col-12 col-md-8">
                <span class="badge bg-primary bg-opacity-20 text-primary border border-primary border-opacity-30 px-3 py-1 rounded-pill mb-2">
                    Laporan Kehadiran Siswa
                </span>
                <h2 class="fw-bold text-white mb-1">
                    Kelas <?= esc($sesi['nama_kelas']) ?>
                </h2>
                <div class="d-flex flex-wrap align-items-center gap-3 text-light small mt-2">
                    <div>
                        <i class="bi bi-calendar3 text-primary me-1"></i>
                        <?= date('l, d F Y', strtotime($tanggal)) ?>
                    </div>
                    <div>
                        <i class="bi bi-person-badge text-info me-1"></i>
                        Wali Kelas: <strong><?= esc($sesi['nama_walas'] ?? 'Bapak/Ibu Guru') ?></strong>
                    </div>
                    <?php if (!empty($sesi['shift'])): ?>
                        <div>
                            <i class="bi bi-sun text-warning me-1"></i> Shift <?= esc($sesi['shift']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="col-12 col-md-4 text-md-end">
                <div class="p-3 rounded-3 bg-dark border border-secondary border-opacity-25 text-center text-md-end">
                    <div class="small text-muted mb-1">Tingkat Kehadiran:</div>
                    <div class="display-6 fw-bold text-success"><?= $persenHadir ?>%</div>
                    <div class="progress mt-2" style="height: 6px;">
                        <div class="progress-bar bg-success" style="width: <?= $persenHadir ?>%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Foto Dokumentasi Anak-Anak di Kelas Hari Ini -->
    <div class="card card-custom border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 pt-4 px-4 pb-1 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-white d-flex align-items-center gap-2">
                <i class="bi bi-camera-fill text-warning"></i>
                <span>Suasana Anak-Anak di Kelas Hari Ini</span>
            </h5>
            <?php if (!empty($sesi['foto_kelas']) && file_exists(FCPATH . $sesi['foto_kelas'])): ?>
                <span class="badge bg-secondary bg-opacity-20 text-light border px-2 py-1 small">
                    <i class="bi bi-zoom-in me-1"></i> Klik untuk memperbesar
                </span>
            <?php endif; ?>
        </div>
        <div class="card-body p-4 pt-3">
            <?php if (!empty($sesi['foto_kelas']) && file_exists(FCPATH . $sesi['foto_kelas'])): ?>
                <div class="photo-container shadow" data-bs-toggle="modal" data-bs-target="#modalZoomFoto">
                    <img src="<?= base_url(esc($sesi['foto_kelas'])) ?>" alt="Dokumentasi Kelas <?= esc($sesi['nama_kelas']) ?>">
                </div>
                <div class="text-center text-muted small mt-2">
                    <i class="bi bi-info-circle me-1"></i> Foto dokumentasi kegiatan belajar & presensi di kelas hari ini.
                </div>
            <?php else: ?>
                <div class="p-4 rounded-3 bg-dark border border-dashed border-secondary text-center text-muted">
                    <i class="bi bi-camera fs-1 d-block mb-2 text-secondary"></i>
                    <h6 class="fw-bold text-white mb-1">Foto kegiatan belum diunggah</h6>
                    <p class="small text-muted mb-0">Perwakilan kelas akan mengambil dan mengunggah foto suasana anak-anak di kelas saat sesi presensi berlangsung.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Ringkasan Statistik 4 Kartu -->
    <div class="row g-2 g-sm-3 mb-4">
        <div class="col-6 col-sm-3">
            <div class="card card-custom border-0 shadow-sm p-3 text-center border-bottom border-3 border-success">
                <div class="text-muted small fw-semibold">HADIR (H)</div>
                <div class="display-6 fw-bold text-success my-1"><?= $countH ?></div>
                <div class="small text-muted">Siswa</div>
            </div>
        </div>
        <div class="col-6 col-sm-3">
            <div class="card card-custom border-0 shadow-sm p-3 text-center border-bottom border-3 border-warning">
                <div class="text-muted small fw-semibold">SAKIT (S)</div>
                <div class="display-6 fw-bold text-warning my-1"><?= $countS ?></div>
                <div class="small text-muted">Siswa</div>
            </div>
        </div>
        <div class="col-6 col-sm-3">
            <div class="card card-custom border-0 shadow-sm p-3 text-center border-bottom border-3 border-info">
                <div class="text-muted small fw-semibold">IZIN (I)</div>
                <div class="display-6 fw-bold text-info my-1"><?= $countI ?></div>
                <div class="small text-muted">Siswa</div>
            </div>
        </div>
        <div class="col-6 col-sm-3">
            <div class="card card-custom border-0 shadow-sm p-3 text-center border-bottom border-3 border-danger">
                <div class="text-muted small fw-semibold">ALPHA (A)</div>
                <div class="display-6 fw-bold text-danger my-1"><?= $countA ?></div>
                <div class="small text-muted">Siswa</div>
            </div>
        </div>
    </div>

    <!-- Daftar Presensi Siswa Realtime -->
    <div class="card card-custom border-0 shadow-sm mb-4">
        <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                <div>
                    <h5 class="fw-bold mb-0 text-white d-flex align-items-center gap-2">
                        <i class="bi bi-people-fill text-primary"></i>
                        <span>Daftar Kehadiran Siswa</span>
                    </h5>
                    <small class="text-muted">Total: <?= $totalSiswa ?> Siswa Terdaftar</small>
                </div>
                <!-- Live Filter Cari Nama Anak -->
                <div class="col-12 col-sm-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-dark border-secondary border-opacity-50 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" id="cariNamaInput" class="form-control bg-dark text-light border-secondary border-opacity-50" placeholder="Cari nama putra/putri Anda..." onkeyup="filterSiswa()">
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="list-group list-group-flush rounded-bottom-4" id="daftarSiswaContainer">
                <?php if (empty($daftarSiswa)): ?>
                    <div class="p-5 text-center text-muted">
                        <i class="bi bi-inbox fs-1 d-block mb-2"></i> Belum ada data siswa di kelas ini.
                    </div>
                <?php else: ?>
                    <?php foreach ($daftarSiswa as $idx => $s): ?>
                        <?php
                        $st = $s['status'] ?? 'H';
                        $badgeClass = 'bg-success text-success';
                        $statusLabel = 'Hadir';
                        if ($st === 'S') {
                            $badgeClass = 'bg-warning text-warning';
                            $statusLabel = 'Sakit';
                        } elseif ($st === 'I') {
                            $badgeClass = 'bg-info text-info';
                            $statusLabel = 'Izin';
                        } elseif ($st === 'A') {
                            $badgeClass = 'bg-danger text-danger';
                            $statusLabel = 'Alpha';
                        }
                        ?>
                        <div class="p-3 px-4 d-flex justify-content-between align-items-center student-item" data-nama="<?= strtolower(esc($s['nama_siswa'])) ?>" data-nisn="<?= esc($s['nisn']) ?>">
                            <div class="d-flex align-items-center gap-3">
                                <span class="badge bg-dark text-muted border rounded-circle p-2" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">
                                    <?= $idx + 1 ?>
                                </span>
                                <div>
                                    <h6 class="fw-bold text-white mb-0 student-name"><?= esc($s['nama_siswa']) ?></h6>
                                    <div class="small text-muted font-monospace">NISN: <?= esc($s['nisn']) ?></div>
                                    <?php if (!empty($s['keterangan'])): ?>
                                        <div class="small text-warning mt-1">
                                            <i class="bi bi-info-circle me-1"></i> <?= esc($s['keterangan']) ?>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div>
                                <span class="status-badge-pill <?= $badgeClass ?> bg-opacity-15 border border-opacity-30">
                                    <?php if ($st === 'H'): ?>
                                        <i class="bi bi-check-circle-fill me-1"></i> Hadir
                                    <?php elseif ($st === 'S'): ?>
                                        <i class="bi bi-bandaid-fill me-1"></i> Sakit
                                    <?php elseif ($st === 'I'): ?>
                                        <i class="bi bi-envelope-paper-fill me-1"></i> Izin
                                    <?php else: ?>
                                        <i class="bi bi-x-circle-fill me-1"></i> Alpha
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Refresh Bar & Footer -->
    <div class="text-center mb-5">
        <button type="button" class="btn btn-outline-light rounded-pill px-4 btn-sm mb-3" onclick="window.location.reload()">
            <i class="bi bi-arrow-clockwise me-1"></i> Muat Ulang Pembaruan
        </button>
        <p class="text-muted small mb-0">
            &copy; <?= date('Y') ?> <strong>SI-ABSEN</strong>. Sistem Informasi Presensi & Pembinaan Terpadu.<br>
            Laporan ini dibuat otomatis dan dapat diakses kapan saja oleh orang tua murid.
        </p>
    </div>

</div>

<!-- Modal Zoom Foto Dokumentasi -->
<?php if (!empty($sesi['foto_kelas']) && file_exists(FCPATH . $sesi['foto_kelas'])): ?>
    <div class="modal fade" id="modalZoomFoto" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content bg-dark border-secondary">
                <div class="modal-header border-0 pb-0">
                    <h6 class="modal-title fw-bold text-white">
                        <i class="bi bi-images text-warning me-1"></i> Foto Kelas <?= esc($sesi['nama_kelas']) ?> - <?= date('d/m/Y', strtotime($tanggal)) ?>
                    </h6>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center p-3">
                    <img src="<?= base_url(esc($sesi['foto_kelas'])) ?>" alt="Zoom Foto" class="img-fluid rounded-3">
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Bootstrap 5 JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    function filterSiswa() {
        const query = document.getElementById('cariNamaInput').value.toLowerCase().trim();
        const items = document.querySelectorAll('.student-item');

        items.forEach(function(item) {
            const nama = item.getAttribute('data-nama');
            const nisn = item.getAttribute('data-nisn');
            if (nama.includes(query) || nisn.includes(query)) {
                item.style.display = 'flex';
            } else {
                item.style.display = 'none';
            }
        });
    }
</script>

</body>
</html>
