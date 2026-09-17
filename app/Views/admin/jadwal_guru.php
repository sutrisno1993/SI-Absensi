<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1">
                <li class="breadcrumb-item"><a href="<?= site_url('admin') ?>" class="text-decoration-none text-muted">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="<?= site_url('admin/guru') ?>" class="text-decoration-none text-muted">Data Guru</a></li>
                <li class="breadcrumb-item active text-white" aria-current="page">Jadwal Mengajar</li>
            </ol>
        </nav>
        <h3 class="fw-bold mb-1 d-flex align-items-center gap-2">
            <i class="bi bi-calendar2-week-fill text-primary"></i>
            <span>Pengaturan Jadwal Mengajar Guru</span>
        </h3>
        <p class="text-muted small mb-0">Tentukan guru yang mengajar di setiap hari dan shift dengan sistem checklist cepat.</p>
    </div>

    <div class="d-flex gap-2">
        <a href="<?= site_url('admin/guru') ?>" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="bi bi-people me-1"></i> Data Master Guru
        </a>
        <a href="<?= site_url('admin/penugasan-piket') ?>" class="btn btn-outline-primary btn-sm rounded-pill px-3">
            <i class="bi bi-shield-check me-1"></i> Jadwal Guru Piket
        </a>
    </div>
</div>

<!-- PANEL PEMILIH HARI & SHIFT -->
<div class="card card-custom border-0 shadow-sm mb-4">
    <div class="card-body p-3 p-md-4">
        <div class="row g-3 align-items-center">
            <!-- 1. Pilih Hari -->
            <div class="col-12 col-lg-7">
                <label class="form-label small fw-semibold text-secondary mb-2 d-flex align-items-center gap-1">
                    <i class="bi bi-calendar3 text-primary"></i> 1. Pilih Hari Mengajar:
                </label>
                <div class="d-flex flex-wrap gap-2">
                    <?php foreach ($hariList as $h): ?>
                        <a href="<?= site_url('admin/jadwal-guru?hari=' . $h . '&shift=' . $selectedShift) ?>" 
                           class="btn btn-sm rounded-pill px-3 <?= $selectedHari === $h ? 'btn-primary' : 'btn-outline-secondary' ?>">
                            <i class="bi bi-calendar-event me-1"></i><?= $h ?>
                            <?php 
                            $countHari = count($matrixJadwal[$h][$selectedShift] ?? []);
                            if ($countHari > 0): ?>
                                <span class="badge bg-dark text-white rounded-pill ms-1" style="font-size: 0.7rem;"><?= $countHari ?></span>
                            <?php endif; ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- 2. Pilih Shift -->
            <div class="col-12 col-lg-5">
                <label class="form-label small fw-semibold text-secondary mb-2 d-flex align-items-center gap-1">
                    <i class="bi bi-clock-history text-warning"></i> 2. Pilih Shift Sekolah:
                </label>
                <div class="d-flex gap-2">
                    <a href="<?= site_url('admin/jadwal-guru?hari=' . $selectedHari . '&shift=Pagi') ?>" 
                       class="btn btn-sm rounded-pill flex-fill py-2 <?= $selectedShift === 'Pagi' ? 'btn-warning fw-bold text-dark' : 'btn-outline-warning' ?>">
                        <i class="bi bi-sun-fill me-1"></i> Shift Pagi
                        <span class="d-none d-sm-inline small ms-1">(07:00 - 12:30)</span>
                    </a>
                    <a href="<?= site_url('admin/jadwal-guru?hari=' . $selectedHari . '&shift=Siang') ?>" 
                       class="btn btn-sm rounded-pill flex-fill py-2 <?= $selectedShift === 'Siang' ? 'btn-info fw-bold text-white' : 'btn-outline-info' ?>">
                        <i class="bi bi-cloud-sun-fill me-1"></i> Shift Siang
                        <span class="d-none d-sm-inline small ms-1">(12:30 - 17:30)</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- FORM CHECKLIST GURU -->
