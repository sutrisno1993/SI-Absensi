<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="row mb-4 align-items-center">
    <div class="col-12 col-md-6">
        <h3 class="fw-bold text-dark mb-1">
            <i class="bi bi-person-badge text-primary me-2"></i>Master Data Guru
        </h3>
        <p class="text-muted small mb-0">Kelola data tenaga pendidik dan wali kelas sekolah.</p>
    </div>
    <div class="col-12 col-md-6 text-md-end mt-3 mt-md-0 d-flex flex-wrap justify-content-md-end gap-2">
        <a href="<?= site_url('admin/jadwal-guru') ?>" class="btn btn-outline-primary">
            <i class="bi bi-calendar2-week-fill me-1"></i> Atur Jadwal Mengajar
        </a>
        <button type="button" class="btn btn-outline-success" data-bs-toggle="modal" data-bs-target="#modalImportGuru">
            <i class="bi bi-file-earmark-excel me-1"></i> Import Excel
        </button>
        <button type="button" class="btn btn-primary-custom" onclick="tambahGuru()">
            <i class="bi bi-plus-circle-fill me-1"></i> Tambah Guru Baru
        </button>
    </div>
</div>

<div class="card card-custom border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-custom table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="text-center" style="width: 50px;">No</th>
                    <th style="width: 160px;">NIP</th>
                    <th>Nama Lengkap Guru</th>
                    <th style="width: 150px;">No. HP / WA</th>
                    <th>Jadwal Mengajar (Hari & Shift)</th>
                    <th class="text-end" style="width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($guruList)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">Belum ada data guru.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($guruList as $idx => $g): ?>
                        <tr>
                            <td class="text-center fw-semibold text-muted"><?= $idx + 1 ?></td>
                            <td><span class="badge bg-light text-dark border font-monospace"><?= esc($g['nip'] ?? '-') ?></span></td>
                            <td class="fw-bold text-dark"><?= esc($g['nama_guru']) ?></td>
                            <td><span class="text-secondary font-monospace small"><?= esc($g['no_hp'] ?? '-') ?></span></td>
                            <td>
                                <?php if (! empty($g['jadwal'])): ?>
                                    <div class="d-flex flex-wrap gap-1">
                                        <?php foreach ($g['jadwal'] as $jd): ?>
                                            <?php if ($jd['shift'] === 'Pagi'): ?>
                                                <span class="badge bg-warning bg-opacity-15 text-warning-emphasis border border-warning border-opacity-25 py-1 px-2">
                                                    <i class="bi bi-sun-fill text-warning me-1"></i><?= $jd['hari'] ?> (Pagi)
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-info bg-opacity-10 text-primary border border-info border-opacity-25 py-1 px-2">
                                                    <i class="bi bi-cloud-sun-fill text-primary me-1"></i><?= $jd['hari'] ?> (Siang)
                                                </span>
                                            <?php endif; ?>
                                        <?php endforeach; ?>
                                    </div>
                                <?php else: ?>
                                    <span class="badge bg-secondary bg-opacity-10 text-muted border">Belum Ada Jam Mengajar</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1" onclick="editGuru(<?= htmlspecialchars(json_encode($g), ENT_QUOTES, 'UTF-8') ?>)">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </button>
                                <a href="<?= site_url('admin/guru/hapus/' . $g['id']) ?>" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1 ms-1" onclick="return confirm('Apakah Anda yakin ingin menghapus data guru ini?')">
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

