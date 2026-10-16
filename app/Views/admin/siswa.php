<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="row mb-4 align-items-center">
    <div class="col-12 col-md-5">
        <h3 class="fw-bold text-dark mb-1">
            <i class="bi bi-people text-primary me-2"></i>Master Data Siswa
        </h3>
        <p class="text-muted small mb-0">Kelola basis data seluruh siswa dan penempatan kelas.</p>
    </div>
    <div class="col-12 col-md-7 text-md-end mt-3 mt-md-0 d-flex flex-wrap justify-content-md-end gap-2">
        <!-- Filter Kelas & Status -->
        <form action="<?= site_url('admin/siswa') ?>" method="GET" class="d-flex align-items-center gap-2">
            <select name="kelas_id" class="form-select form-select-sm w-auto shadow-sm" onchange="this.form.submit()">
                <option value="">-- Semua Kelas --</option>
                <?php foreach ($kelasList as $k): ?>
                    <option value="<?= $k['id'] ?>" <?= $selectedKelasId == $k['id'] ? 'selected' : '' ?>><?= esc($k['nama_kelas']) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="status" class="form-select form-select-sm w-auto shadow-sm" onchange="this.form.submit()">
                <option value="">-- Semua Status --</option>
                <option value="Aktif" <?= ($selectedStatus ?? '') === 'Aktif' ? 'selected' : '' ?>>Hanya Aktif</option>
                <option value="Nonaktif" <?= ($selectedStatus ?? '') === 'Nonaktif' ? 'selected' : '' ?>>Hanya Nonaktif</option>
            </select>
            <?php if (!empty($selectedKelasId) || !empty($selectedStatus)): ?>
                <a href="<?= site_url('admin/siswa') ?>" class="btn btn-sm btn-outline-secondary" title="Reset Filter"><i class="bi bi-x-circle"></i></a>
            <?php endif; ?>
        </form>

        <!-- Form Auto-Detect Gender 1 Klik -->
        <form action="<?= site_url('admin/siswa/auto-detect-gender') ?>" method="POST" class="d-inline" onsubmit="return confirm('Sistem akan mendeteksi otomatis Jenis Kelamin (L/P) seluruh siswa berdasarkan pola nama. Lanjutkan?')">
            <?= csrf_field() ?>
            <?php if (!empty($selectedKelasId)): ?>
                <input type="hidden" name="kelas_id" value="<?= esc($selectedKelasId) ?>">
            <?php endif; ?>
            <button type="submit" class="btn btn-outline-info text-dark fw-semibold" title="Deteksi Otomatis L/P berdasarkan nama siswa">
                <i class="bi bi-magic me-1"></i> Auto-Detect Gender
            </button>
        </form>

        <button type="button" class="btn btn-outline-secondary fw-semibold" data-bs-toggle="modal" data-bs-target="#modalGenderBatchAdmin">
            <i class="bi bi-gender-ambiguous me-1"></i> Ceklis Gender Cepat
        </button>

        <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#modalImportSiswa">
            <i class="bi bi-file-earmark-excel me-1"></i> Import Excel
        </button>
        <button type="button" class="btn btn-primary-custom" onclick="tambahSiswa()">
            <i class="bi bi-plus-circle-fill me-1"></i> Tambah Siswa Baru
        </button>
    </div>
</div>

