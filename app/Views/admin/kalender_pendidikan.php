<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="row mb-4 align-items-center">
    <div class="col-12 col-md-7">
        <h3 class="fw-bold text-dark mb-1">
            <i class="bi bi-calendar-event text-primary me-2"></i>Kalender Pendidikan & Hari Libur Sekolah
        </h3>
        <p class="text-muted small mb-0">Kelola tanggal libur resmi, cuti bersama, dan agenda/acara sekolah (HUT, Ujian TKA, STS, Porseni) agar presensi tidak salah terdeteksi kelewat.</p>
    </div>
    <div class="col-12 col-md-5 mt-3 mt-md-0 text-md-end">
        <button type="button" class="btn btn-primary-custom px-4 shadow-sm" onclick="bukaModalTambah()">
            <i class="bi bi-plus-circle me-1"></i> Tambah Hari Libur / Acara
        </button>
    </div>
</div>

<!-- Info Alert -->
<div class="alert alert-info border-0 shadow-sm rounded-4 d-flex align-items-center gap-3 mb-4">
    <div class="fs-2 text-info">
        <i class="bi bi-info-circle-fill"></i>
    </div>
    <div class="small">
        <strong>Ketentuan Presensi Hari Aktif:</strong> Hari sekolah aktif dihitung <strong>Senin s/d Sabtu</strong> (hari Minggu otomatis libur). Tanggal yang didaftarkan di sini dengan status <strong>"Bebas Absen"</strong> otomatis dikecualikan dari audit presensi kelewat dan tidak akan dianggap alpa bagi kelas/tingkat yang bersangkutan.
    </div>
</div>

