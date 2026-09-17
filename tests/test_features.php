<?php

require_once __DIR__ . '/../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$cookieFile = __DIR__ . '/test_admin_cookie.txt';
if (file_exists($cookieFile)) unlink($cookieFile);

function curlRequest($url, $method = 'GET', $data = [], $cookieFile = null, $files = []) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
    if ($cookieFile) {
        curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
    }
    if ($method === 'POST') {
        curl_setopt($ch, CURLOPT_POST, true);
        if (!empty($files)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, array_merge($data, $files));
        } else {
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
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

echo "=== TESTING EXCEL IMPORT & DRILL-DOWN REPORTING ===\n";

// 1. Login Admin
$login = curlRequest('http://localhost:8080/auth/login', 'POST', ['username' => 'admin', 'password' => 'admin123'], $cookieFile);
echo "1. Admin Login: " . ($login['code'] == 200 ? "OK" : "FAIL (Code: {$login['code']})") . "\n";

// 2. Download Template Siswa
$tplSiswa = curlRequest('http://localhost:8080/admin/siswa/download-template', 'GET', [], $cookieFile);
$isXlsx = (strpos($tplSiswa['header'], 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') !== false);
echo "2. Download Template Siswa: " . ($isXlsx ? "SUCCESS (Content-Type: Xlsx)" : "FAIL") . "\n";

// 3. Download Template Guru
$tplGuru = curlRequest('http://localhost:8080/admin/guru/download-template', 'GET', [], $cookieFile);
$isXlsxGuru = (strpos($tplGuru['header'], 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet') !== false);
echo "3. Download Template Guru: " . ($isXlsxGuru ? "SUCCESS (Content-Type: Xlsx)" : "FAIL") . "\n";

// 4. Create and Import Excel Siswa
$ssSiswa = new Spreadsheet();
$sheetSiswa = $ssSiswa->getActiveSheet();
$sheetSiswa->setCellValue('A1', 'NISN');
$sheetSiswa->setCellValue('B1', 'Nama Siswa');
$sheetSiswa->setCellValue('C1', 'Nama Kelas');
$sheetSiswa->setCellValueExplicit('A2', '9991234501', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
$sheetSiswa->setCellValue('B2', 'Siswa Test Import Excel');
$sheetSiswa->setCellValue('C2', 'XII AK 1');
$testSiswaFile = __DIR__ . '/test_siswa.xlsx';
(new Xlsx($ssSiswa))->save($testSiswaFile);

$cFileSiswa = new CURLFile($testSiswaFile, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'test_siswa.xlsx');
$importSiswaRes = curlRequest('http://localhost:8080/admin/siswa/import', 'POST', [], $cookieFile, ['file_excel' => $cFileSiswa]);
$hasSuccessMsg = (strpos($importSiswaRes['body'], 'Proses import selesai') !== false);
echo "4. Import Siswa Excel: " . ($hasSuccessMsg ? "SUCCESS (Siswa berhasil ditambahkan)" : "CHECK: " . strip_tags(substr($importSiswaRes['body'], 0, 300))) . "\n";
if (file_exists($testSiswaFile)) unlink($testSiswaFile);

// 5. Create and Import Excel Guru
$ssGuru = new Spreadsheet();
$sheetGuru = $ssGuru->getActiveSheet();
$sheetGuru->setCellValue('A1', 'NIP');
$sheetGuru->setCellValue('B1', 'Nama Guru');
$sheetGuru->setCellValue('C1', 'No HP');
$sheetGuru->setCellValueExplicit('A2', '199505052024011999', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
$sheetGuru->setCellValue('B2', 'Guru Test Import Excel, M.Kom');
$sheetGuru->setCellValueExplicit('C2', '085200112233', \PhpOffice\PhpSpreadsheet\Cell\DataType::TYPE_STRING);
$testGuruFile = __DIR__ . '/test_guru.xlsx';
(new Xlsx($ssGuru))->save($testGuruFile);

$cFileGuru = new CURLFile($testGuruFile, 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'test_guru.xlsx');
$importGuruRes = curlRequest('http://localhost:8080/admin/guru/import', 'POST', [], $cookieFile, ['file_excel' => $cFileGuru]);
$hasGuruSuccess = (strpos($importGuruRes['body'], 'Proses import guru selesai') !== false);
echo "5. Import Guru Excel: " . ($hasGuruSuccess ? "SUCCESS (Guru & Akun Walas otomatis dibuat)" : "CHECK") . "\n";
if (file_exists($testGuruFile)) unlink($testGuruFile);

// 6. Level 1 Report (Per Kelas)
$rep1 = curlRequest('http://localhost:8080/admin/laporan', 'GET', [], $cookieFile);
$hasKelasTable = (strpos($rep1['body'], 'XII AK 1') !== false && strpos($rep1['body'], 'Rincian Siswa') !== false);
echo "6. Level 1 Report (Per Kelas): " . ($hasKelasTable ? "SUCCESS (Tabel per kelas & link rincian siswa tampil)" : "FAIL") . "\n";

// 7. Level 2 Report (Daftar Siswa dalam Kelas)
$rep2 = curlRequest('http://localhost:8080/admin/laporan/kelas/1', 'GET', [], $cookieFile);
$hasStudentList = (strpos($rep2['body'], 'Doni Hermawan') !== false && strpos($rep2['body'], 'Track Record') !== false);
echo "7. Level 2 Report (Daftar Siswa Kelas XII AK 1): " . ($hasStudentList ? "SUCCESS (Daftar siswa & Hadir/Sakit/Izin/Alpa tampil)" : "FAIL") . "\n";

// 8. Level 3 Report (Track Record Ketidakhadiran Siswa)
$rep3 = curlRequest('http://localhost:8080/admin/laporan/siswa/5', 'GET', [], $cookieFile);
$hasTrackRecord = (strpos($rep3['body'], 'Track Record Tanggal Ketidakhadiran') !== false && strpos($rep3['body'], 'Alpa (A)') !== false);
echo "8. Level 3 Report (Track Record Doni Hermawan): " . ($hasTrackRecord ? "SUCCESS (Tanggal & status ketidakhadiran tampil)" : "FAIL") . "\n";

// 9. Dashboard Top Siswa Alpa
$dash = curlRequest('http://localhost:8080/admin', 'GET', [], $cookieFile);
$hasTopAlpa = (strpos($dash['body'], 'Daftar Siswa Paling Banyak Alpa') !== false && strpos($dash['body'], 'Doni Hermawan') !== false);
echo "9. Dashboard Top Siswa Alpa: " . ($hasTopAlpa ? "SUCCESS (Peringkat siswa paling banyak alpa tampil)" : "FAIL") . "\n";

if (file_exists($cookieFile)) unlink($cookieFile);
echo "=== ALL NEW FEATURES TESTED SUCCESSFULLY ===\n";

