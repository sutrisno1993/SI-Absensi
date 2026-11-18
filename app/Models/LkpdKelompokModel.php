<?php

namespace App\Models;

use CodeIgniter\Model;

class LkpdKelompokModel extends Model
{
    protected $table            = 'lkpd_kelompok';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $useSoftDeletes   = false;
    protected $protectFields    = true;
    protected $allowedFields    = [
        'lkpd_id',
        'nama_kelompok',
        'nomor_kelompok',
        'ketua_siswa_id',
    ];

    protected $useTimestamps = true;
    protected $dateFormat    = 'datetime';
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getKelompokByLkpd(int $lkpdId)
    {
        $kelompokList = $this->select('lkpd_kelompok.*, siswa.nama_siswa as nama_ketua')
            ->join('siswa', 'siswa.id = lkpd_kelompok.ketua_siswa_id', 'left')
            ->where('lkpd_kelompok.lkpd_id', $lkpdId)
            ->orderBy('lkpd_kelompok.nomor_kelompok', 'ASC')
            ->findAll();

        $anggotaModel = new LkpdAnggotaModel();
        $jawabanModel = new LkpdJawabanModel();

        foreach ($kelompokList as &$k) {
            $k['anggota'] = $anggotaModel->getAnggotaByKelompok($k['id']);
            $k['jawaban'] = $jawabanModel->where('kelompok_id', $k['id'])->first();
        }

        return $kelompokList;
    }
}
