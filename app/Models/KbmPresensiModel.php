<?php

namespace App\Models;

use CodeIgniter\Model;

class KbmPresensiModel extends Model
{
    protected $table            = 'kbm_presensi';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $allowedFields    = [
        'sesi_id',
        'siswa_id',
        'status',
        'catatan',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getPresensiBySesi(int $sesiId)
    {
        return $this->select('kbm_presensi.*, siswa.nama_siswa, siswa.nisn, siswa.jenis_kelamin')
                    ->join('siswa', 'siswa.id = kbm_presensi.siswa_id')
                    ->where('kbm_presensi.sesi_id', $sesiId)
                    ->orderBy('siswa.nama_siswa', 'ASC')
                    ->findAll();
    }
}

