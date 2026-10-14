<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="row mb-4 align-items-center">
    <div class="col-12 col-md-8">
        <h3 class="fw-bold text-dark mb-1">
            <i class="bi bi-speedometer2 text-primary me-2"></i>Dashboard Administrator
        </h3>
        <p class="text-muted small mb-0">Selamat datang di panel kontrol Sistem Informasi Presensi & Pembinaan Siswa (SI-ABSEN).</p>
    </div>
    <div class="col-12 col-md-4 text-md-end mt-3 mt-md-0">
        <a href="<?= site_url('admin/laporan') ?>" class="btn btn-primary-custom">
            <i class="bi bi-file-earmark-bar-graph me-1"></i> Laporan Global Sekolah
        </a>
    </div>
</div>

<!-- Statistik Metrik Utama -->
<div class="row g-3 mb-4">
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-custom border-0 p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-person-badge-fill fs-3"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Total Guru</div>
                    <div class="fs-4 fw-bold text-dark"><?= $totalGuru ?> Orang</div>
                    <a href="<?= site_url('admin/guru') ?>" class="small text-decoration-none">Kelola Guru &rarr;</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-custom border-0 p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-success bg-opacity-10 text-success">
                    <i class="bi bi-door-open-fill fs-3"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Total Kelas</div>
                    <div class="fs-4 fw-bold text-dark"><?= $totalKelas ?> Rombel</div>
                    <a href="<?= site_url('admin/kelas') ?>" class="small text-success text-decoration-none">Kelola Kelas &rarr;</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-custom border-0 p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-info bg-opacity-10 text-info">
                    <i class="bi bi-people-fill fs-3"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Total Siswa</div>
                    <div class="fs-4 fw-bold text-dark"><?= $totalSiswa ?> Murid</div>
                    <a href="<?= site_url('admin/siswa') ?>" class="small text-info text-decoration-none">Kelola Siswa &rarr;</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card card-custom border-0 p-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-4 p-3 bg-warning bg-opacity-10 text-warning">
                    <i class="bi bi-shield-lock-fill fs-3"></i>
                </div>
                <div>
                    <div class="text-muted small fw-semibold">Pengguna Sistem</div>
                    <div class="fs-4 fw-bold text-dark"><?= $totalUser ?> Akun</div>
                    <a href="<?= site_url('admin/users') ?>" class="small text-warning text-decoration-none">Kelola Akun &rarr;</a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ======================================================= -->
<!-- SECTION: MONITORING STATUS PENGISIAN PRESENSI HARI INI  -->
<!-- ======================================================= -->
<?php
$cntBelum    = 0;
$cntSebagian = 0;
$cntLengkap  = 0;
$totalKls    = count($statusPresensiKelasHariIni ?? []);

if (! empty($statusPresensiKelasHariIni)) {
    foreach ($statusPresensiKelasHariIni as $stk) {
        if ($stk['status_input'] === 'belum') {
            $cntBelum++;
        } elseif ($stk['status_input'] === 'sebagian') {
            $cntSebagian++;
        } else {
            $cntLengkap++;
        }
    }
}

$hariIndo = ['Sunday'=>'Minggu', 'Monday'=>'Senin', 'Tuesday'=>'Selasa', 'Wednesday'=>'Rabu', 'Thursday'=>'Kamis', 'Friday'=>'Jumat', 'Saturday'=>'Sabtu'];
$namaBulanIndo = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
$tglObj = strtotime($tanggalHariIni ?? date('Y-m-d'));
$isMinggu = ((int)date('w', $tglObj) === 0);
$labelHariIni = ($hariIndo[date('l', $tglObj)] ?? date('l', $tglObj)) . ', ' . date('j', $tglObj) . ' ' . ($namaBulanIndo[(int)date('n', $tglObj)] ?? '') . ' ' . date('Y', $tglObj);
?>

