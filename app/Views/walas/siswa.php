<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="row mb-4 align-items-center">
    <div class="col-12 col-md-7">
        <h3 class="fw-bold text-dark mb-1">
            <i class="bi bi-people-fill text-primary me-2"></i>Data Siswa Kelas <?= esc($kelas['nama_kelas']) ?>
        </h3>
        <p class="text-muted small mb-0">Kelola status aktif/nonaktif siswa rombel binaan Anda. Siswa nonaktif tidak akan muncul di form presensi.</p>
    </div>
    <div class="col-12 col-md-5 mt-3 mt-md-0 text-md-end d-flex flex-wrap justify-content-md-end gap-2">
        <!-- Form Auto-Detect Gender 1 Klik -->
        <form action="<?= site_url('walas/siswa/auto-detect-gender') ?>" method="POST" class="d-inline" onsubmit="return confirm('Sistem akan mendeteksi otomatis Jenis Kelamin (L/P) seluruh siswa kelas ini berdasarkan pola nama. Lanjutkan?')">
            <?= csrf_field() ?>
            <button type="submit" class="btn btn-outline-info text-dark fw-semibold rounded-pill px-3 shadow-sm" title="Deteksi Otomatis L/P berdasarkan pola nama siswa">
                <i class="bi bi-magic me-1"></i> Auto-Detect Gender
            </button>
        </form>
        <button type="button" class="btn btn-outline-secondary fw-semibold rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalGenderBatchWalas">
            <i class="bi bi-gender-ambiguous me-1"></i> Ceklis Gender Cepat
        </button>
        <a href="<?= site_url('walas/absen') ?>" class="btn btn-outline-primary fw-semibold rounded-pill px-3 shadow-sm">
            <i class="bi bi-pencil-square me-1"></i> Buka Presensi
        </a>
    </div>
</div>

<!-- Kartu Ringkasan Status Siswa -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-4">
        <div class="card card-custom border-0 p-3 h-100 shadow-sm">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-people fs-3"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Total Terdaftar</div>
                    <div class="fs-4 fw-bold text-dark"><?= $totalSemua ?> Murid</div>
                    <small class="text-muted">Di kelas <?= esc($kelas['nama_kelas']) ?></small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-4">
        <div class="card card-custom border-0 p-3 h-100 shadow-sm">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-success bg-opacity-10 text-success">
                    <i class="bi bi-person-check-fill fs-3"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Siswa Aktif</div>
                    <div class="fs-4 fw-bold text-success"><?= $totalAktif ?> Murid</div>
                    <small class="text-muted">Muncul di lembar absen</small>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-4">
        <div class="card card-custom border-0 p-3 h-100 shadow-sm">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-secondary bg-opacity-10 text-muted">
                    <i class="bi bi-person-x-fill fs-3 text-secondary"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Siswa Nonaktif</div>
                    <div class="fs-4 fw-bold text-secondary"><?= $totalNonaktif ?> Murid</div>
                    <small class="text-muted">Disembunyikan dari absen</small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Info Alert untuk Walas -->
<div class="alert alert-info bg-info bg-opacity-10 border-info border-opacity-25 d-flex align-items-center p-3 rounded-3 mb-4 gap-3">
    <i class="bi bi-info-circle-fill fs-3 text-info"></i>
    <div class="small text-muted">
        <strong class="text-info d-block mb-1">Catatan Wali Kelas:</strong>
        Menonaktifkan siswa <strong>bukan menghapus data</strong>, melainkan mengecualikan siswa dari daftar hadir harian kelas (misal siswa pindah sekolah, cuti, atau berhenti). Seluruh riwayat presensi yang pernah dicatat sebelumnya tetap aman tersimpan di arsip sekolah, dan Anda dapat mengaktifkannya kembali kapan saja.
    </div>
</div>

