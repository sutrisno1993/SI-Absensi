<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<style>
    @media print {
        .filter-card, .btn-action-top, footer {
            display: none !important;
        }
        .card {
            border: 1px solid #ddd !important;
            box-shadow: none !important;
        }
    }
</style>

<div class="row mb-4 align-items-center">
    <div class="col-12 col-md-6">
        <h3 class="fw-bold text-dark mb-1">
            <i class="bi bi-file-earmark-bar-graph text-primary me-2"></i>Laporan Presensi Sekolah per Kelas
        </h3>
        <p class="text-muted small mb-0">Klik pada salah satu kelas untuk melihat rincian presensi nama-nama siswanya.</p>
    </div>
    <div class="col-12 col-md-6 text-md-end mt-3 mt-md-0 d-flex flex-wrap justify-content-md-end gap-2 btn-action-top">
        <a href="<?= site_url('admin/laporan/export-penindakan-excel') ?>" class="btn btn-outline-warning" title="Unduh Catatan Kasus Pembinaan & Siswa Terlambat">
            <i class="bi bi-file-earmark-spreadsheet me-1"></i> Laporan Penindakan Siswa (.xlsx)
        </a>
        <a href="<?= site_url('admin/laporan/export-global-excel?' . http_build_query(['periode' => $periode, 'bulan' => $bulan, 'tahun' => $tahun, 'semester' => $semester])) ?>" class="btn btn-outline-success">
            <i class="bi bi-file-earmark-excel-fill me-1"></i> Download Excel Presensi (.xlsx)
        </a>
        <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Cetak
        </button>
    </div>
</div>

