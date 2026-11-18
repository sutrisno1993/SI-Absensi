<?php

namespace App\Models;

use CodeIgniter\Model;

class AsesmenModel extends Model
{
    protected $table            = 'asesmen';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'sesi_id',
        'guru_id',
        'judul_asesmen',
        'tipe',
        'durasi_menit',
        'token_live',
        'status',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getDetailByToken(string $token)
    {
        return $this->select('asesmen.*, kbm_sesi.judul_materi, kbm_sesi.kelas_id, kelas.nama_kelas, mapel.nama_mapel, guru.nama_guru')
                    ->join('kbm_sesi', 'kbm_sesi.id = asesmen.sesi_id')
                    ->join('kelas', 'kelas.id = kbm_sesi.kelas_id')
                    ->join('mapel', 'mapel.id = kbm_sesi.mapel_id')
                    ->join('guru', 'guru.id = asesmen.guru_id')
                    ->where('asesmen.token_live', $token)
                    ->first();
    }
}

