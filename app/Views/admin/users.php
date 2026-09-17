<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="row mb-4 align-items-center">
    <div class="col-12 col-md-6">
        <h3 class="fw-bold text-dark mb-1">
            <i class="bi bi-shield-lock text-primary me-2"></i>Kelola Akun Pengguna
        </h3>
        <p class="text-muted small mb-0">Manajemen akun pengguna sistem, hak akses peran (Role), dan reset password.</p>
    </div>
    <div class="col-12 col-md-6 text-md-end mt-3 mt-md-0">
        <button type="button" class="btn btn-primary-custom" onclick="tambahUser()">
            <i class="bi bi-person-plus-fill me-1"></i> Buat Akun Baru
        </button>
    </div>
</div>

<div class="card card-custom border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-custom table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="text-center" style="width: 50px;">No</th>
                    <th style="width: 180px;">Username</th>
                    <th style="width: 130px;">Peran (Role)</th>
                    <th>Entitas Terkait (Ref ID)</th>
                    <th class="text-center" style="width: 130px;">First Login</th>
                    <th class="text-end" style="width: 200px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">Belum ada akun pengguna.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($users as $idx => $u): ?>
                        <tr>
                            <td class="text-center fw-semibold text-muted"><?= $idx + 1 ?></td>
                            <td><span class="fw-bold text-dark font-monospace"><?= esc($u['username']) ?></span></td>
                            <td>
                                <?php if ($u['role'] === 'admin'): ?>
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">Admin</span>
                                <?php elseif ($u['role'] === 'walas'): ?>
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary">Walas</span>
                                <?php elseif ($u['role'] === 'guru_piket'): ?>
                                    <span class="badge bg-warning bg-opacity-20 text-warning-emphasis border border-warning">Guru Piket</span>
                                <?php else: ?>
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info">PJ Kelas</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php
                                if ($u['role'] === 'walas' || $u['role'] === 'guru_piket') {
                                    $g = array_filter($guruList, fn($item) => $item['id'] == $u['ref_id']);
                                    $g = reset($g);
                                    echo $g ? '<i class="bi bi-person me-1"></i> Guru: ' . esc($g['nama_guru']) : '<span class="text-muted">-</span>';
                                } elseif ($u['role'] === 'pj_kelas') {
                                    $k = array_filter($kelasList, fn($item) => $item['id'] == $u['ref_id']);
                                    $k = reset($k);
                                    echo $k ? '<i class="bi bi-door-open me-1"></i> Kelas: ' . esc($k['nama_kelas']) : '<span class="text-muted">-</span>';
                                } else {
                                    echo '<span class="text-muted">Akses Global</span>';
                                }
                                ?>
                            </td>
                            <td class="text-center">
                                <?php if ((int)$u['is_first_login'] === 1): ?>
                                    <span class="badge bg-warning text-dark">Wajib Ganti</span>
                                <?php else: ?>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success">Aktif</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <a href="<?= site_url('admin/users/reset-password/' . $u['id']) ?>" class="btn btn-sm btn-outline-warning rounded-pill px-2 py-1" onclick="return confirm('Reset password akun <?= $u['username'] ?> ke password default?')" title="Reset Password">
                                    <i class="bi bi-arrow-counterclockwise me-1"></i> Reset Pwd
                                </a>
                                <?php if ((int)$u['id'] !== (int)session()->get('user_id')): ?>
                                    <a href="<?= site_url('admin/users/hapus/' . $u['id']) ?>" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1 ms-1" onclick="return confirm('Hapus akun ini?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Form User -->
<div class="modal fade" id="modalUser" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark">Buat Akun Pengguna Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= site_url('admin/users/simpan') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="user_id">
                
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <label for="user_username" class="form-label small fw-semibold text-secondary">Username <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="user_username" name="username" required placeholder="Contoh: walas_ipa1 / pj_xii_ak1">
                    </div>

                    <div class="mb-3">
                        <label for="user_password" class="form-label small fw-semibold text-secondary">Password <span class="text-danger">*</span></label>
                        <input type="password" class="form-control" id="user_password" name="password" required minlength="6" placeholder="Minimal 6 karakter">
                    </div>

                    <div class="mb-3">
                        <label for="user_role" class="form-label small fw-semibold text-secondary">Peran Akun (Role) <span class="text-danger">*</span></label>
                        <select class="form-select" id="user_role" name="role" required onchange="handleRoleChange()">
                            <option value="">-- Pilih Role --</option>
                            <option value="admin">Administrator</option>
                            <option value="walas">Wali Kelas (Walas)</option>
                            <option value="guru_piket">Guru Piket</option>
                            <option value="pj_kelas">Penanggung Jawab Kelas (PJ Kelas)</option>
                        </select>
                    </div>

                    <div class="mb-3 d-none" id="group_ref_guru">
                        <label for="ref_id_guru" class="form-label small fw-semibold text-secondary">Pilih Data Guru Terkait</label>
                        <select class="form-select" id="ref_id_guru" name="ref_id_guru">
                            <option value="">-- Pilih Guru --</option>
                            <?php foreach ($guruList as $g): ?>
                                <option value="<?= $g['id'] ?>"><?= esc($g['nama_guru']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3 d-none" id="group_ref_kelas">
                        <label for="ref_id_kelas" class="form-label small fw-semibold text-secondary">Pilih Data Kelas Terkait</label>
                        <select class="form-select" id="ref_id_kelas" name="ref_id_kelas">
                            <option value="">-- Pilih Kelas --</option>
                            <?php foreach ($kelasList as $k): ?>
                                <option value="<?= $k['id'] ?>"><?= esc($k['nama_kelas']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary-custom px-4">Buat Akun</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    var modalUser = new bootstrap.Modal(document.getElementById('modalUser'));

    function tambahUser() {
        document.getElementById('user_id').value = '';
        document.getElementById('user_username').value = '';
        document.getElementById('user_password').value = '';
        document.getElementById('user_role').value = '';
        handleRoleChange();
        modalUser.show();
    }

    function handleRoleChange() {
        var role = document.getElementById('user_role').value;
        var groupGuru = document.getElementById('group_ref_guru');
        var groupKelas = document.getElementById('group_ref_kelas');

        groupGuru.classList.add('d-none');
        groupKelas.classList.add('d-none');

        if (role === 'walas' || role === 'guru_piket') {
            groupGuru.classList.remove('d-none');
        } else if (role === 'pj_kelas') {
            groupKelas.classList.remove('d-none');
        }
    }
</script>
<?= $this->endSection() ?>