<div class="card card-custom border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-custom table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="text-center" style="width: 45px;">No</th>
                    <th style="width: 130px;">NISN</th>
                    <th>Nama Lengkap Siswa</th>
                    <th class="text-center" style="width: 60px;">L/P</th>
                    <th style="width: 130px;">Kelas</th>
                    <th style="width: 200px;">Status & Mutasi</th>
                    <th class="text-end" style="width: 220px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($siswaList)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">Belum ada data siswa.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($siswaList as $idx => $s): ?>
                        <tr>
                            <td class="text-center fw-semibold text-muted"><?= $idx + 1 ?></td>
                            <td><span class="badge bg-light text-dark border font-monospace"><?= esc($s['nisn']) ?></span></td>
                            <td class="fw-bold text-dark fs-6"><?= esc($s['nama_siswa']) ?></td>
                            <td class="text-center">
                                <span class="badge <?= ($s['jenis_kelamin'] ?? 'L') === 'L' ? 'bg-info bg-opacity-15 text-primary border border-info border-opacity-25' : 'bg-danger bg-opacity-15 text-danger border border-danger border-opacity-25' ?> rounded-pill px-2 py-1">
                                    <?= esc($s['jenis_kelamin'] ?? 'L') ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-2 py-1">
                                    <?= esc($s['nama_kelas']) ?>
                                </span>
                            </td>
                            <td>
                                <?php if (($s['status'] ?? 'Aktif') === 'Aktif'): ?>
                                    <span class="badge bg-success bg-opacity-20 text-success border border-success border-opacity-50 px-2 py-1 rounded-pill">
                                        <i class="bi bi-check-circle-fill me-1"></i> Aktif
                                    </span>
                                <?php else: ?>
                                    <div>
                                        <span class="badge bg-danger bg-opacity-20 text-danger border border-danger border-opacity-50 px-2 py-1 rounded-pill mb-1">
                                            <i class="bi bi-slash-circle-fill me-1"></i> Nonaktif
                                        </span>
                                        <?php if (!empty($s['alasan_nonaktif'])): ?>
                                            <span class="badge bg-secondary bg-opacity-15 text-dark border border-secondary border-opacity-25 rounded-pill px-2 py-1 mb-1">
                                                <?= esc(ucwords(str_replace('_', ' ', $s['alasan_nonaktif']))) ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($s['tanggal_nonaktif'])): ?>
                                            <div class="small text-muted" style="font-size: 0.75rem;">
                                                <i class="bi bi-calendar-event me-1"></i> <?= date('d/m/Y', strtotime($s['tanggal_nonaktif'])) ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($s['file_surat_nonaktif'])): ?>
                                            <div class="mt-1">
                                                <a href="<?= base_url('uploads/mutasi/' . $s['file_surat_nonaktif']) ?>" target="_blank" class="btn btn-xs btn-outline-primary py-0 px-2 rounded-pill shadow-xs" style="font-size: 0.72rem;">
                                                    <i class="bi bi-file-earmark-arrow-down me-1"></i> Surat Pendukung
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php if (($s['status'] ?? 'Aktif') === 'Aktif'): ?>
                                    <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-2 py-1" title="Nyatakan Siswa Keluar / Mutasi Nonaktif" onclick="bukaModalNonaktifAdmin(<?= htmlspecialchars(json_encode($s), ENT_QUOTES, 'UTF-8') ?>)">
                                        <i class="bi bi-person-x"></i> Mutasi Keluar
                                    </button>
                                <?php else: ?>
                                    <form action="<?= site_url('admin/siswa/toggle-status/' . $s['id']) ?>" method="POST" class="d-inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-success rounded-pill px-2 py-1" title="Aktifkan Siswa" onclick="return confirm('Aktifkan kembali siswa <?= esc($s['nama_siswa']) ?>?')">
                                            <i class="bi bi-person-check"></i> Aktifkan
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1 ms-1" onclick="editSiswa(<?= htmlspecialchars(json_encode($s), ENT_QUOTES, 'UTF-8') ?>)">
                                    <i class="bi bi-pencil-square"></i>
                                </button>
                                <a href="<?= site_url('admin/siswa/hapus/' . $s['id']) ?>" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1 ms-1" onclick="return confirm('Apakah Anda yakin ingin menghapus data siswa ini?')">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Form Siswa -->
