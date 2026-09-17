<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<div class="row mb-4 align-items-center">
    <div class="col-12 col-md-8">
        <h3 class="fw-bold text-dark mb-1">
            <i class="bi bi-person-check-fill text-primary me-2"></i>Presensi Kedatangan Guru
        </h3>
        <p class="text-muted small mb-0">Catat presensi guru yang memiliki jadwal mengajar pada shift dan hari aktif.</p>
    </div>
    <div class="col-12 col-md-6 text-md-end mt-3 mt-md-0 d-flex flex-wrap justify-content-md-end gap-2">
        <button type="button" class="btn btn-success" onclick="bukaModalWA()">
            <i class="bi bi-whatsapp me-1"></i> Share Laporan WA Grup
        </button>
        <a href="<?= site_url('piket') ?>" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Dashboard
        </a>
    </div>
</div>

<?php
$bulanIndo = [
    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
    7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
];
$ts = strtotime($tanggal);
$tglMurniIndo = date('j', $ts) . ' ' . ($bulanIndo[(int)date('n', $ts)] ?? date('F', $ts)) . ' ' . date('Y', $ts);
$tglIndoFormatted = $hari . ', ' . $tglMurniIndo;
$headerLaporanWA = $shift . ', ' . $hari . ' ' . $tglMurniIndo;
?>

<!-- Filter Tanggal & Shift -->
<div class="card card-custom border-0 shadow-sm mb-4">
    <div class="card-body p-3">
        <form action="<?= site_url('piket/guru-absen') ?>" method="GET">
            <div class="row g-3 align-items-end">
                <div class="col-12 col-sm-6 col-md-4">
                    <label class="form-label small fw-semibold text-secondary mb-1">Pilih Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" value="<?= esc($tanggal) ?>" onchange="this.form.submit()">
                </div>
                <div class="col-12 col-sm-6 col-md-4">
                    <label class="form-label small fw-semibold text-secondary mb-1">Pilih Shift Sekolah</label>
                    <div class="btn-group w-100" role="group">
                        <input type="radio" class="btn-check" name="shift" id="shift_pagi" value="Pagi" <?= $shift === 'Pagi' ? 'checked' : '' ?> onchange="this.form.submit()">
                        <label class="btn btn-outline-warning text-dark fw-semibold" for="shift_pagi">
                            <i class="bi bi-sun-fill text-warning me-1"></i> Shift Pagi (<?= esc($jamMasukPagi ?? '06:30') ?>)
                        </label>

                        <input type="radio" class="btn-check" name="shift" id="shift_siang" value="Siang" <?= $shift === 'Siang' ? 'checked' : '' ?> onchange="this.form.submit()">
                        <label class="btn btn-outline-info text-primary fw-semibold" for="shift_siang">
                            <i class="bi bi-cloud-sun-fill text-primary me-1"></i> Shift Siang (<?= esc($jamMasukSiang ?? '12:30') ?>)
                        </label>
                    </div>
                </div>
                <div class="col-12 col-md-4 text-md-end">
                    <div class="p-2 bg-light rounded-3 border text-start">
                        <div class="text-muted small">Hari: <strong><?= $hari ?></strong> | Shift: <strong><?= $shift ?></strong></div>
                        <div class="fw-bold text-dark">Total Wajib Hadir: <?= count($guruTerjadwal) ?> Guru</div>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Informasi Anti-Alfa -->
<div class="alert alert-light border shadow-sm rounded-4 d-flex align-items-center justify-content-between mb-4">
    <div class="d-flex align-items-center gap-3">
        <div class="fs-3 text-success"><i class="bi bi-shield-check"></i></div>
        <div>
            <div class="fw-bold text-dark">Sistem Anti-Alfa Aktif</div>
            <small class="text-muted">
                Hanya guru yang memiliki jadwal mengajar pada <strong><?= $hari ?> Shift <?= $shift ?></strong> yang wajib diabsen. Guru yang tidak memiliki jam tidak akan dihitung sebagai Alfa.
            </small>
        </div>
    </div>
    <div class="d-flex gap-2">
        <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="bukaModalWA()">
            <i class="bi bi-whatsapp me-1"></i> Preview Format WA
        </button>
        <span class="badge bg-success bg-opacity-10 text-success border border-success px-3 py-2 rounded-pill d-none d-md-inline">
            <?= count($guruTerjadwal) ?> Terjadwal
        </span>
    </div>
