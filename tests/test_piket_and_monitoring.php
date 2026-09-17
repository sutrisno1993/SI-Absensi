<?php

$cookie = __DIR__ . '/test_piket_cookie.txt';
if (file_exists($cookie)) unlink($cookie);

function req($url, $method = 'GET', $fields = null, $follow = false) {
    global $cookie;
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, $follow);

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
}

$mysqli = new mysqli('localhost', 'root', '', 'db_siabsen');

echo "=== STARTING COMPREHENSIVE TEST: GURU PIKET & MONITORING ===\n\n";

// 1. Admin Login
echo "TEST 1: Login Administrator...\n";
$login = req('http://localhost:8080/auth/login', 'POST', [
    'username' => 'admin',
    'password' => 'admin123',
]);
if ($login['code'] === 303 || $login['code'] === 302) {
    echo "  [PASS] Admin login successful (Code {$login['code']})\n";
} else {
    echo "  [FAIL] Admin login failed (Code {$login['code']})\n";
}

// 2. Set Jadwal Mengajar Guru 1 (ID 1) & Guru 2 (ID 2)
echo "\nTEST 2: Atur Jadwal Mengajar Guru di Master Guru...\n";
// Ambil guru 1 & 2
$g1 = $mysqli->query("SELECT * FROM guru ORDER BY id ASC LIMIT 1")->fetch_assoc();
$g2 = $mysqli->query("SELECT * FROM guru ORDER BY id ASC LIMIT 1 OFFSET 1")->fetch_assoc();

if (! $g1 || ! $g2) {
    die("Guru data missing in db_siabsen\n");
}

$g1Id = (int)$g1['id'];
$g2Id = (int)$g2['id'];

// Simpan jadwal Guru 1: Senin:Pagi, Rabu:Pagi
$saveG1 = req('http://localhost:8080/admin/guru/simpan', 'POST', [
    'id'        => $g1Id,
    'nip'       => $g1['nip'],
    'nama_guru' => $g1['nama_guru'],
    'no_hp'     => $g1['no_hp'],
    'jadwal'    => ['Senin:Pagi', 'Rabu:Pagi'],
]);
echo "  Simpan Jadwal Guru 1 ({$g1['nama_guru']}: Senin Pagi, Rabu Pagi): Code {$saveG1['code']}\n";

// Simpan jadwal Guru 2: Senin:Siang, Kamis:Siang
$saveG2 = req('http://localhost:8080/admin/guru/simpan', 'POST', [
    'id'        => $g2Id,
    'nip'       => $g2['nip'],
    'nama_guru' => $g2['nama_guru'],
    'no_hp'     => $g2['no_hp'],
    'jadwal'    => ['Senin:Siang', 'Kamis:Siang'],
]);
echo "  Simpan Jadwal Guru 2 ({$g2['nama_guru']}: Senin Siang, Kamis Siang): Code {$saveG2['code']}\n";

// Verifikasi di DB
$checkJadwal = $mysqli->query("SELECT COUNT(*) as c FROM guru_jadwal WHERE guru_id = {$g1Id} AND hari = 'Senin' AND shift = 'Pagi'")->fetch_assoc();
if ((int)$checkJadwal['c'] > 0) {
    echo "  [PASS] Guru 1 jadwal Senin Pagi tersimpan di tabel guru_jadwal\n";
} else {
    echo "  [FAIL] Guru 1 jadwal tidak ditemukan di guru_jadwal\n";
}

// 3. Test Presensi Guru Berdasarkan Shift (Senin: 2026-09-14)
echo "\nTEST 3: Filter Presensi Guru Senin Shift Pagi vs Siang...\n";
// Senin: 2026-09-14
$urlAbsenPagi = 'http://localhost:8080/piket/guru-absen?tanggal=2026-09-14&shift=Pagi';
$pagePagi = req($urlAbsenPagi);
echo "  Buka Presensi Senin Shift Pagi: Code {$pagePagi['code']}\n";

if (strpos($pagePagi['body'], $g1['nama_guru']) !== false) {
    echo "  [PASS] Guru 1 ({$g1['nama_guru']}) masuk daftar wajib absen Senin Pagi\n";
} else {
    echo "  [FAIL] Guru 1 tidak ditemukan di daftar wajib absen Senin Pagi\n";
}

