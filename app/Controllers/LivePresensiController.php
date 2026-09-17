<?php

namespace App\Controllers;

use App\Models\AbsensiModel;
use App\Models\KelasModel;
use App\Models\SiswaModel;
use App\Models\GuruModel;
use App\Models\PresensiKelasHarianModel;

class LivePresensiController extends BaseController
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
     * Halaman Publik Pemantauan Presensi Realtime untuk Orang Tua Murid (Tanpa Login)
     */
    public function show($token)
    {
        $token = trim($token);
        if (empty($token)) {
            return $this->renderErrorPage('Tautan presensi tidak valid.');
        }

        $sesi = $this->presensiKelasHarianModel->getByToken($token);
        if (! $sesi) {
            return $this->renderErrorPage('Tautan presensi live tidak ditemukan atau telah kedaluwarsa.');
        }

        $kelasId = (int)$sesi['kelas_id'];
        $tanggal = $sesi['tanggal'];

        // Ambil data presensi seluruh siswa di kelas ini pada tanggal sesi
        $daftarSiswa = $this->absensiModel->getAbsensiByDateAndKelas($tanggal, $kelasId);

        // Hitung statistik kehadiran
        $countH = 0; $countS = 0; $countI = 0; $countA = 0;
        foreach ($daftarSiswa as $s) {
            $st = $s['status'] ?? 'H';
            if ($st === 'H') $countH++;
            elseif ($st === 'S') $countS++;
            elseif ($st === 'I') $countI++;
            elseif ($st === 'A') $countA++;
        }

        $totalSiswa = count($daftarSiswa);
        $persenHadir = $totalSiswa > 0 ? round(($countH / $totalSiswa) * 100, 1) : 0;

        return view('public/live_presensi', [
            'title'       => 'Live Presensi Siswa - ' . $sesi['nama_kelas'],
            'sesi'        => $sesi,
            'tanggal'     => $tanggal,
            'daftarSiswa' => $daftarSiswa,
            'totalSiswa'  => $totalSiswa,
            'countH'      => $countH,
            'countS'      => $countS,
            'countI'      => $countI,
            'countA'      => $countA,
            'persenHadir' => $persenHadir,
        ]);
    }

    protected function renderErrorPage(string $message)
    {
        return '<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tautan Tidak Ditemukan - SI-ABSEN</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <style>body { font-family: "Plus Jakarta Sans", sans-serif; background: #070a13; color: #f8fafc; }</style>
</head>
<body class="d-flex align-items-center justify-content-center min-vh-100 p-3">
    <div class="card bg-dark border-secondary border-opacity-50 text-center p-4 shadow-lg rounded-4" style="max-width: 480px;">
        <div class="text-warning display-1 mb-3">⚠️</div>
        <h4 class="fw-bold mb-2">Tautan Tidak Tersedia</h4>
        <p class="text-muted small mb-4">' . esc($message) . '</p>
        <a href="' . site_url('auth/login') . '" class="btn btn-outline-primary rounded-pill px-4">Kembali ke Beranda</a>
    </div>
</body>
</html>';
    }
}