<!-- Filter Periode (Semua Waktu, Per Bulan, Per Semester) -->
<div class="card card-custom border-0 shadow-sm mb-4 filter-card">
    <div class="card-body p-3">
        <form action="<?= site_url('admin/laporan') ?>" method="GET" id="formFilterLaporan">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-3">
                    <label class="form-label small fw-semibold text-secondary mb-1">Mode Periode</label>
                    <select name="periode" class="form-select" id="selectPeriode" onchange="toggleFilterInputs()">
                        <option value="all" <?= $periode === 'all' ? 'selected' : '' ?>>Semua Waktu (Keseluruhan)</option>
                        <option value="bulan" <?= $periode === 'bulan' ? 'selected' : '' ?>>Per Bulan</option>
                        <option value="semester" <?= $periode === 'semester' ? 'selected' : '' ?>>Per Semester</option>
                    </select>
                </div>

                <!-- Input Bulan (Aktif jika periode = bulan) -->
                <div class="col-6 col-md-3 <?= $periode !== 'bulan' ? 'd-none' : '' ?>" id="groupBulan">
                    <label class="form-label small fw-semibold text-secondary mb-1">Pilih Bulan</label>
                    <select name="bulan" class="form-select">
                        <?php 
                        $namaBulan = [
                            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                        ];
                        foreach ($namaBulan as $num => $nb): 
                        ?>
                            <option value="<?= $num ?>" <?= (int)$bulan === $num ? 'selected' : '' ?>><?= $nb ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Input Semester (Aktif jika periode = semester) -->
                <div class="col-6 col-md-3 <?= $periode !== 'semester' ? 'd-none' : '' ?>" id="groupSemester">
                    <label class="form-label small fw-semibold text-secondary mb-1">Pilih Semester</label>
                    <select name="semester" class="form-select">
                        <option value="ganjil" <?= $semester === 'ganjil' ? 'selected' : '' ?>>Semester Ganjil (Juli - Des)</option>
                        <option value="genap" <?= $semester === 'genap' ? 'selected' : '' ?>>Semester Genap (Jan - Jun)</option>
                    </select>
                </div>

                <!-- Input Tahun -->
                <div class="col-6 col-md-2 <?= $periode === 'all' ? 'd-none' : '' ?>" id="groupTahun">
                    <label class="form-label small fw-semibold text-secondary mb-1">Tahun</label>
                    <select name="tahun" class="form-select">
                        <?php 
                        $currYear = (int)date('Y');
                        for ($y = $currYear; $y >= $currYear - 3; $y--): 
                        ?>
                            <option value="<?= $y ?>" <?= (int)$tahun === $y ? 'selected' : '' ?>><?= $y ?></option>
                        <?php endfor; ?>
                    </select>
                </div>

                <div class="col-12 col-md-auto">
                    <button type="submit" class="btn btn-primary-custom px-4">
                        <i class="bi bi-funnel-fill me-1"></i> Tampilkan Data
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<div class="card card-custom border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h5 class="fw-bold text-dark mb-0">Daftar Rekapitulasi Presensi per Rombel Kelas</h5>
                <small class="text-muted">
                    Periode Aktif: <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-2 py-1"><i class="bi bi-calendar3 me-1"></i><?= esc($labelPeriode) ?></span>
                </small>
            </div>
            <span class="badge bg-primary px-3 py-2 rounded-pill">Total Kelas: <?= count($rekapGlobal) ?></span>
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-custom table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="text-center" style="width: 50px;">No</th>
                    <th style="width: 170px;">Kelas</th>
                    <th>Wali Kelas</th>
                    <th class="text-center" style="width: 120px;">Jumlah Siswa</th>
                    <th class="text-center" style="width: 100px;">Hadir (H)</th>
                    <th class="text-center" style="width: 100px;">Sakit (S)</th>
                    <th class="text-center" style="width: 100px;">Izin (I)</th>
                    <th class="text-center" style="width: 100px;">Alpa (A)</th>
                    <th class="text-center" style="width: 160px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rekapGlobal)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">Belum ada data presensi kelas pada periode ini.</td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $grandSiswa = 0; $grandH = 0; $grandS = 0; $grandI = 0; $grandA = 0;
                    $drillQuery = http_build_query(['periode' => $periode, 'bulan' => $bulan, 'tahun' => $tahun, 'semester' => $semester]);
                    foreach ($rekapGlobal as $idx => $rg): 
                        $grandSiswa += $rg['total_siswa'];
                        $grandH += $rg['total_h'];
                        $grandS += $rg['total_s'];
                        $grandI += $rg['total_i'];
                        $grandA += $rg['total_a'];
                    ?>
                        <tr>
                            <td class="text-center fw-semibold text-muted"><?= $idx + 1 ?></td>
                            <td>
                                <a href="<?= site_url('admin/laporan/kelas/' . $rg['kelas_id'] . '?' . $drillQuery) ?>" class="fw-bold text-primary text-decoration-none fs-6">
                                    <?= esc($rg['nama_kelas']) ?> <i class="bi bi-arrow-right-short"></i>
                                </a>
                            </td>
                            <td><?= esc($rg['nama_walas'] ?? 'Belum Ditentukan') ?></td>
                            <td class="text-center"><span class="badge bg-light text-dark border px-2 py-1"><?= $rg['total_siswa'] ?> Siswa</span></td>
                            <td class="text-center"><span class="badge badge-h px-3 py-1 fs-6 rounded-pill"><?= $rg['total_h'] ?></span></td>
                            <td class="text-center"><span class="badge badge-s px-3 py-1 fs-6 rounded-pill"><?= $rg['total_s'] ?></span></td>
                            <td class="text-center"><span class="badge badge-i px-3 py-1 fs-6 rounded-pill"><?= $rg['total_i'] ?></span></td>
                            <td class="text-center">
                                <span class="badge badge-a px-3 py-1 fs-6 rounded-pill <?= $rg['total_a'] > 0 ? 'bg-danger text-white' : '' ?>">
                                    <?= $rg['total_a'] ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="<?= site_url('admin/laporan/kelas/' . $rg['kelas_id'] . '?' . $drillQuery) ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1">
                                    <i class="bi bi-people me-1"></i> Rincian Siswa &rarr;
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
            <?php if (! empty($rekapGlobal)): ?>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="3" class="text-end">TOTAL KESELURUHAN SEKOLAH:</td>
                        <td class="text-center"><?= $grandSiswa ?> Siswa</td>
                        <td class="text-center text-success"><?= $grandH ?></td>
                        <td class="text-center text-primary"><?= $grandS ?></td>
                        <td class="text-center text-warning"><?= $grandI ?></td>
                        <td class="text-center text-danger"><?= $grandA ?></td>
                        <td></td>
                    </tr>
                </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>

<script>
function toggleFilterInputs() {
    const val = document.getElementById('selectPeriode').value;
    const gBulan = document.getElementById('groupBulan');
    const gSemester = document.getElementById('groupSemester');
    const gTahun = document.getElementById('groupTahun');

    if (val === 'bulan') {
        gBulan.classList.remove('d-none');
        gSemester.classList.add('d-none');
        gTahun.classList.remove('d-none');
    } else if (val === 'semester') {
        gBulan.classList.add('d-none');
        gSemester.classList.remove('d-none');
        gTahun.classList.remove('d-none');
    } else {
        gBulan.classList.add('d-none');
        gSemester.classList.add('d-none');
        gTahun.classList.add('d-none');
    }
}
</script>
<?= $this->endSection() ?>
