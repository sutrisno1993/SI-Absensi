<?= $this->extend('layout/main') ?>

<?= $this->section('content') ?>
<?php
    $linkLive = site_url('live-quiz/' . $asesmen['token_live']);
    $waText = "Halo teman-teman kelas {$sesi['nama_kelas']}, silakan buka tautan berikut untuk mengerjakan {$asesmen['judul_asesmen']}: " . $linkLive;
?>

<div class="row align-items-center mb-4">
    <div class="col-12 col-md-7">
        <a href="<?= site_url('lms/sesi/' . $asesmen['sesi_id']) ?>" class="text-secondary small text-decoration-none d-inline-flex align-items-center mb-1">
            <i class="bi bi-arrow-left me-1"></i> Kembali ke Pertemuan KBM
        </a>
        <h3 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
            <i class="bi bi-broadcast text-danger"></i>
            <span><?= esc($asesmen['judul_asesmen']) ?></span>
            <span class="badge bg-primary bg-opacity-15 text-primary text-uppercase px-2 py-1 rounded-pill fs-6"><?= esc($asesmen['tipe']) ?></span>
        </h3>
        <div class="text-muted small">
            Kelas <strong><?= esc($sesi['nama_kelas']) ?></strong> &bull; Mapel: <strong><?= esc($sesi['nama_mapel']) ?></strong> &bull; Materi: <?= esc($sesi['judul_materi']) ?>
        </div>
    </div>
    <div class="col-12 col-md-6 text-md-end mt-3 mt-md-0 d-flex flex-wrap justify-content-md-end gap-2">
        <button type="button" class="btn btn-outline-success rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalImportJson">
            <i class="bi bi-file-earmark-code me-1"></i> Paste / Import JSON
        </button>
        <button type="button" class="btn btn-outline-primary rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahSoalManual">
            <i class="bi bi-plus-circle me-1"></i> Tambah Soal Manual
        </button>
        <button type="button" class="btn btn-success rounded-pill px-3 shadow-sm" onclick="bagikanWA()">
            <i class="bi bi-whatsapp me-1"></i> Share ke WhatsApp
        </button>
    </div>
</div>

