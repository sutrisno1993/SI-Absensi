<?php

namespace App\Models;

use CodeIgniter\Model;

class GuruModel extends Model
{
    protected $table            = 'guru';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nip',
        'nama_guru',
        'no_hp',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Ambil seluruh guru beserta jadwal mengajar masing-masing
     */
    public function getGuruWithJadwal(): array
    {
        $guruList = $this->orderBy('nama_guru', 'ASC')->findAll();
        $jadwalModel = new \App\Models\GuruJadwalModel();
        $jadwalGrouped = $jadwalModel->getAllJadwalGrouped();

        foreach ($guruList as &$g) {
            $g['jadwal'] = $jadwalGrouped[$g['id']] ?? [];
        }

        return $guruList;
    }
}
