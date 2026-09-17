<?php

namespace App\Models;

use CodeIgniter\Model;

class PiketPetugasModel extends Model
{
    protected $table            = 'piket_petugas';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'hari',
        'shift',
        'guru_id',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Ambil petugas piket pada hari dan shift tertentu
     */
    public function getPetugasByHariAndShift(string $hari, string $shift): array
    {
        return $this->select('piket_petugas.*, guru.nama_guru, guru.nip, guru.no_hp')
                    ->join('guru', 'guru.id = piket_petugas.guru_id')
                    ->where('piket_petugas.hari', $hari)
                    ->where('piket_petugas.shift', $shift)
                    ->findAll();
    }

    /**
     * Ambil semua penugasan piket dikelompokkan berdasarkan Hari & Shift
     * Format: [hari => [shift => [guru1, guru2]]]
     */
    public function getAllGrouped(): array
    {
        $all = $this->select('piket_petugas.*, guru.nama_guru, guru.nip')
                    ->join('guru', 'guru.id = piket_petugas.guru_id')
                    ->orderBy('piket_petugas.hari', 'ASC')
                    ->findAll();

        $grouped = [];
        foreach ($all as $item) {
            $grouped[$item['hari']][$item['shift']][] = $item;
        }

        return $grouped;
    }

    /**
     * Simpan penugasan guru piket untuk hari & shift tertentu
     */
    public function syncPetugas(string $hari, string $shift, array $guruIds): bool
    {
        $this->where('hari', $hari)->where('shift', $shift)->delete();

        if (empty($guruIds)) {
            return true;
        }

        $insertData = [];
        foreach ($guruIds as $gid) {
            $gid = (int)$gid;
            if ($gid > 0) {
                $insertData[] = [
                    'hari'       => $hari,
                    'shift'      => $shift,
                    'guru_id'    => $gid,
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
}
