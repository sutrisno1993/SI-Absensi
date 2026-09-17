<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="row mb-4 align-items-center">
    <div class="col-12 col-md-7">
        <h3 class="fw-bold text-dark mb-1">
            <i class="bi bi-speedometer2 text-primary me-2"></i>Dashboard Wali Kelas <?= esc($kelas['nama_kelas']) ?>
        </h3>
        <p class="text-muted small mb-0">Pantau perkembangan kedisiplinan dan input tindakan pembinaan untuk siswa binaan Anda.</p>
    </div>
    <div class="col-12 col-md-5 mt-3 mt-md-0 text-md-end d-flex justify-content-md-end gap-2">
        <a href="<?= site_url('walas/absen') ?>" class="btn btn-outline-primary fw-semibold rounded-3 shadow-sm">
            <i class="bi bi-clipboard-check me-1"></i> Input Presensi Kelewat
        </a>
        <button type="button" class="btn btn-primary-custom" data-bs-toggle="modal" data-bs-target="#modalBinaSiswa">
            <i class="bi bi-plus-circle-fill me-1"></i> Input Pembinaan
        </button>
    </div>
</div>

<!-- Statistik Ringkas -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card card-custom border-0 p-3 h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-door-open-fill fs-3"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Kelas Binaan</div>
                    <div class="fs-4 fw-bold text-dark"><?= esc($kelas['nama_kelas']) ?></div>
                    <small class="text-muted"><?= count($daftarSiswa) ?> Siswa Terdaftar</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card card-custom border-0 p-3 h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-danger bg-opacity-10 text-danger">
                    <i class="bi bi-exclamation-octagon-fill fs-3"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Perlu Pembinaan (Alpa > 3)</div>
                    <div class="fs-4 fw-bold text-danger"><?= count($siswaBermasalah) ?> Siswa</div>
                    <small class="text-muted">Membutuhkan intervensi segera</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-4">
        <div class="card card-custom border-0 p-3 h-100">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-success bg-opacity-10 text-success">
                    <i class="bi bi-journal-bookmark-fill fs-3"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Total Tindakan Pembinaan</div>
                    <div class="fs-4 fw-bold text-success"><?= count($riwayatPembinaan) ?> Tindakan</div>
                    <small class="text-muted">Tercatat dalam sistem</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- CRITICAL ALERT: Siswa Perlu Pembinaan (Alpa > 3) -->
<?php if (! empty($siswaBermasalah)): ?>
    <div class="card border-danger border-2 shadow-sm rounded-4 mb-4 overflow-hidden">
        <div class="card-header bg-danger text-white py-3 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                <h5 class="mb-0 fw-bold">Peringatan: Siswa Memiliki Alpa Lebih Dari 3 Kali!</h5>
            </div>
            <span class="badge bg-white text-danger fw-bold px-3 py-2 rounded-pill">Tindakan Diperlukan</span>
        </div>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-danger">
                    <tr>
                        <th class="text-center" style="width: 60px;">No</th>
                        <th style="width: 150px;">NISN</th>
                        <th>Nama Siswa</th>
                        <th class="text-center" style="width: 140px;">Akumulasi Alpa</th>
                        <th class="text-end" style="width: 180px;">Aksi Cepat</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($siswaBermasalah as $idx => $sb): ?>
                        <tr>
                            <td class="text-center fw-bold"><?= $idx + 1 ?></td>
                            <td><span class="badge bg-light text-dark border font-monospace"><?= esc($sb['nisn']) ?></span></td>
                            <td class="fw-bold text-dark fs-6"><?= esc($sb['nama_siswa']) ?></td>
                            <td class="text-center">
                                <span class="badge bg-danger px-3 py-2 fs-6 rounded-pill">
                                    <i class="bi bi-x-circle me-1"></i> <?= $sb['total_alpa'] ?> Alpa
                                </span>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-danger fw-bold rounded-pill px-3 shadow-sm" onclick="binaSiswaCepat(<?= $sb['siswa_id'] ?>, '<?= addslashes($sb['nama_siswa']) ?>')">
                                    <i class="bi bi-pencil-square me-1"></i> Bina Siswa
                                </button>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<!-- Riwayat Pembinaan Siswa -->
