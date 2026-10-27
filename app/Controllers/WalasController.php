<?php

namespace App\Controllers;

use App\Models\AbsensiModel;
use App\Models\KelasModel;
use App\Models\PembinaanModel;
use App\Models\SiswaModel;
use App\Models\KeterlambatanSiswaModel;
use App\Services\ExportService;

class WalasController extends BaseController
{
    protected $absensiModel;
    protected $kelasModel;
    protected $pembinaanModel;
    protected $siswaModel;
    protected $keterlambatanSiswaModel;
    protected $exportService;

    public function __construct()
    {
        $this->absensiModel            = new AbsensiModel();
        $this->kelasModel              = new KelasModel();
        $this->pembinaanModel          = new PembinaanModel();
        $this->siswaModel              = new SiswaModel();
        $this->keterlambatanSiswaModel = new KeterlambatanSiswaModel();
        $this->exportService           = new ExportService();
    }

    /**
     * Dashboard Wali Kelas
     */
    public function index()
    {
        $walasId = (int)$this->session->get('ref_id');
        $kelas   = $this->kelasModel->getKelasByWalas($walasId);

        if (! $kelas) {
            return view('walas/no_kelas', [
                'title' => 'Dashboard Wali Kelas',
            ]);
        }

        $kelasId = (int)$kelas['id'];

        // 1. Siswa yang memicu alert pembinaan (Alpa > 3)
        $siswaBermasalahAlpa = $this->absensiModel->getSiswaPerluPembinaan($kelasId, 3);

        // 2. Alert Keterlambatan: Siswa Terlambat > 3x (Wajib Pembinaan) & Terlambat > 1x (Dilaporkan ke Walas)
        $rekapTerlambatBulanIni = $this->keterlambatanSiswaModel->getRekapKeterlambatanKelas($kelasId, date('Y-m-01'), date('Y-m-t'));
        $siswaTerlambatBina  = array_values(array_filter($rekapTerlambatBulanIni, static fn($r) => (int)$r['total_terlambat'] > 3));
        $siswaTerlambatLapor = array_values(array_filter($rekapTerlambatBulanIni, static fn($r) => (int)$r['total_terlambat'] > 1 && (int)$r['total_terlambat'] <= 3));

        // 3. Rekap absensi kelas bulan ini
        $bulanSekarang = date('m');
        $tahunSekarang = date('Y');
        $rekapBulanIni = $this->absensiModel->getRekapKelas($kelasId, $bulanSekarang, $tahunSekarang);

        // 4. Riwayat pembinaan terakhir
        $riwayatPembinaan = $this->pembinaanModel->getRiwayatPembinaan($walasId, $kelasId);

        // 5. Daftar seluruh siswa aktif untuk dropdown form bina siswa
        $daftarSiswa = $this->siswaModel->getSiswaAktifByKelas($kelasId);

        // 6. Cek hari aktif belajar yang kelewat / belum diinput presensinya
        $tanggalKelewat = $this->absensiModel->getTanggalKelewatKelas($kelasId);

        return view('walas/dashboard', [
            'title'                 => 'Dashboard Wali Kelas',
            'kelas'                 => $kelas,
            'siswaBermasalah'       => $siswaBermasalahAlpa,
            'siswaTerlambatBina'    => $siswaTerlambatBina,
            'siswaTerlambatLapor'   => $siswaTerlambatLapor,
            'rekapBulanIni'         => $rekapBulanIni,
            'riwayatPembinaan'      => $riwayatPembinaan,
            'daftarSiswa'           => $daftarSiswa,
            'tanggalKelewat'        => $tanggalKelewat,
        ]);
    }

    /**
     * Halaman Input / Edit Presensi Wali Kelas (Mendukung Presensi Kelewat / Past Dates)
     */
    public function absen()
    {
        $walasId = (int)$this->session->get('ref_id');
        $kelas   = $this->kelasModel->getKelasByWalas($walasId);

        if (! $kelas) {
            return view('walas/no_kelas', [
                'title' => 'Input Presensi Kelas',
            ]);
        }

        $kelasId = (int)$kelas['id'];
        $tanggal = $this->request->getGet('tanggal') ?: date('Y-m-d');

        if (! strtotime($tanggal)) {
            $tanggal = date('Y-m-d');
        }

        $daftarSiswa = $this->absensiModel->getAbsensiByDateAndKelas($tanggal, $kelasId);
        $isKelas2    = $this->absensiModel->isKelas2($kelas['nama_kelas']);

        $kalenderModel    = new \App\Models\KalenderPendidikanModel();
        $tingkat          = $this->absensiModel->getTingkatKelas($kelas['nama_kelas']);
        $agendaBebasAbsen = $kalenderModel->isHariBebasAbsen($tanggal, $tingkat, $kelasId);
        $isSunday         = ((int)date('w', strtotime($tanggal)) === 0);

        $hasExistingRecords = !empty(array_filter($daftarSiswa, static fn($s) => !empty($s['absensi_id'])));
        $kalenderEvents     = $kalenderModel->getEventsForPicker($tingkat, $kelasId);

        return view('walas/absen_harian', [
            'title'              => 'Input Presensi Kelas ' . $kelas['nama_kelas'],
            'kelas'              => $kelas,
            'tanggal'            => $tanggal,
            'daftarSiswa'        => $daftarSiswa,
            'isKelas2'           => $isKelas2,
            'agendaBebasAbsen'   => $agendaBebasAbsen,
            'isSunday'           => $isSunday,
            'hasExistingRecords' => $hasExistingRecords,
            'kalenderEvents'     => $kalenderEvents,
        ]);
    }

