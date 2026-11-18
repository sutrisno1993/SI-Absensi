<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title) ?> - SI-ABSEN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: #070a13; color: #f8fafc; }
        .card-quiz { background: #121a2d; border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 1.25rem; }
    </style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 p-3">

<div class="card card-quiz shadow-lg p-4 p-md-5 text-center" style="max-width: 480px; width: 100%;">
    <div class="rounded-circle bg-primary bg-opacity-20 text-primary p-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 70px; height: 70px; margin: 0 auto;">
        <i class="bi bi-broadcast fs-1"></i>
    </div>

    <span class="badge bg-primary bg-opacity-15 text-primary text-uppercase px-3 py-1 rounded-pill mb-2">
        <?= esc($asesmen['tipe']) ?>
    </span>
    <h4 class="fw-bold text-white mb-1"><?= esc($asesmen['judul_asesmen']) ?></h4>
    <p class="text-muted small mb-4">Kelas: <strong><?= esc($asesmen['nama_kelas']) ?></strong> &bull; Mapel: <?= esc($asesmen['nama_mapel']) ?></p>

    <div class="alert alert-dark text-start border-secondary small mb-4">
        <div class="fw-bold text-light mb-1"><i class="bi bi-info-circle me-1 text-primary"></i> Petunjuk Siswa:</div>
        Silakan pilih nama Anda pada daftar di bawah ini untuk memulai pengerjaan kuis/asesmen.
    </div>

    <form action="<?= site_url('live-quiz/' . $asesmen['token_live'] . '/kerjakan') ?>" method="GET">
        <div class="mb-4 text-start">
            <label class="form-label small fw-semibold text-secondary">Pilih Nama Anda di Kelas:</label>
            <select name="siswa_id" class="form-select form-select-lg bg-dark text-light border-secondary" required>
                <option value="">-- Pilih Nama Siswa --</option>
                <?php foreach ($siswaList as $s): ?>
                    <option value="<?= $s['id'] ?>"><?= esc($s['nama_siswa']) ?> (NISN: <?= esc($s['nisn']) ?>)</option>
                <?php endforeach; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow-sm">
            <i class="bi bi-pencil-square me-2"></i> Mulai Mengerjakan
        </button>
    </form>
</div>

</body>
</html>

