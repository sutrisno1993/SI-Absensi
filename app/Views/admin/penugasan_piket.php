<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="row mb-4 align-items-center">
    <div class="col-12 col-md-8">
        <h3 class="fw-bold text-dark mb-1">
            <i class="bi bi-person-check-fill text-primary me-2"></i>Penugasan Jadwal Guru Piket
        </h3>
        <p class="text-muted small mb-0">Atur jadwal guru yang bertugas di meja piket sekolah untuk setiap hari dan shift.</p>
    </div>
    <div class="col-12 col-md-4 text-md-end mt-3 mt-md-0">
        <a href="<?= site_url('piket') ?>" class="btn btn-outline-primary" target="_blank">
            <i class="bi bi-box-arrow-up-right me-1"></i> Buka Portal Piket
        </a>
    </div>
</div>

<div class="alert alert-info border-0 shadow-sm rounded-4 d-flex align-items-center gap-3 mb-4">
    <div class="fs-2 text-info"><i class="bi bi-info-circle-fill"></i></div>
    <div>
        <div class="fw-bold text-dark">Fleksibilitas Sistem Piket</div>
        <small class="text-secondary">
            Meskipun guru piket dapat ditugaskan secara terjadwal di bawah ini, portal piket tetap dapat dibuka dan diinput secara fleksibel oleh akun <strong>Guru</strong> maupun <strong>Admin</strong> jika sewaktu-waktu terjadi pergantian guru piket dadakan.
        </small>
    </div>
</div>

