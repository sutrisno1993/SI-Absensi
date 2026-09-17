<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\GuruModel;
use App\Models\KelasModel;
use App\Models\SiswaModel;
use App\Models\AbsensiModel;
use App\Models\PembinaanModel;
use App\Models\GuruJadwalModel;
use App\Models\AbsensiGuruModel;
use App\Models\KeterlambatanSiswaModel;
use App\Models\PiketPetugasModel;
use App\Models\PengaturanModel;
use App\Services\ExportService;

class AdminController extends BaseController
{
    protected $userModel;
    protected $guruModel;
    protected $kelasModel;
    protected $siswaModel;
    protected $absensiModel;
    protected $pembinaanModel;
    protected $guruJadwalModel;
    protected $absensiGuruModel;
    protected $keterlambatanSiswaModel;
    protected $piketPetugasModel;
    protected $pengaturanModel;
    protected $exportService;

    public function __construct()
    {
        $this->userModel               = new UserModel();
        $this->guruModel               = new GuruModel();
        $this->kelasModel              = new KelasModel();
        $this->siswaModel              = new SiswaModel();
        $this->absensiModel            = new AbsensiModel();
        $this->pembinaanModel          = new PembinaanModel();
        $this->guruJadwalModel         = new GuruJadwalModel();
        $this->absensiGuruModel        = new AbsensiGuruModel();
        $this->keterlambatanSiswaModel = new KeterlambatanSiswaModel();
        $this->piketPetugasModel       = new PiketPetugasModel();
        $this->pengaturanModel         = new PengaturanModel();
        $this->exportService           = new ExportService();
    }

    /**
     * Dashboard Utama Admin
     */
    public function index()
    {
        $totalGuru  = $this->guruModel->countAllResults();
        $totalKelas = $this->kelasModel->countAllResults();
        $totalSiswa = $this->siswaModel->countAllResults();
        $totalUser  = $this->userModel->countAllResults();

        $rekapGlobal = $this->absensiModel->getRekapGlobal();
        $riwayatPembinaan = $this->pembinaanModel->getRiwayatPembinaan();
        $topSiswaAlpa = $this->absensiModel->getTopSiswaAlpa(10);

        return view('admin/dashboard', [
            'title'            => 'Dashboard Administrator',
            'totalGuru'        => $totalGuru,
            'totalKelas'       => $totalKelas,
            'totalSiswa'       => $totalSiswa,
            'totalUser'        => $totalUser,
            'rekapGlobal'      => $rekapGlobal,
            'riwayatPembinaan' => $riwayatPembinaan,
            'topSiswaAlpa'     => $topSiswaAlpa,
        ]);
    }

    // ==========================================
    // 1. MASTER GURU
    // ==========================================
    public function guru()
    {
        $guruList = $this->guruModel->getGuruWithJadwal();
        return view('admin/guru', [
            'title'    => 'Kelola Master Guru & Jadwal Mengajar',
            'guruList' => $guruList,
        ]);
    }

    public function simpanGuru()
    {
        $id = $this->request->getPost('id');
        $nip = $this->request->getPost('nip');

        $rules = [
            'nama_guru' => 'required|min_length[3]',
            'no_hp'     => 'permit_empty',
        ];

        if (! empty($nip)) {
            $rules['nip'] = 'trim|is_unique[guru.nip,id,' . ($id ?: 0) . ']';
        }

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'nip'       => !empty($nip) ? $nip : null,
            'nama_guru' => $this->request->getPost('nama_guru'),
            'no_hp'     => $this->request->getPost('no_hp'),
        ];

        if (! empty($id)) {
            $this->guruModel->update($id, $data);
            $guruId = (int)$id;
            $msg = 'Data guru berhasil diperbarui!';
        } else {
            $guruId = (int)$this->guruModel->insert($data);
            $msg = 'Data guru berhasil ditambahkan!';
        }

        // Simpan sinkronisasi jadwal mengajar (Hari x Shift)
        $jadwalRaw = $this->request->getPost('jadwal') ?: [];
        $jadwalList = [];
        foreach ($jadwalRaw as $j) {
            $parts = explode(':', $j);
            if (count($parts) === 2) {
                $jadwalList[] = [
                    'hari'  => trim($parts[0]),
                    'shift' => trim($parts[1]),
                ];
            }
        }
        $this->guruJadwalModel->syncJadwal($guruId, $jadwalList);

        // Sinkronisasi otomatis akun login Guru (Username & Password: No. HP)
        $noHp = trim($this->request->getPost('no_hp') ?? '');
        if (! empty($noHp)) {
            $existingUser = $this->userModel->where('role', 'walas')->where('ref_id', $guruId)->first();
            if ($existingUser) {
                $this->userModel->update($existingUser['id'], [
                    'username'       => $noHp,
                    'password_hash'  => password_hash($noHp, PASSWORD_BCRYPT),
                    'is_first_login' => 0,
                ]);
            } else {
                // Cek username agar tidak duplikat
                if (! $this->userModel->where('username', $noHp)->first()) {
                    $this->userModel->insert([
                        'username'       => $noHp,
                        'password_hash'  => password_hash($noHp, PASSWORD_BCRYPT),
                        'role'           => 'walas',
                        'is_first_login' => 0,
                        'ref_id'         => $guruId,
                    ]);
                }
            }
        }

