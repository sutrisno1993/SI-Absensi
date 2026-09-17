<?php

namespace App\Controllers;

use App\Models\GuruModel;
use App\Models\KelasModel;
use App\Models\SiswaModel;
use App\Models\AbsensiModel;
use App\Models\GuruJadwalModel;
use App\Models\AbsensiGuruModel;
use App\Models\KeterlambatanSiswaModel;
use App\Models\PiketPetugasModel;
use App\Models\PembinaanModel;
use App\Services\ExportService;

class PiketController extends BaseController
{
    protected $guruModel;
    protected $kelasModel;
    protected $siswaModel;
    protected $absensiModel;
    protected $guruJadwalModel;
    protected $absensiGuruModel;
    protected $keterlambatanSiswaModel;
    protected $piketPetugasModel;
    protected $pembinaanModel;
    protected $exportService;

    public function __construct()
    {
        $this->guruModel               = new GuruModel();
        $this->kelasModel              = new KelasModel();
        $this->siswaModel              = new SiswaModel();
        $this->absensiModel            = new AbsensiModel();
        $this->guruJadwalModel         = new GuruJadwalModel();
        $this->absensiGuruModel        = new AbsensiGuruModel();
        $this->keterlambatanSiswaModel = new KeterlambatanSiswaModel();
        $this->piketPetugasModel       = new PiketPetugasModel();
        $this->pembinaanModel          = new PembinaanModel();
        $this->exportService           = new ExportService();
    }

    /**
     * Konversi tanggal Y-m-d ke nama hari bahasa Indonesia
     */
    protected function getNamaHari(string $date): string
    {
        $dayOfWeek = date('w', strtotime($date));
        $daftarHari = [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];
        return $daftarHari[$dayOfWeek] ?? 'Senin';
    }

    /**
     * Dashboard Utama Guru Piket
     */
    public function index()
    {
        $today = date('Y-m-d');
        $hari  = $this->getNamaHari($today);
        $currentHour = (int)date('H');
        $activeShift = ($currentHour >= 12) ? 'Siang' : 'Pagi';

        // Petugas piket hari ini
        $petugasPagi  = $this->piketPetugasModel->getPetugasByHariAndShift($hari, 'Pagi');
        $petugasSiang = $this->piketPetugasModel->getPetugasByHariAndShift($hari, 'Siang');

        // Statistik hari ini
        $terlambatHariIni = $this->keterlambatanSiswaModel->where('tanggal', $today)->countAllResults();
        
        $absenGuruPagi  = $this->absensiGuruModel->where('tanggal', $today)->where('shift', 'Pagi')->where('status', 'H')->findAll();
        $absenGuruSiang = $this->absensiGuruModel->where('tanggal', $today)->where('shift', 'Siang')->where('status', 'H')->findAll();

        $guruWajibPagi  = $this->guruJadwalModel->getGuruByHariAndShift($hari, 'Pagi');
        $guruWajibSiang = $this->guruJadwalModel->getGuruByHariAndShift($hari, 'Siang');

        // 5 Siswa terlambat terakhir hari ini
        $recentTerlambat = $this->keterlambatanSiswaModel->getTerlambatList($today);
        $recentTerlambat = array_slice($recentTerlambat, 0, 5);

        return view('piket/dashboard', [
            'title'            => 'Portal Operasional Guru Piket',
            'today'            => $today,
            'hari'             => $hari,
            'activeShift'      => $activeShift,
            'petugasPagi'      => $petugasPagi,
            'petugasSiang'     => $petugasSiang,
            'terlambatHariIni' => $terlambatHariIni,
            'countGuruPagi'    => count($absenGuruPagi),
            'wajibGuruPagi'    => count($guruWajibPagi),
            'countGuruSiang'   => count($absenGuruSiang),
            'wajibGuruSiang'   => count($guruWajibSiang),
            'recentTerlambat'  => $recentTerlambat,
        ]);
    }