<!-- Modal Form Guru -->
<div class="modal fade" id="modalGuru" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="modalGuruTitle">Tambah Guru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= site_url('admin/guru/simpan') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="guru_id">
                <div class="modal-body py-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <label for="guru_nip" class="form-label small fw-semibold text-secondary">NIP (Nomor Induk Pegawai)</label>
                            <input type="text" class="form-control" id="guru_nip" name="nip" placeholder="Opsional jika belum ada NIP">
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="guru_nama" class="form-label small fw-semibold text-secondary">Nama Lengkap & Gelar <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="guru_nama" name="nama_guru" required placeholder="Contoh: Drs. Budi Santoso, M.Pd">
                        </div>
                        <div class="col-12 col-md-4">
                            <label for="guru_nohp" class="form-label small fw-semibold text-secondary">No. Handphone / WhatsApp</label>
                            <input type="text" class="form-control" id="guru_nohp" name="no_hp" placeholder="Contoh: 081234567890">
                        </div>
                    </div>

                    <!-- Checklist Matriks Jadwal Mengajar -->
                    <div class="mt-4">
                        <label class="form-label small fw-semibold text-secondary d-flex justify-content-between align-items-center">
                            <span><i class="bi bi-calendar3 text-primary me-1"></i> Jadwal Mengajar (Hari & Shift)</span>
                            <span class="text-muted fw-normal" style="font-size: 0.78rem;">Tentukan kapan guru memiliki jam tatap muka</span>
                        </label>
                        <div class="table-responsive border rounded-3 p-2 bg-light">
                            <table class="table table-sm table-bordered bg-white text-center align-middle mb-0" style="font-size: 0.85rem;">
                                <thead class="table-light">
                                    <tr>
                                        <th class="text-start ps-3" style="width: 140px;">Hari</th>
                                        <th><i class="bi bi-sun-fill text-warning me-1"></i> Shift Pagi (07:00)</th>
                                        <th><i class="bi bi-cloud-sun-fill text-primary me-1"></i> Shift Siang (12:30)</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach (['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'] as $hari): ?>
                                        <tr>
                                            <td class="text-start ps-3 fw-semibold text-dark"><?= $hari ?></td>
                                            <td>
                                                <div class="form-check d-flex justify-content-center m-0">
                                                    <input class="form-check-input check-jadwal" type="checkbox" name="jadwal[]" value="<?= $hari ?>:Pagi" id="chk_<?= $hari ?>_Pagi">
                                                    <label class="form-check-label ms-1 small text-muted d-none d-sm-inline" for="chk_<?= $hari ?>_Pagi">Pagi</label>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="form-check d-flex justify-content-center m-0">
                                                    <input class="form-check-input check-jadwal" type="checkbox" name="jadwal[]" value="<?= $hari ?>:Siang" id="chk_<?= $hari ?>_Siang">
                                                    <label class="form-check-label ms-1 small text-muted d-none d-sm-inline" for="chk_<?= $hari ?>_Siang">Siang</label>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        <div class="form-text text-muted mt-2" style="font-size: 0.76rem;">
                            <i class="bi bi-shield-check text-success me-1"></i> <strong>Anti-Alfa:</strong> Guru yang tidak memiliki jam pada hari & shift tertentu tidak akan dituntut absen oleh Guru Piket dan tidak dianggap Alfa.
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary-custom px-4">Simpan Guru & Jadwal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Import Excel Guru -->
<div class="modal fade" id="modalImportGuru" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-success text-white rounded-3 p-2">
                        <i class="bi bi-file-earmark-excel-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark">Import Guru dari Excel</h5>
                        <p class="text-muted small mb-0">Unggah berkas spreadsheet .xlsx, .xls, atau .csv</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= site_url('admin/guru/import') ?>" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-body py-4">
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div>
                                <div class="fw-bold small text-dark">Gunakan Format Template Resmi</div>
                                <div class="text-muted small">Susunan kolom baris 1 (Header): NIP, Nama Guru, No HP</div>
                            </div>
                            <a href="<?= site_url('admin/guru/download-template') ?>" class="btn btn-sm btn-outline-success text-nowrap">
                                <i class="bi bi-download me-1"></i> Unduh Template (.xlsx)
                            </a>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="file_excel" class="form-label small fw-semibold text-secondary">Pilih Berkas Excel / CSV <span class="text-danger">*</span></label>
                        <input class="form-control" type="file" id="file_excel" name="file_excel" accept=".xlsx, .xls, .csv" required>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success px-4"><i class="bi bi-upload me-1"></i> Mulai Import</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    var modalGuru = new bootstrap.Modal(document.getElementById('modalGuru'));

    function resetJadwalCheckboxes() {
        document.querySelectorAll('.check-jadwal').forEach(function(chk) {
            chk.checked = false;
        });
    }

    function tambahGuru() {
        document.getElementById('modalGuruTitle').innerText = 'Tambah Guru Baru';
        document.getElementById('guru_id').value = '';
        document.getElementById('guru_nip').value = '';
        document.getElementById('guru_nama').value = '';
        document.getElementById('guru_nohp').value = '';
        resetJadwalCheckboxes();
        modalGuru.show();
    }

    function editGuru(data) {
        document.getElementById('modalGuruTitle').innerText = 'Edit Data Guru & Jadwal';
        document.getElementById('guru_id').value = data.id;
        document.getElementById('guru_nip').value = data.nip || '';
        document.getElementById('guru_nama').value = data.nama_guru || '';
        document.getElementById('guru_nohp').value = data.no_hp || '';
        resetJadwalCheckboxes();

        if (data.jadwal && Array.isArray(data.jadwal)) {
            data.jadwal.forEach(function(jd) {
                var el = document.getElementById('chk_' + jd.hari + '_' + jd.shift);
                if (el) el.checked = true;
            });
        }

        modalGuru.show();
    }
</script>
<?= $this->endSection() ?>
