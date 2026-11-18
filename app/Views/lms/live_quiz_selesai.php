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
    <div class="rounded-circle bg-success bg-opacity-20 text-success p-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 75px; height: 75px; margin: 0 auto;">
        <i class="bi bi-check-circle-fill fs-1"></i>
    </div>

    <h4 class="fw-bold text-white mb-1">Jawaban Berhasil Terkirim!</h4>
    <p class="text-muted small mb-4">Terima kasih, <strong><?= esc($siswa['nama_siswa']) ?></strong>. Hasil pengerjaan Anda telah masuk ke sistem guru.</p>

    <!-- Ringkasan Nilai Pilihan Ganda -->
    <div class="p-4 bg-dark rounded-4 border border-secondary border-opacity-25 mb-4">
        <div class="text-muted small mb-1">Skor Pilihan Ganda (Otomatis):</div>
        <div class="display-4 fw-bold text-success mb-1">
            <?= number_format($hasil['total_skor'] ?? 0, 1) ?>
        </div>
        <div class="small text-muted">
            Benar <strong><?= $hasil['pg_benar'] ?? 0 ?></strong> soal PG
        </div>
    </div>

    <div class="alert alert-info py-2 px-3 small rounded-3 mb-0 text-start">
        <i class="bi bi-info-circle me-1"></i> Untuk butir soal essay (uraian), nilai akhir akan diperbarui setelah diperiksa dan dinilai langsung oleh Guru mata pelajaran.
    </div>
</div>

</body>
</html>