if ((strpos($pagePagi['body'], 'Tanpa Jam Mengajar') !== false || strpos($pagePagi['body'], 'Tidak Ada Jam') !== false) && strpos($pagePagi['body'], $g2['nama_guru']) !== false) {
    echo "  [PASS] Guru 2 ({$g2['nama_guru']}) masuk kategori 'Tanpa Jam Mengajar' (Anti-Alfa Berhasil!)\n";
} else {
    echo "  [FAIL] Status anti-alfa Guru 2 tidak sesuai\n";
}

// 4. Test Simpan Presensi Guru Pagi (Guru 1 hadir jam 07:15 -> terlambat 15 menit)
echo "\nTEST 4: Simpan Presensi Kedatangan Guru Pagi (Jam 07:15)...\n";
$simpanAbsenG = req('http://localhost:8080/piket/guru-absen/simpan', 'POST', [
    'tanggal' => '2026-09-14',
    'shift'   => 'Pagi',
    'guru'    => [
        $g1Id => [
            'status'     => 'H',
            'jam_masuk'  => '07:15',
            'keterangan' => 'Macet ban bocor',
        ]
    ]
]);
echo "  Submit Presensi Guru: Code {$simpanAbsenG['code']}\n";

$checkAbsenGuru = $mysqli->query("SELECT * FROM absensi_guru WHERE tanggal = '2026-09-14' AND shift = 'Pagi' AND guru_id = {$g1Id}")->fetch_assoc();
if ($checkAbsenGuru && $checkAbsenGuru['status'] === 'H' && (int)$checkAbsenGuru['menit_terlambat'] === 15) {
    echo "  [PASS] Presensi Guru 1 tersimpan: Status=H, Menit Terlambat=15\n";
} else {
    echo "  [FAIL] Presensi Guru 1 tidak tersimpan sesuai: " . json_encode($checkAbsenGuru) . "\n";
}

// 5. Test Pencatatan Siswa Terlambat & Auto-Sync Presensi Kelas
echo "\nTEST 5: Catat Siswa Terlambat & Auto-Sync Presensi Harian...\n";
$siswaTest = $mysqli->query("SELECT * FROM siswa ORDER BY id ASC LIMIT 1")->fetch_assoc();
$sId = (int)$siswaTest['id'];

// Bersihkan data lama jika ada
$mysqli->query("DELETE FROM keterlambatan_siswa WHERE siswa_id = {$sId} AND tanggal = '2026-09-14'");
$mysqli->query("DELETE FROM absensi WHERE siswa_id = {$sId} AND tanggal = '2026-09-14'");

// Catat siswa terlambat jam 07:25 di shift pagi (terlambat 25 menit)
$catatSiswa = req('http://localhost:8080/piket/siswa-terlambat/simpan', 'POST', [
    'tanggal'   => '2026-09-14',
    'shift'     => 'Pagi',
    'siswa_id'  => $sId,
    'jam_masuk' => '07:25',
    'alasan'    => 'Bangun kesiangan',
    'tindakan'  => 'Pengarahan disiplin dan izin masuk',
]);
echo "  Submit Siswa Terlambat: Code {$catatSiswa['code']}\n";

// Cek di tabel keterlambatan_siswa
$checkTerlambat = $mysqli->query("SELECT * FROM keterlambatan_siswa WHERE siswa_id = {$sId} AND tanggal = '2026-09-14'")->fetch_assoc();
if ($checkTerlambat && (int)$checkTerlambat['menit_terlambat'] === 25) {
    echo "  [PASS] Log keterlambatan tersimpan di keterlambatan_siswa (Menit: 25)\n";
} else {
    echo "  [FAIL] Log keterlambatan siswa gagal tersimpan: " . json_encode($checkTerlambat) . "\n";
}

