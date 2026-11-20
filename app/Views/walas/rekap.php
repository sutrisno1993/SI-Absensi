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
        <a href="<?= site_url('walas/rekap/export-excel?' . http_build_query([
            'periode'    => $periode,
            'bulan'      => $bulan,
            'tahun'      => $tahun,
            'semester'   => $semester,
            'start_date' => $startDate ?? date('Y-m-01'),
            'end_date'   => $endDate ?? date('Y-m-d'),
        ])) ?>" class="btn btn-outline-success btn-sm">
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

<!-- Filter Periode (Per Bulan / Per Semester / Rentang Tanggal / Semua Waktu) -->
<div class="card card-custom border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form action="<?= site_url('walas/rekap') ?>" method="GET" class="d-flex flex-wrap align-items-center gap-2">
            <div class="d-flex align-items-center gap-1">
                <span class="small fw-semibold text-secondary"><i class="bi bi-funnel me-1"></i>Periode:</span>
                <select name="periode" class="form-select form-select-sm w-auto shadow-sm" id="selectWalasPeriode" onchange="toggleWalasFilter()">
                    <option value="bulan" <?= $periode === 'bulan' ? 'selected' : '' ?>>Per Bulan</option>
                    <option value="semester" <?= $periode === 'semester' ? 'selected' : '' ?>>Per Semester</option>
                    <option value="rentang" <?= $periode === 'rentang' ? 'selected' : '' ?>>Rentang Tanggal (Kustom)</option>
                    <option value="all" <?= $periode === 'all' ? 'selected' : '' ?>>Semua Waktu</option>
                </select>
            </div>

            <!-- Group Rentang Tanggal (Custom Date Range) -->
            <div id="groupWalasRentang" class="d-flex align-items-center gap-1 <?= $periode !== 'rentang' ? 'd-none' : '' ?>">
                <div class="input-group input-group-sm w-auto shadow-sm">
                    <span class="input-group-text bg-white text-muted"><i class="bi bi-calendar-event me-1"></i>Dari</span>
                    <input type="date" name="start_date" id="inputWalasStartDate" class="form-control form-control-sm" value="<?= esc($startDate ?? date('Y-m-01')) ?>">
                </div>
                <span class="text-muted small">s/d</span>
                <div class="input-group input-group-sm w-auto shadow-sm">
                    <span class="input-group-text bg-white text-muted"><i class="bi bi-calendar-check me-1"></i>Sampai</span>
                    <input type="date" name="end_date" id="inputWalasEndDate" class="form-control form-control-sm" value="<?= esc($endDate ?? date('Y-m-d')) ?>">
                </div>
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

            <div id="groupWalasTahun" class="<?= ($periode === 'all' || $periode === 'rentang') ? 'd-none' : '' ?>">
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
                    <th class="text-center" style="width: 100px;">Hadir (H)</th>
                    <th class="text-center" style="width: 100px;">Sakit (S)</th>
                    <th class="text-center" style="width: 100px;">Izin (I)</th>
                    <th class="text-center" style="width: 100px;">Alpa (A)</th>
                    <th class="text-center" style="width: 120px;">Terlambat (T)</th>
                    <th class="text-center" style="width: 220px;">Status Disiplin &amp; Pembinaan</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($rekap)): ?>
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            Belum ada riwayat presensi di periode ini.
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($rekap as $idx => $r): ?>
                        <?php 
                            $totA = (int)($r['total_a'] ?? 0);
                            $totT = (int)($r['total_t'] ?? 0);
                            $sid  = (int)($r['siswa_id'] ?? $r['id'] ?? 0);
                            $binaList = $pembinaanBySiswa[$sid] ?? [];
                        ?>
                        <tr>
                            <td class="text-center fw-semibold text-muted"><?= $idx + 1 ?></td>
                            <td><span class="badge bg-light text-dark border font-monospace"><?= esc($r['nisn']) ?></span></td>
                            <td class="fw-bold text-dark"><?= esc($r['nama_siswa']) ?></td>
                            <td class="text-center"><span class="badge badge-h px-3 py-2 fs-6 rounded-pill"><?= $r['total_h'] ?></span></td>
                            <td class="text-center"><span class="badge badge-s px-3 py-2 fs-6 rounded-pill"><?= $r['total_s'] ?></span></td>
                            <td class="text-center"><span class="badge badge-i px-3 py-2 fs-6 rounded-pill"><?= $r['total_i'] ?></span></td>
                            <td class="text-center">
                                <span class="badge badge-a px-3 py-2 fs-6 rounded-pill <?= $totA > 3 ? 'bg-danger text-white' : '' ?>">
                                    <?= $totA ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge <?= $totT > 3 ? 'bg-danger text-white' : ($totT > 1 ? 'bg-warning text-dark' : 'bg-light text-dark border') ?> px-3 py-2 fs-6 rounded-pill">
                                    <?= $totT ?>
                                </span>
                            </td>
                            <td class="text-center">
                                <?php if (! empty($binaList)): ?>
                                    <button type="button" class="btn btn-sm btn-success fw-bold rounded-pill px-3 py-1 shadow-sm" onclick="lihatDetailPembinaan(<?= $sid ?>, '<?= addslashes(esc($r['nama_siswa'])) ?>', '<?= addslashes(esc($r['nisn'])) ?>')">
                                        <i class="bi bi-check-circle-fill me-1"></i> Pernah Dibina (<?= count($binaList) ?>x) &middot; Detail
                                    </button>
                                <?php elseif ($totA > 3 || $totT > 3): ?>
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger px-2 py-1">
                                        <i class="bi bi-exclamation-triangle-fill me-1"></i> Wajib Dibina (Belum Dibina)
                                    </span>
                                <?php elseif ($totA > 0 || $totT > 1): ?>
                                    <span class="badge bg-warning bg-opacity-10 text-warning border border-warning px-2 py-1">
                                        <i class="bi bi-info-circle me-1"></i> Perhatian
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