<div class="card card-custom border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 py-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 p-2 bg-primary bg-opacity-10 text-primary">
                    <i class="bi bi-calendar2-check-fill fs-4"></i>
                </div>
                <div>
                    <h5 class="fw-bold text-dark mb-0">Monitoring Pengisian Presensi Hari Ini</h5>
                    <small class="text-muted"><i class="bi bi-calendar3 me-1"></i><?= $labelHariIni ?></small>
                </div>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-outline-danger filter-btn-status <?= (!$isMinggu && $cntBelum > 0) ? 'active bg-danger text-white' : '' ?>" onclick="filterStatusKelas('belum', this)">
                    <i class="bi bi-exclamation-circle-fill me-1"></i> Belum Input (<?= $cntBelum ?>)
                </button>
                <button type="button" class="btn btn-sm btn-outline-warning filter-btn-status <?= (!$isMinggu && $cntBelum === 0 && $cntSebagian > 0) ? 'active bg-warning text-dark' : '' ?>" onclick="filterStatusKelas('sebagian', this)">
                    <i class="bi bi-hourglass-split me-1"></i> Sebagian (<?= $cntSebagian ?>)
                </button>
                <button type="button" class="btn btn-sm btn-outline-success filter-btn-status <?= (!$isMinggu && $cntBelum === 0 && $cntSebagian === 0) ? 'active bg-success text-white' : '' ?>" onclick="filterStatusKelas('lengkap', this)">
                    <i class="bi bi-check-circle-fill me-1"></i> Sudah Lengkap (<?= $cntLengkap ?>)
                </button>
                <button type="button" class="btn btn-sm btn-outline-secondary filter-btn-status <?= $isMinggu ? 'active bg-secondary text-white' : '' ?>" onclick="filterStatusKelas('all', this)">
                    Semua Kelas (<?= $totalKls ?>)
                </button>
            </div>
        </div>
    </div>

    <!-- Alert status hari Minggu atau hari aktif belajar -->
    <?php if ($isMinggu): ?>
        <div class="px-3 pt-2">
            <div class="alert alert-success bg-success bg-opacity-10 border-success border-opacity-25 d-flex align-items-center p-3 rounded-3 mb-0 gap-3">
                <i class="bi bi-cup-hot-fill fs-3 text-success"></i>
                <div>
                    <strong class="text-success">Hari Ini Hari Minggu (Libur Akhir Pekan):</strong>
                    <div class="small text-muted">Seluruh kelas libur KBM reguler. Sistem tidak menuntut pengisian presensi untuk hari ini.</div>
                </div>
            </div>
        </div>
    <?php elseif ($cntBelum > 0): ?>
        <div class="px-3 pt-2">
            <div class="alert alert-danger bg-danger bg-opacity-10 border-danger border-opacity-25 d-flex flex-wrap align-items-center justify-content-between p-3 rounded-3 mb-0 gap-2">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-bell-fill fs-4 text-danger"></i>
                    <div>
                        <strong class="text-danger">Peringatan:</strong>
                        <span class="text-dark small ms-1">Ada <strong><?= $cntBelum ?> rombel kelas</strong> yang hari ini belum menginput presensi siswa sama sekali!</span>
                    </div>
                </div>
                <small class="text-muted fst-italic"><i class="bi bi-info-circle me-1"></i>Klik tombol WhatsApp di baris kelas untuk mengingatkan Wali Kelas</small>
            </div>
        </div>
    <?php endif; ?>

    <div class="table-responsive">
        <table class="table table-custom table-hover align-middle mb-0" id="tableStatusPresensiKelas">
            <thead>
                <tr>
                    <th class="text-center" style="width: 50px;">No</th>
                    <th style="width: 140px;">Kelas</th>
                    <th>Wali Kelas & Kontak</th>
                    <th class="text-center" style="width: 160px;">Progress Diabsen</th>
                    <th class="text-center" style="width: 160px;">Rincian (H / S / I / A)</th>
                    <th class="text-center" style="width: 150px;">Status Input</th>
                    <th class="text-center" style="width: 130px;">Jam Update</th>
                    <th class="text-center" style="width: 220px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($statusPresensiKelasHariIni)): ?>
                    <tr>
                        <td colspan="8" class="text-center py-5 text-muted">Belum ada data rombel kelas terdaftar.</td>
                    </tr>
                <?php else: ?>
                    <?php 
                    $no = 1;
                    foreach ($statusPresensiKelasHariIni as $st): 
                        $pct = $st['total_siswa_aktif'] > 0 ? round(($st['total_diabsen'] / $st['total_siswa_aktif']) * 100) : 0;
                        
                        // Siapkan pesan WA pengingat
                        $namaWalasClean = $st['nama_walas'] ?: 'Bapak/Ibu Wali Kelas';
                        $pesanWA = "Halo {$namaWalasClean}, kami menginfokan dari SI-ABSEN bahwa presensi siswa Kelas {$st['nama_kelas']} untuk hari ini ({$labelHariIni}) belum tercatat di sistem. Mohon bantuannya untuk segera melakukan input presensi atau berkoordinasi dengan PJ Kelas. Terima kasih.";
                        
                        // Format nomor HP ke 62...
                        $noHpClean = preg_replace('/[^0-9]/', '', (string)$st['no_hp_walas']);
                        if (str_starts_with($noHpClean, '0')) {
                            $noHpClean = '62' . substr($noHpClean, 1);
                        }
                    ?>
                        <tr class="row-status-kelas" data-status="<?= $st['status_input'] ?>">
                            <td class="text-center fw-semibold text-muted cell-no"><?= $no++ ?></td>
                            <td>
                                <div class="fw-bold text-dark fs-6"><?= esc($st['nama_kelas']) ?></div>
                                <span class="badge bg-light text-muted border"><?= esc($st['shift'] ?? 'Pagi') ?></span>
                            </td>
                            <td>
                                <div class="fw-semibold text-dark"><?= esc($st['nama_walas'] ?? 'Belum Ditentukan') ?></div>
                                <?php if (! empty($st['no_hp_walas'])): ?>
                                    <small class="text-muted"><i class="bi bi-whatsapp text-success me-1"></i><?= esc($st['no_hp_walas']) ?></small>
                                <?php else: ?>
                                    <small class="text-muted fst-italic">No. HP belum diisi</small>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="fw-bold text-dark small mb-1"><?= $st['total_diabsen'] ?> / <?= $st['total_siswa_aktif'] ?> Siswa</div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-<?= $st['status_badge'] ?>" style="width: <?= $pct ?>%"></div>
                                </div>
                            </td>
                            <td class="text-center">
                                <?php if ($st['total_diabsen'] > 0): ?>
                                    <span class="badge badge-h px-2 py-1" title="Hadir"><?= $st['total_h'] ?> H</span>
                                    <span class="badge badge-s px-2 py-1" title="Sakit"><?= $st['total_s'] ?> S</span>
                                    <span class="badge badge-i px-2 py-1" title="Izin"><?= $st['total_i'] ?> I</span>
                                    <span class="badge badge-a px-2 py-1 <?= $st['total_a'] > 0 ? 'bg-danger text-white' : '' ?>" title="Alpa"><?= $st['total_a'] ?> A</span>
                                <?php else: ?>
                                    <span class="text-muted small fst-italic">Belum ada data</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <?php if ($st['status_input'] === 'belum'): ?>
                                    <span class="badge bg-danger px-3 py-2 rounded-pill shadow-sm">
                                        <i class="bi bi-x-circle-fill me-1"></i> Belum Input
                                    </span>
                                <?php elseif ($st['status_input'] === 'sebagian'): ?>
                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning px-3 py-2 rounded-pill">
                                        <i class="bi bi-hourglass-split me-1"></i> Sebagian
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-success bg-opacity-10 text-success border border-success px-3 py-2 rounded-pill">
                                        <i class="bi bi-check-circle-fill me-1"></i> Sudah Lengkap
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center small text-muted">
                                <?php if (! empty($st['waktu_update'])): ?>
                                    <i class="bi bi-clock me-1"></i><?= date('H:i', strtotime($st['waktu_update'])) ?> WIB
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-center">
                                <div class="d-flex align-items-center justify-content-center gap-1">
                                    <?php if ($st['status_input'] === 'belum' && ! empty($noHpClean)): ?>
                                        <a href="https://api.whatsapp.com/send?phone=<?= $noHpClean ?>&text=<?= rawurlencode($pesanWA) ?>" target="_blank" class="btn btn-sm btn-success rounded-pill px-2 py-1" title="Kirim Pengingat WhatsApp ke Walas">
                                            <i class="bi bi-whatsapp me-1"></i> Ingatkan WA
                                        </a>
                                    <?php endif; ?>
                                    <a href="<?= site_url('admin/laporan/kelas/' . $st['kelas_id'] . '?periode=hari&tanggal=' . ($tanggalHariIni ?? date('Y-m-d'))) ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1">
                                        <i class="bi bi-box-arrow-up-right me-1"></i> Cek Siswa
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- ======================================================= -->
<!-- SECTION: AUDIT PRESENSI KELEWAT (HARI AKTIF BELAJAR)    -->
<!-- ======================================================= -->
<?php if (! empty($allKelasKelewat)): ?>
    <div class="card border-warning border-2 shadow-sm rounded-4 mb-4 overflow-hidden">
        <div class="card-header bg-warning bg-opacity-25 border-warning py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-exclamation-triangle-fill fs-3 text-warning"></i>
                <div>
                    <h5 class="mb-0 fw-bold text-dark">Peringatan: Ada Presensi Kelas Belum Diinput di Hari Aktif Belajar!</h5>
                    <small class="text-muted">Ditemukan <strong><?= count($allKelasKelewat) ?> kelas</strong> yang memiliki tanggal belajar (Senin-Sabtu) belum diisi presensinya sejak 21 Juli 2026.</small>
                </div>
            </div>
            <div>
                <a href="<?= site_url('admin/kalender-pendidikan') ?>" class="btn btn-sm btn-outline-dark fw-semibold rounded-pill">
                    <i class="bi bi-calendar-event me-1"></i> Atur Kalender & Hari Libur
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 50px;">No</th>
                            <th style="width: 180px;">Kelas</th>
                            <th style="width: 200px;">Wali Kelas</th>
                            <th>Tanggal-Tanggal yang Belum Diinput</th>
                            <th class="text-end" style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($allKelasKelewat as $idx => $ak): ?>
                            <?php
                                $noHpClean = !empty($ak['no_hp_walas']) ? preg_replace('/[^0-9]/', '', $ak['no_hp_walas']) : null;
                                if (!empty($noHpClean) && str_starts_with($noHpClean, '0')) {
                                    $noHpClean = '62' . substr($noHpClean, 1);
                                }
                                $tglListStr = implode(', ', array_map(function($d) { return $d['hari'] . ' (' . $d['tanggal'] . ')'; }, $ak['daftar_kelewat']));
                                $pesanWA = "Halo Bapak/Ibu " . ($ak['nama_walas'] ?? 'Wali Kelas') . ", mengingatkan bahwa presensi kelas " . $ak['nama_kelas'] . " belum diinput pada: " . $tglListStr . ". Mohon segera dilengkapi melalui aplikasi SI-ABSEN.";
                            ?>
                            <tr>
                                <td class="text-center text-muted fw-semibold"><?= $idx + 1 ?></td>
                                <td>
                                    <div class="fw-bold text-dark fs-6"><?= esc($ak['nama_kelas']) ?></div>
                                    <span class="badge bg-light text-muted border"><?= esc($ak['shift']) ?></span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark"><?= esc($ak['nama_walas'] ?? 'Belum Ditentukan') ?></div>
                                    <?php if (!empty($ak['no_hp_walas'])): ?>
                                        <small class="text-muted"><i class="bi bi-whatsapp text-success me-1"></i><?= esc($ak['no_hp_walas']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="d-flex flex-wrap gap-1">
                                        <?php foreach ($ak['daftar_kelewat'] as $dk): ?>
                                            <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-2 py-1" title="<?= esc($dk['formatted']) ?>">
                                                <i class="bi bi-calendar-x me-1"></i><?= $dk['hari'] ?>, <?= date('d/m', strtotime($dk['tanggal'])) ?>
                                            </span>
                                        <?php endforeach; ?>
                                    </div>
                                </td>
                                <td class="text-end">
                                    <?php if (!empty($noHpClean)): ?>
                                        <a href="https://api.whatsapp.com/send?phone=<?= $noHpClean ?>&text=<?= rawurlencode($pesanWA) ?>" target="_blank" class="btn btn-sm btn-success rounded-pill px-3 py-1 shadow-sm">
                                            <i class="bi bi-whatsapp me-1"></i> Ingatkan
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small fst-italic">Tanpa Kontak</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
<?php endif; ?>

<script>
function filterStatusKelas(status, btn) {
    document.querySelectorAll('.filter-btn-status').forEach(b => {
        b.classList.remove('active', 'bg-danger', 'bg-warning', 'bg-success', 'bg-secondary', 'text-white', 'text-dark');
    });

    btn.classList.add('active');
    if (status === 'belum') {
        btn.classList.add('bg-danger', 'text-white');
    } else if (status === 'sebagian') {
        btn.classList.add('bg-warning', 'text-dark');
    } else if (status === 'lengkap') {
        btn.classList.add('bg-success', 'text-white');
    } else {
        btn.classList.add('bg-secondary', 'text-white');
    }

    const rows = document.querySelectorAll('.row-status-kelas');
    let visibleCount = 0;

    rows.forEach(row => {
        const rowStatus = row.getAttribute('data-status');
        if (status === 'all' || rowStatus === status) {
            row.style.display = '';
            visibleCount++;
            row.querySelector('.cell-no').innerText = visibleCount;
        } else {
            row.style.display = 'none';
        }
    });
}

// Inisialisasi awal jika ada kelas belum input, langsung filter belum input
document.addEventListener('DOMContentLoaded', function() {
    <?php if ($cntBelum > 0): ?>
        const belumBtn = document.querySelector('.filter-btn-status.btn-outline-danger');
        if (belumBtn) {
            filterStatusKelas('belum', belumBtn);
        }
    <?php endif; ?>
});
</script>

<!-- SECTION: DAFTAR SISWA PALING BANYAK ALPA (TANPA KETERANGAN) -->
<div class="card card-custom border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
        <div class="d-flex align-items-center gap-2">
            <div class="rounded-3 p-2 bg-danger bg-opacity-10 text-danger">
                <i class="bi bi-person-x-fill fs-4"></i>
            </div>
            <div>
                <h5 class="fw-bold text-dark mb-0">Daftar Siswa Paling Banyak Alpa (Tanpa Keterangan)</h5>
                <small class="text-muted">Peringkat siswa dengan akumulasi ketidakhadiran tanpa izin tertinggi di sekolah</small>
            </div>
        </div>
        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-3 py-2 rounded-pill">
            Monitoring Kedisiplinan Siswa
        </span>
    </div>

    <div class="table-responsive">
        <table class="table table-custom table-hover align-middle mb-0">
            <thead>
                <tr>
                    <th class="text-center" style="width: 60px;">Peringkat</th>
                    <th style="width: 140px;">NISN</th>
                    <th>Nama Lengkap Siswa</th>
                    <th style="width: 140px;">Kelas</th>
                    <th>Wali Kelas</th>
                    <th class="text-center" style="width: 140px;">Total Alpa</th>
                    <th class="text-center" style="width: 170px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($topSiswaAlpa)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <i class="bi bi-check-circle-fill fs-2 text-success d-block mb-2"></i>
                            <span class="fw-bold text-success">Tidak Ada Siswa Alpa!</span>
                            <div class="small text-muted">Seluruh siswa tercatat hadir dengan tertib atau memiliki keterangan resmi.</div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($topSiswaAlpa as $rank => $sa): ?>
                        <tr>
                            <td class="text-center">
                                <?php if ($rank === 0): ?>
                                    <span class="badge rounded-circle bg-warning text-dark p-2 fs-6 shadow-sm" title="Peringkat 1 Terbanyak">🥇</span>
                                <?php elseif ($rank === 1): ?>
                                    <span class="badge rounded-circle bg-secondary text-white p-2 fs-6 shadow-sm" title="Peringkat 2 Terbanyak">🥈</span>
                                <?php elseif ($rank === 2): ?>
                                    <span class="badge rounded-circle bg-danger bg-opacity-75 text-white p-2 fs-6 shadow-sm" title="Peringkat 3 Terbanyak">🥉</span>
                                <?php else: ?>
                                    <span class="badge bg-light text-muted border"><?= $rank + 1 ?></span>
                                <?php endif; ?>
                            </td>
                            <td><span class="badge bg-light text-dark border font-monospace"><?= esc($sa['nisn']) ?></span></td>
                            <td>
                                <a href="<?= site_url('admin/laporan/siswa/' . $sa['siswa_id']) ?>" class="fw-bold text-dark text-decoration-none d-inline-flex align-items-center gap-1">
                                    <span><?= esc($sa['nama_siswa']) ?></span>
                                    <i class="bi bi-box-arrow-up-right small text-primary"></i>
                                </a>
                            </td>
                            <td>
                                <a href="<?= site_url('admin/laporan/kelas/' . $sa['kelas_id']) ?>" class="badge bg-primary bg-opacity-10 text-primary border border-primary text-decoration-none px-2 py-1">
                                    <?= esc($sa['nama_kelas']) ?>
                                </a>
                            </td>
                            <td><span class="text-secondary small"><?= esc($sa['nama_walas'] ?? 'Belum Ditentukan') ?></span></td>
                            <td class="text-center">
                                <span class="badge bg-danger px-3 py-2 fs-6 rounded-pill shadow-sm">
                                    <i class="bi bi-x-circle me-1"></i> <?= $sa['total_alpa'] ?> Hari Alpa
                                </span>
                            </td>
                            <td class="text-center">
                                <a href="<?= site_url('admin/laporan/siswa/' . $sa['siswa_id']) ?>" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 text-nowrap">
                                    <i class="bi bi-clock-history me-1"></i> Rekam Jejak &rarr;
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Rekapitulasi Presensi Seluruh Kelas -->
    <div class="col-12 col-xl-7">
        <div class="card card-custom border-0 h-100">
            <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
                <h5 class="fw-bold text-dark mb-0">
                    <i class="bi bi-bar-chart-line text-primary me-2"></i>Rekap Presensi per Kelas
                </h5>
                <span class="badge bg-light text-muted border">Akumulatif</span>
            </div>
            <div class="table-responsive">
                <table class="table table-custom table-hover align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Kelas</th>
                            <th>Wali Kelas</th>
                            <th class="text-center">Siswa</th>
                            <th class="text-center">Hadir</th>
                            <th class="text-center">Alpa</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($rekapGlobal)): ?>
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted">Belum ada data kelas.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($rekapGlobal as $rg): ?>
                                <tr>
                                    <td class="fw-bold text-dark"><?= esc($rg['nama_kelas']) ?></td>
                                    <td><?= esc($rg['nama_walas'] ?? 'Belum Ditentukan') ?></td>
                                    <td class="text-center"><span class="badge bg-light text-dark border"><?= $rg['total_siswa'] ?></span></td>
                                    <td class="text-center"><span class="badge badge-h px-2 py-1"><?= $rg['total_h'] ?></span></td>
                                    <td class="text-center">
                                        <span class="badge badge-a px-2 py-1 <?= $rg['total_a'] > 0 ? 'bg-danger text-white' : '' ?>">
                                            <?= $rg['total_a'] ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Aktivitas Pembinaan Terakhir -->
    <div class="col-12 col-xl-5">
        <div class="card card-custom border-0 h-100">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="fw-bold text-dark mb-0">
                    <i class="bi bi-clock-history text-primary me-2"></i>Pembinaan Terkini
                </h5>
            </div>
            <div class="card-body p-0">
                <?php if (empty($riwayatPembinaan)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-check-circle fs-1 text-success d-block mb-2"></i>
                        Belum ada tindakan pembinaan dicatat.
                    </div>
                <?php else: ?>
                    <div class="list-group list-group-flush">
                        <?php foreach (array_slice($riwayatPembinaan, 0, 5) as $rp): ?>
                            <div class="list-group-item px-3 py-3 border-0 border-bottom">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold text-dark"><?= esc($rp['nama_siswa']) ?> <span class="badge bg-light text-muted border"><?= esc($rp['nama_kelas']) ?></span></span>
                                    <small class="text-muted"><?= date('d/m/Y', strtotime($rp['tanggal_tindakan'])) ?></small>
                                </div>
                                <div class="small text-primary fw-semibold mb-1"><?= esc($rp['jenis_tindakan']) ?></div>
                                <div class="small text-muted text-truncate"><?= esc($rp['catatan_pembinaan']) ?></div>
                                <small class="text-secondary d-block mt-1"><i class="bi bi-person me-1"></i> Walas: <?= esc($rp['nama_walas']) ?></small>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>
