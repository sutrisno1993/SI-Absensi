<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="row mb-4 align-items-center">
    <div class="col-12 col-md-8">
        <h3 class="fw-bold text-dark mb-1">
            <i class="bi bi-alarm-fill text-danger me-2"></i>Pencatatan Siswa Terlambat
        </h3>
        <p class="text-muted small mb-0">Catat data kedatangan siswa yang hadir melewati batas waktu dan berikan tindakan pembinaan.</p>
    </div>
    <div class="col-12 col-md-4 text-md-end mt-3 mt-md-0">
        <a href="<?= site_url('piket') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
        </a>
    </div>
</div>

<div class="row g-4">
    <!-- Kolom Kiri: Form Input Siswa Terlambat -->
    <div class="col-12 col-lg-5">
        <div class="card card-custom border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="fw-bold text-dark mb-0">
                    <i class="bi bi-plus-circle-fill text-danger me-2"></i>Form Input Keterlambatan
                </h5>
            </div>
            <form action="<?= site_url('piket/siswa-terlambat/simpan') ?>" method="POST" id="formTerlambat">
                <?= csrf_field() ?>
                <div class="card-body p-4">
                    <!-- Alert Auto-Sync -->
                    <div class="alert alert-success border-0 bg-success bg-opacity-10 text-success p-3 rounded-3 mb-3 small d-flex align-items-center gap-2">
                        <i class="bi bi-check-circle-fill fs-5"></i>
                        <div>
                            <strong>Sinkronisasi Otomatis:</strong> Data ini langsung mengubah absensi kelas siswa hari ini menjadi <strong>Hadir (Terlambat)</strong>.
                        </div>
                    </div>

                    <!-- Tanggal & Shift -->
                    <div class="row g-2 mb-3">
                        <div class="col-7">
                            <label for="input_tanggal" class="form-label small fw-semibold text-secondary">Tanggal</label>
                            <input type="date" class="form-control" id="input_tanggal" name="tanggal" value="<?= esc($tanggal) ?>" required>
                        </div>
                        <div class="col-5">
                            <label for="input_shift" class="form-label small fw-semibold text-secondary">Shift</label>
                            <select class="form-select" id="input_shift" name="shift" onchange="hitungMenitTerlambat()">
                                <option value="Pagi" <?= $shift === 'Pagi' ? 'selected' : '' ?>>Pagi (07:00)</option>
                                <option value="Siang" <?= $shift === 'Siang' ? 'selected' : '' ?>>Siang (12:30)</option>
                            </select>
                        </div>
                    </div>

                    <!-- Pilih Siswa (Filter Kelas & Siswa) -->
                    <div class="mb-3">
                        <label for="select_kelas" class="form-label small fw-semibold text-secondary">Filter Kelas</label>
                        <select class="form-select form-select-sm mb-2" id="select_kelas" onchange="filterSiswaByKelas()">
                            <option value="">-- Tampilkan Seluruh Kelas --</option>
                            <?php foreach ($kelasList as $k): ?>
                                <option value="<?= $k['id'] ?>"><?= esc($k['nama_kelas']) ?> (<?= esc($k['shift'] ?? 'Pagi') ?>)</option>
                            <?php endforeach; ?>
                        </select>

                        <label for="select_siswa" class="form-label small fw-semibold text-secondary">Pilih Siswa <span class="text-danger">*</span></label>
                        <select class="form-select" id="select_siswa" name="siswa_id" required>
                            <option value="">-- Cari / Pilih Siswa --</option>
                            <?php foreach ($siswaList as $s): ?>
                                <option value="<?= $s['id'] ?>" data-kelas="<?= $s['kelas_id'] ?>">
                                    <?= esc($s['nama_siswa']) ?> (<?= esc($s['nama_kelas']) ?> - NISN: <?= esc($s['nisn']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Jam Datang & Indikator Menit Terlambat -->
                    <div class="mb-3">
                        <label for="input_jam" class="form-label small fw-semibold text-secondary">Jam Kedatangan Siswa <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-clock-fill text-danger"></i></span>
                            <input type="time" class="form-control" id="input_jam" name="jam_masuk" value="<?= date('H:i') ?>" required onchange="hitungMenitTerlambat()">
                        </div>
                        <div class="mt-2" id="boxMenitTerlambat">
                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-2 py-1" id="labelMenitTerlambat">
                                Terlambat: Sedang dihitung...
                            </span>
                        </div>
                    </div>

                    <!-- Alasan Keterlambatan -->
                    <div class="mb-3">
                        <label for="input_alasan" class="form-label small fw-semibold text-secondary">Alasan Keterlambatan</label>
                        <div class="d-flex flex-wrap gap-1 mb-2">
                            <button type="button" class="btn btn-sm btn-light border" onclick="pilihAlasan('Bangun kesiangan')">Bangun kesiangan</button>
                            <button type="button" class="btn btn-sm btn-light border" onclick="pilihAlasan('Macet di perjalanan')">Macet</button>
                            <button type="button" class="btn btn-sm btn-light border" onclick="pilihAlasan('Kendaraan rusak / ban bocor')">Kendaraan rusak</button>
                            <button type="button" class="btn btn-sm btn-light border" onclick="pilihAlasan('Hujan deras / cuaca')">Hujan deras</button>
                            <button type="button" class="btn btn-sm btn-light border" onclick="pilihAlasan('Keperluan keluarga')">Keperluan keluarga</button>
                        </div>
                        <input type="text" class="form-control" id="input_alasan" name="alasan" placeholder="Tulis atau pilih alasan di atas...">
                    </div>

                    <!-- Tindakan Pembinaan -->
                    <div class="mb-3">
                        <label for="input_tindakan" class="form-label small fw-semibold text-secondary">Tindakan Pembinaan Meja Piket</label>
                        <div class="d-flex flex-wrap gap-1 mb-2">
                            <button type="button" class="btn btn-sm btn-light border" onclick="pilihTindakan('Diberikan pengarahan disiplin & surat izin masuk')">Pengarahan & izin masuk</button>
                            <button type="button" class="btn btn-sm btn-light border" onclick="pilihTindakan('Tugas kebersihan lingkungan sekolah 15 menit')">Tugas kebersihan</button>
                            <button type="button" class="btn btn-sm btn-light border" onclick="pilihTindakan('Pencatatan buku pelanggaran piket')">Buku pelanggaran</button>
                        </div>
                        <input type="text" class="form-control" id="input_tindakan" name="tindakan" placeholder="Contoh: Diberikan pengarahan dan surat izin masuk kelas">
                    </div>
                </div>

                <div class="card-footer bg-white border-top py-3 text-end">
                    <button type="submit" class="btn btn-danger text-white px-4">
                        <i class="bi bi-save2-fill me-1"></i> Simpan Siswa Terlambat
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Kolom Kanan: Log Siswa Terlambat Hari Ini -->
    <div class="col-12 col-lg-7">
        <div class="card card-custom border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold text-dark mb-0">
                        <i class="bi bi-card-list text-primary me-2"></i>Log Siswa Terlambat
                    </h5>
                    <small class="text-muted">Tanggal: <?= esc($tanggal) ?> (<?= count($terlambatList) ?> Terdata)</small>
                </div>
                <form action="<?= site_url('piket/siswa-terlambat') ?>" method="GET" class="d-flex gap-2">
                    <input type="date" name="tanggal" class="form-control form-control-sm" value="<?= esc($tanggal) ?>" onchange="this.form.submit()">
                </form>
            </div>

            <div class="table-responsive">
                <table class="table table-custom table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 40px;">No</th>
                            <th style="width: 110px;">Jam Datang</th>
                            <th>Nama Siswa & Kelas</th>
                            <th style="width: 100px;" class="text-center">Keterlambatan</th>
                            <th>Alasan & Tindakan</th>
                            <th class="text-end" style="width: 60px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($terlambatList)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-check-circle-fill text-success fs-3 d-block mb-1"></i>
                                    <span class="fw-bold text-dark">Belum ada siswa terlambat pada tanggal ini.</span>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($terlambatList as $idx => $tl): ?>
                                <tr>
                                    <td class="text-center fw-semibold text-muted"><?= $idx + 1 ?></td>
                                    <td>
                                        <div class="fw-bold text-danger font-monospace">
                                            <i class="bi bi-clock me-1"></i> <?= esc(substr($tl['jam_masuk'], 0, 5)) ?>
                                        </div>
                                        <span class="badge bg-light text-secondary border" style="font-size: 0.7rem;"><?= esc($tl['shift']) ?></span>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= esc($tl['nama_siswa']) ?></div>
                                        <div class="small text-muted">
                                            <span class="badge bg-light text-primary border me-1"><?= esc($tl['nama_kelas'] ?? '-') ?></span>
                                            <span class="font-monospace">NISN: <?= esc($tl['nisn']) ?></span>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <?php if ($tl['menit_terlambat'] > 0): ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger">
                                                +<?= $tl['menit_terlambat'] ?> menit
                                            </span>
                                        <?php else: ?>
                                            <span class="badge bg-light text-secondary border">0 mnt</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="small">
                                        <div class="fw-semibold text-dark"><?= esc($tl['alasan'] ?? 'Tanpa alasan') ?></div>
                                        <?php if (! empty($tl['tindakan'])): ?>
                                            <div class="text-muted"><i class="bi bi-shield-check text-success me-1"></i> <?= esc($tl['tindakan']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="<?= site_url('piket/siswa-terlambat/hapus/' . $tl['id']) ?>" class="btn btn-sm btn-outline-danger rounded-circle p-1" onclick="return confirm('Hapus catatan keterlambatan ini?')" title="Hapus Catatan">
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
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function filterSiswaByKelas() {
        var kelasId = document.getElementById('select_kelas').value;
        var selectSiswa = document.getElementById('select_siswa');
        var options = selectSiswa.querySelectorAll('option');

        options.forEach(function(opt) {
            if (opt.value === '') {
                opt.style.display = 'block';
                return;
            }
            var optKelas = opt.getAttribute('data-kelas');
            if (kelasId === '' || optKelas === kelasId) {
                opt.style.display = 'block';
            } else {
                opt.style.display = 'none';
            }
        });
        selectSiswa.value = '';
    }

    function hitungMenitTerlambat() {
        var shift = document.getElementById('input_shift').value;
        var jamVal = document.getElementById('input_jam').value;
        var label = document.getElementById('labelMenitTerlambat');

        if (! jamVal) return;

        var parts = jamVal.split(':');
        var actualMinutes = parseInt(parts[0]) * 60 + parseInt(parts[1]);

        // Standar: Pagi 07:00 (420 menit), Siang 12:30 (750 menit)
        var standardMinutes = (shift === 'Siang') ? (12 * 60 + 30) : (7 * 60);

        var diff = actualMinutes - standardMinutes;

        if (diff > 0) {
            label.className = 'badge bg-danger bg-opacity-10 text-danger border border-danger px-2 py-1';
            label.innerHTML = '<i class="bi bi-alarm-fill me-1"></i> Terlambat: <strong>+' + diff + ' menit</strong> melewati batas ' + (shift === 'Siang' ? '12:30' : '07:00');
        } else {
            label.className = 'badge bg-success bg-opacity-10 text-success border border-success px-2 py-1';
            label.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Tepat waktu / Masuk sebelum batas ' + (shift === 'Siang' ? '12:30' : '07:00');
        }
    }

    function pilihAlasan(alasan) {
        document.getElementById('input_alasan').value = alasan;
    }

    function pilihTindakan(tindakan) {
        document.getElementById('input_tindakan').value = tindakan;
    }

    document.addEventListener('DOMContentLoaded', function() {
        hitungMenitTerlambat();
    });
</script>
<?= $this->endSection() ?>
