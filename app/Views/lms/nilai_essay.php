<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="row align-items-center mb-4">
    <div class="col-12 col-md-8">
        <a href="<?= site_url('lms/asesmen/' . $asesmen['id']) ?>" class="text-secondary small text-decoration-none d-inline-flex align-items-center mb-1">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Papan Asesmen Live
        </a>
        <h3 class="fw-bold text-dark mb-1">
            <i class="bi bi-person-check text-primary me-2"></i>Koreksi Jawaban: <?= esc($siswa['nama_siswa']) ?>
        </h3>
        <div class="text-muted small">
            NISN: <span class="font-monospace fw-bold"><?= esc($siswa['nisn']) ?></span> &bull; Asesmen: <strong><?= esc($asesmen['judul_asesmen']) ?></strong>
        </div>
    </div>
</div>

<div class="card card-custom border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 py-3">
        <h5 class="fw-bold text-dark mb-0">Daftar Jawaban Siswa</h5>
    </div>

    <form action="<?= site_url('lms/asesmen/' . $asesmen['id'] . '/koreksi-essay/' . $siswa['id']) ?>" method="POST">
        <?= csrf_field() ?>
        <div class="card-body p-4">
            <?php foreach ($jawabanList as $idx => $j): ?>
                <div class="p-3 border rounded-3 mb-3 bg-light">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="badge <?= $j['tipe_soal'] === 'pg' ? 'bg-info' : 'bg-warning text-dark' ?> rounded-pill">
                            Soal No. <?= $j['nomor_urut'] ?> &bull; <?= strtoupper($j['tipe_soal']) ?>
                        </span>
                        <span class="badge bg-white text-dark border">Maksimal: <?= $j['bobot'] ?> Poin</span>
                    </div>

                    <div class="fw-bold text-dark mb-2"><?= nl2br(esc($j['pertanyaan'])) ?></div>

                    <?php if ($j['tipe_soal'] === 'pg'): ?>
                        <div class="alert alert-secondary py-2 small mb-0 d-flex justify-content-between align-items-center">
                            <div>
                                Jawaban Siswa: <strong class="text-uppercase"><?= esc($j['jawaban_pg'] ?: '-') ?></strong>
                            </div>
                            <div>
                                <?php if ($j['is_benar'] == 1): ?>
                                    <span class="badge bg-success"><i class="bi bi-check-circle me-1"></i>Benar (+<?= $j['skor'] ?> Poin)</span>
                                <?php else: ?>
                                    <span class="badge bg-danger"><i class="bi bi-x-circle me-1"></i>Salah (0 Poin)</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php else: ?>
                        <!-- Jawaban Essay -->
                        <div class="mb-2">
                            <label class="small text-secondary fw-semibold">Jawaban Yang Ditulis Siswa:</label>
                            <div class="p-3 bg-white rounded border text-dark font-monospace small">
                                <?= !empty($j['jawaban_essay']) ? nl2br(esc($j['jawaban_essay'])) : '<em class="text-muted">Tidak ada jawaban</em>' ?>
                            </div>
                        </div>

                        <?php if (!empty($j['pedoman_essay'])): ?>
                            <div class="small text-success mb-3 p-2 bg-success bg-opacity-10 border border-success border-opacity-25 rounded">
                                <i class="bi bi-lightbulb-fill me-1"></i><strong>Kunci / Pedoman Guru:</strong> <?= esc($j['pedoman_essay']) ?>
                            </div>
                        <?php endif; ?>

                        <div class="row g-2 align-items-center">
                            <div class="col-12 col-md-3">
                                <label class="small text-secondary fw-semibold">Beri Nilai Poin (0 - <?= $j['bobot'] ?>):</label>
                                <input type="number" step="0.5" min="0" max="<?= $j['bobot'] ?>" class="form-control" name="skor[<?= $j['id'] ?>]" value="<?= $j['skor'] ?>" required>
                            </div>
                            <div class="col-12 col-md-9">
                                <label class="small text-secondary fw-semibold">Catatan Evaluasi Guru (Opsional):</label>
                                <input type="text" class="form-control" name="catatan[<?= $j['id'] ?>]" value="<?= esc($j['catatan_guru'] ?? '') ?>" placeholder="Umpan balik untuk siswa...">
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="card-footer bg-white border-0 py-3 text-end">
            <a href="<?= site_url('lms/asesmen/' . $asesmen['id']) ?>" class="btn btn-light rounded-pill px-4 me-2">Batal</a>
            <button type="submit" class="btn btn-primary-custom rounded-pill px-4 fw-semibold shadow-sm">
                <i class="bi bi-check2-circle me-1"></i> Simpan Penilaian Siswa
            </button>
        </div>
    </form>
</div>
<?= $this->endSection() ?>

