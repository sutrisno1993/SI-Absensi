<?php

namespace App\Models;

use CodeIgniter\Model;

class AbsensiGuruModel extends Model
{
    protected $table            = 'absensi_guru';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'tanggal',
        'hari',
        'shift',
        'guru_id',
        'status',
        'jam_masuk',
        'menit_terlambat',
        'keterangan',
        'dicatat_oleh',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Ambil data presensi guru untuk tanggal dan shift tertentu
     */
    public function getAbsensiByTanggalAndShift(string $tanggal, string $shift): array
    {
        return $this->select('absensi_guru.*, guru.nama_guru, guru.nip')
                    ->join('guru', 'guru.id = absensi_guru.guru_id')
                    ->where('absensi_guru.tanggal', $tanggal)
                    ->where('absensi_guru.shift', $shift)
                    ->findAll();
    }

    /**
     * Simpan / update status presensi guru secara batch
     */
    public function simpanAbsenBatch(string $tanggal, string $hari, string $shift, array $dataGuru, ?int $dicatatOleh = null): bool
    {
        // Ambil standar jam masuk dari tabel pengaturan
        $pengaturanModel = new \App\Models\PengaturanModel();
        $jamStandarPagi  = $pengaturanModel->getSetting('jam_masuk_pagi', '06:30');
        $jamStandarSiang = $pengaturanModel->getSetting('jam_masuk_siang', '12:30');
        $toleransi       = (int)$pengaturanModel->getSetting('toleransi_menit', '0');

        $jamStandar = ($shift === 'Siang') ? $jamStandarSiang : $jamStandarPagi;
        if (strlen($jamStandar) === 5) {
            $jamStandar .= ':00';
        }
        $standarTimestamp = strtotime("$tanggal $jamStandar") + ($toleransi * 60);

        foreach ($dataGuru as $guruId => $info) {
            $status = !empty($info['status']) ? trim($info['status']) : null;
            $jamMasuk = !empty($info['jam_masuk']) ? trim($info['jam_masuk']) : null;
            $keterangan = !empty($info['keterangan']) ? trim($info['keterangan']) : null;
            $menitTerlambat = 0;

            // Jika status null (belum diabsen / belum datang)
            if (empty($status)) {
                $existing = $this->where('tanggal', $tanggal)
                                 ->where('shift', $shift)
                                 ->where('guru_id', $guruId)
                                 ->first();
                if ($existing) {
                    $this->delete($existing['id']);
                }
                continue;
            }

            if ($status === 'H' && !empty($jamMasuk)) {
                $actualTimestamp = strtotime("$tanggal $jamMasuk");
                if ($actualTimestamp > $standarTimestamp) {
                    $menitTerlambat = (int)ceil(($actualTimestamp - $standarTimestamp) / 60);
                }
            }

            // Cek apakah sudah ada catatan untuk tanggal, shift, guru_id
            $existing = $this->where('tanggal', $tanggal)
                             ->where('shift', $shift)
                             ->where('guru_id', $guruId)
                             ->first();

            $record = [
                'tanggal'         => $tanggal,
                'hari'            => $hari,
                'shift'           => $shift,
                'guru_id'         => $guruId,
                'status'          => $status,
                'jam_masuk'       => $jamMasuk,
                'menit_terlambat' => $menitTerlambat,
                'keterangan'      => $keterangan,
                'dicatat_oleh'    => $dicatatOleh,
            ];

            if ($existing) {
                $this->update($existing['id'], $record);
            } else {
                $this->insert($record);
            }
        }

        return true;
    }

