<?php

function testLogin($username, $password, $expectedRedirect) {
    $cookie = __DIR__ . '/test_login_' . preg_replace('/[^a-zA-Z0-9]/', '_', $username) . '.txt';
    if (file_exists($cookie)) unlink($cookie);

    $ch = curl_init('http://localhost:8080/auth/login');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'username' => $username,
        'password' => $password,
    ]));
    curl_setopt($ch, CURLOPT_COOKIEJAR, $cookie);
    curl_setopt($ch, CURLOPT_COOKIEFILE, $cookie);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header = substr($response, 0, $headerSize);

    preg_match('/Location:\s*([^\r\n]+)/i', $header, $matches);
    $location = $matches[1] ?? '';

    // Now follow redirect to verify landing page
    if ($location) {
        curl_setopt($ch, CURLOPT_URL, $location);
        curl_setopt($ch, CURLOPT_POST, false);
        $landingPage = curl_exec($ch);
        $landingCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    } else {
        $landingPage = '';
        $landingCode = 0;
    }

    curl_close($ch);
    if (file_exists($cookie)) unlink($cookie);

    $isMatch = (strpos($location, $expectedRedirect) !== false);
    return [
        'code' => $httpCode,
        'location' => $location,
        'landingCode' => $landingCode,
        'isMatch' => $isMatch,
    ];
}

echo "=== TESTING PHONE NUMBER & NISN LOGIN CREDENTIALS ===\n";

// 1. Guru 1 (Walas XII AK 1) Login using Phone Number
$r1 = testLogin('081234567890', '081234567890', '/walas');
echo "1. Guru 1 (No. HP: 081234567890 / 081234567890): " . ($r1['isMatch'] ? "SUCCESS (Redirect to /walas)" : "FAIL (Location: {$r1['location']})") . "\n";

// 2. Guru 2 (Walas XI RPL 1) Login using Phone Number
$r2 = testLogin('081298765432', '081298765432', '/walas');
echo "2. Guru 2 (No. HP: 081298765432 / 081298765432): " . ($r2['isMatch'] ? "SUCCESS (Redirect to /walas)" : "FAIL (Location: {$r2['location']})") . "\n";

// 3. Murid 1 (Siswa XII AK 1) Login using NISN
$r3 = testLogin('0061234501', '0061234501', '/pj');
echo "3. Murid 1 (NISN: 0061234501 / 0061234501): " . ($r3['isMatch'] ? "SUCCESS (Redirect to /pj)" : "FAIL (Location: {$r3['location']})") . "\n";

// 4. Murid 2 (Siswa XI RPL 1) Login using NISN
$r4 = testLogin('0071234501', '0071234501', '/pj');
echo "4. Murid 2 (NISN: 0071234501 / 0071234501): " . ($r4['isMatch'] ? "SUCCESS (Redirect to /pj)" : "FAIL (Location: {$r4['location']})") . "\n";

// 5. Admin Login
$r5 = testLogin('admin', 'admin123', '/admin');
echo "5. Admin (admin / admin123): " . ($r5['isMatch'] ? "SUCCESS (Redirect to /admin)" : "FAIL (Location: {$r5['location']})") . "\n";

echo "=== ALL CREDENTIAL TESTS FINISHED ===\n";