<!-- Card Tabel Siswa -->
<div class="card card-custom border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-2">
                <h5 class="fw-bold text-dark mb-0">Daftar Anggota Kelas</h5>
                <span class="badge bg-light text-muted border"><?= count($siswaList) ?> Siswa Ditampilkan</span>
            </div>

            <!-- Filter Status Pills -->
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="<?= site_url('walas/siswa') ?>" class="btn btn-sm <?= empty($selectedStatus) ? 'btn-primary-custom text-white' : 'btn-outline-secondary' ?> rounded-pill px-3">
                    Semua (<?= $totalSemua ?>)
                </a>
                <a href="<?= site_url('walas/siswa?status=Aktif') ?>" class="btn btn-sm <?= ($selectedStatus === 'Aktif') ? 'btn-success text-white' : 'btn-outline-success' ?> rounded-pill px-3">
                    <i class="bi bi-check-circle me-1"></i> Aktif (<?= $totalAktif ?>)
                </a>
                <a href="<?= site_url('walas/siswa?status=Nonaktif') ?>" class="btn btn-sm <?= ($selectedStatus === 'Nonaktif') ? 'btn-secondary text-white' : 'btn-outline-secondary' ?> rounded-pill px-3">
                    <i class="bi bi-slash-circle me-1"></i> Nonaktif (<?= $totalNonaktif ?>)
                </a>
            </div>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-custom table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="text-center" style="width: 50px;">No</th>
                    <th style="width: 130px;">NISN</th>
                    <th>Nama Lengkap Siswa</th>
                    <th class="text-center" style="width: 70px;">L/P</th>
                    <th style="width: 220px;">Status & Keterangan Mutasi</th>
                    <th class="text-end" style="width: 220px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($siswaList)): ?>
                    <tr>
                        <td colspan="6" class="text-center py-5 text-muted">
                            <i class="bi bi-people fs-1 d-block mb-2"></i>
                            Tidak ada siswa yang sesuai dengan filter ini.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($siswaList as $idx => $s): ?>
                        <tr>
                            <td class="text-center fw-semibold text-muted"><?= $idx + 1 ?></td>
                            <td>
                                <span class="badge bg-light text-dark border font-monospace"><?= esc($s['nisn']) ?></span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark fs-6"><?= esc($s['nama_siswa']) ?></div>
                                <small class="text-muted">Kelas <?= esc($kelas['nama_kelas']) ?></small>
                            </td>
                            <td class="text-center">
                                <span class="badge <?= ($s['jenis_kelamin'] ?? 'L') === 'L' ? 'bg-info bg-opacity-15 text-primary border border-info border-opacity-25' : 'bg-danger bg-opacity-15 text-danger border border-danger border-opacity-25' ?> rounded-pill px-2 py-1">
                                    <?= esc($s['jenis_kelamin'] ?? 'L') ?>
                                </span>
                            </td>
                            <td>
                                <?php if (($s['status'] ?? 'Aktif') === 'Aktif'): ?>
                                    <span class="badge bg-success bg-opacity-15 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill">
                                        <i class="bi bi-check-circle-fill me-1"></i> Aktif
                                    </span>
                                <?php else: ?>
                                    <div>
                                        <span class="badge bg-secondary text-white px-2 py-1 rounded-pill mb-1">
                                            <i class="bi bi-person-x-fill me-1"></i> Nonaktif
                                        </span>
                                        <?php if (!empty($s['alasan_nonaktif'])): ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2 py-1 mb-1">
                                                <?= esc(ucwords(str_replace('_', ' ', $s['alasan_nonaktif']))) ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($s['tanggal_nonaktif'])): ?>
                                            <div class="small text-muted">
                                                <i class="bi bi-calendar-event me-1"></i> Tgl: <?= date('d/m/Y', strtotime($s['tanggal_nonaktif'])) ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if (!empty($s['file_surat_nonaktif'])): ?>
                                            <div class="mt-1">
                                                <a href="<?= base_url('uploads/mutasi/' . $s['file_surat_nonaktif']) ?>" target="_blank" class="btn btn-xs btn-outline-primary py-0 px-2 rounded-pill shadow-xs" style="font-size: 0.75rem;">
                                                    <i class="bi bi-file-earmark-arrow-down me-1"></i> Surat Pendukung
                                                </a>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?php if (($s['status'] ?? 'Aktif') === 'Aktif'): ?>
                                    <!-- Tombol Pemicu Modal Mutasi Nonaktif -->
                                    <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 shadow-sm" onclick="bukaModalNonaktif(<?= htmlspecialchars(json_encode($s), ENT_QUOTES, 'UTF-8') ?>)">
                                        <i class="bi bi-person-x-fill me-1"></i> Nyatakan Keluar / Mutasi
                                    </button>
                                <?php else: ?>
                                    <form action="<?= site_url('walas/siswa/toggle-status/' . $s['id']) ?>" method="POST" class="d-inline">
                                        <?= csrf_field() ?>
                                        <button type="submit" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1 shadow-sm" onclick="return confirm('Aktifkan kembali siswa <?= esc(addslashes($s['nama_siswa'])) ?>?\n\nSiswa ini akan kembali dimunculkan di pengisian presensi.')">
                                            <i class="bi bi-person-check-fill me-1"></i> Aktifkan Kembali
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Nyatakan Keluar / Pindah / Nonaktifkan Siswa -->
<div class="modal fade" id="modalNonaktifkanSiswa" tabindex="-1" aria-labelledby="modalNonaktifkanLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-danger text-white border-0 px-4 py-3 rounded-top-4">
                <h5 class="modal-title fw-bold" id="modalNonaktifkanLabel">
                    <i class="bi bi-person-x-fill me-2"></i>Nyatakan Siswa Keluar / Nonaktif
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="<?= site_url('walas/siswa/nonaktifkan') ?>" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <input type="hidden" name="siswa_id" id="mutasi_siswa_id" value="">
                
                <div class="modal-body p-4">
                    <div class="alert alert-light border d-flex align-items-center gap-3 p-3 rounded-3 mb-3">
                        <div class="rounded-circle bg-danger bg-opacity-10 p-2 text-danger">
                            <i class="bi bi-person-bounding-box fs-4"></i>
                        </div>
                        <div>
                            <div class="text-muted small">Siswa Rombel:</div>
                            <div class="fw-bold text-dark fs-6" id="mutasi_nama_siswa">-</div>
                            <small class="text-muted font-monospace" id="mutasi_nisn_siswa">NISN: -</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Alasan Nonaktif / Keluar <span class="text-danger">*</span></label>
                        <select name="alasan_nonaktif" id="mutasi_alasan" class="form-select" required>
                            <option value="pindah_sekolah">Pindah Sekolah / Mutasi Keluar</option>
                            <option value="mengundurkan_diri">Mengundurkan Diri (Atas Permintaan Sendiri/Ortu)</option>
                            <option value="dikeluarkan">Dikeluarkan / Drop Out (DO)</option>
                            <option value="lainnya">Lainnya</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Tanggal Efektif Nonaktif <span class="text-danger">*</span></label>
                        <input type="date" name="tanggal_nonaktif" id="mutasi_tanggal" class="form-control" value="<?= date('Y-m-d') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark small">Unggah Berkas Surat Pendukung <span class="text-danger">*</span></label>
                        <input type="file" name="file_surat" id="mutasi_file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                        <small class="text-muted d-block mt-1">
                            Format PDF / JPG / PNG (Maks 4MB). Contoh: Surat Permohonan Pindah, Surat Pengunduran Diri Orang Tua, dsb.
                        </small>
                    </div>

                    <div class="mb-0">
                        <label class="form-label fw-semibold text-dark small">Catatan Keterangan Tambahan</label>
                        <textarea name="catatan_nonaktif" id="mutasi_catatan" class="form-control" rows="3" placeholder="Contoh: Pindah ke SMKN 1 Semarang mengikuti dinas orang tua..."></textarea>
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