<!-- Kartu QR Code & Tautan Akses Siswa -->
<div class="card card-custom border-0 shadow-sm p-4 mb-4" style="background: linear-gradient(135deg, rgba(99, 102, 241, 0.08) 0%, rgba(16, 185, 129, 0.08) 100%);">
    <div class="row align-items-center g-3">
        <div class="col-12 col-md-3 text-center">
            <!-- QR Code Generator Dinamis via API -->
            <div class="p-2 bg-white rounded-3 d-inline-block border shadow-sm">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=<?= urlencode($linkLive) ?>" alt="QR Code Asesmen" class="img-fluid" style="width: 140px; height: 140px;">
            </div>
            <div class="small text-muted mt-1 fw-bold">Scan untuk Mengerjakan</div>
        </div>
        <div class="col-12 col-md-9">
            <span class="badge bg-success bg-opacity-15 text-success border border-success px-3 py-1 rounded-pill mb-2">
                <i class="bi bi-broadcast me-1"></i> Live Session Aktif
            </span>
            <h4 class="fw-bold text-dark mb-1">Tampilkan QR Code di Proyektor Kelas atau Bagikan Tautan</h4>
            <p class="text-muted small mb-3">Siswa cukup membuka tautan di HP masing-masing, memilih nama dari daftar kelas, dan jawaban akan langsung terhitung secara otomatis saat disubmit.</p>

            <div class="input-group">
                <span class="input-group-text bg-white border"><i class="bi bi-link-45deg fs-5"></i></span>
                <input type="text" id="inputLinkLive" class="form-control font-monospace bg-white" value="<?= esc($linkLive) ?>" readonly>
                <button type="button" class="btn btn-primary-custom px-4" onclick="salinLink()">
                    <i class="bi bi-clipboard me-1"></i> Salin Tautan
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Papan Nilai & Hasil Live Siswa -->
<div class="row g-4 mb-4">
    <!-- Kolom Kiri: Live Respons & Skor Siswa -->
    <div class="col-12 col-xl-8">
        <div class="card card-custom border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <div>
                    <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="bi bi-speedometer2 text-primary"></i>
                        <span>Papan Live Respons &amp; Skor Siswa</span>
                    </h5>
                    <small class="text-muted" id="statusLiveText"><i class="bi bi-arrow-repeat spin me-1"></i>Memperbarui otomatis setiap beberapa detik...</small>
                </div>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="muatSkorLive()">
                    <i class="bi bi-arrow-clockwise me-1"></i> Refresh
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 45px;">No</th>
                            <th>Nama Siswa</th>
                            <th class="text-center" style="width: 130px;">PG Benar</th>
                            <th class="text-center" style="width: 140px;">Total Skor</th>
                            <th class="text-center" style="width: 140px;">Waktu Submit</th>
                            <th class="text-center" style="width: 140px;">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="tabelBodySkor">
                        <?php if (empty($rekapNilai)): ?>
                            <tr id="rowKosong">
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="bi bi-hourglass-split fs-2 d-block mb-1 text-warning"></i>
                                    Menunggu siswa mengumpulkan jawaban...
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($rekapNilai as $idx => $r): ?>
                                <tr>
                                    <td class="text-center text-muted fw-semibold"><?= $idx + 1 ?></td>
                                    <td>
                                        <div class="fw-bold text-dark"><?= esc($r['nama_siswa']) ?></div>
                                        <small class="text-muted font-monospace"><?= esc($r['nisn']) ?></small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-success bg-opacity-15 text-success border border-success px-2 py-1">
                                            <?= $r['total_pg_benar'] ?> Soal Benar
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        <span class="fs-5 fw-bold text-primary"><?= number_format($r['total_skor'], 1) ?></span>
                                    </td>
                                    <td class="text-center small text-muted">
                                        <?= !empty($r['waktu_selesai']) ? date('H:i:s', strtotime($r['waktu_selesai'])) : '-' ?>
                                    </td>
                                    <td class="text-center">
                                        <a href="<?= site_url('lms/asesmen/' . $asesmen['id'] . '/koreksi-essay/' . $r['siswa_id']) ?>" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1">
                                            <i class="bi bi-pencil-square me-1"></i> Koreksi Essay
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Daftar Soal Asesmen -->
    <div class="col-12 col-xl-4">
        <div class="card card-custom border-0 shadow-sm h-100">
            <div class="card-header bg-white border-0 py-3 d-flex justify-content-between align-items-center">
                <h5 class="fw-bold text-dark mb-0">
                    <i class="bi bi-list-ol text-primary me-2"></i>Daftar Soal (<?= count($soalList) ?>)
                </h5>
                <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-2 py-1" data-bs-toggle="modal" data-bs-target="#modalTambahSoalManual">
                    + Soal
                </button>
            </div>
            <div class="card-body p-3" style="max-height: 520px; overflow-y: auto;">
                <?php if (empty($soalList)): ?>
                    <div class="text-center py-4 text-muted small">
                        Belum ada butir soal. Silakan tambah butir soal manual.
                    </div>
                <?php else: ?>
                    <?php foreach ($soalList as $s): ?>
                        <div class="p-3 border rounded-3 mb-2 bg-light">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <span class="badge <?= $s['tipe_soal'] === 'pg' ? 'bg-info' : 'bg-warning text-dark' ?> rounded-pill">
                                    No. <?= $s['nomor_urut'] ?> &bull; <?= strtoupper($s['tipe_soal']) ?>
                                </span>
                                <small class="text-muted fw-bold"><?= $s['bobot'] ?> Poin</small>
                            </div>
                            <div class="fw-semibold text-dark small mb-2"><?= nl2br(esc($s['pertanyaan'])) ?></div>
                            <?php if ($s['tipe_soal'] === 'pg'): ?>
                                <div class="small text-muted" style="font-size: 0.76rem;">
                                    <div>A. <?= esc($s['opsi_a']) ?></div>
                                    <div>B. <?= esc($s['opsi_b']) ?></div>
                                    <div>C. <?= esc($s['opsi_c']) ?></div>
                                    <div>D. <?= esc($s['opsi_d']) ?></div>
                                    <?php if (!empty($s['opsi_e'])): ?><div>E. <?= esc($s['opsi_e']) ?></div><?php endif; ?>
                                    <div class="mt-1 text-success fw-bold"><i class="bi bi-check-circle me-1"></i>Kunci: Opsi <?= strtoupper($s['kunci_pg']) ?></div>
                                </div>
                            <?php else: ?>
                                <div class="small text-muted" style="font-size: 0.76rem;">
                                    <strong>Pedoman Jawaban:</strong> <?= esc($s['pedoman_essay'] ?? '-') ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Soal Manual -->
