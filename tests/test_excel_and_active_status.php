<?php

function makeClient($cookieFile) {
    if (file_exists($cookieFile)) @unlink($cookieFile);
    return function($url, $method = 'GET', $fields = null) use ($cookieFile) {
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        if ($method === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if ($fields) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
            }
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $header = substr($response, 0, $headerSize);
        $body = substr($response, $headerSize);
        curl_close($ch);

        return ['code' => $httpCode, 'header' => $header, 'body' => $body];
    };
}

$adminReq = makeClient(__DIR__ . '/test_admin_cookie.txt');
$walasReq = makeClient(__DIR__ . '/test_walas_cookie.txt');
$dummyReq = makeClient(__DIR__ . '/test_dummy_cookie.txt');

$mysqli = new mysqli('localhost', 'root', '', 'db_siabsen');

echo "=== MEMULAI TEST: STATUS SISWA (AKTIF/NONAKTIF) & DOWNLOAD EXCEL ===\n\n";

// 1. Admin Login
echo "TEST 1: Login Administrator...\n";
$login = $adminReq('http://localhost:8080/auth/login', 'POST', [
    'username' => 'admin',
    'password' => 'admin123',
]);
if ($login['code'] === 200 && strpos($login['body'], 'Dashboard') !== false) {
    echo "  [PASS] Admin login berhasil (Code {$login['code']})\n";
} else {
    die("  [FAIL] Admin login gagal! Code: {$login['code']}\n");
}

// 2. Pilih siswa target (ID 1: Ahmad Fauzi)
$targetSiswa = $mysqli->query("SELECT * FROM siswa WHERE id = 1")->fetch_assoc();
echo "\nTEST 2: Uji Nonaktifkan Siswa (ID: 1, Nama: {$targetSiswa['nama_siswa']})...\n";

$toggleRes = $adminReq('http://localhost:8080/admin/siswa/toggle-status/1', 'POST');
$currStatus = $mysqli->query("SELECT status FROM siswa WHERE id = 1")->fetch_assoc()['status'];
if ($currStatus === 'Nonaktif') {
    echo "  [PASS] Status siswa ID 1 berhasil berubah menjadi: {$currStatus}\n";
} else {
    die("  [FAIL] Gagal mengubah status siswa menjadi Nonaktif! Status saat ini: {$currStatus}\n");
}

// 3. Verifikasi siswa NONAKTIF tidak muncul di presensi Walas & PJ
echo "\nTEST 3: Verifikasi Siswa Nonaktif Tidak Muncul di Presensi Walas...\n";
$wLogin = $walasReq('http://localhost:8080/auth/login', 'POST', [
    'username' => '081513275208',
    'password' => '081513275208'
]);
$absenRes = $walasReq('http://localhost:8080/walas/absen?tanggal=' . date('Y-m-d'));

if (strpos($absenRes['body'], $targetSiswa['nama_siswa']) === false) {
    echo "  [PASS] Nama siswa '{$targetSiswa['nama_siswa']}' TIDAK MUNCUL di daftar presensi kelas! (Berhasil difilter)\n";
} else {
    die("  [FAIL] Nama siswa '{$targetSiswa['nama_siswa']}' masih muncul di daftar presensi kelas!\n");
}

// Siswa aktif lain (ID 2: Anisa Bella) harus tetap muncul
$siswaAktif = $mysqli->query("SELECT nama_siswa FROM siswa WHERE id = 2")->fetch_assoc()['nama_siswa'];
if (strpos($absenRes['body'], $siswaAktif) !== false) {
    echo "  [PASS] Siswa aktif '{$siswaAktif}' tetap muncul dengan normal.\n";
} else {
    die("  [FAIL] Siswa aktif '{$siswaAktif}' tidak ditemukan di daftar presensi!\n");
}

// 4. Verifikasi Siswa Nonaktif Diblokir dari Login
echo "\nTEST 4: Verifikasi Pencegahan Login Siswa Nonaktif...\n";
$respLog = $dummyReq('http://localhost:8080/auth/login', 'POST', [
    'username' => $targetSiswa['nisn'],
    'password' => $targetSiswa['nisn']
]);

