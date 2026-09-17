<?php

namespace App\Models;

use CodeIgniter\Model;

class SiswaModel extends Model
{
    protected $table            = 'siswa';
    protected $primaryKey       = 'id';
    protected $useAutoIncrement = true;
    protected $returnType       = 'array';
    protected $protectFields    = true;
    protected $allowedFields    = [
        'nisn',
        'nama_siswa',
        'kelas_id',
        'status',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    /**
     * Dapatkan siswa dengan nama kelas, opsional filter kelas dan status
     */
    public function getSiswaWithKelas(?int $kelasId = null, ?string $status = null)
    {
        $builder = $this->select('siswa.*, kelas.nama_kelas')
                        ->join('kelas', 'kelas.id = siswa.kelas_id', 'inner');
        
        if ($kelasId !== null) {
            $builder->where('siswa.kelas_id', $kelasId);
        }

        if ($status !== null && in_array($status, ['Aktif', 'Nonaktif'])) {
            $builder->where('siswa.status', $status);
        }

        return $builder->orderBy('siswa.nama_siswa', 'ASC')->findAll();
    }

    /**
     * Ambil hanya siswa aktif pada kelas tertentu
     */
    public function getSiswaAktifByKelas(int $kelasId): array
    {
        return $this->where('kelas_id', $kelasId)
                    ->where('status', 'Aktif')
                    ->orderBy('nama_siswa', 'ASC')
                    ->findAll();
    }
}
