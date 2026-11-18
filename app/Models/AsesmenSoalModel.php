<?php

namespace App\Models;

use CodeIgniter\Model;

class AsesmenSoalModel extends Model
{
    protected $table            = 'asesmen_soal';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'asesmen_id',
        'nomor_urut',
        'tipe_soal',
        'pertanyaan',
        'opsi_a',
        'opsi_b',
        'opsi_c',
        'opsi_d',
        'opsi_e',
        'kunci_pg',
        'pedoman_essay',
        'bobot',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getSoalByAsesmen(int $asesmenId)
    {
        return $this->where('asesmen_id', $asesmenId)
                    ->orderBy('nomor_urut', 'ASC')
                    ->findAll();
    }
}