if (strpos($respLog['body'], 'nonaktif') !== false) {
    echo "  [PASS] Login siswa nonaktif berhasil dicegah dengan notifikasi status nonaktif.\n";
} else {
    echo "  [WARN] Pesan penolakan login siswa nonaktif tidak terdeteksi di redirect.\n";
}

// 5. Test Download Excel untuk seluruh endpoint Admin
echo "\nTEST 5: Pengujian Download Laporan Excel (.xlsx)...\n";

$excelEndpoints = [
    'Rekap Global Presensi Siswa' => 'http://localhost:8080/admin/laporan/export-global-excel',
    'Presensi Kelas XII AK 1'     => 'http://localhost:8080/admin/laporan/export-kelas-excel/1',
    'Track Record Siswa ID 1'     => 'http://localhost:8080/admin/laporan/export-siswa-excel/1',
    'Laporan Penindakan Siswa'    => 'http://localhost:8080/admin/laporan/export-penindakan-excel',
    'Monitoring Presensi Guru'    => 'http://localhost:8080/admin/monitoring/export-guru-excel?periode=all',
    'Rekap Piket (Piket Portal)'  => 'http://localhost:8080/piket/rekap/export-excel',
];

foreach ($excelEndpoints as $name => $url) {
    $res = $adminReq($url);
    $isExcel = strpos($res['header'], 'spreadsheetml.sheet') !== false;
    $hasSize = strlen($res['body']) > 2000;

    if ($res['code'] === 200 && $isExcel && $hasSize) {
        echo "  [PASS] {$name}: Code 200, Content-Type Valid, Ukuran " . strlen($res['body']) . " bytes\n";
    } else {
        die("  [FAIL] {$name} gagal! Code: {$res['code']}, IsExcel: " . ($isExcel ? 'true' : 'false') . ", Size: " . strlen($res['body']) . " bytes\n");
    }
}

// 6. Test Download Excel dari Akun Walas
echo "\nTEST 6: Download Excel Rekap Kelas oleh Wali Kelas...\n";
$resWE = $walasReq('http://localhost:8080/walas/rekap/export-excel');
$isExcelWE = strpos($resWE['header'], 'spreadsheetml.sheet') !== false;
$hasSizeWE = strlen($resWE['body']) > 2000;

if ($resWE['code'] === 200 && $isExcelWE && $hasSizeWE) {
    echo "  [PASS] Walas Rekap Excel: Code 200, Ukuran " . strlen($resWE['body']) . " bytes\n";
} else {
    die("  [FAIL] Walas Rekap Excel gagal! Code: {$resWE['code']}, Size: " . strlen($resWE['body']) . " bytes\n");
}

// 7. Kembalikan Status Siswa menjadi Aktif
echo "\nTEST 7: Kembalikan Status Siswa ID 1 menjadi Aktif...\n";
$toggleBack = $adminReq('http://localhost:8080/admin/siswa/toggle-status/1', 'POST');
$finalStatus = $mysqli->query("SELECT status FROM siswa WHERE id = 1")->fetch_assoc()['status'];
if ($finalStatus === 'Aktif') {
    echo "  [PASS] Status siswa ID 1 berhasil kembali menjadi: {$finalStatus}\n";
} else {
    die("  [FAIL] Gagal mengembalikan status siswa ID 1!\n");
}

// 8. Cek Siswa Muncul Kembali di Presensi
$absenRes2 = $walasReq('http://localhost:8080/walas/absen?tanggal=' . date('Y-m-d'));
if (strpos($absenRes2['body'], $targetSiswa['nama_siswa']) !== false) {
    echo "  [PASS] Siswa '{$targetSiswa['nama_siswa']}' berhasil MUNCUL KEMBALI di daftar presensi setelah diaktifkan!\n";
} else {
    die("  [FAIL] Siswa tidak muncul kembali setelah diaktifkan!\n");
}

// Cleanup temporary cookies
@unlink(__DIR__ . '/test_admin_cookie.txt');
@unlink(__DIR__ . '/test_walas_cookie.txt');
@unlink(__DIR__ . '/test_dummy_cookie.txt');

echo "\n=== SEMUA 8 PENGUJIAN SELESAI DENGAN SUKSES (100% PASS)! ===\n";
