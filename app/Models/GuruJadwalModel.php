<?php

namespace App\Models;

use CodeIgniter\Model;

class GuruJadwalModel extends Model
{
    protected $table            = 'guru_jadwal';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'guru_id',
        'hari',
        'shift',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Ambil jadwal mengajar untuk satu guru
     */
    public function getJadwalByGuru(int $guruId): array
    {
        return $this->where('guru_id', $guruId)->findAll();
    }

    /**
     * Ambil semua jadwal dikelompokkan berdasarkan guru_id
     * Return format: [guru_id => [['hari' => 'Senin', 'shift' => 'Pagi'], ...]]
     */
    public function getAllJadwalGrouped(): array
    {
        $all = $this->orderBy('hari', 'ASC')->findAll();
        $grouped = [];
        foreach ($all as $item) {
            $grouped[$item['guru_id']][] = [
                'hari'  => $item['hari'],
                'shift' => $item['shift'],
            ];
        }
        return $grouped;
    }

    /**
     * Ambil daftar Guru yang memiliki jadwal mengajar pada hari dan shift tertentu
     */
    public function getGuruByHariAndShift(string $hari, string $shift): array
    {
        return $this->select('guru.*, guru_jadwal.hari, guru_jadwal.shift')
                    ->join('guru', 'guru.id = guru_jadwal.guru_id')
                    ->where('guru_jadwal.hari', $hari)
                    ->where('guru_jadwal.shift', $shift)
                    ->orderBy('guru.nama_guru', 'ASC')
                    ->findAll();
    }

    /**
     * Sinkronisasi jadwal mengajar seorang guru
     * $jadwalList: array of ['hari' => 'Senin', 'shift' => 'Pagi']
     */
    public function syncJadwal(int $guruId, array $jadwalList): bool
    {
        // Hapus jadwal lama guru ini
        $this->where('guru_id', $guruId)->delete();

        if (empty($jadwalList)) {
            return true;
        }

        $insertData = [];
        $uniqueCheck = [];
        foreach ($jadwalList as $j) {
            $hari  = trim($j['hari'] ?? '');
            $shift = trim($j['shift'] ?? '');
            $key   = $hari . '_' . $shift;

            if (! empty($hari) && ! empty($shift) && ! isset($uniqueCheck[$key])) {
                $uniqueCheck[$key] = true;
                $insertData[] = [
                    'guru_id'    => $guruId,
                    'hari'       => $hari,
                    'shift'      => $shift,
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ];
            }
        }

        if (! empty($insertData)) {
            $this->insertBatch($insertData);
        }

        return true;
    }

    /**
     * Sinkronisasi penugasan guru berdasarkan Hari dan Shift tertentu
     * Menghapus semua penugasan pada (hari, shift) tersebut dan menggantinya dengan daftar guruId baru
     */
    public function syncJadwalByHariAndShift(string $hari, string $shift, array $guruIds): bool
    {
        // 1. Hapus jadwal untuk sesi (hari, shift) ini
        $this->where('hari', $hari)->where('shift', $shift)->delete();

        if (empty($guruIds)) {
            return true;
        }

        // 2. Insert batch guru yang dicentang
        $now = date('Y-m-d H:i:s');
        $batch = [];
        $unique = array_unique(array_map('intval', $guruIds));

        foreach ($unique as $gid) {
            if ($gid > 0) {
                $batch[] = [
                    'guru_id'    => $gid,
                    'hari'       => $hari,
                    'shift'      => $shift,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
        }

        if (! empty($batch)) {
            $this->insertBatch($batch);
        }

        return true;
    }

    /**
     * Ambil matriks lengkap jadwal mengajar (6 Hari x 2 Shift)
     * Return format: [hari => ['Pagi' => [guru...], 'Siang' => [guru...]]]
     */
    public function getJadwalMatrix(): array
    {
        $allHari = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
        $matrix = [];
        foreach ($allHari as $h) {
            $matrix[$h] = [
                'Pagi'  => [],
                'Siang' => [],
            ];
        }

        $records = $this->select('guru_jadwal.*, guru.nama_guru, guru.nip, guru.no_hp')
                        ->join('guru', 'guru.id = guru_jadwal.guru_id')
                        ->orderBy('guru.nama_guru', 'ASC')
                        ->findAll();

        foreach ($records as $r) {
            if (isset($matrix[$r['hari']][$r['shift']])) {
                $matrix[$r['hari']][$r['shift']][] = $r;
            }
        }

        return $matrix;
    }
}