</div>

<!-- Form Presensi Guru Terjadwal -->
<form action="<?= site_url('piket/guru-absen/simpan') ?>" method="POST" id="formPresensiGuru">
    <?= csrf_field() ?>
    <input type="hidden" name="tanggal" id="absenTanggal" value="<?= esc($tanggal) ?>">
    <input type="hidden" name="shift" id="absenShift" value="<?= esc($shift) ?>">

    <div class="card card-custom border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom py-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
            <h5 class="fw-bold text-dark mb-0">
                <i class="bi bi-card-checklist text-primary me-2"></i>Daftar Guru Wajib Hadir (<?= count($guruTerjadwal) ?> Orang)
            </h5>
            <div class="d-flex flex-wrap gap-2">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="resetSemuaStatus()">
                    <i class="bi bi-arrow-counterclockwise me-1"></i> Kosongkan Semua
                </button>
                <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="setSemuaHadir()">
                    <i class="bi bi-check-all me-1"></i> Tandai Semua Hadir
                </button>
                <button type="button" class="btn btn-sm btn-success rounded-pill px-3" onclick="bukaModalWA()">
                    <i class="bi bi-whatsapp me-1"></i> Format Laporan WA
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-custom table-hover align-middle mb-0" id="tabelGuruAbsen">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 50px;">No</th>
                        <th style="width: 160px;">NIP</th>
                        <th>Nama Lengkap Guru</th>
                        <th style="width: 250px;" class="text-center">Status Kehadiran</th>
                        <th style="width: 160px;">Jam Kedatangan</th>
                        <th style="width: 200px;">Keterangan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($guruTerjadwal)): ?>
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="bi bi-calendar-x fs-2 text-warning d-block mb-1"></i>
                                <span class="fw-bold text-dark">Tidak Ada Jadwal Mengajar Guru</span>
                                <div class="small">Tidak ada guru yang terdaftar memiliki jadwal mengajar pada hari <?= $hari ?> Shift <?= $shift ?>.</div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($guruTerjadwal as $idx => $gt): ?>
                            <?php 
                            $ab = $absenMap[$gt['id']] ?? null;
                            // Default status adalah NULL jika belum dicatat (bukan otomatis Hadir)
                            $currentStatus = $ab['status'] ?? null;
                            $currentJam    = $ab['jam_masuk'] ?? ($shift === 'Siang' ? '12:30' : '07:00');
                            $currentKet    = $ab['keterangan'] ?? '';
                            ?>
                            <tr class="baris-guru" data-id="<?= $gt['id'] ?>" data-nama="<?= esc($gt['nama_guru']) ?>">
                                <td class="text-center fw-semibold text-muted"><?= $idx + 1 ?></td>
                                <td><span class="badge bg-light text-dark border font-monospace"><?= esc($gt['nip'] ?? '-') ?></span></td>
                                <td class="fw-bold text-dark">
                                    <span class="nama-guru"><?= esc($gt['nama_guru']) ?></span>
                                    <div class="small text-muted font-monospace"><?= esc($gt['no_hp'] ?? '') ?></div>
                                </td>
                                <td>
                                    <div class="d-flex justify-content-center align-items-center gap-1">
                                        <!-- Hadir -->
                                        <input type="radio" class="status-pill-radio radio-status" name="guru[<?= $gt['id'] ?>][status]" id="status_h_<?= $gt['id'] ?>" value="H" <?= $currentStatus === 'H' ? 'checked' : '' ?> onclick="handleRadioToggle(this)" onchange="onStatusChanged()">
                                        <label class="status-label" for="status_h_<?= $gt['id'] ?>" title="Hadir (✅)">H</label>

                                        <!-- Sakit -->
                                        <input type="radio" class="status-pill-radio radio-status" name="guru[<?= $gt['id'] ?>][status]" id="status_s_<?= $gt['id'] ?>" value="S" <?= $currentStatus === 'S' ? 'checked' : '' ?> onclick="handleRadioToggle(this)" onchange="onStatusChanged()">
                                        <label class="status-label" for="status_s_<?= $gt['id'] ?>" title="Sakit">S</label>

                                        <!-- Izin -->
                                        <input type="radio" class="status-pill-radio radio-status" name="guru[<?= $gt['id'] ?>][status]" id="status_i_<?= $gt['id'] ?>" value="I" <?= $currentStatus === 'I' ? 'checked' : '' ?> onclick="handleRadioToggle(this)" onchange="onStatusChanged()">
                                        <label class="status-label" for="status_i_<?= $gt['id'] ?>" title="Izin">I</label>

                                        <!-- Alfa -->
                                        <input type="radio" class="status-pill-radio radio-status" name="guru[<?= $gt['id'] ?>][status]" id="status_a_<?= $gt['id'] ?>" value="A" <?= $currentStatus === 'A' ? 'checked' : '' ?> onclick="handleRadioToggle(this)" onchange="onStatusChanged()">
                                        <label class="status-label" for="status_a_<?= $gt['id'] ?>" title="Alfa">A</label>

                                        <!-- Batal / Kosongkan status guru ini -->
                                        <button type="button" class="btn btn-sm btn-link text-secondary p-0 ms-1 text-decoration-none" onclick="resetStatusGuru(<?= $gt['id'] ?>)" title="Batal / Reset ke Belum Datang (Null)">
                                            <i class="bi bi-x-circle text-muted fs-6"></i>
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light"><i class="bi bi-clock"></i></span>
                                        <input type="time" class="form-control jam-input input-jam" name="guru[<?= $gt['id'] ?>][jam_masuk]" value="<?= esc(substr($currentJam, 0, 5)) ?>" onchange="onStatusChanged()">
                                    </div>
                                </td>
                                <td>
                                    <input type="text" class="form-control form-control-sm input-keterangan" name="guru[<?= $gt['id'] ?>][keterangan]" value="<?= esc($currentKet) ?>" placeholder="Catatan opsional" oninput="onStatusChanged()">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if (! empty($guruTerjadwal)): ?>
            <div class="card-footer bg-white border-top py-3 d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2">
                <button type="button" class="btn btn-outline-success px-3" onclick="bukaModalWA()">
                    <i class="bi bi-whatsapp me-1"></i> Preview & Kirim WA Grup
                </button>
                <button type="submit" class="btn btn-primary-custom px-4">
                    <i class="bi bi-save2-fill me-1"></i> Simpan Presensi Kedatangan Guru
                </button>
            </div>
        <?php endif; ?>
    </div>