<div class="modal fade" id="modalSiswa" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="modalSiswaTitle">Tambah Siswa</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= site_url('admin/siswa/simpan') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="siswa_id">
                <div class="modal-body py-4">
                    <div class="mb-3">
                        <label for="siswa_nisn" class="form-label small fw-semibold text-secondary">NISN (Nomor Induk Siswa Nasional) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="siswa_nisn" name="nisn" required placeholder="Contoh: 0061234501">
                    </div>
                    <div class="mb-3">
                        <label for="siswa_nama" class="form-label small fw-semibold text-secondary">Nama Lengkap Siswa <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="siswa_nama" name="nama_siswa" required placeholder="Contoh: Ahmad Fauzi">
                    </div>
                    <div class="mb-3">
                        <label for="siswa_jk" class="form-label small fw-semibold text-secondary">Jenis Kelamin <span class="text-danger">*</span></label>
                        <select class="form-select" id="siswa_jk" name="jenis_kelamin" required>
                            <option value="L">Laki-Laki (L)</option>
                            <option value="P">Perempuan (P)</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="siswa_kelas_id" class="form-label small fw-semibold text-secondary">Pilih Kelas Siswa <span class="text-danger">*</span></label>
                        <select class="form-select" id="siswa_kelas_id" name="kelas_id" required>
                            <option value="">-- Pilih Kelas --</option>
                            <?php foreach ($kelasList as $k): ?>
                                <option value="<?= $k['id'] ?>"><?= esc($k['nama_kelas']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="siswa_status" class="form-label small fw-semibold text-secondary">Status Siswa <span class="text-danger">*</span></label>
                        <select class="form-select" id="siswa_status" name="status" required>
                            <option value="Aktif">Aktif (Mengikuti Presensi & KBM)</option>
                            <option value="Nonaktif">Nonaktif (Pindah / Mengundurkan Diri - Tidak Muncul di Presensi)</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary-custom px-4">Simpan Siswa</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Import Excel Siswa -->
<div class="modal fade" id="modalImportSiswa" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-success text-white rounded-3 p-2">
                        <i class="bi bi-file-earmark-excel-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark">Import Siswa dari Excel</h5>
                        <p class="text-muted small mb-0">Unggah berkas spreadsheet .xlsx, .xls, atau .csv</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= site_url('admin/siswa/import') ?>" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-body py-4">
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <div class="fw-bold small text-dark">Gunakan Format Template Resmi</div>
                                <div class="text-muted small">Susunan kolom baris 1 (Header): NISN, Nama Siswa, Nama Kelas, Jenis Kelamin (L/P)</div>
                            </div>
                            <a href="<?= site_url('admin/siswa/download-template') ?>" class="btn btn-sm btn-outline-success text-nowrap">
                                <i class="bi bi-download me-1"></i> Unduh Template (.xlsx)
                            </a>
                        </div>
                        <!-- Tabel Contoh Format -->
                        <div class="table-responsive mt-2">
                            <table class="table table-bordered table-sm bg-white mb-0 text-center" style="font-size: 0.78rem;">
                                <thead class="table-primary text-dark">
                                    <tr>
                                        <th>Kolom A (NISN)</th>
                                        <th>Kolom B (Nama Siswa)</th>
                                        <th>Kolom C (Nama Kelas)</th>
                                        <th>Kolom D (L/P)*</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="font-monospace">0061234509</td>
                                        <td class="text-start">Ahmad Dani Pratama</td>
                                        <td>XII AK 1</td>
                                        <td><span class="badge bg-primary bg-opacity-15 text-primary">L</span></td>
                                    </tr>
                                    <tr>
                                        <td class="font-monospace">0061234510</td>
                                        <td class="text-start">Bella Nur Safitri</td>
                                        <td>XII AK 1</td>
                                        <td><span class="badge bg-danger bg-opacity-15 text-danger">P</span></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="form-text small text-muted mt-2" style="font-size: 0.76rem;">
                            <i class="bi bi-magic text-primary me-1"></i> <strong>Cerdas:</strong> Kolom D boleh diisi <code>L</code> atau <code>P</code>. Jika dikosongkan (atau memakai file lama), sistem otomatis mendeteksi gender siswa dari pola namanya!
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="file_excel_siswa" class="form-label small fw-semibold text-secondary">Pilih Berkas Excel (.xlsx / .xls / .csv) <span class="text-danger">*</span></label>
                        <input type="file" class="form-control" id="file_excel_siswa" name="file_excel" accept=".xlsx,.xls,.csv" required>
                        <div class="form-text small">Jika nama kelas belum ada di sistem, sistem akan otomatis mendaftarkan kelas baru tersebut. Siswa lama dengan NISN sama akan diperbarui datanya.</div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success px-4">
                        <i class="bi bi-cloud-arrow-up-fill me-1"></i> Mulai Import Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Nyatakan Keluar / Mutasi Nonaktif (Admin) -->
<div class="modal fade" id="modalNonaktifkanSiswaAdmin" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-danger text-white border-0 px-4 py-3 rounded-top-4">
                <h5 class="modal-title fw-bold">
                    <i class="bi bi-person-x-fill me-2"></i>Nyatakan Siswa Keluar / Mutasi (Nonaktif)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= site_url('admin/siswa/nonaktifkan') ?>" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="siswa_id" id="admin_mutasi_siswa_id" value="">
                
                <div class="modal-body p-4">
                    <div class="alert alert-light border d-flex align-items-center gap-3 p-3 rounded-3 mb-3">
                        <div class="rounded-circle bg-danger bg-opacity-10 p-2 text-danger">
                            <i class="bi bi-person-bounding-box fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Siswa:</div>
                            <div class="fw-bold text-dark fs-6" id="admin_mutasi_nama_siswa">-</div>
                            <small class="text-muted font-monospace" id="admin_mutasi_nisn_siswa">NISN: -</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Alasan Nonaktif / Keluar <span class="text-danger">*</span></label>
                        <select name="alasan_nonaktif" id="admin_mutasi_alasan" class="form-select" required>
                            <option value="pindah_sekolah">Pindah Sekolah / Mutasi Keluar</option>
                            <option value="mengundurkan_diri">Mengundurkan Diri (Atas Permintaan Sendiri/Ortu)</option>
                            <option value="dikeluarkan">Dikeluarkan / Drop Out (DO)</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Tanggal Efektif Nonaktif <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_nonaktif" id="admin_mutasi_tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Unggah Berkas Surat Pendukung</label>
                        <input type="file" name="file_surat" id="admin_mutasi_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                        <small class="text-muted d-block mt-1">
                            Format PDF / JPG / PNG (Maks 4MB). Surat pindah sekolah, SK Drop Out, atau surat pengunduran diri.
                        </small>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-semibold text-dark small">Catatan Keterangan Tambahan</label>
                        <textarea name="catatan_nonaktif" id="admin_mutasi_catatan" class="form-control" rows="3" placeholder="Keterangan pindah atau alasan mutasi..."></textarea>
                    </div>
                </div>

                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-semibold shadow-sm">
                        <i class="bi bi-check2-circle me-1"></i> Simpan Status Nonaktif
                    </button>
                </div>
            </form>
        </div>
</div>

<!-- Modal Ceklis Cepat Gender Massal (Admin) -->
<div class="modal fade" id="modalGenderBatchAdmin" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-primary text-white border-0 px-4 py-3 rounded-top-4">
                <div>
                    <h5 class="modal-title fw-bold mb-0">
                        <i class="bi bi-gender-ambiguous me-2"></i>Ceklis Cepat Jenis Kelamin Siswa
                    </h5>
                    <small class="text-white-50">Periksa dan sesuaikan Laki-laki (L) / Perempuan (P) sekaligus dalam 1 tabel cepat.</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= site_url('admin/siswa/simpan-gender-batch') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 px-3 small rounded-3 d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-info-circle-fill fs-5"></i>
                        <span>Pilih opsi <strong>L</strong> atau <strong>P</strong> untuk setiap siswa di bawah, lalu klik <strong>Simpan Semua Gender</strong>.</span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 45px;">No</th>
                                    <th>Nama Siswa & Kelas</th>
                                    <th class="text-center" style="width: 200px;">Jenis Kelamin</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($siswaList as $idx => $s): ?>
                                    <?php $currentG = $s['jenis_kelamin'] ?? \App\Models\SiswaModel::guessGenderByName($s['nama_siswa']); ?>
                                    <tr>
                                        <td class="text-center text-muted fw-semibold"><?= $idx + 1 ?></td>
                                        <td>
                                            <div class="fw-bold text-dark"><?= esc($s['nama_siswa']) ?></div>
                                            <small class="text-muted"><?= esc($s['nama_kelas'] ?? '') ?> | <?= esc($s['nisn']) ?></small>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <input type="radio" class="btn-check" name="gender[<?= $s['id'] ?>]" id="admin_g_l_<?= $s['id'] ?>" value="L" <?= $currentG === 'L' ? 'checked' : '' ?> autocomplete="off">
                                                <label class="btn btn-outline-primary px-3 fw-semibold" for="admin_g_l_<?= $s['id'] ?>"><i class="bi bi-gender-male me-1"></i> L</label>

                                                <input type="radio" class="btn-check" name="gender[<?= $s['id'] ?>]" id="admin_g_p_<?= $s['id'] ?>" value="P" <?= $currentG === 'P' ? 'checked' : '' ?> autocomplete="off">
                                                <label class="btn btn-outline-danger px-3 fw-semibold" for="admin_g_p_<?= $s['id'] ?>"><i class="bi bi-gender-female me-1"></i> P</label>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary-custom rounded-pill px-4 fw-semibold shadow-sm">
                        <i class="bi bi-check2-all me-1"></i> Simpan Semua Gender Siswa
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    var modalSiswa = new bootstrap.Modal(document.getElementById('modalSiswa'));
    var modalNonaktifAdmin = new bootstrap.Modal(document.getElementById('modalNonaktifkanSiswaAdmin'));

    function tambahSiswa() {
        document.getElementById('modalSiswaTitle').innerText = 'Tambah Siswa Baru';
        document.getElementById('siswa_id').value = '';
        document.getElementById('siswa_nisn').value = '';
        document.getElementById('siswa_nama').value = '';
        document.getElementById('siswa_jk').value = 'L';
        document.getElementById('siswa_kelas_id').value = '<?= $selectedKelasId ?: '' ?>';
        document.getElementById('siswa_status').value = 'Aktif';
        modalSiswa.show();
    }

    function editSiswa(data) {
        document.getElementById('modalSiswaTitle').innerText = 'Edit Data Siswa';
        document.getElementById('siswa_id').value = data.id;
        document.getElementById('siswa_nisn').value = data.nisn || '';
        document.getElementById('siswa_nama').value = data.nama_siswa || '';
        document.getElementById('siswa_jk').value = data.jenis_kelamin || 'L';
        document.getElementById('siswa_kelas_id').value = data.kelas_id || '';
        document.getElementById('siswa_status').value = data.status || 'Aktif';
        modalSiswa.show();
    }

    function bukaModalNonaktifAdmin(siswa) {
        document.getElementById('admin_mutasi_siswa_id').value = siswa.id;
        document.getElementById('admin_mutasi_nama_siswa').textContent = siswa.nama_siswa;
        document.getElementById('admin_mutasi_nisn_siswa').textContent = 'NISN: ' + (siswa.nisn || '-');
        document.getElementById('admin_mutasi_alasan').value = 'pindah_sekolah';
        document.getElementById('admin_mutasi_tanggal').value = new Date().toISOString().split('T')[0];
        document.getElementById('admin_mutasi_file').value = '';
        document.getElementById('admin_mutasi_catatan').value = '';
        modalNonaktifAdmin.show();
    }
</script>
<?= $this->endSection() ?>
