<?php

// Test script for verifying SI-ABSEN endpoints and RBAC flows
$ch = curl_init();
$cookieFile = __DIR__ . '/test_cookie.txt';
if (file_exists($cookieFile)) unlink($cookieFile);

function request($url, $method = 'GET', $data = [], $cookieFile = null) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    if ($cookieFile) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    }
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
    }
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($response, 0, $headerSize);
    $body = substr($response, $headerSize);
    
    preg_match('/Location:\s*([^\r\n]+)/i', $header, $matches);
    $location = $matches[1] ?? null;

    curl_close($ch);
    return ['code' => $httpCode, 'location' => $location, 'body' => $body];
}

echo "=== SI-ABSEN INTEGRATION TEST ===\n";

// 1. Check Login Page
$res = request('http://localhost:8080/auth/login');
echo "1. GET /auth/login: HTTP " . $res['code'] . " - " . (strpos($res['body'], 'Masuk SI-ABSEN') !== false ? "SUCCESS" : "FAIL") . "\n";

// 2. Login as walas1 (First Login = 1) -> Should redirect to /auth/ganti-password
$res = request('http://localhost:8080/auth/login', 'POST', ['username' => 'walas1', 'password' => 'walas123'], $cookieFile);
echo "2. POST /auth/login (walas1): HTTP " . $res['code'] . ", Location: " . $res['location'] . "\n";
$isForceChangeRedirect = (strpos($res['location'], 'ganti-password') !== false);
echo "   -> Force password change redirect: " . ($isForceChangeRedirect ? "PASSED" : "FAILED") . "\n";

// 3. Try to access /walas while is_first_login is 1 -> ForcePasswordChangeFilter should block and redirect to /auth/ganti-password
$res = request('http://localhost:8080/walas', 'GET', [], $cookieFile);
echo "3. GET /walas before password change: HTTP " . $res['code'] . ", Location: " . $res['location'] . "\n";
echo "   -> Intercepted by ForcePasswordChangeFilter: " . (strpos($res['location'], 'ganti-password') !== false ? "PASSED" : "FAILED") . "\n";

// 4. Update Password for walas1
$res = request('http://localhost:8080/auth/update-password', 'POST', [
    'password_baru' => 'walasbaru123',
    'konfirmasi_password' => 'walasbaru123'
], $cookieFile);
echo "4. POST /auth/update-password: HTTP " . $res['code'] . ", Location: " . $res['location'] . "\n";

// 5. Now access /walas dashboard -> should allow access and display XII AK 1 and student Doni Hermawan
$res = request('http://localhost:8080/walas', 'GET', [], $cookieFile);
echo "5. GET /walas after password change: HTTP " . $res['code'] . " - ";
$hasDoniAlert = (strpos($res['body'], 'Doni Hermawan') !== false);
$hasAlpaAlert = (strpos($res['body'], 'Peringatan: Siswa Memiliki Alpa') !== false);
echo ($hasDoniAlert && $hasAlpaAlert ? "PASSED (Alert Alpa > 3 Siswa Doni Hermawan tampil)" : "CHECK BODY") . "\n";

// 6. Test Admin Login
$adminCookie = __DIR__ . '/admin_cookie.txt';
if (file_exists($adminCookie)) unlink($adminCookie);
$res = request('http://localhost:8080/auth/login', 'POST', ['username' => 'admin', 'password' => 'admin123'], $adminCookie);
echo "6. POST /auth/login (admin): HTTP " . $res['code'] . ", Location: " . $res['location'] . "\n";

// 7. Test Admin Dashboard & Laporan
$res = request('http://localhost:8080/admin', 'GET', [], $adminCookie);
echo "7. GET /admin: HTTP " . $res['code'] . " - " . (strpos($res['body'], 'Dashboard Administrator') !== false ? "PASSED" : "FAILED") . "\n";

$res = request('http://localhost:8080/admin/laporan', 'GET', [], $adminCookie);
echo "8. GET /admin/laporan: HTTP " . $res['code'] . " - " . (strpos($res['body'], 'Rekapitulasi Presensi Global') !== false ? "PASSED" : "FAILED") . "\n";

// 8. Test PJ Kelas Login
$pjCookie = __DIR__ . '/pj_cookie.txt';
if (file_exists($pjCookie)) unlink($pjCookie);
$res = request('http://localhost:8080/auth/login', 'POST', ['username' => 'pj_xii_ak1', 'password' => 'pj123'], $pjCookie);
echo "9. POST /auth/login (pj_xii_ak1): HTTP " . $res['code'] . ", Location: " . $res['location'] . "\n";

$res = request('http://localhost:8080/pj', 'GET', [], $pjCookie);
echo "10. GET /pj: HTTP " . $res['code'] . " - " . (strpos($res['body'], 'XII AK 1') !== false ? "PASSED" : "FAILED") . "\n";

// Clean up cookies
if (file_exists($cookieFile)) unlink($cookieFile);
if (file_exists($adminCookie)) unlink($adminCookie);
if (file_exists($pjCookie)) unlink($pjCookie);
echo "=== ALL INTEGRATION TESTS FINISHED ===\n";
