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
        <a href="<?= site_url('admin/laporan/export-global-excel?' . http_build_query(['periode' => $periode, 'bulan' => $bulan, 'tahun' => $tahun, 'semester' => $semester, 'tanggal' => $tanggal])) ?>" class="btn btn-outline-success">
            <i class="bi bi-file-earmark-excel-fill me-1"></i> Download Excel Presensi (.xlsx)
        </a>
        <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
            <i class="bi bi-printer me-1"></i> Cetak
        </button>
    </div>
</div>

<!-- Filter Periode (Per Hari, Per Minggu, Per Bulan, Per Semester, Per Tahun, Semua Waktu) -->
<div class="card card-custom border-0 shadow-sm mb-4 filter-card">
    <div class="card-body p-3">
        <form action="<?= site_url('admin/laporan') ?>" method="GET" id="formFilterLaporan">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-md-3">
                    <label class="form-label small fw-semibold text-secondary mb-1">Mode Rekapitulasi</label>
                    <select name="periode" class="form-select" id="selectPeriode" onchange="toggleFilterInputs()">
                        <option value="hari" <?= $periode === 'hari' ? 'selected' : '' ?>>Per Hari (Harian)</option>
                        <option value="minggu" <?= $periode === 'minggu' ? 'selected' : '' ?>>Per Minggu (Mingguan)</option>
                        <option value="bulan" <?= $periode === 'bulan' ? 'selected' : '' ?>>Per Bulan (Bulanan)</option>
                        <option value="semester" <?= $periode === 'semester' ? 'selected' : '' ?>>Per Semester</option>
                        <option value="tahun" <?= $periode === 'tahun' ? 'selected' : '' ?>>Per Tahun (Tahunan)</option>
                        <option value="all" <?= $periode === 'all' ? 'selected' : '' ?>>Semua Waktu (Keseluruhan)</option>
                    </select>
                </div>

                <!-- Input Tanggal (Aktif jika periode = hari atau minggu) -->
                <div class="col-12 col-md-3 <?= ! in_array($periode, ['hari', 'minggu']) ? 'd-none' : '' ?>" id="groupTanggal">
                    <label class="form-label small fw-semibold text-secondary mb-1" id="labelTanggal">
                        <?= $periode === 'minggu' ? 'Pilih Tanggal Acuan Minggu' : 'Pilih Tanggal Presensi' ?>
                    </label>
                    <input type="date" name="tanggal" class="form-control" value="<?= esc($tanggal) ?>" id="inputTanggal">
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

                <!-- Input Tahun (Aktif jika periode = bulan, semester, atau tahun) -->
                <div class="col-6 col-md-2 <?= ! in_array($periode, ['bulan', 'semester', 'tahun']) ? 'd-none' : '' ?>" id="groupTahun">
                    <label class="form-label small fw-semibold text-secondary mb-1">Tahun</label>
                    <select name="tahun" class="form-select">
                        <?php 
                        $currYear = (int)date('Y');
                        for ($y = $currYear; $y >= $currYear - 4; $y--): 
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

<?php
$cntBelumLaporan = 0;
if ($periode === 'hari' && ! empty($rekapGlobal)) {
    foreach ($rekapGlobal as $rgCheck) {
        if (($rgCheck['total_h'] + $rgCheck['total_s'] + $rgCheck['total_i'] + $rgCheck['total_a']) === 0) {
            $cntBelumLaporan++;
        }
    }
}
?>