    /**
     * Presensi Kedatangan Guru (Berdasarkan Hari dan Shift)
     */
    public function guruAbsen()
    {
        $tanggal = $this->request->getGet('tanggal') ?: date('Y-m-d');
        $shift   = $this->request->getGet('shift');

        if (empty($shift)) {
            $shift = ((int)date('H') >= 12) ? 'Siang' : 'Pagi';
        }

        $hari = $this->getNamaHari($tanggal);

        // 1. Ambil guru yang MEMILIKI JAM MENGAJAR pada hari dan shift ini
        $guruTerjadwal = $this->guruJadwalModel->getGuruByHariAndShift($hari, $shift);

        // 2. Ambil seluruh data presensi yang sudah tercatat untuk tanggal & shift ini
        $absensiTercatat = $this->absensiGuruModel->getAbsensiByTanggalAndShift($tanggal, $shift);
        $absenMap = [];
        foreach ($absensiTercatat as $ab) {
            $absenMap[$ab['guru_id']] = $ab;
        }

        // 3. Ambil guru yang TIDAK MEMILIKI JAM MENGAJAR pada shift ini (sebagai informasi transparansi)
        $semuaGuru = $this->guruModel->orderBy('nama_guru', 'ASC')->findAll();
        $idTerjadwal = array_column($guruTerjadwal, 'id');
        $guruTanpaJam = [];
        foreach ($semuaGuru as $sg) {
            if (! in_array($sg['id'], $idTerjadwal)) {
                $guruTanpaJam[] = $sg;
            }
        }

        $pengaturanModel = new \App\Models\PengaturanModel();
        $jamMasukPagi  = $pengaturanModel->getSetting('jam_masuk_pagi', '06:30');
        $jamMasukSiang = $pengaturanModel->getSetting('jam_masuk_siang', '12:30');

        return view('piket/guru_absen', [
            'title'          => 'Presensi Kedatangan Guru',
            'tanggal'        => $tanggal,
            'shift'          => $shift,
            'hari'           => $hari,
            'guruTerjadwal'  => $guruTerjadwal,
            'guruTanpaJam'   => $guruTanpaJam,
            'absenMap'       => $absenMap,
            'jamMasukPagi'   => $jamMasukPagi,
            'jamMasukSiang'  => $jamMasukSiang,
        ]);
    }

    /**
     * Simpan Presensi Kedatangan Guru
     */
    public function simpanAbsensiGuru()
    {
        $tanggal = $this->request->getPost('tanggal') ?: date('Y-m-d');
        $shift   = $this->request->getPost('shift') ?: 'Pagi';
        $hari    = $this->getNamaHari($tanggal);
        $dataGuru = $this->request->getPost('guru') ?: [];

        $userId = session()->get('user_id');

        $this->absensiGuruModel->simpanAbsenBatch($tanggal, $hari, $shift, $dataGuru, $userId);

        return redirect()->to("/piket/guru-absen?tanggal={$tanggal}&shift={$shift}")
                         ->with('success', "Presensi guru untuk hari {$hari}, {$tanggal} Shift {$shift} berhasil disimpan!");
    }

    /**
     * Halaman Input & Log Keterlambatan Siswa
     */
    public function siswaTerlambat()
    {
        $tanggal = $this->request->getGet('tanggal') ?: date('Y-m-d');
        $shift   = $this->request->getGet('shift') ?: (((int)date('H') >= 12) ? 'Siang' : 'Pagi');

        $terlambatList = $this->keterlambatanSiswaModel->getTerlambatList($tanggal);
        $kelasList     = $this->kelasModel->orderBy('nama_kelas', 'ASC')->findAll();
        $siswaList     = $this->siswaModel->select('siswa.*, kelas.nama_kelas')
                                          ->join('kelas', 'kelas.id = siswa.kelas_id')
                                          ->where('siswa.status', 'Aktif')
                                          ->orderBy('siswa.nama_siswa', 'ASC')
                                          ->findAll();

        return view('piket/siswa_terlambat', [
            'title'         => 'Pencatatan Siswa Terlambat',
            'tanggal'       => $tanggal,
            'shift'         => $shift,
            'terlambatList' => $terlambatList,
            'kelasList'     => $kelasList,
            'siswaList'     => $siswaList,
        ]);
    }

