<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center align-items-center" style="min-height: 75vh;">
    <div class="col-12 col-md-8 col-lg-5 col-xl-4">
        
        <div class="card card-custom p-4 p-md-5 border-0 shadow-lg">
            <div class="text-center mb-4">
                <div class="d-inline-flex align-items-center justify-content-center bg-primary text-white rounded-4 p-3 shadow-sm mb-3">
                    <i class="bi bi-mortarboard-fill fs-2"></i>
                </div>
                <h3 class="fw-bold text-dark mb-1">Masuk SI-ABSEN</h3>
                <p class="text-muted small">Sistem Informasi Presensi & Pembinaan Siswa</p>
            </div>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 d-flex align-items-center gap-2 mb-3" role="alert">
                    <i class="bi bi-exclamation-triangle-fill fs-5 text-danger flex-shrink-0"></i>
                    <div class="small"><?= session()->getFlashdata('error') ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('success')): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 d-flex align-items-center gap-2 mb-3" role="alert">
                    <i class="bi bi-check-circle-fill fs-5 text-success flex-shrink-0"></i>
                    <div class="small"><?= session()->getFlashdata('success') ?></div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <form action="<?= site_url('auth/login') ?>" method="POST">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label for="username" class="form-label fw-semibold text-secondary small">Username</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-person"></i></span>
                        <input type="text" class="form-control bg-light border-start-0 py-2" id="username" name="username" value="<?= old('username') ?>" placeholder="Masukkan username" required autofocus>
                    </div>
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label fw-semibold text-secondary small">Password</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-lock"></i></span>
                        <input type="password" class="form-control bg-light border-start-0 py-2" id="password" name="password" placeholder="Masukkan password" required>
                    </div>
                </div>

                <div class="d-grid mb-2">
                    <button type="submit" class="btn btn-primary-custom py-2 fw-bold">
                        <i class="bi bi-box-arrow-in-right me-2"></i> Masuk Sekarang
                    </button>
                </div>
            </form>

        </div>

    </div>
</div>
<?= $this->endSection() ?>