    /**
     * Proses Simpan / Edit Presensi oleh Wali Kelas
     */
    public function simpanAbsen()
    {
        $walasId = (int)$this->session->get('ref_id');
        $kelas   = $this->kelasModel->getKelasByWalas($walasId);

        if (! $kelas) {
            return redirect()->to('/walas')->with('error', 'Anda tidak memiliki kelas ampu.');
        }

        $kelasId = (int)$kelas['id'];
        $tanggal = $this->request->getPost('tanggal') ?: date('Y-m-d');
        $inputAbsensi = $this->request->getPost('absensi') ?: [];

        if (empty($inputAbsensi)) {
            return redirect()->back()->with('error', 'Tidak ada data presensi yang dikirimkan.');
        }

        // Isolasi data: pastikan siswa aktif milik kelas wali kelas ini
        $validSiswaList = array_map('intval', $this->siswaModel->where('kelas_id', $kelasId)->where('status', 'Aktif')->findColumn('id') ?: []);

        $sanitizedData = [];
        foreach ($inputAbsensi as $siswaId => $row) {
            $siswaId = (int)$siswaId;
            if (! in_array($siswaId, $validSiswaList, true)) {
                return redirect()->back()->with('error', 'Akses ditolak: Terdeteksi manipulasi data siswa.');
            }

            // Validasi: status kehadiran wajib diisi (tidak boleh null/kosong)
            if (empty($row['status']) || !in_array($row['status'], ['H', 'S', 'I', 'A', 'P'])) {
                return redirect()->back()->with('error', 'Status kehadiran wajib dipilih untuk semua siswa. Pastikan tidak ada yang kosong.');
            }

            $sanitizedData[$siswaId] = [
                'status'     => $row['status'],
                'keterangan' => !empty($row['keterangan']) ? trim(strip_tags($row['keterangan'])) : null,
            ];
        }

        $success = $this->absensiModel->saveOrUpdateRecords($tanggal, $sanitizedData);

        if ($success) {
            return redirect()->to('/walas/absen?tanggal=' . $tanggal)->with('success', 'Presensi tanggal ' . date('d/m/Y', strtotime($tanggal)) . ' berhasil disimpan!');
        }

        return redirect()->back()->with('error', 'Gagal menyimpan presensi. Silakan coba lagi.');
    }

    /**
     * Kosongkan seluruh data presensi kelas untuk tanggal tertentu
     */
    public function hapusPresensiTanggal()
    {
        $walasId = (int)$this->session->get('ref_id');
        $kelas   = $this->kelasModel->getKelasByWalas($walasId);

        if (! $kelas) {
            return redirect()->to('/walas')->with('error', 'Anda tidak memiliki kelas ampu.');
        }

        $kelasId = (int)$kelas['id'];
        $tanggal = $this->request->getPost('tanggal');

        if (empty($tanggal) || ! strtotime($tanggal)) {
            return redirect()->back()->with('error', 'Tanggal tidak valid.');
        }

        $deletedCount = $this->absensiModel->deletePresensiByDateAndKelas($tanggal, $kelasId);

        return redirect()->to('/walas/absen?tanggal=' . $tanggal)->with('success', 'Presensi kelas ' . esc($kelas['nama_kelas']) . ' untuk tanggal ' . date('d/m/Y', strtotime($tanggal)) . ' berhasil dikosongkan. ' . ($deletedCount > 0 ? "Sebanyak {$deletedCount} data presensi siswa berhasil dibersihkan dan dikembalikan ke status awal (belum diisi)." : 'Data presensi sudah dalam kondisi kosong.'));
    }