<div class="modal fade" id="modalTambahSoalManual" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-dark">Tambah Butir Soal Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= site_url('lms/asesmen/' . $asesmen['id'] . '/soal') ?>" method="POST">
                <?= csrf_field() ?>
                <div class="modal-body py-4">
                    <div class="row g-2 mb-3">
                        <div class="col-8">
                            <label class="form-label small fw-semibold text-secondary">Tipe Soal</label>
                            <select class="form-select" name="tipe_soal" id="select_tipe_soal" onchange="toggleFormSoal(this.value)">
                                <option value="pg">Pilihan Ganda (A - E)</option>
                                <option value="essay">Essay / Uraian</option>
                            </select>
                        </div>
                        <div class="col-4">
                            <label class="form-label small fw-semibold text-secondary">Bobot Poin</label>
                            <input type="number" class="form-control" name="bobot" value="10" min="1" max="100">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Teks Pertanyaan / Soal <span class="text-danger">*</span></label>
                        <textarea class="form-control" name="pertanyaan" rows="3" required placeholder="Tuliskan pertanyaan di sini..."></textarea>
                    </div>

                    <!-- Input Opsi PG -->
                    <div id="sectionOpsiPg">
                        <div class="row g-2 mb-2">
                            <div class="col-6">
                                <label class="small text-muted">Pilihan A</label>
                                <input type="text" class="form-control form-control-sm" name="opsi_a">
                            </div>
                            <div class="col-6">
                                <label class="small text-muted">Pilihan B</label>
                                <input type="text" class="form-control form-control-sm" name="opsi_b">
                            </div>
                            <div class="col-6">
                                <label class="small text-muted">Pilihan C</label>
                                <input type="text" class="form-control form-control-sm" name="opsi_c">
                            </div>
                            <div class="col-6">
                                <label class="small text-muted">Pilihan D</label>
                                <input type="text" class="form-control form-control-sm" name="opsi_d">
                            </div>
                            <div class="col-12">
                                <label class="small text-muted">Pilihan E</label>
                                <input type="text" class="form-control form-control-sm" name="opsi_e">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-success">Kunci Jawaban Benar (PG)</label>
                            <select class="form-select form-select-sm" name="kunci_pg">
                                <option value="a">A</option>
                                <option value="b">B</option>
                                <option value="c">C</option>
                                <option value="d">D</option>
                                <option value="e">E</option>
                            </select>
                        </div>
                    </div>

                    <!-- Input Pedoman Essay -->
                    <div id="sectionPedomanEssay" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-secondary">Kunci Jawaban / Pedoman Penilaian Essay</label>
                            <textarea class="form-control" name="pedoman_essay" rows="2" placeholder="Tuliskan kata kunci atau poin jawaban yang diharapkan..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary-custom rounded-pill px-4 fw-semibold">Simpan Soal</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Import / Paste JSON -->
