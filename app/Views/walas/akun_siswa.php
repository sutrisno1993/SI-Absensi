<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= site_url('walas') ?>" class="text-decoration-none text-muted">Dashboard</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Akun Perwakilan Kelas</li>
            </ol>
        </nav>
        <h3 class="fw-bold mb-1 d-flex align-items-center gap-2">
            <i class="bi bi-person-badge-fill text-primary"></i> 
            <span>Akun Perwakilan Kelas: <?= esc($kelas['nama_kelas']) ?></span>
            <span class="badge bg-primary bg-opacity-20 text-primary border border-primary border-opacity-25 fs-6 fw-normal">
                1 Akun per Rombel
            </span>
        </h3>
        <p class="text-muted small mb-0">Kelola 1 akun resmi yang digunakan oleh ketua kelas / penanggung jawab (PJ) untuk input presensi dan dokumentasi kelas.</p>
    </div>

    <!-- Tombol Aksi -->
    <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-primary-custom rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalGenerateAkun">
            <i class="bi bi-gear-fill me-1"></i> <?= $akunKelas ? 'Kelola / Ganti Password' : 'Buat Akun Kelas' ?>
        </button>
    </div>
</div>

<?php if (session()->getFlashdata('last_generated_username')): ?>
    <!-- Alert Kredensial Baru Dibuat -->
    <div class="card border-0 shadow-sm mb-4 bg-success bg-opacity-10 border-start border-4 border-success">
        <div class="card-body p-4">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <h5 class="fw-bold text-success mb-1 d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill"></i> Kredensial Akun Berhasil Diperbarui!
                    </h5>
                    <p class="text-light small mb-2">Segera salin informasi login di bawah ini untuk diserahkan kepada Ketua Kelas / PJ Kelas Anda:</p>
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <span class="badge bg-dark text-white border px-3 py-2 font-monospace fs-6">
                            Username: <strong><?= esc(session()->getFlashdata('last_generated_username')) ?></strong>
                        </span>
                        <span class="badge bg-dark text-warning border px-3 py-2 font-monospace fs-6">
                            Password: <strong><?= esc(session()->getFlashdata('last_generated_password')) ?></strong>
                        </span>
                    </div>
                </div>
                <div>
                    <button type="button" class="btn btn-success rounded-pill px-3 fw-semibold" onclick="salinKredensialFlash()">
                        <i class="bi bi-clipboard-check me-1"></i> Salin Format WhatsApp
                    </button>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Kartu Utama Akun Khusus Perwakilan Kelas -->