<div class="card card-custom border-0 shadow-sm">
    <div class="card-header bg-white border-0 py-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h5 class="fw-bold text-dark mb-0">Daftar Rekapitulasi Presensi per Rombel Kelas</h5>
                <small class="text-muted">
                    Periode Aktif: <span class="badge bg-primary bg-opacity-10 text-primary border border-primary px-2 py-1"><i class="bi bi-calendar3 me-1"></i><?= esc($labelPeriode) ?></span>
                </small>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <?php if ($periode === 'hari' && $cntBelumLaporan > 0): ?>
                    <button type="button" class="btn btn-sm btn-outline-danger active" id="btnFilterBelumLaporan" onclick="toggleOnlyBelumLaporan(this)">
                        <i class="bi bi-exclamation-circle-fill me-1"></i> Belum Input (<?= $cntBelumLaporan ?> Kelas)
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary" id="btnFilterSemuaLaporan" onclick="toggleAllLaporan(this)">
                        Semua Kelas (<?= count($rekapGlobal) ?>)
                    </button>
                <?php else: ?>
                    <span class="badge bg-primary px-3 py-2 rounded-pill">Total Kelas: <?= count($rekapGlobal) ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <?php if ($periode === 'hari' && $cntBelumLaporan > 0): ?>
        <div class="px-3 pt-2">
            <div class="alert alert-danger bg-danger bg-opacity-10 border-danger border-opacity-25 d-flex flex-wrap align-items-center justify-content-between p-3 rounded-3 mb-0 gap-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-bell-fill fs-4 text-danger"></i>
                    <div>
                        <strong class="text-danger">Pemberitahuan:</strong>
                        <span class="text-dark small ms-1">Terdapat <strong><?= $cntBelumLaporan ?> rombel kelas</strong> yang belum melakukan input absensi untuk tanggal <?= esc($labelPeriode) ?>.</span>
                    </div>
                </div>
                <small class="text-muted fst-italic">Gunakan tombol WhatsApp di kolom aksi untuk mengingatkan Wali Kelas</small>
            </div>
        </div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-custom table-hover align-middle mb-0" id="tableLaporanGlobal">
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
                    <?php if ($periode === 'hari'): ?>
                        <th class="text-center" style="width: 140px;">Status Input</th>
                    <?php endif; ?>
                    <th class="text-center" style="width: <?= $periode === 'hari' ? '220px' : '160px' ?>;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rekapGlobal)): ?>
                    <tr>
                        <td colspan="<?= $periode === 'hari' ? 10 : 9 ?>" class="text-center py-5 text-muted">Belum ada data presensi kelas pada periode ini.</td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $grandSiswa = 0; $grandH = 0; $grandS = 0; $grandI = 0; $grandA = 0;
                    $drillQuery = http_build_query([
                        'periode'  => $periode,
                        'bulan'    => $bulan,
                        'tahun'    => $tahun,
                        'semester' => $semester,
                        'tanggal'  => $tanggal,
                    ]);
                    $rowNo = 1;
                    foreach ($rekapGlobal as $idx => $rg): 
                        $grandSiswa += $rg['total_siswa'];
                        $grandH += $rg['total_h'];
                        $grandS += $rg['total_s'];
                        $grandI += $rg['total_i'];
                        $grandA += $rg['total_a'];

                        $totDiabsen = $rg['total_h'] + $rg['total_s'] + $rg['total_i'] + $rg['total_a'];
                        $isBelumInput = ($totDiabsen === 0);

                        // Siapkan pesan WA
                        $namaWalasClean = $rg['nama_walas'] ?: 'Bapak/Ibu Wali Kelas';
                        $pesanWA = "Halo {$namaWalasClean}, kami menginfokan dari SI-ABSEN bahwa presensi siswa Kelas {$rg['nama_kelas']} untuk hari ini ({$labelPeriode}) belum tercatat di sistem. Mohon bantuannya untuk segera melakukan input presensi atau berkoordinasi dengan PJ Kelas. Terima kasih.";
                        
                        $noHpClean = preg_replace('/[^0-9]/', '', (string)($rg['no_hp_walas'] ?? ''));
                        if (str_starts_with($noHpClean, '0')) {
                            $noHpClean = '62' . substr($noHpClean, 1);
                        }
                    ?>
                        <tr class="row-laporan-kelas" data-belum-input="<?= $isBelumInput ? '1' : '0' ?>">
                            <td class="text-center fw-semibold text-muted cell-laporan-no"><?= $rowNo++ ?></td>
                            <td>
                                <a href="<?= site_url('admin/laporan/kelas/' . $rg['kelas_id'] . '?' . $drillQuery) ?>" class="fw-bold text-primary text-decoration-none fs-6">
                                    <?= esc($rg['nama_kelas']) ?> <i class="bi bi-arrow-right-short"></i>
                                </a>
                                <span class="badge bg-light text-muted border ms-1"><?= esc($rg['shift'] ?? 'Pagi') ?></span>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark"><?= esc($rg['nama_walas'] ?? 'Belum Ditentukan') ?></div>
                                <?php if (! empty($rg['no_hp_walas'])): ?>
                                    <small class="text-muted"><i class="bi bi-whatsapp text-success me-1"></i><?= esc($rg['no_hp_walas']) ?></small>
                                <?php endif; ?>
                            </td>
                            <td class="text-center"><span class="badge bg-light text-dark border px-2 py-1"><?= $rg['total_siswa'] ?> Siswa</span></td>
                            <td class="text-center"><span class="badge badge-h px-3 py-1 fs-6 rounded-pill"><?= $rg['total_h'] ?></span></td>
                            <td class="text-center"><span class="badge badge-s px-3 py-1 fs-6 rounded-pill"><?= $rg['total_s'] ?></span></td>
                            <td class="text-center"><span class="badge badge-i px-3 py-1 fs-6 rounded-pill"><?= $rg['total_i'] ?></span></td>
                            <td class="text-center">
                                <span class="badge badge-a px-3 py-1 fs-6 rounded-pill <?= $rg['total_a'] > 0 ? 'bg-danger text-white' : '' ?>">
                                    <?= $rg['total_a'] ?>
                                </span>
                            </td>
                            <?php if ($periode === 'hari'): ?>
                                <td class="text-center">
                                    <?php if ($isBelumInput): ?>
                                        <span class="badge bg-danger px-2 py-1 rounded-pill shadow-sm">
                                            <i class="bi bi-x-circle-fill me-1"></i> Belum Input
                                        </span>
                                    <?php elseif ($totDiabsen < $rg['total_siswa']): ?>
                                        <span class="badge bg-warning bg-opacity-10 text-warning border border-warning px-2 py-1 rounded-pill">
                                            <i class="bi bi-hourglass-split me-1"></i> Sebagian
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success px-2 py-1 rounded-pill">
                                            <i class="bi bi-check-circle-fill me-1"></i> Lengkap
                                        </span>
                                    <?php endif; ?>
                                </td>
                            <?php endif; ?>
                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-center gap-1">
                                    <?php if ($periode === 'hari' && $isBelumInput && ! empty($noHpClean)): ?>
                                        <a href="https://api.whatsapp.com/send?phone=<?= $noHpClean ?>&text=<?= rawurlencode($pesanWA) ?>" target="_blank" class="btn btn-sm btn-success rounded-pill px-2 py-1" title="Ingatkan Walas via WhatsApp">
                                            <i class="bi bi-whatsapp me-1"></i> Ingatkan WA
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?= site_url('admin/laporan/kelas/' . $rg['kelas_id'] . '?' . $drillQuery) ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1">
                                        <i class="bi bi-people me-1"></i> Rincian &rarr;
                                    </a>
                                </div>
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
                        <?php if ($periode === 'hari'): ?>
                            <td></td>
                        <?php endif; ?>
                        <td></td>
                    </tr>
                </tfoot>
            <?php endif; ?>
        </table>
    </div>
