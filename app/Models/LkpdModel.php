<?php

namespace App\Models;

use CodeIgniter\Model;

class LkpdModel extends Model
{
    protected $table            = 'lkpd';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'sesi_id',
        'guru_id',
        'judul_lkpd',
        'petunjuk',
        'file_panduan',
        'metode_kelompok',
        'tenggat_waktu',
        'token_lkpd',
        'status',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getDetailByToken(string $token)
    {
        return $this->select('lkpd.*, kbm_sesi.judul_materi, kbm_sesi.kelas_id, kelas.nama_kelas, mapel.nama_mapel, guru.nama_guru')
            ->join('kbm_sesi', 'kbm_sesi.id = lkpd.sesi_id')
            ->join('kelas', 'kelas.id = kbm_sesi.kelas_id')
            ->join('mapel', 'mapel.id = kbm_sesi.mapel_id')
            ->join('guru', 'guru.id = lkpd.guru_id')
            ->where('lkpd.token_lkpd', $token)
            ->first();
    }
}
