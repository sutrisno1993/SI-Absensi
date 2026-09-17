<?php

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\GuruModel;
use App\Models\KelasModel;

class AuthController extends BaseController
{
    protected $userModel;
    protected $guruModel;
    protected $kelasModel;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->guruModel = new GuruModel();
        $this->kelasModel = new KelasModel();
    }

    public function login()
    {
        if ($this->session->get('logged_in')) {
            return $this->redirectByRole($this->session->get('role'));
        }

        return view('auth/login', [
            'title' => 'Login SI-ABSEN',
        ]);
    }

    public function attemptLogin()
    {
        $rules = [
            'username' => 'required|trim',
            'password' => 'required',
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('error', 'Username dan password wajib diisi.');
        }

        $username = trim($this->request->getPost('username'));
        $password = trim($this->request->getPost('password'));

        $user = $this->userModel->findByUsername($username);

        // Fallback 1: Cek Guru berdasarkan No HP jika belum ada user
        if (! $user) {
            $guru = $this->guruModel->where('no_hp', $username)->first();
            if ($guru) {
                // Buat user otomatis
                $newUserId = $this->userModel->insert([
                    'username'       => $username,
                    'password_hash'  => password_hash($username, PASSWORD_BCRYPT),
                    'role'           => 'walas',
                    'is_first_login' => 0,
                    'ref_id'         => $guru['id'],
                ]);
                $user = $this->userModel->find($newUserId);
            }
        }

        // Fallback 2: Cek Siswa berdasarkan NISN jika belum ada user
        if (! $user) {
            $siswaModel = new \App\Models\SiswaModel();
            $siswa = $siswaModel->where('nisn', $username)->first();
            if ($siswa) {
                if ($siswa['status'] === 'Nonaktif') {
                    return redirect()->back()->withInput()->with('error', 'Akun siswa Anda berstatus nonaktif. Silakan hubungi pihak sekolah/kesiswaan.');
                }
                // Buat user otomatis untuk siswa
                $newUserId = $this->userModel->insert([
                    'username'       => $username,
                    'password_hash'  => password_hash($username, PASSWORD_BCRYPT),
                    'role'           => 'pj_kelas',
                    'is_first_login' => 0,
                    'ref_id'         => $siswa['kelas_id'],
                ]);
                $user = $this->userModel->find($newUserId);
            }
        }

        if (! $user) {
            return redirect()->back()->withInput()->with('error', 'Username atau password salah. (Guru gunakan No. HP, Murid gunakan NISN)');
        }

        // Cek jika user bertipe pj_kelas apakah siswanya berstatus Nonaktif
        if ($user['role'] === 'pj_kelas') {
            $siswaModel = new \App\Models\SiswaModel();
            $siswa = $siswaModel->where('nisn', $user['username'])->first();
            if ($siswa && $siswa['status'] === 'Nonaktif') {
                return redirect()->back()->withInput()->with('error', 'Akun siswa Anda berstatus nonaktif. Silakan hubungi pihak sekolah/kesiswaan.');
            }
        }

        if (! password_verify($password, $user['password_hash'])) {
            return redirect()->back()->withInput()->with('error', 'Username atau password salah. (Guru gunakan No. HP, Murid gunakan NISN)');
        }

        // Ambil display info nama & kelas
        $namaLengkap = $user['username'];
        $namaKelas   = null;

        if (($user['role'] === 'walas' || $user['role'] === 'guru_piket') && ! empty($user['ref_id'])) {
            $guru = $this->guruModel->find($user['ref_id']);
            if ($guru) {
                $namaLengkap = $guru['nama_guru'];
            }
            $kelas = $this->kelasModel->getKelasByWalas($user['ref_id']);
            if ($kelas) {
                $namaKelas = $kelas['nama_kelas'];
            }
        } elseif ($user['role'] === 'pj_kelas') {
            $siswaModel = new \App\Models\SiswaModel();
            $siswa = $siswaModel->where('nisn', $user['username'])->first();
            if ($siswa) {
                $namaLengkap = $siswa['nama_siswa'];
                $kelas = $this->kelasModel->find($siswa['kelas_id']);
                if ($kelas) {
                    $namaKelas = $kelas['nama_kelas'];
                }
            } elseif (! empty($user['ref_id'])) {
                $kelas = $this->kelasModel->find($user['ref_id']);
                if ($kelas) {
                    $namaKelas = $kelas['nama_kelas'];
                    $namaLengkap = 'PJ ' . $kelas['nama_kelas'];
                }
            }
        } elseif ($user['role'] === 'admin') {
            $namaLengkap = 'Administrator';
        }

        // Set session
        $this->session->set([
            'user_id'        => $user['id'],
            'username'       => $user['username'],
            'role'           => $user['role'],
            'ref_id'         => $user['ref_id'],
            'is_first_login' => (int)$user['is_first_login'],
            'nama_lengkap'   => $namaLengkap,
            'nama_kelas'     => $namaKelas,
            'logged_in'      => true,
        ]);

        return $this->redirectByRole($user['role'])->with('success', 'Selamat datang, ' . $namaLengkap . '!');
    }

    public function gantiPassword()
    {
        if (! $this->session->get('logged_in')) {
            return redirect()->to('/auth/login');
        }

        return view('auth/ganti_password', [
            'title' => 'Ganti Password',
        ]);
    }

    public function updatePassword()
    {
        if (! $this->session->get('logged_in')) {
            return redirect()->to('/auth/login');
        }

        $rules = [
            'password_baru' => [
                'rules'  => 'required|min_length[6]',
                'errors' => [
                    'required'   => 'Password baru wajib diisi.',
                    'min_length' => 'Password baru minimal harus 6 karakter.',
                ],
            ],
            'konfirmasi_password' => [
                'rules'  => 'required|matches[password_baru]',
                'errors' => [
                    'required' => 'Konfirmasi password wajib diisi.',
                    'matches'  => 'Konfirmasi password tidak cocok dengan password baru.',
                ],
            ],
        ];

        if (! $this->validate($rules)) {
            return redirect()->back()->withInput()->with('errors', $this->validator->getErrors());
        }

        $userId = (int)$this->session->get('user_id');
        $newPassword = $this->request->getPost('password_baru');

        $this->userModel->updatePassword($userId, $newPassword);

        // Update session is_first_login ke 0
        $this->session->set('is_first_login', 0);

        return $this->redirectByRole($this->session->get('role'))->with('success', 'Password berhasil diperbarui!');
    }

    public function logout()
    {
        $this->session->destroy();
        return redirect()->to('/auth/login')->with('info', 'Anda telah berhasil logout.');
    }

    protected function redirectByRole(string $role)
    {
        switch ($role) {
            case 'admin':
                return redirect()->to('/admin');
            case 'walas':
                return redirect()->to('/walas');
            case 'guru_piket':
                return redirect()->to('/piket');
            case 'pj_kelas':
                return redirect()->to('/pj');
            default:
                return redirect()->to('/auth/login');
        }
    }
}