</div>

<script>
function toggleOnlyBelumLaporan(btn) {
    const btnSemua = document.getElementById('btnFilterSemuaLaporan');
    if (btnSemua) btnSemua.classList.remove('active', 'bg-secondary', 'text-white');
    btn.classList.add('active', 'bg-danger', 'text-white');

    const rows = document.querySelectorAll('.row-laporan-kelas');
    let visibleCount = 0;
    rows.forEach(r => {
        if (r.getAttribute('data-belum-input') === '1') {
            r.style.display = '';
            visibleCount++;
            r.querySelector('.cell-laporan-no').innerText = visibleCount;
        } else {
            r.style.display = 'none';
        }
    });
}

function toggleAllLaporan(btn) {
    const btnBelum = document.getElementById('btnFilterBelumLaporan');
    if (btnBelum) btnBelum.classList.remove('active', 'bg-danger', 'text-white');
    btn.classList.add('active', 'bg-secondary', 'text-white');

    const rows = document.querySelectorAll('.row-laporan-kelas');
    let visibleCount = 0;
    rows.forEach(r => {
        r.style.display = '';
        visibleCount++;
        r.querySelector('.cell-laporan-no').innerText = visibleCount;
    });
}
</script>

<script>
function toggleFilterInputs() {
    const val = document.getElementById('selectPeriode').value;
    const gTanggal = document.getElementById('groupTanggal');
    const labelTanggal = document.getElementById('labelTanggal');
    const gBulan = document.getElementById('groupBulan');
    const gSemester = document.getElementById('groupSemester');
    const gTahun = document.getElementById('groupTahun');

    // Sembunyikan semua dulu
    gTanggal.classList.add('d-none');
    gBulan.classList.add('d-none');
    gSemester.classList.add('d-none');
    gTahun.classList.add('d-none');

    if (val === 'hari') {
        gTanggal.classList.remove('d-none');
        labelTanggal.innerText = 'Pilih Tanggal Presensi';
    } else if (val === 'minggu') {
        gTanggal.classList.remove('d-none');
        labelTanggal.innerText = 'Pilih Tanggal Acuan Minggu';
    } else if (val === 'bulan') {
        gBulan.classList.remove('d-none');
        gTahun.classList.remove('d-none');
    } else if (val === 'semester') {
        gSemester.classList.remove('d-none');
        gTahun.classList.remove('d-none');
    } else if (val === 'tahun') {
        gTahun.classList.remove('d-none');
    }
}
</script>
<?= $this->endSection() ?>
