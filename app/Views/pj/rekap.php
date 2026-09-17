<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="row mb-4 align-items-center">
    <div class="col-12 col-md-6">
        <h3 class="fw-bold text-dark mb-1">
            <i class="bi bi-journal-text text-primary me-2"></i>Rekap Presensi Kelas <?= esc($kelas['nama_kelas']) ?>
        </h3>
        <p class="text-muted small mb-0">
            Ringkasan kehadiran teman sekelas | Periode: <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-2 py-1"><i class="bi bi-calendar3 me-1"></i><?= esc($labelPeriode) ?></span>
        </p>
    </div>
    <div class="col-12 col-md-6 mt-3 mt-md-0">
        <!-- Filter Periode (Bulan / Semester / Semua Waktu) -->
        <form action="<?= site_url('pj/rekap') ?>" method="GET" class="d-flex flex-wrap align-items-center justify-content-md-end gap-2">
            <select name="periode" class="form-select form-select-sm w-auto shadow-sm" id="selectPjPeriode" onchange="togglePjFilter()">
                <option value="bulan" <?= $periode === 'bulan' ? 'selected' : '' ?>>Per Bulan</option>
                <option value="semester" <?= $periode === 'semester' ? 'selected' : '' ?>>Per Semester</option>
                <option value="all" <?= $periode === 'all' ? 'selected' : '' ?>>Semua Waktu</option>
            </select>

            <div id="groupPjBulan" class="<?= $periode !== 'bulan' ? 'd-none' : '' ?>">
                <select name="bulan" class="form-select form-select-sm w-auto shadow-sm">
                    <?php
                    $bulanList = [
                        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                    ];
                    foreach ($bulanList as $k => $v):
                    ?>
                        <option value="<?= $k ?>" <?= (int)$bulan === $k ? 'selected' : '' ?>><?= $v ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div id="groupPjSemester" class="<?= $periode !== 'semester' ? 'd-none' : '' ?>">
                <select name="semester" class="form-select form-select-sm w-auto shadow-sm">
                    <option value="ganjil" <?= $semester === 'ganjil' ? 'selected' : '' ?>>Semester Ganjil (Jul - Des)</option>
                    <option value="genap" <?= $semester === 'genap' ? 'selected' : '' ?>>Semester Genap (Jan - Jun)</option>
                </select>
            </div>

            <div id="groupPjTahun" class="<?= $periode === 'all' ? 'd-none' : '' ?>">
                <select name="tahun" class="form-select form-select-sm w-auto shadow-sm">
                    <?php 
                    $currY = (int)date('Y');
                    for ($y = $currY; $y >= $currY - 3; $y--): 
                    ?>
                        <option value="<?= $y ?>" <?= (int)$tahun === $y ? 'selected' : '' ?>><?= $y ?></option>
                    <?php endfor; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-sm btn-primary-custom px-3">
                <i class="bi bi-filter me-1"></i> Filter
            </button>
        </form>
    </div>
</div>

<div class="card card-custom border-0 shadow-sm">
    <div class="table-responsive">
        <table class="table table-custom table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="text-center" style="width: 50px;">No</th>
                    <th style="width: 140px;">NISN</th>
                    <th>Nama Siswa</th>
                    <th class="text-center" style="width: 100px;">Hadir (H)</th>
                    <th class="text-center" style="width: 100px;">Sakit (S)</th>
                    <th class="text-center" style="width: 100px;">Izin (I)</th>
                    <th class="text-center" style="width: 100px;">Alpa (A)</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rekap)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            Belum ada riwayat presensi di periode ini.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rekap as $idx => $r): ?>
                        <tr>
                            <td class="text-center fw-semibold text-muted"><?= $idx + 1 ?></td>
                            <td><span class="badge bg-light text-dark border font-monospace"><?= esc($r['nisn']) ?></span></td>
                            <td class="fw-bold text-dark"><?= esc($r['nama_siswa']) ?></td>
                            <td class="text-center"><span class="badge badge-h px-3 py-2 fs-6 rounded-pill"><?= $r['total_h'] ?></span></td>
                            <td class="text-center"><span class="badge badge-s px-3 py-2 fs-6 rounded-pill"><?= $r['total_s'] ?></span></td>
                            <td class="text-center"><span class="badge badge-i px-3 py-2 fs-6 rounded-pill"><?= $r['total_i'] ?></span></td>
                            <td class="text-center">
                                <span class="badge badge-a px-3 py-2 fs-6 rounded-pill <?= $r['total_a'] > 3 ? 'bg-danger text-white' : '' ?>">
                                    <?= $r['total_a'] ?>
                                </span>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function togglePjFilter() {
    const val = document.getElementById('selectPjPeriode').value;
    const gb = document.getElementById('groupPjBulan');
    const gs = document.getElementById('groupPjSemester');
    const gt = document.getElementById('groupPjTahun');

    if (val === 'bulan') {
        gb.classList.remove('d-none');
        gs.classList.add('d-none');
        gt.classList.remove('d-none');
    } else if (val === 'semester') {
        gb.classList.add('d-none');
        gs.classList.remove('d-none');
        gt.classList.remove('d-none');
    } else {
        gb.classList.add('d-none');
        gs.classList.add('d-none');
        gt.classList.add('d-none');
    }
}
</script>
<?= $this->endSection() ?>