        return redirect()->to('/admin/guru')->with('success', $msg);
    }

    public function hapusGuru($id)
    {
        $this->guruModel->delete($id);
        return redirect()->to('/admin/guru')->with('success', 'Data guru berhasil dihapus.');
    }

    // ==========================================
    // 2. MASTER KELAS
    // ==========================================
    public function kelas()
    {
        $kelasList = $this->kelasModel->getKelasWithWalas();
        $guruList  = $this->guruModel->orderBy('nama_guru', 'ASC')->findAll();

        return view('admin/kelas', [
            'title'     => 'Kelola Master Kelas',
            'kelasList' => $kelasList,
            'guruList'  => $guruList,
        ]);
    }

    public function simpanKelas()
    {
        $id = $this->request->getPost('id');

        $rules = [
            'nama_kelas' => 'required',
            'shift'      => 'required|in_list[Pagi,Siang]',
            'walas_id'   => 'permit_empty',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $walasId = $this->request->getPost('walas_id');
        $shift   = $this->request->getPost('shift');

        $data = [
            'nama_kelas' => $this->request->getPost('nama_kelas'),
            'shift'      => in_array($shift, ['Pagi', 'Siang']) ? $shift : 'Pagi',
            'walas_id'   => !empty($walasId) ? (int)$walasId : null,
        ];

        if (! empty($id)) {
            $this->kelasModel->update($id, $data);
            $msg = 'Data kelas berhasil diperbarui!';
        } else {
            $this->kelasModel->insert($data);
            $msg = 'Data kelas berhasil ditambahkan!';
        }

        return redirect()->to('/admin/kelas')->with('success', $msg);
    }

    public function hapusKelas($id)
    {
        $this->kelasModel->delete($id);
        return redirect()->to('/admin/kelas')->with('success', 'Data kelas berhasil dihapus.');
    }

    // ==========================================
    // 3. MASTER SISWA
    // ==========================================
    public function siswa()
    {
        $kelasId   = $this->request->getGet('kelas_id');
        $status    = $this->request->getGet('status');
        $kelasList = $this->kelasModel->orderBy('nama_kelas', 'ASC')->findAll();
        $siswaList = $this->siswaModel->getSiswaWithKelas($kelasId ? (int)$kelasId : null, $status);

        return view('admin/siswa', [
            'title'           => 'Kelola Master Siswa',
            'kelasList'       => $kelasList,
            'siswaList'       => $siswaList,
            'selectedKelasId' => $kelasId,
            'selectedStatus'  => $status,
        ]);
    }

    public function simpanSiswa()
    {
        $id     = $this->request->getPost('id');
        $nisn   = $this->request->getPost('nisn');
        $status = $this->request->getPost('status') ?: 'Aktif';

        $rules = [
            'nisn'       => 'required|trim|is_unique[siswa.nisn,id,' . ($id ?: 0) . ']',
            'nama_siswa' => 'required',
            'kelas_id'   => 'required|is_natural_no_zero',
            'status'     => 'permit_empty|in_list[Aktif,Nonaktif]',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $data = [
            'nisn'       => $nisn,
            'nama_siswa' => $this->request->getPost('nama_siswa'),
            'kelas_id'   => (int)$this->request->getPost('kelas_id'),
            'status'     => in_array($status, ['Aktif', 'Nonaktif']) ? $status : 'Aktif',
        ];

        if (! empty($id)) {
            $this->siswaModel->update($id, $data);
            $msg = 'Data siswa berhasil diperbarui!';
        } else {
            $this->siswaModel->insert($data);
            $msg = 'Data siswa berhasil ditambahkan!';
        }

        // Sinkronisasi akun login Murid (Username & Password: NISN)
        $existingUser = $this->userModel->where('username', $nisn)->first();
        if ($existingUser) {
            $this->userModel->update($existingUser['id'], [
                'password_hash'  => password_hash($nisn, PASSWORD_BCRYPT),
                'role'           => 'pj_kelas',
                'ref_id'         => (int)$data['kelas_id'],
                'is_first_login' => 0,
            ]);
        } else {
            $this->userModel->insert([
                'username'       => $nisn,
                'password_hash'  => password_hash($nisn, PASSWORD_BCRYPT),
                'role'           => 'pj_kelas',
                'is_first_login' => 0,
                'ref_id'         => (int)$data['kelas_id'],
            ]);
        }

        return redirect()->to('/admin/siswa')->with('success', $msg);
    }

    /**
     * Toggle Cepat Status Siswa (Aktif <-> Nonaktif)
     */
    public function toggleStatusSiswa($id)
    {
        $siswa = $this->siswaModel->find($id);
        if (! $siswa) {
            return redirect()->back()->with('error', 'Siswa tidak ditemukan.');
        }

        $newStatus = ($siswa['status'] === 'Aktif') ? 'Nonaktif' : 'Aktif';
        $this->siswaModel->update($id, [
            'status' => $newStatus,
        ]);

        $pesan = ($newStatus === 'Nonaktif')
            ? "Siswa {$siswa['nama_siswa']} berhasil di-nonaktifkan (tidak akan muncul di daftar presensi murid/kelas)."
            : "Siswa {$siswa['nama_siswa']} berhasil di-aktifkan kembali.";

        return redirect()->back()->with('success', $pesan);
    }

    public function hapusSiswa($id)
    {
        $this->siswaModel->delete($id);
        return redirect()->to('/admin/siswa')->with('success', 'Data siswa berhasil dihapus.');
    }

    // ==========================================
    // 4. PENGGUNA & RESET PASSWORD
    // ==========================================
    public function users()
    {
        $users     = $this->userModel->orderBy('role', 'ASC')->findAll();
        $guruList  = $this->guruModel->orderBy('nama_guru', 'ASC')->findAll();
        $kelasList = $this->kelasModel->orderBy('nama_kelas', 'ASC')->findAll();

        return view('admin/users', [
            'title'     => 'Kelola Akun Pengguna',
            'users'     => $users,
            'guruList'  => $guruList,
            'kelasList' => $kelasList,
        ]);
    }

    public function simpanUser()
    {
        $id = $this->request->getPost('id');
        $username = $this->request->getPost('username');

        $rules = [
            'username' => 'required|trim|is_unique[users.username,id,' . ($id ?: 0) . ']',
            'role'     => 'required|in_list[admin,walas,pj_kelas,guru_piket]',
        ];

        if (empty($id)) {
            $rules['password'] = 'required|min_length[6]';
        }

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $role  = $this->request->getPost('role');
        $refId = null;

        if ($role === 'walas' || $role === 'guru_piket') {
            $refId = $this->request->getPost('ref_id_guru') ?: null;
        } elseif ($role === 'pj_kelas') {
            $refId = $this->request->getPost('ref_id_kelas') ?: null;
        }

        $data = [
            'username' => $username,
            'role'     => $role,
            'ref_id'   => $refId,
        ];

        if (! empty($this->request->getPost('password'))) {
            $data['password_hash'] = password_hash($this->request->getPost('password'), PASSWORD_BCRYPT);
            $data['is_first_login'] = ($role === 'walas') ? 1 : 0;
        }

        if (! empty($id)) {
            $this->userModel->update($id, $data);
            $msg = 'Akun pengguna berhasil diperbarui!';
        } else {
            $this->userModel->insert($data);
            $msg = 'Akun pengguna baru berhasil dibuat!';
        }

        return redirect()->to('/admin/users')->with('success', $msg);
    }

    public function resetPassword($id)
    {
        $user = $this->userModel->find($id);
        if (! $user) {
            return redirect()->back()->with('error', 'User tidak ditemukan.');
        }

        if ($user['role'] === 'admin') {
            $defaultPassword = 'admin123';
        } elseif ($user['role'] === 'walas' || $user['role'] === 'guru_piket') {
            $guru = $this->guruModel->find($user['ref_id']);
            $defaultPassword = (!empty($guru) && !empty($guru['no_hp'])) ? $guru['no_hp'] : $user['username'];
        } else {
            // pj_kelas / murid: default password adalah NISN
            $defaultPassword = $user['username'];
        }

        $this->userModel->update($id, [
            'password_hash'  => password_hash($defaultPassword, PASSWORD_BCRYPT),
            'is_first_login' => 0,
        ]);

        return redirect()->to('/admin/users')->with('success', 'Password user "' . $user['username'] . '" berhasil direset ke: ' . $defaultPassword);
    }

    public function hapusUser($id)
    {
        if ((int)$id === (int)$this->session->get('user_id')) {
            return redirect()->back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $this->userModel->delete($id);
        return redirect()->to('/admin/users')->with('success', 'Akun pengguna berhasil dihapus.');
    }

    // ==========================================
    // 5. IMPORT EXCEL & TEMPLATES
    // ==========================================

    /**
     * Download Template Excel Siswa
     */
    public function downloadTemplateSiswa()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Siswa');

        // Header Styling
        $sheet->setCellValue('A1', 'NISN');
        $sheet->setCellValue('B1', 'Nama Siswa');
        $sheet->setCellValue('C1', 'Nama Kelas');

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '4F46E5']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ];
        $sheet->getStyle('A1:C1')->applyFromArray($headerStyle);

        // Contoh Data
        $sheet->setCellValueExplicit('A2', '0061234509', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValue('B2', 'Ahmad Dani Pratama');
        $sheet->setCellValue('C2', 'XII AK 1');

        $sheet->setCellValueExplicit('A3', '0061234510', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValue('B3', 'Bella Nur Safitri');
        $sheet->setCellValue('C3', 'XII AK 1');

        foreach (['A', 'B', 'C'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'Template_Import_Siswa.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    /**
     * Proses Import Siswa dari Excel (.xlsx, .xls, .csv)
     */
    public function importSiswa()
    {
        $file = $this->request->getFile('file_excel');

        if (! $file || ! $file->isValid()) {
            return redirect()->back()->with('error', 'Silakan pilih berkas Excel yang valid.');
        }

        $ext = strtolower($file->getClientExtension());
        if (! in_array($ext, ['xlsx', 'xls', 'csv'])) {
            return redirect()->back()->with('error', 'Format berkas harus .xlsx, .xls, atau .csv');
        }

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getTempName());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            $imported = 0;
            $updated  = 0;
            $skipped  = 0;

            // Cache kelas untuk efisiensi
            $kelasList = $this->kelasModel->findAll();
            $kelasMap = [];
            foreach ($kelasList as $k) {
                $kelasMap[strtoupper(trim($k['nama_kelas']))] = (int)$k['id'];
            }

            foreach ($rows as $idx => $row) {
                if ($idx === 1) continue; // Skip baris header

                $nisn       = trim($row['A'] ?? '');
                $namaSiswa  = trim($row['B'] ?? '');
                $namaKelas  = trim($row['C'] ?? '');

                if (empty($nisn) || empty($namaSiswa)) {
                    $skipped++;
                    continue;
                }

                // Cari ID Kelas atau buat jika belum ada
                $normalizedKelas = strtoupper($namaKelas);
                if (! empty($namaKelas) && isset($kelasMap[$normalizedKelas])) {
                    $kelasId = $kelasMap[$normalizedKelas];
                } elseif (! empty($namaKelas)) {
                    // Buat kelas baru otomatis
                    $newKelasId = $this->kelasModel->insert([
                        'nama_kelas' => $namaKelas,
                        'shift'      => 'Pagi',
                        'walas_id'   => null,
                    ]);
                    $kelasMap[$normalizedKelas] = (int)$newKelasId;
                    $kelasId = (int)$newKelasId;
                } else {
                    $skipped++;
                    continue;
                }

                // Cek apakah siswa dengan NISN ini sudah ada
                $existing = $this->siswaModel->where('nisn', $nisn)->first();

                if ($existing) {
                    $this->siswaModel->update($existing['id'], [
                        'nama_siswa' => $namaSiswa,
                        'kelas_id'   => $kelasId,
                    ]);
                    $updated++;
                } else {
                    $this->siswaModel->insert([
                        'nisn'       => $nisn,
                        'nama_siswa' => $namaSiswa,
                        'kelas_id'   => $kelasId,
                    ]);
                    $imported++;
                }

                // Otomatis buat/sinkronkan akun login Murid (Username & Password: NISN)
                $existingUser = $this->userModel->where('username', $nisn)->first();
                if ($existingUser) {
                    $this->userModel->update($existingUser['id'], [
                        'password_hash'  => password_hash($nisn, PASSWORD_BCRYPT),
                        'role'           => 'pj_kelas',
                        'ref_id'         => $kelasId,
                        'is_first_login' => 0,
                    ]);
                } else {
                    $this->userModel->insert([
                        'username'       => $nisn,
                        'password_hash'  => password_hash($nisn, PASSWORD_BCRYPT),
                        'role'           => 'pj_kelas',
                        'is_first_login' => 0,
                        'ref_id'         => $kelasId,
                    ]);
                }
            }

            return redirect()->to('/admin/siswa')->with('success', "Proses import selesai! {$imported} siswa baru ditambahkan, {$updated} siswa diperbarui. Akun login seluruh murid otomatis aktif (Username & Password: NISN).");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memproses berkas: ' . $e->getMessage());
        }
    }

    /**
     * Download Template Excel Guru / Walas
     */
    public function downloadTemplateGuru()
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Template Guru');

        // Header Styling
        $sheet->setCellValue('A1', 'NIP');
        $sheet->setCellValue('B1', 'Nama Guru');
        $sheet->setCellValue('C1', 'No HP');

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => '10B981']],
            'alignment' => ['horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER],
        ];
        $sheet->getStyle('A1:C1')->applyFromArray($headerStyle);

        // Contoh Data
        $sheet->setCellValueExplicit('A2', '198901012015011001', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValue('B2', 'Bambang Sugiharto, S.Kom');
        $sheet->setCellValueExplicit('C2', '081234567890', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

        $sheet->setCellValueExplicit('A3', '199105152019022002', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
        $sheet->setCellValue('B3', 'Endang Tri Wahyuni, M.Pd');
        $sheet->setCellValueExplicit('C3', '081298765432', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);

        foreach (['A', 'B', 'C'] as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $fileName = 'Template_Import_Guru.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        $writer->save('php://output');
        exit;
    }

    /**
     * Proses Import Guru dari Excel (.xlsx, .xls, .csv) & Otomatis Buat Akun Walas
     */
    public function importGuru()
    {
        $file = $this->request->getFile('file_excel');

        if (! $file || ! $file->isValid()) {
            return redirect()->back()->with('error', 'Silakan pilih berkas Excel yang valid.');
        }

        $ext = strtolower($file->getClientExtension());
        if (! in_array($ext, ['xlsx', 'xls', 'csv'])) {
            return redirect()->back()->with('error', 'Format berkas harus .xlsx, .xls, atau .csv');
        }

        try {
            $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getTempName());
            $sheet = $spreadsheet->getActiveSheet();
            $rows = $sheet->toArray(null, true, true, true);

            $imported = 0;
            $updated  = 0;
            $skipped  = 0;

            foreach ($rows as $idx => $row) {
                if ($idx === 1) continue; // Skip header

                $nip      = trim($row['A'] ?? '');
                $namaGuru = trim($row['B'] ?? '');
                $noHp     = trim($row['C'] ?? '');

                if (empty($namaGuru)) {
                    $skipped++;
                    continue;
                }

                $dataGuru = [
                    'nip'       => !empty($nip) ? $nip : null,
                    'nama_guru' => $namaGuru,
                    'no_hp'     => !empty($noHp) ? $noHp : null,
                ];

                // Cek guru berdasarkan NIP jika ada
                $existingGuru = null;
                if (! empty($nip)) {
                    $existingGuru = $this->guruModel->where('nip', $nip)->first();
                }

                if ($existingGuru) {
                    $this->guruModel->update($existingGuru['id'], $dataGuru);
                    $guruId = $existingGuru['id'];
                    $updated++;
                } else {
                    $guruId = $this->guruModel->insert($dataGuru);
                    $imported++;
                }

                // Otomatis buatkan akun Walas (Username & Password: No. HP)
                if (! empty($noHp)) {
                    $existingUser = $this->userModel->where('ref_id', $guruId)->where('role', 'walas')->first();
                    if ($existingUser) {
                        $this->userModel->update($existingUser['id'], [
                            'username'       => $noHp,
                            'password_hash'  => password_hash($noHp, PASSWORD_BCRYPT),
                            'is_first_login' => 0,
                        ]);
                    } else {
                        if (! $this->userModel->where('username', $noHp)->first()) {
                            $this->userModel->insert([
                                'username'       => $noHp,
                                'password_hash'  => password_hash($noHp, PASSWORD_BCRYPT),
                                'role'           => 'walas',
                                'is_first_login' => 0,
                                'ref_id'         => $guruId,
                            ]);
                        }
                    }
                }
            }

            return redirect()->to('/admin/guru')->with('success', "Proses import guru selesai! {$imported} guru baru ditambahkan, {$updated} diperbarui. Akun login walas otomatis aktif (Username & Password: No. HP).");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Terjadi kesalahan saat memproses berkas: ' . $e->getMessage());
        }
    }

    // ==========================================
    // 6. REKAP LAPORAN HIERARKIS (DRILL-DOWN)
    // ==========================================

    /**
     * Level 1: Rekapitulasi Presensi per Kelas Sekolah
     */
    public function rekapLaporan()
    {
        $periode  = $this->request->getGet('periode') ?: 'all'; // all, bulan, semester
        $bulan    = (int)($this->request->getGet('bulan') ?: date('n'));
        $tahun    = (int)($this->request->getGet('tahun') ?: date('Y'));
        $semester = $this->request->getGet('semester') ?: (date('n') >= 7 ? 'ganjil' : 'genap');

        [$startDate, $endDate, $labelPeriode] = $this->absensiModel->resolveDateFilter($periode, $bulan, $tahun, $semester);
        $rekapGlobal = $this->absensiModel->getRekapGlobal($startDate, $endDate);

        return view('admin/rekap_laporan', [
            'title'        => 'Rekapitulasi Presensi Global Sekolah',
            'rekapGlobal'  => $rekapGlobal,
            'periode'      => $periode,
            'bulan'        => $bulan,
            'tahun'        => $tahun,
            'semester'     => $semester,
            'labelPeriode' => $labelPeriode,
        ]);
    }

    /**
     * Level 2: Drill-down Rekap Presensi per Kelas (Daftar Siswa di Kelas Ini)
     */
    public function laporanKelas($kelasId)
    {
        $kelas = $this->kelasModel->select('kelas.*, guru.nama_guru, guru.nip')
                                  ->join('guru', 'guru.id = kelas.walas_id', 'left')
                                  ->find($kelasId);

        if (! $kelas) {
            return redirect()->to('/admin/laporan')->with('error', 'Kelas tidak ditemukan.');
        }

        $periode  = $this->request->getGet('periode') ?: ($this->request->getGet('semester') ? 'semester' : ($this->request->getGet('bulan') ? 'bulan' : 'all'));
        $bulan    = (int)($this->request->getGet('bulan') ?: date('n'));
        $tahun    = (int)($this->request->getGet('tahun') ?: date('Y'));
        $semester = $this->request->getGet('semester') ?: (date('n') >= 7 ? 'ganjil' : 'genap');

        [$startDate, $endDate, $labelPeriode] = $this->absensiModel->resolveDateFilter($periode, $bulan, $tahun, $semester);
        $rekapSiswa = $this->absensiModel->getRekapKelas((int)$kelasId, $startDate, $endDate);

        return view('admin/laporan_kelas', [
            'title'        => 'Laporan Presensi Kelas ' . $kelas['nama_kelas'],
            'kelas'        => $kelas,
            'rekapSiswa'   => $rekapSiswa,
            'periode'      => $periode,
            'bulan'        => $bulan,
            'tahun'        => $tahun,
            'semester'     => $semester,
            'labelPeriode' => $labelPeriode,
        ]);
    }

    /**
     * Level 3: Drill-down Track Record Ketidakhadiran Siswa (Tanggal, Status S/I/A, Keterangan)
     */
    public function laporanSiswa($siswaId)
    {
        $siswa = $this->siswaModel->select('siswa.*, kelas.nama_kelas, kelas.id as kelas_id, guru.nama_guru as nama_walas')
                                  ->join('kelas', 'kelas.id = siswa.kelas_id', 'inner')
                                  ->join('guru', 'guru.id = kelas.walas_id', 'left')
                                  ->find($siswaId);

        if (! $siswa) {
            return redirect()->to('/admin/laporan')->with('error', 'Siswa tidak ditemukan.');
        }

        // Statistik Kehadiran
        $stats = $this->absensiModel->getStatistikSiswa((int)$siswaId);

        // Track record ketidakhadiran (S, I, A) beserta tanggal dan keterangan
        $trackRecord = $this->absensiModel->getTrackRecordSiswa((int)$siswaId);

        // Riwayat tindakan pembinaan siswa ini
        $riwayatPembinaan = $this->pembinaanModel->where('siswa_id', $siswaId)
                                                ->orderBy('tanggal_tindakan', 'DESC')
                                                ->findAll();

        return view('admin/laporan_siswa', [
            'title'            => 'Track Record Kehadiran - ' . $siswa['nama_siswa'],
            'siswa'            => $siswa,
            'stats'            => $stats,
            'trackRecord'      => $trackRecord,
            'riwayatPembinaan' => $riwayatPembinaan,
        ]);
    }

    // ==========================================
    // 6. PENUGASAN GURU PIKET
    // ==========================================
    public function penugasanPiket()
    {
        $guruList = $this->guruModel->orderBy('nama_guru', 'ASC')->findAll();
        $penugasanGrouped = $this->piketPetugasModel->getAllGrouped();

        return view('admin/penugasan_piket', [
            'title'            => 'Pengaturan Penugasan Guru Piket',
            'guruList'         => $guruList,
            'penugasanGrouped' => $penugasanGrouped,
            'hariList'         => ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'],
            'shiftList'        => ['Pagi', 'Siang'],
        ]);
    }

    public function simpanPenugasanPiket()
    {
        $hari    = $this->request->getPost('hari');
        $shift   = $this->request->getPost('shift');
        $guruIds = $this->request->getPost('guru_ids') ?: [];

        if (! in_array($hari, ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu']) || ! in_array($shift, ['Pagi', 'Siang'])) {
            return redirect()->back()->with('error', 'Hari atau shift tidak valid.');
        }

        $this->piketPetugasModel->syncPetugas($hari, $shift, $guruIds);

        return redirect()->to('/admin/penugasan-piket')->with('success', "Penugasan guru piket untuk {$hari} Shift {$shift} berhasil diperbarui!");
    }

    // ==========================================
    // 7. PENGATURAN JADWAL MENGAJAR GURU (HARI & SHIFT)
    // ==========================================
    public function jadwalGuru()
    {
        $hariList  = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $shiftList = ['Pagi', 'Siang'];

        $namaHariIni = [
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
            7 => 'Senin',
        ];
        $defaultHari = $namaHariIni[(int)date('N')] ?? 'Senin';

        $selectedHari  = $this->request->getGet('hari') ?: $defaultHari;
        $selectedShift = $this->request->getGet('shift') ?: 'Pagi';

        if (! in_array($selectedHari, $hariList)) {
            $selectedHari = 'Senin';
        }
        if (! in_array($selectedShift, $shiftList)) {
            $selectedShift = 'Pagi';
        }

        $guruList = $this->guruModel->orderBy('nama_guru', 'ASC')->findAll();
        
        // Ambil ID guru yang saat ini terjadwal pada hari & shift terpilih
        $assignedGuru = $this->guruJadwalModel->where('hari', $selectedHari)
                                             ->where('shift', $selectedShift)
                                             ->findColumn('guru_id') ?: [];

        // Ambil matriks jadwal lengkap untuk ringkasan
        $matrixJadwal = $this->guruJadwalModel->getJadwalMatrix();

        return view('admin/jadwal_guru', [
            'title'          => 'Pengaturan Jadwal Mengajar Guru',
            'hariList'       => $hariList,
            'shiftList'      => $shiftList,
            'selectedHari'   => $selectedHari,
            'selectedShift'  => $selectedShift,
            'guruList'       => $guruList,
            'assignedGuruIds'=> array_map('intval', $assignedGuru),
            'matrixJadwal'   => $matrixJadwal,
        ]);
    }

    public function simpanJadwalGuru()
    {
        $hari    = $this->request->getPost('hari');
        $shift   = $this->request->getPost('shift');
        $guruIds = $this->request->getPost('guru_ids') ?: [];

        $hariList  = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $shiftList = ['Pagi', 'Siang'];

        if (! in_array($hari, $hariList) || ! in_array($shift, $shiftList)) {
            return redirect()->back()->with('error', 'Hari atau shift tidak valid.');
        }

        $this->guruJadwalModel->syncJadwalByHariAndShift($hari, $shift, $guruIds);
        $count = count($guruIds);

        return redirect()->to("/admin/jadwal-guru?hari={$hari}&shift={$shift}")
                         ->with('success', "Jadwal mengajar untuk hari {$hari} (Shift {$shift}) berhasil diperbarui! Sebanyak {$count} guru ditugaskan.");
    }

    // ==========================================
    // 8. MONITORING PERSENTASE KEHADIRAN GURU & SISWA
    // ==========================================
    public function monitoring()
    {
        $periode  = $this->request->getGet('periode') ?: 'all'; // all, bulan, semester
        $bulan    = (int)($this->request->getGet('bulan') ?: date('n'));
        $tahun    = (int)($this->request->getGet('tahun') ?: date('Y'));
        $semester = $this->request->getGet('semester') ?: (date('n') >= 7 ? 'ganjil' : 'genap');

        $startDate = null;
        $endDate   = null;
        $labelPeriode = 'Semua Waktu (Keseluruhan)';

        if ($periode === 'bulan') {
            $startDate = sprintf('%04d-%02d-01', $tahun, $bulan);
            $endDate   = date('Y-m-t', strtotime($startDate));
            $namaBulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            $labelPeriode = "Bulan " . ($namaBulan[$bulan] ?? '') . " " . $tahun;
        } elseif ($periode === 'semester') {
            if ($semester === 'ganjil') {
                $startDate = sprintf('%04d-07-01', $tahun);
                $endDate   = sprintf('%04d-12-31', $tahun);
                $labelPeriode = "Semester Ganjil {$tahun}";
            } else {
                $startDate = sprintf('%04d-01-01', $tahun);
                $endDate   = sprintf('%04d-06-30', $tahun);
                $labelPeriode = "Semester Genap {$tahun}";
            }
        }

        // Statistik Guru
        $statGuru       = $this->absensiGuruModel->getStatistikKehadiranRange($startDate, $endDate);
        $performaGuru   = $this->absensiGuruModel->getPerformaPerGuru($startDate, $endDate);

        // Statistik Siswa
        $statSiswa      = $this->absensiModel->getStatistikKehadiranSiswaRange($startDate, $endDate);
        $performaKelas  = $this->absensiModel->getPerformaPerKelas($startDate, $endDate);

        return view('admin/monitoring', [
            'title'         => 'Monitoring Persentase Kehadiran Guru & Siswa',
            'periode'       => $periode,
            'bulan'         => $bulan,
            'tahun'         => $tahun,
            'semester'      => $semester,
            'labelPeriode'  => $labelPeriode,
            'statGuru'      => $statGuru,
            'performaGuru'  => $performaGuru,
            'statSiswa'     => $statSiswa,
            'performaKelas' => $performaKelas,
        ]);
    }

    // ==========================================
    // 8. EXPORT LAPORAN EXCEL (.XLSX)
    // ==========================================

    /**
     * Download Excel Rekapitulasi Global Presensi Seluruh Kelas
     */
    public function exportGlobalExcel()
    {
        $periode  = $this->request->getGet('periode') ?: 'all';
        $bulan    = (int)($this->request->getGet('bulan') ?: date('n'));
        $tahun    = (int)($this->request->getGet('tahun') ?: date('Y'));
        $semester = $this->request->getGet('semester') ?: (date('n') >= 7 ? 'ganjil' : 'genap');

        [$startDate, $endDate, $labelPeriode] = $this->absensiModel->resolveDateFilter($periode, $bulan, $tahun, $semester);
        $rekapGlobal = $this->absensiModel->getRekapGlobal($startDate, $endDate);
        $this->exportService->exportGlobalPresensi($rekapGlobal, $labelPeriode);
    }

    /**
     * Download Excel Rekap Presensi per Rombel Kelas
     */
    public function exportKelasExcel($kelasId)
    {
        $kelas = $this->kelasModel->select('kelas.*, guru.nama_guru, guru.nip')
                                  ->join('guru', 'guru.id = kelas.walas_id', 'left')
                                  ->find($kelasId);

        if (! $kelas) {
            return redirect()->back()->with('error', 'Kelas tidak ditemukan.');
        }

        $periode  = $this->request->getGet('periode') ?: ($this->request->getGet('semester') ? 'semester' : ($this->request->getGet('bulan') ? 'bulan' : 'all'));
        $bulan    = (int)($this->request->getGet('bulan') ?: date('n'));
        $tahun    = (int)($this->request->getGet('tahun') ?: date('Y'));
        $semester = $this->request->getGet('semester') ?: (date('n') >= 7 ? 'ganjil' : 'genap');

        [$startDate, $endDate, $labelPeriode] = $this->absensiModel->resolveDateFilter($periode, $bulan, $tahun, $semester);
        $rekapSiswa = $this->absensiModel->getRekapKelas((int)$kelasId, $startDate, $endDate);
        $this->exportService->exportKelasPresensi($kelas, $rekapSiswa, $labelPeriode);
    }

    /**
     * Download Excel Track Record Individual Siswa
     */
    public function exportSiswaExcel($siswaId)
    {
        $siswa = $this->siswaModel->select('siswa.*, kelas.nama_kelas, kelas.id as kelas_id, guru.nama_guru as nama_walas')
                                  ->join('kelas', 'kelas.id = siswa.kelas_id', 'inner')
                                  ->join('guru', 'guru.id = kelas.walas_id', 'left')
                                  ->find($siswaId);

        if (! $siswa) {
            return redirect()->back()->with('error', 'Siswa tidak ditemukan.');
        }

        $stats            = $this->absensiModel->getStatistikSiswa((int)$siswaId);
        $trackRecord      = $this->absensiModel->getTrackRecordSiswa((int)$siswaId);
        $riwayatPembinaan = $this->pembinaanModel->where('siswa_id', $siswaId)
                                                ->orderBy('tanggal_tindakan', 'DESC')
                                                ->findAll();

        $this->exportService->exportSiswaTrackRecord($siswa, $stats, $trackRecord, $riwayatPembinaan);
    }

    /**
     * Download Excel Laporan Penindakan & Keterlambatan Siswa (Multi-Sheet)
     */
    public function exportPenindakanExcel()
    {
        $pembinaanList = $this->pembinaanModel->getRiwayatPembinaan();
        $terlambatList = $this->keterlambatanSiswaModel->getRekapPeriode(null, null);

        $this->exportService->exportPenindakanSiswa($pembinaanList, $terlambatList);
    }

    /**
     * Download Excel Monitoring Presensi Guru
     */
    public function exportGuruExcel()
    {
        $periode  = $this->request->getGet('periode') ?: 'all';
        $bulan    = (int)($this->request->getGet('bulan') ?: date('n'));
        $tahun    = (int)($this->request->getGet('tahun') ?: date('Y'));
        $semester = $this->request->getGet('semester') ?: (date('n') >= 7 ? 'ganjil' : 'genap');

        $startDate = null;
        $endDate   = null;
        $labelPeriode = 'Semua Waktu (Keseluruhan)';

        if ($periode === 'bulan') {
            $startDate = sprintf('%04d-%02d-01', $tahun, $bulan);
            $endDate   = date('Y-m-t', strtotime($startDate));
            $namaBulan = ['', 'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
            $labelPeriode = "Bulan " . ($namaBulan[$bulan] ?? '') . " " . $tahun;
        } elseif ($periode === 'semester') {
            if ($semester === 'ganjil') {
                $startDate = sprintf('%04d-07-01', $tahun);
                $endDate   = sprintf('%04d-12-31', $tahun);
                $labelPeriode = "Semester Ganjil {$tahun}";
            } else {
                $startDate = sprintf('%04d-01-01', $tahun);
                $endDate   = sprintf('%04d-06-30', $tahun);
                $labelPeriode = "Semester Genap {$tahun}";
            }
        }

        $performaGuru = $this->absensiGuruModel->getPerformaPerGuru($startDate, $endDate);
        $this->exportService->exportPresensiGuru($performaGuru, $labelPeriode);
    }

    /**
     * Download Excel Monitoring Presensi Siswa
     */
    public function exportSiswaMonitoringExcel()
    {
        $periode  = $this->request->getGet('periode') ?: 'all';
        $bulan    = (int)($this->request->getGet('bulan') ?: date('n'));
        $tahun    = (int)($this->request->getGet('tahun') ?: date('Y'));
        $semester = $this->request->getGet('semester') ?: (date('n') >= 7 ? 'ganjil' : 'genap');

        [$startDate, $endDate, $labelPeriode] = $this->absensiModel->resolveDateFilter($periode, $bulan, $tahun, $semester);

        $statSiswa     = $this->absensiModel->getStatistikKehadiranSiswaRange($startDate, $endDate);
        $performaKelas = $this->absensiModel->getPerformaPerKelas($startDate, $endDate);

        $this->exportService->exportSiswaMonitoringExcel($statSiswa, $performaKelas, $labelPeriode);
    }

    // ==========================================
    // 7. RIWAYAT PEMBINAAN SISWA (ANAK BERMASALAH)
    // ==========================================
    public function pembinaan()
    {
        $kelasId       = $this->request->getGet('kelas_id') ? (int)$this->request->getGet('kelas_id') : null;
        $jenisTindakan = $this->request->getGet('jenis_tindakan') ?: null;
        $keyword       = $this->request->getGet('q') ?: null;

        // Ambil riwayat tindakan pembinaan yang telah difilter
        $riwayatPembinaan = $this->pembinaanModel->getFilteredPembinaan($kelasId, $jenisTindakan, $keyword);

        // Ambil radar anak yang bermasalah:
        // 1. Siswa dengan akumulasi Alpa >= 3
        $db = \Config\Database::connect();
        $whereKelas = $kelasId ? "WHERE s.kelas_id = " . (int)$kelasId : "";
        
        $siswaAlpaBermasalah = $db->query("
            SELECT s.id as siswa_id, s.nisn, s.nama_siswa, k.id as kelas_id, k.nama_kelas, g.nama_guru as nama_walas,
                   COUNT(a.id) as total_alpa,
                   (SELECT COUNT(*) FROM pembinaan p WHERE p.siswa_id = s.id) as total_pembinaan
            FROM siswa s
            JOIN kelas k ON k.id = s.kelas_id
            LEFT JOIN guru g ON g.id = k.walas_id
            JOIN absensi a ON a.siswa_id = s.id AND a.status = 'A'
            $whereKelas
            GROUP BY s.id, s.nisn, s.nama_siswa, k.id, k.nama_kelas, g.nama_guru
            HAVING COUNT(a.id) >= 3
            ORDER BY total_alpa DESC
        ")->getResultArray();

        // 2. Siswa yang sering terlambat >= 3 kali
        $siswaTerlambatBermasalah = $db->query("
            SELECT s.id as siswa_id, s.nisn, s.nama_siswa, k.id as kelas_id, k.nama_kelas, g.nama_guru as nama_walas,
                   COUNT(ks.id) as total_terlambat,
                   SUM(ks.menit_terlambat) as total_menit,
                   (SELECT COUNT(*) FROM pembinaan p WHERE p.siswa_id = s.id) as total_pembinaan
            FROM siswa s
            JOIN kelas k ON k.id = s.kelas_id
            LEFT JOIN guru g ON g.id = k.walas_id
            JOIN keterlambatan_siswa ks ON ks.siswa_id = s.id
            $whereKelas
            GROUP BY s.id, s.nisn, s.nama_siswa, k.id, k.nama_kelas, g.nama_guru
            HAVING COUNT(ks.id) >= 3
            ORDER BY total_terlambat DESC
        ")->getResultArray();

        // Daftar kelas untuk dropdown filter dan form
        $kelasList = $this->kelasModel->orderBy('nama_kelas', 'ASC')->findAll();

        // Daftar seluruh siswa aktif untuk modal input pembinaan
        $siswaList = $this->siswaModel->select('siswa.id, siswa.nama_siswa, siswa.nisn, kelas.nama_kelas, kelas.walas_id')
                                      ->join('kelas', 'kelas.id = siswa.kelas_id')
                                      ->where('siswa.status', 'Aktif')
                                      ->orderBy('kelas.nama_kelas', 'ASC')
                                      ->orderBy('siswa.nama_siswa', 'ASC')
                                      ->findAll();

        // Daftar guru untuk pilihan pembina
        $guruList = $this->guruModel->orderBy('nama_guru', 'ASC')->findAll();

        return view('admin/pembinaan', [
            'title'                    => 'Data Riwayat Pembinaan Siswa Bermasalah',
            'riwayatPembinaan'         => $riwayatPembinaan,
            'siswaAlpaBermasalah'      => $siswaAlpaBermasalah,
            'siswaTerlambatBermasalah' => $siswaTerlambatBermasalah,
            'kelasList'                => $kelasList,
            'siswaList'                => $siswaList,
            'guruList'                 => $guruList,
            'selectedKelas'            => $kelasId,
            'selectedJenis'            => $jenisTindakan,
            'keyword'                  => $keyword,
        ]);
    }

    /**
     * Simpan Tindakan Pembinaan oleh Admin / Kesiswaan
     */
    public function simpanPembinaan()
    {
        $rules = [
            'siswa_id'          => 'required|is_not_unique[siswa.id]',
            'tanggal_tindakan'  => 'required|valid_date',
            'jenis_tindakan'    => 'required|min_length[3]',
            'catatan_pembinaan' => 'required|min_length[5]',
            'file_bukti'        => [
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
        $walasId = $this->request->getPost('walas_id') ? (int)$this->request->getPost('walas_id') : null;

        if (empty($walasId)) {
            $siswa = $this->siswaModel->select('siswa.*, kelas.walas_id')
                                      ->join('kelas', 'kelas.id = siswa.kelas_id')
                                      ->find($siswaId);
            $walasId = $siswa['walas_id'] ?? null;
        }

        // Upload berkas bukti
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

        return redirect()->to('/admin/pembinaan')->with('success', 'Catatan pembinaan siswa berhasil disimpan!');
    }

    /**
     * Hapus Data Tindakan Pembinaan
     */
    public function hapusPembinaan($id)
    {
        $pembinaan = $this->pembinaanModel->find($id);
        if ($pembinaan) {
            if (!empty($pembinaan['file_bukti'])) {
                $filePath = FCPATH . 'uploads/pembinaan/' . $pembinaan['file_bukti'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }
            $this->pembinaanModel->delete($id);
            return redirect()->to('/admin/pembinaan')->with('success', 'Catatan pembinaan berhasil dihapus.');
        }

        return redirect()->to('/admin/pembinaan')->with('error', 'Data pembinaan tidak ditemukan.');
    }

    // ==========================================
    // 8. PENGATURAN JAM MASUK & KETERLAMBATAN GURU
    // ==========================================
    public function keterlambatanGuru()
    {
        $startDate = $this->request->getGet('start_date') ?: date('Y-m-01');
        $endDate   = $this->request->getGet('end_date') ?: date('Y-m-d');
        $guruId    = $this->request->getGet('guru_id') ? (int)$this->request->getGet('guru_id') : null;
        $shift     = $this->request->getGet('shift') ?: null;

        $jamMasukPagi  = $this->pengaturanModel->getSetting('jam_masuk_pagi', '06:30');
        $jamMasukSiang = $this->pengaturanModel->getSetting('jam_masuk_siang', '12:30');
        $toleransi     = (int)$this->pengaturanModel->getSetting('toleransi_menit', '0');

        $keterlambatanList = $this->absensiGuruModel->getKeterlambatanList($startDate, $endDate, $guruId, $shift);
        $guruList          = $this->guruModel->orderBy('nama_guru', 'ASC')->findAll();

        // Hitung statistik ringkas
        $totalTerlambat = count($keterlambatanList);
        $totalMenit = array_sum(array_column($keterlambatanList, 'menit_terlambat'));
        $rataMenit = $totalTerlambat > 0 ? round($totalMenit / $totalTerlambat, 1) : 0;

        return view('admin/keterlambatan_guru', [
            'title'             => 'Catatan Keterlambatan Guru',
            'startDate'         => $startDate,
            'endDate'           => $endDate,
            'guruId'            => $guruId,
            'shift'             => $shift,
            'jamMasukPagi'      => $jamMasukPagi,
            'jamMasukSiang'     => $jamMasukSiang,
            'toleransi'         => $toleransi,
            'keterlambatanList' => $keterlambatanList,
            'guruList'          => $guruList,
            'totalTerlambat'    => $totalTerlambat,
            'totalMenit'        => $totalMenit,
            'rataMenit'         => $rataMenit,
        ]);
    }

    public function simpanPengaturanJam()
    {
        $jamPagi   = $this->request->getPost('jam_masuk_pagi') ?: '06:30';
        $jamSiang  = $this->request->getPost('jam_masuk_siang') ?: '12:30';
        $toleransi = (int)($this->request->getPost('toleransi_menit') ?? 0);

        $this->pengaturanModel->setSetting('jam_masuk_pagi', $jamPagi, 'Standar batas jam masuk kedatangan guru shift pagi');
        $this->pengaturanModel->setSetting('jam_masuk_siang', $jamSiang, 'Standar batas jam masuk kedatangan guru shift siang');
        $this->pengaturanModel->setSetting('toleransi_menit', (string)$toleransi, 'Toleransi keterlambatan dalam menit');

        return redirect()->to('/admin/keterlambatan-guru')->with('success', "Pengaturan jam kedatangan berhasil disimpan! (Pagi: {$jamPagi}, Siang: {$jamSiang})");
    }

    public function exportKeterlambatanGuruExcel()
    {
        $startDate = $this->request->getGet('start_date') ?: date('Y-m-01');
        $endDate   = $this->request->getGet('end_date') ?: date('Y-m-d');
        $guruId    = $this->request->getGet('guru_id') ? (int)$this->request->getGet('guru_id') : null;
        $shift     = $this->request->getGet('shift') ?: null;

        $keterlambatanList = $this->absensiGuruModel->getKeterlambatanList($startDate, $endDate, $guruId, $shift);

        return $this->exportService->exportKeterlambatanGuruExcel($keterlambatanList, $startDate, $endDate);
    }
}