<div class="modal fade" id="modalImportJson" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-0 pb-0">
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-success text-white rounded-3 p-2">
                        <i class="bi bi-file-earmark-code fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-dark mb-0">Import / Paste Soal dari JSON</h5>
                        <small class="text-muted">Masukkan soal yang digenerate oleh AI (ChatGPT / Gemini / Claude)</small>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="<?= site_url('lms/asesmen/' . $asesmen['id'] . '/import-json') ?>" method="POST" enctype="multipart/form-data">
                <?= csrf_field() ?>
                <div class="modal-body py-4">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small fw-bold text-dark">Pilih Cara Input:</span>
                        <button type="button" class="btn btn-xs btn-link text-decoration-none" onclick="isiContohJsonDetail()">
                            <i class="bi bi-magic me-1"></i>Isi Contoh Template JSON
                        </button>
                    </div>

                    <ul class="nav nav-pills nav-fill mb-3" id="pillsTabImport" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active py-2 small rounded-pill fw-semibold" id="tab-paste-import" data-bs-toggle="pill" data-bs-target="#pane-paste-import" type="button" role="tab">
                                <i class="bi bi-clipboard-check me-1"></i> Paste Kode JSON Langsung
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-2 small rounded-pill fw-semibold" id="tab-upload-import" data-bs-toggle="pill" data-bs-target="#pane-upload-import" type="button" role="tab">
                                <i class="bi bi-upload me-1"></i> Upload Berkas .json
                            </button>
                        </li>
                    </ul>

                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="pane-paste-import" role="tabpanel">
                            <textarea class="form-control font-monospace" name="paste_json" id="textareaImportJson" rows="9" style="font-size: 0.82rem;" placeholder='Paste kode JSON di sini...&#10;{&#10;  "soal": [ ... ]&#10;}'></textarea>
                        </div>
                        <div class="tab-pane fade" id="pane-upload-import" role="tabpanel">
                            <div class="p-4 border border-dashed rounded-3 text-center bg-light">
                                <i class="bi bi-file-earmark-arrow-up display-5 text-secondary d-block mb-2"></i>
                                <input type="file" class="form-control w-75 mx-auto" name="file_json" accept=".json">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold shadow-sm">
                        <i class="bi bi-check-circle me-1"></i> Import Soal Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function isiContohJsonDetail() {
    const contoh = {
        "soal": [
            {
                "tipe": "pg",
                "pertanyaan": "Manakah port default yang digunakan oleh protokol DNS?",
                "opsi": {
                    "a": "21",
                    "b": "22",
                    "c": "53",
                    "d": "80",
                    "e": "443"
                },
                "kunci": "c",
                "bobot": 10
            },
            {
                "tipe": "essay",
                "pertanyaan": "Jelaskan perbedaan antara alamat IP Publik dan Alamat IP Privat serta sebutkan rentang IP Privat kelas C!",
                "pedoman_jawaban": "IP Publik digunakan di internet global dan unik di dunia. IP Privat digunakan dalam jaringan lokal LAN (rentang kelas C: 192.168.0.0 - 192.168.255.255) dan memerlukan NAT untuk terhubung ke internet.",
                "bobot": 20
            }
        ]
    };
    document.getElementById('textareaImportJson').value = JSON.stringify(contoh, null, 2);
    const tabEl = document.getElementById('tab-paste-import');
    if (tabEl) {
        bootstrap.Tab.getOrCreateInstance(tabEl).show();
    }
}
function salinLink() {
    const input = document.getElementById('inputLinkLive');
    input.select();
    input.setSelectionRange(0, 99999);
    navigator.clipboard.writeText(input.value);
    alert('Tautan Asesmen Live berhasil disalin ke clipboard!');
}

function bagikanWA() {
    const text = "<?= rawurlencode($waText) ?>";
    window.open("https://api.whatsapp.com/send?text=" + text, "_blank");
}

function toggleFormSoal(tipe) {
    if (tipe === 'essay') {
        document.getElementById('sectionOpsiPg').style.display = 'none';
        document.getElementById('sectionPedomanEssay').style.display = 'block';
    } else {
        document.getElementById('sectionOpsiPg').style.display = 'block';
        document.getElementById('sectionPedomanEssay').style.display = 'none';
    }
}

// Live polling skor otomatis setiap 4 detik
function muatSkorLive() {
    fetch('<?= site_url('lms/asesmen/' . $asesmen['id'] . '/live-scores') ?>')
        .then(res => res.json())
        .then(res => {
            if (res.status === 'success' && res.data.length > 0) {
                const tbody = document.getElementById('tabelBodySkor');
                let html = '';
                res.data.forEach((r, idx) => {
                    const tgl = r.waktu_selesai ? r.waktu_selesai.split(' ')[1] : '-';
                    html += `<tr>
                        <td class="text-center text-muted fw-semibold">${idx + 1}</td>
                        <td>
                            <div class="fw-bold text-dark">${r.nama_siswa}</div>
                            <small class="text-muted font-monospace">${r.nisn}</small>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-success bg-opacity-15 text-success border border-success px-2 py-1">
                                ${r.total_pg_benar} Soal Benar
                            </span>
                        </td>
                        <td class="text-center">
                            <span class="fs-5 fw-bold text-primary">${parseFloat(r.total_skor).toFixed(1)}</span>
                        </td>
                        <td class="text-center small text-muted">${tgl}</td>
                        <td class="text-center">
                            <a href="<?= rtrim(site_url('lms/asesmen/' . $asesmen['id'] . '/koreksi-essay'), '/') ?>/${r.siswa_id}" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1">
                                <i class="bi bi-pencil-square me-1"></i> Koreksi Essay
                            </a>
                        </td>
                    </tr>`;
                });
                tbody.innerHTML = html;
            }
        });
}

setInterval(muatSkorLive, 4000);
</script>
<?= $this->endSection() ?>