<div class="card card-custom border-0 mb-4">
    <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
        <h5 class="fw-bold text-dark mb-0">
            <i class="bi bi-journal-text text-primary me-2"></i>Riwayat Tindakan Pembinaan Kelas
        </h5>
        <a href="<?= site_url('walas/rekap') ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3">
            <i class="bi bi-calendar-check me-1"></i> Rekap Presensi Lengkap
        </a>
    </div>

    <div class="table-responsive">
        <table class="table table-custom table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="text-center" style="width: 50px;">No</th>
                    <th style="width: 120px;">Tanggal</th>
                    <th style="width: 180px;">Nama Siswa</th>
                    <th style="width: 170px;">Jenis Tindakan</th>
                    <th>Catatan Pembinaan</th>
                    <th class="text-center" style="width: 130px;">Bukti Berkas</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($riwayatPembinaan)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-shield-check fs-1 d-block mb-2 text-success"></i>
                            Belum ada riwayat tindakan pembinaan yang dicatat.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($riwayatPembinaan as $idx => $rp): ?>
                        <tr>
                            <td class="text-center fw-semibold text-muted"><?= $idx + 1 ?></td>
                            <td class="text-nowrap fw-semibold"><?= date('d/m/Y', strtotime($rp['tanggal_tindakan'])) ?></td>
                            <td>
                                <div class="fw-bold text-dark"><?= esc($rp['nama_siswa']) ?></div>
                                <small class="text-muted font-monospace"><?= esc($rp['nisn']) ?></small>
                            </td>
                            <td>
                                <span class="badge bg-light text-primary border px-2 py-1">
                                    <?= esc($rp['jenis_tindakan']) ?>
                                </span>
                            </td>
                            <td>
                                <div class="small text-secondary"><?= nl2br(esc($rp['catatan_pembinaan'])) ?></div>
                            </td>
                            <td class="text-center">
                                <?php if (! empty($rp['file_bukti'])): ?>
                                    <a href="<?= base_url('uploads/pembinaan/' . $rp['file_bukti']) ?>" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1" title="Lihat Berkas Bukti">
                                        <i class="bi bi-paperclip me-1"></i> Berkas
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted small">-</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Input Tindakan Pembinaan -->
<div class="modal fade" id="modalBinaSiswa" tabindex="-1" aria-labelledby="modalBinaSiswaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-primary text-white rounded-3 p-2">
                        <i class="bi bi-person-check-fill fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark" id="modalBinaSiswaLabel">Input Tindakan Pembinaan Siswa</h5>
                        <p class="text-muted small mb-0">Catat tindakan pembinaan dan lampirkan bukti berkas fisik/foto.</p>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            
            <form action="<?= site_url('walas/pembinaan/simpan') ?>" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                
                <div class="modal-body py-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-7">
                            <label for="modal_siswa_id" class="form-label fw-semibold small text-secondary">Pilih Siswa Binaan <span class="text-danger">*</span></label>
                            <select class="form-select" id="modal_siswa_id" name="siswa_id" required>
                                <option value="">-- Pilih Siswa --</option>
                                <?php foreach ($daftarSiswa as $s): ?>
                                    <option value="<?= $s['id'] ?>"><?= esc($s['nama_siswa']) ?> (<?= esc($s['nisn']) ?>)</option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-12 col-md-5">
                            <label for="tanggal_tindakan" class="form-label fw-semibold small text-secondary">Tanggal Tindakan <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="tanggal_tindakan" name="tanggal_tindakan" value="<?= date('Y-m-d') ?>" required>
                        </div>

                        <div class="col-12">
                            <label for="jenis_tindakan" class="form-label fw-semibold small text-secondary">Jenis Tindakan Pembinaan <span class="text-danger">*</span></label>
                            <select class="form-select" id="jenis_tindakan" name="jenis_tindakan" required>
                                <option value="">-- Pilih Jenis Tindakan --</option>
                                <option value="Konseling Khusus Wali Kelas">Konseling Khusus Wali Kelas</option>
                                <option value="Pemanggilan Orang Tua / Wali">Pemanggilan Orang Tua / Wali</option>
                                <option value="Surat Peringatan 1 (SP1)">Surat Peringatan 1 (SP1)</option>
                                <option value="Surat Peringatan 2 (SP2)">Surat Peringatan 2 (SP2)</option>
                                <option value="Kunjungan Rumah (Homevisit)">Kunjungan Rumah (Homevisit)</option>
                                <option value="Pelimpahan ke Guru BK / Konselor">Pelimpahan ke Guru BK / Konselor</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="catatan_pembinaan" class="form-label fw-semibold small text-secondary">Catatan Pembinaan & Hasil Diskusi <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="catatan_pembinaan" name="catatan_pembinaan" rows="4" placeholder="Tuliskan latar belakang masalah, kesepakatan pembinaan, dan komitmen siswa..." required></textarea>
                        </div>

                        <div class="col-12">
                            <label for="file_bukti" class="form-label fw-semibold small text-secondary">Upload Bukti Berkas (Foto / Surat / PDF)</label>
                            <input type="file" class="form-control" id="file_bukti" name="file_bukti" accept=".jpg,.jpeg,.png,.pdf">
                            <div class="form-text small">Format file: JPG, PNG, atau PDF. Ukuran berkas maksimal 2MB.</div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary-custom px-4">
                        <i class="bi bi-save-fill me-1"></i> Simpan Tindakan Pembinaan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function binaSiswaCepat(siswaId, namaSiswa) {
        var selectSiswa = document.getElementById('modal_siswa_id');
        if (selectSiswa) {
            selectSiswa.value = siswaId;
        }
        var modal = new bootstrap.Modal(document.getElementById('modalBinaSiswa'));
        modal.show();
    }
</script>
<?= $this->endSection() ?>
