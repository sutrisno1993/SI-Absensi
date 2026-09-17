<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<!-- Breadcrumb Navigation -->
<nav aria-label="breadcrumb" class="mb-3">
    <ol class="breadcrumb">
        <li class="breadcrumb-item"><a href="<?= site_url('admin') ?>">Dashboard</a></li>
        <li class="breadcrumb-item"><a href="<?= site_url('admin/laporan?' . http_build_query(['periode' => $periode, 'bulan' => $bulan, 'tahun' => $tahun, 'semester' => $semester])) ?>">Laporan Presensi Sekolah</a></li>
        <li class="breadcrumb-item active" aria-current="page">Kelas <?= esc($kelas['nama_kelas']) ?></li>
    </ol>
</nav>

<div class="row mb-4 align-items-center">
    <div class="col-12 col-md-7">
        <h3 class="fw-bold text-dark mb-1">
            <i class="bi bi-door-open text-primary me-2"></i>Rincian Presensi Siswa Kelas <?= esc($kelas['nama_kelas']) ?>
        </h3>
        <p class="text-muted small mb-0">
            Wali Kelas: <strong><?= esc($kelas['nama_guru'] ?? 'Belum Ditentukan') ?></strong> | 
            NIP: <code><?= esc($kelas['nip'] ?? '-') ?></code> | 
            Periode: <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-2 py-1"><i class="bi bi-calendar3 me-1"></i><?= esc($labelPeriode) ?></span>
        </p>
    </div>
    <div class="col-12 col-md-5 text-md-end mt-3 mt-md-0 d-flex flex-wrap justify-content-md-end gap-2">
        <a href="<?= site_url('admin/laporan/export-kelas-excel/' . $kelas['id'] . '?' . http_build_query(['periode' => $periode, 'bulan' => $bulan, 'tahun' => $tahun, 'semester' => $semester])) ?>" class="btn btn-outline-success btn-sm">
            <i class="bi bi-file-earmark-excel-fill me-1"></i> Download Excel (.xlsx)
        </a>
        <a href="<?= site_url('admin/laporan?' . http_build_query(['periode' => $periode, 'bulan' => $bulan, 'tahun' => $tahun, 'semester' => $semester])) ?>" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left me-1"></i> Kembali
        </a>
        <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Cetak
        </button>
    </div>
</div>

<div class="card card-custom border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-primary px-3 py-2 rounded-pill fs-6">
                Kelas <?= esc($kelas['nama_kelas']) ?>
            </span>
            <span class="badge bg-light text-muted border px-2 py-2">
                Total Siswa: <?= count($rekapSiswa) ?> Orang
            </span>
        </div>

        <!-- Filter Periode (Semua Waktu, Per Bulan, Per Semester) -->
        <form action="<?= site_url('admin/laporan/kelas/' . $kelas['id']) ?>" method="GET" class="d-flex flex-wrap align-items-center gap-2">
            <select name="periode" class="form-select form-select-sm w-auto shadow-sm" id="selectKelasPeriode" onchange="toggleKelasFilter()">
                <option value="all" <?= $periode === 'all' ? 'selected' : '' ?>>Semua Waktu</option>
                <option value="bulan" <?= $periode === 'bulan' ? 'selected' : '' ?>>Per Bulan</option>
                <option value="semester" <?= $periode === 'semester' ? 'selected' : '' ?>>Per Semester</option>
            </select>

            <div id="groupKelasBulan" class="<?= $periode !== 'bulan' ? 'd-none' : '' ?>">
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

            <div id="groupKelasSemester" class="<?= $periode !== 'semester' ? 'd-none' : '' ?>">
                <select name="semester" class="form-select form-select-sm w-auto shadow-sm">
                    <option value="ganjil" <?= $semester === 'ganjil' ? 'selected' : '' ?>>Semester Ganjil (Jul-Des)</option>
                    <option value="genap" <?= $semester === 'genap' ? 'selected' : '' ?>>Semester Genap (Jan-Jun)</option>
                </select>
            </div>

            <div id="groupKelasTahun" class="<?= $periode === 'all' ? 'd-none' : '' ?>">
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

    <div class="table-responsive">
        <table class="table table-custom table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="text-center" style="width: 50px;">No</th>
                    <th style="width: 140px;">NISN</th>
                    <th>Nama Siswa (Klik untuk Rincian)</th>
                    <th class="text-center" style="width: 100px;">Hadir (H)</th>
                    <th class="text-center" style="width: 100px;">Sakit (S)</th>
                    <th class="text-center" style="width: 100px;">Izin (I)</th>
                    <th class="text-center" style="width: 100px;">Alpa (A)</th>
                    <th class="text-center" style="width: 160px;">Status Disiplin</th>
                    <th class="text-center" style="width: 160px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rekapSiswa)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            Belum ada data siswa atau presensi di kelas ini pada periode yang dipilih.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rekapSiswa as $idx => $s): ?>
                        <tr>
                            <td class="text-center fw-semibold text-muted"><?= $idx + 1 ?></td>
                            <td><span class="badge bg-light text-dark border font-monospace"><?= esc($s['nisn']) ?></span></td>
                            <td>
                                <a href="<?= site_url('admin/laporan/siswa/' . $s['siswa_id']) ?>" class="fw-bold text-primary text-decoration-none fs-6 d-inline-flex align-items-center gap-1">
                                    <span><?= esc($s['nama_siswa']) ?></span>
                                    <i class="bi bi-box-arrow-up-right small text-muted"></i>
                                </a>
                            </td>
                            <td class="text-center"><span class="badge badge-h px-3 py-1 fs-6 rounded-pill"><?= $s['total_h'] ?></span></td>
                            <td class="text-center"><span class="badge badge-s px-3 py-1 fs-6 rounded-pill"><?= $s['total_s'] ?></span></td>
                            <td class="text-center"><span class="badge badge-i px-3 py-1 fs-6 rounded-pill"><?= $s['total_i'] ?></span></td>
                            <td class="text-center">
                                <span class="badge badge-a px-3 py-1 fs-6 rounded-pill <?= $s['total_a'] > 3 ? 'bg-danger text-white' : '' ?>">
                                    <?= $s['total_a'] ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <?php if ($s['total_a'] > 3): ?>
                                    <span class="badge bg-danger text-white px-2 py-1">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Perlu Pembinaan
                                    </span>
                                <?php elseif ($s['total_a'] > 0): ?>
                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning px-2 py-1">
                                        Perhatian
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success px-2 py-1">
                                        Baik
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <a href="<?= site_url('admin/laporan/siswa/' . $s['siswa_id']) ?>" class="btn btn-sm btn-outline-info rounded-pill px-3 py-1 text-nowrap">
                                    <i class="bi bi-clock-history me-1"></i> Track Record &rarr;
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
function toggleKelasFilter() {
    const val = document.getElementById('selectKelasPeriode').value;
    const gb = document.getElementById('groupKelasBulan');
    const gs = document.getElementById('groupKelasSemester');
    const gt = document.getElementById('groupKelasTahun');

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
