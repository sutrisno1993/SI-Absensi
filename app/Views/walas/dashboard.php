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

<!-- CRITICAL ALERT: Presensi Hari Aktif Belajar Belum Diinput (Kelewat) -->
<?php if (! empty($tanggalKelewat)): ?>
    <div class="card border-warning border-2 shadow-sm rounded-4 mb-4 overflow-hidden">
        <div class="card-header bg-warning bg-opacity-25 border-warning py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-clock-history fs-3 text-warning"></i>
                <div>
                    <h5 class="mb-0 fw-bold text-dark">Peringatan: Presensi Kelas Belum Diinput!</h5>
                    <small class="text-muted">Terdapat <strong><?= count($tanggalKelewat) ?> hari aktif belajar (Senin-Sabtu)</strong> sejak 21 Juli 2026 yang belum memiliki catatan absensi.</small>
                </div>
            </div>
            <span class="badge bg-warning text-dark fw-bold px-3 py-2 rounded-pill">
                <i class="bi bi-exclamation-triangle-fill me-1"></i> Perlu Dilengkapi
            </span>
        </div>
        <div class="card-body p-3 bg-light" style="max-height: 420px; overflow-y: auto;">
            <div class="row g-2">
                <?php foreach ($tanggalKelewat as $tk): ?>
                    <div class="col-12 col-md-6 col-lg-4">
                        <div class="card border-0 shadow-sm p-3 rounded-3 bg-white h-100 d-flex flex-column justify-content-between">
                            <div class="d-flex align-items-start justify-content-between mb-2">
                                <div>
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 mb-1">
                                        <i class="bi bi-calendar-x me-1"></i> <?= $tk['hari'] ?>
                                    </span>
                                    <div class="fw-bold text-dark fs-6"><?= esc($tk['formatted']) ?></div>
                                    <small class="text-muted"><?= $tk['is_today'] ? 'Hari Ini (Belum Diinput)' : 'Hari Belajar Terlewat' ?></small>
                                </div>
                            </div>
                            <div class="d-flex gap-2 mt-2 pt-2 border-top">
                                <a href="<?= site_url('walas/absen?tanggal=' . $tk['tanggal']) ?>" class="btn btn-sm btn-primary-custom flex-grow-1 rounded-pill fw-semibold shadow-sm text-center">
                                    <i class="bi bi-pencil-square me-1"></i> Isi Absen
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-warning text-dark border-warning rounded-pill px-3 fw-semibold text-nowrap" onclick="bukaModalTandaiLibur('<?= $tk['tanggal'] ?>', '<?= esc($tk['formatted']) ?>')">
                                    <i class="bi bi-calendar-event me-1"></i> Libur / Acara
                                </button>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

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

<!-- Modal Walas Tandai Libur / Acara Khusus -->
<div class="modal fade" id="modalTandaiLibur" tabindex="-1" aria-labelledby="modalTandaiLiburLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-warning bg-opacity-25 border-bottom border-warning py-3">
                <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="modalTandaiLiburLabel">
                    <i class="bi bi-calendar2-check-fill text-warning"></i>
                    Tandai Hari Libur / Acara Non-KBM
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= site_url('walas/tandai-libur') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="tanggal" id="libur_tanggal" value="">
                
                <div class="modal-body p-4">
                    <div class="alert alert-warning bg-opacity-10 border-0 rounded-3 mb-3 d-flex align-items-start gap-2">
                        <i class="bi bi-info-circle-fill text-warning fs-5 mt-1"></i>
                        <div class="small text-muted">
                            Fitur ini digunakan jika ada kegiatan/libur (misal: <strong>Maulid Nabi, Class Meeting, HUT Sekolah, Hari Libur Bersama</strong>) yang belum tercatat oleh Admin di Kalender Pendidikan. Presensi pada tanggal ini akan dinonaktifkan (bebas absen / auto null) dan notifikasi kelewat akan langsung hilang.
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Tanggal Terpilih</label>
                        <input type="text" class="form-control bg-light fw-bold text-primary" id="libur_tanggal_formatted" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="nama_agenda" class="form-label fw-bold text-dark small">Nama Agenda / Keterangan Libur <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="nama_agenda" name="nama_agenda" placeholder="Contoh: Libur Acara Maulid Nabi Muhammad SAW" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Kategori Kegiatan <span class="text-danger">*</span></label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipe" id="tipe_libur" value="libur" checked>
                                <label class="form-check-label small fw-semibold" for="tipe_libur">
                                    <i class="bi bi-calendar-x text-danger me-1"></i> Hari Libur / Bebas KBM
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="tipe" id="tipe_acara" value="acara">
                                <label class="form-check-label small fw-semibold" for="tipe_acara">
                                    <i class="bi bi-trophy text-primary me-1"></i> Acara / Kegiatan Sekolah
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark small">Cakupan Keterangan</label>
                        <select class="form-select" name="cakupan">
                            <option value="kelas" selected>Hanya Berlaku Untuk Kelas Ini (<?= esc($kelas['nama_kelas'] ?? 'Kelas Saya') ?>)</option>
                            <option value="tingkat">Berlaku Untuk Seluruh Kelas Se-Tingkat</option>
                        </select>
                        <div class="form-text small">Pilih "Hanya Berlaku Untuk Kelas Ini" agar tidak memengaruhi kelas lain, atau se-tingkat jika seluruh angkatan memiliki kegiatan serupa.</div>
                    </div>
                </div>

                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold rounded-pill px-4 shadow-sm">
                        <i class="bi bi-check2-circle me-1"></i> Simpan & Bebaskan Absen
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

    function bukaModalTandaiLibur(tanggal, formatted) {
        document.getElementById('libur_tanggal').value = tanggal;
        document.getElementById('libur_tanggal_formatted').value = formatted;
        document.getElementById('nama_agenda').value = '';
        var modal = new bootstrap.Modal(document.getElementById('modalTandaiLibur'));
        modal.show();
    }
</script>
<?= $this->endSection() ?>
