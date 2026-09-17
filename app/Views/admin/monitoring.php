<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<style>
    @media print {
        .app-navbar, .filter-card, .btn-print, footer, .no-print {
            display: none !important;
        }
        .card {
            border: 1px solid #ddd !important;
            box-shadow: none !important;
        }
        body {
            background-color: white !important;
        }
    }
</style>

<!-- Header & Print Action -->
<div class="row mb-4 align-items-center">
    <div class="col-12 col-md-8">
        <h3 class="fw-bold text-dark mb-1">
            <i class="bi bi-graph-up-arrow text-primary me-2"></i>Monitoring Persentase Kehadiran
        </h3>
        <p class="text-muted small mb-0">Analisis metrik persentase kehadiran guru dan siswa berdasarkan periode waktu.</p>
    </div>
    <div class="col-12 col-md-4 text-md-end mt-3 mt-md-0 no-print d-flex flex-wrap justify-content-md-end gap-2">
        <a href="<?= site_url('admin/monitoring/export-guru-excel?' . http_build_query(['periode' => $periode, 'bulan' => $bulan, 'tahun' => $tahun, 'semester' => $semester])) ?>" class="btn btn-outline-success rounded-3 px-3 shadow-sm" title="Unduh Rekap Presensi Guru ke Excel">
            <i class="bi bi-file-earmark-excel-fill me-1"></i> Excel Presensi Guru
        </a>
        <button type="button" class="btn btn-outline-secondary btn-print rounded-3 px-3 shadow-sm" onclick="window.print()">
            <i class="bi bi-printer-fill me-1"></i> Cetak Laporan
        </button>
    </div>
</div>

<!-- Filter Periode (Keseluruhan, Per Bulan, Per Semester) -->
<div class="card card-custom border-0 shadow-sm mb-4 filter-card no-print">
    <div class="card-body p-3">
        <form action="<?= site_url('admin/monitoring') ?>" method="GET" id="formFilterMonitoring">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-3">
                    <label class="form-label small fw-semibold text-secondary mb-1">Pilih Mode Periode</label>
                    <select name="periode" class="form-select" id="selectPeriode" onchange="toggleFilterInputs()">
                        <option value="all" <?= $periode === 'all' ? 'selected' : '' ?>>Semua Waktu (Keseluruhan)</option>
                        <option value="bulan" <?= $periode === 'bulan' ? 'selected' : '' ?>>Per Bulan</option>
                        <option value="semester" <?= $periode === 'semester' ? 'selected' : '' ?>>Per Semester</option>
                    </select>
                </div>

                <!-- Input Bulan (Aktif jika periode = bulan) -->
                <div class="col-6 col-md-3 <?= $periode !== 'bulan' ? 'd-none' : '' ?>" id="groupBulan">
                    <label class="form-label small fw-semibold text-secondary mb-1">Bulan</label>
                    <select name="bulan" class="form-select">
                        <?php 
                        $namaBulan = [
                            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
                            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
                            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                        ];
                        foreach ($namaBulan as $num => $nb): 
                        ?>
                            <option value="<?= $num ?>" <?= $bulan === $num ? 'selected' : '' ?>><?= $nb ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Input Semester (Aktif jika periode = semester) -->
                <div class="col-6 col-md-3 <?= $periode !== 'semester' ? 'd-none' : '' ?>" id="groupSemester">
                    <label class="form-label small fw-semibold text-secondary mb-1">Semester</label>
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
                            <option value="<?= $y ?>" <?= $tahun === $y ? 'selected' : '' ?>><?= $y ?></option>
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

<!-- Header Informasi Periode Aktif -->
<div class="d-flex align-items-center justify-content-between mb-3">
    <div>
        <span class="text-muted small">Periode Analisis:</span>
        <h5 class="fw-bold text-dark mb-0"><?= esc($labelPeriode) ?></h5>
    </div>
    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-3 py-2 rounded-pill">
        Data Realtime
    </span>
</div>