<!-- Grid Jadwal Piket per Hari -->
<div class="row g-4">
    <?php foreach ($hariList as $hari): ?>
        <div class="col-12 col-lg-6">
            <div class="card card-custom border-0 shadow-sm h-100">
                <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold text-dark mb-0">
                        <i class="bi bi-calendar-event text-primary me-2"></i><?= $hari ?>
                    </h5>
                    <span class="badge bg-light text-secondary border">Jadwal Harian</span>
                </div>
                <div class="card-body p-3">
                    <div class="row g-3">
                        <!-- Shift Pagi -->
                        <div class="col-12 col-sm-6">
                            <div class="border rounded-3 p-3 bg-light h-100 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-warning bg-opacity-20 text-warning-emphasis border border-warning border-opacity-50">
                                            <i class="bi bi-sun-fill text-warning me-1"></i> Shift Pagi
                                        </span>
                                        <small class="text-muted fw-semibold">07:00</small>
                                    </div>
                                    <div class="mt-2">
                                        <?php 
                                        $petugasPagi = $penugasanGrouped[$hari]['Pagi'] ?? [];
                                        ?>
                                        <?php if (empty($petugasPagi)): ?>
                                            <div class="small text-muted py-2 fst-italic">Belum ada guru piket ditugaskan</div>
                                        <?php else: ?>
                                            <ul class="list-unstyled mb-0 small">
                                                <?php foreach ($petugasPagi as $pt): ?>
                                                    <li class="mb-1 text-dark fw-semibold d-flex align-items-center gap-1">
                                                        <i class="bi bi-person-fill text-primary"></i> <?= esc($pt['nama_guru']) ?>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary w-100 mt-3" 
                                        onclick="aturPiket('<?= $hari ?>', 'Pagi', <?= htmlspecialchars(json_encode(array_column($petugasPagi, 'guru_id')), ENT_QUOTES, 'UTF-8') ?>)">
                                    <i class="bi bi-pencil-square me-1"></i> Atur Petugas
                                </button>
                            </div>
                        </div>

                        <!-- Shift Siang -->
                        <div class="col-12 col-sm-6">
                            <div class="border rounded-3 p-3 bg-light h-100 d-flex flex-column justify-content-between">
                                <div>
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <span class="badge bg-info bg-opacity-10 text-primary border border-info border-opacity-50">
                                            <i class="bi bi-cloud-sun-fill text-primary me-1"></i> Shift Siang
                                        </span>
                                        <small class="text-muted fw-semibold">12:30</small>
                                    </div>
                                    <div class="mt-2">
                                        <?php 
                                        $petugasSiang = $penugasanGrouped[$hari]['Siang'] ?? [];
                                        ?>
                                        <?php if (empty($petugasSiang)): ?>
                                            <div class="small text-muted py-2 fst-italic">Belum ada guru piket ditugaskan</div>
                                        <?php else: ?>
                                            <ul class="list-unstyled mb-0 small">
                                                <?php foreach ($petugasSiang as $pt): ?>
                                                    <li class="mb-1 text-dark fw-semibold d-flex align-items-center gap-1">
                                                        <i class="bi bi-person-fill text-primary"></i> <?= esc($pt['nama_guru']) ?>
                                                    </li>
                                                <?php endforeach; ?>
                                            </ul>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <button type="button" class="btn btn-sm btn-outline-primary w-100 mt-3" 
                                        onclick="aturPiket('<?= $hari ?>', 'Siang', <?= htmlspecialchars(json_encode(array_column($petugasSiang, 'guru_id')), ENT_QUOTES, 'UTF-8') ?>)">
                                    <i class="bi bi-pencil-square me-1"></i> Atur Petugas
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Modal Atur Petugas Piket -->
<div class="modal fade" id="modalAturPiket" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <div>
                    <h5 class="modal-title fw-bold text-dark" id="modalPiketTitle">Atur Petugas Piket</h5>
                    <p class="text-muted small mb-0" id="modalPiketSubtitle">Pilih guru yang bertugas</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= site_url('admin/penugasan-piket/simpan') ?>" method="POST">
                <?= csrf_field() ?>
                <input type="hidden" name="hari" id="piket_hari">
                <input type="hidden" name="shift" id="piket_shift">
                
                <div class="modal-body py-4">
                    <label class="form-label small fw-semibold text-secondary mb-2">Pilih Guru yang Bertugas:</label>
                    <div class="border rounded-3 p-3 bg-light" style="max-height: 280px; overflow-y: auto;">
                        <?php foreach ($guruList as $g): ?>
                            <div class="form-check py-1 border-bottom border-light">
                                <input class="form-check-input check-petugas" type="checkbox" name="guru_ids[]" value="<?= $g['id'] ?>" id="piket_guru_<?= $g['id'] ?>">
                                <label class="form-check-label small fw-semibold text-dark cursor-pointer" for="piket_guru_<?= $g['id'] ?>">
                                    <?= esc($g['nama_guru']) ?>
                                    <span class="text-muted fw-normal ms-1" style="font-size: 0.75rem;">(NIP: <?= esc($g['nip'] ?? '-') ?>)</span>
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="form-text text-muted mt-2" style="font-size: 0.75rem;">
                        Anda dapat memilih lebih dari satu guru piket untuk setiap shift.
                    </div>
                </div>
                
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary-custom px-4">Simpan Penugasan</button>
                </div>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    var modalAturPiket = new bootstrap.Modal(document.getElementById('modalAturPiket'));

    function aturPiket(hari, shift, currentGuruIds) {
        document.getElementById('piket_hari').value = hari;
        document.getElementById('piket_shift').value = shift;
        document.getElementById('modalPiketTitle').innerText = 'Petugas Piket ' + hari;
        document.getElementById('modalPiketSubtitle').innerText = 'Pilih guru untuk Shift ' + shift;

        // Reset all checkboxes
        document.querySelectorAll('.check-petugas').forEach(function(chk) {
            chk.checked = false;
        });

        // Check assigned ones
        if (currentGuruIds && Array.isArray(currentGuruIds)) {
            currentGuruIds.forEach(function(gid) {
                var el = document.getElementById('piket_guru_' + gid);
                if (el) el.checked = true;
            });
        }

        modalAturPiket.show();
    }
</script>
<?= $this->endSection() ?>
