<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="row align-items-center mb-4">
    <div class="col-12 col-md-7">
        <h3 class="fw-bold text-dark mb-1">
            <i class="bi bi-mortarboard-fill text-primary me-2"></i>KBM &amp; Asesmen Live Interaktif
        </h3>
        <p class="text-muted small mb-0">Masuk kelas, presensi mapel, mulai materi pembelajaran, dan luncurkan asesmen live (diagnostik &amp; formatif).</p>
    </div>
    <div class="col-12 col-md-5 text-md-end mt-3 mt-md-0">
        <button type="button" class="btn btn-primary-custom rounded-pill px-4 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalMasukKelas">
            <i class="bi bi-door-open-fill me-1"></i> Mulai KBM Masuk Kelas
        </button>
    </div>
</div>

<!-- Modal Masuk Kelas & Mulai KBM -->
<div class="modal fade" id="modalMasukKelas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-primary text-white rounded-3 p-2">
                        <i class="bi bi-book-half fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0">Mulai Pertemuan KBM</h5>
                        <small class="text-muted">Pilih kelas dan mata pelajaran yang Anda ampu</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= site_url('lms/sesi/buat') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body py-4">
                    <!-- 1. Pilih Kelas -->
                    <div class="mb-3">
                        <label for="select_kelas_id" class="form-label small fw-semibold text-secondary">Pilih Kelas <span class="text-danger">*</span></label>
                        <select class="form-select" id="select_kelas_id" name="kelas_id" required onchange="muatMapelKelas(this.value)">
                            <option value="">-- Pilih Kelas Yang Diajar --</option>
                            <?php foreach ($kelasList as $k): ?>
                                <option value="<?= $k['id'] ?>"><?= esc($k['nama_kelas']) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- 2. Pilih Mapel (Spesifik Guru di Kelas ini) -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label for="select_mapel_id" class="form-label small fw-semibold text-secondary mb-0">Mata Pelajaran <span class="text-danger">*</span></label>
                            <a href="#" class="small text-primary text-decoration-none fw-semibold" onclick="bukaModalTambahMapel(); return false;">
                                <i class="bi bi-plus-circle me-1"></i>Tambah Mapel Baru
                            </a>
                        </div>
                        <select class="form-select" id="select_mapel_id" name="mapel_id" required>
                            <option value="">-- Pilih Kelas Terlebih Dahulu --</option>
                        </select>
                        <small class="text-muted" style="font-size: 0.76rem;" id="infoMapelKosong"></small>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-7">
                            <label class="form-label small fw-semibold text-secondary">Tanggal KBM</label>
                            <input type="date" class="form-control" name="tanggal" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="col-5">
                            <label class="form-label small fw-semibold text-secondary">Jam Pelajaran Ke-</label>
                            <input type="text" class="form-control" name="jam_ke" placeholder="Misal: 1 - 3">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Topik / Judul Materi Pembelajaran <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="judul_materi" required placeholder="Contoh: Instalasi & Konfigurasi Jaringan DHCP Server">
                    </div>

                    <div class="mb-0">
                        <label class="form-label small fw-semibold text-secondary">Capaian Pembelajaran / Catatan KBM</label>
                        <textarea class="form-control" name="deskripsi_materi" rows="2" placeholder="Catatan tujuan atau modul pembelajaran hari ini..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary-custom rounded-pill px-4 fw-semibold shadow-sm">
                        Lanjut ke Presensi Siswa &rarr;
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Tambah Mapel Baru untuk Guru di Kelas ini -->
<div class="modal fade" id="modalTambahMapel" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark">Tambah Mapel Guru di Kelas Ini</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= site_url('lms/mapel/tambah') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="kelas_id" id="tambah_mapel_kelas_id">
                <div class="modal-body py-4">
                    <div class="alert alert-info py-2 small mb-3">
                        Menambahkan mapel khusus yang Anda ajar di kelas <strong id="tambah_mapel_kelas_nama">-</strong>.
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Nama Mata Pelajaran <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="nama_mapel" required placeholder="Misal: Bisnis Teknologi Informasi">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold">Simpan Mapel</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Riwayat Sesi KBM Guru -->
<div class="card card-custom border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
        <div class="d-flex align-items-center gap-2">
            <i class="bi bi-clock-history text-primary fs-5"></i>
            <h5 class="fw-bold text-dark mb-0">Riwayat Sesi Pembelajaran &amp; KBM Saya</h5>
        </div>
        <span class="badge bg-light text-muted border"><?= count($riwayatSesi) ?> Sesi Tercatat</span>
    </div>

    <div class="table-responsive">
        <table class="table table-custom table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="text-center" style="width: 50px;">No</th>
                    <th>Tanggal &amp; Waktu</th>
                    <th>Kelas</th>
                    <th>Mata Pelajaran</th>
                    <th>Materi Pembelajaran</th>
                    <th class="text-center">Status Sesi</th>
                    <th class="text-center" style="width: 170px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($riwayatSesi)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-journal-x fs-1 d-block mb-2"></i>
                            Belum ada riwayat sesi KBM. Klik tombol <strong>"Mulai KBM Masuk Kelas"</strong> untuk memulai.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($riwayatSesi as $idx => $s): ?>
                        <tr>
                            <td class="text-center text-muted fw-semibold"><?= $idx + 1 ?></td>
                            <td>
                                <div class="fw-bold text-dark"><?= date('d/m/Y', strtotime($s['tanggal'])) ?></div>
                                <small class="text-muted"><?= !empty($s['jam_ke']) ? 'Jam: ' . esc($s['jam_ke']) : '' ?></small>
                            </td>
                            <td>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-2 py-1">
                                    <?= esc($s['nama_kelas']) ?>
                                </span>
                            </td>
                            <td class="fw-semibold text-dark"><?= esc($s['nama_mapel']) ?></td>
                            <td>
                                <div class="fw-bold text-dark"><?= esc($s['judul_materi']) ?></div>
                                <?php if (!empty($s['deskripsi_materi'])): ?>
                                    <small class="text-muted text-truncate d-block" style="max-width: 250px;"><?= esc($s['deskripsi_materi']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($s['status'] === 'berlangsung'): ?>
                                    <span class="badge bg-success bg-opacity-15 text-success border border-success px-2 py-1 rounded-pill">
                                        <i class="bi bi-broadcast me-1"></i> Berlangsung (Live)
                                    </span>
                                <?php elseif ($s['status'] === 'selesai'): ?>
                                    <span class="badge bg-secondary bg-opacity-15 text-muted border border-secondary px-2 py-1 rounded-pill">
                                        <i class="bi bi-check2-all me-1"></i> Selesai
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-warning bg-opacity-15 text-warning border border-warning px-2 py-1 rounded-pill">
                                        Draft
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <a href="<?= site_url('lms/sesi/' . $s['id']) ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1">
                                    <i class="bi bi-arrow-right-circle me-1"></i> Buka KBM
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function muatMapelKelas(kelasId) {
    const mapelSelect = document.getElementById('select_mapel_id');
    const infoText = document.getElementById('infoMapelKosong');
    mapelSelect.innerHTML = '<option value="">Memuat mapel Anda...</option>';
    infoText.textContent = '';

    if (!kelasId) {
        mapelSelect.innerHTML = '<option value="">-- Pilih Kelas Terlebih Dahulu --</option>';
        return;
    }

    fetch('<?= site_url('lms/mapel-by-kelas/') ?>' + kelasId)
        .then(response => response.json())
        .then(res => {
            if (res.status === 'success' && res.data.length > 0) {
                let html = '<option value="">-- Pilih Mata Pelajaran --</option>';
                res.data.forEach(m => {
                    html += `<option value="${m.mapel_id}">${m.nama_mapel}</option>`;
                });
                mapelSelect.innerHTML = html;
            } else {
                mapelSelect.innerHTML = '<option value="">Belum ada mapel di kelas ini</option>';
                infoText.innerHTML = 'Klik <strong>+ Tambah Mapel Baru</strong> di atas untuk menambahkan mapel yang Anda ajar.';
            }
        })
        .catch(err => {
            mapelSelect.innerHTML = '<option value="">Gagal memuat mapel</option>';
        });
}

function bukaModalTambahMapel() {
    const kelasSelect = document.getElementById('select_kelas_id');
    const kelasId = kelasSelect.value;
    if (!kelasId) {
        alert('Silakan pilih kelas terlebih dahulu sebelum menambah mapel.');
        return;
    }

    document.getElementById('tambah_mapel_kelas_id').value = kelasId;
    document.getElementById('tambah_mapel_kelas_nama').textContent = kelasSelect.options[kelasSelect.selectedIndex].text;

    const modal = new bootstrap.Modal(document.getElementById('modalTambahMapel'));
    modal.show();
}
</script>
<?= $this->endSection() ?>

