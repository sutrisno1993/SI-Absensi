<?php

namespace App\Controllers;

use App\Models\AbsensiModel;
use App\Models\KelasModel;
use App\Models\SiswaModel;
use App\Models\GuruModel;
use App\Models\PresensiKelasHarianModel;

class PjKelasController extends BaseController
{
    protected $absensiModel;
    protected $kelasModel;
    protected $siswaModel;
    protected $guruModel;
    protected $presensiKelasHarianModel;

    public function __construct()
    {
        $this->absensiModel             = new AbsensiModel();
        $this->kelasModel               = new KelasModel();
        $this->siswaModel               = new SiswaModel();
        $this->guruModel                = new GuruModel();
        $this->presensiKelasHarianModel = new PresensiKelasHarianModel();
    }

    /**
     * Tampilan form input absensi harian kelas sendiri & upload foto dokumentasi
     */
    public function index()
    {
        $kelasId = (int)$this->session->get('ref_id');
        $kelas   = $this->kelasModel->find($kelasId);

        if (! $kelas) {
            return redirect()->to('/auth/login')->with('error', 'Kelas Anda tidak ditemukan dalam sistem.');
        }

        // Ambil parameter tanggal atau gunakan hari ini
        $tanggal = $this->request->getGet('tanggal');
        if (empty($tanggal) || ! strtotime($tanggal)) {
            $tanggal = date('Y-m-d');
        }

        // Isolation data: hanya ambil siswa milik kelas ini
        $daftarSiswa = $this->absensiModel->getAbsensiByDateAndKelas($tanggal, $kelasId);

        // Ambil atau buat sesi presensi harian kelas hari ini (termasuk share token publik)
        $sesiKelas = $this->presensiKelasHarianModel->getOrCreateForToday($kelasId, $tanggal);

        // Ambil data wali kelas
        $walas = !empty($kelas['walas_id']) ? $this->guruModel->find($kelas['walas_id']) : null;

        $hasExistingRecords = !empty(array_filter($daftarSiswa, static fn($s) => !empty($s['absensi_id'])));

        // Hitung rekapitulasi kehadiran (hanya dihitung jika presensi sudah diisi)
        $isKelas2 = $this->absensiModel->isKelas2($kelas['nama_kelas']);
        $countH = 0; $countS = 0; $countI = 0; $countA = 0; $countP = 0;
        if ($hasExistingRecords) {
            foreach ($daftarSiswa as $s) {
                if (empty($s['absensi_id'])) continue;
                $st = $s['status'] ?? 'H';
                if ($st === 'H') $countH++;
                elseif ($st === 'S') $countS++;
                elseif ($st === 'I') $countI++;
                elseif ($st === 'A') $countA++;
                elseif ($st === 'P') $countP++;
            }
        }

        $kalenderModel    = new \App\Models\KalenderPendidikanModel();
        $tingkat          = $this->absensiModel->getTingkatKelas($kelas['nama_kelas']);
        $agendaBebasAbsen = $kalenderModel->isHariBebasAbsen($tanggal, $tingkat, $kelasId);
        $isSunday         = ((int)date('w', strtotime($tanggal)) === 0);
        $kalenderEvents   = $kalenderModel->getEventsForPicker($tingkat, $kelasId);

        return view('pj/absen_harian', [
            'title'              => 'Input Presensi Harian - ' . $kelas['nama_kelas'],
            'kelas'              => $kelas,
            'walas'              => $walas,
            'tanggal'            => $tanggal,
            'daftarSiswa'        => $daftarSiswa,
            'sesiKelas'          => $sesiKelas,
            'countH'             => $countH,
            'countS'             => $countS,
            'countI'             => $countI,
            'countA'             => $countA,
            'countP'             => $countP,
            'isKelas2'           => $isKelas2,
            'agendaBebasAbsen'   => $agendaBebasAbsen,
            'isSunday'           => $isSunday,
            'hasExistingRecords' => $hasExistingRecords,
            'kalenderEvents'     => $kalenderEvents,
        ]);
    }