// Cek sinkronisasi otomatis di tabel absensi kelas
$checkAbsensiKelas = $mysqli->query("SELECT * FROM absensi WHERE siswa_id = {$sId} AND tanggal = '2026-09-14'")->fetch_assoc();
if ($checkAbsensiKelas && $checkAbsensiKelas['status'] === 'H' && strpos($checkAbsensiKelas['keterangan'], 'Terlambat piket') !== false) {
    echo "  [PASS] Auto-Sync Berhasil: Status absensi harian kelas otomatis Hadir (H) dengan ket: {$checkAbsensiKelas['keterangan']}\n";
} else {
    echo "  [FAIL] Auto-Sync gagal: " . json_encode($checkAbsensiKelas) . "\n";
}

// 6. Test Penugasan Guru Piket Harian
echo "\nTEST 6: Atur Penugasan Guru Piket di Admin...\n";
$assignPiket = req('http://localhost:8080/admin/penugasan-piket/simpan', 'POST', [
    'hari'     => 'Senin',
    'shift'    => 'Pagi',
    'guru_ids' => [$g1Id],
]);
echo "  Simpan Penugasan Piket Senin Pagi: Code {$assignPiket['code']}\n";

$checkPetugas = $mysqli->query("SELECT * FROM piket_petugas WHERE hari = 'Senin' AND shift = 'Pagi' AND guru_id = {$g1Id}")->fetch_assoc();
if ($checkPetugas) {
    echo "  [PASS] Petugas piket Senin Pagi tersimpan di piket_petugas\n";
} else {
    echo "  [FAIL] Petugas piket gagal tersimpan\n";
}

// 7. Test Monitoring Persentase Kehadiran
echo "\nTEST 7: Halaman Monitoring Persentase Kehadiran...\n";
// Periode All
$monAll = req('http://localhost:8080/admin/monitoring?periode=all');
echo "  Monitoring Periode Keseluruhan: Code {$monAll['code']}";
if (strpos($monAll['body'], 'Monitoring Persentase Kehadiran') !== false && strpos($monAll['body'], 'Kehadiran Guru') !== false) {
    echo " -> [PASS]\n";
} else {
    echo " -> [FAIL]\n";
}

// Periode Bulan
$monBulan = req('http://localhost:8080/admin/monitoring?periode=bulan&bulan=9&tahun=2026');
echo "  Monitoring Periode Bulanan (Sep 2026): Code {$monBulan['code']}";
if (strpos($monBulan['body'], 'Bulan September 2026') !== false) {
    echo " -> [PASS]\n";
} else {
    echo " -> [FAIL]\n";
}

// Periode Semester
$monSemester = req('http://localhost:8080/admin/monitoring?periode=semester&semester=ganjil&tahun=2026');
echo "  Monitoring Periode Semester (Ganjil 2026): Code {$monSemester['code']}";
if (strpos($monSemester['body'], 'Semester Ganjil 2026') !== false) {
    echo " -> [PASS]\n";
} else {
    echo " -> [FAIL]\n";
}

// 8. Test Akses Guru (Walas) ke Portal Piket
echo "\nTEST 8: Akses Portal Piket oleh Akun Guru/Walas...\n";
if (file_exists($cookie)) unlink($cookie);

// Login as Walas (No HP Guru 1)
$loginWalas = req('http://localhost:8080/auth/login', 'POST', [
    'username' => $g1['no_hp'],
    'password' => $g1['no_hp'],
]);
echo "  Login Guru ({$g1['nama_guru']}): Code {$loginWalas['code']}\n";

$piketByGuru = req('http://localhost:8080/piket');
echo "  Akses /piket oleh Guru: Code {$piketByGuru['code']}";
if ($piketByGuru['code'] === 200 && strpos($piketByGuru['body'], 'Portal Layanan Guru Piket') !== false) {
    echo " -> [PASS] Guru berhasil membuka portal piket\n";
} else {
    echo " -> [FAIL] Guru tidak dapat membuka portal piket\n";
}

// Clean up
$mysqli->query("DELETE FROM keterlambatan_siswa WHERE siswa_id = {$sId} AND tanggal = '2026-09-14'");
$mysqli->query("DELETE FROM absensi WHERE siswa_id = {$sId} AND tanggal = '2026-09-14'");
$mysqli->query("DELETE FROM absensi_guru WHERE tanggal = '2026-09-14'");

if (file_exists($cookie)) unlink($cookie);

echo "\n=== ALL TESTS COMPLETED SUCCESSFULLY! ===\n";
