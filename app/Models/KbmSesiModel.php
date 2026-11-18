<?php

namespace App\Models;

use CodeIgniter\Model;

class KbmSesiModel extends Model
{
    protected $table            = 'kbm_sesi';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'guru_id',
        'kelas_id',
        'mapel_id',
        'tanggal',
        'jam_ke',
        'judul_materi',
        'deskripsi_materi',
        'status',
        'token_sesi',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getDetailSesi(int $sesiId)
    {
        return $this->select('kbm_sesi.*, kelas.nama_kelas, mapel.nama_mapel, guru.nama_guru')
                    ->join('kelas', 'kelas.id = kbm_sesi.kelas_id')
                    ->join('mapel', 'mapel.id = kbm_sesi.mapel_id')
                    ->join('guru', 'guru.id = kbm_sesi.guru_id')
                    ->where('kbm_sesi.id', $sesiId)
                    ->first();
    }
}

