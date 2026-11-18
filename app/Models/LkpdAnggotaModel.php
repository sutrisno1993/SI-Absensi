<?php

namespace App\Models;

use CodeIgniter\Model;

class LkpdAnggotaModel extends Model
{
    protected $table            = 'lkpd_anggota';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'kelompok_id',
        'siswa_id',
        'peran',
        'nilai_individu',
        'catatan',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getAnggotaByKelompok(int $kelompokId)
    {
        return $this->select('lkpd_anggota.*, siswa.nama_siswa, siswa.nisn')
            ->join('siswa', 'siswa.id = lkpd_anggota.siswa_id')
            ->where('lkpd_anggota.kelompok_id', $kelompokId)
            ->orderBy('siswa.nama_siswa', 'ASC')
            ->findAll();
    }
}