    /**
     * Hitung statistik persentase kehadiran guru dalam rentang tanggal
     */
    public function getStatistikKehadiranRange(?string $startDate = null, ?string $endDate = null): array
    {
        $builder = $this->select("
            COUNT(*) as total_sesi,
            SUM(CASE WHEN status = 'H' THEN 1 ELSE 0 END) as total_hadir,
            SUM(CASE WHEN status = 'S' THEN 1 ELSE 0 END) as total_sakit,
            SUM(CASE WHEN status = 'I' THEN 1 ELSE 0 END) as total_izin,
            SUM(CASE WHEN status = 'A' THEN 1 ELSE 0 END) as total_alfa,
            SUM(CASE WHEN status = 'H' AND menit_terlambat > 0 THEN 1 ELSE 0 END) as total_terlambat
        ");

        if ($startDate && $endDate) {
            $builder->where('tanggal >=', $startDate)->where('tanggal <=', $endDate);
        }

        $row = $builder->first();

        $totalSesi = (int)($row['total_sesi'] ?? 0);
        $totalHadir = (int)($row['total_hadir'] ?? 0);
        $totalSakit = (int)($row['total_sakit'] ?? 0);
        $totalIzin  = (int)($row['total_izin'] ?? 0);
        $totalAlfa  = (int)($row['total_alfa'] ?? 0);
        $totalTerlambat = (int)($row['total_terlambat'] ?? 0);

        $persenHadir = $totalSesi > 0 ? round(($totalHadir / $totalSesi) * 100, 1) : 0;
        $persenSakit = $totalSesi > 0 ? round(($totalSakit / $totalSesi) * 100, 1) : 0;
        $persenIzin  = $totalSesi > 0 ? round(($totalIzin / $totalSesi) * 100, 1) : 0;
        $persenAlfa  = $totalSesi > 0 ? round(($totalAlfa / $totalSesi) * 100, 1) : 0;

        return [
            'total_sesi'      => $totalSesi,
            'total_hadir'     => $totalHadir,
            'total_sakit'     => $totalSakit,
            'total_izin'      => $totalIzin,
            'total_alfa'      => $totalAlfa,
            'total_terlambat' => $totalTerlambat,
            'persen_hadir'    => $persenHadir,
            'persen_sakit'    => $persenSakit,
            'persen_izin'     => $persenIzin,
            'persen_alfa'     => $persenAlfa,
        ];
    }

    /**
     * Hitung performa kehadiran per masing-masing guru dalam rentang tanggal
     */
    public function getPerformaPerGuru(?string $startDate = null, ?string $endDate = null): array
    {
        $db = \Config\Database::connect();
        $dateFilter = '';
        if ($startDate && $endDate) {
            $dateFilter = "AND ag.tanggal BETWEEN " . $db->escape($startDate) . " AND " . $db->escape($endDate);
        }

        $sql = "
            SELECT 
                g.id,
                g.nip,
                g.nama_guru,
                COUNT(ag.id) as total_sesi,
                SUM(CASE WHEN ag.status = 'H' THEN 1 ELSE 0 END) as total_hadir,
                SUM(CASE WHEN ag.status = 'S' THEN 1 ELSE 0 END) as total_sakit,
                SUM(CASE WHEN ag.status = 'I' THEN 1 ELSE 0 END) as total_izin,
                SUM(CASE WHEN ag.status = 'A' THEN 1 ELSE 0 END) as total_alfa,
                SUM(CASE WHEN ag.status = 'H' AND ag.menit_terlambat > 0 THEN 1 ELSE 0 END) as total_terlambat
            FROM guru g
            LEFT JOIN absensi_guru ag ON ag.guru_id = g.id {$dateFilter}
            GROUP BY g.id, g.nip, g.nama_guru
            ORDER BY g.nama_guru ASC
        ";

        $rows = $db->query($sql)->getResultArray();
        foreach ($rows as &$r) {
            $total = (int)$r['total_sesi'];
            $hadir = (int)$r['total_hadir'];
            $r['persen_hadir'] = $total > 0 ? round(($hadir / $total) * 100, 1) : 100; // default 100% jika belum ada sesi
        }

        return $rows;
    }

    /**
     * Ambil daftar rincian keterlambatan guru dengan filter
     */
    public function getKeterlambatanList(?string $startDate = null, ?string $endDate = null, ?int $guruId = null, ?string $shift = null): array
    {
        $builder = $this->select('absensi_guru.*, guru.nama_guru, guru.nip, guru.no_hp, u.username as dicatat_oleh_user')
                        ->join('guru', 'guru.id = absensi_guru.guru_id', 'inner')
                        ->join('users u', 'u.id = absensi_guru.dicatat_oleh', 'left')
                        ->where('absensi_guru.status', 'H')
                        ->where('absensi_guru.menit_terlambat >', 0);

        if ($startDate && $endDate) {
            $builder->where('absensi_guru.tanggal >=', $startDate)->where('absensi_guru.tanggal <=', $endDate);
        } elseif ($startDate) {
            $builder->where('absensi_guru.tanggal', $startDate);
        }

        if ($guruId) {
            $builder->where('absensi_guru.guru_id', $guruId);
        }

        if ($shift) {
            $builder->where('absensi_guru.shift', $shift);
        }

        return $builder->orderBy('absensi_guru.tanggal', 'DESC')
                       ->orderBy('absensi_guru.jam_masuk', 'DESC')
                       ->findAll();
    }
}