<div class="card card-custom border-0 shadow-sm mb-4">
    <div class="card-body p-4">
        <div class="row align-items-center g-4">
            <div class="col-12 col-lg-7">
                <div class="d-flex gap-3 align-items-start">
                    <div class="bg-primary bg-opacity-15 text-primary p-3 rounded-4 fs-2 flex-shrink-0">
                        <i class="bi bi-shield-lock-fill"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2 mb-1">
                            <h5 class="fw-bold text-white mb-0">Status Akun Presensi Kelas <?= esc($kelas['nama_kelas']) ?></h5>
                            <?php if ($akunKelas): ?>
                                <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-25 px-2 py-1">
                                    <i class="bi bi-check-circle me-1"></i> Aktif
                                </span>
                            <?php else: ?>
                                <span class="badge bg-warning bg-opacity-20 text-warning border border-warning border-opacity-25 px-2 py-1">
                                    <i class="bi bi-exclamation-triangle me-1"></i> Belum Dibuat
                                </span>
                            <?php endif; ?>
                        </div>
                        <p class="text-muted small mb-3">
                            Akun ini digunakan perwakilan kelas untuk login ke portal presensi, menginput kehadiran siswa harian, mengunggah foto suasana kelas, dan membagikan link live ke grup WhatsApp orang tua.
                        </p>

                        <?php if ($akunKelas): ?>
                            <div class="p-3 rounded-3 bg-dark border border-secondary border-opacity-25 mb-3">
                                <div class="row g-2">
                                    <div class="col-12 col-sm-6">
                                        <div class="text-muted small">Username Login:</div>
                                        <div class="fw-bold font-monospace fs-5 text-primary">
                                            <?= esc($akunKelas['username']) ?>
                                        </div>
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <div class="text-muted small">Hak Akses Role:</div>
                                        <div class="fw-semibold text-white">
                                            <span class="badge bg-info bg-opacity-20 text-info border border-info border-opacity-25">
                                                Perwakilan Kelas (PJ)
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="d-flex flex-wrap gap-2">
                                <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#modalGenerateAkun">
                                    <i class="bi bi-pencil-square me-1"></i> Ganti Username / Password
                                </button>
                                <form action="<?= site_url('walas/akun-siswa/reset-kelas') ?>" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin mereset password akun kelas ini dengan password baru acak?');" class="d-inline">
                                    <?= csrf_field() ?>
                                    <button type="submit" class="btn btn-outline-warning btn-sm rounded-pill px-3">
                                        <i class="bi bi-key me-1"></i> Reset Password Acak
                                    </button>
                                </form>
                                <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3" onclick="salinInfoAkunAktif('<?= esc($akunKelas['username']) ?>')">
                                    <i class="bi bi-whatsapp me-1"></i> Salin Format WA untuk PJ
                                </button>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning border-0 rounded-3 small mb-3">
                                <i class="bi bi-info-circle-fill me-1"></i> Kelas ini belum memiliki akun perwakilan. Klik tombol di bawah untuk membuat akun otomatis.
                            </div>
                            <button type="button" class="btn btn-primary-custom rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalGenerateAkun">
                                <i class="bi bi-plus-circle me-1"></i> Buat Akun Perwakilan Kelas Sekarang
                            </button>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="col-12 col-lg-5">
                <div class="p-3 rounded-3 bg-light bg-opacity-5 border border-secondary border-opacity-25">
                    <h6 class="fw-bold text-white mb-2 d-flex align-items-center gap-2">
                        <i class="bi bi-lightbulb-fill text-warning"></i> Panduan Wali Kelas
                    </h6>
                    <ul class="text-muted small mb-0 ps-3">
                        <li class="mb-1">Hanya <strong>1 akun khusus</strong> yang diberikan ke pengurus / PJ kelas.</li>
                        <li class="mb-1">Perwakilan kelas dapat login dari HP masing-masing melalui alamat web sekolah.</li>
                        <li class="mb-1">Jika ada pergantian pengurus kelas, Anda cukup klik <strong>Reset Password</strong> kapan saja.</li>
                        <li>Siswa perwakilan dapat memotret suasana kelas dan langsung share link presensi ke grup WA orang tua.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Referensi Daftar Siswa Rombel -->
<div class="card card-custom border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
        <div>
            <h5 class="fw-bold mb-0 d-flex align-items-center gap-2">
                <i class="bi bi-people-fill text-primary"></i> Daftar Siswa Rombel <?= esc($kelas['nama_kelas']) ?>
            </h5>
            <small class="text-muted">Total terdaftar: <?= count($siswaList) ?> Siswa aktif di kelas ini.</small>
        </div>
        <div>
            <span class="badge bg-secondary bg-opacity-20 text-light border px-3 py-2">
                Wali Kelas: <?= esc(session()->get('nama_lengkap') ?? 'Bapak/Ibu Guru') ?>
            </span>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-custom table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="text-center" style="width: 50px;">No</th>
                    <th style="width: 140px;">NISN</th>
                    <th>Nama Lengkap Siswa</th>
                    <th class="text-center" style="width: 130px;">Jenis Kelamin</th>
                    <th class="text-center" style="width: 120px;">Status</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($siswaList)): ?>
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="bi bi-people fs-1 d-block mb-2"></i> Belum ada data siswa di rombel ini.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($siswaList as $idx => $s): ?>
                        <tr>
                            <td class="text-center fw-semibold text-muted"><?= $idx + 1 ?></td>
                            <td><span class="badge bg-dark text-light border font-monospace"><?= esc($s['nisn']) ?></span></td>
                            <td class="fw-bold text-white"><?= esc($s['nama_siswa']) ?></td>
                            <td class="text-center">
                                <?php if (($s['jenis_kelamin'] ?? '') === 'L'): ?>
                                    <span class="badge bg-primary bg-opacity-20 text-primary border border-primary border-opacity-25">Laki-laki</span>
                                <?php else: ?>
                                    <span class="badge bg-danger bg-opacity-20 text-danger border border-danger border-opacity-25">Perempuan</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-25">
                                    <?= esc($s['status'] ?? 'Aktif') ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Generate / Edit Akun Kelas -->
