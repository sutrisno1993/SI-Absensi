<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="row mb-4 align-items-center">
    <div class="col-12 col-md-6">
        <h3 class="fw-bold text-dark mb-1">
            <i class="bi bi-calendar-check text-primary me-2"></i>Rekapitulasi Presensi Kelas <?= esc($kelas['nama_kelas']) ?>
        </h3>
        <p class="text-muted small mb-0">
            Laporan kehadiran siswa | Periode: <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-2 py-1"><i class="bi bi-calendar3 me-1"></i><?= esc($labelPeriode) ?></span>
        </p>
    </div>
    <div class="col-12 col-md-6 mt-3 mt-md-0 d-flex justify-content-md-end flex-wrap gap-2">
        <a href="<?= site_url('walas/rekap/export-excel?' . http_build_query(['periode' => $periode, 'bulan' => $bulan, 'tahun' => $tahun, 'semester' => $semester])) ?>" class="btn btn-outline-success btn-sm">
            <i class="bi bi-file-earmark-excel-fill me-1"></i> Download Excel
        </a>
        <a href="<?= site_url('walas/absen') ?>" class="btn btn-primary-custom btn-sm">
            <i class="bi bi-pencil-square me-1"></i> Input / Edit Presensi
        </a>
        <button class="btn btn-outline-secondary btn-sm" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Cetak Rekap
        </button>
    </div>
</div>

<!-- Filter Periode (Per Bulan / Per Semester / Semua Waktu) -->
<div class="card card-custom border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form action="<?= site_url('walas/rekap') ?>" method="GET" class="d-flex flex-wrap align-items-center gap-2">
            <div class="d-flex align-items-center gap-1">
                <span class="small fw-semibold text-secondary"><i class="bi bi-funnel me-1"></i>Periode:</span>
                <select name="periode" class="form-select form-select-sm w-auto shadow-sm" id="selectWalasPeriode" onchange="toggleWalasFilter()">
                    <option value="bulan" <?= $periode === 'bulan' ? 'selected' : '' ?>>Per Bulan</option>
                    <option value="semester" <?= $periode === 'semester' ? 'selected' : '' ?>>Per Semester</option>
                    <option value="all" <?= $periode === 'all' ? 'selected' : '' ?>>Semua Waktu</option>
                </select>
            </div>

            <div id="groupWalasBulan" class="<?= $periode !== 'bulan' ? 'd-none' : '' ?>">
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

            <div id="groupWalasSemester" class="<?= $periode !== 'semester' ? 'd-none' : '' ?>">
                <select name="semester" class="form-select form-select-sm w-auto shadow-sm">
                    <option value="ganjil" <?= $semester === 'ganjil' ? 'selected' : '' ?>>Semester Ganjil (Jul - Des)</option>
                    <option value="genap" <?= $semester === 'genap' ? 'selected' : '' ?>>Semester Genap (Jan - Jun)</option>
                </select>
            </div>

            <div id="groupWalasTahun" class="<?= $periode === 'all' ? 'd-none' : '' ?>">
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
                <i class="bi bi-filter me-1"></i> Terapkan
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
                    <th class="text-center" style="width: 110px;">Hadir (H)</th>
                    <th class="text-center" style="width: 110px;">Sakit (S)</th>
                    <th class="text-center" style="width: 110px;">Izin (I)</th>
                    <th class="text-center" style="width: 110px;">Alpa (A)</th>
                    <th class="text-center" style="width: 160px;">Status Disiplin</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rekap)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">
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
                            <td class="text-center">
                                <?php if ($r['total_a'] > 3): ?>
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-2 py-1">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Perlu Dibina
                                    </span>
                                <?php elseif ($r['total_a'] > 0): ?>
                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning px-2 py-1">
                                        Perhatian
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success px-2 py-1">
                                        Disiplin Baik
                                    </span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function toggleWalasFilter() {
    const val = document.getElementById('selectWalasPeriode').value;
    const gb = document.getElementById('groupWalasBulan');
    const gs = document.getElementById('groupWalasSemester');
    const gt = document.getElementById('groupWalasTahun');

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