</form>

<!-- Daftar Guru Tanpa Jam Mengajar (Bukan Alfa) -->
<div class="card card-custom border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 py-3" data-bs-toggle="collapse" data-bs-target="#collapseTanpaJam" role="button">
        <div class="d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-secondary bg-opacity-10 text-secondary border p-2 rounded-3">
                    <i class="bi bi-cup-hot-fill fs-6"></i>
                </span>
                <div>
                    <h6 class="fw-bold text-dark mb-0">Guru Tanpa Jam Mengajar Hari Ini (Shift <?= $shift ?>)</h6>
                    <small class="text-muted"><?= count($guruTanpaJam) ?> guru tidak memiliki jam tatap muka (Aman / Bukan Alfa)</small>
                </div>
            </div>
            <i class="bi bi-chevron-down text-muted"></i>
        </div>
    </div>

    <div class="collapse" id="collapseTanpaJam">
        <div class="card-body pt-0">
            <div class="row g-2">
                <?php if (empty($guruTanpaJam)): ?>
                    <div class="col-12 text-muted small py-2">Seluruh guru memiliki jadwal mengajar pada shift ini.</div>
                <?php else: ?>
                    <?php foreach ($guruTanpaJam as $gtj): ?>
                        <div class="col-12 col-sm-6 col-md-4">
                            <div class="p-2 border rounded-3 bg-light d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold text-dark small"><?= esc($gtj['nama_guru']) ?></div>
                                    <div class="text-muted" style="font-size: 0.72rem;">NIP: <?= esc($gtj['nip'] ?? '-') ?></div>
                                </div>
                                <span class="badge bg-secondary bg-opacity-10 text-muted border">Tidak Ada Jam</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Floating Action Button untuk Akses Cepat WA -->