<!-- Card Tabel Agenda -->
<div class="card card-custom border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
        <h5 class="fw-bold text-dark mb-0">Daftar Agenda, Libur & Kegiatan Sekolah</h5>
        <span class="badge bg-light text-muted border px-3 py-2">Total: <?= count($agendaList) ?> Agenda</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
                <tr>
                    <th class="text-center" style="width: 50px;">No</th>
                    <th>Nama Agenda / Kegiatan</th>
                    <th style="width: 220px;">Rentang Tanggal</th>
                    <th class="text-center" style="width: 130px;">Tipe</th>
                    <th class="text-center" style="width: 140px;">Status KBM</th>
                    <th class="text-center" style="width: 150px;">Sasaran</th>
                    <th class="text-end" style="width: 120px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($agendaList)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-calendar-x fs-1 d-block mb-2 text-secondary opacity-50"></i>
                            Belum ada agenda hari libur atau acara sekolah yang didaftarkan.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($agendaList as $idx => $a): ?>
                        <?php
                            $mulai   = strtotime($a['tanggal_mulai']);
                            $selesai = strtotime($a['tanggal_selesai']);
                            $durasi  = round(($selesai - $mulai) / (60 * 60 * 24)) + 1;
                        ?>
                        <tr>
                            <td class="text-center text-muted fw-semibold"><?= $idx + 1 ?></td>
                            <td>
                                <div class="fw-bold text-dark fs-6"><?= esc($a['nama_agenda']) ?></div>
                                <?php if (!empty($a['keterangan'])): ?>
                                    <small class="text-muted d-block"><?= esc($a['keterangan']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-1 small fw-semibold text-secondary">
                                    <i class="bi bi-calendar-range text-primary"></i>
                                    <span><?= date('d M Y', $mulai) ?></span>
                                    <?php if ($a['tanggal_mulai'] !== $a['tanggal_selesai']): ?>
                                        <span>s/d</span>
                                        <span><?= date('d M Y', $selesai) ?></span>
                                    <?php endif; ?>
                                </div>
                                <small class="text-muted"><?= $durasi ?> hari</small>
                            </td>
                            <td class="text-center">
                                <?php if ($a['tipe'] === 'libur'): ?>
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1">
                                        <i class="bi bi-x-circle me-1"></i> Libur Resmi
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-1">
                                        <i class="bi bi-stars me-1"></i> Acara Sekolah
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($a['keterangan_kbm'] === 'bebas_absen'): ?>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2 py-1">
                                        <i class="bi bi-check-circle me-1"></i> Bebas Absen
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-25 px-2 py-1">
                                        <i class="bi bi-clipboard-check me-1"></i> Wajib Absen
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($a['target_tingkat'] === 'semua'): ?>
                                    <span class="badge bg-light text-dark border px-2 py-1">Semua Siswa</span>
                                <?php elseif (!empty($a['nama_kelas'])): ?>
                                    <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 px-2 py-1"><?= esc($a['nama_kelas']) ?></span>
                                <?php else: ?>
                                    <span class="badge bg-purple bg-opacity-10 text-purple border px-2 py-1" style="background-color: rgba(139,92,246,0.15); color: #7c3aed; border-color: rgba(139,92,246,0.3) !important;">
                                        Tingkat <?= esc($a['target_tingkat']) ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1" onclick='bukaModalEdit(<?= json_encode($a) ?>)'>
                                    <i class="bi bi-pencil"></i>
                                </button>
                                <a href="<?= site_url('admin/kalender-pendidikan/hapus/' . $a['id']) ?>" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1" onclick="return confirm('Apakah Anda yakin ingin menghapus agenda ini?')">
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

<!-- Modal Tambah / Edit Agenda -->
<div class="modal fade" id="modalAgenda" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <form action="<?= site_url('admin/kalender-pendidikan/simpan') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="id" id="agenda_id" value="">

                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold text-dark" id="modalTitle">Tambah Hari Libur / Acara Sekolah</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="nama_agenda" class="form-label fw-semibold text-secondary small">Nama Agenda / Kegiatan <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3" id="nama_agenda" name="nama_agenda" placeholder="Contoh: HUT Sekolah, Ujian TKA, Libur Idul Fitri..." required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="tanggal_mulai" class="form-label fw-semibold text-secondary small">Tanggal Mulai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control rounded-3" id="tanggal_mulai" name="tanggal_mulai" required>
                        </div>
                        <div class="col-6">
                            <label for="tanggal_selesai" class="form-label fw-semibold text-secondary small">Tanggal Selesai <span class="text-danger">*</span></label>
                            <input type="date" class="form-control rounded-3" id="tanggal_selesai" name="tanggal_selesai" required>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label for="tipe" class="form-label fw-semibold text-secondary small">Tipe Agenda <span class="text-danger">*</span></label>
                            <select class="form-select rounded-3" id="tipe" name="tipe" required>
                                <option value="libur">Libur Resmi (KBM Libur Total)</option>
                                <option value="acara">Acara / Kegiatan Sekolah</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label for="keterangan_kbm" class="form-label fw-semibold text-secondary small">Aturan Presensi <span class="text-danger">*</span></label>
                            <select class="form-select rounded-3" id="keterangan_kbm" name="keterangan_kbm" required>
                                <option value="bebas_absen">Bebas Absen (Tidak Dituntut)</option>
                                <option value="wajib_absen">Tetap Wajib Absen</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="target_tingkat" class="form-label fw-semibold text-secondary small">Sasaran / Tingkat Kelas <span class="text-danger">*</span></label>
                        <select class="form-select rounded-3" id="target_tingkat" name="target_tingkat" onchange="toggleKelasSelect(this.value)" required>
                            <option value="semua">Semua Tingkat (Seluruh Sekolah)</option>
                            <option value="X">Khusus Kelas 1 (Tingkat X)</option>
                            <option value="XI">Khusus Kelas 2 (Tingkat XI)</option>
                            <option value="XII">Khusus Kelas 3 (Tingkat XII - misal Ujian TKA)</option>
                        </select>
                        <small class="text-muted d-block mt-1">Pilih tingkat tertentu jika acara hanya untuk kelas tersebut (misal TKA untuk kelas 3).</small>
                    </div>

                    <div class="mb-3">
                        <label for="keterangan" class="form-label fw-semibold text-secondary small">Catatan Tambahan (Opsional)</label>
                        <textarea class="form-control rounded-3" id="keterangan" name="keterangan" rows="2" placeholder="Keterangan singkat kegiatan..."></textarea>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary-custom rounded-pill px-4">Simpan Agenda</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const modalAgenda = new bootstrap.Modal(document.getElementById('modalAgenda'));

    function bukaModalTambah() {
        document.getElementById('modalTitle').textContent = 'Tambah Hari Libur / Acara Sekolah';
        document.getElementById('agenda_id').value = '';
        document.getElementById('nama_agenda').value = '';
        document.getElementById('tanggal_mulai').value = '<?= date('Y-m-d') ?>';
        document.getElementById('tanggal_selesai').value = '<?= date('Y-m-d') ?>';
        document.getElementById('tipe').value = 'libur';
        document.getElementById('keterangan_kbm').value = 'bebas_absen';
        document.getElementById('target_tingkat').value = 'semua';
        document.getElementById('keterangan').value = '';
        modalAgenda.show();
    }

    function bukaModalEdit(data) {
        document.getElementById('modalTitle').textContent = 'Edit Hari Libur / Acara Sekolah';
        document.getElementById('agenda_id').value = data.id;
        document.getElementById('nama_agenda').value = data.nama_agenda;
        document.getElementById('tanggal_mulai').value = data.tanggal_mulai;
        document.getElementById('tanggal_selesai').value = data.tanggal_selesai;
        document.getElementById('tipe').value = data.tipe;
        document.getElementById('keterangan_kbm').value = data.keterangan_kbm;
        document.getElementById('target_tingkat').value = data.target_tingkat || 'semua';
        document.getElementById('keterangan').value = data.keterangan || '';
        modalAgenda.show();
    }
</script>
<?= $this->endSection() ?>