<!-- Ringkasan Persentase Guru & Siswa -->
<div class="row g-4 mb-4">
    <!-- Kartu Persentase Guru -->
    <div class="col-12 col-lg-6">
        <div class="card card-custom border-0 shadow-sm h-100 p-4">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-4 p-3 bg-primary bg-opacity-10 text-primary fs-3">
                        <i class="bi bi-person-badge-fill"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold text-dark mb-0">Kehadiran Guru</h5>
                        <small class="text-muted">Berdasarkan jadwal wajib tatap muka</small>
                    </div>
                </div>
                <div class="text-end">
                    <span class="fs-2 fw-bold <?= $statGuru['persen_hadir'] >= 90 ? 'text-success' : ($statGuru['persen_hadir'] >= 75 ? 'text-warning' : 'text-danger') ?>">
                        <?= $statGuru['persen_hadir'] ?>%
                    </span>
                    <div class="small text-muted">Tingkat Hadir</div>
                </div>
            </div>

            <!-- Progress Bar Guru -->
            <div class="progress mb-3" style="height: 10px; border-radius: 6px;">
                <div class="progress-bar bg-success" role="progressbar" style="width: <?= $statGuru['persen_hadir'] ?>%" title="Hadir: <?= $statGuru['persen_hadir'] ?>%"></div>
                <div class="progress-bar bg-info" role="progressbar" style="width: <?= $statGuru['persen_sakit'] ?>%" title="Sakit: <?= $statGuru['persen_sakit'] ?>%"></div>
                <div class="progress-bar bg-warning" role="progressbar" style="width: <?= $statGuru['persen_izin'] ?>%" title="Izin: <?= $statGuru['persen_izin'] ?>%"></div>
                <div class="progress-bar bg-danger" role="progressbar" style="width: <?= $statGuru['persen_alfa'] ?>%" title="Alfa: <?= $statGuru['persen_alfa'] ?>%"></div>
            </div>

            <!-- Rincian Metrik Guru -->
            <div class="row g-2 text-center small mt-auto">
                <div class="col-4 col-sm-2">
                    <div class="p-2 bg-light rounded-3 border">
                        <div class="text-muted" style="font-size: 0.72rem;">Jadwal</div>
                        <div class="fw-bold fs-6 text-dark"><?= $statGuru['total_sesi'] ?></div>
                    </div>
                </div>
                <div class="col-4 col-sm-2">
                    <div class="p-2 bg-success bg-opacity-10 text-success rounded-3 border border-success border-opacity-25">
                        <div style="font-size: 0.72rem;">Hadir</div>
                        <div class="fw-bold fs-6"><?= $statGuru['total_hadir'] ?></div>
                    </div>
                </div>
                <div class="col-4 col-sm-2">
                    <div class="p-2 bg-info bg-opacity-10 text-info rounded-3 border border-info border-opacity-25">
                        <div style="font-size: 0.72rem;">Sakit</div>
                        <div class="fw-bold fs-6"><?= $statGuru['total_sakit'] ?></div>
                    </div>
                </div>
                <div class="col-4 col-sm-2">
                    <div class="p-2 bg-warning bg-opacity-10 text-warning-emphasis rounded-3 border border-warning border-opacity-25">
                        <div style="font-size: 0.72rem;">Izin</div>
                        <div class="fw-bold fs-6"><?= $statGuru['total_izin'] ?></div>
                    </div>
                </div>
                <div class="col-4 col-sm-2">
                    <div class="p-2 bg-danger bg-opacity-10 text-danger rounded-3 border border-danger border-opacity-25">
                        <div style="font-size: 0.72rem;">Alfa</div>
                        <div class="fw-bold fs-6"><?= $statGuru['total_alfa'] ?></div>
                    </div>
                </div>
                <div class="col-4 col-sm-2">
                    <div class="p-2 bg-secondary bg-opacity-10 text-secondary rounded-3 border">
                        <div style="font-size: 0.72rem;">Terlambat</div>
                        <div class="fw-bold fs-6 text-dark"><?= $statGuru['total_terlambat'] ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Kartu Persentase Siswa -->
    <div class="col-12 col-lg-6">
        <div class="card card-custom border-0 shadow-sm h-100 p-4">
            <div class="d-flex justify-content-between align-items-start mb-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-4 p-3 bg-success bg-opacity-10 text-success fs-3">
                        <i class="bi bi-people-fill"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold text-dark mb-0">Kehadiran Siswa</h5>
                        <small class="text-muted">Akumulasi presensi seluruh kelas</small>
                    </div>
                </div>
                <div class="text-end">
                    <span class="fs-2 fw-bold <?= $statSiswa['persen_hadir'] >= 90 ? 'text-success' : ($statSiswa['persen_hadir'] >= 75 ? 'text-warning' : 'text-danger') ?>">
                        <?= $statSiswa['persen_hadir'] ?>%
                    </span>
                    <div class="small text-muted">Tingkat Hadir</div>
                </div>
            </div>

            <!-- Progress Bar Siswa -->
            <div class="progress mb-3" style="height: 10px; border-radius: 6px;">
                <div class="progress-bar bg-success" role="progressbar" style="width: <?= $statSiswa['persen_hadir'] ?>%" title="Hadir: <?= $statSiswa['persen_hadir'] ?>%"></div>
                <div class="progress-bar bg-info" role="progressbar" style="width: <?= $statSiswa['persen_sakit'] ?>%" title="Sakit: <?= $statSiswa['persen_sakit'] ?>%"></div>
                <div class="progress-bar bg-warning" role="progressbar" style="width: <?= $statSiswa['persen_izin'] ?>%" title="Izin: <?= $statSiswa['persen_izin'] ?>%"></div>
                <div class="progress-bar bg-danger" role="progressbar" style="width: <?= $statSiswa['persen_alfa'] ?>%" title="Alfa: <?= $statSiswa['persen_alfa'] ?>%"></div>
            </div>

            <!-- Rincian Metrik Siswa -->
            <div class="row g-2 text-center small mt-auto">
                <div class="col-4 col-sm-2">
                    <div class="p-2 bg-light rounded-3 border">
                        <div class="text-muted" style="font-size: 0.72rem;">Presensi</div>
                        <div class="fw-bold fs-6 text-dark"><?= $statSiswa['total_sesi'] ?></div>
                    </div>
                </div>
                <div class="col-4 col-sm-2">
                    <div class="p-2 bg-success bg-opacity-10 text-success rounded-3 border border-success border-opacity-25">
                        <div style="font-size: 0.72rem;">Hadir</div>
                        <div class="fw-bold fs-6"><?= $statSiswa['total_hadir'] ?></div>
                    </div>
                </div>
                <div class="col-4 col-sm-2">
                    <div class="p-2 bg-info bg-opacity-10 text-info rounded-3 border border-info border-opacity-25">
                        <div style="font-size: 0.72rem;">Sakit</div>
                        <div class="fw-bold fs-6"><?= $statSiswa['total_sakit'] ?></div>
                    </div>
                </div>
                <div class="col-4 col-sm-2">
                    <div class="p-2 bg-warning bg-opacity-10 text-warning-emphasis rounded-3 border border-warning border-opacity-25">
                        <div style="font-size: 0.72rem;">Izin</div>
                        <div class="fw-bold fs-6"><?= $statSiswa['total_izin'] ?></div>
                    </div>
                </div>
                <div class="col-4 col-sm-2">
                    <div class="p-2 bg-danger bg-opacity-10 text-danger rounded-3 border border-danger border-opacity-25">
                        <div style="font-size: 0.72rem;">Alfa</div>
                        <div class="fw-bold fs-6"><?= $statSiswa['total_alfa'] ?></div>
                    </div>
                </div>
                <div class="col-4 col-sm-2">
                    <div class="p-2 bg-secondary bg-opacity-10 text-secondary rounded-3 border">
                        <div style="font-size: 0.72rem;">Terlambat</div>
                        <div class="fw-bold fs-6 text-dark"><?= $statSiswa['total_terlambat'] ?></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Tab Rincian Data: Guru & Siswa -->
