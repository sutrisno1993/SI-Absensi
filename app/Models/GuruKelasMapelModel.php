<?php

namespace App\Models;

use CodeIgniter\Model;

class GuruKelasMapelModel extends Model
{
    protected $table            = 'guru_kelas_mapel';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'guru_id',
        'kelas_id',
        'mapel_id',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Dapatkan daftar mapel yang diajar guru tertentu pada kelas tertentu
     */
    public function getMapelByGuruDanKelas(int $guruId, int $kelasId)
    {
        return $this->select('guru_kelas_mapel.*, mapel.nama_mapel, mapel.kode_mapel')
                    ->join('mapel', 'mapel.id = guru_kelas_mapel.mapel_id')
                    ->where('guru_kelas_mapel.guru_id', $guruId)
                    ->where('guru_kelas_mapel.kelas_id', $kelasId)
                    ->orderBy('mapel.nama_mapel', 'ASC')
                    ->findAll();
    }
}