    /**
     * Simpan Catatan Siswa Terlambat (Auto-Sync ke Presensi Harian Siswa)
     */
    public function simpanSiswaTerlambat()
    {
        $siswaId  = $this->request->getPost('siswa_id');
        $tanggal  = $this->request->getPost('tanggal') ?: date('Y-m-d');
        $shift    = $this->request->getPost('shift') ?: 'Pagi';
        $jamMasuk = $this->request->getPost('jam_masuk') ?: date('H:i:s');
        $alasan   = $this->request->getPost('alasan');
        $tindakan = $this->request->getPost('tindakan');

        if (empty($siswaId)) {
            return redirect()->back()->withInput()->with('error', 'Silakan pilih siswa yang terlambat.');
        }

        $userId = session()->get('user_id');

        $this->keterlambatanSiswaModel->catatKeterlambatan([
            'tanggal'      => $tanggal,
            'siswa_id'     => (int)$siswaId,
            'shift'        => $shift,
            'jam_masuk'    => $jamMasuk,
            'alasan'       => $alasan,
            'tindakan'     => $tindakan,
            'dicatat_oleh' => $userId,
        ]);

        return redirect()->to("/piket/siswa-terlambat?tanggal={$tanggal}&shift={$shift}")
                         ->with('success', 'Data keterlambatan berhasil dicatat dan status presensi harian siswa otomatis disinkronkan menjadi Hadir (Terlambat).');
    }

    /**
     * Hapus Catatan Keterlambatan Siswa
     */
    public function hapusSiswaTerlambat($id)
    {
        $this->keterlambatanSiswaModel->delete($id);
        return redirect()->back()->with('success', 'Catatan keterlambatan berhasil dihapus.');
    }

    /**
     * Rekapitulasi Data Piket (Guru & Siswa)
     */
    public function rekap()
    {
        $startDate = $this->request->getGet('start_date') ?: date('Y-m-01');
        $endDate   = $this->request->getGet('end_date') ?: date('Y-m-d');

        // Rekap Siswa Terlambat
        $db = \Config\Database::connect();
        $builder = $db->table('keterlambatan_siswa ks')
                      ->select('ks.*, s.nama_siswa, s.nisn, k.nama_kelas')
                      ->join('siswa s', 's.id = ks.siswa_id')
                      ->join('kelas k', 'k.id = s.kelas_id', 'left')
                      ->where('ks.tanggal >=', $startDate)
                      ->where('ks.tanggal <=', $endDate)
                      ->orderBy('ks.tanggal', 'DESC')
                      ->orderBy('ks.jam_masuk', 'DESC');
        $rekapTerlambat = $builder->get()->getResultArray();

        // Rekap Presensi Guru
        $builderGuru = $db->table('absensi_guru ag')
                          ->select('ag.*, g.nama_guru, g.nip')
                          ->join('guru g', 'g.id = ag.guru_id')
                          ->where('ag.tanggal >=', $startDate)
                          ->where('ag.tanggal <=', $endDate)
                          ->orderBy('ag.tanggal', 'DESC')
                          ->orderBy('g.nama_guru', 'ASC');
        $rekapGuru = $builderGuru->get()->getResultArray();

        return view('piket/rekap', [
            'title'          => 'Rekapitulasi Layanan Guru Piket',
            'startDate'      => $startDate,
            'endDate'        => $endDate,
            'rekapTerlambat' => $rekapTerlambat,
            'rekapGuru'      => $rekapGuru,
        ]);
    }

    /**
     * Unduh Laporan Rekapitulasi Piket dalam Format Excel (.xlsx)
     */
    public function exportRekapPiketExcel()
    {
        $startDate = $this->request->getGet('start_date') ?: date('Y-m-01');
        $endDate   = $this->request->getGet('end_date') ?: date('Y-m-d');

        $pembinaanList = $this->pembinaanModel->select('pembinaan.*, s.nama_siswa, s.nisn, k.nama_kelas')
                                              ->join('siswa s', 's.id = pembinaan.siswa_id')
                                              ->join('kelas k', 'k.id = s.kelas_id', 'left')
                                              ->where('pembinaan.tanggal_tindakan >=', $startDate)
                                              ->where('pembinaan.tanggal_tindakan <=', $endDate)
                                              ->orderBy('pembinaan.tanggal_tindakan', 'DESC')
                                              ->findAll();

        $terlambatList = $this->keterlambatanSiswaModel->getRekapPeriode($startDate, $endDate);

        $this->exportService->exportPenindakanSiswa($pembinaanList, $terlambatList);
    }
}
