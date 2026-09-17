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

        // Kunci secara ketat ke tanggal hari ini untuk PJ Kelas
        $tanggal = date('Y-m-d');

        // Isolation data: hanya ambil siswa milik kelas ini
        $daftarSiswa = $this->absensiModel->getAbsensiByDateAndKelas($tanggal, $kelasId);

        // Ambil atau buat sesi presensi harian kelas hari ini (termasuk share token publik)
        $sesiKelas = $this->presensiKelasHarianModel->getOrCreateForToday($kelasId, $tanggal);

        // Ambil data wali kelas
        $walas = !empty($kelas['walas_id']) ? $this->guruModel->find($kelas['walas_id']) : null;

        // Hitung rekapitulasi kehadiran hari ini
        $countH = 0; $countS = 0; $countI = 0; $countA = 0;
        foreach ($daftarSiswa as $s) {
            $st = $s['status'] ?? 'H';
            if ($st === 'H') $countH++;
            elseif ($st === 'S') $countS++;
            elseif ($st === 'I') $countI++;
            elseif ($st === 'A') $countA++;
        }

        return view('pj/absen_harian', [
            'title'       => 'Input Presensi Harian - ' . $kelas['nama_kelas'],
            'kelas'       => $kelas,
            'walas'       => $walas,
            'tanggal'     => $tanggal,
            'daftarSiswa' => $daftarSiswa,
            'sesiKelas'   => $sesiKelas,
            'countH'      => $countH,
            'countS'      => $countS,
            'countI'      => $countI,
            'countA'      => $countA,
        ]);
    }

    /**
     * Proses simpan absensi dengan validasi isolasi data kelas & opsi upload foto
     */
    public function simpan()
    {
        $kelasId = (int)$this->session->get('ref_id');
        $tanggal = date('Y-m-d');
        $inputAbsensi = $this->request->getPost('absensi') ?: [];

        if (empty($inputAbsensi)) {
            return redirect()->back()->with('error', 'Tidak ada data presensi yang dikirimkan.');
        }

        // Ambil daftar valid siswa_id aktif pada kelas ini (Security Data Isolation)
        $validSiswaList = array_map('intval', $this->siswaModel->where('kelas_id', $kelasId)->where('status', 'Aktif')->findColumn('id') ?: []);

        // Validasi: pastikan tidak ada siswa_id dari luar kelas yang diselundupkan
        $sanitizedData = [];
        foreach ($inputAbsensi as $siswaId => $row) {
            $siswaId = (int)$siswaId;
            if (! in_array($siswaId, $validSiswaList, true)) {
                return redirect()->back()->with('error', 'Akses ditolak: Terdeteksi manipulasi data siswa luar kelas.');
            }

            $sanitizedData[$siswaId] = [
                'status'     => in_array($row['status'] ?? 'H', ['H', 'S', 'I', 'A']) ? $row['status'] : 'H',
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
            return redirect()->to('/pj')->with('success', 'Presensi dan dokumentasi kelas berhasil disimpan!');
        }

        return redirect()->back()->with('error', 'Gagal menyimpan presensi. Silakan coba lagi.');
    }

    /**
     * Endpoint khusus upload/ganti foto dokumentasi kelas
     */
    public function uploadFoto()
    {
        $kelasId = (int)$this->session->get('ref_id');
        $tanggal = date('Y-m-d');

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

        return redirect()->to('/pj')->with('success', 'Foto dokumentasi kelas berhasil diperbarui!');
    }

    /**
     * Rekap presensi kelas sendiri
     */
    public function rekap()
    {
        $kelasId = (int)$this->session->get('ref_id');
        $kelas   = $this->kelasModel->find($kelasId);
        $periode  = $this->request->getGet('periode') ?: ($this->request->getGet('semester') ? 'semester' : 'bulan');
        $bulan    = (int)($this->request->getGet('bulan') ?: date('n'));
        $tahun    = (int)($this->request->getGet('tahun') ?: date('Y'));
        $semester = $this->request->getGet('semester') ?: (date('n') >= 7 ? 'ganjil' : 'genap');

        [$startDate, $endDate, $labelPeriode] = $this->absensiModel->resolveDateFilter($periode, $bulan, $tahun, $semester);
        $rekap = $this->absensiModel->getRekapKelas($kelasId, $startDate, $endDate);

        return view('pj/rekap', [
            'title'        => 'Rekap Presensi Kelas',
            'kelas'        => $kelas,
            'periode'      => $periode,
            'bulan'        => $bulan,
            'tahun'        => $tahun,
            'semester'     => $semester,
            'labelPeriode' => $labelPeriode,
            'rekap'        => $rekap,
        ]);
    }
}
