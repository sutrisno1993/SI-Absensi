<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="row justify-content-center align-items-center" style="min-height: 70vh;">
    <div class="col-12 col-md-8 col-lg-5">
        
        <div class="card card-custom p-4 p-md-5 border-0 shadow-lg">
            <div class="text-center mb-4">
                <div class="d-inline-flex align-items-center justify-content-center bg-warning text-white rounded-4 p-3 shadow-sm mb-3">
                    <i class="bi bi-shield-lock-fill fs-2"></i>
                </div>
                <h4 class="fw-bold text-dark mb-1">Pembaruan Password Akun</h4>
                <p class="text-muted small">Demi keamanan data sekolah, silakan buat password baru Anda (minimal 6 karakter).</p>
            </div>

            <form action="<?= site_url('auth/update-password') ?>" method="POST">
                <?= csrf_field() ?>

                <div class="mb-3">
                    <label for="password_baru" class="form-label fw-semibold text-secondary small">Password Baru</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-key"></i></span>
                        <input type="password" class="form-control bg-light border-start-0 py-2" id="password_baru" name="password_baru" placeholder="Masukkan password baru" required minlength="6">
                    </div>
                </div>

                <div class="mb-4">
                    <label for="konfirmasi_password" class="form-label fw-semibold text-secondary small">Ulangi Password Baru</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="bi bi-check2-circle"></i></span>
                        <input type="password" class="form-control bg-light border-start-0 py-2" id="konfirmasi_password" name="konfirmasi_password" placeholder="Ketik ulang password baru" required minlength="6">
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-primary-custom py-2 fw-bold">
                        <i class="bi bi-save me-2"></i> Simpan Password Baru & Masuk
                    </button>
                </div>
            </form>

        </div>

    </div>
</div>
<?= $this->endSection() ?>