<!-- Modal Detail Riwayat Pembinaan Siswa -->
<div class="modal fade" id="modalDetailPembinaanSiswa" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-2">
                <div class="d-flex align-items-center gap-3">
                    <div class="bg-success text-white rounded-3 p-2">
                        <i class="bi bi-journal-check fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0" id="detailBinaNamaSiswa">Detail Riwayat Pembinaan</h5>
                        <div class="small text-muted">
                            NISN: <span class="font-monospace fw-semibold" id="detailBinaNisn">-</span> &bull;
                            Total Dibina: <span class="badge bg-success rounded-pill" id="detailBinaTotal">0 Kali</span>
                        </div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body py-3" id="detailBinaListContainer" style="max-height: 450px; overflow-y: auto;"></div>
            <div class="modal-footer border-0 pt-2">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script>
const PEMBINAAN_BY_SISWA = <?= json_encode($pembinaanBySiswa ?? [], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

function lihatDetailPembinaan(siswaId, namaSiswa, nisn) {
    const list = PEMBINAAN_BY_SISWA[siswaId] || [];
    document.getElementById('detailBinaNamaSiswa').textContent = 'Riwayat Pembinaan: ' + namaSiswa;
    document.getElementById('detailBinaNisn').textContent = nisn || '-';
    document.getElementById('detailBinaTotal').textContent = list.length + ' Kali Dibina';

    const container = document.getElementById('detailBinaListContainer');
    if (list.length === 0) {
        container.innerHTML = '<div class="text-center py-4 text-muted">Belum ada catatan pembinaan untuk siswa ini.</div>';
    } else {
        let html = '<div class="d-flex flex-column gap-3">';
        list.forEach((item, idx) => {
            const berkasBtn = item.file_url
                ? `<a href="${escapeHtml(item.file_url)}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1">
                       <i class="bi bi-paperclip me-1"></i> Lihat Bukti Berkas
                   </a>`
                : `<span class="badge bg-light text-muted border">Tanpa Lampiran Berkas</span>`;

            html += `
                <div class="p-3 rounded-3 border bg-light">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-2 pb-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-success rounded-pill">#${idx + 1}</span>
                            <span class="fw-bold text-dark"><i class="bi bi-calendar-event me-1 text-primary"></i>${escapeHtml(item.tanggal_formatted)}</span>
                            <span class="badge bg-primary bg-opacity-15 text-primary border border-primary px-2 py-1">${escapeHtml(item.jenis_tindakan)}</span>
                        </div>
                        <div>${berkasBtn}</div>
                    </div>
                    <div class="small text-secondary mb-2" style="white-space: pre-line;">${escapeHtml(item.catatan_pembinaan)}</div>
                    <div class="text-end">
                        <small class="text-muted" style="font-size: 0.74rem;"><i class="bi bi-person-badge me-1"></i>Dicatat oleh: <strong>${escapeHtml(item.nama_walas)}</strong></small>
                    </div>
                </div>
            `;
        });
        html += '</div>';
        container.innerHTML = html;
    }

    const modal = new bootstrap.Modal(document.getElementById('modalDetailPembinaanSiswa'));
    modal.show();
}

function toggleWalasFilter() {
    const val = document.getElementById('selectWalasPeriode').value;
    const gb = document.getElementById('groupWalasBulan');
    const gs = document.getElementById('groupWalasSemester');
    const gt = document.getElementById('groupWalasTahun');
    const gr = document.getElementById('groupWalasRentang');

    if (val === 'bulan') {
        gb.classList.remove('d-none');
        gs.classList.add('d-none');
        gt.classList.remove('d-none');
        if (gr) gr.classList.add('d-none');
    } else if (val === 'semester') {
        gb.classList.add('d-none');
        gs.classList.remove('d-none');
        gt.classList.remove('d-none');
        if (gr) gr.classList.add('d-none');
    } else if (val === 'rentang') {
        gb.classList.add('d-none');
        gs.classList.add('d-none');
        gt.classList.add('d-none');
        if (gr) gr.classList.remove('d-none');
    } else {
        gb.classList.add('d-none');
        gs.classList.add('d-none');
        gt.classList.add('d-none');
        if (gr) gr.classList.add('d-none');
    }
}
</script>
<?= $this->endSection() ?>