<div class="modal fade" id="modalGenerateAkun" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content card-custom border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2">
                    <i class="bi bi-person-gear text-primary"></i>
                    <span><?= $akunKelas ? 'Kelola Akun Perwakilan Kelas' : 'Buat Akun Perwakilan Kelas' ?></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= site_url('walas/akun-siswa/generate') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body py-4">
                    <div class="alert alert-info border-0 rounded-3 small mb-3">
                        <i class="bi bi-info-circle-fill me-1"></i> Anda dapat menentukan username dan password kustom, atau biarkan default/kosong agar sistem men-generate otomatis.
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Username Akun</label>
                        <input type="text" name="username" class="form-control" value="<?= esc($akunKelas['username'] ?? $suggestedUsername) ?>" placeholder="Contoh: <?= esc($suggestedUsername) ?>" required>
                        <div class="form-text text-muted small">Disarankan menggunakan format huruf kecil tanpa spasi.</div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-secondary">Password Akun</label>
                        <div class="input-group">
                            <input type="text" name="password" id="inputPasswordModal" class="form-control font-monospace" placeholder="Kosongkan untuk acak 6 digit...">
                            <button type="button" class="btn btn-outline-secondary" onclick="generateRandomPin()">
                                <i class="bi bi-shuffle me-1"></i> Buat Acak
                            </button>
                        </div>
                        <div class="form-text text-muted small">Jika dikosongkan, password acak 6 digit akan otomatis dibuatkan.</div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary-custom rounded-pill px-4">
                        <i class="bi bi-check-circle-fill me-1"></i> Simpan & Terapkan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function generateRandomPin() {
        const pin = Math.floor(100000 + Math.random() * 900000);
        document.getElementById('inputPasswordModal').value = pin;
    }

    function salinKredensialFlash() {
        const u = "<?= esc(session()->getFlashdata('last_generated_username') ?? '') ?>";
        const p = "<?= esc(session()->getFlashdata('last_generated_password') ?? '') ?>";
        const urlLogin = "<?= site_url('auth/login') ?>";
        const kelas = "<?= esc($kelas['nama_kelas']) ?>";

        const text = `Assalamu'alaikum Warahmatullahi Wabarakatuh,\n\nBerikut adalah akun resmi Perwakilan Kelas / PJ Presensi ${kelas}:\n\n🔗 Link Login: ${urlLogin}\n👤 Username: ${u}\n🔑 Password: ${p}\n\nHarap simpan dan gunakan akun ini untuk menginput presensi harian teman sekelas dan upload foto kegiatan kelas. Terima kasih.\n- Wali Kelas`;

        navigator.clipboard.writeText(text).then(() => {
            alert("Format pesan WhatsApp berhasil disalin ke clipboard! Silakan paste ke chat WhatsApp Ketua Kelas / PJ.");
        }).catch(() => {
            prompt("Salin teks di bawah ini:", text);
        });
    }

    function salinInfoAkunAktif(username) {
        const urlLogin = "<?= site_url('auth/login') ?>";
        const kelas = "<?= esc($kelas['nama_kelas']) ?>";

        const text = `Assalamu'alaikum Warahmatullahi Wabarakatuh,\n\nBerikut adalah akun resmi Perwakilan Kelas / PJ Presensi ${kelas}:\n\n🔗 Link Login: ${urlLogin}\n👤 Username: ${username}\n🔑 Password: (Sesuai password yang telah ditentukan oleh Wali Kelas)\n\nSilakan gunakan akun ini untuk menginput presensi harian teman sekelas dan dokumentasi kegiatan kelas. Terima kasih.`;

        navigator.clipboard.writeText(text).then(() => {
            alert("Format pesan WhatsApp akun berhasil disalin ke clipboard!");
        }).catch(() => {
            prompt("Salin teks di bawah ini:", text);
        });
    }
</script>
<?= $this->endSection() ?>