<div class="card card-custom border-0 shadow-sm mb-4">
    <form action="<?= site_url('admin/jadwal-guru/simpan') ?>" method="POST" id="formJadwal">
        <?= csrf_field() ?>
        <input type="hidden" name="hari" value="<?= esc($selectedHari) ?>">
        <input type="hidden" name="shift" value="<?= esc($selectedShift) ?>">

        <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
                <div>
                    <h5 class="fw-bold mb-1 text-white d-flex align-items-center gap-2">
                        <span>Checklist Guru: Hari <strong><?= esc($selectedHari) ?></strong></span>
                        <span class="badge <?= $selectedShift === 'Pagi' ? 'bg-warning text-dark' : 'bg-info text-white' ?>">
                            <?= esc($selectedShift) ?>
                        </span>
                        <span class="badge bg-primary bg-opacity-20 text-primary border border-primary border-opacity-25" id="badgeSelectedCount">
                            <?= count($assignedGuruIds) ?> dari <?= count($guruList) ?> Guru Terpilih
                        </span>
                    </h5>
                    <p class="text-muted small mb-0">Centang guru yang memiliki jam mengajar pada sesi ini, lalu klik Simpan.</p>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" onclick="pilihSemua(true)">
                        <i class="bi bi-check-all me-1"></i> Pilih Semua
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" onclick="pilihSemua(false)">
                        <i class="bi bi-x me-1"></i> Batal Pilih
                    </button>
                    <div class="input-group input-group-sm" style="width: 220px;">
                        <span class="input-group-text bg-dark border-secondary border-opacity-50 text-muted">
                            <i class="bi bi-search"></i>
                        </span>
                        <input type="text" id="cariGuruInput" class="form-control bg-dark text-white border-secondary border-opacity-50" 
                               placeholder="Cari guru..." onkeyup="filterGuruChecklist()">
                    </div>
                </div>
            </div>
        </div>

        <div class="card-body p-4">
            <?php if (empty($guruList)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-people fs-1 d-block mb-2 text-secondary"></i>
                    Belum ada data master guru. Silakan tambahkan data guru terlebih dahulu.
                </div>
            <?php else: ?>
                <div class="row g-3" id="daftarGuruContainer">
                    <?php foreach ($guruList as $g): ?>
                        <?php 
                        $isChecked = in_array((int)$g['id'], $assignedGuruIds, true);
                        ?>
                        <div class="col-12 col-md-6 col-xl-4 guru-item" data-nama="<?= strtolower(esc($g['nama_guru'])) ?>" data-nip="<?= esc($g['nip'] ?? '') ?>">
                            <label class="card card-custom h-100 p-3 border border-secondary border-opacity-25 rounded-3 cursor-pointer user-select-none d-flex flex-row align-items-center gap-3 transition-all hover-highlight <?= $isChecked ? 'border-primary bg-primary bg-opacity-10' : '' ?>" for="guru_chk_<?= $g['id'] ?>">
                                <div class="form-check m-0">
                                    <input class="form-check-input guru-checkbox fs-5" type="checkbox" name="guru_ids[]" 
                                           value="<?= $g['id'] ?>" id="guru_chk_<?= $g['id'] ?>" 
                                           <?= $isChecked ? 'checked' : '' ?> onchange="updateItemHighlight(this)">
                                </div>
                                <div class="flex-grow-1 overflow-hidden">
                                    <div class="fw-semibold text-white text-truncate"><?= esc($g['nama_guru']) ?></div>
                                    <div class="d-flex align-items-center gap-2 text-muted small" style="font-size: 0.78rem;">
                                        <span class="font-monospace">NIP: <?= esc($g['nip'] ?? '-') ?></span>
                                        <?php if (!empty($g['no_hp'])): ?>
                                            <span>&bull; <?= esc($g['no_hp']) ?></span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="border-top border-secondary border-opacity-25 pt-4 mt-4 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-3">
                    <div class="small text-muted">
                        <i class="bi bi-info-circle me-1 text-primary"></i> Guru yang tidak dicentang otomatis tidak akan dianggap alfa pada meja guru piket hari <strong><?= esc($selectedHari) ?> (<?= esc($selectedShift) ?>)</strong>.
                    </div>
                    <button type="submit" class="btn btn-primary-custom px-5 py-2 fw-bold rounded-pill shadow">
                        <i class="bi bi-check-circle-fill me-2"></i> Simpan Jadwal <?= esc($selectedHari) ?> (<?= esc($selectedShift) ?>)
                    </button>
                </div>
            <?php endif; ?>
        </div>
    </form>
</div>

<!-- RINGKASAN MATRIKS JADWAL MENGAJAR MINGGUAN (OVERVIEW) -->
<div class="card card-custom border-0 shadow-sm">
    <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h5 class="fw-bold mb-1 text-white d-flex align-items-center gap-2">
                    <i class="bi bi-grid-3x3-gap-fill text-info"></i>
                    <span>Matriks Ringkasan Jadwal Mingguan Sekolah</span>
                </h5>
                <p class="text-muted small mb-0">Klik pada tombol "Atur Sesi" untuk langsung beralih dan mengedit sesi yang bersangkutan.</p>
            </div>
        </div>
    </div>
    <div class="card-body p-4">
        <div class="row g-3">
            <?php foreach ($hariList as $h): ?>
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="card bg-dark border <?= ($selectedHari === $h) ? 'border-primary shadow' : 'border-secondary border-opacity-25' ?> rounded-4 p-3 h-100">
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom border-secondary border-opacity-25">
                            <h6 class="fw-bold mb-0 text-white d-flex align-items-center gap-2">
                                <i class="bi bi-calendar2-day text-primary"></i> <?= $h ?>
                            </h6>
                            <?php if ($selectedHari === $h): ?>
                                <span class="badge bg-primary text-white small">Sedang Diedit</span>
                            <?php endif; ?>
                        </div>

                        <!-- Shift Pagi -->
                        <div class="mb-3 p-2 rounded-3 bg-secondary bg-opacity-10 border border-secondary border-opacity-25">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge bg-warning bg-opacity-20 text-warning border border-warning border-opacity-25 py-1 px-2 small">
                                    <i class="bi bi-sun-fill me-1"></i> Shift Pagi (<?= count($matrixJadwal[$h]['Pagi']) ?> Guru)
                                </span>
                                <a href="<?= site_url('admin/jadwal-guru?hari=' . $h . '&shift=Pagi') ?>" class="btn btn-sm btn-link text-warning p-0 text-decoration-none small">
                                    Atur Sesi &rarr;
                                </a>
                            </div>
                            <div class="small text-muted text-truncate" style="font-size: 0.75rem;">
                                <?php if (!empty($matrixJadwal[$h]['Pagi'])): ?>
                                    <?= esc(implode(', ', array_slice(array_column($matrixJadwal[$h]['Pagi'], 'nama_guru'), 0, 3))) ?>
                                    <?= count($matrixJadwal[$h]['Pagi']) > 3 ? '...' : '' ?>
                                <?php else: ?>
                                    <span class="fst-italic text-secondary">Belum ada guru mengajar</span>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Shift Siang -->
                        <div class="p-2 rounded-3 bg-secondary bg-opacity-10 border border-secondary border-opacity-25">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge bg-info bg-opacity-20 text-info border border-info border-opacity-25 py-1 px-2 small">
                                    <i class="bi bi-cloud-sun-fill me-1"></i> Shift Siang (<?= count($matrixJadwal[$h]['Siang']) ?> Guru)
                                </span>
                                <a href="<?= site_url('admin/jadwal-guru?hari=' . $h . '&shift=Siang') ?>" class="btn btn-sm btn-link text-info p-0 text-decoration-none small">
                                    Atur Sesi &rarr;
                                </a>
                            </div>
                            <div class="small text-muted text-truncate" style="font-size: 0.75rem;">
                                <?php if (!empty($matrixJadwal[$h]['Siang'])): ?>
                                    <?= esc(implode(', ', array_slice(array_column($matrixJadwal[$h]['Siang'], 'nama_guru'), 0, 3))) ?>
                                    <?= count($matrixJadwal[$h]['Siang']) > 3 ? '...' : '' ?>
                                <?php else: ?>
                                    <span class="fst-italic text-secondary">Belum ada guru mengajar</span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?= $this->section('scripts') ?>
<script>
    // Update visual border and background on toggle
    function updateItemHighlight(checkbox) {
        const card = checkbox.closest('label');
        if (checkbox.checked) {
            card.classList.add('border-primary', 'bg-primary', 'bg-opacity-10');
        } else {
            card.classList.remove('border-primary', 'bg-primary', 'bg-opacity-10');
        }
        updateCountBadge();
    }

    // Select / Deselect All
    function pilihSemua(select) {
        const checkboxes = document.querySelectorAll('.guru-checkbox');
        checkboxes.forEach(chk => {
            // Hanya toggle checkbox yang sedang terlihat (tidak difilter search)
            const parentCol = chk.closest('.guru-item');
            if (parentCol && parentCol.style.display !== 'none') {
                chk.checked = select;
                updateItemHighlight(chk);
            }
        });
        updateCountBadge();
    }

    // Update Counter Badge
    function updateCountBadge() {
        const total = document.querySelectorAll('.guru-checkbox').length;
        const checked = document.querySelectorAll('.guru-checkbox:checked').length;
        const badge = document.getElementById('badgeSelectedCount');
        if (badge) {
            badge.innerText = checked + ' dari ' + total + ' Guru Terpilih';
        }
    }

    // Live search filter
    function filterGuruChecklist() {
        const q = document.getElementById('cariGuruInput').value.toLowerCase().trim();
        const items = document.querySelectorAll('.guru-item');
        items.forEach(item => {
            const nama = item.getAttribute('data-nama') || '';
            const nip = item.getAttribute('data-nip') || '';
            if (nama.includes(q) || nip.includes(q)) {
                item.style.display = '';
            } else {
                item.style.display = 'none';
            }
        });
    }
</script>
<?= $this->endSection() ?>
<?= $this->endSection() ?>
