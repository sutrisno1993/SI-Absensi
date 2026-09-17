<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center align-items-center" style="min-height: 60vh;">
    <div class="col-12 col-md-6 text-center">
        <div class="card card-custom p-5 border-0 shadow-sm">
            <div class="text-warning mb-3">
                <i class="bi bi-exclamation-circle-fill" style="font-size: 4rem;"></i>
            </div>
            <h4 class="fw-bold text-dark">Belum Ditugaskan Sebagai Wali Kelas</h4>
            <p class="text-muted">Akun guru Anda saat ini belum dipetakan ke kelas manapun oleh Administrator. Silakan hubungi Administrator sekolah untuk melakukan mapping kelas ampunan.</p>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