    /**
     * Proses simpan absensi dengan validasi isolasi data kelas & opsi upload foto
     */
    public function simpan()
    {
        $kelasId = (int)$this->session->get('ref_id');
        $tanggal = $this->request->getPost('tanggal') ?: date('Y-m-d');
        $inputAbsensi = $this->request->getPost('absensi') ?: [];

        if (empty($inputAbsensi)) {
            return redirect()->back()->with('error', 'Tidak ada data presensi yang dikirimkan.');
        }

        $kelas = $this->kelasModel->find($kelasId);
        if (!$kelas) {
            return redirect()->back()->with('error', 'Kelas tidak ditemukan.');
        }

        // Proteksi Hari Libur & Hari Minggu: Siswa / PJ tidak boleh mengisi presensi
        $kalenderModel    = new \App\Models\KalenderPendidikanModel();
        $tingkat          = $this->absensiModel->getTingkatKelas($kelas['nama_kelas']);
        $agendaBebasAbsen = $kalenderModel->isHariBebasAbsen($tanggal, $tingkat, $kelasId);
        $isSunday         = ((int)date('w', strtotime($tanggal)) === 0);

        if ($isSunday || !empty($agendaBebasAbsen)) {
            $reason = $isSunday ? 'Hari Minggu adalah hari libur akhir pekan.' : ('Hari ini adalah agenda ' . esc($agendaBebasAbsen['nama_agenda']) . ' (Bebas KBM).');
            return redirect()->back()->with('error', 'Pengisian Presensi Ditolak: ' . $reason . ' Siswa tidak diperkenankan mengisi presensi pada hari libur.');
        }

        // Ambil daftar valid siswa_id aktif pada kelas ini (Security Data Isolation)
        $validSiswaList = array_map('intval', $this->siswaModel->where('kelas_id', $kelasId)->where('status', 'Aktif')->findColumn('id') ?: []);

        // Status yang diizinkan (tambahkan P jika Kelas 2 / XI)
        $allowedStatuses = ['H', 'S', 'I', 'A'];
        if ($this->absensiModel->isKelas2($kelas['nama_kelas'])) {
            $allowedStatuses[] = 'P';
        }

        // Validasi: pastikan tidak ada siswa_id dari luar kelas yang diselundupkan
        $sanitizedData = [];
        foreach ($inputAbsensi as $siswaId => $row) {
            $siswaId = (int)$siswaId;
            if (! in_array($siswaId, $validSiswaList, true)) {
                return redirect()->back()->with('error', 'Akses ditolak: Terdeteksi manipulasi data siswa luar kelas.');
            }

            // Validasi: status kehadiran wajib diisi (tidak boleh kosong)
            if (empty($row['status']) || !in_array($row['status'], $allowedStatuses)) {
                return redirect()->back()->with('error', 'Status kehadiran wajib dipilih untuk semua siswa. Pastikan tidak ada yang kosong.');
            }

            $sanitizedData[$siswaId] = [
                'status'     => $row['status'],
                'keterangan' => !empty($row['keterangan']) ? trim(strip_tags($row['keterangan'])) : null,
            ];
        }

        $success = $this->absensiModel->saveOrUpdateRecords($tanggal, $sanitizedData);

        // Periksa apakah ada foto dokumentasi yang diupload bersamaan
        $fotoFile = $this->request->getFile('foto_kelas');
        if ($fotoFile && $fotoFile->isValid() && ! $fotoFile->hasMoved()) {
            $uploadDir = FCPATH . 'uploads/dokumentasi/';
            if (! is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $ext = $fotoFile->getClientExtension();
            $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
            if (in_array(strtolower($ext), $allowedExts, true)) {
                $newName = 'foto_' . $kelasId . '_' . $tanggal . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
                $fotoFile->move($uploadDir, $newName);

                $sesi = $this->presensiKelasHarianModel->getOrCreateForToday($kelasId, $tanggal);
                $this->presensiKelasHarianModel->update($sesi['id'], [
                    'foto_kelas' => 'uploads/dokumentasi/' . $newName,
                ]);
            }
        }

        if ($success) {
            return redirect()->to('/pj?tanggal=' . $tanggal)->with('success', 'Presensi dan dokumentasi kelas tanggal ' . date('d/m/Y', strtotime($tanggal)) . ' berhasil disimpan!');
        }

        return redirect()->back()->with('error', 'Gagal menyimpan presensi. Silakan coba lagi.');
    }

    /**
     * Kosongkan seluruh data presensi kelas untuk tanggal tertentu
     */
    public function kosongkanAbsen()
    {
        $kelasId = (int)$this->session->get('ref_id');
        $kelas   = $this->kelasModel->find($kelasId);

        if (! $kelas) {
            return redirect()->to('/pj')->with('error', 'Kelas tidak ditemukan.');
        }

        $tanggal = $this->request->getPost('tanggal');
        if (empty($tanggal) || ! strtotime($tanggal)) {
            return redirect()->back()->with('error', 'Tanggal tidak valid.');
        }

        $deletedCount = $this->absensiModel->deletePresensiByDateAndKelas($tanggal, $kelasId);

        return redirect()->to('/pj?tanggal=' . $tanggal)
                         ->with('success', 'Presensi kelas ' . esc($kelas['nama_kelas']) . ' untuk tanggal ' . date('d/m/Y', strtotime($tanggal)) . ' berhasil dikosongkan. ' . ($deletedCount > 0 ? "Sebanyak {$deletedCount} data presensi siswa berhasil dibersihkan dan dikembalikan ke status awal (belum diisi)." : 'Data presensi sudah dalam kondisi kosong.'));
    }

    /**
     * Endpoint khusus upload/ganti foto dokumentasi kelas
     */
    public function uploadFoto()
    {
        $kelasId = (int)$this->session->get('ref_id');
        $tanggal = $this->request->getPost('tanggal') ?: date('Y-m-d');

        $fotoFile = $this->request->getFile('foto_kelas');
        if (! $fotoFile || ! $fotoFile->isValid() || $fotoFile->hasMoved()) {
            return redirect()->back()->with('error', 'File foto tidak valid atau belum dipilih.');
        }

        $ext = strtolower($fotoFile->getClientExtension());
        $allowedExts = ['jpg', 'jpeg', 'png', 'webp'];
        if (! in_array($ext, $allowedExts, true)) {
            return redirect()->back()->with('error', 'Format gambar harus JPG, JPEG, PNG, atau WEBP.');
        }

        $uploadDir = FCPATH . 'uploads/dokumentasi/';
        if (! is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $newName = 'foto_' . $kelasId . '_' . $tanggal . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
        $fotoFile->move($uploadDir, $newName);

        $sesi = $this->presensiKelasHarianModel->getOrCreateForToday($kelasId, $tanggal);
        $this->presensiKelasHarianModel->update($sesi['id'], [
            'foto_kelas' => 'uploads/dokumentasi/' . $newName,
        ]);

        return redirect()->to('/pj?tanggal=' . $tanggal)->with('success', 'Foto dokumentasi kelas berhasil diperbarui!');
    }

    /**
     * Rekap presensi kelas sendiri
     */
    public function rekap()
    {
        $kelasId = (int)$this->session->get('ref_id');
        $kelas   = $this->kelasModel->find($kelasId);
        $periode   = $this->request->getGet('periode') ?: ($this->request->getGet('semester') ? 'semester' : 'bulan');
        $bulan     = (int)($this->request->getGet('bulan') ?: date('n'));
        $tahun     = (int)($this->request->getGet('tahun') ?: date('Y'));
        $semester  = $this->request->getGet('semester') ?: (date('n') >= 7 ? 'ganjil' : 'genap');
        $startDate = $this->request->getGet('start_date') ?: date('Y-m-01');
        $endDate   = $this->request->getGet('end_date') ?: date('Y-m-d');

        [$startDateResolved, $endDateResolved, $labelPeriode] = $this->absensiModel->resolveDateFilter(
            $periode, $bulan, $tahun, $semester, null, $startDate, $endDate
        );
        $rekap = $this->absensiModel->getRekapKelas($kelasId, $startDateResolved, $endDateResolved);

        return view('pj/rekap', [
            'title'        => 'Rekap Presensi Kelas',
            'kelas'        => $kelas,
            'periode'      => $periode,
            'bulan'        => $bulan,
            'tahun'        => $tahun,
            'semester'     => $semester,
            'startDate'    => $startDate,
            'endDate'      => $endDate,
            'labelPeriode' => $labelPeriode,
            'rekap'        => $rekap,
        ]);
    }
}