<!-- Modal Ceklis Cepat Gender Massal (Walas) -->
<div class="modal fade" id="modalGenderBatchWalas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-primary text-white border-0 px-4 py-3 rounded-top-4">
                <div>
                    <h5 class="modal-title fw-bold mb-0">
                        <i class="bi bi-gender-ambiguous me-2"></i>Ceklis Cepat Jenis Kelamin Kelas <?= esc($kelas['nama_kelas']) ?>
                    </h5>
                    <small class="text-white-50">Periksa dan ubah Laki-laki (L) / Perempuan (P) sekaligus dalam satu layar tanpa perlu edit satu per satu.</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= site_url('walas/siswa/simpan-gender-batch') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 px-3 small rounded-3 d-flex align-items-center gap-2 mb-3">
                        <i class="bi bi-info-circle-fill fs-5"></i>
                        <span><strong>Petunjuk:</strong> Anda cukup memilih opsi <strong>L</strong> atau <strong>P</strong> untuk masing-masing siswa di bawah, lalu klik <strong>Simpan Semua Gender</strong>.</span>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" style="width: 45px;">No</th>
                                    <th>Nama Lengkap Siswa</th>
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
                                            <small class="text-muted font-monospace"><?= esc($s['nisn']) ?></small>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group btn-group-sm" role="group">
                                                <input type="radio" class="btn-check" name="gender[<?= $s['id'] ?>]" id="g_l_<?= $s['id'] ?>" value="L" <?= $currentG === 'L' ? 'checked' : '' ?> autocomplete="off">
                                                <label class="btn btn-outline-primary px-3 fw-semibold" for="g_l_<?= $s['id'] ?>"><i class="bi bi-gender-male me-1"></i> L</label>

                                                <input type="radio" class="btn-check" name="gender[<?= $s['id'] ?>]" id="g_p_<?= $s['id'] ?>" value="P" <?= $currentG === 'P' ? 'checked' : '' ?> autocomplete="off">
                                                <label class="btn btn-outline-danger px-3 fw-semibold" for="g_p_<?= $s['id'] ?>"><i class="bi bi-gender-female me-1"></i> P</label>
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
                        <i class="bi bi-check2-all me-1"></i> Simpan Semua Gender Kelas
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function bukaModalNonaktif(siswa) {
    document.getElementById('mutasi_siswa_id').value = siswa.id;
    document.getElementById('mutasi_nama_siswa').textContent = siswa.nama_siswa;
    document.getElementById('mutasi_nisn_siswa').textContent = 'NISN: ' + (siswa.nisn || '-');
    document.getElementById('mutasi_alasan').value = 'pindah_sekolah';
    document.getElementById('mutasi_tanggal').value = new Date().toISOString().split('T')[0];
    document.getElementById('mutasi_file').value = '';
    document.getElementById('mutasi_catatan').value = '';

    const modal = new bootstrap.Modal(document.getElementById('modalNonaktifkanSiswa'));
    modal.show();
}
</script>
<?= $this->endSection() ?>
