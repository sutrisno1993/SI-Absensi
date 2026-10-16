<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

class ExportService
{
    /**
     * 1. Export Rekapitulasi Global Presensi Seluruh Kelas
     */
    public function exportGlobalPresensi(array $rekapGlobal, string $periodeTitle = 'Semua Waktu (Keseluruhan)'): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Global');

        // Header Dokumen
        $sheet->setCellValue('A1', 'SISTEM INFORMASI PRESENSI & PEMBINAAN SISWA (SI-ABSEN)');
        $sheet->setCellValue('A2', 'REKAPITULASI PRESENSI GLOBAL - ' . strtoupper($periodeTitle));
        $sheet->setCellValue('A3', 'Dicetak pada: ' . date('d F Y, H:i') . ' WIB');

        $sheet->mergeCells('A1:I1');
        $sheet->mergeCells('A2:I2');
        $sheet->mergeCells('A3:I3');

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1E293B'));
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('4338CA'));
        $sheet->getStyle('A3')->getFont()->setSize(9)->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('64748B'));

        // Table Header
        $headers = ['No', 'Nama Kelas', 'Shift', 'Wali Kelas', 'Hadir (H)', 'Sakit (S)', 'Izin (I)', 'Alpa (A)', '% Kehadiran'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '5', $h);
            $col++;
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4338CA']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];
        $sheet->getStyle('A5:I5')->applyFromArray($headerStyle);
        $sheet->getRowDimension(5)->setRowHeight(26);

        // Data Rows
        $row = 6;
        $totalH = 0; $totalS = 0; $totalI = 0; $totalA = 0;
        foreach ($rekapGlobal as $idx => $item) {
            $h = (int)($item['total_h'] ?? 0);
            $s = (int)($item['total_s'] ?? 0);
            $i = (int)($item['total_i'] ?? 0);
            $a = (int)($item['total_a'] ?? 0);
            $totalRecords = $h + $s + $i + $a;
            $persen = $totalRecords > 0 ? round(($h / $totalRecords) * 100, 1) : 0;

            $totalH += $h; $totalS += $s; $totalI += $i; $totalA += $a;

            $sheet->setCellValue('A' . $row, $idx + 1);
            $sheet->setCellValue('B' . $row, $item['nama_kelas']);
            $sheet->setCellValue('C' . $row, $item['shift'] ?? 'Pagi');
            $sheet->setCellValue('D' . $row, $item['nama_walas'] ?? 'Belum Ditentukan');
            $sheet->setCellValue('E' . $row, $h);
            $sheet->setCellValue('F' . $row, $s);
            $sheet->setCellValue('G' . $row, $i);
            $sheet->setCellValue('H' . $row, $a);
            $sheet->setCellValue('I' . $row, $persen . '%');

            if ($row % 2 === 1) {
                $sheet->getStyle("A{$row}:I{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');
            }

            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("E{$row}:I{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $row++;
        }

        // Summary Total Row
        $grandTotal = $totalH + $totalS + $totalI + $totalA;
        $grandPersen = $grandTotal > 0 ? round(($totalH / $grandTotal) * 100, 1) : 0;

        $sheet->setCellValue('A' . $row, 'TOTAL SELURUH SEKOLAH');
        $sheet->mergeCells("A{$row}:D{$row}");
        $sheet->setCellValue('E' . $row, $totalH);
        $sheet->setCellValue('F' . $row, $totalS);
        $sheet->setCellValue('G' . $row, $totalI);
        $sheet->setCellValue('H' . $row, $totalA);
        $sheet->setCellValue('I' . $row, $grandPersen . '%');

        $footerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => '0F172A']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];
        $sheet->getStyle("A{$row}:I{$row}")->applyFromArray($footerStyle);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getRowDimension($row)->setRowHeight(24);

        // Border Styling
        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CBD5E1'],
                ],
            ],
        ];
        $sheet->getStyle("A5:I{$row}")->applyFromArray($borderStyle);

        foreach (range('A', 'I') as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        $this->outputFile($spreadsheet, 'Laporan_Presensi_Global_Sekolah_' . date('Ymd_His') . '.xlsx');
    }

    /**
     * 2. Export Laporan Presensi Rombel Kelas (Siswa per Siswa)
     */
    public function exportKelasPresensi(array $kelas, array $rekapSiswa, $labelPeriodeOrBulan = null, ?string $tahun = null): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Presensi ' . substr($kelas['nama_kelas'], 0, 20));

        $namaBulan = [
            '1' => 'Januari', '01' => 'Januari', '2' => 'Februari', '02' => 'Februari',
            '3' => 'Maret', '03' => 'Maret', '4' => 'April', '04' => 'April',
            '5' => 'Mei', '05' => 'Mei', '6' => 'Juni', '06' => 'Juni',
            '7' => 'Juli', '07' => 'Juli', '8' => 'Agustus', '08' => 'Agustus',
            '9' => 'September', '09' => 'September', '10' => 'Oktober',
            '11' => 'November', '12' => 'Desember'
        ];

        if (is_string($labelPeriodeOrBulan) && !empty($labelPeriodeOrBulan) && !is_numeric($labelPeriodeOrBulan)) {
            $periodeStr = $labelPeriodeOrBulan;
        } elseif ($labelPeriodeOrBulan && isset($namaBulan[$labelPeriodeOrBulan])) {
            $periodeStr = 'Bulan ' . $namaBulan[$labelPeriodeOrBulan] . ' ' . ($tahun ?: date('Y'));
        } else {
            $periodeStr = 'Seluruh Periode (' . ($tahun ?: date('Y')) . ')';
        }

        // Header Dokumen
        $sheet->setCellValue('A1', 'REKAPITULASI PRESENSI SISWA KELAS ' . strtoupper($kelas['nama_kelas']));
        $sheet->setCellValue('A2', 'Periode: ' . $periodeStr . ' | Shift: ' . ($kelas['shift'] ?? 'Pagi') . ' | Wali Kelas: ' . ($kelas['nama_guru'] ?? '-'));
        $sheet->setCellValue('A3', 'Dicetak pada: ' . date('d F Y, H:i') . ' WIB');

        $sheet->mergeCells('A1:K1');
        $sheet->mergeCells('A2:K2');
        $sheet->mergeCells('A3:K3');

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('0F172A'));
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(10)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('4338CA'));
        $sheet->getStyle('A3')->getFont()->setSize(9)->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('64748B'));

        // Table Header
        $headers = ['No', 'NISN', 'Nama Siswa', 'L/P', 'Status', 'Hadir (H)', 'Sakit (S)', 'Izin (I)', 'Alpa (A)', 'Terlambat (T)', '% Hadir'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '5', $h);
            $col++;
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '059669']], // Emerald Green
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];
        $sheet->getStyle('A5:K5')->applyFromArray($headerStyle);
        $sheet->getRowDimension(5)->setRowHeight(25);

        // Data Rows
        $row = 6;
        $totalH = 0; $totalS = 0; $totalI = 0; $totalA = 0; $totalT = 0;
        foreach ($rekapSiswa as $idx => $s) {
            $h = (int)($s['total_h'] ?? 0);
            $sk = (int)($s['total_s'] ?? 0);
            $i = (int)($s['total_i'] ?? 0);
            $a = (int)($s['total_a'] ?? 0);
            $t = (int)($s['total_t'] ?? 0);
            $totalPresensi = $h + $sk + $i + $a;
            $persen = $totalPresensi > 0 ? round(($h / $totalPresensi) * 100, 1) : 0;
            $statusSiswa = $s['status_siswa'] ?? 'Aktif';
            $gender = $s['jenis_kelamin'] ?? 'L';

            $totalH += $h; $totalS += $sk; $totalI += $i; $totalA += $a; $totalT += $t;

            $sheet->setCellValue('A' . $row, $idx + 1);
            $sheet->setCellValueExplicit('B' . $row, $s['nisn'], DataType::TYPE_STRING);
            $sheet->setCellValue('C' . $row, $s['nama_siswa']);
            $sheet->setCellValue('D' . $row, $gender);
            $sheet->setCellValue('E' . $row, $statusSiswa);
            $sheet->setCellValue('F' . $row, $h);
            $sheet->setCellValue('G' . $row, $sk);
            $sheet->setCellValue('H' . $row, $i);
            $sheet->setCellValue('I' . $row, $a);
            $sheet->setCellValue('J' . $row, $t);
            $sheet->setCellValue('K' . $row, $persen . '%');

            if ($row % 2 === 1) {
                $sheet->getStyle("A{$row}:K{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');
            }

            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$row}:E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("F{$row}:K{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            // Jika siswa nonaktif beri warna teks abu-abu
            if ($statusSiswa === 'Nonaktif') {
                $sheet->getStyle("A{$row}:K{$row}")->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('94A3B8'));
            }

            $row++;
        }

        // Summary Total Row
        $grandTotal = $totalH + $totalS + $totalI + $totalA;
        $grandPersen = $grandTotal > 0 ? round(($totalH / $grandTotal) * 100, 1) : 0;

        $sheet->setCellValue('A' . $row, 'TOTAL KELAS');
        $sheet->mergeCells("A{$row}:E{$row}");
        $sheet->setCellValue('F' . $row, $totalH);
        $sheet->setCellValue('G' . $row, $totalS);
        $sheet->setCellValue('H' . $row, $totalI);
        $sheet->setCellValue('I' . $row, $totalA);
        $sheet->setCellValue('J' . $row, $totalT);
        $sheet->setCellValue('K' . $row, $grandPersen . '%');

        $footerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => '0F172A']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];
        $sheet->getStyle("A{$row}:K{$row}")->applyFromArray($footerStyle);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CBD5E1'],
                ],
            ],
        ];
        $sheet->getStyle("A5:K{$row}")->applyFromArray($borderStyle);

        foreach (range('A', 'K') as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        $sanitizedKelas = preg_replace('/[^A-Za-z0-9_\-]/', '_', $kelas['nama_kelas']);
        $this->outputFile($spreadsheet, 'Laporan_Presensi_' . $sanitizedKelas . '_' . date('Ymd') . '.xlsx');
    }

    /**
     * 3. Export Track Record Kehadiran Individu Siswa
     */
    public function exportSiswaTrackRecord(array $siswa, array $stats, array $trackRecord, array $riwayatPembinaan): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Riwayat Siswa');

        // Header Profil Siswa
        $sheet->setCellValue('A1', 'LEMBAR TRACK RECORD KEHADIRAN & PEMBINAAN SISWA');
        $sheet->mergeCells('A1:F1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('0F172A'));

        $sheet->setCellValue('A3', 'Nama Siswa:');
        $sheet->setCellValue('B3', $siswa['nama_siswa']);
        $sheet->setCellValue('D3', 'Kelas / Rombel:');
        $sheet->setCellValue('E3', $siswa['nama_kelas']);

        $sheet->setCellValue('A4', 'NISN:');
        $sheet->setCellValueExplicit('B4', $siswa['nisn'], DataType::TYPE_STRING);
        $sheet->setCellValue('D4', 'Wali Kelas:');
        $sheet->setCellValue('E4', $siswa['nama_walas'] ?? '-');

        $sheet->setCellValue('A5', 'Status Siswa:');
        $sheet->setCellValue('B5', $siswa['status'] ?? 'Aktif');
        $sheet->setCellValue('D5', 'Dicetak Tanggal:');
        $sheet->setCellValue('E5', date('d/m/Y H:i') . ' WIB');

        $sheet->getStyle('A3:A5')->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('64748B'));
        $sheet->getStyle('D3:D5')->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('64748B'));

        // Box Statistik Kehadiran
        $sheet->setCellValue('A7', 'RINGKASAN KEHADIRAN:');
        $sheet->mergeCells('A7:F7');
        $sheet->getStyle('A7')->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('4338CA'));

        $statHeaders = ['Hadir (H)', 'Sakit (S)', 'Izin (I)', 'Alpa (A)', 'Total Pertemuan', '% Kehadiran'];
        $statCols = ['A', 'B', 'C', 'D', 'E', 'F'];
        foreach ($statHeaders as $k => $sh) {
            $sheet->setCellValue($statCols[$k] . '8', $sh);
        }
        $sheet->getStyle('A8:F8')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E293B']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $totalRec = ($stats['total_h'] ?? 0) + ($stats['total_s'] ?? 0) + ($stats['total_i'] ?? 0) + ($stats['total_a'] ?? 0);
        $persen = $totalRec > 0 ? round((($stats['total_h'] ?? 0) / $totalRec) * 100, 1) : 0;

        $sheet->setCellValue('A9', (int)($stats['total_h'] ?? 0));
        $sheet->setCellValue('B9', (int)($stats['total_s'] ?? 0));
        $sheet->setCellValue('C9', (int)($stats['total_i'] ?? 0));
        $sheet->setCellValue('D9', (int)($stats['total_a'] ?? 0));
        $sheet->setCellValue('E9', $totalRec);
        $sheet->setCellValue('F9', $persen . '%');
        $sheet->getStyle('A9:F9')->applyFromArray([
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F1F5F9']],
        ]);

        // Detail Riwayat Ketidakhadiran
        $sheet->setCellValue('A11', 'RIWAYAT KETIDAKHADIRAN (SAKIT / IZIN / ALPA)');
        $sheet->mergeCells('A11:E11');
        $sheet->getStyle('A11')->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('DC2626'));

        $sheet->setCellValue('A12', 'No');
        $sheet->setCellValue('B12', 'Tanggal');
        $sheet->setCellValue('C12', 'Status');
        $sheet->setCellValue('D12', 'Keterangan / Alasan');
        $sheet->mergeCells('D12:F12');
        $sheet->getStyle('A12:F12')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DC2626']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $row = 13;
        if (empty($trackRecord)) {
            $sheet->setCellValue('A' . $row, 'Tidak ada riwayat ketidakhadiran (Disiplin / Hadir Terus)');
            $sheet->mergeCells("A{$row}:F{$row}");
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("A{$row}")->getFont()->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('059669'));
            $row++;
        } else {
            foreach ($trackRecord as $idx => $tr) {
                $sheet->setCellValue('A' . $row, $idx + 1);
                $sheet->setCellValue('B' . $row, date('d/m/Y', strtotime($tr['tanggal'])));
                $sheet->setCellValue('C' . $row, $tr['status']);
                $sheet->setCellValue('D' . $row, $tr['keterangan'] ?: '-');
                $sheet->mergeCells("D{$row}:F{$row}");

                $sheet->getStyle("A{$row}:C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $row++;
            }
        }

        // Riwayat Pembinaan Siswa
        $row += 2;
        $sheet->setCellValue('A' . $row, 'CATATAN & TINDAKAN PEMBINAAN SISWA');
        $sheet->mergeCells("A{$row}:F{$row}");
        $sheet->getStyle("A{$row}")->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('D97706'));
        $row++;

        $sheet->setCellValue('A' . $row, 'No');
        $sheet->setCellValue('B' . $row, 'Tanggal Tindakan');
        $sheet->setCellValue('C' . $row, 'Jenis Tindakan');
        $sheet->setCellValue('D' . $row, 'Catatan & Solusi');
        $sheet->mergeCells("D{$row}:F{$row}");
        $sheet->getStyle("A{$row}:F{$row}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D97706']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $row++;

        if (empty($riwayatPembinaan)) {
            $sheet->setCellValue('A' . $row, 'Belum ada catatan penindakan pembinaan.');
            $sheet->mergeCells("A{$row}:F{$row}");
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("A{$row}")->getFont()->setItalic(true);
            $row++;
        } else {
            foreach ($riwayatPembinaan as $idx => $pb) {
                $sheet->setCellValue('A' . $row, $idx + 1);
                $sheet->setCellValue('B' . $row, date('d/m/Y', strtotime($pb['tanggal_tindakan'])));
                $sheet->setCellValue('C' . $row, $pb['jenis_tindakan']);
                $sheet->setCellValue('D' . $row, $pb['catatan_pembinaan']);
                $sheet->mergeCells("D{$row}:F{$row}");

                $sheet->getStyle("A{$row}:C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $row++;
            }
        }

        foreach (range('A', 'F') as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        $sanitizedNama = preg_replace('/[^A-Za-z0-9_\-]/', '_', $siswa['nama_siswa']);
        $this->outputFile($spreadsheet, 'Track_Record_' . $sanitizedNama . '_' . date('Ymd') . '.xlsx');
    }

    /**
     * 4. Export Laporan Presensi Guru
     */
    public function exportPresensiGuru(array $guruList, string $periodeTitle = 'Semua Periode'): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Presensi Guru');

        // Header
        $sheet->setCellValue('A1', 'LAPORAN REKAPITULASI PRESENSI KEDATANGAN GURU');
        $sheet->setCellValue('A2', 'Periode: ' . $periodeTitle . ' | SI-ABSEN');
        $sheet->setCellValue('A3', 'Dicetak pada: ' . date('d F Y, H:i') . ' WIB');

        $sheet->mergeCells('A1:J1');
        $sheet->mergeCells('A2:J2');
        $sheet->mergeCells('A3:J3');

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('0F172A'));
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(10)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('4338CA'));
        $sheet->getStyle('A3')->getFont()->setSize(9)->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('64748B'));

        // Table Header
        $headers = ['No', 'NIP', 'Nama Guru', 'No. WhatsApp', 'Jadwal Mengajar', 'Wajib Hadir', 'Hadir (H)', 'Terlambat', 'Sakit/Izin/Alfa', '% Kehadiran'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '5', $h);
            $col++;
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '3B82F6']], // Indigo Blue
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];
        $sheet->getStyle('A5:J5')->applyFromArray($headerStyle);
        $sheet->getRowDimension(5)->setRowHeight(25);

        $row = 6;
        $totWajib = 0; $totHadir = 0; $totLate = 0;
        foreach ($guruList as $idx => $g) {
            $wajib = (int)($g['total_wajib'] ?? ($g['total_sesi'] ?? 0));
            $hadir = (int)($g['total_hadir'] ?? 0);
            $late  = (int)($g['total_terlambat'] ?? 0);
            $sia   = (int)($g['total_sia'] ?? (($g['total_sakit'] ?? ($g['total_s'] ?? 0)) + ($g['total_izin'] ?? ($g['total_i'] ?? 0)) + ($g['total_alfa'] ?? ($g['total_a'] ?? 0))));
            $pct   = isset($g['persen_kehadiran']) ? (float)$g['persen_kehadiran'] : (isset($g['persen_hadir']) ? (float)$g['persen_hadir'] : ($wajib > 0 ? round(($hadir / $wajib) * 100, 1) : 100));

            $totWajib += $wajib;
            $totHadir += $hadir;
            $totLate  += $late;

            $jadwalStr = is_array($g['jadwal'] ?? null) 
                ? implode(', ', array_map(fn($j) => $j['hari'] . ' (' . $j['shift'] . ')', $g['jadwal']))
                : ($g['jadwal_string'] ?? '-');

            $sheet->setCellValue('A' . $row, $idx + 1);
            $sheet->setCellValueExplicit('B' . $row, $g['nip'] ?? '-', DataType::TYPE_STRING);
            $sheet->setCellValue('C' . $row, $g['nama_guru']);
            $sheet->setCellValueExplicit('D' . $row, $g['no_hp'] ?? '-', DataType::TYPE_STRING);
            $sheet->setCellValue('E' . $row, $jadwalStr ?: '-');
            $sheet->setCellValue('F' . $row, $wajib);
            $sheet->setCellValue('G' . $row, $hadir);
            $sheet->setCellValue('H' . $row, $late);
            $sheet->setCellValue('I' . $row, $sia);
            $sheet->setCellValue('J' . $row, $pct . '%');

            if ($row % 2 === 1) {
                $sheet->getStyle("A{$row}:J{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');
            }

            $sheet->getStyle("A{$row}:B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("F{$row}:J{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $row++;
        }

        // Footer Total
        $grandPct = $totWajib > 0 ? round(($totHadir / $totWajib) * 100, 1) : 0;
        $sheet->setCellValue('A' . $row, 'TOTAL SELURUH GURU');
        $sheet->mergeCells("A{$row}:E{$row}");
        $sheet->setCellValue('F' . $row, $totWajib);
        $sheet->setCellValue('G' . $row, $totHadir);
        $sheet->setCellValue('H' . $row, $totLate);
        $sheet->setCellValue('I' . $row, '-');
        $sheet->setCellValue('J' . $row, $grandPct . '%');

        $footerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => '0F172A']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2E8F0']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];
        $sheet->getStyle("A{$row}:J{$row}")->applyFromArray($footerStyle);
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

        $borderStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CBD5E1'],
                ],
            ],
        ];
        $sheet->getStyle("A5:J{$row}")->applyFromArray($borderStyle);

        foreach (range('A', 'J') as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        $this->outputFile($spreadsheet, 'Laporan_Presensi_Guru_' . date('Ymd_His') . '.xlsx');
    }

    /**
     * 5. Export Laporan Penindakan Siswa (Multi-Sheet: Tindakan Pembinaan + Siswa Terlambat Piket)
     */
    public function exportPenindakanSiswa(array $pembinaanList, array $terlambatList): void
    {
        $spreadsheet = new Spreadsheet();

        // SHEET 1: Tindakan Pembinaan Siswa
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Tindakan Pembinaan');

        $sheet1->setCellValue('A1', 'LAPORAN REKAPITULASI TINDAKAN PEMBINAAN SISWA');
        $sheet1->setCellValue('A2', 'Dicetak pada: ' . date('d F Y, H:i') . ' WIB | SI-ABSEN');
        $sheet1->mergeCells('A1:G1');
        $sheet1->mergeCells('A2:G2');
        $sheet1->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $sheet1->getStyle('A2')->getFont()->setItalic(true)->setSize(9)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('64748B'));

        $headers1 = ['No', 'Tanggal', 'NISN', 'Nama Siswa', 'Kelas', 'Jenis Pembinaan', 'Catatan & Solusi'];
        $col = 'A';
        foreach ($headers1 as $h) {
            $sheet1->setCellValue($col . '4', $h);
            $col++;
        }
        $sheet1->getStyle('A4:G4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DC2626']], // Red
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $row1 = 5;
        if (empty($pembinaanList)) {
            $sheet1->setCellValue('A5', 'Belum ada data tindakan pembinaan.');
            $sheet1->mergeCells('A5:G5');
            $sheet1->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row1++;
        } else {
            foreach ($pembinaanList as $idx => $pb) {
                $sheet1->setCellValue('A' . $row1, $idx + 1);
                $sheet1->setCellValue('B' . $row1, date('d/m/Y', strtotime($pb['tanggal_tindakan'])));
                $sheet1->setCellValueExplicit('C' . $row1, $pb['nisn'] ?? '-', DataType::TYPE_STRING);
                $sheet1->setCellValue('D' . $row1, $pb['nama_siswa'] ?? '-');
                $sheet1->setCellValue('E' . $row1, $pb['nama_kelas'] ?? '-');
                $sheet1->setCellValue('F' . $row1, $pb['jenis_tindakan'] ?? '-');
                $sheet1->setCellValue('G' . $row1, $pb['catatan_pembinaan'] ?? '-');

                $sheet1->getStyle("A{$row1}:C{$row1}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet1->getStyle("E{$row1}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $row1++;
            }
        }
        foreach (range('A', 'G') as $c) {
            $sheet1->getColumnDimension($c)->setAutoSize(true);
        }

        // SHEET 2: Rekap Keterlambatan Siswa di Piket
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Keterlambatan Piket');

        $sheet2->setCellValue('A1', 'REKAPITULASI SISWA TERLAMBAT DI MEJA PIKET');
        $sheet2->setCellValue('A2', 'Dicetak pada: ' . date('d F Y, H:i') . ' WIB | SI-ABSEN');
        $sheet2->mergeCells('A1:I1');
        $sheet2->mergeCells('A2:I2');
        $sheet2->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $sheet2->getStyle('A2')->getFont()->setItalic(true)->setSize(9)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('64748B'));

        $headers2 = ['No', 'Tanggal', 'Jam Kedatangan', 'NISN', 'Nama Siswa', 'Kelas', 'Keterlambatan', 'Alasan', 'Tindakan Piket'];
        $col = 'A';
        foreach ($headers2 as $h) {
            $sheet2->setCellValue($col . '4', $h);
            $col++;
        }
        $sheet2->getStyle('A4:I4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'D97706']], // Amber
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $row2 = 5;
        if (empty($terlambatList)) {
            $sheet2->setCellValue('A5', 'Belum ada data keterlambatan siswa.');
            $sheet2->mergeCells('A5:I5');
            $sheet2->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row2++;
        } else {
            foreach ($terlambatList as $idx => $tb) {
                $jamDisplay = !empty($tb['jam_masuk']) ? substr($tb['jam_masuk'], 0, 5) . ' WIB' : (!empty($tb['jam_kedatangan']) ? substr($tb['jam_kedatangan'], 0, 5) . ' WIB' : '-');
                $sheet2->setCellValue('A' . $row2, $idx + 1);
                $sheet2->setCellValue('B' . $row2, date('d/m/Y', strtotime($tb['tanggal'])));
                $sheet2->setCellValue('C' . $row2, $jamDisplay);
                $sheet2->setCellValueExplicit('D' . $row2, $tb['nisn'] ?? '-', DataType::TYPE_STRING);
                $sheet2->setCellValue('E' . $row2, $tb['nama_siswa'] ?? '-');
                $sheet2->setCellValue('F' . $row2, $tb['nama_kelas'] ?? '-');
                $sheet2->setCellValue('G' . $row2, '+' . (int)($tb['menit_terlambat'] ?? 0) . ' menit');
                $sheet2->setCellValue('H' . $row2, $tb['alasan'] ?? '-');
                $sheet2->setCellValue('I' . $row2, $tb['tindakan'] ?? $tb['tindakan_pembinaan'] ?? '-');

                $sheet2->getStyle("A{$row2}:D{$row2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet2->getStyle("F{$row2}:G{$row2}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $row2++;
            }
        }
        foreach (range('A', 'I') as $c) {
            $sheet2->getColumnDimension($c)->setAutoSize(true);
        }

        // Set active sheet to first sheet
        $spreadsheet->setActiveSheetIndex(0);

        $this->outputFile($spreadsheet, 'Laporan_Penindakan_Siswa_' . date('Ymd_His') . '.xlsx');
    }

    /**
     * Export Rekapitulasi Keterlambatan Guru ke Excel
     */
    public function exportKeterlambatanGuruExcel(array $keterlambatanList, string $startDate, string $endDate): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Keterlambatan Guru');

        // Header Dokumen
        $sheet->setCellValue('A1', 'SISTEM INFORMASI PRESENSI & PEMBINAAN (SI-ABSEN)');
        $sheet->setCellValue('A2', 'REKAPITULASI CATATAN KETERLAMBATAN GURU');
        $sheet->setCellValue('A3', 'Periode: ' . date('d/m/Y', strtotime($startDate)) . ' s/d ' . date('d/m/Y', strtotime($endDate)) . ' | Dicetak: ' . date('d F Y, H:i') . ' WIB');

        $sheet->mergeCells('A1:J1');
        $sheet->mergeCells('A2:J2');
        $sheet->mergeCells('A3:J3');

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1E293B'));
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('B91C1C'));
        $sheet->getStyle('A3')->getFont()->setSize(9)->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('64748B'));

        // Table Header
        $headers = ['No', 'Tanggal', 'Hari', 'NIP', 'Nama Guru', 'Shift', 'Jam Masuk', 'Terlambat', 'Keterangan / Alasan', 'Dicatat Oleh'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '5', $h);
            $col++;
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'B91C1C']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];
        $sheet->getStyle('A5:J5')->applyFromArray($headerStyle);
        $sheet->getRowDimension(5)->setRowHeight(26);

        $row = 6;
        $totalMenit = 0;
        if (empty($keterlambatanList)) {
            $sheet->setCellValue('A6', 'Tidak ada catatan keterlambatan guru pada periode filter yang dipilih.');
            $sheet->mergeCells('A6:J6');
            $sheet->getStyle('A6')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle('A6')->getFont()->setItalic(true);
            $row = 7;
        } else {
            foreach ($keterlambatanList as $idx => $item) {
                $menit = (int)($item['menit_terlambat'] ?? 0);
                $totalMenit += $menit;
                $jamDisplay = !empty($item['jam_masuk']) ? substr($item['jam_masuk'], 0, 5) . ' WIB' : '-';

                $sheet->setCellValue('A' . $row, $idx + 1);
                $sheet->setCellValue('B' . $row, date('d/m/Y', strtotime($item['tanggal'])));
                $sheet->setCellValue('C' . $row, $item['hari'] ?? date('l', strtotime($item['tanggal'])));
                $sheet->setCellValueExplicit('D' . $row, $item['nip'] ?? '-', DataType::TYPE_STRING);
                $sheet->setCellValue('E' . $row, $item['nama_guru'] ?? '-');
                $sheet->setCellValue('F' . $row, $item['shift'] ?? '-');
                $sheet->setCellValue('G' . $row, $jamDisplay);
                $sheet->setCellValue('H' . $row, '+' . $menit . ' menit');
                $sheet->setCellValue('I' . $row, $item['keterangan'] ?? '-');
                $sheet->setCellValue('J' . $row, $item['dicatat_oleh_user'] ?? 'Piket');

                $sheet->getStyle("A{$row}:D{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("F{$row}:H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getStyle("J{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $row++;
            }

            // Total row
            $sheet->setCellValue('A' . $row, 'TOTAL KETERLAMBATAN');
            $sheet->mergeCells("A{$row}:G{$row}");
            $sheet->setCellValue('H' . $row, '+' . $totalMenit . ' menit');
            $sheet->getStyle("A{$row}:J{$row}")->getFont()->setBold(true);
            $sheet->getStyle("A{$row}:J{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FEE2E2');
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
            $sheet->getStyle("H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        // Auto size
        foreach (range('A', 'J') as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        $this->outputFile($spreadsheet, 'Rekap_Keterlambatan_Guru_' . date('Ymd_His') . '.xlsx');
    }

    /**
     * 7. Export Monitoring Presensi Siswa & Performa Per Kelas
     */
    public function exportSiswaMonitoringExcel(array $statSiswa, array $performaKelas, string $periodeTitle = 'Semua Periode'): void
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Monitoring Siswa');

        // Header Dokumen
        $sheet->setCellValue('A1', 'LAPORAN MONITORING PERSENTASE KEHADIRAN SISWA PER KELAS');
        $sheet->setCellValue('A2', 'Periode: ' . $periodeTitle . ' | SI-ABSEN');
        $sheet->setCellValue('A3', 'Dicetak pada: ' . date('d F Y, H:i') . ' WIB');

        $sheet->mergeCells('A1:J1');
        $sheet->mergeCells('A2:J2');
        $sheet->mergeCells('A3:J3');

        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(13)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('0F172A'));
        $sheet->getStyle('A2')->getFont()->setBold(true)->setSize(10)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('4338CA'));
        $sheet->getStyle('A3')->getFont()->setSize(9)->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('64748B'));

        // Table Header
        $headers = ['No', 'Nama Kelas', 'Shift', 'Jumlah Siswa', 'Total Sesi', 'Hadir (H)', 'Sakit (S)', 'Izin (I)', 'Alpa (A)', '% Kehadiran'];
        $col = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($col . '5', $h);
            $col++;
        }

        $headerStyle = [
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4338CA']], // Indigo
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ];
        $sheet->getStyle('A5:J5')->applyFromArray($headerStyle);
        $sheet->getRowDimension(5)->setRowHeight(25);

        $row = 6;
        $totSiswa = 0; $totSesi = 0; $totH = 0; $totS = 0; $totI = 0; $totA = 0;
        foreach ($performaKelas as $idx => $pk) {
            $siswa = (int)($pk['total_siswa'] ?? 0);
            $sesi  = (int)($pk['total_sesi'] ?? 0);
            $h     = (int)($pk['total_hadir'] ?? 0);
            $s     = (int)($pk['total_sakit'] ?? 0);
            $i     = (int)($pk['total_izin'] ?? 0);
            $a     = (int)($pk['total_alfa'] ?? 0);
            $pct   = (float)($pk['persen_hadir'] ?? ($sesi > 0 ? round(($h / $sesi) * 100, 1) : 100));

            $totSiswa += $siswa;
            $totSesi  += $sesi;
            $totH     += $h;
            $totS     += $s;
            $totI     += $i;
            $totA     += $a;

            $sheet->setCellValue('A' . $row, $idx + 1);
            $sheet->setCellValue('B' . $row, $pk['nama_kelas']);
            $sheet->setCellValue('C' . $row, $pk['shift'] ?? 'Pagi');
            $sheet->setCellValue('D' . $row, $siswa);
            $sheet->setCellValue('E' . $row, $sesi);
            $sheet->setCellValue('F' . $row, $h);
            $sheet->setCellValue('G' . $row, $s);
            $sheet->setCellValue('H' . $row, $i);
            $sheet->setCellValue('I' . $row, $a);
            $sheet->setCellValue('J' . $row, $pct . '%');

            if ($row % 2 === 1) {
                $sheet->getStyle("A{$row}:J{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('F8FAFC');
            }

            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C{$row}:J{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $row++;
        }

        // Summary Total
        $grandPct = $totSesi > 0 ? round(($totH / $totSesi) * 100, 1) : 0;
        $sheet->setCellValue('A' . $row, 'TOTAL SELURUH SISWA SEKOLAH');
        $sheet->mergeCells("A{$row}:C{$row}");
        $sheet->setCellValue('D' . $row, $totSiswa);
        $sheet->setCellValue('E' . $row, $totSesi);
        $sheet->setCellValue('F' . $row, $totH);
        $sheet->setCellValue('G' . $row, $totS);
        $sheet->setCellValue('H' . $row, $totI);
        $sheet->setCellValue('I' . $row, $totA);
        $sheet->setCellValue('J' . $row, $grandPct . '%');

        $sheet->getStyle("A{$row}:J{$row}")->getFont()->setBold(true);
        $sheet->getStyle("A{$row}:J{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E2E8F0');
        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
        $sheet->getStyle("D{$row}:J{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getRowDimension($row)->setRowHeight(24);

        // Borders
        $sheet->getStyle("A5:J{$row}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['rgb' => 'CBD5E1'],
                ],
            ],
        ]);

        foreach (range('A', 'J') as $c) {
            $sheet->getColumnDimension($c)->setAutoSize(true);
        }

        $this->outputFile($spreadsheet, 'Monitoring_Presensi_Siswa_' . date('Ymd_His') . '.xlsx');
    }

    /**
     * Export Evaluasi Rombel Rapat Akhir Semester (Dinamika L/P, Siswa Bermasalah, Mutasi, dan Presensi Lengkap)
     */
    public function exportEvaluasiSemester(
        array $kelas, 
        array $dinamika, 
        array $rekapSiswa, 
        array $siswaBermasalah, 
        string $labelPeriode = 'Semester 1', 
        string $tahun = '2026/2027'
    ): void {
        $spreadsheet = new Spreadsheet();
        
        // ==========================
        // SHEET 1: DINAMIKA & MUTASI
        // ==========================
        $sheet1 = $spreadsheet->getActiveSheet();
        $sheet1->setTitle('Dinamika Rombel');

        // Header
        $sheet1->setCellValue('A1', 'EVALUASI ROMBEL RAPAT AKHIR SEMESTER');
        $sheet1->setCellValue('A2', 'KELAS: ' . strtoupper($kelas['nama_kelas'] ?? '-') . ' | WALI KELAS: ' . strtoupper($kelas['nama_walas'] ?? '-') . ' | ' . strtoupper($labelPeriode) . ' T.A ' . $tahun);
        $sheet1->setCellValue('A3', 'Dicetak pada: ' . date('d F Y, H:i') . ' WIB');
        $sheet1->mergeCells('A1:G1');
        $sheet1->mergeCells('A2:G2');
        $sheet1->mergeCells('A3:G3');

        $sheet1->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('1E293B'));
        $sheet1->getStyle('A2')->getFont()->setBold(true)->setSize(11)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('4338CA'));
        $sheet1->getStyle('A3')->getFont()->setSize(9)->setItalic(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('64748B'));

        // Dinamika Summary Box
        $sheet1->setCellValue('A5', 'RINGKASAN DINAMIKA ROMBONGAN BELAJAR');
        $sheet1->mergeCells('A5:D5');
        $sheet1->getStyle('A5')->getFont()->setBold(true);

        $sheet1->setCellValue('A6', 'Kondisi Rombel');
        $sheet1->setCellValue('B6', 'Laki-Laki (L)');
        $sheet1->setCellValue('C6', 'Perempuan (P)');
        $sheet1->setCellValue('D6', 'Total Murid');
        $sheet1->getStyle('A6:D6')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '4338CA']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $awal = $dinamika['awal'] ?? ['total' => 0, 'l' => 0, 'p' => 0];
        $mutasi = $dinamika['mutasi'] ?? ['total' => 0, 'l' => 0, 'p' => 0, 'daftar' => []];
        $akhir = $dinamika['akhir'] ?? ['total' => 0, 'l' => 0, 'p' => 0];

        $sheet1->setCellValue('A7', '1. Awal Semester');
        $sheet1->setCellValue('B7', $awal['l']);
        $sheet1->setCellValue('C7', $awal['p']);
        $sheet1->setCellValue('D7', $awal['total']);

        $sheet1->setCellValue('A8', '2. Mutasi Keluar / Mengundurkan Diri');
        $sheet1->setCellValue('B8', $mutasi['l']);
        $sheet1->setCellValue('C8', $mutasi['p']);
        $sheet1->setCellValue('D8', $mutasi['total']);

        $sheet1->setCellValue('A9', '3. Akhir Semester (Aktif)');
        $sheet1->setCellValue('B9', $akhir['l']);
        $sheet1->setCellValue('C9', $akhir['p']);
        $sheet1->setCellValue('D9', $akhir['total']);

        $sheet1->getStyle('B7:D9')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet1->getStyle('A7:D9')->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
        ]);

        // Daftar Siswa Mutasi
        $sheet1->setCellValue('A11', 'DAFTAR SISWA KELUAR / PINDAH / MENGUNDURKAN DIRI');
        $sheet1->mergeCells('A11:G11');
        $sheet1->getStyle('A11')->getFont()->setBold(true);

        $mHeaders = ['No', 'NISN', 'Nama Siswa', 'L/P', 'Tanggal Nonaktif', 'Alasan Mutasi', 'Catatan / Berkas'];
        $mCols = ['A', 'B', 'C', 'D', 'E', 'F', 'G'];
        foreach ($mHeaders as $i => $mh) {
            $sheet1->setCellValue($mCols[$i] . '12', $mh);
        }
        $sheet1->getStyle('A12:G12')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DC2626']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $mRow = 13;
        if (! empty($mutasi['daftar'])) {
            foreach ($mutasi['daftar'] as $mIdx => $ms) {
                $alasanLabel = ucwords(str_replace('_', ' ', $ms['alasan_nonaktif'] ?? '-'));
                $berkasInfo = !empty($ms['file_surat_nonaktif']) ? 'Lampiran Berkas: ' . $ms['file_surat_nonaktif'] : 'Tanpa Berkas';
                $sheet1->setCellValue('A' . $mRow, $mIdx + 1);
                $sheet1->setCellValueExplicit('B' . $mRow, $ms['nisn'] ?? '-', DataType::TYPE_STRING);
                $sheet1->setCellValue('C' . $mRow, $ms['nama_siswa'] ?? '-');
                $sheet1->setCellValue('D' . $mRow, $ms['jenis_kelamin'] ?? 'L');
                $sheet1->setCellValue('E' . $mRow, $ms['tanggal_nonaktif'] ?? '-');
                $sheet1->setCellValue('F' . $mRow, $alasanLabel);
                $sheet1->setCellValue('G' . $mRow, ($ms['catatan_nonaktif'] ? $ms['catatan_nonaktif'] . ' | ' : '') . $berkasInfo);
                $mRow++;
            }
        } else {
            $sheet1->setCellValue('A13', 'Tidak ada siswa mutasi / keluar pada semester ini.');
            $sheet1->mergeCells('A13:G13');
            $sheet1->getStyle('A13')->getFont()->setItalic(true);
            $sheet1->getStyle('A13')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $mRow = 14;
        }

        $sheet1->getStyle("A12:G" . ($mRow - 1))->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
        ]);

        foreach (range('A', 'G') as $c) {
            $sheet1->getColumnDimension($c)->setAutoSize(true);
        }

        // ======================================
        // SHEET 2: SISWA BERMASALAH (RAPAT AKHIR)
        // ======================================
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Siswa Bermasalah');

        $sheet2->setCellValue('A1', 'DAFTAR SISWA BERMASALAH & REKOMENDASI RAPAT AKHIR SEMESTER');
        $sheet2->setCellValue('A2', 'KELAS: ' . strtoupper($kelas['nama_kelas'] ?? '-') . ' | ' . strtoupper($labelPeriode));
        $sheet2->mergeCells('A1:K1');
        $sheet2->mergeCells('A2:K2');
        $sheet2->getStyle('A1')->getFont()->setBold(true)->setSize(13);
        $sheet2->getStyle('A2')->getFont()->setSize(10)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('4338CA'));

        $bHeaders = ['No', 'NISN', 'Nama Siswa', 'L/P', 'Hadir (H)', 'Sakit (S)', 'Izin (I)', 'Alpa (A)', 'Terlambat (T)', 'Indikator Disiplin', 'Rincian Kasus Pembinaan (Berantem/SP/Perilaku)'];
        $bCols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K'];
        foreach ($bHeaders as $i => $bh) {
            $sheet2->setCellValue($bCols[$i] . '4', $bh);
        }
        $sheet2->getStyle('A4:K4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'B91C1C']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $bRow = 5;
        if (! empty($siswaBermasalah)) {
            foreach ($siswaBermasalah as $bIdx => $bs) {
                $statusDisiplin = [];
                if ((int)($bs['total_a'] ?? 0) >= 3) {
                    $statusDisiplin[] = 'Alpa Tinggi (' . $bs['total_a'] . 'x)';
                }
                if ((int)($bs['total_t'] ?? 0) > 3) {
                    $statusDisiplin[] = 'Wajib Bina Terlambat (' . $bs['total_t'] . 'x)';
                } elseif ((int)($bs['total_t'] ?? 0) > 1) {
                    $statusDisiplin[] = 'Perhatian Terlambat (' . $bs['total_t'] . 'x)';
                }
                if (! empty($bs['total_kasus_sp'])) {
                    $statusDisiplin[] = 'Ada SP / Kasus (' . $bs['total_kasus_sp'] . 'x)';
                }

                $rincianKasus = [];
                if (! empty($bs['kasus_pembinaan'])) {
                    foreach ($bs['kasus_pembinaan'] as $kp) {
                        $tgl = !empty($kp['tanggal_tindakan']) ? date('d/m/Y', strtotime($kp['tanggal_tindakan'])) : '-';
                        $tindakan = $kp['jenis_tindakan'] ?? 'Kasus Disiplin';
                        $alasan = $kp['catatan_pembinaan'] ?? '-';
                        $rincianKasus[] = "[{$tgl}] {$tindakan}: \"{$alasan}\"";
                    }
                }

                $sheet2->setCellValue('A' . $bRow, $bIdx + 1);
                $sheet2->setCellValueExplicit('B' . $bRow, $bs['nisn'] ?? '-', DataType::TYPE_STRING);
                $sheet2->setCellValue('C' . $bRow, $bs['nama_siswa'] ?? '-');
                $sheet2->setCellValue('D' . $bRow, $bs['jenis_kelamin'] ?? 'L');
                $sheet2->setCellValue('E' . $bRow, $bs['total_h'] ?? 0);
                $sheet2->setCellValue('F' . $bRow, $bs['total_s'] ?? 0);
                $sheet2->setCellValue('G' . $bRow, $bs['total_i'] ?? 0);
                $sheet2->setCellValue('H' . $bRow, $bs['total_a'] ?? 0);
                $sheet2->setCellValue('I' . $bRow, $bs['total_t'] ?? 0);
                $sheet2->setCellValue('J' . $bRow, !empty($statusDisiplin) ? implode('; ', $statusDisiplin) : 'Perhatian Kehadiran');
                $sheet2->setCellValue('K' . $bRow, !empty($rincianKasus) ? implode("\n", $rincianKasus) : '-');
                $sheet2->getStyle('K' . $bRow)->getAlignment()->setWrapText(true);
                $bRow++;
            }
        } else {
            $sheet2->setCellValue('A5', 'Nihil (Tidak ada siswa bermasalah / pelanggaran di ambang batas).');
            $sheet2->mergeCells('A5:K5');
            $sheet2->getStyle('A5')->getFont()->setItalic(true);
            $sheet2->getStyle('A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $bRow = 6;
        }

        $sheet2->getStyle("A4:K" . ($bRow - 1))->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
        ]);

        foreach (range('A', 'K') as $c) {
            $sheet2->getColumnDimension($c)->setAutoSize(true);
        }

        // ======================================
        // SHEET 3: REKAP LENGKAP PRESENSI KELAS
        // ======================================
        $sheet3 = $spreadsheet->createSheet();
        $sheet3->setTitle('Rekap Presensi');

        $sheet3->setCellValue('A1', 'REKAPITULASI PRESENSI LENGKAP SEMESTER');
        $sheet3->setCellValue('A2', 'KELAS: ' . strtoupper($kelas['nama_kelas'] ?? '-') . ' | ' . strtoupper($labelPeriode));
        $sheet3->mergeCells('A1:J1');
        $sheet3->mergeCells('A2:J2');
        $sheet3->getStyle('A1')->getFont()->setBold(true)->setSize(13);

        $rHeaders = ['No', 'NISN', 'Nama Lengkap', 'L/P', 'H', 'S', 'I', 'A', 'Terlambat (T)', '% Hadir'];
        $rCols = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J'];
        foreach ($rHeaders as $i => $rh) {
            $sheet3->setCellValue($rCols[$i] . '4', $rh);
        }
        $sheet3->getStyle('A4:J4')->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1E293B']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $rRow = 5;
        foreach ($rekapSiswa as $rIdx => $rs) {
            $h = (int)($rs['total_h'] ?? 0);
            $s = (int)($rs['total_s'] ?? 0);
            $i = (int)($rs['total_i'] ?? 0);
            $a = (int)($rs['total_a'] ?? 0);
            $tot = $h + $s + $i + $a;
            $pct = $tot > 0 ? round(($h / $tot) * 100, 1) : 0;

            $sheet3->setCellValue('A' . $rRow, $rIdx + 1);
            $sheet3->setCellValueExplicit('B' . $rRow, $rs['nisn'] ?? '-', DataType::TYPE_STRING);
            $sheet3->setCellValue('C' . $rRow, $rs['nama_siswa'] ?? '-');
            $sheet3->setCellValue('D' . $rRow, $rs['jenis_kelamin'] ?? 'L');
            $sheet3->setCellValue('E' . $rRow, $h);
            $sheet3->setCellValue('F' . $rRow, $s);
            $sheet3->setCellValue('G' . $rRow, $i);
            $sheet3->setCellValue('H' . $rRow, $a);
            $sheet3->setCellValue('I' . $rRow, (int)($rs['total_t'] ?? 0));
            $sheet3->setCellValue('J' . $rRow, $pct . '%');
            $rRow++;
        }

        $sheet3->getStyle("A4:J" . ($rRow - 1))->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CBD5E1']]],
        ]);

        foreach (range('A', 'J') as $c) {
            $sheet3->getColumnDimension($c)->setAutoSize(true);
        }

        $spreadsheet->setActiveSheetIndex(0);
        $safeName = preg_replace('/[^A-Za-z0-9_-]/', '_', $kelas['nama_kelas'] ?? 'Kelas');
        $this->outputFile($spreadsheet, 'Evaluasi_Semester_' . $safeName . '_' . date('Ymd_His') . '.xlsx');
    }

    /**
     * Stream File Spreadsheet ke Browser
     */
    protected function outputFile(Spreadsheet $spreadsheet, string $fileName): void
    {
        $writer = new Xlsx($spreadsheet);

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $fileName . '"');
        header('Cache-Control: max-age=0');
        header('Pragma: public');

        $writer->save('php://output');
        exit;
    }
}
