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
        .card-quiz { background: #121a2d; border: 1px solid rgba(255, 255, 255, 0.08); border-radius: 1rem; }
        .soal-box { background: #0d1322; border: 1px solid rgba(255, 255, 255, 0.06); border-radius: 0.85rem; }
    </style>
</head>
<body class="py-4">

<div class="container" style="max-width: 780px;">

    <!-- Header Soal Siswa -->
    <div class="card card-quiz p-3 p-md-4 mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <span class="badge bg-primary bg-opacity-20 text-primary text-uppercase px-2 py-1 rounded-pill mb-1">
                    <?= esc($asesmen['tipe']) ?>
                </span>
                <h4 class="fw-bold text-white mb-0"><?= esc($asesmen['judul_asesmen']) ?></h4>
                <small class="text-muted">Nama: <strong class="text-white"><?= esc($siswa['nama_siswa']) ?></strong> (NISN: <?= esc($siswa['nisn']) ?>)</small>
            </div>
            <div class="text-end">
                <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-30 px-3 py-2 rounded-pill">
                    <i class="bi bi-stopwatch me-1"></i> <?= $asesmen['durasi_menit'] ?> Menit
                </span>
            </div>
        </div>
    </div>

    <?php if ($sudahKerja): ?>
        <div class="alert alert-warning text-center p-4 rounded-4 shadow">
            <i class="bi bi-check-circle-fill display-4 text-warning mb-2 d-block"></i>
            <h5 class="fw-bold">Anda Sudah Mengumpulkan Jawaban!</h5>
            <p class="small mb-0">Jawaban Anda untuk asesmen ini sudah tersimpan di sistem guru.</p>
        </div>
    <?php else: ?>
        <form action="<?= site_url('live-quiz/' . $asesmen['token_live'] . '/submit') ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mengumpulkan seluruh lembar jawaban?')">
            <?= csrf_field() ?>
            <input type="hidden" name="siswa_id" value="<?= $siswa['id'] ?>">

            <?php foreach ($soalList as $idx => $s): ?>
                <div class="card card-quiz p-4 mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge bg-secondary bg-opacity-20 text-light border px-2 py-1 rounded-pill">
                            Nomor <?= $s['nomor_urut'] ?> &bull; <?= strtoupper($s['tipe_soal']) ?>
                        </span>
                        <small class="text-muted fw-bold"><?= $s['bobot'] ?> Poin</small>
                    </div>

                    <h6 class="fw-bold text-white mb-3" style="line-height: 1.6;"><?= nl2br(esc($s['pertanyaan'])) ?></h6>

                    <?php if ($s['tipe_soal'] === 'pg'): ?>
                        <div class="d-flex flex-column gap-2">
                            <?php foreach (['a', 'b', 'c', 'd', 'e'] as $opt): ?>
                                <?php if (!empty($s['opsi_' . $opt])): ?>
                                    <div class="form-check p-0">
                                        <input class="btn-check" type="radio" name="pg[<?= $s['id'] ?>]" id="opt_<?= $s['id'] ?>_<?= $opt ?>" value="<?= $opt ?>" required>
                                        <label class="btn btn-outline-secondary w-100 text-start p-3 rounded-3 text-light d-flex align-items-center gap-2" for="opt_<?= $s['id'] ?>_<?= $opt ?>">
                                            <span class="badge bg-dark border text-uppercase" style="width: 28px;"><?= $opt ?></span>
                                            <span><?= esc($s['opsi_' . $opt]) ?></span>
                                        </label>
                                    </div>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <!-- Input Essay Siswa -->
                        <div>
                            <label class="small text-muted mb-1">Tuliskan Jawaban Uraian Anda:</label>
                            <textarea class="form-control bg-dark text-light border-secondary" name="essay[<?= $s['id'] ?>]" rows="4" placeholder="Ketikkan jawaban Anda secara rinci di sini..." required></textarea>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>

            <div class="text-center mt-4 mb-5">
                <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 py-3 fw-bold shadow-lg">
                    <i class="bi bi-send-check me-2"></i> Kirim Semua Jawaban
                </button>
            </div>
        </form>
    <?php endif; ?>

</div>

</body>
</html>

