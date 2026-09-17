<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Landing page -> redirect ke login
$routes->get('/', 'AuthController::login');

// Auth Routes
$routes->group('auth', static function ($routes) {
    $routes->get('login', 'AuthController::login');
    $routes->post('login', 'AuthController::attemptLogin');
    $routes->get('logout', 'AuthController::logout');
    $routes->get('ganti-password', 'AuthController::gantiPassword', ['filter' => 'auth']);
    $routes->post('update-password', 'AuthController::updatePassword', ['filter' => 'auth']);
});

// Admin Route Group (Filter: auth, role:admin)
$routes->group('admin', ['filter' => ['auth', 'role:admin']], static function ($routes) {
    $routes->get('/', 'AdminController::index');
    
    // Master Guru & Import
    $routes->get('guru', 'AdminController::guru');
    $routes->post('guru/simpan', 'AdminController::simpanGuru');
    $routes->get('guru/hapus/(:num)', 'AdminController::hapusGuru/$1');
    $routes->get('guru/download-template', 'AdminController::downloadTemplateGuru');
    $routes->post('guru/import', 'AdminController::importGuru');
    
    // Master Kelas
    $routes->get('kelas', 'AdminController::kelas');
    $routes->post('kelas/simpan', 'AdminController::simpanKelas');
    $routes->get('kelas/hapus/(:num)', 'AdminController::hapusKelas/$1');
    
    // Master Siswa & Import
    $routes->get('siswa', 'AdminController::siswa');
    $routes->post('siswa/simpan', 'AdminController::simpanSiswa');
    $routes->post('siswa/toggle-status/(:num)', 'AdminController::toggleStatusSiswa/$1');
    $routes->get('siswa/hapus/(:num)', 'AdminController::hapusSiswa/$1');
    $routes->get('siswa/download-template', 'AdminController::downloadTemplateSiswa');
    $routes->post('siswa/import', 'AdminController::importSiswa');
    
    // Pengguna / User & Reset Password
    $routes->get('users', 'AdminController::users');
    $routes->post('users/simpan', 'AdminController::simpanUser');
    $routes->get('users/reset-password/(:num)', 'AdminController::resetPassword/$1');
    $routes->get('users/hapus/(:num)', 'AdminController::hapusUser/$1');

    // Laporan Presensi Hierarkis (Drill-Down) & Export Excel
    $routes->get('laporan', 'AdminController::rekapLaporan');
    $routes->get('laporan/export-global-excel', 'AdminController::exportGlobalExcel');
    $routes->get('laporan/export-penindakan-excel', 'AdminController::exportPenindakanExcel');
    $routes->get('laporan/kelas/(:num)', 'AdminController::laporanKelas/$1');
    $routes->get('laporan/export-kelas-excel/(:num)', 'AdminController::exportKelasExcel/$1');
    $routes->get('laporan/siswa/(:num)', 'AdminController::laporanSiswa/$1');
    $routes->get('laporan/export-siswa-excel/(:num)', 'AdminController::exportSiswaExcel/$1');

    // Penugasan Guru Piket
    $routes->get('penugasan-piket', 'AdminController::penugasanPiket');
    $routes->post('penugasan-piket/simpan', 'AdminController::simpanPenugasanPiket');

    // Jadwal Mengajar Guru (Hari & Shift)
    $routes->get('jadwal-guru', 'AdminController::jadwalGuru');
    $routes->post('jadwal-guru/simpan', 'AdminController::simpanJadwalGuru');

    // Monitoring Persentase Kehadiran Guru & Siswa
    $routes->get('monitoring', 'AdminController::monitoring');
    $routes->get('monitoring/export-guru-excel', 'AdminController::exportGuruExcel');

    // Riwayat Pembinaan Siswa (Anak Bermasalah)
    $routes->get('pembinaan', 'AdminController::pembinaan');
    $routes->post('pembinaan/simpan', 'AdminController::simpanPembinaan');
    $routes->get('pembinaan/hapus/(:num)', 'AdminController::hapusPembinaan/$1');

    // Catatan Keterlambatan Guru & Pengaturan Jam Masuk
    $routes->get('keterlambatan-guru', 'AdminController::keterlambatanGuru');
    $routes->post('keterlambatan-guru/pengaturan', 'AdminController::simpanPengaturanJam');
    $routes->get('keterlambatan-guru/export-excel', 'AdminController::exportKeterlambatanGuruExcel');
});

// Guru Piket Route Group (Filter: auth, role:admin,walas,guru_piket)
$routes->group('piket', ['filter' => ['auth', 'role:admin,walas,guru_piket']], static function ($routes) {
    $routes->get('/', 'PiketController::index');
    $routes->get('guru-absen', 'PiketController::guruAbsen');
    $routes->post('guru-absen/simpan', 'PiketController::simpanAbsensiGuru');
    $routes->get('siswa-terlambat', 'PiketController::siswaTerlambat');
    $routes->post('siswa-terlambat/simpan', 'PiketController::simpanSiswaTerlambat');
    $routes->get('siswa-terlambat/hapus/(:num)', 'PiketController::hapusSiswaTerlambat/$1');
    $routes->get('rekap', 'PiketController::rekap');
    $routes->get('rekap/export-excel', 'PiketController::exportRekapPiketExcel');
    $routes->get('export-rekap-excel', 'PiketController::exportRekapPiketExcel');
});

// Walas Route Group (Filter: auth, role:walas, force_change_pwd)
$routes->group('walas', ['filter' => ['auth', 'role:walas', 'force_change_pwd']], static function ($routes) {
    $routes->get('/', 'WalasController::index');
    $routes->get('absen', 'WalasController::absen');
    $routes->post('absen/simpan', 'WalasController::simpanAbsen');
    $routes->get('rekap', 'WalasController::rekap');
    $routes->get('rekap/export-excel', 'WalasController::exportRekapExcel');
    $routes->get('export-rekap-excel', 'WalasController::exportRekapExcel');
    $routes->post('pembinaan/simpan', 'WalasController::simpanPembinaan');
    
    // Manajemen Akun Khusus Perwakilan Kelas
    $routes->get('akun-siswa', 'WalasController::akunSiswa');
    $routes->post('akun-siswa/generate', 'WalasController::generateAkunKelas');
    $routes->post('akun-siswa/reset-kelas', 'WalasController::resetPasswordAkunKelas');
    $routes->post('akun-siswa/reset/(:num)', 'WalasController::resetPasswordSiswa/$1');
    $routes->post('akun-siswa/reset-semua', 'WalasController::resetSemuaPassword');
});

// PJ Kelas Route Group (Filter: auth, role:pj_kelas)
$routes->group('pj', ['filter' => ['auth', 'role:pj_kelas']], static function ($routes) {
    $routes->get('/', 'PjKelasController::index');
    $routes->get('absen', 'PjKelasController::index');
    $routes->post('simpan', 'PjKelasController::simpan');
    $routes->post('upload-foto', 'PjKelasController::uploadFoto');
    $routes->get('rekap', 'PjKelasController::rekap');
});

// Halaman Publik Live Presensi Orang Tua Murid (Tanpa Login)
$routes->get('live-presensi/(:segment)', 'LivePresensiController::show/$1');