    /**
     * Halaman Rekapitulasi Presensi Kelas
     */
    public function rekap()
    {
        $walasId = (int)$this->session->get('ref_id');
        $kelas   = $this->kelasModel->getKelasByWalas($walasId);

        if (! $kelas) {
            return redirect()->to('/walas')->with('error', 'Anda belum ditugaskan mengampu kelas.');
        }

        $periode   = $this->request->getGet('periode') ?: ($this->request->getGet('semester') ? 'semester' : 'bulan');
        $bulan     = (int)($this->request->getGet('bulan') ?: date('n'));
        $tahun     = (int)($this->request->getGet('tahun') ?: date('Y'));
        $semester  = $this->request->getGet('semester') ?: (date('n') >= 7 ? 'ganjil' : 'genap');
        $startDate = $this->request->getGet('start_date') ?: date('Y-m-01');
        $endDate   = $this->request->getGet('end_date') ?: date('Y-m-d');

        [$startDateResolved, $endDateResolved, $labelPeriode] = $this->absensiModel->resolveDateFilter(
            $periode, $bulan, $tahun, $semester, null, $startDate, $endDate
        );
        $rekap = $this->absensiModel->getRekapKelas((int)$kelas['id'], $startDateResolved, $endDateResolved);

        return view('walas/rekap', [
            'title'        => 'Rekap Presensi Kelas ' . $kelas['nama_kelas'],
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

    /**
     * Simpan tindakan pembinaan siswa beserta upload bukti berkas
     */
    public function simpanPembinaan()
    {
        $walasId = (int)$this->session->get('ref_id');
        $kelas   = $this->kelasModel->getKelasByWalas($walasId);

        if (! $kelas) {
            return redirect()->to('/walas')->with('error', 'Anda tidak memiliki kelas ampunan.');
        }

        $rules = [
            'siswa_id' => [
                'rules'  => 'required|is_natural_no_zero',
                'errors' => ['required' => 'Siswa yang dibina wajib dipilih.'],
            ],
            'tanggal_tindakan' => [
                'rules'  => 'required|valid_date',
                'errors' => ['required' => 'Tanggal tindakan wajib diisi.'],
            ],
            'jenis_tindakan' => [
                'rules'  => 'required',
                'errors' => ['required' => 'Jenis tindakan pembinaan wajib dipilih.'],
            ],
            'catatan_pembinaan' => [
                'rules'  => 'required',
                'errors' => ['required' => 'Catatan pembinaan wajib diisi.'],
            ],
            'file_bukti' => [
                'rules'  => 'permit_empty|uploaded[file_bukti]|max_size[file_bukti,2048]|ext_in[file_bukti,jpg,jpeg,png,pdf]',
                'errors' => [
                    'max_size' => 'Ukuran berkas bukti maksimal 2MB.',
                    'ext_in'   => 'Format berkas bukti harus JPG, JPEG, PNG, atau PDF.',
                ],
            ],
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $siswaId = (int)$this->request->getPost('siswa_id');

        // Pastikan siswa yang dipilih berada di kelas walas ini
        $siswa = $this->siswaModel->where('id', $siswaId)->where('kelas_id', $kelas['id'])->first();
        if (! $siswa) {
            return redirect()->back()->withInput()->with('error', 'Siswa yang dipilih bukan anggota kelas yang Anda ampu.');
        }

        // Penanganan upload berkas bukti
        $fileBukti = $this->request->getFile('file_bukti');
        $namaFileBukti = null;

        if ($fileBukti && $fileBukti->isValid() && ! $fileBukti->hasMoved()) {
            $namaFileBukti = $fileBukti->getRandomName();
            $fileBukti->move(FCPATH . 'uploads/pembinaan', $namaFileBukti);
        }

        $data = [
            'tanggal_tindakan'  => $this->request->getPost('tanggal_tindakan'),
            'siswa_id'          => $siswaId,
            'walas_id'          => $walasId,
            'jenis_tindakan'    => $this->request->getPost('jenis_tindakan'),
            'catatan_pembinaan' => trim($this->request->getPost('catatan_pembinaan')),
            'file_bukti'        => $namaFileBukti,
        ];

        $this->pembinaanModel->insert($data);

        return redirect()->to('/walas')->with('success', 'Data pembinaan untuk siswa ' . $siswa['nama_siswa'] . ' berhasil disimpan!');
    }

    /**
     * Unduh Laporan Rekap Presensi Kelas dalam Format Excel (.xlsx)
     */
    public function exportRekapExcel()
    {
        $walasId = (int)$this->session->get('ref_id');
        $kelas   = $this->kelasModel->getKelasByWalas($walasId);

        if (! $kelas) {
            return redirect()->to('/walas')->with('error', 'Anda tidak memiliki kelas ampu.');
        }

        $periode   = $this->request->getGet('periode') ?: ($this->request->getGet('semester') ? 'semester' : ($this->request->getGet('bulan') ? 'bulan' : 'all'));
        $bulan     = (int)($this->request->getGet('bulan') ?: date('n'));
        $tahun     = (int)($this->request->getGet('tahun') ?: date('Y'));
        $semester  = $this->request->getGet('semester') ?: (date('n') >= 7 ? 'ganjil' : 'genap');
        $startDate = $this->request->getGet('start_date') ?: date('Y-m-01');
        $endDate   = $this->request->getGet('end_date') ?: date('Y-m-d');

        [$startDateResolved, $endDateResolved, $labelPeriode] = $this->absensiModel->resolveDateFilter(
            $periode, $bulan, $tahun, $semester, null, $startDate, $endDate
        );
        $rekapSiswa = $this->absensiModel->getRekapKelas((int)$kelas['id'], $startDateResolved, $endDateResolved);
        $this->exportService->exportKelasPresensi($kelas, $rekapSiswa, $labelPeriode);
    }

    /**
     * Manajemen Akun Login Siswa Rombel Kelas
     */
    public function akunSiswa()
    {
        $walasId = (int)$this->session->get('ref_id');
        $kelas   = $this->kelasModel->getKelasByWalas($walasId);

        if (! $kelas) {
            return view('walas/no_kelas', [
                'title' => 'Akun Perwakilan Kelas',
            ]);
        }

        $kelasId = (int)$kelas['id'];
        $userModel = new \App\Models\UserModel();

        // Ambil akun khusus perwakilan kelas (role = pj_kelas, ref_id = kelas_id)
        $akunKelas = $userModel->where('role', 'pj_kelas')
                               ->where('ref_id', $kelasId)
                               ->first();

        // Saran username default berdasarkan nama kelas
        $slugKelas = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', trim($kelas['nama_kelas'])));
        $suggestedUsername = 'pj_' . trim($slugKelas, '_');

        // Daftar nama siswa rombel sebagai referensi
        $siswaList = $this->siswaModel->where('kelas_id', $kelasId)
                                      ->orderBy('nama_siswa', 'ASC')
                                      ->findAll();

        return view('walas/akun_siswa', [
            'title'             => 'Akun Perwakilan Kelas - ' . $kelas['nama_kelas'],
            'kelas'             => $kelas,
            'akunKelas'         => $akunKelas,
            'suggestedUsername' => $suggestedUsername,
            'siswaList'         => $siswaList,
        ]);
    }

    /**
     * Generate atau Perbarui Akun Khusus Perwakilan Kelas
     */
    public function generateAkunKelas()
    {
        $walasId = (int)$this->session->get('ref_id');
        $kelas   = $this->kelasModel->getKelasByWalas($walasId);

        if (! $kelas) {
            return redirect()->back()->with('error', 'Akses ditolak. Anda belum dipetakan ke kelas.');
        }

        $kelasId  = (int)$kelas['id'];
        $username = trim($this->request->getPost('username') ?: '');
        $password = trim($this->request->getPost('password') ?: '');

        // Jika username kosong, gunakan format standar pj_[nama_kelas]
        if (empty($username)) {
            $slugKelas = strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', trim($kelas['nama_kelas'])));
            $username = 'pj_' . trim($slugKelas, '_');
        }

        // Jika password kosong, generate password acak 6 digit
        if (empty($password)) {
            $password = (string)random_int(100000, 999999);
        }

        $userModel = new \App\Models\UserModel();

        // Cek apakah username sudah dipakai oleh user LAIN di luar akun kelas ini
        $existingByUsername = $userModel->where('username', $username)->first();

        // Akun kelas saat ini
        $currentClassAccount = $userModel->where('role', 'pj_kelas')
                                         ->where('ref_id', $kelasId)
                                         ->first();

        if ($existingByUsername && (! $currentClassAccount || $existingByUsername['id'] !== $currentClassAccount['id'])) {
            return redirect()->back()->withInput()->with('error', "Username '{$username}' sudah digunakan akun lain. Silakan pilih username lain.");
        }

        if ($currentClassAccount) {
            $userModel->update($currentClassAccount['id'], [
                'username'       => $username,
                'password_hash'  => password_hash($password, PASSWORD_BCRYPT),
                'is_first_login' => 0,
            ]);
            $pesan = "Akun Perwakilan Kelas {$kelas['nama_kelas']} berhasil diperbarui!";
        } else {
            $userModel->insert([
                'username'       => $username,
                'password_hash'  => password_hash($password, PASSWORD_BCRYPT),
                'role'           => 'pj_kelas',
                'ref_id'         => $kelasId,
                'is_first_login' => 0,
            ]);
            $pesan = "Akun Perwakilan Kelas {$kelas['nama_kelas']} berhasil dibuat!";
        }

        // Simpan info kredensial di session flashdata agar wali kelas bisa langsung menyalin
        session()->setFlashdata('last_generated_username', $username);
        session()->setFlashdata('last_generated_password', $password);

        return redirect()->to('/walas/akun-siswa')->with('success', $pesan);
    }

    /**
     * Reset Password Akun Khusus Perwakilan Kelas
     */
    public function resetPasswordAkunKelas()
    {
        $walasId = (int)$this->session->get('ref_id');
        $kelas   = $this->kelasModel->getKelasByWalas($walasId);

        if (! $kelas) {
            return redirect()->back()->with('error', 'Akses ditolak. Anda belum dipetakan ke kelas.');
        }

        $kelasId = (int)$kelas['id'];
        $userModel = new \App\Models\UserModel();

        $akunKelas = $userModel->where('role', 'pj_kelas')
                               ->where('ref_id', $kelasId)
                               ->first();

        if (! $akunKelas) {
            return redirect()->back()->with('error', 'Akun perwakilan kelas belum dibuat. Silakan klik tombol Buat Akun.');
        }

        // Buat password acak 6 digit
        $newPassword = (string)random_int(100000, 999999);

        $userModel->update($akunKelas['id'], [
            'password_hash'  => password_hash($newPassword, PASSWORD_BCRYPT),
            'is_first_login' => 0,
        ]);

        session()->setFlashdata('last_generated_username', $akunKelas['username']);
        session()->setFlashdata('last_generated_password', $newPassword);

        return redirect()->to('/walas/akun-siswa')->with('success', "Password akun perwakilan kelas berhasil direset ke: {$newPassword}");
    }

    /**
     * Reset Password Siswa Individual ke NISN (Legacy/Fallback)
     */
    public function resetPasswordSiswa($siswaId)
    {
        $walasId = (int)$this->session->get('ref_id');
        $kelas   = $this->kelasModel->getKelasByWalas($walasId);

        if (! $kelas) {
            return redirect()->back()->with('error', 'Akses ditolak. Anda belum dipetakan ke kelas.');
        }

        $siswa = $this->siswaModel->where('id', (int)$siswaId)
                                  ->where('kelas_id', (int)$kelas['id'])
                                  ->first();

        if (! $siswa) {
            return redirect()->back()->with('error', 'Data siswa tidak ditemukan di kelas Anda.');
        }

        $userModel = new \App\Models\UserModel();
        $existingUser = $userModel->where('username', $siswa['nisn'])->first();

        if ($existingUser) {
            $userModel->update($existingUser['id'], [
                'password_hash'  => password_hash($siswa['nisn'], PASSWORD_BCRYPT),
                'is_first_login' => 0,
            ]);
        } else {
            $userModel->insert([
                'username'       => $siswa['nisn'],
                'password_hash'  => password_hash($siswa['nisn'], PASSWORD_BCRYPT),
                'role'           => 'pj_kelas',
                'ref_id'         => (int)$kelas['id'],
                'is_first_login' => 0,
            ]);
        }

        return redirect()->back()->with('success', "Password akun siswa \"{$siswa['nama_siswa']}\" berhasil direset ke nomor NISN: {$siswa['nisn']}");
    }

    /**
     * Reset Semua Password Siswa Kelas ke NISN masing-masing (Legacy/Fallback)
     */
    public function resetSemuaPassword()
    {
        $walasId = (int)$this->session->get('ref_id');
        $kelas   = $this->kelasModel->getKelasByWalas($walasId);

        if (! $kelas) {
            return redirect()->back()->with('error', 'Akses ditolak. Anda belum dipetakan ke kelas.');
        }

        $siswaList = $this->siswaModel->where('kelas_id', (int)$kelas['id'])->findAll();
        $userModel = new \App\Models\UserModel();
        $count = 0;

        foreach ($siswaList as $s) {
            $existingUser = $userModel->where('username', $s['nisn'])->first();
            if ($existingUser) {
                $userModel->update($existingUser['id'], [
                    'password_hash'  => password_hash($s['nisn'], PASSWORD_BCRYPT),
                    'is_first_login' => 0,
                ]);
            } else {
                $userModel->insert([
                    'username'       => $s['nisn'],
                    'password_hash'  => password_hash($s['nisn'], PASSWORD_BCRYPT),
                    'role'           => 'pj_kelas',
                    'ref_id'         => (int)$kelas['id'],
                    'is_first_login' => 0,
                ]);
            }
            $count++;
        }

        return redirect()->back()->with('success', "Seluruh password ($count siswa) kelas {$kelas['nama_kelas']} berhasil direset ke nomor NISN masing-masing.");
    }

    /**
     * Tandai hari tertentu sebagai Hari Libur / Acara Khusus oleh Wali Kelas
     * (Menghilangkan notifikasi kelewat dan mencatat alasan resmi libur/acara)
     */
    public function tandaiLiburAcara()
    {
        $walasId = (int)$this->session->get('ref_id');
        $kelas   = $this->kelasModel->getKelasByWalas($walasId);

        if (! $kelas) {
            return redirect()->to('/walas')->with('error', 'Anda tidak memiliki kelas ampu.');
        }

        $tanggal    = $this->request->getPost('tanggal');
        $namaAgenda = trim($this->request->getPost('nama_agenda') ?? '');
        $tipe       = $this->request->getPost('tipe') ?: 'libur';
        $cakupan    = $this->request->getPost('cakupan') ?: 'kelas';

        if (empty($tanggal) || empty($namaAgenda)) {
            return redirect()->back()->with('error', 'Tanggal dan nama agenda/kegiatan wajib diisi.');
        }

        $kelasId = (int)$kelas['id'];
        $tingkat = $this->absensiModel->getTingkatKelas($kelas['nama_kelas']);

        $targetTingkat = 'semua';
        $targetKelasId = null;

        if ($cakupan === 'kelas') {
            $targetKelasId = $kelasId;
        } elseif ($cakupan === 'tingkat') {
            $targetTingkat = $tingkat;
        }

        $walasName = $this->session->get('nama_lengkap') ?: 'Wali Kelas ' . $kelas['nama_kelas'];

        // 1. Simpan ke kalender_pendidikan
        $kalenderModel = new \App\Models\KalenderPendidikanModel();
        $kalenderModel->insert([
            'nama_agenda'     => $namaAgenda,
            'tanggal_mulai'   => $tanggal,
            'tanggal_selesai' => $tanggal,
            'tipe'            => $tipe,
            'keterangan_kbm'  => 'bebas_absen',
            'target_tingkat'  => $targetTingkat,
            'target_kelas_id' => $targetKelasId,
            'keterangan'      => 'Ditandai oleh ' . $walasName . ' (' . $kelas['nama_kelas'] . ')',
        ]);

        // Bersihkan data presensi yang mungkin terlanjur diisi pada tanggal ini
        $deleted = $this->absensiModel->deletePresensiByDateRangeAndTarget($tanggal, $tanggal, $targetTingkat, $targetKelasId);

        // 2. Simpan catatan di sesi presensi_kelas_harian
        $presensiKelasModel = new \App\Models\PresensiKelasHarianModel();
        $sesi = $presensiKelasModel->getOrCreateForToday($kelasId, $tanggal);
        $presensiKelasModel->update($sesi['id'], [
            'catatan' => ($tipe === 'libur' ? 'Libur: ' : 'Acara: ') . $namaAgenda,
        ]);

        $cleanNotice = $deleted > 0 ? " ({$deleted} data presensi yang terlanjur terisi telah otomatis dibersihkan)" : "";
        return redirect()->back()->with('success', "Tanggal " . date('d F Y', strtotime($tanggal)) . " berhasil ditandai sebagai: {$namaAgenda}. Presensi dinonaktifkan (bebas absen){$cleanNotice}.");
    }

    /**
     * Batalkan penandaan libur/acara khusus yang dibuat oleh wali kelas
     */
    public function batalkanTandaiLibur()
    {
        $walasId = (int)$this->session->get('ref_id');
        $kelas   = $this->kelasModel->getKelasByWalas($walasId);

        if (! $kelas) {
            return redirect()->to('/walas')->with('error', 'Anda tidak memiliki kelas ampu.');
        }

        $agendaId = (int)$this->request->getPost('agenda_id');
        $kalenderModel = new \App\Models\KalenderPendidikanModel();
        $agenda = $kalenderModel->find($agendaId);

        if ($agenda && ((int)$agenda['target_kelas_id'] === (int)$kelas['id'] || $agenda['target_tingkat'] === $this->absensiModel->getTingkatKelas($kelas['nama_kelas']))) {
            $kalenderModel->delete($agendaId);
            return redirect()->back()->with('success', "Penandaan agenda '{$agenda['nama_agenda']}' berhasil dibatalkan.");
        }

        return redirect()->back()->with('error', 'Agenda libur tidak dapat dibatalkan atau bukan dibuat khusus untuk kelas/tingkat Anda.');
    }

    /**
     * Halaman Kelola Data Siswa Kelas (Aktif / Nonaktifkan)
     */
    public function siswa()
    {
        $walasId = (int)$this->session->get('ref_id');
        $kelas   = $this->kelasModel->getKelasByWalas($walasId);

        if (! $kelas) {
            return view('walas/no_kelas', [
                'title' => 'Data Siswa Kelas',
            ]);
        }

        $kelasId = (int)$kelas['id'];
        $status  = $this->request->getGet('status');

        $builder = $this->siswaModel->where('kelas_id', $kelasId);
        if (!empty($status) && in_array($status, ['Aktif', 'Nonaktif'])) {
            $builder->where('status', $status);
        }
        $siswaList = $builder->orderBy('nama_siswa', 'ASC')->findAll();

        $totalSemua    = $this->siswaModel->where('kelas_id', $kelasId)->countAllResults();
        $totalAktif    = $this->siswaModel->where('kelas_id', $kelasId)->where('status', 'Aktif')->countAllResults();
        $totalNonaktif = $this->siswaModel->where('kelas_id', $kelasId)->where('status', 'Nonaktif')->countAllResults();

        return view('walas/siswa', [
            'title'          => 'Data Siswa Kelas ' . $kelas['nama_kelas'],
            'kelas'          => $kelas,
            'siswaList'      => $siswaList,
            'selectedStatus' => $status,
            'totalSemua'     => $totalSemua,
            'totalAktif'     => $totalAktif,
            'totalNonaktif'  => $totalNonaktif,
        ]);
    }

    /**
     * Toggle Cepat Status Siswa (Aktif <-> Nonaktif) oleh Wali Kelas
     * Siswa yang di-nonaktifkan tidak akan dimunculkan di daftar presensi siswa/kelas
     */
    public function toggleStatusSiswa($siswaId)
    {
        $walasId = (int)$this->session->get('ref_id');
        $kelas   = $this->kelasModel->getKelasByWalas($walasId);

        if (! $kelas) {
            return redirect()->to('/walas')->with('error', 'Anda tidak memiliki kelas ampu.');
        }

        $kelasId = (int)$kelas['id'];
        $siswa   = $this->siswaModel->where('id', (int)$siswaId)
                                    ->where('kelas_id', $kelasId)
                                    ->first();

        if (! $siswa) {
            return redirect()->back()->with('error', 'Data siswa tidak ditemukan di kelas Anda.');
        }

        $newStatus = ($siswa['status'] === 'Aktif') ? 'Nonaktif' : 'Aktif';
        $updateData = ['status' => $newStatus];
        if ($newStatus === 'Aktif') {
            // Jika diaktifkan kembali, kita tetap bisa simpan riwayat atau reset
            // Kita biarkan catatan lama tersimpan atau tetap ada
        }
        $this->siswaModel->update($siswa['id'], $updateData);

        $pesan = ($newStatus === 'Nonaktif')
            ? "Siswa \"{$siswa['nama_siswa']}\" berhasil dinonaktifkan. Siswa ini tidak akan muncul lagi di lembar pengisian presensi kelas."
            : "Siswa \"{$siswa['nama_siswa']}\" berhasil diaktifkan kembali dan akan muncul di lembar presensi kelas.";

        return redirect()->back()->with('success', $pesan);
    }

    /**
     * Nyatakan Siswa Keluar / Pindah / Mengundurkan Diri (Status Nonaktif + Wajib/Opsional Surat Pendukung)
     */
    public function nonaktifkanSiswa()
    {
        $walasId = (int)$this->session->get('ref_id');
        $kelas   = $this->kelasModel->getKelasByWalas($walasId);

        if (! $kelas) {
            return redirect()->to('/walas')->with('error', 'Anda tidak memiliki kelas ampu.');
        }

        $siswaId = (int)$this->request->getPost('siswa_id');
        $siswa   = $this->siswaModel->where('id', $siswaId)
                                    ->where('kelas_id', (int)$kelas['id'])
                                    ->first();

        if (! $siswa) {
            return redirect()->back()->with('error', 'Data siswa tidak ditemukan di kelas Anda.');
        }

        $alasan   = $this->request->getPost('alasan_nonaktif') ?: 'pindah_sekolah';
        $tanggal  = $this->request->getPost('tanggal_nonaktif') ?: date('Y-m-d');
        $catatan  = trim($this->request->getPost('catatan_nonaktif') ?: '');
        $fileSurat = $this->request->getFile('file_surat');

        $fileName = $siswa['file_surat_nonaktif'] ?? null;
        if ($fileSurat && $fileSurat->isValid() && ! $fileSurat->hasMoved()) {
            $ext = strtolower($fileSurat->getClientExtension());
            if (! in_array($ext, ['pdf', 'jpg', 'jpeg', 'png'], true)) {
                return redirect()->back()->withInput()->with('error', 'Format berkas surat pendukung harus PDF, JPG, atau PNG.');
            }
            if ($fileSurat->getSizeByUnit('mb') > 4) {
                return redirect()->back()->withInput()->with('error', 'Ukuran berkas surat maksimal 4MB.');
            }

            $uploadPath = FCPATH . 'uploads/mutasi/';
            if (! is_dir($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            $fileName = 'mutasi_' . $siswa['id'] . '_' . date('Ymd_His') . '.' . $ext;
            $fileSurat->move($uploadPath, $fileName);
        }

        $this->siswaModel->update($siswa['id'], [
            'status'              => 'Nonaktif',
            'alasan_nonaktif'     => $alasan,
            'tanggal_nonaktif'    => $tanggal,
            'catatan_nonaktif'    => $catatan,
            'file_surat_nonaktif' => $fileName,
        ]);

        $alasanText = ucwords(str_replace('_', ' ', $alasan));
        return redirect()->back()->with('success', "Siswa \"{$siswa['nama_siswa']}\" berhasil dinyatakan {$alasanText} (Status: Nonaktif).");
    }

    /**
     * Auto-detect jenis kelamin siswa kelas binaan berdasarkan nama
     */
    public function autoDetectGender()
    {
        $walasId = (int)$this->session->get('ref_id');
        $kelas   = $this->kelasModel->getKelasByWalas($walasId);

        if (! $kelas) {
            return redirect()->to('/walas')->with('error', 'Anda tidak memiliki kelas ampu.');
        }

        $res = $this->siswaModel->autoDetectGender((int)$kelas['id']);

        return redirect()->back()->with('success', "Auto-Detect Berhasil! {$res['total']} siswa kelas {$kelas['nama_kelas']} berhasil diidentifikasi (Laki-laki: {$res['l']}, Perempuan: {$res['p']}).");
    }

    /**
     * Simpan massal perubahan jenis kelamin siswa kelas binaan
     */
    public function simpanGenderBatch()
    {
        $walasId = (int)$this->session->get('ref_id');
        $kelas   = $this->kelasModel->getKelasByWalas($walasId);

        if (! $kelas) {
            return redirect()->to('/walas')->with('error', 'Anda tidak memiliki kelas ampu.');
        }

        $genderData = $this->request->getPost('gender');
        if (is_array($genderData)) {
            $updated = 0;
            foreach ($genderData as $sId => $g) {
                if (in_array($g, ['L', 'P'], true)) {
                    $this->siswaModel->where('kelas_id', (int)$kelas['id'])
                                     ->where('id', (int)$sId)
                                     ->set(['jenis_kelamin' => $g])
                                     ->update();
                    $updated++;
                }
            }
            return redirect()->back()->with('success', "Berhasil memperbarui jenis kelamin untuk {$updated} siswa kelas {$kelas['nama_kelas']}.");
        }
        return redirect()->back()->with('error', 'Tidak ada data jenis kelamin yang dikirim.');
    }

    /**
     * Laporan Keterlambatan Khusus Kelas Ampu Walas
     * Aturan:
     * - Terlambat > 1 kali: Dilaporkan ke Walas (Badge Kuning)
     * - Terlambat > 3 kali: Wajib Pembinaan (Badge Merah)
     */
    public function keterlambatan()
    {
        $walasId = (int)$this->session->get('ref_id');
        $kelas   = $this->kelasModel->getKelasByWalas($walasId);

        if (! $kelas) {
            return view('walas/no_kelas', [
                'title' => 'Laporan Keterlambatan Siswa',
            ]);
        }

        $kelasId   = (int)$kelas['id'];
        $startDate = $this->request->getGet('start_date') ?: date('Y-m-01');
        $endDate   = $this->request->getGet('end_date') ?: date('Y-m-t');

        // Rekap keterlambatan seluruh siswa kelas
        $rekapTerlambat = $this->keterlambatanSiswaModel->getRekapKeterlambatanKelas($kelasId, $startDate, $endDate);

        // Riwayat log piket lengkap untuk kelas ini
        $logTerlambat = $this->keterlambatanSiswaModel
            ->select('keterlambatan_siswa.*, siswa.nama_siswa, siswa.nisn, users.username as pencatat')
            ->join('siswa', 'siswa.id = keterlambatan_siswa.siswa_id')
            ->join('users', 'users.id = keterlambatan_siswa.dicatat_oleh', 'left')
            ->where('siswa.kelas_id', $kelasId)
            ->where('keterlambatan_siswa.tanggal >=', $startDate)
            ->where('keterlambatan_siswa.tanggal <=', $endDate)
            ->orderBy('keterlambatan_siswa.tanggal', 'DESC')
            ->orderBy('keterlambatan_siswa.jam_masuk', 'DESC')
            ->findAll();

        $totalSiswaTerlambat = count(array_filter($rekapTerlambat, static fn($r) => (int)$r['total_terlambat'] > 0));
        $siswaPerhatian      = count(array_filter($rekapTerlambat, static fn($r) => (int)$r['total_terlambat'] > 1 && (int)$r['total_terlambat'] <= 3));
        $siswaWajibBina      = count(array_filter($rekapTerlambat, static fn($r) => (int)$r['total_terlambat'] > 3));
        $totalKejadian       = count($logTerlambat);
        $daftarSiswa         = $this->siswaModel->getSiswaAktifByKelas($kelasId);

        return view('walas/keterlambatan', [
            'title'               => 'Laporan Keterlambatan Kelas ' . $kelas['nama_kelas'],
            'kelas'               => $kelas,
            'rekapTerlambat'      => $rekapTerlambat,
            'logTerlambat'        => $logTerlambat,
            'daftarSiswa'         => $daftarSiswa,
            'startDate'           => $startDate,
            'endDate'             => $endDate,
            'totalSiswaTerlambat' => $totalSiswaTerlambat,
            'siswaPerhatian'      => $siswaPerhatian,
            'siswaWajibBina'      => $siswaWajibBina,
            'totalKejadian'       => $totalKejadian,
        ]);
    }

    /**
     * Download Excel Rekapitulasi Keterlambatan Kelas (Wali Kelas)
     */
    public function exportKeterlambatanExcel()
    {
        $walasId = (int)$this->session->get('ref_id');
        $kelas   = $this->kelasModel->getKelasByWalas($walasId);

        if (! $kelas) {
            return redirect()->to('/walas')->with('error', 'Kelas tidak ditemukan.');
        }

        $kelasId   = (int)$kelas['id'];
        $startDate = $this->request->getGet('start_date') ?: date('Y-m-01');
        $endDate   = $this->request->getGet('end_date') ?: date('Y-m-t');

        $rekapTerlambat = $this->keterlambatanSiswaModel->getRekapKeterlambatanKelas($kelasId, $startDate, $endDate);
        $logTerlambat   = $this->keterlambatanSiswaModel
            ->select('keterlambatan_siswa.*, siswa.nama_siswa, siswa.nisn, users.username as pencatat')
            ->join('siswa', 'siswa.id = keterlambatan_siswa.siswa_id')
            ->join('users', 'users.id = keterlambatan_siswa.dicatat_oleh', 'left')
            ->where('siswa.kelas_id', $kelasId)
            ->where('keterlambatan_siswa.tanggal >=', $startDate)
            ->where('keterlambatan_siswa.tanggal <=', $endDate)
            ->orderBy('keterlambatan_siswa.tanggal', 'DESC')
            ->orderBy('keterlambatan_siswa.jam_masuk', 'DESC')
            ->findAll();

        $this->exportService->exportKeterlambatanWalasExcel($kelas, $rekapTerlambat, $logTerlambat, $startDate, $endDate);
    }

    /**
     * Menu Utama Rapat Evaluasi Rombel Akhir Semester (Wali Kelas)
     */
    public function evaluasiSemester()
    {
        $walasId = (int)$this->session->get('ref_id');
        $kelas   = $this->kelasModel->getKelasByWalas($walasId);

        if (! $kelas) {
            return view('walas/no_kelas', [
                'title' => 'Evaluasi Rombel Akhir Semester',
            ]);
        }

        $kelasId = (int)$kelas['id'];

        $semester     = $this->request->getGet('semester') ?: (date('n') >= 7 ? '1' : '2');
        $tahun        = $this->request->getGet('tahun') ?: date('Y');
        $minAlpa      = (int)($this->request->getGet('min_alpa') ?: 3);
        $minTerlambat = (int)($this->request->getGet('min_terlambat') ?: 3);
        $minKasus     = (int)($this->request->getGet('min_kasus') ?: 1);

        // Rentang tanggal semester
        if ($semester === '1') {
            $startDate = $tahun . '-07-01';
            $endDate   = $tahun . '-12-31';
            $labelPeriode = "Semester 1 (Ganjil) {$tahun}";
        } else {
            $startDate = $tahun . '-01-01';
            $endDate   = $tahun . '-06-30';
            $labelPeriode = "Semester 2 (Genap) {$tahun}";
        }

        // 1. Dinamika Rombel Awal L/P vs Mutasi Keluar L/P vs Akhir Aktif L/P
        $dinamika = $this->siswaModel->getDinamikaSiswaKelas($kelasId, $startDate, $endDate);

        // 2. Rekap Presensi Seluruh Siswa (termasuk Terlambat T dan Jenis Kelamin)
        $rekapSiswa = $this->absensiModel->getRekapKelas($kelasId, $startDate, $endDate);

        // 3. Riwayat Pembinaan Siswa di kelas ini selama semester
        $riwayatPembinaan = $this->pembinaanModel
            ->select('pembinaan.*, siswa.nama_siswa, siswa.nisn, guru.nama_guru as nama_walas')
            ->join('siswa', 'siswa.id = pembinaan.siswa_id')
            ->join('guru', 'guru.id = pembinaan.walas_id', 'left')
            ->where('siswa.kelas_id', $kelasId)
            ->where('pembinaan.tanggal_tindakan >=', $startDate)
            ->where('pembinaan.tanggal_tindakan <=', $endDate)
            ->orderBy('pembinaan.tanggal_tindakan', 'DESC')
            ->findAll();

        // Hitung total kasus SP per siswa
        $kasusPerSiswa = [];
        foreach ($riwayatPembinaan as $p) {
            $sId = (int)$p['siswa_id'];
            if (! isset($kasusPerSiswa[$sId])) {
                $kasusPerSiswa[$sId] = [];
            }
            $kasusPerSiswa[$sId][] = $p;
        }

        // 4. Identifikasi Siswa Bermasalah berdasarkan Threshold Rapat
        $siswaBermasalah = [];
        foreach ($rekapSiswa as $item) {
            $sId        = (int)$item['siswa_id'];
            $alpa       = (int)($item['total_a'] ?? 0);
            $terlambat  = (int)($item['total_t'] ?? 0);
            $kasusList  = $kasusPerSiswa[$sId] ?? [];
            $totalKasus = count($kasusList);
            $hasSP      = $totalKasus >= $minKasus;

            if ($alpa >= $minAlpa || $terlambat >= $minTerlambat || $hasSP) {
                $item['kasus_pembinaan'] = $kasusList;
                $item['total_kasus_sp']  = $totalKasus;
                $siswaBermasalah[] = $item;
            }
        }

        return view('walas/evaluasi_semester', [
            'title'            => 'Rapat Evaluasi Akhir Semester - ' . $kelas['nama_kelas'],
            'kelas'            => $kelas,
            'semester'         => $semester,
            'tahun'            => $tahun,
            'labelPeriode'     => $labelPeriode,
            'startDate'        => $startDate,
            'endDate'          => $endDate,
            'minAlpa'          => $minAlpa,
            'minTerlambat'     => $minTerlambat,
            'minKasus'         => $minKasus,
            'dinamika'         => $dinamika,
            'rekapSiswa'       => $rekapSiswa,
            'siswaBermasalah'  => $siswaBermasalah,
            'riwayatPembinaan' => $riwayatPembinaan,
        ]);
    }

    /**
     * Export Evaluasi Semester ke Format Excel
     */
    public function exportEvaluasiSemesterExcel()
    {
        $walasId = (int)$this->session->get('ref_id');
        $kelas   = $this->kelasModel->getKelasByWalas($walasId);

        if (! $kelas) {
            return redirect()->to('/walas')->with('error', 'Anda tidak memiliki kelas ampu.');
        }

        $kelasId      = (int)$kelas['id'];
        $semester     = $this->request->getGet('semester') ?: (date('n') >= 7 ? '1' : '2');
        $tahun        = $this->request->getGet('tahun') ?: date('Y');
        $minAlpa      = (int)($this->request->getGet('min_alpa') ?: 3);
        $minTerlambat = (int)($this->request->getGet('min_terlambat') ?: 3);
        $minKasus     = (int)($this->request->getGet('min_kasus') ?: 1);

        if ($semester === '1') {
            $startDate = $tahun . '-07-01';
            $endDate   = $tahun . '-12-31';
            $labelPeriode = "Semester 1 (Ganjil)";
        } else {
            $startDate = $tahun . '-01-01';
            $endDate   = $tahun . '-06-30';
            $labelPeriode = "Semester 2 (Genap)";
        }

        $dinamika   = $this->siswaModel->getDinamikaSiswaKelas($kelasId, $startDate, $endDate);
        $rekapSiswa = $this->absensiModel->getRekapKelas($kelasId, $startDate, $endDate);

        $riwayatPembinaan = $this->pembinaanModel
            ->where('pembinaan.tanggal_tindakan >=', $startDate)
            ->where('pembinaan.tanggal_tindakan <=', $endDate)
            ->findAll();
        $kasusPerSiswa = [];
        foreach ($riwayatPembinaan as $p) {
            $sId = (int)$p['siswa_id'];
            $kasusPerSiswa[$sId][] = $p;
        }

        $siswaBermasalah = [];
        foreach ($rekapSiswa as $item) {
            $sId        = (int)$item['siswa_id'];
            $alpa       = (int)($item['total_a'] ?? 0);
            $terlambat  = (int)($item['total_t'] ?? 0);
            $kasusList  = $kasusPerSiswa[$sId] ?? [];
            $totalKasus = count($kasusList);
            $hasSP      = $totalKasus >= $minKasus;

            if ($alpa >= $minAlpa || $terlambat >= $minTerlambat || $hasSP) {
                $item['kasus_pembinaan'] = $kasusList;
                $item['total_kasus_sp']  = $totalKasus;
                $siswaBermasalah[] = $item;
            }
        }

        $tahunAjaran = $tahun . '/' . ((int)$tahun + 1);
        $this->exportService->exportEvaluasiSemester($kelas, $dinamika, $rekapSiswa, $siswaBermasalah, $labelPeriode, $tahunAjaran);
    }
}
