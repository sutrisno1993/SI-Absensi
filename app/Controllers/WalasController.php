<?php

namespace App\Controllers;

use App\Models\AbsensiModel;
use App\Models\KelasModel;
use App\Models\PembinaanModel;
use App\Models\SiswaModel;
use App\Services\ExportService;

class WalasController extends BaseController
{
    protected $absensiModel;
    protected $kelasModel;
    protected $pembinaanModel;
    protected $siswaModel;
    protected $exportService;

    public function __construct()
    {
        $this->absensiModel   = new AbsensiModel();
        $this->kelasModel     = new KelasModel();
        $this->pembinaanModel = new PembinaanModel();
        $this->siswaModel     = new SiswaModel();
        $this->exportService  = new ExportService();
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
        $siswaBermasalah = $this->absensiModel->getSiswaPerluPembinaan($kelasId, 3);

        // 2. Rekap absensi kelas bulan ini
        $bulanSekarang = date('m');
        $tahunSekarang = date('Y');
        $rekapBulanIni = $this->absensiModel->getRekapKelas($kelasId, $bulanSekarang, $tahunSekarang);

        // 3. Riwayat pembinaan terakhir
        $riwayatPembinaan = $this->pembinaanModel->getRiwayatPembinaan($walasId, $kelasId);

        // 4. Daftar seluruh siswa aktif untuk dropdown form bina siswa
        $daftarSiswa = $this->siswaModel->getSiswaAktifByKelas($kelasId);

        return view('walas/dashboard', [
            'title'            => 'Dashboard Wali Kelas',
            'kelas'            => $kelas,
            'siswaBermasalah'  => $siswaBermasalah,
            'rekapBulanIni'    => $rekapBulanIni,
            'riwayatPembinaan' => $riwayatPembinaan,
            'daftarSiswa'      => $daftarSiswa,
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

        return view('walas/absen_harian', [
            'title'       => 'Input Presensi Kelas ' . $kelas['nama_kelas'],
            'kelas'       => $kelas,
            'tanggal'     => $tanggal,
            'daftarSiswa' => $daftarSiswa,
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

            $sanitizedData[$siswaId] = [
                'status'     => in_array($row['status'] ?? 'H', ['H', 'S', 'I', 'A']) ? $row['status'] : 'H',
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
     * Halaman Rekapitulasi Presensi Kelas
     */
    public function rekap()
    {
        $walasId = (int)$this->session->get('ref_id');
        $kelas   = $this->kelasModel->getKelasByWalas($walasId);

        if (! $kelas) {
            return redirect()->to('/walas')->with('error', 'Anda belum ditugaskan mengampu kelas.');
        }

        $periode  = $this->request->getGet('periode') ?: ($this->request->getGet('semester') ? 'semester' : 'bulan');
        $bulan    = (int)($this->request->getGet('bulan') ?: date('n'));
        $tahun    = (int)($this->request->getGet('tahun') ?: date('Y'));
        $semester = $this->request->getGet('semester') ?: (date('n') >= 7 ? 'ganjil' : 'genap');

        [$startDate, $endDate, $labelPeriode] = $this->absensiModel->resolveDateFilter($periode, $bulan, $tahun, $semester);
        $rekap = $this->absensiModel->getRekapKelas((int)$kelas['id'], $startDate, $endDate);

        return view('walas/rekap', [
            'title'        => 'Rekap Presensi Kelas ' . $kelas['nama_kelas'],
            'kelas'        => $kelas,
            'periode'      => $periode,
            'bulan'        => $bulan,
            'tahun'        => $tahun,
            'semester'     => $semester,
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

        $periode  = $this->request->getGet('periode') ?: ($this->request->getGet('semester') ? 'semester' : ($this->request->getGet('bulan') ? 'bulan' : 'all'));
        $bulan    = (int)($this->request->getGet('bulan') ?: date('n'));
        $tahun    = (int)($this->request->getGet('tahun') ?: date('Y'));
        $semester = $this->request->getGet('semester') ?: (date('n') >= 7 ? 'ganjil' : 'genap');

        [$startDate, $endDate, $labelPeriode] = $this->absensiModel->resolveDateFilter($periode, $bulan, $tahun, $semester);
        $rekapSiswa = $this->absensiModel->getRekapKelas((int)$kelas['id'], $startDate, $endDate);
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
}