<div class="card card-custom border-0 shadow-sm">
    <div class="card-header bg-white border-bottom-0 pt-3 px-3">
        <ul class="nav nav-pills card-header-pills" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold" id="pills-guru-tab" data-bs-toggle="pill" data-bs-target="#pills-guru" type="button" role="tab">
                    <i class="bi bi-person-badge me-1"></i> Rincian Kehadiran Per Guru (<?= count($performaGuru) ?>)
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold" id="pills-kelas-tab" data-bs-toggle="pill" data-bs-target="#pills-kelas" type="button" role="tab">
                    <i class="bi bi-door-open me-1"></i> Rincian Kehadiran Per Kelas (<?= count($performaKelas) ?>)
                </button>
            </li>
        </ul>
    </div>

    <div class="card-body p-0">
        <div class="tab-content" id="pills-tabContent">
            <!-- Tab Guru -->
            <div class="tab-pane fade show active" id="pills-guru" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-custom table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 50px;">No</th>
                                <th style="width: 160px;">NIP</th>
                                <th>Nama Lengkap Guru</th>
                                <th class="text-center" style="width: 90px;">Jadwal</th>
                                <th class="text-center" style="width: 80px;">Hadir</th>
                                <th class="text-center" style="width: 80px;">Sakit</th>
                                <th class="text-center" style="width: 80px;">Izin</th>
                                <th class="text-center" style="width: 80px;">Alfa</th>
                                <th class="text-center" style="width: 90px;">Terlambat</th>
                                <th class="text-center" style="width: 140px;">% Kehadiran</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($performaGuru)): ?>
                                <tr>
                                    <td colspan="10" class="text-center py-5 text-muted">Belum ada data guru.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($performaGuru as $idx => $pg): ?>
                                    <tr>
                                        <td class="text-center fw-semibold text-muted"><?= $idx + 1 ?></td>
                                        <td><span class="badge bg-light text-dark border font-monospace"><?= esc($pg['nip'] ?? '-') ?></span></td>
                                        <td class="fw-bold text-dark"><?= esc($pg['nama_guru']) ?></td>
                                        <td class="text-center fw-semibold text-muted"><?= $pg['total_sesi'] ?></td>
                                        <td class="text-center fw-bold text-success"><?= $pg['total_hadir'] ?></td>
                                        <td class="text-center text-info"><?= $pg['total_sakit'] ?></td>
                                        <td class="text-center text-warning"><?= $pg['total_izin'] ?></td>
                                        <td class="text-center text-danger"><?= $pg['total_alfa'] ?></td>
                                        <td class="text-center text-secondary"><?= $pg['total_terlambat'] ?></td>
                                        <td class="text-center">
                                            <div class="d-flex align-items-center justify-content-center gap-2">
                                                <div class="progress flex-grow-1" style="height: 6px; width: 60px;">
                                                    <div class="progress-bar <?= $pg['persen_hadir'] >= 90 ? 'bg-success' : ($pg['persen_hadir'] >= 75 ? 'bg-warning' : 'bg-danger') ?>" 
                                                         style="width: <?= $pg['persen_hadir'] ?>%"></div>
                                                </div>
                                                <span class="fw-bold <?= $pg['persen_hadir'] >= 90 ? 'text-success' : ($pg['persen_hadir'] >= 75 ? 'text-warning' : 'text-danger') ?>" style="font-size: 0.85rem;">
                                                    <?= $pg['persen_hadir'] ?>%
                                                </span>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Tab Kelas -->
            <div class="tab-pane fade" id="pills-kelas" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-custom table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th class="text-center" style="width: 50px;">No</th>
                                <th style="width: 200px;">Nama Kelas</th>
                                <th style="width: 120px;">Shift</th>
                                <th class="text-center" style="width: 100px;">Jumlah Siswa</th>
                                <th class="text-center" style="width: 100px;">Total Presensi</th>
                                <th class="text-center" style="width: 80px;">Hadir</th>
                                <th class="text-center" style="width: 80px;">Sakit</th>
                                <th class="text-center" style="width: 80px;">Izin</th>
                                <th class="text-center" style="width: 80px;">Alfa</th>
                                <th class="text-center" style="width: 140px;">% Kehadiran Kelas</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($performaKelas)): ?>
                                <tr>
                                    <td colspan="10" class="text-center py-5 text-muted">Belum ada data kelas.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($performaKelas as $idx => $pk): ?>
                                    <tr>
                                        <td class="text-center fw-semibold text-muted"><?= $idx + 1 ?></td>
                                        <td class="fw-bold text-dark"><?= esc($pk['nama_kelas']) ?></td>
                                        <td>
                                            <?php if (($pk['shift'] ?? 'Pagi') === 'Siang'): ?>
                                                <span class="badge bg-info bg-opacity-10 text-primary border border-info border-opacity-25 px-2 py-1">
                                                    <i class="bi bi-cloud-sun me-1"></i> Siang
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-warning bg-opacity-15 text-warning-emphasis border border-warning border-opacity-25 px-2 py-1">
                                                    <i class="bi bi-sun-fill text-warning me-1"></i> Pagi
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-center fw-semibold text-dark"><?= $pk['total_siswa'] ?> Anak</td>
                                        <td class="text-center text-muted"><?= $pk['total_sesi'] ?></td>
                                        <td class="text-center fw-bold text-success"><?= $pk['total_hadir'] ?></td>
                                        <td class="text-center text-info"><?= $pk['total_sakit'] ?></td>
                                        <td class="text-center text-warning"><?= $pk['total_izin'] ?></td>
                                        <td class="text-center text-danger"><?= $pk['total_alfa'] ?></td>
                                        <td class="text-center">
                                            <div class="d-flex align-items-center justify-content-center gap-2">
                                                <div class="progress flex-grow-1" style="height: 6px; width: 60px;">
                                                    <div class="progress-bar <?= $pk['persen_hadir'] >= 90 ? 'bg-success' : ($pk['persen_hadir'] >= 75 ? 'bg-warning' : 'bg-danger') ?>" 
                                                         style="width: <?= $pk['persen_hadir'] ?>%"></div>
                                                </div>
                                                <span class="fw-bold <?= $pk['persen_hadir'] >= 90 ? 'text-success' : ($pk['persen_hadir'] >= 75 ? 'text-warning' : 'text-danger') ?>" style="font-size: 0.85rem;">
                                                    <?= $pk['persen_hadir'] ?>%
                                                </span>
                                            </div>
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
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    function toggleFilterInputs() {
        var mode = document.getElementById('selectPeriode').value;
        var groupBulan = document.getElementById('groupBulan');
        var groupSemester = document.getElementById('groupSemester');
        var groupTahun = document.getElementById('groupTahun');

        groupBulan.classList.add('d-none');
        groupSemester.classList.add('d-none');
        groupTahun.classList.add('d-none');

        if (mode === 'bulan') {
            groupBulan.classList.remove('d-none');
            groupTahun.classList.remove('d-none');
        } else if (mode === 'semester') {
            groupSemester.classList.remove('d-none');
            groupTahun.classList.remove('d-none');
        }
    }
</script>
<?= $this->endSection() ?>
