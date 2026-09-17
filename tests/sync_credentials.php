<?php

require_once __DIR__ . '/../app/Config/Paths.php';
require_once FCPATH . '../vendor/autoload.php';

$bootstrap = require_once SYSTEMPATH . 'Test/bootstrap.php';
$app = Config\Services::codeigniter();
$app->initialize();

$db = \Config\Database::connect();
$now = date('Y-m-d H:i:s');

echo "=== SINKRONISASI AKUN GURU (NO HP) & MURID (NISN) ===\n";

// 1. Sinkronisasi Guru -> Users (username: no_hp, password: no_hp)
$guruList = $db->table('guru')->get()->getResultArray();
foreach ($guruList as $g) {
    if (empty($g['no_hp'])) continue;
    $noHp = trim($g['no_hp']);

    // Cek apakah sudah ada user untuk guru ini
    $existingUser = $db->table('users')->where('role', 'walas')->where('ref_id', $g['id'])->get()->getRowArray();

    if ($existingUser) {
        $db->table('users')->where('id', $existingUser['id'])->update([
            'username'       => $noHp,
            'password_hash'  => password_hash($noHp, PASSWORD_BCRYPT),
            'is_first_login' => 0,
            'updated_at'     => $now,
        ]);
        echo "Guru updated: {$g['nama_guru']} -> username: {$noHp} | password: {$noHp}\n";
    } else {
        $db->table('users')->insert([
            'username'       => $noHp,
            'password_hash'  => password_hash($noHp, PASSWORD_BCRYPT),
            'role'           => 'walas',
            'is_first_login' => 0,
            'ref_id'         => $g['id'],
            'created_at'     => $now,
            'updated_at'     => $now,
        ]);
        echo "Guru created: {$g['nama_guru']} -> username: {$noHp} | password: {$noHp}\n";
    }
}

// 2. Sinkronisasi Siswa -> Users (username: nisn, password: nisn)
$siswaList = $db->table('siswa')->get()->getResultArray();
foreach ($siswaList as $s) {
    if (empty($s['nisn'])) continue;
    $nisn = trim($s['nisn']);

    $existingUser = $db->table('users')->where('username', $nisn)->get()->getRowArray();

    if ($existingUser) {
        $db->table('users')->where('id', $existingUser['id'])->update([
            'password_hash'  => password_hash($nisn, PASSWORD_BCRYPT),
            'role'           => 'pj_kelas',
            'ref_id'         => $s['kelas_id'],
            'is_first_login' => 0,
            'updated_at'     => $now,
        ]);
        echo "Siswa updated: {$s['nama_siswa']} -> username: {$nisn} | password: {$nisn}\n";
    } else {
        $db->table('users')->insert([
            'username'       => $nisn,
            'password_hash'  => password_hash($nisn, PASSWORD_BCRYPT),
            'role'           => 'pj_kelas',
            'is_first_login' => 0,
            'ref_id'         => $s['kelas_id'],
            'created_at'     => $now,
            'updated_at'     => $now,
        ]);
        echo "Siswa created: {$s['nama_siswa']} -> username: {$nisn} | password: {$nisn}\n";
    }
}

// 3. Pastikan Admin tetap aktif
$adminUser = $db->table('users')->where('username', 'admin')->get()->getRowArray();
if ($adminUser) {
    $db->table('users')->where('id', $adminUser['id'])->update([
        'password_hash' => password_hash('admin123', PASSWORD_BCRYPT),
    ]);
    echo "Admin confirmed: admin / admin123\n";
}

echo "=== SINKRONISASI SELESAI ===\n";