<div class="position-fixed bottom-0 end-0 p-3" style="z-index: 1040;">
    <button type="button" class="btn btn-success rounded-pill shadow-lg py-2 px-3 d-flex align-items-center gap-2 border-2 border-white" onclick="bukaModalWA()" title="Buka Template Laporan WhatsApp">
        <i class="bi bi-whatsapp fs-5"></i>
        <span class="fw-bold d-none d-sm-inline">Laporan WA</span>
        <span class="badge bg-white text-success rounded-pill font-monospace fw-bold px-2 py-1" id="countHadirBadge">0 Hadir</span>
    </button>
</div>

<!-- Modal Template Laporan WhatsApp -->
<div class="modal fade" id="modalLaporanWA" tabindex="-1" aria-labelledby="modalLaporanWALabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header bg-success text-white py-3">
                <div class="d-flex align-items-center gap-2">
                    <i class="bi bi-whatsapp fs-3"></i>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="modalLaporanWALabel">Template Laporan Kehadiran Guru (WhatsApp)</h5>
                        <small class="text-white-50">Kirim laporan kehadiran guru ke grup WhatsApp piket sekolah</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <!-- Opsi Tampilan -->
                <div class="card bg-light border-0 mb-3 p-3 rounded-3">
                    <div class="small fw-bold text-secondary text-uppercase mb-2">Opsi Format Laporan:</div>
                    <div class="row g-2">
                        <div class="col-12 col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="optTulisBelumHadir" checked onchange="updateLaporanWA()">
                                <label class="form-check-label small fw-semibold" for="optTulisBelumHadir">
                                    Tulis Guru Belum Datang (Tanpa Ceklis)
                                </label>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="optSertakanJam" onchange="updateLaporanWA()">
                                <label class="form-check-label small fw-semibold" for="optSertakanJam">
                                    Sertakan Jam Kedatangan
                                </label>
                            </div>
                        </div>
                        <div class="col-12 col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="optSertakanTidakHadir" checked onchange="updateLaporanWA()">
                                <label class="form-check-label small fw-semibold" for="optSertakanTidakHadir">
                                    Sertakan Status Izin / Sakit / Alfa
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Live Preview Textarea -->
                <div class="mb-2 d-flex justify-content-between align-items-center">
                    <label class="form-label fw-bold text-dark mb-0">
                        <i class="bi bi-chat-text-fill text-success me-1"></i> Format Siap Kirim (Bisa diedit manual jika perlu):
                    </label>
                    <span class="badge bg-success bg-opacity-10 text-success border border-success" id="badgeJumlahHadirModal">0 Hadir / 0 Belum Datang</span>
                </div>
                <textarea id="teksLaporanWA" class="form-control font-monospace border-2 border-success p-3" rows="13" style="font-size: 0.92rem; line-height: 1.6; resize: vertical;" placeholder="Memuat format laporan..."></textarea>

                <div class="alert alert-info py-2 px-3 small d-flex align-items-center gap-2 mt-3 mb-0">
                    <i class="bi bi-info-circle-fill fs-5 text-primary flex-shrink-0"></i>
                    <span>Nama guru yang sudah hadir diberi tanda centang <strong>✅</strong>. Guru yang belum datang tetap ditulis namanya tetapi <strong>tanpa tanda centang</strong>. Klik <strong>Salin Teks</strong> untuk membagikan ke grup WhatsApp.</span>
                </div>
            </div>
            <div class="modal-footer bg-light py-3 px-4 justify-content-between">
                <button type="button" class="btn btn-outline-secondary px-3" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg me-1"></i> Tutup
                </button>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-primary px-3 shadow-sm" id="btnSalinWA" onclick="salinTeksWA()">
                        <i class="bi bi-clipboard-check me-1"></i> Salin Teks
                    </button>
                    <button type="button" class="btn btn-success px-4 shadow-sm" onclick="kirimWhatsAppDirect()">
                        <i class="bi bi-whatsapp me-1"></i> Buka WhatsApp
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script>
    const WA_HEADER_TITLE = "Absensi Kehadiran Guru";
    const WA_HEADER_SUB = "<?= esc($headerLaporanWA) ?>";

    // Fungsi cerdas untuk menambahkan sapaan Bapak / Ibu pada nama guru
    function formatNamaGuruDenganSapaan(nama) {
        if (!nama) return '';
        let trimNama = nama.trim();
        
        // Cek jika sudah memiliki awalan Bapak / Pak / Ibu / Bu
        if (/^(bapak|pak)\s+/i.test(trimNama)) {
            return 'Bapak ' + trimNama.replace(/^(bapak|pak)\s+/i, '');
        }
        if (/^(ibu|bu)\s+/i.test(trimNama)) {
            return 'Ibu ' + trimNama.replace(/^(ibu|bu)\s+/i, '');
        }

        // Daftar kata kunci nama perempuan Indonesia
        const kataKunciWanita = [
            'fitri', 'sri', 'astri', 'christin', 'wida', 'idayatul', 'widjayanti', 
            'dewi', 'putri', 'nur', 'anisa', 'ani', 'rina', 'ratih', 'dwi', 
            'lestari', 'rahayu', 'retno', 'tri', 'endah', 'yuni', 'diah', 'indah', 
            'maya', 'nita', 'lia', 'ayu', 'maria', 'fatimah', 'aisyah', 'khadijah', 
            'nurul', 'safitri', 'wahyuni', 'susanti', 'handayani', 'novita', 'yuliana', 
            'mutia', 'hartini', 'kartika', 'kusuma', 'sulastri', 'sarah', 'rachel', 
            'diana', 'tita', 'mulyati', 'hartani', 'mustafidah'
        ];

        const namaKecil = trimNama.toLowerCase();
        const kataArray = namaKecil.split(/[\s,.]+/);
        const isWanita = kataArray.some(k => kataKunciWanita.includes(k));

        return (isWanita ? 'Ibu ' : 'Bapak ') + trimNama;
    }

    // Toggle radio button jika diklik kembali (bisa di-uncheck)
    function handleRadioToggle(radio) {
        if (radio.dataset.wasChecked === "true") {
            radio.checked = false;
            radio.dataset.wasChecked = "false";
            onStatusChanged();
        } else {
            const groupName = radio.name;
            document.querySelectorAll(`input[name="${groupName}"]`).forEach(r => r.dataset.wasChecked = "false");
            radio.dataset.wasChecked = "true";
            onStatusChanged();
        }
    }

    // Reset status per guru ke null (belum absen)
    function resetStatusGuru(guruId) {
        document.querySelectorAll(`input[name="guru[${guruId}][status]"]`).forEach(function(radio) {
            radio.checked = false;
            radio.dataset.wasChecked = "false";
        });
        onStatusChanged();
    }

    // Reset semua status guru ke null (belum absen)
    function resetSemuaStatus() {
        document.querySelectorAll('.radio-status').forEach(function(radio) {
            radio.checked = false;
            radio.dataset.wasChecked = "false";
        });
        onStatusChanged();
    }

    // Set semua guru hadir
    function setSemuaHadir() {
        document.querySelectorAll('input[type="radio"][value="H"]').forEach(function(radio) {
            radio.checked = true;
            radio.dataset.wasChecked = "true";
        });
        // Non-aktifkan status selain H
        document.querySelectorAll('input[type="radio"]:not([value="H"])').forEach(function(radio) {
            radio.dataset.wasChecked = "false";
        });
        onStatusChanged();
    }

    // Bangun teks laporan WhatsApp dari baris tabel
    function generateLaporanText() {
        const rows = document.querySelectorAll('.baris-guru');
        const tulisBelumHadir = document.getElementById('optTulisBelumHadir')?.checked ?? true;
        const sertakanJam = document.getElementById('optSertakanJam')?.checked ?? false;
        const sertakanTidakHadir = document.getElementById('optSertakanTidakHadir')?.checked ?? true;

        let listLines = [];
        let countHadir = 0;
        let countBelumHadir = 0;
        let noUrut = 1;

        rows.forEach(function(row) {
            const rawNama = row.getAttribute('data-nama') || row.querySelector('.nama-guru')?.innerText?.trim() || '';
            const checkedRadio = row.querySelector('.radio-status:checked');
            const status = checkedRadio ? checkedRadio.value : null;
            const jam = row.querySelector('.input-jam')?.value || '';
            const ket = row.querySelector('.input-keterangan')?.value?.trim() || '';

            const namaBerwibawa = formatNamaGuruDenganSapaan(rawNama);

            if (status === 'H') {
                countHadir++;
                let item = `${noUrut}. ${namaBerwibawa}✅`;
                if (sertakanJam && jam) {
                    item += ` (${jam} WIB)`;
                }
                listLines.push(item);
                noUrut++;
            } else if (!status) {
                // Belum datang / null: Tulis nama tapi ceklisan ga ada!
                countBelumHadir++;
                if (tulisBelumHadir) {
                    listLines.push(`${noUrut}. ${namaBerwibawa}`);
                    noUrut++;
                }
            } else {
                // S, I, A
                if (sertakanTidakHadir) {
                    let labelStatus = '';
                    if (status === 'S') labelStatus = ' (Sakit)';
                    else if (status === 'I') labelStatus = ' (Izin)';
                    else if (status === 'A') labelStatus = ' (Alfa)';
                    if (ket) labelStatus += ` - ${ket}`;

                    listLines.push(`${noUrut}. ${namaBerwibawa}${labelStatus}`);
                    noUrut++;
                } else if (tulisBelumHadir) {
                    listLines.push(`${noUrut}. ${namaBerwibawa}`);
                    noUrut++;
                }
            }
        });

        // Update badge jumlah hadir
        const badgeCount = document.getElementById('countHadirBadge');
        if (badgeCount) {
            badgeCount.innerText = `${countHadir} Hadir`;
        }
        const badgeModal = document.getElementById('badgeJumlahHadirModal');
        if (badgeModal) {
            badgeModal.innerText = `${countHadir} Hadir / ${countBelumHadir} Belum Datang`;
        }

        // Rakit template akhir
        let textResult = `${WA_HEADER_TITLE}\n${WA_HEADER_SUB}\n\n`;
        if (listLines.length === 0) {
            textResult += `(Belum ada jadwal guru)`;
        } else {
            textResult += listLines.join('\n');
        }

        return {
            text: textResult,
            countHadir: countHadir,
            countBelumHadir: countBelumHadir
        };
    }

    function updateLaporanWA() {
        const data = generateLaporanText();
        const textarea = document.getElementById('teksLaporanWA');
        if (textarea) {
            textarea.value = data.text;
        }
    }

    function onStatusChanged() {
        const data = generateLaporanText();
        const textarea = document.getElementById('teksLaporanWA');
        if (textarea) {
            textarea.value = data.text;
        }
    }

    function bukaModalWA() {
        updateLaporanWA();
        const modalEl = document.getElementById('modalLaporanWA');
        if (modalEl) {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        }
    }

    function salinTeksWA() {
        const textarea = document.getElementById('teksLaporanWA');
        if (!textarea) return;

        textarea.select();
        textarea.setSelectionRange(0, 99999); // Untuk mobile

        navigator.clipboard.writeText(textarea.value).then(function() {
            const btn = document.getElementById('btnSalinWA');
            const originalHtml = btn.innerHTML;
            btn.classList.replace('btn-primary', 'btn-success');
            btn.innerHTML = '<i class="bi bi-check2-all me-1"></i> Tersalin!';
            setTimeout(function() {
                btn.classList.replace('btn-success', 'btn-primary');
                btn.innerHTML = originalHtml;
            }, 2000);
        }).catch(function(err) {
            document.execCommand('copy');
            alert('Format laporan berhasil disalin!');
        });
    }

    function kirimWhatsAppDirect() {
        const textarea = document.getElementById('teksLaporanWA');
        if (!textarea) return;
        const textEncoded = encodeURIComponent(textarea.value);
        const url = `https://api.whatsapp.com/send?text=${textEncoded}`;
        window.open(url, '_blank');
    }

    // Inisialisasi status saat halaman dimuat
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.radio-status:checked').forEach(function(radio) {
            radio.dataset.wasChecked = "true";
        });
        generateLaporanText();
    });
</script>
<?= $this->endSection() ?>

